<?php

namespace App\Enums;

enum ReportStatus: string
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
}
