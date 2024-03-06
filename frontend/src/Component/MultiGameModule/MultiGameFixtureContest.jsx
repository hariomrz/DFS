import React,{lazy, Suspense} from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MATCH_TYPE } from '../../helper/Constants';
import { MomentDateComponent } from "../../Component/CustomComponent";
import * as Constants from "../../helper/Constants";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));

export default class FixtureContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            slideIndex: 0
        }
    }

    FixtureListFunction = (item) => {
        let matchStatus = item.status == 0 && (item.game_starts_in > new Date().getTime()) ? 0 : item.status == 2 || item.status == 4 ? 2 : 1; // 1 is for live and 0 is for upcoming 2 is for completed
          return (
            <div className="collection-list">
                <div className="display-table">
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                    </div>
                    <div className="display-table-cell text-center v-mid w-lobby-40">
                        <div className="team-block">
                            <span className="team-name text-uppercase">{item.home}</span>
                            <span className="verses">{AppLabels.VS}</span>
                            <span className="team-name text-uppercase">{item.away}</span>
                        </div>
                        <div className="match-timing">
                            {
                                matchStatus == 0 ?
                                    Utilities.showCountDown(item) ?
                                        <div className="countdown time-line">
                                            {item.game_starts_in && <CountdownTimer
                                                deadlineTimeStamp={item.game_starts_in}
                                                timerCallback={this.props.timerCallback}
                                            />}
                                        </div> :
                                        <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                                    :
                                    matchStatus == 1 ?
                                        <span className="text-danger text-bold">
                                            {AppLabels.LIVE}
                                        </span>
                                        :
                                        matchStatus == 2 ?
                                            <span className="text-success text-bold">
                                                <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                - {AppLabels.COMPLETED}
                                            </span>
                                            :
                                            <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                            }
                        </div>
                    </div>
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                    </div>
                </div>
            </div>
        );
    }

    gotoDetails = (ContestListItem, event) => {
        this.props.gotoDetails(ContestListItem, event);
    }

    render() {
        const { ContestListItem, indexKey, timerCallback, isFrom } = this.props;
        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '100px 0 0px',
            initialSlide: 0,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '60px 0 10px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '20px 0 10px',
                        afterChange: '',
                    }
                }
            ]
        };

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div key={indexKey} className={`${Constants.SELECTED_GAMET == Constants.GameType.MultiGame ? "fixture-list-content" : ''}`} style={{position: 'relative'}}>
                        {
                            (ContestListItem.playing_announce == "1" || ContestListItem.delay_minute > "0") &&
                            <div className="match-delay-info">
                                    {
                                        ContestListItem.playing_announce == "1" && 
                                        <span >{AppLabels.LINEUP_OUT}</span>
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
                                            <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer">{AppLabels.DELAYED} {ContestListItem.delay_text}</span>
                                            </OverlayTrigger>
                                        </span>
                                    }
                                </div>
                            
                        }

                        {ContestListItem.match_list.length == 1 &&
                            <div onClick={(event) => this.props.gotoDetails(ContestListItem, event)}>
                                {
                                    ContestListItem.match_list.map((item, idx) => {
                                        return (
                                            <div key={idx} className="collection-list" >
                                                <div className="display-table">
                                                    <div className="display-table-cell text-right v-mid w20">
                                                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                                                    </div>
                                                    <div className="display-table-cell text-center v-mid w-lobby-40">
                                                        <div className="team-block">
                                                            <span className="team-name text-uppercase">{item.home}</span>
                                                            <span className="verses">{AppLabels.VS}</span>
                                                            <span className="team-name text-uppercase">{item.away}</span>
                                                        </div>
                                                        <div className="match-timing">
                                                            {
                                                                Utilities.showCountDown(item) ?
                                                                    <div className="countdown time-line">
                                                                        {item.game_starts_in && <CountdownTimer timerCallback={timerCallback} deadlineTimeStamp={item.game_starts_in} currentDateTimeStamp={item.today} />}
                                                                    </div> :
                                                                    <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                                                            }
                                                        </div>

                                                    </div>
                                                    <div className="display-table-cell text-left v-mid w20">
                                                        <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                                                    </div>
                                                </div>
                                            </div>
                                        )
                                    })
                                }
                                <div className="collection-list-footer">
                                    <div className="match-type">
                                        {ContestListItem.league_name || ContestListItem.league_abbr} - {MATCH_TYPE[ContestListItem.match_list[0].format]}
                                    </div>
                                    {process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 && ContestListItem.total_prize_pool > 0 &&
                                        <div className="collection-list-prize-pool">{AppLabels.WINNINGS}&nbsp;<span> {Utilities.getMasterData().currency_code + Utilities.numberWithCommas(ContestListItem.total_prize_pool)}</span></div> 
                                        // : <div className="collection-list-prize-pool">{AppLabels.PRACTICE}</div>
                                    }
                                </div>
                            </div>
                        }
                        { ContestListItem.match_list&&ContestListItem.match_list.length > 1 &&
                            <React.Fragment>
                                <div className="collection-wrap" onClick={(event) => this.props.gotoDetails(ContestListItem, event)}>
                                    
                                    <div className="collection-header">
                                        <div className="collection-header-left">
                                            <h1>{ContestListItem.collection_name}</h1>
                                            <div className="collection-count">
                                            <span className="collection-league-name">{ContestListItem.league_name}</span>
                                            <span className="dot-divider"></span>
                                                {
                                                    isFrom == 'lobby' ?
                                                    <span className="text-capitalize">{ContestListItem.team_count} {AppLabels.TEAMS} | {ContestListItem.contest_count} {AppLabels.CONTEST_TEXT}</span>
                                                    :
                                                    <React.Fragment>{ContestListItem.match_list.length} {AppLabels.MATCHES_SM}</React.Fragment>
                                                }
                                            </div>
                                        </div>
                                        {process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 && ContestListItem.total_prize_pool > 0 &&
                                            <div className="collection-list-prize-pool">{AppLabels.WINNINGS}&nbsp;<span> {Utilities.getMasterData().currency_code + Utilities.numberWithCommas(ContestListItem.total_prize_pool)}</span></div> 
                                            // : <div className="collection-list-prize-pool">{AppLabels.PRACTICE}</div>
                                        }
                                    </div>
                                    <div className="collection-body">
                                        <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}
                                            slideIndex={this.state.slideIndex}>
                                            {ContestListItem.match_list &&ContestListItem.match_list.map((item, index) => {
                                                return (
                                                    <React.Fragment key={index}>
                                                        {<div className="collection-list-slider">
                                                                {this.FixtureListFunction(item)}
                                                            </div>
                                                        }
                                                    </React.Fragment>
                                                );
                                            })

                                            }
                        
                                        </ReactSlickSlider></Suspense>
                                    </div>
                                </div>
                            </React.Fragment>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}