<?php

class Equity_model extends MY_Model {
    public function __construct() {
        parent::__construct();
    }
    
    public function statics($data){
        $type = isset($data['type']) ? $data['type'] : 0; // 1 - top gainer, 2 - top loser       
        $collection_id = $data['collection_id']; 
        $published_date = $data['published_date'];
        $published_date = date('Y-m-d',strtotime($published_date));
        $end_date = date('Y-m-d',strtotime($data['end_date']));
        $page = isset($data['page']) ? $data['page'] : 0;
       
        $limit = 5;
        $offset = 0;
        $offset = $limit * $page;

        $current_date = format_date();
        $this->db->select('IFNULL(i.name,"") as industry_name', FALSE);
        $this->db->select('IFNULL(i.industry_id,"") as industry_id', FALSE);
        $this->db->select("(TRUNCATE(
                                (
                                    CASE 
                                    WHEN cm.scheduled_date <= '".$current_date."' AND cm.end_date >= '".$current_date."' THEN (s.last_price-IFNULL(sh.close_price,0)) 
                                    WHEN cm.end_date < '".$current_date."' THEN (sh1.close_price-sh.close_price) 
                                    ELSE (s.last_price-s.previous_close) END
                                ),2)) as price_diff"
                        );
        $this->db->select("(
                                CASE 
                                WHEN cm.scheduled_date <= '".$current_date."' AND cm.end_date >= '".$current_date."' THEN s.last_price 
                                WHEN cm.end_date < '".$current_date."' THEN ROUND(sh1.close_price,2) 
                                ELSE ROUND(sh.close_price,2) END
                            ) as current_price"
                        );                
                
        $this->db->select('(
            TRUNCATE((CASE WHEN cm.scheduled_date <= "'.$current_date.'" AND cm.end_date >= "'.$current_date.'"  THEN ((s.last_price-sh.close_price)/sh.close_price)*100  
                 WHEN cm.end_date < "'.$current_date.'" THEN ((sh1.close_price-sh.close_price)/sh.close_price)*100 
                 ELSE ((s.last_price-s.previous_close)/s.previous_close)*100 END), 2)
        ) as percent_change');     

        $this->db->select("s.stock_id, cs.stock_name, s.display_name, s.trading_symbol, IFNULL(s.logo,'') as logo, ROUND(IFNULL(sh.close_price,0),2) as pr_price",FALSE); //, s.last_price as current_price, (s.last_price - IFNULL(sh.close_price,0)) as price_diff
        $this->db->from(COLLECTION_STOCK . " cs");
        $this->db->join(COLLECTION . " cm", "cm.collection_id=cs.collection_id");
        $this->db->join(STOCK.' s','s.stock_id=cs.stock_id');
        $this->db->join(STOCK_HISTORY.' sh','sh.stock_id=s.stock_id AND sh.schedule_date = "'.$published_date.'"');
        $this->db->join(STOCK_HISTORY.' sh1','sh1.stock_id=s.stock_id AND sh1.schedule_date = "'.$end_date.'"', 'LEFT');
        $this->db->join(INDUSTRY.' i','i.industry_id=s.industry_id', 'LEFT');
        $this->db->where('cs.collection_id', $collection_id, FALSE);
        if(empty($page)) {
            $this->db->limit($limit,$offset);
        }
        
        if($type == 1) {
            $this->db->having('percent_change > ', 0);
            $this->db->order_by('percent_change', 'DESC');
        } else if($type == 2) {
            $this->db->having('percent_change < ', 0);
            $this->db->order_by('percent_change', 'ASC');
        } else {
            $this->db->order_by('percent_change', 'DESC');
        }
        
        $results = $this->db->get()->result_array();
        //echo $this->db->last_query();die;
        $this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($data['user_id'], TRUE);
        foreach($results as &$result) {
            $result['is_wish'] = 0;
            if(in_array($result['stock_id'], $wishlist_stock_ids)) {
                $result['is_wish'] = 1;
            }
            
        }

        return $results;
    }  

}
