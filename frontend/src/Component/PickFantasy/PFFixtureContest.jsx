import React from 'react';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import CountdownTimer from '../../views/CountDownTimer';
import { MatchCard, MomentDateComponent } from "../CustomComponent";
import Images from '../../components/images';
import PFMatchCard from './PFMatchCard';

export default class PFFixtureContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            allowCollection: Utilities.getMasterData().a_collection,
            slideIndex: 0
        }
    }

    gotoDetails = (ContestListItem, event) => {
        this.props.gotoDetails(ContestListItem, event);
    }
    gotoGameCenter =(ContestListItem, event) => {
        this.props.gotoGameCenter(ContestListItem, event);
    }

    render() {
        const { ContestListItem, indexKey, timerCallback, onLBClick,sports_id } = this.props;
        let isPinned = ContestListItem.is_pin_fixture == 1 ? true : false
        console.log('sports_id',this.props)
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
                           <PFMatchCard sports_id={sports_id} item={ContestListItem} gotoGameCenter={this.gotoGameCenter} gotoDetails={this.gotoDetails} fixtureCardLg={true} timerCallback={timerCallback} onLBClick={onLBClick}/>

                    </li>
                )}
            </MyContext.Consumer>
        )
    }
}