<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PotholeCases\PotholeCaseResource;
use App\Models\PotholeCase;
use App\Services\PotholeBudgetService;
use App\Services\PotholeForecastService;
use App\Services\PotholeSpendPriorityService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PotholesDashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Potholes dashboard';

    protected static string|\UnitEnum|null $navigationGroup = 'Potholes';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Potholes operations';

    protected static ?string $slug = 'potholes';

    protected string $view = 'filament.pages.potholes-dashboard';

    public string $activeTab = 'cases';

    public int $rainfall = 40;

    public int $uv = 55;

    public int $temperature = 31;

    public int $vehicle_volume = 50;

    public ?string $activePreset = 'normal';

    public ?int $selectedCaseId = null;

    public bool $showImpactPanel = false;

    public function mount(): void
    {
        $this->applyPreset('normal');
        $this->selectedCaseId = PotholeCase::query()->open()->orderByDesc('ai_risk_score')->value('id');
        $this->showImpactPanel = false;
    }

    public function getSubheading(): ?string
    {
        return 'Annual budget meter, spend priorities, case impact and formation forecast — admin POC.';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['cases', 'forecast'], true) ? $tab : 'cases';
    }

    public function applyPreset(string $key): void
    {
        $presets = app(PotholeForecastService::class)->presets();

        if (! isset($presets[$key])) {
            return;
        }

        $this->activePreset = $key;
        $this->rainfall = $presets[$key]['rainfall'];
        $this->uv = $presets[$key]['uv'];
        $this->temperature = $presets[$key]['temperature'];
        $this->vehicle_volume = $presets[$key]['vehicle_volume'];
    }

    public function selectCase(int $id): void
    {
        $this->selectedCaseId = $id;
        $this->showImpactPanel = true;
        $this->activeTab = 'cases';
    }

    public function backToPriorities(): void
    {
        $this->showImpactPanel = false;
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['rainfall', 'uv', 'temperature', 'vehicle_volume'], true)) {
            $this->activePreset = null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $budget = app(PotholeBudgetService::class)->meter();
        $priorities = app(PotholeSpendPriorityService::class)->ranked(5);
        $forecast = app(PotholeForecastService::class)->simulate([
            'rainfall' => $this->rainfall,
            'uv' => $this->uv,
            'temperature' => $this->temperature,
            'vehicle_volume' => $this->vehicle_volume,
        ]);
        $cases = PotholeCase::query()->with('contractor')->orderByDesc('ai_risk_score')->get();
        $selected = $this->selectedCaseId
            ? PotholeCase::query()->with('contractor')->find($this->selectedCaseId)
            : $cases->first();

        $mapPoints = $cases
            ->filter(fn (PotholeCase $case) => $case->latitude !== null && $case->longitude !== null)
            ->map(fn (PotholeCase $case) => [
                'id' => 'pothole-'.$case->id,
                'caseId' => $case->id,
                'kind' => 'pothole',
                'tone' => $case->dominant_impact->value,
                'label' => strtoupper(substr($case->dominant_impact->value, 0, 1)),
                'selected' => $selected?->id === $case->id,
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

        return [
            'budget' => $budget,
            'priorities' => $priorities,
            'forecast' => $forecast,
            'presets' => app(PotholeForecastService::class)->presets(),
            'cases' => $cases,
            'selected' => $selected,
            'mapData' => [
                'points' => $mapPoints,
                'lines' => [],
            ],
            'caseIndexUrl' => PotholeCaseResource::getUrl('index'),
            'selectedUrl' => $selected ? PotholeCaseResource::getUrl('view', ['record' => $selected]) : null,
        ];
    }
}
