<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReportStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case UnderReview = 'under_review';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Solved = 'solved';
    case FalseReport = 'false_report';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::UnderReview => 'Under review',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::Solved => 'Solved',
            self::FalseReport => 'False report',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::UnderReview => 'warning',
            self::Assigned, self::InProgress => 'info',
            self::Solved => 'success',
            self::FalseReport => 'gray',
        };
    }
}
