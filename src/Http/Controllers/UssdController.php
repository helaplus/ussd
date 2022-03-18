<?php


namespace Helaplus\Ussd\Http\Controllers;


use Helaplus\Ussd\Models\UssdMenu;
use Helaplus\Ussd\Models\UssdState;
use Helaplus\Ussd\Ussd;
use Helaplus\Ussd\UssdHelper;
use Illuminate\Http\Request;

class UssdController extends Controller
{


    public function app(Request $request){

        //get Inputs
        $input = UssdHelper::getInputs($request);

        //using the phone number get the ussd state
        $state = UssdState::firstorcreate([ 'phone'=>$input->phone]);
        //route request
        $response = UssdHelper::isUserStarting($input) ? UssdHelper::getHomeMenu($state) : UssdHelper::stateSwitch($input,$state);

        //sendResponse
        return UssdHelper::sendResponse($response,1,$state,$input);
    }


}
