<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Juspay_payout {
  const JP_URL = 'https://api.juspay.in/payout/merchant/v1/';
  const TEST_URL = 'https://api.juspay.in/payout/merchant/v1/';

  private static $token = '';
  private static $m_id = '';
  private static $mode = '';

  function __construct($config = []) {
    if (!empty($config)) {
      self::$token = base64_encode($config['c_id']);
      self::$m_id = $config['s_id'];
      self::$mode = $config['mode'];
    }
  }

  /**get current available balance of Merchant */
  function get_balance() {
    $url = (strtolower(self::$mode)=='prod') ? self::JP_URL : self::TEST_URL;
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url.'getways/balance',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'accept: text/plain',
      'Authorization: Basic '.self::$token
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response = json_decode($response,true);
  }


  function req_transfer($data) {
    //echo json_encode($data); exit;
    $url = (strtolower(self::$mode)=='prod') ? self::JP_URL : self::TEST_URL;
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url.'orders',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($data),
    CURLOPT_HTTPHEADER => array(
      'Authorization: Basic '.self::$token,
      'Content-Type: application/json',
      'x-merchantid: '.self::$m_id,
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response,true);
    return $response;
  }

  function get_transfer_status($jp_order_id) {
    $url = (strtolower(self::$mode)=='prod') ? self::JP_URL : self::TEST_URL;
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url.'orders/'.$jp_order_id.'?expand=fulfillment',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "accept: application/json",
      'Authorization: Basic '.self::$token,
      'x-merchantid: '.self::$m_id,
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response =  json_decode($response,true);
    return $response;
  }
}