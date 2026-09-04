<?php

namespace App\Filament\Widgets;

use App\Enums\ReportStatus;
use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpsStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $openStatuses = [
            ReportStatus::New->value,
            ReportStatus::UnderReview->value,
            ReportStatus::Assigned->value,
            ReportStatus::InProgress->value,
        ];

        $solved = Report::query()
            ->where('status', ReportStatus::Solved)
            ->whereNotNull('submitted_at')
            ->whereNotNull('resolved_at')
            ->get(['submitted_at', 'resolved_at']);

        $avgClearance = $solved->isNotEmpty()
            ? round($solved->avg(fn (Report $report) => $report->submitted_at->diffInMinutes($report->resolved_at) / 60), 1).' hrs'
            : '—';

        return [
            Stat::make('Open cases', Report::query()->whereIn('status', $openStatuses)->count())
                ->description('New / review / assigned / in progress')
                ->color('warning'),
            Stat::make('High risk (≥80)', Report::query()->whereIn('status', $openStatuses)->where('risk_score', '>=', 80)->count())
                ->description('Prioritise these first')
                ->color('danger'),
            Stat::make('Solved', Report::query()->where('status', ReportStatus::Solved)->count())
                ->description('Cleared sites')
                ->color('success'),
            Stat::make('Avg clearance time', $avgClearance)
                ->description('Complaint → solved')
                ->color('info'),
        ];
    }
}
