import React, {Suspense, lazy} from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map,addOrdinalSuffix } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import CountdownTimer from '../../views/CountDownTimer';
import { MomentDateComponent } from "./CustomComponents";
const ReactSlickSlider = lazy(()=>import('./ReactSlickSlider'));


export default class NewMatchCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: Constants.AppSelectedSport,
            timerCallback : this.props.timerCallback,
            featureStrip: [{name:AppLabels.LEADERBOARD + ' ' + AppLabels.AVAILABLE, icon: 'icon-leaderboard'}, {name:AppLabels.LB_PRIZE_AVA,icon: 'icon-trophy'}],
            currentFIDX: 0
        }
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps != this.props){
            this.setState({
                timerCallback : nextProps.timerCallback
            })
        }

    }
    fixtureCardClick = (event) => {
        const { item, gotoDetails } = this.props;
        let sDate = new Date(Utilities.getUtcToLocal(item.season_scheduled_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;

        gotoDetails(item, event)
        if(item.status == 0) {
            Utilities.gtmEventFire('upcoming_matches_screen', {"match_name": item.collection_name})
        }
    }
    gameCenter = (event) => {
        const { item, gotoGameCenter } = this.props;
        gotoGameCenter(item, event)
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
            beforeChange:(cidx,nextIDX)=> this.setState({ currentFIDX : nextIDX, preFIDX: nextIDX - 1, nextINDX: nextIDX + 1}),
            speed: 600,
            className:'ldb-strip-slider',
            swipe: false,
            arrows: false
        };
        return (
            <ReactSlickSlider settings={settings}>
                {
                    _Map(fList, (item,indx) => {
                        return (
                            <div key={indx} onClick={onLBClick} className={"ldb-strip d-flex " + (fList.length > 1 ? 'justify-content-start' : '')}>
                                <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : ' next-s' )}><i className={item.icon} /><span>{item.name}</span></div>
                            </div>
                        )
                    })
                }
            </ReactSlickSlider>
        )
    }

    showTourTimimg=(item)=>{
        let sDate = new Date(Utilities.getUtcToLocal(item.start_date))
        let game_starts_in = Date.parse(sDate)
        item['game_starts_in'] = game_starts_in;
        return <>
            { 
                item.status == 3 ?
                <div className="tag comp">
                    {AppLabels.COMPLETED}
                </div>
                :
                <>
                {
                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.start_date), 'YYYY-MM-DD HH:mm ')
                    ? 
                    <div className="tag live">
                        <span></span>{AppLabels.LIVE}
                    </div>
                    :
                    <span>
                        {
                            Utilities.showCountDown(item) ?
                            <div className="countdown time-line">
                                {item.game_starts_in &&
                                    (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                                    <CountdownTimer deadlineTimeStamp={item.game_starts_in} />
                                }
                            </div> 
                            :
                            <MomentDateComponent data={{ date: item.start_date, format: "D MMM - hh:mm A " }} />
                        }
                    </span>
                }
                </>
            }
        </>
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
        const { item} = this.props;

        let lengthFixture = item.match_list ? item.match_list.length : 0
        let match_item = lengthFixture >= 1 ? item.match_list && item.match_list[0] : item
        let secInn = item['2nd_inning_count']
        let {int_version} = Utilities.getMasterData()


        const timeRemaining =  Utilities.getFormatedDateTime(Utilities.getUtcToLocal(item.season_scheduled_date), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ')

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={`new-dm-fixture-card ${item.tournament_id ? ' with-tour' : ''}`} 
                        onClick={(event) => this.fixtureCardClick(event)}>
                        {
                            item.is_live != 1 && 
                            item.status == 0 && item.playing_announce == "1" && 
                            <span className='lineup-out-tag'>{AppLabels.LINEUP_OUT}</span>
                        }
                        <div className="time-sec">
                            {
                                item.tournament_id ?
                                
                                this.showTourTimimg(item)
                                :
                                (item.status == 0 && !timeRemaining) ?
                                    <div className="tag live">
                                        <span></span>{AppLabels.LIVE}
                                    </div>
                                :
                                <>
                                    {
                                        (item.status == 1 && !timeRemaining) ?
                                        <div className="tag comp">
                                            {AppLabels.COMPLETED}
                                        </div>
                                        :
                                        Utilities.showCountDown(item) && (item.status == 0 && timeRemaining) ?
                                        <div className="countdown time-line">
                                            {item.game_starts_in &&
                                                (Utilities.minuteDiffValue({ date: item.game_starts_in }) <= 0) &&
                                                <CountdownTimer timerCallback={this.state.timerCallback} deadlineTimeStamp={item.game_starts_in} />
                                            }
                                        </div> :
                                        <span> 
                                            <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                        </span>
                                    }
                                </>
                            }
                        </div>
                        {
                            item.tournament_id ?
                            <>
                                <div className="tour-title">{item.name}</div>
                                <div className="tag-sec">
                                    <div className="tag">{AppLabels.TOURNAMENT}</div>
                                    <div className="tag"> {item.match_count || 0} {int_version == "1" ? AppLabels.GAMES : AppLabels.FIXTURE_TEXT}</div>
                                </div>
                            </>
                            :
                            <>
                                <div className="fx-tm-nm">{item.collection_name}</div>
                            </>
                        }
                        <div className="btm-sec">
                            {
                                item.tournament_id ?
                                <span className={`rank-txt ${item.is_winner && item.is_winner != '0' ? ' succ' : ''}`}> 
                                    {
                                        item.is_winner && item.is_winner != '0' && <i className="icon-trophy"></i>
                                    }
                                    <span>{addOrdinalSuffix(item.rank_value)} {AppLabels.RANK}</span>
                                </span>
                                :
                                <>
                                    {/* // item.status == 2 ?
                                    //     <>
                                    //         <span className="rank-txt">
                                    //             {
                                    //                 item.is_winner && item.is_winner != '0' && <i className="icon-trophy"></i>
                                    //             }
                                    //             {item.game_rank} {AppLabels.RANK}
                                    //         </span>
                                    //     </>
                                    //     : */}
                                    <span className="edit-txt regular">{AppLabels.JOINED_WITH} {item.team_count} {AppLabels.TEAMS_MYCONTEST} </span>
                                    <>
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
                                    </>
                                </>
                            }                            
                        </div>
                       
                        {
                            item.is_live != 1 &&
                            item.status == 0 && item.delay_minute > "0" &&
                            <span>
                                <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                    <Tooltip id="tooltip" className={"tooltip-featured" + (item.delay_message != '' ? ' display-tooltip' : ' hide-tooltip')}>
                                        <strong> {item.delay_message} </strong>
                                    </Tooltip>
                                }>
                                <span  onClick={(e)=>e.stopPropagation()} className="cursor-pointer delayed-tag">{AppLabels.DELAYED}</span>
                                </OverlayTrigger>
                            </span>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}