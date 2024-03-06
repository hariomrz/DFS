import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import FloatingLabel from 'floating-label-react';
import CustomHeader from '../../components/CustomHeader';
import * as AL from "../../helper/AppLabels";
import { SELECTED_GAMET, GameType, PFSelectedSport } from '../../helper/Constants';
import { Utilities, _Map, _isUndefined, _filter, _cloneDeep, checkBanState, parseURLDate } from '../../Utilities/Utilities';
import { Col, Row, FormGroup } from 'react-bootstrap';
import PFViewQueCard from "./PFViewQueCard";
import { inputStyleLeft , darkInputStyleLeft} from '../../helper/input-style';
import {GetPFLineupProcess,GetPFUserTeams,GetPFJoinGame,PFSwitchTeamContest} from "../../WSHelper/WSCallings";
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import {DARK_THEME_ENABLE} from "../../helper/Constants";
import PFRulesScoringModal from './PFRulesScoringModal';
import { Thankyou, ConfirmationPopup } from '../../Modals';
import ls from 'local-storage';

export default class PFBooster extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            FixturedContest: [], 
            LobyyData: [], 
            resetIndex: '', 
            current_sport: PFSelectedSport.sports_id, 
            isFrom: '',
            allQuestionList: [],
            selectedOptions: {},
            ansCount: 0,
            masterData: [],
            seasonId: '',
            boosters: {},
            selDobler: {},
            selNoNegative: {},
            DBooster: 1,
            NNBooster: 1,
            SelDB:0,
            SelNB: 0,
            teamName:'',
            clickOnce: false,
            showRules: false,
            from:'',
            showThankYouModal: false,
            showConfirmationPopUp:false,
            isClone: false,
            teamData: '',
            isFromMyTeams:false,
            userMasterdId: '',
            ifFromSwitchTeamModal:false,
            user_team_contest_id:'',
            userTieValue: ''
        }
        this.headerRef = React.createRef();
    }

    componentDidMount() { 
        console.log('first booster', this.props)
     }

    componentWillMount() { 
        this.setLocationProps()
    }

    setLocationProps=()=>{
        if(this.props && this.props.location && this.props.location.state){
            const {
                FixturedContest,LobyyData,current_sport,isFrom,resetIndex,allQuestionList,selectedOptions,ansCount,masterData,seasonId,teamName,from,
                isClone,teamData,isFromMyTeams,ifFromSwitchTeamModal,user_team_contest_id,userTieValue
            } = this.props.location.state
            this.setState({
                FixturedContest: FixturedContest, 
                LobyyData: LobyyData, 
                resetIndex: resetIndex, 
                current_sport: current_sport, 
                isFrom: isFrom,
                allQuestionList: allQuestionList,
                selectedOptions: selectedOptions,
                ansCount: ansCount,
                masterData: masterData,
                seasonId: seasonId,
                DBooster: masterData.booster && masterData.booster['2x'],
                NNBooster: masterData.booster && masterData.booster.NN,
                teamName: teamName,
                from: from,
                isClone: isClone,
                teamData: !_isUndefined(from) && from == 'editView' ? teamData : '',
                isFromMyTeams: !_isUndefined(isFromMyTeams) ? isFromMyTeams : false,
                ifFromSwitchTeamModal: ifFromSwitchTeamModal || false,
                user_team_contest_id: user_team_contest_id || '',
                userTieValue: userTieValue || ''
            },()=>{
                let selAnsCount = Object.keys(selectedOptions).length
                let BoosterCount = selAnsCount > 8 ? 3 : selAnsCount > 3 ? 2 : 1
                this.setState({
                    DBooster: BoosterCount,
                    NNBooster: BoosterCount
                })
                if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                    this.headerRef.current.GetHeaderProps("lineup", this.state.allQuestionList, this.state.masterData, this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.LobyyData, this.state.teamName);
                }
                if(from=='editView'){
                    this.setSelBooster(allQuestionList)
                }
            })
        }
    }

    setSelBooster=(allQuestionList)=>{
        let SelDB = this.state.SelDB
        let SelNB = this.state.SelNB
        _Map(allQuestionList,(que,idx)=>{
            if(que.is_captain == 1){
                SelDB = SelDB + 1
            }
            if(que.is_vc == 1){
                SelNB = SelNB + 1
            }
        })
        this.setState({
            SelDB:SelDB,
            SelNB:SelNB
        })
    }

    renderBoosterBlock=(isFor)=>{
       // isFor == 0 for doubler, 1 for no negative
        let count = isFor == 0 ? this.state.DBooster : this.state.NNBooster
        let renderSec = []
        for(let i = 0;i<count;i++){
            renderSec.push(['span'])
        }
        return renderSec
    }

    applyBooster=(item,isFor)=>{
        //isFor 0 for doubler, 1 for no negative
        let tmpArray = []
        let SelNB = this.state.SelNB
        let SelDB = this.state.SelDB
        _Map(this.state.allQuestionList,(data,idx)=>{
            if(data.pick_id == item.pick_id){
                // DBooster
                if(isFor == 0){
                    if(data.db == 1 || SelDB != this.state.DBooster){
                        SelDB = data.db && data.db == 1 ? SelDB - 1 : SelDB + 1
                        data['db'] = data.db ? 0 : 1 
                    }
                }
                else{
                    if(data.nn == 1 || SelNB != this.state.NNBooster){
                        SelNB = data.nn && data.nn == 1 ? SelNB - 1 : SelNB + 1
                        data['nn'] = data.nn ? 0 : 1 
                    }
                    // data['nn'] = data.nm ? 0 : 1
                }
            }
            tmpArray.push(data)
        })
        this.setState({
            allQuestionList: tmpArray,
            SelDB:SelDB,
            SelNB: SelNB
        })
    }

    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value });
    }

    checkButtonEnable(){
        var isValid = true;
        var teamname = this.state.teamName ? this.state.teamName: this.props.location.state.team_name
        if(!teamname || teamname.length < 4 || this.state.SelDB != this.state.DBooster || this.state.SelNB != this.state.NNBooster){
            isValid = false;
        }
        else if(this.state.isLoading || this.state.clickOnce){
            isValid = false;
        }
        return isValid;        
    }

    SubmitLineup = () => {
        if(this.checkButtonEnable()){
            this.setState({ clickOnce: true })
            if (this.isLoading) {
                return true;
            }
    
            let tmpQueList = {};
            let tmpDB = [];
            let tmpNB = [];
    
            _Map(this.state.allQuestionList, (item) => {
                tmpQueList={
                    ...tmpQueList,
                    [item.pick_id] : item.answer
                }
                if(item.db == 1){
                    tmpDB.push(item.pick_id)
                }
                if(item.nn == 1){
                    tmpNB.push(item.pick_id)
                }
            });
            let param = {
                "league_id": this.state.LobyyData.league_id ? this.state.LobyyData.league_id : this.state.FixturedContest.league_id,
                "season_id": this.state.LobyyData.season_id ? this.state.LobyyData.season_id : this.state.FixturedContest.season_id,
                "sports_id": this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : (this.state.FixturedContest && this.state.FixturedContest.sports_id || PFSelectedSport.sports_id) ,
                // PFSelectedSport.sports_id == 0 ? this.state.LobyyData.sports_id : PFSelectedSport.sports_id,
                "team_name": this.state.teamName,
                "picks": {"picks" : tmpQueList},
                "c_id": tmpDB,
                "vc_id":tmpNB,
                "user_team_id": !_isUndefined(this.state.from) && this.state.from == 'editView' ? this.state.teamData.user_team_id : '',
                "tie_breaker_answer": this.state.userTieValue
            }
            this.setState({
                isLoading: true
            });
            GetPFLineupProcess(param).then((responseJson) => {
                this.setState({
                    isLoading: false
                });
                if (responseJson.response_code == WSC.successCode) {
                    if (responseJson.data.user_team_id) {
                        // let keyy = responseJson.data.lineup_master_id + param.collection_master_id + 'lineup';
                        // globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                        this.setState({ userMasterdId: responseJson.data.user_team_id })
                    }
                    // else{
                    //     let keyy = param.lineup_master_id + param.collection_master_id + 'lineup';
                    //     globalLineupData[keyy] = _cloneDeep(this.state.lineupArr);
                    // }
                    if ((this.state.from == "MyTeams" || this.state.from == "MyContest" || this.state.from == "editView") ){
                        Utilities.showToast(responseJson.message, 5000);
                    // && this.state.isFromMyTeams) {
                        var go_index = -2;
                        if (this.state.from == "editView" && !this.state.isFromMyTeams) {
                            go_index = -3;
                        }
                        WSManager.clearLineup();
                        this.props.history.go(go_index);
                    }
                    else if (this.state.ifFromSwitchTeamModal) {
                        this.switchTeam(this.state.FixturedContest, this.state.userMasterdId, this.state.user_team_contest_id);
            
                    }
                    else {
                        if (checkBanState(this.state.FixturedContest, CustomHeader, 'CAP')) {
                            this.getUserLineUpListApi();
                        }
                    }
                }
                this.setState({ clickOnce: false })
            })
        }
    }

    
    switchTeam(FixturedContest, userMasterdId, user_team_contest_id) {
        let param = {
            "sports_id": this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : (this.state.FixturedContest.sports_id || PFSelectedSport.sports_id),
            // "sports_id": PFSelectedSport.sports_id,
            "contest_id": FixturedContest.contest_id,
            "user_team_id":userMasterdId,
            "user_contest_id": user_team_contest_id,
        }

        this.setState({ isLoaderShow: true })
        PFSwitchTeamContest(param).then((responseJson) => {
            this.setState({ isLoaderShow: false })
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000);
                this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
                WSManager.clearLineup();
            }
        })
    }

    getUserLineUpListApi() {
        let param = {
            "sports_id": this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : (this.state.FixturedContest.sports_id || PFSelectedSport.sports_id),
            // "sports_id": PFSelectedSport.sports_id,
            "season_id": this.state.LobyyData.season_id ? this.state.LobyyData.season_id : this.state.FixturedContest.season_id,
        }
        this.setState({ isLoaderShow: true })
        GetPFUserTeams(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let tList = responseJson.data
                this.setState({
                    showConfirmationPopUp: true,
                    TotalTeam: tList,
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
        })
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
                    this.goToLobby()
                }
            }
        })
    }

    createTeamAndJoin = (dataFromConfirmFixture) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            let urlData = this.state.LobyyData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

            this.props.history.push({ pathname: '/pick-fantasy/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: this.state.LobyyData, resetIndex: 1, current_sport: PFSelectedSport.sports_id } })
           
        }
    }

    goToLobby = () => {
        ls.remove('selOptArray')
        ls.remove('pickQueList')
        // this.props.history.push({ pathname: '/' });
        const {LobyyData,FixturedContest} = this.state;
        let dateformaturl = parseURLDate(LobyyData.scheduled_date);
        let contestListingPath = '/' +
          Utilities.getPFSelectedSportsForUrl().toLowerCase() + "/pick-fantasy/contest-listing/" + LobyyData.season_id +'/' + LobyyData.league_name + "-" + LobyyData.home +
          "-vs-" + LobyyData.away + "-" + dateformaturl;
        let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET);
        this.props.history.push({
          pathname: CLPath,
          state: {
            FixturedContest: this.state.FixtureData,
            LobyyData: LobyyData,
            // lineupPath: CLPath,
          },
        });


        // let dateformaturl = Utilities.getUtcToLocal(LobyyData.scheduled_date);
        // dateformaturl = new Date(dateformaturl);

        // let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        // let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)

        // let home = FixturedContest.home || LobyyData.home;
        // let away = FixturedContest.away || LobyyData.away;

      
        // dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        // let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + FixturedContest.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET) 
        // this.setState({ LobyyData: FixturedContest });
        // contestListingPath = contestListingPath.toLowerCase()
        // this.props.history.push({ pathname: contestListingPath, state: { FixturedContest: this.state.FixtureData, LobyyData: LobyyData, isFromPM: true,isJoinContestFlow: true } })
    
    
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
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

    showRulesScoring=()=>{
        this.setState({
            showRules: true
        })
    }

    hideRulesScoring=()=>{
        this.setState({
            showRules: false
        })
    }

    userQuesAns=(list)=>{
        let UserQueList = list.filter(obj => obj.answer)
        return UserQueList
    }

    render() {
        const {
            DBooster,
            NNBooster,
            allQuestionList,
            SelDB,
            SelNB,
            teamName,
            showRules,
            LobyyData,
            showConfirmationPopUp,
            TotalTeam,
            userTeamListSend,
            showThankYouModal,
            FixturedContest,
            masterData
        } = this.state;
        let totalBooster = parseInt(DBooster) + parseInt(NNBooster)
        const HeaderOption = {
            back: true,
            fixture: true,
            hideShadow: false,
            title: '',
            // showAlertRoster: true,
            themeHeader: true,
            isPrimary: true,
            isbooster: true,
            boosterdata: AL.APPLY + ' '+ totalBooster + ' '+ AL.BOOSTERS,
            ShowRuleScoring: true,
            RuleScoringFn: this.showRulesScoring
        }

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container apply-booster-container " + `${this.state.activeClass}`}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader ref={this.headerRef} {...this.props} HeaderOption={HeaderOption} LobyyData={LobyyData} />
                        <div className="booster-header">
                            <div className="booster-tracker">
                                    <div className="booster-text booster-text-left">
                                        <span>{AL.DOUBLER} </span>
                                        {'('+ SelDB + '/' + DBooster + ')'}
                                    </div>
                                    <div className="booster-text booster-text-right">
                                        {'('+ SelNB + '/' + NNBooster + ')'}
                                        <span> {AL.NO_NEGATIVE}</span>
                                    </div>
                                    <div className="side-booster left-side-booster"> 
                                        <span>
                                            <i className="icon-2x-point"></i>
                                        </span>
                                    </div>
                                    <div className="tracker-slider">
                                        <div className="text-left tracker-slider-inner">
                                            {_Map(this.renderBoosterBlock(0),(item,idx)=>{
                                                return (
                                                    <span className={(SelDB == idx + 1 || SelDB > idx + 1) && 'selected'}></span>
                                                )
                                            })}
                                        </div>
                                        <div className="text-right tracker-slider-inner">
                                            {_Map(this.renderBoosterBlock(1),(item,idx)=>{
                                                return (
                                                    <span className={(SelNB == idx + 1 || SelNB > idx + 1) && 'selected'}></span>
                                                )
                                            })}
                                        </div>
                                    </div>
                                    <div className="side-booster right-side-booster">
                                        <span>
                                            <i className="icon-no-negative"></i>
                                        </span>
                                    </div>
                            </div>
                            <div className="team-name-sec">
                                <Row >
                                    <Col xs={12}>
                                        <FormGroup
                                            className='input-label-center'
                                            controlId="formBasicText"
                                        >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='teamName'
                                                    name='teamName'
                                                    placeholder={AL.ENTER_TAM_NAME}
                                                    type='text'
                                                    value={teamName}
                                                    onChange={this.handleChange}
                                                />
                                        </FormGroup>
                                    </Col>
                                </Row>                            
                            </div>
                        </div>                        
                        <div className="apply-booster-que-wrap">
                            {
                                _Map(this.userQuesAns(allQuestionList),(item,idx)=>{
                                    return (
                                        <PFViewQueCard 
                                            data ={{
                                                addBooster: true,
                                                que: item,
                                                queNo: idx+1,
                                                selOpt: this.state.selectedOptions[item.pick_id],
                                                picks_data: masterData.picks_data
                                            }}
                                            applyBooster={this.applyBooster}
                                        />
                                    )
                                })
                            }
                        </div>
                        <button disabled={!this.checkButtonEnable()} onClick={() => this.SubmitLineup()} 
                            className="btn btn-primary  btn-block btm-fix-btn">{AL.SUBMIT}</button>
                        {showRules &&
                            <PFRulesScoringModal MShow={showRules} MHide={this.hideRulesScoring} />
                        }

                        {
                            showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={showConfirmationPopUp}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={userTeamListSend}
                                TotalTeam={TotalTeam}
                                FixturedContest={FixturedContest}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={LobyyData}
                                fromContestListingScreen={true}
                                createdLineUp={this.state.lineup_master_id}
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