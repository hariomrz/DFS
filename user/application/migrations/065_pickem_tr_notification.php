<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pickem_tr_notification extends CI_Migration {

    public function up() {
       
        $notifications = array(
            array(
            "notification_type" =>473,
            "en_subject"=>"",
            "hi_subject"=>"",
            "tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => 'Tournament {{name}} joined successfully.',
            "en_message"=>'Tournament {{name}} joined successfully.', 
            "hi_message"=>'टूर्नामेंट {{name}} सफलतापूर्वक शामिल हो गए।',
            "tam_message"=> 'போட்டி {{name}} வெற்றிகரமாக இணைந்தது.',
            "ben_message"=>'টুর্নামেন্ট {{name}} সফলভাবে যোগ দেন।',
            "pun_message"=>'ਟੂਰਨਾਮੈਂਟ {{name} successfully ਸਫਲਤਾਪੂਰਵਕ ਸ਼ਾਮਲ ਹੋਇਆ.',
            "fr_message"=>'Tournoi {{name}} a ​​rejoint avec succès.',
            "guj_message"=>'ટુર્નામેન્ટ {{name}} સફળતાપૂર્વક જોડાયા હતા.',
            "th_message"=>'เข้าร่วมการแข่งขัน {{name}} เรียบร้อยแล้ว',
            "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
            "notification_type" =>474,
            "en_subject"=>"",
            "hi_subject"=>"",
            "tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => 'Hey, {{name}} tournament is live now! Check your picks.',
            "en_message"=>'Hey, {{name}} tournament is live now! Check your picks.', 
            "hi_message"=>'{{name}} टूर्नामेंट लाइव है! अपने पिक्स की जाँच करें।',
            "guj_message"=>'અરે, {{name}} ટુર્નામેન્ટ હવે લાઇવ છે! તમારા ચૂંટણીઓ તપાસો.',
            "tam_message"=> 'ஏய், {{name}} போட்டி இப்போது நேரலையில் உள்ளது! உங்கள் தேர்வுகளை சரிபார்க்கவும்.',
            "ben_message"=>'আরে, {{name}} টুর্নামেন্ট এখন লাইভ! আপনার অকার্যকর চেক করুন।',
            "pun_message"=>'ਹੇ, {{name}} ਟੂਰਨਾਮੈਂਟ ਹੁਣ ਲਾਈਵ ਹੈ! ਆਪਣੇ ਪਿਕਸ ਚੈੱਕ ਕਰੋ.',
            "fr_message"=>'Hey, tournoi {{name}} est maintenant en ligne! Vérifiez vos choix.',
            "th_message"=>'สวัสดีทัวร์นาเมนต์ของ {{name}} ถ่ายทอดสดแล้ว! ตรวจสอบการเลือกของคุณ',
            "es_message" => "Hey, {{name}} torneo es en vivo ahora! Revisar sus selecciones."
            )
            );

            // echo '<pre>';
            // print_r($notifications);die('dfd');
            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);     
    }

    function down()
    {
        //down script  
        // $this->db->where('source', '350');
        // $this->db->delete(MASTER_SOURCE);

        // $this->db->where('source', '350');
        // $this->db->delete(TRANSACTION_MESSAGES);
    }
}