<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cashfree_payout {

  const CF_URL = 'https://payout-api.cashfree.com/payout/v1/';
  const TEST_URL = 'https://payout-gamma.cashfree.com/payout/v1/';

  private static $token = '';
  function __construct($config = array())
    {
        if ( ! empty($config))
        {
            $this->generate_token($config);
        }

        log_message('debug', 'cashfree Class Initialized');
    }




/**function to create token */
function generate_token($config)
  {
    // $url = (strtolower($config['mode'])=='prod') ? self::CF_URL : self::CF_URL;
    $url = self::CF_URL;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => self::CF_URL.'authorize',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => array(
      'X-Client-Id: '.$config['c_id'],
      'X-Client-Secret: '.$config['s_id'],
      'cache-control: no-cache'
    ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response,true);
    // echo "<pre>";
    // print_r($response);exit;
    self::$token = $response['data']['token'];
  }

  /**get currenct available balance of client */
function get_balance()
{
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => $url.'getBalance',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer '.self::$token
  ),
));
$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response,true);
// print_r($response);exit;
return $response['data']['balance'];
}



function get_bene($cf_user_id)
{
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url.'getBeneficiary/'.$cf_user_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
'Authorization: Bearer '.self::$token,
    'Postman-Token: 59c129af-c109-482a-bb6f-6fc18abde21d',
    'cache-control: no-cache'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = json_decode($response,true);
return $response;
// $_POST['status'] = 'VERIFIED';
// echo "<pre>";print_r($response);

}


function add_bene($data)
{
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url.'addBeneficiary',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
'Authorization: Bearer '.self::$token,
    'Content-Type: text/plain'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response,true);
return $response;
}


function req_transfer($data)
{
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url.'requestTransfer',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>json_encode($data),
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer '.self::$token,
    'Content-Type: text/plain'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$response = json_decode($response,true);
return $response;
}



function get_transfer_status($ref_id,$cf_order_id)
{
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url.'getTransferStatus?referenceId='.$ref_id.'&transferId='.$cf_order_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer '.self::$token,
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response =  json_decode($response,true);
// echo "<pre>";
// print_r($response);
return $response;
}

/**
 * API to remove an existing beneficiary in case if user us updating his bank details.
 * @param user unique id
 * @response array
 */
function remove_bene($cf_user_id)
{
  $post_data = json_encode(["beneId"=>$cf_user_id]);
  $url = (strtolower(ENVIRONMENT)=='production') ? self::CF_URL : self::CF_URL;
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url.'removeBeneficiary',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$post_data,
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer '.self::$token,
      'Content-Type: text/plain'
    ),
  ));
  $response = curl_exec($curl);
  curl_close($curl);
  $response = json_decode($response,true);
  return $response;
}

}