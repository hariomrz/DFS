<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Free_to_play extends CI_Migration {

    public function up() {

      $hub_setting = array(
          array(
              'en_title' => "FREE TO PLAY",
              'game_key' => 'allow_free2play',
              'hi_title'=> "फ्री में खेले",
              'guj_title' => 'ફ્રી માં રમો',
              'en_desc' => "Play daily fantasy totally free and win exciting prizes.",
              'hi_desc' => "दैनिक फंतासी को पूरी तरह से मुफ्त में खेलें और रोमांचक पुरस्कार जीतें।",
              'guj_desc' => "પ્લે દૈલી ફૅન્ટેસી મફત અને જીત આકર્ષક ઇનામો",
              'image' => "free2play.png",
              'status' => 0
          )
      );
      $this->db->insert_batch(SPORTS_HUB,$hub_setting);

      $notification_description = array(
                    array(
                        'notification_type' => 231,
                        'message'=> 'Mini-league {{mini_league_name}} join successfully',
                        'en_message' => 'Mini-league {{mini_league_name}} join successfully',
                        'hi_message' => 'मिनी-लीग {{mini_league_name}} सफलतापूर्वक सम्मिलित हों',
                        'guj_message' => 'મિની-લીગ {{mini_league_name}} સફળતાપૂર્વક જોડાઓ'
                    ),
                    array(
                        'notification_type' => 230,
                        'message'=> "Congratulations! You're a winner in the {{mini_league_name}} Mini-league",
                        'en_message' => "Congratulations! You're a winner in the {{mini_league_name}} Mini-league",
                        'hi_message' => "बधाई हो! आप {{mini_league_name}} मिनी-लीग में विजेता हैं",
                        'guj_message' => 'અભિનંદન! તમે {{mini_league_name}} મિની-લીગમાં વિજેતા છો'
                    )
                );
      $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_description);

      $email_temp = array(
                'template_name' => 'Mini-League Won',
                'subject' => 'Mini-League Winning',
                'template_path' => 'mini-league-won',
                'notification_type' => 230,
                'status' => 1,
                'display_label' => 'Mini-League Won'
              );
      
      $this->db->insert(EMAIL_TEMPLATE,$email_temp);

      $transaction_messages = array(
          array(
              'source' => 230,
              'en_message' => 'Mini-League Won',
              'hi_message' => 'प्रतियोगिता का पुरस्कार जीता',
              'guj_message' => 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
          ),
      );
      $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

    }

    public function down() {
      //down script 
      $this->db->where('en_title', 'FREE TO PLAY');
      $this->db->delete(SPORTS_HUB);

      $this->db->where_in('notification_type', array(231));
      $this->db->delete(NOTIFICATION_DESCRIPTION);

      $this->db->where_in('notification_type', array(230));
      $this->db->delete(NOTIFICATION_DESCRIPTION);
      
      $this->db->where_in('source', array(230));
      $this->db->delete(TRANSACTION_MESSAGES);
    }

}
