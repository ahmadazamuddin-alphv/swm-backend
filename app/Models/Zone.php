<?php

namespace App\Models;

use App\Enums\AreaType;
use App\Enums\SocioeconomicGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'postcode',
        'taman',
        'area_type',
        'socioeconomic_group',
        'responsible_party_id',
    ];

    protected function casts(): array
    {
        return [
            'area_type' => AreaType::class,
            'socioeconomic_group' => SocioeconomicGroup::class,
        ];
    }

    public function responsibleParty(): BelongsTo
    {
        return $this->belongsTo(ResponsibleParty::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
