<?php

namespace App\Services;

use App\Enums\AreaType;
use App\Enums\AssignmentStatus;
use App\Enums\PartyType;
use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use App\Models\Contractor;
use App\Models\DisposalCentre;
use App\Models\Driver;
use App\Models\Report;
use App\Models\ResponsibleParty;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperationsDemo
{
    public function seed(): void
    {
        DB::transaction(function () {
            $context = $this->referenceData();
            $states = [ReportStatus::New, ReportStatus::UnderReview, ReportStatus::Assigned, ReportStatus::InProgress,
                ReportStatus::Solved, ReportStatus::FalseReport, ReportStatus::New, ReportStatus::UnderReview];
            $risks = [94, 82, 73, 66, 43, 35, 57, 28];
            $descriptions = [
                'Mixed waste blocks the service lane beside an industrial boundary wall. Check access for the clearance vehicle.',
                'Discarded furniture and household bags have been reported beside a residential lane.',
                'Repeated waste pile behind the shops. The assigned crew is preparing to attend.',
                'Household waste beside a service road. The team is clearing the site.',
                'Waste removed and the lane swept. The attached image demonstrates the resolution record.',
                'Review found a duplicate of an existing case; no additional clearance is required.',
                'New household rubbish at the lane entrance. Location and extent require review.',
                'A small pile was reported near the rear access. Awaiting inspection.',
            ];

            foreach ($states as $i => $state) {
                $reference = sprintf('OPS-DEMO-%03d', $i + 1);
                if (Report::where('reference', $reference)->exists()) {
                    continue;
                }
                $zone = $context['zones'][$i % 3];
                $report = Report::create([
                    'reference' => $reference, 'source' => ReportSource::Citizen, 'status' => $state,
                    'latitude' => [3.0738, 2.9935, 3.1073][$i % 3],
                    'longitude' => [101.5183, 101.7885, 101.6067][$i % 3],
                    'photos' => ['/demo/reports/lane-before.png'],
                    'reporter_name' => 'Demo resident '.($i + 1),
                    'reporter_email' => 'resident'.($i + 1).'@example.com',
                    'reporter_phone' => 'Demo contact',
                    'waste_category_id' => $context['categories'][$i % 2 === 0 ? 'waste-piles' : 'furniture']->id,
                    'zone_id' => $zone->id, 'risk_score' => $risks[$i], 'notes' => $descriptions[$i],
                    'submitted_at' => now()->subHours([2, 18, 8, 10, 48, 30, 1, 5][$i]),
                    'resolved_at' => in_array($state, [ReportStatus::Solved, ReportStatus::FalseReport]) ? now()->subHours(6) : null,
                    'false_report_reason' => $state === ReportStatus::FalseReport ? 'Duplicate location and evidence for OPS-DEMO-001. Retained for the investigation record.' : null,
                ]);
                app(ReportRecommendationService::class)->applyToReport($report);

                if (in_array($state, [ReportStatus::Assigned, ReportStatus::InProgress, ReportStatus::Solved])) {
                    $driver = $context['drivers'][$i === 2 ? 0 : ($i === 3 ? 1 : 2)];
                    $party = $context['teams'][$driver->contractor_id];
                    $report->assignments()->create([
                        'responsible_party_id' => $party->id, 'driver_id' => $driver->id,
                        'manpower' => $report->suggested_manpower, 'lorries' => $report->suggested_lorries,
                        'deadline' => $report->suggested_deadline,
                        'status' => $state === ReportStatus::Solved ? AssignmentStatus::Completed : AssignmentStatus::Active,
                    ]);
                }
                if ($state === ReportStatus::Solved) {
                    $proof = $report->resolutionProofs()->create([
                        'path' => '/demo/reports/lane-cleared.png',
                        'notes' => 'Illustrative, AI-generated clearance photograph for the local demonstration.',
                        'uploaded_by' => User::query()->value('id'),
                    ]);
                    $proof->forceFill([
                        'created_at' => $report->resolved_at,
                        'updated_at' => $report->resolved_at,
                    ])->saveQuietly();
                }
                if ($state !== ReportStatus::New) {
                    $report->activities()->create([
                        'event' => $state->label(),
                        'description' => 'Seeded demonstration state. '.$descriptions[$i],
                        'created_at' => $report->resolved_at ?? now()->subMinutes(30),
                    ]);
                }
                $report->notifications()->whereHas('report', fn ($query) => $query->where('status', '!=', ReportStatus::New))
                    ->update(['read_at' => now()]);
            }
        });
    }

    public function incoming(): Report
    {
        return DB::transaction(function () {
            $context = $this->referenceData();
            $report = Report::create([
                'reference' => 'OPS-IN-'.strtoupper(Str::random(6)),
                'source' => ReportSource::Citizen, 'status' => ReportStatus::New,
                'latitude' => 3.0738, 'longitude' => 101.5183,
                'photos' => ['/demo/reports/lane-before.png'],
                'reporter_name' => 'Demo resident', 'reporter_email' => 'resident@example.com',
                'reporter_phone' => 'Demo contact',
                'waste_category_id' => $context['categories']['waste-piles']->id,
                'zone_id' => $context['zones'][0]->id, 'risk_score' => 87,
                'notes' => 'Simulated incoming report: waste bags and furniture obstruct the industrial service lane.',
                'submitted_at' => now(),
            ]);
            app(ReportRecommendationService::class)->applyToReport($report);

            return $report;
        });
    }

    private function referenceData(): array
    {
        $categories = collect([
            'construction-waste' => 'Construction waste',
            'furniture' => 'Furniture',
            'waste-piles' => 'Waste piles',
            'e-waste' => 'E-waste',
        ])->map(fn ($name, $slug) => WasteCategory::firstOrCreate(['slug' => $slug], ['name' => $name]));
        $ids = $categories->pluck('id')->all();
        $owner = ResponsibleParty::firstOrCreate(['name' => 'Demo district waste department'], [
            'type' => PartyType::Department, 'phone' => 'Demo contact', 'email' => 'district@example.com',
        ]);
        $zones = [];
        foreach ([
            ['Demo Shah Alam industrial', 'Seksyen 15', '40000', AreaType::Industrial],
            ['Demo Kajang residential', 'Taman Kajang Utama', '43000', AreaType::Residential],
            ['Demo Petaling Jaya', 'SS2', '46000', AreaType::Mixed],
        ] as [$name, $taman, $postcode, $type]) {
            $zones[] = Zone::firstOrCreate(['name' => $name], [
                'taman' => $taman, 'postcode' => $postcode, 'area_type' => $type, 'responsible_party_id' => $owner->id,
            ]);
        }
        $drivers = [];
        $teams = [];
        foreach ([
            ['Demo western clearance', 3.081, 101.521],
            ['Demo eastern clearance', 3.012, 101.755],
        ] as $index => [$name, $lat, $lng]) {
            $contractor = Contractor::firstOrCreate(['name' => $name], ['contact_person' => 'Demo coordinator', 'phone' => 'Demo contact', 'email' => 'operations'.($index + 1).'@example.com']);
            $teams[$contractor->id] = ResponsibleParty::firstOrCreate(['name' => $name.' team'], [
                'type' => PartyType::Contractor, 'contractor_id' => $contractor->id,
                'phone' => 'Demo contact', 'email' => $contractor->email,
            ]);
            for ($n = 1; $n <= 3; $n++) {
                $drivers[] = Driver::firstOrCreate(['vehicle_plate' => 'DEMO-'.($index + 1).$n], [
                    'contractor_id' => $contractor->id, 'name' => 'Demo driver '.($index * 3 + $n),
                    'phone' => 'Demo contact', 'base_latitude' => $lat + ($n - 1) * 0.01,
                    'base_longitude' => $lng, 'is_available' => true, 'accepted_category_ids' => $ids,
                ]);
            }
        }
        foreach ([
            ['Demo western disposal centre', 3.139, 101.447],
            ['Demo eastern disposal centre', 3.016, 101.789],
            ['Demo northern disposal centre', 3.267, 101.517],
        ] as [$name, $lat, $lng]) {
            DisposalCentre::firstOrCreate(['name' => $name], [
                'latitude' => $lat, 'longitude' => $lng,
                'address' => 'Fictional destination, Selangor demonstration',
                'accepted_category_ids' => $ids,
            ]);
        }

        return compact('categories', 'zones', 'drivers', 'teams');
    }
}
