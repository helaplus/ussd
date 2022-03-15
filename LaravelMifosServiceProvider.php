<?php

namespace Helaplus\LaravelMifos;

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
        //
    }
}