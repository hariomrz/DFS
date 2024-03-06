import React from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MatchCard, MomentDateComponent } from "../../Component/CustomComponent";
import * as Constants from "../../helper/Constants";
import WSManager from "../../WSHelper/WSManager";
import MiniLeagueCard from './MiniLeagueCard'



export default class FreeToPlayFixtureContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            slideIndex: 0
        }
    }

    UNSAFE_componentWillMount = () => {
        WSManager.setPickedGameType(Constants.GameType.Free2Play)
    }
    FixtureListFunction = (item) => {
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
                                Utilities.showCountDown(item) ?
                                    <div className="countdown time-line">
                                        {item.game_starts_in && <CountdownTimer
                                            deadlineTimeStamp={item.game_starts_in}
                                            timerCallback={this.props.timerCallback}
                                        />}
                                    </div> :
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
    
    gotoLeaderBoard = (ContestListItem, event) => {
        this.props.gotoLeaderBoard(ContestListItem, event);
    }
     render() {
        const { ContestListItem, indexKey, timerCallback,isFromFreeToPlayLandingPage } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <li key={indexKey} style={{position: 'relative'}}>
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
                        {
                           ContestListItem.obj_type && ContestListItem.obj_type =="fixture" ?
                           <MatchCard item={ContestListItem} isFromFreeToPlayLandingPage={isFromFreeToPlayLandingPage} gotoDetails={this.gotoDetails} gotoLeaderBoard={this.gotoLeaderBoard} fixtureCardLg={true} timerCallback={timerCallback}/>
                           :ContestListItem.obj_type && ContestListItem.obj_type =="league" ?
                           <MiniLeagueCard item={ContestListItem} isFromFreeToPlayLandingPage={isFromFreeToPlayLandingPage} gotoDetails={this.gotoDetails} gotoLeaderBoard={this.gotoLeaderBoard} fixtureCardLg={true} timerCallback={timerCallback}/>
                           :
                           <MatchCard item={ContestListItem} gotoDetails={this.gotoDetails} gotoLeaderBoard={this.gotoLeaderBoard} fixtureCardLg={true} timerCallback={timerCallback}/>



                        }
                    </li>
                )}
            </MyContext.Consumer>
        )
    }
}