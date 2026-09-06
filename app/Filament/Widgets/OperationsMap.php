<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\DisposalCentre;
use App\Models\Report;
use App\Services\ReportRecommendationService;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class OperationsMap extends Widget
{
    protected string $view = 'filament.widgets.operations-map';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[On('operations-updated')]
    public function refreshMap(): void {}

    protected function getViewData(): array
    {
        $recommendations = app(ReportRecommendationService::class);
        $reports = Report::query()
            ->open()
            ->with(['wasteCategory', 'zone'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('risk_score')
            ->get()
            ->filter(fn (Report $report) => $recommendations->validCoordinates($report->latitude, $report->longitude))
            ->take(50)
            ->map(fn (Report $report) => [
                'id' => 'report-'.$report->id,
                'kind' => 'report',
                'tone' => $report->risk_score >= 80 ? 'high' : ($report->risk_score >= 60 ? 'elevated' : 'standard'),
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
                'title' => $report->reference,
                'description' => ($report->wasteCategory?->name ?? 'Unclassified').' · '.($report->zone?->name ?? 'Area unknown'),
                'meta' => $report->risk_score.'/100 · '.$report->status->label(),
                'url' => ReportResource::getUrl('view', ['record' => $report]),
                'action' => 'Investigate case',
            ]);

        $centres = DisposalCentre::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get()
            ->filter(fn (DisposalCentre $centre) => $recommendations->validCoordinates($centre->latitude, $centre->longitude))
            ->map(fn (DisposalCentre $centre) => [
                'id' => 'centre-'.$centre->id,
                'kind' => 'disposal',
                'tone' => 'disposal',
                'latitude' => (float) $centre->latitude,
                'longitude' => (float) $centre->longitude,
                'title' => $centre->name,
                'description' => $centre->address,
                'meta' => 'Demo disposal centre',
            ]);

        return [
            'mapData' => [
                'points' => $reports->concat($centres)->values()->all(),
                'lines' => [],
            ],
            'reportCount' => $reports->count(),
            'centreCount' => $centres->count(),
        ];
    }
}
