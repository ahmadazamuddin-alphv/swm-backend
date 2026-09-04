<?php

namespace App\Filament\Widgets;

use App\Models\Zone;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class HotspotZonesWidget extends TableWidget
{
    protected static ?string $heading = 'Hotspot zones';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Zone::query()
                    ->withCount('reports')
                    ->orderByDesc('reports_count')
            )
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('postcode'),
                TextColumn::make('taman'),
                TextColumn::make('area_type')->badge(),
                TextColumn::make('reports_count')->label('Reports')->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5]);
    }
}
