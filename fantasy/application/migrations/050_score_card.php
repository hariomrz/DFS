<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_score_card extends CI_Migration 
{

  public function up()
  {
    //add filed in season table
    //up script
      $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON)." ADD `score_updated_info` JSON NULL DEFAULT NULL AFTER `is_pickem_published`";
      $this->db->query($sql);
  }

  public function down()
  {
	   $this->dbforge->drop_column(SEASON, 'score_updated_info'); 
  }

}