<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisposalCentre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'address',
        'accepted_category_ids',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accepted_category_ids' => 'array',
        ];
    }

    public function suggestedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'suggested_disposal_centre_id');
    }
}
