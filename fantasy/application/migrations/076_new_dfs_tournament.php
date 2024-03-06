<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_dfs_tournament extends CI_Migration {

  public function up()
  {
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    $this->dbforge->rename_table('tournament', 'old_tournament');
    $this->dbforge->rename_table('tournament_banner', 'old_tournament_banner');
    $this->dbforge->rename_table('tournament_completed_team', 'old_tournament_completed_team');
    $this->dbforge->rename_table('tournament_invite', 'old_tournament_invite');
    $this->dbforge->rename_table('tournament_lineup', 'old_tournament_lineup');
    $this->dbforge->rename_table('tournament_scoring_rules', 'old_tournament_scoring_rules');
    $this->dbforge->rename_table('tournament_season', 'old_tournament_season');
    $this->dbforge->rename_table('tournament_team', 'old_tournament_team');
    $this->dbforge->rename_table('user_tournament', 'old_user_tournament');
    $this->dbforge->rename_table('user_tournament_season', 'old_user_tournament_season');

    $fields = array(
        'tournament_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'sports_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'league_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE
        ),
        'image' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => FALSE
        ),
        'start_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'end_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'prize_detail' => array(
          'type' => 'JSON',
          'null' => FALSE
        ),
        'match_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'banner_images' => array(
          'type' => 'JSON',
          'null' => TRUE
        ),
        'is_tie_breaker' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'is_notify' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'is_pin' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0,
          'comment' => '0-Active,1-Cancel,2-Complete,3-PrizeDistributed'
        ),
        'cancel_reason' => array(
          'type' => 'TEXT',
          'null' => TRUE
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'modified_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('tournament_id', TRUE);
      $this->dbforge->create_table(TOURNAMENT,FALSE,$attributes);

      $sql = "ALTER TABLE ".$this->db->dbprefix(TOURNAMENT)." ADD INDEX `sports_id` (`sports_id`) USING BTREE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(TOURNAMENT)." ADD INDEX `league_id` (`league_id`) USING BTREE;";
      $this->db->query($sql);

      //tournament history
      $fields = array(
        'history_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'tournament_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'total_score' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default' => '0.00',
          'null' => FALSE
        ),
        'rank_value' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'is_winner' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'prize_data' => array(
          'type' => 'JSON',
          'null' => TRUE
        ),
        'custom_data' => array(
          'type' => 'JSON',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('history_id', TRUE);
      $this->dbforge->create_table(TOURNAMENT_HISTORY,FALSE,$attributes);

      $this->db->query('ALTER TABLE '.$this->db->dbprefix(TOURNAMENT_HISTORY).' ADD UNIQUE tournament_id (`tournament_id`, `user_id`)');


      //tournament season
      $fields = array(
        'tournament_season_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'tournament_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'season_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'cm_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'season_scheduled_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('tournament_season_id', TRUE);
      $this->dbforge->create_table(TOURNAMENT_SEASON,FALSE,$attributes);

      $this->db->query('ALTER TABLE '.$this->db->dbprefix(TOURNAMENT_SEASON).' ADD UNIQUE tournament_id (`tournament_id`, `season_id`)');

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