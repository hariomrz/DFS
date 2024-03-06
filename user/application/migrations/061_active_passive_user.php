<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Active_passive_user extends CI_Migration {

	function __construct()
{
  $this->db_analytics =$this->load->database('db_analytics',TRUE);
  $this->analytics_forge = $this->load->dbforge($this->db_analytics, TRUE);

}
	public function up()
	{
		// $this->db		= $this->load->database('db_analytics', TRUE);
		// $this->myforge = \Config\Database::forge('db_analytics');
		$fields = array(
			'active_users' => array(
				'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL,
                'after' => 'no_of_referrals',
			),
			'passive_users' => array(
				'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL,
				'after' => 'active_users'
			)
		);
		$this->analytics_forge->add_column(ANALYTICS, $fields);
		// echo $this->db_analytics->last_query();exit;  
	}

	public function down()
	{
		//down script
		$this->analytics_forge->drop_column(ANALYTICS, 'active_users');
		$this->analytics_forge->drop_column(ANALYTICS, 'passive_users');
	}

}