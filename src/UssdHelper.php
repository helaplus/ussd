<?php

namespace Helaplus\Ussd;

use Helaplus\Ussd\Models\UssdLog;
use Helaplus\Ussd\Models\UssdMenu;
use Helaplus\Ussd\Models\UssdMenuItems;
use Helaplus\Ussd\Models\UssdResponse;

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
        $skipLogic = Ussd::wherePhoneAndUssdMenuId($state->phone,$menu->id)->first();

        while($skipLogic->skip == 1) {
            $menu = UssdMenu::find($menu->next_ussd_menu_id);
            $skipLogic = UssdUserMenuSkipLogic::wherePhoneAndUssdMenuId($state->phone,$menu->id)->first();
        }
        return $menu;
    }

    public function getMenuItems($id){
        return UssdMenuItems::whereMenuId($id)->get();
    }

    
    public function storeUssdResponse($state, $message)
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
    public function singleProcess($menu, $state, $step)
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
        self::storeUssdLog($state,$input,0,$response);

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

    public static function replaceTemplates($session,$response){
        //$search  = array('{param}','{param2}'); // sample
        //$replace = array('{param}','{param2');
        //$response = str_replace($search, $replace, $response);
        return $response;
    }

}