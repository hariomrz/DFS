<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Payu_payout {

    const URLS = array(
        "token" => PAYU_PAYOUT_TOKEN_URL,                             //"https://{{mode}}.payu.in/oauth/token",
        "default"=> PAYU_PAYOUT_DEFAULT_URL                         //"https://{{mode}}.payumoney.com/",
    );
    const CLIENT_ID = array(
        "test"=> TEST_CLIENT_ID,                                     //"6f8bb4951e030d4d7349e64a144a534778673585f86039617c167166e9154f7e",
        "prod"=> PROD_CLIENT_ID,                                      //"ccbb70745faad9c06092bb5c79bfd919b6f45fd454f34619d83920893e90ae6b",
    );
  public $token = '';
  public $config = array();
  function __construct($config = array())
    {
        if ( ! empty($config))
        {
            $this->config = $config;
            $this->get_token($config);
        }

        log_message('debug', 'Payu payout token generated Successfully : ' . $this->token);
    }


    function get_token($config)
    {
        // print_r($config);
        $mod = ($config['mode']=="TEST") ? "uat-accounts":"accounts";
        $url = str_replace('{{mode}}',$mod,self::URLS['token']);
        // $header = base64_encode($config['c_id'] . ':' . $config['s_id']);
        $token_data = "grant_type=password&scope=create_payout_transactions&client_id=".self::CLIENT_ID[strtolower($config['mode'])]."&username=".urlencode($config['initiator'])."&password=".urlencode($config['password']);
        $token_data = array(
            "url"=>$url,
            "header"=>array('Content-Type: application/x-www-form-urlencoded'),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1,
            "post_data"=>$token_data
        );
        $response = $this->_curl_exe($token_data);
        $this->token =  $response['access_token'];
    }

    function check_ifsc($ifsc='')
    {
        $mod = ($this->config['mode']=="TEST") ? "test":"payout";
        $url = str_replace('{{mode}}',$mod,self::URLS['default']);
        $ifsc_check = array(
                    "url"=>$url.'payout/merchant/getIfscDetails?ifsc='.$ifsc,
                    "curlopt_customrequest"=>'GET',
                    "header"=>array(
                                    'payoutMerchantId: '.$this->config['shortcode'],
                                    'Authorization: Bearer '.$this->token,
                                    'Cache-Control: no-cache',
                                    'Content-Type: application/x-www-form-urlencoded'
                                ),
                    "header_flag"=>false,
                    "ssl_flag"=>false,
                    "returtransfer"=>1
                );
        $is_ifsc =  $this->_curl_exe($ifsc_check);

        if($is_ifsc['status']==1)
        {
            throw new Exception($is_ifsc['msg']);
        }
        // print_r($is_ifsc);die;
    }

    function get_balance($amount)
    {
        $mod = ($this->config['mode']=="TEST") ? "test":"payout";
        $url = str_replace('{{mode}}',$mod,self::URLS['default']);
        $ac_det_data = array(
            "url"=>$url.'payout/merchant/getAccountDetail',
            "header"=>array(
						    'payoutMerchantId: '.$this->config['shortcode'],
						    'Authorization: Bearer '.$this->token,
						    'Cache-Control: no-cache',
						    'Content-Type: application/x-www-form-urlencoded'
						  ),
            "header_flag"=>false,
            "ssl_flag"=>false,
            "returtransfer"=>1
        );
        $ac_det = $this->_curl_exe($ac_det_data);
        
        if($ac_det['data']['balance'] < $amount)
        {
            throw new Exception("Facing some Technical issue right now, please try after some time.");
        }
        // print_r($ac_det);die;
    }
  function transfer($banificiary)
  {
    $mod = ($this->config['mode']=="TEST") ? "test":"payout";
    $url = str_replace('{{mode}}',$mod,self::URLS['default']);

    $transfer_data = array(
        "url"=>$url.'payout/payment',
        "header"=>array(
                        'payoutMerchantId: '.$this->config['shortcode'],
                        'Authorization: Bearer '.$this->token,
                        'Cache-Control: no-cache',
                        'Content-Type: application/json'
                      ),
        "curlopt_customrequest"=>'POST',
        "post_data"=>$banificiary,
        "header_flag"=>false,
        "ssl_flag"=>false,
        "returtransfer"=>1
    );
        $result = $this->_curl_exe($transfer_data);
        return $result;
  }

  function get_txn_status($filter)
  {
    $mod = ($this->config['mode']=="TEST") ? "test":"payout";
    $url = str_replace('{{mode}}',$mod,self::URLS['default']);

    $transfer_data = array(
        "url"=>$url.'payout/payment/listTransactions',
        "header"=>array(
                        'payoutMerchantId: '.$this->config['shortcode'],
                            'Authorization: Bearer '.$this->token,
                        'Cache-Control: no-cache',
                        'Content-Type: application/x-www-form-urlencoded'
                      ),
        "curlopt_customrequest"=>'POST',
        "post_data"=>$filter,
        "header_flag"=>false,
        "ssl_flag"=>false,
        "returtransfer"=>1
    );
        // echo $this->token;
        // die;
        $result = $this->_curl_exe($transfer_data);
        // print_r($result);
        // die;
        return $result;
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
  }