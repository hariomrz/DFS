import React, { Component } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Images from "../images";
import HF, { _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
export default class PT_LeaderBoardModal extends Component {
    constructor(props) {
        super(props)
        this.state = {}
    }

    render() {
        let { PickItem, ParticipantsList, closeUserListModal, PARTI_CURRENT_PAGE, PERPAGE, PartiListPosting, usersModalOpen, TotalParticipants, activeTab } = this.props
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
                                            <img src={PickItem.image ? NC.S3 + NC.PICKEM_TR_LOGO + PickItem.image : Images.no_image} className="img-cover" />
                                        </figure>
                                        <div className="pt-detail">
                                            <div className="pt-tag">Pick’em Tournament</div>
                                            <div
                                                className={`pt-title`}
                                                onClick={() => this.props.redirectCallback(PickItem.pickem_id)}
                                            >
                                                {PickItem.name}
                                            </div>
                                            <div className="pt-dt-fx">
                                                <span className="pt-date">
                                                    {/* <MomentDateComponent data={{ date: PickItem.start_date, format: "DD MMM" }} /> */}
                                                    {HF.getFormatedDateTime(PickItem.start_date, "DD MMM")}

                                                    {'-'}
                                                    {/* <MomentDateComponent data={{ date: PickItem.end_date, format: "DD MMM" }} /> */}
                                                    {HF.getFormatedDateTime(PickItem.end_date, "DD MMM")}

                                                </span>
                                                <span className="pt-date">{PickItem.match_count ? PickItem.match_count : '0'} Fixtures</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="pt-win-box clearfix">
                                        <div className="pt-leg-name float-left">
                                            {PickItem.league_name}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="participant-count">Leaderboard</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className="table-responsive common-table bg-white">
                                <div className="tbl-min-hgt">
                                    <Table>
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Username</th>
                                                <th>Total Score</th>
                                                <th>Prize</th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(ParticipantsList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>{item.game_rank ? item.game_rank : '--'}</td>

                                                                <td>{item.user_name ? item.user_name : '--'}</td>

                                                                <td>{item.total_score ? item.total_score : '--'}</td>

                                                                <td>
                                                                    {
                                                                        !_isEmpty(item.prize_data) ? <span
                                                                            className="pt-prize"
                                                                            dangerouslySetInnerHTML={HF.getPrizeMoney(item.prize_data)}
                                                                        >
                                                                        </span>
                                                                            :
                                                                            '--'
                                                                    }
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                                :
                                                <tbody>
                                                    <tr>
                                                        <td colSpan="8">
                                                            {(TotalParticipants == 0 && !PartiListPosting) ?
                                                                <div className="no-records">
                                                                    {NC.NO_RECORDS}</div>
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
                                            onChange={e => this.props.handleUsersPageChange(e)}
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