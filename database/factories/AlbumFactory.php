<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bandcamp_id' => $this->faker->unique()->numberBetween(100000, 999999),
            'bandcamp_band_id' => $this->faker->numberBetween(10000, 99999),
            'album' => $this->faker->words(3, true),
            'artist' => $this->faker->name(),
            'url' => $this->faker->url(),
            'thumbnail_url' => $this->faker->imageUrl(200, 200),
            'artwork_url' => $this->faker->imageUrl(400, 400),
            'purchased' => $this->faker->boolean(),
            'rating' => $this->faker->numberBetween(-1, 5),
            'comment' => $this->faker->optional(0.7)->paragraph(),
        ];
    }
}
