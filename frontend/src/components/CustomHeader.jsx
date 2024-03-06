import React, { Suspense, lazy, useEffect } from 'react';
import { MyContext } from '../InitialSetup/MyProvider';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import { Utilities, parseURLDate, _isEmpty, isDateTimePast, isDesktop } from '../Utilities/Utilities';
import { MatchInfo, MomentDateComponent } from "../Component/CustomComponent";
import { getUserProfile, getUserBalance, getAppNotificationCount, getDailyCoins, getSpinTheWheelData, getUserLiveOversLf } from "../WSHelper/WSCallings";
import Images from './images';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as Constants from "../helper/Constants";
import * as AppLabels from "../helper/AppLabels";
import { BanStateModal, BanStateMSGModal, RGIModal } from '../Modals';
import { LobbyCoachMarkIndex } from '../Component/CoachMarks';
import CountdownTimer from '../views/CountDownTimer';
import ls from 'local-storage';
import { DARK_THEME_ENABLE } from "../helper/Constants";
import Moment from "react-moment";
import { PredictionLearnMore } from '../Component/PredictionModule';
import { SportsIDs } from '../JsonFiles';
import UnreadNotification from '../helper/location'
import PropsWarningPopup from '../Component/PropsFantasy/Common/PropsModal/PropsWarningPopup';
import { CommonLabels } from "../helper/AppLabels";


// import { trafficSource } from "../helper/Constants";
// import firebase from '../views/firebase/firebase';
const RefferCoachMark = lazy(() => import('../Modals/RefferCoachMark'));
const OpenSourceUrl = lazy(() => import('../Component/OpenPredictorModule/OpenSourceUrl'));
const SpeenWheelModal = lazy(() => import('../Modals/SpeenWheelModal'));
const Banner = lazy(() => import('../Modals/Banner'));
const MyAlert = lazy(() => import('../Modals/MyAlert'));
const DailyCheckinBonus = lazy(() => import('../Component/CoinsModule/DailyCheckinBonus'));
const RedeemSuccess = lazy(() => import('../Component/CoinsModule/RedeemSuccess'));
const ReedemCoachMarks = lazy(() => import('../Component/CoinsModule/ReedemCoachMarks'));
const LeaderboardModal = lazy(() => import('../Modals/LeaderboardModal'));
const LiveOverSocket = lazy(() => import('../Component/LiveFantasy/LiveOverSocket'));


var lastBlanceCallDate = {};
var globalThis = null;

class CustomHeader extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            mTotalBalance: "",
            showResetAlert: false,
            message: AppLabels.RESET_ACTION,
            notificationData: Constants.NOTIFICATION_DATA,
            allowCollection: Utilities.getMasterData().a_collection,
            showTooltip: true,
            lineupArr: [],
            showDCBM: false,
            showCoinCM: false,
            showRCM: false,
            refRCMData: '',
            dailyData: '',
            isBannerShow: false,
            userCoinBalnc: (WSManager.getBalance().point_balance || 0),
            selectedGameType: WSManager.getPickedGameType(),
            showRSuccess: false,
            redeemData: '',
            showRedeemCM: false,
            SHCoachMarks: null,
            showSHSCM: false,
            NEWP: false,
            NPMSG: '',
            sourceUrlShow: false,
            sourceUrlData: '',
            isFilterselected: false,
            filterBy: '',
            showSpinWheel: false,
            spinWheelData: '',
            showBanStateModal: false,
            banStateData: '',
            showBanStateMSGModal: false,
            banStateMSGData: '',
            showRGIModal: WSManager.loggedIn() && Utilities.getMasterData().allow_self_exclusion == 1 ? ((ls.get('RGMshow') || 0) == 1 ? false : true) : false,
            ApiCalled: Utilities.getMasterData().a_coin !== "0" ? false : true,
            LobyyData: {
                home_flag: '',
                away_flag: '',
                home: '--',
                away: '--',
                collection_name: '--',
                game_starts_in: 0,
                today: 0,
                season_scheduled_date: 0,
            },
            showLBModal: false,
            listPieStatus: 0,
            isSkipSpin: Constants.IsSpinWheelSkip || false,
            updateBal: false,
            apiBalCalled: false,
            showPredLM: false,
            showUniLf: false,
            soff: 0,
            warningPopup: false
        }
        this.desktopObject = isDesktop()
    }


    openUniversalModalLF = () => {
        this.setState({
            showUniLf: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideUniversalModalLF = () => {
        this.setState({
            showUniLf: false,
        });
    }

    showLiveOverPopup = (obj) => {
        let isShowpopu = ls.get("isULF")
        if (!isShowpopu) {
            this.getUserLiveOvers()
        }

    }
    getUserLiveOvers = async () => {
        let param = {
            "sports_id": Constants.AppSelectedSport
        }
        getUserLiveOversLf(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ userLiveOverList: responseJson.data }, () => {
                    if (!_isEmpty(this.state.userLiveOverList)) {
                        this.openUniversalModalLF()

                    }


                })
            }
        })

    }

    componentDidMount() {
        globalThis = this;
        const { is_desktop } = this.desktopObject
        if(is_desktop) return;

        if (WSManager.loggedIn()) {
            var page_pathname = window.location.pathname;
            let setBal = WSManager.getBalance() || '';
            if (Object.keys(setBal).length === 0) {
                this.callUserBalanceApi();
            }
            if (page_pathname === "/add-funds") {
                setTimeout(() => {
                    this.callUserBalanceApi();
                }, 500);
            }
            if (!WSManager.getProfile().user_setting) {
                this.callGetMyProfileApi();
            } else {
                this.showWhatsNew(WSManager.getProfile());
            }
            // setTimeout(() => {
            //     if (Constants.SELECTED_GAMET != Constants.GameType.Free2Play) {
            //         this.getAPiNotificationCount();
            //     }
            // }, 2500);
            let lsBannerData = WSManager.getBannerData();
            let mdBannerData = Utilities.getMasterData().banner;
            if (mdBannerData && (!lsBannerData || lsBannerData.app_banner_id != mdBannerData.app_banner_id)) {
                this.isDisplayBanner();
            }
            // if(Utilities.getMasterData().a_spin == 1){
            //     let todayString = new Date().toDateString();
            //     if (WSManager.getWheelData().day_string !== todayString && window.location.pathname === '/lobby') {
            //         this.getSpinWheelData();
            //     }
            // }
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.LobyyData && nextProps.LobyyData && this.state.LobyyData.collection_name != nextProps.LobyyData.collection_name) {
            this.setState({ LobyyData: nextProps.LobyyData })
        }
        if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && this.state.LobyyData && nextProps.LobyyData &&
            this.state.LobyyData.league_name != nextProps.LobyyData.league_name && this.state.LobyyData.home != nextProps.LobyyData.home) {
            this.setState({ LobyyData: nextProps.LobyyData })
        }
    }

    componentWillUnmount() {
        this.setState = () => {
            return;
        };
        window.removeEventListener('scroll', this.onScrollList);
    }

    getAPiNotificationCount = () => {

        let minuts = Constants.NOTIFICATION_DATA.date ? Utilities.minuteDiffValue(Constants.NOTIFICATION_DATA) : 0;
        if (this.props.HeaderOption.notification && (minuts === 0 || minuts > 1)) {
            getAppNotificationCount().then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    if (typeof responseJson.data != "undefined") {
                        let obj = { date: Date.now(), count: responseJson.data }
                        Constants.setValue.setNotificationCount(obj);
                        this.setState({ notificationData: obj })
                    }
                }
            })
        }
    }

    static showSpinWheel = () => {
        if (Utilities.getMasterData().a_spin == 1) {
            let todayString = new Date().toDateString();
            if (WSManager.getWheelData().day_string !== todayString) {
                globalThis.setState({
                    isSkipSpin: true
                })
                globalThis.getSpinWheelData();
            }
        }
    }

    static changeFilter = (value, filterBy) => {
        globalThis.setState({
            isFilterselected: value,
            filterBy: filterBy
        })
    }

    callGetMyProfileApi() {
        let param = {
        }

        getUserProfile(param).then(async (responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                // if (trafficSource != null && trafficSource !=undefined && trafficSource != '') {
                //     let mEvent = await this.getEventName(trafficSource)
                //     firebase.analytics().logEvent(mEvent, {
                //         content_type: 'signup',
                //         user_name: '' + responseJson.data.user_name,
                //         phone_no: '' + responseJson.data.phone_no,
                //         email: '' + responseJson.data.email
                //      });
                // }
                // else {
                //     let mEvent = 'dt_signup_profile_update';
                //     console.log('direct_signup trigger', '');
                //     firebase.analytics().logEvent(mEvent, {
                //         content_type: 'signup',
                //         user_name: '' + responseJson.data.user_name,
                //         phone_no: '' + responseJson.data.phone_no,
                //         email: '' + responseJson.data.email                
                //         });
                // }
                WSManager.setProfile(responseJson.data);
                this.setState({ profile: responseJson.data });
                this.showWhatsNew(responseJson.data);
            }
        })
    }

    getEventName(source) {
        if (source == 'fb') {
            return 'fb_profile_update';
        }
        else if (source == 'insta') {
            return 'insta_profile_update';
        }
        else if (source == 'google_ads') {
            return 'googleads_profile_update';
        }
        else if (source == 'twitter') {
            return 'twitter_profile_update';
        }
        else {
            return 'direct_profile_update';

        }
    }

    callUserBalanceApi() {
        if (!lastBlanceCallDate.date || Utilities.minuteDiffValue(lastBlanceCallDate) > 1 || this.state.updateBal) {
            lastBlanceCallDate['date'] = Date.now();
            this.getUserBal()
        } else {
            let lsbalance = WSManager.getBalance() || {};
            let tempBalance = Utilities.getTotalUserBalance((lsbalance.bonus_amount || 0), (lsbalance.real_amount || 0), (lsbalance.winning_amount || 0))
            this.setState({ mTotalBalance: Utilities.kFormatter(tempBalance), userCoinBalnc: (lsbalance.point_balance || 0) });
        }
    }

    getUserBal = () => {
        // let minuts = Constants.NOTIFICATION_DATA.date ? Utilities.minuteDiffValue(Constants.NOTIFICATION_DATA) : 0;
        // if (this.props.HeaderOption.notification && (minuts === 0 || minuts > 0.7)) {
        getUserBalance().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
                WSManager.setBalance(responseJson.data.user_balance);
                let tempBalance = Utilities.getTotalUserBalance((responseJson.data.user_balance.bonus_amount || 0), (responseJson.data.user_balance.real_amount || 0), (responseJson.data.user_balance.winning_amount || 0))
                this.setState({ mTotalBalance: Utilities.kFormatter(tempBalance), userCoinBalnc: responseJson.data.user_balance.point_balance });
            }
        })
        // }
    }

    getDailyStreakCoins = () => {
        let param = {}
        getDailyCoins(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ dailyData: responseJson.data, showDCBM: responseJson.data.allow_claim === 1 })
                WSManager.setDailyData(responseJson.data)
            }
            this.setState({
                ApiCalled: true
            })
        })
    }

    getSpinWheelData = () => {
        let param = {}
        getSpinTheWheelData(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    spinWheelData: responseJson.data, showSpinWheel: responseJson.data.claimed == 0
                }, () => {
                    if (responseJson.data.claimed == 1) {
                        const data = {};
                        let todayString = new Date().toDateString();
                        data['day_string'] = todayString;
                        data['claimed'] = 1;
                        WSManager.setWheelData(data);
                    }
                })
            }
            this.setState({
                ApiCalled: true
            })
        })
    }

    showWhatsNew = (data) => {
        let isSporthub = Constants.IS_SPORTS_HUB ? Constants.SELECTED_GAMET : true;
        const {
            HeaderOption,
        } = this.props;
        if (!HeaderOption.isOnb && isSporthub && Utilities.getMasterData().a_coin !== "0" && !WSManager.getShareContestJoin()) {
            // if (data.user_setting && data.user_setting.earn_coin == "0") {
            //     this.props.history.push({ pathname: '/what-is-new', state: { isFirst: true } })
            // } else {
            // if(Utilities.getMasterData().a_spin != 1){
            if (Utilities.getMasterData().daily_streak_bonus !== "0") {
                let todayString = new Date().toDateString();
                if (WSManager.getDailyData().day_string !== todayString) {
                    this.getDailyStreakCoins();
                } else {
                    this.setState({ dailyData: WSManager.getDailyData(), showDCBM: WSManager.getDailyData().allow_claim === 1 })
                }
            }
            else {
                this.setState({
                    ApiCalled: true
                })
            }
            // }
        }
    }

    hideSpinWheel = () => {
        this.setState({ showSpinWheel: false, isSkipSpin: false, updateBal: true })
        this.props.hideSpinWheel()
    }
    succSpinWheel = () => {
        this.props.succSpinWheel()
    }

    static showDailyStreak = () => {
        globalThis.setState({ showDCBM: true })
    }

    hideDailyCheckIn = () => {
        this.setState({ showDCBM: false, updateBal: true })
    }

    static showCoinCM = () => {
        globalThis.setState({ showCoinCM: Constants.IS_SPORTS_HUB ? false : true })
    }

    hideCoinCM = () => {
        this.setState({ showCoinCM: false })
    }

    static showSHSCM = () => {
        globalThis.setState({ showSHSCM: true })
    }

    hideSHSCM = () => {
        this.setState({ showSHSCM: false })

    }
    static showRCM = (data) => {
        if (WSManager.getProfile().user_setting && WSManager.getProfile().user_setting.refer_a_friend == "0") {
            globalThis.setState({ showRCM: true, refRCMData: data })
        }

    }

    hideRCM = () => {
        this.setState({ showRCM: false })
    }

    /**
    * @description this method to hide responsible gaming into modal,
    */
    RGIModalHide = () => {
        ls.set('RGMshow', 1)
        this.setState({
            showRGIModal: false
        });
    }

    static showUrlModal = (data) => {
        globalThis.setState({
            sourceUrlShow: true,
            sourceUrlData: data
        })
    }

    /**
    * @description this method to hide responsible gaming into modal,
    */
    LBModalHide = () => {
        this.setState({
            showLBModal: false
        });
    }

    static LBModalShow = () => {
        globalThis.setState({
            showLBModal: true
        })
    }

    hideUrmModal = () => {
        this.setState({
            sourceUrlShow: false
        })
    }

    static showRSuccess = (value) => {
        globalThis.setState({ showRSuccess: true, redeemData: value })
    }

    hideRSuccess = () => {
        this.setState({ showRSuccess: false })
    }

    static showRedeemCM = () => {
        if (!globalThis.state.showDCBM && !globalThis.state.showCoinCM && !globalThis.state.showRSuccess && !globalThis.state.isBannerShow) {
            globalThis.setState({ showRedeemCM: true })
        }
    }

    hideRedeemCM = () => {
        this.setState({ showRedeemCM: false });
    }

    static updateCoinBalance = (bal) => {
        globalThis.setState({ userCoinBalnc: bal })
    }

    isDisplayBanner() {
        let masterData = Utilities.getMasterData();
        if (masterData.banner) {
            this.setState({ isBannerShow: true })
        } else {
            this.setState({ isBannerShow: false })
        }
    }

    onBannerHide() {
        let masterData = Utilities.getMasterData();
        this.setState({ isBannerShow: false });
        WSManager.setBannerData(masterData.banner)
    }

    hidePitchToolTip = () => {
        setTimeout(() => {
            this.setState({
                showTooltip: false
            })
        }, 3000)
        return true;
    }

    AlertgoBack = () => {
        if (this.state.lineupArr && this.state.lineupArr.length > 0) {
            this.setState({ showResetAlert: true })
        }
        else {
            if (Constants.SELECTED_GAMET == Constants.GameType.PickFantasy && Object.keys(this.state.lineupArr).length > 0) {
                this.setState({ showResetAlert: true })
            }
            else {
                this.goBackScreen()
            }
        }
    }

    GetHeaderProps = (type, data, master, lobby, FixturedContest, isFrom, rootDataItem, teamData, teamName) => {
        if (teamName) {
            this.setState({ teamName: teamName })
        }
        this.setState({ lineupArr: data, masterData: master, LobyyData: lobby, FixturedContest: FixturedContest, isFrom: isFrom, rootDataItem: rootDataItem, team: teamData })
    }

    GetRosterEqHeaderProps = (data) => { // method call only for equity roster to resolve issue of clicking back arrow very frequently from c/vc screen 
        this.setState({ lineupArr: data })
    }

    showFilter = () => {
        this.props.showLobbyFitlers();
    }

    resetConfirm() {
        this.setState({ showResetAlert: true })
    }

    resetConfirmHide() {
        this.setState({ showResetAlert: false })
    }

    goToScreen = (pathname) => {
        this.props.history.push(pathname);
    }

    goToPrivateContestBanner = (pathname) => {
        let mSports = Utilities.getSelectedSportsForUrl().toLowerCase();
        let data = this.state.LobyyData;
        let dateformaturl = parseURLDate(data.season_scheduled_date);

        let contestListingPath = '/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl;
        this.props.history.push({ pathname: '/' + mSports + contestListingPath + '/private-contest-banner', state: { LobyyData: data } });

    }

    showWarningPopup = () => {
        this.setState({
            warningPopup: true
        })
    }

    goBackScreen = () => {
        if (this.props && this.props.location.state && this.props.location.state.isFrom == 'lineup-flow') {
            this.props.history.replace('/lobby' + "#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
        }
        else if (WSManager.getIsFromPayment() == 'true') {
            WSManager.setIsFromPayment(false)
            this.props.history.replace('/lobby' + "#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
        }
        else if (this.props && this.props.HeaderOption && this.props.HeaderOption.isFrom == "compTrue") {
            this.props.history.replace('/' + Utilities.getSelectedSportsForUrl() + '/dfs-completed-list')
        }
        else if (this.props && this.props.HeaderOption && this.props.HeaderOption.isFrom == "ndfs-tour") {
            this.props.history.replace('/' + Utilities.getSelectedSportsForUrl() + '/dfs-tournament-list')
        }
        else if (this.props && this.props.HeaderOption && this.props.HeaderOption.isFrom == "LBlobby") {
            this.props.history.replace('/lobby' + "#" + Utilities.getSelectedSportsForUrl())
        }
        else if (this.props && this.props.HeaderOption && this.props.HeaderOption.isFrom == "ftour"){
            this.props.history.replace('/sports-hub' + "#" + Utilities.getSelectedSportsForUrl())
        }
        
        else if (this.props && this.props.HeaderOption && this.props.HeaderOption.user_team_id) {
            this.showWarningPopup()
        } 
        else if(this.props && this.props.HeaderOption && (this.props.HeaderOption.isFrom == "PTDetail" || this.props.HeaderOption.isFrom == "DFSDetail" )) {
            // this.props.history.push('/lobby')
            this.props.history.goBack();
        }
        else {
            this.props.history.goBack();
        }


    }

    GobackToLobby = () => {
        if (this.props.HeaderOption.resetIndex && this.props.HeaderOption.resetIndex > 0) {
            this.props.history.go(-this.props.HeaderOption.resetIndex);
        } else {
            let urlParams = Utilities.setUrlParams(this.state.LobyyData);
            let sportsId = Utilities.getSelectedSportsForUrl();
            let collection_master_id = this.state.LobyyData.collection_master_id;
            this.props.history.push({ pathname: "/" + sportsId + '/my-teams/' + collection_master_id + "/" + urlParams, state: { LobyyData: this.state.LobyyData } })
        }

        this.setState({ showResetAlert: false })
        WSManager.clearLineup();
    }

    // calUCBal=()=>{
    //     this.getUserBal()
    //     this.setState({
    //         apiBalCalled: true
    //     })
    //     return 0
    //     // setTimeout(() => {
    //     //     let bal = this.state.userCoinBalnc
    //     //     return 
    //     // }, 100);
    // }

    renderLeftSection = (HeaderOpt) => {
        if (HeaderOpt.back) {
            var backAction = this.goBackScreen;
            if (HeaderOpt.showAlertRoster) {
                backAction = this.AlertgoBack
            } else if (HeaderOpt.goBackLobby) { 
                backAction = () => {
                    this.goToScreen("/lobby#" + Utilities.getSelectedSportsForUrl())
                }
            } else if (HeaderOpt.goBackProfile) {
                backAction = () => {
                    this.goToScreen("/my-profile")
                }
            } else if (HeaderOpt.goBackMore) {
                backAction = () => {
                    this.goToScreen("/more")
                }
            } else if (HeaderOpt.redirectTo) {
                backAction = () => {
                    this.goToScreen(HeaderOpt.redirectTo)
                }
            }
            return <>
                <a
                    style={{ width: !HeaderOpt.scoreCardShowStatus && HeaderOpt.backWidthGC ? 55 : HeaderOpt.backWidthGC ? 80 : '' }} className="header-action" onClick={backAction}><i className="icon-left-arrow"></i></a>
                {/* {HeaderOpt.profilePic && <img style={{
                    width: 32,
                    height: 32,
                    position: 'absolute',
                    left: 50,
                    pointerEvents: 'none',
                    top: 13,
                    borderRadius: 16
                }} src={Utilities.getThumbURL(HeaderOpt.profilePic)} alt='' />} */}
            </>
        }
        else if (WSManager.loggedIn() && HeaderOpt.title !== AppLabels.WHATSNEW && HeaderOpt.title !== CommonLabels.ADD_PLAYERS.toUpperCase()) {
            // let usrCBal = !this.state.apiBalCalled ? this.calUCBal() : 0;

            let WCount = this.state.userCoinBalnc ? (Utilities.numberWithCommas(Utilities.kFormatter(this.state.userCoinBalnc || 0)).length) : 1
            return (
                <div className={'center-container text-left' + ((Utilities.getMasterData().a_coin !== "0" && Number(this.state.userCoinBalnc) != 0) ? " coin-wall-ani" : "")} onClick={() => this.goToScreen('/my-wallet')}>
                    <a href className={"header-action" + (Utilities.getMasterData().a_coin !== "0" ? " coin-wall-ani" : "")}>

                        <span className="frontspan">
                            <i className="icon-wallet-ic"></i>
                        </span>
                        {
                            (Utilities.getMasterData().a_coin !== "0" && Number(this.state.userCoinBalnc) != 0) &&
                            <span className={"backspan " + (WCount > 5 && " WCount")}>{<img className="coin-img" src={Images.IC_COIN} alt="" />} {Utilities.numberWithCommas(Utilities.kFormatter(parseInt(this.state.userCoinBalnc)))}</span>
                        }
                    </a>
                </div>
            )
        }
        else if (!WSManager.loggedIn() && HeaderOpt.loginOpt) {
            return (
                <div className='center-container text-left' onClick={() => this.goToScreen('/signup')}>
                    <a href className="header-action">
                        <i className="icon-login"></i>
                    </a>
                </div>
            )
        }

    }

    renderMiddleSection = (HeaderOpt) => {
        if ((HeaderOpt.fixture || HeaderOpt.leaderboard) && this.state.LobyyData) {
            return <MatchInfo over={HeaderOpt.over} isHSI={this.props.isHSI} item={this.state.LobyyData} status={HeaderOpt.status} isH2H={HeaderOpt.h2hText} UserData={HeaderOpt.UserData || false} isbooster={HeaderOpt.isbooster || false} boosterdata={HeaderOpt.boosterdata || ''} isHideFlag={HeaderOpt.isHideFlag} />
        }
        else if (HeaderOpt.fixtureDate && this.state.LobyyData) {
            return <MatchInfo isHSI={this.props.isHSI} item={this.state.LobyyData} status={HeaderOpt.status} onlyTimeShow={HeaderOpt.fixtureDate} />
        }
        else if (HeaderOpt.title && HeaderOpt.FPPLeaderboard) {
            return <div className={'app-header-text selected-cat-text' + (this.state.filterBy != '' ? ' cat-name-exist' : '')}>
                {
                    this.state.filterBy != '' &&
                    <span>
                        {this.state.filterBy}
                    </span>
                }
                {HeaderOpt.title}
            </div>
        }
        else if (HeaderOpt.title && !HeaderOpt.FPPLeaderboard && !HeaderOpt.isBid) {
            return <div className={'app-header-text ' + (this.state.filterBy != '' ? ' cat-name-exist' : '') + (HeaderOpt.gameCenterH ? "  app-header-text-font-big" : " app-header-text") + (HeaderOpt.title_text_view ? " font-size-change-view" : "")}>
                {
                    HeaderOpt.livedot &&
                    <i className="livedot" />
                }
                {HeaderOpt.title}
                {
                    this.state.filterBy != '' &&
                    <div className="selected-cat-nm">{this.state.filterBy}</div>
                }
                {
                    HeaderOpt.showGTTitle != '' &&
                    <div className="game-text-title">{HeaderOpt.showGTTitle}</div>
                }
            </div>
        } else if (!HeaderOpt.back || HeaderOpt.MLogo) {
            return <div className='center-container'>
                <img onClick={() => this.goToScreen("/lobby#" + Utilities.getSelectedSportsForUrl())} className='header-brand-logo' alt="" src={DARK_THEME_ENABLE ? ((HeaderOpt.isPrimary || HeaderOpt.DFSPrimary) ? Images.DT_BRAND_LOGO : Images.DT_BRAND_LOGO) : (HeaderOpt.isPrimary || HeaderOpt.DFSPrimary) ? Images.WHITE_BRAND_LOGO : Images.BRAND_LOGO}></img>
                {/* <img onClick={() => this.goToScreen("/lobby#" + Utilities.getSelectedSportsForUrl())} className='header-brand-logo' alt="" src={DARK_THEME_ENABLE ? (HeaderOpt.isPrimary ? Images.DT_WHITE_BRAND_LOGO : Images.DT_BRAND_LOGO) : HeaderOpt.isPrimary ? Images.WHITE_BRAND_LOGO : Images.BRAND_LOGO}></img> */}
            </div>
        } else if (HeaderOpt.screentitle && HeaderOpt.leagueDate) {
            return (
                <React.Fragment>
                    <div className="match-info-section">

                        <div className="section-middle">
                            {
                                <span className={"team-home" + (HeaderOpt.minileague ? ' no-transform' : '')}>
                                    {HeaderOpt.screentitle}
                                </span>
                            }
                            {
                                <div className="match-timing">
                                    {
                                        HeaderOpt.leagueDate.end_date && !HeaderOpt.showleagueTime ?
                                            <span>
                                                {HeaderOpt.leagueDate.lbl ? `${HeaderOpt.leagueDate.lbl} | ` : ''}<MomentDateComponent data={{ date: HeaderOpt.leagueDate.scheduled_date, format: "DD MMM" }} /> -  <MomentDateComponent data={{ date: HeaderOpt.leagueDate.end_date, format: "DD MMM" }} />
                                            </span>
                                            :
                                            (HeaderOpt.leagueDate.game_starts_in && Utilities.showCountDown(HeaderOpt.leagueDate)) ?
                                                <div className="countdown time-line">
                                                    {HeaderOpt.leagueDate.lbl ? `${HeaderOpt.leagueDate.lbl} | ` : ''}{HeaderOpt.leagueDate.game_starts_in && (Utilities.minuteDiffValue({ date: HeaderOpt.leagueDate.game_starts_in }) <= 0) && <CountdownTimer deadlineTimeStamp={HeaderOpt.leagueDate.game_starts_in} />}
                                                    {
                                                        HeaderOpt.showleagueTime && HeaderOpt.leagueDate.end_date &&
                                                        <span className="date-sch">
                                                            <MomentDateComponent data={{ date: HeaderOpt.leagueDate.scheduled_date, format: "DD MMM hh:mm a" }} />
                                                            {
                                                                HeaderOpt.leagueDate.catID && HeaderOpt.leagueDate.catID.toString() === "1" ?
                                                                    <MomentDateComponent data={{ date: HeaderOpt.leagueDate.end_date, format: " - hh:mm a" }} />
                                                                    :
                                                                    <MomentDateComponent data={{ date: HeaderOpt.leagueDate.end_date, format: " - DD MMM hh:mm a" }} />
                                                            }
                                                        </span>
                                                    }
                                                </div>
                                                : <>{HeaderOpt.leagueDate.lbl ? `${HeaderOpt.leagueDate.lbl} | ` : ''}{HeaderOpt.leagueDate.scheduled_date &&
                                                    <>
                                                        {
                                                            (HeaderOpt.showleagueTime && HeaderOpt.leagueDate.end_date) ?
                                                                <>
                                                                    <MomentDateComponent data={{ date: HeaderOpt.leagueDate.scheduled_date, format: "DD MMM hh:mm a" }} />
                                                                    {
                                                                        HeaderOpt.leagueDate.catID && HeaderOpt.leagueDate.catID.toString() === "1" ?
                                                                            <MomentDateComponent data={{ date: HeaderOpt.leagueDate.end_date, format: " - hh:mm a" }} />
                                                                            :
                                                                            <MomentDateComponent data={{ date: HeaderOpt.leagueDate.end_date, format: " - DD MMM hh:mm a" }} />
                                                                    }
                                                                </>
                                                                :
                                                                <MomentDateComponent data={{ date: HeaderOpt.leagueDate.scheduled_date, format: "DD MMM - hh:mm A" }} />
                                                        }
                                                    </>
                                                }</>
                                    }
                                </div>
                            }
                        </div>
                    </div>
                </React.Fragment>
            )
        } else if (HeaderOpt.screenDatetitle) {
            return (
                <React.Fragment>
                    <div className="match-info-section">

                        <div className="section-middle">
                            {
                                HeaderOpt.isBid ?
                                    <span className={"team-home"}>
                                        {HeaderOpt.title}
                                    </span>
                                    :
                                    <span className={"team-home" + (HeaderOpt.minileague ? ' no-transform' : '')}>
                                        <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.scheduled_date, format: "hh:mm a" }} /> -  <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.end_date, format: "hh:mm a" }} />

                                        {/* <Moment date={HeaderOpt.screenDatetitle.scheduled_date} format={"hh:mm a"} /> - <Moment date={HeaderOpt.screenDatetitle.end_date} format={"hh:mm a" } /> */}
                                    </span>
                            }
                            {
                                <div className="match-timing">
                                    <span className="date-sch">
                                        <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.scheduled_date, format: "DD MMM" }} />
                                        {
                                            HeaderOpt.isBid &&
                                            <>
                                                <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.scheduled_date, format: " hh:mm a" }} /> -
                                                {
                                                    Constants.SELECTED_GAMET == Constants.GameType.LiveStockFantasy ?
                                                        <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.end_date, format: "DD MMM hh:mm a" }} /> :
                                                        <MomentDateComponent data={{ date: HeaderOpt.screenDatetitle.end_date, format: "hh:mm a" }} />
                                                }
                                            </>
                                            // <> <Moment date={HeaderOpt.screenDatetitle.scheduled_date} format={"hh:mm a"} /> - <Moment date={HeaderOpt.screenDatetitle.end_date} format={"hh:mm a" } /> </>
                                        }
                                    </span>
                                </div>
                            }
                        </div>
                    </div>
                </React.Fragment>
            )
        }
        else if (HeaderOpt.screentitle && HeaderOpt.rank) {
            return (
                <React.Fragment>
                    <div className="match-info-section">

                        <div className="section-middle">
                            {
                                <span className="team-home">
                                    {HeaderOpt.screentitle}
                                </span>
                            }
                            {
                                <div className="match-timing">
                                    {<span> {HeaderOpt.rank}</span>
                                    }
                                </div>
                            }
                        </div>
                    </div>

                </React.Fragment>
            )
        }
        else if (HeaderOpt.referalLeaderboradTitle && HeaderOpt.referalLeaderboradSubTitle) {
            return (
                <React.Fragment>
                    <div className="match-info-section">

                        <div className="section-middle">
                            {
                                <span className="team-home-rf-leaderboard">
                                    {HeaderOpt.referalLeaderboradTitle}
                                </span>
                            }
                            {/* {
                                <div className="match-timing-rf-leaderboard">
                                    {<span> {HeaderOpt.referalLeaderboradSubTitle}</span>
                                    }
                                </div>
                            } */}
                        </div>
                    </div>

                </React.Fragment>
            )
        }
        else if (HeaderOpt.headerType == 'scoreboard' && HeaderOpt.headerContent) {
            return (
                <React.Fragment>
                    <div className="match-info-section">
                        <div className="section-middle">
                            {
                                <span className="team-home-rf-leaderboard">
                                    {HeaderOpt.isMultiDFS ? HeaderOpt.headerContent.match_list[0].home : HeaderOpt.headerContent.home}
                                    {' ' + AppLabels.VS + ' '}
                                    {HeaderOpt.isMultiDFS ? HeaderOpt.headerContent.match_list[0].away : HeaderOpt.headerContent.away}
                                </span>
                            }
                        </div>
                    </div>

                </React.Fragment>
            )
        }
        else if (HeaderOpt.reminingBudget && HeaderOpt.reminingBudget != '') {
            return <div className="center-container remining-budget">
                {/* {Utilities.getMasterData().currency_code} */}
                {Utilities.numberWithCommas(parseFloat(Utilities.getExactValueSP(HeaderOpt.reminingBudget)))}
                {
                    HeaderOpt.brokerage && parseFloat(HeaderOpt.brokerage) > 0 &&
                    <>{' (' + HeaderOpt.brokerage + '%)'}</>
                }
            </div>
        }
        else if (HeaderOpt.stockContestTitle && HeaderOpt.stockContestTitle != '') {
            return <div className="center-container stk-contest">
                <div className="cont-ttl">{HeaderOpt.stockContestTitle}</div>
                {HeaderOpt.stockContestDate && HeaderOpt.stockContestDate != '' && <div className="cont-date">
                    <MomentDateComponent data={{ date: HeaderOpt.stockContestDate.scheduled_date, format: "D MMM hh:mm A " }} /> - <MomentDateComponent data={{ date: HeaderOpt.stockContestDate.end_date, format: "D MMM hh:mm A " }} />
                </div>}
            </div>
        }
        else if (HeaderOpt.viewPicks && HeaderOpt.teamName) {
            return (
                <React.Fragment>
                    <div className="match-info-section">
                        <div className="section-middle">
                            <div className="team-name">{HeaderOpt.teamName}</div>
                            {
                                <span className="teamdetail">
                                    {HeaderOpt.viewPicksData && HeaderOpt.viewPicksData.home}
                                    {' ' + AppLabels.VS + ' '}
                                    {HeaderOpt.viewPicksData && HeaderOpt.viewPicksData.away}
                                </span>
                            }
                        </div>
                    </div>

                </React.Fragment>
            )
        }
        else if (HeaderOpt.tourData && HeaderOpt.tourData != '') {
            return <div className="center-container tour-hdr">
                <div className="cont-ttl">{HeaderOpt.tourData.name}</div>
                <div className="cont-ttl-tl">
                    <MomentDateComponent data={{ date: HeaderOpt.tourData.start_date, format: "D MMM " }} /> -
                    <MomentDateComponent data={{ date: HeaderOpt.tourData.end_date, format: "D MMM " }} />
                </div>
            </div>
        }
        else if (HeaderOpt.centerStatus) {
            return <div className={HeaderOpt.status == 1 ? 'center-status-sec-live' : 'center-status-sec'}>{HeaderOpt.status == 1 && <img src={Images.WIDGET_IMG}/> }{HeaderOpt.centerStatus}</div>
        }
    }

    setlistPieStatus = (HeaderOpt, value) => {
        this.setState({ listPieStatus: value }, () => {
            HeaderOpt.listPieOption(value)

        })
    }

    onHRightBlcClick = (isCoins) => {
        if (isCoins) {
            this.props.history.push({ pathname: '/transactions', state: { tab: AppLabels.COINS } })
        } else {
            this.goToScreen('/my-wallet')
        }
    }

    renderRightSection = (HeaderOpt) => {
        let btnAction = HeaderOpt.showFilterByTeam ? HeaderOpt.showRosterFilter : this.showFilter;
        let isCoins = (window.location.pathname === '/earn-coins' || window.location.pathname === '/rewards');

        var resultReplace = AppLabels.YOU_CAN_WITHDRAW_ONLY_UPTO;
        var mapObj = {
            Rs: Utilities.getMasterData().currency_code,
            10000: Utilities.getMasterData().auto_withdrawal_limit
        };
        resultReplace = resultReplace.replace(/Rs|10000/gi, function (matched) {
            return mapObj[matched];
        });
        return (
            <React.Fragment>
                {
                    (HeaderOpt.showRS) &&
                    <a href className="header-action srulscr" onClick={HeaderOpt.showRSAction}>
                        <i className="icon-note"></i>
                    </a>
                }
                {
                    (HeaderOpt.skip) &&
                    <a href className="header-action skip-step" onClick={HeaderOpt.skipAction}>
                        {AppLabels.SKIP_STEP}
                    </a>
                }
                {
                    (HeaderOpt.filter || HeaderOpt.showFilterByTeam) &&
                    <a href className="header-action" onClick={btnAction}>
                        <i className="icon-filter"></i>
                        {
                            this.state.isFilterselected &&
                            <span className="filter-applied"></span>
                        }
                    </a>
                }
                {
                    (HeaderOpt.pitch && this.state.lineupArr.length > 0 && this.hidePitchToolTip()) &&
                    <a href className="header-action hide-sm-above" onClick={HeaderOpt.fieldViewAction}>
                        <i className="icon-ground"></i>
                        {this.state.showTooltip &&
                            <div className="onLoadTooltip">{AppLabels.TAB_TO_SEE_FIELD_VIEW}</div>
                        }
                    </a>
                }
                {
                    (HeaderOpt.edit) &&
                    <a href onClick={() => this.goToScreen('/edit-profile')} className="header-action">
                        <i className="icon-edit-line"></i>
                    </a>
                }
                {
                    (HeaderOpt.affiIcon) &&
                    <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                        <Tooltip id="tooltip" className="tooltip-featured">
                            <strong>{AppLabels.AFFILIATE_INFO_TEXT}</strong>
                        </Tooltip>
                    }>
                        <a href className="header-action with-affi-icon">
                            <i className="icon-info"></i>
                        </a>
                    </OverlayTrigger>
                }
                {
                    HeaderOpt.leaderIcon &&
                    <div className='info-icon-net-view'>
                        <OverlayTrigger trigger={['click']} placement="bottom" overlay={
                            <Tooltip id="tooltip" className=" tooltip-featured-container">

                                <storng>{AppLabels.leaderbaord_new_text1}</storng>
                            </Tooltip>
                        }>
                            <a href className="header-action header-action-new ">
                                <i className="icon-info"></i>
                            </a>
                        </OverlayTrigger>

                    </div>
                }

                {
                    (HeaderOpt.infoPredL) &&
                    <a href className="header-action info-ic" onClick={() => this.setState({ showPredLM: true })} >
                        <i className="icon-ic-info font-22">
                        </i>
                    </a>
                }
                {
                    (HeaderOpt.notification) &&
                    <a href className="header-action with-notification" onClick={() => this.goToScreen('/notification')}>
                        <i style={{ marginTop: 2 }} className="icon-alarm-new">
                            {
                                (this.state.notificationData && this.state.notificationData.count > 0) &&
                                <div style={{ textAlign: 'center', fontSize: 9, fontFamily: 'PrimaryF-Bold', position: 'absolute', top: -8, padding: '4px 0px', left: 8, height: 17, width: 17, borderRadius: "100%", backgroundColor: 'red', color: '#fff' }}>
                                    <span>{this.state.notificationData.count > 99 ? '99+' : this.state.notificationData.count}</span>
                                </div>
                            }
                        </i>
                    </a>
                }
                {
                    (HeaderOpt.howToPlayPrivate) &&
                    <a href className="header-action" onClick={() => this.goToPrivateContestBanner('/private-contest-banner')}>
                        <i className="icon-question">
                        </i>
                    </a>
                }
                {
                    (HeaderOpt.export) &&
                    <a href className="header-action" >
                        <i className="icon-export-ic">
                        </i>
                    </a>
                }
                {
                    (HeaderOpt.statusLeaderBoard) &&
                    <span className={"header-action status border-live" + (HeaderOpt.newLBD ? ' lbd' : '') + (HeaderOpt.statusLeaderBoard === Constants.CONTEST_LIVE ? '' : ' completed')}>
                        {
                            HeaderOpt.statusLeaderBoard == Constants.CONTEST_LIVE &&
                            <span className={"live-indicator " + (HeaderOpt.statusLeaderBoard === Constants.CONTEST_LIVE ? 'live' : '')} />
                        }
                        <span className={"status-text league-margin " + (HeaderOpt.statusLeaderBoard === Constants.CONTEST_LIVE ? 'live' : 'completed')}>{HeaderOpt.statusLeaderBoard === Constants.CONTEST_LIVE ? AppLabels.LIVE : AppLabels.COMPLETED}</span>
                    </span>
                }

                {
                    (HeaderOpt.status) &&
                    <span className={"header-action status " + (HeaderOpt.status == 1 || HeaderOpt.status == 2  ? " d-none " : '' ) + (HeaderOpt.statusBox ? (HeaderOpt.status === Constants.CONTEST_LIVE ? 'status-box-live' : 'status-box-completed') : '')}>
                        {
                            HeaderOpt.status == Constants.CONTEST_LIVE &&
                            <span className={"live-indicator  live-indicator-league" + (HeaderOpt.status == 1  ? " d-none " : '' ) + (HeaderOpt.status === Constants.CONTEST_LIVE ? 'live' : '')} />
                        }
                        <span className={"status-text " + (HeaderOpt.status == 1 || HeaderOpt.status == 2  ? " d-none " : '' ) + (HeaderOpt.status === Constants.CONTEST_LIVE ? 'live' : 'completed')}>{HeaderOpt.status === Constants.CONTEST_LIVE ? AppLabels.LIVE : AppLabels.COMPLETED}</span>
                    </span>
                }

                {
                    (HeaderOpt.close) &&
                    <a href className="header-action" onClick={this.goBackScreen}>
                        <i className="icon-close font-12"></i>
                    </a>
                }
                {
                    (HeaderOpt.earnCoin) &&
                    <a href className="header-action earn-coin-hdr-link" onClick={() => this.props.history.push('/earn-coins')}>
                        <img className="coin-img" src={Images.IC_COIN} alt="" />{AppLabels.EARN_COINS_LOWCASE}
                    </a>
                }
                {
                    (HeaderOpt.showBal) &&
                    <div className='right-container-bal-box' onClick={() => this.onHRightBlcClick(isCoins)}>
                        {
                            isCoins ? <div className='balance-box-style is-coin'>{<img className="coin-img" src={Images.IC_COIN} alt="" />} {Utilities.numberWithCommas(Utilities.kFormatter(this.state.userCoinBalnc))}</div>
                                : <div className='balance-box-style'>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(this.state.mTotalBalance)}</div>
                        }
                    </div>
                }
                {
                    (HeaderOpt.referAfriend) && WSManager.loggedIn() &&
                    <div className='right-container-refer-f' onClick={() => this.goToScreen('/refer-friend')}>
                        <img className="img-ref" src={Images.REFER_FRIEND_HEADER} alt='' />
                    </div>
                }
                {
                    (HeaderOpt.mute_status != undefined && HeaderOpt.mute_status != "2") && WSManager.loggedIn() &&
                    <div className='right-container-refer-f' onClick={HeaderOpt.rightAction}>
                        {HeaderOpt.mute_status && HeaderOpt.mute_status == '0' ?
                            <i className="icon-mute-ic"></i>
                            :
                            <i className="icon-un-mute-ic"></i>
                        }
                    </div>
                }
                {
                    (HeaderOpt.statusAll) &&
                    <span className={"header-action status " + (HeaderOpt.statusBox ? (HeaderOpt.statusAll === Constants.CONTEST_LIVE ? 'status-box-live' : 'status-box-completed') : '')}>
                        {
                            HeaderOpt.statusAll == Constants.CONTEST_LIVE &&
                            <span className={"live-indicator  live-indicator-league" + (HeaderOpt.statusAll === Constants.CONTEST_LIVE ? 'live' : '')} />
                        }
                        <span className={"status-text " + (HeaderOpt.statusAll === Constants.CONTEST_LIVE ? 'live' : 'completed')}>{HeaderOpt.statusAll === Constants.CONTEST_LIVE ? AppLabels.LIVE : HeaderOpt.statusAll === Constants.CONTEST_COMPLETED ? AppLabels.COMPLETED : AppLabels.UPCOMING}</span>
                    </span>
                }
                {
                    (HeaderOpt.info) &&
                    <a href className="header-action info-ic" onClick={() => HeaderOpt.infoAction ? HeaderOpt.infoAction() : null} >
                        <i className="icon-ic-info font-22">
                        </i>
                    </a>
                }
                {/* {
                    (HeaderOpt.scoreCardShowStatus) &&
                    <div className='scorecard-stats'>
                        {
                            Constants.AppSelectedSport == SportsIDs.cricket ?
                                <div onClick={(e) => HeaderOpt.infoAction ? HeaderOpt.infoAction(e) : null}>{AppLabels.SCORECARD_STATS}</div>
                                :
                                <div onClick={(e) => HeaderOpt.infoAction ? HeaderOpt.infoAction(e) : null}>{AppLabels.SHOW_STATS}</div>
                        }
                    </div>
                } */}
                {
                    (HeaderOpt.infoIcon && ((Utilities.getMasterData().auto_withdrawal_limit > 0 && Utilities.getMasterData().auto_withdrawal_limit) || Constants.DEFAULT_COUNTRY == "IND")) &&
                    (Utilities.getMasterData().allow_auto_withdrawal == "1" || Constants.DEFAULT_COUNTRY == "IND") &&
                    <div className='info-icon-net-view'>
                        {!HeaderOpt.isTdsText && <OverlayTrigger trigger={['click']} placement="bottom" overlay={
                            <Tooltip id="tooltip" className=" tooltip-featured-container">
                                <ul>
                                    {(Utilities.getMasterData().allow_auto_withdrawal == "1" && Utilities.getMasterData().auto_withdrawal_limit > 0) &&
                                        <li>
                                            {resultReplace}
                                        </li>
                                    }

                                    {(Constants.DEFAULT_COUNTRY == "IND" && !_isEmpty(Utilities.getMasterData().allow_tds)) &&
                                        <>
                                            <li className='mt-2'> {AppLabels.NEY_WINNING_FOR}</li>
                                            <li className='mt-2'> {AppLabels.AMOUNY_PAYABLE_IS_CALCULATE}</li>
                                        </>
                                    }
                                </ul>

                            </Tooltip>
                        }>
                            <a href className="header-action with-affi-icon">
                                <i className="icon-info"></i>
                            </a>
                        </OverlayTrigger>}
                        {HeaderOpt.isTdsText && <OverlayTrigger trigger={['click']} placement="bottom" overlay={
                            <Tooltip id="tooltip" className=" tooltip-featured-container">
                                <ul>
                                    {(Utilities.getMasterData().allow_auto_withdrawal == "1" && Utilities.getMasterData().auto_withdrawal_limit > 0) &&
                                        <li className='block-li'>
                                            {AppLabels.TDS_DASHBOARD_TOOLTIP}
                                        </li>
                                    }


                                </ul>

                            </Tooltip>
                        }>
                            <a href className="header-action with-affi-icon">
                                <i className="icon-info"></i>
                            </a>
                        </OverlayTrigger>}
                    </div>
                }

                {
                    (HeaderOpt.rightSection) &&
                    <div className="header-pie-list-view">
                        <div className={"list-view-container" + (this.state.listPieStatus == 0 ? ' active' : '')}>
                            <i className={"icon-list-ic" + (this.state.listPieStatus == 0 ? ' list-active' : '')} onClick={() => this.setlistPieStatus(HeaderOpt, 0)} >

                            </i>
                        </div>
                        <div className={"pi-view-container" + (this.state.listPieStatus == 1 ? ' active' : '')} >
                            <i className={"icon-pie" + (this.state.listPieStatus == 1 ? ' pi-active' : '')} onClick={() => this.setlistPieStatus(HeaderOpt, 1)} >

                            </i>
                        </div>

                    </div>

                }
                {
                    (HeaderOpt.ShowRuleScoring) &&
                    <a href className="header-action" onClick={(e) => HeaderOpt.RuleScoringFn(e)}>
                        <i className="icon-question">
                        </i>
                    </a>
                }
                {
                    (HeaderOpt.editpick) &&
                    <a href onClick={(e) => HeaderOpt.editpickFn(e)} className="header-action">
                        <i className="icon-edit-line"></i>
                    </a>
                }





                {(HeaderOpt.tourData && HeaderOpt.tourData != '') &&
                    <div className="center-container tour-hdr">
                        <div className="cont-date">
                            {
                                HeaderOpt.tourData.status == 3 || HeaderOpt.tourData.status == 2 ?
                                    <span className="comp-sec">{AppLabels.COMPLETED}</span>
                                    :
                                    <>
                                        {
                                            // Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(HeaderOpt.tourData.start_date), 'YYYY-MM-DD HH:mm ')
                                            isDateTimePast(HeaderOpt.tourData.start_date)
                                                ?
                                                <span className="live-sec"><span></span> {AppLabels.LIVE}</span>
                                                :
                                                <>
                                                    {/* {
                                                        Utilities.showCountDown({ game_starts_in: HeaderOpt.tourData.game_starts_in })
                                                        &&
                                                        <div className={"countdown-timer-section"}>
                                                            {
                                                                HeaderOpt.tourData.game_starts_in && <CountdownTimer
                                                                    timerCallback={() => { }}
                                                                    deadlineTimeStamp={HeaderOpt.tourData.game_starts_in} />
                                                            }
                                                        </div>
                                                        // :
                                                        // <>
                                                        //     <MomentDateComponent data={{ date: HeaderOpt.tourData.start_date, format: "D MMM " }} /> -
                                                        //     <MomentDateComponent data={{ date: HeaderOpt.tourData.end_date, format: "D MMM " }} />
                                                        // </>
                                                    } */}
                                                </>
                                        }
                                    </>
                            }
                        </div>
                    </div>
                }
            </React.Fragment>
        )
    }

    static showNewPToast = (msg) => {
        globalThis.setState({
            NEWP: true,
            NPMSG: msg ? msg : ''
        })
        setTimeout(() => {
            globalThis.setState({
                NEWP: false
            })
        }, 4000);
    }

    renderNewPrediction = () => {
        const { NPMSG } = this.state;
        return (
            <div onClick={() => Utilities.scrollToTop()} className="new-data-toast">
                <i className="icon-alarm-new primary" />
                <div className="text-msg">{NPMSG ? NPMSG : AppLabels.NEW_PRE}</div>
            </div>
        )
    }

    static showBanStateModal = (banStateData) => {
        globalThis.setState({ showBanStateModal: true, banStateData: banStateData, isForShare: banStateData.isFromShare || false })
    }

    hideBanStateModal = (data, page) => {
        this.setState({ showBanStateModal: false })
        let banStates = Object.keys(Utilities.getMasterData().banned_state || {});
        if (data && banStates.includes(data.master_state_id)) {
            if (page == 'addFunds') {
                CustomHeader.showBanStateMSGModal({ isFrom: 'addFunds', title: 'You are unable to Deposit Funds', Msg1: 'Sorry, but players from ', Msg2: ' are not able to deposit funds at this time' });
            }
            if (page == 'CL' || page == 'CAP') {
                CustomHeader.showBanStateMSGModal({ isFrom: page, title: 'Important', Msg1: 'Sorry, If you are currently living in ', Msg2: ' you cannot play cash games' });
            }
        }
        else {
            if (this.props.HeaderOption && this.props.HeaderOption.goBackLobby) {
                this.props.history.push('/')
            }
        }
    }
    static showBanStateMSGModal = (banStateMSGData) => {
        globalThis.setState({ showBanStateMSGModal: true, banStateMSGData: banStateMSGData, isForShare: banStateMSGData.isFromShare || false })
    }

    hideBanStateMSGModal = () => {
        this.setState({ showBanStateMSGModal: false })
    }

    hidePredLM = () => {
        this.setState({
            showPredLM: false
        })
    }
    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        this.setState({
            soff: scrollOffset
        })

    }

    hidePropsModal = () => {
        // this.props.history.push('/my-contests')
        this.setState({
            warningPopup: false
        })
    }


    onMyAlertHide = () => {
        this.props.history.push('/my-contests')
    }

    UNSAFE_componentWillMount = () => {
        window.addEventListener('scroll', this.onScrollList);
    }

    render() {
        const {
            HeaderOption
        } = this.props;

        const {
            showDCBM,
            dailyData,
            showRedeemCM,
            showRSuccess,
            redeemData,
            showResetAlert,
            message,
            isBannerShow,
            NEWP,
            sourceUrlShow,
            sourceUrlData,
            showSpinWheel,
            spinWheelData,
            showBanStateModal,
            banStateData,
            showBanStateMSGModal,
            banStateMSGData,
            showRGIModal,
            ApiCalled,
            showLBModal,
            showPredLM,
            showUniLf,
            soff,
            warningPopup
        } = this.state;

        let isCoins = (window.location.pathname === '/earn-coins' || window.location.pathname === '/rewards');
        let isWeb = window.ReactNativeWebView ? false : true;
        globalThis = this;
        let user_uni_id_base = localStorage.getItem('user_id') || ''
        let user_match_id = JSON.parse(localStorage.getItem('profile')) || ''
        let user_uni_id = user_uni_id_base ? user_uni_id_base.replaceAll(/['"]+/g, '') : '';

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={(soff > 10 ? 'app-header-style header-fixed-backround ' : ' app-header-style  ') + (HeaderOption.showColorHeader ? ' share-pred' : ' ') + (HeaderOption.hideHeader ? ' hide ' : '') + (HeaderOption.hideShadow ? 'hide-shadow' : '') + (HeaderOption.themeHeader ? ' header-transparent' : '') + (isCoins || HeaderOption.isPrimary ? ' coin-headr ' : '') + (HeaderOption.status == 1 ? " live-widget-status" : '' ) }>

                        {/* UnreadNotification */}
                        <UnreadNotification {...{
                            getAPiNotificationCount: this.getAPiNotificationCount,
                            rule: Constants.SELECTED_GAMET != Constants.GameType.Free2Play
                        }} />
                        <div className='row-container'>
                            {
                                NEWP && this.renderNewPrediction()
                            }
                            <div className='section-min section-left'>
                                {
                                    this.renderLeftSection(HeaderOption)
                                }
                            </div>
                            <div className='section-middle'>
                                {
                                    this.renderMiddleSection(HeaderOption)
                                }
                            </div>

                            <div xs={2} className='section-min section-right'>
                                {
                                    this.renderRightSection(HeaderOption)
                                }
                            </div>
                        </div>
                        {
                            Utilities.getMasterData().a_coin !== "0" &&
                            <React.Fragment>
                                {
                                    !HeaderOption.isOnb && showDCBM &&
                                    //    Utilities.getMasterData().a_spin != 1 && 
                                    <Suspense fallback={<div />} ><DailyCheckinBonus {...this.props} preData={{
                                        dailyData: dailyData,
                                        mShow: showDCBM,
                                        mHide: this.hideDailyCheckIn
                                    }} /></Suspense>
                                }
                                {
                                    !showDCBM && showSpinWheel && this.state.isSkipSpin &&
                                    // (window.location.pathname === '/lobby' || window.location.pathname === '/earn-coins') && 
                                    <Suspense fallback={<div />} ><SpeenWheelModal {...this.props} preData={{
                                        data: spinWheelData,
                                        mHide: this.hideSpinWheel,
                                        showSpinWheel: showSpinWheel,
                                        updateUserBal: this.getUserBal,
                                        succSpinWheel: this.succSpinWheel
                                    }} /></Suspense>
                                }
                                {
                                    !showDCBM && !showSpinWheel && showRedeemCM && <Suspense fallback={<div />} ><ReedemCoachMarks {...this.props} cmData={{
                                        mHide: this.hideRedeemCM,
                                        mShow: showRedeemCM
                                    }} /></Suspense>
                                }
                                {
                                    showRSuccess && <Suspense fallback={<div />} ><RedeemSuccess {...this.props} rmData={{
                                        redeemData: redeemData,
                                        mShow: showRSuccess,
                                        mHide: this.hideRSuccess
                                    }} /></Suspense>
                                }
                            </React.Fragment>
                        }
                        {
                            (ApiCalled && !showDCBM && !HeaderOption.isOnb && !showSpinWheel && isBannerShow) &&
                            <Suspense fallback={<div />} ><Banner
                                {...this.props}
                                isBannerShow={true}
                                onBannerHide={() => this.onBannerHide()}
                            /></Suspense>
                        }

                        {
                            Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy && ApiCalled && !showDCBM && !showSpinWheel && !isBannerShow && window.location.pathname === '/lobby' && showRGIModal &&
                            <RGIModal showM={showRGIModal} hideM={this.RGIModalHide} />
                        }
                        {
                            showResetAlert &&
                            <Suspense fallback={<div />} ><MyAlert isMyAlertShow={showResetAlert} onMyAlertHide={() => this.GobackToLobby()} hidemodal={() => this.resetConfirmHide()} message={message} /></Suspense>
                        }
                        {
                            sourceUrlShow &&
                            <Suspense fallback={<div />} ><OpenSourceUrl mShow={sourceUrlShow} mHide={this.hideUrmModal} UrlData={sourceUrlData} /></Suspense>
                        }

                        {
                            showBanStateModal && ((!showDCBM && !isBannerShow) || this.state.isForShare) && Utilities.getMasterData().a_aadhar != "1" &&
                            <BanStateModal
                                {...this.props}
                                mShow={showBanStateModal}
                                mHide={this.hideBanStateModal}
                                banStateData={banStateData}
                            />
                        }
                        {
                            showBanStateMSGModal && ((!showDCBM && !isBannerShow) || this.state.isForShare) &&
                            <BanStateMSGModal
                                {...this.props}
                                mShow={showBanStateMSGModal}
                                mHide={this.hideBanStateMSGModal}
                                banStateMSGData={banStateMSGData}
                            />
                        }
                        {
                            showLBModal &&
                            <Suspense fallback={<div />} >
                                <LeaderboardModal
                                    {...this.props}
                                    mShow={showLBModal}
                                    mHide={this.LBModalHide}
                                />
                            </Suspense>
                        }
                        {
                            !showDCBM && !showSpinWheel && !isBannerShow && window.location.pathname === '/lobby' && !showRGIModal && Constants.CMStatus != 0
                            && user_uni_id != user_match_id.user_unique_id &&
                            <LobbyCoachMarkIndex />
                        }
                        {
                            showPredLM && <PredictionLearnMore {...this.props} preData={{
                                mShow: showPredLM,
                                mHide: this.hidePredLM
                            }} />
                        }
                        {
                            isWeb &&
                            <LiveOverSocket />
                        }
                        {
                            warningPopup &&
                            <PropsWarningPopup warningPopup={warningPopup} hidePropsModal={this.hidePropsModal} onMyAlertHide={this.onMyAlertHide} {...this.props} />
                        }
                    </div>
                )}

            </MyContext.Consumer>
        )
    }
}

export default CustomHeader
