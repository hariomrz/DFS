<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Picks_new_changes extends CI_Migration {

  public function up()
  {

  	$fields = array(
        'option_images' => 
        	array(
                'type' => 'JSON',
                'after' => 'option_4',
                'null' => TRUE
        )
	);
	 if(!$this->db->field_exists('option_images', PICKS)){
            $this->dbforge->add_column(PICKS,$fields);
      }

      $fields = array(
          'option_stats' => 
          	array(
                  'type' => 'JSON',
                  'after' => 'option_images',
                  'null' => TRUE
          )
  	);
	  if(!$this->db->field_exists('option_stats', PICKS)){
            $this->dbforge->add_column(PICKS,$fields);
    }

    $fields = array(
          'tie_breaker_question' => 
            array(
                  'type' => 'JSON',
                  'after' => 'wrong',
                  'null' => TRUE
          )
    );
    if(!$this->db->field_exists('tie_breaker_question', SEASON)){
            $this->dbforge->add_column(SEASON,$fields);
    } 
    $fields = array(
          'tie_breaker_answer' => 
            array(
                  'type' => 'INT',
                  'after' => 'tie_breaker_question',
                  'null' => FALSE
          )
    );
    if(!$this->db->field_exists('tie_breaker_answer', SEASON)){
            $this->dbforge->add_column(SEASON,$fields);
    }

    $fields = array(
        'tie_breaker_answer' => 
        array(
                'type' => 'INT',
                'after' => 'team_name',
                'default'=>0,
                'null' => FALSE
        )
    );
    if(!$this->db->field_exists('tie_breaker_answer', USER_TEAM)){
            $this->dbforge->add_column(USER_TEAM,$fields);
    } 

    $fields = array(
          'explaination' => 
            array(
                  'type' => 'TEXT',
                  'after' => 'answer',
                  'null' => TRUE
          )
    );
    if(!$this->db->field_exists('explaination', PICKS)){
            $this->dbforge->add_column(PICKS,$fields);
    }           
    $sql = "ALTER TABLE `vi_contest` CHANGE `is_tie_breaker` `is_tie_breaker` TINYINT(1) NOT NULL DEFAULT '1'";
    $this->db->query($sql);
  

        $fields = array(
                'explaination_image' => 
                array(
                        'type' => 'VARCHAR',
                        'constraint'  => 255,
                        'null' => TRUE
                )
        );
        if(!$this->db->field_exists('explaination_image', PICKS)){
                $this->dbforge->add_column(PICKS,$fields);
        }    
}

  public function down()
  {
	//down script 
	//$this->dbforge->drop_column(PICKS, 'option_images');
	//$this->dbforge->drop_column(PICKS, 'option_stats');
  //$this->dbforge->drop_column(CONTEST, 'tie_breaker_question');
  //$this->dbforge->drop_column(CONTEST, 'tie_breaker_answer');
  //$this->dbforge->drop_column(USER_CONTEST, 'tie_breaker_answer');
  }
}