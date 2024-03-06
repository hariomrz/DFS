<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sms_template extends CI_Migration {

    public function up() {

        $fields = array(
            'sms_template_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE,
                    'null' => FALSE
            ),
            'module_type' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE,
                    'comment' => "0=> for cd, 1=>apk sms status",
                    'default' => 0
            ),
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
              'default' => 0
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
              ),
              'reference_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
              ),
              'dlt_template_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
              ),
              'message' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
              ),
              'help_text' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'default' => NULL
              ),
           
            'updated_date' => array(
              'type' => 'DATETIME',
              'null' => TRUE,
              'default' => NULL
            )
            );
    
          $attributes = array('ENGINE' => 'InnoDB');
          $this->dbforge->add_field($fields);
          $this->dbforge->add_key('sms_template_id',TRUE);
          $this->dbforge->create_table(SMS_TEMPLATE ,FALSE,$attributes); 

        $sms_data = array(
            array(
                'module_type' => 0,
                'status' => 0,
                'name' => 'Promostion for deposit',
                'reference_id' => 120,
                'dlt_template_id' => NULL,
                'message' => NULL,
                'help_text' => 'Get {#var#} % extra on your Deposit. Use code {#var#}! Exclusively for you on Cricjam! Team, Viscus',
                'updated_date' => format_date()
            ),
            array(
                'module_type' => 0,
                'status' => 0,
                'name' => 'refer a friend',
                'reference_id' => 123,
                'dlt_template_id' => NULL,
                'message' => NULL,
                'help_text' => 'Mega offer on CRICJAM! Use {{promo_code}} Code, and earn {{offer_percentage}}% cashback on your next deposit. Play Now.',
                'updated_date' => format_date()
            ),
            array(
                'module_type' => 0,
                'status' => 0,
                'name' => 'promotion-for-fixture',
                'reference_id' => 300,
                'dlt_template_id' => NULL,
                'message' => NULL,
                'help_text' => 'Hey There!! Join {{home}} vs {{away}} to win BIG CASH Prize on CRICJAM.',
                'updated_date' => format_date()
            )
        );

        $this->db->insert_batch(SMS_TEMPLATE,$sms_data);


          $this->db->where('category_id',9);
          $this->db->update(CD_EMAIL_CATEGORY,array('status'=> 0));
  }

  public function down()
  {
	//down script 
    //$this->db->where_in('notification_type', array(423));
	//$this->db->delete(SMS_TEMPLATE);
  }
}
