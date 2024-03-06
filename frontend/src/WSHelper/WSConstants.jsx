import app_config from "../InitialSetup/AppConfig";

export class DeviceToken {
    static getDeviceId = () => {
        return deviceID;
    }

    static setDeviceId = (token) => {
        deviceID = token;
    }
 
}
export class UserLatLong {
    static getLatLong = () => {
        return latlong;
    }
    static setLatLONG = (latlongi) => {
        latlong = latlongi;
    }

}
export class getActiveVisibileTime {
    static getVisbleTime = () => {
        return vTime;
    }
    static setVisibleTime = (vTime) => {
        vTime = vTime;
    }

}

export const AppName = process.env.REACT_APP_NAME;
export const nodeBaseURL = app_config.apiGateway.nodeURL;
export const baseURL = app_config.apiGateway.URL;
export const userURL = app_config.apiGateway.USER_URL;
export const fantasyURL = app_config.apiGateway.FANTASY_URL;
export const stockURL = app_config.apiGateway.STOCK_URL;
export const propsURL = app_config.apiGateway.PROPS_URL;
export const oTradeURL = app_config.apiGateway.OPINION_TRADE_URL;
export const S3_BUCKET_PATH = app_config.s3.BUCKET;
export const BUCKET_DATA_PREFIX = app_config.s3.BUCKET_DATA_PREFIX;
export const deviceType = "3";
export const deviceTypeAndroid = "1";
export const deviceTypeIOS = "2";
export var deviceID = "";
export var latlong = "";
export var vTime = new Date().getTime();
export const successCode = 200;
export const sessionExpireCode = 401;
export const inQueueCode = 400;
export const BannedStateCode = 403;


// Social
export const FB_APP_ID = app_config.cognito.FB_APP_ID;
export const GPLUS_ID = app_config.cognito.GOOGLE_CLIENT_ID;
//Analytics Constant
export const GA_PROFILE_ID = app_config.cognito.GOOGLE_PROFILE_ID; //cricjamtrial@gmail.com

//Api Constant
export const GET_USER_TRACK_ID = "user/auth/get_user_track_id";
export const MASTER_DATA = "user/auth/get_app_master_list";
export const STATIC_PAGE_DATA = "user/common/get_static_content";
export const VALIDATE_OTP = "user/auth/validate_otp";
export const VALIDATE_EMAIL_OTP = "user/emailauth/validate_otp";
export const GET_SIGNUP_REFERRAL_DATA = "user/auth/get_signup_referral_data";
export const VALIDATE_LOGIN = "user/auth/validate_login";
export const LOGIN = "user/auth/login";
export const EDIT_MOBILE = "user/profile/new_number_send_otp";
export const VERIFY_EDITED_MOBILE = "user/profile/new_number_verify_and_update";
export const EDIT_EMAIL = "user/profile/new_email_otp_send";
export const VERIFY_EDITED_EMAIL = "user/profile/new_email_verify_and_update";
export const EMAIL_LOGIN = "user/emailauth/login";
export const RESEND_OTP = "user/auth/resend_otp";
export const RESEND_EMAIL_OTP = "user/emailauth/resend_otp";
export const FORGOT_PASSWORD = "user/emailauth/forgot_password";
export const RESET_PASSWORD = "user/emailauth/reset_password";
export const VALIDATE_FORGOT_PASSWORD = "user/emailauth/forgot_password_validate_code";
export const GET_LINEUP_MASTER_DATA = "fantasy/lineup/get_lineup_master_data";
export const GET_ALL_ROSTER = "fantasy/lineup/get_all_roster";
export const LINEUP_PROCCESS = "fantasy/lineup/save_team"; // Create/Edit Lineup
export const GET_USER_LINEUP = "fantasy/lineup/get_user_lineup";
export const SOCIAL_LOGIN = "user/auth/social_login";
export const SOCIAL_EMAIL_LOGIN = "user/emailauth/social_login";

export const GET_LINPEUP_WITH_SCORE = "fantasy/contest/get_linpeup_with_score";
//Player card
export const GET_PLAYERCARD = "fantasy/common/get_playercard";
export const GET_PLAYER_BREAKDOWN = "fantasy/common/get_player_breakdown";
//Leaderboard
export const GET_INVITE_CODE = "fantasy/contest/get_contest_invite_code";
// Profile
export const UPDATE_SIGNUP_DATA = "user/profile/update_profile_data";
export const VERIFY_PAN_INFO = "user/profile/verify_pan_info";
export const VERIFY_BANK_ACCOUNT = "user/profile/verify_bank_account";
export const UPDATE_EMAIL_SIGNUP_DATA = "user/emailauth/update_profile_data";
export const CHANGE_PASSWORD = "user/profile/change_password";
export const UPDATE_DEVICE_TOKEN = "user/profile/update_device_id";
export const GET_MY_PROFILE = "user/profile/profile_detail";
export const DO_UPLOAD = "user/profile/do_upload";
export const UPDATE_PROFILE = "user/profile/update_basic_info";
export const UPDATE_STATE_DATA = "user/profile/update_state_city";
export const GET_ALL_COUNTRY = "user/profile/get_country_list";
export const UPDATE_BAN_STATE_DATA = "user/profile/update_declaration";
export const GET_ALL_STATE = "user/profile/get_state_list";
export const UPDATE_BANK_ACC_DETAILS = "user/profile/update_bank_ac_detail";
export const DO_UPLOAD_PAN = "user/profile/do_upload_pan";
export const UPDATE_PAN_INFO = "user/profile/update_pan_info";
export const DO_UPLOAD_BANK_DOCUMENT = "user/profile/do_upload_bank_document";
export const CHECK_USERNAME_AVAIBILITY = "user/profile/check_username";
export const UPDATE_USERNAME = "user/profile/update_username";
export const PLAYING_EXPERIENCE = "user/profile/get_playing_experience";
export const DELETE_BANK_ACC_DETAILS = "user/profile/delete_bank_details";
export const GET_AVATARS_LIST = "user/profile/get_avatars";
export const UPDATE_PROFILE_PICTURE = "user/profile/update_profile_picture";

//lobby
export const GET_LOBBY_FIXTURE = "fantasy/lobby/get_lobby_fixture";
export const GET_FIXTURE_CONTEST_LIST = "fantasy/lobby/get_fixture_contest";
export const GET_FIXTURE_DETAIL = "fantasy/lobby/get_fixture_details";
export const GET_CONTEST_DETAIL = "fantasy/contest/get_contest_detail";
export const GET_CONTEST_USERS = "fantasy/contest/get_contest_users";
export const GET_USER_LINEUP_LIST = "fantasy/lineup/get_user_lineup_list";
export const GET_MY_COLLECTION_LIST = "fantasy/contest/get_user_joined_fixture";
export const GET_MY_CONTEST_LIST = "fantasy/contest/get_user_joined_contest";
export const GET_CONTEST_LEADERBOARD = "fantasy/contest/get_contest_leaderboard";
export const GET_CONTEST_USER_LEADERBOARD = "fantasy/contest/get_contest_user_leaderboard_teams";
export const GET_GAME_USER_JOIN_COUNT = "fantasy/contest/get_user_contest_join_count";
export const GET_PUBLIC_CONTEST = "fantasy/contest/get_public_contest";
export const GET_LOBBY_BANNER_LIST = "user/auth/get_lobby_banner_list";
export const GET_USER_LINEUP_TEAM_NAME = "fantasy/lineup/get_user_match_team_data";
export const GET_MY_LOBBY_FIXTURE = "fantasy/contest/get_lobby_joined_fixtures"; //"fantasy/contest/get_my_lobby_fixtures";
// Finance
export const GET_TRANSACTION_HISTORY = "user/finance/get_transaction_history";
export const GET_USER_BALANCE = "user/finance/get_user_balance";
export const WITHDRAW_BALANCE = "user/finance/withdraw";
export const DEPOSIT_BY_PAYUMONEY = "user/payumoney/deposit";
export const DEPOSIT_BY_PAYTM = "user/paytm/deposit";
export const DEPOSIT_BY_PAYPAL = "user/paypal/deposit";
export const DEPOSIT_BY_IPAY = "user/ipay/deposit";
export const DEPOSIT_BY_SIRIUS_PAY = "user/siriuspay/deposit";
export const DEPOSIT_BY_PAYSTACK = "user/paystack/deposit";
export const DEPOSIT_BY_RAZORPAY = "user/razorpay/deposit";
export const DEPOSIT_BY_VPAY = "user/vpay/deposit";
export const DEPOSIT_BY_DIRECTPAY = "user/directpay/deposit";
export const VALIDATE_PROMO_CODE = "user/finance/validate_promo_code";
export const GET_DEALS = "user/finance/get_deals";
export const GET_PENDING_WITHDRAW = "user/finance/get_pending_withdraw";
export const DEPOSIT_BY_STRIPE = "user/stripe/deposit";
export const GET_PROMO_CODES = "user/finance/get_promo_codes";

export const DEPOSIT_BY_CRYPTO = "user/crypto/deposit";
export const DEPOSIT_BY_IFANTASY = "user/ifantasy/deposit";
export const DEPOSIT_BY_CASHFREE = "user/cashfree/deposit";
export const DEPOSIT_BY_CASHIERPAY = "user/cashierpay/deposit";
export const DEPOSIT_BY_PAYLOGIC = "user/paylogic/deposit";
export const DEPOSIT_BY_BTCPAY = "user/btcpay/deposit";
export const DEPOSIT_BY_MPESA = "user/mpesa/deposit";
export const DEPOSIT_BY_PHONEPE = "user/phonepe/deposit";
export const DEPOSIT_BY_JUSPE = "user/juspay/deposit";

//ReferFriendFragment
export const GET_AFFILIATE_MASTER_DATA = "user/affiliate/get_affiliate_master_data";
export const GET_MASTER_DATA_REF = "user/affiliate/get_master_data";
export const UPDATE_REF_CODE = "user/profile/update_referral_code";
export const GET_AFFILIATE_MYREFERRAL_LIST = "user/affiliate/get_referral_list";
export const GET_USER_EARN_MONEY = "user/affiliate/get_user_earning_by_friend";
export const GET_SAVE_SHORTEN_URL = "user/shorturl/save_shortened_url";
export const GET_SHORTENED_URL = "user/shorturl/get_shortened_url";
export const GET_CONTEST_LINEUP_EXPORT = "fantasy/contest/download_contest_teams";
// Join Game  
export const JOIN_FANTASY_CONTEST_GAME = "fantasy/contest/join_game";
export const MULTITEAM_JOIN_GAME = "fantasy/contest/multiteam_join_game";
export const GET_USER_SWITCH_TEAM_LIST = "fantasy/contest/get_user_switch_team_list";
export const SWITCH_TEAM_CONTEST = "fantasy/contest/switch_team_contest";
// Promo Code 
export const VALIDATE_CONTEST_PROMO_CODE = "fantasy/contest/validate_contest_promo_code";

//Notification
export const GET_UNREAD_NOTIFICATION = "user/notification/get_unread_notification";
export const GET_USER_NOTIFICAITONS = "user/notification/get_notification";

//Create A League Api's  
export const CREATE_CONTEST_MASTER_DATA = "fantasy/private_contest/create_contest_master_data";
export const GET_MATCHES_BY_LEAGUE_ID = "fantasy/private_contest/get_matches_by_league_id";
export const CREATE_USER_CONTEST = "fantasy/private_contest/create_user_contest";

//Have A LeagueCode Api
export const CHECK_ELIGIBILITY_FOR_CONTEST = "fantasy/contest/validate_contest_code"; // "fantasy/contest/check_eligibility_for_contest";

//Scorring Api 
export const GET_SCORING_MASTER_DATA = "fantasy/contest/get_scoring_master_data";



//Lobby/Contest List Filter Master Data Api
export const GET_FILTER_MASTER_DATA = "fantasy/lobby/get_lobby_filter";
export const ACTIVATE_ACCOUNT = "user/auth/activate_account";
export const GET_BANNED_STATE = "user/auth/get_banned_state";

//For edit profile -> resend email verification link
export const RESEND_EMAIL_VERIFICATION_LINK = "user/auth/resend_email_verification_link";
export const GET_DOWNLOAD_APP_LINK = "user/auth/send_applink";
//Logout Api 
export const GET_LOGOUT = "user/auth/logout";


//Spin Wheel Api
export const GET_SPIN_THE_WHEEL_DATA = "user/spinthewheel/get_spinthewheel";
export const CLAIM_SPIN_THE_WHEEL = "user/spinthewheel/win_spinthewheel";

//Coins Api
export const GET_DAILYCOIN = "user/coins/get_daily_streak_coins";
export const CLAIM_COINS = "user/coins/claim_coins";
export const EARN_COIN_LIST = "user/coins/get_earn_coins_list";
export const GET_REWARD_LIST = "user/coins/get_reward_list";
export const REDEEM_REWARD = "user/coins/redeem_reward";
export const UPDATE_USER_SETTING = "user/profile/update_user_setting";
export const GET_FB_LIST = "user/coins/get_feedback_question_list";
export const SAVE_FEEDBACK = "user/coins/save_feedback";

//Pickem Api
export const GET_FIXTURE_LIST = "pickem/lobby/get_fixture_list";
export const GET_FILTER_BY_LEAGUE = "pickem/lobby/get_filter_leagues";
export const SUBMIT_PICKEM = "pickem/lobby/submit_pickem";
export const GET_MY_PICKEM = "pickem/lobby/get_my_pickem";
export const GET_MY_PICKEM_PARTICIPANTS = "pickem/lobby/get_pickem_participants";
export const GET_PICKEM_DETAIL = "pickem/lobby/get_pickem_detail";
export const GET_PICKEM_LEADERBOARD = "pickem/leaderboard/get_pickem_leaderboard";
export const GET_WEEK_LIST = "pickem/leaderboard/get_week_list";
export const GET_MONTH_LIST = "pickem/leaderboard/get_month_list";
export const GET_LEADERBOARD = "pickem/leaderboard/get_leaderboard";
export const GET_USER_WEEKLY_LEADERBOARD = "pickem/leaderboard/get_user_weekly_leaderboard";
export const GET_USER_MONTHLY_LEADERBOARD = "pickem/leaderboard/get_user_monthly_leaderboard";

//MultiGame Api.
export const GET_LOBBY_MULTI_GAME = "fantasy/multigame/get_lobby_fixture";
export const GET_FIXTURE_DETAIL_MULTI_GAME = "fantasy/multigame/get_fixture_details";
export const GET_CONTEST_DETAIL_MULTI_GAME = "fantasy/multigame/get_contest_detail";
export const GET_PUBLIC_CONTEST_MULTI_GAME = "fantasy/multigame/get_public_contest";
export const GET_MY_COLLECTION_LIST_MULTI_GAME = "fantasy/multigame/get_user_joined_fixture_by_status";
export const CHECK_ELIGIBILITY_FOR_CONTEST_MULTI_GAME = "fantasy/multigame/check_eligibility_for_contest";
//Prediction
export const GET_LOBBY_PREDICTION = "prediction/prediction/get_lobby_fixture";
export const GET_PREDICTION_LEADERBORD = "prediction/prediction/get_prediction_leaderboard";
export const GET_PREDICTION_USERS = "prediction/prediction/get_prediction_participants";
export const GET_PREDICTIONS_CONTEST = ":4000/prediction/get_predictions";
export const GET_PREDICTIONS_DETAIL = ":4000/prediction/get_prediction_detail";
export const MAKE_PREDICTIONS = "prediction/prediction/make_prediction";
export const PREDICTIONS_SEASON = ":4000/prediction/get_my_prediction_season";
export const MY_PREDICTIONS = ":4000/prediction/get_my_contest_fixtures_predictions";
export const CHECK_PREDICTION_JOINED = ":4000/prediction/check_prediction_user_joined";
export const GET_LIVE_MATCH_LIST = ":4000/fixtures/live_match_list";

//FreeToPlay
export const GET_LOBBY_FREE_TO_PLAY = "fantasy/freetoplay/get_lobby_fixture";
export const GET_MY_CONTEST_LIST_FREE_TO_PLAY = "fantasy/freetoplay/get_user_joined_fixture_by_status";
export const GET_MINI_LEAGUE_BY_STATUS = "fantasy/mini_league/get_mini_league_by_status"
export const GET_MINI_LEAGUE_DETAILS = "fantasy/mini_league/get_mini_league_detail"
export const GET_FIXTURE_MINI_LEAGUE= "fantasy/freetoplay/get_fixture_mini_leagues"
export const GTE_MINI_LEAGUE_LEADER_BOARD="fantasy/mini_league/get_mini_league_leaderboard"
export const GTE_USER_MINI_LEAGUE_LEADER_BOARD="fantasy/mini_league/get_user_mini_league_leaderboard"
export const GTE_USER_MINI_LEAGUE_LEADER_BOARD_MATCHES="fantasy/mini_league/get_user_leaderboard_matches"
export const GET_MINI_LEAGUE_MY_CONTEST_LIST = "fantasy/freetoplay/get_user_contest_by_status";
export const GET_MINI_LEAGUE_UPCOMING_FIXTURE="fantasy/freetoplay/get_upcoming_fixture"

//Open Prediction
export const GET_LOBBY_OPEN_PREDICTION = "prediction/open_predictor/get_lobby_fixture";
export const GET_OPEN_PREDICTION_LEADERBORD = "prediction/open_predictor/get_prediction_leaderboard";
export const GET_OPEN_PREDICTION_USERS = "prediction/open_predictor/get_prediction_participants";
export const GET_OPEN_PREDICTIONS_CONTEST = ":4000/open_predictor/get_predictions";
export const GET_OPEN_PREDICTIONS_DETAIL = ":4000/open_predictor/get_prediction_detail";
export const MAKE_OPEN_PREDICTIONS = "prediction/open_predictor/make_prediction";
export const GET_MY_PREDICTION_CATEGORY = ":4000/open_predictor/get_my_prediction_category";
export const MY_OPEN_PREDICTIONS = ":4000/open_predictor/get_my_contest_category_predictions";
export const CHECK_OPEN_PREDICTION_JOINED = ":4000/open_predictor/check_prediction_user_joined";
export const GET_FIXED_PREDICTION_CATEGORY = "prediction/open_predictor/get_fixed_prediction_categories";
export const GET_FIXED_PREDICTION_LEADERBOARD = "prediction/open_predictor/get_fixed_prediction_leaderboard";

//Open Prediction With Fixed Prize Pool
export const GET_FPP_LOBBY_OPEN_PREDICTION = "prediction/fixed_open_predictor/get_lobby_fixture";

// export const GET_OPEN_PREDICTION_LEADERBORD = "prediction/open_predictor/get_prediction_leaderboard";
export const GET_FPP_OPEN_PREDICTION_USERS = "prediction/fixed_open_predictor/get_prediction_participants";
export const GET_FPP_OPEN_PREDICTIONS_CONTEST = ":4000/fixed_open_predictor/get_predictions";
export const GET_FPP_OPEN_PREDICTIONS_DETAIL = ":4000/fixed_open_predictor/get_prediction_detail";
export const MAKE_FPP_OPEN_PREDICTIONS = "prediction/fixed_open_predictor/make_prediction";
export const GET_MY_FPP_PREDICTION_CATEGORY = ":4000/fixed_open_predictor/get_my_prediction_category";
export const MY_FPP_OPEN_PREDICTIONS = ":4000/fixed_open_predictor/get_my_contest_category_predictions";
export const GET_FPP_FIXED_PREDICTION_CATEGORY = "prediction/fixed_open_predictor/get_fixed_prediction_categories";
export const GET_FPP_FIXED_PREDICTION_LEADERBOARD = "prediction/fixed_open_predictor/get_open_predictor_leaderboard";


export const GET_MY_CONTEST_TEAM_COUNT = "fantasy/contest/get_my_contest_team_count"; 
export const GET_NEW_CONTEST_LEADERBOARD = "fantasy/contest/get_contest_leaderboard"; //"fantasy/contest/get_new_contest_leaderboard"

//Affiliate
export const BECOME_AFFILATE_USER = "user/profile/become_affiliate";
export const GET_AFFILIATE_SUMMARY = "user/profile/get_affiliate_summary";
export const GET_AFFILIATE_TRANSACTIONS = "user/profile/get_affiliate_transactions";
// mutigame standarization api
export const GET_MY_MULTIGAME_CONTEST_TEAM_COUNT = "fantasy/multigame/get_my_contest_team_count"; 
export const GET_MULTIGAME_USER_CONTEST_BY_STATUS = "fantasy/multigame/get_user_contest_by_status"; 
export const GET_MULTIGAME_USER_LINEUP_LIST = "fantasy/multigame/get_user_lineup_list"; 
export const GET_MULTIGAME_MY_LOBBY_FIXTURE = "fantasy/multigame/get_my_lobby_fixtures"; 
// buy coin module
export const GET_COINS_PACKAGE_LIST = "user/coins_package/get_coins_package_list";
export const BUY_COINS = "user/coins_package/buy_coins";

//SCORE CARD
export const GET_CONTEST_SCORE_CARD = "fantasy/contest/get_contest_score_card";


// self exclusion api's
export const GET_USER_SELF_EXCLUSION = "user/selfexclusion/get_user_self_exclusion";
export const SET_SELF_EXCLUSION = "user/selfexclusion/set_self_exclusion";

//CASHFREE
export const GET_CASHFREE_GATWAY_LIST = "user/cashfree/get_wallet_bank_list";

//Referal 
export const GET_REFERAL_LEADERBOARD="/fantasy/referral_leaderboard/get_referral_leaderboard"
export const GET_REFERAL_PRIZES="/fantasy/referral_leaderboard/get_referral_prizes"

//Network Fantasy
export const MULTITEAM_JOIN_GAME_NETWORK_FANTASY = "network/contest/multiteam_join_game";

export const MASTER_DATA_NETWORK_FANTASY="network/auth/get_app_master_list"
export const GET_FIXTURE_DETAIL_NETWORK_FANTASY = "network/lobby/get_fixture_details";
export const GET_FIXTURE_CONTEST_LIST_NETWORK_FANTASY = "network/lobby/get_fixture_contest";
export const GET_USER_LINEUP_LIST_NETWORK_FANTASY = "network/lobby/get_user_lineup_list";
export const GET_NEW_CONTEST_LEADERBOARD_NETWORK_FANTASY = "network/contest/get_new_contest_leaderboard"; 
export const GET_CONTEST_SCORE_CARD_NETWORK_FANTASY = "network/contest/get_contest_score_card";
export const GET_PLAYER_BREAKDOWN_NETWORK_FANTASY = "network/common/get_player_breakdown";

export const JOIN_FANTASY_CONTEST_GAME_NETWORK_FANTASY = "network/contest/join_game";
export const GET_CONTEST_DETAIL_NETWORK_FANTASY = "network/contest/get_contest_detail";
export const GET_GAME_USER_JOIN_COUNT_NETWORK_FANTASY = "network/contest/get_user_contest_join_count";
export const GET_CONTEST_USERS_NETWORK_FANTASY = "network/contest/get_contest_users";

export const GET_USER_LINEUP_NETWORK_FANTASY = "network/lineup/get_user_lineup";
export const GET_LINEUP_MASTER_DATA_NETWORK_FANTASY = "network/lineup/get_lineup_master_data";

export const GET_ALL_ROSTER_NETWORK_FANTASY = "network/lineup/get_all_roster";
export const GET_USER_LINEUP_TEAM_NAME_NETWORK_FANTASY = "network/lineup/get_user_match_team_data";

export const LINEUP_PROCCESS_NETWORK_FANTASY = "network/lineup/lineup_proccess"; // Create/Edit Lineup
export const SWITCH_TEAM_CONTEST_NETWORK_FANTASY = "network/contest/switch_team_contest";

export const GET_INVITE_CODE_NF = "network/contest/get_contest_invite_code";
export const GET_SAVE_SHORTEN_URL_NF = "network/common/save_shortened_url";
export const GET_SHORTENED_URL_NF = "network/common/get_shortened_url";
export const GET_PUBLIC_CONTEST_NF = "network/contest/get_public_contest";
export const GET_LINPEUP_WITH_SCORE_NF = "network/contest/get_linpeup_with_score";
export const GET_PLAYERCARD_NF = "network/common/get_playercard";
export const GET_USER_BALANCE_NF = "network/user/get_user_balance";
export const GET_MY_COLLECTION_LIST_NF = "network/contest/get_user_joined_fixture_by_status";
export const GET_MY_CONTEST_LIST_NF = "network/contest/get_user_contest_by_status";
export const GET_USER_SWITCH_TEAM_LIST_NF = "network/contest/get_user_switch_team_list";
export const GET_CONTEST_LINEUP_EXPORT_NF = "network/contest/download_contest_teams";
export const GET_CONTEST_LEADERBOARD_NF = "network/contest/get_contest_leaderboard";
export const GET_CONTEST_USER_LEADERBOARD_NF = "network/contest/get_contest_user_leaderboard_teams";
//Team Comparison
export const GET_LINPEUP_WITH_TEAM_COMPARE = "fantasy/contest/get_compare_teams";
// Session tracking 
export const TRACK_ACTIVE_SESSION = "user/profile/track_active_session";
// Session tracking
//Pickem Tournament
export const GET_PICKEM_MY_LOBBY_TOURNAMENT = "pickem/lobby/get_my_lobby_tournaments";
export const GET_PICKEM_TOURNAMENT_MATCH = "pickem/lobby/get_tournament_match";
export const GET_PICKEM_TOURNAMENT_DETAIL = "pickem/lobby/get_tournament_detail";
export const GET_PICKEM_TOURNAMENT_RULES = "pickem/lobby/get_rules";
export const GET_PICKEM_JOIN_TOURNAMENT = "pickem/lobby/join_tournament";
export const SUBMIT_TOUR_PICKEM = "pickem/lobby/submit_pickem_season";
export const EDIT_TOURNAMENT_PICK = "pickem/lobby/edit_pickem";
export const GET_PICK_TOURNAMENT_LEADERBOARD = "pickem/leaderboard/tournament_leaderboard/get_tournament_leaderboard";
export const GET_MY_PICK_TOURNAMENT = "pickem/lobby/get_my_tournament";
export const GET_PICK_TOUR_PARTICIPANTS = "pickem/lobby/get_pickem_season_participants";
export const GET_USER_PICK_TOUR_HISTORY = "pickem/leaderboard/tournament_leaderboard/get_user_tournament_history";
//DFS Tournament
export const GET_DFS_TOURNAMENT_DETAIL = "fantasy/tournament/get_tournament_detail";
export const GET_DFS_TOURNAMENT_MATCH = "fantasy/tournament/get_tournament_match";
export const GET_DFS_TOUR_LINEUP_MASTER_DATA = "fantasy/tournament/tournament_lineup/get_lineup_master_data";
export const GET_DFS_TOUR_ALL_ROSTER = "fantasy/tournament/tournament_lineup/get_all_roster";
export const GET_DFS_TOUR_USER_LINEUP_TEAM_NAME = "fantasy/tournament/tournament_lineup/get_user_match_team_data";
export const DFS_TOUR_LINEUP_PROCCESS = "fantasy/tournament/tournament_lineup/lineup_proccess";
export const GET_DFS_TOUR_USER_LINEUP_LIST = "fantasy/tournament/tournament_lineup/get_user_lineup_list";
export const GET_DFS_JOIN_TOURNAMENT = "fantasy/tournament/tournament/join_tournament";
export const GET_DFS_JOIN_TOURNAMENT_SEASON = "fantasy/tournament/tournament/join_tournament_season";
export const GET_DFS_TOUR_USER_LINEUP = "fantasy/tournament/tournament_lineup/get_user_lineup";
export const GET_DFS_TOUR_FIXTURE_LEADERBOARD = "fantasy/tournament/tournament_leaderboard/get_tournament_season_leaderboard";
export const GET_DFS_TOURNAMENT_LEADERBOARD = "fantasy/tournament/tournament_leaderboard/get_tournament_leaderboard";
export const GET_DFS_MY_LOBBY_TOURNAMENT = "fantasy/tournament/tournament/get_my_lobby_tournaments";
export const GET_DFS_MY_TOURNAMENT = "fantasy/tournament/tournament/get_my_tournament";
export const GET_DFS_TOUR_RULES = "fantasy/tournament/tournament/get_rules";
export const GET_USER_DFS_TOUR_HISTORY = "fantasy/tournament/tournament_leaderboard/get_user_tournament_history";
export const GET_DFS_TOUR_LINPEUP_WITH_SCORE = "fantasy/tournament/tournament_lineup/get_lineup_with_score";
export const GET_DFS_TOUR_INVITE_CODE = "fantasy/tournament/tournament/get_tournament_invite_code";
export const GET_PUBLIC_DFS_TOURNAMENT = "fantasy/tournament/tournament/get_public_tournament";
export const GET_USER_JOINED_DFS_TOURNAMENT_IDS = "fantasy/tournament/get_user_joined_tournament_ids";
export const GET_DFS_TOURNAMENT_LIST = "fantasy/tournament/get_lobby_tournaments";

export const GET_RENDOM_SCRATCH_CARD = "user/scratchwin/get_rendom_scratch_card";
export const CLAIM_SCRATCH_CARD = "user/scratchwin/claim_scratch_card";
export const GET_SCRATCH_CARD = "user/scratchwin/get_scratch_card";

export const GET_FIXTURE_SCORECARD = "fantasy/stats/get_fixture_scorecard";
export const GET_FIXTURE_STATS = "fantasy/stats/get_collection_stats";//"fantasy/stats/get_contest_stats";
// XP Point Module
export const GET_XP_REWARD_LIST = "user/xp_point/get_reward_list";
export const GET_XP_ACTIVITY_LIST = "user/xp_point/get_activity_list";
export const GET_USER_XP_CARD = "user/xp_point/get_user_xp_card";
export const GET_USER_XP_HISTORY = "user/xp_point/get_user_xp_history";
export const GET_MY_PUBLIC_PROFILE = "user/profile/user_detail";

export const GET_FR_LEADERBOARD_MASTER = "user/leaderboard/get_leaderboard_master";
export const GET_FR_LEADERBOARD_LIST = "user/leaderboard/get_leaderboard_list";
export const GET_FR_LEADERBOARD_DETAIL = "user/leaderboard/get_user_leaderboard_detail";

export const GET_L_STOCK_FIXTURE = "stock/lobby/get_lobby_fixture";
export const GET_MY_L_STOCK_FIXTURE = "stock/contest/get_my_lobby_fixtures";
export const GET_STOCK_CONTEST_LIST = "stock/lobby/get_fixture_contest";
export const GET_STOCK_CONTEST_BY_STATUS = "stock/contest/get_user_contest_by_status";
export const GET_STOCK_JOINED_FIXTURE_BY_STATUS = "stock/contest/get_user_joined_fixture_by_status";
export const GET_STOCK_USER_LINEUP = "stock/lineup/get_user_lineup";
export const GET_STOCK_USER_LINEUP_LIST = "stock/lobby/get_user_lineup_list";
export const GET_STOCK_CONTEST_TEAM_COUNT = "stock/contest/get_my_contest_team_count";
export const GET_STOCK_GET_FIXTURE_DETAILS = "stock/lobby/get_fixture_details";
export const GET_STOCK_FILTER_MASTER_DATA = "stock/lobby/get_lobby_filter";
export const GET_STOCK_LINEUP_MASTER_DATA = "stock/lineup/get_lineup_master_data";
export const GET_STOCK_USER_MATCH_TEAM_DATA = "stock/lineup/get_user_match_team_data";
export const GET_STOCK_ALL_STOCKS = "stock/lineup/get_all_stocks";
export const STOCK_LINEUP_PROCCESS = "stock/lineup/lineup_proccess";
export const STOCK_PLAYER_CARD = "stock/common/get_playercard";
export const STOCK_ADD_REMOVE_WISHLIST = "stock/wishlist/toggle";
export const STOCK_JOIN_GAME = "stock/contest/join_game";
export const STOCK_GET_CONTEST_DETAIL = "stock/contest/get_contest_detail";
export const GET_SF_GAME_USER_JOIN_COUNT = "stock/contest/get_user_contest_join_count";
export const GET_STOCK_CONTEST_USERS = "stock/contest/get_contest_users";
export const GET_STOCK_WISHLIST = "stock/wishlist/list";
export const GET_STOCK_INVITE_CODE = "stock/contest/get_contest_invite_code";
export const STOCK_CHECK_ELIGIBILITY_FOR_CONTEST = "stock/contest/check_eligibility_for_contest";
export const STOCK_GET_PUBLIC_CONTEST = "stock/contest/get_public_contest";
export const STOCK_GET_STOCKCARD_DETAILS = "stock/stock/card";
export const STOCK_GET_STATICS = "stock/stock/statics";
export const STOCK_SWITCH_TEAM_CONTEST = "stock/contest/switch_team_contest";
export const STOCK_SWITCH_TEAM_LIST = "stock/contest/get_user_switch_team_list";
export const STOCK_MULTITEAM_JOIN_GAME = "stock/contest/multiteam_join_game";
export const STOCK_CREATE_CONTEST_MASTER_DATA = "stock/contest/private_contest/create_contest_master_data";
export const STOCK_CREATE_PRIVATE_CONTEST = "stock/contest/private_contest/create_user_contest";
export const STOCK_DOWNLOAD_CONTEST_TEAMS = "stock/contest/download_contest_teams";
export const STOCK_LEADERBOARD = "stock/contest/get_new_contest_leaderboard";
export const STOCK_GET_COMPARE_TEAMS = "stock/contest/get_compare_teams";
export const STOCK_GET_LINPEUP_WITH_SCORE = "stock/contest/get_linpeup_with_score";
export const VALIDATE_STOCK_PROMO_CODE = "stock/contest/validate_contest_promo_code";
export const GET_STOCK_LOBBY_BANNER_LIST = "stock/lobby/get_lobby_banner_list";
export const STOCK_LINEUP_SCORE_CALCULATION = "stock/contest/get_lineup_score_calculation";
export const STOCK_LOBBY_SETTING = "stock/lobby/stock_setting";
export const STOCK_CONTEST_LEADERBOARD = "stock/contest/get_contest_leaderboard";
export const STOCK_LINEUP_PROCCESS_EQUITY = "stock/lineup/equity/lineup_proccess";
export const STOCK_TEAM_COMPARE_EQUITY = "stock/lineup/equity/get_compare_teams";
export const GET_STOCK_USER_LINEUP_EQUITY = "stock/lineup/equity/get_user_lineup";
export const GET_SCORE_CALCULATION_EQUITY = "stock/lineup/equity/get_lineup_score_calculation";
export const GET_CONTEST_STK_STATICS = "stock/stock/equity/get_collection_statics";

export const GET_STOCK_LEADERBOARD_DETAIL = "stock/leaderboard/get_user_leaderboard_detail";
export const GET_STOCK_PLAYING_EXPERIENCE = "stock/user/profile/get_playing_experience";


//Booster
export const GET_COLLECTION_BOOSTER = "fantasy/booster/get_collection_booster";
export const APPLY_BOOSTER = "fantasy/booster/save_booster"
export const GET_BOOSTER_LIST = "fantasy/booster/get_booster_list";
//Bench
export const SAVE_BENCH_PLAYER = "fantasy/lineup/save_bench_player";

//Guru Module
export const GENRATE_GURU_LINEUP = "fantasy/lineup/generate_team";

//Quiz Module
export const GET_QUIZ_QUESTION = "user/quiz/get_questions";
export const GET_QUIZ_CHECK_ANS = "user/quiz/check_answer";
export const APPLY_QUIZ_CLAIM = "user/quiz/claim_quiz";

//Download App Coin Claim 
export const CLAIM_DOWNLOAD_APP_COIN = "user/coins/claim_download_app_coins";

// Download Shorten URL
export const GET_SOURCE_URL = "user/auth/get_source_url";

// Stock Prediction Module
export const GET_SP_LOBBY_FILTER = "stock/predict/get_lobby_filter";
export const GET_SP_LBY_CONTEST_LT = "stock/predict/get_contest_list";
export const GET_SP_LINEUP_MATSER_DATA = "stock/predict/get_lineup_master_data";
export const GET_SP_ALL_STOCKS = "stock/lineup/get_all_stocks";
export const GET_SP_LINEUP_PROCESS = "stock/predict/lineup_proccess";
export const GET_SP_USER_CONTEST_BY_STATUS = "stock/predict/get_user_contest_by_status";
export const GET_SP_COLLECTION_STATICS = "stock/predict/get_collection_statics";
export const GET_SP_USER_LINEUP_LIST = "stock/predict/get_user_lineup_list";
export const GET_SP_MY_LOBBY_CONTEST = "stock/predict/get_my_lobby_contest";
export const GET_SP_USER_LINEUP = "stock/predict/get_user_lineup";
export const GET_SP_TEAM_COMPARE = "stock/predict/get_compare_teams";
export const GET_SP_SCORE_CALCULATION = "stock/predict/get_lineup_score_calculation";
export const GET_SP_PREDICTION_HISTORY = "stock/predict/get_user_predict_detail";
export const GET_SP_UPDATED_HISTORY = "stock/predict/get_last_predict_value";


//H2H Challange 
export const GET_H2H_CHALLANGE_CONTEST_LIST = "fantasy/h2h/get_h2h_games"; 
export const GET_H2H_CHALLANGE_BANNER_LIST = "fantasy/h2h/get_h2h_cms"; 
export const JOIN_FANTASY_CONTEST_GAME_H2H = "fantasy/h2h/join_game";
export const GET_USER_H2H_CONTEST = "fantasy/h2h/get_user_h2h_contest";

export const VERIFY_CRYPTO_DETAILS = "user/profile/verify_crypto_wallet";

// New Affiliate Visit
export const ADD_VISIT = "user/auth/add_visit";
export const UPI_INTENT_CALLBACK = "user/cashfree/upi_intent_callback";
export const PAYUMONEY_MARK_SUCCESS = "user/payumoney/mark_success";


//Live Fantasy
export const LF_GET_LOBBY_FIXTURE = "livefantasy/lobby/get_lobby_fixture";
export const LF_GET_FIXTURE_CONTEST_LIST = "livefantasy/lobby/get_fixture_contest";
export const LF_GET_CONTEST_DETAIL = "livefantasy/lobby/get_contest_detail";
export const LF_GET_CONTEST_USERS = "livefantasy/lobby/get_contest_users";
export const LF_GET_PUBLIC_CONTEST = "livefantasy/lobby/get_public_contest";
export const LF_CHECK_ELIGIBILITY_FOR_CONTEST = "livefantasy/lobby/check_eligibility_for_contest";
export const LF_JOIN_FANTASY_CONTEST_GAME = "livefantasy/lobby/join_game";
export const LF_GET_MY_LOBBY_FIXTURE = "livefantasy/lobby/get_my_lobby_fixtures";
export const LF_GET_MY_CONTEST_LIST = "livefantasy/lobby/get_user_contest_by_status";
export const LF_GET_INVITE_CODE = "livefantasy/lobby/get_contest_invite_code";
export const LF_GET_MY_COLLECTION_LIST = "livefantasy/lobby/get_user_joined_fixture_by_status";
export const LF_GET_FIXTURE_DETAIL = "livefantasy/lobby/get_fixture_details";
export const LF_GET_OVER_DETAIL = "livefantasy/lobby/get_match_over_ball";
export const LF_PREDICT_ANSWER = "livefantasy/lobby/save_user_prediction";
export const LF_GET_OVER_ODDS_BALL = "livefantasy/lobby/get_over_ball_odds";
export const LF_GET_MATCH_PLAYERS = "livefantasy/lobby/get_match_players";
export const LF_GET_CONTEST_LEADERBOARD = "livefantasy/lobby/get_contest_leaderboard";
export const LF_GET_USER_MATCH_STATUS = "livefantasy/lobby/get_user_match_stats";
export const LF_VALIDATE_CONTEST_PROMO_CODE = "livefantasy/lobby/validate_contest_promo_code";
export const LF_GET_USER_TEAM_STATS = "livefantasy/lobby/get_user_team_stats";
export const LF_GET_USER_LIVE_OVERS = "livefantasy/lobby/get_user_live_overs";
export const CREATE_CONTEST_MASTER_DATA_LF = "livefantasy/private_contest/create_contest_master_data";
export const CREATE_USER_CONTEST_LF = "livefantasy/private_contest/create_user_contest";
export const LF_NEXT_OVER_DETAILS= "livefantasy/lobby/get_next_over";

// Lve Stock Fantasy
export const GET_LSF_LOBBY_FILTER = "stock/livestockfantasy/get_lobby_filter";
export const GET_LSF_LBY_CONTEST_LT = "stock/livestockfantasy/get_contest_list";
export const LSF_JOIN_GAME = "stock/livestockfantasy/join_game";
export const GET_LSF_MY_LOBBY_CONTEST = "stock/livestockfantasy/get_my_lobby_contest";
export const GET_LSF_LINEUP_MATSER_DATA = "stock/livestockfantasy/get_lineup_master_data";
export const GET_LSF_ALL_STOCKS = "stock/livestockfantasy/get_all_stocks";
// export const GET_SP_LINEUP_PROCESS = "stock/predict/lineup_proccess";
export const GET_LSF_USER_CONTEST_BY_STATUS = "stock/livestockfantasy/get_user_contest_by_status";
export const GET_LSF_COLLECTION_STATICS = "stock/livestockfantasy/get_collection_statics";
// export const GET_SP_USER_LINEUP_LIST = "stock/predict/get_user_lineup_list";
// export const GET_SP_MY_LOBBY_CONTEST = "stock/predict/get_my_lobby_contest";
export const GET_LSF_USER_LINEUP = "stock/livestockfantasy/get_user_lineup";
export const LSF_USER_TRADE = "stock/livestockfantasy/user_trade";
// export const GET_SP_TEAM_COMPARE = "stock/predict/get_compare_teams";
// export const GET_SP_SCORE_CALCULATION = "stock/predict/get_lineup_score_calculation";
export const GET_LSF_HOLIDAY_LIST = "stock/livestockfantasy/get_holiday";
export const GET_LSF_USER_TRANSACTION = "stock/livestockfantasy/get_user_transaction";
export const GET_USER_NOTIFICATION_SETTING= "user/notification/get_user_notifications_setting";
export const UPDATE_USER_NOTIFICATION_SETTING= "user/notification/update_user_notification_setting";


// Single step onboarding 
export const SIGNUP = "user/auth/signup";
export const VALIDATE_EMAIL_OTP_SINGLE = "user/auth/signup_validate";

// Pick Fantasy
export const PF_GET_SPORTS_LIST = "picks/lobby/get_sports_list";
export const PF_LOBBY_FIXTURE = "picks/lobby/get_lobby_fixture";
export const PF_FIXTURE_CONTEST = "picks/lobby/get_fixture_contest";
export const PF_GET_USER_LINEUP = "picks/lobby/get_user_lineup_list";
export const PF_GET_USER_CONTEST_BY_STATUS = "picks/contest/get_user_contest_by_status";
export const PF_GET_MY_CONTEST_TEAM_COUNT = "picks/contest/get_my_contest_team_count";
export const PF_GET_MY_LOBBY_FIXTURES = "picks/contest/get_my_lobby_fixtures";
export const PF_GET_FIXTURE_DETAILS = "picks/lobby/get_fixture_details";
export const PF_GET_CONTEST_DETAILS = "picks/contest/get_contest_detail";
export const PF_GET_CONTEST_USERS = "picks/contest/get_contest_users";
export const PF_GET_LINEUP_MASTER_DATA = "picks/lineup/get_lineup_master_data";
export const PF_GET_ALL_ROSTER = "picks/lineup/get_all_roster";
export const PF_LINEUP_PROCESS = "picks/lineup/lineup_process";
export const PF_JOIN_GAME = "picks/contest/join_game";
export const PF_GET_TEAM_NAME = "picks/lineup/get_team_name_by_season_id";

export const PF_GET_USER_JOINED_FIXTURE_BY_STATUS = "picks/contest/get_user_joined_fixture_by_status";
export const PF_GET_CONTEST_LEADERBOARD = "picks/contest/get_contest_leaderboard";
export const PF_GET_USER_LINEUP_DATA = "picks/lineup/get_user_lineup";
export const PF_GET_USER_SWITCH_TEAM_LIST = "picks/contest/get_user_switch_team_list";
export const PF_SWITCH_TEAM_CONTEST = "picks/contest/switch_team_contest";






// New servicious payment
export const SERVICIOUS = "user/servicious/deposit";

//aadhar verification

export const DO_UPLOAD_AADHAR = "user/profile/do_upload_aadhar";
export const SAVE_AADHAR_DETAIL = "user/profile/save_aadhar";
export const GET_USER_AADHAR_DETAIL = "user/profile/get_user_aadhar"
export const GENERATE_AADHAR_OTP = "user/profile/generate_aadhar_otp";
export const VERIFY_AADHAR_OTP = "user/profile/verify_aadhar_otp";

//GST report

export const GET_GST_REPORT = "fantasy/contest/gst_invoice";

// New DFS tournament
export const GET_DFST_LOBBY_TOURNAMENT = "fantasy/tournament/get_lobby_tournament"
export const GET_DFST_TOURNAMENT_LIST = "fantasy/tournament/get_tournament_list"
export const GET_DFST_TOURNAMENT_DETAIL = "fantasy/tournament/get_tournament_details"
export const GET_DFST_TOURNAMENT_LEADERBOARD = "fantasy/tournament/get_leaderboard"
export const GET_DFST_TOURNAMENT_USER_DETAIL = "fantasy/tournament/get_user_team_detail"
//What is New

export const WHAT_IS_NEW_DETAIL = "user/auth/get_whats_new_list";
// New DFS tournament

//Pickem Tournament
export const PT_TOURNAMENT_LIST = "pickem/tournament/get_lobby_tournament_list";
export const PT_JOIN_TOURNAMENT = "pickem/tournament/join_tournament";
export const PT_MY_LOBBY_TOURNAMENT = "pickem/tournament/get_my_lobby_tournament";
export const PT_GET_TOURNAMENT_DETAIL = "pickem/tournament/get_tournament_details";
export const PT_SUBMIT_PICKEM = "pickem/tournament/submit_pickem";
export const PT_LEADERBOARD = "pickem/tournament/get_leaderboard";
export const PT_MY_CONTEST_TOURNAMENT = "pickem/tournament/get_my_contest_tournament";
export const PT_SUBMIT_TIE_BREAKER = "pickem/tournament/save_tie_breaker_answer";

//All Tournament Leaderboard
export const GET_DFS_TOUR_LEAD = "fantasy/tournament/get_tournament_leaderboard";
export const GET_PICKEM_DFS_TOUR_LEAD = "pickem/tournament/get_tournament_leaderboard";

export const SEND_OTP = 'user/finance/generate_wdl_otp';

//TDS
export const GET_TDS_DOCUMENT = "user/finance/get_tds_document";
export const GET_TDS_DETAIL = "user/finance/get_tds_detail";
export const GET_TDS_REPORT = "user/finance/get_tds_report";
export const GET_DFS_SPORTS_SHORT ="fantasy/tournament/get_sports_list_leaderboard";
export const GET_PICKEM_SPORTS_SHORT = "pickem/tournament/get_sports_list_leaderboard";

export const GET_MATCH_SCORECARD_STATS= "fantasy/stats/get_match_stats";
export const GET_USER_MATCH_CONTEST= "fantasy/contest/get_user_match_contest";

//MPG
export const DO_UPLOAD_PROOF ="user/common/do_upload";
export const TYPE_LIST ="user/manualpg/get_type_list";
export const TRANSACTION_UPDATE ="user/manualpg/update_txn_detail";
export const REMOVE_MEDIA_MPG ="user/common/remove_media"

// Optimization: May/June 2023
export const GET_TEAM_DETAIL ="fantasy/lineup/get_team_detail"
export const DFS_CREATE_CONTEST_MASTER_DATA = "fantasy/contest/get_contest_master_data";
export const DFS_CREATE_USER_CONTEST = "fantasy/contest/create_user_contest";


// Tournament changes new apis
export var GET_USER_FIXTURE_TEAMS = "fantasy/tournament/get_user_fixture_teams";

//featured league
export var GET_PICKEM_TOUR_LIST = "pickem/tournament/get_featured_tournament_list";
export var GET_DFS_TOUR_LIST = "fantasy/tournament/get_featured_tournament_list";
//props-fantasy
export var PROPS_SAVE_TEAM = "props/lobby/save_team";
export var OT_GET_SPORTS_LIST = "trade/lobby/get_sports_list";

// PHONEPE
export const PHONEPE_CALLBACK = "user/phonepe/update_txn_status";

//Participants Details

export var PARTICIPANTS_DETAIL = "pickem/tournament/get_fixture_users";



// JUSPE 
export const JUSPAY_CALLBACK = "user/juspay/update_txn_status";

//pickem/tournament/get_user_team_history

export const PT_TEAM_HISTORY = "pickem/tournament/get_user_team_history";

