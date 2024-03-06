<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dfs_tournament extends CI_Migration {

	public function up() {
        $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT)." (
            `tournament_id` bigint NOT NULL AUTO_INCREMENT,
            `tournament_unique_id` varchar(255) NOT NULL,
            `name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `sports_id` int NOT NULL,
            `league_id` varchar(100) NOT NULL,
            `entry_fee` int NOT NULL DEFAULT '0',
            `currency_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0-bonus 1-real amount 2-points',
            `prize_pool` int NOT NULL DEFAULT '0',
            `total_user_joined` int DEFAULT '0',
            `prize_detail` json DEFAULT NULL,
            `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=>open,1=>cancelled,2=>completed,3=>prize distributed',
            `image` varchar(100) DEFAULT NULL,
            `start_date` datetime NOT NULL,
            `end_date` datetime DEFAULT NULL,
            `is_tie_breaker` tinyint(1) NOT NULL DEFAULT '0',
            `match_count` int NOT NULL DEFAULT '1',
            `is_win_notify` tinyint(1) NOT NULL DEFAULT '0',
            `prize_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '	0 bonus 1 for real amount 2 for points,3=merchandise',
            `added_date` datetime NOT NULL,
            `updated_date` datetime NOT NULL,
            `max_bonus_allowed` int NOT NULL DEFAULT '0',
            `cancel_reason` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`tournament_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);
        
        $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT)."
        ADD KEY `sports_id` (`sports_id`),
        ADD KEY `league_id` (`league_id`),
        ADD KEY `status` (`status`);";
	  	$this->db->query($sql);
       
        $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_BANNER)." (
            `tournament_banner_id` int NOT NULL AUTO_INCREMENT,
            `tournament_id` int NOT NULL,
            `image` varchar(200) NOT NULL,
            PRIMARY KEY (`tournament_banner_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;";
        $this->db->query($sql);
       
        $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_BANNER)."
        ADD KEY `tournament_id` (`tournament_id`);";
	  	$this->db->query($sql);
       
       $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_COMPLETED_TEAM)." (
        `tournament_completed_team_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
        `tournament_season_id` int NOT NULL,
        `tournament_team_id` int NOT NULL,
        `user_id` int NOT NULL,
        `team_data` json NOT NULL,
        `added_date` datetime NOT NULL,
        PRIMARY KEY (`tournament_completed_team_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_COMPLETED_TEAM)."
          ADD UNIQUE KEY `tournament_season_id` (`tournament_season_id`,`tournament_team_id`,`user_id`);";
	  	$this->db->query($sql);
       
          $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_INVITE)." (
            `invite_id` int NOT NULL AUTO_INCREMENT,
            `tournament_id` int DEFAULT NULL,
            `tournament_unique_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `user_id` int NOT NULL DEFAULT '0',
            `message` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `invite_from` int DEFAULT NULL COMMENT 'include the user id of the user inviting',
            `code` varchar(100) DEFAULT '1',
            `season_type` tinyint(1) DEFAULT NULL COMMENT '1=>daily,2=>UK season long',
            `status` int DEFAULT NULL,
            `expire_date` datetime DEFAULT NULL,
            `created_date` datetime DEFAULT NULL,
            PRIMARY KEY (`invite_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	  	$this->db->query($sql);
      
          $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_INVITE)."
          ADD UNIQUE KEY `tournament_unique_id` (`tournament_id`,`email`,`user_id`,`season_type`) USING BTREE,
          ADD KEY `fk_vi_invite_2_idx` (`user_id`);";
	  	$this->db->query($sql);
      
          $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_LINEUP)." (
            `tournament_lineup_id` int NOT NULL AUTO_INCREMENT,
            `tournament_team_id` int NOT NULL,
            `master_lineup_position_id` int NOT NULL,
            `player_unique_id` varchar(100) NOT NULL,
            `player_team_id` int NOT NULL DEFAULT '0' COMMENT 'Actual player team id from player table',
            `team_league_id` int NOT NULL DEFAULT '0',
            `player_salary` float NOT NULL DEFAULT '0',
            `score` decimal(50,2) NOT NULL DEFAULT '0.00',
            `captain` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=>Captain, 2=>Vice Captain',
            `added_date` datetime DEFAULT NULL,
            PRIMARY KEY (`tournament_lineup_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
          ";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_LINEUP)."
          ADD UNIQUE KEY `tournament_team_id` (`tournament_team_id`,`player_unique_id`);";
	  	$this->db->query($sql);
       
          $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_SCORING_RULES)." (
            `tournament_scoring_rules_id` int NOT NULL AUTO_INCREMENT,
            `en_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `en_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `en_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `en_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `en_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `hi_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `hi_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `hi_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `hi_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `guj_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `guj_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `guj_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `guj_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `guj_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `hi_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `fr_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `fr_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `fr_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `fr_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `fr_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ben_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ben_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ben_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ben_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ben_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `pun_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `pun_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `pun_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `pun_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `pun_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `es_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `es_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `es_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `es_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `es_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `th_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `th_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `th_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `th_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `th_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

            `tam_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tam_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tam_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tam_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tam_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

            `id_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `id_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `id_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `id_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `id_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

            `ru_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ru_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ru_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ru_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `ru_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

            `tl_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tl_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tl_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tl_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `tl_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

            `zh_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `zh_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `zh_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `zh_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `zh_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,

           

            `kn_para1` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `kn_para2` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `kn_para3` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `kn_para4` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            `kn_note` text CHARACTER SET utf8 COLLATE utf8_general_ci,
            PRIMARY KEY (`tournament_scoring_rules_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);
       
          $sql="INSERT INTO ".$this->db->dbprefix(TOURNAMENT_SCORING_RULES)." (`tournament_scoring_rules_id`, `en_para1`, `en_para2`, `en_para3`, `en_para4`, `en_note`, `hi_para1`, `hi_para2`, `hi_para3`, `hi_para4`, `guj_para1`, `guj_para2`, `guj_para3`, `guj_para4`, `guj_note`, `hi_note`, `fr_para1`, `fr_para2`, `fr_para3`, `fr_para4`, `fr_note`, `ben_para1`, `ben_para2`, `ben_para3`, `ben_para4`, `ben_note`, `pun_para1`, `pun_para2`, `pun_para3`, `pun_para4`, `pun_note`, `es_para1`, `es_para2`, `es_para3`, `es_para4`, `es_note`, `th_para1`, `th_para2`, `th_para3`, `th_para4`, `th_note`, `tam_para1`, `tam_para2`, `tam_para3`, `tam_para4`, `tam_note`, `id_para1`, `id_para2`, `id_para3`, `id_para4`, `id_note`, `ru_para1`, `ru_para2`, `ru_para3`, `ru_para4`, `ru_note`, `tl_para2`, `tl_para1`, `tl_para3`, `tl_para4`, `tl_note`, `zh_para1`, `zh_para2`, `zh_para3`, `zh_para4`, `zh_note`, `kn_para1`, `kn_para2`, `kn_para3`, `kn_para4`, `kn_note`) VALUES
          (1, '<div class=\"text-label\">Overview</div>\r\n                                                        <p>DFS tournament gives the same thrill as Daily Fantasy but in a tournament form, which runs for multiple days and consists of multiple fixtures from a single league. Simply join the tournament, create your lineup for each fixture and get ahead in the tournament and compare yourself to others on the leaderboard. If you top the leaderboards, you stand a chance to win the prizes.</p>', '<div class=\"text-label\">Joining Tournament</div>\r\n                                                        <p>The tournament entry fee will be paid once when the first team of the tournament is created and submitted.</p>\r\n<p>A single team will be submitted for each fixture in the tournament.</p>', '<div class=\"text-label\">  Scoring System</div>\r\n                                                                                                                                                                \r\n<p>The scoring rules and the points system will be the same as the  Daily Fantasy and is applicable based on the sport format.</p> \r\n', NULL, NULL, '<div class=\"text-label\">अवलोकन</div>\r\n                                                        <p>डीएफएस टूर्नामेंट दैनिक काल्पनिक रूप में, लेकिन एक टूर्नामेंट रूप है, जो कई दिन तक चलता है और एक ही लीग से कई जुड़नार के होते हैं में वही रोमांच देता है। सीधे शब्दों में, टूर्नामेंट में शामिल होने के लिए प्रत्येक स्थिरता के लिए अपनी लाइनअप बना सकते हैं और टूर्नामेंट में आगे लाने के लिए और अपने आप को लीडरबोर्ड पर दूसरों के लिए की तुलना करें। आप लीडरबोर्ड ऊपर हैं, तो आप पुरस्कार जीतने का एक मौका खड़े हैं।</p>', '<div class=\"text-label\">टूर्नामेंट में शामिल होने से</div>\r\n                                                        <p>टूर्नामेंट प्रवेश शुल्क एक बार जब टूर्नामेंट के पहले टीम बनाई गई है और प्रस्तुत भुगतान किया जाएगा।</p>\r\n<p>एक एकल टीम टूर्नामेंट में प्रत्येक स्थिरता के लिए प्रस्तुत किया जाएगा।</p>', '<div class=\"text-label\"> स्कोरिंग प्रणाली</div>\r\n                                                                                                                                                                \r\n<p>स्कोरिंग नियमों और अंक प्रणाली दैनिक काल्पनिक रूप में एक ही हो सकता है और खेल प्रारूप के आधार पर लागू होता है जाएगा।</p> ', NULL, '<div class=\"text-label\">ઝાંખી</div>\r\n                                                        <p>ડીએફએસ ટુર્નામેન્ટ દૈનિક ફૅન્ટેસી, પરંતુ એક ટુર્નામેન્ટ ફોર્મ, જે બહુવિધ દિવસ માટે ચાલે છે અને એક લીગમાંથી બહુવિધ ફિક્સર સમાવે જ રોમાંચ આપે છે. ફક્ત ટુર્નામેન્ટ જોડાવા દરેક મેચ માટે તમારા લાઇનઅપ બનાવવા અને ટુર્નામેન્ટમાં આગળ વિચાર અને પોતાને લીડરબોર્ડ પર અન્ય લોકો માટે સરખામણી કરો. તમે લીડરબોર્ડ્સ ટોચ, તો તમે ઇનામ જીતવા માટે તક ઊભી છે.</p>', '<div class=\"text-label\">ટુર્નામેન્ટ જોડાયા</div>\r\n                                                        <p>ટુર્નામેન્ટમાં પ્રવેશ ફી એકવાર જ્યારે ટુર્નામેન્ટના પ્રથમ ટીમ બનાવવામાં આવે છે અને સબમિટ ચૂકવવામાં આવશે.</p>\r\n<p>એક જ ટીમ ટુર્નામેન્ટમાં દરેક મેચ માટે સબમિટ કરવામાં આવશે.</p>', '<div class=\"text-label\"> સ્કોરિંગ સિસ્ટમ</div>\r\n                                                                                                                                                                \r\n<p>સ્કોરિંગ નિયમો અને પોઈન્ટ સિસ્ટમ દૈનિક ફૅન્ટેસી જેમ જ હોઈ શકે છે અને રમતગમત ફોર્મેટ પર આધારિત લાગુ પડે છે કરશે.</p> ', NULL, NULL, NULL, '<div class=\"text-label\">Aperçu</div>\r\n                                                        <p>DFS tournoi donne le même frisson que Daily fantaisie, mais sous une forme de tournoi, qui se déroule pendant plusieurs jours et se compose de plusieurs appareils à partir d\'une seule ligue. rejoindre tout simplement le tournoi, créez votre gamme pour chaque appareil et aller de l\'avant dans le tournoi et vous par rapport aux autres sur le leaderboard. Si vous le haut du classement, vous avez une chance de gagner des prix.</p>', '<div class=\"text-label\">Tournoi rejoindre</div>\r\n                                                        <p>Les frais d\'inscription du tournoi sera versée une fois quand la première équipe du tournoi est créé et soumis.</p>\r\n<p>Une seule équipe sera soumise pour chaque appareil dans le tournoi.</p>', '<div class=\"text-label\"> système de notation</div>\r\n                                                                                                                                                                \r\n<p>Les règles de notation et le système de points sera le même que Fantasy Daily et est applicable en fonction du format du sport.</p> ', NULL, NULL, '<div class=\"text-label\">সংক্ষিপ্ত বিবরণ</div>\r\n                                                        <p>DFS টুর্নামেন্ট দৈনিক ফ্যান্টাসি হিসেবে নয় বরং একটি টুর্নামেন্ট ফর্ম, যা একাধিক দিনের জন্য সঞ্চালিত হয় ও একটি একক লীগ থেকে একাধিক রাজধানী নিয়ে গঠিত একই রোমাঁচিত দেয়। সহজভাবে, টুর্নামেন্ট যোগদানের প্রতিটি চোকান জন্য আপনার লাইন আপ তৈরি করুন এবং টুর্নামেন্ট এগিয়ে পেতে এবং নিজেকে লিডারবোর্ডে অন্যদের তুলনা করুন। আপনি লিডারবোর্ড শীর্ষে থাকে, তাহলে আপনি পুরস্কার জয় করার একটি সুযোগ দাঁড়ানো।</p>', '<div class=\"text-label\">টুর্নামেন্ট যোগদান</div>\r\n                                                        <p>টুর্নামেন্ট প্রবেশমূল্য একবার যখন টুর্নামেন্টের প্রথম দল তৈরি করা হয় এবং জমা দেওয়া প্রদান করা হবে।.</p>\r\n<p>একটি একক দল টুর্নামেন্ট প্রতিটি চোকান জন্য জমা দেওয়া হবে।</p>', '<div class=\"text-label\"> স্কোরিং সিস্টেম</div>\r\n                                                                                                                                                                \r\n<p>স্কোরিং নিয়ম এবং পয়েন্ট সিস্টেম দৈনিক ফ্যান্টাসি হিসাবে একই হতে হবে এবং খেলাধুলা বিন্যাস উপর ভিত্তি করে প্রযোজ্য হবে।</p> ', NULL, NULL, '<div class=\"text-label\">ਅਵਲੋਕਨ</div>\r\n                                                        <p>ਡੀਐਫਐਸ ਮੁਕਾਬਲੇ ਰੋਜ਼ਾਨਾ ਕਲਪਨਾ ਦੇ ਤੌਰ ਤੇ, ਪਰ ਇੱਕ ਮੁਕਾਬਲੇ ਦਾ ਰੂਪ ਹੈ, ਜੋ ਕਿ ਕਈ ਕਈ ਦਿਨ ਲਈ ਚੱਲਦਾ ਹੈ ਅਤੇ ਇੱਕ ਸਿੰਗਲ ਲੀਗ ਕਈ ਪ੍ਰੋਗਰਾਮ ਦੇ ਸ਼ਾਮਲ ਹਨ ਵਿੱਚ ਇੱਕੋ ਹੀ ਖ਼ੁਸ਼ੀ ਦਿੰਦਾ ਹੈ. ਬਸ, ਮੁਕਾਬਲੇ ਵਿੱਚ ਸ਼ਾਮਲ ਹਰ ਇੱਕ ਮੈਚ ਲਈ ਆਪਣੇ ਲਾਈਨਅੱਪ ਬਣਾਉਣ ਅਤੇ ਮੁਕਾਬਲੇ ਵਿਚ ਅੱਗੇ ਪ੍ਰਾਪਤ ਕਰੋ ਅਤੇ ਆਪਣੇ ਆਪ ਨੂੰ ਲੀਡਰਬੋਰਡ \'ਤੇ ਹੋਰ ਦੀ ਤੁਲਨਾ ਕਰੋ. ਤੁਹਾਨੂੰ ਲੀਡਰਬੋਰਡਸ ਚੋਟੀ ਹੋ, ਤੁਹਾਨੂੰ ਇਨਾਮ ਜਿੱਤਣ ਦਾ ਮੌਕਾ ਖੜ੍ਹੇ.</p>', '<div class=\"text-label\">ਪ੍ਰਤੀਯੋਗਤਾ ਵਿੱਚ ਸ਼ਾਮਿਲ ਹੋਵੋ</div>\r\n                                                        <p>ਮੁਕਾਬਲੇ ਇੰਦਰਾਜ਼ ਫੀਸ ਇਕ ਵਾਰ ਜਦ ਮੁਕਾਬਲੇ ਦੇ ਪਹਿਲੇ ਟੀਮ ਨੂੰ ਬਣਾਇਆ ਗਿਆ ਹੈ ਅਤੇ ਪੇਸ਼ ਦਾ ਭੁਗਤਾਨ ਕੀਤਾ ਜਾਵੇਗਾ.</p>\r\n<p>ਇੱਕ ਇੱਕਲੇ ਦੀ ਟੀਮ ਮੁਕਾਬਲੇ ਵਿਚ ਹਰ ਮੈਚ ਲਈ ਪੇਸ਼ ਕੀਤਾ ਜਾਵੇਗਾ.</p>', '<div class=\"text-label\"> ਸਕੋਰਿੰਗ ਸਿਸਟਮ</div>\r\n                                                                                                                                                                \r\n<p>ਸਕੋਰਿੰਗ ਨਿਯਮ ਅਤੇ ਅੰਕ ਸਿਸਟਮ ਰੋਜ਼ਾਨਾ ਕਲਪਨਾ ਦੇ ਤੌਰ ਤੇ ਹੀ ਹੋ ਸਕਦਾ ਹੈ ਅਤੇ ਖੇਡ ਫਾਰਮੈਟ \'ਤੇ ਅਧਾਰਿਤ ਲਾਗੂ ਹੁੰਦਾ ਹੈ ਜਾਵੇਗਾ.</p> ', NULL, NULL, '<div class=\"text-label\">Descripción general</div>\r\n                                                        <p>torneo DFS da la misma emoción como Daily Fantasy pero en una forma torneo, que tiene una duración de varios días y se compone de varios aparatos a partir de una sola liga. Simplemente participar en el torneo, crear su alineación para cada aparato y salir adelante en el torneo y compare con los demás en la clasificación. Si recarga las tablas de clasificación, que tienen una oportunidad de ganar los premios.</p>', '<div class=\"text-label\">Torneo de unirse</div>\r\n                                                        <p>La cuota de entrada al torneo será pagado una vez cuando se crea el primer equipo del torneo y entregados.</p>\r\n<p>Un equipo solo se presentará para cada dispositivo en el torneo.</p>', '<div class=\"text-label\"> Sistema de puntuación</div>\r\n                                                                                                                                                                \r\n<p>Las reglas de puntuación y el sistema de puntos será el mismo que el diario de la fantasía y es aplicable en función del formato deporte.</p> ', NULL, NULL, '<div class=\"text-label\">ภาพรวม</div>\r\n                                                        <p>ทัวร์นาเมนต์ DFS ให้ตื่นเต้นเช่นเดียวกับเดลี่แฟนตาซี แต่ในรูปแบบการแข่งขันซึ่งจะทำงานหลายวันและประกอบด้วยการแข่งขันหลายรายการจากลีกเดียว เพียงแค่เข้าร่วมการแข่งขันการสร้างผู้เล่นตัวจริงของคุณสำหรับแต่ละติดตั้งและก้าวไปข้างหน้าในการแข่งขันและเปรียบเทียบตัวเองกับคนอื่น ๆ บนลีดเดอร์บอร์ด หากคุณด้านบนลีดเดอร์บอร์ดที่คุณจะมีโอกาสที่จะชนะรางวัลที่</p>', '<div class=\"text-label\">เข้าร่วมการแข่งขัน</div>\r\n                                                        <p>ค่าธรรมเนียมแรกเข้าแข่งขันจะได้รับเงินครั้งเดียวเมื่อเป็นทีมแรกของทัวร์นาเมนต์ถูกสร้างขึ้นและส่ง</p>\r\n<p>ทีมเดียวที่จะถูกส่งไปสำหรับแต่ละอุปกรณ์ติดตั้งในการแข่งขัน</p>', '<div class=\"text-label\"> ระบบการให้คะแนน</div>\r\n                                                                                                                                                                \r\n<p>กฎการให้คะแนนและระบบการให้คะแนนจะเป็นเช่นเดียวกับเดลี่แฟนตาซีและมีผลบังคับใช้ขึ้นอยู่กับรูปแบบการเล่นกีฬา</p> ', NULL, NULL, '<div class=\"text-label\">கண்ணோட்டம்</div>\r\n                                                        <p>உண்மை கண்டறியும் போட்டியில் டெய்லி பேண்டஸி ஆனால் பல நாட்கள் இயங்குகின்றது மற்றும் ஒற்றைச் லீக் இருந்து பல பொருத்தப்பட்ட ஆகியவற்றை உள்ளடக்கிய ஒரு போட்டியில் வடிவம், அதே சுகமே கொடுக்கிறது. வெறுமனே அந்த போட்டித்தொடரில் சேர ஒவ்வொரு அங்கமாகி உங்கள் வரிசையில் உருவாக்க போட்டித்தொடரின் முன்னோக்கி எடுத்து லீடர்போர்டில் மற்றவர்கள் உங்களை ஒப்பிட்டு. நீங்கள் முன்னிலைப் பட்டியல்கள் மேல் என்றால், நீங்கள் பரிசுகளை வெல்வதற்கான ஒரு வாய்ப்பு நிற்க.</p>', '<div class=\"text-label\">போட்டி சேர்வது</div>\r\n                                                        <p>போட்டியில் நுழைவு கட்டணம் போட்டிகளின் முதல் அணி உருவாக்கப்பட்ட மற்றும் சமர்ப்பிக்கப்படும் போது ஒருமுறை வழங்கப்படும்.</p>\r\n<p>ஒரு ஒற்றை டீம் டோர்னமண்ட்டில் ஒவ்வொரு அங்கமாகி சமர்ப்பிக்கப்படும்.</p>', '<div class=\"text-label\">  ஸ்கோரிங் அமைப்பு</div>\r\n                                                                                                                                                                \r\n<p>கோல் விதிகள் மற்றும் புள்ளிகள் அமைப்பு டெய்லி பேண்டஸி அதே இருக்க மற்றும் விளையாட்டு ஃபார்மட் அடிப்படையிலானது பொருந்தும் வேண்டும்.</p> ', NULL, NULL, '<div class=\"text-label\">Gambaran</div>\r\n                                                        <p>DFS turnamen memberikan sensasi yang sama seperti Harian Fantasy tetapi dalam bentuk turnamen, yang berlangsung selama beberapa hari dan terdiri dari beberapa perlengkapan dari liga tunggal. Cukup bergabung turnamen, membuat lineup Anda untuk setiap perlengkapan dan maju dalam turnamen dan membandingkan diri sendiri dengan orang lain di leaderboard. Jika Anda atas leaderboards, Anda memiliki kesempatan untuk memenangkan hadiah.</p>', '<div class=\"text-label\">bergabung Turnamen</div>\r\n                                                        <p>Biaya masuk turnamen akan dibayar sekali ketika tim pertama turnamen dibuat dan disampaikan.</p>\r\n<p>Sebuah tim tunggal akan diajukan untuk setiap fixture di turnamen.</p>', '<div class=\"text-label\">  Sistem penilaian</div>\r\n                                                                                                                                                                \r\n<p>Aturan scoring dan sistem poin akan sama dengan Harian Fantasy dan berlaku berdasarkan format olahraga.</p> ', NULL, NULL, '<div class=\"text-label\">обзор</div>\r\n                                                        <p>турнир ДФС дает тот же кайф, как Daily Фантазия, но в форме турнира, который проходит в течение нескольких дней и состоит из нескольких светильников из одной лиги. Просто присоединиться турнир, создать свой модельный ряд для каждого прибора и получить вперед в турнире и сравнивать себя с другими на лидеров. Если Вы возглавляете на лидер, у вас есть шанс выиграть призы.</p>', '<div class=\"text-label\">Присоединение Tournament</div>\r\n                                                        <p>Вступительный взнос турнира будет выплачиваться один раз, когда создается первая команда турнира и представила.</p>\r\n<p>Одна команда будет представлена ​​для каждого прибора в турнире.</p>', '<div class=\"text-label\">  Система баллов</div>\r\n                                                                                                                                                                \r\n<p>Правила озвучивания и система начисления очков будет такой же, как Daily Фантазии и применяется на основе спортивного формата.</p> ', NULL, NULL, '<div class=\"text-label\">Ang pagsali Tournament</div>\r\n                                                        <p>Ang tournament entry fee ay babayaran nang isang beses kapag ang unang team sa tournament ay nilikha at na isinumite.</p>\r\n<p>Ang nag-iisang team ay isusumite para sa bawat kabit sa paligsahan.</p>', '<div class=\"text-label\">Pangkalahatang-ideya</div>\r\n                                                        <p>DFS tournament ay nagbibigay ng parehong kiligin bilang Daily Fantasy ngunit sa isang tournament form, na kung saan tumatakbo para sa maramihang mga araw at binubuo ng maramihang mga fixtures mula sa isang solong league. Kailangan lang sumali sa tournament, lumikha ng iyong lineup para sa bawat kabit at makakuha ng maaga sa tournament at ihambing ang iyong sarili sa iba sa leaderboard. Kung itaas mo ang mga leaderboard, tumayo ka ng isang pagkakataon upang manalo ng mga premyo.</p>', '<div class=\"text-label\">  pagmamarka System</div>\r\n                                                                                                                                                                \r\n<p>Ang scoring patakaran at ang mga puntos na sistema ay magiging kapareho ng Daily Fantasy at ito ay naaangkop batay sa format sport.</p> ', NULL, NULL, '<div class=\"text-label\">概述</div>\r\n                                                        <p>DFS比赛给出了同样的快感作为日常的幻想，但在比赛的形式，它运行多天，由从单一的联赛多个灯具的。只要参加比赛，创建你的阵容每台灯和在比赛中获得成功和自己比作别人在排行榜上。如果顶部的排行榜，你将有机会赢取奖品。</p>', '<div class=\"text-label\">加入比赛</div>\r\n                                                        <p>本次比赛的报名费将支付被创建并提交比赛的一线队时一次。</p>\r\n<p>一个团队将提交在比赛中每个灯具。</p>', '<div class=\"text-label\">  评分系统</div>\r\n                                                                                                                                                                \r\n<p>计分规则和积分制度将是一样的每日幻想和基于运动形式是适用的。</p> ', NULL, NULL, '<div class=\"text-label\">ಅವಲೋಕನ</div>\r\n                                                        <p>DFS ನ ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ಡೈಲಿ ಫ್ಯಾಂಟಸಿ ಆದರೆ ಬಹು ದಿನಗಳ ಕಾಲ ನಡೆಯುತ್ತದೆ ಮತ್ತು ಏಕೈಕ ಲೀಗ್ನಲ್ಲಿ ಬಹು ಪಂದ್ಯಗಳ ಒಳಗೊಂಡ ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ರೂಪ, ಅದೇ ಥ್ರಿಲ್ ನೀಡುತ್ತದೆ. ಸರಳವಾಗಿ, ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ಸೇರಲು ಪ್ರತಿಯೊಂದು ಪಂದ್ಯವು ನಿಮ್ಮ ತಂಡವು ರಚಿಸಲು ಮತ್ತು ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ಮುಂದೆ ಪಡೆಯಲು ಮತ್ತು ಲೀಡರ್ ಇತರರಿಗೆ ನಿಮ್ಮ ಹೋಲಿಸಿ. ನೀವು ನಾಯಕ ಮೇಲಕ್ಕೆ, ನೀವು ಬಹುಮಾನಗಳನ್ನು ಗೆಲ್ಲಲು ನಿಲ್ಲುವುದು</p>', '<div class=\"text-label\">ಟೂರ್ನಮೆಂಟ್ ಸೇರುವ</div>\r\n                                                        <p>ಪಂದ್ಯಾವಳಿಯಲ್ಲಿ ಪ್ರವೇಶ ಶುಲ್ಕ ಪಂದ್ಯಾವಳಿಯ ಮೊದಲ ತಂಡ ರಚಿಸಿದ ಮತ್ತು ಸಲ್ಲಿಸಿದಾಗ ಒಮ್ಮೆ ಪಾವತಿಸಲಾಗುವುದು.</p>\r\n<p>ಒಂದು ತಂಡ ಪಂದ್ಯಾವಳಿ ಪ್ರತಿ ಪಂದ್ಯವು ಸಲ್ಲಿಸಲಾಗುತ್ತದೆ.</p>', '<div class=\"text-label\">  ಅಂಕಗಳಿಸುವ ವಿಧಾನ</div>\r\n                                                                                                                                                                \r\n<p>ಗಳಿಕೆಯ ನಿಯಮಗಳನ್ನು ಮತ್ತು ಅಂಕಗಳನ್ನು ವ್ಯವಸ್ಥೆಯ ಡೈಲಿ ಫ್ಯಾಂಟಸಿ ಅದೇ ಮತ್ತು ಕ್ರೀಡಾ ಸ್ವರೂಪವನ್ನು ಆಧರಿಸಿದೆ ಅನ್ವಯವಾಗುತ್ತದೆ ಕಾಣಿಸುತ್ತದೆ.</p> ', NULL, NULL);";
	  	$this->db->query($sql);
       
          $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_SEASON)." (
            `tournament_season_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `tournament_id` int NOT NULL,
            `season_game_uid` varchar(150) DEFAULT NULL,
            `season_scheduled_date` datetime NOT NULL,
            `is_lineup_processed` tinyint(1) NOT NULL DEFAULT '0',
            `added_date` datetime DEFAULT NULL,
            PRIMARY KEY (`tournament_season_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_SEASON)."
          ADD KEY `pickem_id` (`tournament_id`),
          ADD KEY `season_game_uid` (`season_game_uid`),
          ADD KEY `season_scheduled_date` (`season_scheduled_date`);
        ";
	  	$this->db->query($sql);
      
          $sql="CREATE TABLE ".$this->db->dbprefix(TOURNAMENT_TEAM)." (
            `tournament_team_id` int NOT NULL AUTO_INCREMENT,
            `tournament_season_id` int NOT NULL,
            `user_id` int NOT NULL,
            `user_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `team_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
            `team_short_name` varchar(100) DEFAULT NULL,
            `team_data` json DEFAULT NULL,
            `added_date` datetime DEFAULT NULL,
            `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`tournament_team_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(TOURNAMENT_TEAM)."
          ADD KEY `user_id` (`user_id`),
          ADD KEY `tournament_season_id` (`tournament_season_id`),
          ADD KEY `team_name` (`team_name`);";
	  	$this->db->query($sql);
      
          $sql="CREATE TABLE ".$this->db->dbprefix(USER_TOURNAMENT)." (
            `user_tournament_id` int NOT NULL AUTO_INCREMENT,
            `tournament_id` int DEFAULT NULL,
            `user_id` int NOT NULL,
            `user_name` varchar(100) DEFAULT NULL,
            `total_score` decimal(10,2) DEFAULT '0.00',
            `game_rank` int NOT NULL DEFAULT '0',
            `fee_refund` tinyint(1) NOT NULL DEFAULT '0',
            `is_winner` tinyint(1) NOT NULL DEFAULT '0',
            `prize_data` json DEFAULT NULL,
            `added_date` datetime DEFAULT NULL,
            `updated_date` datetime DEFAULT NULL,
            PRIMARY KEY (`user_tournament_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(USER_TOURNAMENT)."
          ADD UNIQUE KEY `user_unique_pickem` (`tournament_id`,`user_id`),
          ADD KEY `tournament_id` (`tournament_id`),
          ADD KEY `user_id` (`user_id`);";
	  	$this->db->query($sql);
      
          $sql="CREATE TABLE ".$this->db->dbprefix(USER_TOURNAMENT_SEASON)." (
            `user_tournament_season_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
            `tournament_season_id` int UNSIGNED NOT NULL,
            `tournament_team_id` int UNSIGNED NOT NULL,
            `score` decimal(10,2) DEFAULT '0.00',
            `match_rank` int DEFAULT NULL,
            `added_date` datetime DEFAULT NULL,
            `updated_date` datetime DEFAULT NULL,
            PRIMARY KEY (`user_tournament_season_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;";
	  	$this->db->query($sql);
       
          $sql="ALTER TABLE ".$this->db->dbprefix(USER_TOURNAMENT_SEASON)."
          ADD UNIQUE KEY `user_id_tournament_season_id` (`tournament_season_id`,`tournament_team_id`) USING BTREE,
          ADD KEY `ts_lineup_master_id` (`tournament_team_id`);";
	  	$this->db->query($sql);

	}

	public function down() {
		// $this->dbforge->drop_column(CONTEST, 'is_invoice_sent');
	}

}