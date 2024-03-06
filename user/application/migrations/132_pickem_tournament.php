<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pickem_tournament extends CI_Migration {

    public function up() {

      $hub_setting = array(
            'game_key' => 'pickem_tournament',
            'en_title' => "Pickem Tournament",              
            'hi_title'=> "पिकम टूर्नामेंट",
            'guj_title' => 'પિકમ ટૂર્નામેન્ટ',
            'fr_title' => 'Tournoi Pickem',
            'ben_title' => 'পিকেম টুর্নামেন্ট',
            'pun_title' => 'ਪਿਕੈਮ ਟੂਰਨਾਮੈਂਟ',
            'tam_title' => 'பிக்கம் போட்டி',              
            'th_title' => 'ทัวร์นาเมนต์ Pickem',
            'kn_title' => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ',
            'ru_title' => 'Реквизит фантазия',
            'id_title' => 'Turnamen Pickem',
            'tl_title' => 'Pickem Tournament',
            'zh_title' => 'Pickem锦标赛',
            'en_desc' => "Pickem correct answer to win",
            'hi_desc' => "पिकम सही उत्तर जीतने के लिए",
            'guj_desc' => "જીત માટે પિકમ સાચો જવાબ ",
            'fr_desc' => 'Pickem bonne réponse à gagner.',
            'ben_desc' => 'পিকেম সঠিক উত্তর জয়ের',
            'pun_desc' => 'ਪਿਕਮ ਨੂੰ ਜਿੱਤਣ ਦਾ ਸਹੀ ਜਵਾਬ',
            'tam_desc' => 'வெற்றிக்கு சரியான பதில்',              
            'th_desc' => 'คำตอบที่ถูกต้องเพื่อชนะ',
            'kn_desc' => 'ಗೆಲುವಿಗೆ ಸರಿಯಾದ ಉತ್ತರ',
            'ru_desc' => 'Выберите правильный ответ на победу',
            'id_desc' => 'Pickem Jawaban yang Benar Untuk Menang.',
            'tl_desc' => 'Tamang sagot ni Pickem upang manalo.',
            'zh_desc' => 'Pickem正确的答案',
            'display_order' => 9,
            'allowed_sports' => NULL,
            'status' => 0
      );
      $sql = "UPDATE ".$this->db->dbprefix(SPORTS_HUB)." SET STATUS = 0 where game_key='allow_pickem'";
      $this->db->query($sql);

      $result = $this->db->select('*')->from(SPORTS_HUB)->where('game_key',"pickem_tournament")->get()->num_rows();
      if(!$result){
        $this->db->insert(SPORTS_HUB,$hub_setting);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',529)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '529',
                'name' => 'Pickem Tournament Game Join'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',530)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '530',
                'name' => 'Pickem Tournament Game Cancel'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',531)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '531',
                'name' => 'Pickem Tournament Game Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',532)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '532',
                'name' => 'Pickem Tournament TDS deduction'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',533)->get()->row_array();
    if(empty($result)){
        $data_arr = array(
                'source' => '533',
                'name' => 'Pickem Tournament Perfect score credit'
            );
        $this->db->insert(MASTER_SOURCE,$data_arr);
    }



    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"529")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
            array(
                'source' => 529,
                'en_message'      => 'Pickem tournament {{name}} Joined',
                'hi_message'      => 'पिकम टूर्नामेंट {{name}} शामिल हुए',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ {{name}} જોડાયો',
                'fr_message'      => 'Tournoi Pickem {{name}} rejoint',
                'ben_message'     => 'পিকেম টুর্নামেন্ট {{name}} যুক্ত',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ {name} ਸ਼ਾਮਲ ਹੋਏ',
                'tam_message'     => 'பிக்கம் போட்டி {{name}} இணைந்தது',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem {{name}} เข้าร่วม',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ {{name}} ಸೇರಿಕೊಂಡರು',
                'ru_message'      => 'Pickem Tournament {{name}} присоединился',
                'id_message'      => 'Turnamen Pickem {{name}} bergabung',
                'tl_message'      => 'Pickem Tournament {{name}} sumali',
                'zh_message'      => 'Pickem锦标赛 {{name}} 加入',
                'es_message'      => 'Torneo Pickem {{name}} unido',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "530")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
            array(
                'source' => 530,
                'en_message'      => 'Pickem tournament {{name}} cancellation fee refund',
                'hi_message'      => 'पिकम टूर्नामेंट {{name}} रद्दीकरण शुल्क वापसी',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ {{name}} રદ ફી રિફંડ',
                'fr_message'      => "Tournoi Pickem {{name}} remboursement des frais d'annulation",
                'ben_message'     => 'পিকেম টুর্নামেন্ট {{name}} বাতিল ফি ফেরত',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ {name}ਰੱਦ ਕਰਨ ਦੇ ਫੀਸ ਰਿਫੰਡ',
                'tam_message'     => 'பிக்கம் போட்டி {{name}} ரத்து கட்டணம் திருப்பிச் செலுத்துதல்',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem {{name}} การคืนเงินค่าธรรมเนียมการยกเลิก',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ {{name}} ರದ್ದತಿ ಶುಲ್ಕ ಮರುಪಾವತಿ',
                'ru_message'      => 'Pickem Tournament {{name}} Плата за отмену возврат средств',
                'id_message'      => 'Turnamen Pickem {{name}} Pengembalian Biaya Pembatalan',
                'tl_message'      => 'Pickem Tournament {{name}} Pagkansela Refund',
                'zh_message'      => 'Pickem锦标赛 {{name}} 取消费用退款',
                'es_message'      => 'Torneo Pickem {{name}} reembolso de la tarifa de cancelación',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "531")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
            array(
               'source' => 531,
                'en_message'      => 'Pickem tournament {{name}} winning credit',
                'hi_message'      => 'पिकम टूर्नामेंट {{name}} विजेता साख',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ {{name}} જીતવાની ધિરાણ',
                'fr_message'      => 'Tournoi Pickem {{name}} crédit gagnant',
                'ben_message'     => 'পিকেম টুর্নামেন্ট {{name}} বিজয়ী credit ণ',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ {name} ਜੇਤੂ ਕ੍ਰੈਡਿਟ',
                'tam_message'     => 'பிக்கம் போட்டி {{name}} கடன் வென்றது',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem {{name}} เครดิตที่ชนะ',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ {{name}} ಗೆಲುವಿನ ಕ್ರೆಡಿಟ್',
                'ru_message'      => 'Pickem Tournament {{name}} Победный кредит',
                'id_message'      => 'Turnamen Pickem {{name}} Menang Kredit',
                'tl_message'      => 'Pickem Tournament {{name}} nanalong kredito',
                'zh_message'      => 'Pickem锦标赛 {{name}} 赢得信用',
                'es_message'      => 'Torneo Pickem {{name}} crédito ganador',

            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }



    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "532")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
            array(
                 'source' => 532,
                'en_message'      => 'Pickem tournament TDS Deduction',
                'hi_message'      => 'पिकम टूर्नामेंट टीडीएस कटौती',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ ટી.ડી.એસ.',
                'fr_message'      => 'Tournoi Pickem Déduction TDS',
                'ben_message'     => 'পিকেম টুর্নামেন্ট টিডিএস ছাড়',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ ਟੀਡੀਜ਼ ਕਟੌਤੀ',
                'tam_message'     => 'பிக்கம் போட்டி டி.டி.எஸ் விலக்கு',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem การหัก TDS',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ ಟಿಡಿಎಸ್ ಕಡಿತ',
                'ru_message'      => 'Pickem Tournament Вычет TDS',
                'id_message'      => 'Turnamen Pickem Pengurangan TDS',
                'tl_message'      => 'Pickem Tournament Pagbabawas ng TDS',
                'zh_message'      => 'Pickem锦标赛 TDS扣除',
                'es_message'      => 'Deducción de tournamentos de pickem TDS'
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

     $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "533")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
            array(
                 'source' => 533,
                'en_message'      => "Pickem tournament perfect score credit",
                'hi_message'      => 'पिकम टूर्नामेंट परफेक्ट स्कोर साख',
                'guj_message'     => 'પિકમ ટૂર્નામેન્ટ સંપૂર્ણ સ્કોર ધિરાણ',
                'fr_message'      => 'Tournoi Pickem Crédit de score parfait',
                'ben_message'     => 'পিকেম টুর্নামেন্ট পারফেক্ট স্কোর ক্রেডিট',
                'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ ਸੰਪੂਰਨ ਸਕੋਰ ਕ੍ਰੈਡਿਟ',
                'tam_message'     => 'பிக்கம் போட்டி சரியான மதிப்பெண் கடன்',
                'th_message'      => 'ทัวร์นาเมนต์ Pickem Perpektong iskor credit',
                'kn_message'      => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ ಪರಿಪೂರ್ಣ ಸ್ಕೋರ್ ಕ್ರೆಡಿಟ್',
                'ru_message'      => 'Pickem Tournament Идеальный кредит',
                'id_message'      => 'Turnamen Pickem Kredit Skor Sempurna',
                'tl_message'      => 'Pickem Tournament Perpektong iskor credit',
                'zh_message'      => 'Pickem锦标赛 完美的分数信用',
                'es_message'      => 'Torneo Pickem Credit de puntaje perfecto'
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }



     $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "650")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description =array(
                    "notification_type" =>650,
                    "en_subject"    =>"Game Join",
                    "hi_subject"    =>"खेल में शामिल हों",
                    "guj_subject"   =>"પિકસ ફૅન્ટેસી",
                    "fr_subject"    =>"Joindre",
                    "ben_subject"   =>"খেলা যোগ দিন",
                    "pun_subject"   =>"ਖੇਡ ਸ਼ਾਮਲ ਹੋਵੋ",
                    "th_subject"    =>"เเกมเข้าร่วม",
                    "kn_subject"    =>"ಗೇಮ್ ಸೇರಲು",
                    "ru_subject"    =>"Игра Присоединиться",
                    "id_subject"    =>"Game Gabung",
                    "tl_subject"    =>"Sumali ang laro",
                    "zh_subject"    =>"游戏加入",
                    "message"       => "Pickem tournament {{name}} Joined successfully",
                    'en_message'    => 'Pickem tournament {{name}} Joined successfully',
                    'hi_message'    => 'पिकम टूर्नामेंट {{name}} शामिल हुए सफलतापूर्वक',
                    'guj_message'   => 'પિકમ ટૂર્નામેન્ટ {{name}} જોડાયો સફળતાપૂર્વક ',
                    'fr_message'    => 'Tournoi Pickem {{name}} avec succès',
                    'ben_message'   => 'পিকেম টুর্নামেন্ট {{name}} যুক্ত সফলভাবে',
                    'pun_message'   => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ {{name}} ਸ਼ਾਮਲ ਹੋਏ',
                    'tam_message'   => 'பிக்கம் போட்டி {{name}} இணைந்தது ਸਫਲਤਾਪੂਰਵਕ',
                    'th_message'    => 'ทัวร์นาเมนต์ Pickem {{name}} เข้าร่วม อย่างประสบความสำเร็จ',
                    'kn_message'    => 'ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ {{name}} ಸೇರಿಕೊಂಡರು ಯಶಸ್ವಿಯಾಗಿ',
                    'ru_message'    => 'Pickem Tournament {{name}} присоединился успешно',
                    'id_message'    => 'Turnamen Pickem {{name}} berhasil',
                    'tl_message'    => 'Pickem Tournament {{name}} sumali matagumpay',
                    'zh_message'    => 'Pickem锦标赛 {{name}} 加入 成功地',
                    'es_message'    => 'Torneo Pickem {{name}} unido con éxito'


                );
                $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        
        $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "649")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description = array(
                "notification_type" =>649,
                "en_subject"    =>"Game Cancelled",
                "hi_subject"    =>"खेल रद्द",
                "guj_subject"   =>"રમત રદ",
                "fr_subject"    =>"Jeu annulé",
                "ben_subject"   =>"খেলা বাতিল",
                "pun_subject"   =>"ਖੇਡ ਰੱਦ ਕੀਤੀ ਗਈ",
                "th_subject"    =>"ยกเลิกเกม",
                "kn_subject"    =>"ಗೇಮ್ ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ",
                "ru_subject"    =>"Игра отменена",
                "id_subject"    =>"Game dibatalkan",
                "tl_subject"    =>"Kinansela ang laro.",
                "zh_subject"    =>"游戏取消了",
                "message"       =>"Pickem tournament {{name}} canceled {{cancel_reason}}.",
                "en_message"     =>"Pickem tournament {{name}}  canceled {{cancel_reason}}.",
                "hi_message"    =>"पिकम टूर्नामेंट{{name}}  रद्द कर दिया गया {{cancel_reason}} ।",
                "guj_message"   =>"પિકમ ટૂર્નામેન્ટ {{name}}  રદ થયેલ {{cancel_reason}} .",
                "fr_message"    =>"Pickem tournament {{name}}  annulé {{cancel_reason}} .",
                "ben_message"   =>"পিকেম টুর্নামেন্ট {{name}}  বাতিল {{cancel_reason}}",
                "pun_message"   =>"ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ {{name}}  ਰੱਦ ਕਰੋ {{cancel_reason}} ਰੱਦ ਕਰੋ.",
                "tam_message"   =>"பிக்கம் போட்டி {{name}}  ரத்து செய்யப்பட்டது {{cancel_reason}} .",
                "th_message"    =>"ทัวร์นาเมนต์ Pickem {{name}}  ถูกยกเลิก {{cancel_reason}} .",
                "kn_message"    =>"ಪಿಕಮ್ ಟೂರ್ನಮೆಂಟ್ {{name}}  ರದ್ದುಗೊಂಡ {{cancel_reason}} .",
                "ru_message"    =>"Pickem Tournament {{name}}  отменен {{cancel_reason}} .",
                "id_message"    =>"Turnamen Pickem {{name}}  dibatalkan {{cancel_reason}} .",
                "tl_message"    =>"Pickem Tournament {{name}} kanselahin ang {{cancel_reason}} .",
                "zh_message"    =>"Pickem锦标赛 {{name}} 取消{{cancel_reason}} .",
                'es_message'    => 'Torneo Pickem {{name}} cancelado {{cancel_reason}}.'
            );
             $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
         $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "651")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description =array(
                "notification_type" =>651,
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
                "message"       =>"Congratulations! You're a winner in the {{name}} tournament.",
                "en_message"     =>"Congratulations! You're a winner in the {{name}} tournament.",
                "hi_message"    =>"बधाई हो! आप {{name}} टूर्नामेंट में एक विजेता हैं।",
                "guj_message"   =>"અભિનંદન! તમે {{name}} ટૂર્નામેન્ટમાં વિજેતા છો.",
                "fr_message"    =>"Toutes nos félicitations! Vous êtes un gagnant dans le tournoi {{name}}.",
                "ben_message"   =>"অভিনন্দন! আপনি {{name}} টুর্নামেন্টে একজন বিজয়ী হন।",
                "pun_message"   =>"ਵਧਾਈਆਂ! ਤੁਸੀਂ} {name}} of ਟੂਰਨਾਮੈਂਟ ਵਿੱਚ ਇੱਕ ਵਿਜੇਤਾ ਦੁਬਾਰਾ ਲੈ ਕੇ.",
                "tam_message"   =>"வாழ்த்துக்கள்! {{name}} போட்டியில் நீங்கள் ஒரு வெற்றியாளராக இருக்கிறீர்கள்.",
                "th_message"    =>"ยินดีด้วย! คุณเป็นผู้ชนะในทัวร์นาเมนต์ {{name}}",
                "kn_message"    =>"ಅಭಿನಂದನೆಗಳು! ನೀವು {{name}} ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ವಿಜೇತರಾಗಿದ್ದೀರಿ.",
                "ru_message"    =>"Поздравляю! Вы победитель в турнире {{name}}.",
                "id_message"    =>"Selamat! Anda adalah pemenang di turnamen {{name}}.",
                "tl_message"    =>"Binabati kita! Ikaw ay isang nagwagi sa {{name}} Tournament.",
                "zh_message"    =>"恭喜！您是 {{name}} 锦标赛中的赢家。",
                'es_message'   => 'ongratulaciones! Usted es un ganador en el torneo {{name}}.'
            );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        
        $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "652")
            ->get()
            ->row_array();
        if(empty($row)) {
                $notification_description =array(
                    "notification_type" =>652,
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
                    "message"       =>"Pickem tournament {{currency}}{{amount}} deducted as TDS",
                    "en_message"    =>"Pickem tournament {{currency}}{{amount}} deducted as TDS",
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
                    "tl_message"    =>"Pickem Tournament {{currency}}{{amount}} ibabawas bilang tds",
                    "zh_message"    =>"Pickem锦标赛 {{currency}}{{amount}}被扣除为TDS",
                    "es_message"    => "Torneo Pickem {{moneda}} {{cantidad}} deducido como TDS"
              );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 650)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 650,
                            'template_name'=> 'tournament_join',
                            'template_path'=> 'tournament_join',
                            'status' => '1',
                            'display_label' => 'Pickem Tournament Game Join',
                            'subject' => 'Your Contest Joining is Confirmed!'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 649)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 649,
                            'template_name'=> 'tournament_cancel',
                            'template_path'=> 'tournament_cancel',
                            'status' => '1',
                            'display_label' => 'Pickem Tournament Game Cancel',
                            'subject' => 'Contest Canceled by Admin'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 651)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 651,
                            'template_name'=> 'tournament_won',
                            'template_path'=> 'tournament_won',
                            'status' => '1',
                            'display_label' => 'Pickem Tournament Game Won',
                            'subject' => 'Congratulations - Game Won'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

           $sql = "UPDATE `vi_sports_hub` SET `allowed_sports` = '[7, 5]' WHERE `vi_sports_hub`.`game_key` = 'pickem_tournament'";
    $this->db->query($sql);
       
    }
    public function down()
    {
        //down script
    }
}
