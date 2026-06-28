<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaintenanceUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isMaintenance()) {
            abort(403, 'Maintenance access only.');
        }

        return $next($request);
    }
}
