<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed');}

 /**
 * @method deposit cash
 * @uses funtion to get access token for each transaction
 * */

function _curl_exe($data) {
    try{
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $data['url']);

        if(isset($data['header']) && $data['header']!='')
        curl_setopt($curl, CURLOPT_HTTPHEADER, $data['header']);

        if(isset($data['curlopt_encoding']) && $data['curlopt_encoding']!='')
        curl_setopt($curl, CURLOPT_ENCODING, $data['curlopt_encoding']);

        if(isset($data['curlopt_maxredirs']) && $data['curlopt_maxredirs']!='')
        curl_setopt($curl, CURLOPT_MAXREDIRS, $data['curlopt_maxredirs']);

        if(isset($data['curlopt_timeout']) && $data['curlopt_timeout']!='')
        curl_setopt($curl, CURLOPT_TIMEOUT, $data['curlopt_timeout']);

        if(isset($data['curlopt_followlocation']) && $data['curlopt_followlocation']!='')
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $data['curlopt_followlocation']);

        if(isset($data['curlopt_http_version']) && $data['curlopt_http_version']!='')
        curl_setopt($curl, CURLOPT_HTTP_VERSION, $data['curlopt_http_version']);
        
        if(isset($data['header_flag']) && $data['header_flag']!='')
        curl_setopt($curl, CURLOPT_HEADER, $data['header_flag']);

        if(isset($data['returtransfer']) && $data['returtransfer']!='')
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, $data['returtransfer']);

        if(isset($data['ssl_flag']) && $data['ssl_flag']!='')
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $data['ssl_flag']);

        if(isset($data['post']) && $data['post']!='')
        curl_setopt($curl, CURLOPT_POST, $data['post']);

        if(isset($data['curlopt_customrequest']) && $data['curlopt_customrequest']!='')
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $data['curlopt_customrequest']);

        if(isset($data['post_data']) && $data['post_data']!='')
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data['post_data']);

        $curl_response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($curl_response,true);
        if ($response) {
            return $response;
        }else{
            return false;
        }
    }catch(Exception $e)
    {
        throw new Exception($e);
    }
}

function get_mpesa_txn_status($txnid,$config)
{
        //generate token
        $mod = ($config["mode"]=="TEST") ? "sandbox":"api";
        $url = str_replace('{{mode}}',$mod,MPESA_ACCESS_TOKEN_URL);
        $header = base64_encode($config["key"] . ':' . $config["secret"]);
        $curl_data = array(
            "url"=>$url,
            "header"=>array('Authorization: Basic ' .$header),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1
        );
        $token = _curl_exe($curl_data);

        // checking status
        $status_url = MPESA_STATUS_URL;
        $mod = ($config["mode"]=="TEST") ? "sandbox":"api";
        $status_url = str_replace('{{mode}}',$mod,MPESA_STATUS_URL);

        $publicKey = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/ProductionCertificate.cer');
        openssl_public_encrypt($config['password'], $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $post_data = array(
            "Initiator"             => $config['initiator'],
            "SecurityCredential"    => $password,
            "CommandID"             => "TransactionStatusQuery",
            "TransactionID"         => $txnid,
            "PartyA"                => $config['short_code'],
            "IdentifierType"        => "4",
            "ResultURL"             => USER_API_URL . 'mpesa/callback',
            "QueueTimeOutURL"       => USER_API_URL . 'mpesa/callback',
            "Remarks"               => "status check",
            "Occassion"             => ""
        );
        
        $curl_data = array(
            "url"=>$status_url,
            "header"=>array(
                    'Authorization: Bearer '.$token['access_token'],
                    'Content-Type: application/json'
            ),
            "post"=>0,
            "post_data"=>json_encode($post_data),
            "returtransfer"=>1,
            "header_flag"=>false,
            "ssl_flag"=>false,
        );

        $response = _curl_exe($curl_data);
        // print_r($post_data);die;
        return $response;
}
function get_phonepe_transaction_status($endpoint_url, $merchant_id, $transactionId, $salt,$key_index) {
    // CHECK PAYMENT STATUS
    $phonepe_status_url =   $endpoint_url.$merchant_id.'/'.$transactionId;
    $salt_index     =   $key_index;
    $string         =   "/pg/v1/status/".$merchant_id.'/'.$transactionId.''.$salt;   
    $sha256         =   hash('sha256', $string);
    $finalXHeader   =   $sha256.'###'.$salt_index;

    $ch = curl_init($phonepe_status_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "X-VERIFY:  $finalXHeader", "X-MERCHANT-ID: $merchant_id"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $response = curl_exec($ch);
    curl_close ($ch);
    if (!empty($response)) {
        $response = json_decode($response, TRUE);
    }
    return $response;
}
/**
     * Transaction Status
     * @param int $txnid
     * @param array $config
     * @return array
     */
    function get_juspay_transaction_status($mode, $mid, $txnid) {   
        $merchant_id    =   $mid; 
        $transactionId  =   $txnid;

        if(!isset($merchant_id) || empty($merchant_id)) {
            return FALSE;
        }
        if(!isset($transactionId) || empty($transactionId)) {
            return FALSE;
        }

        if($mode == "TEST") {
            $endpointUrl = JUSPAY_ORDER_TEST_URL;
        } else {
            $endpointUrl = JUSPAY_ORDER_TEST_URL;
        }
        
        $phonepe_status_url =   $endpointUrl.$transactionId;
        $juspayMerchantId       =   $merchant_id; 
        $basicAuth              =   base64_encode($juspayMerchantId);
        
        $curl_data['url']           = $phonepe_status_url;
        $curl_data['header']        = ["Content-Type: application/x-www-form-urlencoded","authorization:  Basic $basicAuth", "x-merchantid: $juspayMerchantId"];
        $curl_data['returtransfer'] = true;
        $response                   = _curl_exe($curl_data);
        return $response;
    }
?>