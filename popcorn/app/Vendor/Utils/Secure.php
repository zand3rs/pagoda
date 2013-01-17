<?php

class Secure {

    public function __construct() {
    }

    //--------------------------------------------------------------------------

    public static function generateSignature($data, $private_key_data){
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

    public static function verifySignature($data, $public_key_data, $signature){
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

    public static function randomString($length = 8, $prefix = '') {
        //$valid = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
        $valid = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr($prefix.str_shuffle($valid), 0, $length);
    }
}

