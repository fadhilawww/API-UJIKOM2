<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPetugas
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'petugas') {
            return $next($request);
        }
            return response()->json(['message' => 'Akses ditolak. Anda bukan Admin.'], 403);
    }
}
