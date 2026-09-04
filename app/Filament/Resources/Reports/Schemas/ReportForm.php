<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Case')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')
                            ->disabledOn('edit')
                            ->dehydrated()
                            ->helperText('Auto-generated if left blank on create.'),
                        Select::make('source')
                            ->options(collect(ReportSource::cases())->mapWithKeys(
                                fn (ReportSource $source) => [$source->value => $source->label()]
                            ))
                            ->required()
                            ->default(ReportSource::Citizen->value),
                        Select::make('status')
                            ->options(collect(ReportStatus::cases())->mapWithKeys(
                                fn (ReportStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required()
                            ->default(ReportStatus::New->value),
                        TextInput::make('risk_score')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->default(50),
                        Select::make('waste_category_id')
                            ->relationship('wasteCategory', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('zone_id')
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('submitted_at'),
                        DateTimePicker::make('resolved_at'),
                    ]),
                Section::make('Location & media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')->numeric(),
                        TextInput::make('longitude')->numeric(),
                        FileUpload::make('photos')
                            ->image()
                            ->multiple()
                            ->directory('reports/photos')
                            ->columnSpanFull(),
                    ]),
                Section::make('Reporter')
                    ->columns(2)
                    ->schema([
                        TextInput::make('reporter_name')->maxLength(255),
                        TextInput::make('reporter_phone')->tel()->maxLength(50),
                        TextInput::make('reporter_email')->email()->maxLength(255),
                    ]),
                Section::make('AI recommendations')
                    ->columns(2)
                    ->schema([
                        TextInput::make('suggested_manpower')->numeric()->minValue(1),
                        TextInput::make('suggested_lorries')->numeric()->minValue(1),
                        DateTimePicker::make('suggested_deadline'),
                        Select::make('suggested_disposal_centre_id')
                            ->relationship('suggestedDisposalCentre', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Review')
                    ->schema([
                        Textarea::make('false_report_reason')->columnSpanFull(),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}
