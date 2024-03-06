<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Props_fantasy extends CI_Migration {

    public function up() {

      $hub_setting = array(
            'game_key' => 'props_fantasy',
            'en_title' => "Props Fantasy",              
            'hi_title'=> "प्रॉप्स फंतासी",
            'guj_title' => 'પ્રૂબ કાલ્પનિક',
            'fr_title' => 'Accessoires fantaisie',
            'ben_title' => 'প্রপস ফ্যান্টাসি',
            'pun_title' => 'ਕਲਪਨਾ ਕਰੋ ਕਲਪਨਾ ਕਰੋ',
            'tam_title' => 'முட்டுகள் கற்பனை',              
            'th_title' => 'อุปกรณ์ประกอบฉากแฟนตาซี',
            'kn_title' => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ',
            'ru_title' => 'Реквизит Фэнтези',
            'id_title' => 'Props Fantasy',
            'tl_title' => 'Props fantasy',
            'zh_title' => '道具幻想',
            'en_desc' => "Score more or less project to win.",
            'hi_desc' => "जीतने के लिए कम या ज्यादा प्रोजेक्ट स्कोर करें।",
            'guj_desc' => "જીતવા માટે વધુ કે ઓછા પ્રોજેક્ટનો સ્કોર કરો.",
            'fr_desc' => 'Marquez un projet plus ou moins pour gagner.',
            'ben_desc' => 'জয়ের জন্য কম -বেশি প্রকল্প স্কোর করুন।',
            'pun_desc' => 'ਜਿੱਤ ਲਈ ਵਧੇਰੇ ਜਾਂ ਘੱਟ ਪ੍ਰੋਜੈਕਟ ਸਕੋਰ ਕਰੋ.',
            'tam_desc' => 'வெற்றி பெற அதிகமாகவோ அல்லது குறைவாகவோ திட்டத்தை மதிப்பெண் செய்யுங்கள்.',              
            'th_desc' => 'Skor lebih atau lebih sedikit proyek untuk menang.',
            'kn_desc' => 'ಗೆಲ್ಲಲು ಹೆಚ್ಚು ಅಥವಾ ಕಡಿಮೆ ಯೋಜನೆಯನ್ನು ಸ್ಕೋರ್ ಮಾಡಿ.',
            'ru_desc' => 'Оцените более или менее проект, чтобы выиграть.',
            'id_desc' => 'Skor lebih atau lebih sedikit proyek untuk menang..',
            'tl_desc' => 'Puntos higit pa o mas kaunting proyekto upang manalo.',
            'zh_desc' => '得分或多或少地获胜。',
            'display_order' => 11,
            'allowed_sports' => json_encode([5,7]),
            'status' => 0
      );

      $result = $this->db->select('*')->from(SPORTS_HUB)->where('game_key',"props_fantasy")->get()->row_array();
      if(!$result){
        $this->db->insert(SPORTS_HUB,$hub_setting);
      }else{
        $this->db->update(SPORTS_HUB,$hub_setting,array('sports_hub_id'=>$result['sports_hub_id']));
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',537)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '537',
                'name' => 'Props fantasy Join Entry'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',538)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '538',
                'name' => 'Props Fantasy Additional Join Entry'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

       $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',539)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '539',
                'name' => 'Props Fantasy Refund Entry'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',540)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '540',
                'name' => 'Props Fantasy Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',541)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '541',
                'name' => 'Props Fantasy TDS Deduction'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }


    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"537")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
            array(
                'source' => 537,
                'en_message'      => '{{team_name}} for props',
                'hi_message'      => '{{team_name}} प्रॉप्स के लिए',
                'guj_message'     => '{{team_name}} પ્રોપ્સ માટે',
                'fr_message'      => '{{team_name}} pour les accessoires',
                'ben_message'     => '{{team_name}} প্রপসগুলির জন্য',
                'pun_message'     => '{{team_name}} ਪ੍ਰੋਪਸ ਲਈ',
                'tam_message'     => 'Prot {{team_name}} props props',
                'th_message'      => '{{team_name}} สำหรับอุปกรณ์ประกอบฉาก',
                'kn_message'      => '{{team_name} the props ಗಾಗಿ',
                'ru_message'      => '{{team_name}} для реквизита',
                'id_message'      => '{{team_name}} untuk alat peraga',
                'tl_message'      => '{{team_name}} para sa props',
                'zh_message'      => '{{team_name}} 取消费用退款',
                'es_message'      => '{{team_name}} para sa props',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "538")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
            array(
                'source' => 538,
                'en_message'      => '{{team_name}} for Props [Stake Increased]',
                'hi_message'      => '{{team_name}} प्रॉप्स के लिए [हिस्सेदारी बढ़ी]',
                'guj_message'     => '{{team_name}} પ્રોપ્સ માટે [હિસ્સો વધ્યો]',
                'fr_message'      => "{{team_name}} pour les accessoires [pieu augmenté]",
                'ben_message'     => '{{team_name}} প্রপসগুলির জন্য  [স্টেক বাড়ানো]',
                'pun_message'     => 'Props props ਲਈ {{team_name}} [ਹਿੱਸੇ ਵਿੱਚ ਵਾਧਾ]',
                'tam_message'     => '{{team_name}} [பங்கு அதிகரித்தது]',
                'th_message'      => '{{team_name}} สำหรับอุปกรณ์ประกอบฉาก [สเตคเพิ่มขึ้น]',
                'kn_message'      => '{{team_name}} props [ಪಾಲು ಹೆಚ್ಚಾಗಿದೆ]',
                'ru_message'      => '{{team_name}} для реквизита [коллега увеличилась]',
                'id_message'      => '{{team_name}} untuk alat peraga [saham meningkat]',
                'tl_message'      => '{{team_name}} para sa props [nadagdagan ang stake]',
                'zh_message'      => '{{team_name}} props [赌注增加]',
                'es_message'      => '{{team_name}} para sa props [nadagdagan ang stake]',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

      $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "539")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
            array(
                'source' => 539,
                'en_message'      => 'Fee Refund [{{team_name}} for Props]',
                'hi_message'      => 'फीस रिफंड [{{team_name}} प्रॉप्स के लिए]',
                'guj_message'     => 'ફી રિફંડ [{{team_name}} પ્રોપ્સ માટે]',
                'fr_message'      => "Remboursement des frais [{{team_name}} pour les accessoires]",
                'ben_message'     => 'ফি ফেরত [{{team_name}} প্রপসের জন্য]',
                'pun_message'     => 'ਪ੍ਰੋਫ਼ਸ ਲਈ ਫੀਸ ਰਿਫੰਡ [{{team_name}} ਪ੍ਰੋਪਸ ਲਈ] ',
                'tam_message'     => 'கட்டணம் திருப்பிச் செலுத்துதல் [{{team_name}} props props]',
                'th_message'      => 'การคืนเงินค่าธรรมเนียม [{{team_name}} สำหรับอุปกรณ์ประกอบฉาก]',
                'kn_message'      => 'ಶುಲ್ಕ ಮರುಪಾವತಿ [{{team_name}} ರಂಗಪರಿಕರಗಳಿಗಾಗಿ]',
                'ru_message'      => 'Возврат платы [{{team_name}} за реквизит]',
                'id_message'      => 'Pengembalian Biaya [{{team_name}} untuk props]',
                'tl_message'      => 'Bayad sa bayad [{{team_name}} para sa props]',
                'zh_message'      => '费用退款[{{team_name}}用于道具]',
                'es_message'      => 'Bayad sa bayad [{{team_name}} para sa props]',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "540")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
            array(
               'source' => 540,
                'en_message'      => 'Won {{team_name}} in Props',
                'hi_message'      => 'जीता {{team_name}} प्रॉप्स में',
                'guj_message'     => 'પ્રોપ્સમાં {{team_name}} જીત્યો',
                'fr_message'      => 'A gagné {{team_name}} dans les accessoires',
                'ben_message'     => 'প্রপসগুলিতে {{team_name}} জিতেছে',
                'pun_message'     => 'ਪ੍ਰੋਪਸ ਵਿੱਚ {{team_name}} ਪ੍ਰੋਪਸ ਲਈ',
                'tam_message'     => 'முட்டுக்கட்டைகளில் {{team_name}} வென்றது',
                'th_message'      => 'ชนะ {{team_name}} ในอุปกรณ์ประกอบฉาก',
                'kn_message'      => 'ಪ್ರಾಪ್ಸ್ನಲ್ಲಿ {{team_name}} ಗೆದ್ದರು',
                'ru_message'      => 'Win {{team_name}} в репутациях',
                'id_message'      => 'Won {{team_name}} di Props',
                'tl_message'      => 'Nanalo {{team_name}} sa props',
                'zh_message'      => '在道具中赢得 {{team_name}}',
                'es_message'      => 'Nanalo {{team_name}} sa props',

            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }



    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "541")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
            array(
                 'source' => 541,
                'en_message'      => 'Props fantasy TDS Deduction',
                'hi_message'      => 'पिकम टूर्नामेंट टीडीएस कटौती',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ ટી.ડી.એસ.',
                'fr_message'      => 'Tournoi Pickem Déduction TDS',
                'ben_message'     => 'পিকেম টুর্নামেন্ট টিডিএস ছাড়',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ ਟੀਡੀਜ਼ ਕਟੌਤੀ',
                'tam_message'     => 'பிக்கம் போட்டி டி.டி.எஸ் விலக்கு',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem การหัก TDS',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ ಟಿಡಿಎಸ್ ಕಡಿತ',
                'ru_message'      => 'Props fantasy Вычет TDS',
                'id_message'      => 'Turnamen Pickem Pengurangan TDS',
                'tl_message'      => 'Props fantasy Pagbabawas ng TDS',
                'zh_message'      => 'Pickem锦标赛 TDS扣除',
                'es_message'      => 'Deducción de tournamentos de pickem TDS'
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }


     $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "654")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description =array(
                    "notification_type" =>654,
                    "en_subject"    =>"Entry Join",
                    "hi_subject"    =>"प्रविष्टि में शामिल होना",
                    "guj_subject"   =>"પ્રવેશ -જોડાણ",
                    "fr_subject"    =>"Entrée",
                    "ben_subject"   =>"প্রবেশ যোগ",
                    "pun_subject"   =>"ਐਂਟਰੀ ਸ਼ਾਮਲ ਹੋਵੋ",
                    "tam_subject"  => "நுழைவு சேர",
                    "th_subject"    =>"เข้าร่วม",
                    "kn_subject"    =>"ಪ್ರವೇಶ ಸೇರ್ಪಡೆ",
                    "ru_subject"    =>"Вход соединение",
                    "id_subject"    =>"Entri bergabung",
                    "tl_subject"    =>"Sumali sa entry",
                    "zh_subject"    =>"进入加入",
                    "es_subject"    =>"Sumali sa entry",
                    "message"       => "{{team_name}} joined successfully!",
                    'en_message'    => '{{team_name}} joined successfully!',
                    'hi_message'    => '{{team_name}} शामिल हुए सफलतापूर्वक!',
                    'guj_message'   => '{{team_name}} જોડાયો સફળતાપૂર્વક!',
                    'fr_message'    => '{{team_name}} avec succès!',
                    'ben_message'   => '{{team_name}} যুক্ত সফলভাবে!',
                    'pun_message'   => '{{team_name}} ਸ਼ਾਮਲ ਹੋਏ!',
                    'tam_message'   => '{{team_name}} இணைந்தது ਸਫਲਤਾਪੂਰਵਕ!',
                    'th_message'    => '{{team_name}} เข้าร่วม อย่างประสบความสำเร็จ!',
                    'kn_message'    => '{{team_name}} ಸೇರಿಕೊಂಡರು ಯಶಸ್ವಿಯಾಗಿ!',
                    'ru_message'    => '{{team_name}} присоединился успешно!',
                    'id_message'    => '{{team_name}} berhasil!',
                    'tl_message'    => '{{team_name}} sumali matagumpay!',
                    'zh_message'    => '{{team_name}} 加入 成功地!',
                    'es_message'    => '{{team_name}} unido con éxito!'


                );
                $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        

         $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "655")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description =array(
                "notification_type" =>655,
                "en_subject"    =>"Game Won",
                "hi_subject"    =>"खेल जीता",
                "guj_subject"   =>"રમત જીત્યો",
                "fr_subject"    =>"Partie gagnée",
                "ben_subject"   =>"খেলা জিতেছে",
                "pun_subject"   =>"ਖੇਡ ਜਿੱਤੀ",
                "th_subject"    =>"ชนะเกม",
                "kn_subject"    =>"ಗೇಮ್",
                "ru_subject"    =>"Игра победила",
                "id_subject"    =>"Game Won.",
                "tl_subject"    =>"Game Won.",
                "zh_subject"    =>"比赛赢了",
                "message"       =>"Woohoo!!! You won {{winning}}{{currency_type}}  in a {{team_name}}.",
                "en_message"     =>"Woohoo!!! You won {{winning}}{{currency_type}}  in a {{team_name}}.",
                "hi_message"    =>"वू हू!!! आपने {{team_name}} में {{winning}} {{currency_type}} जीता।",
                "guj_message"   =>"વાહ!!! તમે {{team_name}}માં {{winning}}{{currency_type}} જીત્યા.",
                "fr_message"    =>"Waouh !!! Vous avez gagné {{winning}}{{currency_type}} dans une {{team_name}}.",
                "ben_message"   =>"উহু!!! আপনি একটি {{team_name}}-এ {{winning}}{{currency_type}} জিতেছেন।",
                "pun_message"   =>"ਵਾਹ !!! ਤੁਸੀਂ ਇੱਕ {{team_name}} ਵਿੱਚ {{winning}}{{currency_type}} ਜਿੱਤੇ।",
                "tam_message"   =>"ஊஹூ!!! நீங்கள் {{winning}}{{currency_type}} ஒரு {{team_name}} இல் வென்றுள்ளீர்கள்.",
                "th_message"    =>"วู้ฮู้!!! คุณชนะ {{winning}}{{currency_type}} ใน {{team_name}}",
                "kn_message"    =>"ವೂಹೂ!!! ನೀವು {{winning}}{{currency_type}} ಅನ್ನು {{team_name}} ನಲ್ಲಿ ಗೆದ್ದಿದ್ದೀರಿ.",
                "ru_message"    =>"Ууууу!!! Вы выиграли {{winning}}{{currency_type}} в {{team_name}}.",
                "id_message"    =>"Woo hoo!!! Anda memenangkan {{winning}}{{currency_type}} dalam {{team_name}}.",
                "tl_message"    =>"Woohoo !!! Nanalo ka {{winning}}{{currency_type}} sa isang {{team_name}}.",
                "zh_message"    =>"哇！！！您在{{team_name}}中赢得了 {{winning}}{{currency_type}}。",
                'es_message'   => 'Woohoo !!! Nanalo ka {{winning}}{{currency_type}} sa isang {{team_name}}.'
            );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        
        $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "656")
            ->get()
            ->row_array();
        if(empty($row)) {
                $notification_description =array(
                    "notification_type" =>656,
                    "en_subject"    =>"TDS Deducted",
                    "hi_subject"    =>"टीडीएस कटौती की गई",
                    "guj_subject"   =>"કપાત",
                    "fr_subject"    =>"TDS déduit",
                    "ben_subject"   =>"টিডিএস কেটে নেওয়া",
                    "pun_subject"   =>"ਟੀਡੀਜ਼ ਕਟੌਤੀ",
                    "th_subject"    =>"TDS หัก",
                    "kn_subject"    =>"ಟಿಡಿಗಳನ್ನು ಕಡಿತಗೊಳಿಸಲಾಗಿದೆ",
                    "ru_subject"    =>"TDS вычитается",
                    "id_subject"    =>"TDS dikurangi",
                    "tl_subject"    =>"Ibawas ang TDS",
                    "zh_subject"    =>"TDS扣除",
                    "message"       =>"Props fantasy {{currency}}{{amount}} deducted as TDS",
                    "en_message"    =>"Props fantasy {{currency}}{{amount}} deducted as TDS",
                    "hi_message"    =>"पिकम टूर्नामेंट {{currency}}{{amount}} टीडीएस के रूप में कटौती की गई",
                    "guj_message"   =>"પિકમ ટૂર્નામેન્ટ {{currency}}{{amount}} ટીડીએસ તરીકે કપાત",
                    "fr_message"    =>"Tournoi Pickem {{currency}}{{amount}} déduit comme TDS",
                    "ben_message"   =>"পিকেম টুর্নামেন্ট {{currency}}{{amount}} টিডিএস হিসাবে কেটে নেওয়া",
                    "pun_message"   =>"ਪਿਕੈਮ ਟੂਰਨਾਮੈਂਟ {{currency}}{{amount}} ਜਿਵੇਂ ਟੀਡੀਐਸ ਦੇ ਤੌਰ ਤੇ ਕਟੌਤੀ ਕਰਦਾ ਹੈ",
                    "tam_message"   =>"பிக்கம் போட்டி {{currency}}{{amount}} tds எனக் கழிக்கப்படுகிறது",
                    "th_message"    =>"ทัวร์นาเมนต์ Pickem{{currency}}{{amount}} หักออกเป็น tds",
                    "kn_message"    =>"ಪಿಕ್ಕೆಮ್ ಪಂದ್ಯಾವಳಿ {{currency}}{{amount}} ಟಿಡಿಎಸ್ ಎಂದು ಕಡಿತಗೊಳಿಸಲಾಗಿದೆ",
                    "ru_message"    =>"Турнир пика {{currency}}{{amount}} вычитается как tds",
                    "id_message"    =>"Turnamen Pickem {{currency}}{{amount}} dikurangkan sebagai tds",
                    "tl_message"    =>"Props fantasy {{currency}}{{amount}} ibabawas bilang tds",
                    "zh_message"    =>"Pickem锦标赛 {{currency}}{{amount}}被扣除为TDS",
                    "es_message"    => "Torneo Pickem {{moneda}} {{cantidad}} deducido como TDS"
              );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 654)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 654,
                            'template_name'=> 'entry_join',
                            'template_path'=> 'entry_join',
                            'status' => '1',
                            'display_label' => 'Props fantasy entry Join',
                            'subject' => 'Your Entry Joining is Confirmed!'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }


        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 655)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 655,
                            'template_name'=> 'entry_won',
                            'template_path'=> 'entry_won',
                            'status' => '1',
                            'display_label' => 'Props fantasy Entry Won',
                            'subject' => 'Congratulations - Entry Won'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

         $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 656)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 656,
                            'template_name'=> 'props_tds',
                            'template_path'=> 'props_tds',
                            'status' => '1',
                            'display_label' => 'Props fantasy Winning Tds',
                            'subject' => 'Props Fantasy TDS Deduction'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }


    }
    public function down()
    {
        //down script
    }
}
