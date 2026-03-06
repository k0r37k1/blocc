<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $posts = $category->posts()
            ->published()
            ->with(['media'])
            ->latest('published_at')
            ->simplePaginate((int) Setting::get('posts_per_page', '10'));

        return view('category.show', compact('category', 'posts'));
    }
}
