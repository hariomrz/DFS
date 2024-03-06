import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter } from 'reactstrap';
import { MSG_SUBMIT_PREDICTION_SUB } from "../../helper/Message";
export default class PromptModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render(){
        let { modalActionNo, publishModalOpen, MainMessage, SubMessage, publishPosting, modalActionYes } = this.props
        return (
            <Modal
                isOpen={publishModalOpen}
                className="modal-sm coupon-history prediction-popup"
                toggle={modalActionNo}
            >
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className={`ask-text ${MainMessage.includes("scores") ? 'scores-font' : ''}`}>
                                {MainMessage}
                                {SubMessage && <br />}
                                <span className="sbmsg">{SubMessage && SubMessage}</span></div>
                        </Col>
                    </Row>                    
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button className="btn-secondary-outline ripple no-btn" onClick={modalActionNo}>No</Button>
                    <Button 
                        disabled={publishPosting}
                        onClick={modalActionYes}
                        className={`btn-secondary-outline yes-wd-cls`}>Yes</Button>
                </ModalFooter>
            </Modal>
        )
    }
}