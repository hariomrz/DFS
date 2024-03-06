<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Common_content extends CI_Migration {

    public function up() {

        $fields = array(
              'id'=>array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'auto_increment' => TRUE,
                'null' => FALSE,
              ),
              'content_key' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'default'=>NULL,
              ),
              'en_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'hi_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'guj_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'fr_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'ben_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'pun_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'tam_header' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'en_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ), 
              'hi_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'guj_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'fr_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'ben_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'pun_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'tam_body' => array(
                'type' => 'varchar',
                'constraint' => '100',
                'character'=>'set utf8 COLLATE utf8_general_ci',
                'default'=>NULL,
              ),
              'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default'=>1,
              ),
          );

          $attributes = array('ENGINE' => 'InnoDB');
          $this->dbforge->add_field($fields);
          $this->dbforge->add_key('id',TRUE);
          $this->dbforge->create_table(COMMON_CONTENT,FALSE,$attributes);   
         
        $value = array(
        array(
        "content_key" =>'wallet',
        "en_header"=>'Total Balance', 
        "hi_header"=>'कुल शेष',
        "tam_header"=> 'மொத்த சமநிலை',
        "ben_header"=>'মোট ভারসাম্য',
        "pun_header"=>'ਕੁਲ ਬਕਾਇਆ',
        "fr_header"=>'Solde total',
        "guj_header"=>'કુલ બેલેન્સ',
        "en_body"=>'Winnings + Bonus Cash + Deposit', 
        "hi_body"=>'जीत + बोनस नकद + जमा',
        "tam_body"=> 'வெற்றிகள் + போனஸ் ரொக்கம் + வைப்பு',
        "ben_body"=>'বিজয়ী + বোনাস নগদ + আমানত',
        "pun_body"=>'ਜੇਤੂ + ਬੋਨਸ ਨਕਦ + ਜਮ੍ਹਾ',
        "fr_body"=>'Gains + Bonus Cash + Dépôt',
        "guj_body"=>'વિજેતા + બોનસ કેશ + ડિપોઝિટ',
        ),
        );
        $this->db->insert_batch(COMMON_CONTENT,$value);


             
    }
    
    public function down() {
        	//down script 
            $this->dbforge->drop_table(COMMON_CONTENT);        
    }
}
