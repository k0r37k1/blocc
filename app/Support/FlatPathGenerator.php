<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores media files flat in the disk root (no subdirectories).
 * Used for Site uploads so files are accessible at /images/{filename}.
 */
class FlatPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return '';
    }

    public function getPathForConversions(Media $media): string
    {
        return 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return 'responsive-images/';
    }
}
