<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_notification_status extends CI_Migration {

	public function up()
	{
		$fields = array(
			'notification_delivered_count' => array(
				'type' => 'INT',
				'constraint' => 255,
				'default' => 0,
				'after' => 'notification_count',
				'null' => FALSE
			),
			'notification_viewed_count' => array(
				'type' => 'INT',
				'constraint' => 255,
				'default' => 0,
				'after' => 'notification_delivered_count',
				'null' => FALSE
			)
		);
		$this->dbforge->add_column(CD_RECENT_COMMUNICATION, $fields);

		$fields = array(
            'app_notification_setting' => array(
              'type' => 'TINYINT',
              'constraint' => 1,
              'null' => FALSE,
              'default' => 1,
              'comment' => '0-OFF, 1-ON'
            )
        );
        $this->dbforge->add_column(USER, $fields);



	}

	public function down()
	{
		//down script
		$this->dbforge->drop_column(CD_RECENT_COMMUNICATION, 'notification_delivered_count');
		$this->dbforge->drop_column(CD_RECENT_COMMUNICATION, 'notification_viewed_count');
		$this->dbforge->drop_column(USER, 'app_notification_setting');
	}

}