<?php

namespace App\Filament\Resources\ResponsibleParties\Pages;

use App\Filament\Resources\ResponsibleParties\ResponsiblePartyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResponsibleParties extends ListRecords
{
    protected static string $resource = ResponsiblePartyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
