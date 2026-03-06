<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $posts = $tag->posts()
            ->published()
            ->with(['category', 'media'])
            ->latest('published_at')
            ->simplePaginate((int) Setting::get('posts_per_page', '10'));

        return view('tag.show', compact('tag', 'posts'));
    }
}
