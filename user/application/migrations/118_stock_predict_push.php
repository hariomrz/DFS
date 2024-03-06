<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_predict_push extends CI_Migration {

    public function up() {

        $notifications = array(
            array(
                "notification_type" =>623,
                "en_subject"=>"New Candle Published",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"Create your portfolios and join the new contests to win big.",
                "en_message"        =>"Create your portfolios and join the new contests to win big",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            array(
                "notification_type" =>624,
                "en_subject"=>"Contest `{{contest_name}}` Over",
                "hi_subject"=>"प्रतियोगिता `{{contest_name}}` समाप्त",
                //"tam_subject"=>"",
                "ben_subject"=>"প্রতিযোগিতা `{{contest_name}}` শেষ",
                "pun_subject"=>"ਮੁਕਾਬਲਾ `{{contest_name}}` ਓਵਰ",
                "fr_subject"=>"Concours `{{contest_name}}` terminé",
                "guj_subject"=>"હરીફાઈ `{{contest_name}}` સમાપ્ત",
                "th_subject"=>"",
                "message"           =>"Check where you stand on the leaderboard now!",
                "en_message"        =>"Check where you stand on the leaderboard now!",
                "hi_message"        =>"जांचें कि आप लीडरबोर्ड पर अब कहां खड़े हैं !!",
                "guj_message"       =>"હવે લીડરબોર્ડ પર તમે ક્યાં ઉભા છો તે તપાસો!!",
                "fr_message"        =>"Vérifiez où vous vous situez dans le classement maintenant !!",
                "ben_message"       =>"আপনি এখন লিডারবোর্ডে কোথায় দাঁড়িয়েছেন তা পরীক্ষা করে দেখুন!!",
                "pun_message"       =>"ਜਾਂਚ ਕਰੋ ਕਿ ਤੁਸੀਂ ਹੁਣ ਲੀਡਰਬੋਰਡ \'ਤੇ ਕਿੱਥੇ ਖੜ੍ਹੇ ਹੋ !!",
                "tam_message"       =>"லீடர்போர்டில் நீங்கள் எங்கு நிற்கிறீர்கள் என்பதை இப்போது சரிபார்க்கவும்!!",
                "th_message"        =>"ตรวจสอบตำแหน่งที่คุณยืนอยู่บนกระดานผู้นำตอนนี้!!",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
                "notification_type" =>625,
                "en_subject"=>"It's getting live ",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"           =>"The contest {{contest_name}} is about to start. Check your portfolio and get it right.",
                "en_message"        =>"The contest {{contest_name}} is about to start. Check your portfolio and get it right.",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            array(
                "notification_type" =>626,
                "en_subject"=>"Accuracy",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message"    =>"You are {{average_accuracy}} % accurate till date. Keep playing and",
                "en_message" =>"You are {{average accuracy}} % accurate till date. Keep playing and",
                "hi_message"        =>"",
                "guj_message"       =>"",
                "fr_message"        =>"",
                "ben_message"       =>"",
                "pun_message"       =>"",
                "tam_message"       =>"",
                "th_message"        =>"",
                "kn_message"        =>"",
                "ru_message"        =>"",
                "id_message"        =>"",
                "tl_message"        =>"",
                "zh_message"        =>"",
                // "es_message" => "Torneo {{name}} unió con éxito."
                ),
            );
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);

        }   

    function down()
    {
        //down script  
        // $this->db->where_in('notification_type', array(441,442,443));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);
    }
}