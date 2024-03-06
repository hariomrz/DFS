import React, { Component, lazy, Suspense } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
const ReactSlickSlider = lazy(()=>import('../CustomComponent/ReactSlickSlider'));


class MGRosterCoachMarkModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 0

        }
    }

    componentDidMount() {
        ls.set('MGRC', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('MGRC', 1)
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
            if (this.state.indexPage == 0) {
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
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 0 })
            }
        }
    }
    render() {
        const { mShow } = this.props.cmData;
        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            centerPadding: '100px 0 5px',
            initialSlide: 0,
            // variableWidth: true,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '60px 0 10px',
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
                    breakpoint: 360,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '40px 0 5px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '10px 0 5px',
                    }
                }
            ]
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
                                    <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-one" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
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
                                <div className={"v-container roster-coachmark MGR-coachmark" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-third' : this.state.indexPage == 3 ? ' CM-fourth' : this.state.indexPage == 4 ? ' CM-fifth' : this.state.indexPage == 5 ? ' CM-sixth' : this.state.indexPage == 6 ? ' CM-seventh' : '')}>
                                    {
                                        this.state.indexPage == 0 &&
                                        <div className="page-one">
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL1}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT1}</div>
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

                                            <div className="bg-highlighter">
                                                <button class="btn btn-primary btn-block btm-fix-btn team-stats"><i class="icon-stats-ic"></i> {AL.TEAM_STATS}</button>
                                            </div>
                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">

                                                <div className="roster-player-detail" style={{ display: 'flex' }}>
                                                    <div class="roster-player-image">
                                                        <img src={Images.TEAM_JERSY} alt=""/>
                                                    </div>
                                                    <div class="roster-player-content">
                                                        <h4><a>B Mooney</a></h4>
                                                        <div className="text-left">
                                                            <span class="roster-player-team">PER </span>
                                                            <span class="roster-player-team float-right" style={{marginTop: 2}}>WI </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL2}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT2}</div>

                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className="xrectangle-left-two rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 2)} className="xrectangle-right-two rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            


                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-three">
                                            <div className="bg-highlighter">
                                                <ul className="tab-coach-sec">
                                                    <li>
                                                        WK <span>(1)</span>
                                                    </li>
                                                    <li className="active">
                                                        BAT <span>(2)</span>
                                                    </li>
                                                    <li>
                                                        AR <span>(1)</span>
                                                    </li>
                                                    <li>
                                                        BOW <span>(0)</span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL3}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT3}</div>


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
                                                    <a class="btn-roster-action "><i class="icon-plus"></i></a>
                                                </div>
                                                <div className="image-strip resp-view">
                                                    <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop" />
                                                </div>
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop hor-line web-view" />
                                                <div className="image-strip web-view">
                                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                                </div>
                                            </div>
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL4}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT4}</div>
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
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL5}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT5}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 3)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 5)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five rotate-full" />
                                            </div>
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter">
                                                    <button class="btn btn-primary btn-block btm-fix-btn team-preview">{AL.TEAM_PREVIEW}</button>
                                                </div>
                                            </div>                                            
                                        </div>

                                    }
                                    {
                                        this.state.indexPage == 5 &&
                                        <div className="page-six">   
                                            {/* <div className="TopView">
                                                <div className="preview-bg">
                                                    <div onClick={this.hideCoachMark} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.START_CREATING_TEAM}</div>
                                                    </div>
                                                </div>
                                            </div> */}
                                            <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL6}</div>
                                            <div className="coachmark-text">{AL.ROSTER_COACH_TEXT6}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 4)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 6)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five rotate-full" />
                                            </div>
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter">
                                                    <button class="btn disabled btn-primary btn-block btm-fix-btn team-preview">{AL.NEXT}</button>
                                                </div>
                                            </div>
                                        </div>

                                    }
                                    {
                                        this.state.indexPage == 6 &&
                                        <div className="page-six">   
                                            <div className="highlighter-wrap">
                                                <div className="bg-highlighter">
                                                    <div className="roster-header ">
                                                        <div className="whole-team-info new-sec">
                                                            <div className="collection-slider-wrapper-roster">
                                                                <div className="contest-collection-slider fixture-list-content contest-collection-slider-roster">
                                                                <Suspense fallback={<div />} ><ReactSlickSlider settings = {settings}>
                                                                        <div class="collection-list-slider">
                                                                            <div class="collection-list">
                                                                                <div class="display-table ">
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_HYDERABAD} alt="" class="team-img" />
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w-lobby-40">
                                                                                        <div class="team-block">
                                                                                            <span class="team-name text-uppercase">BDH</span>
                                                                                            <span class="verses">vs</span>
                                                                                            <span class="team-name text-uppercase">GKH</span>
                                                                                        </div>
                                                                                        <div class="match-timing">
                                                                                            <span><time datetime="1607585400000">10 Dec - 01:00 PM </time></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_KOLKATA} alt="" class="team-img" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="collection-list-slider">
                                                                            <div class="collection-list">
                                                                                <div class="display-table ">
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_HYDERABAD} alt="" class="team-img" />
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w-lobby-40">
                                                                                        <div class="team-block">
                                                                                            <span class="team-name text-uppercase">BDH</span>
                                                                                            <span class="verses">vs</span>
                                                                                            <span class="team-name text-uppercase">GKH</span>
                                                                                        </div>
                                                                                        <div class="match-timing">
                                                                                            <span><time datetime="1607585400000">10 Dec - 01:00 PM </time></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_KOLKATA} alt="" class="team-img" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="collection-list-slider">
                                                                            <div class="collection-list">
                                                                                <div class="display-table ">
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_HYDERABAD} alt="" class="team-img" />
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w-lobby-40">
                                                                                        <div class="team-block">
                                                                                            <span class="team-name text-uppercase">BDH</span>
                                                                                            <span class="verses">vs</span>
                                                                                            <span class="team-name text-uppercase">GKH</span>
                                                                                        </div>
                                                                                        <div class="match-timing">
                                                                                            <span><time datetime="1607585400000">10 Dec - 01:00 PM </time></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="display-table-cell text-center v-mid w20">
                                                                                        <img src={Images.TEAM_KOLKATA} alt="" class="team-img" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        </ReactSlickSlider></Suspense>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div className="image-strip">
                                                <img style={{ fontSize: 18 }} src={Images.SINGLE_LINE} alt="" className="line-img-prop-five" />
                                            </div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 5)} className="rectangle-left">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                            </div>
                                            <div className="coachmark-heading">{AL.MGR_COAC_LABEL7}</div>
                                            <div className="coachmark-text">{AL.MGR_COAC_TEXT7}</div>
                                            <div className="bottomView">
                                                <div className="preview-bg">
                                                    <div onClick={this.hideCoachMark} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.START_CREATING_TEAM}</div>
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

export default MGRosterCoachMarkModal;
