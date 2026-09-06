<?php

namespace App\Enums;

enum DominantImpact: string
{
    case Rakyat = 'rakyat';
    case Gdp = 'gdp';
    case Balanced = 'balanced';

    public function label(): string
    {
        return match ($this) {
            self::Rakyat => 'Rakyat',
            self::Gdp => 'GDP',
            self::Balanced => 'Balanced',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Rakyat => 'Rakyat-led: prioritise public satisfaction and safety for dense residential / motorcycle corridors (e.g. PJ, Subang).',
            self::Gdp => 'GDP-led: prioritise state economic flow — ports, HGV and industrial feeders (e.g. Klang / Port Klang).',
            self::Balanced => 'Balanced: arterial junctions where safety, commute and logistics all matter — spend unlocks both rakyat and GDP.',
        };
    }
}
