<?php
class Coins_package extends Common_Api_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("Coins_package_model");
        
    }

    /**
     * @method get_coins_package_list_post
     * @uses method coins_package_list
     * @since Dec 2020
     * @param Array limit, offset 
     * @return Array 
    */
    public function get_coins_package_list_post()
    {
        $user_id = $this->user_id;
        $post_data = $this->post();

        $page_no    = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit      = isset($post_data['page_size']) ? $post_data['page_size'] : 10;
        $offset     = get_pagination_offset($page_no, $limit);

        $coins_package_list = $this->Coins_package_model->get_coins_package_list($offset, $limit);
        
        if(!empty($coins_package_list))
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['data']            = $coins_package_list;
        }
        else{
            $this->api_response_arry['data'] = array();
        }
        $this->api_response();
    }


    /**
     * @method buy_coins_post
     * @uses method buy_coins_post
     * @since Dec 2020
     * @param Array limit, offset 
     * @return Array 
    */
    public function buy_coins_post()
    {
        
        $this->form_validation->set_rules('package_id', 'Package id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->post();

        //Check coin package exist in our DB
        $coin_package = $this->Coins_package_model->get_coins_package($post_data['package_id']);
        if(empty($coin_package)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("no_record_found");
            $this->api_response();
        }

        //Check User Balance for Package
        $this->load->model("finance/Finance_model");  
        $get_balance = $this->Finance_model->get_user_balance($this->user_id);
        $wallet_balance = $get_balance['real_amount']+$get_balance['winning_amount'];
        //$wallet_balance = $get_balance['real_amount'];

        if($coin_package['amount'] > $wallet_balance){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("insufficent_amount");
            $this->api_response();
        }

        $post_data['amount'] = $coin_package['amount'];

        if($post_data['amount'] > $get_balance['real_amount']){
            $amount_left = $coin_package['amount'] - $get_balance['real_amount'];
            $post_data['real_amount'] = $get_balance['real_amount'];
            $post_data['winning_amount'] = $amount_left;
        }else{
            $post_data['real_amount'] = $coin_package['amount'];
            $post_data['winning_amount'] = 0;
        }

        $post_data['coin_package_id'] = $coin_package['coin_package_id'];
        $post_data['coins'] = $coin_package['coins'];
        $post_data['user_id'] = $this->user_id;
        
        $result = $this->Coins_package_model->buy_coins($post_data);

        if($result)
        {
            //Send notification for Coin purchase 
            $tmp = array();
            $tmp["notification_type"] = 331;
            $tmp["source_id"] = 0;
            $tmp["notification_destination"] = 7; //  Web, Push, Email
            $tmp["user_id"] = $this->user_id;
            $tmp["to"] = $this->email;
            $tmp["user_name"] = $this->user_name;
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $input = array("coins" => $coin_package['coins']);
            $tmp["content"] = json_encode($input);
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->send_notification($tmp);
            $this->api_response_arry['data'] =  $this->lang->line("coin_success_message");
            
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("error_occured");
            $this->api_response();
        }
        $this->api_response();
    }
    
}
