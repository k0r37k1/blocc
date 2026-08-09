<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostCache;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('blog.index');
    }

    public function show(string $post): View
    {
        $post = PostCache::rememberShow($post, function () use ($post): Post {
            $model = Post::query()
                ->published()
                ->where('slug', $post)
                ->firstOrFail();

            $model->load(['category', 'tags', 'media', 'author.media'])
                ->loadCount('approvedComments');

            return $model;
        });

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

        $relatedPosts = PostCache::rememberRelated(
            $post,
            fn () => Post::relatedFor($post, 5)->load(['media']),
        );

        return view('blog.show', compact('post', 'previousPost', 'nextPost', 'relatedPosts'));
    }
}
