
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_H2h_notification extends CI_Migration {

    public function up() {

        if (!$this->db->field_exists('tam_subject', NOTIFICATION_DESCRIPTION))   
        {
            $notification_field = array(
                'tam_subject' => array(
                    'type' => 'LONGTEXT',
                    'character_set' => 'utf8 COLLATE utf8_general_ci',
                    'null' => TRUE,
                    'default'=>NULL
                )
            );
            $this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);
        }

        if (!$this->db->field_exists('tam_message', NOTIFICATION_DESCRIPTION))   
        {
            $notification_field = array(
                'tam_message' => array(
                    'type' => 'LONGTEXT',
                    'character_set' => 'utf8 COLLATE utf8_general_ci',
                    'null' => FALSE,
                    'default'=>NULL
                )
            );
            $this->dbforge->add_column(NOTIFICATION_DESCRIPTION, $notification_field);
        }
       
        $notifications = array(
            array(
                "notification_type" => 600,
                "en_subject"=>"Congratulations!",
                "pun_subject"=>"ਵਧਾਈਆਂ!",
                "ben_subject"=>"অভিনন্দন!",
                "fr_subject"=>"Toutes nos félicitations!",
                "guj_subject"=>"અભિનંદન!",
                "hi_subject"=>"बधाई हो!",
                "th_subject"=>"ยินดีด้วย!",
                "kn_subject"=>"ಅಭಿನಂದನೆಗಳು!",
                "ru_subject"=>"Поздравляю!",
                "id_subject"=>"Selamat!",
                "tl_subject"=>"Binabati kita!",
                "zh_subject"=>"祝贺！",
                "tam_subject"=>"வாழ்த்துகள்!",
                "message"=>"You have been matched with {{user_name}} for {{contest_name}}",
                "en_message"=>"You have been matched with {{user_name}} for {{contest_name}}",
                "pun_message"=>"ਤੁਹਾਡਾ {{contest_name}} ਲਈ {{user_name}} ਨਾਲ ਮੇਲ ਹੋਇਆ ਹੈ",
                "ben_message"=>"আপনি {{user_name}} এর সাথে মিলিত হয়েছেন {{contest_name}} এর জন্য",
                "fr_message"=>"Vous avez été assorti avec {{user_name}} pour {{contest_name}}",
                "guj_message"=>"તમે {{user_name}} {{contest_name}} સાથે મેળ ખાતા છો",
                "hi_message"=>"{{contest_name}} के लिए आप {{user_name}} से मेल खाते हैं",
                "th_message"=>"คุณได้รับการจับคู่กับ {{user_name}} สำหรับ {{contest_name}}",
                "kn_message"=>"{{contest_name}} ಗಾಗಿ ನೀವು {{user_name}} ಗೆ ಹೊಂದಿದ್ದೀರಿ.",
                "ru_message"=>"Вы были сопоставлены с {{user_name}} для {{contest_name}}",
                "id_message"=>"Anda telah dicocokkan dengan {{user_name}} untuk {{contest_name}}",
                "tl_message"=>"Ikaw ay naitugma sa {{user_name}} para {{contest_name}}",
                "zh_message"=>"您已与{{contest_name}}匹配的{{user_name}}匹配",
                "tam_message"=>"{{contest_name}} க்கான {{user_name}} உடன் நீங்கள் பொருந்தியுள்ளீர்கள்"
            )
        );

        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);
    }   

    function down()
    {
        //down script  
        // $this->db->where('notification_type',600);
        // $this->db->delete(NOTIFICATION_DESCRIPTION);
    }
}