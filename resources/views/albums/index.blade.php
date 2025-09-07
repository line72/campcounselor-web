@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Bar with Search and Filters -->
    <div class="header-bar">
        <input type="text" 
               id="searchInput" 
               class="search-input" 
               placeholder="Search albums or artists..."
               value="{{ request('search') }}">
        
        <select id="filterSelect" class="filter-select">
            <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All Albums</option>
            <option value="purchased" {{ request('filter') == 'purchased' ? 'selected' : '' }}>Purchased</option>
            <option value="wishlist" {{ request('filter') == 'wishlist' ? 'selected' : '' }}>Wishlist</option>
        </select>
        
        <select id="sortSelect" class="sort-select">
            <option value="artist_asc" {{ request('sort') == 'artist_asc' ? 'selected' : '' }}>Artist ↑</option>
            <option value="artist_desc" {{ request('sort') == 'artist_desc' ? 'selected' : '' }}>Artist ↓</option>
            <option value="rating_asc" {{ request('sort') == 'rating_asc' ? 'selected' : '' }}>Rating ↑</option>
            <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Rating ↓</option>
            <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Created ↑</option>
            <option value="created_desc" {{ request('sort') == 'created_desc' ? 'selected' : '' }}>Created ↓</option>
            <option value="updated_asc" {{ request('sort') == 'updated_asc' ? 'selected' : '' }}>Updated ↑</option>
            <option value="updated_desc" {{ request('sort') == 'updated_desc' ? 'selected' : '' }}>Updated ↓</option>
        </select>
        
        <button id="refreshBtn" class="refresh-btn">
            Refresh Albums
        </button>
    </div>

    <!-- Loading indicator -->
    <div id="loading" class="loading">
        <div>Loading albums...</div>
    </div>

    <!-- Albums Grid -->
    <div id="albumsContainer" class="album-grid">
        @foreach($albums as $album)
            <div class="album-card">
                <div class="album-cover">
                    <img src="{{ $album->thumbnail_url ?: '/images/default-album.png' }}" 
                         alt="{{ $album->album }}" 
                         onerror="this.src='/images/default-album.png'">
                    
                    <!-- Star Rating Overlay -->
                    <div class="stars">
                        @if($album->rating < 0)
                            {{-- Unrated (-1) --}}
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star empty">☆</span>
                            @endfor
                        @else
                            @php
                                $starRating = $album->rating / 2; // Convert 0-10 to 0-5
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($starRating >= $i)
                                    <span class="star">★</span>
                                @elseif($starRating >= $i - 0.5)
                                    <span class="star half">☆</span>
                                @else
                                    <span class="star empty">☆</span>
                                @endif
                            @endfor
                        @endif
                    </div>
                    
                    <!-- Play Button Overlay -->
                    <div class="album-overlay">
                        <button class="play-button" onclick="playAlbum({{ $album->id }})">▶</button>
                    </div>
                    
                    <!-- Edit Comment Button -->
                    <button class="edit-comment-btn" 
                            data-album-id="{{ $album->id }}" 
                            data-rating="{{ $album->rating }}" 
                            data-comment="{{ $album->comment }}">
                        Edit Comment
                    </button>
                </div>
                
                <div class="album-info">
                    <div class="album-artist">{{ $album->artist }}</div>
                    <div class="album-title">{{ $album->album }}</div>
                    <a href="{{ $album->url }}" target="_blank" class="album-link">View on Bandcamp</a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Server-side Pagination (hidden when JavaScript is enabled) -->
    @if($albums->hasPages())
        <div id="serverPagination" class="mt-6 flex justify-center">
            {{ $albums->appends(request()->query())->links() }}
        </div>
    @endif
    
    <!-- JavaScript Pagination Container -->
    <div id="paginationContainer"></div>

    <!-- Empty State -->
    @if($albums->isEmpty())
        <div class="text-center py-12">
            <div class="text-gray-500 text-lg mb-4">No albums found</div>
            <p class="text-gray-400 mb-6">
                @if(request('search'))
                    Try adjusting your search terms or filters.
                @else
                    Get started by refreshing your Bandcamp collection.
                @endif
            </p>
            @if(!request('search'))
                <button id="refreshBtn" class="refresh-btn">
                    Refresh Albums from Bandcamp
                </button>
            @endif
        </div>
    @endif
</div>

<script>
    // Additional JavaScript specific to the albums index
    document.addEventListener('DOMContentLoaded', function() {
        // No additional setup needed - DOM is the single source of truth
    });

    async function playAlbum(albumId) {
        // The main layout now has a full music player, so we just call that function
        // This provides a consistent experience across all pages
        if (typeof window.playAlbum === 'function') {
            return window.playAlbum(albumId);
        }
        
        // Fallback if the main function isn't available (shouldn't happen)
        await showAlert('Music Player', 'Music player is loading...', 'info');
    }
</script>
@endsection
