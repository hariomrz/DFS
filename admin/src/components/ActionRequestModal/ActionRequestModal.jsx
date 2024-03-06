import React, { Component } from "react";
import { Button, Modal, ModalBody, ModalHeader, ModalFooter } from 'reactstrap';
import Images from "../images";
class ActionRequestModal extends Component {
    constructor(props) {
        super(props)
    }

    render() {
        let { Message, ActionPopupOpen, Screen, posting } = this.props
        return (
            <React.Fragment>
                <Modal
                    isOpen={ActionPopupOpen}
                    className="modal-sm action-request"
                    toggle={() => this.props.modalCallback()}
                >
                    <ModalHeader>
                        <img src={Images.ERROR_ICON} alt="" />
                    </ModalHeader>
                    <ModalBody>
                        <span className="info-text">{Message}</span>
                    </ModalBody>
                    <ModalFooter className="request-footer">
                        <Button className="btn-secondary-outline ripple no-btn" onClick={this.props.modalCallback}>No</Button>
                        <Button
                            disabled={posting}
                            className="btn-secondary-outline ripple"
                            onClick={() => Screen == 'Report' ? this.props.modalReportActionCallback()
                                :
                                Screen == 'Approve' ?
                                    this.props.modalUpdatePendingCallback()
                                    :
                                    this.props.modalActioCallback()}>Yes</Button>
                    </ModalFooter>
                </Modal>
            </React.Fragment>
        )
    }
}
export default ActionRequestModal