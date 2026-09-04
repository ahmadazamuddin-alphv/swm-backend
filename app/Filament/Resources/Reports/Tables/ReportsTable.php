<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Enums\ReportStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('risk_score')->sortable()->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'danger',
                        $state >= 60 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('source')->badge(),
                TextColumn::make('wasteCategory.name')->label('Waste')->toggleable(),
                TextColumn::make('zone.name')->label('Zone')->toggleable(),
                TextColumn::make('reporter_name')->toggleable(),
                TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->defaultSort('risk_score', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ReportStatus::cases())->mapWithKeys(
                        fn (ReportStatus $status) => [$status->value => $status->label()]
                    )),
                SelectFilter::make('zone_id')
                    ->relationship('zone', 'name')
                    ->label('Zone'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
