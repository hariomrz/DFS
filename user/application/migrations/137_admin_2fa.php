<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_2fa extends CI_Migration {

    public function up() {
        
        $fields = array(
            'two_fa' => array(
                'type'        => 'int',
                'constraint' => 11,
                'default' => 1,
                'after' =>'status'
            )
        );
        if(!$this->db->field_exists('two_fa', ADMIN)){
            $this->dbforge->add_column(ADMIN,$fields);
        }
    }
    public function down()
    {
        //down script
    }
}
