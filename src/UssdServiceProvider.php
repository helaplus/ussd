<?php

namespace Helaplus\Ussd;

//use Helaplus\Ussd\Console\InstallUssdPackage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class UssdServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ussd');

        // Register the service the package provides.
        $this->app->singleton('ussd', function ($app) {
            return new Ussd($app);
        });
    }

    public function boot()
    {
        //publish the config file
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('ussd.php'),
            ], 'config');

        } 
        //Register a command if we are using the application vis CLI
//        if($this->app->runningInConsole()){
//            $this->commands([
//                    InstallUssdPackage::class,
//                ]);
//        }
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('ussd.prefix')
        ];
    }
}