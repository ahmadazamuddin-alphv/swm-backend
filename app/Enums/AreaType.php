<?php

namespace App\Enums;

enum AreaType: string
{
    case Residential = 'residential';
    case Industrial = 'industrial';
    case Commercial = 'commercial';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential',
            self::Industrial => 'Industrial',
            self::Commercial => 'Commercial',
            self::Mixed => 'Mixed',
        };
    }
}
