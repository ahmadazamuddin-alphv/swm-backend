<?php

namespace App\Filament\Resources\DisposalCentres\Pages;

use App\Filament\Resources\DisposalCentres\DisposalCentreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDisposalCentres extends ListRecords
{
    protected static string $resource = DisposalCentreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
