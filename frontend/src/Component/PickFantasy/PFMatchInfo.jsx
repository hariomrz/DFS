import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "../CustomComponent";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";

export default class PFMatchInfo extends React.Component {  

    render() {
        const { item, status, timerCallback, onlyTimeShow, isFrom, UserData, tourHeader, isSecondInning, isHSI, over ,sports_id} = this.props;
        let lengthFixture = item.match_list ? item.match_list.length : 0
        let match_item = lengthFixture >= 1 ? item.match_list && item.match_list[0] : item
        let is_Tournament = item.is_tournament == 1 || item.tournament == 1 ? true : false
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"match-info-section" + (onlyTimeShow ? ' match-time-only' : '')}>

                        <div className="section-left">
                            <img src={Utilities.teamFlagURL(match_item.home_flag)} alt="" className="home-team-flag" />
                        </div>
                        <div className="section-middle">

                            {
                                <>
                                    <div>
                                        <span className="team-home">{match_item.home}</span>
                                        <span className="vs-text">{AppLabels.VERSES}</span>
                                        <span className="team-away">{match_item.away}
                                            {
                                                over && <span style={{ textTransform: 'none' }}>{over ? ' Over ' + over : ''}</span>
                                            }
                                        </span>
                                    </div>
                                    {
                                        !is_Tournament && status !== Constants.CONTEST_LIVE &&
                                        <div className="match-timing">
                                            {

                                                Utilities.showCountDown(item) && status !== Constants.CONTEST_COMPLETED ?
                                                    <div className="countdown time-line">
                                                        {item.game_starts_in &&
                                                            (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                                                            <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={item.game_starts_in} />
                                                        }
                                                    </div> :
                                                    <React.Fragment>
                                                        {
                                                            isFrom == 'MyContestSlider' ?
                                                                <React.Fragment>
                                                                    {
                                                                        Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().dfs_multi == 1 ?
                                                                            <>
                                                                                {(item.is_live == 1 || item.match_list[0] && item.match_list[0].status == 1) ?
                                                                                    <span className="live-text">
                                                                                        {AppLabels.LIVE}
                                                                                    </span>
                                                                                    :
                                                                                    item.match_list[0].status == 2 ?
                                                                                        <span className="completed-text">
                                                                                            <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].scheduled_date, format: "D MMM" }} />
                                                                                            - {AppLabels.COMPLETED}
                                                                                        </span>
                                                                                        :
                                                                                        <span>
                                                                                            <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                        </span>
                                                                                }
                                                                            </>
                                                                            :
                                                                            <>
                                                                                {(item.is_live == 1) ?
                                                                                    <span className="live-text">
                                                                                        {AppLabels.LIVE}
                                                                                    </span>
                                                                                    :
                                                                                    item.status == 2 ?
                                                                                        <span className="completed-text">
                                                                                            <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM" }} />
                                                                                            - {AppLabels.COMPLETED}
                                                                                        </span>
                                                                                        :
                                                                                        <span>
                                                                                            <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                        </span>}
                                                                            </>
                                                                    }
                                                                </React.Fragment>
                                                                :
                                                                !over &&
                                                                <span>
                                                                    <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                </span>
                                                        }
                                                    </React.Fragment>
                                            }
                                        </div>
                                    }
                                </>
                            }
                            {console.log('sports_name', Constants.AppSelectedSport)}
                            {
                                sports_id == 0 &&
                                <div className="sport-tag"><span>{match_item.sports_name}</span></div>
                            }
                        </div>
                        <div className="section-right">
                            <img src={Utilities.teamFlagURL(match_item.away_flag)} alt="" className="away-team-flag" />
                        </div>

                    </div>
                )}
            </MyContext.Consumer>
        )
    }

}