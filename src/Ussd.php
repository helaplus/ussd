<?php

namespace Helaplus\Ussd;


class Ussd
{

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function launch()
    {
        return new Launch();
    }
}
