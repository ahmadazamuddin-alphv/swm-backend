<?php

namespace App\Filament\Resources\CctvDetections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CctvDetectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Run')->sortable(),
                TextColumn::make('raw_result.scenario_label')->label('Scenario')->placeholder('Awaiting analysis')->wrap(),
                IconColumn::make('activity_detected')->label('Activity')->boolean(),
                TextColumn::make('wasteCategory.name')->label('Waste type')->placeholder('None detected'),
                TextColumn::make('confidence')->suffix('%')->sortable(),
                TextColumn::make('raw_result.camera.label')->label('Camera')->placeholder('Unknown')->wrap()->toggleable(),
                TextColumn::make('report.reference')->label('Report')->placeholder('Not linked'),
                TextColumn::make('created_at')->label('Uploaded')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('Review'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
