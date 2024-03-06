import React from 'react';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _isEmpty } from '../../Utilities/Utilities';
import { getUserProfile, getUserBalance, withdrawPending } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { CommonLabels } from '../../helper/AppLabels';
import WSManager from "../../WSHelper/WSManager";
import ls from 'local-storage';
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { UserWinning, DataCountBlock, NetWinning } from "../CustomComponent";
import { OnlyCoinsFlow, DARK_THEME_ENABLE, AllowRedeem } from '../../helper/Constants';
import Images from '../../components/images';
import { DownloadAppBuyCoinModal, BonusExpiryDaysModal } from "../../Modals";
import { createBrowserHistory } from 'history';
import TDSBreakupModal from './TDSBreakupModal';
import { OverlayTrigger, Tooltip } from 'react-bootstrap';
const queryString = require('query-string');
const history = createBrowserHistory();
const location = history.location;
const parsed = queryString.parse(location.search);
export default class Wallet extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            userBalance: "",
            userBalObj: "",
            allowedBonusPercantage: "",
            winningAmt: "",
            profileDetail: WSManager.getProfile(),
            verificationSteps: '',
            bonusCash: '',
            depositAmt: '',
            coin_balance: '',
            withdrawStatus: '',
            allow_withdraw: '',
            accVerified: this.isAccountVerified(WSManager.getProfile()),
            wallet_content: '',
            showBonusExpModal: false,
            be_data: '',
            showDAM: false,
            apiCalled: false,
            profileData: '',
            net_winning: "",
            tdsBreakupshow: false,
            cashbackAmt :''
        }
    }

    componentDidMount() {
        Utilities.handleAppBackManage('my-wallet')
        this.callProfileDetail();
        this.callUserBalanceApi();
        this.showRedeemCM(WSManager.getProfile());
        this.handelBycoinAppEvent();
        Utilities.setScreenName('mywallet')
        // if (WSManager.loggedIn()) {
        //     getUserProfile().then((responseJson) => {
        //         if (responseJson && responseJson.response_code == WSC.successCode) {
        //             this.setState({ profileData: responseJson.data });
        //         }
        //     })
        // }
    }

    handelBycoinAppEvent() {
        window.addEventListener('message', (e) => {
            if (e.data.action == 'buyCoin' && e.data.type == 'succuss') {
                this.callUserBalanceApi();
            }
            else if (e.data.action == 'buySubscription' && e.data.type == 'Success') {
                this.callProfileDetail();
                this.callUserBalanceApi();
            }

        });
    }


    showRedeemCM = (data) => {
        if (Utilities.getMasterData().a_coin !== "0") {
            if (data.user_setting && data.user_setting.redeem == "0" && AllowRedeem) {
                CustomHeader.showRedeemCM();
            }
        }
    }


    isAccountVerified = (data) => {
        let m_e_p_b = Utilities.getMasterData().m_e_p_b;
        let tmpArray = m_e_p_b.split('_');
        let mobOptional = tmpArray.length > 0 ? parseInt(tmpArray[0]) : 1;
        let emailOptional = tmpArray.length > 1 ? parseInt(tmpArray[1]) : 1;
        let panOptional = tmpArray.length > 2 ? parseInt(tmpArray[2]) : 1;
        let bankOptional = tmpArray.length > 3 ? parseInt(tmpArray[3]) : 1;
        return ((data.pan_verified == "1" || panOptional === 0) && (data.is_bank_verified == "1" || bankOptional === 0) && (data.email_verified == "1" || emailOptional === 0) && (data.phone_verfied == "1" || mobOptional === 0)) ? true : false
    }

    goToAddFunds() {
        this.props.history.push({ pathname: '/add-funds', state: {} })
    }
    goToEarnCoin() {
        this.props.history.push({ pathname: "/earn-coins" });
    }

    goToWithdraw() {
        if (this.state.accVerified && this.state.allow_withdraw === 1 && WSManager.getProfile().wdl_status == '1') {
            this.props.history.push({ pathname: '/withdraw', state: { withdrawStatus: this.state.withdrawStatus, isFromWallet: true, allow_withdraw: this.state.allow_withdraw } })
        }
        else if (!this.state.accVerified) {
            Utilities.showToast(AppLabels.WITHDRWAL_ACCOUNT_VARIFICATION_TEXT, 3000, "icon-warning")
            // this.props.history.push({ pathname: '/verify-account', state: {} })
        }
        if (WSManager.getProfile().wdl_status == '2') {
            Utilities.showToast(AppLabels.BLOCKED_TEXT)
        }

    }
    goToTransList() {
        this.props.history.push({ pathname: '/transactions', state: {} })
    }
    goToTds() {
        this.props.history.push({ pathname: '/tds-dashboard', state: {} })
    }
    goToContactUs() {
        this.props.history.push({ pathname: '/contact-us', state: {} })
    }
    goToSelfExclusion() {
        this.props.history.push({ pathname: '/self-exclusion', state: { isFrom: 'my-wallet' } })
    }

    callProfileDetail() {
        getUserProfile().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                ls.set('profile', responseJson.data)
                if (window.ReactNativeWebView) {
                    let data = {
                        action: 'profileApiCall',
                        targetFunc: 'profileApiCall'
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(data));
                }
                this.setState({
                    profileDetail: responseJson.data,
                    profileData: responseJson.data,
                    accVerified: this.isAccountVerified(responseJson.data)
                }, () => {
                    if (!this.state.apiCalled && this.state.profileDetail && this.state.profileDetail.is_bank_verified == 1) {
                        this.withdrawPendingApi()
                    }
                })
            }
        })
    }

    withdrawPendingApi() {
        withdrawPending().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    withdrawStatus: responseJson.data.pending_request,
                    allow_withdraw: responseJson.data.allow_withdraw,
                    apiCalled: true
                })
            }
        })
    }
    callUserBalanceApi() {
        let params = {
            be: 1
        }
        getUserBalance(Utilities.getMasterData().allow_bonus_cash_expiry == 1 ? params : {}).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    userBalance: Utilities.getTotalBalance(responseJson.data.user_balance),
                    winningAmt: responseJson.data.user_balance.winning_amount,
                    bonusCash: responseJson.data.user_balance.bonus_amount,
                    net_winning: String(responseJson.data.user_balance.net_winning),
                    // bonusCash: Utilities.getMasterData().currency_code + responseJson.data.user_balance.bonus_amount,
                    // depositAmt: Utilities.getMasterData().currency_code + responseJson.data.user_balance.real_amount,
                    depositAmt:  responseJson.data.user_balance.real_amount,
                    cashbackAmt : responseJson.data.user_balance.cb_balance || 0,
                    coin_balance: responseJson.data.user_balance.point_balance,
                    wallet_content: responseJson.data.wallet_content,
                    userBalObj: responseJson.data.user_balance,
                    be_data: responseJson.data.bonus_expire
                }, () => {
                    const { winningAmt, bonusCash, coin_balance, userBalance } = this.state
                    Utilities.gtmEventFire('wallet_screen', {
                        total_balance: userBalance,
                        winning_amount: winningAmt,
                        coin_balance: coin_balance,
                        bonus_cash: bonusCash,
                        deposited_balance: responseJson.data.user_balance.real_amount,
                    })
                })
                WSManager.setBalance(responseJson.data.user_balance);

            }
            // this.withdrawPendingApi()
            if (!this.state.apiCalled && this.state.profileDetail && this.state.profileDetail.is_bank_verified == 1) {
                this.withdrawPendingApi()
            }
        })
    }

    goToVerifyAccount() {
        this.props.history.push({
            pathname: '/verify-account',
            state: {
                email_verified: this.state.profileDetail.email_verified,
                phone_verfied: this.state.profileDetail.phone_verfied,
                pan_verified: this.state.profileDetail.pan_verified,
                is_bank_verified: this.state.profileDetail.is_bank_verified,
                isFromProfile: false,
                aadhar_verified: this.state.profileDetail.a_aadhar
            }
        })
    }

    onBonusCashClick = () => {
        if (Utilities.getMasterData().allow_bonus_cash_expiry == 1 && this.state.be_data) {
            this.setState({
                showBonusExpModal: true
            })
        }
    }

    coinAction = (coin_balance) => {
        if (coin_balance == 0 || AllowRedeem === false) {
            this.props.history.push({ pathname: "/earn-coins" });
        } else {
            this.props.history.push({ pathname: "/rewards" });
        }
    }

    goToBuyCoins = () => {

        if (OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2) {
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'openBuyScreen',
                    targetFunc: 'openBuyScreen',
                    currency_code: Utilities.getMasterData().currency_code,
                    subscription: !_isEmpty(this.state.profileDetail.subscription) ? this.state.profileDetail.subscription : false,
                    currentLang: WSManager.getAppLang() ? WSManager.getAppLang() : Utilities.getMasterData().default_lang
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            } else {
                this.showDownloadApp();
            }
        } else {
            this.props.history.push({ pathname: "/buy-coins", state: { userBalance: this.state.userBalObj } });
        }
    }
    // goToSubcription = () => {
    //     if (OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2) {

    //     if (window.ReactNativeWebView) {
    //         let data = {
    //             action: 'openBuyScreen',
    //             targetFunc: 'openBuyScreen',
    //             currency_code:Utilities.getMasterData().currency_code,
    //             subscription:!_isEmpty(this.state.profileDetail.subscription) ? this.state.profileDetail.subscription :false
    //         }
    //         window.ReactNativeWebView.postMessage(JSON.stringify(data));
    //     } else {
    //         this.showDownloadApp();
    //     }
    // }else{
    //     this.props.history.push({ pathname: "/buy-coins", state: { userBalance: this.state.userBalObj } });

    // }
    // }

    showDownloadApp = () => {
        this.setState({
            showDAM: true
        })
    }

    hideDownloadApp = () => {
        this.setState({
            showDAM: false
        })
    }

    UNSAFE_componentWillMount() {
        if (Utilities.getMasterData().a_btcpay == 1) {
            this.checkTransactionFlow()

        }
    }
    checkTransactionFlow = () => {
        setTimeout(() => {
            let url = window.location.href;
            if (parsed && parsed.status == "success") {
                parsed['status'] = 0
                Utilities.showToast(AppLabels.Your_payment_successful, 1500);
                if (url.includes('?')) {
                    url = url.split('?')[0];
                    window.history.replaceState("", "", url);
                }
            } else if (parsed && parsed.status == "failed") {
                parsed['status'] = 0
                Utilities.showToast(AppLabels.Your_payment_failed, 1500);
                if (url.includes('?')) {
                    url = url.split('?')[0];
                    window.history.replaceState("", "", url);
                }
            } else if (parsed && parsed.status == "pending") {
                parsed['status'] = 0
                Utilities.showToast(AppLabels.Your_payment_pending, 1500);
                if (url.includes('?')) {
                    url = url.split('?')[0];
                    window.history.replaceState("", "", url);

                }
            }

        }, 500);
    }


    // geoValidate = (profileData, value) => {
    //     let bn_state = localStorage.getItem('banned_on')
    //     let aadhar_data = WSManager.getProfile()

    //     if ((bn_state == 0)) {
    //         if (Utilities.getMasterData().a_aadhar == "1" && WSManager.loggedIn()) {
    //             if (profileData && profileData.aadhar_status == "1") {
    //                 if (value == 'add-funds') {
    //                     this.goToAddFunds()
    //                 }
    //                 else {
    //                     this.goToWithdraw()
    //                 }
    //             }
    //             else {
    //                 if(Utilities.getMasterData().adr_deposit == '1'){
    //                     // this.aadharConfirmation()
    //                     if (value == 'add-funds') {
    //                     this.goToAddFunds()
    //                     }
    //                 }else{
    //                     Utilities.aadharConfirmation(aadhar_data, this.props)
    //                     // this.goToAddFunds()
    //                 }
    //             }
    //         }
    //         else {
    //             if (value == 'add-funds') {
    //                 this.goToAddFunds()
    //             }
    //             else {
    //                 this.goToWithdraw()
    //             }
    //         }
    //     }
    //     else {
    //         Utilities.bannedStateToast(bn_state)
    //     }
    // }


    geoValidate = (profileData, value) => {
        let bn_state = localStorage.getItem('banned_on')
        let aadhar_data = WSManager.getProfile()

        if ((bn_state == 0)) {
            if (Utilities.getMasterData().a_aadhar == "1" && WSManager.loggedIn()) {
                if (profileData && profileData.aadhar_status == "1") {
                    if (value == 'add-funds') {
                        this.goToAddFunds()
                    }
                    else {
                        this.goToWithdraw()
                    }
                } else{
                    if (Utilities.getMasterData().adr_deposit == '0') {
                        if (value == 'add-funds') {
                            this.goToAddFunds()
                        }
                        else {
                            if(profileData && profileData.aadhar_status == "1"){
                            this.goToWithdraw()
                            }else{
                                Utilities.aadharConfirmation(aadhar_data, this.props)
                            }
                        }
                    }
                    else {
                        Utilities.aadharConfirmation(aadhar_data, this.props)
                    }
                }


                // else {
                //     Utilities.aadharConfirmation(aadhar_data, this.props)
                // }
            }
            else {
                if (value == 'add-funds') {
                    this.goToAddFunds()
                }
                else {
                    this.goToWithdraw()
                }
            }
        }
        else {
            Utilities.bannedStateToast(bn_state)
        }
    }

    // geoValidate = (profileData, value) => {
    //     let bn_state = localStorage.getItem('banned_on')
    //     let aadhar_data = WSManager.getProfile()

    //     if ((bn_state == 0)) {
    //         if (Utilities.getMasterData().a_aadhar == "1" && WSManager.loggedIn()) {
    //             if (profileData && profileData.aadhar_status == "1") {
    //                 if (value == 'add-funds') {
    //                     this.goToAddFunds()
    //                 }
    //                 else {
    //                     this.goToWithdraw()
    //                 }
    //             }
    //             else {
    //                 Utilities.aadharConfirmation(aadhar_data, this.props)
    //             }
    //         }
    //         else {
    //             if (value == 'add-funds') {
    //                 this.goToAddFunds()
    //             }
    //             else {
    //                 this.goToWithdraw()
    //             }
    //         }
    //     }
    //     else {
    //         Utilities.bannedStateToast(bn_state)
    //     }
    // }

    tdsBreakupHandler = () => {
        this.setState({ tdsBreakupshow: !this.state.tdsBreakupshow })
    }
    newGSTDwld = () => {
        let sessionKey = WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '';
        window.open(WSC.baseURL + 'user/finance/get_gst_report?' +'Sessionkey='+ sessionKey, '_blank');
    }

    render() {
        const HeaderOption = {
            back: true,
            notification: true,
            title: AppLabels.MY_WALLET,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            goBackLobby: Utilities.getMasterData().a_btcpay == 1 ? true : false
        }

        const { accVerified, withdrawStatus, allow_withdraw, coin_balance, wallet_content, showDAM, showBonusExpModal, be_data, profileDetail, profileData, net_winning, tdsBreakupshow } = this.state;
        let subsCriptionPlan = Utilities.getMasterData().a_subscription == 1 ? !_isEmpty(profileDetail.subscription) ? profileDetail.subscription : false : false;
        let planDetails = subsCriptionPlan ? subsCriptionPlan.name + " -" + subsCriptionPlan.coins + " " + AppLabels.COINS + " at" + " " + Utilities.getMasterData().currency_code + subsCriptionPlan.amount : AppLabels.SELECT_SUBCRIPTION_PLAN;


        const masterData = Utilities.getMasterData()
        const TDSBreakupProps = {
            masterData,
            net_winning,
            show: tdsBreakupshow,
            onHide: this.tdsBreakupHandler
        }
        let depositeValue = parseFloat(this.state.depositAmt) + parseFloat(this.state.cashbackAmt)
        let finaldepositValue = Utilities.getMasterData().currency_code + parseFloat(depositeValue).toFixed(2)

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed wallet-wrapper">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        {!this.props.hideHeader &&
                            <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        }
                        <div className={"wallet-header header-with-circle" + (accVerified || OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2 ? '' : ' wallet-header-with-verify')}>
                            <div className="overlay-white-circle"></div>
                            <div className="wallet-cont">
                                <div className="acc-bal">{OnlyCoinsFlow == 1 ? <img className="coin-img" src={Images.IC_COIN} alt="" /> : Utilities.getMasterData().currency_code} {(OnlyCoinsFlow == 1 ? Math.ceil(coin_balance) : Utilities.numberWithCommas(this.state.userBalance)) || '--'}</div>
                                {
                                    (wallet_content && wallet_content.header)
                                        ? <div className="total-bal-text">{wallet_content.header}</div>
                                        : <div className="total-bal-text">{AppLabels.TOTAL_BALANCE}</div>
                                }
                                {
                                    (wallet_content && wallet_content.body)
                                        ? <div className="bal-summary">{wallet_content.body}</div>
                                        : <div className="bal-summary">{AppLabels.WINNINGS} + {AppLabels.BONUS_CASH} + {AppLabels.DEPOSIT}</div>
                                }
                                {/* <div className="currency-circle">
                                    <i className="icon-no-currency"/>
                                </div> */}

                                {(OnlyCoinsFlow != 1 && OnlyCoinsFlow != 2) && <div className={"add-cash-btn" + (Utilities.getMasterData().a_coin == "1" ? ' b35' : '')}>
                                    <a href className="btn btn-primary btn-rounded"
                                        onClick={() => this.geoValidate(profileData, 'add-funds')}>{Utilities.getMasterData().int_version == '0' ? AppLabels.ADD_CASH : AppLabels.ADD_FUNDS}</a>
                                </div>}
                                {
                                    Utilities.getMasterData().a_coin == "1" &&
                                    <a href className="earn-coin-link" onClick={() => this.goToEarnCoin()}>
                                        <img src={Images.IC_COIN} alt="" />
                                        <span>{AppLabels.EARN_COINS}</span>
                                    </a>
                                }
                            </div>
                        </div>
                        <div className={"wallet-body" + (accVerified ? '' : ' wallet-body-with-verify') + ((OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2) ? ' coin-only-head' : '')}>
                            {this.state.winningAmt && (OnlyCoinsFlow != 1 && OnlyCoinsFlow != 2) &&
                                <UserWinning
                                    winningAmt={this.state.winningAmt}
                                    goToVerifyAccount={() => this.goToVerifyAccount()}
                                    StepList={this.state.verificationSteps}
                                    IsProfileVerifyShow={accVerified}
                                />
                            }
                            {
                                (!_isEmpty(masterData.allow_tds) && masterData.allow_tds.ind == 1 && net_winning != '') &&
                                <NetWinning net_winning={net_winning} onClick={this.tdsBreakupHandler} />
                            }
                            {
                                Utilities.getMasterData().a_coin == 1 && <DataCountBlock item={
                                    {
                                        'icon': 'icon-coins-bal-ic',
                                        'count': Math.ceil(coin_balance),
                                        'count_for': AppLabels.COINS_BALANCE,
                                        'isCoin': true
                                    }
                                }
                                    onClick={(e) => { e.stopPropagation(); this.coinAction(coin_balance) }}
                                    onBuyCoins={(e) => { e.stopPropagation(); this.goToBuyCoins() }}
                                />
                            }
                            {
                                Utilities.getMasterData().a_subscription == 1 && Utilities.getMasterData().a_coin == 1 &&
                                <DataCountBlock item={
                                    {
                                        'icon': 'icon-subscription',
                                        'count': AppLabels.COINS_PACKAGE,
                                        'count_for': planDetails,
                                        'isSubsCribe': true,
                                        'isSubTaken': subsCriptionPlan ? true : false
                                    }
                                }

                                    onSubsCribeManage={(e) => { e.stopPropagation(); this.goToBuyCoins() }}
                                />
                            }
                            {
                                OnlyCoinsFlow != 1 && <div className="bal-summary-wrap m-t-20 m-b-20">
                                    <div className="display-table-row">
                                        <div className={"cash-summary-with-amt" + (OnlyCoinsFlow == 2 ? ' p-0' : '')}>
                                            <DataCountBlock item={
                                                {
                                                    'icon': 'icon-bonus1',
                                                    'count': this.state.bonusCash,
                                                    'count_for': AppLabels.BONUS_CASH,
                                                    'isBonusExp': be_data ? true : false,
                                                    'isBonus': true
                                                }
                                            }
                                                onClick={(e) => { e.stopPropagation(); this.onBonusCashClick() }}
                                                countInt={true}
                                            />
                                        </div>
                                        {
                                            OnlyCoinsFlow != 2 && <div className="cash-summary-with-amt">
                                                <DataCountBlock item={
                                                    {
                                                        'icon': 'icon-deposit',
                                                        'count': finaldepositValue,
                                                        'count_for': AppLabels.DEPOSIT
                                                    }
                                                }
                                                    countInt={true}
                                                />
                                            </div>
                                        }
                                          {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" &&  (Utilities.getMasterData(). gst_bonus > 0 || this.state.cashbackAmt > 0 ) &&
                                        <div className="information-icon">
                                         <OverlayTrigger trigger={['click']} placement="left" overlay={
                                            <Tooltip id="tooltip">
                                                <div className="wallet-information-view">
                                                    <div className="value-view">
                                                        <div>{CommonLabels.DEPOSITED_TEXT} :</div>
                                                        <div>{CommonLabels.CASHBACK_TEXT} :</div>
                                                        
                                                    </div>
                                                    <div className="value-view">
                                                    <div className='value-number'>{Utilities.getMasterData().currency_code}{" "}{this.state.depositAmt}</div>
                                                        <div className='value-number'>{Utilities.getMasterData().currency_code}{" "}{this.state.cashbackAmt}</div>
                                                    </div>
                                              </div>
                                            </Tooltip>  
                                        }>
                                            <span className="icon-info" onClick={(e) => e.stopPropagation()} />
                                        </OverlayTrigger>
                                        </div>
                                        // <div className="information-icon"><i className="icon-info" /></div>
                                        }
                                         {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" && <div className="GST-download-view"
                                            onClick={() => this.newGSTDwld()}
                                        >GST<i className="icon-download1" /></div>}
                                    </div>
                                    {
                                        be_data && parseFloat(be_data.total || 0) > 0 && <span className="exp-bal-msg">
                                            <i className="icon-bonus" />
                                            <span className="bonus-amt">{be_data.total}<span className="msg-exp">{AppLabels.EXP_IN_DAY}</span></span>
                                        </span>
                                    }
                                </div>


                            }
                            
                            <div className="cash-action">
                                {
                                    (OnlyCoinsFlow != 1 && OnlyCoinsFlow != 2) && <DataCountBlock item={
                                        {
                                            'icon': 'icon-add-cash',
                                            'count': Utilities.getMasterData().int_version == '0'? AppLabels.ADD_CASH : AppLabels.ADD_FUNDS,
                                            'count_for': Utilities.getMasterData().a_crypto == 1  ? AppLabels.PAY_WITH_CRPTO : Utilities.getMasterData().a_offpg == 1 ?  AppLabels.PAY_USING_BANK_OR_WALLET_TRANSFER :AppLabels.PAY_WITH_DEBIT_CARD_CREDIT_CARD_UPI_MORE,
                                            'isHighlight': true
                                        }
                                    }
                                        onClick={() => this.geoValidate(profileData, 'add-funds')}
                                        countInt={false}
                                    />
                                }
                                {
                                    Utilities.getMasterData().a_coin == 1 && <DataCountBlock item={
                                        {
                                            'icon': 'icon-star-circle',
                                            'count': AppLabels.EARN_COINS_LOWCASE,
                                            'count_for': AppLabels.PLAY_AND_EARN_COINS,
                                            'isHighlight': true
                                        }
                                    }
                                        onClick={() => this.goToEarnCoin()}
                                        countInt={false}
                                    />
                                }
                                {
                                    // withdrawStatus !== '' && 
                                    (OnlyCoinsFlow != 1 && OnlyCoinsFlow != 2) &&
                                    <DataCountBlock item={
                                        {
                                            'icon': 'icon-withdraw',
                                            'count': AppLabels.WITHDRAW_MONEY,
                                            'count_for': WSManager.getProfile().wdl_status == '2' ? AppLabels.WITHDRWALS_ARE_BLOCKED : allow_withdraw !== 0 ? (accVerified ? Utilities.getMasterData().a_crypto == 1 ? AppLabels.WITHDRAW_CRYPTO_MESSAGE : Utilities.getMasterData().int_version == 1 ? AppLabels.BPX_INT_WALLET_TEXT : AppLabels.WITHDRAW_YOUR_WINNINGS_IN_YOUR_ACCOUNT : AppLabels.VERIFY_YOUR_ACCOUNT_FIRST) : AppLabels.YOUR_WITHDRAWAL_REQUEST_IS_PENDING
                                            // 'count_for': (allow_withdraw !== 0) ? (accVerified ? Utilities.getMasterData().a_crypto == 1 ? AppLabels.WITHDRAW_CRYPTO_MESSAGE : AppLabels.WITHDRAW_YOUR_WINNINGS_IN_YOUR_ACCOUNT : AppLabels.VERIFY_YOUR_ACCOUNT_FIRST) : AppLabels.YOUR_WITHDRAWAL_REQUEST_IS_PENDING
                                        }
                                    }
                                        onClick={() => this.geoValidate(profileData, 'withdraw')}
                                        countInt={false}
                                        showPendingIcon={allow_withdraw === 0 && WSManager.getProfile().wdl_status != '2'}
                                    // showPendingIcon={allow_withdraw === 0}
                                    />
                                }
                                <DataCountBlock item={
                                    {
                                        'icon': 'icon-transaction',
                                        'count': AppLabels.TRANSACTION_HISTORY,
                                        'count_for': AppLabels.WHERE__HOW_MUCH_SPENT_KNOW_ALL
                                    }
                                }
                                    onClick={() => this.goToTransList()}
                                    countInt={false}
                                />
                                {
                                    Utilities.getMasterData().allow_self_exclusion == 1 &&
                                    <DataCountBlock item={
                                        {
                                            'icon': 'icon-filter-v2',
                                            'count': AppLabels.SELF_EXCLUSION,
                                            'count_for': AppLabels.SET_A_LIMIT_ON_LOSING_AMT
                                        }
                                    }
                                        onClick={() => this.goToSelfExclusion()}
                                        countInt={false}
                                    />
                                }
                                {
                                    !_isEmpty(masterData.allow_tds) &&
                                    <DataCountBlock item={
                                        {
                                            'icon': 'icon-tds sm',
                                            'count': AppLabels.TDS_DASHBOARD,
                                            'count_for': AppLabels.HRS24_SUPPORT
                                        }
                                    }
                                        onClick={() => this.goToTds()}
                                        countInt={false}
                                    />
                                }


                                <DataCountBlock item={
                                    {
                                        'icon': 'icon-support',
                                        'count': AppLabels.NEED_HELP,
                                        'count_for': AppLabels.HRS24_SUPPORT
                                    }
                                }
                                    onClick={() => this.goToContactUs()}
                                />
                            </div>
                        </div>
                        {showBonusExpModal && <BonusExpiryDaysModal be_data={be_data} mHide={() => this.setState({ showBonusExpModal: false })} />}
                        {
                            showDAM &&
                            <DownloadAppBuyCoinModal
                                hideM={this.hideDownloadApp}
                            />
                        }
                        {
                            tdsBreakupshow &&
                            <TDSBreakupModal {...TDSBreakupProps} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}