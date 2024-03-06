import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter, Input } from 'reactstrap';
import { MSG_SUBMIT_PREDICTION_SUB } from "../../helper/Message";
import { _isEmpty } from "../../helper/HelperFunction";
export default class CancelReasonModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { modalActionNo, ModalOpen, MainMessage, SubMessage, CancelPosting, modalActionYes } = this.props
        return (
            <Modal
                isOpen={ModalOpen}
                toggle={modalActionNo}
                className="cancel-match-modal"
            >
                {/* <ModalHeader>{API_FLAG == 1 ? CANCEL_GAME_TITLE : CANCEL_CONTEST_TITLE}</ModalHeader> */}
                <ModalBody>
                    <div className="confirm-msg">{MainMessage}</div>
                    {!_isEmpty(SubMessage) && <div className="confirm-msg">{SubMessage}</div>}
                    <div className="inputform-box">
                        <label>Reason</label>
                        <Input
                            minLength="3"
                            maxLength="160"
                            rows={3}
                            type="textarea"
                            name="CancelReason"
                            onChange={(e) => this.props.cancelInputChange(e)}
                        />
                    </div>
                </ModalBody>
                <ModalFooter>
                    <Button
                        color="secondary"
                        onClick={modalActionYes}
                        disabled={CancelPosting}
                    >Yes</Button>{' '}
                    <Button color="primary" onClick={modalActionNo}>No</Button>
                </ModalFooter>
            </Modal>
        )
    }
}