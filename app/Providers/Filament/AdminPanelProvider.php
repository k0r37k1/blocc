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
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->brandName('blocc Admin')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('1.5rem')
            ->colors(fn () => [
                'primary' => Color::hex(\App\Models\Setting::get('accent_color', '#15803d')),
            ])
            ->font(
                'Inter',
                url: asset('css/fonts.css'),
                provider: LocalFontProvider::class,
            )
            ->plugins([
                FilamentSvgAvatarPlugin::make(),
            ])
            ->unsavedChangesAlerts()
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
                function (): HtmlString {
                    $accentColor = \App\Models\Setting::get('accent_color', '#15803d');

                    return new HtmlString(
                        '<style>
                        /* WCAG 2.2 AA: match frontend accent-bg for consistent button contrast */
                        .fi-bg-color-400, .dark .fi-bg-color-600 { background-color: '.$accentColor.' !important; }
                        .fi-bg-color-400 { color: #fff !important; }

                        /* FilePond grid mode: center the drop label vertically in the panel */
                        .filepond--root[data-style-panel-layout~="grid"] .filepond--drop-label {
                            height: 100%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .filepond--root[data-style-panel-layout~="grid"] .filepond--drop-label label {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 0.5rem;
                        }
                        .filepond--root[data-style-panel-layout~="grid"] .filepond--drop-label label::before {
                            content: "";
                            display: block;
                            width: 2rem;
                            height: 2rem;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%236b7280\' stroke-width=\'1.5\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5\'/%3E%3C/svg%3E");
                            background-repeat: no-repeat;
                            background-size: contain;
                            opacity: 0.5;
                        }
                    </style>'
                    );
                },
            );
    }
}
