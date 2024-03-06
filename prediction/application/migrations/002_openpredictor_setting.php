<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Openpredictor_setting extends CI_Migration {
function __construct()
{
  $this->db_open_prediction =$this->load->database('open_predictor_db',TRUE);
  $this->open_predictor_forge = $this->load->dbforge($this->db_open_prediction, TRUE);

  $this->db_user =$this->load->database('user_db',TRUE);
  $this->user_forge = $this->load->dbforge($this->db_user, TRUE);
}



  public function up()
  {
      $prediction_setting = array(
              
                        'name' => 'allow_open_predictor',
                        'display_label'=> 'Open Predictor - Prize Pool',
                        'status' => 0,
                       
      );
      $this->db_user->insert(MODULE_SETTING,$prediction_setting);

      $hub_setting = 
      array(
        'en_title' => "Open Predictor - Prize Pool",
        'game_key' => 'allow_open_predictor',
        'hi_title'=> "भविष्यवाणी करें और सिक्के जीतो",
        'guj_title' => 'અનુમાન & સિક્કા જીતવા',
        'en_desc' => "Just predict the outcome and win coins",
        'hi_desc' => "बस परिणाम की भविष्यवाणी और सिक्के जीतने",
        'guj_desc' => "જસ્ટ પરિણામ આગાહી અને સિક્કા જીતવા",
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
        'site_rake' => array(
          'type' => 'FLOAT',
          'null' => FALSE,
          'default' => 0,
        ),
        'total_pool' => array(
          'type' => 'BIGINT',
          'constraint' => 20,
          'null' => TRUE,
          'default' => 0,
        ),
        'prize_pool' => array(
          'type' => 'BIGINT',
          'constraint' => 20,
          'null' => TRUE,
          'default' => 0,
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
        'bet_coins' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'default' => '0',
          'comment' => '1=>correct,0=>wrong'
        ),
        'win_coins' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'default' => '0'
        ),
        'is_refund' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'null' => FALSE,
          'default' => '0',
          'comment' => '1=>refunded,0=>not refund'
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
              'notification_type' => 222,
              'message' => 'Hey! Use your skills and predict on {{category}}, predictions is live now!',
              'en_message' => 'Hey! Use your skills and predict on {{category}}, predictions is live now!',
              'hi_message' => 'अपने कौशल का प्रयोग करें और {{category}} पर भविष्यवाणी, भविष्यवाणी लाइव है!',
              'guj_message' => 'હેય! તમારી કુશળતા વાપરો અને {{category}} પર આગાહી, આગાહીઓ હવે લાઇવ છે!'),
              array(
                'notification_type' => 223,
                'message' => 'Congratulations on predicting the right answer for {{category}}. Check out the results.',
                'en_message' => 'Congratulations on predicting the right answer for {{category}}. Check out the results.',
                'hi_message' => '{{category}} के लिए सही जवाब की भविष्यवाणी के लिए बधाई। परिणाम देखें।',
                'guj_message' => '{{category}} માટે યોગ્ય જવાબ અનુમાન પર અભિનંદન. પરિણામો તપાસો.'),
                array(
                  'notification_type' => 224,
                  'message' => 'You have received {{amount}} coins as Refund on canceling prediction by Admin',
                  'en_message' => 'You have received {{amount}} coins as Refund on canceling prediction by Admin',
                  'hi_message' => 'आप व्यवस्थापक द्वारा भविष्यवाणी रद्द करने {{amount}} वापसी के रूप में सिक्के प्राप्त हुआ है',
                  'guj_message' => 'જો તમે Admin દ્વારા આગાહી રદ પર {{amount}} રીફંડ કારણ કે સિક્કા પ્રાપ્ત થઈ છે')
            
            ) ;
      
            $this->db_user->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

            $transaction_message = array(
              array(
                'source' => 220,
                'en_message' => 'Bet Coins For Prediction',
                'hi_message' => 'शर्त के लिए बेट सिक्के',
                'guj_message' => 'આગાહી માટે સિક્કાઓ')
              ,
              array(
                'source' => 221,
                'en_message' => 'Prediction Won',
                'hi_message' => 'भविष्यवाणी जीता',
                'guj_message' => 'આગાહી જીતી')
              ,
              array(
              'source' => 224,
              'en_message' => 'Entry fee refund for prediction cancellation',
              'hi_message' => 'भविष्यवाणी रद्द करने के लिए प्रवेश शुल्क वापसी।',
              'guj_message' => 'આગાહી રદ એન્ટ્રી ફી રિફંડ')
            );
    
              $this->db_user->insert_batch(TRANSACTION_MESSAGES,$transaction_message);  

              //INSERT INTO `vi_email_template` (`email_template_id`, `template_name`, `subject`, `template_path`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES (NULL, 'Prediction Won', 'Prediction Winning', 'prediction-won', '183', '1', '0', NULL, NULL, 'Prediction Won', NULL, NULL);

              $email_temp = array(
                'template_name' => 'Prediction Won',
                'subject' => 'Prediction Winning',
                'template_path' => 'open-prediction-won',
                'notification_type' => 223,
                'status' => 1,
                'display_label' => 'Prediction Won'
              );
      
                $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);  



  }

  public function down()
  {
	    //down script 
      $this->db_user->delete(MODULE_SETTING, array('name' => 'allow_open_predictor'));
      
      $this->open_predictor_forge->drop_table(CATEGORY);
      $this->open_predictor_forge->drop_table(PREDICTION_MASTER);
      $this->open_predictor_forge->drop_table(PREDICTION_OPTION);
      $this->open_predictor_forge->drop_table(USER_PREDICTION);


      $this->db_user->where_in('notification_type',array(220,221,222,223));
      $this->db_user->delete(NOTIFICATION_DESCRIPTION);
      
      $this->db_user->where_in('source',array(220));
      $this->db_user->delete(TRANSACTION_MESSAGES);
      
      $this->db_user->where_in('notification_type',array(223));
      $this->db_user->delete(EMAIL_TEMPLATE);

  }
}