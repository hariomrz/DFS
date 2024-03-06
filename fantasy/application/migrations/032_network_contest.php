<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_contest extends CI_Migration {

  public function up()
  {

    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

      $fields = array(
          'network_max_prize_pool' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default' => 0,
          'null' => FALSE
        )
    );
    $this->dbforge->add_column(NETWORK_CONTEST,$fields);

    //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }
  	
  }

  public function down()
  {
    //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      
        $$this->dbforge->drop_column(NETWORK_CONTEST, 'network_max_prize_pool');

      //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }  
	   
  }
}