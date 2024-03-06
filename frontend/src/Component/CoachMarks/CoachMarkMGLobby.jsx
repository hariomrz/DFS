import React, { Component, Suspense, lazy } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable'
import { Utilities } from '../../Utilities/Utilities';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));


class MGLobbyCoachMarkModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: Utilities.getMasterData().sports_hub.length == 1 ? (Utilities.getMasterData().a_coin == "1" ? 0 : 1) : 0, 
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('MGLCM', 1)
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('MGLCM', 1)
        if (this.props.cmData.mHide) {
            this.props.cmData.mHide();
        } else {
            this.props.history.push('/');
        }
    }
    startPlaying = () => {
        if (this.props.cmData.mHide) {
            this.props.cmData.mHide();
        } else {
            this.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl());
        }
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
        }
        if (eventData && eventData.dir === "Right") {
            if (this.state.indexPage == 3) {
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
    render() {
        const { mShow } = this.props.cmData;
        const {sportsList} = this.state;
        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '100px 0 0px',
            initialSlide: 0,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '60px 0 10px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '20px 0 10px',
                        afterChange: '',
                    }
                }
            ]
        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"contest-detail-modal prop-coin-coachmark coin-coachmark coachmark-modal " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >

                            <div className='skip-header-view'>
                                <span style={{ left: 20 }} onClick={this.hideCoachMark} className='skip-close'>{AL.SKIP_STEP}</span>
                                <div className="walkthrough-circle ">
                                    {
                                        (sportsList.length != 1 || Utilities.getMasterData().a_coin == "1") &&
                                        <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-one" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
                                    }
                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-two" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container MG-coachmark" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-third' : '')}>
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
                                            <div className="coachmark-heading">{AL.LOBBY_COAC_LABEL4}</div>
                                            <div className="coachmark-text">{AL.LOBBY_COAC_TEXT4}</div>
                                            <div className="navigation-view-four navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10" + ((sportsList.length != 1 || Utilities.getMasterData().a_coin == "1") ? ' ' : ' is-disable')}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 2)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>   
                                            {/* <div className="bottomView">
                                                <div className="preview-bg">
                                                    <div onClick={this.startPlaying} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.LETS_START_PLAYING}</div>
                                                    </div>
                                                </div>
                                            </div>   */}
                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-three">
                                            <div className="coachmark-heading">{AL.MGL_COAC_LABEL3}</div>
                                            <div className="coachmark-text">{AL.MGL_COAC_TEXT3}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 1)} className="rectangle-left">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="bg-highlighter">
                                                <div className="fixture-list-content mb-0">
                                                    <div className="collection-wrap text-left mb-0">
                                                        <div class="collection-header">
                                                            <div class="collection-header-left">
                                                                <h1>Bangldesh 10 Dec</h1>
                                                                <div class="collection-count">
                                                                    <span class="collection-league-name">Bangladesh T20 league</span>
                                                                    <span class="dot-divider"></span>
                                                                    <span class="text-capitalize">1 teams | 1 Contests</span>
                                                                </div>
                                                            </div>
                                                            <div class="collection-list-prize-pool">Winnings&nbsp;
                                                                <span> ₹604</span>
                                                            </div>
                                                        </div>
                                                        <div className="collection-body">
                                                            <div className="col-body-overlay"></div>
                                                            <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                                                <div className="collection-list-slider">
                                                                    <div class="collection-list">
                                                                        <div class="display-table">
                                                                            <div class="display-table-cell text-center v-mid w20">
                                                                                <img src="https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/flag/BDH_1606124258.png" alt="" class="team-img" />
                                                                            </div>
                                                                            <div class="display-table-cell text-center v-mid w-lobby-40">
                                                                                <div class="team-block">
                                                                                    <span class="team-name text-uppercase">BDH</span>
                                                                                    <span class="verses">vs</span>
                                                                                    <span class="team-name text-uppercase">GKH</span>
                                                                                </div>
                                                                                <div class="match-timing">
                                                                                    <span> 
                                                                                        <time datetime="1607585400000">10 Dec - 01:00 PM </time>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="display-table-cell text-center v-mid w20">
                                                                                <img src="https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/flag/GKH_1606124287.png" alt="" class="team-img" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div className="collection-list-slider">
                                                                    <div class="collection-list">
                                                                        <div class="display-table">
                                                                            <div class="display-table-cell text-center v-mid w20">
                                                                                <img src="https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/flag/BDH_1606124258.png" alt="" class="team-img" />
                                                                            </div>
                                                                            <div class="display-table-cell text-center v-mid w-lobby-40">
                                                                                <div class="team-block">
                                                                                    <span class="team-name text-uppercase">BDH</span>
                                                                                    <span class="verses">vs</span>
                                                                                    <span class="team-name text-uppercase">GKH</span>
                                                                                </div>
                                                                                <div class="match-timing">
                                                                                    <span> 
                                                                                        <time datetime="1607585400000">10 Dec - 01:00 PM </time>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="display-table-cell text-center v-mid w20">
                                                                                <img src="https://predev-vinfotech-org.s3.ap-south-1.amazonaws.com/upload/flag/GKH_1606124287.png" alt="" class="team-img" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                </ReactSlickSlider></Suspense>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="bottomView">
                                                <div className="preview-bg">
                                                    {/* <div onClick={this.hideCoachMark} className="innerbox-preview"> */}
                                                    <div onClick={this.startPlaying} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.LETS_START_PLAYING}</div>
                                                    </div>
                                                </div>
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

export default MGLobbyCoachMarkModal;
