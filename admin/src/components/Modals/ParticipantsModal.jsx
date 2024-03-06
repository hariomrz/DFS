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
export default class ParticipantsModal extends Component {
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
                            <div className="common-fixture">
                                <img src={PickItem.home_flag ? NC.S3 + NC.PT_TEAM_FLAG + PickItem.home_flag : Images.KOL} className="com-fixture-flag float-left" alt="" />
                                <img src={PickItem.away_flag ? NC.S3 + NC.PT_TEAM_FLAG + PickItem.away_flag : Images.HYD} className="com-fixture-flag float-right" alt="" />
                                <div className="com-fixture-container">
                                    <div className="com-fixture-name">
                                        <span>{PickItem.home}</span>
                                        vs
                                        <span>{PickItem.away}</span>
                                    </div>
                                    <div className="com-fixture-time">
                                        {
                                            (HF.showCountDown(PickItem.season_scheduled_date) && !PickItem.onCompTimer) ?
                                                <div className="pickem-matchtype">
                                                    <Countdown
                                                        daysInHours={true}
                                                        date={WSManager.getUtcToLocal(PickItem.season_scheduled_date)}
                                                    />
                                                </div>
                                                :
                                                <div className="live-comp-date">
                                                    <Moment className="date-style" date={WSManager.getUtcToLocal(PickItem.season_scheduled_date)} format="D-MMM-YYYY hh:mm A" />
                                                </div>
                                        }
                                    </div>
                                    <div className="com-fixture-title">{PickItem.league_name}</div>
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
                                                <th className="pl-4">Name</th>
                                                <th>Pick</th>
                                                <th>Bid</th>
                                                <th>
                                                    {
                                                        activeTab != "3" ?
                                                            'Estimated Winnings'
                                                            :
                                                            'Won'
                                                    }
                                                </th>
                                            </tr>
                                        </thead>
                                        {
                                            TotalParticipants > 0 ?
                                                _.map(ParticipantsList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className="pl-4">{item.user_name ? item.user_name : '--'}</td>
                                                                <td className="text-ellipsis">{item.user_selected_pick ? item.user_selected_pick : '--'}</td>
                                                                <td>{item.bet_coins ? item.bet_coins : '--'}</td>
                                                                <td>
                                                                    {
                                                                        activeTab != "3" 
                                                                            ?
                                                                            item.estimated_winning ? item.estimated_winning : 0
                                                                            :
                                                                            !_.isEmpty(item.prize_data) ? <span
                                                                                className="pt-prize"
                                                                                dangerouslySetInnerHTML={HF.getPrizeMoney(item.prize_data)}
                                                                            >
                                                                            </span>
                                                                                :
                                                                                '0'
                                                                    }</td>
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