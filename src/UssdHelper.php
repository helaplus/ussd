<?php

namespace Helaplus\Ussd;

use Helaplus\Ussd\Models\UssdLog;

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

}