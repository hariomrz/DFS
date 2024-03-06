<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_User_engagement extends CI_Migration {

    public function up()
	{
        $notification = array(
            array(
                "notification_type" =>580,
                "en_subject"=>"Congratulations!",
                "hi_subject"=>"बधाई हो",
                //"tam_subject"=>"",
                "ben_subject"=>"অভিনন্দন",
                "pun_subject"=>"ਵਧਾਈ",
                "fr_subject"=>"Toutes nos félicitations",
                "guj_subject"=>"અભિનંદન",
                "th_subject"=>"ขอแสดงความยินดี",
                "message" => 'You have received {{amount}} coins in Quiz.',
                "en_message"=>'You have received {{amount}} coins in Quiz.', 
                "hi_message"=> 'आपको क्विज़ में {{amount}} सिक्के प्राप्त हुए हैं।',
                "tam_message"=> 'வினாடி வினாவில் நீங்கள் {{amount}} நாணயங்களைப் பெற்றுள்ளீர்கள்.',
                "ben_message"=>'আপনি QUIZ এ {{amount}} কয়েন পেয়েছেন।',
                "pun_message"=>'ਤੁਹਾਨੂੰ ਕੁਇਜ਼ ਵਿੱਚ {{amount} Coins ਪ੍ਰਾਪਤ ਹੋਏ ਹਨ.',
                "fr_message"=>'Vous avez reçu {{amount}} monnaie en quiz.',
                "guj_message"=>'તમે ક્વિઝમાં {{amount}} સિક્કા પ્રાપ્ત થયા છે.',
                "th_message"=>'คุณได้รับ {{amount}} เหรียญในแบบทดสอบ',
                "ru_subject" => "Поздравляю",
                "id_subject" => "Selamat",
                "tl_subject" => "pagbati",
                "zh_subject" => "祝贺",
                "kn_subject" => "ಅಭಿನಂದನೆಗಳು",
                "ru_message" => "Вы получили {{amount}} монет в викторине.",
                "id_message" => "Anda telah menerima {{amount}} koin dalam kuis.",
                "tl_message" => "Nakatanggap ka ng {{amount}} mga barya sa pagsusulit.",
                "zh_message" => "您已在测验中收到{{amount}}硬币。",
                "kn_message" => "ರಸಪ್ರಶ್ನೆಯಲ್ಲಿ ನೀವು {{amount}} ನಾಣ್ಯಗಳನ್ನು ಸ್ವೀಕರಿಸಿದ್ದೀರಿ."                
            ),
            array(
                "notification_type" =>422,
                "en_subject"=>"🥳 Congratulations 🥳",
                "hi_subject"=>"",
                //"tam_subject"=>"",
                "ben_subject"=>"",
                "pun_subject"=>"",
                "fr_subject"=>"",
                "guj_subject"=>"",
                "th_subject"=>"",
                "message" => 'You are an Affiliate now. Welcome to the team. See what you got in store for you. 💰💰',
                "en_message"=>'You are an Affiliate now. Welcome to the team. See what you got in store for you. 💰💰', 
                "hi_message"=> '',
                "tam_message"=> '',
                "ben_message"=>'',
                "pun_message"=>'',
                "fr_message"=>'',
                "guj_message"=>'',
                "th_message"=>'',
                "ru_subject" => "",
                "id_subject" => "",
                "tl_subject" => "",
                "zh_subject" => "",
                "kn_subject" => "",
                "ru_message" => "",
                "id_message" => "",
                "tl_message" => "",
                "zh_message" => "",
                "kn_message" => ""              
            ),
            array(
              "notification_type" =>581,
              "en_subject"=>"We love your Feedbacks 🧡",
              "hi_subject"=>"",
              //"tam_subject"=>"",
              "ben_subject"=>"",
              "pun_subject"=>"",
              "fr_subject"=>"",
              "guj_subject"=>"",
              "th_subject"=>"",
              "message" => 'Because we love your feedbacks. 😍 See what\'s in here 📩',
              "en_message"=>'Because we love your feedbacks. 😍 See what\'s in here 📩', 
              "hi_message"=> '',
              "tam_message"=> '',
              "ben_message"=>'',
              "pun_message"=>'',
              "fr_message"=>'',
              "guj_message"=>'',
              "th_message"=>'',
              "ru_subject" => "",
              "id_subject" => "",
              "tl_subject" => "",
              "zh_subject" => "",
              "kn_subject" => "",
              "ru_message" => "",
              "id_message" => "",
              "tl_message" => "",
              "zh_message" => "",
              "kn_message" => ""              
          ),
          array(
            "notification_type" =>582,
            "en_subject"=>"Today's Quiz is Live ‼️🕙",
            "hi_subject"=>"",
            //"tam_subject"=>"",
            "ben_subject"=>"",
            "pun_subject"=>"",
            "fr_subject"=>"",
            "guj_subject"=>"",
            "th_subject"=>"",
            "message" => 'Click here to play and win rewards. 🪙🪙',
            "en_message"=>'Click here to play and win rewards. 🪙🪙', 
            "hi_message"=> '',
            "tam_message"=> '',
            "ben_message"=>'',
            "pun_message"=>'',
            "fr_message"=>'',
            "guj_message"=>'',
            "th_message"=>'',
            "ru_subject" => "",
            "id_subject" => "",
            "tl_subject" => "",
            "zh_subject" => "",
            "kn_subject" => "",
            "ru_message" => "",
            "id_message" => "",
            "tl_message" => "",
            "zh_message" => "",
            "kn_message" => ""              
          ),
        array(
          "notification_type" =>583,
          "en_subject"=>"You are almost there 🤩🤩",
          "hi_subject"=>"",
          //"tam_subject"=>"",
          "ben_subject"=>"",
          "pun_subject"=>"",
          "fr_subject"=>"",
          "guj_subject"=>"",
          "th_subject"=>"",
          "message" => 'Just a few coins more and you can get the {{merchandise_name}} 🛍️😵‍💫🛍️',
          "en_message"=>'Just a few coins more and you can get the {{merchandise_name}} 🛍️😵‍💫🛍️', 
          "hi_message"=> '',
          "tam_message"=> '',
          "ben_message"=>'',
          "pun_message"=>'',
          "fr_message"=>'',
          "guj_message"=>'',
          "th_message"=>'',
          "ru_subject" => "",
          "id_subject" => "",
          "tl_subject" => "",
          "zh_subject" => "",
          "kn_subject" => "",
          "ru_message" => "",
          "id_message" => "",
          "tl_message" => "",
          "zh_message" => "",
          "kn_message" => ""              
      ),
      array(
        "notification_type" =>584,
        "en_subject"=>"Yay!",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message" => 'You made it to a {{merchandise_name}} 🛍️😵‍💫🛍️ Claim it now.',
        "en_message"=>'You made it to a {{merchandise_name}} 🛍️😵‍💫🛍️ Claim it now.',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
      array(
        "notification_type" =>585,
        "en_subject"=>"✊🏻 Knock Knock‼️",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message" => '{{username}} Can you take a few minutes out of your schedule for us?',
        "en_message"=>'{{username}} Can you take a few minutes out of your schedule for us?',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
      array(
        "notification_type" =>586,
        "en_subject"=>"You don\'t want to lose your rewards for today",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message"   => 'Check out what is waiting for you.🔮💰',
        "en_message"=> 'Check out what is waiting for you.🔮💰',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
      array(
        "notification_type" =>587,
        "en_subject"=>"<number> people won <total coins earned> yesterday.",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message"   => 'Grab your chance today to win big. 🤑💰',
        "en_message"=> 'Grab your chance today to win big. 🤑💰',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
      array(
        "notification_type" =>588,
        "en_subject"=>"You wanted it, we brought it 😎",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message"   => 'Try all new {{module}} and stay in game 😎',
        "en_message"=> 'Try all new {{module}} and stay in game 😎',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
      array(
        "notification_type" =>589,
        "en_subject"=>"Your game is live It\'s here 😍",
        "hi_subject"=>"",
        //"tam_subject"=>"",
        "ben_subject"=>"",
        "pun_subject"=>"",
        "fr_subject"=>"",
        "guj_subject"=>"",
        "th_subject"=>"",
        "message"   => 'Visit <game name>\'s Game Center and score along with your team 💵',
        "en_message"=> 'Visit <game name>\'s Game Center and score along with your team 💵',
        "hi_message"=> '',
        "tam_message"=> '',
        "ben_message"=>'',
        "pun_message"=>'',
        "fr_message"=>'',
        "guj_message"=>'',
        "th_message"=>'',
        "ru_subject" => "",
        "id_subject" => "",
        "tl_subject" => "",
        "zh_subject" => "",
        "kn_subject" => "",
        "ru_message" => "",
        "id_message" => "",
        "tl_message" => "",
        "zh_message" => "",
        "kn_message" => ""              
      ),
        );

            $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification);   

            $transaction_message =array(array(
                
                'source' => 470,
                'en_message' => 'Coins earned in Quiz of {{scheduled_date}}',
                'fr_message' => 'Coins gagnés dans un quiz de {{scheduled_date}}',
                'hi_message' => '{{scheduled_date}} के प्रश्नोत्तरी में अर्जित सिक्के',
                'guj_message' =>'કોઇન્સ {{scheduled_date}} ના ક્વિઝમાં કમાવ્યા',
                'ben_message' =>'কয়েন {{scheduled_date}} এর কুইজ অর্জিত',
                'pun_message' =>'Cocks {{scheduled_date}} ਦੇ ਕਵਿਜ਼ ਵਿੱਚ ਪ੍ਰਾਪਤ ਸਿੱਕੇ',
                'tam_message' =>'நாணயங்கள் {{scheduled_date}} இன் வினாவில் சம்பாதித்த நாணயங்கள்',
                'th_message' => 'เหรียญที่ได้รับจากการตอบคำถามของ {{scheduled_date}}',
                "ru_message" => "Монеты, заработанные в викторине {{scheduled_date}}",
                "id_message" => "Koin diperoleh dalam kuis {{scheduled_date}}",
                "tl_message" => "Mga barya na nakuha sa pagsusulit ng {{scheduled_date}}",
                "zh_message" => "{{scheduled_date}}测量中获得的硬币",
                "kn_message" => "{{scheduled_date}} ನ ರಸಪ್ರಶ್ನೆಯಲ್ಲಿ ಗಳಿಸಿದ ನಾಣ್ಯಗಳು}"
            ),
            array(
                'source' => 471,
                'en_message' => 'Coins credited for App Download.',
                'fr_message' => 'Coins crédités pour l\'application téléchargement.',
                'hi_message' => 'ऐप डाउनलोड के लिए सिक्के जमा किए गए।',
                'guj_message' =>'સિક્કા એપ્લિકેશન ડાઉનલોડ માટે શ્રેય.',
                'ben_message' =>'মুদ্রা ডাউনলোডের জন্য ক্রেডিট ক্রেডিট।',
                'pun_message' =>'ਐਪ ਡਾ Download ਨਲੋਡ ਕਰਨ ਲਈ ਸਿਹਤਮੰਦ ਸਿੱਕੇ.',
                'tam_message' =>'பயன்பாட்டு பதிவிறக்கத்திற்கான நாணயங்கள் வரவு.',
                'th_message' => 'เหรียญให้เครดิตสำหรับการดาวน์โหลดแอป',
                "ru_message" => "Монеты зачислены на загрузку приложения.",
                "id_message" => "Koin dikreditkan untuk unduhan aplikasi.",
                "tl_message" => "Mga barya na kredito para sa pag-download ng app.",
                "zh_message" => "Coins归功于应用程序下载。",
                "kn_message" => "ಅಪ್ಲಿಕೇಶನ್ ಡೌನ್ಲೋಡ್ಗಾಗಿ ನಾಣ್ಯಗಳು ಸಲ್ಲುತ್ತದೆ."

            
        )
            ) ;
          $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_message);

          $sources = array(
              array('source' => 470,"name" => "coin earned for quiz"),
              array('source' => 471,"name" => "download app coins")
          );

          $this->db->insert_batch(MASTER_SOURCE,$sources);

        $sql = "UPDATE ".$this->db->dbprefix(EARN_COINS)." SET `image_url` = 'feedback-img-nw.png' WHERE `vi_earn_coins`.`module_key` = 'feedback';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(EARN_COINS)." SET `image_url` = 'prediction-img-nw.png' WHERE `vi_earn_coins`.`module_key` = 'prediction';";
        $this->db->query($sql);

        $sql = "UPDATE ".$this->db->dbprefix(EARN_COINS)." SET `image_url` = 'refer-img-nw.png' WHERE `vi_earn_coins`.`module_key` = 'refer-a-friend';";
        $this->db->query($sql);

        $earn_coins = array (
            'module_key' => 'download_app',
            'en' => 
            json_encode(array (
              'label' => 'Download App',
              'description' => '',
              'button_text' => '',
            )) ,
            'hi' => 
            json_encode(array (
              'label' => 'ऐप डाउनलोड करें',
              'description' => '',
              'button_text' => '',
            )) ,
            'guj' => 
            json_encode(array (
              'label' => 'એપ્લિકેશન ડાઉનલોડ કરો',
              'description' => '',
              'button_text' => '',
            )) ,
            'fr' => 
            json_encode(array (
              'label' => 'Télécharger l\'application',
              'description' => '',
              'button_text' => '',
            )) ,
            'ben' => 
            json_encode(array (
              'label' => 'অ্যাপ্লিকেশন ডাউনলোড করুন',
              'description' => '',
              'button_text' => '',
            )) ,
            'pun' => 
            json_encode(array (
              'label' => 'ਡਾਉਨਲੋਡ ਐਪ',
              'description' => '',
              'button_text' => '',
            )) ,
            'kn' => 
            json_encode(array (
              'label' => 'ಡೌನ್ಲೋಡ್ ಅಪ್ಲಿಕೇಶನ್',
              'description' => '',
              'button_text' => '',
            )) ,
            'ru' => 
            json_encode(array (
              'label' => 'Скачать приложение',
              'description' => '',
              'button_text' => '',
            )) ,
            'tl' => 
            json_encode(array (
              'label' => 'I-download ang App.',
              'description' => '',
              'button_text' => '',
            )) ,
            'zh' => 
            json_encode(array (
              'label' => '下载应用程序',
              'description' => '',
              'button_text' => '',
            )) ,
            'id' => 
            json_encode(array (
              'label' => 'Unduh aplikasi',
              'description' => '',
              'button_text' => '',
            )) ,
            'th' => 
            json_encode(array (
              'label' => 'ดาวน์โหลดแอป',
              'description' => '',
              'button_text' => '',
            )) ,
            'tam' => 
            json_encode(array (
              'label' => 'APP ஐப் பதிவிறக்கவும்',
              'description' => '',
              'button_text' => '',
            )) ,
            'image_url' => 'download-img-ic.png',
            'status' => 1,
            'url' => '',
            );

            $this->db->insert(EARN_COINS,$earn_coins);

            $sql="UPDATE `vi_earn_coins` SET `en` = '{\"label\": \"Give Feedback\", \"button_text\": \"Write Us\", \"description\": \"Genuine feedback will get coins after admin approval\"}', `hi` = '{\"label\": \"प्रतिक्रिया दें\", \"button_text\": \"हमें लिखें\", \"description\": \"वास्तविक प्रतिक्रिया व्यवस्थापक अनुमोदन के बाद सिक्के मिल जाएगा\"}', `guj` = '{\"label\": \"અભીપ્રાય આપો\", \"button_text\": \"અમને લખો\", \"description\": \"જેન્યુઇન પ્રતિસાદ એડમિન મંજૂરી પછી સિક્કા મળશે\"}', `fr` = '{\"label\": \"Donner des commentaires\", \"button_text\": \"Écrivez-nous\", \"description\": \"rétroaction authentique sera obtenir des pièces après approbation de l\'administrateur\"}', `ben` = '{\"label\": \"প্রতিক্রিয়া দিন\", \"button_text\": \"আমাদের লিখুন\", \"description\": \"জেনুইন প্রতিক্রিয়া অ্যাডমিন অনুমোদনের পরে কয়েন পাবেন\"}', `pun` = '{\"label\": \"ਫੀਡਬੈਕ ਦਿਓ\", \"button_text\": \"ਸਾਨੂੰ ਲਿਖੋ\", \"description\": \"ਸਹੀ ਫੀਡਬੈਕ ਐਡਮਿਨ ਦੀ ਮਨਜ਼ੂਰੀ ਤੋਂ ਬਾਅਦ ਸਿੱਕੇ ਪ੍ਰਾਪਤ ਕਰੇਗਾ\"}', `tam` = '{\"label\": \"கருத்து தெரிவிக்கவும்\", \"button_text\": \"எங்களை எழுது\", \"description\": \"உண்மையான கருத்துக்களை நிர்வாக ஒப்புதலுக்கு பின்னர் நாணயங்கள் கிடைக்கும்\"}', `th` = '{\"label\": \"ให้ข้อเสนอแนะ\", \"button_text\": \"เขียนถึงเรา\", \"description\": \"ข้อเสนอแนะของแท้จะได้รับเหรียญหลังจากได้รับอนุมัติผู้ดูแลระบบ\"}', `kn` = '{\"label\": \"ಪ್ರತಿಕ್ರಿಯೆ ನೀಡಿ\", \"button_text\": \"ನಮಗೆ ಇಮೇಲ್ ಮಾಡಿ\", \"description\": \"ನಿರ್ವಾಹಕರ ಅನುಮೋದನೆಯ ನಂತರ ಅಧಿಕೃತ ಪ್ರಸ್ತಾಪವನ್ನು ನೀಡಲಾಗುವುದು\"}', `ru` = '{\"label\": \"Дать обратную связь\", \"button_text\": \"Email kami\", \"description\": \"Proposal otentik akan diberikan setelah persetujuan administrator\"}', `tl` = '{\"label\": \"Magbigay ng feedback.\", \"button_text\": \"I-email sa amin\", \"description\": \"Ang tunay na panukala ay ibibigay pagkatapos ng pag-apruba ng administrator\"}', `zh` = '{\"label\": \"给予反馈\", \"button_text\": \"写信给我们\", \"description\": \"真正的建议书将在管理员批准后颁发。\"}', `id` = '{\"label\": \"Berikan umpan balik\", \"button_text\": \"Email kami\", \"description\": \"Proposal otentik akan diberikan setelah persetujuan administrator\"}' WHERE `vi_earn_coins`.`module_key` = 'feedback';";
            $this->db->query($sql);
           
            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Play Prediction' WHERE source = 41;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Download App' WHERE `source` = 471;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Play Quiz' WHERE `source` = 470;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Spin & Earn' WHERE `source` = 322;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Daily Check-ins' WHERE `source` = 144;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Sports pick\'em' WHERE `source` = 181;";
            $this->db->query($sql);

            $sql="UPDATE ".$this->db->dbprefix(MASTER_SOURCE)." SET `name` = 'Play Prediction' WHERE `source` = 41;";
            $this->db->query($sql);


    }
}