<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\Concerns\ManagesPostSiteMedia;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\File;

class CreatePost extends CreateRecord
{
    use ManagesPostSiteMedia;

    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stripPostMediaFormFields($data);
    }

    protected function afterCreate(): void
    {
        $this->processPostMediaAfterSave();

        $post = $this->record;

        if (filled($this->postMediaFormState()['site_media_id'] ?? null)) {
            return;
        }

        if (! ($this->postMediaFormState()['use_placeholder_image'] ?? false)) {
            return;
        }

        if (! $post->getMedia('featured-image')->isEmpty()) {
            return;
        }

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
