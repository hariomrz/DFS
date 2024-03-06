<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Prediction_feed_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->db	        = $this->load->database('prediction_db', TRUE);
		$this->db_user		= $this->load->database('user_db', TRUE);
		$this->db_fantasy   = $this->load->database('fantasy_db', TRUE);

	}

	function get_all_prediction()
	{
		return   $this->db->select("PM.prediction_master_id,PM.desc,PM.season_game_uid,PM.sports_id,PM.deadline_date,PM.total_pool,PM.prize_pool,"
                . "CONCAT(
            '[',
                GROUP_CONCAT(
                JSON_OBJECT(
                	'id',PO.prediction_option_id,
                    'value', PO.option
                   
                )
              ), 
            ']'
        ) AS options", FALSE)
				 ->from(PREDICTION_MASTER. " PM")
				 ->join(PREDICTION_OPTION. " PO","PM.prediction_master_id=PO.prediction_master_id")
				 ->where('PM.is_prediction_feed',1)
				 ->where('PM.status',0)
				 ->group_by("PO.prediction_master_id")
				 ->get()->result_array();
		//echo $this->db->last_query();die;
	}

	function save_record($table,$data){
		$this->db->insert($table,$data);
		return $this->db->insert_id();
	}

	function get_all_prediction_answer($feed_id)
	{   
		return $this->db->select('PO.is_correct,PM.prediction_master_id,PO.prediction_option_id')
				->from(PREDICTION_MASTER. " PM")
				->join(PREDICTION_OPTION." PO","PO.prediction_master_id=PM.prediction_master_id")
				->where_in('PM.status',[1,2])
				->where('PM.is_prediction_feed',1)
				->where_in('PM.prediction_master_id',$feed_id)
				->where('PO.is_correct',1)
				->get()->result_array();
	}

	function get_prediction_details($prediction_master_id)
	{
		$current_date = format_date();
		$result =  $this->db->select('PM.*')
		->from(PREDICTION_MASTER.' PM' )
		->where('prediction_master_id',$prediction_master_id)
		->get()->row_array();

		$result['option'] =$this->db->select('PO.*,
		0 as prediction_count,"" as user_id')
		->from(PREDICTION_OPTION.' PO' )
		->where('PO.prediction_master_id',$prediction_master_id)
		->group_by('PO.prediction_option_id')->get()->result_array();

		$result['deadline_time'] = strtotime($result['deadline_date'])*1000000 ;
		$result['today'] = strtotime($current_date)*1000000 ;

		return $result;	
	}

	public function save_question($data)
	{	
		if(empty($data)){
			return false;
		}
		$prediction_array =[];
		$current_date = format_date();
		foreach ($data as $key => $value) {
                $check_exist = $this->get_single_row('prediction_master_id',PREDICTION_MASTER,['feed_id'=>$value['prediction_master_id']]);
                if(!empty($check_exist)){
                    continue;
                }

                $prediction_array = array(
                    'feed_id'         =>    $value['prediction_master_id'],
                    'desc'            =>    $value['desc'],
                    'season_game_uid' =>    $value['season_game_uid'],
                    'sports_id'       =>    $value['sports_id'],
                    'deadline_date'   =>    $value['deadline_date'],
                    'total_pool'      =>    $value['total_pool'],
                    'is_prediction_feed'=>  1,
                    'prize_pool'      =>    $value['prize_pool'],
                    'added_date'      =>    $current_date,
                    'updated_date'    =>    $current_date,

                );
               $pred_master_id =  $this->save_record(PREDICTION_MASTER,$prediction_array);
               $options     = json_decode($value['options'],1);
                   $option_arr = [];
                   foreach ($options as $opt_key => $opt_value) {
                      $option_arr[]    = array(
                        'feed_id'              => $opt_value['id'],
                        'prediction_master_id' => $pred_master_id,
                        'option'               => $opt_value['value'], 
                        'added_date'           => $current_date,
                        'updated_date'         => $current_date,
                    );
                  
               }
               $this->table_name = PREDICTION_OPTION;
               $this->insert_batch($option_arr);

                $one_prediction = $this->get_prediction_details($pred_master_id); 
				$node_url = "newPredictionAlert";
				$node_data=  array('season_game_uid' => $value['season_game_uid'],'prediction' => $one_prediction);
				$this->notify_prediction_to_client($node_url,$node_data);

				 //add to queue
				   $prediction_queue=array(
					'prediction_master_id'=>$pred_master_id,
					'season_game_uid' => $value['season_game_uid'],
					'sports_id'=>$value['sports_id'],
					"prediction_action"    => 2 ,
					'question' =>isset($value['question'])?$value['question']:''
				   );
				   $this->load->helper('queue');
				   rabbit_mq_push($prediction_queue,'prediction');

            }
		return true;
	}

	public function save_answer($data)
	{	if(empty($data)){
			return false;
		}

		foreach ($data as $key => $value) {
           $prediction_master_id = $this->get_single_row('prediction_master_id',PREDICTION_MASTER,['feed_id'=>$value['prediction_master_id']]);         
           
           $update_po = $this->update(PREDICTION_OPTION,['is_correct'=>1],['feed_id'=>$value['prediction_option_id']]);
           if(!empty($update_po)){
              $update_pm =   $this->update(PREDICTION_MASTER,['status'=>1],['feed_id'=>$value['prediction_master_id']]);
              if(!empty($update_pm)){
                //$this->Prediction_feed_model->process_prediction_winning($value['prediction_master_id']);

				$result = $this->get_one_prediction_details($prediction_master_id['prediction_master_id']);
				$season_data = $this->get_match_details($result[0]['season_game_uid']);
				$prediction_data = array_merge($result[0],$season_data);
				
				$queue_content = array(
				"prediction_master_id" => $prediction_master_id ['prediction_master_id'],
				"status"               => 1,
				"added_on_queue"       => format_date(),
				"prediction_action"    => 1 ,
				"prediction_data" => $prediction_data
				);

				$this->load->helper('queue');
				rabbit_mq_push($queue_content, 'prediction');
              }
           }
        }
        return true;
	}

	function get_one_prediction_details($prediction_master_id)
	{
		$default_selected_columns = "prediction_master_id,season_game_uid,`desc`,season_game_uid,DATE_FORMAT(deadline_date, '".MYSQL_DATE_TIME_FORMAT."') as deadline_date,status,total_user_joined,site_rake,IFNULL(prize_pool,0) AS prize_pool,total_pool,is_pin";
		$sql = $this->db->select($default_selected_columns,FALSE)
		->from(PREDICTION_MASTER)
		->where('prediction_master_id',$prediction_master_id);

		$query  = $this->db->get();
		$result = $query->result_array();

		if(!empty($result))
		{
			foreach ($result as $key => $value)
			{
				$selected_option_id      = "";
				$prediction_master_id    = $value['prediction_master_id'];
				$result[$key]['options'] = $this->get_prediction_options($prediction_master_id);
				if(!empty($result[$key]['options']))
				{
					foreach ($result[$key]['options'] as $okey => $ovalue)
					{
					    if(!empty($ovalue['is_correct']))
					    {
					    	$selected_option_id = $ovalue['prediction_option_id'];
					    }		
					}
				}	

				$result[$key]['selected_option_id'] = $selected_option_id;
			}
		}	

		return $result;
	}

	public function get_prediction_options($prediction_master_id)
	{

		$optionSql = $this->db->select("PO.prediction_master_id,PO.prediction_option_id,PO.`option`,PO.is_correct,COUNT(UP.user_id) as prediction_count,SUM(IFNULL(UP.bet_coins,0)) as option_total_coins",FALSE)
						->from(PREDICTION_OPTION.' PO')
						->join(USER_PREDICTION.' UP',"UP.prediction_option_id=PO.prediction_option_id",'LEFT')
						->where("PO.prediction_master_id",$prediction_master_id)
						->group_by('PO.prediction_option_id')
						->order_by("PO.prediction_option_id","ASC")
						->get();

						//echo $this->db_prediction->last_query();die;
		return ($optionSql->num_rows() > 0) ? $optionSql->result_array() : array();				

	}



	function get_match_details($season_game_uid)
	{
		return $this->db_fantasy->select('season_game_uid,home,away,season_scheduled_date')
		->from(SEASON)
		->where('season_game_uid',$season_game_uid)
		->get()->row_array();	
	}

	/**
	 * Pause/play if update from cricjam
	 * @param Int pause,prediction_master_id
	 */
	public function update_pause_play($data){
		$record = $this->get_single_row('prediction_master_id,season_game_uid',PREDICTION_MASTER,['feed_id'=>$data['prediction_master_id']]);  
		if(!empty($record)){
			
			$node_data=  array('prediction_master_id' => $record['prediction_master_id'],'season_game_uid' => $record['season_game_uid']);
			if($data['pause'] == 1){
				$status = 3;
				$node_data['pause'] = 1;
			}else{
				$status = 0;
				$node_data['pause'] = 0;
				$one_prediction = $this->get_prediction_details($record['prediction_master_id']);
	            $node_data['prediction'] = $one_prediction;
			}
			$this->db->where('prediction_master_id',$record['prediction_master_id'])
				->update(PREDICTION_MASTER,array('status'=> $status));
			$node_url = "pausePlayPrediction";
            $this->notify_prediction_to_client($node_url,$node_data);
		}       
       return;
	}

	public function update_pin_prediction($data){
		$this->db->where('feed_id',$data['prediction_master_id'])
       ->update(PREDICTION_MASTER,array('is_pin'=> $data['is_pin']));
       return $this->db->affected_rows();    

	}

	/**
	 * update prediction from feed
	 * @param prediction_master_id
	 * Update question,create new Prediction option 
	 */
	public function update_prediction($data)
	{
		if(empty($data)){
			return false;
		}
		$prediction_master_id = $this->get_single_row('prediction_master_id,sports_id',PREDICTION_MASTER,['feed_id'=>$data['prediction_master_id']]);
		$current_date = format_date();
		$update_arr = array(
			'desc'=>$data['desc'],
			'deadline_date'=>$data['deadline_date'],
			'updated_date'=>$current_date
		);
		$update = $this->db->where('prediction_master_id',$prediction_master_id['prediction_master_id'])
			 ->update(PREDICTION_MASTER,$update_arr);


		if($update){
			$options     = json_decode($data['options'],1);
                   $option_arr = [];
                   foreach ($options as $opt_key => $opt_value) {
                      $option_arr[]    = array(
                        'feed_id'              => $opt_value['id'],
                        'prediction_master_id' => $prediction_master_id['prediction_master_id'],
                        'option'               => $opt_value['value'], 
                        'added_date'           => $current_date,
                        'updated_date'         => $current_date,
                    );
                  

               }
			$this->db->where('prediction_master_id', $prediction_master_id['prediction_master_id'])
			->delete(PREDICTION_OPTION);
               $this->table_name = PREDICTION_OPTION;
               $this->insert_batch($option_arr);

				$this->load->helper('queue_helper');
				$bucket_data = array("file_name"=>"lobby_fixture_list_prediction_".$prediction_master_id['sports_id'],"data"=>array(),"action"=>'delete');
				rabbit_mq_push($bucket_data, 'bucket');
              
		}
	return;
	}
	/**
	 * Delete prediction 
	 * @param prediction_master_id
	 * Refund if any user joined
	 */
	public function delete_prediction($data)
	{	
		$record = $this->get_single_row('prediction_master_id,total_user_joined,season_game_uid',PREDICTION_MASTER,['feed_id'=>$data['prediction_master_id']]);
		if(!empty($record)){
			$update = $this->db->where('prediction_master_id',$record['prediction_master_id'])
				->update(PREDICTION_MASTER,array('status'=> 4));//delete
			if($update) {
				if($record['total_user_joined'] > 0){
					$this->load->helper('queue');
					rabbit_mq_push(array('prediction_master_id'=>$record['prediction_master_id'], "prediction_action"=>0 ),'prediction');

				}
					$node_url = "deletePrediction";
					$node_data=  array('season_game_uid' => $record['season_game_uid'],
					'prediction_master_id' => $record['prediction_master_id']);
					$this->notify_prediction_to_client($node_url,$node_data);	
				}

		}
		return true;
		
	}

	public function notify_prediction_to_client($url,$data)
	{
		 $curlUrl = NODE_BASE_URL.$url;

		 $data_string = json_encode($data);

		 try{

		 	$header = array("Content-Type:application/json",
		 	 "Accept:application/json",
		 	  "User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
		 	);

 			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$curlUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);

		 	// Check the return value of curl_exec(), too
		    if ($server_output === false) {
		        throw new Exception(curl_error($ch), curl_errno($ch));
		    }
			curl_close ($ch);

		 }
		 catch(Exception $e){
		 	// var_dump($e);
		 	// die('dfdf');
		 }

            return true;
	}


}