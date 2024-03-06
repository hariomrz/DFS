<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Auto_match_addition extends CI_Migration {

    function __construct()
    {

    }

    public function up() {
        //up script
        $fields = array(
          'auto_match_publish' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'after' => 'is_pin',
            'comment' => '0-Manual,1-Auto',
          )
        );
        if(!$this->db->field_exists('auto_match_publish', TOURNAMENT)){
            $this->dbforge->add_column(TOURNAMENT,$fields);
        }
    }

    public function down() {
        //down script
    }

}