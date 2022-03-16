<?php

namespace Helaplus\LaravelMifos\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Helaplus\LaravelMifos\Tests\TestCase;

class InstallLaravelMIfosPackageTest extends TestCase
{
    /** @test */
    function the_install_command_copies_the_configuration()
    {
        //make sure we are starting from a clean state
        if(File::exists(config_path('mifos.php'))){
            unlink(config_path('mifos.php'));
        }

        $this->assetFalse(File::exists(config_path('blogpackage.php')));

        Artisan::call('laravelmifos:install');

        $this->assetTrue(File::exists(config_path('mifos.php')));

    }
}