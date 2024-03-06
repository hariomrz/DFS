import React, { Component } from 'react';
import Loadable from "react-loadable";
import CustomLoader from "../helper/CustomLoader";
import { Utilities, getRightFlow, _Map } from "../Utilities/Utilities";

import { MyWallet, Transaction, AddFunds, Withdraw, PaymentMethod,BuyCoins } from "../Component/Finance";

import { LandingScreen, TermsCondition, RulesScoring, PrivacyPolicy, FAQ, AboutUs, HowToPlay, ContactUs, Offers,Legality, HowItWorks, RefundPolicy,NewRulesScoring, RookieFeature, DownloadAppPage } from "../Component/StaticPages";

import { ForgotEmailPassword, ResetPassword, ChangePassword, EmailLogin, SetPassword, VerifyEmail, UpdateMobileNo, EnterPassword } from "../Component/OnBoarding/EmailFlow";

import { MobileLogin, VerifyMobile, PickUsername, ReferralCode, PickEmail, PickMobileNo, } from "../Component/OnBoarding/MobileFlow";
import { Profile, ProfileEdit, VerifyAccount, PanVerification, BankVerification } from "../Component/Profile";

import Contest from "../views/Contest";
import createContest from "../views/CreateContest";
import LeaderBoard from "../views/Leaderboard";
import UserNotification from '../views/Notification';
import RefferalSystem from "../views/ReferralSystem";
import HaveALeagueCodeClass from '../views/HaveALeagueCodeClass';
import MyTeams from "../views/MyTeams";
import { MultiGameContest } from '../Component/MultiGameModule';
import { FreeToPlayList, AllPrizes, UserLeaguePoints, FreeToPlayLobby } from "../Component/FreeToPlayModule";
import AllLeagueList from '../Component/FreeToPlayModule/AllLeagueList';
import CompletedLeagueList from '../Component/FreeToPlayModule/CompletedLeagueList';
import LeagueDetails from '../Component/FreeToPlayModule/LeagueDetails';
import LeaderBoardFreeToPlay from '../Component/FreeToPlayModule/LeaderBoardFreeToPlay';
import LeagueSheduledFixture from '../Component/FreeToPlayModule/LeagueSheduledFixture';
import WhyPrivateContest from '../views/PrivateContest/WhyPrivateContest';
import PrivateContestParent from '../views/PrivateContest/PrivateContestParent';
import GroupChat from '../views/PrivateContest/GroupChat';
import SharePrivateContest from '../views/PrivateContest/SharePrivateContest';

import { AffiliateProgram } from '../Component/BecomeAffiliate';
import {AffiliateRequest} from '../Component/BecomeAffiliate';
import SelfExclusion from '../views/SelfExclusion';
import ReferalLeaderBoard from '../views/ReferalLeaderBoard';

import SelectCaptainList from "../views/SelectCaptainList";
import Roster from "../views/Roster";
import ContestListing from "../views/ContestListing";
import ReferFriend from '../views/ReferFriend';
import ReferFriendStandard from '../views/ReferFriendStandard';
import Dashboard from "../views/Dashboard";
import TeamComparison from '../views/Leaderboard/TeamComparison';
import {
    DFSTourFixtures,
    DFSTourList,
    DFSTourLeaderboard,
    DFSTourLiveFixtureList,
    DFSTourShare,
    DFSTourRoster,
    DFSTourSelectCaptainList,
    DFSTourFieldView,
    DFSTourFixtureLeaderboard
} from "../Component/DFSTournament";
import DFSUserTourHistory from "../Component/DFSTournament/DFSTourUserHistory";

import {
    XPPoints,
    XPLevels,
    XPPointsHistory,
    UserPublicProfile
} from "../Component/XPModule";
import ScoreCard from "../views/Leaderboard/ScoreCard";
import Stats from "../views/Leaderboard/Stats";
import LBAllPrizes from "../Component/FantasyRefLeaderboard/LBAllPrizes";
import LBLeaguePoints from "../Component/FantasyRefLeaderboard/LBLeaguePoints";

import MatchScoreCardStats from "../views/Leaderboard/MatchScoreCardStats";
import FantasyLeagueLeaderboard from '../Component/FantasyRefLeaderboard/FantasyLeagueLeaderboard';
import { StockContestListing, StockFStatistics, StockFMyWatchList, StockRoster, StockSelectCaptainList, StockShreContest, StockTeamComparison } from '../Component/StockFantasy';
import { StockContestListingEquity,StockRosterEquity, StockSelectCaptainListEquity,StockTeamCompEquity,StockShreContestEquity } from '../Component/StockFantasyEquity';
import { ApplyBooster } from '../Component/Booster';
import {BenchSelection,BenchPlayerList} from "../Component/Bench";
import CryptoVerifcation from '../Component/Profile/CryptoVerifcation';

import GuruRoster from '../Component/Guru/GuruRoster';
import {LFLeaderBoard, LFPrivateContestParent, LivefantasyCenter, LiveFantasyContest, LiveFantasyContestListing,LivefantasyOverResult} from '../Component/LiveFantasy';
import {SPRoster,SPStockBid,SPTeamComp,SPScoreCalc} from '../Component/StockPrediction';
import Feed from '../Component/Feed/Feed';
import FantasyRefLeaderboard from '../Component/FantasyRefLeaderboard/FantasyRefLeaderboard';
import GameCenter from '../Component/GameCenterModule/GameCenter';
import LFSharePrivateContest from '../Component/LiveFantasy/LFSharePrivateContest';
import { Login, Signup } from '../Component/OnBoarding/EmailFlowSingleStep';
import { MyContest } from '../Component/MyContest';
import { DMContest } from '../Component/DFSWithMultigame';

function LoadingComponent(showHeader) {
    return <div className="web-container bg-white">{showHeader && <div className="app-header-style" />}<CustomLoader /></div>;
}
const YouRInQueue = Loadable({
    loader: () => import('../Component/CustomComponent/YouRInQueue'),
    delay: 0,
    loading: ()=> <div />
});
const EditReferralCode = Loadable({
    loader: () => import('../views/EditReferralCode'),
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const FieldView = Loadable({
    loader: () => import('../views/FieldView'),
    delay: 0,
    loading: ()=> <div />
});
const DFSTourFieldview = Loadable({
    loader: () => import('../Component/DFSTournament/DFSTourFieldview'),
    delay: 0,
    loading: ()=> <div />
});
const GuruFiledView = Loadable({
    loader: () => import('../Component/Guru/GuruFiledView'),
    delay: 0,
    loading: ()=> <div />
});
const SportsHub = Loadable({
    loader: () => {
        if (Utilities.getMasterData().sports_hub && Utilities.getMasterData().sports_hub.length > 1)
            return import('../Component/SportsHub/SportsHub')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const EarnCoins = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/CoinsModule/EarnCoins')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

const RedeemCoins = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/CoinsModule/RedeemCoins')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const WhatIsNew = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/CoinsModule/WhatIsNew')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

const FeedbackQA = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/CoinsModule/FeedbackQA')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

const PredictionShare = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/PredictionModule/SharePrediction')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const PredictionParticipants = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/PredictionModule/PredictionParticipants')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

const ShareOpenPredictor = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/OpenPredictorModule/ShareOpenPredictor')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const OpenPredictorParticipants = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/OpenPredictorModule/OpenPredictorParticipants')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

const ShareFPPOpenPredictor = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/OpenPredictorFPPModule/ShareFPPOpenPredictor')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});
const OpenPredictorFPPParticipants = Loadable({
    loader: () => {
        if (Utilities.getMasterData().a_coin)
            return import('../Component/OpenPredictorFPPModule/OpenPredictorFPPParticipants')
        else
            return import('../views/PageNotFound/PageNoFound')
    },
    delay: 0,
    loading: ()=> LoadingComponent(true)
});

// const PickemShare = Loadable({
//     loader: () => {
//         if (Utilities.getMasterData().a_coin)
//             return import('../Component/Pickem/NewPickemShare')
//         else
//             return import('../views/PageNotFound/PageNoFound')
//     },
//     delay: 0,
//     loading: ()=> LoadingComponent(true)
// });

// const PickemParticipants = Loadable({
//     loader: () => {
//         if (Utilities.getMasterData().a_coin)
//             return import('../Component/Pickem/NewPickemParticipants')
//         else
//             return import('../views/PageNotFound/PageNoFound')
//     },
//     delay: 0,
//     loading: ()=> LoadingComponent(true)
// });

const Routes = [
    {
        path: "/you-are-in-queue",
        name: "YouRInQueue",
        component: YouRInQueue,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/lobby",
        name: "Dashboard",
        component: Dashboard,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/",
        name: "LandingScreen",
        component: LandingScreen,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/lineup/:lineupKey",
        name: "Roster",
        component: Roster,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/field-view/:fieldKey",
        name: "FieldView",
        component: FieldView,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/field-view-guru/:fieldKey",
        name: "GuruFiledView",
        component: GuruFiledView,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/select-captain/:captainKey",
        name: "SelectCaptainList",
        component: SelectCaptainList,
        exact: true,
        RouteType: 'SequenceRoute'
    }, 
    // Onboarding
    {
        path: "/signup",
        name: "signup",
        dynamic: true,
        component: "signup",
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/login",
        name: "login",
        dynamic: true,
        component: "login",
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/set-password",
        name: "SetPassword",
        component: SetPassword,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/password",
        name: "EnterPassword",
        component: EnterPassword,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/verify",
        name: "verify",
        dynamic: true,
        component: "verify",
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/referral",
        name: "ReferralCode",
        component: ReferralCode,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/pick-username",
        name: "PickUsername",
        component: PickUsername,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/email",
        name: "PickEmail",
        component: PickEmail,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/enter-email",
        name: "ForgotEmailPassword",
        component: ForgotEmailPassword,
        exact: true,
        RouteType: 'OnboardingRoute'
    },
    {
        path: "/forgot-password",
        name: "ResetPassword",
        component: ResetPassword,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/pick-mobile",
        name: "pick-mobile",
        dynamic: true,
        component: 'pickmobile',
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/contest-listing/:collection_master_id/:myKey",
        name: "ContestListing",
        component: ContestListing,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/contest/:contest_unique_id",
        name: "Contest",
        component: Contest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/multigame-contest/:contest_unique_id",
        name: "MultiGameContest",
        component: MultiGameContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/multi-with-dfs/:contest_unique_id",
        name: "DMContest",
        component: DMContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/my-teams/:collection_master_id/:myKey",
        name: "MyTeams",
        component: MyTeams,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/terms-condition",
        name: "TermsCondition",
        component: TermsCondition,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/legality",
        name: "Legality",
        component: Legality,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/rules-and-scoring",
        name: "RulesScoring",
        component: RulesScoring,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/new-rules-and-scoring",
        name: "NewRulesScoring",
        component: NewRulesScoring,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/offers",
        name: "Offers",
        component: Offers,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/privacy-policy",
        name: "PrivacyPolicy",
        component: PrivacyPolicy,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/faq",
        name: "FAQ",
        component: FAQ,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/apk",
        name: "DownloadAppPage",
        component: DownloadAppPage,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/app",
        name: "DownloadAppPage",
        component: DownloadAppPage,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/contact-us",
        name: "ContactUs",
        component: ContactUs,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/about-us",
        name: "AboutUs",
        component: AboutUs,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/how-to-play",
        name: "HowToPlay",
        component: HowToPlay,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/refund-policy",
        name: "RefundPolicy",
        component: RefundPolicy,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/how-it-works",
        name: "HowItWorks",
        component: HowItWorks,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/private-contest",
        name: "HaveALeagueCodeClass",
        component: HaveALeagueCodeClass,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/payment-method",
        name: "PaymentMethod",
        component: PaymentMethod,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/prediction-details/:season_game_uid/:prediction_master_id",
        name: "PredictionShare",
        component: PredictionShare,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/prediction/participants/:prediction_master_id",
        name: "PredictionParticipants",
        component: PredictionParticipants,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/open-predictor-details/:category_id/:prediction_master_id",
        name: "ShareOpenPredictor",
        component: ShareOpenPredictor,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/open-predictor/participants/:prediction_master_id",
        name: "OpenPredictorParticipants",
        component: OpenPredictorParticipants,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/open-predictor-leaderboard-details/:category_id/:prediction_master_id",
        name: "ShareFPPOpenPredictor",
        component: ShareFPPOpenPredictor,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/open-predictor-leaderboard/participants/:prediction_master_id",
        name: "OpenPredictorFPPParticipants",
        component: OpenPredictorFPPParticipants,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/self-exclusion",
        name: "SelfExclusion",
        component: SelfExclusion,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/team-comparison",
        name: "TeamComparison",
        component: TeamComparison,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/leaderboard",
        name: "Dashboard",
        component: Dashboard,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/leaderboards",
        name: "FantasyRefLeaderboard",
        component: FantasyRefLeaderboard,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/group-chat/:groupId",
        name: "GroupChat",
        component: GroupChat,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/my-contests",
        page_key: 'my-contests',
        name: "MyContest",
        component: MyContest,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/my-profile",
        page_key: 'my-profile',
        name: "Profile",
        component: Profile,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/feed",
        page_key: 'feed',
        name: "Feed",
        component: Feed,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/notification",
        page_key: 'notification',
        name: "UserNotification",
        component: UserNotification,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/refer-friend",
        page_key: 'refer-friend',
        name: "ReferFriendStandard",
        component: ReferFriendStandard,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/refer-friend-leaderboard",
        page_key: 'refer-friend-leaderboard',
        name: "ReferalLeaderBoard",
        component: ReferalLeaderBoard,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/my-wallet",
        page_key: 'my-wallet',
        name: "MyWallet",
        component: MyWallet,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/buy-coins",
        page_key: 'my-wallet',
        name: "BuyCoins",
        component: BuyCoins,
        exact: true,
        RouteType: 'DashboardPRoute'
    },
    {
        path: "/earn-coins",
        name: "earn-coins",
        component: <></>,
        exact: true,
        RouteType: 'CoinRoute'
    },
    {
        path: "/rewards",
        name: "RedeemCoins",
        component: RedeemCoins,
        exact: true,
        RouteType: 'CoinRoute'
    },
    {
        path: "/feedback",
        name: "FeedbackQA",
        component: FeedbackQA,
        exact: true,
        RouteType: 'CoinRoute'
    },
    {
        path: "/what-is-new",
        name: "WhatIsNew",
        component: WhatIsNew,
        exact: true,
        RouteType: 'CoinRoute'
    },
    {
        path: "/game-center/:collection_master_id",
        name: "GameCenter",
        component: GameCenter,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/edit-profile",
        name: "ProfileEdit",
        component: ProfileEdit,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/create-contest",
        name: "createContest",
        component: createContest,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/:sportsId/leaderboard",
        name: "LeaderBoard",
        component: LeaderBoard,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/transactions",
        name: "Transaction",
        component: Transaction,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/add-funds",
        name: "AddFunds",
        component: AddFunds,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/withdraw",
        name: "Withdraw",
        component: Withdraw,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/more",
        name: "Dashboard",
        component: Dashboard,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/change-password",
        name: "ChangePassword",
        component: ChangePassword,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/verify-account",
        name: "VerifyAccount",
        component: VerifyAccount,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/pan-verification",
        name: "PanVerification",
        component: PanVerification,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/bank-verification",
        name: "BankVerification",
        component: BankVerification,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/referral-system",
        name: "RefferalSystem",
        component: RefferalSystem,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/edit-referral-code",
        name: "EditReferralCode",
        component: EditReferralCode,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/affiliate-program",
        name: "AffiliateProgram",
        component: AffiliateProgram,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/affiliate-request",
        name: "AffiliateRequest",
        component: AffiliateRequest,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/crypto-verification",
        name: "CryptoVerifcation",
        component: CryptoVerifcation,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/:sportsId/free-to-play/:collection_master_id/:myKey/:game_type/:season_game_uid/:contest_id",
        name: "FreeToPlayList",
        component: FreeToPlayList,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/all-prizes/:mini_league_uid/:isMiniLeaguePrize",
        name: "AllPrizes",
        component: AllPrizes,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/mini-league-fixture/:mini_league_id/:league_name",
        name: "FreeToPlayLobby",
        component: FreeToPlayLobby,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/sheduled-fixture/:mini_league_uid/:mini_league_name",
        name: "LeagueSheduledFixture",
        component: LeagueSheduledFixture,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/user-league-points",
        name: "UserLeaguePoints",
        component: UserLeaguePoints,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/all-leagues",
        name: "AllLeagueList",
        component: AllLeagueList,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/completed-leagues",
        name: "CompletedLeagueList",
        component: CompletedLeagueList,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/league-details",
        name: "LeagueDetails",
        component: LeagueDetails,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/mini-league-leader-board",
        name: "LeaderBoardFreeToPlay",
        component: LeaderBoardFreeToPlay,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/sports-hub", // SportsHub && 
        name: "SportsHub",
        component: SportsHub,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/:collection_master_id/:fixterDetail/private-contest-banner",
        name: "WhyPrivateContest",
        component: WhyPrivateContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/:collection_master_id/:fixterDetail/private-contest",
        name: "PrivateContestParent",
        component: PrivateContestParent,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/:collection_master_id/:fixterDetail/lf-private-contest",
        name: "LFPrivateContestParent",
        component: LFPrivateContestParent,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/share-private-contest",
        name: "SharePrivateContest",
        component: SharePrivateContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/lf-share-private-contest",
        name: "LFSharePrivateContest",
        component: LFSharePrivateContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/pl/lineup",
        name: "GuruRoster",
        component: GuruRoster,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/:sportsId/tournament/:tournamentId/:leagueId/:leaguename/:date",
        name: "DFSTourFixtures",
        component: DFSTourFixtures,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/tournament-list",
        name: "DFSTourList",
        component: DFSTourList,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/tournament-leaderboard/:league_name",
        name: "DFSTourLeaderboard",
        component: DFSTourLeaderboard,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/tournament-fixture-leaderboard/:league_name/:tournament_season_id",
        name: "DFSTourFixtureLeaderboard",
        component: DFSTourFixtureLeaderboard,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/tournament/live-fixture-list/:tournament_id/:leaguename/:date",
        name: "DFSTourLiveFixtureList",
        component: DFSTourLiveFixtureList,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/tournament/:tournament_unique_id",
        name: "DFSTourShare",
        component: DFSTourShare,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/tournament/user/history/:user_id",
        name: "DFSUserTourHistory",
        component: DFSUserTourHistory,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/tournament/lineup/:lineupKey",
        name: "DFSTourRoster",
        component: DFSTourRoster,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/tournament/select-captain/:captainKey",
        name: "DFSTourSelectCaptainList",
        component: DFSTourSelectCaptainList,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/tournament/field-view/:fieldKey",
        name: "DFSTourFieldView",
        component: DFSTourFieldView,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/experience-points",
        name: "XPPoints",
        component: XPPoints,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/experience-points-levels",
        name: "XPLevels",
        component: XPLevels,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/experience-points-history",
        name: "XPPointsHistory",
        component: XPPointsHistory,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/match-scorecard-stats/:league_id/:season_game_uid/:collection_master_id",
        name: "MatchScoreCardStats",
        component: MatchScoreCardStats,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/my-profile/:user_id",
        name: "UserPublicProfile",
        component: UserPublicProfile,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/league-leaderboard/:lbid/:status/:lname",
        name: "FantasyLeagueLeaderboard",
        component: FantasyLeagueLeaderboard,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/leaderboard-details/:history_id/:u_name",
        name: "LBLeaguePoints",
        component: LBLeaguePoints,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/all-prizes",
        name: "LBAllPrizes",
        component: LBAllPrizes,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy/contest/:collection_master_id/:myKey",
        name: "StockContestListing",
        component: StockContestListing,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy/statistics",
        name: "StockFStatistics",
        component: StockFStatistics,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy/my-watchlist",
        name: "StockFMyWatchList",
        component: StockFMyWatchList,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy/lineup/:lineupKey",
        name: "StockRoster",
        component: StockRoster,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/stock-fantasy/select-captain/:captainKey",
        name: "StockSelectCaptainList",
        component: StockSelectCaptainList,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/stock-fantasy/share-contest/:contest_unique_id",
        name: "StockShreContest",
        component: StockShreContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy/team-comparison",
        name: "StockTeamComparison",
        component: StockTeamComparison,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy-equity/contest/:collection_master_id/:myKey",
        name: "StockContestListingEquity",
        component: StockContestListingEquity,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-fantasy-equity/lineup/:lineupKey",
        name: "StockRosterEquity",
        component: StockRosterEquity,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/stock-fantasy-equity/select-captain/:captainKey",
        name: "StockSelectCaptainListEquity",
        component: StockSelectCaptainListEquity,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/stock-fantasy-equity/team-comparison",
        name: "StockTeamCompEquity",
        component: StockTeamCompEquity,
        exact: true,
        RouteType: 'SequenceRoute'
    },
    {
        path: "/stock-fantasy-equity/share-contest/:contest_unique_id",
        name: "StockShreContestEquity",
        component: StockShreContestEquity,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/leaderboard-details-stock/:history_id/:type/:u_name",
        name: "LBLeaguePoints",
        component: LBLeaguePoints,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/booster-collection/:c_id/:sportsId/:lineupId",
        name: "ApplyBooster",
        component: ApplyBooster,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/bench-selection/:lineupId/:collection_master_id/:teamKey",
        name: "BenchSelection",
        component: BenchSelection,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/feature/rookie",
        name: "RookieFeature",
        component: RookieFeature,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/live-fantasy-center/:collection_id",
        name: "LivefantasyCenter",
        component: LivefantasyCenter,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/contest-listing-lf/:collection_master_id/:myKey",
        name: "LiveFantasyContestListing",
        component: LiveFantasyContestListing,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/live-fantasy/share-contest/:contest_unique_id",
        name: "LiveFantasyContest",
        component: LiveFantasyContest,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/live-fantasy/over-result/:collection_master_id/",
        name: "LivefantasyOverResult",
        component: LivefantasyOverResult,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/:sportsId/lf-leaderboard",
        name: "LFLeaderBoard",
        component: LFLeaderBoard,
        exact: true,
        RouteType: 'PrivateRoute'
    },
    {
        path: "/stock-prediction/lineup/:lineupKey",
        name: "SPRoster",
        component: SPRoster,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-prediction/stock-bid/:CollID",
        name: "SPStockBid",
        component: SPStockBid,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-prediction/team-comparison",
        name: "SPTeamComp",
        component: SPTeamComp,
        exact: true,
        RouteType: 'Route'
    },
    {
        path: "/stock-prediction/score-calculation",
        name: "SPScoreCalc",
        component: SPScoreCalc,
        exact: true,
        RouteType: 'Route'
    }
]
const SpecialRoutes = {
    MobileLogin,
    VerifyMobile,
    EmailLogin,
    VerifyEmail,
    Signup,
    Login, 
    Dashboard,
    EarnCoins,
    PickMobileNo,
    UpdateMobileNo
}

export { Routes, SpecialRoutes };