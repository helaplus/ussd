<?php


namespace Helaplus\Ussd\Http\Controllers;


class UssdController extends Controller
{

    public function __construct()
    {

    }

    public function index(){
        return "CON USSD is running";
    }
}
