<?php

namespace App\Filament\Widgets;

use App\Enums\ReportStatus;
use App\Models\Report;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class OperationsOverview extends Widget
{
    protected string $view = 'filament.widgets.operations-overview';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[On('operations-updated')]
    public function refreshOverview(): void {}

    protected function getViewData(): array
    {
        return [
            'open' => Report::query()->open()->count(),
            'high' => Report::query()->open()->where('risk_score', '>=', 80)->count(),
            'solved' => Report::query()->where('status', ReportStatus::Solved)->count(),
            'overdue' => Report::query()->open()->where(function ($query) {
                $query->whereHas('assignment', fn ($q) => $q->where('status', 'active')->where('deadline', '<', now()))
                    ->orWhere(fn ($q) => $q->whereDoesntHave('assignment', fn ($a) => $a->where('status', 'active'))
                        ->where('suggested_deadline', '<', now()));
            })->count(),
        ];
    }
}
