<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $posts = $tag->posts()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->simplePaginate(10);

        return view('tag.show', compact('tag', 'posts'));
    }
}
