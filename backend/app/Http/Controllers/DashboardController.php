<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request, DashboardService $dashboardService): JsonResponse
    {
        return ApiResponse::success($dashboardService->summaryFor($request->user()));
    }
}
