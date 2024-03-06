<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Start of file general_lang.php */
$lang['global_error'] = 'Silakan masukkan parameter yang valid.';
$lang['invalid_status'] = 'Status Tidak Sah.';
$lang['valid_leaderboard_type'] = 'Jenis papan peringkat tidak sah.';
$lang['sports_id'] = 'sports id';
$lang['league_id'] = 'liga id';
$lang['collection_master_id'] = 'master koleksi id';
$lang['player_uid'] = 'player uid';
$lang['player_team_id'] = 'kunci pemain';
$lang['contest_id'] = 'kontes id';
$lang['contest_unique_id'] = 'kontes unique id';
$lang['lineup_master_id'] = 'daftar master id';
$lang['lineup_master_contest_id'] = 'daftar kontes master id';
$lang['season_game_uid'] = 'season game uid';
$lang['no_of_match'] = 'jumlah kecocokan';
$lang['against_team'] = 'Melawan Tim';
$lang['promo_code'] = 'Kode Promo';
$lang['match_status'] = 'Status pertandingan';
$lang['lineup'] = 'lineup';
$lang['team_name'] = 'nama tim';
$lang['format'] = 'format';
$lang['join_code'] = 'kode gabung';
$lang['prize_type'] = 'jenis hadiah';
$lang['salary_cap'] = 'batas gaji';
$lang['size'] = 'size';
$lang['size_min'] = 'ukuran min';
$lang['game_name'] = 'nama game';
$lang['game_desc'] = 'game desc';
$lang['entry_fee'] = 'biaya masuk';
$lang['prize_pool'] = 'kumpulan hadiah';
$lang['number_of_winners'] = 'jumlah pemenang';
$lang['prize_distribution_detail'] = 'detail hadiah';
$lang['disable_private_contest'] = "saat ini fitur ini dinonaktifkan oleh admin.";
$lang["contest_added_success"] = "kontes berhasil dibuat.";
$lang["contest_added_error"] = "Masalah saat kontes dibuat. Silakan coba lagi.";

//generalmessage
$lang["lineup_required"] = "lineup diperlukan";

//ContestLanguage
$lang['contest']["invalid_contest"] = "Silakan pilih kontes yang valid.";
$lang['contest']["invalid_contest_code"] = "Bukan kode Liga yang valid.";
$lang['contest']["contest_not_found"] = "Detail kontes tidak ditemukan.";
$lang['contest']["problem_while_join_game"] = "Masalah saat bergabung dengan permainan.";
$lang['contest']["contest_already_started"] = "Kontes sudah dimulai.";
$lang['contest']["contest_already_full"] = "Kontes ini sudah penuh.";
$lang['contest']["contest_closed"] = "Kontes ditutup.";
$lang['contest']["not_enough_coins"] = "Koin tidak cukup.";
$lang['contest']["not_enough_balance"] = "Saldo tidak cukup.";
$lang['contest']["join_game_success"] = "Anda berhasil mengikuti kontes.";
$lang["contest"]["invalid_promo_code"] = "Kode promo tidak valid. masukkan kode yang valid.";
$lang["contest"]["allowed_limit_exceed"] = "Anda telah menggunakan kode promo ini untuk waktu maksimum.";
$lang["contest"]["promo_code_exp_used"] = "Promocode sudah kadaluarsa atau sudah digunakan!";
$lang['contest']["you_already_joined_to_max_limit"] = "Anda telah bergabung dengan kontes ini hingga batas tim maksimum.";
$lang['contest']["join_multiple_time_error"] = "Anda tidak dapat mengikuti kontes ini berkali-kali.";
$lang['contest']["you_already_joined_this_contest"] = "Anda telah bergabung dengan kontes ini berdasarkan lineup yang dipilih.";
$lang['contest']["provide_a_valid_lineup_master_id"] = "Harap berikan nomor master daftar yang valid.";
$lang['contest']["not_a_valid_team_for_contest"] = "Bukan tim yang valid untuk kontes.";
$lang['contest']['exceed_promo_used_count'] = "Anda telah melebihi jumlah penggunaan yang diizinkan.";
$lang['contest']['team_detail_not_found'] = "Kami sedang memproses data tim.";
$lang['contest']["invalid_previous_team_for_collecton"] = "Tim sebelumnya tidak valid untuk kontes yang dipilih.";
$lang['contest']["team_switch_success"] = "Tim berhasil diganti.";
$lang['contest']["invalid_team_for_collecton"] = "Daftar tidak valid untuk kontes yang dipilih.";
$lang['contest']['processing_team_pdf_data'] = "Kami sedang memproses data tim, ini akan segera tersedia.";
$lang['contest']["join_game_email_subject"] = "Pendaftaran kontes Anda telah dikonfirmasi!";
$lang['contest_cancel_mail_subject'] = '[' .SITE_TITLE. '] Informasi pembatalan kontes';
$lang['contest']["process_contest_pdf"] = "Kami sedang memproses pdf tim, ini akan segera tersedia.";

//mulitple team join
$lang['contest']["select_min_one_team"] = "Silakan pilih setidaknya satu tim.";
$lang['contest']["already_joined_with_teams "] =" Maaf, Anda telah mengikuti kontes ini oleh tim terpilih. ";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Maaf, Anda tidak dapat mengikuti kontes ini dengan lebih dari {TEAM_LIMIT} tim.";
$lang['contest']["problem_while_join_game_some_team"] = "Masalah saat bergabung dengan permainan dengan {TEAM_COUNT} tim.";
$lang['contest']["multiteam_join_game_success"] = "Anda telah berhasil mengikuti kontes dengan {TEAM_COUNT} tim.";

//Lineup Language
$lang['lineup'] = array();
$lang['lineup']["contest_not_found"] = "Contest not found.";
$lang['lineup']["contest_started"] = "Contest already started.";
$lang['lineup']["match_detail_not_found"] = "Match details not found.";
$lang['lineup']['invalid_collection_player'] = "Invalid selected players. please reset team lineup and create new one.";
$lang['lineup']["lineup_not_exist"] = "Team Not exist";
$lang['lineup']['team_name_already_exist'] = 'Team name already exist.';
$lang['lineup']["lineup_team_rquired"] = "Player league team id required.";
$lang['lineup']["lineup_player_id_required"] = "Player unique id required.";
$lang['lineup']["lineup_player_team_required"] = "Player team id required.";
$lang['lineup']["position_invalid"] = "invalid position";
$lang['lineup']["salary_required"] = "player salary required.";
$lang['lineup']["lineup_player_id_duplicate"] = "You can't select single player twice time";
$lang['lineup']["lineup_max_limit"] = "You should select %s players to create team.";
$lang['lineup']["lineup_team_limit_exceeded"] = "Please correct your lineup. You can select maximum %s players from one team.";
$lang['lineup']["position_exceeded_invalid"] = "You have exceeded player position limit.";
$lang['lineup']["salary_cap_not_enough"] = "Players salary exceeding max available salary.";
$lang['lineup']["lineup_posisiotn_not_found"] = "Please select %s player";
$lang['lineup']['already_created_same_team'] = "You have already created this team.";
$lang['lineup']["lineup_success"] = "You have created team successfully";
$lang['lineup']["lineup_update_success"] = "Your team has been updated successfully";
$lang['lineup']["lineup_captain_error"] = "Team captain required";
$lang['lineup']["lineup_vice_captain_error"] = "Team vice captain required";
$lang['lineup']['team_detail_not_found'] = "Team details not found.";

//private contest
$lang["enter_valid_sport_id"] = "Silakan masukkan id olahraga yang valid.";
$lang["enter_valid_season_game_uid"] = "Silakan masukkan nomor pencocokan yang valid.";

$lang['group_name_1'] = "Kontes Besar";
$lang['group_description_1'] = "Ikuti kontes terpanas dengan hadiah besar.";

$lang['group_name_9'] = "Kontes Terpopuler";
$lang['group_description_9'] = "Tidak ada yang bilang ini akan mudah";

$lang['group_name_8'] = "Perang Gang";
$lang['group_description_8'] = "Ketika tim Anda adalah senjata Anda";

$lang['group_name_2'] = "Head2Head";
$lang['group_description_2'] = "Rasakan serunya menghadapi Fantasy Face off satu lawan satu.";

$lang['group_name_10'] = "Pemenang Mengambil Semua";
$lang['group_description_10'] = "Resiko Besar, Imbalan Lebih Besar!";

$lang['group_name_3'] = "Kemenangan 50% teratas";
$lang['group_description_3'] = "Separuh pemain pasti menang. Masuk dan coba keberuntungan Anda!";

$lang['group_name_11'] = "Semua Menang";
$lang['group_description_11'] = "Sesuatu untuk semua orang.";

$lang['group_name_4'] = "Hanya untuk Pemula";
$lang['group_description_4'] = "Mainkan kontes pertama Anda sekarang";

$lang['group_name_5'] = "Kontes Lainnya";
$lang['group_description_5'] = "Tidak masalah! Ini zona Anda untuk bermain secara gratis.";

$lang['group_name_6'] = "Kontes Gratis";
$lang['group_description_6'] = "Tidak masalah! Ini adalah zona Anda untuk bermain secara gratis & menangkan uang tunai";

$lang['group_name_7'] = "Kontes Pribadi";
$lang['group_description_7'] = "Eksklusif dan menyenangkan! Main bersama teman sekarang.";

$lang['group_name_12'] = "Kontes untuk Juara";
$lang['group_description_12'] = "Kontes untuk juara";

$lang["problem_while_join_game_network"] = "Masalah saat bergabung dengan game! Silakan coba lagi.";
$lang["action_cant_completed_err"] = "Tindakan tidak dapat diselesaikan! Silakan coba lagi.";
$lang['contest'] ["self_exclusion_limit_reached"] = "Tidak dapat mengikuti kontes, melebihi batas bergabung.";

//mulitple team join
$lang['contest']["select_min_one_team"] = "Pilih setidaknya satu tim.";
$lang['contest']["already_joined_with_teams"] = "Maaf, Anda telah bergabung dalam kontes ini oleh tim terpilih.";
$lang['contest']["contest_max_allowed_team_limit_exceed"] = "Maaf, Anda telah bergabung dalam kontes ini oleh tim terpilih. Maaf, Anda tidak dapat bergabung dalam kontes ini dengan lebih dari {TEAM_LIMIT} tim.";
$lang['contest']["problem_while_join_game_some_team"] = "Masalah saat bergabung dalam permainan dengan {TEAM_COUNT} tim.";
$lang['contest']["multiteam_join_game_success"] = "Anda berhasil bergabung dalam kontes dengan {TEAM_COUNT} tim.";
