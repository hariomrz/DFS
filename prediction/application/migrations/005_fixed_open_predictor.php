<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fixed_open_predictor extends CI_Migration {
function __construct()
{
  $this->db_open_prediction =$this->load->database('fixed_open_predictor_db',TRUE);
  $this->open_predictor_forge = $this->load->dbforge($this->db_open_prediction, TRUE);

  
  $this->db_user =$this->load->database('user_db',TRUE);
  $this->user_forge = $this->load->dbforge($this->db_user, TRUE);
}



  public function up()
  {
      $prediction_setting = array(
              
                        'name' => 'allow_fixed_open_predictor',
                        'display_label'=> 'Open Predictor - Leaderboard',
                        'status' => 0,
                       
      );
      $this->db_user->insert(MODULE_SETTING,$prediction_setting);

      $hub_setting = 
      array(
        'en_title' => "Open Predictor - Leaderboard",
        'game_key' => 'allow_fixed_open_predictor',
        'hi_title'=> "भविष्यवाणी करें",
        'guj_title' => 'અનુમાન & સિક્કા જીતવા',
        'en_desc' => "Just predict the outcome and win prizes",
        'hi_desc' => "बस परिणाम की भविष्यवाणी और पुरस्कार जीतने",
        'guj_desc' => "જસ્ટ પરિણામ આગાહી અને ઇનામો જીતવા",
        'image' => "open-prediction.png",
        'status' => 0
      );
      $this->db_user->insert(SPORTS_HUB,$hub_setting);  


      $fields = array(
        'category_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                //'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 150,
          'null' => TRUE
        ),
        'image' => array(
          'type' => 'VARCHAR',
          'constraint' => 150,
          'null' => TRUE,
          'default' => NULL,
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'null' => FALSE,
          'comment' => '0=>inactive,1=>Active'
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'updated_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->open_predictor_forge->add_field($fields);
      $this->open_predictor_forge->add_key('category_id',TRUE);
      $this->open_predictor_forge->create_table(CATEGORY ,FALSE,$attributes);   
     


      $fields = array(
        'prediction_master_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                //'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'desc' => array(
          'type' => 'text',
          'null' => TRUE
        ),
        'category_id' => array(
          'type' => 'INT',
          'constraint' => 10,
          'null' => FALSE
        ),
        'is_pin' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'null' => FALSE,
            'comment' => '0=>not pin,1=>pinned',
            'default' => 0
        ),
        'deadline_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'null' => FALSE,
          'comment' => '0=>open,1=>close,2=>prize distributed,3=> pause,4=> deleted'
        ),
        'total_user_joined' => array(
          'type' => 'BIGINT',
          'constraint' => 15,
          'null' => FALSE,
          'default' => '0',
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'updated_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'source_url' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => TRUE,
            'default' => NULL
        ),
        'source_desc' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => TRUE,
          'default' => NULL
        ),
        'proof_desc' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => TRUE,
          'default' => NULL
        ),
        'proof_image' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => TRUE,
            'default' => NULL
          )
        );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->open_predictor_forge->add_field($fields);
      $this->open_predictor_forge->add_key('prediction_master_id',TRUE);
      $this->open_predictor_forge->create_table(PREDICTION_MASTER ,FALSE,$attributes);   
     
      
      
      //create prediction option table

       

      $fields = array(
        'prediction_option_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'prediction_master_id' => array(
          'type' => 'BIGINT',
          'constraint' => 15,
          'null' => FALSE,
        ),
        'option' => array(
          'type' => 'TEXT',
          'null' => FALSE
        ),
        'is_correct' => array(
          'type' => 'TINYINT',
          'constraint' => 2,
          'null' => FALSE,
          'default' => '0',
          'comment' => '1=>correct,0=>wrong'
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'updated_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
        );

        $this->open_predictor_forge->add_field($fields);
        $this->open_predictor_forge->add_key('prediction_option_id', TRUE);
        $this->open_predictor_forge->create_table(PREDICTION_OPTION,TRUE); 

      // //add 
      $fields = array(
        'user_prediction_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
        ),
        'prediction_option_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'updated_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
        );

        $this->open_predictor_forge->add_field($fields);
         $this->open_predictor_forge->add_key('user_prediction_id', TRUE);
         $this->open_predictor_forge->create_table(USER_PREDICTION,TRUE); 

        
        
          $notification_messages =array(
            array(
              'notification_type' => 240,
              'message' => 'Hey! Use your skills and predict on {{category}}, predictions is live now!',
              'en_message' => 'Hey! Use your skills and predict on {{category}}, predictions is live now!',
              'hi_message' => 'अपने कौशल का प्रयोग करें और {{category}} पर भविष्यवाणी, भविष्यवाणी लाइव है!',
              'guj_message' => 'હેય! તમારી કુશળતા વાપરો અને {{category}} પર આગાહી, આગાહીઓ હવે લાઇવ છે!'),
              array(
                'notification_type' => 241,
                'message' => 'Congratulations on predicting the right answer for {{category}}.Check out the results.',
                'en_message' => 'Congratulations on predicting the right answer for {{category}}. Check out the results.',
                'hi_message' => '{{category}} के लिए सही जवाब की भविष्यवाणी के लिए बधाई। परिणाम देखें।',
                'guj_message' => '{{category}} માટે યોગ્ય જવાબ અનુમાન પર અભિનંદન. પરિણામો તપાસો.'),
                ) ;
      
            $this->db_user->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

              //INSERT INTO `vi_email_template` (`email_template_id`, `template_name`, `subject`, `template_path`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES (NULL, 'Prediction Won', 'Prediction Winning', 'prediction-won', '183', '1', '0', NULL, NULL, 'Prediction Won', NULL, NULL);

              $email_temp = array(
                'template_name' => 'Prediction Won',
                'subject' => 'Prediction Winning',
                'template_path' => 'open-prediction-won',
                'notification_type' => 241,
                'status' => 1,
                'display_label' => 'Prediction Won'
              );
      
                $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);  

                $fields = array(
                  'prediction_prize_id' => array(
                          'type' => 'INT',
                          'constraint' => 10,
                          //'unsigned' => TRUE,
                          'auto_increment' => TRUE,
                          'null' => FALSE
                  ),
                  'prize_category' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => TRUE,
                    'comment' => '1=> day, 2=> week, 3 =>month'
                  ),
                  'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'status' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE,
                    'comment' => '0=>inactive,1=>Active'
                  ),
                  'allow_prize' => array(
                    'type' => 'TINYINT',
                    'null' => TRUE,
                    'default' => 0,
                  ),
                  'prize_distribution_detail' => array(
                    'type' => 'JSON',
                    'null' => TRUE,
                    //'default' => '[]',
                  ),
                  'allow_sponsor' => array(
                    'type' => 'TINYINT',
                    'null' => TRUE,
                    'default' => 0,
                  ),
                  'sponsor_logo' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'sponsor_link' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'sponsor_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  );
                  
                $attributes = array('ENGINE' => 'InnoDB');
                $this->open_predictor_forge->add_field($fields);
                $this->open_predictor_forge->add_key('prediction_prize_id',TRUE);
                $this->open_predictor_forge->create_table(PREDICTION_PRIZE ,FALSE,$attributes);   
               
                $prizes = 
                array(
                  array(
                    'prize_category' => 1,//day
                    'name' => 'Daily',
                    'status'=> 1,
                    'allow_prize' => 0
                  ),
                  array(
                    'prize_category' => 2,//week
                    'name' => 'Weekly',
                    'status'=> 1,
                    'allow_prize' => 0
                  ),
                  array(
                    'prize_category' => 3,//month
                    'name' => 'Monthly',
                    'status'=> 1,
                    'allow_prize' => 0
                  )
                );
                
                $this->db_open_prediction->insert_batch(PREDICTION_PRIZE,$prizes);  
            
            
            
            
                //leaderboard day  
                $fields = array(
                  'leaderboard_day_id' => array(
                          'type' => 'INT',
                          'constraint' => 10,
                          //'unsigned' => TRUE,
                          'auto_increment' => TRUE,
                          'null' => FALSE
                  ),
                  'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'is_winner' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => TRUE,
                    'default' => 0
                  ),
                  'rank_value' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'prize_data' => array(
                    'type' => 'JSON',
                    //'constraint' => 150,
                    'null' => TRUE,
                    //'default' => '[]',
                  ),
                  'correct_answer' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => FALSE
                  ),
                  'attempts' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 10,
                    'default' => 0,
                  ),
                  'day_number' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 5,
                    'default' => 0,
                  ),
                  'day_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'prize_distribution_history_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 11,
                    'default' => NULL,
                  )
                  );
                  
                $attributes = array('ENGINE' => 'InnoDB');
                $this->open_predictor_forge->add_field($fields);
                $this->open_predictor_forge->add_key('leaderboard_day_id',TRUE);
                $this->open_predictor_forge->create_table(LEADERBOARD_DAY ,FALSE,$attributes);   
                $this->db_open_prediction->query('ALTER TABLE `vi_leaderboard_day` ADD UNIQUE `unique_index` (`day_number`, `day_date`,`user_id`)');

                //leaderboard week
                $fields = array(
                  'leaderboard_week_id' => array(
                          'type' => 'INT',
                          'constraint' => 10,
                          //'unsigned' => TRUE,
                          'auto_increment' => TRUE,
                          'null' => FALSE
                  ),
                  'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'is_winner' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => TRUE,
                    'default' => 0
                  ),
                  'rank_value' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'prize_data' => array(
                    'type' => 'JSON',
                    //'constraint' => 150,
                    'null' => TRUE,
                    //'default' => '[]',
                  ),
                  'correct_answer' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => FALSE
                  ),
                  'attempts' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 10,
                    'default' => 0,
                  ),
                  'week_number' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 5,
                    'default' => 0,
                  ),
                  'week_start_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'week_end_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'prize_distribution_history_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 11,
                    'default' => NULL,
                  )
                  );
                  
                $attributes = array('ENGINE' => 'InnoDB');
                $this->open_predictor_forge->add_field($fields);
                $this->open_predictor_forge->add_key('leaderboard_week_id',TRUE);
                $this->open_predictor_forge->create_table(LEADERBOARD_WEEK ,FALSE,$attributes);   
                $this->db_open_prediction->query('ALTER TABLE `vi_leaderboard_week` ADD UNIQUE `unique_index` (`week_number`, `week_start_date`,`user_id`)');
                 //leaderboard month
                 $fields = array(
                  'leaderboard_month_id' => array(
                          'type' => 'INT',
                          'constraint' => 10,
                          //'unsigned' => TRUE,
                          'auto_increment' => TRUE,
                          'null' => FALSE
                  ),
                  'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'is_winner' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => TRUE,
                    'default' => 0
                  ),
                  'rank_value' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'prize_data' => array(
                    'type' => 'JSON',
                    //'constraint' => 150,
                    'null' => TRUE,
                    //'default' => '[]',
                  ),
                  'correct_answer' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => FALSE
                  ),
                  'attempts' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 10,
                    'default' => 0,
                  ),
                  'month_number' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 5,
                    'default' => 0,
                  ),
                  'month_start_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'month_end_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'default' => NULL,
                  ),
                  'prize_distribution_history_id' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'constraint' => 11,
                    'default' => NULL,
                  )
                  );
                  
                $attributes = array('ENGINE' => 'InnoDB');
                $this->open_predictor_forge->add_field($fields);
                $this->open_predictor_forge->add_key('leaderboard_month_id',TRUE);
                $this->open_predictor_forge->create_table(LEADERBOARD_MONTH ,FALSE,$attributes); 
                $this->db_open_prediction->query('ALTER TABLE `vi_leaderboard_month` ADD UNIQUE `unique_index` (`month_number`, `month_start_date`,`user_id`)');

                $fields = array(
                  'prize_distribution_history_id' => array(
                          'type' => 'INT',
                          'constraint' => 10,
                          //'unsigned' => TRUE,
                          'auto_increment' => TRUE,
                          'null' => FALSE
                  ),
                  'prediction_prize_id' => array(
                    'type' => 'INT',
                    'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => TRUE,
                    'default' => 0
                  ),
                  'prize_date' => array(
                    'type' => 'DATETIME',
                    //'constraint' => 10,
                    'null' => TRUE,
                    'comment' => ''
                  ),
                  'status' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => TRUE,
                    'default' => 0,
                  ),
                  'is_win_notify' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 0
                  )
                  );
                  
                $attributes = array('ENGINE' => 'InnoDB');
                $this->open_predictor_forge->add_field($fields);
                $this->open_predictor_forge->add_key('prize_distribution_history_id',TRUE);
                $this->open_predictor_forge->create_table(PRIZE_DISTRIBUTION_HISTORY ,FALSE,$attributes);    
                $this->db_open_prediction->query('ALTER TABLE `vi_prize_distribution_history` ADD UNIQUE `unique_key` (`prediction_prize_id`, `prize_date`);');

    $email_temp = array(
            'template_name' => 'Prediction Daily Won',
            'subject' => 'Prediction Leaderboard Winnings',
            'template_path' => 'fixed-prediction-won',
            'notification_type' => 225,
            'status' => 1,
            'display_label' => 'Prediction Daily Won'
          );
    $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);

    $email_temp = array(
            'template_name' => 'Prediction Weekly Won',
            'subject' => 'Prediction Leaderboard Winnings',
            'template_path' => 'fixed-prediction-won',
            'notification_type' => 226,
            'status' => 1,
            'display_label' => 'Prediction Weekly Won'
          );
    $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);

    $email_temp = array(
            'template_name' => 'Prediction Monthly Won',
            'subject' => 'Prediction Leaderboard Winnings',
            'template_path' => 'fixed-prediction-won',
            'notification_type' => 227,
            'status' => 1,
            'display_label' => 'Prediction Monthly Won'
          );
    $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);

    $transaction_messages = array(
          array(
              'source' => 225,
              'en_message' => 'Prediction Leaderboard Winnings',
              'hi_message' => 'भविष्यवाणी लीडरबोर्ड जीत',
              'guj_message' => 'આગાહી લીડરબોર્ડ વિજેતા',
          ),
          array(
              'source' => 226,
              'en_message' => 'Prediction Leaderboard Winnings',
              'hi_message' => 'भविष्यवाणी लीडरबोर्ड जीत',
              'guj_message' => 'આગાહી લીડરબોર્ડ વિજેતા',
          ),
          array(
              'source' => 227,
              'en_message' => 'Prediction Leaderboard Winnings',
              'hi_message' => 'भविष्यवाणी लीडरबोर्ड जीत',
              'guj_message' => 'આગાહી લીડરબોર્ડ વિજેતા',
          ),
      );
    $this->db_user->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

    $notification_description = array(
                  array(
                      'notification_type' => 225,
                      'message'=> 'Congratulations! You have won {{amount}} on Daily Leaderboard of {{start_date}} by achieving {{rank_value}} rank.',
                      'en_message' => 'Congratulations! You have won {{amount}} on Daily Leaderboard of {{start_date}} by achieving {{rank_value}} rank.',
                      'hi_message' => 'बधाई हो! आपने {{rank_value}} रैंक प्राप्त करके {{start_date}} के दैनिक लीडरबोर्ड पर {{amount}} जीता है।',
                      'guj_message' => 'અભિનંદન! તમે {{rank_value}} રેન્ક પ્રાપ્ત કરીને {{start_date}} ના દૈનિક લીડરબોર્ડ પર {{amount}} જીત્યો છે.'
                  ),
                  array(
                      'notification_type' => 226,
                      'message'=> "Congratulations! You have won {{amount}} on Weekly Leaderboard of Week {{start_date}} to {{end_date}} by achieving {{rank_value}} rank.",
                      'en_message' => "Congratulations! You have won {{amount}} on Weekly Leaderboard of Week {{start_date}} to {{end_date}} by achieving {{rank_value}} rank.",
                      'hi_message' => "बधाई हो! आपने वीकली लीडरबोर्ड ऑफ़ वीक {{start_date}} पर {{end_date}} {{rank_value}} रैंक हासिल करके {{amount}} जीता है।",
                      'guj_message' => 'અભિનંદન! તમે {{rank_value}} રેન્ક પ્રાપ્ત કરીને અઠવાડિયાના સાપ્તાહિક લીડરબોર્ડ {{start_date}} થી {{end_date}} પર {{amount}} જીત્યાં છે.'
                  ),
                  array(
                      'notification_type' => 227,
                      'message'=> "Congratulations! You have won {{amount}} on Monthly Leaderboard of {{start_date}} month by achieving {{rank_value}} rank.",
                      'en_message' => "Congratulations! You have won {{amount}} on Monthly Leaderboard of {{start_date}} month by achieving {{rank_value}} rank.",
                      'hi_message' => "बधाई हो! आपने {{rank_value}} रैंक हासिल करके {{start_date}} मासिक लीडरबोर्ड पर {{amount}} जीता है।",
                      'guj_message' => 'અભિનંદન! તમે {{rank_value}} રેન્ક પ્રાપ્ત કરીને {{start_date}} મહિનાના માસિક લીડરબોર્ડ પર {{amount}} જીત્યો છે.'
                  )
              );
    $this->db_user->insert_batch(NOTIFICATION_DESCRIPTION,$notification_description);


 
  }

  public function down()
  {
	    //down script 
      $this->db_user->delete(MODULE_SETTING, array('name' => 'allow_fixed_open_predictor'));
      
      $this->open_predictor_forge->drop_table(CATEGORY);
      $this->open_predictor_forge->drop_table(PREDICTION_MASTER);
      $this->open_predictor_forge->drop_table(PREDICTION_OPTION);
      $this->open_predictor_forge->drop_table(USER_PREDICTION);


      $this->db_user->where_in('notification_type',array(240,241));
      $this->db_user->delete(NOTIFICATION_DESCRIPTION);
      
      $this->db_user->where_in('notification_type',array(241));
      $this->db_user->delete(EMAIL_TEMPLATE);

      $this->open_predictor_forge->drop_table(PREDICTION_PRIZE);
      $this->open_predictor_forge->drop_table(LEADERBOARD_DAY);
      $this->open_predictor_forge->drop_table(LEADERBOARD_WEEK);
      $this->open_predictor_forge->drop_table(LEADERBOARD_MONTH);

      $this->db_user->where_in('notification_type',array(225,226,227));
      $this->db_user->delete(EMAIL_TEMPLATE);

      $this->db_user->where_in('source', array(225,226,227));
      $this->db_user->delete(TRANSACTION_MESSAGES);

      $this->db_user->where_in('notification_type', array(225,226,227));
      $this->db_user->delete(NOTIFICATION_DESCRIPTION);
  }
}