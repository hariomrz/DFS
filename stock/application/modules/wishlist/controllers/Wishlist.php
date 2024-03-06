<?php

class Wishlist extends Common_Api_Controller {

	function __construct() {
        parent::__construct();        
	}

    /**
     * Whishlist: Used to add/remove stock
     */
    public function toggle_post() {   
        $config = array(
                        array(
                            'field' => 'stock_id',
                            'label' => $this->lang->line('stock_id'),
                            'rules' => 'trim|required'
                        )
                    );
        $this->form_validation->set_rules($config);
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->model("wishlist/Wishlist_model");
        $post_data = $this->input->post();
        $stock_id = $post_data['stock_id'];
        $stock  = $this->Wishlist_model->get_single_row('stock_id',STOCK,array("stock_id" => $stock_id));
        if($stock['stock_id']) {
            $is_wishlist = $this->Wishlist_model->is_wishlist($this->user_id, $stock_id);  
                              
            if($is_wishlist){ 
                $this->Wishlist_model->remove($this->user_id, $stock_id);
                $this->api_response_arry['message']			= $this->lang->line("remove_whishlist_success");                
            } else {
                $this->Wishlist_model->add($this->user_id, $stock_id);
                $this->api_response_arry['message']			= $this->lang->line("add_whishlist_success");
            }
            $wishlist_cache_key = "st_wishlist_".$this->user_id;
            $this->delete_cache_data($wishlist_cache_key);    
        } else {
            $this->api_response_arry['response_code'] 	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error']  	= sprintf($this->lang->line('valid_value'), $this->lang->line('stock_id'));
        }
        $this->api_response();
    }

    /**
     * Used to get user wishlist
     */
    public function list_post() {

        $config = array(
            array(
                'field' => 'day_filter',
                'label' => $this->lang->line('filter'),
                'rules' => 'trim|in_list[1,2,3]'
            )
        );
        $this->form_validation->set_rules($config);
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $day_filter = isset($post_data['day_filter']) ? $post_data['day_filter'] : 1;
        $this->load->model("wishlist/Wishlist_model");

        $from_date = $this->get_from_date($day_filter, 2); //, 1
        $from_date = $this->Wishlist_model->check_holiday_recursive($from_date, 1, '-1 day');

        $data = array(
            'from_date' => $from_date,
            'user_id' => $this->user_id,
            'day_filter' => $day_filter
        );

        $stocks  = $this->Wishlist_model->list($data);
        $this->api_response_arry['data'] = $stocks;
        $this->api_response();
    }
}