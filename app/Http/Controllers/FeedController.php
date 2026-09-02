<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\RssFeedBuilder;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function __invoke(RssFeedBuilder $feed): Response
    {
        $posts = Post::query()
            ->published()
            ->with(['tags', 'author', 'media'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        return response()
            ->view('feed.rss', [
                'posts' => $posts,
                'feed' => $feed,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
