<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sports_team_formation extends CI_Migration {

  public function up()
  {
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    if($this->db->field_exists('max_player_per_team', LEAGUE))
    {
      $this->dbforge->drop_column(LEAGUE,'max_player_per_team');
    }

    if(!$this->db->field_exists('max_player_per_team', MASTER_SPORTS))
    {
      $fields = array(
                  'max_player_per_team' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 7,
                    'after' => 'team_player_count'
                  )
                );
      $this->dbforge->add_column(MASTER_SPORTS,$fields);

      $master_sports =array(
            array (
                'max_player_per_team' => 4,
                'sports_id' => 8,
            ),
            array (
                'max_player_per_team' => 5,
                'sports_id' => 4,
            ),
            array (
                'max_player_per_team' => 9,
                'sports_id' => 3,
            ),
            array (
                'max_player_per_team' => 4,
                'sports_id' => 9,
            ),
            array (
                'max_player_per_team' => 3,
                'sports_id' => 10,
            ),
            array (
                'max_player_per_team' => 3,
                'sports_id' => 11,
            ),
            array (
                'max_player_per_team' => 3,
                'sports_id' => 12,
            )
      );
      $this->db->update_batch(MASTER_SPORTS,$master_sports,'sports_id');
    }

    if(!$this->db->table_exists('sports_config_history'))
    {
      $fields = array(
        'history_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'admin_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'ip_address' => array(
          'type' => 'VARCHAR',
          'constraint' => 20,
          'null' => TRUE
        ),
        'data' => array(
          'type' => 'JSON',
          'null' => FALSE
        ),
        'date_created' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('history_id', TRUE);
      $this->dbforge->create_table('sports_config_history',FALSE,$attributes);
    }

    //Trasaction end
    $this->db->trans_complete();
    if($this->db->trans_status() === FALSE )
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