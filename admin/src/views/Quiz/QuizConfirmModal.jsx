import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalFooter, ModalHeader } from 'reactstrap';
import { MSG_SUBMIT_PREDICTION_SUB } from "../../helper/Message";
export default class QuizConfirmModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { modalActionHide, modalActionNo, publishModalOpen, MainMessage, SubMessage, publishPosting, modalActionYes, main_class, sub_class, no_text, yes_text, hide_text, } = this.props
        return (
            <Modal
                isOpen={publishModalOpen}
                className="modal-sm qz-delete-mdl"
                toggle={modalActionNo}
            >
                <ModalHeader>Alert</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12}>
                            <div className={main_class}>
                                {MainMessage}
                                {SubMessage && <br />}
                                <span className={sub_class}>{SubMessage && SubMessage}</span></div>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={publishPosting}
                        onClick={modalActionYes}
                        className={`btn-secondary-outline gray-btn`}>
                        {yes_text}
                    </Button>

                    {
                        hide_text == 'Hide' &&
                        <Button
                            disabled={publishPosting}
                            className="btn-secondary-outline ripple gray-btn"
                            onClick={modalActionHide}>
                            {hide_text}
                        </Button>
                    }

                    <Button className="btn-secondary-outline ripple" onClick={modalActionNo}>{no_text}</Button>

                </ModalFooter>
            </Modal>
        )
    }
}