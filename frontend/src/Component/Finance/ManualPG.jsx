import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import { FormGroup, FormControl, ControlLabel, Modal, ModalBody, ModalHeader } from 'react-bootstrap';
import Images from '../../components/images';
import { MyContext } from '../../InitialSetup/MyProvider'
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { Utilities, _Map, blobToFile, compressImg } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { typeList, transactionUpdate, ImageUploadMPG } from '../../WSHelper/WSCallings';
import { BanStateEnabled, DARK_THEME_ENABLE } from '../../helper/Constants';
import WSManager from '../../WSHelper/WSManager';
import * as Constants from "../../helper/Constants";
import ls from 'local-storage';
import { createBrowserHistory } from 'history';
import CustomLoader from '../../helper/CustomLoader';
import CopyToClipboard from 'react-copy-to-clipboard';
import { event } from 'react-ga';
import _ from 'lodash';
import { NoDataView } from '../CustomComponent';
import Moment from 'react-moment';

const queryString = require('query-string');
var globalThis = null;
const options = {
    maxWidthOrHeight: 900
}


class ManualPG extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoading: false,
            checked: false,
            url_Updated: '',
            urlCheck: false,
            imageUrl: '',
            bankDocImageURL: '',
            profileImageFile: '',
            Amount: '',
            typelist_st: [],
            receipt: '',
            types: '',//default-bank
            type_id: '',
            transactionList: {},
            upi_true: false,
            crypto_true: false,
            bank_true: false,
            amountUP: true,

            //WALLET
            QR_image: '',//For upi
            upi_id: '',//For upi
            disclaimer: '',//For upi
            user_info: '',//For upi
            tid_wallet: '',//For upi

            //CRYPTO
            QR_image_cr: '',//crypto
            upi_id_cr: '',//crypto
            disclaimer_cr: '',//crypto
            user_info_cr: '',//crypto
            tid_crypto: '',//crypto

            //BANK
            acc_no: '',//For bank
            ifsc: '',//for bank
            disclaimer_bn: '',//for bank
            user_info_bn: '',//for bank
            bank_name: '',//For bank
            tid_bank: '',//For bank


        }
    }
    typeListAPI = () => {
        let param = {}
        this.setState({ isLoaderShow: true })
        typeList(param).then((responseJson) => {
            let tempArray = responseJson.data.type
            let transaction = responseJson.data.last_txns
            console.log(tempArray, 'aasdas')
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    typelist_st: responseJson.data.type, transactionList: transaction,
                    isLoaderShow: false

                })
                if (!_.isEmpty(responseJson.data.type)) {
                    if (responseJson.data.type[0].key === 'wallet') {
                        this.setState({
                            types: 2,
                            type_id: responseJson.data.type[0].type_id
                        })
                    }
                    if (responseJson.data.type[0].key === 'crypto') {
                        this.setState({
                            types: 1,
                            type_id: responseJson.data.type[0].type_id
                        })
                    }
                    if (responseJson.data.type[0].key === 'bank') {
                        this.setState({
                            types: 0,
                            type_id: responseJson.data.type[0].type_id
                        })
                    }
                }
                _Map(tempArray, (item, index) => {
                    //For upi
                    if (item.key == 'wallet') {
                        this.setState({
                            QR_image: item.custom_data.qr_code,
                            upi_id: item.custom_data.upi_id,
                            disclaimer: item.custom_data.disclaimer,
                            user_info: item.custom_data.user_info_txt,
                            upi_true: true,
                            tid_wallet: item.type_id
                        })
                    }
                    //crypto
                    else if (item.key == 'crypto') {
                        this.setState({
                            QR_image_cr: item.custom_data.qr_code,
                            upi_id_cr: item.custom_data.upi_id,
                            disclaimer_cr: item.custom_data.disclaimer,
                            user_info_cr: item.custom_data.user_info_txt,
                            crypto_true: true,
                            tid_crypto: item.type_id
                        })
                    }
                    else if (item.key == 'bank') {
                        this.setState({
                            acc_no: item.custom_data.acc_no,
                            ifsc: item.custom_data.ifsc,
                            disclaimer_bn: item.custom_data.disclaimer,
                            user_info_bn: item.custom_data.user_info_txt,
                            bank_name: item.custom_data.bank,
                            bank_true: true,
                            tid_bank: item.type_id
                        })
                    }
                })


            }
        })
    }
    componentDidMount() {
        globalThis = this
        this.typeListAPI()

    }
    ValidateWebsiteUrl = (websiteUrl) => {
        return (websiteUrl.includes('http') || websiteUrl.includes('https') || websiteUrl.includes('www.')) ? this.setState({ urlCheck: true, url_Updated: websiteUrl }) : this.setState({ urlCheck: false })
    }
    handleInputChange = (e) => {
        let value = e.target.value
        if (value.length == '' || value.length < 101) {
            this.setState({ url_Updated: e.target.value })
        }
        else {
        }
    }
    validateInput = (input) => {
        const regex = /^([1-9]\d{0,5})$/;
        return regex.test(input);
    }

    handleInputChanges = (e) => {
        this.setState({ amountUP: true })
        let Mvalue = e.target.value
        if (this.validateInput(Mvalue)) {
            this.setState({ Amount: Mvalue, amountUP: false })
        }
        else {

            this.showCopyToast(AppLabels.AMOUNT_VAL)
            if (Mvalue.length == 1 || Mvalue.length == 0) {
                this.setState({ Amount: '' })
            }

        }
    }
    handlechange = () => {
        this.setState({ checked: !this.state.checked })
        console.log(this.state.checked)
    }
    confirmFunds = () => {
        // alert(this.state.url_Updated)
        this.props.history.push({ pathname: '/my-wallet' })
    }
    showCopyToast = (message) => {
        Utilities.showToast(message, 2000)
    }
    onCopyLink = () => {
        if (this.state.types == 1) {
            this.showCopyToast(AppLabels.WALLET_COPY);
        }
        else if (this.state.types == 2) {
            this.showCopyToast(AppLabels.UPI_COPY);
        }
    }
    onCopyLinkACC = () => {
        this.showCopyToast(AppLabels.AMOUNT_COPY);
    }
    onCopyLinkIFSC = () => {
        this.showCopyToast(AppLabels.IFSC_COPY);
    }

    transactionUpdateShow = () => {
        let params = {
            bank_ref: this.state.url_Updated,
            amount: this.state.Amount,
            type_id: this.state.type_id,
            receipt: this.state.receipt
        }
        transactionUpdate(params).then((response) => {
            if (response.response_code == WSC.successCode) {
                this.showCopyToast(response.message, 'success')
                this.confirmFunds();
            }
            else {
                this.showCopyToast(response.error.message)
            }
        })
    }
    onSubmitCrypto = () => {
        let { checked, url_Updated, amountUP } = this.state
        if (checked && url_Updated && !amountUP) {
            if (this.state.receipt != '' && this.state.types != 1) {
                this.transactionUpdateShow()
                this.props.history.push({ pathname: '/my-wallet' })
            }
            else if (this.state.types == 1) {
                this.transactionUpdateShow()
            }
            else {
                this.showCopyToast(AppLabels.ATTACH_IMG)
            }
        }
        else {
            if (!url_Updated) { this.showCopyToast(AppLabels.T_LINK_UP) }
        }
    }
    removeImg = () => {
        var data = new FormData();
        data.append("file_name", this.state.receipt);
        data.append("type", 'mpg');

        var xhr = new XMLHttpRequest();
        // console.log('xhr', xhr)
        xhr.withCredentials = false;
        xhr.addEventListener("readystatechange", function () {
            if (this.readyState == 4) {
                // console.log('000000')
                if (!this.responseText) {
                    // console.log('1111')
                    Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
                    return;
                }
                var response = JSON.parse(this.responseText);
                if (response !== '' && response.response_code == WSC.successCode) {
                    globalThis.setState({
                        isLoading: false,
                        imageUrl: '',
                        receipt: ''
                    })
                }
                else {
                    if (response.global_error && response.global_error != '') {
                        // console.log('33333')
                        Utilities.showToast(response.global_error, 5000);
                    }
                    else {
                        // console.log('444444')
                        var keys = Object.keys(response.error);
                        if (keys.length > 0) {
                            Utilities.showToast(response.global_error, 5000);
                        }
                    }
                }

            }
        })
        xhr.open("POST", WSC.userURL + WSC.REMOVE_MEDIA_MPG);
        xhr.setRequestHeader('Sessionkey', WSManager.getToken())
        xhr.send(data);
    }
    handleImage = (e) => {
        let file = e.target.files[0]

        var data = new FormData();
        data.append("file_name", file);
        data.append("type", 'mpg');

        var xhr = new XMLHttpRequest();
        // console.log('xhr', xhr)
        xhr.withCredentials = false;
        xhr.addEventListener("readystatechange", function () {
            if (this.readyState == 4) {
                // console.log('000000')
                if (!this.responseText) {
                    // console.log('1111')
                    Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
                    return;
                }
                var response = JSON.parse(this.responseText);
                if (response !== '' && response.response_code == WSC.successCode) {
                    globalThis.setState({
                        isLoading: false,
                        imageUrl: response.data.image_url,
                        receipt: response.data.image_name
                    })
                }
                else {
                    if (response.message && response.message != '') {
                        // console.log('33333')
                        Utilities.showToast(response.message, 5000);
                    }
                    else {
                        // console.log('444444')
                        var keys = Object.keys(response.message);
                        if (keys.length > 0) {
                            Utilities.showToast(response.message, 5000);
                        }
                    }
                }

            }
            // console.log('5555555')
        });

        xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD_PROOF);
        xhr.setRequestHeader('Sessionkey', WSManager.getToken())
        xhr.send(data);
    }
    onValueChange = (e) => {
        this.setState({
            types: e,

        })
        if (e == 0) {
            this.setState({
                type_id: this.state.tid_bank,
                Amount: '',
                url_Updated: '',
            })
            this.removeImg()
        }
        if (e == 1) {
            this.setState({
                type_id: this.state.tid_crypto,
                Amount: '',
                url_Updated: ''
            })
            this.removeImg()
        }
        if (e == 2) {
            this.setState({
                type_id: this.state.tid_wallet,
                Amount: '',
                url_Updated: ''
            })
            this.removeImg()
        }

    }
    closeModalFn = () => {
        this.props.closeModal()
    }
    render() {
        const { isLoaderShow, upi_true, crypto_true, bank_true, checked, url_Updated, types, isLoading, transactionList, typelist_st } = this.state
        // const { show, types } = this.props
        const HeaderOption = {
            back: true,
            notification: false,
            title: AppLabels.ADD_FUNDS,
            fromAddFund: false,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }
        // console.log(typelist_st, 'typelist_sttypelist_sttypelist_sttypelist_st')
        return (
            <MyContext.Consumer >
                {(context) => (

                    <div className="web-container web-container-fixed add-funds-wrapper xwhite-bg bg-clr-fix">
                        {isLoading && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.addfunds.title}</title>
                            <meta name="description" content={MetaData.addfunds.description} />
                            <meta name="keywords" content={MetaData.addfunds.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {!_.isEmpty(typelist_st) ?
                            <div>
                                {(!_.isUndefined(typelist_st) || !_.isEmpty(typelist_st)) && typelist_st.length > 1 ?
                                    <div className='paymentmode-block'>
                                        <div className='method-label'>{AppLabels.PAYMENT_MODE_S}</div>
                                        <div className='radio-block'>
                                            {upi_true &&
                                                <div className="radio">
                                                    <label>
                                                        <input
                                                            type="radio"
                                                            checked={this.state.types === 2}
                                                            onChange={() => this.onValueChange(2)}
                                                        />
                                                        {AppLabels.MODE_UPI}
                                                    </label>
                                                </div>}
                                            {crypto_true &&
                                                <div className="radio">
                                                    <label>
                                                        <input
                                                            type="radio"
                                                            checked={this.state.types === 1}
                                                            onChange={() => this.onValueChange(1)}
                                                        />
                                                        {AppLabels.MODE_CR}
                                                    </label>
                                                </div>}
                                            {bank_true &&
                                                <div className="radio">
                                                    <label>
                                                        <input
                                                            type="radio"
                                                            checked={this.state.types === 0}
                                                            onChange={() => this.onValueChange(0)}
                                                        />
                                                        {AppLabels.MODE_BANK}
                                                    </label>
                                                </div>}
                                        </div>
                                    </div>
                                    : ''
                                }
                                {(types == 1 || types == 2) && <div className='qr-body'>
                                    {types == 1 ?
                                        <p className='disc-para'>{this.state.user_info_cr}</p>
                                        :
                                        <p className='disc-para'>{this.state.user_info}</p>
                                    }{types == 1 ?
                                        //for crypto 
                                        <div className='qr-div'>
                                            <img src={WSC.S3_BUCKET_PATH + 'upload/mpg/' + this.state.QR_image_cr}
                                                className='qr-img'
                                                alt='QR' title='QR Image' />
                                        </div> :
                                        //for wallet
                                        <div className='qr-div'>
                                            <img src={WSC.S3_BUCKET_PATH + 'upload/mpg/' + this.state.QR_image}
                                                className='qr-img'
                                                alt='QR' title='QR Image' />
                                        </div>}

                                    <div className='copy-to-clipboard'>
                                        {types == 1 ? <div className='wallet-address'>
                                            <span className='p-l-cls'>{AppLabels.PAYTM_WALLET}:</span>{' '}
                                            <span className='wallet-addres-span'>{this.state.upi_id_cr}</span>
                                        </div> :
                                            <div className='wallet-address'>
                                                <span className='p-l-cls'>{AppLabels.UPI} id:</span>{' '}
                                                <span className='wallet-addres-span'>{this.state.upi_id}</span>
                                            </div>}
                                        <CopyToClipboard onCopy={this.onCopyLink} text={types == 1 ? this.state.upi_id_cr : this.state.upi_id} className="social-circle">
                                            <div className='copy-to-clipboard'>
                                                <span className='copy-ic'>
                                                    <i className='icon-copy' />
                                                </span>
                                            </div>
                                        </CopyToClipboard>
                                    </div>
                                </div>}
                                {types == 0 && <div className='qr-body'>
                                    <p className='disc-para'>{this.state.user_info_bn}</p>
                                    <div className='bank-details'>
                                        <div className='copy-to-clipboard'>
                                            <span className='type-txt'>{AppLabels.BANK_NAME} : </span>
                                            <span className='bold-txt'>{this.state.bank_name}</span>
                                        </div>
                                        <div className='copy-to-clipboard'>
                                            <div>
                                                <span className='type-txt'>{AppLabels.ACCOUNT_NUMBER} : </span>
                                                <span className='bold-txt'>{this.state.acc_no}</span>
                                            </div>
                                            <CopyToClipboard onCopy={this.onCopyLinkACC} text={this.state.acc_no} className="ic-clr-mpg">
                                                <span className='copy-ic'>
                                                    <i className='icon-copy' />
                                                </span>
                                            </CopyToClipboard>
                                        </div>
                                        <div className='copy-to-clipboard'>
                                            <div>
                                                <span className='type-txt'>{AppLabels.IFSC_CODE} : </span>
                                                <span className='bold-txt'>{this.state.ifsc}</span>
                                            </div>

                                            <CopyToClipboard onCopy={this.onCopyLinkIFSC} text={this.state.ifsc} className="ic-clr-mpg">
                                                <span className='copy-ic'>
                                                    <i className='icon-copy' />
                                                </span>
                                            </CopyToClipboard>
                                        </div>
                                    </div>

                                </div>
                                }
                                <div className="field">
                                    <input
                                        type="numeric"
                                        name="Amount"
                                        id="Amount"
                                        placeholder=" "
                                        className='input-cls'
                                        value={this.state.Amount}
                                        onChange={this.handleInputChanges}
                                    />
                                    <label htmlFor="Amount">{AppLabels.T_AMOUNT}</label>
                                </div>
                                <div className="field">
                                    <input
                                        type="text"
                                        name="url_Updated"
                                        id="url_Updated"
                                        minLength={4}
                                        maxLength={100}
                                        placeholder=" "
                                        className='input-cls'
                                        value={this.state.url_Updated}
                                        onChange={this.handleInputChange}
                                    />
                                    <label htmlFor="url_Updated">{AppLabels.TRANSACTION_LINK}</label>
                                </div>
                                <div className='transaction-txt'>
                                    <span>
                                        {this.state.types == 1 ? AppLabels.IMAGE_PROOF_OPTIONAL : AppLabels.IMAPE_PROFF}
                                    </span>
                                </div>
                                {<div className="verify-wrapper aadhar-block pt-fix" >
                                    <div className='upload-aadhar h-fix-mpg'>
                                        {
                                            this.state.imageUrl != '' ?
                                                <div className='flex-image'>
                                                    <div className='remove-img'>
                                                        <img src={this.state.imageUrl} className='img-uploaded' alt='Proof' />
                                                        <span className='cross-ic-m'>
                                                            <i className='icon-cross-circular change-btn'
                                                                onClick={() => this.removeImg()}
                                                            />
                                                        </span>
                                                    </div>
                                                </div>
                                                :
                                                <>
                                                    <div className='pos-relative'>
                                                        <img src={Images.IMAGE_IMAGE} alt='Proof' className='image-gallary' />
                                                        <input type='file' onChange={(e) => this.handleImage(e)} className='image-upoader' accept="image/jpg, image/jpeg, image/png"></input>
                                                    </div>
                                                    <h5 className='selectImg-heading'>{AppLabels.ATTACH_IMG}</h5>
                                                </>
                                        }

                                    </div>
                                </div>}
                                <div className='pfix bold-title'>{AppLabels.DISCLAIMER}</div>
                                <div className='disclaimer-block'>
                                    <div className='disclamer-check'>
                                        <div className='chk-div'>
                                            <label className="container">
                                                <input type="checkbox" className='check-bxs' onClick={this.handlechange} />
                                                <span className="checkmark"></span>
                                            </label>
                                        </div>
                                        <div>
                                            {types == 1 ?
                                                <div>
                                                    {this.state.disclaimer_cr}
                                                </div>
                                                : types == 2 ?
                                                    <div>
                                                        {this.state.disclaimer}
                                                    </div>
                                                    :
                                                    <div>
                                                        {this.state.disclaimer_bn}
                                                    </div>
                                            }
                                        </div>
                                    </div>
                                </div>
                                <div className='flex-div'>
                                    {this.state.types != 1 ? <button
                                        className={checked && url_Updated
                                            && this.state.imageUrl != ''
                                            && !this.state.amountUP
                                            ? 'confirm-btn' : 'inactive'}
                                        disabled={!checked}
                                        onClick={this.onSubmitCrypto}>
                                        {AppLabels.CONFIRM}
                                    </button> :
                                        <button
                                            className={checked && url_Updated
                                                && !this.state.amountUP
                                                ? 'confirm-btn' : 'inactive'}
                                            disabled={!checked}
                                            onClick={this.onSubmitCrypto}>
                                            {AppLabels.CONFIRM}
                                        </button>
                                    }
                                </div>
                                <div className='last-transactions'>
                                    <span className='bold-title'>{AppLabels.RECENT_TRANSACTION}</span>
                                    <div className='border-block-txn'>
                                        {!_.isEmpty(transactionList) ? <table className='table-trnx'>
                                            <thead>
                                                <tr>
                                                    <th>Sr. no</th>
                                                    <th>Date / time</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                            {
                                                _Map(transactionList, (item, idx) => {
                                                    return (
                                                        <tbody key={idx}>
                                                            <tr>
                                                                <td className='tac'>{idx + 1}</td>
                                                                <td>
                                                                    {Utilities.getFormatedDateTime(item.added_date, "DD/MM/YYYY | hh:mm a")}
                                                                </td>
                                                                <td>{parseInt(item.amount)}</td>
                                                                <td>{item.status == '0' ? 'Pending' : item.status == '1' ? 'Approved' : item.status == '2' ? 'Rejected' : 'Fake Entry'}</td>
                                                            </tr>
                                                        </tbody>
                                                    )
                                                })
                                            }
                                        </table>
                                            :
                                            <NoDataView
                                                BG_IMAGE={Images.NDF_IMAGE}
                                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.NDF_IMAGE : Images.NDF_IMAGE}
                                                MESSAGE_1={AppLabels.NO_DATA_FOUND} />}
                                    </div>
                                </div>
                            </div>
                            : isLoaderShow ? <CustomLoader /> :
                                <NoDataView
                                    BG_IMAGE={Images.NDF_IMAGE}
                                    CENTER_IMAGE={DARK_THEME_ENABLE ? Images.NDF_IMAGE : Images.NDF_IMAGE}
                                    MESSAGE_1={AppLabels.NO_DATA_FOUND} />}

                    </div>

                )}
            </MyContext.Consumer>

        );
    }
}

export default withRouter(ManualPG);
