<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		//Do your magic here
		// $this->admin_id = $this->session->userdata('admin_id');
	}

	public function chnage_password($data_arr)
	{
		$this->db->where('password',md5($data_arr['old_password']))
				->where('admin_id',$this->admin_id)
				->update(ADMIN,array('password'=>md5($data_arr['new_password'])));
		// echo $this->db->last_query();die;
		return $this->db->affected_rows();
	}

	public function delete_all_active_login_admin($role = "2") 
	{
		$this->db->where('role', $role)->delete(ACTIVE_LOGIN);
	}

	/*OLD FUNCTION TO GET AFFILATE MASTER DATA */
	/*public function affiliate_master_data()
	{
		$sql = $this->db->select("affiliate_type,amount_type,affiliate_description,invest_money,bonus_amount,real_amount,user_bonus")
						->from(AFFILIATE_MASTER)
						->where('amount_type',1)
						->get();
		$result = $sql->result_array();
		return $result;

	}*/

	/*NEW FUNCTION TO GET AFFILATE MASTER DATA */
	public function affiliate_master_data()
	{
		$INT_VERSION = INT_VERSION;
		$sql = $this->db->select("affiliate_master_id,affiliate_type,amount_type,(CASE WHEN $INT_VERSION =1 && affiliate_description = 'Verify PAN w/o referral' THEN 'Verify ID Card w/o referral' WHEN $INT_VERSION =1 && affiliate_description='PAN verification with referral' THEN 'ID Card verification with referral' ELSE affiliate_description END) as affiliate_description,bonus_amount,real_amount,coin_amount,user_bonus,user_real,user_coin,is_referral,max_earning_amount,invest_money")
						->from(AFFILIATE_MASTER)
						//->where('amount_type',1)
						->where('status',1)
						->order_by('order',"ASC")
						->order_by('affiliate_master_id',"ASC")
						->get();
		$result = $sql->result_array();
		return $result;

	}

	public function update_affiliate_amount($data)
	{
		$this->db->update_batch(AFFILIATE_MASTER, $data,'affiliate_type');
		return true;
	}

	/*
	 * [update_referral_amount description]
	 * Summary :- update referral_fund data 
	 * @param  [array] $data_arr [referral_amount,invest_money]
	 * @return [type]           [description]
	 */
	public function update_referral_amount($data_arr)
	{
		$this->db->where('referral_fund_id',1)
				->update(REFERRAL_FUND,array('referral_amount'=>$data_arr['referral_amount'], 'invest_money'=>$data_arr['invest_money'],'last_update_date'=>date("Y-m-d H:i:s")));
		// echo $this->db->last_query();die;
		return $this->db->affected_rows();
	}

	/**
	 * [get_bonus_current_amount description]
	 * Summary :- get bonus_fund data 
	 * @return [array] [result]
	 */
	public function get_bonus_current_amount()
	{
		$sql = $this->db->select("bonus_money")
						->from(BONUS_FUND)
						->where('bonus_fund_id',1)
						->get();
		$result = $sql->row_array();
		return $result;
	}

	/**
	 * [update_bonus_amount description]
	 * Summary :- update bonus_fund data 
	 * @param  [array] $data_arr [bonus_amount,invest_money]
	 * @return [type]           [description]
	 */
	public function update_bonus_amount($data_arr)
	{
		$this->db->where('bonus_fund_id',1)
				->update(BONUS_FUND,array('bonus_money'=>$data_arr['bonus_money'],'last_update_date'=>date("Y-m-d H:i:s")));
		// echo $this->db->last_query();die;
		return $this->db->affected_rows();
	}

	public function get_payment_setting()
	{
		$sql = $this->db->select("payment_gateway_setting_id as payment_method")
						->from(PAYMENT_GATEWAY_SETTING)
						->where('is_active','1')
						->get();
		$result = $sql->row_array();
		return $result;
	}

	public function update_payment_setting($data_arr)
	{
		$this->db->update(PAYMENT_GATEWAY_SETTING,array('is_active'=>'0'));

		$this->db->where('payment_gateway_setting_id',$data_arr['payment_method'])
				->update(PAYMENT_GATEWAY_SETTING,array('is_active'=>'1'));
		// echo $this->db->last_query();die;
		return $this->db->affected_rows();
	}



	public function update_sports_hub($data_arr,$game_key)
	{
		$this->db->where('game_key',$game_key)
				->update(SPORTS_HUB,$data_arr);
		// echo $this->db->last_query();die;
		return $this->db->affected_rows();
	}

	public function get_payment_config(){
		$sql = $this->db->select("payment_config_id,meta_key,meta_value")
						->from(PAYMENT_CONFIG)
						->where('meta_key !=','')
						->get();
		$result = $sql->result_array();
		return $result;
	}

	public function update_payment_config($config_array){

		foreach ($config_array as $key => $value) {
			$this->db->where($value['where_array'])
				->update(PAYMENT_CONFIG,$value['update_array']);
		}


	}
/**
 * update banner image data in app_config
 */
public function update_app_config_data($update_data,$key_name){
	$where = array(
		'key_name'=>$key_name
	);
	$check_exist = $this->get_single_row('*',APP_CONFIG,array('key_name' => $key_name));
	if(empty($check_exist)){
		$data_arr = array("name"=>ucfirst(str_replace("_"," ",$key_name)),"key_name"=>$key_name,"key_value"=>$update_data['key_value']);
		$this->db->insert(APP_CONFIG,$data_arr);
	}else{
		$result = $this->db->update(APP_CONFIG,$update_data,$where);
	}
	return true;
}

/**
 * getting image data from app_config
 */
public function get_app_config_data($data_array=array()){
	if(!empty($data_array)){
		$image = "JSON_UNQUOTE(JSON_EXTRACT(AC.custom_data, '$.image'))";
		
		$result = $this->db->select("IFNULL($image,'') as image,key_name,name,key_value,custom_data")
		->from(APP_CONFIG.' AS AC')
		->where_in("key_name",$data_array)
		->get()->result_array();
		// echo $this->db->last_query();exit;
		return $result;
	}
		return false;
}

/**
 * 
 */
public function toggle_banner_image_status(){
	$post_data = $this->input->post();
	$status = ['key_value'=>$post_data['status']];
	$where = ["key_name"=>$post_data['key_name']];
	$this->db->update(APP_CONFIG,$status,$where);
	return true;
}

/**
 * 
 */
public function update_app_config_item_status(){
	$post_data = $this->input->post();
	$status = ['key_value'=>$post_data['status']];
	$where = ["key_name"=>$post_data['key_name']];
	$this->db->update(APP_CONFIG,$status,$where);
	return true;
}

/**
 * 
 */
public function update_hub_icon($image,$key_name="allow_hub_icon"){
	$custom_data = json_encode(array('image' => $image));
	$update_data = array('custom_data' => $custom_data);
	$where = ["key_name"=>$key_name];
	$this->db->update(APP_CONFIG,$update_data,$where);
	return true;
}

	/**
	 * getting image data from app_config
	 */
	public function get_hub_icon_banner($banner_array = array()){

		if(empty($banner_array))
		{
			$banner_array = array(
				"allow_hub_icon",
				"allow_hub_banner"
				
			);
		}
			$image = "JSON_UNQUOTE(JSON_EXTRACT(AC.custom_data, '$.image'))";
			
			$result = $this->db->select("IFNULL($image,'') as image,key_name,key_value,name")
			->from(APP_CONFIG.' AS AC')
			->where_in("key_name",$banner_array)
			->get()->result_array();
			return $result;
	}

	public function get_sport_hub_item($key_name)
	{
		return $this->db->select("sports_hub_id,game_key,image
		")
        ->from(SPORTS_HUB)
        ->where('status',1)
        ->where('game_key',$key_name)
        ->get()->row_array();
	}

	/**
	 * to get content from common content table
	 */
	public function get_content(){
		$post_data = $this->input->post();
		
		$result = $this->db->select("content_key,IFNULL({$post_data['language']}_header ,en_header) AS {$post_data['language']}_header,IFNULL({$post_data['language']}_body ,en_body) AS {$post_data['language']}_body")
        ->from(COMMON_CONTENT)
		->where('status',1);
		IF(isset($post_data['content_key']) && $post_data['content_key']!= null){
			$this->db->where('content_key',$post_data['content_key']);
		}
		$result = $this->db->get()->row_array();
		// echo $this->db->last_query();exit;
		
		return $result;
	}
	/**
	 * update content
	 */

	public function update_content(){
		$post_data = $this->input->post();
		$where = ["content_key"=>$post_data['content_key']];
		unset($post_data['content_key']);
		$this->db->update(COMMON_CONTENT,$post_data,$where);
		return true;
		// echo $this->db->last_query();exit;
	}

	/**
	 * update new sports order to sports hub table 
	 */

	 public function update_sports_hub_order($data){
		$old_data = $this->db->select('game_key,allowed_sports')
		->get(SPORTS_HUB)->result_array();
		
		$new_data = array_column($data,'sports_id');
		// print_r($new_data);exit;
		foreach($old_data as $key=>$old){
			$allowed_sports = json_decode($old['allowed_sports']);
			$sorted_array = array();
			if(!empty($allowed_sports)){

				foreach($new_data as $new)
				{
					if(in_array($new,$allowed_sports)){
						$sorted_array[] = $new;
					}
				}
				$sorted_array = json_encode($sorted_array);
				$updated = $this->db->update(SPORTS_HUB,["allowed_sports"=>$sorted_array],["game_key"=>$old['game_key']]);
			}
			// echo $this->db->last_query();exit;
		}

		return true;
	 }

	 public function get_active_payment_gateway(){
		 $sql = $this->db->select('MP.*', FALSE)
          ->from(MASTER_PG.' as MP')
		  ->join(APP_CONFIG." AS AC","AC.key_name=MP.pg_key","INNER")
		 ->where("AC.key_value",1);
		//  ->where_in("AC.key_name",['allow_payumoney','allow_paytm','allow_mpesa','allow_ipay','allow_paypal','allow_paystack','allow_razorpay','allow_stripe','allow_vpay','allow_ifantasy','allow_crypto','allow_cashierpay','allow_cashfree','allow_paylogic','allow_btcpay','allow_directpay','allow_phonepe','allow_juspay']);  
         $query = $this->db->get();    
      return $result = $query->result_array();

		
	 }



	public function update_paymentgatway_detail($post_data){
              $update_data = array();
				$update_data['title'] = $post_data['title'];
				$update_data['description'] = $post_data['description'];
				$update_data['image_name'] = $post_data['image'];		
		$where = ["pg_id" => $post_data["pg_id"]];
		$this->db->update(MASTER_PG,$update_data,$where);
		return true;

	}

}	
/* End of file Teamroster_model.php */
/* Location: ./application/models/Teamroster_model.php */