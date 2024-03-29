<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['input_invalid_format']               ="Формат ввода недействителен.";
$lang['global_error']                       ="Пожалуйста, введите допустимые параметры.";
$lang['status']                             ="Статус";
$lang['invalid_status']                     ="Неверный статус.";
$lang['valid_leaderboard_type']             ="Неверный тип лидеров.";
$lang['sports_id']                          ="Спорт ID";
$lang['league_id']                          ="лига ID";
$lang['collection_master_id']               ="Учитель коллекции ID.";
$lang['collection_id']                      ="Коллекция ID.";
$lang['player_uid']                         ="Игрок UID";
$lang['player_team_id']                     ="ключ игрока";
$lang['contest_id']                         ="Конкурс ID.";
$lang['contest_unique_id']                  ="Конкурс уникальный ID.";
$lang['lineup_master_id']                   ="Линейный мастер ID";
$lang['lineup_master_contest_id']           ="Линейный магистерский конкурс ID";
$lang['season_game_uid']                    ="Сезон игра UID";
$lang['no_of_match']                        ="Количество матчей";
$lang['against_team']                       ="Против команды";
$lang['promo_code']                         ="Промо код";
$lang['match_status']                       ="Состояние соответствия";
$lang['lineup']                             ="расстановка";
$lang['team_name']                          ="Название команды";
$lang['format']                             ="формат";
$lang['join_code']                          ="Присоединиться к коду";
$lang['prize_type']                         ="Тип приза";
$lang['salary_cap']                         ="Крышка заработной платы";
$lang['size']                               ="размер";
$lang['size_min']                           ="мин размер";
$lang['game_name']                          ="Имя игры";
$lang['category']                           ="Категория";
$lang['game_desc']                          ="Игра Desc.";
$lang['entry_fee']                          ="взнос";
$lang['prize_pool']                         ="призовой фонд";
$lang['number_of_winners']                  ="Количество победителей";
$lang['prize_distribution_detail']          ="премия детали";
$lang['disable_private_contest']            ="В настоящее время эта функция отключена администратором.";
$lang["contest_added_success"]              ="Конкурс создан успешно.";
$lang["contest_added_error"]                ="Проблема во время конкурса создавать. пожалуйста, попробуйте снова.";
$lang['leaderboard_type']                   ="Тип лидеров";
$lang['user_id']                            ="Логин пользователя";
$lang["source"]                             ="источник";
$lang['file_not_found']                     ="Вы не выбрали файл для загрузки.";
$lang['invalid_image_size']                 ="Пожалуйста, загрузите файл изображения Max {Size} Размер";
$lang['invalid_image_ext']                  ="Пожалуйста, загрузите изображение только с расширением% S";
$lang["icon_upload_success"]                ="Изображение загружено успешно";
$lang["image_removed"]                      ="Изображение удалено успешно.";
$lang["image_removed_error"]                ="Извините, что-то пошло не так, удалите изображение.";
$lang['file_upload_error']                  ="Извините, есть некоторая проблема с загрузкой файла. пожалуйста, попробуйте снова.";
$lang['match_not_found_msg']                ="Детали спички не найдены";
$lang['match_custom_msg_sent']              ="Пользовательское сообщение отправьте успешно.";
$lang["successfully_cancel_collection"]     ="Коллекция была отменена успешно.";
$lang["successfully_cancel_contest"]        ="Конкурс был успешно отменен";
$lang["no_contest_for_cancel"]     			= "There is not contest for cancel.";
$lang["delete_contest"]                     = "Contest deleted successfully";
$lang["no_change"]                          = "No change";
$lang['invalid_image_dimension']            = 'Please upload image of size less than or equal to {max_width}x{max_height} ';

$lang['type']                               	= 'type';
$lang['filter']                             	= 'filter';
$lang["sponsor_name"]				            = "Sponsor name";
$lang["sponsor_logo"]				            = "Sponsor image";
$lang["sponsor_contest_dtl_image"]				= "Sponsor contest detail image";
$lang["sponsor_link"]				            = "Sponsor link";
$lang["invalid_sponsor_image_size"]			    = "Please upload image of size less than or equal to {max_width}x{max_height} ";
$lang['site_rake'] = 'Site Rake';
$lang["invalid_game_size"]						= "Please select games";
$lang["invalid_game_multiple_lineup_size"]			= "Multiple lineup should be less than or equal to size.";
$lang["invalid_tie_breaker_status"]				= "Merchandise prize type should always be in tie breaker.";
$lang["auto_recurrent_merchandise_error"]       = "Auto recurrent can't be created for merchandise prize type.";
$lang['auto_recurrent_create_error'] = "You can't create auto recurrent free contest which prize pool is greater than zero";

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']["contest_not_found"] = "Конкурс не найден.";
$lang['lineup']["contest_started"] = "Конкурс уже начался.";
$lang['lineup']["match_detail_not_found"] = "Детали матча не найдены.";
$lang['lineup']["lineup_max_limit"] = "Вы должны выбрать максимум %s вопросов для создания команды.";
$lang['lineup']['invalid_collection_player'] = "Неверно выбранные игроки. Пожалуйста, сбросьте состав команды и создайте новый.";
$lang['lineup']['team_name_already_exist'] = 'Название команды уже существует.';
$lang['lineup']["lineup_success"] = "Вы успешно создали подборки";
$lang['lineup']["update_lineup_success"] = "Вы успешно обновили подборки";

//ContestLanguage
$lang['contest']["invalid_contest"] = "Пожалуйста, выберите действительный конкурс.";
$lang['contest']["contest_not_found"] = "Детали конкурса не найдены";
$lang['contest']["problem_while_join_game"] = "Проблема при входе в игру.";
$lang['contest']["contest_already_started"] = "Конкурс уже начался.";
$lang['contest']["contest_already_full"] = "Этот конкурс уже полный.";
$lang['contest']["contest_closed"] = "Конкурс закрыт.";
$lang['contest']["not_enough_coins"] = "Недостаточно монет.";
$lang['contest']["not_enough_balance"] = "Не хватает баланса.";
$lang['contest']["join_multiple_time_error"] = "Вы не можете участвовать в этом конкурсе несколько раз.";
$lang['contest']["you_already_joined_this_contest"]	= "Вы уже присоединились к этому конкурсу выбранным составом.";
$lang['contest']["provide_a_valid_user_team_id"] = "Укажите действительный идентификатор группы пользователей.";
$lang['contest']["join_game_success"] = "Вы успешно присоединились к конкурсу.";
$lang['contest']["join_game_email_subject"] = "Ваше участие в конкурсе подтверждено!";
$lang['contest']["you_already_joined_to_max_limit"] = "Вы уже присоединились к этому соревнованию до максимального количества команд.";
$lang['contest']["self_exclusion_limit_reached"] = "Невозможно присоединиться к соревнованию. Превышен лимит на количество участников.";
$lang['contest']["invalid_previous_team_for_season"]	= "Недействительная предыдущая команда для выбранного соревнования.";
$lang['contest']["invalid_team_for_season"] = "Неверный состав для выбранного конкурса.";
$lang['contest']["team_switch_success"]                  = "Команда успешно переключилась.";