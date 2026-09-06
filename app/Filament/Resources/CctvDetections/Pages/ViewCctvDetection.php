<?php

namespace App\Filament\Resources\CctvDetections\Pages;

use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use App\Filament\Resources\CctvDetections\CctvDetectionResource;
use App\Models\Report;
use App\Models\Zone;
use App\Services\CctvDemoAnalyzer;
use App\Services\ReportRecommendationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCctvDetection extends ViewRecord
{
    protected static string $resource = CctvDetectionResource::class;

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getHeading(): string
    {
        return 'CCTV review #'.$this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rerunAnalysis')
                ->label('Rerun demo analysis')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => $this->record->report_id === null)
                ->modalDescription('Choose a deterministic scene profile and camera metadata fixture to explore the review controls.')
                ->fillForm(fn (): array => [
                    'demo_scenario' => data_get($this->record->raw_result, 'scenario', 'roadside_dumping'),
                    'camera_preset' => data_get($this->record->raw_result, 'camera.key', 'shah_alam_sa17'),
                ])
                ->schema([
                    Select::make('demo_scenario')->label('Detection scenario')->options(CctvDemoAnalyzer::scenarioOptions())->required(),
                    Select::make('camera_preset')->label('Camera metadata')->options(CctvDemoAnalyzer::cameraOptions())->required(),
                ])
                ->action(function (array $data, CctvDemoAnalyzer $analyzer): void {
                    $analyzer->analyze($this->record, $data['demo_scenario'], $data['camera_preset']);
                    $this->record->refresh();
                    Notification::make()->title('Demo analysis refreshed')->success()->send();
                }),
            Action::make('createReport')
                ->label('Create report from detection')
                ->icon('heroicon-o-document-plus')
                ->visible(fn (): bool => $this->record->report_id === null && $this->record->activity_detected)
                ->requiresConfirmation()
                ->action(function (ReportRecommendationService $service): void {
                    $result = $this->record->raw_result ?? [];
                    $location = data_get($result, 'location');
                    $zone = match (data_get($result, 'camera.key')) {
                        'petaling_jaya_pj59' => Zone::query()->where('name', 'like', '%Petaling%')->first(),
                        'kajang_kj04' => Zone::query()->where('name', 'like', '%Kajang%')->first(),
                        'shah_alam_sa17' => Zone::query()->where('name', 'like', '%Shah Alam%')->first(),
                        default => null,
                    };
                    $report = Report::create([
                        'source' => ReportSource::Cctv,
                        'status' => ReportStatus::New,
                        'latitude' => $location['latitude'] ?? $this->record->latitude,
                        'longitude' => $location['longitude'] ?? $this->record->longitude,
                        'waste_category_id' => $this->record->waste_category_id,
                        'zone_id' => $zone?->id,
                        'risk_score' => max(50, (int) round((float) ($this->record->confidence ?? 50))),
                        'notes' => 'Created from CCTV detection #'.$this->record->id.'. '.(data_get($result, 'summary') ?? 'Simulated CCTV detection result.'),
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
        ];
    }
}
