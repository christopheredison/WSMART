<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    return redirect(RouteServiceProvider::HOME);
                }
            } catch (QueryException $e) {
                // Kasus transient: koneksi DB sempat drop saat first-hit auth check.
                if (! $this->isTransientConnectionError($e)) {
                    throw $e;
                }

                usleep(200000); // retry kecil (200ms), setara "manual refresh" otomatis sekali.

                try {
                    if (Auth::guard($guard)->check()) {
                        return redirect(RouteServiceProvider::HOME);
                    }
                } catch (QueryException $retryException) {
                    if (! $this->isTransientConnectionError($retryException)) {
                        throw $retryException;
                    }

                    Log::warning('Transient DB error in RedirectIfAuthenticated.', [
                        'guard' => $guard,
                        'message' => $retryException->getMessage(),
                    ]);
                }
            }
        }

        return $next($request);
    }

    private function isTransientConnectionError(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $message = strtolower($e->getMessage());

        return in_array($sqlState, ['08006', '08001'], true)
            || str_contains($message, 'connection refused')
            || str_contains($message, 'could not connect')
            || str_contains($message, 'server closed the connection unexpectedly');
    }
}
