import React, { lazy, Suspense } from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, _isEmpty, _debounce, _filter } from '../../Utilities/Utilities';
import { LSFUpcomingContest, LSFCompletedContest, LSFLiveContest } from '.';
import {StockTeamPreview} from "../StockFantasy";
import { my_contest_config } from '../../JsonFiles';
import { stockJoinContest, getSPUserLineupList, getLSFUserContestByStatus ,getStockLobbySetting} from '../../WSHelper/WSCallings';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import ContestDetailModal from '../../Modals/ContestDetail';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import queryString from 'query-string';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../CustomComponent';
import UnableJoinContest from '../../Modals/UnableJoinContest';
const LSFRules = lazy(() => import('./LSFRules'));
const StockPlayerCard = lazy(() => import('../StockFantasy/StockPlayerCard'));
const MyAlert = lazy(() => import('../../Modals/MyAlert'));

/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class LSFMyContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoaderShow: false,
            showTeamView: false,
            selectedTab: (this.props.location && this.props.location.state) ? (this.props.location.state.from == 'notification' || this.props.location.state.from == 'lobby-completed' ? Constants.CONTEST_COMPLETED : this.props.location.state.from == 'lobby-live' ? Constants.CONTEST_LIVE : Constants.CONTEST_UPCOMING) : Constants.CONTEST_UPCOMING,
            collectionMasterId: (this.props.location && this.props.location.state) ? (this.props.location.state.collectionMasterId ? this.props.location.state.collectionMasterId : '') : '',
            liveContestList: [],
            upcomingContestList: [],
            ShimmerList: [1, 2, 3, 4, 5],
            completedContestList: [],
            showContestDetail: false,
            FixtureData: '',
            FixtureContestData: '',
            userTeamListSend: [],
            TotalTeam: [],
            showThankYouModal: false,
            LobyyData: '',
            ConfirmationIsFrom: '',
            lineupArr: [],
            showUJC: false,
            rootitem: [],
            showPreview: false,
            showRules: false,
            activeTab: 1,
            showPlayerCard: false,
            playerDetails: {},
            showTimeOutAlert: false
        }
    }

    componentDidMount() {
        Utilities.handleAppBackManage('my-contest')
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);

        let contest = urlParams.contest;
        if (contest in my_contest_config.contest_url) {
            this.setState({ selectedTab: my_contest_config.contest_url[contest] }, () => {
                this.getMyCollectionsList(this.state.selectedTab)
            })
        }
        else {
            if (contest in my_contest_config.contest) {
                this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[contest])
            }
            else {
                this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[this.state.selectedTab])
            }
            this.getMyCollectionsList(this.state.selectedTab)
        }
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

    /**
     * @description Call this function when you want to go fo lobby screen
    */
    goToLobby = () => {
        this.props.history.push({ pathname: '/' })
    }

    /**
     * @description This function is called from Thankyou Modal
     * @see Thankyou Modal
    */
    seeMyContest = () => {
        this.setState({ showThankYouModal: false }, () => {
            this.getMyCollectionsList(this.state.selectedTab)
        })
    }

    /**
     * @description This function is called to get user lineup from server
     * @param event user click event
     * @param CollectionData Root item or Fixture item of Fixture list
     * @param childItem Contest list item comes in Fixture item
     * @param teamItem Team list item comes in Contest item
     * @param showPopup bollean value to display confirmation popup
    */
    getUserLineUpListApi = (event, CollectionData, childItem, teamItem, showPopup, checkForTimeOut) => {
        if (event != null) {
            event.stopPropagation();
        }
        if(checkForTimeOut && !Utilities.minuteDiffValueStock({ date: CollectionData.game_starts_in },-5)){
            this.setState({
                showTimeOutAlert:true
            })
            return;
        }
        else{
            let param = {
                "collection_id": CollectionData.collection_id,
            }
    
            this.setState({ isLoaderShow: true })
            getSPUserLineupList(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    let data = responseJson.data || []
                    this.setState({
                        TotalTeam: data,
                        userTeamListSend: data
                    })
                }
                if (responseJson && responseJson.data && responseJson.data.length > 0) {
                    let tempList = [];
                    let tList = responseJson.data
                    tList.map((data, key) => {
                        tempList.push({ value: data, label: data.team_name })
                        return '';
                    })
    
                    this.setState({ userTeamListSend: tempList, showConfirmationPopUp: showPopup, FixtureData: CollectionData, FixtureContestData: childItem });
                }
                else {
                    this.joinContest(CollectionData, childItem, teamItem)
                }
                if (!showPopup) {
                    this.setState({
                        FixtureData: childItem,
                        showContestDetail: true,
                    });
                }
            })            
        }
    }

    showTimeOutModal=()=>{
        this.setState({
            showTimeOutAlert: true
        })
    }

    hideTimeOutModal=()=>{
        this.setState({
            showTimeOutAlert: false
        })
    }


    /**
     * @description call this to display confirmation popup
     * @param data unused here
     * @see ConfirmationPopup
    */
    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }

    /**
     * @description call this to hide confirmation popup
     * @param data unused here
     * @see ConfirmationPopup
    */
    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    /**
     * @description This function is responsible to call lineup class with formated url data 
     * @param dataFromConfirmFixture Contest list item
     * @param dataFromConfirmLobby Fixture list item
     * @see ConfirmationPopup
    */
    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        let dateformaturl = Utilities.getUtcToLocal(dataFromConfirmFixture.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        lineupPath = '/stock-prediction/lineup/' + dataFromConfirmFixture.contest_id + '-' + dateformaturl + "?tab=1"

        WSManager.clearLineup();
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: dataFromConfirmFixture,
                LobyyData: dataFromConfirmFixture,
                resetIndex: 1,
                // collection_master_id: dataFromConfirmFixture.collection_master_id
            }
        })
    }

    /**
     * @description This function is responsible to call lineup class with formated url data 
     * @param dataFromConfirmPopUp state of Confirmatio popup
     * @see ConfirmationPopup
    */
    ConfirmEvent = (dataFromConfirmPopUp) => {
        if( Constants.SELECTED_GAMET == Constants.GameType.StockPredict && !Utilities.minuteDiffValueStock({ date: dataFromConfirmPopUp.FixturedContestItem.game_starts_in },-5)){
            this.ConfirmatioPopUpHide();
            this.showTimeOutModal();
        }
        else{
            if (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
                Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
            } else {
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
                            WSManager.setPaymentCalledFrom("mycontest")
                            this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'mycontest', isStockF: true ,isStockPF: true} });
                        }
                        else {
                            this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true,isStockPF: true } })
                        }
                    }
                    else {
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("mycontest")
                        this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isStockF: true,isStockPF: true });
                    }
    
                }
            }
        }
    }

    /**
     * @description This function is responsible to call Join contest API 
     * @param dataFromConfirmPopUp state of Confirmatio popup
     * @see ConfirmationPopup
    */
    CallJoinGameApi(dataFromConfirmPopUp) {
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        this.setState({ isLoaderShow: true })

        stockJoinContest(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })
    }

    /**
     * @description This function is used to display Thank you popup
     * @param data unused here
     * @see Thankyou
    */
    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    /**
     * @description This function is used to hide Thank you popup
     * @param data unused here
     * @see Thankyou
    */
    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = _debounce((selectedTab) => {
        window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[selectedTab]);
        this.setState({ selectedTab: selectedTab }, () => {
            this.getMyCollectionsList(this.state.selectedTab)
        });
    }, 300)

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyCollectionsList = async (status) => {
        var param = {
            "status": status,
        }
        this.setState({ isLoaderShow: true })
        var responseJson = await getLSFUserContestByStatus(param);
        this.setState({ isLoaderShow: false })
        if (responseJson && responseJson.response_code == WSC.successCode) {
            switch (this.state.selectedTab) {
                case Constants.CONTEST_UPCOMING:
                    this.setState({ upcomingContestList: responseJson.data })
                    break;
                case Constants.CONTEST_LIVE:
                    this.setState({ liveContestList: responseJson.data })
                    break;
                case Constants.CONTEST_COMPLETED:
                    this.setState({ completedContestList: responseJson.data })
                    break;
                default:
                    this.setState({ upcomingContestList: responseJson.data })
            }
        }

    }

    /**
     * @description This function is responsible to open leaderboard page for selected contest
     * @param e click event
     * @param childItem Contest list item
     * @param rootItem Fixture list item
     * @see Leaderboard
     */
    openLeaderboard = (e, childItem, rootItem) => {
        if (e) {
            e.stopPropagation()
        }
        this.props.history.push({
            pathname: '/' + (rootItem.collection_id || childItem.collection_master_id) + '/leaderboard',
            state: {
                rootItem: rootItem,
                contestItem: childItem,
                status: this.state.selectedTab,
                isStockF: true,
                isStockPF:true
            }

        })
    }
    goToChatMyContest = (e, contest_unique_id, childItem) => {
        if (e) {
            e.stopPropagation()
        }

        this.props.history.push({ pathname: '/group-chat/'+contest_unique_id, state: { contest_unique_id: contest_unique_id, childItem: childItem }})

    }

    /**
     * @description This function opens lineup page with formated data to join contest
     * @param data Fixture (root) data
     * @see ContestDetailModal
     */
    onSubmitBtnClick = (data) => {
        // let category_id = ''
        // if (data.category_id) {
            data['collection_master_id'] = data.collection_id;
        //     category_id = data.category_id;
        // } else {
        //     category_id = this.state.FixtureData.category_id;
        // }
        // if (!data.collection_master_id) {
        //     data['collection_master_id'] = this.state.FixtureData.collection_id;
        // }
        if(!Utilities.minuteDiffValueStock({ date: data.game_starts_in },-5)){
            this.ContestDetailHide();
            this.showTimeOutModal();
        }
        else{
            if (this.state.userTeamListSend != null && !_isUndefined(this.state.userTeamListSend) && this.state.userTeamListSend.length > 0) {
    
                this.ContestDetailHide();
                setTimeout(() => {
                    this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData, ConfirmationIsFrom: 'contestdetail' })
                }, 200);
            } else {
                let dateformaturl = Utilities.getUtcToLocal(data.scheduled_date);
                dateformaturl = new Date(dateformaturl);
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
                let lineupPath = ''
                lineupPath = '/stock-prediction/lineup/' + data.contest_id + '-' + dateformaturl + "?tab=1"
        
                WSManager.clearLineup();
                this.props.history.push({
                    pathname: lineupPath.toLowerCase(), state: {
                        FixturedContest: data,
                        LobyyData: data,
                        resetIndex: 1,
                        from: "contestJoin"
                    }
                })
            }
        }
    }

    /**
     * @description This function opens a detailed page for contest on modal
     * @param data contest item
     * @see ContestDetailModal
     */
    ContestDetailShow = (data, activetab,event) => {
        if ((parseInt(data.user_joined_count) < parseInt(data.multiple_lineup)) && (parseInt(data.size) > parseInt(data.total_user_joined))) {

            this.setState({
                FixtureData: data,
                showContestDetail: true,
                LobyyData: data
            }, () => {
                if (this.state.selectedTab == Constants.CONTEST_UPCOMING) {
                    this.getUserLineUpListApi(null, data, data, "teamItem", false)
                }
            });
        }
        else {
            this.setState({
                FixtureData: data,
                showContestDetail: true,
                LobyyData: data,
                activeTab: activetab,
            });
        }
    }

    /**
     * @description This function hides contest detail
     * @see ContestDetailModal
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    /**
     * @description This function responsible to open lineup screen for team with url formated data
     * @param rootitem Fixture Item
     * @param contestItem Contest Item
     * @param teamitem Team Item
     * @param isEdit is lineup editable or not
     * @param isFromtab from which the lineup is called
     * @see FieldView
     */
    openLineup = (rootitem, contestItem, teamitem, isEdit, isFromtab, sideView) => {
        this.setState({
            rootitem: rootitem
        })

        ls.set('Lineup_data', this.state.lineupArr);

        if (isEdit == false) {
            this.setState({
                showPreview: true,
                teamitem: teamitem,
                rootitem: rootitem
            })
        }
        else {

            let dateformaturl = Utilities.getUtcToLocal(contestItem.scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            let lineupPath = ''
            lineupPath = '/live-stock-fantasy/lineup/' + contestItem.contest_id + '-' + dateformaturl + "?tab=1"

            // let myTeam = {}
            // if (isFromMyTeam) {
            //     myTeam = { from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams" }
            // }

            this.props.history.push({
                // pathname: lineupPath.toLowerCase(), state: {
                //     SelectedLineup: this.state.lineupArr,
                //     MasterData: this.state.MasterData,
                //     FixturedContest: contestItem,
                //     team: this.state.TeamMyContestData,
                //     LobyyData: contestItem,
                //     resetIndex: 1,
                //     teamitem: teamitem,
                //     rootDataItem: rootitem,
                //     from: 'editView',
                //     isFromMyTeams: true,
                //     collection_master_id: contestItem.collection_id
                // }
                pathname: lineupPath.toLowerCase(), state: {
                    FixturedContest: contestItem,
                    LobyyData: contestItem,
                    teamitem: teamitem,
                    resetIndex: 1,
                    isMyContest: true
                    // ...myTeam
                }
            })
        }
    }

    /**
     * @description This function is responsible to remove item from list
     * @param status Selected Tab
     * @param index index of item to remove from list
     */
    removeFromList = (status, index) => {
        let key = my_contest_config.tab_state_key[this.state.selectedTab];
        let list = this.state[key];
        list.splice(index, 1);
        this.setState({ [key]: list })
    }

    /**
     * @description This function is responsible to open lineup to join contest with formatted URL data
     * @param item Fixture item
     * @see FieldView
     */
    joinContest(rootitem, ContestItem, teamitem) {
        let dateformaturl = Utilities.getUtcToLocal(ContestItem.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = ''
        lineupPath = '/stock-prediction/lineup/' + ContestItem.contest_id + '-' + dateformaturl + "?tab=1"

        WSManager.clearLineup();
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: ContestItem,
                LobyyData: ContestItem,
                rootDataItem: rootitem,
                team: teamitem,
                from: "contestJoin"
            }
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

    PlayerCardShow = (e, item) => {
        e.stopPropagation();
        item.collection_master_id = this.state.collectionMasterId;
        this.setState({
            playerDetails: item,
            showPlayerCard: true
        });
    }

    PlayerCardHide = () => {
        this.setState({
            showPlayerCard: false,
            playerDetails: {}
        });
    }

    /**
     * @description This function render all UI components. It is the React lifecycle methods that called after @see componentWillMount()
     * @return UI Components
    */
    render() {
        const {
            showConfirmationPopUp,
            showThankYouModal,
            showUJC,
            TotalTeam,
            showPreview,
            selectedTab, 
            showRules ,
            activeTab, 
            showPlayerCard,
            playerDetails,
            showTimeOutAlert
        } = this.state;

        let MESSAGE_1 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.NO_UPCOMING_CONTEST1
            :
            this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.NO_LIVE_CONTEST1
                :
                AppLabels.NO_COMPLETED_CONTEST1

        let MESSAGE_2 = this.state.selectedTab == Constants.CONTEST_UPCOMING ?
            AppLabels.NO_UPCOMING_CONTEST2
            :
            this.state.selectedTab == Constants.CONTEST_LIVE ?
                AppLabels.NO_LIVE_CONTEST2
                :
                AppLabels.NO_COMPLETED_CONTEST2

        let HeaderOption = {
            title: AppLabels.MY_CONTEST,
            notification: true,
            hideShadow: true,
            back: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true

        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div style={{minHeight: '89vh'}} className="web-container my-contest-style web-container-fixed sp-my-contest lsf-mycontest">
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className={"tabs-primary " + (!this.props.hideHeader ? ' mt50' : '')}>
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => console.log('clicked')} defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab pb-3" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span><span className="live-indicator"></span> {AppLabels.LIVE} </span></NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col style={{ marginTop: this.state.selectedTab == Constants.CONTEST_COMPLETED ? -5 : -5 }} className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                                <LSFLiveContest {...this.props} liveContestList={this.state.liveContestList} 
                                                ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} 
                                                goToChatMyContest={this.goToChatMyContest} selectedTab={selectedTab} 
                                                openLineup={this.openLineup}
                                                />

                                                {
                                                    this.state.liveContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.liveContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>

                                                <LSFUpcomingContest {...this.props} collectionMasterId={this.state.collectionMasterId} upcomingContestList={this.state.upcomingContestList} removeFromList={this.removeFromList}
                                                    ContestDetailShow={this.ContestDetailShow} getUserLineUpListApi={this.getUserLineUpListApi}
                                                    openLineup={this.openLineup}
                                                    goToChatMyContest={this.goToChatMyContest} showRulesModal={this.showRulesModal} />
                                                {
                                                    this.state.upcomingContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.upcomingContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                                                <LSFCompletedContest  {...this.props} collectionMasterId={this.state.collectionMasterId} completedContestList={this.state.completedContestList} ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} />


                                                {
                                                    this.state.completedContestList.length == 0 && !this.state.isLoaderShow &&
                                                    <NoDataView
                                                        BG_IMAGE={Images.no_data_bg_image}
                                                        // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                        CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                        MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                        MESSAGE_2={''}
                                                        BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                        onClick={this.goToLobby}
                                                    />
                                                }

                                                {
                                                    this.state.completedContestList.length == 0 && this.state.isLoaderShow &&
                                                    this.state.ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} />
                                                        )
                                                    })
                                                }

                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>

                            {this.state.showContestDetail &&
                                <ContestDetailModal isStockF={true} {...this.props} contestStatus={this.state.selectedTab} IsContestDetailShow={this.state.showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={this.state.FixtureData} LobyyData={this.state.LobyyData} isStockPF={true} 
                                activeTabIndex={activeTab} />
                            }

                            {showConfirmationPopUp &&
                                <ConfirmationPopup isStockF={true} IsConfirmationPopupShow={showConfirmationPopUp} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={this.state.userTeamListSend} TotalTeam={TotalTeam} FixturedContest={this.state.FixtureContestData} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} lobbyDataToPopup={this.state.FixtureData} fromContestListingScreen={true} createdLineUp={''} isStockPF={true} />
                            }

                            {showThankYouModal &&
                                <Thankyou from={'MyContest'} ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isStock={true}/>
                            }
                            {
                                showPreview && <StockTeamPreview isFrom={this.state.selectedTab == Constants.CONTEST_UPCOMING ? 'preview' : 'point'} CollectionData={this.state.rootitem} openTeam={this.state.teamitem} isViewAllShown={showPreview} onViewAllHide={() => this.setState({ showPreview: false })} isTeamPrv={'true'} PlayerCardShow={this.PlayerCardShow} />
                            }
                            {
                                showUJC &&
                                <UnableJoinContest
                                    showM={showUJC}
                                    hideM={this.hideUJC}
                                />
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
                                showPlayerCard &&
                                <Suspense fallback={<div />} >
                                    <StockPlayerCard
                                        mShow={showPlayerCard}
                                        mHide={this.PlayerCardHide}
                                        playerData={playerDetails}
                                        IncZIndex={true} 
                                        isFCap={true}
                                        // buySellAction={this.buySellAction}
                                        // addToWatchList={this.addToWatchList} 
                                    />
                                </Suspense>
    
                            }
                            {
                                showTimeOutAlert &&
                                <MyAlert
                                    isMyAlertShow={showTimeOutAlert}
                                    hidemodal={() => this.hideTimeOutModal()}
                                    isFrom={'TimeOutAlert'}
                                    message={AppLabels.JOIN_BEFORE_5MIN}
                                />
                            }
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

/**
  * @description Display shimmer effects while loading list
  * @return UI components
*/
const Shimmer = ({ idx }) => {
    return (
        <SkeletonTheme color={Constants.DARK_THEME_ENABLE ? "#161920" : null} highlightColor={Constants.DARK_THEME_ENABLE ? "#0E2739" : null}>
            <div key={idx} className="contest-list m border shadow-none shimmer-border">
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
                        <div className="progress-bar-default w-100">
                            <Skeleton height={6} />
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={60} />
                                <Skeleton height={4} width={60} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}