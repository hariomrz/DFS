<?php

class Scratchwin_model extends MY_Model {

    function __construct() {
        parent::__construct();
    }
    
    public function get_rendom_scratch_card(){
        $result = $this->db->select('scratch_card_id,prize_type,amount')
        ->from(SCRATCH_WIN)
        ->where('status','1')
        ->limit(1)
        ->order_by('rand()')
        ->get()->row_array();
        $result['prize_data'] = base64_encode($result['prize_type'].'_'.$this->user_id.'_'.$result['scratch_card_id'].'_'.$result['amount']);
        return $result;
    }

    public function update_scratch_card_record($json_data){
        $insert_data = array(
            "user_id"=>$this->user_id,
            "contest_id" => $this->input->post('contest_id'),
            "claimed_date" => format_date('today'),
            "scratch_details" => $json_data,
        );
        if($this->input->post('amount') == 0)
        {
            $insert_data['status'] = 1;
        }
        $check = $this->db->select('scratch_win_claimed_id,status')->where(['user_id'=>$this->user_id,'contest_id'=>$this->input->post('contest_id')])->get(SCRATCH_WIN_CLAIMED)->row_array();
        if($check){
            if($check['status']==1){
                return false;
            }
            return $check['scratch_win_claimed_id'];
        }else{
            $this->db->insert(SCRATCH_WIN_CLAIMED,$insert_data);
            return $this->db->insert_id();
        }
    }

    public function get_user_claimed($card_detail){
        $data = $this->input->post();
        $card_details = json_decode($card_detail,TRUE);

        if($card_details["amount"] > 0)
        {
        // print_r($card_details);exit;
        $balance_update =  array();
        $order_data = array();
        
        $order_data['user_id'] = $this->user_id;
        $order_data['source'] = "381";
        $order_data['source_id'] = $card_details['scratch_card_id'];
        $order_data['type'] = 0;
        $order_data["date_added"] = format_date();
        $order_data["modified_date"] = format_date();
        $order_data["status"] = 1;
        $order_data["real_amount"] = 0;
        $order_data["bonus_amount"] = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = 0; 

        // $order_data['amount'] = $card_details['amount']; 
        // $order_data['cash_type'] = $card_details['prize_type'];

        if($card_details['prize_type']==0) {
            $order_data['reason']           = $this->lang->line("scratchwin_bonus_desc");
            $order_data["bonus_amount"]     = $card_details['amount'];
            $balance_update['bonus_amount'] = $card_details["amount"];
        }
        if($card_details['prize_type']==1) {
            $order_data['reason']           = $this->lang->line("scratchwin_cash_desc");
            $order_data["real_amount"]      = $card_details["amount"];
            $balance_update['real_amount']  = $card_details["amount"];
        }
        if($card_details['prize_type']==2) {
            $order_data['reason']           = $this->lang->line("scratchwin_coin_desc");
            $order_data["points"]           = $card_details["amount"];
            $balance_update['points']       = $card_details["amount"];
        }

        //generate order
        $this->load->model("finance/Finance_model");
        $order_data['order_unique_id'] = $this->Finance_model->_generate_order_key();
        $this->db->insert(ORDER, $order_data);
        $order_id = $this->db->insert_id();

        if ($order_id) {
            
            if($order_data["points"] > 0) {
                $this->load->helper('queue_helper');
                $coin_data = array(
                    'oprator' => 'add', 
                    'user_id' => $this->user_id, 
                    'total_coins' => $order_data["points"], 
                    'bonus_date' => format_date("today", "Y-m-d")
                );
                add_data_in_queue($coin_data, 'user_coins');    
            }

            $this->load->model("finance/Finance_model");  
            $this->Finance_model->update_user_balance($this->user_id, $balance_update, 'add');
            return true;
        }
        //$this->db->trans_complete();
    }
    return false;
    }
}
?>