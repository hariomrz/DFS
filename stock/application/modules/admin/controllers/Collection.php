<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collection extends Common_Api_Controller {

    public function __construct() {
        parent::__construct();
        $_POST = $this->post();
        $this->load->model('admin/Collection_model');      
    }

    /**
     * Used to get fixture list
     */
    public function get_fixtures_post() {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('category_id', $this->lang->line('category'), 'trim|required');            
        $this->form_validation->set_rules('status', $this->lang->line('status'), 'trim|required');
        
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $fixture = $this->Collection_model->get_fixtures();
        $live_fixture = $upcoming_fixture = array();
	    if(!empty($fixture)){
            $current_date = format_date();
            $category_id	= $post_data['category_id'];
            foreach($fixture['result'] as $key => $res){
                if($category_id != 1) {
                    $date = new DateTime($res['scheduled_date']);
                    if($category_id == 2) {
                        $res['week'] = $date->format("W");
                    }

                    if($category_id == 3) {
                        $res['month'] = $date->format("F");
                    }
                }
                

                $live_date=date('Y-m-d H:i:s',strtotime($res['scheduled_date']."-". CONTEST_DISABLE_INTERVAL_MINUTE." minute"));
                if($current_date >= $live_date){
                    $live_fixture[] = $res;
                } else {
                    $upcoming_fixture[] = $res;
                }
            }
        }
        $result['total'] = $fixture['total'];
        $result['result'] = array('live_fixture' => $live_fixture, 'upcoming_fixture' => $upcoming_fixture);

        $this->api_response_arry['data'] = $result;
        $this->api_response();        
	}

    /**
     * Used to update custom message for fixture
     */
    public function update_fixture_custom_message_post(){

        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'),'trim|required');
        $post_data = $this->input->post();
        if(empty($post_data['is_remove']) || !isset($post_data['is_remove'])) {
            $this->form_validation->set_rules('custom_message','Custom Message','trim|required|max_length[160]');
        }
        
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $collection_info = $this->Collection_model->get_single_row('collection_id',COLLECTION,array('collection_id' => $post_data['collection_id']));
        
        if(empty($collection_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_not_found_msg');
            $this->api_response();
        }

        $this->Collection_model->update_fixture_custom_message($post_data);
        $this->api_response_arry['message'] = $this->lang->line('match_custom_msg_sent');
        if(isset($post_data['is_remove']) && $post_data['is_remove'] == 1){
			$this->api_response_arry['message'] = $this->lang->line('match_custom_msg_remove');
		}
        
        $this->api_response();
    }

    /**
    * function used for get fixture stats list
    */
    public function get_fixture_stats_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'),'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $collection_info = $this->Collection_model->get_single_row("collection_id, status,stock_type",COLLECTION,array('collection_id' => $post_data['collection_id']));
        if(empty($collection_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_not_found_msg');
            $this->api_response();
        }
        $post_data['status'] = $collection_info['status'];
        $post_data['stock_type'] = $collection_info['stock_type'];
        $result = $this->Collection_model->get_fixture_stats($post_data);        

        $cap_list = get_cap_types();

        foreach($result as &$row)
        {
            $row['cap']= '';
            if(isset($cap_list[$row['cap_type']]))
            {
                $row['cap'] = $cap_list[$row['cap_type']];
            }
        }

        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['data']            = $result;
        $this->api_response();
    }

    /**
	 * Used to cancel collection
	 */
	public function cancel_collection_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_collection';

		add_data_in_queue($post_data, 'stock_game_cancel');

        $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_candle');
        $this->api_response();
    }
}