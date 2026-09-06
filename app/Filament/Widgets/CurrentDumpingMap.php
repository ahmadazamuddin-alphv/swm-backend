<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use App\Services\ReportRecommendationService;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class CurrentDumpingMap extends Widget
{
    protected string $view = 'filament.widgets.current-dumping-map';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[On('operations-updated')]
    public function refreshCurrentReports(): void {}

    protected function getViewData(): array
    {
        $recommendations = app(ReportRecommendationService::class);
        $reports = Report::query()
            ->open()
            ->with(['wasteCategory', 'zone'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('submitted_at')
            ->get()
            ->filter(fn (Report $report) => $recommendations->validCoordinates($report->latitude, $report->longitude))
            ->take(50)
            ->values();

        $locations = $reports
            ->groupBy(fn (Report $report) => $report->latitude.','.$report->longitude)
            ->map(function ($reportsAtLocation): array {
                /** @var Report $leadReport */
                $leadReport = $reportsAtLocation->sortByDesc('risk_score')->first();
                $count = $reportsAtLocation->count();
                $categories = $reportsAtLocation
                    ->pluck('wasteCategory.name')
                    ->filter()
                    ->unique()
                    ->take(2)
                    ->implode(', ');

                return [
                    'id' => 'citizen-location-'.$leadReport->latitude.'-'.$leadReport->longitude,
                    'kind' => 'report',
                    'tone' => $leadReport->risk_score >= 80 ? 'high' : ($leadReport->risk_score >= 60 ? 'elevated' : 'standard'),
                    'label' => $count > 1 ? (string) $count : null,
                    'latitude' => (float) $leadReport->latitude,
                    'longitude' => (float) $leadReport->longitude,
                    'title' => $count > 1 ? $count.' citizen reports at this location' : $leadReport->reference,
                    'description' => ($categories ?: 'Unclassified').' · '.($leadReport->zone?->name ?? 'Area unknown'),
                    'meta' => $count > 1
                        ? 'Highest risk '.$leadReport->risk_score.'/100 · open the priority report'
                        : 'Submitted '.($leadReport->submitted_at?->diffForHumans() ?? 'recently').' · risk '.$leadReport->risk_score.'/100',
                    'url' => ReportResource::getUrl('view', ['record' => $leadReport]),
                    'action' => $count > 1 ? 'Open priority report' : 'Investigate report',
                ];
            })
            ->values();

        return [
            'reportCount' => $reports->count(),
            'locationCount' => $locations->count(),
            'mapData' => [
                'points' => $locations->all(),
                'lines' => [],
            ],
        ];
    }
}
