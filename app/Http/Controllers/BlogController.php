<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('blog.index');
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
