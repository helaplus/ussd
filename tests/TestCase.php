<?php

namespace Helaplus\LaravelMifos\Tests;

use Helaplus\LaravelMifos\LaravelMifosServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMifosServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app){
        //perform environment setup
    }
}