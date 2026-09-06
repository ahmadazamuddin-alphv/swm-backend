<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_id',
        'name',
        'phone',
        'vehicle_plate',
        'base_latitude',
        'base_longitude',
        'is_available',
        'accepted_category_ids',
    ];

    protected function casts(): array
    {
        return [
            'base_latitude' => 'float',
            'base_longitude' => 'float',
            'is_available' => 'boolean',
            'accepted_category_ids' => 'array',
        ];
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
