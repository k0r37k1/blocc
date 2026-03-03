<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->get(['title', 'slug', 'published_at']);

        $postsByYear = $posts->groupBy(fn (Post $post): int => $post->published_at->year);

        return view('archive.index', compact('postsByYear'));
    }
}
