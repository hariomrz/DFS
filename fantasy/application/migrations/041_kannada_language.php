<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Kannada_language extends CI_Migration {

	public function up() {


        //up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `kn_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `kn_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
          
        $master_cat_field = array(
            'kn_scoring_category_name' => array(
                'type' => 'VARCHAR',
				'constraint' => 255,
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => TRUE,
			),
		);
        $this->dbforge->add_column(MASTER_SCORING_CATEGORY, $master_cat_field);

        $master_rule_field = array(
			'kn_score_position' => array(
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
                'kn_name' => 'ಸ್ನೇಹಿತನನ್ನು ನೋಡಿ',
                'banner_id' => '1'
            ), array (
                'kn_name' => 'ಠೇವಣಿ',
                'banner_id' => '2'
            ), array (
                'kn_name' => 'ಸೈನ್ ಅಪ್',
                'banner_id' => '3'
            ),
        );

        $this->db->update_batch(BANNER_MANAGEMENT,$banner_arr,'banner_id');

        
        $master_category_arr = array(
            array (
                'kn_scoring_category_name' => 'ಸಾಮಾನ್ಯ',
                'scoring_category_name' => 'normal'
            ), array (
                'kn_scoring_category_name' => 'ಬೋನಸ್',
                'scoring_category_name' => 'bonus'
            ), array (
                'kn_scoring_category_name' => 'ಆರ್ಥಿಕ ಮಟ್ಟ',
                'scoring_category_name' => 'economy_rate'
            ), array (
                'kn_scoring_category_name' => 'ಹಿಟ್',
                'scoring_category_name' => 'hitting'
            ), array (
                'kn_scoring_category_name' => 'ಪಿಚಿಂಗ್',
                'scoring_category_name' => 'pitching'
            ), array (
                'kn_scoring_category_name' => 'ದಾಳಿ ಶ್ರೇಣಿ',
                'scoring_category_name' => 'strike_rate'
            ),
        );

        $this->db->update_batch(MASTER_SCORING_CATEGORY,$master_category_arr,'scoring_category_name');

        $master_format_arr =array(
            array (
                'kn_display_name' => 'ಬೇಸ್‌ಬಾಲ್',
                 'sports_id' => 1,
            ), array (
                'kn_display_name' => 'ಫುಟ್‌ಬಾಲ್',
                 'sports_id' => 2,
            ), array (
                'kn_display_name' => 'ಬಾಸ್ಕೆಟ್‌ಬಾಲ್',
                 'sports_id' => 4,
            ), array (
                'kn_display_name' => 'ಸಾಕರ್',
                 'sports_id' => 5,
            ), array (
                'kn_display_name' => 'ಕ್ರಿಕೆಟ್',
                 'sports_id' => 7,
            ), array (
                'kn_display_name' => 'ಕಬಡ್ಡಿ',
                 'sports_id' => 8,
            ), array (
                'kn_display_name' => 'ಗಾಲ್ಫ್',
                 'sports_id' => 9,
            ), array (
                'kn_display_name' => 'ಬ್ಯಾಡ್ಮಿಂಟನ್',
                 'sports_id' => 10,
            ), array (
                'kn_display_name' => 'ಟೆನ್ನಿಸ್',
                 'sports_id' => 11,
            ), array (
                'kn_display_name' => 'ಎನ್‌ಸಿಎಎ',
                 'sports_id' => 13,
            ),
        );

        $this->db->update_batch(MASTER_SPORTS_FORMAT,$master_format_arr,'sports_id');

        $master_rule_data = array(
			array(
				'meta_key'=>'PLAYING_X1',
				'kn_score_position'=>'ಎಲೆವನ್ ಭಾಗವಾಗಿರುವುದಕ್ಕೆ',
				),
				array(
				'meta_key'=>'EVERY_RUN',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ರನ್',
				),
				array(
				'meta_key'=>'WICKET',
				'kn_score_position'=>'ವಿಕೆಟ್ (ರನ್ ಔಟ್ ಹೊರತುಪಡಿಸಿ)',
				),
				array(
				'meta_key'=>'CATCH',
				'kn_score_position'=>'ಕ್ಯಾಚ್',
				),
				array(
				'meta_key'=>'STUMPING',
				'kn_score_position'=>'ಸ್ಟಂಪಿಂಗ್',
				),
				array(
				'meta_key'=>'RUN_OUT_THROWER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಎಸೆತಗಾರ)',
				),
				array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಕ್ಯಾಚರ್)',
				),
				array(
				'meta_key'=>'RUN_OUT',
				'kn_score_position'=>'ರನ್ ಔಟ್',
				),
				array(
				'meta_key'=>'DUCK',
				'kn_score_position'=>'ಸೊನ್ನೆಗೆ ವಜಾಗೊಳಿಸು (ಬ್ಯಾಟ್ಸ್ಮನ್ಗಳು, ವಿಕೆಟ್ ಕೀಪರ್ ಮತ್ತು ಸರ್ವಾಂಗೀಣ)',
				),
				array(
				'meta_key'=>'EVERY_FOUR',
				'kn_score_position'=>'ಪ್ರತಿ ಗಡಿ ಹಿಟ್',
				),
				array(
				'meta_key'=>'EVERY_SIX',
				'kn_score_position'=>'ಪ್ರತಿ ಆರು ಹಿಟ್',
				),
				array(
				'meta_key'=>'HALF_CENTURY',
				'kn_score_position'=>'ಅರ್ಧಶತಕ',
				),
				array(
				'meta_key'=>'CENTURY',
				'kn_score_position'=>'ಸೆಂಚುರಿ',
				),
				array(
				'meta_key'=>'MAIDEN_OVER',
				'kn_score_position'=>'ಮೇಲೆ ಮೇಡನ್',
				),
				array(
				'meta_key'=>'FOUR_WICKET',
				'kn_score_position'=>'4 ವಿಕೆಟ್',
				),
				array(
				'meta_key'=>'FIVE_WICKET',
				'kn_score_position'=>'5 ವಿಕೆಟ್ಗಳಿಂದ',
				),
				array(
				'meta_key'=>'MINIMUM_BOWLING_OVER',
				'kn_score_position'=>'ಓವರ್ಗಳ ಯಾವುದೇ ನಮೂದಿಸಿ ಗಳಿಕೆಯ ನಿಯಮಗಳನ್ನು ಕೆಳಗಿನ ಇದಕ್ಕಾಗಿ ಅನ್ವಯವಾಗುತ್ತದೆ',
				),
				array(
				'meta_key'=>'BETWEEN_35_45',
				'kn_score_position'=>'4.5 ನಡುವೆ ಮತ್ತು 3.5 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BETWEEN_25_349',
				'kn_score_position'=>'3.49 ನಡುವೆ ಮತ್ತು 2.5 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BELOW_25',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 2.5 ರನ್ಗಳು ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'BETWEEN_7_8',
				'kn_score_position'=>'ನಡುವೆ 7 ಮತ್ತು 8 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BETWEEN_81_9',
				'kn_score_position'=>'8.01 ನಡುವೆ ಮತ್ತು 9 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'ABOVE_9',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 9 ರನ್ ಮೇಲೆ',
				),
				array(
				'meta_key'=>'MINIMUM_BALL',
				'kn_score_position'=>'ಯಾವುದೇ ನಮೂದಿಸಿ. ಕನಿಷ್ಟ ಚೆಂಡುಗಳ ಗಳಿಕೆಯ ನಿಯಮಗಳನ್ನು ಕೆಳಗಿನ ಇದಕ್ಕಾಗಿ (ಬೌಲರ್ಗಳು ಹೊರತುಪಡಿಸಿ) ಅನ್ವಯವಾಗುತ್ತದೆ',
				),
				array(
				'meta_key'=>'BETWEEN_50_60',
				'kn_score_position'=>'ನಡುವೆ 50 ಮತ್ತು 60 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BETWEEN_40_499',
				'kn_score_position'=>'ನಡುವೆ 40 ಮತ್ತು 49.9 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BELOW_40',
				'kn_score_position'=>'100 ಚೆಂಡುಗಳಿಗೆ 40 ರನ್ ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'PLAYING_X1',
				'kn_score_position'=>'ಎಲೆವನ್ ಭಾಗವಾಗಿರುವುದಕ್ಕೆ',
				),
				array(
				'meta_key'=>'EVERY_RUN',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ರನ್',
				),
				array(
				'meta_key'=>'WICKET',
				'kn_score_position'=>'ವಿಕೆಟ್ (ರನ್ ಔಟ್ ಹೊರತುಪಡಿಸಿ)',
				),
				array(
				'meta_key'=>'CATCH',
				'kn_score_position'=>'ಕ್ಯಾಚ್',
				),
				array(
				'meta_key'=>'STUMPING',
				'kn_score_position'=>'ಸ್ಟಂಪಿಂಗ್',
				),
				array(
				'meta_key'=>'RUN_OUT_THROWER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಎಸೆತಗಾರ)',
				),
				array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಕ್ಯಾಚರ್)',
				),
				array(
				'meta_key'=>'RUN_OUT',
				'kn_score_position'=>'ರನ್ ಔಟ್',
				),
				array(
				'meta_key'=>'DUCK',
				'kn_score_position'=>'ಸೊನ್ನೆಗೆ ವಜಾಗೊಳಿಸು (ಬ್ಯಾಟ್ಸ್ಮನ್ಗಳು, ವಿಕೆಟ್ ಕೀಪರ್ ಮತ್ತು ಸರ್ವಾಂಗೀಣ)',
				),
				array(
				'meta_key'=>'EVERY_FOUR',
				'kn_score_position'=>'ಪ್ರತಿ ಗಡಿ ಹಿಟ್',
				),
				array(
				'meta_key'=>'EVERY_SIX',
				'kn_score_position'=>'ಪ್ರತಿ ಆರು ಹಿಟ್',
				),
				array(
				'meta_key'=>'HALF_CENTURY',
				'kn_score_position'=>'ಅರ್ಧಶತಕ',
				),
				array(
				'meta_key'=>'CENTURY',
				'kn_score_position'=>'ಸೆಂಚುರಿ',
				),
				array(
				'meta_key'=>'FOUR_WICKET',
				'kn_score_position'=>'4 ವಿಕೆಟ್',
				),
				array(
				'meta_key'=>'FIVE_WICKET',
				'kn_score_position'=>'5 ವಿಕೆಟ್ಗಳಿಂದ',
				),
				array(
				'meta_key'=>'PLAYING_X1',
				'kn_score_position'=>'ಎಲೆವನ್ ಭಾಗವಾಗಿರುವುದಕ್ಕೆ',
				),
				array(
				'meta_key'=>'EVERY_RUN',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ರನ್',
				),
				array(
				'meta_key'=>'WICKET',
				'kn_score_position'=>'ವಿಕೆಟ್ (ರನ್ ಔಟ್ ಹೊರತುಪಡಿಸಿ)',
				),
				array(
				'meta_key'=>'CATCH',
				'kn_score_position'=>'ಕ್ಯಾಚ್',
				),
				array(
				'meta_key'=>'STUMPING',
				'kn_score_position'=>'ಸ್ಟಂಪಿಂಗ್',
				),
				array(
				'meta_key'=>'RUN_OUT_THROWER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಎಸೆತಗಾರ)',
				),
				array(
				'meta_key'=>'RUN_OUT_CATCHER',
				'kn_score_position'=>'ರನ್ ಔಟ್ (ಕ್ಯಾಚರ್)',
				),
				array(
				'meta_key'=>'RUN_OUT',
				'kn_score_position'=>'ರನ್ ಔಟ್',
				),
				array(
				'meta_key'=>'DUCK',
				'kn_score_position'=>'ಸೊನ್ನೆಗೆ ವಜಾಗೊಳಿಸು (ಬ್ಯಾಟ್ಸ್ಮನ್ಗಳು, ವಿಕೆಟ್ ಕೀಪರ್ ಮತ್ತು ಸರ್ವಾಂಗೀಣ)',
				),
				array(
				'meta_key'=>'EVERY_FOUR',
				'kn_score_position'=>'ಪ್ರತಿ ಗಡಿ ಹಿಟ್',
				),
				array(
				'meta_key'=>'EVERY_SIX',
				'kn_score_position'=>'ಪ್ರತಿ ಆರು ಹಿಟ್',
				),
				array(
				'meta_key'=>'HALF_CENTURY',
				'kn_score_position'=>'ಅರ್ಧಶತಕ',
				),
				array(
				'meta_key'=>'CENTURY',
				'kn_score_position'=>'ಸೆಂಚುರಿ',
				),
				array(
				'meta_key'=>'MAIDEN_OVER',
				'kn_score_position'=>'ಮೇಲೆ ಮೇಡನ್',
				),
				array(
				'meta_key'=>'FOUR_WICKET',
				'kn_score_position'=>'4 ವಿಕೆಟ್',
				),
				array(
				'meta_key'=>'FIVE_WICKET',
				'kn_score_position'=>'5 ವಿಕೆಟ್ಗಳಿಂದ',
				),
				array(
				'meta_key'=>'MINIMUM_BOWLING_OVER',
				'kn_score_position'=>'ಓವರ್ಗಳ ಯಾವುದೇ ನಮೂದಿಸಿ ಗಳಿಕೆಯ ನಿಯಮಗಳನ್ನು ಕೆಳಗಿನ ಇದಕ್ಕಾಗಿ ಅನ್ವಯವಾಗುತ್ತದೆ',
				),
				array(
				'meta_key'=>'BETWEEN_5_6',
				'kn_score_position'=>'ನಡುವೆ 6 ಮತ್ತು 5 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BETWEEN_4_499',
				'kn_score_position'=>'4.99 ನಡುವೆ ಮತ್ತು 4 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BELOW_4',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 4 ರನ್ ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'BETWEEN_9_10',
				'kn_score_position'=>'ನಡುವೆ 9 ಮತ್ತು 10 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BETWEEN_101_11',
				'kn_score_position'=>'10,01 ನಡುವೆ ಮತ್ತು 11 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'ABOVE_11',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 11 ರನ್ ಮೇಲೆ',
				),
				array(
				'meta_key'=>'BETWEEN_60_70',
				'kn_score_position'=>'60 ನಡುವೆ ಮತ್ತು 70 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BETWEEN_50_599',
				'kn_score_position'=>'ನಡುವೆ 50 ಮತ್ತು 59,9 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BELOW_50',
				'kn_score_position'=>'100 ಚೆಂಡುಗಳಿಗೆ 50 ರನ್ ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'MINUTES_55',
				'kn_score_position'=>'55 ನಿಮಿಷ ಅಥವಾ ಹೆಚ್ಚು ಆಡಿದ್ದು',
				),
				array(
				'meta_key'=>'MINUTES_BELOW_55',
				'kn_score_position'=>'ಆಡಿದ್ದು 55 ನಿಮಿಷಗಳಲ್ಲಿ',
				),
				array(
				'meta_key'=>'GOAL_GK_DF',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ಗೋಲು (ಗ್ರಾಮೀಣ ಕೂಟದ / ರಕ್ಷಕ)',
				),
				array(
				'meta_key'=>'GOAL_MF',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ಗೋಲು (ಮಧ್ಯಮೈದಾನದ ಆಟಗಾರ)',
				),
				array(
				'meta_key'=>'GOAL_FW',
				'kn_score_position'=>'ಗಳಿಸಿದರು ಪ್ರತಿ ಗೋಲು (ಫಾರ್ವರ್ಡ್)',
				),
				array(
				'meta_key'=>'GOAL_ASSIST',
				'kn_score_position'=>'ಪ್ರತಿ ಅಸಿಸ್ಟ್ ಫಾರ್',
				),
				array(
				'meta_key'=>'PASSES_COMPLETED',
				'kn_score_position'=>'ಪೂರ್ಣಗೊಂಡ ಪ್ರತಿ 20 ಪಾಸ್ಗಳು',
				),
				array(
				'meta_key'=>'SHOT_ON_TARGET',
				'kn_score_position'=>'ಪ್ರತಿ 2 ಗುರಿ ಹೊಡೆತಗಳನ್ನು',
				),
				array(
				'meta_key'=>'CLEAN_SHEET_MD',
				'kn_score_position'=>'ಕ್ಲೀನ್ ಶೀಟ್ (ಮಿಡ್ ಫೀಲ್ಡರ್) ಅವರು ಕನಿಷ್ಠ 55 ನಿಮಿಷಗಳ ವಹಿಸಿದೆ ಒದಗಿಸಿದ',
				),
				array(
				'meta_key'=>'CLEAN_SHEET_GK_DF',
				'kn_score_position'=>'ಕ್ಲೀನ್ ಶೀಟ್ (ಗ್ರಾಮೀಣ ಕೂಟದ / ರಕ್ಷಕ) ಅವರು ಕನಿಷ್ಠ 55 ನಿಮಿಷಗಳ ವಹಿಸಿದೆ ಒದಗಿಸಿದ',
				),
				array(
				'meta_key'=>'SAVES_GK',
				'kn_score_position'=>'ಪ್ರತಿ 3 ಹೊಡೆತಗಳನ್ನು ಉಳಿಸಿದ (ಗ್ರಾಮೀಣ ಕೂಟದ) ಫಾರ್',
				),
				array(
				'meta_key'=>'PENALTY_SAVE_GK',
				'kn_score_position'=>'ಉಳಿಸಿದ ಪ್ರತಿ ಪೆನಾಲ್ಟಿ (ಗ್ರಾಮೀಣ ಕೂಟದ) ಫಾರ್',
				),
				array(
				'meta_key'=>'TACKLE_WON',
				'kn_score_position'=>'ಮಾಡಿದ ಪ್ರತಿ 3 ಯಶಸ್ವಿ ಟ್ಯಾಕಲ್ಸ್',
				),
				array(
				'meta_key'=>'YELLOW_CARD',
				'kn_score_position'=>'ಹಳದಿ ಕಾರ್ಡ್',
				),
				array(
				'meta_key'=>'RED_CARD',
				'kn_score_position'=>'ರೆಡ್ ಕಾರ್ಡ್',
				),
				array(
				'meta_key'=>'OWN_GOALS',
				'kn_score_position'=>'ಪ್ರತಿ ಆದ ಗುರಿ',
				),
				array(
				'meta_key'=>'GOAL_2_CONCEDED_GK_DF',
				'kn_score_position'=>'ಪ್ರತಿ 2 ಗೋಲುಗಳನ್ನು ಬಿಟ್ಟುಕೊಟ್ಟಿತು ಫಾರ್ (ಗ್ರಾಮೀಣ ಕೂಟದ / ರಕ್ಷಕ)',
				),
				array(
				'meta_key'=>'PENALTY_MISS',
				'kn_score_position'=>'ತಪ್ಪಿದ ಪ್ರತಿ ಪೆನಾಲ್ಟಿ ಫಾರ್',
				),
				array(
				'meta_key'=>'STARTING_7',
				'kn_score_position'=>'ಆರಂಭಿಕ 7 ಪಾಲ್ಗೊಳ್ಳುವುದು',
				),
				array(
				'meta_key'=>'SUBSTITUTE',
				'kn_score_position'=>'ಪರ್ಯಾಯವಾಗಿ ಕಾಣಿಸಿಕೊಂಡ',
				),
				array(
				'meta_key'=>'SUCCESSFUL_RAID_TOUCH',
				'kn_score_position'=>'ಪ್ರತಿಯೊಂದು ಯಶಸ್ವೀ ದಾಳಿಯ ಸ್ಪರ್ಶವನ್ನು',
				),
				array(
				'meta_key'=>'RAID_BONUS',
				'kn_score_position'=>'ರೈಡ್ ಬೋನಸ್',
				),
				array(
				'meta_key'=>'SUCCESSFUL_TACKLE',
				'kn_score_position'=>'ಪ್ರತಿಯೊಂದು ಯಶಸ್ವೀ ನಿಭಾಯಿಸಲು',
				),
				array(
				'meta_key'=>'SUPER_TACKLE',
				'kn_score_position'=>'ಸೂಪರ್ ನಿಭಾಯಿಸಲು',
				),
				array(
				'meta_key'=>'PUSHING_ALL_OUT_7',
				'kn_score_position'=>'ಎಲ್ಲಾ ಔಟ್ ಪುಶಿಂಗ್ (ಆರಂಭಿಕ 7)',
				),
				array(
				'meta_key'=>'GETTING_ALL_OUT_7',
				'kn_score_position'=>'ಎಲ್ಲಾ ಹೊರಬರುವುದನ್ನು (ಆರಂಭಿಕ 7)',
				),
				array(
				'meta_key'=>'UNSUCCESSFUL_RAID',
				'kn_score_position'=>'ಪ್ರತಿ ವಿಫಲ ದಾಳಿ',
				),
				array(
				'meta_key'=>'GREEN_CARD',
				'kn_score_position'=>'ಹಸಿರು ಕಾರ್ಡ್',
				),
				array(
				'meta_key'=>'DOUBLE_EAGLE',
				'kn_score_position'=>'ಡಬಲ್ ಈಗಲ್',
				),
				array(
				'meta_key'=>'EAGLE',
				'kn_score_position'=>'ಈಗಲ್',
				),
				array(
				'meta_key'=>'BIRDIE',
				'kn_score_position'=>'ಬರ್ಡೀ',
				),
				array(
				'meta_key'=>'PAR',
				'kn_score_position'=>'ಪರ್',
				),
				array(
				'meta_key'=>'BOGEY',
				'kn_score_position'=>'ಬೋಗಿ',
				),
				array(
				'meta_key'=>'DOUBLE_BOGEY',
				'kn_score_position'=>'ಡಬಲ್ ಬೋಗಿ',
				),
				array(
				'meta_key'=>'WORSE_THAN_DOUBLE_BOGEY',
				'kn_score_position'=>'ಡಬಲ್ ಬೋಗಿ ಕೆಟ್ಟದಾಗಿದೆ',
				),
				array(
				'meta_key'=>'RANK_1',
				'kn_score_position'=>'ಶ್ರೇಣಿ 1',
				),
				array(
				'meta_key'=>'RANK_2',
				'kn_score_position'=>'ಶ್ರೇಣಿ 2',
				),
				array(
				'meta_key'=>'RANK_3',
				'kn_score_position'=>'ಶ್ರೇಣಿ 3',
				),
				array(
				'meta_key'=>'RANK_4',
				'kn_score_position'=>'ಶ್ರೇಣಿ 4',
				),
				array(
				'meta_key'=>'RANK_5',
				'kn_score_position'=>'ಶ್ರೇಣಿ 5',
				),
				array(
				'meta_key'=>'RANK_6',
				'kn_score_position'=>'ಶ್ರೇಣಿ 6',
				),
				array(
				'meta_key'=>'RANK_7',
				'kn_score_position'=>'ಶ್ರೇಣಿ 7',
				),
				array(
				'meta_key'=>'RANK_8',
				'kn_score_position'=>'ಶ್ರೇಣಿ 8',
				),
				array(
				'meta_key'=>'RANK_9',
				'kn_score_position'=>'ಶ್ರೇಣಿ 9',
				),
				array(
				'meta_key'=>'RANK_10',
				'kn_score_position'=>'ಶ್ರೇಣಿ 10',
				),
				array(
				'meta_key'=>'RANK_11_15',
				'kn_score_position'=>'ಶ್ರೇಣಿ 11-15',
				),
				array(
				'meta_key'=>'RANK_16_20',
				'kn_score_position'=>'ಶ್ರೇಣಿ 16-20',
				),
				array(
				'meta_key'=>'RANK_21_25',
				'kn_score_position'=>'ಶ್ರೇಣಿ 21-25',
				),
				array(
				'meta_key'=>'RANK_26_30',
				'kn_score_position'=>'ಶ್ರೇಣಿ 26-30',
				),
				array(
				'meta_key'=>'RANK_31_40',
				'kn_score_position'=>'ಶ್ರೇಣಿ 31-40',
				),
				array(
				'meta_key'=>'RANK_41_50',
				'kn_score_position'=>'ಶ್ರೇಣಿ 41-50',
				),
				array(
				'meta_key'=>'STREAK_OF_3_BIRDIES_OF_BETTER',
				'kn_score_position'=>'ಉತ್ತಮ 3 ಬರ್ಡಿಗಳು ಆಫ್ ಸ್ಟ್ರೀಕ್',
				),
				array(
				'meta_key'=>'BOGEY_FREE_ROUND',
				'kn_score_position'=>'ಬೋಗಿ ಉಚಿತ ರೌಂಡ್',
				),
				array(
				'meta_key'=>'ALL_4_ROUNDS_UNDER_70_STROKES',
				'kn_score_position'=>'70 ಹೊಡೆತಗಳು ಅಡಿಯಲ್ಲಿ ಎಲ್ಲಾ 4 ರೌಂಡ್ಸ್',
				),
				array(
				'meta_key'=>'HOLE_IN_ONE',
				'kn_score_position'=>'ಒನ್ ರಂಧ್ರ',
				),
				array(
				'meta_key'=>'STARTING_BONUS',
				'kn_score_position'=>'ಬೋನಸ್ ಆರಂಭಗೊಂಡು',
				),
				array(
				'meta_key'=>'SINGLES',
				'kn_score_position'=>'ಪ್ರತಿಯೊಂದು ಬಿಂದು ಗಳಿಸಿದರೆ - ಸಿಂಗಲ್ಸ್',
				),
				array(
				'meta_key'=>'DOUBLES',
				'kn_score_position'=>'ಪ್ರತಿಯೊಂದು ಬಿಂದು ಗಳಿಸಿದರೆ - ಡಬಲ್ಸ್',
				),
				array(
				'meta_key'=>'TRUMP_MATCH',
				'kn_score_position'=>'ಟ್ರಂಪ್ ಹೊಂದಿಕೆ',
				),
				array(
				'meta_key'=>'CAPTAIN',
				'kn_score_position'=>'ಕ್ಯಾಪ್ಟನ್ (ಸ್ಕೋರ್ ಎಕ್ಸ್ 1.5)',
				),
				array(
				'meta_key'=>'REBOUNDS',
				'kn_score_position'=>'ರೀಬೌಂಡ್ಗಳು',
				),
				array(
				'meta_key'=>'ASSISTS',
				'kn_score_position'=>'ನೆರವುಗಳು',
				),
				array(
				'meta_key'=>'BLOCKED_SHOT',
				'kn_score_position'=>'ನಿರ್ಬಂಧಿಸಿದ ಹೊಡೆತಗಳು',
				),
				array(
				'meta_key'=>'STEALS',
				'kn_score_position'=>'ಸ್ಟೀಲ್ಸ್',
				),
				array(
				'meta_key'=>'TURNOVERS',
				'kn_score_position'=>'turnovers',
				),
				array(
				'meta_key'=>'EACH_POINT',
				'kn_score_position'=>'ಪ್ರತಿ ಪಾಯಿಂಟ್',
				),
				array(
				'meta_key'=>'PASSING_YARDS',
				'kn_score_position'=>'25 ಗಜ ಪ್ರತಿ Yards- ಹಾದುಹೋಗುವ',
				),
				array(
				'meta_key'=>'PASSING_TOUCHDOWNS',
				'kn_score_position'=>'ಟಚ್ಡೌನ್ಗಳನ್ನು ಹಾದುಹೋಗುವ',
				),
				array(
				'meta_key'=>'PASSING_INTERCEPTIONS',
				'kn_score_position'=>'ಇಂಟರ್ಸೆಪ್ಶನ್ಗಳು ಹಾದುಹೋಗುವ',
				),
				array(
				'meta_key'=>'RUSHING_YARDS',
				'kn_score_position'=>'10 ಗಜಗಳಷ್ಟು ಪ್ರತಿ Yards- ನುಗ್ಗುತ್ತಿರುವ',
				),
				array(
				'meta_key'=>'RUSHING_TOUCHDOWNS',
				'kn_score_position'=>'ಟಚ್ಡೌನ್ಗಳನ್ನು ನುಗ್ಗುತ್ತಿರುವ',
				),
				array(
				'meta_key'=>'RECEPTIONS',
				'kn_score_position'=>'ಸತ್ಕಾರಕೂಟ (ಕೇವಲ ಪಿಪಿಆರ್ ಅಂಕ ಬಳಸಿ ವೇಳೆ)',
				),
				array(
				'meta_key'=>'RECEIVING_YARDS',
				'kn_score_position'=>'10 ಗಜಗಳಷ್ಟು ಪ್ರತಿ Yards- ಪಡೆದುಕೊಳ್ಳುವುದು',
				),
				array(
				'meta_key'=>'RECEIVING_TOUCHDOWNS',
				'kn_score_position'=>'ಟಚ್ಡೌನ್ಗಳನ್ನು ಪಡೆದುಕೊಳ್ಳುವುದು',
				),
				array(
				'meta_key'=>'FUMBLES_LOST',
				'kn_score_position'=>'ಫಂಬ್ಲೆಸ್ಗಳನ್ನು ಲಾಸ್ಟ್',
				),
				array(
				'meta_key'=>'DEFENSE_SACK',
				'kn_score_position'=>'ಸ್ಯಾಕ್ಸ್',
				),
				array(
				'meta_key'=>'DEFENSE_INTERCEPTIONS',
				'kn_score_position'=>'ಇಂಟರ್ಸೆಪ್ಶನ್ಗಳು',
				),
				array(
				'meta_key'=>'DEFENSE_FUMBLES_RECOVERED',
				'kn_score_position'=>'ಫಂಬ್ಲೆಸ್ಗಳನ್ನು ರಿಕವರ್ಡ್',
				),
				array(
				'meta_key'=>'DEFENSE_SAFETIES',
				'kn_score_position'=>'ಸುರಕ್ಷತೆಗಳು',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_0',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (0)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_1_6',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (1-6)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_7_13',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (7-13)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_14_20',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (14-20)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_21_27',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (21-27)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_28_34',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (28-34)',
				),
				array(
				'meta_key'=>'DEFENSE_POINTS_ALLOWED_35plus',
				'kn_score_position'=>'ಪಾಯಿಂಟುಗಳು ಅನುಮತಿಸಲಾಗಿದೆ (35 +)',
				),
				array(
				'meta_key'=>'DEFENSE_KICK_RETURN_TOUCHDOWNS',
				'kn_score_position'=>'ರಿಟರ್ನ್ ಟಚ್ಡೌನ್ಗಳನ್ನು ಕಿಕ್',
				),
				array(
				'meta_key'=>'DEFENSE_PUNT_RETURN_TOUCHDOWNS',
				'kn_score_position'=>'ಓಡ ರಿಟರ್ನ್ ಟಚ್ಡೌನ್ಗಳನ್ನು',
				),
				array(
				'meta_key'=>'INNING_PITCHED',
				'kn_score_position'=>'ಇನ್ನಿಂಗ್ಸ್ ಪಿಚ್',
				),
				array(
				'meta_key'=>'EARNED_RUNS_ALLOWED',
				'kn_score_position'=>'ಗಳಿಸಿದ ರನ್ಗಳು ಅನುಮತಿಸಲಾಗಿದೆ',
				),
				array(
				'meta_key'=>'WALKS',
				'kn_score_position'=>'ವಾಕ್ಸ್',
				),
				array(
				'meta_key'=>'WINS',
				'kn_score_position'=>'ಗೆಲುವುಗಳು',
				),
				array(
				'meta_key'=>'SAVES',
				'kn_score_position'=>'ಉಳಿತಾಯ',
				),
				array(
				'meta_key'=>'HOME_RUN',
				'kn_score_position'=>'ಮುಖಪುಟ ರನ್ಗಳು',
				),
				array(
				'meta_key'=>'RUNS',
				'kn_score_position'=>'ರನ್',
				),
				array(
				'meta_key'=>'STRIKE_OUTS',
				'kn_score_position'=>'ಸ್ಟ್ರೈಕ್ ಇರಿಸು',
				),
				array(
				'meta_key'=>'HIT_BATSMAN',
				'kn_score_position'=>'ಬ್ಯಾಟ್ಸ್ಮನ್ ಹಿಟ್',
				),
				array(
				'meta_key'=>'SINGLE',
				'kn_score_position'=>'ಏಕ',
				),
				array(
				'meta_key'=>'DOUBLE',
				'kn_score_position'=>'ಡಬಲ್',
				),
				array(
				'meta_key'=>'TRIPLES',
				'kn_score_position'=>'ಮೂರು',
				),
				array(
				'meta_key'=>'RUNS_BATTED_IN',
				'kn_score_position'=>'ಬ್ಯಾಟ್ ರನ್ನುಗಳು',
				),
				array(
				'meta_key'=>'STOLEN_BASES',
				'kn_score_position'=>'ಸ್ಟೋಲನ್ ಬೇಸಸ್',
				),
				array(
				'meta_key'=>'HIT_BY_PITCH',
				'kn_score_position'=>'ಪಿಚ್ ಮೂಲಕ ಹಿಟ್',
				),
				array(
				'meta_key'=>'CAUGHT_STEALING',
				'kn_score_position'=>'ಕಾಟ್ ಸ್ಟೀಲಿಂಗ್',
				),
				array(
				'meta_key'=>'CAUGHT_N_BOWLED',
				'kn_score_position'=>'ಕ್ಯಾಚ್ & ಬೌಲ್ಡ್',
				),
				array(
				'meta_key'=>'TWO_POINT_CONVERSIONS',
				'kn_score_position'=>'2-ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆಗಳು',
				),
				array(
				'meta_key'=>'DEFENSE_DEFENSIVE_TOUCHDOWNS',
				'kn_score_position'=>'ರಕ್ಷಣಾತ್ಮಕ ಟಚ್ಡೌನ್ಗಳನ್ನು',
				),
				array(
				'meta_key'=>'DEFENSE_TWO_POINT_CONVERSION_RETURNS',
				'kn_score_position'=>'2-ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆ ರಿಟರ್ನ್ಸ್',
				),
				array(
				'meta_key'=>'FG_MADE_0_49',
				'kn_score_position'=>'ಎಫ್ಜಿ ಮೇಡ್ (0-49 ಗಜಗಳಷ್ಟು)',
				),
				array(
				'meta_key'=>'FG_MADE_50PLUS',
				'kn_score_position'=>'ಎಫ್ಜಿ ಮೇಡ್ (50 + ಗಜಗಳಷ್ಟು)',
				),
				array(
				'meta_key'=>'THREE_POINTERS_MADE',
				'kn_score_position'=>'ಮೂರು ಪಾಯಿಂಟ್ ಫೀಲ್ಡ್ ಗೋಲುಗಳು',
				),
				array(
				'meta_key'=>'TWO_POINTERS_MADE',
				'kn_score_position'=>'ಎರಡು ಪಾಯಿಂಟ್ ಫೀಲ್ಡ್ ಗೋಲುಗಳು.',
				),
				array(
				'meta_key'=>'FREE_THROWS_MADE',
				'kn_score_position'=>'ಉಚಿತ ಮೇಡ್ ಹೊಡೆತಗಳು',
				),
				array(
				'meta_key'=>'EXTRA_POINTS_MADE',
				'kn_score_position'=>'ಎಕ್ಸ್ಟ್ರಾ ಪಾಯಿಂಟ್ ಮೇಡ್',
				),
				array(
				'meta_key'=>'FUMBLES_RECOVERED',
				'kn_score_position'=>'ಫಂಬ್ಲೆಸ್ಗಳನ್ನು ರಿಕವರ್ಡ್',
				),
				array(
				'meta_key'=>'FUMBLES_INTERCEPTIONS',
				'kn_score_position'=>'ಫಂಬ್ಲೆಸ್ಗಳನ್ನು ಪ್ರತಿಬಂಧ',
				),
				array(
				'meta_key'=>'THIRTY_RUN',
				'kn_score_position'=>'30 ರನ್ ಬೋನಸ್',
				),
				array(
				'meta_key'=>'TWO_WICKET',
				'kn_score_position'=>'2 ವಿಕೆಟ್ಗಳನ್ನು',
				),
				array(
				'meta_key'=>'THREE_WICKET',
				'kn_score_position'=>'3 ವಿಕೆಟ್',
				),
				array(
				'meta_key'=>'BELOW_6',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 6 ರನ್ಗಳು ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'BETWEEN_6_699',
				'kn_score_position'=>'ನಡುವೆ 6 ಮತ್ತು 6.99 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'BETWEEN_11_12',
				'kn_score_position'=>'11 ನಡುವೆ ಮತ್ತು ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು 12',
				),
				array(
				'meta_key'=>'BETWEEN_121_13',
				'kn_score_position'=>'12.01 ನಡುವೆ ಮತ್ತು 13 ಪ್ರತಿ ಓವರ್ಗೆ ಗಳಿಸುವ ಓಟಗಳು',
				),
				array(
				'meta_key'=>'ABOVE_13',
				'kn_score_position'=>'ಪ್ರತಿ ಓವರ್ಗೆ 13 ರನ್ ಮೇಲೆ',
				),
				array(
				'meta_key'=>'BETWEEN_90_999',
				'kn_score_position'=>'90 ನಡುವೆ ಮತ್ತು 99.9 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BETWEEN_80_899',
				'kn_score_position'=>'80 ನಡುವೆ ಮತ್ತು 89.9 100 ಚೆಂಡುಗಳಿಗೆ ರನ್',
				),
				array(
				'meta_key'=>'BELOW_80',
				'kn_score_position'=>'100 ಚೆಂಡುಗಳಿಗೆ 80 ರನ್ ಕೆಳಗೆ',
				),
				array(
				'meta_key'=>'FIELD_GOALS_MISSED',
				'kn_score_position'=>'ತಪ್ಪಿದ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'FREE_THROWS_MISSED',
				'kn_score_position'=>'ತಪ್ಪಿದ ಎಫ್ಟಿ',
				),
				array(
				'meta_key'=>'PASSING_TWO_POINT',
				'kn_score_position'=>'2-ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆಗಳು ಹಾದುಹೋಗುವ',
				),
				array(
				'meta_key'=>'RUSHING_TWO_POINT',
				'kn_score_position'=>'2-ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆಗಳು ನುಗ್ಗುತ್ತಿರುವ',
				),
				array(
				'meta_key'=>'RECEVING_TWO_POINT',
				'kn_score_position'=>'2-ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆಗಳು Receving',
				),
				array(
				'meta_key'=>'KICK_RETURN_TOUCHDOWNS',
				'kn_score_position'=>'ರಿಟರ್ನ್ ಟಚ್ಡೌನ್ಗಳನ್ನು ಕಿಕ್',
				),
				array(
				'meta_key'=>'PUNT_RETURN_TOUCHDOWNS',
				'kn_score_position'=>'ಓಡ ರಿಟರ್ನ್ ಟಚ್ಡೌನ್ಗಳನ್ನು',
				),
				array(
				'meta_key'=>'DEFENSE_TOUCHDOWNS',
				'kn_score_position'=>'ರಕ್ಷಣಾತ್ಮಕ ಟಚ್ಡೌನ್ಗಳನ್ನು',
				),
				array(
				'meta_key'=>'FUMBLE_RECOVERY_TOUCHDOWNS',
				'kn_score_position'=>'ಆಕ್ರಮಣಕಾರಿ fumble ರಿಕವರಿ ಟಿಡಿ',
				),
				array(
				'meta_key'=>'KICKER_EXTRA_PT_MADE',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ ಎಕ್ಸ್ಟ್ರಾ ಪಾರ್ಟ್ ಮೇಡ್',
				),
				array(
				'meta_key'=>'DEFENSE_FUMBLES_RECOVERY_TD',
				'kn_score_position'=>'ರಕ್ಷಣಾ fumble ರಿಕವರಿ ಟಿಡಿ',
				),
				array(
				'meta_key'=>'KICKER_FIELD_GOAL_BLOCKED',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ ಫೀಲ್ಡ್ ಗೋಲ್ ತಪ್ಪಿದ / ನಿರ್ಬಂಧಿಸಿದ',
				),
				array(
				'meta_key'=>'KICKER_EXTRA_PT_BLOCKED',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ ಎಕ್ಸ್ಟ್ರಾ ಪಾರ್ಟ್ ತಪ್ಪಿದ / ನಿರ್ಬಂಧಿಸಿದ',
				),
				array(
				'meta_key'=>'KICKER_FG_0_19',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ 0-19 ಯಾರ್ಡ್ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'KICKER_FG_20_29',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ 20-29 ಯಾರ್ಡ್ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'KICKER_FG_30_39',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ 30-39 ಯಾರ್ಡ್ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'KICKER_FG_40_49',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ 40-49 ಯಾರ್ಡ್ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'KICKER_FG_50PLUS',
				'kn_score_position'=>'ಫುಸ್ಬಾಲ್ 50 + ಯಾರ್ಡ್ ಎಫ್ಜಿ',
				),
				array(
				'meta_key'=>'DEFENSE_DEFAULT_POINTS',
				'kn_score_position'=>'ರಕ್ಷಣಾತ್ಮಕ ಡೀಫಾಲ್ಟ್ ಪಾಯಿಂಟುಗಳು',
				),
		);

		$this->db->update_batch(MASTER_SCORING_RULES,$master_rule_data,'meta_key');

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'kn_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'kn_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'kn_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'kn_display_name');
	}

}
