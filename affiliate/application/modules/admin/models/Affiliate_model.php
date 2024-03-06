<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		
	}

	public function add_affiliate($data)
	{
		if(!empty($data))
		{
			$this->db->insert(AFFILIATE,$data);
			return $this->db->insert_id();
		}
	}

	public function update_affiliate($data)
	{
		if(!empty($data))
		{
			$id = $data['affiliate_id'];
			unset($data['affiliate_id']);
			$this->db->update(AFFILIATE,$data,['affiliate_id'=>$id]);
			if($this->db->affected_rows() >=0){
				return true;
			}
			return false;
		}
		return false;
	}

	public function get_affiliates($data)
	{
		if(!empty($data))
		{
		
		$sort_field	= 'date_created';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 1;
		$csv		=false;

		if(isset($data['items_perpage']))
		{
			$limit = $data['items_perpage'];
		}

		if(isset($data['current_page']))
		{
			$page = $data['current_page'];
		}

		if(isset($data['sort_field']) && in_array($data['sort_field'],array('name','email','mobile','password','note','date_created')))
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

		if(!isset($csv) || $csv == false)
		{
			$select = "affiliate_id,name,email,mobile,password,note,date_created,date_modified,
			(CASE 
			WHEN status =2 THEN 'inactive' 
			WHEN status =1 THEN 'active' 
			ELSE 'invalid' 
			END) AS status";
		}else{
			$select = "DATE_FORMAT(date_created,'%Y-%m-%d') date_created,name,email,mobile,note,
			(CASE 
			WHEN status =2 THEN 'inactive' 
			WHEN status =1 THEN 'active' 
			ELSE 'invalid' 
			END) AS status";
		}

			$this->db->select($select)
			->from(AFFILIATE)
			->order_by($sort_field, $sort_order);

		if(isset($data['keyword']) && $data['keyword'] != "")
		{	
			$this->db->like('LOWER( CONCAT(IFNULL(name,""),IFNULL(email,""),IFNULL(mobile,"")))', strtolower($data['keyword']));
		}

		if(isset($data['status']) && in_array($data['status'],[1,2]))
		{	//echo $data['status'];exit;
			$this->db->where('status',$data['status']);
		}

		if(isset($data['from_date']) && isset($data['to_date']) && $data['from_date'] != '' && $data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(date_created,'%Y-%m-%d') >= '".format_date($data['from_date'],'Y-m-d')."' and DATE_FORMAT(date_created, '%Y-%m-%d') <= '".format_date($data['to_date'],'Y-m-d')."' ");
		}
		$tempdb = clone $this->db;
		$total = $tempdb->get()->num_rows();


		if(!isset($csv) || $csv == false)
		{
			$this->db->limit($limit,$offset);
		}
		
		$query = $this->db->get(); //->result_array();
		// echo $this->db->last_query();exit;
		
		if($total > 0)
		{
			$result = $query->result_array();
			// echo $this->db->last_query();exit;
			return [
				'result'=>$result,
				'total'=>$total
			];
		}
		return array();
		}
		return array();
	}

	public function add_campaign($data)
	{
		if(!empty($data))
		{
			$data['expiry_date'] = $data['expiry_date'].' 23:59:59';
			$this->db->insert(CAMPAIGN,$data);
			return $this->db->insert_id();
		}
	}

	public function update_table($table,$data,$where)
	{
		if(!empty($data))
		{
			if($table==CAMPAIGN)
			{
				$data['expiry_date'] = $data['expiry_date'].' 23:59:59';
			}
			$this->db->update($table,$data,$where);
			if($this->db->affected_rows() >=0){
				return true;
			}
			return false;
		}
		return false;
	}

	public function get_campaign_details($data)
	{
		if(!empty($data))
		{
			
			$csv = FALSE;
			$sort_field	= 'date_created';
			$sort_order	= 'DESC';

			if(isset($data['sort_field']) && in_array($data['sort_field'],array('name','email','mobile','password','note','date_created')))
			{
				$sort_field = $data['sort_field'];
			}

			if(isset($data['sort_order']) && in_array($data['sort_order'],array('DESC','ASC')))
			{
				$sort_order = $data['sort_order'];
			}

			if(isset($data['csv']) && $data['csv']==true)
			{
				$csv = $data['csv'];
			}
			
			if(!isset($csv) || $csv == false)
			{
				$select = "C.campaign_id,C.affiliate_id,C.campaign_code,C.name,C.source,C.medium,C.url website_url,C.expiry_date,C.commission,C.date_created,C.modified_date,
				 C.status,C.is_unpublished";
			}else{
				$signup 		= "JSON_EXTRACT(C.commission, '$.signup')";
				$deposit 		= "JSON_EXTRACT(C.commission, '$.deposit_per')";
				$deposit_cap 	= "JSON_EXTRACT(C.commission, '$.deposit_cap')";
				$game 			= "JSON_EXTRACT(C.commission, '$.game_per')";
				// $game_cap 		= "JSON_EXTRACT(C.commission, '$.deposit_cap')";

				$select = "DATE_FORMAT(C.date_created,'%Y-%m-%d') date_created,C.campaign_code,C.name,C.source,C.medium,C.url website_url,C.expiry_date,C.modified_date,
				($signup*1) register_comm,($deposit*1) deposit_comm,($deposit_cap*1) deposit_cap,($game*1) game_comm,
				(CASE 
				WHEN C.status =2 THEN 'Unpublished' 
				WHEN C.status =1 THEN 'Published' 
				ELSE 'invalid' 
				END) AS status";
			}

			$this->db->select($select)
			->from(CAMPAIGN.' C')
			->join(CAMPAIGN_USERS.' CU','C.campaign_id=CU.campaign_id','LEFT')
			->where('status!=',3) //exclud deleted
			->where('C.affiliate_id',$data['affiliate_id'])
			->order_by($sort_field, $sort_order)
			->group_by('C.campaign_id');

			if(isset($data['keyword']) && $data['keyword'] != "")
			{	
				$this->db->like('LOWER( CONCAT(IFNULL(C.name,""),IFNULL(C.source,""),IFNULL(C.medium,"")))', strtolower($data['keyword']));
			}

			if(isset($data['from_date']) && $data['from_date'] != '' && isset($data['to_date']) && $data['to_date'] != '')
			{
				$this->db->where("DATE_FORMAT(C.date_created,'%Y-%m-%d') >= '".format_date($data['from_date'],'Y-m-d')."' and DATE_FORMAT(C.date_created, '%Y-%m-%d') <= '".format_date($data['to_date'],'Y-m-d')."' ");
			}

			$tempdb = clone $this->db;
			$query = $this->db->get(); //->result_array();
			// echo $this->db->last_query();exit;
			$total = $tempdb->get()->num_rows();
			
			$aff_data = $this->get_aff_details($data['affiliate_id'],1);
			$aff_data['grand_commission']=0;

			if($total > 0)
			{
				$result = $query->result_array();
				// print_r($result);exit;
				if(isset($result[0]['campaign_id']) && $result[0]['campaign_id']!='')
				{
					$campaign_arr = array_unique(array_column($result,'campaign_id'));
					$grand_commission = $this->db->select('SUM(commission) grand_commission')
					->from(CAMPAIGN_HISTORY)
					->where('status',1)
					->where_in('campaign_id',$campaign_arr)
					->get()->row_array();
					$aff_data['grand_commission'] = $grand_commission['grand_commission'] ? $grand_commission['grand_commission'] : 0;
				}
				foreach($result as $key=>$res)
				{
					if(!(strpos($res['website_url'],'#')))
					{
						if(!(strpos($res['website_url'],'?')))
						{
						$result[$key]['url'] = $res['website_url'].'?cp='.$res['campaign_code'];
						}else
						{
						$result[$key]['url'] = $res['website_url'].'&cp='.$res['campaign_code'];
						}
					}else{
						if(!(strpos($res['website_url'],'?')))
						{
							$broken_str = explode('#',$res['website_url'],2);
							$result[$key]['url'] = $broken_str[0].'?cp='.$res['campaign_code'].'#'.$broken_str[1];
						}
						else{
							$broken_str = explode('#',$res['website_url'],2);
							$result[$key]['url'] = $broken_str[0].'&cp='.$res['campaign_code'].'#'.$broken_str[1];	
						}
					}

					if(!isset($csv) || $csv == false)
					{
						$result[$key]['total'] = $this->db->select('count(visit_code) as visit')->from(VISIT)->where(['campaign_id'=>$res['campaign_id'],"user_id!="=>0])
						->get()->row_array();
						$result[$key]['total'] =  $result[$key]['total']['visit'];
					}
					
				}
				// echo $this->db->last_query();exit;
				$total_users = array_sum(array_column($result,'total'));
				return ['result'=>$result,"total_url"=>$total,"total_users"=>$total_users,"aff"=>$aff_data];
			}
			return ['result'=>[],"total_url"=>0,"total_users"=>0,"aff"=>$aff_data];
		}
		return ['result'=>[],"total_url"=>0,"total_users"=>0];

	}

	public function track_single_url($data)
	{	
		if($data)
		{
			$sort_field	= 'CU.campaign_user_id';
			$sort_order	= 'DESC';
			$limit		= 10;
			$page		= 1;
			$csv		= false;

			if(isset($data['items_perpage']))
			{
				$limit = $data['items_perpage'];
			}

			if(isset($data['current_page']))
			{
				$page = $data['current_page'];
			}

			// if(isset($data['sort_field']) && in_array($data['sort_field'],array('name','mobile')))
			// {
			// 	$sort_field = $data['sort_field'];
			// }
			$offset = get_pagination_offset($page, $limit);

			// if(isset($data['sort_order']) && in_array($data['sort_order'],array('DESC','ASC')))
			// {
			// 	$sort_order = $data['sort_order'];
			// }

			if(isset($data['csv']) && $data['csv']==true)
			{
				$csv = $data['csv'];
			}

			$this->db->select("CU.user_id,CU.name,CU.mobile,CH.date_created,
			SUM(CASE WHEN type=2 THEN 1 ELSE 0 END) as total_deposit,
			SUM(CASE WHEN type=2 THEN CH.amount ELSE 0 END) as deposit_amount,
			SUM(CASE WHEN type=3 THEN 1 ELSE 0 END) as contest_played,CU.is_expired,
			ROUND(SUM(commission),2) as total_commission,IF(CU.is_expired=1,'Inactive','Active') status")
			->from(CAMPAIGN_USERS.' CU')
			->join(CAMPAIGN_HISTORY.' CH','CU.user_id = CH.user_id AND CU.campaign_id = CH.campaign_id','INNER')
			->where('CH.status',1)
			->where('CU.campaign_id',$data['campaign_id'])
			->group_by('CU.user_id')
			->order_by('CU.campaign_user_id','DESC');

			if($this->auth_key_role==2)
			{
				//for affiliate user , expired transaction will not be visible
				$this->db->where('CH.is_expired',0);
				$this->db->where('CU.is_expired',0);
			}

			if(isset($data['keyword']) && $data['keyword'] != "")
			{	
				$this->db->like('LOWER( CONCAT(IFNULL(CU.name,"")))', strtolower($data['keyword']));
			}

			if(isset($data['from_date']) && $data['from_date'] != '' && isset($data['to_date']) && $data['to_date'] != '')
			{
				// $this->db->where("DATE_FORMAT(CH.date_created,'%Y-%m-%d %H %i %s') >= '".format_date($data['date'],'Y-m-d')." 00:00:00' and DATE_FORMAT(CH.date_created, '%Y-%m-%d %H %i %s') <= '".format_date($data['date'],'Y-m-d')." 59:59:59' ");
				$this->db->where("DATE_FORMAT(CH.date_created,'%Y-%m-%d') >= '".format_date($data['from_date'],'Y-m-d')."' and DATE_FORMAT(CH.date_created, '%Y-%m-%d') <= '".format_date($data['to_date'],'Y-m-d')."' ");
			}

			if(isset($data['type']) && $data['type'] != '')
			{
				$this->db->where('CH.type',$data['type']);
			}

			$tempdb = clone $this->db;
			$totaldb = clone $this->db;
			
			$total = $tempdb->get()->num_rows();


			if(!isset($csv) || $csv == false)
			{
				$this->db->limit($limit,$offset);
			}
			
			$query = $this->db->get(); //->result_array();
			// echo $this->db->last_query();exit;
			
			if($total > 0)
			{
				$result = $query->result_array();
				$block_total_values = $totaldb->get()->result_array();
			}else{
				$result = [];
			}

				$block_values = $this->db->select("COUNT(DISTINCT CH.user_id) as total_depositors")
						->from(CAMPAIGN_HISTORY.' CH')
						->where('CH.campaign_id',$data['campaign_id'])
						->where('CH.type',2);
						if($this->auth_key_role==2)
						{
							$this->db->where('CH.is_expired',0);
						}
						$block_values = $this->db->get()->row_array();
						// echo $this->db->last_query();exit;

				$aff_data = $this->get_aff_details($data['campaign_id'],2);
				$visit_where = ["campaign_id"=>$data['campaign_id']];
				if($this->auth_key_role==2)
				{
					$visit_where = ["campaign_id"=>$data['campaign_id'],"is_expired"=>0];
				}
				$visit_count = $this->Affiliate_model->get_single_row('COUNT(visit_code) visit',VISIT,$visit_where);
				// echo $this->db->last_query();exit;

				$total_contest_played = array_sum(array_column($block_total_values,'contest_played'));
				$grand_commission = number_format(array_sum(array_column($block_total_values,'total_commission')),2,".","");
				$grand_amount = array_sum(array_column($block_total_values,'deposit_amount'));
				$block_values['total_contest_played'] 	= $total_contest_played ? $total_contest_played : 0;
				$block_values['grand_commission'] 	= $grand_commission ? $grand_commission : 0;
				$block_values['grand_amount'] 	= $grand_amount ? $grand_amount : 0;
				$block_values['registrations']			= $total ? $total : 0;
				$block_values['visit']			= $visit_count['visit'] ? $visit_count['visit']:0;

				return [
					'result'=>$result,
					'block_values'=>array_merge($block_values,$aff_data),
				];
			//}
			// return array();
		}
		return false;
	}

	public function get_single_user_details($data)
	{
	    $sort_field	= 'CH.history_id';
		$sort_order	= 'DESC';
		$limit		= 10;
		$page		= 1;
		$csv		= false;

		if(isset($data['items_perpage']))
		{
			$limit = $data['items_perpage'];
		}

		if(isset($data['current_page']))
		{
			$page = $data['current_page'];
		}

		if(isset($data['sort_field']) && in_array($data['sort_field'],array('name','mobile')))
		{
			$sort_field = $data['sort_field'];
		}
		$offset = get_pagination_offset($page, $limit);

		if(isset($data['sort_order']) && in_array($data['sort_order'],array('DESC','ASC')))
		{
			$sort_order = $data['sort_order'];
		}				

		// if(isset($data['date']) && $data['date'] != '')
		// {
		if(isset($data['from_date']) && $data['from_date'] != '' && isset($data['to_date']) && $data['to_date'] != '')
		{
			$this->db->where("DATE_FORMAT(CH.date_created,'%Y-%m-%d') >= '".format_date($data['from_date'],'Y-m-d')."' and DATE_FORMAT(CH.date_created, '%Y-%m-%d') <= '".format_date($data['to_date'],'Y-m-d')."' ");
		}

		if(isset($data['type']) && $data['type'] != '')
		{

			$this->db->where('CH.type',$data['type']);
		}


		if(isset($data['csv']) && $data['csv']==true)
		{
			$csv = $data['csv'];
		}

		// ->order_by('CH.date_created', 'DESC')

		$result = $this->db->select("CH.history_id,CH.date_created,
		(CASE WHEN CH.type=1 THEN 'Register' WHEN CH.type=2 THEN 'Deposit' WHEN CH.type=3 THEN 'Contest Joined' END) AS activity,
		CH.amount,ROUND(CH.commission, 2) as commission,CH.is_expired,IFNULL(JSON_UNQUOTE(JSON_EXTRACT(CH.event_data, '$.currency_type')),1) currency_type,IF(CH.is_expired=1,'Expired','Active') status")
		->from(CAMPAIGN_HISTORY.' CH')
		->join(CAMPAIGN_USERS.' CU','CU.user_id = CH.user_id AND CU.campaign_id = CH.campaign_id','INNER')
		->where('CH.status',1)
		->where('CH.user_id',$data['user_id'])
		->order_by('CH.history_id', 'DESC');
		
		if($this->auth_key_role==2)
		{
			//for affiliate user , expired transaction will not be visible
			$this->db->where('CH.is_expired',0);
			$this->db->where('CU.is_expired',0);
		}

		$tempdb = clone $this->db;
		$total = $tempdb->get()->num_rows();			

		if(!isset($csv) || $csv == false)
		{
			$this->db->limit($limit,$offset);
		}

		$query = $this->db->get(); //->result_array();		
		// echo $this->db->last_query();exit;
		
		if($total > 0)
		{
			$result = $query->result_array();
			// echo $this->db->last_query();exit;
		}else{
			$result = [];
		}
			$block_values = $this->db->select("CU.name user_name,SUM(commission) as grand_commission,SUM(IF(CH.type=2,CH.amount,0)) as grand_amount ,SUM(CH.type=3) as total_contest_played,CH.campaign_id")
				->from(CAMPAIGN_HISTORY.' CH')
				->join(CAMPAIGN_USERS.' CU','CU.user_id = CH.user_id AND CU.campaign_id = CH.campaign_id','INNER')
				->where('CH.status',1)
				// ->where('CH.type!=','1')
				->where('CH.user_id',$data['user_id']);
				if($this->auth_key_role==2)
				{
					//for affiliate user , expired transaction will not be visible
					$this->db->where('CH.is_expired',0);
					$this->db->where('CU.is_expired',0);
				}						
				$block_values = $this->db->get()->row_array();

				$aff_data = $this->get_aff_details($block_values['campaign_id'],2);

				if(isset($data['csv']) && $data['csv']==true)
				{
					foreach($result as $key=>$res)
					{
						unset($result[$key]['is_expired'],$result[$key]['currency_type']);
						switch(strtolower($res['activity']))
						{
							case 'register':
								$result[$key]['deposit_amount'] = '-';
								$result[$key]['commission'] = $result[$key]['amount'];
								$result[$key]['entry_fee'] = '-';
								unset($result[$key]['amount']);
							break;
							case 'deposit':
								$result[$key]['deposit_amount'] = $result[$key]['amount'];
								$result[$key]['entry_fee'] = '-';
								unset($result[$key]['amount']);
							break;
							case 'Contest Joined':
								$result[$key]['deposit_amount'] = '-';
								$result[$key]['entry_fee'] = $result[$key]['amount'];
								unset($result[$key]['amount']);
							break;
							default:
								$result[$key]['deposit_amount'] = '-';
								$result[$key]['entry_fee'] = $result[$key]['amount'];
								unset($result[$key]['amount']);
							break;
						}
					}
				}
			return [
				'result'=>$result,					
				'block_values'=>array_merge($block_values,$aff_data),
				'total'=>$total ? $total : 0,
			];
		return array();
	}

}
/**
 * Affiliate admin model 
 * path : affiliate/application/modules/admin/models
 */
?>