<?php

namespace App\Filament\Resources\PotholeCases\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PotholeCaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evidence')
                    ->schema([
                        ImageEntry::make('photo_path')
                            ->label('Pothole photo')
                            ->disk('public')
                            ->visibility('public')
                            ->imageHeight(280)
                            ->columnSpanFull(),
                    ]),
                Section::make('Case')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reference'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('corridor_type')->badge(),
                        TextEntry::make('road_name'),
                        TextEntry::make('area'),
                        TextEntry::make('severity')->badge(),
                        TextEntry::make('latitude'),
                        TextEntry::make('longitude'),
                        TextEntry::make('reported_at')->dateTime(),
                    ]),
                Section::make('AI risk & meters')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ai_risk_score')->label('Overall AI risk %')->suffix('%'),
                        TextEntry::make('street_risk'),
                        TextEntry::make('traffic_impact'),
                        TextEntry::make('safety_risk'),
                        TextEntry::make('cost_to_fix'),
                    ]),
                Section::make('Rakyat vs GDP impact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rakyat_impact')->label('Rakyat satisfaction Δ'),
                        TextEntry::make('gdp_impact')->label('State GDP Δ'),
                        TextEntry::make('dominant_impact')->badge(),
                        TextEntry::make('impact_reason')->columnSpanFull(),
                        TextEntry::make('spend_recommendation')->columnSpanFull(),
                    ]),
                Section::make('Budget')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('estimated_cost_rm')->money('MYR'),
                        TextEntry::make('budget_spent_rm')->money('MYR'),
                        TextEntry::make('resolved_at')->dateTime()->placeholder('—'),
                        TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
