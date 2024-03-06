<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Scoring_rules extends CI_Migration 
{

	public function up() 
	{
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();

    	$fields = array(
				          'new_score_points' => array(
						          'type' => 'FLOAT',
						          'null' => FALSE
	        			)	
	    			);
	    $this->dbforge->add_column(MASTER_SCORING_RULES,$fields);
		
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

	public function down() 
	{
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();

    	//$this->dbforge->drop_column(MASTER_SCORING_RULES, 'new_score_points');
		
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

}