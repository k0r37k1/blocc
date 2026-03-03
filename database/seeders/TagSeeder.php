<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Seed sample blog tags.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Performance', 'slug' => 'performance'],
            ['name' => 'Testing', 'slug' => 'testing'],
            ['name' => 'Security', 'slug' => 'security'],
            ['name' => 'Docker', 'slug' => 'docker'],
            ['name' => 'API', 'slug' => 'api'],
            ['name' => 'Database', 'slug' => 'database'],
            ['name' => 'Tips', 'slug' => 'tips'],
            ['name' => 'Beginner', 'slug' => 'beginner'],
            ['name' => 'Advanced', 'slug' => 'advanced'],
            ['name' => 'Open Source', 'slug' => 'open-source'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
