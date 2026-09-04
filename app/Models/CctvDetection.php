<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CctvDetection extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_path',
        'waste_category_id',
        'activity_detected',
        'latitude',
        'longitude',
        'confidence',
        'raw_result',
        'report_id',
    ];

    protected function casts(): array
    {
        return [
            'activity_detected' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'confidence' => 'decimal:2',
            'raw_result' => 'array',
        ];
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
