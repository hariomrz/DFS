<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Common_Api_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('admin/Users_model', 'Admin_user');
	

		//$_POST = $this->post();
	}


	public function user_game_history_post() {
		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}

		$data = $this->Admin_user->contest_list_by_user_id();
				
		foreach ($data['result'] as $key => $value) {
			$winnings = $this->Admin_user->get_contest_winning_amount($value['lineup_master_contest_ids'],$value['user_id']);
			$data['result'][$key]['winning_amount'] = $winnings['winning_amount'] ?$winnings['winning_amount'] : 0;
			$data['result'][$key]['winning_bonus'] = $winnings['winning_bonus'] ? $winnings['winning_bonus'] : 0;
			$data['result'][$key]['winning_coin'] = $winnings['winning_coin'] ? $winnings['winning_coin'] : 0;
			$data['result'][$key]['prize_data'] = json_decode($value['prize_data'],TRUE);

			unset($data['result'][$key]['lineup_master_contest_ids']);
		}
		
		$this->api_response_arry['data']  = $data;
		$this->api_response();
	}



	public function user_game_history_export_get() {

		$result = array();
		$is_user_history = 1;		
		$data = $this->Admin_user->get_contest_list_by_user_id($is_user_history);
					
		foreach ($data['result'] as $key => $value) {

			$winnings = $this->Admin_user->get_contest_winning_amount($value['lineup_master_contest_ids'],$value['user_id']);
			$data['result'][$key]['winning_amount'] = $winnings['winning_amount'] ?$winnings['winning_amount'] : 0;
			$data['result'][$key]['winning_bonus'] = $winnings['winning_bonus'] ? $winnings['winning_bonus'] : 0;
			$data['result'][$key]['winning_coin'] = $winnings['winning_coin'] ? $winnings['winning_coin'] : 0;
			$data['result'][$key]['merchandise'] = '';
			if(isset($value['prize_data']) && !empty($value['prize_data'])){
				$prize_data = json_decode($value['prize_data'],TRUE);
				foreach($prize_data as $prizes) {
					if($prizes['prize_type']=='3') {
						$data['result'][$key]['merchandise'] = $prizes['name'];
					}
					if($prizes['prize_type']=='2') {
						$data['result'][$key]['winning_coin'] = $prizes['amount'];
					}
				}
			}
			
			//$data['result'][$key]['stock_type_text'] = $this->stock_type_map[$value['stock_type']];
			
			unset($data['result'][$key]['stock_type']);
			unset($data['result'][$key]['user_id']);
			unset($data['result'][$key]['lineup_master_contest_ids']);
		}
		
		if(!empty($data['result'])){
			$header = array_keys($data['result'][0]);
			$result = array_merge(array($header),$data['result']);
			$this->load->helper('csv');
			array_to_csv($result,'User_stock_game_history_list.csv');
		} else {
			$result = "no record found";
			$this->load->helper('download');
			$this->load->helper('csv');
			$data = array_to_csv($result);
			$name = 'User_stock_game_history_list.csv';
			force_download($name, $result);
		}

	}
}
/* End of file User.php */
/* Location: ./application/controllers/User.php */