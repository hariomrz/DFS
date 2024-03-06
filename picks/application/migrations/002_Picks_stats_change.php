<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Picks_stats_change extends CI_Migration {

        public function up()
        {
                $fields_stats_text = array(
                        'stats_text' => 
                                array(
                                'type' => 'VARCHAR',
                                'constraint'  => 255,
                                'null' => TRUE
                        )
                );
                if(!$this->db->field_exists('stats_text', PICKS)){
                        $this->dbforge->add_column(PICKS,$fields_stats_text);
                }
        }

        public function down()
        {
                
        }
}