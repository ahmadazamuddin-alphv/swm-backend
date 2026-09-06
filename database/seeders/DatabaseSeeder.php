<?php

namespace Database\Seeders;

use App\Enums\AreaType;
use App\Enums\AssignmentStatus;
use App\Enums\PartyType;
use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use App\Enums\SocioeconomicGroup;
use App\Models\Assignment;
use App\Models\CctvDetection;
use App\Models\Contractor;
use App\Models\DisposalCentre;
use App\Models\Driver;
use App\Models\Report;
use App\Models\ResponsibleParty;
use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Zone;
use App\Services\ReportRecommendationService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@selangor.gov.my'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ],
        );

        $categories = collect([
            ['name' => 'Construction waste', 'slug' => 'construction-waste', 'description' => 'Debris, concrete, timber'],
            ['name' => 'Furniture', 'slug' => 'furniture', 'description' => 'Discarded sofas, mattresses, cabinets'],
            ['name' => 'Waste piles', 'slug' => 'waste-piles', 'description' => 'Mixed household waste piles'],
            ['name' => 'E-waste', 'slug' => 'e-waste', 'description' => 'Electronics and appliances'],
        ])->map(fn (array $data) => WasteCategory::firstOrCreate(['slug' => $data['slug']], $data));

        $contractor = Contractor::firstOrCreate(
            ['name' => 'Alam Flora Selangor'],
            [
                'contact_person' => 'Encik Rahman',
                'phone' => '03-1234-5678',
                'email' => 'ops@alamflora.example',
            ],
        );

        $driverA = Driver::firstOrCreate(
            ['vehicle_plate' => 'BQA1234'],
            [
                'contractor_id' => $contractor->id,
                'name' => 'Abang Lori Ahmad',
                'phone' => '012-3456789',
            ],
        );

        $driverB = Driver::firstOrCreate(
            ['vehicle_plate' => 'BQB5678'],
            [
                'contractor_id' => $contractor->id,
                'name' => 'Abang Lori Faizal',
                'phone' => '012-9876543',
            ],
        );

        $dept = ResponsibleParty::firstOrCreate(
            ['name' => 'Jabatan Pengurusan Sisa'],
            [
                'type' => PartyType::Department,
                'phone' => '03-5544-1000',
                'email' => 'sisa@selangor.gov.my',
            ],
        );

        $partyContractor = ResponsibleParty::firstOrCreate(
            ['name' => 'Alam Flora Ops Team'],
            [
                'type' => PartyType::Contractor,
                'phone' => '03-1234-5678',
                'email' => 'ops@alamflora.example',
                'contractor_id' => $contractor->id,
            ],
        );

        $zoneShahAlam = Zone::firstOrCreate(
            ['name' => 'Shah Alam Industrial'],
            [
                'postcode' => '40000',
                'taman' => 'Seksyen 15',
                'area_type' => AreaType::Industrial,
                'socioeconomic_group' => SocioeconomicGroup::M40,
                'responsible_party_id' => $partyContractor->id,
            ],
        );

        $zoneKajang = Zone::firstOrCreate(
            ['name' => 'Kajang Residential'],
            [
                'postcode' => '43000',
                'taman' => 'Taman Kajang Utama',
                'area_type' => AreaType::Residential,
                'socioeconomic_group' => SocioeconomicGroup::B40,
                'responsible_party_id' => $dept->id,
            ],
        );

        $zonePetaling = Zone::firstOrCreate(
            ['name' => 'Petaling Jaya Mixed'],
            [
                'postcode' => '46000',
                'taman' => 'SS2',
                'area_type' => AreaType::Mixed,
                'socioeconomic_group' => SocioeconomicGroup::T20,
                'responsible_party_id' => $dept->id,
            ],
        );

        $centreA = DisposalCentre::firstOrCreate(
            ['name' => 'Kundang Landfill'],
            [
                'latitude' => 3.2667000,
                'longitude' => 101.5167000,
                'address' => 'Kundang, Selangor',
                'accepted_category_ids' => $categories->pluck('id')->all(),
            ],
        );

        DisposalCentre::firstOrCreate(
            ['name' => 'Jeram Sanitary Landfill'],
            [
                'latitude' => 3.2333000,
                'longitude' => 101.3167000,
                'address' => 'Jeram, Kuala Selangor',
                'accepted_category_ids' => $categories->pluck('id')->all(),
            ],
        );

        $recommender = app(ReportRecommendationService::class);

        $samples = [
            [
                'reference' => 'RPT-DEMO0001',
                'source' => ReportSource::Citizen,
                'status' => ReportStatus::New,
                'latitude' => 3.0738000,
                'longitude' => 101.5183000,
                'reporter_name' => 'Siti Aminah',
                'reporter_phone' => '011-2222333',
                'waste_category_id' => $categories[0]->id,
                'zone_id' => $zoneShahAlam->id,
                'risk_score' => 88,
                'notes' => 'Large construction debris near factory gate',
                'submitted_at' => now()->subHours(3),
            ],
            [
                'reference' => 'RPT-DEMO0002',
                'source' => ReportSource::Citizen,
                'status' => ReportStatus::UnderReview,
                'latitude' => 2.9935000,
                'longitude' => 101.7885000,
                'reporter_name' => 'Lim Wei',
                'reporter_phone' => '012-1111222',
                'waste_category_id' => $categories[1]->id,
                'zone_id' => $zoneKajang->id,
                'risk_score' => 65,
                'notes' => 'Mattresses dumped beside taman playground',
                'submitted_at' => now()->subDay(),
            ],
            [
                'reference' => 'RPT-DEMO0003',
                'source' => ReportSource::Citizen,
                'status' => ReportStatus::Assigned,
                'latitude' => 3.1073000,
                'longitude' => 101.6067000,
                'reporter_name' => 'Priya Nair',
                'reporter_phone' => '013-4444555',
                'waste_category_id' => $categories[2]->id,
                'zone_id' => $zonePetaling->id,
                'risk_score' => 72,
                'notes' => 'Recurring piles along backlane',
                'submitted_at' => now()->subDays(2),
            ],
            [
                'reference' => 'RPT-DEMO0004',
                'source' => ReportSource::Citizen,
                'status' => ReportStatus::Solved,
                'latitude' => 3.0801000,
                'longitude' => 101.5201000,
                'reporter_name' => 'Azlan',
                'reporter_phone' => '019-6666777',
                'waste_category_id' => $categories[2]->id,
                'zone_id' => $zoneShahAlam->id,
                'risk_score' => 40,
                'notes' => 'Cleared small pile',
                'submitted_at' => now()->subDays(5),
                'resolved_at' => now()->subDays(4),
            ],
            [
                'reference' => 'RPT-DEMO0005',
                'source' => ReportSource::Cctv,
                'status' => ReportStatus::New,
                'latitude' => 3.0755000,
                'longitude' => 101.5210000,
                'waste_category_id' => $categories[0]->id,
                'zone_id' => $zoneShahAlam->id,
                'risk_score' => 91,
                'notes' => 'Detected via CCTV POC upload',
                'submitted_at' => now()->subHour(),
            ],
        ];

        foreach ($samples as $sample) {
            $report = Report::firstOrCreate(
                ['reference' => $sample['reference']],
                $sample,
            );

            if ($report->wasRecentlyCreated) {
                $recommender->applyToReport($report);
            }
        }

        $assigned = Report::where('reference', 'RPT-DEMO0003')->first();
        if ($assigned && ! $assigned->assignments()->exists()) {
            Assignment::create([
                'report_id' => $assigned->id,
                'responsible_party_id' => $dept->id,
                'driver_id' => $driverA->id,
                'deadline' => now()->addHours(12),
                'manpower' => 5,
                'lorries' => 2,
                'status' => AssignmentStatus::Active,
            ]);
        }

        CctvDetection::firstOrCreate(
            ['video_path' => 'cctv/demo-detection.mp4'],
            [
                'waste_category_id' => $categories[0]->id,
                'activity_detected' => true,
                'latitude' => 3.0755000,
                'longitude' => 101.5210000,
                'confidence' => 87.50,
                'raw_result' => [
                    'labels' => ['illegal_dumping', 'construction_waste'],
                    'frames' => 120,
                ],
                'report_id' => Report::where('reference', 'RPT-DEMO0005')->value('id'),
            ],
        );

        // Silence unused vars for static analysis friendliness
        unset($driverB, $centreA);
        $this->call(OperationsDemoSeeder::class);
        $this->call(PotholeCaseSeeder::class);
    }
}
