<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Signup_parameters_tracking extends CI_Migration {

	public function up() {

        $tracking = array(
			'tracking' => array(
                'type' => 'JSON',
				'null' => TRUE,
                'default' => NULL
			)
		);
        $this->dbforge->add_column(USER, $tracking);
    }
}

?>