<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Profile_model extends MY_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	public function get_affiliate_profile($post_data)
	{
		
		$this->db->select('A.affiliate_id as admin_id,A.name,A.email,A.mobile,A.note,SUM(CH.commission) earning')
		->from(AFFILIATE.' A')
		->join(CAMPAIGN.' C','A.affiliate_id=C.affiliate_id','LEFT')
		->join(CAMPAIGN_HISTORY.' CH','C.campaign_id=CH.campaign_id and CH.is_expired=0','LEFT')
		->where("A.affiliate_id",$this->admin_id)
		->group_by('A.affiliate_id');

		if(isset($post_data['campaign_id']) && $post_data['campaign_id']!="")
		{
			$this->db->where('CH.campaign_id',$post_data['campaign_id']);	
		}

		$result = $this->db
		->get()
		->row_array();
		// echo $this->db->last_query();exit;
		return $result;
	}

	public function get_campaign_details($data)
	{
		// echo $this->auth_key_role;exit;
		$sort_field	= 'date_created';
		$sort_order	= 'ASC';
		$limit		= 10;
		$page		= 0;
		$csv		=false;

		if(isset($data['items_perpage']))
		{
			$limit = $data['items_perpage'];
		}

		if(isset($data['current_page']) && $data['current_page']>1)
		{
			$page = $data['current_page'];
		}

		if(isset($data['sort_field']) && in_array($data['sort_field'],array("date_created","source","medium","name")))
		{
			$sort_field = $data['sort_field'];
		}
		
		$offset = get_pagination_offset($page, $limit);

		if(isset($data['sort_order']) && in_array($data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $data['sort_order'];
		}

		if(isset($data['csv']) && $data['csv']==true)
		{
			$csv = $data['csv'];
		}

		$signup_commission 		= "JSON_EXTRACT(C.commission, '$.signup')";
		$deposit_commission 	= "JSON_EXTRACT(C.commission, '$.deposit_per')";
		$deposit_cap 			= "JSON_EXTRACT(C.commission, '$.deposit_cap')";
		$game_commission 		= "JSON_EXTRACT(C.commission, '$.game_per')";

		if(!isset($csv) || $csv == false)
		{
			$select = "C.campaign_id,C.campaign_code,COUNT(CU.user_id) registrations,C.date_created,C.source,C.medium,C.name,C.url,IFNULL($signup_commission,0) signup,IFNULL($deposit_commission,0) deposit,IFNULL($game_commission,0) game,C.commission,C.expiry_date,C.status";
		}else{
			$select = "DATE_FORMAT(C.date_created,'%Y-%m-%d') date_created,C.campaign_code,C.name,C.source,C.medium,C.url,C.expiry_date,
			IFNULL($signup_commission,0) signup,IFNULL($deposit_commission,0) deposit,IFNULL($game_commission,0) game,$deposit_cap deposit_cap,
			(CASE 
			WHEN C.status =2 THEN 'Unpublished' 
			WHEN C.status =1 THEN 'Published' 
			WHEN C.status =4 THEN 'Inactive' 
			ELSE 'invalid' 
			END) AS status";
		}

		$this->db->select($select)
		->from(CAMPAIGN.' C')
		->join(CAMPAIGN_USERS.' CU', 'C.campaign_id = CU.campaign_id and CU.is_expired=0','LEFT')
		// ->join(VISIT.' V', 'V.campaign_id = C.campaign_id','LEFT')
		->where('affiliate_id',$this->admin_id)
		->where("is_unpublished",0)
		->where_in('C.status',[1,4])
		->order_by($sort_field, $sort_order)
		->group_by('C.campaign_id');

		if(isset($data['keyword']) && $data['keyword'] != "")
		{	
			$this->db->like('LOWER( CONCAT(IFNULL(C.name,"")))', strtolower($data['keyword']));
		}

		if(isset($data['from_date']) && $data['from_date'] != '' && isset($data['to_date']) && $data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(C.date_created,'%Y-%m-%d') >= '".format_date($data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.date_created, '%Y-%m-%d') <= '".format_date($data['to_date'],'Y-m-d')."' ");
		}

		$tempdb = clone $this->db;
		$tempdb2 = clone $this->db;
		$total = $tempdb->get()->num_rows();
		$registrations = $tempdb2->get()->result_array();
		$registrations = array_sum(array_column($registrations,'registrations'));

		if(!isset($csv) || $csv == false)
		{
			$this->db->limit($limit,$offset);
		}

		$query = $this->db->get(); //->result_array();
		// echo $this->db->last_query();exit;
		if($total > 0)
		{
			$result = $query->result_array();

			foreach($result as $key=>$res)
			{
				if(!(strpos($res['url'],'#')))
				{
					if(!(strpos($res['url'],'?')))
					{
					$result[$key]['camapign_url'] = $res['url'].'?cp='.$result[$key]['campaign_code'];
					}else
					{
						$result[$key]['camapign_url'] = $res['url'].'&cp='.$result[$key]['campaign_code'];
					}
				}else{
					if(!(strpos($res['url'],'?')))
					{
						$broken_str = explode('#',$res['url'],2);
						$result[$key]['camapign_url'] = $broken_str[0].'?cp='.$result[$key]['campaign_code'].'#'.$broken_str[1];
					}
					else{
						$broken_str = explode('#',$res['url'],2);
						$result[$key]['camapign_url'] = $broken_str[0].'&cp='.$result[$key]['campaign_code'].'#'.$broken_str[1];	
					}
				}
					$result[$key]['signup'] = str_replace('"','',$res['signup']);
					$result[$key]['deposit'] = str_replace('"','',$res['deposit']);
					$result[$key]['game'] = str_replace('"','',$res['game']);
					$result[$key]['deposit_cap'] = str_replace('"','',$res['deposit_cap']);

			}

			$block_values = $this->db->select("SUM(CH.commission) as grand_commission")
			->from(CAMPAIGN_HISTORY.' CH')
			->join(CAMPAIGN.' C','C.campaign_id = CH.campaign_id','INNER')
			->join(AFFILIATE.' A','A.affiliate_id = C.affiliate_id','INNER')
			->where('CH.status',1)
			->where('A.affiliate_id',$this->admin_id)
			->where('CH.is_expired',0)
			->get()->row_array();

			// $get_total_registrations = $this->db->select()
			// echo $this->db->last_query();exit;
			return [
				'result'=>$result,
				'campaign_url'=>$total,
				'registrations'=>$registrations,
				'grand_commission'=>$block_values['grand_commission'] ? $block_values['grand_commission'] : 0,
			];
		}
		return array();

	}

}