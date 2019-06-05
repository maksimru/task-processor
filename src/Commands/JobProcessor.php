<?php

namespace MaksimM\JobProcessor\Commands;

use Auth;
use Illuminate\Console\Command;
use MaksimM\JobProcessor\Exceptions\ExponentialBackOffAttemptsExceededException;
use MaksimM\JobProcessor\Models\User;
use MaksimM\JobProcessor\Traits\TaskReader;

class JobProcessor extends Command
{
    use TaskReader;

    protected $description = 'Process next job';

    protected $signature = 'job-processor:process {--sleep=1} {--daemon=0}';

    /**
     * Execute the console command.
     *
     * @throws ExponentialBackOffAttemptsExceededException
     *
     * @return mixed
     */
    public function handle()
    {
        // We can use some special service account or other methods based on requirements, it will be stored as processor_id
        Auth::setUser(User::first());
        do {
            $job = $this->callWithExponentialBackOff(
                function () {
                    return $this->getNextJob();
                },
                null,
                20
            );
            // execute the job
            if ($job) {
                $job->process();
            }
            if (!$this->option('daemon')) {
                break;
            }
            sleep($this->option('sleep'));
        } while (true);
    }
}
