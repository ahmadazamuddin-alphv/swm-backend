<?php

namespace App\Filament\Resources\CctvDetections\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CctvDetectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('video_path'),
                TextEntry::make('wasteCategory.name')->label('Waste type'),
                TextEntry::make('activity_detected')->badge(),
                TextEntry::make('latitude'),
                TextEntry::make('longitude'),
                TextEntry::make('confidence'),
                TextEntry::make('report.reference')->label('Linked report')->placeholder('—'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
