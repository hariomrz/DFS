<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Prize_revert extends CI_Migration {

    public function up() {
      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',20)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '20',
                'name' => 'Contest Prize Revert'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',21)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '21',
                'name' => 'Wrong Game Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $txn_arr = array();
      $txn_arr['source'] = 20;
      $txn_arr['en_message'] = "Revert Contest Prize";
      $txn_arr['hi_message'] = "प्रतियोगिता पुरस्कार वापस करें";
      $txn_arr['guj_message'] = "હરીફાઈ પુરસ્કાર પાછો ખેંચો";
      $txn_arr['fr_message'] = "Rétablir le prix du concours";
      $txn_arr['ben_message'] = "প্রতিযোগিতার পুরস্কার ফিরিয়ে দিন";
      $txn_arr['pun_message'] = "ਪ੍ਰਤੀਯੋਗਤਾ ਇਨਾਮ ਵਾਪਸ ਕਰੋ";
      $txn_arr['tam_message'] = "போட்டி பரிசை திரும்பப்பெறு";
      $txn_arr['th_message'] = "ย้อนกลับรางวัลการแข่งขัน";
      $txn_arr['ru_message'] = "Вернуть приз конкурса";
      $txn_arr['id_message'] = "Kembalikan Hadiah Kontes";
      $txn_arr['tl_message'] = "Ibalik ang Prize ng Paligsahan";
      $txn_arr['zh_message'] = "还原比赛奖品";
      $txn_arr['kn_message'] = "ಸ್ಪರ್ಧೆಯ ಬಹುಮಾನವನ್ನು ಹಿಂತಿರುಗಿಸಿ";
      $result = $this->db->select('*')->from(TRANSACTION_MESSAGES)->where('source',20)->get()->row_array();
      if(!empty($result)){
        $this->db->where("source",$txn_arr['source']);
        $this->db->update(TRANSACTION_MESSAGES, $txn_arr);
      }else{
        $this->db->insert(TRANSACTION_MESSAGES,$txn_arr);
      }

      $txn_arr = array();
      $txn_arr['source'] = 21;
      $txn_arr['en_message'] = "Won Contest Prize";
      $txn_arr['hi_message'] = "प्रतियोगिता का पुरस्कार जीता";
      $txn_arr['guj_message'] = "કોન્ટેસ્ટ પ્રાઇઝ જીત્યો";
      $txn_arr['fr_message'] = "Prix du concours remporté";
      $txn_arr['ben_message'] = "প্রতিযোগিতা পুরস্কার জিতেছে";
      $txn_arr['pun_message'] = "ਮੁਕਾਬਲਾ ਇਨਾਮ ਜਿੱਤਿਆ";
      $txn_arr['tam_message'] = "வென்றது போட்டி பரிசு";
      $txn_arr['th_message'] = "ได้รับรางวัลการประกวด";
      $txn_arr['ru_message'] = "Выигранный приз конкурса";
      $txn_arr['id_message'] = "Memenangkan Hadiah Kontes";
      $txn_arr['tl_message'] = "Nanalong Premyo sa Paligsahan";
      $txn_arr['zh_message'] = "赢得比赛奖";
      $txn_arr['kn_message'] = "ಸ್ಪರ್ಧೆಯ ಬಹುಮಾನ ಗೆದ್ದರು";
      $result = $this->db->select('*')->from(TRANSACTION_MESSAGES)->where('source',21)->get()->row_array();
      if(!empty($result)){
        $this->db->where("source",$txn_arr['source']);
        $this->db->update(TRANSACTION_MESSAGES, $txn_arr);
      }else{
        $this->db->insert(TRANSACTION_MESSAGES,$txn_arr);
      }
    }

    public function down() {
      //down script 
    }

}
