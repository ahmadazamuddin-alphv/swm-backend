<?php

namespace App\Providers\Filament;

use App\Filament\Pages\OperationsDashboard;
use App\Filament\Pages\PotholesDashboard;
use App\Filament\Pages\DistrictPriorityAnalytics;
use App\Filament\Pages\ResponsePerformanceAnalytics;
use App\Filament\Pages\HotspotHeatmapAnalytics;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;
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
            Css::make('maplibre', 'https://unpkg.com/maplibre-gl@6.7.0/dist/maplibre-gl.css'),
            Css::make('swm-theme', __DIR__.'/../../../resources/css/filament/swm-theme.css'),
            Js::make('leaflet', base_path('node_modules/leaflet/dist/leaflet.js'))->defer(),
            Js::make('maplibre', 'https://unpkg.com/maplibre-gl@6.7.0/dist/maplibre-gl.js')->defer(),
            Js::make('operations-map', resource_path('js/operations-map.js'))->defer(),
            Js::make('analytics-heatmap', resource_path('js/analytics-heatmap.js'))->defer(),
            Js::make('cctv-review', resource_path('js/cctv-review.js'))->defer(),
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->navigationGroups([
                NavigationGroup::make('Operations'),
                NavigationGroup::make('Analytics'),
                NavigationGroup::make('Master data'),
            ])
            ->navigationItems([
                NavigationItem::make('Illegal dumping')
                    ->group('Operations')
                    ->icon(Heroicon::OutlinedTrash)
                    ->sort(1),
                NavigationItem::make('Potholes')
                    ->group('Operations')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->sort(2),
            ])
            ->brandName('Siaga Selangor')
            ->brandLogo(asset('brand/siaga-selangor-wordmark.png'))
            ->favicon(asset('brand/selangor-crest.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::hex('#D2222B'),
                'warning' => Color::hex('#FDB915'),
                'danger' => Color::hex('#E83B3B'),
                'success' => Color::hex('#16A34A'),
                'info' => Color::hex('#06B6D4'),
            ])
            ->font('Figtree')
            ->darkMode(false)
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): \Illuminate\Contracts\View\View => view('filament.auth.login-intro'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                OperationsDashboard::class,
                PotholesDashboard::class,
                DistrictPriorityAnalytics::class,
                ResponsePerformanceAnalytics::class,
                HotspotHeatmapAnalytics::class,
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
