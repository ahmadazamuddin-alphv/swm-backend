<?php

namespace App\Enums;

enum SocioeconomicGroup: string
{
    case B40 = 'B40';
    case M40 = 'M40';
    case T20 = 'T20';

    public function label(): string
    {
        return $this->value;
    }
}
