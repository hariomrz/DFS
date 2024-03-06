<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Coin_package extends CI_Migration {

  public function up()
  {

        $fields = array(
          'coin_package_id' => array(
              'type' => 'INT',
              'constraint' => 10,
              //'unsigned' => TRUE,
              'auto_increment' => TRUE,
              'null' => FALSE
          ),
          'coins' => array(
            'type' => 'INT',
            'constraint' => 10,
            'null' => FALSE,
          ),
          'amount' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
          ),
          'package_name' => array(
              'type' => 'VARCHAR',
              'constraint' => 255,
              'null' => FALSE,
          ),
          'status' => array(
            'type' => 'ENUM("0","1")',
            'default' => '0',
            'null' => FALSE,
          ),
          'created_date' => array(
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

      $attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('coin_package_id',TRUE);
      $this->dbforge->create_table(COIN_PACKAGE ,FALSE,$attributes);


      //Default Package insert
      $default_package = 
      array(
          array(
              'coins' => "200",
              'amount' => '0.50',
              'package_name'=> "Coin Earn 200 from Rs 0.50",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          ),array(
              'coins' => "500",
              'amount' => '1.25',
              'package_name'=> "Coin Earn 500 from Rs 1.25",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          ),array(
              'coins' => "1000",
              'amount' => '2.5',
              'package_name'=> "Coin Earn 1000 from Rs 2.5",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          ),array(
              'coins' => "3000",
              'amount' => '7.5',
              'package_name'=> "Coin Earn 3000 from Rs 7.5",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          ),array(
              'coins' => "5000",
              'amount' => '12.5',
              'package_name'=> "Coin Earn 5000 from Rs 12.5",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          ),array(
              'coins' => "10000",
              'amount' => '25',
              'package_name'=> "Coin Earn 10000 from Rs 25",
              'status' => '1',
              'created_date' => '2020-09-10 01:00:00',
              'updated_date' => '2020-09-10 01:00:00'
          )
      );
      $this->db->insert_batch(COIN_PACKAGE,$default_package);


      //up script for notification descriptins  
      $notification_messages =array(
        array(
          'notification_type' => 331,
          'message' => 'Wohoo! Coin {{coins}} is credited to your coin balance on your coin purchase.',
          'en_message' => 'Wohoo! Coin {{coins}} is credited to your coin balance on your coin purchase.',
          'hi_message' => 'Wohoo! सिक्का {{coins}} आपके सिक्का खरीद पर आपके सिक्का संतुलन के लिए श्रेय दिया जाता है।',
          'guj_message' => 'વહુ! સિક્કો {{coins}} તમારી સિક્કો ખરીદી પરના તમારા સિક્કો સિલકને જમા કરવામાં આવે છે.',
          'fr_message' => 'Wohoo! La pièce {{coins}} est créditée sur votre solde de pièces lors de votre achat de pièces.',
          'ben_message' => "ওহু! কয়েন {{coins}} আপনার মুদ্রা ক্রয়ে আপনার মুদ্রা ব্যালেন্সে জমা হয়।",
          'pun_message' => "ਵਾਹ! ਸਿੱਕਾ {{coins}} ਤੁਹਾਡੇ ਸਿੱਕੇ ਦੀ ਖਰੀਦ 'ਤੇ ਤੁਹਾਡੇ ਸਿੱਕੇ ਦੀ ਸੰਤੁਲਨ ਵਿੱਚ ਜਾਂਦਾ ਹੈ.",
          'tam_message' => "வூஹூ! உங்கள் நாணயம் வாங்கியதில் நாணயம் {{coins}} உங்கள் நாணய இருப்புக்கு வரவு வைக்கப்படுகிறது.",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         ),array(
          'notification_type' => 332,
          'message' => 'Wallet is debited {{amount}} for {{coins}} coins on coins purchase.',
          'en_message' => 'Wallet is debited {{amount}} for {{coins}} coins on coins purchase.',
          'hi_message' => '{{amount}} सिक्कों की खरीद के लिए वॉलेट में {{amount}} से डेबिट किया जाता है।',
          'guj_message' => '{{amount}} સિક્કાઓની ખરીદી માટે વletલેટ {{amount}} ડ debબિટ છે.',
          'fr_message' => "Le portefeuille est débité de {{amount}} pour l'achat de {{coins}} pièces.",
          'ben_message' => "{{amount}} মুদ্রা কেনার জন্য ওয়ালেট {{coins}} টি ডেবিট করা হয়।",
          'pun_message' => "ਵਾਲਿਟ ਨੂੰ {{amount}} ਸਿੱਕਿਆਂ ਦੀ ਖਰੀਦ ਲਈ {{coins}} ਵਿੱਚ ਡੈਬਿਟ ਕੀਤਾ ਗਿਆ ਹੈ.",
          'tam_message' => "நாணயங்கள் வாங்கும்போது {{amount}} நாணயங்களுக்கு வாலட் {{coins}} பற்று வைக்கப்படுகிறது.",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         )
      );
      $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);
 
  }

  public function down()
  {
      //down scripts 
      $this->db->where_in('notification_type',array(331,332));
      $this->db->delete(NOTIFICATION_DESCRIPTION);

      //Table drop
      $this->dbforge->drop_table(COIN_PACKAGE);

  }

}
