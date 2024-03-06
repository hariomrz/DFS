<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */

$lang['global_error']                   = 'Пожалуйста, введите допустимые параметры.'; 
$lang['invalid_status']                 = 'Недействительный статус.';
$lang['valid_leaderboard_type']         = 'Неверный тип таблицы лидеров.';
$lang['sports_id']                      = 'спортивный идентификатор';
$lang['league_id']                      = 'идентификатор лиги';
$lang['collection_master_id']           = 'идентификатор мастера коллекции';
$lang['player_uid']                     = 'uid игрока';
$lang['player_team_id']                 = 'ключ игрока';
$lang['contest_id']                     = 'идентификатор конкурса';
$lang['contest_unique_id']              = 'уникальный идентификатор конкурса';
$lang['lineup_master_id']               = 'идентификатор мастера очереди';
$lang['lineup_master_contest_id']       = 'идентификатор конкурса мастера состава';
$lang['season_game_uid']                = 'uid игры сезона';
$lang['no_of_match']                    = 'количество совпадений';
$lang['against_team']                   = 'Против команды';
$lang['promo_code']                     = 'Промокод';
$lang['match_status']                   = 'Статус соответствия';
$lang['lineup']                         = 'состав';
$lang['team_name']                      = 'название команды';
$lang['format']                         = 'формат';
$lang['join_code']                      = 'код соединения';
$lang['prize_type']                     = 'тип приза';
$lang['salary_cap']                     = 'потолок зарплаты';
$lang['size']                           = 'размер';
$lang['size_min']                       = 'минимальный размер';
$lang['game_name']                      = 'название игры';
$lang['game_desc']                      = 'описание игры';
$lang['entry_fee']                      = 'вступительный взнос';
$lang['prize_pool']                     = 'призовой фонд';
$lang['number_of_winners']              = 'количество победителей';
$lang['prize_distribution_detail']      = 'деталь приза';
$lang['disable_private_contest']        = "в настоящее время эта функция отключена администратором.";
$lang["contest_added_success"]          = "Конкурс успешно создан.";
$lang["contest_added_error"]            = "Проблема при создании конкурса. Повторите попытку.";


//generalmessage
$lang["lineup_required"] = "Требуется состав";

//ContestLanguage
$lang['contest']["invalid_contest"] = 'Выберите действительный конкурс.';
$lang['contest']["invalid_contest_code"] = "Недействительный код лиги.";
$lang['contest']["contest_not_found"] = "Детали конкурса не найдены.";
$lang['contest']["problem_while_join_game"] = "Проблема при присоединении к игре.";
$lang['contest']["contest_already_started"] = "Конкурс уже начался.";
$lang['contest']["contest_already_full"] = "Этот конкурс уже заполнен.";
$lang['contest']["contest_closed"] = "Конкурс закрыт.";
$lang['contest']["not_enough_coins"] = 'Недостаточно монет.';
$lang['contest']["not_enough_balance"] = 'Недостаточно на балансе.';
$lang['contest']["join_game_success"] = "Вы успешно присоединились к конкурсу.";
$lang["contest"]["invalid_promo_code"] = "Недействительный промокод. Пожалуйста, введите действительный код.";
$lang["contest"]["allowed_limit_exceed"] = "Вы уже использовали этот промокод максимальное время.";
$lang["contest"]["promo_code_exp_used"] = "Промокод просрочен или уже использован!";
$lang['contest']["you_already_joined_to_max_limit"] = "Вы уже присоединились к этому конкурсу до максимального командного лимита.";
$lang['contest']["join_multiple_time_error"] = 'Вы не можете участвовать в этом конкурсе несколько раз.';
$lang['contest']["you_already_joined_this_contest"] = "Вы уже присоединились к этому конкурсу выбранным составом.";
$lang['contest']["provide_a_valid_lineup_master_id"] = "Пожалуйста, предоставьте действительный идентификатор мастера линейки.";
$lang['contest']["not_a_valid_team_for_contest"] = 'Недопустимая команда для участия в конкурсе.';
$lang['contest']['exceed_promo_used_count'] = "Вы превысили допустимое количество использованных материалов.";
$lang['contest']['team_detail_not_found'] = 'Мы обрабатываем данные команды.';
$lang['contest']["invalid_previous_team_for_collecton"] = 'Недопустимая предыдущая команда для выбранного конкурса.';
$lang['contest']["team_switch_success"] = "Команда успешно сменилась.";
$lang['contest']["invalid_team_for_collecton"] = 'Недопустимый состав для выбранного конкурса.';
$lang['contest']['processing_team_pdf_data'] = "Мы обрабатываем данные команды, они скоро будут доступны.";
$lang['contest']["join_game_email_subject"] = "Ваше участие в конкурсе подтверждено!";
$lang['contest_cancel_mail_subject'] = '['.SITE_TITLE.'] Информация об отмене конкурса';
$lang['contest']["process_contest_pdf"] = "Мы обрабатываем командный pdf файл, скоро он будет доступен.";

//mulitple team join
$lang['contest']["select_min_one_team"] = 'Выберите хотя бы одну команду.';
$lang['contest']["already_joined_with_teams"] = "Извините, вы уже присоединились к этому конкурсу выбранной командой (ами).";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Извините, вы не можете присоединиться к этому конкурсу с более чем {TEAM_LIMIT} командами.";
$lang['contest']["problem_while_join_game_some_team"] = "Проблема при присоединении к игре с {TEAM_COUNT} командами.";
$lang['contest']["multiteam_join_game_success"] = "Вы успешно присоединились к конкурсу с {TEAM_COUNT} командой (ами).";

//Lineup Language
$lang['lineup'] = array ();
$lang['lineup']["contest_not_found"] = 'Конкурс не найден.';
$lang['lineup']["contest_started"] = "Конкурс уже начался.";
$lang['lineup']["match_detail_not_found"] = 'Детали совпадения не найдены.';
$lang['lineup']['invalid_collection_player'] = "Недействительно выбранные игроки. Пожалуйста, сбросьте состав команды и создайте новый.";
$lang['lineup']["lineup_not_exist"] = 'Команда не существует';
$lang['lineup']['team_name_already_exist'] = 'Название команды уже существует.';
$lang['lineup']["lineup_team_rquired"] = "Требуется идентификатор команды лиги игрока.";
$lang['lineup']["lineup_player_id_required"] = "Требуется уникальный идентификатор игрока.";
$lang['lineup']["lineup_player_team_required"] = "Требуется идентификатор команды игрока.";
$lang['lineup']["position_invalid"] = "недопустимая позиция";
$lang['lineup']["salary_required"] = "требуется зарплата игрока.";
$lang['lineup']["lineup_player_id_duplicate"] = 'Вы не можете выбрать одного игрока дважды';
$lang['lineup']["lineup_max_limit"] = "Вы должны выбрать %s игроков для создания команды.";
$lang['lineup']["lineup_team_limit_exceeded"] = "Исправьте свой состав. Вы можете выбрать максимум %s игроков из одной команды.";
$lang['lineup']["position_exceeded_invalid"] = 'Вы превысили лимит позиции игрока.';
$lang['lineup']["salary_cap_not_enough"] = "Заработная плата игроков превышает максимально доступную зарплату.";
$lang['lineup']["lineup_posisiotn_not_found"] = 'Пожалуйста, выберите %s игрока';
$lang['lineup']['already_created_same_team'] = 'Вы уже создали эту команду.';
$lang['lineup']["lineup_success"] = "Вы успешно создали команду";
$lang['lineup']["lineup_update_success"] = 'Ваша команда успешно обновлена';
$lang['lineup']["lineup_captain_error"] = 'Требуется капитан команды';
$lang['lineup']["lineup_vice_captain_error"] = 'Требуется вице-капитан команды';
$lang['lineup']['team_detail_not_found'] = "Детали команды не найдены.";



//private contest
$lang["enter_valid_sport_id"] = "Пожалуйста, введите действительный идентификатор вида спорта.";
$lang["enter_valid_season_game_uid"] = "Пожалуйста, введите действительный идентификатор совпадения.";

$lang['group_name_1'] = "Мега-конкурс";
$lang['group_description_1'] = "Примите участие в самом горячем конкурсе с мега-призами.";

$lang['group_name_9'] = 'Горячий конкурс';
$lang['group_description_9'] = "Никто не сказал, что это будет легко";

$lang['group_name_8'] = "Война банд";
$lang['group_description_8'] = 'Когда ваша команда - ваше оружие';

$lang['group_name_2'] = "Head2Head";
$lang['group_description_2'] = "Почувствуйте острые ощущения от фэнтези-боя один на один.";

$lang['group_name_10'] = 'Победитель забирает все';
$lang['group_description_10'] = 'Большой риск, большая награда!';

$lang['group_name_3'] = "50% лучших выигрышей";
$lang['group_description_3'] = "Половина игроков точно выиграет. Войдите и испытайте удачу!";

$lang['group_name_11'] = 'Выигрывают все';
$lang['group_description_11'] = 'Что-то для всех.';

$lang['group_name_4'] = 'Только для начинающих';
$lang['group_description_4'] = "Сыграйте в свой самый первый конкурс прямо сейчас";

$lang['group_name_5'] = "Еще конкурс";
$lang['group_description_5'] = "Без суеты! Это ваша зона, где можно играть бесплатно.";

$lang['group_name_6'] = 'Бесплатное соревнование';
$lang['group_description_6'] = 'Без суеты! Это ваша зона, где можно играть бесплатно и выигрывать деньги';

$lang['group_name_7'] = "Частный конкурс";
$lang['group_description_7'] = "Это эксклюзивно и весело! Играйте с друзьями прямо сейчас.";

$lang['group_name_12'] = 'Соревнование за чемпионов';
$lang['group_description_12'] = 'Соревнование за чемпионов';

$lang["problem_while_join_game_network"] = "Masalah saat bergabung dengan game! Silakan coba lagi.";
$lang["action_cant_completed_err"] = "Tindakan tidak dapat diselesaikan! Silakan coba lagi.";
$lang['contest'] ["self_exclusion_limit_reached"] = "Tidak dapat mengikuti kontes, melebihi batas bergabung.";

//mulitple team join
$lang['contest']["select_min_one_team"] = "Выберите хотя бы одну команду.";
$lang['contest']["already_joined_with_teams"] = "Извините, вы уже присоединились к этому конкурсу выбранными командами.";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "К сожалению, вы не можете присоединиться к этому конкурсу с более чем {TEAM_LIMIT} командами.";
$lang['contest']["problem_while_join_game_some_team"] = "Проблема при присоединении к игре с {TEAM_COUNT} командами.";
$lang['contest']["multiteam_join_game_success"] = "Вы успешно присоединились к конкурсу с командами ({TEAM_COUNT}).";