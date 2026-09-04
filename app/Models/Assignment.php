<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'responsible_party_id',
        'driver_id',
        'deadline',
        'manpower',
        'lorries',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'status' => AssignmentStatus::class,
            'manpower' => 'integer',
            'lorries' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function responsibleParty(): BelongsTo
    {
        return $this->belongsTo(ResponsibleParty::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
