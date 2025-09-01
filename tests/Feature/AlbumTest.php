<?php

namespace Tests\Feature;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_album_can_be_created(): void
    {
        $albumData = [
            'bandcamp_id' => '12345',
            'bandcamp_band_id' => '67890',
            'album' => 'Test Album',
            'artist' => 'Test Artist',
            'url' => 'https://testartist.bandcamp.com/album/test-album',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'artwork_url' => 'https://example.com/artwork.jpg',
            'purchased' => false,
            'rating' => 4,
            'comment' => 'Great album!',
        ];

        $album = Album::create($albumData);

        $this->assertDatabaseHas('albums', $albumData);
        $this->assertEquals('Test Album', $album->album);
        $this->assertEquals('Test Artist', $album->artist);
        $this->assertEquals(4, $album->rating);
        $this->assertFalse($album->purchased);
    }

    public function test_album_scopes_work_correctly(): void
    {
        // Create test albums
        Album::factory()->create(['purchased' => true, 'artist' => 'Artist A']);
        Album::factory()->create(['purchased' => false, 'artist' => 'Artist B']);
        Album::factory()->create(['purchased' => true, 'artist' => 'Artist C']);

        // Test purchased scope
        $purchasedAlbums = Album::purchased()->get();
        $this->assertCount(2, $purchasedAlbums);
        $this->assertTrue($purchasedAlbums->every(fn($album) => $album->purchased));

        // Test wishlist scope
        $wishlistAlbums = Album::wishlist()->get();
        $this->assertCount(1, $wishlistAlbums);
        $this->assertTrue($wishlistAlbums->every(fn($album) => !$album->purchased));

        // Test search scope
        $searchResults = Album::search('Artist A')->get();
        $this->assertCount(1, $searchResults);
        $this->assertEquals('Artist A', $searchResults->first()->artist);
    }

    public function test_album_star_rating_attribute(): void
    {
        $album = Album::factory()->create(['rating' => 3]);
        
        $stars = $album->stars;
        $this->assertCount(5, $stars);
        $this->assertTrue($stars[0]); // 1st star filled
        $this->assertTrue($stars[1]); // 2nd star filled
        $this->assertTrue($stars[2]); // 3rd star filled
        $this->assertFalse($stars[3]); // 4th star empty
        $this->assertFalse($stars[4]); // 5th star empty
    }

    public function test_album_has_rating_method(): void
    {
        $albumWithRating = Album::factory()->create(['rating' => 4]);
        $albumWithoutRating = Album::factory()->create(['rating' => -1]);

        $this->assertTrue($albumWithRating->hasRating());
        $this->assertFalse($albumWithoutRating->hasRating());
    }

    public function test_album_formatted_dates(): void
    {
        $album = Album::factory()->create([
            'created_at' => '2023-01-15 10:30:00',
            'updated_at' => '2023-02-20 15:45:00',
        ]);

        $this->assertEquals('Jan 15, 2023', $album->formatted_created_at);
        $this->assertEquals('Feb 20, 2023', $album->formatted_updated_at);
    }

    public function test_album_requires_unique_bandcamp_id(): void
    {
        Album::factory()->create(['bandcamp_id' => '12345']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Album::factory()->create(['bandcamp_id' => '12345']);
    }
}
