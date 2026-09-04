<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteCategory;
use Illuminate\Http\JsonResponse;

class WasteCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => WasteCategory::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']),
        ]);
    }
}
