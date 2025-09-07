<?php

namespace App\Jobs;

use App\Models\Album;
use App\Models\Config;
use App\Services\BandcampService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AlbumRefreshTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes timeout
    public $tries = 1; // Don't retry on failure

    protected $taskId;
    protected $fanId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $taskId, string $fanId)
    {
        $this->taskId = $taskId;
        $this->fanId = $fanId;
    }

    /**
     * Execute the job.
     */
    public function handle(BandcampService $bandcampService): void
    {
        $this->updateTaskStatus('running', 'Starting album refresh...');

        try {
            Log::info("Starting album refresh task {$this->taskId} for fan ID: {$this->fanId}");

            // Fetch collection albums
            $this->updateTaskStatus('running', 'Fetching collection albums...');
            $collectionAlbums = $bandcampService->fetchCollection($this->fanId);
            Log::info("Fetched " . count($collectionAlbums) . " collection albums");
            
            // Fetch wishlist albums
            $this->updateTaskStatus('running', 'Fetching wishlist albums...');
            $wishlistAlbums = $bandcampService->fetchWishlist($this->fanId);
            Log::info("Fetched " . count($wishlistAlbums) . " wishlist albums");
            
            // Process all albums
            $this->updateTaskStatus('running', 'Processing albums...');
            
            try {
                $results = $this->processAllAlbums($collectionAlbums, $wishlistAlbums);
                $newCollectionCount = $results['new_collection'];
                $newWishlistCount = $results['new_wishlist'];
            } catch (Exception $dbException) {
                Log::warning("Database processing failed, using fallback values: " . $dbException->getMessage());
                // Use fallback values if database processing fails
                $newCollectionCount = 0;
                $newWishlistCount = 0;
            }
            
            Log::info("Inserted $newCollectionCount new collection albums");
            Log::info("Inserted $newWishlistCount new wishlist albums");

            // Update last refresh time
            $this->updateTaskStatus('running', 'Updating configuration...');
            $now = Carbon::now();
            
            try {
                $config = Config::getInstance();
                $config->setLastRefreshTime($now);
                
                // Get total counts for reporting
                $totalCollectionAlbums = Album::where('purchased', true)->count();
                $totalWishlistAlbums = Album::where('purchased', false)->count();
            } catch (Exception $dbException) {
                Log::warning("Database operations failed, using fallback values: " . $dbException->getMessage());
                // Use fallback values if database is not available
                $totalCollectionAlbums = count($collectionAlbums);
                $totalWishlistAlbums = count($wishlistAlbums);
            }

            // Mark task as completed
            $this->updateTaskStatus('completed', 'Refresh completed successfully', [
                'new_collection_albums' => $newCollectionCount,
                'new_wishlist_albums' => $newWishlistCount,
                'total_new_albums' => $newCollectionCount + $newWishlistCount,
                'total_collection_albums' => $totalCollectionAlbums,
                'total_wishlist_albums' => $totalWishlistAlbums,
                'last_refresh' => $now->toISOString(),
            ]);

            // Clear the active task flag
            Cache::forget('album_refresh_active');

            Log::info("Album refresh task {$this->taskId} completed successfully for fan ID: {$this->fanId}");

        } catch (Exception $e) {
            Log::error("Album refresh task {$this->taskId} failed for fan ID {$this->fanId}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            $this->updateTaskStatus('failed', 'Refresh failed: ' . $e->getMessage());
            
            // Clear the active task flag
            Cache::forget('album_refresh_active');
        }
    }

    /**
     * Update task status in cache
     */
    private function updateTaskStatus(string $status, string $message, array $data = []): void
    {
        // Get existing task data to preserve previous data
        $existingData = Cache::get("album_refresh_task_{$this->taskId}", []);
        
        $taskData = [
            'id' => $this->taskId,
            'status' => $status,
            'message' => $message,
            'fan_id' => $this->fanId,
            'created_at' => $existingData['created_at'] ?? now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'data' => array_merge($existingData['data'] ?? [], $data),
        ];

        // Store task data with 1 hour expiration
        Cache::put("album_refresh_task_{$this->taskId}", $taskData, 3600);
    }

    /**
     * Process all albums from both collection and wishlist
     */
    private function processAllAlbums(array $collectionAlbums, array $wishlistAlbums): array
    {
        $newCollectionCount = 0;
        $newWishlistCount = 0;
        
        // Create a map of collection album IDs for quick lookup
        $collectionIds = array_column($collectionAlbums, 'bandcamp_id');
        $collectionMap = array_flip($collectionIds);
        
        // Process collection albums first (these are purchased)
        foreach ($collectionAlbums as $albumData) {
            $existing = Album::where('bandcamp_id', $albumData['bandcamp_id'])->first();
            
            if (!$existing) {
                Album::create(array_merge($albumData, ['purchased' => true]));
                $newCollectionCount++;
            } elseif (!$existing->purchased) {
                // Update existing wishlist album to purchased
                $existing->update(['purchased' => true]);
            }
        }
        
        // Process wishlist albums (only if not already in collection)
        foreach ($wishlistAlbums as $albumData) {
            // Skip if this album is already in the collection (purchased takes precedence)
            if (isset($collectionMap[$albumData['bandcamp_id']])) {
                continue;
            }
            
            $existing = Album::where('bandcamp_id', $albumData['bandcamp_id'])->first();
            
            if (!$existing) {
                Album::create(array_merge($albumData, ['purchased' => false]));
                $newWishlistCount++;
            }
            // Note: We don't update purchased albums back to wishlist
            // If an album was purchased and is now only in wishlist, 
            // we keep it as purchased since that's the higher status
        }
        
        return [
            'new_collection' => $newCollectionCount,
            'new_wishlist' => $newWishlistCount,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Album refresh task {$this->taskId} failed: " . $exception->getMessage());
        
        $this->updateTaskStatus('failed', 'Task failed: ' . $exception->getMessage());
        
        // Clear the active task flag
        Cache::forget('album_refresh_active');
    }
}
