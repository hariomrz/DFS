<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Second_inn extends CI_Migration {

	public function up() 
	{
      
        $sql = "ALTER TABLE ".$this->db->dbprefix(CONTEST)." ADD `is_2nd_inning` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_scratchwin`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(CONTEST_TEMPLATE)." ADD `is_2nd_inning` TINYINT(1) NULL DEFAULT '0' AFTER `is_scratchwin`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(COLLECTION_MASTER)." ADD `2nd_inning_date` DATETIME NULL DEFAULT NULL AFTER `season_scheduled_date`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON)." ADD `2nd_inning_date` VARCHAR(50) NOT NULL AFTER `scheduled_date`, ADD `second_inning_update` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1=>Update by Admin' AFTER `2nd_inning_date`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(LINEUP_MASTER)." ADD `is_2nd_inning` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_pl_team`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(COLLECTION_MASTER)." ADD `is_2nd_inn_lineup_processed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `2nd_inning_date`";
        $this->db->query($sql);
        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON)." ADD `2nd_inning_team_uid` VARCHAR(100) NULL DEFAULT NULL COMMENT 'team_uid for Batting second inning' AFTER `result_info`";
        $this->db->query($sql);
        
        $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_PLAYER_SCORING)." ADD `2nd_inning_score` FLOAT NOT NULL DEFAULT '0' AFTER `expected_score`";
        $this->db->query($sql);
    }

    public function down() 
	{

	}
}