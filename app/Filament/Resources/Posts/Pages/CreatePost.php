<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\File;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function afterCreate(): void
    {
        $post = $this->record;

        if ($post->getMedia('featured-image')->isEmpty()) {
            $placeholderDir = storage_path('app/public/featured-placeholders');

            if (! is_dir($placeholderDir)) {
                return;
            }

            $files = File::files($placeholderDir);

            if (empty($files)) {
                return;
            }

            $random = $files[array_rand($files)];

            $post->addMedia($random->getPathname())
                ->preservingOriginal()
                ->toMediaCollection('featured-image');
        }
    }
}
