<?php

namespace App\Filament\Resources\PotholeCases\Pages;

use App\Filament\Resources\PotholeCases\PotholeCaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPotholeCase extends EditRecord
{
    protected static string $resource = PotholeCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
