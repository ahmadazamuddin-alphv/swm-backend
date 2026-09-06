<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Widgets\HotspotZonesWidget;
use App\Filament\Widgets\CurrentDumpingMap;
use App\Filament\Widgets\OperationsMap;
use App\Filament\Widgets\OperationsOverview;
use App\Filament\Widgets\PotholeOverview;
use App\Filament\Widgets\PriorityQueue;
use App\Filament\Widgets\ReportInbox;
use App\Services\OperationsDemo;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Livewire\Attributes\Url;

class OperationsDashboard extends Dashboard
{
    protected static ?string $title = 'Dashboard overview';

    protected static ?string $navigationLabel = 'Dashboard overview';

    protected static ?int $navigationSort = -10;

    #[Url(as: 'service', history: true)]
    public string $service = 'dumping';

    public function getSubheading(): ?string
    {
        return $this->service === 'potholes'
            ? 'Road-condition risk, repair spend and forecasted pressure across Selangor.'
            : 'Review incoming reports, coordinate clearance and record the outcome.';
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'xl' => 3];
    }

    public function getWidgets(): array
    {
        if ($this->service === 'potholes') {
            return [PotholeOverview::class];
        }

        return [OperationsOverview::class, CurrentDumpingMap::class, PriorityQueue::class, ReportInbox::class, OperationsMap::class, HotspotZonesWidget::class];
    }

    public function switchService(string $service): void
    {
        $service = in_array($service, ['dumping', 'potholes'], true)
            ? $service
            : 'dumping';

        // Dashboard widget discovery happens on page mount. Navigate to the
        // selected service URL so Filament mounts that service's widget set,
        // rather than leaving the previous dashboard's widgets in place.
        $this->redirect(static::getUrl(['service' => $service]), navigate: true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('showDumping')
                ->label('Illegal dumping')
                ->color($this->service === 'dumping' ? 'primary' : 'gray')
                ->extraAttributes([
                    'class' => 'swm-service-toggle swm-service-toggle-dumping '.($this->service === 'dumping' ? 'swm-service-toggle-active' : ''),
                ])
                ->action(fn () => $this->switchService('dumping')),
            Action::make('showPotholes')
                ->label('Potholes')
                ->color($this->service === 'potholes' ? 'primary' : 'gray')
                ->extraAttributes([
                    'class' => 'swm-service-toggle swm-service-toggle-potholes '.($this->service === 'potholes' ? 'swm-service-toggle-active' : ''),
                ])
                ->action(fn () => $this->switchService('potholes')),
            Action::make('simulateReport')
                ->label('Simulate dumping report')
                ->icon('heroicon-o-plus')
                ->visible($this->service === 'dumping')
                ->action(function (OperationsDemo $demo): void {
                    $report = $demo->incoming();
                    $this->dispatch('operations-updated');
                    Notification::make()->title("{$report->reference} received")
                        ->body('The demo report is in the queue and your inbox.')
                        ->actions([Action::make('investigate')->label('Investigate')->url(ReportResource::getUrl('view', ['record' => $report]))])
                        ->success()->send();
                }),
            Action::make('reports')
                ->label('Dumping reports')
                ->color('gray')
                ->visible($this->service === 'dumping')
                ->url(ReportResource::getUrl()),
        ];
    }
}
