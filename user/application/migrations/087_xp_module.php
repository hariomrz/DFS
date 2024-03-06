<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Xp_module extends CI_Migration {

    public function up()
	{
		$fields = array(
			'kyc_date' => array(
				'type' => 'DATETIME',
                'null' => TRUE,
                'default' => NULL
			)
		);
		$this->dbforge->add_column(USER, $fields);

        $notification = 
            array(
            "notification_type" =>550,
            "en_subject"=>"Congratulations!",
            "hi_subject"=>"बधाई हो",
            //"tam_subject"=>"",
            "ben_subject"=>"অভিনন্দন",
            "pun_subject"=>"ਵਧਾਈ",
            "fr_subject"=>"Toutes nos félicitations",
            "guj_subject"=>"અભિનંદન",
            "th_subject"=>"ขอแสดงความยินดี",
            "message" => 'You have reached LEVEL-{{level_number}} Exciting rewards unlocked, #learn_more#',
            "en_message"=>'You have reached LEVEL-{{level_number}} Exciting rewards unlocked, #learn_more#', 
            "hi_message"=>'आप स्तर -{{level_number}} रोमांचक पुरस्कारों को अनलॉक कर चुके हैं, #learn_more#',
            "tam_message"=> 'நீங்கள் நிலை -{{level_number}} பரபரப்பான வெகுமதிகளை அடைந்துவிட்டீர்கள், #learn_more#',
            "ben_message"=>'আপনি লেভেল -{{level_number}} রোমাঞ্চকর পুরষ্কার আনলক করেছেন, #learn_more#',
            "pun_message"=>'ਤੁਸੀਂ ਲੈਵਲ -{{level_number}} ਦਿਲਚਸਪ ਇਨਾਮਾਂ ਤੇ ਤਾਲਾਬੰਦ ਹੋ ਗਏ ਹੋ, #learn_more#',
            "fr_message"=>'Vous avez atteint le niveau {{level_number}} récompenses passionnantes déverrouillées, #learn_more#',
            "guj_message"=>'તમે સ્તર -{{level_number}} આકર્ષક પુરસ્કારોને અનલૉક કર્યું છે, #learn_more#',
            "th_message"=>'คุณได้รับการปลดล็อค Rewards ที่น่าตื่นเต้นในระดับ -{{level_number}} แล้ว #learn_more#',
            "ru_subject" => "Поздравляю",
            "id_subject" => "Selamat",
            "tl_subject" => "pagbati",
            "zh_subject" => "祝贺",
            "kn_subject" => "ಅಭಿನಂದನೆಗಳು",
            "ru_message" => "Вы достигли удивительных награждений на уровне {{level_number}}, #learn_more#",
            "id_message" => "Anda telah mencapai level-{{level_number}} hadiah menarik yang tidak terkunci, #learn_more#",
            "tl_message" => "Naabot mo na ang naka-unlock na antas-{{level_number}} kapana-panabik na gantimpala, #learn_more#",
            "zh_message" => "您已达到级别 -  {{level_number}}令人兴奋的奖励解锁，#learn_more#",
            "kn_message" => "ನೀವು ಮಟ್ಟದ-{{level_number}} ಅತ್ಯಾಕರ್ಷಕ ಬಹುಮಾನಗಳನ್ನು ಅನ್ಲಾಕ್ ಮಾಡಿದ್ದೀರಿ, #learn_more#"
            //"es_message" => "{{name}} torneo se cancela por Admin"
            );

            $this->db->insert(NOTIFICATION_DESCRIPTION,$notification);   

            $this->db->insert(APP_CONFIG,array(
                'name' => "Allow XP point",
                'key_name'=>"allow_xp_point",
                'key_value' => 0,
                'custom_data'=> json_encode(array('start_date' => NULL))
            ));

            $transaction_messages = array(
                array(
                    'source' => 450,
                    'en_message' => 'Coins credited for Level-{{level_number}} promotion',
                    'fr_message' => 'Pièces créditées pour niveau- {{level_number}} Promotion',
                    'hi_message' => 'स्तर के लिए जमा सिक्के- {{level_number}} प्रचार',
                    'guj_message' => 'સિક્કા સ્તર- {{level_number}} પ્રમોશન માટે શ્રેય',
                    'ben_message' => 'কয়েন লেভেল- {{level_number}} প্রচারের জন্য ক্রেডিট',
                    'pun_message' => 'ਦੇਵਿਆਂ ਨੂੰ ਪੱਧਰ ਤੇ ਕ੍ਰੈਡਿਟ- {{level_number}} ਤਰੱਕੀ ਲਈ',
                   // 'es_message' => 'Precio de la entrada para %s',
                    'tam_message' => 'நிலைக்கு வரவு வைக்கப்படும் நாணயங்கள்- {{level_number}} பதவி உயர்வு',
                    'th_message' => 'เหรียญเครดิตสำหรับระดับ - {{level_number}} โปรโมชั่น',
                    "ru_message" => "Монеты, зачисленные на уровень - {{level_number}} Продвижение",
                    "id_message" => "Koin dikreditkan untuk level - {{level_number}} Promosi",
                    "tl_message" => "Mga barya na kredito para sa antas - {{level_number}} promosyon",
                    "zh_message" => "Coins归功于级别 -  {{level_number}}促销",
                    "kn_message" => "ನಾಣ್ಯಗಳು ಮಟ್ಟಕ್ಕೆ ಸಲ್ಲುತ್ತದೆ - {{level_number}} ಪ್ರಚಾರ"

                ),
                array(
                    'source' => 451,
                    'en_message' => 'Cashback deposit benefit for Level-{{level_number}}',
                    'fr_message' => 'Bénéfice de dépôt de remboursement pour niveau- {{level_number}}',
                    'hi_message' => 'स्तर के लिए कैशबैक जमा लाभ- {{level_number}}',
                    'guj_message' => 'સ્તર માટે કેશબૅક ડિપોઝિટ લાભ- {{level_number}}',
                    'ben_message' => 'স্তরের জন্য ক্যাশব্যাক ডিপোজিট বেনিফিট- {{level_number}}',
                    'pun_message' => 'ਲੈਵਲ- {{level_number}} ਲਈ ਕੈਸ਼ਬੈਕ ਡਿਪਾਜ਼ਿਟ ਲਾਭ',
                   // 'es_message' => 'Para Honorario del reembolso del torneo',
                    'tam_message' => 'Dashback வைப்புத்தொகை நன்மை-{{level_number}}',
                    'th_message' => 'ผลประโยชน์การฝากเงินคืนเงินสำหรับระดับ - {{level_number}}' ,
                    "ru_message" => "Пособие взимания взимания на уровень - {{level_number}}}",
                    "id_message" => "Manfaat Setoran Cashback untuk Level - {{level_number}}",
                    "tl_message" => "Cashback Deposit Benefit para sa antas - {{level_number}}",
                    "zh_message" => "水平的现金返还存款福利 -  {{level_number}}",
                    "kn_message" => "ಹಂತಕ್ಕೆ ಕ್ಯಾಶ್ಬ್ಯಾಕ್ ಠೇವಣಿ ಲಾಭ - {{level_number}}"
                ),
                
                array(
                    'source' => 452,
                    'en_message' => 'Contest Joining Cashback benefit for Level-{{level_number}}',
                    'fr_message' => 'CONCOURS REJOINDRE AVANTAGES DE CASHBACK POUR NIVEAU- {{level_number}}',
                    'hi_message' => 'स्तर के लिए कैशबैक लाभ में शामिल होना- {{level_number}}',
                    'guj_message' => 'લેવલ- {{level_number}} માટે કેશબેક લાભમાં જોડાતા હરીફાઈ',
                    'ben_message' => 'স্তরের জন্য ক্যাশব্যাক বেনিফিট যোগদান করুন- {{level_number}}',
                    'pun_message' => 'ਮੁਕਾਬਲੇ ਦੇ ਮੁਕਾਬਲੇ ਲਈ ਕੈਸ਼ਬੈਕ ਲਾਭ ਸ਼ਾਮਲ ਕਰਨਾ {{level_number}} ',
                   // 'es_message' => 'Premio ganó el concurso',
                    'tam_message' => 'போட்டியில் Cashback நன்மைக்காக இணைந்த போட்டி- {{level_number}}',
                    'th_message' => 'การประกวดที่เข้าร่วมผลประโยชน์ของเงินคืนสำหรับระดับ - {{level_number}}',
                    "ru_message" => "Соревнование присоединение к эмблерному выгоду для уровня - {{level_number}}",
                    "id_message" => "Kontes bergabung dengan manfaat cashback untuk level - {{level_number}}",
                    "tl_message" => "Paligsahan pagsali sa cashback benepisyo para sa antas - {{level_number}}",
                    "zh_message" => "比赛加入Cashback Level级 -  {{level_number}}",
                    "kn_message" => "ಸ್ಪರ್ಧೆಯ ಮಟ್ಟಕ್ಕೆ ಕ್ಯಾಶ್ಬ್ಯಾಕ್ ಪ್ರಯೋಜನವನ್ನು ಸೇರುತ್ತಿದೆ - {{level_number}}"
                )
            );
          $this->db->insert_batch(TRANSACTION_MESSAGES, $transaction_messages);

          //$this->db->dbprefix(XP_ACTIVITIES)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_ACTIVITIES)." (
            `activity_id` int(11) NOT NULL AUTO_INCREMENT,
            `activity_master_id` int(11) NOT NULL,
            `recurrent_count` tinyint(3) NOT NULL DEFAULT '0',
            `xp_point` int(11) NOT NULL DEFAULT '1',
            `xp_cap` int(11) NOT NULL DEFAULT '0',
            `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
            `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No,1=Yes',
            `added_date` datetime DEFAULT NULL,
            `modified_date` datetime DEFAULT NULL,
            PRIMARY KEY (`activity_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
	  	$this->db->query($sql);

       

           //$this->db->dbprefix(XP_ACTIVITY_MASTER)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_ACTIVITY_MASTER)." (
            `activity_master_id` int(11) NOT NULL AUTO_INCREMENT,
            `activity_title` varchar(255) DEFAULT NULL,
            `activity_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: Onetime, 2: Recurrent with count',
            `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: Inactive, 1: Active',
            PRIMARY KEY (`activity_master_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;
          ";
	  	$this->db->query($sql);

          $sql="INSERT INTO ".$this->db->dbprefix(XP_ACTIVITY_MASTER)." (`activity_master_id`, `activity_title`, `activity_type`, `status`) VALUES
          (1, 'Sign Up', 1, 1),
          (2, 'Play Cash Contest', 2, 1),
          (3, 'Play Coins Contest', 2, 1),
          (4, 'Play Free Contest', 2, 1),
          (5, 'Invite Friends', 2, 1),
          (6, 'KYC Approved', 1, 1),
          (7, 'Make 1st Deposit', 1, 1),
          (8, 'Post 1st Deposit', 2, 1),
          (9, 'Winning Zone', 2, 1);";
	  	$this->db->query($sql);


           //$this->db->dbprefix(XP_BADGE_MASTER)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_BADGE_MASTER)." (
            `badge_id` int(11) NOT NULL AUTO_INCREMENT,
            `badge_name` varchar(55) NOT NULL,
            `badge_icon` varchar(255) NOT NULL,
            PRIMARY KEY (`badge_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	  	$this->db->query($sql);

          $sql="INSERT INTO ".$this->db->dbprefix(XP_BADGE_MASTER)." (`badge_id`, `badge_name`, `badge_icon`) VALUES
          (1, 'Bronze', ''),
          (2, 'Silver', ''),
          (3, 'Gold', ''),
          (4, 'Platinum', ''),
          (5, 'Diamond', ''),
          (6, 'Elite', '');
          ";
	  	$this->db->query($sql);

 //$this->db->dbprefix(XP_LEVEL_POINTS)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_LEVEL_POINTS)." (
            `level_pt_id` int(11) NOT NULL AUTO_INCREMENT,
            `level_number` int(3) NOT NULL DEFAULT '0',
            `start_point` int(11) NOT NULL DEFAULT '0',
            `end_point` int(11) NOT NULL DEFAULT '0',
            `added_date` datetime DEFAULT NULL,
            `updated_date` datetime DEFAULT NULL,
            PRIMARY KEY (`level_pt_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);

          $sql="ALTER TABLE ".$this->db->dbprefix(XP_LEVEL_POINTS)."
          ADD UNIQUE KEY `level_number` (`level_number`);";
	  	$this->db->query($sql);

           //$this->db->dbprefix(XP_LEVEL_REWARDS)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_LEVEL_REWARDS)." (
            `reward_id` int(11) NOT NULL AUTO_INCREMENT,
            `level_number` int(3) NOT NULL DEFAULT '0',
            `badge_id` tinyint(2) NOT NULL DEFAULT '0',
            `is_coin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No,1=Yes',
            `coin_amt` int(11) NOT NULL DEFAULT '0',
            `is_cashback` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No,1=Yes',
            `cashback_amt` int(11) NOT NULL DEFAULT '0',
            `cashback_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - Bonus, 0 - Real Cash',
            `cashback_amt_cap` int(11) NOT NULL DEFAULT '0',
            `is_contest_discount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No,1=Yes',
            `discount_percent` tinyint(3) NOT NULL DEFAULT '0',
            `discount_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - Bonus, 0 - Real Cash',
            `discount_amt_cap` int(11) NOT NULL DEFAULT '0',
            `added_date` datetime DEFAULT NULL,
            `modified_date` datetime DEFAULT NULL,
            `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No,1=Yes',
            PRIMARY KEY (`reward_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);


           //$this->db->dbprefix(XP_REWARD_HISTORY)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_REWARD_HISTORY)." (
            `reward_history_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` int(9) NOT NULL,
            `reward_id` int(11) NOT NULL,
            `coins` int(11) NOT NULL,
            `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 => pending, 1=> success',
            `added_date` datetime NOT NULL,
            PRIMARY KEY (`reward_history_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);

          $sql="ALTER TABLE `vi_xp_reward_history`
          ADD UNIQUE KEY `user_reward` (`reward_id`,`user_id`);";
	  	$this->db->query($sql);

           //$this->db->dbprefix(XP_USERS)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_USERS)." (
            `xp_user_id` bigint(12) NOT NULL AUTO_INCREMENT,
            `user_id` int(9) NOT NULL,
            `point` int(9) NOT NULL,
            `level_id` int(9) DEFAULT NULL,
            `custom_data` json NOT NULL,
            `added_date` datetime DEFAULT NULL,
            `update_date` datetime DEFAULT NULL,
            PRIMARY KEY (`xp_user_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);

          $sql="ALTER TABLE ".$this->db->dbprefix(XP_USERS)."
          ADD UNIQUE KEY `user_id_unique` (`user_id`);";
	  	$this->db->query($sql);

           //$this->db->dbprefix(XP_USER_HISTORY)
          $sql="CREATE TABLE ".$this->db->dbprefix(XP_USER_HISTORY)." (
  `history_id` bigint(12) NOT NULL AUTO_INCREMENT,
  `activity_id` int(9) NOT NULL,
  `point` int(9) DEFAULT '0',
  `user_id` int(9) NOT NULL,
  `added_date` datetime DEFAULT NULL,
  PRIMARY KEY (`history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);
    }
}