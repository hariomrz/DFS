<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tamil_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `tam_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pun_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(TRANSACTION_MESSAGES)." ADD `tam_message` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `pun_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `tam_title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_title`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `tam_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_desc`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'போட்டி முறை',tam_desc='புரோ சீசன் லாங் பிளேயரா? முழு பருவத்திற்கும் இங்கே விளையாடுங்கள்' WHERE `game_key` = 'allow_tournament';";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'தினசரி கற்பனை விளையாட்டு',tam_desc='பாரம்பரிய கற்பனை விளையாட்டுகளை விட தினசரி கற்பனை விளையாட்டு உற்சாகமானது' WHERE `game_key` = 'allow_dfs';";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'கணிக்கவும் & நாணயங்களை வெல்லவும்',tam_desc='கேமிங் திறன்கள் தேவையில்லை. முடிவை முன்னறிவித்து நாணயங்களை வெல்லுங்கள்' WHERE `game_key` = 'allow_prediction';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'எம் பரிசுக் குளத்தைத் தேர்ந்தெடுங்கள்',tam_desc='கேம் பிளே மிகவும் எளிதானது. வென்ற பக்கத்தைத் தேர்ந்தெடுங்கள்' WHERE `game_key` = 'allow_pickem';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'மல்டி கேம்ஸ்',tam_desc='மல்டி கேம்ஸ் கற்பனை விளையாட்டு பாரம்பரிய கற்பனை விளையாட்டுகளை விட மிகவும் உற்சாகமானது' WHERE `game_key` = 'allow_multigame';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'திறந்த முன்னறிவிப்பாளர் - பரிசுக் குளம்',tam_desc='முடிவை முன்னறிவித்து நாணயங்களை வெல்லுங்கள்' WHERE `game_key` = 'allow_open_predictor';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'விளையாடுவதற்கு இலவசம்',tam_desc='தினசரி கற்பனையை முற்றிலும் இலவசமாக விளையாடுங்கள் மற்றும் அற்புதமான பரிசுகளை வெல்லுங்கள்.' WHERE `game_key` = 'allow_free2play';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'திறந்த முன்கணிப்பு - லீடர்போர்டு',tam_desc='முடிவை முன்னறிவித்து பரிசுகளை வெல்லுங்கள்' WHERE `game_key` = 'allow_fixed_open_predictor';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `tam_title` = 'ப்ராப் பேண்டஸி',tam_desc='முடிவை முன்னறிவித்து பரிசுகளை வெல்லுங்கள்' WHERE `game_key` = 'allow_prop_fantasy';";
		$this->db->query($sql);
	
	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tam_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_meta_keyword`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tam_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_page_title`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tam_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_meta_desc`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tam_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `pun_page_content`;";
	  	$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'tam_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'tam_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'tam_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'tam_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'tam_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'tam_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'tam_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'tam_page_content');
	}

}