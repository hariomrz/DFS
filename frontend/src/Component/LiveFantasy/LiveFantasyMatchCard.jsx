import React, {Suspense, lazy} from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities, _Map } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MATCH_TYPE ,SELECTED_GAMET} from "../../helper/Constants";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from "../../components/images";
import LivefantasyMatchInfo from "./LivefantasyMatchInfo";
import { io } from "socket.io-client";
import * as WSC from "../../WSHelper/WSConstants";
import WSManager from "../../WSHelper/WSManager";
import { LFCountdown } from ".";

const ReactSlickSlider = lazy(()=>import('../../Component/CustomComponent/ReactSlickSlider'));
var socket = ''
var globalThis = null;

export default class LiveFantasyMatchCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sports_id: Constants.AppSelectedSport,
            timerCallback : this.props.timerCallback,
            featureStrip: [{name:AppLabels.LEADERBOARD + ' ' + AppLabels.AVAILABLE, icon: 'icon-leaderboard'}, {name:AppLabels.LB_PRIZE_AVA,icon: 'icon-trophy'}],
            currentFIDX: 0,
            showCurrentItem: this.props.item.game && this.props.item.game.length > 2 ? 2 : this.props.item.game && this.props.item.game.length ? this.props.item.game.length : 0 ,
    
        }
    }
    UNSAFE_componentWillMount(){
        if (socket) {
            socket.disconnect();
        }
    }

  
   

    UNSAFE_componentWillReceiveProps(nextProps) {
        if(nextProps != this.props){
            this.setState({
                timerCallback : nextProps.timerCallback
            })
        }

    }
    fixtureCardClick = (event,gameData) => {
        const { item, gotoDetails } = this.props;
        event.stopPropagation()
        gotoDetails(item,event,gameData)
        // if(item.status == 0) {
        //     Utilities.gtmEventFire('upcoming_matches_screen', {"match_name": item.collection_name})
        // }
    }
    viewALL = (event,item) => {
        event.stopPropagation()
        if(item.season_game_uid){
            if(this.state.showCurrentItem != 2){
                this.setState({showCurrentItem:2,expandedItem:''})

            }
            else{
                this.setState({expandedItem :item.season_game_uid},()=>{
                    if(item.season_game_uid == this.state.expandedItem){
                        this.setState({showCurrentItem:item.game.length})
                    }
                    
                })
            }
            
        }
     
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
                                {/* <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : this.state.preFIDX === indx ? ' next-s' : this.state.nextINDX === indx ? ' slide-in-v' : ' next-s' )}><i className="icon-leaderboard" /><span>{item}</span></div> */}
                                <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : ' next-s' )}><i className={item.icon} /><span>{item.name}</span></div>
                            </div>
                        )
                    })
                }
            </ReactSlickSlider>
        )
    }
    _istimerOver = (gameData) => {
        this.props.timerCallback(gameData)
        Utilities.removeSoketEve(gameData.collection_id)
    }  
    getIsTimeOverer =(game)  =>{
        let timeRemainging = false;
        if(game.timer_date!= ''){
            let dateObj = Utilities.getUtcToLocal(game.timer_date)
            let over_start_time = new Date(dateObj).getTime();
            let cTime = new Date().getTime();
            if(cTime < over_start_time){
                timeRemainging = true
            }
            else if(cTime > over_start_time){
                timeRemainging = false

            }

        }
        return timeRemainging;


    }
    render() {
        const { item, gotoDetails,gotoLeaderBoard,isFromFreeToPlayLandingPage,isFrom, onLBClick, isSecondInning} = this.props;
        let sponserImage = item.sponsor_logo && item.sponsor_logo!=null ? item.sponsor_logo : 0
        let dateObj = Utilities.getUtcToLocal(item.season_scheduled_date)
        let game_start_time = new Date(dateObj).getTime();
        let cTime = new Date().getTime();

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"fixture-card-wrapper fixture-card-wrapper-new cursor-pointer xhighlighted-card " + (this.props.fixtureCardLg ? ' fixture-card-wrapper-lg livef' : '')} >
                        <div className={"fixture-card-body display-table padding-fix" + (isSecondInning ? ' sec-innig' : '')}>
                            {
                                item.custom_message != '' && item.custom_message != null &&
                                <div className="announcement-custom-msg-wrapper">
                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                 <strong>{item.custom_message} </strong> 
                                            </Tooltip>
                                        }>
                                        <i className="icon-megaphone" onClick={(e)=>e.stopPropagation()}></i>
                                    </OverlayTrigger>
                                </div>
                            
                            }
                            {
                              Utilities.getMasterData().booster == 1 && item.booster && item.booster != '' &&  !isSecondInning && 
                                <div className={"booster" + (item.custom_message != '' && item.custom_message != null ? ' is-custom-message' : '')}>
                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                 <small style={{fontFamily: 'PrimaryF-Regular',fontWeight:'lighter'}}>{AppLabels.AVAILABLE_BOOSTER} </small> 
                                                 <br></br>
                                                 <strong>{item.booster} </strong> 

                                            </Tooltip>
                                        }>
                                        <img src={Images.BOOSTER_ICON} alt='' onClick={(e)=>e.stopPropagation()}></img>
                                    </OverlayTrigger>
                                </div>
                            
                            }

                            <LivefantasyMatchInfo item={item} timerCallback={this.state.timerCallback} isFrom={isFrom} />
                        </div>
                        <div className={"fixture-card-footer live-fantasy"}>
                            
                            {isFrom != 'MyContestSlider' && item.game && item.game.length > 0 &&
                                item.game.slice(0, this.state.showCurrentItem).map((gameData, indx) => {
                                    return (
                                        <div onClick={(event) => this.fixtureCardClick(event,gameData)} key={indx} className='over-conatiner'>
                                            <div className='ball-over-conatiner'>
                                                <i className='icon-game-ball icon-ball'></i>
                                                <div className='over-inner-conatiner'>
                                                    <div className='over-number'>{AppLabels.OVER + ' ' + gameData.over}</div>
                                                    {
                                                        indx == 0 && cTime > game_start_time &&
                                                        <div className='over-status up-next'>
                                                            {AppLabels.UP_NEXT}
                                                            <div className="arw-cont">
                                                                <i className="icon-arrow-right"></i> <i className="icon-arrow-right"></i>
                                                            </div>
                                                        </div>

                                                    }


                                                </div>
                                                {/* {
                                                    gameData.timer_date!= '' && this.getIsTimeOverer(gameData) &&
                                                } */}
                                                <div className='timer-container'>
                                                    <div className={'timer-holder-lf'}>
                                                        <LFCountdown
                                                            {...{
                                                                onComplete: this._istimerOver,
                                                                data: gameData,
                                                                show: gameData.timer_date != ''
                                                            }}
                                                        />
                                                    </div>
                                                </div>

                                            </div>
                                            <div className='prize-points-container'>
                                                <div className='points-winning-value'>
                                                    {gameData.prize_pool > 0 && AppLabels.WIN + ' ' + Utilities.getMasterData().currency_code + Utilities.getPrizeInWordFormat(gameData.prize_pool)}

                                                </div>
                                                {/* <div className='over-status'></div> */}


                                            </div>

                                        </div>
                                    )
                                })

                            }

                            {
                                isFrom != 'MyContestSlider' && item.game && item.game.length > 0  && item.game.length > 2 &&
                                <div onClick={(event) => this.viewALL(event,item)} className='view-all'>{AppLabels.VIEW}{this.state.showCurrentItem == 2 ? " " + AppLabels.ALL :  ' Less'} </div>

                            }
                            {isFrom == 'MyContestSlider' && item  &&
                                <div onClick={(event) => this.fixtureCardClick(event, item)} className='over-conatiner'>
                                    <div className='ball-over-conatiner'>
                                        <i className='icon-game-ball icon-ball'></i>
                                        <div className='over-inner-conatiner'>
                                            <div className='over-number'>{AppLabels.OVER + ' ' + item.overs}</div>
                                            {
                                                  item.status != 1 &&item.match_status == 1 &&  cTime > game_start_time &&
                                                    <div className='over-status up-next'>
                                                        {AppLabels.UP_NEXT} 
                                                        <div className="arw-cont">
                                                            <i className="icon-arrow-right"></i> <i className="icon-arrow-right"></i>
                                                        </div>
                                                    </div>

                                                }
                                            {
                                                !Utilities.showCountDown(item) && item.status == 1 ?
                                                <div className='live-status-container'>
                                                    <div className='oval'></div>

                                                    <div className='live-status'>{AppLabels.LIVE}</div>

                                                </div>
                                                :
                                                item.status == 2 &&
                                                <div className='live-status-container'>

                                                    <div className='live-status completed'>{AppLabels.COMPLETED}</div>

                                                </div>
                                            }



                                        </div>
                                        {/* {
                                                    item.timer_date!= '' && this.getIsTimeOverer(item) &&
                                                } */}
                                        <div className='timer-container'>
                                            <div className={'timer-holder-lf'}>
                                            <LFCountdown
                                                    {...{
                                                        onComplete: this._istimerOver,
                                                        data: item,
                                                        show: item.timer_date != ''
                                                    }}
                                                />
                                            </div>
                                        </div>

                                    </div>
                                    <div className='prize-points-container'>
                                        <div className='points-winning-value mycontset-sider'>
                                            {item.total_score ? item.total_score : 0}

                                        </div>
                                        <div className='over-status'>
                                            {AppLabels.TOTAL + " " + AppLabels.PTS}

                                        </div>


                                    </div>

                                </div>

                            }

                            
                            {/* {
                                process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 &&
                                <div className="winning-section">
                                    { 
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
                                                <div className="user-contest-detail ">{item.team_count} {AppLabels.TEAMSS} | {item.contest_count} {AppLabels.CONTESTS_POPUP}</div>
                                            }
                                        </React.Fragment>
                                        :
                                        item.total_prize_pool > 0 &&
                                            <div className="collection-list-prize-pool">{AppLabels.WINNINGS}&nbsp;<span> {Utilities.getMasterData().currency_code + Utilities.getPrizeInWordFormat(item.total_prize_pool)}</span></div> 
                                            // : <div className="collection-list-prize-pool">{AppLabels.PRACTICE}</div>
                                        
                                    }
                                
                                </div>
                            }
                         */}
                        </div>
                        {
                            item.ldb == '1' && !isSecondInning && <Suspense fallback={<div />} >
                                {this.renderFeatureStrip(onLBClick)}
                            </Suspense>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}