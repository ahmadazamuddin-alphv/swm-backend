<?php

namespace App\Models;

use App\Enums\CorridorType;
use App\Enums\DominantImpact;
use App\Enums\PotholeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PotholeCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'road_name',
        'area',
        'corridor_type',
        'status',
        'latitude',
        'longitude',
        'street_risk',
        'ai_risk_score',
        'traffic_impact',
        'safety_risk',
        'cost_to_fix',
        'rakyat_impact',
        'gdp_impact',
        'dominant_impact',
        'impact_reason',
        'spend_recommendation',
        'estimated_cost_rm',
        'budget_spent_rm',
        'severity',
        'notes',
        'photo_path',
        'contractor_id',
        'reported_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'corridor_type' => CorridorType::class,
            'status' => PotholeStatus::class,
            'dominant_impact' => DominantImpact::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'street_risk' => 'integer',
            'ai_risk_score' => 'integer',
            'traffic_impact' => 'integer',
            'safety_risk' => 'integer',
            'cost_to_fix' => 'integer',
            'rakyat_impact' => 'integer',
            'gdp_impact' => 'integer',
            'estimated_cost_rm' => 'integer',
            'budget_spent_rm' => 'integer',
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PotholeCase $case): void {
            if (blank($case->reference)) {
                $case->reference = 'PH-'.strtoupper(Str::random(8));
            }

            if (blank($case->reported_at)) {
                $case->reported_at = now();
            }

            if (blank($case->dominant_impact)) {
                $case->dominant_impact = $case->resolveDominantImpact();
            }
        });
    }

    public function scopeSpendable(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PotholeStatus::InProgress->value,
            PotholeStatus::Solved->value,
        ]);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            PotholeStatus::Solved->value,
            PotholeStatus::Deferred->value,
        ]);
    }

    public function resolveDominantImpact(): DominantImpact
    {
        $delta = $this->gdp_impact - $this->rakyat_impact;

        if ($delta >= 15) {
            return DominantImpact::Gdp;
        }

        if ($delta <= -15) {
            return DominantImpact::Rakyat;
        }

        return DominantImpact::Balanced;
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function locationLabel(): string
    {
        return "{$this->road_name}, {$this->area}";
    }

    public function photoUrl(): ?string
    {
        if (blank($this->photo_path)) {
            return null;
        }

        return asset('storage/'.$this->photo_path);
    }
}
