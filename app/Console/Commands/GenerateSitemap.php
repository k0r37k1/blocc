<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the public/sitemap.xml file';

    public function handle(): int
    {
        $posts = Post::query()->published()->latest('published_at')->get(['title', 'slug', 'updated_at']);
        $pages = Page::query()->published()->get(['title', 'slug', 'updated_at']);
        $categories = Category::all(['name', 'slug']);
        $tags = Tag::all(['name', 'slug']);

        $xml = ltrim(view('sitemap', compact('posts', 'pages', 'categories', 'tags'))->render());

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->line('Path: '.public_path('sitemap.xml'));

        $this->info('sitemap.xml generated successfully.');

        return self::SUCCESS;
    }
}
