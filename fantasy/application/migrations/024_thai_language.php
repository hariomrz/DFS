<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Thai_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `th_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `th_name` = 'แนะนำเพื่อน' WHERE `banner_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `th_name` = 'ฝาก' WHERE `banner_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `th_name` = 'ลงชื่อ' WHERE `banner_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." ADD `th_scoring_category_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_scoring_category_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'ปกติ' WHERE scoring_category_name='normal';";
        $this->db->query($sql);
          
        $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'โบนัส' WHERE scoring_category_name='bonus';";
	  	$this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'อัตราประหยัด' WHERE scoring_category_name='economy_rate';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'การกดปุ่ม' WHERE scoring_category_name='hitting';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'ทอย' WHERE scoring_category_name='pitching';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set th_scoring_category_name = 'อัตราการนัดหยุดงาน' WHERE scoring_category_name='strike_rate';";
        $this->db->query($sql);
        
	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_RULES)." ADD `th_score_position` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_score_position`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `th_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_display_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'เบสบอล' WHERE `sports_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'ฟุตบอล' WHERE `sports_id` = 2;";
	  	$this->db->query($sql);
          
	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'บาสเกตบอล' WHERE `sports_id` = 4;";
	  	$this->db->query($sql);
          
	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'ฟุตบอล' WHERE `sports_id` = 5;";
	  	$this->db->query($sql);
          
	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'คริกเก็ต' WHERE `sports_id` = 7;";
	  	$this->db->query($sql);
          
	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'กาบัดดี' WHERE `sports_id` = 8;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'กอล์ฟ' WHERE `sports_id` = 9;";
	  	$this->db->query($sql);	  

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'แบดมินตัน' WHERE `sports_id` = 10;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'เทนนิส' WHERE `sports_id` = 11;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `th_display_name` = 'ซีเอ' WHERE `sports_id` = 13;";
        $this->db->query($sql);

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'th_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'th_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'th_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'th_display_name');
	}

}