<?php

namespace Tests\Unit;

use App\Services\BandcampService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BandcampServiceTest extends TestCase
{
    protected BandcampService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BandcampService();
    }

    public function test_parse_albums_from_response(): void
    {
        $mockResponse = [
            'items' => [
                [
                    'album_id' => 12345,
                    'band_id' => 67890,
                    'album_title' => 'Test Album',
                    'band_name' => 'Test Artist',
                    'item_url' => 'https://testartist.bandcamp.com/album/test-album',
                    'item_art' => [
                        'thumb_url' => 'https://example.com/thumb.jpg',
                        'url' => 'https://example.com/artwork.jpg',
                    ],
                    'added' => '2023-01-15T10:30:00Z',
                    'updated' => '2023-02-20T15:45:00Z',
                ],
                [
                    'album_id' => 0, // Should be skipped
                    'band_id' => 11111,
                    'album_title' => 'Skip This',
                    'band_name' => 'Skip Artist',
                ],
            ],
            'last_token' => 'next_token_123',
        ];

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseAlbumsFromResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $mockResponse);

        $this->assertCount(1, $result);
        $this->assertEquals('12345', $result[0]['bandcamp_id']);
        $this->assertEquals('67890', $result[0]['bandcamp_band_id']);
        $this->assertEquals('Test Album', $result[0]['album']);
        $this->assertEquals('Test Artist', $result[0]['artist']);
        $this->assertEquals('https://testartist.bandcamp.com/album/test-album', $result[0]['url']);
        $this->assertEquals('https://example.com/thumb.jpg', $result[0]['thumbnail_url']);
        $this->assertEquals('https://example.com/artwork.jpg', $result[0]['artwork_url']);
    }

    public function test_parse_tracks_from_tralbum(): void
    {
        $mockTralbum = json_encode([
            'trackinfo' => [
                [
                    'title' => 'Track 1',
                    'track_num' => 1,
                    'file' => [
                        'mp3-128' => 'https://example.com/track1.mp3',
                    ],
                ],
                [
                    'title' => 'Track 2',
                    'track_num' => 2,
                    'file' => [
                        'mp3-128' => 'https://example.com/track2.mp3',
                    ],
                ],
                [
                    'title' => 'Track Without File',
                    'track_num' => 3,
                    // Missing file - should be skipped
                ],
            ],
        ]);

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseTracksFromTralbum');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'Test Artist', 'Test Album', $mockTralbum);

        $this->assertCount(2, $result);
        $this->assertEquals('Track 1', $result[0]['name']);
        $this->assertEquals(1, $result[0]['track_num']);
        $this->assertEquals('https://example.com/track1.mp3', $result[0]['url']);
        $this->assertEquals('Track 2', $result[1]['name']);
        $this->assertEquals(2, $result[1]['track_num']);
    }

    public function test_get_fan_id_from_username_success(): void
    {
        $mockHtml = '
            <html>
                <body>
                    <div id="pagedata" data-blob="{&quot;fan_data&quot;:{&quot;fan_id&quot;:123456}}">
                    </div>
                </body>
            </html>
        ';

        Http::fake([
            'https://bandcamp.com/testuser' => Http::response($mockHtml, 200),
        ]);

        $result = $this->service->getFanIdFromUsername('testuser');

        $this->assertEquals('123456', $result);
    }

    public function test_get_fan_id_from_username_not_found(): void
    {
        $mockHtml = '<html><body>No pagedata here</body></html>';

        Http::fake([
            'https://bandcamp.com/testuser' => Http::response($mockHtml, 200),
        ]);

        $result = $this->service->getFanIdFromUsername('testuser');

        $this->assertNull($result);
    }

    public function test_get_fan_id_from_username_http_error(): void
    {
        Http::fake([
            'https://bandcamp.com/testuser' => Http::response('Not Found', 404),
        ]);

        $result = $this->service->getFanIdFromUsername('testuser');

        $this->assertNull($result);
    }

    public function test_parse_tracks_success(): void
    {
        $mockHtml = '
            <html>
                <head>
                    <script data-tralbum="{&quot;trackinfo&quot;:[{&quot;title&quot;:&quot;Test Track&quot;,&quot;track_num&quot;:1,&quot;file&quot;:{&quot;mp3-128&quot;:&quot;https://example.com/track.mp3&quot;}}]}">
                    </script>
                </head>
            </html>
        ';

        Http::fake([
            'https://testartist.bandcamp.com/album/test-album' => Http::response($mockHtml, 200),
        ]);

        $result = $this->service->parseTracks('Test Artist', 'Test Album', 'https://testartist.bandcamp.com/album/test-album');

        $this->assertCount(1, $result);
        $this->assertEquals('Test Track', $result[0]['name']);
        $this->assertEquals('Test Artist', $result[0]['artist']);
        $this->assertEquals('Test Album', $result[0]['album']);
    }

    public function test_parse_tracks_no_tralbum_data(): void
    {
        $mockHtml = '<html><head>No tralbum data here</head></html>';

        Http::fake([
            'https://testartist.bandcamp.com/album/test-album' => Http::response($mockHtml, 200),
        ]);

        $result = $this->service->parseTracks('Test Artist', 'Test Album', 'https://testartist.bandcamp.com/album/test-album');

        $this->assertEmpty($result);
    }

    public function test_fetch_collection_makes_correct_api_calls(): void
    {
        Http::fake([
            'https://bandcamp.com/api/fancollection/1/collection_items' => Http::sequence()
                ->push([
                    'items' => [
                        [
                            'album_id' => 12345,
                            'band_id' => 67890,
                            'album_title' => 'Test Album',
                            'band_name' => 'Test Artist',
                            'item_url' => 'https://testartist.bandcamp.com/album/test-album',
                            'item_art' => [
                                'thumb_url' => 'https://example.com/thumb.jpg',
                                'url' => 'https://example.com/artwork.jpg',
                            ],
                        ],
                    ],
                    'last_token' => null, // End pagination
                ], 200),
        ]);

        $result = $this->service->fetchCollection('123456');

        $this->assertCount(1, $result);
        $this->assertEquals('Test Album', $result[0]['album']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://bandcamp.com/api/fancollection/1/collection_items' &&
                   $request->method() === 'POST' &&
                   $request->data()['fan_id'] === '123456';
        });
    }

    public function test_fetch_wishlist_makes_correct_api_calls(): void
    {
        Http::fake([
            'https://bandcamp.com/api/fancollection/1/wishlist_items' => Http::response([
                'items' => [],
                'last_token' => null,
            ], 200),
        ]);

        $result = $this->service->fetchWishlist('123456');

        $this->assertIsArray($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://bandcamp.com/api/fancollection/1/wishlist_items' &&
                   $request->method() === 'POST' &&
                   $request->data()['fan_id'] === '123456';
        });
    }
}
