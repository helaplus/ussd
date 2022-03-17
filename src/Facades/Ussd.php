<?php

namespace Helaplus\Ussd\Facades;

use Illuminate\Support\Facades\Facade;

class Ussd extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ussd';
    }
}