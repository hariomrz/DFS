import React, { Suspense, lazy } from 'react';
import { Row, Col, Button, FormGroup, Table } from 'react-bootstrap';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft } from '../helper/input-style';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import ls from 'local-storage';
import { MyContext } from '../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../helper/MetaData";
import ConfirmationPopup from '../Modals/ConfirmationPopup';
import Thankyou from '../Modals/Thankyou';
import { checkBanState, Utilities, _filter } from '../Utilities/Utilities';
import CustomHeader from '../components/CustomHeader';
import { AppSelectedSport ,DARK_THEME_ENABLE, EnableBuyCoin,OnlyCoinsFlow ,SELECTED_GAMET, GameType,} from '../helper/Constants';
import { getUserTeams, joinContest, getMatchByLeague, createPrivateContest, createContestMasterData, getUserAadharDetail } from '../WSHelper/WSCallings';
import Images from '../components/images';
const ReactSelectDD = lazy(() => import('../Component/CustomComponent/ReactSelectDD'));

var mEntryFee = 0;
var mMinTeamSize = 0;
var mSiteRake = 0;
var mPrizePool = 0;
var mCalculation = 0;
var ErrorMsgTime = 2500;
var cAmount = 0;
var globalThis = undefined;

export default class CreateContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            leagueId: '',
            leaguesArray: [],
            selectedLeague: '',
            siteRake: '',
            salaryCap: '',
            formats: '',
            matchesArray: [],
            selectedMatch: '',
            entryFee: '',
            numberOfWinners: '',
            showPrizeList: false,
            minTeamSize: '',
            maxTeamSize: '',
            prizePool: '',
            prizeDistributionDetail: [],
            disableWinner: '',
            contestName: '',
            prizeCalculated: false,
            prizeDistributed: false,
            isEntryFeeChanged: true,
            master_min_size: 2,
            TeamList: [],
            TotalTeam: [],
            userTeamListSend: [],
            showConfirmationPopUp: false,
            FixtureData: '',
            showThankYouModal: false,
            isValidPrizeStructure: true,
            allowCollection: Utilities.getMasterData().a_collection,
            isCMounted: false,
            allowRevFantasy: Utilities.getMasterData().a_reverse == '1',
            selEntryFee: '',
            LobyyData: [],
            aadharData: ''
        }
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('createcontest')

        globalThis = this;

    }
    componentDidMount() {
        Utilities.handleAppBackManage('create-contest')
        this.callCreateContestMasterData();
        this.setState({
            isCMounted: true,
            entryFeeOpt: [
                {
                    value: 1,
                    label: Utilities.getMasterData().currency_code
                },
                {
                    value: 2,
                    label: <img src={Images.IC_COIN} alt="" />
                }
            ]
        }, () => {
            this.setState({
                selEntryFee: OnlyCoinsFlow == 0 ? this.state.entryFeeOpt[0] : this.state.entryFeeOpt[1]
            })
        })
        if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            if(WSManager.getProfile().aadhar_status != 1){
                getUserAadharDetail().then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ aadharData: responseJson.data },()=>{
                            WSManager.updateProfile(this.state.aadharData)
                        });
                    }
                })
            }
            else{
                let aadarData = {
                    'aadhar_status': WSManager.getProfile().aadhar_status,
                    "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                }
                this.setState({ aadharData: aadarData });
            }
        }
    }

    componentDidUpdate() {
        this.handleEntryfeeChange.bind(this);
        this.handleMaxTeamChange.bind(this);
        this.handleMinTeamSizeChange.bind(this);
        this.handleWinnerChange.bind(this);
        this.validateValuesAndJoinGame.bind(this)

    }

    validateValuesAndJoinGame() {
        const {
            numberOfWinners, selectedLeague, selectedMatch, entryFee, minTeamSize, maxTeamSize, contestName
        } = this.state

        if (selectedLeague == "") {
            Utilities.showToast(AppLabels.SELECT_LEAGUE_ERROR, ErrorMsgTime);
        } else if (selectedMatch == "") {
            Utilities.showToast(AppLabels.SELECT_MATCH, ErrorMsgTime);
        }
        else if (minTeamSize == "") {
            Utilities.showToast(AppLabels.SELECT_MINIMUM_TEAM, ErrorMsgTime);
        } else if (maxTeamSize == "") {
            Utilities.showToast(AppLabels.SELECT_MAX_TEAMS, ErrorMsgTime);
        }
        else if (parseInt(minTeamSize) < parseInt(this.state.master_min_size)) {
            Utilities.showToast(AppLabels.MIN_TEAM_CONDITION + ' ' + this.state.master_min_size, ErrorMsgTime);
        }
        else if (parseInt(maxTeamSize) < parseInt(minTeamSize)) {
            Utilities.showToast(AppLabels.MAX_TEAM_CONDITION, ErrorMsgTime);
        } else if (entryFee == "") {
            Utilities.showToast(AppLabels.SELECT_ENTRY_FEE, ErrorMsgTime);
        } else if (numberOfWinners == "") {
            Utilities.showToast(AppLabels.SELECT_WINNERS_COUNT, ErrorMsgTime);
        } else if (contestName == "") {
            Utilities.showToast(AppLabels.SELECT_CONTEST_NAME, ErrorMsgTime);
        } else if (contestName.length < 3) {
            Utilities.showToast(AppLabels.SELECT_CONTEST_NAME_MIN_CONDITION, ErrorMsgTime);
        } else if (parseInt(numberOfWinners) > parseInt(minTeamSize)) {
            Utilities.showToast(AppLabels.WINNERS_CONDITION, ErrorMsgTime);
        }
        else if (!this.checkValidPrizeStructure(true)) {

            Utilities.showToast(AppLabels.PRIZE_STRUCTURE_ERROR, ErrorMsgTime);
        }
        else {
            this.callCreateUserContest();
        }

    }
    callCreateContestMasterData() {
        let param = {
            "sports_id": AppSelectedSport
        }
        createContestMasterData(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let League = []
                var leaguesForKeyValue = responseJson.data.league_list
                for (var obj of leaguesForKeyValue) {
                    obj['label'] = obj.league_name;
                    obj['value'] = obj.league_id;
                }
                League = leaguesForKeyValue

                this.setState({
                    leaguesArray: League,
                    siteRake: responseJson.data.site_rake,
                    salaryCap: responseJson.data.salary_cap
                })
            }
        })
    }

    callGetMatchesByLeagueId() {
        let param = {
            "league_id": this.state.selectedLeague.league_id
        }

        getMatchByLeague(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                var matchesForKeyValue = responseJson.data;
                let Matches = [];
                for (var matchObj of matchesForKeyValue) {
                    matchObj['label'] = matchObj.home + " vs " + matchObj.away + " - " + Utilities.getFormatedDateTime(matchObj.season_scheduled_date, "MMM DD, YY - hh:mm a")
                    matchObj['value'] = matchObj.season_game_uid
                    Matches.push(matchObj);
                }
                this.setState({
                    matchesArray: Matches
                })
            }
        })
    }

    callCreateUserContest() {
        let isCoins = Utilities.getMasterData().a_coin == "1"
        let isReal = Utilities.getMasterData().currency_code != ""
        let OnlyCoin= OnlyCoinsFlow
        let prizeType = OnlyCoin == 0 ? ((isReal && isCoins) ? (this.state.selEntryFee.value == 2 ? "2" : "1") : isReal ? "1" : isCoins ? "2" : "") : "2";
        let currType = OnlyCoin == 0 ? ((isReal && isCoins) ? this.state.selEntryFee.value : isReal ? 1 : isCoins ? 2 : "") : '2';
        let param = {
            "sports_id": AppSelectedSport,
            "league_id": this.state.selectedLeague.league_id,
            "collection_master_id": this.state.selectedMatch.collection_master_id,
            "prize_type": prizeType,
            "salary_cap": this.state.salaryCap,
            "prize_pool": this.state.prizePool,
            "number_of_winners": this.state.numberOfWinners,
            "entry_fee": this.state.entryFee,
            "size_min": this.state.minTeamSize,
            "size": this.state.maxTeamSize,
            "prize_distribution_detail": this.state.prizeDistributionDetail,
            "season_game_uid": [this.state.selectedMatch.season_game_uid],
            "season_scheduled_date": this.state.selectedMatch.season_scheduled_date,
            "game_name": this.state.contestName,
            "game_desc": "test",
            "currency_type": currType
        }
        Utilities.gtmEventFire('create_contest', {
            league_name: this.state.selectedLeague.league_name,
            match_name: this.state.selectedMatch.label,
            team_size: param.size,
            no_of_winner: param.number_of_winners,
            contest_name: param.game_name,
            entry_fee: param.entry_fee
        })
        if (checkBanState(param, CustomHeader)) {
            if (!this.state.isLoading) {
                this.setState({ isLoading: true })

                createPrivateContest(param).then((responseJson) => {
                    this.setState({ isLoading: false })
                    if (responseJson.response_code == WSC.successCode) {
                        this.getUserLineUpListApi(responseJson.data)
                    }
                })
            }

            createPrivateContest(param).then((responseJson) => {
                this.setState({ isLoading: false })
                if (responseJson.response_code == WSC.successCode) {
                    this.getUserLineUpListApi(responseJson.data)
                }
            })
        }
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

    getUserLineUpListApi = async (contestData) => {
        let param = {
            "sports_id": this.state.selectedLeague.sports_id,
            "collection_master_id": contestData.collection_master_id,
        }

        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = await getUserTeams(param, user_unique_id);
        if (api_response_data) {
            let tList = this.state.isSecIn ? _filter(api_response_data,(obj,idx) => {
                return obj.is_2nd_inning == "1";
            }) : (this.state.allowRevFantasy && SELECTED_GAMET == GameType.DFS) ? _filter(api_response_data,(obj,idx) => {
                return obj.is_reverse != "1";
            }) : _filter(api_response_data,(obj,idx) => {
                return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            })
            this.setState({
                TotalTeam: api_response_data,
                TeamList: tList,
                userTeamListSend: tList,
                // TeamList: (this.state.allowRevFantasy && SELECTED_GAMET == GameType.DFS) ? api_response_data.filter((obj,idx) => {
                //     return obj.is_reverse != "1"
                // }) : api_response_data,
                // userTeamListSend: (this.state.allowRevFantasy && SELECTED_GAMET == GameType.DFS) ? api_response_data.filter((obj,idx) => {
                //     return obj.is_reverse != "1"
                // }) : api_response_data
            })

            if (this.state.userTeamListSend) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {

                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })

                this.setState({ userTeamListSend: tempList });
            }

            let urlData = contestData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

            if (api_response_data.length > 0) {
                this.setState({ showConfirmationPopUp: true, FixtureData: contestData, LobyyData: contestData })
            } else {
                WSManager.clearLineup();
                let lineupPath = '';
                if (urlData.home) {
                    lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
                }
                else {
                    if (contestData.macth_list && contestData.macth_list.length == 1) {
                        lineupPath = '/lineup/' + urlData.macth_list[0].home + "-vs-" + urlData.away + "-" + dateformaturl
                    }
                    else {
                        let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_')
                        lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
                    }
                }
                if (!contestData.today && contestData.match_list && contestData.match_list.length > 0) {
                    contestData['today'] = contestData.match_list[0].today || '';
                    contestData['game_starts_in'] = contestData.match_list[0].game_starts_in || '';
                }
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: contestData, LobyyData: contestData, resetIndex: 1 } })
            }
        }
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {

        WSManager.clearLineup();
        let urlData = dataFromConfirmFixture
        // let urlData = dataFromConfirmFixture.match_list[0]
        let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = '';
        if (urlData.home) {
            lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        }
        else {
            let pathurl = '';
            if (urlData.match_list && urlData.match_list.length == 1) {
                lineupPath = '/lineup/' + urlData.match_list[0].home + "-vs-" + urlData.match_list[0].away + "-" + dateformaturl
            }
            else {
                pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            }
        }
        this.setState({
            LobyyData: dataFromConfirmFixture
        })

        this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmFixture, resetIndex: 1 } })
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {

        if (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if (currentEntryFee <= dataFromConfirmPopUp.balanceAccToMaxPercent) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            } else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("SelectCaptainList")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
        }

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        
        joinContest(param).then((responseJson) => {

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

                console.log(dataFromConfirmPopUp, '9. dataFromConfirmPopUp');
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
                    console.log('deviceIds', deviceIds);
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
            } else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
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

    goToLobby = () => {
        // this.props.history.push({ pathname: '/' });


        const { LobyyData, FixturedContest } = this.state;
        let dateformaturl = Utilities.getUtcToLocal(LobyyData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);

        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        let home = LobyyData.home || LobyyData.home;
        let away = LobyyData.away || LobyyData.away;

        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + LobyyData.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET)
        this.setState({ LobyyData: LobyyData });
        contestListingPath = contestListingPath.toLowerCase()
        this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: this.state.FixtureData, LobyyData: LobyyData, isFromPM: true, isJoinContestFlow: true } })
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    handleLeagueChange = (selectedOption) => {
        this.setState({
            selectedLeague: selectedOption,
            selectedMatch: ""
        })

        setTimeout(() => {
            this.callGetMatchesByLeagueId()
        }, 100);
    }

    handleMatchChange = (selectedOption) => {
        this.setState({ selectedMatch: selectedOption })
    }

    handleEntryFeeType = (selType) => {
        this.setState({
            selEntryFee: selType
        }, () => {
            setTimeout(() => {
                this.getPrizePool();
                this.createWinnersList();
            }, 100);
        }, 100)
    }

    handleEntryfeeChange = (e) => {
        const value = e.target.value;

        this.setState({
            isEntryFeeChanged: false
        })

        if (value == "") {
            this.setState({
                disableWinner: false,
                numberOfWinners: "",
            })
        } else if (value == 0) {
            this.setState({
                numberOfWinners: 1,
                disableWinner: true,
            })
        } else {
            this.setState({
                disableWinner: false,
                numberOfWinners: "",
            })
        }

        setTimeout(() => {
            this.setState({ entryFee: value, isEntryFeeChanged: true });
            this.getPrizePool();
            setTimeout(() => {
                this.createWinnersList();
            }, 100);
        }, 100);
    }

    handleWinnerChange = (e) => {
        let { minTeamSize } = this.state;
        const value = e.target.value;
        let showPrizeList = false;
        if (parseInt(value) <= parseInt(minTeamSize)) {
            showPrizeList = true;
        }
        else {
            Utilities.showToast(AppLabels.WINNERS_CONDITION, ErrorMsgTime);
            showPrizeList = false;
        }
        this.setState({ showPrizeList: showPrizeList, numberOfWinners: value }, () => {

            this.getPrizePool();
            setTimeout(() => {
                this.createWinnersList();
            }, 100);
        });
    }

    handleMinTeamSizeChange = (e) => {
        let { numberOfWinners } = this.state;
        const value = e.target.value;

        let showPrizeList = false;
        if (parseInt(numberOfWinners) <= parseInt(value)) {
            showPrizeList = true;
        }
        else {
            showPrizeList = false;
        }

        this.setState({ minTeamSize: value, showPrizeList: showPrizeList }, () => {

            setTimeout(() => {
                this.getPrizePool();
                this.createWinnersList();
            }, 100);
        });
    }

    handleContestNameChange = (e) => {
        const value = e.target.value;
        this.setState({ contestName: value });
    }
    onKeyDown = (e) => {
        if (e.keyCode === 8 || e.keyCode === 46) {
            this.setState({ isDeletePer: true })
        } else {
            this.setState({ isDeletePer: false })
        }
    }

    onBlurInput = (e, perIndex) => {
        let previousList = this.state.prizeDistributionDetail;
        previousList.map((item, index) => {
            if (index == perIndex) {
                previousList[index].isValid = item.isValid;
                previousList[index].per = parseFloat(item.per || 0).toFixed(2);
                previousList[index].amount = parseFloat(item.amount || 0).toFixed(2);
            }
        });
        this.setState({ prizeDistributionDetail: previousList }, function () {
            this.checkValidPrizeStructure(true);
        });
    }

    handleWinningPerChange = (e, perIndex) => {
        this.setState({ isValidPrizeStructure: true })
        if (e && typeof perIndex != "undefined") {
            const { prizePool, maxTeamSize, entryFee, siteRake } = this.state
            let tempPrizePool = parseFloat(prizePool);
            let maxPrizePool = parseFloat(maxTeamSize * entryFee) - parseFloat((siteRake * (maxTeamSize * entryFee)) / 100);;
            let previousList = this.state.prizeDistributionDetail;
            let targetValue = e.target.value;

            if (this.state.isDeletePer) {
                let upamount = parseFloat((tempPrizePool * targetValue / 100).toFixed(2))

                let maxupamount = parseFloat((maxPrizePool * targetValue / 100).toFixed(2))

                previousList.map((item, index) => {
                    if (index == perIndex) {
                        previousList[index].isValid = false;
                        previousList[index].per = targetValue;
                        previousList[index].amount = upamount;
                        previousList[index].min_value = upamount;
                        previousList[index].max_value = maxupamount;
                    }
                });
                return true;
            }
            let floatRegExp = new RegExp('^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$')
            if (!floatRegExp.test(targetValue)) {
                previousList.map((item, index) => {
                    if (index == perIndex) {
                        previousList[index].isValid = false;
                        previousList[index].per = 0.0;
                        previousList[index].amount = 0.0;
                        previousList[index].min_value = 0.0;
                        previousList[index].max_value = 0.0;
                    }
                });
                this.setState({ prizeDistributionDetail: previousList }, function () {
                    // this.checkValidPrizeStructure();
                });
                this.setState({ isValidPrizeStructure: false }, function () {
                    Utilities.showToast(AppLabels.Please_enter_valid_percentage_value, ErrorMsgTime);
                });
                return false;
            }
            let updatedPerValue = floatRegExp.test(targetValue) ? targetValue : parseFloat(targetValue).toFixed(2);
            if (updatedPerValue > 100) {
                this.setState({ isValidPrizeStructure: false }, function () {
                    Utilities.showToast(AppLabels.Please_enter_percentage_value_less_100, ErrorMsgTime);
                });
                return false;
            }
            if (updatedPerValue < 0) {
                this.setState({ isValidPrizeStructure: false }, function () {
                    Utilities.showToast(AppLabels.Please_enter_valid_percentage_value, ErrorMsgTime);
                });
                return false;
            }

            let updatedAmountValue = parseFloat((tempPrizePool * updatedPerValue / 100).toFixed(2))
            let maxupamount = parseFloat((maxPrizePool * targetValue / 100).toFixed(2))
            let totalPer = updatedPerValue;
            let totalAmount = updatedAmountValue;
            previousList.map((item, index) => {
                if (index == perIndex) {
                    previousList[index].isValid = true;
                    previousList[index].per = updatedPerValue;
                    previousList[index].amount = updatedAmountValue;
                    previousList[index].min_value = updatedAmountValue;
                    previousList[index].max_value = maxupamount;
                }
                else {
                    totalPer = parseFloat(totalPer) + parseFloat(previousList[index].per);
                    totalAmount = parseFloat(totalAmount) + parseFloat((tempPrizePool * previousList[index].per / 100).toFixed(2));
                }
            });

            this.setState({ prizeDistributionDetail: previousList }, function () {
                // this.checkValidPrizeStructure();
            });
        }
    }

    checkValidPrizeStructure(beforeApiCall) {
        let previousList = this.state.prizeDistributionDetail;
        let totalPer = 0;
        let totalAmount = 0;
        let validFlag = this.state.isValidPrizeStructure;
        const { prizePool } = this.state
        let tempPrizePool = parseFloat(prizePool).toFixed(2);
        previousList.map((item, index) => {

            totalPer = parseFloat(totalPer) + parseFloat(previousList[index].per);
            totalAmount = parseFloat(totalAmount) + parseFloat((tempPrizePool * previousList[index].per / 100).toFixed(2));

        });

        cAmount = (totalAmount).toFixed(2);
        //check total percentage and total prize pool amount

        if (totalPer != 100.00) {
            Utilities.showToast(AppLabels.WINNING_PER_EQ100, ErrorMsgTime);
            validFlag = false;
        }
        else if (parseFloat(totalAmount).toFixed(2) != parseFloat(tempPrizePool).toFixed(2)) {
            Utilities.showToast(AppLabels.WINNING_AMT_CONDITION + ' ' + tempPrizePool, ErrorMsgTime);
            validFlag = false;
        }
        else {
            validFlag = true;
        }

        this.setState({ isValidPrizeStructure: validFlag }, function () {
        });

        return validFlag;

    }
    handleMaxTeamChange = (e) => {
        const value = e.target.value;
        this.setState({ maxTeamSize: value });
    }

    getPrizePool() {
        mEntryFee = this.state.entryFee != "" ? this.state.entryFee : 0;
        mMinTeamSize = this.state.minTeamSize != "" ? this.state.minTeamSize : 0;
        mSiteRake = this.state.siteRake != "" ? this.state.siteRake : 0;
        mCalculation = mEntryFee * mMinTeamSize;
        if (mCalculation > 0) {
            mPrizePool = mCalculation - ((mSiteRake * mCalculation) / 100);
        } else {
            mPrizePool = 0;
        }
        this.setState({
            prizePool: mPrizePool.toFixed(2),
            prizeCalculated: true
        })
        // return mPrizePool.toFixed(2);
    }

    createWinnersList() {
        const { prizePool, numberOfWinners, } = this.state
        let tempPrizePool = parseFloat(prizePool);
        let individualPer = parseFloat((100 / numberOfWinners).toFixed(2))
        let individualAmount = parseFloat((tempPrizePool * individualPer / 100).toFixed(2))

        var firstPer = 0
        var lastPer = 0

        if ((individualPer * numberOfWinners) < 100.00) {
            firstPer = parseFloat((100.00 - (individualPer * numberOfWinners)).toFixed(2))
        } else if ((individualPer * numberOfWinners) > 100.00) {
            lastPer = parseFloat(((individualPer * numberOfWinners) - 100.00).toFixed(2))
        }

        var firstAmount = 0
        var lastAmount = 0

        if ((individualAmount * numberOfWinners) < tempPrizePool) {
            firstAmount = parseFloat((tempPrizePool - (individualAmount * numberOfWinners)).toFixed(2))
        } else if ((individualAmount * numberOfWinners) > tempPrizePool) {
            lastAmount = parseFloat(((individualAmount * numberOfWinners) - tempPrizePool).toFixed(2))
        }

        var tempPrizeArraY = []
        for (var i = 1; i <= numberOfWinners; i++) {
            let prizeDictionary = {
                "isValid": true,
                "min": i,
                "max": i,
                "per": i == 1 ? (individualPer + firstPer).toFixed(2) : i == numberOfWinners ? (individualPer - lastPer).toFixed(2) : individualPer.toFixed(2),
                "amount": i == 1 ? (individualAmount + firstAmount).toFixed(2) : i == numberOfWinners ? (individualAmount - lastAmount).toFixed(2) : individualAmount.toFixed(2),
            }
            tempPrizeArraY.push(prizeDictionary)
        }
        cAmount = tempPrizePool;
        this.setState({
            prizeDistributionDetail: tempPrizeArraY,
            prizeDistributed: true
        })
    }

    static reload() {
        if (globalThis && window.location.pathname.startsWith("/create-contest")) {
            globalThis.callCreateContestMasterData();
        }
    }

    render() {
        globalThis = this;
        const HeaderOption = {
            back: true,
            title: AppLabels.Create_a_Contest,
            hideShadow: false,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        const {
            leaguesArray,
            selectedLeague,
            matchesArray,
            selectedMatch,
            entryFee,
            numberOfWinners,
            minTeamSize,
            maxTeamSize,
            prizePool,
            disableWinner,
            contestName,
            prizeCalculated,
            prizeDistributed,
            isEntryFeeChanged,
            showConfirmationPopUp,
            userTeamListSend,
            FixtureData,
            showThankYouModal,
            isCMounted,
            entryFeeOpt, 
            selEntryFee,
            TotalTeam,
            aadharData
        } = this.state;

        let showEntryFeeWith = (Utilities.getMasterData().currency_code != "" && Utilities.getMasterData().a_coin == "1") ? 1 : Utilities.getMasterData().currency_code != "" ? 2 : Utilities.getMasterData().a_coin == "1" ? 3 : 2
       
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container create-contest transparent-header web-container-fixed">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.createcontest.title}</title>
                            <meta name="description" content={MetaData.createcontest.description} />
                            <meta name="keywords" content={MetaData.createcontest.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="webcontainer-inner">
                            <div className='parent-container'>
                                <div className="verification-block-left-align">
                                    <Row>
                                        <Col xs={12}>
                                            <FormGroup className='input-label-center-align input-transparent font-14 league-select'>
                                                <div className="select-league">
                                                    <label className='label-text'>{AppLabels.SELECT_LEAGUE}</label>
                                                    <div className="genderStyle">
                                                        {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                            onChange={this.handleLeagueChange}
                                                            options={leaguesArray}
                                                            classNamePrefix="secondary"
                                                            className="select-secondary minusML10"
                                                            value={selectedLeague}
                                                            placeholder="--"
                                                            isSearchable={false} isClearable={false}
                                                            theme={(theme) => ({
                                                                ...theme,
                                                                borderRadius: 0,
                                                                colors: {
                                                                    ...theme.colors,
                                                                    primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                                },
                                                            })}
                                                        /></Suspense>}
                                                    </div>
                                                    <span className="select-arr"><i className="icon-arrow-down"></i></span>
                                                    <div className='league-border col-sm-12' />
                                                </div>
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </div>
                                <div className="verification-block-left-align">
                                    <Row>
                                        <Col xs={12}>
                                            <FormGroup className='input-label-center-align input-transparent font-14 match-list'>
                                                <div className="select-match">
                                                    <label className='label-text'>{AppLabels.MATCHES}</label>
                                                    <div className="genderStyle">
                                                        {isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                            onChange={this.handleMatchChange}
                                                            options={matchesArray}
                                                            classNamePrefix="secondary"
                                                            className="select-secondary minusML10"
                                                            value={selectedMatch}
                                                            placeholder="--"
                                                            isSearchable={false} isClearable={false}
                                                            theme={(theme) => ({
                                                                ...theme,
                                                                borderRadius: 0,
                                                                colors: {
                                                                    ...theme.colors,
                                                                    primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                                },
                                                            })}
                                                        /></Suspense>}
                                                    </div>
                                                    <span className="select-arr"><i className="icon-arrow-down"></i></span>
                                                    <div className='match-border col-sm-12' />
                                                </div>
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </div>

                                <div className="verification-block-left-align">
                                    <Row>
                                        <Col xs={5} className="input-label-spacing-create-contest">
                                            <FormGroup
                                                className='input-label-center-align input-transparent font-16'
                                                controlId="formBasicText">
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='min-team-size'
                                                    name='min-team-size'
                                                    placeholder={AppLabels.Min_team_size}
                                                    type='number'
                                                    onChange={this.handleMinTeamSizeChange.bind(this)}
                                                    value={minTeamSize}
                                                />
                                            </FormGroup>
                                            <span className="bordered-span"></span>
                                        </Col>
                                        <Col xs={2} className="input-label-spacing-create-contest"></Col>
                                        <Col xs={5} className="input-label-spacing-create-contest">
                                            <FormGroup
                                                className='input-label-center-align input-transparent font-16'
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='max-team-size'
                                                    name='max-team-size'
                                                    placeholder={AppLabels.Max_team_size}
                                                    type='number'
                                                    onChange={this.handleMaxTeamChange.bind(this)}
                                                    value={maxTeamSize}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </div>
                                <div className="verification-block-left-align">
                                    <Row>
                                        <Col xs={5} className="input-label-spacing-create-contest" style={{ zIndex: 2 }}>

                                            <FormGroup
                                                className='input-label-center-align input-transparent font-16'
                                                controlId="formBasicText">
                                                {
                                                    OnlyCoinsFlow == 0 ?
                                                        <>
                                                            <FloatingLabel
                                                                autoComplete='off'
                                                                styles={inputStyleLeft}
                                                                id='entry-fee'
                                                                name='entry-fee'
                                                                placeholder={showEntryFeeWith == 2 ? AppLabels.Entry_fee + '(' + Utilities.getMasterData().currency_code + ')' : showEntryFeeWith == 3 ? AppLabels.Entry_fee + ' (' + AppLabels.Coin + ')' : AppLabels.Entry_fee}
                                                                type='number'
                                                                onChange={this.handleEntryfeeChange.bind(this)}
                                                                value={entryFee}
                                                            />
                                                            {
                                                                showEntryFeeWith == 1 &&
                                                                <>
                                                                    <Suspense fallback={<div />} ><ReactSelectDD
                                                                        onChange={this.handleEntryFeeType}
                                                                        options={entryFeeOpt}
                                                                        classNamePrefix="secondary"
                                                                        className="select-secondary minusML10 sel-entry-type"
                                                                        value={selEntryFee}
                                                                        placeholder="--"
                                                                        isSearchable={false}
                                                                        isClearable={false}
                                                                        theme={(theme) => ({
                                                                            ...theme,
                                                                            borderRadius: 0,
                                                                            colors: {
                                                                                ...theme.colors,
                                                                                primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                                            },
                                                                        })}
                                                                    /></Suspense>
                                                                    <span className="select-arr select-arr-entry-type"><i className="icon-arrow-down"></i></span>
                                                                </>
                                                            }
                                                        </>
                                                        :
                                                        <FloatingLabel
                                                            autoComplete='off'
                                                            styles={inputStyleLeft}
                                                            id='entry-fee'
                                                            name='entry-fee'
                                                            placeholder={AppLabels.Entry_fee + ' (' + AppLabels.Coin + ')'}
                                                            type='number'
                                                            onChange={this.handleEntryfeeChange.bind(this)}
                                                            value={entryFee}
                                                        />
                                                }

                                            </FormGroup>
                                            <span className="bordered-span"></span>
                                        </Col>
                                        <Col xs={2} className="input-label-spacing-create-contest"></Col>
                                        <Col xs={5} className="input-label-spacing-create-contest">

                                            <FormGroup
                                                className={entryFee == 0 && entryFee != '' ? 'default-floting input-label-center-align input-transparent font-16' : 'input-label-center-align input-transparent font-16'}
                                                controlId="formBasicText"
                                            >
                                                {isEntryFeeChanged && <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='no-winners'
                                                    name='no-winners'
                                                    placeholder={AppLabels.Number_of_winners}
                                                    type='number'
                                                    onChange={this.handleWinnerChange.bind(this)}
                                                    value={this.state.numberOfWinners + ""}
                                                    disabled={this.state.disableWinner}
                                                />}
                                                {!isEntryFeeChanged && <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='no-winners'
                                                    name='no-winners'
                                                    placeholder={AppLabels.Number_of_winners}
                                                    type='number'
                                                    onChange={this.handleWinnerChange.bind(this)}
                                                    value={this.state.numberOfWinners + ""}
                                                    disabled={this.state.disableWinner}
                                                />}

                                            </FormGroup>


                                        </Col>
                                    </Row>
                                </div>
                                <div className="verification-block-left-align">
                                    <Row>
                                        <Col xs={12} >
                                            <FormGroup
                                                className='input-label-center-align input-transparent font-16 contest-name-input'
                                                controlId="formBasicText">
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='contest-name'
                                                    name='contest-name'
                                                    placeholder={AppLabels.Contest_Name}
                                                    type='text'
                                                    onChange={this.handleContestNameChange.bind(this)}
                                                    value={contestName}
                                                />
                                            </FormGroup>
                                            <span className="bordered-span"></span>
                                        </Col>
                                    </Row>
                                </div>
                                <div className="prize-pool">
                                    {AppLabels.PRIZE_POOL}&nbsp;<span> {Utilities.getMasterData().currency_code}{prizePool != "" || !prizePool == undefined ? prizePool : 0.00}</span>
                                </div>
                                {
                                    this.state.showPrizeList && prizeCalculated && prizeDistributed && <div className="prize-detail">
                                        <div className="prize-heading"><span>{AppLabels.Prize_Distribution}</span></div>
                                        <Table>
                                            <thead>
                                                <tr>
                                                    <th className="text-left">{AppLabels.RANK}</th>
                                                    <th className="text-left">{AppLabels.WINNING} %</th>
                                                    <th className="text-left">{AppLabels.WINNING}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {
                                                    this.state.prizeDistributionDetail && this.state.prizeDistributionDetail.map((WinnersListItem, index) => {
                                                        return (
                                                            <tr key={index}>
                                                                <td className={'winning-amt ' + (index == this.state.prizeDistributionDetail.length - 1 ? ' pb20' : '')}>{"#" + WinnersListItem.min}</td>
                                                                <td className={'text-left ' + (index == this.state.prizeDistributionDetail.length - 1 ? ' pb20' : '')}>
                                                                    <div className="win-amt-input" style={{ backgroundColor: '#fff', borderRadius: 4, overflow: 'hidden', border: (WinnersListItem.isValid ? 'none' : '0.5px solid red') }}>
                                                                        <input
                                                                            autoComplete='off'
                                                                            className='winning-perc'
                                                                            id={'winning-per-' + index}
                                                                            name={'winning-per-' + index}
                                                                            type='text'
                                                                            onChange={(e) => this.handleWinningPerChange(e, index)}
                                                                            value={WinnersListItem.per}
                                                                            onKeyDown={this.onKeyDown}
                                                                            onBlur={(e) => this.onBlurInput(e, index)}
                                                                        />
                                                                        <i className="icon-edit-line"></i>
                                                                    </div>
                                                                </td>
                                                                <td className={"text-left winning-amt " + (index == this.state.prizeDistributionDetail.length - 1 ? ' pb20' : '')}>{Utilities.getMasterData().currency_code}{WinnersListItem.amount}</td>
                                                            </tr>
                                                        );
                                                    })
                                                }
                                                <tr>
                                                    <td className="text-center pt10 pl15 total-container" colSpan="3">
                                                        <span className='total-text'>{AppLabels.TOTAL}</span>
                                                        {Utilities.getMasterData().currency_code}{cAmount != "" || !cAmount == undefined ? cAmount : 0.00}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </Table>
                                    </div>
                                }
                            </div>
                        </div>
                        <div className="page-footer">
                            <Button onClick={() => this.validateValuesAndJoinGame()} bsStyle="primary" className={"btn btn-block" + (!this.state.isValidPrizeStructure ? ' disabled' : '')} disabled={!this.state.isValidPrizeStructure}>{AppLabels.CREATE_SHARE}</Button>
                        </div>

                        {showConfirmationPopUp &&
                            <ConfirmationPopup  {...this.props} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend}
                                TotalTeam={TotalTeam} FixturedContest={FixtureData} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} lobbyDataToPopup={FixtureData} fromContestListingScreen={true} createdLineUp={""} profileData={aadharData} />
                        }

                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

