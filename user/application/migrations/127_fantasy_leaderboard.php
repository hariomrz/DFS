<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fantasy_leaderboard extends CI_Migration{

  public function up(){

    //leaderboard category
    if(!$this->db->table_exists(LEADERBOARD_CATEGORY))
    {
      $fields = array(
        'category_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 150
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '0-Inactive,1-Active'
        ),
        'display_order' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 1
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('category_id', TRUE);
      $this->dbforge->create_table(LEADERBOARD_CATEGORY,FALSE,$attributes);

      $category = array(
                  array(
                    'category_id' => 1,
                    'name' => 'Referral',
                    'status'=> 0,
                    'display_order' => 1
                  ),
                  array(
                    'category_id' => 2,
                    'name' => 'Fantasy Points',
                    'status'=> 0,
                    'display_order' => 2
                  )
              );

      $this->db->insert_batch(LEADERBOARD_CATEGORY,$category);
    }
    
    //leaderboard_prize
    if(!$this->db->table_exists(LEADERBOARD_PRIZE))
    {
      $fields = array(
        'prize_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'category_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'type' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '1-Daily,2-Weekly,3-Monthly,4-League'
        ),
        'reference_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default' => 0,
          'comment' => 'League id or 0 for other leaderboard'
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 150
        ),
        'is_complete' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '0-Inactive,1-Active'
        ),
        'allow_prize' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'prize_detail' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        ),
        'allow_sponsor' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
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
        'custom_data' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('prize_id', TRUE);
      $this->dbforge->create_table(LEADERBOARD_PRIZE,FALSE,$attributes);
    }

    //leaderboard
    if(!$this->db->table_exists(LEADERBOARD))
    {
      $fields = array(
        'leaderboard_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'prize_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 150
        ),
        'prize_detail' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        ),
        'prize_date' => array(
          'type' => 'DATE',
          'null' => TRUE
        ),
        'entity_no' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default'=>'0'
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0,
          'comment' => '0-Pending,2-Complete,3-PrizeDistributed'
        ),
        'is_win_notify' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'start_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'end_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('leaderboard_id', TRUE);
      $this->dbforge->create_table(LEADERBOARD,FALSE,$attributes);
      
      //add unique key
      $query = "ALTER TABLE vi_leaderboard ADD UNIQUE (prize_id,entity_no, prize_date)";
      $this->db->query($query);
    }
    
    //leaderboard_history
    if(!$this->db->table_exists(LEADERBOARD_HISTORY))
    {
      $fields = array(
        'history_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'leaderboard_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'total_value' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'default'=>'0.00',
          'comment'=>'total count referral,fantasy points and etc'
        ),
        'rank_value' => array(
          'type' => 'INT',
          'constraint' => 11,
          'default'=>'0'
        ),
        'is_winner' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0
        ),
        'prize_data' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        ),
        'custom_data' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('history_id', TRUE);
      $this->dbforge->create_table(LEADERBOARD_HISTORY,FALSE,$attributes);

      //unique key
      $query = "ALTER TABLE vi_leaderboard_history  ADD UNIQUE (leaderboard_id,user_id)";
      $this->db->query($query);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "264")
            ->get()
            ->row_array();
    if(empty($row)) {
      $transaction_messages = array(
        array(
            'source' => 264, //Won in Nth Monthly Referral leaderboard
            'en_message'      => 'Won {{entity_no}}{{type}} referral leaderboard',
            'hi_message'      => '{{entity_no}}{{type}} रेफ़रल लीडरबोर्ड जीता',
            'guj_message'     => 'રેફરલ લીડરબોર્ડમાં {{entity_no}}{{type}} માં જીત્યાં',
            'fr_message'      => 'A remporté dans le classement des parrainages {{entity_no}}{{type}}',
            'ben_message'     => '{{entity_no}}{{type}} রেফারেল লিডারবোর্ডে on জিতেছে',
            'pun_message'     => 'ਜਿੱਤੀ {{entity_no}}{{type}} ਵਿੱਚ ਰੈਫਰਲ ਲੀਡਰਬੋਰਡ',
            'tam_message'     => '{{entity_no}}{{type}} பரிந்துரை லீடர்போர்டில் வென்றது',
            'th_message'      => 'ชนะ ในกระดานผู้นำการอ้างอิง {{entity_no}}{{type}}',
            'kn_message'      => '{{entity_no}}{{type}}ಉಲ್ಲೇಖಿತ ಲೀಡರ್‌ಬೋರ್ಡ್‌ನಲ್ಲಿ',
            'ru_message'      => 'Выиграл в таблице лидеров рефералов {{entity_no}}{{type}}',
            'id_message'      => 'Memenangkan di {{entity_no}}{{type}} leaderboard rujukan',
            'tl_message'      => 'Nanalo ng sa {{entity_no}}{{type}} na referral na leaderboard',
            'zh_message'      => '在 {{entity_no}}{{type}} 推荐排行榜中赢得',
        )
      );
      $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "265")
            ->get()
            ->row_array();
    if(empty($row)){
      $transaction_messages = array(
        array(
          'source' => 265,
          'en_message'      => 'Won {{entity_no}}{{type}} fantasy leaderboard',
          'hi_message'      => '{{entity_no}}{{type}} फैंटेसी लीडरबोर्ड जीता',
          'guj_message'     => '{{entity_no}}{{type}} માં જીત્યાં',
          'fr_message'      => 'A remporté dans le classement fantastique de {{entity_no}}{{type}}',
          'ben_message'     => '{{entity_no}}{{type}} কল্পনা লিডারবোর্ডে জিতে',
          'pun_message'     => 'ਜਿੱਤੀ {{entity_no}}{{type}} ਵਿੱਚ ਕਲਪਨਾ ਲੀਡਰਬੋਰਡ',
          'tam_message'     => '{{entity_no}}{{type}} கற்பனை லீடர்போர்டில் வென்றது',
          'th_message'      => 'ชนะ ในกระดานผู้นำแฟนตาซี {{entity_no}}{{type}}',
          'kn_message'      => '{{entity_no}}{{type}} ಫ್ಯಾಂಟಸಿ ಲೀಡರ್‌ಬೋರ್ಡ್‌ನಲ್ಲಿ ಗೆದ್ದಿದೆ',
          'ru_message'      => 'Выиграл в {{entity_no}}{{type}} таблице лидеров фэнтези',
          'id_message'      => 'Memenangkan di {{entity_no}}{{type}} papan peringkat fantasi',
          'tl_message'      => 'Nanalo ng sa {{entity_no}}{{type}} fantasboard',
          'zh_message'      => '在 {{entity_no}}{{type}} 幻想排行榜中赢得',
        )
      );
      $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }
    
    $leaderboard_notification_messages = array(
      array(
        "notification_type" =>264,
        "en_subject"=>"",
        "hi_subject"=>"",
        "guj_subject"=>"",
        "fr_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        // "tam_subject"=>"",
        "th_subject"=>"",
        "kn_subject"=>"",
        "ru_subject"=>"",
        "id_subject"=>"",
        "tl_subject"=>"",
        "zh_subject"=>"",
        "message"         => 'Congratulations! you won {{amount}} in {{entity_name}} referral leaderboard',
        'en_message'      => 'Congratulations! you won {{amount}} in {{entity_name}} referral leaderboard',
        'hi_message'      => 'बधाई हो! आपने जीता {{amount}} में {{entity_name}} रेफरल लीडरबोर्ड',
        'guj_message'     => 'અભિનંદન! તમે જીત્યા {{amount}} માં {{entity_name}} રેફરલ લીડરબોર્ડ',
        'fr_message'      => 'Félicitations! vous avez gagné {{amount}} quantité dans {{entity_name}} référence leaderboard',
        'ben_message'     => 'অভিনন্দন! আপনি জয়ী {{amount}} এ {{entity_name}} রেফারেল লিডারবোর্ডে',
        'pun_message'     => 'ਵਧਾਈ! ਤੁਹਾਨੂੰ ਜਿੱਤਿਆ {{amount}} {{entity_name}} ਰੈਫਰਲ ਲੀਡਰਬੋਰਡ',
        'tam_message'     => 'நீங்கள்! {{amount}} உள்ள {{entity_name}} பரிந்துரை முன்னிலை பெற்றது வாழ்த்துக்கள்',
        'th_message'      => 'ขอแสดงความยินดีคุณได้รับรางวัล {{amount}} ใน {{entity_name}} ประเภทอ้างอิงลีดเดอร์',
        'kn_message'      => 'ಅಭಿನಂದನೆಗಳು! ನೀವು ಸಾಧಿಸಿದೆ {{amount}} ನಲ್ಲಿ {{entity_name}} ಉಲ್ಲೇಖಿತ ಲೀಡರ್',
        'ru_message'      => 'Поздравляем! вы выиграли {{amount}} в {{entity_name}} направление лидеров',
        'id_message'      => 'Selamat Anda memenangkan {{amount}} di {{entity_name}} rujukan leaderboard',
        'tl_message'      => 'Congratulations! nanalo ka {{amount}} sa {{entity_name}} referral leaderboard',
        'zh_message'      => '恭喜你中了{{amount}}在{{entity_name}}推荐榜',
      ),
      array(
        "notification_type" =>265,
        "en_subject"=>"",
        "hi_subject"=>"",
        "guj_subject"=>"",
        "fr_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        // "tam_subject"=>"",
        "th_subject"=>"",
        "kn_subject"=>"",
        "ru_subject"=>"",
        "id_subject"=>"",
        "tl_subject"=>"",
        "zh_subject"=>"",
        "message"         => 'Congratulations! you won {{amount}} in {{entity_name}} fantasy leaderboard',
        'en_message'      => 'Congratulations! you won {{amount}} in {{entity_name}} fantasy leaderboard',
        'hi_message'      => 'बधाई हो! आपने जीता {{amount}} में {{entity_name}} कल्पना लीडरबोर्ड',
        'guj_message'     => 'અભિનંદન! તમે જીત્યા {{amount}} માં {{entity_name}} કાલ્પનિક લીડરબોર્ડ',
        'fr_message'      => 'Félicitations! vous avez gagné {{amount}} quantité dans {{entity_name}} fantaisie leaderboard',
        'ben_message'     => 'অভিনন্দন! আপনি জয়ী {{amount}} এ {{entity_name}} কল্পনা লিডারবোর্ডে',
        'pun_message'     => 'ਵਧਾਈ! ਤੁਹਾਨੂੰ ਜਿੱਤਿਆ {{amount}} {{entity_name}} fantasy ਲੀਡਰਬੋਰਡ',
        'tam_message'     => 'நீ வென்றாய் வாழ்த்துகள்!, {{amount}} உள்ள {{entity_name}} கற்பனை முன்னிலை',
        'th_message'      => 'ขอแสดงความยินดีคุณได้รับรางวัล {{amount}} ใน {{entity_name}} entity_no ลีดเดอร์จินตนาการ',
        'kn_message'      => 'ನೀವು ಸಾಧಿಸಿದೆ ಅಭಿನಂದನೆಗಳು! {{amount}} ನಲ್ಲಿ {{entity_name}} ಫ್ಯಾಂಟಸಿ ಲೀಡರ್',
        'ru_message'      => 'Поздравляем! вы выиграли {{amount}} в {{entity_name}} фантазии лидеров',
        'id_message'      => 'Selamat! Anda memenangkan {{amount}} di {{entity_name}} leaderboard fantasi',
        'tl_message'      => 'Congratulations! nanalo ka {{amount}} sa {{entity_name}} fantasy leaderboard',
        'zh_message'      => '恭喜你中了{{amount}}在{{entity_name}}幻想排行榜',
        ),
    );
    $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$leaderboard_notification_messages);     
  }

  public function down(){
    //down script
  }
}
