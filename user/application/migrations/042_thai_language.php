<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Thai_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `th_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `tam_message`;";
          $this->db->query($sql);
          
        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `th_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `en_subject`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(TRANSACTION_MESSAGES)." ADD `th_message` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `tam_message`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `th_title` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_title`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(SPORTS_HUB)." ADD `th_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_desc`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'โหมดการแข่งขัน',th_desc='ฤดูกาลเล่นยาว? เล่นทั้งฤดูกาลที่นี่' WHERE `game_key` = 'allow_tournament';";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'กีฬาแฟนตาซีรายวัน (DFS) เป็น',th_desc='กีฬาแฟนตาซีในชีวิตประจำวันมากขึ้นน่าตื่นเต้นกว่ากีฬาแฟนตาซีแบบดั้งเดิม' WHERE `game_key` = 'allow_dfs';";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'คาดการณ์และชนะเหรียญ',th_desc='ไม่มีทักษะการจินตนาการที่จำเป็น เพียงแค่ทำนายผลและชนะเหรียญ' WHERE `game_key` = 'allow_prediction';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'เลือก \'em รางวัล PoolPick\' em Pr',th_desc='การเล่นเกมเป็นซุปเปอร์ง่าย เพียงแค่เลือกฝ่ายชนะ' WHERE `game_key` = 'allow_pickem';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'หลายเกม',th_desc='เกมหลายเกมจินตนาการมีความน่าสนใจมากขึ้นกว่าเกมแฟนตาซีแบบดั้งเดิม' WHERE `game_key` = 'allow_multigame';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'เปิด Predictor - รางวัลรวม',th_desc='เพียงแค่ทำนายผลและชนะเหรียญ' WHERE `game_key` = 'allow_open_predictor';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'เล่นฟรี',th_desc='เล่นจินตนาการในชีวิตประจำวันสมบูรณ์ฟรีและลุ้นรับรางวัลที่น่าตื่นเต้น' WHERE `game_key` = 'allow_free2play';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'เปิด Predictor - ลีดเดอร์บอร์ด',th_desc='เพียงแค่ทำนายผลและลุ้นรับรางวัล' WHERE `game_key` = 'allow_fixed_open_predictor';";
		$this->db->query($sql);
		
		$sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET `th_title` = 'PROP FANTASY',th_desc='' WHERE `game_key` = 'allow_prop_fantasy';";
		$this->db->query($sql);
	
	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `th_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_meta_keyword`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `th_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_page_title`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `th_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_meta_desc`;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `th_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `tam_page_content`;";
		$this->db->query($sql);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(COMMON_CONTENT)." ADD `th_header` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `tam_header`;";
		$this->db->query($sql);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(COMMON_CONTENT)." ADD `th_body` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `tam_body`;";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(COMMON_CONTENT)." SET `th_header` = 'ยอดรวมทั้งหมด',th_body='ชนะ + โบนัสเงินสด + เงินฝาก' WHERE `content_key` = 'wallet';";
		$this->db->query($sql);
		
		$field = array(
			'th' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
				'after'=>'tam'
              ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$earn_coins =array (
            
            array (
              'module_key' => 'refer-a-friend',
              'th' => 
              json_encode(array (
                'label' => 'แนะนำเพื่อน',
                'description' => 'ได้รับ 100 เหรียญสำหรับเพื่อนทุกคน \'s ลงทะเบียน',
                'button_text' => 'อ้างถึง',
              )),
            ),
           
            array (
              'module_key' => 'daily_streak_bonus',
              'th' => 
              json_encode(array (
                'label' => 'โบนัส DAILY การเช็คอิน',
                'description' => 'ได้รับเหรียญทุกวันโดยการเข้าสู่ระบบ',
                'button_text' => 'เรียนรู้เพิ่มเติม',
              )) ,
            ),
            
            array (
              'module_key' => 'prediction',
              'th' => 
              json_encode(array (
                'label' => 'PLAY ทำนาย',
                'description' => 'ทำนายและได้รับเหรียญ',
                'button_text' => 'ทำนาย',
              )) ,
            ),
            
            array (
              'module_key' => 'promotions',
              'th' => 
              json_encode(array (
                'label' => 'โปรโมชั่น',
                'description' => 'วิ่งออกมาจากเหรียญ? ดูวิดีโอและเติมเงินกระเป๋าสตางค์เหรียญของคุณ',
                'button_text' => 'ดู',
              )),
            ),
           
            array (
              'module_key' => 'feedback',
              'th' => 
              json_encode( array (
                'label' => 'ผลตอบรับ',
                'description' => 'ข้อเสนอแนะของแท้จะได้รับเหรียญหลังจากได้รับอนุมัติผู้ดูแลระบบ',
                'button_text' => 'เขียนถึงเรา',
              )),
            ),
        );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(FAQ_QUESTIONS)." ADD `th_question` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL AFTER `tam_answer`;";
		  $this->db->query($sql);
		  
		$sql = "ALTER TABLE ".$this->db->dbprefix(FAQ_QUESTIONS)." ADD `th_answer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL AFTER `th_question`;";
		$this->db->query($sql);
		  
		  
		$sql = "ALTER TABLE ".$this->db->dbprefix(FAQ_CATEGORY)." ADD `th_category` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `tam_category`;";
		$this->db->query($sql);

		$categories = array (
			array(
			'category_alias' => 'registration',
			'th_category' =>"การลงทะเบียน",
		  ),array (
			'category_alias' => 'playing_the_game',
			'th_category' =>"กำลังเล่นเกม",
		  ),array (
			'category_alias' => 'scores_points',
			'th_category' =>"คะแนนและคะแนน",
		  ),array (
			'category_alias' => 'contests',
			'th_category' =>"การแข่งขัน",
		  ),array (
			'category_alias' => 'account_balance',
			'th_category' =>"ยอดเงินในบัญชี",
		  ),array (
			'category_alias' => 'verification',
			'th_category' =>"การยืนยัน",
		  ),array (
			'category_alias' => 'withdrawals',
			'th_category' =>"การถอน",
		  ),array (
			'category_alias' => 'legality',
			'th_category' =>"ถูกต้องตามกฎหมาย",
		  ),array (
			'category_alias' => 'fair_play_violation',
			'th_category' =>"การละเมิดการเล่นอย่างยุติธรรม",
		  ),array (
			'category_alias' => 'payments',
			'th_category' =>"การชำระเงิน",
		  ),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
			array(
			'page_alias' => 'about',
			'th_meta_keyword' =>"เกี่ยวกับเรา",
			'th_page_title' =>"เกี่ยวกับเรา",
			'th_meta_desc' =>"เกี่ยวกับเรา",
			'th_page_content' =>"เกี่ยวกับเรา",
			),array(
			'page_alias' => 'how_it_works',
			'th_meta_keyword' =>"มันทำงานอย่างไร",
			'th_page_title' =>"มันทำงานอย่างไร",
			'th_meta_desc' =>"มันทำงานอย่างไร",
			'th_page_content' =>"มันทำงานอย่างไร",
			),array(
			'page_alias' => 'terms_of_use',
			'th_meta_keyword' =>"ข้อตกลงในการใช้งาน",
			'th_page_title' =>"ข้อตกลงในการใช้งาน",
			'th_meta_desc' =>"ข้อตกลงในการใช้งาน",
			'th_page_content' =>"ข้อตกลงในการใช้งาน",
			),array(
			'page_alias' => 'privacy_policy',
			'th_meta_keyword' =>"นโยบายความเป็นส่วนตัว",
			'th_page_title' =>"นโยบายความเป็นส่วนตัว",
			'th_meta_desc' =>"นโยบายความเป็นส่วนตัว",
			'th_page_content' =>"นโยบายความเป็นส่วนตัว",
			),array(
			'page_alias' => 'faq',
			'th_meta_keyword' =>"คำถามที่พบบ่อย",
			'th_page_title' =>"คำถามที่พบบ่อย",
			'th_meta_desc' =>"คำถามที่พบบ่อย",
			'th_page_content' =>"คำถามที่พบบ่อย",
			),array(
			'page_alias' => 'support',
			'th_meta_keyword' =>"สนับสนุน",
			'th_page_title' =>"สนับสนุน",
			'th_meta_desc' =>"สนับสนุน",
			'th_page_content' =>"สนับสนุน",
			),array(
			'page_alias' => 'affiliations',
			'th_meta_keyword' =>"พันธมิตร",
			'th_page_title' =>"พันธมิตร",
			'th_meta_desc' =>"พันธมิตร",
			'th_page_content' =>"พันธมิตร",
			),array(
			'page_alias' => 'rules_and_scoring',
			'th_meta_keyword' =>"กฎและการให้คะแนน",
			'th_page_title' =>"กฎและการให้คะแนน",
			'th_meta_desc' =>"กฎและการให้คะแนน",
			'th_page_content' =>"กฎและการให้คะแนน",
			),array(
			'page_alias' => 'career',
			'th_meta_keyword' =>"อาชีพ",
			'th_page_title' =>"อาชีพ",
			'th_meta_desc' =>"อาชีพ",
			'th_page_content' =>"อาชีพ",
			),array(
			'page_alias' => 'press_media',
			'th_meta_keyword' =>"สื่อมวลชนและสื่อ",
			'th_page_title' =>"สื่อมวลชนและสื่อ",
			'th_meta_desc' =>"สื่อมวลชนและสื่อ",
			'th_page_content' =>"สื่อมวลชนและสื่อ",
			),array(
			'page_alias' => 'referral',
			'th_meta_keyword' =>"การอ้างอิง",
			'th_page_title' =>"การอ้างอิง",
			'th_meta_desc' =>"การอ้างอิง",
			'th_page_content' =>"การอ้างอิง",
			),array(
			'page_alias' => 'offers',
			'th_meta_keyword' =>"ข้อเสนอ",
			'th_page_title' =>"ข้อเสนอ",
			'th_meta_desc' =>"ข้อเสนอ",
			'th_page_content' =>"ข้อเสนอ",
			),array(
			'page_alias' => 'contact_us',
			'th_meta_keyword' =>"เกี่ยวกับเรา",
			'th_page_title' =>"เกี่ยวกับเรา",
			'th_meta_desc' =>"เกี่ยวกับเรา",
			'th_page_content' =>"เกี่ยวกับเรา",
			),array(
			'page_alias' => 'legality',
			'th_meta_keyword' =>"ถูกต้องตามกฎหมาย",
			'th_page_title' =>"ถูกต้องตามกฎหมาย",
			'th_meta_desc' =>"ถูกต้องตามกฎหมาย",
			'th_page_content' =>"ถูกต้องตามกฎหมาย",
			),array(
			'page_alias' => 'refund_policy',
			'th_meta_keyword' =>"นโยบายการคืนเงิน",
			'th_page_title' =>"นโยบายการคืนเงิน",
			'th_meta_desc' =>"นโยบายการคืนเงิน",
			'th_page_content' =>"นโยบายการคืนเงิน",
			),
		);
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'th_message');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'th_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'th_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'th_desc');
		
		$this->dbforge->drop_column(CMS_PAGES, 'th_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'th_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'th_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'th_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'th_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'th_body');
		$this->dbforge->drop_column(EARN_COINS, 'th');

		$this->dbforge->drop_column(FAQ_QUESTIONS, 'th_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'th_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'th_category');
	}

}