import React, { Component } from 'react';
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
const dropdown_list = [
    { value: 7, label: 'Last 7 days' },
    { value: 10, label: 'Last 10 days' },
    { value: 30, label: 'Last month' },
    { value: 365, label: 'Last year' }
]
class Feedback_Leaderboard extends Component {
    constructor() {
        super()
        this.state = {
            FromDate: _isUndefined(this.props.dbCallback) ? HF.getFirstDateOfMonth() : this.props.from_date,
            ToDate: _isUndefined(this.props.dbCallback) ? new Date() : this.props.to_date,
            Total: 0,
            ListPosting: false,
            CURRENT_PAGE: 1,
            PERPAGE: _isUndefined(this.props.dbCallback) ? NC.ITEMS_PERPAGE : 10,
            Keyword: '',
            SortField: 'rank',
            IsOrder: true,
        }
        this.SearchCodeReq = _debounce(this.SearchCodeReq.bind(this), 500);
    }

    componentDidMount() {
        this.getRefRank()
    }

    handleSelect = (value) => {
        if (value != null)
            this.setState({
                SelPeriod: value.value,
                FromDate: new Date(Date.now() - value.value * 24 * 60 * 60 * 1000),
                ToDate: new Date(),
                CURRENT_PAGE: 1
            }, this.getRefRank)
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 },
            this.getRefRank
        )
    }

    getRefRank = () => {
        let { CURRENT_PAGE, PERPAGE, FromDate, ToDate, Keyword, SelPeriod, IsOrder, SortField } = this.state
        let params = {
            "current_page": CURRENT_PAGE,
            "from_date": FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            "to_date": ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
            "items_perpage": PERPAGE,
            "keyword": Keyword,
            "period": SelPeriod,
            "sort_order": IsOrder ? "ASC" : 'DESC',
            "sort_field": SortField,
            "leaderboard": "referral"
        }

        getReferralRank(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({})
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        this.getRefRank()
    }

    clearFilter = () => {
        this.setState({
            CURRENT_PAGE: 1,
            Keyword: '',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            SelPeriod: ''
        }, this.getRefRank
        )
    }

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE != current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            },
                this.getRefRank
            );
        }
    }

    sortLeaderboard = (sortfiled, IsOrder) => {
        let Order = (sortfiled == this.state.SortField) ? !IsOrder : IsOrder
        this.setState({
            SortField: sortfiled,
            IsOrder: Order,
            CURRENT_PAGE: 1,
        }, this.getRefRank
        )
    }

    render() {
        let { SelPeriod, FromDate, ToDate, UsersList, Total, ListPosting, CURRENT_PAGE, ITEMS_PERPAGE, Keyword, IsOrder, SortField, PERPAGE } = this.state
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
            max_date: new Date(),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <div className="ldr-common ldr-referral">
                <Row>
                    <Col md={12}>
                        <div className="ldr-title">Referrals</div>
                        <div className="ldr-subtitle">Leaderboard</div>
                    </Col>
                </Row>
                <Row className="mt-30">
                    <Col md={12}>
                        <div className="ldr-tot-container">
                            <div className="ldr-tot-heading">Total Referrals</div>
                            <div className="ldr-tot-count">
                                {HF.getNumberWithCommas('10584')}
                            </div>
                        </div>
                        <div className="ldr-tot-container">
                            <div className="ldr-tot-heading">Entry Fee</div>
                            <div className="ldr-tot-count">
                                {HF.getNumberWithCommas('300000')}
                            </div>
                        </div>
                        <div className="ldr-tot-container">
                            <div className="ldr-tot-heading">Site Rake</div>
                            <div className="ldr-tot-count">
                                {HF.getNumberWithCommas('3000')}
                            </div>
                        </div>
                        <div className="ldr-tot-container float-right mt-3">
                            <i className="export-list icon-export"></i>
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
                <Row className="mt-5 mb-5">
                    <Col md={12} className="table-responsive common-table">
                        <Table className="mb-0">
                            <thead>
                                <tr>
                                    <th
                                        className="pointer left-th pl-3"
                                        onClick={() => this.sortLeaderboard('rank', IsOrder)}
                                    >
                                        Rank<span className={(IsOrder && SortField === 'rank') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th
                                        className="pointer"
                                        onClick={() => this.sortLeaderboard('user_name', IsOrder)}
                                    >
                                        User Name<span className={(IsOrder && SortField === 'user_name') ? "arrow-up" : "arrow-down"}></span>
                                    </th>
                                    <th>Mobile no.</th>
                                    <th>Email id</th>
                                    <th>City</th>
                                    <th
                                        className="pointer right-th"
                                        onClick={() => this.sortLeaderboard('total_referrals', IsOrder)}
                                    >Total referrals <span className={(IsOrder && SortField === 'total_referrals') ? "arrow-up" : "arrow-down"}></span></th>
                                </tr>
                            </thead>
                            {
                                Total == 0 ?
                                    // _Map(UsersList, (item, idx) => {
                                    _times(10, (item, idx) => {
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td>#{item}</td>
                                                    <td className="ldr-uname">
                                                        <div className="ldr-img">
                                                            <img src={Images.KOL} className="img-cover" />
                                                        </div>
                                                        <a href={"/admin/#/profile/" + item.user_unique_id}>
                                                            Elijah Burns
                                                        </a>
                                                    </td>
                                                    <td>396-460-9190</td>
                                                    <td>Avtar@gmail.com</td>
                                                    <td>Angelicamouth</td>
                                                    <td>7</td>
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
                            Total > PERPAGE &&
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
export default Feedback_Leaderboard