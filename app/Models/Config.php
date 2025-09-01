<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Config extends Model
{
    protected $table = 'config';

    protected $fillable = [
        'last_refresh',
    ];

    protected $casts = [
        'last_refresh' => 'integer',
    ];

    /**
     * Get the last refresh time as a Carbon instance
     */
    public function getLastRefreshTimeAttribute()
    {
        return Carbon::createFromTimestamp($this->last_refresh);
    }

    /**
     * Set the last refresh time from a Carbon instance
     */
    public function setLastRefreshTime(Carbon $time)
    {
        $this->last_refresh = $time->timestamp;
        $this->save();
    }

    /**
     * Get or create the singleton config record
     */
    public static function getInstance()
    {
        $config = static::first();
        if (!$config) {
            $config = static::create(['last_refresh' => 0]);
        }
        return $config;
    }
}
