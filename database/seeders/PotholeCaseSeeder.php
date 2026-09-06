<?php

namespace Database\Seeders;

use App\Enums\CorridorType;
use App\Enums\DominantImpact;
use App\Enums\PotholeStatus;
use App\Models\Contractor;
use App\Models\PotholeCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PotholeCaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDemoPhotos();

        $alamFlora = Contractor::firstOrCreate(
            ['name' => 'Alam Flora Selangor'],
            [
                'contact_person' => 'Encik Rahman',
                'phone' => '03-1234-5678',
                'email' => 'ops@alamflora.example',
            ],
        );

        $jalanKuasa = Contractor::firstOrCreate(
            ['name' => 'Jalan Kuasa Sdn Bhd'],
            [
                'contact_person' => 'Pn. Aina',
                'phone' => '03-3344-7788',
                'email' => 'dispatch@jalankuasa.example',
            ],
        );

        $selRoad = Contractor::firstOrCreate(
            ['name' => 'Selangor RoadCare'],
            [
                'contact_person' => 'Encik Hafiz',
                'phone' => '03-5566-2211',
                'email' => 'crew@selroadcare.example',
            ],
        );

        $cases = [
            [
                'reference' => 'PH-KLANG001',
                'road_name' => 'Persiaran Pelabuhan Utara',
                'area' => 'Port Klang',
                'corridor_type' => CorridorType::Industrial,
                'status' => PotholeStatus::InProgress,
                'latitude' => 3.0018000,
                'longitude' => 101.3915000,
                'street_risk' => 92,
                'ai_risk_score' => 89,
                'traffic_impact' => 95,
                'safety_risk' => 78,
                'cost_to_fix' => 72,
                'rakyat_impact' => 42,
                'gdp_impact' => 94,
                'dominant_impact' => DominantImpact::Gdp,
                'impact_reason' => 'HGV feeders to port yards; delay cascades into container turnaround.',
                'spend_recommendation' => 'Spend here first because Port Klang freight GDP loss outweighs unit repair cost.',
                'estimated_cost_rm' => 480_000,
                'budget_spent_rm' => 310_000,
                'severity' => 'high',
                'notes' => 'Industrial / HGV corridor — demo high-GDP case.',
                'reported_at' => now()->subDays(12),
            ],
            [
                'reference' => 'PH-PJ0001',
                'road_name' => 'Jalan SS2/24 motorcycle lane',
                'area' => 'Petaling Jaya',
                'corridor_type' => CorridorType::Residential,
                'status' => PotholeStatus::New,
                'latitude' => 3.0742000,
                'longitude' => 101.6168000,
                'street_risk' => 81,
                'ai_risk_score' => 84,
                'traffic_impact' => 70,
                'safety_risk' => 88,
                'cost_to_fix' => 45,
                'rakyat_impact' => 93,
                'gdp_impact' => 38,
                'dominant_impact' => DominantImpact::Rakyat,
                'impact_reason' => 'Dense residential + motorcycle lane; riders hit the same edge daily.',
                'spend_recommendation' => 'Spend here first because rakyat satisfaction recovers fast at modest cost.',
                'estimated_cost_rm' => 95_000,
                'budget_spent_rm' => 0,
                'severity' => 'high',
                'notes' => 'PJ motorcycle-lane — demo high-rakyat case.',
                'reported_at' => now()->subDays(3),
            ],
            [
                'reference' => 'PH-SUB0001',
                'road_name' => 'Persiaran Kewajipan',
                'area' => 'Subang Jaya',
                'corridor_type' => CorridorType::Residential,
                'status' => PotholeStatus::UnderReview,
                'latitude' => 3.0489000,
                'longitude' => 101.5856000,
                'street_risk' => 74,
                'ai_risk_score' => 76,
                'traffic_impact' => 68,
                'safety_risk' => 80,
                'cost_to_fix' => 48,
                'rakyat_impact' => 86,
                'gdp_impact' => 40,
                'dominant_impact' => DominantImpact::Rakyat,
                'impact_reason' => 'School-run and motorcycle density; complaints cluster after rain.',
                'spend_recommendation' => 'Prioritise after PJ lane — similar rakyat profile, slightly lower risk.',
                'estimated_cost_rm' => 120_000,
                'budget_spent_rm' => 0,
                'severity' => 'medium',
                'reported_at' => now()->subDays(5),
            ],
            [
                'reference' => 'PH-ART0001',
                'road_name' => 'Federal Highway KM 12 junction',
                'area' => 'Shah Alam',
                'corridor_type' => CorridorType::Arterial,
                'status' => PotholeStatus::InProgress,
                'latitude' => 3.0725000,
                'longitude' => 101.5189000,
                'street_risk' => 88,
                'ai_risk_score' => 87,
                'traffic_impact' => 90,
                'safety_risk' => 91,
                'cost_to_fix' => 78,
                'rakyat_impact' => 72,
                'gdp_impact' => 75,
                'dominant_impact' => DominantImpact::Balanced,
                'impact_reason' => 'Arterial junction — safety-led with both commute and logistics exposure.',
                'spend_recommendation' => 'Spend here for balanced safety + throughput; junction failure is costly.',
                'estimated_cost_rm' => 650_000,
                'budget_spent_rm' => 420_000,
                'severity' => 'high',
                'reported_at' => now()->subDays(18),
            ],
            [
                'reference' => 'PH-KLANG002',
                'road_name' => 'Jalan Kem',
                'area' => 'Klang',
                'corridor_type' => CorridorType::Industrial,
                'status' => PotholeStatus::Solved,
                'latitude' => 3.0445000,
                'longitude' => 101.4492000,
                'street_risk' => 70,
                'ai_risk_score' => 68,
                'traffic_impact' => 82,
                'safety_risk' => 60,
                'cost_to_fix' => 55,
                'rakyat_impact' => 35,
                'gdp_impact' => 80,
                'dominant_impact' => DominantImpact::Gdp,
                'impact_reason' => 'Warehouse access spur; completed patch restored HGV turn-in.',
                'spend_recommendation' => 'Already spent — keep as GDP success reference.',
                'estimated_cost_rm' => 210_000,
                'budget_spent_rm' => 198_000,
                'severity' => 'medium',
                'reported_at' => now()->subDays(40),
                'resolved_at' => now()->subDays(10),
            ],
            [
                'reference' => 'PH-SA0001',
                'road_name' => 'Persiaran Kayangan',
                'area' => 'Shah Alam',
                'corridor_type' => CorridorType::Commercial,
                'status' => PotholeStatus::Solved,
                'latitude' => 3.0731000,
                'longitude' => 101.5180000,
                'street_risk' => 62,
                'ai_risk_score' => 60,
                'traffic_impact' => 58,
                'safety_risk' => 55,
                'cost_to_fix' => 40,
                'rakyat_impact' => 64,
                'gdp_impact' => 52,
                'dominant_impact' => DominantImpact::Balanced,
                'impact_reason' => 'Commercial strip footfall; solved mid-year.',
                'spend_recommendation' => 'Closed case — budget already utilised.',
                'estimated_cost_rm' => 85_000,
                'budget_spent_rm' => 82_000,
                'severity' => 'low',
                'reported_at' => now()->subDays(60),
                'resolved_at' => now()->subDays(25),
            ],
            [
                'reference' => 'PH-KLANG003',
                'road_name' => 'Jalan Pelabuhan Utara 3',
                'area' => 'Port Klang',
                'corridor_type' => CorridorType::Industrial,
                'status' => PotholeStatus::Solved,
                'latitude' => 2.9991000,
                'longitude' => 101.3928000,
                'street_risk' => 85,
                'ai_risk_score' => 83,
                'traffic_impact' => 88,
                'safety_risk' => 70,
                'cost_to_fix' => 80,
                'rakyat_impact' => 30,
                'gdp_impact' => 90,
                'dominant_impact' => DominantImpact::Gdp,
                'impact_reason' => 'Container trailer queue; major GDP spend already booked.',
                'spend_recommendation' => 'Large GDP repair already completed this FY.',
                'estimated_cost_rm' => 1_250_000,
                'budget_spent_rm' => 1_180_000,
                'severity' => 'high',
                'reported_at' => now()->subDays(90),
                'resolved_at' => now()->subDays(35),
            ],
            [
                'reference' => 'PH-ART0002',
                'road_name' => 'NKVE slip road B',
                'area' => 'Subang',
                'corridor_type' => CorridorType::Arterial,
                'status' => PotholeStatus::InProgress,
                'latitude' => 3.0912000,
                'longitude' => 101.5614000,
                'street_risk' => 79,
                'ai_risk_score' => 77,
                'traffic_impact' => 84,
                'safety_risk' => 82,
                'cost_to_fix' => 70,
                'rakyat_impact' => 66,
                'gdp_impact' => 70,
                'dominant_impact' => DominantImpact::Balanced,
                'impact_reason' => 'Expressway slip — safety + commute GDP mix.',
                'spend_recommendation' => 'Keep funded; slip-road failure multiplies delays.',
                'estimated_cost_rm' => 890_000,
                'budget_spent_rm' => 540_000,
                'severity' => 'high',
                'reported_at' => now()->subDays(22),
            ],
            [
                'reference' => 'PH-PJ0002',
                'road_name' => 'Jalan Gasing',
                'area' => 'Petaling Jaya',
                'corridor_type' => CorridorType::Commercial,
                'status' => PotholeStatus::New,
                'latitude' => 3.1008000,
                'longitude' => 101.6495000,
                'street_risk' => 58,
                'ai_risk_score' => 55,
                'traffic_impact' => 52,
                'safety_risk' => 50,
                'cost_to_fix' => 35,
                'rakyat_impact' => 70,
                'gdp_impact' => 48,
                'dominant_impact' => DominantImpact::Rakyat,
                'impact_reason' => 'Shopfront access; moderate rakyat visibility.',
                'spend_recommendation' => 'Lower priority than SS2 motorcycle lane.',
                'estimated_cost_rm' => 70_000,
                'budget_spent_rm' => 0,
                'severity' => 'low',
                'reported_at' => now()->subDays(2),
            ],
        ];

        $photos = [
            'PH-KLANG001' => 'potholes/pothole-klang-industrial.png',
            'PH-PJ0001' => 'potholes/pothole-pj-residential.png',
            'PH-SUB0001' => 'potholes/pothole-subang-residential.png',
            'PH-ART0001' => 'potholes/pothole-arterial-junction.png',
            'PH-KLANG002' => 'potholes/pothole-klang-industrial.png',
            'PH-SA0001' => 'potholes/pothole-commercial-street.png',
            'PH-KLANG003' => 'potholes/pothole-klang-industrial.png',
            'PH-ART0002' => 'potholes/pothole-arterial-junction.png',
            'PH-PJ0002' => 'potholes/pothole-commercial-street.png',
        ];

        $contractors = [
            'PH-KLANG001' => $selRoad->id,
            'PH-PJ0001' => $jalanKuasa->id,
            'PH-SUB0001' => $jalanKuasa->id,
            'PH-ART0001' => $selRoad->id,
            'PH-KLANG002' => $selRoad->id,
            'PH-SA0001' => $alamFlora->id,
            'PH-KLANG003' => $selRoad->id,
            'PH-ART0002' => $selRoad->id,
            'PH-PJ0002' => $jalanKuasa->id,
        ];

        foreach ($cases as $case) {
            $case['photo_path'] = $photos[$case['reference']] ?? null;
            $case['contractor_id'] = $contractors[$case['reference']] ?? null;

            PotholeCase::updateOrCreate(
                ['reference' => $case['reference']],
                $case,
            );
        }
    }

    /**
     * Demo PNGs live in public/demo/potholes (tracked in git).
     * Copy into storage so Filament /storage/... URLs keep working.
     */
    private function seedDemoPhotos(): void
    {
        $source = public_path('demo/potholes');
        $destination = storage_path('app/public/potholes');

        if (! File::isDirectory($source)) {
            return;
        }

        File::ensureDirectoryExists($destination);

        foreach (File::files($source) as $file) {
            File::copy($file->getPathname(), $destination.DIRECTORY_SEPARATOR.$file->getFilename());
        }
    }
}
