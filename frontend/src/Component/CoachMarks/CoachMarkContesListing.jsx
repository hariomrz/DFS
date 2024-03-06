import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal,Button, ProgressBar } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable'
import {DARK_THEME_ENABLE,OnlyCoinsFlow} from "../../helper/Constants";
import { Utilities } from '../../Utilities/Utilities';


class ContestListingCoachMarkModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 0

        }
    }

    componentDidMount() {
        ls.set('cl-coachmark', 1)

        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('cl-coachmark', 1);
        this.props.cmData.mHide();
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
        }
        if (eventData && eventData.dir === "Right") {
        //    if (this.state.indexPage == 4) {
        //         this.setState({ indexPage: 3 })
        //     }
        //     else 
            if (this.state.indexPage == 3) {
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
                                    <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-one" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-two" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 3)} className={"circle circle-four" + (this.state.indexPage == 3 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 4)} className={"circle circle-four" + (this.state.indexPage == 4 ? ' selected-page' : '')}></div>
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container cl-coach-mark" + (this.state.indexPage == 1 ? ' CM-second' :
                                    this.state.indexPage == 2 ? ' CM-third' : this.state.indexPage == 3 ? ' CM-fourth' : this.state.indexPage == 4 ? ' CM-fifth' : '')}>
                                    {
                                        this.state.indexPage == 0 &&
                                        <div>
                                            <div className="bg-highlighter">
                                                
                                            <div class="contest-list-header">
                                                <div class="contest-heading">
                                                    <span className="multi-text contest-type-text">{AL.MULTI}</span>
                                                    <span className="gau-text contest-type-text">{AL.GUARANTEED}</span>
                                                    <h3 class="win-type">
                                                        <span>
                                                            <span class="prize-pool-text text-capitalize">Win </span>
                                                            <span>Prizes</span>
                                                        </span>
                                                        <i class="icon-share"></i>
                                                    </h3>
                                                    <div class="text-small-italic mt3x">
                                                        <span>Use 10% Bonus cash</span>
                                                    </div>
                                                </div>
                                                <div class="display-table">
                                                    <div className="progress-bar-default display-table-cell v-mid">
                                                        <ProgressBar now={85} />
                                                        <div className="progress-bar-value" >
                                                            <span className="user-joined">85</span><span className="total-entries"> / 200 {AL.ENTRIES}</span>
                                                            <span className="min-entries">{AL.MIN} 50</span>
                                                        </div>
                                                    </div>
                                                    <div class="display-table-cell v-mid position-relative entry-criteria">
                                                        <Button className="white-base btnStyle btn-rounded" bsStyle="primary">
                                                            {
                                                                (OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2) ?
                                                                <span>
                                                                    <img className="coin-img" src={Images.IC_COIN} alt="" />
                                                                </span>
                                                                :
                                                                <span>₹</span>
                                                            }
                                                            399
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>

                                            </div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.CL_COAC_LABEL1}</div>
                                            <div className="coachmark-text">{AL.CL_COAC_TEXT1}</div>
                                            <div className={"navigation-view"}>
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10" + (this.state.indexPage == 0 ? ' is-disable' : ' ')}>
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 1)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">
                                                <div className="contest-btn-wrap">
                                                    <a href className="btn btnStyle btn-rounded small">
                                                        <span className="text-uppercase">{AL.CREATE_PRIVATE_CONTEST}</span>
                                                    </a>
                                                </div>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.CL_COAC_LABEL2}</div>
                                            <div className="coachmark-text">{AL.CL_COAC_TEXT2}</div>

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
                                                <ul className="nav-coach">
                                                    <li className="active">
                                                        {AL.ALL_CONTEST}
                                                    </li>
                                                    <li>
                                                       {AL.MY_CONTEST} (3)
                                                    </li>
                                                    <li>
                                                        {AL.MY_TEAMS} (5)
                                                    </li>
                                                </ul>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.CL_COAC_LABEL3}</div>
                                            <div className="coachmark-text">{AL.CL_COAC_TEXT3}</div>  

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
                                             <div className="coachmark-heading">{AL.CL_COAC_LABEL4}</div>
                                            <div className="coachmark-text">{AL.CL_COAC_TEXT4}</div>
                                            <div className="navigation-view-four navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 2)} className="rectangle-left mr10">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showPreviouPage(e, 4)} className={"rectangle-right"}>
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>
                                            </div>   
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="bg-highlighter">
                                                <Button type="button" className="btn-block btn-primary bottom btn btn-default">{AL.CREATE_YOUR_TEAM}</Button>
                                            </div>
                                           

                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 4 &&
                                        <div className="page-five">  
                                                                                  
                                            <div className="hight-wrap">
                                                <div className="bg-highlighter">
                                                    <a class="fantasy-rules-sec"><i class="icon-file"></i>{AL.RULES}</a>
                                                </div>
                                                <div className="image-strip resp-view">
                                                    <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop" />
                                                </div>
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop hor-line web-view" />
                                                <div className="image-strip web-view">
                                                    <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                                </div>
                                            </div>      
                                            <div className="coachmark-heading">{AL.CL_COAC_LABEL5}</div>
                                            <div className="coachmark-text">{AL.CL_COAC_TEXT5}</div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 3)} className="rectangle-left">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                            </div>  
                                            <div className="bottomView">
                                                <div className="preview-bg">
                                                    <div onClick={this.startPlaying} className="innerbox-preview">
                                                        <div className="btn">{AL.JOIN_NOW}</div>
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

export default ContestListingCoachMarkModal;
