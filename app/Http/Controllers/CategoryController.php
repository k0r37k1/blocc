<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $posts = $category->posts()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->simplePaginate(10);

        return view('category.show', compact('category', 'posts'));
    }
}
