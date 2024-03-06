import React, { Component } from "react";
import { Col, Row } from "react-bootstrap";
import { Modal, ModalHeader, ModalBody, ModalFooter, Table } from 'reactstrap';
import { MomentDateComponent } from "../../components/CustomComponent";
import WSManager from "../../helper/WSManager";
import HF from "../../helper/HelperFunction";
class ViewParticipants extends Component {
    constructor(props) {
        super(props)
        this.state = { StatusmodalIsOpen: false }
    }



    render() {

        const { viewparticipantModal, participantsListPickem, participantDetail } = this.props;

        return (
            <Modal isOpen={viewparticipantModal}
                className="inactive-modal view-participants lineup-details modal-md">
                <ModalHeader className="modal-header">
                    <div>Participant List ({participantsListPickem && participantsListPickem.length})</div>
                    <div onClick={() => this.props.closeParticipantsModal()} className="cursor-pointer">X</div>
                </ModalHeader>
                <ModalBody className="p-0">
                    <div className="lineup-teams theme-color">
                        <Row>
                            <Col xs={12}>

                                <div className='d-flex pt-team-wrap'>
                                    <div className='pt-team-heading'>
                                        <p className='pt-tour-name'>Tournament name</p>
                                        <p className='pt-team-detail'> {participantDetail.name} </p>
                                    </div>
                                    <div className='pt-team-heading'>
                                        <p className='pt-tour-name'>Start Date</p>
                                        <p className='pt-team-detail'> 
                                        {/* <MomentDateComponent data={{ date: participantDetail.start_date, format: "D MMM hh:mmA" }} /> */}
                                        {HF.getFormatedDateTime(participantDetail.start_date, "D MMM hh:mmA ")}
                                        </p>
                                    </div>
                                    <div className='pt-team-heading'>
                                        <p className='pt-tour-name'>End Date</p>
                                        <p className='pt-team-detail'> 
                                        {/* <MomentDateComponent data={{ date: participantDetail.end_date, format: "D MMM hh:mmA" }} /> */}
                                        {HF.getFormatedDateTime(participantDetail.end_date, "D MMM hh:mmA")}
                                        </p>
                                    </div>
                                    <div className='pt-team-heading'>
                                        <p className='pt-tour-name'>Bonus Allowed</p>
                                        <p className='pt-team-detail'> {parseInt(participantDetail.max_bonus)}%</p>
                                    </div>
                                </div>
                            </Col>
                        </Row>
                    </div>
                    <Row className="mb-5 mt-4">
                        <Col md={12}>
                            <div className="table-responsive common-table">
                                <Table striped>
                                    <thead className="colored-header">
                                        <tr>
                                            <th>Name</th>
                                            <th>Fixtures Joined</th>
                                            <th>Joined On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {(participantsListPickem && participantsListPickem.length > 0) && participantsListPickem.map((item) => {
                                            return (
                                                <tr>
                                                    <td>{item.user_name}</td>
                                                    <td>{item.joined_fixture_count}</td>
                                                    <td>
                                                        {/* <MomentDateComponent data={{ date: item.added_date,
                                                             format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                        {HF.getFormatedDateTime(item.added_date, "D-MMM-YYYY hh:mm A")}
                                                    </td>
                                                </tr>
                                            )
                                        })
                                        }
                                    </tbody>
                                </Table>
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }
}
export default ViewParticipants
