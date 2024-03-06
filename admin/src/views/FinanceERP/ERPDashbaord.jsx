import React, { Component, Fragment } from 'react';
import { Row, Col, Tooltip } from 'reactstrap';
import SelectDropdown from "../../components/SelectDropdown";
import HF from "../../helper/HelperFunction";
import SelectDate from "../../components/SelectDate";
import { _isNull, _times, _Map, _isUndefined, _isEmpty } from '../../helper/HelperFunction';
import WSManager from '../../helper/WSManager';
import * as NC from '../../helper/NetworkingConstants';
import { notify } from 'react-notify-toast';
import { getErpDashboardData } from '../../helper/WSCalling';
import moment from 'moment';
import _ from 'lodash';
import { MSG_SG_HELP } from "../../helper/Message";
const QuatersOptions = [
    { value: 3, label: 'Overall' },
    { value: 7, label: 'Last 7 days' },
    { value: 30, label: 'Last 30 days' },
    { value: 2, label: 'Current Month' },
    { value: 1, label: 'Custom range' },
]
class ERPDashbaord extends Component {
    constructor(props) {
        super(props);
        let DefaultTT = {
            expenses_tt: false,
            income_tt: false,
            Liabilites_tt: false,
        }
        this.state = {
            FromDate: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
            ToDate: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
            AmountDisbursed: 0,
            BonusCash: 0,
            BonusExpired: 0,
            Revenue: 0,
            Expenses: 0,
            ExpensesList: [],
            Income: 0,
            IncomeList: [],
            LiabilitiesList: [],
            Profit: 0,
            QuatersType: 3,
            RefundEntry: 0,
            ToolTipArr: DefaultTT
        }
    }

    componentDidMount = () => {
        this.getDashboardData()
    }

    handleQuatersChange = (value) => {
        if (!_isNull(value)) {
            if (value.value != 1 && value.value != 2) {
                this.setState({
                    FromDate: new Date(Date.now() - value.value * 24 * 60 * 60 * 1000),
                    ToDate: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
                })
            }
            if (value.value == 2) {
                let d = new Date();
                let todayD = d.getDate()
                this.setState({
                    FromDate: new Date(Date.now() - (todayD - 1) * 24 * 60 * 60 * 1000),
                    ToDate: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
                })
            }
            this.setState({ QuatersType: value.value }, () => {
                this.getDashboardData()
            })
        }
    }

    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date, CURRENT_PAGE: 1 },
            this.getDashboardData
        )
    }

    getCommonList(list, list_cond) {
        return (
            _Map(list, (item, idx) => {
                return (
                    item.is_custom == list_cond &&
                    <li key={idx} className="erp-cat-item clearfix">
                        <div className="erp-wdgt-item">
                            <div className="erp-c-item float-left">
                                {item.name}
                            </div>
                            <div className="erp-c-exp">
                                {HF.getCurrencyCode()}
                                {item.total != 0 ? HF.getNumberWithCommas(item.total) : 0}
                            </div>
                        </div>
                    </li>
                )
            })
        )
    }

    sysGeneratedToggle = (CallType, key) => {
        let { ToolTipArr } = this.state
        ToolTipArr[CallType + key] = !ToolTipArr[CallType + key]; 
        this.setState({ ToolTipArr: ToolTipArr })
    }

    wdgtHtml(list, flag, CallType) {
        let { ToolTipArr } = this.state
        return (
            <div className="erp-wdgt-view">
                <div className="erp-wdgt-head">
                    <div>Categories</div>
                    <div className="float-right">Amount</div>
                </div>
                <div className="erp-cat-box">
                    {
                        flag &&
                        <div className="erp-sg-title">
                            System Generated
                            <i className="ml-2 icon-info-border cursor-pointer" id={CallType + '_tt'}>
                                <Tooltip placement="right" isOpen={ToolTipArr[CallType + '_tt']} target={CallType + '_tt'} toggle={() => this.sysGeneratedToggle(CallType, '_tt')}>
                                    {MSG_SG_HELP}
                                </Tooltip>
                            </i>
                        </div>
                    }
                    <ul className="erp-cat-list">
                        {
                            this.getCommonList(list, '0')
                        }
                    </ul>
                    <Fragment>
                        {
                            !this.checkIsCustom(list) &&
                            <div className="erp-sg-title">User Generated</div>
                        }
                    </Fragment>
                    <ul className="erp-cat-list">
                        {
                            !this.checkIsCustom(list) &&
                            this.getCommonList(list, '1')
                        }
                        {/* <li className="erp-cat-item clearfix"></li> */}
                        <li className="erp-item-tot clearfix">
                            <div className="erp-wdgt-item">
                                <div className="erp-total-l">Total</div>
                                <div className="erp-c-exp font-xl">
                                    {HF.getCurrencyCode()}
                                    {this.getTotal(list) != 0 ? HF.getNumberWithCommas(this.getTotal(list)) : 0}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        )
    }

    exportList = () => {
        let { FromDate, ToDate, QuatersType } = this.state
        var query_string = ''

        let from_date = (QuatersType != 3 && FromDate) ? moment(FromDate).format("YYYY-MM-DD") : ''
        let to_date = (QuatersType != 3 && ToDate) ? moment(ToDate).format("YYYY-MM-DD") : ''

        query_string = 'from_date=' + from_date + '&to_date=' + to_date;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;


        window.open(NC.baseURL + 'adminapi/finance_erp/export_dashboard_data?' + query_string, '_blank');
    }

    getDashboardData = () => {
        let { FromDate, ToDate, QuatersType } = this.state
        let params = {
            "from_date": (QuatersType != 3 && FromDate) ? moment(FromDate).format("YYYY-MM-DD") : '',
            "to_date": (QuatersType != 3 && ToDate) ? moment(ToDate).format("YYYY-MM-DD") : '',
        }
        getErpDashboardData(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                ResponseJson = ResponseJson.data
                this.setState({
                    AmountDisbursed: !_isUndefined(ResponseJson.amount_disbursed) ? parseInt(ResponseJson.amount_disbursed).toFixed(2) : 0,

                    BonusCash: !_isUndefined(ResponseJson.bonus_cash) ? parseInt(ResponseJson.bonus_cash).toFixed(2) : 0,

                    BonusExpired: !_isUndefined(ResponseJson.bonus_expired) ? parseInt(ResponseJson.bonus_expired).toFixed(2) : 0,

                    Revenue: !_isUndefined(ResponseJson.revenue) ? parseInt(ResponseJson.revenue).toFixed(2) : 0,

                    Expenses: (!_isUndefined(ResponseJson.expenses) && !_isUndefined(ResponseJson.expenses.current)) ? parseInt(ResponseJson.expenses.current).toFixed(2) : 0,

                    ExpensesPast: (!_isUndefined(ResponseJson.expenses) && !_isUndefined(ResponseJson.expenses.past)) ? ResponseJson.expenses.past : 0,

                    ExpensesList: (!_isUndefined(ResponseJson.expenses_list)) ? ResponseJson.expenses_list : [],

                    Income: (!_isUndefined(ResponseJson.income) && !_isUndefined(ResponseJson.income.current)) ? parseInt(ResponseJson.income.current).toFixed(2) : 0,

                    IncomePast: (!_isUndefined(ResponseJson.income) && !_isUndefined(ResponseJson.income.past)) ? ResponseJson.income.past : 0,

                    IncomeList: (!_isUndefined(ResponseJson.income_list)) ? ResponseJson.income_list : [],

                    LiabilitiesList: (!_isUndefined(ResponseJson.liabilities_list)) ? ResponseJson.liabilities_list : [],

                    Profit: (!_isUndefined(ResponseJson.profit) && !_isUndefined(ResponseJson.profit.current)) ? parseInt(ResponseJson.profit.current).toFixed(2) : 0,

                    ProfitPast: (!_isUndefined(ResponseJson.profit) && !_isUndefined(ResponseJson.profit.past)) ? ResponseJson.profit.past : 0,

                    RefundEntry: !_isUndefined(ResponseJson.refund_entry) ? parseInt(ResponseJson.refund_entry).toFixed(2) : 0,
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }

    getTotal = (listfor_sum) => {
        let sumTot = 0
        if (!_isEmpty(listfor_sum))
            sumTot = _.sumBy(listfor_sum, item => !_.isUndefined(item.total) ? parseFloat(item.total) : 0)

        return sumTot.toFixed(2);
    }

    checkIsCustom = (listfor_custom) => {
        return !_.find(listfor_custom, { is_custom: '1' })
    }

    getPercent = (current, past) => {
        let new_current = parseInt(current)
        let new_past = parseInt(past)
        let Percentage = ((new_current - new_past) / new_past) * 100
        let newPer = Percentage.toFixed(2)

        if (HF.isFloat(newPer)) {
            return newPer.toString().replace("-", ' ');
        }
        else {
            return 0
        }
    }

    render() {
        let { QuatersType, FromDate, ToDate, AmountDisbursed, BonusCash, BonusExpired,
            Revenue, Expenses, ExpensesList, Income, IncomeList, LiabilitiesList, Profit, ExpensesPast, IncomePast, RefundEntry, ProfitPast } = this.state
        const Select_Props = {
            is_disabled: false,
            is_searchable: true,
            is_clearable: false,
            menu_is_open: false,
            class_name: "",
            sel_options: QuatersOptions,
            place_holder: "Select Quaters",
            selected_value: QuatersType,
            modalCallback: this.handleQuatersChange
        }
        const sameDateProp = {
            disabled_date: false,
            show_time_select: false,
            time_format: false,
            time_intervals: false,
            time_caption: false,
            date_format: 'dd/MM/yyyy',
            handleCallbackFn: this.handleDate,
            class_name: 'epr-datep mr-3',
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
            max_date: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }

        return (
            <div className="erp-dashboard">
                <Row>
                    <Col md={12}>
                        <div className="float-left">
                            <h2 className="h2-cls mt-2">Financial ERP</h2>
                        </div>
                    </Col>
                </Row>
                <Row className="erp-filters">
                    <Col md={12}>
                        <div className="erp-sel-qr">
                            <label htmlFor="fquaters">Filters</label>
                            <SelectDropdown SelectProps={Select_Props} />
                        </div>
                        {
                            QuatersType == 1 &&
                            <div className="float-left">
                                <div className="float-left">
                                    <label htmlFor="fquaters">Date From</label>
                                    <SelectDate DateProps={FromDateProps} />
                                </div>
                                <div className="float-left">
                                    <label htmlFor="fquaters">Date To</label>
                                    <SelectDate DateProps={ToDateProps} />
                                </div>
                            </div>
                        }
                    </Col>
                </Row>
                <Col md={12}>
                    <Row className="erp-t-box">
                        <Col md={4}>
                            <div className="erp-t-info">
                                <div className="erp-t-lable">Total Expenses</div>
                                <div className="erp-t-count">
                                    {HF.getCurrencyCode()}
                                    {Expenses != 0 ? HF.getNumberWithCommas(Expenses) : 0}
                                </div>
                            </div>

                            {/* Current value > Past value = Up arrow Red
                            Expenses= Up arrow Red
                            Expenses= Up arrow Red 0% */}

                            {(QuatersType != 3) && <div className="erp-t-percent erp-loss">
                                {
                                    (this.getPercent(Expenses, ExpensesPast) >= 0) &&
                                    <i className="icon-Path"></i>
                                }
                                {
                                    (this.getPercent(Expenses, ExpensesPast) < 0) &&
                                    <i className="icon-down-arrow"></i>
                                }
                                <span className="erf-per">
                                    {this.getPercent(Expenses, ExpensesPast)}%
                                </span>
                            </div>}
                        </Col>
                        <Col md={4} className="erp-sepration">
                            <div className="erp-t-info">
                                <div className="erp-t-lable cls-profit">Total Income</div><div className="erp-t-count">
                                    {HF.getCurrencyCode()}
                                    {Income != 0 ? HF.getNumberWithCommas(Income) : 0}
                                </div>
                            </div>
                            {/* Current value > Past value = Up arrow Green
                                Current value < Past value = Down arrow Red
                                Current Value = Past value = Up arrow Green (0%) */}

                            {
                                (QuatersType != 3 && Income >= IncomePast) &&
                                <div className="erp-t-percent erp-profit">
                                    <i className="icon-Path"></i>
                                    <span className="erf-per">
                                        {this.getPercent(Income, IncomePast)}%
                                    </span>
                                </div>
                            }

                            {
                                (QuatersType != 3 && Income < IncomePast) &&
                                <div className="erp-t-percent erp-loss">
                                    <i className="icon-down-arrow"></i>
                                    <span className="erf-per">
                                        {this.getPercent(Income, IncomePast)}%
                                    </span>
                                </div>
                            }
                        </Col>
                        <Col md={4} className="erp-sepration">
                            <div className="erp-t-info">
                                <div className="erp-t-lable cls-profit">Total Profit</div>
                                <div className="erp-t-count">
                                    {HF.getCurrencyCode()}
                                    {Profit != 0 ? HF.getNumberWithCommas(Profit) : 0}
                                </div>
                            </div>

                            {/* Total Profit

                                Current value > Past value = Up arrow Green
                                Current value < Past value= Down arrow Red
                                Current Value= Past value= Up arrow Green (0%) 
                            */}


                            {
                                (QuatersType != 3 && Profit >= ProfitPast) &&
                                <div className="erp-t-percent erp-profit">
                                    <i className="icon-Path"></i>
                                    <span className="erf-per">
                                        {this.getPercent(Profit, ProfitPast)}%
                                    </span>
                                </div>
                            }
                            {
                                (QuatersType != 3 && Profit < ProfitPast) &&
                                <div className="erp-t-percent erp-loss">
                                    <i className="icon-down-arrow"></i>
                                    <span className="erf-per">
                                        {this.getPercent(Profit, ProfitPast)}%
                                    </span>
                                </div>
                            }
                        </Col>
                    </Row>
                </Col>
                <Row className="mt-4">
                    <Col md={2} className="erp-box-p">
                        <div className="erp-totals">
                            <div className="erp-t-title">Total Revenue</div>
                            <div className="erp-t-count">
                                {HF.getCurrencyCode()}{Revenue != 0 ? HF.getNumberWithCommas(Revenue) : 0}
                            </div>
                        </div>
                    </Col>
                    <Col md={2} className="erp-box-p">
                        <div className="erp-totals">
                            <div className="erp-t-title">Contest amount refunded</div>
                            <div className="erp-t-count">
                                {HF.getCurrencyCode()}{RefundEntry > 0 ? HF.getNumberWithCommas(RefundEntry) : 0}
                            </div>
                        </div>
                    </Col>
                    <Col md={2} className="erp-box-p">
                        <div className="erp-totals">
                            <div className="erp-t-title">Amount Disbursed</div>
                            <div className="erp-t-count">
                                {HF.getCurrencyCode()}{AmountDisbursed > 0 ? HF.getNumberWithCommas(AmountDisbursed) : 0}
                            </div>
                        </div>
                    </Col>
                    <Col md={2} className="erp-box-p">
                        <div className="erp-totals">
                            <div className="erp-t-title">Bonus Cash with users</div>
                            <div className="erp-t-count">
                                <i className="icon-bonus"></i>{BonusCash > 0 ? HF.getNumberWithCommas(BonusCash) : 0}
                            </div>
                        </div>
                    </Col>
                    <Col md={2} className="erp-box-p">
                        <div className="erp-totals">
                            <div className="erp-t-title">Bonus cash Expired</div>
                            <div className="erp-t-count">
                                <i className="icon-bonus"></i>{BonusExpired > 0 ? HF.getNumberWithCommas(BonusExpired) : 0}
                            </div>
                        </div>
                    </Col>
                </Row>
                <Row className="erp-wdgt-box">
                    <Col md={4}>
                        <div className="erp-wdgt">
                            <div className="erp-wdgt-title">Expenses</div>
                            {this.wdgtHtml(ExpensesList, true, 'expenses')}
                        </div>
                    </Col>
                    <Col md={4}>
                        <div className="erp-wdgt">
                            <div className="erp-wdgt-title">Income</div>
                            {this.wdgtHtml(IncomeList, true, 'income')}
                        </div>
                    </Col>
                    <Col md={4}>
                        <div className="erp-wdgt">
                            <div className="clearfix">
                                <div className="erp-wdgt-title float-left">Liabilites</div>
                                <div className="cursor-pointer float-right">
                                    <i className="export-list icon-export" onClick={e => this.exportList()}></i>
                                </div>
                            </div>
                            {this.wdgtHtml(LiabilitiesList, false, 'liabilites')}
                        </div>
                    </Col>
                </Row>
            </div>
        )
    }
}
export default ERPDashbaord