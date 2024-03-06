<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_aadhar_auto extends CI_Migration {

  public function up() {
    //up script
    $fields = array(
            'verify_by' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                    'comment' => '1=>manual, 2=>auto'
            )
    );
        if(!$this->db->field_exists('verify_by', USER_AADHAR)){
            $this->dbforge->add_column(USER_AADHAR,$fields);
        }

        $this->db->where('notification_type', 532);
        $this->db->update(NOTIFICATION_DESCRIPTION, array(
          'hi_message' => 'आपका {{a_to_id}} कार्ड अस्वीकार कर दिया गया है। कारण: {{aadhar_rejected_reason}}',
        ));
	}

	public function down() {
    //down script
    // $this->dbforge->drop_column(ORDER, 'payout_processed');
  
	}

}