<?php

class Profile extends Common_Api_Controller {

	function __construct() {
        parent::__construct();        
	}

    public function get_playing_experience_post() {

        $this->load->model("contest/Contest_model");
        $this->load->model("user/User_model");
        $series_counts = array();
        $series_counts['total_referral'] = 0;
        $series_counts['winning_amount'] = 0;
        $series_counts['won_contest'] = 0;
        $series_counts['total_contest'] = 0;

        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        if(!empty($balance_arr) && !empty($balance_arr['winning_amount'])) {
            $series_counts['winning_amount'] = $balance_arr['winning_amount'];
        }

        $total_joined = $this->User_model->get_user_referral_count($this->user_id);
        if($total_joined > 0) {
            $series_counts['total_referral'] = $total_joined;
        }

        $contest_counts = $this->Contest_model->get_contest_won($this->user_id);
       
        
        
        if(!empty($contest_counts['won_contest'])) {
            $series_counts['won_contest'] = $contest_counts['won_contest'];
        }
        
        
        if(!empty($contest_counts['total_contest'])) {
            $series_counts['total_contest'] = $contest_counts['total_contest'];
        }

        $this->api_response_arry['data'] = $series_counts;
        $this->api_response();
    }
}
