import ls from 'local-storage';
import { Utilities } from "../Utilities/Utilities";
import WSManager from "./WSManager";
import * as WSC from "./WSConstants";

const S3_URL_PREFIX = WSC.S3_BUCKET_PATH + "appstatic/" + WSC.BUCKET_DATA_PREFIX;
const AppLANG = WSManager ? ('_' + WSManager.getAppLang()) : '';

export function setAdsgraphyTrackingID(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_TRACK_ID, params)
}

export function getMasterData(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "app_master_data.json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.MASTER_DATA, params)
    // return WSManager.Rest(WSC.userURL + WSC.MASTER_DATA, params);
}
export function loadLanguageResource(data) {
    let params = data ? data : {}
    var s3_api_data_url = WSC.S3_BUCKET_PATH + "assets/i18n/translations/" + params.lang_code + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, '', '')
}
export function getStaticPageData(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "static_page_" + params.page_alias + AppLANG + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.STATIC_PAGE_DATA, params)
}
export function getRulePageData(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "scoring_master_data_" + params.sports_id + AppLANG + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_SCORING_MASTER_DATA, params);
}
export function getFilterData(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_FILTER_MASTER_DATA, params);
}
export function getLobbyBanner(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_banner_list_" + params.sports_id + AppLANG + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_BANNER_LIST, params);
}
export function getContestDetails(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_CONTEST_DETAIL, params);
}
export function getUserContestJoinCount(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_GAME_USER_JOIN_COUNT, params);
}
export function getContestUserList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_CONTEST_USERS, params);
}
export function getLobbyFixtures(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_fixture_list_" + params.sports_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_FIXTURE, params);
    // let params = data ? data : {}
    // return WSManager.Rest(WSC.fantasyURL + WSC.GET_LOBBY_FIXTURE, params);
}
export function getMyLobbyFixtures(data) {
    let params = data ? data : {}
    // var s3_api_data_url = S3_URL_PREFIX + "lobby_fixture_list_" + params.sports_id + ".json";
    // return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_MY_LOBBY_FIXTURE, params);
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MY_LOBBY_FIXTURE, params);
}

export function getFixtureDetail(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_FIXTURE_DETAIL, params);
}
export function getUserTeams(data, user_unique_id) {
    let params = data ? data : {}
    // var s3_api_data_url = S3_URL_PREFIX + "user_teams_" + params.collection_master_id + "_" + user_unique_id + ".json";
    // return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_USER_LINEUP_LIST, params);
    return WSManager.RestS3ApiCall('', WSC.GET_USER_LINEUP_LIST, params);
}
export function getLineupMasterData(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lineup_master_data_" + params.collection_master_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LINEUP_MASTER_DATA, params);
}
export function getRosterList(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "collection_roster_list_" + params.collection_master_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_ALL_ROSTER, params);
}
export function getPlayerCard(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_PLAYERCARD, params);
}
export function getPlayerBreakdown(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_PLAYER_BREAKDOWN, params);
}
export function checkContestEligibility(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CHECK_ELIGIBILITY_FOR_CONTEST, params);
}
export function getUserLineUps(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_LINEUP_LIST, params);
}
export function getUserLineUpDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_LINEUP, params);
}
export function getFixtureContestList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_FIXTURE_CONTEST_LIST, params)
}
export function getReferralData(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_SIGNUP_REFERRAL_DATA, params)
}
export function getNewTeamName(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_LINEUP_TEAM_NAME, params);
}
export function getMyCollection(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MY_COLLECTION_LIST, params, cacheEnable);
}
export function getMyContest(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MY_CONTEST_LIST, params, cacheEnable);
}
export function processLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LINEUP_PROCCESS, params);
}
export function getUserBalance(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_BALANCE, params);
}
export function validateFundPromo(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VALIDATE_PROMO_CODE, params);
}
export function withdrawAmount(data) {
    let params = data ? data : {}
    // params['apiversion'] = 'v2';
    return WSManager.Rest(WSC.userURL + WSC.WITHDRAW_BALANCE, params);
}
export function withdrawPending(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_PENDING_WITHDRAW, params);
}
export function depositPaytmFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_PAYTM, params);
}
export function depositPhonepeFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_PHONEPE, params);
}
export function depositJuspeFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_JUSPE, params);
}
export function depositPayPalFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_PAYPAL, params);
}
export function depositIPAYFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_IPAY, params);
}
export function depositSiriusPay(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_SIRIUS_PAY, params);
}
export function depositPayLogic(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_PAYLOGIC, params);
}
export function depositPayStackFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.DEPOSIT_BY_PAYSTACK, params);
}
export function depositPayUmoneyFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_PAYUMONEY, params);
}
export function depositVpayFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_VPAY, params);
}
export function depositDirectPay(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_DIRECTPAY, params);
}
export function depositIFantasyFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_IFANTASY, params);
}
export function depositCashierPay(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_CASHIERPAY, params);
}

export function depositCrypto(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_CRYPTO, params);
}
export function depositCashFreeFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_CASHFREE, params);
}
export function getTranscationHistory(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_TRANSACTION_HISTORY, params);
}
export function validateContestPromo(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.VALIDATE_CONTEST_PROMO_CODE, params);
}
export function joinContest(data = {}) {
    let params = data
    if (!ls.get('ct')) {
        params = { ...data, ct: "1" }
    }
    let JOIN_FANTASY_CONTEST_GAME = WSManager.Rest(WSC.fantasyURL + WSC.JOIN_FANTASY_CONTEST_GAME, params);
    JOIN_FANTASY_CONTEST_GAME.then(res => {
        if (res.response_code == WSC.successCode) {
            ls.set('ct', '1')
            if (res.data.ct && res.data.ct == "1") {
                Utilities.gtmEventFire('first_join_contest')
            }
        }
        if(res.response_code == WSC.BannedStateCode){
            Utilities.bannedStateToast()
        }
    })
    return JOIN_FANTASY_CONTEST_GAME
}
export function joinContestWithMultiTeam(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.MULTITEAM_JOIN_GAME, params);
}
export function userLogin(data) {
    let params = data ? data : {}
    let api = Utilities.getMasterData().login_flow === '1' ? WSC.EMAIL_LOGIN : WSC.LOGIN;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function logoutUser(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_LOGOUT, params)
}
export function editMobile(data) {
    let params = data ? data : {}
    let api = WSC.EDIT_MOBILE;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function verifyEditedMobile(data) {
    let params = data ? data : {}
    let api = WSC.VERIFY_EDITED_MOBILE;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function editEmail(data) {
    let params = data ? data : {}
    let api = WSC.EDIT_EMAIL;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function verifyEditedEmail(data) {
    let params = data ? data : {}
    let api = WSC.VERIFY_EDITED_EMAIL;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function updateSignupData(data) {
    let params = data ? data : {}
    let api = Utilities.getMasterData().login_flow === '1' ? WSC.UPDATE_EMAIL_SIGNUP_DATA : WSC.UPDATE_SIGNUP_DATA;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function validateLogin(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VALIDATE_LOGIN, params)
}
export function socialLogin(data) {
    let params = data ? data : {}
    let api = Utilities.getMasterData().login_flow === '1' ? WSC.SOCIAL_EMAIL_LOGIN : WSC.SOCIAL_LOGIN;
    return WSManager.Rest(WSC.userURL + api, params)
}
export function updateDeviceToken(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_DEVICE_TOKEN, params)
}
export function updateUserProfile(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_PROFILE, params)
}
export function verifyUserPan(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VERIFY_PAN_INFO, params)
}
export function verifyUserBank(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VERIFY_BANK_ACCOUNT, params)
}
export function updateStateDetail(data) {
    let params = data ? data : {}
    let api = params.ban_state ? WSC.UPDATE_BAN_STATE_DATA : WSC.UPDATE_STATE_DATA
    return WSManager.Rest(WSC.userURL + api, params)
}
export function updatePANCardDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_PAN_INFO, params)
}
export function updateUserBankDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_BANK_ACC_DETAILS, params)
}
export function deleteUserBankDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DELETE_BANK_ACC_DETAILS, params)
}
export function getUserProfile(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_MY_PROFILE, params)
}
export function getAvatarList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_AVATARS_LIST, params)
}
export function setUserAvatar(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_PROFILE_PICTURE, params)
}
export function activateAccount(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.ACTIVATE_ACCOUNT, params)
}
export function getBannedStats(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.GET_BANNED_STATE, params)
}
export function getAppStoreLink(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_DOWNLOAD_APP_LINK, params)
}
export function getResendEmailVerLink(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.RESEND_EMAIL_VERIFICATION_LINK, params)
}
export function validatePhoneOTP(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VALIDATE_OTP, params)
}
export function validateEmailOTP(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VALIDATE_EMAIL_OTP, params)
}
export function resendPhoneOTP(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.RESEND_OTP, params)
}
export function resendEmailOTP(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.RESEND_EMAIL_OTP, params)
}
export function getAllCountries(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "country_list.json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_ALL_COUNTRY, params);
}
export function getAllStates(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "state_list_" + params.master_country_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_ALL_STATE, params);
}
export function changePassword(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CHANGE_PASSWORD, params);
}
export function forgotPassword(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.FORGOT_PASSWORD, params);
}
export function validateForgotPassword(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VALIDATE_FORGOT_PASSWORD, params);
}
export function resetForgotPassword(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.RESET_PASSWORD, params);
}
export function getAppNotificationCount(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_UNREAD_NOTIFICATION, params);
}
export function getNotification(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_NOTIFICAITONS, params);
}
export function getContestShareCode(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_INVITE_CODE, params);
}
export function getShortURL(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_SHORTENED_URL, params);
}
export function saveShortURL(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_SAVE_SHORTEN_URL, params);
}
export function getPublicContestDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_PUBLIC_CONTEST, params);
}
export function checkUsername(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CHECK_USERNAME_AVAIBILITY, params);
}
export function updateUsername(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_USERNAME, params);
}
export function getMyReferralList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_AFFILIATE_MYREFERRAL_LIST, params);
}

export function getUserEarnMoney(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_EARN_MONEY, params);
}
export function getReferralMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_AFFILIATE_MASTER_DATA, params);
}
export function getMasterDataRef(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_MASTER_DATA_REF, params);
}
export function updateRefCode(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_REF_CODE, params);
}
export function playingExperience(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.PLAYING_EXPERIENCE, params);
}
export function getSwitchTeamList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_SWITCH_TEAM_LIST, params);
}
export function switchTeamContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.SWITCH_TEAM_CONTEST, params);
}
export function downloadContestTeam(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_CONTEST_LINEUP_EXPORT, params);
}
export function getLineupWithScore(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_LINPEUP_WITH_SCORE, params);
}
export function getContestLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_CONTEST_LEADERBOARD, params);
}
export function getOwnLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_CONTEST_USER_LEADERBOARD, params);
}
export function getDealsAPI(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_DEALS, params);
}
export function getPromoCodes(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_PROMO_CODES, params);
}
export function getMatchByLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MATCHES_BY_LEAGUE_ID, params);
}
export function createPrivateContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CREATE_USER_CONTEST, params);
}
export function createContestMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CREATE_CONTEST_MASTER_DATA, params);
}
/*=======Wheel======*/

export function getSpinTheWheelData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_SPIN_THE_WHEEL_DATA, params);
}

export function claimSpinTheWheel(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CLAIM_SPIN_THE_WHEEL, params);
}

/*=======Coins======*/
export function getDailyCoins(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_DAILYCOIN, params);
}
export function claimCoins(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CLAIM_COINS, params);
}
export function getEarnCoinsList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.EARN_COIN_LIST, params);
}
export function getRewardList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_REWARD_LIST, params);
}
export function redeemRewards(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.REDEEM_REWARD, params);
}
export function updateUserSettings(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.UPDATE_USER_SETTING, params);
}
export function getFeedbackQA(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_FB_LIST, params);
}
export function saveFeedback(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.SAVE_FEEDBACK, params);
}

/*========Multigame==========*/


export function getLobbyMultiGame(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_fixture_list_multigame_" + params.sports_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_MULTI_GAME, params);
}
export function getFixtureDetailMultiGame(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_FIXTURE_DETAIL_MULTI_GAME, params);
}

export function getContestDetailsMultiGame(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_CONTEST_DETAIL_MULTI_GAME, params);
}

export function getPublicContestDetailMultiGame(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_PUBLIC_CONTEST_MULTI_GAME, params);
}
export function getMyCollectionMultiGame(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MY_COLLECTION_LIST_MULTI_GAME, params, cacheEnable);
}
export function checkContestEligibilityMultiGame(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CHECK_ELIGIBILITY_FOR_CONTEST_MULTI_GAME, params);
}


/*==================*/

/*=========Prediction=========*/

export function getLobbyPrediction(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_fixture_list_prediction_" + params.sports_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_PREDICTION, params);
}

export function getPredictionContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_PREDICTIONS_CONTEST, params)
}

export function makePrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.MAKE_PREDICTIONS, params)
}

export function getPredictionSeason(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.PREDICTIONS_SEASON, params)
}

export function getMyPrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.MY_PREDICTIONS, params)
}

export function getPredictionDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_PREDICTIONS_DETAIL, params)
}

export function getPredictionParticipants(data) {
    let params = data ? data : {};
    let url = params.isLeader ? WSC.GET_PREDICTION_LEADERBORD : WSC.GET_PREDICTION_USERS
    return WSManager.Rest(WSC.baseURL + url, params)
}

export function checkIsPredictionJoin(data) {
    let params = data ? data : {};
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.CHECK_PREDICTION_JOINED, params)
}
export function getLiveMatchGameCenter(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_LIVE_MATCH_LIST, params)
}


/*=========FREE TO PLAY=========*/

export function getLobbyFreeToPlay(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "f2p_fixture_list_" + params.sports_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_FREE_TO_PLAY, params);

}
export function getMiniLeagueUpcomingFixture(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MINI_LEAGUE_UPCOMING_FIXTURE, params, cacheEnable);
}

export function getMyContestFreeToPlay(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MY_CONTEST_LIST_FREE_TO_PLAY, params, cacheEnable);
}
export function getMiniLeagueByStatus(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MINI_LEAGUE_BY_STATUS, params, cacheEnable);
}
export function getMiniLeagueDetails(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MINI_LEAGUE_DETAILS, params, cacheEnable);
}
export function getFixtureMiniLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_FIXTURE_MINI_LEAGUE, params)
}
export function getMiniLeagueLeaderBoard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GTE_MINI_LEAGUE_LEADER_BOARD, params);
}
export function getUserMiniLeagueLeaderBoard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GTE_USER_MINI_LEAGUE_LEADER_BOARD, params);
}
export function getUserMiniLeagueLeaderBoardMatches(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GTE_USER_MINI_LEAGUE_LEADER_BOARD_MATCHES, params);
}
export function getMiniLeagueMyContest(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MINI_LEAGUE_MY_CONTEST_LIST, params, cacheEnable);
}
/*==================*/

/*=========Open Prediction=========*/

export function getLobbyOpenPrediction(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_category_list_open_predictor.json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_LOBBY_OPEN_PREDICTION, params);
}

export function getOpenPredictionContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_OPEN_PREDICTIONS_CONTEST, params)
}

export function makeOpenPrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.MAKE_OPEN_PREDICTIONS, params)
}

export function getMyOpenPredictionCategory(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_MY_PREDICTION_CATEGORY, params)
}

export function getMyOpenPrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.MY_OPEN_PREDICTIONS, params)
}

export function getOpenPredictionParticipants(data) {
    let params = data ? data : {};
    let url = params.isLeader ? WSC.GET_OPEN_PREDICTION_LEADERBORD : WSC.GET_OPEN_PREDICTION_USERS
    return WSManager.Rest(WSC.baseURL + url, params)
}

export function getOpenPredictionDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_OPEN_PREDICTIONS_DETAIL, params)
}
export function checkOpenPredictionISJoin(data) {
    let params = data ? data : {};
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.CHECK_OPEN_PREDICTION_JOINED, params)
}
export function getFixedPredictionCategory(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_FIXED_PREDICTION_CATEGORY, params)
}
export function getFixedPredictionLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_FIXED_PREDICTION_LEADERBOARD, params)
}

/*==================*/
/*=======Pickem======*/
export function getPickemFixtureList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_FIXTURE_LIST, params);
}

export function getPickemLeagueFilter(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_FILTER_BY_LEAGUE, params);
}

export function submitPickemFixture(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.SUBMIT_PICKEM, params);
}

export function getMyPicks(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_MY_PICKEM, params);
}

export function getPickemParticipants(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_MY_PICKEM_PARTICIPANTS, params)
}

export function getPickemLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_LEADERBOARD, params)
}

export function getPickemDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_DETAIL, params)
}

export function GetWeekList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_WEEK_LIST, params);
}

export function GetMonthList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_MONTH_LIST, params);
}

export function GetPickemLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_LEADERBOARD, params);
}

export function GetUserWeeklyLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_USER_WEEKLY_LEADERBOARD, params);
}

export function GetUserMonthlyLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_USER_MONTHLY_LEADERBOARD, params);
}

/*==================*/


/*=========Open Prediction With Fixed Prize Pool =========*/

export function getFPPLobbyOpenPrediction(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "lobby_category_list_fixed_open_predictor.json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_FPP_LOBBY_OPEN_PREDICTION, params);
}

export function getFPPOpenPredictionContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_FPP_OPEN_PREDICTIONS_CONTEST, params)
}

export function makeFPPOpenPrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.MAKE_FPP_OPEN_PREDICTIONS, params)
}

export function getMyFPPOpenPredictionCategory(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_MY_FPP_PREDICTION_CATEGORY, params)
}

export function getMyFPPOpenPrediction(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.MY_FPP_OPEN_PREDICTIONS, params)
}

export function getFPPOpenPredictionParticipants(data) {
    let params = data ? data : {};
    let url = params.isLeader ? WSC.GET_OPEN_PREDICTION_LEADERBORD : WSC.GET_FPP_OPEN_PREDICTION_USERS
    return WSManager.Rest(WSC.baseURL + url, params)
}

export function getFPPOpenPredictionDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(process.env.REACT_APP_NODE_URL + WSC.GET_FPP_OPEN_PREDICTIONS_DETAIL, params)
}

export function getFPPFixedPredictionCategory(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_FPP_FIXED_PREDICTION_CATEGORY, params)
}
export function getFPPFixedPredictionLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_FPP_FIXED_PREDICTION_LEADERBOARD, params)
}

export function getMyContestTeamCount(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_MY_CONTEST_TEAM_COUNT, params)
}
export function getNewContestLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_NEW_CONTEST_LEADERBOARD, params)
}

export function getMultigameUserTeams(data, user_unique_id) {
    let params = data ? data : {}
    // var s3_api_data_url = S3_URL_PREFIX + "user_teams_" + params.collection_master_id + "_" + user_unique_id + ".json";
    // return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_MULTIGAME_USER_LINEUP_LIST, params);
    return WSManager.RestS3ApiCall('', WSC.GET_MULTIGAME_USER_LINEUP_LIST, params);
}
export function getMultigameMyContest(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MULTIGAME_USER_CONTEST_BY_STATUS, params, cacheEnable);
}
export function getMultigameMyContestTeamCount(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_MY_MULTIGAME_CONTEST_TEAM_COUNT, params)
}
export function getMultigameMyLobbyFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MULTIGAME_MY_LOBBY_FIXTURE, params);
}
export function depositRazorPayFund(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_RAZORPAY, params);
}
export function depositStripe(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_STRIPE, params);
}


/*=========Affiliate=========*/

export function becomeAffilateUser(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.BECOME_AFFILATE_USER, params);
}
export function getAffilateUserSummary(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_AFFILIATE_SUMMARY, params);
}
export function getAffilateUserTransaction(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_AFFILIATE_TRANSACTIONS, params);
}

export function getCoinPackageList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_COINS_PACKAGE_LIST, params);
}
export function callBuyCoins(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.BUY_COINS, params);
}
/*=========Contest SCORE CARD =========*/
export function getContestScoreCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_CONTEST_SCORE_CARD, params);

}
export function callUserSelfExcl(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_SELF_EXCLUSION, params);
}
export function setSelfExcl(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.SET_SELF_EXCLUSION, params);
}

export function getCashFreeGatewayList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_CASHFREE_GATWAY_LIST, params);
}

//Refereal
export function getReferalLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(process.env.REACT_APP_BASE_URL + WSC.GET_REFERAL_LEADERBOARD, params)
}
export function getReferalPrizes(data) {
    let params = data ? data : {};
    return WSManager.Rest(process.env.REACT_APP_BASE_URL + WSC.GET_REFERAL_PRIZES, params)
}
//Network Fantasy
export function joinContestNetworkfantasy(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.JOIN_FANTASY_CONTEST_GAME_NETWORK_FANTASY, params);
}
export function getPublicContestNetworkfantasy(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PUBLIC_CONTEST_NF, params);
}

export function getContestDetailsNetworkfantasy(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "contest_detail_" + params.contest_id + AppLANG + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_CONTEST_DETAIL_NETWORK_FANTASY, params);
    //return WSManager.Rest(WSC.baseURL + WSC.GET_CONTEST_DETAIL_NETWORK_FANTASY, params)

}
export function getUserContestJoinCountNetworkfantasy(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_GAME_USER_JOIN_COUNT_NETWORK_FANTASY, params);
}

export function getContestUserListNetworkfantasy(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_CONTEST_USERS_NETWORK_FANTASY, params);
}
export function getContestShareCodeNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_INVITE_CODE_NF, params);
}
export function getLineupWithScoreNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_LINPEUP_WITH_SCORE_NF, params);
}

export function getSwitchTeamListNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_USER_SWITCH_TEAM_LIST_NF, params);
}
export function downloadContestTeamNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_CONTEST_LINEUP_EXPORT_NF, params);
}
export function getContestLeaderboardNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_CONTEST_LEADERBOARD_NF, params);
}
export function getOwnLeaderboardNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_CONTEST_USER_LEADERBOARD_NF, params);
}

export function switchTeamContestNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.SWITCH_TEAM_CONTEST_NETWORK_FANTASY, params);
}
export function getNewContestLeaderboardNF(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.baseURL + WSC.GET_NEW_CONTEST_LEADERBOARD_NETWORK_FANTASY, params)
}
export function getContestScoreCardNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_CONTEST_SCORE_CARD_NETWORK_FANTASY, params);

}
export function joinContestWithMultiTeamNF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.MULTITEAM_JOIN_GAME_NETWORK_FANTASY, params);
}
export function getPlayerBreakdownNF(data) {
    let params = data ? data : {}
    return WSManager.RestS3ApiCall('', WSC.GET_PLAYER_BREAKDOWN_NETWORK_FANTASY, params);
}

//Team Compare

export function getLineupWithTeamCompare(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_LINPEUP_WITH_TEAM_COMPARE, params);
}
// Session tracking 
export function updateTrackActiveSession(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.TRACK_ACTIVE_SESSION, params);
}

//Pickem Tournament
export function getMyLobbyPickemTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_MY_LOBBY_TOURNAMENT, params);
}
export function getPickemTournamentMatch(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_TOURNAMENT_MATCH, params);
}
export function getPickemTournamentDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_TOURNAMENT_DETAIL, params);
}
export function getPickemTournamentRules(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_TOURNAMENT_RULES, params);
}
export function getPickemJoinTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICKEM_JOIN_TOURNAMENT, params);
}
export function submitTourPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.SUBMIT_TOUR_PICKEM, params);
}
export function editTournamentPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.EDIT_TOURNAMENT_PICK, params);
}
export function getPickemTournamentLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICK_TOURNAMENT_LEADERBOARD, params);
}
export function getMyPickemTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_MY_PICK_TOURNAMENT, params);
}
export function getPickTourParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_PICK_TOUR_PARTICIPANTS, params);
}
export function getUserPickTourHistory(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_USER_PICK_TOUR_HISTORY, params);
}
// DFS Tournament
export function getDFSTournamentList(data) {
    let params = data ? data : {}
    // var s3_api_data_url = S3_URL_PREFIX + "lobby_fixture_list_" + params.sports_id + ".json";
    // return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_DFS_TOURNAMENT_LIST, params);
    return WSManager.Rest(WSC.baseURL + WSC.GET_DFS_TOURNAMENT_LIST, params);
}
export function getDFSTourDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_DFS_TOURNAMENT_DETAIL, params);
}
export function getDFSTourMatch(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_DFS_TOURNAMENT_MATCH, params);
}
export function getDFSTourLineupMasterData(data) {
    let params = data ? data : {}
    // var s3_api_data_url = S3_URL_PREFIX + "lineup_master_data_" + params.tournament_season_id + ".json";
    return WSManager.RestS3ApiCall('', WSC.GET_DFS_TOUR_LINEUP_MASTER_DATA, params);
    // return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_DFS_TOUR_LINEUP_MASTER_DATA, params);
    // return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_LINEUP_MASTER_DATA, params);
}
export function getDFSTourAllRoster(data) {
    let params = data ? data : {}
    var s3_api_data_url = S3_URL_PREFIX + "roster_list_" + params.tournament_season_id + ".json";
    return WSManager.RestS3ApiCall(s3_api_data_url, WSC.GET_DFS_TOUR_ALL_ROSTER, params);
    // return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_ALL_ROSTER, params);
}
export function getDFSTourNewTeamName(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_USER_LINEUP_TEAM_NAME, params);
}
export function processDFSTourLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.DFS_TOUR_LINEUP_PROCCESS, params);
}
export function getUserDFSTourLineUps(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_USER_LINEUP_LIST, params);
}
export function getDFSTourUserTeams(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_USER_LINEUP_LIST, params);
}
export function joinDFSTour(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_JOIN_TOURNAMENT, params);
}
export function joinDFSTourSeason(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_JOIN_TOURNAMENT_SEASON, params);
}
export function getDFSTourUserLineUpDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_USER_LINEUP, params);
}
export function getDFSTourFixtureLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_FIXTURE_LEADERBOARD, params);
}
export function getDFSTournamentLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOURNAMENT_LEADERBOARD, params);
}
export function getMyLobbyDFSTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_MY_LOBBY_TOURNAMENT, params);
}
export function getMyDFSTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_MY_TOURNAMENT, params);
}
export function getDFSTourRules(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_DFS_TOUR_RULES, params);
}
export function getDFSTourUserHistory(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.GET_USER_DFS_TOUR_HISTORY, params);
}
export function getDFSTourLineupWithScore(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_LINPEUP_WITH_SCORE, params);
}
export function getDFSTourShareCode(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_INVITE_CODE, params);
}
export function getPublicDFSTourDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_PUBLIC_DFS_TOURNAMENT, params);
}
export function getUserJoinedDFSTourId(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_JOINED_DFS_TOURNAMENT_IDS, params);
}
export function getRandomScratchCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_SCRATCH_CARD, params);
}
export function claimScratchCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CLAIM_SCRATCH_CARD, params);

}

export function getFixtureScoreCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_FIXTURE_SCORECARD, params);
}
export function getFixtureStats(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_FIXTURE_STATS, params);
}

export function getXPRewardList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_XP_REWARD_LIST, params);
}
export function getXPActivityList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_XP_ACTIVITY_LIST, params);
}
export function getUserXPCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_XP_CARD, params);
}
export function getUserXPHistory(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_XP_HISTORY, params);
}
export function getMyPublicProfile(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_MY_PUBLIC_PROFILE, params);
}

export function geFantasyRefLBMasterData(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_FR_LEADERBOARD_MASTER, params)
}
export function geFantasyRefLBList(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_FR_LEADERBOARD_LIST, params)
}
export function geFantasyRefLBHistory(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_FR_LEADERBOARD_DETAIL, params)
}
export function getLStockFixture(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.stockURL + WSC.GET_L_STOCK_FIXTURE, params)
}
export function getMyLStockFixture(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.stockURL + WSC.GET_MY_L_STOCK_FIXTURE, params)
}
export function getStockContestList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_CONTEST_LIST, params)
}
export function getStockContestByStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_CONTEST_BY_STATUS, params);
}
export function getStockJoinedFixtureByStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_JOINED_FIXTURE_BY_STATUS, params);
}
export function getStockUserLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_USER_LINEUP, params);
}
export function getStockUserAllTeams(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_USER_LINEUP_LIST, params);
}
export function getStockContestTeamCount(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_CONTEST_TEAM_COUNT, params);
}
export function getStockFixtureDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_GET_FIXTURE_DETAILS, params);
}
export function getStockFilterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_FILTER_MASTER_DATA, params);
}
export function getStockLineupMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_LINEUP_MASTER_DATA, params);
}
export function getStockLineupTeamName(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_USER_MATCH_TEAM_DATA, params);
}
export function getStockRoster(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_ALL_STOCKS, params);
}
export function stockLineupProcess(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_LINEUP_PROCCESS, params);
}
export function getStockPlayerCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_PLAYER_CARD, params);
}
export function addRemoveStockWishlist(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_ADD_REMOVE_WISHLIST, params);
}
export function stockJoinContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_JOIN_GAME, params);
}
export function stockContestDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_CONTEST_DETAIL, params);
}
export function getSFUserContestJoinCount(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SF_GAME_USER_JOIN_COUNT, params);
}
export function getStockContestUserList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_CONTEST_USERS, params);
}
export function getStockWishlist(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_WISHLIST, params);
}
export function getStockInviteCode(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_INVITE_CODE, params);
}
export function StockCheckContestEligibility(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_CHECK_ELIGIBILITY_FOR_CONTEST, params);
}
export function getStockPublicContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_PUBLIC_CONTEST, params);
}
export function getStockCardDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_STOCKCARD_DETAILS, params);
}
export function getStockStatictics(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_STATICS, params);
}
export function stockSwitchTeam(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_SWITCH_TEAM_CONTEST, params);
}
export function stockSwitchTeamList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_SWITCH_TEAM_LIST, params);
}
export function joinStockContestWithMultiTeam(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_MULTITEAM_JOIN_GAME, params);
}
export function getStockCreateContestMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_CREATE_CONTEST_MASTER_DATA, params);
}
export function stockCreateContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_CREATE_PRIVATE_CONTEST, params);
}
export function stockDownloadTeams(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_DOWNLOAD_CONTEST_TEAMS, params);
}
export function stockLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_LEADERBOARD, params);
}
export function stockCompareTeams(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_COMPARE_TEAMS, params);
}
export function stockLineupWithScore(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_GET_LINPEUP_WITH_SCORE, params);
}
export function validateStockContestPromo(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.VALIDATE_STOCK_PROMO_CODE, params);
}
export function getStockLobbyBanner(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_LOBBY_BANNER_LIST, params);
}
export function getStockScoreCalculation(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_LINEUP_SCORE_CALCULATION, params);
}
export function getStockLobbySetting(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_LOBBY_SETTING, params);
}
export function getStockContestLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_CONTEST_LEADERBOARD, params);
}
export function stockLineupProcessEquity(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_LINEUP_PROCCESS_EQUITY, params);
}
export function stockCompareTeamsEquity(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.STOCK_TEAM_COMPARE_EQUITY, params);
}
export function getStockUserLineupEquity(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_STOCK_USER_LINEUP_EQUITY, params);
}
export function getStockScoreCalculationEquity(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SCORE_CALCULATION_EQUITY, params);
}
export function getStockContestStaticsEquity(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_CONTEST_STK_STATICS, params);
}

export function getStockLBHistory(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_STOCK_LEADERBOARD_DETAIL, params)
}
export function getStockPlayingExperience(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_STOCK_PLAYING_EXPERIENCE, params)
}

//Booster
export function getCollectionBooster(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_COLLECTION_BOOSTER, params)
}
export function applyBooster(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.APPLY_BOOSTER, params)
}
export function getBoosterList(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_BOOSTER_LIST, params)
}

//bench
export function saveBenchPlayer(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.SAVE_BENCH_PLAYER, params)
}


//Guru Module

export function genrateLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GENRATE_GURU_LINEUP, params);

}

//Quiz Module

export function getQuizQuestion(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_QUIZ_QUESTION, params);
}
export function getQuizCheckAns(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_QUIZ_CHECK_ANS, params);
}
export function applyQuizClaim(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.APPLY_QUIZ_CLAIM, params);
}


//Download App Coin Claim 
export function claimDownAppCoin(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.CLAIM_DOWNLOAD_APP_COIN, params);
}

// Download Shorten URL
export function getSourceUrl(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_SOURCE_URL, params);
}

// Stock Prediction Module
export function getSPLbyFilter(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_LOBBY_FILTER, params);
}
export function getSPLbyContestLst(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_LBY_CONTEST_LT, params);
}
export function getSPLineupMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_LINEUP_MATSER_DATA, params);
}
export function getSPAllStock(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_ALL_STOCKS, params);
}
export function getSPLineupProcess(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_LINEUP_PROCESS, params);
}
export function getSPUserContestByStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_USER_CONTEST_BY_STATUS, params);
}
export function getSPCollectionStatics(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_COLLECTION_STATICS, params);
}
export function getSPUserLineupList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_USER_LINEUP_LIST, params);
}
export function getSPMyLobbyContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_MY_LOBBY_CONTEST, params);
}
export function getSPUserLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_USER_LINEUP, params);
}
export function getSPCompareTeams(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_TEAM_COMPARE, params);
}
export function getSPScoreCalculation(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_SP_SCORE_CALCULATION, params);
}

///H2H Challange
export function getH2HContestList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_H2H_CHALLANGE_CONTEST_LIST, params);
}
export function getH2HBannerList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.GET_H2H_CHALLANGE_BANNER_LIST, params);
}
export function joinContestH2H(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.JOIN_FANTASY_CONTEST_GAME_H2H, params);
}
export function getH2HJoinedContestList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_H2H_CONTEST, params);
}

//Live Fantasy
export function getLobbyFixturesLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_LOBBY_FIXTURE, params);
}
export function getFixtureContestListLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_FIXTURE_CONTEST_LIST, params)
}
export function getContestDetailsLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_CONTEST_DETAIL, params);
}
export function getContestUserListLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_CONTEST_USERS, params);
}
export function getPublicContestDetailLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_PUBLIC_CONTEST, params);
}
export function checkContestEligibilityLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_CHECK_ELIGIBILITY_FOR_CONTEST, params);
}
export function joinContestLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_JOIN_FANTASY_CONTEST_GAME, params);
}
export function getMyLobbyFixturesLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_MY_LOBBY_FIXTURE, params);
}
export function getMyContestLF(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_MY_CONTEST_LIST, params, cacheEnable);
}
export function getContestShareCodeLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_INVITE_CODE, params);
}
export function verifyCryptoDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.VERIFY_CRYPTO_DETAILS, params);
}
export function getMyCollectionLF(data, cacheEnable) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_MY_COLLECTION_LIST, params, cacheEnable);
}
export function getFixtureDetailLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_GET_FIXTURE_DETAIL, params);
}
export function getNextOverDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_NEXT_OVER_DETAILS, params);
}
export function getOverDetailsLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_GET_OVER_DETAIL, params);
}
export function saveUserPridcitionLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_PREDICT_ANSWER, params);
}
export function getOddsOverLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_GET_OVER_ODDS_BALL, params);
}
export function getMatchPlayersLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.baseURL + WSC.LF_GET_MATCH_PLAYERS, params);
}
export function getContestLeaderboardLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_CONTEST_LEADERBOARD, params);
}
export function getUserMatchStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_USER_MATCH_STATUS, params);
}
export function validateContestPromoLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_VALIDATE_CONTEST_PROMO_CODE, params);
}
export function getUserTeamStats(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_USER_TEAM_STATS, params);
}
export function getUserLiveOversLf(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.LF_GET_USER_LIVE_OVERS, params);
}

export function createContestMasterDataLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CREATE_CONTEST_MASTER_DATA_LF, params);
}
export function createPrivateContestLF(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.CREATE_USER_CONTEST_LF, params);
}
export function btcPay(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_BTCPAY, params);
}
export function mpesaDeposit(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.userURL + WSC.DEPOSIT_BY_MPESA, params);
}

// Stock Live Fantasy
export function getLSFLobbyFilter(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_LOBBY_FILTER, params);
}
export function getLSFLbyContestLst(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_LBY_CONTEST_LT, params);
}
export function LSFJoinContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.LSF_JOIN_GAME, params);
}
export function getLSFMyLobbyContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_MY_LOBBY_CONTEST, params);
}
export function getLSFLineupMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_LINEUP_MATSER_DATA, params);
}
export function getLSFAllStock(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_ALL_STOCKS, params);
}
export function getLSFUserLineup(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_USER_LINEUP, params);
}
export function getLSFCollectionStatics(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_COLLECTION_STATICS, params);
}
export function LSF_USER_TRADE(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.LSF_USER_TRADE, params);
}
export function getLSFHOLIDAYLIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_HOLIDAY_LIST, params);
}
export function getLSFUserTransaction(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_USER_TRANSACTION, params);
}
export function getLSFUserContestByStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.stockURL + WSC.GET_LSF_USER_CONTEST_BY_STATUS, params);
}

// New Affiliate Visit
export function addVisit(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.ADD_VISIT, params);
}
export function upiIntentCallback(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.UPI_INTENT_CALLBACK, params);
}
export function payuCallback(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PAYUMONEY_MARK_SUCCESS, params);
}


// New Affiliate Visit
export function GetPickFantasySports(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_SPORTS_LIST, params);
}
export function GetPFFixtureLobby(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_LOBBY_FIXTURE, params);
}
export function GetPFFixtureContest(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_FIXTURE_CONTEST, params);
}
export function GetPFUserTeams(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_USER_LINEUP, params);
}
export function GetPFUserContestByStatus(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_USER_CONTEST_BY_STATUS, params);
}
export function GetPFMyContestTeamCount(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_MY_CONTEST_TEAM_COUNT, params);
}
export function GetPFMyLobbyFixtures(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_MY_LOBBY_FIXTURES, params);
}
export function GetPFFixtureDetails(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_FIXTURE_DETAILS, params);
}
export function GetPFContestDetail(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_CONTEST_DETAILS, params);
}
export function GetPFLineupMasterData(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_LINEUP_MASTER_DATA, params);
}
export function GetPFAllRoster(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_ALL_ROSTER, params);
}
export function GetPFLineupProcess(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_LINEUP_PROCESS, params);
}
export function GetPFJoinGame(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_JOIN_GAME, params);
}
export function GetPFTeamName(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_TEAM_NAME, params);
}

export function GetPFUserJoinedFixture(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_USER_JOINED_FIXTURE_BY_STATUS, params);
}
export function GetPFContestLeaderboard(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_CONTEST_LEADERBOARD, params);
}
export function GetPFContestUsers(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_CONTEST_USERS, params);
}
export function GetPFUserLineupData(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PF_GET_USER_LINEUP_DATA, params);
}
export function getPFSwitchTeamList(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.PF_GET_USER_SWITCH_TEAM_LIST, params);
}
export function PFSwitchTeamContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.PF_SWITCH_TEAM_CONTEST, params);
}



//aadhar verification

export function getAadharDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.DO_UPLOAD_AADHAR, params)
}
export function saveAadharDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.SAVE_AADHAR_DETAIL, params)
}
export function getUserAadharDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_USER_AADHAR_DETAIL, params)
}

export function GetAadharOtp(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GENERATE_AADHAR_OTP, params)
}

export function VerfiyAadharOtp(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.VERIFY_AADHAR_OTP, params)
}


export function getGSTDownload(data) {
    let params = data ? data : {};
    return WSManager.RestGet(WSC.userURL + WSC.GET_GST_REPORT, params)
}
export function getWhatIsNew(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.WHAT_IS_NEW_DETAIL, params)
}

// New DFS tournament
export function getDFSTLobbyTournament(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFST_LOBBY_TOURNAMENT, params)
}
export function getDFSTTournamentList(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFST_TOURNAMENT_LIST, params)
}
export function getDFSTTournamentDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFST_TOURNAMENT_DETAIL, params)
}
export function getDFSTTournamentLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFST_TOURNAMENT_LEADERBOARD, params)
}
export function getDFSTTournamentUserDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFST_TOURNAMENT_USER_DETAIL, params)
}

//Pickem Tournament
export function getPTTourList(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_TOURNAMENT_LIST, params)
}
export function getPTJoinTour(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_JOIN_TOURNAMENT, params)
}
export function getPTMyJoinedTour(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_MY_LOBBY_TOURNAMENT, params)
}
export function getPTTourDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_GET_TOURNAMENT_DETAIL, params)
}
export function callSubmitPickem(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_SUBMIT_PICKEM, params)
}
export function getPTLeaderboard(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_LEADERBOARD, params)
}
export function getPTMyContest(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_MY_CONTEST_TOURNAMENT, params)
}
export function submitPTTieBreaker(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.PT_SUBMIT_TIE_BREAKER, params)
}
export function getDFSTourLead(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFS_TOUR_LEAD, params)
}
export function getPickemTourLead(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_PICKEM_DFS_TOUR_LEAD, params)
}
export function sendOTP(data) {
    let params = data ? data : {}
    // params['apiversion'] = 'v2';
    return WSManager.Rest(WSC.userURL + WSC.SEND_OTP, params);
}
//TDS
export function getTdsDocument(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_TDS_DOCUMENT, params)
}

export function getTDSBreakup(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_TDS_DETAIL, params)
}
export function getDFSSportsShort(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_DFS_SPORTS_SHORT, params)
}

export function getPickemSportsShort(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.GET_PICKEM_SPORTS_SHORT, params)
}

export function getMatchScorecardStats(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_MATCH_SCORECARD_STATS, params);
}
export function getUserMatchContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_MATCH_CONTEST, params);
}

//MPG
export function typeList(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL + WSC.TYPE_LIST, params)
}
export function transactionUpdate(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL +WSC.TRANSACTION_UPDATE, params)
}
export function ImageUploadMPG(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.userURL +WSC.DO_UPLOAD_PROOF, params)
}
export function getTeamDetail(params = {}) {
    return WSManager.Rest(WSC.fantasyURL +WSC.GET_TEAM_DETAIL, params)
}
// Optimization: May/June 2023
export function createContestMasterDataDFS(params = {}) {
    return WSManager.Rest(WSC.fantasyURL + WSC.DFS_CREATE_CONTEST_MASTER_DATA, params);
}
export function createPrivateContestDFS(params = {}) {
    return WSManager.Rest(WSC.fantasyURL + WSC.DFS_CREATE_USER_CONTEST, params);
}

// Tournament changes new apis
export function getUserFixtureTeams(params = {}) {
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_USER_FIXTURE_TEAMS, params);
}


//featured league
export function getPickemTourList(params = {}) {
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_PICKEM_TOUR_LIST, params);
}

export function getDfsTourList(params = {}) {
    return WSManager.Rest(WSC.fantasyURL + WSC.GET_DFS_TOUR_LIST, params);
}
//props
export function propsSaveTeam(params = {}) {
    return  WSManager.Rest(WSC.propsURL + WSC.PROPS_SAVE_TEAM, params);
}

// Juspay
export function jusPayCallback(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.JUSPAY_CALLBACK, params);
}
// OT
export function otGetSportsList(params = {}) {
    return  WSManager.Rest(WSC.oTradeURL + WSC.OT_GET_SPORTS_LIST, params);
}

export function phonePeCallback(params = {}) {
    return WSManager.Rest(WSC.userURL + WSC.PHONEPE_CALLBACK, params);
}

export function participantsDetail(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.fantasyURL + WSC.PARTICIPANTS_DETAIL, params)
}

export function pickemTeamHistory(data) {
    let params = data ? data : {};
    return WSManager.Rest(WSC.fantasyURL + WSC.PT_TEAM_HISTORY, params)
}

