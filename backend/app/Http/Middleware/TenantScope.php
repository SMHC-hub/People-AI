<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    /**
     * Handle an incoming request.
     *
     * Scopes all relevant Eloquent queries to the authenticated user's tenant_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required for tenant-scoped access',
            ], 401);
        }

        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with a tenant',
            ], 403);
        }

        // Apply global scopes to tenant-aware models
        $tenantModels = [
            \App\Models\Employee::class,
            \App\Models\Department::class,
            \App\Models\Position::class,
            \App\Models\Attendance::class,
            \App\Models\Leave::class,
        ];

        foreach ($tenantModels as $model) {
            $model::addGlobalScope('tenant', function ($builder) use ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            });
        }

        return $next($request);
    }
}
