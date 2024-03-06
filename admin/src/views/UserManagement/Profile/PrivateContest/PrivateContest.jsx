
import React, { Component, Fragment } from "react";
import { Row, Col, CardBody, Table, Card } from "reactstrap";
import WSManager from "../../../../helper/WSManager";
import _ from 'lodash';
import Profile from '../Profile';
import * as NC from "../../../../helper/NetworkingConstants";
import { notify } from 'react-notify-toast';
import HF from '../../../../helper/HelperFunction';
import Images from '../../../../components/images';
import Pagination from "react-js-pagination";
import LS from 'local-storage';
import Moment from 'react-moment';
import moment from 'moment';
export default class PrivateContest extends Component {
    constructor(props) {
        super(props)

        let selected_sports_id = (LS.get('selected_sport')) ? LS.get('selected_sport') : NC.sportsId;

        this.state = {
            RankData: [],
            contestParams: { 'user_id': this.props.user_id, 'sports_id': selected_sports_id, 'league_id': '', 'season_game_uid': '', 'collection_master_id': '', 'group_id': '', 'status': '', 'keyword': '', 'sort_field': 'season_scheduled_date', 'sort_order': 'DESC', currentPage: 1, pageSize: 10, pagesCount: 1 },
            contestList: [],
            AdminEarning: '',
            UserEarning: '',
            UserJoined: '',
            PrivateContests: '',
        }
    }
    componentDidMount() {
        this.getRank()
        this.GetContestList()
    }

    getRank() {
        let params = {
            user_id: this.props.user_id
        }
        WSManager.Rest(NC.baseURL + NC.PC_GET_USER__DATA, params).then((ResponseJson) => {
            if (ResponseJson.response_code === NC.successCode) {
                this.setState({
                    AdminEarning: ResponseJson.data.total_admin_earning ? ResponseJson.data.total_admin_earning : 0,
                    PrivateContests: ResponseJson.data.total_private_contest_created ? ResponseJson.data.total_private_contest_created : 0,
                    UserEarning: ResponseJson.data.total_user_earning ? ResponseJson.data.total_user_earning : 0,
                    UserJoined: ResponseJson.data.total_new_user_signups ? ResponseJson.data.total_new_user_signups : 0,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    GetContestList = () => {
        this.setState({ posting: true })
        let params = this.state.contestParams;
        WSManager.Rest(NC.baseURL + NC.PC_GET_USER_LIST, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                var responseJsonData = responseJson.data;
                this.setState({
                    contestList: responseJsonData.result,
                    Total: responseJsonData.total,

                    contestParams: { ...this.state.contestParams, pagesCount: Math.ceil(responseJson.data.total / this.state.contestParams.pageSize), totalRecords: responseJson.data.total },
                })
            }
            this.setState({ posting: false })
        })
    }

    sortContestList = (e, sort_field) => {
        let contestParams = _.cloneDeep(this.state.contestParams);
        let sort_order = contestParams.sort_order;
        if (contestParams.sort_field == sort_field) {
            if (sort_order == "DESC") {
                sort_order = "ASC";
            } else {
                sort_order = "DESC";
            }
        } else {
            sort_order = "DESC";
        }

        contestParams['sort_field'] = sort_field;
        contestParams['sort_order'] = sort_order;
        this.setState({ 'contestParams': contestParams }, function () {
            this.GetContestList();
        });
    }

    handlePageChange(current_page) {
        let contestParams = this.state.contestParams;
        if (contestParams['currentPage'] != current_page) {
            contestParams['currentPage'] = current_page;
            this.setState({ contestParams: contestParams },
                function () { this.GetContestList(); });
        }
    }

    getWinnerCount(ContestItem) {

        console.log("ContestItem===", ContestItem);

        if (ContestItem.prize_distibution_detail != '') {
            if ((ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max) > 1) {
                return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winners"
            } else {
                return ContestItem.prize_distibution_detail[ContestItem.prize_distibution_detail.length - 1].max + " Winner"
            }
        } else {
            return '0 Winner';
        }
    }

    isDateTimePast=(season_scheduled_date)=>{
        let date = moment(season_scheduled_date).utc(true).local().valueOf()
        let now = moment().utc().local().valueOf();
        return now > date;
    }

    render() {
        const { contestParams, contestList, AdminEarning, UserEarning, PrivateContests, UserJoined, Total } = this.state
        return (
            <Fragment>
                <Row>
                    <Col md={12}>
                        <div className="user-activity-dashboard">
                            <div className="dashboard-row">
                                <div className="act-item ml-0 pointer">
                                    <div>
                                        <div className="act-title">PRIVATE CONTEST CREATED</div>
                                        <div className="act-count">{PrivateContests}</div>
                                    </div>
                                </div>
                                <div className="act-item pointer">
                                    <div>
                                        <div className="act-title">EARNINGS FROM PRIVATE CONTEST</div>
                                        {/* <div className="act-count">{HF.getCurrencyCode() + UserEarning}</div> */}
                                        <div className="act-count">{HF.getCurrencyCode() + HF.getNumberWithCommas(HF.convertTodecimal(UserEarning, 2))}</div>
                                    </div>
                                </div>
                                <div className="act-item pointer">
                                    <div>
                                        <div className="act-title">ADMIN EARNING</div>
                                        <div className="act-count">{HF.getCurrencyCode() + HF.getNumberWithCommas(HF.convertTodecimal(AdminEarning, 2))}</div>
                                    </div>
                                </div>
                                <div className="act-item pointer">
                                    <div>
                                        <div className="act-title">NEW USER JOINED</div>
                                        <div className="act-count">{UserJoined}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-30 mb-20">
                    <Col md={12}>
                        <h3 className="h3-cls">User’s private contest history</h3>
                    </Col>
                </Row>
                <div className="user-pc contestlist-dashboard">
                    <Row>
                        <Col xs="12" lg="12" >
                            <div className="contestcard">
                                <CardBody>

                                    <Table className="communication-table">
                                        <thead>
                                            <tr>
                                                <th className="xcontest-column">
                                                    <div
                                                        className="dropdown"
                                                        onClick={(e) => this.sortContestList(e, 'date')}>
                                                        <button
                                                            className="contests dropdown-toggle contest-dashboard-btn"
                                                            type="button"
                                                            id="dropdownMenu"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false"
                                                        >
                                                            Date
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'date' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'date' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th className="xcontest-column">
                                                    <div
                                                        className="dropdown"
                                                        onClick={(e) => this.sortContestList(e, 'match_name')}>
                                                        <button
                                                            className="contests dropdown-toggle contest-dashboard-btn"
                                                            type="button"
                                                            id="dropdownMenu"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false"
                                                        >
                                                            Match
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'match_name' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'match_name' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th className="xcontest-column">
                                                    <div
                                                        className="dropdown"
                                                        onClick={(e) => this.sortContestList(e, 'contest_name')}>
                                                        <button
                                                            className="contests dropdown-toggle contest-dashboard-btn"
                                                            type="button"
                                                            id="dropdownMenu"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false"
                                                        >
                                                            Contests
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'contest_name' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'contest_name' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th onClick={(e) => this.sortContestList(e, 'entry_fee')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Entry Fee
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'entry_fee' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'entry_fee' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>

                                                <th onClick={(e) => this.sortContestList(e, 'minimum_size')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Participants
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'minimum_size' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'minimum_size' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>

                                                <th onClick={(e) => this.sortContestList(e, 'total_user_joined')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Entries
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'total_user_joined' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'total_user_joined' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>

                                                <th onClick={(e) => this.sortContestList(e, 'prize_pool')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Winnings
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'prize_pool' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'prize_pool' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>

                                                <th onClick={(e) => this.sortContestList(e, 'winners')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Winners
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'winners' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'winners' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>

                                                <th onClick={(e) => this.sortContestList(e, 'new_user_joined')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            New users
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'new_user_joined' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'new_user_joined' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th onClick={(e) => this.sortContestList(e, 'multi_entry')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Multi Entry
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'multi_entry' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'multi_entry' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th onClick={(e) => this.sortContestList(e, 'status')}>
                                                    <div className="dropdown">
                                                        <button className="dropdown-toggle contest-dashboard-btn" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Status
                                                        </button>
                                                        {
                                                            contestParams.sort_field == 'status' && contestParams.sort_order == 'DESC' &&
                                                            <i className="fa fa-sort-desc"></i>
                                                        }
                                                        {
                                                            contestParams.sort_field == 'status' && contestParams.sort_order == 'ASC' &&
                                                            <i className="fa fa-sort-asc"></i>
                                                        }
                                                    </div>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                    </Table>
                                </CardBody>
                            </div>
                        </Col>
                    </Row>
                    {
                        Total > 0 &&
                        _.map(contestList, (item, contest_index) => {
                            return (
                                <Row>
                                    <Col xs="12" lg="12" className="collection-vd">
                                        <Card className="recentcom">
                                            <CardBody>
                                                <Table responsive ClassName="tablecontest">
                                                    <tr>
                                                        <td>
                                                            <Moment
                                                                className="date-style"
                                                                date={WSManager.getUtcToLocal(item.season_scheduled_date)}
                                                                format="D MMM YYYY"
                                                            />
                                                        </td>
                                                        <td className="xcontest-column">
                                                            <p className="contest-table-p">
                                                                <span className="line-text-ellipsis" style={{ WebkitBoxOrient: 'vertical' }}> {item.match_name}</span>
                                                            </p>
                                                        </td>
                                                        <td className="xcontest-column">
                                                            <p className="contest-table-p">
                                                                <span className="line-text-ellipsis" style={{ WebkitBoxOrient: 'vertical' }}> {item.contest_name}</span>
                                                            </p>
                                                        </td>
                                                        <td>
                                                            {
                                                                item.currency_type == '0' &&
                                                                <i className="icon-bonus"></i>
                                                            }
                                                            {
                                                                item.currency_type == '1' &&
                                                                HF.getCurrencyCode()
                                                            }
                                                            {
                                                                item.currency_type == '2' &&
                                                                <img src={Images.COINIMG} alt="coin-img" />
                                                            }
                                                            {item.entry_fee}
                                                        </td>
                                                        <td>{item.minimum_size + '-' + item.size}</td>
                                                        <td>{item.total_user_joined}</td>
                                                        <td>
                                                            {
                                                                item.prize_type == '0' &&
                                                                <i className="icon-bonus"></i>
                                                            }
                                                            {
                                                                item.prize_type == '1' &&
                                                                HF.getCurrencyCode()
                                                            }
                                                            {
                                                                item.prize_type == '2' &&
                                                                <img src={Images.COINIMG} alt="coin-img" />
                                                            }
                                                            {item.prize_pool}
                                                        </td>
                                                        <td>
                                                            <span>{this.getWinnerCount(item)}</span>
                                                        </td>
                                                        <td>{item.new_user_joined}</td>
                                                        <td>{item.multiple_lineup}</td>
                                                        <td>
                                                            <span className="text">
                                                                {item.status == "0" && !this.isDateTimePast(item.season_scheduled_date) && 'Upcoming'}
                                                            </span>
                                                            <span className="text-red">
                                                                {
                                                                   item.status == "0" && this.isDateTimePast(item.season_scheduled_date) && 'Live'
                                                                }
                                                                {/* {item.status == "0" && item.season_scheduled_date <= current_date && 'Live'} */}
                                                            </span>
                                                            <span className="text-green">{(item.status == "2" || item.status == "3") && 'Completed'}</span>
                                                        </td>
                                                    </tr>
                                                </Table>
                                            </CardBody>
                                        </Card>
                                    </Col>
                                </Row>
                            )
                        })
                    }
                    {Total <= 0 &&
                        <div className="no-records">{NC.NO_RECORDS}</div>
                    }
                    {Total > contestParams.pageSize &&
                        <Col>
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={contestParams.currentPage}
                                    itemsCountPerPage={contestParams.pageSize}
                                    totalItemsCount={contestParams.totalRecords}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        </Col>
                    }
                </div>
            </Fragment>
        )
    }
}