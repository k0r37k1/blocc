<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED_LOCALES, true)) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('locale.switch');

Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/kategorie/{category}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/tag/{tag}', [TagController::class, 'show'])->name('tag.show');
Route::get('/archiv', [ArchiveController::class, 'index'])->name('archive');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/feed', FeedController::class)->name('feed');
Route::get('/seite/{page}', [PageController::class, 'show'])->name('page.show');
Route::get('/newsletter/bestaetigt', fn () => view('newsletter.confirmed'))->name('newsletter.confirmed');
