<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booster extends CI_Migration 
{

	public function up() {
		
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();

    	$fields = array(
	      'booster_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'auto_increment' => TRUE,
	        'null' => FALSE
	      ),
	      'sports_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'default' => CRICKET_SPORTS_ID,
	      ),
	      'position_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'null' => FALSE
	      ),
	      'name' => array(
	        'type' => 'VARCHAR',
	        'constraint' => 150
	      ),
	      'display_name' => array(
	        'type' => 'VARCHAR',
	        'constraint' => 150
	      ),
	      'image_name' => array(
	        'type' => 'VARCHAR',
	        'constraint' => 150,
	        'null' => FALSE
	      ),
	      'points' => array(
	        'type' => 'DECIMAL',
	        'constraint' => '10,2',
	        'default'=>'0.00'
	      ),
	      'status' => array(
	        'type' => 'TINYINT',
	        'constraint' => 1,
	        'default' => 1,
	        'comment' => '0-Inactive,1-Active'
	      ),
	      'date_created' => array(
	        'type' => 'DATETIME',
	        'null' => TRUE
	      ),
	      'date_modified' => array(
	        'type' => 'DATETIME',
	        'null' => TRUE
	      )
	    );
	    $attributes = array('ENGINE'=>'InnoDB');
	    $this->dbforge->add_field($fields);
	    $this->dbforge->add_key('booster_id', TRUE);
	    $this->dbforge->create_table(BOOSTER,FALSE,$attributes);

	    //save cricket default record
	    $booster_list = array(
              	array(
                    'sports_id' => CRICKET_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Fours',
                    'display_name' => 'Fours',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'sports_id' => CRICKET_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Sixes',
                    'display_name' => 'Sixes',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'sports_id' => CRICKET_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Wickets',
                    'display_name' => 'Wickets',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	),
              	array(
                    'sports_id' => CRICKET_SPORTS_ID,
                    'position_id' => 0,
                    'name' => 'Run Outs',
                    'display_name' => 'Run Outs',
                    'image_name' => '',
                    'points'=> 1,
                    'status'=> 0,
                    'date_created' => format_date(),
                    'date_modified' => format_date()
              	)
          	);

    	$this->db->insert_batch(BOOSTER,$booster_list);

    	//booster collection table
    	$fields = array(
	      	'id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'auto_increment' => TRUE,
		        'null' => FALSE
	      	),
	      	'booster_id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'null' => FALSE
	      	),
	      	'collection_master_id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'null' => FALSE
	      	),
	      	'position_id' => array(
		        'type' => 'INT',
		        'constraint' => 11,
		        'default' => '0'
	      	),
	      	'points' => array(
		        'type' => 'DECIMAL',
		        'constraint' => '10,2',
		        'default'=>'0.00'
	      	)
	    );
	    $attributes = array('ENGINE'=>'InnoDB');
	    $this->dbforge->add_field($fields);
	    $this->dbforge->add_key('id', TRUE);
	    $this->dbforge->create_table(BOOSTER_COLLECTION,FALSE,$attributes);

	    //lineup master booster
		$fields = array(
			'booster_id' => array(
				'type' => 'INT',
				'constraint' => '11',
				'default' => 0
			)
		);
		$this->dbforge->add_column(LINEUP_MASTER,$fields);
		
		//lineup master contest booster points
		$fields = array(
			'booster_points' => array(
		        'type' => 'DECIMAL',
		        'constraint' => '10,2',
		        'default'=>'0.00'
	      	)
		);
		$this->dbforge->add_column(LINEUP_MASTER_CONTEST,$fields);
		
		//game_player_scoring booster
		$fields = array(
			'booster_break_down' => array(
				'type' => 'JSON',
				'null' => TRUE
			)
		);
		$this->dbforge->add_column(GAME_PLAYER_SCORING,$fields);

	  	//Trasaction end
	    $this->db->trans_complete();
	    if ($this->db->trans_status() === FALSE )
	    {
	        $this->db->trans_rollback();
	    }
	    else
	    {
	       $this->db->trans_commit();
	    }
	}

	public function down() {
		//$this->dbforge->drop_table(BOOSTER);
	    //$this->dbforge->drop_table(BOOSTER_COLLECTION);
	    //$this->dbforge->drop_column(LINEUP_MASTER, 'booster_id');
		//$this->dbforge->drop_column(LINEUP_MASTER_CONTEST, 'booster_points');
		//$this->dbforge->drop_column(GAME_PLAYER_SCORING, 'booster_break_down');
	}

}