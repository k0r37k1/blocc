<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(): View
    {
        return view('archive.index', [
            'postCount' => Post::query()->published()->count(),
        ]);
    }
}
