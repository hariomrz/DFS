<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Chinese_language extends CI_Migration {

	public function up() {

        $notification_field = array(
			'zh_message' => array(
                'type' => 'LONGTEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
			'zh_subject' => array(
			'type' => 'LONGTEXT',
			'character_set' => 'utf8 COLLATE utf8_general_ci',
			'null' => FALSE,
			),
		);
		$this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);

		$transection_field = array(
			'zh_message' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
			),
		);
		$this->dbforge->add_column(TRANSACTION_MESSAGES, $transection_field);
		
		$sportshub_field = array(
			'zh_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'zh_desc' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			
		);
		
		$this->dbforge->add_column(SPORTS_HUB, $sportshub_field);
		
		$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `zh_meta_keyword` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `zh_page_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `zh_meta_desc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);	  	

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(CMS_PAGES)." ADD `zh_page_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		$this->db->query($sql);

			
		$common_content_field = array(
			'zh_header'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'zh_body'=> array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);
	
		$this->dbforge->add_column(COMMON_CONTENT, $common_content_field);

		$field = array(
			'zh' => array(
                'type' => 'JSON',
                'null' => TRUE,
				'default' => NULL,
			  ),
		);
		$this->dbforge->add_column(EARN_COINS, $field);

		$faq_question_fields = array(
			'zh_question'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
			'zh_answer'=>array(
				'type' => 'TEXT',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
				'default'=>NULL,
			),
		);

		$this->dbforge->add_column(FAQ_QUESTIONS, $faq_question_fields);
		
		$faq_category_fields = array(
			'zh_category'=>array(
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
				'zh_title'=>'比赛模式',
				'zh_desc'=>'职业赛季长球员？ 在这里玩整个赛季',
				'game_key'=>'allow_tournament',
			),array(
				'zh_title'=>'每日幻想运动',
				'zh_desc'=>'日常幻想运动比传统幻想运动更加令人兴奋',
				'game_key'=>'allow_dfs',
			),array(
				'zh_title'=>'预测和胜利硬币',
				'zh_desc'=>'不需要幻想技能。 只需预测结果并赢取金币',
				'game_key'=>'allow_prediction',
			),array(
				'zh_title'=>'选择“ em奖”基金',
				'zh_desc'=>'游戏本身非常简单。 只要选择获胜的一面',
				'game_key'=>'allow_pickem',
			),array(
				'zh_title'=>'多游戏',
				'zh_desc'=>'多游戏幻想游戏比传统幻想游戏有趣得多',
				'game_key'=>'allow_multigame',
			),array(
				'zh_title'=>'公开预测',
				'zh_desc'=>'只需预测结果并赢取金币',
				'game_key'=>'allow_open_predictor',
			),array(
				'zh_title'=>'免费玩',
				'zh_desc'=>'完全免费玩每日幻想并赢取大奖.',
				'game_key'=>'allow_free2play',
			),array(
				'zh_title'=>'固定开放预测',
				'zh_desc'=>'只需预测结果并赢取奖品',
				'game_key'=>'allow_fixed_open_predictor',
			),array(
				'zh_title'=>'',
				'zh_desc'=>'',
				'game_key'=>'allow_prop_fantasy',
            ),
		);

		$this->db->update_batch(SPORTS_HUB,$sports_hub_arr,'game_key');
		
		$common_content_arr = array(
			array (
				'zh_header' => '全部的',
				'zh_body' => '奖金+现金红利+存款',
				'content_key' => 'wallet',
				),
		);
		$this->db->update_batch(COMMON_CONTENT,$common_content_arr,'content_key');
		  
		$earn_coins =array (
            
			array (
				'module_key' => 'refer-a-friend',
				'zh' =>
				json_encode (array (
                  'label'=>'邀请朋友',
                  'description'=>'为每个注册朋友赚取100个硬币',
                  'button_text'=>'引用',
				)),
			  ),
			 
			  array (
				'module_key' => 'daily_streak_bonus',
				'zh' =>
				json_encode (array (
                  'label'=>'DAILY注册红利',
                  'description'=>'每天通过登录获取硬币',
                  'button_text'=>'了解更多',
				)),
			  ),
			  
			  array (
				'module_key' => 'prediction',
				'zh' =>
				json_encode (array (
                  'label'=>'播放预测',
                  'description'=>'预测并赚取金币',
                  'button_text'=>'预测',
				)),
			  ),
			  
			  array (
				'module_key' => 'promotions',
				'zh' =>
				json_encode (array (
                  'label'=>'促销',
                  'description'=>'硬币没了吗？ 观看视频并为硬币钱包充值',
                  'button_text'=>'视图',
				)),
			  ),
			 
			  array (
				'module_key' => 'feedback',
				'zh' =>
				json_encode (array (
				  'label' => '回馈',
				  'description' => '真正的建议书将在管理员批准后颁发。',
				  'button_text' => '写信给我们',
				)),
			  ),
		  );

		$this->db->update_batch(EARN_COINS,$earn_coins,'module_key');

		$categories = array (
			array (
			'category_alias' => 'registration',
			'zh_category' => '报到',
			), array (
			'category_alias' => 'playing_the_game',
			'zh_category' => '玩游戏',
			), array (
			'category_alias' => 'scores_points',
			'zh_category' => '评估与评级',
			), array (
			'category_alias' => 'contests',
			'zh_category' => '竞赛',
			), array (
			'category_alias' => 'account_balance',
			'zh_category' => '账户余额',
			), array (
			'category_alias' => 'verification',
			'zh_category' => '确认',
			), array (
			'category_alias' => 'withdrawals',
			'zh_category' => '脱掉',
			), array (
			'category_alias' => 'legality',
			'zh_category' => '正确的',
			), array (
			'category_alias' => 'fair_play_violation',
			'zh_category' => '违反公平竞争',
			), array (
			'category_alias' => 'payments',
			'zh_category' => '支付',
			),
		);
		$this->db->update_batch(FAQ_CATEGORY,$categories,'category_alias');
		
		$cms_data = array (
			array (
				'page_alias' => 'about',
                'zh_meta_keyword'=>'关于我们',
                'zh_page_title'=>'关于我们',
                'zh_meta_desc'=>'关于我们',
                'zh_page_content'=>'关于我们',
				), array (
				'page_alias' => 'how_it_works',
				'zh_meta_keyword'=>'它如何工作？',
                'zh_page_title'=>'如何运作？',
                'zh_meta_desc'=>'它是如何工作的？',
                'zh_page_content'=>'它如何工作？',
				), array (
				'page_alias' => 'terms_of_use',
				'zh_meta_keyword'=>'服务条款',
                'zh_page_title'=>'服务条款',
                'zh_meta_desc'=>'服务条款',
                'zh_page_content'=>'服务条款',
				), array (
				'page_alias' => 'privacy_policy',
				'zh_meta_keyword'=>'隐私政策',
                'zh_page_title'=>'隐私政策',
                'zh_meta_desc'=>'隐私政策',
                'zh_page_content'=>'隐私政策',
				), array (
				'page_alias' => 'faq',
				'zh_meta_keyword'=>'常见问题',
                'zh_page_title'=>'常见问题',
                'zh_meta_desc'=>'常见问题',
                'zh_page_content'=>'常见问题',
				), array (
				'page_alias' => 'support',
				'zh_meta_keyword'=>'支持',
                'zh_page_title'=>'支持',
                'zh_meta_desc'=>'支持',
                'zh_page_content'=>'支持',
				), array (
				'page_alias' => 'affiliations',
				'zh_meta_keyword'=>'合作伙伴',
                'zh_page_title'=>'合作伙伴',
                'zh_meta_desc'=>'合作伙伴',
                'zh_page_content'=>'合作伙伴',
				), array (
				'page_alias' => 'rules_and_scoring',
				'zh_meta_keyword'=>'规则和得分',
				'zh_page_title'=>'规则和等级',
				'zh_meta_desc'=>'规则和得分',
				'zh_page_content'=>'规则和评估',
				), array (
				'page_alias' => 'career',
				'zh_meta_keyword'=>'职业',
				'zh_page_title'=>'职业',
				'zh_meta_desc'=>'职业',
				'zh_page_content'=>'专业',
				), array (
				'page_alias' => 'press_media',
				'zh_meta_keyword'=>'新闻和媒体',
				'zh_page_title'=>'新闻和媒体',
				'zh_meta_desc'=>'新闻和媒体',
				'zh_page_content'=>'新闻和媒体',
				), array (
				'page_alias' => 'referral',
				'zh_meta_keyword'=>'目录',
				'zh_page_title'=>'参考',
				'zh_meta_desc'=>'目录',
				'zh_page_content'=>'链接',
				), array (
				'page_alias' => 'offers',
				'zh_meta_keyword'=>'建议',
				'zh_page_title'=>'要约',
				'zh_meta_desc'=>'建议',
				'zh_page_content'=>'建议',
				), array (
				'page_alias' => 'contact_us',
				'zh_meta_keyword'=>'关于我们',
				'zh_page_title'=>'关于我们',
				'zh_meta_desc'=>'关于我们',
				'zh_page_content'=>'关于我们',
				), array (
				'page_alias' => 'legality',
				'zh_meta_keyword'=>'法律',
				'zh_page_title'=>'法律',
				'zh_meta_desc'=>'法律',
				'zh_page_content'=>'法律',
				), array (
				'page_alias' => 'refund_policy',
				'zh_meta_keyword'=>'退货政策',
				'zh_page_title'=>'退款政策',
				'zh_meta_desc'=>'退货政策',
				'zh_page_content'=>'退款政策',
				),
		);
		
        $this->db->update_batch(CMS_PAGES,$cms_data,'page_alias');
}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'zh_message');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'zh_subject');
		$this->dbforge->drop_column(TRANSACTION_MESSAGES, 'zh_message');
		$this->dbforge->drop_column(SPORTS_HUB, 'zh_title');
		$this->dbforge->drop_column(SPORTS_HUB, 'zh_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'zh_meta_keyword');
		$this->dbforge->drop_column(CMS_PAGES, 'zh_page_title');
		$this->dbforge->drop_column(CMS_PAGES, 'zh_meta_desc');
		$this->dbforge->drop_column(CMS_PAGES, 'zh_page_content');
		$this->dbforge->drop_column(COMMON_CONTENT, 'zh_header');
		$this->dbforge->drop_column(COMMON_CONTENT, 'zh_body');
		$this->dbforge->drop_column(EARN_COINS, 'zh');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'zh_question');
		$this->dbforge->drop_column(FAQ_QUESTIONS, 'zh_answer');
		$this->dbforge->drop_column(FAQ_CATEGORY, 'zh_category');
	}

}