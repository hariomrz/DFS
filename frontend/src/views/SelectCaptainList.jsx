import React from 'react';
import ls from 'local-storage';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { inputStyleLeft, darkInputStyleLeft } from '../helper/input-style';
import FloatingLabel from 'floating-label-react';
import Validation from '../helper/Validation';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels }  from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import ConfirmationPopup from '../Modals/ConfirmationPopup';
import Thankyou from '../Modals/Thankyou';
import CustomHeader from '../components/CustomHeader';
import FieldView from "./FieldView";
import UnableJoinContest from '../Modals/UnableJoinContest';
import { Utilities, _isUndefined, _indexOf, _Map, _isEmpty, _filter, _cloneDeep, parseURLDate, checkBanState } from '../Utilities/Utilities';
import { SportsIDs } from "../JsonFiles";
import { AppSelectedSport, globalLineupData, preTeamsList, SELECTED_GAMET, GameType, EnableBuyCoin, BanStateEnabled, setValue } from '../helper/Constants';
import { getNewTeamName, processLineup, joinContest, getUserLineUps, switchTeamContest, joinContestNetworkfantasy, switchTeamContestNF, joinContestH2H, getUserAadharDetail, getBannedStats } from '../WSHelper/WSCallings';
import { DARK_THEME_ENABLE } from "../helper/Constants";
import BoosterGameOnModal from '../Component/Booster/BoosterGameOnModal';
var masterDataResponse = null;

export default class SelectCaptainList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineupArr: this.props.location.state.SelectedLineup,
            MasterData: this.props.location.state.MasterData,
            LobyyData: this.props.location.state.LobyyData,
            allPosition: this.props.location.state.MasterData.all_position,
            allRosterList: this.props.location.state.allRosterList,
            IsChanged: false,
            showConfirmationPopUp: false,
            TotalTeam: [],
            userTeamListSend: [],
            FixturedContest: this.props.location.state.FixturedContest,
            isFrom: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.isFrom : !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'contestJoin' ? this.props.location.state.isFrom : this.props.location.state.isFrom == 'MyTeams' ? this.props.location.state.isFrom : "",
            teamData: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.team : '',
            teamName: (this.props.location.state.teamitem && this.props.location.state.teamitem.team_name != '') ? this.props.location.state.teamitem.team_name : (this.props.location.state.isClone ? '' : (!_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' || this.props.location.state.isFrom == 'MyContest' ? (this.props.location.state.team && this.props.location.state.team.team_name) : this.props.location.state.teamName)),

            lineupMasterdId: '',
            showThankYouModal: false,
            rootDataItem: !_isUndefined(this.props.location.state.isFrom) && this.props.location.state.isFrom == 'editView' ? this.props.location.state.rootDataItem : !_isUndefined(this.props.location.state.FixturedContest) ? this.props.location.state.FixturedContest : "",
            sportsSelected: AppSelectedSport,
            isFromMyTeams: this.props.location.state.isFromMyTeams ? this.props.location.state.isFromMyTeams : false,
            ifFromSwitchTeamModal: !_isUndefined(this.props.location.state.ifFromSwitchTeamModal) ? this.props.location.state.ifFromSwitchTeamModal : false,
            isLoading: false,
            clickOnce: false,
            sort_field: 'salary',//fantasy_score
            sort_order: 'DESC',//ASC
            rosterList: [],
            isCategory: true,
            isClone: !_isUndefined(this.props.location.state.isClone) ? this.props.location.state.isClone : false,
            isTeamNameChanged: true,
            showFieldV: false,
            showUJC: false,
            isSecIn: false,
            showBoosterModal: false,
            booster_id: '0',
            isBenchEnable: Utilities.getMasterData().bench_player == '1',
            isEditView: this.props.location.state.isEditView || false,
            benchArr: this.props.location.state.benchArr || [],
            isPlayingAnnounced: this.props.location.state.isPlayingAnnounced || this.props.location.state.LobyyData.playing_announce || 0,
            isCNT: this.props.location.state.isCNT || false,
            isDFSMulti: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ? true : false,
            showDFSMulti: SELECTED_GAMET == GameType.DFS && Utilities.getMasterData().dfs_multi == 1 && this.props.location.state.LobyyData && this.props.location.state.LobyyData.season_game_count > 1 ? true : false,
            isShare: this.props.location.state && this.props.location.state.isShare ? this.props.location.state.isShare : false,
            aadharData: '',
            bn_state: localStorage.getItem('banned_on'),
            geoPlayFree: localStorage.getItem('geoPlayFree')
        }
        this.headerRef = React.createRef();

    }

    showUJC = (data) => {
        this.setState({
            showUJC: true,
        });
    }

    hideUJC = () => {
        this.setState({
            showUJC: false,
        }, () => {
            this.props.history.push({ pathname: '/' });
        });
    }
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }
    filterLineypArrByPosition = (player) => {
        let arrPositionOfSelectedPlayer = this.state.lineupArr.filter(function (item) {
            return item.position == player.position
        })

        return arrPositionOfSelectedPlayer
    }


    ChangePlayerRole = (role, player) => {
        let lineupArr = this.state.lineupArr;
        _Map(lineupArr, (item) => {
            if (item.captain == role || item.captain == 0) {
                item.captain = 0;
            }
            return item;
        })
        let index = _indexOf(lineupArr, player);
        lineupArr[index].captain = (role === 1) ? "1" : "2";
        this.setState({ lineupArr })
        if (AppSelectedSport == SportsIDs.MOTORSPORTS || AppSelectedSport == SportsIDs.tennis ) {
            this.setState({ IsChanged: true })
        } else 
        if (AppSelectedSport == SportsIDs.badminton) {
            this.setState({ IsChanged: true })
        }
        else if ((role === 1 && this.returnPlayerRole(2, lineupArr)) || (role === 2 && this.returnPlayerRole(1, lineupArr))) {
            this.setState({ IsChanged: true })
        }
        else if ((this.state.MasterData.c_point > 0 && this.state.MasterData.vc_point <= 0) || (this.state.MasterData.vc_point > 0 && this.state.MasterData.c_point <= 0)) {
            this.setState({ IsChanged: true })
        }
        else {
            this.setState({ IsChanged: false })
        }

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'selectcaptain');



    }

    returnPlayerRole = (role, lineupArr) => {
        for (var player of lineupArr) {
            if (player.captain == role) {
                return true
            }
        }
        return false
    }

    PlayerRoleClass = (role, player) => {
        let lineupArr = this.state.lineupArr;
        let LineupFilter = _filter(lineupArr, (o) => { return (o.player_uid == player.player_uid && o.captain == role) });
        return LineupFilter.length == 1;
    }

    aadharDataCheck = (event) => {
        let { rootDataItem } = this.state;

        // if (this.props.location.state && this.props.location.state.aadharData) {
        //     console.log('ifffffffff')
        //     let aadarData = {
        //         'aadhar_status': WSManager.getProfile().aadhar_status,
        //         "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
        //     }
        //     this.setState({ aadharData: aadarData });

        //     this.SubmitLineup(event)

        // }
        // else {
        // console.log('elseeeeeeeeeeeee')
        // if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
        //     if (WSManager.getProfile().aadhar_status != 1 && rootDataItem.entry_fee != '0') {
        //         getUserAadharDetail().then((responseJson) => {
        //             if (responseJson && responseJson.response_code == WSC.successCode) {
        //                 this.setState({ aadharData: responseJson.data }, () => {
        //                     WSManager.updateProfile(this.state.aadharData)
        //                     this.aadharConfirmation()
        //                 });
        //             }
        //         })
        //     }
        //     else {
        //         let aadarData = {
        //             'aadhar_status': WSManager.getProfile().aadhar_status,
        //             "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
        //         }
        //         this.setState({ aadharData: aadarData });

        //         this.SubmitLineup(event)

        //     }
        // }
        // else {
        this.SubmitLineup(event)
        //     }
        // }
    }
    // CheckAdharStatus = () => {
    //     let { bn_state, playFreeContest } = this.state;
    //     let { rootDataItem } = this.state;

    //     if (Utilities.getMasterData().bs_a == 1 && bn_state != '') {
    //         if (playFreeContest == 'true' && rootDataItem.entry_fee == '0') {
    //             this.aadharDataCheck();
    //         }
    //         // else {
    //         //     this.bannedStateToast()
    //         // }
    //     }
    //     else {
    //         this.aadharDataCheck();
    //     }
    // }

    SubmitLineup = (event) => {
        const { MasterData } = this.state
        if (this.checkButtonEnable()) {
            this.setState({ clickOnce: true })
            if (this.isLoading) {
                return true;
            }

            let tmpLineupArray = [];
            let cap_ptID = '';
            let vcap_ptID = '';

            _Map(this.state.lineupArr, (item) => {
                let ptID = item.player_team_id;
                if (item.captain == 1) {
                    cap_ptID = ptID
                }
                if (item.captain == 2) {
                    vcap_ptID = ptID
                }
                tmpLineupArray.push(ptID)
            });
            let param = {
                "league_id": this.state.LobyyData.league_id ? this.state.LobyyData.league_id : this.state.FixturedContest.league_id,
                "sports_id": AppSelectedSport,
                "team_name": this.state.teamName,
                "collection_master_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
                "players": tmpLineupArray,
                "c_id": (MasterData.c_point == 0 && MasterData.vc_point == 0) ? '' : cap_ptID,
                "vc_id": (MasterData.c_point == 0 && MasterData.vc_point == 0) ? '' : vcap_ptID,
                "lineup_master_id": this.state.isClone ? '' : (this.props.location.state.teamitem ? this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id : (this.state.teamData.lineup_master_id ? this.state.teamData.lineup_master_id : this.state.lineupMasterdId))
            }


            if (this.state.isSecIn) {
                param['is_2nd_inning'] = 1
            }

            this.setState({
                isLoading: true
            });

            let is_tour_game = this.state.LobyyData && this.state.LobyyData.is_tour_game == 1 ? true : false;
            processLineup(param).then((responseJson) => {
                this.setState({
                    isLoading: false
                });
                if (responseJson.response_code == WSC.successCode) {
                    let keyName = 'my-teams' + Utilities.getSelectedSportsForUrl() + param.collection_master_id;
                    let isBoosterKey = this.state.LobyyData.booster != undefined && this.state.LobyyData.booster != '' ? true : this.state.LobyyData.is_booster != undefined && this.state.LobyyData.is_booster == '1' ? true : false;
                    let IsNotNF = this.props && this.props.location && this.props.location.state && this.props.location.state.FixturedContest && this.props.location.state.FixturedContest.is_network_contest != 1 ? true : this.state.LobyyData.is_network_contest != undefined && this.state.LobyyData.is_network_contest != 1 ? true : false;
                    let isBoosterEnable = Utilities.getMasterData().booster == 1 && isBoosterKey && IsNotNF && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS && !this.state.showDFSMulti ? true : false;
                    preTeamsList[keyName] = [];
                    if (responseJson.data.lineup_master_id) {
                        let keyy = responseJson.data.lineup_master_id + param.collection_master_id + 'lineup';
                        globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                        this.setState({ lineupMasterdId: responseJson.data.lineup_master_id })
                    } else {
                        let keyy = param.lineup_master_id + param.collection_master_id + 'lineup';
                        globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                    }

                    if (
                        SELECTED_GAMET == GameType.DFS && 
                        this.state.isBenchEnable && 
                        !this.state.isSecIn && 
                        !this.state.showDFSMulti &&
                        !is_tour_game &&
                        (   this.state.isPlayingAnnounced == 0 ||
                            (
                                this.state.isPlayingAnnounced == 1 && 
                                !this.state.isClone &&
                                !this.state.isCNT && 
                                this.state.isFrom == 'editView' && 
                                this.state.benchArr && 
                                this.state.benchArr.length > 0
                            )
                        )
                    ) {
                        ls.set('Lineup_data', this.state.lineupArr)
                        let urlData = _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData;
                        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
                        dateformaturl = new Date(dateformaturl);
                        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
                        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

                        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_').toLowerCase();

                        let CMID = this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id;

                        let LMID = responseJson.data.lineup_master_id || param.lineup_master_id
                        let benchPath = '/bench-selection/' + LMID + '/' + CMID + '/' + pathurl + "-" + dateformaturl;

                        this.props.history.push({ pathname: benchPath, state: { from: 'SelectCaptain', LobyyData: this.state.LobyyData, FixturedContest: this.state.FixturedContest, sports_id: AppSelectedSport, teamName: this.state.teamName, collection_master_id: CMID, players: tmpLineupArray, c_id: cap_ptID, vc_id: vcap_ptID, MasterData: this.state.MasterData, selLineupArr: this.state.lineupArr, allRosterList: this.state.allRosterList, lineupMasterdId: LMID, isFrom: this.state.isFrom, isFromMyTeams: this.state.isFromMyTeams, TeamMyContestData: this.state.teamData, isBoosterEnable: isBoosterEnable,  isSecIn: this.state.isSecIn, benchArr: this.state.benchArr, isEditView: this.state.isEditView, isClone: this.state.isClone, isPlayingAnnounced: this.state.isPlayingAnnounced, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, lineup_master_contest_id: this.state.ifFromSwitchTeamModal ? this.props.location.state.lineup_master_contest_id : 0, isShare: this.state.isShare } });
                    }


                    else {
                        if (this.state.isFrom == 'editView' && !this.state.isFromMyTeams) {
                            Utilities.showToast(responseJson.message, 1000);
                            if (SELECTED_GAMET == GameType.Free2Play) {
                                let dateformaturl = parseURLDate(this.state.LobyyData.season_scheduled_date);
                                let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/free-to-play/' + this.state.LobyyData.collection_master_id + '/' + this.state.LobyyData.league_name + "-" + this.state.LobyyData.home + "-vs-" + this.state.LobyyData.away + "-" + dateformaturl + "/" + SELECTED_GAMET + "/" + this.state.LobyyData.season_game_uid + "/" + this.state.LobyyData.contest_id;
                                this.props.history.push({ pathname: contestListingPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: this.state.LobyyData, lineupPath: contestListingPath, TeamSubmit: true} })
                            }
                            else {
                                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
                            }
                        }
                        else if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {
                            Utilities.showToast(responseJson.message, 1000);
                            if (isBoosterEnable && this.state.isFrom != "editView") {
                                this.BoosterModalShow()
                            } else if (isBoosterEnable && this.state.isClone) {
                                this.BoosterModalShow()
                            }
                            else {
                                this.skipBoosterAndContinue()
                            }
                        }
                        else if (this.state.ifFromSwitchTeamModal) {

                            if (isBoosterEnable) {
                                this.BoosterModalShow()

                            } else {
                                Utilities.showToast(responseJson.message, 1000);
                                this.switchTeam(this.state.FixturedContest, this.state.FixturedContest.is_network_contest == 1 ? responseJson.data.network_lineup_master_id : responseJson.data.lineup_master_id, this.props.location.state.lineup_master_contest_id);

                            }
                        }
                        else {
                            if (checkBanState(this.state.FixturedContest, CustomHeader, 'CAP', this.state.isShare)) {
                                // if(checkBanState(this.state.FixturedContest,CustomHeader, 'CAP') || this.state.isShare){
                                if (isBoosterEnable) {
                                    this.BoosterModalShow()
                                } else {
                                    this.getUserLineUpListApi();

                                }

                            }
                        }
                    }
                    //ls.remove('Lineup_data')

                    //Analytics Calling 
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, 'confirmteam');

                }
                else if (responseJson.response_code == WSC.BannedStateCode) {
                    Utilities.bannedStateToast(this.state.bn_state)
                }
                else{
                    Utilities.showToast(responseJson.message, 3000);
                }
                this.setState({ clickOnce: false })
            })
        }
    }

    switchTeam(FixturedContest, lineup_master_id, lineup_master_contest_id) {
        let param = {
            "sports_id": AppSelectedSport,
            "contest_id": FixturedContest.contest_id,
            "lineup_master_id": lineup_master_id,
            "lineup_master_contest_id": lineup_master_contest_id,
        }

        let apiCall = FixturedContest.is_network_contest == 1 ? switchTeamContestNF : switchTeamContest
        this.setState({ isLoaderShow: true })
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain'} });
                WSManager.clearLineup();
            }
            else{
                Utilities.showToast(responseJson.message, 3000)
            }
        })

    }

    getTeamName() {
        if (!this.state.teamName) {
            let param = {
                "collection_master_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
            }
            getNewTeamName(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({ teamName: responseJson.data.team_name, isTeamNameChanged: false }, () => {
                        this.setState({ isTeamNameChanged: true })
                    })
                }
                else{
                    Utilities.showToast(responseJson.message, 3000)
                }
            })
        }
    }

    getUserLineUpListApi() {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
            "league_id": this.state.LobyyData.league_id
        }
        this.setState({ isLoaderShow: true })
        getUserLineUps(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tList = this.state.isSecIn ? _filter(responseJson.data, (obj, idx) => {
                    return obj.is_2nd_inning == "1";
                }) : _filter(responseJson.data, (obj, idx) => {
                    return (obj.is_2nd_inning != "1")
                })
                this.setState({
                    showConfirmationPopUp: true,
                    TotalTeam: tList,//responseJson.data,
                    userTeamListSend: tList 
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
            else{
                Utilities.showToast(responseJson.message, 3000)
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

    aadhaarCheckValidation = (dataFromConfirmPopUp, context) => {
        if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        }
        else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if (SELECTED_GAMET == GameType.Free2Play) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
            else if (
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
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'SelectCaptainList' } });

                    }
                    else {
                        // Utilities.showToast('Not enough coins', 1000);
                        this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
                    }
                }
                else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true,  isSecIn: this.state.isSecIn });
                }

                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
            }
        }
    }


    ConfirmEvent = (dataFromConfirmPopUp, context, FixturedContestItem) => {
        let aadhar_data = localStorage.getItem('profile')
        // Constants.setValue.SetRFContestId(FixturedContestItem.collection_master_id);
        // if (Utilities.getMasterData().a_aadhar == "1") {
        //     if (WSManager.getProfile().aadhar_status == '1') {
        //         this.aadhaarCheckValidation(dataFromConfirmPopUp, context)
        //     }
        //     else {
        //         Utilities.aadharConfirmation(aadhar_data, this.props)
        //     }
        // }
        // else {
        this.aadhaarCheckValidation(dataFromConfirmPopUp, context)
        // }
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        this.props.history.push({ pathname: '/lineup', state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmLobby, current_sport: AppSelectedSport,isSecIn: this.state.isSecIn } })
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        var contestId = SELECTED_GAMET == GameType.Free2Play ? this.state.LobyyData.contest_id : dataFromConfirmPopUp.FixturedContestItem.contest_id
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true : false;
        let param = {
            "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : contestId,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        this.setState({ isLoaderShow: true })
        let IsNetworkContest = this.props && this.props.location && this.props.location.state && this.props.location.state.FixturedContest && this.props.location.state.FixturedContest.is_network_contest == 1 ? true : this.state.LobyyData.is_network_contest != undefined && this.state.LobyyData.is_network_contest == 1 ? true : false;
        if (this.state.isSecIn) {
            param['is_2nd_inning'] = 1
        }

        let apiCall = IsNetworkContest ? joinContestNetworkfantasy : isH2h ? joinContestH2H : joinContest;
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (isH2h) {
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
                else if (responseJson.response_code == 403) {
                    Utilities.showToast(AppLabels.USER_FROM_BANNED_STATE_ARE_NOT_ALLOWED, 1000);
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })

        //Analytics Calling 
        WSManager.googleTrack(WSC.GA_PROFILE_ID, 'joingame');


    }
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value }, this.validateForm);
    }


    banStateChanges = () => {
        getBannedStats().then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let Data = Utilities.getMasterData();
                Data['banned_state'] = responseJson.data;
                let banStates = Object.keys(responseJson.data || {});
                setValue.setBanStateEnabled(banStates.length > 0);
                Utilities.setMasterData(Data);
                ls.set('bslist', responseJson.data);
                ls.set('bslistTime', { date: Date.now() });
            }
            else{
                Utilities.showToast(responseJson.message, 3000)
            }
        })
    }
    componentWillUnmount() {
        // ls.remove('Lineup_data')
    }
    
    componentDidMount = () => {



        if (this.state.isShare && Utilities.getMasterData().bs_a == 1 && WSManager.getProfile().master_state_id == null) {
            this.banStateChanges()
        }

        masterDataResponse = Utilities.getMasterData()

        setTimeout(() => {
            if (!_isEmpty(this.state.lineupArr) && this.headerRef && this.headerRef.current) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) || _isUndefined(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest);
            }
        }, 500)
        if (this.state.lineupArr && ls.get('Lineup_data') && ls.get('Lineup_data').length > 0 && this.state.lineupArr != ls.get('Lineup_data') && SELECTED_GAMET == GameType.DFS && this.state.isBenchEnable && !this.state.isSecIn) {
            this.setState({
                lineupArr: ls.get('Lineup_data')
            })
        }
        if (this.state.isFrom == 'editView') {
            if (this.state.isClone) {
                this.getTeamName()
            }
            this.setState({ IsChanged: true })
        }
        else {
            this.getTeamName()
        }
        if (BanStateEnabled && !WSManager.getProfile().master_state_id) {
            checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')
        }
        if (this.state.isBenchEnable && ls.get('bench_data') && !this.state.isEditView && !this.state.isSecIn && SELECTED_GAMET == GameType.DFS) {
            ls.remove('bench_data')
        }
    }

    goToLobby = () => {
        const { LobyyData, FixturedContest } = this.state;
        let dateformaturl = Utilities.getUtcToLocal(FixturedContest.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        let home = FixturedContest.home || LobyyData.home;
        let away = FixturedContest.away || LobyyData.away;


        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
            let contestListingPath = this.state.isSecIn ?
                '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixturedContest.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET) + '&sit=' + btoa(true)
                : '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixturedContest.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET)
            this.setState({ LobyyData: FixturedContest });
            contestListingPath = contestListingPath.toLowerCase()
            this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: this.state.FixtureData, LobyyData: LobyyData, isFromPM: true, isJoinContestFlow: true } })
        // }
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    callAfterAddFundPopup() {
        if (WSManager.getFromConfirmPopupAddFunds()) {
            WSManager.setFromConfirmPopupAddFunds(false);
            var contestData = WSManager.getContestFromAddFundsAndJoin();
            this.ConfirmEvent(contestData)
        }
    }

    UNSAFE_componentWillMount() {
        this.setStateDataLocation()
        if (SELECTED_GAMET != GameType.MultiGame && SELECTED_GAMET != GameType.Free2Play) {
            WSManager.setPickedGameType(GameType.DFS);
        }
        let CinfirmPopUpIsAddFundsClicked = WSManager.getFromConfirmPopupAddFunds()
        let tempIsAddFundsClicked = WSManager.getFromFundsOnly()
        setTimeout(() => {
            if (tempIsAddFundsClicked == 'true' && CinfirmPopUpIsAddFundsClicked == 'true' || CinfirmPopUpIsAddFundsClicked == true) {
                setTimeout(() => {
                    this.callAfterAddFundPopup()
                }, 200);
            }
        }, 500);
    }
    setStateDataLocation = () => {
        if (this.props && this.props.location && this.props.location.state) {
            const { isSecIn } = this.props.location.state;
            this.setState({
                isSecIn: isSecIn || false
            })
        }
    }
    onPlayers = () => {
        this.setState({
            isCategory: true
        })
    }
    onPoints = () => {
        this.setState({
            isCategory: false,
            lineupArr: this.state.lineupArr.sort((a, b) => (b.fantasy_score - a.fantasy_score))
        })
    }

    checkButtonEnable() {
        const { MasterData } = this.state
        var isValid = true;
        var teamname = this.state.teamName ? this.state.teamName : this.props.location.state.team_name
        if (!teamname || teamname.length < 4 || !this.state.IsChanged) {
            isValid = false;
        }
        else if (this.state.isLoading || this.state.clickOnce) {
            isValid = false;
        }
        return (MasterData.c_point == 0 && MasterData.vc_point == 0) ? true : isValid;
    }

    showFieldV = () => {
        this.setState({
            showFieldV: true
        });
    }

    hideFieldV = () => {
        this.setState({
            showFieldV: false
        });
    }
    /**
   * @description method to Bosster model
   */
    BossterModalHide = () => {
        this.setState({
            showBoosterModal: false,
        });
    }
    /**
    * @description method to Bosster model
    */
    BoosterModalShow = () => {
        this.setState({
            showBoosterModal: true,
        });
    }
    skipBoosterAndContinue = () => {
        this.BossterModalHide()
        if ((this.state.isFrom == "MyTeams" || this.state.isFrom == "MyContest" || this.state.isFrom == "editView") && this.state.isFromMyTeams) {
            var go_index = -2;
            if (this.state.isFrom == "editView" && !this.state.isClone && !this.state.isFromMyTeams) {
                go_index = -3;
            }
            WSManager.clearLineup();
            this.props.history.go(go_index);
        }
        else if (this.state.ifFromSwitchTeamModal) {
            this.switchTeam(this.state.FixturedContest, this.state.lineupMasterdId, this.props.location.state.lineup_master_contest_id);

        }
        else {
            if (checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')) {
                this.getUserLineUpListApi();
            }
        }

    }
    openRosterCollection = () => {
        this.props.history.push({
            pathname: `/booster-collection/${this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id}/${Utilities.getSelectedSportsForUrl().toLowerCase()}/${(this.state.isClone ? this.state.lineupMasterdId : this.props.location.state.teamitem ? this.props.location.state.teamitem.lineup_master_id ? this.props.location.state.teamitem.lineup_master_id : this.props.location.state.lineup_master_id : (this.state.teamData.lineup_master_id ? this.state.teamData.lineup_master_id : this.state.lineupMasterdId))}`
            , state: { LobyyData: this.state.LobyyData, FixturedContest: this.state.FixturedContest, team_name: this.state.teamName, isFromFlow: this.state.isFrom, isFromMyTeams: this.state.isFromMyTeams, booster_id: this.state.isClone ? this.state.booster_id : this.props.location.state.teamitem && this.props.location.state.teamitem.booster_id && this.props.location.state.teamitem.booster_id ? this.props.location.state.teamitem.booster_id : this.state.booster_id, direct: false, ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, lineup_master_contest_id: this.state.ifFromSwitchTeamModal ? this.props.location.state.lineup_master_contest_id : 0, isPlayingAnnounced: this.state.isPlayingAnnounced }
        })
    }

    render() {
        const {
            teamName,
            allPosition,
            showConfirmationPopUp,
            userTeamListSend,
            FixturedContest,
            showThankYouModal,
            lineupMasterdId,
            sportsSelected,
            showUJC,
            TotalTeam,
            showBoosterModal,
            isSecIn,
            aadharData,
            LobyyData,
            MasterData
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            hideShadow: true,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        let is_tour_game = LobyyData && LobyyData.is_tour_game == 1 ? true : false;

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed white-bg">
                        <CustomHeader {...this.props} ref={this.headerRef} HeaderOption={HeaderOption} />
                        <div className="select-captian-wrap">
                            <div className="filed-with-icon">
                                <Row >
                                    {
                                        <Col xs={12}>
                                            <FormGroup
                                                className='xinput-label-center'
                                                controlId="formBasicText"
                                                validationState={teamName ? "success" : this.props.location.state.team_name && this.getValidationState('teamName', teamName ? teamName : this.props.location.state.team_name)}
                                            >
                                                {
                                                    this.state.isTeamNameChanged &&
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='teamName'
                                                        name='teamName'
                                                        placeholder={AppLabels.ENTER_TAM_NAME}
                                                        type='text'
                                                        value={teamName ? teamName : this.props.location.state.team_name || ''}
                                                        onChange={this.handleChange}
                                                    />
                                                }
                                            </FormGroup>
                                            {/* {
                                                <i className="ic-team-name icon-with-input-cvc"></i>
                                            } */}
                                        </Col>
                                    }
                                </Row>
                            </div>
                            {(this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) &&
                                <div className="selection-help-txt">
                                    <h4>
                                        {
                                            (sportsSelected == SportsIDs.badminton) ?
                                                <React.Fragment>
                                                    {this.state.MasterData.c_point > 0 &&
                                                        <span>{AppLabels.CHOOSE_CAPTAIN}</span>
                                                    }
                                                </React.Fragment>
                                                :
                                                (AppSelectedSport == SportsIDs.MOTORSPORTS) ? 
                                                <>
                                                    {
                                                        this.state.MasterData.c_point > 0 &&
                                                        <span>{CommonLabels.CHOOSE_TURBO}</span>
                                                    }
                                                </> :
                                                <span>
                                                    {this.state.MasterData.c_point > 0 && this.state.MasterData.vc_point <= 0 &&
                                                        AppLabels.CHOOSE_CAPTAIN
                                                    }
                                                    {this.state.MasterData.c_point > 0 && this.state.MasterData.vc_point > 0 &&
                                                        AppLabels.CHOOSE_CAPTAIN_VICE_CAPTAIN
                                                    }
                                                    {this.state.MasterData.c_point <= 0 && this.state.MasterData.vc_point > 0 &&
                                                        AppLabels.CHOOSE_VICE_CAPTAIN
                                                    }
                                                </span>
                                        }
                                    </h4>
                                    <p>
                                        {this.state.MasterData.c_point > 0 &&
                                            <span>
                                                <span className='captain_cirlce'>{is_tour_game && AppSelectedSport != SportsIDs.tennis ? 'T': AppLabels.C}</span>
                                                {AppLabels.GETS}
                                                {Utilities.getMasterData() != null &&
                                                    ((sportsSelected != SportsIDs.badminton)
                                                        ?

                                                        <>
                                                            {
                                                                this.state.MasterData.c_point + 'x'
                                                            }
                                                        </>
                                                        :
                                                        <>
                                                            {
                                                                this.state.MasterData.vc_point + 'x'
                                                            }
                                                        </>
                                                    )
                                                }
                                                <React.Fragment> {AppLabels.POINTS}</React.Fragment>
                                            </span>
                                        }
                                        {this.state.MasterData.vc_point > 0 && !(is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS) &&
                                            <React.Fragment>
                                                {
                                                    (sportsSelected != SportsIDs.badminton) &&
                                                    <span>
                                                        <span className='captain_cirlce'>{AppLabels.VC}</span>
                                                        {AppLabels.GETS}
                                                        {Utilities.getMasterData() != null &&
                                                            <>
                                                                {
                                                                    this.state.MasterData.vc_point + 'x'
                                                                }
                                                            </>
                                                        }
                                                        <React.Fragment> {AppLabels.POINTS}</React.Fragment>
                                                    </span>
                                                }
                                            </React.Fragment>
                                        }
                                    </p>

                                </div>
                            }
                            <div className={"sorting-pts-player " + ((this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) ? "mt-0" : "nocvc")}>
                                {
                                    (this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) && 
                                    <Row>
                                        <Col xs={12}>
                                            <span>{AppLabels.SORT_BY} -</span>

                                            <button onClick={() => this.onPlayers()} className={" btns " + (this.state.isCategory ? 'btnsblue' : '')} >{AppLabels.PLAYERS} </button>

                                            <button onClick={() => this.onPoints()} className={" btns " + (!this.state.isCategory ? 'btnsblue' : '')} >{AppLabels.POINTS} </button>
                                        </Col>
                                    </Row>
                                }
                            </div>
                            {this.state.isCategory ?
                                <div className="lineup-list-view">
                                    <div className="list-view-detail">
                                        {_Map(allPosition, (positem, posidx) => {
                                            return (
                                                <div key={posidx} className="list-view-header-wrap">
                                                    <div className="list-view-header"> {AppSelectedSport == SportsIDs.tennis ? AppLabels.PLAYERS : positem.position_display_name} </div>
                                                    <ul className="list-secondary" key={posidx}>
                                                        {
                                                            _Map(this.filterLineypArrByPosition(positem), (item, idx) => {
                                                                return (
                                                                    <li key={idx}>
                                                                        <Row className="style">
                                                                            <Col xs={6} className="text-left-ltr mt-6 player-fullname">
                                                                                <h4>{item.full_name}</h4>
                                                                                <span>{item.team_abbreviation || item.team_abbr}</span>

                                                                            </Col>
                                                                            <Col xs={(this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) ? 3 : 6} className="text-right">
                                                                                <ul className="roster-player-salary">
                                                                                    <li>
                                                                                        <span className="pts-style" >{item.fantasy_score} {AppLabels.PTS}</span>
                                                                                    </li>
                                                                                </ul>
                                                                            </Col>
                                                                            {
                                                                                (this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) &&
                                                                                <Col xs={3} className="text-right-ltr">
                                                                                    <ul className="list-inline-capt pt2">

                                                                                        {this.state.MasterData.c_point > 0 &&
                                                                                            <li>
                                                                                                <a onClick={() => this.ChangePlayerRole(1, item)} className={this.PlayerRoleClass(1, item) ? 'selected-captain' : ''}>
                                                                                                    {!this.PlayerRoleClass(1, item) ?
                                                                                                        <span className='captain-c'>{is_tour_game && AppSelectedSport != SportsIDs.tennis ? 'T' : 'C'}</span>
                                                                                                        :
                                                                                                        <span className={"captain-c"}>
                                                                                                            {masterDataResponse != null &&
                                                                                                                <>
                                                                                                                    {
                                                                                                                        masterDataResponse.c_point + 'x'
                                                                                                                    }
                                                                                                                </>
                                                                                                            }
                                                                                                        </span>
                                                                                                    }
                                                                                                </a>
                                                                                            </li>
                                                                                        }
                                                                                        {this.state.MasterData.vc_point > 0 && !(is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS) &&
                                                                                            <React.Fragment>
                                                                                                {AppSelectedSport != SportsIDs.badminton &&
                                                                                                    <li>
                                                                                                        <a onClick={() => this.ChangePlayerRole(2, item)} className={this.PlayerRoleClass(2, item) ? 'selected-vcaptain' : ''}>
                                                                                                            {this.PlayerRoleClass(2, item) ?
                                                                                                                <span className={"vice-captain-v"}>
                                                                                                                    {
                                                                                                                        masterDataResponse != null &&
                                                                                                                        <>
                                                                                                                            {
                                                                                                                                masterDataResponse.vc_point + 'x'
                                                                                                                            }
                                                                                                                        </>
                                                                                                                    }
                                                                                                                </span>
                                                                                                                :
                                                                                                                <span className='vice-captain-v'>V</span>
                                                                                                            }

                                                                                                        </a>
                                                                                                    </li>
                                                                                                }
                                                                                            </React.Fragment>
                                                                                        }
                                                                                    </ul>
                                                                                </Col>

                                                                            }
                                                                        </Row>
                                                                    </li>
                                                                )
                                                            })
                                                        }
                                                    </ul>
                                                </div>
                                            )
                                        })
                                        }

                                    </div>

                                </div>

                                :
                                <div className="lineup-list-view">
                                    <div className="list-view-detail">
                                        {
                                            <div
                                            >
                                                <ul className="list-secondary" >
                                                    {
                                                        _Map(this.state.lineupArr, (item, idx) => {
                                                            return (
                                                                <li key={idx} >
                                                                    <Row className="style">
                                                                        <Col xs={6} className="text-left-ltr mt-6 player-fullname">
                                                                            <h4>{item.full_name}</h4>
                                                                            <span>{item.team_abbreviation || item.team_abbr}</span>

                                                                        </Col>
                                                                        <Col xs={3} className="text-right">
                                                                            <ul className="roster-player-salary">
                                                                                <li>
                                                                                    <span className="pts-style" >{item.fantasy_score} {AppLabels.PTS}</span>
                                                                                </li>
                                                                            </ul>

                                                                        </Col>
                                                                        {
                                                                            (this.state.MasterData.c_point > 0 || this.state.MasterData.vc_point > 0) &&
                                                                            <Col xs={3} className="text-right-ltr">
                                                                                <ul className="list-inline-capt pt2">
                                                                                    {this.state.MasterData.c_point > 0 &&
                                                                                        <li >
                                                                                            <a onClick={() => this.ChangePlayerRole(1, item)} className={this.PlayerRoleClass(1, item) ? 'selected-captain' : ''}>
                                                                                                {!this.PlayerRoleClass(1, item) ?
                                                                                                    <span className='captain-c'>C</span> : <span className={"captain-c"}>
                                                                                                        {masterDataResponse != null &&
                                                                                                            <>
                                                                                                                {
                                                                                                                    masterDataResponse.c_point + 'x'
                                                                                                                }
                                                                                                            </>
                                                                                                        }
                                                                                                    </span>
                                                                                                }
                                                                                            </a>
                                                                                        </li>
                                                                                    }
                                                                                    {
                                                                                        this.state.MasterData.vc_point > 0 && !(is_tour_game && AppSelectedSport == SportsIDs.MOTORSPORTS) &&
                                                                                        <React.Fragment>
                                                                                            {AppSelectedSport != SportsIDs.badminton &&
                                                                                                <li>
                                                                                                    <a onClick={() => this.ChangePlayerRole(2, item)} className={this.PlayerRoleClass(2, item) ? 'selected-vcaptain' : ''}>
                                                                                                        {this.PlayerRoleClass(2, item) ?
                                                                                                            <span className={"vice-captain-v"}>
                                                                                                                {
                                                                                                                    masterDataResponse != null &&
                                                                                                                    <>
                                                                                                                        {
                                                                                                                            masterDataResponse.vc_point + 'x'
                                                                                                                        }
                                                                                                                    </>
                                                                                                                }
                                                                                                            </span>
                                                                                                            :
                                                                                                            <span className='vice-captain-v'>V</span>
                                                                                                        }

                                                                                                    </a>
                                                                                                </li>
                                                                                            }
                                                                                        </React.Fragment>
                                                                                    }
                                                                                </ul>
                                                                            </Col>

                                                                        }
                                                                    </Row>
                                                                </li>
                                                            )
                                                        })
                                                    }
                                                </ul>
                                            </div>

                                        }


                                    </div>

                                </div>
                            }
                        </div>


                        <button disabled={!this.checkButtonEnable()} onClick={(event) => this.aadharDataCheck(event)} className="btn btn-primary  btn-block btm-fix-btn">{AppLabels.SUBMIT_LINEUP}</button>

                        {showConfirmationPopUp &&
                            <ConfirmationPopup {...this.props} profileData={WSManager.getProfile()} lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={FixturedContest} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={lineupMasterdId} isSecIn={isSecIn} />
                        }

                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} />
                        }
                        {
                            showBoosterModal &&
                            <BoosterGameOnModal team_name={this.state.teamName} gotoBooster={this.openRosterCollection} skipToMyTeam={this.skipBoosterAndContinue} IsBoosterModalShow={this.BoosterModalShow}
                                IsBoosterModalHide={this.BossterModalHide} />
                        }
                        {
                            this.state.lineupArr.length > 0 && <FieldView
                                SelectedLineup={this.state.lineupArr}
                                MasterData={this.state.MasterData}
                                isFrom={'captain'}
                                isBSOFV={this.state.isFrom == 'editView' ? true : false}
                                team_name={this.state.teamName}
                                showFieldV={this.state.showFieldV}
                                hideFieldV={this.hideFieldV.bind(this)}
                                benchPlayer={this.state.benchArr}
                                isPlayingAnnounced={this.state.isPlayingAnnounced}
                                FixturedContest={this.state.FixturedContest}
                                isSecIn={this.state.isSecIn}
                                LobyyData={this.state.LobyyData}
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