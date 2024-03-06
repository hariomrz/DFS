<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_contestshare extends CI_Migration {

  public function up()
  {

    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

      $fields = array(
          'network_contest' => array(
          'type' => 'TINYINT',
          'constraint' => '1',
          'default' => 0,
          'Comment' => '1=>Network Contest'
        )
    );
    $this->dbforge->add_column(INVITE,$fields);
    $this->db->query('ALTER TABLE '.$this->db->dbprefix(INVITE).' DROP INDEX game_unique_id, ADD UNIQUE game_unique_id (contest_id,email,user_id,season_type,network_contest) USING BTREE');

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
      
        //$this->dbforge->drop_column(INVITE, 'network_contest');
        //$this->db->query('ALTER TABLE '.$this->db->dbprefix(INVITE).' DROP INDEX game_unique_id, ADD UNIQUE game_unique_id (contest_id,email,user_id,season_type) USING BTREE');

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