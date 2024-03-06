import React, { Component, Fragment } from 'react';
import { Row, Col, Table,Tooltip } from 'reactstrap';
import DatePicker from "react-datepicker";
import Select from 'react-select';
import _ from 'lodash';
import Moment from 'react-moment';
import * as NC from "../../../../helper/NetworkingConstants";
import WSManager from "../../../../helper/WSManager";
import Pagination from "react-js-pagination";
import { notify } from 'react-notify-toast';
import moment from 'moment';
import HF, { _isUndefined,_isEmpty } from "../../../../helper/HelperFunction";
import { MomentDateComponent } from "../../../../components/CustomComponent";
import { PC_getUserPromoCodeData } from '../../../../helper/WSCalling';
const options = [
    { value: 0, label: 'Pending' },
    { value: 1, label: 'Success' },
    { value: 2, label: 'Failed' },
]
export default class Transaction extends Component {
    constructor(props) {
        super(props)
        let filter = {
            current_page: 1,
            status: 1,
            pending_pan_approval: '',
            keyword: '',
            items_perpage: !this.props.DashboardTranProps ? 5 : 50,
            sort_field: 'added_date',
            sort_order: 'DESC',
            from_date: HF.getFirstDateOfMonth(),
            to_date: new Date(),
        }
        this.state = {
            PERPAGE: !this.props.DashboardTranProps ? 5 : NC.ITEMS_PERPAGE,
            filter: filter,
            total: 0,
            userTransaction: [],
            RankData: [],
            FilterChange: 1,
            ALLOW_COIN_MODULE: HF.allowCoin(),
            UserPcodeData: {},
            isShowAutoToolTip: false,
        }
    }
    componentDidMount() {
        setTimeout(()=>{
            this.getTransaction()
        }, 1000)
        setTimeout(()=>{
            this.getRank()
        }, 1000)
        
        // this.getRank()
    }
    handlePageChange(current_page) {
        let filter = this.state.filter;
        filter['current_page'] = current_page;
        this.setState(
            { filter: filter }, () => {
                this.getTransaction();
            });
    }
    handleChange(date, dateType) {
        let filter = this.state.filter;
        filter[dateType] = date;
        this.setState({ filter: filter },
            function () {
                // if (dateType == "to_date")
                this.getTransaction();
            });
    }
    getTransaction() {
        let { filter } = this.state
        let user_id = !_.isUndefined(this.props) ? this.props.userBasic.user_id : '';

        this.setState({ posting: true })
        let params = {
            "from_date": filter.from_date ? moment(filter.from_date).format("YYYY-MM-DD") : '',
            "to_date": filter.to_date ? moment(filter.to_date).format("YYYY-MM-DD") : '',
            "items_perpage": filter.items_perpage,
            "total_items": 0,
            "current_page": filter.current_page,
            "sort_order": "DESC",
            "sort_field": "created_date",
            "user_id": user_id,
            "status": filter.status
        };
        WSManager.Rest(NC.baseURL + NC.GET_USER_TRANSACTION_HISTORY, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                let userTransaction = responseJson.data.result;
                this.setState({
                    userTransaction: userTransaction,
                    total: responseJson.data.total,
                    posting: false
                })

            }
        })
    }
    handleTypeChange = (value) => {
        if (value != null) {
            let filter = this.state.filter;
            filter['status'] = value.value;
            this.setState({ filter, FilterChange: value }, () => { this.getTransaction() })
        }
    }
    exportTransaction = () => {
        let { filter } = this.state

        let tempFromDate = moment(filter.from_date).format("YYYY-MM-DD");
        let tempToDate = moment(filter.to_date).format("YYYY-MM-DD");
        var UserUniqueid = this.props.userBasic.user_unique_id ? this.props.userBasic.user_unique_id : this.props.user_unique_id
        var query_string = 'items_perpage=' + filter.items_perpage + '&total_items=0&current_page=' + filter.current_page + '&payment_type=&from_date=' + tempFromDate + '&to_date=' + tempToDate + '&sort_order=' + filter.sort_order + '&sort_field=' + filter.sort_field + '&frombalance=0&tobalance=&keyword=' + UserUniqueid + '&csv=true'

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        window.open(NC.baseURL + 'adminapi/finance/get_all_transaction?' + query_string, '_blank');
    }
    getRank() {
        let params = {
            user_id: this.props.userBasic.user_id
        }
        WSManager.Rest(NC.baseURL + NC.GET_USER_NOSQL_DATA, params).then((responseJson) => {
            if (responseJson.response_code === NC.successCode) {
                this.setState({
                    RankData: responseJson.data
                })
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000);
        })
    }

    getPromoCodeData() {
        let params = {
            user_id: this.props.userBasic.user_id,
        }

        PC_getUserPromoCodeData(params).then(ResponseJson => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    UserPcodeData: ResponseJson.data ? ResponseJson.data : [],
                })
            } else {
                notify.show(NC.SYSTEM_ERROR, "error", 3000)
            }
        }).catch(error => {
            notify.show(NC.SYSTEM_ERROR, "error", 3000)
        })
    }
    AutoToolTipToggle = () => {
        this.setState({ isShowAutoToolTip: !this.state.isShowAutoToolTip });
    }
    cashbackAdd = (userBasic) =>{
        const bothValueAdd = parseFloat(userBasic.balance) + parseFloat(userBasic.cb_balance)
        const valueAdd = parseFloat(bothValueAdd).toFixed(2)
        return valueAdd;
    }

    render() {
        let { userTransaction, total, filter, PERPAGE, FilterChange, RankData, ALLOW_COIN_MODULE, UserPcodeData,isShowAutoToolTip } = this.state
        let { userBasic, DashboardTranProps } = this.props
        return (
            <Fragment>
                <div className="transaction">
                    {
                        DashboardTranProps && (
                            <Row>
                                <Col md={8}>
                                    <div className="float-left">
                                        <label className="filter-label">To Date</label>
                                        <DatePicker
                                            maxDate={new Date(filter.to_date)}
                                            className="filter-date mr-1"
                                            showYearDropdown='true'
                                            selected={filter.from_date}
                                            onChange={e => this.handleChange(e, "from_date")}
                                            placeholderText="From"
                                            dateFormat='dd/MM/yyyy'
                                        />
                                    </div>


                                    <div>    
                                        <label className="filter-label">From Date</label>
                                        <DatePicker
                                            minDate={new Date(filter.from_date)}
                                            maxDate={new Date()}
                                            className="filter-date"
                                            showYearDropdown='true'
                                            selected={filter.to_date}
                                            onChange={e => this.handleChange(e, "to_date")}
                                            placeholderText="To"
                                            dateFormat='dd/MM/yyyy'
                                           
                                        />
                                    </div>
                                </Col>
                                <Col md={4}>
                                    <div className="filter-right-box clearfix">
                                        <div className="filter-export">
                                            <i className="icon-export" onClick={e => this.exportTransaction()}></i>
                                        </div>
                                        <div className="filter-box">

                                            <Select
                                                isSearchable={false}
                                                isClearable={false}
                                                class="trans-filter"
                                                options={options}
                                                placeholder="Filters"
                                                value={FilterChange}
                                                onChange={e => this.handleTypeChange(e)}
                                            />
                                        </div>
                                    </div>
                                </Col>
                            </Row>
                        )
                    }
                    {
                        DashboardTranProps && (
                            <Row className="mt-4 mb-3">
                                <Col md={12}>
                                    <ul className="calculations-list">
                                        <li className="calculations-item total-box">
                                            <div className="total-deposit">
                                                <label>Total Deposit</label>
                                                {
                                                    (!_.isEmpty(RankData.balance)) ?
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{RankData.balance ? RankData.balance : '0'}</div>
                                                        :
                                                        <div className="numbers">--</div>
                                                }
                                            </div>
                                            <div>
                                                <label>Total Withdraw</label>
                                                {
                                                    RankData.total_withdraw ?
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{RankData.total_withdraw}</div>
                                                        :
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;0</div>
                                                }
                                            </div>
                                        </li>
                                        <li className="calculations-seprator"></li>
                                        <li className="calculations-item width-145">
                                            <div>
                                                <label>Current Balance</label>
                                                <div className="numbers">{HF.getCurrencyCode()}&nbsp;
                                                    {
                                                        (parseFloat(userBasic.balance) +
                                                        + parseFloat(userBasic.cb_balance) +
                                                            parseFloat(userBasic.bonus_balance) +
                                                            parseFloat(userBasic.winning_balance)).toFixed(2)
                                                    }
                                                </div>
                                            </div>
                                        </li>
                                        <li className="calculations-sign body-text">=</li>
                                        <li className="calculations-item width-108">
                                            <div>
                                                <label>Deposit {
                                                HF.allowGst() == 1 && HF.allowGstType() == "new" &&
                                                 <> <i className="icon-info-border cursor-pointer info-cb-bonus" id="AutoTooltip" />
                                                    <Tooltip
                                                        placement="right"
                                                        isOpen={isShowAutoToolTip} target="AutoTooltip"
                                                        toggle={() => this.AutoToolTipToggle(1)}
                                                    >
                                                        <div className="wallet-information-view">
                                                            <div className="value-view">
                                                                <div>Deposit :</div>
                                                                <div>Cashback :</div>

                                                            </div>
                                                            <div className="value-view">
                                                                <div className='value-number'>{HF.getCurrencyCode()}&nbsp;{userBasic.balance}</div>
                                                                <div className='value-number'>{HF.getCurrencyCode()}&nbsp;{userBasic.cb_balance}</div>
                                                            </div>
                                                        </div>
                                                    </Tooltip>
                                                </>}</label>
                                                {
                                                    userBasic.balance ?
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{this.cashbackAdd(userBasic)}</div>
                                                        :
                                                        <div className="numbers">--</div>
                                                }
                                            </div>
                                        </li>
                                        <li className="calculations-sign">+</li>
                                        <li className="calculations-item width-108">
                                            <div>
                                                <label>Bonus</label>
                                                {
                                                    userBasic.bonus_balance ?
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{userBasic.bonus_balance}</div>
                                                        :
                                                        <div className="numbers">--</div>
                                                }
                                            </div>
                                        </li>
                                        <li className="calculations-sign">+</li>
                                        <li className="calculations-item width-108">
                                            <div>
                                                <label>Winning</label>
                                                {
                                                    userBasic.winning_balance ?
                                                        <div className="numbers">{HF.getCurrencyCode()}&nbsp;{userBasic.winning_balance}</div>
                                                        :
                                                        <div className="numbers">--</div>
                                                }
                                            </div>
                                        </li>

                                        {
                                            ALLOW_COIN_MODULE == 1 && (
                                                <Fragment>
                                                    <li className="calculations-seprator"></li>
                                                    <li className="calculations-item width-145">
                                                        <div>
                                                            <label>Coins Balance</label>
                                                            <div className="numbers">
                                                                <b>C</b>
                                                                {
                                                                    parseInt(userBasic.point_balance)
                                                                }
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li className="calculations-sign body-text">=</li>
                                                    <li className="calculations-item width-108">
                                                        <div>
                                                            <label>Earned</label>
                                                            {
                                                                userBasic.earned_coins ?
                                                                    <div className="numbers">C{userBasic.earned_coins}</div>
                                                                    :
                                                                    <div className="numbers">--</div>
                                                            }
                                                        </div>
                                                    </li>
                                                    <li className="calculations-sign">-</li>
                                                    <li className="calculations-item width-108">
                                                        <div>
                                                            <label>Redeem</label>
                                                            {
                                                                userBasic.redeem_coins ?
                                                                    <div className="numbers">C{userBasic.redeem_coins}</div>
                                                                    :
                                                                    <div className="numbers">--</div>
                                                            }
                                                        </div>
                                                    </li>
                                                    <li className="calculations-seprator"></li>
                                                    <li className="calculations-item total-box">
                                                        <div className="total-deposit">
                                                            <label>Promo code Used</label>
                                                            {
                                                                (!_.isEmpty(UserPcodeData.p_total)) ?
                                                                    <div className="numbers">#{UserPcodeData.p_total ? UserPcodeData.p_total : '0'}</div>
                                                                    :
                                                                    <div className="numbers">--</div>
                                                            }
                                                        </div>
                                                        <div className="text-center">
                                                            <div className="numbers">
                                                                {HF.getCurrencyCode()}&nbsp;{UserPcodeData.real_amount ? UserPcodeData.real_amount : '0'}
                                                            </div>

                                                            {
                                                                <div className="numbers">
                                                                    B&nbsp; {UserPcodeData.bonus_amount ? UserPcodeData.bonus_amount : '0'}
                                                                </div>
                                                            }
                                                        </div>
                                                    </li>
                                                </Fragment>)
                                        }
                                    </ul>
                                </Col>
                            </Row>

                        )
                    }
                    <Row>
                        <Col md={12} className="common-table table-responsive">
                            <Table>
                                <thead>
                                    <tr>
                                        <th rowSpan="2" className="pl-5 left-th">Date</th>
                                        <th rowSpan="2">Status</th>
                                        <th rowSpan="2">Transaction ID</th>
                                        <th rowSpan="2">Description</th>
                                        <th rowSpan="2">Amount</th>
                                        <th rowSpan="2">Merchandise</th>
                                        {
                                            HF.allowCryto() == '1' && <th rowSpan="2">Crypto Detail</th>
                                        }
                                      
                                        <th colSpan={ALLOW_COIN_MODULE == 1 ? "4" : "3"} className="text-center bt-right">Closing Balance</th>                                        
                                    </tr>
                                    <tr className="balance-type">
                                        <th>Real</th>
                                        <th>Bonus</th>
                                        <th>Winnings</th>
                                        {ALLOW_COIN_MODULE == 1 && <th className="bb-right">Coins</th>}                                        
                                    </tr>                                    
                                </thead>
                                {
                                    _.map(userTransaction, (item, idx) => {
                                        let item_prize_data = (item.custom_data) ? item.custom_data : [];
                                        let row_prize_data = (item_prize_data.length > 0) ? item_prize_data : [];
                                        let admin_data = !_.isNull(item.custom_data) ? item.custom_data : '';

                                        let admin_name = '';
                                        if (!_.isEmpty(admin_data) && typeof admin_data === 'string') {
                                            let jPData = JSON.parse(admin_data)
                                            if (!_.isUndefined(jPData.first_name)) {
                                                admin_name = "(" + HF.capitalFirstLetter(jPData.first_name) + ")";
                                            }
                                        }

                                        /**Start For stock fantsy */
                                        if (item.source == '462') {
                                            _.map(item_prize_data.prize_data, (stkdata, idx) => {
                                                if (stkdata.prize_type == "0") {
                                                    item.bonus_amount = stkdata.amount
                                                }
                                                if (stkdata.prize_type == "1") {
                                                    item.real_amount = stkdata.amount
                                                }
                                                if (stkdata.prize_type == "2") {
                                                    item.points = stkdata.amount
                                                }
                                                if (stkdata.prize_type == "3") {
                                                    item.mer_name = stkdata.name
                                                }
                                            })

                                        }
                                        /**End For stock fantsy */

                                        let cryto_curr = ''
                                        let cryto_wallet = ''
                                        let cryto_coins = ''
                                        if (!_.isEmpty(admin_data) && typeof admin_data === 'object') {
                                            cryto_curr = (!_.isUndefined(admin_data.deposit_crypto)) ? admin_data.deposit_crypto : '';
                                            cryto_wallet = (!_.isUndefined(admin_data.deposit_to_addr)) ? admin_data.deposit_to_addr : '';
                                            cryto_coins = (!_.isUndefined(admin_data.deposit_crypto_amt)) ? admin_data.deposit_crypto_amt : '';
                                        }
                                        return (
                                            <tbody key={idx}>
                                                <tr>
                                                    <td className="pl-5">
                                                        {/* <MomentDateComponent data={{ date: item.date_added, format: "D MMMM YY" }} /> */}
                                                        {HF.getFormatedDateTime(item.date_added, "D MMMM YY")}
                                                    </td>
                                                    {
                                                        <td className={item.status === "0" ? "pending" : item.status === "1" ? "success" : "failed"}>
                                                            {item.status === "0" ? "Pending" : item.status === "1" ? "Success" : "Failed"}
                                                        </td>

                                                    }
                                                    <td>{item.transaction_id ? item.transaction_id : '--'}</td>

                                                    <td className="desc-width">
                                                        {
                                                            (!_isUndefined(item_prize_data.promo_code) && (item.source == '30' || item.source == '31' || item.source == '32')) &&
                                                            <span className="pcode">{item_prize_data.promo_code}{' '}</span>
                                                        }
                                                        {item.trans_desc ? item.trans_desc + ' ' + admin_name : '--'}
                                                    </td>

                                                    <td>{HF.getCurrencyCode()}&nbsp;{HF.getNumberWithCommas(item.real_amount)}</td>
                                                    <Fragment><td>
                                                                    {item.merchandise || '--'}
                                                                </td>
                                                        {/* {
                                                            item.source == '462' ?
                                                                <td>{item.mer_name ? item.mer_name : '--'}</td>
                                                                :
                                                                item.source == '372' || item.source == '531' ?
                                                                <td>
                                                                    {item.custom_data && item.custom_data.merchandise ? item.custom_data.merchandise : '--'}
                                                                </td>
                                                                :
                                                                <Fragment>
                                                                    {
                                                                        row_prize_data.length > 0 &&
                                                                        <td>
                                                                            {(row_prize_data[0].prize_type) ?
                                                                                (row_prize_data[0].prize_type == 3) ?
                                                                                    row_prize_data[0].name :
                                                                                    '' :
                                                                                ''}
                                                                        </td>
                                                                    }
                                                                    {row_prize_data.length == 0 &&
                                                                        <td>--</td>
                                                                    }
                                                                </Fragment>
                                                        } */}
                                                    </Fragment>

                                                        
                                                    <td>
                                                        {HF.getCurrencyCode()}&nbsp;
                                                        {HF.getNumberWithCommas(item.real_amount)}
                                                        {/* {item.real_amount} */}
                                                    </td>
                                                    <td>
                                                        {HF.getCurrencyCode()}&nbsp;
                                                        {HF.getNumberWithCommas(item.bonus_amount)}
                                                        {/* {item.bonus_amount} */}
                                                    </td>
                                                    <td>
                                                        {HF.getCurrencyCode()}&nbsp;
                                                        {HF.getNumberWithCommas(item.winning_amount)}
                                                        {/* {item.winning_amount} */}
                                                    </td>
                                                    {ALLOW_COIN_MODULE == 1 &&
                                                        <td>
                                                            <b>C</b>
                                                            {/* {item.points} */}
                                                            {HF.getNumberWithCommas(item.points)}
                                                        </td>
                                                    }
                                                    {/* {row_prize_data.length == 0 &&
                                                        <td>--</td>
                                                    }
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
                                                    <td>{HF.getCurrencyCode()}&nbsp;{item.real_amount}</td>
                                                    <td>{HF.getCurrencyCode()}&nbsp;{item.bonus_amount}</td>
                                                    <td>{HF.getCurrencyCode()}&nbsp;{item.winning_amount}</td>
                                                    {ALLOW_COIN_MODULE == 1 && <td><b>C</b>{item.points}</td>}                                                     */}
                                                </tr>
                                            </tbody>
                                        )
                                    })
                                }
                            </Table>
                            {
                                (DashboardTranProps && total > PERPAGE) && (
                                    <div className="custom-pagination userlistpage-paging float-right">
                                        <Pagination
                                            activePage={filter.current_page}
                                            itemsCountPerPage={filter.items_perpage}
                                            totalItemsCount={total}
                                            pageRangeDisplayed={5}
                                            onChange={e => this.handlePageChange(e)}
                                        />
                                    </div>
                                )
                            }
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}