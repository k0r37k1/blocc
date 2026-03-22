<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Site extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('uploads');
        $this->addMediaCollection('site_assets');
    }

    /**
     * Always return the singleton instance (ID=1), creating it if needed.
     */
    public static function instance(): static
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
