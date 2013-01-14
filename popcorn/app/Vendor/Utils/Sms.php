<?php

App::import('Vendor', 'Utils/Web');

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
        $transid = self::randomString(36, $msisdn);

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
        $signature = self::generateSignature($data, $private_key);
        $options = array('header' => array('SIG' => $signature));

        CakeLog::write('sms', 'url: '.$url);
        CakeLog::write('sms', 'sig: '.$signature);
        CakeLog::write('sms', 'data: '.$data);

        return Web::post($url, $data, $options);
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    private function generateSignature($data, $private_key_data){
        /* This function generates a signature given the private key and the data to be signed.
         *
         * Returns a base64 encoded signature.
         *
         * Parameters:
         *
         * $data - the data to be signed
         * $private_key_data - the contents of a X.509 private key
         *
         */

        $pkeyid = openssl_get_privatekey($private_key_data);
        openssl_sign($data, $signature, $pkeyid, "sha512");
        openssl_free_key($pkeyid);

        return base64_encode($signature);
    }

    //--------------------------------------------------------------------------

    private function verifySignature($data, $public_key_data, $signature){
        /* This function verifies a signature given the public key and the signed data.
         *
         * Returns 1 if the signature verified for the data and public key; returns 0, otherwise.
         *
         * Parameters:
         *
         * $data - the data that was signed
         * $public_key_data - the conetnts of a X.509 public key
         * $signature - the signature generated for the data
         *
         */

        $pubkeyid = openssl_get_publickey($public_key_data);
        $status = openssl_verify($data, base64_decode($signature), $pubkeyid, "sha512");
        openssl_free_key($pubkeyid);

        return $status;
    }

    //--------------------------------------------------------------------------

    private function randomString($length = 8, $prefix = '') {
        $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $possible_max = strlen($possible) - 1;

        $str = $prefix;
        $len = strlen($str);

        for ($i=$len; $i<$length; $i++) {
            $idx = mt_rand(0, $possible_max);
            $char = $possible[$idx];
            $str .= $char;
        }

        return $str;
    }

}
