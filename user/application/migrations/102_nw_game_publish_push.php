<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Nw_game_publish_push extends CI_Migration {

    public function up() {
       
        $notifications = array(
            array(
            "notification_type" =>440,
            "en_subject"=>"Grand game is open",
            "hi_subject"=>"भव्य खेल खुला है",
            "guj_subject"=>"ભવ્ય રમત ખુલ્લી છે",
            "fr_subject"=>"Le grand jeu est ouvert",
            "ben_subject"=>"গ্র্যান্ড গেম উন্মুক্ত",
            "pun_subject"=>"ਸ਼ਾਨਦਾਰ ਖੇਡ ਖੁੱਲ੍ਹੀ ਹੈ",
            // "tam_subject"=>"",
            "th_subject"=>"เกมแกรนด์เปิดแล้ว",
            "kn_subject"=>"ಭವ್ಯವಾದ ಆಟವು ತೆರೆದಿರುತ್ತದೆ",
            "ru_subject"=>"Великая игра открыта",
            "id_subject"=>"Permainan besar terbuka",
            "tl_subject"=>"Grand game ay bukas",
            "zh_subject"=>"盛大游戏开启",
            "message"           =>"Join the hottest grand contest {{collection_name}} and win huge",
            "en_message"        =>"Join the hottest grand contest {{collection_name}} and win huge",
            "hi_message"        =>"सबसे भव्य प्रतियोगिता {{Collection_name}} में शामिल हों और बड़ी जीत हासिल करें",
            "guj_message"       =>"સૌથી ભવ્ય સ્પર્ધા {{collection_name}} માં જોડાઓ અને જોરદાર જીત મેળવો",
            "fr_message"        =>"Rejoignez le grand concours le plus populaire {{collection_name}} et gagnez un énorme",
            "ben_message"       =>"হটেস্ট গ্র্যান্ড প্রতিযোগিতায় যোগ দিন {{collection_name}} এবং জিতুন বিশাল",
            "pun_message"       =>"ਸਭ ਤੋਂ ਮਸ਼ਹੂਰ ਮੁਕਾਬਲੇ {{collection_name}} ਵਿੱਚ ਸ਼ਾਮਲ ਹੋਵੋ ਅਤੇ ਵੱਡੀ ਜਿੱਤ ਪ੍ਰਾਪਤ ਕਰੋ",
            "tam_message"       =>"மிகச் சிறந்த மாபெரும் போட்டியில் {{collection_name}} சேர்ந்து பெரும் வெற்றியைப் பெறுங்கள்",
            "th_message"        =>"เข้าร่วมการแข่งขันที่ยิ่งใหญ่ที่ร้อนแรงที่สุด {{collection_name}} และรับรางวัลใหญ่",
            "kn_message"        =>"ಅತ್ಯಂತ ಭವ್ಯವಾದ ಸ್ಪರ್ಧೆಗೆ ಸೇರಿಕೊಳ್ಳಿ {{collection_name}} ಮತ್ತು ದೊಡ್ಡದನ್ನು ಗೆದ್ದಿರಿ",
            "ru_message"        =>"Присоединяйтесь к крупнейшему грандиозному конкурсу {{collection_name}} и выиграйте огромные",
            "id_message"        =>"Bergabunglah dengan kontes akbar terpanas {{collection_name}} dan menangkan besar",
            "tl_message"        =>"Sumali sa pinakamainit na engrandeng paligsahan na {{koleksyon_name}} at manalo ng malaki",
            "zh_message"        =>"加入最热门的盛大比赛{{collection_name}}并赢得大奖",
           // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            );

            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);
        }   

            function down()
    {
        //down script  
        // $this->db->where_in('notification_type', array(440));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);
    }
}

?>