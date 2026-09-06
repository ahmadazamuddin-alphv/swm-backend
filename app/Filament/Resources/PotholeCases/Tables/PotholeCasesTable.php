<?php

namespace App\Filament\Resources\PotholeCases\Tables;

use App\Enums\DominantImpact;
use App\Enums\PotholeStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PotholeCasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('road_name')->searchable()->limit(28),
                TextColumn::make('area')->sortable(),
                TextColumn::make('contractor.name')->label('Contractor')->toggleable()->searchable(),
                TextColumn::make('corridor_type')->badge(),
                TextColumn::make('dominant_impact')->badge()
                    ->color(fn (DominantImpact $state): string => match ($state) {
                        DominantImpact::Gdp => 'danger',
                        DominantImpact::Rakyat => 'warning',
                        DominantImpact::Balanced => 'success',
                    }),
                TextColumn::make('ai_risk_score')->label('AI risk')->sortable()->suffix('%'),
                TextColumn::make('estimated_cost_rm')->money('MYR')->sortable()->toggleable(),
                TextColumn::make('budget_spent_rm')->money('MYR')->sortable()->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
            ])
            ->defaultSort('ai_risk_score', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PotholeStatus::cases())->mapWithKeys(
                        fn (PotholeStatus $status) => [$status->value => $status->label()]
                    )),
                SelectFilter::make('dominant_impact')
                    ->options(collect(DominantImpact::cases())->mapWithKeys(
                        fn (DominantImpact $impact) => [$impact->value => $impact->label()]
                    )),
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
