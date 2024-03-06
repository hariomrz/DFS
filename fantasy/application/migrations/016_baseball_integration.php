<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Baseball_integration extends CI_Migration {

  public function up()
  {

    //up script for lineup master position
    $this->db->delete(MASTER_LINEUP_POSITION,array("sports_id" =>BASEBALL_SPORTS_ID));

    $master_lineup_position_arr = array(
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => '1B',
              'position_display_name'   => "1st Base",
              'number_of_players'       => 1,
              'position_order'          => 1,
              'max_player_per_position' => 1,
              'allowed_position'        => "1B",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => '2B',
              'position_display_name'   => "2nd Base",
              'number_of_players'       => 1,
              'position_order'          => 2,
              'max_player_per_position' => 1,
              'allowed_position'        => "2B",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => '3B',
              'position_display_name'   => "3rd Base",
              'number_of_players'       => 1,
              'position_order'          => 3,
              'max_player_per_position' => 1,
              'allowed_position'        => "3B",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => 'C',
              'position_display_name'   => "Catcher",
              'number_of_players'       => 1,
              'position_order'          => 4,
              'max_player_per_position' => 1,
              'allowed_position'        => "C",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => 'OF',
              'position_display_name'   => "Outfielder",
              'number_of_players'       => 1,
              'position_order'          => 5,
              'max_player_per_position' => 5,
              'allowed_position'        => "OF",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => 'P',
              'position_display_name'   => "Pitcher",
              'number_of_players'       => 1,
              'position_order'          => 6,
              'max_player_per_position' => 1,
              'allowed_position'        => "P",
              'is_pbl_position'         => 0
          ),
          array(
              'sports_id'               => BASEBALL_SPORTS_ID,
              'position_name'           => 'SS',
              'position_display_name'   => "Shortstop",
              'number_of_players'       => 1,
              'position_order'          => 7,
              'max_player_per_position' => 5,
              'allowed_position'        => "SS",
              'is_pbl_position'         => 0
          )
      );
    $this->db->insert_batch(MASTER_LINEUP_POSITION,$master_lineup_position_arr);


    //up script for game statistics baseball
    $this->dbforge->add_field(array(
      'league_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'season_game_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'week' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'scheduled' => array(
        'type' => 'VARCHAR',
        'constraint' => 500,
        'null' => FALSE
      ),
      'scheduled_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      ),
      'home_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'away_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'status' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => FALSE
      ),
      'inning' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'team_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'player_uid' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE
      ),
      'position' => array(
        'type' => 'VARCHAR',
        'constraint' => 20,
        'null' => FALSE
      ),
      'fantasy_points' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'scoring_type' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => FALSE
      ),
      'runs' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'singles' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'doubles' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'triples' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'home_runs' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'runs_batted_in' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'strike_outs' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'walks' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'hits' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'hbp' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'hit_by_pitch' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'stolen_bases' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'caught_stealing' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'win' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'saves' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'innings_pitched' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'earned_runs' => array(
        'type' => 'FLOAT',
        'null' => FALSE,
        'default' => 0
      ),
      'away_team_runs' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'home_team_runs' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'away_team_hits' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'home_team_hits' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'away_team_errors' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'home_team_errors' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'updated_at' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL
      )
      
    ));
    
    $this->dbforge->add_key('league_id', TRUE);
    $this->dbforge->add_key('season_game_uid', TRUE);
    $this->dbforge->add_key('player_uid', TRUE);
    $this->dbforge->add_key('scoring_type', TRUE);
    $this->dbforge->create_table(GAME_STATISTICS_BASEBALL);

    //up script for baseball scoring category translation
    $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." SET `hi_scoring_category_name` = 'पिचिंग', `guj_scoring_category_name` = 'પિચીંગ', `fr_scoring_category_name` = 'tangage', `ben_scoring_category_name` = 'নিক্ষেপ', `pun_scoring_category_name` = 'ਪਿਚਿੰਗ' WHERE `master_scoring_category_id` = 21";
      $this->db->query($sql);

      $sql = "UPDATE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." SET `hi_scoring_category_name` = 'मार', `guj_scoring_category_name` = 'પહિટ', `fr_scoring_category_name` = 'frappe', `ben_scoring_category_name` = 'আঘাত', `pun_scoring_category_name` = 'ਮਾਰਨਾ' WHERE `master_scoring_category_id` = 22";
      $this->db->query($sql);
  	
    //up script for master scoring rules.
    $this->db->where_in('master_scoring_category_id',array(21,22));
    $this->db->delete(MASTER_SCORING_RULES);

    $master_scoring_rules_arr = array(
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Innings Pitched",
              'en_score_position'           => "Innings Pitched",
              'hi_score_position'           => "इनिंग्स पिच हुईं",
              'guj_score_position'          => "ઇનિંગ્સ પિચ્ડ",
              'fr_score_position'           => "Innings Pitched",
              'ben_score_position'          => "ইননিংস পিচড",
              'pun_score_position'          => "ਇਨਨਿੰਗਜ਼ ਪਿਟਡ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "INNING_PITCHED",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Earned Runs Allowed",
              'en_score_position'           => "Earned Runs Allowed",
              'hi_score_position'           => "अर्जित रन अनुमति है",
              'guj_score_position'          => "કમાયેલ રનને મંજૂરી છે",
              'fr_score_position'           => "Runs gagnés autorisés",
              'ben_score_position'          => "অর্জিত রান অনুমোদিত",
              'pun_score_position'          => "ਕਮਾਈ ਗਈ ਰਨ ਦੀ ਆਗਿਆ ਹੈ",
              'score_points'                => -3,
              'points_unit'                 => 0,
              'meta_key'                    => "EARNED_RUNS_ALLOWED",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Walks",
              'en_score_position'           => "Walks",
              'hi_score_position'           => "सैर",
              'guj_score_position'          => "ચાલે છે",
              'fr_score_position'           => "Des promenades",
              'ben_score_position'          => "পদচারনা",
              'pun_score_position'          => "ਚਲਦਾ ਹੈ",
              'score_points'                => -1,
              'points_unit'                 => 0,
              'meta_key'                    => "WALKS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Wins",
              'en_score_position'           => "Wins",
              'hi_score_position'           => "जीत",
              'guj_score_position'          => "જીતે",
              'fr_score_position'           => "Victoires",
              'ben_score_position'          => "জেতা",
              'pun_score_position'          => "ਜਿੱਤੇ",
              'score_points'                => 4,
              'points_unit'                 => 0,
              'meta_key'                    => "WINS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Saves",
              'en_score_position'           => "Saves",
              'hi_score_position'           => "बचाता है",
              'guj_score_position'          => "બચાવે છે",
              'fr_score_position'           => "Sauvegarde",
              'ben_score_position'          => "সংরক্ষণ",
              'pun_score_position'          => "ਬਚਾਉਂਦਾ ਹੈ",
              'score_points'                => 2,
              'points_unit'                 => 0,
              'meta_key'                    => "SAVES",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Home Runs",
              'en_score_position'           => "Home Runs",
              'hi_score_position'           => "होम रन",
              'guj_score_position'          => "ઘર ચલાવો",
              'fr_score_position'           => "Home Runs",
              'ben_score_position'          => "হোম রান",
              'pun_score_position'          => "ਘਰ ਦੌੜ",
              'score_points'                => 4,
              'points_unit'                 => 0,
              'meta_key'                    => "HOME_RUN",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Runs",
              'en_score_position'           => "Runs",
              'hi_score_position'           => "रन",
              'guj_score_position'          => "ચાલે છે",
              'fr_score_position'           => "Runs",
              'ben_score_position'          => "রান",
              'pun_score_position'          => "ਚਲਦਾ ਹੈ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "RUNS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Strike Outs",
              'en_score_position'           => "Strike Outs",
              'hi_score_position'           => "स्ट्राइक आउट",
              'guj_score_position'          => "સ્ટ્રાઈક આઉટ્સ",
              'fr_score_position'           => "Sorties de grève",
              'ben_score_position'          => "স্ট্রাইক রাখুন",
              'pun_score_position'          => "ਹੜਤਾਲ ਨਾਕਆਊਟ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "STRIKE_OUTS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 21,
              'format'                      => 1,
              'score_position'              => "Hit Batsman",
              'en_score_position'           => "Hit Batsman",
              'hi_score_position'           => "हिट बल्लेबाज",
              'guj_score_position'          => "હિટ બેટ્સમેન",
              'fr_score_position'           => "Hit batteur",
              'ben_score_position'          => "হিট ব্যাটসম্যান",
              'pun_score_position'          => "ਹਿੱਟ ਬੱਲੇਬਾਜ਼",
              'score_points'                => -1,
              'points_unit'                 => 0,
              'meta_key'                    => "HIT_BATSMAN",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Single",
              'en_score_position'           => "Single",
              'hi_score_position'           => "एक",
              'guj_score_position'          => "એકલુ",
              'fr_score_position'           => "Célibataire",
              'ben_score_position'          => "একক",
              'pun_score_position'          => "ਸਿੰਗਲ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "SINGLE",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Double",
              'en_score_position'           => "Double",
              'hi_score_position'           => "दोहरा",
              'guj_score_position'          => "ડબલ",
              'fr_score_position'           => "Double",
              'ben_score_position'          => "ডবল",
              'pun_score_position'          => "ਡਬਲ",
              'score_points'                => 2,
              'points_unit'                 => 0,
              'meta_key'                    => "DOUBLE",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Triples",
              'en_score_position'           => "Triples",
              'hi_score_position'           => "ट्रिपल",
              'guj_score_position'          => "ટ્રીપલ",
              'fr_score_position'           => "triples",
              'ben_score_position'          => "ট্রিপল",
              'pun_score_position'          => "ਟ੍ਰਿਪਲ",
              'score_points'                => 3,
              'points_unit'                 => 0,
              'meta_key'                    => "TRIPLES",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Home Runs",
              'en_score_position'           => "Home Runs",
              'hi_score_position'           => "होम रन",
              'guj_score_position'          => "ઘર રન",
              'fr_score_position'           => "Coups de circuit",
              'ben_score_position'          => "হোম রান",
              'pun_score_position'          => "ਮੁੱਖ ਰਨ",
              'score_points'                => 4,
              'points_unit'                 => 0,
              'meta_key'                    => "HOME_RUN",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Runs Batted In",
              'en_score_position'           => "Runs Batted In",
              'hi_score_position'           => "रन बेटेड इन",
              'guj_score_position'          => "ચાલે બેટિંગ માં",
              'fr_score_position'           => "Points produits",
              'ben_score_position'          => "চালায় ব্যাটিং ইন",
              'pun_score_position'          => "ਚੱਲਦਾ ਬੱਲੇਬਾਜ਼ੀ ਵਿੱਚ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "RUNS_BATTED_IN",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Runs",
              'en_score_position'           => "Runs",
              'hi_score_position'           => "रन",
              'guj_score_position'          => "રન",
              'fr_score_position'           => "runs",
              'ben_score_position'          => "রান",
              'pun_score_position'          => "ਰਨ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "RUNS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Walks",
              'en_score_position'           => "Walks",
              'hi_score_position'           => "सैर",
              'guj_score_position'          => "ચાલે છે",
              'fr_score_position'           => "Des promenades",
              'ben_score_position'          => "পদচারনা",
              'pun_score_position'          => "ਚਲਦਾ ਹੈ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "WALKS",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Stolen Bases",
              'en_score_position'           => "Stolen Bases",
              'hi_score_position'           => "स्टोलन बेसेस",
              'guj_score_position'          => "સ્ટોલન મથક",
              'fr_score_position'           => "bases volés",
              'ben_score_position'          => "হৃত ঘাঁটি",
              'pun_score_position'          => "ਚੋਰੀ ਦਾ ਠਿਕਾਣਾ ਪ੍ਰਦਾਨ ਕਰਦਾ ਹੈ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "STOLEN_BASES",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Hit By Pitch",
              'en_score_position'           => "Hit By Pitch",
              'hi_score_position'           => "हिट बाय पिच",
              'guj_score_position'          => "પિચ દ્વારા હિટ",
              'fr_score_position'           => "Hit par la hauteur",
              'ben_score_position'          => "পিচ দ্বারা হিট",
              'pun_score_position'          => "ਪਿੱਚ ਕੇ ਹਿੱਟ",
              'score_points'                => 1,
              'points_unit'                 => 0,
              'meta_key'                    => "HIT_BY_PITCH",
              'meta_key_alias'              => ""
          ),
          array(
              'master_scoring_category_id'  => 22,
              'format'                      => 1,
              'score_position'              => "Caught Stealing",
              'en_score_position'           => "Caught Stealing",
              'hi_score_position'           => "कॉट स्टीलिंग",
              'guj_score_position'          => "કેચ સ્ટિલિંગ",
              'fr_score_position'           => "Surpris en train de voler",
              'ben_score_position'          => "চুরি করতে ধরা পরেছিল",
              'pun_score_position'          => "ਫੜਿਆ ਚੋਰੀ",
              'score_points'                => -1,
              'points_unit'                 => 0,
              'meta_key'                    => "CAUGHT_STEALING",
              'meta_key_alias'              => ""
          )
          
      );
    $this->db->insert_batch(MASTER_SCORING_RULES,$master_scoring_rules_arr);


  }

  public function down()
  {
    //down script for master lineup position
    $this->db->delete(MASTER_LINEUP_POSITION,array("sports_id" =>BASEBALL_SPORTS_ID));

    //down script for game statistics baseball
    $this->dbforge->drop_table(GAME_STATISTICS_BASEBALL);

    //down script for master scoring rules 
    $this->db->where_in('master_scoring_category_id',array(21,22));
    $this->db->delete(MASTER_SCORING_RULES);
	   
  }
}