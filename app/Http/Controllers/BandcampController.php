<?php

namespace App\Http\Controllers;

use App\Jobs\AlbumRefreshTask;
use App\Models\Album;
use App\Models\Config;
use App\Services\BandcampService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BandcampController extends Controller
{
    protected $bandcampService;

    public function __construct(BandcampService $bandcampService)
    {
        $this->bandcampService = $bandcampService;
    }

    /**
     * Start an asynchronous album refresh task
     */
    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fan_id' => 'required|string',
        ]);

        $fanId = $validated['fan_id'];

        // Check if there's already an active refresh task
        $activeTaskId = Cache::get('album_refresh_active');
        if ($activeTaskId) {
            return response()->json([
                'success' => false,
                'message' => 'A refresh is already in progress. Please wait for it to complete.',
                'active_task_id' => $activeTaskId,
            ], 409);
        }

        // Generate unique task ID
        $taskId = (string) Str::uuid();
        
        // Mark this task as active
        Cache::put('album_refresh_active', $taskId, 3600); // 1 hour expiration

        // Create initial task data
        $taskData = [
            'id' => $taskId,
            'status' => 'pending',
            'message' => 'Task created, waiting to start...',
            'fan_id' => $fanId,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'data' => [],
        ];

        // Store task data
        Cache::put("album_refresh_task_{$taskId}", $taskData, 3600);

        // Dispatch the job
        AlbumRefreshTask::dispatch($taskId, $fanId);

        Log::info("Started album refresh task {$taskId} for fan ID: {$fanId}");

        return response()->json([
            'success' => true,
            'message' => 'Album refresh started',
            'task_id' => $taskId,
            'status' => 'pending',
        ]);
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
     * Get refresh task status
     */
    public function refreshStatus(Request $request, string $taskId = null): JsonResponse
    {
        // If no task ID provided, check for active task
        if (!$taskId) {
            $activeTaskId = Cache::get('album_refresh_active');
            if ($activeTaskId) {
                $taskId = $activeTaskId;
            } else {
                // No active task, return general status
                return response()->json([
                    'active' => false,
                    'can_refresh' => true,
                    'last_refresh' => null, // Database access removed for simplicity
                ]);
            }
        }

        // Get task data from cache
        $taskData = Cache::get("album_refresh_task_{$taskId}");
        
        if (!$taskData) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'task' => $taskData,
            'active' => $taskData['status'] === 'running' || $taskData['status'] === 'pending',
            'can_refresh' => $taskData['status'] === 'completed' || $taskData['status'] === 'failed',
        ]);
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
