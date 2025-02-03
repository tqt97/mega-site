<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\DeadlockException;
use Illuminate\Database\QueryException;
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
                return DB::transaction(function () use ($request, $next, $attempt) {
                    logger()->debug("Transaction attempt: {$attempt}", ['url' => $request->fullUrl()]);

                    return $next($request);
                });
            } catch (QueryException $e) {
                logger()->warning("Deadlock Exception on attempt {$attempt}", ['url' => $request->fullUrl(), 'exception' => $e->getMessage()]);
                if ($attempt >= $maxAttempts || ! $this->isTransientException($e)) {
                    logger()->error("Transaction failed after {$maxAttempts} attempts due to deadlock", ['url' => $request->fullUrl(), 'exception' => $e->getMessage()]);
                    throw $e;
                }
                // Exponential backoff with jitter: (2^attempt * 100ms) + random(50ms)
                $baseDelay = pow(2, $attempt - 1) * 100;
                $jitter = rand(0, 50);
                $delayMs = $baseDelay + $jitter;
                usleep($delayMs * 1000); // Convert ms to microseconds
            }
        }
    }

    /**
     * Check if the exception is transient and retryable.
     * For simplicity, only checking for deadlock and connection errors as examples.
     * In production, expand this list based on your database error codes.
     */
    protected function isTransientException(\Illuminate\Database\QueryException $e): bool
    {
        if ($e instanceof DeadlockException) {
            return true;
        }

        $sqlErrorCode = $e->getCode();
        // Example: MySQL connection error codes (adjust based on your DB)
        $connectionErrorCodes = ['HY000', 2006, 2013]; // Example codes, might need adjustment

        return in_array($sqlErrorCode, $connectionErrorCodes);
    }
}
