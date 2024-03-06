import React from 'react';
import { FormGroup, Col, Row } from 'react-bootstrap';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft, darkInputStyleLeft } from '../helper/input-style';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import { MyContext } from '../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../helper/MetaData";
import ContestDetailModal from '../Modals/ContestDetail';
import ls from 'local-storage';
import ConfirmationPopup from '../Modals/ConfirmationPopup';
import UnableJoinContest from '../Modals/UnableJoinContest';
import Thankyou from '../Modals/Thankyou';
import * as AppLabels from "../helper/AppLabels";
import CustomHeader from '../components/CustomHeader';
import {Utilities,checkBanState, _filter, _isUndefined, convertToTimestamp} from '../Utilities/Utilities';
import { SELECTED_GAMET, GameType, AppSelectedSport,DARK_THEME_ENABLE, EnableBuyCoin,setValue } from '../helper/Constants';
import { getUserTeams, joinContest, checkContestEligibility,checkContestEligibilityMultiGame,getMultigameUserTeams, StockCheckContestEligibility, getStockUserAllTeams, stockJoinContest,checkContestEligibilityLF,joinContestLF,getUserContestJoinCountNetworkfantasy,GetPFMyContestTeamCount,getSFUserContestJoinCount,getUserContestJoinCount,getUserAadharDetail, getContestDetails } from '../WSHelper/WSCallings';
import LFContestDetailsModal from '../Component/LiveFantasy/LFContestDetails';

export default class HaveALeagueCodeClass extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            mLeagueCode: '',
            showContestDetail: false,
            clickOnce: false,
            showConfirmationPopUp: false,
            userTeamListSend: [],
            TotalTeam: [],
            LobyyData: [],
            showThankYouModal: false,
            showUJC: false,
            allowRevFantasy: Utilities.getMasterData().a_reverse == '1',
            isReverseF: false,
            preLData: this.props.LobyyData || '',
            isStockF: this.props.isStockF || SELECTED_GAMET == GameType.StockFantasy || SELECTED_GAMET == GameType.StockFantasyEquity,
            userJoinCount: 0,
            aadharData: ''
        };
    }
    componentDidMount() {
        Utilities.setScreenName('referral')
        Utilities.handleAppBackManage('Join-contest')

    }
    ContestDetailShow = (data, activeTab) => {
        console.log('ContestDetailShow 1', data)
        if (this.state.isStockF) {
            data['collection_master_id'] = data.collection_id || data.collection_master_id || this.state.preLData.collection_master_id
        }
        this.setState({
            showContestDetail: true,
            contestData: data,
            activeTab: activeTab,
        }, () => {
            console.log('ContestDetailShow 1', data)
        });
    }

    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
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

    handleChange = (e) => {
        const value = e.target.value;
        this.setState({
            mLeagueCode: value,
        });
    }

    joinPrivateLeague() {
        this.setState({ clickOnce: true })
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        } else {
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
            if (this.state.mLeagueCode != "" && this.state.mLeagueCode.length > 3) {
                this.callHaveALeagueCodeApi();
            } else {
                Utilities.showToast(AppLabels.Please_enter_a_valid_league_code, 3000);
            }
        }
    }

       
    hanleJsonParser=(data)=>{
        try{
            return JSON.parse(data)
        }
        catch{
            return data
        }
    }

    callHaveALeagueCodeApi() {
        let param = {
            'join_code': this.state.mLeagueCode
        }
        if (SELECTED_GAMET == GameType.StockFantasy) {
            param['stock_type'] = '1'
        }
        else if (SELECTED_GAMET == GameType.StockFantasyEquity) {
            param['stock_type'] = '2'

        }

        let apiMethod = this.state.isStockF ? StockCheckContestEligibility : SELECTED_GAMET == GameType.MultiGame ? checkContestEligibility : SELECTED_GAMET == GameType.LiveFantasy ? checkContestEligibilityLF : checkContestEligibility
        apiMethod(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if(_isUndefined(responseJson.data.size)) {
                    var param = {
                        "contest_id": responseJson.data.contest_id
                    }
                    getContestDetails(param).then(res => {
                        let apiResponseData = res;
                        let data = apiResponseData
                        if(data.match) {
                            const { match, current_prize, ..._apiResponseData } = apiResponseData
                            let match_list =  match.map((item) => {
                                item.game_starts_in = convertToTimestamp(data.scheduled_date)
                                return item
                            })
                            data = {..._apiResponseData, match_list, current_prize: this.hanleJsonParser(current_prize), game_starts_in: convertToTimestamp(data.scheduled_date)}
                        }
    
                        this.setState({
                            contestData: data,
                            LobyyData: data,
                            isReverseF: data.is_reverse == 1 ? true : false,
                            isSecIn: data.is_2nd_inning == 1
                        }, () => {
                            console.log('got to data', data)
                            this.ContestDetailShow(data, 2);
                            if (WSManager.loggedIn() && SELECTED_GAMET != GameType.DFS) {
                                this.getUserJoinCount(data);
    
                                setTimeout(() => {
                                    if (SELECTED_GAMET != GameType.LiveFantasy && SELECTED_GAMET != GameType.DFS &&
                                        (data.total_user_joined != data.size) &&
                                        (data.multiple_lineup > this.state.userJoinCount)
                                    ) {
                                        this.getUserLineUpListApi(data)
                                    }
                                }, 50);
                            }
                        })
                    })
                } else {
                    if (responseJson.data.total_user_joined == responseJson.data.size) {
                        Utilities.showToast(AppLabels.Entry_for_the_contest, 3000);
                    } else {
                        if (responseJson.data.game_type == 'dfs') {
                            WSManager.setPickedGameType(GameType.DFS);
                        }
                        // if(responseJson.data.game_type == 'multigame'){
                        //     WSManager.setPickedGameType(GameType.MultiGame);
                        // }
                        if (responseJson.data.game_type == 'free2play') {
                            WSManager.setPickedGameType(GameType.Free2Play);
                        }
                        if (responseJson.data.game_type == 'tournament') {
                            WSManager.setPickedGameType(GameType.Tournament);
                        }
                        if (responseJson.data.stock_type && responseJson.data.stock_type == '2') {
                            WSManager.setPickedGameType(GameType.StockFantasyEquity);
                        }
                        if (responseJson.data.stock_type && responseJson.data.stock_type == '1') {
                            WSManager.setPickedGameType(GameType.StockFantasy);
                        }
                        if (responseJson.data.game_type == 'livefantasy') {
                            WSManager.setPickedGameType(GameType.LiveFantasy);
                        }
                        console.log('got to 1')
                        setTimeout(() => {
                            let data = responseJson.data;
                            if (this.state.isStockF) {
                                let cName = (data.collection_name || '').toLowerCase()
                                data['collection_master_id'] = data.collection_id;
                                data['category_id'] = data.category_id || (cName == 'daily' ? '1' : cName == 'weekly' ? '2' : '3');
                            }
                            else if (SELECTED_GAMET == GameType.LiveFantasy) {
                                data['collection_master_id'] = data.collection_id;
    
                            }
                            console.log('responseJson.data', data)
                            this.setState({
                                contestData: data,
                                LobyyData: data,
                                isReverseF: data.is_reverse == 1 ? true : false,
                                isSecIn: data.is_2nd_inning == 1
                            }, () => {
                                console.log('got to data', data)
                                this.ContestDetailShow(data, 2);
                                if (WSManager.loggedIn() && SELECTED_GAMET != GameType.DFS) {
                                    this.getUserJoinCount(data);
    
                                    setTimeout(() => {
                                        if (SELECTED_GAMET != GameType.LiveFantasy && SELECTED_GAMET != GameType.DFS &&
                                            (data.total_user_joined != data.size) &&
                                            (data.multiple_lineup > this.state.userJoinCount)
                                        ) {
                                            this.getUserLineUpListApi(data)
                                        }
                                    }, 50);
                                }
                            })
    
    
                        }, 200);
                    }
                }

            }
            this.setState({ clickOnce: false })
        })
    }


    getUserJoinCount(data) {
        console.log('getUserJoinCount 1111')
        if (SELECTED_GAMET == GameType.PickFantasy) {
            var param = {
                "season_id": data.season_id,
            }
        }
        else {
            var param = {
                "contest_id": data.contest_id,
            }
        }
        this.setState({ isLoading: true })
        if (this.state.contestData && this.state.contestData.is_network_contest && this.state.contestData.is_network_contest == 1) {
            getUserContestJoinCountNetworkfantasy(param).then((responseJson) => {
                this.setState({ isLoading: false })
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({ userJoinCount: responseJson.data.user_joined_count })
                }
            })
        }
        else {
            if (SELECTED_GAMET == GameType.PickFantasy) {
                let apiV = GetPFMyContestTeamCount
                apiV(param).then((responseJson) => {
                    this.setState({ isLoading: false })
                    if (responseJson.response_code == WSC.successCode) {
                        this.setState({ userJoinCount: responseJson.data && responseJson.data.contest_count ? responseJson.data.contest_count : 0 })
                    }
                })
            }
            else {
                let apiV = this.state.isStockF ? getSFUserContestJoinCount : getUserContestJoinCount
                apiV(param).then((responseJson) => {
                    this.setState({ isLoading: false })
                    if (responseJson.response_code == WSC.successCode) {
                        this.setState({ userJoinCount: responseJson.data.user_joined_count })
                    }
                })
            }
        }
    }


    gotoStockLineup = (FixturedContestItem) => {
        if (!FixturedContestItem.collection_master_id && FixturedContestItem.collection_id) {
            FixturedContestItem['collection_master_id'] = FixturedContestItem.collection_id;
        } else if (!FixturedContestItem.collection_master_id) {
            FixturedContestItem['collection_master_id'] = this.state.LobyyData.collection_master_id || this.state.LobyyData.collection_id;
        }
        let cat_id = FixturedContestItem.category_id || this.state.LobyyData.category_id || this.state.contestData.category_id || '';
        FixturedContestItem['category_id'] = cat_id;
        let name = cat_id.toString() === "1" ? 'Daily' : cat_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let lineupPath;
        if (SELECTED_GAMET == GameType.StockFantasyEquity) {
            lineupPath = '/stock-fantasy-equity/lineup/' + name;
        }
        else {
            lineupPath = '/stock-fantasy/lineup/' + name;

        }
        this.props.history.push({
            pathname: lineupPath.toLowerCase(), state: {
                FixturedContest: FixturedContestItem,
                LobyyData: this.state.LobyyData || FixturedContestItem,
                resetIndex: 1,
                collection_master_id: FixturedContestItem.collection_master_id
            }
        })
    }

    CreateTeamClickEvent = (data, LobyyData) => {
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        }
        else {
            WSManager.clearLineup();
            if (this.state.isStockF) {
                this.gotoStockLineup(LobyyData)
            } else {
                let urlData = LobyyData;
                urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}

                let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                let lineupPath = '';
                if (urlData.home) {
                    let lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                    this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: this.state.LobyyData, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: this.state.LobyyData.is_reverse || false, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
                }
                else {
                    let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                    lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                    this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: this.state.LobyyData, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: this.state.LobyyData.is_reverse || false, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
                }
            }
        }
    }

    onSubmitBtnClick = (data) => {
        this.setState({ LobyyData: data, isReverseF: this.state.allowRevFantasy && data.is_reverse == 1 ? true : false })
        if (!WSManager.loggedIn()) {
            setTimeout(() => {
                this.props.history.push({ pathname: '/signup' })
                Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
            }, 10);
        }
        else {
            if (SELECTED_GAMET == GameType.DFS) {
                if ((this.state.contestData.total_user_joined != this.state.contestData.size) && (this.state.contestData.multiple_lineup > this.state.userJoinCount)) {
                    this.getUserLineUpListApi(this.state.contestData, true)
                }
            }
            else {
                this.submitAction(data)
            }
        }
    }

    submitAction = (data) => {
        if (checkBanState(this.state.contestData, CustomHeader)) {
            if (this.state.userTeamListSend.length > 0 && SELECTED_GAMET != GameType.LiveFantasy) {
                this.setState({ showContestDetail: false, showConfirmationPopUp: true })
            }
            else if (SELECTED_GAMET == GameType.LiveFantasy) {
                this.setState({ showContestDetail: false, showConfirmationPopUp: true })

            }
            else {
                WSManager.clearLineup();
                if (this.state.isStockF) {
                    this.gotoStockLineup(data)
                } else {
                    let urlData = data;
                    urlData = {...urlData, playing_announce: urlData.match_list[0].playing_announce}

                    let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                    dateformaturl = new Date(dateformaturl);
                    let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                    let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                    dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                    let lineupPath = '';
                    if (urlData.home) {
                        lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: data.is_reverse || false, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
                    }
                    else {
                        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                        lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: data.is_reverse || false, isSecIn: this.state.isSecIn, isPlayingAnnounced: urlData.playing_announce } })
                    }
                }
            }
        } else {
            this.ContestDetailHide();
        }
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {


        if ((SELECTED_GAMET != GameType.LiveFantasy) && (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "")) {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            if (checkBanState(dataFromConfirmPopUp.FixturedContestItem, CustomHeader)) {
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
                            WSManager.setPaymentCalledFrom("SelectCaptainList")
                            this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList', isStockF: this.state.isStockF } });

                        }
                        else {
                            this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow', isStockF: this.state.isStockF } })
                        }
                    }
                    else {
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isStockF: this.state.isStockF } });
                    }
                }

            }
            else {
                this.ConfirmatioPopUpHide();
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }
        if (SELECTED_GAMET != GameType.LiveFantasy) {
            param['lineup_master_id'] = dataFromConfirmPopUp.selectedTeam.value.lineup_master_id;
        }
        this.setState({ isLoaderShow: true })

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        let apiAct = SELECTED_GAMET == GameType.LiveFantasy ? joinContestLF : joinContest;
        if (this.state.isStockF) {
            apiAct = stockJoinContest
        }
        apiAct(param).then((responseJson) => {
            // let IsNetworkContest = dataFromConfirmPopUp.FixturedContestItem.is_network_contest == 1 ? true:false;
            // let apiCall = IsNetworkContest ? joinContestNetworkfantasy : joinContest;
            // apiCall(param).then((responseJson) => {
            //this.setState({ isLoaderShow: false })
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

                console.log(dataFromConfirmPopUp, '10. dataFromConfirmPopUp');
                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
                    contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                })

                this.ConfirmatioPopUpHide();
                // if(contestAccessType=='1' || isPrivate=='1'){
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
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


    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,

        });
    }

    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
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

    goToLobby = () => {
        // this.props.history.push({ pathname: '/' });
        if (SELECTED_GAMET == GameType.LiveFantasy) {
            this.props.history.push({ pathname: '/lobby' })
            return;
        }
        const { LobyyData, FixturedContest } = this.state;
        let dateformaturl = Utilities.getUtcToLocal(LobyyData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        let home = LobyyData.home || LobyyData.home;
        let away = LobyyData.away || LobyyData.away;

        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let contestListingPath = this.state.isSecIn ?
            '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + LobyyData.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET) + '&sit=' + btoa(true)
            : '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + LobyyData.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET)
        this.setState({ LobyyData: LobyyData });
        contestListingPath = contestListingPath.toLowerCase()
        this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: this.state.LobyyData, LobyyData: LobyyData, isFromPM: true, isJoinContestFlow: true } })
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    getUserLineUpListApi = async (CollectionData, isDFS) => {
        let param = {
            "sports_id": CollectionData.sports_id,
            "collection_master_id": CollectionData.collection_master_id,
        }
        this.setState({ isLoaderShow: true })
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        if (this.state.isStockF) {
            param = {
                "collection_id": CollectionData.collection_id || CollectionData.collection_master_id,
            }
        }
        console.log('SELECTED_GAMET', SELECTED_GAMET)
        var api_response_data = this.state.isStockF ? await getStockUserAllTeams(param, user_unique_id) : SELECTED_GAMET == GameType.DFS ? await getUserTeams(param, user_unique_id) : await getMultigameUserTeams(param, user_unique_id);
        if (this.state.isStockF) {
            api_response_data = api_response_data ? api_response_data.data : ''
        }
        if (api_response_data) {
            let tList = this.state.isSecIn ? _filter(api_response_data, (obj, idx) => {
                return obj.is_2nd_inning == "1";
            }) : this.state.isReverseF ? _filter(api_response_data, (obj, idx) => {
                return obj.is_reverse == "1";
            }) : _filter(api_response_data, (obj, idx) => {
                return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            })
            this.setState({
                TotalTeam: tList,
                userTeamListSend: tList
            })
            if (this.state.userTeamListSend) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {
                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })

                this.setState({ userTeamListSend: tempList }, () => {
                    if (isDFS) {
                        this.submitAction(CollectionData)
                    }
                });
            }
        }
    }

    render() {
        const {
            mLeagueCode,
            showContestDetail,
            activeTab,
            contestData,
            showConfirmationPopUp,
            userTeamListSend,
            LobyyData,
            showThankYouModal,
            showUJC,
            TotalTeam,
            aadharData
        } = this.state;

        const HeaderOption = {
            back: true,
            backForLeagueCode: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className={"web-container bg-white  private-contest-parent " + (!this.props.from && 'fixed-height')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.joincontest.title}</title>
                            <meta name="description" content={MetaData.joincontest.description} />
                            <meta name="keywords" content={MetaData.joincontest.keywords}></meta>
                        </Helmet>
                        {(!this.props.from) &&
                            <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} />
                        }
                        {/* <div>{AppLabels.JOIN_CONTEST}</div> */}
                        <form className="webcontainer-inner ">
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="have-a-code-label">
                                            {AppLabels.JOIN_PRIVATE_MSG1}<br /> {AppLabels.JOIN_PRIVATE_MSG2}
                                        </div>
                                        <div className="have-a-code-description">
                                            {AppLabels.JOIN_PRIVATE_TITLE}
                                        </div>
                                    </Col>
                                </Row>

                                <Row>
                                    <Col xs={12} >
                                        <FormGroup
                                            className='input-transparent'
                                            controlId="formBasicText"
                                        >
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='league-code'
                                                name='league-code'
                                                placeholder={AppLabels.ENTER_CONTEST_CODE}
                                                type='text'
                                                onChange={this.handleChange.bind(this)}
                                                value={mLeagueCode}
                                            />

                                        </FormGroup>
                                    </Col>
                                </Row>
                            </div>

                        </form>
                        {/* <div className="space-section"></div> */}
                        <div className={"join-contest-btn " + (mLeagueCode.length < 3 ? ' disabled' : '')} onClick={() => mLeagueCode.length > 3 && this.joinPrivateLeague()}>
                            <span>{AppLabels.JOIN_NOW}</span>
                            {/* <Button disabled={(mLeagueCode.length > 3 ? false : true) || clickOnce } onClick={() => this.joinPrivateLeague()} className="btn-block btm-fix-btn btn-primary"><span>{AppLabels.JOIN_PRIVATE_CONTEST}</span></Button> */}
                        </div>
                        {/* <div className="league-btn-section">
                        <Button disabled={(mLeagueCode.length > 3 ? false : true) || clickOnce } onClick={() => this.joinPrivateLeague()} className="btn-block btm-fix-btn btn-primary">{AppLabels.JOIN_CONTEST}</Button>
                    </div> */}

                        {showContestDetail && SELECTED_GAMET != GameType.LiveFantasy &&
                            <> {console.log('contestData', contestData)}
                                <ContestDetailModal
                                    {...this.props}
                                    showPCError={true}
                                    IsContestDetailShow={showContestDetail}
                                    onJoinBtnClick={this.onSubmitBtnClick}
                                    IsContestDetailHide={this.ContestDetailHide}
                                    OpenContestDetailFor={contestData}
                                    activeTabIndex={activeTab}
                                    LobyyData={contestData}
                                    isSecIn={this.state.isSecIn}
                                    isStockF={this.state.isStockF}
                                    fromLeagueCode={true}
                                    userJoinCount={this.state.userJoinCount}
                                    profileShow={aadharData}
                                />
                            </>
                        }
                        {showContestDetail && SELECTED_GAMET == GameType.LiveFantasy &&
                            <LFContestDetailsModal showPCError={true} IsContestDetailShow={showContestDetail} onJoinBtnClick={this.onSubmitBtnClick} IsContestDetailHide={this.ContestDetailHide} OpenContestDetailFor={contestData} activeTabIndex={activeTab} LobyyData={contestData} isSecIn={this.state.isSecIn} {...this.props} isStockF={this.state.isStockF} />
                        }

                        {showConfirmationPopUp && 
                             <ConfirmationPopup IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={contestData} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.CreateTeamClickEvent} lobbyDataToPopup={LobyyData} fromContestListingScreen={true} createdLineUp={''} isStockF={this.state.isStockF}  {...this.props} profileData={aadharData} />
                        }
                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} {...this.props}/>
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
        );
    }
}
