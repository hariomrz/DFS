<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_French_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `fr_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `guj_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(TRANSACTION_MESSAGES)." ADD `fr_message` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `guj_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `fr_title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_title`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `fr_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_desc`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'LE MODE TOURNOI',fr_desc='Joueur Pro Season Long? Jouez ici toute la saison' WHERE `vi_sports_hub`.`sports_hub_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'SPORTS QUOTIDIENS FANTAISIE',fr_desc='Les sports fantastiques quotidiens sont beaucoup plus excitants que les sports fantastiques traditionnels' WHERE `vi_sports_hub`.`sports_hub_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'PREDICT & WIN COINS',fr_desc='Aucune compétence de fantaisie requise. Il suffit de prédire le résultat et de gagner des pièces' WHERE `vi_sports_hub`.`sports_hub_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'PICKEM',fr_desc='Le jeu est super facile. Choisissez simplement le côté gagnant' WHERE `vi_sports_hub`.`sports_hub_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'MULTI JEUX',fr_desc='Les sports fantastiques Multi Games sont beaucoup plus excitants que les sports fantastiques traditionnels' WHERE `vi_sports_hub`.`sports_hub_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'PRÉDICTION OUVERTE',fr_desc='Il suffit de prédire le résultat et de gagner des pièces' WHERE `vi_sports_hub`.`sports_hub_id` = 6;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `fr_title` = 'LIBRE DE JOUER',fr_desc='Jouez à la fantaisie quotidienne gratuitement et gagnez des prix excitants.' WHERE `vi_sports_hub`.`sports_hub_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `fr_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_meta_keyword`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `fr_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_page_title`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `fr_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_meta_desc`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `fr_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `guj_page_content`;";
	  	$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'fr_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'fr_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'fr_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'fr_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'fr_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'fr_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'fr_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'fr_page_content');
	}

}