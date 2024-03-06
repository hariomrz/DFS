<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_French_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `fr_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `fr_name` = 'Référez un ami' WHERE `vi_banner_management`.`banner_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `fr_name` = 'Dépôt' WHERE `vi_banner_management`.`banner_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `fr_name` = 'Sinscrire' WHERE `vi_banner_management`.`banner_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." ADD `fr_scoring_category_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_scoring_category_name`;";
	  	$this->db->query($sql);

	  	$sql = "update ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set fr_scoring_category_name=scoring_category_name;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_RULES)." ADD `fr_score_position` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_score_position`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `fr_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_display_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'BASE-BALL' WHERE `vi_master_sports_format`.`master_sports_format_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'CRIQUET' WHERE `vi_master_sports_format`.`master_sports_format_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'FOOTBALL' WHERE `vi_master_sports_format`.`master_sports_format_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'FOOTBALL' WHERE `vi_master_sports_format`.`master_sports_format_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'BASKETBALL' WHERE `vi_master_sports_format`.`master_sports_format_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'KABADDI' WHERE `vi_master_sports_format`.`master_sports_format_id` = 6;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'LE GOLF' WHERE `vi_master_sports_format`.`master_sports_format_id` = 7;";
	  	$this->db->query($sql);	  

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'BADMINTON' WHERE `vi_master_sports_format`.`master_sports_format_id` = 8;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `fr_display_name` = 'TENNIS' WHERE `vi_master_sports_format`.`master_sports_format_id` = 9;";
	  	$this->db->query($sql);

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'fr_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'fr_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'fr_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'fr_display_name');
	}

}