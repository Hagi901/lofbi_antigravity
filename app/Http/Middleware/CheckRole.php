<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Contoh pemakaian di route:
     *   Route::middleware('role:admin,operator')->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            // API route → JSON, web route → halaman 403
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin.',
                ], 403);
            }
            abort(403, 'Akses ditolak. Role Anda (' . ($user->role ?? 'guest') . ') tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
