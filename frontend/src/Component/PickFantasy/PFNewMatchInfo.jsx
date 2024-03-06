import React, {Suspense, lazy} from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "../CustomComponent";


export default class NewMatchCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: Constants.AppSelectedSport,
            timerCallback : this.props.timerCallback
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps != this.props){
            this.setState({
                timerCallback : nextProps.timerCallback
            })
        }

    }
    
    renderPrefix=(i)=>{
        var j = i % 10,
            k = i % 100;
        if (j == 1 && k != 11) {
            return i + "st";
        }
        if (j == 2 && k != 12) {
            return i + "nd";
        }
        if (j == 3 && k != 13) {
            return i + "rd";
        }
        return i + "th";
    }
  
    render() {
        const { item,status} = this.props;

        let lengthFixture = item ? item.length : 0
        let match_item = item
        let secInn = item['2nd_inning_count']

        const timeRemaining =  Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.scheduled_date), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ')
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={`new-dm-fixture-card`} 
                       >
                        {/* {
                            item.is_live != 1 && match_item.status == 0 && match_item.playing_announce == "1" && 
                            <span className='lineup-out-tag'>{AppLabels.LINEUP_OUT}</span>
                        } */}
                        <div className="time-sec">
                            {
                                // (item.status == 0 && !timeRemaining) ?
                                item.is_live == 1 ?
                                    <div className="tag live">
                                        <span></span>{AppLabels.LIVE}
                                    </div>
                                :
                                <>
                                    {
                                        item.status == 2 ?
                                        <div className="tag comp">
                                            {AppLabels.COMPLETED}
                                        </div>
                                        :
                                        Utilities.showCountDown(match_item)?
                                        <div className="countdown time-line">
                                            {match_item.game_starts_in &&
                                                (Utilities.minuteDiffValue({ date: match_item.game_starts_in }) <= 0) &&
                                                <CountdownTimer timerCallback={this.state.timerCallback} deadlineTimeStamp={match_item.game_starts_in} />
                                            }
                                        </div> :
                                        <span> 
                                            <MomentDateComponent data={{ date: item.scheduled_date, format: "D MMM - hh:mm A " }} />
                                        </span>
                                    }
                                </>
                            }
                        </div>
                        <div className={`fx-tm-nm `}>{item.home} {AppLabels.VERSES} {item.away}</div>
                        <div className="btm-sec">
                            {
                                // (item.status == 2 || item.is_live == 1) ?
                                // <span className={`rank-txt ${item.is_winner && item.is_winner != '0' ? ' succ' : ''}`}> 
                                //     {
                                //         item.is_winner && item.is_winner != '0' && <i className="icon-trophy"></i>
                                //     }
                                //     <span>{this.renderPrefix(item.game_rank)} {AppLabels.RANK}</span>
                                // </span>
                                // :
                                <span className="edit-txt regular">{AppLabels.JOINED_WITH} {item.team_count} {AppLabels.TEAMS_MYCONTEST} </span>
                            }
                            {/* <>
                                {
                                    secInn && parseInt(secInn) > 0 &&
                                    <>
                                        <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                <Tooltip id="tooltip" className={"tooltip-featured"}>
                                                    <strong> {AppLabels.SEC_INNING_CONTEST_JOINED} </strong>
                                                </Tooltip>
                                            }>
                                            <span className="sec-in" onClick={(e)=>e.stopPropagation()}>s</span>
                                        </OverlayTrigger>
                                    </>
                                }
                            </>                  */}
                        </div>
                       
                        {
                            item.is_live != 1 && match_item.status == 0 && match_item.delay_minute > "0" &&
                            <span>
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className={"tooltip-featured" + (match_item.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                                        <strong> {match_item.delay_message} </strong>
                                    </Tooltip>
                                }>
                                <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer delayed-tag">{AppLabels.DELAYED}</span>
                                </OverlayTrigger>
                            </span>
                        }


{/* {
    (item.status == 0 && item.is_live != 1) && (item.playing_announce == "1" || item.delay_minute > "0") &&
<div className="match-delay-info">
    {
        item.playing_announce == "1" && 
        <span >{AppLabels.LINEUP_OUT}</span>
    }
    {
        item.playing_announce =="1" && item.delay_minute 
        > "0" &&
            <span className="seperator-class"> | </span>
    }
    {
        item.delay_minute > "0" &&
        <span>
            <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                <Tooltip id="tooltip" className={"tooltip-featured" + (item.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                    <strong> {item.delay_message} </strong>
                </Tooltip>
            }>
            <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer">{AppLabels.DELAYED} {item.delay_text}</span>
            </OverlayTrigger>
        </span>
    }
</div>                                            
} */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}