<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db_fantasy		= $this->load->database('db_fantasy', TRUE);
	}

	/**
	 * [get_all_number_of_winner description]
	 * @MethodName get_all_number_of_winner
	 * @Summary This function used for get all prizing list and prize validation
	 * @param [int] $league_id
	 * @return     [array]
	 */
	public function get_all_number_of_winner()
	{
		$sql = $this->db_fantasy->select('MNOW.master_contest_type_id,master_contest_type_id as league_contest_type_id,master_contest_type_desc,position_or_percentage,places')
						->from(MASTER_CONTEST_TYPE . " AS MNOW")
						->where('MNOW.status', '1')
						->order_by('MNOW.order', 'ASC')
						->get();
		$result = $sql->result_array();
		
		$all_number_of_winner = array();
		foreach($result as $rs)
		{

			$all_number_of_winner[] = array(
										"master_contest_type_desc"=>$rs['master_contest_type_desc'],
										"league_contest_type_id"=>$rs['master_contest_type_id']
										);
			

		}
		
		
		return array('all_number_of_winner' => $all_number_of_winner, 'number_of_winner_validation' => $result);
	}

	public function prize_details_by_size_fee_prizing($data,$prize_pool = '0',$desc=FALSE)
	{
		
		$site_rake				= $data['site_rake'];				
		$size					= $data['size'];						
		$fee					= ($data['entry_fee']) ? $data['entry_fee']	: 0;			
		$prize_selection		= $data['prize_selection'];
		$league_contest_type_id	= isset($data['league_number_of_winner_id'])?$data['league_number_of_winner_id']:'';

		$collected_amount	= $size * $fee;
		$site_rake_amount	= ($collected_amount * $site_rake)/100; 
		$prize_pool_amount	= $collected_amount - $site_rake_amount;
		
		$prize_place = 0;
		if($prize_pool != '0')
		{
			$prize_pool_amount = $prize_pool;
		}

		$prize_details = '';
		$prize_place = '';
		
		if($prize_selection == 'auto'){
				$sql = $this->db->select("MNOW.master_contest_type_id,MNOW.master_contest_type_desc,MNOW.places")
								->from(MASTER_CONTEST_TYPE." AS MNOW")
								->where("MNOW.master_contest_type_id",$league_contest_type_id)
								->get();
				
				$result			= $sql->row_array();
				$file			= prize_json();
				$winner_array	= json_decode($file,true);
				$prize_details	= array();

				//for Top 1 Place
				if($result['master_contest_type_id'] == 1)
				{
					$prize_percent		= $winner_array[$result['master_contest_type_desc']];
					$amt				= (($prize_pool_amount*$prize_percent['1'])/100);
					$prize_details[0]	= $amt;
				}
				//Top 3,10,20 Place
				elseif( $result['master_contest_type_id'] == 2 || $result['master_contest_type_id'] == 3 || $result['master_contest_type_id'] == 4) 
				{
					$prize_percent = $winner_array[$result['master_contest_type_desc']];			
					for($i=0;$i<count($prize_percent);$i++)
					{
						$amt				= (($prize_pool_amount*$prize_percent[$i+1])/100);
						$prize_details[$i]	= $amt;
					}
				}
				//for Top 30% 0r 50%
				elseif($result['master_contest_type_id'] == 5 || $result['master_contest_type_id'] == 6)
				{
					
					$prize_percent = $winner_array[$result['master_contest_type_desc']];

					if((isset($prize_percent['30']) && $prize_percent['30']== 100) || (isset($prize_percent['50']) && $prize_percent['50'] == 100))
					{
						$place = floor(($size*$result['places'])/100);
						$amt = $prize_pool_amount/$place;

						if($desc)
						{
							//$prize_details = array($amt.' - TOP '.$place);
							$prize_details[0]	= $amt;
							$prize_place = $place;
						}
						else
						{
							for($i=0;$i<$place;$i++)
							{
								$amt				= $prize_pool_amount/$place;
								$prize_details[$i]	= $amt;
							}
						}
					} 
				}
				else
				{
					$prize_details = "";
				}
		}
		
		
		$data_array = array("prizes"=>$prize_details,"place"=>$prize_place,'prize_pool'=>$prize_pool_amount);
		
		return  $data_array;
	}

	public function get_master_description($post_data = array(),$rows = true)
	{
		$this->db->select('*');
		$this->db->from(MASTERDESCRIPTION);

		if(!empty($post_data))
		$this->db->where($post_data);

		$result  = $this->db->get();

		if($rows)
		$result = $result->result_array();
		else
		$result = $result->row_array();

		return $result;
	}

	public function get_salary_cap_detail($con = array())
	{
		$data = $this->db->select('*')
				->where('user_active', '1')
				->where($con)
				->get(MASTER_SALARY_CAP)
				->row_array();
		return $data;
	}
	
}