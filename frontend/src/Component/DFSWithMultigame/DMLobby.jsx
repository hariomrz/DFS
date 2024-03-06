import React, { lazy, Suspense } from 'react';
import PQueue from "p-queue/dist";
import { Row, Col, Button, Tab, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { updateDeviceToken, getLobbyBanner, getLobbyFixtures, getMyLobbyFixtures, getLiveMatchGameCenter, getDFSTLobbyTournament, getBannedStats, getUserAadharDetail, getUserXPCard } from "../../WSHelper/WSCallings";
import { NavLink } from "react-router-dom";
import { Utilities, _filter, _Map, BannerRedirectLink, parseURLDate, _isEmpty, convertToTimestamp, _includes, _uniqBy, isDateTimePast, _isUndefined } from '../../Utilities/Utilities';
import { CollectionInfoModal, ContestDetailModal, RFHTPModal, DailyFantasyHTP, RulesScoringModal } from "../../Modals";
import { NoDataView, LobbyBannerSlider, LobbyShimmer } from '../CustomComponent';
import CustomHeader from '../../components/CustomHeader';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import FixtureContest from "../../views/FixtureContest";
import Filter from "../../components/filter";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import MyContestSlider from "../../views/MyContestSlider";
import MetaComponent from '../MetaComponent';
// import MultiGameFixtureContest from '../../Component/MultiGameModule/MultiGameFixtureContest';
import DMMultiCard from "./DMMultiCard";
import DMMyContestSlider from "./DMMyContestSlider";
import NDFSTourCard from '../NewDFSTournament/NDFSTourCard';
import { SportsIDs } from "../../JsonFiles";
import TournamentLeaderboardModal from '../../Modals/TournamentLeaderboardModal';
const DFSHTPModal = lazy(() => import('../DFSTournament/DFSHTPModal'));
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

var bannerData = {}

export class DMLobby extends React.Component {
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
            showModalSequence: (ls.get('seqNo') && ls.get('seqNo') == '') ? true : false,
            filterLeagueList: [],
            showHTP: false,
            showShadow: false,
            DFSTourEnable: Utilities.getMasterData().a_dfst == 1 ? true : false,
            MerchandiseList: [],
            ismodeListLoad: false,
            SecondInningFixtures: [],
            onLoadCls: false,
            updatedLiveMatch: {},
            liveMatchCount: 0,
            dfsHTP: false,
            showDFSRulesModal: false,
            pinTournament: {},
            userTournament: {},
            load: false,
            premierLeagueData: [],
            premierLeagueDataMtor: [],
            fixture_match_list: {},
            fixture_map_item: {},
            userXPDetail: ''

        }
    }

    getGcLiveMatchList = (resolve = () => { }) => {
        let param = {
            "sports_id": Constants.AppSelectedSport
        }
        this.updatedLiveMatchList({})
        getLiveMatchGameCenter(param).then((responseJson) => {
            this.setState({ isLoading: false })
            if (responseJson.response_code === WSC.successCode) {
                resolve()
                if (responseJson.data.live_match && responseJson.data.live_match.length > 0) {
                    this.setState({
                        liveMatchCount: responseJson.data.live_match.length || 0
                    }, () => {
                        this.updatedLiveMatchList(responseJson.data.live_match)
                    })
                }
                else {
                    this.setState({ updatedLiveMatch: {} })
                }
            } else {
                resolve()
            }
        })
    }
    updatedLiveMatchList = (obj) => {
        let data = {};
        clearInterval(this.intervalLive);
        this.setState({ isLoading: false, updatedLiveMatch: {} })
        if (obj != null && obj != undefined && obj.length > 0) {
            let intrvalCount = 0
            this.intervalLive = setInterval(() => this.setState({ time: Date.now() }, () => {
                intrvalCount = intrvalCount + 1
                data = obj[intrvalCount - 1]
                this.setState({ updatedLiveMatch: {} }, () => {
                    this.setState({
                        updatedLiveMatch: data
                    })
                })
                if (obj.length == 1) {
                    clearInterval(this.intervalLive);
                } else if (intrvalCount >= obj.length) {
                    intrvalCount = 0
                }
            }), 3500);

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

    getBannedStateList() {
        this.setState({
            isLoading: true
        })
        let bsList = ls.get('bslist');
        let bslistTime = ls.get('bslistTime');
        let minuts = bslistTime ? Utilities.minuteDiffValue(bslistTime) : 0;
        let hours = Math.floor(minuts / 60);
        if (bsList && hours < 2) {
            let Data = Utilities.getMasterData() || {};
            Data['banned_state'] = bsList;
            let banStates = Object.keys(Data.banned_state || {});
            Constants.setValue.setBanStateEnabled(banStates.length > 0);
            Utilities.setMasterData(Data);
            this.setState({
                isLoading: false
            })
        } else {
            let param = {
            }
            getBannedStats(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let Data = Utilities.getMasterData();
                    Data['banned_state'] = responseJson.data;
                    let banStates = Object.keys(responseJson.data || {});
                    Constants.setValue.setBanStateEnabled(banStates.length > 0);
                    Utilities.setMasterData(Data);
                    ls.set('bslist', responseJson.data);
                    ls.set('bslistTime', { date: Date.now() });
                    this.setState({
                        isLoading: false
                    })
                }
            })
        }
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
    gotoDetails = (data, event) => {
        ls.remove('guru_lineup_data')
        event.preventDefault();
        if (data.tournament_id) {
            this.goToTourDetail(data)
        }
        else {
            const regex = /[ \/,\s]/g;
            const timeRemaining = Utilities.getFormatedDateTime(Utilities.getUtcToLocal(data.season_scheduled_date), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ')
            if (data.status == 1 && !timeRemaining || data.status == 0 && !timeRemaining) {
                this.props.history.push({ pathname: '/my-contests', state: { from: data.is_live == 1 ? 'lobby-live' : 'lobby-completed' } });
            } else {
                if (data.match_list.length >= 1 && data.is_tour_game != 1) {
                    data.home = data.match_list[0].home;
                    data.home_flag = data.match_list[0].home_flag;
                    data.away = data.match_list[0].away;
                    data.away_flag = data.match_list[0].away_flag;
                    data.league_name = data.league_name || data.match_list[0].league_name;
                } else if (data.is_tour_game == 1) {
                    data.tournament_name_url = data.tournament_name.replaceAll(regex, '-')
                    data.league_name_url = data.league_name.replaceAll(regex, '-')
                }

                let dateformaturl = parseURLDate(data.season_scheduled_date);
                this.setState({ LobyyData: data })
                let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + (data.is_tour_game == 1 ? data.league_name_url : data.league_name) + '-' + (data.is_tour_game == 1 ? data.tournament_name_url : (data.home + "-vs-" + data.away)) + "-" + dateformaturl;
                let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET)
                this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: CLPath } })
            }


        }
    }

    gotoGameCenter = (data, event) => {
        event.stopPropagation();
        let gameCenter = '/game-center/' + data.collection_master_id;
        this.props.history.push({ pathname: gameCenter, state: { LobyyData: data } })

    }

    gotoSecondInningDetails = (data, event) => {
        event.preventDefault();
        let dateformaturl = parseURLDate(data.season_scheduled_date);
        ls.set('is_2nd_inning', true)
        this.setState({ LobyyData: data })
        let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' +
            data.collection_master_id + '/' + data.league_name + '-' + data.match_list[0].home + "-vs-" + data.match_list[0].away + "-" + dateformaturl;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET) + '&sit=' + btoa(true)
        this.props.history.push({ pathname: CLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: data, lineupPath: CLPath, is_2nd_inning: true } })
    }

    sequentiallyCalling = () => {
        const queue = new PQueue({ concurrency: 1 });
        const myPromises = [
            () =>
                new Promise(resolve => {
                    if (this.props.location.pathname == '/lobby') {
                        let { sports_id } = this.state;
                        WSManager.setFromConfirmPopupAddFunds(false);
                        let league_id = this.getSportsLeagueId(sports_id, Constants.LOBBY_FILTER_ARRAY);
                        this.setState({ isLoaderShow: true, sports_id, league_id, filterArray: Constants.LOBBY_FILTER_ARRAY }, () => {
                            this.lobbyContestList(0, resolve)
                        })
                        this.checkOldUrl();
                    }
                }),
            () =>
                new Promise(resolve => {
                    if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
                        this.getAadharStatus(resolve)
                    } else {
                        resolve()
                    }
                }),
            () =>
                new Promise(resolve => {
                    if (Utilities.getMasterData().a_dfst == 1) {
                        this.getUserTourList(resolve)
                    } else {
                        resolve()
                    }
                }),
            () => new Promise(resolve => {
                if (WSManager.loggedIn()) {
                    this.getMyLobbyFixturesList(resolve);
                } else {
                    resolve()
                }
            }),
            () => new Promise(resolve => {
                if (Utilities.getMasterData().allow_gc == 1) {
                    this.getGcLiveMatchList(resolve)
                } else {
                    resolve()
                }
            }),
            () => new Promise(resolve => {
                this.getBannerList(resolve);
            })
        ];

        queue.addAll(myPromises);
    }


    /**
     * @description - this is life cycle method of react
     */
    componentDidMount() {
        ls.remove('is_2nd_inning')
        this.sequentiallyCalling()

        this.getBannedStateList()
        if (Utilities.getMasterData().a_xp_point == 1 && WSManager.loggedIn()) {
            this.callUserXPDetail();
        }
        if (ls.get('showMyTeam')) {
            ls.remove('showMyTeam')
        }
        ls.set('h2hTab', false);
        ls.remove('guru_lineup_data')
        window.addEventListener('scroll', this.onScrollList);
        setTimeout(() => {
            this.setState({
                onLoadCls: true
            })
        }, 10);
        Utilities.gtmEventFire('landing_screen')

        if (window.ReactNativeWebView) {
            let data = {
                action: 'SessionKey',
                targetFunc: 'SessionKey',
                page: 'lobby',
                SessionKey: WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '',
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
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
        Utilities.setScreenName('lobby')
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
        clearInterval(this.intervalLive);
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
                MCOffset: 0,
                sports_id: nextProps.selectedSport,
                updatedLiveMatch: {}
            }, () => {
                this.sequentiallyCalling()
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
            "device_type": Utilities.getDeviceType(),
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
    lobbyContestList = async (offset, resolve = () => { }) => {

        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport
        }

        this.setState({ isLoaderShow: true, isListLoading: true })
        delete param.limit;
        var api_response_data = await getLobbyFixtures(param);
        if (api_response_data && param.sports_id == Constants.AppSelectedSport) {
            const { fixture, match, booster, tournament } = api_response_data
            let fixture_match_list = {}
            let fixture_map_item = {}
            let filterLeagueList = []
            let premierLeagueData = []
            let premierLeagueDataMtor = []
            this.setState({ isLoaderShow: false })



            let fixture_list = _Map(fixture, _item => {
                let _booster = _filter(booster, (o, i) => _includes(_item.collection_master_id, i))[0] || '';
                let match_list = _filter(match, o => _includes(_item.season_ids, o.season_id));
                let _tournament = _filter(tournament, (o, i) => _includes(_item.season_ids, i));
                let second_innings_enable = _item['2nd_total'] > 0 && isDateTimePast(_item.season_scheduled_date) && !isDateTimePast(_item['2nd_inning_date'])
                let obj = {
                    ..._item, game_starts_in: convertToTimestamp(_item.season_scheduled_date), match_list, booster: _booster,
                    ...(!_isEmpty(match_list) && !second_innings_enable ? match_list[0] : {}),
                    tournament: _tournament,
                    second_innings_enable
                }
                fixture_match_list[_item.collection_master_id] = match_list;
                fixture_map_item[_item.collection_master_id] = obj;
                let objLeague = {
                    league_id: obj.league_id,
                    league_name: obj.league_name
                }
                if (filterLeagueList.filter(e => e.league_id === objLeague.league_id).length === 0) {
                    filterLeagueList.push(objLeague)
                }

                if (obj.is_featured == "1") {
                    let objLeague = { league_id: obj.league_id, league_name: obj.league_name, is_featured: obj.is_featured }
                    if (premierLeagueDataMtor.filter(e => e.league_id === objLeague.league_id).length === 0) {
                        premierLeagueDataMtor.push(objLeague)
                    }
                }
                obj.match_list.map((item, index) => {
                    item.game_starts_in = convertToTimestamp(_item.season_scheduled_date)
                    item.is_tournament = _isEmpty(_tournament) ? 0 : ((_tournament.length == 1 && !second_innings_enable) ? 1 : 0);
                    let __tournament = _filter(_tournament, o => o.season_id == item.season_id)
                    item.tournament_count = _isEmpty(__tournament) ? 0 : __tournament[0].tournament_count
                    item.tournament_name = _isEmpty(__tournament) ? "" : __tournament[0].tournament_name

                    if (item.is_featured == "1") {
                        let objLeague = { league_id: obj.league_id, league_name: obj.league_name }
                        if (premierLeagueData.filter(e => e.league_id === objLeague.league_id).length === 0) {
                            premierLeagueData.push(objLeague)
                        }
                    }
                    return item
                })

                return obj
            })

            let sortList = fixture_list.sort((a, b) => new Date(a.season_scheduled_date) - new Date(b.season_scheduled_date));
            let pinFixtures = []
            let normalFixture = []
            _Map(sortList, (obj) => {
                if (!isDateTimePast(obj.season_scheduled_date)) {
                    if (obj.is_pin == 1) {
                        pinFixtures.push(obj)
                    } else {
                        normalFixture.push(obj)
                    }
                }
            })

            this.setState({
                fixture_match_list: fixture_match_list,
                fixture_map_item: fixture_map_item,
                ContestList: [...pinFixtures, ...normalFixture], //sortList, 
                OriginalContestList: [...pinFixtures, ...normalFixture], //sortList, 
                filterLeagueList,
                premierLeagueData,
                premierLeagueDataMtor,
                SecondInningFixtures: _filter(fixture_list, (obj) => obj.second_innings_enable)
            }, resolve)

        }
        this.setState({ isListLoading: false })
    }


    getAadharStatus = async (resolve = () => { }) => {
        getUserAadharDetail().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ aadharData: responseJson.data }, resolve
                    , () => {
                        WSManager.updateProfile(this.state.aadharData)
                    }
                );
            }
        })
    }

    /**
     * @description - method to get fixtures listing from server/s3 bucket
     */
    getMyLobbyFixturesList = async (resolve = () => { }) => {
        if (Constants.AppSelectedSport == null)
            return;

        let param = {
            "sports_id": Constants.AppSelectedSport,
            // "limit": this.state.limit,
            // "offset": this.state.MCOffset
        }


        this.setState({ isLoaderShow: true, isListLoading: true, myContestData: [] })
        // delete param.limit;
        var api_response_data = await getMyLobbyFixtures(param);
        if (api_response_data) {
            resolve()
            this.setState({ isLoaderShow: false })
            let _data = api_response_data.data || [];
            let tmpArray = []

            const { fixture_match_list, fixture_map_item } = this.state
            let data = _Map(_data, (_obj) => {

                let obj = {
                    ..._obj,
                    match_list: fixture_match_list[_obj.collection_master_id] || [],
                    ...fixture_map_item[_obj.collection_master_id]
                }
                // if (obj['2nd_inning_count'] > 0) {
                if (obj.dfs_count == 0 && obj['2nd_inning_count'] > 0) {
                } else {
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
            else {
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
    filterLobbyResults = (filterObj, isLocalFilter) => {
        let league_id = isLocalFilter ? filterObj : (filterObj.league_id ? filterObj.league_id : "");
        this.setState({ league_id: league_id }, function () {
            this.filterFixturesLocally(league_id)
        })

        let filterArray = this.setFilterArray(league_id);
        Constants.setValue.setFilter(filterArray);
        this.setState({ league_id: league_id, showLobbyFitlers: false, offset: 0, filterArray: filterArray })
        this.props.hideFilterData()
    }

    filterFixturesLocally(leagueIds) {
        let allFixtures = this.state.OriginalContestList;
        if (leagueIds == '') {
            this.setState({ ContestList: allFixtures })
        }
        else {
            let filteredList = [];
            for (var i = 0; i < allFixtures.length; i++) {
                if (leagueIds.includes(allFixtures[i].league_id)) {
                    filteredList.push(allFixtures[i])
                }

            }
            this.setState({ ContestList: filteredList })
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
    getBannerList = (resolve = () => { }) => {
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
                    resolve()
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
        // _Map(this.getSelectedbanners(bdata), (item, idx) => {
        _Map(bdata, (item, idx) => {
            if (item.game_type_id == 0 || WSManager.getPickedGameTypeID() == item.game_type_id) {
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
            }
        })
        if (refData) {
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
            else {
                if (banner.banner_type_id == '6') {
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
        if (isRFBanner) {
            this.showRFHTPModalFn()
        }
        else {
            if (WSManager.loggedIn()) {
                BannerRedirectLink(result, this.props)
            }
            else {
                this.props.history.push({ pathname: '/signup' })
            }
        }
    }
    showRFHTPModalFn = () => {
        this.setState({ showRFHTPModal: true })
    }
    hideRFHTPModalFn = () => {
        this.setState({ showRFHTPModal: false })
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
        let fArray = _filter(this.state.ContestList, (obj) => {
            return item.collection_master_id != obj.collection_master_id
        })
        this.setState({
            ContestList: fArray
        })
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

    renderGameCenterLiveGameCard = () => {
        let updatedLiveMatchList = this.state.updatedLiveMatch;
        return (
            // <div onClick={(event) => !_isEmpty(updatedLiveMatchList) && this.gotoGameCenter(updatedLiveMatchList, event)} className='bg-game-center'>
            //     <div className='bg-image'>
            //         <div></div>
            //         <div className={`go-to-game-center-of  ${this.state.liveMatchCount == 1 ? ' no-ani' : ''}`}>
            //             <div className="goto-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</div>
            //             {
            //                 !_isEmpty(updatedLiveMatchList) &&
            //                 <div className={`h-a-container `}>
            //                     <img className='flag-home' src={updatedLiveMatchList.home_flag ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag) : Images.NODATA} alt="" />
            //                     <div className="verses-h-a">
            //                         {updatedLiveMatchList.home}{" " + AppLabels.VS + " "}{updatedLiveMatchList.away}</div>
            //                     <img className='flag-away' src={updatedLiveMatchList.away_flag ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag) : Images.NODATA} alt="" />
            //                 </div>
            //             }

            //         </div>
            //         <div className='arrow-icon-container'>
            //                     <i className="icon-arrow-right iocn-first"></i>
            //                     <i className="icon-arrow-right iocn-second"></i>
            //                     <i className="icon-arrow-right iocn-third"></i>

            //                     </div>
            //         <div className='live-container'>
            //             <img src={Images.LIVE_GC} alt='' className='image'></img>
            //             <div className='live-text'>{AppLabels.LIVE}</div>

            //         </div>
            //     </div>

            // </div>
            <div onClick={(event) => !_isEmpty(updatedLiveMatchList) && this.gotoGameCenter(updatedLiveMatchList, event)} className='bg-game-center-container'>
                <div className='inner-view-live'>
                    <div className={`  ${this.state.liveMatchCount == 1 ? ' no-ani' : ''}`}>

                        {
                            !_isEmpty(updatedLiveMatchList) &&
                            <div className="game-center-view">
                                <div className='image-game-center'><img className='home-img' src={updatedLiveMatchList.home_flag ? Utilities.teamFlagURL(updatedLiveMatchList.home_flag) : Images.NODATA} alt="" />
                                    <img className='away-img' src={updatedLiveMatchList.away_flag ? Utilities.teamFlagURL(updatedLiveMatchList.away_flag) : Images.NODATA} alt="" /></div>
                                <div className='responsive-view-cotainer'>
                                    <span className="go-to-game-center-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</span>
                                    <span className="team-name">
                                        {updatedLiveMatchList.home}{" " + AppLabels.VS + " "}{updatedLiveMatchList.away}</span>
                                </div>

                            </div>
                        }

                    </div>
                    <div className='arrow-icon-container'>
                        <i className="icon-arrow-right iocn-first"></i>
                        <i className="icon-arrow-right iocn-second"></i>
                        <i className="icon-arrow-right iocn-third"></i>

                    </div>

                </div>

            </div>

        )
    }
    callUserXPDetail() {
        getUserXPCard().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPDetail: responseJson.data.user_xp_card
                })
            }
        })
    }
    calcPer = (point, total) => {
        point = parseInt(point);
        total = parseInt(total);
        let per = ((point / total) * 100).toFixed(2) + '%';
        return per;
    }
    goToPage = (pathname) => {
        this.props.history.push({ pathname: pathname, state: { goBackProfile: true, userXPDetail: this.state.userXPDetail } });
    }


    renderPREDCard = () => {
        if (Utilities.getMasterData().a_sports_prediction_bnr != 1 || ls.get('selectedSports') == SportsIDs.MOTORSPORTS || ls.get('selectedSports') == SportsIDs.tennis) {
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
                            <p>{CommonLabels.PLAY_PREDICTION_WIN}</p>
                        </div>
                    </div>
                </li>

            )
        }
        return ''
    }

    goToMyContest = () => {
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

    clickEarnCoins = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push("/earn-coins")
        } else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    showDFSHTPModal = () => {
        this.setState({
            dfsHTP: true
        })
    }

    hideDFSHTPModal = () => {
        this.setState({
            dfsHTP: false
        })
    }

    showDFSRulesModal = () => {
        this.setState({
            dfsHTP: false,
            showDFSRulesModal: true
        })
    }

    hideDFSRulesModal = () => {
        this.setState({
            showDFSRulesModal: false
        })
    }

    getUserTourList = async (resolve = () => { }) => {
        if (Constants.AppSelectedSport == null)
            return;
        let param = {
            "sports_id": Constants.AppSelectedSport
        }
        let apiResponse = await getDFSTLobbyTournament(param)
        if (apiResponse) {
            resolve()
            let tmpArray = []
            if (apiResponse && apiResponse.data && apiResponse.data.user && apiResponse.data.user.tournament_id) {
                this.setState({
                    load: true
                })
                tmpArray.push(apiResponse.data.user)
            }
            let ccc = tmpArray.concat(this.state.myContestData)
            this.setState({
                pinTournament: apiResponse && apiResponse.data && apiResponse.data.pin,
                userTournament: apiResponse && apiResponse.data && apiResponse.data.user
            })
        }
    }

    goToTourDetail = (item) => {
        if (WSManager.loggedIn()) {
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + item.tournament_id,
                state: {
                    tourId: item.tournament_id,
                    completedItem: item.status == 3 ? true : false
                }
            })
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    showTourList = (event) => {
        
        if (event) {
            event.stopPropagation();
        }
        if (WSManager.loggedIn()) {
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-list'
            })
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    goToTourDetailLB = (event, TourFilter) => {
        event.stopPropagation();
        // console.log('ppp', item)
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + TourFilter[0].tournament_id,
            state: {
                tourId: TourFilter[0].tournament_id,
            }
        })
    }


    ContestDataWithTour = (userTournament, myContestData) => {
        if (userTournament && userTournament.tournament_id) {
            let tmpArray = []
            tmpArray.push(userTournament)
            return [...tmpArray, ...myContestData]
        }
        else {
            return myContestData
        }
    }
    activeTabsPremium = (item) => {
        this.setState({
            league_id: this.state.league_id == item.league_id ? '' : item.league_id
        }, () => {
            this.filterLobbyResults(this.state.league_id, true)
        })
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
            ismodeListLoad,
            MerchandiseList,
            SecondInningFixtures,
            updatedLiveMatch,
            dfsHTP,
            showDFSRulesModal,
            pinTournament,
            userTournament,
            load,
            premierLeagueData,
            premierLeagueDataMtor,
            userXPDetail
        } = this.state;

        const settings = {
            className: "slider variable-width",
            dots: false,
            infinite: false,
            centerMode: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: true
        };
        let FitlerOptions = {
            showLobbyFitler: showLobbyFitlers,
            filtered_league_id: league_id
        }

        let bannerLength = BannerList.length;
        let showToggleSec = DFSTourEnable
        var showLobbySportsTab = process.env.REACT_APP_LOBBY_SPORTS_ENABLE == 1 ? true : false
        let ContestDataWithTour = this.ContestDataWithTour(userTournament, myContestData)

        let isMaxPt = userXPDetail.max_level == userXPDetail.level_number;
        let total = isMaxPt ? parseInt(userXPDetail.max_end_point) - parseInt(userXPDetail.start_point) : parseInt(userXPDetail.next_level_start_point) - parseInt(userXPDetail.start_point);
        let point = parseInt(userXPDetail.point) - parseInt(userXPDetail.start_point);
        let maxExc = (userXPDetail.max_end_point && parseInt(userXPDetail.point) > parseInt(userXPDetail.max_end_point)) ? true : false;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="transparent-header web-container tab-two-height pb0 DFS-tour-lobby">
                        <MetaComponent page="lobby" />
                        {
                            !ismodeListLoad &&
                            <Filter customLeagues={this.state.filterLeagueList} leagueList={league_id} {...this.props} FitlerOptions={FitlerOptions} hideFilter={this.hideFilter} filterLobbyResults={this.filterLobbyResults}></Filter>
                        }

                        {/* <div className={"header-fixed-strip" + (showLobbySportsTab ? " header-fixed-strip-2" : '')}>
                            <div className={"strip-content" + (showShadow ? ' strip-content-shadow' : '')}>
                                <span className='head-bg-strip'>{AppLabels.DAILY_FANTASY}</span>
                                <a className='decoration-under'
                                    href
                                    onClick={(e) => { this.showDFSHTPModal(e) }}
                                >
                                    {AppLabels.HOW_TO_PLAY_FREE}
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
                                    <span className='head-bg-strip'>{AppLabels.DAILY_FANTASY}</span>
                                    <a className='decoration-under'
                                        href
                                        onClick={(e) => { this.showDFSHTPModal(e) }}
                                    >
                                        {AppLabels.HOW_TO_PLAY_FREE}
                                    </a>
                                </div>
                            </div>

                            <>
                                <div className={showToggleSec ? "tp-btn-sec" : ''}>
                                    {
                                        showToggleSec &&
                                        <div className="my-lobby-dfs-tabs pt5">
                                            <Button className='btn btnStyle btn-rounded small' onClick={() => this.showTourList()}> {AppLabels.TOURNAMENT}</Button>
                                        </div>
                                    }
                                    {
                                        WSManager.loggedIn() && ContestList.length > 0 &&
                                        <div className={"contest-action single-btn-contest-action" + (bannerLength == 0 ? (showToggleSec ? ' pt5' : ' mt15') : ' pt5')}>
                                            {
                                                Constants.SELECTED_GAMET != Constants.GameType.DFS && Utilities.getMasterData().private_contest == '1' &&
                                                <NavLink exact to={"/create-contest"} className="btn btnStyle btn-rounded small">
                                                    <span className="text-uppercase">{AppLabels.Create_a_Contest}</span>
                                                </NavLink>
                                            }
                                            <NavLink exact to="/private-contest" className="btn btnStyle btn-rounded small">
                                                <span className="league-code-btn text-uppercase">
                                                    {AppLabels.JOIN_CONTEST}
                                                </span>
                                            </NavLink>
                                        </div>
                                    }
                                </div>





                                {/* {premierLeagueData.length > 0 &&

                                    <div className="premium-league-container ">
                                        <Tab.Container id='top-sports-slider'>
                                            <div className="sports-tab-nav custom-scrollbar ">
                                                <i className='icon-stock_up' />
                                                <Nav>
                                                    <Suspense fallback={<div />} > <ReactSlickSlider settings={settings}>
                                                        {
                                                            _Map(premierLeagueData, (item, idx) => {

                                                                return (
                                                                    <NavItem className="premium-league-view" onClick={() => this.activeTabsPremium(item)} >
                                                                        <span className={`premium-league-tabs  ${league_id == item.league_id ? ' active ' : ' inactive '}`}
                                                                            style={{ width: 100 }}
                                                                        >
                                                                            {item.league_name}
                                                                        </span>
                                                                    </NavItem>
                                                                )
                                                            })
                                                        }
                                                    </ReactSlickSlider>
                                                    </Suspense>
                                                </Nav>
                                            </div>

                                        </Tab.Container>
                                    </div>
                                } */}




                                {/* {premierLeagueDataMtor.length > 0 &&

                                    <div className="premium-league-container ">
                                        <Tab.Container id='top-sports-slider'>
                                            <div className="sports-tab-nav custom-scrollbar ">
                                                <i className='icon-stock_up' />
                                                <Nav>
                                                    <Suspense fallback={<div />} > <ReactSlickSlider settings={settings}>
                                                        {
                                                            _Map(premierLeagueDataMtor, (item, idx) => {

                                                                return (
                                                                    <NavItem className="premium-league-view" onClick={() => this.activeTabsPremium(item)} >
                                                                        <span className={`premium-league-tabs  ${league_id == item.league_id ? ' active ' : ' inactive '}`}
                                                                            style={{ width: 100 }}
                                                                        >
                                                                            {item.league_name}
                                                                        </span>
                                                                    </NavItem>
                                                                )
                                                            })
                                                        }
                                                    </ReactSlickSlider>
                                                    </Suspense>
                                                </Nav>
                                            </div>

                                        </Tab.Container>
                                    </div>
                                } */}
                                {
                                    WSManager.loggedIn() && ContestDataWithTour && ContestDataWithTour.length > 0 &&
                                    <div className="my-lobby-fixture-wrap">
                                        <div className="top-section-heading md">
                                            {AppLabels.MY_CONTEST}
                                            <a href onClick={() => this.goToMyContest()}>{AppLabels.VIEW} {AppLabels.All}</a>
                                        </div>
                                        <DMMyContestSlider
                                            FixtureData={ContestDataWithTour}
                                            gotoDetails={this.gotoDetails}
                                            getMyLobbyFixturesList={this.getMyLobbyFixturesList}
                                            timerCallback={() => this.timerCompletionCall(ContestDataWithTour)}
                                        />
                                    </div>
                                }
                                {
                                    SecondInningFixtures.length > 0 &&
                                    <div className="my-lobby-fixture-wrap second-inning">
                                        <div className="top-section-heading md">
                                            <span className='live-text-sec'>{AppLabels.LIVE}</span> {AppLabels.MATCHES}
                                        </div>
                                        <MyContestSlider
                                            FixtureData={SecondInningFixtures}
                                            gotoDetails={this.gotoSecondInningDetails}
                                            timerCallback={(matchobj) => this.timerSecInngCompletionCall(matchobj)}
                                            isSecondInning={true}
                                            getMyLobbyFixturesList={() => ''}
                                        />
                                    </div>
                                }
                                {
                                    DFSTourEnable && pinTournament && pinTournament.tournament_id &&
                                    <div className={`lobby-tour-sec tour-sec ${SecondInningFixtures.length > 0 ? ' pt0' : ''}`}>
                                        <div className="top-section-heading md spc-none">
                                            {/* 
                                                {AppLabels.DFS_TOURNAMENT}
                                                <a href onClick={() => this.goToTourDetail(pinTournament)}>{AppLabels.VIEW} {AppLabels.All}</a>
                                             */}
                                            {AppLabels.DFS_TOURNAMENT}
                                            <a href onClick={() => this.showTourList()}>{AppLabels.VIEW} {AppLabels.All}</a>
                                        </div>
                                        <NDFSTourCard item={pinTournament} goToDetail={this.goToTourDetail} />
                                    </div>
                                }
                               
                               { ContestList && ContestList.length == 0 && <>
                                    {WSManager.loggedIn() && Utilities.getMasterData().a_xp_point == 1 &&
                                        <div style={{margin: " 15px 15px 0"}}>
                                            <div className="xpprofile-card m-0 mb20 p-0">
                                                <div className="border-box-inn-sec border-box-inn-sec-new">
                                                    <div className="xpprofile-card-body">

                                                        <div className="level-text-heading">
                                                            {AppLabels.YOUR_CURRENT_XP_LEVEL}
                                                        </div>
                                                        <div className="xpprofile-card-slider xpprofile-card-slider-new">
                                                            <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point, total)) }}></div>
                                                            {!maxExc && <span>{userXPDetail.level_number}</span>}
                                                            {
                                                                maxExc ?
                                                                    <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                                                                    :
                                                                    <span className="next-lvl">{userXPDetail.next_level}</span>
                                                            }
                                                        </div>

                                                        <div className='earn-xp-button'>
                                                            <Button className="button button-primary-rounded-sm" onClick={() => this.goToPage('/experience-points')}><img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {AppLabels.EARN_XP}</Button>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>}
                                </>}
                                <div className="upcoming-lobby-contest">
                                    <div className="top-section-heading md">{AppLabels.UPCOMING} {AppLabels.MATCHES}</div>
                                    <Row className={bannerLength > 0 ? '' : ''}>
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
                                                                            <>
                                                                                {
                                                                                    item.season_game_count != 1 ?
                                                                                        <DMMultiCard
                                                                                            {...this.props}
                                                                                            key={item.collection_master_id}
                                                                                            indexKey={item.collection_master_id}
                                                                                            ContestListItem={item}
                                                                                            gotoDetails={this.gotoDetails}
                                                                                            CollectionInfoShow={this.CollectionInfoShow}
                                                                                            IsCollectionInfoHide={this.CollectionInfoHide}
                                                                                            timerCallback={() => this.timerCompletionCall(item)}
                                                                                        />
                                                                                        :
                                                                                        <FixtureContest
                                                                                            {...this.props}
                                                                                            onLBClick={(e) => {
                                                                                                e.stopPropagation()
                                                                                                CustomHeader.LBModalShow()
                                                                                            }}
                                                                                            indexKey={item.collection_master_id}
                                                                                            ContestListItem={item}
                                                                                            gotoDetails={this.gotoDetails}
                                                                                            gotoGameCenter={this.gotoGameCenter}
                                                                                            showTourList={this.showTourList}
                                                                                            CollectionInfoShow={this.CollectionInfoShow}
                                                                                            IsCollectionInfoHide={this.CollectionInfoHide}
                                                                                            timerCallback={() => this.timerCompletionCall(item)}
                                                                                            teamNameText={true}
                                                                                            goToTourDetailLB={this.goToTourDetailLB}
                                                                                        />
                                                                                }
                                                                            </>
                                                                        }
                                                                        {
                                                                            index === 1 && this.renderPREDCard()
                                                                        }
                                                                        {
                                                                            ContestList.length == 1 && index === 0 &&
                                                                            <>
                                                                                {WSManager.loggedIn() && Utilities.getMasterData().a_xp_point == 1 &&
                                                                                    <li >
                                                                                        <div className="xpprofile-card m-0 mb20 p-0">
                                                                                            <div className="border-box-inn-sec border-box-inn-sec-new">
                                                                                                <div className="xpprofile-card-body">

                                                                                                    <div className="level-text-heading">
                                                                                                        {AppLabels.YOUR_CURRENT_XP_LEVEL}
                                                                                                    </div>
                                                                                                    <div className="xpprofile-card-slider xpprofile-card-slider-new">
                                                                                                        <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point, total)) }}></div>
                                                                                                        {!maxExc && <span>{userXPDetail.level_number}</span>}
                                                                                                        {
                                                                                                            maxExc ?
                                                                                                                <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                                                                                                                :
                                                                                                                <span className="next-lvl">{userXPDetail.next_level}</span>
                                                                                                        }
                                                                                                    </div>

                                                                                                    <div className='earn-xp-button'>
                                                                                                        <Button className="button button-primary-rounded-sm" onClick={() => this.goToPage('/experience-points')}><img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {AppLabels.EARN_XP}</Button>

                                                                                                    </div>

                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </li>}
                                                                            </>
                                                                        }
                                                                        {
                                                                            ContestList.length > 1 && index === 1 &&
                                                                            <>
                                                                                {WSManager.loggedIn() && Utilities.getMasterData().a_xp_point == 1 &&
                                                                                    <li >
                                                                                        <div className="xpprofile-card m-0 mb20 p-0">
                                                                                            <div className="border-box-inn-sec border-box-inn-sec-new">
                                                                                                <div className="xpprofile-card-body">

                                                                                                    <div className="level-text-heading">
                                                                                                        {AppLabels.YOUR_CURRENT_XP_LEVEL}
                                                                                                    </div>
                                                                                                    <div className="xpprofile-card-slider xpprofile-card-slider-new">
                                                                                                        <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point, total)) }}></div>
                                                                                                        {!maxExc && <span>{userXPDetail.level_number}</span>}
                                                                                                        {
                                                                                                            maxExc ?
                                                                                                                <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                                                                                                                :
                                                                                                                <span className="next-lvl">{userXPDetail.next_level}</span>
                                                                                                        }
                                                                                                    </div>

                                                                                                    <div className='earn-xp-button'>
                                                                                                        <Button className="button button-primary-rounded-sm" onClick={() => this.goToPage('/experience-points')}><img src={Images.EARN_XPPOINTS} alt="" width="16px" /> {AppLabels.EARN_XP}</Button>

                                                                                                    </div>

                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </li>}
                                                                            </>
                                                                        }
                                                                        {
                                                                            (Utilities.getMasterData().allow_gc == 1 &&
                                                                                !_isEmpty(updatedLiveMatch)) &&
                                                                            index === 1 && this.renderGameCenterLiveGameCard()
                                                                        }

                                                                    </React.Fragment>
                                                                );
                                                            })
                                                        }
                                                        {
                                                            (ContestList.length < 2 && !isListLoading) && this.renderPREDCard()
                                                        }
                                                        {
                                                            (Utilities.getMasterData().allow_gc == 1 && (ContestList.length < 2 && !isListLoading) &&
                                                                !_isEmpty(updatedLiveMatch))
                                                            && this.renderGameCenterLiveGameCard()
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
                        </div>

                        {showContestDetail &&
                            <ContestDetailModal IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={FixtureData} {...this.props} />
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
                                <DFSHTPModal
                                    ModalData={{
                                        show: showHTP,
                                        hide: this.hideHTPModal
                                    }}
                                />
                            </Suspense>
                        }
                        {
                            dfsHTP &&
                            <Suspense fallback={<div />} >
                                <DailyFantasyHTP
                                    mShow={dfsHTP}
                                    mHide={this.hideDFSHTPModal}
                                    rulesModal={this.showDFSRulesModal}
                                />
                            </Suspense>
                        }
                        {showDFSRulesModal &&
                            <RulesScoringModal MShow={showDFSRulesModal} MHide={this.hideDFSRulesModal} />
                        }
                       
                        {/* {
                            this.state.geoLocation && (
                                <GeoLocationModal geoLocation={this.state.geoLocation} />
                            )
                        } */}
                    </div>
                )}
            </MyContext.Consumer>

        )
    }
}

export default DMLobby
