import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "../../Component/CustomComponent/CustomComponents";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

export default class LivefantasyMatchInfo extends React.Component {

    render() {
        const { item, status, timerCallback, onlyTimeShow, isFrom ,UserData,tourHeader,isSecondInning, isHSI} = this.props;
        let lengthFixture = item.match_list ? item.match_list.length : 0
        let match_item = lengthFixture >= 1 ? item.match_list[0] : item
        let is_Tournament = item.is_tournament == 1 || item.tournament == 1 ? true : false
        let dateObj = Utilities.getUtcToLocal(item.season_scheduled_date)
        let game_start_time = new Date(dateObj).getTime();
        let cTime = new Date().getTime();

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"match-info-section" + (onlyTimeShow ? ' match-time-only' : '')}>
                      
                        {
                            ((Constants.SELECTED_GAMET == Constants.GameType.Pred || lengthFixture <= 1) && !onlyTimeShow) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame && !is_Tournament && !UserData &&
                            <div  style={{display:'flex'}} className="section-left">
                                <img src={Constants.SELECTED_GAMET == Constants.GameType.Pickem ? Utilities.getPickemTeamFlag(match_item.home_flag) : Utilities.teamFlagURL(match_item.home_flag)} alt="" className="home-team-flag-live" />
                                <div className="home-team-name-live">{match_item.home}</div>

                            </div>
                        }
                        <div className="section-middle">

                            {
                                UserData ?
                                <span className="userinfo-sec">
                                    <span className="usr-nm">{item.user_name}</span>
                                    <span className="user-data">{AppLabels.RANK}# {item.game_rank}</span>
                                </span>
                                :
                                <>
                                {
                                    ((Constants.SELECTED_GAMET == Constants.GameType.Pred) || Constants.SELECTED_GAMET != Constants.GameType.MultiGame)
                                        ?
                                        is_Tournament ?
                                        <span className="team-home">
                                            {item.name}
                                        </span>
                                        :
                                        !onlyTimeShow &&
                                        <div>
                                            <span style={{fontWeight:'normal' ,color:'#999999',fontSize:10,textTransform:'none',fontFamily:"unset"}} className="team-home">{match_item.league_abbr ? match_item.league_abbr :match_item.league_name }</span>
                                            {/* <span className="vs-text">{AppLabels.VERSES}</span>
                                            <span className="team-away">{match_item.away}</span> */}
                                        </div>
                                        :

                                        <span className="team-home">
                                            {item.collection_name}
                                        </span>
                                }
                                {isHSI ? 
                                <div className="match-timing h-match-timing">
                                <span className="live-text">
                                    {AppLabels.LIVE}
                                </span>
                                </div>
                                : isSecondInning ? 
                                                <div className="match-timing">
                                                    <span className="live-text">
                                                        {AppLabels.LIVE}
                                                    </span>
                                                    <a href className="btnStyle btn-rounded">
                                                        {AppLabels.JOIN_SEC_INNING} {item.game_starts_in && (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={item.game_starts_in} />}
                                                    </a>
                                                </div>
                                                :
                                
                                                item.match_status !== Constants.CONTEST_LIVE && <div className="match-timing">
                                                    {
                                                        Utilities.showCountDown(item) && item.match_status !== Constants.CONTEST_COMPLETED ?
                                                            <div className="countdown time-line">
                                                                {item.game_starts_in && (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={item.game_starts_in} />}
                                                            </div> :
                                                            <React.Fragment>
                                                                {
                                                                    isFrom == 'MyContestSlider' ?
                                                                        <React.Fragment>
                                                                            {

                                                                                (item.match_status == 1) ?
                                                                                    <span className="live-text">
                                                                                        {AppLabels.LIVE}
                                                                                    </span>
                                                                                    :
                                                                                    item.match_status == 2 ?
                                                                                        <span className="completed-text">
                                                                                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                                                                            - {AppLabels.COMPLETED}
                                                                                        </span>
                                                                                        :
                                                                                        (item.match_status == 0) && (item.game_starts_in < new Date().getTime()) ?
                                                                                        <span className="live-text">
                                                                                            {AppLabels.LIVE}
                                                                                        </span>
                                                                                        :
                                                                                        (cTime > game_start_time) ?
                                                                                                <span>
                                                                                                    <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                                                                                    - {AppLabels.IN_PROGRESS}
                                                                                                </span>
                                                                                       
                                                                                        :
                                                                                        <span>
                                                                                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                        </span>
                                                                            }
                                                                        </React.Fragment>
                                                                        :
                                                                        <span>
                                                                            {
                                                                                cTime > game_start_time ?
                                                                                <span>
                                                                                    <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                                                                    - {AppLabels.IN_PROGRESS}


                                                                                </span>
                                                                                :
                                                                                <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} />


                                                                            }

                                                                        </span>
                                                                      
                                                                      
                                                                }
                                                            </React.Fragment>
                                                    }
                                                </div>
                                }
                                {
                                    is_Tournament &&
                                    <div className="match-timing">
                                        <span>
                                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "D MMM " }} />
                                        </span>
                                    </div>
                                }
                            </>
                            }
                        </div>
                        {
                            !is_Tournament && !UserData && ((Constants.SELECTED_GAMET == Constants.GameType.Pred || lengthFixture <= 1) && !onlyTimeShow) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame &&
                            <div style={{display:'flex'}} className="section-right">
                                <div className="away-team-name-live">{match_item.away}</div>
                                <img src={Constants.SELECTED_GAMET == Constants.GameType.Pickem ? Utilities.getPickemTeamFlag(match_item.away_flag) : Utilities.teamFlagURL(match_item.away_flag)} alt="" className="away-team-flag-live" />

                            </div>   }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }

}