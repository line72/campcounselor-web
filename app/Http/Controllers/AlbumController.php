<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Album::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter functionality
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'purchased':
                    $query->purchased();
                    break;
                case 'wishlist':
                    $query->wishlist();
                    break;
                // 'all' is default - no filter
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'artist_asc');
        switch ($sortBy) {
            case 'artist_asc':
                $query->orderBy('artist', 'asc');
                break;
            case 'artist_desc':
                $query->orderBy('artist', 'desc');
                break;
            case 'rating_asc':
                $query->orderBy('rating', 'asc');
                break;
            case 'rating_desc':
                $query->orderBy('rating', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'updated_asc':
                $query->orderBy('updated_at', 'asc');
                break;
            case 'updated_desc':
                $query->orderBy('updated_at', 'desc');
                break;
            default:
                $query->orderBy('artist', 'asc');
        }

        $albums = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($albums);
        }

        return view('albums.index', compact('albums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('albums.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bandcamp_id' => 'required|string|max:50|unique:albums',
            'bandcamp_band_id' => 'required|string|max:50',
            'album' => 'required|string|max:4096',
            'artist' => 'required|string|max:4096',
            'url' => 'required|string|max:4096',
            'thumbnail_url' => 'nullable|string|max:4096',
            'artwork_url' => 'nullable|string|max:4096',
            'purchased' => 'boolean',
            'rating' => 'integer|min:-1|max:10',
            'comment' => 'nullable|string',
        ]);

        $album = Album::create($validated);

        return response()->json($album, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json($album);
        }

        return view('albums.show', compact('album'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Album $album): View
    {
        return view('albums.edit', compact('album'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'bandcamp_id' => 'sometimes|string|max:50|unique:albums,bandcamp_id,' . $album->id,
            'bandcamp_band_id' => 'sometimes|string|max:50',
            'album' => 'sometimes|string|max:4096',
            'artist' => 'sometimes|string|max:4096',
            'url' => 'sometimes|string|max:4096',
            'thumbnail_url' => 'nullable|string|max:4096',
            'artwork_url' => 'nullable|string|max:4096',
            'purchased' => 'sometimes|boolean',
            'rating' => 'sometimes|integer|min:-1|max:10',
            'comment' => 'nullable|string',
        ]);

        $album->update($validated);

        return response()->json($album);
    }

    /**
     * Update album rating and comment
     */
    public function updateRating(Request $request, Album $album): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:-1|max:10',
            'comment' => 'nullable|string',
        ]);

        $album->update($validated);

        return response()->json([
            'success' => true,
            'album' => $album,
            'message' => 'Album rating and comment updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Album $album): JsonResponse
    {
        $album->delete();

        return response()->json(['message' => 'Album deleted successfully']);
    }

    /**
     * Get album statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Album::count(),
            'purchased' => Album::purchased()->count(),
            'wishlist' => Album::wishlist()->count(),
            'rated' => Album::where('rating', '>=', 0)->count(),
            'average_rating' => Album::where('rating', '>=', 0)->avg('rating'),
        ];

        return response()->json($stats);
    }
}
