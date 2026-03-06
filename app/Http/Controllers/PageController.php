<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $page)
            ->firstOrFail();

        return view('page.show', compact('page'));
    }
}
