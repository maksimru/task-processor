<?php

namespace MaksimM\JobProcessor\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $primaryKey = 'job_id';
    protected $table = 'jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submitter_id',
        'processor_id',
        'payload',
        'is_processed',
        'processing_time',
        'priority',
        'is_locked',
    ];

    public function processor()
    {
        return $this->belongsTo(User::class, 'processor_id', 'user_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitter_id', 'user_id');
    }

    public function getValidationRulesForUpdate()
    {
        return self::getValidationRules();
    }

    public static function getValidationRules()
    {
        return [
            'payload' => 'required',
        ];
    }

    public function scopeProcessed($query)
    {
        $query->whereIsProcessed(true);
    }

    public function scopeNotProcessed($query)
    {
        $query->whereIsProcessed(false);
    }

    public function scopeLocked($query)
    {
        $query->whereIsLocked(true);
    }

    public function scopeNotLocked($query)
    {
        $query->whereIsLocked(false);
    }

    public function isLocked()
    {
        return cache()->has($this->lockKey());
    }

    private function lockKey()
    {
        return 'job-lock'.$this->getKey();
    }

    public function setLock(User $processor)
    {
        cache()->forever($this->lockKey(), 1);
        $this->update(['is_locked' => true, 'processor_id' => $processor]);
    }

    public function process()
    {
        $startupTime = $this->getMicroTime();
        $this->handleJobPayload();
        $endTime = $this->getMicroTime();
        $this->update(
            [
                'is_processed'    => true,
                'processing_time' => $endTime - $startupTime,
            ]
        );
        $this->removeLock();
    }

    private function getMicroTime()
    {
        list($usec, $sec) = explode(' ', microtime());

        return (float) $usec + (float) $sec;
    }

    private function handleJobPayload()
    {
        // function should do something useful, based on payload or something
        usleep(mt_rand(1000, 100000));
    }

    public function removeLock()
    {
        cache()->forget($this->lockKey());
        $this->update(['is_locked' => false]);
    }
}
