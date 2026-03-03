<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Seed sample blog posts: 5 published, 2 drafts with categories and tags.
     */
    public function run(): void
    {
        $categoryIds = Category::pluck('id');

        $posts = Post::factory()
            ->count(5)
            ->published()
            ->sequence(fn ($sequence) => ['category_id' => $categoryIds[$sequence->index % $categoryIds->count()]])
            ->create();

        $drafts = Post::factory()
            ->count(2)
            ->draft()
            ->sequence(fn ($sequence) => ['category_id' => $categoryIds->random()])
            ->create();

        $allPosts = $posts->merge($drafts);

        foreach ($allPosts as $post) {
            $post->tags()->attach(
                Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')
            );
        }
    }
}
