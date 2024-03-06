<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_noti_sms_sent extends CI_Migration {

	public function up() {
		//up script
		$fields = array(
			'cd_sent_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'email_sent' => array(
				'type' => 'BIGINT',
				'constraint' => 50,
				'null' => FALSE
            ),
            'notification_sent' => array(
				'type' => 'BIGINT',
				'constraint' => 50,
				'null' => FALSE
            ),
            'sms_sent' => array(
				'type' => 'BIGINT',
				'constraint' => 50,
				'null' => FALSE
            ),
            'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE,
			),
			
        );
        
        $attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('cd_sent_id', TRUE);
		$this->dbforge->create_table(CD_EMAIL_SENT,FALSE,$attributes);
		
		$data = array(
			"email_sent"=>0,
			"notification_sent"=>0,
			"sms_sent"=>0,
			"added_date"=>date('Y-m-d')
		);

		$this->db->insert(CD_EMAIL_SENT,$data);
	}



	public function down() {
		$this->dbforge->drop_table(CD_EMAIL_SENT);
	}

}