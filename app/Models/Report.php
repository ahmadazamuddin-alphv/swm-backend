<?php

namespace App\Models;

use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'source',
        'status',
        'latitude',
        'longitude',
        'photos',
        'reporter_name',
        'reporter_phone',
        'reporter_email',
        'waste_category_id',
        'zone_id',
        'risk_score',
        'false_report_reason',
        'suggested_manpower',
        'suggested_lorries',
        'suggested_deadline',
        'suggested_disposal_centre_id',
        'notes',
        'submitted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => ReportSource::class,
            'status' => ReportStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'photos' => 'array',
            'risk_score' => 'integer',
            'suggested_manpower' => 'integer',
            'suggested_lorries' => 'integer',
            'suggested_deadline' => 'datetime',
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Report $report): void {
            if (blank($report->reference)) {
                $report->reference = 'RPT-'.strtoupper(Str::random(8));
            }

            if (blank($report->submitted_at)) {
                $report->submitted_at = now();
            }
        });

        static::created(function (Report $report): void {
            $users = User::query()->get();

            foreach ($users as $user) {
                AdminNotification::create([
                    'user_id' => $user->id,
                    'report_id' => $report->id,
                    'title' => 'New waste report',
                    'body' => "Report {$report->reference} was submitted and needs review.",
                ]);
            }
        });
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function suggestedDisposalCentre(): BelongsTo
    {
        return $this->belongsTo(DisposalCentre::class, 'suggested_disposal_centre_id');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class)->latestOfMany();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function resolutionProofs(): HasMany
    {
        return $this->hasMany(ResolutionProof::class);
    }

    public function cctvDetection(): HasOne
    {
        return $this->hasOne(CctvDetection::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ReportActivity::class)->latest('id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [ReportStatus::Solved->value, ReportStatus::FalseReport->value]);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [ReportStatus::Solved, ReportStatus::FalseReport], true);
    }

    public function priorityLabel(): string
    {
        return match (true) {
            $this->risk_score >= 80 => 'High risk',
            $this->risk_score >= 60 => 'Elevated',
            default => 'Standard',
        };
    }

    public function clearanceHours(): ?float
    {
        if (! $this->submitted_at || ! $this->resolved_at) {
            return null;
        }

        return round($this->submitted_at->diffInMinutes($this->resolved_at) / 60, 1);
    }
}
