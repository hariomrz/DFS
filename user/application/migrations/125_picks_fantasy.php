<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Picks_fantasy extends CI_Migration {

    public function up() {

      $hub_setting = array(
            'game_key' => 'picks_fantasy',
            'en_title' => "Picks Fantasy",              
            'hi_title'=> "पिक्स  फंतासी",
            'guj_title' => 'પિકસ ફૅન્ટેસી',
            'fr_title' => 'Picks fantaisie',
            'ben_title' => 'পিক্স  ફૅન્ટેસી',
            'pun_title' => 'ਪਿਕਸ  ਫੰਤਾਸੀ',
            'tam_title' => 'பிசிக்ஸ்  பாண்டஸி',              
            'th_title' => 'อุปกรณ์ประกอบฉากแฟนตาซี',
            'kn_title' => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ',
            'ru_title' => 'Реквизит фантазия',
            'id_title' => 'Picks Fantasy',
            'tl_title' => 'Picks fantasy',
            'zh_title' => '道具幻想',
            'en_desc' => "Picks correct answer to win",
            'hi_desc' => "जीतने के लिए सही उत्तर चुनें",
            'guj_desc' => "જીતવા માટે અથવા સ્કોર ઉપર અથવા હેઠળ પ્રોજેક્ટ.",
            'fr_desc' => 'Projet sur ou en dessous du score pour gagner.',
            'ben_desc' => 'জয়ের জন্য স্কোর ওভার বা এর অধীনে প্রকল্প।',
            'pun_desc' => 'ਵੱਧ ਜਾਂ ਸਕੋਰ ਨੂੰ ਜਿੱਤਣ ਲਈ ਪ੍ਰੋਜੈਕਟ ਦੇ ਅਧੀਨ.',
            'tam_desc' => 'வெற்றிபெற மதிப்பெண்ணுக்கு மேல் அல்லது கீழ் திட்டம்.',              
            'th_desc' => 'โครงการเหนือหรือต่ำกว่าคะแนนที่จะชนะ',
            'kn_desc' => 'ಗೆಲ್ಲಲು ಸ್ಕೋರ್ ಓವರ್ ಅಥವಾ ಅಡಿಯಲ್ಲಿ ಪ್ರಾಜೆಕ್ಟ್ ಮಾಡಿ.',
            'ru_desc' => 'Проект над или под счетом, чтобы выиграть.',
            'id_desc' => 'Proyek lebih atau di bawah skor untuk menang.',
            'tl_desc' => 'Proyekto o sa ilalim ng marka upang manalo.',
            'zh_desc' => '在得分上或以下项目以获胜。',
            'display_order' => 8,
            'allowed_sports' => NULL,
            'status' => 0
      );
      $result = $this->db->select('*')->from(SPORTS_HUB)->where('game_key',"picks_fantasy")->get()->num_rows();
      if(!$result){
        $this->db->insert(SPORTS_HUB,$hub_setting);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',524)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '524',
                'name' => 'Picks Fantasy Game Join'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',525)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '525',
                'name' => 'Picks Fantasy Game Cancel'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',526)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '526',
                'name' => 'Picks Fantasy Game Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }



    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',527)->get()->row_array();
    if(empty($result)){
        $data_arr = array(
                'source' => '527',
                'name' => 'Picks Fantasy TDS Deduction'
            );
        $this->db->insert(MASTER_SOURCE,$data_arr);
    }

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',528)->get()->row_array();
    if(empty($result)){
        $data_arr = array(
                'source' => '528',
                'name' => 'Picks Fantasy Leaderboard winner'
            );
        $this->db->insert(MASTER_SOURCE,$data_arr);
    }


    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"524")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 524,
                'en_message'      => 'Picks Fantasy {{match}} Contest {{contest}} Joined',
                'hi_message'      => 'पिक्स फंतासी {{match}} प्रतियोगिता {{contest}} शामिल हुईं',
                'guj_message'     => 'પિકસ ફૅન્ટેસી {{મેચ}} હરીફાઈ {{હરીફાઈ}} જોડાયા',
                'fr_message'      => 'Choisissez Fantasy {{match}} concours {{concours}} rejoint',
                'ben_message'     => 'প্রপস ফ্যান্টাসি {{match}} প্রতিযোগিতা {{contest}} যুক্ত',
                'pun_message'     => 'ਸ਼ਾਨਦਾਰ fant fsy {{match}} ਮੁਕਾਬਲੇ {{contest}} ਸ਼ਾਮਲ ਹੋ ਗਿਆ',
                'tam_message'     => 'கற்பனை {{போட்டி}} போட்டி {{போட்டி}} இணைந்தது',
                'th_message'      => 'เลือกแฟนตาซี {{match}} การประกวด {{การประกวด}} เข้าร่วม',
                'kn_message'      => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{match}} ಸ್ಪರ್ಧೆ {{contest}} ಸೇರ್ಪಡೆಗೊಂಡಿದೆ',
                'ru_message'      => 'Реквизит Fantasy {{match}} конкурс {{contest}} присоединился',
                'id_message'      => 'Picks Fantasy {{match}} Contest {{contest}} bergabung',
                'tl_message'      => 'Picks fantasy {{match}} paligsahan {{contest}} sumali',
                'zh_message'      => 'Picks Fantasy {{match}}竞赛{{contest}}加入'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "525")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 525,
                'en_message'      => 'Picks Fantasy {{match}} Contest {{contest}} cancellation fee refund',
                'hi_message'      => 'पिक्स फंतासी {{match}} प्रतियोगिता {{contest}} रद्दीकरण शुल्क वापसी',
                'guj_message'     => 'પિકસ ફૅન્ટેસી {{મેચ}} હરીફાઈ {{હરીફાઈ}} રદ ફી રિફંડ',
                'fr_message'      => 'Choisissez Fantasy {{match}} concours {{concours}} remboursement des frais dannulation',
                'ben_message'     => 'প্রপস ফ্যান্টাসি {{match}} প্রতিযোগিতা {{contest}} বাতিল ফি ফেরত',
                'pun_message'     => 'ਸ਼ਾਨਦਾਰ fant fsy {{match}} ਮੁਕਾਬਲੇ {{contest}}ਰੱਦ ਕਰਨ ਦੀ ਫੀਸ ਦੀ ਵਾਪਸੀ',
                'tam_message'     => 'கற்பனை {{போட்டி}} போட்டி {{போட்டி}} ரத்து கட்டணம் திரும்ப',
                'th_message'      => 'เลือกแฟนตาซี {{match}} การประกวด {{การประกวด}} คืนเงินค่าธรรมเนียมการยกเลิก',
                'kn_message'      => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{match}} ಸ್ಪರ್ಧೆ {{contest}} ರದ್ದತಿ ಶುಲ್ಕ ಮರುಪಾವತಿ',
                'ru_message'      => 'Реквизит Fantasy {{match}} конкурс {{contest}} возврат платы за отмену',
                'id_message'      => 'Picks Fantasy {{match}} Contest {{contest}} pengembalian biaya pembatalan',
                'tl_message'      => 'Picks fantasy {{match}} paligsahan {{contest}} sumali',
                'zh_message'      => 'Picks Fantasy {{match}}竞赛{{contest}} 取消费用退款'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "526")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 526,
                'en_message'      => 'Picks Fantasy {{match}} contest winning credit',
                'hi_message'      => 'पिक्स फैंटेसी {{match}} प्रतियोगिता विनिंग क्रेडिट',
                'guj_message'     => 'પિકસ ફૅન્ટેસી {{match}} હરીફાઈ વિજેતા ક્રેડિટ',
                'fr_message'      => 'sélectionne Fantasy {{match}} Crédit gagnant du concours',
                'ben_message'     => 'প্রপস ফ্যান্টাসি {{match}} প্রতিযোগিতা বিজয়ী ক্রেডিট',
                'pun_message'     => 'ਸ਼ਾਨਦਾਰ fant fsy{{match}} ਮੁਕਾਬਲੇ ਦਾ ਕ੍ਰੈਡਿਟ ਲਿਖੋ',
                'tam_message'     => 'முன்மொழியப்பட்ட பேண்டஸி {{match}} போட்டி வென்ற கடன்',
                'th_message'      => 'Picks Fantasy {{match}} การแข่งขันที่ชนะเครดิต',
                'kn_message'      => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{match}} ಸ್ಪರ್ಧೆ ಗೆಲುವಿನ ಕ್ರೆಡಿಟ್',
                'ru_message'      => 'Реквизит Fantasy {{match}} Конкурс выигрышной кредит',
                'id_message'      => 'Picks Fantasy {{match}} Kontes pemenang kredit',
                'tl_message'      => 'Picks Fantasy {{match}} Contest Winning Credit',
                'zh_message'      => 'Picks Fantasy {{match}}竞赛赢得信用'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }



    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "527")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 527,
                'en_message'      => 'Picks Fantasy TDS Deduction',
                'hi_message'      => 'पिक्स फंतासी टीडी कटौती',
                'guj_message'     => 'પિકસ ફૅન્ટેસી ટીડીએસ કપાત',
                'fr_message'      => 'sélectionne de TDS Fantasy TDS',
                'ben_message'     => 'প্রপস ফ্যান্টাসি টিডিএস ছাড়',
                'pun_message'     => 'ਕਲਪਨਾ ਟੀਡੀਐਸ ਕਟੌਤੀ ਕਰੋ',
                'tam_message'     => 'Picks கற்பனை TDS விலக்கு',
                'th_message'      => 'อุปกรณ์ประกอบฉากการหัก TDS แฟนตาซี',
                'kn_message'      => 'ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ ಟಿಡಿಎಸ್ ಕಡಿತ',
                'ru_message'      => 'Реквизит фэнтезийный вычет TDS',
                'id_message'      => 'Picks Fantasy TDS Deduction',
                'tl_message'      => 'Picks Fantasy TDS Deduction',
                'zh_message'      => '道具幻想TD扣除'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }



    $notification_description = array(
            array(
                "notification_type" =>645,
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
                "message"       =>"Picks Fantasy {{contest_name}} – {{match}} joined successfully",
                "en_message"     =>"Picks Fantasy {{contest_name}} – {{match}} joined successfully",
                "hi_message"    =>"पिक्स फंतासी {{contest_name}} - {{match}} सफलतापूर्वक शामिल हुए",
                "guj_message"   =>"પિકસ કાલ્પનિક {{contest_name}} - {{match}} સફળતાપૂર્વક જોડાયો",
                "fr_message"    =>"Picks Fantasy {{contest_name}} - {{match}} a rejoint avec succès",
                "ben_message"   =>"প্রপস ফ্যান্টাসি {{contest_name}} - {{match}} সফলভাবে যোগদান করেছে",
                "pun_message"   =>"Picks flantasy {{contest_name}} - {{match}} lice ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋ ਗਿਆ",
                "tam_message"   =>"முன்மொழியப்பட்ட பேண்டஸி {{contest_name}} - {{match}} வெற்றிகரமாக சேர்ந்தார்",
                "th_message"    =>"Picks Fantasy {{contest_name}} - {{match}} เข้าร่วมสำเร็จ",
                "kn_message"    =>"ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{contest_name}} - {{match}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಿಕೊಂಡರು",
                "ru_message"    =>"Reps fantasy {{contest_name}} - {{match}} присоединился к успешно",
                "id_message"    =>"Picks Fantasy {{contest_name}} - {{match}} Bergabung dengan sukses",
                "tl_message"    =>"Picks fantasy {{contest_name}} - {{match}} matagumpay na sumali",
                "zh_message"    =>"Picks Fantasy {{contest_name}}  -  {{match}}成功连接"
            ),
            array(
                "notification_type" =>646,
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
                "message"       =>"Picks Fantasy {{match}} contest {{contest_name}} canceled {{cancel_reason}}.",
                "en_message"     =>"Picks Fantasy {{match}} contest {{contest_name}} canceled {{cancel_reason}}.",
                "hi_message"    =>"પિકસ फैंटेसी {{match}} प्रतियोगिता {{contest_name}} रद्द कर दिया गया {{{cancel_reason}}}।",
                "guj_message"   =>"પિકસ ફ ant ન્ટેસી {{match}} હરીફાઈ {{contest_name}} રદ થયેલ {{cancel_reason}}.",
                "fr_message"    =>"Picks Fantasy {{match}} Contest {{contest_name}} annulé {{cancel_reason}}.",
                "ben_message"   =>"প্রপস ফ্যান্টাসি {{match}} প্রতিযোগিতা {{contest_name}} বাতিল {{cancel_reason}}",
                "pun_message"   =>"ਫੈਨਟਸੀ {{match}} ਮੁਕਾਬਲੇ {{{contest_name}} ਰੱਦ ਕਰੋ {{cancel_reason}} ਰੱਦ ਕਰੋ.",
                "tam_message"   =>"முன்மொழியப்பட்ட பேண்டஸி {{match}} போட்டி {{contest_name}} ரத்து செய்யப்பட்டது {{cancel_reason}}.",
                "th_message"    =>"Picks Fantasy {{match}} การประกวด {{contest_name}} ถูกยกเลิก {{cancel_reason}}",
                "kn_message"    =>"ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{match}} ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಂಡ {{cancel_reason}}.",
                "ru_message"    =>"Picks fantasy {{match}} конкурс {{contest_name}} отменен {{cancel_reason}}.",
                "id_message"    =>"Picks Fantasy {{match}} Contest {{contest_name}} dibatalkan {{cancel_reason}}.",
                "tl_message"    =>"Picks fantasy {{match}} paligsahan {{contest_name}} kanselahin ang {{cancel_reason}}.",
                "zh_message"    =>"Picks Fantasy {{match}}竞赛{{contest_name}}取消{{cancel_reason}}。"
            ),
            array(
                "notification_type" =>647,
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
                "message"       =>"Congratulations! You\'re a winner in the {{match}} match.",
                "en_message"     =>"बधाई हो! आप {{मैच}} मैच के विजेता हैं।",
                "hi_message"    =>"प्रॉप्स फंतासी {{match}} प्रतियोगिता जीतने वाला क्रेडिट",
                "guj_message"   =>"પ્રોપ્સ કાલ્પનિક {{match}} હરીફાઈ વિજેતા ક્રેડિટ",
                "fr_message"    =>"Accessoires Fantasy {{match}} Crédit gagnant du concours",
                "ben_message"   =>"প্রপস ফ্যান্টাসি {{match}} প্রতিযোগিতা বিজয়ী ক্রেডিট",
                "pun_message"   =>"ਫੈਨਟਸੀ {{match}} ਮੁਕਾਬਲਾ ਕ੍ਰੈਡਿਟ ਜਿੱਤਣ ਦੀ ਪੇਸ਼ਕਸ਼ ਕਰਦਾ ਹੈ",
                "tam_message"   =>"முன்மொழியப்பட்ட பேண்டஸி {{match}} போட்டியில் வென்ற கிரெடிட்",
                "th_message"    =>"Picks Fantasy {{match}} การแข่งขันที่ชนะเครดิต",
                "kn_message"    =>"ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{match}} ಸ್ಪರ್ಧೆ ಗೆಲ್ಲುವ ಕ್ರೆಡಿಟ್",
                "ru_message"    =>"Реквизит Fantasy {{match}} Конкурс выигрышный кредит",
                "id_message"    =>"Picks Fantasy {{match}} Kontes pemenang kredit",
                "tl_message"    =>"Picks fantasy {{match}} paligsahan winning credit",
                "zh_message"    =>"道具幻想{{match}}竞赛赢得信用"
            ),
            array(
                "notification_type" =>648,
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
                "message"       =>"Picks Fantasy {{currency}}{{amount}} deducted as TDS",
                "en_message"     =>"Picks Fantasy {{currency}}{{amount}} deducted as TDS",
                "hi_message"    =>"प्रॉप्स फंतासी {{currency}}{{amount}} टीडीएस के रूप में कटौती की गई",
                "guj_message"   =>"પ્રોપ્સ કાલ્પનિક {{currency}}{{amount}} ટીડીએસ તરીકે કપાત",
                "fr_message"    =>"Accessoires fantasy {{currency}}{{amount}} déduit comme TDS",
                "ben_message"   =>"প্রপস ফ্যান্টাসি {{currency}}{{amount}} টিডিএস হিসাবে কেটে নেওয়া",
                "pun_message"   =>"Fantasy sups spartsy {{currency}}{{amount}} ਜਿਵੇਂ ਟੀਡੀਐਸ ਦੇ ਤੌਰ ਤੇ ਕਟੌਤੀ ਕਰਦਾ ਹੈ",
                "tam_message"   =>"முன்மொழியப்பட்ட பேண்டஸி {{currency}}{{amount}} tds எனக் கழிக்கப்படுகிறது",
                "th_message"    =>"อุปกรณ์ประกอบฉากแฟนตาซี {{currency}}{{amount}} หักออกเป็น tds",
                "kn_message"    =>"ಪ್ರಾಪ್ಸ್ ಫ್ಯಾಂಟಸಿ {{currency}}{{amount}} ಟಿಡಿಎಸ್ ಎಂದು ಕಡಿತಗೊಳಿಸಲಾಗಿದೆ",
                "ru_message"    =>"Реквизит Fantasy {{currency}}{{amount}} вычитается как tds",
                "id_message"    =>"Picks fantasi {{currency}}{{amount}} dikurangkan sebagai tds",
                "tl_message"    =>"Picks fantasy {{currency}}{{amount}} ibabawas bilang tds",
                "zh_message"    =>"Picks fantasy {{currency}}{{amount}}被扣除为TDS"
            )
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 645)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 645,
                            'template_name'=> 'game_join',
                            'template_path'=> 'game_join',
                            'status' => '1',
                            'display_label' => 'Picks Fantasy Game Join',
                            'subject' => 'Your Contest Joining is Confirmed!'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 646)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 646,
                            'template_name'=> 'game_cancel',
                            'template_path'=> 'game_cancel',
                            'status' => '1',
                            'display_label' => 'Picks Fantasy Game Cancel',
                            'subject' => 'Contest Canceled - Insufficient Participation'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 647)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 647,
                            'template_name'=> 'game_won',
                            'template_path'=> 'game_won',
                            'status' => '1',
                            'display_label' => 'Picks Fantasy Game Won',
                            'subject' => 'Congratulations - Game Won'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        /*add leaderboard category*/
        $row = $this->db->select('category_id')
            ->from(LEADERBOARD_CATEGORY)
            ->where('category_id', PICKS_FANTASY_LEADERBOARD_ID) 
            ->get()
            ->row_array();

        if(empty($row)) {
            $category = array(
                'category_id' => PICKS_FANTASY_LEADERBOARD_ID,
                'name' => 'Picks Fantasy',
                'status'=> 0,
                'display_order' => 8
            );    
            $this->db->insert(LEADERBOARD_CATEGORY,$category);
        }

    }
    public function down()
    {
        //down script
    }
}
