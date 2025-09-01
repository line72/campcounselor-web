<?php

namespace Tests\Feature;

use App\Models\Album;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlbumControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_index_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('albums.index');
    }

    public function test_index_displays_albums(): void
    {
        $albums = Album::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        foreach ($albums as $album) {
            $response->assertSee($album->artist);
            $response->assertSee($album->album);
        }
    }

    public function test_index_can_search_albums(): void
    {
        Album::factory()->create(['artist' => 'Test Artist', 'album' => 'Test Album']);
        Album::factory()->create(['artist' => 'Other Artist', 'album' => 'Other Album']);

        $response = $this->get('/?search=Test');

        $response->assertStatus(200);
        $response->assertSee('Test Artist');
        $response->assertDontSee('Other Artist');
    }

    public function test_index_can_filter_by_purchased(): void
    {
        Album::factory()->create(['purchased' => true, 'artist' => 'Purchased Artist']);
        Album::factory()->create(['purchased' => false, 'artist' => 'Wishlist Artist']);

        $response = $this->get('/?filter=purchased');

        $response->assertStatus(200);
        $response->assertSee('Purchased Artist');
        $response->assertDontSee('Wishlist Artist');
    }

    public function test_index_can_filter_by_wishlist(): void
    {
        Album::factory()->create(['purchased' => true, 'artist' => 'Purchased Artist']);
        Album::factory()->create(['purchased' => false, 'artist' => 'Wishlist Artist']);

        $response = $this->get('/?filter=wishlist');

        $response->assertStatus(200);
        $response->assertSee('Wishlist Artist');
        $response->assertDontSee('Purchased Artist');
    }

    public function test_index_returns_json_when_requested(): void
    {
        $albums = Album::factory()->count(2)->create();

        $response = $this->getJson('/');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'bandcamp_id',
                    'artist',
                    'album',
                    'url',
                    'rating',
                    'purchased',
                ]
            ]
        ]);
    }

    public function test_show_displays_album(): void
    {
        $album = Album::factory()->create();

        $response = $this->get("/albums/{$album->id}");

        $response->assertStatus(200);
        $response->assertViewIs('albums.show');
        $response->assertSee($album->artist);
        $response->assertSee($album->album);
    }

    public function test_show_returns_json_when_requested(): void
    {
        $album = Album::factory()->create();

        $response = $this->getJson("/albums/{$album->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'bandcamp_id',
            'artist',
            'album',
            'url',
            'rating',
            'purchased',
        ]);
    }

    public function test_store_creates_album(): void
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

        $response = $this->postJson('/albums', $albumData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('albums', $albumData);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/albums', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'bandcamp_id',
            'bandcamp_band_id',
            'album',
            'artist',
            'url',
        ]);
    }

    public function test_update_rating_updates_album(): void
    {
        $album = Album::factory()->create(['rating' => 2, 'comment' => 'Old comment']);

        $response = $this->putJson("/albums/{$album->id}/rating", [
            'rating' => 5,
            'comment' => 'New comment',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $album->refresh();
        $this->assertEquals(5, $album->rating);
        $this->assertEquals('New comment', $album->comment);
    }

    public function test_update_rating_validates_rating_range(): void
    {
        $album = Album::factory()->create();

        $response = $this->putJson("/albums/{$album->id}/rating", [
            'rating' => 10, // Invalid rating
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }

    public function test_destroy_deletes_album(): void
    {
        $album = Album::factory()->create();

        $response = $this->deleteJson("/albums/{$album->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
    }

    public function test_stats_returns_correct_statistics(): void
    {
        Album::factory()->create(['purchased' => true, 'rating' => 5]);
        Album::factory()->create(['purchased' => false, 'rating' => 3]);
        Album::factory()->create(['purchased' => true, 'rating' => -1]);

        $response = $this->getJson('/api/albums/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'total' => 3,
            'purchased' => 2,
            'wishlist' => 1,
            'rated' => 2,
            'average_rating' => 4.0, // (5 + 3) / 2
        ]);
    }
}
