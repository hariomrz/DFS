<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Season_lineup_out extends CI_Migration {

  public function up()
  {

    if(!$this->db->field_exists('lineup_announced_at', SEASON))
    {
        //Trasaction start
        $this->db->trans_strict(TRUE);
        $this->db->trans_start();
        $fields = array(
                          'lineup_announced_at' => array(
                          'type' => 'DATETIME',
                          'default' => NULL,
                        )
                    );
        $this->dbforge->add_column(SEASON,$fields);
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

  public function down()
  {
     
  }
  
}