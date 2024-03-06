<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Quiz_image extends CI_Migration {

    public function up() {
        
        $fields = array(
            'question_image' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'after' => 'question_text'
            )
        );
        if(!$this->db->field_exists('question_image', QUIZ_QUESTION)){
            $this->dbforge->add_column(QUIZ_QUESTION,$fields);
        }
    }
    public function down()
    {
        //down script
    }
}
