import React, { Component } from "react";
import { notify } from "react-notify-toast";
import { Row, Col, Modal, ModalBody, ModalFooter, Button } from 'reactstrap';
import { _Map } from "../../helper/HelperFunction";
import * as NC from "../../helper/NetworkingConstants";
import { pickemMarkCompleted } from "../../helper/WSCalling";

export default class PickemMarkCompletedModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
        }
    }


    markAsCompleted = () => {
        const { tourna_id } = this.props;
        let param = { tournament_id: tourna_id ? tourna_id : '' }
        pickemMarkCompleted(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.props.closePickemMarkCompletedModal();
            }
            else {
                notify.show(Response.message, 'error', 3000)
                this.props.closePickemMarkCompletedModal();
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 3000)

        })
    }


    render() {
        let { markComplete } = this.props;
        return (
            <Modal
                isOpen={markComplete}
                className="cancel-tour-modal"
            >
                <ModalBody>
                    <Row>
                        <Col md={12} className="cancel-wrap">
                            <p className='cancel-heading mb-0'>Are you sure you want to declare this result?</p>
                            <p className='cancel-heading-undo mb-0'>(You cannot undo this action)</p>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer mt-5">
                    <Button className="btn-secondary-outline"
                        onClick={() => this.props.closePickemMarkCompletedModal()}>
                        No
                    </Button>
                    <Button
                        disabled={this.state.cancel_reason == ''}
                        onClick={() => this.markAsCompleted()}
                        className={`btn-secondary-outline yes-wd-cls`}>
                        Yes
                    </Button>
                </ModalFooter>
            </Modal>
        )
    }
}
