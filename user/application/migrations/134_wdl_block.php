<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Wdl_block extends CI_Migration {

    public function up() {

      // wdl_status filed add for block withdrawal request
        $field_one = array(
            'wdl_status'       => array(
            'type'          => 'TINYINT',
            'constraint'    => '1',
            'null'          => FALSE,
            'default'       => 1,
            'after'         =>'status',
            'comment'       =>'1 = Active, 2 = Block'
            ),
        );
    
        if(!$this->db->field_exists('wdl_status', USER)){
            $this->dbforge->add_column(USER,$field_one);
        }

    }
    public function down()
    {
        //down script
    }
}
