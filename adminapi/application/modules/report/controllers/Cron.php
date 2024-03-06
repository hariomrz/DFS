<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
	}

	/**
     * Used for generate match report
     * @param 
     * @return string
     */
	public function update_match_report_get(){
		$post_data = $_REQUEST;
		if(!isset($post_data['start_date']) || ! isset($post_data['end_date']))
		{
			$post_data['start_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 00:00:00';
			$post_data['end_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 23:59:59';
			$temp_convert_start = get_timezone(strtotime($post_data['start_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$temp_convert_end = get_timezone(strtotime($post_data['end_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$post_data['start_date'] = $temp_convert_start['date'];
			$post_data['end_date'] = $temp_convert_end['date'];
		}
        $this->benchmark->mark('code_start');
		$this->load->model('report/Cron_model');
        $result = $this->Cron_model->get_completed_matches($post_data);
       
        $report_data = array();
        foreach($result as $match){
        	$input_arr = array('contest_ids'=>$match['contest_ids'],'contest_unique_ids'=>$match['contest_unique_ids']);
        	$match_entry = $this->Cron_model->get_match_entry_fee_details($input_arr);
        	if(!empty($match_entry)){
        		$tmp_arr = array();
        		$tmp_arr['sports_id'] = $match['sports_id'];
        		$tmp_arr['league_id'] = $match['league_id'];
        		$tmp_arr['collection_master_id'] = $match['collection_master_id'];
        		$tmp_arr['match_name'] = $match['match_name'];
        		$tmp_arr['schedule_date'] = $match['schedule_date'];
        		$tmp_arr['total_user'] = $match['total_user_joined'];
        		$tmp_arr['real_user'] = $match['total_user_joined'] - $match['total_system_user'];
        		$tmp_arr['site_rake'] = $match['total_rake'];
        		$tmp_arr['site_rake_private'] = $match['private_site_rake'];
        		$tmp_arr['prize_pool'] = $match['total_pool'];
        		$tmp_arr['entry_real'] = $match_entry['entry_real'];
        		$tmp_arr['entry_bonus'] = $match_entry['entry_bonus'];
        		$tmp_arr['entry_coins'] = $match_entry['entry_coins'];
        		$tmp_arr['prize_pool_real'] = $match_entry['prize_pool_real'];
        		$tmp_arr['prize_pool_bonus'] = $match_entry['prize_pool_bonus'];
        		$tmp_arr['prize_pool_coins'] = $match_entry['prize_pool_coins'];
        		$tmp_arr['promo_discount'] = $match_entry['promo_discount'];
        		$tmp_arr['bots_entry'] = $match_entry['bots_entry'];
        		$tmp_arr['bots_winning'] = $match_entry['bots_winning'];
        		$tmp_arr['revenue'] = number_format($match_entry['entry_real'] + $match['private_site_rake'],2,".","");
        		$tmp_arr['profit'] = number_format($tmp_arr['revenue']-$tmp_arr['prize_pool_real'],2,".","");
        		$tmp_arr['modified_date'] = format_date();
        		$report_data[] = $tmp_arr;
        	}
        }
       
        if(!empty($report_data)){
        	$this->Cron_model->replace_into_batch(MATCH_REPORT,$report_data);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}

	/**
     * Used for generate contest report
     * @param 
     * @return string
     */
	public function update_contest_report_get($collection_master_id=""){
		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');
		if(!isset($from_date) || ! isset($to_date))
		{
			$post_data['start_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 00:00:00';
			$post_data['end_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 23:59:59';		
			$temp_convert_start = get_timezone(strtotime($post_data['start_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$temp_convert_end = get_timezone(strtotime($post_data['end_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$from_date = $temp_convert_start['date'];
			$to_date = $temp_convert_end['date'];
		}
        $this->benchmark->mark('code_start');
		$this->load->model('report/Cron_model');
					$result = $this->Cron_model->get_completed_contest_report($from_date,$to_date,$collection_master_id);
					$contest_ids = array_column($result, 'contest_id');
				if(!empty($result)){
				$report_data = array();
				foreach($result as $match){		
					$input_arr = array('contest_ids'=>$match['contest_id'],'contest_unique_ids'=>$match['contest_unique_id']);
					$match_entry = $this->Cron_model->get_match_entry_fees_details($input_arr);		
					// if(!empty($match_entry)){
						$tmp_arr = array();
						$tmp_arr['contest_id']         =  $match['contest_id'];
						$tmp_arr['collection_master_id']         =  $match['collection_master_id'];
						$tmp_arr['contest_unique_id']  =  $match['contest_unique_id'];
						$tmp_arr['game_type']          =  1 ;
						$tmp_arr['contest_type']       =  NULL;
						$tmp_arr['sports_id']          =  $match['sports_id'];
						$tmp_arr['league_id']          =  $match['league_id'];
						$tmp_arr['group_id']           =  $match['group_id'];
						$tmp_arr['match_name']         =  $match['collection_name'];
						$tmp_arr['contest_name']       =  $match['contest_name'];
						$tmp_arr['group_name']         =  $match['group_name'];
						$tmp_arr['schedule_date']      =  $match['season_scheduled_date'];
						$tmp_arr['minimum_size']       =  $match['minimum_size'];
						$tmp_arr['size']               =  $match['size'];
						$tmp_arr['total_user_joined']  =  $match['total_user_joined'];
						$tmp_arr['real_user']          =  $match['total_user_joined'] - $match['total_system_user'];
						$tmp_arr['bot_user']           =  $match['total_system_user'];
						$tmp_arr['entry_fee']          =  $match['entry_fee'];			
						$tmp_arr['max_bonus_allowed']  =  $match['max_bonus_allowed'];
						$tmp_arr['site_rake']          =  $match['site_rake'];
						$tmp_arr['currency_type']      =  $match['currency_type'];
						$tmp_arr['entry_real']         =  $match_entry['entry_real'];
						$tmp_arr['entry_bonus']        =  $match_entry['entry_bonus'];
						$tmp_arr['entry_coins']        =  $match_entry['entry_coins'];
						$tmp_arr['promo_entry']        =  $match_entry['promo_discount'];
						// $tmp_arr['total_entry_fee']     = $match_entry['total_entry_fee'];
						$tmp_arr['bot_entry_fee']      =  $match['bot_entry_fee'];

						$tmp_arr['system_teams']       =  $match['system_teams'];
						$tmp_arr['real_teams']         =  $match['real_teams'];

						$tmp_arr['prize_pool']         = $match['prize_pool'];
						$tmp_arr['real_prize']         = $match_entry['prize_pool_real'];
						$tmp_arr['coin_prize']         = $match_entry['prize_pool_coins'];
						$tmp_arr['bonous_prize']       = $match_entry['prize_pool_bonus'];		
						$tmp_arr['profit']             = number_format($match_entry['entry_real'] + $match['private_site_rake']-$tmp_arr['real_prize'],2,".","");				
						$tmp_arr['guaranteed_prize']   =  $match['guaranteed_prize'];
						$tmp_arr['feature_type']       =      $match['feature_type'];

						$tmp_arr['is_reverse']       =      $match['is_reverse'];
						$tmp_arr['is_2nd_inning']       =   $match['is_2nd_inning'];

						$tmp_arr['created_date']       = format_date();        		
						$report_data[] = $tmp_arr;
					// }
				}
				if(!empty($contest_ids)){
					if(!empty($report_data)){
						$contest_data_arr = array_chunk($report_data, 999);
						foreach($contest_data_arr as $c_data){
							$this->Cron_model->replace_into_batch(CONTEST_REPORT,$c_data);
						} 
						// $this->Cron_model->replace_into_batch(CONTEST_REPORT,$report_data);
					}
					$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);	
					$this->db_fantasy->where_in('contest_id',$contest_ids)->update(CONTEST,['report_generated'=>1]);
				}
			}
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}
	

	/**
   * Function used for process match tds
   * @param int $collection_master_id
   * @return boolean
   */
	public function user_match_report_get($collection_master_id='')
	{
		$this->benchmark->mark('code_start');
		$this->load->model('report/Cron_model');
		if(isset($collection_master_id) && $collection_master_id != ""){$this->Cron_model->user_match_report($collection_master_id);}
		$this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}

	public function user_old_data_update_get()
	{
		$this->benchmark->mark('code_start');
		$this->load->model('report/Cron_model');
		$this->Cron_model->push_match_report();
		$this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}

}
/* End of file Cron.php */