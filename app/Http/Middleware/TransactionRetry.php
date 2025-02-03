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
     * Base delay in milliseconds for exponential backoff
     */
    private int $baseDelayMs;

    /**
     * Maximum jitter in milliseconds to add to delay
     */
    private int $maxJitterMs;

    /**
     * @param  int  $baseDelayMs  Base delay in milliseconds (default: 100ms)
     * @param  int  $maxJitterMs  Maximum jitter in milliseconds (default: 50ms)
     */
    public function __construct(int $baseDelayMs = 100, int $maxJitterMs = 50)
    {
        $this->baseDelayMs = $baseDelayMs;
        $this->maxJitterMs = $maxJitterMs;
    }

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
            } catch (\Illuminate\Database\QueryException $e) { // Catch broader QueryException for transient DB errors
                if ($this->isTransientException($e)) { // Check if exception is transient
                    logger()->warning("Transient database exception on attempt {$attempt}", ['url' => $request->fullUrl(), 'exception' => $e->getMessage()]);
                    if ($attempt >= $maxAttempts) {
                        logger()->error("Transaction failed after {$maxAttempts} attempts due to transient database error", ['url' => $request->fullUrl(), 'exception' => $e->getMessage()]);
                        throw $e;
                    }
                    // Exponential backoff with jitter: (2^attempt * 100ms) + random(50ms)
                    $baseDelay = pow(2, $attempt - 1) * $this->baseDelayMs;
                    $jitter = rand(0, $this->maxJitterMs);
                    usleep(($baseDelay + $jitter) * 1000); // Convert ms to microseconds
                } else {
                    throw $e; // Re-throw non-transient exceptions
                }
            } catch (DeadlockException $e) { // Keep DeadlockException handling
                logger()->warning("Deadlock Exception on attempt {$attempt}", ['url' => $request->fullUrl(), 'exception' => $e->getMessage()]);
                if ($attempt >= $maxAttempts) {
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
        $sqlState = $e->getCode(); // Using getCode() to get SQLSTATE
        $message = $e->getMessage();

        // Common SQLSTATEs for transient errors (MySQL/MariaDB, PostgreSQL, others might vary)
        $transientSqlStates = ['08S01', '08006', '08007']; // Connection errors, communication link failure, transaction resolution unknown
        $transientErrorMessages = [ // Error message patterns indicating transient issues
            'Deadlock',
            'Lock wait timeout',
            'connection refused',
            'Connection timed out',
            'SQLSTATE[HY000] [2002] Connection refused', // Example connection refused error
            'SQLSTATE[HY000] [2006] MySQL server has gone away', // Example server gone away error
        ];

        if (in_array($sqlState, $transientSqlStates)) {
            return true;
        }

        foreach ($transientErrorMessages as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
