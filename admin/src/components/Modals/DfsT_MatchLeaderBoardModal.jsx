import React, { Component } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import * as NC from '../../helper/NetworkingConstants';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import Images from "../images";
import HF, { _isEmpty, _isUndefined } from "../../helper/HelperFunction";
import { MomentDateComponent } from "../../components/CustomComponent";
export default class DfsT_MatchLeaderBoardModal extends Component {
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
                            <div className="participant-count">Fixture Leaderboard</div>
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
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(ParticipantsList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td>{item.match_rank ? item.match_rank : '--'}</td>

                                                                <td>
                                                                    <a className="text-click" href={"/admin/#/profile/" + item.user_unique_id}>
                                                                        {item.user_name ? item.user_name : '--'}
                                                                    </a>
                                                                </td>

                                                                <td>{item.total_score ? item.total_score : '--'}</td>
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