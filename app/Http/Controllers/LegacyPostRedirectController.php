<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class LegacyPostRedirectController extends Controller
{
    public function __invoke(string $slug): RedirectResponse
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->first(['slug']);

        if ($post === null) {
            abort(404);
        }

        return redirect()->route('blog.show', $post, status: 301);
    }
}
