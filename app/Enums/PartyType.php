<?php

namespace App\Enums;

enum PartyType: string
{
    case Department = 'department';
    case Contractor = 'contractor';

    public function label(): string
    {
        return match ($this) {
            self::Department => 'Department',
            self::Contractor => 'Contractor',
        };
    }
}
