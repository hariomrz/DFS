<?php  defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sport_cfl extends CI_Migration 
{

  public function up()
  {
    
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    //Insert master sports
    
    $this->db->insert(MASTER_SPORTS,
                        array(
                              'sports_id'         => CFL_SPORTS_ID,
                              'sports_name'       => 'CFL',
                              'updated_date'      => format_date(),
                              'active'            => 0,
                              'team_player_count' => 7,
                              'order'             => 14
                          )
                      );
    
    $this->db->insert(MASTER_SPORTS_FORMAT,
                        array(
                              'sports_id'         => CFL_SPORTS_ID,
                              'display_name'      => 'CFL',
                              'en_display_name'   => 'CFL',
                              'hi_display_name'   => 'सीएफएल',
                              'guj_display_name'  => 'સી.એફ.એલ.',
                              'fr_display_name'   => 'LCF',
                              'ben_display_name'  => 'সিএফএল',
                              'pun_display_name'  => 'ਸੀ.ਐੱਫ.ਐੱਲ',
                              'tam_display_name'  => 'சி.எஃப்.எல்',
                              'th_display_name'   => 'CFL',
                              'ru_display_name'   => 'CFL',
                              'id_display_name'   => 'CFL',
                              'tl_display_name'   => 'CFL',
                              'zh_display_name'   => '节能灯',
                              'kn_display_name'  => 'ಸಿಎಫ್ಎಲ್',
                              'format_type'  => 'DAILY',
                              'description'  => 'Daily Fantasy'
                          )
                      );

     $this->db->insert_batch(MASTER_LINEUP_POSITION, array(
                        array(
                              'sports_id'             => CFL_SPORTS_ID,
                              'position_name'         => 'QB',
                              'position_display_name'  => 'QB',
                              'number_of_players'     => '1',
                              'position_order'        => '1',
                              'max_player_per_position'  => '1',
                              'allowed_position'        => 'QB'
                          ),
                          array(
                              'sports_id'             => CFL_SPORTS_ID,
                              'position_name'         => 'RB',
                              'position_display_name'  => 'RB',
                              'number_of_players'     => '3',
                              'position_order'        => '2',
                              'max_player_per_position'  => '3',
                              'allowed_position'        => 'RB'
                          ),
                            array(
                              'sports_id'             => CFL_SPORTS_ID,
                              'position_name'         => 'WR',
                              'position_display_name'  => 'WR',
                              'number_of_players'     => '3',
                              'position_order'        => '3',
                              'max_player_per_position'  => '3',
                              'allowed_position'        => 'WR'
                          ),
                            array(
                              'sports_id'             => CFL_SPORTS_ID,
                              'position_name'         => 'DEF',
                              'position_display_name'  => 'DEF',
                              'number_of_players'     => '1',
                              'position_order'        => '4',
                              'max_player_per_position'  => '1',
                              'allowed_position'        => 'DEF'
                          )
                        )    
                      );

      $this->db->insert(LEAGUE,
                        array(
                              'league_uid'     => 1,
                              'sports_id'      => CFL_SPORTS_ID,
                              'league_abbr'       => 'CFL',
                              'league_name'       => 'CFL',
                              'league_display_name'       => 'CFL',
                              'active'            => 1,
                              'max_player_per_team' => 3,
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
                              'sports_id'    => CFL_SPORTS_ID
                            )
                      );
      $master_scoring_category_id = $this->db->insert_id(); 
      //insert rules
      $this->db->insert_batch(MASTER_SCORING_RULES,array(

                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Yards',
                              'en_score_position'         => 'Passing Yards',
                              'hi_score_position'         => 'पासिंग यार्ड',
                              'guj_score_position'        => 'યાર્ડ્સ પસાર',
                              'fr_score_position'         => 'Les chantiers de dépassement',
                              'ben_score_position'        => 'পাসিং ইয়ার্ডস',
                              'pun_score_position'        => 'ਲੰਘ ਰਹੇ ਵਿਹੜੇ',
                              'tam_score_position'        => 'கடந்து செல்லும் யார்டுகள்',
                              'th_score_position'         => 'ผ่านหลา',
                              'ru_score_position'         => 'Пасовые ярды',
                              'id_score_position'         => 'Melewati Yard',
                              'tl_score_position'         => 'Dumadaan na Yard',
                              'zh_score_position'         => '通过码',
                              'kn_score_position'         => 'ಯಾರ್ಡ್‌ಗಳನ್ನು ಹಾದುಹೋಗುವುದು',
                              'score_points'              => '0.04',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Touchdowns',
                              'en_score_position'         => 'Passing Touchdowns',
                              'hi_score_position'         => 'टचडाउन पास करना',
                              'guj_score_position'        => 'પાસિંગ ટચડાઉન્સ',
                              'fr_score_position'        => 'Passer des touchés',
                              'ben_score_position'        => 'পাসিং টাচডাউনস',
                              'pun_score_position'        => 'ਟਚਡਾਉਨਜ਼ ਪਾਸ ਕਰਨਾ',
                              'tam_score_position'        => 'டச் டவுன்களைக் கடந்து செல்கிறது',
                              'th_score_position'         => 'ผ่านทัชดาวน์',
                              'ru_score_position'         => 'Прохождение тачдаунов',
                              'id_score_position'         => 'Melewati Touchdown',
                              'tl_score_position'         => 'Pagpasa sa mga Touchdown',
                              'zh_score_position'         => '传球达阵',
                              'kn_score_position'         => 'ಟಚ್‌ಡೌನ್‌ಗಳನ್ನು ಹಾದುಹೋಗುವುದು',
                              'score_points'              => '4',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Passing Interceptions',
                              'en_score_position'         => 'Passing Interceptions',
                              'hi_score_position'         => 'पासिंग इंटरसेप्शन',
                              'guj_score_position'        => 'અંતરાયો પસાર',
                              'fr_score_position'        => 'Interceptions réussies',
                              'ben_score_position'        => 'পাসিং ইন্টারসেপশনস',
                              'pun_score_position'        => 'ਲੰਘ ਰਹੇ ਰੁਕਾਵਟਾਂ',
                              'tam_score_position'        => 'குறுக்கீடுகள் கடந்து',
                              'th_score_position'         => 'สกัดกั้น',
                              'ru_score_position'         => 'Прохождение перехватов',
                              'id_score_position'         => 'Melewati Intersepsi',
                              'tl_score_position'         => 'Pagpasa sa Mga Paghadlang',
                              'zh_score_position'         => '传球拦截',
                              'kn_score_position'         => 'ಹಾದುಹೋಗುವ ಪ್ರತಿಬಂಧಗಳು',
                              'score_points'              => '-2',
                              'points_unit'               => '0',
                              'meta_key'                  => 'PASSING_INTERCEPTIONS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Rushing Yards',
                              'en_score_position'         => 'Rushing Yards',
                              'hi_score_position'         => 'रशिंग यार्ड',
                              'guj_score_position'        => 'રશિંગ યાર્ડ્સ',
                              'fr_score_position'         => 'Cours de précipitation',
                              'ben_score_position'        => 'রাশিং ইয়ার্ডস',
                              'pun_score_position'        => 'ਜਲਦੀ ਗਜ਼',
                              'tam_score_position'        => 'விரைவான யார்டுகள்',
                              'th_score_position'         => 'ลานวิ่ง',
                              'ru_score_position'         => 'Мчащиеся ярды',
                              'id_score_position'         => 'Lapangan Terburu-buru',
                              'tl_score_position'         => 'Rushing Yards',
                              'zh_score_position'         => '冲码',
                              'kn_score_position'         => 'ನುಗ್ಗುತ್ತಿರುವ ಗಜಗಳು',
                              'score_points'              => '0.1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RUSHING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Rushing Touchdowns',
                              'en_score_position'         => 'Rushing Touchdowns',
                              'hi_score_position'         => 'रशिंग टचडाउन',
                              'guj_score_position'        => 'રશિંગ ટચડાઉન્સ',
                              'fr_score_position'        => 'Touchés précipités',
                              'ben_score_position'        => 'রাশিং টাচডাউনস',
                              'pun_score_position'        => 'ਤਣਾਅ',
                              'tam_score_position'        => 'விரைவான டச் டவுன்கள்',
                              'th_score_position'         => 'วิ่งทัชดาวน์down',
                              'ru_score_position'         => 'Быстрые приземления',
                              'id_score_position'         => 'Touchdown Terburu-buru',
                              'tl_score_position'         => 'Rushing Touchdowns',
                              'zh_score_position'         => '冲球达阵',
                              'kn_score_position'         => 'ನುಗ್ಗುತ್ತಿರುವ ಟಚ್‌ಡೌನ್‌ಗಳು',
                              'score_points'              => '6',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RUSHING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Receptions',
                              'en_score_position'         => 'Receptions',
                              'hi_score_position'         => 'स्वागत',
                              'guj_score_position'        => 'રિસેપ્શન',
                              'fr_score_position'        => 'Réceptions',
                              'ben_score_position'        => 'রিসেপশনস',
                              'pun_score_position'        => 'ਧਾਰਣਾ',
                              'tam_score_position'        => 'வரவேற்புகள்',
                              'th_score_position'         => 'แผนกต้อนรับ',
                              'ru_score_position'         => 'Приемы',
                              'id_score_position'         => 'Resepsi',
                              'tl_score_position'         => 'Mga pagtanggap',
                              'zh_score_position'         => '招待会',
                              'kn_score_position'         => 'ಪುರಸ್ಕಾರಗಳು',
                              'score_points'              => '1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RECEPTIONS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Receiving Yards',
                              'en_score_position'         => 'Receiving Yards',
                              'hi_score_position'         => 'रिसीविंग यार्ड',
                              'guj_score_position'        => 'પ્રાપ્ત યાર્ડ્સ',
                              'fr_score_position'         => 'Cours de réception',
                              'ben_score_position'        => 'গজ প্রাপ্তি',
                              'pun_score_position'        => 'ਗਜ਼ ਪ੍ਰਾਪਤ ਕਰਨਾ',
                              'tam_score_position'        => 'யார்டுகளைப் பெறுதல்',
                              'th_score_position'         => 'รับหลา',
                              'ru_score_position'         => 'Получение ярдов',
                              'id_score_position'         => 'Menerima Yard',
                              'tl_score_position'         => 'Tumatanggap ng Mga Yard',
                              'zh_score_position'         => '接收码',
                              'kn_score_position'         => 'ಯಾರ್ಡ್‌ಗಳನ್ನು ಸ್ವೀಕರಿಸಲಾಗುತ್ತಿದೆ',
                              'score_points'              => '0.1',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RECEIVING_YARDS',
                              'meta_key_alias'            => ''
                            ),
                        array(
                              'master_scoring_category_id'  => $master_scoring_category_id,
                              'format'                    => '1',
                              'score_position'            => 'Receiving Touchdowns',
                              'en_score_position'         => 'Receiving Touchdowns',
                              'hi_score_position'         => 'टचडाउन प्राप्त करना',
                              'guj_score_position'        => 'Rટચડાઉન્સ પ્રાપ્ત કરવું',
                              'fr_score_position'        => 'Recevoir des touchés',
                              'ben_score_position'        => 'টাচডাউনগুলি প্রাপ্ত',
                              'pun_score_position'        => 'ਟਚਡਾਉਨ ਪ੍ਰਾਪਤ ਕਰਨਾ',
                              'tam_score_position'        => 'டச் டவுன்களைப் பெறுதல்',
                              'th_score_position'         => 'รับทัชดาวน์',
                              'ru_score_position'         => 'Прием приземлений',
                              'id_score_position'         => 'Menerima Touchdown',
                              'tl_score_position'         => 'Tumatanggap ng mga Touchdown',
                              'zh_score_position'         => '接收触地得分',
                              'kn_score_position'         => 'ಟಚ್‌ಡೌನ್‌ಗಳನ್ನು ಸ್ವೀಕರಿಸಲಾಗುತ್ತಿದೆ',
                              'score_points'              => '6',
                              'points_unit'               => '0',
                              'meta_key'                  => 'RECEIVING_TOUCHDOWNS',
                              'meta_key_alias'            => ''
                            ),array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Kick Return Yards',
                                  'en_score_position'         => 'kick Return Yards',
                                  'hi_score_position'         => 'किक रिटर्न यार्ड',
                                  'guj_score_position'        => 'કિક રીટર્ન યાર્ડ્સ',
                                  'fr_score_position'         => 'coup de pied retour yards',
                                  'ben_score_position'        => 'রিটার্ন গজ',
                                  'pun_score_position'        => 'ਕਿੱਕ ਰਿਟਰਨ ਗਜ਼',
                                  'tam_score_position'        => 'கிக் ரிட்டர்ன் யார்ட்ஸ்',
                                  'th_score_position'         => 'เตะกลับหลา',
                                  'ru_score_position'         => 'кик возврат ярдов',
                                  'id_score_position'         => 'tendangan Kembali Yards',
                                  'tl_score_position'         => 'sipa sa mga Balikbayan',
                                  'zh_score_position'         => '踢回场',
                                  'kn_score_position'         => 'ಕಿಕ್ ರಿಟರ್ನ್ ಯಾರ್ಡ್ಸ್',
                                  'score_points'              => '0.04',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'KICK_RETURN_YARDS',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Punt Return Yards',
                                  'en_score_position'         => 'Punt Return Yards',
                                  'hi_score_position'         => 'पंट रिटर्न यार्ड',
                                  'guj_score_position'        => 'પન્ટ રીટર્ન યાર્ડ્સ',
                                  'fr_score_position'         => 'Yards de retour de coup de volée',
                                  'ben_score_position'        => 'পুনট রিটার্ন গজ',
                                  'pun_score_position'        => 'ਪੈਂਟ ਰਿਟਰਨ ਗਜ਼',
                                  'tam_score_position'        => 'பன்ட் ரிட்டர்ன் யார்டுகள்',
                                  'th_score_position'         => 'ถ่อกลับหลา',
                                  'ru_score_position'         => 'Верфи Punt Return',
                                  'id_score_position'         => 'Lapangan Pengembalian Punt',
                                  'tl_score_position'         => 'Punt Return yard',
                                  'zh_score_position'         => '平底船返回码',
                                  'kn_score_position'         => 'ಪಂಟ್ ರಿಟರ್ನ್ ಯಾರ್ಡ್ಸ್',
                                  'score_points'              => '0.04',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'PUNT_RETURN_YARDS',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Kick Return Touchdowns',
                                  'en_score_position'         => 'Kick Return Touchdowns',
                                  'hi_score_position'         => 'किक रिटर्न टचडाउन',
                                  'guj_score_position'        => 'કિક રીટર્ન ટચડાઉન્સ',
                                  'fr_score_position'         => 'Coup de pied de retour de touché',
                                  'ben_score_position'        => 'কিক রিটার্ন টাচডাউনস',
                                  'pun_score_position'        => 'ਕਿੱਕ ਰਿਟਰਨ ਟਚਡਾਉਨਜ਼',
                                  'tam_score_position'        => 'கிக் ரிட்டர்ன் டச் டவுன்கள்',
                                  'th_score_position'         => 'เตะกลับทัชดาวน์',
                                  'ru_score_position'         => 'Тачдауны с ответным ударом',
                                  'id_score_position'         => 'Tendangan Kembali Touchdown',
                                  'tl_score_position'         => 'Kick Return Touchdowns',
                                  'zh_score_position'         => '踢回触地得分',
                                  'kn_score_position'         => 'ಕಿಕ್ ರಿಟರ್ನ್ ಟಚ್‌ಡೌನ್‌ಗಳು',
                                  'score_points'              => '6',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'KICK_RETURN_TOUCHDOWNS',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Punt Return Touchdowns',
                                  'en_score_position'         => 'Punt Return Touchdowns',
                                  'hi_score_position'         => 'पंट रिटर्न टचडाउन',
                                  'guj_score_position'        => 'પન્ટ રીટર્ન ટચડાઉન્સ',
                                  'fr_score_position'         => 'Touchés de retour de botté de dégagement',
                                  'ben_score_position'        => 'পুনট রিটার্ন টাচডাউনস',
                                  'pun_score_position'        => 'ਪੈਂਟ ਰਿਟਰਨ ਟੱਚਡਾdownਨ',
                                  'tam_score_position'        => 'பன்ட் ரிட்டர்ன் டச் டவுன்கள்',
                                  'th_score_position'         => 'Punt Return ทัชดาวน์',
                                  'ru_score_position'         => 'Тачдауны с возвратом пунта',
                                  'id_score_position'         => 'Tendangan Kembali Punt',
                                  'tl_score_position'         => 'Punt Return Touchdowns',
                                  'zh_score_position'         => '平底船返回达阵',
                                  'kn_score_position'         => 'ಪಂಟ್ ರಿಟರ್ನ್ ಟಚ್‌ಡೌನ್‌ಗಳು',
                                  'score_points'              => '6',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'PUNT_RETURN_TOUCHDOWNS',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Passing 2 Point Conversion',
                                  'en_score_position'         => 'Passing 2 Point Conversion',
                                  'hi_score_position'         => '2 प्वाइंट रूपांतरण पास करना',
                                  'guj_score_position'        => '2 પોઇન્ટ રૂપાંતર પસાર',
                                  'fr_score_position'         => 'Passer la conversion à 2 points',
                                  'ben_score_position'        => '2 পয়েন্ট রূপান্তর পাস হচ্ছে',
                                  'pun_score_position'        => '2 ਪੁਆਇੰਟ ਪਰਿਵਰਤਨ ਨੂੰ ਪਾਸ ਕਰਨਾ',
                                  'tam_score_position'        => '2 புள்ளி மாற்றத்தை கடந்து',
                                  'th_score_position'         => 'ผ่านการแปลง 2 แต้ม',
                                  'ru_score_position'         => 'Передача 2-х балльной конверсии',
                                  'id_score_position'         => 'Melewati Konversi 2 Poin',
                                  'tl_score_position'         => 'Pagpasa sa 2 Point na Conversion',
                                  'zh_score_position'         => '通过 2 点转换',
                                  'kn_score_position'         => '2 ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆ ಹಾದುಹೋಗುತ್ತಿದೆ',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'PASSING_TWO_POINT',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Rushing 2 Point Conversion',
                                  'en_score_position'         => 'Rushing 2 Point Conversion',
                                  'hi_score_position'         => 'रशिंग 2 पॉइंट रूपांतरण',
                                  'guj_score_position'        => '2 પોઇન્ટ કન્વર્ઝન દોડાવે છે',
                                  'fr_score_position'         => 'Conversion rapide en 2 points',
                                  'ben_score_position'        => 'রাশিয়ার 2 পয়েন্ট রূপান্তর',
                                  'pun_score_position'        => '2 ਪੁਆਇੰਟ ਪਰਿਵਰਤਨ ਤੇਜ਼ੀ ਨਾਲ ਚਲ ਰਿਹਾ ਹੈ',
                                  'tam_score_position'        => 'விரைவு 2 புள்ளி மாற்றம்',
                                  'th_score_position'         => 'การแปลงเร่ง 2 จุด Point',
                                  'ru_score_position'         => 'Рывок 2-х очковой конверсии',
                                  'id_score_position'         => 'Konversi 2 Poin Bergegas',
                                  'tl_score_position'         => 'Rushing 2 Point Conversion',
                                  'zh_score_position'         => '冲2点转换',
                                  'kn_score_position'         => '2 ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆ ನುಗ್ಗುತ್ತಿದೆ',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'RUSHING_TWO_POINT',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Receiving 2 Point Conversion',
                                  'en_score_position'         => 'Receiving 2 Point Conversion',
                                  'hi_score_position'         => '2 बिंदु रूपांतरण प्राप्त करना',
                                  'guj_score_position'        => '2 પોઇન્ટ રૂપાંતર પ્રાપ્ત કરવું',
                                  'fr_score_position'         => 'Recevoir une conversion à 2 points',
                                  'ben_score_position'        => '2 পয়েন্ট রূপান্তর প্রাপ্ত',
                                  'pun_score_position'        => '2 ਪੁਆਇੰਟ ਪਰਿਵਰਤਨ ਪ੍ਰਾਪਤ ਕਰਨਾ',
                                  'tam_score_position'        => '2 புள்ளி மாற்றத்தைப் பெறுதல்',
                                  'th_score_position'         => 'รับการแปลง 2 คะแนน',
                                  'ru_score_position'         => 'Получение 2-х балльной конверсии',
                                  'id_score_position'         => 'Menerima Konversi 2 Poin',
                                  'tl_score_position'         => 'Tumatanggap ng 2 Point na Conversion',
                                  'zh_score_position'         => '接收 2 点转换',
                                  'kn_score_position'         => '2 ಪಾಯಿಂಟ್ ಪರಿವರ್ತನೆ ಸ್ವೀಕರಿಸಲಾಗುತ್ತಿದೆ',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'RECEVING_TWO_POINT',
                                  'meta_key_alias'            => ''
                                ),

//defence
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Sacks',
                                  'en_score_position'         => 'Defense Sacks',
                                  'hi_score_position'         => 'रक्षा बोरे',
                                  'guj_score_position'        => 'સંરક્ષણ કોથળો',
                                  'fr_score_position'         => 'Sacs de défense',
                                  'ben_score_position'        => 'প্রতিরক্ষা বস্তা',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬੋਰੀ',
                                  'tam_score_position'        => 'பாதுகாப்பு சாக்குகள்',
                                  'th_score_position'         => 'กระสอบป้องกัน',
                                  'ru_score_position'         => 'Защитные мешки',
                                  'id_score_position'         => 'Karung Pertahanan',
                                  'tl_score_position'         => 'Mga Sack ng Depensa',
                                  'zh_score_position'         => '防御麻袋',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಚೀಲಗಳು',
                                  'score_points'              => '1',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_SACK',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Interceptions',
                                  'en_score_position'         => 'Defense Interceptions',
                                  'hi_score_position'         => 'रक्षा अवरोधन',
                                  'guj_score_position'        => 'સંરક્ષણ વિક્ષેપો',
                                  'fr_score_position'         => 'Interceptions défensives',
                                  'ben_score_position'        => 'প্রতিরক্ষা বাধা',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਰੁਕਾਵਟਾਂ',
                                  'tam_score_position'        => 'பாதுகாப்பு குறுக்கீடுகள்',
                                  'th_score_position'         => 'การป้องกันสกัดกั้น',
                                  'ru_score_position'         => 'Перехваты защиты',
                                  'id_score_position'         => 'Intersepsi Pertahanan',
                                  'tl_score_position'         => 'Mga Paghadlang sa Depensa',
                                  'zh_score_position'         => '防御拦截',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಪ್ರತಿಬಂಧಗಳು',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_INTERCEPTIONS',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Fumbles Recovered',
                                  'en_score_position'         => 'Defense Fumbles Recovered',
                                  'hi_score_position'         => 'रक्षा गड़बड़ी बरामद',
                                  'guj_score_position'        => 'સંરક્ષણ ધુમ્મસ પુન .પ્રાપ્ત',
                                  'fr_score_position'         => 'Fumbles de défense récupérés',
                                  'ben_score_position'        => 'প্রতিরক্ষা Fumbles পুনরুদ্ধার',
                                  'pun_score_position'        => 'ਬਚਾਅ ਪੱਖੂ',
                                  'tam_score_position'        => 'பாதுகாப்பு தடுமாறியது',
                                  'th_score_position'         => 'กลาโหม Fumbles กู้คืน',
                                  'ru_score_position'         => 'Оборона необратимо восстановлена',
                                  'id_score_position'         => 'Kesalahan Pertahanan Dipulihkan',
                                  'tl_score_position'         => 'Narekober ang Defense Fumbles',
                                  'zh_score_position'         => '防守失误恢复',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಫಂಬಲ್ಸ್ ಮರುಪಡೆಯಲಾಗಿದೆ',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_FUMBLES_RECOVERED',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Safeties',
                                  'en_score_position'         => 'Defense Safeties',
                                  'hi_score_position'         => 'रक्षा सुरक्षा',
                                  'guj_score_position'        => 'સંરક્ષણ સલામતી',
                                  'fr_score_position'         => 'Sécurité de la Défense',
                                  'ben_score_position'        => 'প্রতিরক্ষা সেফটিস',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਸੁਰੱਖਿਆ',
                                  'tam_score_position'        => 'பாதுகாப்பு பாதுகாப்புகள்',
                                  'th_score_position'         => 'การป้องกันความปลอดภัย',
                                  'ru_score_position'         => 'Защитные меры',
                                  'id_score_position'         => 'Keamanan Pertahanan',
                                  'tl_score_position'         => 'Mga Safity sa Depensa',
                                  'zh_score_position'         => '国防安全',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಸುರಕ್ಷತೆಗಳು',
                                  'score_points'              => '2',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_SAFETIES',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (0)',
                                  'en_score_position'         => 'Defense Points Allowed (0)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (0)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સ માન્ય છે (0)',
                                  'fr_score_position'         => 'Points de défense autorisés (0)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (0)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (0)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (0)',
                                  'th_score_position'         => 'อนุญาตให้ใช้แต้มป้องกัน (0)',
                                  'ru_score_position'         => 'Разрешенные очки защиты (0)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (0)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (0)',
                                  'zh_score_position'         => '允许的防御点 (0)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (0)',
                                  'score_points'              => '10',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_0',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (1-6)',
                                  'en_score_position'         => 'Defense Points Allowed (1-6)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (1-6)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (1-6)',
                                  'fr_score_position'         => 'Points de défense autorisés (1-6)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (1-6)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (1-6)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (1-6)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (1-6)',
                                  'ru_score_position'         => 'Допустимые очки защиты (1-6)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (1-6)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (1-6)',
                                  'zh_score_position'         => '允许的防御点数 (1-6)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (1-6)',
                                  'score_points'              => '7',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_1_6',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (7-13)',
                                  'en_score_position'         => 'Defense Points Allowed (7-13)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (7-13)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (7-13)',
                                  'fr_score_position'         => 'Points de défense autorisés (7-13)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (7-13)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (7-13)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (7-13)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (7-13)',
                                  'ru_score_position'         => 'Допустимые очки защиты (7-13)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (7-13)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (7-13)',
                                  'zh_score_position'         => '允许的防御点数 (7-13)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (7-13)',
                                  'score_points'              => '4',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_7_13',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (14-20)',
                                  'en_score_position'         => 'Defense Points Allowed (14-20)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (14-20)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (14-20)',
                                  'fr_score_position'         => 'Points de défense autorisés (14-20)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (14-20)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (14-20)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (14-20)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (14-20)',
                                  'ru_score_position'         => 'Допустимые очки защиты (14-20)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (14-20)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (14-20)',
                                  'zh_score_position'         => '允许的防御点数 (14-20)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (14-20)',
                                  'score_points'              => '1',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_14_20',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (21-27)',
                                  'en_score_position'         => 'Defense Points Allowed (21-27)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (21-27)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (21-27)',
                                  'fr_score_position'         => 'Points de défense autorisés (21-27)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (21-27)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (21-27)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (21-27)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (21-27)',
                                  'ru_score_position'         => 'Допустимые очки защиты (21-27)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (21-27)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (21-27)',
                                  'zh_score_position'         => '允许的防御点数 (21-27)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (21-27)',
                                  'score_points'              => '0',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_21_27',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (28-34)',
                                  'en_score_position'         => 'Defense Points Allowed (28-34)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (28-34)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (28-34)',
                                  'fr_score_position'         => 'Points de défense autorisés (28-34)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (28-34)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (28-34)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (28-34)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (28-34)',
                                  'ru_score_position'         => 'Допустимые очки защиты (28-34)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (28-34)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (28-34)',
                                  'zh_score_position'         => '允许的防御点数 (28-34)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (28-34)',
                                  'score_points'              => '-1',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_28_34',
                                  'meta_key_alias'            => ''
                                ),
                            array(
                                  'master_scoring_category_id'  => $master_scoring_category_id,
                                  'format'                    => '1',
                                  'score_position'            => 'Defense Points Allowed (35+)',
                                  'en_score_position'         => 'Defense Points Allowed (35+)',
                                  'hi_score_position'         => 'रक्षा अंक की अनुमति (35+)',
                                  'guj_score_position'        => 'સંરક્ષણ પોઇન્ટ્સને મંજૂરી આપવામાં આવી છે (35+)',
                                  'fr_score_position'         => 'Points de défense autorisés (35+)',
                                  'ben_score_position'        => 'প্রতিরক্ষা পয়েন্ট অনুমোদিত (35+)',
                                  'pun_score_position'        => 'ਰੱਖਿਆ ਬਿੰਦੂ ਆਗਿਆ (35+)',
                                  'tam_score_position'        => 'பாதுகாப்பு புள்ளிகள் அனுமதிக்கப்பட்டன (35+)',
                                  'th_score_position'         => 'ได้แต้มป้องกัน (35+)',
                                  'ru_score_position'         => 'Допустимые очки защиты (35+)',
                                  'id_score_position'         => 'Poin Pertahanan Diizinkan (35+)',
                                  'tl_score_position'         => 'Pinapayagan ang Mga Punto ng Depensa (35+)',
                                  'zh_score_position'         => '允许的防御点数 (35+)',
                                  'kn_score_position'         => 'ರಕ್ಷಣಾ ಅಂಕಗಳನ್ನು ಅನುಮತಿಸಲಾಗಿದೆ (35+)',
                                  'score_points'              => '-4',
                                  'points_unit'               => '0',
                                  'meta_key'                  => 'DEFENSE_POINTS_ALLOWED_35plus',
                                  'meta_key_alias'            => ''
                                ),
                       )
                      );

      
    //Insert statistics table
    $sql = "CREATE TABLE ".$this->db->dbprefix(GAME_STATISTICS_CFL)." (
            `league_id` int NOT NULL,
            `season_game_uid` varchar(100) NOT NULL,
            `week` int NOT NULL,
            `scheduled` varchar(500) NOT NULL,
            `scheduled_date` datetime NOT NULL,
            `home_uid` varchar(50) NOT NULL,
            `home_score` int NOT NULL DEFAULT '0',
            `away_uid` varchar(100) NOT NULL,
            `away_score` int NOT NULL DEFAULT '0',
            `team_uid` varchar(100) NOT NULL,
            `player_uid` varchar(100) NOT NULL,
            `passing_yards` int NOT NULL DEFAULT '0',
            `passing_touch_downs` int NOT NULL DEFAULT '0',
            `passing_interceptions` int NOT NULL DEFAULT '0',
            `passing_two_pt` int NOT NULL DEFAULT '0',
            `rushing_yards` int NOT NULL DEFAULT '0',
            `rushing_touch_downs` int NOT NULL DEFAULT '0',
            `rushing_two_pt` int NOT NULL DEFAULT '0',
            `receiving_yards` int NOT NULL DEFAULT '0',
            `receptions` int NOT NULL DEFAULT '0',
            `receiving_touch_downs` int NOT NULL DEFAULT '0',
            `receiving_two_pt` int NOT NULL DEFAULT '0',
            `fumbles_touch_downs` int NOT NULL DEFAULT '0',
            `fumbles_lost` int NOT NULL DEFAULT '0',
            `fumbles_recovered` int NOT NULL DEFAULT '0',
            `interceptions_yards` int NOT NULL DEFAULT '0',
            `interceptions_touch_downs` int NOT NULL DEFAULT '0',
            `interceptions` int NOT NULL DEFAULT '0',
            `kick_returns_yards` int NOT NULL DEFAULT '0',
            `kick_returns_touch_downs` int NOT NULL DEFAULT '0',
            `punt_returns_yards` int NOT NULL DEFAULT '0',
            `punt_return_touch_downs` int NOT NULL DEFAULT '0',
            `field_goals_made` int NOT NULL DEFAULT '0',
            `field_goals_from_1_19_yards` int NOT NULL DEFAULT '0',
            `field_goals_from_20_29_yards` int NOT NULL DEFAULT '0',
            `field_goals_from_30_39_yards` int NOT NULL DEFAULT '0',
            `field_goals_from_40_49_yards` int NOT NULL DEFAULT '0',
            `field_goals_from_50_yards` int NOT NULL DEFAULT '0',
            `extra_points_made` int NOT NULL DEFAULT '0',
            `extra_point_blocked` int NOT NULL DEFAULT '0',
            `field_goals_blocked` int NOT NULL DEFAULT '0',
            `defensive_interceptions` int NOT NULL DEFAULT '0',
            `defensive_fumbles_recovered` int NOT NULL DEFAULT '0',
            `defensive_kick_return_touchdowns` int NOT NULL DEFAULT '0',
            `defensive_punt_return_touchdowns` int NOT NULL DEFAULT '0',
            `sacks` int NOT NULL DEFAULT '0',
            `safeties` int NOT NULL DEFAULT '0',
            `defensive_touch_downs` int NOT NULL DEFAULT '0',
            `defence_turnovers` int NOT NULL DEFAULT '0',
            `points_allowed` int NOT NULL DEFAULT '0',
            `minutes` int NOT NULL DEFAULT '0',
            `player_minute` int NOT NULL DEFAULT '0',
            `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";
      $this->db->query($sql);
      $sql = "ALTER TABLE ".$this->db->dbprefix(GAME_STATISTICS_CFL)."
            ADD PRIMARY KEY (`league_id`,`season_game_uid`,`week`,`player_uid`) USING BTREE;";
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
                              WHERE MS.sports_id = ".CFL_SPORTS_ID." 
                                  ");
      $this->db->query(" DELETE MSC 
                              FROM ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." AS MSC
                              INNER JOIN ".$this->db->dbprefix(MASTER_SPORTS)." AS MS ON MS.sports_id = MSC.sports_id  
                              WHERE MS.sports_id = ".CFL_SPORTS_ID." 
                                  ");
      //Down script for master sports
      $this->db->where('sports_id',  CFL_SPORTS_ID);
      $this->db->delete(LEAGUE);

      $this->db->where('sports_id' , CFL_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS_FORMAT);

      $this->db->where('sports_id' , CFL_SPORTS_ID);
      $this->db->delete(MASTER_SPORTS);
      
      //Down script for statistic
      $this->dbforge->drop_table(GAME_STATISTICS_CFL);

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