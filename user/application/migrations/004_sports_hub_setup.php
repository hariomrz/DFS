<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sports_hub_setup extends CI_Migration {

  public function up()
  {
    $fields = array(
        'sports_hub_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                //'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'game_key' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => FALSE,
        ),
        'en_title' => array(
            'type' => 'VARCHAR',
            'constraint' => 100,
            'null' => FALSE,
          ),
          'hi_title' => array(
            'type' => 'VARCHAR',
            'constraint' => 100,
            'null' => FALSE, 
          ),
          'guj_title' => array(
            'type' => 'VARCHAR',
            'constraint' => 100,
            'null' => FALSE, 
          ),
          'en_desc' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          ),
          'hi_desc' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          ),
          'guj_desc' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          ),
        'image' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'null' => FALSE,
          'comment' => '0=>inactive,1=>active'
        )
        );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('sports_hub_id',TRUE);
      $this->dbforge->create_table(SPORTS_HUB ,FALSE,$attributes);   


      $hub_setting = 
      array(
          array(
              'en_title' => "TOURNAMENT MODE",
              'game_key' => 'allow_tournament',
              'hi_title'=> "टूर्नामेन्ट मोड",
              'guj_title' => 'ટુર્નામેન્ટ સ્થિતિ',
              'en_desc' => "Pro Season Long Player? Play for the entire season here",
              'hi_desc' => "प्रो सीजन लांग प्लेयर? पूरे सीजन के लिए खेलते हैं यहां",
              'guj_desc' => "પ્રો સિઝન લાંબા પ્લેયર? સમગ્ર સિઝન માટે રમો અહીં",
              'image' => "tournament.png",
              'status' => 0
          ),
          array(
            'en_title' => "DAILY FANTASY SPORTS",
            'game_key' => 'allow_dfs',
            'hi_title'=> "दैनिक फंतासी खेल",
            'guj_title' => 'દૈનિક કાલ્પનિક રમતો',
            'en_desc' => "Daily fantasy sports is much more exciting than traditional fantasy sports",
            'hi_desc' => "दैनिक काल्पनिक खेल पारंपरिक काल्पनिक खेल की तुलना में अधिक रोमांचक है",
            'guj_desc' => "દૈનિક કાલ્પનિક રમતો પરંપરાગત કાલ્પનિક રમતો કરતાં વધુ આકર્ષક છે",
            'image' => "daily.png",
            'status' => 1
          ),
          array(
            'en_title' => "PREDICT & WIN COINS",
            'game_key' => 'allow_prediction',
            'hi_title'=> "भविष्यवाणी करें और सिक्के जीतो",
            'guj_title' => 'અનુમાન & સિક્કા જીતવા',
            'en_desc' => "No fantasy skills required. Just predict the outcome and win coins",
            'hi_desc' => "कोई कल्पना कौशल की आवश्यकता है। बस परिणाम की भविष्यवाणी और सिक्के जीतने",
            'guj_desc' => "કોઈ કાલ્પનિક કુશળતા જરૂરી છે. જસ્ટ પરિણામ આગાહી અને સિક્કા જીતવા",
            'image' => "prediction.png",
            'status' => 0
          ),
          array(
            'en_title' => "PICK'EM",
            'game_key' => 'allow_pickem',
            'hi_title'=> "टूर्नामेन्ट मोड",
            'guj_title' => 'ટુર્નામેન્ટ સ્થિતિ',
            'en_desc' => "Game play is super easy. Just pick the winning side",
            'hi_desc' => "खेल खेलने के सुपर आसान है। बस जीतने पक्ष लेने",
            'guj_desc' => "આ રમત રમવા સુપર સરળ છે. જસ્ટ વિજેતા બાજુ પસંદ",
            'image' => "pickem.png",
            'status' => 0
          )
      );
      $this->db->insert_batch(SPORTS_HUB,$hub_setting);


  }

  public function down()
  {
	//down script 
	$this->dbforge->drop_table(SPORTS_HUB);
  }
}