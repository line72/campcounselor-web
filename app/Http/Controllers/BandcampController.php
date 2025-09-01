<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Config;
use App\Services\BandcampService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BandcampController extends Controller
{
    protected $bandcampService;

    public function __construct(BandcampService $bandcampService)
    {
        $this->bandcampService = $bandcampService;
    }

    /**
     * Refresh albums from Bandcamp
     */
    public function refresh(Request $request): JsonResponse
    {
        // Increase execution time limit for this operation
        set_time_limit(300); // 5 minutes for very large collections
        
        $validated = $request->validate([
            'fan_id' => 'required|string',
        ]);

        $fanId = $validated['fan_id'];
        $config = Config::getInstance();
        $now = Carbon::now();

        Log::info("Starting album refresh for fan ID: $fanId");

        try {
            // Fetch collection (purchased albums)
            Log::info("Fetching collection albums for fan ID: $fanId");
            $collectionAlbums = $this->bandcampService->fetchCollection($fanId);
            Log::info("Found " . count($collectionAlbums) . " collection albums");
            
            // Fetch wishlist
            Log::info("Fetching wishlist albums for fan ID: $fanId");
            $wishlistAlbums = $this->bandcampService->fetchWishlist($fanId);
            Log::info("Found " . count($wishlistAlbums) . " wishlist albums");
            
            // Process albums with proper purchased/wishlist logic
            $result = $this->processAllAlbums($collectionAlbums, $wishlistAlbums);
            $newCollectionCount = $result['new_collection'];
            $newWishlistCount = $result['new_wishlist'];
            
            Log::info("Inserted $newCollectionCount new collection albums");
            Log::info("Inserted $newWishlistCount new wishlist albums");

            // Update last refresh time
            $config->setLastRefreshTime($now);

            Log::info("Album refresh completed successfully for fan ID: $fanId");

            return response()->json([
                'success' => true,
                'message' => 'Albums refreshed successfully',
                'new_collection_albums' => $newCollectionCount,
                'new_wishlist_albums' => $newWishlistCount,
                'total_new_albums' => $newCollectionCount + $newWishlistCount,
                'total_collection_albums' => count($collectionAlbums),
                'total_wishlist_albums' => count($wishlistAlbums),
                'last_refresh' => $now,
            ]);

        } catch (\Exception $e) {
            Log::error("Album refresh failed for fan ID $fanId: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing albums: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get fan ID from username
     */
    public function getFanId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
        ]);

        try {
            $fanId = $this->bandcampService->getFanIdFromUsername($validated['username']);
            
            if ($fanId) {
                return response()->json([
                    'success' => true,
                    'fan_id' => $fanId,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not find fan ID for username',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching fan ID: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse tracks from album URL
     */
    public function parseTracks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'artist' => 'required|string',
            'album' => 'required|string',
        ]);

        try {
            $tracks = $this->bandcampService->parseTracks(
                $validated['artist'],
                $validated['album'],
                $validated['url']
            );

            return response()->json([
                'success' => true,
                'tracks' => $tracks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error parsing tracks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get refresh status
     */
    public function refreshStatus(): JsonResponse
    {
        $config = Config::getInstance();

        return response()->json([
            'last_refresh' => $config->last_refresh_time,
            'can_refresh' => true, // Always allow refresh
        ]);
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
     * Insert new albums into database (legacy method - kept for compatibility)
     */
    private function insertNewAlbums(array $albums, bool $purchased): int
    {
        $newCount = 0;

        foreach ($albums as $albumData) {
            $existing = Album::where('bandcamp_id', $albumData['bandcamp_id'])->first();
            
            if (!$existing) {
                Album::create(array_merge($albumData, ['purchased' => $purchased]));
                $newCount++;
            } elseif ($purchased && !$existing->purchased) {
                // Update existing album to purchased if it was in wishlist
                $existing->update(['purchased' => true]);
            }
        }

        return $newCount;
    }
}
