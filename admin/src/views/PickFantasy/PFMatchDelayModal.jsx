import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import _ from 'lodash';
import HF, { _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
import { DELAY_TIME_MSG_HEAD } from "../../helper/Message"
export default class PFMatchDelayModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    render() {
        let { DelayModalIsOpen, fixtureObjData, delay_hour, delay_minute, delayPosting, delay_message, HourMsg, MinuteMsg, modalActionNo } = this.props
        console.log('fixtureObjData',fixtureObjData)
        return (
            <Modal
                isOpen={DelayModalIsOpen}
                className="match-msg-modal"
                toggle={modalActionNo}
            >
                <ModalHeader>{DELAY_TIME_MSG_HEAD}</ModalHeader>
                <ModalBody>

                    <Row className="msg-matchinfo">
                        <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureObjData.home_flag}></img>
                        <div className="matchinfo-box">
                            <div className="match-title">{fixtureObjData.league_abbr}</div>
                            <div className="match-date">
                                {/* <MomentDateComponent data={{ date: fixtureObjData.scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                {HF.getFormatedDateTime(fixtureObjData.scheduled_date, "D-MMM-YYYY hh:mm A")}
                            </div>
                            <div className="match-vs"><b>{fixtureObjData.home}{' VS '}{fixtureObjData.away}</b></div>
                        </div>
                        <img className="cardimg" src={NC.S3 + NC.FLAG + fixtureObjData.away_flag}></img>
                    </Row>

                    <Row>
                        <Col xs="6">
                            <Input type="number" min="0" max="48" maxLength="2" id="delay_hour" name="delay_hour" placeholder="hh"
                                value={delay_hour}
                                onChange={(e) => this.props.handleFieldVal(e)} required />
                            {
                                HourMsg &&
                                <p className="warning-msg">Hour should be 1 to 47 delay</p>
                            }
                        </Col>
                        <Col xs="6">
                            <Input type="number" min="0" max="59" maxLength="2" id="delay_minute" name="delay_minute" placeholder="mm"
                                value={delay_minute}
                                onChange={(e) => this.props.handleFieldVal(e)} required />
                            {
                                MinuteMsg &&
                                <p className="warning-msg">Minute should be 1 to 59 delay</p>
                            }
                        </Col>
                        <br /><br />
                    </Row>
                    <Row>
                        <Col md={12}>
                            <Input
                                type="textarea"
                                maxLength="160"
                                className="match-msg mt-3"
                                id="delay_message"
                                name="delay_message"
                                placeholder="Enter Delay Message"
                                value={delay_message}
                                onChange={(e) => this.props.handleFieldVal(e)}
                                required resize="0"
                            />

                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <p className="warning-msg">Warning: After your intervention if any update come from feed will not be considered as priority. Your update will be considered as final. For any change you need to update this section again.</p>
                        </Col>
                    </Row>
                    {
                        fixtureObjData.scheduled_date && fixtureObjData.scheduled_date != "" && !_isUndefined(fixtureObjData.new_deadline) &&
                        <Row className="mt-3">
                            <Col xs="12" className="new-deadline">
                                New Deadline :  
                                {/* {console.log(fixtureObjData.new_deadline)} */}
                                {HF.getFormatedDateTime(fixtureObjData.new_deadline, "D-MMM-YYYY hh:mm A")}  
                                {/* {fixtureObjData.new_deadline} */}
                            </Col>
                        </Row>
                    }

                </ModalBody>
                <ModalFooter className="border-0 justify-content-center">
                    <Row>
                        <Button
                            disabled={!delayPosting}
                            className="btn-secondary-outline"
                            onClick={() => this.props.modalActionYes()}
                        >Send</Button>
                    </Row>
                </ModalFooter>
            </Modal>
        )
    }
}