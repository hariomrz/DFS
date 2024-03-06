<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Punjabi_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `pun_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ben_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(TRANSACTION_MESSAGES)." ADD `pun_message` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ben_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `pun_title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_title`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `pun_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_desc`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਟੂਰਨਾਮੈਟ ਮੋਡ',pun_desc='ਪ੍ਰੋ ਸੀਜ਼ਨ ਲੰਬੇ ਖਿਡਾਰੀ? ਇੱਥੇ ਪੂਰੇ ਸੀਜ਼ਨ ਲਈ ਖੇਡੋ' WHERE `sports_hub_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਡੇਲੀ ਫੈਨਸੀ ਸਪੋਰਟਸ',pun_desc='ਰੋਜ਼ਾਨਾ ਕਲਪਨਾ ਖੇਡਾਂ ਰਵਾਇਤੀ ਕਲਪਨਾ ਖੇਡਾਂ ਨਾਲੋਂ ਵਧੇਰੇ ਉਤਸ਼ਾਹਪੂਰਨ ਹੁੰਦੀਆਂ ਹਨ' WHERE `sports_hub_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਪ੍ਰਧਾਨ ਅਤੇ ਜਿੱਤ ਦੇ ਸਿੱਕੇ',pun_desc='ਕੋਈ ਕਲਪਨਾ ਦੇ ਹੁਨਰ ਦੀ ਲੋੜ ਨਹੀਂ. ਸਿਰਫ ਨਤੀਜੇ ਦੀ ਭਵਿੱਖਬਾਣੀ ਕਰੋ ਅਤੇ ਸਿੱਕੇ ਜਿੱਤੋ' WHERE `sports_hub_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਤਸਵੀਰ',pun_desc='ਗੇਮ ਖੇਡ ਬਹੁਤ ਅਸਾਨ ਹੈ. ਬਸ ਜਿੱਤਣ ਵਾਲਾ ਪਾਸਾ ਚੁਣੋ' WHERE `sports_hub_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਬਹੁ ਖੇਲ',pun_desc='ਮਲਟੀ ਗੇਮਜ਼ ਕਲਪਨਾ ਖੇਡਾਂ ਰਵਾਇਤੀ ਕਲਪਨਾ ਖੇਡਾਂ ਨਾਲੋਂ ਕਿਤੇ ਵਧੇਰੇ ਦਿਲਚਸਪ ਹਨ' WHERE `sports_hub_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਖੁੱਲਾ ਭਾਸ਼ਣ',pun_desc='ਸਿਰਫ ਨਤੀਜੇ ਦੀ ਭਵਿੱਖਬਾਣੀ ਕਰੋ ਅਤੇ ਸਿੱਕੇ ਜਿੱਤੋ' WHERE `sports_hub_id` = 6;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `pun_title` = 'ਖੇਡਣ ਲਈ ਮੁਫ਼ਤ',pun_desc='ਰੋਜ਼ਾਨਾ ਕਲਪਨਾ ਪੂਰੀ ਤਰ੍ਹਾਂ ਮੁਫਤ ਖੇਡੋ ਅਤੇ ਦਿਲਚਸਪ ਇਨਾਮ ਜਿੱਤੋ.' WHERE `sports_hub_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `pun_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_meta_keyword`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `pun_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_page_title`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `pun_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_meta_desc`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `pun_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_page_content`;";
	  	$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'pun_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'pun_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'pun_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'pun_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'pun_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'pun_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'pun_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'pun_page_content');
	}

}