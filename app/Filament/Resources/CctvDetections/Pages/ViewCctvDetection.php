<?php

namespace App\Filament\Resources\CctvDetections\Pages;

use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use App\Filament\Resources\CctvDetections\CctvDetectionResource;
use App\Models\Report;
use App\Services\ReportRecommendationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCctvDetection extends ViewRecord
{
    protected static string $resource = CctvDetectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createReport')
                ->label('Create report from detection')
                ->icon('heroicon-o-document-plus')
                ->visible(fn (): bool => $this->record->report_id === null && $this->record->activity_detected)
                ->requiresConfirmation()
                ->action(function (ReportRecommendationService $service): void {
                    $report = Report::create([
                        'source' => ReportSource::Cctv,
                        'status' => ReportStatus::New,
                        'latitude' => $this->record->latitude,
                        'longitude' => $this->record->longitude,
                        'waste_category_id' => $this->record->waste_category_id,
                        'risk_score' => max(50, (int) round((float) ($this->record->confidence ?? 50))),
                        'notes' => 'Created from CCTV detection #'.$this->record->id,
                        'submitted_at' => now(),
                    ]);

                    $service->applyToReport($report);

                    $this->record->update(['report_id' => $report->id]);

                    Notification::make()
                        ->title('Report created')
                        ->body($report->reference)
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
