<?php

namespace Helaplus\Ussd\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Helaplus\Ussd\Events\UssdEvent;
use Helaplus\laravelmifos\Listeners\UpdatePostTitle;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostWasCreated::class => [
            UpdatePostTitle::class,
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