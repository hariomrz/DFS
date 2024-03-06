<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_season_substitutes_list extends CI_Migration {

  public function up()
  {

    if(!$this->db->field_exists('substitute_list', SEASON))
    {
        //Trasaction start
        $this->db->trans_strict(TRUE);
        $this->db->trans_start();
        $fields = array(
                          'substitute_list' => array(
                          'type' => 'JSON',
                          'default' => NULL,
                          'AFTER' => 'playing_list',
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