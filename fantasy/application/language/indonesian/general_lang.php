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
$lang['currency_type'] = 'currency type';
$lang["same_currency_prize_type"] = "jenis mata uang dan jenis hadiah harus sama.";

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
$lang['contest']["rookie_user_not_allowed_for_this_contest"] = "Anda tidak diizinkan untuk bergabung dengan kontes ini";
$lang['contest']["max_usage_limit_code"] = "Penggunaan maksimum untuk batas kode promo ini terlampaui";

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
$lang['lineup']["allow_team_limit_error"] = "Anda tidak dapat membuat lebih dari %s tim.";
$lang['captain'] = "Captain";
$lang['vice_captain'] = "Vice Captain";

$lang['tr_lineup'] = array();
$lang['tr_lineup']["match_not_found"] = "Kecocokan tidak ditemukan.";
$lang['tr_lineup']["match_started"] = "Pertandingan sudah dimulai.";
$lang['tr_lineup']['invalid_match_player'] = "Invalid selected players. please reset team lineup and create new one.";

$lang["tournament_season_id"] = "Tournament Season ID";
$lang["user_tournament_season_id"] = "User tournament Season ID";
$lang["tournament_id"] = "Tournament ID";


$lang['tournament']["invalid_tournament"]                   ="Silakan pilih turnamen valid.";
$lang['tournament']["invalid_tournament_code"]              ="Bukan kode Turnamen valid.";
$lang['tournament']["tournament_not_found"]                 ="rincian turnamen tidak ditemukan.";
$lang['tournament']["problem_while_join_tournament"]        ="Masalah ketika bergabung turnamen.";
$lang['tournament']["match_already_started"]                ="Mencocokkan sudah dimulai.";
$lang['tournament']["tournament_closed"]                    ="Turnamen ditutup.";
$lang['tournament']["not_enough_coins"]                     ="Tidak cukup uang.";
$lang['tournament']["not_enough_balance"]                   ="keseimbangan tidak cukup.";
$lang['tournament']["join_tournament_success"]              ="Anda telah bergabung turnamen berhasil.";
$lang["tournament"]["invalid_promo_code"]                   ="kode promo valid. masukkan kode yang valid.";
$lang["tournament"]["allowed_limit_exceed"]                 ="Anda telah menggunakan promocode ini untuk waktu maksimum.";
$lang["tournament"]["promo_code_exp_used"]                  ="Promocode kadaluarsa atau sudah digunakan!";
$lang['tournament']["join_multiple_time_error"]             ="Anda tidak dapat bergabung beberapa waktu turnamen ini.";
$lang['tournament']["you_already_joined_this_contest"]="Anda sudah bergabung pertandingan ini dengan lineup yang dipilih.";
$lang['tournament']["provide_a_valid_tournament_team_id"]   ="Harap memberikan tim turnamen id valid.";
$lang['tournament']["not_a_valid_team_for_match"]           ="Bukan tim yang valid untuk pertandingan.";
$lang['tournament']['exceed_promo_used_count']              ="Anda telah melebihi diperbolehkan count digunakan.";
$lang['tournament']['team_detail_not_found']                ="Kami sedang memproses data yang tim.";
$lang['tournament']["team_switch_success"]                  ="Tim beralih berhasil.";
$lang['tournament']["invalid_team_for_match"]               ="lineup valid untuk pertandingan yang dipilih.";
$lang['tournament']['processing_team_pdf_data']             ="Kami sedang memproses data yang tim, itu akan segera tersedia.";
$lang['tournament']["join_tournament_email_subject"]        ="Turnamen Anda bergabung dikonfirmasi!";
$lang['tournament_cancel_mail_subject']                     ="[".SITE_TITLE."] Turnamen informasi pembatalan";
$lang['tournament']["process_contest_pdf"]                  ="Kami sedang memproses tim pdf, itu akan segera tersedia.";
$lang['tournament']["state_banned_error"]                   ="Maaf, tapi pemain dari {{STATE_LIST}} tidak bisa masuk dalam turnamen dibayar.";
$lang['tournament']["state_required_error"]                 ="Harap negara pembaruan dalam profil Anda.";

$lang['tournament']["join_tournament_to_continue"] ="Silakan bergabung turnamen untuk melanjutkan";
$lang['tournament']["join_match_success"] ="Anda telah bergabung pertandingan berhasil.";

$lang['tournament']["err_tournament_cancelled"] ="Turnamen ini dibatalkan";
$lang['tournament']["err_tournament_completed"] ="turnamen ini selesai";
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

$lang['module_disable_error'] = "Maaf, modul ini tidak diaktifkan. silakan hubungi admin.";

$lang['file_upload_error'] = "Maaf, ada masalah dengan pengunggahan file. Silakan coba lagi.";
$lang['players'] = "pemain";

$lang['module_not_activated'] = "Modul tidak diaktifkan";

$lang['multiple_lineup'] = 'beberapa daftar';
$lang['lineup'] ['team_generate_error'] = "Maaf, beberapa masalah saat tim menghasilkan. Silakan coba lagi.";
$lang['contest'] ["self_exclusion_limit_reached"] = "Tidak dapat mengikuti kontes, melebihi batas bergabung.";
$lang['contest'] ["state_banned_error"] = "Maaf, tetapi pemain dari {{STATE_LIST}} tidak dapat mengikuti kontes berbayar.";
$lang['contest'] ["state_required_error"] = "Harap perbarui status di profil Anda.";

$lang['invalid_booster_id'] = "Maaf, id booster tidak valid untuk pertandingan.";
$lang['save_booster_error'] = "Maaf, ada beberapa masalah saat menerapkan booster. silakan coba lagi.";
$lang['save_booster_success'] = "Penguat berhasil diterapkan.";
$lang['update_booster_success'] = "Penguat berhasil diperbarui.";
$lang['booster_only_for_dfs'] = "Maaf, Booster hanya berlaku untuk tim fantasi klasik.";
$lang['invalid_team_for_match'] = "Tim tidak valid untuk pertandingan.";

$lang['err_2nd_inning_format_contest'] = "Anda tidak dapat membuat kontes pribadi inning ke-2 untuk (T10 / Match Test)";

//bench module
$lang['max_bench_limit_error'] = "Max pemain 4 bangku diperbolehkan.";
$lang['invalid_collection_bench_player'] = "Valid dipilih pemain id. silahkan pilih pemain valid.";
$lang['bench_player_team_pl_error'] = "bench pemain harus berbeda dari pemain tim.";
$lang['bench_player_save_error'] = "Maaf, ada beberapa masalah saat menyimpan pemain bench. silakan coba lagi.";
$lang['save_bench_player_success'] = "pemain bangku berhasil disimpan.";
$lang["bench_process_waiting_error"] = "Tim akan segera tersedia, coba lagi dalam beberapa waktu.";

$lang['invalid_prize_distribution_error'] = "Detail distribusi hadiah tidak valid. Silakan coba dengan data yang benar.";
$lang['invalid_prize_pool_error'] = "Harap berikan kumpulan hadiah yang benar.";

$lang['contest']["h2h_game_join_limit_error"] = "Maaf Anda tidak dapat bergabung dengan kontes {CONTEST_LIMIT} h2h.";

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