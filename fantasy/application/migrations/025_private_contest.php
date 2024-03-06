<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Private_contest extends CI_Migration {

  public function up()
  {
    $fields = array(
        'host_rake' => array(
                'type' => 'FLOAT',
                'null' => FALSE,
                'default' => 0,
                'after' => 'site_rake',
                'comment' => "For private contest only, from admin"
        ),
        'is_final_prize_details_updated' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'comment' => "For private contest only after contest starts"
        ),
        'host_rake_awarded' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'comment' => "commission to creator after contest completion"
        )
    );
    $this->dbforge->add_column(CONTEST,$fields);

    $update_field = array(
      'contest_description' => array(
              'type' => 'varchar',
              'constraint' => 200,
      )
    );
    $this->dbforge->modify_column(CONTEST, $update_field);
  }

  public function down()
  {
    //down script 
    $this->dbforge->drop_column(CONTEST, 'host_rake');
    $this->dbforge->drop_column(CONTEST, 'is_final_prize_details_updated');

    $update_field = array(
      'contest_description' => array(
              'type' => 'varchar',
              'constraint' => 100,
      )
    );
    $this->dbforge->modify_column(CONTEST, $update_field);
  }
}