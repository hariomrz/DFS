<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_admin_distributor extends CI_Migration {

	public function up() {
		$fields = array(
        'recharge_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'amount' => array(
          'type' => 'FLOAT',
          'constraint' => 11,
          'null' => FALSE,
        ),
        'reference_id' => array(
            'type' => 'VARCHAR',
            'constraint' => 60,
            'null' => TRUE
          ),
          'upload_reciept' => array(
			'type' => 'VARCHAR',
			'constraint' => 100,
            'null' => FALSE
          ),
          'from_admin_id' => array(
            'type' => 'INT',
            'null' => FALSE
          ),
          'to_admin_id' => array(
            'type' => 'INT',
            'null' => FALSE 
		  ),
		  'status' => array(
            'type' => 'INT',
            'null' => FALSE 
		  ),
		  'status' => array(
            'type' => 'DATETIME',
            'null' => TRUE 
          )
        );

      $attributes = array('ENGINE' => 'InnoDB');
	  $this->dbforge->add_field($fields);
	  $this->dbforge->add_key('recharge_id',TRUE);
	  $this->dbforge->create_table(ADMIN_RECHARGE ,FALSE,$attributes);



	$fields2 = array(
        'dtransaction_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
		),
		'user_id' => array(
            'type' => 'INT',
            'null' => FALSE
          ),
          'admin_id' => array(
            'type' => 'INT',
            'null' => FALSE 
		  ),
        'amount' => array(
          'type' => 'FLOAT',
          'constraint' => 11,
          'null' => FALSE,
		),
		'created_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'status' => array(
            'type' => 'INT',
            'null' => FALSE 
		  ),
        );

    $attributes = array('ENGINE' => 'InnoDB');
	$this->dbforge->add_field($fields2);
	$this->dbforge->add_key('dtransaction_id',TRUE);
    $this->dbforge->create_table(ADMIN_TRANSACTION ,FALSE,$attributes);
    


    $categories = array(
			array(
				'role_id'		=> 2,
                'name'		    => "MASTERDISTRIBUTOR",
                'description'   => "",
            ),
			array(
				'role_id'		=> 3,
                'name'		    => "DISTRIBUTOR",
                'description'	=> "",
            ),
            array(
				'role_id'		=> 4,
                'name'		    => "AGENT",
                'description'	=> "",
			),
		);
    $this->db->update_batch(ADMIN_ROLES,$categories,'role_id');
    

    $categories2 = array(
			array(
                'admin_roles_rights_id'		=> 2,
    			'role_id'		=> 2,
                'right_ids'		=> '["distributors"]',
			),
			array(
                'admin_roles_rights_id'		=> 2,
				'role_id'		=> 3,
                'right_ids'		=> '["distributors"]',
            ),
            array(
                'admin_roles_rights_id'		=> 2,
				'role_id'		=> 4,
                'right_ids'		=> '["distributors"]',
			),
		);
    $this->db->update_batch(ADMIN_ROLES_RIGHTS,$categories2,"admin_roles_rights_id");


    $cms_custom_field = array(
                            'fullname' => array(
                            'type' => 'VARCHAR',
                            'constraint' => 100,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'new_password_requested'
                            ),

                            'mobile' => array(
                            'type' => 'VARCHAR',
                            'constraint' => 100,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'fullname'
                            ),
                            'address' => array(
                            'type' => 'VARCHAR',
                            'constraint' => 100,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'mobile'
                            ),
                            'city' => array(
                            'type' => 'VARCHAR',
                            'constraint' => 100,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'address'
                            ),
                            'state_id' => array(
                            'type' => 'INT',
                            'constraint' => 11,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'city'
                            ),
                            'country_id' => array(
                            'type' => 'INT',
                            'constraint' => 11,
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'state_id'
                            ),
                            'balance' => array(
                            'type' => 'FLOAT',
                            
                            'null' => TRUE,
                            'default'=>NULL,
                            'after'=>'state_id'
                            ),
  ); 
  
    $this->dbforge->add_column(ADMIN,$cms_custom_field);

    }

	public function down() {
		$this->dbforge->drop_table(ADMIN_RECHARGE);
        $this->dbforge->drop_table(ADMIN_TRANSACTION);
    }

}
