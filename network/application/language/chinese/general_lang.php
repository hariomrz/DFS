<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] ='请输入有效参数。';
$lang['invalid_status'] ='无效状态。';
$lang['valid_leaderboard_type'] ='无效的页首横幅类型。';
$lang['sports_id'] ='体育ID';
$lang['league_id'] ='联赛ID';
$lang['collection_master_id'] ='集合主ID';
$lang['player_uid'] ='玩家uid';
$lang['player_team_id'] ='玩家钥匙';
$lang['contest_id'] ='比赛ID';
$lang['contest_unique_id'] ='比赛唯一ID';
$lang['lineup_master_id'] ='阵容主ID';
$lang['lineup_master_contest_id'] ='阵容大师竞赛ID';
$lang['season_game_uid'] ='季节游戏uid';
$lang['no_of_match'] ='匹配数';
$lang['against_team'] ='反对团队';
$lang['promo_code'] ='促销代码';
$lang['match_status'] ='比赛状态';
$lang['lineup'] ='lineup';
$lang['team_name'] ='团队名称';
$lang['format'] ='格式';
$lang['join_code'] ='加入代码';
$lang['prize_type'] ='奖品类型';
$lang['salary_cap'] ='薪金上限';
$lang['size'] ='尺寸';
$lang['size_min'] ='最小大小';
$lang['game_name'] ='游戏名称';
$lang['game_desc'] ='游戏说明';
$lang['entry_fee'] ='参赛费';
$lang['prize_pool'] ='奖池';
$lang['number_of_winners'] ='获奖人数';
$lang['prize_distribution_detail'] ='奖品细节';
$lang['disable_private_contest'] ='目前此功能已被管理员禁用。';
$lang['contest_added_success'] ='竞赛创建成功。';
$lang['contest_added_error'] ='创建比赛时出现问题。请重试。';
$lang['currency_type'] ='货币类型';
$lang['same_currency_prize_type'] ='货币类型和奖金类型应该相同。';

//generalmessage
$lang["lineup_required"] = "需要阵容";

//ContestLanguage
$lang['contest']['invalid_contest'] ='请选择一个有效的比赛。';
$lang['contest']['invalid_contest_code'] ='无效的联赛代码。';
$lang['contest']['contest_not_found'] ='找不到比赛详细信息。';
$lang['contest']['problem_while_join_game'] ='加入游戏时出现的问题。';
$lang['contest']['contest_already_started'] ='竞赛已经开始。';
$lang['contest']['contest_already_full'] ='这个比赛已经满了。';
$lang['contest']['contest_closed'] ='比赛已关闭。';
$lang['contest']['not_enough_coins'] ='没有足够的硬币。';
$lang['contest']['not_enough_balance'] ='余额不足。';
$lang['contest']['join_game_success'] ='您已成功加入比赛。';
$lang['contest']['invalid_promo_code'] ='无效的促销代码。请输入有效的代码。';
$lang['contest']['allowed_limit_exceed'] ='您已经在最大时间使用了此促销代码。';
$lang['contest']['promo_code_exp_used'] ='促销代码已过期或已使用！';
$lang['contest']['you_already_joined_to_max_limit'] ='您已经参加了本次比赛，达到了团队上限。';
$lang['contest']['join_multiple_time_error'] ='您不能多次参加此竞赛。';
$lang['contest']['you_already_joined_this_contest'] ='您已经通过选定的阵容加入了该竞赛。';
$lang['contest']['provide_a_valid_lineup_master_id'] ='请提供有效的阵容主ID。';
$lang['contest']['not_a_valid_team_for_contest'] ='该比赛无效。';
$lang['contest']['exceed_promo_used_count'] ='您已经超出了允许的使用数量。';
$lang['contest']['team_detail_not_found'] ='我们正在处理团队数据。';
$lang['contest']['invalid_previous_team_for_collecton'] ='所选比赛的前一支队伍无效。';
$lang['contest']['team_switch_success'] ='团队切换成功。';
$lang['contest']['invalid_team_for_collecton'] ='所选比赛的阵容无效。';
$lang['contest']['processing_team_pdf_data'] ='我们正在处理团队数据，很快就会提供。';
$lang['contest']['join_game_email_subject'] ='您的竞赛已被确认！';
$lang['contest_cancel_mail_subject'] ='['.SITE_TITLE.']​​比赛取消信息';
$lang['contest']['process_contest_pdf'] ='我们正在处理团队pdf，将很快推出。';

//mulitple team join

$lang['contest']['select_min_one_team'] ='请选择至少一个团队。';
$lang['contest']['already_joined_with_teams'] ='很抱歉，您已经被选定的团队加入此竞赛。';
$lang['contest']['contest_max_allowed_team_limit_exceed'] ='很抱歉，您不能与更多的{TEAM_LIMIT}个团队一起参加比赛。';
$lang['contest']['problem_while_join_game_some_team'] ='与{TEAM_COUNT}个团队一起参加比赛时出现的问题。';
$lang['contest']['multiteam_join_game_success'] ='您已经成功与{TEAM_COUNT}个团队参加了比赛。';

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']['contest_not_found']='找不到比赛。';
$lang['lineup']['contest_started']='比赛已经开始。';
$lang['lineup']['match_detail_not_found']='找不到匹配的详细信息。';
$lang['lineup']['invalid_collection_player'] ='选定的球员无效。请重置球队阵容并创建新的。';
$lang['lineup']['lineup_not_exist'] ='团队不存在';
$lang['lineup']['team_name_already_exist'] ='团队名称已存在。';
$lang['lineup']['lineup_team_rquired']='需要玩家联赛团队ID。';
$lang['lineup']['lineup_player_id_required']='需要玩家唯一ID。';
$lang['lineup']['lineup_player_team_required']='需要玩家团队ID。';
$lang['lineup']['position_invalid'] ='无效位置';
$lang['lineup']['salary_required'] ='所需的球员工资。';
$lang['lineup']['lineup_player_id_duplicate'] ='您两次不能选择一个玩家';
$lang['lineup']['lineup_max_limit']='您应该选择％s个玩家来创建团队。';
$lang['lineup']['lineup_team_limit_exceeded']='请更正您的阵容。您可以从一个团队中选择最多％s个球员。';
$lang['lineup']['position_exceeded_invalid']='您已经超出玩家位置限制。';
$lang['lineup']['salary_cap_not_enough']='玩家的薪水超过最大可用薪水。';
$lang['lineup']['lineup_posisiotn_not_found'] ='请选择％s播放器';
$lang['lineup']['already_created_same_team'] ='您已经创建了这个团队。';
$lang['lineup']['lineup_success'] ='您已经成功创建了团队';
$lang['lineup']['lineup_update_success'] ='您的团队已成功更新';
$lang['lineup']['lineup_captain_error'] ='需要队长';
$lang['lineup']['lineup_vice_captain_error'] ='需要团队副队长';
$lang['lineup']['team_detail_not_found'] ='找不到团队详细信息。';
$lang['captain'] ='船长';
$lang['vice_captain'] ='副队长';

//private contest
$lang[' enter_valid_sport_id'] ='请输入有效的运动ID。';
$lang[' enter_valid_season_game_uid'] ='请输入有效的比赛ID。';

$lang['group_name_1'] ='超级竞赛';
$lang['group_description_1'] ='进入最热的竞赛，并获得大奖。';

$lang['group_name_9'] =' Hot Contest';
$lang['group_description_9'] ='没有人说这会很容易';

$lang['group_name_8'] ='帮派战争';
$lang['group_description_8'] ='当你的团队是你的武器';

$lang['group_name_2'] =' Head2Head';
$lang['group_description_2'] ='感受终极一对一幻想面孔的刺激。';

$lang['group_name_10'] ='赢家通吃';
$lang['group_description_10'] ='大风险，更大的奖励！';

$lang['group_name_3'] ='前50％获胜';
$lang['group_description_3'] ='确定有一半的玩家获胜。输入并尝试您的运气！';

$lang['group_name_11'] ='所有人都赢了';
$lang['group_description_11'] ='适合所有人。';

$lang['group_name_4'] ='仅适用于初学者';
$lang['group_description_4'] ='立即参加您的第一场比赛';

$lang['group_name_5'] ='更多比赛';
$lang['group_description_5'] ='不用大惊小怪！这是您免费玩的区域。';

$lang['group_name_6'] ='免费比赛';
$lang['group_description_6'] ='不用大惊小怪！这是您免费玩并赢取现金的区域';

$lang['group_name_7'] ='私人比赛';
$lang['group_description_7'] ='这很有趣，很有趣！现在和你的朋友一起玩。';

$lang['group_name_12'] ='冠军争夺赛';
$lang['group_description_12'] ='冠军争夺战';

$lang['problem_while_join_game_network'] ='加入游戏时出现问题！请重试。';
$lang['action_cant_completed_err'] ='操作无法完成！请重试。';
$lang['contest']['self_exclusion_limit_reached'] ='无法参加比赛，参加人数超出限制。';


//mulitple team join
$lang['contest']["select_min_one_team"] = "请选择至少一个团队。";
$lang['contest']["already_joined_with_teams"] = "抱歉，您已经由选定的团队参加此竞赛";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "抱歉，您不能再加入超过{TEAM_LIMIT}个团队的比赛。";
$lang['contest']["problem_while_join_game_some_team"] = "与{TEAM_COUNT}个团队一起加入游戏时出现问题";
$lang['contest']["multiteam_join_game_success"] = "您已成功与{TEAM_COUNT}个团队一起参加比赛）";