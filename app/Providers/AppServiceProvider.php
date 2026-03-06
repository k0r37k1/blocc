<?php

namespace App\Providers;

use App\Filament\Pages\Auth\EditProfile;
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
    }
}
