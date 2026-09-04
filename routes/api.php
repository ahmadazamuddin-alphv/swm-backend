<?php

use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResponsiblePartyController;
use App\Http\Controllers\Api\WasteCategoryController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

Route::get('/waste-categories', [WasteCategoryController::class, 'index']);
Route::get('/zones', [ZoneController::class, 'index']);
Route::get('/responsible-parties', [ResponsiblePartyController::class, 'index']);
Route::get('/reports', [ReportController::class, 'index']);
Route::post('/reports', [ReportController::class, 'store']);
Route::get('/reports/{report}', [ReportController::class, 'show']);
