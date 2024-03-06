<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Spinthewheel extends CI_Migration {

  public function up()
  {

        $fields = array(
          'spinthewheel_id' => array(
              'type' => 'INT',
              'constraint' => 10,
              'auto_increment' => TRUE,
              'null' => FALSE
          ),
          'slice_name' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE
          ),
          'amount' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
          ),
          'type' => array(
              'type' => 'VARCHAR',
              'constraint' => 50,
              'null' => FALSE
          ),
          'win' => array(
            'type' => 'INT',
            'constraint' => 10,
            'null' => FALSE
          ),
          'result_text' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE
          ),
          'probability' => array(
            'type' => 'INT',
            'constraint' => 10,
            'null' => FALSE
          ),
          'cash_type' => array(
            'type' => 'INT',
            'constraint' => 10,
            'null' => FALSE
          ),
          'status' => array(
            'type' => 'ENUM("0","1")',
            'default' => '0',
            'null' => FALSE
          ),
          'created_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE,
            'default' => NULL
          ),
          'updated_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE,
            'default' => NULL
          )
      );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('spinthewheel_id',TRUE);
      $this->dbforge->create_table(SPIN_THE_WHEEL ,FALSE,$attributes);



      $spinthewheel_insert_data =array(
        array(
          'slice_name' => "45 Real Cash",
          'amount' => "45",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 45 Real Cash",
          'probability' => 31,
          'cash_type' => 0,
          'status' => '1'
         ),array(
          'slice_name' => "29 Bonus Cash",
          'amount' => "29",
          'type' => "string",
          'win' => '1',
          'result_text' => "You won 29 Bonus Cash",
          'probability' => 24,
          'cash_type' => 1,
          'status' => '1'
         ),array(
          'slice_name' => "36 Real Cash",
          'amount' => "36",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 36 Real Cash",
          'probability' => 45,
          'cash_type' => 0,
          'status' => '1'
         ),array(
          'slice_name' => "Angry Bird Sticker",
          'amount' => "20",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won Angry Bird Sticker",
          'probability' => 99,
          'cash_type' => 3,
          'status' => '1'
         ),array(
          'slice_name' => "20 Coins",
          'amount' => "20",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 20 Coins",
          'probability' => 21,
          'cash_type' => 2,
          'status' => '1'
         ),array(
          'slice_name' => "100 Bonus Cash",
          'amount' => "100",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 20 Coins",
          'probability' => 80,
          'cash_type' => 1,
          'status' => '1'
         ),array(
          'slice_name' => "20 Coins",
          'amount' => "20",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 20 Coins",
          'probability' => 90,
          'cash_type' => 2,
          'status' => '1'
         ),array(
          'slice_name' => "36 Coins",
          'amount' => "36",
          'type' => "string",
          'win' => 1,
          'result_text' => "You won 36 Coins",
          'probability' => 90,
          'cash_type' => 2,
          'status' => '0'
         )
      );
      $this->db->insert_batch(SPIN_THE_WHEEL,$spinthewheel_insert_data);


      $claim = array(
          'spin_claimed_id' => array(
              'type' => 'INT',
              'constraint' => 10,
              'auto_increment' => TRUE,
              'null' => FALSE
          ),
          'user_id' => array(
              'type' => 'INT',
              'constraint' => 10,
              'null' => FALSE
          ),
          'claimed_date' => array(
             'type' => 'DATETIME',
              'null' => TRUE,
              'default' => NULL
          ),
          'spin_json' => array(
            'type' => 'json',
            'null' => TRUE,
            'default' => NULL
          )
      );

      $claim_attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($claim);
      $this->dbforge->add_key('spin_claimed_id',TRUE);
      $this->dbforge->create_table(SPIN_CLAIMED ,FALSE,$claim_attributes);




       // 0 Real, 1 Bonus Amount, 2 Points, 5 Merchendies 
      //up script for notification descriptins  
      $notification_messages =array(
        array(
          'notification_type' => 411,
          'message' => 'Wohoo! You won {{amount}} coins in spin the wheel',
          'en_message' => "Wohoo! You won {{amount}} coins in spin the wheel",
          'hi_message' => "वू हू! आपने व्हील को स्पिन करने के लिए {{amount}} के सिक्के जीते",
          'guj_message' => "વહુ! તમે સ્પિન વ્હીલમાં {{amount}} સિક્કા જીત્યા",
          'fr_message' => "Wohoo! Vous avez gagné {{amount}} pièces en faisant tourner la roue",
          'ben_message' => "ওহু! আপনি স্পিনে {{amount}} কয়েন জিতেছেন",
          'pun_message' => "ਵਾਹ! ਤੁਸੀਂ ਸਪਿਨ ਚੱਕਰ ਵਿਚ {{amount}} ਸਿੱਕੇ ਜਿੱਤੇ",
          //'tam_message' => "வூஹூ! சக்கரத்தை சுழற்ற நீங்கள் {{amount}} ins நாணயங்களை வென்றீர்கள்",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         ),array(
          'notification_type' => 412,
          'message' => 'Wohoo! You won {{amount}} real money in spin the wheel',
          'en_message' => "Wohoo! You won {{amount}} real money in spin the wheel",
          'hi_message' => "वू हू! आपने व्हील को स्पिन करने में {{amount}} असली पैसा जीता",
          'guj_message' => "વહુ! તમે સ્પિન વ્હીલમાં {{amount}} વાસ્તવિક પૈસા જીત્યા",
          'fr_message' => "Woohoo! Vous avez gagné {{amount}} de l'argent réel en faisant tourner la roue",
          'ben_message' => "ওহু! আপনি চাকা স্পিনে {{amount}} আসল টাকা জিতেছেন",
          'pun_message' => "ਵਾਹ! ਤੁਸੀਂ ਸਪਿਨ ਚੱਕਰ ਵਿਚ {{amount}} ਅਸਲ ਪੈਸਾ ਜਿੱਤਿਆ",
          //'tam_message' => "வூஹூ! சக்கரத்தை சுழற்ற நீங்கள் {{amount}} உண்மையான பணத்தை வென்றீர்கள்",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         ),array(
          'notification_type' => 413,
          'message' => 'Wohoo! You won {{amount}} bonus in spin the wheel',
          'en_message' => "Wohoo! You won {{amount}} bonus in spin the wheel",
          'hi_message' => "वू हू! आपने व्हील को स्पिन करने में {{amount}} बोनस जीता",
          'guj_message' => "વહુ! તમે સ્પિન વ્હીલમાં {{amount}} બોનસ જીત્યો",
          'fr_message' => "Wohoo! Vous avez gagné {{amount}} bonus en faisant tourner la roue",
          'ben_message' => "ওহু! আপনি চাকা স্পিনে {{amount}} বোনাস জিতেছেন",
          'pun_message' => "ਵਾਹ! ਤੁਸੀਂ ਸਪਿਨ ਚੱਕਰ ਵਿਚ {{amount}} ਬੋਨਸ ਜਿੱਤੀ",
          //'tam_message' => "வூஹூ! சக்கரத்தை சுழற்ற நீங்கள் {{amount}} போனஸை வென்றீர்கள்",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         ),array(
          'notification_type' => 414, 
          'message' => 'Wohoo! You won {{name}} in spin the wheel',
          'en_message' => "Wohoo! You won {{name}} in spin the wheel",
          'hi_message' => "वू हू! आपने पहिया को स्पिन करने में {{name}} जीता",
          'guj_message' => "વહુ! તમે સ્પિન વ્હીલમાં {{name}} won જીત્યાં",
          'fr_message' => "Wohoo! Vous avez gagné {{name}} en faisant tourner la roue",
          'ben_message' => "ওহু! আপনি {{name}} চক্র স্পিন জিতেছে",
          'pun_message' => "ਵਾਹ! ਤੁਸੀਂ ਚੱਕਰ ਵਿੱਚ {{name}} won ਜਿੱਤੇ",
          //'tam_message' => "வூஹூ! நீங்கள் சுழற்சியில் {{name}} வென்றீர்கள்",
          //'es_message'  => "¡Felicidades! {{friend_name}} referido por que se ha unido a un concurso. Se han ganado ₹ {{amount}} de dinero real."
         )
      );
      $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_messages);

      $module_setting =array(
        array(
          'name' => 'allow_spin',
          'display_label' => 'Spin Wheel',
          'status' => 0
         )
      );
      $this->db->insert_batch(MODULE_SETTING,$module_setting);

 
  }

  public function down()
  {
      //down scripts 
      $this->db->where_in('notification_type',array(411,412,413,414));
      $this->db->delete(NOTIFICATION_DESCRIPTION);

      //down scripts 
      $this->db->where_in('name',array('allow_spin'));
      $this->db->delete(MODULE_SETTING);

      //Table drop
      $this->dbforge->drop_table(SPIN_THE_WHEEL);
      $this->dbforge->drop_table(SPIN_CLAIMED);

  }

}
