<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Filament\Resources\Reports\ReportResource;
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
            ->description('Assign or reassign from a report investigation so its status and history stay together.')
            ->recordUrl(fn ($record) => ReportResource::getUrl('view', ['record' => $record->report_id]))
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
