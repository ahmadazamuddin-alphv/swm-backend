<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['wasteCategory', 'zone', 'assignment.responsibleParty']))
            ->columns([
                TextColumn::make('reference')->label('Case')->searchable()->weight('semibold')
                    ->description(fn (Report $record) => $record->wasteCategory?->name ?? 'Unclassified'),
                TextColumn::make('risk_score')->label('Risk')->sortable()->badge()
                    ->formatStateUsing(fn (Report $record) => $record->risk_score.' · '.$record->priorityLabel())
                    ->color(fn (int $state) => $state >= 80 ? 'danger' : ($state >= 60 ? 'warning' : 'gray')),
                TextColumn::make('status')->badge(),
                TextColumn::make('zone.name')->label('Area')->searchable()->wrap()->toggleable(),
                TextColumn::make('assignment.responsibleParty.name')->label('Assigned team')->placeholder('Unassigned')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('submitted_at')->label('Received')->since()->dateTimeTooltip()->sortable(),
                TextColumn::make('suggested_deadline')->label('Deadline')
                    ->state(fn (Report $record) => $record->assignment?->deadline ?? $record->suggested_deadline)
                    ->dateTime('d M, H:i')->placeholder('Not set')
                    ->color(fn (Report $record) => $record->isOpen() && ($record->assignment?->deadline ?? $record->suggested_deadline)?->isPast() ? 'danger' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query) => $query->orderByDesc('risk_score')->orderBy('submitted_at')->orderBy('id'))
            ->recordUrl(fn (Report $record) => ReportResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('status')->options(collect(ReportStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])),
                SelectFilter::make('zone_id')->label('Area')->relationship('zone', 'name')->searchable()->preload(),
                SelectFilter::make('waste_category_id')->label('Waste type')->relationship('wasteCategory', 'name'),
                Filter::make('high_risk')->label('High risk only')->query(fn (Builder $query) => $query->where('risk_score', '>=', 80)),
            ])
            ->recordActions([
                Action::make('investigate')
                    ->label('Investigate')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Report $record) => ReportResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No matching cases')
            ->emptyStateDescription('Try clearing the filters, or simulate a new report from the operations overview.')
            ->striped(false);
    }
}
