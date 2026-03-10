<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Voltra\FilamentSvgAvatar\FilamentSvgAvatarPlugin;

use function Filament\Support\original_request;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->spa()
            ->brandName('blocc Admin')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('1.5rem')
            ->colors(fn () => [
                'primary' => Color::hex(\App\Models\Setting::get('accent_color', '#16a34a')),
            ])
            ->font(
                'Inter',
                url: asset('css/fonts.css'),
                provider: LocalFontProvider::class,
            )
            ->plugins([
                FilamentSvgAvatarPlugin::make(),
            ])
            ->globalSearchKeyBindings(['mod+k'])
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('Content')),
                NavigationGroup::make(fn (): string => __('Taxonomy')),
                NavigationGroup::make(fn (): string => __('General'))->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make(fn (): string => __('My Profile'))
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->url(fn (): string => EditProfile::getUrl())
                    ->isActiveWhen(fn (): bool => original_request()->routeIs('filament.admin.auth.profile'))
                    ->group(fn (): string => __('General'))
                    ->sort(-1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureCredentialsChanged::class,
            ])
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->hidden(),
                Action::make('visit-site')
                    ->label(fn (): string => __('Visit website'))
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->url(fn (): string => url('/'), shouldOpenInNewTab: true),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(
                    '<script>
                        document.querySelector("nav.fi-topbar")?.setAttribute("aria-label", "'.__('Top bar').'");
                        document.querySelector("nav.fi-sidebar-nav")?.setAttribute("aria-label", "'.__('Main navigation').'");
                    </script>'
                ),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<style>
                        /* WCAG 2.2 AA: match frontend accent-bg (#15803d) for consistent button contrast */
                        .fi-bg-color-400, .dark .fi-bg-color-600 { background-color: #15803d; }
                    </style>'
                ),
            );
    }
}
