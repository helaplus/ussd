<?php


namespace Helaplus\Ussd\Http\Controllers;


use Helaplus\Ussd\Models\UssdState;
use Helaplus\Ussd\Ussd;
use Helaplus\Ussd\UssdHelper;
use Illuminate\Http\Request;

class UssdController extends Controller
{

    public function __construct()
    {

    }

    public function app(Request $request){
        //get Inputs
        $input = UssdHelper::getInputs($request);

        //get phone user ussd state
        $state = UssdState::firstorcreate([ 'phone'=>$input->phone]);

        return "CON Welcome to ".env('APP_NAME');
    }


}
