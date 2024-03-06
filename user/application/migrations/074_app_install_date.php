<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_App_install_date extends CI_Migration {

	public function up()
	{
		$fields = array(
			'ios_install_date' => array(
				'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL,
                'after' => 'modified_date',
			),
			'android_install_date' => array(
				'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL,
                'after' => 'ios_install_date',
			),
			'uninstall_date' => array(
				'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL,
                'after' => 'android_install_date',
			),
		);
		$this->dbforge->add_column(USER, $fields);

		$sql = "UPDATE ".$this->db->dbprefix(USER)." as U INNER JOIN (SELECT T1.keys_id,T1.user_id,T1.date_created FROM ".$this->db->dbprefix(ACTIVE_LOGIN)." T1 WHERE T1.keys_id = (SELECT MIN(T2.keys_id) FROM ".$this->db->dbprefix(ACTIVE_LOGIN)." T2 WHERE T2.user_id = T1.user_id AND T2.device_type = 1 AND T2.role=1 GROUP BY T2.user_id)) as L ON L.user_id=U.user_id SET U.android_install_date=L.date_created WHERE U.android_install_date IS NULL;";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(USER)." as U INNER JOIN (SELECT T1.keys_id,T1.user_id,T1.date_created FROM ".$this->db->dbprefix(ACTIVE_LOGIN)." T1 WHERE T1.keys_id = (SELECT MIN(T2.keys_id) FROM ".$this->db->dbprefix(ACTIVE_LOGIN)." T2 WHERE T2.user_id = T1.user_id AND T2.device_type = 2 AND T2.role=1 GROUP BY T2.user_id)) as L ON L.user_id=U.user_id SET U.ios_install_date=L.date_created WHERE U.ios_install_date IS NULL;";
		$this->db->query($sql);

	}

	public function down()
	{
		//down script
		$this->dbforge->drop_column(USER, 'install_date');
	}
}