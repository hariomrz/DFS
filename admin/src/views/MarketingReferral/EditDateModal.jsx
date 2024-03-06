import React, { Component } from "react";
import { Row, Col, Button, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import SelectDate from "../../components/SelectDate";
export default class EditDateModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { modal_action_no, modal_open, btn_posting, modal_action_yes, head_msg, body_msg, label_text, btn_text, class_name, date_props } = this.props
        return (
            <Modal
                isOpen={modal_open}
                toggle={modal_action_no}
                className={"edit-date-mdl modal-sm " + class_name}>
                <ModalHeader>{head_msg}</ModalHeader>
                <ModalBody>
                    <Row>
                        <Col md={12} className="pc-body-text">{body_msg}</Col>
                        <Col md={12} className="position-relative">
                            <label>{label_text}</label>
                            <Row>
                                <Col md={12}>
                                    <label className="w-100">
                                        <SelectDate DateProps={date_props} />
                                        <i className="icon-calender"></i>
                                    </label>
                                </Col>
                            </Row>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter>
                    <Button
                        disabled={btn_posting}
                        className="btn-secondary-outline"
                        onClick={modal_action_yes}>{btn_text}</Button>
                </ModalFooter>
            </Modal>
        )
    }
}