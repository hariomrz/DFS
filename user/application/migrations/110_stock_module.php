<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_module extends CI_Migration {

    public function up() {
      $data = array(
              'game_key' => 'allow_stock_fantasy',
              'en_title' => "Stock Fantasy",              
              'hi_title'=> "काल्पनिक स्टॉक",
              'guj_title' => 'સ્ટોક ફantન્ટેસી',
              'fr_title' => 'Stock Fantaisie',
              'ben_title' => 'স্টক ফ্যান্টাসি',
              'pun_title' => 'ਸਟਾਕ ਕਲਪਨਾ',
              'tam_title' => 'பங்கு பேண்டஸி',              
              'th_title' => 'หุ้นแฟนตาซี',
              'kn_title' => 'ಸ್ಟಾಕ್ ಫ್ಯಾಂಟಸಿ',
              'ru_title' => 'Склад Фантазия',
              'id_title' => 'Fantasi Saham',
              'tl_title' => 'Stock Pantasiya',
              'zh_title' => '股票幻想',

              'en_desc' => "Play fantasy game by picking stocks and win prizes",
              'hi_desc' => "स्टॉक चुनकर काल्पनिक गेम खेलें और पुरस्कार जीतें",
              'guj_desc' => "સ્ટોક્સ ચૂંટતા અને ઇનામો જીતીને કાલ્પનિક રમત રમો",
              'fr_desc' => 'Jouez à un jeu fantastique en choisissant des actions et gagnez des prix',
              'ben_desc' => 'স্টক বাছাই করে ফ্যান্টাসি গেম খেলুন এবং পুরষ্কার জিতে নিন',
              'pun_desc' => 'ਸਟਾਕਾਂ ਨੂੰ ਚੁਣ ਕੇ ਕਲਪਨਾ ਖੇਡ ਖੇਡੋ ਅਤੇ ਇਨਾਮ ਜਿੱਤੇ',
              'tam_desc' => 'பங்குகளைத் தேர்ந்தெடுத்து பரிசுகளை வெல்வதன் மூலம் கற்பனை விளையாட்டை விளையாடுங்கள்',              
              'th_desc' => 'เล่นเกมแฟนตาซีด้วยการเลือกหุ้นและลุ้นรับรางวัล',
              'kn_desc' => 'ಷೇರುಗಳನ್ನು ಆರಿಸಿ ಮತ್ತು ಬಹುಮಾನಗಳನ್ನು ಗೆಲ್ಲುವ ಮೂಲಕ ಫ್ಯಾಂಟಸಿ ಆಟವನ್ನು ಆಡಿ',
              'ru_desc' => 'Играйте в фэнтезийную игру, выбирая акции и выигрывайте призы',
              'id_desc' => 'Mainkan game fantasi dengan memilih saham dan menangkan hadiah',
              'tl_desc' => 'Maglaro ng pantasya sa pamamagitan ng pagpili ng mga stock at manalo ng mga premyo',
              'zh_desc' => '通過選股玩奇幻遊戲並贏取獎品',
              'status' => 0
      );
      $this->db->insert(SPORTS_HUB,$data);

      $data = array(
              'game_key' => 'allow_stock_predict',
              'en_title' => "Stock Predict",              
              'hi_title'=> "स्टॉक भविष्यवाणी",
              'guj_title' => 'સ્ટોક અનુમાન',
              'fr_title' => "Prévision d'actions",
              'ben_title' => 'স্টক পূর্বাভাস',
              'pun_title' => 'ਸਟਾਕ ਦੀ ਭਵਿੱਖਬਾਣੀ',
              'tam_title' => 'பங்கு கணிப்பு',              
              'th_title' => 'หุ้นแฟนตาซี',
              'kn_title' => 'ಸ್ಟಾಕ್ ಪ್ರಿಡಿಕ್ಟ್',
              'ru_title' => 'Склад Фантазия',
              'id_title' => 'Prediksi Saham',
              'tl_title' => 'Stock Pantasiya',
              'zh_title' => '股票预测',
              'en_desc' => "Play fantasy game by picking stocks and win prizes",
              'hi_desc' => "स्टॉक चुनकर काल्पनिक गेम खेलें और पुरस्कार जीतें",
              'guj_desc' => "સ્ટોક્સ ચૂંટતા અને ઇનામો જીતીને કાલ્પનિક રમત રમો",
              'fr_desc' => 'Jouez à un jeu fantastique en choisissant des actions et gagnez des prix',
              'ben_desc' => 'স্টক বাছাই করে ফ্যান্টাসি গেম খেলুন এবং পুরষ্কার জিতে নিন',
              'pun_desc' => 'ਸਟਾਕਾਂ ਨੂੰ ਚੁਣ ਕੇ ਕਲਪਨਾ ਖੇਡ ਖੇਡੋ ਅਤੇ ਇਨਾਮ ਜਿੱਤੇ',
              'tam_desc' => 'பங்குகளைத் தேர்ந்தெடுத்து பரிசுகளை வெல்வதன் மூலம் கற்பனை விளையாட்டை விளையாடுங்கள்',              
              'th_desc' => 'เล่นเกมแฟนตาซีด้วยการเลือกหุ้นและลุ้นรับรางวัล',
              'kn_desc' => 'ಷೇರುಗಳನ್ನು ಆರಿಸಿ ಮತ್ತು ಬಹುಮಾನಗಳನ್ನು ಗೆಲ್ಲುವ ಮೂಲಕ ಫ್ಯಾಂಟಸಿ ಆಟವನ್ನು ಆಡಿ',
              'ru_desc' => 'Играйте в фэнтезийную игру, выбирая акции и выигрывайте призы',
              'id_desc' => 'Mainkan game fantasi dengan memilih saham dan menangkan hadiah',
              'tl_desc' => 'Maglaro ng pantasya sa pamamagitan ng pagpili ng mga stock at manalo ng mga premyo',
              'zh_desc' => '通過選股玩奇幻遊戲並贏取獎品',
              'status' => 0
      );
      $this->db->insert(SPORTS_HUB,$data); 

      $data = array(
                array(
                  'source' => 460,
                  'name'=> 'Join Stock Contest'
                ),
                array(
                  'source' => 461,
                  'name'=> 'Cancel Stock Contest'
                ),
                array(
                  'source' => 462,
                  'name'=> 'Won Stock Contest'
                ),
                array(
                  'source' => 463,
                  'name'=> 'amount deducted as TDS'
                )
              );
      $this->db->insert_batch(MASTER_SOURCE,$data);

      $sql = "ALTER TABLE ".$this->db->dbprefix(PROMO_CODE)." CHANGE `type` `type` TINYINT(2) NULL DEFAULT NULL COMMENT '0-First Deposit, 1- Deposit Range, 2-PromoCode, 3-Contest Code,4- Tournament Mode, 5 stock contest code';";
	  	$this->db->query($sql);

      $notification_description = 
      array(
          array(
              'notification_type' => 551,
              'message'=> 'Contest {{contest_name}} has been canceled due to insufficient participation',
              'en_message' => 'Contest {{contest_name}} has been canceled due to insufficient participation',
              'hi_message' => 'खेल {{contest_name}} कम लोग की भागीदारी के कारण रद्द कर दिया गया है।',
              'guj_message' => 'અપર્યાપ્ત ભાગીદારીને કારણે {{contest_name}} સ્પર્ધા રદ કરવામાં આવી છે.',
              'fr_message' => "Nom du concours {{contest_name}} ர annulé en reason d'une participation insuffisante",
              'ben_message' => 'প্রতিযোগিতা {{contest_name}} অপর্যাপ্ত অংশগ্রহণের কারণে বাতিল করা হয়েছে',
              'pun_message' => 'ਮੁਕਾਬਲਾ {{contest_name}} ਨਾਕਾਫੀ ਭਾਗੀਦਾਰੀ ਕਰਕੇ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ',
              'tam_message' => 'போட்டி {{contest_name}} போதிய பங்கேற்பு காரணமாக ரத்து செய்யப்பட்டது',
              'th_message' => 'การประกวด {{contest_name}} ได้ถูกยกเลิกเนื่องจากการมีส่วนร่วมไม่เพียงพอ',
              'kn_message' => 'ಸ್ಪರ್ಧೆ {{contest_name}} ಸಾಕಷ್ಟು ಭಾಗವಹಿಸುವಿಕೆ ಕಾರಣ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ',
              'ru_message' => 'Конкурс {{contest_name}} был отменен из-за недостаточное участие',
              'id_message' => 'Kontes {{contest_name}} telah dibatalkan karena Partisipasi tidak cukup',
              'tl_message' => 'Contest {{contest_name}} ay nakansela dahil sa hindi sapat Paglahok',
              'zh_message' => '大赛{{contest_name}}已因参与不足取消',
              'en_subject' => '',
              'hi_subject' => '',
              'guj_subject' => '', 
              'fr_subject' => '',
              'ben_subject' => '',
              'pun_subject' => '',
              'th_subject' => '', 
              'kn_subject' => '',
              'ru_subject' => '',
              'id_subject' => '',
              'tl_subject' => '',
              'zh_subject' => ''           
          ),
          array(
              'notification_type' => 552,
              'message'=> 'Contest {{contest_name}} has been cancelled by the admin. Reason has been sent to your email.',
              'en_message' => 'Contest {{contest_name}} has been cancelled by the admin. Reason has been sent to your email.',
              'hi_message' => 'खेल {{contest_name}} ऐदडमिन द्वारा बन्द कर दिया गया है। कारण आपको ईमेल पर भेज दिया गया है',
              'guj_message' => 'રમત {{contest_name}} અદ્મિન્ દ્વારા બંધ કરવામાં આવ્યું. કારણ કે તમે ઇમેઇલ પર મોકલવામાં આવ્યા હતા',
              'fr_message' => "Concours annulé pour l'administrateur {{contest_name}}. Votre e mail a été envoyé.",
              'ben_message' => 'প্রতিযোগিতা {{contest_name}} প্রশাসক দ্বারা বাতিল করা হয়েছে। কারণ আপনার ইমেল প্রেরণ করা হয়েছে।',
              'pun_message' => 'ਮੁਕਾਬਲਾ {{contest_name}} ਪ੍ਰਬੰਧਕ ਦੁਆਰਾ ਰੱਦ ਕਰ ਦਿੱਤਾ ਗਿਆ ਹੈ. ਕਾਰਨ ਤੁਹਾਡੀ ਈਮੇਲ ਤੇ ਭੇਜਿਆ ਗਿਆ ਹੈ.',
              'tam_message' => 'போட்டி {{contest_name}} நிர்வாகிக்கு ரத்துசெய்யப்பட்டது. காரணம் உங்கள் மின்னஞ்சல் அனுப்பப்பட்டுள்ளது.',
              'th_message' => 'การประกวด {{contest_name}} ถูกยกเลิกโดยผู้ดูแลระบบ เหตุผลที่ถูกส่งไปยังอีเมลของคุณ',
              'kn_message' => 'ಸ್ಪರ್ಧೆ {{contest_name}} ನಿರ್ವಾಹಕರಿಂದ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ. ಕಾರಣ ನಿಮ್ಮ ಇಮೇಲ್ ಕಳುಹಿಸಲಾಗಿದೆ.',
              'ru_message' => 'Конкурс {{contest_name}} был отменен администратором. Причина была отправлена ​​на ваш электронный адрес.',
              'id_message' => 'Kontes {{contest_name}} telah dibatalkan oleh admin. Alasan telah dikirim ke email Anda.',
              'tl_message' => 'Contest {{contest_name}} ay nakansela sa pamamagitan ng admin. Dahilan ay naipadala na sa iyong email.',
              'zh_message' => '大赛{{contest_name}}已被管理员取消。原因已发送到您的邮箱。',
              'en_subject' => '',
              'hi_subject' => '',
              'guj_subject' => '', 
              'fr_subject' => '',
              'ben_subject' => '',
              'pun_subject' => '',
              'th_subject' => '', 
              'kn_subject' => '',
              'ru_subject' => '',
              'id_subject' => '',
              'tl_subject' => '',
              'zh_subject' => ''
          ),
          array(
            'notification_type' => 553,
            'message'=> 'Game {{contest_name}} joined successfully.',
            'en_message' => 'Game {{contest_name}} joined successfully.',
            'hi_message' => 'खेल {{contest_name}} सफलतापूर्वक शामिल हुई',
            'guj_message' => 'રમત {{{custent_name}} સફળતાપૂર્વક જોડાયા',
            'fr_message' => "Jeu {{conteste_name}} a rejoint avec succès",
            'ben_message' => 'খেলা {{contest_name}} সফলভাবে যোগদান',
            'pun_message' => 'ਗੇਮ {{{contest_name} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਏ',
            'tam_message' => 'விளையாட்டு {{contest_name}} வெற்றிகரமாக இணைந்தது',
            'th_message' => 'เกม {{contest_name}} เข้าร่วมได้สำเร็จ',
            'kn_message' => 'ಗೇಮ್ {{contest_name}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಿಕೊಂಡರು',
            'ru_message' => 'Игра {{contest_name}} успешно соединена',
            'id_message' => 'Game {{contest_name}} Bergabung dengan Sukses',
            'tl_message' => 'Laro {{contest_name}} ay matagumpay na sumali',
            'zh_message' => '游戏{{contest_name}}成功加入',
            'en_subject' => '',
            'hi_subject' => '',
            'guj_subject' => '', 
            'fr_subject' => '',
            'ben_subject' => '',
            'pun_subject' => '',
            'th_subject' => '', 
            'kn_subject' => '',
            'ru_subject' => '',
            'id_subject' => '',
            'tl_subject' => '',
            'zh_subject' => ''
          ),
          array(
            'notification_type' => 554,
            'message'=> 'You\'re a winner in the {{collection_name}} match.',
            'en_message' => 'You\'re a winner in the {{collection_name}} match.',
            'hi_message' => 'आप {{collection_name}} मैच में एक विजेता हैं।',
            'guj_message' =>'તમે {{{collection_name}} માં વિજેતા છો.',
            'fr_message' => "Vous êtes un gagnant dans le match {{collection_name}}.",
            'ben_message' =>'আপনি {{collection_name}} ম্যাচে একটি বিজয়ী হন।',
            'pun_message' =>'ਤੁਸੀਂ {{ਭੰਡਾਰਾਂ \'ਤੇ ਮੇਲ ਕਰੋ.',
            'tam_message' =>'நீங்கள் {{collection_name} போட்டியில் ஒரு வெற்றியாளர்.',
            'th_message' => 'คุณเป็นผู้ชนะในการจับคู่ {{collection_name}}',
            'kn_message' => 'ನೀವು {{collection_name}} ಪಂದ್ಯಗಳಲ್ಲಿ ವಿಜೇತರಾಗಿದ್ದೀರಿ.',
            'ru_message' => 'Вы победитель в матче {{collection_name}}.',
            'id_message' => 'Anda seorang pemenang dalam pertandingan {{collection_name}}.',
            'tl_message' => 'Ikaw ay isang nagwagi sa {{collection_name}} tugma.',
            'zh_message' => '你是{{collection_name}}中的胜利者。',
            'en_subject' => 'Congratulations!',
            'hi_subject' => 'बधाई हो!',
            'guj_subject' => 'અભિનંદન!', 
            'fr_subject' => 'Toutes nos félicitations!',
            'ben_subject' => 'অভিনন্দন!',
            'pun_subject' => 'ਵਧਾਈਆਂ!',
            'tam_subject' => 'வாழ்த்துக்கள்!',
            'th_subject' => 'ยินดีด้วย!', 
            'kn_subject' => 'ಅಭಿನಂದನೆಗಳು!',
            'ru_subject' => 'Поздравляю!',
            'id_subject' => 'Selamat!',
            'tl_subject' => 'Binabati kita!',
            'zh_subject' => '恭喜！',
            
            ),
            array(
              'notification_type' => 555,
              'message'=> '₹{{amount}} deducted as TDS',
              'en_message' => '₹{{amount}} deducted as TDS',
              'hi_message' => '₹ {{amount}} टीडीएस के रूप में कटौती की गई',
              'guj_message' =>'₹ {{amount}} ટીડીએસ તરીકે કપાત',
              'fr_message' => "{{amount}} et déduire",
              'ben_message' =>'₹{{amount}} টিডিএস হিসাবে কেটে নেওয়া',
              'pun_message' =>'ਤੁਸੀਂ {{amount}} \'ਤੇ ਮੇਲ ਕਰੋ.',
              'tam_message' =>'₹ {{amount}} அதுமட்டுமல்ல கழிப்பதற்கு',
              'th_message' => '₹ {{amount}} หักเป็นค่า TDS',
              'kn_message' => '₹ {{amount}} ಟಿಡಿಎಸ್ ಎಂದು ಕಳೆಯಲಾಗುತ್ತದೆ',
              'ru_message' => '₹ {{amount}} вычитается, как TDS',
              'id_message' => '₹ {{amount}} dikurangkan sebagai TDS',
              'tl_message' => '₹ {{amount}} ibabawas bilang TDS',
              'zh_message' => '₹{{amount}}扣除TDS',
              'en_subject' =>  '',
              'hi_subject' =>  '',
              'guj_subject' => '', 
              'fr_subject' =>  '',
              'ben_subject' => '',
              'pun_subject' => '',
              'tam_subject' => '',
              'th_subject' =>  '', 
              'kn_subject' =>  '',
              'ru_subject' =>  '',
              'id_subject' =>  '',
              'tl_subject' =>  '',
              'zh_subject' =>  '',
              
          )
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_description); 
        
        $email_temp = array(
                          array(
                            'template_name' => 'stock-contest-cancel',
                            'subject' => '',
                            'template_path' => 'stock-contest-cancel',
                            'notification_type' => 551,
                            'status' => 1,
                            'display_label' => '',
                            'date_added'      => format_date('today')
                          ),
                          array(
                            'template_name' => 'stock-contest-cancel-by-admin',
                            'subject' => '',
                            'template_path' => 'stock-contest-cancel-by-admin',
                            'notification_type' => 552,
                            'status' => 1,
                            'display_label' => 'Contest cancel from admin',
                            'date_added'      => format_date('today')
                          ),
                          array(
                            'template_name' => 'join-contest',
                            'subject' => '',
                            'template_path' => 'join-league',
                            'notification_type' => 553,
                            'status' => 1,
                            'display_label' => 'Join Contest',
                            'date_added'      => format_date('today')
                          ),
                          array(
                            'template_name' => 'contest-won
                            ',
                            'subject' => '',
                            'template_path' => 'contest-won
                            ',
                            'notification_type' => 554,
                            'status' => 1,
                            'display_label' => 'contest-won
                            ',
                            'date_added'      => format_date('today')
                          )
                        );
        $this->db->insert_batch(EMAIL_TEMPLATE,$email_temp);     
        
        
        $transaction_message = 
        array(
          array(
              'source' => 460,
              'en_message' => 'Entry fee for %s',
              'hi_message' => '%s के लिए प्रवेश शुल्क',
              'guj_message' => '%s માટે પ્રવેશ ફી',
              'fr_message' => "%s frais d'entrée",
              'ben_message' => '%s এর জন্য প্রবেশ ফি',
              'pun_message' => '%s ਲਈ ਐਂਟਰੀ ਫੀਸ',
              'tam_message' => '%s நுழைவு கட்டணம்',
              'th_message' => 'ค่าธรรมเนียมแรกเข้าสำหรับ %s',
              'kn_message' => '%s ಪ್ರವೇಶ ಶುಲ್ಕ',
              'ru_message' => 'Стартовый взнос для %s',
              'id_message' => 'biaya masuk untuk %s',
              'tl_message' => 'Entry fee para sa %s',
              'zh_message' => '%s 的报名费'           
          ),
          array(
            'source' => 461,
            'en_message' => 'Fee Refund For Contest',
            'hi_message' => 'प्रतियोगिता के लिए शुल्क वापसी',
            'guj_message' => 'હરીફાઈ માટે ફી પરત',
            'fr_message' => "Remboursement des frais compétitifs",
            'ben_message' => 'প্রতিযোগিতার জন্য ফি ফেরত',
            'pun_message' => 'ਮੁਕਾਬਲੇ ਲਈ ਫੀਸ ਦੀ ਰਿਫੰਡ',
            'tam_message' => 'போட்டி கட்டணம் திரும்பப்பெறும்',
            'th_message' => 'ค่าธรรมเนียมการคืนเงินสำหรับการประกวด',
            'kn_message' => 'ಸ್ಪರ್ಧೆ ಫಾರ್ ಶುಲ್ಕ ಮರುಪಾವತಿ',
            'ru_message' => 'Плата за возврат Для конкурса',
            'id_message' => 'Biaya Pengembalian Untuk Kontes',
            'tl_message' => 'Fee Refund Para Contest',
            'zh_message' => '费退款大赛'
          ),
          array(
            'source' => 462,
            'en_message' => 'Won Contest Prize',
            'hi_message' => 'प्रतियोगिता का पुरस्कार जीता',
            'guj_message' => 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
            'fr_message' => "Prix du concours gagné",
            'ben_message' => 'প্রতিযোগিতা পুরস্কার জিতেছে',
            'pun_message' => 'ਮੁਕਾਬਲਾ ਇਨਾਮ ਜਿੱਤਿਆ',
            'tam_message' => 'வென்றது போட்டி பரிசு',
            'th_message' => 'ได้รับรางวัลการประกวด',
            'kn_message' => 'ಸಗೆದ್ದಿದ್ದು ಸ್ಪರ್ಧೆ ಪ್ರಶಸ್ತಿ',
            'ru_message' => 'Выигранный приз конкурса',
            'id_message' => 'Memenangkan hadiah kontes.',
            'tl_message' => 'Won Paligsahan Prize',
            'zh_message' => '赢得竞赛奖'
          ),
          array(
            'source' => 463,
            'en_message' => 'Total TDS Deducted',
            'hi_message' => 'कुल टीडीएस घटाया गया ',
            'guj_message' => 'કુલ ટીડીએસ કપાત',
            'fr_message' => "Total et soustrait",
            'ben_message' => 'মোট টিডিএস হ্রাস',
            'pun_message' => 'ਕੁੱਲ ਟੀ.ਡੀ.ਐੱਸ',
            'tam_message' => 'மொத்த அதுமட்டுமல்ல கழிக்கப்படும்',
            'th_message' => 'รวม TDS หัก',
            'kn_message' => 'ಒಟ್ಟು ಟಿಡಿಎಸ್ ಕಡಿತಗೊಳಿಸಲಾಗುತ್ತದೆ ',
            'ru_message' => 'Всего TDS вычитаются',
            'id_message' => 'Kabuuang TDS ibabawas',
            'tl_message' => 'Kabuuang TDS ibabawas',
            'zh_message' => '总扣除的TDS'
          )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES,$transaction_message); 


        $this->db->where('key_name', 'allow_stock_fantasy');
        $this->db->update(APP_CONFIG, json_encode(array(
          'contest_publish_time' => '10:45:00',
          'contest_start_time' => '03:45:00',
          'contest_end_time' => '09:45:00',
        ))); 


        $sql = "ALTER TABLE ".$this->db->dbprefix(SHORT_URLS)." CHANGE `url_type` `url_type` INT(11) NULL DEFAULT NULL COMMENT '1=invite,2=contest,3=collection,4 => DFS tournament, 5=> stock contest';";
	  	  $this->db->query($sql);
    }

    public function down() {
      //down script 
     /* 
      $this->db->where_in('notification_type', array(551,552));
      $this->db->delete(EMAIL_TEMPLATE);

      $this->db->where_in('notification_type', array(551,552));
      $this->db->delete(NOTIFICATION_DESCRIPTION);

      $this->db->where_in('source', array(460, 461, 462));
      $this->db->delete(MASTER_SOURCE);

      $this->db->where('game_key', 'allow_stock_fantasy');
      $this->db->delete(SPORTS_HUB);
      */
    }
}