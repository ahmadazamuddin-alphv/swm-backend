<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PotholeCases\PotholeCaseResource;
use App\Models\PotholeCase;
use App\Services\PotholeBudgetService;
use App\Services\PotholeForecastService;
use App\Services\PotholeSpendPriorityService;
use Filament\Widgets\Widget;

class PotholeOverview extends Widget
{
    protected string $view = 'filament.widgets.pothole-overview';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        $cases = PotholeCase::query()
            ->open()
            ->with('contractor')
            ->orderByDesc('ai_risk_score')
            ->get();

        $mapPoints = $cases
            ->filter(fn (PotholeCase $case) => $case->latitude !== null && $case->longitude !== null)
            ->map(fn (PotholeCase $case) => [
                'id' => 'pothole-'.$case->id,
                'caseId' => $case->id,
                'kind' => 'pothole',
                'tone' => $case->dominant_impact->value,
                'label' => strtoupper(substr($case->dominant_impact->value, 0, 1)),
                'latitude' => (float) $case->latitude,
                'longitude' => (float) $case->longitude,
                'title' => $case->reference,
                'description' => $case->locationLabel(),
                'meta' => $case->dominant_impact->label().' · AI risk '.$case->ai_risk_score.'%',
                'url' => PotholeCaseResource::getUrl('view', ['record' => $case]),
                'action' => 'Open case',
            ])
            ->values()
            ->all();

        $forecast = app(PotholeForecastService::class)->simulate(
            app(PotholeForecastService::class)->presets()['normal'],
        );

        return [
            'openCount' => $cases->count(),
            'criticalCount' => $cases->where('ai_risk_score', '>=', 80)->count(),
            'averageRisk' => (int) round($cases->avg('ai_risk_score') ?? 0),
            'budget' => app(PotholeBudgetService::class)->meter(),
            'forecast' => $forecast,
            'priorities' => app(PotholeSpendPriorityService::class)->ranked(4),
            'mapData' => ['points' => $mapPoints, 'lines' => []],
            'caseIndexUrl' => PotholeCaseResource::getUrl('index'),
        ];
    }
}
