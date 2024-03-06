import React, { Component, Fragment } from "react";
import { Row, Col, Button, Table, Input } from 'reactstrap';
import Select from 'react-select';
import _ from 'lodash';
import Pagination from "react-js-pagination";
import WSManager from "../../helper/WSManager";
import { notify } from 'react-notify-toast';
import * as NC from "../../helper/NetworkingConstants";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Loader from '../../components/Loader';
import moment from 'moment';
import HF, { _isEmpty } from '../../helper/HelperFunction';
import SelectDate from "../../components/SelectDate";
const TrTypeOptions = [
    { value: '', label: 'All' },
    { value: '1', label: 'Debit' },
    { value: '0', label: 'Credit' }
]

const TrStatusOptions = [
    { value: '', label: 'All' },
    { value: '0', label: 'Pending' },
    { value: '1', label: 'Success' },
    { value: '2', label: 'Failed' }
]

const TrDescriptionOptions = [
    { value: 'other', label: 'Other' },
    { value: '0', label: 'Admin' },
    { value: '4', label: 'Friend Refferal Bonus' },
    { value: '5', label: 'Bonus Expired' },
    { value: '6', label: 'Promocode' },
    { value: '7', label: 'Deposit' },
    { value: '8', label: 'Withdraw' },
    { value: '546', label: 'Casual Game Join' },
    { value: '547', label: 'Casual Game Cancel' },
    { value: '548', label: 'Casual Game Won' }

]
const liveFantasy = [
    { value: '500', label: 'Join Live Fantasy Contest' },
    { value: '501', label: 'Cancel Live Fantasy Contest' },
    { value: '502', label: 'Won Live Fantasy Contest'}

]

const swOptions = [
    { value: '381', label: 'Scratch & Win' }
]

const netwGameOptions = [
    { value: '240', label: 'Join Network Contest' },
    { value: '241', label: 'Won Network Contest' },
    { value: '242', label: 'Cancel Network Contest' },
    { value: '381', label: 'Scratch & Win' },
    { value: '50', label: 'New Signup(all) - Bonus' },
    { value: '147', label: 'Coins Deduct on Redeem Coins' },
    { value: '53', label: 'New Signup Referral - Bonus' },
    { value: '144', label: 'Daily Streak Coins' },
    { value: '56', label: 'New Signup(referred) - Bonus' },
    { value: '58', label: 'New signup(referred) - Coin' },
    { value: '99', label: 'First Deposit Referral - Real' },
]
const TrDescriptionOptionsDFS = [

    { value: '1', label: 'JoinGame' },
    { value: '2', label: 'GameCancel' },
    { value: '3', label: 'GameWon' }
]

const SportPrediction = [
    { value: '40', label: 'Make Prediction' },
    { value: '41', label: 'Prediction Won' },
]

const xpOptions = [
    { value: '450', label: 'XP - Level Promotion Benefit' },
    { value: '451', label: 'XP - Deposit Cashback' },
    { value: '452', label: 'XP - Contest Joining Cashback' },
]

// Join Stock Contest - 460
// Cancel Stock Contest - 461
// Won Stock Contest - 462

const stockOptions = [
    { value: '460', label: 'Join Stock Contest' },
    { value: '461', label: 'Cancel Stock Contest' },
    { value: '462', label: 'Won Stock Contest' },
]
const pickFantasyOptions = [
    { value: '524', label: 'Join Pick Fantasy Contest' },
    { value: '525', label: 'Cancel Pick Fantasy Contest' },
    { value: '526', label: 'Won Pick Fantasy Contest' },
]

const propsFantasyOptions = [
    { value: '537', label: 'Join Props' },
    { value: '539', label: 'Fee Refund Props' },
    { value: '540', label: 'Won Props' },
    { value: '538', label: 'Stack Increase Props' },
]

const opinionTradingOptions = [
    { value: '542', label: 'Join Opinion' },
    { value: '544', label: 'Game Won Opinion' },
    { value: '543', label: 'Cancel Opinion' },
]
const CashBackBonus = [
    { value: '550', label: 'Bonus Cashback' },
    
]

export default class TransactionList extends Component {
    constructor(props) {
        super(props)
        this.state = {
            Total: 0,
            PERPAGE: NC.ITEMS_PERPAGE,
            // PERPAGE: 5,
            CURRENT_PAGE: 1,
            Keyword: '',
            TransactionData: [],
            TrTypeChange: '',
            TrStatusChange: '',
            TrDescChange: '0',
            CheckAllStatus: false,
            EmailModalOpen: false,
            balanceModalOpen: false,
            formValid: false,
            BalanceType: 1,
            TrAmountType: 1,
            SelectedUserID: [],
            posting: false,
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            TrDescriptionOptionsMain:[]
        }
        this.SearchKeyReq = _.debounce(this.SearchKeyReq.bind(this), 500);
    }
    componentDidMount() {
        this.getAllTransaction()
        if(HF.allowDFS() == 1){
            let TrDescriptionOptionsMainData = [...TrDescriptionOptionsDFS,...TrDescriptionOptions]
            this.setState({TrDescriptionOptionsMain:TrDescriptionOptionsMainData})

        }
        else{
            this.setState({TrDescriptionOptionsMain:TrDescriptionOptions})

        }

    }

    getDescriptionDDown = () => {
        let temp_dd = TrDescriptionOptions
        if (HF.allowDFS() == '1') {
            temp_dd = [...temp_dd, ...TrDescriptionOptionsDFS]
        }
        if (HF.allowNetworkGame() == '1') {
            temp_dd = [...temp_dd, ...netwGameOptions]
        }
        if (HF.allowScratchWin() == '1') {
            temp_dd = [...temp_dd, ...swOptions]
        }
        if (HF.allowLiveFantsy() == '1') {
            temp_dd = [...temp_dd, ...liveFantasy]
        }
        if (HF.allowSportsPrediction() == '1') {
            temp_dd = [...temp_dd, ...SportPrediction]
        }
        if (HF.allowXpPoints() == '1') {
            temp_dd = [...temp_dd, ...xpOptions]
        }
        if (HF.allowStockFantasy() == '1') {
            temp_dd = [...temp_dd, ...stockOptions]
        }
        if (HF.allowPicksFantasy() == '1') {
            temp_dd = [...temp_dd, ...pickFantasyOptions]
        }

        if (HF.allowPropsFantasy() == '1') {
            temp_dd = [...temp_dd, ...propsFantasyOptions]
        }

         if (HF.allowOpinionTrade() == '1') {
            temp_dd = [...temp_dd, ...opinionTradingOptions]
        }

        
        if (HF.allowGst() == '1' && HF.allowGstType() == 'new') {
            temp_dd = [...temp_dd, ...CashBackBonus]
        }

        return temp_dd
    }

    getAllTransaction = () => {
        this.setState({ posting: true })
        const { PERPAGE, CURRENT_PAGE, Keyword, TrTypeChange, TrStatusChange, TrDescChange, FromDate, ToDate } = this.state
        let params = {
            items_perpage: PERPAGE,
            current_page: CURRENT_PAGE,
            from_date: FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : '',
            to_date: ToDate ? moment(ToDate).format('YYYY-MM-DD') : '',
            sort_order: "DESC",
            sort_field: "created_date",
            status: TrStatusChange,
            csv: false,
            type: TrTypeChange,
            source: TrDescChange,
            keyword: Keyword
        }

        WSManager.Rest(NC.baseURL + NC.GET_ALL_TRANSACTION, params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {

                this.setState({
                    posting: false,
                    TransactionData: ResponseJson.data.result,
                })
                if (ResponseJson.data.total > 0) {
                    this.setState({
                        Total: ResponseJson.data.total
                    })
                }

            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    handlePageChange(current_page) {
        this.setState({
            CURRENT_PAGE: current_page
        }, () => {
            this.getAllTransaction()
        });
    }

    searchByKey = (e) => {
        this.setState({ Keyword: e.target.value }, this.SearchKeyReq)
    }

    SearchKeyReq() {
        if (this.state.Keyword.length > 2)
            this.getAllTransaction()
    }
    handleTypeChange = (value, name) => {
        if (value != null)
            this.setState({ [name]: value.value }, this.getAllTransaction)
    }
    handleDate = (date, dateType) => {
        this.setState({ [dateType]: date }, () => {
            if (this.state.FromDate || this.state.ToDate) {
                this.getAllTransaction()
            }
        })
    }
    clearFilter = () => {
        this.setState({
            TrTypeChange: '',
            TrStatusChange: '',
            TrDescChange: '',
            FromDate: new Date(Date.now() - ((HF.getTodayDate()) - 1) * 24 * 60 * 60 * 1000),
            ToDate: new Date(moment().format('D MMM YYYY')),
            Keyword: ''
        }, this.getAllTransaction
        )
    }
    exportUser = () => {
        var query_string = '';//pairs.join('&');
        let { FromDate, ToDate, Keyword, TrStatusChange, TrTypeChange, TrDescChange } = this.state
        // let tempFromDate = FromDate ? moment(FromDate).format('YYYY-MM-DD') : '';
        // let tempToDate = ToDate ? moment(ToDate).format('YYYY-MM-DD') : '';
        let tempFromDate = FromDate ? WSManager.getLocalToUtcFormat(FromDate, 'YYYY-MM-DD') : ''
        let tempToDate = ToDate ? moment(ToDate).format("YYYY-MM-DD") : '';

        query_string = 'keyword=' + Keyword + '&status=' + TrStatusChange + '&type=' + TrTypeChange + '&source=' + TrDescChange + '&csv=true' + '&from_date=' + tempFromDate + '&to_date=' + tempToDate;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/finance/get_all_transaction?' + query_string, '_blank');
    }

    redirectToDetails = (obj) => {
        let url = ''
        if (obj.source == '460' || obj.source == '461' || obj.source == '462') {
            url = '/stockfantasy/contest_detail/' + obj.reference_id + '?tab='
        }
        else
        {            
            url = '/finance/contest_detail/' + obj.contest_unique_id
        }
        this.props.history.push(url)
    }
    
    exportUserTxn = (order_id) => {
        var query_string = 'order_id='+order_id;
        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;
        window.open(NC.baseURL + 'adminapi/finance/user_txn_report?' + query_string, '_blank');
    }

    render() {
        let { posting, CURRENT_PAGE, PERPAGE, Total, Keyword, TransactionData, TrTypeChange, TrStatusChange, TrDescChange, FromDate, ToDate } = this.state
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
            // min_date: new Date(FromDate),
            // max_date: new Date(),
            // sel_date: new Date(ToDate),

            min_date: new Date(FromDate),
            max_date: todaysDate,
            sel_date: new Date(ToDate),
            date_key: 'ToDate',
            place_holder: 'To Date',
        }
        return (
            <Fragment>
                <div className="animated fadeIn transaction-list">
                    <Row>
                        <Col md={12}>
                            <h1 className="h1-cls">Transaction</h1>
                        </Col>
                    </Row>
                    <Row className="mt-5">
                        <Col md={12}>
                            <h3 className="h3-cls">Filters</h3>
                        </Col>
                    </Row>
                    <Row className="mt-2">
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Transaction Type</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    options={TrTypeOptions}
                                    placeholder="Withdrawal Type"
                                    menuIsOpen={true}
                                    value={TrTypeChange}
                                    onChange={e => this.handleTypeChange(e, 'TrTypeChange')}
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
                            <label className="filter-label">From Date</label>
                            {/* <DatePicker
                                maxDate={new Date(ToDate)}
                                className="form-control"
                                showYearDropdown='true'
                                selected={FromDate}
                                onChange={e => this.handleDateFilter(e, "FromDate")}
                                placeholderText="From"
                            /> */}
                            <SelectDate DateProps={FromDateProps} />
                        </Col>
                        <Col md={2}>
                            <label className="filter-label">To Date</label>
                            {/* <DatePicker
                                minDate={new Date(FromDate)}
                                maxDate={new Date()}
                                className="form-control"
                                showYearDropdown='true'
                                selected={ToDate}
                                onChange={e => this.handleDateFilter(e, "ToDate")}
                                placeholderText="To"
                            /> */}
                            <SelectDate DateProps={ToDateProps} />
                        </Col>
                        <Col md={2}>
                            <div>
                                <label className="filter-label">User ID / Username / Email</label>
                                <Input
                                    placeholder="User ID/Username"
                                    name='UserKeywordname'
                                    value={Keyword}
                                    onChange={this.searchByKey}
                                />
                            </div>
                        </Col>
                        <Col md={2}>
                            <div>
                                <label className="filter-label">Description</label>
                                <Select
                                    isSearchable={true}
                                    class="form-control"
                                    // options={TrDescriptionOptions}
                                    options={this.getDescriptionDDown()}
                                    placeholder="Description"
                                    menuIsOpen={true}
                                    value={TrDescChange}
                                    onChange={e => this.handleTypeChange(e, 'TrDescChange')}
                                />
                            </div>
                        </Col>
                    </Row>
                    <Row className="filters-box mt-3">
                        <Col md={12}>
                            <div className="filters-area">
                                <Button className="btn-secondary" onClick={() => this.clearFilter()}>Clear Filters</Button>
                            </div>
                        </Col>
                    </Row>
                    <Row className="mb-2">
                        <Col md={12}>
                            <h3 className="h3-cls pull-left">Transactions</h3>
                            <div className="cursor-pointer">
                                <i className="export-list icon-export" onClick={e => this.exportUser()}></i>
                            </div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12} className="table-responsive common-table">
                            <Table>
                                <thead>
                                    <tr>
                                        <th>Unique Id</th>
                                        <th>User Name</th>
                                        <th>Order Id</th>
                                        <th>Transaction Id</th>
                                        <th>Description</th>
                                        <th>Contest</th>
                                        <th>Payment Mode</th>
                                        <th>Payment Type</th>
                                        <th>Real Amount</th>
                                        <th>Winning Amount</th>
                                        <th>Bonus Amount</th>
                                        <th>Coins</th>
                                        <th>Merchandise</th>
                                        <th>Promo Code</th>
                                        {
                                            HF.allowCryto() == '1' && <th>Crypto Detail</th>
                                        }
                                        <th>Transaction Date</th>
                                        <th className="right-th">Status</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                {
                                    TransactionData.length > 0 ?
                                        _.map(TransactionData, (transaction, idx) => {

                                            let item_prize_data = (transaction.custom_data) ? transaction.custom_data : [];
                                            let row_prize_data = (item_prize_data.length > 0) ? item_prize_data : [];

                                         
                                            
                                            let nw_c_data = (transaction.custom_data) ? transaction.custom_data : [];
                                            let nw_contest_name = (!_.isUndefined(nw_c_data.contest_name)) ? nw_c_data.contest_name : '';
                                            let nw_con_unique_id = (!_.isUndefined(nw_c_data.contest_unique_id)) ? nw_c_data.contest_unique_id : '';

                                            /**Start For stock fantsy */
                                            if (transaction.source == '462') {
                                                _.map(item_prize_data.prize_data, (stkdata, idx) => {
                                                    if (stkdata.prize_type == "0") {
                                                        transaction.bonus_amount = stkdata.amount
                                                    }
                                                    if (stkdata.prize_type == "1") {
                                                        transaction.real_amount = stkdata.amount
                                                    }
                                                    if (stkdata.prize_type == "2") {
                                                        transaction.points = stkdata.amount
                                                    }
                                                    if (stkdata.prize_type == "3") {
                                                        transaction.mer_name = stkdata.name
                                                    }
                                                })

                                            }
                                        /**End For stock fantsy */
                                        
                                            let cryto_curr = (!_.isUndefined(nw_c_data.deposit_crypto)) ? nw_c_data.deposit_crypto : '';
                                            let cryto_wallet = (!_.isUndefined(nw_c_data.deposit_to_addr)) ? nw_c_data.deposit_to_addr : '';
                                            let cryto_coins = (!_.isUndefined(nw_c_data.deposit_crypto_amt)) ? nw_c_data.deposit_crypto_amt : '';

                                            return (
                                                <tbody key={idx}>
                                                    <tr>
                                                        <td>{transaction.user_unique_id}</td>
                                                        <td
                                                            // onClick={() => this.props.history.push("/profile/" + transaction.user_unique_id)}
                                                            className="user-name">
                                                            <a href={"/admin/#/profile/" + transaction.user_unique_id}>
                                                                {transaction.user_name ? transaction.user_name : '--'}
                                                            </a>
                                                        </td>

                                                        <td>{transaction.order_id}</td>

                                                        <td>{transaction.transaction_id ? transaction.transaction_id : '--'}</td>

                                                        <td>
                                                            {
                                                                (!_.isUndefined(item_prize_data.promo_code) && (transaction.source == '30' || transaction.source == '31' || transaction.source == '32')) &&
                                                                <span className="pcode">{item_prize_data.promo_code}{' '}</span>
                                                            }
                                                            {transaction.trans_desc}
                                                        </td>

                                                        {
                                                            ((transaction.source === "240" || transaction.source === "241" || transaction.source === "242") && !_.isEmpty(nw_contest_name)) ? (
                                                                <td onClick={() => this.props.history.push('/network-game/details/' + nw_con_unique_id)} className="user-name">
                                                                    {nw_contest_name}
                                                                </td>
                                                            )
                                                                :
                                                                transaction.contest_name ? (
                                                                    <td
                                                                        // onClick={() => this.props.history.push('/finance/contest_detail/' + transaction.contest_unique_id)} className="user-name"
                                                                        onClick={() => this.redirectToDetails(transaction)}
                                                                        className="user-name"
                                                                    >
                                                                        {transaction.contest_name}
                                                                    </td>
                                                                )
                                                                    :
                                                                    <td>--</td>
                                                        }


                                                        {/* <td>
                                                            {
                                                                transaction.payment_method ? transaction.payment_method :  '--'
                                                            }
                                                        </td> */}
                                                        <td>{transaction.gate_way_name ? transaction.gate_way_name : '--'}</td>

                                                        <td>{transaction.type == 0 ? 'CREDIT' : 'DEBIT'
                                                        }</td>

                                                        <td>{HF.getCurrencyCode()}&nbsp;{transaction.real_amount}</td>
                                                        <td>{HF.getCurrencyCode()}&nbsp;{transaction.winning_amount}</td>
                                                        <td>{HF.getCurrencyCode()}&nbsp;{transaction.bonus_amount}</td>
                                                        <td>{transaction.points}</td>
                                                        <Fragment>
                                                            {
                                                                // transaction.source == '531' ?
                                                                //     <td>{transaction.merchandise ? transaction.merchandise : '--'}</td>
                                                                //     :
                                                                // transaction.source == '462' ?
                                                                //     <td>{transaction.mer_name ? transaction.mer_name : '--'}</td>
                                                                //     :
                                                                // transaction.source == '372' ?
                                                                //     <td>
                                                                //         {transaction.custom_data && transaction.custom_data.merchandise ? transaction.custom_data.merchandise : '--'}
                                                                      
                                                                //     </td>
                                                                //     :
                                                                //     <Fragment>
                                                                //         {
                                                                //             row_prize_data.length > 0 &&
                                                                //             <td>
                                                                //                 {(row_prize_data[0].prize_type) ?
                                                                //                     (row_prize_data[0].prize_type == 3) ?
                                                                //                         row_prize_data[0].name :
                                                                //                         '' :
                                                                //                     ''}
                                                                //             </td>
                                                                //         }
                                                                //         {row_prize_data.length == 0 &&
                                                                //             <td>--</td>
                                                                //         }
                                                                //     </Fragment>
                                                                <td>{transaction.merchandise ? transaction.merchandise : '--'}</td>
                                                            }
                                                        </Fragment>

                                                        <td>{transaction.promo_code ? transaction.promo_code : '--'}</td>
                                                        {
                                                            HF.allowCryto() == '1' &&
                                                            <td>
                                                                {
                                                                    !_isEmpty(cryto_curr) ?
                                                                        <Fragment>
                                                                            Currency : {cryto_curr}
                                                                            <br />
                                                                            Coins : {cryto_coins}
                                                                            <br />
                                                                            Wallet : {cryto_wallet}
                                                                        </Fragment>
                                                                        :
                                                                        <Fragment>
                                                                            {'--'}
                                                                        </Fragment>
                                                                }
                                                            </td>
                                                        }
                                                        <td>
                                                            {/* {WSManager.getUtcToLocalFormat(transaction.order_date_added, 'D-MMM-YYYY hh:mm A')} */}
                                                            {HF.getFormatedDateTime(transaction.order_date_added, 'D-MMM-YYYY hh:mm A')}
                                                        </td>
                                                        <td>
                                                            {
                                                                transaction.status == 0
                                                                    ?
                                                                    <i className="icon-verified" title='Not yet' />
                                                                    :
                                                                    transaction.status == 1 || transaction.status == 3
                                                                        ?
                                                                        <i className="icon-verified text-green" title='Payment Processed Done' />
                                                                        :
                                                                        <i className="icon-inactive text-red" title={(transaction.source == 8) ? 'Rejected' : 'Failed'} />
                                                            }
                                                        </td>
                                                        <td>
                                                            {
                                                                transaction.source==7 ?
                                                                <i className="export-list icon-export" onClick={e => this.exportUserTxn(transaction.order_id)}></i>
                                                                :
                                                                ""
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
                                                    {(TransactionData.length == 0 && !posting) ?
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
                    {Total > 0 && (
                        <div className="custom-pagination lobby-paging">
                            <Pagination
                                activePage={CURRENT_PAGE}
                                itemsCountPerPage={PERPAGE}
                                totalItemsCount={Total}
                                pageRangeDisplayed={5}
                                onChange={e => this.handlePageChange(e)}
                            />
                        </div>
                    )
                    }
                </div>
            </Fragment>
        )
    }
}