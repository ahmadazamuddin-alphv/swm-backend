<?php

namespace App\Filament\Widgets;

use App\Models\Driver;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DriverPerformanceWidget extends TableWidget
{
    protected static ?string $heading = 'Abang lori job counts';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Driver::query()
                    ->with('contractor')
                    ->withCount('assignments')
                    ->orderByDesc('assignments_count')
            )
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('contractor.name')->label('Contractor'),
                TextColumn::make('vehicle_plate'),
                TextColumn::make('assignments_count')->label('Jobs')->sortable(),
            ])
            ->paginated(false);
    }
}
