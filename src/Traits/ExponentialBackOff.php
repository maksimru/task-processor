<?php

namespace MaksimM\JobProcessor\Traits;

use MaksimM\JobProcessor\Exceptions\ExponentialBackOffAttemptsExceededException;

/**
 * Trait ExponentialBackOff.
 */
trait ExponentialBackOff
{
    /**
     * @param \Closure      $callable
     * @param \Closure|null $errorHandler
     * @param int           $maxAttempts
     * @param float|int     $cap
     * @param float|int     $base
     *
     * @throws ExponentialBackOffAttemptsExceededException
     * @throws \Exception
     *
     * @return mixed
     */
    public function callWithExponentialBackOff(
        \Closure $callable,
        \Closure $errorHandler = null,
        $maxAttempts = 3,
        $cap = 3 * 10 ** 6,
        $base = 50 * 10 ** 3
    ) {
        $attempts = 0;
        do {
            try {
                return $callable();
            } catch (\Exception $exception) {
                if (is_callable($errorHandler)) {
                    $shouldMakeRetryAttempt = $errorHandler($exception);
                } else {
                    $shouldMakeRetryAttempt = true;
                }
                if ($shouldMakeRetryAttempt) {
                    $attempts++;
                    usleep(mt_rand(0, min($cap, $base * 2 ** $attempts)));
                } else {
                    throw $exception;
                }
            }
        } while ($attempts <= $maxAttempts);

        throw new ExponentialBackoffAttemptsExceededException('Function failed after '.($attempts - 1).' attempts');
    }
}
