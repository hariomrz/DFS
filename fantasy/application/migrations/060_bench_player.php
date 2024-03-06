<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bench_player extends CI_Migration 
{

	public function up() {
		
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();

    	$fields = array(
	      'bench_player_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'auto_increment' => TRUE,
	        'null' => FALSE
	      ),
	      'lineup_master_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'null' => FALSE
	      ),
	      'priority' => array(
	        'type' => 'TINYINT',
	        'constraint' => 1,
	        'default' => 1
	      ),
	      'player_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'null' => FALSE
	      ),
	      'out_player_id' => array(
	        'type' => 'INT',
	        'constraint' => 11,
	        'default' => 0
	      ),
	      'status' => array(
	        'type' => 'TINYINT',
	        'constraint' => 1,
	        'default' => 0,
	        'comment' => '0-Pending,1-Processed,2-NA/No_LineupOut'
	      ),
	      'reason' => array(
	        'type' => 'VARCHAR',
	        'constraint' => 255,
	        'null' => TRUE
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
	    $this->dbforge->add_key('bench_player_id', TRUE);
	    $this->dbforge->create_table(BENCH_PLAYER,FALSE,$attributes);

	    //bench_process in collection master table
		$fields = array(
			'bench_processed' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
		        'comment' => '0-Pending,1-Processed'
			)
		);
		$this->dbforge->add_column(COLLECTION_MASTER,$fields);

		//update status
		$this->db->set('bench_processed','2');
		$this->db->where('is_lineup_processed != ','0');
        $this->db->update(COLLECTION_MASTER);
		
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
		//$this->dbforge->drop_table(BENCH_PLAYER);
	    //$this->dbforge->drop_column(COLLECTION_MASTER, 'bench_process');
	}

}