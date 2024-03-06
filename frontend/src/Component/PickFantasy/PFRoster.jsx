import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { GetPFLineupMasterData,GetPFAllRoster,GetPFFixtureDetails,GetPFTeamName ,GetPFUserLineupData} from "../../WSHelper/WSCallings";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { SELECTED_GAMET, GameType, DARK_THEME_ENABLE ,PFSelectedSport, globalLineupData,setValue} from '../../helper/Constants';
import { Utilities, _isUndefined, _isEmpty, _Map, _sumBy, _cloneDeep } from '../../Utilities/Utilities';
import { Col, ProgressBar, Row } from 'react-bootstrap';
import PFQueCard from "./PFQueCard";
import WSManager from "../../WSHelper/WSManager";
import PFRulesScoringModal from './PFRulesScoringModal';
import ls from 'local-storage';
import Slider from 'react-rangeslider';
import 'react-rangeslider/lib/index.css';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from '../CustomComponent';

export default class PFRoster extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            current_sports_id: PFSelectedSport.sports_id,
            seasonId: '',
            leagueId:'',
            masterData: [],
            boosterData: '',
            picksData: '',
            teamList: [],
            questionList: [],
            allQuestionList: [],
            isLoading: false,
            selectedOptions: ls.get('selOptArray') || {},
            ansCount: ls.get('selOptArray') ? Object.keys(ls.get('selOptArray')).length : 0,
            teamName: '',
            showRules: false,
            from:'',
            isClone: false,
            teamData: '',
            contestListData:[],
            isFromMyTeams:false,
            ifFromSwitchTeamModal:false,
            user_team_contest_id:'',
            seasonData: {},
            tieBreakerAns: '',
            tie_breaker_question: {},
            userTieValue: '',
        }
        this.headerRef = React.createRef();
    }

    //Logic Dynamic End
    UNSAFE_componentWillMount = () => {
        Utilities.setScreenName('lineup')
        this.setLocationStateData();
        WSManager.setPickedGameType(GameType.PickFantasy);
    }

    setLocationStateData() {
        if (this.props.location && this.props.location.state) {
            let data = this.props.location.state.nextStepData ? this.props.location.state.nextStepData : this.props.location.state
            const { FixturedContest,LobyyData,current_sport,isFrom,resetIndex,from,isFromMyTeams,season_id,rootDataItem,teamitem,league_id,queList,isClone,
                ifFromSwitchTeamModal,user_team_contest_id} = data;
            if (current_sport && current_sport != this.state.current_sports_id) {
                Utilities.showToast(AL.SOMETHING_ERROR, 3000);
                if (this.props.history) {
                    this.props.history.goBack();
                }
                return;
            }
            this.setState({
                LobyyData: LobyyData ? LobyyData : this.getFixtureDetails(season_id),
                contestListData: FixturedContest,
                FixturedContest: FixturedContest,
                from: from,
                isFrom: !_isUndefined(from) && from == 'editView' || from == 'MyTeams' || from == 'MyContestSwitchModal' || from == 'MyContest' ? from : !_isUndefined(from) && from == 'contestJoin' ? from : '',
                teamData: !_isUndefined(from) && from == 'editView' ? teamitem : '',
                // rootDataItem: !_isUndefined(from) && from == 'editView' ? rootDataItem : !_isUndefined(from) && from == 'contestJoin' ? rootDataItem : '',
                isFromMyTeams: !_isUndefined(isFromMyTeams) ? isFromMyTeams : false,
                seasonId: LobyyData ? LobyyData.season_id : season_id,
                leagueId: LobyyData ? LobyyData.league_id : league_id,
                queList: queList,
                isClone: isClone,
                ifFromSwitchTeamModal: ifFromSwitchTeamModal || false,
                user_team_contest_id: user_team_contest_id || ''
            }, () => {
                this.fetchLineupMasterData();
                this.getLobbyData();
                if (this.props.location.state.from == 'editView') {
                    this.getLineupForEdit();
                    this.setState({
                        isEditEnable: true
                    })
                }
            })
        }
    }

    getLineupForEdit() {
        let lineupID = this.props.location.state.teamitem.user_team_id ? this.props.location.state.teamitem.user_team_id : this.props.location.state.user_team_id
        let keyy = lineupID + this.props.location.state.season_id + 'lineup';

        let isEdit = ls.get('isPickEdit') ? true : false
        if(!isEdit){
            let param = {
                "user_team_id": lineupID,
                "season_id": this.props.location.state.season_id,
                "sports_id": this.state.LobyyData && this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : this.state.current_sports_id,
            }

            GetPFUserLineupData(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    let queList = _cloneDeep(responseJson.data.lineup);
                    let TBAns = queList != '' && queList[0].tie_breaker_answer
                    queList = {queList: queList,
                        user_team_id: lineupID,
                        team_name: this.state.teamData.team_name}
                    globalLineupData[keyy]=queList
                    this.setState({
                        queList: queList,
                        userTieValue:TBAns
                    },()=>{
                        ls.set('isPickEdit',1)
                        this.setQueData(queList)
                    })
                }
            })
        }
        // }
    }

    setQueData=(queList)=>{
        let lineupArr = queList.queList
        let tmpArray = []
        let selOptArray = []
        if (typeof lineupArr != 'undefined' && lineupArr.length > 0) {
            _Map(lineupArr,(obj,idx)=>{
                selOptArray = { ...selOptArray,
                    [obj.pick_id]: obj.user_answer
                }
                obj['answer'] = obj.user_answer
                if(obj.is_captain == 1){
                    obj['db'] = 1
                }
                if(obj.is_vc == 1){
                    obj['nn'] = 1
                }
                tmpArray.push(obj)
            })
        }
        
        ls.set('pickQueList',tmpArray)
        let TBData = this.state.tie_breaker_question
        ls.set('pickTBQueList',TBData)
        ls.set('selOptArray',selOptArray)
        ls.set('ansCount',Object.keys(selOptArray).length)
        ls.set('showMyTeam',1)
        // teamItem['team_name'] = lineupArr.team_name;
        this.setState({
            allQuestionList:tmpArray,
            selectedOptions: selOptArray,
            ansCount: Object.keys(selOptArray).length,
            teamName: queList.team_name
        })
    }

    getLobbyData() {
        if (this.state.LobyyData) {
            if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.selectedOptions, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
            if (this.state.isFrom != 'editView' || this.state.isClone) {
                this.getTeamName();
            }
            else if (this.state.isFrom == 'editView' && !this.state.isClone) {
                this.setState({ teamName: this.props.location.state.teamitem.team_name })
            }
        }
        else {
            setTimeout(() => {
                this.getLobbyData()
            }, 500);
        }
    }

    getTeamName() {
        let param = {
            "season_id": this.state.LobyyData.season_id ? this.state.LobyyData.season_id : this.state.FixturedContest.season_id,
        }
        GetPFTeamName(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ teamName: responseJson.data.team_name }, () => {
                    if (this.headerRef && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                        this.headerRef.current.GetHeaderProps("lineup", this.state.selectedOptions, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData, responseJson.data.team_name);
                    }
                })

            }
        })
    }

    getFixtureDetails = async (seasonId) => {
        let param = {
            "sports_id": this.state.LobyyData && this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : this.state.current_sports_id,
            "collection_master_id": seasonId,
        }
        let methodApi = GetPFFixtureDetails
        var api_response_data = await methodApi(param);

        if (api_response_data) {
            this.setState({
                LobyyData: api_response_data
            });
        }
    }

    fetchLineupMasterData = async () => {
        // if (globalLineupData[this.state.seasonId]) {
        //     this.parseMasterData(globalLineupData[this.state.seasonId]);
        // } else {
            let param = {
                "league_id": this.state.leagueId ? this.state.leagueId : '',
                "sports_id": this.state.LobyyData && this.state.LobyyData.sports_id ? this.state.LobyyData.sports_id : this.state.current_sports_id,
                "season_id": this.state.seasonId
            }
            var api_response_data = await GetPFLineupMasterData(param);
            if (api_response_data) {
                this.parseMasterData(api_response_data.data);
                globalLineupData[this.state.seasonId] = api_response_data.data;
            }
        // }
    }

    parseMasterData(api_response_data) {
        const { LobyyData } = this.state;
        this.setState({
            masterData: api_response_data,
            boosterData: api_response_data.booster,
            picksData: api_response_data.picks_data,
            teamList: api_response_data.team_list
        }, () => {
            if (LobyyData && !LobyyData.home && this.state.teamList.length > 1) {
                LobyyData.away = this.state.teamList[0].team_abbr || this.state.teamList[0].team_abbreviation;
                LobyyData.home = this.state.teamList[1].team_abbr || this.state.teamList[1].team_abbreviation;
            }
            if(ls.get('pickQueList')){
                this.setState({
                    allQuestionList:ls.get('pickQueList'),
                    tie_breaker_question:ls.get('pickTBQueList')
                },()=>{
                    this.setState({
                        userTieValue: this.state.allQuestionList != '' && this.state.allQuestionList[0].tie_breaker_answer
                    })
                    if(this.props.location.state.from == 'editView'){
                        this.getAllRoster(this.state.seasonId);
                    }
                })
            }
            else{
                // if(this.props.location.state.from != 'editView'){
                    this.getAllRoster(this.state.seasonId);
                // }
            }
            if (this.headerRef && this.headerRef.current && this.headerRef.current.GetHeaderProps && this.headerRef.current.GetHeaderProps != null) {
                this.headerRef.current.GetHeaderProps("lineup", this.state.selectedOptions, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
            }
        })
    }

    getAllRoster=async(seasonId,isReset)=>{
        if(isReset){
            this.setState({
                allQuestionList: [],
                selectedOptions: {},
                ansCount: 0,
                userTieValue: ''
            })
            ls.remove('selOptArray')
            ls.remove('pickQueList')
            ls.remove('pickTBQueList')
        }
        let param = {
            "season_id": seasonId
        }
        this.setState({
            isLoading: true
        })
        let apiresponse = await GetPFAllRoster(param)
        if(apiresponse){
            this.setState({
                allQuestionList: apiresponse.data.questions,
                seasonData: apiresponse.data.season,
                isLoading: false,
                tieBreakerAns: apiresponse.data.season.tie_breaker_answer,
                tie_breaker_question: this.callParseJson(apiresponse.data.season.tie_breaker_question)
            },()=>{
                // this.setState({
                //     userTieValue: apiresponse.data.season.tie_breaker_answer ? apiresponse.data.season.tie_breaker_answer : this.state.tie_breaker_question.start
                // })
                if(this.props.location.state.from == 'editView' && ls.get('pickQueList')){
                    this.updateUserSelectedQue(this.state.allQuestionList, ls.get('pickQueList'))
                }
            })
        }
    }

    callParseJson=(data)=>{
        try {
            return JSON.parse(data)
        } catch{
            return data
        }
    }

    updateUserSelectedQue=(allList,userList)=>{
        let tmpList = []
        _Map(allList,(data,idx)=>{
            let obj = userList.filter(d => d.pick_id == data.pick_id)
            if(obj.length > 0){
                tmpList.push(obj[0])
            }
            else{
                tmpList.push(data)
            }
        })
        this.setState({
            allQuestionList: tmpList,
            userTieValue:this.props.location.state.from == 'editView' ? this.state.userTieValue: tmpList[0].tie_breaker_answer
        })
    }

    checkPlayerExistInLineup(player) {
        var isExist = false;
        for (var selectedPlayer of this.state.lineupArr) {
            if (selectedPlayer.player_uid == player.player_uid) {
                isExist = true
                selectedPlayer['fantasy_score'] = player.fantasy_score
                break
            }
        }
        return isExist
    }

    selectAns=(item,opt)=>{
        let tmpArray = []
        let selOptArray = this.state.selectedOptions
        let count = this.state.ansCount

        _Map(this.state.allQuestionList,(obj,idx)=>{
            if(obj.pick_id == item.pick_id){
                if(!obj.answer){
                    count = count + 1
                }
                if(Object.keys(selOptArray).includes(obj.pick_id) && selOptArray[obj.pick_id] == opt){
                    delete selOptArray[obj.pick_id];
                    delete obj['answer'];
                    if(obj.is_vc){
                        delete obj['is_vc'];   
                        delete obj['nn'];
                    }
                    if(obj.is_captain){
                        delete obj['is_captain'];
                        delete obj['db'];
                    }
                }
                else{
                    selOptArray = { ...selOptArray,
                        [obj.pick_id]: opt
                    }
                    obj['answer'] = opt
                }
            }
            if(this.state.tie_breaker_question && this.state.userTieValue){
                obj['tie_breaker_answer'] = this.state.userTieValue
            }
            tmpArray.push(obj)
        })
        let TBData = this.state.tie_breaker_question
        ls.set('pickQueList',tmpArray)
        ls.set('pickTBQueList',TBData)
        ls.set('selOptArray',selOptArray)
        this.setState({
            allQuestionList: tmpArray,
            selectedOptions: selOptArray,
            ansCount: Object.keys(selOptArray).length
        },()=>{
            if (this.headerRef)
            this.headerRef.current.GetHeaderProps("lineup", this.state.selectedOptions, this.state.masterData, _isEmpty(this.state.LobyyData) ? this.state.rootDataItem : this.state.LobyyData, this.state.FixturedContest, this.state.isFrom, this.state.rootDataItem, this.state.teamData ? this.state.teamData : this.state.teamName);
             
        })
          
    }

    goToNextScreen=()=>{
        let dateformaturl = Utilities.getUtcToLocal(this.state.LobyyData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = '/picks-fantasy/apply-booster/' + this.state.LobyyData.home + "-vs-" + this.state.LobyyData.away + "-" + dateformaturl
        this.props.history.push({ 
            pathname: lineupPath.toLowerCase(), 
            state: { 
                FixturedContest: this.state.FixturedContest, 
                contestListData: this.state.contestListData,
                LobyyData: this.state.LobyyData, 
                resetIndex: 1, 
                current_sport: PFSelectedSport.sports_id, 
                isFrom: 'Roster',
                from:this.state.from,
                allQuestionList: this.state.allQuestionList,
                selectedOptions: this.state.selectedOptions,
                ansCount: this.state.ansCount,
                masterData: this.state.masterData,
                seasonId: this.state.seasonId,
                teamName: this.state.teamName,
                isClone: this.state.isClone,
                teamData: this.state.teamData,
                isFromMyTeams:this.state.isFromMyTeams || false,
                ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal,
                user_team_contest_id: this.state.user_team_contest_id,
                userTieValue: this.state.userTieValue
            } 
        })
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

    showdetailFn=(item)=>{
        let tmpArray = []
        _Map(this.state.allQuestionList,(obj,idx)=>{
            if(obj.pick_id == item.pick_id){
                obj['showDetail'] = obj.showDetail && obj.showDetail == true ? false : true;
            }
            tmpArray.push(obj)
        })
        this.setState({
            allQuestionList: tmpArray
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

    tieBreakerChange = (rangeValue) => {
        this.setState({
            userTieValue: rangeValue
        })
    }

    handleChangeComplete = () => {
        let tmpQueList = this.state.allQuestionList
        tmpQueList[0]['tie_breaker_answer'] = this.state.userTieValue
        this.setState({
            allQuestionList: tmpQueList 
        },()=>{
            ls.set('pickQueList',tmpQueList)
        })
    }

    render() {
        const {
            questionList,
            allQuestionList,
            isLoading,
            masterData,
            boosterData,
            picksData,
            teamList,
            seasonId,
            selectedOptions,
            ansCount,
            LobyyData,
            showRules,
            seasonData,
            tieBreakerAns,
            tie_breaker_question,
            userTieValue
        } = this.state;
        const HeaderOption = {
            back: true,
            fixture: true,
            filter: false,
            title: '',
            showAlertRoster: true,
            hideShadow: true,
            resetIndex: this.props.location.state.nextStepData ? this.props.location.state.nextStepData.resetIndex : this.props.location.state.resetIndex,
            // showRosterFilter: this.showRosterFilter,
            // showFilterByTeam: true,
            themeHeader: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            ShowRuleScoring: true,
            RuleScoringFn: this.showRulesScoring,
            isHideFlag: true
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container roster-web-container fixed-sub-header web-container-fixed PFRoster "}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.lineup.title}</title>
                            <meta name="description" content={MetaData.lineup.description} />
                            <meta name="keywords" content={MetaData.lineup.keywords}></meta>
                        </Helmet>
                        <CustomHeader 
                         LobyyData={LobyyData}
                         ref={this.headerRef}
                         HeaderOption={HeaderOption}
                         {...this.props} 
                        />
                        <div className="roster-quer-bar">
                            <ProgressBar now={this.ShowProgressBar(ansCount,picksData.question)} />
                            <div className="que-no">
                                {ansCount}<span>/</span>{picksData.question} {AL.QUESTIONS}
                            </div>
                        </div>
                        <div className="picks-que-wrap">
                            <div className="points-sec">
                                <div>
                                    <span className="text-success">+{picksData.correct} pts</span>
                                    {AL.FOR_CORRECT_PICK}
                                </div>
                                <div className="text-right">
                                    <span className="text-danger">{parseInt(picksData.wrong) > 0 && '-'}{picksData.wrong} pts</span>
                                    {AL.FOR_INCORRECT_PICK}
                                </div>
                            </div>
                            {
                                allQuestionList && allQuestionList.length > 0 && !isLoading &&
                                _Map(allQuestionList,(item,idx)=>{
                                    return(
                                        <PFQueCard 
                                            data={{
                                                content: item,
                                                queNo:idx + 1
                                            }}
                                            selectAns={this.selectAns}
                                            showDetailFn={this.showdetailFn}
                                        />
                                    )
                                })
                            }
                            {
                                tie_breaker_question && tie_breaker_question.question &&
                                // !(Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') >= Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ')) &&
                                <div className="pk-tie-breaker-block">
                                    <div className="tie-breaker-sec">
                                        {/* <div className={`tie-breaker-block  ${parseInt(userTieValue) != parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : ''}`}> */}
                                        <div className={`tie-breaker-block  ${(parseInt(userTieValue) ? parseInt(userTieValue) : parseInt(tie_breaker_question.start) )!= parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : ''}`}>
                                        {/* <div className={`tie-breaker-block ${parseInt(userTieValue) != parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-D HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-D HH:mm ') ? ' disabled' : '')}`}> */}
                                            <div className='overlay'></div>
                                            <div className="tp-sec">
                                                <span className="tag">{AL.TIE_BREAKER}</span>
                                                <div className="timer-section cust-timer">
                                                <div className="timer-section cust-timer">
                                                    {
                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(LobyyData.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                        <>
                                                            {
                                                                Utilities.showCountDown({ game_starts_in: LobyyData.game_starts_in })
                                                                    ?
                                                                    <div className={"countdown-timer-section"}>
                                                                        {
                                                                            LobyyData.game_starts_in && <CountdownTimer
                                                                                timerCallback={this.props.timerCompletionCall}
                                                                                deadlineTimeStamp={LobyyData.game_starts_in} />
                                                                        }
                                                                    </div>
                                                                    :
                                                                    <MomentDateComponent data={{ date: LobyyData.start_date, format: "D MMM - hh:mm A " }} />
                                                            }
                                                        </>
                                                    }
                                                </div>
                                                </div>
                                                
                                                {/* <div className="timer-section cust-timer">
                                                    {
                                                        Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                        <>
                                                            {
                                                                Utilities.showCountDown({ game_starts_in: detail.game_starts_in })
                                                                    ?
                                                                    <div className={"countdown-timer-section"}>
                                                                        {
                                                                            detail.game_starts_in && <CountdownTimer
                                                                                timerCallback={this.props.timerCompletionCall}
                                                                                deadlineTimeStamp={detail.game_starts_in} />
                                                                        }
                                                                    </div>
                                                                    :
                                                                    <MomentDateComponent data={{ date: detail.start_date, format: "D MMM - hh:mm A " }} />
                                                            }
                                                        </>
                                                    }
                                                </div> */}
                                            </div>
                                            <div className="que-txt pick-que">
                                                <span className='checkbox'>
                                                    <i className="icon-tick-circular"></i>
                                                </span>
                                                <div>{tie_breaker_question.question}</div>

                                            </div>
                                            <div className='slider'>
                                                <Slider
                                                    disabled={true}
                                                    min={parseInt(tie_breaker_question.start)}
                                                    max={parseInt(tie_breaker_question.end)}
                                                    value={userTieValue}
                                                    onChange={this.tieBreakerChange}
                                                    handleLabel={userTieValue}
                                                    tooltip={false}
                                                    onChangeComplete={this.handleChangeComplete}
                                                />
                                                <div className="tie-breaker-value">
                                                    <span>{tie_breaker_question.start}</span>
                                                    <span>{tie_breaker_question.end}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="tie-breaker-info">
                                            {AL.TIE_BREAKER_INFO}
                                        </div>
                                    </div>
                                </div>
                            }
                        </div>
                        <div className={"roster-footer roster-footer-more-down"}>
                            <div className="btn-wrap">
                                <button className="btn btn-primary btn-block btm-fix-btn team-preview" disabled={ansCount == 0} onClick={()=>this.getAllRoster(seasonId,true)}>
                                    {AL.RESET_THE_PICKS}
                                </button>
                                <button className="btn btn-primary btn-block btm-fix-btn" disabled={ansCount == 0} onClick={()=>this.goToNextScreen()}>{AL.NEXT}</button>
                            </div>
                        </div>
                        {showRules &&
                            <PFRulesScoringModal MShow={showRules} MHide={this.hideRulesScoring} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}