import React from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, _isEmpty, _debounce, parseURLDate, _filter, _Map, _includes, _chain, convertToTimestamp } from '../../Utilities/Utilities';
import {
    DMUpcomingContest,
    DMLiveContest,
    DMCompletedContest
} from './index';
import { my_contest_config } from '../../JsonFiles';
import { joinContest, getUserLineUps, getMyCollection, getMyCollectionMultiGame, getMyContestFreeToPlay, joinContestNetworkfantasy } from '../../WSHelper/WSCallings';
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
import FieldViewRight from "../../views/FieldViewRight";
import CustomHeader from '../../components/CustomHeader';
import { NoDataView } from '../CustomComponent';
import UnableJoinContest from '../../Modals/UnableJoinContest';
import InfiniteScroll from 'react-infinite-scroll-component';

/**
  * @class MyContest
  * @description My contest listing of current loggedin user for selected sports
  * @author Vinfotech
*/
export default class DMMyContest extends React.Component {
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
            sports_id: Constants.AppSelectedSport ? Constants.AppSelectedSport : '',
            allowCollection: Utilities.getMasterData().a_collection,
            LobyyData: '',
            ConfirmationIsFrom: '',
            lineupArr: [],
            sideView: false,
            fieldViewRightData: [],
            showUJC: false,
            rootitem: [],
            allowRevFantasy: Utilities.getMasterData().a_reverse == '1',
            DFSTourEnable: Utilities.getMasterData().a_dfst == 1 ? true : false,
            isTLoading: false,
            MerchandiseList: [],
            TourList: [],
            isNF: false,
            hasMoreUpcoming: false,
            hasMoreCompleted: false,
            hasMoreLive: false,
            page: 1,
            limit: 20,
            updateTeamDetails: null
        }
    }

    componentDidMount() {
        if (ls.get('showMyTeam')) {
            ls.remove('showMyTeam')
        }
        ls.remove('SHActive')
        Utilities.setScreenName('contests')
        Utilities.handleAppBackManage('my-contest')
        WSManager.setH2hMessage(false)
        ls.set('h2hTab', false);
        ls.remove('bench_data')
        this.setState({ sideView: false })
        let url = this.props.location.search;
        let urlParams = queryString.parse(url);
        let contest = urlParams.contest;
        if (contest in my_contest_config.contest_url) {
            let { sports_id } = this.state;
            sports_id = Constants.AppSelectedSport;

            this.setState({ selectedTab: my_contest_config.contest_url[contest], sports_id }, () => {
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
            this.setState({ sports_id: Constants.AppSelectedSport }, () => {
                this.getMyCollectionsList(this.state.selectedTab)

            })
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

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (WSManager.loggedIn() && this.props.history.location.pathname == '/my-contests') {

            if (this.state.sports_id != nextProps.selectedSport) {
                this.reload(nextProps);
            }
            // else {
            //     let url = this.props.location.search;
            //     let urlParams = queryString.parse(url);

            //     let contest = urlParams.contest;
            //     if (contest in my_contest_config.contest_url) {
            //         let { sports_id } = this.state;
            //         sports_id = Constants.AppSelectedSport;
            //            let tmpSelectedTab = my_contest_config.contest_url[contest];
            //         if (this.state.selectedTab != tmpSelectedTab || this.state.sports_id != Constants.AppSelectedSport) {

            //             this.setState({ selectedTab: my_contest_config.contest_url[contest], sports_id }, () => {
            //                 this.getMyCollectionsList(this.state.selectedTab)

            //             })
            //         }
            //     }
            //     else {
            //         if (contest in my_contest_config.contest) {
            //             this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[contest])
            //         }
            //         else {
            //             this.props.history.replace("/my-contests?contest=" + my_contest_config.contest[this.state.selectedTab])
            //         }
            //     }
            // }
        }
    }

    sideViewHide = () => {
        this.setState({
            sideView: false,
        })
    }

    /**
     * @description Call this function when you want to go fo lobby screen
    */
    goToLobby = () => {
        // this.props.history.push({ pathname: '/' })
        const { LobyyData, FixtureData } = this.state;
        let dateformaturl = Utilities.getUtcToLocal(FixtureData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        let home = FixtureData.home || LobyyData.home;
        let away = FixtureData.away || LobyyData.away;

        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixtureData.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(Constants.SELECTED_GAMET)

        contestListingPath = contestListingPath.toLowerCase()
        let CLLobyyData = FixtureData.home ? FixtureData : LobyyData
        this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: FixtureData, LobyyData: CLLobyyData, isFromPM: true, isJoinContestFlow: true } })
    }

    goLobby = () => {
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
    getUserLineUpListApi = (event, CollectionData, childItem, teamItem, showPopup, rootItem) => {
        if(childItem.is_2nd_inning == "1" && CollectionData['2nd_inning_date']){
            let gTime = new Date(Utilities.getUtcToLocal(CollectionData['2nd_inning_date'])).getTime()
            CollectionData['game_starts_in'] = gTime
        }
        if (event != null) {
            event.stopPropagation();
        }
        let param = {
            "sports_id": Constants.AppSelectedSport,
            "collection_master_id": CollectionData.collection_master_id,
        }

        this.setState({ isLoaderShow: true })
        getUserLineUps(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let data = responseJson.data
                let tList = childItem.is_2nd_inning == "1" ? _filter(data,(obj,idx) => {
                    return obj.is_2nd_inning == "1";
                }) : childItem.is_reverse == 1 ? _filter(data,(obj,idx) => {
                    return obj.is_reverse == "1";
                }) : _filter(data,(obj,idx) => {
                    return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
                })
                this.setState({
                    TotalTeam: tList, //data,
                    userTeamListSend: tList/*(this.state.allowRevFantasy && Constants.SELECTED_GAMET == Constants.GameType.DFS) ?  data.filter((obj,idx) => {
                        return (childItem.is_reverse == 1 ? obj.is_reverse == "1" : obj.is_reverse != "1")
                    }) : data*/
                })
            }
            if (responseJson && responseJson.data && responseJson.data.length > 0) {
                let tempList = [];
                // let data = (this.state.allowRevFantasy && Constants.SELECTED_GAMET == Constants.GameType.DFS) ?  responseJson.data.filter((obj,idx) => {
                //     return (childItem.is_reverse == 1 ? obj.is_reverse == "1" : obj.is_reverse != "1")
                // }) : responseJson.data

                let tList = childItem.is_2nd_inning == "1" ? _filter(responseJson.data,(obj,idx) => {
                    return obj.is_2nd_inning == "1";
                }) : childItem.is_reverse == 1 ? _filter(responseJson.data,(obj,idx) => {
                    return obj.is_reverse == "1";
                }) : _filter(responseJson.data,(obj,idx) => {
                    return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
                })

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
                    showContestDetail: true
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
        let urlData = '';

        if (this.state.ConfirmationIsFrom == '') {
            urlData = this.state.FixtureData;
        }
        else {
            urlData = this.state.LobyyData;
        }
        let lengthMatchList = urlData.match_list ? urlData.match_list.length : 0
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
        
        urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}
        if (lengthMatchList == 0) {
            this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData ? this.state.LobyyData : dataFromConfirmLobby, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: Constants.AppSelectedSport, isReverseF: dataFromConfirmFixture.is_reverse || false, isSecIn: dataFromConfirmFixture.is_2nd_inning == 1, isPlayingAnnounced: urlData.playing_announce  } })
        }
        else if (lengthMatchList == 1) {
            if(Constants.AppSelectedSport === '11'){
                let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })

                // this.props.history.push({ pathname: '/lineup/' + urlData.match_list[0].home.toLowerCase() + "-vs-" + urlData.match_list[0].away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData ? this.state.LobyyData : dataFromConfirmLobby, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: Constants.AppSelectedSport, isReverseF: dataFromConfirmFixture.is_reverse || false, isSecIn: dataFromConfirmFixture.is_2nd_inning == 1, isPlayingAnnounced: urlData.playing_announce } })
            }else{
            this.props.history.push({ pathname: '/lineup/' + urlData.match_list[0].home.toLowerCase() + "-vs-" + urlData.match_list[0].away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData ? this.state.LobyyData : dataFromConfirmLobby, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: Constants.AppSelectedSport, isReverseF: dataFromConfirmFixture.is_reverse || false, isSecIn: dataFromConfirmFixture.is_2nd_inning == 1, isPlayingAnnounced: urlData.playing_announce } })
            }
        }
        else {
            let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            this.props.history.push({ pathname: '/lineup/' + pathurl + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData ? this.state.LobyyData : dataFromConfirmLobby, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: Constants.AppSelectedSport, isReverseF: dataFromConfirmFixture.is_reverse || false, isSecIn: dataFromConfirmFixture.is_2nd_inning == 1, isPlayingAnnounced: urlData.playing_announce } })
        }
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
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'mycontest' } });


                    }
                    else {
                        // Utilities.showToast('Not enough coins', 1000);
                        this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
                    }
                    // Utilities.showToast('Not enough coins', 1000);
                    // WSManager.setFromConfirmPopupAddFunds(true);
                    // WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    // WSManager.setPaymentCalledFrom("ContestListing")
                    // this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true });
                }

                else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("mycontest")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true });
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
        let IsNetworkContest = this.state.FixtureData.is_network_collection == 1 ? true : dataFromConfirmPopUp.FixturedContestItem.is_network_contest == 1 ? true : false;
        let apiCall = IsNetworkContest ? joinContestNetworkfantasy : joinContest

        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
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

                // if (contestAccessType == '1' || isPrivate == '1') {
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
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
        if(this.state.selectedTab == selectedTab) return
        window.history.replaceState("", "", "/my-contests?contest=" + my_contest_config.contest[selectedTab]);
        this.setState({ 
            selectedTab: selectedTab,
            hasMoreUpcoming: false,
            hasMoreCompleted: false,
            hasMoreLive: false,
            page: 1,
            limit: 20
        }, () => {
            this.getMyCollectionsList(this.state.selectedTab)
        });
    }, 300)

    /**
     * @description This function is responsible to get Live Contests response
     * @param status selected tab (Live, Upcoming, Completed)
     */
    getMyCollectionsList = async (status) => {
        const { page, limit } = this.state
        this.setState({ sideView: false })
        var param = {
            "sports_id": Constants.AppSelectedSport,
            "status": status,
            "page": page,
            "limit": limit
        }

        this.setState({ isLoaderShow: true })

        let apiStatus = Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? getMyCollectionMultiGame : Constants.SELECTED_GAMET == Constants.GameType.Free2Play ? getMyContestFreeToPlay : getMyCollection
        var responseJson = await apiStatus(param);
        const { fixture = {}, match = {}, booster = {}} = responseJson.data;
        this.setState({ isLoaderShow: false })

        let fixture_list = _Map(fixture, (_item) => {
            let _booster = _filter(booster, (o, i) => _includes(_item.collection_master_id, i))[0] || '';
            let match_list = match.filter(o => _includes(_item.season_ids.split(','), o.season_id))
                                .map(ob => {
                                    return {...ob, score_data: (_item.is_tour_game != 1 ? JSON.parse(ob.score_data) : {}), game_starts_in: convertToTimestamp(_item.season_scheduled_date)}
                                 })

            let obj = { ..._item, match_list, game_starts_in: convertToTimestamp(_item.season_scheduled_date), booster: _booster}
            return obj;
        })

        if (responseJson && responseJson.response_code == WSC.successCode) {
            switch (this.state.selectedTab) {
                case Constants.CONTEST_UPCOMING:
                    this.setState({ 
                        upcomingContestList: page == 1 ? fixture_list : [...this.state.upcomingContestList, ...fixture_list],
                        hasMoreUpcoming: fixture_list.length === limit
                    })
                    break;
                case Constants.CONTEST_LIVE:
                    this.setState({ 
                        liveContestList: page == 1 ? fixture_list : [...this.state.liveContestList, ...fixture_list],
                        hasMoreLive: fixture_list.length === limit
                    })
                    break;
                case Constants.CONTEST_COMPLETED:
                    this.setState({
                        completedContestList: page == 1 ? fixture_list : [...this.state.completedContestList, ...fixture_list],
                        hasMoreCompleted: fixture_list.length === limit
                    })
                    break;
                default:
                    this.setState({ upcomingContestList: fixture_list })
            }
        }

    }

    fetchMoreData = (selectedTab) => {
        const { page } = this.state;
        this.setState({
            page: page + 1
        }, () => this.getMyCollectionsList(selectedTab))
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
        if (childItem.is_2nd_inning == "1" && rootItem['2nd_inning_date']) {
            let secDate = rootItem['2nd_inning_date'];
            let gTime = new Date(Utilities.getUtcToLocal(secDate)).getTime()
            rootItem['game_starts_in'] = gTime
            rootItem['season_scheduled_date'] = secDate
        }
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/leaderboard',
            state: {
                rootItem: rootItem,
                contestItem: childItem,
                status: this.state.selectedTab,
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
        if (this.state.userTeamListSend != null && !_isUndefined(this.state.userTeamListSend) && this.state.userTeamListSend.length > 0) {

            this.ContestDetailHide();
            setTimeout(() => {
                // this.setState({ showConfirmationPopUp: true, FixtureData: this.state.FixtureData, ConfirmationIsFrom: 'contestdetail' })
                this.setState({ showConfirmationPopUp: true, FixtureData: data, ConfirmationIsFrom: 'contestdetail' })
            }, 200);
        } else {
            let urlData = data;
            let dateformaturl = parseURLDate(urlData.season_scheduled_date);
            let lineupPath = '/lineup/' + data.home + "-vs-" + data.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, from: "contestJoin", LobyyData: urlData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.FixtureData.is_2nd_inning == 1, isPlayingAnnounced: data.playing_announce } })
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
        if (contestItem.is_2nd_inning == "1" && rootitem['2nd_inning_date']) {
            let gTime = new Date(Utilities.getUtcToLocal(rootitem['2nd_inning_date'])).getTime()
            rootitem['game_starts_in'] = gTime
        }
        let isNFContest = contestItem.is_network_contest && contestItem.is_network_contest == 1 ? true : false
        this.setState({
            sideView: sideView,
            updateTeamDetails: new Date().valueOf(),
            fieldViewRightData: teamitem,
            rootitem: rootitem,
            isRF: contestItem.is_reverse == "1" ? true : false,
            isNF: isNFContest,
            isSecIn: contestItem.is_2nd_inning == 1 ? true : false
        })
        let urlData = rootitem;
        let dateformaturl = parseURLDate(urlData.season_scheduled_date);
        let homePlayerCount = 0;
        let awayPlayerCount = 0;

        if (typeof this.state.lineupArr != 'undefined' && this.state.lineupArr.length > 0) {
            this.state.lineupArr.map((lineupItem, lineupIndex) => {
                if (Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
                    if (lineupItem.team_abbreviation == urlData.home || lineupItem.team_abbr == urlData.home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }
                else {
                    if (lineupItem.team_abbreviation == urlData.match_list[0].home || lineupItem.team_abbr == urlData.match_list[0].home) {
                        homePlayerCount = homePlayerCount + 1;
                    }
                    else {
                        awayPlayerCount = awayPlayerCount + 1;
                    }
                }


            });
        }

        ls.set('home_player_count', homePlayerCount);
        ls.set('away_player_count', awayPlayerCount);
        ls.set('Lineup_data', this.state.lineupArr);

        let lineupPath = '';
        rootitem = {...rootitem, ...(rootitem.match_list[0] ? rootitem.match_list[0] : {}) }
        if (sideView == false || isEdit == true) {
            if (isEdit == false) {
                if (Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
                    if (!urlData.home) {
                        urlData['home'] = urlData.match_list[0].home
                        urlData['away'] = urlData.match_list[0].away
                    }
                    let fieldViewPath = '/field-view/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({
                        pathname: fieldViewPath.toLowerCase(),
                        state: {
                            team: teamitem, contestItem: contestItem, rootitem: rootitem, isEdit: isEdit, from: 'MyContest',
                            isFromtab: isFromtab, isFrom: 'MyContest', resetIndex: 1, isPlayingAnnounced: rootitem.playing_announce,
                            isReverseF: contestItem.is_reverse || false, isFromUpcoming: true, isSecIn: contestItem.is_2nd_inning == 1 ? true : false,
                            isNF: isNFContest, LobyyData: this.state.LobyyData, lData: rootitem
                        }
                    });
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    let fieldViewPath = '/field-view/' + pathurl + "-" + dateformaturl
                    this.props.history.push({
                        pathname: fieldViewPath.toLowerCase(),
                        state: {
                            team: teamitem, contestItem: contestItem, rootitem: rootitem,
                            isEdit: isEdit, from: 'MyContest', isFromtab: isFromtab, isFrom: 'MyContest', resetIndex: 1,
                            isPlayingAnnounced: rootitem.playing_announce, isReverseF: contestItem.is_reverse || false, isFromUpcoming: true,
                            isSecIn: contestItem.is_2nd_inning == 1 ? true : false, isNF: isNFContest, LobyyData: this.state.LobyyData, lData: rootitem
                        }
                    });
                }
            }
            else if (Constants.SELECTED_GAMET != Constants.GameType.MultiGame) {
                if (!urlData.home) {
                    urlData['home'] = urlData.match_list[0].home
                    urlData['away'] = urlData.match_list[0].away
                }
                lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: urlData, isFromMyTeams: true, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id, isReverseF: contestItem.is_reverse || false, isSecIn: contestItem.is_2nd_inning == 1, isPlayingAnnounced: rootitem.playing_announce, isNF: isNFContest, isFromUpcoming: true } });
            }
            else {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData, LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, FixturedContest: this.state.myContestData, team: this.state.TeamMyContestData, from: 'editView', rootDataItem: this.state.rootDataItem, isFromMyTeams: true, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, resetIndex: 1, teamitem: teamitem, collection_master_id: contestItem.collection_master_id, league_id: contestItem.league_id, isReverseF: contestItem.is_reverse || false, isSecIn: contestItem.is_2nd_inning == 1, isPlayingAnnounced: rootitem.playing_announce, isNF: isNFContest, isFromUpcoming: true } });
            }

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

        let urlData = rootitem;
        let dateformaturl = parseURLDate(urlData.season_scheduled_date);
        let lineupPath = '';

        if (urlData.home) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: contestItem, from: "contestJoin", team: teamitem, rootDataItem: rootitem, current_sport: Constants.AppSelectedSport, isSecIn: contestItem.is_2nd_inning == 1, isPlayingAnnounced: rootitem.playing_announce } })
        }
        else {
            let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: contestItem, from: "contestJoin", team: teamitem, rootDataItem: rootitem, current_sport: Constants.AppSelectedSport, isSecIn: contestItem.is_2nd_inning == 1, isPlayingAnnounced: rootitem.playing_announce } })
        }
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
        * @description This function opens the Switch Team Modal
        * @param fixtureData Fixture item
        * @param contestData Contest item
        * @param teamData Team item
        * @see SwitchTeam
        */
    goToBoosterScreen = (fixtureData, contestData, teamData) => {
        this.props.history.push({
            pathname: `/booster-collection/${fixtureData.collection_master_id}/${Utilities.getSelectedSportsForUrl().toLowerCase()}/${teamData.lineup_master_id ? teamData.lineup_master_id : '0'}`
            , state: { LobyyData: fixtureData, FixturedContest: fixtureData, team_name: teamData.team_name, isFromFlow: "MyTeams", isFromMyTeams: true, booster_id: teamData.booster_id, direct: true, ifFromSwitchTeamModal: false }
        })
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
     * @description This function is called when sports changed from header
     * @static A static function 
    */
    reload = (nextProps) => {
        if (window.location.pathname.startsWith("/my-contests")) {
            this.setState({ completedContestList: [], liveContestList: [], upcomingContestList: [], sports_id: nextProps.selectedSport }, () => {
                this.getMyCollectionsList(this.state.selectedTab)

            })
        }
    }


    openScoreCard = (e, item, status) => {
        e.stopPropagation()
        let leagueId = item.league_id
        let season_game_uid = item.match_list[0].season_game_uid
        let collection_master_id = item.collection_master_id
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/match-scorecard-stats' + '/' + leagueId + '/' + season_game_uid + '/' + collection_master_id,
            state: {
                rootItem: item,
                status: status == 0 ? Constants.CONTEST_LIVE : Constants.CONTEST_COMPLETED
            }
        })
    }

    showStats = (e, item) => {
        e.stopPropagation()
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/stats',
            state: {
                item: item
            }

        })
    }

    goToBench = (item, childItem, teamItem) => {
        let urlData = item;
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_').toLowerCase();

        let CMID = item.collection_master_id ? item.collection_master_id : childItem.collection_master_id;
        let benchPath = '/bench-selection/' + teamItem.lineup_master_id + '/' + CMID + '/' + pathurl + "-" + dateformaturl;
        this.props.history.push({
            pathname: benchPath, state: { from: 'MyContest', LobyyData: item, FixturedContest: childItem, sports_id: Constants.AppSelectedSport, teamName: teamItem.team_name, collection_master_id: CMID, MasterData: this.state.MasterData, selLineupArr: this.state.lineupArr, allRosterList: this.state.allRosterList, lineupMasterdId: teamItem.lineup_master_id, isFrom: 'MyContest', isFromMyTeams: this.state.isFromMyTeams, TeamMyContestData: teamItem, isReverseF: this.state.isReverseF, isSecIn: this.state.isSecIn, isBenchUC: true, isPlayingAnnounced: this.state.rootitem.playing_announce || 0}
        });

    }

    goToMyContDetail=(data)=>{ 
        // this.props.history.push({
        //     pathname:  this.state.sports_id + '/fixture-detail/' + data.collection_master_id
        // })

        // e.stopPropagation()
        // let leagueId = data.league_id
        // let season_game_uid = data.season_game_uid
        // let collection_master_id = data.collection_master_id
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/fixture-detail/' + data.collection_master_id,
            state: {
                rootItem: data,
                status: this.state.selectedTab == Constants.CONTEST_LIVE  ? Constants.CONTEST_LIVE : Constants.CONTEST_COMPLETED
            }
        })
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
            TourList,
            MerchandiseList,
            isTLoading,
            hasMoreUpcoming,
            hasMoreCompleted,
            hasMoreLive,
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
                    <div className={"web-container my-contest-style tab-two-height web-container-fixed" + (this.state.selectedTab == Constants.CONTEST_COMPLETED ? ' ' : '') + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? ' multigame-my-contest' : '')}>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className={"tabs-primary " + (!this.props.hideHeader ? ' mt50' : '')}>
                            <Tab.Container id='my-contest-tabs' activeKey={this.state.selectedTab} onSelect={() => {}} defaultActiveKey={this.state.selectedTab}>
                                <Row className="clearfix">
                                    <Col className="top-fixed my-contest-tab circular-tab circular-tab-new xnew-tab" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_UPCOMING)} eventKey={Constants.CONTEST_UPCOMING}>{AppLabels.UPCOMING}</NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_LIVE)} eventKey={Constants.CONTEST_LIVE} className="live-contest"><span><span className="live-indicator"></span> {AppLabels.LIVE} </span></NavItem>
                                            <NavItem onClick={() => this.onTabClick(Constants.CONTEST_COMPLETED)} eventKey={Constants.CONTEST_COMPLETED}>{AppLabels.COMPLETED}</NavItem>
                                        </Nav>
                                    </Col>
                                    <Col className="top-tab-margin top-tab-margin-view" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={Constants.CONTEST_LIVE}>
                                            <InfiniteScroll
                                                        dataLength={this.state.liveContestList.length}
                                                        next={() => this.fetchMoreData(selectedTab)}
                                                        hasMore={hasMoreLive}
                                                        scrollThreshold={'100px'}
                                                        scrollableTarget='DMLiveContest'
                                                    >
                                                    <DMLiveContest {...this.props} liveContestList={this.state.liveContestList} ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} allowCollection={this.state.allowCollection} goToChatMyContest={this.goToChatMyContest} TourList={TourList} MerchandiseList={MerchandiseList} isTLoading={isTLoading} openScoreCard={this.openScoreCard} showStats={this.showStats} isLoaderShow={this.state.isLoaderShow} goToMyContDetail={this.goToMyContDetail} id="DMLiveContest" />

                                                    {
                                                        this.state.liveContestList.length == 0 && !this.state.isLoaderShow &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                            MESSAGE_2={''}
                                                            BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                            onClick={this.goLobby}
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
                                                </InfiniteScroll>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_UPCOMING}>

                                                <InfiniteScroll
                                                    dataLength={this.state.upcomingContestList.length}
                                                    next={() => this.fetchMoreData(selectedTab)}
                                                    hasMore={hasMoreUpcoming}
                                                    scrollThreshold={'100px'}
                                                    scrollableTarget='DMUpcomingContest'
                                                >
                                                    <DMUpcomingContest {...this.props} collectionMasterId={this.state.collectionMasterId} upcomingContestList={this.state.upcomingContestList} removeFromList={this.removeFromList}
                                                        ContestDetailShow={this.ContestDetailShow} getUserLineUpListApi={this.getUserLineUpListApi}
                                                        shareContest={this.shareContest} switchTeamModalShow={this.switchTeamModalShow} openLineup={this.openLineup}
                                                        allowCollection={this.state.allowCollection}
                                                        goToChatMyContest={this.goToChatMyContest} TourList={TourList} MerchandiseList={MerchandiseList} isTLoading={isTLoading} isLoaderShow={this.state.isLoaderShow} goToBoosterScreen={this.goToBoosterScreen} goToBench={this.goToBench} id="DMUpcomingContest" />


                                                    {
                                                        this.state.upcomingContestList.length == 0 && !this.state.isLoaderShow &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                            MESSAGE_2={''}
                                                            BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                            onClick={this.goLobby}
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
                                                </InfiniteScroll>

                                            </Tab.Pane>
                                            <Tab.Pane eventKey={Constants.CONTEST_COMPLETED}>
                                                <InfiniteScroll
                                                        dataLength={this.state.completedContestList.length}
                                                        next={() => this.fetchMoreData(selectedTab)}
                                                        hasMore={hasMoreCompleted}
                                                        scrollThreshold={'100px'}
                                                        scrollableTarget='DMCompletedContest'

                                                    >
                                                    <DMCompletedContest  {...this.props} collectionMasterId={this.state.collectionMasterId} completedContestList={this.state.completedContestList} ContestDetailShow={this.ContestDetailShow} openLeaderboard={this.openLeaderboard} allowCollection={this.state.allowCollection} TourList={TourList} MerchandiseList={MerchandiseList} isTLoading={isTLoading} openScoreCard={this.openScoreCard} showStats={this.showStats} isLoaderShow={this.state.isLoaderShow} goToMyContDetail={this.goToMyContDetail} id="DMCompletedContest" />


                                                    {
                                                        this.state.completedContestList.length == 0 && !this.state.isLoaderShow &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={MESSAGE_1 + ' ' + MESSAGE_2}
                                                            MESSAGE_2={''}
                                                            BUTTON_TEXT={AppLabels.GO_TO_LOBBY}
                                                            onClick={this.goLobby}
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
                                                </InfiniteScroll>


                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>
                            {this.state.showContestDetail &&
                                <ContestDetailModal {...this.props}
                                    contestStatus={this.state.selectedTab}
                                    IsContestDetailShow={this.state.showContestDetail}
                                    onJoinBtnClick={this.onSubmitBtnClick}
                                    IsContestDetailHide={this.ContestDetailHide}
                                    OpenContestDetailFor={this.state.FixtureData}
                                    LobyyData={this.state.LobyyData}
                                    isSecIn={this.state.FixtureData.is_2nd_inning == 1}
                                />
                            }

                            {showSharContestModal &&
                                <ShareContestModal IsShareContestModalShow={this.shareContestModalShow} IsShareContestModalHide={this.shareContestModalHide} FixturedContestItem={this.state.FixtureData} />
                            }

                            {showSwitchTeamModal &&
                                <SwitchTeam ref={ref => this.switchTeamRef = ref} mHistory={this.props.history} IsSwitchTeamModalShow={this.switchTeamModalShow} IsSwitchTeamModalHide={this.switchTeamModalHide} />
                            }

                            {showConfirmationPopUp &&
                                <ConfirmationPopup IsConfirmationPopupShow={showConfirmationPopUp} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={this.state.userTeamListSend} TotalTeam={TotalTeam} FixturedContest={this.state.FixtureContestData} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} lobbyDataToPopup={this.state.FixtureData} fromContestListingScreen={true} createdLineUp={''} />
                            }

                            {showThankYouModal &&
                                <Thankyou from={'MyContest'} ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} />
                            }
                            {this.state.sideView &&
                                <FieldViewRight
                                    SelectedLineup={this.state.lineupArr.length ? this.state.lineupArr : []}
                                    MasterData={this.state.masterData}
                                    LobyyData={this.state.LobyyData}
                                    FixturedContest={this.state.FixturedContest}
                                    isFrom={this.state.isFrom}
                                    isFromUpcoming={true}
                                    rootDataItem={this.state.rootDataItem}
                                    team={this.state.team}
                                    team_name={this.state.teamName}
                                    resetIndex={1}
                                    TeamMyContestData={this.state.fieldViewRightData}
                                    isFromMyTeams={this.state.isFromMyTeams}
                                    ifFromSwitchTeamModal={this.state.ifFromSwitchTeamModal}
                                    rootitem={this.state.rootitem}
                                    sideViewHide={this.sideViewHide}
                                    isPlayingAnnounced={this.state.rootitem.playing_announce || 0}
                                    isReverseF={this.state.isRF || false}
                                    isSecIn={this.state.isSecIn || false}
                                    isNF={this.state.isNF || false}
                                    updateTeamDetails={this.state.updateTeamDetails}
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