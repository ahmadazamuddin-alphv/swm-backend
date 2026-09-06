<?php

namespace App\Filament\Resources\PotholeCases\Pages;

use App\Filament\Resources\PotholeCases\PotholeCaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPotholeCases extends ListRecords
{
    protected static string $resource = PotholeCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
