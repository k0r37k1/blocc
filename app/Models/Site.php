<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Site extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('uploads')->useDisk('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Crop, 400, 300)
            ->format('webp')
            ->quality(90)
            ->nonQueued();
    }

    private static ?self $cached = null;

    /**
     * Always return the singleton instance (ID=1), creating it if needed.
     * Result is cached for the lifetime of the request.
     */
    public static function instance(): static
    {
        return static::$cached ??= static::find(1) ?? static::forceCreate(['id' => 1]);
    }
}
