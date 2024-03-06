<?php 
class Private_contest extends Common_Api_Controller {

    public $group_id = 7;
    public $site_rake = 10;
    public $host_rake = 0;
    public $salary_cap = SALARY_CAP;
	public function __construct()
	{
		parent::__construct();
        $private_contest = isset($this->app_config['allow_private_contest']) ? $this->app_config['allow_private_contest']['key_value'] : 0;
        if($private_contest == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('module_not_activated');
            $this->api_response();
        }
        else{
            $this->site_rake = isset($this->app_config['site_rake']) ? $this->app_config['site_rake']['key_value'] : 10;
            $this->host_rake = isset($this->app_config['host_rake']) ? $this->app_config['host_rake']['key_value'] : 0;
        }
	}

	/**
     * used for get create contest master data
     * @param array $post_data
     * @return array
    */
	public function get_contest_master_data_post()
	{
		$post_data = $this->input->post();
        $result = array();
        $result['site_rake'] = $this->site_rake;
        $result['host_rake'] = $this->host_rake;
        $result['salary_cap'] = $this->salary_cap;
        $result['prize_distribution'] = get_prize_distribution_data_for_PC();

		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

    /**
     * used for validate number of winner
     * @param array post data
     * @return boolean
    */
    public function number_of_winners_check() 
    {
        $number_of_winners = $this->input->post("number_of_winners");
        if($number_of_winners > 10)
        {
            $this->form_validation->set_message('number_of_winners_check', "Please provide number of winners less then or equal to 10.");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * used for validate contest size
     * @param int $size
     * @return boolean
    */
    public function check_participant_size() {
        $size = $this->input->post("size");
        $pc_data_cache = "pc_data_limit";
        $pc_data = $this->get_cache_data($pc_data_cache);
        if(!$pc_data){
            $this->load->model("contest/Private_contest_model");
            $pc_data = $this->Private_contest_model->get_all_table_data("data_desc,user_lower_limit,user_upper_limit",MASTER_DATA_ENTRY, array());
            $pc_data = array_column($pc_data,NULL,"data_desc");
            $this->set_cache_data($pc_data_cache, $pc_data, REDIS_30_DAYS);
        }
        $user_lower_limit = $user_upper_limit = 2;
        if(isset($pc_data['size']) && !empty($pc_data['size'])){
            $user_lower_limit = $pc_data['size']['user_lower_limit'];
            $user_upper_limit = $pc_data['size']['user_upper_limit'];
        }
        if($size > $user_lower_limit || $size < $user_upper_limit) {
            return TRUE;
        }
        $this->form_validation->set_message('check_participant_size', "Please provide size between " . $pc_data['user_lower_limit'] . " and " . $pc_data["user_upper_limit"] . ".");
        return FALSE;
    }

    /**
     * used for validate prize pool data array
     * @param array post data
     * @return boolean
    */
    public function prize_distribution_detail_check() {
        $prize_detail = $this->post('prize_distribution_detail');
        $number_of_winners = $this->input->post("number_of_winners");
        $prize_count = count($prize_detail);
        if(empty($prize_detail)) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Please provide valid prize distribution.");
            return FALSE;
        }

        $valid_prize = TRUE;
        foreach($prize_detail as $key => $value) {
            if($value['min'] != $value['max'] || $value['min'] != $key+1 || $value['max'] != $key+1)
            {
                $valid_prize = FALSE;
                break;
            }
        }

        if($valid_prize == FALSE)
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "Please provide valid prize distribution details.");
            return FALSE;
        }

        if($number_of_winners > $prize_count || $number_of_winners < $prize_count) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Number of winners should equal to prize distribution.");
            return FALSE;
        }

        $percent_arr = array_column($prize_detail, "per");
        $per_sum = round(array_sum($percent_arr));
        if ($per_sum > 100) {
            $this->form_validation->set_message('prize_distribution_detail_check', "Percent can not be greater than 100.");
            return FALSE;
        }
        else if($per_sum < 100)
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "Percent can not be less than 100.");
            return FALSE;
        }

        $currency_type = $this->input->post("currency_type");
        if($currency_type == 2){
            $this->site_rake = $this->host_rake = 0;            
        }
        $entry_fee = $this->input->post("entry_fee");
        $site_rake = $this->site_rake + $this->host_rake;
        $total_pool = (2 * $entry_fee);
        $rake_amount = number_format((($total_pool * $site_rake) / 100),2,".","");
        $prize_pool = number_format(($total_pool - $rake_amount),2,".","");
        $winning_amount = array_column($prize_detail, "amount");
        $winning_amount = number_format(array_sum($winning_amount),2,".","");
        if ($winning_amount > $prize_pool) 
        {
            $this->form_validation->set_message('prize_distribution_detail_check', "amount can not be greater than " . $prize_pool);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * used for save user contest
     * @param array $post_data
     * @return array
    */
    public function create_user_contest_post()
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('game_name', $this->lang->line('game_name'), 'trim|required');
        $this->form_validation->set_rules('game_desc', $this->lang->line('game_desc'), 'trim|required');
        $this->form_validation->set_rules('currency_type', $this->lang->line('currency_type'), 'trim|required');
        $this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required|max_length[4]');
        $this->form_validation->set_rules('size', $this->lang->line('size'), 'trim|required|is_natural_no_zero|callback_check_participant_size');
        $this->form_validation->set_rules('number_of_winners',$this->lang->line('number_of_winners'), 'trim|required|is_natural_no_zero|callback_number_of_winners_check');
        $this->form_validation->set_rules('prize_distribution_detail', $this->lang->line('prize_distribution_detail'), 'trim|callback_prize_distribution_detail_check');
        $this->form_validation->set_rules('multiple_lineup', $this->lang->line('multiple_lineup'), 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $this->load->model("contest/Private_contest_model");
        $fixture = $this->Private_contest_model->get_fixture_detail($cm_id);
        if(empty($fixture)){
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_cm_id');
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($fixture['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->lang->line('cm_started_pc_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
        $minimum_size = 2;
        $size = $post_data['size'];
        $entry_fee = $post_data["entry_fee"];
        $currency_type = $post_data["currency_type"];
        $multiple_lineup = $post_data["multiple_lineup"];
        $prize_detail = $post_data["prize_distribution_detail"];
        if($currency_type == 2){
            $this->site_rake = $this->host_rake = 0;            
        }
        if($multiple_lineup <= 0){
            $multiple_lineup = 1;
        }

        $total_site_rake = $this->site_rake + $this->host_rake;
        $total_pool = ($minimum_size * $entry_fee);
        $rake_amount = number_format((($total_pool * $total_site_rake) / 100),2,".","");
        $prize_pool = number_format(($total_pool - $rake_amount),2,".","");

        $winning_amount = array_column($prize_detail, "amount");
        $winning_amount = number_format(array_sum($winning_amount),2,".","");
        $total_per = round(array_sum(array_column($prize_detail, "per")));
        if($prize_pool != $winning_amount || $total_per != 100){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_prize_pool_error');
            $this->api_response();
        }
        $prize_type = $currency_type;//1-Real,2-Coins
        $max_total_pool = ($size * $entry_fee);
        $max_rake_amount = number_format((($max_total_pool * $total_site_rake) / 100),2,".","");
        $max_prize_pool = number_format(($max_total_pool - $max_rake_amount),2,".","");
        //echo $prize_pool."====".$max_prize_pool;die;
        foreach($prize_detail as &$prize){
            $min_amt = number_format(($prize_pool * $prize['per'] / 100),2,".","");
            $max_amt = number_format(($max_prize_pool * $prize['per'] / 100),2,".","");
            $prize['amount'] = $min_amt;
            $prize['min_value'] = $min_amt;
            $prize['max_value'] = $max_amt;
            $prize['prize_type'] = $prize_type;
        }

        $contest_unique_id = random_string('alnum', 9);
        $contest = array(
                        "contest_unique_id"        => $contest_unique_id,
                        "sports_id"                => $fixture['sports_id'],
                        "league_id"                => $fixture['league_id'],
                        "group_id"                 => $this->group_id,
                        "collection_master_id"     => $fixture['collection_master_id'],
                        "contest_name"             => $post_data['game_name'],
                        "contest_description"      => $post_data['game_desc'],
                        "season_scheduled_date"    => $fixture['season_scheduled_date'],
                        "contest_type"             => 1,
                        "salary_cap"               => $this->salary_cap,
                        "prize_distibution_detail" => json_encode($prize_detail),
                        "size"                     => $post_data['size'],
                        "minimum_size"             => $minimum_size,
                        "entry_fee"                => $entry_fee,
                        "prize_pool"               => $prize_pool,
                        "site_rake"                => $this->site_rake,
                        "host_rake"                => $this->host_rake,
                        "added_date"               => $current_date,
                        "contest_access_type"      => 1,
                        "user_id"                  => $this->user_id,
                        "multiple_lineup"          => $multiple_lineup,    
                        "is_custom_prize_pool"     => 1 ,
                        "max_bonus_allowed"        => 0 ,
                        "currency_type"            => $currency_type,
                        "prize_type"               => $prize_type,
                        "base_prize_details"       => json_encode($prize_detail)
                    );
        $tmp_game_data = $contest;
        $tmp_game_data['is_private'] = 1;
        $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];
        $current_prize = reset_contest_prize_data($tmp_game_data);
        $contest['current_prize'] = json_encode($current_prize);
        //echo "<pre>";print_r($contest);die;
        $contest_id = $this->Private_contest_model->save_user_contest($contest);
        if($contest_id) 
        {
            $this->load->model("contest/Contest_model");
            $contest = $this->Contest_model->get_contest_detail($contest_id);
            $this->api_response_arry['data'] = $contest;
            $this->api_response_arry['message'] = $this->lang->line('contest_added_success');
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('contest_added_error');
            $this->api_response();
        }       
    }
}