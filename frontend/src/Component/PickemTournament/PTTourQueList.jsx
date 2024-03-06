import React, { Component } from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from '../../components/images';
import { Utilities, _isEmpty, _isNull, _isUndefined } from '../../Utilities/Utilities';
import * as AL from "../../helper/AppLabels";
import { CommonLabels } from '../../helper/AppLabels';
import { CircularProgressBar, MomentDateComponent } from "../CustomComponent";
import CountdownTimer from '../../views/CountDownTimer';
import { AppSelectedSport } from '../../helper/Constants';
import { ParticipantsModal } from 'Modals';

class PTTourQueList extends Component {
    constructor(props) {
      super(props)
    
      this.state = {
        showParticipants : false,
        participantsData : ''
      }
    }

    PickedPercentage = (picked, total) => {
        let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
        let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
        pickedPer = Math.round(checkpickedPer);
        return pickedPer;
    }

    callJsonParse = (data) => {
        try {
            return JSON.parse(data)
        } catch {
            return data
        }
    }
    showLiveMsg = () => {
        Utilities.showToast(AL.YOU_CANNOT_UPDATE_THE_SELECTION_NOW, 2000);
    }
    
    showParticipantsModal = (item) =>{
        if(item.total_season_count && item.total_season_count > 0){
            this.setState({ showParticipants: !this.state.showParticipants, participantsData : item })
        }else{
            Utilities.showToast(CommonLabels.NO_PARTICIPATION_TEXT, 2000);
        }
    }
//     hideParticipantsModal = () =>{
//         this.setState({ showParticipants: false, participantsData : '' })
//    }
   
    render() {
        const {showParticipants,participantsData} = this.state;
        const { item, isFor, detail, selectedawayScore, selectedhomeScore, activeSeasonId, scorePredictFor } = this.props   
        if (isFor == 'comp') {
            item['is_correct'] = item.team_id && item.team_id != '' && item.winning_team_id == item.team_id ? 1 : 0
        }
        let ScoreData = this.callJsonParse(item.score_data)

        let winGoal = Utilities.getMasterData().pickem_win_goal
        let winGoalDiff = Utilities.getMasterData().pickem_win_goal_diff
        let winOnly = Utilities.getMasterData().pickem_win_only

        let isScorePredictCorrect = ScoreData && !_isUndefined(ScoreData.away_score) && !_isUndefined(ScoreData.home_score) && (parseInt(ScoreData.away_score) == parseInt(item.away_predict)) && (parseInt(ScoreData.home_score) == parseInt(item.home_predict)) ? true : false
        return (

            <div className={`${detail.is_score_predict && detail.is_score_predict == 1 && isFor == 'comp' ? (!_isUndefined(item.team_id) ? '' : 'not-attempt-card') : ''}`}>
                {
                    isFor != 'live' && isFor != 'comp' &&
                    <div className='countdown-timer-view'>
                        {
                            Utilities.showCountDown({ game_starts_in: item.game_starts_in })
                                ?
                                // item.status == 4 ? <div className={"countdown-timer-section" + (isFor == 'upc' ? ' text-center text-uppercase' : '')}>{AL.CANCELED}</div> :
                                <div className={"countdown-timer-section" + (isFor == 'upc' ? ' text-center' : '')}>
                                    {
                                        item.game_starts_in && <CountdownTimer
                                            timerCallback={this.props.timerCallback}
                                            deadlineTimeStamp={item.game_starts_in} />
                                    }
                                </div>
                                :
                                <div className={"timer-section" + (isFor == 'upc' ? ' text-center' : '')}>
                                    <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                </div>
                        }
                    </div>
                }
                {
                    isFor == 'live' &&
                    <div className='countdown-timer-view'>
                        <div className="timer-section text-center ttl-par">
                            {
                                

                                // (item.total_season_count && parseInt(item.total_season_count) > 0) && (item.user_team_id) ?
                                    <a className={item.total_season_count && item.total_season_count > 0  && detail.is_score_predict == 0 ?  "anker-tg-view" : "" }
                                        href
                                        onClick={item.total_season_count && item.total_season_count > 0 &&  detail.is_score_predict == 0 ? () => this.showParticipantsModal(item) : '' }
                                    >
                                        {item.total_season_count ? item.total_season_count : 0} {AL.PARTICIPANTS}
                                    </a>
                                //     :
                                //     ''
                                // // <>{AL.YOU_DID_NOT_ATTEMPT}</>
                            }
                        </div>
                    </div>
                }

                <div className='countdown-timer-view'>
                    {
                        isFor == 'comp' &&
                        <>
                            {
                                item.status == 4 ?
                                    <div className="countdown-timer-section text-uppercase">
                                        {AL.MATCH_CANCELLED}
                                    </div>
                                    :
                                    <>

                                        {
                                            // !_isUndefined(item.team_id) && item.team_id && item.team_id != '' ?
                                            <div className=" comp-timer-section">
                                                <div className='com-timer-view text-uppercase'><MomentDateComponent data={{ date: item.scheduled_date, format: "MMM D - hh:mm A " }} /></div>
                                                {
                                                    detail.is_score_predict && detail.is_score_predict == 1 ?
                                                        <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                                {detail.is_score_predict && detail.is_score_predict == 1 && parseInt(item.score) != 0 ? "+" : ""}
                                                                {
                                                                    parseInt(item.score) == parseInt(winGoal) ?
                                                                        parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT1 :
                                                                        parseInt(item.score) == parseInt(winGoalDiff) ?
                                                                            parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT2 :
                                                                            parseInt(item.score) == parseInt(winOnly) ?
                                                                                parseInt(item.score) + ' ' + AL.COMPLTED_TOOLTIP_TEXT3 :
                                                                                parseInt(item.score) == 0 ?
                                                                                    AL.COMPLTED_TOOLTIP_TEXT4 : ""
                                                                }
                                                            </Tooltip>
                                                        }>
                                                            <div className={`pts-score pts-score-view txt-underline ${item.is_correct == 1 ? ' succ ' : item.is_correct == 0 ? ' zero-color-change ' : ' dan '} ${!item.team_id?'zero-point':''}`}> {item.is_correct == 1 && "+"}{parseInt(item.score) || 0} {AL.PTS}</div>
                                                        </OverlayTrigger>
                                                        :
                                                        <div className={`pts-score pts-score-view ${item.is_correct == 1 ? 'succ' : 'dan'} ${!item.team_id?'zero-point':''}`}> {item.is_correct == 1 && "+"}{parseInt(item.score) || 0} {AL.PTS}</div>
                                                }
                                            </div>
                                            // :
                                            // <div className="timer-section text-center ttl-par">{AL.YOU_DID_NOT_ATTEMPT}</div>
                                        }
                                    </>
                            }
                        </>
                    }
                </div>

                <div className="pickem-prediction-outer-card" onClick={detail.is_score_predict == 0 && item.status == 2 ? () => this.showParticipantsModal(item) : '' } >
                    {
                        detail.is_score_predict && detail.is_score_predict == 1 ?
                            <div className="pickem-prediction-card predict-score-card">
                                <div className="option-section">

                                    <div className={`option-info `}>
                                        <div className="" style={{ position: "relative" }}>
                                            <div className="option-img">
                                                <div className="option-img-wrap">
                                                    <img src={Utilities.teamFlagURL(item.home_flag)} alt="" />
                                                </div>
                                            </div>
                                            <div className="option-name">{item.home_name}</div>
                                        </div>
                                    </div>
                                    <div className={`option-info `}>
                                        {
                                            ((isFor == 'comp' || isFor == 'live') && (_isUndefined(item.away_predict) || item.away_predict == '')) ?
                                                <div className={`vs-inp-sec ${isFor == 'comp' && ' corr-scr'}`}>
                                                    {!_isNull(ScoreData) && <>
                                                        {isFor == 'comp' && AL.CORRECT_SCORE + ' ' + ScoreData.home_score + '-' + ScoreData.away_score}
                                                    </>}
                                                    {/* {isFor == 'comp' &&  AL.CORRECT_SCORE + ' ' + ScoreData.home_score + '-' + ScoreData.away_score } */}
                                                </div>
                                                :
                                                <div className="input-score-sec">
                                                    {
                                                        isFor == 'comp' &&
                                                        <>
                                                            {(ScoreData && ScoreData.away_score && ScoreData.home_score && parseInt(ScoreData.away_score) == item.away_predict && parseInt(ScoreData.home_score) == item.home_predict) ?
                                                                <i className="icon-tick-circular"></i>
                                                                :
                                                                <i className="icon-cross-circular"></i>}
                                                        </>
                                                    }
                                                    <div className='hm-score'
                                                        onClick={() =>
                                                            detail.user_tournament_id != 0 ?
                                                                (isFor == 'upc' ? this.props.selectScoreMdl('home', item) : (isFor == 'live' ? this.showLiveMsg() : ''))
                                                                :
                                                                this.props.joinTournament(detail)
                                                        }>
                                                        {
                                                            activeSeasonId == item.season_id && scorePredictFor == 'home' ?
                                                                <p className='blk-txt'>{selectedhomeScore}</p> :
                                                                <>{item.home_predict ?
                                                                    <p className='blk-txt'>{item.home_predict}</p>
                                                                    :
                                                                    (selectedhomeScore && activeSeasonId == item.season_id ?
                                                                        <p className='blk-txt'>{selectedhomeScore == '-' ? 0 : selectedhomeScore}</p> :
                                                                        <p>-</p>
                                                                    )
                                                                }</>
                                                        }
                                                    </div>
                                                    <div className='ay-score'
                                                        onClick={() =>
                                                            detail.user_tournament_id != 0 ?
                                                                (isFor == 'upc' ? this.props.selectScoreMdl('away', item) : (isFor == 'live' ? this.showLiveMsg() : ''))
                                                                :
                                                                this.props.joinTournament(detail)
                                                        }>
                                                        {
                                                            activeSeasonId == item.season_id && scorePredictFor == 'away' ?
                                                                <p className='blk-txt'>{selectedawayScore}</p> :
                                                                <> {item.away_predict ?
                                                                    <p className='blk-txt'>{item.away_predict}</p> :
                                                                    (selectedawayScore && activeSeasonId == item.season_id ?
                                                                        <p className='blk-txt'>{selectedawayScore == '-' ? 0 : selectedawayScore}</p>
                                                                        : <p>-</p>
                                                                    )
                                                                }
                                                                </>
                                                        }
                                                    </div>
                                                </div>
                                        }
                                    </div>
                                    <div className={`option-info `}>
                                        <div className="" style={{ position: "relative" }}>
                                            <div className="option-img">
                                                <div className="option-img-wrap">
                                                    <img src={Utilities.teamFlagURL(item.away_flag)} alt="" />
                                                </div>
                                            </div>
                                            <div className="option-name">{item.away_name}</div>
                                        </div>
                                    </div>
                                    {
                                        (isFor == 'comp' && !isScorePredictCorrect && ScoreData && !_isUndefined(item.team_id) && item.team_id && item.team_id != '') ?
                                            <div className="correct-score-sec">
                                                {AL.CORRECT_SCORE} {ScoreData.home_score || 0} - {ScoreData.away_score || 0}
                                            </div>
                                            : ''
                                    }
                                </div>
                             
                            </div>
                            :
                            <div className={`pickem-prediction-card ${(!item.team_id) && Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.scheduled_date), 'YYYY-MM-DD HH:mm ')  ?'is-not-attend':''}`}>
                                <div className={`option-section new-fix-wdx ${isFor == 'comp' && (_isUndefined(item.team_id) || _isEmpty(item.team_id)) ? ' no-opt-sel' : ''}`}>
                                    <div className={`option-info  ${(
                                        isFor == 'comp' ?
                                            (
                                                item.team_id && item.team_id != '' && item.team_id != '0' ?
                                                    (
                                                        item.winning_team_id && item.winning_team_id == item.home_id && (item.team_id == item.home_id) ?
                                                            'correct-sel' : (item.team_id && item.team_id == item.home_id ? 'wrong-sel' :
                                                                (item.winning_team_id) && (item.winning_team_id == item.home_id) ? 'correct-sel no-bg' :
                                                                    (item.team_id) && (item.winning_team_id == item.home_id) ? 'wrong-sel no-bg' : ''
                                                            )
                                                    )
                                                    :
                                                    ((!item.team_id || item.team_id == '0') && ((item.winning_team_id == item.home_id)) ? "correct-sel no-bg" : '')
                                            )
                                            :
                                            (item.team_id && item.team_id == item.home_id ? 'sel-opt' : '')
                                    )
                                        }`}
                                        onClick={() => detail.user_tournament_id != 0 ? (isFor == 'upc' ? this.props.optionSelection(item, item.home_id, item.team_id && item.team_id == item.home_id ? true : false) : isFor == 'live' ? this.showLiveMsg() : '') : this.props.joinTournament(detail)}>
                                        <div className='sel-sec'></div>
                                        {<i className="icon-tick"></i>}
                                        {item.team_id && <i className="icon-close"></i>}
                                        <div className="" style={{ position: "relative" }}>
                                            <div className="option-img">
                                                {/* <CircularProgressBar
                                            data={item}
                                            progressPer={this.PickedPercentage(
                                                    parseFloat(item.home_count || 0),
                                                    item.total_season_count ? item.total_season_count : 100
                                                )
                                            }
                                        /> */}

                                                <div className="option-img-wrap">
                                                    <img src={Utilities.teamFlagURL(item.home_flag)} alt="" />
                                                </div>
                                            </div>
                                            <div className="option-name">{item.home_name}</div>
                                            <div className='picked-percentage-view'>
                                                {this.PickedPercentage(
                                                    parseFloat(item.home_count || 0),
                                                    item.total_season_count ? item.total_season_count : 100
                                                ) || 0}<span>%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className='divider-picekm' />
                                    {
                                    AppSelectedSport != "7" &&
                                        <div className={`option-info ${(
                                            isFor == 'comp' ?
                                                (
                                                    item.team_id && item.team_id != '' && item.team_id != '0' ?
                                                        (
                                                            item.winning_team_id && item.winning_team_id == 0 ?
                                                                'correct-sel no-bg' : (item.team_id && item.team_id == 0 ? 'wrong-sel no-bg' : '')
                                                        )
                                                        :
                                                        ((item.team_id == '0' && item.winning_team_id == 0) ? "correct-sel" : 'wrong-sel')


                                                )
                                                :
                                                (item.team_id && item.team_id == 0 ? 'sel-opt' : '')
                                        )
                                            }`}
                                            onClick={() => detail.user_tournament_id != 0 ? (isFor == 'upc' ? this.props.optionSelection(item, '0', item.team_id && item.team_id == 0 ? true : false) : isFor == 'live' ? this.showLiveMsg() : '') : this.props.joinTournament(detail)}>
                                            <div className='sel-sec'></div>
                                            { <i className="icon-tick"></i>}
                                            {item.team_id && <i className="icon-close"></i>}
                                            <div className="" style={{ position: "relative" }}>
                                                <div className="option-img">

                                                    {//this was already commented 
                                                        //     <CircularProgressBar
                                                        //     data={item}
                                                        //     progressPer={this.PickedPercentage(
                                                        //             parseFloat(item.draw_count || 0),
                                                        //             item.total_season_count ? item.total_season_count : 100
                                                        //         )
                                                        //     }
                                                        // />
                                                    }

                                                    <div className="option-img-wrap">
                                                        <img src={Images.DRAW_IMG} alt="" />
                                                    </div>
                                                </div>
                                                <div className="option-name">{'Draw'}</div>
                                                <div className='picked-percentage-view'>
                                                    {this.PickedPercentage(
                                                        parseFloat(item.draw_count || 0),
                                                        item.total_season_count ? item.total_season_count : 100
                                                    ) || 0}<span>%</span>
                                                </div>
                                            </div>
                                        </div>
                                    }
                                    <div className='divider-picekm' />
                                    <div className={`option-info ${(
                                        isFor == 'comp' ?
                                            (
                                                item.team_id && item.team_id != '' && item.team_id != '0' ?
                                                    (
                                                        (item.winning_team_id) && (item.winning_team_id == item.away_id) && (item.team_id == item.away_id) ?
                                                            'correct-sel' : (item.team_id && (item.team_id == item.away_id) ? 'wrong-sel' :
                                                                (item.winning_team_id) && (item.winning_team_id == item.away_id) ? 'correct-sel no-bg' :
                                                                    (item.team_id) && (item.winning_team_id == item.away_id) ? 'wrong-sel no-bg' : ''
                                                            )
                                                    )
                                                    :
                                                    ((!item.team_id || item.team_id == '0') && ((item.winning_team_id == item.away_id)) ? "correct-sel no-bg" : '')
                                            )
                                            :
                                            (item.team_id && item.team_id == item.away_id ? 'sel-opt' : '')
                                    )
                                        }`}
                                        onClick={() => detail.user_tournament_id != 0 ? (isFor == 'upc' ? this.props.optionSelection(item, item.away_id, item.team_id && item.team_id == item.away_id ? true : false) : isFor == 'live' ? this.showLiveMsg() : '') : this.props.joinTournament(detail)}>
                                        <div className='sel-sec'></div>

                                        { <i className="icon-tick"></i>}
                                         {item.team_id && <i className="icon-close"></i>}
                                        <div className="" style={{ position: "relative" }}>
                                            <div className="option-img">
                                                {/* <CircularProgressBar
                                            data={item}
                                            progressPer={this.PickedPercentage(
                                                    parseFloat(item.away_count || 0),
                                                    item.total_season_count ? item.total_season_count : 100
                                                )
                                            }
                                        /> */}

                                                <div className="option-img-wrap">
                                                    <img src={Utilities.teamFlagURL(item.away_flag)} alt="" />
                                                </div>
                                            </div>
                                            <div className="option-name">{item.away_name}</div>
                                            <div className='picked-percentage-view'>
                                                {this.PickedPercentage(
                                                    parseFloat(item.away_count || 0),
                                                    item.total_season_count ? item.total_season_count : 100
                                                ) || 0}<span>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                              {Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.scheduled_date), 'YYYY-MM-DD HH:mm ') && !item.team_id && <span className='not-attend'>{AL.YOU_DID_NOT_ATTEMPT}</span>}
                              
                              
                            </div>
                    }
                    {
                            showParticipants &&
                            <ParticipantsModal
                                {...this.props}
                                mShow={showParticipants}
                                participantsData ={participantsData}
                                mHide= {() => this.setState({showParticipants : false})}
                                // mHide= {() => this.hideParticipantsModal()}
                                item = {item}
                                details = {detail}
                            />
                        }
                </div>
            </div>
        );
    }
}

export default PTTourQueList;