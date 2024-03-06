import React, {Suspense, lazy} from "react";
import { MyContext } from '../../InitialSetup/MyProvider';
import PFMatchInfo from "./PFMatchInfo";
import { Utilities, _Map } from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MATCH_TYPE ,SELECTED_GAMET} from "../../helper/Constants";
import * as Constants from "../../helper/Constants";
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from "../../components/images";
import { SportsIDs } from "../../JsonFiles";
import PFNewMatchInfo from "./PFNewMatchInfo";
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));


export default class PFNewMatchCard extends React.Component {
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
                                {/* <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : this.state.preFIDX === indx ? ' next-s' : this.state.nextINDX === indx ? ' slide-in-v' : ' next-s' )}><i className="icon-leaderboard" /><span>{item}</span></div> */}
                                <div className={'anim-v' + (this.state.currentFIDX === indx ? ' slide-in-v' : ' next-s' )}><i className={item.icon} /><span>{item.name}</span></div>
                            </div>
                        )
                    })
                }
            </ReactSlickSlider>
        )
    }
  
    render() {
        const { item,gotoLeaderBoard,isFromFreeToPlayLandingPage,isFrom, onLBClick, isSecondInning,gotoGameCenter} = this.props;
        let sponserImage = item.sponsor_logo && item.sponsor_logo!=null ? item.sponsor_logo : 0
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"new-pf-card fixture-card-wrapper cursor-pointer xhighlighted-card " + (this.props.fixtureCardLg ? ' fixture-card-wrapper-lg' : '')} onClick={(event) => this.fixtureCardClick(event)}>
                        <div className={"fixture-card-body display-table p0" + (isSecondInning ? ' sec-innig' : '')}>
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
                            {/* {
                                Utilities.getMasterData().booster == 1 && item.booster && item.booster != '' && !isSecondInning &&
                                <div className={"booster" + (item.custom_message != '' && item.custom_message != null ? ' is-custom-message' : '')}>
                                    <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                        <Tooltip id="tooltip" className="tooltip-featured">
                                            <small style={{ fontFamily: 'PrimaryF-Regular', fontWeight: 'lighter' }}>{AppLabels.AVAILABLE_BOOSTER} </small>
                                            <br></br>
                                            <strong>{item.booster} </strong>

                                        </Tooltip>
                                    }>
                                        <img src={Images.BOOSTER_ICON} alt='' onClick={(e) => e.stopPropagation()}></img>
                                    </OverlayTrigger>
                                </div>

                            } */}

                            <PFNewMatchInfo isSecondInning={isSecondInning} item={item} timerCallback={this.state.timerCallback} isFrom={isFrom} />
                        </div>
                        {/* <div className={"fixture-card-footer" + (item.league_name ? '' : ' justify-center') + (SELECTED_GAMET == Constants.GameType.Free2Play ? ' height-league-card' : '')}>
                          
                            {item.league_name &&
                                <div className={"match-type" + (process.env.REACT_APP_LOBBY_WINNING_ENABLE == 1 ? '' : ' match-type-only')}>
                                    {item.league_name || item.league_abbr}
                                    
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
                                                                    item.total_entry_fee > 0 ?
                                                                        <React.Fragment> {Utilities.getMasterData().currency_code + Utilities.kFormatter(item.total_entry_fee)}</React.Fragment>
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
                        { Utilities.getMasterData().allow_gc == 1 && item.is_gc ==1 && isFrom != 'MyContestSlider' && 
                            <div onClick={(event) => this.gameCenter(event)} className='game-center-strip'>
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
                        }
                        {
                            item.ldb == '1' && !isSecondInning && <Suspense fallback={<div />} >
                                {this.renderFeatureStrip(onLBClick)}
                            </Suspense>
                        } */}
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}