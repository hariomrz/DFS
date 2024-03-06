import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
import { Utilities } from '../../Utilities/Utilities';


class CMStkLdrEqModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 0, 
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('stkeq-ldrCM', 1)
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('stkeq-ldrCM', 1)
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
    }
    render() {
        const { mShow } = this.props.cmData;
        const {sportsList} = this.state;
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
                                    
                                    <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-two" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable onSwiped={this.onSwiped}>
                                <div className={"v-container stk-eq-lb-cm " + (this.state.indexPage == 1 ? ' CM-second' : '')}>
                                    {
                                        this.state.indexPage == 0 &&
                                        <div className="page-one">

                                            <div className="coachmark-heading">{AL.STK_LB_CM_LABEL1}</div>
                                            <div className="coachmark-text">{AL.STK_LB_CM_TEXT1}</div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop rotate-full" />
                                            </div>
                                            <div className="bg-highlighter">
                                                <table>
                                                    <tr>
                                                        <td className="rank-td">1</td>
                                                        <td className="user-name-td">
                                                            <img src={Images.DEFAULT_USER} alt="" />
                                                            <span class="user-ellip">r2@123</span>
                                                            <div class="sub-detail">155Pts |  T1</div>
                                                        </td>
                                                        <td className="prize-td p-0">
                                                            <div>
                                                                <span className="contest-prizes">
                                                                    <span> <img alt='' style={{ marginRight: '2px', marginBottom: '1px' }} src={Images.IC_COIN} width="14px" height="14px" />14</span>
                                                                </span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div className="bottomView">
                                                <div className="preview-bg">
                                                    {/* <div onClick={this.hideCoachMark} className="innerbox-preview"> */}
                                                    <div onClick={this.startPlaying} className="innerbox-preview">
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

export default CMStkLdrEqModal;
