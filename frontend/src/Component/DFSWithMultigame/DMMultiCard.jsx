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

export default class DMMultiCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            slideIndex: 0
        }
    }

    FixtureListFunction = (item) => {
        // let matchStatus = item.status == 0 && (item.game_starts_in > new Date().getTime()) ? 0 : item.status == 2 || item.status == 4 ? 2 : 1; // 1 is for live and 0 is for upcoming 2 is for completed
          return (
            <div className="collection-list">
                <div className="display-table">
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                    </div>
                    <div className="display-table-cell text-center v-mid w-lobby-40">
                        <div className="team-block">
                            <span className="team-name text-uppercase">{item.home}</span>
                            <span className="verses">{AL.VS}</span>
                            <span className="team-name text-uppercase">{item.away}</span>
                        </div>
                        <div className="match-timing">
                            {
                                item.is_upcoming == 1 ?
                                    Utilities.showCountDown(item) ?
                                        <div className="countdown time-line">
                                            {item.game_starts_in && <CountdownTimer
                                                deadlineTimeStamp={item.game_starts_in}
                                                timerCallback={this.props.timerCallback}
                                            />}
                                        </div> :
                                        <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                                    :
                                    item.is_live == 1 ?
                                        <span className="text-danger text-bold">
                                            {AL.LIVE}
                                        </span>
                                        :
                                        (item.contest_status == 2 || item.contest_status == 3) ?
                                            <span className="text-success text-bold">
                                                <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM" }} />
                                - {AL.COMPLETED}
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
        let {int_version} = Utilities.getMasterData()
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
                    <div key={indexKey} className={`dfsmulti-fixture`} style={{position: 'relative'}}>
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
                            
                        } */}
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
                                            {ContestListItem.season_game_count} {int_version == "1" ? AL.GAMES : AL.FIXTURES}
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
                                {/* {process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 && ContestListItem.total_prize_pool > 0 &&
                                    <div className="prize-pool">{AL.WINNINGS}&nbsp;
                                        <span> {Utilities.getMasterData().currency_code + Utilities.numberWithCommas(ContestListItem.total_prize_pool)}</span>
                                    </div> 
                                } */}
                            </div>
                        </div>
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}