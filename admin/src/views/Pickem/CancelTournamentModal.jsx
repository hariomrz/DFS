import React, { Component } from "react";
import { Row, Col, Modal, ModalBody, ModalHeader, ModalFooter, Input, Button } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { cancelTournament, DFST_getTournamentFixtures } from '../../helper/WSCalling';
import WSManager from "../../helper/WSManager";
import HF, { _Map } from "../../helper/HelperFunction";
import { getPickemSaveTournamentFixtures, getPickemGetTournamentFixtures } from "../../helper/WSCalling";

export default class CancelTournamentModal extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Posting: false,
            fixtureList: [],
            selFixList: [],
            isMore: false,
            cancel_reason: ''
        }
    }

    cancelTournamentFunc = () => {
        let param = {
            tournament_id: this.props.tournID,
            cancel_reason: this.state.cancel_reason
        }
        cancelTournament(param).then(Response => {
            if (Response.response_code == NC.successCode) {
                notify.show(Response.message, 'error', 3000)
                this.props.closeCancelTour();
                setTimeout(() => {
                    this.props.history.push('/pickem/picks')
                }, 1000)
            }
            else {
                notify.show(NC.SYSTEM_ERROR, 'error', 5000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }


    render() {
        let { visibleCnclModal, tournID } = this.props
        return (
            <Modal
                isOpen={visibleCnclModal}
                className="cancel-tour-modal"
            >
                <ModalBody>
                    <Row>
                        <Col md={12} className="cancel-wrap">
                            <p className='cancel-heading'>Do you want to cancel this tournament?</p>
                            <input type="textarea" name='cancel' className='cancel-input' placeholder='Enter Reason' onChange={(e) => this.setState({ cancel_reason: e.target.value })} />
                        </Col>
                    </Row>
                </ModalBody>
                <ModalFooter className="request-footer mt-5">
                    <Button className="btn-secondary-outline"
                        onClick={() => this.props.closeCancelTour()}
                    >Cancel</Button>
                    <Button
                        disabled={this.state.cancel_reason == ''}
                        onClick={() => this.cancelTournamentFunc()}
                        className={`btn-secondary-outline yes-wd-cls`}>Yes</Button>
                </ModalFooter>
            </Modal>
        )
    }
}
