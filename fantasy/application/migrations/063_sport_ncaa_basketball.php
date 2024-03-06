<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sport_ncaa_basketball extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    //Insert master sports
    
    $this->db->insert(MASTER_SPORTS,
                        array(
                              'sports_id'         => NCAA_BASKETBALL_SPORTS_ID,
                              'sports_name'       => 'NCAA BASKETBALL',
                              'updated_date'      => format_date(),
                              'active'            => 0,
                              'team_player_count' => 8,
                              'order'             => 15
                          )
                      );
    
    $this->db->insert(MASTER_SPORTS_FORMAT,
                        array(
                              'sports_id'         => NCAA_BASKETBALL_SPORTS_ID,
                              'display_name'      => 'NCAA BASKETBALL',
                              'en_display_name'   => 'NCAA BASKETBALL',
                              'hi_display_name'   => 'एनसीएए बास्केटबॉल',
                              'guj_display_name'  => 'एनसीएए બાસ્કેટબોલ',
                              'fr_display_name'   => 'BASKET-BALL NCAA',
                              'ben_display_name'  => 'NCAA বাস্কেটবল',
                              'pun_display_name'  => 'ਐਨਸੀਏਏ ਬਾਸਕੇਟਬਾਲ',
                              'tam_display_name'  => 'என்சிஏஏ பாஸ்கெட்பால்',
                              'th_display_name'   => 'บาสเกตบอลซีเอ',
                              'ru_display_name'   => 'NCAA БАСКЕТБОЛ',
                              'id_display_name'   => 'BASKET NCAA',
                              'tl_display_name'   => 'NCAA BASKETBALL',
                              'zh_display_name'   => 'NCAA 篮球',
                              'kn_display_name'   => 'NCAA ಬ್ಯಾಸ್ಕೆಟ್ಬಾಲ್',
                              'format_type'  => 'DAILY',
                              'description'  => 'Daily Fantasy'
                          )
                      );

     $this->db->insert_batch(MASTER_LINEUP_POSITION, array(
                        array(
                              'sports_id'             => NCAA_BASKETBALL_SPORTS_ID,
                              'position_name'         => 'F',
                              'position_display_name'  => 'Forward',
                              'number_of_players'     => '1',
                              'position_order'        => '1',
                              'max_player_per_position'  => '4',
                              'allowed_position'        => 'F'
                          ),
                          array(
                              'sports_id'             => NCAA_BASKETBALL_SPORTS_ID,
                              'position_name'         => 'G',
                              'position_display_name'  => 'Guard',
                              'number_of_players'     => '1',
                              'position_order'        => '2',
                              'max_player_per_position'  => '4',
                              'allowed_position'        => 'G'
                          ),
                            array(
                              'sports_id'             => NCAA_BASKETBALL_SPORTS_ID,
                              'position_name'         => 'C',
                              'position_display_name'  => 'Centre',
                              'number_of_players'     => '1',
                              'position_order'        => '3',
                              'max_player_per_position'  => '4',
                              'allowed_position'        => 'C'
                          )
                        )    
                      );

      $this->db->insert(LEAGUE,
                        array(
                              'league_uid'     => 1,
                              'sports_id'      => NCAA_BASKETBALL_SPORTS_ID,
                              'league_abbr'       => 'ncaa',
                              'league_name'       => 'NCAAB',
                              'league_display_name'       => 'NCAA Basketball',
                              'active'            => 1,
                              'max_player_per_team' => 4,
                              'updated_date'      => format_date(),
                          )
                      );

    
       //scoring rules
       $this->db->insert(MASTER_SCORING_CATEGORY,
                        array(
                              'scoring_category_name'       => 'normal',
                              'en_scoring_category_name'    => 'normal',
                              'hi_scoring_category_name'    => 'साधारण',
                              'guj_scoring_category_name'   => 'સામાન્ય',
                              'fr_scoring_category_name'    => 'Ordinaire',
                              'ben_scoring_category_name'   => 'সাধারণ',
                              'pun_scoring_category_name'   => 'ਆਮ',
                              'tam_scoring_category_name'   => 'சாதாரண',
                              'th_scoring_category_name'    => 'ปกติ',
                              'ru_scoring_category_name'    => 'нормальный',
                              'id_scoring_category_name'    => 'normal',
                              'tl_scoring_category_name'    => 'normal',
                              'zh_scoring_category_name'    => '普通的',
                              'kn_scoring_category_name'    => 'ಸಾಮಾನ್ಯ',
                              'sports_id'    => NCAA_BASKETBALL_SPORTS_ID
                            )
                      );
      $master_scoring_category_id = $this->db->insert_id(); 

      //insert rules
      $this->db->insert_batch(MASTER_SCORING_RULES,array(

                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Assist',
                              'en_score_position'         => 'Assist',
                              'hi_score_position'         => 'सहायता देना',
                              'guj_score_position'        => 'સહાય કરો',
                              'fr_score_position'         => 'Aider',
                              'ben_score_position'        => 'সহায়তা করুন',
                              'pun_score_position'        => 'ਸਹਾਇਤਾ',
                              'tam_score_position'        => 'உதவு',
                              'th_score_position'         => 'ช่วยเหลือ',
                              'ru_score_position'         => 'Помощь',
                              'id_score_position'         => 'Membantu',
                              'tl_score_position'         => 'Tulungan',
                              'zh_score_position'         => '助攻',
                              'kn_score_position'         => 'ಸಹಾಯ',
                              'score_points'              => '1.5',
                              'points_unit'               => '0',
                              'meta_key'                  => 'ASSISTS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Block',
                              'en_score_position'         => 'Block',
                              'hi_score_position'         => 'बाधा',
                              'guj_score_position'        => 'બ્લોક',
                              'fr_score_position'         => 'Bloquer',
                              'ben_score_position'        => 'ব্লক',
                              'pun_score_position'        => 'ਬਲਾਕ',
                              'tam_score_position'        => 'தடு',
                              'th_score_position'         => 'ปิดกั้น',
                              'ru_score_position'         => 'Блокировать',
                              'id_score_position'         => 'Memblokir',
                              'tl_score_position'         => 'Harangan',
                              'zh_score_position'         => '堵塞',
                              'kn_score_position'         => 'ನಿರ್ಬಂಧಿಸಿ',
                              'score_points'              => '2',
                              'points_unit'               => '0',
                              'meta_key'                  => 'BLOCKED_SHOT',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Point',
                              'en_score_position'         => 'Point',
                              'hi_score_position'         => 'बिंदु',
                              'guj_score_position'        => 'બિંદુ',
                              'fr_score_position'        => 'Point',
                              'ben_score_position'        => 'বিন্দু',
                              'pun_score_position'        => 'ਬਿੰਦੂ',
                              'tam_score_position'        => 'புள்ளி',
                              'th_score_position'         => 'จุด',
                              'ru_score_position'         => 'Точка',
                              'id_score_position'         => 'Titik',
                              'tl_score_position'         => 'Punto',
                              'zh_score_position'         => '观点',
                              'kn_score_position'         => 'ಪಾಯಿಂಟ್',
                              'score_points'              => '1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'EACH_POINT',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Missed FG',
                              'en_score_position'         => 'Missed FG',
                              'hi_score_position'         => 'चूक गया फील्ड लक्ष्य',
                              'guj_score_position'        => 'મિસ્ડ ફીલ્ડ ગોલ',
                              'fr_score_position'         => 'BUT DE TERRAIN MANQUÉ',
                              'ben_score_position'        => 'মিসড ফিল্ড গোল',
                              'pun_score_position'        => 'ਮਿਸਡ ਫੀਲਡ ਗੋਲ',
                              'tam_score_position'        => 'தவறவிட்ட கோல்',
                              'th_score_position'         => 'พลาดเป้าหมายในสนาม',
                              'ru_score_position'         => 'Пропущенная цель на поле',
                              'id_score_position'         => 'GOL LAPANGAN TERLEWATKAN',
                              'tl_score_position'         => 'MISSED FIELD GOAL',
                              'zh_score_position'         => '射门偏出',
                              'kn_score_position'         => 'ತಪ್ಪಿದ ಗುರಿ',
                              'score_points'              => '-0.5',
                              'points_unit'               => '0',
                              'meta_key'                  => 'FIELD_GOALS_MISSED',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Missed FT',
                              'en_score_position'         => 'Missed FT',
                              'hi_score_position'         => 'छूटे हुए फ्री थ्रो',
                              'guj_score_position'        => 'મિસ્ડ ફ્રી થ્રો',
                              'fr_score_position'        => 'JETS FRANCS MANQUÉS',
                              'ben_score_position'        => 'মিস ফ্রি থ্রো',
                              'pun_score_position'        => 'ਮਿਸਡ ਫ੍ਰੀ ਥ੍ਰੋਅਜ਼',
                              'tam_score_position'        => 'கலக்காத இலவச த்ரோக்கள்',
                              'th_score_position'         => 'พลาดฟรี',
                              'ru_score_position'         => 'ПРОПУЩЕННЫЕ БРОСКИ',
                              'id_score_position'         => 'Lemparan Bebas yang Terlewatkan',
                              'tl_score_position'         => 'Napalampas na Libreng Pagtatapon',
                              'zh_score_position'         => '罚球失误',
                              'kn_score_position'         => 'ತಪ್ಪಿದ ಉಚಿತ ಥ್ರೋಗಳು',
                              'score_points'              => '-0.5',
                              'points_unit'               => '0',
                              'meta_key'                  => 'FREE_THROWS_MISSED',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Rebound',
                              'en_score_position'         => 'Rebound',
                              'hi_score_position'         => 'रिबाउंड्स',
                              'guj_score_position'        => 'રિબાઉન્ડ્સ',
                              'fr_score_position'         => 'Rebond',
                              'ben_score_position'        => 'রিবাউন্ডস',
                              'pun_score_position'        => 'ਦੁਬਾਰਾ',
                              'tam_score_position'        => 'திரும்பப் பெறுதல்',
                              'th_score_position'         => 'รีบาวน์',
                              'ru_score_position'         => 'ВОЗВРАТЫ',
                              'id_score_position'         => 'Rebound',
                              'tl_score_position'         => 'Rebound',
                              'zh_score_position'         => '反弹',
                              'kn_score_position'         => 'ಮರುಪಾವತಿಗಳು',
                              'score_points'              => '1.25',
                              'points_unit'               => '0',
                              'meta_key'                  => 'REBOUNDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Steal',
                              'en_score_position'         => 'Steal',
                              'hi_score_position'         => 'चुराना',
                              'guj_score_position'        => 'ચોરી',
                              'fr_score_position'         => 'Voler',
                              'ben_score_position'        => 'চুরি',
                              'pun_score_position'        => 'ਚੋਰੀ',
                              'tam_score_position'        => 'திருடு',
                              'th_score_position'         => 'ขโมย',
                              'ru_score_position'         => 'Воровать',
                              'id_score_position'         => 'Mencuri',
                              'tl_score_position'         => 'Magnakaw',
                              'zh_score_position'         => '偷',
                              'kn_score_position'         => 'ಕದಿಯಲು',
                              'score_points'              => '2',
                              'points_unit'               => '0',
                              'meta_key'                  => 'STEALS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Turnover',
                              'en_score_position'         => 'Turnover',
                              'hi_score_position'         => 'कारोबार',
                              'guj_score_position'        => 'ટર્નઓવર',
                              'fr_score_position'        => 'Chiffre daffaires',
                              'ben_score_position'        => 'টার্নওভার',
                              'pun_score_position'        => 'ਟਰਨਓਵਰ',
                              'tam_score_position'        => 'விற்றுமுதல்',
                              'th_score_position'         => 'มูลค่าการซื้อขาย',
                              'ru_score_position'         => 'Оборот',
                              'id_score_position'         => 'Pergantian',
                              'tl_score_position'         => 'Turnover',
                              'zh_score_position'         => '周转',
                              'kn_score_position'         => 'ವಹಿವಾಟು',
                              'score_points'              => '-1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'TURNOVERS',
                              'meta_key_alias'            => ''
                            )
                       )
                      );

      
    //Insert statistics table 
    $sql = "CREATE TABLE 
              ".$this->db->dbprefix(GAME_STATISTICS_NCAA_BASKETBALL)." (
            `league_id` int NOT NULL,
            `season_game_uid` varchar(100) NOT NULL,
            `week` int NOT NULL,
            `scheduled_date` datetime NOT NULL,
            `home_uid` varchar(100) NOT NULL,
            `away_uid` varchar(100) NOT NULL,
            `status` varchar(20) DEFAULT NULL,
            `team_uid` varchar(100) NOT NULL,
            `team_points` decimal(11,2) NOT NULL DEFAULT '0.00',
            `scoring_type` varchar(20) NOT NULL,
            `player_uid` varchar(100) NOT NULL,
            `position` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `minutes` decimal(7,2) NOT NULL DEFAULT '0.00',
            `field_goals_made` decimal(7,2) NOT NULL DEFAULT '0.00',
            `field_goals_attempted` decimal(7,2) NOT NULL DEFAULT '0.00',
            `field_goals_missed` decimal(7,2) NOT NULL DEFAULT '0.00',
            `free_throws_made` decimal(7,2) NOT NULL DEFAULT '0.00',
            `free_throws_attempted` decimal(7,2) NOT NULL DEFAULT '0.00',
            `free_throws_missed` decimal(7,2) NOT NULL DEFAULT '0.00',
            `rebounds` decimal(7,2) NOT NULL DEFAULT '0.00',
            `assists` decimal(7,2) NOT NULL DEFAULT '0.00',
            `steals` decimal(7,2) NOT NULL DEFAULT '0.00',
            `blocked_shots` decimal(7,2) NOT NULL DEFAULT '0.00',
            `turnovers` decimal(7,2) NOT NULL DEFAULT '0.00',
            `points` decimal(7,2) NOT NULL DEFAULT '0.00',
            `updated_at` datetime DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;";
      $this->db->query($sql);
      $sql = "ALTER TABLE 
              ".$this->db->dbprefix(GAME_STATISTICS_NCAA_BASKETBALL)."
              ADD PRIMARY KEY (`league_id`,`season_game_uid`,`player_uid`,`scoring_type`,`week`) USING BTREE;";
    $this->db->query($sql);  
    
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
	    //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      /*
      //Delete scoring rules
      $this->db->query(" DELETE MSR 
                              FROM ".$this->db->dbprefix(MASTER_SCORING_RULES)." AS MSR
                              INNER JOIN ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC ON MSC.master_scoring_category_id = MSR.master_scoring_category_id
                              INNER JOIN ".$this->db->dbprefix(MASTER_SPORTS)." AS MS ON MS.sports_id = MSC.sports_id  
                              WHERE MS.sports_id = ".NCAA_BASKETBALL_SPORTS_ID." 
                                  ");
      $this->db->query(" DELETE MSC 
                              FROM ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC
                              INNER JOIN ".$this->db->dbprefix(MASTER_SPORTS)." AS MS ON MS.sports_id = MSC.sports_id  
                              WHERE MS.sports_id = ".NCAA_BASKETBALL_SPORTS_ID." 
                                  ");
      //Down script for master sports
      $this->db->where('sports_id',  NCAA_BASKETBALL_SPORTS_ID);
      $this->db->delete(LEAGUE);

      $this->db->where('sports_id' , NCAA_BASKETBALL_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS_FORMAT);

      $this->db->where('sports_id' , NCAA_BASKETBALL_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS);
      
      //Down script for statistic
      $this->dbforge->drop_table(GAME_STATISTICS_NCAA_BASKETBALL);

      */
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

}