<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);
//require_once "package/Api_credential_model.php";

class Common_cron_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->load->helper('cron');
	}

	
	/*********GAME CANCELLATION for All Leauge********/
	
	/**
	 * @Summary: This function for game cancellation when time up and total entry less then required.
	 *
	 * @access: public
	 * @param:
	 * @return:
	 */
	function contest_cancellation($uncapped)
	{
		//Check url hit by server or manual
		$this->check_url_hit();
		$sql = "SELECT
						C.contest_unique_id, count(LM.contest_unique_id) AS total_entries,C.size,C.minimum_size 
					FROM 
						".$this->db->dbprefix(CONTEST)." AS C
					LEFT JOIN 
						".$this->db->dbprefix(LINEUP_MASTER)." AS LM 
							ON  LM.contest_unique_id = C.contest_unique_id	
					WHERE 
						C.season_scheduled_date <= '".format_date()."' 
					AND 
						C.is_cancel = '0'
					AND
						C.prize_distributed = '0'
					AND
						C.is_feature = '0'	
				";
		if($uncapped == 'uncapped')
		{
			$sql .=	"	AND
							C.disable_auto_cancel = '1'
						AND			
							C.is_uncapped = '1'
						AND
							C.minimum_size != '0'			 
						GROUP BY 
							C.contest_unique_id HAVING total_entries < C.minimum_size 
					";			
		}
		else 
		{
			$sql .=	"	AND
							C.disable_auto_cancel = '0'
						AND			
							C.is_uncapped = '0'	
						AND
							C.minimum_size = '0'		 
						GROUP BY 
							C.contest_unique_id HAVING total_entries < C.size 
					";
			
		}  		 		   
		//echo $sql;	die;   
		$query = $this->db->query($sql);
		$contests_id = $query->result_array();
		
		//echo "<pre>";print_r($contests_id);die;
		//$this->contest_fee_refund();
		$contest_unique_id = array();
		foreach ($contests_id AS $key => $contest) {
			$contest_unique_id[] = $contest['contest_unique_id'];
		}
		//echo "<pre>";print_r($contest_unique_id);die;
		if (count($contest_unique_id) > 0) {

			//update contest table for contest cancel
			$this->db->where_in('contest_unique_id', $contest_unique_id);
			$this->db->update(CONTEST, array('is_cancel' => '1','modified_date' => format_date()));
			//fee refund immediately when contest cancel
			$this->contest_fee_refund();
		}
	}


	/**
	 * @Summary: This function for contest Fee refund when time up and total entry less then required.
	 *
	 * @access: public
	 * @param:
	 * @return:
	 */

	private function contest_fee_refund()
	{
		$sql = "SELECT
					LM.lineup_master_id,LM.user_id,LM.contest_unique_id,LM.fee_refund,C.entry_fee,C.season_scheduled_date
				FROM
					".$this->db->dbprefix(LINEUP_MASTER)." AS LM
				INNER JOIN ".$this->db->dbprefix(CONTEST)."	AS C ON C.contest_unique_id = LM.contest_unique_id
				WHERE
					C.is_cancel = '1'
				AND
					LM.fee_refund = '0'
			   ";
		//echo $sql;die;
		$query = $this->db->query($sql);
		$refund_data = $query->result_array();
		
		//echo "<pre>";print_r($refund_data);//die;
		if (count($refund_data) > 0)
		{
			foreach ($refund_data AS $refund)
			{

				//For update line up master table for fee refund update
				$this->db->where('lineup_master_id', $refund['lineup_master_id']);
				$this->db->update(LINEUP_MASTER, array('fee_refund' => '1'));

				//For update user and history table for add refund fee in balance $refund['users_id']
				$user_info = $this->common_model->get_single_row('balance,email,first_name,last_name,user_name',
																	USER, array('user_id' => $refund['user_id']));
				$return_amount = $refund['entry_fee'];
				
				$total_amount = $user_info['balance'] + $return_amount;

				//add history table
				$data = array(
					'user_id'						=> $refund['user_id'],
					'contest_unique_id'				=> $refund['contest_unique_id'],
					'master_description_id'			=> TRANSACTION_HISTORY_DESCRIPTION_ENTRY_FEE_REFUND,
					'payment_type'					=> CREDIT,
					'transaction_amount'			=> $return_amount,
					'user_balance_at_transaction'	=> $user_info['balance'],
					'date_added'					=> format_date()
				);
				
				//echo "<pre>";print_r($data);//die;
				try {
					if( $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION, $data) )
					{
						//update user data
						$this->db->where('user_id', $refund['user_id']);
						$this->db->update(USER, array('balance' => $total_amount));
						//Send mail for fee refund when Contest cancellation
						$this->mail_model->contest_cancellation_fee_refund_mail($user_info['email'],$user_info['first_name'],$user_info['last_name'],$refund['contest_unique_id'],$refund['season_scheduled_date'],$refund['user_id'],$user_info['user_name']);
					}
				}
				catch (Exception $e)
				{
					//echo 'Caught exception: '.  $e->getMessage(). "\n";
				} 
			}
		}
	}


	/**
	 * @Summary: This function for transfer prise money to user.
	 * @access: public
	 * @param:
	 * @return:
	 */
	private function transfer_prize_money_to_user($lineup_master_id, $win_amount, $contest_unique_id)
	{
		// Get user if from lineup_master id
		$lineup_master_info = $this->common_model->get_single_row('user_id',LINEUP_MASTER, array('lineup_master_id' => $lineup_master_id));
    	$user_id = $lineup_master_info['user_id'];

		$win_amount = truncate_number($win_amount,2);
		
		$user_info = $this->common_model->get_single_row('balance,email,first_name,last_name,user_name',USER, array('user_id' => $user_id));
		$total_amount = $user_info['balance'] + $win_amount;

		//add history table
		$data = array(
					'user_id'						=> $user_id,
					'lineup_master_id'           	=> $lineup_master_id,
					'master_description_id'			=> PRIZE_WON_DESCRIPTION,
					'contest_unique_id'				=> $contest_unique_id,
					'payment_type'					=> CREDIT,
					'transaction_amount'			=> $win_amount,
					'user_balance_at_transaction'	=> $user_info['balance'],
					'created_date'					=> format_date()
				);
		try 
		{		
			if( $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION, $data) )
			{
				//update user data
				$this->db->where('user_id', $user_id);
				$this->db->update(USER, array('balance' => $total_amount));

				// Game table update is_prize_distributed 1
				$this->db->where('contest_unique_id', $contest_unique_id);
				$this->db->update(CONTEST, array('prize_distributed' => '1','modified_date' => format_date(),'status'=>'closed'));
				//lineup master update is_winner 1
				$this->db->where(array('contest_unique_id' => $contest_unique_id,'user_id' =>$user_id,'fee_refund' => '0'));
				$this->db->update(LINEUP_MASTER, array('is_winner' => '1'));
				
				// Insert data for leaderbord
				$this->save_leaderboard_data($data);

				//Send mail to the user for won the Contest
				$this->mail_model->win_contest_transfer_prize_money_to_user_mail($user_info['email'],$user_info['first_name'], $user_info['last_name'],$contest_unique_id,$win_amount,$user_id,$user_info['user_name']);
			} 
		}
		catch (Exception $e)
		{
			//echo 'Caught exception: '.  $e->getMessage(). "\n";
		} 
	}
	
	
	/**
	 * @Summary: This function for handling tie up situation at posotion wiese and update.
	 * @access: public
	 * @param:
	 * @return:
	 */
	private function handling_tie_up_situation($winner_places, $user_data, $contest_unique_id)
	{
		//echo "*******************winner_places POSITION******************************";
		$user_score            = array();
		$remaining_prize_place = count($winner_places);
		$dpp                   = 0;         // $distributed_prize_place
		foreach($user_data as $val)
		{
			//$user_score[$val['user_id']]  = $val['total_score'];
			$user_score[$val['lineup_master_id']]  = $val['total_score'];
		}
		
		$tie_up_postion         = array_count_values($user_score);
		//echo "*******************TIEUP POSITION******************************";
		$final_user_amount_arry = array();
		foreach($tie_up_postion as $cur_score => $prize_count)
		{
			$total_amount_on_current_position = 0;
			$loop_count                       = 0;
			
			if($prize_count < $remaining_prize_place)
			{	
				$loop_count = $prize_count;
				for($j = 0; $j<$loop_count; $j++)
				{
					$prize_posi = $j+$dpp;
					$total_amount_on_current_position = $total_amount_on_current_position + ($winner_places[$prize_posi]);
				}
			
				$each_user_prize_amount =  $total_amount_on_current_position / $prize_count;
				foreach($user_score as $uid => $score)
				{
					if($score == $cur_score)
					{
						$final_user_amount_arry[$uid] = $each_user_prize_amount ;
					}
				}
			}
			else
			{		
				if($prize_count == $remaining_prize_place)
				{
					$loop_count = $prize_count;
				}
				else
				{
					$loop_count = $remaining_prize_place ;
				}
				for($j =0 ; $j<$loop_count; $j++) 
				{
					$prize_posi = $j + $dpp;
					$total_amount_on_current_position = $total_amount_on_current_position + ($winner_places[$prize_posi]);
				}

				$each_user_prize_amount =  $total_amount_on_current_position/$prize_count ;

				
				foreach($user_score as $uid => $score)
				{
					if($score == $cur_score)
					{
						$final_user_amount_arry[$uid] = $each_user_prize_amount ;
					}
				}
			}
			
			// When all prize position has been complete then break the loop
			$remaining_prize_place = $remaining_prize_place - $prize_count;
			$dpp                   = $dpp + $prize_count ;
			if($remaining_prize_place <=0)
			{
				break;
			}           
		}
		// End of tie_up_position loop
		//echo "*******************final_user_amount_arry******************************";
		foreach ($final_user_amount_arry as $lineup_master_id => $amount) 
		{
			//for truncate
			$this->transfer_prize_money_to_user($lineup_master_id, truncate_number($amount,2), $contest_unique_id );		
		}
	}
	
	
	/**
	 * @Summary: This function for get winners is from the limeup master table.
	 * @access: public
	 * @param:
	 * @return:
	 */
	private function get_winners_ids($number_of_places='1', $contest_unique_id)
	{
		
		$sql = "SELECT LM.user_id, LM.total_score,LM.lineup_master_id
						 FROM  ( SELECT DISTINCT total_score 
									FROM ".$this->db->dbprefix(LINEUP_MASTER)."  
									WHERE contest_unique_id = '".$contest_unique_id."' 
									ORDER BY total_score DESC  
									LIMIT ".$number_of_places." 
								) as preq
						LEFT JOIN ".$this->db->dbprefix(LINEUP_MASTER)."  AS LM ON 
									LM.total_score = preq.total_score
						WHERE 
							LM.contest_unique_id = '".$contest_unique_id."'
						ORDER BY 
							LM.total_score DESC ";
		//echo $sql;die;
		$rs     = $this->db->query($sql);
		$result = $rs->result_array();
		return $result;
	}

	/*********GAME CANCELLATION END*****/


		/**
	 * @Summary: This function for distribute daily prize for winner.
	 * @access: public
	 * @param:
	 * @return:
	 */
	function daily_prize_distribute_to_winner()
	{
		//Check url hit by server or manual
		$this->check_url_hit();
		$sql = "SELECT
					C.contest_unique_id,C.season_scheduled_date,C.league_duration_id,C.entry_fee,C.league_contest_type_id,
					C.size AS entries,C.prize_pool,MNOF.places AS places_paid,MNOF.position_or_percentage,
					MD.duration_id,MD.duration_desc,C.is_uncapped,C.minimum_size,C.site_rake,
					count(LM.contest_unique_id) AS total_user_joined
				FROM
					".$this->db->dbprefix(CONTEST)." AS C
				INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON 
						LM.contest_unique_id = C.contest_unique_id	
				INNER JOIN ".$this->db->dbprefix(LEAGUE_CONTEST_TYPE)." AS LNOW ON
						LNOW.league_contest_type_id = C.league_contest_type_id
				INNER JOIN ".$this->db->dbprefix(MASTER_CONTEST_TYPE)." AS MNOF ON 
						MNOF.master_contest_type_id = LNOW.master_contest_type_id
				INNER JOIN ".$this->db->dbprefix(LEAGUE_DURATION)." AS LD ON
						LD.league_duration_id = C.league_duration_id
				INNER JOIN ".$this->db->dbprefix(MASTER_DURATION)." AS MD ON
						MD.duration_id = LD.duration_id 				
				WHERE
                                 	C.is_cancel = '0'
				AND
					C.prize_distributed = '0'
				AND
					C.status = 'closed'	
				AND
					C.season_scheduled_date	< '".format_date()."'
				GROUP BY
					C.contest_unique_id	
				";
		//echo $sql;die;
		$query = $this->db->query($sql);
		$prize_data =  $query->result_array();
                if(!empty($prize_data)) {
                        
                        ## Get Paid Game Join offer
                        $paid_game_join_offer = $this->common_model->get_single_row('*', OFFER, array('offer_type'=> BONUS_TYPE_PAID_GAME_JOIN_POINT,'status'=>'1'));
                    
			$user_ids = array();

			foreach ($prize_data AS $key => $prize) {
                            
				//for uncapped funatinlity for distributed prize based on total entry
				if($prize['is_uncapped'] == '1' AND  $prize['minimum_size'] > '0')
				{
					$prize_entries = $prize['total_user_joined'];
				}
				else
				{
					$prize_entries = $prize['entries'];
				}
				//end uncapped model
				$wining_amount = $this->common_model->prize_details_by_size_fee_prizing(
													$prize_entries,$prize['entry_fee'],$prize['league_contest_type_id'],$prize['site_rake'],$prize['prize_pool']);
				$wining_amount = $wining_amount['prizes'];
				$winner_places = count($wining_amount);			

				$result  = $this->get_winners_ids($winner_places, $prize['contest_unique_id']);
					
				if(!empty($result))
				{
					$user_ids = array();
					$lineup_master_ids = array();
					$total_scores = array();
					foreach ($result as  $key=>$value)
					{
							$user_ids[$key] = $value['user_id'];
							$lineup_master_ids[$key] = $value['lineup_master_id'];
							$total_scores[$key] = $value['total_score'];
					}
					
					/** new start here  */
					if(count(array_unique($lineup_master_ids)) == count(array_unique($total_scores)))
					{                   
						if(!empty($lineup_master_ids))
						{
							foreach($lineup_master_ids as $key=> $lineup_master_id)
							{
								$i = $key;
								$this->transfer_prize_money_to_user($lineup_master_id, $wining_amount[$i], $prize['contest_unique_id'] );
							}
						}
					} 
					else 
					{
						// Now here is tie up situation
						$this->handling_tie_up_situation($wining_amount, $result, $prize['contest_unique_id']);
						//End tie Braker 	
					}
					/** new End here  */
				}
                                
                                ## Distribute POINTS to Contest Winner to Gets USER RATING
                                $this->distribute_contest_winning_point($prize['contest_unique_id'], $prize['entry_fee']);
                                                                
                                ## Distribute Big Game Join Bonus if this game is big game join bonus game
                                if(BONUS_BIG_GAME_UNIQUE_ID ==  $prize['contest_unique_id']){
                                        $this->distribute_paid_and_big_game_bonus($prize['contest_unique_id']);
                                }
                                ## Distribute PAID Game Join POINT if this game is paid game
                                if(!empty($paid_game_join_offer) && $prize['entry_fee'] > 0){
                                        $this->distribute_paid_and_big_game_bonus($prize['contest_unique_id'], $paid_game_join_offer);
                                }
			}	
		}
                //call function for process bonus cash
                $this->process_bonus_conversion();
                
		//cancel game when no join
		$this->close_is_feature_game();
	}
	

	/**
	 * @Summary: This function for game calcel when user not join and game if is_fature.
	 * @access: public
	 * @param:
	 * @return:
	 */
	private function close_is_feature_game()
	{
		$sql = "SELECT
					C.contest_unique_id,C.season_scheduled_date,C.size AS entries,C.minimum_size,C.total_user_joined,
					count(LM.contest_unique_id) AS user_joined
				FROM
					".$this->db->dbprefix(CONTEST)." AS C
				LEFT JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON 
						LM.contest_unique_id = C.contest_unique_id	
				WHERE
					C.is_cancel = '0'
				AND
					C.prize_distributed = '0'
				AND
					C.status = 'closed'	
				AND
					C.minimum_size = '0' 
				AND
					C.total_user_joined = '0'	
				AND
					C.season_scheduled_date	< '".format_date()."'
				GROUP BY
					C.contest_unique_id	
				";
		//echo $sql;die;
		//DATE_FORMAT(G.season_scheduled_date, '%Y-%m-%d' ) = DATE_SUB('".date('Y-m-d',strtotime(format_date())). "',INTERVAL 1 DAY)
		$query = $this->db->query($sql);
		$prize_data =  $query->result_array();
		
		//echo "<pre>";print_r($prize_data);die;
		if(!empty($prize_data)) 
		{
			foreach ($prize_data AS $key => $contest)
			{
				//update Contest table for contest cancel
				$this->db->where_in('contest_unique_id', $contest['contest_unique_id']);
				$this->db->update(CONTEST, array('is_cancel' => '1','modified_date' => format_date()));
			}	
		}	
	}
	
	
	/**
	 * @Summary: This function for return last week from current date.
	 * @access: public
	 * @param:
	 * @return:
	 */
	 
	private function get_last_week($league_id = '1')
	{
		$today_date = format_date();
		//$today_date = '2013-01-01';

		$sql = "SELECT 
					season_week
				FROM 
					".$this->db->dbprefix(SEASON_WEEK)."
				WHERE   
					league_id='".$league_id."'
				AND
					season_week_start_date_time < '" . $today_date . "'
				AND		
					season_week_close_date_time >'" . $today_date . "'
				LIMIT 1";

		$rs = $this->db->query($sql);
		$res = $rs->row_array();

		//return $res['season_week'] - 1 ;  // To get last week from current date
		return $res['season_week']  ;  // To get last week from current date
	}

	/**
	 * @Summary: This function for use for update fantasy plaer points to lineup and lineup master table after caclulation of fantasy points 
	 * database.
	 * @access: public
	 * @param:$leagues
	 * @return:
	 */
	public function update_scores_in_lineup($season_matches)
	{

		//$season_matches  = $this->get_current_season_match($sports_id);
		 echo "<pre>";print_r($season_matches);die;
		if(!empty($season_matches))
		{
			$all_season_game_unique_id = array();

			foreach ($season_matches AS $season)
			{
				//echo "<pre>";print_r($season);die;
				$sql = "UPDATE  
							".$this->db->dbprefix(LINEUP)."  AS LU
						LEFT JOIN 	
							".$this->db->dbprefix(GAME_PLAYER_SCORING)." AS GPS  ON ( GPS.player_unique_id = LU.player_unique_id  
								AND GPS.season_game_uid = '".$season['season_game_uid']."'	)
						SET 
							LU.score = IFNULL(GPS.score,'0.00')
						WHERE
							GPS.league_id = '".$league_id."'
						AND	
							LU.season_game_unique_id = '".$season['season_game_uid']."'
						";
					
					//echo "<pre>";	echo $sql;die;
				$this->db->query($sql);
				$all_season_game_unique_id[] = "'".$season['season_game_uid']."'" ;
			}		
			
			//update master line up
			// $this->update_scores_in_lineup_master($season['week'],$season['season_game_unique_id'],$league_id);

			$today_date = format_date();

			$lineup_sql = " SELECT 
								LM.lineup_master_id 
							FROM 
								".$this->db->dbprefix(LINEUP)." AS L 
							INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM ON LM.lineup_master_id = L.lineup_master_id
							INNER JOIN ".$this->db->dbprefix(CONTEST)." AS C ON C.contest_unique_id = LM.contest_unique_id
							WHERE 
								L.season_game_unique_id IN (".implode(',', $all_season_game_unique_id).")
							AND
								C.is_cancel = '0'
							AND
								C.prize_distributed = '0'
							AND
								C.season_scheduled_date >= SUBDATE('".$today_date."', INTERVAL 10 DAY)
							GROUP BY 
								LM.lineup_master_id
						";
			$lineup_master_ids = $this->db->query($lineup_sql)->result_array();

			if(!empty($lineup_master_ids))
			{
				$ids = array_column($lineup_master_ids, 'lineup_master_id');

				$update_sql = " UPDATE 
									".$this->db->dbprefix(LINEUP_MASTER)." AS LM
								INNER JOIN ( SELECT SUM(score) AS scores, L.lineup_master_id, L.season_game_unique_id 
												FROM ".$this->db->dbprefix(LINEUP)." AS L 
												WHERE L.lineup_master_id IN (".implode(',', $ids).")  
												GROUP BY L.lineup_master_id 
											) AS L_PQ ON L_PQ.lineup_master_id = LM.lineup_master_id 
								SET 
									LM.total_score			=   IFNULL( L_PQ.scores,'0.00'),
									LM.current_week_score	= IFNULL( L_PQ.scores,'0.00') 
							";

				$this->db->query($update_sql);
			}
		}	
	}

	/**
	 * @Summary: This function for use for update score when game in live through node client
	 * database.
	 * @access: public
	 * @param:$league_id
	 * @return:
	 */
	public function update_score_node_client($league_id)
	{
		//get current game which is start 
		$teams  = $this->get_current_game($league_id);
		if(!empty($teams))
		{
			// debug($teams);
			// This function is use for node score update.
			// echo "<script>setTimeout( function(){window.location.reload();},10000);</script>";
			$this->update_node_client($teams);
		}	
	}

		/**
	 * @Summary: This function for use for update contest status when contest are closed or spent time (6 hours)
	 * database.
	 * @access: public
	 * @param:$league_id
	 * @return:
	 */
	public function update_contest_status($league_id)
	{
		//Check url hit by server or manual
			// $this->check_url_hit();
		$interval = game_interval($league_id);	
		//die;exit;
		$current_date_time =  format_date();//format_date();
		$previous_date = date('Y-m-d', strtotime(format_date().' -1 day'));
		//echo $previous_date; die;
		$sql = "SELECT 
					S.`season_game_unique_id` 
				FROM 
					".$this->db->dbprefix(SEASON)."  AS S
				WHERE 
					DATE_FORMAT ( S.season_scheduled_date ,'%Y-%m-%d %H:%i:%s' ) <=	DATE_SUB('$current_date_time' , INTERVAL ".$interval." HOUR)
				AND
					 ( DATE_FORMAT ( S.season_scheduled_date ,'%Y-%m-%d' ) =	DATE_FORMAT ( '$current_date_time' ,'%Y-%m-%d' )
						OR 
						DATE_FORMAT ( S.season_scheduled_date ,'%Y-%m-%d' ) =	'$previous_date'
					 )		
				AND 
					S.league_id = $league_id
				";
		//echo $sql;die;		
		$result = $this->db->query($sql);
		$teams  = $result->result_array();
		//echo "<pre>";print_r($teams);die;
		if(isset($teams) && count($teams)> 0 )
		{
			foreach ($teams as $key => $value)
			{
				$contest_unique_id = $value['season_game_unique_id'];
				$this->change_contest_status($contest_unique_id ,$league_id);
			}
		}	
	}

	private function get_site_rake($entry_fee)
	{
		$this->db->select('site_rake');
		$this->db->from(MASTER_SITE_RAKE);		
		$this->db->where('lower_limit_of_entry_fee <=', $entry_fee);
		$this->db->where('upper_limit_of_entry_fee >=', $entry_fee);	
		$query = $this->db->get();
		return $query->row('site_rake');
	}
	 /**
	 * This method insert leaderboard data
	 * @param array $data
	 * @return boolean 
	 * @throws Exception
	 */
	public function save_leaderboard_data($data)
	{	
		try 
		{
			//Update rank or winner_place in lineup master
			$this->update_all_user_rank_by_scoring($data['contest_unique_id']);
			//echo "<pre>";print_r($data);die;
			// Get contest_id from contest_unique_id 
			$contest_data = $this->common_model->get_single_row('contest_id,winner_place', LINEUP_MASTER, array('contest_unique_id'=> $data['contest_unique_id'],'user_id'=>$data['user_id']));

			$data_array = array(
								'contest_id'			=> $contest_data['contest_id'],
								'lineup_master_id'		=> $data['lineup_master_id'],
								'user_id'				=> !empty($data['user_id']) ? $data['user_id'] : 0,
								'transaction_amount'	=> !empty($data['transaction_amount']) ? $data['transaction_amount'] : 0,
								'rank'					=> $contest_data['winner_place'],
								'created_date'			=> format_date()
							);

			$this->db->insert(LEADERBOARD, $data_array);
			return true;

		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * [get_referral_users description]
	 * @MethodName get_referral_users
	 * @Summary This function used for Referral user list which user signup with referral
	 * @return     [type]
	 */
	public function get_referral_users()
	{
		$referral_fund_data = $this->get_referral_fund_detail();

		$sql = $this->db->select("SUM(PHT.transaction_amount) as invest_amount ,U.email,U.user_id, R.from_user_id, R.referral_id")
						->from(REFERRAL." AS R")
						->join(USER." AS U", "U.email = R.to_email", "INNER")
						->join(PAYMENT_HISTORY_TRANSACTION." AS PHT", "PHT.user_id = U.user_id", "LEFT")
						->join(CONTEST." AS C", "C.contest_unique_id = PHT.contest_unique_id", "LEFT")
						->where("R.is_registered",1)
						->where("C.prize_distributed",'1')
						->where("R.is_referral_bonus",0)
						->group_by("U.user_id")
						->having("invest_amount>=".$referral_fund_data['invest_money'])
						->get();
		$referral_data = $sql->result_array();
		return $referral_data;		
	}

	/**
	 * [get_referral_fund_detail description]
	 * @MethodName get_referral_fund_detail
	 * @Summary This function used for Referral invest fund and referral amount detail
	 * @return     array
	 */
	public function get_referral_fund_detail()
	{
		$sql = $this->db->select("invest_money, referral_amount")
						->from(REFERRAL_FUND)
						->where("referral_fund_id",1)
						->get();
		$referral_fund_data = $sql->row_array();
		return $referral_fund_data;
	}

	/**
	 * [add_transaction description]
	 * @MethodName add_transaction
	 * @Summary This function used to add referral fund entry in payment history table
	 * @return boolean
	 */
	public function add_transaction($data_array)
	{
		$this->db->insert(PAYMENT_HISTORY_TRANSACTION, $data_array);
		return $this->db->insert_id();
	}

	/**
	 * [update_user_balance description]
	 * @MethodName update_user_balance
	 * @Summary This function used to update user balance
	 * @return boolean
	 */
	public function update_user_balance($user_id, $balance)
	{
		$this->db->where("user_id", $user_id)->update(USER, array("balance"=>$balance));
		return $this->db->affected_rows();
	}

	public function get_user_detail_by_id($user_id)
	{
		$sql = $this->db->select("balance")
						->from(USER)
						->where("user_id", $user_id)
						->get();
		return $sql->row_array();
	}

	/**
	 * [update_referral_status description]
	 * @MethodName update_referral_status
	 * @Summary This function used to update referral status
	 * @return boolean
	 */
	public function update_referral_status($referral_id)
	{
		$this->db->where("referral_id", $referral_id)->update(REFERRAL, array("is_referral_bonus"=>1));
		return $this->db->affected_rows();
	}

	public function get_user_limit_data()
	{
		$sql = "SELECT 
					UL.limit_id,UL.user_id,UL.limit_for,UL.date_added 
				FROM 
					".$this->db->dbprefix(USER_LIMIT)." AS UL 
					join (select UL2.date_added,UL2.user_id,UL2.limit_for from `vi_user_limit` AS UL2 where status='1') AS current_limit
				where 
					current_limit.user_id = UL.user_id 
				AND 
					current_limit.limit_for = UL.limit_for 
				AND 
					UL.date_added > current_limit.date_added 
				AND 
					UL.status = '0' 				
				GROUP BY 
					UL.limit_for 
				ORDER BY 
					UL.date_added DESC";
		$query = $this->db->query($sql);
		$result = $query->result_array();
		if (!empty($result)) {
			return $result;
		}else{
			return false;
		}
	}


	public function prize_distribute_for_prediction($league_id)
	{
		//Check url hit by server or manual
			// $this->check_url_hit();
		$interval = game_interval($league_id);	
		//die;exit;
		$current_date_time =  format_date();//format_date();
		$previous_date = date('Y-m-d', strtotime(format_date().' -1 day'));
		//echo $previous_date; die;
		$sql = "SELECT 
					S.`season_game_unique_id`,S.league_id,PRE.prediction_id,PRE.prediction,PRE.user_id,L.sports_id  
				FROM 
					".$this->db->dbprefix(PREDICTION)."  AS PRE
				INNER JOIN
					".$this->db->dbprefix(SEASON)."  AS S ON S.season_game_unique_id = PRE.season_game_unique_id AND S.league_id = PRE.league_id
				INNER JOIN
					".$this->db->dbprefix(LEAGUE)."  AS L ON L.league_id = PRE.league_id AND L.league_id = S.league_id	AND L.league_id = $league_id
				WHERE 
					DATE_FORMAT ( S.season_scheduled_date ,'%Y-%m-%d %H:%i:%s' ) <=	DATE_SUB('$current_date_time' , INTERVAL ".$interval." HOUR)
				AND 
					S.league_id = $league_id
				AND
					PRE.result = '0'
				";
		//echo $sql;die;		
		$result = $this->db->query($sql);
		$prediction_array  = $result->result_array();
		//echo "<pre>";print_r($prediction_array);die;
		if(isset($prediction_array) && !empty($prediction_array))
		{
			foreach ($prediction_array as $key => $value) 
			{
				//echo "<pre>";echo $value['prediction'];
				// For soccer	
				if ($value['sports_id'] == '5') 
				{
				        $sql = "SELECT
				        			GSS.team_id,GSS.team_goals				        			
				        		FROM 
				        			".$this->db->dbprefix(GAME_STATISTICS_SOCCER)." AS GSS
				        		WHERE
				        			GSS.season_game_unique_id = '".$value['season_game_unique_id']."'
				        		GROUP BY
				        			GSS.team_id 		
				        	   ";
				        //echo $sql;die;
						$rs     = $this->db->query($sql);
						$result = $rs->result_array();
						//echo "<pre>";print_r($result);continue;die;
						if(isset($result) && !empty($result))
						{	
							$team_1 = $result[0]['team_id'];
							$team_2 = $result[1]['team_id'];
							$score_1 = $result[0]['team_goals'];
							$score_2 = $result[1]['team_goals'];
							if($score_1 == $score_2) 
							{
								$final_result =  '0';
							}
							if($score_1 > $score_2)
							{
								$final_result = $team_1;
							}
							if($score_2 > $score_1) 
							{
								$final_result = $team_2;
							}
							//Update here in prediction table amd trasaction history	
							if(isset($final_result) && !empty($final_result))
							{	
								if($value['prediction'] == $final_result)
								{	
									$user_info = $this->common_model->get_single_row('balance,email,first_name,last_name,user_name',
																						USER, array('user_id' => $value['user_id']));
									$total_amount = $user_info['balance'] + 3;		
									$data = array(
													'user_id'						=> $value['user_id'],
													'lineup_master_id'           	=> 0,
													'master_description_id'			=> PRIZE_WON_DESCRIPTION,
													'contest_unique_id'				=> $value['prediction_id'],
													'payment_type'					=> CREDIT,
													'transaction_amount'			=> '3',
													'user_balance_at_transaction'	=> $user_info['balance'],
													'created_date'					=> format_date()
												);
									//echo "<pre>";print_r($data);continue;die;
									try 
									{		
										if( $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION, $data) )
										{
											//update user data
												$this->db->where('user_id', $value['user_id']);
												$this->db->update(USER, array('balance' => $total_amount));
											//Update prediction table
												$this->db->where('prediction_id', $value['prediction_id']);
												$this->db->update(PREDICTION, array('result' => '1','modify_date' => format_date()));
										} 
									}
									catch (Exception $e)
									{
										//echo 'Caught exception: '.  $e->getMessage(). "\n";
									}
								}
								else
								{
									//Update prediction table
										$this->db->where('prediction_id', $value['prediction_id']);
										$this->db->update(PREDICTION, array('result' => '2','modify_date' => format_date()));
								}	
							}	
						}		
				}

				//For NFL Football
				if ($value['sports_id'] == '2') 
				{
				        $sql = "SELECT
				        			GSF.team_points,
				        			CASE
				        				WHEN GSF.team_id = T1.team_abbr THEN T1.team_id
				        				WHEN GSF.team_id = T2.team_abbr THEN T2.team_id
				        			END as team_id					        			
				        		FROM 
				        			".$this->db->dbprefix(GAME_STATISTICS_FOOTBALL)." AS GSF
				        		INNER JOIN 
				        			".$this->db->dbprefix(TEAM)." AS T1 ON T1.team_abbr = GSF.home AND T1.league_id = $league_id
				        		INNER JOIN 
				        			".$this->db->dbprefix(TEAM)." AS T2 ON T2.team_abbr = GSF.away AND T2.league_id = $league_id		
				        		WHERE
				        			GSF.season_game_unique_id = '".$value['season_game_unique_id']."'
				        		GROUP BY
				        			GSF.team_id 		
				        	   ";
				       //echo $sql;die;
						$rs     = $this->db->query($sql);
						$result = $rs->result_array();
						//echo "<pre>";print_r($result);continue;die;
						if(isset($result) && !empty($result))
						{	
							$team_1 = $result[0]['team_id'];
							$team_2 = $result[1]['team_id'];
							$score_1 = $result[0]['team_points'];
							$score_2 = $result[1]['team_points'];
							if($score_1 == $score_2) 
							{
								$final_result =  '0';
							}
							if($score_1 > $score_2)
							{
								$final_result = $team_1;
							}
							if($score_2 > $score_1) 
							{
								$final_result = $team_2;
							}
							//Update here in prediction table amd trasaction history	
							if(isset($final_result) && !empty($final_result))
							{	
								if($value['prediction'] == $final_result)
								{	
									$user_info = $this->common_model->get_single_row('balance,email,first_name,last_name,user_name',
																						USER, array('user_id' => $value['user_id']));
									$total_amount = $user_info['balance'] + 3;		
									$data = array(
													'user_id'						=> $value['user_id'],
													'lineup_master_id'           	=> 0,
													'master_description_id'			=> PRIZE_WON_DESCRIPTION,
													'contest_unique_id'				=> $value['prediction_id'],
													'payment_type'					=> CREDIT,
													'transaction_amount'			=> '3',
													'user_balance_at_transaction'	=> $user_info['balance'],
													'created_date'					=> format_date()
												);
									//echo "<pre>";print_r($data);continue;die;
									try 
									{		
										if( $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION, $data) )
										{
											//update user data
												$this->db->where('user_id', $value['user_id']);
												$this->db->update(USER, array('balance' => $total_amount));
											//Update prediction table
												$this->db->where('prediction_id', $value['prediction_id']);
												$this->db->update(PREDICTION, array('result' => '1','modify_date' => format_date()));
										} 
									}
									catch (Exception $e)
									{
										//echo 'Caught exception: '.  $e->getMessage(). "\n";
									}
								}
								else
								{
									//Update prediction table
										$this->db->where('prediction_id', $value['prediction_id']);
										$this->db->update(PREDICTION, array('result' => '2','modify_date' => format_date()));
								}	
							}	
						}		
				}

				//For NBA basketball
				if ($value['sports_id'] == '4') 
				{
				        $sql = "SELECT
				        			GSB.team_points,GSB.team_id					        			
				        		FROM 
				        			".$this->db->dbprefix(GAME_STATISTICS_BASKETBALL)." AS GSB
				        		WHERE
				        			GSB.season_game_unique_id = '".$value['season_game_unique_id']."'
				        		GROUP BY
				        			GSB.team_id 		
				        	   ";
				       //echo $sql;die;
						$rs     = $this->db->query($sql);
						$result = $rs->result_array();
						//echo "<pre>";print_r($result);continue;die;
						if(isset($result) && !empty($result))
						{	
							$team_1 = $result[0]['team_id'];
							$team_2 = $result[1]['team_id'];
							$score_1 = $result[0]['team_points'];
							$score_2 = $result[1]['team_points'];
							if($score_1 == $score_2) 
							{
								$final_result =  '0';
							}
							if($score_1 > $score_2)
							{
								$final_result = $team_1;
							}
							if($score_2 > $score_1) 
							{
								$final_result = $team_2;
							}
							//Update here in prediction table amd trasaction history	
							if(isset($final_result) && !empty($final_result))
							{	
								if($value['prediction'] == $final_result)
								{	
									$user_info = $this->common_model->get_single_row('balance,email,first_name,last_name,user_name',
																						USER, array('user_id' => $value['user_id']));
									$total_amount = $user_info['balance'] + 3;		
									$data = array(
													'user_id'						=> $value['user_id'],
													'lineup_master_id'           	=> 0,
													'master_description_id'			=> PRIZE_WON_DESCRIPTION,
													'contest_unique_id'				=> $value['prediction_id'],
													'payment_type'					=> CREDIT,
													'transaction_amount'			=> '3',
													'user_balance_at_transaction'	=> $user_info['balance'],
													'created_date'					=> format_date()
												);
									//echo "<pre>";print_r($data);continue;die;
									try 
									{		
										if( $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION, $data) )
										{
											//update user data
												$this->db->where('user_id', $value['user_id']);
												$this->db->update(USER, array('balance' => $total_amount));
											//Update prediction table
												$this->db->where('prediction_id', $value['prediction_id']);
												$this->db->update(PREDICTION, array('result' => '1','modify_date' => format_date()));
										} 
									}
									catch (Exception $e)
									{
										//echo 'Caught exception: '.  $e->getMessage(). "\n";
									}
								}
								else
								{
									//Update prediction table
										$this->db->where('prediction_id', $value['prediction_id']);
										$this->db->update(PREDICTION, array('result' => '2','modify_date' => format_date()));
								}	
							}	
						}		
				}
				 
			}
		}
	}

	/**
	 * [process_bonus_conversion description]
	 * @method  process_bonus_conversion
	 * @Summary This function to check bonus cash convertion and make convet bonus cash to real money
	 * @return  
	 */
	public function process_bonus_conversion()
	{
		$sql  = $this->db->select("sum(C.entry_fee) AS total_spent_amount,LM.user_id,U.bonus_balance as user_bonus_balance")
				 ->from(CONTEST." AS C")
				 ->join(LINEUP_MASTER." AS LM", "LM.contest_unique_id = C.contest_unique_id", "INNER")
				 ->join(USER." AS U", "U.user_id = LM.user_id", "INNER")
				 ->where('C.prize_distributed','1')
				 ->where('LM.bonus_added','0')
                        	 ->group_by("LM.user_id")
				 ->get();
                //->where('C.contest_unique_id', 'E2bdlU58Q')
		$contest_data = $sql->result_array();		 
                //print_r($contest_data); die;
		if($sql->num_rows() > 0 && !empty($contest_data))
		{


			foreach ($contest_data as $key => $value) 
			{
				
				
				//update flag of bonus added(bonus processed) to all fetched contests of user
				$lineup_master_update = array('bonus_added'=>'1');
				$condition_arr = array('user_id'=>$value['user_id']);
				$this->common_model->update(LINEUP_MASTER,$lineup_master_update,$condition_arr); 	

				//if spending amount is empty then ignore this entry.
				if($value['total_spent_amount'] <= 0){continue;}
				
				//if user current bonus balance is empty then remove remaining bonus conversion amount for user
				if($value['user_bonus_balance'] <= 0)
				{
				  	$update_arr = array(
						'balance' 		=> 0,
						'update_date'	=> format_date()
					);
					$this->common_model->update(BONUS_CONVERSION,$update_arr, array('user_id'=>$value['user_id']));
					continue;
				}

				//if spending amount is not empty and user bonus balance is greater than 0 then move user spending amount to bonus conversion table.
				
				//check entry for this user is already exists or not
				$user_entry_exists = $this->common_model->get_single_row('balance',BONUS_CONVERSION,array('user_id'=>$value['user_id']));
				if($user_entry_exists) //if entry exists then update amount
				{
					$updated_amount = $user_entry_exists['balance']+$value['total_spent_amount'];
					$update_arr = array(
						'balance' 		=> $updated_amount,
						'update_date'	=> format_date()
					);
					$this->common_model->update(BONUS_CONVERSION,$update_arr, array('user_id'=>$value['user_id']));
				} 
				else //if not exists then make new entry in table 
				{
					$insert_arr = array(
					 'user_id'	   => $value['user_id'],
					 'balance'     => $value['total_spent_amount'],
					 'create_date' => format_date(),
					 'update_date' => format_date()
					);
					$this->common_model->insert_data(BONUS_CONVERSION,$insert_arr);
				}

			}
		}

		//call function for convert bonus cash to user's main balance
		$this->convert_bonus_cash_to_main_balance();
	}

	/**
	 * [convert_bonus_cash_to_main_balance description]
	 * @method  convert_bonus_cash_to_main_balance
	 * @Summary This function used to convert bonus cash to main balance
	 * @return  [type]
	 */
	private function convert_bonus_cash_to_main_balance()
	{
		$base_invest_money       = 10;
		$bonus_sql = $this->db->select("BC.user_id,BC.balance AS conversion_amount,U.balance AS user_current_balance,U.bonus_balance")
		             ->from(BONUS_CONVERSION." AS BC")
				 	 ->join(USER." AS U", "U.user_id = BC.user_id", "INNER")
				 	 ->where('BC.balance >=',$base_invest_money)
				 	 ->where('U.bonus_balance >',0)
		             ->get();
		$bonus_data = $bonus_sql->result_array();
		if($bonus_sql->num_rows() > 0 && !empty($bonus_data))
		{
			foreach ($bonus_data as $key => $value)
			{
			  	if(empty($value['conversion_amount']) || empty($value['bonus_balance'])){ continue;}

			  	$conversion_amount 	  		 = $value['conversion_amount'];
			  	$bonus_balance 	   	  		 = $value['bonus_balance'];
			  	$user_current_balance 		 = $value['user_current_balance'];
			  	$earning_amount       		 = floor($conversion_amount/$base_invest_money);
			  	$remaining_conversion_amount = ($conversion_amount%$base_invest_money);
			  	$user_id 					 = $value['user_id'];

			  	//if earning amount greater than or equal to available bonus amount
				if($earning_amount >= $bonus_balance)
				{
					$credit_amount 		= $bonus_balance;
					$debit_amount  		= $bonus_balance;
					$new_balance   		= $user_current_balance + $credit_amount;
					$new_bonus_balance 	= 0;
				} 
				else
				{
					$credit_amount 		= $earning_amount;
					$debit_amount  		= $earning_amount;
					$new_balance   		= $user_current_balance + $credit_amount;
					$new_bonus_balance 	= $bonus_balance - $earning_amount;
				}

				//make entry into transaction history table if credit amount exists.
				if(!empty($credit_amount))
				{

					$credit_history_array = array(
						'user_id'					 => $user_id,
						//'contest_unique_id'			 => $contest_unique_id,
						'payment_type'				 => CREDIT,
						'transaction_amount'    	 => $credit_amount,
						'user_balance_at_transaction'=> $user_current_balance,
						'created_date'				 => format_date(),
						'master_description_id'		 => TRANSACTION_HISTORY_BONUS_CONVERT_BALANCE	
					);

					$credit_history_id = $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION,$credit_history_array);
					if($credit_history_id) 
					{
						//update user data
						$this->db->where('user_id', $user_id);
						$this->db->update(USER, array('balance' => $new_balance));
					}	
				}

				//make entry into transaction history table if debit amount exists.
				if(!empty($debit_amount))
				{
					$debit_history_array = array(
						'user_id'					 => $user_id,
						//'contest_unique_id'			 => $contest_unique_id,
						'payment_type'				 => DEBIT,
						'amount_type'				 => 'bonus',
						'transaction_amount'    	 => $debit_amount,
						'user_balance_at_transaction'=> $bonus_balance,
						'created_date'				 => format_date(),
						'master_description_id'		 => TRANSACTION_HISTORY_BONUS_DEBITED
					);

					$debit_history_id = $this->common_model->insert_data(PAYMENT_HISTORY_TRANSACTION,$debit_history_array);
					if($debit_history_id)
					{
						//update user data
						$this->db->where('user_id', $user_id);
						$this->db->update(USER, array('bonus_balance' => $new_bonus_balance));
					}
				}

				//now update remaining bonus conversion amount on bonus conversion table
				$bonus_conversion_update_arr = array(
					'balance'=>$remaining_conversion_amount,
					'update_date'=>format_date()
				);
				$this->db->where('user_id', $user_id);
				$this->db->update(BONUS_CONVERSION, $bonus_conversion_update_arr);
			}//foreach end.
		}//if end.         
	}//function end.
        
        public function player_club_change_notification() {
               
                $current_date_time =  format_date();
                
                $this->load->model('api_credential_model');
		
                

                $this->db->select('C.contest_id, C.contest_unique_id, C.contest_name, C.league_id, C.selected_matches, C.season_scheduled_date, DATE_FORMAT(C.season_scheduled_date,"%Y-%m-%d") AS season_scheduled_date_day, C.season_week, MD.duration_id', false);
        		$this->db->from(CONTEST.' AS C');
                $this->db->join(LEAGUE.' AS L', 'L.league_id = C.league_id', 'INNER');
                $this->db->join(LEAGUE_DURATION.' AS LD', 'LD.league_duration_id = C.league_duration_id', 'INNER');
                $this->db->join(MASTER_DURATION.' AS MD', 'MD.duration_id = LD.duration_id', 'INNER');
               
                $this->db->where('C.season_scheduled_date >', $current_date_time);
                //$this->db->where('C.league_id =', $league_id);
                $this->db->where('C.is_cancel =', '0');
                $this->db->where('L.active =', '1');
                $this->db->where('LD.active =', '1');
                //$this->db->where('C.league_duration_id', '31');
                //$this->db->where("C.league_duration_id IN ('29','30')");
                $this->db->where('C.prize_distributed =', '0');
                //$this->db->where('C.contest_unique_id  in ("lzTkP39s8", "RXkKkrEHt")');//
                //$this->db->where('C.contest_unique_id  in ("zaNUcgUHB")'); // weekly game
	            //$this->db->order_by('C.contest_name ASC');
               
                $query = $this->db->get();
               
                //echo $this->db->last_query();
               
                $gameList = $query->result_array();
                //echo '<pre>'; print_r($gameList); die;
               
                if(!empty($gameList)) {
                   
                        foreach($gameList as $game) {
                                $api = $this->api_credential_model->active_feed_provide_season_year($game['league_id']);
                                $season_type = $api['season'];
                                $current_season_year = $api['year'];
                           
                                // For Seasonlong game
//                                if($game['season_end_week'] > 0) {
//                                   
//                                        $this->player_club_change_notification_for_seasonlong($game, $current_season_year);
//                                }
//                                else {  // For Daily-Weekly game
                                        $this->player_club_change_notification_for_dailyweekly($game, $current_season_year);
//                                }   
                        }
                }
        }

        public function player_club_change_notification_for_dailyweekly($game_data, $current_season_year){
           

                $max_player_per_team = $game_data['max_player_per_team'];
           
                ## Get lineup_master date with next week lineup master data ##
           
                $this->db->select('LM.lineup_master_id, LM.contest_unique_id, LM.user_id');
                $this->db->select('U.first_name, U.last_name, U.user_name, U.email');
                $this->db->from(LINEUP_MASTER.' AS LM');
                $this->db->join(USER.' AS U', 'U.user_id = LM.user_id', 'LEFT');
                $this->db->where('contest_unique_id', $game_data['contest_unique_id']);
               
                $query = $this->db->get();
                $lineup_master_data = $query->result_array();

                //echo '<pre>lineup_master_result: '; print_r($lineup_master_data);

                if(!empty($lineup_master_data)){
                    foreach($lineup_master_data as $lineup_master){
                                               
                        if(!empty($lineup_master['lineup_master_id'])){
                            
                                ## Get all matches team of this game
                           
                                $selected_matches = "'".str_replace(",", "','" , $game_data['selected_matches'])."'";
                               
                                $this->db->select('GROUP_CONCAT(T1.team_id) as home_team_ids, GROUP_CONCAT(T2.team_id) as away_team_ids');
                                $this->db->from(SEASON.' AS S');
                                $this->db->join(TEAM.' AS T1', 'T1.team_abbr = S.home AND T1.year = "'.$current_season_year.'"', 'LEFT');
                                $this->db->join(TEAM.' AS T2', 'T2.team_abbr = S.away AND T2.year = "'.$current_season_year.'"', 'LEFT');
                               
                                $this->db->where("S.season_game_unique_id IN ($selected_matches)");
                                $this->db->where('S.year', $current_season_year);
                                $this->db->group_by('S.week');
                                $query = $this->db->get();
                                //echo $this->db->last_query();
                                $match_team_result = $query->row_array();
                               
                                $match_team_id_arr = array();

                                if(!empty($match_team_result)) {
                                    $match_team_ids = implode(',', $match_team_result);
                                    $match_team_id_arr = explode(',', $match_team_ids);
                                }

                                ## Now get lineup player data and ##

                                $this->db->select('L.lineup_id, L.lineup_master_id, L.player_unique_id, L.player_team_id AS lineup_team_id, L.is_club_mail_send', FALSE);
                                $this->db->select('P.team_id, P.full_name ', FALSE);
                                $this->db->select('LT.team_name AS lineup_team_name, T.team_name AS player_team_name', FALSE);
                               
                                $this->db->from(LINEUP.' AS L');
                                $this->db->join(PLAYER.' AS P', 'P.player_unique_id = L.player_unique_id AND P.league_id = '.$game_data['league_id'], 'LEFT');
                                $this->db->join(TEAM.' AS LT', 'LT.team_id = L.player_team_id AND LT.year = "'.$current_season_year.'"', 'LEFT'); // lineup player team
                                $this->db->join(TEAM.' AS T', 'T.team_id = P.team_id AND T.year = "'.$current_season_year.'"', 'LEFT');  // new team from player table
                               
                                $this->db->where('lineup_master_id', $lineup_master['lineup_master_id']);
                                $query = $this->db->get();
                               
                                //echo $this->db->last_query();
                               
                                $lineup_player = $query->result_array();
                               
                                $is_rule_violate = false;
                                $is_club_out_of_match = false;
                               
                                $club_change_player = array();
                                $player_team_temp = array();
                               
                                if(!empty($lineup_player)) {
                                   
                                        foreach($lineup_player as $lineup) {
                                           
                                                // if playre club change
                                                if($lineup['lineup_team_id'] != $lineup['team_id']){
                                                        $club_change_player[$lineup['player_unique_id']] = $lineup;
                                                }
                                               
                                                // Set array for players having same team in lineup
                                                $player_team_temp[$lineup['team_id']][] = $lineup['player_unique_id'];
                                        }
                                }
                               
                                // Send notification to all users who's lineup player club changed
                                if(!empty($club_change_player)){
                                       
                                        echo "<pre>\nContestUniqueID : ".$lineup_master['contest_unique_id'];
                                        echo "\t UserID : ".$lineup_master['user_id'];
                                       
                                        foreach ($player_team_temp as $key => $team_player){
                                           
                                                // If player new club is not in selected match's club
                                           
                                                if(!in_array($key, $match_team_id_arr)){
                                                        foreach($team_player as $player){
                                                           
                                                                ## if mail already sent, it means rule_violate flag already updated
                                                                # and no need to send again rule_violate type notification
                                                                if($club_change_player[$player]['is_club_mail_send'] == '0') {
                                                                        echo "<br>\t Player-id: $player :: is_rule_violate: 1 :: club out of match";

                                                                        $this->db->where(array('lineup_id' => $club_change_player[$player]['lineup_id']));
                                                                        $this->db->update(LINEUP, array('is_rule_violate' => '1'));

                                                                        $is_club_out_of_match = true;
                                                                }
                                                        }
                                                }
                                                else if(count($team_player) > $max_player_per_team){

                                                        ## set number of players who voilating rule
                                                        $cnt = count($team_player) - $max_player_per_team;
                                                       
                                                        ## loop player of same team id
                                                        foreach($team_player as $player){

                                                                ## if player have club change then set the flag is_rule_violate to 1

                                                                if(!empty($club_change_player[$player])){

                                                                        //if( $cnt > 0){
                                                                        ## if mail already sent, it means rule_violate flag already updated
                                                                        # and no need to send again rule_violate type notification
                                                                        if($club_change_player[$player]['is_club_mail_send'] == '0') {
                                                                       
                                                                                echo "<br>\t Player-id: $player :: is_rule_violate: 1";

                                                                                $this->db->where(array('lineup_id' => $club_change_player[$player]['lineup_id']));
                                                                                $this->db->update(LINEUP, array('is_rule_violate' => '1'));
                                                                                $is_rule_violate = true;
                                                                                $cnt--;
                                                                        }
                                                                        //}
                                                                }

                                                        }
                                                }
                                           
                                        }
                                        $notify_player = array();
                                        foreach($club_change_player as $ccp_key => $change_player){
                                                if($change_player['is_club_mail_send'] == '0') {   

                                                        $this->db->where(array('lineup_id' => $change_player['lineup_id']));
                                                        $this->db->update(LINEUP, array('is_club_mail_send' => '1'));
                                                       
                                                        $notify_player[$ccp_key] = $change_player;
                                                }       
                                        }
                                        // Send mail for players whose club change and notification for those not send yet
                                        if(!empty($notify_player)){
                                            //echo "\n notify_player: "; print_r($notify_player);
                                                $this->mail_model->player_club_change_mail($game_data, $lineup_master, $notify_player, $is_rule_violate, false, $is_club_out_of_match);
                                        }
                                }
                        }
                    }
                }
        }
        
        /**
	 * [distribute_contest_winning_point description]
	 * @method  distribute_contest_winning_point
	 * @Summary This method distribute points to contest winner to gets user rating
         * @param string $contest_unique_id
         * @param number $entry_fee
         */
        public function distribute_contest_winning_point($contest_unique_id, $entry_fee){
                
                echo "<br>DISTRIBUTE WINNING POINTS  :: contest_unique_id: $contest_unique_id ($ $entry_fee)";
            
                ## Get all user who wins this game
                $this->db->select('LM.lineup_master_id, LM.contest_unique_id, U.user_id, U.game_win_points', FALSE);
		
		$this->db->from(LINEUP_MASTER.' AS LM')
				->join(USER.' AS U', 'U.user_id=LM.user_id', 'INNER')
				->where('LM.contest_unique_id'  , $contest_unique_id)
				->where('LM.is_winner'          , '1')
                                ->group_by('LM.contest_unique_id, LM.user_id');
                
                $result = $this->db->get();
                //echo $this->db->last_query();die;
                $winners_detail = $result->result_array();
                
                if(!empty($winners_detail)){
                    
                        $winning_point = ($entry_fee > 0) ? GAME_WIN_POINT_PAID : GAME_WIN_POINT_FREE;
                    
                        foreach($winners_detail as $winner) {
                            
                                ## Distribute Game Winning Points
                                echo " <br>  user_id: ".$winner['user_id']." :: point_earn: ".$winning_point; 
                
                                $point_data = array(
                                        'contest_unique_id'         => $winner['contest_unique_id'],
                                        'user_id'                   => $winner['user_id'],
                                        'bonus_amount'              => $winning_point,
                                        'bonus_type'                => BONUS_TYPE_GAME_WIN_POINT,
                                        'amount_type'               => BONUS_AMOUNT_TYPE_POINT,
                                        'added_date'                => format_date()
                                );
                                $this->db->insert(BONUS_CODE_EARNING, $point_data);
                                $this->db->insert_id();

                                ## Update User Game Winning Points
                                $this->db->where('user_id', $winner['user_id']);
                                $this->db->update(USER, array('game_win_points' => $winner['game_win_points'] + $winning_point));
                        }
                }
        }
        
        /**
	 * [distribute_big_game_bonus description]
	 * @method  distribute_big_game_bonus
	 * @Summary This method distribute bonus cash/points for Paid Game and Big Game Join Offer
	 * @param string $contest_unique_id
         * @param array $paid_game_join_offer
         * @return  [type]
	 */
        public function distribute_paid_and_big_game_bonus($contest_unique_id, $paid_game_join_offer=array()){
            
                ## Get all user who joined this game
                $this->db->select('LM.lineup_master_id, LM.contest_unique_id, U.user_id, U.bonus_balance, U.game_join_points', FALSE);
		
		$this->db->from(LINEUP_MASTER.' AS LM')
				->join(USER.' AS U', 'U.user_id=LM.user_id', 'INNER')
				->where('LM.contest_unique_id', $contest_unique_id)
                                ->group_by('LM.contest_unique_id, LM.user_id');
                
                $result = $this->db->get();
                //echo $this->db->last_query();die;
                $participants_detail = $result->result_array();
                
                if(!empty($participants_detail)){
                    
                        foreach($participants_detail as $participant){
                            
                                ## Distribute Big Game Join Bonus if this game is big game join bonus game
                                if(BONUS_BIG_GAME_UNIQUE_ID ==  $contest_unique_id) {
                                        $this->distribute_bonus_to_user(BONUS_TYPE_BIG_GAME_JOIN, BONUS_ON_BIG_GAME_JOIN, TRANSACTION_HISTORY_BONUS_CREDITED, $participant);
                                }
                                
                                ## Distribute Game Joining Points
                                if(!empty($paid_game_join_offer)) {
                                        $this->distribute_paid_game_join_bonus($participant, $paid_game_join_offer);
                                }
                            
                        }
                }
        }
        
        /**
         * This method allocate points and convet points into bonus cash at paid game join
         * @param type $participant
         * @param type $offer_detail
         */
        private function distribute_paid_game_join_bonus($participant, $offer_detail){
            echo "<br>PRIVATE GAME CREATE BONUS ";
            
                $offer_extra_param = json_decode($offer_detail['extra_param'], true);
                /*
                 * Commented becoz min contest join limit not application now
                 * 
                ## Get User Joined Game Count
                $this->db->select("COUNT(DISTINCT(C.contest_unique_id)) contest_count", FALSE);
		$this->db->from(CONTEST.' AS C')
				->join(LINEUP_MASTER.' AS LM', 'LM.contest_unique_id=C.contest_unique_id', 'LEFT')
                                ->where('LM.user_id', $participant['user_id'])
				->where('C.prize_distributed', '1')
				->where('C.status', 'closed')
				->where('C.is_cancel', '0');
                
                if(!empty($offer_detail['extra_param'])) {
                        
                        $this->db->having('contest_count > ', $offer_extra_param['min_contest_joined']);
                }
                
                $result = $this->db->get();
                //echo $this->db->last_query();die;
                $game_count = $result->row_array();
                */
                echo " :: contest_unique_id: ".$participant['contest_unique_id']." :: user_id: ".$participant['user_id']; 
                
                //if(!empty($game_count)){
                    
                        //echo " :: joined_game_count: ".$joined_game_count = $game_count['contest_count'];
                        
                        //if($joined_game_count > $offer_extra_param['min_contest_joined']){
                            
                                ## Insert PAID Game Join Points
                                $point_data = array(
                                        'contest_unique_id'         => $participant['contest_unique_id'],
                                        'user_id'                   => $participant['user_id'],
                                        'bonus_amount'              => $offer_extra_param['per_game_point_earn'],
                                        'bonus_type'                => BONUS_TYPE_PAID_GAME_JOIN_POINT,
                                        'amount_type'               => BONUS_AMOUNT_TYPE_POINT,
                                        'added_date'                => format_date()
                                );
                                //echo "<br>Point Data:"; print_r($point_data);
                                $this->db->insert(BONUS_CODE_EARNING, $point_data);
                                $this->db->insert_id();

                                //Add notification
                                //$this->Finance_model->add_notification(NOTIFY_ADD_FUND, 0, $result['user_id'], $amount);

                                ## Update User Bonus Amount
                                $total_game_join_points = $participant['game_join_points'] + $offer_extra_param['per_game_point_earn'];
                                $this->db->where('user_id', $participant['user_id']);
                                $this->db->update(USER, array('game_join_points' => $total_game_join_points));
                                
                                echo " :: point_earn: ".$offer_extra_param['per_game_point_earn'];
                                echo " :: total_points: ".$total_game_join_points;
                                
                                ####### Now Convert POINTS into BONUS CASH #########
                                
                                $offer_value = json_decode($offer_detail['offer_value'], true);
                                $offer_value = multidim_array_sort($offer_value, 'point_earned', SORT_DESC);
                                
                                foreach($offer_value as $offer) {

                                        ## Check whether user is eligible or not for the offer cases

                                        if($total_game_join_points >= $offer['point_earned']){

                                                ## Check if offer already taken 

                                                $taken_where = array(
                                                    'user_id'               => $participant['user_id'],
                                                    'offer_id'              => $offer_detail['offer_id'],
                                                    'offer_entity'          => 'point_earned',
                                                    'offer_entity_value'    => $offer['point_earned']
                                                );
                                                $offer_taken = $this->common_model->get_single_row('offer_taken_id',OFFER_TAKEN, $taken_where);

                                                if(empty($offer_taken['offer_taken_id'])){

                                                    ## If not taken yet, allocate bonus cash and insert data into offer_taken table and Exit from this offer cases

                                                    echo " :: bonus_cash: ".$offer['bonus'];

                                                    # allocate bonus cash to user
                                                    unset($participant['contest_unique_id']);
                                                    unset($participant['lineup_master_id']);
                                                    
                                                    $this->distribute_bonus_to_user(BONUS_TYPE_GAME_JOIN_POINT_CONVERT_BONUS, $offer['bonus'], TRANSACTION_HISTORY_GAME_JOIN_POINT_CONVERT_BONUS, $participant);

                                                    # Insert offer_taken data
                                                    $data_taken = $taken_where;
                                                    $data_taken['value_taken'] = json_encode(array('point_earned'=>$offer['point_earned'], 'bonus' => $offer['bonus']));
                                                    $data_taken['added_date'] = format_date();

                                                    $this->db->insert(OFFER_TAKEN, $data_taken);
                                                    $this->db->insert_id();

                                                }
                                                break;
                                        }
                                }
                        //}
                //}
                
        }
        
        /**
	 * [distribute_bonus_to_user description]
	 * @method  distribute_bonus_to_user
	 * @Summary This method distribute bonus cash to user 
         * @param   string $bonus_type
         * @param   integer $bonus_amount
         * @param   array $user_data
         * @return  boolean
	 */
        private function distribute_bonus_to_user($bonus_type, $bonus_amount, $txn_history_type, $user_data) {
                // Insert Bonus Payment History Data
                $payment_history = array();
                
                if(!empty($user_data['contest_unique_id'])) {
                    $payment_history['contest_unique_id']       = $user_data['contest_unique_id'];
                }
                if(!empty($user_data['lineup_master_id'])) {
                    $payment_history['lineup_master_id']        = $user_data['lineup_master_id'];
                }
                $payment_history['user_id']                     = $user_data['user_id'];
                $payment_history['payment_type']                = CREDIT;
                $payment_history['master_description_id']       = $txn_history_type;
                $payment_history['transaction_amount']          = $bonus_amount;
                $payment_history['user_balance_at_transaction'] = $user_data['bonus_balance'];
                $payment_history['amount_type']                 = AMOUNT_TYPE_BONUS;
                $payment_history['created_date']                = format_date();
                $payment_history['is_processed']                = '1';

                $this->db->insert(PAYMENT_HISTORY_TRANSACTION, $payment_history);
                $this->db->insert_id();

                // Insert Bonus Earning Data
                $data_array = array();
                $data_array['user_id']                      = $user_data['user_id'];
                $data_array['bonus_amount']                 = $bonus_amount;
                $data_array['added_date']                   = format_date();
                $data_array['bonus_type']                   = $bonus_type;
                $data_array['amount_type']      = BONUS_AMOUNT_TYPE_CASH;
                
                $this->db->insert(BONUS_CODE_EARNING, $data_array);
                $this->db->insert_id();

                //Add notification
                //$this->Finance_model->add_notification(NOTIFY_ADD_FUND, 0, $result['user_id'], $amount);

                // Update User Bonus Amount
                $this->db->where('user_id', $user_data['user_id']);
				$this->db->update(USER, array('bonus_balance' => $user_data['bonus_balance'] + $bonus_amount));
				
				if($bonus_amount > 0) {
					$this->load->helper('queue_helper');
					$bonus_data = array('oprator' => 'add', 'user_id' => $user_data["user_id"], 'total_bonus' => $bonus_amount, 'bonus_date' => format_date("today", "Y-m-d"));
					add_data_in_queue($bonus_data, 'user_bonus');	
				}
        }
        
        /**
	 * [private_game_create_bonus description]
	 * @method  private_game_create_bonus
	 * @Summary This method distribute bonus cash for Private Game Create Offer
         * @return  boolean
	 */
        public function private_game_create_bonus(){
                //Check url hit by server or manual
		$this->check_url_hit();
            
                echo "<br>PRIVATE GAME CREATE BONUS ";
                ## Get Private Game Create Offer data
                
                $this->db->select('*', false);
                $this->db->from(OFFER.' AS O');
		$this->db->where('O.offer_type', BONUS_TYPE_PRIVATE_GAME_CREATE);
		$this->db->where('O.status', '1');
                
                $offer_query = $this->db->get();
                $offer_detail = $offer_query->row_array();
                
                if(empty($offer_detail)){
                    return 1;
                }
            
                ## Get Users data who have created 10 or more than 10 completed private games 
            
                $this->db->select('COUNT(C.contest_unique_id) AS contest_count, U.user_id, U.bonus_balance', false);
		
		$this->db->from(CONTEST.' AS C')
				->join(USER.' AS U', 'U.user_id=C.user_id', 'INNER')
				->where('C.contest_access_type', '1')
				->where('C.user_id > ', 0)
				->where('C.prize_distributed', '1')
				->where('C.status', 'closed');
                
                if(!empty($offer_detail['extra_param'])){
                        $contest_limit = json_decode($offer_detail['extra_param'], TRUE);
                        $this->db->having('contest_count >= ', $contest_limit['contest_limit']);
                }
                
                $this->db->group_by('C.user_id');
                
                $result = $this->db->get();
                //echo $this->db->last_query(); die;
                $user_list = $result->result_array();
                
                if(!empty($user_list)) {
                    
                        if(!empty($offer_detail['offer_value'])){
                            
                            $offer_value = json_decode($offer_detail['offer_value'], true);
                            $offer_value = multidim_array_sort($offer_value, 'contest_count', SORT_DESC);
                                
                                foreach($user_list as $user){
                                    
                                    echo "<br>user_id:".$user['user_id']." :: contest_count:".$user['contest_count'];
                                    
                                        foreach($offer_value as $offer){

                                                ## Check whether user is eligible or not for the offer cases

                                                if($user['contest_count'] >= $offer['contest_count']){

                                                        ## Check if offer already taken 

                                                        $taken_where = array(
                                                            'user_id'               => $user['user_id'],
                                                            'offer_id'              => $offer_detail['offer_id'],
                                                            'offer_entity'          => 'contest_count',
                                                            'offer_entity_value'    => $offer['contest_count']
                                                        );
                                                        $offer_taken = $this->common_model->get_single_row('offer_taken_id',OFFER_TAKEN, $taken_where);

                                                        if(empty($offer_taken['offer_taken_id'])){

                                                            ## If not taken yet, allocate bonus cash and insert data into offer_taken table and Exit from this offer cases

                                                            echo " :: bonus:".$offer['bonus'];

                                                            # allocate bonus cash to user
                                                            $this->distribute_bonus_to_user(BONUS_TYPE_PRIVATE_GAME_CREATE, $offer['bonus'], TRANSACTION_HISTORY_BONUS_CREDITED, $user);

                                                            # Insert offer_taken data
                                                            $data_taken = $taken_where;
                                                            $data_taken['value_taken'] = json_encode(array('contest_count'=>$offer['contest_count'], 'bonus' => $offer['bonus']));
                                                            $data_taken['added_date'] = format_date();

                                                            $this->db->insert(OFFER_TAKEN, $data_taken);
                                                            $this->db->insert_id();

                                                        }
                                                        break;
                                                }
                                        }
                                }
                        }
                }
        }
        
        /**
	 * [set_user_rating description]
	 * @method  set_user_rating
	 * @Summary This method set users rating as per their game_win_points 
         * @return  boolean
	 */
        public function set_user_rating(){
                //Check url hit by server or manual
		$this->check_url_hit();
                
                $sql = "
                        SELECT 
                                IF (@score=U.game_win_points, @rank:=@rank, @rank:=@rank+1) rank, 
                                @score:=U.game_win_points score, 
                                U.user_id, U.game_win_points 
                        FROM 
                                ".$this->db->dbprefix(USER)." AS U
                        JOIN 
                                (SELECT  @score:=0, @rank:=0) R 
                        ORDER BY 
                                U.game_win_points DESC
                        ";
                
                $query = $this->db->query($sql);
		$user_rating = $query->result_array();
                
                if(!empty($user_rating)) {
                    
                        $update_rank = array();
                        
                        foreach ($user_rating as $user) {
                                //echo "<br>user_id: ".$user['user_id']." :: score: ". $user['game_win_points']." :: rank: ".$user['rank'];
                                $update_rank[] = array(
					"user_id" => $user['user_id'],
					"user_rank" => $user['rank']
				);
                        }
                        $this->db->update_batch(USER, $update_rank, 'user_id');
                        
                        ## USER PROFILING
                        $this->set_user_profiling();
                }
        }
        
        public function set_user_profiling() {
            
                #Get User Matches Played Count
		$matches_played = "SELECT COUNT(LM.contest_unique_id) FROM ".$this->db->dbprefix(LINEUP_MASTER)." AS LM INNER JOIN ".$this->db->dbprefix(CONTEST)." G ON 
		G.contest_unique_id = LM.contest_unique_id WHERE G.is_cancel = '0' AND U.user_id = LM.user_id";

		#Get User Matches Won Count
		$matches_won = "SELECT COUNT(LM.lineup_master_id) FROM ".$this->db->dbprefix(LINEUP_MASTER)." AS LM INNER JOIN ".$this->db->dbprefix(CONTEST)." AS G ON 
		G.contest_unique_id = LM.contest_unique_id WHERE G.is_cancel = '0' AND LM.is_winner = '1' AND U.user_id = LM.user_id ";

//                $this->db->select("U.user_id, U.user_rank, ($matches_played) as matches_played, ($matches_won) as matches_won", FALSE)
//				->from(USER." AS U")
//                                
//				->where("U.game_win_points !=", 0) // temp
//				//->where("U.user_id IN ('50','51')") // temp
//                                ->order_by("U.user_rank ASC")
//				->group_by('U.user_id');
                
                $sql = "SELECT U.user_id, U.user_rank, WPC3.top_3_count, WPC5.top_5_count, ($matches_played) as matches_played, ($matches_won ) as matches_won
                                FROM 
                                        `vi_user` AS `U` 
                                LEFT JOIN (
                                                SELECT count(winner_place) as top_3_count, LMP.user_id FROM ".$this->db->dbprefix(LINEUP_MASTER)." AS LMP 
                                                INNER JOIN ".$this->db->dbprefix(CONTEST)." AS G ON G.contest_unique_id = LMP.contest_unique_id 
                                                WHERE G.contest_type != '0' AND LMP.winner_place <= 3 group by LMP.user_id 
                                        ) AS WPC3 ON WPC3.user_id = U.user_id
                                        
                                LEFT JOIN (
                                                SELECT count(winner_place) as top_5_count, LMP.user_id FROM ".$this->db->dbprefix(LINEUP_MASTER)." AS LMP 
                                                INNER JOIN ".$this->db->dbprefix(CONTEST)." AS G ON G.contest_unique_id = LMP.contest_unique_id 
                                                WHERE G.contest_type != '0' AND LMP.winner_place <= 5 group by LMP.user_id 
                                        ) AS WPC5 ON WPC5.user_id = U.user_id
                                        
                            GROUP BY U.user_id
                            ORDER BY U.user_rank ASC";
                
                $query = $this->db->query($sql);
                $user_list = $query->result_array();
                
                if(!empty($user_list)){
                        
                        $users_rank = array_column($user_list, 'user_rank');
                        $max_rank = max($users_rank);
                        
                        //$max_rank= 6;
                        
                        $advance_rank_to = (int)(($max_rank*50)/100);
                        
                        if ($max_rank == 1) {
                                $advance_rank_to = 1;
                                $intermediate_rank_to = 2;
                        } else {
                                $intermediate_rank_to = $max_rank;
                        }
                        
                        $winnig_ration = json_decode(USER_LEVEL_WIN_RATIO, TRUE);

                        echo '<br> max_rank: '. $max_rank.' ::  rating slot:  advance=> '.$advance_rank_to.' :: intermediate => '.$intermediate_rank_to.'<br>';
                        
                        foreach($user_list as $user){
                            
                                if($user['matches_won'] > 0 && $user['matches_played'] > 0){
                                        $win_ratio = (float) round(((100 * $user['matches_won']) / $user['matches_played']), 2);
                                } else {
                                    $win_ratio = 0;
                                }
                                
                                echo "<br> userid: ".$user['user_id'].'  ::  top_3: '.$user['top_3_count'].'  ::  top_5: '.$user['top_5_count'].'  ::  rank: '.$user['user_rank'].'  ::  win_ratio :'. $win_ratio.'  ::  played:'.$user['matches_played'].'  ::  win:'.$user['matches_won'].'  ::  ';
                                
                                if( ($win_ratio >= $winnig_ration[USER_LEVEL_ADVANCE]) && $user['top_3_count'] >= USER_LEVEL_WINNER_PLACE_COUNT && ($user['user_rank'] <= $advance_rank_to) ) {
                                    echo ' Advanced ';
                                    $user_level = USER_LEVEL_ADVANCE;
                                }
                                else if( ($win_ratio >= $winnig_ration[USER_LEVEL_INTERMEDIATE]) && $user['top_5_count'] >= USER_LEVEL_WINNER_PLACE_COUNT && ($user['user_rank'] <= $intermediate_rank_to) ) {
                                    echo ' Intermediate ';
                                    $user_level = USER_LEVEL_INTERMEDIATE;
                                }
                                else {
                                    echo ' Beginner ';
                                    $user_level = USER_LEVEL_BEGINNER;
                                }
                                
                                $upd_user_level[] = array(
					"user_id"       => $user['user_id'],
					"user_level"    => $user_level
				);
                        }
                        
                        // Update User Level
                        $this->db->update_batch(USER, $upd_user_level, 'user_id');
                }
        }
}