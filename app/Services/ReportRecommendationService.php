<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Models\DisposalCentre;
use App\Models\Driver;
use App\Models\Report;
use App\Models\ResponsibleParty;
use Illuminate\Support\Collection;

class ReportRecommendationService
{
    public function recommend(Report $report): array
    {
        $risk = (int) ($report->risk_score ?? 50);
        [$people, $lorries, $hours] = match (true) {
            $risk >= 80 => [8, 3, 6],
            $risk >= 60 => [5, 2, 12],
            $risk >= 40 => [3, 1, 24],
            default => [2, 1, 48],
        };

        return [
            'suggested_manpower' => $people + ($report->wasteCategory?->slug === 'construction-waste' ? 2 : 0),
            'suggested_lorries' => $lorries,
            // Repeating a recommendation must never silently extend a case's deadline.
            'suggested_deadline' => ($report->submitted_at ?? now())->copy()->addHours($hours),
            'suggested_disposal_centre_id' => $this->nearestDisposalCentreId(
                $report->latitude === null ? null : (float) $report->latitude,
                $report->longitude === null ? null : (float) $report->longitude,
                $report->waste_category_id,
            ),
        ];
    }

    public function rationale(Report $report): string
    {
        $hours = match (true) {
            $report->risk_score >= 80 => 6,
            $report->risk_score >= 60 => 12,
            $report->risk_score >= 40 => 24,
            default => 48,
        };

        return "{$report->priorityLabel()} ({$report->risk_score}/100): the demo rule allows {$hours} hours from submission."
            .($report->wasteCategory?->slug === 'construction-waste' ? ' Construction debris adds two people for handling.' : '')
            .' Officers can adjust the crew and deadline when assigning.';
    }

    public function applyToReport(Report $report): Report
    {
        $report->update($this->recommend($report));

        return $report;
    }

    public function nearestDisposalCentreId(?float $latitude, ?float $longitude, ?int $wasteCategoryId = null): ?int
    {
        return $this->disposalOptions($latitude, $longitude, $wasteCategoryId)->first()['centre']->id ?? null;
    }

    public function disposalOptions(?float $latitude, ?float $longitude, ?int $categoryId): Collection
    {
        if (! $this->validCoordinates($latitude, $longitude) || $categoryId === null) {
            return collect();
        }

        return DisposalCentre::query()->orderBy('id')->get()
            ->filter(fn (DisposalCentre $centre) => $this->accepts($centre->accepted_category_ids, $categoryId))
            ->filter(fn (DisposalCentre $centre) => $this->validCoordinates((float) $centre->latitude, (float) $centre->longitude))
            ->map(fn (DisposalCentre $centre) => [
                'centre' => $centre,
                'distance' => round($this->haversineKm($latitude, $longitude, (float) $centre->latitude, (float) $centre->longitude), 1),
            ])
            ->sortBy('distance')->values();
    }

    public function availableDrivers(Report $report, ?int $partyId = null): Collection
    {
        $party = $partyId ? ResponsibleParty::find($partyId) : null;

        return Driver::query()->with('contractor')
            ->where('is_available', true)
            ->when($party?->contractor_id, fn ($query, $contractorId) => $query->where('contractor_id', $contractorId))
            ->whereDoesntHave('assignments', fn ($query) => $query
                ->whereIn('status', [AssignmentStatus::Pending->value, AssignmentStatus::Active->value])
                ->where('report_id', '!=', $report->id))
            ->orderBy('id')->get()
            ->filter(fn (Driver $driver) => $this->accepts($driver->accepted_category_ids, $report->waste_category_id))
            ->map(function (Driver $driver) use ($report) {
                $distance = $this->validCoordinates($report->latitude, $report->longitude)
                    && $this->validCoordinates($driver->base_latitude, $driver->base_longitude)
                    ? $this->haversineKm((float) $report->latitude, (float) $report->longitude, $driver->base_latitude, $driver->base_longitude)
                    : null;

                return ['driver' => $driver, 'distance' => $distance === null ? null : round($distance, 1)];
            })
            ->sortBy(fn ($option) => $option['distance'] ?? PHP_FLOAT_MAX)->values();
    }

    public function accepts(?array $acceptedIds, ?int $categoryId): bool
    {
        // Empty means suitability is unknown, never an implicit licence to accept every waste type.
        return $categoryId !== null && in_array($categoryId, array_map('intval', $acceptedIds ?? []), true);
    }

    public function validCoordinates(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && is_finite((float) $latitude) && is_finite((float) $longitude)
            && abs((float) $latitude) <= 90 && abs((float) $longitude) <= 180;
    }

    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lon2 - $lon1) / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }
}
