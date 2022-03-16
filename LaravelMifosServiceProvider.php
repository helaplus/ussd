<?php

namespace Helaplus\LaravelMifos;

use Helaplus\LaravelMifos\Console\InstallLaravelMifosPackage;
use Illuminate\Support\ServiceProvider;

class LaravelMifosServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('mifos',function($app){
           return new Mifos();
        });
    }

    public function boot()
    {
        //Register a command if we are using the application vis CLI
        if($this->app->runningInConsole()){
            $this->commands([
                    InstallLaravelMifosPackage::class,
                ]);
        }
    }
}