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
            'accent_color' => '#16a34a',
            'accent_color_dark' => '#4ade80',
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'code_theme' => 'GitHub',
            'footer_text' => '',
            'head_scripts' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
