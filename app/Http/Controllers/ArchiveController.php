<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(): View
    {
        return view('archive.index');
    }
}
