<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Site;
use App\Services\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SiteMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_returns_selectable_site_uploads(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $site->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $site->addMedia(UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml'))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $this->assertCount(1, $siteMedia->items());
    }

    public function test_add_from_media_deduplicates_by_content_hash(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $source = $site->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $first = $siteMedia->addFromMedia($source, 'hero.jpg');
        $second = $siteMedia->addFromMedia($source, 'hero.jpg');

        $this->assertSame($first->id, $second->id);
        $this->assertCount(1, $siteMedia->items());
    }

    public function test_apply_to_post_copies_upload_to_post(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();
        $post = Post::factory()->create();

        $upload = $site->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $postMedia = $siteMedia->applyToPost($post, $upload);

        $this->assertNotNull($post->fresh()->getFirstMedia('featured-image'));
        $this->assertSame('featured-image', $postMedia->collection_name);
    }

    public function test_sync_post_upload_to_uploads_adds_unique_images(): void
    {
        $siteMedia = app(SiteMedia::class);
        $post = Post::factory()->create();

        $post->addMedia(UploadedFile::fake()->image('uploaded.jpg', 1200, 675))
            ->toMediaCollection('featured-image');

        $upload = $siteMedia->syncPostUploadToUploads($post->fresh());

        $this->assertNotNull($upload);
        $this->assertSame(SiteMedia::COLLECTION, $upload->collection_name);
        $this->assertCount(1, $siteMedia->items());
    }

    public function test_import_from_posts_skips_duplicates_on_second_run(): void
    {
        $siteMedia = app(SiteMedia::class);

        $post = Post::factory()->create();
        $post->addMedia(UploadedFile::fake()->image('shared.jpg', 1200, 675))
            ->toMediaCollection('featured-image');

        $firstRun = $siteMedia->importFromPosts();
        $secondRun = $siteMedia->importFromPosts();

        $this->assertSame(1, $firstRun['imported']);
        $this->assertSame(0, $secondRun['imported']);
        $this->assertSame(1, $secondRun['skipped']);
        $this->assertCount(1, $siteMedia->items());
    }

    public function test_migrate_legacy_moves_images_into_uploads(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $legacy = $site->addMedia(UploadedFile::fake()->image('legacy.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::LEGACY_COLLECTION);

        $legacy->setCustomProperty('label', 'Legacy cover');
        $legacy->save();

        $stats = $siteMedia->migrateLegacy();

        $this->assertSame(1, $stats['migrated']);
        $this->assertSame(0, $stats['skipped']);
        $this->assertCount(1, $siteMedia->items());
        $this->assertDatabaseMissing('media', [
            'id' => $legacy->id,
            'collection_name' => SiteMedia::LEGACY_COLLECTION,
        ]);
        $this->assertSame('Legacy cover', $siteMedia->items()->first()->getCustomProperty('label'));
    }

    public function test_migrate_legacy_preserves_library_label(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $legacy = $site->addMedia(UploadedFile::fake()->image('legacy.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::LEGACY_COLLECTION);

        $legacy->setCustomProperty('library_label', 'Old label');
        $legacy->save();

        $siteMedia->migrateLegacy();

        $this->assertSame('Old label', $siteMedia->items()->first()->getCustomProperty('label'));
    }

    public function test_display_label_falls_back_to_legacy_library_label(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $upload = $site->addMedia(UploadedFile::fake()->image('hero.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $upload->setCustomProperty('library_label', 'Old label');
        $upload->save();

        $this->assertSame('Old label', $siteMedia->displayLabel($upload));
    }

    public function test_migrate_legacy_skips_duplicates_already_in_uploads(): void
    {
        $siteMedia = app(SiteMedia::class);
        $site = Site::instance();

        $upload = $site->addMedia(UploadedFile::fake()->image('shared.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::COLLECTION);

        $upload->setCustomProperty('content_hash', $siteMedia->contentHash($upload));
        $upload->save();

        $site->addMedia(UploadedFile::fake()->image('shared.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::LEGACY_COLLECTION);

        $stats = $siteMedia->migrateLegacy();

        $this->assertSame(0, $stats['migrated']);
        $this->assertSame(1, $stats['skipped']);
        $this->assertCount(1, $siteMedia->items());
    }
}
