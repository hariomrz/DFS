<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tamil_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `tam_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `tam_name` = 'ஒரு நண்பரைப் பார்க்கவும்' WHERE `banner_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `tam_name` = 'வைப்பு' WHERE `banner_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `tam_name` = 'பதிவுபெறுதல்' WHERE `banner_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." ADD `tam_scoring_category_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_scoring_category_name`;";
	  	$this->db->query($sql);

	  	$sql = "update ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set tam_scoring_category_name=scoring_category_name;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_RULES)." ADD `tam_score_position` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_score_position`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `tam_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_display_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'பேஸ்பால்' WHERE `sports_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'மட்டைப்பந்து' WHERE `sports_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'கால்பந்து' WHERE `sports_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'கால்பந்து' WHERE `sports_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'பாஸ்கட்பால்' WHERE `sports_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'கபாடி' WHERE `sports_id` = 8;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'கோல்ஃப்' WHERE `sports_id` = 9;";
	  	$this->db->query($sql);	  

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'பூப்பந்து' WHERE `sports_id` = 10;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'டென்னிஸ்' WHERE `sports_id` = 11;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `tam_display_name` = 'பப்' WHERE `sports_id` = 12;";
	  	$this->db->query($sql);

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'tam_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'tam_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'tam_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'tam_display_name');
	}

}