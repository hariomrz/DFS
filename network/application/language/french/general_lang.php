<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'Veuillez saisir des paramètres valides.';
$lang['invalid_status'] = 'Statut invalide.';
$lang['valid_leaderboard_type'] = 'Type de classement invalide.';
$lang['sports_id'] = 'identifiant sportif';
$lang['league_id'] = 'identifiant de la ligue';
$lang['collection_master_id'] = 'ID maître de collection';
$lang['player_uid'] = 'joueur uid';
$lang['player_team_id'] = 'clé de joueur';
$lang['contest_id'] = 'identifiant du concours';
$lang['contest_unique_id'] = 'concours id unique';
$lang['lineup_master_id'] = 'id de maître de programmation';
$lang['lineup_master_contest_id'] = 'identifiant du concours de programmation';
$lang['season_game_uid'] = 'uid de jeu de saison';
$lang['no_of_match'] = 'nombre de matchs';
$lang['against_team'] = "Contre l'équipe";
$lang['promo_code'] = 'code promo';
$lang['match_status'] = 'Statut du match';
$lang['lineup'] = "s'aligner";
$lang['team_name'] = "Nom de l'équipe";
$lang['format'] = 'format';
$lang['join_code'] = 'joindre le code';
$lang['prize_type'] = 'type de prix';
$lang['salary_cap'] = 'plafond salarial';
$lang['size'] = 'Taille';
$lang['size_min'] = 'taille min';
$lang['game_name'] = 'nom du jeu';
$lang['game_desc'] = 'jeu desc';
$lang['entry_fee'] = "frais d'entrée";
$lang['prize_pool'] = 'prize pool';
$lang['number_of_winners'] = 'nombre de gagnants';
$lang['prize_distribution_detail'] = "détail du prix";
$lang['disable_private_contest'] = "actuellement cette fonctionnalité est désactivée par l'administrateur.";
$lang["contest_added_success"] = "concours créé avec succès.";
$lang["contest_added_error"] = "Problème lors de la création du concours. Veuillez réessayer.";
$lang['currency_type'] = 'type de devise';
$lang["same_currency_prize_type"] = "le type de devise et le type de prix doivent être identiques.";

//generalmessage
$lang["lineup_required"] = "alignement requis";

//ContestLanguage
$lang['contest']["invalid_contest"] = "Veuillez sélectionner un concours valide.";
$lang['contest']["invalid_contest_code"] = "Pas un code de ligue valide.";
$lang['contest']["contest_not_found"] = "Détails du concours introuvables.";
$lang['contest']["problem_while_join_game"] = "Problème lors de la participation au jeu.";
$lang['contest']["contest_already_started"] = "Le concours a déjà commencé.";
$lang['contest']["contest_already_full"] = "Ce concours déjà complet.";
$lang['contest']["contest_closed"] = "Concours clos.";
$lang['contest']["not_enough_coins"] = "Pas assez de pièces.";
$lang['contest']["not_enough_balance"] = "Pas assez d'équilibre.";
$lang['contest']["join_game_success"] = "Vous avez rejoint le concours avec succès.";
$lang["contest"]["invalid_promo_code"] = "Code promotionnel non valide. veuillez entrer un code valide.";
$lang["contest"]["allowed_limit_exceed"] = "Vous avez déjà utilisé ce code promo pendant la durée maximale.";
$lang["contest"]["promo_code_exp_used"] = "Le code promotionnel a expiré ou est déjà utilisé!";
$lang['contest']["you_already_joined_to_max_limit"] = "Vous avez déjà rejoint ce concours dans la limite maximale d'équipe.";
$lang['contest']["join_multiple_time_error"] = "Vous ne pouvez pas participer à ce concours plusieurs fois.";
$lang['contest']["you_already_joined_this_contest"]	= "Vous avez déjà rejoint ce concours par sélection sélectionnée.";
$lang['contest']["provide_a_valid_lineup_master_id"] = "Veuillez fournir un ID maître de programmation valide.";
$lang['contest']["not_a_valid_team_for_contest"] = "Pas une équipe valide pour le concours.";
$lang['contest']['exceed_promo_used_count'] = "Vous avez dépassé le nombre utilisé autorisé.";
$lang['contest']['team_detail_not_found'] = "Détails de l'équipe introuvables.";
$lang['contest']["invalid_previous_team_for_collecton"]	= "Équipe précédente non valide pour le concours sélectionné.";
$lang['contest']["team_switch_success"] = "L'équipe est passée avec succès.";
$lang['contest']["invalid_team_for_collecton"] = "Composition non valide pour le concours sélectionné.";
$lang['contest']['processing_team_pdf_data'] = "Nous traitons les données de l'équipe, elles seront bientôt disponibles.";
$lang['contest']["join_game_email_subject"] = "Votre inscription au concours est confirmée!";
$lang['contest_cancel_mail_subject'] = "[".SITE_TITLE."] Informations sur l'annulation du concours";
$lang['contest']["state_banned_error"] = "Désolé, mais les joueurs de {{STATE_LIST}} ne peuvent pas participer à un concours payant.";
$lang['contest']["process_contest_pdf"] = "Nous traitons l'équipe pdf, il sera bientôt disponible.";
$lang['contest']["state_required_error"] = "Veuillez mettre à jour l'état dans votre profil.";

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']["contest_not_found"] = "Concours introuvable.";
$lang['lineup']["contest_started"] = "Le concours a déjà commencé.";
$lang['lineup']["match_detail_not_found"] = "Détails du match introuvables.";
$lang['lineup']['invalid_collection_player'] = "Joueurs sélectionnés non valides. veuillez réinitialiser la composition de l'équipe et en créer une nouvelle.";
$lang['lineup']["lineup_not_exist"] = "Équipe n'existe pas";
$lang['lineup']['team_name_already_exist'] = "Le nom de l'équipe existe déjà.";
$lang['lineup']["lineup_team_rquired"] = "Identifiant de l'équipe de la ligue des joueurs requis.";
$lang['lineup']["lineup_player_id_required"] = "Identifiant unique du joueur requis.";
$lang['lineup']["lineup_player_team_required"] = "Identifiant de l'équipe du joueur requis.";
$lang['lineup']["position_invalid"] = "position invalide";
$lang['lineup']["salary_required"] = "salaire du joueur requis.";
$lang['lineup']["lineup_player_id_duplicate"] = "Vous ne pouvez pas sélectionner un seul joueur deux fois";
$lang['lineup']["lineup_max_limit"] = "Vous devez sélectionner %s joueurs pour créer une équipe.";
$lang['lineup']["lineup_team_limit_exceeded"] = "Veuillez corriger votre programmation. Vous pouvez sélectionner un maximum de %s joueurs dans une équipe.";
$lang['lineup']["position_exceeded_invalid"] = "Vous avez dépassé la limite de position du joueur.";
$lang['lineup']["salary_cap_not_enough"] = "Salaire des joueurs dépassant le salaire maximum disponible.";
$lang['lineup']["lineup_posisiotn_not_found"] = "Veuillez sélectionner %s player";
$lang['lineup']['already_created_same_team'] = "Vous avez déjà créé cette équipe.";
$lang['lineup']["lineup_success"] = "Vous avez créé une équipe avec succès";
$lang['lineup']["lineup_update_success"] = "Votre équipe a été mise à jour avec succès";
$lang['lineup']["lineup_captain_error"] = "Capitaine d'équipe requis";
$lang['lineup']["lineup_vice_captain_error"] = "Vice-capitaine d'équipe requis";
$lang['lineup']['team_detail_not_found'] = "Détails de l'équipe introuvables.";


$lang['captain'] = "Capitaine";
$lang['vice_captain'] = "Vice capitaine";
//private contest
$lang["enter_valid_sport_id"] = "Veuillez saisir un identifiant sport valide.";
$lang["enter_valid_season_game_uid"] = "Veuillez saisir un identifiant de correspondance valide.";

$lang['group_name_1'] ="Mega Contest";
$lang['group_description_1'] ="Participez au concours le plus chaud avec des méga prix.";

$lang['group_name_9'] ="Concours chaud";
$lang['group_description_9'] ="Personne n'a dit que ça allait être facile";

$lang['group_name_8'] ="Guerre des gangs";
$lang['group_description_8'] ="Quand votre équipe est votre arme";

$lang['group_name_2'] ="Head2Head";
$lang['group_description_2'] ="Ressentez le frisson de l'ultime face à face Fantasy.";

$lang['group_name_10'] ="Le gagnant prend tout";
$lang['group_description_10'] ="Gros risque, plus grande récompense!";

$lang['group_name_3'] ="Top 50% gagner";
$lang['group_description_3'] ="La moitié des joueurs gagnent à coup sûr. Entrez et tentez votre chance!";

$lang['group_name_11'] ="Tout le monde gagne";
$lang['group_description_11'] ="Quelque chose pour tout le monde.";

$lang['group_name_4'] ="Uniquement pour les débutants";
$lang['group_description_4'] ="Jouez votre tout premier concours maintenant";

$lang['group_name_5'] ="Plus de concours";
$lang['group_description_5'] ="Pas d'histoires! C'est votre zone pour jouer gratuitement.";

$lang['group_name_6'] ="Concours gratuit";
$lang['group_description_6'] ="Pas d'histoires! C'est votre zone pour jouer gratuitement et gagner de l'argent";

$lang['group_name_7'] ="Concours privé";
$lang['group_description_7'] ="C'est exclusif et c'est amusant! Jouez avec vos amis maintenant.";

$lang['group_name_12'] ="Concours des champions";
$lang['group_description_12'] ="Concours de champions";

$lang['module_disable_error'] = "Désolé, ce module n'est pas activé. veuillez contacter l'administrateur.";

$lang['file_upload_error'] = "Désolé, il y a un problème avec le téléchargement de fichiers. Veuillez réessayer.";

$lang['players'] = "joueurs";

$lang['module_not_activated'] = "Module non activé";

$lang["action_cant_completed_err"] = "L'action ne peut pas être terminée! Veuillez réessayer.";
$lang["problem_while_join_game_network"] = "Problème en rejoignant le jeu! Veuillez réessayer.";
//mulitple team join
$lang['contest']["select_min_one_team"] = "Veuillez sélectionner au moins une équipe.";
$lang['contest']["already_joined_with_teams"] = "Désolé, vous avez déjà rejoint ce concours par les équipes sélectionnées";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Désolé, vous ne pouvez pas rejoindre ce concours avec plus de {TEAM_LIMIT} équipes.";
$lang['contest']["problem_while_join_game_some_team"] = "Problème en rejoignant le jeu avec {TEAM_COUNT} équipes.";
$lang['contest']["multiteam_join_game_success"] = "Vous avez réussi à rejoindre le concours avec {TEAM_COUNT} équipe (s).";