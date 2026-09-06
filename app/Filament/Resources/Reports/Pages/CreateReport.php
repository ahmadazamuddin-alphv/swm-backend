<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Services\ReportRecommendationService;
use Filament\Resources\Pages\CreateRecord;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'status' => ReportStatus::New, 'resolved_at' => null, 'false_report_reason' => null];
    }

    protected function getRedirectUrl(): string
    {
        return ReportResource::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        app(ReportRecommendationService::class)->applyToReport($this->record);
    }
}
