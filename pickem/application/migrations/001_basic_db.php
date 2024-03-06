<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Basic_db extends CI_Migration {

  public function up()
  {
  	
    //up script
    $fields = array(
      'is_score_predict' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,
        'after' => 'status',
        'comment' => '0:Regular,1:Score Predictor',
      )
    );
    if(!$this->db->field_exists('is_score_predict', TOURNAMENT)){
      $this->dbforge->add_column(TOURNAMENT,$fields);
    }

    
    $fields = array(
      'home_predict' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' =>NULL,
        'after' => 'score',
      )
    );
    if(!$this->db->field_exists('home_predict', USER_TEAM)){
      $this->dbforge->add_column(USER_TEAM,$fields);
    }

    $fields = array(
      'away_predict' => array(
        'type' => 'INT',
        'constraint' => 11,
        'default' =>NULL,
        'after' =>'score',
      )
    );
    if(!$this->db->field_exists('away_predict', USER_TEAM)){
      $this->dbforge->add_column(USER_TEAM,$fields);
    }

    $fields = array(
      'perfect_score_data' => array(
        'type' => 'json',
        'default' =>NULL,
        'after' =>'tie_breaker_answer',
      )
    );
    if(!$this->db->field_exists('perfect_score_data', USER_TOURNAMENT)){
      $this->dbforge->add_column(USER_TOURNAMENT,$fields);
    }
    
  }

  public function down()
  {
	 //down script
  }
}