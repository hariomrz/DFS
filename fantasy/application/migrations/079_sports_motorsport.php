<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sports_motorsport extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    $result = $this->db->select('*')->from(MASTER_SPORTS)->where('sports_id',MOTORSPORT_SPORTS_ID)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
                      'sports_id'      => MOTORSPORT_SPORTS_ID,
                      'sports_name'       => 'MOTORSPORT',
                      'team_player_count' => 6,
                      'max_player_per_team' => 2,
                      'active'            => 0,
                      'order'             => 15,
                      'updated_date'      => format_date()
                    );
      $this->db->insert(MASTER_SPORTS,$data_arr);
    }

    $result = $this->db->select('*')->from(MASTER_SPORTS_FORMAT)->where('sports_id',MOTORSPORT_SPORTS_ID)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
                      'sports_id'         => MOTORSPORT_SPORTS_ID,
                      'format_type'  => 'DAILY',
                      'description'  => 'Daily Fantasy',
                      'display_name'      => 'MOTORSPORT',
                      'en_display_name'   => 'MOTORSPORT',
                      'hi_display_name'   => 'मोटरस्पोर्ट',
                      'guj_display_name'  => 'મોટરસ્પોર્ટ',
                      'fr_display_name'  => 'Sport automobile',
                      'ben_display_name'  => 'মোটরস্পোর্ট',
                      'pun_display_name'  => 'ਮੋਟਰਸਪੋਰਟ',
                      'tam_display_name'  => 'மோட்டார்ஸ்போர்ட்',
                      'th_display_name'  => 'มอเตอร์สปอร์ต',
                      'ru_display_name'  => 'Автоспорт',
                      'id_display_name'  => 'Olahraga motor',
                      'tl_display_name'  => 'Motorsport',
                      'zh_display_name'  => '赛车运动',
                      'kn_display_name'  => 'ಮೋಟಾರು'
                  );
      $this->db->insert(MASTER_SPORTS_FORMAT,$data_arr);
    }

    $result = $this->db->select('*')->from(MASTER_LINEUP_POSITION)->where('sports_id',MOTORSPORT_SPORTS_ID)->get()->row_array();
    if(empty($result)){
      $data_arr = array(
                    array(
                          'sports_id'             => MOTORSPORT_SPORTS_ID,
                          'position_name'         => 'DR',
                          'position_display_name'  => 'Driver',
                          'number_of_players'     => '1',
                          'position_order'        => '1',
                          'max_player_per_position'  => '5',
                          'allowed_position'        => 'DR'
                      ),
                      array(
                          'sports_id'             => MOTORSPORT_SPORTS_ID,
                          'position_name'         => 'CR',
                          'position_display_name'  => 'Constructor',
                          'number_of_players'     => '1',
                          'position_order'        => '2',
                          'max_player_per_position'  => '1',
                          'allowed_position'        => 'CR'
                      )
                    );
      $this->db->insert_batch(MASTER_LINEUP_POSITION,$data_arr);
    }
     
    //scoring rules
    $result = $this->db->select('*')->from(MASTER_SCORING_CATEGORY)->where('sports_id',MOTORSPORT_SPORTS_ID)->get()->row_array();
    if(empty($result)){
      $this->db->insert(MASTER_SCORING_CATEGORY,
                      array(
                            'scoring_category_name'       => 'qualifying',
                            'en_scoring_category_name'      => 'Qualifying',
                            'hi_scoring_category_name'            => 'योग्यता',
                            'guj_scoring_category_name' => 'લાયકાત',
                            'fr_scoring_category_name' => 'Qualification',
                            'ben_scoring_category_name' => 'যোগ্যতা',
                            'pun_scoring_category_name' => 'ਯੋਗਤਾ',
                            'tam_scoring_category_name' => 'தகுதி பெறுதல்',
                            'th_scoring_category_name' => 'ควอลิฟาย',
                            'ru_scoring_category_name' => 'Квалификация',
                            'id_scoring_category_name' => 'Kualifikasi',
                            'tl_scoring_category_name' => 'Kwalipikado',
                            'zh_scoring_category_name' => '排位赛',
                            'kn_scoring_category_name' => 'ಅರ್ಹತೆ ಪಡೆಯುವುದು',
                            'es_scoring_category_name' => 'Calificación',
                            'sports_id'    => MOTORSPORT_SPORTS_ID
                          )
                    );
      $master_scoring_category_id = $this->db->insert_id();
      if($master_scoring_category_id){
        //insert rules
        $this->db->insert_batch(MASTER_SCORING_RULES,array(
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Q1 Finish',
                                'en_score_position'         => 'Q1 Finish',
                                'hi_score_position'         => 'Q1 खत्म',
                                'guj_score_position'        => 'Q1 સમાપ્ત',
                                'fr_score_position'         => 'Finition Q1',
                                'ben_score_position'        => 'প্রশ্ন 1 সমাপ্তি',
                                'pun_score_position'        => 'ಕ್ಯೂ 1 ಮುಕ್ತಾಯ',
                                'tam_score_position'        => 'Q1 பூச்சு',
                                'th_score_position'         => 'เสร็จสิ้น Q1',
                                'ru_score_position'         => 'ಕ್ಯೂ 1 ಮುಕ್ತಾಯ',
                                'id_score_position'         => 'Q1 selesai',
                                'tl_score_position'         => 'ಕ್ಯೂ 1 ಮುಕ್ತಾಯ',
                                'zh_score_position'         => 'Q1完成',
                                'kn_score_position'         => 'ಕ್ಯೂ 1 ಮುಕ್ತಾಯ',
                                'es_score_position'         => 'Terminar Q1',
                                'score_points'              => '1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q1_FINISH',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Q2 Finish',
                                'en_score_position'         => 'Q2 Finish',
                                'hi_score_position'         => 'Q2 खत्म',
                                'guj_score_position'        => 'Q2 સમાપ્ત',
                                'fr_score_position'         => 'Finition Q2',
                                'ben_score_position'        => 'প্রশ্ন 2 সমাপ্তি',
                                'pun_score_position'        => 'ಕ್ಯೂ 2 ಮುಕ್ತಾಯ',
                                'tam_score_position'        => 'Q2 பூச்சு',
                                'th_score_position'         => 'เสร็จสิ้น Q2',
                                'ru_score_position'         => 'ಕ್ಯೂ 2 ಮುಕ್ತಾಯ',
                                'id_score_position'         => 'Q2 selesai',
                                'tl_score_position'         => 'ಕ್ಯೂ 2 ಮುಕ್ತಾಯ',
                                'zh_score_position'         => 'Q2完成',
                                'kn_score_position'         => 'ಕ್ಯೂ 2 ಮುಕ್ತಾಯ',
                                'es_score_position'         => 'Terminar Q2',
                                'score_points'              => '2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q2_FINISH',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Q3 Finish',
                                'en_score_position'         => 'Q3 Finish',
                                'hi_score_position'         => 'Q3 खत्म',
                                'guj_score_position'        => 'Q3 સમાપ્ત',
                                'fr_score_position'         => 'Finition Q3',
                                'ben_score_position'        => 'প্রশ্ন 3 সমাপ্তি',
                                'pun_score_position'        => 'ಕ್ಯೂ 3 ಮುಕ್ತಾಯ',
                                'tam_score_position'        => 'Q3 பூச்சு',
                                'th_score_position'         => 'เสร็จสิ้น Q3',
                                'ru_score_position'         => 'ಕ್ಯೂ 3 ಮುಕ್ತಾಯ',
                                'id_score_position'         => 'Q3 selesai',
                                'tl_score_position'         => 'ಕ್ಯೂ 3 ಮುಕ್ತಾಯ',
                                'zh_score_position'         => 'Q3完成',
                                'kn_score_position'         => 'ಕ್ಯೂ 3 ಮುಕ್ತಾಯ',
                                'es_score_position'         => 'Terminar Q3',
                                'score_points'              => '3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q3_FINISH',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Qualified ahead of team mate (driver only)',
                                'en_score_position'         => 'Qualified ahead of team mate (driver only)',
                                'hi_score_position'         => 'टीम मेट (केवल ड्राइवर) के आगे योग्य',
                                'guj_score_position'        => 'ટીમ મેટ (ફક્ત ડ્રાઇવર) ની આગળ ક્વોલિફાય',
                                'fr_score_position'        => 'Qualifié avant son coéquipier (conducteur uniquement)',
                                'ben_score_position'        => 'টিম সাথীর চেয়ে যোগ্য (কেবল ড্রাইভার)',
                                'pun_score_position'        => 'ਟੀਮ ਸਾਥੀ (ਸਿਰਫ ਡਰਾਈਵਰ) ਦੇ ਅੱਗੇ ਯੋਗ',
                                'tam_score_position'        => 'அணி துணையை விட தகுதி (டிரைவர் மட்டும்)',
                                'th_score_position'         => 'มีคุณสมบัติก่อนเพื่อนร่วมทีม (คนขับเท่านั้น)',
                                'ru_score_position'         => 'Квалифицировано перед товарищем по команде (только водитель)',
                                'id_score_position'         => 'Memenuhi syarat di depan rekan setim (hanya pengemudi)',
                                'tl_score_position'         => 'Kwalipikado nangunguna sa Team Mate (driver lamang)',
                                'zh_score_position'         => '有资格领先队友（仅驾驶员）',
                                'kn_score_position'         => 'ತಂಡದ ಸಂಗಾತಿಗಿಂತ ಅರ್ಹತೆ (ಚಾಲಕ ಮಾತ್ರ)',
                                'es_score_position'         => 'Calificado por delante de su compañero de equipo (solo piloto)',
                                'score_points'              => '2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_AHEAD_TEAM_MATE',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Did not qualify (driver only)',
                                'en_score_position'         => 'Did not qualify (driver only)',
                                'hi_score_position'         => 'योग्य नहीं (ड्राइवर)',
                                'guj_score_position'        => 'ક્વોલિફાય ન કર્યું (ફક્ત ડ્રાઇવર)',
                                'fr_score_position'        => 'Ne sest pas qualifié (conducteur uniquement)',
                                'ben_score_position'        => 'যোগ্যতা অর্জন করেনি (কেবল ড্রাইভার)',
                                'pun_score_position'        => 'ਯੋਗਤਾ ਪੂਰੀ ਨਹੀਂ ਕੀਤੀ (ਸਿਰਫ ਡਰਾਈਵਰ)',
                                'tam_score_position'        => 'தகுதி பெறவில்லை (இயக்கி மட்டும்)',
                                'th_score_position'         => 'தகுதி பெறவில்லை (இயக்கி மட்டும்)',
                                'ru_score_position'         => 'Не квалифицировался (только драйвер)',
                                'id_score_position'         => 'Tidak memenuhi syarat (hanya pengemudi)',
                                'tl_score_position'         => 'Не квалифицировался (только драйвер)',
                                'zh_score_position'         => '没有资格（仅驾驶员）',
                                'kn_score_position'         => 'ಅರ್ಹತೆ ಪಡೆಯಲಿಲ್ಲ (ಚಾಲಕ ಮಾತ್ರ)',
                                'es_score_position'         => 'No calificó (solo conductor)',
                                'score_points'              => '-5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_NOT_QUALIFY',
                                'meta_key_alias'            => ''
                              ),
                          
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Pole position',
                                'en_score_position'         => 'Pole position',
                                'hi_score_position'         => 'पोल पोजीशन',
                                'guj_score_position'        => 'સ્તંભ સ્થાન',
                                'fr_score_position'        => 'Position de tête',
                                'ben_score_position'        => 'মেরু অবস্থানn',
                                'pun_score_position'        => 'ਖੰਭੇ ਦੀ ਸਥਿਤੀ',
                                'tam_score_position'        => 'துருவ நிலை',
                                'th_score_position'         => 'ตำแหน่งเสา',
                                'ru_score_position'         => 'Поул-позиция',
                                'id_score_position'         => 'Posisi tiang',
                                'tl_score_position'         => 'Posisyon ng poste',
                                'zh_score_position'         => '杆位第十位',
                                'kn_score_position'         => 'ಧ್ರುವ ಸ್ಥಾನ',
                                'es_score_position'         => 'Posición de privilegio',
                                'score_points'              => '10',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_1',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '2nd place',
                                'en_score_position'         => '2nd place',
                                'hi_score_position'         => 'दूसरा स्थान',
                                'guj_score_position'        => 'બીજી જગ્યા',
                                'fr_score_position'        => '2ème place',
                                'ben_score_position'        => 'দ্বিতীয় স্থান',
                                'pun_score_position'        => 'ਦੂਜਾ ਸਥਾਨ',
                                'tam_score_position'        => '2 வது இடம்',
                                'th_score_position'         => '2 வது இடம்',
                                'ru_score_position'         => '2 -е место',
                                'id_score_position'         => 'Tempat ke -2',
                                'tl_score_position'         => 'Ika -2 lugar',
                                'zh_score_position'         => '第二名',
                                'kn_score_position'         => '2 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => 'Segundo lugar',
                                'score_points'              => '9',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_2',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '3rd place',
                                'en_score_position'         => '3rd place',
                                'hi_score_position'         => 'तीसरा स्थान',
                                'guj_score_position'        => '3 જી સ્થળ',
                                'fr_score_position'        => '3ème place',
                                'ben_score_position'        => 'তৃতীয় স্থান',
                                'pun_score_position'        => 'ਤੀਜਾ ਜਗ੍ਹਾ',
                                'tam_score_position'        => '3 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 3',
                                'ru_score_position'         => '3 -е место',
                                'id_score_position'         => 'Tempat ke -3',
                                'tl_score_position'         => 'Ika -3 Lugar',
                                'zh_score_position'         => '第三名',
                                'kn_score_position'         => '3 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '3er lugar',
                                'score_points'              => '8',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_3',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '4th place',
                                'en_score_position'         => '4th place',
                                'hi_score_position'         => 'चौथे स्थान',
                                'guj_score_position'        => 'ચોથું સ્થાન',
                                'fr_score_position'        => '4e place',
                                'ben_score_position'        => 'চতুর্থ স্থান',
                                'pun_score_position'        => 'ਚੌਥੀ ਜਗ੍ਹਾ',
                                'tam_score_position'        => '4 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 4',
                                'ru_score_position'         => '4 -е место',
                                'id_score_position'         => 'Tempat ke -4',
                                'tl_score_position'         => 'Ika -4 na Lugar',
                                'zh_score_position'         => '第四名',
                                'kn_score_position'         => '4 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '4to lugar',
                                'score_points'              => '7',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_4',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '5th place',
                                'en_score_position'         => '5th place',
                                'hi_score_position'         => '5 वां स्थान',
                                'guj_score_position'        => '5 માં સ્થાન',
                                'fr_score_position'         => '5e place',
                                'ben_score_position'        => '5 ম স্থান',
                                'pun_score_position'        => '5 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '5 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 5',
                                'ru_score_position'         => '5 -е место',
                                'id_score_position'         => 'Tempat ke -5',
                                'tl_score_position'         => 'Ika -5 lugar',
                                'zh_score_position'         => '第五位',
                                'kn_score_position'         => '5 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '5to lugar',
                                'score_points'              => '6',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_5',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '6th place',
                                'en_score_position'         => '6th place',
                                'hi_score_position'         => '6 वां स्थान',
                                'guj_score_position'        => 'છઠ્ઠું સ્થાન',
                                'fr_score_position'         => '6e place',
                                'ben_score_position'        => '6th ষ্ঠ স্থান',
                                'pun_score_position'        => '6 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '6 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 6',
                                'ru_score_position'         => '6 -е место',
                                'id_score_position'         => 'Tempat ke -6',
                                'tl_score_position'         => 'Ika -6 na Lugar',
                                'zh_score_position'         => '第六名',
                                'kn_score_position'         => '6 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '6to lugar',
                                'score_points'              => '5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_6',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '7th place',
                                'en_score_position'         => '7th place',
                                'hi_score_position'         => '7 वां स्थान',
                                'guj_score_position'        => '7 મો સ્થાન',
                                'fr_score_position'         => '7e place',
                                'ben_score_position'        => '7 ম স্থান',
                                'pun_score_position'        => '7 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '7 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 7',
                                'ru_score_position'         => '7 -е место',
                                'id_score_position'         => 'Tempat ke -7',
                                'tl_score_position'         => 'Ika -7 na lugar',
                                'zh_score_position'         => '第七名',
                                'kn_score_position'         => '7 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '7mo lugar',
                                'score_points'              => '4',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_7',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '8th place',
                                'en_score_position'         => '8th place',
                                'hi_score_position'         => '8 वां स्थान',
                                'guj_score_position'        => '8 મો સ્થાન',
                                'fr_score_position'         => '8e place',
                                'ben_score_position'        => 'অষ্টম স্থান',
                                'pun_score_position'        => '8 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '8 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 8',
                                'ru_score_position'         => '8 -е место',
                                'id_score_position'         => 'Tempat ke -8',
                                'tl_score_position'         => 'Ika -8 na lugar',
                                'zh_score_position'         => '第八位',
                                'kn_score_position'         => '8 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '8vo lugar',
                                'score_points'              => '3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_8',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '9th place',
                                'en_score_position'         => '9th place',
                                'hi_score_position'         => '9 वां स्थान',
                                'guj_score_position'        => '9 મી જગ્યા',
                                'fr_score_position'         => '9e place',
                                'ben_score_position'        => 'নবম স্থান',
                                'pun_score_position'        => '9 ਵਾਂ',
                                'tam_score_position'        => '9 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 9',
                                'ru_score_position'         => '9 -е место',
                                'id_score_position'         => 'Tempat ke -9',
                                'tl_score_position'         => 'Ika -9 na lugar',
                                'zh_score_position'         => '第9位',
                                'kn_score_position'         => '9 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '9no lugar',
                                'score_points'              => '2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_9',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '10th place',
                                'en_score_position'         => '10th place',
                                'hi_score_position'         => '10 वां स्थान',
                                'guj_score_position'        => '10 મો સ્થાન',
                                'fr_score_position'         => '10e place',
                                'ben_score_position'        => 'দশম স্থান',
                                'pun_score_position'        => '10 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '10 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 10',
                                'ru_score_position'         => '10 -е место',
                                'id_score_position'         => 'Tempat ke -10',
                                'tl_score_position'         => 'Ika -10 lugar',
                                'zh_score_position'         => '第十位',
                                'kn_score_position'         => '10 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => 'décimo lugar',
                                'score_points'              => '1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'q_POS_10',
                                'meta_key_alias'            => ''
                              ),
                        )
                      );
      }

      $this->db->insert(MASTER_SCORING_CATEGORY,array(
                                'scoring_category_name'       => 'race',
                                'en_scoring_category_name'    => 'Race',
                                'hi_scoring_category_name'    => 'दौड़',
                                'guj_scoring_category_name'   => 'રેસ',
                                'fr_scoring_category_name' => 'Course',
                                'ben_scoring_category_name' => 'জাতি',
                                'pun_scoring_category_name' => 'ਦੌੜ',
                                'tam_scoring_category_name' => 'இனம்',
                                'th_scoring_category_name' => 'แข่ง',
                                'ru_scoring_category_name' => 'Раса',
                                'id_scoring_category_name' => 'Balapan',
                                'tl_scoring_category_name' => 'Lahi',
                                'zh_scoring_category_name' => '种族',
                                'kn_scoring_category_name' => 'ಜನಾಂಗ',
                                'es_scoring_category_name' => 'Carrera',
                                'sports_id'    => MOTORSPORT_SPORTS_ID
                              )
                        );
      $master_scoring_category_id = $this->db->insert_id(); 
      if($master_scoring_category_id){
        //insert rules
        $this->db->insert_batch(MASTER_SCORING_RULES,array(
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Finished Race',
                                'en_score_position'         => 'Finished Race',
                                'hi_score_position'         => 'समाप्त दौड़',
                                'guj_score_position'        => 'સંસર્જિત જાતિ',
                                'fr_score_position'         => 'Race finie',
                                'ben_score_position'        => 'সমাপ্ত রেস',
                                'pun_score_position'        => 'ਮੁਕੰਮਲ ਦੌੜ',
                                'tam_score_position'        => 'முடிக்கப்பட்ட இனம்',
                                'th_score_position'         => 'การแข่งขันเสร็จสิ้น',
                                'ru_score_position'         => 'Закончил гонку',
                                'id_score_position'         => 'Balapan selesai',
                                'tl_score_position'         => 'Tapos na lahi',
                                'zh_score_position'         => '比赛结束',
                                'kn_score_position'         => 'ಓಟ',
                                'es_score_position'         => '',
                                'score_points'              => '1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_FINISH',
                                'meta_key_alias'            => ''
                              ),
                          
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Finished ahead of team mate (driver only)',
                                'en_score_position'         => 'Finished ahead of team mate (driver only)',
                                'hi_score_position'         => 'टीम मेट (केवल ड्राइवर) से आगे समाप्त हुआ',
                                'guj_score_position'        => 'ટીમ મેટ (ફક્ત ડ્રાઇવર) ની આગળ સમાપ્ત થઈ',
                                'fr_score_position'         => 'Terminé avant son coéquipier (conducteur uniquement)',
                                'ben_score_position'        => 'টিম সাথীর আগে শেষ হয়েছে (কেবল ড্রাইভার)',
                                'pun_score_position'        => 'ਟੀਮ ਸਾਥੀ ਤੋਂ ਪਹਿਲਾਂ (ਸਿਰਫ ਡਰਾਈਵਰ)',
                                'tam_score_position'        => 'அணி துணையை விட (டிரைவர் மட்டும்)',
                                'th_score_position'         => 'เสร็จก่อนเพื่อนร่วมทีม (คนขับเท่านั้น)',
                                'ru_score_position'         => 'Закончил перед товарищем по команде (только водитель)',
                                'id_score_position'         => 'Selesai di depan rekan setim (hanya pengemudi)',
                                'tl_score_position'         => 'Tapos na sa unahan ng Team Mate (driver lamang)',
                                'zh_score_position'         => '领先于队友（仅驾驶员）',
                                'kn_score_position'         => 'ತಂಡದ ಸಂಗಾತಿಗಿಂತ ಮುಂದೆ ಮುಗಿದಿದೆ (ಚಾಲಕ ಮಾತ್ರ)',
                                'es_score_position'         => 'Terminado por delante de su compañero de equipo (solo piloto)',
                                'score_points'              => '3',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_AHEAD_TEAM_MATE',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Fastest lap (driver only)',
                                'en_score_position'         => 'Fastest lap (driver only)',
                                'hi_score_position'         => 'सबसे तेज़ गोद (केवल ड्राइवर)',
                                'guj_score_position'        => 'સૌથી ઝડપી લેપ (ફક્ત ડ્રાઇવર)',
                                'fr_score_position'        => 'Lapage le plus rapide (conducteur uniquement)',
                                'ben_score_position'        => 'দ্রুততম কোলে (কেবল ড্রাইভার)',
                                'pun_score_position'        => 'ਤੇਜ਼ ਲੈਪ (ਸਿਰਫ ਡਰਾਈਵਰ)',
                                'tam_score_position'        => 'வேகமான மடியில் (இயக்கி மட்டும்)',
                                'th_score_position'         => 'รอบที่เร็วที่สุด (คนขับเท่านั้น)',
                                'ru_score_position'         => 'Самый быстрый круг (только водитель)',
                                'id_score_position'         => 'Putaran tercepat (hanya pengemudi)',
                                'tl_score_position'         => 'Pinakamabilis na lap (driver lamang)',
                                'zh_score_position'         => '最快的圈（仅驱动器）',
                                'kn_score_position'         => 'ವೇಗದ ಲ್ಯಾಪ್ (ಚಾಲಕ ಮಾತ್ರ)',
                                'es_score_position'         => 'Vuelta más rápida (solo conductor)',
                                'score_points'              => '5',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_FASTEST_LAP',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Started race within Top 10, finished race but lost position (per place lost, max. -10 pts)',
                                'en_score_position'         => 'Started race within Top 10, finished race but lost position (per place lost, max. -10 pts)',
                                'hi_score_position'         => 'शीर्ष 10 में दौड़ शुरू की, दौड़ पूरी की लेकिन स्थान खो दिया (प्रति स्थान खोया, अधिकतम -10 अंक)',
                                'guj_score_position'        => 'ટોપ 10 ની અંદર રેસ શરૂ કરી, રેસ પૂરી કરી પણ પોઝિશન ગુમાવી (હારેલા સ્થાન દીઠ, મહત્તમ -10 પોઇન્ટ)',
                                'fr_score_position'         => 'Course commencée dans le Top 10, course terminée mais position perdue (par place perdue, max. -10 pts)',
                                'ben_score_position'        => 'শীর্ষ 10 এর মধ্যে রেস শুরু হয়েছে, রেস শেষ হয়েছে কিন্তু পজিশন হারিয়েছে (প্রতি জায়গা হারানো, সর্বোচ্চ -10 পয়েন্ট)',
                                'pun_score_position'        => 'ਸਿਖਰਲੇ 10 ਦੇ ਅੰਦਰ ਦੌੜ ਸ਼ੁਰੂ ਕੀਤੀ, ਦੌੜ ਖਤਮ ਹੋ ਗਈ ਪਰ ਸਥਿਤੀ ਹਾਰ ਗਈ (ਪ੍ਰਤੀ ਸਥਾਨ ਗੁਆਚਿਆ, ਅਧਿਕਤਮ -10 ਅੰਕ)',
                                'tam_score_position'        => 'முதல் 10 இடங்களுக்குள் பந்தயம் தொடங்கப்பட்டது, பந்தயத்தை முடித்தது ஆனால் இழந்த நிலையை (ஒரு இடத்திற்கு இழந்தது, அதிகபட்சம் -10 புள்ளிகள்)',
                                'th_score_position'         => 'เริ่มการแข่งขันภายใน 10 อันดับแรก จบการแข่งขันแต่เสียตำแหน่ง (ต่อตำแหน่งที่แพ้ สูงสุด -10 แต้ม)',
                                'ru_score_position'         => 'Начал гонку в Топ-10, закончил гонку, но потерял позицию (за потерянное место, макс. -10 очков)',
                                'id_score_position'         => 'Memulai balapan dalam Top 10, menyelesaikan balapan tetapi kehilangan posisi (per tempat hilang, maks. -10 poin)',
                                'tl_score_position'         => 'Sinimulan ang karera sa loob ng Nangungunang 10, natapos ang karera ngunit nawala ang posisyon (bawat lugar natalo, max. -10 pts)',
                                'zh_score_position'         => '在前 10 名内开始比赛，完成比赛但失去位置（每失去一个位置，最多 -10 分）',
                                'kn_score_position'         => 'ಟಾಪ್ 10 ರೊಳಗೆ ಓಟವನ್ನು ಪ್ರಾರಂಭಿಸಿದರು, ಓಟವನ್ನು ಪೂರ್ಣಗೊಳಿಸಿದರು ಆದರೆ ಸ್ಥಾನವನ್ನು ಕಳೆದುಕೊಂಡರು (ಪ್ರತಿ ಸ್ಥಾನ ಕಳೆದುಕೊಂಡರು, ಗರಿಷ್ಠ. -10 ಅಂಕಗಳು)',
                                'es_score_position'         => 'Carrera iniciada dentro del Top 10, carrera finalizada pero posición perdida (por puesto perdido, máx. -10 pts)',
                                'score_points'              => '-2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_START_RACE_WITHIN_10',
                                'meta_key_alias'            => ''
                              ),
                          
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => 'Pole position',
                                'en_score_position'         => 'Pole position',
                                'hi_score_position'         => 'पोल पोजीशन',
                                'guj_score_position'        => 'સ્તંભ સ્થાન',
                                'fr_score_position'        => 'Position de tête',
                                'ben_score_position'        => 'মেরু অবস্থানn',
                                'pun_score_position'        => 'ਖੰਭੇ ਦੀ ਸਥਿਤੀ',
                                'tam_score_position'        => 'துருவ நிலை',
                                'th_score_position'         => 'ตำแหน่งเสา',
                                'ru_score_position'         => 'Поул-позиция',
                                'id_score_position'         => 'Posisi tiang',
                                'tl_score_position'         => 'Posisyon ng poste',
                                'zh_score_position'         => '杆位第十位',
                                'kn_score_position'         => 'ಧ್ರುವ ಸ್ಥಾನ',
                                'es_score_position'         => 'Posición de privilegio',
                                'score_points'              => '25',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_1',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '2nd place',
                                'en_score_position'         => '2nd place',
                                'hi_score_position'         => 'दूसरा स्थान',
                                'guj_score_position'        => 'બીજી જગ્યા',
                                'fr_score_position'        => '2ème place',
                                'ben_score_position'        => 'দ্বিতীয় স্থান',
                                'pun_score_position'        => 'ਦੂਜਾ ਸਥਾਨ',
                                'tam_score_position'        => '2 வது இடம்',
                                'th_score_position'         => '2 வது இடம்',
                                'ru_score_position'         => '2 -е место',
                                'id_score_position'         => 'Tempat ke -2',
                                'tl_score_position'         => 'Ika -2 lugar',
                                'zh_score_position'         => '第二名',
                                'kn_score_position'         => '2 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => 'Segundo lugar',
                                'score_points'              => '18',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_2',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '3rd place',
                                'en_score_position'         => '3rd place',
                                'hi_score_position'         => 'तीसरा स्थान',
                                'guj_score_position'        => '3 જી સ્થળ',
                                'fr_score_position'        => '3ème place',
                                'ben_score_position'        => 'তৃতীয় স্থান',
                                'pun_score_position'        => 'ਤੀਜਾ ਜਗ੍ਹਾ',
                                'tam_score_position'        => '3 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 3',
                                'ru_score_position'         => '3 -е место',
                                'id_score_position'         => 'Tempat ke -3',
                                'tl_score_position'         => 'Ika -3 Lugar',
                                'zh_score_position'         => '第三名',
                                'kn_score_position'         => '3 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '3er lugar',
                                'score_points'              => '15',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_3',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '4th place',
                                'en_score_position'         => '4th place',
                                'hi_score_position'         => 'चौथे स्थान',
                                'guj_score_position'        => 'ચોથું સ્થાન',
                                'fr_score_position'        => '4e place',
                                'ben_score_position'        => 'চতুর্থ স্থান',
                                'pun_score_position'        => 'ਚੌਥੀ ਜਗ੍ਹਾ',
                                'tam_score_position'        => '4 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 4',
                                'ru_score_position'         => '4 -е место',
                                'id_score_position'         => 'Tempat ke -4',
                                'tl_score_position'         => 'Ika -4 na Lugar',
                                'zh_score_position'         => '第四名',
                                'kn_score_position'         => '4 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '4to lugar',
                                'score_points'              => '12',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_4',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '5th place',
                                'en_score_position'         => '5th place',
                                'hi_score_position'         => '5 वां स्थान',
                                'guj_score_position'        => '5 માં સ્થાન',
                                'fr_score_position'         => '5e place',
                                'ben_score_position'        => '5 ম স্থান',
                                'pun_score_position'        => '5 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '5 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 5',
                                'ru_score_position'         => '5 -е место',
                                'id_score_position'         => 'Tempat ke -5',
                                'tl_score_position'         => 'Ika -5 lugar',
                                'zh_score_position'         => '第五位',
                                'kn_score_position'         => '5 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '5to lugar',
                                'score_points'              => '10',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_5',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '6th place',
                                'en_score_position'         => '6th place',
                                'hi_score_position'         => '6 वां स्थान',
                                'guj_score_position'        => 'છઠ્ઠું સ્થાન',
                                'fr_score_position'         => '6e place',
                                'ben_score_position'        => '6th ষ্ঠ স্থান',
                                'pun_score_position'        => '6 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '6 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 6',
                                'ru_score_position'         => '6 -е место',
                                'id_score_position'         => 'Tempat ke -6',
                                'tl_score_position'         => 'Ika -6 na Lugar',
                                'zh_score_position'         => '第六名',
                                'kn_score_position'         => '6 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '6to lugar',
                                'score_points'              => '8',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_6',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '7th place',
                                'en_score_position'         => '7th place',
                                'hi_score_position'         => '7 वां स्थान',
                                'guj_score_position'        => '7 મો સ્થાન',
                                'fr_score_position'         => '7e place',
                                'ben_score_position'        => '7 ম স্থান',
                                'pun_score_position'        => '7 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '7 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 7',
                                'ru_score_position'         => '7 -е место',
                                'id_score_position'         => 'Tempat ke -7',
                                'tl_score_position'         => 'Ika -7 na lugar',
                                'zh_score_position'         => '第七名',
                                'kn_score_position'         => '7 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '7mo lugar',
                                'score_points'              => '6',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_7',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '8th place',
                                'en_score_position'         => '8th place',
                                'hi_score_position'         => '8 वां स्थान',
                                'guj_score_position'        => '8 મો સ્થાન',
                                'fr_score_position'         => '8e place',
                                'ben_score_position'        => 'অষ্টম স্থান',
                                'pun_score_position'        => '8 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '8 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 8',
                                'ru_score_position'         => '8 -е место',
                                'id_score_position'         => 'Tempat ke -8',
                                'tl_score_position'         => 'Ika -8 na lugar',
                                'zh_score_position'         => '第八位',
                                'kn_score_position'         => '8 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '8vo lugar',
                                'score_points'              => '4',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_8',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '9th place',
                                'en_score_position'         => '9th place',
                                'hi_score_position'         => '9 वां स्थान',
                                'guj_score_position'        => '9 મી જગ્યા',
                                'fr_score_position'         => '9e place',
                                'ben_score_position'        => 'নবম স্থান',
                                'pun_score_position'        => '9 ਵਾਂ',
                                'tam_score_position'        => '9 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 9',
                                'ru_score_position'         => '9 -е место',
                                'id_score_position'         => 'Tempat ke -9',
                                'tl_score_position'         => 'Ika -9 na lugar',
                                'zh_score_position'         => '第9位',
                                'kn_score_position'         => '9 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => '9no lugar',
                                'score_points'              => '2',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_9',
                                'meta_key_alias'            => ''
                              ),
                          array(
                                'master_scoring_category_id'  => $master_scoring_category_id,
                                'format'                    => '1',
                                'score_position'            => '10th place',
                                'en_score_position'         => '10th place',
                                'hi_score_position'         => '10 वां स्थान',
                                'guj_score_position'        => '10 મો સ્થાન',
                                'fr_score_position'         => '10e place',
                                'ben_score_position'        => 'দশম স্থান',
                                'pun_score_position'        => '10 ਵਾਂ ਸਥਾਨ',
                                'tam_score_position'        => '10 வது இடம்',
                                'th_score_position'         => 'อันดับที่ 10',
                                'ru_score_position'         => '10 -е место',
                                'id_score_position'         => 'Tempat ke -10',
                                'tl_score_position'         => 'Ika -10 lugar',
                                'zh_score_position'         => '第十位',
                                'kn_score_position'         => '10 ನೇ ಸ್ಥಾನ',
                                'es_score_position'         => 'décimo lugar',
                                'score_points'              => '1',
                                'points_unit'               => '0',
                                'meta_key'                  => 'f_POS_10',
                                'meta_key_alias'            => ''
                              ),
                        )
                      );
      }
    }

    //statistics table
    if(!$this->db->table_exists(GAME_STATISTICS_MOTORSPORT))
    {
      $fields = array(
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
          'default' => 0,
          'null' => FALSE
        ),
       
        'scheduled_date' => array(
          'type' => 'DATETIME',
          'default' => NULL,
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
        'f_points' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_fastest_lap_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'f_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_position' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_grid' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_pitstop_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'f_status' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),

        'q_points' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_fastest_lap_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_position' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_grid' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_pitstop_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q_status' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
      
        'q1_points' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_fastest_lap_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q1_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_position' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_grid' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_pitstop_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q1_status' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),

         'q2_points' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_fastest_lap_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q2_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_position' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_grid' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_pitstop_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q2_status' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q3_points' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_fastest_lap_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q3_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_position' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_grid' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_pitstop_count' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_time' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q3_status' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'q_fastest_lap_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_fastest_lap_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_fastest_lap_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_fastest_lap_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_fastest_lap_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q_total_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q1_total_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q2_total_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'q3_total_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'f_total_laps' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'null' => FALSE
        ),
        'updated_at' => array(
          'type' => 'VARCHAR',
          'constraint' => 25,
          'null' => FALSE
        )
      );

      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->create_table(GAME_STATISTICS_MOTORSPORT,FALSE,$attributes);

      //add unique key
      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_MOTORSPORT)." ADD UNIQUE KEY module_type(league_id,season_game_uid,player_uid);";
      $this->db->query($sql);
    }

    $fields = array(
            'match_event' => array(
              'type' => 'TINYINT',
              'constraint' => 1,
              'default' => 0
            ),
            'track_name' => array(
              'type' => 'VARCHAR',
              'constraint' => 255,
              'null' => FALSE
            )
    );
    if(!$this->db->field_exists('match_event', SEASON)){
      $this->dbforge->add_column(SEASON,$fields);
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