import React from 'react';
import Loadable from 'react-loadable'

import DefaultLayout from './containers/DefaultLayout';

function Loading() {
  return <div> Loading... </div>;
}
const LandingScreen = Loadable({
  loader: () => import('./views/LandingScreen/LandingScreen'),
  loading: Loading,
});

const VerifyFantasyDomain = Loadable({
  loader: () => import('./views/Common/VerifyFantasyDomain'),
  loading: Loading,
});
const CampaignDashboard = Loadable({
  loader: () => import('./views/Marketing/CommunicationDashboard/CampaignDashboard'),
  loading: Loading,
});
const UserSegmentation = Loadable({
  loader: () => import('./views/Marketing/UserSegmentation/NewCampaign'),
  loading: Loading,
});
const ContestTemplate = Loadable({
  loader: () => import('./views/ContestTemplate/Contesttemplate'),
  loading: Loading,
});
const CreateContestTemplate = Loadable({
  loader: () => import('./views/ContestTemplate/Createtemplate'),
  loading: Loading,
});
const CreateTemplateContest = Loadable({
  loader: () => import('./views/Contest/Createtemplatecontest'),
  loading: Loading,
});

const FixtureContest = Loadable({
  loader: () => import('./views/Contest/Fixturecontest'),
  loading: Loading,
});

const ContestList = Loadable({
  loader: () => import('./views/Contest/Contestlist'),
  loading: Loading,
});
const SystemUserReport = Loadable({
  loader: () => import('./views/SystemUserReport/UsersReport'),
  loading: Loading,
});
const MultigameContestList = Loadable({
  loader: () => import('./views/Contest/MultigameContestlist'),
  loading: Loading,
});

const CreateContest = Loadable({
  loader: () => import('./views/Contest/Createcontest'),
  loading: Loading,
});

const CreateF2PContest = Loadable({
  loader: () => import('./views/Contest/CreateF2Pcontest'),
  loading: Loading,
});

const Dashboard = Loadable({
  loader: () => import('./views/Dashboard/Dashboard'),
  loading: Loading,
});

const DFS = Loadable({
  loader: () => import('./views/GameCenter/DFS/DFS'),
  loading: Loading,
});

const Multigame = Loadable({
  loader: () => import('./views/GameCenter/Multigame/Multigame'),
  loading: Loading,
});

const Playing11 = Loadable({
  loader: () => import('./views/GameCenter/DFS/Playing11'),
  loading: Loading,
});
const CreateCollection = Loadable({
  loader: () => import('./views/GameCenter/Multigame/CreateCollection'),
  loading: Loading,
});

const Teams = Loadable({
  loader: () => import('./views/GameCenter/DFS/Teams'),
  loading: Loading,
});


const PlayerManagement = Loadable({
  loader: () => import('./views/GameCenter/DFS/PlayerManagement'),
  loading: Loading,
});

const PropsPlayerManagement = Loadable({
  loader: () => import('./views/PropsFantasy/PlayerManagement'),
  loading: Loading,
});

const PropsLeagueManagement = Loadable({
  loader: () => import('./views/PropsFantasy/LeagueManagment'),
  loading: Loading,
});

const PropsSettingManagement = Loadable({
  loader: () => import('./views/PropsFantasy/Setting'),
  loading: Loading,
});

const PropsUserReport = Loadable({
  loader: () => import('./views/PropsFantasy/UserReport'),
  loading: Loading,
});



const deals = Loadable({
  loader: () => import('./views/deals/DealList'),
  loading: Loading
})
const DealsDetail = Loadable({
  loader: () => import('./views/deals/DealsDetail'),
  loading: Loading
})


const AddUser = Loadable({
  loader: () => import('./views/UserManagement/AddUser/AddUser'),
  loading: Loading,
});

const Manageuser = Loadable({
  loader: () => import('./views/UserManagement/Manageuser/Manageuser'),
  loading: Loading,
});

const Logout = Loadable({
  loader: () => import('./views/Pages/Login/Logout'),
  loading: Loading,
});

const Profile = Loadable({
  loader: () => import('./views/UserManagement/Profile/Profile'),
  loading: Loading,
});

const ManageScoring = Loadable({
  loader: () => import('./views/ManageScoring/ManageScoring'),
  loading: Loading
})

const LobbyBanner = Loadable({
  loader: () => import('./views/Cms/LobbyBanner'),
  loading: Loading
})
const AppBanner = Loadable({
  loader: () => import('./views/Cms/AppBanner'),
  loading: Loading
})


const BackgroundImage = Loadable({
  loader: () => import('./views/Cms/BackgroundImage'),
  loading: Loading
})
const CMS = Loadable({
  loader: () => import('./views/Cms/CMS'),
  loading: Loading
})



//Start marketing module
const REFERRALAMOUNT = Loadable({
  loader: () => import('./views/MarketingReferral/ReferralAmount'),
  loading: Loading
})

const PROMOCODE = Loadable({
  loader: () => import('./views/MarketingReferral/PromoCode'),
  loading: Loading
})

const PROMOCODEDETAILS = Loadable({
  loader: () => import('./views/MarketingReferral/PromoCodeDetails'),
  loading: Loading
})

const WITHDRAWAL_LIST = Loadable({
  loader: () => import('./views/Finance/WithdrawalList'),
  loading: Loading
})

const USER_REPORTS = Loadable({
  loader: () => import('./views/Report/UserReport'),
  loading: Loading
})

const USER_MONEY_PAID = Loadable({
  loader: () => import('./views/Report/UserMoneyPaid'),
  loading: Loading
})

const USER_DEPOSIT_MONEY = Loadable({
  loader: () => import('./views/Report/UserDepositMoney'),
  loading: Loading
})

const REFERRAL_REPORT = Loadable({
  loader: () => import('./views/Report/UserReferralReport'),
  loading: Loading
})

const CONTEST_REPORT = Loadable({
  loader: () => import('./views/Report/UserContestReport'),
  loading: Loading
})


const TRANSACTION_LIST = Loadable({
  loader: () => import('./views/Finance/TransactionList'),
  loading: Loading
})

const CONTEST_DETAILS = Loadable({
  loader: () => import('./views/Finance/ContestDetails'),
  loading: Loading
})

const LANGUAGE_UPLOAD = Loadable({
  loader: () => import('./views/Common/LanguageUpload'),
  loading: Loading
})

const CoinsDashboard = Loadable({
  loader: () => import('./views/Coins/CoinsDashboard'),
  loading: Loading
})
const CoinsSetting = Loadable({
  loader: () => import('./views/Coins/CoinsSetting'),
  loading: Loading
})
const Redeem = Loadable({
  loader: () => import('./views/Coins/Redeem'),
  loading: Loading
})
const Merchandise = Loadable({
  loader: () => import('./views/Merchandise/Merchandise'),
  loading: Loading
})
const Promotions = Loadable({
  loader: () => import('./views/Coins/Promotions'),
  loading: Loading
})

const QuestionDetails = Loadable({
  loader: () => import('./views/Coins/QuestionDetails'),
  loading: Loading
})

const TopEarner = Loadable({
  loader: () => import('./views/Coins/TopEarner'),
  loading: Loading
})

const RedeemCoin = Loadable({
  loader: () => import('./views/Coins/RedeemCoin'),
  loading: Loading
})

const SpinTheWheel = Loadable({
  loader: () => import('./views/Coins/SpinTheWheel'),
  loading: Loading
})


const PredictionType = Loadable({
  loader: () => import('./views/PredictionType/PredictionType'),
  loading: Loading
})

const PredictionDashboard = Loadable({
  loader: () => import('./views/PredictionType/PredictionDashboard'),
  loading: Loading
})

const MostWinBid = Loadable({
  loader: () => import('./views/PredictionType/MostWinBid'),
  loading: Loading
})

const PredictionFixture = Loadable({
  loader: () => import('./views/PredictionType/PredictionFixture'),
  loading: Loading
})

const SetPrediction = Loadable({
  loader: () => import('./views/PredictionType/SetPrediction'),
  loading: Loading
})

const SEASON_SCHEDULE = Loadable({
  loader: () => import('./views/GameCenter/DFS/Seasonschedule'),
  loading: Loading,
});

const CreateNewPick = Loadable({
  loader: () => import('./views/Pickem/CreateNewPick'),
  loading: Loading
})

const JoinedUserList = Loadable({
  loader: () => import('./views/Pickem/JoinedUserList'),
  loading: Loading
})

const PickemContest = Loadable({
  loader: () => import('./views/Pickem/PickemContest'),
  loading: Loading
})

const PredictionCompletedQues = Loadable({
  loader: () => import('./views/PredictionType/PredictionCompletedQues'),
  loading: Loading
})

const SystemUsersList = Loadable({
  loader: () => import('./views/SystemUsers/SystemUsersList'),
  loading: Loading
})

const AddSystemUser = Loadable({
  loader: () => import('./views/SystemUsers/AddSystemUser'),
  loading: Loading
})

const AddNetwGameSysUser = Loadable({
  loader: () => import('./views/SystemUsers/AddNetwGameSysUser'),
  loading: Loading
})


//Start open Predictor
const OpenPredictionType = Loadable({
  loader: () => import('./views/OpenPredictor/PredictionType'),
  loading: Loading
})

const OpenPredictionDashboard = Loadable({
  loader: () => import('./views/OpenPredictor/PredictionDashboard'),
  loading: Loading
})

const OpenMostWinBid = Loadable({
  loader: () => import('./views/OpenPredictor/MostWinBid'),
  loading: Loading
})



const OpenSetPrediction = Loadable({
  loader: () => import('./views/OpenPredictor/SetPrediction'),
  loading: Loading
})

const OpenPredictionCompletedQues = Loadable({
  loader: () => import('./views/OpenPredictor/PredictionCompletedQues'),
  loading: Loading
})

const PredictionCategory = Loadable({
  loader: () => import('./views/OpenPredictor/PredictionCategory'),
  loading: Loading
})

const PredictionCreateCategory = Loadable({
  loader: () => import('./views/OpenPredictor/PredictionCreateCategory'),
  loading: Loading
})
//End open Predictor


//Start Prize Open Predictor
const PrizeOpenPredictionType = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/PredictionType'),
  loading: Loading
})

const PrizeOpenPredictionDashboard = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/PredictionDashboard'),
  loading: Loading
})

const PrizeOpenMostWinBid = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/MostWinBid'),
  loading: Loading
})

const PrizeOpenSetPrediction = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/SetPrediction'),
  loading: Loading
})

const PrizeOpenPredictionCompletedQues = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/PredictionCompletedQues'),
  loading: Loading
})

const PrizePredictionCategory = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/PredictionCategory'),
  loading: Loading
})

const PrizePredictionCreateCategory = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/PredictionCreateCategory'),
  loading: Loading
})

const SetPrize = Loadable({
  loader: () => import('./views/OpenPredictorWithPrize/SetPrize'),
  loading: Loading
})
//End Prize Open Predictor

const FixtureUpdateSalary = Loadable({
  loader: () => import('./views/GameCenter/DFS/FixtureUpdateSalary'),
  loading: Loading,
});

//Mini league starts here
const CreateMiniLeague = Loadable({
  loader: () => import('./views/Free2Play/CreateMiniLeague'),
  loading: Loading,
});

const EditMiniLeague = Loadable({
  loader: () => import('./views/Free2Play/EditMiniLeague'),
  loading: Loading,
});
const EditMiniLeagueFixture = Loadable({
  loader: () => import('./views/Free2Play/EditMiniLeagueFixture'),
  loading: Loading,
});

const MiniLeagueDetail = Loadable({
  loader: () => import('./views/Free2Play/MiniLeagueDetail'),
  loading: Loading,
});

const ChangePassword = Loadable({
  loader: () => import('./views/ChangePassword/ChangePassword'),
  loading: Loading,
});

//Start pickem module
const ViewPicks = Loadable({
  loader: () => import('./views/Pickem/ViewPicks'),
  loading: Loading
})

const PickemTDetails = Loadable({
  loader: () => import('./views/Pickem/PickemTDetails'),
  loading: Loading
})

const LeaguesPlayers = Loadable({
  loader: () => import('./views/Pickem/LeaguesPlayers'),
  loading: Loading
})

const PickemDasboard = Loadable({
  loader: () => import('./views/Pickem/PickemDasboard'),
  loading: Loading
})

const PickemMostWinBid = Loadable({
  loader: () => import('./views/Pickem/MostWinBid'),
  loading: Loading
})
//End pickem module

const AboutUs = Loadable({
  loader: () => import('./views/AboutUs/AboutUs'),
  loading: Loading
})

const FAQ = Loadable({
  loader: () => import('./views/Cms/FAQ'),
  loading: Loading
})

//Start admin role management
const AddRole = Loadable({
  loader: () => import('./views/AdminRole/AddRole'),
  loading: Loading
})

const AdminRoleList = Loadable({
  loader: () => import('./views/AdminRole/AdminRoleList'),
  loading: Loading
})

const WelcomeAdmin = Loadable({
  loader: () => import('./views/AdminRole/WelcomeAdmin'),
  loading: Loading
})

const ManageAvatars = Loadable({
  loader: () => import('./views/ManageAvatars/ManageAvatars'),
  loading: Loading
})
//End admin role management


const ForgotPassword = Loadable({
  loader: () => import('./views/Pages/ForgotPassword/ForgotPassword'),
  loading: Loading
});

//End admin role management

const DISTRIBUTOR = Loadable({
  loader: () => import('./views/Distributor/Distributor'),
  loading: Loading
});

const ADD_DISTRIBUTOR = Loadable({
  loader: () => import('./views/Distributor/Add'),
  loading: Loading
});



// LEAGUE MODUEL End
// https://github.com/ReactTraining/react-router/tree/master/packages/react-router-config
//Start new communication dashboard module
const CreateNewCampaign = Loadable({
  loader: () => import('./views/Marketing/CommunicationCampaign/CreateNewCampaign'),
  loading: Loading,
});

const CustomTemplate = Loadable({
  loader: () => import('./views/Marketing/CommunicationCampaign/CustomTemplate'),
  loading: Loading,
});

const CreateUserbaseList = Loadable({
  loader: () => import('./views/Marketing/CommunicationCampaign/CreateUserbaseList'),
  loading: Loading,
});
//End new communication dashboard module

const ParticipantReport = Loadable({
  loader: () => import('./views/Report/ParticipantReport'),
  loading: Loading,
});

//Start affiliate module
const Affiliate = Loadable({
  loader: () => import('./views/Affiliate/Affiliate'),
  loading: Loading
})

const AddAffiliate = Loadable({
  loader: () => import('./views/Affiliate/AddAffiliate'),
  loading: Loading
})

const AffiliateDashboard = Loadable({
  loader: () => import('./views/Affiliate/AffiliateDashboard'),
  loading: Loading
})

const AffiliateReport = Loadable({
  loader: () => import('./views/Affiliate/Report'),
  loading: Loading
})



const NewAffiliate = Loadable({
  loader: () => import('./views/NewAffiliate/NewAffililate'),
  loading: Loading
})
const AffiliateUsers = Loadable({
  loader: () => import('./views/Affiliate/AffiliateUsers'),
  loading: Loading
})

const HubPage = Loadable({
  loader: () => import('./views/Cms/HubPage'),
  loading: Loading
})

const Lobby = Loadable({
  loader: () => import('./views/Cms/Lobby'),
  loading: Loading
})

const WalletSetting = Loadable({
  loader: () => import('./views/Cms/WalletSetting'),
  loading: Loading
})

const MinimumWithdrawl = Loadable({
  loader: () => import('./views/Cms/MinimumWithdrawl'),
  loading: Loading
})
//End affiliate module

//Start Private Contest module
const PC_Dashboard = Loadable({
  loader: () => import('./views/PrivateContest/PC_Dashboard'),
  loading: Loading
})

const PC_Setting = Loadable({
  loader: () => import('./views/PrivateContest/PC_Setting'),
  loading: Loading
})
//End Private Contest module

const EmailSetting = Loadable({
  loader: () => import('./views/Settings/EmailSetting'),
  loading: Loading
})

const MobileApp = Loadable({
  loader: () => import('./views/Settings/MobileApp'),
  loading: Loading
})


const PrizeCron = Loadable({
  loader: () => import('./views/Settings/PrizeCron'),
  loading: Loading
})

const BuyCoin = Loadable({
  loader: () => import('./views/Coins/BuyCoin'),
  loading: Loading
})

const BuyCoinUserReport = Loadable({
  loader: () => import('./views/Coins/BuyCoinUserReport'),
  loading: Loading
})

const SelfExclude = Loadable({
  loader: () => import('./views/SelfExclude/SelfExclude'),
  loading: Loading
})
const AppSettingForm = Loadable({
  loader: () => import('./views/AppSettingForm/AppSettingForm'),
  loading: Loading
})

const ContestCategory = Loadable({
  loader: () => import('./views/GameCenter/DFS/ContestCategory'),
  loading: Loading,
});
const LeagueManagement = Loadable({
  loader: () => import('./views/GameCenter/DFS/LeagueManagment'),
  loading: Loading,
});

const ReferralSetprize = Loadable({
  loader: () => import('./views/MarketingReferral/ReferralSetprize'),
  loading: Loading
})

const ReferralLeaderboard = Loadable({
  loader: () => import('./views/MarketingReferral/ReferralLeaderboard'),
  loading: Loading
})

//Start network game
const NetworkGame = Loadable({
  loader: () => import('./views/NetworkGame/NetworkGame'),
  loading: Loading,
});

const NetworkGameDetails = Loadable({
  loader: () => import('./views/NetworkGame/NetworkGameDetails'),
  loading: Loading,
});

const NetworkCommission = Loadable({
  loader: () => import('./views/NetworkGame/NetworkCommission'),
  loading: Loading,
});

const NetworkContestReport = Loadable({
  loader: () => import('./views/NetworkGame/NetworkContestReport'),
  loading: Loading,
});

/* Start Leaderboard */
const Referral_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/Referral_Leaderboard'),
  loading: Loading,
});

const Depositor_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/Depositor_Leaderboard'),
  loading: Loading,
});

const Winning_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/Winning_Leaderboard'),
  loading: Loading,
});

const Feedback_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/Feedback_Leaderboard'),
  loading: Loading,
});

const TimeSpent_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/TimeSpent_Leaderboard'),
  loading: Loading,
});

const TopTeam_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/TopTeam_Leaderboard'),
  loading: Loading,
});

const Withdrawal_Leaderboard = Loadable({
  loader: () => import('./views/Leaderboard/Withdrawal_Leaderboard'),
  loading: Loading,
});

const UserMatchReport = Loadable({
  loader: () => import('./views/Report/UserMatchReport'),
  loading: Loading,
});
/* End Leaderboard */
//End network game
//Picken tournament
const PickemAddSports = Loadable({
  loader: () => import('./views/Pickem/PickemAddSports'),
  loading: Loading,
});

const PickemLeagueManagement = Loadable({
  loader: () => import('./views/Pickem/LeagueManagment'),
  loading: Loading,
});

const PTDetail = Loadable({
  loader: () => import('./views/Pickem/PTDetail'),
  loading: Loading,
});

const PTCreateTournament = Loadable({
  loader: () => import('./views/Pickem/PTCreateTournament'),
  loading: Loading,
});

const PTMerchandise = Loadable({
  loader: () => import('./views/Pickem/PTMerchandise'),
  loading: Loading,
});

//ERP start
const ERPDashbaord = Loadable({
  loader: () => import('./views/FinanceERP/ERPDashbaord'),
  loading: Loading,
});
const ERPTransactions = Loadable({
  loader: () => import('./views/FinanceERP/ERPTransactions'),
  loading: Loading,
});
//ERP end

//Start DFS tournament
const DfsTDetails = Loadable({
  loader: () => import('./views/GameCenter/DFSTournament/DfsTDetails'),
  loading: Loading,
});

const DfsCreateTournament = Loadable({
  loader: () => import('./views/GameCenter/DFSTournament/DfsCreateTournament'),
  loading: Loading,
});
//End DFS tournament
//Start GST module
const GSTDashboard = Loadable({
  loader: () => import('./views/GSTAccounting/GSTDashboard'),
  loading: Loading
})

const GSTReports = Loadable({
  loader: () => import('./views/GSTAccounting/GSTReports'),
  loading: Loading
})

const Reward = Loadable({
  loader: () => import('./views/Settings/Reward'),
  loading: Loading
})

const WhatsNew = Loadable({
  loader: () => import('./views/Settings/WhatsNew'),
  loading: Loading
})

const paymentManagment = Loadable({
  loader: () => import('./views/Settings/PaymentManagement'),
  loading: Loading
})

//End GST module

// START XP MODULE
const AddLevel = Loadable({
  loader: () => import('./views/XPModule/AddLevel'),
  loading: Loading
})
const EditLevel = Loadable({
  loader: () => import('./views/XPModule/EditLevel'),
  loading: Loading
})
const RewardsLevel = Loadable({
  loader: () => import('./views/XPModule/RewardsLevel'),
  loading: Loading
})
const ActivitiesLevel = Loadable({
  loader: () => import('./views/XPModule/ActivitiesLevel'),
  loading: Loading
})
const LevelLeaderboard = Loadable({
  loader: () => import('./views/XPModule/LevelLeaderboard'),
  loading: Loading
})
const ActivitiesLeaderboard = Loadable({
  loader: () => import('./views/XPModule/ActivitiesLeaderboard'),
  loading: Loading
})

const SetprizeLeaderboard = Loadable({
  loader: () => import('./views/SetprizeLeaderboard/SetprizeLeaderboard'),
  loading: Loading
})

const LeaderboardList = Loadable({
  loader: () => import('./views/SetprizeLeaderboard/LeaderboardList'),
  loading: Loading
})

const LeaderboardDetails = Loadable({
  loader: () => import('./views/SetprizeLeaderboard/LeaderboardDetails'),
  loading: Loading
})

const UserPointHistory = Loadable({
  loader: () => import('./views/XPModule/UserPointHistory'),
  loading: Loading
})
// END XP MODULE
const BoosterList = Loadable({
  loader: () => import('./views/Booster/BoosterList'),
  loading: Loading
})

const PTSetPrizes = Loadable({
  loader: () => import('./views/Pickem/PTSetPrizes'),
  loading: Loading,
});
const ContestDetailReport = Loadable({
  loader: () => import('./views/NetworkGame/ContestDetailReport'),
  loading: Loading
})

const ApplyBooster = Loadable({
  loader: () => import('./views/Booster/ApplyBooster'),
  loading: Loading
})
const ManageSystemUser = Loadable({
  loader: () => import('./views/SystemUsers/ManageSystemUser'),
  loading: Loading
})

/** Start stock fantasy  */
const SF_Merchandise = Loadable({
  loader: () => import('./views/StockFantasy/SF_Merchandise'),
  loading: Loading,
});
const SF_ContestCategory = Loadable({
  loader: () => import('./views/StockFantasy/SF_ContestCategory'),
  loading: Loading,
});
const SF_ContestTemplate = Loadable({
  loader: () => import('./views/StockFantasy/SF_ContestTemplate'),
  loading: Loading,
});
const SF_CreateTemplate = Loadable({
  loader: () => import('./views/StockFantasy/SF_CreateTemplate'),
  loading: Loading,
});
const SF_ContestDetails = Loadable({
  loader: () => import('./views/StockFantasy/SF_ContestDetails'),
  loading: Loading,
});
const SF_ManageStock = Loadable({
  loader: () => import('./views/StockFantasy/SF_ManageStock'),
  loading: Loading,
});
const SF_DSF = Loadable({
  loader: () => import('./views/StockFantasy/SF_DSF'),
  loading: Loading,
});
const SF_VerifyStocks = Loadable({
  loader: () => import('./views/StockFantasy/SF_VerifyStocks'),
  loading: Loading,
});
const SF_CreateTemplateContest = Loadable({
  loader: () => import('./views/StockFantasy/SF_CreateTemplateContest'),
  loading: Loading,
});
const SF_FixtureContest = Loadable({
  loader: () => import('./views/StockFantasy/SF_FixtureContest'),
  loading: Loading,
});
const SF_CreateContest = Loadable({
  loader: () => import('./views/StockFantasy/SF_CreateContest'),
  loading: Loading,
});
const SF_UserContestReport = Loadable({
  loader: () => import('./views/StockFantasy/SF_UserContestReport'),
  loading: Loading,
});

const SF_Nse_Stats = Loadable({
  loader: () => import('./views/StockFantasy/SF_Nse_Stats'),
  loading: Loading,
});

const SF_ContestList = Loadable({
  loader: () => import('./views/StockFantasy/SF_ContestList'),
  loading: Loading,
});

const SF_MatchCloser = Loadable({
  loader: () => import('./views/StockFantasy/SF_MatchCloser'),
  loading: Loading,
});
/** End stock fantasy  */
const ViewRookie = Loadable({
  loader: () => import('./views/Rookie/ViewRookie'),
  loading: Loading
})

const AllRookieUser = Loadable({
  loader: () => import('./views/Rookie/AllRookieUser'),
  loading: Loading
})
const BannedStates = Loadable({
  loader: () => import('./views/BannedStates/BannedStates'),
  loading: Loading
})

const Subscription = Loadable({
  loader: () => import('./views/Coins/Subscription'),
  loading: Loading
})

/** Start equity stock fantasy  */
const ESF_Merchandise = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_Merchandise'),
  loading: Loading,
});
const ESF_ContestCategory = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_ContestCategory'),
  loading: Loading,
});
const ESF_ContestTemplate = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_ContestTemplate'),
  loading: Loading,
});
const ESF_CreateTemplate = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_CreateTemplate'),
  loading: Loading,
});
const ESF_ContestDetails = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_ContestDetails'),
  loading: Loading,
});
const ESF_ManageStock = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_ManageStock'),
  loading: Loading,
});
const ESF_DSF = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_DSF'),
  loading: Loading,
});
const ESF_VerifyStocks = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_VerifyStocks'),
  loading: Loading,
});
const ESF_CreateTemplateContest = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_CreateTemplateContest'),
  loading: Loading,
});
const ESF_FixtureContest = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_FixtureContest'),
  loading: Loading,
});
const ESF_CreateContest = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_CreateContest'),
  loading: Loading,
});
const ESF_UserContestReport = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_UserContestReport'),
  loading: Loading,
});

const ESF_Nse_Stats = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_Nse_Stats'),
  loading: Loading,
});

const ESF_ContestList = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_ContestList'),
  loading: Loading,
});

const ESF_MatchCloser = Loadable({
  loader: () => import('./views/EquityStockFantasy/ESF_MatchCloser'),
  loading: Loading,
});
/** End equity stock fantasy  */
/**Start User engagement(Quiz) */
const CreateQuiz = Loadable({
  loader: () => import('./views/Quiz/CreateQuiz'),
  loading: Loading
})

const QuizQuestionList = Loadable({
  loader: () => import('./views/Quiz/QuizQuestionList'),
  loading: Loading
})

const QuizDashboard = Loadable({
  loader: () => import('./views/Quiz/QuizDashboard'),
  loading: Loading
})

const QuizViewAllUser = Loadable({
  loader: () => import('./views/Quiz/QuizViewAllUser'),
  loading: Loading
})

const QuizReportsList = Loadable({
  loader: () => import('./views/Quiz/QuizReportsList'),
  loading: Loading
})

const QuizSpinWheelLrdBrd = Loadable({
  loader: () => import('./views/Quiz/QuizSpinWheelLrdBrd'),
  loading: Loading
})

const QuizAppLrdBrd = Loadable({
  loader: () => import('./views/Quiz/QuizAppLrdBrd'),
  loading: Loading
})

const QuizRewardDashboard = Loadable({
  loader: () => import('./views/Quiz/QuizRewardDashboard'),
  loading: Loading
})

/**Start Stock Predict */

const SP_CreateCandle = Loadable({
  loader: () => import('./views/StockPredict/SP_CreateCandle'),
  loading: Loading
})

const SP_CreateTemplateContest = Loadable({
  loader: () => import('./views/StockPredict/SP_CreateTemplateContest'),
  loading: Loading
})

const SP_DSF = Loadable({
  loader: () => import('./views/StockPredict/SP_DSF'),
  loading: Loading
})

const SP_FixtureContest = Loadable({
  loader: () => import('./views/StockPredict/SP_FixtureContest'),
  loading: Loading
})

const SP_CreateContest = Loadable({
  loader: () => import('./views/StockPredict/SP_CreateContest'),
  loading: Loading
})

const SPContestTemplate = Loadable({
  loader: () => import('./views/StockPredict/SPContestTemplate'),
  loading: Loading
})

const SP_CreateTemplate = Loadable({
  loader: () => import('./views/StockPredict/SP_CreateTemplate'),
  loading: Loading
})

const SP_ManageStock = Loadable({
  loader: () => import('./views/StockPredict/SP_ManageStock'),
  loading: Loading
})

const SP_MatchCloser = Loadable({
  loader: () => import('./views/StockPredict/SP_MatchCloser'),
  loading: Loading
})

const SP_Merchandise = Loadable({
  loader: () => import('./views/StockPredict/SP_Merchandise'),
  loading: Loading
})

const SP_ContestList = Loadable({
  loader: () => import('./views/StockPredict/SP_ContestList'),
  loading: Loading
})

const SP_ContestDetails = Loadable({
  loader: () => import('./views/StockPredict/SP_ContestDetails'),
  loading: Loading
})

const SP_Nse_Stats = Loadable({
  loader: () => import('./views/StockPredict/SP_Nse_Stats'),
  loading: Loading
})
/**End Stock Predict */

/**Start Code for H2H */

const h2hDashboard = Loadable({
  loader: () => import('./views/H2H/h2hDashboard'),
  loading: Loading
})

const ViewAllH2hContest = Loadable({
  loader: () => import('./views/H2H/ViewAllH2hContest'),
  loading: Loading
})

const H2hContestUser = Loadable({
  loader: () => import('./views/H2H/H2hContestUser'),
  loading: Loading
})

const H2HCms = Loadable({
  loader: () => import('./views/H2H/H2HCms'),
  loading: Loading
})

// Start fast khelo
const LF_Merchandise = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Merchandise'),
  loading: Loading
})

const LF_Contesttemplate = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Contesttemplate'),
  loading: Loading
})

const LF_Createtemplate = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Createtemplate'),
  loading: Loading
})

const LF_DFS = Loadable({
  loader: () => import('./views/LiveFantasy/LF_DFS'),
  loading: Loading
})

const LF_OverSetUp = Loadable({
  loader: () => import('./views/LiveFantasy/LF_OverSetUp'),
  loading: Loading
})

const LF_Createtemplatecontest = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Createtemplatecontest'),
  loading: Loading
})

const LF_OverDetails = Loadable({
  loader: () => import('./views/LiveFantasy/LF_OverDetails'),
  loading: Loading
})

const LF_Fixturecontest = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Fixturecontest'),
  loading: Loading
})

const LF_Createcontest = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Createcontest'),
  loading: Loading
})

const LF_Contestlist = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Contestlist'),
  loading: Loading
})

const LF_ContestDetails = Loadable({
  loader: () => import('./views/LiveFantasy/LF_ContestDetails'),
  loading: Loading
})

const LF_Seasonschedule = Loadable({
  loader: () => import('./views/LiveFantasy/LF_Seasonschedule'),
  loading: Loading
})

const LF_ContestCategory = Loadable({
  loader: () => import('./views/LiveFantasy/LF_ContestCategory'),
  loading: Loading
})

const LF_UpdateScore = Loadable({
  loader: () => import('./views/LiveFantasy/LF_UpdateScore'),
  loading: Loading
})
const LF_PC_Dashboard = Loadable({
  loader: () => import('./views/LiveFantasy/LF_PC_Dashboard'),
  loading: Loading
})
const LF_PC_Setting = Loadable({
  loader: () => import('./views/LiveFantasy/LF_PC_Setting'),
  loading: Loading
})
const LF_USER_MONEY_PAID = Loadable({
  loader: () => import('./views/LiveFantasy/LF_UserMoneyPaid'),
  loading: Loading
})
const LF_USER_CONTEST = Loadable({
  loader: () => import('./views/LiveFantasy/LF_UserContest'),
  loading: Loading
})

/**Start Stock Predict */

const LSF_CreateCandle = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_CreateCandle'),
  loading: Loading
})

const LSF_CreateTemplateContest = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_CreateTemplateContest'),
  loading: Loading
})

const LSF_DSF = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_DSF'),
  loading: Loading
})

const LSF_FixtureContest = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_FixtureContest'),
  loading: Loading
})

const LSF_CreateContest = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_CreateContest'),
  loading: Loading
})

const LSF_CreateTemplate = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_CreateTemplate'),
  loading: Loading
})

const LSFContestTemplate = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_ContestTemplate'),
  loading: Loading
})

const LSF_ManageStock = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_ManageStock'),
  loading: Loading
})

const LSF_MatchCloser = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_MatchCloser'),
  loading: Loading
})

const LSF_Merchandise = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_Merchandise'),
  loading: Loading
})

const LSF_ContestList = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_ContestList'),
  loading: Loading
})

const LSF_ContestDetails = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_ContestDetail'),
  loading: Loading
})

const LSF_NseStats = Loadable({
  loader: () => import('./views/LiveStockFantasy/LSF_Nse_Stats'),
  loading: Loading
})
/**End Stock Predict */
//pick-fantasy

const PicksFantasy = Loadable({
  loader: () => import('./views/PickFantasy/Fixture'),
  loading: Loading
})
const PFContestDashboard = Loadable({
  loader: () => import('./views/PickFantasy/PFContestDashboard'),
  loading: Loading
})
const TeamPlayerManagement = Loadable({
  loader: () => import('./views/PickFantasy/TeamPlayerManagement'),
  loading: Loading
})
const Leagues = Loadable({
  loader: () => import('./views/PickFantasy/Leagues'),
  loading: Loading
})
const PFContestTemplate = Loadable({
  loader: () => import('./views/PickFantasy/PFContestTemplate'),
  loading: Loading
})
const PFCreateTemplate = Loadable({
  loader: () => import('./views/PickFantasy/PFCreateTemplate'),
  loading: Loading
})
const PFCreateContest = Loadable({
  loader: () => import('./views/PickFantasy/PFCreateContest'),
  loading: Loading
})
const PFContestTemplateDetail = Loadable({
  loader: () => import('./views/PickFantasy/PFContestTemplateDetail'),
  loading: Loading
})
const PFAddMatch = Loadable({
  loader: () => import('./views/PickFantasy/PFAddMatch'),
  loading: Loading
})
const PFCreatetemplatecontest = Loadable({
  loader: () => import('./views/PickFantasy/PFCreatetemplatecontest'),
  loading: Loading
})
const CreateFormContest = Loadable({
  loader: () => import('./views/PickFantasy/CreateFormContest'),
  loading: Loading
})
const PF_ContestList = Loadable({
  loader: () => import('./views/PickFantasy/PF_ContestList'),
  loading: Loading
})

const CopyCreateContestTemplate = Loadable({
  loader: () => import('./views/PickFantasy/PFCreateTemplate'),
  loading: Loading
})
const PFContestReport = Loadable({
  loader: () => import('./views/PickFantasy/PFContestReport'),
  loading: Loading
})

const PFTemplateDetails = Loadable({
  loader: () => import('./views/PickFantasy/PFTemplateDetails'),
  loading: Loading
})
const PFContestDetail = Loadable({
  loader: () => import('./views/PickFantasy/PFContestDetail'),
  loading: Loading
})

const TDSAccounting = Loadable({
  loader: () => import('./views/TDSAccounting/TDSAccounting'),
  loading: Loading
})

const TDSDocument = Loadable({
  loader: () => import('./views/TDSDocument/TDSDocument'),
  loading: Loading
})
const PaymentSetup = Loadable({
  loader: () => import('./views/ManualPayment/PaymentSetup'),
  loading: Loading
})
const ReportMP = Loadable({
  loader: () => import('./views/ManualPayment/Reports'),
  loading: Loading
})

const PropsFantasy = Loadable({
  loader: () => import('./views/PropsFantasy/Teams'),
  loading: Loading,
});

const PropsPlayer = Loadable({
  loader: () => import('./views/PropsFantasy/PropsPlayer'),
  loading: Loading,
});

const TradeLeagueManagement = Loadable({
  loader: () => import('./views/OpinionTrading/LeagueManagment'),
  loading: Loading,
});

const TradeTeamManagement = Loadable({
  loader: () => import('./views/OpinionTrading/Teams'),
  loading: Loading,
});

const TradeTemplate = Loadable({
  loader: () => import('./views/OpinionTrading/Template'),
  loading: Loading,
});


const OT_DFS = Loadable({
  loader: () => import('./views/OpinionTrading/OT_DFS'),
  loading: Loading
})

const OTAddMatch = Loadable({
  loader: () => import('./views/OpinionTrading/OTAddMatch'),
  loading: Loading
})

const OT_REPORT = Loadable({
  loader: () => import('./views/OpinionTrading/Report'),
  loading: Loading
})


const OTPUBMatch = Loadable({
  loader: () => import('./views/OpinionTrading/OTPUBMatch'),
  loading: Loading
})






const routes = [{
  path: '/',
  name: 'Home',
  component: DefaultLayout,
  exact: true
},
{
  path: '/landing-screen',
  name: 'Landing Screen',
  component: LandingScreen
},
{
  path: '/verify-fantasy-domain/:encoded_auth_key',
  name: 'Verify Domain',
  component: VerifyFantasyDomain
},
{
  path: '/dashboard',
  name: 'Dashboard',
  component: Dashboard
},
{
  path: '/game_center/DFS',
  name: 'DFS',
  component: DFS,
  exact: true
},
{
  path: '/multigame/Fixtures',
  name: 'Multigame',
  component: Multigame,
  exact: true
},
{
  path: '/game_center/Playing11/:league_id/:season_id',
  name: 'Select Playing11',
  component: Playing11,
  exact: true
},
{
  path: '/game_center/CreateCollection',
  name: 'Create Collection',
  component: CreateCollection,
  exact: true
},
{
  path: '/game_center/Teams',
  name: 'Teams',
  component: Teams,
  exact: true
},

  {
    path: '/propsFantasy/Teams',
    name: 'Teams Management',
    component: PropsFantasy,
    exact: true
  },

  {
    path: '/propsFantasy/player-management',
    name: 'Player Management',
    component: PropsPlayerManagement,
    exact: true
  },

  {
    path: '/propsFantasy/league-managment',
    name: 'League Management',
    component: PropsLeagueManagement,
    exact: true
  },

  {
    path: '/propsFantasy/setting',
    name: 'League Management',
    component: PropsSettingManagement,
    exact: true
  },
  {
    path: '/propsFantasy/user-report',
    name: 'User Report',
    component: PropsUserReport,
    exact: true
  },

  {
    path: '/propsFantasy/players',
    name: 'Player',
    component: PropsPlayer,
    exact: true
  },
  
  
{
  path: '/game_center/player-management',
  name: 'Players Management',
  component: PlayerManagement,
  exact: true
},
{
  path: '/usermanagement/manageuser',
  name: 'Manage User',
  component: Manageuser,
  exact: true
},

{
  path: '/marketing/user_segmentation',
  name: 'UserSegmentation',
  component: UserSegmentation,
  exact: true
},
{
  path: '/marketing/communication_dashboard',
  name: 'CommunicationDashboard',
  component: CampaignDashboard,
  exact: true
},

{
  path: '/game_center/contesttemplate',
  name: 'ContestTemplate',
  component: ContestTemplate,
  exact: true
},
{
  path: '/createcontesttemplate',
  name: 'CreateContestTemplate',
  component: CreateContestTemplate,
  exact: true
},

//contest section
{
  path: '/contest/createtemplatecontest/:collection_master_id/:season_id/:tab/:fromfixture',
  name: 'CreateTemplateContest',
  component: CreateTemplateContest,
  exact: true
},
{
  path: '/contest/createtemplatecollectioncontest/:league_id/:collection_master_id',
  name: 'CreateTemplateContest',
  component: CreateTemplateContest,
  exact: true
},
{
  path: '/contest/fixturecontest/:collection_master_id/:season_id',
  name: 'FixtureContest',
  component: FixtureContest,
  exact: true
},
{
  path: '/contest/collectioncontest/:league_id/:collection_master_id',
  name: 'FixtureContest',
  component: FixtureContest,
  exact: true
},

{
  path: '/contest/createcontest/:collection_master_id/:season_id',
  name: 'CreateContest',
  component: CreateContest,
  exact: true
},
{
  path: '/contest/createf2pcontest/:league_id/:season_id',
  name: 'CreateF2PContest',
  component: CreateF2PContest,
  exact: true
},
{
  path: '/contest/createcollectioncontest/:league_id/:collection_master_id',
  name: 'CreateContest',
  component: CreateContest,
  exact: true
},
{
  path: '/game_center/contest/contestlist',
  name: 'ContestList',
  component: ContestList,
  exact: true
},
{
  path: '/game_center/system_user_report',
  name: 'SystemUserSupport',
  component: SystemUserReport,
  exact: true
},
{
  path: '/pickem/pickem-detail/:tournament_id',
  name: "PickemTDetails",
  component: PickemTDetails,
  exact: true
},
{
  path: '/pickem/view-contest/:tournament_id/:user_count',
  name: "PickemContest",
  component: PickemContest,
  exact: true
},
{
  path: '/contest/multigamecontest',
  name: 'MultigameContestList',
  component: MultigameContestList,
  exact: true
},
{ path: '/logout', name: 'logout', component: Logout },
{
  path: '/game_center/Teams',
  name: 'Teams',
  component: Teams
},
{
  path: '/contest/fixturecontest/:collection_master_id/:season_id/:tab',
  name: 'FixtureContest',
  component: FixtureContest,
  exact: true
},
{
  path: '/manage_user',
  name: 'Manageuser',
  component: Manageuser,
  exact: true
},
{
  path: '/add_user',
  name: 'adduser',
  component: AddUser,
  exact: true
},
{
  path: '/profile/:user_unique_id',
  name: 'Profile',
  component: Profile,
  exact: true
},
//Scoring section
{
  path: '/game_center/manage_scoring/',
  name: 'ManageScoring',
  component: ManageScoring,
  exact: true
},
//CMS
{
  path: '/cms/lobby_banner/',
  name: 'LobbyBanner',
  component: LobbyBanner,
  exact: true
},
{
  path: '/cms/app_banner/',
  name: 'AppBanner',
  component: AppBanner,
  exact: true
},
{
  path: '/deals/deal_list/',
  name: 'Deals',
  component: deals,
  exact: true
},
{
  path: '/deals/deal_list/detail/:deal_unique_id',
  name: 'DealsDetail',
  component: DealsDetail,
  exact: true
},
{
  path: '/cms/background_image/',
  name: 'BackgroundImage',
  component: BackgroundImage,
  exact: true
},
{
  path: '/cms/cms/',
  name: 'CMS',
  component: CMS,
  exact: true
},
{
  path: '/marketing/referral_amount/',
  name: 'REFERRALAMOUNT',
  component: REFERRALAMOUNT,
  exact: true
},
{
  path: '/marketing/promo_code/',
  name: "PROMOCODE",
  component: PROMOCODE,
  exact: true
},
{
  path: '/marketing/promo_code/details/:promo_code/:promo_type/:tab',
  name: "PROMOCODEDETAILS",
  component: PROMOCODEDETAILS,
  exact: true
},
{
  path: '/finance/withdrawal_list',
  name: "WITHDRAWAL_LIST",
  component: WITHDRAWAL_LIST,
  exact: true
},
{
  path: '/report/user_report',
  name: "USER_REPORTS",
  component: USER_REPORTS,
  exact: true
},
{
  path: '/report/user_money_paid',
  name: "USER_MONEY_PAID",
  component: USER_MONEY_PAID,
  exact: true
},
{
  path: '/report/user_deposit_amount',
  name: "USER_DEPOSIT_MONEY",
  component: USER_DEPOSIT_MONEY,
  exact: true
},
{
  path: '/report/referral_report',
  name: "REFERRAL_REPORT",
  component: REFERRAL_REPORT,
  exact: true
},
{
  path: '/report/contest_report',
  name: "CONTEST_REPORT",
  component: CONTEST_REPORT,
  exact: true
},
{
  path: '/finance/transaction_list',
  name: "TRANSACTION_LIST",
  component: TRANSACTION_LIST,
  exact: true
},
{
  path: '/language-upload',
  name: "LANGUAGE_UPLOAD",
  component: LANGUAGE_UPLOAD,
  exact: true
},
{

  path: '/finance/contest_detail/:id/:isTourGame?/',
  name: "CONTEST_DETAILS",
  component: CONTEST_DETAILS,
  exact: true
},
{
  path: '/coins/dashboard',
  name: "CoinsDashboard",
  component: CoinsDashboard,
  exact: true
},
{
  path: '/coins/setting',
  name: "CoinsSetting",
  component: CoinsSetting,
  exact: true
},
{
  path: '/coins/redeem',
  name: "Redeem",
  component: Redeem,
  exact: true
},
{
  path: '/coins/promotions',
  name: "promotions",
  component: Promotions,
  exact: true
},
{
  path: '/coins/promotions/:pending',
  name: "promotions",
  component: Promotions,
  exact: true
},
{
  path: '/coins/question-details/:qid',
  name: "QuestionDetails",
  component: QuestionDetails,
  exact: true
},
{
  path: '/coins/top-earner',
  name: "TopEarner",
  component: TopEarner,
  exact: true
},
{
  path: '/coins/top-redeemer',
  name: "TopEarner",
  component: TopEarner,
  exact: true
},
{
  path: '/coins/coins-distributed',
  name: "TopEarner",
  component: TopEarner,
  exact: true
},
{
  path: '/coins/coin-redeem',
  name: "RedeemCoin",
  component: RedeemCoin,
  exact: true
},
{
  path: '/coins/spinthewheel',
  name: "SpinTheWheel",
  component: SpinTheWheel,
  exact: true
},
{
  path: '/prediction/module',
  name: "PredictionType",
  component: PredictionType,
  exact: true
},
{
  path: '/open-predictor/module',
  name: "OpenPredictionType",
  component: OpenPredictionType,
  exact: true
},
{
  path: '/prize-open-predictor/module',
  name: "PrizeOpenPredictionType",
  component: PrizeOpenPredictionType,
  exact: true
},
{
  path: '/prediction',
  name: "PredictionType",
  component: PredictionType,
  exact: true
},
{
  path: '/open-predictor',
  name: "OpenPredictionType",
  component: OpenPredictionType,
  exact: true
},
{
  path: '/prize-open-predictor',
  name: "PrizeOpenPredictionType",
  component: PrizeOpenPredictionType,
  exact: true
},
{
  path: '/prediction/dashboard',
  name: "PredictionDashboard",
  component: PredictionDashboard,
  exact: true
},
{
  path: '/open-predictor/dashboard',
  name: "OpenPredictionDashboard",
  component: OpenPredictionDashboard,
  exact: true
},
{
  path: '/prize-open-predictor/dashboard',
  name: "PrizeOpenPredictionDashboard",
  component: PrizeOpenPredictionDashboard,
  exact: true
},
{
  path: '/prediction/most-win',
  name: "MostWinBid",
  component: MostWinBid,
  exact: true
},
{
  path: '/open-predictor/most-win',
  name: "OpenMostWinBid",
  component: OpenMostWinBid,
  exact: true
},
{
  path: '/prize-open-predictor/most-answer',
  name: "PrizeOpenMostWinBid",
  component: PrizeOpenMostWinBid,
  exact: true
},
{
  path: '/prediction/most-bid',
  name: "MostWinBid",
  component: MostWinBid,
  exact: true
},
{
  path: '/open-predictor/most-bid',
  name: "OpenMostWinBid",
  component: OpenMostWinBid,
  exact: true
},
{
  path: '/prize-open-predictor/most-attempt',
  name: "PrizeOpenMostWinBid",
  component: PrizeOpenMostWinBid,
  exact: true
},
{
  path: '/prediction/fixture',
  name: "PredictionFixture",
  component: PredictionFixture,
  exact: true
},

{
  path: '/prediction/set-prediction/:fixturetype/:seasongameid/:sportsid',
  name: "SetPrediction",
  component: SetPrediction,
  exact: true
},
{
  path: '/merchandise',
  name: "Merchandise",
  component: Merchandise,
  exact: true
},
{
  path: '/pickem/picks',
  name: "ViewPicks",
  component: ViewPicks,
  exact: true
},
{
  path: '/pickem/leagues',
  name: "LeaguesPlayers",
  component: LeaguesPlayers,
  exact: true
},
{
  path: '/pickem/dashboard',
  name: "PickemDasboard",
  component: PickemDasboard,
  exact: true
},
{
  path: '/pickem/most-win',
  name: "PickemMostWinBid",
  component: PickemMostWinBid,
  exact: true
},
{
  path: '/pickem/most-bid',
  name: "PickemMostWinBid",
  component: PickemMostWinBid,
  exact: true
},
{
  path: '/open-predictor/set-prediction/:category_id/:type',
  name: "OpenSetPrediction",
  component: OpenSetPrediction,
  exact: true
},
{
  path: '/prize-open-predictor/set-prediction/:category_id/:type',
  name: "PrizeOpenSetPrediction",
  component: PrizeOpenSetPrediction,
  exact: true
},
{
  path: '/prediction/completed-questions/:season_game_uid',
  name: "PredictionCompletedQues",
  component: PredictionCompletedQues,
  exact: true
},
{
  path: '/open-predictor/completed-questions/:season_game_uid',
  name: "OpenPredictionCompletedQues",
  component: OpenPredictionCompletedQues,
  exact: true
},
{
  path: '/prize-open-predictor/completed-questions/:season_game_uid',
  name: "PrizeOpenPredictionCompletedQues",
  component: PrizeOpenPredictionCompletedQues,
  exact: true
},
{
  path: '/open-predictor/category/',
  name: "PredictionCategory",
  component: PredictionCategory,
  exact: true
},
{
  path: '/prize-open-predictor/category/',
  name: "PrizePredictionCategory",
  component: PrizePredictionCategory,
  exact: true
},
{
  path: '/open-predictor/create-category/',
  name: "PredictionCreateCategory",
  component: PredictionCreateCategory,
  exact: true
},
{
  path: '/prize-open-predictor/create-category/',
  name: "PrizePredictionCreateCategory",
  component: PrizePredictionCreateCategory,
  exact: true
},
{
  path: '/prize-open-predictor/set-prize/',
  name: "SetPrize",
  component: SetPrize,
  exact: true
},
{
  path: '/DFS/season_schedule/:leagueid/:gameid/:sportsid/:tab',
  name: "SEASON_SCHEDULE",
  component: SEASON_SCHEDULE,
  exact: true
},
{
  path: '/pickem/create-pick',
  name: "CreateNewPick",
  component: CreateNewPick,
  exact: true
},
{
  path: '/pickem/userlist',
  name: "JoinedUserList",
  component: JoinedUserList,
  exact: true
},
{
  path: '/game_center/update-salary/:league_id/:season_id',
  name: "FixtureUpdateSalary",
  component: FixtureUpdateSalary,
  exact: true
},
{
  path: '/change-password',
  name: "ChangePassword",
  component: ChangePassword,
  exact: true
},
{
  path: '/freetwoplay/create_mini_league',
  name: "CreateMiniLeague",
  component: CreateMiniLeague,
  exact: true
},
{
  path: '/freetwoplay/edit_mini_league/:mini_league_uid',
  name: "EditMiniLeague",
  component: EditMiniLeague,
  exact: true
},
{
  path: '/freetwoplay/edit_mini_league_fixture/:mini_league_uid',
  name: "EditMiniLeagueFixture",
  component: EditMiniLeagueFixture,
  exact: true
},
{
  path: '/freetwoplay/mini_league_detail/:mini_league_uid',
  name: "MiniLeagueDetail",
  component: MiniLeagueDetail,
  exact: true
},
{
  path: '/system-users/userslist',
  name: "SystemUsersList",
  component: SystemUsersList,
  exact: true
},
{
  path: '/system-users/add-system-users/:collection_master_id/:season_id/:contest_unique_id',
  name: "AddSystemUser",
  component: AddSystemUser,
  exact: true
},
{
  path: '/cms/about-us/:page_id',
  name: "AboutUs",
  component: AboutUs,
  exact: true
},
{
  path: '/cms/faq/:page_id',
  name: "FAQ",
  component: FAQ,
  exact: true
},
{
  path: '/admin-role/add-role',
  name: 'AddRole',
  component: AddRole,
  exact: true
},
{
  path: '/manage-role',
  name: 'AdminRoleList',
  component: AdminRoleList,
  exact: true
},
{
  path: '/welcome-admin',
  name: 'WelcomeAdmin',
  component: WelcomeAdmin,
  exact: true
},
{
  path: '/marketing/new_campaign',
  name: "CreateNewCampaign",
  component: CreateNewCampaign,
  exact: true
},
{
  path: '/marketing/custome-template',
  name: "CustomTemplate",
  component: CustomTemplate,
  exact: true
},
{
  path: '/marketing/userbase-list/:ub_list_id',
  name: "CreateUserbaseList",
  component: CreateUserbaseList,
  exact: true
},
{
  path: '/manage-avatars',
  name: 'ManageAvatars',
  component: ManageAvatars,
  exact: true
},
{
  path: '/distributors/',
  name: "DISTRIBUTOR",
  component: DISTRIBUTOR,
  exact: true
},
{
  path: '/distributors/add',
  name: "ADD_DISTRIBUTOR",
  component: ADD_DISTRIBUTOR,
  exact: true
},
{
  path: '/distributors/detail/:unique_id',
  name: "DISTRIBUTOR",
  component: DISTRIBUTOR,
  exact: true
},
{
  path: '/affiliate',
  name: 'Affiliate',
  component: Affiliate,
  exact: true
},
{
  path: '/add-affiliate/:afphone',
  name: 'AddAffiliate',
  component: AddAffiliate,
  exact: true
},
{
  path: '/affiliates',
  name: 'AffiliateDashboard',
  component: AffiliateDashboard,
  exact: true
},
{
  path: '/affiliate/Report',
  name: 'AffiliateReport',
  component: AffiliateReport,
  exact: true
},
{
  path: '/new-affiliates',
  name: 'NewAffiliate',
  component: NewAffiliate,
  exact: true
},
{
  path: '/affiliates-users/:uid',
  name: 'AffiliateUsers',
  component: AffiliateUsers,
  exact: true
},
{
  path: '/private-contest/dashboard',
  name: 'PC_Dashboard',
  component: PC_Dashboard,
  exact: true
},
{
  path: '/private-contest/setting',
  name: 'PC_Setting',
  component: PC_Setting,
  exact: true
},
{
  path: '/cms/hub-page',
  name: 'HubPage',
  component: HubPage,
  exact: true
},
{
  path: '/cms/lobby',
  name: 'Lobby',
  component: Lobby,
  exact: true
},
{
  path: '/settings/minimum-withdrawal',
  name: 'MinimumWithdrawl',
  component: MinimumWithdrawl,
  exact: true
},
{
  path: '/settings/wallet',
  name: 'WalletSetting',
  component: WalletSetting,
  exact: true
},
{
  path: '/settings/email',
  name: 'EmailSetting',
  component: EmailSetting,
  exact: true
},
{
  path: '/settings/MobileApp',
  name: 'MobileApp',
  component: MobileApp,
  exact: true
},
{
  path: '/user_management/self-exclude',
  name: 'SelfExclude',
  component: SelfExclude,
  exact: true
},
{
  path: '/settings/prize_cron',
  name: 'PrizeCron',
  component: PrizeCron,
  exact: true
},
{
  path: '/coins/buy-coins',
  name: "BuyCoin",
  component: BuyCoin,
  exact: true
},
{
  path: '/coins/buy-coins-report/:coin_package_id',
  name: "BuyCoinUserReport",
  component: BuyCoinUserReport,
  exact: true
},
{
  path: '/app_config',
  name: 'AppSettingForm',
  component: AppSettingForm,
  exact: true
},
{
  path: '/report/participant',
  name: 'ParticipantReport',
  component: ParticipantReport,
},
{
  path: '/game_center/category',
  name: 'ContestCategory',
  component: ContestCategory,
  exact: true
},
{
  path: '/game_center/league-management',
  name: 'LeagueManagement',
  component: LeagueManagement,
  exact: true
},
{
  path: '/marketing/referral_setprize',
  name: "ReferralSetprize",
  component: ReferralSetprize,
  exact: true
}, {
  path: '/marketing/referral_leaderboard',
  name: "ReferralLeaderboard",
  component: ReferralLeaderboard,
  exact: true
},
{

  path: '/network-game',
  name: "NetworkGame",
  component: NetworkGame,
  exact: true
},
{
  path: '/network-game/details/:contest_unique_id/',
  name: "NetworkGameDetails",
  component: NetworkGameDetails,
  exact: true
},
{
  path: '/network-game/contest-report',
  name: "NetworkContestReport",
  component: NetworkContestReport,
  exact: true
},
{
  path: '/network-game/commission-history',
  name: "NetworkCommission",
  component: NetworkCommission,
  exact: true
},
{
  path: '/pickem/view-sports',
  name: "PickemAddSports",
  component: PickemAddSports,
  exact: true
},
{
  path: '/pickem/tournament-detail/:pid/:pctab',
  name: "PTDetail",
  component: PTDetail,
  exact: true
},
{
  path: '/pickem/tournament',
  name: "PTCreateTournament",
  component: PTCreateTournament,
  exact: true
},
{
  path: '/pickem/merchandise',
  name: "PTMerchandise",
  component: PTMerchandise,
  exact: true
},
  {
    path: '/pickem/league-management',
    name: 'LeagueManagement',
    component: PickemLeagueManagement,
    exact: true
  },
{
  path: '/erp/dashboard',
  name: "ERPDashbaord",
  component: ERPDashbaord,
  exact: true
},
{
  path: '/erp/custom',
  name: "ERPTransactions",
  component: ERPTransactions,
},
{
  path: '/system-users/add-ntwk-system-users/:league_id/:season_game_uid/:contest_unique_id',
  name: "AddNetwGameSysUser",
  component: AddNetwGameSysUser,
  exact: true
},
{
  path: '/leaderboard/referral',
  name: "Referral_Leaderboard",
  component: Referral_Leaderboard,
  exact: true
},
{
  path: '/leaderboard/depositors',
  name: "Depositor_Leaderboard",
  component: Depositor_Leaderboard,
  exact: true
},
{
  path: '/leaderboard/winnings',
  name: "Winning_Leaderboard",
  component: Winning_Leaderboard,
  exact: true
},
{
  path: '/leaderboard/feedback',
  name: "Feedback_Leaderboard",
  component: Feedback_Leaderboard,
  exact: true
},
{
  path: '/leaderboard/timespent',
  name: "TimeSpent_Leaderboard",
  component: TimeSpent_Leaderboard,
  exact: true
},
{
  path: '/game_center/tournament-detail/:tid/:pctab',
  name: "DfsTDetails",
  component: DfsTDetails,
  exact: true
},
{
  path: '/game_center/tournament',
  name: "DfsCreateTournament",
  component: DfsCreateTournament,
  exact: true
},
{
  path: '/leaderboard/topteams',
  name: "TopTeam_Leaderboard",
  component: TopTeam_Leaderboard,
  exact: true
},
{
  path: '/leaderboard/withdrawal',
  name: "Withdrawal_Leaderboard",
  component: Withdrawal_Leaderboard,
  exact: true
},
{
  path: '/report/match_report',
  name: "UserMatchReport",
  component: UserMatchReport,
  exact: true
},
//  {
//    path: '/accounting/dashboard',
//    name: "GSTDashboard",
//    component: GSTDashboard,
//    exact: true
//  },
{
  path: '/accounting/gst-reports',
  name: "GSTReports",
  component: GSTReports,
  exact: true
},
{
  path: '/settings/reward',
  name: "Reward",
  component: Reward,
  exact: true
},
{
  path: "/settings/what's-new",
  name: "WhatsNew",
  component: WhatsNew,
  exact: true
},
{
  path: "/settings/PaymentManagement",
  name: "paymentManagment",
  component: paymentManagment,
  exact: true
},
{
  path: '/xp/add-level',
  name: "AddLevel",
  component: AddLevel,
  exact: true
},
{
  path: '/xp/edit-level',
  name: "EditLevel",
  component: EditLevel,
  exact: true
},
{
  path: '/xp/rewards',
  name: "RewardsLevel",
  component: RewardsLevel,
  exact: true
},
{
  path: '/xp/activity',
  name: "RewardsLevel",
  component: ActivitiesLevel,
  exact: true
},
{
  path: '/xp/level-leaderboard',
  name: "LevelLeaderboard",
  component: LevelLeaderboard,
  exact: true
},
{
  path: '/xp/activity-leaderboard',
  name: "ActivitiesLeaderboard",
  component: ActivitiesLeaderboard,
  exact: true
},
{
  path: '/xp/userpointhistory/:uid',
  name: "UserPointHistory",
  component: UserPointHistory,
  exact: true
},
{
  path: '/copycreatecontesttemplate/:contest_template_id',
  name: 'CreateContestTemplateClone',
  component: CreateContestTemplate
},
{
  path: '/contest_template_detail/:contest_template_id',
  name: 'CONTEST_TEMPLATE_DETAILS',
  component: CONTEST_DETAILS
},
{
  path: '/pickem/setprize',
  name: "PTSetPrizes",
  component: PTSetPrizes,
  exact: true
},
{
  path: '/marketing/marketingleaderboard_setprize/:prize_id',
  name: 'SetprizeLeaderboard',
  component: SetprizeLeaderboard
},
{
  path: '/marketing/marketing_leaderBoard',
  name: 'Marketing Leaderboard',
  component: LeaderboardList
},
{
  path: '/marketing/marketingleaderboard-details/:prize_id',
  name: 'LeaderboardDetails',
  component: LeaderboardDetails
},
{
  path: '/stockfantasy/merchandise',
  name: 'SF_Merchandise',
  component: SF_Merchandise
},
{
  path: '/stockfantasy/category',
  name: 'SF_ContestCategory',
  component: SF_ContestCategory
},
{
  path: '/stockfantasy/contesttemplate',
  name: 'SF_ContestTemplate',
  component: SF_ContestTemplate
},
{
  path: '/stockfantasy/createcontesttemplate',
  name: 'SF_CreateTemplate',
  component: SF_CreateTemplate
},
{
  path: '/stockfantasy/copycreatecontesttemplate/:template_id',
  name: 'SF_CreateTemplate',
  component: SF_CreateTemplate
},
{
  path: '/stockfantasy/contest_detail/:id',
  name: "SF_ContestDetails",
  component: SF_ContestDetails,
  exact: true
},
{
  path: '/stockfantasy/contest_template_detail/:contest_template_id',
  name: 'SF_ContestDetails',
  component: SF_ContestDetails
},
{
  path: '/stockfantasy/stock',
  name: 'SF_ManageStock',
  component: SF_ManageStock
},
{
  path: '/stockfantasy/fixture',
  name: 'SF_DSF',
  component: SF_DSF
},
{
  path: '/stockfantasy/verify-stocks/:category/:activeTab/:fxvalue/:collection_id/:fxname',
  name: 'SF_VerifyStocks',
  component: SF_VerifyStocks
},
{
  path: '/stockfantasy/createtemplatecontest/:category/:activeTab/:fxvalue/:collection_id',
  name: 'SF_CreateTemplateContest',
  component: SF_CreateTemplateContest
}, {
  path: '/stockfantasy/fixturecontest/:category/:activeTab/:collection_id',
  name: 'SF_FixtureContest',
  component: SF_FixtureContest,
  exact: true
}, {
  path: '/stockfantasy/createcontest/:category/:activeTab/:fxvalue/:collection_id',
  name: 'SF_CreateContest',
  component: SF_CreateContest,
},
{
  path: '/report/stock_report',
  name: "SF_UserContestReport",
  component: SF_UserContestReport,
  exact: true
},
{
  path: '/stockfantasy/nsestats/:category/:activeTab/:collection_id',
  name: "SF_Nse_Stats",
  component: SF_Nse_Stats,
  exact: true
},
{
  path: '/stockfantasy/contestlist',
  name: "SF_ContestList",
  component: SF_ContestList,
  exact: true
},
{
  path: '/stockfantasy/manual_close',
  name: "SF_MatchCloser",
  component: SF_MatchCloser,
  exact: true
},
{
  path: '/game_center/settings/booster',
  name: 'BoosterList',
  component: BoosterList
},
{
  path: '/contest/booster/:collection_master_id/:season_id/:tab/:fromfixture',
  name: 'ApplyBooster',
  component: ApplyBooster
},

{
  path: '/network-game/contest-details',
  name: "ContestDetailReport",
  component: ContestDetailReport,
  exact: true
},
{
  path: '/user_management/viewrookie',
  name: "ViewRookie",
  component: ViewRookie,
  exact: true
},
{
  path: '/user_management/all_rookie',
  name: "AllRookieUser",
  component: AllRookieUser,
},
{
  path: '/settings/banned-states',
  name: 'BannedStates',
  component: BannedStates,
  exact: true
},
{
  path: '/coins/subscription',
  name: "Subscription",
  component: Subscription,
  exact: true
},
{
  path: '/equitysf/merchandise',
  name: 'ESF_Merchandise',
  component: ESF_Merchandise
}, {
  path: '/equitysf/category',
  name: 'ESF_ContestCategory',
  component: ESF_ContestCategory
}, {
  path: '/equitysf/contesttemplate',
  name: 'ESF_ContestTemplate',
  component: ESF_ContestTemplate
}, {
  path: '/equitysf/createcontesttemplate',
  name: 'ESF_CreateTemplate',
  component: ESF_CreateTemplate
}, {
  path: '/equitysf/copycreatecontesttemplate/:template_id',
  name: 'ESF_CreateTemplate',
  component: ESF_CreateTemplate
}, {
  path: '/equitysf/contest_detail/:id',
  name: "ESF_ContestDetails",
  component: ESF_ContestDetails,
  exact: true
}, {
  path: '/equitysf/contest_template_detail/:contest_template_id',
  name: 'ESF_ContestDetails',
  component: ESF_ContestDetails
}, {
  path: '/equitysf/stock',
  name: 'ESF_ManageStock',
  component: ESF_ManageStock
}, {
  path: '/equitysf/fixture',
  name: 'ESF_DSF',
  component: ESF_DSF
}, {
  path: '/equitysf/verify-stocks/:category/:activeTab/:fxvalue/:collection_id/:fxname',
  name: 'ESF_VerifyStocks',
  component: ESF_VerifyStocks
}, {
  path: '/equitysf/createtemplatecontest/:category/:activeTab/:fxvalue/:collection_id',
  name: 'ESF_CreateTemplateContest',
  component: ESF_CreateTemplateContest
}, {
  path: '/equitysf/fixturecontest/:category/:activeTab/:collection_id',
  name: 'ESF_FixtureContest',
  component: ESF_FixtureContest,
  exact: true
}, {
  path: '/equitysf/createcontest/:category/:activeTab/:fxvalue/:collection_id',
  name: 'ESF_CreateContest',
  component: ESF_CreateContest,
}, {
  path: '/report/stock_report',
  name: "ESF_UserContestReport",
  component: ESF_UserContestReport,
  exact: true
}, {
  path: '/equitysf/nsestats/:category/:activeTab/:collection_id',
  name: "ESF_Nse_Stats",
  component: ESF_Nse_Stats,
  exact: true
}, {
  path: '/equitysf/contestlist',
  name: "ESF_ContestList",
  component: ESF_ContestList,
  exact: true
}, {
  path: '/equitysf/manual_close',
  name: "ESF_MatchCloser",
  component: ESF_MatchCloser,
  exact: true
},
{
  path: '/coins/quiz/create-quiz/:edit',
  name: "CreateQuiz",
  component: CreateQuiz,
  exact: true
},
{
  path: '/coins/quiz/questions',
  name: "QuizQuestionList",
  component: QuizQuestionList,
  exact: true
},
{
  path: '/coins/quiz/dashboard',
  name: "QuizDashboard",
  component: QuizDashboard,
  exact: true
},
{
  path: '/quiz/users/',
  name: "QuizViewAllUser",
  component: QuizViewAllUser,
  exact: true
},
{
  path: '/coins/reports/',
  name: "QuizReportsList",
  component: QuizReportsList,
  exact: true
},
{
  path: '/quiz/spinwheel-user/:filter/:fdate/:tdate',
  name: "QuizSpinWheelLrdBrd",
  component: QuizSpinWheelLrdBrd,
  exact: true
},
{
  path: '/quiz/app-user/:tab/:fdate/:tdate',
  name: "QuizAppLrdBrd",
  component: QuizAppLrdBrd,
  exact: true
},
{
  path: '/coins/quiz/reward-dashboard/',
  name: "QuizRewardDashboard",
  component: QuizRewardDashboard,
  exact: true
},
{
  path: '/stockpredict/create-candle/:collection_id',
  name: "SP_CreateCandle",
  component: SP_CreateCandle,
  exact: true
},
{
  path: '/stockpredict/createtemplatecontest/:activeTab/:collection_id',
  name: "SP_CreateTemplateContest",
  component: SP_CreateTemplateContest,
  exact: true
},
{
  path: '/stockpredict/fixture',
  name: "SP_DSF",
  component: SP_DSF,
  exact: true
},
{
  path: '/stockpredict/fixturecontest/:activeTab/:collection_id',
  name: "SP_FixtureContest",
  component: SP_FixtureContest,
  exact: true
},
{
  path: '/stockpredict/createcontest/:activeTab/:collection_id',
  name: 'SP_CreateContest  ',
  component: SP_CreateContest,
},
{
  path: '/stockpredict/contesttemplate',
  name: 'SPContestTemplate',
  component: SPContestTemplate
},
{
  path: '/stockpredict/copycreatecontesttemplate/:template_id',
  name: 'SP_CreateTemplate',
  component: SP_CreateTemplate
},
{
  path: '/stockpredict/createcontesttemplate',
  name: 'SP_CreateTemplate',
  component: SP_CreateTemplate
},
{
  path: '/stockpredict/stock',
  name: 'SP_ManageStock',
  component: SP_ManageStock
},
{
  path: '/stockpredict/manual_close',
  name: 'SP_MatchCloser',
  component: SP_MatchCloser
},
{
  path: '/stockpredict/merchandise',
  name: 'SP_Merchandise',
  component: SP_Merchandise
},
{
  path: '/stockpredict/contestlist',
  name: 'SP_ContestList',
  component: SP_ContestList
},
{
  path: '/stockpredict/contest_detail/:id',
  name: "SP_ContestDetails",
  component: SP_ContestDetails,
  exact: true
},
{
  path: '/stockpredict/contest_template_detail/:contest_template_id',
  name: 'SP_ContestDetails',
  component: SP_ContestDetails
},
{
  path: '/stockpredict/nsestats/:activeTab/:collection_id',
  name: "SP_Nse_Stats",
  component: SP_Nse_Stats,
  exact: true
},
{
  path: '/user_management/h2h/dashboard/',
  name: "h2hDashboard",
  component: h2hDashboard,
  exact: true
},
{
  path: '/user_management/h2h/contest',
  name: "ViewAllH2hContest",
  component: ViewAllH2hContest,
  exact: true
},
{
  path: '/user_management/h2h/user',
  name: "H2hContestUser",
  component: H2hContestUser,
  exact: true
},
{
  path: '/user_management/h2h/h2hcms/',
  name: "H2HCms",
  component: H2HCms,
  exact: true
},
{
  path: '/livefantasy/merchandise/',
  name: "LF_Merchandise",
  component: LF_Merchandise,
  exact: true
},
{
  path: '/livefantasy/contesttemplate/',
  name: "LF_Contesttemplate",
  component: LF_Contesttemplate,
  exact: true
},
{
  path: '/livefantasy/createcontesttemplate',
  name: 'LF_Createtemplate',
  component: LF_Createtemplate
},
{
  path: '/livefantasy/copycreatecontesttemplate/:contest_template_id',
  name: 'LF_Createtemplate',
  component: LF_Createtemplate
},
{
  path: '/livefantasy/fixture',
  name: 'LF_DFS',
  component: LF_DFS
},
{
  path: '/livefantasy/oversetup/:league_id/:season_game_uid',
  name: 'LF_OverSetUp',
  component: LF_OverSetUp
},
{
  //  path: '/livefantasy/createtemplatecontest/:league_id/:season_game_uid/:tab',
  path: '/livefantasy/createtemplatecontest/:league_id/:season_game_uid/:collection_id/:tab',
  name: 'LF_Createtemplatecontest',
  component: LF_Createtemplatecontest,
  exact: true
},
{
  path: '/livefantasy/overdetails/:league_id/:season_game_uid/:tab',
  name: 'LF_OverDetails',
  component: LF_OverDetails,
  exact: true
},
{
  path: '/livefantasy/fixturecontest/:league_id/:season_game_uid/:tab/:collection_id',
  name: 'LF_Fixturecontest',
  component: LF_Fixturecontest,
  exact: true
},
{
  path: '/livefantasy/createcontest/:league_id/:season_game_uid/:collection_id',
  name: 'LF_Createcontest',
  component: LF_Createcontest,
  exact: true
},
{
  path: '/livefantasy/contestlist',
  name: 'LF_Contestlist',
  component: LF_Contestlist,
  exact: true
},
{
  path: '/livefantasy/contest_detail/:id',
  name: "LF_ContestDetails",
  component: LF_ContestDetails,
  exact: true
},
{
  path: '/livefantasy/contest_template_detail/:contest_template_id',
  name: 'CONTEST_TEMPLATE_DETAILS',
  component: LF_ContestDetails
},
{
  path: '/livefantasy/season_schedule/:leagueid/:gameid/:sportsid/:tab',
  name: 'LF_Seasonschedule',
  component: LF_Seasonschedule
},
{
  path: '/livefantasy/category',
  name: 'LF_ContestCategory',
  component: LF_ContestCategory
},
{
  path: '/livefantasy/update-score/:league_id/:season_game_uid/:tab/:inning/:c_id/:over',
  name: 'LF_UpdateScore',
  component: LF_UpdateScore,
  exact: true
},
{
  path: '/livefantasy/pc-dashboard',
  name: 'LF_PC_Dashboard',
  component: LF_PC_Dashboard,
  exact: true
},
{
  path: '/livefantasy/pc-setting',
  name: 'LF_PC_SETTING',
  component: LF_PC_Setting,
  exact: true
},
{
  path: '/livefantasy/user-money-paid',
  name: 'LF_USER_MONEY_PAID',
  component: LF_USER_MONEY_PAID,
  exact: true
},
{
  path: '/livefantasy/user-contest-report',
  name: 'LF_USER_CONTEST',
  component: LF_USER_CONTEST,
  exact: true
},
{
  path: '/system-users/manage-system-users/:collection_master_id/:league_id/:season_id',
  name: "ManageSystemUser",
  component: ManageSystemUser,
  exact: true
},
{
  path: '/livestockfantasy/create-candle/:collection_id',
  name: "LSF_CreateCandle",
  component: LSF_CreateCandle,
  exact: true
},
{
  path: '/livestockfantasy/createtemplatecontest/:activeTab/:collection_id',
  name: "LSF_CreateTemplateContest",
  component: LSF_CreateTemplateContest,
  exact: true
},
{
  path: '/livestockfantasy/fixture',
  name: "LSF_DSF",
  component: LSF_DSF,
  exact: true
},
{
  path: '/livestockfantasy/fixturecontest/:activeTab/:collection_id',
  name: "LSF_FixtureContest",
  component: LSF_FixtureContest,
  exact: true
},
{
  path: '/livestockfantasy/createcontest/:activeTab/:collection_id',
  name: 'LSF_CreateContest  ',
  component: LSF_CreateContest,
},
{
  path: '/livestockfantasy/contesttemplate',
  name: 'LSFContestTemplate',
  component: LSFContestTemplate
},
{
  path: '/livestockfantasy/copycreatecontesttemplate/:template_id',
  name: 'LSF_CreateTemplate',
  component: LSF_CreateTemplate
},
{
  path: '/livestockfantasy/createcontesttemplate',
  name: 'LSF_CreateTemplate',
  component: LSF_CreateTemplate
},
{
  path: '/livestockfantasy/stock',
  name: 'LSF_ManageStock',
  component: LSF_ManageStock
},
{
  path: '/livestockfantasy/manual_close',
  name: 'LSF_MatchCloser',
  component: LSF_MatchCloser
},
{
  path: '/livestockfantasy/merchandise',
  name: 'LSF_Merchandise',
  component: LSF_Merchandise
},
{
  path: '/livestockfantasy/contestlist',
  name: 'LSF_ContestList',
  component: LSF_ContestList
},
{
  path: '/liveStockFantasy/contest_template_detail/:contest_template_id',
  name: "LSF_ContestDetails",
  component: LSF_ContestDetails,
},
{
  path: '/LiveStockFantasy/contest_detail/:id',
  name: "LSF_ContestDetails",
  component: LSF_ContestDetails,
  exact: true
},
{
  path: '/livestockfantasy/nsestats/:activeTab/:collection_id',
  name: "LSF_NseStats",
  component: LSF_NseStats,
  exact: true
},



//pickfantasy
{
  path: '/picksfantasy/fixture',
  name: "Picks Fantasy",
  component: PicksFantasy,
  exact: true
},
{
  path: '/picksfantasy/contestdashboard',
  name: "PFContestDashboard",
  component: PFContestDashboard,
  exact: true
},
{
  path: '/picksfantasy/team_player_management',
  name: "Team Player Management",
  component: TeamPlayerManagement,
  exact: true
},
{
  path: '/picksfantasy/leagues',
  name: "Leagues / Player",
  component: Leagues,
  exact: true
},
{
  path: '/picksfantasy/contest-template',
  name: "Contest Template",
  component: PFContestTemplate,
  exact: true
},
{
  path: '/picksfantasy/createcontesttemplate',
  name: 'PFCreateTemplate',
  component: PFCreateTemplate,
  exact: true
},
{
  path: '/picksfantasy/create-contest/:season_id',
  name: 'PFCreateContest',
  component: PFCreateContest,
  exact: true
},
{
  path: '/picksfantasy/contest_template_detail/:contest_template_id',
  name: 'PFContestTemplateDetail',
  component: PFContestTemplateDetail
},
{
  path: '/picksfantasy/publish_match/:league_id/:season_id',
  name: 'PFAddMatch',
  component: PFAddMatch
},
// {
//   path: '/picksfantasy/publish_match/:league_id/:season_game_uid',
//   name: 'PFAddMatch',
//   component: PFAddMatch
// },
{
  path: '/picksfantasy/createtemplatecontest/:league_id/:season_id',
  name: 'PFCreatetemplatecontest',
  component: PFCreatetemplatecontest
},
{
  path: '/picksfantasy/copycreatecontesttemplate/:contest_template_id',
  name: 'CreateContestTemplateClone',
  component: CopyCreateContestTemplate
},
{
  path: '/picksfantasy/create-form-contest/:league_id/:season_id',
  name: 'CreateContestTemplateClone',
  component: CreateFormContest
},
{
  path: '/picksfantasy/contest-list/:league_id/:season_id',
  name: 'PF_ContestList',
  component: PF_ContestList
},
{
  path: '/picksfantasy/contest-report',
  name: 'PFContestReport',
  component: PFContestReport
},
{
  path: '/picksfantasy/template_detail/:contest_template_id',
  name: 'Template Details',
  component: PFTemplateDetails,

  exact: true
},
{
  path: '/picksfantasy/contest_detail/:contest_unique_id',
  name: 'PFContestDetail',
  component: PFContestDetail,
  exact: true
},
{
  path: '/accounting/tds-reports',
  name: "TDSAccounting",
  component: TDSAccounting,
  exact: true
},
{
  path: '/accounting/tds-document',
  name: "TDSDocument",
  component: TDSDocument,
  exact: true
},
{
  path: '/manual_payment/payment-setup',
  name: "Payment Setup",
  component: PaymentSetup,
  exact: true
},
{
  path: '/manual_payment/reports',
  name: "Reports",
  component: ReportMP,
  exact: true
},
 {
    path: '/opinionTrading/league-managment',
    name: 'League Management',
    component: TradeLeagueManagement,
    exact: true
  },
   {
    path: '/opinionTrading/Teams',
    name: 'Team Management',
    component: TradeTeamManagement,
    exact: true
  },

   {
    path: '/opinionTrading/Template',
    name: 'Template',
    component: TradeTemplate,
    exact: true
  },
  {
  path: '/opinionTrading/fixture',
  name: 'OT_DFS',
  component: OT_DFS
},

{
  path: '/opinionTrading/add_question/:league_id/:season_id/:tab',
  name: 'OTAddMatch',
  component: OTAddMatch
},

{
  path: '/opinionTrading/report',
  name: 'OT_REPORT',
  component: OT_REPORT
},

{
  path: '/opinionTrading/publish_match/:league_id/:season_id/:tab',
  name: 'OTPUBMatch',
  component: OTPUBMatch
},





];

export default routes;
