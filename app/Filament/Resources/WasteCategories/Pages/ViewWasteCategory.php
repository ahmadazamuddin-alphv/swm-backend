<?php

namespace App\Filament\Resources\WasteCategories\Pages;

use App\Filament\Resources\WasteCategories\WasteCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWasteCategory extends ViewRecord
{
    protected static string $resource = WasteCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
