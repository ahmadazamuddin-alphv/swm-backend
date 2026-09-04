<?php

namespace App\Enums;

enum ReportSource: string
{
    case Citizen = 'citizen';
    case Cctv = 'cctv';

    public function label(): string
    {
        return match ($this) {
            self::Citizen => 'Citizen',
            self::Cctv => 'CCTV',
        };
    }
}
