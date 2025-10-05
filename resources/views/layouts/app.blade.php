<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">

    <title>{{ config('app.name', 'Camp Counselor') }}</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- No external fonts - using system fonts for better privacy and performance -->

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
    </script>

    <style>
        /* Prevent flash of unstyled content */
        body {
            visibility: hidden;
        }
        
        body.loaded {
            visibility: visible;
        }
        
        .album-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }
        
        .album-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .album-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        /* Remove hover effects on touch devices for better performance */
        @media (hover: none) and (pointer: coarse) {
            .album-card:hover {
                transform: none;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        }
        
        .album-cover {
            position: relative;
            width: 100%;
            height: 200px;
            background: #f3f4f6;
            overflow: hidden;
        }
        
        .album-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .album-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .album-card:hover .album-overlay {
            opacity: 1;
        }

        /* Always show overlay on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .album-overlay {
                opacity: 1;
            }
        }
        
        .play-button {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
        }
        
        .stars {
            position: absolute;
            top: 8px;
            left: 8px;
            display: flex;
            gap: 2px;
        }
        
        .star {
            color: #fbbf24;
            font-size: 16px;
        }
        
        .star.empty {
            color: #d1d5db;
        }
        
        .star.half {
            color: #fbbf24;
            position: relative;
        }
        
        .star.half:before {
            content: '★';
            position: absolute;
            left: 0;
            width: 50%;
            overflow: hidden;
            color: #fbbf24;
        }
        
        .album-info {
            padding: 1rem;
        }
        
        .album-artist {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .album-title {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .album-link {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.75rem;
        }
        
        .album-link:hover {
            text-decoration: underline;
        }
        
        .edit-comment-btn {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0,0,0,0.8);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .album-card:hover .edit-comment-btn {
            opacity: 1;
        }

        /* Always show edit button on touch devices */
        @media (hover: none) and (pointer: coarse) {
            .edit-comment-btn {
                opacity: 1;
            }
        }
        
        .header-bar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        
        .filter-select, .sort-select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
        }
        
        .refresh-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        
        .refresh-btn:hover {
            background: #059669;
        }
        
        .refresh-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 24px 24px 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .modal-body {
            padding: 16px 24px;
        }

        .modal-footer {
            padding: 16px 24px 24px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 8px;
        }

        .modal-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 8px;
            resize: vertical;
            min-height: 100px;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .modal-message {
            line-height: 1.6;
            color: #374151;
            white-space: pre-line;
        }

        .modal-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        /* Music Player Styles */
        .music-player {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            padding: 8px 12px;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 999;
            transform: translateY(100%);
            transition: transform 0.3s ease-in-out;
        }

        .music-player.visible {
            transform: translateY(0);
        }

        /* Dynamic bottom spacing when music player is visible */
        body.music-player-active {
            /* Will be set dynamically by JavaScript */
        }

        @media (min-width: 640px) {
            .music-player {
                padding: 16px;
            }
        }

        .player-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 8px;
            align-items: center;
        }

        @media (min-width: 640px) {
            .player-content {
                gap: 16px;
            }
        }

        .track-info {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        @media (min-width: 640px) {
            .track-info {
                gap: 12px;
            }
        }

        .track-artwork {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
            background: #374151;
        }

        @media (min-width: 640px) {
            .track-artwork {
                width: 48px;
                height: 48px;
            }
        }

        .track-details {
            min-width: 0;
        }

        .track-title {
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (min-width: 640px) {
            .track-title {
                font-size: 14px;
            }
        }

        .track-artist {
            font-size: 11px;
            color: #9ca3af;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (min-width: 640px) {
            .track-artist {
                font-size: 12px;
            }
        }

        .player-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (min-width: 640px) {
            .player-controls {
                gap: 16px;
            }
        }

        .control-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 6px;
            border-radius: 50%;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        @media (min-width: 640px) {
            .control-btn {
                padding: 8px;
                font-size: 16px;
            }
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .play-pause-btn {
            width: 48px;
            height: 48px;
            background: #3b82f6;
            font-size: 18px;
        }

        .play-pause-btn:hover {
            background: #2563eb;
        }

        .skip-btn {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .progress-bar {
            flex: 1;
            height: 4px;
            background: #374151;
            border-radius: 2px;
            cursor: pointer;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: #3b82f6;
            border-radius: 2px;
            transition: width 0.1s;
        }

        .time-display {
            font-size: 12px;
            color: #9ca3af;
            font-family: monospace;
            min-width: 40px;
        }

        .volume-controls {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .playlist-toggle {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .playlist-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .playlist-panel {
            position: absolute;
            bottom: 100%;
            right: 0;
            width: 300px;
            max-height: 400px;
            background: #1f2937;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
            display: none;
            overflow-y: auto;
        }

        .playlist-panel.visible {
            display: block;
        }

        .playlist-header {
            padding: 16px;
            border-bottom: 1px solid #374151;
            font-weight: 600;
        }

        .playlist-track {
            padding: 12px 16px;
            border-bottom: 1px solid #374151;
            cursor: pointer;
            transition: background 0.2s;
        }

        .playlist-track:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .playlist-track.current {
            background: rgba(59, 130, 246, 0.2);
        }

        .playlist-track-title {
            font-size: 14px;
            font-weight: 500;
        }

        .playlist-track-artist {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Floating music button when player is minimized */
        .floating-music-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            z-index: 998;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .floating-music-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.6);
        }

        .floating-music-btn.visible {
            display: flex;
        }

        @media (max-width: 768px) {
            .player-content {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            /* First row: Track info spans full width */
            .track-info {
                width: 100%;
                justify-content: flex-start;
            }
            
            /* Second row: All controls in a single flex row */
            .player-controls {
                display: flex;
                align-items: center;
                width: 100%;
                gap: 8px;
            }
            
            /* Progress container takes available space */
            .progress-container {
                flex: 1;
                gap: 6px;
            }
            
            .progress-container .time-display {
                font-size: 12px;
                min-width: 35px;
            }
            
            .playlist-panel {
                width: 100%;
                right: auto;
                left: 0;
            }
            
            .floating-music-btn {
                width: 50px;
                height: 50px;
                font-size: 18px;
                bottom: 15px;
                right: 15px;
            }
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .loading.show {
            display: block;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('header-logo-40.png') }}" alt="Camp Counselor" class="w-10 h-10">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ config('app.name', 'Camp Counselor') }}
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="showAbout()" class="text-gray-600 hover:text-gray-900 transition-colors cursor-pointer" title="About Camp Counselor">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- About Modal -->
    <div id="aboutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-2">
        <div class="bg-gray-50 rounded-lg w-full max-w-lg h-[90vh] shadow-xl flex flex-col" style="display: flex; flex-direction: column; height: 90vh;">
                <!-- Scrollable Content Area -->
                <div class="overflow-y-auto flex-1 p-6" style="overflow-y: auto; flex: 1;">
                    <div class="text-center mb-8">
                        <!-- Logo -->
                        <img src="{{ asset('apple-touch-icon.png') }}" alt="Camp Counselor" class="w-20 h-20 mx-auto mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Camp Counselor</h2>
                        <p class="text-gray-600 mt-2">Bandcamp.com Wishlist Manager</p>
                        <p class="text-sm text-gray-500 mt-2">Version {{ config('app.version') }}</p>
                    </div>

                    <!-- Author -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Author</h3>
                        <p class="text-gray-700">Marcus Dillavou</p>
                    </div>

                    <!-- Links -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Links</h3>
                        <div class="space-y-3">
                            <a href="https://line72.net/software/camp-counselor" target="_blank" 
                               class="flex items-center text-blue-600 hover:text-blue-800 transition-colors p-2 rounded-md hover:bg-blue-50">
                                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Official Website
                            </a>
                            <a href="https://github.com/line72/campcounselor-web/issues/new" target="_blank" 
                               class="flex items-center text-blue-600 hover:text-blue-800 transition-colors p-2 rounded-md hover:bg-blue-50">
                                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L4.182 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Report an Issue
                            </a>
                        </div>
                    </div>

                    <!-- Legal -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Legal</h3>
                        <div class="text-sm text-gray-600 space-y-3 leading-relaxed">
                            <p>Copyright © Marcus Dillavou &lt;<a href="mailto:line72@line72.net" class="text-blue-600 hover:text-blue-800">line72@line72.net</a>&gt;</p>
                            <p>This software is released under the 
                                <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    GNU General Public License v3.0 or later
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Static Close Button - Always Visible -->
                <div class="border-t border-gray-200 p-4 bg-gray-50 flex-shrink-0" style="flex-shrink: 0;">
                    <button id="closeAbout" class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
    </div>

    <!-- Modal for editing comments -->
    <div id="commentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Edit Album Rating & Comment</h3>
            <form id="commentForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating (-1=Unrated, 0-10)</label>
                    <div class="flex items-center gap-4">
                        <input type="range" id="ratingSlider" min="-1" max="10" step="1" value="-1" 
                               class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                        <div class="flex items-center gap-2">
                            <span id="ratingValue" class="text-lg font-bold min-w-[3rem] text-center">-1</span>
                            <div id="ratingStars" class="flex gap-1"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
                    <textarea id="comment" name="comment" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelComment" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generic Modal -->
    <div id="genericModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Modal Title</h2>
            </div>
            <div class="modal-body">
                <div id="modalMessage" class="modal-message"></div>
                <div id="modalInput" style="display: none;">
                    <label class="modal-label" id="modalInputLabel">Input:</label>
                    <input type="text" id="modalInputField" class="modal-input" placeholder="">
                </div>
                <div id="modalTextarea" style="display: none;">
                    <label class="modal-label" id="modalTextareaLabel">Comment:</label>
                    <textarea id="modalTextareaField" class="modal-textarea" placeholder=""></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="modalCancel" class="btn btn-secondary">Cancel</button>
                <button type="button" id="modalConfirm" class="btn btn-primary">OK</button>
            </div>
        </div>
    </div>

    <!-- Music Player -->
    <div id="musicPlayer" class="music-player">
        <div class="player-content">
            <!-- Track Info -->
            <div class="track-info">
                <img id="playerArtwork" class="track-artwork" src="" alt="Album artwork">
                <div class="track-details">
                    <div id="playerTrackTitle" class="track-title">No track selected</div>
                    <div id="playerTrackArtist" class="track-artist">Select an album to play</div>
                </div>
            </div>

            <!-- Player Controls -->
            <div class="player-controls">
                <button id="prevBtn" class="control-btn skip-btn" title="Previous track">⏮</button>
                <button id="playPauseBtn" class="control-btn play-pause-btn" title="Play/Pause">▶</button>
                <button id="nextBtn" class="control-btn skip-btn" title="Next track">⏭</button>
                
                <div class="progress-container">
                    <span id="currentTime" class="time-display">0:00</span>
                    <div id="progressBar" class="progress-bar">
                        <div id="progressFill" class="progress-fill"></div>
                    </div>
                    <span id="totalTime" class="time-display">0:00</span>
                </div>
                
                <button id="playlistBtn" class="playlist-toggle" title="Playlist">📋</button>
                <button id="minimizeBtn" class="control-btn" title="Minimize Player">⬇️</button>
            </div>
        </div>

        <!-- Playlist Panel (outside player content) -->
        <div id="playlistPanel" class="playlist-panel">
            <div class="playlist-header">
                <span id="playlistTitle">Current Playlist</span>
                <button onclick="closePlaylist()" style="float: right; background: none; border: none; color: white;">✕</button>
            </div>
            <div id="playlistTracks"></div>
        </div>
        </div>
        
        <!-- Hidden audio element -->
        <audio id="audioPlayer" preload="metadata"></audio>
    </div>

    <!-- Floating Music Button (shown when player is minimized) -->
    <button id="floatingMusicBtn" class="floating-music-btn" title="Show Music Player">🎵</button>

    <script>
        // Global variables
        let currentAlbumId = null;
        let currentRating = 0;

        // Music Player variables
        let musicPlayer = null;
        let audioPlayer = null;
        let floatingMusicBtn = null;
        let currentPlaylist = [];
        let currentTrackIndex = 0;
        let isPlaying = false;
        let isDraggingProgress = false;

        // Initialize when DOM and stylesheets are fully loaded
        function initializeApp() {
            // Show the body now that everything is loaded
            document.body.classList.add('loaded');
            
            initializeEventListeners();
            initializeModals();
            
            // Hide server-side pagination when JavaScript takes over
            const serverPagination = document.getElementById('serverPagination');
            if (serverPagination) {
                serverPagination.style.display = 'none';
            }
            
                        // Load albums with JavaScript pagination
            loadAlbums();

            // Initialize music player
            initializeMusicPlayer();
            
            // Update player padding on resize
            window.addEventListener('resize', updateBodyPaddingForPlayer);
        }

        // Wait for both DOM and stylesheets to be fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Wait a bit more for stylesheets to load
                if (document.readyState === 'complete') {
                    initializeApp();
                } else {
                    window.addEventListener('load', initializeApp);
                }
            });
        } else if (document.readyState === 'interactive') {
            window.addEventListener('load', initializeApp);
        } else {
            // Document is already complete
            initializeApp();
        }

        // Modal Functions
        function initializeModals() {
            const modal = document.getElementById('genericModal');
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            
            // Close modal with Escape key (but don't interfere with promise-based modals)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    // Only close if there are no active promise handlers
                    const confirmBtn = document.getElementById('modalConfirm');
                    const cancelBtn = document.getElementById('modalCancel');
                    
                    // Check if buttons have active event listeners (promise-based modals)
                    // If so, let them handle the escape key
                    if (!confirmBtn.onclick && !cancelBtn.onclick) {
                        closeModal();
                    }
                }
            });
        }

        function showAlert(title, message, type = 'info') {
            const modal = document.getElementById('genericModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalInput = document.getElementById('modalInput');
            const modalTextarea = document.getElementById('modalTextarea');
            const confirmBtn = document.getElementById('modalConfirm');
            const cancelBtn = document.getElementById('modalCancel');
            
            // Reset modal
            modalInput.style.display = 'none';
            modalTextarea.style.display = 'none';
            cancelBtn.style.display = 'none';
            
            // Set content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            
            // Set button style based on type
            confirmBtn.className = 'btn ' + (type === 'success' ? 'btn-success' : type === 'danger' ? 'btn-danger' : 'btn-primary');
            confirmBtn.textContent = 'OK';
            
            // Show modal
            modal.style.display = 'block';
            
            return new Promise((resolve) => {
                const handleConfirm = () => {
                    cleanup();
                    closeModal();
                    resolve(true);
                };
                
                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                };
                
                // Remove any existing listeners and add new ones
                confirmBtn.removeEventListener('click', handleConfirm);
                confirmBtn.addEventListener('click', handleConfirm);
            });
        }

        function showPrompt(title, message, placeholder = '', defaultValue = '') {
            const modal = document.getElementById('genericModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalInput = document.getElementById('modalInput');
            const modalInputField = document.getElementById('modalInputField');
            const modalInputLabel = document.getElementById('modalInputLabel');
            const modalTextarea = document.getElementById('modalTextarea');
            const confirmBtn = document.getElementById('modalConfirm');
            const cancelBtn = document.getElementById('modalCancel');
            
            // Reset modal
            modalTextarea.style.display = 'none';
            
            // Set content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalInputLabel.textContent = 'Input:';
            modalInputField.placeholder = placeholder;
            modalInputField.value = defaultValue;
            
            // Show input and buttons
            modalInput.style.display = 'block';
            cancelBtn.style.display = 'inline-block';
            confirmBtn.className = 'btn btn-primary';
            confirmBtn.textContent = 'OK';
            
            // Show modal
            modal.style.display = 'block';
            
            // Focus input
            setTimeout(() => modalInputField.focus(), 100);
            
            return new Promise((resolve) => {
                const handleConfirm = () => {
                    const value = modalInputField.value.trim();
                    cleanup();
                    closeModal();
                    resolve(value || null);
                };
                
                const handleCancel = () => {
                    cleanup();
                    closeModal();
                    resolve(null);
                };
                
                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                    cancelBtn.removeEventListener('click', handleCancel);
                    modalInputField.removeEventListener('keydown', handleKeydown);
                };
                
                const handleKeydown = (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleConfirm();
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        handleCancel();
                    }
                };
                
                // Remove any existing listeners and add new ones
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
                modalInputField.removeEventListener('keydown', handleKeydown);
                
                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
                modalInputField.addEventListener('keydown', handleKeydown);
            });
        }

        function showConfirm(title, message, confirmText = 'OK', cancelText = 'Cancel') {
            const modal = document.getElementById('genericModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalInput = document.getElementById('modalInput');
            const modalTextarea = document.getElementById('modalTextarea');
            const confirmBtn = document.getElementById('modalConfirm');
            const cancelBtn = document.getElementById('modalCancel');
            
            // Reset modal
            modalInput.style.display = 'none';
            modalTextarea.style.display = 'none';
            
            // Set content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            
            // Show buttons
            cancelBtn.style.display = 'inline-block';
            confirmBtn.className = 'btn btn-primary';
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            
            // Show modal
            modal.style.display = 'block';
            
            return new Promise((resolve) => {
                const handleConfirm = () => {
                    cleanup();
                    closeModal();
                    resolve(true);
                };
                
                const handleCancel = () => {
                    cleanup();
                    closeModal();
                    resolve(false);
                };
                
                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                    cancelBtn.removeEventListener('click', handleCancel);
                };
                
                // Remove any existing listeners and add new ones
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
                
                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
            });
        }

        function closeModal() {
            const modal = document.getElementById('genericModal');
            modal.style.display = 'none';
        }

        // Music Player Functions
        function initializeMusicPlayer() {
            musicPlayer = document.getElementById('musicPlayer');
            audioPlayer = document.getElementById('audioPlayer');
            floatingMusicBtn = document.getElementById('floatingMusicBtn');
            
            if (!musicPlayer || !audioPlayer || !floatingMusicBtn) return;

            // Audio event listeners
            audioPlayer.addEventListener('loadedmetadata', updateTrackInfo);
            audioPlayer.addEventListener('timeupdate', updateProgress);
            audioPlayer.addEventListener('ended', playNext);
            audioPlayer.addEventListener('error', handleAudioError);

            // Control button listeners
            document.getElementById('playPauseBtn').addEventListener('click', togglePlayPause);
            document.getElementById('prevBtn').addEventListener('click', playPrevious);
            document.getElementById('nextBtn').addEventListener('click', playNext);
            document.getElementById('playlistBtn').addEventListener('click', togglePlaylist);
            document.getElementById('minimizeBtn').addEventListener('click', hideMusicPlayer);
            
            // Floating button listener
            floatingMusicBtn.addEventListener('click', showMusicPlayer);
            
            // Progress bar listeners
            const progressBar = document.getElementById('progressBar');
            progressBar.addEventListener('mousedown', startProgressDrag);
            progressBar.addEventListener('click', seekToPosition);
            
            // Global mouse listeners for dragging
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
        }

        function updateBodyPaddingForPlayer() {
            if (musicPlayer && document.body.classList.contains('music-player-active')) {
                // Wait a frame for the player to be fully rendered
                requestAnimationFrame(() => {
                    const playerHeight = musicPlayer.offsetHeight;
                    const extraPadding = 20; // Add some extra space for safety
                    document.body.style.paddingBottom = (playerHeight + extraPadding) + 'px';
                });
            } else {
                document.body.style.paddingBottom = '';
            }
        }

        function showMusicPlayer() {
            if (musicPlayer && floatingMusicBtn) {
                musicPlayer.classList.add('visible');
                document.body.classList.add('music-player-active');
                floatingMusicBtn.classList.remove('visible');
                
                // Update padding based on actual player height
                updateBodyPaddingForPlayer();
            }
        }

        function hideMusicPlayer() {
            if (musicPlayer && floatingMusicBtn) {
                musicPlayer.classList.remove('visible');
                document.body.classList.remove('music-player-active');
                
                // Remove dynamic padding
                document.body.style.paddingBottom = '';
                
                // Only show floating button if there's an active playlist
                if (currentPlaylist.length > 0) {
                    floatingMusicBtn.classList.add('visible');
                }
            }
        }

        function loadPlaylist(tracks, albumInfo) {
            currentPlaylist = tracks.map(track => ({
                ...track,
                albumArtist: albumInfo.artist,
                albumTitle: albumInfo.album,
                albumArtwork: albumInfo.artwork_url || albumInfo.thumbnail_url
            }));
            currentTrackIndex = 0;
            
            updatePlaylistDisplay();
            
            if (currentPlaylist.length > 0) {
                loadTrack(0);
                showMusicPlayer();
            }
        }

        function loadTrack(index) {
            if (index < 0 || index >= currentPlaylist.length) return;
            
            currentTrackIndex = index;
            const track = currentPlaylist[index];
            
            // Update audio source
            audioPlayer.src = track.url;
            
            // Update UI
            document.getElementById('playerTrackTitle').textContent = track.name;
            document.getElementById('playerTrackArtist').textContent = `${track.albumArtist} - ${track.albumTitle}`;
            
            const artwork = document.getElementById('playerArtwork');
            artwork.src = track.albumArtwork || '';
            artwork.alt = `${track.albumTitle} artwork`;
            
            // Update playlist highlighting
            updatePlaylistDisplay();
            
            // Update controls
            updateControlButtons();
        }

        function togglePlayPause() {
            if (!audioPlayer.src) return;
            
            if (isPlaying) {
                audioPlayer.pause();
                isPlaying = false;
                document.getElementById('playPauseBtn').textContent = '▶';
            } else {
                audioPlayer.play().then(() => {
                    isPlaying = true;
                    document.getElementById('playPauseBtn').textContent = '⏸';
                }).catch(error => {
                    console.error('Error playing audio:', error);
                    showAlert('Playback Error', 'Unable to play this track. It may not be available for streaming.', 'danger');
                });
            }
        }

        function playNext() {
            if (currentTrackIndex < currentPlaylist.length - 1) {
                loadTrack(currentTrackIndex + 1);
                if (isPlaying) {
                    audioPlayer.play();
                }
            }
        }

        function playPrevious() {
            if (currentTrackIndex > 0) {
                loadTrack(currentTrackIndex - 1);
                if (isPlaying) {
                    audioPlayer.play();
                }
            }
        }

        function updateTrackInfo() {
            const duration = audioPlayer.duration;
            if (duration && !isNaN(duration)) {
                document.getElementById('totalTime').textContent = formatTime(duration);
            }
        }

        function updateProgress() {
            if (isDraggingProgress) return;
            
            const currentTime = audioPlayer.currentTime;
            const duration = audioPlayer.duration;
            
            if (duration && !isNaN(duration) && !isNaN(currentTime)) {
                const progress = (currentTime / duration) * 100;
                document.getElementById('progressFill').style.width = progress + '%';
                document.getElementById('currentTime').textContent = formatTime(currentTime);
            }
        }

        function updateControlButtons() {
            document.getElementById('prevBtn').disabled = currentTrackIndex === 0;
            document.getElementById('nextBtn').disabled = currentTrackIndex === currentPlaylist.length - 1;
        }

        function updatePlaylistDisplay() {
            const container = document.getElementById('playlistTracks');
            if (!container) return;
            
            container.innerHTML = currentPlaylist.map((track, index) => `
                <div class="playlist-track ${index === currentTrackIndex ? 'current' : ''}" 
                     onclick="playTrackFromPlaylist(${index})">
                    <div class="playlist-track-title">${track.name}</div>
                    <div class="playlist-track-artist">${track.albumArtist}</div>
                </div>
            `).join('');
            
            document.getElementById('playlistTitle').textContent = 
                currentPlaylist.length > 0 ? `${currentPlaylist[0].albumTitle} (${currentPlaylist.length} tracks)` : 'Empty Playlist';
        }

        function playTrackFromPlaylist(index) {
            loadTrack(index);
            if (audioPlayer.src) {
                audioPlayer.play().then(() => {
                    isPlaying = true;
                    document.getElementById('playPauseBtn').textContent = '⏸';
                });
            }
        }

        function togglePlaylist() {
            const panel = document.getElementById('playlistPanel');
            panel.classList.toggle('visible');
        }

        function closePlaylist() {
            document.getElementById('playlistPanel').classList.remove('visible');
        }

        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        function handleAudioError(e) {
            console.error('Audio error:', e);
            isPlaying = false;
            document.getElementById('playPauseBtn').textContent = '▶';
        }

        // Progress bar dragging
        function startProgressDrag(e) {
            isDraggingProgress = true;
            seekToPosition(e);
        }

        function seekToPosition(e) {
            if (!audioPlayer.duration) return;
            
            const progressBar = document.getElementById('progressBar');
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            const newTime = percent * audioPlayer.duration;
            
            audioPlayer.currentTime = Math.max(0, Math.min(newTime, audioPlayer.duration));
            updateProgress();
        }



        // Mouse event handlers
        function handleMouseMove(e) {
            if (isDraggingProgress) {
                seekToPosition(e);
            }
        }

        function handleMouseUp() {
            isDraggingProgress = false;
        }

        function initializeEventListeners() {
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadAlbums();
                    }, 300);
                });
            }

            // Filter and sort functionality
            const filterSelect = document.getElementById('filterSelect');
            const sortSelect = document.getElementById('sortSelect');
            
            if (filterSelect) {
                // Atempt to restore a saved filter
                const savedFilter = localStorage.getItem('album_filter');
                if (savedFilter) {
                    filterSelect.value = savedFilter;
                }
                
                filterSelect.addEventListener('change', (evt) => {
                    localStorage.setItem('album_filter', evt.target.value);
                    loadAlbums();
                });
            }
            
            if (sortSelect) {
                // Atempt to restore a saved filter
                const savedSort = localStorage.getItem('album_sort');
                if (savedSort) {
                    sortSelect.value = savedSort;
                }
                
                sortSelect.addEventListener('change', (evt) => {
                    localStorage.setItem('album_sort', evt.target.value);
                    loadAlbums();
                });
            }

            // Refresh buttons (handle multiple buttons with same functionality)
            const refreshBtns = document.querySelectorAll('#refreshBtn, .refresh-btn');
            refreshBtns.forEach(refreshBtn => {
                if (refreshBtn) {
                    refreshBtn.addEventListener('click', refreshAlbums);
                    
                    // Right-click to clear saved credentials
                    refreshBtn.addEventListener('contextmenu', async (e) => {
                        e.preventDefault();
                        
                        const savedInput = localStorage.getItem('bandcamp_user_input');
                        if (savedInput) {
                            const confirmed = await showConfirm(
                                'Clear Saved Info',
                                `Clear your saved Bandcamp credentials: "${savedInput}"?
                                
This will remove the saved username/fan ID from your browser.`,
                                'Clear Info',
                                'Cancel'
                            );
                            
                            if (confirmed) {
                                clearSavedBandcampInfo();
                                await showAlert('Cleared', 'Saved Bandcamp credentials have been cleared.', 'success');
                            }
                        } else {
                            await showAlert('No Saved Info', 'No Bandcamp credentials are currently saved.', 'info');
                        }
                    });
                }
            });

            // Edit comment buttons using event delegation
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('edit-comment-btn')) {
                    const albumId = parseInt(e.target.dataset.albumId);
                    const rating = parseInt(e.target.dataset.rating);
                    const comment = e.target.dataset.comment || '';
                    editComment(albumId, rating, comment);
                }
            });

            // Modal functionality
            const modal = document.getElementById('commentModal');
            const cancelBtn = document.getElementById('cancelComment');
            const commentForm = document.getElementById('commentForm');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeCommentModal);
            }

            if (commentForm) {
                commentForm.addEventListener('submit', saveComment);
            }

            // About modal functionality
            const aboutModal = document.getElementById('aboutModal');
            const closeAboutBtn = document.getElementById('closeAbout');
            
            if (closeAboutBtn) {
                closeAboutBtn.addEventListener('click', function() {
                    aboutModal.classList.add('hidden');
                    aboutModal.classList.remove('flex');
                    // Restore background scrolling
                    document.body.style.overflow = '';
                });
            }

            // Close about modal when clicking outside
            if (aboutModal) {
                aboutModal.addEventListener('click', function(e) {
                    if (e.target === aboutModal) {
                        aboutModal.classList.add('hidden');
                        aboutModal.classList.remove('flex');
                        // Restore background scrolling
                        document.body.style.overflow = '';
                    }
                });
            }

            // Rating slider in modal
            const ratingSlider = document.getElementById('ratingSlider');
            const ratingValue = document.getElementById('ratingValue');
            const ratingStars = document.getElementById('ratingStars');
            
            if (ratingSlider) {
                ratingSlider.addEventListener('input', function() {
                    currentRating = parseInt(this.value);
                    updateRatingDisplay();
                });
            }

            // Close modal when clicking outside
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeCommentModal();
                    }
                });
            }
        }

        function loadAlbums(page = 1) {
            const searchTerm = document.getElementById('searchInput')?.value || '';
            const filter = localStorage.getItem('album_filter') || document.getElementById('filterSelect')?.value || 'all';
            const sort = localStorage.getItem('album_sort') || document.getElementById('sortSelect')?.value || 'artist_asc';

            const params = new URLSearchParams({
                search: searchTerm,
                filter: filter,
                sort: sort,
                page: page
            });

            showLoading(true);

            fetch(`/?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                renderAlbums(data.data);
                renderPagination(data);
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading albums:', error);
                showLoading(false);
            });
        }

        function renderAlbums(albums) {
            const container = document.getElementById('albumsContainer');
            if (!container) return;

            container.innerHTML = albums.map(album => `
                <div class="album-card">
                    <div class="album-cover">
                        <img src="${album.thumbnail_url || '/images/default-album.png'}" 
                             alt="${album.album}" 
                             onerror="this.src='/images/default-album.png'">
                        <div class="stars">
                            ${generateStars(album.rating)}
                        </div>
                        <div class="album-overlay">
                            <button class="play-button" onclick="playAlbum(${album.id})">▶</button>
                        </div>
                        <button class="edit-comment-btn" data-album-id="${album.id}" data-rating="${album.rating}" data-comment="${album.comment || ''}">
                            Edit Comment
                        </button>
                    </div>
                    <div class="album-info">
                        <div class="album-artist">${album.artist}</div>
                        <div class="album-title">${album.album}</div>
                        <a href="${album.url}" target="_blank" class="album-link">View on Bandcamp</a>
                    </div>
                </div>
            `).join('');
        }

        function renderPagination(paginationData) {
            const container = document.getElementById('paginationContainer');
            if (!container) return;

            if (!paginationData.links || paginationData.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            const currentPage = paginationData.current_page;
            const lastPage = paginationData.last_page;
            const prevPage = currentPage > 1 ? currentPage - 1 : null;
            const nextPage = currentPage < lastPage ? currentPage + 1 : null;

            let paginationHTML = '<div class="flex justify-center items-center space-x-1 sm:space-x-2 mt-6 mb-4">';

            // First button (only show if not on first page)
            if (currentPage > 1) {
                paginationHTML += `<button onclick="loadAlbums(1)" class="px-2 sm:px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">First</button>`;
            }

            // Previous button
            if (prevPage) {
                paginationHTML += `<button onclick="loadAlbums(${prevPage})" class="px-2 sm:px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Prev</button>`;
            } else {
                paginationHTML += `<button disabled class="px-2 sm:px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md text-gray-400 cursor-not-allowed">Prev</button>`;
            }

            // Current page indicator
            paginationHTML += `<span class="px-3 py-2 text-sm bg-blue-600 text-white border border-blue-600 rounded-md">${currentPage}</span>`;

            // Page info (show "of X" on larger screens)
            paginationHTML += `<span class="px-2 py-2 text-sm text-gray-500 hidden sm:inline">of ${lastPage}</span>`;

            // Next button
            if (nextPage) {
                paginationHTML += `<button onclick="loadAlbums(${nextPage})" class="px-2 sm:px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
            } else {
                paginationHTML += `<button disabled class="px-2 sm:px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md text-gray-400 cursor-not-allowed">Next</button>`;
            }

            // Last button (only show if not on last page)
            if (currentPage < lastPage) {
                paginationHTML += `<button onclick="loadAlbums(${lastPage})" class="px-2 sm:px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Last</button>`;
            }

            paginationHTML += '</div>';
            container.innerHTML = paginationHTML;
        }

        function generateStars(rating) {
            let stars = '';
            
            // Handle unrated (-1) case
            if (rating < 0) {
                for (let i = 1; i <= 5; i++) {
                    stars += `<span class="star empty">☆</span>`;
                }
                return stars;
            }
            
            // Convert 0-10 rating to 0-5 star display
            const starRating = rating / 2;
            
            for (let i = 1; i <= 5; i++) {
                if (starRating >= i) {
                    // Full star
                    stars += `<span class="star">★</span>`;
                } else if (starRating >= i - 0.5) {
                    // Half star
                    stars += `<span class="star half">☆</span>`;
                } else {
                    // Empty star
                    stars += `<span class="star empty">☆</span>`;
                }
            }
            return stars;
        }

        function showLoading(show) {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.classList.toggle('show', show);
            }
        }

        function editComment(albumId, rating, comment) {
            currentAlbumId = albumId;
            currentRating = rating;
            
            document.getElementById('comment').value = comment;
            updateRatingDisplay();
            
            const modal = document.getElementById('commentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCommentModal() {
            const modal = document.getElementById('commentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentAlbumId = null;
            currentRating = 0;
        }

        function showAbout() {
            const aboutModal = document.getElementById('aboutModal');
            aboutModal.classList.remove('hidden');
            aboutModal.classList.add('flex');
            // Prevent background scrolling
            document.body.style.overflow = 'hidden';
        }

        // Make showAbout globally accessible
        window.showAbout = showAbout;

        function updateRatingDisplay() {
            const ratingSlider = document.getElementById('ratingSlider');
            const ratingValue = document.getElementById('ratingValue');
            const ratingStars = document.getElementById('ratingStars');
            
            if (ratingSlider) ratingSlider.value = currentRating;
            if (ratingValue) {
                ratingValue.textContent = currentRating === -1 ? 'Unrated' : currentRating;
            }
            if (ratingStars) ratingStars.innerHTML = generateStars(currentRating);
        }

        function saveComment(e) {
            e.preventDefault();
            
            if (!currentAlbumId) return;
            
            const comment = document.getElementById('comment').value;
            
            fetch(`/albums/${currentAlbumId}/rating`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rating: currentRating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateAlbumInGrid(currentAlbumId, currentRating, comment);
                    closeCommentModal();
                } else {
                    showAlert('Error', 'Error saving comment', 'danger');
                }
            })
            .catch(error => {
                console.error('Error saving comment:', error);
                showAlert('Error', 'Error saving comment', 'danger');
            });
        }

        function updateAlbumInGrid(albumId, rating, comment) {
            // Find the album card in the grid by looking for the edit button with the album ID
            const editButton = document.querySelector(`[data-album-id="${albumId}"]`);
            if (!editButton) return;

            // Get the album card (parent of the edit button's parent)
            const albumCard = editButton.closest('.album-card');
            if (!albumCard) return;

            // Update the star display in the card
            const starContainer = albumCard.querySelector('.stars');
            if (starContainer) {
                starContainer.innerHTML = generateStars(rating);
            }

            // Update the edit button's data attributes to reflect the new values
            editButton.setAttribute('data-rating', rating);
            editButton.setAttribute('data-comment', comment || '');
        }

        async function playAlbum(albumId) {
            try {
                // Get album data first
                const albumResponse = await fetch(`/albums/${albumId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!albumResponse.ok) {
                    throw new Error('Failed to fetch album data');
                }
                
                const albumData = await albumResponse.json();
                
                // Get tracks for the album
                const tracksResponse = await fetch('/api/bandcamp/tracks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        url: albumData.url,
                        artist: albumData.artist,
                        album: albumData.album
                    })
                });
                
                if (!tracksResponse.ok) {
                    throw new Error('Failed to fetch tracks');
                }
                
                const tracksData = await tracksResponse.json();
                
                if (tracksData.success && tracksData.tracks.length > 0) {
                    // Load the tracks into the music player
                    loadPlaylist(tracksData.tracks, albumData);
                    
                    // Auto-play the first track
                    setTimeout(() => {
                        togglePlayPause();
                    }, 500);
                    
                } else {
                    await showAlert('No Tracks Available', 'This album doesn\'t have any playable tracks available.', 'info');
                }
                
            } catch (error) {
                console.error('Error playing album:', error);
                await showAlert('Playback Error', 'Unable to load album tracks. Please try again.', 'danger');
            }
        }

        function clearSavedBandcampInfo() {
            localStorage.removeItem('bandcamp_user_input');
            localStorage.removeItem('bandcamp_fan_id');
        }

        async function refreshAlbums() {
            // Check if we have a saved fan ID in localStorage
            const savedFanId = localStorage.getItem('bandcamp_fan_id');
            
            let input;
            
            if (savedFanId) {
                // We have a saved fan ID, use it directly without prompting
                input = localStorage.getItem('bandcamp_user_input');
                performRefresh(savedFanId);
            } else {
                // No saved fan ID, check for saved input
                const savedInput = localStorage.getItem('bandcamp_user_input');
                
                if (savedInput) {
                    // We have a saved input, ask if they want to use it or enter a new one
                    const useStored = await showConfirm(
                        'Refresh Albums',
                        `Use your saved Bandcamp info: "${savedInput}"?
                        
Click "Yes" to refresh with saved info, or "No" to enter different credentials.

💡 Tip: Right-click the refresh button to clear saved credentials.`,
                        'Use Saved Info',
                        'Enter New Info'
                    );
                    
                    if (useStored) {
                        input = savedInput;
                    } else {
                        // User wants to enter new info
                        input = await showPrompt(
                            'Refresh Albums',
                            `Enter your Bandcamp fan ID or username:

If you have a username (like "myusername" from https://bandcamp.com/myusername):
• Enter just the username and we'll look up your fan ID

If you already know your fan ID (a long number):
• Enter the fan ID directly`,
                            'Username or Fan ID',
                            savedInput // Pre-fill with saved value
                        );
                    }
                } else {
                    // No saved info, prompt for input
                    input = await showPrompt(
                        'Refresh Albums',
                        `Enter your Bandcamp fan ID or username:

If you have a username (like "myusername" from https://bandcamp.com/myusername):
• Enter just the username and we'll look up your fan ID

If you already know your fan ID (a long number):
• Enter the fan ID directly`,
                        'Username or Fan ID'
                    );
                }
                
                if (!input) return;
                
                // Save the input to localStorage for next time
                localStorage.setItem('bandcamp_user_input', input.trim());
                
                // Check if input looks like a fan ID (all digits) or username
                const isNumeric = /^\d+$/.test(input.trim());
                
                if (isNumeric) {
                    // Direct fan ID - save it too
                    localStorage.setItem('bandcamp_fan_id', input.trim());
                    performRefresh(input.trim());
                } else {
                    // Username - need to resolve to fan ID first
                    resolveFanIdAndRefresh(input.trim());
                }
            }
        }
        
        async function resolveFanIdAndRefresh(username) {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Looking up fan ID...';
            
            fetch('/api/bandcamp/fan-id', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    username: username
                })
            })
            .then(response => response.json())
            .then(async (data) => {
                if (data.success) {
                    // Save the resolved fan ID to localStorage
                    localStorage.setItem('bandcamp_fan_id', data.fan_id);
                    performRefresh(data.fan_id);
                } else {
                    await showAlert('Fan ID Not Found', `Could not find fan ID for username "${username}".

Please check:
• Your username is correct
• Your Bandcamp profile is public
• Try visiting https://bandcamp.com/${username} in your browser

Error: ${data.message}`, 'danger');
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh Albums';
                }
            })
            .catch(async (error) => {
                console.error('Error resolving fan ID:', error);
                await showAlert('Error', 'Error looking up fan ID', 'danger');
                refreshBtn.disabled = false;
                refreshBtn.textContent = 'Refresh Albums';
            });
        }
        
        async function performRefresh(fanId) {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.disabled = true;
            refreshBtn.textContent = 'Starting refresh...';
            
            try {
                // Start the refresh task
                const response = await fetch('/api/bandcamp/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        fan_id: fanId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Start polling for task status
                    await pollTaskStatus(data.task_id, fanId);
                } else if (response.status === 409) {
                    // Another refresh is already in progress
                    await showAlert('Refresh In Progress', `⏳ ${data.message}

You can wait for the current refresh to complete or try again later.`, 'info');
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh Albums';
                } else {
                    await showAlert('Refresh Failed', `❌ ${data.message}

Please check:
• Your fan ID is correct: ${fanId}
• Your Bandcamp profile is public
• You have albums in your collection or wishlist`, 'danger');
                    refreshBtn.disabled = false;
                    refreshBtn.textContent = 'Refresh Albums';
                }
            } catch (error) {
                console.error('Error starting refresh:', error);
                await showAlert('Connection Error', '❌ Error starting album refresh. Please check your internet connection and try again.', 'danger');
                refreshBtn.disabled = false;
                refreshBtn.textContent = 'Refresh Albums';
            }
        }

        async function pollTaskStatus(taskId, fanId) {
            const refreshBtn = document.getElementById('refreshBtn');
            let pollCount = 0;
            const maxPolls = 300; // 5 minutes max (300 * 1 second intervals)
            
            const poll = async () => {
                try {
                    const response = await fetch(`/api/bandcamp/status/${taskId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success && data.task) {
                        const task = data.task;
                        
                        // Update button text with current status
                        refreshBtn.textContent = task.message || 'Refreshing...';
                        
                        if (task.status === 'completed') {
                            // Task completed successfully
                            const taskData = task.data || {};
                            const totalAlbums = taskData.total_new_albums || 0;
                            const collectionCount = taskData.new_collection_albums || 0;
                            const wishlistCount = taskData.new_wishlist_albums || 0;
                            
                            if (totalAlbums > 0) {
                                await showAlert('Refresh Successful', `🎉 Added ${totalAlbums} new albums:

• ${collectionCount} from your collection
• ${wishlistCount} from your wishlist

Your albums are now loading...`, 'success');
                                loadAlbums(); // Refresh the album grid
                            } else {
                                await showAlert('Refresh Complete', `✅ No new albums were found. This could mean:

• Your collection is already up to date
• You don't have any albums in your Bandcamp collection/wishlist
• The fan ID might be incorrect

Fan ID used: ${fanId}`, 'info');
                            }
                            
                            refreshBtn.disabled = false;
                            refreshBtn.textContent = 'Refresh Albums';
                            return; // Stop polling
                            
                        } else if (task.status === 'failed') {
                            // Task failed
                            await showAlert('Refresh Failed', `❌ ${task.message}

Please check:
• Your fan ID is correct: ${fanId}
• Your Bandcamp profile is public
• You have albums in your collection or wishlist`, 'danger');
                            
                            refreshBtn.disabled = false;
                            refreshBtn.textContent = 'Refresh Albums';
                            return; // Stop polling
                            
                        } else if (task.status === 'running' || task.status === 'pending') {
                            // Task still running, continue polling
                            pollCount++;
                            if (pollCount >= maxPolls) {
                                await showAlert('Refresh Timeout', '⏰ The refresh is taking longer than expected. Please check back later or try again.', 'warning');
                                refreshBtn.disabled = false;
                                refreshBtn.textContent = 'Refresh Albums';
                                return;
                            }
                            
                            // Continue polling after 1 second
                            setTimeout(poll, 1000);
                        }
                    } else {
                        // Task not found or error
                        await showAlert('Refresh Error', `❌ ${data.message || 'Unable to check refresh status'}`, 'danger');
                        refreshBtn.disabled = false;
                        refreshBtn.textContent = 'Refresh Albums';
                    }
                } catch (error) {
                    console.error('Error polling task status:', error);
                    pollCount++;
                    if (pollCount >= maxPolls) {
                        await showAlert('Connection Error', '❌ Lost connection while checking refresh status. Please try again.', 'danger');
                        refreshBtn.disabled = false;
                        refreshBtn.textContent = 'Refresh Albums';
                    } else {
                        // Retry after 2 seconds on error
                        setTimeout(poll, 2000);
                    }
                }
            };

            // Start polling
            poll();
        }
    </script>
</body>
</html>
