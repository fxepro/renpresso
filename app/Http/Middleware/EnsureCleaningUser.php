<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCleaningUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isCleaning()) {
            abort(403, 'Cleaning crew portal only.');
        }

        return $next($request);
    }
}
