import React from 'react';
import { Row, Col, Button } from 'react-bootstrap';
import Images from '../../components/images';
import { Helmet } from "react-helmet";
import * as AppLabels from "../../helper/AppLabels";
import CustomHeader from '../../components/CustomHeader';
import { MyContext } from '../../InitialSetup/MyProvider';
import * as Constants from "../../helper/Constants";
import WSManager from "../../WSHelper/WSManager";
import { Utilities, _Map, _isUndefined, _filter, _cloneDeep, checkBanState } from '../../Utilities/Utilities';
import MetaData from "../../helper/MetaData";
import ls from 'local-storage';
import CountdownTimer from './../../views/CountDownTimer';
import { setValue, AppSelectedSport, preTeamsList, DARK_THEME_ENABLE } from '../../helper/Constants';
import { NoDataView } from '../../Component/CustomComponent';
import { createBrowserHistory } from 'history';
import WhatIsH2HChallengeModal from '../../Component/H2HChallenge/WhatIsH2HChallengeModal';
import { Sports, SportsIDs } from "../../JsonFiles";
import { getFixtureDetail, getFixtureDetailMultiGame, getFixtureContestList, getUserTeams, getMultigameUserTeams, joinContest, joinContestNetworkfantasy, joinContestWithMultiTeam, joinContestWithMultiTeamNF, getH2HContestList, getH2HBannerList, joinContestH2H, getH2HJoinedContestList, getUserAadharDetail, getMultigameMyContest, getMyContest } from "../../WSHelper/WSCallings";
import * as WSC from "../../WSHelper/WSConstants";
import { ConfirmationPopup, RulesScoringModal, Thankyou } from '../../Modals';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import H2HJoinedContestSlider from '../H2HChallenge/H2HJoinedContestSlider';
import H2HBannerSlider from '../H2HChallenge/H2HBannerSlider';

var globalThis = null;
const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
class H2hDetail extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            lineup_master_id: '',
            lineup_master_idArray: [],
            FixturedContest: [],
            sortContestList: [],
            ShimmerList: [1, 2, 3, 4, 5, 6],
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
            showRF: this.props && this.props.location && this.props.location.state && this.props.location.state.isReverseF || false,
            showRFNPP: false,
            allowRevFantasy: Utilities.getMasterData().a_reverse == '1',
            HeaderOption: {
                back: true,
                isPrimary: DARK_THEME_ENABLE ? false : true,
                fixture: true,
                filter: false,
                title: '',
                hideShadow: false,
                goBackLobby: !_isUndefined(props.location.state) ? props.location.state.isFromPM : false
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
            aadharData: '',
            isLoading: false,
            bn_state: localStorage.getItem('banned_on')
        }
    }

    componentDidMount() {
        this.headerRef.GetHeaderProps("lobbyheader", '', '', this.state.LobyyData ? this.state.LobyyData : this.props.location.state.LobyyData);
        this.apiCallH2HContest(this.props.location.state.matchParam)
        globalThis = this;
        this.h2hCallangeData()
    }

    apiCallH2HContest = (CollectionData) => {
        let collMasterId = CollectionData.collection_master_id ? CollectionData.collection_master_id : this.props.match.params.collection_master_id
        let SID = CollectionData.sportsId ? Sports[CollectionData.sportsId] : Sports[this.props.match.params.sportsId]
        this.setState({
            isLoading: true
        })
        let param = {
            "sports_id": SID,
            "collection_master_id": collMasterId,
        }
        getH2HContestList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tmpArray = responseJson.data

                this.setState({
                    H2Hchallange: responseJson.data ? responseJson.data : [],
                    allContestData: responseJson.data ? responseJson.data : [],
                    sortContestList: tmpArray,
                    isLoading: false
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
                }, () => {
                    if (WSManager.loggedIn() && this.state.H2Hchallange.length > 0) {
                        this.getUserLineUpListApi(collMasterId);
                    }
                })

            }
        })

    }


    getPrizeAmount = (prize_data, status) => {
        let prizeAmount = this.getWinCalculation(prize_data.prize_distibution_detail);
        return (
            <React.Fragment>
                {
                    prizeAmount.is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className={status == 1 ? "white-dv" : "contest-prizes"}>
                            {Utilities.getMasterData().currency_code}
                            {Utilities.getPrizeInWordFormat(prizeAmount.real)}
                        </span>
                        : prizeAmount.is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <div className={status == 1 ? "white-dv" : "contest-listing-prizes"} ><i style={{ marginLeft: status == 1 ? 4 : '' }} className="icon-bonus" />{Utilities.numberWithCommas(parseFloat(prizeAmount.bonus).toFixed(0))}</div>
                            : prizeAmount.is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ display: 'inlineBlock' }}> <img style={{ height: 15, width: 15, marginLeft: status == 1 ? 4 : '' }} className="img-coin" alt='' src={Images.IC_COIN} />{Utilities.numberWithCommas(parseFloat(prizeAmount.point).toFixed(0))}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }

    handleJson = (data) => {
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
    }

    getWinCalculation = (pdata) => {
        let prize_data = this.handleJson(pdata)
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

    H2hModalShow = () => {
        this.setState({
            showH2hModal: true,
        });
    }

    H2hModalHide = () => {
        this.setState({
            showH2hModal: false,
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

    redirectToMyTeams() {
        let urlData = this.state.LobyyData;

        WSManager.clearLineup()
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
        if (urlData.home) {
            this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: Constants.AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
        }
        else {
            let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
            this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, current_sport: Constants.AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
        }

        // code for guru
        // if (this.state.userTeamListSend.length > 0) {
        //     let urlParams = '';
        //     if (this.state.LobyyData && this.state.LobyyData.home) {
        //         urlParams = Utilities.setUrlParams(this.state.LobyyData);
        //     }
        //     else {
        //         urlParams = Utilities.replaceAll(this.state.LobyyData.collection_name, ' ', '_').toLowerCase();
        //     }

        //     let sportsId = Utilities.getSelectedSportsForUrl();
        //     let collection_master_id = this.state.LobyyData.collection_master_id;
        //     let keyName = 'my-teams' + sportsId + collection_master_id;
        //     if (this.state.isNewCJoined) {
        //         preTeamsList[keyName] = [];
        //     } else {
        //         preTeamsList[keyName] = this.state.TeamList;
        //     }
        //     this.props.history.push({ pathname: "/" + sportsId + '/my-teams/' + collection_master_id + "/" + urlParams, state: { LobyyData: this.state.LobyyData, isReverseF: this.state.showRF, TotalTeam: this.state.TotalTeam } });

        // } else {
        //     let urlData = this.state.LobyyData;
        //     WSManager.clearLineup()
        //     let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        //     dateformaturl = new Date(dateformaturl);
        //     dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();
        //     if (urlData.home) {
        //         this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, isReverseF: this.state.showRF } })
        //     }
        //     else {
        //         let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
        //         this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: this.state.LobyyData, LobyyData: this.state.LobyyData, from: 'MyTeams', isFromMyTeams: true, isFrom: "MyTeams", resetIndex: 1, isReverseF: this.state.showRF } })
        //     }
        // }
    }


    aadharConfirmation = () => {

        let { aadharData } = this.state
        if (WSManager.loggedIn()) {
            if (aadharData.aadhar_status == "0" && aadharData.aadhar_id != "0") {
                Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
                this.props.history.push({ pathname: '/aadhar-verification' })
            }
            else {
                Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
                this.props.history.push({ pathname: '/aadhar-verification' })
            }
        } else {
            this.goToSignup()
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

    /**
    * @description Method to open signup screen for guest user share contest click event
    */
    goToSignup = () => {
        this.props.history.push("/signup")
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
                else if (this.state.showRF && this.state.allowRevFantasy) {
                    this.showRFNotPlayingModal(FixturedContestItem, teamListData)
                }
                else {
                    this.goToLineup(FixturedContestItem)
                }
            }
            WSManager.setFromConfirmPopupAddFunds(false);
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

    goToLineup = (FixturedContestItem) => {
        let urlData = this.state.LobyyData;
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
            lineupPath = '/lineup/' + Utilities.replaceAll(urlData.collection_name, ' ', '_')
        }

        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: FixturedContestItem, LobyyData: this.state.LobyyData, resetIndex: 1, isCollectionEnable: (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && this.state.LobyyData.match_list && this.state.LobyyData.match_list.length > 1), current_sport: Constants.AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning, isFrom: 'MyTeams', aadharData: this.state.aadharData } })
    }


    getUserLineUpListApi = async (CMID) => {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": CMID,
        }
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = Constants.SELECTED_GAMET == Constants.GameType.DFS ? await getUserTeams(param, user_unique_id) : await getMultigameUserTeams(param, user_unique_id);
        if (api_response_data) {
            // let tList = this.state.isSecondInning ? _filter(api_response_data, (obj, idx) => {
            //     return obj.is_2nd_inning == "1";
            // }) : this.state.showRF ? _filter(api_response_data, (obj, idx) => {
            //     return obj.is_reverse == "1";
            // }) : _filter(api_response_data, (obj, idx) => {
            //     return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            // })
            let tList = _filter(api_response_data, (obj, idx) => {
                return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            })
            this.setState({
                TotalTeam: api_response_data,
                TeamList: tList,
                userTeamListSend: tList,
            })
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
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd }, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning });
            }
        }
    }


    CallJoinGameApi(dataFromConfirmPopUp) {
        let IsNetworkContest = this.state.FixtureData.is_network_contest == 1;
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true : false;
        let ApiAction = IsNetworkContest ? joinContestNetworkfantasy : isH2h ? joinContestH2H : joinContest;
        let param = {
            "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
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
                // if (isH2h) {
                this.h2hCallangeData()
                Utilities.setH2hData(dataFromConfirmPopUp, responseJson.data.contest_id)
                // }
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

    h2hCallangeData = () => {
        let matchParam = this.props.match.params;
        this.apiCallH2HContest(matchParam)
        this.apiCallH2HBanner()
        if (WSManager.loggedIn()) {
            this.apiCallH2HJoinedContestList(matchParam)

        }

        // }
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

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            let urlData = this.state.LobyyData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

            if (urlData.home) {
                this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
            }
            else {
                let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: Constants.AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecondInning, aadharData: this.state.aadharData } })
            }
        }
    }

    goToLobby = () => {
        this.setState({
            showThankYouModal: false,
            // lineup_master_id:''
        });
        const matchParam = this.props.location.state.matchParam
        this.apiCallH2HContest(matchParam);
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    geoValidate = (event, item, bn_state) => {
        let globalThis = this;
        if (WSManager.loggedIn()) {
            if (bn_state == 1 || bn_state == 2) {
                if (item.entry_fee == '0') {
                    globalThis.check(event, item, bn_state)
                }
                else {
                    Utilities.bannedStateToast(bn_state)
                }
            }
            else {
                globalThis.check(event, item, bn_state)
            }
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }

    }

    geoValidate = (event, item, bn_state) => {
        let globalThis = this;

        if (bn_state == 1 || bn_state == 2) {
            if (item.entry_fee == '0') {
                globalThis.check(event, item, bn_state)
            }
            else {
                Utilities.bannedStateToast(bn_state)
            }
        }
        if (bn_state == 0) {
            globalThis.check(event, item, bn_state)
        }
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
            FixturedContestTotal,
            SortContestTotal,
            showThankYouModal,
            hasMore,
            LoaderShow,
            showGroupView,
            showCollectionInfo,
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
            showRF,
            allowRevFantasy,
            showRFNPP,
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
            FixtureData,
            bn_state,
        } = this.state;
        let showDFSMulti = false

        const FitlerOptions = {
            showContestListFitler: showContestListFitler,
            entry_fee_from: entry_fee_from,
            entry_fee_to: entry_fee_to,
            participants_from: participants_from,
            participants_to: participants_to,
            prizepool_from: prizepool_from,
            prizepool_to: prizepool_to,

            entry_fee_min: 0,
            entry_fee_max: entry_fee_max,
            participants_min: 0,
            participants_max: participants_max,
            prizepool_min: 0,
            prizepool_max: prizepool_max,
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
                            isHSI={isSecondInning} />

                        <div style={LobyyData.ldb == '1' ? { marginTop: 84 } : {}} className={"webcontainer-inner" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? ' webcontainer-MT' : '')}>
                            <div className={"primary-overlay" + (LobyyData.ldb == '1' ? ' ldb' : '')}>
                                {
                                    LobyyData.ldb == '1' && <div className="ldb-strip primary" onClick={() => this.showLeaderboardModal()}><i className="icon-leaderboard" /><span>{AppLabels.LEADERBOARD} {AppLabels.AVAILABLE}</span></div>
                                }
                            </div>
                            <div className="fantasy-rules-sec">
                                {
                                    isSecondInning ?
                                        <span className="text-uppercase">
                                            {AppLabels.SEC_INNING}
                                            <span className='time-sec'>
                                                {
                                                    LobyyData.game_starts_in && (Utilities.minuteDiffValue({ date: LobyyData.game_starts_in }) <= 0) &&
                                                    <CountdownTimer timerCallback={() => console.log('')} deadlineTimeStamp={LobyyData.game_starts_in} />
                                                }
                                            </span>
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
                                        <i className="icon-question mr-1"></i>
                                        {AppLabels.HOW_TO_PLAY_FREE}
                                    </a>
                                }
                            </div>
                            <div >
                                {
                                    this.state.H2HJoinedContestList && this.state.H2HJoinedContestList.length > 0 ?
                                        <div>
                                            {/* <div className="top-section-heading">
                                                {AppLabels.MY_H2H_CHALLENGES}
                                                <a onClick={() => this.handleTab(1)} >{AppLabels.VIEW} {AppLabels.ALL}</a>
                                            </div> */}
                                            <H2HJoinedContestSlider
                                                JoineContestData={this.state.H2HJoinedContestList}
                                                getOpponentDetail={this.getOpponentDetail}

                                            />
                                        </div>

                                        :
                                        this.state.H2HBannerList && this.state.H2HBannerList.length > 0 &&
                                        <div>

                                            <H2HBannerSlider
                                                BannerData={this.state.H2HBannerList}

                                            />
                                        </div>
                                }

                                <div className='upcoming-contest-title h2h'>{AppLabels.UPCOMING_H2H_CHALLENGES}</div>
                                <Row className='banner-c'>
                                    {
                                        isLoading && this.state.H2Hchallange && this.state.H2Hchallange.length == 0 ?
                                            <div className='h2h-clist-shimmer'>
                                                {
                                                    ShimmerList.map((item, index) => {
                                                        return (
                                                            <Shimmer key={index} index={index} />
                                                        )
                                                    })
                                                }
                                            </div>
                                            :
                                            <>
                                                {this.state.H2Hchallange && this.state.H2Hchallange.length > 0 ?
                                                    _Map(this.state.H2Hchallange, (item, index) => {
                                                        return (
                                                            <Col onClick={(event) => this.geoValidate(event, item, bn_state)}
                                                                className='col-container abc' key={index} sm={6}>
                                                                <div className={'main-conatiner' + ((bn_state == 1 || bn_state == 2) ?
                                                                    (item.entry_fee != '0') ? ' geo-disabled' : ' ' : '')}>
                                                                    <div className='contanier-inner'>
                                                                        <i className={"icon-h2h-logo image-icon" + (item.status == 1 ? ' orange-c' : item.status == 2 ? ' blue-c' : item.status == 3 ? ' yellow-c' : item.status == 4 ? ' ' : '')}></i>

                                                                        <div className={"label-name" + (item.contest_title && item.contest_title != '' ? ' contest-title' : '')}>
                                                                            {item.contest_title != '' ? item.contest_title :
                                                                                <div className='clr3'>
                                                                                    {AppLabels.WIN} {''}{this.getPrizeAmount(item, 1)}
                                                                                </div>

                                                                            }

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
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            MESSAGE_1={AppLabels.NO_FIXTURES_MSG1}
                                                            MESSAGE_2={AppLabels.NO_FIXTURES_MSG3}
                                                            BUTTON_TEXT={AppLabels.GO_BACK_TO_LOBBY}
                                                            onClick={this.goBack}
                                                        />
                                                    </div>

                                                }
                                            </>
                                    }
                                </Row>
                                <div className={'bottom-conatiner h2h-bottom' + (!WSManager.loggedIn() ? ' not-loged-in' : '')}>
                                    <div className='inner-c'>
                                        <div className='count-c'>
                                            <div className='count-value'>{Utilities.getMasterData().h2h_data.climit}</div>
                                            <div className='join-text'>{AppLabels.H2H_INFO1} {Utilities.getMasterData().h2h_data.climit} {AppLabels.H2H_INFO2}</div>

                                        </div>
                                        <div onClick={() => this.H2hModalShow()} className='whats-h-2-h'>{AppLabels.WHATS_H2H}</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        {
                            WSManager.loggedIn() && isLoading &&
                            <React.Fragment>
                                {
                                    activeContestTab == 0 && userTeamListSend.length < parseInt(Utilities.getMasterData().a_teams) &&
                                    <Button onClick={() => this.redirectToMyTeams()} className="btn-block btn-primary bottom">{AppLabels.CREATE_YOUR_TEAM}</Button>
                                }

                            </React.Fragment>
                        }
                        {showH2hModal &&
                            <WhatIsH2HChallengeModal
                                {...this.props} ModalData={{
                                    mShow: this.H2hModalShow,
                                    mHide: this.H2hModalHide
                                }}
                            />
                        }
                        {showRulesModal &&
                            <RulesScoringModal MShow={showRulesModal} MHide={this.hideRulesModal} />
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
                            showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.goToLobby}
                                seeMyContestEvent={this.seeMyContest} />
                        }
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
                    </div>
                    <div className="shimmer-bottom-view">
                        <div className="progress-bar-default">
                            <div className="d-flex justify-content-between">
                                <Skeleton height={4} width={100} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SkeletonTheme>
    )
}

export default H2hDetail;