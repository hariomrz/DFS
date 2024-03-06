import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { _isEmpty, _isUndefined } from "../../helper/HelperFunction";

import { DELAY_MSG_HEAD } from "../../helper/Message"
import SP_FixtureCard from './SP_FixtureCard';
export default class SP_MatchAlertMsgModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    render() {
        let { msgModalIsOpen, MsgItems, Message, msgFormValid, activeFxType, activeTab } = this.props        
        let fx_item = {
            scheduled_date: MsgItems.scheduled_date,
            end_date: MsgItems.end_date,
        }

        return (
            <Modal
                isOpen={msgModalIsOpen}
                className="match-msg-modal"
                toggle={this.props.openMsgModal}
            >
                <ModalHeader>{DELAY_MSG_HEAD}</ModalHeader>
                <ModalBody>
                    <Row className="msg-matchinfo">
                        <SP_FixtureCard
                            // key={idx}
                            callfrom={'2'}
                            activeFxTab={activeFxType}
                            activeTab={activeTab}
                            edit={false}
                            item={fx_item}
                            redirectToTemplate={null}
                            redirectToStockReview={null}
                            redirectToUpdateStock={null}
                            openMsgModal={null}
                            openDelayModal={null}
                            show_flag={true}
                        />
                    </Row>
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
                        (!_isUndefined(MsgItems.custom_message) && MsgItems.custom_message != null) ?
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