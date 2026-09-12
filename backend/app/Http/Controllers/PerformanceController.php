<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    /**
     * Display listing of employee performance records.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = Performance::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenantId);

        if ($request->has('review_period')) {
            $query->where('review_period', $request->input('review_period'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('review_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($reviews);
    }

    /**
     * Get macro performance distribution and statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $period = $request->input('review_period', '2026-Q2');

        $totalReviews = Performance::where('tenant_id', $tenantId)->where('review_period', $period)->count();
        $avgRating = Performance::where('tenant_id', $tenantId)->where('review_period', $period)->avg('rating') ?? 3.5;
        $promotedCount = Performance::where('tenant_id', $tenantId)->where('review_period', $period)->where('promoted', true)->count();
        $avgGoalsMet = Performance::where('tenant_id', $tenantId)->where('review_period', $period)->avg('goals_met_percent') ?? 85;

        $ratingDistribution = [
            '5_stars' => Performance::where('tenant_id', $tenantId)->where('review_period', $period)->where('rating', '>=', 4.5)->count(),
            '4_stars' => Performance::where('tenant_id', $tenantId)->where('review_period', $period)->whereBetween('rating', [3.5, 4.49])->count(),
            '3_stars' => Performance::where('tenant_id', $tenantId)->where('review_period', $period)->whereBetween('rating', [2.5, 3.49])->count(),
            'below_3' => Performance::where('tenant_id', $tenantId)->where('review_period', $period)->where('rating', '<', 2.5)->count(),
        ];

        return $this->success([
            'review_period' => $period,
            'total_reviews' => $totalReviews,
            'avg_rating' => round((float) $avgRating, 2),
            'promoted_count' => $promotedCount,
            'promotion_rate' => $totalReviews > 0 ? round(($promotedCount / $totalReviews) * 100, 1) : 0,
            'avg_goals_met_percent' => round((float) $avgGoalsMet, 1),
            'rating_distribution' => $ratingDistribution,
        ]);
    }
}
