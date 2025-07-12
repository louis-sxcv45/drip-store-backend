<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\Store;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Filament\Store\Widgets\TotalProduct;
use Filament\Support\Colors\Color;
use App\Filament\StoreAuth\Pages\Register;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\View\LegacyComponents\Widget;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;


class StorePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('store')
            ->path('store')
            ->authGuard('store')
            ->brandName('Store Panel')
            ->login()
            ->registration(Register::class)
            // ->brandLogo(function () {
            //     $storeUser = auth('store')->user();      // guard ‘store’
            //     return $storeUser?->logo_url             // accessor di atas
            //         ?? asset('assets/default-logo.png'); // fallback
            // })
            // ->darkModeBrandLogo(
            //     fn() =>
            //     auth('store')->user()?->logo_url
            //         ?? asset('assets/default-logo-dark.png')
            // )

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Store/Resources'), for: 'App\\Filament\\Store\\Resources')
            ->discoverPages(in: app_path('Filament/Store/Pages'), for: 'App\\Filament\\Store\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Store/Widgets'), for: 'App\\Filament\\Store\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                TotalProduct::class,
            ])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
