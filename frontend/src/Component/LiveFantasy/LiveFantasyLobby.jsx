import React, { lazy, Suspense } from 'react';
import { Row, Col } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLobbyFixturesLF, getMyLobbyFixturesLF} from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _filter, _Map, BannerRedirectLink, parseURLDate } from '../../Utilities/Utilities';
import { CollectionInfoModal, RFHTPModal } from "../../Modals";
import { NoDataView, LobbyBannerSlider, LobbyShimmer } from '../../Component/CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import LiveFantasyFixtureContest from "./LiveFantasyFixtureContest";
import Filter from "../../components/filter";
import * as AppLabels from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
//import MyContestSlider from "./MyContestSlider";
import MyDFSTourSlider from "../../Component/DFSTournament/MyDFSTourSlider";
import MetaComponent from '../../Component/MetaComponent';
import { LFMyContestSlider, LiveOverSocket } from '.';
import LFContestDetailsModal from './LFContestDetails';
import { socketConnect } from 'socket.io-react';
const DFSHTPModal = lazy(()=>import('../../Component/DFSTournament/DFSHTPModal'));
const LFHTP = lazy(()=> import('./LFHTP'));

var bannerData = {}
var globalThis = null;

export const LiveFantasyLobby = socketConnect(
    class LiveFantasyLobby extends React.Component {
        constructor(props) {
            super(props);
            this.state = {
                OriginalContestList: [],
                ContestList: [],
                BannerList: [],
                ShimmerList: [1, 2, 3, 4, 5],
                showContestDetail: false,
                FixtureData: '',
                isLoaderShow: false,
                isListLoading: false,
                offset: 0,
                MCOffset: 0,
                showLobbyFitlers: false,
                league_id: "",
                filterArray: [],
                sports_id: Constants.AppSelectedSport,
                showCollectionInfo: false,
                canRedirect: true,
                myContestData: [],
                hasMore: false,
                showCM: true,
                CoachMarkStatus: ls.get('coachmark-dfs') ? ls.get('coachmark-dfs') : 0,
                showModalSequence : (ls.get('seqNo') && ls.get('seqNo') == '')  ? true : false,
                filterLeagueList:[],
                showHTP: false,
                showShadow: false,
                DFSTourEnable: Utilities.getMasterData().a_dfst == 1 ? true : false,
                myTourData: [],
                TourMerchandiseList: [],
                MerchandiseList: [],
                ismodeListLoad: false,
                lisMode: 1 ,// to show dfs fixture in my contest
                TournamentList: [], 
                OriginalTournamentList: [],
                SecondInningFixtures:[],
                userTourIds: [], 
                onLoadCls: false
            }
        }
    
       
        ContestDetailShow = (data) => {
            this.setState({
                showContestDetail: true,
                FixtureData: data
            });
        }
        /**
        * @description this method to hide contest detail model,
        */
        ContestDetailHide = () => {
            this.setState({
                showContestDetail: false,
            });
        }
        /**
         * 
         * @description method to display collection info model.
         */
        CollectionInfoShow = (event) => {
            event.stopPropagation();
            this.setState({
                showCollectionInfo: true
            }, () => {
            });
        }
        /**
         * 
         * @description method to hide collection info model.
         */
        CollectionInfoHide = () => {
            this.setState({
                showCollectionInfo: false,
            });
        }
        /**
         * @description this method to to open create contest screen
         */
        createContest = () => {
            this.props.history.push('/create-contest')
        }
    
        /**
         * @description this method to to open Have a league code screen
         */
        joinContest = () => {
            if (WSManager.loggedIn()) {
                this.props.history.push({ pathname: '/private-contest' })
            }
            else {
                this.props.history.push({ pathname: '/signup' })
            }
        }
        /**
         * @description this method will be call when user click join buttonn from contestt detail model screen,
         * in case user in not logged in then signup/login screen will display
         * @param data - contest model 
         */
        onSubmitBtnClick = (data) => {
            if (!WSManager.loggedIn()) {
                setTimeout(() => {
                    this.props.history.push({ pathname: '/signup' })
                    Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
                }, 10);
            } else {
                let dateformaturl = parseURLDate(data.season_scheduled_date);
                WSManager.clearLineup();
                let lineupPath = '/lineup/' + data.home + "-vs-" + data.away + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data, current_sport: Constants.AppSelectedSport } })
            }
        }
    
        /**
         * @description - this method is to display contest of a fixture on click event
         * @param data - fixture model
         */
        gotoDetails = (data,event,gameData) => {
            event.preventDefault();
            data['collection_master_id'] = gameData.collection_id;
            if(data.status == 2){
                this.props.history.push({ pathname: '/my-contests', state: { from: data.status == 1 ? 'lobby-live' : 'lobby-completed'} });
            }
            else if(data.status == 1){
                this.props.history.push({ pathname: '/live-fantasy-center/' + gameData.collection_id , state: { LobyyData: data} });
            }
            else{
                let dateformaturl = parseURLDate(data.season_scheduled_date);
                this.setState({ LobyyData: data })
                let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing-lf/' + gameData.collection_id + '/' + data.league_abbr + '-' + data.home + "-vs-" + data.away + "-" + dateformaturl ;
                let CLPath = contestListingPath.toLowerCase()+ "?sgmty=" +  btoa(Constants.SELECTED_GAMET)
                this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: CLPath ,OverData:gameData} })
            }
    
          
        }
    
        gotoSecondInningDetails = (data, event) => {
            event.preventDefault();
            let dateformaturl = parseURLDate(data.season_scheduled_date);
            this.setState({ LobyyData: data })
            let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + data.league_name + '-' + data.home + "-vs-" + data.away + "-" + dateformaturl ;
            let CLPath = contestListingPath.toLowerCase()+ "?sgmty=" +  btoa(Constants.SELECTED_GAMET) + '&sit=' + btoa(true)
            this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: CLPath, is_2nd_inning: true } })
        }
    
        updateOverTimer = (obj,status) => {
            let contestList = this.state.ContestList;
    
            
            if(contestList!=undefined){
                for (var i=0; i<= contestList.length ; i++) {
                    let fixture = contestList[i]
                    if(fixture !=undefined){    
                        for (var j=0; j<= fixture.game.length ; j++) {
                            let fixtureGame =fixture.game[j]
                            if(fixtureGame!=undefined){
            
                                if(fixtureGame.collection_id == obj.collection_id){
                                    if(status){
                                        fixtureGame.timer_date = obj.timer_date - obj.time;
    
                                    }
                                    else{
                                        fixtureGame.timer_date = ""
    
                                    }
                                  
               
                               }
                            }
                            
                        
                        } 
                    }
    
                     
                }
            }
            let myContestList = this.state.myContestData;
    
    
            if (myContestList != undefined) {
                for (var i = 0; i <= myContestList.length; i++) {
                    let fixture = myContestList[i]
                    if (fixture != undefined) {
                        if (fixture.collection_id == obj.collection_id) {
                            if (status) {
                                fixture.timer_date = obj.timer_date;
    
                            }
                            else {
                                fixture.timer_date = ""
    
                            }
                            break;
                        }
                    }
    
    
    
                }
            }
            this.setState({ myContestData: myContestList })
    
            this.setState({ ContestList: contestList })
    
    
        }
    
    
        /**
         * @description - this is life cycle method of react
         */
        componentDidMount() {
            const { socket } = this.props
            console.log(socket, 'socket');
            ls.set("isULF", false)
            globalThis = this;
            // let userId = '';
            if (WSManager.loggedIn()) {
                console.log('isConnectedLobby', socket.connected);
                socket.emit('JoinTimerLF', {});
                socket.on('updateMatchOverTimer', (obj) => {
                    console.log("updateMatchOverTimer", JSON.stringify(obj))
                    Utilities.setSocketEve(obj).then(res => {
                        globalThis.updateOverTimer(res, true)
                    })
                })

                socket.on('disconnect', function () {
                    let interval = null
                    let isConnected = null
                    socket.off('updateMatchOverTimer')
                    interval = setInterval(() => {
                        if (isConnected) {
                            clearInterval(interval);
                            interval = null;
                            socket.emit('JoinTimerLF', {});
                            socket.on('updateMatchOverTimer', (obj) => {
                                console.log("updateMatchOverTimer", JSON.stringify(obj))
                                Utilities.setSocketEve(obj).then(res => {
                                    globalThis.updateOverTimer(res, true)
                                })
                            })
                            return;
                        }
                        isConnected = socket.connected;
                        socket.connect();
                    }, 500)
                });

            }
           
    
            window.addEventListener('scroll', this.onScrollList);
            setTimeout(() => {
                this.setState({
                    onLoadCls: true
                })
            }, 10);
    
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'SessionKey',
                    targetFunc: 'SessionKey',
                    page:'lobby',
                    SessionKey: WSManager.getToken() ?  WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken():'',
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            if (this.props.location.pathname == '/lobby') {
                let { sports_id } = this.state;
                WSManager.setFromConfirmPopupAddFunds(false);
                let league_id = this.getSportsLeagueId(sports_id, Constants.LOBBY_FILTER_ARRAY);
                this.setState({ isLoaderShow: true, sports_id, league_id, filterArray: Constants.LOBBY_FILTER_ARRAY }, () => {
                     this.lobbyContestList(0);
                    
                    this.getBannerList();
                })
    
                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'fixture');
                if (WSManager.loggedIn()) {
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
                }
                this.checkOldUrl();
            }
            Utilities.handelNativeGoogleLogin(this)
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
        }
    
        onScrollList = (event) => {
            let scrollOffset = window.pageYOffset;
            if(scrollOffset>0){
                this.setState({
                    showShadow: true
                })
            }
            else{
                this.setState({
                    showShadow: false
                })
            }
        }
    
        UNSAFE_componentWillMount = () => {
            if(Utilities.getMasterData().a_dfst == 1){
                this.setState({
                    lisMode: ls.get('isDfsTourEnable') ? 0 : 1, // to show dfs fixture in my contest
                })
            }
            this.enableDisableBack(false)
            Utilities.setScreenName('lobby')
        }
    
        enableDisableBack(flag) {
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'back',
                    type: flag,
                    targetFunc:'back'
                }
                this.sendMessageToApp(data);
            }
        }
    
    
    
    
        componentWillUnmount() {
            const { socket } = this.props
            socket.off('updateMatchOverTimer')
            this.enableDisableBack(false)
        }
    
        /**
         * @description method will be called when changing sports
         */
        reload = (nextProps) => {
            if (window.location.pathname.startsWith("/lobby")) {
                let league_id = this.getSportsLeagueId(nextProps.selectedSport, this.state.filterArray);
                this.setState({
                    ContestList: [],
                    league_id: league_id,
                    offset: 0,
                    MCOffset:0,
                    sports_id: nextProps.selectedSport,
                }, () => {
                    WSManager.setFromConfirmPopupAddFunds(false);
                     this.lobbyContestList(0);
                    
                   
                    if (WSManager.loggedIn()) {
                        this.getMyLobbyFixturesList(0);
    
                    }
                    this.getBannerList();
                    Filter.reloadLobbyFilter();
                })
            }
        }
    
        sendMessageToApp(action) {
            if (window.ReactNativeWebView) {
                window.ReactNativeWebView.postMessage(JSON.stringify(action));
            }
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
            // if(WSManager.loggedIn() && !Constants.IS_SPORTS_HUB){
                updateDeviceToken(param).then((responseJson) => {
                })
            }
        }
    
        checkOldUrl() {
            let url = window.location.href;
            if (!url.includes('#') && window.location.pathname === "/lobby") {
                if (Utilities.getSelectedSportsForUrl())
                    window.history.replaceState("", "", window.location.pathname + "#" + Utilities.getSelectedSportsForUrl());
            }
        }
    
        
    
        
    
        /**
         * @description - method to get fixtures listing from server/s3 bucket
         */
        lobbyContestList = async (offset) => {
            if (Constants.AppSelectedSport == null)
                return;
    
            let param = {
                "sports_id": Constants.AppSelectedSport
            }
    
            this.setState({ isLoaderShow: true, isListLoading: true })
            delete param.limit;
            var api_response_data = await getLobbyFixturesLF(param);
            if (api_response_data && param.sports_id == Constants.AppSelectedSport) {
                this.setState({ isLoaderShow: false })
                let fixture_list = api_response_data.data;
                let fixture_live =  [];
                let merchandise_list = api_response_data.merchandise_list ? api_response_data.merchandise_list : [];
                if (offset == 0) {
                    let tmpArray = [];
                    let tmpLeagues = []; 
                            _Map(fixture_list,(obj)=>{
                                // if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
                                //     tmpArray.push(obj);
                                //     let objLeague = { league_id: obj.league_id, league_name: obj.league_name }
                                //     if (tmpLeagues.filter(e => e.league_id === objLeague.league_id).length === 0) {
                                //         tmpLeagues.push(objLeague)
                                //     }
                                // }
                                //else
                                 if(this.state.DFSTourEnable && this.state.lisMode == 1 && obj.tournament == 1 && !this.state.userTourIds.includes(obj.tournament_id)){
                                    obj['season_scheduled_date'] = obj.start_date;
                                    tmpArray.push(obj);
                                    let objLeague = { league_id: obj.league_id, league_name: obj.league_name }
                                    if (tmpLeagues.filter(e => e.league_id === objLeague.league_id).length === 0) {
                                        tmpLeagues.push(objLeague)
                                    }
                                }
                                else{
                                    // let gamestartin = 1646052100000
                                    // if(obj.season_game_uid == '53205'){
                                    //     obj["game_starts_in"] = gamestartin
                                    // }
                                    tmpArray.push(obj);
                                    let objLeague = { league_id: obj.league_id, league_name: obj.league_name }
                                    if (tmpLeagues.filter(e => e.league_id === objLeague.league_id).length === 0) {
                                        tmpLeagues.push(objLeague)
                                    }
                                }
                            })
                        let sortList = tmpArray.sort((a, b) => new Date(a.season_scheduled_date) - new Date(b.season_scheduled_date)); 
                        
                        let pinFixtures = []
                        let normalFixture = []
                        _Map(sortList, (obj) => {
                            if (obj.is_pin_fixture == 1) {
                                pinFixtures.push(obj)
                            } else {
                                normalFixture.push(obj)
                            }
                        })
    
    
                        this.setState({ 
                            ContestList: [...pinFixtures, ...normalFixture], //sortList, 
                            OriginalContestList: [...pinFixtures, ...normalFixture] , //sortList, 
                            filterLeagueList: tmpLeagues ,
                            MerchandiseList: merchandise_list,
                            SecondInningFixtures: _filter(fixture_live,(obj)=>{
                                return (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0)
                            })
                        }, () => {
                            if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
                                _Map(Constants.LOBBY_FILTER_ARRAY,(obj)=>{
                                    if(obj.sports_id == this.state.sports_id){
                                        this.filterLobbyResults({ league_id: obj.league_id })
                                    }
                                })
                            }
                    })
                } 
                this.setState({ offset: api_response_data.offset })
            }
            this.setState({ isListLoading: false })
            if (WSManager.loggedIn()) {
                this.getMyLobbyFixturesList(0);
            }
        }
    
        /**
         * @description - method to get fixtures listing from server/s3 bucket
         */
        getMyLobbyFixturesList = async (MCOffset) => {
            if (Constants.AppSelectedSport == null)
                return;
    
            let param = {
                "sports_id": Constants.AppSelectedSport,
                "limit" : this.state.limit,
                "offset" : this.state.MCOffset
            }
    
    
            this.setState({ isLoaderShow: true, isListLoading: true })
            // delete param.limit;
            var api_response_data = await getMyLobbyFixturesLF(param);
            if (api_response_data) {
                this.setState({ isLoaderShow: false })
                let data = api_response_data.data || [];
                let tmpArray = [] 
                _Map(data,(obj)=>{
                    if (obj.dfs_count == 0 && obj['2nd_inning_count'] > 0) {
                    }else{
                        tmpArray.push(obj);
                    }
                })
                let haseMore = data.length >= param.limit
                if (param.offset == 0) {
                    this.setState({
                        myContestData: tmpArray || [],
                        hasMore: false,
                        MCOffset: 0
                    })
                }
                else{
                    this.setState({
                        myContestData: [...this.state.myContestData, ...tmpArray],
                        MCOffset: data.offset,
                        hasMore: haseMore
                    });
                }
                //     let tmpArray = [] 
                //     _Map(api_response_data,(obj)=>{
                //         if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
                //             tmpArray.push(obj);
                //         }
                //     })
                //     this.setState({ ContestList: tmpArray, OriginalContestList: tmpArray }, () => {
                //         if (Constants.LOBBY_FILTER_ARRAY.length > 0) {
                //             this.filterLobbyResults({ league_id: Constants.LOBBY_FILTER_ARRAY[0].league_id })
                //         }
                //     })
                // } 
                // else {
                //     let tmpArray = [] 
                //     _Map(api_response_data,(obj)=>{
                //         if (Utilities.minuteDiffValue({ date: obj.game_starts_in }) < 0) {
                //             tmpArray.push(obj);
                //         }
                //     })
                //     this.setState({ ContestList: [...this.state.ContestList, ...tmpArray], OriginalContestList: [...this.state.ContestList, ...tmpArray] });
                // }
                // this.setState({ offset: api_response_data.offset })
            }
            this.setState({ isListLoading: false })
        }
    
        
    
        getSportsLeagueId(sports_id, filterArray) {
    
            let league_id = '';
            for (let i = 0; i < filterArray.length; i++) {
                if (filterArray[i].sports_id == sports_id) {
                    league_id = filterArray[i].league_id;
                }
            }
            return league_id;
        }
    
        /** 
        @description hide lobby filters 
        */
        hideFilter = () => {
            this.setState({ showLobbyFitlers: false })
            this.props.hideFilterData()
        }
    
        /** 
        @description show lobby filters 
        */
        showFilter = () => {
            this.setState({ showLobbyFitlers: true })
        }
    
        /** 
        @description Apply filters and load data accordingly
        */
        filterLobbyResults = (filterObj) => {
            let league_id = filterObj.league_id ? filterObj.league_id : "";
            this.setState({ league_id: league_id }, function () {
                this.filterFixturesLocally(league_id)
            })
    
            let filterArray = this.setFilterArray(league_id);
            Constants.setValue.setFilter(filterArray);
            this.setState({ league_id: league_id, showLobbyFitlers: false, offset: 0, filterArray: filterArray })
            this.props.hideFilterData()
        }
    
        filterFixturesLocally(leagueIds) {
            let LMode = this.state.lisMode;
            let allFixtures = LMode == 0 ? this.state.OriginalTournamentList : this.state.OriginalContestList;
            if (leagueIds == '') {
                if(LMode == 0){
                    this.setState({ TournamentList: allFixtures })
                }
                else{
                    this.setState({ ContestList: allFixtures })
                }
            }
            else {
                let filteredList = [];
                for (var i = 0; i < allFixtures.length; i++) {
                    if (leagueIds.includes(allFixtures[i].league_id)) {
                        filteredList.push(allFixtures[i])
                    }
    
                }
                if(LMode == 0){
                    this.setState({ TournamentList: filteredList })
                }
                else{
                    this.setState({ ContestList: filteredList })
                }
            }
        }
    
        setFilterArray(league_id) {
            let { filterArray } = this.state;
    
            let hasFilter = false;
            if (filterArray.length > 0) {
                for (let i = 0; i < filterArray.length; i++) {
                    if (filterArray[i].sports_id == this.state.sports_id) {
                        hasFilter = true;
                        filterArray[i].league_id = league_id;
                    }
                }
            }
    
            if (!hasFilter && league_id != "") {
                let filterObj = {
                    'sports_id': this.state.sports_id,
                    'league_id': league_id,
                }
                filterArray.push(filterObj);
            }
    
            return filterArray;
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
                    if (api_response_data && param.sports_id == Constants.AppSelectedSport) {
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
            let refData = '';
            let temp = [];
            _Map(this.getSelectedbanners(bdata), (item, idx) => {
                if (item.banner_type_id == 2) {
                    refData = item;
                }
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
            if(refData){
                setTimeout(() => {
                    CustomHeader.showRCM(refData);
                }, 200);
            }
            this.setState({ BannerList: temp })
        }
    
        /** 
         * @description call to get selected banner data
        */
        getSelectedbanners(api_response_data) {
            let tempBannerList = [];
            for (let i = 0; i < api_response_data.length; i++) {
                let banner = api_response_data[i];
                if (WSManager.getToken() && WSManager.getToken() != '') {
                    if(banner.game_type_id == 0 || WSManager.getPickedGameTypeID() == banner.game_type_id){
                        if (banner.banner_type_id == Constants.BANNER_TYPE_REFER_FRIEND
                            || banner.banner_type_id == Constants.BANNER_TYPE_DEPOSITE) {
                            if (banner.amount > 0)
                                tempBannerList.push(api_response_data[i]);
                        }
                        else if (banner.banner_type_id == '6') {
                            //TODO for banner type-6 add data
                        }
                        else {
                            tempBannerList.push(api_response_data[i]);
                        }
                    }
                }
                else {
                    if (banner.banner_type_id == '6' && (banner.game_type_id == 0 || WSManager.getPickedGameTypeID() == banner.game_type_id)) {
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
        redirectLink = (result, isRFBanner) => {
            if(isRFBanner){
                this.showRFHTPModalFn()
            }
            else{
                BannerRedirectLink(result, this.props)
            }
        }
        showRFHTPModalFn=()=>{
            this.setState({showRFHTPModal:true})
        }
        hideRFHTPModalFn=()=>{
            this.setState({showRFHTPModal:false})
        }
    
    
        UNSAFE_componentWillReceiveProps(nextProps) {
            if (this.state.sports_id != nextProps.selectedSport) {
                this.reload(nextProps);
            }
            if (this.state.showLobbyFitlers != nextProps.showLobbyFitlers) {
                this.setState({ showLobbyFitlers: nextProps.showLobbyFitlers })
            }
        }
    
        timerCompletionCall = (item) => {
            this.updateOverTimer(item,false)
            // let fArray = _filter(this.state.ContestList, (obj) => {
            //     return item.collection_master_id != obj.collection_master_id
            // })
            // this.setState({
            //     ContestList: fArray
            // })
        }
        timerSecInngCompletionCall = (item) => {
            let fArray = _filter(this.state.SecondInningFixtures, (obj) => {
                return item.collection_master_id != obj.collection_master_id
            })
            this.setState({
                SecondInningFixtures: fArray
            })
        }
    
        goToPREDICTION = () => {
            WSManager.setPickedGameType(Constants.GameType.Pred);
            // window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction");
    
            let gameType = Utilities.getMasterData().sports_hub;
            let HGLIST = _filter(gameType, (obj) => {
                return obj.game_key == Constants.GameType.Pred;
            })
            let lsSport = ls.get('selectedSports');
            if (HGLIST[0].allowed_sports.includes(lsSport)) {
                window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction");
            }
            else {
                let sport = HGLIST[0].allowed_sports[0];
                ls.set('selectedSports', sport);
                Constants.setValue.setAppSelectedSport(sport);
                window.location.replace("/lobby#" + Utilities.getSelectedSportsForUrl() + "#prediction");
            }
        }
    
        renderPREDCard = () => {
            if(Utilities.getMasterData().a_sports_prediction_bnr != 1){
                return ''
            }
            let bannerImg = Utilities.getMasterData().sports_prediction_bnr;
            if (Constants.IS_PREDICTION) {
                return (bannerImg ?
                    <li onClick={this.goToPREDICTION} className="prd-card-img-only" >
                        <img className="img-shape" src={Utilities.getSettingURL(bannerImg)} alt='' />
                    </li>
                    :
                    <li onClick={this.goToPREDICTION} className="dfs-card prd-card dfs-card-new" >
                        <div className="dfs-c-new">
                            <div className="dfs-c-inner dfs-c-inner-left">
                                <img className="img-dfs" src={Images.PLAY_PRED_BANNER_IMG} alt='' />
                            </div>
                            <div className="dfs-c-inner  dfs-c-inner-right">
                                <p>Play prediction & win coins to redeem for exciting offers & prizes</p>                          
                            </div>
                        </div>
                    </li>
                )
            }
            return ''
        }
    
        goToMyContest=()=>{
            this.props.history.push({ pathname: '/my-contests' });
        }
    
        showCM = () => {
            this.setState({ showCM: true })
    
        }
    
        hidePropCM = () => {
            this.setState({ showCM: false });
        }
    
        showHTPModal = () => {
            this.setState({
                showHTP: true
            })
        }
    
        hideHTPModal = () => {
            this.setState({
                showHTP: false
            })
        }
    
        joinTournament=(item)=>{
            let isFor = (item.status == 2 || item.status == 3) ? 'completed' : 'upcoming';
            let leaguename = item.league_name.replace(/ /g, '');
            let tournamentId = item.tournament_id;
            let leagueId = item.league_id;
            let dateformaturl = Utilities.getUtcToLocal(item.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
    
            let tourPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/tournament/' + tournamentId + "/" + leagueId + "/" + leaguename + "/" + dateformaturl
            this.props.history.push({ 
                pathname: tourPath.toLowerCase(), 
                state: {
                    data: item,
                    isFor: isFor || 'upcoming',
                    MerchandiseList: this.state.TourMerchandiseList
                } 
            })
        }
    
        clickEarnCoins = () => {
            if (WSManager.loggedIn()) {
                this.props.history.push("/earn-coins")
            } else {
                this.props.history.push({ pathname: '/signup' })
            }
        }       
        
        switchMode=(theme)=>{
            this.setState({
                ismodeListLoad: true
            })
            if(theme == 1){
                ls.set('dfsTourMode', true)
                this.setState({
                    lisMode : 1,
                    ismodeListLoad: false,
                    league_id: ""
                },()=>{
                    CustomHeader.changeFilter(false,'')
                    let filterArray = [];
                    Constants.setValue.setFilter(filterArray);
                    ls.set('isDfsTourEnable',false)
                    if (WSManager.loggedIn()) {
                        this.getMyLobbyFixturesList(0);
                    }
                })
                // document.body.classList.add('body-dark-theme');
                // window.location.reload();
            }
            else if(theme == 0){
                ls.set('dfsTourMode', false)
                this.setState({
                    lisMode : 0,
                    ismodeListLoad: false,
                    league_id: ""
                },()=>{
                    CustomHeader.changeFilter(false,'')
                    let filterArray = [];
                    Constants.setValue.setFilter(filterArray);
                    ls.set('isDfsTourEnable',true)
                    this.lobbyTournamentList(0)
                    if (WSManager.loggedIn()) {
                        this.getMyLobbyDFSTournamentList();
                    }
                })
                // if(document.body.classList.contains('body-dark-theme')){
                //     document.body.classList.remove('body-dark-theme');
                //     window.location.reload();
                // }
            }
        }
    
        render() {
    
            const {
                showContestDetail,
                FixtureData,
                isLoaderShow,
                showCollectionInfo,
                BannerList,
                league_id,
                showLobbyFitlers,
                ShimmerList,
                ContestList,
                isListLoading,
                myContestData,
                showModalSequence,
                showRFHTPModal,
                showHTP, 
                showShadow,
                DFSTourEnable,
                lisMode,
                ismodeListLoad,
                myTourData,
                TourMerchandiseList,
                MerchandiseList,
                TournamentList,
                SecondInningFixtures
            } = this.state
    
            let FitlerOptions = {
                showLobbyFitler: showLobbyFitlers,
                filtered_league_id: league_id
            }
    
            let bannerLength = BannerList.length;
            let showToggleSec = DFSTourEnable // && (myTourData && myTourData.length > 0 || myContestData && myContestData.length > 0)
            var showLobbySportsTab = process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false
            let isMobileApp = window.ReactNativeWebView ?  true :false;
    
            return (
                <MyContext.Consumer>
                    {(context) => (
                        <div className="transparent-header web-container tab-two-height pb0 DFS-tour-lobby ">
                            <MetaComponent page="lobby" />
                            {
                                !ismodeListLoad &&
                                <Filter customLeagues={this.state.filterLeagueList} leagueList={league_id} {...this.props} FitlerOptions={FitlerOptions} hideFilter={this.hideFilter} filterLobbyResults={this.filterLobbyResults}></Filter>
                            }
    
                            {/* <div className={"header-fixed-strip wid-fix-lf" + (showLobbySportsTab ? " header-fixed-strip-2 live-fantasy" : '')}>
                                <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                    <span className='head-bg-strip'>{AppLabels.LIVE_FANTASY}</span>
                                    <a
                                        href
                                        onClick={(e) => { this.showHTPModal(e) }}
                                    >
                                        {AppLabels.How_to_Play}?
                                    </a>
                                </div>
                            </div> */}

                          
    
                            <div className={bannerLength > 0 ? '' : ' m-t-60'}>
                                {
                                    bannerLength > 0 &&
                                    <div className={bannerLength > 0 ? 'banner-v animation' : 'banner-v'}>
                                        {
                                            bannerLength > 0 && <LobbyBannerSlider BannerList={BannerList} redirectLink={this.redirectLink.bind(this)} />
                                        }
                                    </div>
                                }
                                 
                                 <div className={"header-fixed-strip mt-0" + (showLobbySportsTab ? " header-fixed-strip-2" : '')}>
                                <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                    <span className='head-bg-strip'>{AppLabels.LIVE_FANTASY}</span>
                                    <a className='decoration-under'
                                        href
                                        onClick={(e) => { this.showHTPModal(e) }}
                                    >
                                        {AppLabels.HOW_TO_PLAY_FREE}
                                    </a>
                                </div>
                            </div>
                                {/* {
                                    showToggleSec &&
                                    <div className="my-lobby-dfs-tabs">
                                        <ul className="nav">
                                            <li className={lisMode == 1 ? "active" : ''}>
                                                <a href onClick={()=>this.switchMode(1)}>
                                                    {AppLabels.DAILY}
                                                </a>
                                            </li>
                                            <li className={lisMode == 0 ? "active" : ''}>
                                                <a href onClick={()=>this.switchMode(0)}>
                                                    {AppLabels.TOURNAMENT}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                } */}
                                {
                                    lisMode == 1 &&
                                    <>
                                        {
                                            WSManager.loggedIn() && ContestList.length > 0 &&
                                            <div className={"contest-action single-btn-contest-action single-btn-contest-action-new" + (bannerLength == 0 ? ' mt15' : '')}>
                                               
                                                {
                                                   
                                                    <NavLink exact to="/private-contest" className="btn btnStyle btn-rounded small">
                                                    <span className="league-code-btn text-uppercase">
                                                        {AppLabels.JOIN_CONTEST}
                                                    </span>
                                                </NavLink>
                                                }
                                               
                                            </div>
                                        }
                                        {
                                            WSManager.loggedIn() && myContestData && myContestData.length > 0 &&
                                            <div className="my-lobby-fixture-wrap">
                                                <div className="top-section-heading">
                                                    {AppLabels.MY_CONTEST} 
                                                    <a href onClick={()=>this.goToMyContest()}>{AppLabels.VIEW} {AppLabels.ALL}</a>
                                                </div>
                                                <LFMyContestSlider
                                                    FixtureData={myContestData} 
                                                    gotoDetails={this.gotoDetails}
                                                    getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                                                    timerCallback={this.timerCompletionCall} 
                                                />
                                            </div>
                                        }
                                        
                                        <div className="upcoming-lobby-contest">
                                            <div className="top-section-heading">{AppLabels.UPCOMING} {AppLabels.MATCHES}</div>
                                            <Row className={bannerLength > 0 ? '' : 'mt15'}>
                                                <Col sm={12}>
                                                    <Row>
                                                        <Col sm={12}>
                                                            <ul className="collection-list-wrapper lobby-anim">
                                                                {
                                                                    (ContestList.length == 0 && isListLoading) &&
                                                                    ShimmerList.map((item, index) => {
                                                                        return (
                                                                            <LobbyShimmer key={index} />
                                                                        )
                                                                    })
                                                                }
    
                                                                {
                                                                    ContestList.length > 0 &&
                                                                    ContestList.map((item, index) => {
                                                                        return (
                                                                            <React.Fragment key={item.collection_master_id} >
                                                                                {
                                                                                    // DFSTourEnable && item.tournament == 1 ?
                                                                                    // <DFSTourCard
                                                                                    //     data={{
                                                                                    //         item: item,
                                                                                    //         isFrom: 'Lobby',
                                                                                    //         showHTPModal: ()=>this.showHTPModal(),
                                                                                    //         joinTournament: ()=>this.joinTournament(item),
                                                                                    //         MerchandiseList: MerchandiseList
                                                                                    //     }}
                                                                                    // />        
                                                                                    // :
                                                                                    <LiveFantasyFixtureContest
                                                                                        {...this.props}
                                                                                        onLBClick={(e)=> {
                                                                                            e.stopPropagation()
                                                                                            CustomHeader.LBModalShow()
                                                                                        }}
                                                                                        indexKey={item.collection_master_id}
                                                                                        ContestListItem={item}
                                                                                        gotoDetails={this.gotoDetails}
                                                                                        CollectionInfoShow={this.CollectionInfoShow}
                                                                                        IsCollectionInfoHide={this.CollectionInfoHide}
                                                                                        timerCallback={this.timerCompletionCall}
    
                                                                                    />
                                                                                }
                                                                                {
                                                                                    index === 1 && this.renderPREDCard()
                                                                                }
                                                                            </React.Fragment>
                                                                        );
                                                                    })
                                                                }
                                                                {
                                                                    (ContestList.length < 2 && !isListLoading) && this.renderPREDCard()

                                                                }
    
                                                                {
                                                                    (ContestList.length == 0 && !isListLoading) &&
                                                                    <NoDataView
                                                                        BG_IMAGE={Images.no_data_bg_image}
                                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                        MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                                        MESSAGE_2={AppLabels.NO_FIXTURES_MSG2}
                                                                        onClick_2={this.joinContest}
                                                                    />
                                                                }
                                                            </ul>
                                                        </Col>
                                                    </Row>
                                                </Col>
                                            </Row>
                                        </div>
                                    </>
                                }
                                {
                                    lisMode == 0 &&
                                    <>
                                    {
                                        WSManager.loggedIn() && myTourData.length > 0 &&
                                        <div className={"tour-slider-wrapper my-lobby-fixture-wrap" + (myTourData && myTourData.length > 0 ? '' : ' p-0')}>
                                            <div className="top-section-heading">
                                                {AppLabels.MY_CONTEST} 
                                                <a href onClick={()=>this.goToMyContest()}>{AppLabels.VIEW} {AppLabels.ALL}</a>
                                            </div>
                                            <MyDFSTourSlider
                                                viewAll={this.viewAllTournament}
                                                List={myTourData} 
                                                MerchandiseList={TourMerchandiseList}
                                                isFrom={'LSlider'} 
                                                joinTournament={this.joinTournament}
                                            />
                                        </div>
                                    }
                                    <div className="upcoming-lobby-contest">
                                        <div className="top-section-heading">{AppLabels.UPCOMING} {AppLabels.MATCHES}</div>
                                        <Row className={bannerLength > 0 ? '' : 'mt15'}>
                                            <Col sm={12}>
                                                <Row>
                                                    <Col sm={12}>
                                                        <ul className="collection-list-wrapper lobby-anim">
                                                            
                                                        </ul>
                                                    </Col>
                                                </Row>
                                            </Col>
                                        </Row>
                                    </div>
                                    </>
                                }
                            </div>
    
                            {showContestDetail &&
                                <LFContestDetailsModal IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={FixtureData} {...this.props} />
                            }
                            {showCollectionInfo &&
                                <CollectionInfoModal IsCollectionInfoShow={showCollectionInfo} IsCollectionInfoHide={this.CollectionInfoHide} />
                            }
                            {
                                showRFHTPModal &&
                                <RFHTPModal 
                                isShow={showRFHTPModal}
                                isHide={this.hideRFHTPModalFn}
                                />
                            } 
                            {
                                showHTP &&
                                <Suspense fallback={<div />} >
                                    <LFHTP
                                        mShow={showHTP}
                                        mHide={this.hideHTPModal}
                                    />
                        
                                </Suspense>
                            }
                            {
                                isMobileApp && 
                                <LiveOverSocket />
                            }
                        </div>
                    )}
                </MyContext.Consumer>
    
            )
        }
    }
)

export default LiveFantasyLobby
