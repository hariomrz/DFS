import React from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _isEmpty, _isUndefined } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "./CustomComponents";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from "react-bootstrap";

export default class MatchInfo extends React.Component {

    render() {
        const { item, status, timerCallback, onlyTimeShow, isFrom, UserData, tourHeader, isSecondInning, isHSI, over, boosterdata, isbooster, isTour, liveText, teamNameText, isHideFlag } = this.props;
        let lengthFixture = item.match_list ? item.match_list.length : 0
        let match_item = lengthFixture >= 1 ? item.match_list && item.match_list[0] : item
        let is_Tournament = item.is_tournament == 1 || item.tournament == 1 ? true : false
        let isDFSMulti = Constants.SELECTED_GAMET == Constants.GameType.DFS && Utilities.getMasterData().dfs_multi == 1 && item && item.season_game_count > 1 ? true : false
        let isPickFantasy = Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? true : false
        if (item.game_starts_in && !_isEmpty(item.game_starts_in) && !_isUndefined(item.game_starts_in)) {
            item['game_starts_in'] = parseInt(item.game_starts_in)
        }
        else {
            let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
            let game_starts_in = Date.parse(sDate)
            item['game_starts_in'] = game_starts_in;
        }
        let onlyTitle = item.is_tour_game == 1;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"match-info-section" + (onlyTimeShow && !isDFSMulti ? ' match-time-only' : '')}>
                        {
                            this.props.isH2H &&
                            <div className="h2h-label">H2H</div>
                        }
                        {
                            (!onlyTitle && (Constants.SELECTED_GAMET == Constants.GameType.Pred || lengthFixture <= 1) && !onlyTimeShow) &&
                            Constants.SELECTED_GAMET != Constants.GameType.MultiGame && !is_Tournament && !UserData && !over && !isDFSMulti && !isbooster &&
                            <div className={"section-left " + (isHideFlag ? "d-none" : "") + (teamNameText ? " d-flex flex-column align-items-center" : '')}>
                                <img src={Constants.SELECTED_GAMET == Constants.GameType.Pickem ? Utilities.getPickemTeamFlag(match_item.home_flag) : Utilities.teamFlagURL(match_item.home_flag)} alt="" className="home-team-flag" />
                                {teamNameText &&
                                    <OverlayTrigger onClick={(e) => e.stopPropagation()} rootClose trigger={['click']} placement="top" overlay={
                                        <Tooltip id="tooltip"
                                            className="match-tooltip match-tooltip-view"
                                        >
                                            <span className="">{match_item.home_name}</span>
                                        </Tooltip>
                                    }>
                                        <div className="tooltip-team team-name-text">
                                            <span className="team-name-text">{match_item.home_name}</span>
                                        </div>
                                    </OverlayTrigger>

                                }
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
                                            isbooster && boosterdata ?
                                                <div className="boostername">{boosterdata}</div>
                                                :
                                                isDFSMulti ?
                                                    <span className="team-home">
                                                        {item.collection_name}
                                                    </span>
                                                    :
                                                    ((Constants.SELECTED_GAMET == Constants.GameType.Pred) || Constants.SELECTED_GAMET != Constants.GameType.MultiGame)
                                                        ?
                                                        is_Tournament ?
                                                            <span className="team-home">
                                                                {item.name}
                                                            </span>
                                                            :
                                                            !onlyTimeShow &&
                                                            <>
                                                                {
                                                                    onlyTitle ?
                                                                        <span className="team-home">
                                                                            {item.collection_name}
                                                                        </span>
                                                                        :
                                                                        <>
                                                                            <span className="team-home">{match_item.home}</span>
                                                                            <span className="vs-text">{AppLabels.VERSES}</span>
                                                                            <span className="team-away">{match_item.away}
                                                                                {
                                                                                    over && <span style={{ textTransform: 'none' }}>{over ? ' Over ' + over : ''}</span>
                                                                                }
                                                                            </span>
                                                                        </>

                                                                }
                                                            </>
                                                        :

                                                        <span className="team-home">
                                                            {item.collection_name}
                                                        </span>
                                        }
                                        {(isHSI || (liveText && isTour)) ?
                                            <div className="match-timing h-match-timing">
                                                <span className="live-text">
                                                    {AppLabels.LIVE}
                                                </span>
                                            </div>
                                            : isSecondInning ?
                                                <div className="match-timing a2">
                                                    <span className="live-text">
                                                        {AppLabels.LIVE}
                                                    </span>
                                                    <div className="sec-inning-label">
                                                        {AppLabels.JOIN_SEC_INNING}
                                                         {/* • <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} /> */}
                                                    </div>
                                                </div>
                                                :

                                                !is_Tournament && status !== Constants.CONTEST_LIVE && <div className="match-timing aa">
                                                    {
                                                        !over && Utilities.showCountDown(item) && status !== Constants.CONTEST_COMPLETED ?
                                                      
                                                            <div className="countdown time-line">
                                                                {item.game_starts_in &&
                                                                    (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                                                                    <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={item.game_starts_in} />
                                                                }
                                                            </div>
                                                           :
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
                                                                                                    {
                                                                                                        isPickFantasy ?
                                                                                                            <>
                                                                                                                <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].scheduled_date, format: "D MMM" }} />
                                                                                                            </>
                                                                                                            :
                                                                                                            <>
                                                                                                                <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].season_scheduled_date, format: "D MMM" }} />
                                                                                                            </>
                                                                                                    }
                                                                                                    - {AppLabels.COMPLETED}
                                                                                                </span>
                                                                                                :
                                                                                                <span>
                                                                                                    {
                                                                                                        isPickFantasy ?
                                                                                                            <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                                            :
                                                                                                            <MomentDateComponent data={{ date: item.match_list[0] && item.match_list[0].season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                                    }
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
                                                                                                    <>
                                                                                                        {
                                                                                                            isPickFantasy
                                                                                                                ?
                                                                                                                <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM" }} />
                                                                                                                :
                                                                                                                <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                                                                                        }
                                                                                                    </>
                                                                                                    - {AppLabels.COMPLETED}
                                                                                                </span>
                                                                                                :
                                                                                                <span>
                                                                                                    {
                                                                                                        isPickFantasy ?
                                                                                                            <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                                            :
                                                                                                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                                    }
                                                                                                </span>}
                                                                                    </>
                                                                            }
                                                                        </React.Fragment>
                                                                        :
                                                                        !over &&
                                                                        <span>
                                                                            {
                                                                                isPickFantasy ?
                                                                                    <MomentDateComponent data={{ date: item.scheduled_date ? item.scheduled_date : item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                    :
                                                                                    <>
                                                                                        {
                                                                                            item.is_2nd_inning == 1 ?
                                                                                                <>
                                                                                                    <MomentDateComponent data={{ date: item.scheduled_date ? item.scheduled_date : item.season_scheduled_date, format: "D MMM - " }} />
                                                                                                    {AppLabels.SEC_INNING}
                                                                                                </>
                                                                                                :
                                                                                                <>
                                                                                                    {
                                                                                                        // ?
                                                                                                        // :
                                                                                                        <MomentDateComponent data={{ date: item.scheduled_date ? item.scheduled_date : item.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                                                                    }
                                                                                                </>
                                                                                        }
                                                                                    </> // Second Inings Date 
                                                                            }
                                                                        </span>
                                                                }
                                                            </React.Fragment>
                                                    }
                                                </div>
                                        }
                                        {
                                            is_Tournament &&
                                            <div className="match-timing a1">
                                                <span>
                                                    <MomentDateComponent data={{ date: item.start_date, format: "D MMM " }} /> - <MomentDateComponent data={{ date: item.end_date, format: "D MMM " }} />
                                                </span>
                                            </div>
                                        }
                                    </>
                            }
                        </div>
                        {
                            !onlyTitle && !is_Tournament && !UserData && !over && ((Constants.SELECTED_GAMET == Constants.GameType.Pred || lengthFixture <= 1) && !onlyTimeShow) && Constants.SELECTED_GAMET != Constants.GameType.MultiGame && !isDFSMulti && !isbooster &&
                            <div className={"section-right " + (isHideFlag ? "d-none" : "") + (teamNameText ? " d-flex flex-column align-items-center" : "")}>
                                <img src={Constants.SELECTED_GAMET == Constants.GameType.Pickem ? Utilities.getPickemTeamFlag(match_item.away_flag) : Utilities.teamFlagURL(match_item.away_flag)} alt="" className="away-team-flag" />
                                {teamNameText &&
                                    <OverlayTrigger onClick={(e) => e.stopPropagation()} rootClose trigger={['click']} placement="top" overlay={
                                        <Tooltip id="tooltip" className="match-tooltip match-tooltip-view-next">
                                            <span className="">{match_item.away_name}</span>
                                        </Tooltip>
                                    }>
                                        <div className="tooltip-team team-name-text">
                                            <span className="team-name-text">{match_item.away_name}</span>
                                        </div>
                                    </OverlayTrigger>
                                }

                            </div>}

                    </div>
                )}
            </MyContext.Consumer>
        )
    }

}