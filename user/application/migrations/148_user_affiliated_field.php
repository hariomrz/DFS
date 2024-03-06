<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_User_affiliated_field extends CI_Migration {

    public function up() {
        
         $fields = array(
      
        'expected_affiliated_user' => array(
          'type' => 'INT',
          'constraint' => 100,
          'null' => NULL,
          "after" =>'gst_number'
        ),       
        'user_affiliated_website' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => NULL,   
          "after" =>'gst_number' 
         
        ),
         'site_rake_status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0 ,
          "after" =>'gst_number'         
        ),
        'site_rake_commission' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'default'=>0,  
            "after" =>'gst_number'         
        ),
      );
        if(!$this->db->field_exists('expected_affiliated_user', USER) && !$this->db->field_exists('user_affiliated_website', USER) && !$this->db->field_exists('site_rake_status', USER) && !$this->db->field_exists('site_rake_commission', USER)){
            $this->dbforge->add_column(USER,$fields);
        }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',556)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '556',
                'name' => 'Affiliate Match Commission'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"556")
            ->get()
            ->row_array();
      if(empty($row)) {
          $transaction_messages =
              array(
                  'source' => 556,
                  'en_message'      => '{{match}} affiliate commission',
                  'hi_message'      => '{{match}} अफिलिएट कमीशन ',
                  'guj_message'     => '{{match}} એફિલિએટ કમિશન',
                  'fr_message'      => '{{match}} Commission daffiliation',
                  'ben_message'     => '{{match}} অনুমোদিত কমিশন',
                  'pun_message'     => '{{match}} ਐਫੀਲੀਏਟ ਕਮਿਸ਼ਨ',
                  'tam_message'     => '{{match}} துணை ஆணையம்',
                  'th_message'      => '{{match}} คณะกรรมาธิการพันธมิตร',
                  'kn_message'      => '{{match}} ಅಂಗಸಂಸ್ಥೆ ಆಯೋಗ',
                  'ru_message'      => '{{match}} Партнерская комиссия',
                  'id_message'      => '{{match}} komisi afiliasi',
                  'tl_message'      => '{{match}} Komisyon ng kaakibat',
                  'zh_message'      => '{{match}}会员委员会',
                  'es_message'      => '{{match}} Comisión de afiliación'
          );
          $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
      }
    }
    public function down()
    {
        //down script
    }
}
