<?php

namespace App\Enums;

enum PotholeStatus: string
{
    case New = 'new';
    case UnderReview = 'under_review';
    case InProgress = 'in_progress';
    case Solved = 'solved';
    case Deferred = 'deferred';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::UnderReview => 'Under review',
            self::InProgress => 'In progress',
            self::Solved => 'Solved',
            self::Deferred => 'Deferred',
        };
    }
}
