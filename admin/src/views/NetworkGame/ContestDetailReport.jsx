import React, { Component, Fragment } from "react";
import { Row, Col, Table } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import moment from 'moment';
import HF from "../../helper/HelperFunction";
class ContestDetailReport extends Component {
    constructor(props) {
        super(props)
        this.state = {
            userAccountDetail: [],
            posting: false,
            FromDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
            ToDate: new Date(),
        };

    }

    componentDidMount() {
        this.getClientList();
    }

    getClientList = () => {
        this.setState({ posting: true })
        let { FromDate, ToDate } = this.state
        let params = {
            "from_date": FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            "to_date": ToDate ? moment(ToDate).format("YYYY-MM-DD") : ''
        }

        WSManager.Rest(NC.baseURL + NC.GET_CLIENT_CONTEST_DETAILS, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {

                let ResDetails = !_.isUndefined(responseJson.data.details) ? responseJson.data.details : [];

                let vtech = 0
                let profit_loss = 0
                let distributed = 0
                _.map(ResDetails, (item, idx) => {
                    vtech = parseFloat(item.site_rake) - parseFloat(item.commisson)

                    profit_loss = ((parseFloat(item.commisson) + parseFloat(item.winning)) - (parseFloat(item.collection)))

                    distributed = (parseFloat(item.commisson) + parseFloat(item.winning))

                    ResDetails[idx]['vinfotech'] = (vtech).toFixed(2)
                    ResDetails[idx]['profit_loss'] = (profit_loss).toFixed(2)
                    ResDetails[idx]['distributed'] = (distributed).toFixed(2)
                })


                this.setState({
                    userAccountDetail: ResDetails,
                })
            }
            this.setState({ posting: false })
        })
    }

    getSubByKey = (objects, key_n) => {
        if (!_.isEmpty(objects) && !_.isEmpty(key_n)) {
            return _.sumBy(objects, x => parseFloat(x[key_n]));
        } else {
            return 0;
        }
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            this.getClientList()
        })
    }

    render() {
        const { CURRENT_PAGE, PERPAGE, userAccountDetail, posting, FromDate, ToDate } = this.state
        return (
            <div className="client-dtl">
                <div className="manage-user-heading clearfix">
                    <h2 className="h2-cls">Account Statement</h2>
                </div>
                <Row className="filter-userlist">
                    <Col md={4}>
                        <div className="member-box float-left mr-1">
                            <label className="filter-label">From Date</label>
                            <DatePicker
                                maxDate={new Date()}
                                className="filter-date"
                                showYearDropdown='true'
                                selected={FromDate}
                                onChange={e => this.handleDateFilter(e, "FromDate")}
                                placeholderText="From"
                            />
                        </div>
                        <div className="member-box float-left">
                            <label className="filter-label">To Date</label>
                            <DatePicker
                                maxDate={new Date()}
                                className="filter-date"
                                showYearDropdown='true'
                                selected={ToDate}
                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                placeholderText="To"
                            />
                        </div>
                    </Col>
                    <Col md={8}>
                        <div className="ng-t-box">
                            <div className="ng-t-item">
                                <span>Vinfotech (Receivable/Payable)</span>
                                <span className="float-right">
                                    {parseFloat(this.getSubByKey(userAccountDetail, 'profit_loss')).toFixed(2)}
                                </span>
                            </div>
                            <div className="ng-t-item mt-2">
                                <span>Collection</span>
                                <span className="float-right">
                                    {parseFloat(this.getSubByKey(userAccountDetail, 'collection')).toFixed(2)}
                                </span>
                            </div>
                            <div className="ng-t-item mt-3">
                                <span>Total</span>
                                <span className="float-right">
                                    {(parseFloat(this.getSubByKey(userAccountDetail, 'profit_loss')) + parseFloat(this.getSubByKey(userAccountDetail, 'collection'))).toFixed(2)}
                                </span>
                            </div>
                        </div>
                        <div className="ng-t-box ml-4">
                            <div className="ng-t-item">
                                <span>Profit</span>
                                <span className="float-right">
                                    {parseFloat(this.getSubByKey(userAccountDetail, 'commisson')).toFixed(2)}
                                </span>
                            </div>
                            <div className="ng-t-item mt-2">
                                <span>User winnings</span>
                                <span className="float-right">
                                    {parseFloat(this.getSubByKey(userAccountDetail, 'winning')).toFixed(2)}
                                </span>
                            </div>
                            <div className="ng-t-item mt-3">
                                <span>Total</span>
                                <span className="float-right">
                                    {(parseFloat(this.getSubByKey(userAccountDetail, 'commisson')) + parseFloat(this.getSubByKey(userAccountDetail, 'winning'))).toFixed(2)}
                                </span>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="user-list">
                    <Col className="md-12 table-responsive cd-d-center">
                        <Table>
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Entries</th>
                                    <th>Collection</th>
                                    <th>Rake</th>
                                    <th>Pool</th>
                                    <th>Commision</th>
                                    <th>Winning</th>
                                    <th>Receivable/Payble</th>
                                </tr>
                            </thead>
                            {
                                userAccountDetail.length > 0 ?
                                    _.map(userAccountDetail, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="cl-dtl">
                                                        <div className="cl-p-name">{item.collection_name ? item.collection_name : '--'}</div>
                                                        <div className="cl-p-name">{HF.getFormatedDateTime(item.season_scheduled_date, 'D-MMM-YYYY hh:mm A')}</div>
                                                    </td>
                                                    <td>{item.entries ? parseFloat(item.entries).toFixed(2) : '--'}</td>
                                                    <td>{item.collection ? parseFloat(item.collection).toFixed(2) : '--'}</td>
                                                    <td>{item.site_rake ? parseFloat(item.site_rake).toFixed(2) : '--'}</td>
                                                    <td>{item.pool ? parseFloat(item.pool).toFixed(2) : '--'}</td>
                                                    <td>{item.commisson ? parseFloat(item.commisson).toFixed(2) : '--'}</td>
                                                    <td>{item.winning ? parseFloat(item.winning).toFixed(2) : '--'}</td>
                                                    <td className={Number.parseFloat(item.profit_loss) >= 0 ? ' text-green' : ' text-red'}>
                                                        {item.profit_loss ? item.profit_loss : '--'}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="10">
                                                {(userAccountDetail.length == 0 && !posting) ?
                                                    <div className="no-records">
                                                        No Record Found.</div>
                                                    :
                                                    <Loader />
                                                }
                                            </td>
                                        </tr>
                                    </tbody>
                            }
                            {
                                userAccountDetail.length > 0 &&
                                <Fragment>
                                    <tbody>
                                        <tr>
                                            <td className="font-weight-bold">Total</td>

                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'entries')).toFixed(2)}
                                            </td>
                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'collection')).toFixed(2)}
                                            </td>
                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'site_rake')).toFixed(2)}
                                            </td>
                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'pool')).toFixed(2)}
                                            </td>
                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'commisson')).toFixed(2)}
                                            </td>
                                            <td colSpan="1" className="font-weight-bold">
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'winning')).toFixed(2)}
                                            </td>
                                            <td
                                                colSpan="1"
                                                className="font-weight-bold"
                                            >
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'profit_loss')).toFixed(2)}
                                            </td>
                                        </tr>
                                    </tbody>
                                    {/* <tbody className="ng-total-earned">
                                        <tr>
                                            <td colSpan="2"></td>
                                            <td>Total Earned</td>
                                            <td>{parseFloat(this.getSubByKey(userAccountDetail, 'collection')).toFixed(2)}</td>

                                            <td>Total Distributed</td>
                                            <td>
                                                {parseFloat(this.getSubByKey(userAccountDetail, 'distributed')).toFixed(2)}
                                            </td>

                                            <td>Profit/Loss</td>
                                            <td>

                                                {parseFloat(parseFloat(this.getSubByKey(userAccountDetail, 'collection')).toFixed(2) - parseFloat(this.getSubByKey(userAccountDetail, 'distributed')).toFixed(2)).toFixed(2)}

                                            </td>                                           
                                        </tr>
                                    </tbody> */}
                                </Fragment>
                            }
                        </Table>
                        {
                            userAccountDetail.length > PERPAGE &&
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={userAccountDetail.length}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        }
                    </Col>
                </Row>
            </div >
        )
    }
}
export default ContestDetailReport
