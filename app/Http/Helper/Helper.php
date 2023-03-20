<?php

namespace App\Http\Helper;

use Twilio\Rest\Client;
use Mail;
use Auth;
use App\Models\User;
use Exception;


class Helper
{ 
   	/**
    * Send Email
    *
    * @return \Illuminate\Http\Response
    */
    public static function sendEmail($data,$view,$subject)
    {
      try {
            Mail::send('mail.'.$view, $data, function($message) use ($data,$subject) {
                 $message->to($data['email'])->subject($subject.' | Meme Bike');
                 $message->from('Memebike@gmail.com', 'Meme Bike');
                });
            return 1;     
      }catch(Exception $e) {

        return $e->getMessage();

      } 
    }

     
}