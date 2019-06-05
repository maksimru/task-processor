<?php

namespace MaksimM\JobProcessor\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use MaksimM\JobProcessor\Exceptions\ExponentialBackOffAttemptsExceededException;
use MaksimM\JobProcessor\Models\User;
use Teapot\StatusCode;

trait ApiHelpers
{
    /**
     * @var User $authenticatedSubmitter
     */
    protected $authenticatedSubmitter;

    /**
     * @param \Exception $exception
     * @param            $exit_code
     * @param            $http_code
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     *
     * @throws \Exception
     */
    private function prepareFailedResponse($exception, $exit_code, $http_code)
    {
        if (request()->wantsJson() || request()->isXmlHttpRequest()) {
            $error = ['status' => 'false', 'exit_code' => $exit_code];
            if ($exception instanceof ValidationException) {
                $error['messages'] = $exception->errors();
            }

            return response()->json($error, $http_code);
        } else {
            //pass to original exception handler
            throw $exception;
        }
    }

    /**
     * @param \Closure $closure
     *
     * @return Response
     *
     * @throws \Exception
     */
    private function handleApiRequest(\Closure $closure)
    {
        try {
            $this->authenticatedSubmitter = \Auth::user();

            return $closure();
        } catch (ExponentialBackOffAttemptsExceededException $exponentialBackOffAttemptsExceededException) {
            return response()->json(
                [
                    'status' => false,
                    'code' => StatusCode::INTERNAL_SERVER_ERROR,
                ],
                StatusCode::INTERNAL_SERVER_ERROR
            );
        } catch (ModelNotFoundException $modelNotFoundException) {
            return response()->json(
                [
                    'status' => false,
                    'code' => StatusCode::NOT_FOUND,
                ],
                StatusCode::NOT_FOUND
            );
        } catch (ValidationException $validationException) {
            return response()->json(
                [
                    'status' => false,
                    'code' => StatusCode::NOT_ACCEPTABLE,
                    'exit_code' => 1,
                    'messages' => $validationException->errors(),
                ],
                StatusCode::NOT_ACCEPTABLE
            );
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
