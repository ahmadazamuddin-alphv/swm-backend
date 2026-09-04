<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Case overview')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reference'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('source')->badge(),
                        TextEntry::make('risk_score')->badge(),
                        TextEntry::make('wasteCategory.name')->label('Waste type'),
                        TextEntry::make('zone.name')->label('Zone'),
                        TextEntry::make('submitted_at')->dateTime(),
                        TextEntry::make('resolved_at')->dateTime()->placeholder('—'),
                    ]),
                Section::make('Location & media')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('latitude'),
                        TextEntry::make('longitude'),
                        ImageEntry::make('photos')
                            ->disk('public')
                            ->columnSpanFull(),
                    ]),
                Section::make('Reporter')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reporter_name')->placeholder('—'),
                        TextEntry::make('reporter_phone')->placeholder('—'),
                        TextEntry::make('reporter_email')->placeholder('—'),
                    ]),
                Section::make('Responsible party & AI')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('zone.responsibleParty.name')->label('Zone owner'),
                        TextEntry::make('assignment.responsibleParty.name')->label('Assigned party'),
                        TextEntry::make('assignment.driver.name')->label('Assigned driver'),
                        TextEntry::make('suggested_manpower'),
                        TextEntry::make('suggested_lorries'),
                        TextEntry::make('suggested_deadline')->dateTime(),
                        TextEntry::make('suggestedDisposalCentre.name')->label('Suggested disposal centre'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('false_report_reason')->placeholder('—'),
                        TextEntry::make('notes')->placeholder('—'),
                    ]),
            ]);
    }
}
