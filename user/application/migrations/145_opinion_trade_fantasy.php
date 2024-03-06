<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Opinion_trade_fantasy extends CI_Migration {

    public function up() {

      $hub_setting = array(
            'game_key' => 'opinion_trade_fantasy',
            'en_title' => "Opinion Trade Fantasy",              
            'hi_title'=> "राय व्यापार फंतासी",
            'guj_title' => 'ઓપિનિયન ટ્રેડ ફૅન્ટેસી',
            'fr_title' => 'Fantaisie du commerce d’opinion',
            'ben_title' => 'মতামত বাণিজ্য ফ্যান্টাসি',
            'pun_title' => 'ਵਿਚਾਰ ਵਪਾਰ ਕਲਪਨਾ',
            'tam_title' => 'கருத்து வர்த்தக கற்பனை',              
            'th_title' => 'ความคิดเห็นการค้าแฟนตาซี',
            'kn_title' => 'ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರ ಫ್ಯಾಂಟಸಿ',
            'ru_title' => 'Мнение Торговая фантазия',
            'id_title' => 'Opini Perdagangan Fantasi',
            'tl_title' => 'Opinyon Trade Fantasy',
            'zh_title' => '意见贸易幻想',
            'en_desc' => "Opinion trade results and award winning",
            'hi_desc' => "राय व्यापार परिणाम और पुरस्कार विजेता",
            'guj_desc' => "અભિપ્રાય વેપાર પરિણામો અને એવોર્ડ વિજેતા",
            'fr_desc' => "Résultats du commerce d'opinion et primés",
            'ben_desc' => 'মতামত বাণিজ্য ফলাফল এবং পুরস্কার বিজয়ী',
            'pun_desc' => 'ਵਿਚਾਰ ਵਪਾਰ ਦੇ ਨਤੀਜੇ ਅਤੇ ਪੁਰਸਕਾਰ ਜੇਤੂ',
            'tam_desc' => 'கருத்து வர்த்தக முடிவுகள் மற்றும் விருது வென்றது',              
            'th_desc' => 'ความคิดเห็นผลการเทรดและการได้รับรางวัล',
            'kn_desc' => 'ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರ ಫಲಿತಾಂಶಗಳು ಮತ್ತು ಪ್ರಶಸ್ತಿ ವಿಜೇತ',
            'ru_desc' => 'Результаты обмена мнениями и получение наград',
            'id_desc' => 'Hasil perdagangan opini dan pemenang penghargaan',
            'tl_desc' => 'Mga resulta ng kalakalan ng opinyon at pagkapanalo ng award',
            'zh_desc' => '意见交易结果及获奖情况',
            'display_order' => 12,
            'allowed_sports' => json_encode([5,7]),
            'status' => 0
      );

      $result = $this->db->select('*')->from(SPORTS_HUB)->where('game_key',"opinion_trade_fantasy")->get()->row_array();
      if(!$result){
        $this->db->insert(SPORTS_HUB,$hub_setting);
      }else{
        $this->db->update(SPORTS_HUB,$hub_setting,array('sports_hub_id'=>$result['sports_hub_id']));
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',542)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
          'source' => '542',
          'name' => 'Opinion Trade Join Entry'
        );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

       $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',543)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
          'source' => '543',
          'name' => 'Opinion Trade Fantasy Refund Entry'
        );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',544)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '544',
                'name' => 'Opinion Trade Fantasy Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',545)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
          'source' => '545',
          'name' => 'Opinion Trade Fantasy TDS Deduction'
        );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"542")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
        array(
          'source' => 542,
          'en_message'      => '{{match_name}} join for opinion trade',
          'hi_message'      => '{{match_name}} राय व्यापार के लिए जुड़ें',
          'guj_message'     => '{{match_name}} અભિપ્રાય વેપાર માટે જોડાઓ',
          'fr_message'      => "{{match_name}} rejoignez-nous pour le commerce d'opinion",
          'ben_message'     => '{{match_name}} মতামত বাণিজ্যের জন্য যোগদান করুন',
          'pun_message'     => '{{match_name}} ਰਾਏ ਵਪਾਰ ਲਈ ਸ਼ਾਮਲ ਹੋਵੋ',
          'tam_message'     => '{{match_name}} கருத்து வர்த்தகத்தில் சேரவும்',
          'th_message'      => '{{match_name}} เข้าร่วมเพื่อการค้าความคิดเห็น',
          'kn_message'      => '{{match_name} ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರಕ್ಕಾಗಿ ಸೇರಿಕೊಳ್ಳಿ',
          'ru_message'      => '{{match_name}} присоединяйтесь к торговле мнениями',
          'id_message'      => '{{match_name}} bergabung untuk perdagangan opini',
          'tl_message'      => '{{match_name}} sumali para sa kalakalan ng opinyon',
          'zh_message'      => '{{match_name}} 加入意见交易',
          'es_message'      => '{{match_name}} únete para intercambiar opiniones',
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    

      $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "543")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = 
        array(
          'source' => 543,
          'en_message'      => 'Fee Refund {{match_name}} for opinion trade',
          'hi_message'      => '{{match_name}} ओपिनियन ट्रेड के लिए शुल्क वापसी',
          'guj_message'     => '{{match_name}} અભિપ્રાય વેપાર માટે ફી રિફંડ',
          'fr_message'      => "Remboursement des frais pour l'échange d'opinion {{match_name}}",
          'ben_message'     => '{{match_name}} মতামত বাণিজ্যের জন্য ফি ফেরত৷',
          'pun_message'     => '{{match_name}} ਰਾਏ ਵਪਾਰ ਲਈ ਫੀਸ ਰਿਫੰਡ',
          'tam_message'     => '{{match_name}} கருத்து வர்த்தகத்திற்கான கட்டணம் திரும்பப்பெறுதல்',
          'th_message'      => 'การคืนเงินค่าธรรมเนียมสำหรับการแลกเปลี่ยนความคิดเห็นของ {{match_name}}',
          'kn_message'      => '{{match_name}} ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರಕ್ಕಾಗಿ ಶುಲ್ಕ ಮರುಪಾವತಿ',
          'ru_message'      => 'Возврат комиссии за торговлю мнением {{match_name}}',
          'id_message'      => 'Pengembalian Biaya untuk perdagangan opini {{match_name}}',
          'tl_message'      => 'Refund ng Bayad para sa pangangalakal ng opinyon ng {{match_name}}.',
          'zh_message'      => '{{match_name}}意见交易的费用退款',
          'es_message'      => 'Reembolso de tarifa por intercambio de opinión de {{match_name}}',
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "544")
            ->get()
            ->row_array();
    if(empty($row)) {
      $transaction_messages = 
      array(
        'source' => 544,
        'en_message'      => 'Won {{match_name}} in opinion trade',
        'hi_message'      => 'ओपिनियन ट्रेड में {{match_name}} जीता',
        'guj_message'     => 'ઓપિનિયન ટ્રેડમાં {{match_name}} જીત્યા',
        'fr_message'      => "A gagné {{match_name}} dans le cadre d'un échange d'opinion",
        'ben_message'     => 'মতামত বাণিজ্যে {{match_name}} জিতেছে৷',
        'pun_message'     => 'ਰਾਏ ਵਪਾਰ ਵਿੱਚ {{match_name}} ਜਿੱਤਿਆ',
        'tam_message'     => 'கருத்து வர்த்தகத்தில் {{match_name}} வென்றது',
        'th_message'      => 'ชนะ {{match_name}} ในการแลกเปลี่ยนความคิดเห็น',
        'kn_message'      => 'ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರದಲ್ಲಿ {{match_name}} ಗೆದ್ದಿದೆ',
        'ru_message'      => 'Выиграл {{match_name}} в обмене мнениями',
        'id_message'      => 'Memenangkan {{match_name}} dalam perdagangan opini',
        'tl_message'      => 'Nanalo ng {{match_name}} sa opinion trade',
        'zh_message'      => '在意见交易中赢得了 {{match_name}}',
        'es_message'      => 'Ganó {{match_name}} en el intercambio de opiniones',
      );
      $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }



    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "545")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages =
        array(
          'source' => 545,
          'en_message'      => 'Opinion Trade TDS Deduction',
          'hi_message'      => 'ओपिनियन ट्रेड टीडीएस कटौती',
          'guj_message'     => 'ઓપિનિયન ટ્રેડ TDS કપાત',
          'fr_message'      => "Déduction TDS pour les échanges d'opinion",
          'ben_message'     => 'মতামত ট্রেড টিডিএস ডিডাকশন',
          'pun_message'     => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ ਟੀਡੀਜ਼ ਕਟੌਤੀ',
          'tam_message'     => 'கருத்து வர்த்தக TDS விலக்கு',
          'th_message'      => 'ความคิดเห็นการค้าการหัก TDS',
          'kn_message'      => 'ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರ TDS ಕಡಿತ',
          'ru_message'      => 'Вычет TDS по торговле мнениями',
          'id_message'      => 'Pengurangan TDS Perdagangan Opini',
          'tl_message'      => 'Opinyon Trade TDS Deduction',
          'zh_message'      => '意见交易 TDS 扣除',
          'es_message'      => 'Opinión Comercio Deducción TDS'
            
        );
        $this->db->insert(TRANSACTION_MESSAGES, $transaction_messages);
    }


     $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "657")
            ->get()
            ->row_array();
        if(empty($row)) {
          $notification_description =array(
            "notification_type" =>657,
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
            "message"       => "Your Opinion of {{amount}} has been placed on the {{match_name}} match for '{{opinion}}' with a quantity of {{quantity}}.",
            'en_message'    => "Your Opinion of {{amount}} has been placed on the {{match_name}} match for '{{opinion}}' with a quantity of {{quantity}}.",
            'hi_message'    => "आपकी राय {{amount}} को {{quantity}} की मात्रा के साथ '{{opinion}}' के लिए {{match_name}} मिलान पर रखा गया है।",
            'guj_message'   => "તમારો {{amount}} અભિપ્રાય '{{opinion}}' માટે {{match_name}} મેચ પર {{quantity}} ના જથ્થા સાથે મૂકવામાં આવ્યો છે.",
            'fr_message'    => "Votre opinion de {{amount}} a été placée sur la correspondance {{match_name}} pour '{{opinion}}' avec une quantité de {{quantity}}.",
            'ben_message'   => "আপনার {{amount}}-এর মতামত '{{opinion}}'-এর জন্য {{match_name}} মিলের উপর {{quantity}} পরিমাণের সাথে রাখা হয়েছে।",
            'pun_message'   => "ਤੁਹਾਡੀ {{amount}} ਦੀ ਰਾਏ '{{opinion}}' ਦੇ {{match_name}} ਮੈਚ 'ਤੇ {{quantity}} ਦੀ ਮਾਤਰਾ ਦੇ ਨਾਲ ਰੱਖੀ ਗਈ ਹੈ।",
            'tam_message'   => "உங்கள் {{அமவுண்ட்}} கருத்து {{opinion}}' க்கான {{match_name}} பொருத்தத்தில் {{quantity}} அளவுடன் வைக்கப்பட்டுள்ளது.",
            'th_message'    => "ความคิดเห็นของคุณเกี่ยวกับ {{amount}} ได้ถูกวางไว้สำหรับการจับคู่ {{match_name}} สำหรับ '{{opinion}}' ด้วยจำนวน {{quantity}}",
            'kn_message'    => "ನಿಮ್ಮ {{amount}} ಅಭಿಪ್ರಾಯವನ್ನು {{opinion}}' ಗಾಗಿ {{match_name}} ಹೊಂದಾಣಿಕೆಯ ಮೇಲೆ {{quantity}} ಪ್ರಮಾಣದೊಂದಿಗೆ ಇರಿಸಲಾಗಿದೆ.",
            'ru_message'    => "Ваше мнение о {{amount}} было размещено в совпадении {{match_name}} для '{{opinion}}' с количеством {{quantity}}.",
            'id_message'    => "Pendapat Anda tentang {{amount}} telah ditempatkan pada pertandingan {{match_name}} untuk '{{opinion}}' dengan kuantitas {{quantity}}.",
            'tl_message'    => "Ang iyong Opinyon ng {{amount}} ay inilagay sa {{match_name}} na tugma para sa '{{opinion}}' na may dami ng {{quantity}}.",
            'zh_message'    => "您對 {{amount}} 的意見已被添加到“{{opinion}}”的 {{match_name}} 匹配項中，數量為 {{quantity}}。",
            'es_message'    => "Su opinión de {{amount}} se ha colocado en el partido de {{match_name}} para '{{opinion}}' con una cantidad de {{quantity}}."
          );
          $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        

         $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "658")
            ->get()
            ->row_array();
        if(empty($row)) {
            $notification_description =array(
                "notification_type" =>658,
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
                "message"       =>"Congratulations! You’ve won your opinion on the {{match_name}} match for ‘{{opinion}}’. Enjoy your victory!",
                "en_message"     =>"Congratulations! You’ve won your opinion on the {{match_name}} match for ‘{{opinion}}’. Enjoy your victory!",
                "hi_message"    =>"बधाई हो! आपने '{{opinion}}' के लिए {{match_name}} मैच पर अपनी राय जीत ली है। अपनी जीत का आनंद लें!",
                "guj_message"   =>"અભિનંદન! તમે ‘{{opinion}}’ માટે {{match_name}} મેચ પર તમારો અભિપ્રાય જીતી લીધો છે. તમારી જીતનો આનંદ માણો!",
                "fr_message"    =>"Toutes nos félicitations! Vous avez gagné votre avis sur le match {{match_name}} pour « {{opinion}} ». Profitez de votre victoire !",
                "ben_message"   =>"অভিনন্দন! আপনি ‘{{opinion}}’-এর জন্য {{match_name}} ম্যাচে আপনার মতামত জিতেছেন। আপনার বিজয় উপভোগ করুন!",
                "pun_message"   =>"ਵਧਾਈਆਂ! ਤੁਸੀਂ '{{opinion}}' ਲਈ {{match_name}} ਮੈਚ 'ਤੇ ਆਪਣੀ ਰਾਏ ਜਿੱਤ ਲਈ ਹੈ। ਆਪਣੀ ਜਿੱਤ ਦਾ ਆਨੰਦ ਮਾਣੋ!",
                "tam_message"   =>"வாழ்த்துகள்! ‘{{opinion}}’க்கான {{match_name}} போட்டியில் உங்கள் கருத்தை வென்றுள்ளீர்கள். உங்கள் வெற்றியை அனுபவியுங்கள்!",
                "th_message"    =>"ยินดีด้วย! คุณได้รับความคิดเห็นของคุณในการแข่งขัน {{match_name}} สำหรับ '{{opinion}}' สนุกกับชัยชนะของคุณ!",
                "kn_message"    =>"ಅಭಿನಂದನೆಗಳು! ‘{{opinion}}’ ಗಾಗಿ {{match_name}} ಹೊಂದಾಣಿಕೆಯಲ್ಲಿ ನಿಮ್ಮ ಅಭಿಪ್ರಾಯವನ್ನು ನೀವು ಗೆದ್ದಿದ್ದೀರಿ. ನಿಮ್ಮ ವಿಜಯವನ್ನು ಆನಂದಿಸಿ!",
                "ru_message"    =>"Поздравляем! Вы получили свое мнение по совпадению {{match_name}} для '{{opinion}}'. Наслаждайтесь своей победой!",
                "id_message"    =>"Selamat! Anda memenangkan opini Anda tentang pertandingan {{match_name}} untuk ‘{{opinion}}’. Nikmati kemenangan Anda!",
                "tl_message"    =>"Binabati kita! Nakuha mo ang iyong opinyon sa {{match_name}} na laban para sa ‘{{opinion}}’. Masiyahan sa iyong tagumpay!",
                "zh_message"    =>"恭喜！您已贏得對“{{opinion}}”的 {{match_name}} 比賽的意見。享受你的勝利吧！",
                'es_message'   => "¡Felicidades! Has ganado tu opinión sobre la coincidencia de {{match_name}} para '{{opinion}}'. ¡Disfruta tu victoria!"
            );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }
        
        $row = $this->db->select('notification_type')
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "659")
            ->get()
            ->row_array();
        if(empty($row)) {
              $notification_description =array(
                "notification_type" =>659,
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
                "message"       =>"Opinion Trade {{currency}}{{amount}} deducted as TDS",
                "en_message"    =>"Opinion Trade {{currency}}{{amount}} deducted as TDS",
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
            ->from(NOTIFICATION_DESCRIPTION)
            ->where('notification_type', "660")
            ->get()
            ->row_array();
        if(empty($row)) {
              $notification_description =array(
                "notification_type" =>660,
                "en_subject"    =>"Fee Refund for opinion trade",
                "hi_subject"    =>"राय व्यापार के लिए शुल्क वापसी",
                "guj_subject"   =>"અભિપ્રાય વેપાર માટે ફી રિફંડ",
                "fr_subject"    =>"Remboursement des frais pour le commerce d'opinion",
                "ben_subject"   =>"মতামত বাণিজ্যের জন্য ফি ফেরত",
                "pun_subject"   =>"ਰਾਏ ਵਪਾਰ ਲਈ ਫੀਸ ਰਿਫੰਡ",
                "th_subject"    =>"การคืนเงินค่าธรรมเนียมสำหรับการค้าความคิดเห็น",
                "kn_subject"    =>"ಅಭಿಪ್ರಾಯ ವ್ಯಾಪಾರಕ್ಕಾಗಿ ಶುಲ್ಕ ಮರುಪಾವತಿ",
                "ru_subject"    =>"Возврат комиссии за торговлю мнениями",
                "id_subject"    =>"Pengembalian Biaya untuk perdagangan opini",
                "tl_subject"    =>"Refund ng Bayad para sa kalakalan ng opinyon",
                "zh_subject"    =>"意見交易費用退款",
                "message"       =>"Your opinion on the {{match_name}} match for '{{opinion}}' was {{refund_type}}, and a refund has been processed.",
                "en_message"    =>"Your opinion on the {{match_name}} match for '{{opinion}}' was {{refund_type}}, and a refund has been processed.",
                "hi_message"    =>"'{{opinion}}' के लिए {{match_name}} मिलान पर आपकी राय {{refund_type}} थी, और धनवापसी की प्रक्रिया कर दी गई है।",
                "guj_message"   =>"'{{opinion}}' માટે {{match_name}} મેચ પર તમારો અભિપ્રાય {{refund_type}} હતો, અને રિફંડની પ્રક્રિયા કરવામાં આવી છે.",
                "fr_message"    =>"Votre opinion sur la correspondance {{match_name}} pour '{{opinion}}' était de {{refund_type}} et un remboursement a été traité.",
                "ben_message"   =>"'{{opinion}}'-এর জন্য {{match_name}} ম্যাচের বিষয়ে আপনার মতামত ছিল {{refund_type}}, এবং একটি ফেরত প্রক্রিয়া করা হয়েছে৷",
                "pun_message"   =>"'{{opinion}}' ਲਈ {{match_name}} ਮੈਚ 'ਤੇ ਤੁਹਾਡੀ ਰਾਏ {{refund_type}} ਸੀ, ਅਤੇ ਰਿਫੰਡ ਦੀ ਪ੍ਰਕਿਰਿਆ ਹੋ ਗਈ ਹੈ।",
                "tam_message"   =>"'{{opinion}}' க்கான {{match_name}} பொருத்தம் குறித்த உங்கள் கருத்து {{refund_type}} ஆகும், மேலும் பணம் திரும்பப் பெறப்பட்டது.",
                "th_message"    =>"ความคิดเห็นของคุณเกี่ยวกับการจับคู่ {{match_name}} สำหรับ '{{opinion}}' คือ {{refund_type}} และได้ดำเนินการคืนเงินแล้ว",
                "kn_message"    =>"'{{opinion}}' ಗೆ {{match_name}} ಹೊಂದಾಣಿಕೆಯ ಕುರಿತು ನಿಮ್ಮ ಅಭಿಪ್ರಾಯವು {{refund_type}} ಆಗಿತ್ತು ಮತ್ತು ಮರುಪಾವತಿಯನ್ನು ಪ್ರಕ್ರಿಯೆಗೊಳಿಸಲಾಗಿದೆ.",
                "ru_message"    =>"Ваше мнение о совпадении {{match_name}} для '{{opinion}}' было {{refund_type}}, и возврат средств был обработан.",
                "id_message"    =>"Pendapat Anda tentang kecocokan {{match_name}} untuk '{{opinion}}' adalah {{refund_type}}, dan pengembalian dana telah diproses.",
                "tl_message"    =>"Ang iyong opinyon sa {{match_name}} na tugma para sa '{{opinion}}' ay {{refund_type}}, at isang refund ang naproseso.",
                "zh_message"    =>"您對「{{opinion}}」的 {{match_name}} 匹配的意見是 {{refund_type}}，退款已處理。",
                "es_message"    => "Su opinión sobre la coincidencia de {{match_name}} para '{{opinion}}' fue {{refund_type}} y se procesó un reembolso."
            );

            $this->db->insert(NOTIFICATION_DESCRIPTION, $notification_description);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 657)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
              'notification_type' => 657,
              'template_name'=> 'entry_join',
              'template_path'=> 'entry_join',
              'status' => '1',
              'display_label' => 'Opinion Trade Entry Join',
              'subject' => 'Your Entry Joining is Confirmed!'
            );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }


         $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 658)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
              'notification_type' => 658,
              'template_name'=> 'trade_tds',
              'template_path'=> 'trade_tds',
              'status' => '1',
              'display_label' => 'Opinion Trade Winning Tds',
              'subject' => 'Opinion Trade TDS Deduction'
            );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }


    }
    public function down()
    {
        //down script
    }
}
