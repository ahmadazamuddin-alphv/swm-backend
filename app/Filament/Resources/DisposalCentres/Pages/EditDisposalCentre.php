<?php

namespace App\Filament\Resources\DisposalCentres\Pages;

use App\Filament\Resources\DisposalCentres\DisposalCentreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDisposalCentre extends EditRecord
{
    protected static string $resource = DisposalCentreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
