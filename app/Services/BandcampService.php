<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BandcampService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.bandcamp_url', 'https://bandcamp.com');
    }

    /**
     * Fetch collection albums for a fan
     */
    public function fetchCollection(string $fanId): array
    {
        $url = $this->baseUrl . '/api/fancollection/1/collection_items';
        return $this->fetchAlbums($url, $fanId);
    }

    /**
     * Fetch wishlist albums for a fan
     */
    public function fetchWishlist(string $fanId): array
    {
        $url = $this->baseUrl . '/api/fancollection/1/wishlist_items';
        return $this->fetchAlbums($url, $fanId);
    }

    /**
     * Get fan ID from username
     */
    public function getFanIdFromUsername(string $username): ?string
    {
        $url = $this->baseUrl . '/' . $username;

        try {
            $response = Http::timeout(10)
                ->retry(2, 1000)
                ->get($url);
            
            if (!$response->successful()) {
                Log::warning("Failed to fetch fan ID from $url - Status: " . $response->status());
                return null;
            }

            $html = $response->body();
            
            // Parse HTML to find pagedata div
            if (preg_match('/<div[^>]*id="pagedata"[^>]*data-blob="([^"]*)"/', $html, $matches)) {
                $blob = html_entity_decode($matches[1]);
                $data = json_decode($blob, true);
                
                if (isset($data['fan_data']['fan_id'])) {
                    return (string) $data['fan_data']['fan_id'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching fan ID from username: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse tracks from album URL
     */
    public function parseTracks(string $artist, string $album, string $url): array
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 1000)
                ->get($url);
            
            if (!$response->successful()) {
                Log::warning("Failed to fetch tracks from $url - Status: " . $response->status());
                return [];
            }

            $html = $response->body();
            
            // Parse HTML to find tralbum data
            if (preg_match('/<script[^>]*data-tralbum="([^"]*)"/', $html, $matches)) {
                $tralbum = html_entity_decode($matches[1]);
                return $this->parseTracksFromTralbum($artist, $album, $tralbum);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error parsing tracks: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch albums from Bandcamp API
     */
    private function fetchAlbums(string $url, string $fanId): array
    {
        $albums = [];
        $token = time() . '.0::a::';
        $done = false;

        $requestCount = 0;
        $maxRequests = config('app.bandcamp_max_requests', 500); // Configurable limit, default 500 (10,000 albums max)
        $emptyResponseCount = 0;
        $maxEmptyResponses = 3; // Stop after 3 consecutive empty responses

        while (!$done && $requestCount < $maxRequests) {
            $requestCount++;
            
            try {
                $response = Http::timeout(15) // Increased to 15 second timeout per request
                    ->retry(3, 2000) // 3 retries with 2 second delay
                    ->post($url, [
                        'count' => 20,
                        'fan_id' => $fanId,
                        'older_than_token' => $token,
                    ]);

                if (!$response->successful()) {
                    Log::error("Bandcamp API request failed with status: " . $response->status() . " for URL: $url");
                    break;
                }

                $data = $response->json();
                
                if (!is_array($data) || !isset($data['items'])) {
                    Log::error("Invalid response format from Bandcamp API");
                    break;
                }

                $newAlbums = $this->parseAlbumsFromResponse($data);
                $albums = array_merge($albums, $newAlbums);

                // Track empty responses to detect end of collection
                if (empty($newAlbums)) {
                    $emptyResponseCount++;
                    Log::info("Empty response $emptyResponseCount/$maxEmptyResponses in request $requestCount");
                } else {
                    $emptyResponseCount = 0; // Reset counter on successful fetch
                    Log::info("Fetched " . count($newAlbums) . " albums in request $requestCount (Total: " . count($albums) . ")");
                }

                // Stop if we get too many consecutive empty responses
                if ($emptyResponseCount >= $maxEmptyResponses) {
                    Log::info("Stopping after $maxEmptyResponses consecutive empty responses");
                    $done = true;
                }

                $token = $data['last_token'] ?? null;
                if (!$token || empty($data['items'])) {
                    $done = true;
                }

                // Add a small delay between requests to be respectful to the API
                if (!$done) {
                    usleep(300000); // Reduced to 0.3 second delay for faster processing
                }

            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('HTTP request failed: ' . $e->getMessage());
                break;
            } catch (\Exception $e) {
                Log::error('Error fetching albums: ' . $e->getMessage());
                break;
            }
        }

        if ($requestCount >= $maxRequests) {
            Log::warning("Reached maximum request limit ($maxRequests) for fan ID: $fanId. Consider increasing BANDCAMP_MAX_REQUESTS in .env");
        }

        Log::info("Completed fetching albums: $requestCount requests, " . count($albums) . " total albums");

        return $albums;
    }

    /**
     * Parse albums from API response
     */
    private function parseAlbumsFromResponse(array $data): array
    {
        $albums = [];

        if (!isset($data['items'])) {
            return $albums;
        }

        foreach ($data['items'] as $item) {
            // Skip if no album_id
            if (empty($item['album_id'])) {
                continue;
            }

            $albums[] = [
                'bandcamp_id' => (string) $item['album_id'],
                'bandcamp_band_id' => (string) $item['band_id'],
                'album' => $item['album_title'] ?? '',
                'artist' => $item['band_name'] ?? '',
                'url' => $item['item_url'] ?? '',
                'thumbnail_url' => $item['item_art']['thumb_url'] ?? null,
                'artwork_url' => $item['item_art']['url'] ?? null,
                'comment' => '', // Default empty comment to match Vala schema
                'created_at' => $this->parseDate($item['added'] ?? null),
                'updated_at' => $this->parseDate($item['updated'] ?? null),
            ];
        }

        return $albums;
    }

    /**
     * Parse tracks from tralbum data
     */
    private function parseTracksFromTralbum(string $artist, string $album, string $tralbum): array
    {
        try {
            $data = json_decode($tralbum, true);
            
            if (!isset($data['trackinfo'])) {
                return [];
            }

            $tracks = [];
            foreach ($data['trackinfo'] as $track) {
                if (empty($track['title']) || empty($track['file']['mp3-128'])) {
                    continue;
                }

                $tracks[] = [
                    'artist' => $artist,
                    'album' => $album,
                    'name' => $track['title'],
                    'url' => $track['file']['mp3-128'],
                    'track_num' => $track['track_num'] ?? 0,
                ];
            }

            // Sort by track number
            usort($tracks, function ($a, $b) {
                return $a['track_num'] <=> $b['track_num'];
            });

            return $tracks;
        } catch (\Exception $e) {
            Log::error('Error parsing tracks from tralbum: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse date string to Carbon instance
     */
    private function parseDate(?string $dateString): int
    {
        if (!$dateString) {
            return time(); // Default to current timestamp if no date provided
        }

        try {
            return Carbon::parse($dateString)->timestamp;
        } catch (\Exception $e) {
            return time(); // Default to current timestamp if parsing fails
        }
    }
}
