<?php

namespace App\Filament\Resources\CctvDetections\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CctvDetectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('video_path')
                    ->label('Video')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'])
                    ->directory('cctv/videos')
                    ->required()
                    ->columnSpanFull(),
                Select::make('waste_category_id')
                    ->relationship('wasteCategory', 'name')
                    ->searchable()
                    ->preload(),
                Toggle::make('activity_detected')->default(true),
                TextInput::make('latitude')->numeric(),
                TextInput::make('longitude')->numeric(),
                TextInput::make('confidence')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                Select::make('report_id')
                    ->relationship('report', 'reference')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }
}
