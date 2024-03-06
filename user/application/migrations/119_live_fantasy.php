<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Live_fantasy extends CI_Migration {

    public function up() {

      $hub_setting = array(
            'game_key' => 'live_fantasy',
            'en_title' => "Live Fantasy",              
            'hi_title'=> "लिव फंतासी",
            'guj_title' => 'લાઈવ ફૅન્ટેસી',
            'fr_title' => 'Fantaisie vivante',
            'ben_title' => 'লাইভ ফ্যান্টাসি',
            'pun_title' => 'ਲਾਈਵ ਕਲਪਨਾ',
            'tam_title' => 'வாழ்க்கை பேண்டஸி',              
            'th_title' => 'จินตนาการสด',
            'kn_title' => 'ಲೈವ್ ಫ್ಯಾಂಟಸಿ',
            'ru_title' => 'Живая фантазия',
            'id_title' => 'Fantasi hidup',
            'tl_title' => 'Live fantasy.',
            'zh_title' => '生活幻想',
            'en_desc' => "Predict the ball's outcome in a set time and win big.",
            'hi_desc' => "एक निर्धारित समय में गेंद के परिणाम की भविष्यवाणी करें और बड़ा जीतें।",
            'guj_desc' => "સેટ સમયમાં બોલના પરિણામની આગાહી કરો અને મોટા જીતી લો.",
            'fr_desc' => 'Prédisez le résultat de la balle dans un moment donné et gagnez gros.',
            'ben_desc' => 'একটি সেট সময় বল এর ফলাফল পূর্বাভাস এবং বড় জয়।',
            'pun_desc' => 'ਇੱਕ ਨਿਰਧਾਰਤ ਸਮੇਂ ਵਿੱਚ ਗੇਂਦ ਦੇ ਨਤੀਜੇ ਦੀ ਭਵਿੱਖਬਾਣੀ ਕਰੋ ਅਤੇ ਵੱਡੇ ਜਿੱਤੇ.',
            'tam_desc' => 'ஒரு செட் டைமில் பந்து விளைவுகளை முன்னறிவித்தல் மற்றும் பெரிய வெற்றி.',              
            'th_desc' => 'ทำนายผลลัพธ์ของลูกในเวลาที่กำหนดและชนะรางวัลใหญ่',
            'kn_desc' => 'ಒಂದು ಸೆಟ್ ಸಮಯದಲ್ಲಿ ಚೆಂಡಿನ ಫಲಿತಾಂಶವನ್ನು ಊಹಿಸಿ ಮತ್ತು ದೊಡ್ಡ ಗೆದ್ದಿರಿ.',
            'ru_desc' => 'Предсказать результат мяча в установленном времени и выиграть большую.',
            'id_desc' => 'Memprediksi hasil bola dalam waktu yang ditentukan dan menang besar.',
            'tl_desc' => 'Hulaan ang kinalabasan ng bola sa isang takdang oras at manalo ng malaki.',
            'zh_desc' => '预测球在一套时间的结果，并赢得大。',
            'display_order' => 5,
            'allowed_sports' => json_encode(array(CRICKET_SPORTS_ID)),
            'status' => 0
      );
      $result = $this->db->select('*')->from(SPORTS_HUB)->where('game_key',"live_fantasy")->get()->num_rows();
      if(!$result){
        $this->db->insert(SPORTS_HUB,$hub_setting);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',500)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '500',
                'name' => 'Live Fantasy Game Join'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',501)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '501',
                'name' => 'Live Fantasy Game Cancel'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',502)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '502',
                'name' => 'Live Fantasy Game Winning'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

      $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',503)->get()->row_array();
      if(empty($result)){
        $data_arr = array(
                'source' => '503',
                'name' => 'Live Fantasy Private contest commission'
              );
        $this->db->insert(MASTER_SOURCE,$data_arr);
      }

    $result = $this->db->select('*')->from(MASTER_SOURCE)->where('source',504)->get()->row_array();
    if(empty($result)){
        $data_arr = array(
                'source' => '504',
                'name' => 'Live Fantasy TDS Deduction'
            );
        $this->db->insert(MASTER_SOURCE,$data_arr);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source',"500")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 500,
                'en_message'      => 'Live Fantasy {{match}} Over {{over}} Contest {{contest}} Joined',
                'hi_message'      => 'लाइव फैंटेसी {{match}} Over {{over}} प्रतियोगिता {{contest}} शामिल हुए',
                'guj_message'     => 'લાઈવ ફેન્ટસી {{match}} Over {{over}} સ્પર્ધા {{contest}} જોડાઈ',
                'fr_message'      => 'Concours Live Fantasy {{match}} Over {{over}} {{contest}} rejoint',
                'ben_message'     => 'লাইভ ফ্যান্টাসি {{match}} Over {{over}} প্রতিযোগিতায় {{contest}} যোগ দিয়েছেন',
                'pun_message'     => 'ਲਾਈਵ ਫੈਂਟੇਸੀ {{match}} Over {{over}} ਮੁਕਾਬਲੇ {{contest}} ਸ਼ਾਮਲ ਹੋਏ',
                'tam_message'     => 'லைவ் ஃபேண்டஸி {{match}} Over {{over}} போட்டி {{contest}} சேர்ந்தது',
                'th_message'      => 'แฟนตาซีสด {{match}} Over {{over}} การแข่งขัน {{contest}} เข้าร่วม',
                'kn_message'      => 'ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{match}} Over {{over}} ಸ್ಪರ್ಧೆ {{contest}} ಸೇರಿದ್ದಾರೆ',
                'ru_message'      => 'Live Fantasy {{match}} Over {{over}} Contest {{contest}} Присоединился',
                'id_message'      => 'Live Fantasy {{match}} Over {{over}} Contest {{contest}} Partecipato',
                'tl_message'      => 'Live Fantasy {{match}} Over {{over}} Contest {{contest}} Sumali',
                'zh_message'      => 'Live Fantasy {{match}} Over {{over}} 比赛 {{contest}} 已加入'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "501")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 501,
                'en_message'      => 'Contest cancellation fee refund. Live Fantasy {{match}} Over {{over}} {{contest}}',
                'hi_message'      => 'प्रतियोगिता रद्दीकरण शुल्क वापसी। लाइव फ़ैंटेसी {{match}} Over {{over}} {{contest}}',
                'guj_message'     => 'હરીફાઈ કેન્સલેશન ફી રિફંડ. લાઈવ ફેન્ટસી {{match}} Over {{over}} {{contest}}',
                'fr_message'      => 'Remboursement des frais dannulation du concours. Live Fantasy {{match}} Over {{over}} {{contest}}',
                'ben_message'     => 'প্রতিযোগিতা বাতিলের ফি ফেরত। লাইভ ফ্যান্টাসি {{match}} Over {{over}} {{contest}}',
                'pun_message'     => 'ਮੁਕਾਬਲੇ ਰੱਦ ਕਰਨ ਦੀ ਫੀਸ ਦੀ ਵਾਪਸੀ। ਲਾਈਵ ਕਲਪਨਾ {{match}} Over {{over}} {{contest}}',
                'tam_message'     => 'போட்டி ரத்து கட்டணம் திரும்பப்பெறுதல். லைவ் பேண்டஸி {{match}} Over {{over}} {{contest}}',
                'th_message'      => 'คืนเงินค่าธรรมเนียมการยกเลิกการแข่งขัน แฟนตาซีสด {{match}} Over {{over}} {{contest}}',
                'kn_message'      => 'ಸ್ಪರ್ಧೆಯ ರದ್ದತಿ ಶುಲ್ಕ ಮರುಪಾವತಿ. ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{match}} Over {{over}} {{contest}}',
                'ru_message'      => 'Возврат платы за отмену конкурса. Живая фантазия {{match}} Over {{over}} {{contest}}',
                'id_message'      => 'Pengembalian biaya pembatalan kontes. Fantasi Langsung {{match}} Over {{over}} {{contest}}',
                'tl_message'      => 'Pagbabalik ng bayad sa pagkansela ng paligsahan. Live Fantasy {{match}} Over {{over}} {{contest}}',
                'zh_message'      => '比赛取消费退款。 Live Fantasy {{match}} Over {{over}} {{contest}}'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "502")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 502,
                'en_message'      => 'Live Fantasy {{match}} Over {{over}} contest winning credit',
                'hi_message'      => 'लाइव फंतासी {{match}} Over {{over}} प्रतियोगिता जीतना',
                'guj_message'     => 'લાઇવ ફૅન્ટેસી {{match}} Over {{over}} હરીફાઈ વિજેતા ક્રેડિટ',
                'fr_message'      => 'Live fantasy {{match}} Over {{over}} concours gagnant crédit',
                'ben_message'     => 'লাইভ ফ্যান্টাসি {{match}} Over {{over}} প্রতিযোগিতার বিজয়ী ক্রেডিট',
                'pun_message'     => 'ਲਾਈਵ ਫੈਂਟੇਸੀ {{match}} Over {{over}} ਮੁਕਾਬਲੇ ਜਿੱਤਣ ਦਾ ਕ੍ਰੈਡਿਟ',
                'tam_message'     => 'லைவ் பேண்டஸி {{match}} Over {{over}} போட்டியில் வென்ற கிரெடிட்',
                'th_message'      => 'Live Fantasy {{match}} Over {{over}} รายการ',
                'kn_message'      => 'ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{match}} Over {{over}} ಸ್ಪರ್ಧೆ ವಿಜೇತ ಕ್ರೆಡಿಟ್',
                'ru_message'      => 'Live Fantasy {{match}} Over {{over}} победного балла в конкурсе',
                'id_message'      => 'Kredit kemenangan kontes {{match}} Fantasi Langsung Lebih Over {{over}}',
                'tl_message'      => 'Live Fantasy {{match}} Over {{over}} na panalong credit sa paligsahan',
                'zh_message'      => 'Live Fantasy {{match}} Over {{over}} 次比赛获奖学分'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "503")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 503,
                'en_message'      => 'Live Fantasy private contest commission',
                'hi_message'      => 'लाइव फैंटेसी प्राइवेट कॉन्टेस्ट कमीशन',
                'guj_message'     => 'જીવંત કાલ્પનિક ખાનગી હરીફાઈ પંચ',
                'fr_message'      => 'Commission de concours privé en direct fantaisie',
                'ben_message'     => 'লাইভ ফ্যান্টাসি বেসরকারী প্রতিযোগিতা কমিশন',
                'pun_message'     => 'ਲਾਈਵ ਕਲਪਨਾ ਪ੍ਰਾਈਵੇਟ ਮੁਕਾਬਲਾ ਕਮਿਸ਼ਨ',
                'tam_message'     => 'பேண்டஸி தனியார் போட்டி ஆணையம்',
                'th_message'      => 'Live Fantasy Private Contest Commission',
                'kn_message'      => 'ಲೈವ್ ಫ್ಯಾಂಟಸಿ ಖಾಸಗಿ ಸ್ಪರ್ಧೆ ಆಯೋಗ',
                'ru_message'      => 'Комиссия по частному конкурсу в прямом эфире',
                'id_message'      => 'Komisi Kontes Pribadi Fantasi Langsung',
                'tl_message'      => 'Komisyon sa Live Fantasy Pribadong Paligsahan',
                'zh_message'      => '现场幻想私人竞赛委员会'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $row = $this->db->select('source')
            ->from(TRANSACTION_MESSAGES)
            ->where('source', "504")
            ->get()
            ->row_array();
    if(empty($row)) {
        $transaction_messages = array(
            array(
                'source' => 504,
                'en_message'      => 'Live Fantasy TDS Deduction',
                'hi_message'      => 'लाइव फंतासी टीडी कटौती',
                'guj_message'     => 'જીવંત કાલ્પનિક ટીડીએસ કપાત',
                'fr_message'      => 'Déduction de TDS fantastique en direct',
                'ben_message'     => 'লাইভ ফ্যান্টাসি টিডিএস ছাড়',
                'pun_message'     => 'ਲਾਈਵ ਕਲਪਨਾ ਟੀਡੀਐਸ ਕਟੌਤੀ',
                'tam_message'     => 'நேரலை கற்பனை டி.டி.எஸ் விலக்கு',
                'th_message'      => 'การหัก TDS แฟนตาซีสด',
                'kn_message'      => 'ಲೈವ್ ಫ್ಯಾಂಟಸಿ ಟಿಡಿಎಸ್ ಕಡಿತ',
                'ru_message'      => 'Живая фэнтезийная вычеты TDS',
                'id_message'      => 'Deduksi TDS Fantasi Langsung',
                'tl_message'      => 'Live Fantasy TDS Deduction',
                'zh_message'      => '现场幻想TD扣除'
            )
        );
        $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);
    }

    $notification_description = array(
            array(
                "notification_type" =>619,
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
                "message"       =>"Live Fantasy {{currency}}{{amount}} deducted as TDS",
                "en_message"     =>"Live Fantasy {{currency}}{{amount}} deducted as TDS",
                "hi_message"    =>"लाइव फंतासी {{currency}}{{amount}} टीडीएस के रूप में कटौती की गई",
                "guj_message"   =>"લાઇવ ફ ant ન્ટેસી {{currency}}{{amount}} ટીડીએસ તરીકે કપાત",
                "fr_message"    =>"Fantasy en direct {{currency}}{{amount}} déduit comme TDS",
                "ben_message"   =>"লাইভ ফ্যান্টাসি {{currency}}{{amount}} টিডিএস হিসাবে কেটে নেওয়া",
                "pun_message"   =>"ਲਾਈਵ ਕਲਪਨਾ {{currency}}{{amount}} ਜਿਵੇਂ ਕਿ ਟੀਡੀਐਸ ਦੇ ਤੌਰ ਤੇ ਕਟੌਤੀ ਕਰਦਾ ਹੈ",
                "tam_message"   =>"நேரடி பேண்டஸி {{currency}}{{amount}} tds எனக் கழிக்கப்படுகிறது",
                "th_message"    =>"Live Fantasy {{currency}}{{amount}} หักออกเป็น tds",
                "kn_message"    =>"ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{currency}}{{amount}} TDS ಎಂದು ಕಡಿತಗೊಳಿಸಲಾಗಿದೆ",
                "ru_message"    =>"Live Fantasy {{currency}}{{amount}} вычитается как tds",
                "id_message"    =>"Live Fantasy {{currency}}{{amount}} dikurangkan sebagai TDS",
                "tl_message"    =>"Live na pantasya {{currency}}{{amount}} ibabawas bilang tds",
                "zh_message"    =>"实时幻想{{currency}}{{amount}}被扣除为TDS"
            ),
            array(
                "notification_type" =>620,
                "en_subject"    =>"Game Join",
                "hi_subject"    =>"खेल में शामिल हों",
                "guj_subject"   =>"રમત જોડાઓ",
                "fr_subject"    =>"Joindre",
                "ben_subject"   =>"খেলা যোগ দিন",
                "pun_subject"   =>"ਖੇਡ ਸ਼ਾਮਲ ਹੋਵੋ",
                "th_subject"    =>"เเกมเข้าร่วม",
                "kn_subject"    =>"ಗೇಮ್ ಸೇರಲು",
                "ru_subject"    =>"Игра Присоединиться",
                "id_subject"    =>"Game Gabung",
                "tl_subject"    =>"Sumali ang laro",
                "zh_subject"    =>"游戏加入",
                "message"       =>"Live Fantasy {{contest_name}} – {{collection_name}} Inn {{inning}} Over {{over}} joined successfully",
                "en_message"     =>"Live Fantasy {{contest_name}} – {{collection_name}} Inn {{inning}} Over {{over}} joined successfully",
                "hi_message"    =>"लाइव फैंटेसी {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} सफलतापूर्वक शामिल हुई",
                "guj_message"   =>"જીવંત કાલ્પનિક {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} સફળતાપૂર્વક જોડાયા",
                "fr_message"    =>"Fantaisie en direct {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} a rejoint avec succès",
                "ben_message"   =>"লাইভ ফ্যান্টাসি {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} সফলভাবে যোগদান",
                "pun_message"   =>"ਲਾਈਵ ਕਲਪਨਾ {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋ ਗਿਆ",
                "tam_message"   =>"லைவ் பேண்டஸி {{contest_name}} - {{collection_name}} Inn {{inning}} Over {{over}} க்கு வெற்றிகரமாக இணைந்தது",
                "th_message"    =>"แฟนตาซีสด {{contest_name}} – {{collection_name}} Inn {{inning}} Over {{over}} สำเร็จแล้ว",
                "kn_message"    =>"ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{contest_name}} ಆಟ – {{collection_name}} Inn {{inning}} Over {{over}} ಯಶಸ್ವಿಯಾಗಿ ಸೇರಿದೆ",
                "ru_message"    =>"Живая фантазия {{contest_name}} — {{collection_name}} Inn {{inning}} Over {{over}} успешно присоединилась",
                "id_message"    =>"Fantasi Hidup {{contest_name}} – {{collection_name}} Inn {{inning}} Over {{over}} berhasil bergabung",
                "tl_message"    =>"Live na Pantasya {{contest_name}} – {{collection_name}} Inn {{inning}} Over {{over}}",
                "zh_message"    =>"現場幻想 {{contest_name}}  -  {{collection_name}} Inn {{inning}} Over {{over}} 成功加入"
            ),
            array(
                "notification_type" =>621,
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
                "message"       =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} contest {{contest_name}} canceled {{cancel_reason_type}}.",
                "en_message"     =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} contest {{contest_name}} canceled {{cancel_reason_type}}.",
                "hi_message"    =>"{{collection_name}} Inn {{inning}} Over {{overs}} प्रतियोगिता {{contest_name}} {{cancel_reason_type}} रद्द कर दी गई है।",
                "guj_message"   =>"લાઇવ ફેન્ટસી {{collection_name}} Inn {{inning}} Over {{overs}} સ્પર્ધા {{contest_name}} અપૂરતી સહભાગિતાને કારણે રદ કરવામાં આવી.",
                "fr_message"    =>"Live fantasy {{collection_name}} Inn {{inning}} Over {{overs}} Concours {{contest_name}} annulé {{cancel_reason_type}}.",
                "ben_message"   =>"লাইভ ফ্যান্টাসি {{collection_name}} Inn {{inning}} Over {{overs}} প্রতিযোগিতা {{contest_name}} বাতিল হয়েছে {{cancel_reason_type}}",
                "pun_message"   =>"ਲਾਈਵ ਫੈਂਟੇਸੀ {{collection_name}} Inn {{inning}} Over {{overs}} ਮੁਕਾਬਲਾ {{contest_name}} ਰੱਦ {{cancel_reason_type}}।",
                "tam_message"   =>"லைவ் ஃபேண்டஸி {{collection_name}} Inn {{inning}} Over {{overs}} போட்டி {{contest_name}} ரத்து செய்யப்பட்டது {{cancel_reason_type}}.",
                "th_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} รายการ {{contest_name}} แล้ว {{cancel_reason_type}}",
                "kn_message"    =>"ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{collection_name}} Inn {{inning}} Over {{overs}} ಹೆಚ್ಚು ಸ್ಪರ್ಧೆ {{contest_name}} ರದ್ದುಗೊಳಿಸಲಾಗಿದೆ {{cancel_reason_type}}.",
                "ru_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} конкурс {{contest_name}} отменен {{cancel_reason_type}}.",
                "id_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} kontes {{contest_name}} dibatalkan {{cancel_reason_type}}.",
                "tl_message"    =>"Ang Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} contest {{contest_name}} kinansela {{cancel_reason_type}}.",
                "zh_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{overs}} 比赛 {{contest_name}} 取消 {{cancel_reason_type}}"
            ),
            array(
                "notification_type" =>622,
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
                "message"       =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{over}} contest winning credit",
                "en_message"     =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{over}} contest winning credit",
                "hi_message"    =>"लाइव फंतासी {{collection_name}} Inn {{inning}} Over {{over}} प्रतियोगिता जीतना",
                "guj_message"   =>"લાઈવ ફૅન્ટેસી {{collection_name}} Inn {{inning}} Over {{over}} હરીફાઈ વિજેતા ક્રેડિટ",
                "fr_message"    =>"Live fantasy {{collection_name}} Inn {{inning}} Over {{over}} Crédit gagnant",
                "ben_message"   =>"লাইভ ফ্যান্টাসি {{collection_name}} Inn {{inning}} Over {{over}} প্রতিযোগিতার বিজয়ী ক্রেডিট",
                "pun_message"   =>"ਲਾਈਵ ਫੈਂਟੇਸੀ {{collection_name}} Inn {{inning}} Over {{over}} ਮੁਕਾਬਲਾ ਜਿੱਤਣ ਦਾ ਕ੍ਰੈਡਿਟ",
                "tam_message"   =>"லைவ் பேண்டஸி {{collection_name}} Inn {{inning}} Over {{over}} போட்டியில் வென்ற கிரெடிட்",
                "th_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{over}} เครดิตที่ชนะการแข่งขัน",
                "kn_message"    =>"ಲೈವ್ ಫ್ಯಾಂಟಸಿ {{collection_name}} Inn {{inning}} Over {{over}} ಸ್ಪರ್ಧೆಯ ವಿಜೇತ ಕ್ರೆಡಿಟ್",
                "ru_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{over}} победителя конкурса",
                "id_message"    =>"Live fantasy {{collection_name}} Inn {{inning}} Over {{over}} Kontes memenangkan kredit",
                "tl_message"    =>"Live fantasy {{collection_name}} Inn {{inning}} Over {{over}} contest winning credit",
                "zh_message"    =>"Live Fantasy {{collection_name}} Inn {{inning}} Over {{over}} 比赛获奖信用"
            )
        );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION, $notification_description);

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 620)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 620,
                            'template_name'=> 'game_join',
                            'template_path'=> 'game_join',
                            'status' => '1',
                            'display_label' => 'LF Game Join',
                            'subject' => 'Your Contest Joining is Confirmed!'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 621)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 621,
                            'template_name'=> 'game_cancel',
                            'template_path'=> 'game_cancel',
                            'status' => '1',
                            'display_label' => 'LF Game Cancel',
                            'subject' => 'Contest Canceled - Insufficient Participation'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

        $row = $this->db->select('notification_type')
            ->from(EMAIL_TEMPLATE)
            ->where('notification_type', 622)
            ->get()
            ->row_array();
        if(empty($row)) {
            $email_template = array(
                            'notification_type' => 622,
                            'template_name'=> 'game_won',
                            'template_path'=> 'game_won',
                            'status' => '1',
                            'display_label' => 'LF Game Cancel',
                            'subject' => 'Congratulations - Game Won'
                          );
            $this->db->insert(EMAIL_TEMPLATE,$email_template);
        }

    }
    public function down()
    {
      //$this->db->delete(SPORTS_HUB,array("game_key" => "live_fantasy"));
    }
}