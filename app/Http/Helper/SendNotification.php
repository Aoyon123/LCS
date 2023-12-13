<?php

namespace App\Http\Helper;

use Illuminate\Http\Request;

class SendNotification
{
    public function sendNotification($FcmToken, $title, $body)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $serverKey = 'AAAAHIIT-Uo:APA91bE7DqaZnkugFtk7o7VjxkgrwvZbaO-21hmVy96Jn4XdGy9s8mvD-zgEV7JHq6-5vmWL8h5-r3x5dGRxoLSPf9pjNiD8oa2gUFvW07BqXhZ5YnwTS9Vqgpfl8gXMhfyHH8p-83TC';

        // ADD SERVER KEY HERE PROVIDED BY FCM
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
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
        if ($result === false) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        // dd($result);
    }

}
