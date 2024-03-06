<?php
class Coins_package_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_coins_package_list($offset, $limit){

        $this->db->select('CP.coin_package_id,CP.coins,CP.amount,CP.package_name', FALSE)
                ->from(COIN_PACKAGE . " AS CP")
                ->limit($limit, $offset);

        $query = $this->db->where('CP.status', '1')
                ->order_by('CP.coins', 'DESC')
                ->get();

        return $query->result_array();
    }

    public function get_coins_package($package_id){

        $this->db->select('CP.coin_package_id,CP.coins,CP.amount,CP.package_name', FALSE)
                ->from(COIN_PACKAGE . " AS CP");

        $query = $this->db->where('CP.status', '1')
                ->where('CP.coin_package_id', $package_id)
                ->get();

        return $query->row_array()?$query->row_array():array();
    }

    
    public function buy_coins($data){

        $coin_purchase = array();
        $coin_purchase['user_id'] = $data['user_id'];
        $coin_purchase['source'] = "282"; //Coin Package Purchase
        $coin_purchase['type'] = 0; //Credit points, Debit 
        $coin_purchase['source_id'] = $data['coin_package_id'];
        $coin_purchase['reason'] = $this->lang->line("coin_purchase_message");
        $coin_purchase['points'] = $data['coins']; //Credit points
        $coin_purchase['cash_type'] = 2; // 2 point credit, 0 Real Amount  

        $this->create_order($coin_purchase);

        //Debit fund from wallet for purchase coin
        $debit_wallet = array();
        $debit_wallet['user_id'] = $data['user_id'];
        $debit_wallet['source'] = "283"; //With draw Amount for Package Purchase
        $debit_wallet['type'] = 1; //Credit points, Debit 
        $debit_wallet['source_id'] = $data['coin_package_id'];
        $debit_wallet['reason'] = $this->lang->line("coin_purchase_debit");
        $debit_wallet['real_amount'] = $data['real_amount']; //Credit points
        $debit_wallet['winning_amount'] = $data['winning_amount']; //Credit points
        $debit_wallet['cash_type'] = 0; // 2 point credit, 0 Real Amount  
        
        $this->create_order($debit_wallet);

        //Update User balance
        $real_amount = $points_update =  array();
        $real_amount['amount'] = $data['amount'];

        $real_amount['real_amount'] = $data['real_amount'];
        $real_amount['winning_amount'] = $data['winning_amount'];
        $points_update['points'] =  $data['coins'];
        // update user balance!
        $this->load->model("finance/Finance_model");  
        $this->Finance_model->update_user_balance($data['user_id'], $real_amount, 'withdraw');
        $this->Finance_model->update_user_balance($data['user_id'], $points_update, 'add');

        $user_cache_key = "user_balance_" . $data['user_id'];
        $this->delete_cache_data($user_cache_key);

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

        switch ($input['cash_type']) {
            case 0:
                $orderData["real_amount"] = $input['real_amount']; // Real Money
                $orderData["winning_amount"] = $input['winning_amount']; // Winning Balance 
                break;
            case 1:
                $orderData["bonus_amount"] = $input["bonus_amount"]; // Bonus Money 
                break;
            case 2:
                $orderData["points"] = $input["points"]; // Point Balance
                break;
            default:
                return FALSE;
                break;
        }
        //$this->db->trans_start();
        $orderData['order_unique_id'] = $this->Finance_model->_generate_order_key();
        $this->db->insert(ORDER, $orderData);
        $order_id = $this->db->insert_id();

        if (!$order_id) {
            return FALSE;
        }else{
            if($orderData["points"] > 0)
            {
                $this->load->helper('queue_helper');
                $coin_data = array(
                    'oprator' => 'add', 
                    'user_id' => $input['user_id'], 
                    'total_coins' => $orderData["points"], 
                    'bonus_date' => format_date("today", "Y-m-d")
                );
                add_data_in_queue($coin_data, 'user_coins');
            }
        }
        //$this->db->trans_complete();
        return $order_id;
    }

}
