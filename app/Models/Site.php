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
    }

    /**
     * Always return the singleton instance (ID=1), creating it if needed.
     */
    public static function instance(): static
    {
        return static::find(1) ?? static::forceCreate(['id' => 1]);
    }
}
