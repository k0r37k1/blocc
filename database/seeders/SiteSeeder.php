<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::instance();

        Setting::whereIn('key', ['blog_logo', 'favicon'])->delete();
    }
}
