<?php

namespace MaksimM\JobProcessor\Traits;

use MaksimM\JobProcessor\Exceptions\JobLockedException;
use MaksimM\JobProcessor\Models\Job;

trait TaskReader
{
    use ExponentialBackOff;

    /**
     * @return Job|bool
     *
     * @throws JobLockedException
     */
    private function getNextJob()
    {
        $nextJob = Job::notProcessed()
            ->notLocked()
            ->orderBy('priority', 'desc')
            ->first();
        if (is_null($nextJob)) {
            return false;
        }
        //lock allows us to use redis to avoid job to be processed twice
        if ($nextJob->isLocked()) {
            throw new JobLockedException();
        } else {
            $nextJob->setLock(\Auth::user());
        }

        return $nextJob;
    }
}
