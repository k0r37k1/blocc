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
            ['name' => 'Demo', 'slug' => 'demo'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
