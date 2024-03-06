<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Spinthewheel extends Common_Api_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Spinthewheel_model');
        $_POST = $this->post();
        $allow_spin = isset($this->app_config['allow_spin'])?$this->app_config['allow_spin']['key_value']:0;

        if($allow_spin!=1){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = "You have not access of Spin Module";
            $this->api_response();
        }
    }

    /**
     * @method get_spinthewheel
     * @uses method get_spinthewheel
     * @since Oct 2020
     * @param Array limit, offset 
     * @return Array 
    */
    public function get_spinthewheel_post()
    {
        $user_id = $this->user_id;
        $post_data = $this->post();

        $page_no    = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit      = isset($post_data['page_size']) ? $post_data['page_size'] : 10;
        $offset     = get_pagination_offset($page_no, $limit);

        $get_slices_list = $this->Spinthewheel_model->get_slices_list($offset, $limit);

        $user_claimed = $this->Spinthewheel_model->spin_claimed($this->user_id);
        if(empty($user_claimed)){
            $user_claimed = 0;
        }else{
            $user_claimed = 1;
        }

        $wheel_slice_data = array();
        $setting_array = array();
        if(!empty($get_slices_list))
        {

            foreach ($get_slices_list as $key => $value) {
               
                if(isset($value['value']) && $value['value']!=""){
                   if(strlen($value['value'])>10){
                       $arr = explode(' ', $value['value'], 3);
                        if(count($arr)>2){
                         $wheel_data[$key]['value'] = $arr[0].' '.$arr[1].'^'.$arr[2];
                        }elseif(count($arr)>1){
                            $wheel_data[$key]['value'] = $arr[0].' '.$arr[1];
                        }else{
                            $wheel_data[$key]['value'] = $arr[0];
                        }

                   }else{
                       $wheel_data[$key]['value'] = $value['value'];
                   }
                }

                $wheel_data[$key]['win'] = $value['win']==1?true:false;
                $wheel_data[$key]['probability'] = $value['probability'];
                $wheel_data[$key]['resultText'] = $value['resultText'];
                $wheel_data[$key]['type'] = $value['type'];
                $wheel_data[$key]['userData'] = array("spinthewheel_id"=>$value['spinthewheel_id']);
            }

            $setting_array['colorArray'] = array("#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5","#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5");

            $setting_array['segmentValuesArray'] = $wheel_data;

            $setting_array['svgWidth'] = 1024;
            $setting_array['svgHeight'] = 1024;
            $setting_array['wheelStrokeColor'] = "#FDE06E";
            $setting_array['wheelStrokeWidth'] = 18;
            $setting_array['wheelSize'] = 900;
            $setting_array['wheelTextOffsetY'] = 110;
            $setting_array['wheelTextColor'] = "#EDEDED";
            $setting_array['wheelTextSize'] = "2.3em";
            $setting_array['wheelImageOffsetY'] = 40;
            $setting_array['wheelImageSize'] = 50;
            $setting_array['wheelImageSize'] = 50;
            $setting_array['centerCircleSize'] = 360;
            $setting_array['centerCircleStrokeColor'] = "#F1DC15";
            $setting_array['centerCircleStrokeWidth'] = 12;
            $setting_array['centerCircleFillColor'] = "#EDEDED";
            $setting_array['centerCircleImageUrl'] = "./spin2wheel/center_wheel_logo.png";
            $setting_array['centerCircleImageWidth'] = 400;
            $setting_array['centerCircleImageHeight'] = 400;
            $setting_array['segmentStrokeColor'] = "#E2E2E2";
            $setting_array['segmentStrokeWidth'] = 4;
            $setting_array['centerX'] = 512;
            $setting_array['centerY'] = 512;
            $setting_array['hasShadows'] = false;
            $setting_array['numSpins'] = 1;
            $setting_array['spinDestinationArray'] = array();
            $setting_array['minSpinDuration'] = 3;
            $setting_array['gameOverText'] = "I HOPE YOU ENJOYED SPIN WHEEL. :)";
            $setting_array['invalidSpinText'] = "INVALID SPIN. PLEASE SPIN AGAIN.";
            $setting_array['introText'] = "YOU HAVE TO<br>SPIN IT <span style='color:#F282A9;'>1</span> WIN IT!";
            $setting_array['hasSound'] = true;
            $setting_array['gameId'] = "9a0232ec06bc431114e2a7f3aea03bbe2164f1aa";
            $setting_array['clickToSpin'] = true;
            $setting_array['spinDirection'] = "cw";

            $this->api_response_arry['data'] = array('wheel_data' => $setting_array,"claimed"=>$user_claimed);
            $this->api_response();
        }
       
    }


    /**
     * @method buy_coins_post
     * @uses method buy_coins_post
     * @since Dec 2020
     * @param Array limit, offset 
     * @return Array 
    */
    public function win_spinthewheel_post()
    {
        $data = $this->post();
        $this->form_validation->set_rules('spinthewheel_id', 'Spinthewheel id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();

        $get_slice =  $this->Spinthewheel_model->get_single_row(
             "spinthewheel_id,amount,cash_type,slice_name,win",
             SPIN_THE_WHEEL,
             array("status"=>'1',"spinthewheel_id"=> $data['spinthewheel_id'])
        );

        if(empty($get_slice)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("no_record_found");
            $this->api_response();
        }

        //Check claim today 
        $today_claimed = $this->Spinthewheel_model->spin_claimed($this->user_id);
        if(!empty($today_claimed)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('err_spin_today_claimed');
            $this->api_response();
        }


        //claim today 
        $this->Spinthewheel_model->add_spin_claimed(array('user_id' =>$this->user_id,'spin_json'=>json_encode($get_slice)));

        if($get_slice['win']==0){
            $this->api_response_arry['global_error'] = "";
            $this->api_response();
        }

        if($get_slice['amount'] <= 0)
        {
            $this->api_response_arry['data'] =  "You won spin wheel";
            $this->api_response();
        }

        //Check User Balance for Package
        $this->load->model("finance/Finance_model");  
        $get_balance = $this->Finance_model->get_user_balance($this->user_id);

        $post_data['spinthewheel_id'] = $data['spinthewheel_id'];
        $post_data['amount'] = $get_slice['amount'];
        $post_data['cash_type'] = $get_slice['cash_type'];
        $post_data['slice_name'] = $get_slice['slice_name'];
        $post_data['user_id'] = $this->user_id;
        
        $result = $this->Spinthewheel_model->win_spinthewheel($post_data);

        $user_cache_key = "user_balance_" . $this->user_id;
        $this->delete_cache_data($user_cache_key);
        
        if($result)
        {
            $notification_type = "";
            if($get_slice['cash_type']==0) {
                $notification_type = 412;
            }else if($get_slice['cash_type']==1) {
                $notification_type = 413;
            }
            else if($get_slice['cash_type']==2) {
               $notification_type = 411;     
            }else if($get_slice['cash_type']==3) {
               $notification_type = 414;     
            }
            
            //Send notification for Coin purchase 
            $tmp = array();
            $tmp["notification_type"] = $notification_type;
            $tmp["source_id"] = 0;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $this->user_id;
            $tmp["to"] = $this->email;
            $tmp["user_name"] = $this->user_name;
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $input = array("prize_type" => $get_slice['cash_type'],"amount"=> $get_slice['amount'],"name" => $get_slice['slice_name']);
            $tmp["content"] = json_encode($input);

            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
            $this->api_response_arry['data'] =  $this->lang->line('succ_spin_won');
            
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('input_invalid_format');
            $this->api_response();
        }
        $this->api_response();
    }

}
/* End of file Spinthewheel.php */
/* Location: ./application/controllers/Spinthewheel.php */
