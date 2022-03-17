<?php

namespace Helaplus\Ussd\Tests;

use Helaplus\Ussd\UssdServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            UssdServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app){
        //perform environment setup
    }
}