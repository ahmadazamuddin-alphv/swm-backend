<?php

namespace App\Filament\Pages;

use Filament\Support\Icons\Heroicon;

class ResponsePerformanceAnalytics extends AnalyticsPage
{
    protected static ?string $navigationLabel = 'Response performance';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Response performance';

    public function analyticsType(): string { return 'performance'; }
    public function analyticsTitle(): string { return 'Response performance'; }
    public function analyticsDescription(): string { return 'Track incoming demand, resolved cases and time to closure over the last six weeks.'; }
}
