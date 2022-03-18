<?php

namespace Helaplus\Ussd;

use Helaplus\Ussd\Models\UssdLog;
use Helaplus\Ussd\Models\UssdMenu;
use Helaplus\Ussd\Models\UssdMenuItems;
use Helaplus\Ussd\Models\UssdResponse;
use Helaplus\Ussd\Models\UssdUserMenuSkipLogic;

class UssdHelper {

    public static function getInputs($request){
        $input = array();
        $latest_text = '';
        $input['session_id'] = $request->input('sessionId');
        $input['service_code'] = $request->input('serviceCode');
        $input['phone'] = $request->input('phoneNumber');
        $input['text'] = $request->input('text');   //

        $input['latest_text'] = $latest_text;
        return (object) $input;
    }
    
    public static function stateSwitch($input,$state){

        $message = $input->latest_text;
        
        switch ($state->state) {
            case 0 :
                //neutral user
                break;
            case 1 :
                //user authentication
                break;
            case 2 :
                $response = self::continueUssdProgress($state, $message);
                break;
            case 3 :
                //confirm USSD Process
                $response = self::confirmUssdProcess($state, $message);
                break;
            case 4 :
                //Go back menu
                $response = self::confirmGoBack($state, $message);
                break;
            case 5 :
                //Go back menu
                $response = self::resetPIN($state, $message);
                break;
            case 6 :
                //accept terms and conditions
                $menu = UssdMenu::find($state->menu_id);
                $response = self::customApp($state, $menu,$message);
                break;
            default:
                break;
        }
        return $response;
    }
    
    public static function isUserstarting($input)
    {
        if (strlen($input->text) == 0) {
          return TRUE;

        } else {
            return FALSE;
        }
    }
    
    public static function getHomeMenu($state){
        //reset user state
        $state = UssdHelper::resetUser($state);
        //get root Menu
        $root_menu = self::getRootMenu();

        if (!$root_menu) {
            $response = config('ussd.default_welcome_message');
        } else {
            $response = self::nextMenuSwitch($state, $root_menu);
        }
        
        return $response;
    }
    //store ussd log
    public static function storeUssdLog($input,$type=0){
        $UssdLog = new UssdLog();
        $UssdLog->phone = $input['phone'];
        $UssdLog->session_id = $input['session_id'];
        $UssdLog->service_code = $input['service_code'];
        $UssdLog->text = $input['text'];
        $UssdLog->type = $type;
        $UssdLog->save();
        return $UssdLog;
    }
    //reset user state
    public static function resetUser($state)
    {
        $state->state = 0;
        $state->progress = 0;
        $state->menu_id = 0;
        $state->difficulty_level = 0;
        $state->confirm_from = 0;
        $state->menu_item_id = 0;
        $state->save();
        return $state;
    }
    
    public static function getRootMenu(){
       return UssdMenu::whereIsRoot(1)->first();
    }


    public static function nextMenuSwitch($state, $menu)
    {

        $menu = self::checkMenuSkipLogic($state,$menu);

        switch ($menu->type) {
            case 1:
                //continue to another menu
                $menu_items = self::getMenuItems($menu->id);
                $i = 1;
                $response = $menu->title . PHP_EOL;
                foreach ($menu_items as $key => $value) {
                    $response = $response . $i . ": " . $value->description . PHP_EOL;
                    $i++;
                }

                $state->menu_id = $menu->id;
                $state->menu_item_id = 0;
                $state->progress = 0;
                $state->save();
                break;
            case 2:
                //start a process
                self::storeUssdResponse($state, $menu->id);
                $response = self::singleProcess($menu, $state, 1);
                return $menu->title.PHP_EOL.$response;
                break;
            case 3:
                //custom information app
                self::storeUssdResponse($state, $menu->id);
                $response = self::singleProcess($menu, $state, 1);
                return $menu->title.PHP_EOL.$response;
                break;
            case 4:
                //start a custom process
                self::storeUssdResponse($state, $menu->id);
                $message = '';
                self::customApp($state,$menu,$message);
                break;
            default :
                self::resetUser($state);
                $response = config('ussd.default_error_message');
                break;
        }
        return $response;
    }

    public static function checkMenuSkipLogic($state,$menu){
        $skipLogic = UssdUserMenuSkipLogic::wherePhoneAndUssdMenuId($state->phone,$menu->id)->first();
        if($skipLogic){
        while($skipLogic->skip == 1) {
            $menu = UssdMenu::find($menu->next_ussd_menu_id);
            $skipLogic = UssdUserMenuSkipLogic::wherePhoneAndUssdMenuId($state->phone,$menu->id)->first();
        }
        }
        return $menu;
    }

    public static function getMenuItems($id){
        return UssdMenuItems::whereMenuId($id)->get();
    }

    
    public static function storeUssdResponse($state, $message)
    {
        $ussd_response = new UssdResponse();
        $ussd_response->phone = $state->phone;
        $ussd_response->menu_id = $state->menu_id;
        $ussd_response->menu_item_id = $state->menu_item_id;
        $ussd_response->response = $message;
        $ussd_response->save();
        return $ussd_response;
    }
    
//start a single process
    public static function singleProcess($menu, $state, $step)
    {
        $menuItem = UssdMenuItems::whereMenuIdAndStep($menu->id, $step)->first();
        if ($menuItem) {
            //update user data and next request and send back
            $state->menu_item_id = $menuItem->id;
            $state->menu_id = $menu->id;
            $state->progress = $step;
            $state->state = 2;
            $state->save();
            return $menuItem->description;
        }
    }
    
    
    //custom app
    public static function customApp($state,$menu,$message){
        switch ($menu->id) {
            default :
                self::resetUser($state);
                $response = config('ussd.default_error_message');
                break;
        }
        return $response;
    }

    
    //send response

    public static function sendResponse($response, $type, $state=null,$input=null)
    {

        if($state == null){
            $state->app_id = 0;
        }

        $response = self::replaceTemplates($state,$response);
        //Log response
        self::storeUssdLog((array) $input,0,$response);

        if ($type == 1) {
            $output = "CON ";
        } elseif ($type == 2) {
            $output = "CON ";
            $response = $response . PHP_EOL . "1. Back to main menu" . PHP_EOL . "2. Log out";
            $state->state = 4;
            $state->progress = 0;
            $state->save();
        } else {
            $output = "END ";
        }
        $output .= $response;
        header('Content-type: text/plain');
        echo $output;
        exit;
    }

    public static function replaceTemplates($state,$response){
        //$search  = array('{param}','{param2}'); // sample
        //$replace = array('{param}','{param2');
        //$response = str_replace($search, $replace, $response);
        return $response;
    }



    public static function continueUssdProgress($state, $message)
    {
        $response = '';
        $menu = UssdMenu::find($state->menu_id);
        //check the user menu

        switch ($menu->type) {
            case 0:
                //authentication mini app
                break;
            case 1:
                //continue to another menu
                $response = self::continueUssdMenu($state, $message, $menu);
                break;
            case 2:

                //continue to a processs
                $response = self::continueSingleProcess($state, $message, $menu);
                break;
            case 3:
                //continue to a processs
                $response = self::continueSingleProcess($state, $message, $menu);
                break;
            case 4:

                $response = self::customApp($state, $menu, $message);

                break;
            default :
                self::resetUser($state);
                $response = "An authentication error occurred";
                break;
        }

        return $response;

    }
    
    public static function continueSingleProcess($state, $message, $menu)
    {
        $response = "";
        self::storeUssdResponse($state, $message);

        //validate input to be numeric
        $menuItem = UssdMenuItems::whereMenuIdAndStep($menu->id, $state->progress)->first();
        if($menuItem->validation == 'custom'){
            if(self::customValidation($state,$message,$menuItem)){
                $step = $state->progress + 1;
            }
        }elseif(strpos($menuItem->validation, '_preset')) {

            if(self::presetValidation($state,$message,$menuItem)){
                $step = $state->progress + 1;
            }
        }elseif($menuItem->validation == 'schedule'){

        }else{
                //validation is fine
                $step = $state->progress + 1;
        }

        $menuItem = UssdMenuItems::whereMenuIdAndStep($menu->id, $step)->first();

        if ($menuItem) {
            $state->menu_item_id = $menuItem->id;
            $state->progress = $step;
            $state->save();
            return $response. $menuItem->description;
        } else {
                $response = self::confirmBatch($state, $menu);
            return $response;
        }
    }
    public static function presetValidation($state,$message,$menuItem){
        switch ($menuItem->validation) {
            case "nationalId_preset":

                $config = MifosUssdConfig::whereAppId($state->app_id)->first();

                //validate national ID from Mifos
                $response = MifosHelperController::getClientByNationalId($message,$config);

                if(isset($response[0])){
                    if($response[0]->entityType == 'CLIENTIDENTIFIER'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->parentId,$config);
                        if(substr($client->mobileNo,-9) == (substr($state->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->parentId,'external_id'=>$message);
                            $state->other = json_encode($client_details);
                            $state->save();
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$state);
                        }
                    }elseif($response[0]->entityType == 'CLIENT'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->entityId,$config);

                        if(substr($client->mobileNo,-9) == (substr($state->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->entityId,'external_id'=>$message);
                            $state->other = json_encode($client_details);
                            $state->save();
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$state);
                        }
                    }{
                        return FALSE;
                    }
                }else{
                    $response = "National ID is not registered. Service only available to registered customers";
                    self::sendResponse($response,1,$state);
                }
                break;
            case "confirm_pin_preset":

                //veify if the PINs are equal
                $PIN = UssdResponse::wherePhoneAndMenuIdAndMenuItemId($state->phone, $state->menu_id,54)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = UssdResponse::wherePhoneAndMenuIdAndMenuItemId($state->phone, $state->menu_id,55)->orderBy('id', 'DESC')->first();
//                print_r($PIN->response);
//                exit;
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($state->app_id)->first();
                    $client_details = json_decode($state->other);
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);
                    if (!empty($r->errors)) {

                        if (strpos($r->defaultUserMessage, 'already exists')) {
                            //we try to update
                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config,1);
                        }
                        if(!empty($r->errors)){
                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
                            self::sendResponse($error_msg,1,$state);
                        }
                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);
                    //store PIN in session
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $state->other = json_encode($client_details);
                    $state->save();
                    return TRUE;
                }else{
                    $step = $state->progress - 1;
                    $state->progress = $step;
                    $state->save();
                    return FALSE;
                }
                break;
            case "auth_pin_preset":

                if($message == '0' && strlen($message)==1){
                    $menu = UssdMenu::find(12);
                    $response = MifosUssdHelperController::nextMenuSwitch($state,$menu);
                    MifosUssdHelperController::sendResponse($response, 1, $state,null);
                }else{
                    $response = self::validatePIN($state,$message);
                }
                break;
            case "auth_nationalId_preset":
                $config = MifosUssdConfig::whereAppId($state->app_id)->first();
                //validate national ID from Mifos
                $response = MifosHelperController::getClientByNationalId($message,$config);
                if(isset($response[0])){
                    if($response[0]->entityType == 'CLIENTIDENTIFIER'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->parentId,$config);
                        if(substr($client->mobileNo,-9) == (substr($state->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->parentId,'external_id'=>$message);
                            $state->other = json_encode($client_details);
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$state);
                        }
                    }else{
                        return FALSE;
                    }
                }
                break;
            case "auth2_nationalId_preset":
                //veify if the IDs are equal
                $PIN = UssdResponse::wherePhoneAndMenuIdAndMenuItemId($state->phone, $state->menu_id,2)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = UssdResponse::wherePhoneAndMenuIdAndMenuItemId($state->phone, $state->menu_id,2)->orderBy('id', 'DESC')->first();
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($state->app_id)->first();
                    $client_details = json_decode($state->other);
                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);

                    if (!empty($r->errors)) {

                        if (strpos($r->defaultUserMessage, 'already exists')) {
                            //we try to update
                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);
                        }
                        if(!empty($r->errors)){
                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
                            self::sendResponse($error_msg,1,$state);
                        }
                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);

                    //store PIN in session
//                    print_r($PIN->response);
//                    exit;
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $state->other = json_encode($client_details);
                    $state->save();

                    return TRUE;
                }else{
                    $step = $state->progress - 1;
                    $state->progress = $step;
                    $state->save();
                    return FALSE;
                }
                break;

            default :
                break;
        }
        return $response;
    }

    public static function customValidation($state,$message,$menuItem){
        switch ($menuItem->id) {
            default :
                return TRUE;
                break;
        }

    }

}