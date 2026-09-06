<?php

namespace App\Services;

use App\Enums\DominantImpact;
use App\Models\PotholeCase;
use Illuminate\Support\Collection;

class PotholeSpendPriorityService
{
    /**
     * Score ≈ weighted(street risk, rakyat impact, GDP impact) / cost
     *
     * @return Collection<int, array{
     *     case: PotholeCase,
     *     score: float,
     *     dominant_impact: DominantImpact,
     *     estimated_cost_rm: int,
     *     reason: string
     * }>
     */
    public function ranked(int $limit = 5): Collection
    {
        return PotholeCase::query()
            ->with('contractor')
            ->open()
            ->get()
            ->map(function (PotholeCase $case): array {
                $weighted = (
                    ($case->street_risk * 0.35)
                    + ($case->rakyat_impact * 0.325)
                    + ($case->gdp_impact * 0.325)
                );

                $costFactor = max(50_000, $case->estimated_cost_rm) / 100_000;
                $score = round($weighted / $costFactor, 2);
                $dominant = $case->dominant_impact ?? $case->resolveDominantImpact();

                return [
                    'case' => $case,
                    'score' => $score,
                    'dominant_impact' => $dominant,
                    'estimated_cost_rm' => $case->estimated_cost_rm,
                    'reason' => $this->reason($case, $dominant),
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    protected function reason(PotholeCase $case, DominantImpact $dominant): string
    {
        return match ($dominant) {
            DominantImpact::Gdp => "High GDP leverage on {$case->area} {$case->corridor_type->value} corridor — freight / port flow at risk.",
            DominantImpact::Rakyat => "High rakyat impact on dense {$case->area} corridor — motorcycle / residential satisfaction at stake.",
            DominantImpact::Balanced => "Arterial safety + dual impact on {$case->road_name}; spend unlocks both rakyat and GDP.",
        };
    }
}
