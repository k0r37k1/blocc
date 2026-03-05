<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('tags')
            ->latest('published_at')
            ->limit(20)
            ->get();

        return response()
            ->view('feed.rss', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
