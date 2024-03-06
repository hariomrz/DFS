import React from 'react';
import ls from 'local-storage';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { inputStyleLeft , darkInputStyleLeft} from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import Validation from '../../helper/Validation';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
import CustomHeader from '../../components/CustomHeader';
import DFSTourFieldView from "./DFSTourFieldview";
import UnableJoinContest from '../../Modals/UnableJoinContest';
import { Utilities, _isUndefined, _indexOf, _Map, _isEmpty, _filter, _cloneDeep, checkBanState } from '../../Utilities/Utilities';
import { SportsIDs } from "../../JsonFiles";
import { AppSelectedSport, globalLineupData, preTeamsList, SELECTED_GAMET,GameType, EnableBuyCoin, BanStateEnabled } from '../../helper/Constants';
import { getDFSTourNewTeamName, processDFSTourLineup, joinDFSTour, getUserDFSTourLineUps, switchTeamContest,joinDFSTourSeason,switchTeamContestNF } from '../../WSHelper/WSCallings';
import {DARK_THEME_ENABLE} from "../../helper/Constants";

var masterDataResponse = null;

export default class DFSTourSelectCaptainList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lineupArr: [],
            MasterData: [],
            LobyyData: [],
            allPosition: [],
            IsChanged: false,
            showConfirmationPopUp: false,
            TotalTeam: [],
            userTeamListSend: [],
            FixturedContest: [],
            isFrom: "",
            teamData: '',
            teamName:'',
            TourData: '',
            lineupMasterdId: '',
            showThankYouModal: false,
            rootDataItem: "",
            sportsSelected: AppSelectedSport,
            isFromMyTeams: false,
            ifFromSwitchTeamModal: false,
            isLoading: false,
            clickOnce: false,
            sort_field: 'salary',//fantasy_score
            sort_order: 'DESC',//ASC
            rosterList: [],
            isCategory: true,
            isClone: false,
            isTeamNameChanged: true,
            showFieldV: false,
            showUJC: false,
            TourDetail: [],
            isTourJoined: false
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
        },()=>{
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
            if (item.player_role == role || item.player_role == 0) {
                item.player_role = 0;
            }
            return item;
        })
        let index = _indexOf(lineupArr, player);
        lineupArr[index].player_role = (role === 1) ? "1" : "2";
        this.setState({ lineupArr })
        if(AppSelectedSport == SportsIDs.badminton){
            this.setState({ IsChanged: true })
        }
        else if((role === 1 && this.returnPlayerRole(2, lineupArr)) || (role === 2 && this.returnPlayerRole(1, lineupArr))){
            this.setState({ IsChanged: true })
        }
        else if ((Utilities.getMasterData().c_point > 0 && Utilities.getMasterData().vc_point <= 0) || (Utilities.getMasterData().vc_point > 0 && Utilities.getMasterData().c_point <= 0)) {            
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
            if (player.player_role == role) {
                return true
            }
        }
        return false
    }
    PlayerRoleClass = (role, player) => {
        let lineupArr = this.state.lineupArr;
        let LineupFilter = _filter(lineupArr, (o) => { return (o.player_uid == player.player_uid && o.player_role == role) });
        return LineupFilter.length == 1;
    }
    SubmitLineup = () => {
        if(this.checkButtonEnable()){
            this.setState({ clickOnce: true })
            if (this.isLoading) {
                return true;
            }
    
            let tmpLineupArray = [];
            let cap_ptID = '';
            let vcap_ptID = '';
    
            _Map(this.state.lineupArr, (item) => {
                let ptID = item.player_team_id;
                if(item.player_role == 1){
                    cap_ptID = ptID
                }
                if(item.player_role == 2){
                    vcap_ptID = ptID
                }
                tmpLineupArray.push(ptID)
            });
            let param = {
                "league_id": this.state.TourData.league_id,
                "sports_id": AppSelectedSport,
                "team_name": this.state.teamName,
                "tournament_season_id": this.state.LobyyData.tournament_season_id ? this.state.LobyyData.tournament_season_id : this.state.FixturedContest.tournament_season_id, //tournament_season_id
                "players": tmpLineupArray,
                "c_id":cap_ptID,
                "vc_id":vcap_ptID,
                "tournament_team_id": this.state.isClone ? '' : (this.props.location.state.teamitem ? this.props.location.state.teamitem.tournament_team_id ? this.props.location.state.teamitem.tournament_team_id :this.props.location.state.lineup_master_id  : (this.state.teamData.lineup_master_id ? this.state.teamData.lineup_master_id : this.state.lineupMasterdId))

            }
    
            this.setState({
                isLoading: true
            });
            processDFSTourLineup(param).then((responseJson) => {
                this.setState({
                    isLoading: false
                });
                if (responseJson.response_code == WSC.successCode) {
                    let keyName = 'my-teams' + Utilities.getSelectedSportsForUrl() + param.tournament_season_id;
                    preTeamsList[keyName] = [];

                    if (responseJson.data.tournament_team_id) {
                        let keyy = responseJson.data.tournament_team_id + param.tournament_season_id + 'lineup';
                        globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                        this.setState({ lineupMasterdId: responseJson.data.tournament_team_id })
                    }else{
                        let keyy = param.tournament_team_id + param.tournament_season_id + 'lineup';
                        globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                    }
                    if (this.state.isFrom == 'editView') {
                        Utilities.showToast(responseJson.message, 1000);
                        var go_index = -2;
                        WSManager.clearLineup();
                        this.props.history.go(go_index);
                    }
                    else {
                            this.getUserLineUpListApi();
                    }
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, 'confirmteam');
    
                }
                this.setState({ clickOnce: false })
            })
        }
    }

    switchTeam(FixturedContest, lineup_master_id, lineup_master_contest_id) {
        let param = {
            "sports_id": AppSelectedSport,
            "contest_id": FixturedContest.contest_id,
            "lineup_master_id":lineup_master_id,
            "lineup_master_contest_id": lineup_master_contest_id,
        }

        let apiCall =FixturedContest.is_network_contest == 1 ? switchTeamContestNF :switchTeamContest
        this.setState({ isLoaderShow: true })
        apiCall(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain'} });
                WSManager.clearLineup();
            }
        })

    }

    getTeamName() {
        if (!this.state.teamName) {
            let param = {
                "tournament_season_id": this.state.LobyyData.collection_master_id ? this.state.LobyyData.collection_master_id : this.state.FixturedContest.collection_master_id,
            }
            getDFSTourNewTeamName(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({ teamName: responseJson.data.team_name, isTeamNameChanged: false }, () => {
                        this.setState({ isTeamNameChanged: true })
                    })
                }
            })
        }
    }

    getUserLineUpListApi() { 
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_season_id": this.state.LobyyData.tournament_season_id ? this.state.LobyyData.tournament_season_id : this.state.FixturedContest.tournament_season_id,
            "league_id": this.state.TourData.league_id
        }
        this.setState({ isLoaderShow: true })
        getUserDFSTourLineUps(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    showConfirmationPopUp: true,
                    TotalTeam: responseJson.data,
                    userTeamListSend: responseJson.data
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


    ConfirmEvent = (dataFromConfirmPopUp, context) => {
        if (dataFromConfirmPopUp.selectedTeam.value.lineup_master_id && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            var currentEntryFee = 0;
            currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
            if(SELECTED_GAMET == GameType.Free2Play) {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            }
            else if (
                (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) || 
                (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent))) || (dataFromConfirmPopUp.isDFSTour && dataFromConfirmPopUp.TourDetail.user_info.is_joined == '1')
                ) 
            {
                this.CallJoinGameApi(dataFromConfirmPopUp);
            } 
            else {
                if(dataFromConfirmPopUp.FixturedContestItem.currency_type == 2){
                    if(Utilities.getMasterData().allow_buy_coin == 1){     
                        WSManager.setFromConfirmPopupAddFunds(true);
                        WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                        WSManager.setPaymentCalledFrom("SelectCaptainList")
                        this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true , state: {isFrom : 'SelectCaptainList',isDFSTour: true}});

                    }
                    else{
                        this.props.history.push({ pathname:'/earn-coins', state: {isFrom : 'lineup-flow',isDFSTour: true}})
                    }
                }
                else{
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true,isDFSTour: true });
                }

                //Analytics Calling 
                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'paymentgateway');
            }
        }
    }

    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        this.props.history.push({ pathname: '/lineup', state: { FixturedContest: dataFromConfirmFixture, LobyyData: dataFromConfirmLobby, current_sport: AppSelectedSport} })
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let teamId = dataFromConfirmPopUp.selectedTeam.value.tournament_team_id
        let param ={
            "tournament_season_id":this.state.LobyyData.tournament_season_id ? this.state.LobyyData.tournament_season_id : this.state.FixturedContest.tournament_season_id,
            "tournament_team_id": teamId
        }
        if(!this.state.isTourJoined){
            param['tournament_id'] = this.state.LobyyData.tournament_id ? this.state.LobyyData.tournament_id : this.state.FixturedContest.tournament_id;
        }
        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        this.setState({ isLoaderShow: true })

        let apiCall = this.state.isTourJoined ? joinDFSTourSeason : joinDFSTour;
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if(process.env.REACT_APP_SINGULAR_ENABLE > 0)
                {
                    let singular_data = {};
                    singular_data.user_unique_id    = WSManager.getProfile().user_unique_id;
                    singular_data.contest_id        = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                    singular_data.contest_date      = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                    singular_data.fixture_name      = dataFromConfirmPopUp.lobbyDataItem.collection_name;
                    singular_data.entry_fee         = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

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
                    WSManager.updateFirebaseUsers(contestUid,deviceIds);
                }
                this.ConfirmatioPopUpHide();
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if(Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data.self_exclusion_limit == 1 ){
                    this.ConfirmatioPopUpHide();
                    this.showUJC(); 
                }
                else{
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

    componentDidMount = () => {
        masterDataResponse = Utilities.getMasterData()
        setTimeout(() => {
            if (!_isEmpty(this.state.lineupArr)) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.lineupArr, this.state.masterData, _isEmpty(this.state.LobyyData) || _isUndefined(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest);
            }
        }, 500)

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
            checkBanState(this.state.FixturedContest,CustomHeader, 'CAP')
        }
    }

    goToLobby = () => {
        this.props.history.push({ pathname: '/' });
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
        if(Utilities.getMasterData().a_dfst == 1){
            ls.set('isDfsTourEnable',true)
        }
        this.setLocationStateData();
        WSManager.setPickedGameType(GameType.DFS);
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
    
    setLocationStateData=()=>{
        if (this.props && this.props.location && this.props.location.state) {
            const {SelectedLineup, MasterData, LobyyData, FixturedContest, isFrom, isFromMyTeams, team, teamitem, teamName, rootDataItem, isClone, ifFromSwitchTeamModal, TourData, TourDetail, isTourJoined} = this.props.location.state;
            this.setState({
                lineupArr: SelectedLineup,
                MasterData: MasterData,
                LobyyData: LobyyData,
                allPosition: MasterData.all_position,
                FixturedContest: FixturedContest,
                isFrom: !_isUndefined(isFrom) && isFrom == 'editView' ? isFrom : !_isUndefined(isFrom) && isFrom == 'contestJoin' ? isFrom : isFrom == 'MyTeams' ? isFrom : "",
                teamData: !_isUndefined(isFrom) && isFrom == 'editView' ? team : '',
                teamName: (teamitem && teamitem.team_name != '') ? teamitem.team_name : (isClone ? '' : (!_isUndefined(isFrom) && isFrom == 'editView' || isFrom == 'MyContest' ? (team && team.team_name) : teamName)),
                TourData: (TourData ? TourData : ''),
                rootDataItem: !_isUndefined(isFrom) && isFrom == 'editView' ? rootDataItem : !_isUndefined(FixturedContest) ? FixturedContest : "",
                isFromMyTeams: isFromMyTeams ? isFromMyTeams : false,
                ifFromSwitchTeamModal: !_isUndefined(ifFromSwitchTeamModal) ? ifFromSwitchTeamModal : false,
                isClone: !_isUndefined(isClone) ? isClone : false,
                TourDetail: TourDetail || [],
                isTourJoined: isTourJoined || false,
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

    checkButtonEnable(){
        var isValid = true;
        var teamname = this.state.teamName ? this.state.teamName: this.props.location.state.team_name
        if(!teamname || teamname.length < 4 || !this.state.IsChanged){
            isValid = false;
        }
        else if(this.state.isLoading || this.state.clickOnce){
            isValid = false;
        }
        return isValid;        
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

    render() {
        const {
            teamName,
            allPosition,
            showConfirmationPopUp,
            userTeamListSend,
            showThankYouModal,
            lineupMasterdId,
            sportsSelected,
            showUJC,
            TotalTeam,
            TourDetail
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            hideShadow: true,
            title: '',
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
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
                                                validationState={teamName ? "success" : this.props.location.state.team_name  && this.getValidationState('teamName', teamName ? teamName : this.props.location.state.team_name)}
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
                                        </Col>
                                    }
                                </Row>
                            </div>
                            {(Utilities.getMasterData().c_point > 0 || Utilities.getMasterData().vc_point > 0) &&
                                <div className="selection-help-txt">
                                    <h4>
                                        {
                                            (sportsSelected == SportsIDs.badminton) ?
                                                <React.Fragment>
                                                    {Utilities.getMasterData().c_point > 0 &&
                                                        <span>{AppLabels.CHOOSE_CAPTAIN}</span>
                                                    }
                                                </React.Fragment>
                                                :
                                                <span>
                                                    {Utilities.getMasterData().c_point > 0 && Utilities.getMasterData().vc_point <= 0 &&
                                                        AppLabels.CHOOSE_CAPTAIN
                                                    }
                                                    {Utilities.getMasterData().c_point > 0 && Utilities.getMasterData().vc_point > 0 &&
                                                        AppLabels.CHOOSE_CAPTAIN_VICE_CAPTAIN
                                                    }
                                                    {Utilities.getMasterData().c_point <= 0 && Utilities.getMasterData().vc_point > 0 && 
                                                        AppLabels.CHOOSE_VICE_CAPTAIN
                                                    }
                                                </span>
                                        }
                                    </h4>
                                    <p>
                                        {Utilities.getMasterData().c_point > 0 &&
                                            <span>
                                                <span className='captain_cirlce'>{AppLabels.C}</span>
                                                {AppLabels.GETS}
                                                {Utilities.getMasterData() != null && 
                                                    ((sportsSelected != SportsIDs.badminton) 
                                                        ? 
                                                        <>
                                                        {Utilities.getMasterData().c_point + 'x'}
                                                        </>
                                                        : 
                                                        <>
                                                        {Utilities.getMasterData().vc_point + 'x'}
                                                        </>
                                                    )
                                                }
                                                <React.Fragment> {AppLabels.POINTS}</React.Fragment>
                                            </span>
                                        }
                                        {Utilities.getMasterData().vc_point > 0 && 
                                            <React.Fragment>
                                                {
                                                    (sportsSelected != SportsIDs.badminton) &&
                                                    <span>
                                                        <span className='captain_cirlce'>{AppLabels.VC}</span>
                                                        {AppLabels.GETS}
                                                        {Utilities.getMasterData() != null && 
                                                            <>
                                                            { Utilities.getMasterData().vc_point + 'x'}
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
                            <div className={"sorting-pts-player" + ((Utilities.getMasterData().c_point > 0 || Utilities.getMasterData().vc_point > 0) ? " mt-0" : "")}>
                                <Row >
                                    <Col xs={12}>
                                        <span>{AppLabels.SORT_BY} -</span>

                                        <button onClick={() => this.onPlayers()} className={" btns " + (this.state.isCategory ? 'btnsblue' : '')} >{AppLabels.PLAYERS} </button>

                                        <button onClick={() => this.onPoints()} className={" btns " + (!this.state.isCategory ? 'btnsblue' : '')} >{AppLabels.POINTS} </button>
                                    </Col>
                                </Row>
                            </div>
                            {this.state.isCategory ?
                                <div className="lineup-list-view">
                                    <div className="list-view-detail">
                                        {_Map(allPosition, (positem, posidx) => {
                                            return (
                                                <div key={posidx} className="list-view-header-wrap">
                                                    <div className="list-view-header"> {positem.position_display_name} </div>
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
                                                                            <Col xs={(Utilities.getMasterData().c_point > 0 || Utilities.getMasterData().vc_point > 0 ) ? 3 : 6} className="text-right">
                                                                                <ul className="roster-player-salary">
                                                                                    <li>
                                                                                        <span className="pts-style" >{item.fantasy_score} {AppLabels.PTS}</span>
                                                                                    </li>
                                                                                </ul>
                                                                            </Col>
                                                                            {
                                                                                (Utilities.getMasterData().c_point > 0 || Utilities.getMasterData().vc_point > 0) &&
                                                                                    <Col xs={3} className="text-right-ltr">
                                                                                        <ul className="list-inline-capt pt2">
        
                                                                                            {Utilities.getMasterData().c_point > 0 &&
                                                                                                <li>
                                                                                                    <a onClick={() => this.ChangePlayerRole(1, item)} className={this.PlayerRoleClass(1, item) ? 'selected-captain' : ''}>
                                                                                                        {!this.PlayerRoleClass(1, item) ?
                                                                                                            <span className='captain-c'>C</span> 
                                                                                                            : 
                                                                                                            <span className="captain-c">    
                                                                                                                {masterDataResponse != null && 
                                                                                                                    <>
                                                                                                                        { masterDataResponse.c_point + 'x'}
                                                                                                                    </>
                                                                                                                }
                                                                                                            </span>
                                                                                                        }
                                                                                                    </a>
                                                                                                </li>
                                                                                            }
                                                                                            {Utilities.getMasterData().vc_point > 0 &&
                                                                                                <React.Fragment>
                                                                                                    {AppSelectedSport != SportsIDs.badminton &&
                                                                                                        <li>
                                                                                                            <a onClick={() => this.ChangePlayerRole(2, item)} className={this.PlayerRoleClass(2, item) ? 'selected-vcaptain' : ''}>
                                                                                                                {this.PlayerRoleClass(2, item) ?
                                                                                                                    <span className="vice-captain-v">
                                                                                                                        {
                                                                                                                            masterDataResponse != null &&
                                                                                                                            <>
                                                                                                                            { masterDataResponse.vc_point + 'x'}
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
                                                                            (Utilities.getMasterData().c_point > 0 || Utilities.getMasterData().vc_point > 0 ) &&
                                                                                <Col xs={3} className="text-right-ltr">
                                                                                    <ul className="list-inline-capt pt2">
                                                                                        {Utilities.getMasterData().c_point > 0 &&
                                                                                            <li >
                                                                                                <a onClick={() => this.ChangePlayerRole(1, item)} className={this.PlayerRoleClass(1, item) ? 'selected-captain' : ''}>
                                                                                                    {!this.PlayerRoleClass(1, item) ?
                                                                                                        <span className='captain-c'>C</span> : <span className="captain-c">
                                                                                                            {masterDataResponse != null &&
                                                                                                                <>
                                                                                                                { masterDataResponse.c_point + 'x'}
                                                                                                                </>
                                                                                                            }
                                                                                                            </span>
                                                                                                    }
                                                                                                </a>
                                                                                            </li>
                                                                                        }
                                                                                        {
                                                                                            Utilities.getMasterData().vc_point > 0 &&
                                                                                            <React.Fragment>
                                                                                                {AppSelectedSport != SportsIDs.badminton &&
                                                                                                    <li>
                                                                                                        <a onClick={() => this.ChangePlayerRole(2, item)} className={this.PlayerRoleClass(2, item) ? 'selected-vcaptain' : ''}>
                                                                                                            {this.PlayerRoleClass(2, item) ?
                                                                                                                 <span className="vice-captain-v">
                                                                                                                 {
                                                                                                                     masterDataResponse != null &&
                                                                                                                     <>
                                                                                                                     {masterDataResponse.vc_point + 'x'}
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
                       
                       
                        <button disabled={!this.checkButtonEnable()} onClick={() => this.SubmitLineup()} className="btn btn-primary  btn-block btm-fix-btn">{AppLabels.SUBMIT_LINEUP}</button>

                        {showConfirmationPopUp &&
                            <ConfirmationPopup lobbyDataToPopup={this.state.LobyyData} IsConfirmationPopupShow={this.ConfirmatioPopUpShow} IsConfirmationPopupHide={this.ConfirmatioPopUpHide} TeamListData={userTeamListSend} TotalTeam={TotalTeam} FixturedContest={TourDetail} ConfirmationClickEvent={this.ConfirmEvent} CreateTeamClickEvent={this.createTeamAndJoin} fromContestListingScreen={false} createdLineUp={lineupMasterdId} isDFSTour={true} TourDetail={TourDetail} />
                        }

                        {showThankYouModal &&
                            <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isDFSTour={true} />
                        }
                        {
                            this.state.lineupArr.length > 0 && <DFSTourFieldView
                                SelectedLineup={this.state.lineupArr}
                                MasterData={this.state.MasterData}
                                isFrom={'captain'}
                                team_name={this.state.teamName}
                                showFieldV={this.state.showFieldV}
                                hideFieldV={this.hideFieldV.bind(this)}
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