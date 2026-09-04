<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResponsibleParty;
use Illuminate\Http\JsonResponse;

class ResponsiblePartyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ResponsibleParty::query()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'phone', 'email', 'contractor_id']),
        ]);
    }
}
