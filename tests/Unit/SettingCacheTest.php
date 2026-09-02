<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_reads_settings_from_cache_memo(): void
    {
        Setting::query()->create(['key' => 'site_name', 'value' => 'Blocc']);

        Cache::spy();

        $this->assertSame('Blocc', Setting::get('site_name'));
        $this->assertSame('Blocc', Setting::get('site_name'));
        $this->assertSame('Blocc', Setting::get('site_name'));

        Cache::shouldHaveReceived('memo')->times(3);
    }

    public function test_set_clears_cached_settings(): void
    {
        Setting::query()->create(['key' => 'site_name', 'value' => 'Blocc']);

        $this->assertSame('Blocc', Setting::get('site_name'));

        Setting::set('site_name', 'Updated');

        $this->assertSame('Updated', Setting::get('site_name'));
    }

    public function test_set_many_clears_memoized_settings_cache(): void
    {
        Setting::query()->create(['key' => 'blog_name', 'value' => 'Old']);

        $this->assertSame('Old', Setting::get('blog_name'));

        Setting::setMany(['blog_name' => 'New']);

        $this->assertSame('New', Setting::get('blog_name'));
    }
}
