<?php

namespace App\Services;

class PotholeForecastService
{
    /**
     * @return array<string, array{label: string, rainfall: int, uv: int, temperature: int, vehicle_volume: int}>
     */
    public function presets(): array
    {
        return [
            'monsoon_hgv' => [
                'label' => 'Monsoon + high HGV',
                'rainfall' => 88,
                'uv' => 35,
                'temperature' => 28,
                'vehicle_volume' => 92,
            ],
            'dry_heat' => [
                'label' => 'Dry heat + UV',
                'rainfall' => 18,
                'uv' => 90,
                'temperature' => 36,
                'vehicle_volume' => 55,
            ],
            'normal' => [
                'label' => 'Normal week',
                'rainfall' => 40,
                'uv' => 55,
                'temperature' => 31,
                'vehicle_volume' => 50,
            ],
        ];
    }

    /**
     * Hardcoded response curves for stable demos.
     *
     * @param  array{rainfall?: int, uv?: int, temperature?: int, vehicle_volume?: int}  $inputs
     * @return array{
     *     predicted_new_count: int,
     *     formation_index: int,
     *     severity_mix: array{low: int, medium: int, high: int},
     *     corridors: list<array{name: string, pressure: int, note: string}>
     * }
     */
    public function simulate(array $inputs): array
    {
        $rain = (int) ($inputs['rainfall'] ?? 40);
        $uv = (int) ($inputs['uv'] ?? 55);
        $temp = (int) ($inputs['temperature'] ?? 31);
        $volume = (int) ($inputs['vehicle_volume'] ?? 50);

        $formationIndex = (int) min(100, round(
            ($rain * 0.38)
            + ($volume * 0.34)
            + ($uv * 0.14)
            + (max(0, $temp - 28) * 2.2)
        ));

        $predicted = (int) max(3, round(4 + ($formationIndex / 100) * 28));

        $highShare = min(0.55, ($rain * 0.0045) + ($volume * 0.004));
        $mediumShare = min(0.45, 0.35 + ($uv * 0.002));
        $lowShare = max(0.1, 1 - $highShare - $mediumShare);

        $corridors = [
            [
                'name' => 'Port Klang / industrial feeders',
                'pressure' => (int) min(100, round(($rain * 0.45) + ($volume * 0.55))),
                'note' => 'HGV + monsoon water ingress; GDP-critical.',
            ],
            [
                'name' => 'Shah Alam industrial ring',
                'pressure' => (int) min(100, round(($volume * 0.5) + ($rain * 0.35) + 8)),
                'note' => 'Heavy axle load; warehouse access routes.',
            ],
            [
                'name' => 'PJ / Subang motorcycle lanes',
                'pressure' => (int) min(100, round(($uv * 0.35) + ($temp * 1.1) + ($rain * 0.2))),
                'note' => 'Surface cracking + rakyat satisfaction risk.',
            ],
            [
                'name' => 'Federal arterial junctions',
                'pressure' => (int) min(100, round(($volume * 0.4) + ($rain * 0.3) + ($uv * 0.2))),
                'note' => 'Safety-led dual impact corridors.',
            ],
        ];

        usort($corridors, fn (array $a, array $b): int => $b['pressure'] <=> $a['pressure']);

        // Demo narrative: monsoon + high HGV spikes Port Klang first
        if ($rain >= 70 && $volume >= 75) {
            $corridors[0]['pressure'] = max($corridors[0]['pressure'], 94);
            $corridors[0]['note'] = 'Spike: monsoon + high HGV — Port Klang / industrial feeders worsen first.';
            $predicted = max($predicted, 24);
            $formationIndex = max($formationIndex, 86);
        }

        return [
            'predicted_new_count' => $predicted,
            'formation_index' => $formationIndex,
            'severity_mix' => [
                'low' => (int) round($predicted * $lowShare),
                'medium' => (int) round($predicted * $mediumShare),
                'high' => max(0, $predicted - (int) round($predicted * $lowShare) - (int) round($predicted * $mediumShare)),
            ],
            'corridors' => $corridors,
        ];
    }
}
