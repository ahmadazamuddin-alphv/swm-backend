<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Widgets\HotspotZonesWidget;
use App\Filament\Widgets\OperationsMap;
use App\Filament\Widgets\OperationsOverview;
use App\Filament\Widgets\PriorityQueue;
use App\Filament\Widgets\ReportInbox;
use App\Services\OperationsDemo;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;

class OperationsDashboard extends Dashboard
{
    protected static ?string $title = 'Government operations';

    protected static ?string $navigationLabel = 'Operations overview';

    public function getSubheading(): ?string
    {
        return 'Review incoming cases, coordinate clearance and record the outcome. Local demonstration.';
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'xl' => 3];
    }

    public function getWidgets(): array
    {
        return [OperationsOverview::class, PriorityQueue::class, ReportInbox::class, OperationsMap::class, HotspotZonesWidget::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('simulateReport')
                ->label('Simulate new report')
                ->icon('heroicon-o-plus')
                ->action(function (OperationsDemo $demo): void {
                    $report = $demo->incoming();
                    $this->dispatch('operations-updated');
                    Notification::make()->title("{$report->reference} received")
                        ->body('The demo report is in the queue and your inbox.')
                        ->actions([Action::make('investigate')->label('Investigate')->url(ReportResource::getUrl('view', ['record' => $report]))])
                        ->success()->send();
                }),
            Action::make('reports')->label('All reports')->color('gray')->url(ReportResource::getUrl()),
        ];
    }
}
