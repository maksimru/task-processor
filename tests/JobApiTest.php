<?php

namespace MaksimM\JobProcessor\Tests;

use Exception;
use Illuminate\Foundation\Application;
use MaksimM\JobProcessor\JobProcessorServiceProvider;
use MaksimM\JobProcessor\Tests\Traits\AuthenticationTrait;
use Orchestra\Testbench\BrowserKit\TestCase;

class JobApiTest extends TestCase
{

    use AuthenticationTrait;

    /**
     * @param array $response
     *
     * @return mixed
     */
    public function getIdFromApiResponse($body)
    {
        return $body['job_id'];
    }

    /**
     * @test
     * @throws Exception
     */
    public function jobApiAuthTest()
    {
        $apiResponse = $this->json(
            'POST',
            route('task.store', [], false),
            [
                'payload' => 'test_payload',
            ]
        );
        $apiResponse->assertResponseStatus(401);
    }

    /**
     * @test
     * @param int $priority
     * @return int
     * @throws Exception
     */
    public function jobCreationTest($priority = 0)
    {
        return $this->executeWithinAuthentication(
            function () use ($priority) {
                $apiResponse = $this->json(
                    'POST',
                    route('task.store', [], false),
                    [
                        'payload' => 'test_payload',
                        'priority' => $priority
                    ]
                );
                $apiResponse->assertResponseOk();
                $body = $apiResponse->decodeResponseJson();
                $this->assertEquals(\Auth::user()->getAuthIdentifier(), $body['submitter_id']);
                $this->assertEquals($priority, $body['priority']);
                return $this->getIdFromApiResponse($body);
            }
        );
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function jobShowTest()
    {
        $job_id = $this->jobCreationTest();
        $this->executeWithinAuthentication(
            function () use ($job_id) {
                $apiResponse = $this->json(
                    'GET',
                    route('task.show', [$job_id], false)
                );
                $apiResponse->assertResponseOk();
                $this->assertEquals($job_id, $apiResponse->decodeResponseJson()['job_id']);
            }
        );
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function jobPullTest()
    {
        $job_id = $this->jobCreationTest();
        $this->executeWithinAuthentication(
            function () use ($job_id) {
                $apiResponse = $this->json(
                    'GET',
                    route('task.index', [], false)
                );
                $apiResponse->assertResponseOk();
                $this->assertEquals($job_id, $apiResponse->decodeResponseJson()['job_id']);
                $this->assertEquals(false, $apiResponse->decodeResponseJson()['is_locked']);
                $this->assertEquals(true, $apiResponse->decodeResponseJson()['is_processed']);
                $this->assertNotEquals(0, $apiResponse->decodeResponseJson()['processing_time']);
            }
        );
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function jobPullLockTest()
    {
        $this->jobPullTest();
        $this->executeWithinAuthentication(
            function () {
                $apiResponse = $this->json(
                    'GET',
                    route('task.index', [], false)
                );
                $apiResponse->assertResponseOk();
                $this->assertEmpty($apiResponse->decodeResponseJson());
            }
        );
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function jobPriorityTest()
    {
        $priorities = [
            1000,
            100,
            2000,
            300
        ];
        foreach ($priorities as $priority){
            $this->jobCreationTest($priority);
        }
        $this->executeWithinAuthentication(
            function () use ($priorities) {
                $actualPriorities = [];
                foreach ($priorities as $priority){
                    $apiResponse = $this->json(
                        'GET',
                        route('task.index', [], false)
                    );
                    $apiResponse->assertResponseOk();
                    $actualPriorities[] = $apiResponse->decodeResponseJson()['priority'];
                }
                arsort($priorities);
                $this->assertEquals(array_values($priorities),array_values($actualPriorities));
            }
        );
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function jobCommandProcessorTest()
    {
        $this->jobCreationTest();
        $this->executeWithinAuthentication(
            function () {
                $this->artisan('job-processor:process');
                $apiResponse = $this->json(
                    'GET',
                    route('task.index', [], false)
                );
                $apiResponse->assertResponseOk();
                $this->assertEmpty($apiResponse->decodeResponseJson());
            }
        );
    }



    protected function getPackageProviders($app)
    {
        return [
            JobProcessorServiceProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        if (!$app['config']->has('database.connections.testing')) {
            $app['config']->set('database.connections.testing', [
                'driver' => env('DB_DRIVER', 'sqlite'),
                'database' => env('DB_DATABASE', __DIR__.'/database/'),
                'prefix' => '',
                'username' => env('DB_USERNAME', ''),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'modes' => [
                    'STRICT_TRANS_TABLES',
                    'NO_ZERO_IN_DATE',
                    'NO_ZERO_DATE',
                    'ERROR_FOR_DIVISION_BY_ZERO',
                    'NO_AUTO_CREATE_USER',
                    'NO_ENGINE_SUBSTITUTION',
                ],
                'engine' => null,
            ]);
        }
    }

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
