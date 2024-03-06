<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Russian_language extends CI_Migration {

	public function up() {


        //up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `ru_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `ru_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
          
        $master_cat_field = array(
            'ru_scoring_category_name' => array(
                'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
			),
		);
        $this->dbforge->add_column(MASTER_SCORING_CATEGORY, $master_cat_field);

        $master_rule_field = array(
			'ru_score_position' => array(
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
                'ru_name' => 'Пригласите друга',
                'banner_id' => '1'
            ),array(
                'ru_name' => 'Депозит',
                'banner_id' => '2'
            ),array(
                'ru_name' => 'Регистрация',
                'banner_id' => '3'
            ),
        );

        $this->db->update_batch(BANNER_MANAGEMENT,$banner_arr,'banner_id');

        
        $master_category_arr = array(
            array(
                'ru_scoring_category_name' => 'нормальный',
                'scoring_category_name' => 'normal'
            ),array(
                'ru_scoring_category_name' => 'бонус',
                'scoring_category_name' => 'bonus'
            ),array(
                'ru_scoring_category_name' => 'экономичный тариф',
                'scoring_category_name' => 'economy_rate'
            ),array(
                'ru_scoring_category_name' => 'попадание',
                'scoring_category_name' => 'hitting'
            ),array(
                'ru_scoring_category_name' => 'качка',
                'scoring_category_name' => 'pitching'
            ),array(
                'ru_scoring_category_name' => 'рейтинг забастовки',
                'scoring_category_name' => 'strike_rate'
            ),
        );

        $this->db->update_batch(MASTER_SCORING_CATEGORY,$master_category_arr,'scoring_category_name');

        $master_format_arr =array(
            array(
                'ru_display_name' => 'БЕЙСБОЛ',
                 'sports_id' => 1,
            ),array(
                'ru_display_name' => 'ФУТБОЛ',
                 'sports_id' => 2,
            ),array(
                'ru_display_name' => 'БАСКЕТБОЛ',
                 'sports_id' => 4,
            ),array(
                'ru_display_name' => 'ФУТБОЛ',
                 'sports_id' => 5,
            ),array(
                'ru_display_name' => 'КРИКЕТ',
                 'sports_id' => 7,
            ),array(
                'ru_display_name' => 'КАБАДДИ',
                 'sports_id' => 8,
            ),array(
                'ru_display_name' => 'ГОЛЬФ',
                 'sports_id' => 9,
            ),array(
                'ru_display_name' => 'БАДМИНТОН',
                 'sports_id' => 10,
            ),array(
                'ru_display_name' => 'ТЕННИС',
                 'sports_id' => 11,
            ),array(
                'ru_display_name' => 'NCAA',
                 'sports_id' => 13,
            ),
        );

        $this->db->update_batch(MASTER_SPORTS_FORMAT,$master_format_arr,'sports_id');

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'ru_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'ru_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'ru_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'ru_display_name');
	}

}