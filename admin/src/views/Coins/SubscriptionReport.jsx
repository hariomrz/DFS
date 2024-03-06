import React, { Fragment } from "react";
import { Row, Col, Table, Input } from 'reactstrap';
import _ from 'lodash';
import Loader from '../../components/Loader';
import * as NC from '../../helper/NetworkingConstants';
import { withRouter } from 'react-router'
import HF, { _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import SelectDropdown from "../../components/SelectDropdown";
import Pagination from "react-js-pagination";
import { MODULE_NOT_ENABLE } from "../../helper/Message";
import { notify } from 'react-notify-toast';
import WSManager from '../../helper/WSManager';
import { MomentDateComponent } from "../../components/CustomComponent";
var createReactClass = require('create-react-class');
var SubscriptionReport = createReactClass({
    getInitialState: function () {
        return {
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            PackageType: '',
            CURRENT_PAGE: 1,
            PERPAGE: NC.ITEMS_PERPAGE,
            ReportList : [],
            sortField: 'subscription_id',
            isDescOrder: 'true',
            ListPosting : false,
            keyword : '',
            TotalEarn : 0,
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    },
    componentDidMount: function () {
        if (HF.allowSubscription() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }
        this.getReportList()
    },

    getReportList: function () {
        this.setState({ ListPosting: true })
        let { PERPAGE, CURRENT_PAGE, activeTab, keyword, isDescOrder, sortField, FromDate, ToDate, PackageType } = this.state
        let params =
        {
            "keyword": keyword,
            "current_page": CURRENT_PAGE,
            "items_perpage": PERPAGE,
            "sort_field": sortField,
            "sort_order": isDescOrder ? 'DESC' : 'ASC',
            "csv": false,
            "from_date": HF.getDateFormat(FromDate, 'YYYY-MM-DD'),
            "to_date": HF.getDateFormat(ToDate, 'YYYY-MM-DD'),
            "subscription_id": PackageType,
        }

        WSManager.Rest(NC.baseURL + NC.SC_GET_SUBSCRIPTION_REPORT, params).then(Response => {
            if (Response.response_code == NC.successCode) {
                this.setState({
                    ListPosting: false,
                    ReportList: Response.data ? Response.data.result : [],
                    Total: Response.data ? Response.data.total : '',
                    TotalEarn: Response.data ? Response.data.total_earn : 0,
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, 'error', 5000)
        })
    },

    handlePackChange: function (value) {
        this.setState({ PackageType: value.value, CURRENT_PAGE : 1 }, () => {
            this.getReportList()
        })
    },

    exportReport_Get: function () {
        let { keyword, FromDate, ToDate, isDescOrder, sortField, PackageType } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
        }  
        var query_string = '&csv=true&keyword=' + keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&subscription_id=' + PackageType;
        var export_url = 'adminapi/subscription/get_subscription_report?';

        HF.exportFunction(query_string, export_url)
    },

    handleDate: function (date, dateType) {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 }, () => {
            this.getReportList()
        })
    },

    handleSearch: function(e) {
        if (!_.isNull(e)) {
            this.setState({ CURRENT_PAGE: 1, keyword: e.target.value }, () => {
                this.SearchCodeReq()
            })
        }
    },

    SearchCodeReq: function() {
        this.getReportList()
    },

    sortByColumn: function(sortfiled, isDescOrder) {
        let Order = isDescOrder ? false : true
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getReportList)
    },

    handlePageChange(current_page) {
        if (this.state.CURRENT_PAGE !== current_page) {
            this.setState({
                CURRENT_PAGE: current_page
            }, () => {
                this.getReportList()
            });
        }
    },

    render() {
        let { ReportList, FromDate, ToDate, PackageType, Total, PERPAGE, CURRENT_PAGE, isDescOrder, sortField, ListPosting, TotalEarn } = this.state
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'filter-date',
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

        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: this.props.package_opt,
            place_holder: "Select",
            selected_value: PackageType,
            modalCallback: this.handlePackChange
        }
        return (
            <Fragment>
                <Row className="sub-report">
                    <Col md={12}>
                        <div className="sr-filter">
                            <label className="filter-label">Select Package</label>
                            <SelectDropdown SelectProps={Select_Props} />
                        </div>
                        <div className="sr-filter sr-date">
                            <div className="float-left mr-2">
                                <label className="filter-label">Date</label>
                                <SelectDate DateProps={FromDateProps} />
                            </div>
                            <div className="float-left">
                                <SelectDate DateProps={ToDateProps} />
                            </div>
                        </div>
                        <div className="sr-filter">
                            <label className="filter-label">Search</label>
                            <Input
                                name="search-user"
                                id="search-user"
                                placeholder="Username"
                                onChange={e => this.handleSearch(e)}
                            />
                        </div>
                        <div className="sr-filter sr-totals">
                            <div className="float-right">
                                <div className="tr-count">Total record count : {Total}</div>
                                <div>
                                    <span className="t-earn">Total Earning : {HF.getCurrencyCode()}{TotalEarn}</span>
                                    <i className="export-list icon-export"
                                        onClick={e => this.exportReport_Get()}></i>
                                </div>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row>
                    <Col md={12}>
                        <div className="table-responsive common-table mt-30">
                            <Table>
                                <thead>
                                    <tr>
                                        <th onClick={() => this.sortByColumn('name', isDescOrder)}>
                                            Package
                                                <div className={`d-inline-block ${(sortField === 'name' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape"></i>
                                            </div>
                                        </th>
                                        <th onClick={() => this.sortByColumn('user_name', isDescOrder)}>
                                            User name
                                            <div className={`d-inline-block ${(sortField === 'user_name' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape"></i>
                                            </div>
                                        </th>
                                        <th>Email id</th>
                                        <th onClick={() => this.sortByColumn('order_date', isDescOrder)}>
                                            Transaction date
                                            <div className={`d-inline-block ${(sortField === 'order_date' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape"></i>
                                            </div>
                                        </th>
                                        <th onClick={() => this.sortByColumn('amount', isDescOrder)}>
                                            Value
                                            <div className={`d-inline-block ${(sortField === 'amount' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape"></i>
                                            </div>
                                        </th>
                                        <th onClick={() => this.sortByColumn('coins', isDescOrder)}>
                                            Coin Earned
                                            <div className={`d-inline-block ${(sortField === 'coins' && isDescOrder) ? '' : 'rotate-icon'}`}>
                                                <i className="icon-Shape"></i>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                {
                                    Total > 0 ?
                                        _.map(ReportList, (item, idx) => {
                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{item.name}</td>
                                                        <td>{item.user_name}</td>
                                                        <td>{item.email}</td>
                                                        <td>
                                                            {/* <MomentDateComponent data={{ date: item.order_date, format: "D-MMM-YYYY hh:mm A" }} /> */}
                                                {HF.getFormatedDateTime(item.order_date, "D-MMM-YYYY hh:mm A")}

                                                        </td>
                                                        <td>
                                                            {HF.getCurrencyCode() + item.amount}
                                                        </td>
                                                        <td>{item.coins}</td>
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
                        </div>
                    </Col>
                </Row>
                {Total > PERPAGE && (
                    <div className="custom-pagination lobby-paging">
                        <Pagination
                            activePage={CURRENT_PAGE}
                            itemsCountPerPage={PERPAGE}
                            totalItemsCount={Total}
                            pageRangeDisplayed={5}
                            onChange={e => this.handlePageChange(e)}
                        />
                    </div>
                )}
            </Fragment>
        )
    }
})

export default withRouter(SubscriptionReport)
