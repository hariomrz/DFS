import React, { lazy, Suspense } from "react";

import {  Tabs, Tab } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import {Utilities, _Map} from "../../Utilities/Utilities";
import * as AppLabels from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import { getUserTeamStats} from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import Images from "../../components/images";

const ReactSlickSlider = lazy(() => import('../../Component/CustomComponent/ReactSlickSlider'));

export default class LFLeaderBoardUserModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            ballDetailList:[]
        };

    }

    componentDidMount() {
        this.getUserMatchStatus()

    }
    
    getUserMatchStatus = async () => {
        //  if (this.state.LobyyData.home) {
        let param = {
            "user_team_id": this.props.teamItem.user_team_id
        }
        getUserTeamStats(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({ballDetailList: responseJson.data }, () => {
                    //this.parseHistoryStateData();

                })
            }
        })

    }
   

    render() {
        const { ballDetailList} = this.state;

        const { MShow,MHide,teamItem,contestItem } = this.props;
        var settings = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: ballDetailList && ballDetailList.length,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: false,
            autoplaySpeed: 5000,
            centerMode: false,
            centerPadding: "13px",
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "13px",
                    }

                }
            ]
        };
        return (
            <MyContext.Consumer>
                {(context) => (
                    <Modal
                        show={MShow}
                        onHide={MHide}
                        dialogClassName="custom-modal lead-detail-modal header-circular-modal overflow-hidden"
                        className="center-modal"
                    //dialogClassName="custom-modal thank-you-modal confirmation-modal"
                    >
                        <Modal.Header >
                            <div className="modal-img-wrap">
                                <div className="wrap lf">
                                    {/* <i className="icon-note"></i>    */}
                                    <img style={{height:57,width:57,borderRadius:50}} src={teamItem.image ? Utilities.getThumbURL(teamItem.image) : Images.DEFAULT_AVATAR} alt=""  />

                                </div>
                            </div>
                            {teamItem.user_name}
                        </Modal.Header>

                        <Modal.Body >
                            <React.Fragment>
                                <div className="webcontainer-inner mt-0">
                                    <div style={{marginBottom:20}} className="lf-center-header">
                                        <div className='text-over'>{AppLabels.OVER} {" "}{this.props.contestItem.overs}</div>

                                        <div className="ball-sec">
                                            <div className="lft-label-sec">
                                                <div className="vrl"></div>
                                                <div className="lbl">{AL.PTS}</div>
                                            </div>
                                            {
                                                ballDetailList && ballDetailList.length > 0 &&
                                                <>
                                                    {

                                                        <Suspense fallback={<div />} ><ReactSlickSlider ref={slider => (this.slider = slider)} settings={settings}>
                                                            {
                                                                ballDetailList.map((item, idx) => {
                                                                    return (

                                                                        <div className={`ball-wrap `}>
                                                                            {
                                                                                (item.predict_id != '' && item.result != 0) || item.result > 0 ?
                                                                                    <div>
                                                                                        <span className={`ball ${item.is_correct == 1 && item.result > 0 ? " success " : item.is_correct == 2 && item.result > 0 ? " danger " : ''}`}>{item.btext != undefined && item.btext != '' ? item.btext : item.score}</span>
                                                                                        <span className={`${item.is_correct == 1 && item.result > 0 ? ' success' : ' '}`}>{(item.is_correct == 2 && item.result > 0) ? '--' : item.points && item.points != '' && Utilities.getExactValue(parseFloat(item.points))}</span>
                                                                                    </div>

                                                                                    :
                                                                                    <i className={`icon-game-ball icon-ball-status ${item.active && item.active == 1 ? ' active' : item.predict_id == '' && item.market_id != 0 ? " " : ''}`}></i>



                                                                            }
                                                                        </div>
                                                                    )
                                                                })
                                                            }
                                                        </ReactSlickSlider></Suspense>
                                                    }
                                                </>
                                            }
                                        </div>

                                    </div>
                                    <div className='bottom-conatiner'> 
                                    <span className='span-item'>{AppLabels.TOTAL} {" "} {AppLabels.Pts}</span>
                                    {" "}{teamItem.total_score}
                                    </div>
                                </div>
                            </React.Fragment>
                        </Modal.Body>
                        
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}