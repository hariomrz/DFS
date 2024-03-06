const dotenv = require("dotenv"); // Used to set .env variable to node process env
dotenv.config({ path: '../.env' });
setTimeout(() => {
   
}, 2000);


//Upload banner path
var S3_SERVER_URL = process.env.REACT_APP_S3URL;
var S3_REWARD_URL = process.env.REACT_APP_S3URL;
export const ADMIN_FOLDER_NAME = 'adminapi/index.php/';
export const ADMIN_AUTH_KEY = 'admin_id_token';
//baseUrl coming from admin env file 
export const baseURL = process.env.REACT_APP_BASE_URL;
export const baseURLAffiliate = process.env.REACT_APP_BASE_URL_AFFILIATE;
export const ALLOW_COMMUNICATION_DASHBOARD = 1;
export const ALLOW_DEAL = 1;
export const ALLOW_DFS = 1;
export const ALLOW_FREETOPLAY = process.env.REACT_APP_ALLOW_FREETOPLAY
export const ALLOW_SPORTS_PREDICTOR = process.env.REACT_APP_ALLOW_SPORTS_PREDICTOR;

export const ALLOW_OP_WITH_POOL = process.env.REACT_APP_ALLOW_OP_WITH_POOL;
export const ALLOW_SPINTHEWHEEL = process.env.REACT_APP_ALLOW_SPINTHEWHEEL;
export const ALLOW_PRIVATE_CONTEST = process.env.REACT_APP_ALLOW_PRIVATE_CONTEST;
export const ALLOW_BUY_COIN = process.env.REACT_APP_ALLOW_BUY_COIN;
export const ALLOW_REFERRAL_SETPRIZE = process.env.REACT_APP_ALLOW_REFERRAL_LEADERBOARD;
export const SHOW_PREDICTION_CHILD = localStorage.getItem('ALLOW_PREDICTION_MODULE');
export const SHOW_OP_PREDICTION_CHILD = localStorage.getItem('ALLOW_OPEN_PREDICTOR');
export const SHOW_LF_PRIVATE_CONTEST = localStorage.getItem('LF_PRIVATE_CONTEST');
export const deviceType = "3";
export const deviceID = "";
export const leagueId = "";
export const sportsId = "7";
export const CURRENCY = "₹";
export const successCode = 200;
export const AUTHENTICATE_REQUIRE_CODE = 401;
export const sessionExpireCode = 401;
export const ITEMS_PERPAGE = 50;
export const ITEMS_PERPAGE_LG = 100;
export const CURRENT_PAGE = 1;
export const EXPORT_REPORT_LIMIT = 10000;
export const sessionKey = "";
export const SYSTEM_ERROR = "System generated an error please try again later.";
export const MODULE_NOT_ENABLE = "Module is not enabled please contact to support team.";
// export const BANNER_ERROR = "Please upload image of size should be 340x60.";
export const NO_RECORDS = "No Records Found.";
// export const NO_PARTICIPANTS = "No participants till now";
export var Locale = "";
export class Language {
    static update(data) {
        Locale = data;
    }
}
//Imnages folder dir path
export const S3_REWARD = S3_REWARD_URL;
//Imnages folder dir path
export const S3 = S3_SERVER_URL;
export const UPLOAD = 'upload/';
export const BANNER = 'upload/banner/';
export const APPBANNER = 'upload/app_banner/';
export const FLAG = 'upload/flag/';
export const JERSY = 'upload/jersey/';
export const MERCHANDISEIMG = 'upload/merchandise/';
export const SPONSER_IMG_PATH = 'upload/sponsor/';
export const SETTING_IMG_PATH = 'upload/setting/';
export const H2H_CMS = 'upload/h2h/';
export const MOTOR_SPORTS_IMG ='assets/img/';

export const WHATSNEW_IMG_PATH = 'upload/whatsnew/';
export const COMMOM_IMG_PATH = 'common/do_upload/';
export const COMMOMS_IMG_PATH = 'adminapi/common/do_upload/';
export const PAN = 'upload/pan/';
export const AADHAR = 'upload/aadhar/';
export const THUMB = 'upload/profile/thumb/';
export const BANK = 'upload/bank_document/';
export const COINS = 'upload/rewards/';
export const NOIMAGE = 'assets/img/no_image.png';
export const OP_CATEGORY = 'upload/category/';
export const OP_PROOF = 'upload/open_predictor/';
export const FX_SPONSOR_LOGO = 'upload/fixed_open_predictor/sponsor/';
export const UPLOAD_AVATAR = 'upload/profile/thumb/';
export const ABOUT_US_IMG = 'upload/cms_images/';
export const SELF_EXCLUSION_PATH = 'upload/self_exclusion/';
export const GROUP_ICON = 'assets/img/';
export const TEAM_LOGO = 'upload/flag/';
export const PUSH_HEADER = 'upload/cd/push_header/';
export const PUSH_BODY = 'upload/cd/push_body/';
export const PICKEM_TR_LOGO = 'upload/pickem_tr_logo/';
export const PICKEM_TR_SPONSOR = 'upload/pickem_tr_sponsor/';
export const PICKEM_MERCHANDISE = 'upload/pickem_merchandise/';
export const PT_TEAM_FLAG = 'pickem/upload/pt_team_flag/';
export const DFST_LOGO = 'upload/dfstournament/';
export const DFST_SPONSOR = 'upload/dfstournament/';
export const STOCK_PATH = 'upload/stock/';
export const BOOSTER = 'upload/booster/';
export const PICK_FANTASY = 'upload/picks/';
export const QUIZ_IMG = 'upload/quiz/';

//Preponsible module
export const MSG_SET_TO_DEF = "Are you sure you want to set default?";
export const MSG_SUBMIT_LIMIT = "Are you sure you want to submit limit?";
//Private contest message
export const MSG_PC_VISIBILITY = "Are you sure you want to change visibility?";

//Datepicker
export const DATE_FORMAT = 'dd/MM/yyyy';
export const DATE_DIFF = 15;


//Prediction share Url
export const PredictionShareUrl = "cricket/open-predictor-details/";
export const FixedPredictionShareUrl = "cricket/open-predictor-leaderboard-details/";
export const PickemShareUrl = "/pickem-detail/";

//FAQ question delete
export const MSG_DELETE_QUES = "Are you sure you want to delete this question?";
//New reports message
export const PRP_REPORT_MSG = "Please change your filter's to see results.";

export const GET_ALL_SPORTS = "adminapi/common/get_all_sport";
export const GET_SPORT_LEAGUES = "adminapi/common/get_sport_leagues";

//Team Module 
export const GET_ALL_TEAM_LIST = "adminapi/index.php/team/get_all_team_by_sport";
export const UPLOAD_TEAM_JERSEY = "adminapi/index.php/team/do_upload/jersey";
export const UPLOAD_TEAM_FLAG = "adminapi/index.php/team/do_upload/flag";
export const EDIT_TEAM_INFO = "adminapi/index.php/team/edit_team_details";

//League Module 
export const GET_ALL_LEAGUE_LIST = "adminapi/index.php/league/get_sport_leagues";
export const GET_ALL_FIXTURE_LIST = "adminapi/index.php/multigame/get_published_fixtures";
export const CREATE_COLLECTION = "adminapi/index.php/multigame/create_collection";
export const GET_ALL_COLLECTION_FIXTURE_LIST = "adminapi/index.php/multigame/get_fixture_list";
export const GET_COLLECTION_CONTEST_LIST = "adminapi/index.php/multigame/get_all_collection_contests";
export const GET_COLLECTION_SEASON_DETAILS = "adminapi/index.php/multigame/get_season_details";
export const GET_COLLECTION_FIXTURE_CONTEST = "adminapi/index.php/multigame/get_fixture_contest";
//multigame
export const GET_MULTIGAME_CONTEST_LIST = "adminapi/index.php/multigame/multigame/get_contest_list";
export const GET_COLLECTION_FIXTURE_TEMPLATE = "adminapi/index.php/multigame/get_fixture_template";
export const CREATE_TEMPLATE_COLLECTION_CONTEST = "adminapi/index.php/multigame/create_template_contest";
export const COLLECTION_CREATE_CONTEST = "adminapi/index.php/multigame/create_contest";
//Fixture Module
export const GET_ALL_FIXTURE = "adminapi/season/get_season_list";// "adminapi/index.php/season/get_all_season_schedule";
export const GET_SEASON_TEAMS_AND_ROSTERS = "adminapi/index.php/season/get_season_teams_and_rosters";
export const UPDATE_SEASON_PLAYER_SALARY = "adminapi/index.php/season/update_season_roster_salary";
export const PUBLISH_SEASON = "adminapi/index.php/season/publish_season";
export const GET_SEASON_DETAILS = "adminapi/index.php/season/get_season_details";

//Dashboard Module
export const GET_DASHBOARD_SUMMARY = "adminapi/index.php/dashboard/get_summary";
export const GET_DASHBOARD_TIMELINES = "adminapi/index.php/dashboard/get_timelines";
export const GET_DASHBOARD_FREEPAID_USERS = "adminapi/index.php/dashboard/get_freepaidusers";
export const GET_DASHBOARD_DEVICES = "adminapi/index.php/dashboard/get_devices";
export const GET_DASHBOARD_SITERAKE = "adminapi/index.php/dashboard/get_siterake";
export const GET_DASHBOARD_LEADERBOARD = "adminapi/index.php/dashboard/get_leaderboard";
export const GET_DASHBOARD_REFERRAL = "adminapi/index.php/dashboard/get_referral";
export const GET_CALCULATED_SUMMARY = "adminapi/index.php/dashboard/get_calculated_summary";
export const GET_DASHBOARD_SEGREGATION = "adminapi/index.php/dashboard/get_segregation";
export const GET_DASHBOARD_ACTIVE_USERS = "adminapi/index.php/dashboard/get_active_users";

//contest template
export const GET_CONTEST_TEMPLATE_MASTER_DATA = "adminapi/index.php/contest/contest_template/get_all_master_data";
export const GET_CONTEST_TEMPLATE_LIST = "adminapi/index.php/contest/contest_template/get_contest_template_list";
export const CREATE_CONTEST_TEMPLATE = "adminapi/index.php/contest/contest_template/create_template";
export const DELETE_CONTEST_TEMPLATE = "adminapi/index.php/contest/contest_template/delete_template";
export const APPLY_CONTEST_TEMPLATE_TO_LEAGUE = "adminapi/index.php/contest/contest_template/apply_template_to_league";
export const GET_FIXTURE_TEMPLATE = "adminapi/index.php/contest/contest_template/get_fixture_template";

export const UPDATE_CONTEST_AUTOPUBLISH = "adminapi/index.php/contest/contest_template/update_auto_publish_status";

//contest section
export const CREATE_TEMPLATE_CONTEST = "adminapi/index.php/contest/contest/create_template_contest";
export const GET_FIXTURE_CONTEST = "adminapi/index.php/contest/contest/get_fixture_contest";
export const GET_FIXTURE_TOURNAMENT_LIST = "adminapi/tournament/get_fixture_tournament";
export const CREATE_CONTEST = "adminapi/index.php/contest/contest/create_contest";
export const MARK_PIN_CONTEST = "adminapi/index.php/contest/contest/mark_pin_contest";
export const GET_CONTEST_FILTER = "adminapi/index.php/contest/contest/get_contest_filter";
export const GET_CONTEST_LIST = "adminapi/index.php/contest/contest/get_contest_list";
export const DELETE_CONTEST = "adminapi/index.php/contest/contest/delete_contest";
export const REMOVE_CONTEST_RECURRENCE = "adminapi/index.php/contest/contest/remove_contest_recurrence";
export const UPLOAD_SPONSER = "adminapi/index.php/contest/contest/do_upload_sponsor";
export const UPLOAD_CONTEST_TEMPLATE_SPONSER = "adminapi/index.php/contest/contest_template/do_upload_sponsor";

//user module 
export const GET_USERLIST = "adminapi/index.php/user/users";
export const CHANGE_USER_STATUS = "adminapi/index.php/user/change_user_status";
export const GET_USER_DETAIL = "adminapi/index.php/user/get_user_detail";
export const GET_USER_BASIC = "adminapi/index.php/user/get_user_basic";
export const VERIFY_USER_PANCARD = "adminapi/index.php/user/verify_user_pancard";
export const GET_USER_BANK_DATA = "adminapi/index.php/user/get_user_bank_data";
export const UPDATE_WALLET = "adminapi/index.php/user/add_user_balance";
export const GET_USER_TRANSACTION_HISTORY = "adminapi/index.php/user/user_transaction_history";
export const GET_USER_TDS_REPORT = "adminapi/index.php/user/user_tds_report";
export const GET_USER_TDS_REPORT_EXPORT = "adminapi/index.php/user/export_user_tds_report";
export const ADD_USER = "adminapi/index.php/user/add_user";
export const UPDATE_USER_LOCATION_STATUS = "adminapi/index.php/user/update_user_location_status";


//login 
export const DO_LOGIN = "adminapi/index.php/auth/dologin";
export const RESEND_OTP = "adminapi/index.php/auth/resend_otp"

//Scoring
export const GET_SCORING_FILTERS = 'adminapi/index.php/scoring/get_scoring_filters'
export const GET_SCORING_RULES = 'adminapi/index.php/scoring/get_scoring_rules'
export const UPDATE_MASTER_SCORING_POINTS = 'adminapi/index.php/scoring/update_master_scoring_points'

//CMS
export const GET_BANNERS = 'adminapi/index.php/banner/get_banners';
export const GET_BANNER_TYPE = 'adminapi/index.php/banner/get_banner_type';
export const CREATE_BANNER = 'adminapi/index.php/banner/create_banner';
export const GET_APP_BANNERS = 'adminapi/index.php/app_banner/get_app_banners';
export const ADD_APP_BANNER = 'adminapi/index.php/app_banner/add_app_banner';
export const UPDATE_STATUS = 'adminapi/index.php/app_banner/update_status';
export const DELETE_APP_BANNER = 'adminapi/index.php/app_banner/delete_app_banner';
export const GET_PAGES = 'adminapi/index.php/page/pages/';
export const GET_PAGE_DETAIL = 'adminapi/index.php/page/get_page_detail';
export const UPDATE_PAGE = 'adminapi/index.php/page/update_page';
export const UPDATE_PAGE_STATUS = 'adminapi/index.php/page/update_page_status';
export const FRONT_BG_UPLOAD = 'adminapi/index.php/setting/front_bg_upload';
export const RESET_FRONT_BG_IMAGE = 'adminapi/index.php/setting/reset_front_bg_image';
export const GET_FRONT_BG_IMAGE = 'adminapi/index.php/setting/get_front_bg_image';
export const GET_ALL_UPCOMING_COLLECTIONS = 'adminapi/index.php/contest/get_all_upcoming_collections';
export const LOBBY_IMAGE_UPLOAD = 'adminapi/index.php/banner/do_upload';
export const DELETE_LOBBY_BANNER = 'adminapi/index.php/banner/delete_banner';
export const LOBBY_UPDATE_STATUS = 'adminapi/index.php/banner/update_status';
export const APP_BANNER_UPLOAD = 'adminapi/index.php/app_banner/do_upload';

//User dashboard
export const GET_USER_NOSQL_DATA = 'adminapi/index.php/user/get_user_nosql_data';

//User dashboard
export const GET_USER_GAME_HISTORY = 'adminapi/index.php/user/user_game_history/';
export const GET_USER_REFERRAL_DATA = 'adminapi/index.php/user/get_user_referral_data';
export const GET_LINEUP_DETAIL = 'adminapi/index.php/user/get_lineup_detail';
export const GET_GAME_STATS = 'adminapi/index.php/contest/get_game_stats';

//Marketing Referral
export const GET_AFFILIATE_MASTER_DATA = 'adminapi/index.php/setting/get_affiliate_master_data';
export const UPDATE_AFFILIATE_MASTER_DATA = 'adminapi/index.php/setting/update_affiliate_master_data';
export const GET_PROMO_CODES = 'adminapi/index.php/promo_code/get_promo_codes';
export const NEW_PROMO_CODE = 'adminapi/index.php/promo_code/new_promo_code';
export const CHANGE_PROMO_STATUS = 'adminapi/index.php/promo_code/change_promo_status';
export const GET_PROMO_CODE_DETAIL = 'adminapi/index.php/promo_code/get_promo_code_detail';
export const DELETE_PROMO_CODE = 'adminapi/index.php/promo_code/delete_promo_code';

//Finance data
export const GET_WITHDRAWAL_FILTER_DATA = 'adminapi/index.php/finance/get_filter_data';
export const GET_ALL_WITHDRAWAL_REQUEST = 'adminapi/index.php/finance/get_all_withdrawal_request';
export const CHANGE_WITHDRAWAL_STATUS = 'adminapi/index.php/finance/change_withdrawal_status';
export const GET_ALL_TRANSACTION = 'adminapi/index.php/finance/get_all_transaction';
export const SEND_EMAIL_SELECTED_USER = 'adminapi/index.php/user/send_email_selected_user/';
export const GET_GAME_DETAIL = 'adminapi/index.php/contest/get_game_detail';
export const GET_GAME_LINEUP_DETAIL = 'adminapi/index.php/contest/get_game_lineup_detail';

export const ADD_NOTE = 'adminapi/index.php/user/add_note';
export const GET_NOTES = 'adminapi/index.php/user/get_notes';
export const VERIFY_BANK = 'adminapi/index.php/user/verify_user_bank';
export const VERIFY_AADHAR = 'adminapi/index.php/user/verify_user_aadhar';
export const VERIFY_AADHAR_INFO = 'adminapi/index.php/user/update_aadhar_info';
export const UPLOAD_AADHAR_DOCUMENT = 'adminapi/index.php/user/upload_aadhar_document';






//Report

export const GET_ALL_USER_REPORT = 'adminapi/index.php/report/get_all_user_report';
export const GET_REPORT_MONEY_PAID_BY_USER = 'adminapi/index.php/report/get_report_money_paid_by_user';
export const REFERAL_REPORT = 'adminapi/index.php/report/referal_report';
export const GET_DEPOSIT_AMOUNT_FILTER_DATA = 'adminapi/index.php/report/get_deposit_amount_filter_data';
export const GET_REPORT_USER_DEPOSIT_AMOUNT = 'adminapi/index.php/report/get_report_user_deposit_amount';
export const GET_ALL_CONTEST_REPORT = 'adminapi/index.php/report/get_all_contest_report';
export const GET_SPORT_LEAGUES_REPORT = "adminapi/index.php/contest/get_sport_leagues";
export const GET_ALL_COLLECTIONS_BY_LEAGUE = "adminapi/index.php/contest/get_all_collections_by_league";
export const GET_SYSTEM_USER_REPORTS = "adminapi/systemuser/get_system_user_reports";
export const GET_SYSTEM_USER_LEAGUE_LIST = "adminapi/systemuser/get_system_user_league_list";

export const GET_LANGUAGE_LIST = "adminapi/index.php/common/common/get_language_list";
export const DO_UPLOAD_LANG = "adminapi/index.php/common/common/do_upload_lang/";
export const DO_UPLOAD_MASTER_FILE = "adminapi/index.php/common/common/do_upload_master_file/";
export const GET_ALL_POSITION = "adminapi/index.php/league/get_position_list";
export const PLAYING11 = "adminapi/index.php/season/save_playing11";

//Coin module
export const GET_COIN_CONFIGURATION_DETAILS = "adminapi/index.php/coins/get_coin_configuration_details";
export const UPDATE_COINS_STATUS = "adminapi/index.php/coins/update_coins_status";
export const SAVE_COINS_CONFIGURATION = "adminapi/index.php/coins/save_coins_configuration";
export const GET_REWARD_LIST_BY_STATUS = "adminapi/index.php/coins/get_reward_list_by_status";
export const APPROVE_REWARD_REQUEST = "adminapi/index.php/coins/approve_reward_request";
export const GET_REWARD_LIST = "adminapi/index.php/coins/get_reward_list";
export const ADD_REWARD = "adminapi/index.php/coins/add_reward";
export const DO_UPLOAD_REWARD_IMAGE = "adminapi/index.php/coins/do_upload_reward_image";
export const UPDATE_REWARD_STATUS = "adminapi/index.php/coins/update_reward_status";
export const GET_REWARD_HISTORY = "adminapi/index.php/coins/get_reward_history";
export const EXPORT_REWARD_LIST_BY_STATUS = "adminapi/index.php/coins/export_reward_list_by_status";
export const GET_TOP_EARNER = "adminapi/index.php/coins/get_top_earner";
export const GET_TOP_REDEEMER = "adminapi/index.php/coins/get_top_redeemer";
//Feedback module
export const ADD_FEEDBACK_QUESTION = "adminapi/index.php/promotions/feedback/add_feedback_question";
export const GET_FEEDBACK_QUESTIONS_BY_STATUS = "adminapi/index.php/promotions/feedback/get_feedback_questions_by_status";
export const UPDATE_FEEDBACK_QUESTION_STATUS = "adminapi/index.php/promotions/feedback/update_feedback_question_status";
export const UPDATE_FEEDBACK_QUESTION = "adminapi/index.php/promotions/feedback/update_feedback_question";
export const GET_FEEDBACK_QUESTION_DETAILS = "adminapi/index.php/promotions/feedback/get_feedback_question_details";
export const COMMENTS_FOR_FEEDBACK_QUESTIONS_BY_STATUS = "adminapi/index.php/promotions/feedback/comments_for_feedback_questions_by_status";
export const GET_FEEDBACKS_BY_STATUS = "adminapi/index.php/promotions/feedback/get_feedbacks_by_status";
export const RATE_FEEDBACK = "adminapi/index.php/promotions/feedback/rate_feedback";
export const UPDATE_FEEDBACK_STATUS = "adminapi/index.php/promotions/feedback/update_feedback_status";
export const GET_COIN_DISTRIBUTED_HISTORY = "adminapi/index.php/coins/get_coin_distributed_history";
export const EXPORT_COIN_DISTRIBUTION_HISTORY = "adminapi/index.php/coins/export_coin_distribution_history";
export const EXPORT_TOP_EARNER = "adminapi/index.php/coins/export_top_earner";
export const EXPORT_TOP_REDEEMER = "adminapi/index.php/coins/export_top_redeemer";
export const COIN_REDEEM_HISTORY = "adminapi/index.php/coins/coin_redeem_history";
export const EXPORT_COIN_REDEEM_HISTORY = "adminapi/index.php/coins/export_coin_redeem_history";
export const COIN_DISTRIBUTED_GRAPH = "adminapi/index.php/coins/coin_distributed_graph";
export const COIN_REDEEM_GRAPH = "adminapi/index.php/coins/coin_redeem_graph";
export const USER_COIN_REDEEM_GRAPH = "adminapi/index.php/coins/user_coin_redeem_graph";
export const GET_PENDING_COUNTS = "adminapi/index.php/user/get_pending_counts";
//FreeToPlay Module
export const UPLOAD_MERCHANDISE_IMG = "adminapi/index.php/merchandise/do_upload";
export const ADD_MERCHANDISE = "adminapi/index.php/merchandise/add_merchandise";
export const GET_MERCHANDISE_LIST = "adminapi/index.php/merchandise/get_all_merchandise";
export const REMOVE_MERCHANDISE = "adminapi/index.php/merchandise/remove_merchandise_image";
export const GET_MERCHANDISE_BY_ID = "adminapi/index.php/merchandise/get_merchandise_by_id";
export const UPDATE_MERCHANDISE = "adminapi/index.php/merchandise/update_merchandise";

//Prediction module
export const GET_PREDICTION_STATUS = "adminapi/prediction/get_prediction_status";
export const UPDATE_PREDICTION_STATUS = "adminapi/prediction/update_prediction_status";
export const GET_SEASON_LIST = "adminapi/prediction/get_season_list";
export const CREATE_PREDICTION = "adminapi/prediction/create_prediction";
export const UPDATE_PIN_PREDICTION = "adminapi/prediction/update_pin_prediction";
export const PAUSE_PLAY_PREDICTION = "adminapi/prediction/pause_play_prediction";
export const GET_ALL_PREDICTION = "adminapi/prediction/get_all_prediction";
export const GET_PREDICTION_COUNTS = "adminapi/prediction/get_prediction_counts";
export const GET_TRENDING_PREDICTIONS = "adminapi/prediction/get_trending_predictions";
export const GET_PREDICTION_PARTICIPANTS = "adminapi/prediction/get_prediction_participants";
export const SUBMIT_PREDICTION_ANSWER = "adminapi/prediction/submit_prediction_answer";
export const DELETE_PREDICTION = "adminapi/prediction/delete_prediction";
export const MOST_WIN_LEADERBOARD = "adminapi/prediction/most_win_leaderboard";
export const MOST_BID_LEADERBOARD = "adminapi/prediction/most_bid_leaderboard";
export const GET_TOP_TEAM_GRAPH = "adminapi/prediction/get_top_team_graph";
export const GET_COINS_VS_USERS_GRAPH = "adminapi/prediction/get_coins_vs_users_graph";
export const UPDATE_PREDICTION = "adminapi/prediction/UPDATE_PREDICTION";
export const UPDATE_PREDICTION_FEED = "adminapi/prediction/add_prediction_feed";

//Open Predictor module
export const OP_GET_PREDICTION_STATUS = "adminapi/open_predictor/get_prediction_status";
export const OP_UPDATE_PREDICTION_STATUS = "adminapi/open_predictor/update_prediction_status";
export const OP_GET_SEASON_LIST = "adminapi/open_predictor/get_season_list";
export const OP_CREATE_PREDICTION = "adminapi/open_predictor/create_prediction";
export const OP_UPDATE_PIN_PREDICTION = "adminapi/open_predictor/update_pin_prediction";
export const OP_PAUSE_PLAY_PREDICTION = "adminapi/open_predictor/pause_play_prediction";
export const OP_GET_ALL_PREDICTION = "adminapi/open_predictor/get_all_prediction";
export const OP_GET_PREDICTION_COUNTS = "adminapi/open_predictor/get_prediction_counts";
export const OP_GET_TRENDING_PREDICTIONS = "adminapi/open_predictor/get_trending_predictions";
export const OP_GET_PREDICTION_PARTICIPANTS = "adminapi/open_predictor/get_prediction_participants";
export const OP_SUBMIT_PREDICTION_ANSWER = "adminapi/open_predictor/submit_prediction_answer";
export const OP_DELETE_PREDICTION = "adminapi/open_predictor/delete_prediction";
export const OP_MOST_WIN_LEADERBOARD = "adminapi/open_predictor/most_win_leaderboard";
export const OP_MOST_BID_LEADERBOARD = "adminapi/open_predictor/most_bid_leaderboard";
export const OP_GET_TOP_CATEGORY_GRAPH = "adminapi/open_predictor/get_top_category_graph";
export const OP_GET_COINS_VS_USERS_GRAPH = "adminapi/open_predictor/get_coins_vs_users_graph";
export const OP_GET_CATEGORY_LIST_BY_STATUS = "adminapi/open_predictor/get_category_list_by_status";
export const OP_ADD_CATEGORY = "adminapi/open_predictor/add_category";
export const OP_DO_UPLOAD = "adminapi/open_predictor/do_upload";
export const OP_DO_UPLOAD_PROOF_IMAGE = "adminapi/open_predictor/do_upload_proof_image";
export const OP_GET_ALL_CATEGORY = "adminapi/open_predictor/get_all_category";
export const OP_DELETE_CATEGORY = "adminapi/open_predictor/delete_category";
export const OP_UPDATE_CATEGORY = "adminapi/open_predictor/update_category";
export const OP_UPDATE_PREDICTION_PROOF = "adminapi/open_predictor/update_prediction_proof";
export const OP_UPDATE_PREDICTION = "adminapi/open_predictor/update_prediction";

//Start fixed open predictor
export const FIXED_OP_GET_PREDICTION_STATUS = "adminapi/fixed_open_predictor/get_prediction_status";
export const FIXED_OP_UPDATE_PREDICTION_STATUS = "adminapi/fixed_open_predictor/update_prediction_status";
export const FIXED_OP_GET_SEASON_LIST = "adminapi/fixed_open_predictor/get_season_list";
export const FIXED_OP_CREATE_PREDICTION = "adminapi/fixed_open_predictor/create_prediction";
export const FIXED_OP_UPDATE_PIN_PREDICTION = "adminapi/fixed_open_predictor/update_pin_prediction";
export const FIXED_OP_PAUSE_PLAY_PREDICTION = "adminapi/fixed_open_predictor/pause_play_prediction";
export const FIXED_OP_GET_ALL_PREDICTION = "adminapi/fixed_open_predictor/get_all_prediction";
export const FIXED_OP_GET_PREDICTION_COUNTS = "adminapi/fixed_open_predictor/get_prediction_counts";
export const FIXED_OP_GET_TRENDING_PREDICTIONS = "adminapi/fixed_open_predictor/get_trending_predictions";
export const FIXED_OP_GET_PREDICTION_PARTICIPANTS = "adminapi/fixed_open_predictor/get_prediction_participants";
export const FIXED_OP_SUBMIT_PREDICTION_ANSWER = "adminapi/fixed_open_predictor/submit_prediction_answer";
export const FIXED_OP_DELETE_PREDICTION = "adminapi/fixed_open_predictor/delete_prediction";
export const FIXED_OP_MOST_WIN_LEADERBOARD = "adminapi/fixed_open_predictor/most_win_leaderboard";
export const FIXED_OP_MOST_BID_LEADERBOARD = "adminapi/fixed_open_predictor/most_bid_leaderboard";
export const FIXED_OP_GET_TOP_CATEGORY_GRAPH = "adminapi/fixed_open_predictor/get_top_category_graph";
export const FIXED_OP_GET_COINS_VS_USERS_GRAPH = "adminapi/fixed_open_predictor/get_coins_vs_users_graph";
export const FIXED_OP_GET_CATEGORY_LIST_BY_STATUS = "adminapi/fixed_open_predictor/get_category_list_by_status";
export const FIXED_OP_ADD_CATEGORY = "adminapi/fixed_open_predictor/add_category";
export const FIXED_OP_DO_UPLOAD = "adminapi/fixed_open_predictor/do_upload";
export const FIXED_OP_DO_UPLOAD_PROOF_IMAGE = "adminapi/fixed_open_predictor/do_upload_proof_image";
export const FIXED_OP_GET_ALL_CATEGORY = "adminapi/fixed_open_predictor/get_all_category";
export const FIXED_OP_DELETE_CATEGORY = "adminapi/fixed_open_predictor/delete_category";
export const FIXED_OP_UPDATE_CATEGORY = "adminapi/fixed_open_predictor/update_category";
export const FIXED_OP_UPDATE_PREDICTION_PROOF = "adminapi/fixed_open_predictor/update_prediction_proof";
export const FIXED_UPDATE_PRIZES = "adminapi/fixed_open_predictor/update_prizes";
export const DO_UPLOAD_SPONSOR_IMAGE = "adminapi/fixed_open_predictor/do_upload_sponsor_image";
export const GET_PREDICTION_PRIZES = "adminapi/fixed_open_predictor/get_prediction_prizes";
export const GET_LEADERBOARD_MASTER_DATA = "adminapi/fixed_open_predictor/get_leaderboard_master_data";
export const GET_OPEN_PREDICTOR_LEADERBOARD = "adminapi/fixed_open_predictor/get_open_predictor_leaderboard";
export const FIXED_GET_ATTEMPTS_VS_USERS_GRAPH = "adminapi/fixed_open_predictor/get_attempts_vs_users_graph";
export const FIXED_MOST_CORRECT_PREDICTIONS_LEADERBOARD = "adminapi/fixed_open_predictor/most_correct_predictions_leaderboard";
export const FIXED_MOST_ATTEMPTS_LEADERBOARD = "adminapi/fixed_open_predictor/most_attempts_leaderboard";
export const FIXED_UPDATE_PREDICTION = "adminapi/fixed_open_predictor/update_prediction";
//End fixed open predictor

//Match Closer
export const GET_SEASON_STATS = "adminapi/index.php/season/get_season_stats";
export const UPDATE_MATCH_STATUS = "adminapi/index.php/season/update_match_status";
export const UPDATE_PLAYER_MATCH_SCORE = "adminapi/index.php/season/update_player_match_score";
export const RECALCULATE_MATCH_SCORE = "adminapi/index.php/season/recalculate_match_score";

//Pickem
export const CREATE_LEAGUE = "pickem/admin/manual_league/create_league";
export const GET_LEAGUES = "pickem/admin/pickem/get_pickem_leagues";
export const GET_LEAGUES_NEW = "pickem/admin/manual_league/get_leagues";
export const DO_UPLOAD_FLAG = "pickem/admin/manual_team/do_upload/flag";
export const CREATE_TEAM_STATS = "pickem/admin/manual_team/create_team_stats";
export const GET_TEAMS = "pickem/admin/manual_team/get_teams";

export const CREATE_PICKEM = "pickem/admin/pickem/create_pickem";

export const GET_ALL_PICKEM = "pickem/admin/pickem/get_all_pickem";
export const UPDATE_PICKEM_RESULT = "pickem/admin/pickem/update_pickem_result";
export const DELETE_PICKEM = "pickem/admin/pickem/delete_pickem";
export const EDIT_TEAM = "pickem/admin/manual_team/edit_team";
export const EDIT_LEAGUE = "pickem/admin/manual_league/edit_league";
export const GET_UNPUBLISHED_MATCHES = "pickem/admin/pickem/get_unpublished_matches";
export const PUBLISH_MATCH_PICKEM = "pickem/admin/pickem/publish_match_pickem";

export const GET_PICKEM_PARTICIPANTS = "pickem/admin/pickem/get_pickem_participants";

export const GET_COIN_CONFIG = "adminapi/pickem/get_coin_config";
export const SAVE_COIN_CONFIG = "adminapi/pickem/save_coin_config";
export const GET_TRENDING_PICKEMS = "adminapi/pickem/get_trending_pickems";
export const GET_PICKEM_COUNTS = "adminapi/pickem/get_pickem_counts";
export const MOST_WIN_LEADERBOARD_PEMS = "adminapi/pickem/most_win_leaderboard";
export const MOST_BID_LEADERBOARD_PEMS = "adminapi/pickem/most_bid_leaderboard";
export const PEM_GET_COINS_VS_USERS_GRAPH = "adminapi/pickem/get_coins_vs_users_graph";
export const PEM_GET_TOP_TEAM_GRAPH = "adminapi/pickem/get_top_team_graph";

//Reports
export const EXPORT_REPORT = 'adminapi/index.php/report/export_report';

//Cancel Collection
export const CANCEL_COLLECTION = "adminapi/index.php/contest/cancel_collection"
export const CANCEL_CONTEST = "adminapi/index.php/contest/cancel_contest"
//Match Delay 
export const UPDATE_FIXTURE_CUSTOM_MESSAGE = "adminapi/index.php/season/update_fixture_custom_message";
export const UPDATE_FIXTURE_DELAY = "adminapi/index.php/season/update_fixture_delay";

//Update salary 
export const GET_SEASON_PLAYERS = "adminapi/index.php/season/get_season_players";
export const UPDATE_SEASON_PLAYER = "adminapi/index.php/season/update_season_player";
export const PUBLISH_FIXTURE = "adminapi/index.php/season/publish_fixture";

//Create Minileague
export const GET_LIVE_UPCOMING_LEAGUE = "adminapi/index.php/tournament/get_live_upcoming_leagues";
export const GET_LEAGUE_SEASIONS = "adminapi/index.php/tournament/get_league_seasons";


export const GET_LEAGUE_LIST_MINILEAGUE = "adminapi/index.php/mini_league/get_all_leagues";
export const GET_LEAGUE_SEASIONS_MINILEAGUE = "adminapi/index.php/mini_league/get_league_seasons";
export const CREATE_MINILEAGUE = "adminapi/index.php/mini_league/create_mini_league";
export const GET_MINILEAGUE_LIST = "adminapi/index.php/mini_league/get_mini_league_list";
export const UPLOAD_MINILEAGUE_SPONSER = "adminapi/index.php/mini_league/do_upload_sponsor";
export const GET_MINILEAGUE_DETAIL = "adminapi/index.php/mini_league/get_mini_league_detail";
export const GET_MINILEAGUE_LEADERBOARD = "adminapi/index.php/mini_league/mini_league/get_mini_league_leaderboard";
export const CREATE_F2P_CONTEST = "adminapi/index.php/contest/freetoplay/create_contest";
export const UPDATE_MINILEAGUE = "adminapi/index.php/mini_league/update_mini_league";
export const UPDATE_MINILEAGUE_FIXTURE = "adminapi/index.php/mini_league/add_mini_league_seasons ";

//Change Password
export const CHANGE_PASSWORD = 'adminapi/setting/change_password';

//System User
export const GET_USERS = "adminapi/systemuser/get_users";
export const CREATE_USER = "adminapi/systemuser/create_user";
export const GET_CONTEST_DETAIL = "adminapi/systemuser/get_contest_detail";
export const GET_SYSTEM_USERS_FOR_CONTEST = "adminapi/systemuser/get_system_users_for_contest";
export const GET_CONTEST_JOINED_SYSTEM_USERS = "adminapi/systemuser/get_contest_joined_system_users";
export const JOIN_SYSTEM_USERS = "adminapi/systemuser/join_system_users";
export const DELETE_USER = "adminapi/systemuser/delete_user";
export const UPDATE_USER = "adminapi/systemuser/update_user";
export const SU_DO_UPLOAD = "adminapi/systemuser/do_upload";
export const SU_REMOVE_PROFILE_IMAGE = "adminapi/systemuser/remove_profile_image";

export const UPLOAD_MINILEAGUE_BGIMAGE = "adminapi/index.php/mini_league/do_upload_bg";

export const UPLOAD_ABOUT_US = 'adminapi/page/upload_about_us';
export const REMOVE_IMAGE = 'adminapi/page/remove_image';


//New FAQ 
export const GET_FAQ_CATEGORY = "adminapi/page/get_faq_category";
export const ADD_QUESTION_ANSWER = "adminapi/page/add_question_answer";
export const GET_FAQ_QUESTION_ANSWER = "adminapi/page/get_faq_question_answer";
export const DELETE_QUESTION_ANSWER = "adminapi/page/delete_question_answer";
export const UPDATE_QUESTION_ANSWER = "adminapi/page/update_question_answer";

//Admin roles
export const GET_ADMIN_ROLES = "adminapi/index.php/roles/admin_roles_key";
export const ROLES_LIST = "adminapi/index.php/roles/roles_list";
export const ADD_ROLES = "adminapi/index.php/roles/add_roles";
export const GET_ROLES_DETAIL = "adminapi/index.php/roles/get_roles_detail";
export const DELETE_ROLES = "adminapi/index.php/roles/delete_roles";
export const UPDATE_ROLES = "adminapi/index.php/roles/update_roles";

//Avatars
export const GET_ALL_AVATARS = "adminapi/avatars/avatars/get_all_avatars";
export const CHANGE_AVATAR_STATUS = "adminapi/avatars/avatars/change_avatar_status";
export const AVATAR_DO_UPLOAD = "adminapi/avatars/avatars/do_upload";
export const SUBMIT_AVATARS = "adminapi/avatars/avatars/submit_avatars";

//Forgot Password 
export const RESET_PASSWORD = "adminapi/index.php/auth/reset_password";
// Start New Communication Dashboard
export const GET_SEGEMENTATION_TEMPLATE_LIST = "adminapi/communication_dashboard/user_segmentation/get_segementation_template_list";
// End New Communication Dashboard

//Spinthewheel
export const WHEEL_SLICES_LIST = "adminapi/index.php/spinthewheel/wheel_slices_list";
export const SLICES_UPDATE = "adminapi/index.php/spinthewheel/slices_update";

//Distributor
export const GET_ADMIN_LIST = "adminapi/index.php/distributor/get_admin_list";
export const ADD_DISTRIBUTOR = "adminapi/index.php/distributor/add_admin";
export const GET_ADMIN_DETAIL = "adminapi/index.php/distributor/get_admin_detail";
export const GET_RECHARGE_REQUEST_LIST = "adminapi/index.php/distributor/get_recharge_request_list";
export const DISTRIBUTOR_IMAGE_UPLOAD = 'adminapi/index.php/distributor/do_upload';
export const RECHARGE_REQUEST = 'adminapi/index.php/distributor/do_recharge';
export const APPROVE_RECHARGE_REQUEST = 'adminapi/index.php/distributor/approve_recharge';
export const DISTRIBUTOR_RECHARGE_USER = "adminapi/index.php/distributor/recharge_user";
export const DISTRIBUTOR_SEARCH_USER = "adminapi/index.php/distributor/get_search_user";
export const DISTRIBUTOR_RECHARGE_LIST = "adminapi/index.php/distributor/get_recharge_list";
export const DISTRIBUTOR_STATE_LIST = "adminapi/index.php/distributor/get_all_state_by_country";
export const CHANGE_DISTRIBUTOR_STATUS = "adminapi/index.php/distributor/change_status";
export var ADMIN_ROLE = '';
export class Role {
    static setRole(data) {
        ADMIN_ROLE = data;
    }
}
export const RECHARGE_SLIP = S3_SERVER_URL + 'upload/recharge_slip/'

// Start affiliate
export const AFFI_UPDATE_AFFILIATE = "adminapi/affiliate_users/update_affiliate";
export const AFFI_GET_PENDING_AFFILIATE = "adminapi/affiliate_users/get_pending_affiliate";
export const AFFI_GET_COMMISSION_GRAPH = "adminapi/affiliate_users/get_commission_graph";
export const AFFI_USERS = "adminapi/affiliate_users/users";
export const AFFI_RAKE_UPDATE = "adminapi/affiliate_users/update_users_rake";
export const AFFI_GET_AFFILIATE_RECORDS = "adminapi/affiliate_users/get_affiliate_records";
export const AFFI_GET_SIGNUP_GRAPH = "adminapi/affiliate_users/get_signup_graph";
export const AFFI_GET_DEPOSIT_GRAPH = "adminapi/affiliate_users/get_deposit_graph";
export const GET_AFFFILIATE_MATCH_REPORT = "adminapi/affiliate_users/affliate_match_report";
export const GET_AFFILIATE_LEAGUES_REPORT = "adminapi/affiliate_users/get_affiliate_sport_leagues";
export const GET_AFFILIATE_MATCH_REPORT = "adminapi/affiliate_users/get_affiliate_match_by_leagues";
export const GET_TOTAL_SITE_RAKE_COMMISSION = "adminapi/affiliate_users/get_total_affiliate_site_rake";
// End affiliate


export const UPDATE_WITHDRAWAL_STATUS = 'adminapi/index.php/finance/update_withdrawal_status';

export const UPDATE_OTP_BLOCKED_USERS = "adminapi/user/update_otp_blocked_users";

//Start configuration
export const GET_SPORTS_HUB_LIST = "adminapi/setting/get_sports_hub_list";
export const UPDATE_SPORTS_HUB = "adminapi/index.php/setting/update_sports_hub";
export const GET_BANNER_IMAGE_DATA = "adminapi/setting/get_banner_image_data";
export const UPDATE_BANNER_IMAGE_DATA = "adminapi/setting/update_banner_image_data";
export const SETT_BANNER_UPLOAD = "adminapi/setting/banner_upload";
export const SETT_REMOVE_BANNER = "adminapi/setting/remove_banner";
export const HUB_IMAGE_DO_UPLOAD = "adminapi/setting/hub_image_do_upload";
export const TOGGLE_BANNER_IMAGE_STATUS = "adminapi/setting/toggle_banner_image_status";
export const GET_SPORTS_DISPLAY_NAME = "adminapi/setting/get_sports_display_name";
export const UPDATE_SPORTS_DISPLAY_NAME = "adminapi/setting/update_sports_display_name";
export const REVERT_TO_ORIGNAL_HUB = "adminapi/setting/revert_to_orignal_hub";

export const UPDATE_SPORTSHUB_HUB_ORDER = "adminapi/setting/update_sporthub_order";
export const GET_HUB_ICON_BANNER = "adminapi/setting/get_hub_icon_banner";
export const WLT_GET_CONTENT = "adminapi/setting/get_content";
export const WLT_UPDATE_CONTENT = "adminapi/setting/update_content";
export const GET_MIN_MAX_WITHDRAWL_LIMIT = "adminapi/setting/get_min_max_withdrawl_limit";
export const UPDATE_MIN_MAX_WITHDRAWL_LIMIT = "adminapi/setting/update_min_max_withdrawl_limit";
//End configuration
export const GET_EMAIL_SETTING = "adminapi/setting/get_email_setting";
export const SAVE_EMAIL_SETTING_STATUS = "adminapi/setting/save_email_setting_status";

export const GET_PRIZE_CRON_SETTING = "adminapi/setting/get_prize_cron_setting";
export const SAVE_PRIZE_CRON_STATUS = "adminapi/setting/save_prize_cron_status";

//Start buy Coin module
export const ADD_PACKAGE = "adminapi/index.php/coins_package/add_package";
export const PACKAGE_UPDATE = "adminapi/index.php/coins_package/package_update";
export const PACKAGE_LIST = "adminapi/index.php/coins_package/package_list";
export const PACKAGE_REDEEM_LIST = "adminapi/index.php/coins_package/package_redeem_list";
//End buy coin module
//Start private contest
export const PC_DASHBOARD_DATA = "adminapi/private_contest/dashboard_data";
export const PC_CREATED_GRAPH = "adminapi/private_contest/get_private_contest_created_graph";
export const PC_USER_SIGNUP_GRAPH = "adminapi/private_contest/get_new_user_signup_graph";
export const PC_GET_SETTINGS_DATA = "adminapi/private_contest/get_settings_data";
export const PC_TOGGLE_VISIBILITY = "adminapi/private_contest/toggle_private_contest_visibility";
export const PC_UPDATE_SITE_RAKE = "adminapi/private_contest/update_site_rake";
export const PC_UPDATE_HOST_RAKE = "adminapi/private_contest/update_host_rake";
export const PC_GET_USER__DATA = "adminapi/user/get_user_private_contests_data";
export const PC_GET_USER_LIST = "adminapi/user/get_user_private_contests_list ";
//End private contest


//Start Responsible gaming module
export const SELF_EXCLUSION = "adminapi/index.php/user/self_exclusion";
export const UPDATE_SELF_EXCLUSION_LIMIT = "adminapi/index.php/setting/update_self_exclusion_limit";
export const SELF_EXCLUSION_DOCUMENT_UPLOAD = "adminapi/index.php/user/self_exclusion_document_upload";
export const SET_SELF_EXCLUSION = "adminapi/index.php/user/set_self_exclusion";
export const SET_DEFAULT_SELF_EXCLUSION = "adminapi/index.php/user/set_default_self_exclusion";
export const GET_USER_SELF_EXCLUSION = "adminapi/index.php/user/get_user_self_exclusion";
//End Responsible gaming module
export const GET_APP_ADMIN_CONFIG = "adminapi/setting/get_app_admin_config";
export const GET_APP_MASTER_LIST = "adminapi/auth/get_app_master_list";
export const SAVE_CONFIG = "adminapi/setting/save_config";

//New reports
export const NR_GET_COLLECTION_CONTEST = "adminapi/contest/contest/get_collection_contest";
export const NR_GET_ALL_COLLECTIONS = "adminapi/contest/contest/get_all_collections";
export const NR_GET_CONTEST_PARTICIPANT_REPORT = "adminapi/contest/contest/get_contest_participant_report";







//Start code for Add contest category
export const CREATE_GROUP = "adminapi/contest/contest_template/create_group";
export const UPDATE_GROUP = "adminapi/contest/contest_template/update_group";
export const GET_GROUP = "adminapi/contest/contest_template/get_group";
export const UPLOAD_GROUP_ICON = "adminapi/contest/contest_template/upload_group_icon";
export const REMOVE_GROUP_ICON = "adminapi/contest/contest_template/remove_group_icon";
export const DELETE_GROUP = "adminapi/contest/contest_template/inactive_group";
//End code for Add contest category
export const UPLOAD_SYSTEMUSER = "adminapi/index.php/systemuser/upload_systemuser";

//Referral set prize
export const GET_REFERRAL_PRIZES = "adminapi/referral/get_referral_prizes";
export const REFERRAL_UPDATE_PRIZES = "adminapi/referral/update_prizes";
export const GET_REFERRAL_LEADERBOARD_MASTER_DATA = "adminapi/referral/get_referral_leaderboard_master_data";
export const GET_REFERRAL_LEADERBOARD = "adminapi/referral/get_referral_leaderboard";

//KYC edit module
export const UPDATE_PAN_INFO = "adminapi/user/update_pan_info";
export const UPDATE_BANK_AC_DETAIL = "adminapi/user/update_bank_ac_detail";
export const UPLOAD_PAN = "adminapi/user/upload_pan";
export const UPLOAD_BANK_DOCUMENT = "adminapi/user/upload_bank_document";
export const DO_UPLOAD_SPONSOR_CONTEST_DTL = "adminapi/index.php/contest/contest_template/do_upload_sponsor_contest_dtl";
//Network Game 
export const GET_ALL_NETWORK_CONTEST = 'adminapi/nw_contest/get_all_network_contest';
export const PUBLISH_NETWORK_CONTEST = 'adminapi/nw_contest/publish_network_contest';
export const GET_NETWORK_CONTEST_DETAILS = 'adminapi/nw_contest/get_network_contest_details';
export const GET_NETWORK_CONTEST_PARTICIPANTS = 'adminapi/nw_contest/get_network_contest_participants';
export const GET_NETWORK_LINEUP_DETAIL = 'adminapi/nw_contest/get_network_lineup_detail';
export const GET_CONTEST_COMMISSION_HISTORY = 'adminapi/nw_contest/get_contest_commission_history';
export const GET_ALL_NW_CONTEST_REPORT = 'adminapi/nw_contest/get_all_nw_contest_report';
export const GET_NW_CONTEST_REPORT_FILTERS = 'adminapi/nw_contest/get_nw_contest_report_filters';
export const GET_NW_COLLECTION_LIST = 'adminapi/nw_contest/get_nw_collection_list';
export const EXPORT_NW_CONTEST_REPORT = 'adminapi/nw_contest/export_nw_contest_report';
//Upload Cumtom notification img
export const HEADER_IMAGE = "adminapi/communication_dashboard/user_segmentation/do_upload/header_image";
export const BODY_IMAGE = "adminapi/communication_dashboard/user_segmentation/do_upload/body_image";

//Pickem tournament
export const PT_SAVE_SPORTS = "adminapi/index.php/roles/add_roles";
export const PT_UPDATE_SPORTS = "adminapi/index.php/roles/add_roles";
export const PT_DELETE_SPORTS = "adminapi/index.php/roles/add_roles";
export const PT_ENABLE_SPORTS = "adminapi/index.php/roles/add_roles";

export const PT_CREATE_TOURNAMENT = "pickem/admin/tournament/create_tournament";
export const PT_GET_UPCOMING_FIXTURES = "pickem/admin/tournament/get_upcoming_fixtures";
export const PT_DO_UPLOAD_LOGO = "pickem/admin/tournament/do_upload_logo";
export const PT_DO_UPLOAD_SPONSOR = "pickem/admin/tournament/do_upload_sponsor";
export const PT_GET_TOURNAMENT_MASTER_DATA = "pickem/admin/tournament/get_tournament_master_data";
export const PT_GET_ALL_TOURNAMENT = "pickem/admin/tournament/get_all_tournament";
export const PT_GET_TOURNAMENT_FIXTURES = "pickem/admin/tournament/get_tournament_fixtures";
export const PT_DELETE_PICKEM = "pickem/admin/pickem/delete_pickem";
export const PT_ADD_MATCHES_TO_TOURNAMENT = "pickem/admin/tournament/add_matches_to_tournament";
export const PT_GET_TOURNAMENT_EDIT_DATA = "pickem/admin/tournament/get_tournament_edit_data";
export const PT_UPDATE_TOURNAMENT_SEASON_RESULT = "pickem/admin/tournament/update_tournament_season_result";//live match result on details screen
export const PT_UPLOAD_MERCHANDISE_IMG = "pickem/admin/merchandise/do_upload";
export const PT_ADD_MERCHANDISE = "pickem/admin/merchandise/add_merchandise";
export const PT_GET_MERCHANDISE_LIST = "pickem/admin/merchandise/get_all_merchandise";
export const PT_REMOVE_MERCHANDISE = "pickem/admin/merchandise/remove_merchandise_image";
export const PT_GET_MERCHANDISE_BY_ID = "pickem/admin/merchandise/get_merchandise_by_id";
export const PT_UPDATE_MERCHANDISE = "pickem/admin/merchandise/update_merchandise";
export const PT_REMOVE_TOURNAMENT_LOGO = "pickem/admin/tournament/remove_tournament_logo";
export const PT_UPDATE_TOURNAMENT_RESULT = "pickem/admin/tournament/update_tournament_result";
export const PT_REMOVE_TOURNAMENT_BANNER = "pickem/admin/tournament/remove_tournament_banner";
export const PT_GET_TOURNAMENT_PARTICIPANTS = "pickem/admin/tournament/get_tournament_participants";
export const PT_GET_TOURNAMENT_LEADERBOARD = "pickem/admin/tournament/get_tournament_leaderboard";
export const PT_CANCEL_TOURNAMENT = "pickem/admin/tournament/cancel_tournament";
export const PT_DELETE_TOURNAMENT_PICKEM = "pickem/admin/tournament/delete_tournament_match";









//ERP finance
export const ERP_GET_MASTER_DATA = "adminapi/finance_erp/get_master_data";
export const ERP_GET_DASHBOARD_DATA = "adminapi/finance_erp/get_dashboard_data";
export const ERP_GET_TRANSACTION_LIST = "adminapi/finance_erp/get_transaction_list";
export const ERP_SAVE_TRANSACTION = "adminapi/finance_erp/save_transaction";
export const ERP_UPDATE_TRANSACTION = "adminapi/finance_erp/update_transaction";
export const ERP_DELETE_TRANSACTION = "adminapi/finance_erp/delete_transaction";
export const ERP_GET_CATEGORY_LIST = "adminapi/finance_erp/get_category_list";
export const ERP_SAVE_CATEGORY = "adminapi/finance_erp/save_category";
export const ERP_UPDATE_CATEGORY = "adminapi/finance_erp/update_category";
//Netwok game system user
export const NG_GET_CONTEST_DETAIL = "adminapi/nw_contest/nw_systemuser/get_contest_detail";
export const NG_GET_SYSTEM_USERS_FOR_CONTEST = "adminapi/nw_contest/nw_systemuser/get_system_users_for_contest";
export const NG_GET_CONTEST_JOINED_SYSTEM_USERS = "adminapi/nw_contest/nw_systemuser/get_contest_joined_system_users";
export const NG_JOIN_SYSTEM_USERS = "adminapi/nw_contest/nw_systemuser/join_system_users";



//Leaderboards
export const GET_REFERRAL_RANK = "adminapi/dashboard/get_referral_rank";
export const GET_APP_USAGE_DATA = "adminapi/dashboard/get_app_usage_data";
export const GET_ALL_SEASON_WEEKS = "adminapi/season/get_all_season_weeks";
export const GET_WEEK_SEASONS = "adminapi/season/get_week_seasons";


//Dfs Tournament
export const DFST_GET_TOURNAMENT_MASTER_DATA = "adminapi/tournament/get_tournament_master_data";
export const DFST_DO_UPLOAD_LOGO = "adminapi/tournament/do_upload_logo";
export const DFST_DO_UPLOAD_SPONSOR = "adminapi/tournament/do_upload_sponsor";
export const DFST_REMOVE_TOURNAMENT_LOGO = "adminapi/tournament/remove_tournament_logo";
export const DFST_REMOVE_TOURNAMENT_BANNER = "adminapi/tournament/remove_tournament_banner";
export const DFST_GET_ALL_TOURNAMENT = "adminapi/tournament/get_all_tournament";
export const DFST_CREATE_TOURNAMENT = "adminapi/tournament/create_tournament";
export const DFST_GET_UPCOMING_FIXTURES = "adminapi/tournament/get_upcoming_fixtures";
export const DFST_GET_TOURNAMENT_FIXTURES = "adminapi/tournament/get_tournament_fixtures";
export const DFST_GET_TOURNAMENT_PARTICIPANTS = "adminapi/tournament/get_tournament_participants";
export const DFST_GET_TOURNAMENT_SEASON_PARTICIPANTS = "adminapi/tournament/get_tournament_season_participants";
export const DFST_GET_TOURNAMENT_EDIT_DATA = "adminapi/tournament/get_tournament_edit_data";
export const DFST_UPDATE_TOURNAMENT = "adminapi/tournament/update_tournament";
export const DFST_GET_TOURNAMENT_LEADERBOARD = "adminapi/tournament/get_tournament_leaderboard";
export const DFST_GET_TOURNAMENT_SEASON_LEADERBOARD = "adminapi/tournament/get_tournament_season_leaderboard";
// export const DFST_CANCEL_TOURNAMENT = "adminapi/tournament/cancel_tournament";
export const DFST_DELETE_TOURNAMENT_FIXTURE = "adminapi/tournament/delete_tournament_fixture";












export const GET_MATCH_REPORT = 'adminapi/index.php/report/get_match_report';
//GST Module

export const GET_STATE_LIST = 'adminapi/index.php/user/get_state_list';
export const GET_COMPLETED_FIXTURE = 'adminapi/gst/get_gst_completed_match';
export const GET_COMPLETED_CONTEST = 'adminapi/gst/get_gst_completed_contest';
export const GET_TDS_COMPLETED_CONTEST='adminapi/gst/get_tds_completed_contest'
export const GET_GST_REPORT = 'adminapi/gst/gst_report';
export const GET_DOWNLOAD_REPORT = 'adminapi/index.php/gst/gst_invoice_download';
export const GET_EXPORT_GST_REPORT = 'adminapi/index.php/gst/export_gst_invoice_report';
export const GET_SEARCH_USER = 'adminapi/index.php/gst/get_search_user';
export const GET_DASHBOARD = 'adminapi/index.php/gst/get_gst_dashboard';
export const GET_GST_FILLTER_DATA ="adminapi/gst/get_filter_list";  


//New Dlt SMS template
// export const GET_SMS_TEMPLATE = 'adminapi/communication_dashboard/user_segmentation/get_sms_template';
// export const UPDATE_SMS_TEMPLATE = 'adminapi/communication_dashboard/user_segmentation/update_sms_template';

//SCRATCH_WIN
export const CHANGE_SCRATCH_WIN_STATUS = 'adminapi/scratchwin/change_scratch_win_status';
export const GET_SCRATCH_CARD_LIST = 'adminapi/scratchwin/get_scratch_card_list';
export const DELETE_SCRATCH_CARD = 'adminapi/scratchwin/delete_scratch_card';
export const ADD_SCRATCH_CARD = 'adminapi/scratchwin/add_scratch_card';
export const UPDATE_SCRATCH_CARD = 'adminapi/scratchwin/update_scratch_card';
export const UPDATE_NEW_MASTER_SCORING_POINTS = 'adminapi/scoring/update_new_master_scoring_points';

//XP Module

export const GET_CONTEST_TEMPLATE_DETAILS = "adminapi/index.php/contest/contest_template/get_coppied_contest_template_details";


export const JOIN_MULTIPLE_SYSTEM_USERS = "adminapi/nw_contest/nw_systemuser/join_multiple_system_users";
export const MARK_PIN_FIXTURE = "adminapi/index.php/season/pin_fixture";
export const UPDATE_2ND_INNING_DATE = "adminapi/season/update_2nd_inning_date";
export const GET_PICKEM_PRIZES = "pickem/admin/pickem/get_pickem_prizes";
export const PICKEM_UPDATE_PRIZES = "pickem/admin/pickem/update_prizes";
export const GET_ADD_MASTER_DATA = 'adminapi/xp_point/get_add_master_data';
export const ADD_LEVEL = 'adminapi/xp_point/add_level';
export const GET_BADGE_MASTER_LIST = 'adminapi/xp_point/get_badge_master_list';
export const XP_GET_LEVEL_LIST = 'adminapi/xp_point/get_level_list';
export const XP_GET_XP_REWARD_LIST = 'adminapi/xp_point/get_reward_list';
export const XP_GET_ADD_MASTER_DATA = 'adminapi/xp_point/get_add_master_data';
export const XP_ADD_LEVEL = 'adminapi/xp_point/add_level';
export const XP_GET_BADGE_MASTER_LIST = 'adminapi/xp_point/get_badge_master_list';
export const XP_ADD_REWARD = 'adminapi/xp_point/add_reward';
export const XP_DELETE_LEVEL = 'adminapi/xp_point/delete_level';

//Leaderboards
export const LB_GET_MASTER_DATA = "adminapi/leaderboard/get_master_data";
export const LB_GET_SPORT_LEAGUES = "adminapi/index.php/league/get_sport_leagues";
export const LB_GET_LEADERBOARD_LIST = "adminapi/leaderboard/get_leaderboard_list";
export const LB_TOGGLE_LEADERBOARD_BY_ID = "adminapi/index.php/leaderboard/change_leaderboard_status";
export const LB_TOGGLE_LEADERBOARD_DETAILS = "adminapi/index.php/leaderboard/get_leaderboard_prize_details";
export const LB_TOGGLE_LEADERBOARD_USER_LIST = "adminapi/index.php/leaderboard/get_leaderboard_user_list";
export const LB_SAVE_PRIZES_POST = "adminapi/leaderboard/save_prizes";
export const LB_GET_LIVE_UPCOMING_LEAGUES = "adminapi/leaderboard/get_live_upcomming_leagues";
export const MARK_LEAGUE_COMPLETE = "adminapi/leaderboard/mark_complete";
export const LB_GET_PRIZE_DETAIL = "adminapi/leaderboard/get_prize_detail";
export const MARK_LEAGUE_CANCEL = "adminapi/leaderboard/mark_cancel";

//Booster module
export const GET_BOOSTER_LIST = "adminapi/booster/get_booster_list";
export const GET_POSITION_LIST = "adminapi/booster/get_position_list";
export const BSTR_DO_UPLOAD = "adminapi/booster/do_upload";
export const SAVE_BOOSTER = "adminapi/booster/save_booster";
export const GET_FIXTURE_APPLY_BOOSTER = "adminapi/booster/get_fixture_apply_booster";
export const SAVE_FIXTURE_BOOSTER = "adminapi/booster/save_fixture_booster";



export const XP_UPDATE_LEVELS = "adminapi/xp_point/update_levels";
export const XP_DELETE_REWARD = "adminapi/xp_point/delete_reward";
export const XP_UPDATE_REWARD = "adminapi/xp_point/update_reward";
export const XP_GET_ACTIVITIES_LIST = "adminapi/xp_point/get_activities_list";
export const XP_DELETE_ACTIVITY = "adminapi/xp_point/delete_activity";
export const XP_GET_ACTIVITIES_MASTER_LIST = "adminapi/xp_point/get_activities_master_list";
export const XP_ADD_ACTIVITY = "adminapi/xp_point/add_activity";
export const XP_UPDATE_ACTIVITY = "adminapi/xp_point/update_activity";
export const XP_LEVEL_LEADERBOARD = "adminapi/xp_point/level_leaderboard";
export const XP_ACTIVITIES_LEADERBOARD = "adminapi/xp_point/activities_leaderboard";
export const XP_GET_USER_HISTORY = "adminapi/xp_point/get_user_xp_history";
/** Start stock fantasy  */
/*Start code for Add merchandise*/
export const SF_UPLOAD_MERCHANDISE_IMG = "stock/admin/merchandise/do_upload";
export const SF_ADD_MERCHANDISE = "stock/admin/merchandise/add_merchandise";
export const SF_GET_MERCHANDISE_LIST = "stock/admin/merchandise/get_all_merchandise";
export const SF_GET_MERCHANDISE_BY_ID = "stock/admin/merchandise/get_merchandise_by_id";
export const SF_UPDATE_MERCHANDISE = "stock/admin/merchandise/update_merchandise";
/*End code for Add merchandise*/
/*Start code for Add contest category*/
export const SF_CREATE_GROUP = "stock/admin/contest_template/create_group";
export const SF_UPDATE_GROUP = "stock/admin/contest_template/update_group";
export const SF_GET_GROUP = "stock/admin/contest_template/get_group";
export const SF_UPLOAD_GROUP_ICON = "stock/admin/contest_template/upload_group_icon";
export const SF_REMOVE_GROUP_ICON = "adminapi/contest/contest_template/remove_group_icon";
export const SF_DELETE_GROUP = "stock/admin/contest_template/inactive_group";
/*End code for Add contest category*/
/*Start code for contest template*/
export const SF_GET_CONTEST_TEMPLATE_MASTER_DATA = "stock/admin/contest_template/get_all_master_data";
export const SF_GET_CONTEST_TEMPLATE_LIST = "stock/admin/contest_template/get_contest_template_list";
export const SF_CREATE_CONTEST_TEMPLATE = "stock/admin/contest_template/create_template";
export const SF_DELETE_CONTEST_TEMPLATE = "stock/admin/contest_template/delete_template";
export const SF_APPLY_CONTEST_TEMPLATE_TO_CATEGORY = "stock/admin/contest_template/apply_template_to_category";
export const SF_GET_FIXTURE_TEMPLATE = "stock/admin/contest_template/get_fixture_template";
export const SF_DO_UPLOAD_SPONSOR_CONTEST_DTL = "stock/admin/contest_template/do_upload_sponsor_contest_dtl";
export const SF_GET_ALL_CATEGORY_LIST = "stock/admin/contest/get_category_list";
export const SF_GET_CONTEST_TEMPLATE_DETAILS = "stock/admin/contest_template/get_coppied_contest_template_details";
export const SF_UPLOAD_CONTEST_TEMPLATE_SPONSER = "stock/admin/contest_template/do_upload_sponsor";

export const SF_GET_GAME_DETAIL = 'stock/admin/contest/get_game_detail';
export const SF_GET_GAME_LINEUP_DETAIL = 'stock/admin/contest/get_game_lineup_detail';
export const SF_GET_LINEUP_DETAIL = 'stock/admin/contest/get_lineup_detail';
/*End code for contest template*/
/*Start code for Stock Management*/
export const SF_AUTO_SUGGESTION_LIST = 'stock/admin/stock/auto_suggestion_list';
export const SF_SAVE = 'stock/admin/stock/save';
export const SF_UPDATE = 'stock/admin/stock/update';
export const SF_UPLOAD_STOCK_LOGO = 'stock/admin/stock/upload_stock_logo';
export const SF_GET_LOT_SIZE_LIST = 'stock/admin/stock/get_lot_size_list';
export const SF_LIST = 'stock/admin/stock/list';
export const SF_DELETE = 'stock/admin/stock/delete';
export const SF_GET_STOCKS_TO_PUBLISH = 'stock/admin/stock/get_stocks_to_publish';
export const SF_PUBLISH_FIXTURE = 'stock/admin/stock/publish_fixture';
export const SF_GET_FIXTURES = 'stock/admin/collection/get_fixtures';
export const SF_CREATE_TEMPLATE_CONTEST = 'stock/admin/contest/create_template_contest';
export const SF_GET_FIXTURE_CONTEST = 'stock/admin/contest/get_fixture_contest';
export const SF_CANCEL_CONTEST = 'stock/admin/contest/cancel_contest';
export const SF_CANCEL_COLLECTION = "stock/admin/collection/cancel_collection"
export const SF_CREATE_CONTEST = "stock/admin/contest/create_contest"
export const SF_UPDATE_FIXTURE_STOCKS = "stock/admin/stock/update_fixture_stocks"
export const SF_UPDATE_FIXTURE_CUSTOM_MESSAGE = "stock/admin/collection/update_fixture_custom_message"
export const SF_GET_COLLECTION_DETAILS = "stock/admin/contest/get_collection_details"
export const SF_UPDATE_FIXTURE_DELAY = "stock/admin/collection/update_fixture_delay"
export const SF_DELETE_CONTEST = "stock/admin/contest/delete_contest"
export const SF_GET_CONTEST_FILTER = "stock/admin/contest/get_contest_filter"
export const SF_GET_REPORT = "stock/admin/contest/get_report"
export const SF_EXPORT_STOCK_REPORT = 'stock/admin/report/export_report';
export const SF_GET_CONTEST_LIST = 'stock/admin/contest/get_contest_list';
export const SF_GET_FIXTURE_STATS = 'stock/admin/collection/get_fixture_stats';
export const SF_USER_GAME_HISTORY = 'stock/admin/user/user_game_history';
export const SF_VALIDATE_FIXTURE = 'stock/admin/stock/validate_fixture';
export const SF_GET_PROMO_CODE_DETAIL = 'stock/admin/contest/get_promo_code_detail';
export const SF_STOCK_LIST_WITH_CLOSE_PRICE = 'stock/admin/stock/stock_list_with_close_price';
export const SF_UPDATE_PRICE_STATUS = 'stock/admin/stock/update_price_status';
export const SF_UPDATE_CLOSE_PRICE = 'stock/admin/stock/update_close_price';
export const SF_GET_HOLIDAY = 'stock/admin/stock/get_holiday';
/*End code for Stock Management*/
/** End stock fantasy  */

export const LIVE_CONTEST = "adminapi/index.php/dashboard/get_contest_join_graph";

export const GET_CLIENT_CONTEST_DETAILS = "adminapi/nw_contest/get_client_all_contest_details";
export const REMOVE_GET_GA_CAMPAIGN_DATA = "adminapi/index.php/dashboard/get_ga_campaign_data";


/**Start Rookie */
export const ROOK_GET_DASHBOARD_DATA = "adminapi/rookie/get_dashboard_data";
export const ROOK_GET_ROOKIE_USER_LIST = "adminapi/rookie/get_rookie_user_list";
export const ROOK_UPDATE_ROOKIE_SETTING = "adminapi/rookie/update_rookie_setting";
export const ROOK_CHECK_ROOKIE_USER_COUNT = "adminapi/rookie/check_rookie_user_count";
export const ROOK_GET_ROOKIE_SETTING = "adminapi/rookie/get_rookie_setting";
/**End Rookie */
export const PC_GET_MASTER_DATA = "adminapi/promo_code/get_master_data";
export const PC_UPDATE_END_DATE = "adminapi/promo_code/update_end_date";
export const PC_GET_PROMO_CODE_ANALYTICS = "adminapi/promo_code/get_promo_code_analytics";
export const PC_GET_USER_PROMO_CODE_DATA = "adminapi/user/get_user_promo_code_data";

//Banned states
export const GET_COUNTRY_LIST       = "adminapi/common/get_country_list";
export const GET_STATES_LIST        = "adminapi/common/get_state_list";
export const SAVE_BANNED_STATE      = "adminapi/common/save_banned_state";
export const REMOVE_BANNED_STATE    = "adminapi/common/remove_banned_state";
export const GET_BANNED_STATE_LIST  = "adminapi/common/get_banned_state_list";
/**Start API constant for subscription module */
export const SC_ADD_PACKAGE = "adminapi/index.php/subscription/add_package";
export const SC_GET_PACKAGES = "adminapi/index.php/subscription/get_packages";
export const SC_REMOVE_PACKAGE = "adminapi/index.php/subscription/remove_package";
export const SC_GET_SUBSCRIPTION_REPORT = "adminapi/subscription/get_subscription_report";
/**End API constant for subscription module */

/** Start equity stock fantasy  */
/*Start code for equity Add merchandise*/
export const ESF_UPLOAD_MERCHANDISE_IMG = "stock/admin/merchandise/do_upload";
export const ESF_ADD_MERCHANDISE = "stock/admin/merchandise/add_merchandise";
export const ESF_GET_MERCHANDISE_LIST = "stock/admin/merchandise/get_all_merchandise";
export const ESF_GET_MERCHANDISE_BY_ID = "stock/admin/merchandise/get_merchandise_by_id";
export const ESF_UPDATE_MERCHANDISE = "stock/admin/merchandise/update_merchandise";
/*End code for equity Add merchandise*/
/*Start code for equity Add contest category*/
export const ESF_CREATE_GROUP = "stock/admin/contest_template/create_group";
export const ESF_UPDATE_GROUP = "stock/admin/contest_template/update_group";
export const ESF_GET_GROUP = "stock/admin/contest_template/get_group";
export const ESF_UPLOAD_GROUP_ICON = "stock/admin/contest_template/upload_group_icon";
export const ESF_REMOVE_GROUP_ICON = "adminapi/contest/contest_template/remove_group_icon";
export const ESF_DELETE_GROUP = "stock/admin/contest_template/inactive_group";
/*End code for equity Add contest category*/
/*Start code for equity contest template*/
export const ESF_GET_CONTEST_TEMPLATE_MASTER_DATA = "stock/admin/contest_template/get_all_master_data";
export const ESF_GET_CONTEST_TEMPLATE_LIST = "stock/admin/contest_template/get_contest_template_list";
export const ESF_CREATE_CONTEST_TEMPLATE = "stock/admin/contest_template/create_template";
export const ESF_DELETE_CONTEST_TEMPLATE = "stock/admin/contest_template/delete_template";
export const ESF_APPLY_CONTEST_TEMPLATE_TO_CATEGORY = "stock/admin/contest_template/apply_template_to_category";
export const ESF_GET_FIXTURE_TEMPLATE = "stock/admin/contest_template/get_fixture_template";
export const ESF_DO_UPLOAD_SPONSOR_CONTEST_DTL = "stock/admin/contest_template/do_upload_sponsor_contest_dtl";
export const ESF_GET_ALL_CATEGORY_LIST = "stock/admin/contest/get_category_list";
export const ESF_GET_CONTEST_TEMPLATE_DETAILS = "stock/admin/contest_template/get_coppied_contest_template_details";
export const ESF_UPLOAD_CONTEST_TEMPLATE_SPONSER = "stock/admin/contest_template/do_upload_sponsor";

export const ESF_GET_GAME_DETAIL = 'stock/admin/contest/get_game_detail';
export const ESF_GET_GAME_LINEUP_DETAIL = 'stock/admin/contest/get_game_lineup_detail';
export const ESF_GET_LINEUP_DETAIL = 'stock/admin/equity/get_lineup_detail';
/*End code for equity contest template*/
/*Start code for equity Stock Management*/
export const ESF_AUTO_SUGGESTION_LIST = 'stock/admin/stock/auto_suggestion_list';
export const ESF_INDUSTRY_LIST = 'stock/admin/equity/get_industry_list';
export const ESF_SAVE = 'stock/admin/stock/save';
export const ESF_UPDATE = 'stock/admin/stock/update';
export const ESF_UPLOAD_STOCK_LOGO = 'stock/admin/stock/upload_stock_logo';
export const ESF_GET_LOT_SIZE_LIST = 'stock/admin/stock/get_lot_size_list';
export const ESF_LIST = 'stock/admin/stock/list';
export const ESF_DELETE = 'stock/admin/stock/delete';
export const ESF_GET_STOCKS_TO_PUBLISH = 'stock/admin/stock/get_stocks_to_publish';
export const ESF_PUBLISH_FIXTURE = 'stock/admin/stock/publish_fixture';
export const ESF_GET_FIXTURES = 'stock/admin/collection/get_fixtures';
export const ESF_CREATE_TEMPLATE_CONTEST = 'stock/admin/contest/create_template_contest';
export const ESF_GET_FIXTURE_CONTEST = 'stock/admin/contest/get_fixture_contest';
export const ESF_CANCEL_CONTEST = 'stock/admin/contest/cancel_contest';
export const ESF_CANCEL_COLLECTION = "stock/admin/collection/cancel_collection"
export const ESF_CREATE_CONTEST = "stock/admin/contest/create_contest"
export const ESF_UPDATE_FIXTURE_STOCKS = "stock/admin/stock/update_fixture_stocks"
export const ESF_UPDATE_FIXTURE_CUSTOM_MESSAGE = "stock/admin/collection/update_fixture_custom_message"
export const ESF_GET_COLLECTION_DETAILS = "stock/admin/contest/get_collection_details"
export const ESF_UPDATE_FIXTURE_DELAY = "stock/admin/collection/update_fixture_delay"
export const ESF_DELETE_CONTEST = "stock/admin/contest/delete_contest"
export const ESF_GET_CONTEST_FILTER = "stock/admin/contest/get_contest_filter"
export const ESF_GET_REPORT = "stock/admin/contest/get_report"
export const ESF_EXPORT_STOCK_REPORT = 'stock/admin/report/export_report';
export const ESF_GET_CONTEST_LIST = 'stock/admin/contest/get_contest_list';
export const ESF_GET_FIXTURE_STATS = 'stock/admin/collection/get_fixture_stats';
export const ESF_USER_GAME_HISTORY = 'stock/admin/user/user_game_history';
export const ESF_VALIDATE_FIXTURE = 'stock/admin/stock/validate_fixture';
export const ESF_GET_PROMO_CODE_DETAIL = 'stock/admin/contest/get_promo_code_detail';
export const ESF_STOCK_LIST_WITH_CLOSE_PRICE = 'stock/admin/stock/stock_list_with_close_price';
export const ESF_UPDATE_PRICE_STATUS = 'stock/admin/stock/update_price_status';
export const ESF_UPDATE_CLOSE_PRICE = 'stock/admin/stock/update_close_price';
export const ESF_GET_HOLIDAY = 'stock/admin/stock/get_holiday';

/*End code for equity Stock Management*/
/** End equity stock fantasy  */
//revert Collection or contest
export const REVERT_COLLECTION_PRIZE = "adminapi/contest/revert_collection_prize"
export const REVERT_CONTEST_PRIZE = "adminapi/contest/revert_contest_prize"
export const MOVE_MATCH_TO_LIVE = "adminapi/season/move_match_to_live"

/**Start API user engagement module */
export const QZ_ADD = "adminapi/quiz/add"
export const QZ_LIST = "adminapi/quiz/list"
export const QZ_DELETE_QUESTION = "adminapi/quiz/delete_question"
export const QZ_DELETE_QUIZ = "adminapi/quiz/delete"
export const QZ_SHOW_HIDE = "adminapi/quiz/change_question_visibility"
export const QZ_UPDATE_QUESTION = "adminapi/quiz/update_question"
export const QZ_TOGGLE_HOLD = "adminapi/quiz/toggle_hold"
export const QZ_GET_QUESTIONS = "adminapi/quiz/get_questions"
export const QZ_CHECK_QUIZ_EXIST = "adminapi/quiz/check_quiz_exist"
export const QZ_GET_LIVE_QUIZ_GRAPH = "adminapi/quiz/dashboard/get_live_quiz_graph"
export const QZ_GET_QUIZ_PARTICIPATION_GRAPH = "adminapi/quiz/dashboard/get_quiz_participation_graph"
export const QZ_GET_QUIZ_LEADERBOARD = "adminapi/quiz/dashboard/get_quiz_leaderboard"
export const QZ_GET_TOP_GAINERS = "adminapi/spinthewheel/get_top_gainers"
export const QZ_GET_LEADERBOARD_BY_CATEGORY = "adminapi/spinthewheel/get_leaderboard_by_category"
export const QZ_GET_DAILY_CHECKIN_TOP_GAINERS = "adminapi/coins/get_daily_checkin_top_gainers"
export const QZ_GET_LEADERBOARD_DAILYCHECKIN = "adminapi/coins/get_leaderboard_dailycheckin"
export const QZ_GET_DOWNLOAD_APP_LEADERBOARD = "adminapi/user/get_download_app_leaderboard"
export const QZ_GET_DOWNLOAD_APP_GRAPH = "adminapi/user/get_download_app_graph"
export const QZ_REWARD_DASHBOARD_GRAPH = "adminapi/reward_dashboard/graph"
/**End API user engagement module */

//Social Login
export const SOCIAL_LOGIN = "adminapi/social/login"


/**Start API Sport Predict module */
export const SP_PUBLISH_FIXTURE = "stock/admin/stock_predict/publish_fixture"
export const SP_STOCK_LIST = "stock/admin/stock/list"
export const SP_GET_FIXTURE_TEMPLATE = "stock/admin/contest_template/get_fixture_template"
export const SP_CREATE_TEMPLATE_CONTEST = "stock/admin/contest/create_template_contest"
export const SP_GET_COLLECTION_DETAILS = "stock/admin/contest/get_collection_details"
export const SP_GET_FIXTURE_CONTEST = "stock/admin/contest/get_fixture_contest"
export const SP_GET_INDUSTRY_LIST = "stock/admin/stock_predict/get_industry_list"
export const SP_GET_MASTER_DATA = "stock/admin/stock_predict/get_master_data"
export const SP_GET_GAME_DETAIL = "stock/admin/contest/get_game_detail"
export const SP_GET_GAME_LINEUP_DETAIL = "stock/admin/contest/get_game_lineup_detail"
export const SP_GET_CONTEST_TEMPLATE_LIST = "stock/admin/contest_template/get_contest_template_list"
export const SP_CREATE_TEMPLATE = "stock/admin/contest_template/create_template"
export const SP_GET_ALL_MASTER_DATA = "stock/admin/contest_template/get_all_master_data"
export const SP_SAVE = "stock/admin/stock/save"
export const SP_UPDATE = 'stock/admin/stock/update';
export const SP_UPDATE_FIXTURE_STOCKS = "stock/admin/stock/update_fixture_stocks"
export const SP_MARK_PIN_CONTEST = "stock/admin/contest/mark_pin_contest"
export const SP_GET_CANDLE_TIME_LIST = "stock/admin/stock_predict/get_candle_time_list"
export const SP_STOCK_CLOSING_RATE_FOR_TIME = "stock/admin/stock_predict/stock_closing_rate_for_time"
export const SP_UPDATE_STOCK_RATE = "stock/admin/stock_predict/update_stock_rate"
// Start code for H2H
export const H2H_GET_H2H_USER_LIST = "adminapi/h2hchallenge/get_h2h_user_list"
export const H2H_GET_DASHBOARD_DATA = "adminapi/h2hchallenge/get_dashboard_data"
export const H2H_UPDATE_SETTING = "adminapi/h2hchallenge/update_setting"
export const H2H_DO_UPLOAD = "adminapi/h2hchallenge/do_upload"
export const H2H_SAVE_CMS = "adminapi/h2hchallenge/save_cms"
export const H2H_GET_CMS_LIST = "adminapi/h2hchallenge/get_cms_list"
export const H2H_DELETE_CMS = "adminapi/h2hchallenge/delete_cms"
export const H2H_GET_UPCOMING_GAME_LIST = "adminapi/h2hchallenge/get_upcoming_game_list"
export const H2H_GET_H2H_GAME_USERS = "adminapi/h2hchallenge/get_h2h_game_users"
export const H2H_SAVE_FIXTURE_H2H_TEMPLATE = "adminapi/contest/contest_template/save_fixture_h2h_template"

// Start code for Fast Khelo
export const LF_GET_ALL_MERCHANDISE = "livefantasy/admin/merchandise/get_all_merchandise"
export const LF_DO_UPLOAD = "livefantasy/admin/merchandise/do_upload"
export const LF_ADD_MERCHANDISE = "livefantasy/admin/merchandise/add_merchandise"
export const LF_GET_MERCHANDISE_BY_ID = "livefantasy/admin/merchandise/get_merchandise_by_id"
export const LF_UPDATE_MERCHANDISE = "livefantasy/admin/merchandise/update_merchandise"
export const LF_GET_SPORT_LEAGUES = "livefantasy/admin/league/get_sport_leagues"
export const LF_GET_ALL_MASTER_DATA = "livefantasy/admin/contest_template/get_all_master_data"
export const LF_GET_CONTEST_TEMPLATE_LIST = "livefantasy/admin/contest_template/get_contest_template_list"
export const LF_CREATE_TEMPLATE = "livefantasy/admin/contest_template/create_template"
export const LF_DELETE_TEMPLATE = "livefantasy/admin/contest_template/delete_template"
export const LF_APPLY_TEMPLATE_TO_LEAGUE = "livefantasy/admin/contest_template/apply_template_to_league"
export const LF_GET_COPPIED_CONTEST_TEMPLATE_DETAILS = "livefantasy/admin/contest_template/get_coppied_contest_template_details"
export const LF_GET_ALL_SEASON_SCHEDULE = "livefantasy/admin/season/get_all_season_schedule"
export const LF_UPDATE_FIXTURE_DELAY = "livefantasy/admin/season/update_fixture_delay"
export const LF_UPDATE_FIXTURE_CUSTOM_MESSAGE = "livefantasy/admin/season/update_fixture_custom_message"
export const LF_GET_SEASON_TO_PUBLISH = "livefantasy/admin/season/get_season_to_publish"
export const LF_PUBLISH_FIXTURE = "livefantasy/admin/season/publish_fixture"
export const LF_GET_FIXTURE_TEMPLATE = "livefantasy/admin/contest_template/get_fixture_template"
export const LF_GET_FIXTURE_OVERS = "livefantasy/admin/season/get_fixture_overs"
export const LF_GET_FIXTURE_CONTEST = "livefantasy/admin/contest/get_fixture_contest"
export const LF_DELETE_CONTEST = "livefantasy/admin/contest/delete_contest"
export const LF_PIN_FIXTURE = "livefantasy/admin/season/pin_fixture"
export const LF_UPDATE_MATCH_STATUS = "livefantasy/admin/season/update_match_status"
export const LF_GET_GROUP = "livefantasy/admin/contest_template/get_group"
export const LF_DELETE_GROUP = "livefantasy/admin/contest_template/inactive_group";
export const LF_CREATE_GROUP = "livefantasy/admin/contest_template/create_group";
export const LF_UPDATE_GROUP = "livefantasy/admin/contest_template/update_group";
export const LF_GET_CONNTEST_FILTER = "livefantasy/admin/contest/get_conntest_filter";
export const LF_GET_CONTEST_LIST = "livefantasy/admin/contest/get_contest_list";
export const LF_LEAGUE_OVER = "livefantasy/admin/league/league_over";
export const LF_CREATE_CONTEST = "livefantasy/admin/contest/create_contest";
export const LF_GET_SEASON_DETAIL = "livefantasy/admin/season/get_season_detail";
export const LF_CANCEL_FIXTURE = "livefantasy/admin/season/cancel_fixture";
export const LF_CANCEL_FIXTURE_OVER = "livefantasy/admin/season/cancel_fixture_over";
export const LF_CANCEL_COLLECTION = "livefantasy/admin/contest/cancel_collection";
export const LF_CANCEL_CONTEST = "livefantasy/admin/contest/cancel_contest";
export const LF_GET_COLLECTION_DETAIL = "livefantasy/admin/contest/get_collection_detail";

export const LF_MANUAL_SCORING_MASTER = "livefantasy/admin/season/manual_scoring_master";
export const LF_GET_MARKETS_ODDS = "livefantasy/admin/season/get_markets_odds";
export const LF_UPDATE_SCORING_POINTS = "livefantasy/admin/season/update_scoring_points";
export const LF_CHANGE_BALL_STATUS = "livefantasy/admin/season/change_ball_status";
export const LF_UPDATE_BALL_RESULT = "livefantasy/admin/season/update_ball_result";
export const LF_GET_GAME_DETAIL = "livefantasy/admin/contest/get_game_detail";
export const LF_GET_GAME_LINEUP_DETAIL = 'livefantasy/admin/contest/get_game_lineup_detail';
export const LF_GET_USER_PRIDICTION = 'livefantasy/admin/season/get_user_predictions';
export const LF_MARK_PIN_CONTEST = "livefantasy/admin/contest/mark_pin_contest";

export const LF_UPDATE_MATCH_SCORE_STATUS = "livefantasy/admin/season/update_match_score_status";
export const LF_UPDATE_MATCH_SCORE = "livefantasy/admin/season/update_match_score";

export const SP_UPDATE_CANDLE_OPENING_CLOSING_RATES = "stock/admin/stock_predict/update_candle_opening_closing_rates"
export const LF_GET_MATCH_OVER_STATUS = "livefantasy/admin/season/get_collection_detail";
export const LF_UPDATE_OVER_STATUS = "livefantasy/admin/season/update_over_status";
export const LF_START_OVER_TIMER = "livefantasy/admin/season/start_over_timer";
export const LF_PC_GET_SETTING = "livefantasy/admin/private_contest/get_settings_data";
export const LF_PC_TOGGLE_VISIBILITY = "livefantasy/admin/private_contest/toggle_private_contest_visibility";
export const LF_PC_UPDATE_SITE_RAKE = "livefantasy/admin/private_contest/update_site_rake";
export const LF_PC_UPDATE_HOST_RAKE = "livefantasy/admin/private_contest/update_host_rake";
export const LF_PC_DASHBOARD_DATA = "livefantasy/admin/private_contest/dashboard_data";
export const LF_PC_USER_SIGNUP_GRAPH = "livefantasy/admin/private_contest/get_new_user_signup_graph";
export const LF_PC_CREATED_GRAPH = "livefantasy/admin/private_contest/get_private_contest_created_graph";
export const LF_GET_REPORT_MONEY_PAID_BY_USER = 'livefantasy/admin/report/get_report_money_paid_by_user';
export const LF_GET_ALL_CONTEST_REPORT = 'livefantasy/admin/report/get_all_contest_report';
export const LF_GET_SPORT_LEAGUES_REPORT = "livefantasy/admin/report/get_sport_leagues";
export const LF_GET_CONTEST_FILTER = "livefantasy/admin/report/get_conntest_filter";
export const LF_GET_ALL_COLLECTIONS_BY_LEAGUE = "livefantasy/admin/report/get_all_collections_by_league";
export const LF_GET_DASHBOARD_SITERAKE = "livefantasy/admin/dashboard/get_siterake";
export const LF_GET_DASHBOARD_FREEPAID_USERS = "livefantasy/admin/dashboard/get_freepaidusers";

//Player Management Module 
export const UPLOAD_PLAYER_JERSEY = "adminapi/index.php/player_management/do_upload/jersey";
export const GET_ALL_PLAYER_LIST = "adminapi/index.php/player_management/get_all_player_list";
export const SAVE_PLAYER_IMAGE = "adminapi/index.php/player_management/save_player_image";






/**Start API Live Sport Fantasy module */
export const LSF_PUBLISH_FIXTURE = "stock/admin/live_stock_fantasy/publish_fixture";
export const LSF_GET_FIXTURE_TEMPLATE = "stock/admin/contest_template/get_fixture_template";
export const LSF_GET_HOLIDAY = 'stock/admin/stock/get_holiday';
export const LSF_GET_INDUSTRY_LIST = "stock/admin/live_stock_fantasy/get_industry_list";
export const LSF_GET_MASTER_DATA = "stock/admin/live_stock_fantasy/get_master_data";
export const LSF_CREATE_TEMPLATE = "stock/admin/contest_template/create_template";
export const LSF_STOCK_CLOSING_RATE_FOR_TIME = "stock/admin/live_stock_fantasy/stock_closing_rate_for_time"
export const LSF_UPDATE_STOCK_RATE = "stock/admin/live_stock_fantasy/update_stock_rate"
export const LSF_GET_LINEUP_DETAIL = 'stock/admin/live_stock_fantasy/get_user_trade_history';

// Picks Fantasy
export const PF_MASTER_DATA = "picks/admin/contest_template/get_all_master_data";
export const GET_TEAM_PLAYER = "picks/admin/team/get_team_list";
export const PF_GET_SPORTS = "picks/admin/league/get_sports_list";
export const UPLOAD_TEAM_GROUP_LOGO = "picks/admin/team/do_upload/flag";
export const SAVE_TEAM_PLAYER = "picks/admin/team/save_team";
export const DELETE_TEAM_PLAYER = "picks/admin/team/delete_team";
export const DELETE_LEAGUE_PLAYER = "picks/admin/team/delete_league_player";
export const DELETE_LEAGUE = "picks/admin/league/delete_league";
export const PF_GET_LEAGUE_LIST = "picks/admin/league/get_league_list";
export const SAVE_LEAGUE = "picks/admin/league/save_league";
export const SAVE_SPORTS = "picks/admin/league/save_sports";
export const GET_SPORT_LIST = "admin/league/get_sports_list";
export const DELETE_SPORTS = "picks/admin/league/delete_sports";
export const GET_TEAM_BY_LEAGUE_ID_LIST = "picks/admin/team/get_team_by_league_id_list";
export const SAVE_LEAGUE_TEAM = "picks/admin/team/save_league_team";
export const PF_GET_ALL_FIXUTRE = "picks/admin/season/get_all_season_schedule";
export const PF_GET_CONTEST_TEMPLATE_LIST = "picks/admin/contest_template/get_contest_template_list";
export const PF_CREATE_CONTEST_TEMPLATE = "picks/admin/contest_template/create_template";
export const PF_GET_CONTEST_TEMPLATE_DETAILS = "picks/admin/contest_template/get_coppied_contest_template_details";
export const PF_DELETE_CONTEST_TEMPLATE = "picks/admin/contest_template/delete_template";
export const PF_ADD_FIXTURE = "picks/admin/season/add_fixture";
export const PF_PUBLISH_FIXTURE = "picks/admin/season/publish_fixture";
export const PF_GET_FIXTURE_TEMPLATE = "picks/admin/contest_template/get_fixture_template";
export const PF_CREATE_CONTEST = "picks/admin/contest/create_template_contest"; 
export const PF_GET_GROUP_LIST = "picks/admin/contest_template/get_group_list"; 
export const PF_UPDATE_FIXTURE_DELAY = "picks/admin/season/update_fixture_delay"; 
export const PF_SAVE_DRAFT = "picks/admin/season/save_draft"; 
export const PF_GET_QUESTION_LIST_BY_ID = "picks/admin/season/get_question_list_by_id"; 
export const PF_NEW_CREATE_CONTEST = "picks/admin/contest/create_contest"; 
export const PF_CANCEL_SEASON = "picks/admin/contest/cancel_season"; 
export const PF_GET_FIXTURE_CONTEST = "picks/admin/contest/get_fixture_contest"; 
export const PF_GET_QUE_BY_ID = "picks/admin/season/get_question_list_by_id";
export const PF_UPDATE_ANSWER = "picks/admin/season/update_answer_by_id";
export const PF_COPY_CONTEST_TEMPLATE = "picks/admin/contest_template/get_coppied_contest_template_details"; 
export const PF_CANCEL_CONTEST = "picks/admin/contest/cancel_contest"; 
export const PF_DELETE_CONTEST = "picks/admin/contest/delete_contest"; 
export const PF_GET_GAME_DETAIL = "picks/admin/contest/get_game_detail"; 
export const PF_GET_GAME_LINEUP_DETAIL = "picks/admin/contest/get_join_contest_user";
export const PF_GET_LINEUP_DETAIL = "picks/admin/contest/get_lineup_detail";
export const PF_DELETE_FIXTURE = "picks/admin/season/delete_fixture";
export const PF_UPDATE_EXPLANATION = "picks/admin/season/save_explaination";
export const PF_MARK_PIN_FIXTURE = "picks/admin/season/mark_pin";
export const PF_SAVE_TIE_BREAKER = "picks/admin/season/save_tie_breaker_answer";
export const UPDATE_SPORTS_STATUS = "picks/admin/league/update_sports_status";
export const PF_REMOVE_MEDIA = "picks/admin/team/remove_media";

//contest Dashboard
export const PF_GET_CONTEST_LIST = "picks/admin/contest/get_contest_list"; 
export const PF_GET_CONTEST_FILTER = "picks/admin/contest/get_contest_filter"; 
export const PF_GET_FIXTURE_BY_LEAGUE_ID = "picks/admin/contest/get_fixture_by_league_id"; 

//contest Report 
export const PF_GET_ALL_CONTEST_REPORT = "picks/admin/report/get_all_contest_report"; 
export const PF_GET_SPORTS_LIST = "picks/admin/league/get_sports_list"; 

//Reports 
export const EXPORT_CONTEST_WINNERS = 'picks/admin/contest/contest_report';

export const PF_MARK_COMPLETE = 'picks/admin/season/mark_completed';

//Add Multibot
export const ADD_SYSTEM_USER_TEAMS = "adminapi/systemuser/add_system_user_teams"
export const MANAGE_SYSTEM_USER_MASTER_DATA = "adminapi/systemuser/manage_system_user_master_data"
export const MANAGE_SYSTEM_USER_LIST = "adminapi/systemuser/manage_system_user_list"
export const JOIN_MULTIPLE_USERS = "adminapi/systemuser/join_multiple_users"

export const PF_DO_UPLOAD_LOGO = "picks/admin/team/do_upload";

//pickem tournament

export const PICKEM_GET_SPORT_LEAGUE = "pickem/admin/tournament/get_sport_leagues";
export const PICKEM_GET_FIXTURE_LIST = "pickem/admin/tournament/get_fixture_list";
export const PICKEM_SAVE_TOURNAMENT = "pickem/admin/tournament/save_tournament";
export const PICKEM_GET_TOURNAMENT_DETAIL = "pickem/admin/tournament/get_tournament_detail";
export const PICKEM_GET_TOURNAMENT_LIST = "pickem/admin/tournament/get_tournament_list";
export const PICKEM_GET_TOURNAMENT_FIXTURES = "pickem/admin/tournament/get_tournament_fixtures";
export const PICKEM_SAVE_TOURNAMENT_FIXTURES = "pickem/admin/tournament/save_tournament_fixtures";
export const PICKEM_CANCEL_TOURNAMENT_FIXTURES = "pickem/admin/tournament/cancel_tournament";
export const PICKEM_GET_JOIN_PARTICIPANTS_LIST = "pickem/admin/tournament/get_join_partcipants_list";
export const PICKEM_TIE_BREAKER_ANSWER = "pickem/admin/tournament/save_tie_breaker_answer";
export const PICKEM_MARK_PIN = "pickem/admin/tournament/mark_pin";
export const PICKEM_MARK_TOURNAMENT_COMPLETE = "pickem/admin/tournament/mark_tournament_complete";
export const PICKEM_GET_PARTICIPANTS_DETAIL = "pickem/admin/tournament/get_partcipants_detail";
export const PICKEM_DO_UPLOAD = "pickem/admin/league/do_upload";
export const PICKEM_SUBMIT_QA = "pickem/admin/tournament/submit_pickem_answer";
export const PICKEM_GET_MASTER_DATA = "pickem/admin/tournament/get_master_data";
export const PICKEM_SUBMIT_MARK_FIXTURE_COMPLETE = "pickem/admin/tournament/mark_fixture_complete";
export const PICKEM_LEAGUE_MANAGMENT_TABLE = "pickem/admin/league/get_leagues_list";
export const PICKEM_UPDATE_IS_FEATURED =     "pickem/admin/league/update_is_featured";




//League managment 

export const LEAGUE_MANAGMENT_TABLE = "adminapi/league/get_leagues_list";
export const UPDATE_IS_FEATURED = "adminapi/league/update_is_featured";
export const UPDATE_AUTOPUBLISH_STATUS = "adminapi/league/update_auto_publish_status";

// What's New 
export const GET_RECORD_LIST_WHATSNEW = "adminapi/whatsnew/get_record_list";
export const UPDATE_STATUS_WHATSNEW = "adminapi/whatsnew/update_status";
export const DELETE_RECORD_WHATSNEW = "adminapi/whatsnew/delete_record";
export const SAVE_RECORD_WHATSNEW = "adminapi/whatsnew/save_record";
export const DO_UPLOAD_WHATSNEW_IMG = "adminapi/common/do_upload";
export const EDIT_REOCRD_SAVED = "adminapi/whatsnew/update_record";

export const EXPORT_PDF_FILE = "adminapi/contest/download_contest_teams"
//Deals 
export const DEALS_DETAILS = "adminapi/deals/get_deals_detail";


// new DFS tournament apis
export const DFSTR_MASTER_DATA = "adminapi/tournament/get_master_data";
export const DFSTR_SPORT_LEAGUES = "adminapi/tournament/get_sport_leagues";
export const DFSTR_PUBLISHED_FIXTURES = "adminapi/tournament/get_published_fixtures";
export const DFSTR_SAVE_TOURNAMENT = "adminapi/tournament/save_tournament";
export const DO_UPLOAD = "adminapi/common/do_upload";
export const DFST_TOURNAMENT_LIST = "adminapi/tournament/get_tournament_list";
export const DFST_CANCEL_TOURNAMENT = "adminapi/tournament/mark_cancel";
export const DFST_SAVE_TOUR_FIXTURES = "adminapi/tournament/save_tournament_fixtures";
export const DFST_SAVE_CONTEST_TOURNAMENT = "adminapi/tournament/save_contest_tournament";
export const DFST_GET_TOUR_DETAIL = "adminapi/tournament/get_tournament_detail";
export const DFST_GET_TOUR_USERS = "adminapi/tournament/get_tournament_users";
export const DFST_PIN_TOURNAMENT = "adminapi/tournament/mark_pin";
export const DFST_USER_TEAM_DETAIL = "adminapi/tournament/get_user_team_detail";

// pickem
export const PF_CANCEL_FIXTURE = "pickem/admin/tournament/cancel_fixture";
export const GET_FILTER_LIST_TDS = "adminapi/tds/get_filter_list";
export const GET_REPORT_TDS = "adminapi/tds/get_report";
export const GET_TDS_DOCUMENT = "adminapi/tds/get_tds_document";
export const DELETE_TDS_DOCUMENT = "adminapi/tds/delete_tds_document";
export const REMOVE_MEDIA = "adminapi/remove_media";
export const SAVE_TDS_DOCUMENT = "adminapi/tds/save_tds_document";

// ----------------------------------------------------------------
//manual payment gateway
export const DO_UPLOAD_MPG = "adminapi/common/do_upload";
export const MPG_TRANSACTION_UPDATE = "adminapi/manualpg/update_type_list";
export const MPG_GET_TYPE_LIST = "adminapi/manualpg/get_type_list"
export const MPG_REPORT ="adminapi/manualpg/get_manual_txn"
export const MPG_EXPORT = "adminapi/index.php/manualpg/get_menual_txn"
export const MPG_REMOVE_IMG = "adminapi/common/remove_media"
export const MPG_UPDATE_TRANSACTION = "adminapi/manualpg/update_transaction"
export const STATUS_UPDATE_WL = "adminapi/manualpg/change_type_status"


//common
export const REFERRAL_FRIENDS_REPORT = "adminapi/index.php/report/referral_friends";

// 3.0 optimization 
export const GET_DFS_GAME_DETAIL = 'adminapi/index.php/contest/contest/get_contest_detail';
export const GET_DFS_GAME_LINEUP_DETAIL = 'adminapi/index.php/contest/contest/get_contest_users';
export const DFS_MANAGE_SYSTEM_USER_LIST = "adminapi/systemuser/get_fixture_system_user_list";
export const DFS_MANAGE_SYSTEM_USER_MASTER_DATA = "adminapi/systemuser/get_system_user_master_data";
export const DFS_H2H_SAVE_FIXTURE_H2H_TEMPLATE = "adminapi/contest/contest/save_fixture_h2h_template";
export const DFS_GET_USER_CONTEST_TEAM = "adminapi/contest/contest/get_user_contest_team";
export const DFS_GET_FIXTURE_DETAILS = "adminapi/index.php/season/get_fixture_details";
export const GET_LEAGUE_FIXTURES = "adminapi/index.php/contest/get_league_fixture";
export const GET_MG_LEAGUE_LIST = "adminapi/index.php/multigame/get_leagues_list";
export const GET_MG_CONTEST_FILTER = "adminapi/index.php/multigame/get_contest_filter";
export const GET_MG_LEAGUE_FIXTURES = "adminapi/index.php/multigame/get_league_fixture";
export const GET_MG_FIXTURE_LEAGUE_LIST = "adminapi/index.php/multigame/get_fixture_league_list";


//Props Team Module 
export const PROPS_ALL_TEAM_LIST        = "props/admin/league/get_team_list";
export const PROPS_DO_UPLOAD            = "props/do_upload/";
export const PROPS_SAVE_TEAM_DETAIL     = "props/admin/league/save_team_details";
export const PROPS_ALL_PLAYER_LIST      = "props/admin/league/get_player_list";
export const PROPS_SAVE_PLAYER_DETAIL   = "props/admin/league/save_player_image";
export const PROPS_ALL_LEAGUE_LIST      = "props/admin/league/get_league_list";
export const PROPS_UPDATE_LEAGUE_STATUS = "props/admin/league/update_status";
export const PROPS_ALL_SPORTS_LIST      =  "props/admin/league/get_sports_list";
export const PROPS_ALL_PAYOUT_LIST      = "props/admin/league/get_payout_list";
export const PROPS_UPDATE_PAYOUT_STATUS = "props/admin/league/update_payout_status";
export const UPDATE_MASTER_PAYOUT_POINTS= "props/admin/league/update_payout";
export const PROPS_SAVE_SETTING         = "props/admin/league/save_min_max_bet";

export const PROPS_ALL_USER_REPORT      = "props/admin/report/get_user_report";
export const PROPS_UPDATE_USER_STATUS   = "props/admin/report/update_user_status";
export const PROPS_UPDATE_USER_LIMIT    = "props/admin/report/update_user_limit";
export const PROPS_EXPORT_USER_REPORT   = "props/admin/report/get_user_report";
export const PROPS_FILTER_DATA          = "props/admin/props/get_filter_data";
export const PROPS_PLAYER_LIST          = "props/admin/props/get_player_props_list";
export const UPDATE_PLAYER_STATUS       = "props/admin/props/update_props_status";

//Payment gateway content management

export const GET_ACTIVE_PAYMENT_GETWAY    = "adminapi/setting/get_active_payment_gateway";
export const UPDATE_PAYMENT_GETWAY_DETAIL = "adminapi/setting/update_paymentgatway_detail";

//Generate qr
export const QR_GENERATE = "adminapi/setting/generate_qr";


// Opinion trading

export const TRADE_ALL_TEAM_LIST                 = "trade/admin/league/get_team_list";
export const TRADE_DO_UPLOAD                     = "trade/do_upload/";
export const TRADE_SAVE_TEAM_DETAIL              = "trade/admin/league/save_team_details";
export const TRADE_ALL_SPORTS_LIST               = "trade/admin/league/get_sports_list";

export const TRADE_SAVE_PLAYER_DETAIL            = "trade/admin/league/save_player_image";
export const TRADES_ALL_LEAGUE_LIST              = "trade/admin/league/get_league_list";

export const TRADE_UPDATE_LEAGUE_STATUS          = "trade/admin/league/update_status";

export const TRADES_ALL_TEMPLATE_LIST            = "trade/admin/league/get_template_list";
export const TRADE_UPDATE_TEMPLATE_STATUS        = "trade/admin/league/update_template_status";
export const GET_TRADE_ALL_FIXTURE_BY_LEAGUE     = "trade/admin/league/get_fixture_list";

export const GET_TRADE_ALL_FIXTURE               = "trade/admin/season/get_season_list";// "adminapi/index.php/season/get_all_season_schedule";
export const UPDATE_TRADE_FIXTURE_DELAY          = "trade/admin/season/update_fixture_delay"

export const TRADE_SEASON_DETAIL                 = "trade/admin/season/get_season_detail"
export const UPDATE_TRADE_FIXTURE_CUSTOM_MESSAGE = "trade/admin/season/update_fixture_custom_message"
export const GET_TRADE_REPORT                    = "trade/admin/report/get_opinion_report"
export const GET_TRADE_REPORT_EXPORT             = "trade/admin/report/get_opinion_report"
export const OT_ADD_QUESTION                     = "trade/admin/season/add_question"
export const GET_SEASON_QUESTION                 = "trade/admin/season/get_season_question"
export const UPDATE_TRADE_ANSWER                 = "trade/admin/season/update_answer"
export const TRADE_QUESTION_PARTICIPENT_DETAIL   = "trade/admin/season/get_question_detail"
export const TRADE_FIXTURE_CANCEL                = "trade/admin/season/cancel_season"
export const TRADE_QUESTION_CANCEL               = "trade/admin/season/cancel_question"
export const TRADES_ALL_LEAGUE_LIST_DROPDOWN     = "trade/admin/league/get_all_league_list";
export const OT_PIN_FIXTURE                      = "trade/admin/season/mark_pin_season";




