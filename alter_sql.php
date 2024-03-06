<?php die;?>
=============== 10-09-2019 ==============
DELETE FROM `vi_banner_management` WHERE `banner_type_id` IN (2,3,5);

INSERT INTO `vi_banner_type` (`banner_type_id`, `banner_type`, `status`) VALUES (5, 'Signup', '1');

INSERT INTO `vi_banner_management` (`banner_id`, `banner_unique_id`, `banner_type_id`, `name`, `target_url`, `image`, `collection_master_id`, `status`, `is_deleted`, `created_date`) VALUES (NULL, 'b5gJXr2GM', '2', 'Refer a Friend', '', '', '0', '1', '0', '2019-07-08 01:26:23'), (NULL, '76gJRr2b5', '3', 'Deposit', '', '', '0', '1', '0', '2019-07-08 01:26:23'),(NULL, 'b5gJRr2mg', '5', 'Signup', '', '', '0', '1', '0', '2019-07-08 01:26:23');

ALTER TABLE `vi_cd_recent_communication` CHANGE `userbase` `userbase` TINYINT(1) NULL DEFAULT '1' COMMENT '1=>All User,2=> Login, 3=> Signup,4=>by fixture participation';

============== 18-07-2019 RAIN DELAY and Playing XI ===========
ALTER TABLE `vi_season` ADD `delay_minute` INT(11) NOT NULL DEFAULT '0' AFTER `twitter_hashtags`, ADD `delay_message` VARCHAR(255) NULL DEFAULT NULL AFTER `delay_minute`, ADD `custom_message` TEXT NULL DEFAULT NULL AFTER `delay_message`;

ALTER TABLE `vi_season` ADD `playing_announce` TINYINT(1) NOT NULL DEFAULT '0' AFTER `twitter_hashtags`, ADD `playing_list` JSON NULL DEFAULT NULL AFTER `playing_announce`;


INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`) VALUES
(106, 131, ' It’s started to rain, and the {{home}} vs {{away}} match on {{season_scheduled_date}} has been delayed by {{MINUTES}} mins. You can edit your teams till the match starts.'),
(107, 132, ' The toss has happened for the {{home}} vs {{away}} match, and the squads have been announced. You can edit your lineup till the match starts. Game on!');

--

============ 24-07-2019 ===============
ALTER TABLE `vi_game_statistics_kabaddi` CHANGE `week` `week` INT(11) NOT NULL DEFAULT '0';


============ 25-07-2019 =============
ALTER TABLE `vi_collection_master` ADD `delay_minute` INT(9) NOT NULL DEFAULT '0' AFTER `is_lineup_processed`;

============ 30-07-2019 =============
ALTER TABLE `vi_team` ADD `display_team_abbr` VARCHAR(15) NULL DEFAULT NULL AFTER `team_name`, ADD `display_team_name` VARCHAR(255) NULL DEFAULT NULL AFTER `display_team_abbr`;

UPDATE `vi_cd_email_template` SET `message_body` = 'Fun Competition, Easy Winnings. Join {{contest_name}} contest for the {{collection_name}} match. Play Now, visit {{FRONTEND_BITLY_URL}}' WHERE `vi_cd_email_template`.`cd_email_template_id` = 2;


UPDATE `vi_notification_description` SET `message` = 'Game {{contest_name}} for the fixture {{collection_name}} joined successfully.' WHERE `notification_type` = 1;

UPDATE `vi_notification_description` SET `message` = 'Game {{contest_name}} for the fixture {{collection_name}} has been cancelled due to insufficient Participation.' WHERE `notification_type` = 2;

UPDATE `vi_notification_description` SET `message` = '{{contest_name}} game for the fixture {{collection_name}} is over. Click/tap here to see how it went!' WHERE `notification_type` = 3;

UPDATE `vi_notification_description` SET `message` = 'Congratulations! You\'re a winner in the {{collection_name}} match.' WHERE notification_type = 3;

================= 05-08-2019 ================
ALTER TABLE `vi_lineup_master_contest` ADD `user_name` VARCHAR(255) NULL DEFAULT NULL AFTER `won_amount`;

ALTER TABLE `vi_lineup_master` ADD `user_name` VARCHAR(255) NULL DEFAULT NULL AFTER `user_id`;

ALTER TABLE `vi_lineup_master_contest` DROP `user_name`;


ALTER TABLE `vi_order` ADD `old_source_id` INT(11) NOT NULL DEFAULT '0' AFTER `prize_image`;

update `vi_order` set old_source_id = source_id

#this query run once you change old order id and date
update `vi_order` set source_id=0 WHERE `order_id` < '35973' AND `source` IN (1,2,3)

ALTER TABLE `vi_season` ADD `notify_player_announce` TINYINT(1) NOT NULL DEFAULT '0' AFTER `playing_announce`;
================ 12-08-2019 =================
INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`) VALUES (NULL, '133', 'Signup OTP');

================ 13-08-2019 WHY US =================
INSERT INTO `vi_banner_type` (`banner_type_id`, `banner_type`, `status`) VALUES (6, 'Why US', '1');

ALTER TABLE `vi_master_sports_format` ADD `en_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `display_name`, ADD `hi_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `en_display_name`;

ALTER TABLE `vi_master_sports_format` ADD `ar_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `hi_display_name`;

INSERT INTO `vi_banner_management` (`banner_id`, `banner_unique_id`, `banner_type_id`, `name`, `target_url`, `image`, `collection_master_id`, `status`, `is_deleted`, `created_date`) VALUES (NULL, 'b5gJXr2GM', '2', 'Refer a Friend', '', '', '0', '1', '0', '2019-07-08 01:26:23'), (NULL, '76gJRr2b5', '3', 'Deposit', '', '', '0', '1', '0', '2019-07-08 01:26:23'),(NULL, 'b5gJRr2mg', '5', 'Signup', '', '', '0', '1', '0', '2019-07-08 01:26:23');


<!-- 17 - Sep - 2019 -->
ALTER TABLE `vi_order` CHANGE `plateform` `plateform` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1-Fantasy, 2-TeenPatti, 3-Poker etc';

================ 26-09-2019 =============
ALTER TABLE `vi_user` ADD `total_withdrawal` FLOAT NOT NULL DEFAULT '0' AFTER `total_deposit`;

ALTER TABLE `vi_user_bank_detail` ADD `bank_document` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `branch_name`;
ALTER TABLE `vi_user` DROP `bank_document`;

==============04-10-2019============
ALTER TABLE `vi_game_statistics_cricket`   DROP `home_team_score`,   DROP `away_team_score`,   DROP `home_overs`,   DROP `away_overs`,   DROP `home_wickets`,
  DROP `away_wickets`;

  INSERT INTO `vi_master_scoring_rules` (`master_scoring_category_id`, `format`, `score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`) VALUES
(14,	1,	'Run-out (thrower)',	4,	0,	'RUN_OUT_THROWER',	''),
(14,	1,	'Run-out (catcher)',	2,	0,	'RUN_OUT_CATCHER',	''),
(14,	2,	'Run-out (thrower)',	4,	0,	'RUN_OUT_THROWER',	''),
(14,	2,	'Run-out (catcher)',	2,	0,	'RUN_OUT_CATCHER',	''),
(14,	3,	'Run-out (thrower)',	4,	0,	'RUN_OUT_THROWER',	''),
(14,	3,	'Run-out (catcher)',	2,	0,	'RUN_OUT_CATCHER',	'');


============ MongoIndex ==============
vi_notification_type
{
    "notification_type" : 1
}
vi_notification
{
    "notification_status" : 1,
    "user_id" : 1
}
{
    "user_id" : 1
}
vi_active_login
{
    "Sessionkey" : 1
}
vi_lineup
{
    "collection_master_id" : 1
}
{
    "lineup_master_id" : 1
}
{
    "lineup_master_id" : 1,
    "collection_master_id" : 1
}
manage_otp
{
    "phone_no" : 1
}

=============================Soccer======================
INSERT INTO `vi_league` (`league_uid`, `sports_id`, `league_abbr`, `league_name`, `league_display_name`, `active`, `is_promote`, `order`, `max_player_per_team`, `league_schedule_date`, `league_last_date`, `updated_date`, `show_global_leaderboard`, `image`, `archive_status`, `league_provider`, `league_format`, `twitter_hashtags`, `twitter_handles`) VALUES
('epl', 5, 'EPL', 'Premier League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('wc', 5, 'WC', 'World Cup', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('laliga', 5, 'LALIGA', 'Spanish La Liga', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('uefa', 5, 'UEFA', 'Spanish La Liga', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('bundesliga', 5, 'BUNDESLIGA', 'Bundesliga', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('seriea', 5, 'SERIEA', 'Italian Serie A', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('isl', 5, 'ISL', 'Indian League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('ligue1', 5, 'LIGUE1', 'France Ligue1', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('colombia', 5, 'COLOMBIA', 'Colombia Primera', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('argentina', 5, 'ARGENTINA', 'Argentina Superliga', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('europaleague', 5, 'EUROPA', 'Europa League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('saudi', 5, 'SPL', 'Saudi Prof League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('russia', 5, 'RUSSIA', 'Russia League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('china', 5, 'CHINA', 'China Super League', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('mexicoapertura', 5, 'APERTURA', 'Mexico Apertura', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('mexicoclausura', 5, 'CLAUSURA', 'Mexico Clausura', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('chileprimera', 5, 'CHILE', 'Chilw Primera', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL),
('euroqualification', 5, 'EUROCUP', 'Euro Qualification', NULL, 1, 0, 1, 7, '2018-08-10 19:00:00', '2019-05-12 14:00:00', '2018-10-15 07:21:52', 0, '', 0, 0, NULL, NULL, NULL);


================ 11-10-2019 ====================
ALTER TABLE `vi_user` CHANGE `phone_no` `phone_no` BIGINT NULL DEFAULT NULL;

ALTER TABLE `vi_user` ADD INDEX(`phone_no`);

================ 15-10-2019 ====================
ALTER TABLE `vi_user` ADD `is_flag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-normal user,1-flag user' AFTER `is_popup`;

ALTER TABLE `vi_user_affiliate_history` ADD INDEX (`affiliate_type`, `friend_id`);

ALTER TABLE `vi_game_statistics_cricket` CHANGE `bowling_strike_rate` `bowling_economy_rate` FLOAT NOT NULL DEFAULT '0' COMMENT 'economy';
=======
16 oct 2019
INSERT INTO `vi_affiliate_master` (`affiliate_master_id`, `affiliate_type`, `amount_type`, `affiliate_description`, `invest_money`, `bonus_amount`, `real_amount`, `user_bonus`, `coin_amount`, `user_real`, `user_coin`, `is_referral`, `max_earning_amount`, `status`, `order`, `last_update_date`) VALUES (NULL, '16', '1', 'Bank Verify w/o referral', '0', '0', '0', '12', '0', '11', '13', '0', '0', '1', '10', '2019-08-28 08:30:00');

ALTER TABLE `vi_order` CHANGE `source` `source` INT(4) NOT NULL COMMENT '0-Admin, 1-JoinGame, 2-GameCancel 3-GameWon, 4-FriendRefferalBonus, 5-BonusExpired, 6-Promocode, 7-Deposit, 8-Withdraw, 9-BonusOnDeposit,10- DepositPoint,11-WithdrawCoins , 12 - Signup Bonus, 13 - Friend Phone Verified, 14 - Pancard Verified, 15- Referral Contest Join, 16- Referrral Collection Join,17 - User phone verified 40-makePrediction, 41: prediction won,50 - New signup,30-First Deposit,31-Deposit Range,32-PromoCode';

19 Oct 2019
ALTER TABLE `vi_user` ADD `bank_rejected_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `is_bank_verified`;

INSERT INTO `vi_affiliate_master` (`affiliate_master_id`, `affiliate_type`, `amount_type`, `affiliate_description`, `invest_money`, `bonus_amount`, `real_amount`, `user_bonus`, `coin_amount`, `user_real`, `user_coin`, `is_referral`, `max_earning_amount`, `status`, `order`, `last_update_date`) VALUES (NULL, '17', '1', 'Bank Verify with referral', '0', '0', '0', '12', '0', '11', '13', '0', '0', '1', '10', '2019-08-28 08:30:00');

ALTER TABLE `vi_deal` ADD `is_deleted` TINYINT(1) NOT NULL DEFAULT '0' AFTER `status`;


ALTER TABLE `vi_master_sports_format` CHANGE `ar_display_name` `guj_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `vi_banner_management` ADD `en_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`, ADD `hi_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `en_name`, ADD `guj_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `hi_name`;
=========23 Oct 2019======
INSERT INTO `vi_cd_email_template` (`cd_email_template_id`, `template_name`, `subject`, `notification_type`, `status`, `type`, `email_body`, `message_body`, `display_label`, `date_added`, `modified_date`) VALUES
(13, 'custom-sms', '', 134, 1, 6, NULL, NULL, 'Custom SMS', NULL, NULL),
(14, 'custom-notification', '', 135, 1, 6, NULL, NULL, 'Custom Notification', NULL, NULL);

INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`, `en_message`, `hi_message`, `guj_message`) VALUES
(109, 134, 'Custom-msg', 'Custom-msg', 'Custom-msg', 'Custom-msg'),
(110, 135, 'Custom-notification', 'Custom-notification', 'Custom-notification', 'Custom-notification');


=========== 04-11-2019 ========== 
ALTER TABLE `vi_contest` ADD `is_pdf_generated` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0-Pending,1-pushed,2-generated' AFTER `is_rank_calculate`;


=========== 12-11-2019 ==========
ALTER TABLE `vi_user` ADD `total_winning` FLOAT NOT NULL DEFAULT '0' AFTER `total_deposit`;

CREATE TABLE `vi_module_setting` (
  `module_setting_id` int(9) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `display_label` varchar(200) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--
--
-- Indexes for table `vi_module_setting`
--
ALTER TABLE `vi_module_setting`
  ADD PRIMARY KEY (`module_setting_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vi_module_setting`
--
ALTER TABLE `vi_module_setting`
  MODIFY `module_setting_id` int(9) NOT NULL AUTO_INCREMENT;
=======
=========== 14-11-2019 ==========
ALTER TABLE `vi_order` ADD `custom_data` JSON NULL AFTER `old_source_id`;

ALTER TABLE `vi_submodule_setting` ADD `submodule_key` VARCHAR(200) NULL DEFAULT NULL AFTER `module_setting_id`;


INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`, `en_message`, `hi_message`, `guj_message`) VALUES (NULL, '138', 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}');
=======

==============28-Nov-2019======================
INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`, `en_message`, `hi_message`, `guj_message`) VALUES
(NULL, 138, 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}', 'your recieved {coins} for daily checkin Day {day_number}'),
(NULL, 139, 'you have recieved {amount} bonus for redeem coins', 'you have recieved {amount} bonus for redeem coins', 'you have recieved {amount} bonus for redeem coins', 'you have recieved {amount} bonus for redeem coins'),
(NULL, 140, 'you have recieved {amount} real for redeem coins', 'you have recieved {amount} real for redeem coins', 'you have recieved {amount} real for redeem coins', 'you have recieved {amount} real for redeem coins'),
(NULL, 141, '{amount} coins deducted for redeem rewards', '{amount} coins deducted for redeem rewards', '{amount} coins deducted for redeem rewards', '{amount} coins deducted for redeem rewards'),
(NULL, 142, 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification'),
(NULL, 143, 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification'),
(NULL, 144, 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification'),
(NULL, 145, 'you have recieved {amount} bonus for bank verification by your friend', 'you have recieved {amount} bonus for bank verification by your friend', 'you have recieved {amount} bonus for bank verification by your friend', 'you have recieved {amount} bonus for bank verification by your friend'),
(NULL, 146, 'you have recieved {amount} real for bank verification by your friend', 'you have recieved {amount} real for bank verification by your friend', 'you have recieved {amount} real for bank verification by your friend', 'you have recieved {amount} real for bank verification by your friend'),
(NULL, 147, 'you have recieved {amount} coins for bank verification by your friend', 'you have recieved {amount} coins for bank verification by your friend', 'you have recieved {amount} coins for bank verification by your friend', 'you have recieved {amount} coins for bank verification by your friend'),
(NULL, 148, 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification', 'you have recieved {amount} bonus for bank verification'),
(NULL, 149, 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification', 'you have recieved {amount} real cash for bank verification'),
(NULL, 150, 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification', 'you have recieved {amount} coins for bank verification');

=========== 11-12-2019 =================
ALTER TABLE `vi_master_group` ADD `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0-inactive,1-active' AFTER `sort_order`;

UPDATE `vi_master_lineup_position` SET `position_display_name` = 'Forward' WHERE `vi_master_lineup_position`.`master_lineup_position_id` = 46;


/**************NBA - 10 Dec 2019 *****************/

UPDATE `vi_master_sports` SET `active` = '1' WHERE `vi_master_sports`.`sports_id` = 4;

INSERT INTO `vi_league` (`league_uid`, `sports_id`, `league_abbr`, `league_name`, `league_display_name`, `active`, `is_promote`, `order`, `max_player_per_team`, `league_schedule_date`, `league_last_date`, `updated_date`, `show_global_leaderboard`, `image`, `archive_status`, `league_provider`, `league_format`, `twitter_hashtags`, `twitter_handles`) VALUES
('1046', 4, 'NBA', 'NBA', NULL, 1, 0, 0, 4, '2019-10-04 13:30:00', '2020-04-16 02:00:00', '2017-05-01 15:57:14', 0, '', 0, 0, NULL, '', '');


DELETE FROM `vi_master_scoring_rules` WHERE `master_scoring_category_id` = 20;

INSERT INTO `vi_master_scoring_rules` (`master_scoring_category_id`, `format`, `score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`) VALUES
(20, 1, 'Missed FG', -0.5, 0, 'FIELD_GOALS_MISSED', ''),
(20, 1, 'Missed FT', -0.5, 0, 'FREE_THROWS_MISSED', ''),
(20, 1, 'Rebound', 1.25, 0, 'REBOUNDS', ''),
(20, 1, 'Assist', 1.5, 0, 'ASSISTS', ''),
(20, 1, 'Block', 2, 0, 'BLOCKED_SHOT', ''),
(20, 1, 'Steal', 2, 0, 'STEALS', ''),
(20, 1, 'Turnover', -1, 0, 'TURNOVERS', ''),
(20, 1, 'Point ', 1, 0, 'EACH_POINT', '');

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `framework_fantasy`
--

-- --------------------------------------------------------

--
-- Table structure for table `vi_game_statistics_basketball`
--

DROP TABLE IF EXISTS `vi_game_statistics_basketball`;
CREATE TABLE `vi_game_statistics_basketball` (
  `league_id` int(11) NOT NULL,
  `season_game_uid` varchar(100) NOT NULL,
  `week` int(11) NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `home_uid` varchar(100) NOT NULL,
  `away_uid` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `team_uid` varchar(100) NOT NULL,
  `team_points` decimal(11,2) NOT NULL DEFAULT '0.00',
  `scoring_type` varchar(20) NOT NULL,
  `player_uid` varchar(100) NOT NULL,
  `position` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `minutes` decimal(7,2) NOT NULL DEFAULT '0.00',
  `field_goals_made` decimal(7,2) NOT NULL DEFAULT '0.00',
  `field_goals_attempted` decimal(7,2) NOT NULL DEFAULT '0.00',
  `field_goals_missed` decimal(7,2) NOT NULL DEFAULT '0.00',
  `free_throws_made` decimal(7,2) NOT NULL DEFAULT '0.00',
  `free_throws_attempted` decimal(7,2) NOT NULL DEFAULT '0.00',
  `free_throws_missed` decimal(7,2) NOT NULL DEFAULT '0.00',
  `rebounds` decimal(7,2) NOT NULL DEFAULT '0.00',
  `assists` decimal(7,2) NOT NULL DEFAULT '0.00',
  `steals` decimal(7,2) NOT NULL DEFAULT '0.00',
  `blocked_shots` decimal(7,2) NOT NULL DEFAULT '0.00',
  `turnovers` decimal(7,2) NOT NULL DEFAULT '0.00',
  `points` decimal(7,2) NOT NULL DEFAULT '0.00',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vi_game_statistics_basketball`
--
ALTER TABLE `vi_game_statistics_basketball`
  ADD PRIMARY KEY (`league_id`,`season_game_uid`,`player_uid`,`scoring_type`,`week`,`team_uid`);
SET FOREIGN_KEY_CHECKS=1;


***************NFL 11-12-2019************/

UPDATE `vi_master_sports` SET `active` = '1' WHERE `vi_master_sports`.`sports_id` = 2;

INSERT INTO `vi_league` (`league_uid`, `sports_id`, `league_abbr`, `league_name`, `league_display_name`, `active`, `is_promote`, `order`, `max_player_per_team`, `league_schedule_date`, `league_last_date`, `updated_date`, `show_global_leaderboard`, `image`, `archive_status`, `league_provider`, `league_format`, `twitter_hashtags`, `twitter_handles`) VALUES
('1', 2, 'NFL', 'NFL', NULL, 1, 1, 0, 6, '2019-09-06 01:20:00', '2019-12-29 21:25:00', '2017-05-01 15:57:14', 0, '', 0, 0, NULL, '', '');

DELETE FROM `vi_master_scoring_rules` WHERE `master_scoring_category_id` = 19;

INSERT INTO `vi_master_scoring_rules` (`master_scoring_category_id`, `format`, `score_position`, `score_points`, `points_unit`, `meta_key`, `meta_key_alias`) VALUES
(19, 1, 'Passing Yards', 0.04, 0, 'PASSING_YARDS', ''),
(19, 1, 'Passing Touchdowns', 4, 0, 'PASSING_TOUCHDOWNS', ''),
(19, 1, 'Passing Interceptions', -2, 0, 'PASSING_INTERCEPTIONS', ''),
(19, 1, 'Rushing Yards', 0.1, 0, 'RUSHING_YARDS', ''),
(19, 1, 'Rushing Touchdowns', 6, 0, 'RUSHING_TOUCHDOWNS', ''),
(19, 1, 'Receptions', 0.5, 0, 'RECEPTIONS', ''),
(19, 1, 'Receiving Yards', 0.1, 0, 'RECEIVING_YARDS', ''),
(19, 1, 'Receiving Touchdowns', 6, 0, 'RECEIVING_TOUCHDOWNS', ''),
(19, 1, 'Passing 2-Point Conversions', 2, 0, 'PASSING_TWO_POINT', ''),
(19, 1, 'Rushing 2-Point Conversions', 2, 0, 'RUSHING_TWO_POINT', ''),
(19, 1, 'Receving 2-Point Conversions', 2, 0, 'RECEVING_TWO_POINT', ''),
(19, 1, 'Fumbles Lost', -2, 0, 'FUMBLES_LOST', ''),
(19, 1, 'Kick Return Touchdowns', 6, 0, 'KICK_RETURN_TOUCHDOWNS', ''),
(19, 1, 'Punt Return Touchdowns', 6, 0, 'PUNT_RETURN_TOUCHDOWNS', ''),
(19, 1, 'Defense Sacks', 1, 0, 'DEFENSE_SACK', ''),
(19, 1, 'Defense Interceptions', 2, 0, 'DEFENSE_INTERCEPTIONS', ''),
(19, 1, 'Defense Fumbles Recovered', 2, 0, 'DEFENSE_FUMBLES_RECOVERED', ''),
(19, 1, 'Defense Safeties', 2, 0, 'DEFENSE_SAFETIES', ''),
(19, 1, 'Defensive Touchdowns', 6, 0, 'DEFENSE_TOUCHDOWNS', ''),
(19, 1, 'Offensive Fumble Recovery TD', 6, 0, 'FUMBLE_RECOVERY_TOUCHDOWNS', ''),
(19, 1, 'Kicker Extra Pt Made', 1, 0, 'KICKER_EXTRA_PT_MADE', ''),
(19, 1, 'Defense Fumble Recovery TD', 6, 0, 'DEFENSE_FUMBLES_RECOVERY_TD', ''),
(19, 1, 'Kicker Field Goal Missed/Blocked', -3, 0, 'KICKER_FIELD_GOAL_BLOCKED', ''),
(19, 1, 'Kicker Extra Pt Missed/Blocked', -4, 0, 'KICKER_EXTRA_PT_BLOCKED', ''),
(19, 1, 'Kicker 0-19  Yard FG', 3, 0, 'KICKER_FG_0_19', ''),
(19, 1, 'Kicker 20-29  Yard FG', 3, 0, 'KICKER_FG_20_29', ''),
(19, 1, 'Kicker 30-39  Yard FG', 3, 0, 'KICKER_FG_30_39', ''),
(19, 1, 'Kicker 40-49  Yard FG', 4, 0, 'KICKER_FG_40_49', ''),
(19, 1, 'Kicker 50+ Yard FG', 5, 0, 'KICKER_FG_50PLUS', ''),
(19, 1, 'Defense Points Allowed (0)', 10, 0, 'DEFENSE_POINTS_ALLOWED_0', ''),
(19, 1, 'Defense Points Allowed (1-6)', 7, 0, 'DEFENSE_POINTS_ALLOWED_1_6', ''),
(19, 1, 'Defense Points Allowed (7-13)', 4, 0, 'DEFENSE_POINTS_ALLOWED_7_13', ''),
(19, 1, 'Defense Points Allowed (14-20)', 1, 0, 'DEFENSE_POINTS_ALLOWED_14_20', ''),
(19, 1, 'Defense Points Allowed (21-27)', 0, 0, 'DEFENSE_POINTS_ALLOWED_21_27', ''),
(19, 1, 'Defense Points Allowed (28-34)', -1, 0, 'DEFENSE_POINTS_ALLOWED_28_34', ''),
(19, 1, 'Defense Points Allowed (35+)', -4, 0, 'DEFENSE_POINTS_ALLOWED_35plus', ''),
(19, 1, 'Defensive Kick Return Touch Down', 6, 0, 'DEFENSE_KICK_RETURN_TOUCHDOWNS', ''),
(19, 1, 'Defensive Punt Return Touch Down', 6, 0, 'DEFENSE_PUNT_RETURN_TOUCHDOWNS', ''),
(19, 1, 'Defensive Default Points', 10, 0, 'DEFENSE_DEFAULT_POINTS', '');

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `framework_fantasy`
--

-- --------------------------------------------------------

--
-- Table structure for table `vi_game_statistics_football`
--

DROP TABLE IF EXISTS `vi_game_statistics_football`;
CREATE TABLE `vi_game_statistics_football` (
  `league_id` int(11) NOT NULL,
  `season_game_uid` varchar(100) NOT NULL,
  `week` int(11) NOT NULL,
  `scheduled` varchar(500) NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `home_uid` varchar(50) NOT NULL,
  `home_score` int(11) NOT NULL DEFAULT '0',
  `away_uid` varchar(100) NOT NULL,
  `away_score` int(11) NOT NULL DEFAULT '0',
  `team_uid` varchar(100) NOT NULL,
  `player_uid` varchar(100) NOT NULL,
  `passing_yards` int(11) NOT NULL DEFAULT '0',
  `passing_touch_downs` int(11) NOT NULL DEFAULT '0',
  `passing_interceptions` int(11) NOT NULL DEFAULT '0',
  `passing_two_pt` int(11) NOT NULL DEFAULT '0',
  `rushing_yards` int(11) NOT NULL DEFAULT '0',
  `rushing_touch_downs` int(11) NOT NULL DEFAULT '0',
  `rushing_two_pt` int(11) NOT NULL DEFAULT '0',
  `receiving_yards` int(11) NOT NULL DEFAULT '0',
  `receptions` int(11) NOT NULL DEFAULT '0',
  `receiving_touch_downs` int(11) NOT NULL DEFAULT '0',
  `receiving_two_pt` int(11) NOT NULL DEFAULT '0',
  `fumbles_touch_downs` int(11) NOT NULL DEFAULT '0',
  `fumbles_lost` int(11) NOT NULL DEFAULT '0',
  `fumbles_recovered` int(11) NOT NULL DEFAULT '0',
  `interceptions_yards` int(11) NOT NULL DEFAULT '0',
  `interceptions_touch_downs` int(11) NOT NULL DEFAULT '0',
  `interceptions` int(11) NOT NULL DEFAULT '0',
  `kick_returns_yards` int(11) NOT NULL DEFAULT '0',
  `kick_returns_touch_downs` int(11) NOT NULL DEFAULT '0',
  `punt_returns_yards` int(11) NOT NULL DEFAULT '0',
  `punt_return_touch_downs` int(11) NOT NULL DEFAULT '0',
  `field_goals_made` int(11) NOT NULL DEFAULT '0',
  `field_goals_from_1_19_yards` int(11) NOT NULL DEFAULT '0',
  `field_goals_from_20_29_yards` int(11) NOT NULL DEFAULT '0',
  `field_goals_from_30_39_yards` int(11) NOT NULL DEFAULT '0',
  `field_goals_from_40_49_yards` int(11) NOT NULL DEFAULT '0',
  `field_goals_from_50_yards` int(11) NOT NULL DEFAULT '0',
  `extra_points_made` int(11) NOT NULL DEFAULT '0',
  `extra_point_blocked` int(11) NOT NULL DEFAULT '0',
  `field_goals_blocked` int(11) NOT NULL DEFAULT '0',
  `defensive_interceptions` int(11) NOT NULL DEFAULT '0',
  `defensive_fumbles_recovered` int(11) NOT NULL DEFAULT '0',
  `defensive_kick_return_touchdowns` int(11) NOT NULL DEFAULT '0',
  `defensive_punt_return_touchdowns` int(11) NOT NULL DEFAULT '0',
  `sacks` int(11) NOT NULL DEFAULT '0',
  `safeties` int(11) NOT NULL DEFAULT '0',
  `defensive_touch_downs` int(11) NOT NULL DEFAULT '0',
  `defence_turnovers` int(11) NOT NULL DEFAULT '0',
  `points_allowed` int(11) NOT NULL DEFAULT '0',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vi_game_statistics_football`
--
ALTER TABLE `vi_game_statistics_football`
  ADD PRIMARY KEY (`league_id`,`season_game_uid`,`week`,`team_uid`,`player_uid`) USING BTREE;
SET FOREIGN_KEY_CHECKS=1;

UPDATE `vi_module_setting` SET `name` = 'allow_coin' WHERE `vi_module_setting`.`module_setting_id` = 1;

=================24 Dec 2019=================================================
ALTER TABLE `vi_contest` ADD `consolation_prize` JSON NULL DEFAULT NULL COMMENT '{\"prize_type\":\"1\",\"value\":\"10\"} , prize_type => 0 bonus 2 for coins,' AFTER `cancel_reason`;
ALTER TABLE `vi_contest_template` ADD `consolation_prize` JSON NULL DEFAULT NULL COMMENT ' {\"prize_type\":\"1\",\"value\":\"10\"} , prize_type => 0 bonus 2 for coins,' AFTER `modified_date`;
=======

================= 24-12-2019 ============
INSERT INTO `vi_master_group` (`group_id`, `group_name`, `description`, `icon`, `is_private`, `sort_order`, `status`) VALUES (12, 'Contest for Champions', 'Contest for Champions', 'contest_champions.png', '0', '12', '1');

TRUNCATE vi_master_group;

INSERT INTO `vi_master_group` (`group_id`, `group_name`, `description`, `icon`, `is_private`, `sort_order`, `status`) VALUES
(1, 'Mega Contest', 'Enter the hottest contest with mega prizes.', 'mega_contest.png', 0, 1, 1),
(2, 'Head2Head', 'Feel the thrill of the ultimate one on one Fantasy Face off', 'head_to_head.png', 0, 4, 1),
(3, 'Top 50% win', 'Half the players win for sure. Enter and try your luck!', 'double_money.png', 0, 9, 1),
(4, 'New User Challenge', 'Play your very first contest now', 'new_user_challenge.png', 0, 2, 1),
(5, 'More Contest', 'Enter and explore the world of Fantasy Sports', 'more_contest.png', 0, 8, 1),
(6, 'Free Contest/Practice Contest', 'No fuss! This is your zone to play for free.', 'free_contest.png', 0, 11, 1),
(7, 'Private Contest', 'It\'s exclusive and it\'s fun! Play with your friends now.', 'private_contest.png', 1, 12, 1),
(8, 'Gang War', 'When your team is your weapon', 'gangwar.png', 0, 5, 1),
(9, 'Hot Contest', 'No one said it\'s gonna be easy', 'hotcontest.png', 0, 3, 1),
(10, 'Winner Takes All', 'Big Risk, Bigger Reward!', 'winnertakesall.png', 0, 7, 1),
(11, 'Everyone Wins', 'Something for everybody', 'everyoneWins.png', 0, 10, 1),
(12, 'Contest for Champions', 'Contest for Champions', 'contest_champions.png', 0, 6, 1);


ALTER TABLE `vi_game_statistics_football` ADD `minutes` INT(11) NOT NULL DEFAULT '0' AFTER `points_allowed`;


ALTER TABLE `vi_season` DROP INDEX `UNIQUE`, ADD UNIQUE `UNIQUE` (`league_id`, `season_game_uid`) USING BTREE;

ALTER TABLE `vi_team` CHANGE `flag` `flag` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'flag_default.jpg', CHANGE `jersey` `jersey` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'jersey_default.png';


UPDATE `vi_master_scoring_rules` SET `hi_score_position` = '50 और 59.9 के बीच 100 गेंदों प्रति रन (औसत​)' WHERE `vi_master_scoring_rules`.`master_scoring_id` = 68;

================ Player Publish =============
update `vi_player_team` set is_published=1 where player_status=1;

=====================28 Apr 2020============================
CREATE TABLE `vi_prize_distribution_history` (
  `prize_distribution_history_id` int(11) NOT NULL,
  `prediction_prize_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `prize_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 pending, 2 complete, 3 prize ',
  `is_win_notify` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vi_prize_distribution_history`
--
ALTER TABLE `vi_prize_distribution_history`
  ADD PRIMARY KEY (`prize_distribution_history_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vi_prize_distribution_history`
--
ALTER TABLE `vi_prize_distribution_history`
  MODIFY `prize_distribution_history_id` int(11) NOT NULL AUTO_INCREMENT;

  ALTER TABLE `vi_leaderboard_day` ADD `prize_distribution_history_id` INT(11) NULL DEFAULT NULL AFTER `day_date`;
  ALTER TABLE `vi_leaderboard_week` ADD `prize_distribution_history_id` INT(11) NULL DEFAULT NULL ;
  ALTER TABLE `vi_leaderboard_month` ADD `prize_distribution_history_id` INT(11) NULL DEFAULT NULL ;

  ALTER TABLE `vi_leaderboard_week` ADD UNIQUE `unique_index` (`week_number`, `week_start_date`,`user_id`);
  ALTER TABLE `vi_leaderboard_day` ADD UNIQUE `unique_index` (`day_number`, `day_date`,`user_id`);
  ALTER TABLE `vi_leaderboard_month` ADD UNIQUE `unique_index` (`month_number`, `month_start_date`,`user_id`);

  
==================== notification update ==========
INSERT INTO `vi_notification_description` (`notification_description_id`, `notification_type`, `message`, `en_message`, `hi_message`, `guj_message`, `fr_message`) VALUES (NULL, '69', 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned ₹{{amount}} real cash.', 'Congratulations! {{friend_name}} referred by you has joined a contest. You have earned ₹{{amount}} real cash.', 'बधाई हो ! आपको हमारी साइट पर अपने मित्र {{name}} का खेल में खेलने के लिए ₹{{amount}} का अधिक रियल कैश मिला है।', 'અભિનંદન! તમે અમારી સાઇટ પર તમારા મિત્રો {{name}} રમત રમવા માટે વધુ બોનસ ₹{{amount}} મેળવ્યું.', 'Toutes nos félicitations! {{friend_name}} que vous avez référé a rejoint un concours. Vous avez gagné ₹{{amount}} cash en.');

UPDATE `vi_notification_description` SET `message` = 'You have received {{amount}} coins for daily checkin Day {{day_number}}', `en_message` = 'You have received {{amount}} coins for daily checkin Day {{day_number}}', `hi_message` = 'You have received {{amount}} coins for daily checkin Day {{day_number}}', `guj_message` = 'You have received {{amount}} coins for daily checkin Day {{day_number}}' WHERE `notification_type` = 138;

UPDATE `vi_notification_description` SET `message` = 'You have received {{amount}} bonus for redeem coins', `en_message` = 'You have received {{amount}} bonus for redeem coins', `hi_message` = 'You have received {{amount}} bonus for redeem coins', `guj_message` = 'You have received {{amount}} bonus for redeem coins' WHERE `notification_type` = 139;

UPDATE `vi_notification_description` SET `message` = 'You have received {{amount}} real for {{event}}', `en_message` = 'You have received {{amount}} real for {{event}}', `hi_message` = 'You have received {{amount}} real for {{event}}', `guj_message` = 'You have received {{amount}} real for {{event}}' WHERE `notification_type` = 140;

UPDATE `vi_notification_description` SET `message` = 'You have received {{amount}} bonus for editing your referral code', `en_message` = 'You have received {{amount}} bonus for editing your referral code' WHERE `notification_type` = 153;

UPDATE `vi_notification_description` SET `message` = 'You have received ₹{{amount}} real cash for editing your referral code', `en_message` = 'You have received ₹{{amount}} real cash for editing your referral code' WHERE `notification_type` = 154;

UPDATE `vi_notification_description` SET `message` = 'You have received {{amount}} coins for editing your referral code', `en_message` = 'You have received {{amount}} coins for editing your referral code' WHERE `notification_type` = 155;



/*********************BASEBALL INTEGRATION********************/
UPDATE `vi_master_sports` SET `updated_date` = '2020-06-16 14:01:39', `active` = '1' WHERE `vi_master_sports`.`sports_id` = 1;


ALTER TABLE `vi_order` ADD `reference_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'contest id or etc' AFTER `user_id`;
