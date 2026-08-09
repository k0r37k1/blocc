<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Throwable;

class StoreMediaDominantColor
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        if ($media->mime_type === 'image/svg+xml') {
            return;
        }

        $path = $media->getPath();

        if (! is_string($path) || $path === '' || ! is_file($path)) {
            return;
        }

        try {
            $color = Image::fromPath($path)->usingGd()->dominantColor();
            $media->setCustomProperty('dominant_color', $color);
            $media->saveQuietly();
        } catch (Throwable $exception) {
            Log::debug('Unable to extract dominant color for media.', [
                'media_id' => $media->getKey(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
