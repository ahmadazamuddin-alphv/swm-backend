<?php

namespace App\Filament\Resources\CctvDetections\Schemas;

use App\Services\CctvDemoAnalyzer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CctvDetectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Source video')
                    ->description('Upload a short CCTV clip, then review the simulated detections against the actual footage.')
                    ->columnSpanFull()->schema([
                        FileUpload::make('video_path')
                            ->label('CCTV video')
                            ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'])
                            ->disk('public')->visibility('public')->directory('cctv/videos')
                            ->maxSize(102400)->openable()->downloadable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('MP4, WebM, MOV or AVI up to 100 MB. Stored locally for this POC.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Demo analysis controls')
                    ->description('These controls select a deterministic scenario so officers can explore the review surface without a live AI service.')
                    ->columns(2)->columnSpanFull()->schema([
                        Select::make('demo_scenario')->label('Detection scenario')
                            ->options(CctvDemoAnalyzer::scenarioOptions())
                            ->default('roadside_dumping')->required()->dehydrated(false),
                        Select::make('camera_preset')->label('Camera metadata')
                            ->options(CctvDemoAnalyzer::cameraOptions())
                            ->default('shah_alam_sa17')->required()->dehydrated(false),
                    ]),
                Section::make('Stored result')
                    ->columns(3)->columnSpanFull()->schema([
                        TextInput::make('detected_waste_type')->label('Waste type')->disabled()->dehydrated(false),
                        TextInput::make('detected_activity')->label('Illegal-dumping activity')->disabled()->dehydrated(false),
                        TextInput::make('confidence')->label('Confidence')->suffix('%')->disabled()->dehydrated(false),
                        TextInput::make('latitude')->numeric()->disabled()->dehydrated(false),
                        TextInput::make('longitude')->numeric()->disabled()->dehydrated(false),
                        TextInput::make('detected_report')->label('Linked report')->disabled()->dehydrated(false),
                    ]),
            ]);
    }
}
