<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_mlb_position extends CI_Migration 
{

  public function up()
  {
    
      //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();

      //update sports master

       $sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS)." SET `team_player_count` = '10' WHERE `sports_id` = ".BASEBALL_SPORTS_ID;
        $this->db->query($sql);
      //update league
      //Add new rules for soccer
        $sql = "UPDATE ".$this->db->dbprefix(LEAGUE)." SET `max_player_per_team` = '6' WHERE sports_id = ".BASEBALL_SPORTS_ID;
        $this->db->query($sql);

      //update lineup master

       $sql = "DELETE FROM ".$this->db->dbprefix(MASTER_LINEUP_POSITION)." WHERE sports_id = ".BASEBALL_SPORTS_ID;
        $this->db->query($sql);
        $this->db->insert_batch(MASTER_LINEUP_POSITION, array(
                          array(
                                'sports_id'             => BASEBALL_SPORTS_ID,
                                'position_name'         => 'OF',
                                'position_display_name'  => 'Outfielders',
                                'number_of_players'     => '2',
                                'position_order'        => '1',
                                'max_player_per_position'  => '5',
                                'allowed_position'        => 'OF'
                            ),
                            array(
                                'sports_id'             => BASEBALL_SPORTS_ID,
                                'position_name'         => 'IF',
                                'position_display_name'  => 'Infielders',
                                'number_of_players'     => '2',
                                'position_order'        => '2',
                                'max_player_per_position'  => '5',
                                'allowed_position'        => 'IF'
                            ),
                              array(
                                'sports_id'             => BASEBALL_SPORTS_ID,
                                'position_name'         => 'P',
                                'position_display_name'  => 'Pitchers',
                                'number_of_players'     => '1',
                                'position_order'        => '3',
                                'max_player_per_position'  => '2',
                                'allowed_position'        => 'P'
                            ),
                              array(
                                'sports_id'             => BASEBALL_SPORTS_ID,
                                'position_name'         => 'C',
                                'position_display_name'  => 'Catchers',
                                'number_of_players'     => '1',
                                'position_order'        => '4',
                                'max_player_per_position'  => '1',
                                'allowed_position'        => 'C'
                            )
                          )    
                        );

        //Scoring rules
        $sql = "DELETE FROM ".$this->db->dbprefix(MASTER_SCORING_RULES)." WHERE master_scoring_category_id = 22";
        $this->db->query($sql);

        $sql = "INSERT INTO ".$this->db->dbprefix(MASTER_SCORING_RULES)." (`master_scoring_category_id`, `format`, `score_position`, `en_score_position`, `hi_score_position`, `guj_score_position`, `fr_score_position`, `ben_score_position`, `pun_score_position`, `tam_score_position`, `th_score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`, `ru_score_position`, `id_score_position`, `tl_score_position`, `zh_score_position`, `kn_score_position`, `new_score_points`) VALUES
          (22, 1, 'Single', 'Single', 'एक', 'એકલુ', 'Célibataire', 'একক', 'ਸਿੰਗਲ', NULL, 'เดียว', 1, 0, 'SINGLE', '', 'Одинокий', 'Tunggal', 'walang asawa', '单身的', 'ಏಕ', 0),
          (22, 1, 'Double', 'Double', 'दोहरा', 'ડબલ', 'Double', 'ডবল', 'ਡਬਲ', NULL, 'สอง', 2, 0, 'DOUBLE', '', 'Двойной', 'Dua kali lipat', 'doble', '双倍的', 'ಡಬಲ್', 0),
          (22, 1, 'Triples', 'Triples', 'ट्रिपल', 'ટ્રીપલ', 'triples', 'ট্রিপল', 'ਟ੍ਰਿਪਲ', NULL, 'อเนกประสงค์', 3, 0, 'TRIPLES', '', 'троек', 'triples', 'triples', '三同', 'ಮೂರು', 0),
          (22, 1, 'Home Runs', 'Home Runs', 'होम रन', 'ઘર રન', 'Coups de circuit', 'হোম রান', 'ਮੁੱਖ ਰਨ', NULL, 'โฮมรัน', 4, 0, 'HITTING_HOME_RUN', '', 'Главная Запускается', 'rumah Runs', 'Home Nagpapatakbo', '本垒打', 'ಮುಖಪುಟ ರನ್ಗಳು', 0),
          (22, 1, 'Runs Batted In', 'Runs Batted In', 'रन बेटेड इन', 'ચાલે બેટિંગ માં', 'Points produits', 'চালায় ব্যাটিং ইন', 'ਚੱਲਦਾ ਬੱਲੇਬਾਜ਼ੀ ਵਿੱਚ', NULL, 'วิ่งเข้ามาถี่ยิบ', 1, 0, 'RUNS_BATTED_IN', '', 'Запускается сомкнул', 'Berjalan dipukul Dalam', 'Tumatakbo batted in', '打点', 'ಬ್ಯಾಟ್ ರನ್ನುಗಳು', 0),
          (22, 1, 'Runs', 'Runs', 'रन', 'રન', 'runs', 'রান', 'ਰਨ', NULL, 'วิ่ง', 1, 0, 'HITTING_RUNS', '', 'Запускается', 'berjalan', 'tumatakbo', '运行', 'ರನ್', 0),
          (22, 1, 'Walks', 'Walks', 'सैर', 'વોક', 'Des promenades', 'পদচারনা', 'ਚੱਲਣਾ', NULL, 'เดิน', 1, 0, 'HITTING_WALKS', '', 'Ходит', 'Walks', 'paglalakad', '自助游', 'ವಾಕ್ಸ್', 0),
          (22, 1, 'Stolen Bases', 'Stolen Bases', 'स्टोलन बेसेस', 'સ્ટોલન મથક', 'bases volés', 'হৃত ঘাঁটি', 'ਚੋਰੀ ਦਾ ਠਿਕਾਣਾ ਪ੍ਰਦਾਨ ਕਰਦਾ ਹੈ', NULL, 'ฐานขโมย', 1, 0, 'STOLEN_BASES', '', 'Украденные Основы', 'Basa Dicuri', 'Stolen Bases', '盗垒', 'ಸ್ಟೋಲನ್ ಬೇಸಸ್', 0),
          (22, 1, 'Hit By Pitch', 'Hit By Pitch', 'हिट बाय पिच', 'પિચ દ્વારા હિટ', 'Hit par la hauteur', 'পিচ দ্বারা হিট', 'ਪਿੱਚ ਕੇ ਹਿੱਟ', NULL, 'โดนขว้าง', 1, 0, 'HIT_BY_PITCH', '', 'Hit By Pitch', 'Hit Dengan pitch', 'Pindutin Sa pamamagitan ng Pitch', '命中由沥青', 'ಪಿಚ್ ಮೂಲಕ ಹಿಟ್', 0),
          (22, 1, 'Caught Stealing', 'Caught Stealing', 'कॉट स्टीलिंग', 'કેચ સ્ટિલિંગ', 'Surpris en train de voler', 'চুরি করতে ধরা পরেছিল', 'ਫੜਿਆ ਚੋਰੀ', NULL, 'ขโมยจับ', 1, 0, 'CAUGHT_STEALING', '', 'Поймали на краже', 'Tertangkap mencuri', 'nahuli pagnanakaw', '抓到偷', 'ಕಾಟ್ ಸ್ಟೀಲಿಂಗ್', 0) ";
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