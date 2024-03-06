<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function encrypt_e($input, $ky) {
    $key = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    return openssl_encrypt($input, "AES-128-CBC", $key, 0, $iv);
}

function decrypt_e($crypt, $ky) {
    $key = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    return openssl_decrypt($crypt, "AES-128-CBC", $key, 0, $iv);
}

function pkcs5_pad_e($text, $blocksize) {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

function pkcs5_unpad_e($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text)) {
        return false;
    }
    return substr($text, 0, -1 * $pad);
}

function generateSalt_e($length) {
    $random = "";
    srand((double) microtime() * 1000000);

    $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
    $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
    $data .= "0FGH45OP89";

    for ($i = 0; $i < $length; $i++) {
        $random .= substr($data, (rand() % (strlen($data))), 1);
    }

    return $random;
}

function checkString_e($value) {
    if ($value == 'null') {
        $value = '';
    }
    return $value;
}

function getChecksumFromArray($arrayList, $key, $sort = 1) {
    if ($sort != 0) {
        ksort($arrayList);
    }
    $str = getArray2Str($arrayList);
    $salt = generateSalt_e(4);
    $finalString = $str . "|" . $salt;
    $hash = hash("sha256", $finalString);
    $hashString = $hash . $salt;
    return encrypt_e($hashString, $key);
}

function getChecksumFromString($str, $key) {

    $salt = generateSalt_e(4);
    $finalString = $str . "|" . $salt;
    $hash = hash("sha256", $finalString);
    $hashString = $hash . $salt;
    return encrypt_e($hashString, $key);
}

function verifychecksum_e($arrayList, $key, $checksumvalue) {
    $arrayList = removeCheckSumParam($arrayList);
    ksort($arrayList);
    $str = getArray2StrForVerify($arrayList);
    $paytm_hash = decrypt_e($checksumvalue, $key);
    $salt = substr($paytm_hash, -4);

    $finalString = $str . "|" . $salt;

    $website_hash = hash("sha256", $finalString);
    $website_hash .= $salt;

    $validFlag = "FALSE";
    if ($website_hash == $paytm_hash) {
        $validFlag = "TRUE";
    }
    return $validFlag;
}

function verifychecksum_eFromStr($str, $key, $checksumvalue) {
    $paytm_hash = decrypt_e($checksumvalue, $key);
    $salt = substr($paytm_hash, -4);

    $finalString = $str . "|" . $salt;

    $website_hash = hash("sha256", $finalString);
    $website_hash .= $salt;

    $validFlag = "FALSE";
    if ($website_hash == $paytm_hash) {
        $validFlag = "TRUE";
    }
    return $validFlag;
}

function getArray2Str($arrayList) {
    $findme = 'REFUND';
    $findmepipe = '|';
    $paramStr = "";
    $flag = 1;
    foreach ($arrayList as $key => $value) {
        $pos = strpos($value, $findme);
        $pospipe = strpos($value, $findmepipe);
        if ($pos !== false || $pospipe !== false) {
            continue;
        }

        if ($flag) {
            $paramStr .= checkString_e($value);
            $flag = 0;
        } else {
            $paramStr .= "|" . checkString_e($value);
        }
    }
    return $paramStr;
}

function getArray2StrForVerify($arrayList) {
    $paramStr = "";
    $flag = 1;
    foreach ($arrayList as $key => $value) {
        if ($flag) {
            $paramStr .= checkString_e($value);
            $flag = 0;
        } else {
            $paramStr .= "|" . checkString_e($value);
        }
    }
    return $paramStr;
}

function removeCheckSumParam($arrayList) {
    if (isset($arrayList["CHECKSUMHASH"])) {
        unset($arrayList["CHECKSUMHASH"]);
    }
    return $arrayList;
}

// not in use

/* function getTxnStatus($requestParamList) {
    return callAPI(PAYTM_STATUS_QUERY_URL, $requestParamList);
}

function getTxnStatusNew($requestParamList) {
    return callNewAPI(PAYTM_STATUS_QUERY_NEW_URL, $requestParamList);
}

function initiateTxnRefund($requestParamList) {
    $CHECKSUM = getRefundChecksumFromArray($requestParamList, $this->app_config['allow_paytm']['custom_data'][', 0);
    $requestParamList["CHECKSUM"] = $CHECKSUM;
    return callAPI(PAYTM_REFUND_URL, $requestParamList);
}*/

function callAPI($apiURL, $requestParamList) {
    $jsonResponse = "";
    $JsonData = json_encode($requestParamList);
    $postData = 'JsonData=' . urlencode($JsonData);
    $ch = curl_init($apiURL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData))
    );
    $jsonResponse = curl_exec($ch);
    return json_decode($jsonResponse, true);
}

function callNewAPI($apiURL, $requestParamList) {
    $jsonResponse = "";
    $JsonData = json_encode($requestParamList);
    $postData = 'JsonData=' . urlencode($JsonData);
    log_message('error', 'PayTm requestParamList : ' . format_date() . ' : ' . $apiURL . '?' . $postData);
    $ch = curl_init($apiURL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData))
    );
    $jsonResponse = curl_exec($ch);
    return json_decode($jsonResponse, true);
}

function getRefundChecksumFromArray($arrayList, $key, $sort = 1) {
    if ($sort != 0) {
        ksort($arrayList);
    }
    $str = getRefundArray2Str($arrayList);
    $salt = generateSalt_e(4);
    $finalString = $str . "|" . $salt;
    $hash = hash("sha256", $finalString);
    $hashString = $hash . $salt;
    return encrypt_e($hashString, $key);
}

function getRefundArray2Str($arrayList) {
    $findmepipe = '|';
    $paramStr = "";
    $flag = 1;
    foreach ($arrayList as $key => $value) {
        $pospipe = strpos($value, $findmepipe);
        if ($pospipe !== false) {
            continue;
        }

        if ($flag) {
            $paramStr .= checkString_e($value);
            $flag = 0;
        } else {
            $paramStr .= "|" . checkString_e($value);
        }
    }
    return $paramStr;
}

function callRefundAPI($refundApiURL, $requestParamList) {
    $jsonResponse = "";
    $JsonData = json_encode($requestParamList);
    $postData = 'JsonData=' . urlencode($JsonData);
    $ch = curl_init($apiURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $refundApiURL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $jsonResponse = curl_exec($ch);
    return json_decode($jsonResponse, true);
}
