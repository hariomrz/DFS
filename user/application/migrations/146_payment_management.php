<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Payment_management extends CI_Migration {

  private $CI;

  public function up()
  {
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    $fields = array(
        
        'pg_key' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => TRUE
        ),
          'title' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE
        ),
          'description' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => TRUE
        ),
          'image_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE
        ),
      
      );

      if(!$this->db->field_exists('pg_key', MASTER_PG) && !$this->db->field_exists('title', MASTER_PG) && !$this->db->field_exists('description', MASTER_PG) && !$this->db->field_exists('image_name', MASTER_PG)){
        $this->dbforge->add_column(MASTER_PG,$fields);
      }
   
      // UPDATE `vi_master_pg` set pg_key = CONCAT("allow_",LOWER(name)),title=name,description=name WHERE pg_key IS NULL
      $sql = "UPDATE ".$this->db->dbprefix(MASTER_PG)." SET `pg_key` = CONCAT('allow_',LOWER(name)),`title`=name,`description`=name WHERE `pg_key` IS NULL;";
      $this->db->query($sql);
              
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
     
  }
  
}
