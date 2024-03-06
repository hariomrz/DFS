import React, { Component, lazy, Suspense } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
import { Utilities } from '../../Utilities/Utilities';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));


class PredictionCoachMark extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: Utilities.getMasterData().sports_hub.length == 1 ? (Utilities.getMasterData().a_coin == "1" ? 0 : 1) : 0, 
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('coachmark-pred', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('coachmark-pred', 1)
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
            else if (this.state.indexPage == 6) {
                this.setState({ indexPage: 7 })
            }
        }
        if (eventData && eventData.dir === "Right") {
            if (this.state.indexPage == 7) {
                this.setState({ indexPage: 6 })
            }
            else if (this.state.indexPage == 6) {
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

        var settings = {
            infinite: false,
            slidesToShow: 3,
            slidesToScroll: 1,
            variableWidth: false,
            initialSlide: 1,
            className: "center slick-prediction",
            centerMode: false,
            swipeToSlide: true,
            responsive: [
                {
                    breakpoint: 450,
                    settings: {
                        slidesToShow: 2,
                    }
                }
            ],

        };
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
                                    <div onClick={(e) => this.circleTransitions(e, 7)} className={"circle circle-eight" + (this.state.indexPage == 7 ? ' selected-page' : '')}></div>

                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container pickem-coac pred-coac" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-third' : this.state.indexPage == 3 ? ' CM-fourth' : this.state.indexPage == 4 ? ' CM-fifth' : this.state.indexPage == 5 ? ' CM-sixth' : this.state.indexPage == 6 ? ' CM-seventh' : this.state.indexPage == 7 ? ' CM-eight' : '')}>
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
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL2}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT2}</div>
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
                                            <div className="bg-highlighter prediction-wrap-v">
                                            <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                                    <div className="pred-slider">
                                                        <div className="slider-fixture-card fixture-card-wrapper prediction-card-wrapper pointer-cursor">

                                                            <div className="fixture-card-body display-table">
                                                                <div className="match-info-section">
                                                                    <div className="section-left">
                                                                        <img src={Images.TEAM_HYDERABAD} alt="" className="home-team-flag" />
                                                                    </div>
                                                                    <div className="section-middle">
                                                                        <div className="team-n-m">
                                                                            <span className="team-home">HYD</span>
                                                                            <span className="vs-text">{AL.VERSES}</span>
                                                                            <span className="team-away">KOL</span>
                                                                        </div>
                                                                        <div className="countdown time-line">
                                                                            08 : 00 : 00
                                                                        </div>
                                                                    </div>
                                                                    <div className="section-right">
                                                                        <img src={Images.TEAM_KOLKATA} alt="" className="away-team-flag" />
                                                                    </div>
                                                                </div>
                                                                <div className="match-timing league-n">
                                                                    <div className="leag-name">IND v ENG Series..  - </div>
                                                                    <div> - ODI</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="pred-slider">
                                                        <div className="slider-fixture-card fixture-card-wrapper prediction-card-wrapper pointer-cursor">

                                                            <div className="fixture-card-body display-table">
                                                                <div className="match-info-section">
                                                                    <div className="section-left">
                                                                        <img src={Images.TEAM_HYDERABAD} alt="" className="home-team-flag" />
                                                                    </div>
                                                                    <div className="section-middle">
                                                                        <div className="team-n-m">
                                                                            <span className="team-home">HYD</span>
                                                                            <span className="vs-text">{AL.VERSES}</span>
                                                                            <span className="team-away">KOL</span>
                                                                        </div>
                                                                        <div className="countdown time-line">
                                                                            08 : 00 : 00
                                                                        </div>
                                                                    </div>
                                                                    <div className="section-right">
                                                                        <img src={Images.TEAM_KOLKATA} alt="" className="away-team-flag" />
                                                                    </div>
                                                                </div>
                                                                <div className="match-timing league-n">
                                                                    <div className="leag-name">IND v ENG Series..  - </div>
                                                                    <div> - ODI</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="pred-slider">
                                                        <div className="slider-fixture-card fixture-card-wrapper prediction-card-wrapper pointer-cursor">

                                                            <div className="fixture-card-body display-table">
                                                                <div className="match-info-section">
                                                                    <div className="section-left">
                                                                        <img src={Images.TEAM_HYDERABAD} alt="" className="home-team-flag" />
                                                                    </div>
                                                                    <div className="section-middle">
                                                                        <div className="team-n-m">
                                                                            <span className="team-home">HYD</span>
                                                                            <span className="vs-text">{AL.VERSES}</span>
                                                                            <span className="team-away">KOL</span>
                                                                        </div>
                                                                        <div className="countdown time-line">
                                                                            08 : 00 : 00
                                                                        </div>
                                                                    </div>
                                                                    <div className="section-right">
                                                                        <img src={Images.TEAM_KOLKATA} alt="" className="away-team-flag" />
                                                                    </div>
                                                                </div>
                                                                <div className="match-timing league-n">
                                                                    <div className="leag-name">IND v ENG Series..  - </div>
                                                                    <div> - ODI</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </ReactSlickSlider></Suspense>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL3}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT3}</div>


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
                                                    <a href className="btn btn-rounded">How to Predict?</a>
                                                </div>
                                                <div className="image-strip">
                                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                                </div>
                                            </div>
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL4}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT4}</div>
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
                                                    <a href className="btn btn-rounded">Earn Coins</a>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                            </div>
                                            <div className="float-right">
                                                <div className="coachmark-heading">{AL.PRD_COAC_LABEL5}</div>
                                                <div className="coachmark-text">{AL.PRD_COAC_TEXT5}</div>
                                                <div className="navigation-view">
                                                    <div onClick={(e) => this.showPreviouPage(e, 3)} className="rectangle-left mr10">
                                                        <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                    </div>
                                                    <div onClick={(e) => this.showNextPage(e, 5)} className="rectangle-right">
                                                        <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    }
                                    {
                                        this.state.indexPage == 5 &&
                                        <div className="page-five">
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter">
                                                    <div className="prediction-wrap-v">
                                                        <div className="p_view-container">
                                                            <ul className="list-pred new-list-pred mb-0 p-0">
                                                                <li className="mb-0">
                                                                    <i className="icon-share" />
                                                                    <p className="questions">Which team will win the match? </p>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '70%', animationDelay: (0.05 * 1) + 's' }} />
                                                                        <p className="answer">India</p>
                                                                        <div className="corrected-ans">
                                                                            <p>70%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '30%', animationDelay: (0.05 * 2) + 's' }} />
                                                                        <p className="answer">England</p>
                                                                        <div className="corrected-ans">
                                                                            <p>300%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '0%', animationDelay: (0.05 * 3) + 's' }} />
                                                                        <p className="answer">Tie / No Result</p>
                                                                        <div className="corrected-ans">
                                                                            <p></p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="footer-vc">
                                                                        <div>
                                                                            <div className="date-v new-fc">
                                                                                <div className="match-timing">
                                                                                    <span className="d-flex">
                                                                                        <i className="icon-stop-watch"></i>
                                                                                        <div className="countdown time-line">
                                                                                            02 : 30 : 59
                                                                                        </div>
                                                                                        {/* {AL.REMAINING} */}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p className="price-pool">
                                                                            <span className="price-pool-first">{AL.WIN}</span>
                                                                            <img src={Images.IC_COIN} alt="" />10,000
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                            </div>
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL6}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT6}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 4)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 6)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>
                                        </div>

                                    }
                                    {
                                        this.state.indexPage == 6 &&
                                        <div className="page-five">
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter bg-highlighter-2">
                                                <i className="icon-stop-watch"></i> 02 : 30 : 59
                                                </div>
                                                <div className="bg-highlighter shadow-none">
                                                    <div className="overlay-highlighter"></div>
                                                    <div className="prediction-wrap-v">
                                                        <div className="p_view-container">
                                                            <ul className="list-pred new-list-pred mb-0 p-0">
                                                                <li className="mb-0">
                                                                    <i className="icon-share" />
                                                                    <p className="questions">Which team will win the match? </p>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '70%', animationDelay: (0.05 * 1) + 's' }} />
                                                                        <p className="answer">India</p>
                                                                        <div className="corrected-ans">
                                                                            <p>70%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '30%', animationDelay: (0.05 * 2) + 's' }} />
                                                                        <p className="answer">England</p>
                                                                        <div className="corrected-ans">
                                                                            <p>30%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '0%', animationDelay: (0.05 * 3) + 's' }} />
                                                                        <p className="answer">Tie / No Result</p>
                                                                        <div className="corrected-ans">
                                                                            <p></p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="footer-vc">
                                                                        <div>
                                                                            <div className="date-v new-fc">
                                                                                <div className="match-timing">
                                                                                    <span className="d-flex">
                                                                                        <i className="icon-stop-watch"></i>
                                                                                        <div className="countdown time-line">
                                                                                            02 : 30 : 59
                                                                                        </div>
                                                                                        {/* {AL.REMAINING} */}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p className="price-pool">
                                                                            <span className="price-pool-first">{AL.WIN}</span>
                                                                            <img src={Images.IC_COIN} alt="" />10,000
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                                <div className="img2-wrap">
                                                    <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five img2 " />
                                                </div>
                                            </div>
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL7}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT7}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 5)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 7)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>
                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 7 &&
                                        <div className="page-five">                                           
                                            <div className="coachmark-heading">{AL.PRD_COAC_LABEL8}</div>
                                            <div className="coachmark-text">{AL.PRD_COAC_TEXT8}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 6)} className="rectangle-left ">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five " />
                                                <div className="img2-wrap">
                                                    <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five img2 " />
                                                </div>
                                            </div>
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter bg-highlighter-2">
                                                    <i className="icon-share" />
                                                </div>
                                                <div className="bg-highlighter shadow-none">
                                                    <div className="overlay-highlighter"></div>
                                                    <div className="prediction-wrap-v">
                                                        <div className="p_view-container">
                                                            <ul className="list-pred new-list-pred mb-0 p-0">
                                                                <li className="mb-0">
                                                                    <i className="icon-share" />
                                                                    <p className="questions">Which team will win the match? </p>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '70%', animationDelay: (0.05 * 1) + 's' }} />
                                                                        <p className="answer">India</p>
                                                                        <div className="corrected-ans">
                                                                            <p>70%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '30%', animationDelay: (0.05 * 2) + 's' }} />
                                                                        <p className="answer">England</p>
                                                                        <div className="corrected-ans">
                                                                            <p>30%</p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="prediction-bar" >
                                                                        <div className="filled-bar" style={{ width: '0%', animationDelay: (0.05 * 3) + 's' }} />
                                                                        <p className="answer">Tie / No Result</p>
                                                                        <div className="corrected-ans">
                                                                            <p></p>
                                                                        </div>
                                                                    </div>
                                                                    <div className="footer-vc">
                                                                        <div>
                                                                            <div className="date-v new-fc">
                                                                                <div className="match-timing">
                                                                                    <span className="d-flex">
                                                                                        <i className="icon-stop-watch"></i>
                                                                                        <div className="countdown time-line">
                                                                                            02 : 30 : 59
                                                                                        </div>
                                                                                        {/* {AL.REMAINING} */}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <p className="price-pool">
                                                                            <span className="price-pool-first">{AL.WIN}</span>
                                                                            <img src={Images.IC_COIN} alt="" />10,000
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="bottomView">
                                                {/* <div onClick={this.hideCoachMark} className="innerbox-preview"> */}
                                                <a onClick={()=>this.hideCoachMark()} className="btn" href>
                                                    Predict Now!
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

export default PredictionCoachMark;
