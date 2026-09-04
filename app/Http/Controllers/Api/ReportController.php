<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportSource;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReportRequest;
use App\Models\Report;
use App\Services\ReportRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::query()
            ->with(['wasteCategory:id,name', 'zone:id,name'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('open_only')) {
            $query->whereNotIn('status', [
                ReportStatus::Solved->value,
                ReportStatus::FalseReport->value,
            ]);
        }

        $reports = $query
            ->orderByDesc('risk_score')
            ->limit(500)
            ->get([
                'id',
                'reference',
                'source',
                'status',
                'latitude',
                'longitude',
                'waste_category_id',
                'zone_id',
                'risk_score',
                'submitted_at',
                'resolved_at',
            ]);

        return response()->json(['data' => $reports]);
    }

    public function show(Report $report): JsonResponse
    {
        $report->load([
            'wasteCategory',
            'zone.responsibleParty',
            'suggestedDisposalCentre',
            'assignment.driver',
            'assignment.responsibleParty',
            'resolutionProofs',
        ]);

        return response()->json(['data' => $report]);
    }

    public function store(StoreReportRequest $request, ReportRecommendationService $recommender): JsonResponse
    {
        $photoPaths = [];

        if ($request->hasFile('photo')) {
            $photoPaths[] = $request->file('photo')->store('reports/photos', 'public');
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('reports/photos', 'public');
            }
        }

        $ai = $request->input('ai_suggestions', []);

        $report = Report::create([
            'source' => ReportSource::Citizen,
            'status' => ReportStatus::New,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'photos' => $photoPaths ?: null,
            'reporter_name' => $request->input('reporter_name'),
            'reporter_phone' => $request->input('reporter_phone'),
            'reporter_email' => $request->input('reporter_email'),
            'waste_category_id' => $request->input('waste_category_id'),
            'zone_id' => $request->input('zone_id'),
            'risk_score' => $request->input('risk_score', 50),
            'notes' => $request->input('notes'),
            'suggested_manpower' => $ai['manpower'] ?? null,
            'suggested_lorries' => $ai['lorries'] ?? null,
            'suggested_deadline' => $ai['deadline'] ?? null,
            'submitted_at' => now(),
        ]);

        if (
            blank($report->suggested_manpower)
            || blank($report->suggested_lorries)
            || blank($report->suggested_deadline)
            || blank($report->suggested_disposal_centre_id)
        ) {
            $recommender->applyToReport($report);
        } else {
            $report->update([
                'suggested_disposal_centre_id' => $recommender->nearestDisposalCentreId(
                    (float) $report->latitude,
                    (float) $report->longitude,
                    $report->waste_category_id,
                ),
            ]);
        }

        $report->load(['wasteCategory', 'zone', 'suggestedDisposalCentre']);

        return response()->json(['data' => $report], 201);
    }
}
