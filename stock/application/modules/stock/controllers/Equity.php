<?php

class Equity extends Common_Api_Controller {

	function __construct() {
        parent::__construct();        
	}

    /**
     * Get Collection statistics
     * @param collection_id
     * return top gainers and losser statistics collection wise
     */
    public function get_collection_statics_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model("stock/Equity_model");
        $data = $this->input->post();
      
        $collection_id = $data['collection_id'];
        $collection_data = $this->Equity_model->get_single_row('published_date, scheduled_date, end_date', COLLECTION, array('collection_id' => $collection_id));

        if(empty($collection_data)){
            $this->lineup_lang = $this->lang->line('lineup');
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $type = isset($post_data['type']) ? $post_data['type'] : 0;
        $data = array_merge($data, $collection_data);
        $data['user_id'] = $this->user_id;
        $statics = array();
        if(empty($type)) {
            $data['type'] = 1;
            $statics['gainers'] = $this->Equity_model->statics($data);

            $data['type'] = 2;
            $statics['losers'] = $this->Equity_model->statics($data);
        } else {
            $data['page'] = 1;
            $statics = $this->Equity_model->statics($data);
        }
        
        $this->api_response_arry['data'] = $statics;
        $this->api_response();
    } 
}