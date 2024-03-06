import React from 'react';
import CustomHeader from '../../components/CustomHeader';
import * as Constants from "../../helper/Constants";
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Col, Row } from 'react-bootstrap';
import { GetPFUserLineupData } from '../../WSHelper/WSCallings';
import { Utilities, _isEmpty ,_Map, _filter} from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import ls from 'local-storage';
import PFViewQueCard from './PFViewQueCard';
import Slider from 'react-rangeslider';
import 'react-rangeslider/lib/index.css';
import * as AppLabels from '../../helper/AppLabels'
export default class PFViewPick extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            LobyyData: [],
            isFrom: '',
            isEdit: false,
            teamData:{},
            current_sport: Constants.PFSelectedSport.sports_id,
            allQuestionList:[],
            selectedOptions: {},
            ansCount: 0,
            teamName: '',
            FixturedContest:[],
            isFromMyTeams: false,
            status:'',
            contestInfo:'',
            pickData: {},
            tie_breaker_question: '',
            userTieValue: '',
            lineupDta:{}
        }
    }

    componentDidMount() {
        this.setlocaltionProps()
    }

    setlocaltionProps=()=>{
        if (this.props && this.props.location && this.props.location.state) {
            const {teamData,isEdit,LobyyData,isFrom,current_sport,FixturedContest,isFromMyTeams,status} = this.props.location.state

            this.setState({
                LobyyData: LobyyData,
                isFrom: isFrom,
                isEdit: isEdit,
                teamData:teamData,
                current_sport: current_sport,
                teamName: teamData.team_name,
                FixturedContest: FixturedContest,
                isFromMyTeams: isFromMyTeams || false,
                status: status || ''
            },()=>{
                this.getUserLineup()
            })
        }
    }

       
    UNSAFE_componentWillReceiveProps(nextProps) {
        console.log('nextProps',nextProps)
        if(nextProps.LobyyData && nextProps.LobyyData.away != this.state.LobyyData){
            this.setState({
                LobyyData: nextProps.LobyyData
            })
        }
    }

    getUserLineup=()=>{
        let param = {
            "user_team_id": this.state.teamData.user_team_id,
            "season_id": this.state.teamData.season_id,
            "sports_id": this.state.current_sport,
        }

        GetPFUserLineupData(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                let queList = responseJson.data.lineup;
                this.setState({
                    queList: queList,
                    contestInfo: responseJson.data.contest,
                    pickData: responseJson.data.picks_data,
                    lineupDta:responseJson.data.lineup[0],
                    tie_breaker_question: this.callParseJson(responseJson.data.picks_data.tie_breaker_question || ''),
                    userTieValue:queList != '' ? queList[0].tie_breaker_answer : ''
                },()=>{
                    this.setQueData(queList)
                })
            }
        })
    }

    callParseJson=(data)=>{
        try {
            return JSON.parse(data)
        } catch{
            return data
        }
    }

    setQueData=(queList)=>{
        let tmpArray = []
        let selOptArray = []
        if (typeof queList != 'undefined' && queList.length > 0) {
            _Map(queList,(obj,idx)=>{
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
        ls.set('selOptArray',selOptArray)
        ls.set('ansCount',Object.keys(selOptArray).length)
        this.setState({
            allQuestionList:tmpArray,
            selectedOptions: selOptArray,
            ansCount: Object.keys(selOptArray).length
        })
    }

    goToRosterEdit=()=>{
        let urlData = this.state.LobyyData;
        let dateformaturl = Utilities.getUtcToLocal(urlData.scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let lineupPath = '/pick-fantasy/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
        this.props.history.push({ 
            pathname: lineupPath.toLowerCase(), 
            state: {
                // SelectedLineup: this.state.lineupArr, MasterData: this.state.MasterData,
                LobyyData: _isEmpty(this.state.LobyyData) ? urlData : this.state.LobyyData, 
                FixturedContest: this.state.FixturedContest, 
                team: this.state.teamData, 
                from: 'editView', 
                rootDataItem: urlData, 
                isFromMyTeams: false, 
                // ifFromSwitchTeamModal: this.state.ifFromSwitchTeamModal, 
                resetIndex: 2, 
                teamitem: this.state.teamData, 
                season_id: this.state.LobyyData.season_id, 
                league_id: this.state.LobyyData.league_id , 
                current_sport: Constants.PFSelectedSport.sports_id 
            } 
        });   
    }

    render() {
        const {allQuestionList,LobyyData,teamName,isFrom,isEdit,status,contestInfo,teamData,pickData,tie_breaker_question,userTieValue,lineupDta} = this.state;
        let titleName = isFrom == 'Leaderboard' ? (teamData.user_id == ls.get('profile').user_id ? 'You' : teamData.user_name) : teamName
        let HeaderOption = {
            // title: AL.MY_CONTEST,
            viewPicks: true,
            viewPicksData: LobyyData,
            teamName: titleName,
            hideShadow: true,
            back: true,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
            editpick: isEdit,
            editpickFn: this.goToRosterEdit,
            status: status
        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container web-container-fixed" + (isFrom == 'Leaderboard' ? ' vpick-detail' : '')}>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {
                            isFrom == 'Leaderboard' &&
                            <div className="pick-detail">
                                <div className="pick-name">{contestInfo.team_name}</div>
                                <div className="pick-sub-dtl">
                                    <div>
                                        <div className='val'>{contestInfo.game_rank}</div>
                                        <div className='lbl'>{AL.RANK}</div>
                                    </div>
                                    <div>
                                        <div className='val'>{contestInfo.total_score}</div>
                                        <div className='lbl'>{AL.POINTS}</div>
                                    </div>
                                </div>
                            </div>
                        }
                        <div className="view-pick-sec">
                            {
                                _Map(allQuestionList,(item,idx)=>{
                                    return (
                                        <PFViewQueCard 
                                            data ={{
                                                viewPicks:true,
                                                que: item,
                                                queNo: idx+1,
                                                isLeaderboard:isFrom == 'Leaderboard' ? true : false,
                                                picks_data: pickData
                                                // selOpt: this.state.selectedOptions[item.pick_id]
                                            }}
                                        />
                                    )
                                })

                            }
                            {status != 1 && status != 3 && status != 2 &&
                                tie_breaker_question && tie_breaker_question.question &&
                                // !(Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') >= Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ')) &&
                                <div className="pk-tie-breaker-block">
                                    <div className="tie-breaker-sec">
                                        <div className={`tie-breaker-block ${parseInt(userTieValue) != parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : ''}`}>
                                        {/* <div className={`tie-breaker-block ${parseInt(userTieValue) != parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ') ? ' disabled' : '')}`}> */}
                                            <div className='overlay'></div>
                                            <div className="tp-sec">
                                                <span className="tag">{AL.TIE_BREAKER}</span>
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
                        {console.log('lineupDtalineupDtalineupDta', lineupDta)}
                        {pickData.tie_breaker_question != null && <div className='tie-breaker'>
                            <div className='tb-tag'>
                                {AppLabels.TIE_BREAKER}
                             </div>
                            <div className='title-tx'>{AppLabels.TOTAL_PTS_TXT}</div>
                            <div className='flex-tb-div'>
                               {lineupDta &&  <div className='point-counts'><span>{lineupDta.tie_breaker_answer||'--'}</span><br/>{AppLabels.YOUR_PRE}</div>} 
                                <div className='mid-line'/>
                                <div className='point-counts'><span>{pickData.tie_breaker_answer||'--'}</span><br/>{AppLabels.CORRECT_ANS}</div>
                            </div>
                        </div>}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}