<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\ReportSource;
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
        return $schema->components([
            Section::make('Investigation details')->description('Use the investigation actions to change status, assign a team or close this case.')
                ->columns(2)->columnSpanFull()->schema([
                    Select::make('source')->options(collect(ReportSource::cases())->mapWithKeys(fn ($source) => [$source->value => $source->label()]))
                        ->default(ReportSource::Citizen->value)->required(),
                    TextInput::make('risk_score')->integer()->minValue(1)->maxValue(100)->default(50)->required()
                        ->helperText('Demo priority: 80–100 high risk, 60–79 elevated, 1–59 standard.'),
                    Select::make('waste_category_id')->label('Waste type')->relationship('wasteCategory', 'name')->searchable()->preload()->required(),
                    Select::make('zone_id')->label('Area')->relationship('zone', 'name')->searchable()->preload()->required(),
                    TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90),
                    TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
                    Textarea::make('notes')->label('Report description / investigation notes')->maxLength(5000)->columnSpanFull(),
                    FileUpload::make('photos')->label('Report photographs')->image()->multiple()->disk('public')
                        ->directory('reports/photos')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(8192)->maxFiles(5)->columnSpanFull()
                        ->helperText('Up to five photographs, 8 MB each. Attachments are stored locally.')
                        // Fixtures live in public/demo and are kept separately from uploaded evidence.
                        ->hidden(fn ($record) => collect($record?->photos ?? [])->contains(fn ($path) => str_starts_with($path, '/demo/'))),
                ]),
            Section::make('Reporter')->columns(3)->columnSpanFull()->schema([
                TextInput::make('reporter_name')->label('Name')->maxLength(255),
                TextInput::make('reporter_phone')->label('Phone')->maxLength(50),
                TextInput::make('reporter_email')->label('Email')->email()->maxLength(255),
            ]),
        ]);
    }
}
