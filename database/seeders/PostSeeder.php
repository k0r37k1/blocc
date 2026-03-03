<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed sample blog posts: 5 published, 2 drafts.
     */
    public function run(): void
    {
        Post::factory()->count(5)->published()->create();
        Post::factory()->count(2)->draft()->create();
    }
}
