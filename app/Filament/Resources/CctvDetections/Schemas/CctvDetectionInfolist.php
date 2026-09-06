<?php

namespace App\Filament\Resources\CctvDetections\Schemas;

use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class CctvDetectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.cctv-detections.review')->columnSpanFull(),
            ]);
    }
}
