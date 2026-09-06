<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\ReportStatus;
use App\Models\Driver;
use App\Models\Report;
use App\Models\ResponsibleParty;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReportWorkflow
{
    public function __construct(private ReportRecommendationService $recommendations) {}

    public function review(Report $report, User $actor): void
    {
        $this->change($report, $actor, function (Report $case) {
            $this->requireState($case, [ReportStatus::New]);
            $case->update(['status' => ReportStatus::UnderReview]);

            return ['Review started', 'The evidence is being investigated.'];
        });
    }

    public function recommend(Report $report, User $actor): void
    {
        $this->change($report, $actor, function (Report $case) {
            $this->recommendations->applyToReport($case);

            return ['Recommendations updated', $this->recommendations->rationale($case)];
        });
    }

    public function assign(Report $report, User $actor, array $data): void
    {
        Validator::make($data, [
            'responsible_party_id' => ['required', 'integer', 'exists:responsible_parties,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'manpower' => ['required', 'integer', 'min:1', 'max:100'],
            'lorries' => ['required', 'integer', 'min:1', 'max:20'],
            'deadline' => ['required', 'date', 'after:now'],
        ])->validate();

        $this->change($report, $actor, function (Report $case) use ($data) {
            $party = ResponsibleParty::query()->whereKey($data['responsible_party_id'])->lockForUpdate()->firstOrFail();
            if ($party->contractor_id === null) {
                throw ValidationException::withMessages(['responsible_party_id' => 'Choose a contractor-backed clearance team for this assignment.']);
            }

            Driver::query()->whereKey($data['driver_id'])->lockForUpdate()->firstOrFail();
            $available = $this->recommendations->availableDrivers($case, (int) $data['responsible_party_id']);
            if (! $available->contains(fn ($option) => $option['driver']->id === (int) $data['driver_id'])) {
                throw ValidationException::withMessages(['driver_id' => 'This driver is unavailable or unsuitable for this waste or team. Choose another driver.']);
            }

            $case->assignments()->whereIn('status', ['pending', 'active'])->update(['status' => AssignmentStatus::Cancelled]);
            $assignment = $case->assignments()->create([
                ...array_intersect_key($data, array_flip(['responsible_party_id', 'driver_id', 'manpower', 'lorries', 'deadline'])),
                'status' => AssignmentStatus::Active,
            ]);
            $case->update(['status' => ReportStatus::Assigned]);

            return ['Team assigned', "{$assignment->responsibleParty->name}; {$assignment->driver->name} leads {$assignment->manpower} people and {$assignment->lorries} lorries. Deadline: {$assignment->deadline->format('d M Y, H:i')}."];
        });
    }

    public function start(Report $report, User $actor): void
    {
        $this->change($report, $actor, function (Report $case) {
            $this->requireState($case, [ReportStatus::Assigned]);
            if (! $case->assignments()->where('status', AssignmentStatus::Active)->exists()) {
                throw ValidationException::withMessages(['status' => 'Assign a team before starting clearance.']);
            }
            $case->update(['status' => ReportStatus::InProgress]);

            return ['Clearance started', 'The assigned team has started work at the site.'];
        });
    }

    public function markFalse(Report $report, User $actor, string $reason): void
    {
        $reason = trim($reason);
        Validator::make(['reason' => $reason], ['reason' => ['required', 'string', 'min:5', 'max:2000']])->validate();

        $this->change($report, $actor, function (Report $case) use ($reason) {
            $case->update(['status' => ReportStatus::FalseReport, 'false_report_reason' => $reason, 'resolved_at' => now()]);
            $case->assignments()->whereIn('status', ['pending', 'active'])->update(['status' => AssignmentStatus::Cancelled]);

            return ['Marked false', $reason];
        });
    }

    public function solve(Report $report, User $actor, string $path, ?string $notes): void
    {
        if (! str_starts_with($path, "reports/{$report->id}/proofs/") || str_contains($path, '..') || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages(['path' => 'Upload a clearance photo for this case before marking it solved.']);
        }
        if (! in_array(Storage::disk('public')->mimeType($path), ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages(['path' => 'Use a JPEG, PNG or WebP clearance photo.']);
        }

        $this->change($report, $actor, function (Report $case) use ($actor, $path, $notes) {
            $case->resolutionProofs()->create(['path' => $path, 'notes' => $notes, 'uploaded_by' => $actor->id]);
            $case->update(['status' => ReportStatus::Solved, 'resolved_at' => now()]);
            $case->assignments()->whereIn('status', ['pending', 'active'])->update(['status' => AssignmentStatus::Completed]);

            return ['Case solved', filled($notes) ? $notes : 'Clearance photograph recorded.'];
        });
    }

    private function change(Report $report, User $actor, callable $change): void
    {
        DB::transaction(function () use ($report, $actor, $change) {
            $case = Report::query()->lockForUpdate()->findOrFail($report->id);
            if (! $case->isOpen()) {
                throw ValidationException::withMessages(['status' => 'This case is already closed. Refresh to see its latest state.']);
            }
            [$event, $description] = $change($case);
            $case->activities()->create(['user_id' => $actor->id, 'event' => $event, 'description' => $description]);
        });
        $report->refresh();
    }

    private function requireState(Report $report, array $states): void
    {
        if (! in_array($report->status, $states, true)) {
            throw ValidationException::withMessages(['status' => 'The case has moved to another stage. Refresh before continuing.']);
        }
    }
}
