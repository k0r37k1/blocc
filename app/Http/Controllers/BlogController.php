<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->simplePaginate(10);

        return view('blog.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        $post->load(['category', 'tags', 'media']);

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
