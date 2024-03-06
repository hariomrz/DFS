import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
import { Utilities } from '../../Utilities/Utilities';
import StockFixtureCard from '../StockFantasy/StockFixtureCard';


class CoachMarkStcLobbyEqModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 1, //0, 
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('stkeq-coachmark', 1)
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {
        ls.set('stkeq-coachmark', 1)
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
    // showPreviouPage = (e, index) => {
    //     e.stopPropagation();
    //     this.setState({ indexPage: index })

    // }
    // showNextPage = (e, index) => {
    //     e.stopPropagation();
    //     this.setState({ indexPage: index })


    // }
    circleTransitions = (e, index) => {
        e.stopPropagation();
        this.setState({ indexPage: index })

    }
    onSwiped = (eventData) => {
        if (eventData && eventData.dir === "Left") {
            if (this.state.indexPage == 0 ) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 2 })
            }
        }
        if (eventData && eventData.dir === "Right") {
        //    if (this.state.indexPage == 4) {
        //         this.setState({ indexPage: 3 })
        //     }
        //     else 
            // if (this.state.indexPage == 3) {
            //     this.setState({ indexPage: 2 })
            // }
            // else 
            if (this.state.indexPage == 2) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 0 })
            }
        }
    }
    render() {
        const { mShow } = this.props.cmData;
        const {sportsList} = this.state;
        let stkItem = {'category_id': "1",
        'collection_id': "123",
        'collection_name': "Daily",
        'custom_message': "",
        'end_date': "2021-11-24 10:00:00",
        'prize_type': "1",
        'published_date': "2021-11-23 10:45:00",
        'scheduled_date': "2021-11-24 03:45:00",
        'total_contest': "4",
        'total_prize_pool': "58"
        }

        if (stkItem.scheduled_date) {
            let sDate = new Date(Utilities.getUtcToLocal(stkItem.scheduled_date))
            let game_starts_in = Date.parse(sDate)
            stkItem['game_starts_in'] = game_starts_in;
            stkItem['season_scheduled_date'] = stkItem.scheduled_date;
        }
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
                                    
                                    {/* <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-two" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div> */}
                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-three" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable >
                                {/* onSwiped={this.onSwiped}> */}
                                <div className={"v-container stk-eq-lobby-cm " + (this.state.indexPage == 1 ? ' CM-second' : '')}>
                                    {
                                        this.state.indexPage == 0 &&
                                        <div className="page-two">
                                            <div className="bg-highlighter">
                                                <div class="banner-item refer-banner-item">
                                                    <img alt='' className='banner-logo' src={Images.STK_EQ_LBY_BNR} />
                                                    {/* <div className='info-container'>
                                                        <div className="message-style">{AL.REFER_A_FRIEND_AND_GET}  <span class="highlighted-text">100</span> {AL.on_your_friends_signup}
                                                        </div>
                                                    </div> */}
                                                </div>
                                            </div>

                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop" />
                                            </div>
                                            <div className="coachmark-heading">{AL.STK_LBY_CM_LABEL1}</div>
                                            <div className="coachmark-text">{AL.STK_LBY_CM_TEXT1}</div>

                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10" + (this.state.indexPage == 0 ? ' is-disable' : ' ')}>
                                                    <span style={{ fontSize: 18 }} className="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 1)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            


                                        </div>
                                    }

                                    {
                                        this.state.indexPage == 1 &&
                                        <div className="page-three">
                                            <div className="coachmark-heading">{AL.STK_LBY_CM_LABEL2}</div>
                                            <div className="coachmark-text">{AL.STK_LBY_CM_TEXT2}</div>
                                            {/* <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className="rectangle-left">
                                                    <span style={{ fontSize: 18 }} class="icon-arrow-left"></span>
                                                </div>
                                            </div> */}
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop rotate-full" />
                                            </div>
                                            <div className="bg-highlighter">
                                                <ul class="collection-list-wrapper lobby-anim">
                                                    <StockFixtureCard
                                                        key={stkItem.collection_id}
                                                        data={{
                                                            item: stkItem,
                                                            isFrom: 'LobbyCM',
                                                            btnAction: () => this.playNow(stkItem),
                                                            showHTPModal: (e) => this.showHTPModal(e)
                                                        }}
                                                    />
                                                </ul>
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

export default CoachMarkStcLobbyEqModal;
