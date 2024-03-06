MYSQL => 

CREATE TABLE `vi_module_setting` (
  `module_setting_id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `display_label` varchar(200) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `vi_module_setting` (`module_setting_id`, `name`, `display_label`, `status`) VALUES
(1,	'allow_coin',	'coins',	1);

DROP TABLE IF EXISTS `vi_submodule_setting`;
CREATE TABLE `vi_submodule_setting` (
  `submodule_setting_id` int(9) NOT NULL AUTO_INCREMENT,
  `module_setting_id` int(9) DEFAULT NULL,
  `submodule_key` varchar(200) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` varchar(300) DEFAULT NULL,
  `daily_coins_data` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`submodule_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `vi_submodule_setting` (`submodule_setting_id`, `module_setting_id`, `submodule_key`, `name`, `description`, `daily_coins_data`, `status`) VALUES
(3,	1,	'feedback',	'Feedback',	'Lorem Ipsum is simply dummy text of the printing and typesetting industry. ',	NULL,	1),
(4,	1,	'daily_streak_bonus',	'daily Streak Bonus',	'Lorem Ipsum is simply dummy text of the printing and typesetting industry. ',	'[\"10\", \"15\", \"20\", \"30\", \"140\"]',	1);

INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`, `en_message`, `hi_message`, `guj_message`) VALUES
(NULL,	138,	'You have recieved {{amount}} coins for daily checkin Day {{day_number}}',	'You have recieved {{amount}} coins for daily checkin Day {{day_number}}',	'You have recieved {{amount}} coins for daily checkin Day {{day_number}}',	'You have recieved {{amount}} coins for daily checkin Day {{day_number}}'),
(NULL,	139,	'You have recieved {{amount}} bonus for redeem coins',	'You have recieved {{amount}} bonus for redeem coins',	'You have recieved {{amount}} bonus for redeem coins',	'You have recieved {{amount}} bonus for redeem coins'),
(NULL,	140,	'You have recieved {{amount}} real for redeem coins',	'You have recieved {{amount}} real for redeem coins',	'You have recieved {{amount}} real for redeem coins',	'You have recieved {{amount}} real for redeem coins'),
(NULL,	141,	'{{amount}} coins deducted for redeem rewards',	'{{amount}} coins deducted for redeem rewards',	'{{amount}} coins deducted for redeem rewards',	'{{amount}} coins deducted for redeem rewards'),
(NULL,	142,	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification'),
(NULL,	143,	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification'),
(NULL,	144,	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification'),
(NULL,	145,	'You have received {{amount}} bonus for bank verification by your friend',	'You have received {{amount}} bonus for bank verification by your friend',	'You have received {{amount}} bonus for bank verification by your friend',	'You have received {{amount}} bonus for bank verification by your friend'),
(NULL,	146,	'You have received {{amount}} real for bank verification by your friend',	'You have received {{amount}} real for bank verification by your friend',	'You have received {{amount}} real for bank verification by your friend',	'You have received {{amount}} real for bank verification by your friend'),
(NULL,	147,	'You have received {{amount}} coins for bank verification by your friend',	'yYou have received {{amount}} coins for bank verification by your friend',	'You have received {{amount}} coins for bank verification by your friend',	'You have received {{amount}} coins for bank verification by your friend'),
(NULL,	148,	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification',	'You have received {{amount}} bonus for bank verification'),
(NULL,	149,	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification',	'You have received {{amount}} real cash for bank verification'),
(NULL,	150,	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification',	'You have received {{amount}} coins for bank verification');

ALTER TABLE `vi_contest` ADD `consolation_prize` JSON NULL DEFAULT NULL COMMENT '{\"prize_type\":\"1\",\"value\":\"10\"} , prize_type => 0 bonus 2 for coins,' AFTER `cancel_reason`;
ALTER TABLE `vi_contest_template` ADD `consolation_prize` JSON NULL DEFAULT NULL COMMENT ' {\"prize_type\":\"1\",\"value\":\"10\"} , prize_type => 0 bonus 2 for coins,' AFTER `modified_date`;


UPDATE `vi_submodule_setting` SET
`submodule_setting_id` = '4',
`module_setting_id` = '1',
`submodule_key` = 'daily_streak_bonus',
`name` = 'daily Streak Bonus',
`description` = 'Setup rewards to your user for daily check-in to the app.',
`daily_coins_data` = '[\"10\", \"15\", \"20\", \"30\", \"34\"]',
`status` = '1'
WHERE `submodule_setting_id` = '4';

UPDATE `vi_submodule_setting` SET
`submodule_setting_id` = '3',
`module_setting_id` = '1',
`submodule_key` = 'feedback',
`name` = 'Feedback',
`description` = 'Encourage your users by rewarding them coins for providing their valuable feedback.',
`daily_coins_data` = NULL,
`status` = '1'
WHERE `submodule_setting_id` = '3';