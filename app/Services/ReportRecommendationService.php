<?php

namespace App\Services;

use App\Models\DisposalCentre;
use App\Models\Report;
use Carbon\Carbon;

class ReportRecommendationService
{
    /**
     * @return array{
     *     suggested_manpower: int,
     *     suggested_lorries: int,
     *     suggested_deadline: Carbon,
     *     suggested_disposal_centre_id: int|null
     * }
     */
    public function recommend(Report $report): array
    {
        $risk = (int) ($report->risk_score ?? 50);

        $manpower = match (true) {
            $risk >= 80 => 8,
            $risk >= 60 => 5,
            $risk >= 40 => 3,
            default => 2,
        };

        $lorries = match (true) {
            $risk >= 80 => 3,
            $risk >= 60 => 2,
            default => 1,
        };

        $hoursUntilDeadline = match (true) {
            $risk >= 80 => 6,
            $risk >= 60 => 12,
            $risk >= 40 => 24,
            default => 48,
        };

        return [
            'suggested_manpower' => $manpower,
            'suggested_lorries' => $lorries,
            'suggested_deadline' => now()->addHours($hoursUntilDeadline),
            'suggested_disposal_centre_id' => $this->nearestDisposalCentreId(
                $report->latitude !== null ? (float) $report->latitude : null,
                $report->longitude !== null ? (float) $report->longitude : null,
                $report->waste_category_id,
            ),
        ];
    }

    public function applyToReport(Report $report): Report
    {
        $report->fill($this->recommend($report));
        $report->save();

        return $report;
    }

    public function nearestDisposalCentreId(?float $latitude, ?float $longitude, ?int $wasteCategoryId = null): ?int
    {
        $centres = DisposalCentre::query()->get();

        if ($centres->isEmpty()) {
            return null;
        }

        if ($latitude === null || $longitude === null) {
            return $centres->first()?->id;
        }

        $bestId = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($centres as $centre) {
            if ($wasteCategoryId && is_array($centre->accepted_category_ids) && $centre->accepted_category_ids !== []) {
                if (! in_array($wasteCategoryId, $centre->accepted_category_ids, true)) {
                    continue;
                }
            }

            $distance = $this->haversineKm(
                $latitude,
                $longitude,
                (float) $centre->latitude,
                (float) $centre->longitude,
            );

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestId = $centre->id;
            }
        }

        return $bestId ?? $centres->first()?->id;
    }

    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }
}
