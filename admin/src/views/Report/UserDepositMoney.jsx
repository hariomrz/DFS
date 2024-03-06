import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import Select from 'react-select';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Pagination from "react-js-pagination";
import Loader from '../../components/Loader';
import HF from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
import moment from "moment-timezone";
const TrStatusOptions = [
    { value: '', label: 'All' },
    { value: '0', label: 'Pending' },
    { value: '1', label: 'Success' },
    { value: '2', label: 'Failed' }
]

export default class UserDepositMoney extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            SelectedPaymentType: { value: "", label: "All" },
            PaymentType: [],
            TotalDeposit: '',
            posting: false,
            TrStatusChange: '',
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        let s = { value: "", label: "All" }
        if (HF.allowCryto() == '1') {
            s = { value: "15", label: "Crypto" }
        }
        if (HF.allowBTC() == '1') {
            s = { value: "19", label: "BTCPay" }
        }
        this.setState({
            SelectedPaymentType: s,
        }, () => {
            this.getReportUser()
            this.getPaymentFilter()
        })
        // this.getReportUser()
        // this.getPaymentFilter()
    }
    getPaymentFilter = () => {

        let params = {}
        WSManager.Rest(NC.baseURL + NC.GET_DEPOSIT_AMOUNT_FILTER_DATA, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []
                Temp.push({
                    value: '', label: "All"
                })
                _.map(ResponseJson.data, (item, idx) => {
                    Temp.push({
                        value: idx, label: item
                    })
                })
                this.setState({
                    PaymentType: Temp
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getReportUser = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, TrStatusChange } = this.state
        let params = {
            status: TrStatusChange.value,
            items_perpage: PERPAGE,
            total_items: 0,
            current_page: CURRENT_PAGE,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            csv: false,
            // from_date: FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '',
            // to_date: ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '',
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
            payment_method: SelectedPaymentType.value
        }
        WSManager.Rest(NC.baseURL + NC.GET_REPORT_USER_DEPOSIT_AMOUNT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    UserReportList: ResponseJson.data.result,
                    TotalUser: ResponseJson.data.total,
                    TotalDeposit: ResponseJson.data.total_deposit
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Post = () => {
        const { Keyword, FromDate, ToDate, sortField, isDescOrder, SelectedPaymentType, TrStatusChange } = this.state
        let params = {
            status: TrStatusChange.value,
            sort_order: isDescOrder ? "ASC" : 'DESC',
            sort_field: sortField,
            // from_date: FromDate,
            // to_date: ToDate,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            keyword: Keyword,
            report_type: "user_deposit",
            payment_method: SelectedPaymentType.value
        }

        WSManager.Rest(NC.baseURL + NC.EXPORT_REPORT, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                notify.show(ResponseJson.message, "success", 5000);
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    exportReport_Get = () => {
        let { Keyword, FromDate, ToDate, isDescOrder, sortField, TrStatusChange, SelectedPaymentType } = this.state
        let tempFromDate = ''
        let tempToDate = ''
        let sOrder = isDescOrder ? "ASC" : 'DESC'
        if (FromDate != '' && ToDate != '') {
            // tempFromDate = FromDate ? HF.getFormatedDateTime(FromDate, 'YYYY-MM-DD') : '';
            // tempToDate = ToDate ? HF.getFormatedDateTime(ToDate, 'YYYY-MM-DD') : '';
            tempFromDate = WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD')
            tempToDate = moment(ToDate).format("YYYY-MM-DD");
        }
        let status_val = !_.isUndefined(TrStatusChange.value) ? TrStatusChange.value : ''
        let payment_val = !_.isUndefined(SelectedPaymentType.value) ? SelectedPaymentType.value : ''

        var query_string = '&report_type=user_deposit&csv=1&keyword=' + Keyword + '&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + sOrder + '&sort_field=' + sortField + '&status=' + status_val + '&payment_method=' + payment_val;
        var export_url = 'adminapi/index.php/report/get_report_user_deposit_amount?';

        // console.log('query_string', query_string)

        HF.exportFunction(query_string, export_url)
    }

    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value }, this.getReportUser)
    }


    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getReportUser()
            }
        })
    }



    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getReportUser();
        });
    }
    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2)
            this.getReportUser()
    }
    clearFilter = () => {
        this.setState({
            SelectedPaymentType: { value: "", label: "All" },
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name'
        }, () => {
            this.getReportUser()
        }
        )
    }
    sortContest(sortfiled, isDescOrder) {
        let Order = sortfiled == this.state.sortField ? !isDescOrder : isDescOrder
        this.setState({
            sortField: sortfiled,
            isDescOrder: Order,
            CURRENT_PAGE: 1,

        }, this.getReportUser)
    }
    render() {
        const { UserReportList, CURRENT_PAGE, PERPAGE, TotalUser, Keyword, isDescOrder, SelectedPaymentType, PaymentType, TotalDeposit, posting, FromDate, ToDate, TrStatusChange } = this.state
        var todaysDate = moment().format('D MMM YYYY');
        
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'form-control mr-3',
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
            <Fragment>
                <div className="animated fadeIn mt-4">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">User Deposit Amount</h1>
                        </Col>
                    </Row>
                    <div className="user-deposit-amount">

                        <Row className="xfilter-userlist mt-5">
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Payment Method</label>
                                    <Select
                                        disabled={HF.allowCryto() == '1' ? true : HF.allowBTC() == '1' ? true : false}
                                        isSearchable={true}
                                        class="form-control"
                                        options={PaymentType}
                                        menuIsOpen={true}
                                        value={SelectedPaymentType}
                                        onChange={e => this.handleTypeChange(e, 'SelectedPaymentType')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Status</label>
                                    <Select
                                        isSearchable={true}
                                        class="form-control"
                                        options={TrStatusOptions}
                                        placeholder="Transaction Status"
                                        menuIsOpen={true}
                                        value={TrStatusChange}
                                        onChange={e => this.handleTypeChange(e, 'TrStatusChange')}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div className="search-box">
                                    <label className="filter-label">Search User</label>
                                    <Input
                                        placeholder="Search User"
                                        name='code'
                                        value={Keyword}
                                        onChange={this.searchByUser}
                                    />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select From Date</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                            </Col>
                            <Col md={2}>
                                <div>
                                    <label className="filter-label">Select To Date</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </Col>
                            <Col md={2}>
                                <label className="filter-label">Total Deposit</label>
                                <h4>{TotalDeposit}</h4>
                            </Col>


                        </Row>
                        <Row className="filters-box TopBot">
                            <Col md={8}>
                                <div className="filters-area">
                                    <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                                </div>
                            </Col>

                            <Col md={2} className="">
                                <i className="export-list icon-export"
                                    onClick={e => (TotalUser > NC.EXPORT_REPORT_LIMIT) ? this.exportReport_Post() : this.exportReport_Get()}></i>
                            </Col>
                        </Row>
                        <Row className="filters-box">
                            <Col md={12}>
                                <div className="filters-area">
                                    <h4>Total Record:{TotalUser}</h4>
                                </div>
                            </Col>
                        </Row>
                        <Row className="filters-box">

                        </Row>

                        <Row>
                            <Col md={12} className="table-responsive common-table">
                                <Table>
                                    <thead>
                                        <tr>
                                            <th>Order Id</th>
                                            <th>Uniqe ID</th>
                                            <th className="pointer" onClick={() => this.sortContest('user_name', isDescOrder)}>UserName</th>
                                            <th className="pointer" onClick={() => this.sortContest('first_name', isDescOrder)}>Name</th>
                                            <th className="pointer" onClick={() => this.sortContest('phone', isDescOrder)}>Phone</th>
                                            <th className="pointer" onClick={() => this.sortContest('email', isDescOrder)}>Email</th>
                                            <th>Transaction Id</th>
                                            <th className="pointer" onClick={() => this.sortContest('payment_request', isDescOrder)}>Request Amount</th>
                                            <th className="pointer" onClick={() => this.sortContest('payment_gateway_id', isDescOrder)}>Payment Mode</th>
                                            <th className="pointer" onClick={() => this.sortContest('O.date_added', isDescOrder)}>Transaction Date </th>
                                            <th className="pointer" onClick={() => this.sortContest('added_date', isDescOrder)}>Member Since</th>
                                            <th className="pointer" onClick={() => this.sortContest('last_deposit_date', isDescOrder)}>Status</th>
                                        </tr>
                                    </thead>
                                    {
                                        UserReportList.length > 0 ?
                                            _.map(UserReportList, (item, idx) => {
                                                return (
                                                    <tbody key={idx}>
                                                        <tr>
                                                            <td>{item.order_unique_id}</td>
                                                            <td>{item.user_unique_id}</td>
                                                            <td><a className="pointer" style={{ textDecoration: 'underline' }} onClick={() => this.props.history.push({ pathname: "/profile/" + item.user_unique_id, state: { activeTabId: '1' } })}>{item.user_name}</a></td>
                                                            <td>{item.name}</td>
                                                            <td>{item.phone}</td>
                                                            <td><a className="pointer" style={{ textDecoration: 'underline' }} onClick={() => this.props.history.push("/profile/" + item.user_unique_id)}>{item.email}</a></td>
                                                            <td>{item.txn_id}</td>
                                                            <td>{item.payment_request}</td>
                                                            <td>{item.payment_method ? item.payment_method : '--'}</td>
                                                            <td>
                                                                {HF.getFormatedDateTime(item.order_date_added, 'D-MMM-YYYY hh:mm A')}
                                                            </td>
                                                            <td>
                                                                {/* {WSManager.getUtcToLocalFormat(item.added_date, 'D-MMM-YYYY')} */}
                                                                {HF.getFormatedDateTime(item.added_date, 'D-MMM-YYYY')}
                                                            </td>

                                                            <td>
                                                                {
                                                                    item.status == 0
                                                                        ?
                                                                        <i className="icon-verified" title='Not yet' />
                                                                        :
                                                                        item.status == 1
                                                                            ?
                                                                            <i className="icon-verified text-green" title='Payment Processed Done' />
                                                                            :
                                                                            <i className="icon-inactive text-red" title={(item.source == 8) ? 'Rejected' : 'Failed'} />
                                                                }
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                )
                                            })
                                            :
                                            <tbody>
                                                <tr>
                                                    <td colSpan='22'>
                                                        {(UserReportList.length == 0 && !posting) ?
                                                            <div className="no-records">No Record Found.</div>
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
                        {TotalUser > PERPAGE && (
                            <div className="custom-pagination lobby-paging">
                                <Pagination
                                    activePage={CURRENT_PAGE}
                                    itemsCountPerPage={PERPAGE}
                                    totalItemsCount={TotalUser}
                                    pageRangeDisplayed={5}
                                    onChange={e => this.handlePageChange(e)}
                                />
                            </div>
                        )
                        }
                    </div>


                </div>
            </Fragment>
        )
    }
}