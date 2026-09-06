<?php

namespace App\Filament\Pages;

use Filament\Support\Icons\Heroicon;

class HotspotHeatmapAnalytics extends AnalyticsPage
{
    protected static ?string $navigationLabel = 'Hotspot heatmap';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Hotspot heatmap';

    public function analyticsType(): string { return 'heatmap'; }
    public function analyticsTitle(): string { return 'Hotspot heatmap'; }
    public function analyticsDescription(): string { return 'See where demand concentrates, with larger red heat halos indicating higher risk.'; }
}
