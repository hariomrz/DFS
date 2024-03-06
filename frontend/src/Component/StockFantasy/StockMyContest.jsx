import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, _isEmpty, _debounce, _filter } from '../../Utilities/Utilities';
import { StockUpcomingContest, StockCompletedContest, StockLiveContest, StockTeamPreview } from '.';
import { my_contest_config } from '../../JsonFiles';
import { stockJoinContest, getStockUserAllTeams, getStockJoinedFixtureByStatus ,getStockLobbySetting} from '../../WSHelper/WSCallings';
import ls from 'local-storage';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import ContestDetailModal from '../../Modals/ContestDetail';
import ShareContestModal from '../../Modals/ShareContestModal';
import SwitchTeam from '../../Modals/SwitchTeamModal';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import queryString from 'query-string';
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../CustomComponent';
import UnableJoinContest from '../../Modals/UnableJoinContest';

/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class StockMyContest extends React.Component {
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
            showSharContestModal: false,
            showSwitchTeamModal: false,
            userTeamListSend: [],
            TotalTeam: [],
            showThankYouModal: false,
            LobyyData: '',
            ConfirmationIsFrom: '',
            lineupArr: [],
            showUJC: false,
            rootitem: [],
            showPreview: false,
            StockSettingValue: [],
            livePrview: false
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

        // if(Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity){
        //     if(Constants.StockSetting.length > 0){
        //         this.setState({
        //             StockSettingValue: Constants.StockSetting
        //         })
        //     }
        //     else{
        //         getStockLobbySetting().then((responseJson) => {
        //             Constants.setValue.setStockSettings(responseJson.data);
        //             this.setState({ StockSettingValue: responseJson.data })
        //         })
        //     }
        // }
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

    // UNSAFE_componentWillReceiveProps(nextProps) {
    //     console.log('====================================');
    //     console.log(JSON.stringify(nextProps));
    //     console.log('====================================');
    //     if (WSManager.loggedIn() && this.props.history.location.pathname == '/my-contests') {
    //         let url = this.props.location.search;
    //         let urlParams = queryString.parse(url);

    //         let contest = urlParams.contest;
    //         if (contest in my_contest_config.contest_url) {
    //             let tmpSelectedTab = my_contest_config.contest_url[contest];
    //             if (this.state.selectedTab != tmpSelectedTab) {

    //                 this.setState({ selectedTab: my_contest_config.contest_url[contest], }, () => {
    //                     this.getMyCollectionsList(this.state.selectedTab)
    //                 })
    //             }
    //         }
    //         else {
    //             if (contest in my_contest_config.contest) {
    //                 this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[contest])
    //             }
    //             else {
    //                 this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[this.state.selectedTab])
    //             }
    //         }
    //     }
    // }

    /**
     * @description Call this function when you want to go fo lobby screen
    */
    goToLobby = () => {
        // this.props.history.push({ pathname: '/' })
        console.log('FixtureData',this.state.FixtureData)
        let data = this.state.FixtureData
        data['collection_master_id'] = data.collection_id;
        let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let contestListingPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity ? '/stock-fantasy-equity/contest/' + data.collection_id + '/' + name : '/stock-fantasy/contest/' + data.collection_id + '/' + name;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(Constants.SELECTED_GAMET)
        this.props.history.push({ pathname: CLPath, state: { LobyyData: data, lineupPath: CLPath,isFromPM: true } })
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
    getUserLineUpListApi = (event, CollectionData, childItem, teamItem, showPopup) => {
        if (event != null) {
            event.stopPropagation();
        }
        let param = {
            "collection_id": CollectionData.collection_master_id,
        }

        this.setState({ isLoaderShow: true })
        getStockUserAllTeams(param).then((responseJson) => {
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

    /**
     * @description call this to display confirmation popup
     * @param data unused here
     * @see ConfirmationPopup
    */
    ConfirmatioPopUpShow = (data) => {
        console.log('FixtureData',this.state.FixtureData)
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
        WSManager.clearLineup();
        if (!dataFromConfirmFixture['collection_master_id'] && dataFromConfirmFixture.collection_id) {
            dataFromConfirmFixture['collection_master_id'] = dataFromConfirmFixture.collection_id;
        } else if (!dataFromConfirmFixture['collection_master_id']) {
            dataFromConfirmFixture['collection_master_id'] = this.state.LobyyData.collection_id || this.state.FixtureData.collection_id
        }
        let name = dataFromConfirmFixture.category_id.toString() === "1" ? 'Daily' : dataFromConfirmFixture.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let lineupPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ?  '/stock-fantasy/lineup/' + name : '/stock-fantasy-equity/lineup/' + name ;
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: dataFromConfirmFixture,
                LobyyData: this.state.LobyyData ? this.state.LobyyData : dataFromConfirmLobby,
                resetIndex: 1,
                collection_master_id: dataFromConfirmFixture.collection_master_id
            }
        })
    }

    /**
     * @description This function is responsible to call lineup class with formated url data 
     * @param dataFromConfirmPopUp state of Confirmatio popup
     * @see ConfirmationPopup
    */
    ConfirmEvent = (dataFromConfirmPopUp) => {
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
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'mycontest', isStockF: true } });
                    }
                    else {
                        this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: true } })
                    }
                }
                else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("mycontest")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isStockF: true });
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
                    console.log('deviceIds', deviceIds);
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
        if(Constants.SELECTED_GAMET == Constants.GameType.StockFantasyEquity){
            param['stock_type'] = 2
        }
        this.setState({ isLoaderShow: true })
        var responseJson = await getStockJoinedFixtureByStatus(param);
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
                isStockF: true
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
        let category_id = ''
        if (data.category_id) {
            data['collection_master_id'] = data.collection_id;
            category_id = data.category_id;
        } else {
            category_id = this.state.FixtureData.category_id;
        }
        if (!data.collection_master_id) {
            data['collection_master_id'] = this.state.FixtureData.collection_id;
        }
        if (this.state.userTeamListSend != null && !_isUndefined(this.state.userTeamListSend) && this.state.userTeamListSend.length > 0) {

            this.ContestDetailHide();
            setTimeout(() => {
                this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData, ConfirmationIsFrom: 'contestdetail' })
            }, 200);
        } else {

            WSManager.clearLineup();
            let name = category_id.toString() === "1" ? 'Daily' : category_id.toString() === "2" ? 'Weekly' : 'Monthly';
            let lineupPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ?  '/stock-fantasy/lineup/' + name : '/stock-fantasy-equity/lineup/' + name ;
            this.props.history.push({
                pathname: lineupPath.toLowerCase(), state: {
                    FixturedContest: this.state.FixtureData,
                    LobyyData: data,
                    resetIndex: 1,
                    from: "contestJoin"
                }
            })
        }
    }

    /**
     * @description This function opens a detailed page for contest on modal
     * @param data contest item
     * @see ContestDetailModal
     */
    ContestDetailShow = (childItem, data) => {
        if ((parseInt(childItem.user_joined_count) < parseInt(childItem.multiple_lineup)) && (parseInt(childItem.size) > parseInt(childItem.total_user_joined))) {

            this.setState({
                FixtureData: childItem,
                showContestDetail: true,
                LobyyData: data
            }, () => {
                if (this.state.selectedTab == Constants.CONTEST_UPCOMING) {
                    this.getUserLineUpListApi(null, childItem, childItem, "teamItem", false)
                }
            });
        }
        else {
            this.setState({
                FixtureData: childItem,
                showContestDetail: true,
                LobyyData: data
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
            rootitem: rootitem,
            livePrview: this.state.selectedTab == Constants.CONTEST_LIVE ? true : false
        })

        ls.set('Lineup_data', this.state.lineupArr);

        if (isEdit == false) {
            console.log('====================================');
            console.log('preview');
            console.log('====================================');
            this.setState({
                showPreview: true,
                teamitem: teamitem,
                rootitem: rootitem
            })
        }
        else {
            let name = rootitem.category_id.toString() === "1" ? 'Daily' : rootitem.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
            let lineupPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ?  '/stock-fantasy/lineup/' + name : '/stock-fantasy-equity/lineup/' + name ;
            this.props.history.push({
                pathname: lineupPath.toLowerCase(),
                state: {
                    SelectedLineup: this.state.lineupArr,
                    MasterData: this.state.MasterData,
                    LobyyData: _isEmpty(this.state.LobyyData) ? rootitem : this.state.LobyyData,
                    FixturedContest: this.state.myContestData,
                    team: this.state.TeamMyContestData,
                    from: 'editView',
                    rootDataItem: rootitem,
                    isFromMyTeams: true,
                    ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal,
                    resetIndex: 1,
                    teamitem: teamitem,
                    collection_master_id: contestItem.collection_master_id
                }
            });
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
    joinContest(rootitem, contestItem, teamitem) {
        let category_id = ''
        if (rootitem.category_id) {
            rootitem['collection_master_id'] = rootitem.collection_id;
            category_id = rootitem.category_id;
        } else {
            category_id = contestItem.category_id;
        }
        if (!rootitem.collection_master_id) {
            rootitem['collection_master_id'] = contestItem.collection_id;
        }

        WSManager.clearLineup();
        let name = category_id.toString() === "1" ? 'Daily' : category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let lineupPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ?  '/stock-fantasy/lineup/' + name : '/stock-fantasy-equity/lineup/' + name ;
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: contestItem,
                rootDataItem: rootitem,
                team: teamitem,
                from: "contestJoin"
            }
        })
    }

    /**
     * @description This function opens the Switch Team Modal
     * @param fixtureData Fixture item
     * @param contestData Contest item
     * @param teamData Team item
     * @see SwitchTeam
     */
    switchTeamModalShow = (fixtureData, contestData, teamData) => {
        this.setState({
            showSwitchTeamModal: true,
        }, () => {
            this.switchTeamRef.setData(fixtureData, contestData, teamData)
        });
    }

    /**
     * @description This function hides the Switch Team Modal
     * @param isSuccess Whether switch api respond success or not
     * @see SwitchTeam
     */
    switchTeamModalHide = (isSuccess) => {
        if (isSuccess) {
            this.getMyCollectionsList(this.state.selectedTab)
        }
        this.setState({
            showSwitchTeamModal: false,
        });
    }

    /**
     * @description This function shows the ShareContestModal
     * @param data not used
     * @see ShareContestModal
     */
    shareContestModalShow = (data) => {
        this.setState({
            showSharContestModal: true,
        });
    }

    /**
     * @description This function hides the ShareContestModal
     * @param data not used
     * @see ShareContestModal
     */
    shareContestModalHide = () => {
        this.setState({
            showSharContestModal: false,
        });
    }

    /**
     * @description This function overloaded @see shareContestModalShow() and shows the ShareContestModal
     * @param shareContestEvent Click event
     * @param FixturedContestItem Contest item
     * @see ShareContestModal
     */
    shareContest = (shareContestEvent, FixturedContestItem) => {
        shareContestEvent.stopPropagation();
        this.setState({ showSharContestModal: true, FixtureData: FixturedContestItem })
    }

    /**
     * @description This function render all UI components. It is the React lifecycle methods that called after @see componentWillMount()
     * @return UI Components
    */
    render() {
        const {
            showSharContestModal,
            showSwitchTeamModal,
            showConfirmationPopUp,
            showThankYouModal,
            showUJC,
            TotalTeam,
            showPreview,
            StockSettingValue,
            selectedTab
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
                    <div style={{minHeight: '89vh'}} className={"web-container my-contest-style web-container-fixed" + (this.state.selectedTab == Constants.CONTEST_COMPLETED ? '' : '')}>
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
                                    <Col style={{ marginTop: this.state.selectedTab == Constants.CONTEST_COMPLETED ? -5 : -15 }} className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                                {
                                                     this.state.selectedTab == Constants.CONTEST_LIVE &&
                                                    <>
                                                        <StockLiveContest {...this.props} liveContestList={this.state.liveContestList} ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} goToChatMyContest={this.goToChatMyContest} selectedTab={selectedTab} openLineup={this.openLineup} />

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
                                                    </>
                                                }
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>

                                                <StockUpcomingContest {...this.props} collectionMasterId={this.state.collectionMasterId} upcomingContestList={this.state.upcomingContestList} removeFromList={this.removeFromList}
                                                    ContestDetailShow={this.ContestDetailShow} getUserLineUpListApi={this.getUserLineUpListApi}
                                                    shareContest={this.shareContest} switchTeamModalShow={this.switchTeamModalShow} openLineup={this.openLineup}
                                                    goToChatMyContest={this.goToChatMyContest} />
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
                                                <StockCompletedContest  {...this.props} collectionMasterId={this.state.collectionMasterId} completedContestList={this.state.completedContestList} ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} />


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
                                <ContestDetailModal isStockF={true} {...this.props} contestStatus={this.state.selectedTab} IsContestDetailShow={this.state.showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={this.state.FixtureData} LobyyData={this.state.LobyyData} />
                            }

                            {showSharContestModal &&
                                <ShareContestModal isStockF={true} IsShareContestModalShow={this.shareContestModalShow} IsShareContestModalHide={this.shareContestModalHide} FixturedContestItem={this.state.FixtureData} />
                            }

                            {showSwitchTeamModal &&
                                <SwitchTeam isStockF={true} ref={ref => this.switchTeamRef = ref} mHistory={this.props.history} IsSwitchTeamModalShow={this.switchTeamModalShow} IsSwitchTeamModalHide={this.switchTeamModalHide} />
                            }

                            {showConfirmationPopUp &&
                                <ConfirmationPopup isStockF={true} IsConfirmationPopupShow={showConfirmationPopUp} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={this.state.userTeamListSend} TotalTeam={TotalTeam} FixturedContest={this.state.FixtureContestData} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} lobbyDataToPopup={this.state.FixtureData} fromContestListingScreen={true} createdLineUp={''} />
                            }

                            {showThankYouModal &&
                                <Thankyou from={'MyContest'} ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isStock={true}/>
                            }
                            {
                                showPreview && <StockTeamPreview isFrom={this.state.selectedTab == Constants.CONTEST_UPCOMING ? 'preview' : this.state.selectedTab == Constants.CONTEST_LIVE && this.state.livePrview ? 'preview' : 'point'} CollectionData={this.state.rootitem} openTeam={this.state.teamitem} isViewAllShown={showPreview} onViewAllHide={() => this.setState({ showPreview: false })}  
                                // StockSettingValue={this.state.StockSettingValue} 
                                isTeamPrv={'true'} />
                            }
                            {
                                showUJC &&
                                <UnableJoinContest
                                    showM={showUJC}
                                    hideM={this.hideUJC}
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