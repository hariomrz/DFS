import React, { Component } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../Loader';
import Images from "../images";
import { NO_PARTICIPANTS } from "../../helper/Message";
import { MomentDateComponent } from "../CustomComponent";
export default class DfsT_MatchParticipantsModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { ListItem, ParticipantsList, closeUserListModal, PARTI_CURRENT_PAGE, PERPAGE, PartiListPosting, usersModalOpen, TotalParticipants, activeTab } = this.props

   

        return (

            <Modal
                isOpen={usersModalOpen}
                className="modal-md comm-userlist-modal match-p-m"
                toggle={closeUserListModal}
            >
                <ModalBody>
                    <Row className="details-header">
                        <Col md={12}>
                            <div className="dfst common-fixture">
                                <div className="bg-card">
                                    <div className="clearfix">
                                        <img className="com-fixture-flag float-left" src={ListItem.home_flag ? NC.S3 + NC.FLAG + ListItem.home_flag : Images.no_image} />

                                        <img className="com-fixture-flag float-right" src={ListItem.away_flag ? NC.S3 + NC.FLAG + ListItem.away_flag : Images.no_image} />

                                        <div className="com-fixture-container">
                                            <div className="com-fixture-name">{(ListItem.home) ? ListItem.home : 'TBA'} VS {(ListItem.away) ? ListItem.away : 'TBA'}</div>
                                            <div className="com-fixture-title">
                                                {
                                                    ListItem.season_scheduled_date ?
                                                        <MomentDateComponent data={{ date: ListItem.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} />
                                                        :
                                                        '--'
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="participant-count">Participant List ({TotalParticipants})</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="table-responsive common-table bg-white">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Mobile No.</th>
                                                <th>Joined date</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(ParticipantsList, (Listitem, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>
                                                                    <a className="text-click" href={"/admin/#/profile/" + Listitem.user_unique_id}>
                                                                        {Listitem.user_name ? Listitem.user_name : '--'}
                                                                    </a>
                                                                </td>
                                                                <td>{Listitem.email ? Listitem.email : '--'}</td>
                                                                <td>{Listitem.phone_no ? Listitem.phone_no : '--'}</td>
                                                                <td>
                                                                    {
                                                                        Listitem.added_date ?
                                                                            <MomentDateComponent data={{ date: Listitem.added_date, format: "D-MMM-YYYY hh:mm A" }} />
                                                                            :
                                                                            '--'

                                                                    }
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody className="no-shadow">
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalParticipants == 0 && !PartiListPosting) ?
                                                                <div className="no-records">
                                                                    {NO_PARTICIPANTS}</div>
                                                                :
                                                                <Loader />
                                                            }
                                                        </td>
                                                    </tr>
                                                </tbody>
                                        }
                                    </Table>
                                </div>
                                {
                                    TotalParticipants > PERPAGE && (
                                        <div className="custom-pagination">
                                            <Pagination
                                                activePage={PARTI_CURRENT_PAGE}
                                                itemsCountPerPage={PERPAGE}
                                                totalItemsCount={TotalParticipants}
                                                pageRangeDisplayed={5}
                                                onChange={e => this.props.handleUsersPageChange(e)}
                                            />
                                        </div>
                                    )
                                }
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }
}