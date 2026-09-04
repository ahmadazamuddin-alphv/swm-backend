<?php

namespace App\Filament\Resources\CctvDetections\Pages;

use App\Filament\Resources\CctvDetections\CctvDetectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCctvDetection extends EditRecord
{
    protected static string $resource = CctvDetectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
