<?php

App::import('Vendor', 'Utils/Web');
App::import('Vendor', 'Utils/Secure');

class Sms {

    public function __construct() {
    }

    //--------------------------------------------------------------------------

    // //-- curl
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // $result = curl_exec($ch);
    // curl_close($ch);
    // //-- curl

    //--------------------------------------------------------------------------

    static public function send($msisdn, $message, $extras=array()){
        $host = Configure::read('CSG.host');
        $uri = Configure::read('CSG.uri');
        $user = Configure::read('CSG.username');
        $pass = Configure::read('CSG.password');
        $private_key = Configure::read('CSG.private_key');
        $transid = Secure::randomString(36, $msisdn);

        $url = $host.$uri;
        $fields = array(
                'URI' => $uri,
                'USERNAME' => $user,
                'PASSWORD' => $pass,
                'MESSAGE_TYPE' => 'PUSH',
                'SUB_TYPE' => 'FREE',
                'SERVICE' => 'POPCORN-PUSH',
                'ENCODING' => 'SMS',
                'TRANSID' => $transid,
                'MSISDN' => $msisdn,
                'BODY' => $message
                );
        $data = http_build_query(array_merge($fields, $extras));
        $signature = Secure::generateSignature($data, $private_key);
        $options = array('header' => array('SIG' => $signature));

        CakeLog::write('sms', 'url: '.$url);
        CakeLog::write('sms', 'sig: '.$signature);
        CakeLog::write('sms', 'data: '.$data);

        return Web::post($url, $data, $options);
    }

}
