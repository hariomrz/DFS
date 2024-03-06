
import React, { Component } from "react";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import { Utilities, _Map } from '../../Utilities/Utilities';
import { MomentDateComponent } from "./CustomComponents";
import Images from "../../components/images";
import { QrCodeCryptoModal } from "../Finance";
import * as WSC from "../../WSHelper/WSConstants";
import WSManager from "../../WSHelper/WSManager";
// var currency_code = Utilities.getMasterData().currency_code;
export default class TransactionList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            transList: this.props.transactionHistoryList,
            ExpandedIndex: -1,
            selectedTAB: this.props.selectedTAB,
            currency_code: Utilities.getMasterData().currency_code,
            showQrCodeModal: false,
            dummyData: [
                { "prize_type": "1", "amount": "14" },
                { "prize_type": "0", "amount": "10" },
                { "prize_type": "2", "amount": "15" }
            ]
        }
    }

    componentDidMount() {
        Utilities.handleAppBackManage('TransactionList')
    }

    UNSAFE_componentWillReceiveProps(nextProps) {
        if (this.state.transList != nextProps.transactionHistoryList) {
            this.setState({
                transList: nextProps.transactionHistoryList,
                selectedTAB: nextProps.selectedTAB
            })
        }
    }

    expand(idx) {
        this.setState({
            ExpandedIndex: idx
        })
    }
    getAmtFormat = (item) => {
        let points = parseFloat(item.points);
        let real = parseFloat(item.real_amount);
        let winning = parseFloat(item.winning_amount);
        let bonus = parseFloat(item.bonus_amount);
        let currency_code = this.state.currency_code;
        if (this.state.selectedTAB == AppLabels.BONUS) {
            return <i className="icon-bonus"></i>
        }
        if (this.state.selectedTAB == AppLabels.WINNINGS || this.state.selectedTAB == AppLabels.DEPOSIT) {
            return currency_code;
        }

        return (real > 0 ? currency_code : points > 0 ? <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14 }} /> : winning > 0 ? currency_code : bonus > 0 ? <i className="icon-bonus"></i> : '');
    }
    calcAmt = (item) => {
        // let points = parseFloat(item.points);
        // var real = parseFloat(item.real_amount);
        // let winning = parseFloat(item.winning_amount);
        // let bonus = parseFloat(item.bonus_amount);
        let winning = parseFloat(item.winning_amount);
        let bonus = parseFloat(item.bonus_amount);
        let points = parseFloat(item.points);
        let cashback = parseFloat(item.cb_amount)
        let realAmt = parseFloat(item.real_amount)
        let totalValue = parseFloat(realAmt) + parseFloat(cashback)
        let real = totalValue;
        if (this.state.selectedTAB == AppLabels.BONUS) {
            return Utilities.numberWithCommas(bonus)
        }
        if (this.state.selectedTAB == AppLabels.WINNINGS) {
            return winning;
        }
        if (this.state.selectedTAB == AppLabels.DEPOSIT) {
            return real;
        }
        if (this.state.selectedTAB == AppLabels.ALL) {
            if (item.source == 372 && item.merchandise) {
                return item.merchandise
            }
            real = real + winning + bonus;
        }
        var value = Utilities.numberWithCommas(real > 0 ? real : points > 0 ? points : winning > 0 ? winning : bonus > 0 ? bonus : '0');
        return value;
    }


    getTransactionStatus(item) {
        if (item.status == 1) {
            return '';
        }
        else if (item.status == 0) {
            return ' (' + AppLabels.TRANSACTION_STATUS_PENDING + ')';
        }
        else if (item.source == "8" && item.status == 2) {
            return ' (' + AppLabels.TRANSACTION_STATUS_REJECTED + ')';
        }
        else {
            return ' (' + AppLabels.TRANSACTION_STATUS_FAILED + ')';
        }
    }

    parseMerchandiseData = (prizeItem, item, idx) => {
        let pAmt = prizeItem.amount;
        let currency_code = this.state.currency_code;
        let custom_data = (item.source == 462 ? item.prize_data : item.source == 502 ? item.prize : item.custom_data);
        if (item.source == 462 || item.source == 502) {
            if (this.state.selectedTAB == AppLabels.BONUS) {
                return (
                    prizeItem.prize_type == 0 ? <div className="no-margin" >
                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                        {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                        {Number(parseFloat(pAmt || 0).toFixed(2))}
                    </div> : ''
                )
            }
            if (this.state.selectedTAB == AppLabels.COINS) {
                return (
                    prizeItem.prize_type == 2 ? <div className="no-margin">
                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                        <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14 }} />
                        {pAmt}
                    </div> : ''
                )
            }
        }
        return (
            (prizeItem.prize_type == 0) ?
                <div className="no-margin" >
                    {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                    {<i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>}
                    {custom_data.length === idx + 1 ? Number(parseFloat(pAmt || 0).toFixed(2)) : Number(parseFloat(pAmt || 0).toFixed(2)) + "/"}
                </div>
                :
                (prizeItem.prize_type == 1) ?
                    <div className="no-margin" >
                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                        {<span className="color-symbol" style={{ display: 'inlineBlock' }}>{currency_code}</span>}
                        {custom_data.length === idx + 1 ? Number(parseFloat(pAmt || 0).toFixed(2)) : Number(parseFloat(pAmt || 0).toFixed(2)) + "/"}
                    </div>
                    :
                    (prizeItem.prize_type == 2) ?
                        <div className="no-margin">
                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                            <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14 }} />
                            {custom_data.length === idx + 1 ? pAmt : pAmt + "/"}
                        </div>
                        :
                        (prizeItem.prize_type == 3) ?
                            <div className="no-margin">
                                {custom_data.length === idx + 1 ? prizeItem.name : prizeItem.name + "/"}
                            </div> : ''
        )
    }

    showTransDesc = (item) => {
        const { trans_desc, custom_data } = item;
        let desc = trans_desc;
        let custom_obj = custom_data || {};
        if (typeof custom_data == 'string') {
            custom_obj = JSON.parse(custom_data)
        }
        if (custom_obj) {
            _Map(custom_obj, (strVal, strKey) => {
                if (trans_desc && strKey && trans_desc.includes(strKey)) {
                    let replaceKey = `{{${strKey}}}`
                    let replaceVal = strVal

                    if (strKey == 'contest') {
                        replaceKey = `{{contest_name}}`
                    }
                    if (strKey == 'match_date') {
                        let startDate = Utilities.getUtcToLocal(item.season_scheduled_date);
                        replaceVal = Utilities.getFormatedDateTime(startDate, "MMM DD, YY - hh:mm a");
                    }
                    if (strKey == 'p_to_id') {
                        replaceVal = AppLabels.replace_PANTOID(AppLabels.PAN);
                    }
                    desc = desc.replace(replaceKey, replaceVal)
                }
            })
        }
        return desc;
    }

    showQrCodeModal = () => {
        this.setState({
            showQrCodeModal: true
        })
    }

    hideQrCodeModal = (value) => {
        this.setState({
            showQrCodeModal: false
        })
    }

    newGSTDwld = (order_unique_id) => {
        let sessionKey = WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '';
        window.open(WSC.baseURL + 'user/finance/gst_invoice_download?' + 'order_unique_id=' + order_unique_id + '&Sessionkey=' + sessionKey, '_blank');
    }

    render() {
        const { transList, currency_code } = this.state;
        return (
            <div className="trans-body-wrap">
                {transList.map((item, idx) => {
                    return (
                        <div key={item + idx} className={"trans-tr-wrap " + (this.state.ExpandedIndex === idx ? 'trans-detail-wrap' : '')} onClick={() => this.expand(idx)}>
                            <div className="trans-tr-view">
                                <div className="trans-td-view v-mid">
                                    <div className="trans-heading">
                                        {this.showTransDesc(item)}
                                        {this.getTransactionStatus(item)}
                                    </div>

                                    <div className="trans-timing">
                                        <MomentDateComponent data={{ date: item.date_added, format: "MMM DD - hh:mm A " }} />
                                    </div>
                                    {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" && item.source == 7 && item.status == 1 &&
                                        <div className="trans-timing">
                                            {CommonLabels.GST_PAID} : {Utilities.getMasterData().currency_code}{' '}{item.tds}
                                        </div>}
                                </div>
                                <div className="trans-td-view v-mid trans-td-view-cashback">
                                    {item.type == 1 &&
                                        (
                                            (this.state.ExpandedIndex !== idx || this.state.selectedTAB != AppLabels.ALL) ?
                                                (
                                                    <div>
                                                        <i className="icon-remove text-danger"></i>
                                                        <span className="color-symbol">{this.getAmtFormat(item)}</span>
                                                        {this.calcAmt(item)}
                                                    </div>
                                                )
                                                :
                                                <React.Fragment>
                                                    {
                                                        item.real_amount > 0 && <div>
                                                            <i className="icon-remove text-danger"></i>
                                                            <span>{currency_code}</span>
                                                            {item.real_amount}
                                                        </div>
                                                    }
                                                    {
                                                        item.cb_amount > 0 && <div>
                                                            <i className="icon-remove text-danger"></i>
                                                            <span>{currency_code}</span>
                                                            {item.cb_amount} <span className="cashback-transcation">({CommonLabels.CASHBACK_TEXT})</span>
                                                        </div>
                                                    }

                                                    {
                                                        item.bonus_amount > 0 && <div>
                                                            <i className="icon-remove text-danger"></i>
                                                            <span className="no-margin-r"><i className="icon-bonus" /></span>{item.bonus_amount}
                                                        </div>
                                                    }
                                                    {
                                                        item.winning_amount > 0 && <div>
                                                            <i className="icon-remove text-danger"></i>
                                                            <span>{currency_code}</span>
                                                            {item.winning_amount}<span className="cashback-transcation">({AppLabels.WIN})</span>
                                                        </div>
                                                    }
                                                    {
                                                        item.points > 0 && <div>
                                                            <i className="icon-remove text-danger"></i>
                                                            <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14 }} />
                                                            {item.points}
                                                        </div>
                                                    }
                                                </React.Fragment>
                                        )
                                    }
                                </div>
                                <div className="trans-td-view v-mid">
                                    {item.type == 0 &&
                                        (
                                            (this.state.ExpandedIndex !== idx || this.state.selectedTAB != AppLabels.ALL) ?
                                                (
                                                    <div>
                                                        {
                                                            item.source != 3 && item.source != 225 && item.source != 226 && item.source != 227 && item.source != 230 &&
                                                                //  item.source != 322 &&
                                                                item.source != 261 && item.source != 262 && item.source != 263 && item.source != 241 && item.source != 264 && item.source != 265 && item.source != 401 && item.source != 462 && item.source != 465 && item.source != 465 && item.source != 502 && item.source != 526
                                                                // && item.source != 531
                                                                // && item.source != 372 && 
                                                                ?
                                                                <React.Fragment>
                                                                    {
                                                                        item.source == 372 && !item.merchandise &&
                                                                        // <></>
                                                                        // :
                                                                        <>
                                                                            {item.status == 1 ?
                                                                                <i className="icon-plus text-success"></i>
                                                                                :
                                                                                <i className="icon-info-down warning"></i>
                                                                            }
                                                                        </>
                                                                    }
                                                                    {
                                                                        (item.source == 531 && item.prize_type == 3) ?
                                                                            <>{item.custom_data.merchandise}</>
                                                                            :
                                                                            <>
                                                                                <span className="no-margin-l color-symbol">
                                                                                    <i className="icon-plus text-success"></i>
                                                                                    {this.getAmtFormat(item)}</span>
                                                                                {this.calcAmt(item)}
                                                                            </>
                                                                    }
                                                                </React.Fragment>
                                                                :
                                                                (
                                                                    <>


                                                                        {
                                                                            item.source != 3 &&
                                                                                (item.source == 462 ? item.prize_data : item.source == 502 ? item.prize : item.custom_data) ?
                                                                                _Map((item.source == 462 ? item.prize_data : item.source == 502 ? item.prize : item.custom_data), (prizeItem, idx) => {
                                                                                    return (<>{this.parseMerchandiseData(prizeItem, item, idx)}</>)
                                                                                })
                                                                                :
                                                                                <React.Fragment>
                                                                                    {
                                                                                        item.real_amount > 0 && <div>
                                                                                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                            <span>{currency_code}</span>
                                                                                            {item.real_amount}
                                                                                        </div>
                                                                                    }
                                                                                    {
                                                                                        item.cb_amount > 0 && <div>
                                                                                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                            <span>{currency_code}</span>
                                                                                            {item.cb_amount}<span className="cashback-transcation">({CommonLabels.CASHBACK_TEXT})</span>
                                                                                        </div>
                                                                                    }

                                                                                    {
                                                                                        item.bonus_amount > 0 && <div>
                                                                                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                            <span className="no-margin-r"><i className="icon-bonus" /></span>{item.bonus_amount}
                                                                                        </div>
                                                                                    }
                                                                                    {
                                                                                        item.winning_amount > 0 && <div>
                                                                                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                            <span>{currency_code}</span>
                                                                                            {item.winning_amount}<span className="cashback-transcation">({AppLabels.WIN})</span>
                                                                                        </div>
                                                                                    }
                                                                                    {
                                                                                        item.points > 0 && <div>
                                                                                            {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                            <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14, marginRight: 3 }} />
                                                                                            {item.points}
                                                                                        </div>
                                                                                    }
                                                                                    {
                                                                                        item.merchandise != '' && <div>
                                                                                            {item.merchandise}
                                                                                        </div>
                                                                                    }
                                                                                </React.Fragment>
                                                                        }
                                                                    </>
                                                                )
                                                        }
                                                    </div>
                                                )
                                                :
                                                <React.Fragment>
                                                    {
                                                        item.source != 3 && item.source != 225 && item.source != 226 && item.source != 227 && item.source != 230 &&
                                                            //  item.source != 322 && 
                                                            item.source != 261 && item.source != 262 && item.source != 263 && item.source != 241 && item.source != 401 && item.source != 462 && item.source != 465 && item.source != 502 && item.source != 526
                                                            // && item.source != 531
                                                            // && item.source != 372 && 
                                                            ?
                                                            <React.Fragment>
                                                                {
                                                                    item.source == 531 && item.prize_type == 3
                                                                    &&
                                                                    <>{item.custom_data.merchandise}</>
                                                                }
                                                                {
                                                                    item.real_amount > 0 && <div>
                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                        <span>{currency_code}</span>
                                                                        {item.real_amount}
                                                                    </div>
                                                                }
                                                                {
                                                                    item.cb_amount > 0 && <div>
                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                        <span>{currency_code}</span>
                                                                        {item.cb_amount}<span className="cashback-transcation">({CommonLabels.CASHBACK_TEXT})</span>
                                                                    </div>
                                                                }
                                                                {
                                                                    item.bonus_amount > 0 && <div>
                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                        <span className="no-margin-r"><i className="icon-bonus" /></span>{item.bonus_amount}
                                                                    </div>
                                                                }
                                                                {
                                                                    item.winning_amount > 0 && <div>
                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                        <span>{currency_code}</span>
                                                                        {item.winning_amount}<span className="cashback-transcation">({AppLabels.WIN})</span>
                                                                    </div>
                                                                }
                                                                {
                                                                    item.points > 0 && <div>
                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                        <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14, marginRight: 3 }} />
                                                                        {item.points}
                                                                    </div>
                                                                }
                                                            </React.Fragment>
                                                            :
                                                            (
                                                                <>
                                                                    {
                                                                        (item.source == 462 ? item.prize_data : item.custom_data) ?
                                                                            _Map((item.source == 462 ? item.prize_data : item.custom_data), (prizeItem, idx) => {
                                                                                return this.parseMerchandiseData(prizeItem, item, idx)
                                                                            })
                                                                            :
                                                                            <React.Fragment>
                                                                                {
                                                                                    item.real_amount > 0 && <div>
                                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                        <span>{currency_code}</span>
                                                                                        {item.real_amount}
                                                                                    </div>
                                                                                }
                                                                                {
                                                                                    item.cb_amount > 0 && <div>
                                                                                        <i className="icon-remove text-danger"></i>
                                                                                        <span>{currency_code}</span>
                                                                                        {item.cb_amount} <span className="cashback-transcation">({CommonLabels.CASHBACK_TEXT})</span>
                                                                                    </div>
                                                                                }
                                                                                {
                                                                                    item.bonus_amount > 0 && <div>
                                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                        <span className="no-margin-r"><i className="icon-bonus" /></span>{item.bonus_amount}
                                                                                    </div>
                                                                                }
                                                                                {
                                                                                    item.winning_amount > 0 && <div>
                                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                        <span>{currency_code}</span>
                                                                                        {item.winning_amount}<span className="cashback-transcation">({AppLabels.WIN})</span>
                                                                                    </div>
                                                                                }
                                                                                {
                                                                                    item.points > 0 && <div>
                                                                                        {item.status == 1 ? <i className="icon-plus text-success"></i> : <i className="icon-info-down warning"></i>}
                                                                                        <img src={Images.IC_COIN} alt="" style={{ maxWidth: 14, marginRight: 3 }} />
                                                                                        {item.points}
                                                                                    </div>
                                                                                }
                                                                            </React.Fragment>
                                                                    }
                                                                </>
                                                            )
                                                    }
                                                </React.Fragment>
                                        )
                                    }
                                </div>
                            </div>
                            <div className="trans-detail">
                                <div>
                                    {AppLabels.TRANS_ID}
                                    <span>
                                        {item.order_unique_id}
                                    </span>
                                </div>
                                <div>
                                    {AppLabels.STATUS}
                                    <span className={"text-status-crypto" + (item.status == "0" ? '  pending' : item.status != "1" ? ' failed' : '')}>
                                        {item.status == "0" ? AppLabels.TRANSACTION_STATUS_PENDING
                                            :
                                            item.source == "8" && item.status == "2" ? AppLabels.TRANSACTION_STATUS_REJECTED
                                                :
                                                item.status == "1" ? AppLabels.TRANSACTION_STATUS_SUCCESS : AppLabels.TRANSACTION_STATUS_FAILED
                                        }
                                    </span>
                                </div>
                                {
                                    Utilities.getMasterData().a_crypto == 1 && item.source == 7 &&
                                    <div onClick={() => this.setState({ cryptoData: item.custom_data, status: item.status }, () => { this.showQrCodeModal() })} style={{ cursor: 'pointer' }} className="view-crypto-desc">
                                        {AppLabels.DETAILS}
                                    </div>
                                }
                                {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" && item.source == 7 && item.status == 1 && item.is_process_gst == 1 &&
                                    <div className="transcation-dowld-gst" onClick={() => this.newGSTDwld(item.order_unique_id)}>GST<i className="icon-download1" /></div>}
                            </div>
                        </div>
                    )
                })
                }
                {
                    this.state.showQrCodeModal && <QrCodeCryptoModal {...this.props} preData={{
                        mShow: this.state.showQrCodeModal,
                        mHide: this.hideQrCodeModal,
                        cryptoData: this.state.cryptoData,
                        status: this.state.status,
                        isTrans: true
                    }} />
                }
            </div>
        )
    }
}