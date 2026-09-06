<?php

namespace App\Services;

use App\Models\CctvDetection;
use App\Models\WasteCategory;
use Illuminate\Support\Carbon;

final class CctvDemoAnalyzer
{
    /**
     * @return array<string, string>
     */
    public static function scenarioOptions(): array
    {
        return [
            'roadside_dumping' => 'Roadside dumping · mixed household waste',
            'construction_dumping' => 'Construction dumping · debris and timber',
            'bulky_dumping' => 'Bulky dumping · furniture and mattresses',
            'no_incident' => 'No illegal-dumping activity · control clip',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function cameraOptions(): array
    {
        return [
            'shah_alam_sa17' => 'CCTV SA-17 · Shah Alam industrial edge',
            'petaling_jaya_pj59' => 'CCTV PJ-59 · Petaling Jaya roadside',
            'kajang_kj04' => 'CCTV KJ-04 · Kajang residential edge',
            'unavailable' => 'Location unavailable · no camera metadata',
        ];
    }

    public function analyze(CctvDetection $detection, string $scenario = 'roadside_dumping', string $camera = 'shah_alam_sa17'): CctvDetection
    {
        $scenario = array_key_exists($scenario, self::scenarioOptions()) ? $scenario : 'roadside_dumping';
        $camera = array_key_exists($camera, self::cameraOptions()) ? $camera : 'shah_alam_sa17';
        $profile = $this->profiles()[$scenario];
        $location = $this->locations()[$camera];
        $category = $profile['category_slug'] === null
            ? null
            : WasteCategory::query()->where('slug', $profile['category_slug'])->first();
        $analysedAt = Carbon::now();

        $result = [
            'schema_version' => 1,
            'engine' => 'SWM Vision POC',
            'mode' => 'deterministic_simulation',
            'scenario' => $scenario,
            'scenario_label' => self::scenarioOptions()[$scenario],
            'summary' => $profile['summary'],
            'analysed_at' => $analysedAt->toISOString(),
            'camera' => [
                'key' => $camera,
                'label' => $location['label'],
                'source' => $location['source'],
            ],
            'location' => $location['latitude'] === null ? null : [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'label' => $location['label'],
                'source' => $location['source'],
            ],
            'events' => $profile['events'],
            'evidence_note' => $profile['evidence_note'],
            'limitations' => [
                'Results are deterministic demo output, not a trained computer-vision model.',
                'Location is read from the selected camera fixture when available.',
                'Timestamp markers and boxes are scene annotations for this POC clip.',
            ],
        ];

        $incidentEvents = collect($profile['events'])->filter(fn (array $event): bool => $event['incident']);

        $detection->update([
            'waste_category_id' => $category?->id,
            'activity_detected' => $profile['activity_detected'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'confidence' => $incidentEvents->max('confidence') ?? $profile['confidence'],
            'raw_result' => $result,
        ]);

        return $detection->refresh();
    }

    /**
     * @return array<string, array{category_slug: ?string, activity_detected: bool, confidence: float, summary: string, evidence_note: string, events: list<array<string, mixed>>}>
     */
    private function profiles(): array
    {
        return [
            'roadside_dumping' => [
                'category_slug' => 'waste-piles',
                'activity_detected' => true,
                'confidence' => 94.2,
                'summary' => 'A vehicle stops at the verge and a person unloads a mixed waste pile beside the road.',
                'evidence_note' => 'The clip shows the roadside pile, stopped vehicle and person at the verge. Review the timestamp markers before creating a report.',
                'events' => [
                    $this->event('vehicle_stopped', 'Vehicle stopped at verge', 0.14, 82.1, false, [43, 16, 22, 25]),
                    $this->event('person_unloading', 'Person unloading waste', 0.31, 91.8, true, [47, 20, 13, 25]),
                    $this->event('illegal_dumping', 'Illegal dumping activity', 0.48, 94.2, true, [8, 37, 47, 48]),
                    $this->event('waste_pile', 'Mixed waste pile detected', 0.68, 96.1, true, [4, 32, 52, 56]),
                ],
            ],
            'construction_dumping' => [
                'category_slug' => 'construction-waste',
                'activity_detected' => true,
                'confidence' => 89.7,
                'summary' => 'The scene is classified as a construction-waste dumping event with active unloading.',
                'evidence_note' => 'The simulated construction profile highlights debris and unloading activity for an officer review.',
                'events' => [
                    $this->event('vehicle_stopped', 'Lorry stopped at verge', 0.18, 84.6, false, [30, 16, 22, 27]),
                    $this->event('person_unloading', 'Person unloading debris', 0.36, 88.1, true, [31, 17, 10, 27]),
                    $this->event('illegal_dumping', 'Illegal dumping activity', 0.54, 89.7, true, [5, 35, 57, 51]),
                    $this->event('construction_waste', 'Construction debris detected', 0.72, 92.3, true, [3, 31, 60, 57]),
                ],
            ],
            'bulky_dumping' => [
                'category_slug' => 'furniture',
                'activity_detected' => true,
                'confidence' => 86.4,
                'summary' => 'Bulky household items are detected being left at the roadside, consistent with illegal dumping.',
                'evidence_note' => 'The bulky-waste profile is a controlled scenario for testing category changes and confidence filtering.',
                'events' => [
                    $this->event('vehicle_stopped', 'Vehicle stopped at verge', 0.16, 78.7, false, [34, 20, 19, 22]),
                    $this->event('bulky_item', 'Bulky item detected', 0.42, 84.9, true, [10, 34, 42, 42]),
                    $this->event('illegal_dumping', 'Illegal dumping activity', 0.61, 86.4, true, [8, 31, 52, 50]),
                ],
            ],
            'no_incident' => [
                'category_slug' => null,
                'activity_detected' => false,
                'confidence' => 21.5,
                'summary' => 'No illegal-dumping activity meets the demo threshold in this control scenario.',
                'evidence_note' => 'Use this control scenario to see how the interface separates ordinary observations from an incident.',
                'events' => [
                    $this->event('vehicle_observation', 'Vehicle observed', 0.35, 58.4, false, [34, 20, 19, 22]),
                    $this->event('pedestrian_observation', 'Pedestrian observed', 0.57, 46.2, false, [30, 17, 10, 27]),
                ],
            ],
        ];
    }

    /**
     * @param  array{0: int, 1: int, 2: int, 3: int}  $box
     * @return array<string, mixed>
     */
    private function event(string $id, string $label, float $timestampRatio, float $confidence, bool $incident, array $box): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'timestamp_ratio' => $timestampRatio,
            'confidence' => $confidence,
            'incident' => $incident,
            'box' => ['x' => $box[0], 'y' => $box[1], 'width' => $box[2], 'height' => $box[3]],
        ];
    }

    /**
     * @return array<string, array{label: string, source: string, latitude: ?float, longitude: ?float}>
     */
    private function locations(): array
    {
        return [
            'shah_alam_sa17' => ['label' => 'CCTV SA-17 · Shah Alam industrial edge', 'source' => 'Demo camera registry', 'latitude' => 3.0755, 'longitude' => 101.521],
            'petaling_jaya_pj59' => ['label' => 'CCTV PJ-59 · Petaling Jaya roadside', 'source' => 'Demo camera registry', 'latitude' => 3.1072, 'longitude' => 101.6067],
            'kajang_kj04' => ['label' => 'CCTV KJ-04 · Kajang residential edge', 'source' => 'Demo camera registry', 'latitude' => 2.9935, 'longitude' => 101.787],
            'unavailable' => ['label' => 'Location unavailable', 'source' => 'No camera metadata', 'latitude' => null, 'longitude' => null],
        ];
    }
}
