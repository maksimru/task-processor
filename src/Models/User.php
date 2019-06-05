<?php

namespace MaksimM\JobProcessor\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $primaryKey = 'user_id';
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submitter_id',
    ];
    protected $guarded = [
        'api_key',
    ];
    protected $hidden = [
        'api_key',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        parent::creating(
            function (User $model) {
                $model->api_key = md5(uniqid('', true), true);
            }
        );
    }

    public function processedJobs()
    {
        return $this->hasMany(Job::class, 'processor_id', 'user_id');
    }

    public function submittedJobs()
    {
        return $this->hasMany(Job::class, 'submitter_id', 'user_id');
    }
}
