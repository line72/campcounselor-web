<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Album extends Model
{
    use HasFactory;

    // Disable Laravel's automatic timestamp management since we're using integer timestamps
    public $timestamps = false;

    protected $fillable = [
        'bandcamp_id',
        'bandcamp_band_id',
        'album',
        'artist',
        'url',
        'thumbnail_url',
        'artwork_url',
        'purchased',
        'rating',
        'comment',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'purchased' => 'boolean',
        'rating' => 'integer',
        'created_at' => 'integer',
        'updated_at' => 'integer',
    ];

    /**
     * Boot the model to handle integer timestamps
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $now = time();
            $model->created_at = $now;
            $model->updated_at = $now;
        });

        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }

    /**
     * Scope to filter purchased albums
     */
    public function scopePurchased($query)
    {
        return $query->where('purchased', true);
    }

    /**
     * Scope to filter wishlist albums
     */
    public function scopeWishlist($query)
    {
        return $query->where('purchased', false);
    }

    /**
     * Scope to search by artist or album name
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('artist', 'LIKE', "%{$term}%")
              ->orWhere('album', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get the star rating as an array for display
     */
    public function getStarsAttribute()
    {
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            $stars[] = $i <= $this->rating;
        }
        return $stars;
    }

    /**
     * Check if album has a valid rating
     */
    public function hasRating()
    {
        return $this->rating >= 0; // -1 means unrated, 0-10 are valid ratings
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::createFromTimestamp($this->created_at)->format('M j, Y');
    }

    /**
     * Get formatted updated date
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return Carbon::createFromTimestamp($this->updated_at)->format('M j, Y');
    }
}
