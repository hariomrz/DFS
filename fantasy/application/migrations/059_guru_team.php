<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_guru_team extends CI_Migration 
{

	public function up() {
		//up script
		$fields = array(
			'is_pl_team' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
		);
		$sql = "SHOW COLUMNS FROM ".$this->db->dbprefix(LINEUP_MASTER)." LIKE 'is_pl_team'";
		$result = $this->db->query($sql);
		$exists = $result->num_rows();
		//echo "<pre>";print_r($exists);die;
		if($exists == '0')
		{	
			$this->dbforge->add_column(LINEUP_MASTER,$fields);
		}	
	}

	public function down() {
		//$this->dbforge->drop_column(LINEUP_MASTER, 'is_pl_team');
	}
}	

