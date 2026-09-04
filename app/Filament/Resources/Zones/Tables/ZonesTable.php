<?php

namespace App\Filament\Resources\Zones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('postcode')->sortable(),
                TextColumn::make('taman')->searchable(),
                TextColumn::make('area_type')->badge(),
                TextColumn::make('socioeconomic_group')->badge(),
                TextColumn::make('responsibleParty.name')->label('Responsible party'),
                TextColumn::make('reports_count')->counts('reports')->label('Reports'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
