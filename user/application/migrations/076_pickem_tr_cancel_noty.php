<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pickem_tr_cancel_noty extends CI_Migration {

    public function up() {
       
        $notifications = array(
            array(
            "notification_type" =>474,
            "en_subject"=>"",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => '{{name}} tournament is cancelled by Admin.',
            "en_message"=>'{{name}} tournament is cancelled by Admin.', 
            "hi_message"=>'{{name}} टूर्नामेंट व्यवस्थापक द्वारा रद्द कर दिया गया।',
            "tam_message"=> '{{name}} போட்டியில் நிர்வாகம் மூலம் ரத்து செய்யப்பட்டது',
            "ben_message"=>'{{name}} টুর্নামেন্ট এডমিন দ্বারা বাতিল করা হয়েছে',
            "pun_message"=>'{{name}} ਮੁਕਾਬਲੇ ਪਰਬੰਧ ਕੇ ਰੱਦ ਕੀਤਾ ਗਿਆ ਹੈ',
            "fr_message"=>'tournoi {{name}} est annulé par Admin',
            "guj_message"=>'{{name}} ટુર્નામેન્ટ સંચાલન દ્વારા રદ કરી છે',
            "th_message"=>'{{name}} ชื่อการแข่งขันถูกยกเลิกโดยผู้ดูแลระบบ'
            //"es_message" => "{{name}} torneo se cancela por Admin"
            ),
            array(
            "notification_type" =>475,
            "en_subject"=>"",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => '{{name}} tournament game {{match}} is cancelled by Admin',
            "en_message"=>'{{name}} tournament game {{match}} is cancelled by Admin', 
            "hi_message"=>'{{name}} टूर्नामेंट खेल {{match}} व्यवस्थापक द्वारा रद्द कर दिया गया',
            "guj_message"=>'{{name}} ટુર્નામેન્ટ ગેઇમમાં {{match}} સંચાલન દ્વારા રદ કરી છે',
            "tam_message"=> '{{name}} போட்டியில் விளையாட்டு {{match}} நிர்வாகம் மூலம் ரத்து செய்யப்பட்டது',
            "ben_message"=>'{{name}} টুর্নামেন্ট খেলা {{match}} এডমিন দ্বারা বাতিল করা হয়েছে',
            "pun_message"=>'{{name}} ਮੁਕਾਬਲੇ ਦੀ ਖੇਡ {{match}} ਪਰਬੰਧ ਕੇ ਰੱਦ ਕੀਤਾ ਗਿਆ ਹੈ',
            "fr_message"=>'{{name}} jeu de tournoi {{match}} correspondance est annulée par Admin',
            "th_message"=>'{{name}} ชื่อเกมการแข่งขันการแข่งขัน {{match}} ถูกยกเลิกโดยผู้ดูแลระบบ'
            //"es_message" => "{{name}} juego del torneo {{match}} se cancela por Admin"
            )
            );

            // echo '<pre>';
            // print_r($notifications);die('dfd');
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);     
    }

    function down()
    {
        //down script  
        // $this->db->where_in('notification_type', array(474,475));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);

        
    }
}