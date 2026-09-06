<?php

namespace App\Services;

use App\Models\PotholeCase;

class PotholeBudgetService
{
    public const ANNUAL_BUDGET_RM = 18_000_000;

    /**
     * @return array{
     *     annual_budget_rm: int,
     *     spent_rm: int,
     *     remaining_rm: int,
     *     utilization_pct: float
     * }
     */
    public function meter(): array
    {
        $spent = (int) PotholeCase::query()->spendable()->sum('budget_spent_rm');
        $budget = self::ANNUAL_BUDGET_RM;
        $remaining = max(0, $budget - $spent);
        $utilization = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0.0;

        return [
            'annual_budget_rm' => $budget,
            'spent_rm' => $spent,
            'remaining_rm' => $remaining,
            'utilization_pct' => $utilization,
        ];
    }

    public static function formatRm(int $amount): string
    {
        if ($amount >= 1_000_000) {
            return 'RM '.number_format($amount / 1_000_000, 1).'M';
        }

        if ($amount >= 1_000) {
            return 'RM '.number_format($amount / 1_000, 0).'K';
        }

        return 'RM '.number_format($amount);
    }
}
