<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Chinese_language extends CI_Migration {

	public function up() {


        //up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `zh_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `zh_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
          
        $master_cat_field = array(
            'zh_scoring_category_name' => array(
                'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
			),
		);
        $this->dbforge->add_column(MASTER_SCORING_CATEGORY, $master_cat_field);

        $master_rule_field = array(
			'zh_score_position' => array(
				'type' => 'VARCHAR',
				'constraint' => 1000,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
            ),
        );

        $this->dbforge->add_column(MASTER_SCORING_RULES, $master_rule_field);
        

        //update query from here

        $banner_arr = array(
            array(
                'zh_name' => '介绍个朋友',
                'banner_id' => '1'
            ),array(
                'zh_name' => '订金',
                'banner_id' => '2'
            ),array(
                'zh_name' => '报名',
                'banner_id' => '3'
            ),
        );

        $this->db->update_batch(BANNER_MANAGEMENT,$banner_arr,'banner_id');

        
        $master_category_arr = array(
            array(
                'zh_scoring_category_name' => '普通的',
                'scoring_category_name' => 'normal'
            ),array(
                'zh_scoring_category_name' => '奖金',
                'scoring_category_name' => 'bonus'
            ),array(
                'zh_scoring_category_name' => '经济率',
                'scoring_category_name' => 'economy_rate'
            ),array(
                'zh_scoring_category_name' => '打',
                'scoring_category_name' => 'hitting'
            ),array(
                'zh_scoring_category_name' => '投球',
                'scoring_category_name' => 'pitching'
            ),array(
                'zh_scoring_category_name' => '罢工率',
                'scoring_category_name' => 'strike_rate'
            ),
        );

        $this->db->update_batch(MASTER_SCORING_CATEGORY,$master_category_arr,'scoring_category_name');

        $master_format_arr =array(
            array(
                'zh_display_name' => '棒球',
                 'sports_id' => 1,
            ),array(
                'zh_display_name' => '足球',
                 'sports_id' => 2,
            ),array(
                'zh_display_name' => '篮球',
                 'sports_id' => 4,
            ),array(
                'zh_display_name' => '足球',
                 'sports_id' => 5,
            ),array(
                'zh_display_name' => '蟋蟀',
                 'sports_id' => 7,
            ),array(
                'zh_display_name' => '卡巴迪',
                 'sports_id' => 8,
            ),array(
                'zh_display_name' => '高尔夫球',
                 'sports_id' => 9,
            ),array(
                'zh_display_name' => '羽毛球',
                 'sports_id' => 10,
            ),array(
                'zh_display_name' => '网球',
                 'sports_id' => 11,
            ),array(
                'zh_display_name' => '恩卡',
                 'sports_id' => 13,
            ),
        );

        $this->db->update_batch(MASTER_SPORTS_FORMAT,$master_format_arr,'sports_id');

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'zh_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'zh_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'zh_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'zh_display_name');
	}

}