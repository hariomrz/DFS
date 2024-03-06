<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Prize_distribution extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
            'is_tie_breaker' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'consolation_prize',
                    'null' => FALSE
            ),
            'prize_value_type' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'is_tie_breaker',
                    'null' => FALSE
            )
	  );
	  $this->dbforge->add_column(CONTEST,$fields);

	  //up script
  	$fields = array(
            'is_tie_breaker' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'consolation_prize',
                    'null' => FALSE
            ),
            'prize_value_type' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'is_tie_breaker',
                    'null' => FALSE
            )
	  );
	  $this->dbforge->add_column(CONTEST_TEMPLATE,$fields);

    //up script
    $fields = array(
            'prize_data' => array(
                    'type' => 'JSON',
                    'after' => 'won_amount',
                    'null' => TRUE
            )
    );
    $this->dbforge->add_column(LINEUP_MASTER_CONTEST,$fields);
  }

  public function down()
  {
   	//down script
   	$this->dbforge->drop_column(CONTEST, 'is_tie_breaker');
    $this->dbforge->drop_column(CONTEST, 'prize_value_type');
   	$this->dbforge->drop_column(CONTEST_TEMPLATE, 'is_tie_breaker');
    $this->dbforge->drop_column(CONTEST_TEMPLATE, 'prize_value_type');
    $this->dbforge->drop_column(LINEUP_MASTER_CONTEST, 'prize_data');
  }
}