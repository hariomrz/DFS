<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Equity extends CI_Migration {

    public function up() {

      $hub_setting = array(
          array(
            'game_key' => 'allow_equity',
              'en_title' => "Stock Equity",              
              'hi_title'=> "काल्पनिक स्टॉक",
              'guj_title' => 'સ્ટોક ફantન્ટેસી',
              'fr_title' => 'Stock Fantaisie',
              'ben_title' => 'স্টক ফ্যান্টাসি',
              'pun_title' => 'ਸਟਾਕ ਕਲਪਨਾ',
              'tam_title' => 'பங்கு பேண்டஸி',              
              'th_title' => 'หุ้นแฟนตาซี',
              'kn_title' => 'ಸ್ಟಾಕ್ ಫ್ಯಾಂಟಸಿ',
              'ru_title' => 'Склад Фантазия',
              'id_title' => 'Fantasi Saham',
              'tl_title' => 'Stock Pantasiya',
              'zh_title' => '股票幻想',
              'en_desc' => "Play fantasy game by picking stocks and win prizes",
              'hi_desc' => "स्टॉक चुनकर काल्पनिक गेम खेलें और पुरस्कार जीतें",
              'guj_desc' => "સ્ટોક્સ ચૂંટતા અને ઇનામો જીતીને કાલ્પનિક રમત રમો",
              'fr_desc' => 'Jouez à un jeu fantastique en choisissant des actions et gagnez des prix',
              'ben_desc' => 'স্টক বাছাই করে ফ্যান্টাসি গেম খেলুন এবং পুরষ্কার জিতে নিন',
              'pun_desc' => 'ਸਟਾਕਾਂ ਨੂੰ ਚੁਣ ਕੇ ਕਲਪਨਾ ਖੇਡ ਖੇਡੋ ਅਤੇ ਇਨਾਮ ਜਿੱਤੇ',
              'tam_desc' => 'பங்குகளைத் தேர்ந்தெடுத்து பரிசுகளை வெல்வதன் மூலம் கற்பனை விளையாட்டை விளையாடுங்கள்',              
              'th_desc' => 'เล่นเกมแฟนตาซีด้วยการเลือกหุ้นและลุ้นรับรางวัล',
              'kn_desc' => 'ಷೇರುಗಳನ್ನು ಆರಿಸಿ ಮತ್ತು ಬಹುಮಾನಗಳನ್ನು ಗೆಲ್ಲುವ ಮೂಲಕ ಫ್ಯಾಂಟಸಿ ಆಟವನ್ನು ಆಡಿ',
              'ru_desc' => 'Играйте в фэнтезийную игру, выбирая акции и выигрывайте призы',
              'id_desc' => 'Mainkan game fantasi dengan memilih saham dan menangkan hadiah',
              'tl_desc' => 'Maglaro ng pantasya sa pamamagitan ng pagpili ng mga stock at manalo ng mga premyo',
              'zh_desc' => '通過選股玩奇幻遊戲並贏取獎品',
              'status' => 0
          )
      );
      $this->db->insert_batch(SPORTS_HUB,$hub_setting);
    }
    public function down()
    {
      
    }
}