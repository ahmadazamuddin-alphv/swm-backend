<?php

namespace App\Filament\Resources\CctvDetections\Pages;

use App\Filament\Resources\CctvDetections\CctvDetectionResource;
use App\Services\CctvDemoAnalyzer;
use Filament\Resources\Pages\CreateRecord;

class CreateCctvDetection extends CreateRecord
{
    protected static string $resource = CctvDetectionResource::class;

    private string $analysisScenario = 'roadside_dumping';

    private string $cameraPreset = 'shah_alam_sa17';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->analysisScenario = $this->data['demo_scenario'] ?? 'roadside_dumping';
        $this->cameraPreset = $this->data['camera_preset'] ?? 'shah_alam_sa17';

        return [...$data, 'activity_detected' => false, 'raw_result' => ['mode' => 'queued']];
    }

    protected function afterCreate(): void
    {
        app(CctvDemoAnalyzer::class)->analyze($this->record, $this->analysisScenario, $this->cameraPreset);
    }

    protected function getRedirectUrl(): string
    {
        return CctvDetectionResource::getUrl('view', ['record' => $this->record]);
    }
}
