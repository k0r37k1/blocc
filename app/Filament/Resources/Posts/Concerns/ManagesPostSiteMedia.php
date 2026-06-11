<?php

namespace App\Filament\Resources\Posts\Concerns;

use App\Services\SiteMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait ManagesPostSiteMedia
{
    /**
     * @return array<string, mixed>
     */
    protected function postMediaFormState(): array
    {
        return $this->form->getState();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stripPostMediaFormFields(array $data): array
    {
        unset($data['site_media_id']);

        return $data;
    }

    protected function processPostMediaAfterSave(): void
    {
        $post = $this->record;
        $siteMedia = app(SiteMedia::class);
        $state = $this->postMediaFormState();
        $siteMediaId = $state['site_media_id'] ?? null;

        if (filled($siteMediaId)) {
            $selected = $siteMedia->find((int) $siteMediaId);

            if ($selected instanceof Media) {
                $siteMedia->applyToPost($post, $selected);

                return;
            }
        }

        $siteMedia->syncPostUploadToUploads($post);
    }
}
