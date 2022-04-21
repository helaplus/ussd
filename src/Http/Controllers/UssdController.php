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
        $input = UssdHelperController::getInputs($request);

        //using the phone number get the ussd state
        $state = UssdState::firstorcreate([ 'phone'=>$input->phone]);
        //route request
        $response = UssdHelperController::isUserStarting($input) ? UssdHelperController::getHomeMenu($state) : UssdHelperController::stateSwitch($input,$state);

        //sendResponse
        return UssdHelperController::sendResponse($response,1,$state,$input);
    }

    public function multiApp(Request $request,$slug){

        //get Inputs
        $input = UssdHelperController::getInputs($request);

        //using the phone number get the ussd state
        $state = UssdState::firstorcreate([ 'phone'=>$input->phone]);
        //route request
        $response = UssdHelperController::isUserStarting($input) ? UssdHelperController::getHomeMenu($state) : UssdHelperController::stateSwitch($input,$state);

        //sendResponse
        return UssdHelperController::sendResponse($response,1,$state,$input);
    }


}
