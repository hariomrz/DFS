<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Season_question extends CI_Migration {

  public function up()
  {
  	   //up script
    	$fields = array(
          'reference_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'default' => 0,
          )
  	 );
  	  
       if(!$this->db->field_exists('reference_id', SEASON_QUESTION)){
          $this->dbforge->add_column(SEASON_QUESTION,$fields);
       }
  }

  public function down()
  {
  	 //down script
    	//$this->dbforge->drop_column(SEASON_QUESTION, 'reference_id');
  }

}