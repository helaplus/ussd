<?php

namespace Helaplus\Ussd\Http\Events;

use Helaplus\Ussd\Models\UssdState;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UssdUserRegistered
{
    use Dispatchable, SerializesModels;

    public $state;

    public function __construct(UssdState $state)
    {
        $this->state = $state;
    }
}