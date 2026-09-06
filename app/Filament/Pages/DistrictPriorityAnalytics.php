<?php

namespace App\Filament\Pages;

use Filament\Support\Icons\Heroicon;

class DistrictPriorityAnalytics extends AnalyticsPage
{
    protected static ?string $navigationLabel = 'District priority';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'District priority';

    public function analyticsType(): string { return 'priority'; }
    public function analyticsTitle(): string { return 'District priority'; }
    public function analyticsDescription(): string { return 'Compare the risk and severity mix by district to direct crews and budget first.'; }
}
