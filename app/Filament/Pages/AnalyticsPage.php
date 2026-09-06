<?php

namespace App\Filament\Pages;

use App\Models\PotholeCase;
use App\Models\Report;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AnalyticsPage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Analytics';

    protected string $view = 'filament.pages.analytics';

    #[Url(as: 'service', history: true)]
    public string $service = 'dumping';

    public string $area = 'all';

    public string $status = 'all';

    abstract public function analyticsType(): string;

    abstract public function analyticsTitle(): string;

    abstract public function analyticsDescription(): string;

    public function mount(): void
    {
        $this->service = in_array($this->service, ['dumping', 'potholes'], true) ? $this->service : 'dumping';
    }

    public function getSubheading(): ?string
    {
        return $this->analyticsDescription();
    }

    public function switchService(string $service): void
    {
        $service = in_array($service, ['dumping', 'potholes'], true) ? $service : 'dumping';
        $this->redirect(static::getUrl(['service' => $service]), navigate: true);
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('showDumping')
                ->label('Illegal dumping')
                ->color($this->service === 'dumping' ? 'primary' : 'gray')
                ->extraAttributes(['class' => 'swm-service-toggle swm-service-toggle-dumping '.($this->service === 'dumping' ? 'swm-service-toggle-active' : '')])
                ->action(fn () => $this->switchService('dumping')),
            Action::make('showPotholes')
                ->label('Potholes')
                ->color($this->service === 'potholes' ? 'primary' : 'gray')
                ->extraAttributes(['class' => 'swm-service-toggle swm-service-toggle-potholes '.($this->service === 'potholes' ? 'swm-service-toggle-active' : '')])
                ->action(fn () => $this->switchService('potholes')),
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportCsv'),
        ];
    }

    /** @return \Illuminate\Support\Collection<int, Report|PotholeCase> */
    public function records(): \Illuminate\Support\Collection
    {
        $records = $this->service === 'dumping'
            ? Report::query()->with('zone')->orderByDesc('submitted_at')->get()
            : PotholeCase::query()->orderByDesc('reported_at')->get();

        return $records->filter(function (Report|PotholeCase $record): bool {
            $area = $record instanceof Report
                ? ($record->zone?->taman ?? $record->zone?->name ?? 'Unassigned')
                : $record->area;

            return ($this->area === 'all' || $this->area === $area)
                && ($this->status === 'all' || $this->status === $record->status->value);
        })->values();
    }

    /** @return array<string, string> */
    public function areas(): array
    {
        $records = $this->service === 'dumping'
            ? Report::query()->with('zone')->get()
            : PotholeCase::query()->get();

        return $records->map(function (Report|PotholeCase $record): string {
            return $record instanceof Report
                ? ($record->zone?->taman ?? $record->zone?->name ?? 'Unassigned')
                : $record->area;
        })->filter()->unique()->sort()->mapWithKeys(fn (string $area) => [$area => $area])->all();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        $records = $this->service === 'dumping'
            ? Report::query()->get()
            : PotholeCase::query()->get();

        return $records->mapWithKeys(fn (Report|PotholeCase $record) => [
            $record->status->value => $record->status->label(),
        ])->all();
    }

    /** @return array<string, mixed> */
    public function analytics(): array
    {
        return match ($this->analyticsType()) {
            'priority' => $this->priorityAnalytics(),
            'performance' => $this->performanceAnalytics(),
            default => $this->hotspotAnalytics(),
        };
    }

    public function exportCsv(): StreamedResponse
    {
        $records = $this->records();
        $service = $this->service;

        return response()->streamDownload(function () use ($records, $service): void {
            $csv = fopen('php://output', 'w');
            fputcsv($csv, $service === 'dumping'
                ? ['Reference', 'Area', 'Status', 'Risk score', 'Submitted', 'Resolved']
                : ['Reference', 'Area', 'Road', 'Status', 'Severity', 'AI risk', 'Reported', 'Resolved']);

            foreach ($records as $record) {
                if ($record instanceof Report) {
                    fputcsv($csv, [$record->reference, $record->zone?->taman ?? $record->zone?->name, $record->status->label(), $record->risk_score, $record->submitted_at?->toDateTimeString(), $record->resolved_at?->toDateTimeString()]);
                    continue;
                }

                fputcsv($csv, [$record->reference, $record->area, $record->road_name, $record->status->label(), $record->severity, $record->ai_risk_score, $record->reported_at?->toDateTimeString(), $record->resolved_at?->toDateTimeString()]);
            }
            fclose($csv);
        }, "siaga-{$service}-{$this->analyticsType()}-".now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return array<string, mixed> */
    private function priorityAnalytics(): array
    {
        $rows = [];
        foreach ($this->records() as $record) {
            $area = $record instanceof Report ? ($record->zone?->taman ?? $record->zone?->name ?? 'Unassigned') : $record->area;
            $bucket = $record instanceof Report
                ? ($record->risk_score >= 80 ? 'High' : ($record->risk_score >= 60 ? 'Elevated' : 'Standard'))
                : ucfirst($record->severity);
            $rows[$area] ??= ['label' => $area, 'High' => 0, 'Elevated' => 0, 'Standard' => 0, 'Critical' => 0, 'Medium' => 0, 'Low' => 0];
            $rows[$area][$bucket]++;
        }

        $rows = collect($rows)->map(function (array $row): array {
            $row['total'] = array_sum(array_filter($row, 'is_int'));
            return $row;
        })->sortByDesc('total')->values()->all();

        return [
            'rows' => $rows,
            'keys' => $this->service === 'dumping' ? ['High', 'Elevated', 'Standard'] : ['Critical', 'High', 'Medium', 'Low'],
            'max' => max(1, collect($rows)->max('total') ?? 1),
        ];
    }

    /** @return array<string, mixed> */
    private function performanceAnalytics(): array
    {
        $weeks = collect(range(5, 0))->map(function (int $offset): array {
            $start = now()->startOfWeek()->subWeeks($offset);
            return ['key' => $start->format('Y-m-d'), 'label' => $start->format('d M'), 'start' => $start, 'end' => $start->copy()->endOfWeek(), 'received' => 0, 'resolved' => 0, 'hours' => []];
        });

        $weeks = $weeks->all();

        foreach ($this->records() as $record) {
            $submitted = $record instanceof Report ? $record->submitted_at : $record->reported_at;
            $resolved = $record->resolved_at;
            foreach ($weeks as $index => $week) {
                if ($submitted && $submitted->between($week['start'], $week['end'])) {
                    $week['received']++;
                }
                if ($resolved && $resolved->between($week['start'], $week['end'])) {
                    $week['resolved']++;
                    $week['hours'][] = abs($submitted?->diffInMinutes($resolved) ?? 0) / 60;
                }
                $weeks[$index] = $week;
            }
        }

        return ['weeks' => collect($weeks)->map(function (array $week): array {
            $week['median'] = count($week['hours']) ? round(collect($week['hours'])->median(), 1) : null;
            unset($week['hours'], $week['start'], $week['end']);
            return $week;
        })->all()];
    }

    /** @return array<string, mixed> */
    private function hotspotAnalytics(): array
    {
        $points = $this->records()->filter(fn (Report|PotholeCase $record) => $record->latitude !== null && $record->longitude !== null)
            ->map(function (Report|PotholeCase $record): array {
                $risk = $record instanceof Report ? $record->risk_score : $record->ai_risk_score;
                return ['lat' => (float) $record->latitude, 'lng' => (float) $record->longitude, 'risk' => $risk, 'label' => $record instanceof Report ? $record->reference : $record->reference, 'area' => $record instanceof Report ? ($record->zone?->taman ?? 'Unassigned') : $record->area];
            })->values()->all();

        return ['points' => $points, 'count' => count($points)];
    }

    protected function getViewData(): array
    {
        return [
            'analytics' => $this->analytics(),
            'title' => $this->analyticsTitle(),
            'serviceLabel' => $this->service === 'dumping' ? 'Illegal dumping' : 'Potholes',
        ];
    }
}
