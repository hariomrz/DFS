<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scratchwin extends Common_Api_Controller {

    public function __construct()
    {
        parent::__construct();
        if(!$this->app_config['allow_scratchwin']['key_value']){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Scratch & win module is disabled";
			$this->api_response();
        }
		$this->admin_lang = $this->lang->line('scratchwin');
        $this->load->model('Scratchwin_model');
        $_POST = $this->post();
    }

    public function get_scratch_card_post(){ 
        $result = $this->Scratchwin_model->get_rendom_scratch_card();
        $content = array();
        if($result['scratch_card_id']){
            //insert into scratch card claimed 
            $content = $result;
            $content['custom_data'][] = array(
                    "amount"=>$result['amount'],
                    "prize_type"=>$result['prize_type']
            );
        
            $_POST['amount'] = ($result['amount']) ? $result['amount']:0;
            $content = json_encode($content);
            $update_id = $this->Scratchwin_model->update_scratch_card_record($content);
            if($update_id){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = $result['prize_data'];
                $this->api_response_arry['message'] = "get scratch card successfully";
                $this->api_response();
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = '';
                $this->api_response_arry['message'] = "get scratch card successfully";
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data'] = '';
            $this->api_response_arry['message'] = "get scratch card successfully";
            $this->api_response();
        }
			

		// }else{
		// 	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['global_error'] = $this->lang->line("better_luck");
		// 	$this->api_response();
		// }
    }

    public function claim_scratch_card_post(){
        $this->form_validation->set_rules('scratch_card_id', 'Scratch card ID', 'trim|required');
        $this->form_validation->set_rules('contest_id', 'Contest ID', 'trim|required');
        $this->form_validation->set_rules('prize_data', 'Prize Data', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();

        $this->load->model('fantasy/fantasy_model');
        $check_contest_status = $this->fantasy_model->get_contest_status($post_data['contest_id']);
        
        $check = $this->Scratchwin_model->get_single_row("*",SCRATCH_WIN_CLAIMED,array("contest_id"=> $post_data['contest_id'],"user_id"=>$this->user_id));
        if($check && $check_contest_status)
        {
            if($check['status']==1){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("already_claimed");
            $this->api_response(); 
            }
            $scratch_data = json_decode($check['scratch_details'],true);
            $prize_data = $scratch_data['prize_data'];
            if($prize_data != $post_data['prize_data']){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = $this->lang->line("invalid_details");
                $this->api_response(); 
            }
            $get_card = array(
                "scratch_card_id"=>$scratch_data['scratch_card_id'],
                "prize_type"=>$scratch_data['prize_type'],
                "amount"=>$scratch_data['amount'],
                "result_text"=>$scratch_data['result_text'],
                "status"=>$scratch_data['status']
            );

        // $get_card =  $this->Scratchwin_model->get_single_row("scratch_card_id,prize_type,amount,result_text,status",SCRATCH_WIN,array("status"=>'1',"scratch_card_id"=> $post_data['scratch_card_id']));
        // echo $this->db->last_query();exit;
        // print_r($get_card);exit;
        // if(is_null($get_card)){
        //     $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        //     $this->api_response_arry['global_error'] = $this->lang->line("no_record_found");
        //     $this->api_response();
        // }
        $content = $get_card;
        unset($content['amount']);
        $content['custom_data'][] = array(
                "amount"=>$get_card['amount'],
                "prize_type"=>$get_card['prize_type']
        );
        $content = json_encode($content);
        $get_card = json_encode($get_card);

        // $update_id = $this->Scratchwin_model->get_single_row("scratch_win_claimed_id",SCRATCH_WIN_CLAIMED,array("status"=>'0',"contest_id"=> $post_data['contest_id'],"user_id"=>$this->user_id));
        $result = $this->Scratchwin_model->get_user_claimed($get_card);
        if($result){
            $this->db->update(SCRATCH_WIN_CLAIMED,["status"=>'1'],["scratch_win_claimed_id"=>$check['scratch_win_claimed_id']]);
           //Send notification for Coin purchase 
           $tmp = array();
           $tmp["notification_type"] = 431;
           $tmp["source_id"] = $post_data['scratch_card_id'];
           $tmp["notification_destination"] = 1; //  only inapp
           $tmp["user_id"] = $this->user_id;
           $tmp["added_date"] = format_date();
           $tmp["modified_date"] = format_date();
           $tmp["content"] = $content;

           $this->load->model('notification/Notify_nosql_model');
           $this->Notify_nosql_model->send_notification($tmp);
           $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
           $this->api_response_arry['message'] =  $this->lang->line("claimed_success");
           $this->api_response();

       }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("better_luck");
            $this->api_response();
        }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("invalid_details");
            $this->api_response();
        }
    }

}
?>