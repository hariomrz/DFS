import React, { Component } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Modal } from 'react-bootstrap';
import Images from '../../components/images';
import * as AL from "../../helper/AppLabels";
import ls from 'local-storage';
import { Swipeable } from 'react-swipeable';
import { Utilities } from '../../Utilities/Utilities';
import StockFixtureCard from '../StockFantasy/StockFixtureCard';


class CoachMarkSPLobby extends Component {
    constructor(props) {
        super(props)
        this.state = {
            ANMTC: '',
            indexPage: 0,
            sportsList: Utilities.getMasterData().sports_hub
        }
    }

    componentDidMount() {
        ls.set('sp-coachmark', 1)
        setTimeout(() => {
            this.setState({ ANMTC: "animate-v" });
        }, 100);
    }
    hideCoachMark = () => {

 ls.set('sp-coachmark', 1)
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
            if (this.state.indexPage == 0) {
                this.setState({ indexPage: 1 })
            }
            else if (this.state.indexPage == 1) {
                this.setState({ indexPage: 2 })
            }
        }
        if (eventData && eventData.dir === "Right") {
            if (this.state.indexPage == 4) {
                this.setState({ indexPage: 3 })
            }
            else
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
        const { sportsList } = this.state;
        let stkItem = {
            'category_id': "1",
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
                        dialogClassName={"contest-detail-modal prop-coin-coachmark coin-coachmark coachmark-modal fvcghfthgj " + this.state.ANMTC}
                        className="contest-detail-dialog"
                        animation={false}
                    >
                        <Modal.Header >

                            <div className='skip-header-view'>
                                <span style={{ left: 20 }} onClick={this.hideCoachMark} className='skip-close'>{AL.SKIP_STEP}</span>
                                <div className="walkthrough-circle ">

                                    {/* <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-three" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div> */}
                                    {/* upcoming */}
                                    <div onClick={(e) => this.circleTransitions(e, 0)} className={"circle circle-two" + (this.state.indexPage == 0 ? ' selected-page' : '')}></div>

                                    <div onClick={(e) => this.circleTransitions(e, 1)} className={"circle circle-three" + (this.state.indexPage == 1 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 2)} className={"circle circle-three" + (this.state.indexPage == 2 ? ' selected-page' : '')}></div>
                                    <div onClick={(e) => this.circleTransitions(e, 3)} className={"circle circle-three" + (this.state.indexPage == 3 ? ' selected-page' : '')}></div>

                                    <div onClick={(e) => this.circleTransitions(e, 4)} className={"circle circle-three" + (this.state.indexPage == 4 ? ' selected-page' : '')}></div>
                                </div>
                            </div>
                        </Modal.Header>
                        <Modal.Body>
                            <Swipeable className='Swipeable'>
                                {/* onSwiped={this.onSwiped}> */}
                                <div className={"v-container sp-lobby-cm " + (this.state.indexPage == 0 ? ' CM-first' : (this.state.indexPage == 1 ? ' CM-second' : (this.state.indexPage == 2 ? ' CM-third' : (this.state.indexPage == 3 ? 'CM-fourth' :( this.state.indexPage == 4 ? 'CM-fifth' :'')))))}>

                                    {
                                        this.state.indexPage == 0 &&
                                        <div className="page-one">
                                            <div className="bg-highlighter">
                                                <div><i className="icon-question" /> How to Play</div>
                                            </div>
                                            <div className="image-strip-sp">
                                                <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop " />
                                            </div>
                                            <div className="coachmark-text">
                                                <p>{AL.SP_LBY_CM_DESC1}<br /> {AL.SP_LBY_CM_DESC12}</p>
                                            </div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10"+ (this.state.indexPage == 0 ? ' is-disable' : ' ')}>
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
                                        <div className="page-one">
                                            <div className="bg-highlighter">
                                                <div>UPCOMING</div>
                                            </div>
                                            <div className="image-strip-sp">
                                                <img src={Images.DOUBLE_LINE} alt="" className="line-img-prop " />
                                            </div>
                                            <div className="coachmark-text">
                                                <p>{AL.SP_LBY_CM_DESC21} <br /> {AL.SP_LBY_CM_DESC22} <br /> {AL.SP_LBY_CM_DESC23}</p>
                                            </div>
                                            <div className="navigation-view">
                                                <div onClick={(e) => this.showPreviouPage(e, 0)} className={"rectangle-left mr10"+ (this.state.indexPage == 0 ? ' is-disable' : ' ')}>
                                                    <span style={{ fontSize: 18 }} className="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 2)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                        </div>
                                    }

                                    

                                    {
                                        this.state.indexPage == 2 &&
                                        <div className="page-one">
                                            <div className="bg-highlighter">
                                                <span className="sap">
                                                All
                                                </span>
                                                <span className="sap">
                                                Today
                                                </span>
                                                <span className="sap">
                                                Tomorrow
                                                </span>
                                                <i className="icon-filter" />
                                            </div>
                                            <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop " />
                                            </div>
                                            <div className="coachmark-text">
                                                {AL.SP_LBY_CM_DESC31} <br /> {AL.SP_LBY_CM_DESC32}<br /> {AL.SP_LBY_CM_DESC33}</div>


                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 1)} className={"rectangle-left mr10"}>
                                                    <span style={{ fontSize: 18 }} className="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 3)} className="rectangle-right">
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>


                                        </div>
                                    }
                                    {
                                        this.state.indexPage == 3 &&
                                        <div className="page-four">
                                             <div className="bg-highlighter">
                                             <span className='price-dropdown'>₹25 <i className="icon-arrow-down" /></span>
                                             </div>


                                            <span className="image-strip-zigzag">
                                                <i />
                                            </span>
                                            <div className="coachmark-text"><p>{AL.SP_LBY_CM_DESC4}</p></div>
                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 2)} className={"rectangle-left mr10"}>
                                                    <span style={{ fontSize: 18 }} className="icon-arrow-left"></span>
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
                                            <div className="preview-bg">
                                                {/* <a href className="btn btn-rounded-s">START PLAYING</a> */}
                                                <a  onClick={this.hideCoachMark} href className="bg-highlighter">START PLAYING</a>
                                                
                                                <div className="innerbox-preview">

                                                </div>
                                            </div>

                                            <div className="team-preview-text">{AL.SP_LBY_CM_DESC51} <br /> {AL.SP_LBY_CM_DESC52} <br /> {AL.SP_LBY_CM_DESC53}</div>



                                            <div className="navigation-view xnavigation-view-two">
                                                <div onClick={(e) => this.showPreviouPage(e, 3)} className={"rectangle-left mr10"}>
                                                    <span style={{ fontSize: 18 }} className="icon-arrow-left"></span>
                                                </div>
                                                <div onClick={(e) => this.showNextPage(e, 4)} className={"rectangle-right"+ (this.state.indexPage == 4 ? ' is-disable' : ' ')}>
                                                    <div style={{ fontSize: 18 }} className="icon-arrow-right"></div>
                                                </div>

                                            </div>
                                            <div className='zig-down'>
                                        <img alt='' src={Images.ZIG_ZAG_BTM_RGT} />
                                    </div>
                                            <div className='i-feed'>
                                                
                                                <span style={{ fontSize: 18 }} ><i className="icon-fs-social"></i><br></br>Feed</span>
                                            </div>
                                            {/* <div className="image-strip">
                                                <img src={Images.SINGLE_LINE} alt="" className="line-img-prop rotate-full" />
                                            </div> */}
                                            {/* <span className="image-strip-zigzag">
                                                <i />
                                            </span> */}



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

export default CoachMarkSPLobby;
