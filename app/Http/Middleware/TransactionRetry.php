<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\DeadlockException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TransactionRetry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 3): Response
    {
        // return $next($request);
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(fn () => $next($request));
            } catch (DeadlockException $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep(rand(100, 500));
            }
        }
    }
}
