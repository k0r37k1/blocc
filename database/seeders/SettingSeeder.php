<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default blog settings.
     */
    public function run(): void
    {
        $defaults = [
            'blog_name' => 'Kopfsalat',
            'blog_description' => 'Thoughts served fresh',
            'posts_per_page' => '10',
            'social_github' => '',
            'social_twitter' => '',
            'social_linkedin' => '',
            'social_instagram' => '',
            'social_bluesky' => '',
            'footer_text' => '',
            'head_scripts' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
