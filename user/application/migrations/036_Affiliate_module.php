<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Affiliate_module extends CI_Migration {

    public function up() {

        $fields = array(
              'is_affiliate'=>array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'comment' => '0=>no-affiliate,1=>active,2=>inactive,3=>blocked'
              ),
              'signup_commission' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default'=>NULL,
                'comment'=>'signup commission of the day'
            ),
                'deposit_commission' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default'=>NULL,
                'comment'=>'deposit commission of the day'
            ),
            'affiliate_narration'=>array(
                'type' => 'varchar',
                'constraint' => 100,
                'default' => NULL
            ),
            'affiliate_date'=>array(
                'type' => 'DATETIME',
                'default' => NULL, 
            ),
                'affiliate_user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => NULL,
                'comment'=>'user id of from affiliate'
            ),
          );

          $this->dbforge->add_column(USER,$fields);
         
          $fields = array(
            'is_affiliate'=>array(
              'type' => 'TINYINT',
              'constraint' => 1,
              'null' => FALSE,
              'default' => 0,
              'comment' => '0=>normal_user,1=>affiliated_user'
            ),
        );
        $this->dbforge->add_column(USER_AFFILIATE_HISTORY,$fields);

        $value = array(
        array(
        "notification_type" =>420,
        "message" => 'User signup through your affiliate program and you got {{amount}}',
        "en_message"=>'User signup through your affiliate program and you got {{amount}}', 
        "hi_message"=>'अपके संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता साइनअप और आप मिल गया {{amount}}',
        "tam_message"=> 'உங்கள் இணைப்பு திட்டம் மூலம் பயனர் இணைந்ததற்கு நீங்கள் கிடைத்தது {{amount}}',
        "ben_message"=>'আপনার অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর সাইনআপ এবং আপনি পেয়েছেন {{amount}}',
        "pun_message"=>'ਆਪਣੇ ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਦੁਆਰਾ ਯੂਜ਼ਰ ਸਾਇਨਅਪ ਅਤੇ ਤੁਹਾਨੂੰ ਮਿਲੀ {{amount}} ਦੀ ਰਕਮ',
        "fr_message"=>'inscription de l\'utilisateur grâce à votre programme d\'affiliation et vous avez {{amount}} quantité',
        "guj_message"=>'તમારા સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા સાઇનઅપ અને તમે મળી {{amount}}',
        ),
        array(
            "notification_type"=>421,
            "message" => 'User deposit through your affiliate program and you got {{amount}}  ',
            "en_message"=>'User deposit through your affiliate program and you got {{amount}}  ', 
            "hi_message"=>'अपके संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता जमा और आप मिल गया {{amount}}',
            "tam_message"=> 'உங்கள் இணைப்பு திட்டம் மூலம் பயனர் வைப்பு மற்றும் நீங்கள் கிடைத்தது {{amount}}',
            "ben_message"=>'আপনার অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর আমানত এবং আপনি পেয়েছেন {{amount}}',
            "pun_message"=>'ਆਪਣੇ ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਯੂਜ਼ਰ ਨੂੰ ਪੇਸ਼ਗੀ ਅਤੇ ਤੁਹਾਨੂੰ ਮਿਲੀ {{amount}} ਦੀ ਰਕਮ',
            "fr_message"=>'Dépôt de l\'utilisateur grâce à votre programme d\'affiliation et vous avez {{amount}} quantité',
            "guj_message"=>'તમારા સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા થાપણ અને તમે મળી {{amount}}',
            )
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$value);

          $affiliate_link_notification = 
                array(
                    array( 
                        'notification_type' => 422,
                        'template_name'=> 'affiliate-link',
                        'subject' => 'Affiliate Link',
                        'template_path' => 'affiliate-link',
                        'status' => 1,
                        'type' => 0,
                        'display_label' => 'Affiliate Link',
                        'date_added' => '2020-11-09 03:15:41'
                    ),
                );
        $this->db->insert_batch(EMAIL_TEMPLATE,$affiliate_link_notification);

        $transaction_messages = array(
            array(
                'source' => 320,
                'en_message' => 'Commission for user signup through affiliate program',
                'hi_message' => 'संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता साइनअप के लिए आयोग',
                "tam_message"=> 'தொடர்புடைய திட்டம் மூலம் பயனர் பதிவுசெய்யப் ஆணையம்',
                "ben_message"=>'অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর সাইনআপ জন্য কমিশন',
                "pun_message"=>'ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਦੁਆਰਾ ਯੂਜ਼ਰ ਨੂੰ ਸਾਇਨਅਪ ਲਈ ਕਮਿਸ਼ਨ',
                "fr_message"=>'Commission pour inscription utilisateur à travers le programme d\'affiliation',
                "guj_message"=>'સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા સાઇનઅપ માટે કમિશન',
            ),
            array(
                'source' => 321,
                'en_message' => 'Commission for user deposit through affiliate program',
                'hi_message' => 'संबद्ध कार्यक्रम के माध्यम से उपयोगकर्ता जमा करने के लिए आयोग',
                "tam_message"=> 'தொடர்புடைய திட்டம் மூலம் பயனர் வைப்பு கமிஷன்',
                "ben_message"=>'অধিভুক্ত প্রোগ্রাম মাধ্যমে ব্যবহারকারীর আমানত জন্য কমিশন',
                "pun_message"=>'ਐਫੀਲੀਏਟ ਪ੍ਰੋਗਰਾਮ ਦੁਆਰਾ ਯੂਜ਼ਰ ਨੂੰ ਡਿਪਾਜ਼ਿਟ ਲਈ ਕਮਿਸ਼ਨ',
                "fr_message"=>'Commission pour le dépôt de l\'utilisateur à travers le programme d\'affiliation',
                "guj_message"=>'સંલગ્ન કાર્યક્રમ દ્વારા વપરાશકર્તા ડિપોઝિટ માટે કમિશન',
            ),
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
             
    }
    
    public function down() {
        	//down script 
        // $this->dbforge->drop_table('user_affiliate_records');
        $this->dbforge->drop_column(USER, 'is_affiliate');
        $this->dbforge->drop_column(USER, 'signup_commission');
        $this->dbforge->drop_column(USER, 'deposit_commission');
        $this->dbforge->drop_column(USER, 'affiliate_narration');
        $this->dbforge->drop_column(USER, 'affiliate_date');
        $this->dbforge->drop_column(USER, 'affiliate_user_id');
        $this->dbforge->drop_column(USER_AFFILIATE_HISTORY, 'is_affiliate');
        $this->db->where('notification_type',422)->delete(EMAIL_TEMPLATE);
        $this->db->where_in('source',[320,321])->delete(TRANSACTION_MESSAGES);
        $this->db->where_in('notification_type',[420,421])->delete(NOTIFICATION_DESCRIPTION);
    }
}
