import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../CustomComponent";
import { DELAY_MSG_HEAD } from "../../helper/Message"
import WSManager from "../../helper/WSManager";
import Images from "../images";
import HF from '../../helper/HelperFunction';

export default class Dfs_MatchAlertMsgModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    render() {
        let { msgModalIsOpen, MsgItems, Message, msgFormValid } = this.props
        return (
            <Modal
                isOpen={msgModalIsOpen}
                className="match-msg-modal"
                toggle={this.props.openMsgModal}
            >
                <ModalHeader>{DELAY_MSG_HEAD}</ModalHeader>
                <ModalBody>
                    {
                        MsgItems.is_tour_game == 1 ? 
                        <Row>
                             <div className="motor-sport-manage-msg">
                                    <div className="motor-sports-container">
                                        
                                        <div className="motor-sports-view">
                                            <img className="img-colum-view" src={NC.S3 + NC.MOTOR_SPORTS_IMG + MsgItems.league_image} alt=""
                                            ></img>
                                            <div className="inner-view-motor-sports">
                                                <div className="tournament-name-view">{MsgItems.tournament_name}</div>
                                                <div className="date-view">{HF.getFormatedDateTime(MsgItems.season_scheduled_date, 'D MMM YYYY hh:mm A')} to {HF.getFormatedDateTime(MsgItems.end_scheduled_date, 'D MMM YYYY hh:mm A')} </div>
                                              <div className="league-name-view"> {MsgItems.league_abbr}</div> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </Row>
                        :
                        <Row className="msg-matchinfo">
                        <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.home_flag}></img>
                        <div className="matchinfo-box">
                            <div className="match-title">{MsgItems.league_abbr}</div>
                            <div className="match-date">
                                {/* <MomentDateComponent data={{ date: MsgItems.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                 */}
                                 {HF.getFormatedDateTime(MsgItems.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                            </div>
                            <div className="match-vs"><b>{MsgItems.home}{' VS '}{MsgItems.away}</b></div>
                        </div>
                        <img className="cardimg" src={NC.S3 + NC.FLAG + MsgItems.away_flag}></img>
                    </Row>
                    }
                    
                    <Row>
                        <Col md={12}>
                            <textarea
                                rows="3"
                                name="Message"
                                className="match-msg"
                                value={Message}
                                onChange={e => this.props.handleInputChange(e)} ></textarea>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <p className="warning-msg">Warning: After your intervention if any update come from feed will not be considered as priority. Your update will be considered as final. For any change you need to update this section again.</p>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="border-0 justify-content-center">
                    <Button
                        disabled={msgFormValid}
                        className="btn-secondary-outline"
                        onClick={() => this.props.updateMatchMsg(1)}
                    >Send</Button>
                    {
                        (MsgItems.custom_message != null) ?
                            MsgItems.custom_message != "" ?
                                <Button
                                    className="btn-secondary-outline"
                                    onClick={() => this.props.updateMatchMsg(2)}
                                >Remove</Button>
                                :
                                ''
                            :
                            ''
                    }
                </ModalFooter>
            </Modal>
        )
    }
}