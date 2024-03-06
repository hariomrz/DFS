<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_leaderboard extends CI_Migration {

    public function up() {
        $row = $this->db->select('category_id')
        ->from(LEADERBOARD_CATEGORY)
        ->where('category_id', STOCK_LEADERBOARD_ID)
        ->get()
        ->row_array();

        if(empty($row)) {
            $category = array(
                'category_id' => STOCK_LEADERBOARD_ID,
                'name' => 'Stock Points',
                'status'=> 0,
                'display_order' => 3
            );    
            $this->db->insert(LEADERBOARD_CATEGORY,$category);
        }

        $row = $this->db->select('category_id')
        ->from(LEADERBOARD_CATEGORY)
        ->where('category_id', STOCK_EQUITY_LEADERBOARD_ID)
        ->get()
        ->row_array();

        if(empty($row)) {
            $category = array(
                'category_id' => STOCK_EQUITY_LEADERBOARD_ID,
                'name' => 'Stock Equity Points',
                'status'=> 0,
                'display_order' => 4
            );    
            $this->db->insert(LEADERBOARD_CATEGORY,$category);
        }

        $row = $this->db->select('source')
        ->from(MASTER_SOURCE)
        ->where_in('source', array(464,465))
        ->get()
        ->row_array();
        if(empty($row)) {
            $data = array(
                array(
                'source' => 464,
                'name'=> 'Stock Leaderboard Winner'
                ),
                array(
                'source' => 465,
                'name'=> 'Stock Equity Leaderboard Winner'
                )
            );
            $this->db->insert_batch(MASTER_SOURCE,$data);
        }

        $row = $this->db->select('source')
        ->from(TRANSACTION_MESSAGES)
        ->where_in('source', array(464,465))
        ->get()
        ->row_array();
        if(empty($row)) {
            $leaderboard_transaction_messages = array(
                array(
                    'source' => 464,
                    'en_message'      => 'Won {{entity_no}}{{type}} stock leaderboard',
                    'hi_message'      => '{{entity_no}}{{type}} स्टॉक लीडरबोर्ड जीता',
                    'guj_message'     => '{{entity_no}} {{type}} સ્ટોક લીડરબોર્ડ જીત્યું',
                    'fr_message'      => 'A remporté le classement des actions de {{entity_no}}{{type}}',
                    'ben_message'     => 'জিতেছে {{entity_no}} {{type}} স্টক লিডারবোর্ড',
                    'pun_message'     => '{{entity_no}} {{type}} ਸਟਾਕ ਲੀਡਰਬੋਰਡ ਜਿੱਤਿਆ',
                    'tam_message'     => 'ஸ்டாக் லீடர்போர்டை {{entity_no}} {{type}} வென்றது',
                    'th_message'      => 'ชนะ {{entity_no}}{{type}} กระดานผู้นำหุ้น',
                    'kn_message'      => '{{entity_no}} {{type}} ಸ್ಟಾಕ್ ಲೀಡರ್‌ಬೋರ್ಡ್ ಗೆದ್ದಿದೆ',
                    'ru_message'      => 'Выиграл {{entity_no}} {{type}} в таблице лидеров акций',
                    'id_message'      => 'Memenangkan {{entity_no}}{{type}} papan peringkat saham',
                    'tl_message'      => 'Nanalo ng {{entity_no}} {{type}} na leaderboard ng stock',
                    'zh_message'      => '赢得了 {{entity_no}}{{type}} 股票排行榜'
                ),
                array(
                    'source' => 465,
                    'en_message'      => 'Won {{entity_no}}{{type}} stock equity leaderboard',
                    'hi_message'      => '{{entity_no}}{{type}} स्टॉक इक्विटी लीडरबोर्ड जीता',
                    'guj_message'     => '{{entity_no}} {{type}} સ્ટોક ઇક્વિટી લીડરબોર્ડ જીત્યું',
                    'fr_message'      => 'A remporté le classement des actions de {{entity_no}}{{type}}',
                    'ben_message'     => 'জিতেছে {{entity_no}} {{type}} স্টক ইক্যুইটি লিডারবোর্ড',
                    'pun_message'     => '{{entity_no}} {{type}} ਸਟਾਕ ਇਕੁਇਟੀ ਲੀਡਰਬੋਰਡ ਜਿੱਤਿਆ',
                    'tam_message'     => '{{entity_no}} {{type}} ஸ்டாக் ஈக்விட்டி லீடர்போர்டை வென்றது',
                    'th_message'      => 'ชนะ {{entity_no}}{{type}} กระดานผู้นำหุ้น',
                    'kn_message'      => '{{entity_no}} {{type}} ಸ್ಟಾಕ್ ಇಕ್ವಿಟಿ ಲೀಡರ್‌ಬೋರ್ಡ್ ಗೆದ್ದಿದೆ',
                    'ru_message'      => 'Выиграл {{entity_no}} {{type}} рейтинг акционерного капитала',
                    'id_message'      => 'Memenangkan {{entity_no}}{{type}} papan peringkat ekuitas saham',
                    'tl_message'      => 'Nanalo ng {{entity_no}} {{type}} na leaderboard ng equity ng stock',
                    'zh_message'      => '荣获 {{entity_no}}{{type}} 股票排行榜'
                )
            );
        
            $this->db->insert_batch(TRANSACTION_MESSAGES, $leaderboard_transaction_messages);
        }
        
        $row = $this->db->select('notification_type')
        ->from(NOTIFICATION_DESCRIPTION)
        ->where_in('notification_type', array(569,570))
        ->get()
        ->row_array();
        if(empty($row)) {
            $leaderboard_notification_messages = array(
                array(
                    "notification_type" =>569,
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
                    "message"         => 'Congratulations! you won {{amount}} in {{entity_name}} stock leaderboard',
                    'en_message'      => 'Congratulations! you won {{amount}} in {{entity_name}} stock leaderboard',
                    'hi_message'      => 'बधाई हो! आपने जीता {{amount}} में {{entity_name}} स्टॉक लीडरबोर्ड',
                    'guj_message'     => 'અભિનંદન! તમે જીત્યા {{amount}} માં {{entity_name}} સ્ટોક લીડરબોર્ડ',
                    'fr_message'      => 'Toutes nos félicitations! vous avez gagné {{amount}} dans le classement des actions de {{entity_name}}',
                    'ben_message'     => 'অভিনন্দন! আপনি জয়ী {{amount}} এ {{entity_name}} স্টক লিডারবোর্ডে',
                    'pun_message'     => 'ਵਧਾਈਆਂ! ਤੁਸੀਂ {{entity_name}} ਸਟਾਕ ਲੀਡਰਬੋਰਡ ਵਿੱਚ {{amount}} ਜਿੱਤੇ ਹਨ',
                    'tam_message'     => 'வாழ்த்துக்கள்! நீங்கள் {{entity_name}} ஸ்டாக் லீடர்போர்டில் {{amount}} வென்றீர்கள்',
                    'th_message'      => 'ยินดีด้วย! คุณชนะ {{amount}} ในกระดานผู้นำหุ้น {{entity_name}}',
                    'kn_message'      => 'ಅಭಿನಂದನೆಗಳು! ನೀವು {{entity_name}} ಸ್ಟಾಕ್ ಲೀಡರ್‌ಬೋರ್ಡ್‌ನಲ್ಲಿ {{amount}} ಗೆದ್ದಿದ್ದೀರಿ',
                    'ru_message'      => 'Поздравляю! вы выиграли {{amount}} в таблице лидеров акций {{entity_name}}',
                    'id_message'      => 'Selamat! Anda memenangkan {{amount}} di {{entity_name}} papan peringkat saham',
                    'tl_message'      => 'Binabati kita! nanalo ka ng {{amount}} sa {{entity_name}} stock leaderboard',
                    'zh_message'      => '恭喜！ 您在 {{entity_name}} 股票排行榜中贏了 {{amount}}'
                ),
                array(
                    "notification_type" =>570,
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
                    "message"         => 'Congratulations! you won {{amount}} in {{entity_name}} stock equity leaderboard',
                    'en_message'      => 'Congratulations! you won {{amount}} in {{entity_name}} stock equity leaderboard',
                    'hi_message'      => 'बधाई हो! आपने जीता {{amount}} में {{entity_name}} स्टॉक इक्विटी लीडरबोर्ड',
                    'guj_message'     => 'અભિનંદન! તમે જીત્યા {{amount}} માં {{entity_name}} સ્ટોક ઇક્વિટી લીડરબોર્ડ',
                    'fr_message'      => 'Toutes nos félicitations! vous avez gagné {{amount}} dans le classement des actions de {{entity_name}}',
                    'ben_message'     => 'অভিনন্দন! আপনি {{entity_name}} স্টক ইক্যুইটি লিডারবোর্ডে {{amount}} জিতেছেন',
                    'pun_message'     => 'ਵਧਾਈਆਂ! ਤੁਸੀਂ {{entity_name}} ਸਟਾਕ ਇਕੁਇਟੀ ਲੀਡਰਬੋਰਡ ਵਿੱਚ {{amount}} ਜਿੱਤੇ',
                    'tam_message'     => 'வாழ்த்துக்கள்! நீங்கள் {{entity_name}} பங்கு ஈக்விட்டி லீடர்போர்டில் {{amount}} வென்றீர்கள்',
                    'th_message'      => 'ยินดีด้วย! คุณชนะ {{amount}} ใน {{entity_name}} กระดานผู้นำหุ้น',
                    'kn_message'      => 'ಅಭಿನಂದನೆಗಳು! ನೀವು {{entity_name}} ಸ್ಟಾಕ್ ಇಕ್ವಿಟಿ ಲೀಡರ್‌ಬೋರ್ಡ್‌ನಲ್ಲಿ {{amount}} ಗೆದ್ದಿದ್ದೀರಿ',
                    'ru_message'      => 'Поздравляю! вы выиграли {{amount}} в таблице лидеров по акциям {{entity_name}}',
                    'id_message'      => 'Selamat! Anda memenangkan {{amount}} di {{entity_name}} papan peringkat ekuitas saham',
                    'tl_message'      => 'Binabati kita! nanalo ka ng {{amount}} sa {{entity_name}} stock equity leaderboard',
                    'zh_message'      => '恭喜！ 您在 {{entity_name}} 股票排行榜中贏了 {{amount}}'
                ),
            );
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$leaderboard_notification_messages);
        }
    }   

    function down()
    {
        //down script  
        // $this->db->where_in('notification_type', array(569,570));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);

        // $this->db->where_in('source', array(464, 465));
        // $this->db->delete(TRANSACTION_MESSAGES);

        // $this->db->where_in('source', array(464, 465));
        // $this->db->delete(MASTER_SOURCE);

        // $this->db->where_in('category_id', array(STOCK_LEADERBOARD_ID, STOCK_EQUITY_LEADERBOARD_ID));
        // $this->db->delete(LEADERBOARD_CATEGORY);

        
    }
}