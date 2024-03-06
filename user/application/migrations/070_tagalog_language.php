<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tagalog_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'tl_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'tl_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'tl_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'tl_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'tl_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tl_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tl_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tl_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `tl_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'tl_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'tl_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'tl' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'tl_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'tl_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'tl_category'=>array(
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
                    'tl_title' => 'TOURNAMENT MODE',
                    'tl_desc' => 'Long Pro Player Season? I-play ang lahat ng mga panahon dito ',
                    'game_key' => 'allow_tournament',
                    ), array (
                    'tl_title' => 'DAILY FANTASY SPORTS',
                    'tl_desc' => 'Ang mga pang-araw-araw na sports sa pantasiya ay mas nakakainteres kaysa sa tradisyonal na pantasyang pantasiya',
                    'game_key' => 'allow_dfs',
                    ), array (
                    'tl_title' => 'FORECAST & WIN COINS',
                    'tl_desc' => 'Walang kinakailangang kasanayan sa pantasya. Hulaan lamang ang resulta at manalo ng mga barya ',
                    'game_key' => 'allow_prediction',
                    ), array (
                    'tl_title' => 'Piliin ang\' Prize Pool',
                    'tl_desc' => 'Ang laro mismo ay napaka-simple. Piliin lang ang panalong panig ',
                    'game_key' => 'allow_pickem',
                    ), array (
                    'tl_title' => 'MULTI GAME',
                    'tl_desc' => 'Ang mga larong pantasiya ng maraming laro ay mas nakakainteres kaysa sa tradisyonal na mga pantasiya na laro',
                    'game_key' => 'allow_multigame',
                    ), array (
                    'tl_title' => 'BUKSAN ANG PAGTATAYA',
                    'tl_desc' => 'Hulaan lamang ang resulta at manalo ng mga barya',
                    'game_key' => 'allow_open_predictor',
                    ), array (
                    'tl_title' => 'Malayang maglaro',
                    'tl_desc' => 'Maglaro ng pantasya araw-araw nang libre at manalo ng magagandang premyo.',
                    'game_key' => 'allow_free2play',
                    ), array (
                    'tl_title' => 'naayos na bukas na tagahula',
                    'tl_desc' => 'Hulaan lamang ang resulta at manalo ng premyo',
                    'game_key' => 'allow_fixed_open_predictor',
                    ), array (
                    'tl_title' => '',
                    'tl_desc' => '',
                    'game_key' => 'allow_prop_fantasy',
                    ),
		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
             array (
                'tl_header' => 'Kabuuan',
                'tl_body' => 'Manalo + cash bonus + deposito',
                'content_key' => 'wallet',
            ),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');
		  
		$earn_coins =array (
            
			array (
                    'module_key' => 'refer-a-friend',
                    'tl' =>
                    json_encode (array (
                    'label' => 'Mag-imbita ng mga kaibigan',
                    'description' => 'Kumuha ng 100 barya para sa bawat nakarehistrong kaibigan',
                    'button_text' => 'Tumutukoy sa',
                    )),
                    ),
                    
                    array (
                    'module_key' => 'daily_streak_bonus',
                    'tl' =>
                    json_encode (array (
                    'label' => 'DAILY registration bonus',
                    'description' => 'Kumuha ng mga barya araw-araw sa pamamagitan ng pag-log in',
                    'button_text' => 'Matuto nang higit pa',
                    )),
                    ),
                    
                    array (
                    'module_key' => 'prediction',
                    'tl' =>
                    json_encode (array (
                    'label' => 'Pagtataya MAGLARO',
                    'description' => 'Hulaan at kumita ng mga barya',
                    'button_text' => 'Pagtataya',
                    )),
                    ),
                    
                    array (
                    'module_key' => 'promotions',
                    'tl' =>
                    json_encode (array (
                    'label' => 'Promosyon',
                    'description' => 'Wala sa mga barya? Panoorin ang video at itaas ang iyong coin purse ',
                    'button_text' => 'Tingnan',
                    )),
                    ),
                    
                    array (
                    'module_key' => 'feedback',
                    'tl' =>
                    json_encode (array (
                    'label' => 'Suriin',
                    'description' => 'Ang tunay na panukala ay ibibigay pagkatapos ng pag-apruba ng administrator',
                    'button_text' => 'I-email sa amin',
                    )),
                    ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
            array (
                    'category_alias' => 'registration',
                    'tl_category' => 'Pagrehistro',
                    ), array (
                    'category_alias' => 'playing_the_game',
                    'tl_category' => '"Naglalaro ng mga laro"',
                    ), array (
                    'category_alias' => 'scores_points',
                    'tl_category' => 'Mga rating at rating',
                    ), array (
                    'category_alias' => 'contests',
                    'tl_category' => '"Kompetisyon"',
                    ), array (
                    'category_alias' => 'account_balance',
                    'tl_category' => '"Balanse ng account"',
                    ), array (
                    'category_alias' => 'verification',
                    'tl_category' => 'Kumpirmahin',
                    ), array (
                    'category_alias' => 'withdrawals',
                    'tl_category' => 'Tanggalin',
                    ), array (
                    'category_alias' => 'legality',
                    'tl_category' => 'Tama',
                    ), array (
                    'category_alias' => 'fair_play_violation',
                    'tl_category' => 'Paglabag sa Makatarungang Play',
                    ), array (
                    'category_alias' => 'payments',
                    'tl_category' => '"Pagbabayad"',
                    ),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
            array (
                'page_alias' => 'about',
                'tl_meta_keyword' => 'Tungkol sa Amin',
                'tl_page_title' => 'Tungkol sa Amin',
                'tl_meta_desc' => 'Tungkol sa amin',
                'tl_page_content' => 'Tungkol sa Amin',
                ), array (
                'page_alias' => 'how_it_works',
                'tl_meta_keyword' => 'Paano ito gumagana?',
                'tl_page_title' => 'Paano ito gumagana?',
                'tl_meta_desc' => 'Paano ito gumagana?',
                'tl_page_content' => 'Paano ito gumagana?',
                ), array (
                'page_alias' => 'terms_of_use',
                'tl_meta_keyword' => 'Mga Tuntunin ng Serbisyo',
                'tl_page_title' => 'Mga Tuntunin ng Serbisyo',
                'tl_meta_desc' => 'Mga Tuntunin ng Serbisyo',
                'tl_page_content' => 'Mga Tuntunin ng Serbisyo',
                ), array (
                'page_alias' => 'privacy_policy',
                'tl_meta_keyword' => 'Patakaran sa Privacy',
                'tl_page_title' => 'Patakaran sa Privacy',
                'tl_meta_desc' => 'Patakaran sa Privacy',
                'tl_page_content' => 'Patakaran sa Privacy',
                ), array (
                'page_alias' => 'faq',
                'tl_meta_keyword' => '"FAQ"',
                'tl_page_title' => 'Mga madalas na tinatanong',
                'tl_meta_desc' => 'Mga madalas na tinatanong',
                'tl_page_content' => 'Mga madalas na tinatanong',
                ), array (
                'page_alias' => 'support',
                'tl_meta_keyword' => 'Suporta',
                'tl_page_title' => 'Suporta',
                'tl_meta_desc' => 'Suporta',
                'tl_page_content' => 'Suporta',
                ), array (
                'page_alias' => 'affiliations',
                'tl_meta_keyword' => 'Mga Kasosyo',
                'tl_page_title' => 'Mga Kasosyo',
                'tl_meta_desc' => 'Mga Kasosyo',
                'tl_page_content' => 'Mga Kasosyo',
                ), array (
                'page_alias' => 'rules_and_scoring',
                'tl_meta_keyword' => 'Mga panuntunan at pagtatasa',
                'tl_page_title' => 'Mga Panuntunan at Halaga',
                'tl_meta_desc' => 'Mga Panuntunan at Rating',
                'tl_page_content' => 'Mga Panuntunan at Pagsusuri',
                ), array (
                'page_alias' => 'career',
                'tl_meta_keyword' => 'Career',
                'tl_page_title' => 'Trabaho',
                'tl_meta_desc' => 'Career',
                'tl_page_content' => 'Propesyon',
                ), array (
                'page_alias' => 'press_media',
                'tl_meta_keyword' => 'Pindutin at Media',
                'tl_page_title' => 'Pindutin ang Media',
                'tl_meta_desc' => 'Pindutin ang Media',
                'tl_page_content' => 'Press & Media',
                ), array (
                'page_alias' => 'referral',
                'tl_meta_keyword' => 'Directory',
                'tl_page_title' => 'Sanggunian',
                'tl_meta_desc' => 'Directory',
                'tl_page_content' => 'Link',
                ), array (
                'page_alias' => 'offers',
                'tl_meta_keyword' => 'Mga Mungkahi',
                'tl_page_title' => 'Alok',
                'tl_meta_desc' => 'Alok',
                'tl_page_content' => 'Alok',
                ), array (
                'page_alias' => 'contact_us',
                'tl_meta_keyword' => 'Tungkol sa Amin',
                'tl_page_title' => 'Tungkol sa Amin',
                'tl_meta_desc' => 'Tungkol sa amin',
                'tl_page_content' => 'Tungkol sa Amin',
                ), array (
                'page_alias' => 'legality',
                'tl_meta_keyword' => 'Ligal',
                'tl_page_title' => 'Ligal',
                'tl_meta_desc' => 'Ligal',
                'tl_page_content' => 'Ligal',
                ), array (
                'page_alias' => 'refund_policy',
                'tl_meta_keyword' => 'Patakaran sa Pagbabalik',
                'tl_page_title' => 'Patakaran sa Refund',
                'tl_meta_desc' => 'Patakaran sa Pagbabalik',
                'tl_page_content' => 'Patakaran sa Refund',
                ),
        );
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'tl_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'tl_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'tl_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'tl_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'tl_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'tl_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'tl_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'tl_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'tl_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'tl_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'tl_body');
		$this->dbforge->drop_column(EARN_COINS, 'tl');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'tl_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'tl_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'tl_category');
	}

}