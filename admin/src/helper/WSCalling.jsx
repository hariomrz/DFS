import WSManager from './WSManager';
import * as NC from './NetworkingConstants';

/*Pickem */
export function getAllSport(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ALL_SPORTS, params);
}

/*Pickem */
export function createLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CREATE_LEAGUE, params);
}
export function editLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.EDIT_LEAGUE, params);
}

export function getLeagues(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_LEAGUES, params);
}

export function getNewLeagues(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_LEAGUES_NEW, params);
}

export function publishMatchPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PUBLISH_MATCH_PICKEM, params);
}

export function getPickemParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_PICKEM_PARTICIPANTS, params);
}

export function getCoinConfigApi(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_COIN_CONFIG, params);
}

export function saveCoinConfig(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_COIN_CONFIG, params);
}

export function createPlayer(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CREATE_TEAM_STATS, params);
}

export function editPlayer(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.EDIT_TEAM, params);
}

export function getPlayers(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_TEAMS, params);
}

export function savePlayerImage(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.DO_UPLOAD_FLAG, params);
}

export function createPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CREATE_PICKEM, params);
}

export function getPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ALL_PICKEM, params);
}

export function getUnpubMatches(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_UNPUBLISHED_MATCHES, params);
}

export function pickemResult(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_PICKEM_RESULT, params);
}

export function deletePickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_PICKEM, params);
}

export function getTrendingPickems(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_TRENDING_PICKEMS, params);
}

export function getPickemCounts(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_PICKEM_COUNTS, params);
}

export function getCoinsVsUsersGraph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PEM_GET_COINS_VS_USERS_GRAPH, params);
}

export function getTopTeamGraph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PEM_GET_TOP_TEAM_GRAPH, params);
}

export function getUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_USERS, params);
}

export function createUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CREATE_USER, params);
}

export function getContestDetail_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_CONTEST_DETAIL, params);
}

export function getContestJoined_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_CONTEST_JOINED_SYSTEM_USERS, params);
}

export function getSystemUsersForContest_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_SYSTEM_USERS_FOR_CONTEST, params);
}

export function joinSystemUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.JOIN_SYSTEM_USERS, params);
}

export function deleteUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_USER, params);
}

export function updateUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_USER, params);
}

export function do_upload_SU(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.SU_DO_UPLOAD, params);
}

export function getSeasonDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_SEASON_DETAILS, params);
}


//Admin roles
export function getRoles(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ADMIN_ROLES, params);
}

export function addRoles(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ADD_ROLES, params);
}

export function rolesList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROLES_LIST, params);
}

export function getRolesDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ROLES_DETAIL, params);
}

export function deleteRoles(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_ROLES, params);
}

export function updateRoles(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_ROLES, params);
}

export function remove_image_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SU_REMOVE_PROFILE_IMAGE, params);
}
// Start New Communication Dashboard
export function getTempList_CD(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_SEGEMENTATION_TEMPLATE_LIST, params);
}
// End New Communication Dashboard

export function get_all_avatars(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ALL_AVATARS, params);
}

export function change_avatar_status(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CHANGE_AVATAR_STATUS, params);
}

export function avatar_do_upload(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.AVATAR_DO_UPLOAD, params);
}

export function submit_avatars(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SUBMIT_AVATARS, params);
}








//Start code for Add contest category

export function createGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CREATE_GROUP, params);
}

export function updateGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_GROUP, params);
}

export function getGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_GROUP, params);
}

export function getLeagueListss(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PF_GET_LEAGUE_LIST, params);
}
export function getSportList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_SPORT_LIST, params);
}
export function createLeagues(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_LEAGUE, params);
}
export function createSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_SPORTS, params);
}
export function getTeamPlayer(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_TEAM_PLAYER, params);
}
export function changeStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_SPORTS_STATUS, params);
}

export function getSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PF_GET_SPORTS, params);
}
export function addTeamPlayer(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_LEAGUE_TEAM, params);
}
export function getTeamByLeagueID(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_TEAM_BY_LEAGUE_ID_LIST, params);
}

export function uploadTeamPlayerLogo(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.UPLOAD_TEAM_GROUP_LOGO, params);
}
export function createTeamPlayerAPI(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_TEAM_PLAYER, params);
}

export function uploadGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.UPLOAD_GROUP_ICON, params);
}

export function removeGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.REMOVE_GROUP_ICON, params);
}
export function deleteTeam(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_TEAM_PLAYER, params);
}
export function deleteLeaguePlayer(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_LEAGUE_PLAYER, params);
}
export function deleteLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_LEAGUE, params);
}
export function updateSport(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_SPORTS, params);
}
export function deleteSport(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_SPORTS, params);
}
export function deleteGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_GROUP, params);
}
//End code for Add contest category

export function getAllNetworkContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ALL_NETWORK_CONTEST, params);
}

export function publishNetworkContest(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PUBLISH_NETWORK_CONTEST, params);
}

export function getNetworkContestDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_NETWORK_CONTEST_DETAILS, params);
}

export function getGameLineupDetail(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_GAME_LINEUP_DETAIL, params);
}

export function getNetworkContestParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_NETWORK_CONTEST_PARTICIPANTS, params);
}

//Pickem Tournament
export function saveSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_SAVE_SPORTS, params);
}

export function updateSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_UPDATE_SPORTS, params);
}

export function deletePTSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_UPDATE_SPORTS, params);
}

export function enableSports(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_ENABLE_SPORTS, params);
}

export function PT_CreateTournment(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_CREATE_TOURNAMENT, params);
}

export function PT_getUpcomingFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_UPCOMING_FIXTURES, params);
}

export function PT_getTournamentMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_TOURNAMENT_MASTER_DATA, params);
}

export function PT_getAllTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_ALL_TOURNAMENT, params);
}

export function PT_getTournamentFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_TOURNAMENT_FIXTURES, params);
}

export function PT_getTournamentEditData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_TOURNAMENT_EDIT_DATA, params);
}

export function PT_addMatchesToTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_ADD_MATCHES_TO_TOURNAMENT, params);
}

export function PT_deletePickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_DELETE_PICKEM, params);
}

export function PT_updateTournamentSeasonResult(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_UPDATE_TOURNAMENT_SEASON_RESULT, params);
}

export function PT_removeTournamentLogo(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_REMOVE_TOURNAMENT_LOGO, params);
}

export function PT_updateTournamentResult(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_UPDATE_TOURNAMENT_RESULT, params);
}

export function PT_removeTournamentBanner(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_REMOVE_TOURNAMENT_BANNER, params);
}
//start ERP finance
export function getErpMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_GET_MASTER_DATA, params);
}

export function getErpDashboardData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_GET_DASHBOARD_DATA, params);
}

export function getErpTransactionList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_GET_TRANSACTION_LIST, params);
}

export function saveErpTransaction(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_SAVE_TRANSACTION, params);
}

export function updateErpTransaction(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_UPDATE_TRANSACTION, params);
}

export function deleteErpTransaction(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_DELETE_TRANSACTION, params);
}

export function getErpCategoryList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_GET_CATEGORY_LIST, params);
}

export function ErpSaveCategory(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_SAVE_CATEGORY, params);
}

export function ErpUpdateCategory(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ERP_UPDATE_CATEGORY, params);
}


export function PT_getTournamentParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_TOURNAMENT_PARTICIPANTS, params);
}

export function PT_getTournamentLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_GET_TOURNAMENT_LEADERBOARD, params);
}

export function PT_cancelTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_CANCEL_TOURNAMENT, params);
}

export function PT_deleteTournamentPickem(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PT_DELETE_TOURNAMENT_PICKEM, params);
}








//Netwok game system user
export function getNGContestDetail_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.NG_GET_CONTEST_DETAIL, params);
}

export function getNGContestJoined_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.NG_GET_CONTEST_JOINED_SYSTEM_USERS, params);
}

export function getNGSystemUsersForContest_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.NG_GET_SYSTEM_USERS_FOR_CONTEST, params);
}

export function joinNGSystemUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.NG_JOIN_SYSTEM_USERS, params);
}
//Leaderboards
export function getReferralRank(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_REFERRAL_RANK, params);
}

export function getAppUsageData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_APP_USAGE_DATA, params);
}

export function getAllSeasonWeek(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_ALL_SEASON_WEEKS, params);
}

export function getWeekSeason(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_WEEK_SEASONS, params);
}

//Dfs Tournament
export function DFST_getTournamentMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFSTR_MASTER_DATA, params);
}

export function DFST_removeTournamentLogo(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_REMOVE_TOURNAMENT_LOGO, params);
}

export function DFST_removeTournamentBanner(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_REMOVE_TOURNAMENT_BANNER, params);
}

export function DFST_getAllTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_TOURNAMENT_LIST, params);
}

export function DFST_CreateTournment(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFSTR_SAVE_TOURNAMENT, params);
}

export function DFST_getUpcomingFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_UPCOMING_FIXTURES, params);
}

export function DFST_getTournamentFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_FIXTURES, params);
}

export function DFST_getTourFixtures(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_FIXTURE_TOURNAMENT_LIST, params);
}

export function DFST_getTournamentParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_PARTICIPANTS, params);
}

export function DFST_getTournamentSeasonParticipants(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_SEASON_PARTICIPANTS, params);
}

export function DFST_getTournamentEditData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_EDIT_DATA, params);
}

export function DFST_updateTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_UPDATE_TOURNAMENT, params);
}

export function DFST_getTournamentLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_LEADERBOARD, params);
}

export function DFST_getSeasonLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOURNAMENT_SEASON_LEADERBOARD, params);
}

export function DFST_cancelTournament(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_CANCEL_TOURNAMENT, params);
}

export function DFST_deleteTournamentMatch(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_DELETE_TOURNAMENT_FIXTURE, params);
}

// export function getSmsTemplate(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.GET_SMS_TEMPLATE, params);
// }

// export function updateSmsTemplate(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.UPDATE_SMS_TEMPLATE, params);
//     }

export function updateNewMasterScoringPoints(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_NEW_MASTER_SCORING_POINTS, params);
}

export function changeScrWinStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.CHANGE_SCRATCH_WIN_STATUS, params);
}

export function getScratchCardList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_SCRATCH_CARD_LIST, params);
}

export function deleteScratchCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_SCRATCH_CARD, params);
}

//XP Module
export function xpGetLevelList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_GET_LEVEL_LIST, params);
}

export function xpGetBadgeList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_GET_BADGE_MASTER_LIST, params);
}

export function xpAddRewards(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_ADD_REWARD, params);
}

export function updateSIDate(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_2ND_INNING_DATE, params);
}

export function xpDeleteLevel(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_DELETE_LEVEL, params);
}
export function LB_geTMasterData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_GET_MASTER_DATA, params);
}

export function LB_geSportLeague(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_GET_SPORT_LEAGUES, params);
}

export function LB_getLeaderboardList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_GET_LEADERBOARD_LIST, params);
}

export function LB_toggleLeaderboardById(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_TOGGLE_LEADERBOARD_BY_ID, params);
}
export function LB_leaderboardByDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_TOGGLE_LEADERBOARD_DETAILS, params);
}
export function LB_leaderboardUserList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_TOGGLE_LEADERBOARD_USER_LIST, params);
}
export function LB_getLiveUpcomingLeagues(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_GET_LIVE_UPCOMING_LEAGUES, params);
}
export function LB_getPrizeDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LB_GET_PRIZE_DETAIL, params);
}

//Booster module
export function getBoosterList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_BOOSTER_LIST, params);
}

export function getPositionList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_POSITION_LIST, params);
}

export function saveBooster(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_BOOSTER, params);
}
export function getFixtureApplyBooster(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_FIXTURE_APPLY_BOOSTER, params);
}

export function saveFixtureBooster(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_FIXTURE_BOOSTER, params);
}

export function xpUpdateLevel(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_UPDATE_LEVELS, params);
}

export function xpDeleteReward(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_DELETE_REWARD, params);
}

export function xpUpdateReward(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_UPDATE_REWARD, params);
}

export function xpGetActivitiesList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_GET_ACTIVITIES_LIST, params);
}

export function xpDelActivity(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_DELETE_ACTIVITY, params);
}

export function xpGetActivityMastList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_GET_ACTIVITIES_MASTER_LIST, params);
}

export function xpAddActivity(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_ADD_ACTIVITY, params);
}

export function xpUpdateActivity(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_UPDATE_ACTIVITY, params);
}

export function xpLevelLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_LEVEL_LEADERBOARD, params);
}

export function xpActivitiesLeaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_ACTIVITIES_LEADERBOARD, params);
}

export function xpGetUserHistory(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.XP_GET_USER_HISTORY, params);
}

export function addScratchCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ADD_SCRATCH_CARD, params);
}

export function updateScratchCard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_SCRATCH_CARD, params);
}

export function joinMultiSystemUsers_SU(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.JOIN_MULTIPLE_SYSTEM_USERS, params);
}

/*Start code for Add contest category for STOCK FANTAY */

export function SF_createGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_CREATE_GROUP, params);
}

export function SF_updateGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_UPDATE_GROUP, params);
}

export function SF_getGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_GET_GROUP, params);
}

export function SF_uploadGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.SF_UPLOAD_GROUP_ICON, params);
}

export function SF_removeGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_REMOVE_GROUP_ICON, params);
}

export function SF_deleteGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_DELETE_GROUP, params);
}
export function SF_uploadStockLogo(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.SF_UPLOAD_STOCK_LOGO, params);
}

export function SF_list(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_LIST, params);
}

export function SF_lot(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_GET_LOT_SIZE_LIST, params);
}

export function SF_delete(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_DELETE, params);
}

export function SF_save(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_SAVE, params);
}

export function SF_autoSuggetionList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_AUTO_SUGGESTION_LIST, params);
}

export function SF_update(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_UPDATE, params);
}

export function SF_getStockVerify(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_GET_STOCKS_TO_PUBLISH, params);
}

export function SF_publishFixture(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_PUBLISH_FIXTURE, params);
}

export function SF_updateFixture(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_UPDATE_FIXTURE_STOCKS, params);
}

export function SF_FixtureStats(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_GET_FIXTURE_STATS, params);
}

export function SF_stockListWithClosePrice(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_STOCK_LIST_WITH_CLOSE_PRICE, params);
}

export function SF_updatePriceStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_UPDATE_PRICE_STATUS, params);
}

export function SF_updateClosePrice(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_UPDATE_CLOSE_PRICE, params);
}

export function SF_getHoliday(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SF_GET_HOLIDAY, params);
}

/*End code for Add contest category for STOCK FANTAY */
export function ROOK_getDashboardData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROOK_GET_DASHBOARD_DATA, params);
}

export function ROOK_getRookieUserList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROOK_GET_ROOKIE_USER_LIST, params);
}

export function ROOK_updateRookieSetting(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROOK_UPDATE_ROOKIE_SETTING, params);
}

export function ROOK_checkRookieUserCount(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROOK_CHECK_ROOKIE_USER_COUNT, params);
}

export function ROOK_getRookieSetting(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ROOK_GET_ROOKIE_SETTING, params);
}
export function PC_updateEndDate(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PC_UPDATE_END_DATE, params);
}

export function PC_getPromoCodeAnalytics(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PC_GET_PROMO_CODE_ANALYTICS, params);
}

export function PC_getUserPromoCodeData(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PC_GET_USER_PROMO_CODE_DATA, params);
}

export function getcountryList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_COUNTRY_LIST, params);
}

/*EQUITY STOCK FANTAY Start code for Add contest category for */

export function ESF_createGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_CREATE_GROUP, params);
}

export function ESF_updateGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_UPDATE_GROUP, params);
}

export function ESF_getGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_GET_GROUP, params);
}

export function ESF_uploadGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.ESF_UPLOAD_GROUP_ICON, params);
}

export function ESF_removeGroupIcon(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_REMOVE_GROUP_ICON, params);
}

export function ESF_deleteGroup(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_DELETE_GROUP, params);
}
export function ESF_uploadStockLogo(data) {
    let params = data ? data : {}
    return WSManager.multipartPost(NC.baseURL + NC.ESF_UPLOAD_STOCK_LOGO, params);
}

export function ESF_list(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_LIST, params);
}

export function ESF_lot(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_GET_LOT_SIZE_LIST, params);
}

export function ESF_delete(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_DELETE, params);
}

export function ESF_save(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_SAVE, params);
}

export function ESF_autoSuggetionList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_AUTO_SUGGESTION_LIST, params);
}

export function ESF_getIndustryList(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_INDUSTRY_LIST, params);
}

export function ESF_update(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_UPDATE, params);
}

export function ESF_getStockVerify(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_GET_STOCKS_TO_PUBLISH, params);
}

export function ESF_publishFixture(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_PUBLISH_FIXTURE, params);
}

export function ESF_updateFixture(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_UPDATE_FIXTURE_STOCKS, params);
}

export function ESF_FixtureStats(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_GET_FIXTURE_STATS, params);
}

export function ESF_stockListWithClosePrice(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_STOCK_LIST_WITH_CLOSE_PRICE, params);
}

export function ESF_updatePriceStatus(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_UPDATE_PRICE_STATUS, params);
}

export function ESF_updateClosePrice(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_UPDATE_CLOSE_PRICE, params);
}

export function ESF_getHoliday(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.ESF_GET_HOLIDAY, params);
}
/*EQUITY STOCK FANTAY End code for Add contest category for */
/**Start API user engagement module */

export function QZ_add(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_ADD, params);
}

export function QZ_list(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_LIST, params);
}

export function QZ_delete_question(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_DELETE_QUESTION, params);
}

export function QZ_delete(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_DELETE_QUIZ, params);
}

export function QZ_show_hide(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_SHOW_HIDE, params);
}

export function QZ_update_question(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_UPDATE_QUESTION, params);
}

export function QZ_toggle_hold(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_TOGGLE_HOLD, params);
}

export function QZ_get_questions(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_QUESTIONS, params);
}

export function QZ_check_quiz_exist(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_CHECK_QUIZ_EXIST, params);
}

export function QZ_get_live_quiz_graph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_LIVE_QUIZ_GRAPH, params);
}

export function QZ_get_quiz_participation_graph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_QUIZ_PARTICIPATION_GRAPH, params);
}

export function QZ_get_quiz_leaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_QUIZ_LEADERBOARD, params);
}

export function QZ_get_top_gainers(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_TOP_GAINERS, params);
}

/**End API user engagement module */

export function QZ_get_leaderboard_by_category(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_LEADERBOARD_BY_CATEGORY, params);
}

export function QZ_get_daily_checkin_top_gainers(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_DAILY_CHECKIN_TOP_GAINERS, params);
}

export function QZ_get_leaderboard_dailycheckin(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_LEADERBOARD_DAILYCHECKIN, params);
}

export function QZ_get_download_app_leaderboard(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_DOWNLOAD_APP_LEADERBOARD, params);
}

export function QZ_get_download_app_graph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_GET_DOWNLOAD_APP_GRAPH, params);
}

export function QZ_reward_dashboard_graph(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.QZ_REWARD_DASHBOARD_GRAPH, params);
}

/**Start API Sport Predict module */

export function SP_PUBLISH_FIXTURE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_PUBLISH_FIXTURE, params);
}

export function SP_STOCK_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_STOCK_LIST, params);
}

export function SP_GET_FIXTURE_TEMPLATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_FIXTURE_TEMPLATE, params);
}

export function SP_CREATE_TEMPLATE_CONTEST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_CREATE_TEMPLATE_CONTEST, params);
}

export function SP_GET_COLLECTION_DETAILS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_COLLECTION_DETAILS, params);
}

export function SP_GET_FIXTURE_CONTEST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_FIXTURE_CONTEST, params);
}

export function SP_GET_INDUSTRY_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_INDUSTRY_LIST, params);
}

export function SP_GET_MASTER_DATA(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_MASTER_DATA, params);
}

export function SP_GET_GAME_DETAIL(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_GAME_DETAIL, params);
}

export function SP_GET_GAME_LINEUP_DETAIL(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_GAME_LINEUP_DETAIL, params);
}

export function SP_GET_CONTEST_TEMPLATE_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_CONTEST_TEMPLATE_LIST, params);
}

export function SP_GET_ALL_MASTER_DATA(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_ALL_MASTER_DATA, params);
}

export function SP_CREATE_TEMPLATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_CREATE_TEMPLATE, params);
}

export function SP_SAVE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_SAVE, params);
}

export function SP_UPDATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_UPDATE, params);
}

export function SP_UPDATE_FIXTURE_STOCKS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_UPDATE_FIXTURE_STOCKS, params);
}

export function SP_GET_CANDLE_TIME_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_GET_CANDLE_TIME_LIST, params);
}

export function SP_STOCK_CLOSING_RATE_FOR_TIME(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_STOCK_CLOSING_RATE_FOR_TIME, params);
}

export function SP_UPDATE_STOCK_RATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_UPDATE_STOCK_RATE, params);
}



// Start code for H2H
export function H2H_GET_H2H_USER_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_GET_H2H_USER_LIST, params);
}

export function H2H_GET_DASHBOARD_DATA(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_GET_DASHBOARD_DATA, params);
}

export function H2H_UPDATE_SETTING(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_UPDATE_SETTING, params);
}

export function H2H_SAVE_CMS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_SAVE_CMS, params);
}

export function H2H_GET_CMS_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_GET_CMS_LIST, params);
}

export function H2H_DELETE_CMS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_DELETE_CMS, params);
}

export function H2H_GET_UPCOMING_GAME_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_GET_UPCOMING_GAME_LIST, params);
}

export function H2H_GET_H2H_GAME_USERS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.H2H_GET_H2H_GAME_USERS, params);
}

export function LF_GET_SEASON_TO_PUBLISH(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_GET_SEASON_TO_PUBLISH, params);
}

export function LF_GET_GROUP(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_GET_GROUP, params);
}

export function LF_DELETE_GROUP(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_DELETE_GROUP, params);
}

export function LF_CREATE_GROUP(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_CREATE_GROUP, params);
}

export function LF_UPDATE_GROUP(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_UPDATE_GROUP, params);
}
export function LF_UPDATE_MATCH_SCORE_STATUS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_UPDATE_MATCH_SCORE_STATUS, params);
}
export function LF_UPDATE_MATCH_SCORE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LF_UPDATE_MATCH_SCORE, params);
}
export function SP_UPDATE_CANDLE_OPENING_CLOSING_RATES(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SP_UPDATE_CANDLE_OPENING_CLOSING_RATES, params);
}

/**Start API Live Sport Fantasy module */

export function LSF_PUBLISH_FIXTURE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_PUBLISH_FIXTURE, params);
}
// export function SP_STOCK_LIST(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_STOCK_LIST, params);
// }

export function LSF_GET_FIXTURE_TEMPLATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_GET_FIXTURE_TEMPLATE, params);
}

// export function SP_CREATE_TEMPLATE_CONTEST(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_CREATE_TEMPLATE_CONTEST, params);
// }

// export function SP_GET_COLLECTION_DETAILS(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_COLLECTION_DETAILS, params);
// }

// export function SP_GET_FIXTURE_CONTEST(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_FIXTURE_CONTEST, params);
// }

export function LSF_GET_INDUSTRY_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_GET_INDUSTRY_LIST, params);
}

export function LSF_GET_MASTER_DATA(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_GET_MASTER_DATA, params);
}

// export function SP_GET_GAME_DETAIL(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_GAME_DETAIL, params);
// }

// export function SP_GET_GAME_LINEUP_DETAIL(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_GAME_LINEUP_DETAIL, params);
// }

// export function SP_GET_CONTEST_TEMPLATE_LIST(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_CONTEST_TEMPLATE_LIST, params);
// }

// export function SP_GET_ALL_MASTER_DATA(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_ALL_MASTER_DATA, params);
// }

export function LSF_CREATE_TEMPLATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_CREATE_TEMPLATE, params);
}
export function LSF_getHoliday(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_GET_HOLIDAY, params);
}
// export function SP_SAVE(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_SAVE, params);
// }

// export function SP_UPDATE(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_UPDATE, params);
// }

// export function SP_UPDATE_FIXTURE_STOCKS(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_UPDATE_FIXTURE_STOCKS, params);
// }

// export function SP_GET_CANDLE_TIME_LIST(data) {
//     let params = data ? data : {}
//     return WSManager.Rest(NC.baseURL + NC.SP_GET_CANDLE_TIME_LIST, params);
// }

export function LSF_STOCK_CLOSING_RATE_FOR_TIME(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_STOCK_CLOSING_RATE_FOR_TIME, params);
}

export function LSF_UPDATE_STOCK_RATE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LSF_UPDATE_STOCK_RATE, params);
}
export function EXPORT_PDF_FILE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.EXPORT_PDF_FILE, params);
}



//PICKEM TOURNAMENT

export function getPickemAllLeagues(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_SPORT_LEAGUE, params)
}

export function getPickemFixtureList(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_FIXTURE_LIST, params)
}

export function getPickemTournamentDetail(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_TOURNAMENT_DETAIL, params)
}

export function getPickemTournamentList(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_TOURNAMENT_LIST, params)
}

export function getPickemGetTournamentFixtures(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_TOURNAMENT_FIXTURES, params)
}

export function getPickemSaveTournamentFixtures(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_SAVE_TOURNAMENT_FIXTURES, params)
}

export function getPickemSaveTournament(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_SAVE_TOURNAMENT, params)
}

export function saveTieBreakerAnswer(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_TIE_BREAKER_ANSWER, params)
}

export function submitQaPickem(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_SUBMIT_QA, params)
}

export function pickemGetMasterdata(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_MASTER_DATA, params)
}

export function pickemSubmitMarkComplete(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_SUBMIT_MARK_FIXTURE_COMPLETE, params)
}

export function cancelTournament(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_CANCEL_TOURNAMENT_FIXTURES, params)
}

export function pickemPinFunc(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_MARK_PIN, params)
}

export function pickemGetAllParticipantsList(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_JOIN_PARTICIPANTS_LIST, params)
}
export function pickemGetParticipantDetail(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_GET_PARTICIPANTS_DETAIL, params)
}
export function pickemMarkCompleted(data){
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_MARK_TOURNAMENT_COMPLETE, params)
}

export function LEAGUE_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.LEAGUE_MANAGMENT_TABLE, params);
    }

export function PROPS_PLAYER_LIST(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PROPS_PLAYER_LIST, params);
}


export function PROPS_LEAGUE_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PROPS_ALL_LEAGUE_LIST, params);
}

export function PROPS_PAYOUT_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PROPS_ALL_PAYOUT_LIST, params);
}

export function PROPS_ALL_USER_REPORT(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PROPS_ALL_USER_REPORT, params);
}

export function GET_RECORD_LIST_WHATSNEW(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.GET_RECORD_LIST_WHATSNEW, params);
}
export function DELETE_RECORD_WHATSNEW(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DELETE_RECORD_WHATSNEW, params);
}

export function SAVE_RECORD_WHATSNEW(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.SAVE_RECORD_WHATSNEW, params);
}
export function DO_UPLOAD_WHATSNEW_IMG(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DO_UPLOAD_WHATSNEW_IMG, params);
}

export function UPDATE_IS_FEATURED(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.UPDATE_IS_FEATURED, params);
}
export function DFSTR_PUBLISHED_FIXTURES(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFSTR_PUBLISHED_FIXTURES, params);
}
export function DFSTR_SAVE_TOUR_FIXTURES(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_SAVE_TOUR_FIXTURES, params);
}

export function DFST_SAVE_CONTEST_TOURNAMENT(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_SAVE_CONTEST_TOURNAMENT, params);
}
export function DFST_GET_TOUR_DETAIL(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOUR_DETAIL, params);
}
export function DFST_GET_TOUR_USERS(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_GET_TOUR_USERS, params);
}
export function DFST_PIN_TOURNAMENT(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_PIN_TOURNAMENT, params);
}
export function DFST_USER_TEAM_DETAIL(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFST_USER_TEAM_DETAIL, params);
}

export function PF_SAVE_TIE_BREAKER(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PF_SAVE_TIE_BREAKER, params);
}
export function PF_CANCEL_FIXTURE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PF_CANCEL_FIXTURE, params);
}

export function getDFSSeasonDetails(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.DFS_GET_FIXTURE_DETAILS, params);
}

export function TRADE_LEAGUE_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.TRADES_ALL_LEAGUE_LIST, params);
}

export function TRADE_TEMPLATE_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.TRADES_ALL_TEMPLATE_LIST, params);
}

export function PICKEM_LEAGUE_MANAGMENT_TABLE(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_LEAGUE_MANAGMENT_TABLE, params);
}
export function PICKEM_UPDATE_IS_FEATURED(data) {
    let params = data ? data : {}
    return WSManager.Rest(NC.baseURL + NC.PICKEM_UPDATE_IS_FEATURED, params);
}
