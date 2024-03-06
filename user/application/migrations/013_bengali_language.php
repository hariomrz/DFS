<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bengali_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `ben_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fr_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(TRANSACTION_MESSAGES)." ADD `ben_message` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `fr_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `ben_title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_title`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `ben_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_desc`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'টুর্নামেন্ট মোড',ben_desc='প্রো সিজন লং প্লেয়ার? পুরো মরসুমের জন্য এখানে খেলুন' WHERE `sports_hub_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'প্রতিদিনের মনোরম স্পোর্টস',ben_desc='প্রতিদিনের ফ্যান্টাসি স্পোর্টস ঐতিহ্যগত তিহ্যবাহী ফ্যান্টাসি ক্রীড়াগুলির চেয়ে অনেক বেশি উত্তেজনাপূর্ণ' WHERE `sports_hub_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'রাষ্ট্রপত্রে এবং বিজয় মুদ্রা',ben_desc='কোন কল্পনা দক্ষতা প্রয়োজন। কেবল ফলাফলের পূর্বাভাস দিন এবং কয়েন জিতে নিন' WHERE `sports_hub_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'PICKEM',ben_desc='গেম খেলা অত্যন্ত সহজ। শুধু বিজয়ী পক্ষ বাছাই করুন' WHERE `sports_hub_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'মাল্টি গেমস',ben_desc='মাল্টি গেমস ফ্যান্টাসি স্পোর্টস ঐতিহ্যগত তিহ্যবাহী ফ্যান্টাসি স্পোর্টসের চেয়ে অনেক বেশি উত্তেজনাপূর্ণ' WHERE `sports_hub_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'ওপেন প্রিডিকশন',ben_desc='কেবল ফলাফলের পূর্বাভাস দিন এবং কয়েন জিতে নিন' WHERE `sports_hub_id` = 6;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `ben_title` = 'খেলা বিনামূল্যে',ben_desc='প্রতিদিনের কল্পনা সম্পূর্ণ বিনামূল্যে খেলুন এবং উত্তেজনাপূর্ণ পুরষ্কার জিতে নিন।' WHERE `sports_hub_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ben_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_meta_keyword`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ben_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_page_title`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ben_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_meta_desc`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ben_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_page_content`;";
	  	$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'ben_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'ben_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'ben_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'ben_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'ben_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'ben_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'ben_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'ben_page_content');
	}

}