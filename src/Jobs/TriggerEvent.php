<?php

namespace Helaplus\Ussd\Jobs;

use Helaplus\Ussd\Events\UssdEvent;
use Helaplus\Ussd\Models\UssdState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriggerEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $state;

    public $eventType;

    public function __construct(UssdState $state,$eventType)
    {
        $this->state = $state;
        $this->eventType = $eventType;
    }

    public function handle()
    {
        event(new UssdEvent($this->state,$this->eventType));
    }
}