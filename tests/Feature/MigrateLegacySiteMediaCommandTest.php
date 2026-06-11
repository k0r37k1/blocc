<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Services\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MigrateLegacySiteMediaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrate_command_moves_legacy_into_uploads(): void
    {
        $site = Site::instance();

        $site->addMedia(UploadedFile::fake()->image('legacy.jpg', 1200, 675))
            ->toMediaCollection(SiteMedia::LEGACY_COLLECTION);

        $this->artisan('site-media:migrate-legacy')
            ->assertSuccessful();

        $this->assertCount(1, app(SiteMedia::class)->items());
    }
}
