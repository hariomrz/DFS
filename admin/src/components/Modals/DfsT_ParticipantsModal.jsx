import React, { Component } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Images from "../images";
import Countdown from 'react-countdown-now';
import HF from "../../helper/HelperFunction";
import WSManager from '../../helper/WSManager';
import Moment from 'react-moment';
import { NO_PARTICIPANTS } from "../../helper/Message";
import { MomentDateComponent } from "../../components/CustomComponent";
export default class DfsT_ParticipantsModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }
    render() {
        let { ListItem, ParticipantsList, closeUserListModal, PARTI_CURRENT_PAGE, PERPAGE, PartiListPosting, usersModalOpen, TotalParticipants, activeTab } = this.props
        return (

            <Modal
                isOpen={usersModalOpen}
                className="modal-md comm-userlist-modal"
                toggle={closeUserListModal}
            >
                <ModalBody>
                    <Row className="details-header">
                        <Col md={12}>
                            <div className="pt-card">
                                <div className="pt-info">
                                    <div className="pt-info-box">
                                        <figure className="pt-icon">
                                            <img src={ListItem.image ? NC.S3 + NC.DFST_LOGO + ListItem.image : Images.no_image} className="img-cover" />
                                        </figure>
                                        <div className="pt-detail">
                                            <div className="pt-tag">DFS TOURNAMENT</div>
                                            <div
                                                className={`pt-title`}
                                                onClick={() => this.props.redirectCallback(ListItem.pickem_id)}
                                            >
                                                {ListItem.name}
                                            </div>
                                            <div className="pt-dt-fx">
                                                <span className="pt-date">
                                                    <MomentDateComponent data={{ date: ListItem.start_date, format: "DD MMM" }} />
                                                    {'-'}
                                                    <MomentDateComponent data={{ date: ListItem.end_date, format: "DD MMM" }} />
                                                </span>
                                                <span className="pt-date">{ListItem.match_count ? ListItem.match_count : '0'} Fixtures</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="pt-win-box clearfix">
                                        <div className="pt-leg-name float-left">
                                            {ListItem.league_name}
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
                                                <th className="pl-4">User Name</th>
                                                <th className="pl-4">Joined date</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(ParticipantsList, (Listitem, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-4">
                                                                    <a className="text-click" href={"/admin/#/profile/" + Listitem.user_unique_id}>
                                                                        {Listitem.user_name ? Listitem.user_name : '--'}
                                                                    </a>
                                                                </td>
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
                                {TotalParticipants > PERPAGE && (
                                    <div className="custom-pagination">
                                        <Pagination
                                            activePage={PARTI_CURRENT_PAGE}
                                            itemsCountPerPage={PERPAGE}
                                            totalItemsCount={TotalParticipants}
                                            pageRangeDisplayed={5}
                                            onChange={(e) => this.props.handleUsersPageChange(e)}
                                        />
                                    </div>
                                )}
                            </div>
                        </Col>
                    </Row>
                </ModalBody>
            </Modal>
        )
    }
}