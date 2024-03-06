<?php

    if (!defined('BASEPATH')) {
        exit('No direct script access allowed');
    }

    function encrypt($input, $key) {
        $key = html_entity_decode($key);
        $iv = "@@@@&&&&####$$$$";
        if(function_exists('openssl_encrypt')){
            $data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
        } else {
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
            $input = pkcs5Pad($input, $size);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, $iv);
            $data = mcrypt_generic($td, $input);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = base64_encode($data);
        }
        return $data;
    }

    function decrypt($encrypted, $key) {
        $key = html_entity_decode($key);
        $iv = "@@@@&&&&####$$$$";
        if(function_exists('openssl_decrypt')){
            $data = openssl_decrypt ( $encrypted , "AES-128-CBC" , $key, 0, $iv );
        } else {
            $encrypted = base64_decode($encrypted);
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
            mcrypt_generic_init($td, $key, $iv);
            $data = mdecrypt_generic($td, $encrypted);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            $data = pkcs5Unpad($data);
            $data = rtrim($data);
        }
        return $data;
    }

    function generateSignature($params, $key) {
        if(!is_array($params) && !is_string($params)){
            throw new Exception("string or array expected, ".gettype($params)." given");			
        }
        if(is_array($params)){
            $params = getStringByParams($params);			
        }
        return generateSignatureByString($params, $key);
    }

    function verifySignature($params, $key, $checksum){
        if(!is_array($params) && !is_string($params)){
            throw new Exception("string or array expected, ".gettype($params)." given");
        }
        if(isset($params['CHECKSUMHASH'])){
            unset($params['CHECKSUMHASH']);
        }
        if(is_array($params)){
            $params = getStringByParams($params);
        }		
        return verifySignatureByString($params, $key, $checksum);
    }

    function generateSignatureByString($params, $key){
        $salt = generatePRandomString(4);
        return calculateChecksum($params, $key, $salt);
    }

    function verifySignatureByString($params, $key, $checksum){
        $paytm_hash = decrypt($checksum, $key);
        $salt = substr($paytm_hash, -4);
        return $paytm_hash == calculateHash($params, $salt) ? true : false;
    }

    function generatePRandomString($length) {
        $random = "";
        srand((double) microtime() * 1000000);

        //$data = "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";	
        $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
        $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
        $data .= "0FGH45OP89";

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }

        return $random;
    }

    function getStringByParams($params) {
        ksort($params);		
        $params = array_map(function ($value){
            return ($value !== null && strtolower($value) !== "null") ? $value : "";
        }, $params);
        return implode("|", $params);
    }

    function calculateHash($params, $salt){
        $finalString = $params . "|" . $salt;
        $hash = hash("sha256", $finalString);
        return $hash . $salt;
    }

    function calculateChecksum($params, $key, $salt){
        $hashString = calculateHash($params, $salt);
        return encrypt($hashString, $key);
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5Unpad($text) {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text))
            return false;
        return substr($text, 0, -1 * $pad);
    }


function callPayoutAPI($api_url, $post_data, $headers=array()) {
    $headers[] = 'Content-Type: application/json';

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    try {
        $response = curl_exec($ch);
        if ($response === FALSE) {
            log_message('error', 'Error callPayoutAPI: ' . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true);
    } catch (Exception $e) {
        curl_close($ch);
        log_message('error', 'Error callPayoutAPI - ' . $e->getMessage());
        return "";
    }
}
