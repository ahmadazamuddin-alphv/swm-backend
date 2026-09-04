<?php

namespace App\Filament\Resources\ResponsibleParties\Pages;

use App\Filament\Resources\ResponsibleParties\ResponsiblePartyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResponsibleParty extends EditRecord
{
    protected static string $resource = ResponsiblePartyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
