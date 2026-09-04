<?php

namespace App\Filament\Resources\Assignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report.reference')->searchable()->sortable(),
                TextColumn::make('responsibleParty.name')->label('Party'),
                TextColumn::make('driver.name')->label('Driver'),
                TextColumn::make('deadline')->dateTime()->sortable(),
                TextColumn::make('manpower'),
                TextColumn::make('lorries'),
                TextColumn::make('status')->badge(),
            ])
            ->defaultSort('deadline')
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
