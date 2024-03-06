<?php

class Stock extends Common_Api_Controller {

	function __construct() {
        parent::__construct();        
	}

    /**
     * Used to stock card details
     */
    public function card_post() { 

    // echo "string";  die();  
        $config = array(
                        array(
                            'field' => 'stock_id',
                            'label' => $this->lang->line('stock_id'),
                            'rules' => 'trim|required'
                        ),
                        array(
                            'field' => 'day_filter',
                            'label' => $this->lang->line('filter'),
                            'rules' => 'trim|in_list[1,2,3,4,5]'
                        )
                    );
        $this->form_validation->set_rules($config);
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->model("stock/Stock_model");
        $post_data = $this->input->post();
        $stock_id = $post_data['stock_id'];
        $day_filter = isset($post_data['day_filter']) ? $post_data['day_filter'] : 1;

        $from_date = $this->get_from_date($day_filter, 2);
        $from_date = $this->Stock_model->check_holiday_recursive($from_date, 1, '-1 day');

       
        
        $stock_data  = $this->Stock_model->detail($stock_id);        
        if($stock_data) {
            $row  = $this->Stock_model->fifty_two_week_min_max_price($stock_id);
            $stock_data  = array_merge($stock_data, $row);

            if(in_array($day_filter, array(1,2))) {
                if($day_filter == 2) {
                    $pr_price = $this->Stock_model->get_history_price($stock_id, $from_date);
                    if(!empty($pr_price)) {
                        $stock_data['pr_price'] = $pr_price;
                    }
                }
                
                $from_date = $from_date.' 09:15:00';
                $history = $this->Stock_model->get_time_wise_history($stock_id, $from_date);
            } else {
                $history = $this->Stock_model->get_history($stock_id, $from_date);
                $length = count($history);
                if($length > 0) {
                    $stock_data['pr_price'] = $history[0]['price'];
                }
            }
            
          /*  if($day_filter == 1) {
                $length = count($history);
                $stock_data['pr_price'] = 0;
                if($length > 0) {
                    $stock_data['pr_price'] = $history[0]['price'];
                }
            }
            */
            $stock_data['history']  = $history;
        }
        $this->api_response_arry['data'] = $stock_data;
        $this->api_response();
    }

     /**
     * Used to stock statics details
     */
    public function statics_post() {   
        $config = array(
            array(
                'field' => 'day_filter',
                'label' => $this->lang->line('filter'),
                'rules' => 'trim|in_list[1,2,3]'
            ),
            array(
                'field' => 'type',
                'label' => $this->lang->line('type'),
                'rules' => 'trim|in_list[0,1,2]'
            )
        );
        $this->form_validation->set_rules($config);
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model("stock/Stock_model");
        $post_data = $this->input->post();
        $day_filter = isset($post_data['day_filter']) ? $post_data['day_filter'] : 1;
        $type = isset($post_data['type']) ? $post_data['type'] : 0;

        $from_date = $this->get_from_date($day_filter, 2); //, 1
        $from_date = $this->Stock_model->check_holiday_recursive($from_date, 1, '-1 day');
        $data = array(
            'type' => $type,
            'from_date' => $from_date,
            'user_id' => $this->user_id,
            'day_filter' => $day_filter
        );
        $history = array();
        if(empty($type)) {
            $data['type'] = 1;
            $history['gainers'] = $this->Stock_model->statics($data);

            $data['type'] = 2;
            $history['losers'] = $this->Stock_model->statics($data);
        } else {
            $data['page'] = 1;
            $history = $this->Stock_model->statics($data);
        }
        
        $this->api_response_arry['data'] = $history;
        $this->api_response();
    }
}