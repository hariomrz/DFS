import React, { lazy, Suspense } from 'react';
import { Row, Col, Button, ProgressBar, OverlayTrigger, Tooltip, Alert } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map, _isUndefined, _filter, _cloneDeep, parseURLDate, checkBanState } from '../../Utilities/Utilities';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import { getStockFixtureDetail, getStockContestList, getStockUserAllTeams, stockJoinContest, getStockContestTeamCount, joinStockContestWithMultiTeam, getUserAadharDetail, getStockContestByStatus } from "../../WSHelper/WSCallings";
import InfiniteScroll from 'react-infinite-scroll-component';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { Thankyou, ContestDetailModal, ConfirmationPopup, UnableJoinContest, ShareContestModal, ShowMyAllTeams,EquityContestDetails } from '../../Modals';
import { NoDataView } from '../../Component/CustomComponent';
import { createBrowserHistory } from 'history';
import * as Constants from "../../helper/Constants";
import StockMyTeams from '../StockFantasy/StockMyTeams';
import StockMyContestList from "../StockFantasy/StockMyContestList";
import { DownloadAppBuyCoinModal } from "../../Modals";
import Filter from "../../components/filter";
import MyAlert from '../../Modals/MyAlert';
import StockEquityFRules from './StockEquityFRules';
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

class StockContestListingEquity extends React.Component {
    constructor(props) {
        super(props);
        const lData = !_isUndefined(props.location.state) ? props.location.state.LobyyData : '';
        var myTeamlbl = AL.MYTEAMS.replace(AL.Teams, AL.PORTFOLIOS);
        this.state = {
            lineup_master_id: '',
            lineup_master_idArray: [],
            FixturedContest: [],
            ShimmerList: [1, 2, 3, 4, 5],
            showContestDetail: false,
            FixtureData: '',
            FixturedPinContest: [],
            TeamList: [],
            TotalTeam: [],
            LobyyData: lData || [],
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
            allContestData: [],
            activeContestTab: 0,
            isLoading: true,
            myContestCount: 0,
            myTeamCount: 0,
            showRulesModal: false,
            allTeamData: [],
            showTeamModal: false,
            windowWidth: window.innerWidth > 550 ? 540 : window.innerWidth,
            ContestTabList: [
                {
                    label: AL.ALL_CONTEST,
                    value: 0
                },
                {
                    label: AL.MY_CONTEST,
                    value: 1
                },
                {
                    label: myTeamlbl.replace(AL.TEAMSS, AL.PORTFOLIOS),
                    value: 2
                }
            ],
            showUJC: false,
            showDAM: false,
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                filter: true,
                title: '',
                hideShadow: false,
                goBackLobby: true ,//!_isUndefined(props.location.state) ? props.location.state.isFromPM : false,
                screentitle: lData ? (lData.collection_name && lData.collection_name != '' ? lData.collection_name : lData.category_id.toString() === "1" ? AL.DAILY : lData.category_id.toString() === "2" ? AL.WEEKLY : AL.MONTHLY) : '',
                leagueDate: {
                    scheduled_date: lData.scheduled_date || '',
                    end_date: lData.end_date || '' , //lData ? (lData.category_id.toString() === "1" ? '' : lData.end_date) : '',
                    game_starts_in: lData.game_starts_in || '',
                    catID: lData.category_id || ''
                },
                showleagueTime: true
            },
            stockSetting: [],
            aadharData: '',
            userJoinedContest: [],
            myContestListData:[],

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

    /**
     * @description lifecycle method of react,
     * method to load data of contest listing and user lineup list
     */
    componentDidMount() {
        Utilities.handleAppBackManage('contest-listing')
        let url = window.location.href;
        let showMyTeamTab = ls.get('showMyTeam') && ls.get('showMyTeam') == 1 ? true : false;
        if (url.includes('#')) {
            let tab = url.split('#')[1];
            url = url.split('#')[0];
            this.setState({
                activeContestTab: tab
            }, () => {
                this.setState({
                    HeaderOption: { ...this.state.HeaderOption, filter: tab != 0 ? false : true, 
                        goBackLobby: true //!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false 
                    }
                })
            })
        }
        else if(showMyTeamTab){
            this.setState({
                activeContestTab: 2
            }, () => {
                this.setState({
                    HeaderOption: { ...this.state.HeaderOption, filter: false, 
                        goBackLobby: true // !_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false 
                    }
                })
            })
        }
        if (!_isUndefined(this.props.location.state)) {
            this.props.history.replace({ pathname: (this.props.location.state.lineupPath + "#" + this.state.activeContestTab), state: this.props.location.state })
        }
        Utilities.scrollToTop()
        globalThis = this;
        const matchParam = this.props.match.params;
        if (parsed.sgmty) {
            let urlGT = atob(parsed.sgmty)
            WSManager.setPickedGameType(urlGT);
        }
        WSManager.setPickedGameType(Constants.GameType.StockFantasyEquity)


        this.FixtureDetail(matchParam);

        window.addEventListener('resize', (event) => {
            this.setState({
                windowWidth: window.innerWidth > 550 ? 540 : window.innerWidth,
            })
        });

        if (WSManager.loggedIn()) {
            this.getUserLineUpListApi(matchParam);
            WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'loggedInusers');
            if (Constants.BanStateEnabled && !WSManager.getProfile().master_state_id  && Utilities.getMasterData().a_aadhar != "1") {
                CustomHeader.showBanStateModal({ isFrom: 'CL' });
            }
            if (Utilities.getMasterData().a_aadhar == "1") {
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
        }
        this.FixtureContestList(matchParam);
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'stock_contestlist');
    }

    componentWillUnmount() {
        window.removeEventListener('resize', () => { });
    }

    /**
     * @description lifecycle method of react,
     * method to load locale storage data and props data
     */
    UNSAFE_componentWillMount() {
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
                lineup_master_idArray: this.props.location.state.lineupObj || []
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

    // callContestTeamCount(data) {
    //     let param = {
    //         "collection_id": data.collection_master_id
    //     }
    //     this.setState({ isLoading: false })
    //     let api = getStockContestTeamCount
    //     api(param).then((responseJson) => {
    //         this.setState({ isLoading: true })
    //         if (responseJson && responseJson.response_code == WSC.successCode) {
    //             let data = responseJson.data;
    //             this.setState({
    //                 myContestCount: data && data.contest_count ? data.contest_count : 0,
    //                 myTeamCount: data && data.team_count ? data.team_count : 0
    //             })
    //         }
    //     })
    // }

    aadharConfirmation = () => {
        Utilities.showToast(AL.VERIFICATION_PENDING_MSG, 3000);
        this.props.history.push({ pathname: '/aadhar-verification' })
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
        if (checkBanState(FixturedContestItem, CustomHeader)) {
            WSManager.clearLineup();
            if (this.state.TeamList.length > 0 || (teamListData && teamListData != null && teamListData.length > 0)) {
                this.setState({ showConfirmationPopUp: true, FixtureData: FixturedContestItem })
            }
            else {
                if (this.state.TotalTeam.length === parseInt(Utilities.getMasterData().a_teams)) {
                    this.openAlert()
                }
                else {
                    this.goToLineup(FixturedContestItem)
                }
            }
            WSManager.setFromConfirmPopupAddFunds(false);
        }
    }

    goToLineup = (FixturedContestItem, isFromMyTeam) => {
        if (!FixturedContestItem.collection_master_id && FixturedContestItem.collection_id) {
            FixturedContestItem['collection_master_id'] = FixturedContestItem.collection_id;
        } else if (!FixturedContestItem.collection_master_id) {
            FixturedContestItem['collection_master_id'] = this.state.LobyyData.collection_master_id;
        }
        let cat_id = FixturedContestItem.category_id || this.state.LobyyData.category_id || ''
        let name = cat_id.toString() === "1" ? 'Daily' : cat_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let lineupPath = '/stock-fantasy-equity/lineup/' + name;
        let myTeam = {}
        if (isFromMyTeam) {
            myTeam = { from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams" }
        }
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: FixturedContestItem,
                LobyyData: this.state.LobyyData,
                resetIndex: 1,
                ...myTeam
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
                Utilities.showToast(AL.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
            if (checkBanState(this.state.FixtureData, CustomHeader)) {
                if (this.state.TeamList != null && !_isUndefined(this.state.TeamList) && this.state.TeamList.length > 0) {
                    this.ContestDetailHide();
                    setTimeout(() => {
                        this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData })
                    }, 200);
                } else {
                    this.goToLineup(this.state.FixtureData)
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
        let param = {
            "collection_id": data.collection_master_id
        }
        this.setState({ isListLoading: true })
        getStockContestList(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data
                let GroupList = data.group
                let ContestList = data.contest
                let PinContestList = ContestList.filter(obj => obj.is_pin_contest == 1)
                let finalList = []
                _Map(GroupList,(group,indx)=>{
                    let tmp = ContestList.filter(item => item.group_id == group.group_id)
                    if(tmp.length > 0){
                        let CList = []
                        _Map(tmp,(titem,idx)=>{
                            if(Object.keys(data.user_data.contest).includes(titem.contest_id) && titem.multiple_lineup == data.user_data.contest[titem.contest_id]){}
                            else{
                                CList.push(titem)
                            }
                        })
                        group['contest_list']=CList
                        if(CList.length > 0){
                            finalList.push(group)
                        }
                    }
                })
                
                let userData = data && data.user_data
                let prevContestJoinedTC = this.state.userJoinedTeamCount
                let contest_length = 0
                _Map(data.user_data.contest, item => {
                    contest_length += Number(item)
                    return item
                })

                let allContestData = {
                    'total_contest': data.total_contest,
                    'contest': finalList
                }
                this.setState({
                    FixturedContest: finalList,
                    FixturedPinContest: PinContestList,
                    allContestData: allContestData,
                    userJoinedContest: userData && userData.contest ? userData.contest : [],
                    myContestCount: userData && userData.contest ? Object.keys(userData.contest).length : 0,
                    myTeamCount: userData && userData.team ? userData.team : 0,
                    userJoinedTeamCount: contest_length
                }, () => {
                    this.setState({
                        isListLoading: false
                    })
                    if (
                        // this.state.activeContestTab == 1 && 
                        this.state.myContestCount > 0 && (this.state.myContestCount != this.state.myContestListData.length || prevContestJoinedTC != this.state.userJoinedTeamCount)
                        ) {
                        this.getMyContest(data.collection_master_id)
                    }
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
        // if (WSManager.loggedIn()) {
        //     this.callContestTeamCount(data);
        // }
    }

    /**
     * @description method to get fixture detail
     */
    FixtureDetail = async (CollectionData) => {
        if (!this.state.LobyyData.scheduled_date) {
            let param = {
                "collection_id": CollectionData.collection_master_id,
            }
            let apiStatus = getStockFixtureDetail;
            var api_response_data = await apiStatus(param);
            if (api_response_data.response_code === WSC.successCode) {
                let url = window.location.href;
                let catID = 1;
                if (url.includes('week')) {
                    catID = 2
                } else if (url.includes('month')) {
                    catID = 3
                }
                api_response_data.data['category_id'] = catID
                let lData = api_response_data.data;
                if (lData.scheduled_date) {
                    lData['season_scheduled_date'] = lData.scheduled_date
                    lData['collection_master_id'] = lData.collection_id
                }
                if (_isUndefined(this.props.location.state)) {
                    this.setState({
                        LobyyData: lData
                    })
                }
                this.setState({
                    FixturedDetail: api_response_data.data,
                    HeaderOption: {
                        ...this.state.HeaderOption, screentitle: lData ? (lData.category_id.toString() === "1" ? AL.DAILY : lData.category_id.toString() === "2" ? AL.WEEKLY : AL.MONTHLY) : '',
                        leagueDate: {
                            scheduled_date: lData.scheduled_date || '',
                            end_date: lData ? (lData.category_id.toString() === "1" ? '' : lData.end_date) : '',
                            game_starts_in: lData.game_starts_in || ''
                        }
                    }
                })
            }
        }
    }

    getUserLineUpListApi = async (CollectionData) => {
        let param = {
            "collection_id": CollectionData.collection_master_id,
        }
        var api_response_data = await getStockUserAllTeams(param)
        if (api_response_data.response_code === WSC.successCode) {
            this.setState({
                TotalTeam: api_response_data.data,
                TeamList: api_response_data.data,
                userTeamListSend: api_response_data.data,
            }, () => {
                if (this.state.userTeamListSend) {
                    let tempList = [];
                    this.state.userTeamListSend.map((data, key) => {
                        tempList.push({ value: data, label: data.team_name })
                        return '';
                    })
                    this.setState({ userTeamListSend: tempList });
                }
            })

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
            Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
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
                    this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList', isStockF: true } });

                }
                else {
                    this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true } })
                }
            }

            else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("ContestListing")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd, isStockF: true }, isReverseF: this.state.showRF });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let ApiAction = stockJoinContest;
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }
        if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
            ApiAction = joinStockContestWithMultiTeam;
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
            this.goToLineup(dataFromConfirmFixture)
        }
    }

    /* Handle contest listing filters */
    hideFilter = () => {
        this.setState({ showContestListFitler: false })
    }

    showFilter = () => {
        Filter.reloadLobbyFilter()
        this.setState({ showContestListFitler: true })
    }

    filterConditions(filterObj, obj) {
        let eFee = parseInt(obj.entry_fee);
        let partic = parseInt(obj.total_user_joined);


        let prize_data = obj.prize_distibution_detail ? this.handleJson(obj.prize_distibution_detail) : this.handleJson(obj.prize_distribution_detail);
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

            let tmpArray = []
            _Map(cloneAllData.contest, (item, index) => {
                tmpArray = [...tmpArray, ...item.contest_list];
            })


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
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AL.WINNERS
            } else {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AL.WINNER;
            }
        }
    }

    redirectToMyTeams() {
        WSManager.clearLineup()
        this.goToLineup(this.state.LobyyData, true)
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
        let{ aadharData } = this.state;
        let { contest, isPinned, isGroup } = data;
        let sponserImage = data.contest.sponsor_logo && data.contest.sponsor_logo != null ? data.contest.sponsor_logo : 0
        let remainingJoinCount = (contest.size || 0) - (contest.total_user_joined || 0);
        let lineupAryLength = this.state.lineup_master_idArray.length;
        let user_join_count = parseInt(contest.user_joined_count || 0);
        let ContestDisabled = lineupAryLength > 1 ? (lineupAryLength > remainingJoinCount || ((lineupAryLength + user_join_count) > contest.multiple_lineup) || contest.multiple_lineup <= 1) : false;
        let user_data = ls.get('profile');
        return (
            <div className={"contest-list contest-listing-list " + (isGroup ? ' contest-card-body' : ' position-relative') + (isPinned ? ' pinned' : '')}>
                <div className={"contest-list-header" + (ContestDisabled ? ' disabled-contest-card' : '')} onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 2, event)}>


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
                                        <strong>{AL.MAX_TEAM_FOR_MULTI_ENTRY} {contest.multiple_lineup} {AL.MAX_MULTI_ENTRY_TEAM}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon multi-feat">
                                        {AL.MULTI}
                                    </span>
                                </OverlayTrigger>

                            }
                            {
                                contest.guaranteed_prize == 2 && parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) &&
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AL.GUARANTEED_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon gau-feat">
                                        {AL.GUARANTEED}
                                    </span>
                                </OverlayTrigger>

                            }
                            {
                                contest.is_confirmed == 1 && parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) &&
                                <OverlayTrigger trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className="tooltip-featured">
                                        <strong>{AL.CONFIRM_DESCRIPTION}</strong>
                                    </Tooltip>
                                }>
                                    <span className="featured-icon new-featured-icon conf-feat">
                                        {AL.CONFIRMED}
                                    </span>
                                </OverlayTrigger>

                            }
                        </div>


                        <h3 className="win-type">
                            {
                                contest.contest_title ?
                                    <span>{contest.contest_title}</span>
                                    :
                                    <React.Fragment>
                                        <span onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 1, event)}>
                                            <span className="prize-pool-text text-capitalize" >{AL.WIN} </span>

                                            <span>
                                                {this.getPrizeAmount(contest)}
                                            </span>
                                        </span>
                                    </React.Fragment>
                            }
                            {
                                <i onClick={(shareContestEvent) => (ContestDisabled) ? null : globalThis.shareContest(shareContestEvent, contest)} className="icon-share"></i>
                            }

                        </h3>
                        {

                            <div className="text-small-italic mt3x">
                                {Constants.OnlyCoinsFlow != 1 && (contest.max_bonus_allowed != '0') && <span onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 1, event)}>
                                    {AL.Use} {contest.max_bonus_allowed}{'% '}{AL.BONUS_CASH_CONTEST_LISTING} {(parseInt(contest.user_joined_count) > 0) ? '|' : ''}
                                </span>}
                                {
                                    this.state.activeContestTab == 1 &&
                                    <>
                                        {(parseInt(contest.user_joined_count) > 0) && <span>{' '}{AL.JOINED_WITH}{' '}<span className='team-name-style'>{contest.team_name} {(parseInt(contest.user_joined_count) > 1) ? (' + ' + ((parseInt(contest.user_joined_count) - 1))) + ' more' : ''}</span></span>}
                                    </>
                                }
                            </div>
                        }

                    </div>
                    <div className={"display-table" + (this.state.activeContestTab == 0 && Constants.OnlyCoinsFlow != 1 && contest.max_bonus_allowed == '0' ? ' top-btm-10px' : '')}>
                        <div className="progress-bar-default display-table-cell v-mid" onClick={(event) => (ContestDisabled) ? null : globalThis.ContestDetailShow(contest, 3, event)}>
                            <ProgressBar now={this.ShowProgressBar(contest.total_user_joined, contest.minimum_size)} className={parseInt(contest.total_user_joined) >= parseInt(contest.minimum_size) ? '' : 'danger-area'} />
                            <div className="progress-bar-value" >
                                <span className="user-joined">{Utilities.numberWithCommas(contest.total_user_joined)}</span><span className="total-entries"> / {Utilities.numberWithCommas(contest.size)} {AL.ENTRIES}</span>
                                <span className="min-entries">{AL.MIN} {Utilities.numberWithCommas(contest.minimum_size)}</span>
                            </div>
                        </div>
                        <div className="display-table-cell v-mid position-relative entry-criteria">
                            <Button className="white-base btnStyle btn-rounded" bsStyle="primary" 
                            
                            
                            onClick={Utilities.getMasterData().a_aadhar == 1 ?
                                (aadharData && aadharData.aadhar_status == "1") ?
                                    (event) => (ContestDisabled) ? null : globalThis.check(event, contest)
                                    :
                                    () => this.aadharConfirmation()
                                :
                                (event) => (ContestDisabled) ? null : globalThis.check(event, contest)}
                            
                            
                            >
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
                                    ) : AL.FREE
                                }
                            </Button>
                        </div>

                    </div>
                    {
                        (data.contest.sponsor_logo && data.contest.sponsor_link) &&
                        <div className="contest-card-footer height-sponsor-strip">
                            <div className="sponsor-logo-section">
                                {
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
                            <span className="box-text">{AL.PRIVATE_CONTEST}</span>
                        </div>
                        <div className='creator-info'>
                            <span className="box-text">{AL.YOU}</span>
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

    handleJson=(data)=>{
        try {
           return JSON.parse(data)
        } catch {
            return data
        }
    }

    getPrizeAmount = (prize_data) => {
        let prizeDetail = this.handleJson(prize_data.prize_distibution_detail)
        let prizeAmount = this.getWinCalculation(prizeDetail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes">
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className="contest-listing-prizes" ><i className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AL.PRIZES
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
            if (this.state.activeContestTab == 1 && this.state.myContestListData.length != this.state.myContestCount) {
                this.getMyContest()
            }
            if((this.state.activeContestTab == 0 || this.state.activeContestTab == 1) && ls.get('showMyTeam')){
                ls.remove('showMyTeam')
            }
            if (!_isUndefined(this.props.location.state)) {
                this.props.history.replace({ pathname: (this.props.location.state.lineupPath + "#" + tab), state: this.props.location.state })
            } else {
                window.history.replaceState("", "", url + "#" + tab);
            }
            this.setState({
                isLoading: false,
                activeContestTab: tab
            }, () => {
                this.setState({
                    isLoading: true,
                    isListLoading: false,
                    HeaderOption: { ...this.state.HeaderOption, filter: tab != 0 ? false : true, 
                        goBackLobby: true //!_isUndefined(this.props.location.state) ? this.props.location.state.isFromPM : false 
                    }
                })
                if (otherData && otherData.from === 'MyTeams') {
                    let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
                    let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
                    if (otherData.lineupObj && otherData.lineupObj.length > 1) {
                        this.setState({ lineup_master_idArray: otherData.lineupObj })
                    } else {
                        let lineupData = otherData.lineupObj && otherData.lineupObj.length === 1 ? otherData.lineupObj[0] : ''
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
    goToPrivateContest() {
        let hideBanner = ls.get('hide_banner');
        let mSports = this.state.LobyyData.collection_master_id;
        let cat_id = this.state.LobyyData.category_id || ''
        let name = cat_id.toString() === "1" ? 'daily' : cat_id.toString() === "2" ? 'weekly' : 'monthly';
        let contestListingPath = '/stock-fantasy-equity/' + mSports + '/' + name;
        if (hideBanner) {
            this.props.history.push({ pathname: contestListingPath + '/private-contest', state: { LobyyData: this.state.LobyyData, isStockF: true } });
        }
        else {
            ls.set('hide_banner', true);
            this.props.history.push({ pathname: contestListingPath + '/private-contest-banner', state: { LobyyData: this.state.LobyyData, isStockF: true } });
        }
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

    getMyContest = () => {
        let collection_master_id = '';
        if (this.props && this.props.LobyyData && this.props.LobyyData.collection_master_id) {
            collection_master_id = this.props.LobyyData.collection_master_id;
        }
        else {
            let url = window.location.href;
            if (url.includes('#')) {
                url = url.split('contest')[1];
                collection_master_id = url.split('/')[1];
            }
        }
        var param = {
            "status": 0,
            "collection_id": collection_master_id
        }
        this.setState({ isLoaderShow: true })
        getStockContestByStatus(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson && responseJson.response_code === WSC.successCode) {
                let data = responseJson.data;
                // let publicContest = data.filter(function (item) {
                //     return (parseInt(item.contest_access_type || '0') !== 1);
                // });
                // let privateContest = data.filter(function (item) {
                //     return (parseInt(item.contest_access_type || '0') === 1);
                // });
                // this.checkUnseen(privateContest)
                this.setState({
                    myContestListData: data,
                    // publicContestList: publicContest
                },()=>{
                    console.log('CL myContestListData',this.state.myContestListData)
                })
            }
        })
    }

    render() {
        const {
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
            isFilterApplied,
            FixturedContest,
            FixturedPinContest,
            HeaderOption,
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
            showAlert
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

        let FixtureData = this.getFixtureModalData();

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
                            HeaderOption={HeaderOption}
                            {...this.props}
                            showLobbyFitlers={this.showFilter} />

                        <Filter
                            {...this.props}
                            stock={true}
                            stock_collection={this.state.LobyyData.collection_id}
                            FitlerOptions={FitlerOptions}
                            hideFilter={this.hideFilter}
                            filterContestList={this.filterContestList} />

                        <div className="webcontainer-inner">
                            <div className="primary-overlay"></div>
                            <div className="fantasy-rules-sec">
                                <span className="text-uppercase">
                                    {AL.STOCK_EQUITY}
                                </span>
                                <a href
                                    onClick={() => this.openRulesModal()}
                                >
                                    <i className="icon-file"></i>
                                    {AL.SCORING_RULES}
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
                            <div className="tab-group">
                                <ul>
                                    {
                                        ContestTabList && _Map(ContestTabList, (item, idx) => {
                                            if (activeContestTab == item.value) {
                                                activeSTIDx = idx;
                                            }
                                            return (
                                                <li key={item.value + idx} style={{ width: 'calc(100% / ' + ContestTabList.length + ')' }} className={activeContestTab == item.value ? 'active' : ''} onClick={() => this.handleTab(item.value)}>
                                                    <a href>{item.label} {item.value != 0 && <span>({item.value == 1 ? myContestCount : myTeamCount})</span>}</a>
                                                </li>
                                            )
                                        })
                                    }
                                    <span style={{ width: tabL > 125 ? 125 : 'calc(100% / ' + ContestTabList.length + ')', left: 'calc(' + (100 / ContestTabList.length * activeSTIDx) + '%' + (tabL > 125 ? (' + ' + ((tabL - 125) / 2) + 'px)') : ')') }} className="active-nav-indicator con-list"></span>
                                </ul>
                            </div>
                            {
                                activeContestTab == 2 ?
                                    <StockMyTeams LobyyData={this.state.LobyyData} history={this.props.history} handleTab={this.handleTab} myTeamCount={myTeamCount} TotalTeam={TotalTeam} />
                                    :
                                    activeContestTab == 1 ?
                                        <StockMyContestList handleTab={this.handleTab} LobyyData={this.state.LobyyData} ContestDetailShow={this.ContestDetailShow} check={this.check} shareContest={this.shareContest.bind(this)} {...this.props} showTeam={this.showTeam.bind(this)} isUpdateJoin={this.state.showThankYouModal} myContestCount={myContestCount} myContestListData={this.state.myContestListData}  />
                                        :
                                        isLoading &&
                                        <Row>
                                            <Col sm={12}>
                                                {/* {
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
                                                                                {AL.CREATE}
                                                                            </div>
                                                                            <div className="slid-desc">
                                                                                {AL.SLIDER_DES1}
                                                                            </div>
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
                                                                                <span className="text-uppercase">{AL.CREATE_PRIVATE_CONTEST}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="slider-wrap">
                                                                        <div className="slider-inner-wrap slider-wrap-s">
                                                                            <div className="user-img">
                                                                                <img src={Images.USER_GROUP_IMG} alt="" />
                                                                            </div>
                                                                            <div className="slide-title">
                                                                                {AL.SHARE}
                                                                            </div>
                                                                            <div className="slid-desc">
                                                                                {AL.SLIDER_DES2}
                                                                            </div>
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
                                                                                <span className="text-uppercase">{AL.CREATE_PRIVATE_CONTEST}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="slider-wrap">
                                                                        <div className="slider-inner-wrap slider-wrap-t">
                                                                            <div className="user-img">
                                                                                <img src={Images.USER_GROUP_IMG} alt="" />
                                                                            </div>
                                                                            <div className="slide-title">
                                                                                {AL.Earn}
                                                                            </div>
                                                                            <div className="slid-desc">
                                                                                {AL.SLIDER_DES3}
                                                                            </div>
                                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small private-contest-btn">
                                                                                <span className="text-uppercase">{AL.CREATE_PRIVATE_CONTEST}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </ReactSlickSlider></Suspense>
                                                            </div>
                                                        }
                                                    </React.Fragment>
                                                }
                                                {WSManager.loggedIn() && Utilities.getMasterData().private_contest == '1' &&
                                                    <React.Fragment>
                                                        <div className="contest-btn-wrap">
                                                            <div onClick={() => this.goToPrivateContest()} className="btn btnStyle btn-rounded small">
                                                                <span className="text-uppercase">{AL.CREATE_PRIVATE_CONTEST}!</span>
                                                            </div>
                                                        </div>
                                                    </React.Fragment>
                                                } */}
                                                {/* {
                                                    (WSManager.loggedIn() && (Utilities.getMasterData().private_contest == '1' || Utilities.getMasterData().private_contest == '2')) &&
                                                    <div className="contest-header-sec mb-0">
                                                        {AL.OR_JOIN_OUR_PUBLIC_CONTESTS}
                                                    </div>
                                                } */}
                                                <InfiniteScroll
                                                    dataLength={FixturedContest.length}
                                                    pullDownToRefreshThreshold={300}
                                                    refreshFunction={!showContestDetail && this.handleRefresh}
                                                    pullDownToRefresh={false}
                                                    hasMore={hasMore}
                                                    loader={
                                                        LoaderShow == true &&
                                                        <h4 className='table-loader'>{AL.LOADING_MSG}</h4>
                                                    }
                                                    pullDownToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8595; {AL.PULL_DOWN_TO_REFRESH}</h3>
                                                    }
                                                    releaseToRefreshContent={
                                                        <h3 style={{ textAlign: 'center', fontSize: 14 }}>&#8593; {AL.RELEASE_TO_REFRESH}</h3>
                                                    }>

                                                    {
                                                        FixturedPinContest &&
                                                        _Map(FixturedPinContest, (contest, index) => {
                                                            if (contest.collection_id) {
                                                                contest['collection_master_id'] = contest.collection_id;
                                                                contest['season_scheduled_date'] = contest.scheduled_date;
                                                            }
                                                            return (
                                                                <div className="contest-list-wrapper mt20 mb20" key={index} >
                                                                    {this.renderContestView({ index: index, contest: contest, isPinned: true })}
                                                                </div>
                                                            )
                                                        })
                                                    }

                                                    {
                                                        _Map(FixturedContest, (group, idx) => {
                                                            group['total']= group.contest_list.length
                                                            var arrGroupList = [];
                                                            if (group.contest_list.length > 2 && group.isReadMore == undefined) {
                                                                arrGroupList.push(group.contest_list[0]);
                                                                arrGroupList.push(group.contest_list[1]);
                                                            } else {
                                                                arrGroupList = group.contest_list;
                                                            }

                                                            return (
                                                                <div className="contest-list-wrapper mt20 mb20" key={idx}>
                                                                    <div className={"contest-listing-card" + (showLoadMore && group.total > 2 ? ' more-contest-card' : '')}>
                                                                        <div className="contest-listing-card-header">
                                                                            <img src={Images.S3_BUCKET_IMG_PATH + group.icon} alt="" className="contest-img" />
                                                                            <div className="contest-name-heading">{group.group_name}</div>
                                                                            <div className="contest-name-heading-description">{group.description}</div>
                                                                        </div>
                                                                        {
                                                                            _Map(arrGroupList, (contest, index) => {
                                                                                if (contest.collection_id) {
                                                                                    contest['collection_master_id'] = contest.collection_id;
                                                                                    contest['season_scheduled_date'] = contest.scheduled_date;
                                                                                }
                                                                                return (
                                                                                    <div key={index} >
                                                                                        {this.renderContestView({ index: index, contest: contest, isGroup: true, })}
                                                                                    </div>
                                                                                )
                                                                            })
                                                                        }

                                                                        {group.total > 2 && group.isReadMore == undefined &&
                                                                            <div className="text-center show-more-contest" onClick={() => this.showDetail(group, idx)}>
                                                                                {AL.MORE_CONTEST}<i className="icon-arrow-down"></i>
                                                                            </div>
                                                                        }

                                                                        {group.total > 2 && group.isReadMore == true &&
                                                                            <div className="text-center show-more-contest" onClick={() => this.hideDetail(group, idx)}>
                                                                                {AL.LESS_CONTEST}<i className="icon-arrow-up"></i>
                                                                            </div>
                                                                        }
                                                                    </div>
                                                                </div>
                                                            );
                                                        })
                                                    }
                                                    {
                                                        FixturedContest.length === 0 && FixturedPinContest.length === 0 && !isListLoading &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={isFilterApplied ? AL.NO_RESULT_FOUND_FILTER_1 : AL.NO_FIXTURES_MSG1}
                                                            MESSAGE_2={isFilterApplied ? AL.NO_CONTEST_FOR_FILTER_2 : AL.NO_FIXTURES_MSG3}
                                                            BUTTON_TEXT={AL.GO_BACK_TO_LOBBY}
                                                            onClick={this.goBack}
                                                        />
                                                    }
                                                    {
                                                        FixturedContest.length === 0 && FixturedPinContest.length === 0 && isListLoading &&
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
                                    activeContestTab == 0 && userTeamListSend.length < parseInt(Utilities.getMasterData().a_teams) &&
                                    <Button onClick={() => this.redirectToMyTeams()} className="btn-block btn-primary bottom">{AL.CREATE_YOUR_TEAM.replace(AL.Team.toUpperCase(), AL.PORTFOLIO.toUpperCase())}</Button>
                                }
                            </React.Fragment>
                        }

                        {
                            showContestDetail &&
                            <EquityContestDetails
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                OpenContestDetailFor={this.state.FixtureData}
                                activeTabIndex={activeTab}
                                isStockF={true}
                                LobyyData={this.state.LobyyData} {...this.props}
                                profileShow={this.state.aadharData}
                                />
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
                                isStockF={true}
                            />
                        }

                        {
                            showSharContestModal &&
                            <ShareContestModal
                                IsShareContestModalShow={this.shareContestModalShow}
                                IsShareContestModalHide={this.shareContestModalHide}
                                isStockF={true}
                                FixturedContestItem={FixtureData} />
                        }

                        {
                            showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.goToLobby}
                                seeMyContestEvent={this.seeMyContest} 
                                isStock={true}
                            />
                        }
                        {
                            showDAM &&
                            <DownloadAppBuyCoinModal
                                hideM={this.hideDownloadApp}
                            />
                        }
                        {showRulesModal &&
                            <StockEquityFRules mShow={showRulesModal} mHide={this.hideRulesModal} 
                            // stockSetting={this.state.stockSetting} 
                            showPtsOnly={(activeContestTab == 1 || activeContestTab == 2) ? true : false} />
                        }
                        {showTeamModal &&
                            <ShowMyAllTeams show={showTeamModal} hide={this.hideTeam} data={this.state.allTeamData} />
                        }
                        {
                            showUJC &&
                            <UnableJoinContest
                                showM={showUJC}
                                hideM={this.hideUJC}
                            />
                        }
                        {
                            showAlert &&
                            <MyAlert
                                isMyAlertShow={showAlert}
                                hidemodal={() => this.hideAlert()}
                                isFrom={'contest-listing'}
                                message={(AL.YOU_CAN_CREATE_ONLY_10TEAMS || '').replace('10', Utilities.getMasterData().a_teams)}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
export default StockContestListingEquity;