import React, { lazy, Suspense } from 'react';
import { Row, Col, Button, ProgressBar, OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import { isMobile } from 'react-device-detect';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, _isUndefined, _filter, _cloneDeep, parseURLDate, getPrizeInWordFormat, checkBanState, isValidJson } from '../../Utilities/Utilities';
import { setValue, AppSelectedSport, preTeamsList, DARK_THEME_ENABLE, PFSelectedSport } from '../../helper/Constants';
import { NavLink } from "react-router-dom";
import { Sports, SportsIDs } from "../../JsonFiles";
import { GetPFFixtureDetails, GetPFFixtureContest, GetPFUserTeams, GetPFJoinGame, GetPFMyContestTeamCount, GetPickFantasySports, getUserProfile } from "../../WSHelper/WSCallings";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
// import CollectionSlider from "./CollectionSlider";
import { Thankyou, ContestDetailModal, ConfirmationPopup, UnableJoinContest, ShareContestModal, CollectionInfoModal, RulesScoringModal, ShowMyAllTeams, SecondIngHTPModal, WhatIsRookieModal } from '../../Modals';
import { NoDataView } from '../CustomComponent';
import { createBrowserHistory } from 'history';
import * as Constants from "../../helper/Constants";
import PFMyTeams from './PFMyTeam';
import PFMyContestList from "./PFMyContestList";
import { ContestListingCoachMarkModal, MGContestListingCoachMarkModal } from '../../Component/CoachMarks';
import { DownloadAppBuyCoinModal, RFNotPlayingPlayerConfirm } from "../../Modals";
import Filter from "../../components/filter";
import PFRulesScoringModal from './PFRulesScoringModal';
// import MyAlert from '../Modals/MyAlert';
// import CountdownTimer from './../views/CountDownTimer';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
var globalThis = null;

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

export default class PFContestListing extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineup_master_id: '',
            lineup_master_idArray: [],
            FixturedContest: [],
            ShimmerList: [1, 2, 3, 4, 5],
            showContestDetail: false,
            WinnerCount: [],
            FixtureData: '',
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
            showModalNo: 2,//((Constants.SELECTED_GAMET == Constants.GameType.DFS && ls.get('cl-coachmark') != 1)) ? 1 : 2,

            showUJC: false,
            showDAM: false,
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                fixture: true,
                filter: true,
                title: '',
                hideShadow: false,
                goBackLobby: true
                // goBackLobby: !_isUndefined(props.location.state) ? props.location.state.isFromPM : false
            },
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
                    label: AppLabels.MY_PICKS,
                    value: 2
                }

            ],
            showOppData: false,
            profileData: ''
        }
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
        if (ls.get("selOptArray")) {
            ls.remove('selOptArray')
        }
        if (ls.get("pickQueList")) {
            ls.remove('pickQueList')
        }
        if (ls.get("isPickEdit")) {
            ls.remove('isPickEdit')
        }
        Utilities.handleAppBackManage('contest-listing')
        const history1 = createBrowserHistory();
        const location1 = history1.location;
        const searchQ = queryString.parse(location1.search);

        let url = window.location.href;
        let showMyTeamTab = ls.get('showMyTeam') && ls.get('showMyTeam') == 1 ? true : false;
        if (url.includes('#')) {
            let tab = url.split('#')[1];
            url = url.split('#')[0];
            this.setState({
                activeContestTab: tab
            }, () => {
                this.setState({
                    HeaderOption: {
                        back: true,
                        fixture: true,
                        filter: tab != 0 ? false : true,
                        title: '',
                        hideShadow: false,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        goBackLobby: true
                        // goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                    }
                })

            })
            window.history.replaceState("", "", url + "#" + this.state.activeContestTab);
            ls.set('toRosterTab', false)
        }
        else if (showMyTeamTab) {
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
                        goBackLobby: true
                        // goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                    }
                })

            })
            window.history.replaceState("", "", url + "#" + 2);
            ls.set('toRosterTab', false)
        }
        else {
        }
        Utilities.scrollToTop()
        globalThis = this;
        const matchParam = this.props.match.params;
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
            this.getUserLineUpListApi(matchParam);
            WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            if (this.state.showModalNo != 1 && Constants.BanStateEnabled && !WSManager.getProfile().master_state_id && Utilities.getMasterData().a_aadhar != "1") {
                CustomHeader.showBanStateModal({ isFrom: 'CL' });
            }
            this.callContestTeamCount(matchParam);
            getUserProfile().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({ profileData: responseJson.data });
                }
            })
        }
        this.FixtureContestList(matchParam);

        this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : this.props.location.state.LobyyData);
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'contestlist');

    }

    componentWillUnmount() {
        window.removeEventListener('resize', () => { });
    }

    /**
     * @description lifecycle method of react,
     * method to load locale storage data and props data
     */
    UNSAFE_componentWillMount() {
        Utilities.setScreenName('contestListing')

        this.checkOldUrlPattern();
        this.checkSportsList();
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
                    filter: true,
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

    callContestTeamCount(data) {
        let param = {
            // "sports_id": PFSelectedSport.sports_id == 0 ? (this.state.LobyyData.sports_id || this.state.FixturedDetail.sports_id) : PFSelectedSport.sports_id,
            "season_id": data.season_id
        }
        this.setState({ isLoading: false })
        let api = GetPFMyContestTeamCount
        api(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                this.setState({
                    myContestCount: data && data.contest_count ? data.contest_count : 0,
                    myTeamCount: data && data.team_count ? data.team_count : 0,
                    isLoading: true,
                    user_rookie_dtl: data && data.user_rookie_dtl ? data.user_rookie_dtl : '' || ''
                })
            }
        })
    }

    aadharConfirmation = () => {
        Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
        this.props.history.push({ pathname: '/aadhar-verification' })
    }

    /**
     * @description this method is used to replace old url pattern to new eg. from "/7/contest-listing" to "/cricket/contest-listing"
     */
    checkOldUrlPattern = () => {
        let sportsId = this.props.match.params.sportsId;
        console.log('checkOldUrlPattern', sportsId)
        // if (!(sportsId in Sports)) {
        //     if (sportsId in Sports.url) {
        //         let sportsId = this.props.match.params.sportsId;
        //         let myKey = this.props.match.params.myKey;
        //         let season_id = this.props.match.params.season_id;

        //         this.props.history.replace("/" + Sports.url[sportsId] + "/contest-listing/" + season_id + '/' + myKey + "?sgmty=" + btoa(Constants.SELECTED_GAMET));

        //         return;
        //     }
        // }
    }

    /**
     * @description this method is used to call api for sports list when its not their in ls
     */
    checkSportsList = () => {
        if (!ls.get('PFSportList')) {
            GetPickFantasySports().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    let data = [{
                        is_default: "0",
                        name: "featured",
                        sports_id: "0"
                    }]
                    data= [...data,...responseJson.data];
                    
                    ls.set('PFSportList', data)
                }
            })
        }
    }

    /**
     * 
     * @description method to refresh page contest when user pull down to refresh screen
     */
    async handleRefresh(resolve, reject) {
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
    check(event, FixturedContestItem) {
        WSManager.loggedIn() ? globalThis.joinGame(event, FixturedContestItem) : globalThis.goToSignup()
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
        if (!isValidJson(FixturedContestItem.prize_distibution_detail)) {

            FixturedContestItem['prize_distibution_detail'] = JSON.parse(FixturedContestItem.prize_distibution_detail)
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
        console.log('goToLineup FixturedContestItem',FixturedContestItem)
        console.log('goToLineup this.state.LobyyData',this.state.LobyyData)
        let urlData = this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        lineupPath = '/pick-fantasy/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl

        this.props.history.push({
            pathname: lineupPath.toLowerCase(),
            state: {
                FixturedContest: FixturedContestItem,
                contestListData: FixturedContestItem,
                LobyyData: this.state.LobyyData, resetIndex: 1,
                current_sport: PFSelectedSport.sports_id, isFrom: 'MyTeams'
            }
        })
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
                    let urlData = this.state.LobyyData;
                    let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                    dateformaturl = new Date(dateformaturl);
                    let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                    let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                    dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                    let lineupPath = '';
                    // if (urlData.home) {
                    lineupPath = '/pick-fantasy/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: PFSelectedSport.sports_id } })
                    // }
                    // else {
                    //     let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    //     lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                    //     this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: PFSelectedSport.sports_id} })
                    // }
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
        console.log('data',data)
        console.log('this.state.LobyyData',this.state.LobyyData)
        console.log('this.state.FixturedDetail',this.state.FixturedDetail)
        let SID = Utilities.getPFSelectedSportsID(data.sportsId) ? Utilities.getPFSelectedSportsID(data.sportsId) : PFSelectedSport.sports_id
        let param = {
            "sports_id": (SID == 0 ? (this.state.LobyyData.sports_id || this.state.FixturedDetail && this.state.FixturedDetail.sports_id) : SID),
            "season_id": data.season_id
        }
        // if (param.sports_id) {
        //     ls.set('selectedSports', param.sports_id.toString() || param.sports_id);
        //     setValue.setAppSelectedSport(param.sports_id);
        // }
        // setTimeout(() => {
        //     ls.set('selectedSports', param.sports_id.toString() || param.sports_id);
        //     setValue.setAppSelectedSport(param.sports_id);
        // }, 100);
        this.setState({ isListLoading: true })

        GetPFFixtureContest(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    FixturedContest: responseJson.data.contest,
                    FixturedPinContest: responseJson.data.pin_contest,
                    allContestData: responseJson.data,
                }, () => {
                    this.setState({
                        isListLoading: false
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
                });
            }
        })
        if (WSManager.loggedIn()) {
            this.callContestTeamCount(data);
        }
    }

    /**
     * @description method to get fixture detail
     */
    FixtureDetail = async (data) => {
        if (!this.state.LobyyData.home || !this.state.FixturedDetail) {
            let sportsID = Utilities.getPFSelectedSportsID(data.sportsId)
            let param = {
                // "sports_id": sportsID,
                "season_id": data.season_id,
            }
            let apiStatus = GetPFFixtureDetails;

            var api_response_data = await apiStatus(param);
            if (api_response_data) {
                if (_isUndefined(this.props.location.state)) {
                    this.setState({
                        LobyyData: api_response_data.data
                    })
                }
                this.setState({
                    FixturedDetail: api_response_data.data,
                })
            }
        }
    }

    getUserLineUpListApi = async (CollectionData) => {
        let param = {
            // "sports_id": PFSelectedSport.sports_id == 0 ? (this.state.LobyyData.sports_id || this.state.FixturedDetail.sports_id) : PFSelectedSport.sports_id,
            "season_id": CollectionData.season_id,
        }
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = await GetPFUserTeams(param, user_unique_id);
        if (api_response_data) {
            api_response_data = api_response_data.data
            // let tList = _filter(api_response_data, (obj, idx) => {
            //     return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            // })
            this.setState({
                TotalTeam: api_response_data,
                TeamList: api_response_data,
                userTeamListSend: api_response_data,
            })

            if (this.state.userTeamListSend && this.state.userTeamListSend.length > 0) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {

                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })

                this.setState({ userTeamListSend: tempList });
            }
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
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd } });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let ApiAction = GetPFJoinGame;
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            'user_team_id': dataFromConfirmPopUp.selectedTeam.value.user_team_id
        }
        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
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
                    lineup_master_idArray: []
                })
                setTimeout(() => {

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');
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

    CheckAadhaarValidate = (e, contest, profileData, ContestDisabled) => {
        // (Utilities.getMasterData().a_aadhar == 1 && contest.entry_fee != '0') ?
        //     (profileData && profileData.aadhar_status == "1") ?
        //         (event) => (ContestDisabled) ? null : globalThis.check(event, contest)
        //         :
        //         () => this.aadharConfirmation()
        //     :
        //     (event) => (ContestDisabled) ? null : globalThis.check(event, contest)

        if (Utilities.getMasterData().a_aadhar == 1 && contest.entry_fee != '0') {
            if (profileData && profileData.aadhar_status == "1") {
                if (e && ContestDisabled) {
                    return null;
                }
                else {
                    globalThis.check(e, contest)
                }
            }
            else {
                this.aadharConfirmation()
            }
        }
        else {
            if (e && ContestDisabled) {
                return null;
            }
            else {
                globalThis.check(e, contest)
            }
        }
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            let urlData = this.state.LobyyData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

            // if (urlData.home) {
            this.props.history.push({ pathname: '/pick-fantasy/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: PFSelectedSport.sports_id } })
            // }
            // else {
            //     let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            //     this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: PFSelectedSport.sports_id } })
            // }
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


        let prize_data = obj.prize_distibution_detail ? obj.prize_distibution_detail : obj.prize_distribution_detail;
        prize_data = JSON.parse(prize_data)
        let prizeAmount = this.getWinCalculation(prize_data);
        let prize = prizeAmount.real > 0 ? prizeAmount.real : prizeAmount.bonus > 0 ? prizeAmount.bonus : prizeAmount.point;

        let isWinBetween = prizeAmount.is_tie_breaker == 1 ? true : (prize >= filterObj.prizepool_from && prize <= filterObj.prizepool_to)

        return (
            (eFee >= filterObj.entry_fee_from && eFee <= filterObj.entry_fee_to)
            &&
            (partic >= filterObj.participants_from && partic <= filterObj.participants_to)
            &&
            isWinBetween
        )
    }

    filterContestList = (filterObj) => {
        const { allContestData } = this.state
        if (filterObj.isReset) {
            this.setState({
                showContestListFitler: false,
                FixturedContest: allContestData.contest,
                FixturedPinContest: allContestData.pin_contest,
                isFilterApplied: false
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
            let filterPINArray = _filter(cloneAllData.pin_contest, (obj) => {
                return this.filterConditions(filterObj, obj)
            })

            this.setState({
                showContestListFitler: false,
                FixturedContest: cloneAllData.contest,
                FixturedPinContest: filterPINArray,
                isFilterApplied: true
            });
        }
        this.setState({
            entry_fee_from: filterObj.entry_fee_from,
            entry_fee_to: filterObj.entry_fee_to,
            participants_from: filterObj.participants_from,
            participants_to: filterObj.participants_to,
            prizepool_from: filterObj.prizepool_from,
            prizepool_to: filterObj.prizepool_to
        });
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

    redirectToMyTeams() {
        let urlData = this.state.LobyyData;
        WSManager.clearLineup()
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
        // if (urlData.home) {
        this.props.history.push({ pathname: '/pick-fantasy/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: PFSelectedSport.sports_id } })
        // }
        // else {
        //     let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
        //     this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: PFSelectedSport.sports_id} })
        // }

    }

    showDetail(Group, index) {
        Group.isReadMore = true;
        var arrFixture = this.state.FixturedContest;
        arrFixture[index] = Group;
        this.setState({ FixturedContest: arrFixture });
    }

    hideDetail(Group, index) {
        Group.isReadMore = undefined;
        var arrFixture = this.state.FixturedContest;
        arrFixture[index] = Group;
        this.setState({ FixturedContest: arrFixture });
    }

    renderContestView(data) {
        let { contest, isPinned, isGroup, isRookie } = data;
        let { profileData } = this.state;
        let sponserImage = data.contest.sponsor_logo && data.contest.sponsor_logo != null ? data.contest.sponsor_logo : 0
        let remainingJoinCount = (contest.size || 0) - (contest.total_user_joined || 0);
        let lineupAryLength = this.state.lineup_master_idArray.length;
        let user_join_count = parseInt(contest.user_joined_count || 0);
        let ContestDisabled = lineupAryLength > 1 ? (lineupAryLength > remainingJoinCount || ((lineupAryLength + user_join_count) > contest.multiple_lineup) || contest.multiple_lineup <= 1) : false;
        let user_data = ls.get('profile');
        return (
            <div className={"contest-list contest-listing-list " + (isGroup ? ' contest-card-body' : ' position-relative') + (isPinned ? ' pinned' : '')}>
                <div className={"contest-list-header" + (ContestDisabled ? ' disabled-contest-card' : '')}
                    onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 2, event)}>


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
                                    <span className="position-relative">{contest.contest_title}</span>
                                    :
                                    <React.Fragment>
                                        <span className="position-relative" onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 1, event)}>
                                            <span className="prize-pool-text text-capitalize" >{AppLabels.WIN} </span>

                                            <span>
                                                {this.getPrizeAmount(contest, 0)}
                                            </span>
                                        </span>
                                    </React.Fragment>
                            }
                            {/* <i onClick={(shareContestEvent) => (ContestDisabled) ? null : globalThis.shareContest(shareContestEvent, contest)} className="icon-share"></i> */}

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
                        <div className="progress-bar-default display-table-cell v-mid" onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 3, event)}>
                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                            <div className="progress-bar-value" >
                                {/* <span className="user-joined">{parseFloat(contest.size) -  parseFloat(contest.total_user_joined)}
                                    {contest.is_tie_breaker == 1 && Constants.SELECTED_GAMET == Constants.GameType.DFS && ' ' + AppLabels.SPOTS_LEFT}
                                </span> */}
                                {
                                    <span className="total-entries">
                                        {contest.total_user_joined > 0 ? Utilities.numberWithCommas(parseFloat(contest.total_user_joined)) : 0} /
                                        <span>{' ' + Utilities.numberWithCommas(contest.size)}{" " + AppLabels.ENTRIES} </span>
                                        <span className="min-entries">min {contest.minimum_size}</span>
                                    </span>
                                }
                            </div>
                        </div>
                        <div className="display-table-cell v-mid position-relative entry-criteria">
                            <Button className={"white-base btnStyle btn-rounded" + (contest.currency_type == 2 ? ' coin-cont' : '')} bsStyle="primary"
                                onClick={(e) => this.CheckAadhaarValidate(e, contest, profileData, ContestDisabled)}>
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

                {contest.group_id === '7' &&
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
            </div>
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

    getPrizeAmount = (prize_data, status) => {
        let PrizeDisDetail = JSON.parse(prize_data.prize_distibution_detail)
        let prizeAmount = this.getWinCalculation(PrizeDisDetail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span style={{ color: status == 1 ? '#ffffff' : '' }} className={"contest-prizes"}>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div style={{ color: status == 1 ? '#ffffff' : '' }} className="contest-listing-prizes" ><i style={{ marginLeft: status == 1 ? 4 : '' }} className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ height: 15, width: 15, marginLeft: status == 1 ? 4 : '' }} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }

    handleTab = (tab, otherData) => {
        if (WSManager.loggedIn()) {
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('#')[0];
            }
            window.history.replaceState("", "", url + "#" + tab);
            this.setState({
                isLoading: false,
                activeContestTab: tab
            }, () => {
                if ((this.state.activeContestTab == 0 || this.state.activeContestTab == 1) && ls.get('showMyTeam')) {
                    ls.remove('showMyTeam')
                }
                this.setState({
                    isLoading: true,
                    isListLoading: false,
                    HeaderOption: {
                        back: true,
                        fixture: true,
                        filter: tab != 0 ? false : true,
                        title: '',
                        hideShadow: false,
                        isPrimary: DARK_THEME_ENABLE ? false : true,
                        goBackLobby: true
                        // goBackLobby: !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false
                    }
                })
                if (otherData && otherData.from === 'MyTeams') {
                    let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
                    let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
                    // if (otherData.lineupObj && otherData.lineupObj.length > 1) {
                    //     this.setState({ lineup_master_idArray: otherData.lineupObj })
                    // } else {
                    let lineupData = otherData.lineupObj[0] || ''
                    this.setState({ lineup_master_id: lineupData.user_team_id })

                    setTimeout(() => {
                        if ((tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true') || CinfirmPopUpIsAddFundsClicked == true) {
                            setTimeout(() => {
                                this.callAfterAddFundPopup()
                            }, 200);
                        }
                    }, 500);
                    // }
                } else {
                    this.setState({ lineup_master_idArray: [] })
                }
            })
        } else {
            this.goToSignup()
        }
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


    render() {
        var {
            showContestDetail,
            showConfirmationPopUp,
            userTeamListSend,
            LobyyData,
            showSharContestModal,
            activeTab,
            showLoadMore,
            showThankYouModal,
            hasMore,
            LoaderShow,
            showCollectionInfo,
            isFilterApplied,
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
            showOppData,
            lineup_master_idArray
        } = this.state;

        const FitlerOptions = {
            showContestListFitler: showContestListFitler,
            entry_fee_from: entry_fee_from,
            entry_fee_to: entry_fee_to,
            participants_from: participants_from,
            participants_to: participants_to,
            prizepool_from: prizepool_from,
            prizepool_to: prizepool_to
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
        var tabL = this.state.windowWidth / ContestTabList.length;
        if (LobyyData && LobyyData.game_starts_in) {
            LobyyData['game_starts_in'] = JSON.parse(LobyyData.game_starts_in)
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container contest-listing-web-conatiner header-margin minus-header-height bg-white contest-listing-new ML-contest-listing pf-contest-list " + (Constants.DARK_THEME_ENABLE ? ' DT-tranparent' : '')}>
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
                            showLobbyFitlers={this.showFilter} />
                        <Filter
                            {...this.props}
                            FitlerOptions={FitlerOptions}
                            hideFilter={this.hideFilter}
                            filterContestList={this.filterContestList}></Filter>

                        <div style={LobyyData.ldb == '1' ? { marginTop: 84 } : {}} className={"webcontainer-inner"}>

                            <div className={"primary-overlay" + (LobyyData.ldb == '1' ? ' ldb' : '')}>
                                {
                                    LobyyData.ldb == '1' && <div className="ldb-strip primary" onClick={() => this.showLeaderboardModal()}>
                                        <i className="icon-leaderboard" /><span>{AppLabels.LEADERBOARD} {AppLabels.AVAILABLE}</span></div>
                                }
                            </div>
                            <div className="fantasy-rules-sec">
                                {
                                    <span className="text-uppercase">
                                        {AppLabels.PICKS_FANTASY}
                                    </span>
                                }

                                <a href
                                    onClick={() => this.openRulesModal()}
                                >
                                    <i className="icon-file"></i>
                                    {AppLabels.RULES}
                                </a>
                            </div>

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
                            <div className={"tab-group"}>
                                <ul>
                                    {
                                        ContestTabList && _Map(ContestTabList, (item, idx) => {
                                            if (activeContestTab == item.value) {
                                                activeSTIDx = idx;
                                            }
                                            // let itemMyContest = item.value==1 ? true :false;
                                            // let itemMyTeam = item.value==2 ? true :false;
                                            // style={{marginLeft: itemMyContest ? 0: itemMyTeam ? 10 :0}}
                                            return (
                                                <li key={item.value + idx} style={{ width: 'calc(100% / ' + ContestTabList.length + ')' }} className={activeContestTab == item.value ? 'active' : ''} onClick={() => this.handleTab(item.value)}>
                                                    <a href>{item.label} {item.value != 0 && item.value != 3 && <span>({item.value == 1 ? myContestCount : item.value == 2 && myTeamCount})</span>}
                                                    </a>
                                                </li>
                                            )
                                        })
                                    }
                                    <span style={{ width: tabL > 125 ? 125 : 'calc(100% / ' + ContestTabList.length + ')', left: 'calc(' + (100 / ContestTabList.length * activeSTIDx) + '%' + (tabL > 125 ? (' + ' + ((tabL - 125) / 2) + 'px)') : ')') }} className="active-nav-indicator con-list"></span>
                                </ul>
                            </div>
                            {console.log('first contest listing ',this.state.LobyyData)}
                            {
                                isLoading && activeContestTab == 2 ?
                                    <PFMyTeams LobyyData={this.state.LobyyData}
                                        // history={this.props.history} 
                                        myTeamCount={myTeamCount}
                                        handleTab={this.handleTab} {...this.props} />
                                    :
                                    isLoading && activeContestTab == 1 ?
                                        <PFMyContestList handleTab={this.handleTab} LobyyData={this.state.LobyyData} ContestDetailShow={this.ContestDetailShow} check={this.check} shareContest={this.shareContest.bind(this)} {...this.props} showTeam={this.showTeam.bind(this)} />
                                        :
                                        isLoading &&
                                        <Row>
                                            <Col sm={12}>
                                                {
                                                    WSManager.loggedIn() && Utilities.getMasterData().private_contest == '2' &&
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
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
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
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
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
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
                                                                                <span className="text-uppercase">{AppLabels.CREATE_PRIVATE_CONTEST}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </ReactSlickSlider></Suspense>
                                                            </div>
                                                        }
                                                    </React.Fragment>
                                                }
                                                <InfiniteScroll
                                                    dataLength={FixturedContest.length}
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
                                                            return (
                                                                <div className="contest-list-wrapper xmt20 mb20" key={index} >
                                                                    {this.renderContestView({ index: index, contest: contest, isPinned: true })}
                                                                </div>
                                                            )
                                                        })
                                                    }

                                                    {_Map(FixturedContest, (group, idx) => {
                                                        var arrGroupList = [];
                                                        if (group.contest_list.length > 2 && group.isReadMore == undefined) {
                                                            arrGroupList.push(group.contest_list[0]);
                                                            arrGroupList.push(group.contest_list[1]);
                                                        } else {
                                                            arrGroupList = group.contest_list;
                                                        }
                                                        return (
                                                            <div className="contest-list-wrapper xmt20 mb20" key={idx}>
                                                                <div className={"contest-listing-card" + (showLoadMore && group.total > 2 ? ' more-contest-card' : '')}>
                                                                    <div className="contest-listing-card-header">
                                                                        <img src={Images.S3_BUCKET_IMG_PATH + group.icon} alt="" className={`contest-img ${group.group_id == 6 ? ' free-contest-img' : ''}`} />
                                                                        <div className="contest-name-heading">
                                                                            {group.group_name}
                                                                        </div>
                                                                        <div className="contest-name-heading-description">{group.description}</div>
                                                                    </div>
                                                                    {
                                                                        _Map(arrGroupList, (contest, index) => {
                                                                            return (
                                                                                <div key={index} >
                                                                                    {this.renderContestView({ index: index, contest: contest, isGroup: true })}
                                                                                </div>
                                                                            )
                                                                        })
                                                                    }

                                                                    {group.total > 2 && group.isReadMore == undefined &&
                                                                        <div className="text-center show-more-contest" onClick={() => this.showDetail(group, idx)}>
                                                                            {AppLabels.MORE_CONTEST}<i className="icon-arrow-down"></i>
                                                                        </div>
                                                                    }

                                                                    {group.total > 2 && group.isReadMore == true &&
                                                                        <div className="text-center show-more-contest" onClick={() => this.hideDetail(group, idx)}>
                                                                            {AppLabels.LESS_CONTEST}<i className="icon-arrow-up"></i>
                                                                        </div>
                                                                    }

                                                                </div>
                                                            </div>
                                                        );
                                                    })
                                                    }
                                                    {
                                                        ((FixturedContest.length == 0 && FixturedPinContest.length == 0)) && !isListLoading &&
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
                                            </Col>
                                        </Row>
                            }
                        </div>
                        {
                            WSManager.loggedIn() && isLoading &&
                            <React.Fragment>
                                {
                                    activeContestTab == 0 &&
                                    userTeamListSend.length < parseInt(Utilities.getMasterData().a_teams) &&
                                    <Button onClick={() => this.redirectToMyTeams()} className="btn-block btn-primary bottom">
                                        {AppLabels.CREATE_YOUR_PICKS}
                                    </Button>
                                }

                            </React.Fragment>
                        }

                        {
                            showContestDetail &&
                            <ContestDetailModal
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                OpenContestDetailFor={this.state.FixtureData}
                                activeTabIndex={activeTab}
                                LobyyData={this.state.LobyyData}
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
                        {showRulesModal &&
                            <PFRulesScoringModal MShow={showRulesModal} MHide={this.hideRulesModal} />
                        }
                        {showTeamModal &&
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
                            showUJC &&
                            <UnableJoinContest
                                showM={showUJC}
                                hideM={this.hideUJC}
                            />
                        }


                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}