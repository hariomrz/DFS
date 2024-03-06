import React, { Suspense, lazy, useEffect, useState } from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import MatchInfo from "./MatchInfo";
import { SportsSchedule, Utilities, _Map, _isEmpty, checkSame } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import { MATCH_TYPE, SELECTED_GAMET } from "../../helper/Constants";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from "../../components/images";
import { SportsIDs } from "../../JsonFiles";
import WSManager from "../../WSHelper/WSManager";
const ReactSlickSlider = lazy(() => import('./ReactSlickSlider'));


export default class MatchCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: Constants.AppSelectedSport,
            timerCallback: this.props.timerCallback,
            featureStrip: [{ name: AppLabels.LEADERBOARD + ' ' + AppLabels.AVAILABLE, icon: 'icon-leaderboard' }, { name: AppLabels.LB_PRIZE_AVA, icon: 'icon-trophy' }],
            currentFIDX: 0
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (nextProps != this.props) {
            this.setState({
                timerCallback: nextProps.timerCallback
            })
        }

    }
    fixtureCardClick = (event,total_team_count) => {
        const { item, gotoDetails } = this.props;
        gotoDetails(item, event,total_team_count)
        if (item.status == 0) {
            Utilities.gtmEventFire('upcoming_matches_screen', { "match_name": item.collection_name })
        }
    }
    gameCenter = (event) => {
        const { item, gotoGameCenter } = this.props;
        gotoGameCenter(item, event)
    }


    showTourList = (event) => {
        this.props.showTourList(event)
    }


    renderFeatureStrip = (onLBClick) => {
        let fList = this.state.featureStrip
        var settings = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: this.state.currentFIDX,
            dots: false,
            autoplay: fList.length > 1,
            autoplaySpeed: 6000,
            centerMode: fList.length > 1,
            centerPadding: "25px",
            draggable: false,
            beforeChange: (cidx, nextIDX) => this.setState({ currentFIDX: nextIDX, preFIDX: nextIDX - 1, nextINDX: nextIDX + 1 }),
            speed: 600,
            className: 'ldb-strip-slider',
            swipe: false,
            arrows: false
        };
        return (
            <ReactSlickSlider settings={settings}>
                {
                    _Map(fList, (item, indx) => {
                        return (
                            <div key={indx} onClick={onLBClick} className={"ldb-strip d-flex " + (fList.length > 1 ? 'justify-content-start' : '')}>
                                {/* <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : this.state.preFIDX === indx ? ' next-s' : this.state.nextINDX === indx ? ' slide-in-v' : ' next-s' )}><i className="icon-leaderboard" /><span>{item}</span></div> */}
                                <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : ' next-s')}><i className={item.icon} /><span>{item.name}</span></div>
                            </div>
                        )
                    })
                }
            </ReactSlickSlider>
        )
    }



    render() {
        const { item, gotoDetails, gotoLeaderBoard, isFromFreeToPlayLandingPage, isFrom, onLBClick, isSecondInning, gotoGameCenter, showTeamCount, isTour, liveText, teamNameText, detail, isFromLB } = this.props;


     



        let sponserImage = item.sponsor_logo && item.sponsor_logo != null ? item.sponsor_logo : 0
        // item['team_count']='3'
        // item['team']='my team'

        let isGameCenter = (Utilities.getMasterData().allow_gc == 1 && item.is_gc == 1 && isFrom != 'MyContestSlider') ? true : false
        let isTourinclude = (item.match_list && item.match_list[0] && item.match_list[0].is_tournament && item.match_list[0].is_tournament == 1) ? true : false
        let { int_version } = Utilities.getMasterData()

        let lbStatus = isFromLB == 'fContest' && detail && detail.status
        let lbUserGame = isFromLB == 'fContest' && detail && detail.user_game
        let lbJoined = (isFromLB == 'fContest' && lbUserGame.length != 0) ? Object.keys(lbUserGame) : ''
       



        let filteredVal = isFromLB == 'fContest' && lbJoined && lbJoined.filter((obj) => obj == item.cm_id)
        
        let total_team_count = isFromLB == 'fContest' && lbUserGame && lbUserGame[filteredVal] && lbUserGame[filteredVal].total_teams
        
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div
                    className={`fixture-card-wrapper  xhighlighted-card ${(this.props.fixtureCardLg ? ' fixture-card-wrapper-lg' : '') + ((item.cm_id > 0 && item.contest_id > 0) ?  
                    " " : isTour ? ' fixture-card-wrapper-disable' : ' '  ) }`}

                    //  className={"fixture-card-wrapper  xhighlighted-card " + (this.props.fixtureCardLg ? ' fixture-card-wrapper-lg' : '')}
                     onClick={(event) => this.fixtureCardClick(event,total_team_count)}>
                        {
                            item.is_tour_game == '1' ?
                                <div className="tour-game-card-wrap">
                                    <MotorSportsCard {...{
                                        ...this.props,
                                        item,
                                        showTeamCount
                                    }} />


                                    {
                                        !isTour && ((isTourinclude) || (isGameCenter)) &&
                                        <div className={`incudes-tour `} onClick={(event) => this.gameCenter(event)}>
                                            {/* onClick={(event) => isGameCenter && this.gameCenter(event)}>  */}

                                            {/* <div className="incudes-tour-view "> */}
                                            <div className={`in-sec ${isGameCenter && isTourinclude ? ' start-ani' : ''}`}>
                                                <div className="inn-wrap">
                                                    {
                                                        (isTourinclude) &&
                                                        <div className="ani1 new-tourInclude-view"
                                                            onClick={(event) =>
                                                                item && item.tournament[0] && item.tournament[0].tournament_count == "1" ?
                                                                    this.props.goToTourDetailLB(event, item.tournament)
                                                                    :
                                                                    this.showTourList(event)}
                                                        >
                                                            <img src={Images.LEAD_TROPHY} className="lead-trophy" alt="" />
                                                            
                                                            {item.tournament && item.tournament[0] && item.tournament[0].tournament_count == "1" ?
                                                                <span className='leadT-name'>{item.tournament[0].tournament_name}</span> :
                                                                <span className='leadT-name'>{item.tournament[0].tournament_name} + <span className='countL-others'> {+ (parseInt(item.tournament[0].tournament_count) - 1) + ' ' + AppLabels.OTHER_TEXT}</span></span>

                                                            }
                                                            {item.tournament.length > 0 && <div className='arrow-container float-right mt-1'>
                                                                <i className="icon-arrow-right iocn-first"></i>
                                                                <i className="icon-arrow-right iocn-second"></i>
                                                                <i className="icon-arrow-right iocn-third"></i>
                                                            </div>}

                                                        </div>
                                                    }
                                                    {
                                                        (isGameCenter) &&
                                                        <div className="">
                                                            <div className={`include-tour-cont ${isGameCenter && isTourinclude ? "mb-1" : "align-item-center"}`}>
                                                                <div className=" go-to-game" >
                                                                    <div className='go-to-game-center'>
                                                                        <img src={Images.GAME_CENTER_CIRCLE} alt="" /> {AppLabels.GO_TO_GAME_CENTER}</div>
                                                                </div>
                                                                {/* <div>
                                                    <img src={Images.ARROW_THREE} className="arrow-three-view"/>
                                                </div> */}
                                                                {/* <div className='arrow-container'>
                                                <i className="icon-arrow-right"></i>
                                                <i className="icon-arrow-right"></i>
                                                <i className="icon-arrow-right"></i>
                                            </div> */}
                                                                {(isGameCenter) && <div className='arrow-container'>
                                                                    <i className="icon-arrow-right iocn-first"></i>
                                                                    <i className="icon-arrow-right iocn-second"></i>
                                                                    <i className="icon-arrow-right iocn-third"></i>

                                                                </div>}



                                                            </div>
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    }


                                </div>
                                :
                                <>
                                    <div className={"fixture-card-body display-table " + (isSecondInning ? ' sec-innig' : '')}>
                                        {
                                            item.custom_message != '' && item.custom_message != null &&
                                            <div className="announcement-custom-msg-wrapper">
                                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                        <strong>{item.custom_message} </strong>
                                                    </Tooltip>
                                                }>
                                                    <i className="icon-megaphone" onClick={(e) => e.stopPropagation()}></i>
                                                </OverlayTrigger>
                                            </div>

                                        }
                                        {
                                            Utilities.getMasterData().booster == 1 && item.booster && item.booster != '' && !isSecondInning &&
                                            <div className={"booster" + (item.custom_message != '' && item.custom_message != null ? ' is-custom-message' : '')}>
                                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                    <Tooltip id="tooltip" className="">
                                                        <small style={{ fontFamily: 'PrimaryF-Regular', fontWeight: 'lighter' }}>{AppLabels.AVAILABLE_BOOSTER} </small>
                                                        <br></br>
                                                        <strong>{item.booster} </strong>

                                                    </Tooltip>
                                                }>
                                                    <img src={Images.BOOSTER_ICON} alt='' onClick={(e) => e.stopPropagation()}></img>
                                                </OverlayTrigger>
                                            </div>

                                        }

                                        <MatchInfo isSecondInning={isSecondInning} item={item} timerCallback={this.state.timerCallback} isFrom={isFrom} isTour={isTour} liveText={liveText} teamNameText={teamNameText} />

                                    </div>

                                    {
                                        isTour ?
                                            <>
                                                {
                                                    item.status != 2 && (total_team_count > 0) ?
                                                        <div className={"fixture-card-footer justify-center"}>
                                                            <div className="fix-team-count">
                                                                {total_team_count == 1 ? AppLabels.TEAM_TEXT + ' ' + total_team_count :
                                                                    AppLabels.JOINED_WITH + ' ' + total_team_count + ' ' + AppLabels.TEAMS_MYCONTEST
                                                                }
                                                            </div>
                                                        </div>
                                                        :
                                                        <>
                                                            {liveText &&
                                                                <div className={"fixture-card-footer justify-center"}>
                                                                    <div className="fix-team-count not-part">{int_version == "1" ? AppLabels.NOT_PART_OF_THIS_MATCH_INT : AppLabels.NOT_PART_OF_THIS_MATCH}</div>
                                                                </div>
                                                            }
                                                        </>

                                                }

                                                {
                                                    item.status == 2 &&
                                                    <div className={"fixture-card-footer justify-center"}>
                                                        {
                                                            showTeamCount && parseInt(item.team_count) > 0 ?
                                                                <div className="fix-team-count">
                                                                    {item.team ? item.team : AppLabels.TEAMS + ' ' + item.team_count}
                                                                </div>
                                                                :
                                                                <div className="fix-team-count not-part">{int_version == "1" ? AppLabels.NOT_PART_OF_THIS_MATCH_INT : AppLabels.NOT_PART_OF_THIS_MATCH}</div>
                                                        }
                                                    </div>
                                                }
                                                {/* {
                                showTeamCount && parseInt(item.team_count) > 0 &&
                                <div className={"fixture-card-footer justify-center"}>
                                        <div className="fix-team-count">
                                            {
                                                item.status != 2 ?
                                                AppLabels.JOINED_WITH + ' ' + item.team_count + ' ' + AppLabels.TEAMS_MYCONTEST
                                                :
                                                <>{item.team ? item.team : AppLabels.TEAMS + ' ' + item.team_count }</>
                                            }
                                        </div>
                                </div>
                            } */}
                                            </>
                                            :
                                            <div className={"fixture-card-footer" + (item.league_name ? '' : ' justify-center') + (SELECTED_GAMET == Constants.GameType.Free2Play ? ' height-league-card' : '')}>

                                                {item.league_name &&
                                                    <div className={"match-type" + (process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 ? '' : ' match-type-only')}>
                                                        {item.league_name || item.league_abbr}
                                                        {this.state.sports_id === '7' &&
                                                            <span> - {MATCH_TYPE[item.match_list && item.match_list[0] && item.match_list[0].format ? item.match_list[0].format : item.format]}</span>
                                                        }
                                                    </div>
                                                }
                                                {
                                                    process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 &&
                                                    <div className="winning-section">
                                                        {SELECTED_GAMET == Constants.GameType.Free2Play ?
                                                            <React.Fragment>
                                                                {window.ReactNativeWebView ?
                                                                    <a
                                                                        href
                                                                        onClick={(event) => Utilities.callNativeRedirection(Utilities.getValidSponserURL(item.sponsor_link, event))}
                                                                        className="attached-url">
                                                                        <img alt='' className="lobby_sponser-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                    </a>

                                                                    :
                                                                    <a
                                                                        href={Utilities.getValidSponserURL(item.sponsor_link)}
                                                                        onClick={(event) => event.stopPropagation()}
                                                                        target='__blank'
                                                                        className="attached-url">
                                                                        <img alt='' className="lobby_sponser-image" style={{ resizeMode: 'contain' }} src={sponserImage == 0 ? Images.BRAND_LOGO_FULL_PNG : Utilities.getSponserURL(sponserImage)} />
                                                                    </a>
                                                                }
                                                            </React.Fragment>
                                                            :
                                                            isFrom == 'MyContestSlider' ?
                                                                <React.Fragment>
                                                                    {
                                                                        item.status == 2 ?
                                                                            <React.Fragment>
                                                                                <div className="user-contest-detail">
                                                                                    {
                                                                                        item.entry_fee > 0 ?
                                                                                            <React.Fragment> {Utilities.getMasterData().currency_code + Utilities.kFormatter(item.entry_fee)}</React.Fragment>
                                                                                            :
                                                                                            <React.Fragment>{AppLabels.PRACTICE}</React.Fragment>
                                                                                    }
                                                                                    &nbsp;{AppLabels.ENTRY} |
                                                                                    <span className={item.won_amt > 0 ? "won" : ''}> {Utilities.getMasterData().currency_code + Utilities.kFormatter(item.won_amt || 0)}</span>&nbsp;{AppLabels.WON}
                                                                                </div>
                                                                            </React.Fragment>
                                                                            :
                                                                            <>
                                                                                {
                                                                                    isSecondInning ?
                                                                                        <div className="collection-list-prize-pool">{AppLabels.WINNINGS}&nbsp;<span> {Utilities.getMasterData().currency_code + Utilities.getPrizeInWordFormat(item.total_prize_pool)}</span></div>
                                                                                        :
                                                                                        <div className="user-contest-detail ">{item.team_count} {AppLabels.TEAMSS} | {item.contest_count} {AppLabels.CONTESTS_POPUP}</div>
                                                                                }
                                                                            </>
                                                                    }
                                                                </React.Fragment>
                                                                :
                                                                item.total_prize_pool > 0 &&
                                                                <div className="collection-list-prize-pool">{AppLabels.WINNINGS}&nbsp;<span> {Utilities.getMasterData().currency_code + Utilities.getPrizeInWordFormat(item.total_prize_pool)}</span></div>
                                                            // : <div className="collection-list-prize-pool">{AppLabels.PRACTICE}</div>

                                                        }

                                                    </div>
                                                }
                                                {
                                                    SELECTED_GAMET == Constants.GameType.Free2Play && !isFromFreeToPlayLandingPage &&
                                                    <div className="free-to-play-info no-margin" onClick={(event) => gotoLeaderBoard(item, event)}>
                                                        <img alt='' src={Images.HALL_OF_FAME_SMALL_ICON} className="hall-of-fame-img" />
                                                        <div className="text_hall_of_fame">
                                                            {AppLabels.GAIN_POINTS}
                                                        </div>
                                                        <img alt='' src={Images.IC_INFO} className="icon-info"></img>

                                                    </div>


                                                }
                                            </div>
                                    }
                                    {/* { !isTour && 
                        Utilities.getMasterData().allow_gc == 1 && item.is_gc ==1 && isFrom != 'MyContestSlider' && 
                            <div onClick={(event) => this.gameCenter(event)} className='game-center-strip new'>
                                <div className='oval-goto-text-container'>
                                    <div className='oval-one'>
                                        <div className='oval-two'>
                                            <div className='oval-three'>
                                                <div className='oval-four'>
                                                    <div className='oval-five'></div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    <div className='go-to-game-center'>{AppLabels.GO_TO_GAME_CENTER}</div>

                                </div>
                                <div className='arrow-icon-container'>
                                    <i className="icon-arrow-right iocn-first"></i>
                                    <i className="icon-arrow-right iocn-second"></i>
                                    <i className="icon-arrow-right iocn-third"></i>

                                </div>

                            </div>
                        } */}
                                    {/* {
                            !isTour && item.ldb == '1' && !isSecondInning && <Suspense fallback={<div />} >
                                {this.renderFeatureStrip(onLBClick)}
                            </Suspense>
                        } */}
                                    {
                                        !isTour && ((isTourinclude) || (isGameCenter)) &&
                                        <div className={`incudes-tour `} onClick={(event) => this.gameCenter(event)}>
                                            {/* onClick={(event) => isGameCenter && this.gameCenter(event)}>  */}

                                            {/* <div className="incudes-tour-view "> */}
                                            <div className={`in-sec ${isGameCenter && isTourinclude ? ' start-ani' : ''}`}>
                                                <div className="inn-wrap">
                                                    {
                                                        (isTourinclude) &&
                                                        <div className="ani1 new-tourInclude-view"
                                                            onClick={(event) =>
                                                                item && item.tournament[0] && item.tournament[0].tournament_count == "1" ?
                                                                    this.props.goToTourDetailLB(event, item.tournament)
                                                                    :
                                                                    this.showTourList(event)}
                                                        >
                                                            <img src={Images.LEAD_TROPHY} className="lead-trophy" alt="" />
                                                            {/* <img src={Images.TROPHY_IMG} alt="" /> */}
                                                            {/* {int_version == "1"
                                                                ?
                                                                AppLabels.GAME_ELIGIBLE_FOR_TOUR
                                                                :
                                                                AppLabels.FIXTURE_ELIGIBLE_FOR_TOUR
                                                            } */}
                                                            {item.tournament && item.tournament[0] && item.tournament[0].tournament_count == "1" ?
                                                                <span className='leadT-name'>{item.tournament[0].tournament_name}</span> :
                                                                <span className='leadT-name'>{item.tournament[0].tournament_name} + <span className='countL-others'> {+ (parseInt(item.tournament[0].tournament_count) - 1) + ' ' + AppLabels.OTHER_TEXT}</span></span>

                                                            }
                                                            {item.tournament.length > 0 && <div className='arrow-container float-right mt-1'>
                                                                <i className="icon-arrow-right iocn-first"></i>
                                                                <i className="icon-arrow-right iocn-second"></i>
                                                                <i className="icon-arrow-right iocn-third"></i>
                                                            </div>}

                                                        </div>
                                                    }
                                                    {
                                                        (isGameCenter) &&
                                                        <div className="">
                                                            <div className={`include-tour-cont ${isGameCenter && isTourinclude ? "mb-1" : "align-item-center"}`}>
                                                                <div className=" go-to-game" >
                                                                    <div className='go-to-game-center'>
                                                                        <img src={Images.GAME_CENTER_CIRCLE} alt="" /> {AppLabels.GO_TO_GAME_CENTER}</div>
                                                                </div>
                                                                {/* <div>
                                                    <img src={Images.ARROW_THREE} className="arrow-three-view"/>
                                                </div> */}
                                                                {/* <div className='arrow-container'>
                                                <i className="icon-arrow-right"></i>
                                                <i className="icon-arrow-right"></i>
                                                <i className="icon-arrow-right"></i>
                                            </div> */}
                                                                {(isGameCenter) && <div className='arrow-container'>
                                                                    <i className="icon-arrow-right iocn-first"></i>
                                                                    <i className="icon-arrow-right iocn-second"></i>
                                                                    <i className="icon-arrow-right iocn-third"></i>

                                                                </div>}



                                                            </div>
                                                        </div>
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    }
                                </>
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}

const MotorSportsCard = (props) => {
    const { item, timerCallback, showTeamCount } = props;
    const sports_id = Constants.AppSelectedSport
    let { int_version } = Utilities.getMasterData()
    const [ teamCount, setTeamCount] = useState(0)
    useEffect(() => {
        if(!_isEmpty(item.user_game)) {
            setTeamCount(item.user_game[item.cm_id].total_teams)
        }
    }, [item])

    return (
        <div {...{ className: `motor-sports-card ${showTeamCount ? 'show-team-count' : ''}` }}>

            {
                item.custom_message != '' && item.custom_message != null &&
                <div className="announcement-custom-msg-wrapper">
                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                        <Tooltip id="tooltip" className="tooltip-featured">
                            <strong>{item.custom_message} </strong>
                        </Tooltip>
                    }>
                        <i className="icon-megaphone" onClick={(e) => e.stopPropagation()}></i>
                    </OverlayTrigger>
                </div>

            }


            <div {...{className: `ms-card-title ${sports_id == SportsIDs.tennis ? 'tennis' : "'"}`}}>
                {item.collection_name}
                <img src={Images['tourgame_' + item.league_image]} alt="" {...{ className: `moto-graphics ${sports_id == SportsIDs.tennis ? 'tennis' : "'"}` }}/>
            </div>
            <div {...{className: `ms-card-bottom ${sports_id == SportsIDs.tennis ? 'tennis' : "'"}`}}>
                <div className="msc-details">
                    <span {...{ className: `schedule` }}>
                        <SportsSchedule item={item} timerCallback={timerCallback} />
                    </span>
                    {/* <span className="sapbar" /> <span>{item.match_event} {CommonLabels.EVENTS}</span> */}
                </div>
                {
                    sports_id == SportsIDs.tennis ? 
                    <>
                    <div className="tag-sec">
                        <div className="tag">{item.league_name}</div>
                        <div className="tag no-tag">{item.match_event} {CommonLabels.ROUND_TXT}</div>
                    </div>
                    </>
                    :
                    <div className="msc-league-name">{item.league_name}</div>
                }
            </div>

            {
                showTeamCount && parseInt(teamCount) > 0 && item.status != 2 &&
                <div className={"fixture-card-footer justify-center"}>
                    <div className="fix-team-count">
                        {
                            AppLabels.JOINED_WITH + ' ' + teamCount + ' ' + AppLabels.TEAMS_MYCONTEST
                        }
                    </div>
                </div>
            }
            {
                item.status == 2 &&
                <div className={"fixture-card-footer justify-center"}>
                    {
                        showTeamCount && parseInt(teamCount) > 0 ?
                            <div className="fix-team-count">
                                {item.team ? item.team : AppLabels.TEAMS + ' ' + teamCount}
                            </div>
                            :
                            <div className="fix-team-count not-part">{int_version == "1" ? AppLabels.NOT_PART_OF_THIS_MATCH_INT : AppLabels.NOT_PART_OF_THIS_MATCH}</div>
                    }
                </div>
            }
        </div>
    )
}