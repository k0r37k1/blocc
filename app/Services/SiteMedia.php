<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteMedia
{
    public const COLLECTION = 'uploads';

    public const LEGACY_COLLECTION = 'featured-library';

    /** @var list<string> */
    public const SELECTABLE_COLLECTIONS = [
        self::COLLECTION,
        self::LEGACY_COLLECTION,
    ];

    private static bool $librarySynced = false;

    /** @var list<string> */
    public const SELECTABLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function site(): Site
    {
        return Site::instance();
    }

    public static function resetLibrarySyncState(): void
    {
        self::$librarySynced = false;
    }

    public function syncLibraryFromPostsAndLegacy(): void
    {
        if (self::$librarySynced) {
            return;
        }

        $this->migrateLegacy();
        $this->importFromPosts();

        self::$librarySynced = true;
    }

    /**
     * @return Builder<Media>
     */
    public function uploadsQuery(): Builder
    {
        return Media::query()
            ->where('model_type', Site::class)
            ->where('model_id', $this->site()->getKey())
            ->where('collection_name', self::COLLECTION)
            ->whereIn('mime_type', self::SELECTABLE_MIME_TYPES);
    }

    /**
     * @return Builder<Media>
     */
    public function libraryQuery(): Builder
    {
        return Media::query()
            ->where('model_type', Site::class)
            ->where('model_id', $this->site()->getKey())
            ->whereIn('collection_name', self::SELECTABLE_COLLECTIONS)
            ->whereIn('mime_type', self::SELECTABLE_MIME_TYPES);
    }

    /**
     * @return Collection<int, Media>
     */
    public function items(): Collection
    {
        $this->syncLibraryFromPostsAndLegacy();

        return $this->libraryQuery()
            ->orderByDesc('created_at')
            ->get();
    }

    public function find(int $id): ?Media
    {
        $this->syncLibraryFromPostsAndLegacy();

        $media = $this->libraryQuery()->whereKey($id)->first();

        return $media instanceof Media ? $media : null;
    }

    public function isSelectableMedia(Media $media): bool
    {
        return $media->model_type === Site::class
            && (int) $media->model_id === (int) $this->site()->getKey()
            && in_array($media->collection_name, self::SELECTABLE_COLLECTIONS, true)
            && in_array($media->mime_type, self::SELECTABLE_MIME_TYPES, true);
    }

    public function contentHash(Media $media): string
    {
        $existing = $media->getCustomProperty('content_hash');

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $path = $media->getPath();

        if (is_file($path)) {
            $hash = md5_file($path);

            if (is_string($hash) && $hash !== '') {
                return $hash;
            }
        }

        return md5($media->file_name.'|'.$media->size.'|'.$media->mime_type);
    }

    public function findByContentHash(string $hash): ?Media
    {
        return $this->uploadsQuery()
            ->get()
            ->first(fn (Media $media): bool => $this->contentHash($media) === $hash);
    }

    public function displayLabel(Media $media): string
    {
        $label = $media->getCustomProperty('label');

        if (is_string($label) && $label !== '') {
            return $label;
        }

        $legacyLabel = $media->getCustomProperty('library_label');

        if (is_string($legacyLabel) && $legacyLabel !== '') {
            return $legacyLabel;
        }

        return $media->file_name;
    }

    public function addFromMedia(Media $source, ?string $label = null): Media
    {
        $hash = $this->contentHash($source);

        $existing = $this->findByContentHash($hash);

        if ($existing instanceof Media) {
            return $existing;
        }

        if ($this->isSelectableMedia($source)) {
            $source->setCustomProperty('content_hash', $hash);

            $this->setLabel($source, $label);

            $source->save();

            return $source;
        }

        $upload = $source->copy($this->site(), self::COLLECTION);

        $upload->setCustomProperty('content_hash', $hash);
        $this->setLabel($upload, $label);

        $upload->save();

        return $upload;
    }

    public function applyToPost(Post $post, Media $siteMedia): Media
    {
        if (! $this->isSelectableMedia($siteMedia)) {
            throw new \InvalidArgumentException('Media cannot be used as a post featured image.');
        }

        $post->clearMediaCollection('featured-image');

        return $siteMedia->copy($post, 'featured-image');
    }

    public function syncPostUploadToUploads(Post $post): ?Media
    {
        $postMedia = $post->getFirstMedia('featured-image');

        if (! $postMedia instanceof Media) {
            return null;
        }

        return $this->addFromMedia($postMedia, $postMedia->file_name);
    }

    public function stampMissingContentHashes(): int
    {
        $stamped = 0;

        foreach ($this->items() as $media) {
            if (filled($media->getCustomProperty('content_hash'))) {
                continue;
            }

            $media->setCustomProperty('content_hash', $this->contentHash($media));
            $media->save();
            $stamped++;
        }

        return $stamped;
    }

    /**
     * @return array{imported: int, skipped: int, processed: int}
     */
    public function importFromPosts(bool $dryRun = false): array
    {
        $imported = 0;
        $skipped = 0;
        $processed = 0;

        Post::query()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'featured-image'))
            ->with(['media'])
            ->orderBy('id')
            ->chunkById(50, function (Collection $posts) use (&$imported, &$skipped, &$processed, $dryRun): void {
                foreach ($posts as $post) {
                    /** @var Post $post */
                    $postMedia = $post->getFirstMedia('featured-image');

                    if (! $postMedia instanceof Media) {
                        continue;
                    }

                    $processed++;
                    $hash = $this->contentHash($postMedia);

                    if ($this->findByContentHash($hash) instanceof Media) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        $this->addFromMedia($postMedia, $postMedia->file_name);
                    }

                    $imported++;
                }
            });

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'processed' => $processed,
        ];
    }

    /**
     * @return array{migrated: int, skipped: int, deleted: int}
     */
    public function migrateLegacy(bool $dryRun = false): array
    {
        $migrated = 0;
        $skipped = 0;
        $deleted = 0;

        $legacyItems = Media::query()
            ->where('model_type', Site::class)
            ->where('model_id', $this->site()->getKey())
            ->where('collection_name', self::LEGACY_COLLECTION)
            ->orderBy('id')
            ->get();

        foreach ($legacyItems as $legacyMedia) {
            $hash = $this->contentHash($legacyMedia);
            $existingUpload = $this->findByContentHash($hash);

            if ($existingUpload instanceof Media) {
                $skipped++;

                if (! $dryRun) {
                    $legacyMedia->delete();
                    $deleted++;
                }

                continue;
            }

            if (! $dryRun) {
                $upload = $legacyMedia->copy($this->site(), self::COLLECTION);
                $upload->setCustomProperty('content_hash', $hash);

                $legacyLabel = $this->displayLabel($legacyMedia);
                $this->setLabel(
                    $upload,
                    $legacyLabel !== $legacyMedia->file_name ? $legacyLabel : null,
                );

                $upload->save();
                $legacyMedia->delete();
                $deleted++;
            }

            $migrated++;
        }

        return [
            'migrated' => $migrated,
            'skipped' => $skipped,
            'deleted' => $deleted,
        ];
    }

    private function setLabel(Media $media, ?string $label): void
    {
        if (filled($label)) {
            $media->setCustomProperty('label', $label);
        }
    }
}
