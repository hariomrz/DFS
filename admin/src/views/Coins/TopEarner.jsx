import React, { Component } from "react";
import { Row, Col, Table } from "reactstrap";
import Images from '../../components/images';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import _ from 'lodash';
import WSManager from '../../helper/WSManager';
import HF from '../../helper/HelperFunction';
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import moment from 'moment';
import { withRouter } from 'react-router'
import Loader from '../../components/Loader';
import { MomentDateComponent } from "../../components/CustomComponent";
class TopEarner extends Component {
    constructor(props) {
        super(props)
        this.state = {
            CURRENT_PAGE: 1,
            PERPAGE: _.isUndefined(this.props.FromDashboard) ? NC.ITEMS_PERPAGE : 10,
            FromDate: '',
            ToDate: '',
            totalCoinsDistributed: 0,
            DistributedCoins: [],
            Pathname: '',
            DistributedPosting: false
        }
    }

    componentDidMount() {
        var PiecesPath = this.props.history.location.pathname.split(/[/ ]+/).pop();
        this.setState({ Pathname: PiecesPath }, () => {
            this.getCoinDistributedHistory()            
        })
    }

    getCoinDistributedHistory() {
        this.setState({ DistributedPosting: true })
        let { CURRENT_PAGE, PERPAGE, FromDate, ToDate, Pathname } = this.state

        let params = {
            // from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            // to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',
            // from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date:  ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
        }

        let CallUrl = ''
        if (Pathname == 'top-redeemer' || this.props.viewType == 'topredeemer') {
            CallUrl = NC.GET_TOP_REDEEMER
        }
        else if (Pathname == 'top-earner' || this.props.viewType == 'topearner') {
            CallUrl = NC.GET_TOP_EARNER
        } else {
            CallUrl = NC.GET_COIN_DISTRIBUTED_HISTORY
        }

        WSManager.Rest(NC.baseURL + CallUrl, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                if (CURRENT_PAGE == 1)
                    this.setState({ Total: ResponseJson.data.total })

                this.setState({
                    DistributedCoins: ResponseJson.data.list,
                    totalCoinsDistributed: ResponseJson.data.total_coins_distributed,
                    DistributedPosting: false
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getCoinDistributedHistory()
        });
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.setState({ CURRENT_PAGE: 1 }, () => {
                    this.getCoinDistributedHistory()
                });
            }
        })
    }

    exportRecords = () => {
        let { Pathname, FromDate, ToDate } = this.state

        let tempFromDate = FromDate ? moment(FromDate).format("YYYY-MM-DD") : '';
        let tempToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : '';
        var query_string = '?from_date=' + tempFromDate + '&to_date=' + tempToDate + '&csv=true';
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        // console.log(query_string); return false;

        if (Pathname == 'top-redeemer' || this.props.viewType == 'topredeemer') {
            window.open(NC.baseURL + NC.EXPORT_TOP_REDEEMER + query_string, '_blank');
        }
        else if (Pathname == 'top-earner' || this.props.viewType == 'topearner') {
            window.open(NC.baseURL + NC.EXPORT_TOP_EARNER + query_string, '_blank');
        } else {
            window.open(NC.baseURL + NC.EXPORT_COIN_DISTRIBUTION_HISTORY + query_string, '_blank');
        }
    }

    render() {
        let { DistributedPosting, CURRENT_PAGE, PERPAGE, Total, FromDate, ToDate, Pathname, DistributedCoins, totalCoinsDistributed } = this.state
        return (
            <React.Fragment>
                <div className={`top-earner-sc ${_.isUndefined(this.props.FromDashboard) ? '' : 'bg-white'}`}>
                    {_.isUndefined(this.props.FromDashboard) && (
                        <React.Fragment>
                            <Row>
                                <Col md={6}>
                                    <div className="float-left">
                                        <div className="top-earner">
                                            {Pathname == 'top-earner' && 'Top Earner'}
                                            {Pathname == 'top-redeemer' && 'Top Redeemer'}
                                            {Pathname == 'coins-distributed' && 'Coin Distributed'}
                                        </div>
                                        <div className="leader-board">Leaderboard</div>
                                    </div>
                                </Col>
                                <Col md={6}>
                                    <div onClick={() => this.props.history.push('/coins/dashboard')} className="go-back">{'<'} Back</div>
                                </Col>
                            </Row>

                            {Pathname == 'coins-distributed' &&
                                <Row className="mt-3">
                                    <Col md={5}>
                                        <div className="float-left">
                                            <div className="total-title">
                                                Total Coin Distributed
                                        </div>
                                            <div className="total-num">
                                                <img className="coin-img" src={Images.REWARD_ICON} alt="" />
                                            <span className="num">{HF.getNumberWithCommas(totalCoinsDistributed)}</span>
                                            </div>
                                        </div>
                                    </Col>
                                    <Col md={2}></Col>
                                    <Col md={5}>
                                        <div className="float-rights">
                                            <div className="member-box float-lefts">
                                                <label className="filter-label">Date</label>
                                                <DatePicker
                                                    maxDate={new Date()}
                                                    className="filter-date"
                                                    showYearDropdown='true'
                                                    selected={FromDate}
                                                    onChange={e => this.handleDateFilter(e, "FromDate")}
                                                    placeholderText="From"
                                                />
                                                <DatePicker
                                                    maxDate={new Date()}
                                                    className="filter-date"
                                                    showYearDropdown='true'
                                                    selected={ToDate}
                                                    onChange={e => this.handleDateFilter(e, "ToDate")}
                                                    placeholderText="To"
                                                />
                                                <div className="export-topearner coin-export">
                                                    <i className="export-list icon-export" onClick={e => this.exportRecords()}></i>
                                                </div>
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
                            }
                        </React.Fragment>
                    )}
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table className="mb-0">
                                <thead>
                                    {
                                        this.props.viewType == 'topearner'
                                            ?
                                            <tr className="dashboard-view">
                                                <th colSpan="3">Top Earner</th>
                                            </tr>
                                            :
                                            this.props.viewType == 'topredeemer' ?
                                                <tr className="dashboard-view">
                                                    <th colSpan="3">Top Redeemer</th>
                                                </tr>
                                                :
                                                <tr>
                                                    {Pathname == 'coins-distributed'
                                                        ?
                                                        <th className="left-th pl-3">Date</th>
                                                        :
                                                        <th className="left-th pl-3">Rank</th>
                                                    }
                                                    <th>Username</th>
                                                    {Pathname == 'coins-distributed' &&
                                                        <th>Event</th>
                                                    }
                                                    <th className="right-th">Coin Earned</th>
                                                </tr>
                                    }

                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(DistributedCoins, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        {Pathname == 'coins-distributed' ?
                                                            <td>
                                                                {/* <MomentDateComponent data={{ date: item.date_added, format: "D MMM YY" }} /> */}
                                                {HF.getFormatedDateTime(item.date_added, "D MMM YY")}
                                                            
                                                            </td>
                                                            :
                                                            <td>#{item.user_rank}</td>
                                                        }
                                                        <td className='cursor-p' onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.user_name}</td>
                                                        {Pathname == 'coins-distributed' &&
                                                            <td className="xtext-ellipsis">{item.message ? item.message : '--'}</td>
                                                        }
                                                        <td><img src={Images.REWARD_ICON} alt="" />
                                                            {Pathname == 'coins-distributed' ? item.points : item.coin_earned}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            )
                                        })
                                        :
                                        <tbody>
                                            <tr>
                                                <td colSpan="8">
                                                    {(Total == 0 && !DistributedPosting) ?
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
                        </Col>
                    </Row>
                    {_.isUndefined(this.props.FromDashboard) ? (
                        Total > NC.ITEMS_PERPAGE &&
                        (<div className="custom-pagination">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>)
                    )
                        :
                        this.props.viewType == 'topearner' ?
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/coins/top-earner')} className="view-all">View All</a>
                            </div>
                            :
                            <div className="view-all-box">
                                <a onClick={() => this.props.history.push('/coins/top-redeemer')} className="view-all">View All</a>
                            </div>
                    }
                </div>
            </React.Fragment>
        )
    }
}
export default withRouter(TopEarner)