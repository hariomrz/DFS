<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Optimisation extends CI_Migration {

	public function up()
	{
		//up script
		$fields = array(
	        'ldb' => array(    
	                'type' => 'TINYINT',    
	                'constraint' => 1,    
	                'default' => 0,    
	                'comment' => 'Leaderboard 0-NA,1-Available'
	        )
		);

		if(!$this->db->field_exists('ldb', LEAGUE)){

	  	    $this->dbforge->add_column(LEAGUE,$fields);
	    }

    }

	public function down()
	{
		//down script 	
	}

}
