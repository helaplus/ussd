<?php

namespace Helaplus\Ussd\Events;

use Helaplus\Ussd\Models\UssdState;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UssdEvent
{
    use Dispatchable, SerializesModels;

    public $state;

    public $eventType;

    public function __construct(UssdState $state,$eventType)
    {
        $this->state = $state;
        $this->eventType = $eventType;
    }
}