<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Cache::put(
                $this->getOnlineCacheKey((int) Auth::id()),
                now()->timestamp,
                now()->addMinutes(2),
            );
        }

        return $next($request);
    }

    protected function getOnlineCacheKey(int $userId): string
    {
        return "user-online:{$userId}";
    }
}
