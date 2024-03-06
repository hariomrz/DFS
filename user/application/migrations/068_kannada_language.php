<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Kannada_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'kn_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'kn_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'kn_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'kn_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'kn_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `kn_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `kn_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `kn_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `kn_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'kn_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'kn_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'kn' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'kn_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'kn_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'kn_category'=>array(
				'type' => 'VARCHAR',
				'constraint' => 30,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
		$this->dbforge->add_column(FAQ_CATEGORY, $faq_category_fields);
		

		//updating columns now

		$sports_hub_arr = array(
            array (
                'kn_title' => 'TOURNAMENT MODE',
                'kn_desc' => 'ಲಾಂಗ್ ಪ್ರೊ ಪ್ಲೇಯರ್ ಸೀಸನ್? ಎಲ್ಲಾ asons ತುಗಳನ್ನು ಇಲ್ಲಿ ಪ್ಲೇ ಮಾಡಿ ',
                'game_key' => 'allow_tournament',
                ), array (
                'kn_title' => 'ದೈನಂದಿನ ಅದ್ಭುತ ಕ್ರೀಡೆಗಳು',
                'kn_desc' => 'ಸಾಂಪ್ರದಾಯಿಕ ಫ್ಯಾಂಟಸಿ ಕ್ರೀಡೆಗಳಿಗಿಂತ ದೈನಂದಿನ ಫ್ಯಾಂಟಸಿ ಕ್ರೀಡೆಗಳು ಹೆಚ್ಚು ಆಸಕ್ತಿಕರವಾಗಿವೆ',
                'game_key' => 'allow_dfs',
                ), array (
                'kn_title' => 'FORECAST & WIN COINS',
                'kn_desc' => 'ಯಾವುದೇ ಫ್ಯಾಂಟಸಿ ಕೌಶಲ್ಯಗಳ ಅಗತ್ಯವಿಲ್ಲ. ಫಲಿತಾಂಶವನ್ನು ict ಹಿಸಿ ಮತ್ತು ನಾಣ್ಯಗಳನ್ನು ಗೆದ್ದಿರಿ',
                'game_key' => 'allow_prediction',
                ), array (
                'kn_title' => 'em\' ಎಮ್ ಪ್ರಶಸ್ತಿ ಪೂಲ್ ಆಯ್ಕೆಮಾಡಿ',
                'kn_desc' => 'ಆಟವು ತುಂಬಾ ಸರಳವಾಗಿದೆ. ವಿಜೇತ ತಂಡವನ್ನು ಆರಿಸಿ ',
                'game_key' => 'allow_pickem',
                ), array (
                'kn_title' => 'ಮಲ್ಟಿ ಗೇಮ್',
                'kn_desc' => 'ಸಾಂಪ್ರದಾಯಿಕ ಫ್ಯಾಂಟಸಿ ಆಟಗಳಿಗಿಂತ ಮಲ್ಟಿ ಗೇಮ್ ಫ್ಯಾಂಟಸಿ ಆಟಗಳು ಹೆಚ್ಚು ಆಸಕ್ತಿಕರವಾಗಿವೆ',
                'game_key' => 'allow_multigame',
                ), array (
                'kn_title' => 'ಓಪನ್ ಫೋರ್ಕಾಸ್ಟ್',
                'kn_desc' => 'ಫಲಿತಾಂಶವನ್ನು ict ಹಿಸಿ ಮತ್ತು ನಾಣ್ಯಗಳನ್ನು ಗೆದ್ದಿರಿ',
                'game_key' => 'allow_open_predictor',
                ), array (
                'kn_title' => 'ಆಡಲು ಉಚಿತ',
                'kn_desc' => 'ಪ್ರತಿದಿನ ಫ್ಯಾಂಟಸಿ ಉಚಿತವಾಗಿ ಪ್ಲೇ ಮಾಡಿ ಮತ್ತು ಉತ್ತಮ ಬಹುಮಾನಗಳನ್ನು ಗೆದ್ದಿರಿ.',
                'game_key' => 'allow_free2play',
                ), array (
                'kn_title' => 'ಸ್ಥಿರ ಮುಕ್ತ ಮುನ್ಸೂಚಕ',
                'kn_desc' => 'ಫಲಿತಾಂಶವನ್ನು ict ಹಿಸಿ ಮತ್ತು ಬಹುಮಾನವನ್ನು ಗೆದ್ದಿರಿ',
                'game_key' => 'allow_fixed_open_predictor',
                ), array (
                'kn_title' => '',
                'kn_desc' => '',
                'game_key' => 'allow_prop_fantasy',
                ),
		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
            array (
                'kn_header' => 'ಒಟ್ಟು',
                'kn_body' => 'ವಿನ್ + ನಗದು ಬೋನಸ್ + ಠೇವಣಿ',
                'content_key' => 'wallet',
            ),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');
		  
		$earn_coins =array (
            
			array (
                'module_key' => 'refer-a-friend',
                'kn' =>
                json_encode (array (
                'label' => 'ಸ್ನೇಹಿತರನ್ನು ಆಹ್ವಾನಿಸಿ',
                 'description' => 'ಪ್ರತಿ ನೋಂದಾಯಿತ ಸ್ನೇಹಿತರಿಗೆ 100 ನಾಣ್ಯಗಳನ್ನು ಪಡೆಯಿರಿ',
                 'button_text' => 'ಇದನ್ನು ಸೂಚಿಸುತ್ತದೆ',
                )),
                ),
                
                array (
                'module_key' => 'daily_streak_bonus',
                'kn' =>
                json_encode (array (
                    'label' => 'ದೈನಂದಿನ ನೋಂದಣಿ ಬೋನಸ್',
                    'description' => 'ಲಾಗಿನ್ ಮಾಡುವ ಮೂಲಕ ಪ್ರತಿದಿನ ನಾಣ್ಯಗಳನ್ನು ಪಡೆಯಿರಿ',
                    'button_text' => 'ಇನ್ನಷ್ಟು ತಿಳಿಯಿರಿ',
                )),
                ),
                
                array (
                'module_key' => 'prediction',
                'kn' =>
                json_encode (array (
                    'label' => 'ಪ್ರಿಡಿಕ್ಷನ್ ಪ್ಲೇ',
                    'description' => 'ನಾಣ್ಯಗಳನ್ನು ict ಹಿಸಿ ಮತ್ತು ಸಂಪಾದಿಸಿ',
                    'button_text' => 'ಭವಿಷ್ಯ',
                )),
                ),
                
                array (
                'module_key' => 'promotions',
                'kn' =>
                json_encode (array (
                    'label' => 'ಪ್ರಚಾರ',
                    'description' => 'ನಾಣ್ಯಗಳಿಂದ ಹೊರಬಂದಿದೆಯೇ? ವೀಡಿಯೊ ನೋಡಿ ಮತ್ತು ನಿಮ್ಮ ನಾಣ್ಯ ಪರ್ಸ್ ಅನ್ನು ಮೇಲಕ್ಕೆತ್ತಿ ',
                    'button_text' => 'ವೀಕ್ಷಿಸಿ',
                )),
                ),
                
                array (
                'module_key' => 'feedback',
                'kn' =>
                json_encode (array (
                    'label' => 'ವಿಮರ್ಶೆ',
                    'description' => 'ನಿರ್ವಾಹಕರ ಅನುಮೋದನೆಯ ನಂತರ ಅಧಿಕೃತ ಪ್ರಸ್ತಾಪವನ್ನು ನೀಡಲಾಗುವುದು',
                    'button_text' => 'ನಮಗೆ ಇಮೇಲ್ ಮಾಡಿ',
                )),
                ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
            array (
                'category_alias' => 'registration',
                'kn_category' => 'ನೋಂದಣಿ',
                ), array (
                'category_alias' => 'playing_the_game',
                'kn_category' => '"ಆಟವನ್ನು ಆಡಲಾಗುತ್ತಿದೆ"',
                ), array (
                'category_alias' => 'scores_points',
                'kn_category' => 'ರೇಟಿಂಗ್‌ಗಳು ಮತ್ತು ರೇಟಿಂಗ್‌ಗಳು',
                ), array (
                'category_alias' => 'contests',
                'kn_category' => '"ಸ್ಪರ್ಧೆ"',
                ), array (
                'category_alias' => 'account_balance',
                'kn_category' => 'ಖಾತೆ ಬಾಕಿ',
                ), array (
                'category_alias' => 'verification',
                'kn_category' => 'ದೃ irm ೀಕರಿಸಿ',
                ), array (
                'category_alias' => 'withdrawals',
                'kn_category' => 'ಅಳಿಸು',
                ), array (
                'category_alias' => 'legality',
                'kn_category' => 'ಬಲ',
                ), array (
                'category_alias' => 'fair_play_violation',
                'kn_category' => 'ಫೇರ್ ಪ್ಲೇ ಉಲ್ಲಂಘನೆ',
                ), array (
                'category_alias' => 'payments',
                'kn_category' => '"ಪಾವತಿ"',
                ),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
            array (
            'page_alias' => 'about',
            'kn_meta_keyword' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_page_title' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_meta_desc' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_page_content' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            ), array (
            'page_alias' => 'how_it_works',
            'kn_meta_keyword' => 'ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
            'kn_page_title' => 'ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
            'kn_meta_desc' => 'ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
            'kn_page_content' => 'ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
            ), array (
            'page_alias' => 'terms_of_use',
            'kn_meta_keyword' => 'ಸೇವಾ ನಿಯಮಗಳು',
            'kn_page_title' => 'ಸೇವಾ ನಿಯಮಗಳು',
            'kn_meta_desc' => 'ಸೇವಾ ನಿಯಮಗಳು',
            'kn_page_content' => 'ಸೇವಾ ನಿಯಮಗಳು',
            ), array (
            'page_alias' => 'privacy_policy',
            'kn_meta_keyword' => 'ಗೌಪ್ಯತೆ ನೀತಿ',
            'kn_page_title' => 'ಗೌಪ್ಯತೆ ನೀತಿ',
            'kn_meta_desc' => 'ಗೌಪ್ಯತೆ ನೀತಿ',
            'kn_page_content' => 'ಗೌಪ್ಯತೆ ನೀತಿ',
            ), array (
            'page_alias' => 'faq',
            'kn_meta_keyword' => '"FAQ"',
            'kn_page_title' => 'ಪದೇ ಪದೇ ಕೇಳಲಾಗುವ ಪ್ರಶ್ನೆಗಳು',
            'kn_meta_desc' => 'ಪದೇ ಪದೇ ಕೇಳಲಾಗುವ ಪ್ರಶ್ನೆಗಳು',
            'kn_page_content' => 'ಪದೇ ಪದೇ ಕೇಳಲಾಗುವ ಪ್ರಶ್ನೆಗಳು',
            ), array (
            'page_alias' => 'support',
            'kn_meta_keyword' => 'ಬೆಂಬಲ',
            'kn_page_title' => 'ಬೆಂಬಲ',
            'kn_meta_desc' => 'ಬೆಂಬಲ',
            'kn_page_content' => 'ಬೆಂಬಲ',
            ), array (
            'page_alias' => 'affiliations',
            'kn_meta_keyword' => 'ಪಾಲುದಾರರು',
            'kn_page_title' => 'ಪಾಲುದಾರರು',
            'kn_meta_desc' => 'ಪಾಲುದಾರರು',
            'kn_page_content' => 'ಪಾಲುದಾರರು',
            ), array (
            'page_alias' => 'rules_and_scoring',
            'kn_meta_keyword' => 'ನಿಯಮಗಳು ಮತ್ತು ಮೌಲ್ಯಮಾಪನಗಳು',
            'kn_page_title' => 'ನಿಯಮಗಳು ಮತ್ತು ಮೌಲ್ಯಗಳು',
            'kn_meta_desc' => 'ನಿಯಮಗಳು ಮತ್ತು ರೇಟಿಂಗ್‌ಗಳು',
            'kn_page_content' => 'ನಿಯಮಗಳು ಮತ್ತು ಮೌಲ್ಯಮಾಪನ',
            ), array (
            'page_alias' => 'career',
            'kn_meta_keyword' => 'ವೃತ್ತಿ',
            'kn_page_title' => 'ಜಾಬ್',
            'kn_meta_desc' => 'ವೃತ್ತಿ',
            'kn_page_content' => 'ವೃತ್ತಿ',
            ), array (
            'page_alias' => 'press_media',
            'kn_meta_keyword' => 'ಒತ್ತಿ ಮತ್ತು ಮಾಧ್ಯಮ',
            'kn_page_title' => 'ಒತ್ತಿ ಮತ್ತು ಮಾಧ್ಯಮ',
            'kn_meta_desc' => 'ಪ್ರೆಸ್ ಮತ್ತು ಮೀಡಿಯಾ',
            'kn_page_content' => 'ಪ್ರೆಸ್ & ಮೀಡಿಯಾ',
            ), array (
            'page_alias' => 'referral',
            'kn_meta_keyword' => 'ಡೈರೆಕ್ಟರಿ',
            'kn_page_title' => 'ಉಲ್ಲೇಖ',
            'kn_meta_desc' => 'ಡೈರೆಕ್ಟರಿ',
            'kn_page_content' => 'ಲಿಂಕ್',
            ), array (
            'page_alias' => 'offers',
            'kn_meta_keyword' => 'ಸಲಹೆಗಳು',
            'kn_page_title' => 'ಆಫರ್',
            'kn_meta_desc' => 'ಆಫರ್',
            'kn_page_content' => 'ಆಫರ್',
            ), array (
            'page_alias' => 'contact_us',
            'kn_meta_keyword' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_page_title' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_meta_desc' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'kn_page_content' => 'ನಮ್ಮ ಬಗ್ಗೆ',
            ), array (
            'page_alias' => 'legality',
            'kn_meta_keyword' => 'ಕಾನೂನು',
            'kn_page_title' => 'ಕಾನೂನು',
            'kn_meta_desc' => 'ಕಾನೂನು',
            'kn_page_content' => 'ಕಾನೂನು',
            ), array (
            'page_alias' => 'refund_policy',
            'kn_meta_keyword' => 'ರಿಟರ್ನ್ ಪಾಲಿಸಿ',
            'kn_page_title' => 'ಮರುಪಾವತಿ ನೀತಿ',
            'kn_meta_desc' => 'ರಿಟರ್ನ್ ಪಾಲಿಸಿ',
            'kn_page_content' => 'ಮರುಪಾವತಿ ನೀತಿ',
            ),
        );
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');


        $transaction_msg_data = array (
            array(
                'source'=>1,
                'kn_message'=>' %s ಪ್ರವೇಶ ಶುಲ್ಕ', 
                ),
                array(
                'source'=>2,
                'kn_message'=>'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
                array(
                'source'=>3,
                'kn_message'=>'ಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>4,
                'kn_message'=>' %s ರಿಂದ ಸ್ನೇಹದ refferal', 
                ),
                array(
                'source'=>5,
                'kn_message'=>'ಬೋನಸ್ ಅವಧಿ', 
                ),
                array(
                'source'=>6,
                'kn_message'=>'ಪ್ರೋಮೊ ಕೋಡ್ ಮೂಲಕ', 
                ),
                array(
                'source'=>7,
                'kn_message'=>'ಸಂಗ್ರಹಗೊಳ್ಳುವವರೆಗೆ', 
                ),
                array(
                'source'=>8,
                'kn_message'=>'ಪ್ರಮಾಣ ಹಿಂತೆಗೆದುಕೊಳ್ಳುವ', 
                ),
                array(
                'source'=>9,
                'kn_message'=>'ಠೇವಣಿ ಮೇಲೆ ಸಾಲ ಬೋನಸ್', 
                ),
                array(
                'source'=>10,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಠೇವಣಿ', 
                ),
                array(
                'source'=>11,
                'kn_message'=>'ಒಟ್ಟು ಟಿಡಿಎಸ್ ಕಡಿತಗೊಳಿಸಲಾಗುತ್ತದೆ', 
                ),
                array(
                'source'=>12,
                'kn_message'=>'ಸೈನ್ ಅಪ್ ಬೋನಸ್', 
                ),
                array(
                'source'=>13,
                'kn_message'=>'ಮೊಬೈಲ್ ಪರಿಶೀಲನಾ ಫಾರ್ ರೆಫರಲ್ ಬೋನಸ್', 
                ),
                array(
                'source'=>14,
                'kn_message'=>'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ರೆಫರಲ್ ಬೋನಸ್', 
                ),
                array(
                'source'=>15,
                'kn_message'=>'ಸ್ಪರ್ಧೆ ಸೇರಲು ಎಂದರೆ ಬೋನಸ್', 
                ),
                array(
                'source'=>21,
                'kn_message'=>'ಅಂಗಡಿಯಿಂದ ವಿಮೋಚನೆಗೊಳ್ಳುತ್ತಾನೆ', 
                ),
                array(
                'source'=>30,
                'kn_message'=>'ಪ್ರೋಮೊ ಕೋಡ್ {cash_type} ಸ್ವೀಕರಿಸಲಾಗಿದೆ', 
                ),
                array(
                'source'=>31,
                'kn_message'=>'ಪ್ರೋಮೊ ಕೋಡ್ {cash_type} ಸ್ವೀಕರಿಸಲಾಗಿದೆ', 
                ),
                array(
                'source'=>32,
                'kn_message'=>'ಪ್ರೋಮೊ ಕೋಡ್ {cash_type} ಸ್ವೀಕರಿಸಲಾಗಿದೆ', 
                ),
                array(
                'source'=>37,
                'kn_message'=>'ವ್ಯವಹಾರದ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>40,
                'kn_message'=>'ಮುನ್ಸೂಚನಾ ಫಾರ್ ಬೆಟ್ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>41,
                'kn_message'=>'ಭವಿಷ್ಯ ಗೆದ್ದದ್ದು', 
                ),
                array(
                'source'=>50,
                'kn_message'=>'ಸೈನ್ ಅಪ್ ಬೋನಸ್ ನಗದು ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>51,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸೈನ್ ಅಪ್ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>52,
                'kn_message'=>'ನೀಡಲಾಯಿತು ರೆಫರಲ್ ನಾಣ್ಯಗಳು ಸ್ನೇಹದ ಮೂಲಕ ಸೈನ್ ಅಪ್', 
                ),
                array(
                'source'=>53,
                'kn_message'=>'ನೀಡಲಾಯಿತು ರೆಫರಲ್ ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಸೈನ್ ಅಪ್', 
                ),
                array(
                'source'=>54,
                'kn_message'=>'ನೀಡಲಾಯಿತು ರೆಫರಲ್ ನಿಜವಾದ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಸೈನ್ ಅಪ್', 
                ),
                array(
                'source'=>55,
                'kn_message'=>'ನೀಡಲಾಯಿತು ರೆಫರಲ್ ನಾಣ್ಯಗಳು ಸ್ನೇಹದ ಮೂಲಕ ಸೈನ್ ಅಪ್', 
                ),
                array(
                'source'=>56,
                'kn_message'=>'ಸೈನ್ ಅಪ್ ಬೋನಸ್ ನಗದು ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>57,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸೈನ್ ಅಪ್ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>58,
                'kn_message'=>'ನೀಡಲಾಯಿತು ರೆಫರಲ್ ನಾಣ್ಯಗಳು ಸ್ನೇಹದ ಮೂಲಕ ಸೈನ್ ಅಪ್', 
                ),
                array(
                'source'=>59,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>60,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>61,
                'kn_message'=>'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>62,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>63,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>64,
                'kn_message'=>'ಸ್ನೇಹದ ಮೂಲಕ ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>65,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>66,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>67,
                'kn_message'=>'ಪ್ಯಾನ್ ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>68,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>69,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>70,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>71,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>72,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>73,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>74,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>75,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>76,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>77,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>78,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>79,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>80,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>81,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>82,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>83,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>84,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>85,
                'kn_message'=>'ಸ್ನೇಹಿತ ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಲು', 
                ),
                array(
                'source'=>86,
                'kn_message'=>'ಇಮೇಲ್ ಪರಿಶೀಲನೆಗೆ ಬೋನಸ್ ನಗದು ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>87,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಇಮೇಲ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>88,
                'kn_message'=>'ಬೋನಸ್ ನಾಣ್ಯಗಳು ಇಮೇಲ್ ಪರಿಶೀಲನೆಗೆ ಪ್ರದಾನ', 
                ),
                array(
                'source'=>89,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಇಮೇಲ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>90,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಇಮೇಲ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>91,
                'kn_message'=>'ಸ್ನೇಹದ ಮೂಲಕ ಇಮೇಲ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಯಿತು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>92,
                'kn_message'=>'ಇಮೇಲ್ ಪರಿಶೀಲನೆಗೆ ಬೋನಸ್ ನಗದು ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>93,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಇಮೇಲ್ ಪರಿಶೀಲನೆ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>94,
                'kn_message'=>'ಬೋನಸ್ ನಾಣ್ಯಗಳು ಇಮೇಲ್ ಪರಿಶೀಲನೆಗೆ ಪ್ರದಾನ', 
                ),
                array(
                'source'=>95,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>96,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>97,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>98,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಠೇವಣಿ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>99,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸ್ನೇಹದ ಮೂಲಕ ಠೇವಣಿ ನೀಡಲಾಯಿತು', 
                ),
                array(
                'source'=>100,
                'kn_message'=>'ಸ್ನೇಹದ ಮೂಲಕ ಠೇವಣಿ ನೀಡಲಾಯಿತು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>102,
                'kn_message'=>'ಆರ್ಡರ್ ರದ್ದು (ಮರುಪಾವತಿ)', 
                ),
                array(
                'source'=>105,
                'kn_message'=>'ಬೋನಸ್ ನಗದು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>106,
                'kn_message'=>'ರಿಯಲ್ ನಗದು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>107,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಸ್ನೇಹಿತನ ಠೇವಣಿ ನೀಡಲಾಗುವುದು', 
                ),
                array(
                'source'=>132,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್', 
                ),
                array(
                'source'=>133,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ರಿಯಲ್ ಪ್ರಮಾಣದ', 
                ),
                array(
                'source'=>134,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>135,
                'kn_message'=>'ಡೀಲ್ ಬೋನಸ್', 
                ),
                array(
                'source'=>136,
                'kn_message'=>'ವ್ಯವಹಾರದ ನಿಜವಾದ', 
                ),
                array(
                'source'=>137,
                'kn_message'=>'ಡೀಲ್ ಫಾರ್ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>138,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್', 
                ),
                array(
                'source'=>139,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ರಿಯಲ್ ಪ್ರಮಾಣದ', 
                ),
                array(
                'source'=>140,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>141,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ಬೋನಸ್', 
                ),
                array(
                'source'=>142,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ರಿಯಲ್ ಪ್ರಮಾಣದ', 
                ),
                array(
                'source'=>143,
                'kn_message'=>'ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>144,
                'kn_message'=>'ಡೈಲಿ ಪರಂಪರೆಯನ್ನು ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>145,
                'kn_message'=>'ಬೋನಸ್ ನಾಣ್ಯಗಳು ಪಡೆದುಕೊಳ್ಳಲು ಪಡೆದರು', 
                ),
                array(
                'source'=>146,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಪಡೆದುಕೊಳ್ಳಲು ಪಡೆದರು ರಿಯಲ್ ಪ್ರಮಾಣದ', 
                ),
                array(
                'source'=>147,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಪಡೆದುಕೊಳ್ಳಲು ನಾಣ್ಯ ಕಡಿತಗೊಳಿಸದಿರುವುದರ', 
                ),
                array(
                'source'=>151,
                'kn_message'=>'ಪ್ರತಿಕ್ರಿಯೆಯನ್ನು ಸೇರಿಸಲಾಗಿದೆ ನಾಣ್ಯಗಳು ಅನುಮೋದನೆ', 
                ),
                array(
                'source'=>153,
                'kn_message'=>'ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಪ್ರತಿಫಲ ಸಂಪಾದನೆ - ಬೋನಸ್', 
                ),
                array(
                'source'=>154,
                'kn_message'=>'ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಪ್ರತಿಫಲ ಸಂಪಾದನೆ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>155,
                'kn_message'=>'ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಪ್ರತಿಫಲ ಸಂಪಾದನೆ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>156,
                'kn_message'=>'5 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ಬೋನಸ್', 
                ),
                array(
                'source'=>157,
                'kn_message'=>'5 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>158,
                'kn_message'=>'5 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>159,
                'kn_message'=>'10 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ಬೋನಸ್', 
                ),
                array(
                'source'=>160,
                'kn_message'=>'10 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>161,
                'kn_message'=>'10 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>162,
                'kn_message'=>'15 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ಬೋನಸ್', 
                ),
                array(
                'source'=>163,
                'kn_message'=>'15 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>164,
                'kn_message'=>'15 ನೇ ಸೈನ್ ಅಪ್ ಉಲ್ಲೇಖಿತ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>165,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ - ಬೋನಸ್', 
                ),
                array(
                'source'=>166,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>167,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>168,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ಬೋನಸ್', 
                ),
                array(
                'source'=>169,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>170,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>171,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ಬೋನಸ್', 
                ),
                array(
                'source'=>172,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ನಿಜವಾದ ನಗದು', 
                ),
                array(
                'source'=>173,
                'kn_message'=>'ದೂರವಾಣಿ ಪರಿಶೀಲನೆ ಉಲ್ಲೇಖಿತ - ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>174,
                'kn_message'=>'ಪ್ರಿಡಿಕ್ಷನ್ ರದ್ದು ಪ್ರವೇಶ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
                array(
                'source'=>181,
                'kn_message'=>'ಗೇಮ್ Pick\'em, CSI- {{home}} ವರ್ಸಸ್ {{away}} {{match_date}}', 
                ),
                array(
                'source'=>184,
                'kn_message'=>'ನಿರ್ವಹಣೆ ಹಿಂತೆಗೆದುಕೊಳ್ಳುವ', 
                ),
                array(
                'source'=>200,
                'kn_message'=>'  %s ಪ್ರವೇಶ ಶುಲ್ಕ', 
                ),
                array(
                'source'=>201,
                'kn_message'=>'ಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>202,
                'kn_message'=>'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
                array(
                'source'=>220,
                'kn_message'=>'ಮುನ್ಸೂಚನಾ ಫಾರ್ ಬೆಟ್ ನಾಣ್ಯಗಳು', 
                ),
                array(
                'source'=>221,
                'kn_message'=>'ಭವಿಷ್ಯ ಗೆದ್ದದ್ದು', 
                ),
                array(
                'source'=>224,
                'kn_message'=>'ಪ್ರಿಡಿಕ್ಷನ್ ರದ್ದು ಪ್ರವೇಶ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
                array(
                'source'=>225,
                'kn_message'=>'ಭವಿಷ್ಯ ಲೀಡರ್ ಗೆಲುವಿನ', 
                ),
                array(
                'source'=>226,
                'kn_message'=>'ಭವಿಷ್ಯ ಲೀಡರ್ ಗೆಲುವಿನ', 
                ),
                array(
                'source'=>227,
                'kn_message'=>'ಭವಿಷ್ಯ ಲೀಡರ್ ಗೆಲುವಿನ', 
                ),
                array(
                'source'=>250,
                'kn_message'=>'ಗೇಮ್ Pick\'em ಫಾರ್ ಬೆಟ್ {{home}} ವರ್ಸಸ್ {{away}} {{match_date}}', 
                ),
                array(
                'source'=>251,
                'kn_message'=>'ಗೇಮ್ Pick\'em ಫಾರ್ ಮರುಪಾವತಿಸಲಾಗಿದೆ {{home}} ವರ್ಸಸ್ {{away}} {{match_date}}', 
                ),
                array(
                'source'=>261,
                'kn_message'=>'ಡೈಲಿ ರೆಫರಲ್ ಲೀಡರ್ ಪ್ರಮಾಣ ವಿನ್ನಿಂಗ್!', 
                ),
                array(
                'source'=>262,
                'kn_message'=>'ಮಾಸಿಕ ರೆಫರಲ್ ಲೀಡರ್ ಪ್ರಮಾಣ ವಿನ್ನಿಂಗ್!', 
                ),
                array(
                'source'=>263,
                'kn_message'=>'ಸಾಪ್ತಾಹಿಕ ರೆಫರಲ್ ಲೀಡರ್ ಪ್ರಮಾಣ ವಿಜೇತ!', 
                ),
                array(
                'source'=>301,
                'kn_message'=>'  %s ಪ್ರವೇಶ ಶುಲ್ಕ', 
                ),
                array(
                'source'=>302,
                'kn_message'=>'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
                array(
                'source'=>303,
                'kn_message'=>'ಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>320,
                'kn_message'=>'ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಸೈನ್ ಅಪ್ ಆಯೋಗ', 
                ),
                array(
                'source'=>321,
                'kn_message'=>'ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಠೇವಣಿ ಆಯೋಗ', 
                ),
                array(
                'source'=>230,
                'kn_message'=>'ಮಿನಿ-ಲೀಗ್ ಗೆದ್ದಿದ್ದು', 
                ),
                array(
                'source'=>270,
                'kn_message'=>'ಒಂದು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸ್ನೇಹಿತರಿಗೆ ಸೇರಿಕೊಂಡರು', 
                ),
                array(
                'source'=>271,
                'kn_message'=>'ಒಂದು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸ್ನೇಹಿತರಿಗೆ ಸೇರಿಕೊಂಡರು', 
                ),
                array(
                'source'=>272,
                'kn_message'=>'ಒಂದು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸ್ನೇಹಿತರಿಗೆ ಸೇರಿಕೊಂಡರು', 
                ),
                array(
                'source'=>273,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಿ', 
                ),
                array(
                'source'=>274,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಿ', 
                ),
                array(
                'source'=>275,
                'kn_message'=>'ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರಿ', 
                ),
                array(
                'source'=>276,
                'kn_message'=>'ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ.', 
                ),
                array(
                'source'=>277,
                'kn_message'=>'ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ.', 
                ),
                array(
                'source'=>278,
                'kn_message'=>'ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ.', 
                ),
                array(
                'source'=>279,
                'kn_message'=>'ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ ಸೇರುವ.', 
                ),
                array(
                'source'=>280,
                'kn_message'=>'ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ ಸೇರುವ.', 
                ),
                array(
                'source'=>281,
                'kn_message'=>'ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಮೇಲೆ ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಲಾಭ ಸೇರುವ.', 
                ),
                array(
                'source'=>325,
                'kn_message'=>'ಅಪ್ಲಿಕೇಶನ್ ನಾಣ್ಯಗಳು ಖರೀದಿಯಲ್ಲಿ', 
                ),
                array(
                'source'=>240,
                'kn_message'=>'  %s ಪ್ರವೇಶ ಶುಲ್ಕ', 
                ),
                array(
                'source'=>241,
                'kn_message'=>'ಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ', 
                ),
                array(
                'source'=>242,
                'kn_message'=>'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ', 
                ),
        );
		
        $this->db->update_batch(TRANSACTION_MESSAGES,$transaction_msg_data,'source');

        $notification_message_data = array(
            array(
                'notification_type'=>0,
                'kn_message'=>'ನಿರ್ವಹಣೆ ದಾಖಲಿಸಿದವರು ಕಸ್ಟಮ್ ವರ್ಗದಲ್ಲಿ',
                ),
                array(
                'notification_type'=>1,
                'kn_message'=>'ಗೇಮ್ {{contest_name}} - {{collection_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಿದರು',
                ),
                array(
                'notification_type'=>2,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆ ಕಾರಣ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>3,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು {{collection_name}} ಪಂದ್ಯದಲ್ಲಿ ವಿಜೇತ ಆರ್.',
                ),
                array(
                'notification_type'=>4,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ನಮ್ಮ ಸೈಟ್ನಲ್ಲಿ ನಿಮ್ಮ ಸ್ನೇಹಿತರಿಗೆ {{name}} ಉಲ್ಲೇಖಿಸಿ ಆಫ್ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>6,
                'kn_message'=>'₹ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಇತ್ತೀಚಿನ ವ್ಯವಹಾರ ಸಂಗ್ರಹಗೊಳ್ಳುವವರೆಗೆ {{reason}}',
                ),
                array(
                'notification_type'=>7,
                'kn_message'=>'ನಿವರ್ತನ ₹ ಮೊದಲಿಗರಾಗಿದ್ದರು {{amount}}, ಪ್ರಮಾಣವನ್ನು ನಿಮ್ಮ ಸೈಟ್ ಬಾಕಿ ಡೆಬಿಟ್.',
                ),
                array(
                'notification_type'=>8,
                'kn_message'=>'ನಿಮ್ಮ ಸೇರಲು invitied ಗಳನ್ನು {{contest_name}}. ಸೇರಲು ಕ್ಲಿಕ್ ಮಾಡಿ.',
                ),
                array(
                'notification_type'=>9,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸೈಟ್ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>10,
                'kn_message'=>'ಆಟಗಾರನ ಗಾಯಗೊಂಡ',
                ),
                array(
                'notification_type'=>11,
                'kn_message'=>'ಕ್ಲಬ್ ಬದಲಾಯಿಸಿ',
                ),
                array(
                'notification_type'=>12,
                'kn_message'=>'ಹೊಂದಿಕೆ ಮುಂದೂಡಿದ್ದರಿಂದ',
                ),
                array(
                'notification_type'=>13,
                'kn_message'=>'ಆಟಗಾರನ ತಡೆಹಿಡಿಯಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>14,
                'kn_message'=>'ಸೈನ್ ಅಪ್',
                ),
                array(
                'notification_type'=>15,
                'kn_message'=>'ಪಾಸ್ವರ್ಡ್ ಮರೆತಿರಾ',
                ),
                array(
                'notification_type'=>16,
                'kn_message'=>'ನಿರ್ವಹಣೆ ಆಹ್ವಾನಿತ ಬಳಕೆದಾರ',
                ),
                array(
                'notification_type'=>17,
                'kn_message'=>'ನಿರ್ವಹಣೆ ಆಹ್ವಾನಿತ ವಿತರಕರು',
                ),
                array(
                'notification_type'=>18,
                'kn_message'=>'ಸ್ನೇಹದ refferal ಆಹ್ವಾನಿಸಿ',
                ),
                array(
                'notification_type'=>19,
                'kn_message'=>'{{message}}',
                ),
                array(
                'notification_type'=>20,
                'kn_message'=>'{{match_name}} ಪಂದ್ಯದಲ್ಲಿ ಮುಗಿದ! ಹೋದರು ಹೇಗೆ ನೋಡಿ',
                ),
                array(
                'notification_type'=>22,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ಪರ್ಧೆಯ (ಗಳು) ಪಂದ್ಯವೆಂದು ಪ್ರಭಾವಿತವಾಗುತ್ತದೆ {{match_name}} ಮಳೆಯಿಂದಾಗಿ ವಿಳಂಬವಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>23,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ಪರ್ಧೆಯ {{contest_name}} ಕಾರಣ ಪಂದ್ಯ (ಗಳು) ರದ್ದುಪಡಿಸಿತು ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ. ನಿಮ್ಮ ಪ್ರವೇಶ ಶುಲ್ಕ ನಿಮ್ಮ ಬಾಕಿ ಒಳಗೆ ವಾಪಸಾದ.',
                ),
                array(
                'notification_type'=>24,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ಪರ್ಧೆಯ (ಗಳು) ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ ಪಂದ್ಯದ ಕೈಬಿಟ್ಟ ಕಾರಣ {{match_name}}. ನಿಮ್ಮ ಪ್ರವೇಶ ಶುಲ್ಕ ನಿಮ್ಮ ಬಾಕಿ ಒಳಗೆ ವಾಪಸಾದ.',
                ),
                array(
                'notification_type'=>25,
                'kn_message'=>'₹ ನಿಮ್ಮ ಹಿಂದಕ್ಕೆ ಮನವಿಯನ್ನು {{amount}} ಅನುಮೋದಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>26,
                'kn_message'=>'₹ ನಿಮ್ಮ ಹಿಂದಕ್ಕೆ ಮನವಿಯನ್ನು {{amount}} ತಿರಸ್ಕರಿಸಲಾಗಿದೆ. ({{reason}})',
                ),
                array(
                'notification_type'=>27,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಬಹುಮಾನ ಮಾಡಲಾಗಿದೆ {{amount}} ನಾಣ್ಯ (ಗಳು) {{reason}}',
                ),
                array(
                'notification_type'=>28,
                'kn_message'=>'₹ {{amount}} {{reason}} ನಿಮ್ಮ ನಾಣ್ಯ ಸಮತೋಲನ ಡೆಬಿಟ್',
                ),
                array(
                'notification_type'=>29,
                'kn_message'=>'ಲೀಗ್ {{contest_name}} ಮುಂದಿನ ಋತುವಿನಲ್ಲಿ ಸರಿಸಲಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>30,
                'kn_message'=>'{{name}} ನೀವು ಸ್ಪರ್ಧೆಗಳು ಸೇರಲು ಆಹ್ವಾನಿಸಿದ್ದಾರೆ {{collection_name}}',
                ),
                array(
                'notification_type'=>31,
                'kn_message'=>'{{name}} ನೀವು ಸೇರಲು ಆಹ್ವಾನಿಸಿದ್ದಾರೆ {{contest_name}}.',
                ),
                array(
                'notification_type'=>33,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ಸೈನ್ ಅಪ್ ಬೋನಸ್ {{amount}} ಯಶಸ್ವಿಯಾಗಿ ನಿಮ್ಮ ಖಾತೆಗೆ ಠೇವಣಿ!',
                ),
                array(
                'notification_type'=>34,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{name}} ನೀವು ಮೂಲಕ ಉಲ್ಲೇಖಿಸಲಾಗುತ್ತದೆ ಸೈಟ್ನಲ್ಲಿ ಫೋನ್ ಸಂಖ್ಯೆ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ಬೋನಸ್ ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>35,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{name}} ನೀವು ಮೂಲಕ ಉಲ್ಲೇಖಿಸಲಾಗುತ್ತದೆ ಸೈಟ್ನಲ್ಲಿ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ಬೋನಸ್ ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>36,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ಬಳಕೆದಾರರಿಂದ ಸೇರಿದ್ದಾರೆ ಮೂಲಕ ಸ್ಪರ್ಧೆ refered. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>37,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನಿಮ್ಮಿಂದ refered ಕಲೆಕ್ಷನ್, ಬಳಕೆದಾರ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>38,
                'kn_message'=>'ನಿರ್ವಹಣೆ ರಿಂದ ಸೈನ್ ಅಪ್ ಆಮಂತ್ರಣವನ್ನು',
                ),
                array(
                'notification_type'=>39,
                'kn_message'=>'ನೀವು ಒಂದು ಉಚಿತ ಪ್ರವೇಶ ನೀಡಲಾಗಿದೆ {{contest_name}}.',
                ),
                array(
                'notification_type'=>42,
                'kn_message'=>'ನಿಮ್ಮ ಪಾವತಿ ವಿಫಲವಾಗಿದೆ ದೊರೆತಿದೆ.',
                ),
                array(
                'notification_type'=>43,
                'kn_message'=>'ನಾವು ನಮ್ಮ ವೆಬ್ಸೈಟ್ನಲ್ಲಿ ಬಿಡುಗಡೆ ಹೊಸ ವೈಶಿಷ್ಟ್ಯವನ್ನು ಘೋಷಿಸಲು ಸಂತೋಷದಿಂದ!',
                ),
                array(
                'notification_type'=>44,
                'kn_message'=>'ನಿಮ್ಮ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ. ಕಾರಣ: {{pan_rejected_reason}}',
                ),
                array(
                'notification_type'=>45,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ಈಗಾಗಲೇ ತುಂಬಿದೆ',
                ),
                array(
                'notification_type'=>50,
                'kn_message'=>'ಬೋನಸ್ ನಗದು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್ ಸೈನ್ ಅಪ್.',
                ),
                array(
                'notification_type'=>51,
                'kn_message'=>'ನಿಜವಾದ ನಗದು ಸೈನ್ ಅಪ್ ₹ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
                ),
                array(
                'notification_type'=>52,
                'kn_message'=>'ನಾಣ್ಯಗಳು ಸೈನ್ ಅಪ್ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
                ),
                array(
                'notification_type'=>53,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸೈಟ್ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>54,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನಿಮ್ಮಿಂದ refered ಸೈಟ್ ಸೇರಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>55,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸೈಟ್ ಸೇರಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>56,
                'kn_message'=>'ಭಲೆ! ನೀವು ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
                ),
                array(
                'notification_type'=>57,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>58,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>59,
                'kn_message'=>'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ಬೋನಸ್ ನಗದು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
                ),
                array(
                'notification_type'=>60,
                'kn_message'=>'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಿಜವಾದ ನಗದು ₹ {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
                ),
                array(
                'notification_type'=>61,
                'kn_message'=>'ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು {{amount}} ನಿಮ್ಮ ಖಾತೆಗೆ ಕ್ರೆಡಿಟ್.',
                ),
                array(
                'notification_type'=>62,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>63,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>64,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲಿಸಲಾಗಿದೆ ಮಾಡಿದೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>65,
                'kn_message'=>'ಭಲೆ! ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
                ),
                array(
                'notification_type'=>66,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>67,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ಪ್ಯಾನ್‌ಕಾರ್ಡ್ ಪರಿಶೀಲನೆಯ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>68,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>70,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>71,
                'kn_message'=>'ಭಲೆ! ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
                ),
                array(
                'notification_type'=>72,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>73,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>74,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>75,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>76,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>77,
                'kn_message'=>'ಭಲೆ! ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
                ),
                array(
                'notification_type'=>78,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>79,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>80,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>81,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>82,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>83,
                'kn_message'=>'ಭಲೆ! ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು.',
                ),
                array(
                'notification_type'=>84,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>85,
                'kn_message'=>'ಭಲೆ! ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಬಳಸಿಕೊಂಡು ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಹೆಚ್ಚುವರಿ {{amount}} ನಾಣ್ಯಗಳು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>86,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>87,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>88,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>89,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಇಮೇಲ್ ಅನ್ನು ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>90,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಇಮೇಲ್ ಅನ್ನು ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>91,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಇಮೇಲ್ ಅನ್ನು ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>92,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>93,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>94,
                'kn_message'=>'ನಿಮ್ಮ ಇಮೇಲ್ ಐಡಿ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>95,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>96,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>97,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>98,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಠೇವಣಿ ಮಾಡಿದೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>99,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಠೇವಣಿ ಮಾಡಿದೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>100,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಠೇವಣಿ ಮಾಡಿದೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>101,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನಿಮ್ಮ Order- {{product_name}} (ಸಂ: {{product_order_unique_id}}) ಇರಿಸಲಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>102,
                'kn_message'=>'ನಿಮ್ಮ Order- {{product_name}} (ಸಂ: {{product_order_unique_id}}) ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>103,
                'kn_message'=>'ಅಭಿನಂದನೆ! ನಿಮ್ಮ Order- {{product_name}} (ಸಂ: {{product_order_unique_id}}) ಪೂರ್ಣಗೊಂಡಿದೆ.',
                ),
                array(
                'notification_type'=>105,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>106,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>107,
                'kn_message'=>'ನಿಮ್ಮ ಮೊದಲ ಠೇವಣಿ ಅಭಿನಂದನೆಗಳು! ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>120,
                'kn_message'=>'ಠೇವಣಿಗಾಗಿ Promocode',
                ),
                array(
                'notification_type'=>121,
                'kn_message'=>'ಸ್ಪರ್ಧೆಯಲ್ಲಿ Promocode',
                ),
                array(
                'notification_type'=>122,
                'kn_message'=>'ಫಿಕ್ಸ್ಚರ್ ಪ್ರಚಾರ',
                ),
                array(
                'notification_type'=>123,
                'kn_message'=>'ಒಂದು ಫ್ರೆಂಡ್ ನೋಡಿ',
                ),
                array(
                'notification_type'=>124,
                'kn_message'=>'ಮೊದಲ ಠೇವಣಿ ಫಾರ್ Promocode',
                ),
                array(
                'notification_type'=>125,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ನಿರ್ವಾಹಕರಿಂದ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ. ಕಾರಣ ನಿಮ್ಮ ಇಮೇಲ್ ಕಳುಹಿಸಲಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>130,
                'kn_message'=>'₹ {{amount}} ಟಿಡಿಎಸ್ ಎಂದು ಕಳೆಯಲಾಗುತ್ತದೆ',
                ),
                array(
                'notification_type'=>134,
                'kn_message'=>'Custom-msg',
                ),
                array(
                'notification_type'=>135,
                'kn_message'=>'Custom-notification',
                ),
                array(
                'notification_type'=>136,
                'kn_message'=>'ನಿಮ್ಮ ಬ್ಯಾಂಕ್ ವಿವರಗಳನ್ನು ನಿರ್ವಾಹಕರಿಂದ ತಿರಸ್ಕರಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>137,
                'kn_message'=>'ನಿಮ್ಮ ಖಾತೆ ನಿರ್ವಾಹಕರಿಂದ ನಿರ್ಬಂಧಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>138,
                'kn_message'=>'ನೀವು ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ {{amount}} ದೈನಂದಿನ ಚೆಕ್ ಇನ್ ನಾಣ್ಯಗಳು ಡೇ {{day_number}}',
                ),
                array(
                'notification_type'=>139,
                'kn_message'=>'ನೀವು ನಾಣ್ಯಗಳನ್ನು ಪಡೆದುಕೊಳ್ಳಲು ಫಾರ್ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>140,
                'kn_message'=>'ನೀವು ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ {{amount}} ರಿಯಲ್ {{event}}',
                ),
                array(
                'notification_type'=>141,
                'kn_message'=>'{{amount}} ಕಡಿತಗೊಳಿಸುವುದು ನಾಣ್ಯಗಳು {{event}}',
                ),
                array(
                'notification_type'=>142,
                'kn_message'=>'ನೀವು ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>143,
                'kn_message'=>'ನೀವು ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆಗಾಗಿ ಸ್ವೀಕರಿಸಿದ್ದೇವೆ {{amount}} ನಿಜವಾದ ನಗದು',
                ),
                array(
                'notification_type'=>144,
                'kn_message'=>'ನೀವು ಸ್ವೀಕರಿಸಿದ {{amount}} ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>145,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ನೇಹಿತ ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>146,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ನೇಹಿತ ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>147,
                'kn_message'=>'yYou ನಿಮ್ಮ ಸ್ನೇಹಿತ ಪಡೆದ {{amount}} ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>148,
                'kn_message'=>'ನೀವು ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>149,
                'kn_message'=>'ನೀವು ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೇವೆ',
                ),
                array(
                'notification_type'=>150,
                'kn_message'=>'ನೀವು ಸ್ವೀಕರಿಸಿದ {{amount}} ಬ್ಯಾಂಕ್ ಪರಿಶೀಲನೆ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>153,
                'kn_message'=>'ನಿಮ್ಮ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಸಂಪಾದನೆ {{amount}} ಬೋನಸ್ ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>154,
                'kn_message'=>'ನಿಮ್ಮ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಸಂಪಾದನೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>155,
                'kn_message'=>'ನಿಮ್ಮ ಉಲ್ಲೇಖಿತ ಕೋಡ್ ಸಂಪಾದನೆ {{amount}} ನಾಣ್ಯಗಳು ಸ್ವೀಕರಿಸಿಲ್ಲ ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>156,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 5 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ಬೋನಸ್ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>157,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 5 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು ₹ {{amount}} ನಿಜವಾದ ನಗದು ಮೈಲಿಗಲ್ಲು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>158,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 5 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ನಾಣ್ಯಗಳ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>159,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 10 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ಬೋನಸ್ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>160,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 10 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು ₹ {{amount}} ನಿಜವಾದ ನಗದು ಮೈಲಿಗಲ್ಲು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>161,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 10 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ನಾಣ್ಯಗಳ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>162,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 15 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ಬೋನಸ್ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>163,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 15 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು ₹ {{amount}} ನಿಜವಾದ ನಗದು ಮೈಲಿಗಲ್ಲು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>164,
                'kn_message'=>'ಚೆನ್ನಾಗಿದೆ! ನೀವು 15 ನೇ ಯಶಸ್ವಿ ಉಲ್ಲೇಖಿತ & ಪಡೆದರು {{amount}} ನಾಣ್ಯಗಳ ಮೈಲಿಗಲ್ಲನ್ನು ಸಾಧಿಸಿದ',
                ),
                array(
                'notification_type'=>165,
                'kn_message'=>'ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ',
                ),
                array(
                'notification_type'=>166,
                'kn_message'=>'ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ತಂದುಕೊಟ್ಟಿವೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು',
                ),
                array(
                'notification_type'=>167,
                'kn_message'=>'ನೀವು ತಂದುಕೊಟ್ಟಿವೆ {{amount}} ನಾಣ್ಯಗಳು ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯನ್ನು ಪರಿಶೀಲಿಸುವಲ್ಲಿ ನಗದು',
                ),
                array(
                'notification_type'=>168,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಫೋನ್ ಸಂಖ್ಯೆ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ಬೋನಸ್ ನಗದು',
                ),
                array(
                'notification_type'=>169,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಫೋನ್ ಸಂಖ್ಯೆ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ',
                ),
                array(
                'notification_type'=>170,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಅವನ / ಅವಳ ಫೋನ್ ಸಂಖ್ಯೆ ಪರಿಶೀಲಿಸಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>171,
                'kn_message'=>'ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ',
                ),
                array(
                'notification_type'=>172,
                'kn_message'=>'ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯ ಪರಿಶೀಲಿಸುವ ಮೂಲಕ ತಂದುಕೊಟ್ಟಿವೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು',
                ),
                array(
                'notification_type'=>173,
                'kn_message'=>'ನೀವು ತಂದುಕೊಟ್ಟಿವೆ {{amount}} ನಾಣ್ಯಗಳು ನಿಮ್ಮ ಫೋನ್ ಸಂಖ್ಯೆಯನ್ನು ಪರಿಶೀಲಿಸುವಲ್ಲಿ ನಗದು',
                ),
                array(
                'notification_type'=>174,
                'kn_message'=>'ನೀವು ನಿರ್ವಹಣೆ ಮೂಲಕ ಭವಿಷ್ಯ ರದ್ದು ಪಡೆದರು {{amount}} ಮರುಪಾವತಿ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>175,
                'kn_message'=>'{{question}} ಈಗ ನಿರೀಕ್ಷಿಸಿ!',
                ),
                array(
                'notification_type'=>176,
                'kn_message'=>'ಹೇ! ನಿಮ್ಮ ಮಕ್ಕಳನ್ನು ಬಳಸಿ ಮತ್ತು {{match}} ಹೊಂದಾಣಿಕೆಯಲ್ಲಿ ಮುನ್ನಂದಾಜು ಮುನ್ನೋಟಗಳನ್ನು ಇದೀಗ ಲೈವ್ ಆಗಿದೆ!',
                ),
                array(
                'notification_type'=>181,
                'kn_message'=>'ನಿಮ್ಮ ಪಿಕ್ {{correct_answer}} ಆಟದ ಸರಿಯಾಗಿದೆಯೇ {{home}} ವರ್ಸಸ್ {{away}} {{date}}.',
                ),
                array(
                'notification_type'=>183,
                'kn_message'=>'{{home}} ವಿರುದ್ಧ {{away}} ಪಂದ್ಯಕ್ಕೆ ಸರಿ ಉತ್ತರ ಮುನ್ಸೂಚನೆ ಅಭಿನಂದನೆಗಳು. ಫಲಿತಾಂಶಗಳನ್ನು ಪರಿಶೀಲಿಸಿ.',
                ),
                array(
                'notification_type'=>184,
                'kn_message'=>'ನಿರ್ವಹಣೆ ಹಿಂತೆಗೆದುಕೊಳ್ಳುವ {{amount}} ನಿಮ್ಮ ಖಾತೆಯಿಂದ',
                ),
                array(
                'notification_type'=>200,
                'kn_message'=>'ನೀವು ಗೇಮ್ ಸೇರಿದ್ದೀರಿ {{contest_name}} ನ {{tournament_name}} ಯಶಸ್ವಿಯಾಗಿ!',
                ),
                array(
                'notification_type'=>201,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು {{tournament_name}} ಪಂದ್ಯದಲ್ಲಿ ವಿಜೇತ ಆರ್.',
                ),
                array(
                'notification_type'=>202,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆ ಕಾರಣ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>203,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ನಿರ್ವಾಹಕರಿಂದ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ. ಕಾರಣ ನಿಮ್ಮ ಇಮೇಲ್ ಕಳುಹಿಸಲಾಗಿದೆ.',
                ),
                array(
                'notification_type'=>220,
                'kn_message'=>'ನೀವು ನಿರ್ವಹಣೆ ಮೂಲಕ ಭವಿಷ್ಯ ರದ್ದು ಪಡೆದರು {{amount}} ಮರುಪಾವತಿ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>221,
                'kn_message'=>'{{question}} ಈಗ ನಿರೀಕ್ಷಿಸಿ!',
                ),
                array(
                'notification_type'=>222,
                'kn_message'=>'ಹೇ! ನಿಮ್ಮ ಮಕ್ಕಳನ್ನು ಬಳಸಿ ಮತ್ತು ಊಹಿಸಲು {{category}}, ಮುನ್ನೋಟಗಳನ್ನು ಇದೀಗ ನೇರ!',
                ),
                array(
                'notification_type'=>223,
                'kn_message'=>'ಸರಿಯಾದ ಉತ್ತರ ಮುನ್ಸೂಚನೆ ಅಭಿನಂದನೆಗಳು {{category}}. ಫಲಿತಾಂಶಗಳನ್ನು ಪರಿಶೀಲಿಸಿ.',
                ),
                array(
                'notification_type'=>225,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} ಡೈಲಿ ಲೀಡರ್ ಆಫ್ {{start_date}} {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ.',
                ),
                array(
                'notification_type'=>226,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ ವೀಕ್ {{start_date}} ವೀಕ್ಲಿ ಲೀಡರ್ ಗೆ {{ಕೊನೆ_ದಿನಾಂಕ}} ರಂದು.',
                ),
                array(
                'notification_type'=>227,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} ಮಾಸಿಕ ಲೀಡರ್ {{start_date}} ತಿಂಗಳ {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ.',
                ),
                array(
                'notification_type'=>240,
                'kn_message'=>'ಹೇ! ನಿಮ್ಮ ಮಕ್ಕಳನ್ನು ಬಳಸಿ ಮತ್ತು ಊಹಿಸಲು {{category}}, ಮುನ್ನೋಟಗಳನ್ನು ಇದೀಗ ನೇರ!',
                ),
                array(
                'notification_type'=>241,
                'kn_message'=>'ಸರಿಯಾದ ಉತ್ತರ ಮುನ್ಸೂಚನೆ ಅಭಿನಂದನೆಗಳು {{category}}. ಫಲಿತಾಂಶಗಳನ್ನು ಪರಿಶೀಲಿಸಿ.',
                ),
                array(
                'notification_type'=>250,
                'kn_message'=>'ಗೇಮ್ {{contest_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಲು',
                ),
                array(
                'notification_type'=>251,
                'kn_message'=>'ನೀವು ನಿರ್ವಹಣೆ ಮೂಲಕ pick\'em ರದ್ದು ಪಡೆದರು {{amount}} ಮರುಪಾವತಿ ನಾಣ್ಯಗಳು ಮಾಡಿಲ್ಲ!',
                ),
                array(
                'notification_type'=>252,
                'kn_message'=>'ಅಯ್ಯೋ! ನೀವು ಆಟದ ಆಯ್ಕೆಯಾಯಿತು {{user_selected_option}} ತಪ್ಪು {{home}} ವರ್ಸಸ್ {{away}} {{match_date}}. ಚಿಂತಿಸಬೇಡಿ, ಇತರ ಆಟಗಳು ಸಾಕಷ್ಟು ನೀವು ಆಡಲು ಕೀಪಿಂಗ್ ಆಡುವುದಕ್ಕಿಂತ!',
                ),
                array(
                'notification_type'=>261,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} ರೆಫರಲ್ ಡೈಲಿ ಲೀಡರ್ ಆಫ್ {{start_date}} {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ.',
                ),
                array(
                'notification_type'=>262,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} ರೆಫರಲ್ ಮಾಸಿಕ ಲೀಡರ್ ಆಫ್ {{start_date}} {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ.',
                ),
                array(
                'notification_type'=>263,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಗೆದ್ದಿದ್ದಾರೆ {{amount}} ರೆಫರಲ್ ಮಾಸಿಕ ಲೀಡರ್ ಆಫ್ {{start_date}} {{rank_value}} ರಾಂಕ್ ಅನ್ನು ಸಾಧಿಸುವ ಮೂಲಕ.',
                ),
                array(
                'notification_type'=>300,
                'kn_message'=>'ಈ ಕ್ಯಾಚ್ ಡ್ರಾಪ್ ಮಾಡಬೇಡಿ, ಇಂದು ಒಂದು ದೊಡ್ಡ ಪಂದ್ಯದಲ್ಲಿ ಇಲ್ಲಿದೆ. ಪ್ಲೇ {{home}} ವಿರುದ್ಧ {{away}} ಈಗ ಮತ್ತು ದೊಡ್ಡ ಗೆಲ್ಲಲು. ಹೋಗಿ {{FRONTEND_BITLY_URL}}',
                ),
                array(
                'notification_type'=>301,
                'kn_message'=>'ನೀವು ನೊಂದಾಯಿತ',
                ),
                array(
                'notification_type'=>331,
                'kn_message'=>'Wohoo! ನಾಣ್ಯ {{coins}} ನಿಮ್ಮ ನಾಣ್ಯ ಖರೀದಿ ಮೇಲೆ ನಿಮ್ಮ ನಾಣ್ಯ ಸಮತೋಲನ ಸಲ್ಲುತ್ತದೆ.',
                ),
                array(
                'notification_type'=>332,
                'kn_message'=>'ವಾಲೆಟ್ ನಾಣ್ಯಗಳನ್ನು ಖರೀದಿ ಮೇಲೆ ಡೆಬಿಟ್ {{amount}} ಫಾರ್ {{coins}} ನಾಣ್ಯಗಳು ಇದೆ.',
                ),
                array(
                'notification_type'=>401,
                'kn_message'=>'ಗೇಮ್ {{contest_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಲು',
                ),
                array(
                'notification_type'=>402,
                'kn_message'=>'ಸ್ಪರ್ಧೆ {{contest_name}} ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆ ಕಾರಣ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ',
                ),
                array(
                'notification_type'=>403,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು {{collection_name}} ಪಂದ್ಯದಲ್ಲಿ ವಿಜೇತ ಆರ್.',
                ),
                array(
                'notification_type'=>410,
                'kn_message'=>'ನಿಮ್ಮ ಸ್ಪರ್ಧೆಯ {{contest_name}} ಕಾರಣ ಪಂದ್ಯ (ಗಳು) ರದ್ದುಪಡಿಸಿತು ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ. ನಿಮ್ಮ ಪ್ರವೇಶ ಶುಲ್ಕ ನಿಮ್ಮ ಬಾಕಿ ಒಳಗೆ ವಾಪಸಾದ.',
                ),
                array(
                'notification_type'=>411,
                'kn_message'=>'Wohoo! ನೀವು {{amount}} ಸ್ಪಿನ್ ನಾಣ್ಯಗಳು ಚಕ್ರದ ಸಾಧಿಸಿದೆ',
                ),
                array(
                'notification_type'=>412,
                'kn_message'=>'Wohoo! ನೀವು ಸ್ಪಿನ್ ಚಕ್ರದ ರೊಕ್ಕ ಸಾಧಿಸಿದೆ {{amount}}',
                ),
                array(
                'notification_type'=>413,
                'kn_message'=>'Wohoo! ನೀವು ಸ್ಪಿನ್ ಚಕ್ರ {{amount}} ಬೋನಸ್ ಸಾಧಿಸಿದೆ',
                ),
                array(
                'notification_type'=>414,
                'kn_message'=>'Wohoo! ನೀವು ಸಾಧಿಸಿದೆ {{name}} ಸ್ಪಿನ್ ಚಕ್ರದ',
                ),
                array(
                'notification_type'=>420,
                'kn_message'=>'ನಿಮ್ಮ ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಸೈನ್ ಅಪ್ ಮತ್ತು ನೀವು ಸಿಕ್ಕಿತು {{amount}}',
                ),
                array(
                'notification_type'=>421,
                'kn_message'=>'ನಿಮ್ಮ ಅಂಗ ಪ್ರೋಗ್ರಾಂ ಮೂಲಕ ಬಳಕೆದಾರ ಠೇವಣಿ ಮತ್ತು ನೀವು ಸಿಕ್ಕಿತು {{amount}}',
                ),
                array(
                'notification_type'=>69,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>132,
                'kn_message'=>'ಟಾಸ್ {{collection_name}} ಪಂದ್ಯಕ್ಕೆ ನಡೆಯಿತು, ಮತ್ತು ತಂಡಗಳು ಘೋಷಿಸಲಾಗುತ್ತದೆ. ಪಂದ್ಯ ಆರಂಭವಾಗಿ ತನಕ ನೀವು ನಿಮ್ಮ ತಂಡದ ಸಂಪಾದಿಸಬಹುದು {{FRONTEND_BITLY_URL}}. ಆಟ ಶುರು!',
                ),
                array(
                'notification_type'=>151,
                'kn_message'=>'ನೀವು ನಿರ್ವಾಹಕ ಪ್ರತಿಕ್ರಿಯೆ ಅನುಮೋದನೆಗೆ {{amount}} ನಾಣ್ಯಗಳು ಸ್ವೀಕರಿಸಿಲ್ಲ ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>253,
                'kn_message'=>'ಪಂದ್ಯ (ಗಳ) ರದ್ದತಿಯಿಂದಾಗಿ ನಿಮ್ಮ ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಂಡಿದೆ. ನಿಮ್ಮ ಪ್ರವೇಶ ಶುಲ್ಕವನ್ನು ನಿಮ್ಮ ಬಾಕಿ ಮೊತ್ತಕ್ಕೆ ಹಿಂತಿರುಗಿಸಲಾಗಿದೆ.',
                ),array(
                'notification_type'=>254,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{collection_name}} {{contest_name}} ಪಂದ್ಯದ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ನೀವು ವಿಜೇತರಾಗಿದ್ದೀರಿ.',
                ),array(
                'notification_type'=>255,
                'kn_message'=>'ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆಯಿಂದಾಗಿ ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಂಡಿದೆ',
                ),
                array(
                'notification_type'=>224,
                'kn_message'=>'ನೀವು ನಿರ್ವಹಣೆ ಮೂಲಕ ಭವಿಷ್ಯ ರದ್ದು ಪಡೆದರು {{amount}} ಮರುಪಾವತಿ ನಾಣ್ಯಗಳು ಹೊಂದಿರುವ',
                ),
                array(
                'notification_type'=>230,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು {{mini_league_name}} ಮಿನಿ ಲೀಗ್ನಲ್ಲಿ ವಿಜೇತ ಆರ್',
                ),
                array(
                'notification_type'=>231,
                'kn_message'=>'ಮಿನಿ-ಲೀಗ್ {{mini_league_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಲು',
                ),
                array(
                'notification_type'=>270,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ₹ {{amount}} ನಿಜವಾದ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>271,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು {{amount}} ಬೋನಸ್ ನಗದು ತಂದುಕೊಟ್ಟಿವೆ.',
                ),
                array(
                'notification_type'=>272,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! {{friend_name}} ನೀವು ಉಲ್ಲೇಖಿಸಲಾಗಿದೆ ಸ್ಪರ್ಧೆ ಸೇರಿದ್ದಾರೆ. ನೀವು ಗಳಿಸಿದ {{amount}} ನಾಣ್ಯಗಳು ಮಾಡಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>273,
                'kn_message'=>'ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೇವೆ.',
                ),
                array(
                'notification_type'=>274,
                'kn_message'=>'ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ {{amount}} ಬೋನಸ್ ನಗದು ಸ್ವೀಕರಿಸಿದ್ದೇವೆ.',
                ),
                array(
                'notification_type'=>275,
                'kn_message'=>'ನೀವು ನಗದು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ {{amount}} ನಾಣ್ಯಗಳು ಸ್ವೀಕರಿಸಿದ್ದೇವೆ.',
                ),
                array(
                'notification_type'=>276,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನಿಮ್ಮ ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಪಾಲ್ಗೊಳ್ಳಲು ಮೇಲೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>277,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನಿಮ್ಮ ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಪಾಲ್ಗೊಳ್ಳಲು ಮೇಲೆ {{amount}} ಬೋನಸ್ ನಗದು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>278,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನಿಮ್ಮ ಸ್ನೇಹಿತನ ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಸೇರುವ ಮೇಲೆ {{amount}} ನಾಣ್ಯಗಳು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>279,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಪಾಲ್ಗೊಳ್ಳಲು ಮೇಲೆ ₹ {{amount}} ನಿಜವಾದ ನಗದು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>280,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಪಾಲ್ಗೊಳ್ಳಲು ಮೇಲೆ {{amount}} ಬೋನಸ್ ನಗದು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>281,
                'kn_message'=>'ಅಭಿನಂದನೆಗಳು! ನೀವು ಸ್ಪರ್ಧೆಯಲ್ಲಿ ಪಾಲ್ಗೊಳ್ಳಲು ಮೇಲೆ ಆಫ್ {{amount}} ನಾಣ್ಯಗಳು ಹೆಚ್ಚುವರಿ ಸಾಪ್ತಾಹಿಕ ಪ್ರಯೋಜನ ಪಡೆದಿದ್ದಾರೆ.',
                ),
                array(
                'notification_type'=>425,
                'kn_message'=>'{{amount}} ನಿಮಗೆ ಮನ್ನಣೆ ನಾಣ್ಯಗಳು ಖಾತೆ.',
                ),
                array(
                'notification_type'=>426,
                'kn_message'=>'ನಿಮ್ಮ ನ {{amount}} ಬೋನಸ್ ನಗದು ಮುಂದಿನ 7 ದಿನಗಳ ಮುಗಿಯುತ್ತಿದೆ.',
                ),
        );
        $this->db->update_batch(NOTIFICATION_DESCRIPTION,$notification_message_data,'notification_type');

}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'kn_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'kn_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'kn_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'kn_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'kn_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'kn_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'kn_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'kn_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'kn_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'kn_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'kn_body');
		$this->dbforge->drop_column(EARN_COINS, 'id');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'kn_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'kn_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'kn_category');
	}

}
