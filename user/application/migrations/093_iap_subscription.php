<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Iap_subscription extends CI_Migration {

    public function up() {

        $subscription_fields = array(
			'subscription_id'   => array(
				'type'            => 'INT',
				'constraint'      => 11,
				'unsigned'        => TRUE,
				'auto_increment'  => TRUE
			),
			'name'          => array(
				'type'            => 'VARCHAR',
				'constraint'      => 20,
				'null'            => FALSE,
			),
			'amount'        => array(
				'type'            => 'INT',
				'constraint'      => 11,
        'null'            => FALSE,
        'comment' => 'amount paid by user to purchase coins'
      ),
      'coins'             => array(
				'type'            => 'INT',
				'constraint'      => 11,
        'null'            => FALSE,
        'comment' => 'purchased by user'
			),
			'android_id'        => array(
			  'type'            => 'VARCHAR',
			  'constraint'      => 255,
        'null'            => TRUE,
        'default'         => NULL,
      ),
      'ios_id'        => array(
			  'type'            => 'VARCHAR',
			  'constraint'      => 255,
        'null'            => TRUE,
        'default'         => NULL,
      ),
      'duration'             => array(
				'type'            => 'INT',
				'constraint'      => 11,
        'null'            => FALSE,
        'default'         => 1
			),
			'status'            => array(
			  'type'            => 'ENUM("0","1")',
			  'default'         => '0',
			  'null'            => FALSE
      ),
      'is_deleted'            => array(
			  'type'            => 'ENUM("0","1")',
			  'default'         => '0',
        'null'            => FALSE,
        'comment' => '0-not deleted package, 1- deleted package'
			),
			'added_date'      => array(
			  'type'            => 'DATETIME',
			  'null'            => TRUE,
			  'default'         => NULL
			),
			'modified_date'      => array(
			  'type'            => 'DATETIME',
			  'null'            => TRUE,
			  'default'         => NULL
			)
        );
        
        $attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($subscription_fields);
		$this->dbforge->add_key('subscription_id', TRUE);
    $this->dbforge->create_table(SUBSCRIPTION,FALSE,$attributes);

    $user_subscription_fields = array(
        'id'   => array(
				'type'            => 'INT',
				'constraint'      => 11,
				'unsigned'        => TRUE,
				'auto_increment'  => TRUE,
      ),
      'user_id'         => array(
				'type'            => 'INT',
        'constraint'      => 11,
        'null'            =>FALSE,
      ),
      'subscription_id'   => array(
				'type'            => 'INT',
        'constraint'      => 11,
        'null'            =>FALSE,
      ),
      'receipt_id'   => array(
				'type'            => 'VARCHAR',
        'constraint'      => 255,
        'null'            =>FALSE,
      ),
      'type'            => array(
			  'type'            => 'TINYINT',
			  'constraint'      => 1,
        'null'            => FALSE,
        'comment' => '1-Android,2-ios'
      ),
      'status'            => array(
			  'type'            => 'ENUM("0","1")',
			  'default'         => '0',
			  'null'            => FALSE
      ),
      'start_date'      => array(
			  'type'            => 'DATETIME',
			  'null'            => TRUE,
			  'default'         => NULL
			),
			'expiry_date'      => array(
			  'type'            => 'DATETIME',
			  'null'            => TRUE,
			  'default'         => NULL
			)
    );
    $attributes = array('ENGINE'=>'InnoDB');
		$this->dbforge->add_field($user_subscription_fields);
		$this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table(USER_SUBSCRIPTION,FALSE,$attributes);
    
    // $sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SUBSCRIPTION)." ADD UNIQUE KEY product_id (product_id,product_type);";
    // $this->db->query($sql);
    

        $value = array(
        array(
          'notification_type'		=>'437',
          'message'				  =>'Yay! Coin package successfully subscribed.{{amount}} coins are credited in balance',
          'en_message'			=>'Yay! Coin package successfully subscribed.{{amount}} coins are credited in balance',
          'hi_message'			=>'वाह! सिक्का पैकेज सफलतापूर्वक सदस्यता ली गई। {{amount}} सिक्के को संतुलन में श्रेय दिया जाता है',
          'guj_message'			=>'હા! સિક્કો પેકેજ સફળતાપૂર્વક સબ્સ્ક્રાઇબ કર્યું. {{amount}} સિક્કા સંતુલનમાં આપવામાં આવે છે',
          'fr_message'			=>'Yay! Paquet de monnaie souscrit avec succès. {{amount}} monnaie sont créditées en solde',
          'ben_message'			=>'হ্যাঁ! মুদ্রা প্যাকেজ সফলভাবে সাবস্ক্রাইব। {{amount}} কয়েন ভারসাম্য জমা দেওয়া হয়',
          'pun_message'			=>'ਯੇ! ਸਿੱਕਾ ਪੈਕੇਜ ਸਫਲਤਾਪੂਰਵਕ ਸਬਸਕ੍ਰਾਈਬ ਕੀਤੀ ਗਈ ਹੈ. {{amount}} ਸੰਤੁਲਨ ਵਿੱਚ ਜਮ੍ਹਾ ਕੀਤਾ ਜਾਂਦਾ ਹੈ',
          'tam_message'			=>'யா! நாணயம் தொகுப்பு வெற்றிகரமாக சந்தாதாரராக. {{amount}} நாணயங்கள் சமநிலையில் வரவு வைக்கப்படுகின்றன',
          'th_message'			=>'เย้! แพคเกจเหรียญสำเร็จแล้ว {{amount}} เหรียญจะได้รับเครดิตในสมดุล',
          'kn_message'      =>'ವಾಹ್! ನಾಣ್ಯ ಪ್ಯಾಕೇಜ್ ಯಶಸ್ವಿಯಾಗಿ ಚಂದಾದಾರರಾಗಿ. {{amount}} ನಾಣ್ಯಗಳನ್ನು ಸಮತೋಲನದಲ್ಲಿ ಸಲ್ಲುತ್ತದೆ',
          'tl_message'			=>'Yay! Matagumpay na naka-subscribe ang barya. {{amount}} Ang mga barya ay kredito sa balanse',
          'ru_message'			=>'Ура! Пакет монет успешно подписался. {{amount}} Монетки зачисляются в балансе',
          'id_message'			=>'Yay! Paket koin berhasil berlangganan. {{amount}} Koin dikreditkan secara saldo',
          'zh_message'			=>'好极了！硬币包装成功订阅。{{amount}}硬币兑换',
        ),
        array(
          'notification_type'		=>'438',
          'message'				  =>'Coin package subscription is canceled!',
          'en_message'			=>'Coin package subscription is canceled!',
          'hi_message'			=>'सिक्का पैकेज सदस्यता रद्द कर दी गई है!',
          'guj_message'			=>'સિક્કો પેકેજ સબ્સ્ક્રિપ્શન રદ કરવામાં આવે છે!',
          'fr_message'			=>'L\'abonnement à la pièce de monnaie est annulé!',
          'ben_message'			=>'মুদ্রা প্যাকেজ সাবস্ক্রিপশন বাতিল করা হয়!',
          'pun_message'			=>'ਸਿੱਕਾ ਪੈਕੇਜ ਗਾਹਕੀ ਰੱਦ ਕੀਤੀ ਗਈ ਹੈ!',
          'tam_message'			=>'நாணயம் தொகுப்பு சந்தா ரத்து செய்யப்பட்டது!',
          'th_message'			=>'การสมัครสมาชิกแพคเกจเหรียญถูกยกเลิกแล้ว!',
          'kn_message'      =>'ನಾಣ್ಯ ಪ್ಯಾಕೇಜ್ ಚಂದಾದಾರಿಕೆಯನ್ನು ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ!',
          'tl_message'			=>'Kinansela ang subscription sa pakete ng barya!',
          'ru_message'			=>'Подписка для пакета монет отменяется!',
          'id_message'			=>'Berlangganan paket koin dibatalkan!',
          'zh_message'			=>'封装订阅已取消！',
        ),
        array(
          'notification_type'		=>'439',
          'message'				  =>'Your coin package is renewed successfully,{{amount}} coins are credited in balance',
          'en_message'			=>'Your coin package is renewed successfully,{{amount}} coins are credited in balance',
          'hi_message'			=>'आपका सिक्का पैकेज सफलतापूर्वक नवीनीकृत किया गया है, {{amount}} सिक्के को संतुलन में श्रेय दिया जाता है',
          'guj_message'			=>'તમારા સિક્કા પેકેજ સફળતાપૂર્વક નવીકરણ કરવામાં આવે છે, {{amount}} સિક્કા સંતુલનમાં આપવામાં આવે છે',
          'fr_message'			=>'Votre package de pièces est renouvelé avec succès, {{amount}} pièces sont créditées en équilibre',
          'ben_message'			=>'আপনার মুদ্রা প্যাকেজ সফলভাবে পুনর্নবীকরণ করা হয়, {{amount}} কয়েন ভারসাম্য জমা দেওয়া হয়',
          'pun_message'			=>'ਤੁਹਾਡਾ ਸਿੱਕਾ ਪੈਕੇਜ ਸਫਲਤਾਪੂਰਵਕ ਨਵੀਨੀਕਰਣ ਕੀਤਾ ਜਾਂਦਾ ਹੈ, {{amount}} ਸਿੱਕੇ ਨੂੰ ਸੰਤੁਲਨ ਵਿੱਚ ਜਮ੍ਹਾ ਕੀਤਾ ਜਾਂਦਾ ਹੈ',
          'tam_message'			=>'உங்கள் நாணய தொகுப்பு வெற்றிகரமாக புதுப்பிக்கப்பட்டது, {{amount}} நாணயங்கள் சமநிலையில் வரவு வைக்கப்படுகின்றன',
          'th_message'			=>'แพคเกจเหรียญของคุณได้รับการต่ออายุสำเร็จ {{amount}} เหรียญจะได้รับเครดิตในสมดุล',
          'kn_message'      =>'ನಿಮ್ಮ ನಾಣ್ಯ ಪ್ಯಾಕೇಜ್ ಅನ್ನು ಯಶಸ್ವಿಯಾಗಿ ನವೀಕರಿಸಲಾಗುತ್ತದೆ, {{amount}} ನಾಣ್ಯಗಳು ಸಮತೋಲನದಲ್ಲಿ ಸಲ್ಲುತ್ತದೆ',
          'tl_message'			=>'Ang iyong barya pakete ay matagumpay na na-renew, {{amount}} Ang mga barya ay kredito sa balanse',
          'ru_message'			=>'Ваша пакет монет успешно продлен, {{amount}} монеты зачисляются в балансе',
          'id_message'			=>'Paket koin Anda berhasil diperbarui, {{amount}} koin dikreditkan secara saldo',
          'zh_message'			=>'您的硬币包装已成功续订，{{amount}}硬币兑换余额',
        ),
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$value);

        $transaction_messages = array(
            array(
                'source' => 437,
                'en_message'      => 'In app coins purchase',
                'hi_message'      => 'एप्लिकेशन सिक्कों की खरीद में',
                "guj_message"     =>'એપ્લિકેશન સિક્કા ખરીદી',
                "fr_message"      =>'Achat de pièces dans l\'application',
                "ben_message"     =>'অ্যাপ্লিকেশন কয়েন ক্রয়',
                "pun_message"     =>'ਐਪ ਸਿੱਕੇ ਖਰੀਦਣ ਵਿੱਚ',
                "tam_message"     => 'பயன்பாட்டு நாணயங்கள் வாங்குவதில்',
                "th_message"      =>'ในการซื้อเหรียญแอป',
                'kn_message'      =>'ಅಪ್ಲಿಕೇಶನ್ ನಾಣ್ಯಗಳಲ್ಲಿ ಖರೀದಿ',
                'ru_message'			=>'В приложении монеты покупки',
                'tl_message'			=>'Sa pagbili ng app barya',
                'id_message'			=>'Dalam Pembelian Koin App',
                'zh_message'			=>'在App Coins购买',

            ),
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
             
    }
    
    public function down() {
        	//down script 
        $this->dbforge->drop_table(SUBSCRIPTION);
        $this->dbforge->drop_table(USER_SUBSCRIPTION);
        $this->db->where('source',437)->delete(TRANSACTION_MESSAGES);
        $this->db->where_in('notification_type',[437,438,439])->delete(NOTIFICATION_DESCRIPTION);
    }
}
