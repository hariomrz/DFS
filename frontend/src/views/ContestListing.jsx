import React, { lazy, Suspense } from 'react';
import { Row, Col, Button, ProgressBar, OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import { MyContext } from '../InitialSetup/MyProvider';
import { Utilities, _Map, _isUndefined, _filter, _cloneDeep, parseURLDate, getPrizeInWordFormat, checkBanState, convertToTimestamp, isDateTimePast, _isEmpty } from '../Utilities/Utilities';
import { setValue, AppSelectedSport, preTeamsList, DARK_THEME_ENABLE } from '../helper/Constants';
import { NavLink } from "react-router-dom";
import { Sports, SportsIDs } from "../JsonFiles";
import { getFixtureDetail, getFixtureDetailMultiGame, getFixtureContestList, getUserTeams, getMultigameUserTeams, joinContest, joinContestNetworkfantasy, joinContestWithMultiTeam, joinContestWithMultiTeamNF, getH2HContestList, getH2HBannerList, joinContestH2H, getH2HJoinedContestList, getUserAadharDetail, getMultigameMyContest, getMyContest } from "../WSHelper/WSCallings";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ls from 'local-storage';
import Images from '../components/images';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import MetaData from "../helper/MetaData";
import CustomHeader from '../components/CustomHeader';
import CollectionSlider from "./CollectionSlider";
import { Thankyou, ContestDetailModal, ConfirmationPopup, UnableJoinContest, ShareContestModal, CollectionInfoModal, RulesScoringModal, ShowMyAllTeams, SecondIngHTPModal, WhatIsRookieModal, RookieContestHTP } from '../Modals';
import { MomentDateComponent, NoDataView } from '../Component/CustomComponent';
import { createBrowserHistory } from 'history';
import * as Constants from "../helper/Constants";
import MyTeams from './MyTeams';
import MyContestList from "./MyContestList";
import { ContestListingCoachMarkModal, MGContestListingCoachMarkModal } from '../Component/CoachMarks';
import { DownloadAppBuyCoinModal } from "../Modals";
import FilterNew from "../components/filterNew";
import CountdownTimer from './../views/CountDownTimer';
import GuruTabDetail from '../Component/Guru/GuruTabDetail';
import H2HBannerSlider from '../Component/H2HChallenge/H2HBannerSlider';
import H2HJoinedContestSlider from '../Component/H2HChallenge/H2HJoinedContestSlider';
import H2HOpponentDetailModal from '../Component/H2HChallenge/H2HOpponentDetailModal';
import WhatIsH2HChallengeModal from '../Component/H2HChallenge/WhatIsH2HChallengeModal';
import DMCollectionSlider from "../Component/DFSWithMultigame/DMCollectionSlider";
import H2hCard from "../Component/H2H/H2hCard";
import TournamentLeaderboardModal from '../Modals/TournamentLeaderboardModal';
import SecondIngFanRules from '../Modals/SecondIngFanRules';
const ReactSlickSlider = lazy(() => import('../Component/CustomComponent/ReactSlickSlider'));
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
var globalThis = null;
var onlyRookieC = true;

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ index }) => {
    return (
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={index} className="contest-list m">
                <div className="shimmer-container">
                    <div className="shimmer-top-view">
                        <div className="shimmer-line">
                            <Skeleton height={9} />
                            <Skeleton height={6} />
                            <Skeleton height={4} width={100} />
                        </div>
                        <div className="shimmer-image">
                            <Skeleton width={30} height={30} />
                        </div>
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="progress-bar-default">
                            <Skeleton height={6} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={60} />
                                <Skeleton height={4} width={60} />
                            </div>
                        </div>
                        <div className="shimmer-buttin">
                            <Skeleton height={30} />
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default class ContestListing extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineup_master_id: '',
            lineup_master_idArray: [],
            FixturedContest: [],
            sortContestList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            showContestDetail: false,
            WinnerCount: [],
            FixtureData: '',
            FixturedContestTotal: 0,
            SortContestTotal: '',
            FixturedPinContest: [],
            TeamList: [],
            TotalTeam: [],
            LobyyData: !_isUndefined(props.location.state) ? props.location.state.LobyyData : [],
            showConfirmationPopUp: false,
            userTeamListSend: [],
            showSharContestModal: false,
            activeTab: "",
            showLoadMore: true,
            showThankYouModal: false,
            hasMore: false,
            isListLoading: false,
            LoaderShow: false,
            showContestListFitler: false,
            isFilterApplied: false,
            showAlert: false,
            entry_fee_from: "",
            entry_fee_to: "",
            participants_from: "",
            participants_to: "",
            prizepool_from: "",
            prizepool_to: "",
            allowCollection: Utilities.getMasterData().a_collection,
            showCollectionInfo: false,
            showGroupView: typeof ls.get("show_group_view") == 'undefined' || ls.get("show_group_view") == null ? true : ls.get("show_group_view"),
            allContestData: [],
            isNewCJoined: false,
            activeContestTab: 0,
            isLoading: true,
            myContestCount: 0,
            myTeamCount: 0,
            showRulesModal: false,
            allTeamData: [],
            showTeamModal: false,
            windowWidth: window.innerWidth > 550 ? 540 : window.innerWidth,
            showCM: true,
            CLCoachMarkStatus: ls.get('cl-coachmark') ? ls.get('cl-coachmark') : 0,
            showMG: true,
            MGCoachMarkStatus: ls.get('MGCLC') ? ls.get('MGCLC') : 0,
            showModalNo: ((Constants.SELECTED_GAMET == Constants.GameType.DFS && ls.get('cl-coachmark') != 1) || (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && ls.get('MGCLC') != 1)) ? 1 : 2,

            showUJC: false,
            showDAM: false,
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                fixture: true,
                filter: false,
                title: '',
                hideShadow: false,
                goBackLobby: !_isUndefined(props.location.state) ? props.location.state.isFromPM : false

                // goBackLobby: true,//!_isUndefined(props.location.state) ? props.location.state.isFromPM : false
            },
            isSecondInning: !_isUndefined(props.location.state) ? (props.location.state.is_2nd_inning || parsed.sit) : (parsed.sit || false),
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            ContestTabList: [
                {
                    label: AppLabels.ALL_CONTEST,
                    value: 0
                },
                {
                    label: AppLabels.MY_CONTEST,
                    value: 1
                },
                {
                    label: AppLabels.MYTEAMS,
                    value: 2
                }

            ],
            allowH2HChallenge: Utilities.getMasterData().h2h_challenge == '1',
            showH2H: false,
            H2Hchallange: [],
            H2HBannerList: [],
            H2HJoinedContestList: [],
            showOppData: false,
            showH2hModal: false,
            isDFSMulti: Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? true : false,
            entry_fee_max: 0,

            participants_max: 0,
            prizepool_max: 0,
            myContestListData: [],
            showRookieHTP: false,
            sort_field: '',//fantasy_score
            sort_order: 'DESC',
            filterApply: true,
            preSortContestList: [],
            filterAppliedCount: 0,
            sortAppliedCount: 0,
            bn_state: localStorage.getItem('banned_on'),
            playFreeContest: localStorage.getItem('playFreeContest'),
            geoPlayFree: localStorage.getItem('geoPlayFree'),
            isMegaExsist: false,
            isCWRookie: false,
            isSecInng: false,
            TournamentList: [],
            showTourLeadModal: false,
            TourFilterList: [],

            c_name: '',
            e_fees: '',
            boxForm: false,
            contdet: {},
            tooltiOverLay: false
        }
    }

    h2hCallangeData = () => {
        if (this.state.showH2H) {
            let matchParam = this.props.match.params;
            // this.apiCallH2HContest(matchParam)
            this.apiCallH2HBanner()
            if (WSManager.loggedIn()) {
                this.apiCallH2HJoinedContestList(matchParam)

            }

        }
    }
    apiCallH2HContest = (CollectionData) => {
        this.setState({ boxForm: true })
        let param = {
            "sports_id": Sports[CollectionData.sportsId],
            "collection_master_id": CollectionData.collection_master_id,
        }
        getH2HContestList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    H2Hchallange: responseJson.data ? responseJson.data : [],
                    c_name: responseJson.data[0].contest_title,
                    e_fees: responseJson.data[0].entry_fee,
                    contdet: responseJson.data[0]

                }, () => {
                    this.setState({
                        boxForm: false
                    })
                })
                let list = responseJson.data;
                let inital = 4;
                let counter = 0;

                for (var i = 0; i < list.length; i++) {
                    if (inital <= i) {
                        if (counter == 4) {
                            list[i]['status'] = 1;
                            counter = 0;
                            counter = counter + 1;
                        }
                        else {
                            counter = counter + 1;
                            list[i]['status'] = counter;

                        }
                    }
                    else {
                        counter = counter + 1;

                        list[i]['status'] = counter;
                    }


                }
                this.setState({
                    H2Hchallange: list ? list : []
                })

            }
        })

    }
    apiCallH2HBanner = () => {
        let param = {}
        getH2HBannerList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    H2HBannerList: responseJson.data
                })

            }
        })
    }
    apiCallH2HJoinedContestList = (CollectionData) => {
        let param = {
            "sports_id": Sports[CollectionData.sportsId],
            "collection_master_id": CollectionData.collection_master_id,
            profileData: ''
        }
        getH2HJoinedContestList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    H2HJoinedContestList: responseJson.data
                })

            }
        })
    }

    showTeam = (e, data) => {
        e.stopPropagation()
        this.setState({
            showTeamModal: true,
            allTeamData: data
        })
    }
    hideTeam = () => {
        this.setState({
            showTeamModal: false
        })
    }

    convertIntoWhole = (x) => {
        var no = Math.round(x)
        return no;
    }

    /**
     * @description lifecycle method of react,
     * method to load data of contest listing and user lineup list
     */
    componentDidMount() {
        onlyRookieC = true;
        WSManager.setH2hMessage(false);
        // if(this.props && this.props.location && this.props.location.state && this.props.location.state.LobyyData && this.props.location.state.LobyyData.is_h2h == 1){
        if (this.state.LobyyData && this.state.LobyyData.is_h2h == 1) {
            this.apiCallH2HContest(this.props.match.params)
        }
        // if (ls.get('h2hTab')) {

        //     this.setState({showH2H: true })
        //     this.switchRFClassicTab(2)
        // }
        Utilities.handleAppBackManage('contest-listing')
        const history1 = createBrowserHistory();
        const location1 = history1.location;
        const searchQ = queryString.parse(location1.search);
        if (searchQ.sit) {
            this.setState({
                isSecondInning: true,
                isSecInng: true
            })
        }
        let url = window.location.href;
        let toRosterTab = ls.get('toRosterTab') && ls.get('toRosterTab') ? true : false;
        let activetab = ls.get('guruTab') && ls.get('guruTab') == 3 ? true : false;
        let showMyTeamTab = ls.get('showMyTeam') && ls.get('showMyTeam') == 1 ? true : false;
        if (url.includes('#')) {
            let tab = url.split('#')[1];
            url = url.split('#')[0];
            ls.set('guruTab', 2)
            this.setState({
                activeContestTab: toRosterTab ? 3 : activetab ? 2 : tab
            }, () => {
                this.setState({
                    HeaderOption: {
                        back: true,
                        fixture: true,
                        filter: false,//tab != 0 ? false : true,
                        title: '',
                        hideShadow: false,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                        // goBackLobby: true,//!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                    }
                })

            })
            if (!ls.set('is_2nd_inning')) {
                window.history.replaceState("", "", url + "#" + toRosterTab ? 3 : activetab ? 2 : this.state.activeContestTab);
            }
            ls.set('toRosterTab', false)
        }
        else if (showMyTeamTab) {
            // let tab = url.split('#')[1];
            // url = url.split('#')[0];
            // ls.set('guruTab', 2)
            this.setState({
                activeContestTab: 2
            }, () => {
                this.setState({
                    HeaderOption: {
                        back: true,
                        fixture: true,
                        filter: false,
                        title: '',
                        hideShadow: false,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                        // goBackLobby: true,//!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                    }
                })

            })
            window.history.replaceState("", "", url + "#" + 2);
            ls.set('toRosterTab', false)
        }
        else {
            if (toRosterTab || activetab) {
                this.setState({ activeContestTab: toRosterTab ? 3 : activetab ? 2 : 0 })
                ls.set('toRosterTab', false)
                ls.set('guruTab', 2)

            }

        }
        Utilities.scrollToTop()
        globalThis = this;
        const matchParam = this.props.match.params;
        let RFContId = ls.get('RFContestID')
        if (parsed.sgmty) {
            let urlGT = atob(parsed.sgmty)
            WSManager.setPickedGameType(urlGT);
        }
        if (this.props && this.props.location && this.props.location.state && this.props.location.state.LobyyData) {
            this.setState({
                FixturedDetail: this.props.location.state.LobyyData
            })
        }
        else {
            this.FixtureDetail(matchParam);
        }

        window.addEventListener('resize', (event) => {
            this.setState({
                windowWidth: window.innerWidth > 550 ? 540 : window.innerWidth,
            })
        });

        if (WSManager.loggedIn()) {
            // this.getUserLineUpListApi(matchParam);
            WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            if (this.state.showModalNo != 1 && Constants.BanStateEnabled && !WSManager.getProfile().master_state_id && Utilities.getMasterData().a_aadhar != "1") {
                CustomHeader.showBanStateModal({ isFrom: 'CL' });
            }
        }
        // if (Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.allowRevFantasy && (Constants.RFContestId == matchParam.collection_master_id || RFContId == matchParam.collection_master_id)) {
        //     if (ls.get('h2hTab')) {

        //     }
        //     else {

        //     }

        // }
        // else {
        this.FixtureContestList(matchParam);
        // }

        this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : this.props.location.state.LobyyData);
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'contestlist');

        if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            if (WSManager.getProfile().aadhar_status != 1) {
                getUserAadharDetail().then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ aadharData: responseJson.data }, () => {
                            WSManager.updateProfile(this.state.aadharData)
                        });
                    }
                })
            }
            else {
                let aadarData = {
                    'aadhar_status': WSManager.getProfile().aadhar_status,
                    "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                }
                this.setState({ aadharData: aadarData });
            }
        }
        localStorage.removeItem('referral_url')

        if (this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.isDFSTour == true) {
            this.FixtureDetail(matchParam);
        }
    }

    componentWillUnmount() {
        this.setState({
            isSecInng: false
        })
        window.removeEventListener('resize', () => { });
        ls.set('RFContestID', '')
    }

    /**
     * @description lifecycle method of react,
     * method to load locale storage data and props data
     */
    UNSAFE_componentWillMount() {
        ls.remove('entercontest_cards')
        ls.remove('team_creation_cards')

        Utilities.setScreenName('contestListing')

        this.checkOldUrlPattern();
        let url = window.location.href;
        if (url.includes('#')) {
            let tab = url.split('#')[1];
            url = url.split('#')[0];
            this.setState({
                activeContestTab: tab
            })
        }
        let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
        let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
        if (this.props.location.state && this.props.location.state.from == 'MyTeams') {
            this.setState({
                lineup_master_id: this.props.location.state.lineup_master_id,
                lineup_master_idArray: this.props.location.state.lineupObj || [],
                HeaderOption: {
                    back: true,
                    fixture: true,
                    filter: false,// true,
                    title: '',
                    hideShadow: false,
                    goBackLobby: true
                }
            })
        }
        setTimeout(() => {
            if ((tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true') || CinfirmPopUpIsAddFundsClicked == true) {
                setTimeout(() => {
                    this.callAfterAddFundPopup()
                }, 200);
            }
        }, 500);
    }

    /**
     * @description this method is used to replace old url pattern to new eg. from "/7/contest-listing" to "/cricket/contest-listing"
     */
    checkOldUrlPattern = () => {
        let sportsId = this.props.match.params.sportsId;
        if (!(sportsId in Sports)) {
            if (sportsId in Sports.url) {
                let sportsId = this.props.match.params.sportsId;
                let collection_master_id = this.props.match.params.collection_master_id;
                let myKey = this.props.match.params.myKey;
                if (this.props.isSecondInning) {
                    this.props.history.replace("/" + Sports.url[sportsId] + "/contest-listing/" + collection_master_id + "/" + myKey + "?sgmty=" + btoa(Constants.SELECTED_GAMET) + + '&sit=' + btoa(true));
                }
                else {
                    this.props.history.replace("/" + Sports.url[sportsId] + "/contest-listing/" + collection_master_id + "/" + myKey + "?sgmty=" + btoa(Constants.SELECTED_GAMET));
                }
                return;
            }
        }
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    async handleRefresh(resolve, reject) {
        WSManager.setH2hMessage(false);
        if (!globalThis.state.isListLoading) {
            globalThis.setState({
                showLoadMore: true
            })
            globalThis.FixtureContestList(globalThis.props.match.params);
        }
    }
    /**
     * 
     * @description method to display confirmation popup model, when user join contest.
     */
    ConfirmatioPopUpShow = (data) => {
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
    /**
     * 
     * @description method to display rules scoring modal, when user join contest.
     */
    openRulesModal = () => {
        this.setState({
            showRulesModal: true,
        });
    }
    /**
     * 
     * @description method to hide rules scoring modal
     */
    hideRulesModal = () => {
        this.setState({
            showRulesModal: false,
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
     * 
     * @description method to display share contest popup model.
     */
    shareContestModalShow = (data) => {
        this.setState({
            showSharContestModal: true,
        });
    }
    /**
     * 
     * @description method to hide share contest popup model.
     */
    shareContestModalHide = () => {
        this.setState({
            showSharContestModal: false,
        });
    }
    /**
     * 
     * @description method invoke when user click on share contest icon
     * @param shareContestEvent - share contest event
     * @param FixturedContestItem - Contest model on which user click
     */
    shareContest(shareContestEvent, FixturedContestItem) {
        if (WSManager.loggedIn()) {
            shareContestEvent.stopPropagation();
            this.setState({ showSharContestModal: true, FixtureData: FixturedContestItem })
        } else {
            this.goToSignup()
        }
    }

    /**
     * @description Method to open signup screen for guest user share contest click event
     */
    goToSignup = () => {
        this.props.history.push("/signup")
    }

    /**
     * @description Method to check user is guest on loggedin in case user join
     * @param {*} event - click event
     * @param {*} FixturedContestItem - contest model on which user click
     */
    check(event, FixturedContestItem, bn_state) {
        event.stopPropagation();

        if (bn_state == 1 || bn_state == 2) {
            if (Utilities.getMasterData().a_aadhar == "1" && FixturedContestItem.entry_fee != '0') {
                globalThis.updateAdharStatus(event, FixturedContestItem)
            }
            else {
                globalThis.joinGame(event, FixturedContestItem)
            }
        }
        else if (bn_state == 0) {
            if (Utilities.getMasterData().a_aadhar == "1" && FixturedContestItem.entry_fee != '0') {
                globalThis.updateAdharStatus(event, FixturedContestItem)
            }
            else {
                globalThis.joinGame(event, FixturedContestItem)
            }
        }
        else {
            globalThis.goToSignup()
        }
    }

    updateAdharStatus = (event, FixturedContestItem) => {
        if (WSManager.getProfile().aadhar_status != 1) {
            getUserAadharDetail().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({ aadharData: responseJson.data }, () => {
                        WSManager.updateProfile(this.state.aadharData)
                        this.aadharConfirmation()
                    });
                }
            })
        }
        else {
            let aadarData = {
                'aadhar_status': WSManager.getProfile().aadhar_status,
                "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
            }
            this.setState({ aadharData: aadarData }, () => {
                globalThis.joinGame(event, FixturedContestItem)
            });
        }
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
            if (this.state.TeamList.length > 0 || (teamListData && teamListData != null && teamListData.length > 0)) {
                this.setState({ showConfirmationPopUp: true, FixtureData: FixturedContestItem })
            }
            else {
                if (this.state.TotalTeam.length == parseInt(Utilities.getMasterData().a_teams)) {
                    this.openAlert()
                }
                else {
                    this.goToLineup(FixturedContestItem)
                }
            }
            WSManager.setFromConfirmPopupAddFunds(false);
        }
    }

    goToLineup = (FixturedContestItem) => {

        let mdata = this.state.LobyyData.match_list[0]
        delete mdata['is_tournament'];

        let urlData = { ...this.state.LobyyData, ...mdata };
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        if (urlData.home) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        }
        else {
            lineupPath = '/lineup/' + Utilities.replaceAll(urlData.collection_name, /[ \/,\s]/g, '_')
            
        }
        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: FixturedContestItem, LobyyData: urlData, resetIndex: 1, isCollectionEnable: (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && this.state.LobyyData.match_list && this.state.LobyyData.match_list.length > 1), current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, isFrom: 'MyTeams', aadharData: this.state.aadharData } })
    }

    /**
     * @description Method to show progress bar
     * @param {*} join - number of user joined
     * @param {*} total - total (max size) of team
     */
    ShowProgressBar = (join, total) => {
        return join * 100 / total;
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
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            if (checkBanState(this.state.FixtureData, CustomHeader)) {
                if (this.state.TeamList != null && !_isUndefined(this.state.TeamList) && this.state.TeamList.length > 0) {
                    this.ContestDetailHide();
                    setTimeout(() => {
                        this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData })
                    }, 200);
                } else {
                    let mdata = this.state.LobyyData.match_list[0]
                    delete mdata['is_tournament'];

                    let urlData = { ...this.state.LobyyData, ...mdata };
                    // let urlData = {...this.state.LobyyData, ...this.state.LobyyData.match_list[0]};
                    let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                    dateformaturl = new Date(dateformaturl);
                    let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                    let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                    dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                    let lineupPath = '';
                    if (urlData.home) {
                        lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, isH2h: this.state.showH2H, aadharData: this.state.aadharData } })
                    }
                    else {
                        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                        lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, isH2h: this.state.showH2H, aadharData: this.state.aadharData } })
                    }
                }
            } else {
                this.ContestDetailHide();
            }
        }
    }

    /**
     * @description method to contest listing data 
     * @param {*} data - fixture data 
     */
    FixtureContestList(data) {
        const { isSecInng } = this.state
        let param = {
            "sports_id": Sports[data.sportsId ? data.sportsId : AppSelectedSport],
            "collection_master_id": data.collection_master_id,
            "is_trnt": 1
        }
        if (param.sports_id) {
            ls.set('selectedSports', param.sports_id.toString() || param.sports_id);
            setValue.setAppSelectedSport(param.sports_id);
        }
        setTimeout(() => {
            ls.set('selectedSports', param.sports_id.toString() || param.sports_id);
            setValue.setAppSelectedSport(param.sports_id);
        }, 100);
        this.setState({ isListLoading: true })
        if (this.state.isSecondInning || isSecInng) {
            param['is_2nd_inning'] = 1
        }
        getFixtureContestList(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {

                let apiData = responseJson.data
                let GroupList = apiData.group
                this.setState({
                    TournamentList: apiData.tournament
                })
                let ContestList = apiData.contest.filter(obj => obj.is_pin_contest != 1 && (!Object.keys(apiData.user_data.contest).includes(obj.contest_id) || obj.multiple_lineup != apiData.user_data.contest[obj.contest_id]))
                let PinContestList = apiData.contest.filter(obj => obj.is_pin_contest == 1 && (!Object.keys(apiData.user_data.contest).includes(obj.contest_id) || obj.multiple_lineup != apiData.user_data.contest[obj.contest_id]))
                // let ContestList = apiData.contest.filter(obj => obj.is_pin_contest != 1)
                // let PinContestList = apiData.contest.filter(obj => obj.is_pin_contest == 1)
                let finalList = []

                _Map(GroupList, (group, indx) => {
                    let tmp = ContestList.filter(item => item.group_id == group.group_id)
                    if (tmp.length > 0) {
                        let CList = []
                        _Map(tmp, (titem, idx) => {
                            // if(Object.keys(apiData.user_data.contest).includes(titem.contest_id) && titem.multiple_lineup == apiData.user_data.contest[titem.contest_id]){}
                            // else{
                            titem['prize_pool_amount'] = this.getWinPrizeAmount(titem)
                            CList.push(titem)
                            // }
                        })
                        group['contest_list'] = CList
                        if (CList.length > 0) {
                            finalList.push(group)
                        }
                    }
                })


                // to add new key of prize distribution calculation so that will apply filter using that key 
                // ** start **
                // let tmpContArray=[]
                // _Map(responseJson.data.contest, (item, index) => {
                //     _Map(item.contest_list, (contest, idx) => {
                //         contest['prize_pool_amount']=this.getWinPrizeAmount(contest)
                //     })
                //     tmpContArray.push(item)
                // })
                // ** end **

                // let tmpArray = []
                // _Map(responseJson.data.contest, (item, index) => {
                //     tmpArray = [...tmpArray, ...item.contest_list];
                // })
                // let Resdata = responseJson.data
                let allContestData = {
                    'total_contest': apiData.total,
                    'contest': finalList
                }
                this.setState({
                    sortContestList: ContestList,
                    preSortContestList: ContestList,
                    FixturedContest: finalList,
                    SortContestTotal: apiData.total,
                    FixturedContestTotal: apiData.total,
                    FixturedPinContest: PinContestList,
                    allContestData: allContestData, //Resdata,
                    myContestCount: apiData && apiData.user_data && apiData.user_data.contest ? Object.keys(apiData.user_data.contest).length : 0,
                    myTeamCount: apiData && apiData.user_data.team ? (this.state.isSecondInning || isSecInng ? apiData.user_data['2nd_team'] : apiData.user_data.team) : 0,
                    user_rookie_dtl: ls.get('userBalance') && ls.get('userBalance').rookie ? ls.get('userBalance').rookie : ''
                }, () => {
                    let rookie_setting = Utilities.getMasterData().rookie_setting || '';
                    let isMegaContestExsist = this.state.FixturedContest.filter(obj => obj.group_id == 1)
                    let isRookieContestExsist = this.state.FixturedContest.filter(obj => obj.group_id == rookie_setting.group_id)
                    if (isRookieContestExsist) {
                        let isUSerRookie = this.showRookieContest(this.state.user_rookie_dtl, rookie_setting) ? true : false
                        this.setState({
                            isCWRookie: isUSerRookie
                        })
                    }
                    if (this.state.myTeamCount && this.state.myTeamCount > 0) {
                        if (WSManager.loggedIn()) {
                            const { activeContestTab } = this.state
                            if (activeContestTab == 1) {
                                this.getMyContest(data.collection_master_id)
                            } else {
                                this.getUserLineUpListApi(data);
                            }
                        }
                    }

                    let showDFSMulti = this.state.isDFSMulti && this.state.LobyyData && this.state.LobyyData.season_game_count > 1 ? true : false;
                    this.addGuruTab(showDFSMulti ? false : true);
                    if (this.state.activeContestTab == 1 && this.state.myContestCount > 0 && this.state.myContestCount != this.state.myContestListData.length) {
                        this.getMyContest(data.collection_master_id)
                    }
                    // this.setFilterRange(this.state.sortContestList)
                    this.setState({
                        isListLoading: false,
                        isMegaExsist: isMegaContestExsist.length != 0 ? true : false
                    })
                    if (this.state.isFilterApplied) {
                        this.filterContestList({
                            entry_fee_from: this.state.entry_fee_from,
                            entry_fee_to: this.state.entry_fee_to,
                            participants_from: this.state.participants_from,
                            participants_to: this.state.participants_to,
                            prizepool_from: this.state.prizepool_from,
                            prizepool_to: this.state.prizepool_to,
                            isApplied: true
                        })
                    }
                    else if (this.state.sortAppliedCount != 0) {
                        this.sortList(this.state.sort_field, this.state.sort_order, 0)
                    }
                });
            }
        })
    }

    /**
     * @description method to get fixture detail
     */
    FixtureDetail = async (CollectionData) => {
        const { isSecInng } = this.state
        if (!this.state.LobyyData.home || !this.state.FixturedDetail) {
            let param = {
                "sports_id": Sports[CollectionData.sportsId],
                "collection_master_id": CollectionData.collection_master_id,
            }
            let apiStatus = getFixtureDetail;
            if ((this.state.isSecondInning || isSecInng) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
                param['is_2nd_inning'] = 1
            }
            var apiResponseData = await apiStatus(param);
            if (apiResponseData) {

                const { tournament } = apiResponseData;
                let second_innings_enable = isDateTimePast(apiResponseData.season_scheduled_date) && !isDateTimePast(apiResponseData['2nd_inning_date'])

                let match_list = apiResponseData.match.map((item) => {
                    item.game_starts_in = convertToTimestamp(apiResponseData.season_scheduled_date)
                    item.is_tournament = _isEmpty(tournament) ? 0 : ((tournament.length == 1 && !second_innings_enable) ? 1 : 0);
                    let __tournament = _filter(tournament, o => o.season_id == item.season_id)
                    item.tournament_count = _isEmpty(__tournament) ? 0 : __tournament[0].tournament_count
                    item.tournament_name = _isEmpty(__tournament) ? "" : __tournament[0].tournament_name
                    return item
                })

                const { match, ...api_response_data } = { ...apiResponseData, match_list, ...match_list[0], game_starts_in: convertToTimestamp(apiResponseData.season_scheduled_date) }


                if (this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.isDFSTour == true) {
                    this.setState({
                        LobyyData: api_response_data,
                        isSecondInning: second_innings_enable
                    }, () => {
                        this.setState({
                            isSecInng: second_innings_enable
                        })
                    })
                }
                else if (_isUndefined(this.props.location.state)) {
                    this.setState({
                        LobyyData: api_response_data
                    })
                }

                this.setState({
                    FixturedDetail: api_response_data, 
                }, () => {
                    if (api_response_data && api_response_data.is_h2h == 1) {
                        this.apiCallH2HContest(this.props.match.params)
                    }
                })
                if (Constants.SELECTED_GAMET == Constants.GameType.MultiGame) {
                    this.setState({
                        HeaderOption: {
                            back: true,
                            fixture: true,
                            filter: false,//true,
                            isPrimary: DARK_THEME_ENABLE ? false : true,
                            hideShadow: this.state.FixturedDetail && this.state.FixturedDetail.match_list && this.state.FixturedDetail.match_list.length > 1 ? true : false,
                            goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                            // goBackLobby: true,//!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                        }
                    })
                }
            }
        }
    }

    getUserLineUpListApi = async (CollectionData) => {
        const { isSecInng } = this.state
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": CollectionData.collection_master_id,
        }
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        if ((this.state.isSecondInning || isSecInng) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
            param['is_2nd_inning'] = 1
        }
        var api_response_data = Constants.SELECTED_GAMET == Constants.GameType.DFS ? await getUserTeams(param, user_unique_id) : await getUserTeams(param, user_unique_id);
        if (api_response_data) {

            let tList = this.state.isSecondInning ? _filter(api_response_data, (obj, idx) => {
                return obj.is_2nd_inning == "1";
            }) : _filter(api_response_data, (obj, idx) => {
                return (obj.is_2nd_inning != "1")
            })
            this.setState({
                TotalTeam: api_response_data,
                TeamList: tList,
                userTeamListSend: tList,
            })
            let showDFSMulti = this.state.isDFSMulti && this.state.LobyyData && this.state.LobyyData.season_game_count > 1 ? true : false;
            this.addGuruTab(showDFSMulti ? false : true);
            if (this.state.userTeamListSend) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {

                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })

                this.setState({ userTeamListSend: tempList });
            }
        }
    }

    addGuruTab = (isAdd) => {
        const { LobyyData } = this.state
        if(LobyyData.is_dm  == 1) return;

        let genrateTab = {};
        genrateTab["label"] = AppLabels.GENRATE;
        genrateTab["value"] = '3';
        (Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.myTeamCount < parseInt(Utilities.getMasterData().a_teams) &&
            Utilities.getMasterData().a_guru == '1' &&
            !this.state.isSecondInning && Constants.AppSelectedSport == SportsIDs.cricket)
            &&
            isAdd ?
            !this.checkIsGuruTab('3') &&
            this.state.ContestTabList.push(genrateTab)
            :
            this.removeGuruTab('3')

    }
    isGuru = () => {
        let isShow = false;
        let showDFSMulti = this.state.isDFSMulti && this.state.LobyyData && this.state.LobyyData.season_game_count > 1 ? true : false;
        (Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.myTeamCount < parseInt(Utilities.getMasterData().a_teams) &&
            Utilities.getMasterData().a_guru == '1' &&
            !this.state.isSecondInning && Constants.AppSelectedSport == SportsIDs.cricket &&
            !showDFSMulti)
            ?
            isShow = true
            :
            isShow = false

        return isShow &&  this.state.LobyyData.is_dm != 1;



    }

    removeGuruTab = (tabs) => {
        let ContestTab = this.state.ContestTabList;
        if (this.checkIsGuruTab(tabs)) {
            var index = 0;
            for (var ContestTabData of this.state.ContestTabList) {
                if (ContestTabData.value == tabs) {
                    ContestTab.splice(index, 1);
                }
                index++
            }
        }
        this.setState({ ContestTabList: ContestTab })
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
        if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
            this.JoinGameApiCall(dataFromConfirmPopUp)
        } else if ((dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "") || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            this.JoinGameApiCall(dataFromConfirmPopUp)
        }
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
                    this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList' } });

                }
                else {
                    this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
                }
            }

            else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("ContestListing")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd }, isSecIn: this.state.isSecondInning });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        const { isSecInng } = this.state
        let IsNetworkContest = this.state.FixtureData.is_network_contest == 1;
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true : false;
        let ApiAction = IsNetworkContest ? joinContestNetworkfantasy : isH2h ? joinContestH2H : joinContest;
        let param = {
            "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }
        if (this.state.isSecondInning || isSecInng) {
            param['is_2nd_inning'] = 1
        }
        if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
            ApiAction = IsNetworkContest ? joinContestWithMultiTeamNF : joinContestWithMultiTeam;
            let resultLineup = dataFromConfirmPopUp.lineUpMasterIdArray.map(a => a.lineup_master_id);
            param['lineup_master_id'] = resultLineup
        } else {
            param['lineup_master_id'] = dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
        }


        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (isH2h) {
                    this.h2hCallangeData()
                    Utilities.setH2hData(dataFromConfirmPopUp, responseJson.data.contest_id)
                }
                if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
                    let singular_data = {};
                    singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                    singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                    singular_data.contest_date = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                    singular_data.fixture_name = dataFromConfirmPopUp.lobbyDataItem.collection_name;
                    singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

                    if (window.ReactNativeWebView) {
                        let event_data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Contest_joined',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(event_data));
                    }
                    else {
                        window.SingularEvent("Contest_joined", singular_data);
                    }
                }

                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
                    contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                })

                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }

                this.ConfirmatioPopUpHide();
                this.setState({
                    isNewCJoined: true,
                    lineup_master_idArray: [],
                    //lineup_master_id: ''
                })
                setTimeout(() => {

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            }
            else if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.showToast(AppLabels.USER_FROM_BANNED_STATE_ARE_NOT_ALLOWED, 3000)
            }
            else {
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
    opponetModalShow = () => {
        this.setState({
            showOppData: true,
        });
    }

    opponetModalHide = () => {
        this.setState({
            showOppData: false,
        });
    }
    H2hModalShow = (e) => {
        e.stopPropagation()
        this.setState({
            showH2hModal: true,
        });
    }

    H2hModalHide = () => {
        this.setState({
            showH2hModal: false,
        });
    }

    showUJC = (data) => {
        this.setState({
            showUJC: true,
        });
    }

    hideUJC = () => {
        this.setState({
            showUJC: false,
        });
    }

    goBack = () => {
        this.props.history.goBack();
    }

    goToLobby = () => {
        this.setState({
            showThankYouModal: false,
            // lineup_master_id:''
        });
        const matchParam = globalThis.props.match.params
        globalThis.FixtureContestList(matchParam);
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            let mdata = this.state.LobyyData.match_list[0]
            delete mdata['is_tournament'];

            let urlData = { ...this.state.LobyyData, ...mdata };
            // let urlData = {...this.state.LobyyData, ...this.state.LobyyData.match_list[0]};
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

            if (urlData.home) {
                this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
            }
            else {
                let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
            }
        }
    }

    /* Handle contest listing filters */
    hideFilter = () => {
        this.setState({ showContestListFitler: false })
    }

    showFilter = () => {
        this.setState({ showContestListFitler: true })
    }

    filterConditions(filterObj, obj) {
        let eFee = parseInt(obj.entry_fee);
        let partic = parseInt(obj.total_user_joined);
        let winPP = parseFloat(obj.prize_pool_amount);

        let EFBelow = filterObj.entry_fee_from || 0
        let EFAbove = filterObj.entry_fee_to || 'Above'

        let PartBelow = filterObj.participants_from || 0
        let PartAbove = filterObj.participants_to || 'Above'

        let PPBelow = filterObj.prizepool_from || 0
        let PPAbove = filterObj.prizepool_to || 'Above'

        let isEFeeEligible = EFAbove == 'Above' ? eFee >= parseInt(EFBelow)
            : (
                (eFee >= parseInt(EFBelow) && eFee <= parseInt(EFAbove))
            )
        let isPartEligible = PartAbove == 'Above' ? partic >= parseInt(PartBelow || 0)
            : (
                (partic >= parseInt(PartBelow || 0) && partic <= parseInt(PartAbove))
            )
        let isWinPPEligible = PPAbove == 'Above' ? winPP >= parseInt(PPBelow || 0)
            : (
                (winPP >= parseFloat(PPBelow || 0) && winPP <= parseFloat(PPAbove))
            )

        return (isEFeeEligible && isPartEligible && isWinPPEligible)


        // let prize_data = obj.prize_distibution_detail ? obj.prize_distibution_detail : obj.prize_distribution_detail;
        // let prizeAmount = this.getWinCalculation(prize_data);
        // let prize = prizeAmount.real > 0 ? prizeAmount.real : prizeAmount.bonus > 0 ? prizeAmount.bonus : prizeAmount.point;

        // let isWinBetween = prizeAmount.is_tie_breaker == 1 ? true : (prize >= filterObj.prizepool_from && prize <= filterObj.prizepool_to)

        // return (
        //     (eFee >= filterObj.entry_fee_from && eFee <= filterObj.entry_fee_to)
        //     &&
        //     (partic >= filterObj.participants_from && partic <= filterObj.participants_to)
        //     &&
        //     isWinBetween
        // )
    }

    filterContestList = (filterObj) => {
        const { allContestData } = this.state
        if (filterObj.isReset) {
            let tmpSortArray = []
            _Map(allContestData.contest, (item) => {
                tmpSortArray = [...tmpSortArray, ...item.contest_list];
            })
            this.setState({
                showContestListFitler: false,
                sortContestList: tmpSortArray,
                preSortContestList: tmpSortArray,
                FixturedContest: allContestData.contest,
                SortContestTotal: allContestData.total_contest,
                FixturedContestTotal: allContestData.total_contest,
                // FixturedPinContest: allContestData.pin_contest,
                isFilterApplied: false,
                filterApply: false,
                filterAppliedCount: 0
            }, () => {
                if (this.state.sort_field != '') {
                    this.sortList(this.state.sort_field, this.state.sort_order, 0)
                }
            });
        } else {
            let cloneAllData = _cloneDeep(allContestData);
            let tmpAllContest = [];
            _Map(cloneAllData.contest, (item) => {
                let filterArray = _filter(item.contest_list, (obj) => {
                    return this.filterConditions(filterObj, obj)
                })
                if (filterArray.length > 0) {
                    item['contest_list'] = filterArray;
                    item['total'] = filterArray.length;
                    tmpAllContest.push(item)
                }
            })
            cloneAllData['contest'] = tmpAllContest;

            let tmpArray = []
            _Map(cloneAllData.contest, (item, index) => {
                tmpArray = [...tmpArray, ...item.contest_list];
            })


            let filterPINArray = _filter(cloneAllData.pin_contest, (obj) => {
                return this.filterConditions(filterObj, obj)
            })

            let filterApplyCount = this.updateFilterCount(filterObj)
            this.setState({
                showContestListFitler: false,
                sortContestList: tmpArray,
                preSortContestList: tmpArray,
                FixturedContest: cloneAllData.contest,
                SortContestTotal: tmpArray.length + filterPINArray.length,
                FixturedContestTotal: tmpArray.length + filterPINArray.length,
                // FixturedPinContest: filterPINArray,
                isFilterApplied: true,
                filterApply: true,
                filterAppliedCount: filterApplyCount
            }, () => {
                if (this.state.sort_field != '') {
                    this.sortList(this.state.sort_field, this.state.sort_order, filterApplyCount)
                }
            });
        }
        this.setState({
            // entry_fee_from: filterObj.entry_fee_from,
            // entry_fee_to: filterObj.isReset ? priviousObj.entryFee.max : filterObj.entry_fee_to,
            // participants_from: filterObj.participants_from,
            // participants_to: filterObj.isReset ? priviousObj.entries.max : filterObj.participants_to,
            // prizepool_from: filterObj.prizepool_from,
            // prizepool_to: filterObj.isReset ? priviousObj.winnings.max : filterObj.prizepool_to
            entry_fee_from: filterObj.entry_fee_from,
            entry_fee_to: filterObj.entry_fee_to,
            participants_from: filterObj.participants_from,
            participants_to: filterObj.participants_to,
            prizepool_from: filterObj.prizepool_from,
            prizepool_to: filterObj.prizepool_to
        });
    }

    updateFilterCount = (filterObj) => {
        let count = 0
        if ((filterObj.entry_fee_from != '' || filterObj.entry_fee_from == 0) && filterObj.entry_fee_to != '') {
            count = count + 1
        }
        if ((filterObj.participants_from != '' || filterObj.participants_from == 0) && filterObj.participants_to != '') {
            count = count + 1
        }
        if ((filterObj.prizepool_from != '' || filterObj.prizepool_from == 0) && filterObj.prizepool_to != '') {
            count = count + 1
        }
        return count
    }

    getContestWinnerCount(prizeDistributionDetail) {
        if (prizeDistributionDetail.length > 0) {
            if ((prizeDistributionDetail[prizeDistributionDetail.length - 1].max) > 1) {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNER;
            }
        }
    }

    sortViewByGroup() {
        if (this.state.showGroupView) {
            this.setState({
                showGroupView: false
            }, () => {
                ls.set('show_group_view', false);
            })
        } else {
            this.setState({
                showGroupView: true,
            }, () => {
                ls.set('show_group_view', true);
            })
        }

    }

    redirectToMyTeams() {

        let mdata = this.state.LobyyData.match_list[0]
        delete mdata['is_tournament'];

        let urlData = { ...this.state.LobyyData, ...mdata };
        WSManager.clearLineup()
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
        if (urlData.home) {
            this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: urlData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
        }
        else {
            let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: urlData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
        }
    }

    redirectToGuru() {
        ls.remove('guru_lineup_data')

        if (this.state.userTeamListSend.length > 0) {
            //alert('000')
            let urlParams = '';
            if (this.state.LobyyData && this.state.LobyyData.home) {
                urlParams = Utilities.setUrlParams(this.state.LobyyData);
            }
            else {
                urlParams = Utilities.replaceAll(this.state.LobyyData.collection_name, ' ', '_').toLowerCase();
            }

            let sportsId = Utilities.getSelectedSportsForUrl();
            let collection_master_id = this.state.LobyyData.collection_master_id;
            let keyName = 'my-teams' + sportsId + collection_master_id;
            if (this.state.isNewCJoined) {
                preTeamsList[keyName] = [];
            } else {
                preTeamsList[keyName] = this.state.TeamList;
            }
            //this.props.history.push({ pathname: "/pl/lineup" , state: { LobyyData: this.state.LobyyData,TotalTeam: this.state.TotalTeam } });
            this.props.history.push({ pathname: "/pl/lineup", state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1 } })

        } else {
            //alert('1111')
            let urlData = this.state.LobyyData;
            WSManager.clearLineup()
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
            if (urlData.home) {
                this.props.history.push({ pathname: "/pl/lineup", state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1 } })
            }
            else {
                let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                this.props.history.push({ pathname: "/pl/lineup", state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1 } })
            }
        }
    }

    showDetail(Group, index) {
        Group.isReadMore = true;
        var arrFixture = this.state.FixturedContest;
        arrFixture[index] = Group;
        this.setState({ FixturedContest: arrFixture });
    }

    aadharConfirmation = () => {
        if (this.state.aadharData.aadhar_status == "0" && this.state.aadharData.aadhar_id != "0") {
            Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
    }

    hideDetail(Group, index) {
        Group.isReadMore = undefined;
        var arrFixture = this.state.FixturedContest;
        arrFixture[index] = Group;
        this.setState({ FixturedContest: arrFixture });
    }

    // bannedStateToast = (event) => {
    //     event.preventDefault();
    //     Utilities.showToast(AppLabels.USER_FROM_BANNED_STATE_ARE_NOT_ALLOWED, 3000)
    // }

    geoValidate = (event, contest, ContestDisabled) => {
        // event.preventDefault();
        event.stopPropagation();

        let { bn_state, geoPlayFree } = this.state;
        if (WSManager.loggedIn()) {
            if (bn_state == 1 || bn_state == 2) {
                if (contest.entry_fee == '0') {
                    if (ContestDisabled) {
                        return null;
                    }
                    else {
                        globalThis.check(event, contest, bn_state)
                    }
                }
                else {
                    Utilities.bannedStateToast(bn_state)
                }
            }
            if (bn_state == 0) {
                if (ContestDisabled) {
                    return null;
                }
                else {
                    globalThis.check(event, contest, bn_state)
                }
            }

        }
        else {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        }

        // contest.entry_fee == '0' ?
        // ((event) =>
        //     (ContestDisabled)
        //         ?
        //         null
        //         :
        //         globalThis.check(event, contest)
        // )
        // :
        // (e) => .bannedStateToast(e)}
    }

    geoContestDetailShow = (e, ContestDisabled, contest) => {
        // e.stopPropagation();
        // e.preventDefault();
        var globalThis = this;
        // let { bn_state, geoPlayFree } = this.state;
        // if (bn_state == 0) {
        //     if (geoPlayFree == 'true' && contest.entry_fee == '0') {
        //         if (ContestDisabled) {
        //             return null;
        //         }
        //         else {
        //             globalThis.ContestDetailShow(contest, 2, e)
        //         }
        //     }
        //     else {
        //         Utilities.bannedStateToast(e)
        //     }
        // }
        // else {
        if (ContestDisabled) {
            return null;
        }
        else {
            globalThis.ContestDetailShow(contest, 2, e)
        }
        // }

    }
    secondInningTooltip = () => {
        this.setState({ tooltiOverLay: !this.state.tooltiOverLay })
    }


    showMoreTour = (TourFilter, val) => {
        if (val == '2') {
            this.setState({
                showTourLeadModal: true,
                TourFilterList: TourFilter
            })
        }
        else {
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-detail/' + TourFilter[0].tournament_id,
                state: {
                    tourId: TourFilter[0].tournament_id,
                }
            })
        }
    }

    closeMoreTour = () => {
        this.setState({
            showTourLeadModal: false
        })
    }

    renderContestView(data) {
        let { contest, isPinned, isGroup, isRookie } = data;
        let sponserImage = data.contest.sponsor_logo && data.contest.sponsor_logo != null ? data.contest.sponsor_logo : 0
        let remainingJoinCount = (contest.size || 0) - (contest.total_user_joined || 0);
        let lineupAryLength = this.state.lineup_master_idArray.length;
        let user_join_count = parseInt(contest.user_joined_count || 0);
        let ContestDisabled = lineupAryLength > 1 ? (lineupAryLength > remainingJoinCount || ((lineupAryLength + user_join_count) > contest.multiple_lineup) || contest.multiple_lineup <= 1) : false;
        let user_data = ls.get('profile');
        let { geoPlayFree, bn_state, TournamentList, showTourLeadModal } = this.state

        let TourFilter = TournamentList.filter((obj) => obj.contest_id == contest.contest_id)

        return (
            <div className={"contest-list contest-listing-list " + (isGroup ? ' contest-card-body' : ' position-relative') + (isPinned ? ' pinned' : '')}>
                <div className={"contest-list-header" + (ContestDisabled ? ' disabled-contest-card' : '')}
                    onClick={(e) => this.geoContestDetailShow(e, ContestDisabled, contest)}
                >
                    <div className="contest-heading">
                        {
                            isPinned && <div className="contest-pin">
                                <i className="icon-pinned-ic"></i>
                            </div>
                        }
                        <div className="featured-icon-wrap" onClick={(e) => e.stopPropagation()} >
                            {
                                contest.multiple_lineup > 1 &&
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AppLabels.MAX_TEAM_FOR_MULTI_ENTRY} {contest.multiple_lineup} {AppLabels.MAX_MULTI_ENTRY_TEAM}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon multi-feat">
                                        {AppLabels.MULTI}
                                    </span>
                                </OverlayTrigger>

                            }
                            {
                                contest.guaranteed_prize == 2 && parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) &&
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AppLabels.GUARANTEED_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon gau-feat">
                                        {AppLabels.GUARANTEED}
                                    </span>
                                </OverlayTrigger>

                            }
                            {
                                contest.is_confirmed == 1 && parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) &&
                                <OverlayTrigger trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AppLabels.CONFIRM_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon conf-feat">
                                        {AppLabels.CONFIRMED}
                                    </span>
                                </OverlayTrigger>

                            }
                        </div>


                        <h3 className="win-type">
                            {
                                contest.contest_title ?
                                    <span className="position-relative">{contest.contest_title}
                                        {this.state.isSecondInning &&
                                            <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                <Tooltip id="tooltip" >
                                                    <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                </Tooltip>
                                            }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                    </span>
                                    :
                                    <React.Fragment>
                                        <span className="position-relative" onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 1, event)}>
                                            <span className="prize-pool-text text-capitalize" >{AppLabels.WIN} </span>

                                            <span>
                                                {this.getPrizeAmount(contest, 0)}
                                            </span>
                                            {this.state.isSecondInning &&
                                                <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                    <Tooltip id="tooltip" >
                                                        <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                    </Tooltip>
                                                }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                        </span>
                                    </React.Fragment>
                            }
                            {

                                !contest.is_network_contest &&
                                <i onClick={(shareContestEvent) => (ContestDisabled) ? null : globalThis.shareContest(shareContestEvent, contest)} className="icon-share"></i>

                            }

                        </h3>
                        {

                            <div className="text-small-italic mt3x">
                                {Constants.OnlyCoinsFlow != 1 && (contest.max_bonus_allowed != '0') && <span onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 1, event)}>
                                    {AppLabels.Use} {contest.max_bonus_allowed}{'% '}{AppLabels.BONUS_CASH_CONTEST_LISTING} {(parseInt(contest.user_joined_count) > 0) ? '|' : ''}
                                </span>}
                                {
                                    this.state.activeContestTab == 1 &&
                                    <>
                                        {(parseInt(contest.user_joined_count) > 0) && <span>{' '}{AppLabels.JOINED_WITH}{' '}<span className='team-name-style'>{contest.team_name} {(parseInt(contest.user_joined_count) > 1) ? (' + ' + ((parseInt(contest.user_joined_count) - 1))) + ' more' : ''}</span></span>}
                                    </>
                                }
                            </div>
                        }

                    </div>
                    <div className={"display-table" + (this.state.activeContestTab == 0 && Constants.OnlyCoinsFlow != 1 && contest.max_bonus_allowed == '0' ? ' top-btm-10px' : '')}>
                        <div className="progress-bar-default display-table-cell v-mid"
                            onClick={
                                // (geoPlayFree == 'false' && contest.entry_fee == '0') ?
                                ((event) =>
                                    (ContestDisabled)
                                        ?
                                        null
                                        :
                                        globalThis.ContestDetailShow(contest, 3, event)
                                )
                                // :
                                // (e) => .bannedStateToast(e)
                            }>
                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                            <div className="progress-bar-value" >
                                {/* <span className="user-joined">{parseFloat(contest.size) -  parseFloat(contest.total_user_joined)}
                                    {contest.is_tie_breaker == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && ' ' + AppLabels.SPOTS_LEFT}
                                </span> */}
                                {
                                    (Constants.SELECTED_GAMET == Constants.GameType.DFS) &&
                                    <><span className="total-entries">{Utilities.numberWithCommas(parseFloat(contest.size) - parseFloat(contest.total_user_joined))}  {AppLabels.SPOTS_LEFT}</span>
                                        <span className="min-entries">{Utilities.numberWithCommas(contest.size)}{" " + AppLabels.SPOTS} </span></>
                                }
                            </div>
                        </div>
                        <div className="display-table-cell v-mid position-relative entry-criteria">
                            <Button className={"white-base btnStyle btn-rounded" + ((bn_state == 1 || bn_state == 2) ?
                                (contest.entry_fee != '0') ? ' geo-disabled' : ' ' : '') + (isRookie ? ' btn-rookie' : '') + (contest.currency_type == 2 ? ' coin-cont' : '')}
                                bsStyle="primary"
                                onClick={(e) => this.geoValidate(e, contest, ContestDisabled)}>
                                {/* // (Utilities.getMasterData().a_aadhar == 1 && WSManager.loggedIn()) ?
                                //     (this.state.aadharData && this.state.aadharData.aadhar_status == "1" ?
                                //         ((event) => (ContestDisabled) ? null : globalThis.check(event, contest))
                                //         :
                                //         () => this.aadharConfirmation())
                                //     : */}

                                {/* <Button className={"white-base btnStyle btn-rounded" + (isRookie ? ' btn-rookie' : '') + (contest.currency_type == 2 ? ' coin-cont' : '')} bsStyle="primary" onClick={
                                (Utilities.getMasterData().a_aadhar == 1 && WSManager.loggedIn()) ?
                                    (this.state.aadharData && this.state.aadharData.aadhar_status == "1" ?
                                        ((event) => (ContestDisabled) ? null : globalThis.check(event, contest))
                                        :
                                        () => this.aadharConfirmation())
                                    :
                                    ((event) => (ContestDisabled) ? null : globalThis.check(event, contest))

                            }> */}
                                {
                                    contest.entry_fee > 0 ? ((contest.prize_type == 1 || contest.prize_type == 0 || contest.prize_type == 2) ?
                                        <React.Fragment>
                                            {
                                                contest.currency_type == 2 ?
                                                    <img className="img-coin" alt='' src={Images.IC_COIN} />
                                                    :
                                                    <span>
                                                        {Utilities.getMasterData().currency_code}
                                                    </span>
                                            }
                                            {Utilities.numberWithCommas(contest.entry_fee)}
                                        </React.Fragment>
                                        :
                                        <React.Fragment>
                                            <span >
                                                <i className="icon-bean"></i>
                                            </span>
                                            {contest.entry_fee}
                                        </React.Fragment>
                                    ) : AppLabels.FREE
                                }
                                {isRookie && <img src={Images.ROOKIE_LOGO} alt='' className='rookie-img' />}
                            </Button>
                        </div>

                    </div>
                    {
                        data.contest.sponsor_logo && data.contest.sponsor_logo != null &&
                        <div className="contest-card-footer height-sponsor-strip">
                            <div className="sponsor-logo-section">
                                {Constants.SELECTED_GAMET == Constants.GameType.DFS &&
                                    window.ReactNativeWebView && !this.checkSponserUrlDomain(data.contest.sponsor_link, process.env.REACT_APP_BASE_URL) ?
                                    <a
                                        href
                                        onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(data.contest.sponsor_link), event)}
                                        className="attached-url">
                                        <img alt='' className="lobby_sponser-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? '' : Utilities.getSponserURL(sponserImage)} />
                                    </a>
                                    :
                                    <a
                                        href={Utilities.getValidSponserURL(data.contest.sponsor_link)}
                                        onClick={(event) => event.stopPropagation()}
                                        target='__blank'
                                        className="attached-url">
                                        <img alt='' className="lobby_sponser-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? '' : Utilities.getSponserURL(sponserImage)} />
                                    </a>
                                }
                            </div>

                        </div>
                    }

                </div>
                {
                    TourFilter.length > 0 &&
                    <div className="ani1 new-tourInclude-view tour-new-leader">
                        <img src={Images.LEAD_TROPHY} className="lead-trophy" alt="" />
                        {TourFilter && TourFilter.length == 1 ?
                            <span className='leadT-name' onClick={() => this.showMoreTour(TourFilter, '1')}>{AppLabels.TOURNAMENT + '-' + TourFilter[0].name}</span> :
                            <span className='leadT-name'>{AppLabels.TOURNAMENT + '-' + TourFilter[0].name} + <span className='countL-others' onClick={() => this.showMoreTour(TourFilter, '2')}>
                                {+  (parseInt(TourFilter.length) - 1) + ' ' + AppLabels.OTHER_TEXT}</span></span>
                        }
                        {TourFilter.length > 0 && <div className='arrow-container float-right mt-1'>
                            <i className="icon-arrow-right iocn-first"></i>
                            <i className="icon-arrow-right iocn-second"></i>
                            <i className="icon-arrow-right iocn-third"></i>
                        </div>}

                    </div>


                }
                {
                    contest.group_id === '7' &&
                    <div className='private-contest-box'>
                        <div className='left-content'>
                            <span className='private-logo'>P</span>
                            <span className="box-text">{AppLabels.PRIVATE_CONTEST}</span>
                        </div>
                        <div className='creator-info'>
                            <span className="box-text">{AppLabels.YOU}</span>
                            <span className="img-wrp">
                                <img src={user_data.image ? Utilities.getThumbURL(user_data.image) : Images.DEFAULT_AVATAR} alt="" />
                            </span>
                        </div>
                    </div>
                }
                {
                    <div>

                    </div>
                }
            </div >


        );

    }

    getWinCalculation = (prize_data) => {
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0, 'is_tie_breaker': 0 };
        prize_data && prize_data.map(function (lObj, lKey) {
            var amount = 0;
            if (lObj.max_value) {
                amount = parseFloat(lObj.max_value);
            } else {
                amount = parseFloat(lObj.amount);
            }
            if (lObj.prize_type == 3) {
                prizeAmount['is_tie_breaker'] = 1;
            }
            if (lObj.prize_type == 0) {
                prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
            } else if (lObj.prize_type == 2) {
                prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
            } else {
                prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
            }
        })
        return prizeAmount;
    }

    handleJson = (data) => {
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
    }

    getPrizeAmount = (prize_data, status) => {
        let prizeDetail = this.handleJson(prize_data.prize_distibution_detail)
        let prizeAmount = this.getWinCalculation(prizeDetail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className={status == 1 ? "white-dv ml-1" : "contest-prizes"}>
                            {' '}  {Utilities.getMasterData().currency_code} {' '}
                            {' '}{Utilities.getPrizeInWordFormat(prizeAmount.real)}{' '}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className={status == 1 ? "white-dv" : "contest-listing-prizes"} ><i style={{ margin: status == 1 ? 4 : '' }} className="icon-bonus" />  {Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ height: 15, width: 15, margin: status == 1 ? 5 : '' }} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }

    handleTab = (tab, otherData) => {
        // return
        if (WSManager.loggedIn()) {
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('#')[0];
            }
            if (this.state.activeContestTab != tab) {

                // window.history.replaceState("", "", url + "#" + tab);
                // this.props.history.replace({
                //     pathname: '/current-route',
                //     search: '?param1=value1&param2=value2',
                //   })

                this.setState({
                    isLoading: false,
                    activeContestTab: tab
                }, () => {
                    if (this.state.activeContestTab == 1 && this.state.myContestListData.length != this.state.myContestCount) {
                        this.getMyContest()
                    }
                    if ((this.state.activeContestTab == 0 || this.state.activeContestTab == 1) && ls.get('showMyTeam')) {
                        ls.remove('showMyTeam')
                    }
                    this.setState({
                        isLoading: true,
                        isListLoading: false,
                        HeaderOption: {
                            back: true,
                            fixture: true,
                            filter: false,//tab != 0 ? false : (this.state.entry_fee_max > 0 || this.state.participants_max > 0 || this.state.prizepool_max > 0),
                            title: '',
                            hideShadow: false,
                            isPrimary: DARK_THEME_ENABLE ? false : true,
                            goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                            // goBackLobby: true,//!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                        }
                    })
                    if (otherData && otherData.from === 'MyTeams') {
                        let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
                        let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
                        if (otherData.lineupObj && otherData.lineupObj.length > 1) {
                            this.setState({ lineup_master_idArray: otherData.lineupObj })
                        } else {
                            let lineupData = otherData.lineupObj && otherData.lineupObj.length == 1 ? otherData.lineupObj[0] : ''
                            this.setState({ lineup_master_id: lineupData.lineup_master_id })

                            setTimeout(() => {
                                if ((tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true') || CinfirmPopUpIsAddFundsClicked == true) {
                                    setTimeout(() => {
                                        this.callAfterAddFundPopup()
                                    }, 200);
                                }
                            }, 500);
                        }
                    } else {
                        this.setState({ lineup_master_idArray: [] })
                    }
                })
            }
        } else {
            this.goToSignup()
        }
    }

    getMyContest = (collId) => {
        let collection_master_id = '';
        if (this.state.LobyyData.collection_master_id) {
            collection_master_id = this.state.LobyyData.collection_master_id;
        }
        else {
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('contest-listing')[1];
                collection_master_id = url.split('/')[1];
            }
        }
        var param = {
            "collection_master_id": collId ? collId : collection_master_id,
            ...(Constants.SELECTED_GAMET == Constants.GameType.MultiGame && { "sports_id": AppSelectedSport, "status": 0 }),
            ...(this.state.isSecondInning ? { is_2nd_inning: 1 } : {})
        }
        this.setState({ isLoaderShow: true })
        if (this.state.isSecIn && Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
            param['is_2nd_inning'] = 1
        }
        let apiStatus = getMyContest
        apiStatus(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })

            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    myContestListData: data
                })
            }
        })
    }

    getFixtureModalData = () => {
        if (this.state.lineup_master_idArray.length > 0 && this.state.FixtureData) {
            const returnData = _cloneDeep(this.state.FixtureData);
            returnData['entry_fee'] = returnData.entry_fee * this.state.lineup_master_idArray.length;
            return returnData
        } else {
            return this.state.FixtureData
        }
    }
    goToPrivateContest(bn_state, geoPlayFree, event) {
        if ((bn_state == 1 || bn_state == 2)) {
            // if ((bn_state == 1 || bn_state == 2) && geoPlayFree == 'true') {
            Utilities.bannedStateToast(bn_state)
        }
        else {
            if (!this.state.showH2H) {
                let hideBanner = ls.get('hide_banner');
                let mSports = Utilities.getSelectedSportsForUrl().toLowerCase();
                let data = this.state.LobyyData;
                let dateformaturl = parseURLDate(data.season_scheduled_date);

                let contestListingPath = this.state.isDFSMulti && data.match_list ?
                    '/' + data.collection_master_id + '/' + data.match_list[0].home + "-vs-" + data.match_list[0].away + "-" + dateformaturl
                    :
                    '/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl;
                if (hideBanner) {
                    this.props.history.push({
                        pathname: '/' + mSports + contestListingPath + '/private-contest',
                        state: { LobyyData: this.state.LobyyData, isSecIn: this.state.isSecondInning }
                    });
                }
                else {
                    ls.set('hide_banner', true);
                    this.props.history.push({
                        pathname: '/' + mSports + contestListingPath + '/private-contest-banner',
                        state: { LobyyData: this.state.LobyyData, isSecIn: this.state.isSecondInning }
                    });
                }
            }
        }
    }

    showCM = () => {
        this.setState({ showCM: true })

    }

    hidePropCM = () => {
        this.setState({ showCM: false }, () => {
            if (WSManager.loggedIn() && Constants.BanStateEnabled && !WSManager.getProfile().master_state_id && Utilities.getMasterData().a_aadhar != "1") {
                CustomHeader.showBanStateModal({ isFrom: 'CL' });
            }
        });
    }

    showMG = () => {
        this.setState({ showMG: true })
    }
    hideMG = () => {
        this.setState({ showMG: false }, () => {
            if (WSManager.loggedIn() && Constants.BanStateEnabled && !WSManager.getProfile().master_state_id && Utilities.getMasterData().a_aadhar != "1") {
                CustomHeader.showBanStateModal({ isFrom: 'CL' });
            }
        });
    }

    showDownloadApp = () => {
        this.ConfirmatioPopUpHide();
        this.setState({
            showDAM: true
        })
    }

    hideDownloadApp = () => {
        this.setState({
            showDAM: false
        })
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

    switchRFClassicTab = (tab) => {
        WSManager.setH2hMessage(false);
        if (tab == 1) {
            ls.set('h2hTab', false);
            this.addGuruTab(true);
            this.setState({
                isLoading: false,
                showH2H: false
            }, () => {
                this.setState({
                    isLoading: true
                })
                const matchParam = this.props.match.params;
                this.setState({
                    lineup_master_id: '',
                    lineup_master_idArray: []
                })
                this.FixtureDetail(matchParam);
                // if (WSManager.loggedIn()) {
                //     this.getUserLineUpListApi(matchParam);
                // }
                this.FixtureContestList(matchParam);
            })
        }
        else if (tab == 2) {
            ls.set('h2hTab', true);
            this.setState({
                isLoading: false,
                showH2H: true
            }, () => {
                this.setState({
                    isLoading: true
                })
                if (this.state.showH2H) {
                    this.h2hCallangeData()
                }
                const matchParam = this.props.match.params;
                this.setState({
                    lineup_master_id: '',
                    lineup_master_idArray: []
                })
                this.FixtureDetail(matchParam);
                // if (WSManager.loggedIn()) {
                //     this.getUserLineUpListApi(matchParam);
                // }
                this.FixtureContestList(matchParam);
            })
        }
        else {
            ls.set('h2hTab', false);
            this.addGuruTab(false);
            this.setState({
                isLoading: false,
                showH2H: false
            }, () => {
                this.setState({
                    isLoading: true
                })
                const matchParam = this.props.match.params;
                this.setState({
                    lineup_master_id: '',
                    lineup_master_idArray: []
                })
                this.FixtureDetail(matchParam);
                // if (WSManager.loggedIn()) {
                //     this.getUserLineUpListApi(matchParam);
                // }
                this.FixtureContestList(matchParam);
            })
        }

    }

    showNCAContest = () => {
        this.setState({
            isLoading: false
        }, () => {
            this.setState({
                isLoading: true
            })
            const matchParam = this.props.match.params;
            this.setState({
                lineup_master_id: '',
                lineup_master_idArray: []
            })
            this.FixtureDetail(matchParam);
            // if (WSManager.loggedIn()) {
            //     this.getUserLineUpListApi(matchParam);
            // }
            this.FixtureContestList(matchParam);
        })
    }

    getFixtureModalData = () => {
        if (this.state.lineup_master_idArray.length > 0 && this.state.FixtureData) {
            const returnData = _cloneDeep(this.state.FixtureData);
            returnData['entry_fee'] = returnData.entry_fee * this.state.lineup_master_idArray.length;
            return returnData
        } else {
            return this.state.FixtureData
        }
    }
    checkSponserUrlDomain = (sponserUrl, baseUrl) => {
        var isPathSame = false;
        const sponserUrlPath = new URL('', sponserUrl);
        const baseUrlPath = new URL('', baseUrl);
        if (sponserUrlPath.hostname == baseUrlPath.hostname) {
            isPathSame = true;
        }
        return isPathSame;

    }

    showLeaderboardModal = () => {
        CustomHeader.LBModalShow()
    }

    showRookieContest = (user_rookie_dtl = '', rookie_setting = '') => {
        let uMonth = parseInt(user_rookie_dtl.months || '0');
        let uWinning = parseInt(user_rookie_dtl.total_winning || '0');
        let month = parseInt(rookie_setting.month_number || '0');
        let winning = parseInt(rookie_setting.winning_amount || '0');
        return (Utilities.getMasterData().a_rookie == 1 && (uMonth < month) && (uWinning < winning))
    }

    checkIsGuruTab(isGuruLabel) {
        var isExist = false;
        for (var tabss of this.state.ContestTabList) {
            if (tabss.value == isGuruLabel) {
                isExist = true
                break
            }
        }
        return isExist

    }
    openGameCenter = (event, data) => {
        event.stopPropagation();
        let gameCenter = '/game-center/' + data.collection_master_id;
        this.props.history.push({ pathname: gameCenter, state: { LobyyData: data } })

    }
    getOpponentDetail = (event, data) => {
        event.preventDefault();
        this.setState({ oppData: data }, () => {
            this.opponetModalShow()
        })

    }
    deselectteam = () => {
        this.setState({
            lineup_master_id: '',
            lineup_master_idArray: []
        })
    }

    // function to set range of filter according to contest fixture list 
    setFilterRange = (list) => {
        let tmpEntryFee = 0;// this.state.entry_fee_max
        let tmpPar = 0;// this.state.participants_max
        let tmpPPool = 0;// this.state.prizepool_max
        _Map(list, (item, idx) => {
            if (parseFloat(item.entry_fee) > parseFloat(tmpEntryFee)) {
                tmpEntryFee = item.entry_fee
            }
            if (parseFloat(item.size) > parseFloat(tmpPar)) {
                tmpPar = item.size
            }
            let entry_fee = parseFloat(item.entry_fee)
            let size = parseFloat(item.size)
            let site_rake = parseFloat(item.site_rake)

            let tempVar = ((entry_fee * size) - ((entry_fee * size * site_rake) / 100))
            if (parseFloat(tempVar) > parseFloat(tmpPPool)) {
                tmpPPool = tempVar
            }
        })
        this.setState({
            entry_fee_to: parseInt(tmpEntryFee),
            entry_fee_max: parseInt(tmpEntryFee),
            participants_to: parseInt(tmpPar),
            participants_max: parseInt(tmpPar),
            prizepool_to: parseInt(tmpPPool),
            prizepool_max: parseInt(tmpPPool)
        }, () => {
            let HOpt = this.state.HeaderOption
            HOpt['filter'] = (this.state.entry_fee_max > 0 || this.state.participants_max > 0 || this.state.prizepool_max > 0)
            this.setState({
                HeaderOption: HOpt
            })
        })
    }

    showTourList = () => {
        if (WSManager.loggedIn()) {
            this.props.history.push({
                pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/dfs-tournament-list'
            })
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    sortList = (sortBy, order) => {
        if (sortBy == 'entryFee') {
            this.setState({
                sort_field: sortBy,
                sortContestList: this.state.sortContestList.sort((a, b) => (order == 'DESC' ? a.entry_fee - b.entry_fee : b.entry_fee - a.entry_fee)),
                sort_order: order,//this.state.sort_order == 'DESC' ? 'ASC' : 'DESC',
                showGroupView: false,
                filterApply: true,
                sortAppliedCount: 1
            })
        }
        if (sortBy == 'spots') {
            this.setState({
                sort_field: sortBy,
                sortContestList: this.state.sortContestList.sort((a, b) => (order == 'DESC' ? a.size - b.size : b.size - a.size)),
                sort_order: order,//this.state.sort_order == 'DESC' ? 'ASC' : 'DESC',
                showGroupView: false,
                filterApply: true,
                sortAppliedCount: 1
            })
        }
        if (sortBy == 'prizePool') {
            this.setState({
                sort_field: sortBy,
                sortContestList: this.state.sortContestList.sort((a, b) => (order == 'DESC' ? a.prize_pool_amount - b.prize_pool_amount : b.prize_pool_amount - a.prize_pool_amount)),
                sort_order: order,//this.state.sort_order == 'DESC' ? 'ASC' : 'DESC',
                showGroupView: false,
                filterApply: true,
                sortAppliedCount: 1
            })
        }
    }

    clearAllFilter = () => {
        const { allContestData } = this.state
        let tmpSortArray = []
        _Map(allContestData.contest, (item) => {
            tmpSortArray = [...tmpSortArray, ...item.contest_list];
        })
        this.setState({
            filterApply: false,
            sort_field: '',
            sort_order: 'DESC',
            sortContestList: this.state.preSortContestList,
            sortAppliedCount: 0,
            filterAppliedCount: 0,
            isFilterApplied: false,
            filterApply: false,
            FixturedContest: allContestData.contest,
            SortContestTotal: allContestData.total_contest,
            FixturedContestTotal: allContestData.total_contest,
            // FixturedPinContest: allContestData.pin_contest,



            entry_fee_from: "",
            entry_fee_to: "",
            participants_from: "",
            participants_to: "",
            prizepool_from: "",
            prizepool_to: "",
            showGroupView: true
        })
        let FilterObj = {
            entry_fee_from: "",
            entry_fee_to: "",
            participants_from: "",
            participants_to: "",
            prizepool_from: "",
            prizepool_to: "",
            isReset: true
        };
        this.filterContestList(FilterObj);
    }

    getWinPrizeAmount = (prize_data) => {
        let prizeDetail = this.handleJson(prize_data.prize_distibution_detail)
        let prizeAmount = this.getWinCalculation(prizeDetail);
        if (prizeAmount.real > 0) {
            return prizeAmount.real
        }
        else if (prizeAmount.bonus > 0) {
            return prizeAmount.bonus
        }
        else {
            // else if(prizeAmount.point > 0){
            return prizeAmount.point
        }
    }



    h2hGeoValidate = (event, item, aadharData, bn_state) => {
        let { geoPlayFree } = this.state;
        let globalThis = this;

        if ((bn_state == 1 || bn_state == 2) && item.entry_fee == '0') {
            if (Utilities.getMasterData().a_aadhar == "1") {
                if ((aadharData && aadharData.aadhar_status == "1") || item.entry_fee == '0') {
                    globalThis.check(event, item, bn_state)
                }
                else {
                    this.aadharConfirmation()
                }
            }
            else {
                globalThis.check(event, item, bn_state)
            }
        }
        else if ((bn_state == 1 || bn_state == 2) && item.entry_fee != '0') {
            Utilities.bannedStateToast(bn_state);
        }
        else {
            if (Utilities.getMasterData().a_aadhar == "1") {
                if ((aadharData && aadharData.aadhar_status == "1") || item.entry_fee == '0') {
                    globalThis.check(event, item, bn_state)
                }
                else {
                    this.aadharConfirmation()
                }
            }
            else {
                globalThis.check(event, item, bn_state)
            }
        }
    }


    goToDetail = () => {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/h2h-detail/' + this.state.LobyyData.collection_master_id,
            state: {
                FixturedContest: this.state.FixtureData,
                LobyyData: this.state.LobyyData,
                // lineupPath: this.props.location.state.lineupPath,
                matchParam: this.props.match.params,
                H2Hchallange: this.state.H2Hchallange
            },
        });
    }

    render() {
        var {
            showContestDetail,
            showConfirmationPopUp,
            userTeamListSend,
            LobyyData,
            showSharContestModal,
            activeTab,
            showLoadMore,
            FixturedContestTotal,
            SortContestTotal,
            showThankYouModal,
            hasMore,
            LoaderShow,
            showGroupView,
            showCollectionInfo,
            isFilterApplied,
            sortContestList,
            FixturedContest,
            FixturedPinContest,
            HeaderOption,
            FixturedDetail,
            showContestListFitler,
            entry_fee_from,
            entry_fee_to,
            participants_from,
            participants_to,
            prizepool_from,
            prizepool_to,
            isListLoading,
            ShimmerList,
            activeContestTab,
            ContestTabList,
            isLoading,
            myContestCount,
            myTeamCount,
            showRulesModal,
            showTeamModal,
            showUJC,
            showDAM,
            TotalTeam,
            showAlert,
            isSecondInning,
            showSecInnigM,
            showRookieM,
            allowH2HChallenge,
            showH2H,
            showOppData,
            showH2hModal,
            lineup_master_idArray,
            isDFSMulti,
            aadharData,
            entry_fee_max,
            participants_max,
            prizepool_max,
            TeamList,
            showRookieHTP,
            sort_field,
            filterAppliedCount,
            filterApply,
            sortAppliedCount,
            isMegaExsist,
            isCWRookie,
            showTourLeadModal,
            TourFilter,
            H2Hchallange,
            boxForm,
            contdet
        } = this.state;

        const FitlerOptions = {
            showContestListFitler: showContestListFitler,
            entry_fee_from: entry_fee_from,
            entry_fee_to: entry_fee_to,
            participants_from: participants_from,
            participants_to: participants_to,
            prizepool_from: prizepool_from,
            prizepool_to: prizepool_to,

            // entry_fee_min: 0,
            // entry_fee_max: entry_fee_max,
            // participants_min: 0,
            // participants_max: participants_max,
            // prizepool_min: 0,
            // prizepool_max: prizepool_max,
        }
        let showMyTeamBtn = true;
        let FixtureData = this.getFixtureModalData();
        if (this.props.location.state && this.props.location.state.from == 'MyTeams' && this.state.lineup_master_id != '' && lineup_master_idArray && lineup_master_idArray.length > 0) {
            showMyTeamBtn = false;
        }
        var settings = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: true,
            autoplaySpeed: 3000,
            centerMode: true,
            centerPadding: "20px",
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "20px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "15px",
                    }

                }
            ]
        };

        var activeSTIDx = 0;
        let showDFSMulti = isDFSMulti && FixturedDetail && FixturedDetail.season_game_count > 1
        let tourCount = LobyyData && LobyyData.match_list && LobyyData.match_list[0] && !_isUndefined(LobyyData.match_list[0].tournament_count) ? parseInt(LobyyData.match_list[0].tournament_count) : 0;
        let tourName = LobyyData && LobyyData.match_list && LobyyData.match_list[0] && !_isUndefined(LobyyData.match_list[0].tournament_name) && LobyyData.match_list[0].tournament_name ? LobyyData.match_list[0].tournament_name : ''
        let bn_state = localStorage.getItem('banned_on')
        let geoPlayFree = localStorage.getItem('geoPlayFree')
        let is_tour_game = LobyyData && LobyyData.is_tour_game == 1 ? true : false;
        let tourContestData = this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.FixturedContest
        let lobMatchList = this.state.LobyyData && this.state.LobyyData.match_list && this.state.LobyyData.match_list[0]

        let tourContestDataisDFSTour = this.props && this.props.history && this.props.history.location && this.props.history.location.state && this.props.history.location.state.isDFSTour

        var tabL = this.state.windowWidth / ContestTabList.length;
        var tabLVal = 125;

        const h2hProps = {
            H2hModalShow: this.H2hModalShow,
            goToDetail: this.goToDetail,
            c_name: this.state.c_name,
            e_fees: this.state.e_fees,
            item: this.state.contdet,
            getPrizeAmount: this.getPrizeAmount
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container contest-listing-web-conatiner header-margin minus-header-height bg-white contest-listing-new ML-contest-listing " + (Constants.DARK_THEME_ENABLE ? ' DT-tranparent' : '')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.contestListing.title}</title>
                            <meta name="description" content={MetaData.contestListing.description} />
                            <meta name="keywords" content={MetaData.contestListing.keywords}></meta>
                        </Helmet>
                        <CustomHeader
                            LobyyData={LobyyData}
                            ref={(ref) => this.headerRef = ref}
                            HeaderOption={HeaderOption}
                            {...this.props}
                            isHSI={isSecondInning}
                            showLobbyFitlers={this.showFilter} />
                        {
                            // (entry_fee_max > 0 || participants_max > 0 || prizepool_max > 0) &&
                            <FilterNew
                                {...this.props}
                                isSecIn={isSecondInning}
                                FitlerOptions={FitlerOptions}
                                hideFilter={this.hideFilter}
                                filterContestList={this.filterContestList}></FilterNew>
                        }

                        <div style={LobyyData.ldb == '1' ? { marginTop: 84 } : {}} className={"webcontainer-inner" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? ' webcontainer-MT' : '')}>
                            {
                                Constants.SELECTED_GAMET == Constants.GameType.MultiGame &&
                                <CollectionSlider
                                    Fixtured={FixturedDetail}
                                    collectionInfo={true}
                                    isFrom={"ContestListing"}
                                    CollectionInfoShow={() => this.CollectionInfoShow()} />
                            }
                            {
                                showDFSMulti &&
                                // <div>Show </div>
                                <DMCollectionSlider
                                    contestSliderData={FixturedDetail}
                                    collectionInfo={true}
                                    isFrom={"ContestListing"}
                                    CollectionInfoShow={() => this.CollectionInfoShow()} />
                            }
                            { LobyyData.match_list
                                    &&
                                    LobyyData.match_list.length <= 1 &&
                                    //  LobyyData.ldb == '1' &&
                                    <>
                            <div className={"primary-overlay" + (LobyyData.ldb == '1' ? ' ldb' : '')}>
                                {
                                    LobyyData.match_list
                                    &&
                                    LobyyData.match_list.length <= 1 && LobyyData.ldb == '1'
                                    &&
                                    <div className="ldb-strip primary" onClick={() => this.showLeaderboardModal()}>
                                        <i className="icon-leaderboard" /><span>{AppLabels.LEADERBOARD} {AppLabels.AVAILABLE}</span>
                                    </div>
                                }
                            </div>
    
                            <div className="fantasy-rules-sec">
                                {
                                    isSecondInning ?
                                        <span className="text-uppercase">
                                            {AppLabels.SEC_INNING}{" • "}<MomentDateComponent data={{ date: LobyyData.season_scheduled_date, format: "D MMM" }} />
                                        </span>
                                        :
                                        <span className="text-uppercase">
                                            {Constants.SELECTED_GAMET == Constants.GameType.DFS && !showDFSMulti ?
                                                AppLabels.DAILY_FANTASY :
                                                // WSC.AppName :
                                                (Constants.SELECTED_GAMET == Constants.GameType.MultiGame || showDFSMulti) ?
                                                    AppLabels.MULTIGAME : ''
                                            }
                                        </span>
                                }
                                {!isSecondInning && <a href
                                    onClick={() => this.openRulesModal()}
                                >
                                    <i className="icon-file"></i>
                                    {AppLabels.RULES}
                                </a>}
                                {
                                    (isSecondInning || Constants.SELECTED_GAMET == Constants.GameType.MultiGame || showDFSMulti) &&
                                    <a href
                                        onClick={(e) => isSecondInning ? this.setState({ showSecInnigM: true }) : this.CollectionInfoShow(e)}
                                    >
                                        {AppLabels.RULES}
                                        {/* <i className="icon-question mr-1"></i>
                                        {AppLabels.HOW_TO_PLAY_FREE} */}
                                    </a>
                                }
                            </div>
                            </>
    }
                            {/* {
                                LobyyData.match_list && LobyyData.match_list.length <= 1 && tourCount > 0 &&
                                <div className="tour-fix-info" onClick={() => this.showTourList()}>
                                    <img src={Images.TROPHY_IMG} alt="" />
                                    Part of {tourName} {tourCount > 1 && <span>+{parseInt(tourCount) - 1} more</span>}
                                    <div className="inline-block">
                                        <div className='arrow-icon-container'>
                                            <i className="icon-arrow-right iocn-first"></i>
                                            <i className="icon-arrow-right iocn-second"></i>
                                            <i className="icon-arrow-right iocn-third"></i>
                                        </div>
                                    </div>
                                </div>
                            } */}

                            {LobyyData && LobyyData.custom_message != '' && LobyyData.custom_message != null &&
                                <div className="">
                                    <Alert variant="warning" className="alert-warning msg-alert-container border-radius-0">
                                        <div className="msg-alert-wrapper">
                                            <span className=""><i className="icon-megaphone"></i></span>
                                            <span>{LobyyData.custom_message}</span>
                                        </div>
                                    </Alert>
                                </div>
                            }
                            <div className={"tab-group" + (this.isGuru() ? ' tab-group-slider' : '')}>
                                <ul>
                                    {
                                        ContestTabList && _Map(ContestTabList, (item, idx) => {
                                            if (activeContestTab == item.value) {
                                                activeSTIDx = idx;
                                            }
                                            return (
                                                <li key={item.value + idx} style={{ width: !this.isGuru() ? 'calc(100% / ' + ContestTabList.length + ')' : '' }} className={activeContestTab == item.value ? 'active' : ''} onClick={() => this.handleTab(item.value)}>
                                                    <a href>{item.label} {item.value != 0 && item.value != 3 && <span>({item.value == 1 ? myContestCount : item.value == 2 && myTeamCount})</span>}
                                                        {item.value == 3 && <span className='guru-tab-span'>{AppLabels.NEW} </span>}
                                                    </a>
                                                </li>
                                            )
                                        })
                                    }
                                    <span style={{ width: tabL > tabLVal ? tabLVal : 'calc(100% / ' + ContestTabList.length + ')', left: 'calc(' + (100 / ContestTabList.length * activeSTIDx) + '%' + (tabL > tabLVal ? (' + ' + ((tabL - tabLVal) / 2) + 'px)') : ')') }} className="active-nav-indicator con-list"></span>
                                </ul>
                            </div>
                            {
                                isLoading && activeContestTab == 2 ?
                                    <MyTeams isSecondInning={isSecondInning} LobyyData={this.state.LobyyData} history={this.props.history} handleTab={this.handleTab} showH2H={showH2H} myTeamCount={myTeamCount} TotalTeam={TotalTeam} />
                                    :
                                    isLoading && activeContestTab == 1 ?
                                        <MyContestList isSecondInning={isSecondInning} handleTab={this.handleTab} LobyyData={this.state.LobyyData} ContestDetailShow={this.ContestDetailShow} check={this.check} shareContest={this.shareContest.bind(this)} {...this.props} showTeam={this.showTeam.bind(this)} myContestCount={myContestCount} myContestListData={this.state.myContestListData} />
                                        :
                                        isLoading && activeContestTab == 3 ?
                                            <GuruTabDetail history={this.props.history} FixturedContest={this.state.LobyyData} LobyyData={this.state.LobyyData} from={'MyTeams'} isFromMyTeams={true} isFrom={'MyTeams'} resetIndex={1} />
                                            :
                                            isLoading &&
                                            <Row>
                                                <Col sm={12}>
                                                    {
                                                        WSManager.loggedIn() && Utilities.getMasterData().private_contest == '2' && !showDFSMulti && !isSecondInning &&
                                                        <React.Fragment>
                                                            {
                                                                <div className="create-contest-slider">
                                                                    <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                                                                        <div className="slider-wrap">
                                                                            <div className="slider-inner-wrap slider-wrap-f">
                                                                                <div className="user-img">
                                                                                    <img src={Images.USER_GROUP_IMG} alt="" />
                                                                                </div>
                                                                                <div className="slide-title">
                                                                                    {AppLabels.CREATE}
                                                                                </div>
                                                                                <div className="slid-desc">
                                                                                    {AppLabels.SLIDER_DES1}
                                                                                </div>
                                                                                <div onClick={(e) => this.goToPrivateContest(bn_state, geoPlayFree, e)} className={"btn btnStyle btn-rounded small private-contest-btn" + ((bn_state == 1 || bn_state == 2) ? '  geo-disabled' : '')}>
                                                                                    <span className="text-uppercase">{AppLabels.CREATE_PRIVATE_CONTEST}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div className="slider-wrap">
                                                                            <div className="slider-inner-wrap slider-wrap-s">
                                                                                <div className="user-img">
                                                                                    <img src={Images.USER_GROUP_IMG} alt="" />
                                                                                </div>
                                                                                <div className="slide-title">
                                                                                    {AppLabels.SHARE}
                                                                                </div>
                                                                                <div className="slid-desc">
                                                                                    {AppLabels.SLIDER_DES2}
                                                                                </div>
                                                                                <div onClick={(e) => this.goToPrivateContest(bn_state, geoPlayFree, e)} className={"btn btnStyle btn-rounded small private-contest-btn" + ((bn_state == 1 || bn_state == 2) ? '  geo-disabled' : '')}>
                                                                                    <span className="text-uppercase">{AppLabels.CREATE_PRIVATE_CONTEST}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div className="slider-wrap">
                                                                            <div className="slider-inner-wrap slider-wrap-t">
                                                                                <div className="user-img">
                                                                                    <img src={Images.USER_GROUP_IMG} alt="" />
                                                                                </div>
                                                                                <div className="slide-title">
                                                                                    {AppLabels.Earn}
                                                                                </div>
                                                                                <div className="slid-desc">
                                                                                    {AppLabels.SLIDER_DES3}
                                                                                </div>
                                                                                <div onClick={(e) => this.goToPrivateContest(bn_state, geoPlayFree, e)} className={"btn btnStyle btn-rounded small private-contest-btn" + ((bn_state == 1 || bn_state == 2) ? '  geo-disabled' : '')}>
                                                                                    <span className="text-uppercase">{AppLabels.CREATE_PRIVATE_CONTEST}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </ReactSlickSlider></Suspense>
                                                                </div>
                                                            }
                                                        </React.Fragment>
                                                    }
                                                    {WSManager.loggedIn() && Utilities.getMasterData().private_contest == '1' &&
                                                        !showH2H && !isSecondInning &&
                                                        <React.Fragment>
                                                            <div className="contest-btn-wrap pb20">
                                                                {
                                                                    Constants.SELECTED_GAMET == Constants.GameType.DFS && !showDFSMulti &&
                                                                    <div onClick={(e) => this.goToPrivateContest(bn_state, geoPlayFree, e)} className={"btn btnStyle btn-rounded small" + (showH2H ? ' disabled' : '') + ((bn_state == 1 || bn_state == 2) ? '  geo-disabled' : '')}>
                                                                        <span className="text-uppercase">{AppLabels.CREATE_PRIVATE_CONTEST}!</span>
                                                                    </div>
                                                                }

                                                            </div>
                                                        </React.Fragment>
                                                    }
                                                    <div className='filter-menu-view'>
                                                        <div className="lft-part">
                                                            <div className="sort-by-text">{AppLabels.SORT_BY}</div>
                                                            <div className={`sports-prizepool-text ${sort_field == 'entryFee' ? 'active' : ''}`} onClick={() => this.sortList('entryFee', this.state.sort_order == 'DESC' ? 'ASC' : 'DESC')}>
                                                                {AppLabels.ENTRY_FEE} {sort_field == 'entryFee' && <i className={`${this.state.sort_order == 'DESC' ? 'icon-arrow-down' : 'icon-arrow-up'}`} />}
                                                            </div>
                                                            <div className={`sports-prizepool-text ${sort_field == 'spots' ? 'active' : ''}`} onClick={() => this.sortList('spots', this.state.sort_order == 'DESC' ? 'ASC' : 'DESC')}>
                                                                {AppLabels.SPOTS} {sort_field == 'spots' && <i className={`${this.state.sort_order == 'DESC' ? 'icon-arrow-down' : 'icon-arrow-up'}`} />}
                                                            </div>
                                                            <div className={`sports-prizepool-text ${sort_field == 'prizePool' ? 'active' : ''}`} onClick={() => this.sortList('prizePool', this.state.sort_order == 'DESC' ? 'ASC' : 'DESC')}>
                                                                {AppLabels.PRIZE_POOL} {sort_field == 'prizePool' && <i className={`${this.state.sort_order == 'DESC' ? 'icon-arrow-down' : 'icon-arrow-up'}`} />}
                                                            </div>
                                                        </div>
                                                        <div className='filter-icon-view' onClick={() => this.showFilter()} >
                                                            <i className='icon-filter-ic' />
                                                            {
                                                                filterAppliedCount != 0 &&
                                                                <span className='icon-plus'></span>
                                                            }
                                                        </div>
                                                    </div>
                                                    {
                                                        filterApply && (filterAppliedCount != 0 || sortAppliedCount != 0) &&
                                                        <div className={`filter-applied-sec ${(sortAppliedCount != 0 || filterAppliedCount != 0) ? 'mb10' : ''}`}>
                                                            <div>{sortContestList.length} {AppLabels.CONTESTS}</div>
                                                            <div className='flex-div'>
                                                                {filterAppliedCount != 0 && <>{parseInt(filterAppliedCount)} {AppLabels.FILTERS} {AppLabels.APPLIED}</>}
                                                                        
                                                                <a href onClick={() => this.clearAllFilter()}>{AppLabels.CLEAR}</a></div>
                                                        </div>
                                                    }
                                                    {/* {
                                                        filterApply && (filterAppliedCount != 0 || sortAppliedCount != 0) &&
                                                        <div className={`filter-applied-sec ${(sortAppliedCount != 0 || filterAppliedCount != 0) ? 'mb10' : ''}`}>
                                                            <div>{sortContestList.length} {AppLabels.CONTESTS}</div>
                                                            <div className='flex-div'>
                                                                {sortAppliedCount != 0 ?
                                                                    <></> :

                                                                    <>
                                                                        {parseInt(filterAppliedCount) + parseInt(sortAppliedCount)} {AppLabels.FILTERS} {AppLabels.APPLIED}
                                                                    </>
                                                                }
                                                                <a href onClick={() => this.clearAllFilter()}>{AppLabels.CLEAR}</a></div>
                                                        </div>
                                                    } */}
                                                    {FixturedContest.length == 0 && sortContestList.length == 0 && (filterAppliedCount == 0 && sortAppliedCount == 0) &&
                                                        !isSecondInning && this.state.LobyyData && this.state.LobyyData.is_h2h == 1 &&
                                                        (Constants.SELECTED_GAMET == Constants.GameType.DFS && FixturedDetail && FixturedDetail.season_game_count == 1 && allowH2HChallenge) &&
                                                        <div className='p-15 pt15'>
                                                            <H2hCard {...h2hProps} />
                                                        </div>
                                                    }
                                                    {
                                                        showH2H ?

                                                            <div >
                                                                {
                                                                    this.state.H2HJoinedContestList && this.state.H2HJoinedContestList.length > 0 ?
                                                                        <div>
                                                                            <div className="top-section-heading">
                                                                                {AppLabels.MY_H2H_CHALLENGES}
                                                                                <a onClick={() => this.handleTab(1)} >{AppLabels.VIEW} {AppLabels.ALL}</a>
                                                                            </div>
                                                                            <H2HJoinedContestSlider
                                                                                JoineContestData={this.state.H2HJoinedContestList}
                                                                                getOpponentDetail={this.getOpponentDetail}

                                                                            />
                                                                        </div>

                                                                        :
                                                                        this.state.H2HBannerList && this.state.H2HBannerList.length > 0 &&
                                                                        <div>
                                                                            {/* <div className="top-section-heading">
                                                                            {AppLabels.MY_CONTEST}
                                                                            <a href onClick={() => this.goToMyContest()}>{AppLabels.VIEW} {AppLabels.ALL}</a>
                                                                        </div> */}
                                                                            <H2HBannerSlider
                                                                                BannerData={this.state.H2HBannerList}

                                                                            />
                                                                        </div>
                                                                }

                                                                <div className='upcoming-contest-title'>{AppLabels.UPCOMING_H2H_CHALLENGES}</div>
                                                                <Row className='banner-c'>


                                                                    {
                                                                        // this.state.H2Hchallange && this.state.H2Hchallange.length > 0 ?
                                                                        this.state.LobyyData && this.state.LobyyData.is_h2h == 1 ?
                                                                            _Map(this.state.H2Hchallange, (item, index) => {
                                                                                return (
                                                                                    <Col onClick={(event) => this.h2hGeoValidate(event, item, aadharData, bn_state)}
                                                                                        className='col-container abc'
                                                                                        key={index}
                                                                                        sm={6}
                                                                                    >
                                                                                        <div className={'main-conatiner' + ((bn_state == 1 || bn_state == 2) ?
                                                                                            (item.entry_fee != '0') ? ' geo-disabled' : ' ' : '')}>
                                                                                            <div className='contanier-inner'>
                                                                                                <i className={"icon-h2h-logo image-icon" + (item.status == 1 ? ' orange-c' : item.status == 2 ? ' blue-c' : item.status == 3 ? ' yellow-c' : item.status == 4 ? '' : '')}></i>

                                                                                                <div className={"label-name" + (item.contest_title && item.contest_title != '' ? ' contest-title' : '')}>
                                                                                                    {item.contest_title != '' ? item.contest_title :
                                                                                                        <div>
                                                                                                            {AppLabels.WIN} {' '}{this.getPrizeAmount(item, 1)}
                                                                                                        </div>

                                                                                                    }
                                                                                                    {/* {AppLabels.WIN} {''}{this.getPrizeAmount(item,1)} */}
                                                                                                </div>
                                                                                                <div className="entry-fee">
                                                                                                    {
                                                                                                        item.entry_fee > 0 ? ((item.prize_type == 1 || item.prize_type == 0 || item.prize_type == 2) ?
                                                                                                            <React.Fragment>
                                                                                                                {
                                                                                                                    item.currency_type == 2 ?
                                                                                                                        <img style={{ height: 15, width: 15, marginTop: -2 }} className="img-coin" alt='' src={Images.IC_COIN} />
                                                                                                                        :
                                                                                                                        <span>
                                                                                                                            {Utilities.getMasterData().currency_code}
                                                                                                                        </span>
                                                                                                                }
                                                                                                                {" " + Utilities.numberWithCommas(item.entry_fee)}{' '} {AppLabels.JOIN}
                                                                                                            </React.Fragment>
                                                                                                            :
                                                                                                            <React.Fragment>
                                                                                                                <span >
                                                                                                                    <i className="icon-bean"></i>
                                                                                                                </span>
                                                                                                                {item.entry_fee} {' '} {AppLabels.JOIN}
                                                                                                            </React.Fragment>
                                                                                                        ) : AppLabels.FREE
                                                                                                    }

                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </Col>
                                                                                )
                                                                            })
                                                                            :
                                                                            <div className='no-data-view'>
                                                                                <NoDataView
                                                                                    BG_IMAGE={Images.no_data_bg_image}
                                                                                    // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                                    MESSAGE_1={isFilterApplied ? AppLabels.NO_RESULT_FOUND_FILTER_1 : AppLabels.NO_FIXTURES_MSG1}
                                                                                    MESSAGE_2={isFilterApplied ? AppLabels.NO_CONTEST_FOR_FILTER_2 : AppLabels.NO_FIXTURES_MSG3}
                                                                                    BUTTON_TEXT={AppLabels.GO_BACK_TO_LOBBY}
                                                                                    onClick={this.goBack}
                                                                                />
                                                                            </div>

                                                                    }
                                                                </Row>
                                                                <div className={'bottom-conatiner' + (!WSManager.loggedIn() ? ' not-loged-in' : '')}>
                                                                    <div className='inner-c'>
                                                                        <div className='count-c'>
                                                                            <div className='count-value'>{Utilities.getMasterData().h2h_data.climit}</div>
                                                                            <div className='join-text'>{AppLabels.H2H_INFO1} {Utilities.getMasterData().h2h_data.climit} {AppLabels.H2H_INFO2}</div>

                                                                        </div>
                                                                        <div onClick={() => this.H2hModalShow()} className='whats-h-2-h'>{AppLabels.WHATS_H2H}</div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            :
                                                            <InfiniteScroll
                                                                dataLength={showGroupView ? FixturedContest.length : sortContestList.length}
                                                                pullDownToRefreshThreshold={300}
                                                                refreshFunction={!showContestDetail && this.handleRefresh}
                                                                pullDownToRefresh={false}
                                                                hasMore={hasMore}
                                                                loader={
                                                                    LoaderShow == true &&
                                                                    <h4 className='table-loader'>{AppLabels.LOADING_MSG}</h4>
                                                                }
                                                                pullDownToRefreshContent={
                                                                    <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8595; {AppLabels.PULL_DOWN_TO_REFRESH}</h3>
                                                                }
                                                                releaseToRefreshContent={
                                                                    <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8593; {AppLabels.RELEASE_TO_REFRESH}</h3>
                                                                }>

                                                                {
                                                                    FixturedPinContest &&
                                                                    _Map(FixturedPinContest, (contest, index) => {
                                                                        let rookie_setting = Utilities.getMasterData().rookie_setting || '';
                                                                        let isRookie = contest.group_id == rookie_setting.group_id;
                                                                        if (isRookie) {
                                                                            if (!this.state.user_rookie_dtl || !this.showRookieContest(this.state.user_rookie_dtl, rookie_setting)) {
                                                                                return ''
                                                                            }
                                                                        }
                                                                        onlyRookieC = false;
                                                                        return (
                                                                            <div className={"contest-list-wrapper mb20" + (filterAppliedCount == 0 && sortAppliedCount == 0 ? ' mt20' : '')} key={index} >
                                                                                {this.renderContestView({ index: index, contest: contest, isPinned: true, isRookie: isRookie })}
                                                                            </div>
                                                                        )
                                                                    })
                                                                }
                                                                {!isSecondInning && filterApply && (filterAppliedCount != 0 || sortAppliedCount != 0) &&
                                                                    // this.state.H2Hchallange.length != 0 && 
                                                                    this.state.LobyyData && this.state.LobyyData.is_h2h == 1 &&
                                                                    (Constants.SELECTED_GAMET == Constants.GameType.DFS && FixturedDetail && FixturedDetail.season_game_count == 1 && allowH2HChallenge) &&
                                                                    <div className='p-15 pt0'>
                                                                        <H2hCard {...h2hProps} />
                                                                    </div>
                                                                }
                                                                {/* {Utilities.getMasterData().allow_gc == 1 && FixturedContest.length === 1 && !is_tour_game && */}
                                                                {Utilities.getMasterData().allow_gc == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.LobyyData.is_gc == 1 && !is_tour_game &&
                                                                    // <div onClick={(event) => this.openGameCenter(event, this.state.LobyyData)} className='bg-game-center'>
                                                                    //     <div className='bg-image'>
                                                                    //         <div className='go-to-game-center-of'>{AppLabels.GO_TO_GAME_CENTER_LISTING_MESAGE}</div>
                                                                    //     </div>
                                                                    // </div>
                                                                    <div>
                                                                        <div className='top-gc-tab'>
                                                                            <div onClick={(event) => this.openGameCenter(event, this.state.LobyyData)} className='bg-game-center-container mt-3'>
                                                                                <div className='inner-view-live'>
                                                                                    <div className="game-center-view">
                                                                                        <div className='image-game-center'>
                                                                                            <img className='home-img'
                                                                                                src={this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].home_flag ?
                                                                                                    Utilities.teamFlagURL(this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].home_flag)
                                                                                                    : (Utilities.teamFlagURL(tourContestData.home_flag) || Images.NODATA)} alt="" />
                                                                                            <img className='away-img'
                                                                                                src={(this.state.LobyyData.match_list
                                                                                                    && this.state.LobyyData.match_list[0].away_flag)
                                                                                                    ?
                                                                                                    Utilities.teamFlagURL(this.state.LobyyData.match_list
                                                                                                        && this.state.LobyyData.match_list[0].away_flag) :
                                                                                                    (Utilities.teamFlagURL(tourContestData.away_flag) || Images.NODATA)} alt=""
                                                                                            />
                                                                                        </div>
                                                                                        <div className='responsive-view-cotainer'>
                                                                                            <span className="go-to-game-center-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</span>
                                                                                            <span className="team-name">
                                                                                                {lobMatchList ? lobMatchList.home : tourContestData.home}
                                                                                                {" " + AppLabels.VS + " "}
                                                                                                {lobMatchList ? lobMatchList.away : tourContestData.away}
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div className='arrow-icon-container'>
                                                                                        <i className="icon-arrow-right iocn-first"></i>
                                                                                        <i className="icon-arrow-right iocn-second"></i>
                                                                                        <i className="icon-arrow-right iocn-third"></i>

                                                                                    </div>

                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {/* <div className='arrow-icon-container'>
                                                                            <i className="icon-arrow-right iocn-first"></i>
                                                                            <i className="icon-arrow-right iocn-second"></i>
                                                                            <i className="icon-arrow-right iocn-third"></i>

                                                                        </div> */}
                                                                    </div>

                                                                    // </div>
                                                                    // </div>
                                                                    // </div>
                                                                }

                                                                {showGroupView &&
                                                                    <>


                                                                        {_Map(FixturedContest, (group, idx) => {

                                                                            var arrGroupList = [];
                                                                            if (group.contest_list.length > 2 && group.isReadMore == undefined) {
                                                                                arrGroupList.push(group.contest_list[0]);
                                                                                arrGroupList.push(group.contest_list[1]);
                                                                            } else {
                                                                                arrGroupList = group.contest_list;
                                                                            }
                                                                            let rookie_setting = Utilities.getMasterData().rookie_setting || '';
                                                                            let isRookie = group.group_id == rookie_setting.group_id;
                                                                            if (isRookie) {
                                                                                if (!this.state.user_rookie_dtl || !this.showRookieContest(this.state.user_rookie_dtl, rookie_setting)) {
                                                                                    return ''
                                                                                }
                                                                            }
                                                                            onlyRookieC = false;
                                                                            return (
                                                                                <>
                                                                                    {

                                                                                        <div className="contest-list-wrapper xmt20 mb20" key={idx}>
                                                                                            {!isSecondInning &&
                                                                                                // this.state.H2Hchallange.length != 0 && 
                                                                                                (filterAppliedCount == 0 && sortAppliedCount == 0) && this.state.LobyyData && this.state.LobyyData.is_h2h == 1 &&
                                                                                                (Constants.SELECTED_GAMET == Constants.GameType.DFS && FixturedDetail && FixturedDetail.season_game_count == 1 && !isMegaExsist && allowH2HChallenge) &&
                                                                                                isCWRookie && idx == 1 && isRookie &&
                                                                                                <div className="mt20"><H2hCard {...h2hProps} /></div>
                                                                                            }
                                                                                            {!isSecondInning &&
                                                                                                // this.state.H2Hchallange.length != 0 && 
                                                                                                this.state.LobyyData && this.state.LobyyData.is_h2h == 1 &&
                                                                                                (filterAppliedCount == 0 && sortAppliedCount == 0) &&
                                                                                                (Constants.SELECTED_GAMET == Constants.GameType.DFS && FixturedDetail && FixturedDetail.season_game_count == 1 && !isMegaExsist && allowH2HChallenge) &&
                                                                                                (!isRookie || (isRookie && !isCWRookie)) && idx == 0 &&
                                                                                                <div className="mt20"> <H2hCard {...h2hProps} /></div>
                                                                                            }
                                                                                            <div className={"contest-listing-card" + (group.group_id == 1 ? ' is-mega-contest' : '') + (showLoadMore && group.total > 2 ? ' more-contest-card' : '')}>
                                                                                                <div className='rookie-contest-view'>
                                                                                                    <div className="contest-listing-card-header">
                                                                                                        <img src={Images.S3_BUCKET_IMG_PATH + group.icon} alt="" className={`contest-img ${group.group_id == 6 ? ' free-contest-img' : ''}`} />
                                                                                                        <div className="contest-name-heading">
                                                                                                            {group.group_name}
                                                                                                            {isRookie && <i onClick={() => this.setState({ showRookieM: true })} className="icon-info info-rookie" />}
                                                                                                        </div>
                                                                                                        <div className="contest-name-heading-description">{group.description}</div>
                                                                                                    </div>
                                                                                                    {isRookie && <div className='htp-rookie-text' onClick={() => this.setState({ showRookieHTP: true })}>{AppLabels.How_to_Play}</div>}
                                                                                                </div>
                                                                                                {
                                                                                                    _Map(arrGroupList, (contest, index) => {
                                                                                                        return (
                                                                                                            <div key={index} >
                                                                                                                {this.renderContestView({ index: index, contest: contest, isGroup: true, isRookie: isRookie })}
                                                                                                            </div>
                                                                                                        )
                                                                                                    })
                                                                                                }

                                                                                                {group.contest_list.length > 2 && group.isReadMore == undefined &&
                                                                                                    <div className="text-center show-more-contest" onClick={() => this.showDetail(group, idx)}>
                                                                                                        {AppLabels.MORE_CONTEST}<i className="icon-arrow-down"></i>
                                                                                                    </div>
                                                                                                }

                                                                                                {group.contest_list.length > 2 && group.isReadMore == true &&
                                                                                                    <div className="text-center show-more-contest" onClick={() => this.hideDetail(group, idx)}>
                                                                                                        {AppLabels.LESS_CONTEST}<i className="icon-arrow-up"></i>
                                                                                                    </div>
                                                                                                }



                                                                                                {/* {
                                                                                                    this.state.TournamentList.length > 0 &&
                                                                                                } */}
                                                                                                {/* {
                                                                                                    idx == 1 && Utilities.getMasterData().allow_gc == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.LobyyData.is_gc == 1 &&
                                                                                                    // <div onClick={(event) => this.openGameCenter(event, this.state.LobyyData)} className='bg-game-center'>
                                                                                                    //     <div className='bg-image'>
                                                                                                    //         <div className='go-to-game-center-of'>{AppLabels.GO_TO_GAME_CENTER_LISTING_MESAGE}</div>
                                                                                                    //     </div>
                                                                                                    // </div>
                                                                                                    <div onClick={(event) => this.openGameCenter(event, this.state.LobyyData)} className='bg-game-center-container mt-3'>
                                                                                                        <div className='inner-view-live'>
                                                                                                            <div className="game-center-view">
                                                                                                                <div className='image-game-center'>
                                                                                                                    <img className='home-img' src={this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].home_flag ? Utilities.teamFlagURL(this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].home_flag) : Images.NODATA} alt="" />
                                                                                                                    <img className='away-img' src={this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].away_flag ? Utilities.teamFlagURL(this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].away_flag) : Images.NODATA} alt="" /></div>
                                                                                                                <div className='responsive-view-cotainer'>
                                                                                                                    <span className="go-to-game-center-text">{AppLabels.GO_TO_GAME_CENTER_FOR}</span>
                                                                                                                    <span className="team-name">
                                                                                                                        {this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].home}{" " + AppLabels.VS + " "}{this.state.LobyyData.match_list && this.state.LobyyData.match_list[0].away}</span>
                                                                                                                </div>
                                                                                                            </div>

                                                                                                            <div className='arrow-icon-container'>
                                                                                                                <i className="icon-arrow-right iocn-first"></i>
                                                                                                                <i className="icon-arrow-right iocn-second"></i>
                                                                                                                <i className="icon-arrow-right iocn-third"></i>

                                                                                                            </div>

                                                                                                        </div>

                                                                                                    </div>

                                                                                                } */}

                                                                                            </div>
                                                                                            {/* {this.state.H2Hchallange.length != 0 &&  */}
                                                                                            {!isSecondInning && this.state.LobyyData && this.state.LobyyData.is_h2h == 1 &&
                                                                                                (filterAppliedCount == 0 && sortAppliedCount == 0) &&
                                                                                                (Constants.SELECTED_GAMET == Constants.GameType.DFS && FixturedDetail && FixturedDetail.season_game_count == 1 && allowH2HChallenge &&
                                                                                                    isMegaExsist && group.group_id == 1) &&
                                                                                                // idx == 0 && group.group_id == 1) && 
                                                                                                <> <H2hCard {...h2hProps} /></>
                                                                                            }


                                                                                        </div>


                                                                                    }




                                                                                </>
                                                                            );
                                                                        })}

                                                                    </>

                                                                }

                                                                {
                                                                    !showGroupView && _Map(sortContestList, (contest, index) => {
                                                                        return (
                                                                            <div className="contest-list-wrapper xmt20 mb20" key={index} >
                                                                                {this.renderContestView({ index: index, contest: contest })}
                                                                            </div>
                                                                        );
                                                                    })
                                                                }
                                                                {
                                                                    ((FixturedContest.length == 0 && FixturedPinContest.length == 0) || onlyRookieC) && !isListLoading &&
                                                                    <NoDataView
                                                                        BG_IMAGE={Images.no_data_bg_image}
                                                                        // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                                        CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                                        MESSAGE_1={isFilterApplied ? AppLabels.NO_RESULT_FOUND_FILTER_1 : AppLabels.NO_FIXTURES_MSG1}
                                                                        MESSAGE_2={isFilterApplied ? AppLabels.NO_CONTEST_FOR_FILTER_2 : AppLabels.NO_FIXTURES_MSG3}
                                                                        BUTTON_TEXT={AppLabels.GO_BACK_TO_LOBBY}
                                                                        onClick={this.goBack}
                                                                    />
                                                                }
                                                                {
                                                                    FixturedContest.length == 0 && FixturedPinContest.length == 0 && isListLoading &&
                                                                    ShimmerList.map((item, index) => {
                                                                        return (
                                                                            <Shimmer key={index} index={index} />
                                                                        )
                                                                    })
                                                                }
                                                            </InfiniteScroll>
                                                    }
                                                </Col>
                                            </Row>
                            }
                        </div>
                        {
                            WSManager.loggedIn() && isLoading &&
                            <React.Fragment>
                                {
                                    //  (activeContestTab == 0 && userTeamListSend.length < parseInt(Utilities.getMasterData().a_teams) && 
                                    // Utilities.getMasterData().a_guru == '1' &&
                                    // !this.state.isSecondInning && Constants.AppSelectedSport == SportsIDs.cricket 
                                    // ) ?
                                    //     <div className="bottom multi-btm-bottom">
                                    //         <>
                                    //             <Button className="btn-primary"  onClick={() => this.redirectToGuru()} >{AppLabels.GENERAT_YOUR_TEAM}</Button>
                                    //             <Button className="btn-primary"  onClick={() => this.redirectToMyTeams()} >{AppLabels.CREATE_YOUR_TEAM}</Button>
                                    //         </>
                                    //     </div>

                                    // :
                                    activeContestTab == 0 && userTeamListSend.length < parseInt(Utilities.getMasterData().a_teams) &&
                                    <Button onClick={() => this.redirectToMyTeams()} className="btn-block btn-primary bottom">{AppLabels.CREATE_YOUR_TEAM}</Button>
                                }


                            </React.Fragment>
                        }
                        {
                            showContestDetail &&
                            <ContestDetailModal
                                profileShow={this.state.aadharData}
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                OpenContestDetailFor={this.state.FixtureData}
                                activeTabIndex={activeTab}
                                isSecIn={isSecondInning}
                                LobyyData={this.state.LobyyData}
                                aadharData={this.state.aadharData}
                                {...this.props} />
                        }
                        {
                            showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={showConfirmationPopUp}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={userTeamListSend}
                                TotalTeam={TotalTeam}
                                FixturedContest={FixtureData}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={LobyyData}
                                fromContestListingScreen={true}
                                createdLineUp={this.state.lineup_master_id}
                                selectedLineUps={this.state.lineup_master_idArray}
                                showDownloadApp={this.showDownloadApp}
                                isSecIn={isSecondInning}
                                isBenchEnable={this.state.isBenchEnable}
                            />
                        }

                        {
                            showSharContestModal &&
                            <ShareContestModal
                                IsShareContestModalShow={this.shareContestModalShow}
                                IsShareContestModalHide={this.shareContestModalHide}
                                FixturedContestItem={FixtureData}
                                LobyyData={this.state.LobyyData} />
                        }

                        {
                            showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.goToLobby}
                                seeMyContestEvent={this.seeMyContest} />
                        }
                        {
                            showOppData &&
                            <H2HOpponentDetailModal
                                MShow={this.opponetModalShow}
                                MHide={this.opponetModalHide}
                                opponentData={this.state.oppData}
                            />
                        }
                        {
                            showH2hModal &&
                            <WhatIsH2HChallengeModal
                                {...this.props} ModalData={{
                                    mShow: this.H2hModalShow,
                                    mHide: this.H2hModalHide
                                }}
                            />
                        }

                        {
                            showCollectionInfo &&
                            <CollectionInfoModal
                                IsCollectionInfoShow={showCollectionInfo}
                                IsCollectionInfoHide={this.CollectionInfoHide} />
                        }
                        {
                            showDAM &&
                            <DownloadAppBuyCoinModal
                                hideM={this.hideDownloadApp}
                            />
                        }
                        {
                            showRulesModal &&
                            <RulesScoringModal MShow={showRulesModal} MHide={this.hideRulesModal} />
                        }
                        {
                            showTeamModal &&
                            <ShowMyAllTeams show={showTeamModal} hide={this.hideTeam} data={this.state.allTeamData} />
                        }
                        {
                            Constants.SELECTED_GAMET == Constants.GameType.DFS && this.state.showCM && this.state.CLCoachMarkStatus == 0 &&
                            <ContestListingCoachMarkModal
                                {...this.props} cmData={{
                                    mHide: this.hidePropCM,
                                    mShow: this.showCM
                                }}
                            />
                        }
                        {
                            Constants.SELECTED_GAMET == Constants.GameType.MultiGame && this.state.showMG && this.state.MGCoachMarkStatus == 0 &&
                            <MGContestListingCoachMarkModal
                                {...this.props} cmData={{
                                    mHide: this.hideMG,
                                    mShow: this.showMG
                                }}
                            />
                        }

                        {showTourLeadModal &&
                            <TournamentLeaderboardModal
                                {...this.props}
                                showTourLeadModal={showTourLeadModal}
                                closeMoreTour={this.closeMoreTour}
                                TourFilter={this.state.TourFilterList}
                            />
                        }

                        {
                            showUJC &&
                            <UnableJoinContest
                                showM={showUJC}
                                hideM={this.hideUJC}
                            />
                        }
                        {this.state.tooltiOverLay &&
                            <div className="tooltip-view-second">
                                {AppLabels.TOOLTIP_TEXT_SECOND_INNING}
                            </div>
                        }
                        {
                            Constants.SELECTED_GAMET == Constants.GameType.DFS && activeContestTab == 0 &&
                            (this.state.LobyyData && this.state.LobyyData['2nd_total'] && this.state.LobyyData['2nd_total'] > 0) &&
                            !this.state.isSecondInning &&
                            <div className="second-inning-view">
                                <img src={Images.SECOND_INNINIG_IMG} alt="" onClick={() => this.secondInningTooltip()} />
                            </div>
                        }
                        {/* 
                        {
                            showSecInnigM &&
                            <SecondIngHTPModal
                                {...this.props}
                                mShow={showSecInnigM}
                                mHide={() => this.setState({ showSecInnigM: false })}
                            />
                        } */}

                        {
                            showSecInnigM &&
                            <SecondIngFanRules
                                {...this.props}
                                mShow={showSecInnigM}
                                mHide={() => this.setState({ showSecInnigM: false })}
                            />
                        }
                        {
                            showRookieM &&
                            <WhatIsRookieModal
                                {...this.props}
                                mShow={showRookieM}
                                mHide={() => this.setState({ showRookieM: false })}
                            />
                        }
                        {
                            showRookieHTP &&
                            <RookieContestHTP
                                {...this.props}
                                mShow={showRookieHTP}
                                mHide={() => this.setState({ showRookieHTP: false })}
                            />
                        }
                    </div >
                )
                }
            </MyContext.Consumer >
        )
    }
}