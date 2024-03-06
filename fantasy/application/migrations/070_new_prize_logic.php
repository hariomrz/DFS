<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_new_prize_logic extends CI_Migration 
{

	public function up() {
		//up script
		$fields = array(
			'current_prize' => array(
				'type' => 'JSON',
				'default' => NULL
			)
		);
		$sql = "SHOW COLUMNS FROM ".$this->db->dbprefix(CONTEST)." LIKE 'current_prize'";
		$result = $this->db->query($sql);
		$exists = $result->num_rows();
		//echo "<pre>";print_r($exists);die;
		if($exists == '0')
		{	
			$this->dbforge->add_column(CONTEST,$fields);
		}	
	}

	public function down() {
		//$this->dbforge->drop_column(CONTEST, 'current_prize');
	}
}	

