<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PostPreviewController extends Controller
{
    public function show(Post $post): View
    {
        $post->load(['category', 'tags', 'media', 'author.media'])
            ->loadCount('approvedComments');

        return view('blog.show', [
            'post' => $post,
            'isPreview' => true,
            'previousPost' => null,
            'nextPost' => null,
            'relatedPosts' => Collection::make(),
        ]);
    }
}
