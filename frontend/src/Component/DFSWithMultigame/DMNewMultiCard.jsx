import React,{lazy, Suspense} from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MATCH_TYPE } from '../../helper/Constants';
import { MomentDateComponent } from "../../Component/CustomComponent";
import * as Constants from "../../helper/Constants";
import Images from '../../components/images';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

export default class DMNewMultiCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            slideIndex: 0
        }
    }

    gotoDetails = (ContestListItem, event) => {
        this.props.gotoDetails(ContestListItem, event);
    }

    render() {
        const { ContestListItem, indexKey, timerCallback, isFrom } = this.props;

        let sDate = new Date(Utilities.getUtcToLocal(ContestListItem.season_scheduled_date))
        let game_starts_in = Date.parse(sDate)
        ContestListItem['game_starts_in'] = game_starts_in;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div key={indexKey} className={`dfsmulti-fixture-new`} onClick={(event) => this.props.gotoDetails(ContestListItem, event)}>
                        {/* {
                            ContestListItem.playing_announce == "1" && 
                            <span className='lineup-out-tag'>{AL.LINEUP_OUT}</span>
                        } */}
                        <div className="time-sec">
                            {
                                ContestListItem.is_live == 1 ?
                                <div className="tag live">
                                    <span></span>{AL.LIVE}
                                </div>
                                :
                                <>
                                {
                                    ContestListItem.status == 1 ?
                                    <div className="tag comp">
                                        {AL.COMPLETED}
                                    </div>
                                    :
                                    Utilities.showCountDown(ContestListItem) ?
                                    <div className="countdown time-line">
                                        {ContestListItem.game_starts_in && 
                                            <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={ContestListItem.game_starts_in} currentDateTimeStamp={ContestListItem.today} />
                                        }
                                    </div> :
                                    <span> <MomentDateComponent data={{ date: ContestListItem.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                                }
                                </>
                            }
                        </div>
                        <div className="multi-title">{ContestListItem.collection_name}</div>
                        <div className="tag-sec">
                            <div className="tag">{AL.MULTIGAME}</div>
                            <div className="tag"> {ContestListItem.season_game_count} {AL.FIXTURES}</div>
                        </div>
                        {/* <div className="rank-sec">
                            54 {AL.RANK} 
                        </div> */}
                        <div className="btm-sec">
                                    <span className="edit-txt">{AL.JOINED_WITH} {ContestListItem.team_count} {AL.TEAMS_MYCONTEST} </span>
                            {/* {
                                item.is_live == 1 ?
                                    <span className="rank-txt">
                                        {  
                                            item.is_winner && item.is_winner != '0' && <i className="icon-trophy"></i>
                                        }
                                        {item.game_rank} {AL.RANK}
                                    </span>
                                    :
                                    <span className="edit-txt">{AL.JOINED_WITH} {item.team_count} {AL.TEAMS_MYCONTEST} </span>
                            }                   */}
                        </div>
                        {
                            ContestListItem.delay_minute > "0" &&
                            <span>
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className={"tooltip-featured" + (ContestListItem.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                                        <strong> {ContestListItem.delay_message} </strong>
                                    </Tooltip>
                                }>
                                <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer delayed-tag">{AL.DELAYED}</span>
                                </OverlayTrigger>
                            </span>
                        }
                        {/* {
                            (ContestListItem.playing_announce == "1" || ContestListItem.delay_minute > "0") &&
                            <div className="match-delay-info">
                                    {
                                        ContestListItem.playing_announce == "1" && 
                                        <span >{AL.LINEUP_OUT}</span>
                                    }
                                    {
                                        ContestListItem.playing_announce =="1" && ContestListItem.delay_minute 
                                        > "0" &&
                                            <span className="seperator-class"> | </span>
                                    }
                                    {
                                        ContestListItem.delay_minute > "0" &&
                                        <span>
                                            <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                <Tooltip id="tooltip" className={"tooltip-featured" + (ContestListItem.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                                                    <strong> {ContestListItem.delay_message} </strong>
                                                </Tooltip>
                                            }>
                                            <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer">{AL.DELAYED} {ContestListItem.delay_text}</span>
                                            </OverlayTrigger>
                                        </span>
                                    }
                                </div>
                            
                        }
                        <div onClick={(event) => this.props.gotoDetails(ContestListItem, event)}>
                            <div className="dfsmulti-body">
                                <div className="img-sec">
                                    <img src={Images.MULTIGAME_IMG_IC} alt="" />
                                </div>
                                <div className="desc-sec">
                                    {
                                        isFrom != 'MyContestSlider' &&
                                        <div className="multi-tag"><span>{AL.MULTIGAME}</span></div>
                                    }
                                    <div className="multi-fix-name">{ContestListItem.collection_name}</div>
                                    {
                                        isFrom == 'MyContestSlider' &&
                                        <div className="multi-tag-txt"><span>{AL.MULTIGAME}</span></div>
                                    }
                                    <div className="match-timing">
                                        {
                                            (ContestListItem.is_live == 1) ?
                                            <span className="live-text">
                                                <span></span> {AL.LIVE}
                                            </span>
                                            :
                                            ContestListItem.contest_status == 2 || ContestListItem.contest_status == 3 ?
                                                <span className="completed-text ">
                                                    <MomentDateComponent data={{ date: ContestListItem.season_scheduled_date, format: "D MMM" }} />
                                                    - {AL.COMPLETED}
                                                </span>
                                            :
                                            Utilities.showCountDown(ContestListItem) ?
                                                <div className="countdown time-line">
                                                    {ContestListItem.game_starts_in && 
                                                        <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={ContestListItem.game_starts_in} currentDateTimeStamp={ContestListItem.today} />
                                                    }
                                                </div> :
                                                <span> <MomentDateComponent data={{ date: ContestListItem.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                                        }
                                            <span className="sep">|</span>
                                            {ContestListItem.season_game_count} {AL.FIXTURES}
                                    </div>
                                </div>
                            </div>
                            <div className="dfsmulti-footer">
                                <div className="match-type">
                                    {ContestListItem.league_name || ContestListItem.league_abbr}
                                    {
                                        this.state.sports_id === '7' && ContestListItem.match_list[0].format &&
                                        <>
                                            - {MATCH_TYPE[ContestListItem.match_list[0].format]}
                                        </> 
                                    }
                                </div>
                                {
                                    isFrom == 'MyContestSlider' ?
                                    <React.Fragment>
                                        {
                                            ContestListItem.status == 2 ?
                                                <React.Fragment>
                                                    <div className="user-contest-detail">
                                                        {
                                                            ContestListItem.entry_fee > 0 ?
                                                                <React.Fragment> {Utilities.getMasterData().currency_code + Utilities.kFormatter(ContestListItem.entry_fee)}</React.Fragment>
                                                                :
                                                                <React.Fragment>{AL.PRACTICE}</React.Fragment>
                                                        }
                                                        &nbsp;{AL.ENTRY} |
                                                        <span className={ContestListItem.won_amt > 0 ? "won" : ''}> {Utilities.getMasterData().currency_code + Utilities.kFormatter(ContestListItem.won_amt || 0)}</span>&nbsp;{AL.WON}
                                                    </div>
                                                </React.Fragment>
                                                :
                                                <div className="user-contest-detail ">{ContestListItem.team_count} {AL.TEAMSS} | {ContestListItem.contest_count} {AL.CONTESTS_POPUP}</div>
                                        }
                                    </React.Fragment>
                                    :
                                    <>
                                    {
                                        process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 && ContestListItem.total_prize_pool > 0 &&
                                        <div className="prize-pool">{AL.WINNINGS}&nbsp;
                                            <span> {Utilities.getMasterData().currency_code + Utilities.numberWithCommas(ContestListItem.total_prize_pool)}</span>
                                        </div> 
                                    }
                                    </>
                                }
                            </div>
                        </div> */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}