<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gst_cashback extends CI_Migration{

  public function up(){

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',550)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
        'source' => '550',
        'name' => 'GST cashback in deposit'
      );
      $this->db->insert(MASTER_SOURCE,$data_arr);
    }
    
    //add cb_balance in user table
    $fields = array(
      'cb_balance'  => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'default'=>'0.00',
        'after' => 'net_winning',
        'comment' => 'Deposit GST/Other cashback amount',
        'null' => FALSE
      )
    );
    if(!$this->db->field_exists('cb_balance', USER)){
      $this->dbforge->add_column(USER,$fields);
    }

    //add cb_amount in order table
    $fields = array(
      'cb_amount'  => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'default'=>'0.00',
        'after' => 'winning_amount',
        'comment' => 'Cashback amount',
        'null' => FALSE
      )
    );
    if(!$this->db->field_exists('cb_amount', ORDER)){
      $this->dbforge->add_column(ORDER,$fields);
    }

    // transaction msg
    $row = $this->db->select('source')
      ->from(TRANSACTION_MESSAGES)
      ->where('source',"550")
      ->get()
      ->row_array();
    if(empty($row)) {
      $transaction_messages =
      array(
        'source' => 550,
        'en_message'      => 'Bonus Cashback on Deposit',
        'hi_message'      => 'जमा पर बोनस कैशबैक',
        'guj_message'     => 'ડિપોઝિટ પર બોનસ કેશબેક',
        'fr_message'      => "Remise en argent bonus sur le dépôt",
        'ben_message'     => 'ডিপোজিটের উপর বোনাস ক্যাশব্যাক',
        'pun_message'     => "ਡਿਪਾਜ਼ਿਟ 'ਤੇ ਬੋਨਸ ਕੈਸ਼ਬੈਕ",
        'tam_message'     => 'டெபாசிட்டில் போனஸ் கேஷ்பேக்',
        'th_message'      => 'โบนัสคืนเงินจากการฝากเงิน',
        'kn_message'      => 'ಠೇವಣಿ ಮೇಲೆ ಬೋನಸ್ ಕ್ಯಾಶ್‌ಬ್ಯಾಕ್',
        'ru_message'      => 'Бонусный кэшбэк на депозит',
        'id_message'      => 'Bonus Cashback di Deposit',
        'tl_message'      => 'Bonus na Cashback sa Deposito',
        'zh_message'      => '存款獎金現金回饋',
        'es_message'      => 'Bonificación de reembolso por depósito',
      );
      $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

  }

  public function down(){
    //down script
  }
}
