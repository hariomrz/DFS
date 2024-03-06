<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dfst_notification extends CI_Migration {

    public function up() {
       
        $notifications = array(
            array(
            "notification_type" =>470,
            "en_subject"=>"",
            "hi_subject"=>"",
            //"tam_subject"=>"",
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
            "th_message"=>'เข้าร่วมการแข่งขัน {{name}} เรียบร้อยแล้ว'
           // "es_message" => "Torneo {{name}} unió con éxito."
            ),
            array(
            "notification_type" =>471,
            "en_subject"=>"",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => 'Hey, {{name}} tournament is live now! Check your score.',
            "en_message"=>'Hey, {{name}} tournament is live now! Check your score.', 
            "hi_message"=>'अरे, {{name}} टूर्नामेंट को लाइव है! अपने स्कोर की जाँच करें।',
            "guj_message"=>'અરે, {{name}} ટુર્નામેન્ટ હવે લાઇવ છે! તમારા સ્કોર તપાસો.',
            "tam_message"=> 'ஏய், {{name}} போட்டி இப்போது நேரலையில் உள்ளது! உங்கள் மதிப்பெண்ணைச் சரிபார்க்கவும்
            ',
            "ben_message"=>'আরে, {{name}} টুর্নামেন্ট এখন লাইভ! আপনার স্কোর চেক করুন',
            "pun_message"=>'ਹੇ, {{name}} ਟੂਰਨਾਮੈਂਟ ਹੁਣ ਲਾਈਵ ਹੈ! ਆਪਣੇ ਸਕੋਰ ਦੀ ਜਾਂਚ ਕਰੋ.',
            "fr_message"=>'Hey, tournoi {{name}} est maintenant en ligne! Vérifiez votre score.',
            "th_message"=>'สวัสดีทัวร์นาเมนต์ของ {{name}} ถ่ายทอดสดแล้ว! ตรวจสอบคะแนนของคุณ'
            //"es_message" => "Hey, {{name}} torneo es en vivo ahora! Comprobar su puntuación."
            ),
            array(
            "notification_type" =>472,
            "en_subject"=>"Congratulations!",
            "hi_subject"=>"बधाई हो!",
            //"tam_subject"=>"வாழ்த்துக்கள்!",
            "ben_subject"=>"অভিনন্দন!",
            "pun_subject"=>"ਵਧਾਈਆਂ!",
            "fr_subject"=>"Toutes nos félicitations!",
            "guj_subject"=>"અભિનંદન!",
            "th_subject"=>"ยินดีด้วย!",
            "message" => "You are a winner in the {{name}} tournament.",
            "en_message"=>'You are a winner in the {{name}} tournament.', 
            "hi_message"=>'आप {{name}} टूर्नामेंट में एक विजेता हैं',
            "tam_message"=> '{{name}} போட்டியில் நீங்கள் வெற்றியாளராக உள்ளீர்கள்',
            "ben_message"=>'আপনি {{name}} টুর্নামেন্টে বিজয়ী।',
            "pun_message"=>'ਤੁਸੀਂ {{name}} ਟੂਰਨਾਮੈਂਟ ਵਿੱਚ ਜੇਤੂ ਹੋ.',
            "fr_message"=>'Vous êtes un gagnant du tournoi {{name}}.',
            "guj_message"=>'તમે {{name}} ટૂર્નામેન્ટમાં વિજેતા છો.',
            "th_message"=>'คุณเป็นผู้ชนะในทัวร์นาเมนต์ {{name}}'
            //"es_message" => "Usted es un ganador en el {{name}} torneo."
            ),
            array(
            "notification_type" =>473,
            "en_subject"=>"",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => '{{new_match_count}} New Fixtures are available in {{name}} tournament.',
            "en_message"=>'{{new_match_count}} New Fixtures are available in {{name}} tournament.', 
            "hi_message"=>'{{new_match_count}} नई फिक्स्चर {{name}} टूर्नामेंट में उपलब्ध हैं।',
            "ben_message"=>'{{new_match_count}} নতুন রাজধানী {{name}} টুর্নামেন্ট পাওয়া যায়।',
            "fr_message"=>'{{new_match_count}} Les nouveaux appareils sont disponibles en tournoi {{name}}.',
            "guj_message"=>'{{new_match_count}} નવી ફિક્ષ્ચર્સ {{name}} ટુર્નામેન્ટમાં ઉપલબ્ધ છે.',
            "pun_message"=>'{{new_match_count}} ਨਵਾਂ ਫਿਕਸਚਰ {{name}} ਟੂਰਨਾਮੈਂਟ ਵਿੱਚ ਉਪਲਬਧ ਹਨ.',
            "tam_message"=> '{{new_match_count}} FI {{name}} போட்டியில் புதிய சாதனங்கள் கிடைக்கின்றன.',
            "th_message"=>'{{new_match_count}} การแข่งขันใหม่มีให้เลือกในทัวร์นาเมนต์ {{name}}'
            //"es_message" => "{{new_match_count}} nuevos accesorios están disponibles en {{name}} torneo."
            ),
            array(
                "notification_type" =>480,
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
                "notification_type" =>481,
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
                ),
                array(
                    "notification_type" =>476,
                    "en_subject"=>"",
                    "hi_subject"=>"",
                    //"tam_subject"=>"",
                    "ben_subject"=>"",
                    "pun_subject"=>"",
                    "fr_subject"=>"",
                    "guj_subject"=>"",
                    "th_subject"=>"",
                    "message" => '₹{{amount}} deducted as TDS',
                    "en_message"=>'₹{{amount}} deducted as TDS', 
                    "hi_message"=>'₹ {{amount}} टीडीएस के रूप में कटौती की',
                    "guj_message"=>'₹ {{amount}} TDS તરીકે કપાત',
                    "tam_message"=> '₹ {{amount}} அதுமட்டுமல்ல கழிப்பதற்கு',
                    "ben_message"=>'₹ {{amount}} উত্সমূলে যেমন কাটা',
                    "pun_message"=>'₹ {{amount}} TDS ਤੌਰ ਕਟੌਤੀ',
                    "fr_message"=>'₹ {{amount}} déduit que TDS',
                    "th_message"=>'₹ {{amount}} หักเป็นค่า TDS'
                    //"es_message" => "{{name}} juego del torneo {{match}} se cancela por Admin"
                    )


            );

            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notifications);     

            $value = array(
                    array(
                    'source' => '370',
                    'name'=> 'DFS Tournament: entry fee'
                ),
                array(
                    'source' => '371',
                    'name'=> 'DFS Tournament: Fee Refund For Tournament'
                ),
                array(
                    'source' => '372',
                    'name'=> 'DFS Tournament: Won Contest Prize'
                ),
                array(
                    'source' => '373',
                    'name'=> 'DFS Tournament: Total TDS Deducted'
                ),

            );
            $this->db->insert_batch(MASTER_SOURCE,$value);

            $transaction_messages = array(
                array(
                    'source' => 370,
                    'en_message' => 'Entry fee for %s',
                    'fr_message' => 'Frais d\'inscription pour %s',
                    'hi_message' => '%s के लिए प्रवेश शुल्क',
                    'guj_message' => '%s માટે પ્રવેશ ફી',
                    'ben_message' => '%s এর জন্য প্রবেশ ফি',
                    'pun_message' => '%s ਲਈ ਐਂਟਰੀ ਫੀਸ',
                   // 'es_message' => 'Precio de la entrada para %s',
                    'tam_message' => '%s க்கான நுழைவு கட்டணம்',
                    'th_message' => 'ค่าธรรมเนียมแรกเข้าสำหรับ %s',
                ),
                array(
                    'source' => 371,
                    'en_message' => 'Fee Refund For %s Tournament',
                    'fr_message' => 'Remboursement des frais pour le %s tournoi',
                    'hi_message' => 'प्रतियोगिता %s के लिए शुल्क वापसी',
                    'guj_message' => 'હરીફાઈ %s માટે ફી પરત',
                    'ben_message' => 'টুর্নামেন্টের %s জন্য ফি ফেরত',
                    'pun_message' => 'ਟੂਰਨਾਮੈਂਟ %s ਲਈ ਫੀਸ ਵਾਪਸੀ',
                   // 'es_message' => 'Para Honorario del reembolso del torneo',
                    'tam_message' => 'போட்டிக்கான %s கட்டணம் திரும்பப்பெறுதல்',
                    'th_message' => 'การคืนเงินค่าธรรมเนียมสำหรับการแข่งขัน %s' ,
                ),
                array(
                    'source' => 372,
                    'en_message' => 'Won tournament Prize',
                    'fr_message' => 'Remporté le prix du concours',
                    'hi_message' => 'प्रतियोगिता का पुरस्कार जीता',
                    'guj_message' => 'કોન્ટેસ્ટ પ્રાઇઝ જીત્યો',
                    'ben_message' => 'ওন কনটেস্ট পুরস্কার',
                    'pun_message' => 'ਟੂਰਨਾਮੈਂਟ ਲਈ ਫੀਸ ਵਾਪਸੀ',
                   // 'es_message' => 'Premio ganó el concurso',
                    'tam_message' => 'போட்டி பரிசு வென்றது',
                    'th_message' => 'ได้รับรางวัลการประกวด',
                ),
                array(
                    'source' => 373,
                    'en_message' => 'DFS Tournament: Total TDS Deducted',
                    'hi_message' => 'DFS टूर्नामेंट: कुल टीडीएस कटौती',
                    'guj_message' => 'DFS ટુર્નામેન્ટ: કુલ TDS ડિડક્ટેડ',
                    'fr_message' => 'DFS Tournoi: Total TDS Déduit',
                    'ben_message' => 'DFS টুর্নামেন্ট: মোট উত্সমূলে কাটা',
                    'pun_message' => 'ਪਿਕਮ ਟੂਰਨਾਮੈਂਟ: ਕੁੱਲ ਟੀਡੀਐਸ ਕਟੌਤੀ',
                    //'es_message' => 'Torneo DFS: Total TDS Deducido',
                    'tam_message' => 'பிக்கெம் போட்டி: மொத்த டி.டி.எஸ் கழிக்கப்பட்டது',
                    'th_message' => 'DFS Tournament: หัก TDS ทั้งหมด',
                ),
            );
          $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

          $this->db->insert(APP_CONFIG,array(
              'name' => "Allow DFS tournament",
              'key_name'=>"allow_dfs_tournament",
              'key_value' => 0,
              'custom_data'=> NULL
          ));


          $email_templtes = 
          array(
              array(
                  'notification_type' => 470,
                  'template_name'=> 'join-tournament',
                  'subject' => 'Your {{name}} tournament joining is confirmed!',
                  'template_path' => 'join-tournament',
                  'status' => 1,
                  'type' => 0,
                  'display_label' => 'Join Tournament',
                  'date_added' => '2021-04-29 12:15:41'
              ),
               array(
                  'notification_type' => 472,
                  'template_name'=> 'tournament-won',
                  'subject' => 'Tournament Won',
                  'template_path' => 'tournament-won',
                  'status' => 1,
                  'type' => 0,
                  'display_label' => 'Tournament Won',
                  'date_added' => '2021-04-29 12:15:41'
               ),
               array(
                'notification_type' => 480,
                'template_name'=> 'tournament-cancel-by-admin',
                'subject' => 'Oops! {{name}} tournament has been Cancelled',
                'template_path' => 'tournament-cancel-by-admin',
                'status' => 1,
                'type' => 0,
                'display_label' => 'Tournament Cancel',
                'date_added' => '2021-04-29 12:15:41'
            )
          );
          $this->db->insert_batch(EMAIL_TEMPLATE,$email_templtes);
    }

    function down()
    {
        //down script  
        // $this->db->where_in('source', array(370,371,372,373));
        // $this->db->delete(MASTER_SOURCE);

        // $this->db->where_in('source', array(370,371,372,373));
        // $this->db->delete(TRANSACTION_MESSAGES);

        // $this->db->where_in('notification_type', array(470,471,472,473));
        // $this->db->delete(NOTIFICATION_DESCRIPTION);
    }
}