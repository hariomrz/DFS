import React, { useEffect } from 'react';
import { BrowserRouter as Router, Route, Switch, Redirect, withRouter } from 'react-router-dom';
import { Utilities, _filter, _isUndefined, isDesktop, isFooterTab } from '../Utilities/Utilities';
import { MyContext } from './MyProvider';
import { MyWallet, Transaction, AddFunds, Withdraw, PaymentMethod,BuyCoins } from "../Component/Finance";
import { LandingScreen, TermsCondition, RulesScoring, PrivacyPolicy, FAQ, AboutUs, HowToPlay, ContactUs, Offers,Legality, HowItWorks, RefundPolicy,NewRulesScoring, RookieFeature, DownloadAppPage, DeleteAccount,FantasyRules } from "../Component/StaticPages";
import { ForgotEmailPassword, ResetPassword, ChangePassword, EmailLogin, SetPassword, VerifyEmail, UpdateMobileNo, EnterPassword } from "../Component/OnBoarding/EmailFlow";
import { MobileLogin, VerifyMobile, PickUsername, ReferralCode, PickEmail, PickMobileNo, } from "../Component/OnBoarding/MobileFlow";
import { MyContest } from '../Component/MyContest';
import { Profile, ProfileEdit, VerifyAccount, PanVerification, BankVerification, AadharVerification } from "../Component/Profile";
import { GAME_ROUTE, ModuleRedirect } from '../helper/Constants';
import ScrollMemory from 'react-router-scroll-memory';
import WSManager from "../WSHelper/WSManager";
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
import PageNotFound from '../views/PageNotFound/PageNoFound';
import Loadable from 'react-loadable';
import CustomLoader from '../helper/CustomLoader';
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
import {LSFRoster, LSFRosterTransaction} from "../Component/LiveStockFantasy";
import { DMContest,FixtureDetail } from '../Component/DFSWithMultigame';
import { PFContestListing ,PFRoster,PFBooster} from '../Component/PickFantasy';
import PFLeaderBoard from '../Component/PickFantasy/PFLeaderboard';
import PFViewPick from '../Component/PickFantasy/PFViewPick';
import CashfreePG from '../Component/Finance/CashfreePG';
import StripePG from '../Component/Finance/StripePG';
import { PredictionContestList } from '../Component/PredictionModule';
import { NDFSTourList, NDFSTourDetail, NDFSCompletedList } from '../Component/NewDFSTournament';
import GeoLocationTagging from '../views/GeoLocationTagging/GeoLocationTagging';
import { PTCompletedList, PTTourDetail } from '../Component/PickemTournament';
import BannedState from '../Component/CustomComponent/BannedState';

import {TLeaderboard} from '../Component/TourLeaderboard';
import ls from 'local-storage';
import CreateAccount from '../Component/OnBoarding/EmailFlow/CreateAccount';
import ResponsibleGaming from '../Component/StaticPages/ResponsibleGaming';
import DirectPayPG from '../Component/Finance/DirectPayPG';
import { TDSDashboard } from '../Component/TDS';
import { H2hDetail } from '../Component/H2H';
import Settings from '../Component/StaticPages/Settings';
import DFSCollapseCard from '../Component/CustomComponent/DFSCollapseCard';
import FeaturedTournament from '../Component/FeaturedLeague/FeaturedTournament';
import {PropsMyEntry, PropsAddPlayer, PropsTeamDetails} from '../Component/PropsFantasy';
import { Lobby as OTLobby, ViewCompletedEntries,QuestionDetails } from '../OpinionTrade/View';
// import { Layout, DefaultHeader, DefaultFooter } from 'Local';	

function LoadingComponent(showHeader) {
    return <div className="web-container bg-white">{showHeader && <div className="app-header-style" />}<CustomLoader /></div>;
}
const AppInstallNotification = Loadable({
    loader: () => import('../views/AppInstallNotification'),
    delay: 0,
    loading: ()=> <div />
});
const SidePage = Loadable({
    loader: () => import('../views/SidePage'),
    delay: 0,
    loading: ()=> <div />
});
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
        if (Utilities.getMasterData().sports_hub && Utilities.getMasterData().sports_hub.length > 2)
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

class Routing extends React.Component {
    constructor(params) {
        super(params);
        this.state = {
            installNotificationShow: true,
            windowWidth: window.innerWidth,
            ModuleRedirect: ModuleRedirect,
            switchModule: null,	
            Layout: LoadingComponent,	
            DefaultHeader: LoadingComponent, 	
            DefaultFooter: LoadingComponent
        };
        this.desktopObject = isDesktop()
    }
    handleResize = (e) => {	
        if(this.state.windowWidth != e.target.innerWidth) {	
            this.setState({	
                windowWidth: window.innerWidth,	
            }, () => {	
                this.setState({	
                    switchModule: this.switchModuleInit(window.innerWidth)	
                });	
            });	
        }	
    }
    componentDidMount() {
        this.setState({
            switchModule: this.switchModuleInit()
        });
        window.addEventListener('online', this.updateIndicator);
        window.addEventListener('offline', this.updateIndicator);
        window.addEventListener('resize', this.handleResize);

    }

    updateIndicator=()=>{
        let internetStatus = '';
        if(navigator.onLine) { 
            internetStatus = true ;// for online
        }
        else{
            internetStatus = false ;// for offline
        }
        if(!internetStatus){
            Utilities.showToast("You're offline right now. Check your connection.", 4000)
        }
    }

    closeInstallNotification = () => {
        this.setState({ installNotificationShow: false })
    }

    switchModuleInit = (winWidth = null) => {
        const { ModuleRedirect} = this.state;
        const windowWidth = winWidth || this.state.windowWidth
        const breakpoint = 767;
        const SHD = Utilities.getMasterData().sports_hub || []
        const gameType = WSManager.getPickedGameType() || this.props.root.GameType;
        let selectedGameObj = _filter(SHD, o => o.game_key == gameType)[0] || {}
        const _finalBreakpoint = windowWidth > breakpoint && selectedGameObj.is_desktop == 1 && ModuleRedirect.includes(gameType)
        if(_finalBreakpoint) {
            document.body.classList.add('desktop-specific');
        } else {
            document.body.classList.remove('desktop-specific');
        }
        if(_finalBreakpoint) {
                import('./../Local').then(CM => {
                    this.setState({
                        Layout: CM.Layout,
                        DefaultHeader: CM.DefaultHeader,
                        DefaultFooter: CM.DefaultFooter
                    });
                });
        } else {
            this.setState({
                Layout: LoadingComponent,
                DefaultHeader: LoadingComponent, 
                DefaultFooter: LoadingComponent
            });
        }

        return _finalBreakpoint
    }
    componentWillUnmount() {
        window.removeEventListener('online', this.updateIndicator);
        window.removeEventListener('offline', this.updateIndicator);
        window.removeEventListener('resize', this.handleResize);
    }
    componentDidUpdate(prevProps) {
        if(prevProps.root.GameType != this.props.root.GameType) {
            this.setState({
                switchModule: this.switchModuleInit()
            });
        }
    }

    render() {
        const { switchModule, Layout, DefaultHeader, DefaultFooter } = this.state	

        const PrivateRoute = ({ component: Component, ...rest }) => (
            <Route {...rest} render={(props) => (
                WSManager.loggedIn() === true
                    ? <Component {...props} />
                    : <Redirect to={{ pathname: '/signup', state: { from: props.location } }} />
            )} />
        )

        const SequenceRoute = ({ component: Component, ...rest }) => (
            <Route {...rest} render={(props) => (
                (WSManager.loggedIn() === true && !_isUndefined(props.location.state))
                    ? <Component {...props} />
                    : <Redirect to={{ pathname: "/lobby", state: { from: props.location } }} />
            )} />
        )

        const OnboardingRoute = ({ component: Component, ...rest }) => (

            <Route {...rest} render={(props) => (
                <React.Fragment>
                    {WSManager.loggedIn() === false ?
                        !_isUndefined(rest.location.state) || rest.path === '/signup'
                            ? <Component {...{...props, ...this.props}} />
                            : <Redirect to={{ pathname: '/signup' }} />
                        : <Redirect to={"/lobby#" + Utilities.getSelectedSportsForUrl()} />
                    }
                </React.Fragment>
            )} />
        )

        const CoinRoute = ({ component: Component, ...rest }) => (

            parseInt(Utilities.getMasterData().a_coin || '0') === 1
                ?
                <Route {...rest} render={(props) => (<Component {...props} />)} />
                :
                <PageNotFound />
        )

        const DashboardPRoute = ({ component: Component, page_key: pageKey, ...rest }) => (
            isFooterTab(pageKey)
                ?
                <Route {...rest} render={(props) => (
                    WSManager.loggedIn() === true
                    ? <Dashboard {...props} />
                    : <Redirect to={{ pathname: '/signup', state: { from: props.location } }} />
                    )} />
                :
                <Route {...rest} render={(props) => (
                    WSManager.loggedIn() === true
                    ? <Component {...props} />
                    : <Redirect to={{ pathname: '/signup', state: { from: props.location } }} />
                )} />
        )

        return (
            <MyContext.Consumer>
                {(context) => (
                    <React.Fragment>
                        <Router>
                            {!window.ReactNativeWebView && switchModule && <DefaultHeader />}
                            <main className='Site'>
                                <DeepLinkHandler />
                                {
                                    !window.ReactNativeWebView && this.state.installNotificationShow && <>
                                        <AppInstallNotification closeInstallNotification={this.closeInstallNotification} />
                                    </>
                                }                                
                                <div
                                    style={{
                                        backgroundImage: window.ReactNativeWebView ? '' : `url(${Utilities.getUploadURL(Utilities.getMasterData().bg_image)})`,
                                        backgroundPosition: "center",
                                        backgroundSize: "cover",
                                        backgroundRepeat: "no-repeat"
                                    }}
                                    className="Site-content">
                                    {
                                        switchModule ? 
                                        <Layout  {...this.props} />
                                        :
                                        <div className='routing-550'>
                                        <ScrollMemory />
                                        <Switch>
                                            
                                            <Route path="/you-are-in-queue" component={YouRInQueue} exact />
                                            <Route path="/banned-state" component={BannedState} exact />
                                            <Route path="/lobby" component={Dashboard} exact />
                                            <Route path="/" component={LandingScreen} exact />

                                            <SequenceRoute path="/lineup/:lineupKey" component={Roster} exact />
                                            <SequenceRoute path="/field-view/:fieldKey" component={FieldView} exact />
                                            <SequenceRoute path="/field-view-guru/:fieldKey" component={GuruFiledView} exact />

                                            <SequenceRoute path="/select-captain/:captainKey" component={SelectCaptainList} exact />

                                            {/* Onboarding */}
                                            <OnboardingRoute path="/signup" component={Utilities.getMasterData().login_flow === '1' ? EmailLogin : MobileLogin} exact />
                                            <OnboardingRoute path="/set-password" component={SetPassword} exact />
                                            <OnboardingRoute path="/password" component={EnterPassword} exact />
                                            <OnboardingRoute path="/verify" component={Utilities.getMasterData().login_flow === '1' ? VerifyEmail : VerifyMobile} exact {...this.props}/>
                                            <OnboardingRoute path="/referral" component={ReferralCode} exact />
                                            <OnboardingRoute path="/pick-username" component={PickUsername} exact />
                                            <OnboardingRoute path="/email" component={PickEmail} exact />
                                            <OnboardingRoute path="/enter-email" component={ForgotEmailPassword} exact />
                                            <OnboardingRoute path="/create-account" component={CreateAccount} exact />
                                            <Route path="/forgot-password" component={ResetPassword} exact />
                                            <Route path="/pick-mobile" component={Utilities.getMasterData().login_flow === '1' ? UpdateMobileNo : PickMobileNo} exact />
                                            <Route path="/geo_location" component={GeoLocationTagging} exact />


                                            {/* Public */}
                                            <Route path="/:sportsId/contest-listing/:collection_master_id/:myKey" component={ContestListing} exact />
                                            <Route path="/:sportsId/contest/:contest_unique_id" component={Contest} exact />
                                            <Route path="/:sportsId/multi-with-dfs/:contest_unique_id" component={DMContest} exact />
                                            <Route path="/:sportsId/multigame-contest/:contest_unique_id" component={MultiGameContest} exact />
                                            <Route path="/:sportsId/my-teams/:collection_master_id/:myKey" component={MyTeams} exact />
                                            <Route path="/terms-condition" component={TermsCondition} exact />
                                            <Route path="/legality" component={Legality} exact />
                                            <Route path="/fantasy-rules" component={FantasyRules} exact />
                                            <Route path="/rules-and-scoring" component={RulesScoring} exact />
                                            <Route path="/new-rules-and-scoring" component={NewRulesScoring} exact />
                                            <Route path="/offers" component={Offers} exact />
                                            <Route path="/privacy-policy" component={PrivacyPolicy} exact />
                                            <Route path="/faq" component={FAQ} exact />
                                            <Route path="/apk" component={DownloadAppPage} exact />
                                            <Route path="/app" component={DownloadAppPage} exact />
                                            <Route path="/contact-us" component={ContactUs} exact />
                                            <Route path="/about-us" component={AboutUs} exact />
                                            <Route path="/how-to-play" component={HowToPlay} exact />
                                            <Route path="/refund-policy" component={RefundPolicy} exact />
                                            <Route path="/legality" component={Legality} exact />
                                            <Route path="/how-it-works" component={HowItWorks} exact />
                                            <Route path="/responsible-gaming" component={ResponsibleGaming} exact />
                                            <Route path="/private-contest" component={HaveALeagueCodeClass} exact />
                                            <Route path="/payment-method" component={this.desktopObject.is_desktop ? PaymentMethodPlaceHolder : PaymentMethod} exact />
                                            <Route path="/cashfree" component={CashfreePG} exact />
                                            <Route path="/stripe" component={StripePG} exact />
                                            <Route path="/directpay" component={DirectPayPG} exact />
                                            <Route path="/:sportsId/prediction/contest-listing/:collection_master_id/:myKey" component={PredictionContestList} exact />
                                            <Route path="/:sportsId/prediction-details/:season_game_uid/:prediction_master_id" component={PredictionShare} exact />
                                            <Route path="/:sportsId/prediction/participants/:prediction_master_id" component={PredictionParticipants} exact />
                                            <Route path="/:sportsId/open-predictor-details/:category_id/:prediction_master_id" component={ShareOpenPredictor} exact />
                                            <Route path="/:sportsId/open-predictor/participants/:prediction_master_id" component={OpenPredictorParticipants} exact />
                                            <Route path="/:sportsId/open-predictor-leaderboard-details/:category_id/:prediction_master_id" component={ShareFPPOpenPredictor} exact />
                                            <Route path="/:sportsId/open-predictor-leaderboard/participants/:prediction_master_id" component={OpenPredictorFPPParticipants} exact />

                                            <Route path="/self-exclusion" component={SelfExclusion} exact />

                                            <Route path="/team-comparison" component={TeamComparison} exact />
                                            <Route path="/delete-account" component={DeleteAccount} exact />
                                            <Route path="/what-is-new" component={WhatIsNew} exact />
                                            <PrivateRoute path="/leaderboard" component={Dashboard} exact />
                                            {/* <PrivateRoute path="/leaderboards" component={Dashboard} exact /> */}
                                            <PrivateRoute path="/global-leaderboard" component={FantasyRefLeaderboard} exact />

                                            <PrivateRoute path="/group-chat/:groupId" component={GroupChat} exact />

                                            {/* Dashboard Private Screen */}
                                            <DashboardPRoute path="/my-contests" component={MyContest} page_key={'my-contests'} exact />
                                            <DashboardPRoute path="/my-profile" component={Profile} page_key={'my-profile'} exact />
                                            <DashboardPRoute path="/feed" component={Feed} page_key={'feed'} exact />
                                            {/* <DashboardPRoute path="/sports-hub" component={SportsHub} page_key={'SportsHub'} exact /> */}

                                            <DashboardPRoute path="/notification" component={UserNotification} page_key={'notification'} exact />
                                            {/* <DashboardPRoute path="/refer-friend" component={ReferFriend} page_key={'refer-friend'} exact /> */}
                                            <DashboardPRoute path="/refer-friend" component={ReferFriendStandard} page_key={'refer-friend'} exact />

                                            <DashboardPRoute path="/refer-friend-leaderboard" component={ReferalLeaderBoard} page_key={'refer-friend-leaderboard'} exact />
                                            <DashboardPRoute path="/my-wallet" component={MyWallet} page_key={'my-wallet'} exact />
                                            <DashboardPRoute path="/buy-coins" component={BuyCoins} page_key={'my-wallet'} exact />
                                            
                                            {/* Coins*/}
                                            <CoinRoute path="/earn-coins" component={(isFooterTab('earn-coins')) ? Dashboard : EarnCoins} exact />
                                            <CoinRoute path="/rewards" component={RedeemCoins} exact />
                                            <CoinRoute path="/feedback" component={FeedbackQA} exact />
                                            {/* <CoinRoute path="/what-is-new" component={WhatIsNew} exact /> */}
                                            {/********/}
                                            <PrivateRoute path="/game-center/:collection_master_id" component={GameCenter} exact />

                                            <PrivateRoute path="/edit-profile" component={ProfileEdit} exact />
                                            <PrivateRoute path="/create-contest" component={createContest} exact />
                                            <PrivateRoute path="/:sportsId/leaderboard" component={LeaderBoard} exact />
                                            <PrivateRoute path="/transactions" component={Transaction} exact />
                                            <PrivateRoute path="/add-funds" component={AddFunds} exact />
                                            <PrivateRoute path="/withdraw" component={Withdraw} exact />
                                            <Route path="/more" component={Dashboard} exact />
                                            <PrivateRoute path="/change-password" component={ChangePassword} exact />
                                            <PrivateRoute path="/verify-account" component={VerifyAccount} exact />
                                            <PrivateRoute path="/pan-verification" component={PanVerification} exact />
                                            <PrivateRoute path="/aadhar-verification" component={AadharVerification} exact />
                                            <PrivateRoute path="/bank-verification" component={BankVerification} exact />
                                            <PrivateRoute path="/referral-system" component={RefferalSystem} exact />
                                            <PrivateRoute path="/edit-referral-code" component={EditReferralCode} exact />
                                            <PrivateRoute path="/affiliate-program" component={AffiliateProgram} exact />
                                            <PrivateRoute path="/affiliate-request" component={AffiliateRequest} exact />
                                            <PrivateRoute path="/crypto-verification" component={CryptoVerifcation} exact />

                                            {/* Pickem */}
                                            <Route path="/:sportsId/free-to-play/:collection_master_id/:myKey/:game_type/:season_game_uid/:contest_id" component={FreeToPlayList} exact />

                                            <PrivateRoute path="/all-prizes/:mini_league_uid/:isMiniLeaguePrize" component={AllPrizes} exact />
                                            <PrivateRoute path="/mini-league-fixture/:mini_league_id/:league_name" component={FreeToPlayLobby} exact />
                                            <PrivateRoute path="/sheduled-fixture/:mini_league_uid/:mini_league_name" component={LeagueSheduledFixture} exact />

                                            <PrivateRoute path="/user-league-points" component={UserLeaguePoints} exact />
                                            <PrivateRoute path="/all-leagues" component={AllLeagueList} exact />
                                            <PrivateRoute path="/completed-leagues" component={CompletedLeagueList} exact />
                                            <PrivateRoute path="/league-details" component={LeagueDetails} exact />
                                            <PrivateRoute path="/mini-league-leader-board" component={LeaderBoardFreeToPlay} exact />
                                            {SportsHub && <Route path="/sports-hub" component={SportsHub} exact />}

                                            {/* Private Contest */}

                                            <Route path="/:sportsId/:collection_master_id/:fixterDetail/private-contest-banner" component={WhyPrivateContest} exact />
                                            <Route path="/:sportsId/:collection_master_id/:fixterDetail/private-contest" component={PrivateContestParent} exact />
                                            <Route path="/:sportsId/:collection_master_id/:fixterDetail/lf-private-contest" component={LFPrivateContestParent} exact />
                                            <Route path="/share-private-contest" component={SharePrivateContest} exact />
                                            <Route path="/lf-share-private-contest" component={LFSharePrivateContest} exact />

                                            {/* Guru Module */}
                                            <PrivateRoute path="/pl/lineup" component={GuruRoster} exact />
                                            <PrivateRoute path="/leaderboards" component={FantasyRefLeaderboard} exact />
                                            <Route path="/all-prizes" component={LBAllPrizes} exact />
                                            <PrivateRoute path="/league-leaderboard/:lbid/:status/:lname" component={FantasyLeagueLeaderboard} exact />
                                            <PrivateRoute path="/leaderboard-details/:history_id/:u_name" component={LBLeaguePoints} exact />
                                            {/* TDS */}
                                            <PrivateRoute path="/tds-dashboard" component={TDSDashboard} exact />
                                            {/* Default Page */}

                                        <Route path="/:sportsId/dfs-tournament-list" component={NDFSTourList} exact />
                                        <Route path="/:sportsId/dfs-completed-list" component={NDFSCompletedList} exact />
                                        <Route path="/:sportsId/pickem-tournament-completed-list" component={PTCompletedList} exact />
                                        <Route path="/:sportsId/dfs-tournament-detail/:tid" component={NDFSTourDetail} exact />
                                        <Route path="/:sportsId/featured-tournament/:dfsid/:pid" component={FeaturedTournament} exact />
                                        {/* DFS Tournament */}
                                        <Route path="/:sportsId/tournament/:tournamentId/:leagueId/:leaguename/:date" component={DFSTourFixtures} exact />
                                        {/* <Route path="/tournament-list" component={DFSTourList} exact /> */}
                                        <Route path="/:sportsId/tournament-leaderboard/:league_name" component={DFSTourLeaderboard} exact />
                                        <Route path="/:sportsId/tournament-fixture-leaderboard/:league_name/:tournament_season_id" component={DFSTourFixtureLeaderboard} exact />
                                        <Route path="/:sportsId/tournament/live-fixture-list/:tournament_id/:leaguename/:date" component={DFSTourLiveFixtureList} exact />


                                            <Route path="/collapse-card" component={DFSCollapseCard} exact />




                                            {/* <Route path="/tournament/share" component={DFSTourShare} exact /> */}
                                            <Route path="/:sportsId/tournament/:tournament_unique_id" component={DFSTourShare} exact />
                                            <PrivateRoute path="/:sportsId/tournament/user/history/:user_id" component={DFSUserTourHistory} exact />
                                            <SequenceRoute path="/tournament/lineup/:lineupKey" component={DFSTourRoster} exact />
                                            <SequenceRoute path="/tournament/select-captain/:captainKey" component={DFSTourSelectCaptainList} exact />
                                            {/* <Route path="/:sportsId/tournament/share/:contest_unique_id" component={DFSTourShare} exact /> */}
                                            <SequenceRoute path="/tournament/field-view/:fieldKey" component={DFSTourFieldView} exact />
                                            {/* XPModule */}
                                            <Route path="/experience-points" component={XPPoints} exact />
                                            <Route path="/experience-points-levels" component={XPLevels} exact />
                                            <Route path="/experience-points-history" component={XPPointsHistory} exact />

                                            <PrivateRoute path="/:sportsId/match-scorecard-stats/:league_id/:season_game_uid/:collection_master_id" component={MatchScoreCardStats} exact />
                                            <Route path="/my-profile/:user_id" component={UserPublicProfile} exact />

                                            <PrivateRoute path="/league-leaderboard/:lbid/:status/:lname" component={FantasyLeagueLeaderboard} exact />
                                            <Route path="/leaderboard-details/:history_id/:u_name" component={LBLeaguePoints} exact />
                                            <Route path="/all-prizes" component={LBAllPrizes} exact />
                                            <Route path="/stock-fantasy/contest/:collection_master_id/:myKey" component={StockContestListing} exact />
                                            <Route path="/stock-fantasy/statistics" component={StockFStatistics} exact />
                                            <Route path="/stock-fantasy/my-watchlist" component={StockFMyWatchList} exact />
                                            <SequenceRoute path="/stock-fantasy/lineup/:lineupKey" component={StockRoster} exact />
                                            <SequenceRoute path="/stock-fantasy/select-captain/:captainKey" component={StockSelectCaptainList} exact />
                                            <Route path="/stock-fantasy/share-contest/:contest_unique_id" component={StockShreContest} exact />
                                            <Route path="/stock-fantasy/team-comparison" component={StockTeamComparison} exact />
                                            {/* StockEqity */}
                                            <Route path="/stock-fantasy-equity/contest/:collection_master_id/:myKey" component={StockContestListingEquity} exact />
                                            <SequenceRoute path="/stock-fantasy-equity/lineup/:lineupKey" component={StockRosterEquity} exact />
                                            <SequenceRoute path="/stock-fantasy-equity/select-captain/:captainKey" component={StockSelectCaptainListEquity} exact />
                                            <SequenceRoute path="/stock-fantasy-equity/team-comparison" component={StockTeamCompEquity} exact />
                                            <Route path="/stock-fantasy-equity/share-contest/:contest_unique_id" component={StockShreContestEquity} exact />

                                            <Route path="/leaderboard-details-stock/:history_id/:type/:u_name" component={LBLeaguePoints} exact />

                                            {/* //Booster */}
                                            <PrivateRoute path="/booster-collection/:c_id/:sportsId/:lineupId" component={ApplyBooster} exact />
                                            <Route path="/bench-selection/:lineupId/:collection_master_id/:teamKey" component={BenchSelection} exact />
                                            {/* <PrivateRoute path="/booster-collection/:c_id/:sportsId/:lineupId" component={BenchPlayerList} exact /> */}

                                            <Route path="/feature/rookie" component={RookieFeature} exact />

                                            {/* Live Fantasy  */}
                                            <Route path="/live-fantasy-center/:collection_id" component={LivefantasyCenter} exact />
                                            <Route path="/:sportsId/contest-listing-lf/:collection_master_id/:myKey" component={LiveFantasyContestListing} exact />
                                            <Route path="/live-fantasy/share-contest/:contest_unique_id" component={LiveFantasyContest} exact />
                                            <Route path="/live-fantasy/over-result/:collection_master_id/" component={LivefantasyOverResult} exact />
                                            <PrivateRoute path="/:sportsId/lf-leaderboard" component={LFLeaderBoard} exact />
                                            {/* Stock Prediction  */}
                                            <Route path="/stock-prediction/lineup/:lineupKey" component={SPRoster} exact />
                                            <Route path="/stock-prediction/stock-bid/:CollID" component={SPStockBid} exact />
                                            <Route path="/stock-prediction/team-comparison" component={SPTeamComp} exact />
                                            <Route path="/stock-prediction/score-calculation" component={SPScoreCalc} exact />
                                            {/* Pick Fantasy  */}
                                            <Route path="/:sportsId/pick-fantasy/contest-listing/:season_id/:myKey" component={PFContestListing} exact />
                                            <SequenceRoute path="/pick-fantasy/lineup/:lineupKey" component={PFRoster} exact />
                                            <SequenceRoute path="/picks-fantasy/apply-booster/:lineupKey" component={PFBooster} exact />
                                            <Route path="/picks-fantasy/pick-leaderboard" component={PFLeaderBoard} exact />
                                            <Route path="/picks-fantasy/pick-view/:seasonID/:userTeamId" component={PFViewPick} exact />
                                            <Route path="/delete-account" component={DeleteAccount} exact />

                                            <Route path="/live-stock-fantasy/lineup/:lineupKey" component={LSFRoster} exact />
                                            <Route path="/live-stock-fantasy/:contestKey/:lineupKey/transaction" component={LSFRosterTransaction} exact />

                                            <Route path="/:sportsId/pickem/detail/:tourId" component={PTTourDetail} exact />
                                            <Route path="/:sportsId/h2h-detail/:collection_master_id" component={H2hDetail} exact />
                                            <Route path="/tour-leaderboard" component={TLeaderboard} exact />
                                            <Route path="/:sportsId/fixture-detail/:collection_master_id" component={FixtureDetail} exact />

                                            {/* {props fantasy} */}
                                            <Route path="/props-fantasy/my-entries" component={PropsMyEntry} exact />
                                            <Route path="/props-fantasy/add-player/:user_team_id" component={PropsAddPlayer} exact />
                                            <Route path="/props-fantasy/team/:user_team_id" component={PropsMyEntry} exact />

                                            <Route path="/props-fantasy/team-details/:props_status/:user_team_id" component={PropsTeamDetails} exact />


                                            {/* opinion trade */}

                                            
                                            <Route path="/completed-entries/:sports_id" component={ViewCompletedEntries} exact />
                                            <Route path="/question-details/:fixture_name/:question_id" component={QuestionDetails} exact />

                                            
                                            <Route path="/settings/:settingId" component={Settings} exact />

                                            <Redirect from="/:ModuleName/lobby" to={{ pathname: '/lobby' }} />
                                            <Redirect from="/:ModuleName/my-contests" to={{ pathname: '/my-contests' }} />
                                            <Route path="/feeds/:post_id" component={Feed} exact />
                                            <Route component={PageNotFound} />

                                        </Switch>
                                        </div>
                                    }

                                    {!window.ReactNativeWebView && <SidePage />}
                                </div>
                            </main>
                            {!window.ReactNativeWebView && switchModule && <DefaultFooter />}
                        </Router>
                    </React.Fragment>
                )}
            </MyContext.Consumer>
        )
    }
}

export default Routing


const PaymentMethodPlaceHolder = () => {
    return ''
}

const DeepLinkHandler = withRouter((props) => {
    const blockMultiRedirection = () => {
        ls.set('canRedirect', false)
        setTimeout(() => {
            ls.set('canRedirect', true)
        }, 1000 * 5);
    }

    const deeplinkListner = (e) => {
        if (e.data.action == 'app_dep_linking' && e.data.type == 'android') {
            let can = ls.get('canRedirect');
            if (can == null || can) {
                blockMultiRedirection()
                let pathName = e.data.pathName;
                if (pathName) {
                    props.history.push(pathName);
                }
            }
        }
    }
    useEffect(() => {
        window.addEventListener('message', deeplinkListner)
        return () => {
            window.removeEventListener('message', deeplinkListner)
        }
    }, [])
    return (<></>)
})