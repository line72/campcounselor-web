@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Album Cover -->
            <div class="md:w-1/3">
                <div class="aspect-square">
                    <img src="{{ $album->artwork_url ?: $album->thumbnail_url ?: '/images/default-album.png' }}" 
                         alt="{{ $album->album }}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='/images/default-album.png'">
                </div>
            </div>
            
            <!-- Album Info -->
            <div class="md:w-2/3 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $album->album }}</h1>
                        <h2 class="text-xl text-gray-600 mb-4">{{ $album->artist }}</h2>
                    </div>
                    
                    <!-- Rating Stars -->
                    <div class="flex items-center">
                        @if($album->rating < 0)
                            {{-- Unrated (-1) --}}
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-2xl text-gray-300">☆</span>
                            @endfor
                            <span class="ml-2 text-gray-600">(Unrated)</span>
                        @else
                            @php
                                $starRating = $album->rating / 2; // Convert 0-10 to 0-5
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($starRating >= $i)
                                    <span class="text-2xl text-yellow-400">★</span>
                                @elseif($starRating >= $i - 0.5)
                                    <span class="text-2xl text-yellow-400 relative">☆<span class="absolute left-0 w-1/2 overflow-hidden text-yellow-400">★</span></span>
                                @else
                                    <span class="text-2xl text-gray-300">☆</span>
                                @endif
                            @endfor
                            <span class="ml-2 text-gray-600">({{ $album->rating }}/10)</span>
                        @endif
                    </div>
                </div>
                
                <!-- Album Details -->
                <div class="space-y-3 mb-6">
                    <div>
                        <span class="font-semibold text-gray-700">Status:</span>
                        <span class="ml-2 px-2 py-1 rounded text-sm {{ $album->purchased ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $album->purchased ? 'Purchased' : 'Wishlist' }}
                        </span>
                    </div>
                    
                    <div>
                        <span class="font-semibold text-gray-700">Added:</span>
                        <span class="ml-2 text-gray-600">{{ $album->formatted_created_at }}</span>
                    </div>
                    
                    @if($album->updated_at != $album->created_at)
                        <div>
                            <span class="font-semibold text-gray-700">Updated:</span>
                            <span class="ml-2 text-gray-600">{{ $album->formatted_updated_at }}</span>
                        </div>
                    @endif
                </div>
                
                <!-- Comment -->
                @if($album->comment)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Comment:</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $album->comment }}</p>
                        </div>
                    </div>
                @endif
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3">
                    <button onclick="playAlbum({{ $album->id }})" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        ▶ Play Album
                    </button>
                    
                    <button class="edit-comment-btn" 
                            data-album-id="{{ $album->id }}" 
                            data-rating="{{ $album->rating }}" 
                            data-comment="{{ $album->comment }}" 
                            class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Edit Rating & Comment
                    </button>
                    
                    <a href="{{ $album->url }}" 
                       target="_blank" 
                       class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition-colors inline-block">
                        View on Bandcamp
                    </a>
                    
                    <a href="{{ route('home') }}" 
                       class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors inline-block">
                        ← Back to Albums
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tracks Section -->
    <div id="tracksSection" class="mt-8 bg-white rounded-lg shadow-lg p-6" style="display: none;">
        <h3 class="text-xl font-semibold mb-4">Tracks</h3>
        <div id="tracksList" class="space-y-2">
            <!-- Tracks will be loaded here -->
        </div>
    </div>
</div>

<script>
    async function playAlbum(albumId) {
        // Use the main layout's music player for consistent experience
        if (typeof window.playAlbum === 'function') {
            return window.playAlbum(albumId);
        }
        
        // Fallback: show tracks in the page (old behavior)
        try {
            const response = await fetch('/api/bandcamp/tracks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    url: '{{ $album->url }}',
                    artist: '{{ $album->artist }}',
                    album: '{{ $album->album }}'
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.tracks.length > 0) {
                displayTracks(data.tracks);
            } else {
                await showAlert('No Tracks', 'No tracks found for this album', 'info');
            }
        } catch (error) {
            console.error('Error loading tracks:', error);
            await showAlert('Error', 'Error loading album tracks', 'danger');
        }
    }
    
    function displayTracks(tracks) {
        const tracksSection = document.getElementById('tracksSection');
        const tracksList = document.getElementById('tracksList');
        
        tracksList.innerHTML = tracks.map(track => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                <div class="flex items-center">
                    <span class="w-8 text-center text-gray-500 font-mono">${track.track_num}.</span>
                    <span class="ml-3 font-medium">${track.name}</span>
                </div>
                <button onclick="playTrack('${track.url}')" 
                        class="text-blue-600 hover:text-blue-800 p-2">
                    ▶
                </button>
            </div>
        `).join('');
        
        tracksSection.style.display = 'block';
        tracksSection.scrollIntoView({ behavior: 'smooth' });
    }
    
    function playTrack(url) {
        // In a real implementation, this would play the track
        // For now, just open the track URL
        window.open(url, '_blank');
    }
    
    async function editComment(albumId, rating, comment) {
        // This function is defined in the main layout
        if (typeof window.editComment === 'function') {
            window.editComment(albumId, rating, comment);
        } else {
            // Fallback for when the function isn't available
            const newRating = await showPrompt('Edit Rating', 'Enter rating (0-10):', 'Rating (0-10)', rating.toString());
            if (newRating !== null) {
                const newComment = await showPrompt('Edit Comment', 'Enter comment:', 'Comment', comment || '');
                updateAlbumRating(albumId, parseInt(newRating) || 0, newComment || '');
            }
        }
    }
    
    function updateAlbumRating(albumId, rating, comment) {
        fetch(`/albums/${albumId}/rating`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rating: rating,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(async (data) => {
            if (data.success) {
                location.reload(); // Refresh the page to show updated rating
            } else {
                await showAlert('Error', 'Error updating rating', 'danger');
            }
        })
        .catch(async (error) => {
            console.error('Error updating rating:', error);
            await showAlert('Error', 'Error updating rating', 'danger');
        });
    }
</script>
@endsection
