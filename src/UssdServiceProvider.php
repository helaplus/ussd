<?php

namespace Helaplus\Ussd;

use Helaplus\Ussd\Console\InstallUssdPackage;
use Helaplus\Ussd\Facades\Ussd;
use Illuminate\Support\ServiceProvider;

class UssdServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('ussd',function($app){
           return new Ussd();
        });
    }

    public function boot()
    {
        //Register a command if we are using the application vis CLI
        if($this->app->runningInConsole()){
            $this->commands([
                    InstallUssdPackage::class,
                ]);
        }
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    } 

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('prefix')
        ];
    }
}