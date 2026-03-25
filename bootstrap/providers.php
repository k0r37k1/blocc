<?php

use App\Providers\AppServiceProvider;
use App\Providers\CookiesServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    CookiesServiceProvider::class,
    AdminPanelProvider::class,
];
