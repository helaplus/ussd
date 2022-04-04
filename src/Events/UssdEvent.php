<?php

namespace Helaplus\Ussd\Events;

use Helaplus\Ussd\Models\UssdState;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UssdEvent
{
    use Dispatchable, SerializesModels, ShouldQueue;

    public $state; 

    public $eventType;

    public function __construct(UssdState $state,$eventType)
    {
        $this->state = $state;
        $this->eventType = $eventType;
    }
}