<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Master_source_private_contest extends CI_Migration{

	public function up(){
		$value = array(
            'master_source_id' => '157',
            'source' => '304',
            'name'=> 'Private contest host commission'
        );
        $this->db->insert(MASTER_SOURCE,$value);
	}

	public function down()
  {
	//down script  
	$this->db->where('master_source_id', '156');
	$this->db->delete(MASTER_SOURCE);
  }

}
?>