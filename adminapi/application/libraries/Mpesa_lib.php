<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mpesa_lib {

  const CF_URL = 'https://payout-api.cashfree.com/payout/v1/';
  const TEST_URL = 'https://payout-gamma.cashfree.com/payout/v1/';
  public $token = '';
  public $config = array();

  function __construct($config = array())
    {
        if ( ! empty($config))
        {
            $this->get_token($config);
        }

        log_message('debug', 'cashfree Class Initialized');
    }

    function get_token($config)
    {
        $mod = ($config['mode']=="TEST") ? "sandbox":"api";
        $url = str_replace('{{mode}}',$mod,MPESA_ACCESS_TOKEN_URL);
        $header = base64_encode($config['c_id'] . ':' . $config['s_id']);
        $curl_data = array(
            "url"=>$url,
            "header"=>array('Authorization: Basic ' .$header),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1
        );
        $response = $this->_curl_exe($curl_data);
        $this->token =  $response['access_token'];
        $this->config = $config;
    }
    function php_curl($url,$post_data)
    {
        
        $curl_data = array(
            "url"=>$url,
            "header"=>array(
                    'Authorization: Bearer '.$this->token,
                    'Content-Type: application/json'
            ),
            "post"=>0,
            "post_data"=>json_encode($post_data),
            "returtransfer"=>1,
            "header_flag"=>false,
            "ssl_flag"=>false,
        );
        // print_r($curl_data);die;
        $response = $this->_curl_exe($curl_data);
        return $response;
    }

    /**
     * common CURL execution
     */
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

  function transfer($amount,$phone,$order_data)
  {
    //   print_r($order_data);die;
    $mod = ($this->config['mode']=="TEST") ? "sandbox":"api";
    $url = str_replace('{{mode}}',$mod,MPESA_PAYOUT_URL);
    $post_data = array(
        "InitiatorName"=>$this->config['initiator'],
        "SecurityCredential"=>$this->config['secret_cred'],
        "CommandID"=>"BusinessPayment",
        "Amount"=>$amount,
        "PartyA"=>$this->config['shortcode'],
        "PartyB"=>254705378676, //$phone,
        "Remarks"=>"Mpesa Withdraw",
        "QueueTimeOutURL"=>USER_API_URL . 'mpesa/payout_callback?tx='.$order_data['transaction_id'],
        "ResultURL"=>USER_API_URL . 'mpesa/payout_callback?tx='.$order_data['transaction_id'],
        "Occassion"=>"",
    );
    $result = $this->php_curl($url,$post_data);
    // print_r($result);die;
    if($result['ResultCode']==0) return true;
    return false;
  }

  function get_mpesa_txn_status($txnid)
{
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
            "ResultURL"             => str_replace('http','https',USER_API_URL) . 'mpesa/callback',
            "QueueTimeOutURL"       => str_replace('http','https',USER_API_URL) . 'mpesa/callback',
            "Remarks"               => "status check",
            "Occassion"             => ""
        );
        
        $curl_data = array(
            "url"=>$status_url,
            "header"=>array(
                    'Authorization: Bearer '.$this->token,
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

  }