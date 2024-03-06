<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sports_tennis extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    $fields = array(
              'no_of_sets' => array(
                'type' => 'TINYINT',
              'constraint' => 1,
              'default' => 3,
              'comment' => "Only for Tennis, 5=>Grand Slams,3=>All other"
              )
      );
      if(!$this->db->field_exists('no_of_sets', LEAGUE)){
        $this->dbforge->add_column(LEAGUE,$fields);
      }

    $sql = "DELETE FROM ".$this->db->dbprefix(MASTER_LINEUP_POSITION)." WHERE sports_id=".TENNIS_SPORTS_ID;
    $this->db->query($sql);

    $result = $this->db->select('*')->from(MASTER_LINEUP_POSITION)->where('sports_id',TENNIS_SPORTS_ID)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
                    array(
                          'sports_id'             => TENNIS_SPORTS_ID,
                          'position_name'         => 'ALL',
                          'position_display_name'  => 'All',
                          'number_of_players'     => '1',
                          'position_order'        => '1',
                          'max_player_per_position'  => '8',
                          'allowed_position'        => 'ALL'
                      ),
                      
                    );
      $this->db->insert_batch(MASTER_LINEUP_POSITION,$data_arr);
    }

      $fields = array(
              'rank' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => FALSE
              ),
              'points' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => FALSE
              )
      );
      if(!$this->db->field_exists('rank', PLAYER)){
        $this->dbforge->add_column(PLAYER,$fields);
      }

      $sql = "ALTER TABLE ".$this->db->dbprefix(PLAYER)." CHANGE `country` `country` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
      $this->db->query($sql);

      if(!$this->db->table_exists(SEASON_MATCH))
      {
        $fields = array(
          'season_match_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'auto_increment' => TRUE,
          ),
          'season_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'match_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'home_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'comment' => 'Player id value from player table'
          ),
          'away_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'comment' => 'Player id value from player table'
          ),
          'scheduled_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE,
            'default' => NULL,
          ),
          'score' => array(
            'type' => 'json',
            'null' => TRUE,
            'default' => NULL,
          ),
          'deleted' => array(
              'type' => 'TINYINT',
              'constraint' => 1,
              'default' => 1,
              'comment' => "1=>Deleted"
            )  
        );

        $attributes = array('ENGINE'=>'InnoDB');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('season_match_id',TRUE);
        $this->dbforge->create_table(SEASON_MATCH,FALSE,$attributes);

        //add unique key
        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON_MATCH)." ADD UNIQUE KEY season_match(season_id,match_id);";
        $this->db->query($sql);


        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON_MATCH)." ADD `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-Not Started, 1-Live, 2-Completed, 3-Delay, 4-Canceled,5-Retired' AFTER `scheduled_date`;";        
        $this->db->query($sql);

        //add foriegn key
        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON_MATCH)." ADD FOREIGN KEY (season_id) REFERENCES vi_season(season_id) ON DELETE CASCADE ON UPDATE CASCADE;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON_MATCH)." ADD FOREIGN KEY (home_id) REFERENCES vi_player(player_id) ON DELETE CASCADE ON UPDATE CASCADE;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(SEASON_MATCH)." ADD FOREIGN KEY (away_id) REFERENCES vi_player(player_id) ON DELETE CASCADE ON UPDATE CASCADE;";
        $this->db->query($sql);
      }

      $fields = array(
              'setting' => array(
                'type' => 'JSON',
                'null' => TRUE,
                'default' => NULL,
                'comment' => 'Create team criteria setting'
              )
      );
      if(!$this->db->field_exists('setting', COLLECTION_MASTER)){
        $this->dbforge->add_column(COLLECTION_MASTER,$fields);
      }

      //statistics table
    if(!$this->db->table_exists(GAME_STATISTICS_TENNIS))
    {
        $fields = array(
          'league_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'season_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'season_match_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'team_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'player_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'scheduled_date' => array(
              'type' => 'DATETIME',
              'default' => NULL,
              'null' => FALSE
          ),                
          's1' => array(
            'type' => 'FLOAT',
            'null' => FALSE,
            'default' => 0,
          ),                
          's2' => array(
            'type' => 'FLOAT',
            'null' => FALSE,
            'default' => 0,
          ),                
          's3' => array(
            'type' => 'FLOAT',
            'null' => FALSE,
            'default' => 0,
          ),                
          's4' => array(
            'type' => 'FLOAT',
            'null' => FALSE,
            'default' => 0,
          ),                
          's5' => array(
            'type' => 'FLOAT',
            'null' => FALSE,
            'default' => 0,
          ),                
          'service_aces' => array(
            'type' => 'INT',
            'null' => FALSE,
            'default' => 0,
            'comment' => 'Aces value'
          ),                
          'service_df' => array(
            'type' => 'INT',
            'null' => FALSE,
            'default' => 0,
            'comment' => 'Double Faults value'
          ),
          'total_score' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
          ),
          'winner' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'comment' => "1=>Winner"
          ),
          'updated_at' => array(
            'type' => 'VARCHAR',
            'constraint' => 25,
            'null' => FALSE
          )
      );

      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->create_table(GAME_STATISTICS_TENNIS,FALSE,$attributes);

      //add unique key
      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_TENNIS)." ADD UNIQUE KEY module_type(league_id,season_id,season_match_id,player_id);";
      $this->db->query($sql);

      //add foriegn key
      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_TENNIS)." ADD FOREIGN KEY (season_id) REFERENCES vi_season(season_id) ON DELETE CASCADE ON UPDATE CASCADE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_TENNIS)." ADD FOREIGN KEY (season_match_id) REFERENCES vi_season_match(season_match_id) ON DELETE CASCADE ON UPDATE CASCADE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_TENNIS)." ADD FOREIGN KEY (player_id) REFERENCES vi_player(player_id) ON DELETE CASCADE ON UPDATE CASCADE;";
      $this->db->query($sql);

      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_TENNIS)." ADD FOREIGN KEY (team_id) REFERENCES vi_team(team_id) ON DELETE CASCADE ON UPDATE CASCADE;";
      $this->db->query($sql);

    }

     //scoring rules
     $sql = "DELETE FROM ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." WHERE sports_id=".TENNIS_SPORTS_ID;
    $this->db->query($sql);
    
    $result = $this->db->select('*')->from(MASTER_SCORING_CATEGORY)->where('sports_id',TENNIS_SPORTS_ID)->get()->row_array();
    if(empty($result))
    {
        $this->db->insert(MASTER_SCORING_CATEGORY,array(
                                  'scoring_category_name'       => 'Best_of_3_sets',
                                  'en_scoring_category_name'    => 'Best of 3 sets',
                                  'hi_scoring_category_name'    => '3 सेटों में से सर्वश्रेष्ठ',
                                  'guj_scoring_category_name'   => '3 સેટમાંથી શ્રેષ્ઠ',
                                  'fr_scoring_category_name' => 'Le meilleur des 3 sets',
                                  'ben_scoring_category_name' => '3 সেটের সেরা',
                                  'pun_scoring_category_name' => '3 ਸੈੱਟਾਂ ਵਿੱਚੋਂ ਸਭ ਤੋਂ ਵਧੀਆ',
                                  'tam_scoring_category_name' => '3 செட்களில் சிறந்தது',
                                  'th_scoring_category_name' => 'ดีที่สุดใน 3 ชุด',
                                  'ru_scoring_category_name' => 'Лучший из 3 сетов',
                                  'id_scoring_category_name' => 'Terbaik dari 3 set',
                                  'tl_scoring_category_name' => 'Pinakamahusay sa 3 set',
                                  'zh_scoring_category_name' => '3组两胜制',
                                  'kn_scoring_category_name' => '3 ಸೆಟ್‌ಗಳಲ್ಲಿ ಅತ್ಯುತ್ತಮ',
                                  //'es_scoring_category_name' => 'La mejor de 3 sets',
                                  'sports_id'    => TENNIS_SPORTS_ID
                                )
                          );

        $master_scoring_category_id = $this->db->insert_id(); 
        if($master_scoring_category_id)
        {
          //insert rules
          $this->db->insert_batch(MASTER_SCORING_RULES,
                      array(
                              array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Won completed match',
                                  'en_score_position'         => 'Won completed match',
                                  'hi_score_position'         => 'पूरा मैच जीत लिया',
                                  'guj_score_position'        => 'પૂર્ણ થયેલ મેચ જીતી',
                                  'fr_score_position'         => 'A remporté le match terminé',
                                  'ben_score_position'        => 'সম্পূর্ণ ম্যাচ জিতেছে',
                                  'pun_score_position'        => 'ਪੂਰਾ ਮੈਚ ਜਿੱਤ ਲਿਆ',
                                  'tam_score_position'        => 'முடிக்கப்பட்ட போட்டியில் வெற்றி பெற்றது',
                                  'th_score_position'         => 'ชนะการแข่งขันเสร็จสิ้น',
                                  'ru_score_position'         => 'Выиграл завершенный матч',
                                  'id_score_position'         => 'Memenangkan pertandingan selesai',
                                  'tl_score_position'         => 'Nanalo sa natapos na laban',
                                  'zh_score_position'         => '赢得完整比赛',
                                  'kn_score_position'         => 'ಪೂರ್ಣಗೊಂಡ ಪಂದ್ಯವನ್ನು ಗೆದ್ದಿದೆ',
                                  //'es_score_position'         => 'Partido completado ganado',
                                  'score_points'              => '2.5',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'MATCH_OWN',
                                  'meta_key_alias'            => ''
                                ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Aces',
                                'en_score_position'         => 'Aces',
                                'hi_score_position'         => 'इक्के',
                                'guj_score_position'        => 'એસિસ',
                                'fr_score_position'         => 'As',
                                'ben_score_position'        => 'Aces',
                                'pun_score_position'        => 'ਏਸ',
                                'tam_score_position'        => 'ஏசஸ்',
                                'th_score_position'         => 'เอซ',
                                'ru_score_position'         => 'тузы',
                                'id_score_position'         => 'Aces',
                                'tl_score_position'         => 'Aces',
                                'zh_score_position'         => '王牌',
                                'kn_score_position'         => 'ಏಸಸ್',
                                //'es_score_position'         => 'ases',
                                'score_points'              => '0.3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'ACES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Double faults',
                                'en_score_position'         => 'Double faults',
                                'hi_score_position'         => 'दोहरा दोष',
                                'guj_score_position'        => 'ડબલ ફોલ્ટ',
                                'fr_score_position'         => 'Doubles fautes',
                                'ben_score_position'        => 'ডাবল ফল্ট',
                                'pun_score_position'        => 'ਦੋਹਰੇ ਨੁਕਸ',
                                'tam_score_position'        => 'இரட்டை தவறுகள்',
                                'th_score_position'         => 'ความผิดพลาดสองครั้ง',
                                'ru_score_position'         => 'Двойные ошибки',
                                'id_score_position'         => 'Kesalahan ganda',
                                'tl_score_position'         => 'Dobleng pagkakamali',
                                'zh_score_position'         => '双误',
                                'kn_score_position'         => 'ಡಬಲ್ ದೋಷಗಳು',
                                //'es_score_position'         => 'Dobles faltas',
                                'score_points'              => '-0.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'DOUBLE_FAULTS',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 2 games',
                                'en_score_position'         => 'Winning set by 2 games',
                                'hi_score_position'         => '2 गेम से जीत का सेट',
                                'guj_score_position'        => '2 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 2 jeux',
                                'ben_score_position'        => '২ গেমে জয়ী সেট',
                                'pun_score_position'        => '2 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '2 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 2 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 2 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 2 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 2 laro',
                                'zh_score_position'         => '两场比赛获胜',
                                'kn_score_position'         => '2 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 2 juegos',
                                'score_points'              => '12',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_2_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 3 games',
                                'en_score_position'         => 'Winning set by 3 games',
                                'hi_score_position'         => '3 गेम से जीत का सेट',
                                'guj_score_position'        => '3 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 3 jeux',
                                'ben_score_position'        => '3 গেমে জয়ী সেট',
                                'pun_score_position'        => '3 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '3 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 3 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 3 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 3 laro',
                                'zh_score_position'         => '连胜3场',
                                'kn_score_position'         => '3 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 3 juegos',
                                'score_points'              => '14',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_3_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 4 games',
                                'en_score_position'         => 'Winning set by 4 games',
                                'hi_score_position'         => '4 गेम से जीत का सेट',
                                'guj_score_position'        => '4 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 4 jeux',
                                'ben_score_position'        => '4 গেমে জয়ী সেট',
                                'pun_score_position'        => '4 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '4 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 4 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 4 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 4 laro',
                                'zh_score_position'         => '4场比赛获胜',
                                'kn_score_position'         => '4 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 4 juegos',
                                'score_points'              => '16',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_4_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 5 games',
                                'en_score_position'         => 'Winning set by 5 games',
                                'hi_score_position'         => '5 गेम से जीत का सेट',
                                'guj_score_position'        => '5 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 5 jeux',
                                'ben_score_position'        => '5 গেমে জয়ী সেট',
                                'pun_score_position'        => '5 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '5 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 5 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 5 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 5 laro',
                                'zh_score_position'         => '5场比赛获胜',
                                'kn_score_position'         => '5 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 5 juegos',
                                'score_points'              => '16.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_5_GAMES',
                                'meta_key_alias'            => ''
                              ),   
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 6 games',
                                'en_score_position'         => 'Winning set by 6 games',
                                'hi_score_position'         => '6 गेम से जीत का सेट',
                                'guj_score_position'        => '6 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 6 jeux',
                                'ben_score_position'        => '6 গেমে জয়ী সেট',
                                'pun_score_position'        => '6 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '6 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 6 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 6 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 6 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 6 laro',
                                'zh_score_position'         => '6场比赛获胜',
                                'kn_score_position'         => '6 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 6 juegos',
                                'score_points'              => '18',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_6_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by tie break',
                                'en_score_position'         => 'Winning set by tie break',
                                'hi_score_position'         => 'टाई ब्रेक से सेट की जीत',
                                'guj_score_position'        => 'ટાઇ બ્રેક દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par tie-break',
                                'ben_score_position'        => 'টাই ব্রেক করে জয়ের সেট',
                                'pun_score_position'        => 'ਟਾਈ ਬ੍ਰੇਕ ਦੁਆਰਾ ਸੈੱਟ ਜਿੱਤਿਆ',
                                'tam_score_position'        => 'டை பிரேக் மூலம் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะโดยไทเบรก',
                                'ru_score_position'         => 'Победный сет по тай-брейку',
                                'id_score_position'         => 'Kemenangan ditentukan melalui tie break',
                                'tl_score_position'         => 'Panalong itinakda sa pamamagitan ng tie break',
                                'zh_score_position'         => '通过抢七局获胜',
                                'kn_score_position'         => 'ಟೈ ಬ್ರೇಕ್ ಮೂಲಕ ಸೆಟ್ ಗೆಲುವು',
                                //'es_score_position'         => 'Set ganador por tie break',
                                'score_points'              => '6',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_TIE_BREAK',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 2 games',
                                'en_score_position'         => 'Losing set by 2 games',
                                'hi_score_position'         => '2 गेम से सेट हारना',
                                'guj_score_position'        => '2 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 2 matchs',
                                'ben_score_position'        => '২ গেমে হেরেছে সেট',
                                'pun_score_position'        => '2 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '2 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 2 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 2 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 2 game',
                                'tl_score_position'         => 'Talong set ng 2 laro',
                                'zh_score_position'         => '输掉两局比赛',
                                'kn_score_position'         => '2 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 2 juegos',
                                'score_points'              => '6',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_2_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 3 games',
                                'en_score_position'         => 'Losing set by 3 games',
                                'hi_score_position'         => '3 गेम से सेट हारना',
                                'guj_score_position'        => '3 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 3 matchs',
                                'ben_score_position'        => '3 গেমে হেরেছে সেট',
                                'pun_score_position'        => '3 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '3 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 3 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 3 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 3 game',
                                'tl_score_position'         => 'Talong set ng 3 laro',
                                'zh_score_position'         => '输掉3局比赛',
                                'kn_score_position'         => '3 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 3 juegos',
                                'score_points'              => '3.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_3_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 4 games',
                                'en_score_position'         => 'Losing set by 4 games',
                                'hi_score_position'         => '4 गेम से सेट हारना',
                                'guj_score_position'        => '4 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 4 matchs',
                                'ben_score_position'        => '4 গেমে হেরেছে সেট',
                                'pun_score_position'        => '4 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '4 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 4 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 4 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 4 game',
                                'tl_score_position'         => 'Talong set ng 4 laro',
                                'zh_score_position'         => '输掉4局比赛',
                                'kn_score_position'         => '4 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 4 juegos',
                                'score_points'              => '2.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_4_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 5 games',
                                'en_score_position'         => 'Losing set by 5 games',
                                'hi_score_position'         => '5 गेम से सेट हारना',
                                'guj_score_position'        => '5 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 5 matchs',
                                'ben_score_position'        => '5 গেমে হেরেছে সেট',
                                'pun_score_position'        => '5 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '5 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 5 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 5 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 5 game',
                                'tl_score_position'         => 'Talong set ng 5 laro',
                                'zh_score_position'         => '输掉5局比赛',
                                'kn_score_position'         => '5 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 5 juegos',
                                'score_points'              => '1.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_5_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 6 games',
                                'en_score_position'         => 'Losing set by 6 games',
                                'hi_score_position'         => '6 गेम से सेट हारना',
                                'guj_score_position'        => '6 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 6 matchs',
                                'ben_score_position'        => '6 গেমে হেরেছে সেট',
                                'pun_score_position'        => '6 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '6 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 6 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 6 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 6 game',
                                'tl_score_position'         => 'Talong set ng 6 laro',
                                'zh_score_position'         => '输掉6局比赛',
                                'kn_score_position'         => '6 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 6 juegos',
                                'score_points'              => '-1.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_6_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by tie break',
                                'en_score_position'         => 'Losing set by tie break',
                                'hi_score_position'         => 'टाई ब्रेक से सेट हारे',
                                'guj_score_position'        => 'ટાઇ બ્રેક દ્વારા સેટ હારી ગયો',
                                'fr_score_position'         => 'Perdre le set par tie-break',
                                'ben_score_position'        => 'টাই ব্রেক করে হেরেছে সেট',
                                'pun_score_position'        => 'ਟਾਈ ਬ੍ਰੇਕ ਦੁਆਰਾ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => 'டை பிரேக் மூலம் செட் இழந்தது',
                                'th_score_position'         => 'แพ้โดยไทเบรก',
                                'ru_score_position'         => 'Проигрыш сета на тай-брейке',
                                'id_score_position'         => 'Kalah ditentukan oleh tie break',
                                'tl_score_position'         => 'Pagkatalo sa set ng tie break',
                                'zh_score_position'         => '输掉6局比赛',
                                'kn_score_position'         => '因抢七而输掉一盘',
                                //'es_score_position'         => 'Perdiendo el set por tie break',
                                'score_points'              => '3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_TIE_BREAK',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Win by retirement before the match',
                                'en_score_position'         => 'Win by retirement before the match',
                                'hi_score_position'         => 'मैच से पहले रिटायरमेंट लेकर जीतें',
                                'guj_score_position'        => 'મેચ પહેલા નિવૃત્તિ દ્વારા જીત',
                                'fr_score_position'         => 'Gagner par abandon avant le match',
                                'ben_score_position'        => 'ম্যাচের আগে অবসর নিয়ে জয়',
                                'pun_score_position'        => 'ਮੈਚ ਤੋਂ ਪਹਿਲਾਂ ਸੰਨਿਆਸ ਲੈ ਕੇ ਜਿੱਤ',
                                'tam_score_position'        => 'போட்டிக்கு முன் ஓய்வு மூலம் வெற்றி',
                                'th_score_position'         => 'ชนะด้วยการรีไทร์ก่อนการแข่งขัน',
                                'ru_score_position'         => 'Победа до выхода на пенсию перед матчем',
                                'id_score_position'         => 'Menang dengan pensiun sebelum pertandingan',
                                'tl_score_position'         => 'Manalo sa pamamagitan ng pagreretiro bago ang laban',
                                'zh_score_position'         => '赛前退役获胜',
                                'kn_score_position'         => 'ಪಂದ್ಯದ ಮೊದಲು ನಿವೃತ್ತಿಯ ಮೂಲಕ ಗೆಲ್ಲಿರಿ',
                                //'es_score_position'         => 'Ganar por retiro antes del partido',
                                'score_points'              => '32',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WIN_BY_RETIREMENT',
                                'meta_key_alias'            => ''
                              ),                      
                            )
                        );
        }

        $this->db->insert(MASTER_SCORING_CATEGORY,array(
                                  'scoring_category_name'       => 'Best_of_5_sets',
                                  'en_scoring_category_name'    => 'Best of 5 sets',
                                  'hi_scoring_category_name'    => '5 सेटों में से सर्वश्रेष्ठ',
                                  'guj_scoring_category_name'   => '5 સેટમાંથી શ્રેષ્ઠ',
                                  'fr_scoring_category_name' => 'Le meilleur des 5 sets',
                                  'ben_scoring_category_name' => '5 সেটের সেরা',
                                  'pun_scoring_category_name' => '5 ਸੈੱਟਾਂ ਵਿੱਚੋਂ ਸਭ ਤੋਂ ਵਧੀਆ',
                                  'tam_scoring_category_name' => '5 செட்களில் சிறந்தது',
                                  'th_scoring_category_name' => 'ดีที่สุดใน 5 ชุด',
                                  'ru_scoring_category_name' => 'Лучший из 5 сетов',
                                  'id_scoring_category_name' => 'Terbaik dari 5 set',
                                  'tl_scoring_category_name' => 'Pinakamahusay sa 5 set',
                                  'zh_scoring_category_name' => '5组两胜制',
                                  'kn_scoring_category_name' => '5 ಸೆಟ್‌ಗಳಲ್ಲಿ ಅತ್ಯುತ್ತಮ',
                                  //'es_scoring_category_name' => 'La mejor de 5 sets',
                                  'sports_id'    => TENNIS_SPORTS_ID
                                )
                          );

        $master_scoring_category_id = $this->db->insert_id(); 
        if($master_scoring_category_id)
        {
          //insert rules
          $this->db->insert_batch(MASTER_SCORING_RULES,
                      array(
                              array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Won completed match',
                                  'en_score_position'         => 'Won completed match',
                                  'hi_score_position'         => 'पूरा मैच जीत लिया',
                                  'guj_score_position'        => 'પૂર્ણ થયેલ મેચ જીતી',
                                  'fr_score_position'         => 'A remporté le match terminé',
                                  'ben_score_position'        => 'সম্পূর্ণ ম্যাচ জিতেছে',
                                  'pun_score_position'        => 'ਪੂਰਾ ਮੈਚ ਜਿੱਤ ਲਿਆ',
                                  'tam_score_position'        => 'முடிக்கப்பட்ட போட்டியில் வெற்றி பெற்றது',
                                  'th_score_position'         => 'ชนะการแข่งขันเสร็จสิ้น',
                                  'ru_score_position'         => 'Выиграл завершенный матч',
                                  'id_score_position'         => 'Memenangkan pertandingan selesai',
                                  'tl_score_position'         => 'Nanalo sa natapos na laban',
                                  'zh_score_position'         => '赢得完整比赛',
                                  'kn_score_position'         => 'ಪೂರ್ಣಗೊಂಡ ಪಂದ್ಯವನ್ನು ಗೆದ್ದಿದೆ',
                                  //'es_score_position'         => 'Partido completado ganado',
                                  'score_points'              => '2.5',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'MATCH_OWN',
                                  'meta_key_alias'            => ''
                                ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Aces',
                                'en_score_position'         => 'Aces',
                                'hi_score_position'         => 'इक्के',
                                'guj_score_position'        => 'એસિસ',
                                'fr_score_position'         => 'As',
                                'ben_score_position'        => 'Aces',
                                'pun_score_position'        => 'ਏਸ',
                                'tam_score_position'        => 'ஏசஸ்',
                                'th_score_position'         => 'เอซ',
                                'ru_score_position'         => 'тузы',
                                'id_score_position'         => 'Aces',
                                'tl_score_position'         => 'Aces',
                                'zh_score_position'         => '王牌',
                                'kn_score_position'         => 'ಏಸಸ್',
                                //'es_score_position'         => 'ases',
                                'score_points'              => '0.2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'ACES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Double faults',
                                'en_score_position'         => 'Double faults',
                                'hi_score_position'         => 'दोहरा दोष',
                                'guj_score_position'        => 'ડબલ ફોલ્ટ',
                                'fr_score_position'         => 'Doubles fautes',
                                'ben_score_position'        => 'ডাবল ফল্ট',
                                'pun_score_position'        => 'ਦੋਹਰੇ ਨੁਕਸ',
                                'tam_score_position'        => 'இரட்டை தவறுகள்',
                                'th_score_position'         => 'ความผิดพลาดสองครั้ง',
                                'ru_score_position'         => 'Двойные ошибки',
                                'id_score_position'         => 'Kesalahan ganda',
                                'tl_score_position'         => 'Dobleng pagkakamali',
                                'zh_score_position'         => '双误',
                                'kn_score_position'         => 'ಡಬಲ್ ದೋಷಗಳು',
                                //'es_score_position'         => 'Dobles faltas',
                                'score_points'              => '-0.3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'DOUBLE_FAULTS',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 2 games',
                                'en_score_position'         => 'Winning set by 2 games',
                                'hi_score_position'         => '2 गेम से जीत का सेट',
                                'guj_score_position'        => '2 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 2 jeux',
                                'ben_score_position'        => '২ গেমে জয়ী সেট',
                                'pun_score_position'        => '2 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '2 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 2 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 2 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 2 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 2 laro',
                                'zh_score_position'         => '两场比赛获胜',
                                'kn_score_position'         => '2 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 2 juegos',
                                'score_points'              => '8',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_2_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 3 games',
                                'en_score_position'         => 'Winning set by 3 games',
                                'hi_score_position'         => '3 गेम से जीत का सेट',
                                'guj_score_position'        => '3 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 3 jeux',
                                'ben_score_position'        => '3 গেমে জয়ী সেট',
                                'pun_score_position'        => '3 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '3 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 3 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 3 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 3 laro',
                                'zh_score_position'         => '连胜3场',
                                'kn_score_position'         => '3 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 3 juegos',
                                'score_points'              => '9.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_3_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 4 games',
                                'en_score_position'         => 'Winning set by 4 games',
                                'hi_score_position'         => '4 गेम से जीत का सेट',
                                'guj_score_position'        => '4 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 4 jeux',
                                'ben_score_position'        => '4 গেমে জয়ী সেট',
                                'pun_score_position'        => '4 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '4 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 4 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 4 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 4 laro',
                                'zh_score_position'         => '4场比赛获胜',
                                'kn_score_position'         => '4 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 4 juegos',
                                'score_points'              => '10.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_4_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 5 games',
                                'en_score_position'         => 'Winning set by 5 games',
                                'hi_score_position'         => '5 गेम से जीत का सेट',
                                'guj_score_position'        => '5 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 5 jeux',
                                'ben_score_position'        => '5 গেমে জয়ী সেট',
                                'pun_score_position'        => '5 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '5 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 5 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 5 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 5 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 5 laro',
                                'zh_score_position'         => '5场比赛获胜',
                                'kn_score_position'         => '5 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 5 juegos',
                                'score_points'              => '11',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_5_GAMES',
                                'meta_key_alias'            => ''
                              ),   
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by 6 games',
                                'en_score_position'         => 'Winning set by 6 games',
                                'hi_score_position'         => '6 गेम से जीत का सेट',
                                'guj_score_position'        => '6 ગેમ દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par 6 jeux',
                                'ben_score_position'        => '6 গেমে জয়ী সেট',
                                'pun_score_position'        => '6 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਜਿੱਤ',
                                'tam_score_position'        => '6 ஆட்டங்களில் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะ 6 เกมติด',
                                'ru_score_position'         => 'Выигрышный сет в 6 геймах',
                                'id_score_position'         => 'Kemenangan ditentukan oleh 6 pertandingan',
                                'tl_score_position'         => 'Panalong itinakda ng 6 laro',
                                'zh_score_position'         => '6场比赛获胜',
                                'kn_score_position'         => '6 ಗೇಮ್‌ಗಳ ಸೆಟ್‌ನಲ್ಲಿ ಗೆಲುವು ಸಾಧಿಸಿದೆ',
                                //'es_score_position'         => 'Set ganador por 6 juegos',
                                'score_points'              => '12',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_6_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Winning set by tie break',
                                'en_score_position'         => 'Winning set by tie break',
                                'hi_score_position'         => 'टाई ब्रेक से सेट की जीत',
                                'guj_score_position'        => 'ટાઇ બ્રેક દ્વારા જીતનો સેટ',
                                'fr_score_position'         => 'Set gagnant par tie-break',
                                'ben_score_position'        => 'টাই ব্রেক করে জয়ের সেট',
                                'pun_score_position'        => 'ਟਾਈ ਬ੍ਰੇਕ ਦੁਆਰਾ ਸੈੱਟ ਜਿੱਤਿਆ',
                                'tam_score_position'        => 'டை பிரேக் மூலம் வெற்றி பெற்றது',
                                'th_score_position'         => 'ชนะโดยไทเบรก',
                                'ru_score_position'         => 'Победный сет по тай-брейку',
                                'id_score_position'         => 'Kemenangan ditentukan melalui tie break',
                                'tl_score_position'         => 'Panalong itinakda sa pamamagitan ng tie break',
                                'zh_score_position'         => '通过抢七局获胜',
                                'kn_score_position'         => 'ಟೈ ಬ್ರೇಕ್ ಮೂಲಕ ಸೆಟ್ ಗೆಲುವು',
                                //'es_score_position'         => 'Set ganador por tie break',
                                'score_points'              => '6',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WINNING_SET_TIE_BREAK',
                                'meta_key_alias'            => ''
                              ), 
                              
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 2 games',
                                'en_score_position'         => 'Losing set by 2 games',
                                'hi_score_position'         => '2 गेम से सेट हारना',
                                'guj_score_position'        => '2 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 2 matchs',
                                'ben_score_position'        => '২ গেমে হেরেছে সেট',
                                'pun_score_position'        => '2 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '2 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 2 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 2 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 2 game',
                                'tl_score_position'         => 'Talong set ng 2 laro',
                                'zh_score_position'         => '输掉两局比赛',
                                'kn_score_position'         => '2 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 2 juegos',
                                'score_points'              => '4',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_2_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 3 games',
                                'en_score_position'         => 'Losing set by 3 games',
                                'hi_score_position'         => '3 गेम से सेट हारना',
                                'guj_score_position'        => '3 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 3 matchs',
                                'ben_score_position'        => '3 গেমে হেরেছে সেট',
                                'pun_score_position'        => '3 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '3 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 3 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 3 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 3 game',
                                'tl_score_position'         => 'Talong set ng 3 laro',
                                'zh_score_position'         => '输掉3局比赛',
                                'kn_score_position'         => '3 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 3 juegos',
                                'score_points'              => '2.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_3_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 4 games',
                                'en_score_position'         => 'Losing set by 4 games',
                                'hi_score_position'         => '4 गेम से सेट हारना',
                                'guj_score_position'        => '4 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 4 matchs',
                                'ben_score_position'        => '4 গেমে হেরেছে সেট',
                                'pun_score_position'        => '4 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '4 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 4 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 4 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 4 game',
                                'tl_score_position'         => 'Talong set ng 4 laro',
                                'zh_score_position'         => '输掉4局比赛',
                                'kn_score_position'         => '4 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 4 juegos',
                                'score_points'              => '1.5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_4_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 5 games',
                                'en_score_position'         => 'Losing set by 5 games',
                                'hi_score_position'         => '5 गेम से सेट हारना',
                                'guj_score_position'        => '5 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 5 matchs',
                                'ben_score_position'        => '5 গেমে হেরেছে সেট',
                                'pun_score_position'        => '5 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '5 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 5 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 5 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 5 game',
                                'tl_score_position'         => 'Talong set ng 5 laro',
                                'zh_score_position'         => '输掉5局比赛',
                                'kn_score_position'         => '5 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 5 juegos',
                                'score_points'              => '1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_5_GAMES',
                                'meta_key_alias'            => ''
                              ), 
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by 6 games',
                                'en_score_position'         => 'Losing set by 6 games',
                                'hi_score_position'         => '6 गेम से सेट हारना',
                                'guj_score_position'        => '6 ગેમથી સેટ હારી ગયો',
                                'fr_score_position'         => 'Set perdant de 6 matchs',
                                'ben_score_position'        => '6 গেমে হেরেছে সেট',
                                'pun_score_position'        => '6 ਗੇਮਾਂ ਨਾਲ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => '6 ஆட்டங்களில் தோல்வி',
                                'th_score_position'         => 'แพ้ 6 เกมติด',
                                'ru_score_position'         => 'Проигрышный сет в 6 геймах',
                                'id_score_position'         => 'Kalah ditetapkan oleh 6 game',
                                'tl_score_position'         => 'Talong set ng 6 laro',
                                'zh_score_position'         => '输掉6局比赛',
                                'kn_score_position'         => '6 ಗೇಮ್‌ಗಳಿಂದ ಸೆಟ್‌ ಸೋತರು',
                                //'es_score_position'         => 'Set perdedor por 6 juegos',
                                'score_points'              => '-1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_6_GAMES',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Losing set by tie break',
                                'en_score_position'         => 'Losing set by tie break',
                                'hi_score_position'         => 'टाई ब्रेक से सेट हारे',
                                'guj_score_position'        => 'ટાઇ બ્રેક દ્વારા સેટ હારી ગયો',
                                'fr_score_position'         => 'Perdre le set par tie-break',
                                'ben_score_position'        => 'টাই ব্রেক করে হেরেছে সেট',
                                'pun_score_position'        => 'ਟਾਈ ਬ੍ਰੇਕ ਦੁਆਰਾ ਸੈੱਟ ਹਾਰ ਗਿਆ',
                                'tam_score_position'        => 'டை பிரேக் மூலம் செட் இழந்தது',
                                'th_score_position'         => 'แพ้โดยไทเบรก',
                                'ru_score_position'         => 'Проигрыш сета на тай-брейке',
                                'id_score_position'         => 'Kalah ditentukan oleh tie break',
                                'tl_score_position'         => 'Pagkatalo sa set ng tie break',
                                'zh_score_position'         => '输掉6局比赛',
                                'kn_score_position'         => '因抢七而输掉一盘',
                                //'es_score_position'         => 'Perdiendo el set por tie break',
                                'score_points'              => '3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'LOSING_SET_TIE_BREAK',
                                'meta_key_alias'            => ''
                              ),
                              array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Win by retirement before the match',
                                'en_score_position'         => 'Win by retirement before the match',
                                'hi_score_position'         => 'मैच से पहले रिटायरमेंट लेकर जीतें',
                                'guj_score_position'        => 'મેચ પહેલા નિવૃત્તિ દ્વારા જીત',
                                'fr_score_position'         => 'Gagner par abandon avant le match',
                                'ben_score_position'        => 'ম্যাচের আগে অবসর নিয়ে জয়',
                                'pun_score_position'        => 'ਮੈਚ ਤੋਂ ਪਹਿਲਾਂ ਸੰਨਿਆਸ ਲੈ ਕੇ ਜਿੱਤ',
                                'tam_score_position'        => 'போட்டிக்கு முன் ஓய்வு மூலம் வெற்றி',
                                'th_score_position'         => 'ชนะด้วยการรีไทร์ก่อนการแข่งขัน',
                                'ru_score_position'         => 'Победа до выхода на пенсию перед матчем',
                                'id_score_position'         => 'Menang dengan pensiun sebelum pertandingan',
                                'tl_score_position'         => 'Manalo sa pamamagitan ng pagreretiro bago ang laban',
                                'zh_score_position'         => '赛前退役获胜',
                                'kn_score_position'         => 'ಪಂದ್ಯದ ಮೊದಲು ನಿವೃತ್ತಿಯ ಮೂಲಕ ಗೆಲ್ಲಿರಿ',
                                //'es_score_position'         => 'Ganar por retiro antes del partido',
                                'score_points'              => '32',
                                'points_unit'               => '0',
                                'meta_key'                  => 'WIN_BY_RETIREMENT',
                                'meta_key_alias'            => ''
                              ),                      
                            )
                        );
        }

    }

    //Trasaction end
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE )
    {
        $this->db->trans_rollback();
    }
    else
    {
        $this->db->trans_commit();
    }
  }

  public function down()
  {
    //down script
  }

}