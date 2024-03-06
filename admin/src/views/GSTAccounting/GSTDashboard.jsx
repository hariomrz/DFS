import React, { Component } from "react";
import { Row, Col } from 'reactstrap';
import _ from 'lodash';
import * as NC from "../../helper/NetworkingConstants";
import WSManager from "../../helper/WSManager";
import Select from 'react-select';
import { notify } from 'react-notify-toast';
import moment from 'moment';
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { MODULE_NOT_ENABLE } from "../../helper/Message";
import HF from '../../helper/HelperFunction';
export default class GSTDashboard extends Component {
    constructor(props) {
        super(props)
        this.state = {
            TotalUser: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            CURRENT_PAGE: 1,
            startDate: '',
            endDate: '',
            FromDate: HF.getFirstDateOfMonth(),
            ToDate: new Date(),
            UserReportList: [],
            Keyword: '',
            sortField: 'first_name',
            isDescOrder: true,
            SelectedPaymentType: { value: 0, label: "Select Type" },
            SelectedState: { value: 0, label: "Select State" },
            PaymentType: [],
            TotalDeposit: '',
            posting: false,
            GstStateList: [],
            GstFixture: [],
            GstContest: [],
            GstReportList: [],
            TableFields: [],
            TotalCount: [],
            Dashboardliabilities: [],
            DashboardProfitLoss: [],
            DashboardExpenses: [],
            DashboardAssets: []
        }
        this.SearchCodeReq = _.debounce(this.SearchCodeReq.bind(this), 500);
    }
    componentDidMount() {
        if (HF.allowGst() != '1') {
            notify.show(MODULE_NOT_ENABLE, 'error', 5000)
            this.props.history.push('/dashboard')
        }

        this.getStateList()
        this.getDashboard()
    }

    getDashboard = () => {
        this.setState({ posting: true })
        const { FromDate, ToDate, SelectedState } = this.state

        let params = {
            state: SelectedState.value,
            from_date: FromDate ? moment(FromDate).format("YYYY-MM-DD") : '',
            to_date: ToDate ? moment(ToDate).format("YYYY-MM-DD") : '',

        }
        WSManager.Rest(NC.baseURL + NC.GET_DASHBOARD, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    posting: false,
                    Dashboardliabilities: ResponseJson.data.liabilities,
                    DashboardProfitLoss: ResponseJson.data.profit_loss,
                    DashboardAssets: ResponseJson.data.assets,
                    DashboardExpenses: ResponseJson.data.expenses,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getStateList = () => {
        this.setState({ posting: true })
        let params = {
            'master_country_id': 101
        }
        WSManager.Rest(NC.baseURL + NC.GET_STATE_LIST, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                const Temp = []

                _.map(ResponseJson.data.state_list, (item, idx) => {
                    Temp.push({
                        value: item.master_state_id, label: item.state_name
                    })
                })
                this.setState({ GstStateList: Temp })
            }
            else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    handleTypeChange = (value, name) => {

        if (value != null)
            this.setState({ [name]: value }, this.getDashboard)
    }

    handleStateChange = (value, name) => {
        this.setState({ GstReportList: [] })
        if (value != null)
            this.setState({ [name]: value }, this.getDashboard)
    }

    handleDateFilter = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate && this.state.ToDate) {
                this.getDashboard()

            }
        })
    }

    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getDashboard();
        });
    }

    searchByUser = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchCodeReq)
    }

    SearchCodeReq() {
        if (this.state.Keyword.length > 2)
            this.getDashboard()
    }

    clearFilter = () => {
        this.setState({
            FromDate: '',
            ToDate: '',
            Keyword: '',
            isDescOrder: true,
            sortField: 'first_name'
        }, () => {
            this.getDashboard()
        }
        )
    }

    render() {

        const { GstStateList, Dashboardliabilities, DashboardProfitLoss, DashboardExpenses, DashboardAssets, SelectedState } = this.state
        return (
            <div className="gst-dashboard">
                <Row className="xfilter-userlist mt-5">
                    <Col md={2}>
                        <div>
                            <label className="filter-label">From Date</label>
                            <DatePicker
                                maxDate={new Date(this.state.ToDate)}
                                className="form-control"
                                showYearDropdown='true'
                                selected={this.state.FromDate}
                                onChange={e => this.handleDateFilter(e, "FromDate")}
                                placeholderText="From"
                                dateFormat = 'dd/MM/yyyy'
                            />
                        </div>
                    </Col>
                    <Col md={2}>
                        <div>
                            <label className="filter-label">To Date</label>
                            <DatePicker
                                minDate={new Date(this.state.FromDate)}
                                maxDate={new Date()}
                                className="form-control"
                                showYearDropdown='true'
                                selected={this.state.ToDate}
                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                placeholderText="To"
                                dateFormat = 'dd/MM/yyyy'
                            />
                        </div>
                    </Col>
                    <Col md={2}>
                        <div className="search-box">
                            <label className="filter-label">State Filter</label>
                            <Select
                                isSearchable={true}
                                class="form-control"
                                options={GstStateList}
                                menuIsOpen={true}
                                value={SelectedState}
                                onChange={e => this.handleStateChange(e, 'SelectedState')}
                            />
                        </div>
                    </Col>
                </Row>
                <Row className="mt-5">
                    <Col md={12}>
                        <div className="gst-heading">Profit & loss Account</div>
                    </Col>
                    <Col md={6}>
                        <div className="gst-details-box">
                            <div className="box-head">
                                <div className="gst-title">Expenses</div>
                                <div className="gst-value">Amount</div>
                            </div>
                            <div className="gst-container">
                                <div className="gst-details">
                                    <table className="inner-table">
                                        <tr>
                                            <td colSpan="2">
                                                <div className="gst-title">Winnings Distribution</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">
                                                    {DashboardExpenses.winning_distribute}
                                                </div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colSpan="2">
                                                <div className="gst-title">GST</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">
                                                    {(DashboardExpenses.gst_tax) ? (DashboardExpenses.gst_tax).toFixed(2) : ''}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colSpan="2">
                                                <div className="gst-title">Total benefit provided by Admin</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.total_benefit_provide_by_admin}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Referral Bonus Paid</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.referal_bonus_paid}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Referral real cash Paid</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.referal_real_cash_paid}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Contest winning Bonus</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.contest_winning_bonus}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Real cash deposit by Admin</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.real_cash_deposit_by_admin}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Bonus deposit </div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.bonus_deposit_by_admin}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Bonus provided in coin redemption</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.coin_redeem_bonus}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Real cash provided in coin redemption</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardExpenses.coin_redeem_realcash}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="main-table-row">
                                            <td colSpan="2">
                                                <div className="gst-title">Profit & Loss</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">
                                                    {DashboardExpenses.profit_loss}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div className="box-footer">
                                <div className="total-gst-title">Total Expenses</div>
                                <div className="total-gst-value">{DashboardExpenses.total_expencses}</div>
                            </div>
                        </div>
                    </Col>
                    <Col md={6}>
                        <div className="gst-details-box">
                            <div className="box-head">
                                <div className="gst-title">Income</div>
                                <div className="gst-value">Amount</div>
                            </div>
                            <div className="gst-container">
                                <div className="gst-details">
                                    <table className="inner-table">
                                        <tr className="main-table-row">
                                            <td colSpan="2">
                                                <div className="gst-title">Total Revenue</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardProfitLoss.entry_fee}</div>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Platform fee (Rake)</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardProfitLoss.site_rake}</div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr className="sub-table-row">
                                            <td>
                                                <div className="gst-title">Winnings Distribution</div>
                                            </td>
                                            <td>
                                                <div className="gst-value">{DashboardProfitLoss.win_amount}</div>
                                            </td>
                                            <td>

                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div className="box-footer">
                                <div className="total-gst-title">Total Income</div>
                                <div className="total-gst-value">{DashboardProfitLoss.total_income}</div>
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="mt-5">
                    <Col md={12}>
                        <div className="gst-heading">Balance Sheet</div>
                    </Col>
                    <Col md={6}>
                        <div className="gst-details-box">
                            <div className="box-head">
                                <div className="gst-title">Liabilities</div>
                                <div className="gst-value">Amount</div>
                            </div>
                            <div className="gst-container">
                                <div className="gst-details">
                                    <div className="gst-info">
                                        <div className="gst-title">Deposit Wallet</div>
                                        <div className="gst-value">{Dashboardliabilities.deposit_wallet}</div>
                                    </div>
                                    <div className="gst-info">
                                        <div className="gst-title">Winning wallet Payable</div>
                                        <div className="gst-value">{Dashboardliabilities.wining_wallet}</div>
                                    </div>
                                    <div className="gst-info">
                                        <div className="gst-title">Tax Payables - IGST</div>
                                        <div className="gst-value">

                                            {
                                                (Dashboardliabilities.tax_payable_igst) ? (Dashboardliabilities.tax_payable_igst).toFixed(2) : ''}
                                        </div>
                                    </div>
                                    <div className="gst-info">
                                        <div className="gst-title">TDS Payable</div>
                                        <div className="gst-value">{Dashboardliabilities.tds_payable}</div>
                                    </div>
                                    <div className="gst-info">
                                        <div className="gst-title">Profit & loss</div>
                                        <div className="gst-value">{Dashboardliabilities.profit_loss}</div>
                                    </div>
                                </div>
                            </div>
                            <div className="box-footer">
                                <div className="total-gst-title">Total Liabilities</div>
                                <div className="total-gst-value"> {
                                    (Dashboardliabilities.total_liabilities) ? (Dashboardliabilities.total_liabilities).toFixed(2) : ''}
                                </div>
                            </div>
                        </div>
                    </Col>
                    <Col md={6}>
                        <div className="gst-details-box">
                            <div className="box-head">
                                <div className="gst-title">Assets</div>
                                <div className="gst-value">Amount</div>
                            </div>
                            <div className="gst-container">
                                <div className="gst-details">
                                    <div className="gst-info">
                                        <div className="gst-title">Site-Rake Balance / Bank</div>
                                        <div className="gst-value">{DashboardAssets.site_rake}</div>
                                    </div>
                                </div>
                            </div>
                            <div className="box-footer">
                                <div className="total-gst-title">Total Assets</div>
                                <div className="total-gst-value">{DashboardAssets.total_assets}</div>
                            </div>
                        </div>
                    </Col>

                </Row>
            </div>
        )
    }
}
