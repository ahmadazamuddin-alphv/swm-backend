<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Services\ReportRecommendationService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! $this->record->fresh()->isOpen()) {
            throw ValidationException::withMessages(['status' => 'This case is closed. Return to the investigation view.']);
        }

        return Arr::only($data, ['source', 'latitude', 'longitude', 'photos', 'reporter_name', 'reporter_phone', 'reporter_email', 'waste_category_id', 'zone_id', 'risk_score', 'notes']);
    }

    protected function afterSave(): void
    {
        app(ReportRecommendationService::class)->applyToReport($this->record);
        $this->record->activities()->create(['user_id' => auth()->id(), 'event' => 'Investigation updated', 'description' => 'Case details updated; demo suggestions recalculated without changing the assignment.']);
    }

    protected function getRedirectUrl(): string
    {
        return ReportResource::getUrl('view', ['record' => $this->record]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
