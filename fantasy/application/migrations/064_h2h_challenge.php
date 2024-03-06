<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_H2h_challenge extends CI_Migration {

	public function up()
	{
		//up script
		$result = $this->db->select('*')->from(MASTER_GROUP)->where('group_name',"H2H Challenge")->get()->num_rows();
        if(!$result){
        	$insert_data = array(
				"group_name" => "H2H Challenge",
				"description" => "H2H Challenge",
				"icon" => "h2h_challenge.png",
				"is_private" => 0,
				"status" => 1,
				"is_default" => 1
			);
			$this->db->insert(MASTER_GROUP,$insert_data);
		}
	
		if(!$this->db->table_exists(COLLECTION_TEMPLATE))
		{
			//collection template table
	    	$fields = array(
		      	'id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'auto_increment' => TRUE,
			        'null' => FALSE
		      	),
		      	'collection_master_id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'null' => FALSE
		      	),
		      	'contest_template_id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'null' => FALSE
		      	)
		    );
		    $attributes = array('ENGINE'=>'InnoDB');
		    $this->dbforge->add_field($fields);
		    $this->dbforge->add_key('id', TRUE);
		    $this->dbforge->create_table(COLLECTION_TEMPLATE,FALSE,$attributes);
		}

		if(!$this->db->table_exists(H2H_CMS))
		{
			//collection template table
	    	$fields = array(
		      	'id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'auto_increment' => TRUE,
			        'null' => FALSE
		      	),
			    'name' => array(
			      'type' => 'VARCHAR',
			      'constraint' => 150,
			      'null' => FALSE
			    ),
			    'description' => array(
			      'type' => 'VARCHAR',
			      'constraint' => 255,
			      'null' => FALSE
			    ),
			    'image_name' => array(
			      'type' => 'VARCHAR',
			      'constraint' => 150,
			      'null' => FALSE
			    ),
			    'bg_image' => array(
			      'type' => 'VARCHAR',
			      'constraint' => 150,
			      'null' => FALSE
			    ),
			    'added_date' => array(
			      'type' => 'DATETIME',
			      'null' => TRUE,
			      'default' => NULL,
			    ),
			    'updated_date' => array(
			      'type' => 'DATETIME',
			      'null' => TRUE,
			      'default' => NULL,
			    )
		    );
		    $attributes = array('ENGINE'=>'InnoDB');
		    $this->dbforge->add_field($fields);
		    $this->dbforge->add_key('id', TRUE);
		    $this->dbforge->create_table(H2H_CMS,FALSE,$attributes);
		}

		if(!$this->db->table_exists(H2H_USERS))
		{
			//h2h users table
	    	$fields = array(
		      	'id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'auto_increment' => TRUE,
			        'null' => FALSE
		      	),
		      	'user_id' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'null' => FALSE
		      	),
		      	'total' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'default' => 0
		      	),
		      	'total_win' => array(
			        'type' => 'INT',
			        'constraint' => 11,
			        'default' => 0
		      	),
			    'date_modified' => array(
			      	'type' => 'DATETIME',
			      	'null' => TRUE,
			      	'default' => NULL
			    )
		    );
		    $attributes = array('ENGINE'=>'InnoDB');
		    $this->dbforge->add_field($fields);
		    $this->dbforge->add_key('id', TRUE);
		    $this->dbforge->create_table(H2H_USERS,FALSE,$attributes);

		    $this->db->query('ALTER TABLE '.$this->db->dbprefix(H2H_USERS).' ADD CONSTRAINT unique_key UNIQUE (user_id)');
		}
    }

	public function down()
	{
		//down script 
		//$this->dbforge->drop_table(COLLECTION_TEMPLATE);
		//$this->db->delete(MASTER_GROUP,array("group_name" => "H2H Challenge"));
	}

}
