<?php

namespace App\Providers;

use App\Filament\Pages\Auth\EditProfile;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('app.filament.pages.auth.edit-profile', EditProfile::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_LOGO_AFTER,
            fn (): string => Blade::render('
                @php
                    $currentLocale = app()->getLocale();
                    $targetLocale = $currentLocale === \'de\' ? \'en\' : \'de\';
                    $switchUrl = route(\'locale.switch\', $targetLocale);
                @endphp
                <button
                    onclick="window.location.href = \'{{ $switchUrl }}\'"
                    style="min-width: 2.5rem; min-height: 2.5rem;"
                    class="inline-flex items-center justify-center gap-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                    title="{{ __(\'Language\') }}"
                >
                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 014 9 15.3 15.3 0 01-4 9 15.3 15.3 0 01-4-9 15.3 15.3 0 014-9z" />
                    </svg>
                    <span class="text-xs font-medium uppercase tracking-wide">{{ $currentLocale === \'de\' ? \'EN\' : \'DE\' }}</span>
                </button>
            '),
        );
    }
}
