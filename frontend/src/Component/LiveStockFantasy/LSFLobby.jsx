import React, { lazy, Suspense } from 'react';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLSFLbyContestLst, getLSFMyLobbyContest, getLSFLobbyFilter, getSPUserLineupList, LSFJoinContest } from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _Map, BannerRedirectLink, parseURLDate, _isUndefined, checkBanState, _filter } from '../../Utilities/Utilities';
import { NoDataView, LobbyBannerSlider, LobbyShimmer } from '../../Component/CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as AL from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import MetaComponent from '../../Component/MetaComponent';
import LSFFixtureCard from "./LSFFixtureCard";
// import SPFixtureCard from './SPFixtureCard';
import LSFMyContestSlider from './LSFMyContestSlider';
import MyAlert from '../../Modals/MyAlert';
import { Thankyou, ContestDetailModal, ConfirmationPopup, UnableJoinContest, ShareContestModal, ShowMyAllTeams } from '../../Modals';
import moment from 'moment';
import LSFHTPP from './LSFHTPP';
const LSFHTP = lazy(() => import('./LSFHTP'));
const LSFLobbyFilter = lazy(() => import('./LSFLobbyFilter'));
const LSFRules = lazy(() => import('./LSFRules'));

var bannerData = {}

class LSFLobby extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            FixtureList: [],
            BannerList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            isListLoading: false,
            showHTP: false,
            showShadow: false,
            stockSetting: [],
            stockStatistic: !_isUndefined(props.location.state) ? props.location.state.stockStatistic : false,
            contestListing: !_isUndefined(props.location.state) ? props.location.state.contestListing : false,
            pushListing: !_isUndefined(props.location.state) ? props.location.state.pushListing : [],
            showLobbyFitlers: false,
            filterList: {},
            minCT: "",
            maxCT: "",
            minFee: "",
            maxFee: "",
            minEnt: "",
            maxEnt: "",
            minWin: "",
            maxWin: "",
            dayFilter: "",
            fromDate: "",
            toDate: "",
            showRules: false,
            showAlert: false,
            showContestDetail: false,
            activeTab: "",
            TeamList: [],
            TotalTeam: [],
            showConfirmationPopUp: false,
            showThankYouModal: false,
            lineup_master_idArray: [],
            MyStockList: [],
            isFilerApplied: false,
            showTimeOutAlert: false
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        // if (this.state.sports_id != nextProps.selectedSport) {
        //     this.reload(nextProps);
        // }
        if (this.state.showLobbyFitlers != nextProps.showLobbyFitlers) {
            this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers })
        }
    }

    /**
     * @description - this is life cycle method of react
     */
    componentDidMount() {
        setTimeout(() => {
            // if(this.state.stockStatistic){
            //     this.props.history.push('/stock-fantasy/statistics')  
            // }
            // if(this.state.contestListing){
            //     this.gotoDetails(this.state.pushListing) 
            // }
        }, 300);
        if (this.props.location.pathname == '/lobby') {
            this.checkOldUrl();
            this.getLobbyFilters();
            this.getLobbyFixture();
            setTimeout(() => {
                this.getBannerList();
            }, 1500);
            WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_fixture');
            if (WSManager.loggedIn()) {
                // this.callLobbySettingApi()
                this.getMyFixtures()
                WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            }
            window.addEventListener('scroll', this.onScrollList);
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'SessionKey',
                    targetFunc: 'SessionKey',
                    page: 'lobby',
                    SessionKey: WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '',
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            this.handelNativeGoogleLogin()
            if (!ls.get('isDeviceTokenUpdated')) {

                let token_data = {
                    action: 'push',
                    targetFunc: 'push',
                    type: 'deviceid',
                }
                this.sendMessageToApp(token_data)
            }
            setTimeout(() => {
                let push_data = {
                    action: 'push',
                    targetFunc: 'push',
                    type: 'receive',
                }
                this.sendMessageToApp(push_data)
            }, 300);
            WSManager.clearLineup();
        }
    }

    onScrollList = () => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0) {
            this.setState({
                showShadow: true
            })
        }
        else {
            this.setState({
                showShadow: false
            })
        }
    }

    UNSAFE_componentWillMount = () => {
        this.enableDisableBack(false)
    }

    checkOldUrl() {
        let url = window.location.href;
        if (!url.includes('#live-stock-fantasy')) {
            url = url + "#live-stock-fantasy";
        }
        window.history.replaceState("", "", url);
    }

    enableDisableBack(flag) {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                type: flag,
                targetFunc: 'back'
            }
            this.sendMessageToApp(data);
        }
    }

    componentWillUnmount() {
        this.enableDisableBack(false);
        window.removeEventListener('scroll', this.onScrollList);
    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

    handelNativeGoogleLogin() {
        window.addEventListener('message', (e) => {

            if (e.data.locale) {
                WSManager.setAppLang(e.data.locale);

            }
            if (e.data.UserProfile) {
                WSManager.setProfile(e.data.UserProfile);
            }
            if (e.data.LoginSessionKey) {
                WSManager.setToken(e.data.LoginSessionKey);
            }
            if (e.data.isMobileApp) {
                WSManager.setIsMobileApp(e.data.isMobileApp);
            }

            if (e.data.action === 'push' && e.data.type === 'deviceid') {
                if (e.data.token && e.data.token.toString() !== (WSC.DeviceToken.getDeviceId() || '').toString()) {
                    WSC.DeviceToken.setDeviceId(e.data.token);
                    this.updateDeviceToken();
                }
            }
            else if (e.data.action === 'push' && e.data.type === 'receive') {
                WSManager.setPickedGameType(Constants.GameType.DFS)
                let pathName = '';
                let pushStockType = ['560', '561', '562', '563', '566', '567', '568'];
                if (pushStockType.indexOf(e.data.notif.notification_type) > -1) {
                    if (e.data.notif.stock_type && e.data.notif.stock_type == '2') {
                        WSManager.setPickedGameType(Constants.GameType.StockFantasyEquity)
                    }
                    else {
                        WSManager.setPickedGameType(Constants.GameType.StockFantasy)

                    }
                    if (e.data.notif.notification_type == '560' || e.data.notif.notification_type == '563' || e.data.notif.notification_type == '566' || e.data.notif.notification_type == '568') {
                        this.props.history.push({ pathname: '/lobby' });
                    }
                    else if (e.data.notif.notification_type == '567') {
                        let pushListingObj = {}
                        pushListingObj['category_id'] = e.data.notif.category_id;
                        pushListingObj['collection_id'] = e.data.notif.collection_id;
                        this.gotoDetails(pushListingObj)
                    }
                    else if (e.data.notif.notification_type == '561' || e.data.notif.notification_type == '562') {
                        this.props.history.push('/stock-fantasy/statistics')
                    }
                    return;
                }
                if ((e.data.notif.notification_type || '').toString() === '120') {//deposit promotion
                    pathName = 'add-funds';
                }
                else if ((e.data.notif.notification_type || '').toString() === '121') {// promotion for contes
                    pathName = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest/' + e.data.notif.contest_unique_id
                }
                else if ((e.data.notif.notification_type || '').toString() === '123') {// admin refer a friend
                    pathName = 'refer-friend';
                }
                else if (e.data.notif.notification_type == '124' ||
                    e.data.notif.notification_type == '131' ||
                    e.data.notif.notification_type == '132' ||
                    e.data.notif.notification_type == '300' ||
                    e.data.notif.notification_type == '442' ||
                    e.data.notif.notification_type == '443' ||
                    e.data.notif.notification_type == '440') {//124-promotion for fixture 131-match delay  132-lineup announced
                    WSManager.setPickedGameType(Constants.GameType.DFS)
                    ls.set('selectedSports', e.data.notif.sports_id);
                    Constants.setValue.setAppSelectedSport(e.data.notif.sports_id);
                    let dateformaturl = parseURLDate(e.data.notif.season_scheduled_date);
                    let data = e.data.notif;
                    let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
                    this.props.history.push({ pathname: contestListingPath.toLowerCase() })
                    contestListingPath = '';
                    return;

                }
                else if ((e.data.notif.notification_type || '').toString() === '135') {// custom notification
                    if (parseInt(e.data.notif.custom_notification_type || '0') === 1) {
                        pathName = 'lobby';
                    }
                    else if (parseInt(e.data.notif.custom_notification_type || '0') === 2) {
                        pathName = 'my-wallet';
                    }
                    else if (parseInt(e.data.notif.custom_notification_type || '0') === 3) {
                        pathName = 'my-profile';
                    }
                    else if (parseInt(e.data.notif.custom_notification_type || '0') === 4) {
                        pathName = 'my-contests?contest=upcoming';
                    }
                    else if (parseInt(e.data.notif.custom_notification_type || '0') === 5) {
                        pathName = 'refer-friend';
                    }
                    else if (parseInt(e.data.notif.custom_notification_type || '0') === 7) {
                        pathName = 'add-funds';
                    }
                    else {
                        pathName = 'lobby';
                    }
                }
                if (pathName && pathName.trim() !== '') {
                    this.props.history.push({ pathname: pathName });
                }
            }
            else if (e.data.action === 'app_dep_linking' && e.data.type === 'android') {
                let can = ls.get('canRedirect');
                if (can == null || can) {
                    this.blockMultiRedirection()
                    let pathName = e.data.pathName;
                    if (pathName) {
                        this.props.history.push(pathName);
                    }
                }
            }
            else if (e.data.action === 'app_dep_linking' && e.data.type === 'reset') {
                ls.set('canRedirect', true)
            }
        });
    }

    blockMultiRedirection() {
        ls.set('canRedirect', false)
        setTimeout(() => {

            ls.set('canRedirect', true)
        }, 1000 * 5);
    }

    updateDeviceToken = () => {
        let param = {
            "device_type": WSC.deviceTypeAndroid,
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if (WSManager.loggedIn()) {
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    /** 
    * @description api call to get stock filter list from server
    */
    getLobbyFilters = () => {
        getLSFLobbyFilter().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    filterList: responseJson.data
                })
            }
        })
    }

    /** 
    * @description api call to get stock fixture listing from server
    */
    getLocalToUTCTime = (value) => {
        var date = new Date();
        var IndHR = value.split(":")[0]
        var IndMin = value.split(":")[1]

        date.setHours(IndHR)
        date.setMinutes(IndMin)

        var now_utc = new Date(date.getTime() + date.getTimezoneOffset() * 60000)
        const formatted = moment(now_utc).format('hh:mm');
        return formatted
    }

    /** 
    * @description api call to get stock fixture listing from server
    */
    getLobbyFixture = async () => {
        this.setState({ isListLoading: true })

        let MinCTUTC = this.state.minCT == '' ? '' : this.getLocalToUTCTime(this.state.minCT)
        let MaxCTUTC = this.state.maxCT == '' ? '' : this.getLocalToUTCTime(this.state.maxCT)

        let param = {
            "min_time": MinCTUTC,
            "max_time": MaxCTUTC,
            "min_fee": this.state.minFee,
            "max_fee": this.state.maxFee,
            "min_entries": this.state.minEnt,
            "max_entries": this.state.maxEnt,
            "min_winning": this.state.minWin,
            "max_min_winning": this.state.maxWin,
            "from_date": this.state.fromDate,
            "to_date": this.state.toDate
        }
        var res = await getLSFLbyContestLst(param);
        if (res.data && res.data) {
            let fArray = _filter(res.data.contest, (obj) => {
                let dateObj = Utilities.getUtcToLocal(obj.schedule_date)
                return Utilities.minuteDiffValue({ date: dateObj }) < 0
            })
            this.setState({ isListLoading: false, FixtureList: res.data.contest })
        } else {
            this.setState({ isListLoading: false })
        }
    }

    /** 
    * @description api call to get joined stock fixture listing from server
    */
    getMyFixtures = async () => {
        let param = {
            // "page_no": "1",
            // "page_size": "20",
            // "stock_type":"3"
        }
        var res = await getLSFMyLobbyContest(param);
        if (res.data && res.data) {
            this.setState({ MyStockList: res.data })
        }
    }

    /** 
     * @description api call to get baner listing from server
    */
    getBannerList = () => {
        let sports_id = Constants.AppSelectedSport;

        if (sports_id == null)
            return;
        if (bannerData[sports_id]) {
            this.parseBannerData(bannerData[sports_id])
        } else {
            setTimeout(async () => {
                this.setState({ isLoaderShow: true })
                let param = {
                    "sports_id": sports_id
                }
                var api_response_data = await getLobbyBanner(param);
                if (api_response_data && param.sports_id.toString() === Constants.AppSelectedSport.toString()) {
                    bannerData[sports_id] = api_response_data;
                    this.parseBannerData(api_response_data)
                }
                this.setState({ isLoaderShow: false })
            }, 1500);
        }
    }

    /** 
     * @description call to parse banner data
    */
    parseBannerData = (bdata) => {
        let temp = [];
        _Map(this.getSelectedbanners(bdata), (item, idx) => {
            if (item.banner_type_id == 1) {
                let dateObj = Utilities.getUtcToLocal(item.schedule_date)
                if (Utilities.minuteDiffValue({ date: dateObj }) < 0) {
                    temp.push(item);
                }
            }
            else {
                temp.push(item);
            }
        })
        this.setState({ BannerList: temp })
    }

    /** 
     * @description call to get selected banner data
    */
    getSelectedbanners(api_response_data) {
        let tempBannerList = [];
        for (let i = 0; i < api_response_data.length; i++) {
            let banner = api_response_data[i];
            if (WSManager.getToken()) {
                if(banner.game_type_id == 0 || 
                    banner.game_type_id == 10 ||
                    banner.game_type_id == 13 ||
                    banner.game_type_id == 27 ||
                    banner.game_type_id == 39
                ){
                    if (parseInt(banner.banner_type_id) === Constants.BANNER_TYPE_REFER_FRIEND
                        || parseInt(banner.banner_type_id) === Constants.BANNER_TYPE_DEPOSITE) {
                        if (banner.amount > 0)
                            tempBannerList.push(api_response_data[i]);
                    }
                    else if (banner.banner_type_id === '6') {
                        //TODO for banner type-6 add data
                    }
                    else {
                        tempBannerList.push(api_response_data[i]);
                    }
                }
            }
            else {
                if (banner.banner_type_id === '6' && (banner.game_type_id == 0 || 
                    banner.game_type_id == 10 ||
                    banner.game_type_id == 13 ||
                    banner.game_type_id == 27 ||
                    banner.game_type_id == 39
                )) {
                    tempBannerList.push(api_response_data[i]);
                }
            }
        }

        return tempBannerList;
    }

    /**
     * @description method to redirect user on appopriate screen when user click on banner
     * @param {*} banner_type_id - id of banner on which clicked
     */
    redirectLink = (result) => {
        BannerRedirectLink(result, this.props)
    }

    goToMyContest = () => {
        this.props.history.push({ pathname: '/my-contests' });
    }

    showHTPModal = (e) => {
        e.stopPropagation()
        this.setState({
            showHTP: true
        })
    }

    hideHTPModal = () => {
        this.setState({
            showHTP: false
        })
    }

    showRulesModal = (e) => {
        e.stopPropagation()
        this.setState({
            showRules: true
        })
    }

    hideRulesModal = () => {
        this.setState({
            showRules: false
        })
    }

    /**
     * 
     * @description method to display confirmation popup model, when user join contest.
     */
    ConfirmatioPopUpShow = () => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }
    /**
     * 
     * @description method to hide confirmation popup model
     */
    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    playNow = (item) => {
        if (WSManager.loggedIn()) {
            this.gotoDetails(item)
        }
        else {
            this.goToSignup()
        }
    }
    btnAction = (item) => {
        if (WSManager.loggedIn()) {
            item['collection_master_id'] = item.collection_id;
            if (parseInt(item.status || '0') > 1 || parseInt(item.is_live || '0') === 1) {
                this.props.history.push({ pathname: '/my-contests', state: { from: parseInt(item.is_live || '0') === 1 ? 'lobby-live' : 'lobby-completed' } });
            } else {
                this.gotoDetails(item)
            }
        }
        else {
            this.goToSignup()
        }
    }

    goToSignup = () => {
        this.props.history.push("/signup")
    }

    gotoDetails = (data) => {
        data['collection_master_id'] = data.collection_id;
        let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let contestListingPath = '/stock-fantasy/contest/' + data.collection_id + '/' + name;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET)
        this.props.history.push({ pathname: CLPath, state: { LobyyData: data, lineupPath: CLPath } })
    }

    showLFilter = () => {
        this.setState({
            showLobbyFitlers: true
        })
    }

    hideLFilter = () => {
        // this.setState({
        //     showLobbyFitlers: false
        // })
        this.setState({ showLobbyFitlers: false })
        this.props.hideFilterData()
    }

    setFilter = (minCT, maxCT, minFee, maxFee, minEnt, maxEnt, minWin, maxWin, isFilerApplied) => {
        // const {minCT, maxCT, minFee, maxFee, minEnt, maxEnt, minWin, maxWin} = this.state;
        // if(isfor == 1){ // 1 is for contest time
        //     this.setState({
        //         minCT: minCT == minVal ? "" : minVal,
        //         maxCT: maxCT == maxVal ? "" : maxVal,
        //         isFilerApplied: true
        //     })
        // }
        // else if(isfor == 2){ // 2 is for entry fee
        //     this.setState({
        //         minFee: minFee == minVal ? "" : minVal, 
        //         maxFee: maxFee == maxVal ? "" : maxVal,
        //         isFilerApplied: true
        //     })
        // }
        // else if(isfor == 3){ //3 is for entries
        //     this.setState({
        //         minEnt: minEnt == minVal ? "" : minVal,
        //         maxEnt: maxEnt == maxVal ? "" : maxVal,
        //         isFilerApplied: true
        //     })
        // }
        // else if(isfor == 4){ //3 is for winning
        //     this.setState({
        //         minWin: minWin == minVal ? "" : minVal,
        //         maxWin: maxWin == maxVal ? "" : maxVal,
        //         isFilerApplied: true
        //     })
        // }
        // else if(isfor == 0){ //0 to clear filter
        this.setState({
            minCT: minCT,
            maxCT: maxCT,
            minFee: minFee,
            maxFee: maxFee,
            minEnt: minEnt,
            maxEnt: maxEnt,
            minWin: minWin,
            maxWin: maxWin,
            isFilerApplied: isFilerApplied,
            dayFilter: isFilerApplied && this.state.dayFilter == "" ? "1" : this.state.dayFilter
        }, () => {
            this.hideLFilter()
            this.getLobbyFixture()
        })
        // }
    }

    ApplyFilter = () => {
        this.hideLFilter()
        this.getLobbyFixture()
    }

    addDayFilter = (val) => {
        if (val == 1) {
            let today = Utilities.getFormatedDate({ date: new Date(), format: '' })
            today = today.split('T')[0];
            this.setState({
                dayFilter: val,
                fromDate: today,
                toDate: today,
            }, () => {
                this.getLobbyFixture()
            })
        }
        else if (val == 2) {
            const TD = new Date()
            let tomorrow = new Date()
            tomorrow.setDate(TD.getDate() + 1)
            tomorrow = Utilities.getFormatedDate({ date: tomorrow, format: '' })
            tomorrow = tomorrow.split('T')[0];
            this.setState({
                dayFilter: val,
                fromDate: tomorrow,
                toDate: tomorrow,
                // minCT: "",
                // maxCT: "",
                // minFee: "",
                // maxFee: "",
                // minEnt: "",
                // maxEnt: "",
                // minWin: "",
                // maxWin: "",
                // isFilerApplied: false                                
            }, () => {
                this.getLobbyFixture()
            })
        }
        else {
            this.setState({
                dayFilter: val,
                fromDate: '',
                toDate: '',
                minCT: "",
                maxCT: "",
                minFee: "",
                maxFee: "",
                minEnt: "",
                maxEnt: "",
                minWin: "",
                maxWin: "",
                isFilerApplied: false
            }, () => {
                this.getLobbyFixture()
            })
        }
    }

    goToLineup = (ContestItem, isFromMyTeam) => {
        // let urlData = this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(ContestItem.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        lineupPath = '/live-stock-fantasy/lineup/' + ContestItem.contest_id + '-' + dateformaturl + "?tab=1"

        let myTeam = {}
        if (isFromMyTeam) {
            myTeam = { from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams" }
        }
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: ContestItem,
                LobyyData: ContestItem,
                resetIndex: 1,
                ...myTeam
            }
        })
    }

    check(event, FixturedContestItem) {
        WSManager.loggedIn() ? this.joinGame(event, FixturedContestItem) : this.goToSignup()
    }

    /**
 * @description Method called when user loggedin  and click on join game 
 * @param {*} event - click event
 * @param {*} FixturedContestItem - contest model on which user click
 * @param {*} teamListData - user created team list of same collection
 */
    joinGame(event, FixturedContestItem, teamListData) {
        if (event) {
            event.stopPropagation();
        }

        if (checkBanState(FixturedContestItem, CustomHeader)) {
            WSManager.clearLineup();
            // if (this.state.TeamList.length > 0 || (teamListData && teamListData != null && teamListData.length > 0)) {
            this.setState({ showConfirmationPopUp: true, FixtureData: FixturedContestItem })
            // }
            // else {
            //     // if (this.state.TotalTeam.length === parseInt(Utilities.getMasterData().a_teams)) {
            //     //     this.openAlert()
            //     // }
            //     // else {
            //         this.goToLineup(FixturedContestItem)
            //     // }
            // }
            WSManager.setFromConfirmPopupAddFunds(false);
        }
    }

    openAlert = () => {
        this.setState({
            showAlert: true
        })
    }

    hideAlert = () => {
        this.setState({
            showAlert: false
        })
    }

    /**
     * @description method to display contest detail model
     * @param data - contest model data for which contest detail to be shown
     * @param activeTab -  tab to be open on detail, screen
     * @param event -  click event
     */
    ContestDetailShow = (data, activeTab, event) => {
        event.stopPropagation();
        event.preventDefault();
        this.setState({
            showContestDetail: true,
            FixtureData: data,
            activeTab: activeTab,
        });
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    /**
     * @description method to submit user entry to join contest
     * if user is guest then loggin screen will display else go to roster to select play to create new team
     */
    onSubmitBtnClick = () => {
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AL.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            // if(Constants.SELECTED_GAMET == Constants.GameType.StockPredict && !Utilities.minuteDiffValueStock({ date: this.state.FixtureData.game_starts_in },-5)){
            //     this.ContestDetailHide();
            //     this.showTimeOutModal();
            // }
            // else{
            if (checkBanState(this.state.FixtureData, CustomHeader)) {
                // if (this.state.TeamList != null && !_isUndefined(this.state.TeamList) && this.state.TeamList.length > 0) {
                this.ContestDetailHide();
                setTimeout(() => {
                    this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData })
                }, 200);
                // } else {
                //     this.goToLineup(this.state.FixtureData)
                // }
            } else {
                this.ContestDetailHide();
            }
            // }
        }
    }

    callAfterAddFundPopup() {
        if (WSManager.getFromConfirmPopupAddFunds()) {
            WSManager.setFromConfirmPopupAddFunds(false);
            setTimeout(() => {
                var contestData = WSManager.getContestFromAddFundsAndJoin();
                this.joinGame(null, contestData.FixturedContestItem, contestData.TeamsSortedArray)
            }, 100);
        }
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {
        // if(!Utilities.minuteDiffValueStock({ date: dataFromConfirmPopUp.FixturedContestItem.game_starts_in },-5)){
        //     this.ConfirmatioPopUpHide();
        //     this.showTimeOutModal();
        // }
        // else 
        // if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
        //         this.JoinGameApiCall(dataFromConfirmPopUp)
        //     } 
        //     else if ((dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "") || dataFromConfirmPopUp.selectedTeam == "") {
        //         Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
        //     } 
        //     else {
        this.JoinGameApiCall(dataFromConfirmPopUp)
        // }

    }
    JoinGameApiCall = (dataFromConfirmPopUp) => {
        var currentEntryFee = 0;
        currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
        if (
            (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) ||
            (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
        ) {
            this.CallJoinGameApi(dataFromConfirmPopUp);
        }
        else {
            if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
                if (Utilities.getMasterData().allow_buy_coin == 1) {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("ContestListing")
                    this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList', isStockF: true, isStockPF: true } });

                }
                else {
                    this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true, isStockPF: true } })
                }
            }

            else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("ContestListing")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd, isStockF: true, isStockPF: true }, isReverseF: this.state.showRF });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let ApiAction = LSFJoinContest;
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType,
        }


        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }

                this.ConfirmatioPopUpHide();
                this.setState({
                    lineup_master_idArray: [],
                    lineup_master_id: ''
                })
                setTimeout(() => {
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'stock_contestjoindaily');

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'stock_contestjoindaily');
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            this.goToLineup(dataFromConfirmFixture)
        }
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }

    joinMore = () => {
        this.ThankYouModalHide()
        this.getLobbyFixture()
        this.getMyFixtures()
    }

    editJoinedContest = (e, item) => {
        let currentDateTime = Date.now() + 1000
        let EndDate = Date.now(item.end_date)
        if (item.is_upcoming != 1 || (item.is_live == 1) || (currentDateTime > item.game_starts_in)) {
            if (item.status == 2 || item.status == 3) {
                this.props.history.push({
                    pathname: '/' + (item.collection_id || item.collection_master_id) + '/leaderboard',
                    state: {
                        rootItem: item,
                        contestItem: item,
                        status: Constants.CONTEST_COMPLETED,
                        isStockF: true,
                        isStockPF:true
                    }

                })
            }
            else {
                let dateformaturl = Utilities.getUtcToLocal(item.scheduled_date);
                dateformaturl = new Date(dateformaturl);
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
                let lineupPath = ''
                lineupPath = '/live-stock-fantasy/lineup/' + item.contest_id + '-' + dateformaturl + "?tab=1"
                this.props.history.push({
                    pathname: lineupPath.toLowerCase(), state: {
                        SelectedLineup: item.lineup_master_id,
                        FixturedContest: item,
                        team: item.team_name,
                        LobyyData: item,
                        resetIndex: 1,
                        teamitem: item,
                        rootDataItem: item,
                        from: 'editView',
                        isFromMyTeams: true,
                        collection_master_id: item.collection_id
                    }
                })
            }
        }
        else {
            Utilities.showToast('You can start trading once the contest goes live', 3000);
        }
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    showTimeOutModal = () => {
        this.setState({
            showTimeOutAlert: true
        })
    }

    hideTimeOutModal = () => {
        this.setState({
            showTimeOutAlert: false
        })
    }

    timerCallback = (item) => {
        this.getMyFixtures()
        this.getLobbyFixture()
    }

    render() {

        const {
            BannerList,
            ShimmerList,
            FixtureList,
            isListLoading,
            showHTP,
            showShadow,
            showLobbyFitlers,
            filterList,
            dayFilter,
            showRules,
            showAlert,
            showContestDetail,
            activeTab,
            TotalTeam,
            showConfirmationPopUp,
            FixtureData,
            showThankYouModal,
            MyStockList,
            isFilerApplied,
            showTimeOutAlert
        } = this.state
        let bannerLength = BannerList.length;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container xsp-container lsf-container">
                        <MetaComponent page="lobby" />

                        {/* <div className={`module-header-strip ${bannerLength == 0 ? '' : ' pb-0'}`}>
                            <span>{AL.LIVE_STOCK}</span>
                            <a
                                href
                                onClick={(e) => { this.showHTPModal(e) }}
                            >
                                {AL.How_to_Play}?
                            </a>
                        </div> */}
                        <div className={bannerLength == 0 ? ' xmt30' : ' xm-t-60'}>
                            <div>
                                <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                    {
                                        bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} isStock />
                                    }
                                </div>
                                <div className={`module-header-strip ${bannerLength == 0 ? '' : ' '}`}>
                            <span>{AL.LIVE_STOCK}</span>
                            <a
                                href
                                onClick={(e) => { this.showHTPModal(e) }}
                            >
                                {AL.How_to_Play}?
                            </a>
                        </div>
                                {
                                    WSManager.loggedIn() && MyStockList && MyStockList.length > 0 &&
                                    <div className="my-lobby-fixture-wrap">
                                        <div className={`top-section-heading ${bannerLength == 0 ? '' : ' pt0'}`}>
                                            <span className="txt-sc">{AL.MY_CONTEST} </span>
                                            <a href onClick={() => this.goToMyContest()}>{AL.VIEW} {AL.All}</a>
                                        </div>
                                        <LSFMyContestSlider
                                            MyContestList={MyStockList}
                                            showRulesModal={this.showRulesModal}
                                            onEdit={this.editJoinedContest.bind(this)}
                                        />
                                    </div>
                                }
                                <div className="upcoming-lobby-contest pt-0">
                                    <div className="top-section-heading">
                                        <span className="txt-sc">{AL.UPCOMING}</span>
                                        <div className="act-sec">
                                            <a href className={`btn ${dayFilter == "" ? ' active' : ''}`} onClick={() => this.addDayFilter("")}><span>{AL.All}</span></a>
                                            <a href className={`btn ${dayFilter == "1" ? ' active' : ''}`} onClick={() => this.addDayFilter(1)}><span>{AL.TODAY}</span></a>
                                            {/* <a href className={`btn ${dayFilter == "2" ? ' active' : ''}`} onClick={()=>this.addDayFilter(2)}><span>{AL.TOMORROW}</span></a> */}
                                        </div>
                                    </div>
                                    <Row className={`sp-up-fx ${bannerLength > 0 ? '' : ' xmt15'}`}>
                                        <Col sm={12}>
                                            <Row>
                                                <Col sm={12}>
                                                    {
                                                        FixtureList && FixtureList.length > 0 && FixtureList.map((item) => {
                                                            return (
                                                                <LSFFixtureCard
                                                                    key={item.contest_id}
                                                                    data={{
                                                                        isFrom: 'SPLobby',
                                                                        item
                                                                    }}
                                                                    goToLineup={this.goToLineup}
                                                                    showRulesModal={this.showRulesModal}
                                                                    check={this.check.bind(this)}
                                                                    ContestDetailShow={this.ContestDetailShow.bind(this)}
                                                                    timerCallback={(item) => this.timerCallback(item)}
                                                                    {...this.props}
                                                                />
                                                            )
                                                        })
                                                    }
                                                    {
                                                        (FixtureList.length === 0 && isListLoading) &&
                                                        ShimmerList.map((item, index) => {
                                                            return (
                                                                <LobbyShimmer key={index} />
                                                            )
                                                        })
                                                    }
                                                    {
                                                        (FixtureList.length === 0 && !isListLoading) && !isFilerApplied && dayFilter == '' &&
                                                        <div className="stay-tuned-card">
                                                            <img className="bg-graph" src={Images.daily_g} alt="" />
                                                            <div className="label">{AL.STAY_TUNED}</div>
                                                            <div className="open-at">{AL.STOCK_OPEN_SHORTLY}</div>
                                                            <div className="link-sec">{AL.SEE} <a href onClick={(e) => this.showHTPModal(e)}>{AL.STOCK_HOW_TO_PLAY}</a> {AL.STOCK_FANTASY}</div>
                                                        </div>
                                                    }
                                                    {
                                                        (FixtureList.length === 0 && !isListLoading) && (isFilerApplied || dayFilter != '') &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                            BUTTON_TEXT={AL.RESET + ' ' + AL.FILTERS}
                                                            onClick={this.showLFilter}
                                                        />
                                                    }
                                                </Col>
                                            </Row>
                                        </Col>
                                    </Row>
                                </div>
                            </div>
                        </div>
                        <div className="stats-fixed-btn" onClick={() => this.props.history.push('/stock-fantasy/statistics')} >
                            <i className="icon-statistics" />
                            <span>{AL.STATS}</span>
                        </div>
                        {
                            showHTP &&
                            <Suspense fallback={<div />} >
                                <LSFHTPP mShow={showHTP}
                                    mHide={this.hideHTPModal}
                                    stockSetting={this.state.stockSetting} />
                            </Suspense>
                        }
                        {
                            showRules &&
                            <Suspense fallback={<div />} >
                                <LSFRules
                                    mShow={showRules}
                                    mHide={this.hideRulesModal}
                                />
                            </Suspense>
                        }
                        {
                            showContestDetail &&
                            <ContestDetailModal
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                OpenContestDetailFor={FixtureData}
                                activeTabIndex={activeTab}
                                isStockF={true}
                                isStockPF={true}
                                LobyyData={FixtureData}
                                {...this.props}
                            />
                        }
                        {
                            showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={showConfirmationPopUp}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={''}
                                TotalTeam={TotalTeam}
                                FixturedContest={FixtureData}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={this.state.LobyyData}
                                fromContestListingScreen={true}
                                createdLineUp={''}
                                selectedLineUps={this.state.lineup_master_idArray}
                                showDownloadApp={this.showDownloadApp}
                                isStockF={true}
                                isStockLF={true}
                            />
                        }
                        {
                            showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.joinMore}
                                seeMyContestEvent={this.seeMyContest}
                                isStock={true}
                            />
                        }
                        {
                            showLobbyFitlers &&
                            <Suspense fallback={<div />} >
                                <LSFLobbyFilter
                                    isShow={showLobbyFitlers}
                                    isHide={this.hideLFilter}
                                    filterList={filterList}
                                    setFilter={this.setFilter}
                                    selFilVal={{
                                        minCT: this.state.minCT,
                                        maxCT: this.state.maxCT,
                                        minFee: this.state.minFee,
                                        maxFee: this.state.maxFee,
                                        minEnt: this.state.minEnt,
                                        maxEnt: this.state.maxEnt,
                                        minWin: this.state.minWin,
                                        maxWin: this.state.maxWin
                                    }}
                                    ApplyFilter={this.ApplyFilter}
                                />
                            </Suspense>
                        }
                    </div>
                )}
            </MyContext.Consumer>

        )
    }
}

export default LSFLobby