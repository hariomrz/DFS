<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'Cron_model.php';
class Stock_predict_model extends Cron_model {
    
    public $db_user;
    public $market_id = 1;
    public $market = array('1' => 'NSE');
    public $stock_type = 3;
    public function __construct() {
      parent::__construct();
      $this->db_user    = $this->load->database('user_db', TRUE);
      $this->db_stock   = $this->load->database('stock_db',TRUE);
    }

    function update_collection_stock_rates($type=1)
    {
        //get live collections
        $collections = array();
        if($type==1)
        {
            $collections = $this->get_live_collections();
        }
        else{
            $collections = $this->get_about_to_complete_collections();

        }

        // echo '<pre>';
        // print_r($collections);
        if(empty($collections))
        {
            return false;
        }

        $stock_ids = array();
        $collection_ids = array();
        foreach($collections as $collection)
        {
          $collection_id = $collection['collection_id'];

          $sql = $this->get_query_to_update_price($type,$collection_id);
          $this->db->query($sql);
        }
        return true;
    }

    function get_query_to_update_price($type,$collection_id)
    {
        if($type==1)
        {
            $sql="UPDATE ".$this->db->dbprefix(COLLECTION_STOCK)."  AS CS
             INNER JOIN
              (SELECT                                   
              CS1.stock_id,SHD.close_price,SHD.schedule_date,CS1.collection_id                       
       
                         FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS1
                         INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS1.collection_id=C.collection_id
                         INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY_DETAILS)." SHD ON SHD.stock_id=CS1.stock_id AND SHD.schedule_date_utc=C.scheduled_date
                         WHERE 
                         C.collection_id =$collection_id
                         )
                         AS GP ON GP.stock_id = CS.stock_id AND CS.collection_id=GP.collection_id
                         
             SET CS.open_price = GP.close_price
             WHERE CS.collection_id=$collection_id AND CS.stock_id=GP.stock_id";
        }
        else{
            $sql="UPDATE ".$this->db->dbprefix(COLLECTION_STOCK)."  AS CS
             INNER JOIN
              (SELECT                                   
                        CS1.stock_id,SHD.close_price,SHD.schedule_date,CS1.collection_id                       
       
                         FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS1
                         INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS1.collection_id=C.collection_id
                         INNER JOIN ".$this->db->dbprefix(STOCK_HISTORY_DETAILS)." SHD ON SHD.stock_id=CS1.stock_id AND SHD.schedule_date_utc=C.end_date
                         WHERE 
                         C.collection_id =$collection_id
                         )
                         AS GP ON GP.stock_id = CS.stock_id AND CS.collection_id=GP.collection_id
                         
             SET CS.close_price = GP.close_price
             WHERE CS.collection_id=$collection_id AND CS.stock_id=GP.stock_id";
        }
        return $sql;
    }

    private function get_live_collections()
    {
        $current_time = format_date();
        $result = $this->db_stock->select('C.collection_id,C.scheduled_date,C.end_date,GROUP_CONCAT(stock_id) as stock_ids',FALSE)
        ->from(COLLECTION.' C')
        ->join(COLLECTION_STOCK.' CS','C.collection_id=CS.collection_id')
        ->where('C.stock_type',$this->stock_type)
        ->where('C.scheduled_date<',$current_time)
        ->where("DATE_FORMAT ( C.scheduled_date ,'%Y-%m-%d %H:%i:%s' ) > DATE_SUB('" . $current_time . "' , INTERVAL 336 HOUR)")
        ->where('C.status',0)
        ->group_by('C.collection_id')
        ->get()->result_array();
        //echo $this->db_stock->last_query();
        return $result;

    }

    private function get_about_to_complete_collections()
    {
        $current_time = format_date();
        $result = $this->db_stock->select('C.collection_id,C.scheduled_date,C.end_date,GROUP_CONCAT(stock_id) as stock_ids',FALSE)
        ->from(COLLECTION.' C')
        ->join(COLLECTION_STOCK.' CS','C.collection_id=CS.collection_id')
        ->where('C.stock_type',$this->stock_type)
        ->where('C.end_date<',$current_time)
        ->where("DATE_FORMAT ( C.end_date ,'%Y-%m-%d %H:%i:%s' ) > DATE_SUB('" . $current_time . "' , INTERVAL 336 HOUR)")
        ->where('C.status',0)
        ->group_by('C.collection_id')
        ->get()->result_array();

        //echo $this->db_stock->last_query();
        return $result;
    }

    /**
     * @Summary: This function for use for update fantasy plaer points to lineup and lineup master table after caclulation of fantasy points 
     * database.
     * @access: public
     * @param:$league_id
     * @return:
     */
    public function update_scores_in_portfolio_by_collection($collection_id="") {

        $current_date = format_date();
        if(empty($collection_id)) {
          $current_game = $this->get_collection_for_point_update(3);//stock type 3 for predict
        } else {
          $one_collection =$this->get_single_row('collection_id,scheduled_date,published_date,end_date,stock_type',COLLECTION,array('collection_id' => $collection_id));
          $current_game[] = $one_collection;
        }
        // echo "<pre>";print_r($current_game);die('ddfd');
        if(!empty($current_game)) {
            $all_collection_id = array_column($current_game, 'collection_id');
            $collection_publish_date_map = array_column($current_game,NULL,'collection_id');
            //$all_collection_id_str = implode(',', array_map( function( $n ){ return '\''.$n.'\''; } ,  $all_collection_id) );
          
            //echo "<pre>";print_r($all_collection_id);die;
            if(!empty($all_collection_id)) {
              //Start Transaction
              $this->db->trans_strict(TRUE);
              $this->db->trans_start();
              $collection_ids_to_update_score = array();
            
              /**
               * Stock- Reliance | Shares- 500 | B/S- Sell | Closing POint- 1100 |  Result Rate- 1080 | * Point Calucaltion- difference of closing rate and Result rate * Shares= 1100-1080=20*500= 10,000 points. Refer attached screenshot for reference.

                L lot_size take from collection_stock
                CR closing rate : collection ki publish date ka stock histroy se close_price
                RR Result rate: colletion end_date ka stock histroy se close_price

                score (P) = (CR-RR)*L
                
                score (P) = (RR-CR)*L

                Lot size* Difference = Fantasy Points of Stocks
                1. if difference is negative for BUY option, then Fantasy points is in Negative value
                2. if difference is positive for BUY option, then Fantasy points is in Positive value.
                3. if difference is negative for SELL option, then Fantasy points is in Positive value.
                4. if difference is positive for SELL option, then Fantasy points is in Negative value
                * 
                *  (TRUNCATE((CASE WHEN SH2.close_price IS NOT NULL THEN SH2.close_price ELSE S.last_price END), 2)-SH1.close_price)*CS.lot_size as score
                * ****/
              foreach ($collection_publish_date_map as $collection_id => $collection_data ) {

                // if($this->validate_stock_date($collection_data['stock_type'],$current_date))
                // {
                //   continue;
                // }
                //echo "<pre>";print_r($season_str);die;
                //Update player fantasy score in particular lineup table based on collection id
                /*  Commented  SET LU.accuracy_percent = (
                                CASE WHEN GP.close_price > LU.user_price THEN IFNULL(-1*ABS(100-
                                (((GP.close_price-LU.user_price)/GP.close_price)*100)),'0')
                                  ELSE IFNULL(100-
                                  ABS(((GP.close_price-LU.user_price)/GP.close_price)*100),'0.00') END
                )*/
                $sql = "UPDATE ".$this->db->dbprefix(LINEUP)."  AS LU
                INNER JOIN (
                            SELECT  CS.stock_id,CS.open_price,             
                            (
                              (CASE 
                                  WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN S.last_price
                                 WHEN C.end_date < '".$current_date."' THEN CS.close_price
                                 ELSE S.last_price END)
                            ) as close_price,

                            ( (CASE WHEN C.scheduled_date <= '".$current_date."' AND C.end_date >= '".$current_date."' THEN CS.open_price  WHEN C.scheduled_date < '".$current_date."' THEN CS.open_price ELSE S.open_price END) ) as openprice 
                           
                            FROM ".$this->db->dbprefix(COLLECTION_STOCK )." AS CS
                            INNER JOIN ".$this->db->dbprefix(COLLECTION)." C ON CS.collection_id=C.collection_id
                            INNER JOIN ".$this->db->dbprefix(STOCK)." S ON S.stock_id=CS.stock_id
                            WHERE 
                            CS.collection_id=$collection_id
                            GROUP BY CS.stock_id
                            ) AS GP ON GP.stock_id = LU.stock_id
                INNER JOIN ".$this->db->dbprefix(LINEUP_MASTER)." AS LM  ON  LU.lineup_master_id=LM.lineup_master_id
                  SET LU.accuracy_percent = ( CASE WHEN (GP.openprice > GP.close_price  && GP.openprice > LU.user_price) OR (GP.openprice < GP.close_price && GP.openprice < LU.user_price) THEN IFNULL(100- ABS(((GP.close_price-LU.user_price)/GP.close_price)*100),'0' )
                ELSE IFNULL (-1*ABS(100- ABS(((GP.close_price-LU.user_price)/GP.close_price)*100)),'0') END )
                WHERE LM.collection_id=$collection_id
                ";  
               
                $this->db->query($sql);
                $collection_ids_to_update_score[] = $collection_id;
                $sql = $this->db->select('LM.lineup_master_id,LM.user_id')
                                        ->from(LINEUP_MASTER . " AS LM")
                                        ->join(LINEUP_MASTER_CONTEST . " AS LMC", "LMC.lineup_master_id = LM.lineup_master_id", 'INNER')
                                        ->join(CONTEST . " AS C", "C.contest_id = LMC.contest_id AND C.status != 1 ", 'INNER')
                                        ->where('LM.collection_id', $collection_id)
                                        ->group_by('LM.lineup_master_id')
                                        ->get();
                $lineup_master_ids = $sql->result_array();
                //echo "<pre>";print_r($lineup_master_ids);die;

               
               
                /**
                 * CASE 1
                 * salary cap 500000
                 * score + remaining cap  = 550000
                 * result = 10%
                 * 
                 * (total-salary_cap/salary_cap)*100
                 * 
                 * 
                 * 
                 * **/
                if (!empty($lineup_master_ids)) {
                    $ids = array_column($lineup_master_ids, 'lineup_master_id');
                    $update_sql = " UPDATE  
                                        ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                                    INNER JOIN   ".$this->db->dbprefix(LINEUP_MASTER)." LM ON LMC.lineup_master_id=LM.lineup_master_id
                                    INNER JOIN 
                                        ( SELECT SUM(L.accuracy_percent)/COUNT(L.lineup_master_id) AS percent_change,
                                        L.lineup_master_id 
                                            FROM 
                                                ".$this->db->dbprefix(LINEUP)." AS L 
                                        WHERE 
                                            L.lineup_master_id IN (".implode(',', $ids).")
                                        GROUP BY 
                                            L.lineup_master_id
                                        ) AS L_PQ ON L_PQ.lineup_master_id = LMC.lineup_master_id 
                                    SET 
                                        LMC.last_percent_change =LMC.percent_change,
                                        LMC.percent_change=L_PQ.percent_change
                                    WHERE LMC.fee_refund=0
                                    ";

                    $this->db->query($update_sql);

                    $lineup_master_ids_chunk = array_chunk($ids,3000);
                    $this->db->select('LMC.lineup_master_contest_id,LMC.contest_id');
                    $this->db->from(LINEUP_MASTER_CONTEST . " AS LMC");
                    foreach($lineup_master_ids_chunk as $lineup_master_ids) {
                      $this->db->or_where_in('LMC.lineup_master_id', $lineup_master_ids);
                    }    
                    $sql = $this->db->get();
                    //   echo $this->db->last_query();                    
                    $lmc_ids = $sql->result_array();
                    //echo "<pre>";print_r($lmc_ids);die;
                    $contest_ids = array_unique(array_column($lmc_ids, 'contest_id'));
                    $lmc_ids = array_unique(array_column($lmc_ids, 'lineup_master_contest_id'));
                    $this->load->helper('queue');
                    foreach($contest_ids as $contest_id) {
                        $contest_info = $this->db->select("is_tie_breaker")
                              ->from(CONTEST) 
                              ->where("contest_id",$contest_id) 
                              ->get()->row_array();
                        $rank_str = "";
                        if(isset($contest_info['is_tie_breaker']) && $contest_info['is_tie_breaker'] == 1){
                          $rank_str = ",lineup_master_contest_id ASC";
                        }
                        //update rank during score
                        $update_rank_sql = "UPDATE 
                                                ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC 
                                            INNER JOIN 
                                                (SELECT LMC1.lineup_master_contest_id,RANK() OVER (ORDER BY `percent_change` DESC ".$rank_str.") user_rank 
                                                  FROM ".$this->db->dbprefix(LINEUP_MASTER_CONTEST)." AS LMC1 
                                                  WHERE LMC1.contest_id = ".$contest_id.") AS L_PQ 
                                            ON L_PQ.lineup_master_contest_id = LMC.lineup_master_contest_id 
                                            SET 
                                                LMC.game_rank = IFNULL(L_PQ.user_rank,'0')
                                            WHERE LMC.fee_refund=0 ";
                        $this->db->query($update_rank_sql);
                    }

                    $score_updated_date = $current_date;
                    $this->db->set('score_updated_date',$score_updated_date);
                    $this->db->where('collection_id', $collection_id);        
                    $this->db->update(COLLECTION);


                    $lmc_data = array('action' => 'collection_info', 'collection_id' => $collection_id, 'lmc_ids' => $lmc_ids, 'score_updated_date' => $score_updated_date);
                                        
                    //$this->notify_node_collection_info($lmc_data);
                    add_data_in_queue($lmc_data,'stock_notify_node');

                    //Trasaction End
                    $this->db->trans_complete();
                    if ($this->db->trans_status() === FALSE ) {
                      $this->db->trans_rollback();
                    } else {
                      $this->db->trans_commit();
                    }

                    //clear cache
                    $del_cache_key = 'sp_collection_player_'.$collection_id;
                    $this->delete_cache_data($del_cache_key);

                    echo "Update lineup score for lineup_master_id:".implode(',', $ids);
                } else {
                    echo "No lineup score update";
                }
              }                                           
            }

            //Update score_updated_info in season table
            //echo "<pre>";print_r($current_game);die; 
            
        }
    }

    public function move_completed_collection_team()
    {
      $current_date = format_date();
      $this->db->select("CM.collection_id,CM.status,CM.is_lineup_processed,CM.stock_type", FALSE);
      $this->db->from(COLLECTION.' CM');
      $this->db->where('CM.is_lineup_processed',"1");
      $this->db->where('CM.status',"1");
      $this->db->where('CM.stock_type',$this->stock_type);//predict
      $this->db->where('CM.end_date < ',$current_date);
      $this->db->order_by('CM.collection_id', "ASC");
      $this->db->limit(5);
      $collection_list = $this->db->get()->result_array();
      //echo "<pre>";print_r($collection_list);die;
      foreach($collection_list as $collection){
       
          $this->db->select("LM.lineup_master_id,LM.user_id,L.stock_id,L.user_price,L.accuracy_percent", FALSE);
          $this->db->from(LINEUP_MASTER.' LM');
          $this->db->join(LINEUP.' as L', 'LM.lineup_master_id = L.lineup_master_id', "INNER");
          $this->db->where('LM.collection_id',$collection['collection_id']);
          $this->db->order_by('LM.lineup_master_id',"ASC");
          $this->db->order_by('L.lineup_id',"ASC");
          $team_list = $this->db->get()->result_array();
          //echo $this->db->last_query();die;
          //close price for collection 
          $cs_close_price =$this->get_collection_stock_close_price($collection['collection_id']);
          //echo "<pre>";print_r($team_list);die;
          $team_data = array();
          foreach($team_list as $row){

             $stock_price =$this->get_collection_stock_rate($collection['collection_id'],$row['stock_id']);
            
            if(isset($team_data[$row['lineup_master_id']])){
              $tm_arr = $team_data[$row['lineup_master_id']];
              $tm_arr['team_data'] = json_decode($tm_arr['team_data'],TRUE);
            }else{
              $tm_arr = array();
              $tm_arr['collection_id'] = $collection['collection_id'];
              $tm_arr['lineup_master_id'] = $row['lineup_master_id'];
              $tm_arr['user_id'] = $row['user_id'];
              $tm_arr['team_data'] = array();
              $tm_arr['added_date'] = $current_date;
            }
                
            if(!empty($stock_price[0]['close_price'])){
                $row['close_price'] = $stock_price[0]['close_price'];
                $row['open_price'] = $stock_price[0]['open_price'];
            }
            //get stock_type function
            $tm_arr['team_data'] = $this->stock_format($tm_arr['team_data'],$row,$cs_close_price);
            
            $tm_arr['team_data'] = json_encode($tm_arr['team_data']);
            $team_data[$row['lineup_master_id']] = $tm_arr;


          }

        
          if(!empty($team_data)){
            $team_data = array_values($team_data);
            $this->replace_into_batch(COMPLETED_TEAM,$team_data);
  
            $this->set_auto_increment_key(COMPLETED_TEAM,'team_id');
          }
  
          //update status
          $this->db->where('collection_id',$collection['collection_id']);
          $this->db->where('status',"1");
          $this->db->update(COLLECTION, array('is_lineup_processed'=>"2"));
          //echo "<pre>";print_r($team_data);die;
        
      }
    }

    private function get_collection_stock_close_price($collection_id)
    {
        $this->db->select("stock_id,close_price,open_price", FALSE)
                    ->from(COLLECTION_STOCK)
                    ->where('collection_id', $collection_id);
            $result = $this->db->get()->result_array();
        return $result;
    }

    private function get_collection_stock_rate($collection_id,$stock_id)
    {
        $this->db->select("stock_id,close_price,open_price", FALSE)
                    ->from(COLLECTION_STOCK)
                    ->where('collection_id', $collection_id)
                    ->where('stock_id', $stock_id);
            $result = $this->db->get()->result_array();
        return $result;
    }
    

    private function stock_format($team_data,$row,$cs_close_price)
    {   
        if(isset($team_data[$row['stock_id']]))
        { 
          $close_price = array_column($cs_close_price,'close_price','stock_id');
          $open_price = array_column($cs_close_price,'open_price','stock_id');
          $team_data[$row['stock_id']][] = array(
                'user_price' => $row['user_price'],
                "accuracy_percent" => $row['accuracy_percent'],
                "close_price" => !empty($row['close_price']) ? $row['close_price'] : $close_price[$row['stock_id']],
                "open_price" => !empty($row['open_price']) ? $row['open_price'] : $close_price[$row['stock_id']],
            ); 
        }
        else 
        {
            $team_data[$row['stock_id']] = array();
            $team_data[$row['stock_id']][] = array(
                'user_price' => $row['user_price'],
                "accuracy_percent" => $row['accuracy_percent'],
                "close_price" => !empty($row['close_price']) ? $row['close_price'] : $close_price[$row['stock_id']],
            ); 
        }
       return $team_data;
  }

  private function get_collection_to_update()
  {
    $current_date = format_date();
    $sql = $this->db->select('C.collection_id,C.scheduled_date,C.status,SUM(IF(CS.close_price <= 0,1,0)) as close_stock,SUM(IF(CS.open_price <= 0,1,0)) as open_stock')
            ->from(COLLECTION." AS C")
            ->join(COLLECTION_STOCK." CS","C.collection_id = CS.collection_id")
            ->where("C.stock_type",$this->stock_type)
            ->where("C.status", 0)
            ->where("C.is_lineup_processed",1)
            ->where("DATE_ADD(end_date,INTERVAL 15 MINUTE) < ", $current_date)
            ->group_by('C.collection_id')
            ->having("close_stock","0")
            ->having("open_stock","0")
            ->get();
   // echo $this->db->last_query(); die;
    $collection_data = $sql->result_array();
    return $collection_data;
  }

  /**
  * Used for update contest status
  * @param int $sports_id
  * @return string print output
  */
  public function update_predict_contest_status()
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
              ->where("percent_change <> ","0")
              ->where("fee_refund","0")
              ->get()
              ->row_array();
        if(!empty($score_check) && isset($score_check['total']) && $score_check['total'] > 0){
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

  public function update_contest_collection_status($all_collection_id_str='')
  {
    if(empty($all_collection_id_str))
    {
      return false;
    }

    $sql = $this->db->select('G.collection_id,G.contest_id,G.contest_unique_id,G.contest_name,G.status')
        ->from(CONTEST." AS G")
        ->where("G.collection_id IN ( $all_collection_id_str )")
        ->where("G.status", 1)
        ->group_by("G.collection_id")
        ->get();
    //echo $this->db->last_query();die();
    $collection_data = $sql->result_array();
    if(!empty($collection_data))
    {
      $collection_ids = array_unique(array_column($collection_data,'collection_id'));
      $this->db->where_in("collection_id", $collection_ids);
      $this->db->update(COLLECTION, array("status" => 1, "modified_date" => format_date()));
    }
  }

/**
 * Close candle taking nearest value of 5 Minutes on both the sides
 * This function use when stock data not come from feed for a partular time
 * @param type 1=Open price,2=Close price 
 */
public function update_stock_rates_narest_value($type=1)
  {  
      if($type ==1){
          $price_column = 'open_price';
          $date = 'scheduled_date';
      }elseif($type ==2){
          $price_column = 'close_price';
          $date = 'end_date';
      }
        $current_date = format_date();//


        $result = $this->db->select('C.collection_id,'.$date.' as scheduled_date, GROUP_CONCAT(CS.stock_id) as stock_id')
                          ->from(COLLECTION. " C")
                          ->join(COLLECTION_STOCK. " CS","CS.collection_id=C.collection_id")
                          ->where('C.status',0)
                          ->where('C.stock_type',$this->stock_type)
                          ->where('CS.'.$price_column.'',0)
                          ->where("DATE_ADD(C.$date,INTERVAL 5 MINUTE) < ", $current_date)
                          ->where("DATE_FORMAT(C.scheduled_date,'%Y-%m-%d') ",format_date('today','Y-m-d'))
                          ->group_by('CS.collection_id')
                          ->get()->result_array();
        //echo $this->db->last_query();die;
       // echo '<pre>';print_r($result);die;
       if(!empty($result))
       { 
        foreach ($result as $key => $value) {
            //echo '<pre>';print_r($value);die;
            $st_date = date('Y-m-d 04:15:00');
            $back_date = '';
            if($value['scheduled_date'] > $st_date){
              $back_date = date('Y-m-d h:i:s',strtotime($value['scheduled_date']. ' -10 minutes'));
            }
            $forward_date = date('Y-m-d h:i:s',strtotime($value['scheduled_date']. ' +10 minutes'));
            $stocks = explode(',', $value['stock_id']);            
           /* echo $back_date;
            echo '-- '.$forward_date;die;*/
            foreach ($stocks as $st_key => $st_value) {
              //echo '<pre>';print_r($st_value);die;
               $check_forward_date = $this->db->select('close_price,stock_id,schedule_date_utc')
                                          ->from(STOCK_HISTORY_DETAILS)
                                          ->where('schedule_date_utc >',$value['scheduled_date'])
                                          ->where('schedule_date_utc <', $forward_date )
                                          ->where('stock_id',$st_value)
                                          ->order_by('schedule_date_utc','ASC')
                                          ->limit(1)
                                        ->get()->row_array();

                   // echo $this->db->last_query();die;
                   if(empty($back_date) && !empty($check_forward_date))
                   {
                     $update = "UPDATE ".$this->db->dbprefix(COLLECTION_STOCK)." CS SET 
                     ".$price_column." = ".$check_forward_date['close_price']." where stock_id = ".$st_value."  and collection_id= ".$value['collection_id']."";
                     
                     $this->db->query($update);
                     continue;
                   }
                  
                   $t1=$t2=0;
                   if($check_forward_date){
                    $diff = date_diff(date_create($value['scheduled_date']) ,date_create($check_forward_date['schedule_date_utc']));
                    $t1 = $diff->i;
                   }

                   $check_back_date = $this->db->select('close_price,stock_id,schedule_date_utc')
                                            ->from(STOCK_HISTORY_DETAILS)
                                            ->where('schedule_date_utc <',$value['scheduled_date'])
                                            ->where('schedule_date_utc >',$back_date)
                                            ->where('stock_id',$st_value)
                                            ->order_by('schedule_date_utc','DESC')
                                            ->limit(1)
                                          ->get()->row_array();
                  if($check_back_date){
                    $diff = date_diff(date_create($value['scheduled_date']) ,date_create($check_back_date['schedule_date_utc']));
                     $t2 = $diff->i;                    
                   }  
/*                   echo '<pre>';
                   print_r($check_forward_date);
                   print_r($check_back_date);
                   echo $t1.'---'.$t2;die;*/
                   $price = '';
                   if($t2 < $t1 && $t2 > 0){ 
                     $price = $check_back_date['close_price'];
                   }
                   elseif($t1 <= $t2 && $t1 > 0){                  
                     $price = $check_forward_date['close_price'];
                   }
                   elseif(!empty($check_back_date))
                   {
                      $price = $check_back_date['close_price'];
                   }
                   elseif(!empty($check_forward_date))
                   {
                      $price = $check_forward_date['close_price'];
                   } 
                    

                   if(!empty($price))
                   {
                      $update = "UPDATE ".$this->db->dbprefix(COLLECTION_STOCK)." CS SET ".$price_column." = ".$price." where stock_id = ".$st_value."  and collection_id= ".$value['collection_id']."";         
                        $this->db->query($update); 

                   }
          
                 
            }
        }
       }
    return;
  }


}