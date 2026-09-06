<?php

namespace App\Providers\Filament;

use App\Filament\Pages\OperationsDashboard;
use App\Filament\Pages\PotholesDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentAsset::register([
            Css::make('leaflet', base_path('node_modules/leaflet/dist/leaflet.css')),
            Css::make('swm-theme', __DIR__.'/../../../resources/css/filament/swm-theme.css'),
            Js::make('leaflet', base_path('node_modules/leaflet/dist/leaflet.js'))->defer(),
            Js::make('operations-map', resource_path('js/operations-map.js'))->defer(),
            Js::make('cctv-review', resource_path('js/cctv-review.js'))->defer(),
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Selangor Waste Management')
            ->colors([
                'primary' => Color::hex('#C45C5C'),
                'warning' => Color::hex('#E8C547'),
                'danger' => Color::hex('#B84A4A'),
                'success' => Color::hex('#6B9B6E'),
                'info' => Color::hex('#D4A84B'),
            ])
            ->font('Figtree')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                OperationsDashboard::class,
                PotholesDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
