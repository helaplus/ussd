<?php
namespace App\Listeners;

use Carbon\Carbon;
use Helaplus\Sms\Http\Controllers\SmsController;
use Helaplus\Ussd\Events\UssdEvent;
use Illuminate\Support\Facades\Http;

class UssdEventListener
{
    public function handle(UssdEvent $event)
    {
        self::eventSwitch($event);
    }

    public function eventSwitch($event){

        switch ($event->eventType) {
            case 'UssdUserRegistered':
                //do something
                self::processRegisteredUser($event);
                break;
            case 'sms':
                self::sendSms($event);
                break;
            default:
                break;
        }
    }
    public static function processRegisteredUser($event){

    }
    public static function sendSms($event){
        SmsController::sendSms($event->state->phone,$event->state->sms);
    }

}