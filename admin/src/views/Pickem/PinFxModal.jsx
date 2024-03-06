import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalFooter, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { pickemPinFunc } from '../../helper/WSCalling';
import { _Map } from "../../helper/HelperFunction";

export default class PinFxModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
            fixtureList: [],
            selFixList: [],
            isMore: false,
        }
    }

    pinTournamentFunc = () => {
        let param = {
            tournament_id: this.props.tournID,
        }
        pickemPinFunc(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'success', 5000)
                this.props.pickemList();
                this.props.closePinModal();
            }
            else {
                notify.show(NC.SYSTEM_ERROR, 'error', 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 3000)

        })
    }


    render() {
        let { fxPinModalOpen, tournID, isPin } = this.props
        // console.log(isPin)
        return (
            <Modal
                isOpen={fxPinModalOpen}
                className="cancel-tour-modal"
            >
                <ModalBody>
                    <Row>
                        <Col md={12} className="cancel-wrap">
                            <p className='cancel-heading'>Do you want to {this.props.isPin == "1" ? 'unpin' : 'pin'} this tournament?</p>
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer">
                    <Button className="btn-secondary-outline"
                        onClick={() => this.props.closePinModal()}
                    >No</Button>
                    <Button
                        disabled={this.state.cancel_reason == ''}
                        onClick={() => this.pinTournamentFunc()}
                        className={`btn-secondary-outline yes-wd-cls`}>Yes</Button>
                </ModalFooter>
            </Modal>
        )
    }
}
