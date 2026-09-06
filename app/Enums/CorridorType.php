<?php

namespace App\Enums;

enum CorridorType: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Industrial = 'industrial';
    case Arterial = 'arterial';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential',
            self::Commercial => 'Commercial',
            self::Industrial => 'Industrial',
            self::Arterial => 'Arterial',
        };
    }
}
