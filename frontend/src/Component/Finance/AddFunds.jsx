import React from 'react';
import { FormGroup, FormControl, ControlLabel } from 'react-bootstrap';
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from '../../helper/AppLabels';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { ApplyPromoCode } from "../../Modals";
import { Utilities, _Map } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { getDealsAPI, getPromoCodes, validateFundPromo, btcPay, getUserAadharDetail, getUserProfile } from '../../WSHelper/WSCallings';
import { BanStateEnabled, DARK_THEME_ENABLE } from '../../helper/Constants';
import WSManager from '../../WSHelper/WSManager';
import Validation from "../../helper/Validation";
import * as Constants from "../../helper/Constants";
import ls from 'local-storage';

import { createBrowserHistory } from 'history';
import CustomLoader from '../../helper/CustomLoader';
import ManualPG from './ManualPG';
const queryString = require('query-string');

var hostName = window.location.host;
var fUrl = window.location.protocol + '//' + hostName + "/my-wallet?status=failure"
var sUrl = window.location.protocol + '//' + hostName + "/my-wallet?status=success"
var pUrl = window.location.protocol + '//' + hostName + "/my-wallet?status=pending"
const history = createBrowserHistory();
const location = history.location;
const parsed = queryString.parse(location.search);
export default class AddFunds extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            otherDeals: [],
            allDeals: [],
            promoCodes: [],
            bestDeal: '',
            selectedDeal: '',
            amount: '',
            isBeastDealSelected: false,
            showPromoCode: false,
            mPromoCodeObj: '',
            fromConfimPopUpFunds: this.props.location.fromConfirmPopupAddFunds,
            contestDataForFunds: this.props.location.contestDataForFunds,
            isStockF: this.props.location.isStockF,
            fromBuyCoin: this.props.location.state && this.props.location.state.fromBuyCoin ? this.props.location.state.fromBuyCoin : '',
            AddAmt: this.props.location.state && this.props.location.state.amountToAdd ? this.props.location.state.amountToAdd : this.props.location.contestDataForFunds ? this.props.location.contestDataForFunds.AmountToAdd : '',
            RFContestId: '',
            isDFSTour: this.props && this.props.location && this.props.location.state && this.props.location.state.isDFSTour ? this.props.location.state.isDFSTour : false,
            isAmountEnter: 0,
            filedPromoCode: '',
            seeMorePromoCode: false,
            PromoCodeListLength: 0,
            isStockPF: this.props.location.isStockPF,
            isLoading: false,
            minDepositAmt: Utilities.getMasterData().min_deposit || 0,
            maxDepositAmt: Utilities.getMasterData().max_deposit || 0,
            aadharData: '',
            profileData: '',
            actualAmountPayable: '',
            actualAmountShow: false,
            haveGSTNumber: false,
            GSTNumber: '',
            editGSTNumber: false,
            GSTValidation: false,
            cashBack : ""
            // GST : Utilities.getMasterData().gst_rate / 100
        }
    }

    componentDidMount() {
        if (this.state.fromConfimPopUpFunds) {
            this.setState({ actualAmountShow: true }, () => this.GSTIncludeActual())

        }

        if (Constants.SELECTED_GAMET == Constants.GameType.DFS && Constants.RFContestId != '') {
            this.setState({
                RFContestId: Constants.RFContestId
            })
        }
        this.getPromoCodesApiCall()

        if (Utilities.getMasterData().a_deal == 1) {
            this.getDeals()
        }
        else {
            this.setState({
                amount: this.state.AddAmt || ''
            })
        }
        let bslist = ls.get('bslist')
        // let banStates = Object.keys(Utilities.getMasterData().banned_state || {});
        let banStates = Object.keys(bslist || {});
        if (BanStateEnabled && !WSManager.getProfile().master_state_id && Utilities.getMasterData().a_aadhar != "1") {
            CustomHeader.showBanStateModal({ isFrom: 'addFunds' });
        } else if (BanStateEnabled && banStates.includes(WSManager.getProfile().master_state_id)) {
            CustomHeader.showBanStateMSGModal({ isFrom: 'addFunds', title: 'You are unable to Deposit Funds', Msg1: 'Sorry, but players from ', Msg2: ' are not able to deposit funds at this time' });
        }
        Utilities.setScreenName('addfunds')
        if (WSManager.loggedIn() && Utilities.getMasterData().a_aadhar == "1") {
            if (WSManager.getProfile().aadhar_status != 1) {
                getUserAadharDetail().then((responseJson) => {
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ aadharData: responseJson.data }, () => {
                            WSManager.updateProfile(this.state.aadharData)
                        });
                    }
                })
            }
            else {
                let aadarData = {
                    'aadhar_status': WSManager.getProfile().aadhar_status,
                    "aadhar_id": WSManager.getProfile().aadhar_detail.aadhar_id
                }
                this.setState({ aadharData: aadarData });
            }
        }
        if (WSManager.loggedIn()) {
            getUserProfile().then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    this.setState({ profileData: responseJson.data, GSTNumber: responseJson.data.gst_number ? responseJson.data.gst_number : "", GSTValidation: responseJson.data.gst_number ? true : false });
                }
            })
        }

    }

    getDeals() {
        let param = {}
        this.setState({ isLoaderShow: true })
        getDealsAPI(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ allDeals: responseJson.data })
                this.getBestDeal(responseJson.data)
            }
        })
    }
    getPromoCodesApiCall() {
        let param = {}
        this.setState({ isLoaderShow: true })
        getPromoCodes(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({ promoCodes: responseJson.data })
                if (responseJson.data && responseJson.data.length > 0 && responseJson.data.length > 2) {
                    this.setState({ PromoCodeListLength: 2, seeMorePromoCode: true })
                }
                else if (responseJson.data && responseJson.data.length > 0 && responseJson.data.length <= 2) {
                    this.setState({ PromoCodeListLength: responseJson.data.length, seeMorePromoCode: false })
                }

            }
            this.setState({ isLoaderShow: false })

        })
    }

    getBestDeal(deals) {
        let bestDealIndex = 0;
        let bestDeal = deals[bestDealIndex];
        if (bestDeal) {
            let bestDealAdditionalAmt = (parseFloat(bestDeal.bonus) + parseFloat(bestDeal.cash)) / parseFloat(bestDeal.amount);
            for (let i = 0; i < deals.length; i++) {
                let tempAdditionalAmt = (parseFloat(deals[i].bonus) + parseFloat(deals[i].cash)) / parseFloat(deals[i].amount);
                if (tempAdditionalAmt >= bestDealAdditionalAmt) {
                    bestDealAdditionalAmt = tempAdditionalAmt;
                    bestDealIndex = i;
                    bestDeal = deals[bestDealIndex];
                }
            }
            let otherDeals = deals.filter(item => item != bestDeal);
            this.setState({
                bestDeal: bestDeal,
                otherDeals: otherDeals,
                selectedDeal: '',
                // amount: this.state.fromConfimPopUpFunds ? this.state.contestDataForFunds.AmountToAdd : '', 
                amount: this.state.fromConfimPopUpFunds ? this.state.contestDataForFunds.AmountToAdd : (this.state.fromBuyCoin ? this.state.AddAmt : ''),
                isBeastDealSelected: false
            })
        }
        else {
            this.setState({
                amount: this.state.fromConfimPopUpFunds ? this.state.contestDataForFunds.AmountToAdd : (this.state.fromBuyCoin ? this.state.AddAmt : ''),
            })
        }
        setTimeout(() => {
            const dictOpinion = ls.get('fromOpinion')
            if(dictOpinion && dictOpinion.params && dictOpinion.params.amount){
                this.setState({amount:parseFloat(dictOpinion.params.amount).toFixed(2)})
            }
        }, 100);
    }

    handleChange = (e) => {
        let amt = e.target.value;
        let mAllDeals = this.state.allDeals;
        let isExist = mAllDeals.filter(item => item.amount == amt)
        this.setState({ isAmountEnter: 0 })
        if (isExist.length > 0) {
            this.setState({ amount: amt, selectedDeal: isExist[0], isBeastDealSelected: true, mPromoCodeObj: '', actualAmountShow: true, fromConfimPopUpFunds: false }, () => this.GSTIncludeActual())
        }
        else {
            this.setState({ amount: amt, selectedDeal: '', isBeastDealSelected: false, mPromoCodeObj: '', actualAmountShow: true, fromConfimPopUpFunds: false }, () => this.GSTIncludeActual())
        }
        if (amt == '') {
            this.setState({
                amount: '',
                selectedDeal: '',
                actualAmountShow: false
            })
        }
    }
    handleChangePromoCode = (e) => {
        let promoCode = e.target.value;
        if (promoCode != '') {
            this.setState({ filedPromoCode: promoCode })
        }
        else {
            this.setState({ filedPromoCode: '' })

        }

    }

    goToPaymentOptions() {
        localStorage.removeItem('isAddFundsClicked')
        const { GSTValidation, haveGSTNumber,editGSTNumber } = this.state;

        if ((haveGSTNumber && !GSTValidation) || (haveGSTNumber && !editGSTNumber && !GSTValidation)) {
            Utilities.showToast(CommonLabels.PLEASE_ENTER_VALID_GST_NUMBER, 2500)
        } else {
            let inputAmt = this.state.amount != '' ? this.state.amount : this.state.selectedDeal.amount;
            let isBtc = Utilities.getMasterData().a_btcpay == 1
            if (parseFloat(inputAmt || 0) >= this.state.minDepositAmt && parseFloat(inputAmt || 0) <= this.state.maxDepositAmt && !isBtc) {
                this.props.history.push({
                    pathname: '/payment-method', state: {
                        amount: this.state.amount != '' ? this.state.amount : this.state.selectedDeal.amount,
                        selectedDeal: this.state.selectedDeal,
                        fromConfimPopUpFunds: this.state.fromConfimPopUpFunds,
                        promoCode: this.state.mPromoCodeObj ? this.state.mPromoCodeObj.promo_code : '',
                        isReverseF: this.props && this.props.location.isReverseF || false,
                        isDFSTour: this.state.isDFSTour || false,
                        isStockF: this.state.isStockF || false,
                        isStockPF: this.state.isStockPF || false,
                        amountActual: this.state.actualAmountPayable != '' ? this.state.actualAmountPayable : this.state.selectedDeal.amount,
                        GSTNumber: this.state.GSTNumber
                    }
                })
            }
            else if (parseFloat(inputAmt || 0) >= this.state.minDepositAmt && parseFloat(inputAmt || 0) <= this.state.maxDepositAmt && isBtc) {
                this.DepositBtcPay()

            }
            else {
                let TextMSG = AppLabels.ENTERED_AMOUNT_MUST_BE_BTWN.replace('#MIN', this.state.minDepositAmt)
                TextMSG = TextMSG.replace('#MAX', this.state.maxDepositAmt)
                Utilities.showToast(TextMSG, 2500)
            }
        }

    }

    /**
   * @description method to display promo modal
   */
    PromoCodeShow = () => {
        let inputAmt = this.state.amount != '' ? this.state.amount : this.state.selectedDeal.amount;
        if ((inputAmt != undefined && inputAmt && inputAmt.toString() || '').trim() != '' && parseFloat(inputAmt) >= this.state.minDepositAmt && parseFloat(inputAmt) <= this.state.maxDepositAmt) {
            this.setState({
                showPromoCode: true,
            });
        }
        else {
            let TextMSG = AppLabels.ENTERED_AMOUNT_MUST_BE_BTWN.replace('#MIN', this.state.minDepositAmt)
            TextMSG = TextMSG.replace('#MAX', this.state.maxDepositAmt)
            Utilities.showToast(TextMSG, 2500);
        }
    }
    /**
     * @description method to hide promo modal
     */
    PromoCodeHide = () => {
        this.setState({
            showPromoCode: false,
        });
    }
    /**
     * @description method to apply promo 
     */
    onApplyPromoCode = (obj) => {
        // if(this.state.amount){
        //     this.setState({
        //         mPromoCodeObj: obj,
        //         isAmountEnter:0
        //     });
        // }
        // else{
        //     this.setState({ isAmountEnter: 1});
        // }
        this.validatePromoCode(obj.promo_code)


    }
    /**
    * @description method to apply promo 
    */
    onApplyPromoCodeField = (filedPromoCode) => {
        if (filedPromoCode == '') {
            Utilities.showToast(AppLabels.PLEASE_ENTER_PROMO_CODE)
            return
        }
        this.validatePromoCode(filedPromoCode)


    }

    validatePromoCode = (filedPromoCode) => {
        if (this.state.amount) {
            //this.setState({mPromoCodeObj:promoObj,isAmountEnter:0})
            let param = {
                "amount": this.state.amount,
                "promo_code": filedPromoCode
            }
            validateFundPromo(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.setState({ mPromoCodeObj: responseJson.data, isAmountEnter: 0 })
                } else {
                    Utilities.showToast(responseJson.message)
                    this.setState({ isAmountEnter: 0 })
                }
            })
        }
        else {
            this.setState({ isAmountEnter: 1 });

        }
    }

    addDescriptionMessage = (msg, data) => {
        let msgDescription = msg
        msgDescription = msgDescription.replace("{promo_code}", data.promo_code);
        msgDescription = msgDescription.replace("{discount}", data.discount);
        msgDescription = msgDescription.replace("{cash_type}", data.cash_type == 0 ? AppLabels.BONUS_CASH_CONTEST_LISTING : data.cash_type == 1 ? AppLabels.REAL_CASH : '');
        msgDescription = msgDescription.replace("{benefit_cap}", data.benefit_cap);
        msgDescription = msgDescription.replace("{cash_type}", data.cash_type == 0 ? AppLabels.BONUS_CASH_CONTEST_LISTING : data.cash_type == 1 ? AppLabels.REAL_CASH : '');
        msgDescription = msgDescription.replace("{desposit_range}", Utilities.getMasterData().currency_code + data.min_amount + "-" + Utilities.getMasterData().currency_code + data.max_amount);


        return msgDescription

    }

    showPromoCodeList = (promoCodes, slice) => {
        return (
            promoCodes.slice(0, slice).map((item, index) => {
                return (
                    <div className='promo-code-conatiner'>
                        {/* <div className='description-promo-code'>
                            {
                                this.addDescriptionMessage(item.description, item)
                            }
                        </div> */}
                        <div className='promo-code-layout'>
                        <div className='promo-inner'>
                        <div className='description-promo-code'>
                            {
                                this.addDescriptionMessage(item.description, item)
                            }
                        </div>
                           
                                <div className='code-text'>{item.promo_code}</div>

                            
                        </div>
                            <div onClick={() => this.onApplyPromoCode(item)} className='apply-list-btn'>{AppLabels.APPLY}</div>

                        </div>
                        
                    </div>
                )
            })

        )
    }

    onSeeMore = () => {
        if (this.state.seeMorePromoCode) {
            this.setState({ seeMorePromoCode: false, PromoCodeListLength: this.state.promoCodes.length })

        }
        else {
            this.setState({ seeMorePromoCode: true, PromoCodeListLength: 2 })

        }
    }

    DepositBtcPay = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCodeObj ? this.state.mPromoCodeObj.promo_code : '',
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "currency_type": 'BTC'

        }
        this.setState({ isLoading: true })
        btcPay(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                window.location.href = responseJson.data.qr_code;
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    GSTIncludeActual = () => {
        const { AddAmt, amount, fromConfimPopUpFunds } = this.state;
        const GSTAmt = fromConfimPopUpFunds ? AddAmt : amount
        const GST = (GSTAmt * Utilities.getMasterData().gst_rate / 100);
        const bothValueAdd = parseFloat(GSTAmt) + parseFloat(GST)
        const valueAdd = parseFloat(bothValueAdd).toFixed(2)
        this.setState({ actualAmountPayable: valueAdd },this.cashbackFunction())

    }
    
    GSTNumberShow = () => {
        this.setState({ haveGSTNumber: !this.state.haveGSTNumber, editGSTNumber: false })
    }
    handleChangeGSTNumber = (e) => {
        let amt = e.target.value;
        this.setState({ GSTNumber: amt, GSTValidation: false })
        this.validateField(e.target.name, e.target.value);
    }


    validateField(fieldName, value) {
        switch (fieldName) {
            case 'gstNumber':
                const GSTNoValid = (Validation.validate(fieldName, value) == 'success');
                this.setState({ GSTValidation: GSTNoValid })
                break;
        }

    }
    editGstNumber = () => {
        this.setState({ editGSTNumber: true, GSTValidation: true })
    }
    dealPicks = (bestDeal) => {
        this.setState({
            selectedDeal: bestDeal,
            amount: bestDeal.amount,
            isBeastDealSelected: true,
            actualAmountShow: true,
            fromConfimPopUpFunds: false
        }, () => this.GSTIncludeActual())
    }
    cashbackFunction = () =>{
        const { AddAmt, amount, fromConfimPopUpFunds} = this.state;
        const CaseBackAmt = fromConfimPopUpFunds ? AddAmt : amount
        const CaseBack = (CaseBackAmt * Utilities.getMasterData().gst_bonus / 100);
        const bothValueAdd = parseFloat(CaseBack).toFixed(2) 
        this.setState({ cashBack: bothValueAdd })
    }

    render() {
        const HeaderOption = {
            back: true,
            notification: false,
            title: AppLabels.ADD_FUNDS,
            fromAddFund: false,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            showBal: Constants.OnlyCoinsFlow == 0
        }
        const {AddAmt,fromConfimPopUpFunds, selectedDeal, bestDeal, isBeastDealSelected, amount, showPromoCode, mPromoCodeObj, promoCodes, seeMorePromoCode, isLoading, aadharData,actualAmountPayable, actualAmountShow, haveGSTNumber, GSTNumber, editGSTNumber, GSTValidation, profileData, cashBack } = this.state;
        let TextMSGGST = CommonLabels.GST_INCLUDES.replace('28', Utilities.getMasterData().gst_rate)
        let textBonus = CommonLabels.GET_BONUS_CASHBACK_TEXT.replace('₹', Utilities.getMasterData().currency_code).replace('30', cashBack)

        //gst calc
        const GSTAmt = fromConfimPopUpFunds ? AddAmt : amount
        const GST = (GSTAmt * Utilities.getMasterData().gst_rate / 100);
        return (
            <>
                {Utilities.getMasterData().a_offpg == '0' ?
                    <MyContext.Consumer>
                        {(context) => (
                            <div className="web-container web-container-fixed add-funds-wrapper xwhite-bg">
                                {isLoading && <CustomLoader />}
                                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                                    <title>{MetaData.addfunds.title}</title>
                                    <meta name="description" content={MetaData.addfunds.description} />
                                    <meta name="keywords" content={MetaData.addfunds.keywords}></meta>
                                </Helmet>
                                <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                                <div className="add-funds-header">
                                    <div>
                                        <FormGroup
                                            className={"position-relative show-currency-icn overlay-fixed-view " + (this.state.amount == '' ? ' chnage-icon-color' : '')}
                                            controlId="formBasicText"
                                        >
                                            <ControlLabel>{AppLabels.ENTER_AMOUNT} ({Utilities.getMasterData().currency_code})</ControlLabel>
                                            <FormControl
                                                autoComplete='off'
                                                value={this.state.amount}
                                                type={'number'}
                                                placeholder={'0'}
                                                onChange={this.handleChange}
                                                disabled={this.state.fromBuyCoin}
                                                readOnly={this.state.fromBuyCoin}
                                            />
                                            <span className="forminput-currency">
                                                {Utilities.getMasterData().currency_code}
                                            </span>
                                        </FormGroup>
                                    </div>
                                </div>
                                <div className="add-funds-body">
                                    {Utilities.getMasterData().a_deal == 1 &&
                                        <React.Fragment>
                                            {
                                                <div>
                                                    {
                                                        this.state.otherDeals && this.state.otherDeals.length <= 3 &&
                                                        <div className="amount-section">
                                                            {_Map(this.state.otherDeals, (item, idx) => {
                                                                return (<a key={idx}
                                                                    //  onClick={() => this.setState({ selectedDeal: item, amount: item.amount, isBeastDealSelected: true })}
                                                                    onClick={() => this.dealPicks(item)}
                                                                    className={(item.deal_id == selectedDeal.deal_id ? ' selected' : '')} href id="amt-2">{Utilities.getMasterData().currency_code} {item.amount} {item.deal_id == selectedDeal.deal_id && <i className="icon-tick-ic"></i>}</a>)
                                                            })}
                                                        </div>
                                                    }
                                                    {
                                                        this.state.otherDeals && this.state.otherDeals.length > 3 &&
                                                        <div>
                                                            <div className="amount-section">
                                                                {
                                                                    this.state.otherDeals && this.state.otherDeals.slice(0, 3).map((item, idx) => {
                                                                        return (<a key={idx}
                                                                            // onClick={() => this.setState({ selectedDeal: item, amount: item.amount, isBeastDealSelected: true })} 
                                                                            onClick={() => this.dealPicks(item)}
                                                                            className={(item.deal_id == selectedDeal.deal_id ? ' selected' : '')} href id="amt-2">{Utilities.getMasterData().currency_code} {item.amount} {item.deal_id == selectedDeal.deal_id && <i className="icon-tick-ic"></i>}</a>)

                                                                    })
                                                                }
                                                            </div>
                                                            <div style={{ marginTop: 5 }} className="amount-section">
                                                                {
                                                                    this.state.otherDeals && this.state.otherDeals.slice(3, this.state.otherDeals.length).map((item, idx) => {
                                                                        return (<a key={idx}
                                                                            onClick={() => this.dealPicks(item)}
                                                                            //  onClick={() => this.setState({ selectedDeal: item, amount: item.amount, isBeastDealSelected: true })} 
                                                                            className={(item.deal_id == selectedDeal.deal_id ? ' selected' : '')} href id="amt-2">{Utilities.getMasterData().currency_code} {item.amount} {item.deal_id == selectedDeal.deal_id && <i className="icon-tick-ic"></i>}</a>)

                                                                    })
                                                                }
                                                            </div>
                                                        </div>
                                                    }
                                                </div>
                                            }
                                            {bestDeal &&
                                                <div
                                                    onClick={() => this.dealPicks(bestDeal)}
                                                    //  onClick={() => this.setState({ selectedDeal: bestDeal, amount: bestDeal.amount, isBeastDealSelected: true })}
                                                    className={"best-deal " + (bestDeal.deal_id == selectedDeal.deal_id ? ' selected' : '')}>
                                                    {/* <img src={Images.FAVOURITE} alt="" /> */}
                                                    {AppLabels.PICK_BEST_DEAL}
                                                    <span>{Utilities.getMasterData().currency_code} {bestDeal.amount}</span>
                                                    <i className="icon-tick-ic"></i>
                                                </div>
                                            }
                                            {
                                                isBeastDealSelected && <div className="selected-deal-offer">
                                                    <div className="deposite-heading">{AppLabels.DEPOSIT} <span>{Utilities.getMasterData().currency_code} {this.state.amount != '' ? this.state.amount : this.state.selectedDeal.amount}</span></div>
                                                    <div className="additional-benifit-section">
                                                        <div className="heading">{CommonLabels.YOU_WILL_ADDITIONALLY_GET_TEXT}</div>
                                                        <div className="bonus-offer">
                                                            {selectedDeal.bonus > 0 && <>
                                                                <div>
                                                                    <div className="bonus-amt"><i className="icon-bonus"></i>{selectedDeal.bonus ? selectedDeal.bonus : 0}</div>
                                                                    <div className="bonus-label">{AppLabels.BONUS_CASH}</div>
                                                                </div>
                                                                {/* <div>
                                                                    <i className="icon-plus-ic"></i>
                                                                </div> */}
                                                            </>}
                                                            <div>
                                                                <div className="bonus-amt">{Utilities.getMasterData().currency_code}{selectedDeal.cash ? selectedDeal.cash : 0}</div>
                                                                <div className="bonus-label">{AppLabels.REAL_CASH}</div>
                                                            </div>
                                                            {Utilities.getMasterData().a_coin == 1 && selectedDeal.coin > 0 && <>
                                                                {/* <div>
                                                                    <i className="icon-plus-ic"></i>
                                                                </div> */}
                                                                <div>
                                                                    <div className="bonus-amt"><img className="coin-img" src={Images.IC_COIN} alt="" />{selectedDeal.coin ? selectedDeal.coin : 0}</div>
                                                                    <div className="bonus-label">{AppLabels.COINS}</div>
                                                                </div>
                                                            </>}
                                                        </div>
                                                    </div>
                                                </div>
                                            }

                                            {actualAmountShow && Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" &&
                                             <div className='dpst-summary'>
                                                <div className='dpst-heading'>{CommonLabels.DEPOSITE_SUMMARY_TEXT}</div>
                                                <div className='dpst-inner'>
                                                <div className='dpst-amt d-flex'><span className='amt-txt'>{CommonLabels.DEPOSIT_AMOUNT_TEXT}</span><span className='amt-val'>{Utilities.getMasterData().currency_code} {this.state.amount}</span></div>
                                                <div className='dpst-gst d-flex'><span className='amt-txt'>GST({Utilities.getMasterData().gst_rate}%)</span><span className='amt-val'>{Utilities.getMasterData().currency_code} {GST}</span></div>
                                                </div>
                                                <div className='dpst-total d-flex'><span>{CommonLabels.TOTAL_PAYABLE_TEXT}</span><span className='tot-val'>{Utilities.getMasterData().currency_code} {actualAmountPayable}</span></div>
                                                </div>  
                                            }
                                            {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new"  && Utilities.getMasterData().gst_bonus > 0 && actualAmountShow && <div className="cashback-view">
                                        <div className="cashback-container">
                                            <img src={Images.CASHBACK_IMG} alt="" />
                                            <div className="cashback-text">
                                                {textBonus}
                                            </div>
                                        </div>
                                    </div>}
                                        </React.Fragment>
                                    }


                                    {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" &&
                                        <>
                                            {/* {actualAmountShow &&
                                                <div className='actual-amount-payable'>
                                                    <div className='actual-amount-text'>{CommonLabels.ACTUAL_PAYABLE_AMOUNT}</div>
                                                    <div className='price-text'>{Utilities.getMasterData().currency_code} {actualAmountPayable} <span>{TextMSGGST}</span></div>
                                                </div>
                                            } */}
                                            <div className="gst-number-view">
                                                <div className='gst-checkbox-view'>
                                                    <div className="checkbox-view" onClick={() => this.GSTNumberShow()}><i className={haveGSTNumber ? 'icon-tick-ic' : ""} /></div> <span>{CommonLabels.I_HAVE_GST_NUMBER}</span>
                                                </div>
                                                   
                                                {haveGSTNumber &&
                                                    <div className='gst-input-view'>
                                                        {profileData && profileData.gst_number && !editGSTNumber && <div className='gst-number-text-view'>

                                                            {profileData.gst_number} <i className='icon-edit-line' onClick={() => this.editGstNumber()} />
                                                        </div>}

                                                        {(profileData && profileData.gst_number == '' || editGSTNumber) &&
                                                            <FormGroup
                                                                className='position-relative'
                                                                // className={"position-relative show-currency-icn  overlay-fixed-view " + (this.state.amount == '' ? ' chnage-icon-color' : '')}
                                                                controlId="formBasicText"
                                                            >
                                                                <FormControl
                                                                    autoComplete='off'
                                                                    value={GSTNumber}
                                                                    type='text'
                                                                    maxLength='15'
                                                                    placeholder={CommonLabels.ENTER_15_DIGIT_GST_NUMBER}
                                                                    onChange={this.handleChangeGSTNumber}
                                                                    id='gstNumber'
                                                                    name='gstNumber'
                                                                />
                                                                {GSTNumber != '' && !GSTValidation && <div className='error-gst-msg'>{CommonLabels.PLEASE_ENTER_VALID_GST_NUMBER}</div>}
                                                                {editGSTNumber && <div className='icon-view-gst-close'><i className='icon-close' onClick={() => this.setState({ editGSTNumber: false, GSTValidation: true })} /></div>}

                                                            </FormGroup>

                                                        }
                                                    </div>}
                                            </div>
                                        </>}

                                    {(!isBeastDealSelected || Utilities.getMasterData().a_deal == 0) &&
                                        <React.Fragment>
                                            {mPromoCodeObj == '' ?
                                                // onClick={() => this.PromoCodeShow()}
                                                <div className="promo-code-section">
                                                    <div className="apply-promocode">{AppLabels.APPLY_PROMOCODE}</div>
                                                    <div className="promocode">
                                                        <FormGroup
                                                            controlId="formBasicText"
                                                        >
                                                            <FormControl
                                                                autoComplete='off'
                                                                value={this.state.filedPromoCode}
                                                                type={'text'}
                                                                placeholder={'Type promocode here'}
                                                                onChange={this.handleChangePromoCode}

                                                            />

                                                        </FormGroup>
                                                        <div onClick={() => this.onApplyPromoCodeField(this.state.filedPromoCode)} className='btn-apply'>{AppLabels.APPLY}</div>
                                                    </div>
                                                    {this.state.isAmountEnter == 1 &&
                                                        <div className='no-amount-warning'>
                                                            <i className="icon-warning alert-icon"></i>
                                                            <div className="please-enter-deposit"> {AppLabels.ENTER_DEPOSIT_AMOUNT_WARNING}</div>

                                                        </div>
                                                    }
                                                    {promoCodes && promoCodes.length > 0 &&
                                                        this.showPromoCodeList(promoCodes, this.state.PromoCodeListLength)
                                                    }
                                                    {
                                                        promoCodes && promoCodes.length > 0 && promoCodes.length > 2 &&
                                                        <div onClick={() => this.onSeeMore()} className='see-more-text'>{seeMorePromoCode ? AppLabels.SEE_MORE : AppLabels.SEE_LESS}</div>
                                                    }
                                                </div>
                                                :
                                                <div>
                                                    <div className="promo-code-section">
                                                        <div className="promocode-selected-container">
                                                            <div className="selected-promo-code-layout">
                                                                <div className="inner-layout">
                                                                    <div className="promo-code-text">{mPromoCodeObj.promo_code}</div>
                                                                    <div className="promo-code-apply-text">{AppLabels.PROMO_CODE_APPLIED_TEXT}</div>

                                                                </div>

                                                                <i onClick={() => this.setState({ mPromoCodeObj: "" })} className='icon-close iconc' />
                                                            </div>
                                                        </div>

                                                    </div>
                                                    {
                                                        mPromoCodeObj && <div className="selected-deal-offer bjghjghjgh">
                                                            <div className="deposite-heading">{AppLabels.DEPOSIT} <span>{Utilities.getMasterData().currency_code} {this.state.amount != '' ? this.state.amount : this.state.selectedDeal.amount}</span></div>
                                                            <div className="additional-benifit-section">
                                                                <div className="heading">{CommonLabels.YOU_WILL_ADDITIONALLY_GET_TEXT}</div>
                                                                <div className="bonus-offer">
                                                                    {mPromoCodeObj.cash_type == 0 &&
                                                                        <div>
                                                                            <div className="bonus-amt"><i className="icon-bonus"></i>{mPromoCodeObj.discount ? mPromoCodeObj.discount : 0}</div>
                                                                            <div className="bonus-label">{AppLabels.BONUS_CASH}</div>
                                                                        </div>
                                                                    }
                                                                    {mPromoCodeObj.cash_type == 1 &&
                                                                        <div>
                                                                            <div className="bonus-amt">{Utilities.getMasterData().currency_code}{mPromoCodeObj.discount ? mPromoCodeObj.discount : 0}</div>
                                                                            <div className="bonus-label">{AppLabels.REAL_CASH}</div>
                                                                        </div>
                                                                    }

                                                                </div>
                                                            </div>
                                                        </div>
                                                    }
                                                </div>
                                            }
                                        </React.Fragment>
                                    }
                                    <div style={{ marginTop: 40 }} className={"text-center add-fund" + (this.state.otherDeals && this.state.otherDeals.length > 3 ? ' add-funds-non-stick' : '')}>
                                        <a href
                                            onClick={() =>
                                                // (amount != '' || selectedDeal != '') &&
                                                this.goToPaymentOptions()} className="button button-primary-rounded button-block">{AppLabels.ADD_CASH}</a>
                                        {Constants.OnlyCoinsFlow == 0 && <div className="card-img-section">
                                            {
                                                Utilities.getMasterData().a_btcpay != 1 &&
                                                <img src={Utilities.getMasterData().a_crypto == 1 ? Images.C_ACCOUNT : Images.CARD_IMG} alt="" />

                                            }
                                        </div>}
                                    </div>
                                    {
                                        showPromoCode &&
                                        <ApplyPromoCode
                                            IsPromoCodeShow={showPromoCode}
                                            IsPromoCodeHide={this.PromoCodeHide}
                                            onApplyPromoCode={this.onApplyPromoCode}
                                            mAmount={amount}
                                        />
                                    }

                                </div>
                            </div>
                        )}
                    </MyContext.Consumer>
                    :
                    <ManualPG />}
            </>
        )
    }
}