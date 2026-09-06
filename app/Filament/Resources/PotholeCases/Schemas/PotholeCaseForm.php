<?php

namespace App\Filament\Resources\PotholeCases\Schemas;

use App\Enums\CorridorType;
use App\Enums\DominantImpact;
use App\Enums\PotholeStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PotholeCaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')
                            ->helperText('Auto-generated if blank.')
                            ->maxLength(50),
                        Select::make('status')
                            ->options(collect(PotholeStatus::cases())->mapWithKeys(
                                fn (PotholeStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required()
                            ->default(PotholeStatus::New->value),
                        TextInput::make('road_name')->required()->maxLength(255),
                        TextInput::make('area')->required()->maxLength(255),
                        Select::make('corridor_type')
                            ->options(collect(CorridorType::cases())->mapWithKeys(
                                fn (CorridorType $type) => [$type->value => $type->label()]
                            ))
                            ->required(),
                        Select::make('contractor_id')
                            ->label('Contractor')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('severity')->datalist(['low', 'medium', 'high']),
                        TextInput::make('latitude')->numeric(),
                        TextInput::make('longitude')->numeric(),
                    ]),
                Section::make('Risk & meters')
                    ->columns(3)
                    ->schema([
                        TextInput::make('street_risk')->numeric()->minValue(0)->maxValue(100)->required(),
                        TextInput::make('ai_risk_score')->label('Overall AI risk %')->numeric()->minValue(0)->maxValue(100)->required(),
                        TextInput::make('traffic_impact')->numeric()->minValue(0)->maxValue(100)->required(),
                        TextInput::make('safety_risk')->numeric()->minValue(0)->maxValue(100)->required(),
                        TextInput::make('cost_to_fix')->numeric()->minValue(0)->maxValue(100)->required(),
                    ]),
                Section::make('Impact (rakyat vs GDP)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rakyat_impact')->numeric()->minValue(0)->maxValue(100)->required(),
                        TextInput::make('gdp_impact')->label('State GDP impact')->numeric()->minValue(0)->maxValue(100)->required(),
                        Select::make('dominant_impact')
                            ->options(collect(DominantImpact::cases())->mapWithKeys(
                                fn (DominantImpact $impact) => [$impact->value => $impact->label()]
                            ))
                            ->required(),
                        TextInput::make('impact_reason')->maxLength(255)->columnSpanFull(),
                        TextInput::make('spend_recommendation')->maxLength(255)->columnSpanFull(),
                    ]),
                Section::make('Evidence')
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Pothole photo')
                            ->image()
                            ->directory('potholes')
                            ->disk('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make('Budget')
                    ->columns(2)
                    ->schema([
                        TextInput::make('estimated_cost_rm')->numeric()->minValue(0)->prefix('RM')->required(),
                        TextInput::make('budget_spent_rm')->numeric()->minValue(0)->prefix('RM')
                            ->helperText('Counts toward annual meter when status is in progress or solved.'),
                        DateTimePicker::make('reported_at'),
                        DateTimePicker::make('resolved_at'),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}
