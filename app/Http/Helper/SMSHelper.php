<?php

namespace App\Http\Helper;

class SMSHelper
{
    public static function sendSMS($to, $message)
    {
        // return true;
        $mobile = substr($to, -10); //remove unwanted number
        $user = 'MysoftH'; //user name
        // $pwd = 'pbm96bsy'; //user password
        $pwd = 'MuIsPs91@Hf'; //new password
        // $sender = '8809612442238'; //sender id
        $sender = '8809617611146';
        $msg = str_replace(' ', '%20', $message); //message remove space with %20
        $smsAPI = 'https://mshastra.com/sendurlcomma.aspx'; //sms url

        $url = $smsAPI . '?user=' . $user . '&pwd=' . $pwd . '&senderid=' . $sender . '&msgtext=' . $msg . '&priority=High&CountryCode=ALL&mobileno=880' . $mobile;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: ASP.NET_SessionId=qywbgzvlyjoj32g3guovlotx',
            ),
        )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public static function generateOTP()
    {
        return random_int(1000, 9999);
    }

}
