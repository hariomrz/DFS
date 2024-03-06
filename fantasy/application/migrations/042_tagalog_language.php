<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tagalog_language extends CI_Migration {

	public function up() {


        //up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `tl_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `tl_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
          
        $master_cat_field = array(
            'tl_scoring_category_name' => array(
                'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
			),
		);
        $this->dbforge->add_column(MASTER_SCORING_CATEGORY, $master_cat_field);

        $master_rule_field = array(
			'tl_score_position' => array(
				'type' => 'VARCHAR',
				'constraint' => 1000,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
            ),
        );

        $this->dbforge->add_column(MASTER_SCORING_RULES, $master_rule_field);
        

        //update query from here

        $banner_arr = array(
            array (
                'tl_name' => 'Pagtuturo ng mga kaibigan',
                'banner_id' => '1'
            ), array (
                'tl_name' => 'deposito',
                'banner_id' => '2'
            ), array (
                'tl_name' => 'Magrehistro',
                'banner_id' => '3'
            ),
        );

        $this->db->update_batch(BANNER_MANAGEMENT,$banner_arr,'banner_id');

        
        $master_category_arr = array(
            array (
                'tl_scoring_category_name' => 'normal',
                'scoring_category_name' => 'normal'
            ), array (
                'tl_scoring_category_name' => 'bonus',
                'scoring_category_name' => 'bonus'
            ), array (
                'tl_scoring_category_name' => 'antas ng ekonomiya',
                'scoring_category_name' => 'economy_rate'
            ), array (
                'tl_scoring_category_name' => 'hit',
                'scoring_category_name' => 'hitting'
            ), array (
                'tl_scoring_category_name' => 'pagtatayo',
                'scoring_category_name' => 'pitching'
            ), array (
                'tl_scoring_category_name' => 'ranggo ng pag-atake',
                'scoring_category_name' => 'strike_rate'
            ),
        );

        $this->db->update_batch(MASTER_SCORING_CATEGORY,$master_category_arr,'scoring_category_name');

        $master_format_arr =array(
            array (
                'tl_display_name' => 'BASEBALL',
                 'sports_id' => 1,
            ), array (
                'tl_display_name' => 'FOOTBALL',
                 'sports_id' => 2,
            ), array (
                'tl_display_name' => 'BASKETBALL',
                 'sports_id' => 4,
            ), array (
                'tl_display_name' => 'SOCCER',
                 'sports_id' => 5,
            ), array (
                'tl_display_name' => 'kuliglig',
                 'sports_id' => 7,
            ), array (
                'tl_display_name' => 'KABADDI',
                 'sports_id' => 8,
            ), array (
                'tl_display_name' => 'GOLF',
                 'sports_id' => 9,
            ), array (
                'tl_display_name' => 'BADMINTON',
                 'sports_id' => 10,
            ), array (
                'tl_display_name' => 'TENNIS',
                 'sports_id' => 11,
            ), array (
                'tl_display_name' => 'NCAA',
                 'sports_id' => 13,
            ),
        );

        $this->db->update_batch(MASTER_SPORTS_FORMAT,$master_format_arr,'sports_id');

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'tl_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'tl_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'tl_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'tl_display_name');
	}

}