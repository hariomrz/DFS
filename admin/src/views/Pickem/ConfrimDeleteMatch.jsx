import React, { Component } from "react";
import { notify } from "react-notify-toast";
import { Row, Col, Modal, ModalBody, ModalFooter, Button } from 'reactstrap';
import { _Map } from "../../helper/HelperFunction";
import * as NC from "../../helper/NetworkingConstants";
import { submitQaPickem } from "../../helper/WSCalling";

export class ConfrimDeleteMatch extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
        }
    }
    render() {
        let {deleteMatchConfirm, upList, deleteMatchConfirmClose } = this.props;
        return (
            <Modal
                isOpen={deleteMatchConfirm}
                className="cancel-tour-modal"
            >
                <ModalBody>
                    <Row>
                        <Col md={12} className="cancel-wrap">
                            <p className='cancel-heading mb-0'>Are you sure you want to delete this Fixture?</p>
                            <p className='cancel-heading-undo mb-0'>(You cannot undo this action)</p>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer mt-5">
                    <Button className="btn-secondary-outline"
                        onClick={() => this.props.deleteMatchConfirmClose()}>
                        No
                    </Button>
                    <Button 
                        // disabled={this.state.cancel_reason == ''}
                        onClick={() => this.props.deleteMatchFitures()}
                        className={`btn-secondary-outline yes-wd-cls`}>
                        Yes
                    </Button>
                </ModalFooter>
        </Modal>
        )
    }
}

export default ConfrimDeleteMatch
