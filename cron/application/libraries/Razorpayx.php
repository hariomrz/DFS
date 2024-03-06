<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Razorpayx {
    const URLS          = 'https://api.razorpay.com/v1/';
    const api_key       = '';
    const secret_key    = '';
    const merchent_acount_num    = '';
    
    function __construct($config = array())
    {
        if ( ! empty($config))
        {
            // testing used
            $this->api_key      = isset($config['c_id'])?$config['c_id']:'rzp_test_CIOYR8RAAUJzXi';
            $this->secret_key   = isset($config['s_id'])?$config['s_id']:'FWZPkBoLof3tm9gKj2ZozLgo';
            $this->merchent_acount_num = isset($config['shortcode'])?$config['shortcode']:2323230015474358;
        }
    }

   
    /**
     * this function used for the create contacts
     * params: array
     * return: array
     */
    function generate_contacts($post_input,$user_bank_detail){
        
        $name           = isset($user_bank_detail['first_name'])?$user_bank_detail['first_name']:'test';
        $email          = isset($post_input['email'])?$post_input['email']:'test@gmail.com';
        $contact        = isset($user_bank_detail['phone_no'])?$user_bank_detail['phone_no']:'1234567890';
        
        $reference_id   = '';
        $random_key_1   = '';
        $random_key_2   = '';
       
        $request_data = array(
            "name"      => isset($name)?$name:'',
            "email"     => isset($email)?$email:'',
            "contact"   => isset($contact)?$contact:'',
            "type"      => "customer",
            "reference_id"=> isset($reference_id)?$reference_id:'',
            "notes"     => array(
                "random_key_1"=>isset($random_key_1)?$random_key_1:'',
                "random_key_2"=>isset($random_key_2)?$random_key_2:'',
            )
        );

       
        $method = 'contacts';
        $contact_res=$this->_curl_exe($method,$request_data);
        
        if(!empty($contact_res) && !empty($contact_res['status']) && !empty($contact_res['data'])){

            $contact_data   = $contact_res['data'];
            $fund_acount_id  = $this->create_fund_accounts($contact_data,$user_bank_detail);
            
            return $fund_acount_id;
            // if(!empty($fund_res) && !empty($fund_res['status']) && !empty($fund_res['data'])){
            //     $return['status']   = true;
            //     $return['data']     = $fund_res['data'];
            // }else{
            //    throw new Exception($fund_res['msg']);
            // }
        }else{
            throw new Exception($contact_res['msg']);
        }
    }   

    /**
     *  this function used for the create fund account
     *  parmas:array()
     *  return frund_id
     */
    function create_fund_accounts($contact_data,$user_bank_detail){
        
        // account details
        $bank_account = array(
            "name"  => $user_bank_detail['first_name'],
            "ifsc"  => $user_bank_detail['ifsc_code'],
            "account_number"=> $user_bank_detail['ac_number']
        );

        $c_id = $contact_data['id'];
        $request_fund = array(
            "contact_id"    => $c_id,
            "account_type"  => "bank_account",
            "bank_account"  => $bank_account
        );

        $method     = 'fund_accounts';
        $fund_res   = $this->_curl_exe($method,$request_fund);

        if(!empty($fund_res) && !empty($fund_res['status']) && !empty($fund_res['data'])){
            $fund_data   = $fund_res['data'];
            // return fund account id
            return $fund_data['id']; 
        }else{
            throw new Exception($fund_res['msg']);
        }
    }

    /**
     *  this function used for the create payout request
     *  params: array
     *  return: array
     */
    function create_payout_request($payout_req){  

        $method     = 'payouts';
        $fund_res   = $this->_curl_exe($method,$payout_req);

        if(!empty($fund_res) && !empty($fund_res['status']) && !empty($fund_res['data'])){
            $fund_data   = $fund_res['data'];
            return $fund_data;
        }else{
            throw new Exception($fund_res['msg']);
        }
    }

    function razorpayx_txn_status($txnid)
    {
        $header = base64_encode($this->api_key.':'.$this->secret_key);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => RAZORPAYX_STATUS_URL.$txnid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$header
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response,true);
    }


    /**
     * common CURL execution
     */
    function _curl_exe($method,$request_data) {
        try{
           
            $return = array('status'=>false,'msg'=>'','data'=>array());
            $curl = curl_init();
        
            curl_setopt($curl, CURLOPT_URL, 'https://api.razorpay.com/v1/'.$method);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($curl, CURLOPT_USERPWD, $this->api_key.":".$this->secret_key);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));

            $response = curl_exec($curl);
            curl_getinfo($curl, CURLINFO_HTTP_CODE);//get status code
            curl_close($curl);

            if(!empty($response)){
                $response = json_decode($response, TRUE);
                // check if any error in curl response
                $error  = isset($response['error'])?$response['error']:'';
                $error_msg  = isset($error['description'])?$error['description']:'';
                
                if(!empty($error) & !empty($error_msg)){
                    
                    $return['status']   = false;
                    $return['msg']      = 'Method:'.$method.', '.$error_msg;

                    return $return;
                }
            }

            $return['status']   = true;
            $return['data']     = $response;
            return $return;


        }catch(Exception $e)
        {
            throw new Exception($e);
        }
    }
    
}