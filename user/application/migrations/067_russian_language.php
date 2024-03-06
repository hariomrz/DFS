<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Russian_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'ru_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'ru_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'ru_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'ru_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'ru_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ru_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ru_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ru_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `ru_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'ru_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'ru_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'ru' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'ru_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'ru_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'ru_category'=>array(
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
			array(
				'ru_title'=>'ТУРНИР РЕЖИМ',
				'ru_desc'=>'Pro Сезон Long игрока? Играть в течение всего сезона здесь',
				'game_key'=>'allow_tournament',
			),array(
				'ru_title'=>'ЕЖЕДНЕВНЫЙ ФАНТАЗИЙ СПОРТ',
				'ru_desc'=>'Ежедневно фантазии спорта является гораздо более захватывающим, чем традиционные фантазии спорта',
				'game_key'=>'allow_dfs',
			),array(
				'ru_title'=>'ПРОГНОЗ & WIN МОНЕТЫ',
				'ru_desc'=>'Не требуется фантазия навыков. Просто предсказать исход и выиграть монеты',
				'game_key'=>'allow_prediction',
			),array(
				'ru_title'=>'Выберите эм Призовой фонд',
				'ru_desc'=>'Сама игра очень проста. Просто выберите выигрышную сторону',
				'game_key'=>'allow_pickem',
			),array(
				'ru_title'=>'МУЛЬТИ ИГРА',
				'ru_desc'=>'Мульти игра фантазия игра гораздо интереснее, чем традиционная фэнтезийная игра',
				'game_key'=>'allow_multigame',
			),array(
				'ru_title'=>'ОТКРЫТЫЙ ПРОГНОЗ',
				'ru_desc'=>'Просто предсказать исход и выиграть монеты',
				'game_key'=>'allow_open_predictor',
			),array(
				'ru_title'=>'Бесплатно играть',
				'ru_desc'=>'Играть ежедневно фантазии совершенно бесплатно и выиграть отличные призы.',
				'game_key'=>'allow_free2play',
			),array(
				'ru_title'=>'исправлен открытый предиктор',
				'ru_desc'=>'Просто предсказать исход и выиграть призы',
				'game_key'=>'allow_fixed_open_predictor',
			),array(
				'ru_title'=>'',
				'ru_desc'=>'',
				'game_key'=>'allow_prop_fantasy',
			),

		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
			array (
				'ru_header' => 'Итого',
				'ru_body' => 'Выигрыш + денежный бонус + депозит',
				'content_key' => 'wallet',
				),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');
		  
		$earn_coins =array (
            
			array (
				'module_key' => 'refer-a-friend',
				'ru' =>
				json_encode (array (
				  'label' => 'Пригласите друга',
				  'description' => 'Зарабатывайте 100 монет за каждого зарегистрированного друга',
				  'button_text' => 'Относится к',
				)),
			  ),
			 
			  array (
				'module_key' => 'daily_streak_bonus',
				'ru' =>
				json_encode (array (
				  'label' => 'ЕЖЕДНЕВНЫЙ бонус за регистрацию',
				  'description' => 'Зарабатывайте монеты каждый день, войдя в систему',
				  'button_text' => 'Узнать больше',
				)),
			  ),
			  
			  array (
				'module_key' => 'prediction',
				'ru' =>
				json_encode (array (
				  'label' => 'PLAY Predict',
				  'description' => 'Прогнозируй и зарабатывай монеты',
				  'button_text' => 'Прогнозировать',
				)),
			  ),
			  
			  array (
				'module_key' => 'promotions',
				'ru' =>
				json_encode (array (
				  'label' => 'Продвижение',
				  'description' => 'Закончилась монета? Смотрите видео и пополняйте кошелек монет',
				  'button_text' => 'Просмотр',
				)),
			  ),
			 
			  array (
				'module_key' => 'feedback',
				'ru' =>
				json_encode (array (
				  'label' => 'Отзыв',
				  'description' => 'Подлинные предложения будут присуждены после одобрения администратором',
				  'button_text' => 'Напишите нам',
				)),
			  ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
			array (
			'category_alias' => 'registration',
			'ru_category' => 'Регистрация',
			), array (
			'category_alias' => 'playing_the_game',
			'ru_category' => 'Игра в игру',
			), array (
			'category_alias' => 'scores_points',
			'ru_category' => 'Оценка и рейтинг',
			), array (
			'category_alias' => 'contests',
			'ru_category' => 'Конкурс',
			), array (
			'category_alias' => 'account_balance',
			'ru_category' => 'Остаток на счете',
			), array (
			'category_alias' => 'verification',
			'ru_category' => 'Подтверждение',
			), array (
			'category_alias' => 'withdrawals',
			'ru_category' => 'Снять',
			), array (
			'category_alias' => 'legality',
			'ru_category' => 'Право',
			), array (
			'category_alias' => 'fair_play_violation',
			'ru_category' => 'Нарушения честной игры',
			), array (
			'category_alias' => 'payments',
			'ru_category' => 'Оплата',
			),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
			array (
				'page_alias' => 'about',
				'ru_meta_keyword' => 'О нас',
				'ru_page_title' => 'О нас',
				'ru_meta_desc' => 'О нас',
				'ru_page_content' => 'О нас',
				), array (
				'page_alias' => 'how_it_works',
				'ru_meta_keyword' => 'Как это работает?',
				'ru_page_title' => 'Как это работает?',
				'ru_meta_desc' => 'Как это работает?',
				'ru_page_content' => 'Как это работает?',
				), array (
				'page_alias' => 'terms_of_use',
				'ru_meta_keyword' => 'Условия использования',
				'ru_page_title' => 'Условия использования',
				'ru_meta_desc' => 'Условия использования',
				'ru_page_content' => 'Условия использования',
				), array (
				'page_alias' => 'privacy_policy',
				'ru_meta_keyword' => 'Политика конфиденциальности',
				'ru_page_title' => 'Политика конфиденциальности',
				'ru_meta_desc' => 'Политика конфиденциальности',
				'ru_page_content' => 'Политика конфиденциальности',
				), array (
				'page_alias' => 'faq',
				'ru_meta_keyword' => 'FAQ',
				'ru_page_title' => 'Часто задаваемые вопросы',
				'ru_meta_desc' => 'Часто задаваемые вопросы',
				'ru_page_content' => 'Часто задаваемые вопросы',
				), array (
				'page_alias' => 'support',
				'ru_meta_keyword' => 'Поддержка',
				'ru_page_title' => 'Поддержка',
				'ru_meta_desc' => 'Поддержка',
				'ru_page_content' => 'Поддержка',
				), array (
				'page_alias' => 'affiliations',
				'ru_meta_keyword' => 'Партнер',
				'ru_page_title' => 'Партнер',
				'ru_meta_desc' => 'Партнер',
				'ru_page_content' => 'Партнер',
				), array (
				'page_alias' => 'rules_and_scoring',
				'ru_meta_keyword' => 'Правила и подсчет очков',
				'ru_page_title' => 'Правила и оценка',
				'ru_meta_desc' => 'Правила и подсчет очков',
				'ru_page_content' => 'Правила и оценка',
				), array (
				'page_alias' => 'career',
				'ru_meta_keyword' => 'Карьера',
				'ru_page_title' => 'Профессия',
				'ru_meta_desc' => 'Карьера',
				'ru_page_content' => 'Профессия',
				), array (
				'page_alias' => 'press_media',
				'ru_meta_keyword' => 'Пресса и СМИ',
				'ru_page_title' => 'Пресса и СМИ',
				'ru_meta_desc' => 'Пресса и СМИ',
				'ru_page_content' => 'Пресса и СМИ',
				), array (
				'page_alias' => 'referral',
				'ru_meta_keyword' => 'Справочник',
				'ru_page_title' => 'Справочник',
				'ru_meta_desc' => 'Справочник',
				'ru_page_content' => 'Ссылка',
				), array (
				'page_alias' => 'offers',
				'ru_meta_keyword' => 'Предложения',
				'ru_page_title' => 'Предложение',
				'ru_meta_desc' => 'Предложения',
				'ru_page_content' => 'Предложения',
				), array (
				'page_alias' => 'contact_us',
				'ru_meta_keyword' => 'О нас',
				'ru_page_title' => 'О нас',
				'ru_meta_desc' => 'О нас',
				'ru_page_content' => 'О нас',
				), array (
				'page_alias' => 'legality',
				'ru_meta_keyword' => 'Юридический',
				'ru_page_title' => 'Юридический',
				'ru_meta_desc' => 'Юридический',
				'ru_page_content' => 'Юридический',
				), array (
				'page_alias' => 'refund_policy',
				'ru_meta_keyword' => 'Политика возврата',
				'ru_page_title' => 'Политика возврата',
				'ru_meta_desc' => 'Политика возврата',
				'ru_page_content' => 'Политика возврата',
				),
		);
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'ru_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'ru_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'ru_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'ru_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'ru_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'ru_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'ru_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'ru_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'ru_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'ru_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'ru_body');
		$this->dbforge->drop_column(EARN_COINS, 'ru');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'ru_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'ru_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'ru_category');
	}

}