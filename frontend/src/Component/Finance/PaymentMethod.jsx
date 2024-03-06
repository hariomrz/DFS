import React, { Suspense, lazy } from 'react';
import { Row, Col, FormGroup, Button } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import CustomHeader from '../../components/CustomHeader';
import MetaData from "../../helper/MetaData";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from '../../helper/AppLabels';
import Images from '../../components/images';
import { Utilities, _isUndefined } from '../../Utilities/Utilities';
import * as WSC from "../../WSHelper/WSConstants";
import { SELECTED_GAMET, GameType, PAYMENT_TYPE, DARK_THEME_ENABLE } from "../../helper/Constants";
import { depositPaytmFund, depositPayUmoneyFund, joinContest, depositIPAYFund, depositPayPalFund, depositPayStackFund, depositRazorPayFund, joinContestWithMultiTeam, callBuyCoins, getCashFreeGatewayList, joinContestNetworkfantasy, joinContestWithMultiTeamNF, joinDFSTour, depositStripe, depositVpayFund, depositSiriusPay, depositIFantasyFund, stockJoinContest, joinStockContestWithMultiTeam, joinContestH2H, depositCrypto, depositCashierPay, depositCashFreeFund, joinContestLF, depositPayLogic, upiIntentCallback, GetPFJoinGame, mpesaDeposit, payuCallback, LSFJoinContest, getPTJoinTour, depositDirectPay, propsSaveTeam, depositPhonepeFund, depositJuspeFund, phonePeCallback, jusPayCallback  } from '../../WSHelper/WSCallings';
import WSManager from '../../WSHelper/WSManager';
import { createBrowserHistory } from 'history';
import Thankyou from '../../Modals/Thankyou';
import EditStateAndCityModal from '../../Modals/EditStateAndCityModal';
import CustomLoader from '../../helper/CustomLoader';
import * as Constants from "../../helper/Constants";
import ls from 'local-storage';
import UnableJoinContest from '../../Modals/UnableJoinContest';
import { QrCodeCryptoModal } from '.';
import _ from "lodash";
import ManualPG from './ManualPG';
const ReactHTMLParser = lazy(() => import('../CustomComponent/ReactHTMLParser'));
const ReactSelectDD = lazy(() => import('../CustomComponent/ReactSelectDD'));
const queryString = require('query-string');

const getcp = window.ReactNativeWebView ? '' : ((Utilities.getCpSessionPath() != 'null') ? Utilities.getCpSessionPath().replace('?', '&') : '');

var hostName = window.location.host;
var fUrl = window.location.protocol + '//' + hostName + "/payment-method?status=failure" + getcp
var sUrl = window.location.protocol + '//' + hostName + "/payment-method?status=success" + getcp
var pUrl = window.location.protocol + '//' + hostName + "/payment-method?status=pending" + getcp
const history = createBrowserHistory();
const location = history.location;
const parsed = queryString.parse(location.search);

export default class PaymentMethod extends React.Component {
    constructor(props) {

        super(props);
        this.state = {
            amount: this.props.location.state ? this.props.location.state.amount : '',
            amountActual : this.props.location.state ? this.props.location.state.amountActual : '',
            GSTNumber: this.props.location.state ? this.props.location.state.GSTNumber : '',
            selectedDeal: this.props.location.state ? this.props.location.state.selectedDeal : "",
            PageContent: '',
            showThankYouModal: false,
            isLoading: false,
            mPromoCode: this.props.location.state ? this.props.location.state.promoCode : '',
            showStateTagModal: false,
            isCMounted: false,
            walletList: [],
            netBankingList: [],
            upiList: [{
                "payment_option": "upi",
                "upiMode": "gpay"
            }],
            paymentTypeSelected: '',
            checkIfCashfreeExist: false,
            selectedWallet: '',
            walletDropDownList: [],
            selectedWalletOption: '',

            selectedNetBanking: '',
            netBankingDropDownList: [],
            selectedNBoption: '',

            selectedUPI: '',
            upiDropDownList: [],
            selectedUPIoption: '',

            cardNumber: '',
            nameOnCard: '',
            expiryDate: '',
            cvvNumber: '',
            RFContestId: '',
            isDFSTour: this.props && this.props.location && this.props.location.state && this.props.location.state.isDFSTour ? this.props.location.state.isDFSTour : false,
            isStockF: this.props.location && this.props.location.state ? this.props.location.state.isStockF : false,
            showUJC: false,
            isStockPF: this.props.location && this.props.location.state ? this.props.location.state.isStockPF : false,
            showQrCodeModal:false,
            bn_state: localStorage.getItem('banned_on'),
            geoPlayFree: localStorage.getItem('geoPlayFree'),
            show: false,
            type: '',
            type_id:''

        }
        this.handelNativePayu = this.handelNativePayu.bind(this);
        this.handleUpiIntent = this.handleUpiIntent.bind(this);
        this.handleVapyIntent = this.handleVapyIntent.bind(this);
        this.handelNativePhonePe = this.handelNativePhonePe.bind(this);

    }
    showQrCodeModal = () => {
        this.setState({
            showQrCodeModal: true
        })
    }

    hideQrCodeModal = (value) => {
        this.setState({
            showQrCodeModal: false
        }, () => {
            this.props.history.replace({ pathname: '/my-wallet' });

        })
    }

    UNSAFE_componentWillMount() {
        if (!window.ReactNativeWebView && parsed.platform_app && (parsed.platform_app == 'ios')) {
            window.location.replace(`${process.env.REACT_APP_DEEPLINK_SCHEMA}://${hostName}/payment-method?status=${parsed.status}&amount=${parsed.amount}`)
        } else {
            if (this.props.location.state && this.props.location.state.from_stripe_pg) {
                parsed['status'] = this.props.location.state.stripe_status ? 'success' : 'pending'
                this.checkTransactionFlow('STRIPE')
            } else {
                this.checkTransactionFlow('WILLMOUNT')
            }
            Utilities.setScreenName('transactions')
        }
    }

    checkTransactionFlow = (method) => {

        let tempIsAddFundsClicked = WSManager.getFromFundsOnly();
        let contestData = WSManager.getContestFromAddFundsAndJoin()
        let FromConfirmPopupAddFunds = WSManager.getFromConfirmPopupAddFunds();
        let calledFrom = WSManager.getPaymentCalledFrom();
        let contestCoinData = WSManager.getContestFromAddCoinAndJoin();
        let isDFSTourEnable = WSManager.getDFSTourEnabel();
        if (isDFSTourEnable) {
            this.setState({
                isDFSTour: isDFSTourEnable
            })
        }
        setTimeout(() => {
            function _updateStatus() {
                Utilities.gtmEventFire('Paymentgateway', {
                    'payment_status': parsed.status
                })
            }
            if (tempIsAddFundsClicked != 'true' && (!this.props.location.state || !this.props.location.state.amount)) {
                window.location.assign('/my-wallet')
            }
            else {
                if (tempIsAddFundsClicked == 'true') {
                    if (parsed.status == "success") {
                        if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
                            let singular_data = {};
                            let singular_dep_amt = localStorage.getItem('singular_dep_amt');

                            singular_data.amount = singular_dep_amt;
                            singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                            singular_data.user_name = WSManager.getProfile().user_name;
                            singular_data.email = WSManager.getProfile().email;
                            singular_data.phone_no = WSManager.getProfile().phone_no;
                            singular_data.currency = Utilities.getMasterData().currency;

                            if (window.ReactNativeWebView) {
                                let data = {
                                    action: 'singular_event',
                                    targetFunc: 'onSingularEventTrack',
                                    type: 'Funds_deposited',
                                    args: singular_data,
                                }
                                window.ReactNativeWebView.postMessage(JSON.stringify(data));
                            }
                            else {
                                window.SingularEvent("Funds_deposited", singular_data);
                            }
                        }
                        Utilities.showToast(AppLabels.Your_payment_successful, 1500);
                        _updateStatus()
                    } else if (parsed.status == "failure") {
                        Utilities.showToast(AppLabels.Your_payment_failed, 1500);
                        _updateStatus()
                    } else if (parsed.status == "pending") {
                        Utilities.showToast(AppLabels.Your_payment_pending, 1500);
                        _updateStatus()
                    }
                    WSManager.setFromFundsOnly(false);
                    this.callOnlyAfterTransactions(contestData, FromConfirmPopupAddFunds, calledFrom, contestCoinData, isDFSTourEnable)
                }
            }
        }, 1000);
    }

    callBuyCoinsApi(contestData, id) {
        let param = {
            "package_id": id
        }
        callBuyCoins(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast('You have successfully Buy Coins', 2000);
            }
            else if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
            if (contestData != '') {
                this.CallJoinGameApi(contestData)
            }
            else {
                let mHist = parseInt(ls.get('back_history') || 0);
                let newHistory = this.props.history.length;
                let updatedHistory = (newHistory - mHist) + 3;
                this.props.history.go(-updatedHistory)
            }
        })
    }

    goToBuyCoinsScreen() {
        let mHist = parseInt(ls.get('back_history') || 0);
        let newHistory = this.props.history.length;
        let updatedHistory = (newHistory - mHist) + 2;
        this.props.history.go(-updatedHistory)
    }


    componentDidMount = () => {
        if ((parsed.platform_app && (parsed.platform_app == 'ios' || parsed.platform_app == 'android')) ){ return }
        if (Constants.SELECTED_GAMET == Constants.GameType.DFS && Constants.RFContestId != '') {
            ls.set('RFContestID', Constants.RFContestId)
            this.setState({
                RFContestId: Constants.RFContestId
            })
        }
        window.addEventListener('message', this.handelNativePayu, false);
        window.addEventListener('message', this.handleUpiIntent, false);
        window.addEventListener('message', this.handelNativeJusPe, false);
        window.addEventListener('message', this.handleVapyIntent, false);
        window.addEventListener('message', this.handelNativePhonePe, false);

        this.checkIfCashfreeExist();
        this.setState({
            isCMounted: true
        })
        if (process.env.REACT_APP_STATE_TAGGING_ENABLE > 0 && (!WSManager.getProfile().master_state_id || !WSManager.getProfile().city)) {
            this.setState({
                showStateTagModal: true
            })
        }

    }

    componentWillUnmount() {
        window.removeEventListener('message', this.handelNativePayu, false);
        window.removeEventListener('message', this.handleUpiIntent, false);
        window.removeEventListener('message', this.handelNativeJusPe, false);
        window.removeEventListener('message', this.handleVapyIntent, false);
        window.removeEventListener('message', this.handelNativePhonePe, false);

    }
    handleVapyIntent(e) {
        if (e.data.action == 'vpay_callback') {
            window.location.href = _.replace(e.data.notif, '"')
        }
    }


    handelNativePayu(e) {
        if (e.data.action == 'payu_callback') {
            const keyExists = (obj, key) => {
                if (!obj || (typeof obj !== "object" && !Array.isArray(obj))) {
                    return false;
                }
                else if (obj.hasOwnProperty(key)) {
                    return obj[key];
                }
                else if (Array.isArray(obj)) {
                    for (let i = 0; i < obj.length; i++) {
                        const result = keyExists(obj[i], key);
                        if (result) {
                            return result;
                        }
                    }
                }
                else {
                    for (const k in obj) {
                        const result = keyExists(obj[k], key);
                        if (result) {
                            return result;
                        }
                    }
                }
                return '';
            };
            if (_isUndefined(e.data.res)) {
                parsed['status'] = "pending"
                this.checkTransactionFlow();
            } else {
                let resData = JSON.parse(e.data.res)
                let param = {
                    "result": {
                        ...(keyExists(resData, "result") == "" ? resData : keyExists(resData, "result")),
                        "status": keyExists(resData, "status"),
                        "txnid": keyExists(resData, "txnid")
                    }
                }

                payuCallback(param).then((response) => {
                    const { data, response_code } = response;
                    if (response_code == WSC.successCode) {
                        const urlParams = queryString.extract(data.url)
                        const queryParams = queryString.parseUrl(data.url)
                        parsed['status'] = queryParams.query.status
                        window.history.replaceState("", "", '?' + urlParams);
                        this.checkTransactionFlow();
                    }
                    else if (response_code == WSC.BannedStateCode) {
                        Utilities.bannedStateToast(this.state.bn_state)
                    }
                    else {
                        this.setState({ isLoading: false })
                    }
                })
            }
        }
    }

    handleUpiIntent = (e) => {
        if (e.data.action == 'upi_intent') {
            if (e.data.res.status == "FAILED") {
                window.location.href = _.replace(e.data.res.data.order_meta.return_url, '{order_id}', e.data.res.data.order_id);
            } else {
                window.location.href = _.replace(e.data.res.data.order_meta.return_url, '{order_id}', e.data.res.order_id);
            }
        }
    }

    onPaymentMethodSelect(method, type) {
        
        this.setState({ paymentTypeSelected: '' })
        switch (method) {
            case 'payumoney':
                this.PayumoneyDeposit();
                break;
            case 'paytm':
                this.PayTmDeposit();
                break;
            case 'ipay':
                this.iPayDeposit();
                break;
            case 'paypal':
                this.PayPalDeposit();
                break;
            case 'paystack':
                this.PayStackDeposit();
                break;
            case 'razorpay':
                this.RazorPayDeposit();
                break;
            case 'siriuspay':
                this.siriusPayDeposit();
                break;
            case 'cashierpay':
                this.CashierPayDeposit();
                break;
            case 'cashfree':
                this.handelPaymentType(type)
                this.GoCashFreeDeposit(type);
                break;
            case 'stripe':
                this.StripeInit()
                break;
            case 'vpay':
                this.vPayDeposit();
                break;
            case 'ifantasy':
                this.vIFantasyDeposit();
                break;
            case 'crypto':
                this.DepositCrypto(type)
                break;
            case 'paylogic':
                this.payLogicDeposit(type)
                break;
            case 'mpesa':
                this.mpesaDeposit(type)
                break;
            case 'directpay':
                this.DirectpayDeposit()
                break;
            case 'phonepe':
                this.PhonepeDeposit()
                break;
            case 'juspay':
                this.JuspeDeposit()
                break;
            default:
                this.PayumoneyDeposit();
                break;
        }
        WSManager.setFromFundsOnly(true);
        localStorage.setItem('singular_dep_amt', this.state.amount);
        WSManager.setIsFromPayment(true);

    }

    DirectpayDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositDirectPay(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.props.history.push({ pathname: '/directpay', state: { ...responseJson.data, "purl": pUrl + `&amount=${this.state.amount}` } })
            }
            // setTimeout(() => {
            //     this.setState({ isLoading: false })
            // }, 500);
        })
    }
    PhonepeDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            ...(window.ReactNativeWebView ? {
                "is_mobile": 1,
                "device_type": WSManager.getIsIOSApp() ? "ios" : "android"
            } : {})
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPhonepeFund(param).then((responseJson) => {
            
            if (responseJson.response_code == WSC.successCode) {
                if (window.ReactNativeWebView) {
                    let PhonePeData = {
                        action: 'phonepe_request',
                        targetFunc: 'phonepe_request',
                        data: responseJson.data,
                        param: param,
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(PhonePeData));
                    
                } else if(responseJson.data.redirectUrl){
                    window.location.href = responseJson.data.redirectUrl;
                } else {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error;
                    Utilities.showToast(errorMsg, 3000);    
                }
            }else{
                var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error;
                Utilities.showToast(errorMsg, 3000);
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }


    handelNativePhonePe = (e) => {
        if (e.data.action == 'phonepe_response') {
            const keyExists = (obj, key) => {
                if (!obj || (typeof obj !== "object" && !Array.isArray(obj))) {
                    return false;
                }
                else if (obj.hasOwnProperty(key)) {
                    return obj[key];
                }
                else if (Array.isArray(obj)) {
                    for (let i = 0; i < obj.length; i++) {
                        const result = keyExists(obj[i], key);
                        if (result) {
                            return result;
                        }
                    }
                }
                else {
                    for (const k in obj) {
                        const result = keyExists(obj[k], key);
                        if (result) {
                            return result;
                        }
                    }
                }
                return '';
            };
            if (_isUndefined(e.data.res)) {
                parsed['status'] = "pending"
                this.checkTransactionFlow();
            } else {
                let resData = e.data.res
                let param =  {
                        ...(keyExists(resData, "result") == "" ? resData : keyExists(resData, "result")),
                        "status": keyExists(resData, "status"),
                        "orderid": keyExists(resData, "orderId")
                    }
                phonePeCallback(param).then((response) => {
                    const { data, response_code } = response;
                    
                    if (response_code == WSC.successCode) {
                        const urlParams = queryString.extract(data.url)
                        const queryParams = queryString.parseUrl(data.url)
                        parsed['status'] = queryParams.query.status
                        window.history.replaceState("", "", '?' + urlParams);
                        this.checkTransactionFlow();
                    } else {
                        const urlParams = queryString.extract(data.url)
                        const queryParams = queryString.parseUrl(data.url)
                        parsed['status'] = queryParams.query.status
                       
                        window.history.replaceState("", "", '?' + urlParams);
                        this.checkTransactionFlow();
                        this.setState({ isLoading: false })
                    }
                })
            } 
        }
    }

    JuspeDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber,
            ...(window.ReactNativeWebView ? {
                "is_mobile": 1,
                "device_type": WSManager.getIsIOSApp() ? "ios" : "android"
            } : {})
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositJuspeFund(param).then((responseJson) => {
            
            if (responseJson.response_code == WSC.successCode) {
                if (window.ReactNativeWebView) {
                    let JusPeData = {
                        action: 'juspay_request',
                        targetFunc: 'juspay_request',
                        data: responseJson.data,
                        param: param,
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(JusPeData));
                } else if(responseJson.data.redirectUrl){
                    window.location.href = responseJson.data.redirectUrl;
                } else {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error;
                    Utilities.showToast(errorMsg, 3000);    
                }
            }else{
                var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error;
                Utilities.showToast(errorMsg, 3000);
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    handelNativeJusPe = (e) => {
        if (e.data.action == 'juspay_response') {
                const keyExists = (obj, key) => {
                    if (!obj || (typeof obj !== "object" && !Array.isArray(obj))) {
                        return false;
                    }
                    else if (obj.hasOwnProperty(key)) {
                        return obj[key];
                    }
                    else if (Array.isArray(obj)) {
                        for (let i = 0; i < obj.length; i++) {
                            const result = keyExists(obj[i], key);
                            if (result) {
                                return result;
                            }
                        }
                    }
                    else {
                        for (const k in obj) {
                            const result = keyExists(obj[k], key);
                            if (result) {
                                return result;
                            }
                        }
                    }
                    return '';
                };
                if (_isUndefined(e.data.data)) {
                    parsed['status'] = "pending"
                    this.checkTransactionFlow();
                } else {
                    let resData = e.data.data
                    let param =  {
                            ...(keyExists(resData, "result") == "" ? resData : keyExists(resData, "result")),
                            "status": keyExists(resData, "status"),
                            "orderid": keyExists(resData, "orderId")
                        }
                      
                    
                    jusPayCallback(param).then((response) => {
                        const { data, response_code } = response;
                        if (response_code == WSC.successCode) {

                            const urlParams = queryString.extract(data.url)
                            const queryParams = queryString.parseUrl(data.url)
                            parsed['status'] = queryParams.query.status
                            window.history.replaceState("", "", '?' + urlParams);
                            this.checkTransactionFlow();
                        } else {
                            const urlParams = queryString.extract(data.url)
                            const queryParams = queryString.parseUrl(data.url)
                            parsed['status'] = queryParams.query.status
                           
                            window.history.replaceState("", "", '?' + urlParams);
                            this.checkTransactionFlow();
                            this.setState({ isLoading: false })
                        }
                    })
                }                   
        }
    }
     

    handelPaymentType = (type) => {
        this.setState({ paymentTypeSelected: type })

        if (type == PAYMENT_TYPE.WALLET) {
            this.setState({ selectedNetBanking: '', selectedNBoption: '', selectedUPIoption: '', selectedUPI: '', cardNumber: '', nameOnCard: '', expiryDate: '', cvvNumber: '' })
        }
        else if (type == PAYMENT_TYPE.NET_BANKING) {
            this.setState({ selectedWallet: '', selectedWalletOption: '', selectedUPIoption: '', selectedUPI: '', cardNumber: '', nameOnCard: '', expiryDate: '', cvvNumber: '' })

        }
        else if (type == PAYMENT_TYPE.UPI) {
            this.setState({ selectedWallet: '', selectedWalletOption: '', selectedNetBanking: '', selectedNBoption: '', cardNumber: '', nameOnCard: '', expiryDate: '', cvvNumber: '' })

        }
        else if (type == PAYMENT_TYPE.CREDIT_DEBIT_CARD) {
            this.setState({ selectedWallet: '', selectedWalletOption: '', selectedNetBanking: '', selectedNBoption: '', selectedUPIoption: '', selectedUPI: '' })

        }

    }

    onMethodSelected(method, type = '') {
        if (method == 'cashfree') {
            return (type == 'upi' && window.ReactNativeWebView) ? false : true;
        }
        else {
            return false;
        }
    }

    PayTmDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPaytmFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var paytmForm = document.forms.paytmForm;
                paytmForm.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    vIFantasyDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositIFantasyFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                window.location.href = responseJson.data.payment_link;
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    PayStackDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPayStackFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var paystackForm = document.forms.paystackform;
                paystackForm.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            this.setState({ posting: false, isLoading: false })
        })
    }
    CashierPayDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositCashierPay(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var cashierpayform = document.forms.cashierpayform;
                cashierpayform.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            this.setState({ posting: false, isLoading: false })
        })
    }

    PayPalDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPayPalFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                window.location.href = responseJson.data.payment_link;
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }


    iPayDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositIPAYFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var ipayform = document.forms.ipayform;
                ipayform.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }
    StripeInit = () => {
        const data = parseFloat(this.state.amount)   
        const GST = (data * Utilities.getMasterData().gst_rate / 100);
        const bothValueAdd = parseFloat(data) + parseFloat(GST)
        const finalAmount =bothValueAdd
        
       const paybleAmount = (Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new") ?  finalAmount : this.state.amount 
       let param = {
            "amount": paybleAmount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "source": null,
            "gst": this.state.GSTNumber
        }
        this.props.history.push({ pathname: '/stripe', state: param }) // For Stripe PG
    }

    siriusPayDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "channel": "WEB",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositSiriusPay(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var siriuspayform = document.forms.siriuspayform;
                siriuspayform.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }
    payLogicDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "channel": "WEB",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPayLogic(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                })
                var paylogicform = document.forms.paylogicform;
                paylogicform.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }
    mpesaDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "channel": "WEB",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        // ls.set('back_history', this.props.history.length)
        mpesaDeposit(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (responseJson.data.ResponseCode == 0) {
                    Utilities.showToast('Your payment is processing. You will now be redirected to the wallet again. To check the transaction status visit the Transaction History page.', 3000)
                }
                else {
                    Utilities.showToast('Your payment failed. You can retry the payment in some time.', 3000)
                }
                // this.setState({
                //     PageContent: responseJson.data
                // })
                // var paylogicform = document.forms.paylogicform;
                // paylogicform.submit();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })

                this.props.history.go(-2)
            }, 500);
        })
    }

    GoCashFreeDeposit = (type) => {
        WSManager.setFromFundsOnly(true);
        WSManager.setIsFromPayment(true);
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber,
            "paymentOption": 'cashfree'
        }
        if (type == PAYMENT_TYPE.UPI) {
            param['paymentOption'] = PAYMENT_TYPE.UPI
            if (window.ReactNativeWebView) {
                WSManager.setFromFundsOnly(true);
                WSManager.setIsFromPayment(true);
            }
        }
        else if (type == PAYMENT_TYPE.CREDIT_DEBIT_CARD) {
            param['paymentOption'] = 'cashfree'
        }
        else if (type == PAYMENT_TYPE.WALLET) {
            if (this.state.selectedWallet != '') {
                if (this.state.selectedWallet.payment_code == '4007' && process.env.REACT_APP_CASHFREE_WALLET_PAYTM_ENABLE == 0) {
                    param['paymentOption'] = 'cashfree'
                }
                else {
                    param['paymentCode'] = this.state.selectedWallet.payment_code;
                    param['paymentOption'] = 'apps'
                }
            }
            else {
                Utilities.showToast("Please select at least one paymenet option", 1500);
                return;
            }
        }
        else if (type == PAYMENT_TYPE.NET_BANKING) {
            if (this.state.selectedNetBanking != '') {
                param['paymentCode'] = this.state.selectedNetBanking.payment_code;
                param['paymentOption'] = 'nb'
            }
            else {
                Utilities.showToast("Please select at least one paymenet option", 1500);
                return;
            }
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositCashFreeFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {

                if (window.ReactNativeWebView) {
                    let CashfreeData = {
                        action: 'upi_intent',
                        targetFunc: 'upi_intent',
                        data: { ...responseJson.data, currency_code: Utilities.getMasterData().currency_code },
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(CashfreeData));
                } else {
                    this.props.history.push({ pathname: '/cashfree', state: responseJson.data })
                }
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            if(!param.upi_intent) {
                setTimeout(() => {
                    this.setState({ isLoading: false })
                }, 500);
            }
        })
    }





    cardValidation = () => {
        if (this.state.cardNumber != '' && this.state.nameOnCard != '' && this.state.expiryDate != '' && this.state.cvvNumber != '') {
            return true;
        }
        else {
            return false;

        }
    }

    PayumoneyDeposit = () => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "is_mobile": window.ReactNativeWebView ? process.env.REACT_APP_PAYU_BIZ_ACCOUNT == 1 ? '0' : '1' : '0',
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositPayUmoneyFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (window.ReactNativeWebView && process.env.REACT_APP_PAYU_BIZ_ACCOUNT != 1) {
                    let payuData = {
                        action: 'payu',
                        targetFunc: 'payu',
                        data: responseJson.data,
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(payuData));
                }
                else {
                    this.setState({
                        PageContent: responseJson.data
                    })
                    var payuForm = document.forms.payuForm;
                    payuForm.submit();
                }

            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    vPayDeposit = () => {
        const getcp_vpay = window.ReactNativeWebView ? `&platform_app=${Utilities.getDeviceType()==1?'android':'ios'}` : ((Utilities.getCpSessionPath() != 'null') ? Utilities.getCpSessionPath().replace('?', '&') : '');
        var fUrl_vpay = window.location.protocol + '//' + hostName + "/payment-method?status=failure" + getcp_vpay
        var sUrl_vpay = window.location.protocol + '//' + hostName + "/payment-method?status=success" + getcp_vpay
        var pUrl_vpay = window.location.protocol + '//' + hostName + "/payment-method?status=pending" + getcp_vpay
        let param = {
            "amount": this.state.amount,
            "furl": fUrl_vpay + `&amount=${this.state.amount}`,
            "surl": sUrl_vpay + `&amount=${this.state.amount}`,
            "purl": pUrl_vpay + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "is_mobile": '0',
            "gst": this.state.GSTNumber

        }

        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositVpayFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    PageContent: responseJson.data
                },()=>{
                    if (window.ReactNativeWebView) {
                        function jsonToQueryString(json) {
                            return Object.keys(json)
                                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(json[key]))
                                .join('&');
                        }
                        const form = document.getElementById('payForm');
                        const formInputs = form.querySelectorAll('input');
                        const formData = {};

                        formInputs.forEach(input => {
                            const name = input.getAttribute('name');
                            const value = input.value;
                            formData[name] = value;
                        });
                        const queryString = jsonToQueryString(formData);
                        const urlWithQuery = `https://cricketme.in/vpay.html?${queryString}`;
                        let CashfreeData = {
                            action: 'vpay_intent',
                            targetFunc: 'vpay_intent',
                            data: urlWithQuery,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(CashfreeData));
                    } else {
                        var payForm = document.forms.payForm;
                        payForm.submit();
                    }
                })
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }
    DepositCrypto = (cType) => {
        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "currency_type": cType,
            "gst": this.state.GSTNumber

        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositCrypto(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    cryptoData: responseJson.data,
                }, () => {
                    this.showQrCodeModal()
                })
             }
             if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    RazorPayDeposit = () => {
        const script = document.createElement("script");
        script.src = "https://checkout.razorpay.com/v1/checkout.js";
        script.async = true;
        document.body.appendChild(script);

        let param = {
            "amount": this.state.amount,
            "furl": fUrl + `&amount=${this.state.amount}`,
            "surl": sUrl + `&amount=${this.state.amount}`,
            "purl": pUrl + `&amount=${this.state.amount}`,
            "promo_code": this.state.mPromoCode,
            "deal_id": this.state.selectedDeal ? this.state.selectedDeal.deal_id : "",
            "gst": this.state.GSTNumber
        }
        this.setState({ isLoading: true })
        ls.set('back_history', this.props.history.length)
        depositRazorPayFund(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                var pg_data = responseJson.data;
                var options = {
                    "key": pg_data.key,
                    "amount": pg_data.amount,
                    "name": pg_data.site_name,
                    "description": pg_data.amount,
                    "image": pg_data.image,
                    "order_id": pg_data.order_id,
                    "shopping_order_id": pg_data.merchant_order_id,
                    "callback_url": pg_data.action,
                    "redirect": true,
                    "handler": function (response) {
                    },
                    "prefill": {
                        "name": pg_data.prefill.name,
                        "email": pg_data.prefill.email,
                        "contact": pg_data.prefill.contact
                    },
                    "theme": {
                        "color": "#15b8f3" // screen color
                    }
                };
                var propay = new window.Razorpay(options);
                propay.open();
            }
            if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            setTimeout(() => {
                this.setState({ isLoading: false })
            }, 500);
        })
    }

    callOnlyAfterTransactions(contestData, FromConfirmPopupAddFunds, calledFrom, contestCoinData) {
        setTimeout(() => {
            this.setState({
                isDFSTour: contestData.isDFSTour || false
            })
            if (parsed.status == "success") {

                if (FromConfirmPopupAddFunds == 'true') {
                    if (calledFrom == "SelectCaptainList" || calledFrom == "ContestListing" || calledFrom == "mycontest") {
                        if (SELECTED_GAMET == GameType.Tournament) {
                            this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
                        }
                        else {
                            this.CallJoinGameApi(contestData)
                        }
                    }
                    else if (calledFrom == "ContestJoinBuyCoins" || calledFrom == 'BuyCoins') {
                        this.callBuyCoinsApi(contestData, contestCoinData)
                    }
                    else {
                        this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
                    }
                }
                else if (calledFrom == 'BuyCoins') {
                    this.goToBuyCoinsScreen(contestData)
                } 
                else if (SELECTED_GAMET == GameType.OpinionTradeFantasy) {
                    if(ls.get('fromOpinion')){
                        window.location.assign(ls.get('fromOpinion').url)
                    }else{
                        this.props.history.push("/opinion-trade/lobby/7")
                    }
                }
                else {
                    let mHist = parseInt(ls.get('back_history') || 0);
                    let newHistory = this.props.history.length;
                    let updatedHistory = (newHistory - mHist) + 2;
                    this.props.history.push('/my-wallet')
                    // this.props.history.go(-updatedHistory)
                }

            }
            else if (parsed.status == "failure" || parsed.status == "pending") {
                if (SELECTED_GAMET == GameType.OpinionTradeFantasy) {
                    if(ls.get('fromOpinion')){
                        window.location.assign(ls.get('fromOpinion').url)
                    }else{
                        this.props.history.push("/opinion-trade/lobby/7")
                    }
                }else if (SELECTED_GAMET == GameType.PickemTournament) {
                    this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
                }
                else {
                    if (FromConfirmPopupAddFunds == 'true') {
                        if (calledFrom == "SelectCaptainList") {
                            if (SELECTED_GAMET == GameType.Tournament) {
                                this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
                            }
                            else {
                                WSManager.setFromConfirmPopupAddFunds(false)
                                this.props.history.replace({ pathname: '/' })
                            }
                        } else if (calledFrom == "mycontest") {
                            this.seeMyContest()
                        } else {
                            this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
                        }

                    } else {
                        let mHist = parseInt(ls.get('back_history') || 0);
                        let newHistory = this.props.history.length;
                        let updatedHistory = (newHistory - mHist) + 2;
                        this.props.history.go(-updatedHistory)
                    }
                }
            }
        }, 1000)
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let isH2h = dataFromConfirmPopUp.FixturedContestItem.contest_template_id ? true : false;
        let IsNetworkContest = dataFromConfirmPopUp.FixturedContestItem.is_network_contest && dataFromConfirmPopUp.FixturedContestItem.is_network_contest == 1;
        let ApiAction = '';
        let contestUid = '';
        let contestAccessType = '';
        let isPrivate = '';
        let isLF = SELECTED_GAMET == GameType.LiveFantasy ? true : false
        let StockLF = SELECTED_GAMET == GameType.LiveStockFantasy ? true : false
        if (dataFromConfirmPopUp.isDFSTour) {
            ApiAction = joinDFSTour;
            var param = {
                "tournament_season_id": dataFromConfirmPopUp.lobbyDataItem.tournament_season_id,
                "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
                "tournament_team_id": dataFromConfirmPopUp.selectedTeam.tournament_team_id ? dataFromConfirmPopUp.selectedTeam.tournament_team_id : dataFromConfirmPopUp.selectedTeam.value.tournament_team_id
            }
            contestUid = dataFromConfirmPopUp.FixturedContestItem.tournament_id
            contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
            isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        }
        else if (SELECTED_GAMET == GameType.PickemTournament) {
            ApiAction = getPTJoinTour;
            var param = {
                "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
            }
        }
        else if (SELECTED_GAMET == GameType.PickFantasy) {
            ApiAction = GetPFJoinGame;
            var param = {
                "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
                'user_team_id': dataFromConfirmPopUp.selectedTeam.value.user_team_id
            }
            contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
            contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
            isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;
        }
        
        else if(SELECTED_GAMET == GameType.StockFantasy){
            ApiAction = stockJoinContest
            var param = {
                "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : dataFromConfirmPopUp.FixturedContestItem.contest_id,
                "promo_code": dataFromConfirmPopUp.promoCode,
                "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType,
                "lineup_master_id": dataFromConfirmPopUp && dataFromConfirmPopUp.selectedTeam && dataFromConfirmPopUp.selectedTeam.value && dataFromConfirmPopUp.selectedTeam.value.lineup_master_id ?  dataFromConfirmPopUp.selectedTeam.value.lineup_master_id : dataFromConfirmPopUp.selectedTeam.lineup_master_id
            }
        }
        else if(SELECTED_GAMET == GameType.PropsFantasy){
            ApiAction = propsSaveTeam;
            param= ls.get('in_params') ? ls.get('in_params') : this.props.location.params
        }
        else {
            if (isLF) {
                WSManager.setH2hMessage(false);
            }
            ApiAction = isLF ? joinContestLF : IsNetworkContest ? joinContestNetworkfantasy : isH2h ? joinContestH2H : joinContest;
            var param = {
                "contest_id": isH2h ? dataFromConfirmPopUp.FixturedContestItem.contest_template_id : dataFromConfirmPopUp.FixturedContestItem.contest_id,
                "promo_code": dataFromConfirmPopUp.promoCode,
                "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
            }

            contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
            contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
            isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

            if (!isLF && !StockLF) {
                this.setState({ isLoaderShow: true })
                if (dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1) {
                    ApiAction = IsNetworkContest ? joinContestWithMultiTeamNF : joinContestWithMultiTeam;
                    let resultLineup = dataFromConfirmPopUp.lineUpMasterIdArray.map(a => a.lineup_master_id);
                    param['lineup_master_id'] = resultLineup
                } else {
                    param['lineup_master_id'] = dataFromConfirmPopUp.selectedTeam.lineup_master_id ? dataFromConfirmPopUp.selectedTeam.lineup_master_id : dataFromConfirmPopUp.selectedTeam.value.lineup_master_id
                }
            }
        }
        if (this.state.isStockF || dataFromConfirmPopUp.isStockPF || SELECTED_GAMET == GameType.StockFantasyEquity) {
            ApiAction = dataFromConfirmPopUp.lineUpMasterIdArray && dataFromConfirmPopUp.lineUpMasterIdArray.length > 1 ? joinStockContestWithMultiTeam : stockJoinContest
        }
        if (dataFromConfirmPopUp.isStockLF) {
            ApiAction = LSFJoinContest
        }
        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (isH2h) {
                    Utilities.setH2hData(dataFromConfirmPopUp, responseJson.data.contest_id)
                }
                if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
                    let singular_data = {};
                    if (dataFromConfirmPopUp.isDFSTour) {
                        singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                        singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.tournament_id;
                        singular_data.contest_date = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                        singular_data.fixture_name = dataFromConfirmPopUp.FixturedContestItem.tournament_name;
                        singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFee;
                        singular_data.tournament_name = dataFromConfirmPopUp.FixturedContestItem.tournament_name;
                    }
                    else {
                        let SSd = dataFromConfirmPopUp.lobbyDataItem && dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date ? dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date : dataFromConfirmPopUp.FixturedContestItem.season_scheduled_date
                        let CollName = dataFromConfirmPopUp.lobbyDataItem && dataFromConfirmPopUp.lobbyDataItem.collection_name ? dataFromConfirmPopUp.lobbyDataItem.collection_name : dataFromConfirmPopUp.FixturedContestItem.collection_name
                        singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                        singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                        singular_data.contest_date = SSd;
                        singular_data.fixture_name = CollName;
                        singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;
                        singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.tournament_season_id;
                    }

                    if (window.ReactNativeWebView) {
                        let event_data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: dataFromConfirmPopUp.isDFSTour ? 'tournament_contest_joined' : 'Contest_joined',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(event_data));
                    }
                    else {
                        window.SingularEvent(dataFromConfirmPopUp.isDFSTour ? 'tournament_contest_joined' : "Contest_joined", singular_data);
                    }
                    let leagueName = dataFromConfirmPopUp.lobbyDataItem && dataFromConfirmPopUp.lobbyDataItem.league_name ? dataFromConfirmPopUp.lobbyDataItem.league_name :
                        dataFromConfirmPopUp.FixturedContestItem && dataFromConfirmPopUp.FixturedContestItem.league_name ? dataFromConfirmPopUp.FixturedContestItem.league_name : '';
                    let EntryFee = dataFromConfirmPopUp.lobbyDataItem && dataFromConfirmPopUp.lobbyDataItem.entry_fee ? dataFromConfirmPopUp.lobbyDataItem.entry_fee :
                        dataFromConfirmPopUp.FixturedContestItem && dataFromConfirmPopUp.FixturedContestItem.entry_fee ? dataFromConfirmPopUp.FixturedContestItem.entry_fee : '';
                    Utilities.gtmEventFire('join_contest', {
                        fixture_name: singular_data.fixture_name,
                        contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                        league_name: leagueName,
                        entry_fee: EntryFee,
                        fixture_scheduled_date: Utilities.getFormatedDateTime(singular_data.contest_date, 'YYYY-MM-DD HH:mm:ss'),
                        contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                    })
                }
                setTimeout(() => {
                    WSManager.googleTrack(WSC.GA_PROFILE_ID, dataFromConfirmPopUp.isDFSTour ? 'tournamentcontestjoined' : 'contestjoin');
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'contestjoindaily');
                    this.ThankYouModalShow()
                }, 300);

                // if (contestAccessType == '1' || isPrivate == '1') {
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                WSManager.setFromConfirmPopupAddFunds(false);
            } 
            else if (responseJson.response_code == WSC.BannedStateCode) {
                Utilities.bannedStateToast(this.state.bn_state)
            }
            else {
                var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                if (errorMsg == '') {
                    for (var key in responseJson.error) {
                        errorMsg = responseJson.error[key];
                    }
                }
                if (responseJson.response_code == WSC.sessionExpireCode) {
                    this.logout();
                }
                Utilities.showToast(errorMsg, 3000);

                setTimeout(() => {
                    if (dataFromConfirmPopUp.isDFSTour || dataFromConfirmPopUp.isStockPF || SELECTED_GAMET == GameType.StockFantasyEquity || SELECTED_GAMET == GameType.LiveStockFantasy) {
                        this.props.history.push({ pathname: '/' });
                    }
                    else if (responseJson.data.self_exclusion_limit == 1) {
                        this.setState({
                            showUJC: true,
                        });
                    }
                    else {
                        let mHist = parseInt(ls.get('back_history') || 0);
                        let newHistory = this.props.history.length;
                        let updatedHistory = (newHistory - mHist) + 2;
                        this.props.history.go(-updatedHistory)
                    }
                }, 500);
            }

        })
    }

    gotoContestListingClass(data, lobbyItem) {
        if (SELECTED_GAMET == GameType.Tournament) {
            setTimeout(() => {
                let mHist = parseInt(ls.get('back_history') || 0);
                let newHistory = this.props.history.length;
                let updatedHistory = (newHistory - mHist) + 2;
                this.props.history.go(-updatedHistory)
            }, 500);
        }
        else if ((SELECTED_GAMET == GameType.DFS && this.state.isDFSTour) || (SELECTED_GAMET == GameType.StockPredict) || (SELECTED_GAMET == GameType.PickFantasy) || (SELECTED_GAMET == GameType.LiveStockFantasy) || SELECTED_GAMET == GameType.PickemTournament) {
            this.props.history.push({ pathname: '/' });
        }
        else {
            if((SELECTED_GAMET == GameType.StockFantasyEquity) || (SELECTED_GAMET == GameType.StockFantasy)){
                data['collection_master_id'] = data.collection_id;
                let name = data.category_id.toString() === "1" ? 'Daily' : data.category_id.toString() === "2" ? 'Weekly' : 'Monthly';

                let mpath = SELECTED_GAMET == GameType.StockFantasy ? "/stock-fantasy/contest/" : '/stock-fantasy-equity/contest/';
                let contestListingPath =  mpath + data.collection_id + "/" + name;
                let CLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET)
                
                this.props.history.push({
                  pathname: CLPath,
                  state: { LobyyData: data, lineupPath: CLPath },
                });
            }
            else{
                let dateformaturl = Utilities.getUtcToLocal(data.season_scheduled_date);
                dateformaturl = new Date(dateformaturl);
    
                let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
                let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
    
                let home = data.home || lobbyItem.home;
                let away = data.away || lobbyItem.away;
                let leageName = data.league_name || lobbyItem.league_name
    
                dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
                // let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl + "?sgmty=" +  btoa(SELECTED_GAMET)
                let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + home + "-vs-" + away + "-" + dateformaturl;
                let cLPath = contestListingPath.toLowerCase() + "?sgmty=" + btoa(SELECTED_GAMET);
                this.setState({ LobyyData: data })
                this.props.history.push({ pathname: cLPath, state: { FixturedContest: this.state.FixtureData, LobyyData: lobbyItem, isFromPM: true } })
            }
        }
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }


    goToLobby = () => {

        if (SELECTED_GAMET == GameType.LiveFantasy || SELECTED_GAMET == GameType.PickFantasy || SELECTED_GAMET == GameType.PickemTournament || SELECTED_GAMET == GameType.PropsFantasy) {
            this.props.history.push({ pathname: '/lobby' })
            return;
        }
        let contestData = WSManager.getContestFromAddFundsAndJoin()
        let calledFrom = WSManager.getPaymentCalledFrom();
        setTimeout(() => {
            // if (calledFrom == 'mycontest') {
            //     this.props.history.push({ pathname: '/' });
            // } else {
            this.gotoContestListingClass(contestData.FixturedContestItem, contestData.lobbyDataItem)
            // }
        }, 500);
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }

    hideStateTagModal = () => {
        this.setState({
            showStateTagModal: false,
        });
    }
    checkIfCashfreeExist = () => {
        var obj = Utilities.getMasterData().pg;
        if (Object.values(obj).indexOf('cashfree') > -1) {
            this.setState({ checkIfCashfreeExist: true })
            this.getCashfreeDetails();
        }
    }

    getCashfreeDetails() {
        let param = {}
        this.setState({ isLoaderShow: true })
        getCashFreeGatewayList(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    walletList: responseJson.data.wallet_list,
                    netBankingList: responseJson.data.bank_list
                })
                const wallet_list = [];
                const nb_list = [];
                const upi_list = [];


                responseJson.data.wallet_list && responseJson.data.wallet_list.map((data, key) => {
                    wallet_list.push({ value: data.payment_code, label: data.payment_option, type_code: data.type_code })
                    return '';
                })
                responseJson.data.bank_list && responseJson.data.bank_list.map((data, key) => {
                    nb_list.push({ value: data.payment_code, label: data.payment_option, type_code: data.type_code })
                    return '';
                })
                this.state.upiList && this.state.upiList.map((data, key) => {
                    upi_list.push({ value: data.upiMode, label: data.upiMode })
                    return '';
                })
                this.setState({
                    walletDropDownList: wallet_list,
                    netBankingDropDownList: nb_list,
                    upiDropDownList: upi_list
                })
            } else {
                var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error
                if (responseJson.response_code == WSC.BannedStateCode) {
                    Utilities.bannedStateToast(this.state.bn_state)
                }
                if (errorMsg == '') {
                    for (var key in responseJson.error) {
                        errorMsg = responseJson.error[key];
                    }
                }
                if (responseJson.response_code == WSC.sessionExpireCode) {
                    this.logout();
                }
                Utilities.showToast(errorMsg, 3000);

            }
        })
    }
    chashfreeBanking = (item, e, type) => {
        e.stopPropagation()
        if (type == PAYMENT_TYPE.WALLET) {
            this.setState({ selectedWallet: item, selectedWalletOption: '' })
        }
        else if (type == PAYMENT_TYPE.NET_BANKING) {
            this.setState({ selectedNetBanking: item, selectedNBoption: '' })

        }
        this.handelPaymentType(type)
    }
    handleWalletChange = (selectedOption) => {
        this.setState({ selectedWalletOption: selectedOption }, () => {
            let selectWalletData = {}
            this.state.walletList && this.state.walletList.map((item, index) => {
                if (selectedOption.value == item.payment_code) {
                    selectWalletData = item;
                }
            })
            this.setState({ selectedWallet: selectWalletData })
        });
        this.handelPaymentType(PAYMENT_TYPE.WALLET)

    }
    handleNetBankingChange = (selectedOption) => {
        this.setState({ selectedNBoption: selectedOption }, () => {
            let selectNbData = {}
            this.state.netBankingList && this.state.netBankingList.map((item, index) => {
                if (selectedOption.value == item.payment_code) {
                    selectNbData = item;
                }
            })
            this.setState({ selectedNetBanking: selectNbData })
        });
        this.handelPaymentType(PAYMENT_TYPE.NET_BANKING)

    }
    handleUpiChange = (selectedOption) => {
        this.setState({ selectedUPIoption: selectedOption }, () => {
            let selectUPIData = {}
            this.state.upiList && this.state.upiList.map((item, index) => {
                if (selectedOption.value == item.upiMode) {
                    selectUPIData = item;
                }
            })
            this.setState({ selectedUPI: selectUPIData })
        });
        this.handelPaymentType(PAYMENT_TYPE.UPI)
    }

    handleChangeCardNumber = (e) => {
        this.setState({ cardNumber: e.target.value })

    }
    handleChangeName = (e) => {
        this.setState({ nameOnCard: e.target.value })
    }
    handleChangeExpiryDate = (e) => {
        this.setState({ expiryDate: e.target.value })
    }
    handleChangeCvv = (e) => {
        this.setState({ cvvNumber: e.target.value })
    }

    renderWalletView = () => {
        return (
            <div>
                <div onClick={() => this.onPaymentMethodSelect(Utilities.getMasterData().pg.wallet, PAYMENT_TYPE.WALLET)} className="item-view">
                    <div className={"btn-expand-action"}>
                        {/* <div className={"view-items"+ (this.state.paymentTypeSelected == PAYMENT_TYPE.WALLET ? ' active-border' : ' ')}>
                            <i className={"icon-plus" + (this.state.paymentTypeSelected == PAYMENT_TYPE.WALLET ? ' active' : ' not-active')}></i>
                        </div> */}
                        <div className={"title-payment" + (this.state.selectedWallet ? ' selected' : '')}>{AppLabels.PAYTM_WALLET}</div>

                    </div>
                    {/* <img src={Images.GROUP_WALLET} alt="" /> */}

                </div>
                {
                    (this.state.walletList && this.state.walletList.length > 0) &&
                    <div style={{ marginTop: 15 }}>
                        <div style={{ display: 'flex', flexDirection: 'row', justifyContent: 'space-evenly' }}>

                            {
                                this.state.walletList.map((item, index) => {
                                    return (
                                        item.payment_code == "4007" || item.payment_code == "4009" || item.payment_code == "4001" ?
                                            <div onClick={(e) =>
                                                this.chashfreeBanking(item, e, PAYMENT_TYPE.WALLET)
                                            }
                                                className={"wallet-box" + (this.state.selectedWallet.payment_code == item.payment_code ? ' selected' : '')}>
                                                {/* <div className="title-wallet">{item.payment_option}</div> */}
                                                {
                                                    item.payment_code == "4007" ?
                                                        <img src={Images.PAYTM_IMG} alt="" />
                                                        : item.payment_code == "4009" ?
                                                            <img style={{ width: 65 }} src={Images.PHONE_PAY} alt="" />
                                                            : item.payment_code == "4001" ?
                                                                <img src={Images.FREECHARGE} alt="" />
                                                                : ''


                                                }
                                            </div>
                                            : ''
                                    )
                                })
                            }
                        </div>

                        <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                            <Row style={{ marginTop: 20 }}>
                                <Col style={{ zIndex: 100 }} xs={12}>
                                    <FormGroup className="input-label-center zIndex1000 input-transparent"
                                        controlId="formBasicText">
                                        {this.state.isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                            className='select-field-transparent'
                                            classNamePrefix='select'
                                            id="select-country"
                                            arrowRenderer={this.arrowRenderer}
                                            onChange={this.handleWalletChange}
                                            options={this.state.walletDropDownList}
                                            value={this.state.selectedWalletOption}
                                            placeholder={'Select Other Wallet'}

                                            theme={(theme) => ({
                                                ...theme,
                                                borderRadius: 0,
                                                colors: {
                                                    ...theme.colors,
                                                    primary: '#013D79',
                                                },
                                            })}
                                        >
                                        </ReactSelectDD></Suspense>}
                                    </FormGroup>
                                </Col>
                            </Row>
                        </div>
                        {/* <div onClick={() => this.GoCashDeposit(PAYMENT_TYPE.WALLET)} className={"paynow-btn" +(this.state.selectedWallet != '' ? ' active-pay-now': ' disable-pay-now' )  }>
                            <div className="title-pay-now">{"PAY NOW!"}</div>
                        </div> */}

                    </div>


                }

            </div>
        )
    }

    renderNBView = () => {
        return (
            <div>
                <div onClick={this.state.selectedNetBanking ? () => this.onPaymentMethodSelect(Utilities.getMasterData().pg.net_banking, PAYMENT_TYPE.NET_BANKING) : null} className="item-view">
                    <div className={"btn-expand-action"}>
                        {/* <div className={"view-items"+ (this.state.paymentTypeSelected == PAYMENT_TYPE.NET_BANKING ? ' active-border' : ' ')}>
                            <i className={"icon-plus" + (this.state.paymentTypeSelected == PAYMENT_TYPE.NET_BANKING ? ' active' : ' not-active')}></i>
                        </div> */}
                        <div className={"title-payment" + (this.state.selectedNetBanking ? ' selected' : '')}>{AppLabels.NET_BANKING}</div>

                    </div>
                    {/* <img src={Images.NET_BANKING} alt="" /> */}

                </div>
                {
                    (this.state.netBankingList && this.state.netBankingList.length > 0) &&
                    <div style={{ marginTop: 15 }}>
                        <div style={{ display: 'flex', flexDirection: 'row', justifyContent: 'space-between', marginLeft: 5 }}>
                            {
                                this.state.netBankingList.slice(0, 4).map((item, index) => {
                                    return (
                                        <div className="nb-view">
                                            <div onClick={(e) =>
                                                this.chashfreeBanking(item, e, PAYMENT_TYPE.NET_BANKING)
                                            }
                                                className={"net-banking" + (this.state.selectedNetBanking.payment_code == item.payment_code ? ' selected' : '')}>
                                                {
                                                    item.payment_code == "3003" ?
                                                        <img className="bank-logo" src={Images.AXIS} alt="" />
                                                        : item.payment_code == "3032" ?
                                                            <img className="bank-logo" src={Images.KOTAK} alt="" />
                                                            : item.payment_code == "3021" ?
                                                                <img className="bank-logo" src={Images.HDFC} alt="" />
                                                                : item.payment_code == "3044" ?
                                                                    <img className="bank-logo" src={Images.SBI} alt="" />
                                                                    : item.payment_code == "3022" ?
                                                                        <img className="bank-logo" src={Images.ICIC} alt="" /> : ''


                                                }
                                            </div>
                                            {
                                                <div className="title-nb">
                                                    {item.payment_code == "3003" ? "AXIS" : item.payment_code == "3032" ? "KOTAK" : item.payment_code == "3021" ? "HDFC" : item.payment_code == "3044" ? "SBI" : item.payment_code == "3022" ? "ICICI" : item.payment_option}

                                                </div>

                                            }
                                        </div>
                                    )
                                })
                            }
                        </div>
                        <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                            <Row style={{ marginTop: 20 }}>
                                <Col style={{ zIndex: 99 }} xs={12}>
                                    <FormGroup className="input-label-center zIndex1000 input-transparent"
                                        controlId="formBasicText">
                                        {this.state.isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                            className='select-field-transparent'
                                            classNamePrefix='select'
                                            id="select-country"
                                            arrowRenderer={this.arrowRenderer}
                                            onChange={this.handleNetBankingChange}
                                            options={this.state.netBankingDropDownList}
                                            value={this.state.selectedNBoption}
                                            placeholder={'Select From Other Bank'}

                                            theme={(theme) => ({
                                                ...theme,
                                                borderRadius: 0,
                                                colors: {
                                                    ...theme.colors,
                                                    primary: '#013D79',
                                                },
                                            })}
                                        >
                                        </ReactSelectDD></Suspense>}
                                    </FormGroup>
                                </Col>
                            </Row>
                        </div>
                        {/* <div onClick={() => this.GoCashDeposit(PAYMENT_TYPE.NET_BANKING)} className={"paynow-btn" +(this.state.selectedNetBanking != '' ? ' active-pay-now': ' disable-pay-now' )  }>
                            <div className="title-pay-now">{"PAY NOW!"}</div>

                        </div> */}

                    </div>


                }

            </div>
        )
    }

    renderUpiMode = () => {
        return (
            <div>
                <div onClick={this.state.selectedUPI ? () => this.onPaymentMethodSelect(Utilities.getMasterData().pg.upi, PAYMENT_TYPE.UPI) : null} className="item-view">
                    <div className={"btn-expand-action"}>
                        {/* <div className={"view-items"+ (this.state.paymentTypeSelected == PAYMENT_TYPE.UPI ? ' active-border' : ' ')}>
                            <i className={"icon-plus" + (this.state.paymentTypeSelected == PAYMENT_TYPE.UPI ? ' active' : ' not-active')}></i>
                        </div> */}
                        <div className={"title-payment" + (this.state.selectedUPI ? ' selected' : '')}>{AppLabels.UPI}</div>

                    </div>
                    {/* <img src={Images.UPI} alt="" /> */}

                </div>

                {

                    <>
                        <div className="verification-block mt-0 p-0 left-align no-margin-l no-margin-r">
                            <Row style={{ marginTop: 20 }}>
                                <Col style={{ zIndex: 99 }} xs={12}>
                                    <FormGroup className="input-label-center zIndex1000 input-transparent"
                                        controlId="formBasicText">
                                        {this.state.isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                            className='select-field-transparent'
                                            classNamePrefix='select'
                                            id="select-country"
                                            arrowRenderer={this.arrowRenderer}
                                            onChange={this.handleUpiChange}
                                            options={this.state.upiDropDownList}
                                            value={this.state.selectedUPIoption}
                                            placeholder={AppLabels.SELECT_UPI}

                                            theme={(theme) => ({
                                                ...theme,
                                                borderRadius: 0,
                                                colors: {
                                                    ...theme.colors,
                                                    primary: '#013D79',
                                                },
                                            })}
                                        >
                                        </ReactSelectDD></Suspense>}
                                    </FormGroup>
                                </Col>
                            </Row>
                        </div>
                    </>
                }
            </div>
        )
    }
    showUJC = () => {
        this.setState({
            showUJC: true,
        });
    }

    hideUJC = () => {
        this.setState({
            showUJC: false,
        }, () => {
            this.props.history.push({ pathname: '/' });
        });
    }

    renderCryptoPG = (keyName) => {
        let key = keyName == 'BNB.BSC' ? 'BNB_BSC' : keyName
        return (
            <div className='image-container'>
                <img className='img-left' src={Images[key]}></img>
                <div className='crypto-text'>{Utilities.getMasterData().crypto_cur[keyName]}</div>
            </div>

        )
    }
    cryptoPG = () => {
        return (
            <div className='payment-section-wrap'>
                {Object.keys(Utilities.getMasterData().crypto_cur).map((key) => (
                    <div onClick={() => this.onPaymentMethodSelect("crypto", `${key}`)} className="payment-selection">
                        {
                            <div >
                                {this.renderCryptoPG(`${key}`)}

                            </div>

                        }
                    </div>
                ))}

            </div>
        )
    }
    paymentMPG = (x) => {
        if (x == 0) {
            this.setState({ type: '0', show: true, type_id: '3' })
        }
        else if (x == 1) {
            this.setState({ type: '1', show: true, type_id: '2' })
        }
        else if (x == 2) {
            this.setState({ type: '2', show: true, type_id: '1' })
        }
        else {
            alert("00 enter")
        }
    }
    handleClose = () => {
        this.setState({ show: false });
    }
    render() {
        const HeaderOption = {
            back: true,
            title: AppLabels.SELECT_PAYMENT_METHOD,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        const { amount, PageContent, showThankYouModal, showStateTagModal, isCMounted, showUJC ,amountActual  } = this.state;
        return (
            <>
                {/* {Utilities.getMasterData().a_offpg == '1' ? */}
                    <MyContext.Consumer>
                        {(context) => (
                            <div className="web-container web-container-fixed trans-web-container pay-method-wrap">
                                <div className='hide'>{isCMounted && <Suspense fallback={<div />}><ReactHTMLParser content={PageContent} /></Suspense>}</div>
                                {
                                    this.state.isLoading && <CustomLoader />
                                }
                                <Helmet titleTemplate={`${MetaData.template} | %s`}>
                                    <title>{MetaData.transactions.title}</title>
                                    <meta name="description" content={MetaData.transactions.description} />
                                    <meta name="keywords" content={MetaData.transactions.keywords}></meta>
                                </Helmet>
                                <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                                <Row>
                                    <Col sm={12}>
                                        <div className="payable-amt">
                                            {AppLabels.TO_PAY}
                                            <div className='payable-amt-view'>
                                                <i className="font-style-normal">{Utilities.getMasterData().currency_code}</i>
                                                {/* <span>{amount}</span> */}
                                                <span>{(Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new") ?  amountActual : amount }</span>
                                            </div>

                                        </div>
                                    </Col>
                                </Row>

                                <div className="how-would-you-view">{CommonLabels.HOW_WOULD_YOU_LIKE_TO_PAY}</div>
                            
                            <div className="payment-method-container">
                                <div className="payment-card-row">
                                    {Utilities.getMasterData().pg_list && Utilities.getMasterData().pg_list.length > 0 &&
                                        Utilities.getMasterData().pg_list.map((item, idx) => {
                                            const replacedKey = item.pg_key.replace(/allow_/gi,'' );
                                            return (
                                                <div className="payment-card" key={idx} onClick={()=> this.onPaymentMethodSelect(replacedKey)}>
                                                    <div className='c-image'>
                                                        <img alt='' className='img-fluid' src={Utilities.getPaymentImg(item.image_name)} /> 
                                                    </div>
                                                    <div className='payment-content'>
                                                        <div className='payments-name'>{item.title}</div>
                                                        <div className='payements-desc'>{item.description}</div>
                                                    </div>
                                                </div>
                                            )
                                        })
                                    }


                                </div>
                            </div>
                                    
                                    

                                {/* <Row>  
                                    <Col sm={12}>
                                        {
                                            Utilities.getMasterData().a_crypto == 1 ? this.cryptoPG() :
                                                <div className={"payment-section-wrap"} >
                                                    {Utilities.getMasterData().pg.credit_debit_card &&
                                                        <div
                                                            className={"payment-selection" + (this.onMethodSelected(Utilities.getMasterData().pg.credit_debit_card) ? (' cashfree-view-wallet' + (this.state.paymentTypeSelected == PAYMENT_TYPE.CREDIT_DEBIT_CARD ? ' height-expand' : '')) : '')}>

                                                            {
                                                                <div onClick={() => this.onPaymentMethodSelect(Utilities.getMasterData().pg.credit_debit_card, PAYMENT_TYPE.CREDIT_DEBIT_CARD)} >
                                                                    {AppLabels.CREDIT_DEBIT_CARD}
                                                                    <img src={Images.CREDIT_IMG} alt="" />
                                                                </div>

                                                            }
                                                        </div>
                                                    }
                                                    {Utilities.getMasterData().pg.wallet &&
                                                        <div
                                                            className={"payment-selection" + (this.state.selectedWallet != '' ? ' payment-selection-border' : '') + (this.onMethodSelected(Utilities.getMasterData().pg.wallet) ? ' cashfree-view-wallet height-expand' : '')}>
                                                            {
                                                                this.onMethodSelected(Utilities.getMasterData().pg.wallet)
                                                                    ?
                                                                    this.renderWalletView()
                                                                    :
                                                                    <div onClick={() => this.onPaymentMethodSelect(Utilities.getMasterData().pg.wallet, PAYMENT_TYPE.WALLET)} >
                                                                        {AppLabels.PAYTM_WALLET}
                                                                        <img src={Images.PAYTM_IMG} alt="" />
                                                                    </div>

                                                            }

                                                        </div>
                                                    }
                                                  
                                                    {Utilities.getMasterData().pg.net_banking &&
                                                        <div
                                                            className={"payment-selection" + (this.state.selectedNetBanking != '' ? ' payment-selection-border' : '') + (this.onMethodSelected(Utilities.getMasterData().pg.net_banking) ? ' cashfree-view-wallet height-expand' : '')}>
                                                            {
                                                                this.onMethodSelected(Utilities.getMasterData().pg.net_banking)
                                                                    ?
                                                                    this.renderNBView()
                                                                    :
                                                                    <div onClick={() => this.onPaymentMethodSelect(Utilities.getMasterData().pg.net_banking, PAYMENT_TYPE.NET_BANKING)} >
                                                                        {AppLabels.NET_BANKING}
                                                                    </div>

                                                            }

                                                        </div>
                                                    }

                                                </div>
                                        }

                                    </Col>
                                </Row> */}
                                {showThankYouModal &&
                                    <Thankyou ThankyouModalShow={this.ThankYouModalShow} ThankYouModalHide={this.ThankYouModalHide} goToLobbyClickEvent={this.goToLobby} seeMyContestEvent={this.seeMyContest} isDFSTour={this.state.isDFSTour} />
                                }
                                {
                                    showStateTagModal &&
                                    <EditStateAndCityModal
                                        {...this.props}
                                        mShow={showStateTagModal}
                                        mHide={this.hideStateTagModal}
                                    />
                                }
                                {
                                    this.state.showQrCodeModal && <QrCodeCryptoModal {...this.props} preData={{
                                        mShow: this.state.showQrCodeModal,
                                        mHide: this.hideQrCodeModal,
                                        cryptoData: this.state.cryptoData,
                                        status: 0
                                    }} />
                                }

                                {/* {
                                    this.state.checkIfCashfreeExist &&
                                    <Button disabled={!this.state.paymentTypeSelected} onClick={() => this.GoCashFreeDeposit(this.state.paymentTypeSelected)} className="btn-block btn-primary bottom">
                                        {AppLabels.PAY_NOW}
                                    </Button>
                                } */}
                                {
                                    showUJC &&
                                    <UnableJoinContest
                                        showM={showUJC}
                                        hideM={this.hideUJC}
                                    />
                                }

                            </div>
                        )}
                    </MyContext.Consumer>
                    {/* :
                    <div className="web-container web-container-fixed trans-web-container pay-method-wrap">
                        <div className='hide'>{isCMounted && <Suspense fallback={<div />}><ReactHTMLParser content={PageContent} /></Suspense>}</div>
                        {
                            this.state.isLoading && <CustomLoader />
                        }
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.transactions.title}</title>
                            <meta name="description" content={MetaData.transactions.description} />
                            <meta name="keywords" content={MetaData.transactions.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <Row>
                            <Col sm={12}>
                                <div className="payable-amt">
                                    {AppLabels.TO_PAY}
                                    <div>
                                        <i className="font-style-normal">{Utilities.getMasterData().currency_code}</i>
                                        <span>{amount}</span>
                                    </div>

                                </div>
                            </Col>
                        </Row>
                        <div className='payment-section-wrap'>
                            <div className='payment-selection'>
                                <div onClick={() => this.paymentMPG(0)} >
                                    Bank transfer
                                </div>
                            </div>
                            <div className='payment-selection'>
                                <div onClick={() => this.paymentMPG(1)} >
                                    Crypto
                                </div>
                            </div>
                            <div className='payment-selection'>
                                <div onClick={() => this.paymentMPG(2)} >
                                    UPI
                                </div>
                            </div>
                        </div>
                    </div>
                } */}
                {/* {<ManualPG show={this.state.show} types={this.state.type} type_id={this.state.type_id} closeModal={this.handleClose} />} */}
            </>
        )
    }
}