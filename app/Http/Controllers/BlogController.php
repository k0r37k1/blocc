<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'media', 'author.media'])
            ->withCount('approvedComments')
            ->latest('published_at')
            ->simplePaginate((int) Setting::get('posts_per_page', '10'));

        return view('blog.index', compact('posts'));
    }

    public function show(string $post): View
    {
        $post = Post::query()
            ->published()
            ->where('slug', $post)
            ->firstOrFail();

        $post->load(['category', 'tags', 'media', 'author.media'])
            ->loadCount('approvedComments');

        $previousPost = Post::query()
            ->published()
            ->where('published_at', '<', $post->published_at)
            ->latest('published_at')
            ->first(['title', 'slug']);

        $nextPost = Post::query()
            ->published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')
            ->first(['title', 'slug']);

        return view('blog.show', compact('post', 'previousPost', 'nextPost'));
    }
}
