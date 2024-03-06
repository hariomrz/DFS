import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';


class CMSPMyContest extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 1,
            showStats: false
        }
    }

    componentDidMount() {
        ls.set('stkPMC', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('stkPMC', 1)
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
            // else if (this.state.indexPage == 2) {
            //     this.setState({ indexPage: 3 })
            // }
        }
        if (eventData && eventData.dir === "Right") {
            // if (this.state.indexPage == 3) {
            //     this.setState({ indexPage: 2 })
            // }
            // else 
            if (this.state.indexPage == 2) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.showStats && this.state.indexPage == 1) {
                this.setState({ indexPage: 0 })
            }
        }
    }
    render() {
        const { mShow } = this.props.cmData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={mShow}
                        dialogClassName={"contest-detail-modal prop-coin-coachmark coin-coachmark xwallet-coins-coachmark coachmark-modal spcm-mycontest " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >

                            <div className='skip-header-view'>

                                {/* <a style={{ left: 20, right: 'auto' }} href onClick={this.hideCoachMark} className="modal-close">
                            </a> */}
                                <span style={{ left: 20 }} onClick={this.hideCoachMark} className='skip-close'>{AL.SKIP_STEP}</span>

                                <div className="walkthrough-circle ">
                                   <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-two" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 3)} className={"circle circle-one" + (this.state.indexPage == 3 ? ' selected-page' : '')}></div>

                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container roster-coachmark" + (this.state.indexPage == 1 ? ' CM-third' :
                                    this.state.indexPage == 2 ? ' CM-fourth' : this.state.indexPage == 3 ? ' CM-second' : '')}>
                                    
                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-three">
                                            <div className="bg-highlighter">
                                                <ul className="tab-coach-sec">
                                                    <li className="active">
                                                        Upcoming
                                                    </li>
                                                    <li>
                                                        <span></span> Live 
                                                    </li>
                                                    <li>
                                                        Completed
                                                    </li>
                                                </ul>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            {/* <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL3}</div> */}
                                            <div className="coachmark-text">{AL.SP_MYC_CM_DESC1}</div>


                                            <div className="navigation-view">
                                                <div className="rectangle-left mr10  is-disable">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showPreviouPage(e, 2)} className={"rectangle-right"}>
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>

                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-four">
                                            <div className="hight-wrap">
                                                <div className="bg-highlighter">
                                                    <i className="icon-edit-line"></i>
                                                    <i className="icon-arrow-down"></i>
                                                </div>
                                                <div className="image-strip resp-view">
                                                    <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop" />
                                                </div>
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop hor-line web-view" />
                                                <div className="image-strip web-view">
                                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                                </div>
                                            </div>
                                            {/* <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL4}</div> */}
                                            <div className="coachmark-text">{AL.SP_MYC_CM_DESC2}</div>
                                            <div className="navigation-view-four navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 1)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 3)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div> 
                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 3 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">
                                                Portfolio 1
                                            </div>
                                            <div className='line-img-prop hor-line block'></div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            {/* <div className="coachmark-heading">{AL.ROSTER_COACH_LABEL2}</div> */}
                                            <div className="coachmark-text">{AL.SP_MYC_CM_DESC3}</div>

                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 2)} className={"xrectangle-left-two rectangle-left mr10"}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div className="xrectangle-right-two rectangle-right is-disable">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            
                                            <div className="TopView">
                                                <div className="preview-bg">
                                                    <div onClick={this.hideCoachMark} className="innerbox-preview">
                                                        <div className="team-preview-text">{AL.GOT_IT}</div>
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

export default CMSPMyContest;
