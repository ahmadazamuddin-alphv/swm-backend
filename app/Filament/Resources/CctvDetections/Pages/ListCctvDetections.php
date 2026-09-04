<?php

namespace App\Filament\Resources\CctvDetections\Pages;

use App\Filament\Resources\CctvDetections\CctvDetectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCctvDetections extends ListRecords
{
    protected static string $resource = CctvDetectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
