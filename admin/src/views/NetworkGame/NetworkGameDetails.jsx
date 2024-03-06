import React, { Component, Fragment } from "react";
import { Row, Col, Table, Modal, ModalBody } from 'reactstrap';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import "react-datepicker/dist/react-datepicker.css";
import Moment from 'react-moment';
import { getNetworkContestDetails, getNetworkContestParticipants } from '../../helper/WSCalling';
import LS from 'local-storage';
import { MomentDateComponent } from "../../components/CustomComponent";
import Images from '../../components/images';
import HF from '../../helper/HelperFunction';
export default class NetworkGameDetails extends Component {
    constructor(props) {
        super(props)
        let selected_sports_id = (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId;
        this.state = {
            selected_sport: selected_sports_id,
            Total: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            isLineupModalOpen: false,
            gameDetail: [],
            LeagueDetail: [],
            MatchDetail: [],
            MatchList: [],
            SportDetail: [],
            UserData: [],
            GameLinupDetail: [],
            LinupUpData: [],
        }
    }
    componentDidMount() {
        this.getGameDetail()
    }

    lineupDetailModal(lineup_master_contest_ids, league_id) {

        this.setState(prevState => ({
            isLineupModalOpen: !prevState.isLineupModalOpen
        }), () => {
            if (this.state.isLineupModalOpen)
                this.getLinupDetails(lineup_master_contest_ids, league_id)
        });
    }

    getGameDetail = () => {
        let params = {
            sports_id: this.state.selected_sport,
            contest_unique_id: this.props.match.params.contest_unique_id
        }

        getNetworkContestDetails(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    gameDetail: ResponseJson.data.contest_detail,
                    LeagueDetail: ResponseJson.data.league_detail,
                    MatchDetail: ResponseJson.data.match_detail,
                    MatchList: ResponseJson.data.match_list,
                    SportDetail: ResponseJson.data.sport_detail,
                    UserData: ResponseJson.data.user_data,
                    prize_distibution_detail: ResponseJson.data.contest_detail.prize_distibution_detail,
                }, () => {
                    this.getGameLinupDetail(this.state.gameDetail)
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    getGameLinupDetail = (gameDetail) => {
        const { selected_sport, PERPAGE, CURRENT_PAGE } = this.state
        let params = {
            "items_perpage": PERPAGE,
            "total_items": 0,
            "current_page": CURRENT_PAGE,
            "game_id": gameDetail.contest_id,
            "sports_id": selected_sport
        }
        getNetworkContestParticipants(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    GameLinupDetail: ResponseJson.data.result,
                    Total: ResponseJson.data.total
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getLinupDetails = (lineup_master_contest_ids, league_id) => {
        let params = {
            lineup_master_contest_id: lineup_master_contest_ids,
            league_id: league_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_NETWORK_LINEUP_DETAIL, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    LinupUpData: ResponseJson.data,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }
    handlePageChange(current_page) {
        if (current_page !== this.state.CURRENT_PAGE) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getGameLinupDetail(this.state.gameDetail);
            });
        }
    }
    render() {
        const { gameDetail, LeagueDetail, MatchDetail, MatchList, SportDetail, UserData, prize_distibution_detail, GameLinupDetail, LinupUpData, CURRENT_PAGE, PERPAGE, Total } = this.state
        return (
            <Fragment>
                <div className="contest-d-main">
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h1 className="h1-cls">Network Contest Detail</h1>
                        </Col>
                    </Row>
                    <div className="details-box">
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Contest Name</label>
                                <div className="user-value">
                                    {gameDetail.contest_name}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>League</label>
                                <div className="user-value">{LeagueDetail.league_name}</div></Col>
                            {
                                gameDetail.sports_id == 7 && (
                                    <Col md={3}>
                                        <label>Match </label>
                                        <div className="user-value">
                                            {gameDetail.collection_name}{' '}{MatchDetail.format}
                                        </div>
                                    </Col>
                                )
                            }
                            {/* <Col md={3}>
                                <label>Salary Cap </label>
                                <div className="user-value">{gameDetail.collection_salary_cap}
                                </div>
                            </Col> */}
                        </Row>
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Entrants / Participants</label>
                                <div className="user-value">{gameDetail.total_user_joined}
                                    /
                                {gameDetail.size != -1 && gameDetail.size}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Entry Fee</label>
                                <div className="user-value">{gameDetail.entry_fee}</div>
                            </Col>
                            <Col md={3}>
                                <label>Site Rake</label>
                                <div className="user-value">{gameDetail.site_rake}</div>
                            </Col>
                            <Col md={3}>
                                <label>Multiple lineup / Lineup Count</label>
                                {
                                    gameDetail.multiple_lineup != '0' && (
                                        <div className="user-value">Yes / {gameDetail.multiple_lineup}
                                        </div>
                                    )
                                }

                            </Col>
                        </Row>
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Featured </label>
                                {gameDetail.is_feature == '0' ?
                                    <div className="user-value">No</div>
                                    :
                                    <div className="user-value">Yes</div>
                                }
                            </Col>
                            <Col md={3}>
                                <label>Auto Recurrent  </label>
                                {gameDetail.is_auto_recurring == '0' ?
                                    <div className="user-value">No</div>
                                    :
                                    <div className="user-value">Yes</div>
                                }
                            </Col>
                            <Col md={3}>
                                <label>Scheduled Date</label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}
                                
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Created Date </label>
                                <div className="user-value">
                                    {/* <MomentDateComponent data={{ date: gameDetail.added_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(gameDetail.added_date, "D-MMM-YYYY hh:mm A")}

                                </div>
                            </Col>
                        </Row>
                        <Row className="box-items mt-3">
                            <Col md={3}>
                                <label>Created By</label>
                                <div className="user-value">

                                    {UserData.user_name === null || UserData.user_name == ''
                                        ?
                                        'ADMIN'
                                        :
                                        UserData.user_name
                                    }
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Status</label>
                                <div className="user-value">
                                    {gameDetail.status == '0' && 'Open'}
                                    {gameDetail.status == '1' && 'Cancel'}
                                    {gameDetail.status == '2' && 'Coming'}
                                    {gameDetail.status == '3' && 'Prize Distributed'}
                                </div>
                            </Col>
                            <Col md={3}>
                                <label>Prize Pool</label>
                                <div className="user-value">{gameDetail.prize_pool}</div>
                            </Col>
                            {/* <Col md={3}>
                                <label>Max Bonus Allowed (%)</label>
                                <div className="user-value">{gameDetail.max_bonus_allowed}</div>
                            </Col> */}
                            <Col md={3}>
                                <label>Total Commimsion</label>
                                <div className="user-value">{gameDetail.total_commimsion}</div>
                            </Col>
                        </Row>
                    </div>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr className="text-center">
                                        <th>Min</th>
                                        <th>Max</th>
                                        <th>%</th>
                                        <th>Amount (Per Person)</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(prize_distibution_detail, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="text-center">{item.min}</td>
                                                    <td className="text-center">{item.max}</td>
                                                    <td className="text-center">{item.per}</td>
                                                    <td className="text-center">

                                                        {
                                                            item.prize_type == "0" &&
                                                            <span className="mr-1"><i className="icon-bonus1 mr-1"></i></span>
                                                        }
                                                        {
                                                            item.prize_type == "1" &&
                                                            <span className="mr-1">{HF.getCurrencyCode()}</span>
                                                        }
                                                        {
                                                            item.prize_type == "2" &&
                                                            <span>
                                                                <img className="mr-1" src={Images.REWARD_ICON} alt="" />
                                                            </span>
                                                        }

                                                        {item.prize_type != '3' && item.amount}
                                                        {item.prize_type == '3' && item.min_value}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h3 className="h3-cls">Match List</h3>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr className="text-center">
                                        <th>Match</th>
                                        <th>Schedule Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td className="text-center">{MatchDetail.home + ' - ' + MatchDetail.away}</td>
                                        <td className="text-center">
                                            {/* <MomentDateComponent data={{ date: MatchDetail.season_scheduled_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                    {HF.getFormatedDateTime(MatchDetail.season_scheduled_date, "D-MMM-YYYY hh:mm A")}

                                        </td>
                                    </tr>
                                </tbody>
                            </Table>
                        </Col>
                    </Row>
                    <Row className="mt-3 mb-3">
                        <Col md={12}>
                            <h3 className="h3-cls">Participants</h3>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Rank</th>
                                        <th>Score</th>
                                        <th>Winning Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                {
                                    _.map(GameLinupDetail, (lineup, idx) => {
                                        var prz = ''
                                        var prz_type = ''
                                        if (!_.isNull(lineup.prize_data) && lineup.prize_data.length > 0) {
                                            if (lineup.prize_data[0].prize_type == 1) {
                                                var prz_type = lineup.prize_data[0].prize_type
                                                prz = lineup.prize_data[0].amount
                                            } else if (lineup.prize_data[0].prize_type == 3) {
                                                prz = lineup.prize_data[0].name
                                            }
                                        }

                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>{lineup.user_name}</td>
                                                    <td>{lineup.game_rank}</td>
                                                    <td>{lineup.total_score}</td>
                                                    <td>
                                                        {prz_type == 1 && <span>&#8377;&nbsp;</span>}
                                                        {prz}
                                                    </td>
                                                    <td onClick={() => this.lineupDetailModal(lineup.lineup_master_contest_id, LeagueDetail.league_id)}>
                                                        <span className="linup-details">Lineup Details</span></td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                        </Col>
                    </Row>
                    {Total > PERPAGE && (<div className="custom-pagination userlistpage-paging float-right mb-5">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={Total}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                    )}
                    <div>
                        <Modal isOpen={this.state.isLineupModalOpen} toggle={() => this.lineupDetailModal()} className="lineup-details modal-md">
                            <ModalBody className="p-0">
                                <div className="lineup-teams theme-color">
                                    <Row>
                                        <Col xs={12}>
                                            <ul className="lineup-feelist">
                                                <li className="lineup-feeitem">
                                                    <label>Team name</label>
                                                    <div className="font-weight-bold">{LinupUpData.team_name}</div>
                                                </li>
                                                <li className="lineup-feeitem">
                                                    <label>Username</label>
                                                    <div className="font-weight-bold">{LinupUpData.user_name ? LinupUpData.user_name : '--'}</div>
                                                </li>
                                                <li className="lineup-feeitem">
                                                    <label>Total Score </label>
                                                    <div className="font-weight-bold">{LinupUpData.score}</div>
                                                </li>
                                            </ul>
                                        </Col>
                                    </Row>
                                </div>
                                <Col xs={12}>
                                    <Row className="rank-box">
                                        <Col xs={3}>
                                            <h3 className="h3-cls">Rank {' '} {LinupUpData.game_rank ? LinupUpData.game_rank : '--'}</h3>
                                        </Col>
                                        <Col xs={9}>
                                            <h3 className="h3-cls">
                                                Winnings {' '} &#8377;{LinupUpData.won_amount ? LinupUpData.won_amount : '--'}</h3>
                                        </Col>
                                    </Row>
                                </Col>
                                <Row className="mb-5">
                                    <Col md={12}>
                                        <div className="table-responsive common-table">
                                            <Table>
                                                <thead>
                                                    <tr>
                                                        <th className="pl-4">Position</th>
                                                        <th>Player Name</th>
                                                        <th>Team Name</th>
                                                        <th>Score</th>
                                                    </tr>
                                                </thead>
                                                {
                                                    _.map(LinupUpData.lineup, (lineup, idx) => {
                                                        return (
                                                            <tbody key={idx}>
                                                                <tr>
                                                                    <td className="pl-4">{lineup.position}</td>
                                                                    <td>
                                                                        {lineup.full_name}
                                                                        {lineup.captain == 1 ?
                                                                            <span>(C)</span>
                                                                            :
                                                                            lineup.captain == 2
                                                                                ?
                                                                                <span>(VC)</span>
                                                                                :
                                                                                ''
                                                                        }
                                                                    </td>
                                                                    <td>{lineup.team_abbr}</td>
                                                                    <td>{lineup.score}</td>
                                                                </tr>
                                                            </tbody>
                                                        )
                                                    })
                                                }
                                            </Table>
                                        </div>
                                    </Col>
                                </Row>
                            </ModalBody>
                        </Modal>
                    </div>
                </div>
            </Fragment>
        )
    }
}
