<?php

namespace Helaplus\Ussd\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Helaplus\Ussd\Events\UssdEvent;
use App\Listeners\UssdEventListener;

class UssdEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UssdEvent::class => [
            UssdEventListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    { 
        parent::boot();
    }
}