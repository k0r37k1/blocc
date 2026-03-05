<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()->published()->latest('published_at')->get(['title', 'slug', 'updated_at']);
        $pages = Page::query()->published()->get(['title', 'slug', 'updated_at']);
        $categories = Category::all(['name', 'slug']);
        $tags = Tag::all(['name', 'slug']);

        return response()
            ->view('sitemap', compact('posts', 'pages', 'categories', 'tags'))
            ->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
