<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cricket_live_score_card extends CI_Migration 
{

	public function up() {
		
		//Trasaction start
    	$this->db->trans_strict(TRUE);
    	$this->db->trans_start();
    	//Add three field 
		$sql="ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_CRICKET)." ADD `out_string` VARCHAR(250) NULL DEFAULT NULL AFTER `man_of_match`, ADD `batting_order` INT NOT NULL DEFAULT '0' AFTER `out_string`, ADD `bowling_order` INT NOT NULL DEFAULT '0' AFTER `batting_order`";
	  	$this->db->query($sql);

	  	//Add one field fall_of_wickets on season table field 
		$sql="ALTER TABLE ".$this->db->dbprefix(SEASON)." ADD `fall_of_wickets` JSON NULL DEFAULT NULL AFTER `score_updated_info`";
	  	$this->db->query($sql);

	  	//Add one field result_info on season table field;

	  	$sql="ALTER TABLE ".$this->db->dbprefix(SEASON)." ADD `result_info` VARCHAR(250) NULL DEFAULT NULL AFTER `fall_of_wickets`";
	  	$this->db->query($sql);

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
		$this->dbforge->drop_column(GAME_STATISTICS_CRICKET, 'out_string');
		$this->dbforge->drop_column(GAME_STATISTICS_CRICKET, 'batting_order');
		$this->dbforge->drop_column(GAME_STATISTICS_CRICKET, 'bowling_order');
		$this->dbforge->drop_column(SEASON, 'fall_of_wickets');
		$this->dbforge->drop_column(SEASON, 'result_info');
	}

}