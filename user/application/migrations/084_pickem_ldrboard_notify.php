<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pickem_Ldrboard_notify extends CI_Migration {

    public function up() {
        $notifications = array(
            array(
            "notification_type" =>501,
            "en_subject"=>"Congratulations!",
            "hi_subject"=>"बधाई हो",
            //"tam_subject"=>"",
            "ben_subject"=>"অভিনন্দন",
            "pun_subject"=>"ਵਧਾਈ",
            "fr_subject"=>"Toutes nos félicitations",
            "guj_subject"=>"અભિનંદન",
            "th_subject"=>"ขอแสดงความยินดี",
            "message" => 'You have received {{prize}} for besting in Daily leaderboard.',
            "en_message"=>'You have received {{prize}} for besting in Daily leaderboard.', 
            "hi_message"=>'आप दैनिक लीडरबोर्ड में besting के लिए {{prize}} प्राप्त हुआ है।',
            "tam_message"=> 'நீங்கள் டெய்லி முன்னிலை உள்ள besting பெற்றுள்ளோம் {{prize}}.',
            "ben_message"=>'আপনি দৈনিক লিডারবোর্ডে মধ্যে besting জন্য {{prize}} পেয়েছি।',
            "pun_message"=>'ਤੁਹਾਨੂੰ ਰੋਜ਼ਾਨਾ ਲੀਡਰਬੋਰਡ ਵਿਚ besting ਲਈ {{prize}} ਪ੍ਰਾਪਤ ਕੀਤਾ ਹੈ.',
            "fr_message"=>'Vous avez reçu {{prize}} pour besting dans leaderboard Daily.',
            "guj_message"=>'તમે દૈનિક લીડરબોર્ડ માં હરાવવા માટે {{prize}} પ્રાપ્ત થઈ છે.',
            "th_message"=>'คุณได้รับรางวัล {{prize}} สำหรับเอาชนะในลีดเดอร์ประจำวัน',
            "ru_subject" => "Поздравляю",
            "id_subject" => "Selamat",
            "tl_subject" => "pagbati",
            "zh_subject" => "祝贺",
            "kn_subject" => "ಅಭಿನಂದನೆಗಳು",
            "ru_message" => "Вы получили {{prize}} приз за обойдя в повседневных лидерах.",
            "id_message" => "Anda telah menerima {{prize}} untuk besting di leaderboard harian.",
            "tl_message" => "Nakatanggap ka ng {{prize}} para besting sa Araw-araw na leaderboard.",
            "zh_message" => "您收到{{prize}}在每日排行榜击败。",
            "kn_message" => "ನೀವು ಡೈಲಿ ಲೀಡರ್ besting ಸ್ವೀಕರಿಸಿದ್ದೇವೆ {{prize}}."
            //"es_message" => "{{name}} torneo se cancela por Admin"
            ),
            array(
            "notification_type" =>502,
            "en_subject"=>"Congratulations!",
            "hi_subject"=>"बधाई हो",
            //"tam_subject"=>"",
            "ben_subject"=>"অভিনন্দন",
            "pun_subject"=>"ਵਧਾਈ",
            "fr_subject"=>"Toutes nos félicitations",
            "guj_subject"=>"અભિનંદન",
            "th_subject"=>"ขอแสดงความยินดี",
            "message" => ' You have received {{prize}} for besting in Weekly leaderboard',
            "en_message"=>' You have received {{prize}} for besting in Weekly leaderboard', 
            "hi_message"=>' आप साप्ताहिक लीडरबोर्ड में besting के लिए {{prize}} प्राप्त हुआ है',
            "guj_message"=>' તમે સાપ્તાહિક લીડરબોર્ડ માં હરાવવા માટે {{prize}} પ્રાપ્ત થઈ છે',
            "tam_message"=> ' நீங்கள் வாராந்திர முன்னிலை உள்ள besting பெற்றுள்ளோம் {{prize}}',
            "ben_message"=>' আপনি সাপ্তাহিক লিডারবোর্ডে মধ্যে besting জন্য {{prize}} পেয়েছি',
            "pun_message"=>' ਤੁਹਾਨੂੰ ਵੀਕਲੀ ਲੀਡਰਬੋਰਡ ਵਿਚ besting ਲਈ {{prize}} ਪ੍ਰਾਪਤ ਕੀਤਾ ਹੈ',
            "fr_message"=>' Vous avez reçu {{prize}} pour besting dans leaderboard hebdomadaire',
            "th_message"=>' คุณได้รับรางวัล {{prize}} สำหรับเอาชนะในสัปดาห์ลีดเดอร์',
            "ru_subject" => "Поздравляю",
            "id_subject" => "Selamat",
            "tl_subject" => "pagbati",
            "zh_subject" => "祝贺",
            "kn_subject" => "ಅಭಿನಂದನೆಗಳು",
            "ru_message" => " Вы получили {{prize}} премии для обойдя в еженедельных лидерах",
            "id_message" => " Anda telah menerima {{prize}} untuk besting di Weekly leaderboard",
            "tl_message" => " Nakatanggap ka ng {{prize}} para besting sa Lingguhang leaderboard",
            "zh_message" => " 您收到{{prize}}在每周排行榜击败",
            "kn_message" => " ನೀವು {{prize}} ಸ್ವೀಕರಿಸಿದ್ದೇವೆ ವೀಕ್ಲಿ ಲೀಡರ್ besting ಫಾರ್"
            //"es_message" => "{{name}} juego del torneo {{match}} se cancela por Admin"
            ),
            array(
                "notification_type" =>503,
                "en_subject"=>"Congratulations!",
                "hi_subject"=>"बधाई हो",
                //"tam_subject"=>"",
                "ben_subject"=>"অভিনন্দন",
                "pun_subject"=>"ਵਧਾਈ",
                "fr_subject"=>"Toutes nos félicitations",
                "guj_subject"=>"અભિનંદન",
                "th_subject"=>"ขอแสดงความยินดี",
                "message" => 'You have received {{prize}} for besting in Monthly leaderboard',
                "en_message"=>'You have received {{prize}} for besting in Monthly leaderboard', 
                "hi_message"=>'आप मासिक लीडरबोर्ड में besting के लिए {{prize}} प्राप्त हुआ है',
                "guj_message"=>'તમે માસિક લીડરબોર્ડ માં હરાવવા માટે {{prize}} પ્રાપ્ત થઈ છે',
                "tam_message"=> 'நீங்கள் மாதாந்திர முன்னிலை உள்ள besting க்கான {{prize}} பெற்றுள்ளோம்',
                "ben_message"=>'আপনি মাসিক লিডারবোর্ডে মধ্যে besting জন্য {{prize}} পেয়েছি',
                "pun_message"=>'ਤੁਹਾਡੇ ਮਹੀਨਾਵਾਰ ਲੀਡਰਬੋਰਡ ਵਿਚ besting ਲਈ {{prize}} ਪ੍ਰਾਪਤ ਕੀਤਾ ਹੈ',
                "fr_message"=>'Vous avez reçu {{prize}} pour besting dans leaderboard mensuel',
                "th_message"=>'คุณได้รับรางวัล {{prize}} สำหรับเอาชนะในลีดเดอร์รายเดือน',
                "ru_subject" => "Поздравляю",
                "id_subject" => "Selamat",
                "tl_subject" => "pagbati",
                "zh_subject" => "祝贺",
                "kn_subject" => "ಅಭಿನಂದನೆಗಳು",
                "ru_message" => "Вы получили {{prize}} Приз за обойдя в месяц лидеров",
                "id_message" => "Anda telah menerima {{prize}} untuk besting di Bulanan leaderboard",
                "tl_message" => "Nakatanggap ka ng {{prize}} para besting sa Buwanang leaderboard",
                "zh_message" => "您收到{{prize}}在每月击败排行榜",
                "kn_message" => "ನೀವು {{prize}} ಸ್ವೀಕರಿಸಿದ್ದೇವೆ ಮಾಸಿಕ ಲೀಡರ್ besting ಫಾರ್"
                //"es_message" => "{{name}} juego del torneo {{match}} se cancela por Admin"
                )
            );

            // echo '<pre>';
            // print_r($notifications);die('dfd');
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);   

            $value = array(
                array(
                'source' => '401',
                'name'=> 'Pickem Leaderboard: Won Daily Leaderboard Prize'
            ),
            array(
                'source' => '402',
                'name'=> ' Pickem Leaderboard: Won Weekly Leaderboard Prize'
            ),
            array(
                'source' => '403',
                'name'=> 'Pickem Leaderboard: Won Monthly Leaderboard Prize'
            ),
            array(
                'source' => '404',
                'name'=> 'Pickem Leaderboard: Total TDS Deducted'
            ),

        );
        $this->db->insert_batch(MASTER_SOURCE,$value);


        $transaction_messages = array(
            array(
                'source' => 401,
                'en_message' => 'Won Daily Leaderboard Prize',
                'fr_message' => 'Won Daily Leaderboard Prix',
                'hi_message' => 'वोन दैनिक लीडरबोर्ड पुरस्कार',
                'guj_message' => 'જીત્યું દૈનિક લીડરબોર્ડ પ્રાઇઝ',
                'ben_message' => 'বিজয়ী দৈনিক লিডারবোর্ড পুরস্কার',
                'pun_message' => 'ਜਿੱਤੀਆ ਰੋਜ਼ਾਨਾ ਲੀਡਰਬੋਰਡ ਪੁਰਸਕਾਰ',
               // 'es_message' => 'Precio de la entrada para %s',
                'tam_message' => 'வென்றது டெய்லி லீடர் பரிசு',
                'th_message' => 'ได้รับรางวัลลีดเดอร์ประจำวัน',
                'ru_message' => 'Вон Leaderboard премии Daily',
                'id_message' => 'Won Harian Prize Papan',
                'tl_message' => 'Won Daily Leaderboard Prize',
                'zh_message' => '荣获每日排行榜奖',
                'kn_message' => 'ಗೆದ್ದಿದ್ದು ಡೈಲಿ ಲೀಡರ್ ಪ್ರಶಸ್ತಿ'
            ),
            array(
                'source' => 402,
                'en_message' => 'Won Weekly Leaderboard Prize',
                'fr_message' => 'Won Weekly Leaderboard Prix',
                'hi_message' => 'वोन साप्ताहिक लीडरबोर्ड पुरस्कार',
                'guj_message' => 'જીત્યું અઠવાડિક લીડરબોર્ડ પ્રાઇઝ',
                'ben_message' => 'ওন সপ্তাহের লিডারবোর্ড পুরস্কার',
                'pun_message' => 'ਜਿੱਤੀਆ ਵੀਕਲੀ ਲੀਡਰਬੋਰਡ ਪੁਰਸਕਾਰ',
               // 'es_message' => 'Para Honorario del reembolso del torneo',
                'tam_message' => 'வென்றது வாராந்திர லீடர் பரிசு',
                'th_message' => 'วอนสัปดาห์รางวัลลีดเดอร์' ,
                'ru_message' => 'Вон Leaderboard приз за неделю',
                'id_message' => 'Won Mingguan Papan Prize',
                'tl_message' => 'Won Weekly Leaderboard Prize',
                'zh_message' => '荣获每周排行榜奖',
                'kn_message' => 'ಗೆದ್ದಿದ್ದು ವೀಕ್ಲಿ ಲೀಡರ್ ಪ್ರಶಸ್ತಿ'
            ),
            array(
                'source' => 403,
                'en_message' => 'Won Monthly Leaderboard Prize',
                'fr_message' => 'Won mensuel Leaderboard Prix',
                'hi_message' => 'वोन मासिक लीडरबोर्ड पुरस्कार',
                'guj_message' => 'જીત્યું માસિક લીડરબોર્ડ પ્રાઇઝ',
                'ben_message' => 'ওন মাসিক লিডারবোর্ড পুরস্কার',
                'pun_message' => 'ਜਿੱਤੀਆ ਮਾਸਿਕ ਲੀਡਰਬੋਰਡ ਪੁਰਸਕਾਰ',
               // 'es_message' => 'Premio ganó el concurso',
                'tam_message' => 'வென்றது மாதாந்திர லீடர் பரிசு',
                'th_message' => 'วอนรายเดือนรางวัลลีดเดอร์',
                'ru_message' => 'Вон Leaderboard премии в месяц',
                'id_message' => 'Won Bulanan Papan Prize',
                'tl_message' => 'Won Buwanang Leaderboard Prize',
                'zh_message' => '荣获月度排行榜奖',
                'kn_message' => 'ಗೆದ್ದಿದ್ದು ಮಾಸಿಕ ಲೀಡರ್ ಪ್ರಶಸ್ತಿ'
            ),
            array(
                'source' => 404,
                'en_message' => 'Pickem Leaderboard: Total TDS Deducted',
                'hi_message' => 'Pickem लीडरबोर्ड: कुल टीडीएस कटौती',
                'guj_message' => 'Pickem લીડરબોર્ડ: કુલ TDS ડિડક્ટેડ',
                'fr_message' => 'Pickem Leaderboard: Total TDS Déduit',
                'ben_message' => 'Pickem লিডারবোর্ড: মোট উত্সমূলে কাটা',
                'pun_message' => 'Pickem ਲੀਡਰਬੋਰਡ: ਕੁੱਲ TDS ਦੀ ਕਟੌਤੀ',
                //'es_message' => 'Torneo DFS: Total TDS Deducido',
                'tam_message' => 'Pickem லீடர்: மொத்த அதுமட்டுமல்ல கழிக்கப்படும்',
                'th_message' => 'Pickem ลีดเดอร์บอร์ด: รวม TDS หัก',
                'ru_message' => 'Pickem Leaderboard: Total TDS вычитаются',
                'id_message' => 'Pickem Leaderboard: Jumlah TDS Dikurangi',
                'tl_message' => 'Pickem Leaderboard: Kabuuang TDS ibabawas',
                'zh_message' => 'Pickem排行榜：总扣除的TDS',
                'kn_message' => 'Pickem ಲೀಡರ್: ಒಟ್ಟು ಟಿಡಿಎಸ್ ಕಡಿತಗೊಳಿಸಲಾಗುತ್ತದೆ'
            ),
        );
      $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

      $email_templtes = 
      array(
          array(
              'notification_type' => 501,
              'template_name'=> 'daily-leaderbord-won',
              'subject' => 'Wohoo! You just WON!',
              'template_path' => 'daily-leaderbord-won',
              'status' => 1,
              'type' => 0,
              'display_label' => 'Daily Leaderboard Won',
              'date_added' => '2021-04-29 12:15:41'
          ),
           array(
              'notification_type' => 502,
              'template_name'=> 'weekly-leaderbord-won',
              'subject' => 'Wohoo! You just WON!',
              'template_path' => 'weekly-leaderbord-won',
              'status' => 1,
              'type' => 0,
              'display_label' => 'Weekly Leaderboard Won',
              'date_added' => '2021-04-29 12:15:41'
           ),
           array(
            'notification_type' => 503,
            'template_name'=> 'monthly-leaderbord-won',
            'subject' => 'Wohoo! You just WON!',
            'template_path' => 'monthly-leaderbord-won',
            'status' => 1,
            'type' => 0,
            'display_label' => 'Monthly Leaderboard Won',
            'date_added' => '2021-04-29 12:15:41'
        )
      );
      $this->db->insert_batch(EMAIL_TEMPLATE,$email_templtes);



    }
}