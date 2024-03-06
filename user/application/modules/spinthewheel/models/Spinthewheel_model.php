<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spinthewheel_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database('user_db');
        //Do your magic here
    }

    public function get_slices_list($offset, $limit){

        $this->db->select('STW.spinthewheel_id,STW.slice_name as value,STW.win,STW.probability,STW.result_text as resultText,STW.type,STW.cash_type,FLOOR(STW.amount) as amount ', FALSE)
                ->from(SPIN_THE_WHEEL . " AS STW")
                ->limit($limit, $offset);

        $query = $this->db->where('STW.status', '1')
                ->get();

        return $query->result_array();
    }

    public function spin_claimed($user_id){

        $this->db->select('user_id', FALSE)
                ->from(SPIN_CLAIMED);
        $query = $this->db->where('date(claimed_date)', date("Y-m-d"))->where('user_id', $user_id)
                ->get();
        return $query->row_array();
    }

    public function add_spin_claimed($post){
        $post["claimed_date"] = format_date('today', 'Y-m-d');
        $this->db->insert(SPIN_CLAIMED, $post);
        return $this->db->insert_id();
    }

    
    public function win_spinthewheel($data){

        $win_wheel = array();
        $win_wheel['user_id'] = $data['user_id'];
        $win_wheel['source'] = "322"; //Win Spin the wheel 
        $win_wheel['type'] = 0;
        $win_wheel['source_id'] = $data['spinthewheel_id'];
        $win_wheel['amount'] = $data['amount']; //Credit points
        $win_wheel['cash_type'] = $data['cash_type']; //
        $win_wheel['slice_name'] = $data['slice_name']; //

        if($data['cash_type']==0) {
            $win_wheel['reason'] = $this->lang->line("spinwheel_cash_desc"); 
        }
        if($data['cash_type']==1) {
            $win_wheel['reason'] = $this->lang->line("spinwheel_bonus_desc"); 
        }
        if($data['cash_type']==2) {
            $win_wheel['reason'] = $this->lang->line("spinwheel_coin_desc"); 
        }
        if($data['cash_type']==3) {
            $win_wheel['reason'] = $this->lang->line("spinwheel_merchandise"); 
        }

        $this->create_order($win_wheel);

        //Update User balance
        $balance_update =  array();
 
        if($data['cash_type']==0) {
            $balance_update['real_amount'] = $data["amount"];
        }
        if($data['cash_type']==1) {
            $balance_update['bonus_amount'] = $data["amount"];
        }
        if($data['cash_type']==2) {
            $balance_update['points'] = $data["amount"];
        }
        if($data['cash_type']==4) {
            $balance_update['winning_amount'] = $data["amount"];
        }

        // update user balance!
        $this->load->model("finance/Finance_model");  
        $this->Finance_model->update_user_balance($data['user_id'], $balance_update, 'add');
        return true;
    }


    /**
     * Used to create order
     * @param array $input
     * @return int
     */
    function create_order($input) {

        $this->load->model("finance/Finance_model");  

        $today = format_date();
        $orderData = array();
        $orderData["user_id"] = $input['user_id'];
        $orderData["source"] = $input['source'];
        $orderData["source_id"] = $input['source_id'];
        $orderData["type"] = $input['type'];  //0 credit 1 Debit
        $orderData["date_added"] = $today;
        $orderData["modified_date"] = $today;
        $orderData["status"] = 1;
        $orderData["real_amount"] = 0;
        $orderData["bonus_amount"] = 0;
        $orderData["winning_amount"] = 0;
        $orderData["points"] = 0;
        $orderData["reason"] = isset($input['reason']) ? $input['reason'] : '';
        
        $custom_data = array();
        $cash_type = '';
        switch ($input['cash_type']) {
            case 0:
                $orderData["real_amount"] = $input['amount']; // Real Money
                $cash_type = 'real cash';
                break;
            case 1:
                $orderData["bonus_amount"] = $input["amount"]; // Bonus Money 
                $cash_type = 'bonus cash';
                break;
            case 2:
                $orderData["points"] = $input["amount"]; // Point Balance
                $cash_type = 'coins';
                break;
            case 3:
                $custom_data['name'] = $input['slice_name'];
                $custom_data['prize_type'] = $input['cash_type'];
                $cash_type = 'prize';
                break;
            default:
                return FALSE;
                break;
        }
        $custom_data['amt_type'] = $cash_type;
        $orderData["custom_data"] = json_encode($custom_data);
        //$this->db->trans_start();
        $orderData['order_unique_id'] = $this->Finance_model->_generate_order_key();
        $this->db->insert(ORDER, $orderData);
        $order_id = $this->db->insert_id();

        if (!$order_id) {
            return FALSE;
        }else{
            $this->load->helper('queue_helper');
            $coin_data = array(
                'oprator' => 'add', 
                'user_id' => $input['user_id'], 
                'total_coins' => $orderData["points"], 
                'bonus_date' => format_date("today", "Y-m-d")
            );
            add_data_in_queue($coin_data, 'user_coins');
        }
        //$this->db->trans_complete();
        return $order_id;
    }

    /**
     * Used to get max coins value for spin wheel
     */
    public function get_max_coins(){
        $this->db->select('amount');
        $this->db->from(SPIN_THE_WHEEL);
        $this->db->where('status', '1');
        $this->db->where('cash_type', 2);
        $this->db->order_by('amount', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = array();
        $amount = 0;
        if ($query->num_rows()) {
            $result = $query->row_array();
            $amount = $result['amount'];  
        }
        return $amount;
    }

}
/* End of file Spinthewheel_model.php */
/* Location: ./application/models/Spinthewheel_model.php */
