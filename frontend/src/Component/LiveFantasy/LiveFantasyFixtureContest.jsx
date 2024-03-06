import React from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import {  MomentDateComponent } from "../../Component/CustomComponent";
import Images from '../../components/images';
import { LiveFantasyMatchCard } from '.';
import CountdownTimer from '../../views/CountDownTimer';


export default class LiveFantasyFixtureContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            allowCollection: Utilities.getMasterData().a_collection,
            slideIndex: 0
        }
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

    gotoDetails = (ContestListItem, event,gameData) => {
        this.props.gotoDetails(ContestListItem, event,gameData);
    }

    render() {
        const { ContestListItem, indexKey, timerCallback, onLBClick } = this.props;
        let isPinned = ContestListItem.is_pin_fixture == 1 ? true : false
        return (
            <MyContext.Consumer>
                {(context) => (

                    <li key={indexKey} style={{position: 'relative'}} className={isPinned ? "lobby-pin":''}>
                        {
                            isPinned && <div className="contest-pin">
                                <i className="icon-pinned-ic"></i>
                            </div>
                        }
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
                                            <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer"> {AppLabels.DELAYED} {ContestListItem.delay_text}</span>
                                            </OverlayTrigger>
                                        </span>
                                    }
                                </div>
                            
                        }
                           <LiveFantasyMatchCard item={ContestListItem} gotoDetails={this.gotoDetails} fixtureCardLg={true} timerCallback={timerCallback} onLBClick={onLBClick}/>

                    </li>
                )}
            </MyContext.Consumer>
        )
    }
}