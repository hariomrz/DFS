<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pridiction_setting extends CI_Migration {
function __construct()
{
  $this->db_prediction =$this->load->database('prediction_db',TRUE);
  $this->prediction_forge = $this->load->dbforge($this->db_prediction, TRUE);

  $this->db_user =$this->load->database('user_db',TRUE);
  $this->user_forge = $this->load->dbforge($this->db_user, TRUE);
}



  public function up()
  {
      $prediction_setting = array(
              
                        'name' => 'allow_prediction',
                        'display_label'=> 'Prediction',
                        'status' => 0,
                       
      );
      $this->db_user->insert(MODULE_SETTING,$prediction_setting);

     

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
        'season_game_uid' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => FALSE
        ),
        'sports_id' => array(
          'type' => 'INT',
          'constraint' => 2,
          'null' => TRUE
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
      $this->prediction_forge->add_field($fields);
      $this->prediction_forge->add_key('prediction_master_id',TRUE);
      $this->prediction_forge->create_table(PREDICTION_MASTER ,FALSE,$attributes);   
     
      
      
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

        $this->prediction_forge->add_field($fields);
        $this->prediction_forge->add_key('prediction_option_id', TRUE);
        $this->prediction_forge->create_table(PREDICTION_OPTION,TRUE); 

     

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

        $this->prediction_forge->add_field($fields);
         $this->prediction_forge->add_key('user_prediction_id', TRUE);
         $this->prediction_forge->create_table(USER_PREDICTION,TRUE); 

         $fields = array(
          'is_pin' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'null' => FALSE,
            'comment' => '0=>not pin,1=>pinned',
            'default' => 0
            )
        );
        $this->prediction_forge->add_column(PREDICTION_MASTER, $fields);
      
        $notification_messages = array(
          'notification_type' => 174,
          'message' => 'You have received {{amount}} coins as Refund on canceling prediction by Admin',
          'en_message' => 'You have received {{amount}} coins as Refund on canceling prediction by Admin',
          'hi_message' => 'आप व्यवस्थापक द्वारा भविष्यवाणी रद्द करने {{amount}} वापसी के रूप में सिक्के प्राप्त हुआ है',
          'guj_message' => 'જો તમે Admin દ્વારા આગાહી રદ પર {{amount}} રીફંડ કારણ કે સિક્કા પ્રાપ્ત થઈ છે');

          $this->db_user->insert(NOTIFICATION_DESCRIPTION,$notification_messages);

          $notification_messages =array(
            array(
            'notification_type' => 175,
            'message' => '{{question}} Predict Now!',
            'en_message' => '{{question}} Predict Now!',
            'hi_message' => '{{question}} भविष्यवाणी करो!',
            'guj_message' => '{{question}} હવે ભવિષ્યવાણી!'),
            array(
              'notification_type' => 176,
              'message' => 'Hey! Use your skills and predict on {{match}} match, predictions is live now!',
              'en_message' => 'Hey! Use your skills and predict on {{match}} match, predictions is live now!',
              'hi_message' => 'अपने कौशल का प्रयोग करें और {{match}} मैच पर भविष्यवाणी, भविष्यवाणी लाइव है!',
              'guj_message' => 'હેય! તમારી કુશળતા વાપરો અને {{match}} મેચ પર આગાહી, આગાહીઓ હવે લાઇવ છે!'),
              array(
                'notification_type' => 183,
                'message' => 'Congratulations on predicting the right answer for {{home}} vs {{away}} match. Check out the results.',
                'en_message' => 'Congratulations on predicting the right answer for {{home}} vs {{away}} match. Check out the results.',
                'hi_message' => '{{home}} vs {{away}} मैच के लिए सही जवाब की भविष्यवाणी के लिए बधाई। परिणाम देखें।',
                'guj_message' => '{{home}} vs {{away}} મેચ માટે યોગ્ય જવાબ અનુમાન પર અભિનંદન. પરિણામો તપાસો.')
            
            ) ;
      
            $this->db_user->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

            $transaction_message = array(
              'source' => 174,
              'en_message' => 'Entry fee refund for prediction cancellation',
              'hi_message' => 'भविष्यवाणी रद्द करने के लिए प्रवेश शुल्क वापसी।',
              'guj_message' => 'આગાહી રદ એન્ટ્રી ફી રિફંડ');
    
              $this->db_user->insert(TRANSACTION_MESSAGES,$transaction_message);  

              //INSERT INTO `vi_email_template` (`email_template_id`, `template_name`, `subject`, `template_path`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES (NULL, 'Prediction Won', 'Prediction Winning', 'prediction-won', '183', '1', '0', NULL, NULL, 'Prediction Won', NULL, NULL);

              $email_temp = array(
                'template_name' => 'Prediction Won',
                'subject' => 'Prediction Winning',
                'template_path' => 'prediction-won',
                'notification_type' => 183,
                'status' => 1,
                'display_label' => 'Prediction Won'
              );
      
                $this->db_user->insert(EMAIL_TEMPLATE,$email_temp);  



  }

  public function down()
  {
	    //down script 
      $this->db_user->delete(MODULE_SETTING, array('name' => 'allow_prediction'));
      
      $this->prediction_forge->drop_table(PREDICTION_MASTER);
      $this->prediction_forge->drop_table(PREDICTION_OPTION);
      $this->prediction_forge->drop_table(USER_PREDICTION);


      $this->db_user->where_in('notification_type',array(175,176,183));
      $this->db_user->delete(NOTIFICATION_DESCRIPTION);

  }
}