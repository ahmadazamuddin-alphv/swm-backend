<?php

namespace App\Filament\Resources\WasteCategories\Pages;

use App\Filament\Resources\WasteCategories\WasteCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWasteCategories extends ListRecords
{
    protected static string $resource = WasteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
