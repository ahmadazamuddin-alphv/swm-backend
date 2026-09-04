<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;

class ZoneController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Zone::query()
                ->with('responsibleParty:id,name,phone')
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'postcode',
                    'taman',
                    'area_type',
                    'socioeconomic_group',
                    'responsible_party_id',
                ]),
        ]);
    }
}
