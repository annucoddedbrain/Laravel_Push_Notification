<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// use App\Models\User;
use App\Services\FCMService;
use App\Http\Controllers\Controller;


class NotificationSendController extends Controller
{
    public function updateDeviceToken(Request $request)
    {
        dd($request->token);

        // dd(new User());
        $auth = new User();
        $auth->device_token = $request->token;
        $auth->save();
        
        return response()->json(['Token successfully stored.']);
    }

    public function sendNotification(Request $request)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $FcmToken = User::whereNotNull('device_token')->pluck('device_token')->all();

        $serverKey = 'AAAAmREpdwM:APA91bGKptuHst891540YlY2yKqn8O8krKms6KbWfc7UokM9UVQDRKGhD0sXtE-HwBX47K0S1c8BZhFAiwk3qQvtX33BS1lDSP_MwOC86TDsfzFYWjmQZC-JnjkkdPLeyUGW6Rdw8DrY'; // ADD SERVER KEY HERE PROVIDED BY FCM
    
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];
        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }        
        // Close connection
        curl_close($ch);
        // FCM response
        dd($result);
    }


    public function sendNotificationrToUser(Request $request)
    {
        
        $id = $request->id;
        $id = 1;
       // get a user to get the fcm_token that already sent.               from mobile apps 
       $user = User::findOrFail($id);
        FCMService::send( $user->fcm_token,
            [
                'title' => 'your title',
                'body' => 'your body',
                // 'image' => $image_url
            ]
        );
    }


}
