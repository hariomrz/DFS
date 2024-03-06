import React, { Component, Fragment } from 'react';
import { Row, Col, Input, Button, Table } from 'reactstrap';
import SelectDropdown from "../../components/SelectDropdown";
import SelectDate from "../../components/SelectDate";
import HF, { _Map, _isEmpty, _debounce, _times, _isUndefined } from "../../helper/HelperFunction";
import * as NC from '../../helper/NetworkingConstants';
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import { getReferralRank } from '../../helper/WSCalling';
import { notify } from 'react-notify-toast';
import Images from "../../components/images";
import queryString from 'query-string';
import moment from "moment-timezone";
import WSManager from '../../helper/WSManager';
const dropdown_list = [
    { value: 7, label: 'Last 7 days' },
    { value: 10, label: 'Last 10 days' },
    { value: 30, label: 'Last month' },
    { value: 365, label: 'Last year' }
]
class TopTeam_Leaderboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            FromDate: _isUndefined(this.props.dbCallback) ? HF.getFirstDateOfMonth() : this.props.from_date,
            ToDate: _isUndefined(this.props.dbCallback) ? new Date(moment().format('D MMM YYYY')) : this.props.to_date,
            ListPosting: false,
            CURRENT_PAGE: 1,
            PERPAGE: _isUndefined(this.props.dbCallback) ? NC.ITEMS_PERPAGE : 10,
            Keyword: '',
            SortField: 'rank_value',
            IsOrder: true,            
            BackBtn: false
        }
        this.SearchCodeReq = _debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount() {

        if (!_isUndefined(this.props.location)) {
            let qString = queryString.parse(this.props.location.search)
            if (qString.rdr)
                this.setState({ BackBtn: true })
        }

        this.getUserList()
    }

    handleSelect = (value) => {
        if (value != null)
            this.setState({
                SelPeriod: value.value, FromDate: new Date(Date.now() - value.value * 24 * 60 * 60 * 1000),
                ToDate: new Date(),
                CURRENT_PAGE: 1
            }, this.getUserList)
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 },
            this.getUserList
        )
    }

    getUserList = () => {
        let { CURRENT_PAGE, PERPAGE, Keyword, SelPeriod, IsOrder, SortField, FromDate, ToDate } = this.state
        let params = {
            "current_page": CURRENT_PAGE,
            "from_date": FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            "to_date": ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            "items_perpage": PERPAGE,
            "keyword": Keyword,
            "period": SelPeriod,
            "sort_order": IsOrder ? "ASC" : 'DESC',
            "sort_field": SortField,
            "leaderboard": "team"
        }

        getReferralRank(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    UsersList: ResponseJson.data.result ? ResponseJson.data.result : [],
                    Total: ResponseJson.data.total ? ResponseJson.data.total : 0,
                    ListPosting: false,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value, CURRENT_PAGE: 1 }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getUserList()
    }

    clearFilter = () => {
        this.setState({
            CURRENT_PAGE: 1,
            Keyword: '',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(moment().format('D MMM YYYY')),
            SelPeriod: ''
        }, this.getUserList
        )
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            },
                this.getUserList
            );
        }
    }

    sortLeaderboard = (sortfiled, IsOrder) => {
        let Order = (sortfiled == this.state.SortField) ? !IsOrder : IsOrder
        this.setState({
            SortField: sortfiled,
            IsOrder: Order,
            CURRENT_PAGE: 1,
        }, this.getUserList
        )
    }

    exportUser = () => {
        let { Keyword, FromDate, ToDate } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        if (FromDate != '' && ToDate != '') {
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }

        var query_string = '&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&leaderboard=team&csv=true';
        var export_url = 'adminapi/dashboard/get_referral_rank?';


        // console.log('query_string', query_string)
        HF.exportFunction(query_string, export_url)
    }

    render() {
        let { SelPeriod, UsersList, Total, ListPosting, CURRENT_PAGE, PERPAGE, Keyword, IsOrder, SortField, FromDate, ToDate, BackBtn } = this.state
        let { dbCallback } = this.props
        var todaysDate = moment().format('D MMM YYYY');
        const Select_Props = {
            is_disabled: false,
            is_searchable: false,
            is_clearable: false,
            menu_is_open: false,
            class_name: "ldr-select",
            sel_options: dropdown_list,
            place_holder: "Select Period",
            selected_value: SelPeriod,
            modalCallback: this.handleSelect
        }
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'filter-date mr-3',
            year_dropdown: true,
            month_dropdown: true,
        }
        const FromDateProps = {
            ...sameDateProp,
            min_date: false,
            max_date: new Date(ToDate),
            sel_date: new Date(FromDate),
            date_key: 'FromDate',
            place_holder: 'From Date',
        }
        const ToDateProps = {
            ...sameDateProp,
            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <div className="ldr-common ldr-referral">
                {
                    _isUndefined(dbCallback) &&
                    <Fragment>
                        <Row>
                            <Col md={12}>
                                <div className="clearfix">
                                    <div className="ldr-title">Top Teams</div>
                                    {BackBtn && <div onClick={() => this.props.history.push('/dashboard')} className="back-btn" id="backbtn">{'< '}Back</div>}
                                </div>
                                <div className="ldr-subtitle">Leaderboard</div>
                            </Col>
                        </Row>
                        <Row className="mt-30">
                            <Col md={12}>
                                <div className="ldr-tot-container float-right mt-3">
                                    <i
                                        className="export-list icon-export"
                                        onClick={e => this.exportUser()}></i>
                                </div>
                            </Col>
                        </Row>
                        <Row className="ldr-fil-box">
                            <Col md={3}>
                                <label htmlFor="ldr-search">Search</label>
                                <Input
                                    type="text"
                                    name="search"
                                    className="ldr-search"
                                    placeholder="Username, mobile no, city, email"
                                    value={Keyword}
                                    onChange={this.searchByUser}
                                />
                            </Col>
                            <Col md={3}>
                                <label htmlFor="ldr-search">Select Period</label>
                                <SelectDropdown SelectProps={Select_Props} />
                            </Col>
                            <Col md={4}>
                                <div className="xfloat-left">
                                    <Row>
                                        <Col md={6} className="pr-0">
                                            <div className="float-right">
                                                <label className="filter-label">Date</label>
                                                <SelectDate DateProps={FromDateProps} />
                                            </div>
                                        </Col>
                                        <Col md={6} className="pl-0 mt-4">
                                            <div className="float-left">
                                                <SelectDate DateProps={ToDateProps} />
                                            </div>
                                        </Col>
                                    </Row>
                                </div>
                            </Col>
                            <Col md={2}>
                                <div className="mt-30 float-right">
                                    <Button
                                        className="btn-secondary"
                                        onClick={() => this.clearFilter()}>
                                        Clear Filters
                            </Button>
                                </div>
                            </Col>
                        </Row>
                    </Fragment>
                }
                <Row className={`mb-5 ${_isUndefined(dbCallback) ? 'mt-5' : ''}`}>
                    <Col className={`table-responsive common-table ${_isUndefined(dbCallback) ? '' : 'col-md-12'}`}>
                        <Table className="mb-0">
                            <thead>
                                <tr>
                                    <th
                                        className="pointer left-th pl-3"
                                        onClick={() => this.sortLeaderboard('rank_value', IsOrder)}
                                    >
                                        Rank<span className={(IsOrder && SortField === 'rank_value') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th
                                        className="pointer float-left"
                                        onClick={() => this.sortLeaderboard('user_name', IsOrder)}
                                    >
                                        User Name<span className={(IsOrder && SortField === 'user_name') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th>Mobile no.</th>
                                    <th>Email id</th>
                                    <th>City</th>
                                    <th
                                        className="pointer right-th"
                                        onClick={() => this.sortLeaderboard('total_team', IsOrder)}
                                    >Team <span className={(IsOrder && SortField === 'total_team') ? "arrow-up" : "arrow-down"}></span></th>
                                </tr>
                            </thead>
                            {
                                Total > 0 ?
                                    _Map(UsersList, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>#{item.rank_value}</td>
                                                    <td className="ldr-uname">
                                                        <div className="ldr-img">
                                                            <img src={(item.image) ? NC.S3 + NC.THUMB + item.image : Images.no_image} className="img-cover" />
                                                        </div>
                                                        <a href={"/admin/#/profile/" + item.user_unique_id}>{item.user_name}</a>
                                                    </td>
                                                    <td>{item.phone}</td>
                                                    <td>{item.email}</td>
                                                    <td>{item.city}</td>
                                                    <td>{item.total_team}</td>
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                    :
                                    <tbody>
                                        <tr>
                                            <td colSpan="8">
                                                {(Total == 0 && !ListPosting) ?
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
                <Row>
                    <Col md={12}>
                        {
                            _isUndefined(dbCallback) &&
                            (Total > PERPAGE) &&
                            (<div className="custom-pagination float-right mt-5">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={Total}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>)
                        }
                    </Col>
                </Row>
            </div>
        )
    }
}
export default TopTeam_Leaderboard