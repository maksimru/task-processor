<?php

namespace MaksimM\JobProcessor\Tests;

use Exception;
use MaksimM\JobProcessor\JobProcessorServiceProvider;
use Orchestra\Testbench\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     *
     * @throws Exception
     */
    public function validateConfigFile()
    {
        $this->assertArrayHasKey('job-processor', $this->app['config']);
    }

    protected function getPackageProviders($app)
    {
        return [
            JobProcessorServiceProvider::class,
        ];
    }
}
