<?php

namespace MaksimM\JobProcessor\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use MaksimM\JobProcessor\Models\Job;
use MaksimM\JobProcessor\Traits\ApiHelpers;
use MaksimM\JobProcessor\Traits\TaskReader;
use Teapot\StatusCode;
use Validator;

class JobController
{
    use ApiHelpers, TaskReader;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     *
     * @throws Exception
     */
    public function index()
    {
        return $this->handleApiRequest(
            function () {
                // we presume that Job never fails and will be executed 100%, it can be improved if required
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

                return response()->json(
                    false === $job ? [] : $job->toArray(),
                    StatusCode::OK
                );
            }
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     */
    public function store(Request $request)
    {
        return $this->handleApiRequest(
            function () use ($request) {
                $validation = Validator::make($request->all(), Job::getValidationRules());

                if ($validation->passes()) {
                    $job = $this->authenticatedSubmitter->submittedJobs()->create($request->all());

                    return response()->json($job, StatusCode::OK);
                } else {
                    throw new ValidationException($validation);
                }
            }
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws Exception
     */
    public function show($id)
    {
        return $this->handleApiRequest(
            function () use ($id) {
                $job = $this->authenticatedSubmitter->submittedJobs()->findOrFail($id);

                return response()->json($job, StatusCode::OK);
            }
        );
    }
}
