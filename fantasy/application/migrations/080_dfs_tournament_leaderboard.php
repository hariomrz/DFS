<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_dfs_tournament_leaderboard extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    //no_of_fixture
		$fields = array(
      'no_of_fixture' => array(    
        'type' => 'INT',
        'constraint' => 11,   
        'default' => 0,
        'null' => FALSE,
        'after' => 'is_pin',
        'comment'=> '0-All Fixture, other - nth fixture'   
      ),
    );

    if(!$this->db->field_exists('no_of_fixture', TOURNAMENT)){
      $this->dbforge->add_column(TOURNAMENT,$fields);
    }

    //is_top_team
		$fields = array(
      'is_top_team' => array(    
        'type' => 'TINYINT',
        'constraint' =>1,   
        'default' => 0,
        'null' => FALSE,
        'after' => 'no_of_fixture',
        'comment'=> '0-All Teams,1-Top Team'
      ),
    );

    if(!$this->db->field_exists('is_top_team', TOURNAMENT)){
      $this->dbforge->add_column(TOURNAMENT,$fields);
    }


    //contest_id
		$fields = array(
      'contest_id'=> array(    
        'type'=> 'INT',
        'constraint' =>11,   
        'default' => 0,
        'null' => FALSE,
        'after' => 'cm_id',
        'comment'=> '0-Not Assigned,other-Contest Id'
      ),
    );

    if(!$this->db->field_exists('contest_id', TOURNAMENT_SEASON)){
      $this->dbforge->add_column(TOURNAMENT_SEASON,$fields);
    }


    //tournament_history_teams table
    if(!$this->db->table_exists(TOURNAMENT_HISTORY_TEAMS))
    {
      $fields = array(
        'id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'auto_increment' => TRUE,
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
        'cm_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'contest_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'lmc_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'lm_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'team_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => FALSE
        ),
        'score' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
	        'null' => FALSE,
          'default' => '0.00',
        ),
        'game_rank' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'is_included'=> array(
          'type' => 'tinyint',
          'constraint' =>1, 
          'null' => FALSE,
          'default' => 0,
          'comment'=>  '0-NotIncluded,1-Included'
        ),
        'created_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
      );

      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id',TRUE);
      $this->dbforge->create_table(TOURNAMENT_HISTORY_TEAMS,FALSE,$attributes);

      //add unique key
      $sql = "ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_HISTORY_TEAMS)." ADD UNIQUE KEY history_id(tournament_id,cm_id,contest_id,lm_id,user_id);";
      $this->db->query($sql);
    }


    //Trasaction end
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE )
    {
      $this->db->trans_rollback();
    }else{
      $this->db->trans_commit();
    }
  }

  public function down()
  {
    //down script
  }

}