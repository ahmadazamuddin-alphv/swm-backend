<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\On;

class PriorityQueue extends TableWidget
{
    protected static ?string $heading = 'Priority queue';

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    protected static bool $isLazy = false;

    #[On('operations-updated')]
    public function refreshQueue(): void {}

    public function table(Table $table): Table
    {
        return ReportsTable::configure($table)
            ->query(Report::query()->open())
            ->description('Open cases, highest risk first. Select a report to investigate.')
            ->recordUrl(fn (Report $record) => ReportResource::getUrl('view', ['record' => $record]))
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10])
            ->poll('15s');
    }
}
