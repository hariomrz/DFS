<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manualpg extends Common_Api_Controller {

    public $form_data = [];
    
	public function __construct()
	{
		parent::__construct();
        $this->load->model('Manualpg_model');
        $this->mpg_lang = $this->lang->line('manualpg');
    }

    /**
     * to get active payment mode for this admin
     */
    public function get_type_list_post()
    {
        $post_data = $this->input->post();
        $type_list = $this->Manualpg_model->get_all_table_data(DEPOSIT_TYPE,'type_id,key,custom_data',['status'=>1]);
        foreach($type_list as $key=>$val)
        {
            $type_list[$key]['custom_data'] = json_decode($val['custom_data'],true);
        }

        $user_last_txns = $this->Manualpg_model->get_last_txns($this->user_id,$post_data['limit'] ? $post_data['limit']:5);
        $this->api_response_arry['data']['type']      = $type_list;
        $this->api_response_arry['data']['last_txns'] = $user_last_txns;
        $this->api_response();
    }

    /**
     * API to add / updated payment mode for the admin.
     * @param key string
     * @param custom_data json
     */
    public function update_txn_detail_post()
    {
        $post_data = $this->input->post();
        if ($post_data) {
            $this->form_validation->set_rules('type_id', 'Payment Type', 'trim|required|callback_valid_type_id');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
            $this->form_validation->set_rules('bank_ref', 'Reference ID', 'trim|required|callback_duplicate_bank_ref');
            // $this->form_validation->set_rules('receipt', 'Receipt Image', 'trim|required');
            if (!$this->form_validation->run()) 
            {
                $this->send_validation_errors();
            }

            $txn_data = array(
                "user_id"=> $this->user_id,
                "amount"=>$post_data['amount'],
                "type_id"=>$post_data['type_id'],
                "bank_ref"=>$post_data['bank_ref'],
                "receipt_image"=>$post_data['receipt'],
                "status"=>0,
                "added_date"=>format_date(),
            );

            $this->load->helper('queue_helper');
            $content = array();
            $content['email'] = $this->app_config['support_id']['key_value'];
            $content['subject'] = "New Transaction Alert!";
            $content['user_name'] = $this->user_name;
            $content['content'] = array(
                "user_name"=>$this->user_name,
                "email"=>$this->email,
                "ref_id"=>$post_data['bank_ref'],
                "amount"=>$post_data['amount'],
                "image"=>'upload/mpg_receipt/'.$post_data['receipt'],
            );
            $content['notification_type'] = "manual_deposit";
            add_data_in_queue($content,"email");

            // print_r($txn_data);die;
            $this->Manualpg_model->update_txn_detail($txn_data);
            $this->api_response_arry['message']         = $this->mpg_lang["success_update"];
            
        } else {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']    = $this->mpg_lang['no_data'];
        }   
        $this->api_response();  // Final Output 
    }

    public function duplicate_bank_ref($bank_ref)
    {
        $ref = $this->Manualpg_model->get_single_row('bank_ref',DEPOSIT_TXN,['bank_ref'=>$bank_ref]);
        if($ref)
        {
            // $this->form_validation->set_message('duplicate_bank_ref', $this->mpg_lang["invalid_bank_ref"]);
            throw new Exception($this->mpg_lang["invalid_bank_ref"]);
            return false;
        }
        return true;
    }

    public function valid_type_id($type_id)
    {
        $typeid = $this->Manualpg_model->get_single_row("*",DEPOSIT_TYPE,['type_id'=>$type_id]);
        
        if(empty($typeid))
        {
            // $this->form_validation->set_message('valid_type_id', $this->mpg_lang["invalid_type_id"]);
            throw new Exception($this->mpg_lang["invalid_type_id"]);

            return false;
        }
        return true;
    }

}