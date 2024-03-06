import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
import { CircularProgressBar } from "../CustomComponent";
import { Utilities } from '../../Utilities/Utilities';


class PickemCoachMark extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: Utilities.getMasterData().sports_hub.length == 1 ? (Utilities.getMasterData().a_coin == "1" ? 0 : 1) : 0, 
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('pickem-coachmark', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('pickem-coachmark', 1)
        this.props.cmData.mHide();
    }
    showPreviouPage = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })

    }
    showNextPage = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })


    }
    circleTransitions = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })

    }
    onSwiped = (eventData) => {
        if (eventData && eventData.dir === "Left") {
            if (this.state.indexPage == 0 && (Utilities.getMasterData().a_coin == "1" || Utilities.getMasterData().sports_hub.length != 1)) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 2 })
            }
            else if (this.state.indexPage == 2) {
                this.setState({ indexPage: 3 })
            }
            else if (this.state.indexPage == 3) {
                this.setState({ indexPage: 4 })
            }
            else if (this.state.indexPage == 4) {
                this.setState({ indexPage: 5 })
            }
            else if (this.state.indexPage == 5) {
                this.setState({ indexPage: 6 })
            }
        }
        if (eventData && eventData.dir === "Right") {
            if (this.state.indexPage == 6) {
                this.setState({ indexPage: 5 })
            }
            else if (this.state.indexPage == 5) {
                this.setState({ indexPage: 4 })
            }
            else if (this.state.indexPage == 4) {
                this.setState({ indexPage: 3 })
            }
            else if (this.state.indexPage == 3) {
                this.setState({ indexPage: 2 })
            }
            else if (this.state.indexPage == 2) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1 && (Utilities.getMasterData().a_coin == "1" || Utilities.getMasterData().sports_hub.length != 1)) {
                this.setState({ indexPage: 0 })
            }
        }
    }

    PickedPercentage = (picked, total) => {
        let pickedPer = picked == 0 ? 0 : ((picked / total) * 100).toFixed(2);
        let checkpickedPer = (pickedPer % 1) == 0 ? Math.floor(pickedPer) : pickedPer;
        pickedPer = Math.round(checkpickedPer);
        return pickedPer;
    }

    render() {
        const { mShow ,mHide} = this.props.cmData;
        const {sportsList} = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"contest-detail-modal prop-coin-coachmark coin-coachmark xwallet-coins-coachmark coachmark-modal " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >

                            <div className='skip-header-view'>

                                {/* <a style={{ left: 20, right: 'auto' }} href onClick={this.hideCoachMark} className="modal-close">
                            </a> */}
                                <span style={{ left: 20 }} onClick={this.hideCoachMark} className='skip-close'>{AL.SKIP_STEP}</span>

                                <div className="walkthrough-circle ">
                                    {
                                        (sportsList.length != 1 || Utilities.getMasterData().a_coin == "1") &&
                                        <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-one" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
                                    }
                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-two" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 3)} className={"circle circle-four" + (this.state.indexPage == 3 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 4)} className={"circle circle-five" + (this.state.indexPage == 4 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 5)} className={"circle circle-six" + (this.state.indexPage == 5 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 6)} className={"circle circle-seven" + (this.state.indexPage == 6 ? ' selected-page' : '')}></div>

                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container pickem-coac" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-third' : this.state.indexPage == 3 ? ' CM-fourth' : this.state.indexPage == 4 ? ' CM-fifth' : this.state.indexPage == 5 ? ' CM-sixth' : this.state.indexPage == 6 ? ' CM-seventh' : '')}>
                                    {
                                        (sportsList.length != 1 || Utilities.getMasterData().a_coin == "1") &&
                                        this.state.indexPage == 0 &&
                                        <div>
                                            <div className="coachmark-heading">
                                                {
                                                    sportsList.length == 1 && Utilities.getMasterData().a_coin == "1" ?
                                                    AL.LOBBY_COAC_LABEL_EC1 : AL.LOBBY_COAC_LABEL1
                                                }
                                            </div>
                                            <div className="coachmark-text">
                                                {
                                                    sportsList.length == 1 && Utilities.getMasterData().a_coin == "1" ?
                                                    AL.LOBBY_COAC_TEXT_EC1 : AL.LOBBY_COAC_TEXT1
                                                }
                                            </div>
                                            <div className={"navigation-view"}>
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10" + (this.state.indexPage == 0 ? ' is-disable' : ' ')}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 1)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop rotate-full" />
                                            </div>
                                            <div className="ring-container">
                                                <div className="pulse-view" style={{ animationDelay: "-2s" }}></div>
                                                <div className="pulse-view" style={{ animationDelay: "-1s" }}></div>
                                                <div className="pulse-view" style={{ animationDelay: "0s" }}></div>
                                            </div>
                                            <div className="coin-footer">
                                                {
                                                    sportsList.length == 1 && Utilities.getMasterData().a_coin == "1" &&
                                                    <span className="coins-tab-label">{AL.EARN_COINS}</span>
                                                }
                                                <img src={(sportsList.length == 1 && Utilities.getMasterData().a_coin == "1") ? Images.EARN_COINS : Images.DT_SPORTS_HUB} alt="" />
                                            </div>


                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">
                                                <ul className="sport-bar-sec">
                                                    <li className="active">
                                                        Cricket
                                                    </li>
                                                    <li>
                                                        Soccer
                                                    </li>
                                                    <li>
                                                        kabaddi
                                                    </li>
                                                </ul>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL2}</div>
                                            <div className="coachmark-text">{AL.PCKM_COAC_TEXT2}</div>
                                            <div className="navigation-view-four navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10" + ((sportsList.length != 1 || Utilities.getMasterData().a_coin == "1") ? ' ' : ' is-disable')}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 2)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>                                      

                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-three">
                                            <div className="bg-highlighter">
                                                <span className="text-htp"><i className="icon-question"></i> {AL.HOW_TO_PLAY}</span>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL3}</div>
                                            <div className="coachmark-text">{AL.PCKM_COAC_TEXT3}</div>


                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 1)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showPreviouPage(e, 3)} className={"rectangle-right"}>
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>

                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 3 &&
                                        <div className="page-four">
                                            <div className="hight-wrap">
                                                <div className="bg-highlighter">
                                                    <a href className="earn-coin-txt"> <img src={Images.IC_COIN} alt=""/> Earn Coins</a>
                                                </div>
                                                <div className="image-strip resp-view">
                                                    <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop" />
                                                </div>
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop hor-line web-view" />
                                                <div className="image-strip web-view">
                                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                                </div>
                                            </div>
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL4}</div>
                                            <div className="coachmark-text">{AL.PCKM_COAC_TEXT4}</div>
                                            <div className="navigation-view-four navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 2)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 4)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div> 
                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 4 &&
                                        <div className="page-five">
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter">
                                                    <div class="pickem-prediction-outer-card">
                                                        <div class="countdown-timer-section">
                                                            <span><strong>03</strong><span>:</span><strong>58</strong><span>:</span><strong>18</strong><span></span></span>
                                                        </div>
                                                        <i class="icon-share pickem-share"></i>
                                                        <div class="pickem-prediction-card xpick-pred-two">
                                                            <div class="option-section">
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    75,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.PICKEM_TEAM_IMG1} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="option-name">Fnatic</div>
                                                                    </div>
                                                                </div>
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    5,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.DRAW_ICON} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                    <div class="option-name">Draw</div>
                                                                </div>
                                                            </div>
                                                                <div class="option-info ">
                                                                        <span class="correct-text">Correct answer</span>
                                                                        <i class="icon-tick-circular"></i>
                                                                        <i class="icon-cross-circular"></i>
                                                                        <div class="">
                                                                            <div class="option-img">
                                                                                <CircularProgressBar
                                                                                    data={''}
                                                                                    progressPer={this.PickedPercentage(
                                                                                        20,
                                                                                        100
                                                                                    )
                                                                                    }
                                                                                />
                                                                                <div class="option-img-wrap">
                                                                                    <img src={Images.PICKEM_TEAM_IMG2} alt=""/>
                                                                                </div>
                                                                            </div>
                                                                        <div class="option-name">Liquid</div>
                                                                    </div>
                                                                </div>                                                            
                                                            </div>
                                                            <div class="pickem-pred-info"><div class="league-name">ONE Esports Grand Tournament</div><div class="first-to-predict">Prize Pool <img src={Images.IC_COIN} alt="" class="coin-img" /><span>30</span></div></div>
                                                        </div> 
                                                    </div>
                                                </div>
                                            </div>    
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                            </div>     
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL5}</div>
                                            <div className="coachmark-text">{AL.PCKM_COAC_TEXT5}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 3)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 5)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>                                      
                                        </div>

                                    }
                                    {
                                        this.state.indexPage == 5 &&
                                        <div className="page-six">   
                                           
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL6}</div>
                                                <div className="coachmark-text">{AL.PCKM_COAC_TEXT6}</div>
                                                <div className="navigation-view">
                                                    <div onClick={(e) => this.showPreviouPage(e, 4)} className="rectangle-left mr10">
                                                        <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                    </div>
                                                    <div onClick={(e) => this.showNextPage(e, 6)} className="rectangle-right">
                                                        <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                    </div>
                                                </div>     
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five img2 " />
                                            </div>  
                                             <div className="highlighter-wrap">
                                                <div className="bg-highlighter bg-highlighter-2">
                                                    <span><strong>03</strong><span>:</span><strong>58</strong><span>:</span><strong>18</strong><span></span></span>
                                                </div> 
                                                <div className="bg-highlighter shadow-none">
                                                    <div className="overlay-highlighter"></div>
                                                    <div class="pickem-prediction-outer-card">
                                                        <div class="countdown-timer-section">
                                                            <span><strong>03</strong><span>:</span><strong>58</strong><span>:</span><strong>18</strong><span></span></span>
                                                        </div>
                                                        <i class="icon-share pickem-share"></i>
                                                        <div class="pickem-prediction-card xpick-pred-two">
                                                            <div class="option-section">
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    75,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.PICKEM_TEAM_IMG1} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="option-name">Fnatic</div>
                                                                    </div>
                                                                </div>
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    5,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.DRAW_ICON} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                    <div class="option-name">Draw</div>
                                                                </div>
                                                            </div>
                                                                <div class="option-info ">
                                                                        <span class="correct-text">Correct answer</span>
                                                                        <i class="icon-tick-circular"></i>
                                                                        <i class="icon-cross-circular"></i>
                                                                        <div class="">
                                                                            <div class="option-img">
                                                                                <CircularProgressBar
                                                                                    data={''}
                                                                                    progressPer={this.PickedPercentage(
                                                                                        20,
                                                                                        100
                                                                                    )
                                                                                    }
                                                                                />
                                                                                <div class="option-img-wrap">
                                                                                    <img src={Images.PICKEM_TEAM_IMG2} alt=""/>
                                                                                </div>
                                                                            </div>
                                                                        <div class="option-name">Liquid</div>
                                                                    </div>
                                                                </div>                                                            
                                                            </div>
                                                            <div class="pickem-pred-info"><div class="league-name">ONE Esports Grand Tournament</div><div class="first-to-predict">Prize Pool <img src={Images.IC_COIN} alt="" class="coin-img" /><span>30</span></div></div>
                                                        </div> 
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 6 &&
                                        <div className="page-six">   
                                            <div className="coachmark-heading">{AL.PCKM_COAC_LABEL7}</div>
                                                <div className="coachmark-text">{AL.PCKM_COAC_TEXT7}</div>
                                                <div className="navigation-view">
                                                    <div onClick={(e) => this.showPreviouPage(e, 5)} className="rectangle-left mr10">
                                                        <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                    </div>
                                                </div>     
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five img2 " />
                                            </div>  
                                             <div className="highlighter-wrap">
                                                <div className="bg-highlighter bg-highlighter-2">
                                                    <i className="icon-share"></i>
                                                </div> 
                                                <div className="bg-highlighter shadow-none">
                                                    <div className="overlay-highlighter"></div>
                                                    <div class="pickem-prediction-outer-card">
                                                        <div class="countdown-timer-section">
                                                            <span><strong>03</strong><span>:</span><strong>58</strong><span>:</span><strong>18</strong><span></span></span>                                                
                                                        </div>
                                                        <i class="icon-share pickem-share"></i>
                                                        <div class="pickem-prediction-card xpick-pred-two">
                                                            <div class="option-section">
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    75,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.PICKEM_TEAM_IMG1} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="option-name">Fnatic</div>
                                                                    </div>
                                                                </div>
                                                                <div class="option-info ">
                                                                    <span class="correct-text">Correct answer</span>
                                                                    <i class="icon-tick-circular"></i>
                                                                    <i class="icon-cross-circular"></i>
                                                                    <div class="">
                                                                        <div class="option-img">
                                                                            <CircularProgressBar
                                                                                data={''}
                                                                                progressPer={this.PickedPercentage(
                                                                                    5,
                                                                                    100
                                                                                )
                                                                                }
                                                                            />
                                                                            <div class="option-img-wrap">
                                                                                <img src={Images.DRAW_ICON} alt=""/>
                                                                            </div>
                                                                        </div>
                                                                    <div class="option-name">Draw</div>
                                                                </div>
                                                            </div>
                                                                <div class="option-info ">
                                                                        <span class="correct-text">Correct answer</span>
                                                                        <i class="icon-tick-circular"></i>
                                                                        <i class="icon-cross-circular"></i>
                                                                        <div class="">
                                                                            <div class="option-img">
                                                                                <CircularProgressBar
                                                                                    data={''}
                                                                                    progressPer={this.PickedPercentage(
                                                                                        20,
                                                                                        100
                                                                                    )
                                                                                    }
                                                                                />
                                                                                <div class="option-img-wrap">
                                                                                    <img src={Images.PICKEM_TEAM_IMG2} alt=""/>
                                                                                </div>
                                                                            </div>
                                                                        <div class="option-name">Liquid</div>
                                                                    </div>
                                                                </div>                                                            
                                                            </div>
                                                            <div class="pickem-pred-info"><div class="league-name">ONE Esports Grand Tournament</div><div class="first-to-predict">Prize Pool <img src={Images.IC_COIN} alt="" class="coin-img" /><span>30</span></div></div>
                                                        </div> 
                                                    </div>
                                                </div>
                                            </div> 
                                            <div className="bottomView">
                                                {/* <div onClick={this.hideCoachMark} className="innerbox-preview"> */}
                                                <a onClick={()=>this.hideCoachMark()} className="btn" href>
                                                Start Picking!
                                                </a>
                                            </div>  
                                        </div>

                                    }


                                </div>
                            </Swipeable>

                        </Modal.Body>
                    </Modal>
                )}
            </MyContext.Consumer>
        )
    }
}

export default PickemCoachMark;
