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
$lang['currency_type']                  = 'тип валюты';
$lang["same_currency_prize_type"]       = "Тип валюты и тип приза должны совпадать.";

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
$lang['contest']["rookie_user_not_allowed_for_this_contest"] = "Вам не разрешено присоединиться к этому конкурсу";
$lang['contest']["max_usage_limit_code"] = "Превышено максимальное использование для этого превышения промо-кода";

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
$lang['lineup']["allow_team_limit_error"] = "Вы не можете создать более %s команд.";
$lang['captain'] = "Капитан";
$lang['vice_captain'] = "Вице-капитан";

$lang['tr_lineup'] = array();
$lang['tr_lineup']["match_not_found"] = "Соответствие не найдено.";
$lang['tr_lineup']["match_started"] = "Матч уже начался.";
$lang['tr_lineup']['invalid_match_player'] = "Недействительно выбранные игроки. Пожалуйста, сбросьте состав команды и создайте новый.";

$lang["tournament_season_id"] = "Tournament Season ID";
$lang["user_tournament_season_id"] = "User tournament Season ID";
$lang["tournament_id"] = "Tournament ID";
$lang["tournament_team_id"] = "Tournament Team ID";


$lang['tournament']["invalid_tournament"]                   ="Пожалуйста, выберите правильный турнир.";
$lang['tournament']["invalid_tournament_code"]              ="Не правильный код турнира.";
$lang['tournament']["tournament_not_found"]                 ="Турнирные детали не найдено.";
$lang['tournament']["problem_while_join_tournament"]        ="Проблема в то время как присоединиться турнир.";
$lang['tournament']["match_already_started"]                ="Матч уже начался.";
$lang['tournament']["tournament_closed"]                    ="Турнир закрыт.";
$lang['tournament']["not_enough_coins"]                     ="Недостаточно монет.";
$lang['tournament']["not_enough_balance"]                   ="Не хватает баланса.";
$lang['tournament']["join_tournament_success"]              ="Вы успешно присоединились турнир.";
$lang["tournament"]["invalid_promo_code"]                   ="Invalid промо-код. Пожалуйста, введите правильный код.";
$lang["tournament"]["allowed_limit_exceed"]                 ="Вы уже использовали этот Промокод для времени максимального.";
$lang["tournament"]["promo_code_exp_used"]                  ="Промокод истек или уже используется!";
$lang['tournament']["join_multiple_time_error"]             ="Вы не можете присоединиться к этому турнир несколько раз.";
$lang['tournament']["you_already_joined_this_contest"]="Вы уже присоединились к этому матчу выбранного состава.";
$lang['tournament']["provide_a_valid_tournament_team_id"]   ="Пожалуйста, укажите правильный турнир ID команды.";
$lang['tournament']["not_a_valid_team_for_match"]           ="Не действует команда на матч.";
$lang['tournament']['exceed_promo_used_count']              ="Вы превышайте позволили б кол.";
$lang['tournament']['team_detail_not_found']                ="Мы обрабатываем данные команды.";
$lang['tournament']["team_switch_success"]                  ="Команда успешно переключился.";
$lang['tournament']["invalid_team_for_match"]               ="Invalid линейка для выбранного матча.";
$lang['tournament']['processing_team_pdf_data']             ="Мы обрабатываем данные команды, она будет доступна в ближайшее время.";
$lang['tournament']["join_tournament_email_subject"]        ="Ваш турнир присоединения подтвержден!";
$lang['tournament_cancel_mail_subject']                     ="[".SITE_TITLE."] Информация отмены турнира";
$lang['tournament']["process_contest_pdf"]                  ="Мы обрабатываем команды PDF, она будет доступна в ближайшее время.";
$lang['tournament']["state_banned_error"]                   ="Извините, но игроки из {{}} STATE_LIST не в состоянии войти в платном турнире.";
$lang['tournament']["state_required_error"]                 ="Пожалуйста, обновление состояния в вашем профиле.";

$lang['tournament']["join_tournament_to_continue"] ="Пожалуйста, присоединяйтесь к турниру, чтобы продолжить";
$lang['tournament']["join_match_success"] ="Вы успешно присоединились матч.";

$lang['tournament']["err_tournament_cancelled"] ="Этот турнир отменен";
$lang['tournament']["err_tournament_completed"] ="Этот турнир завершен";
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

$lang['module_disable_error'] = "К сожалению, этот модуль не включен. Обратитесь к администратору.";

$lang['file_upload_error'] = "Извините, возникла проблема с загрузкой файла. Повторите попытку.";
$lang['players'] = "игроки";

$lang['module_not_activated'] = 'Модуль не активирован»';

$lang['multiple_lineup'] = 'несколько составов';
$lang['lineup'] ['team_generate_error'] = "Извините, некоторые проблемы во время создания команды. пожалуйста, попробуйте снова.";
$lang['contest'] ["self_exclusion_limit_reached"] = 'Невозможно присоединиться к соревнованию, превышено ограничение на количество участников.';
$lang['contest'] ["state_banned_error"] = "Извините, но игроки из {{STATE_LIST}} не могут принять участие в платном конкурсе.";
$lang['contest'] ["state_required_error"] = 'Обновите состояние в своем профиле.';

$lang['invalid_booster_id'] = "Извините, недействительный идентификатор усилителя для матча.";
$lang['save_booster_error'] = "Извините, при применении бустера возникла проблема. пожалуйста, попробуйте снова.";
$lang['save_booster_success'] = "Бустер успешно применен.";
$lang['update_booster_success'] = "Бустер успешно обновлен.";
$lang['booster_only_for_dfs'] = "Извините, Booster применим только к классическим фэнтезийным командам.";
$lang['invalid_team_for_match'] = "Неверная команда для матча.";

$lang['err_2nd_inning_format_contest'] = "Вы не можете создать 2-го финального частного конкурса для (T10 / Test Match)";

//bench module
$lang['max_bench_limit_error'] = "Max 4 игроки скамейки разрешены.";
$lang['invalid_collection_bench_player'] = "Неверно выбранный идентификатор игроков. Пожалуйста, выберите действительные игрок.";
$lang['bench_player_team_pl_error'] = "Скамья игроки должны отличаться от игроков команды.";
$lang['bench_player_save_error'] = "К сожалению, есть какой-то вопрос, а сохранить скамейку запасных игроков. пожалуйста, попробуйте снова.";
$lang['save_bench_player_success'] = "Скамья игроки успешно сохранены.";
$lang["bench_process_waiting_error"] = "Команда будет доступна в ближайшее время, попробуйте еще раз когда-то.";

$lang['invalid_prize_distribution_error'] = "Неверная информация о распределении призов. Пожалуйста, попробуйте с правильными данными.";
$lang['invalid_prize_pool_error'] = "Укажите правильный призовой фонд.";

$lang['contest']["h2h_game_join_limit_error"] = "Извините, вы не можете присоединиться к более чем {CONTEST_LIMIT} CONNEST H2H.";

$lang["match_started_error"] = "Match already started. You can't save your team.";
$lang["lineup_player_limit"] = "{player_limit} players required to create team.";
$lang["lineup_team_limit_exceeded"] = "You can select maximum {team_player_limit} players from one team.";
$lang["team_save_success"] = "Your team has been saved successfully";
$lang["team_save_error"] = "Error! while saving team. please try again.";
$lang["team_detail_not_found"] = "Sorry, Team details not found.";
$lang["team_view_not_allowed"] = "Please wait till the match start to view other user team.";
$lang["guru_allowed_dfs"] = "This module only available for single fixture game.";
$lang["invalid_cm_id"] = "Invalid match id. Please provide valid match id.";
$lang["cm_started_pc_error"] = "Match already started. You can't create contest.";