<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Live_stock_fantasy_model extends Cron_model {
    
    public $db_user;
    public $market_id = 1;
    public $market = array('1' => 'NSE');
    public $stock_type = 4;
    public function __construct() {
      parent::__construct();
      $this->db_user    = $this->load->database('user_db', TRUE);
      $this->db_stock   = $this->load->database('stock_db',TRUE);
    }


    function update_trade_value($type=1)
    {
        $trade_collections = array();
        $trade_collections = $this->get_transactions($type);
        //echo '<pre>';print_r($trade_collections);die;
        $stock_data_update = [];
        $brokerage = '0.00';
        if(!empty($trade_collections)){
          $this->db->trans_strict(TRUE);
          $this->db->trans_start();
          foreach ($trade_collections as $key => $value) {
              $today = date('Y-m-d 09:15:00');
              $select_sql = $this->db->query("SELECT SHD.close_price from  ".$this->db->dbprefix(STOCK_HISTORY_DETAILS)." AS SHD where SHD.schedule_date= 
                '".$today."' and SHD.stock_id = ".$value['stock_id']."");
              $close_price = $select_sql->row_array();
              if(empty($close_price)){
                $select_sql = $this->db->query("SELECT SH.close_price from  ".$this->db->dbprefix(STOCK_HISTORY)." AS SH where SH.schedule_date
                  = DATE_FORMAT('".$today."','%Y-%m-%d') and SH.stock_id = ".$value['stock_id']."");
                $close_price = $select_sql->row_array();
              }
              
          if(!empty($close_price) && $type == 1) {
            if($value['brokerage']   <= 0 ){
                $value['brokerage'] = 0.00;
            }
            $old_trade_value = $value['trade_value'];
            $lot_size = floor($old_trade_value / $close_price['close_price'] * 100 / 100);
            $new_trade_value = $close_price['close_price'] * $lot_size; //value without brokerage
            if($value['contest_brokerage'] > 0 )
            {
               /*new brokerage*/
              $brokerage = round($new_trade_value * $value['contest_brokerage'] / 100,2);
              $new_trade_value = round($new_trade_value + $brokerage,2); //final trade value with brokerage
            }

            if($lot_size > 0 )
            {
              $return_value = (($value['trade_value'] + $value['brokerage']) - $new_trade_value) ; //the value to return tin portfolio
              $status = 1;
            }else{
               $return_value = $value['trade_value'] +  $value['brokerage'];
               $status = 2;
            }

            $lineup = $this->get_single_row('team_data',LINEUP_MASTER,['lineup_master_id'=>$value['lineup_master_id']]);
            if(!empty($lineup)){
                $team_data = json_decode($lineup['team_data'],1);
                $team_data['stocks'][$value['stock_id']] = $lot_size;
                
                if($lot_size > 0 ){ 
                  $this->db->where('lineup_master_id',$value['lineup_master_id']);
                  $this->db->update(LINEUP_MASTER,['team_data'=>json_encode($team_data)]);
                }

            }
            //update final score to lineup master contest
            $total_score = $value['total_score'] + $return_value;
            $update_score = ['total_score'=>$total_score,'last_score'=>$total_score];

            $this->db->where('lineup_master_id',$value['lineup_master_id']);
            $this->db->where('contest_id',$value['contest_id']);
            $this->db->update(LINEUP_MASTER_CONTEST,$update_score);

            if($type == 1){
                $update_array = array(
                  'trade_value' => $new_trade_value,
                  'status'=>$status,
                  'lot_size'=>$lot_size,
                  'price'=>$close_price['close_price'],
                  'modified_date'=>format_date()
                );
                $this->db->where('transaction_id',$value['transaction_id']);
                $this->db->update(USER_TRADE,$update_array);

                if($brokerage > 0 )
                {
                  $this->db->where('parent_id',$value['transaction_id']);
                  $this->db->update(USER_TRADE,['brokerage'=>$brokerage,'status'=>1]);

                }
            }
            

             }

             if(!empty($close_price) && $type == 2)
             {
                $new_trade_value = round($close_price['close_price'] * $value['lot_size'],2);

                    $get_current_score = $this->get_single_row('total_score',LINEUP_MASTER_CONTEST,['lineup_master_contest_id'=>$value['lineup_master_contest_id']]);
     
                  $total_score = round($get_current_score['total_score'] + $new_trade_value,2);
                  $update_score = ['total_score'=>$total_score,'last_score'=>$total_score];
                  $this->db->where('lineup_master_id',$value['lineup_master_id']);
                  $this->db->where('contest_id',$value['contest_id']);
                  $this->db->update(LINEUP_MASTER_CONTEST,$update_score);

                   $update_array = array(
                    'trade_value' => $new_trade_value,
                    'status'=>1,
                    'modified_date'=>format_date(),
                    'price'=>$close_price['close_price']
                  );
                  $this->db->where('transaction_id',$value['transaction_id']);
                  $this->db->update(USER_TRADE,$update_array);


              }
              
             /* $old_trade_value = '';
              $new_trade_value = 0; 
              $return_value = '';
              $lot_size = '';
              $total_score =0;*/

               $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE ) {
                  $this->db->trans_rollback();
                } else {
                  $this->db->trans_commit();
                }
          }
      

    }
  }

    function get_transactions($type) {

      $this->db->select('UT.stock_id,UT.lot_size,UT.lineup_master_id,UT.trade_value,UT.contest_id,UT.price,UT.transaction_id,UT1.brokerage,LMC.total_score,LMC.last_score,C.brokerage as contest_brokerage,C.is_tie_breaker,LMC.lineup_master_contest_id')
            ->from(USER_TRADE. " UT")
            ->join(USER_TRADE. " UT1","UT.transaction_id = UT1.parent_id","LEFT")
            ->join(LINEUP_MASTER. " LM","LM.lineup_master_id = UT.lineup_master_id")
            ->join(LINEUP_MASTER_CONTEST. " LMC","LMC.lineup_master_id = UT.lineup_master_id")
            ->join(CONTEST. " C","C.contest_id = LMC.contest_id")
            
            ->where('UT.status',0)
            ->where('UT.parent_id',0)
            ->where('C.status',0);
            if($type == 2)
            {
              $this->db->where_in('UT.type',[2,3]);
            }else{
              $this->db->where('UT.type',$type);
            }
          $result =  $this->db->get()->result_array();
            //echo $this->db->last_query();die;
      return $result;
    }



    /**
     * @Summary: This function for use for update fantasy plaer points to lineup and lineup master table after caclulation of fantasy points 
     * database.
     * @access: public
     * @param:$league_id
     * @return:
     */
    public function update_rank_in_portfolio_by_collection($collection_id="") {

        $current_date = format_date();
        if(empty($collection_id)) {
          $current_game = $this->get_collection_for_point_update($this->stock_type);
        } else {
          $one_collection =$this->get_single_row('collection_id,scheduled_date,published_date,end_date,stock_type',COLLECTION,array('collection_id' => $collection_id));
          $current_game[] = $one_collection;
        }
        /*echo '<pre>';
        print_r($current_game);die;*/
        foreach ($current_game as $key => $value) {
         
        $sql = $this->db->select('LM.lineup_master_id,LM.user_id,C.contest_id')
                                        ->from(LINEUP_MASTER . " AS LM")
                                        ->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.lineup_master_id = LM.lineup_master_id", 'INNER')
                                        ->join(CONTEST . " AS C", "C.contest_id = LMC.contest_id AND C.status != 1 ", 'INNER')
                                        ->where('LM.collection_id', $value['collection_id'])
                                        ->group_by('LM.lineup_master_id')
                                        ->get();
                $lineup_master_ids = $sql->result_array();
              /* echo $this->db->last_query();
                 echo '<pre>' ;
                print_r($lineup_master_ids);die;*/

              if(!empty($lineup_master_ids)){
                  foreach ($lineup_master_ids as $lineup_key => $lineup_value) {

                     $contest_id = $lineup_value['contest_id'];

                     $update_rank_sql = "UPDATE 
                                ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                            INNER JOIN 
                                (SELECT LMC1.lineup_master_contest_id,RANK() OVER (ORDER BY `total_score` DESC ) user_rank 
                                  FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC1 
                                  WHERE LMC1.contest_id = ".$contest_id.") AS L_PQ 
                            ON L_PQ.lineup_master_contest_id = LMC.lineup_master_contest_id 
                            SET 
                                LMC.game_rank = IFNULL(L_PQ.user_rank,'0')
                            WHERE LMC.fee_refund=0 ";
                        $this->db->query($update_rank_sql);
                        //echo $this->db->last_query();die;

                        $score_updated_date = $current_date;
                        $this->db->set('score_updated_date',$score_updated_date);
                        $this->db->where('collection_id', $value['collection_id']);        
                        $this->db->update(COLLECTION);

                    }
              }

        }
    }


      /**
  * Used for update contest status
  * @param int $sports_id
  * @return string print output
  */
  public function update_lsf_contest_status()
  {
    $current_game = $this->get_collection_to_update();
      
    if(empty($current_game)){
      return false;
    }
    //echo "<pre>conets season: ";print_r($current_game);die;
    if(!empty($current_game))
    {
      $current_date = format_date();
      $all_collection_id = array_column($current_game, 'collection_id');
      $all_collection_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; }, $all_collection_id));
      $sql = $this->db->select('G.collection_id,G.contest_id,G.contest_unique_id,G.contest_name,G.status')
                 ->from(CONTEST." AS G")
                 ->join(COLLECTION." C","G.collection_id=C.collection_id")
                 ->where("C.status", 0)
                 ->where("C.stock_type",$this->stock_type)
                 ->where("G.collection_id IN ($all_collection_id_str)")
                 ->where("G.status", 0)
                 ->where("G.total_user_joined >= ",'G.minimum_size', FALSE)
                 ->get();
      $contest_data = $sql->result_array();
      //echo "<pre>";print_r($contest_data);die;
      if(!empty($contest_data))
      {
        $contest_ids = array_column($contest_data, 'contest_id');
        $score_check = $this->db->select("count(*) as total")
              ->from(LINEUP_MASTER_CONTEST)
              ->where_in("contest_id",$contest_ids)
              ->where("total_score >= ","0")
              ->where("fee_refund","0")
              ->get()
              ->row_array();
          /*$score_check = $this->db->select("SUM(LM.team_data is not null) as team_data")
              ->from(LINEUP_MASTER_CONTEST. " LMC")
              ->join(LINEUP_MASTER . " LM","LM.lineup_master_id=LMC.lineup_master_id")
              ->where_in("LMC.contest_id",$contest_ids)
              ->where("LMC.total_score >= ","0")
              ->where("LMC.fee_refund","0")
              ->get()
              ->row_array();*/
          //echo $this->db->last_query();die;
        if(!empty($score_check) && isset($score_check['total']) && $score_check['total'] > 0){
         /* if(empty($score_check['team_data'])) { */
          $collection_ids = array_unique(array_column($contest_data,'collection_id'));
          // Mark CONTEST Status Complete
          $this->db->where_in("contest_id", $contest_ids);
          $this->db->where_in("collection_id", $collection_ids);
          $this->db->where("status", 0);
          $this->db->update(CONTEST, array("status" => 2,"modified_date" => $current_date ));

          // Mark COLLECTION Status Complete
          $this->db->where_in("collection_id", $collection_ids);
          $this->db->update(COLLECTION, array("status" => 1, "modified_date" => $current_date));

          //delete contest cache
          foreach($contest_ids as $contest_id) {
            $del_cache_key = 'st_contest_'.$contest_id;
            $this->delete_cache_data($del_cache_key);
          }

          //delete lobby filters data
          $del_cache_key = 'st_lobby_filters';
          $this->delete_cache_data($del_cache_key);

          echo "Update status for contest having collection_ids: ".implode(',', $collection_ids)." ";   
        }
      } 
      else{
        //if all contest of any collection cancelled then collection status update
        $this->update_contest_collection_status($all_collection_id_str);
        echo "No contest status update ";
      }
    }
    else
    {
      echo "No contest status update ";
    }
  }

   function update_contest_collection_status($all_collection_id_str='')
   {
   
      if(empty($all_collection_id_str))
      {
        return false;
      }

        $sql = $this->db->select('G.collection_id, G.contest_id, G.contest_unique_id, G.contest_name, G.status')
        ->from(CONTEST." AS G")
        ->join(COLLECTION." C","G.collection_id=C.collection_id")
        ->where("C.collection_id IN ( $all_collection_id_str )")
        ->where("C.collection_id > ", 0)
        ->where("G.status", 1)
        ->get();
          //echo $this->db->last_query();die();
          $collection_data = $sql->result_array();
          if(!empty($collection_data))
          {
              $collection_id = array();
              $collection_id = array_column($collection_data,'collection_id');
              $this->db->where_in("collection_id", $collection_id);
              $this->db->update(COLLECTION, array("status" => 1, "modified_date" => format_date()));
          }
   }

  private function get_collection_to_update()
  {
      $current_date_time = format_date();
       
        $this->db->select("collection_id, scheduled_date,status");
        $this->db->from(COLLECTION);
        $this->db->where("status", '0');
        $this->db->where("is_lineup_processed",1);
        $this->db->where("DATE_ADD(end_date,INTERVAL 10 MINUTE) < ", $current_date_time);
        $this->db->where('stock_type',$this->stock_type);
        $sql = $this->db->get();
        //echo $this->db->last_query(); die;
        $matches = $sql->result_array();
        return $matches;

  }

/**
   * Auto execute transaction when contest end
   */
  function update_contest_end_transactions()
  {    
      $collections = $this->get_collection_on_contest_end();
      if(empty($collections)){
        return false;
      }


      $lineup_details = array_column($collections,NULL,'lineup_master_id');
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      foreach ($lineup_details as $lineup_master_id => $lineup_data) {

            /* $sql = $this->db->select('UT.transaction_id,UT.stock_id')
                                        ->from(USER_TRADE . " AS UT")
                                        ->where('UT.lineup_master_id', $lineup_master_id)
                                        ->where('UT.contest_id', $lineup_data['contest_id'])
                                        ->where('UT.status',0)
                                        ->where('UT.type',1)
                                        ->where('UT.parent_id',0)
                                        ->get();
                $lineup_master_ids = $sql->result_array();
                if(empty($lineup_master_ids)){*/
            if(!empty($lineup_data['team_data']))
            {
                  $stocks = json_decode($lineup_data['team_data'],1);
                    
                  if($stocks){

                      foreach ($stocks['stocks'] as $stock_id => $stock_value) {

                          $select_sql = $this->db->query("SELECT SHD.close_price from  ".$this->db->dbprefix(STOCK_HISTORY_DETAILS)." AS SHD where SHD.schedule_date_utc= 
                          '".$lineup_data['end_date']."' and SHD.stock_id = ".$stock_id."");
                          $close_price = $select_sql->row_array();   
                          if(empty($close_price)){
                            $select_sql = $this->db->query("SELECT SH.close_price from  ".$this->db->dbprefix(STOCK_HISTORY)." AS SH where SH.schedule_date= 
                          DATE_FORMAT('".$lineup_data['end_date']."','%Y-%m-%d') and SH.stock_id = ".$stock_id."");
                            $close_price = $select_sql->row_array();  
                          }

                          if(!empty($close_price['close_price'])){
                             $insert = array(
                              'trade_value' => $stock_value * $close_price['close_price'],
                              'status'=>1,
                              'contest_id'=>$lineup_data['contest_id'],
                              'stock_id'=>$stock_id,
                              'type'=>3,
                              'lot_size'=>$stock_value,
                              'lineup_master_id'=>$lineup_master_id,
                              'price'=>$close_price['close_price'],
                              'added_date'=>format_date(),
                              'modified_date'=>format_date(),
                            );

                            $this->db->insert(USER_TRADE,$insert);
                            $portfolio_score = "SELECT LMC.total_score from  ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC where LMC.lineup_master_id= ".$lineup_master_id."";
                            $portfolio_score = $this->db->query($portfolio_score);
                             $total_score = $portfolio_score->row_array();   

                            $return_value = $total_score['total_score'] + ($stock_value * $close_price['close_price']);
                            $update_score = ['total_score'=>$return_value,'last_score'=>$return_value];
                            $this->db->where('lineup_master_id',$lineup_master_id);
                            $this->db->where('contest_id',$lineup_data['contest_id']);
                            $this->db->update(LINEUP_MASTER_CONTEST,$update_score);


                            unset($stocks['stocks'][$stock_id]);
                            $team_data = json_encode($stocks);
                            if(empty($stocks['stocks']))
                            {
                              $team_data = NULL;
                            }
                            $this->db->where('lineup_master_id',$lineup_master_id); 
                            $this->db->update(LINEUP_MASTER,['team_data'=>$team_data]);
                          }
                          

                      }
                  }

          
                }  

                //Trasaction End
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE ) {
                  $this->db->trans_rollback();
                } else {
                  $this->db->trans_commit();
                }  

      }
  }


   function get_collection_on_contest_end()
  {
       $current_date_time = format_date();
       
        $this->db->select("CM.collection_id,CM.end_date,CM.status,LM.lineup_master_id,LM.team_data,LMC.contest_id,LMC.total_score,C.contest_id");
        $this->db->from(COLLECTION. " CM");
        $this->db->join(LINEUP_MASTER. " LM","LM.collection_id=CM.collection_id");
        $this->db->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.lineup_master_id = LM.lineup_master_id", 'INNER');
        $this->db->join(CONTEST. " C","LMC.contest_id = C.contest_id AND C.status != 1", 'INNER');
        $this->db->where("CM.status", '0');
        $this->db->where("CM.is_lineup_processed",1);
        $this->db->where("CM.end_date < ", $current_date_time);
        $this->db->where('CM.stock_type',$this->stock_type);
        $sql = $this->db->get();
        //echo $this->db->last_query(); die;
        $collections = $sql->result_array();
        //echo $this->db->last_query();die;
      return $collections;
  }


}