import React from 'react';
import { Row, Col, FormGroup, Image } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { inputStyleLeft,darkInputStyleLeft } from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import { Utilities, blobToFile, compressImg } from '../../Utilities/Utilities';
import {
    updateUserBankDetail,
    deleteUserBankDetail,
    verifyUserBank
} from "../../WSHelper/WSCallings";
import Validation from "../../helper/Validation";
import { DeleteConfirmationModal } from "../../Modals";
import CustomLoader from '../../helper/CustomLoader';
import Images from '../../components/images';
import { setValue, DARK_THEME_ENABLE } from '../../helper/Constants';
import SimpleMessageModal from '../../Modals/SimpleMessageModal';

var globalThis = null;
const options = {
    maxWidthOrHeight: 900
}
export default class BankVerification extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            autoKYCChanges: this.checkAutoKYCChanges(),
            bankFormData: {
                userFullname: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.first_name : '',
                firstName: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.first_name : '',
                lastName: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.last_name : '',
                bankName: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.bank_name : '',
                accountNumber: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.ac_number : '',
                ifscCode: WSManager.getProfile().user_bank_detail.ac_number ? WSManager.getProfile().user_bank_detail.ifsc_code : '',
                upi_id: WSManager.getProfile().user_bank_detail.upi_id ? WSManager.getProfile().user_bank_detail.upi_id : ''

            },
            bankFormErrors: {
                userFullname: '',
                bankName: '',
                accountNumber: '',
                ifscCode: '',
                firstName:'',
                lastName:'',
                upi_id: ''
            },
            bankValidation: {
                userFullnameValid: '',
                bankNameValid: '',
                accountNumberValid: '',
                ifscCodeValid: '',
                firstNameValid: '',
                lastNameValid: '',
                upiIdValid: ''

            },
            bankformValid: false,
            userProfile: WSManager.getProfile(),
            bankDocFile: '',
            bankDocImageURL: WSManager.getProfile().user_bank_detail.ac_number ? Utilities.getPanURL(WSManager.getProfile().user_bank_detail.bank_document) : '',
            isLoading: false,
            file: '',
            showDeleteModal: false,
            refreshPage: true,
            isLoadingshow: false,
            showMessageModal: false,
            cameraPermisiionGranted:false,
            auto_Bank_attempted : ''
        }
    }

    checkAutoKYCChanges = () => {
        var status = false;
        if (Utilities.getMasterData().auto_kyc_enable == 1) {
            console.log('checkAutoKYCChanges1')
            if (WSManager.getProfile().user_bank_detail && WSManager.getProfile().user_bank_detail.bank_document) {
                console.log('checkAutoKYCChanges12')
                // status = false
            } else {
                console.log('checkAutoKYCChanges13')
                status = WSManager.getProfile().auto_bank_attempted == 0 || WSManager.getProfile().is_bank_verified == 1 || WSManager.getProfile().is_bank_verified == 2
            }
            console.log('checkAutoKYCChanges1 status',status)
        }
        return status;
    }

    ShowDeletConfirmModal = () => {
        this.setState({
            showDeleteModal: true
        })
    }
    HideDeletConfirmModal = () => {
        this.setState({
            showDeleteModal: false
        })
    }

    deleteBankDetail = () => {
        deleteUserBankDetail().then((responseJson) => {
            this.HideDeletConfirmModal();
            if (responseJson && responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 1000, Images.BANK_ICON);
                let profile = this.state.userProfile;
                profile.user_bank_detail = [];
                profile.is_bank_verified = '0'
                WSManager.setProfile(profile)
                this.setState({
                    autoKYCChanges: false,
                    userProfile: profile,
                    bankDocImageURL: '',
                    bankFormData: {
                        userFullname: '',
                        bankName: '',
                        accountNumber: '',
                        ifscCode: ''
                    },
                    refreshPage: false
                }, () => { this.setState({ refreshPage: true }) })
                setValue.setBankDeleted(true);
            }
        })
    }

    UNSAFE_componentWillMount = () => {
        Utilities.setScreenName('mywallet')
        
        globalThis = this;
        if (WSManager.getProfile().is_bank_verified == '0' && WSManager.getProfile().user_bank_detail.ac_number) {
            this.props.history.replace({ pathname: '/my-profile' })
        }
    };
    actionPancard = () => {
        if (WSManager.getIsIOSApp()) {
            this.bankDocUpload.click()
        } else {
            if (window.ReactNativeWebView && !this.state.cameraPermisiionGranted) {
                let data = {
                    action: 'bankcamera',
                    targetFunc: 'bankcamera',
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            else {
                this.bankDocUpload.click()
            }
        }

    }
    componentDidMount = () => {
        this.handelCameraPermission()
    };

    handelCameraPermission() {
        window.addEventListener('message', (e) => {

            if (e.data.action == 'bankcamera' && e.data.type == 'granted') {
                this.setState({ cameraPermisiionGranted: true }, () => {
                    if (this.state.cameraPermisiionGranted) {
                        this.bankDocUpload.click()

                    }
                })
            }
            else if (e.data.action == 'bankcamera' && e.data.type == 'denied') {
                this.setState({ cameraPermisiionGranted: false })

            }

        });
    }

    onFullNameAsPerBankChange = (e) => {
        let { bankFormData } = this.state;
        bankFormData.userFullname = e.target.value;

        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }

    handleChangeBankName = (e) => {
        let { bankFormData } = this.state;
        bankFormData.bankName = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }
    handleChangeUPI = (e) => {
        let { bankFormData } = this.state;
        bankFormData.upi_id = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)

    }

    handleChangeAccountNo = (e) => {
        let { bankFormData } = this.state;
        bankFormData.accountNumber = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }

    handleChangeIfscCode = (e) => {
        let { bankFormData } = this.state;
        bankFormData.ifscCode = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }

    handleChangeFirstName = (e) => {
        let { bankFormData } = this.state;
        bankFormData.firstName = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }
    handleChangeLastName = (e) => {
        let { bankFormData } = this.state;
        bankFormData.lastName = e.target.value;
        this.setState({ bankFormData: bankFormData })
        this.validateField(e.target.name, e.target.value)
    }

    validateField(fieldname, value) {
        let { bankFormErrors, bankValidation } = this.state;

        switch (fieldname) {
            case 'fName':
                bankValidation.userFullnameValid = (Validation.validate(fieldname, value) === 'success');
                bankFormErrors.userFullname = bankValidation.userFullnameValid ? '' : '' + AppLabels.is_invalid;
                break;
            case 'firstName':
                bankValidation.firstNameValid = (Validation.validate('fName', value) === 'success');
                bankFormErrors.firstName = bankValidation.firstNameValid ? '' : '' + AppLabels.is_invalid;
                break;
            case 'lastName':
                bankValidation.lastNameValid = (Validation.validate('lName', value) === 'success');
                bankFormErrors.lastName = bankValidation.lastNameValid ? '' : '' + AppLabels.is_invalid;
                break;
            case 'bankName':
                bankValidation.bankNameValid = (Validation.validate(fieldname, value) === 'success');
                bankFormErrors.bankName = bankValidation.bankNameValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            case 'accountNo':
                bankValidation.accountNumberValid = (Validation.validate(fieldname, value) === 'success');
                bankFormErrors.accountNumber = bankValidation.accountNumberValid ? '' : ' ' + AppLabels.is_invalid;
                break;
            case 'ifscCode':
                if(Utilities.getMasterData().int_version == 1){
                    bankValidation.ifscCodeValid = value.length > 2 && value.length < 26;
                    bankFormErrors.ifscCode = value.length > 2 && value.length < 26 ? '' : ' ' + AppLabels.is_invalid;
                }else{
                    bankValidation.ifscCodeValid = Utilities.getMasterData().int_version != 1 ? (Validation.validate(fieldname, value) === 'success') : true;
                    bankFormErrors.ifscCode = Utilities.getMasterData().int_version != 1 ? (bankValidation.ifscCodeValid ? '' : ' ' + AppLabels.is_invalid) : '';
                }
                break;
            // case 'upi_id':
            //     bankValidation.upiIdValid = (Validation.validate(fieldname, value) === 'success');
            //     bankFormErrors.upi_id = bankValidation.upiIdValid ? '' : ' ' + AppLabels.is_invalid; 
            //     break;
            default:
                break;
        }

        this.setState({
            bankFormErrors: bankFormErrors,
            bankValidation: bankValidation
        }, this.validateForm(false));
    }

    validateForm = (submit) => {
        let { bankValidation } = this.state;
        if (this.state.autoKYCChanges) {
            this.setState({
                bankformValid: bankValidation.firstNameValid &&
                    bankValidation.lastNameValid &&
                    bankValidation.bankNameValid &&
                    bankValidation.accountNumberValid &&
                    bankValidation.ifscCodeValid
            }, () => {
                    if (submit) {
                        if (this.validateValidUpi()) {
                            this.verifyAutoKYCBANK()
                        }
                    }
            })
        } else {
            this.setState({
                bankformValid: bankValidation.userFullnameValid &&
                    bankValidation.bankNameValid &&
                    bankValidation.accountNumberValid &&
                    // bankValidation.upiIdValid &&
                    bankValidation.ifscCodeValid
            }, () => {
                if (submit && this.state.bankformValid) {
                    if (this.state.bankDocFile == '') {
                        Utilities.showToast(AppLabels.Please_upload_Bank_document, 2000, Images.BANK_ICON)
                    }
                    else {
                        if (this.validateValidUpi()) {
                            this.uploadBankDocImage();
                        }
                    }
                }
            });
        }
    }

    verifyAutoKYCBANK = () => {
        this.setState({ isLoading: true });
        let param = {
            "first_name": this.state.bankFormData.firstName,
            "last_name": this.state.bankFormData.lastName,
            "bank_name": this.state.bankFormData.bankName,
            "ac_number": this.state.bankFormData.accountNumber,
            "ifsc_code": this.state.bankFormData.ifscCode,
            "upi_id": this.state.bankFormData.upi_id

        }
        verifyUserBank(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson.response_code == WSC.successCode) {
                setValue.setBankDeleted(false);
                Utilities.showToast(responseJson.message, 5000, Images.BANK_ICON);
                setTimeout(() => {
                    this.props.history.replace({ pathname: '/my-profile' })
                }, 1000)
            } else {
                // Utilities.showToast(responseJson.message, 5000, Images.BANK_ICON);
                Utilities.showToast(responseJson.global_error, 5000, Images.BANK_ICON);
                if (responseJson.data && responseJson.data.auto_bank_attempted > (Utilities.getMasterData().auto_kyc_limit - 1 ) ) {
                    this.setState({
                        showMessageModal: true,
                        auto_Bank_attempted : responseJson.data.auto_bank_attempted
                    })
                }
            }
        })
    }


 OkButtonClick = () => {
        const {auto_Bank_attempted} = this.state;
        this.setState({
            showMessageModal: false
        })
        let profile = this.state.userProfile;
        // profile.auto_bank_attempted = '1'
        // WSManager.setProfile(profile)
        let lsProfile = WSManager.getProfile();
        lsProfile['auto_bank_attempted'] = auto_Bank_attempted
         WSManager.setProfile(lsProfile);
        if(WSManager.getProfile().auto_bank_attempted < Utilities.getMasterData().auto_kyc_limit){
            this.setState({refreshPage: false, autoKYCChanges : true},()=>{
                this.setState({ refreshPage: true,
                    bankformValid: false})
            })
        }else{
            this.setState({refreshPage: true, autoKYCChanges : false},()=>{
                this.setState({ refreshPage: true,
                    bankformValid: false})
            }) 
        }
        // this.setState({
        //     refreshPage: false,
        //     autoKYCChanges: false,
        // }, () => {
        //     this.setState({
        //         refreshPage: true,
        //         bankformValid: false
        //     })
        // })
    }

    // OkButtonClick = () => {
    //     this.setState({
    //         showMessageModal: false
    //     })
    //     let profile = this.state.userProfile;
    //     profile.auto_bank_attempted = '1'
    //     WSManager.setProfile(profile)
    //     this.setState({
    //         refreshPage: false,
    //         autoKYCChanges: false,
    //     }, () => {
    //         this.setState({
    //             refreshPage: true,
    //             bankformValid: false
    //         })
    //     })
    // }

    errorClass(error) {
        if (error) {
            return (error.length == 0 ? '' : 'has-error');
        }
    }

    validateOnSubmit = () => {
        if (this.state.userProfile.is_bank_verified == '0') {
            let { bankFormData, bankFormErrors, bankValidation } = this.state;

            bankValidation.userFullnameValid = (Validation.validate('fName', bankFormData.userFullname) === 'success');
            bankFormErrors.userFullname = bankValidation.userFullnameValid ? '' : ' ' + AppLabels.is_invalid;

            bankValidation.firstNameValid = (Validation.validate('fName', bankFormData.firstName) === 'success');
            bankFormErrors.firstName = bankValidation.firstNameValid ? '' : ' ' + AppLabels.is_invalid;
            
            bankValidation.lastNameValid = (Validation.validate('fName', bankFormData.lastName) === 'success');
            bankFormErrors.lastName = bankValidation.lastNameValid ? '' : ' ' + AppLabels.is_invalid;

            bankValidation.bankNameValid = (Validation.validate('bankName', bankFormData.bankName) === 'success');
            bankFormErrors.bankName = bankValidation.bankNameValid ? '' : ' ' + AppLabels.is_invalid;

            bankValidation.accountNumberValid = (Validation.validate('accountNo', bankFormData.accountNumber) === 'success');
            bankFormErrors.accountNumber = bankValidation.accountNumberValid ? '' : ' ' + AppLabels.is_invalid;

            if (Utilities.getMasterData().int_version != 1) {
                bankValidation.ifscCodeValid = (Validation.validate('ifscCode', bankFormData.ifscCode) === 'success');
                bankFormErrors.ifscCode = bankValidation.ifscCodeValid ? '' : ' ' + AppLabels.is_invalid;
            }else{
                bankValidation.ifscCodeValid = bankFormData.ifscCode.length > 2 && bankFormData.ifscCode.length < 26;
                bankFormErrors.ifscCode = bankFormData.ifscCode.length > 2 && bankFormData.ifscCode.length < 26 ? '' : ' ' + AppLabels.is_invalid;
            }
            // bankValidation.upiIdValid = (Validation.validate('upi_id', bankFormData.upi_id) === 'success');
            // bankFormErrors.upi_id = bankValidation.upiIdValid ? '' : ' ' + AppLabels.is_invalid;
            this.setState({
                bankFormErrors: bankFormErrors,
                bankValidation: bankValidation
            }, this.validateForm(true)
            );

        }
        else {

            this.ShowDeletConfirmModal();
        }
    }

    onBankDocImgDrop(e) {
        e.preventDefault();
        let reader = new FileReader();
        let mfile = e.target.files[0];
        reader.onloadend = () => {
            if (mfile.type && (mfile.type == 'image/png' || mfile.type == 'image/jpeg')) {
                this.setState({ bankDocImageURL: reader.result, isLoadingshow: true })
                
                this.compressImage(mfile)

            }
            else {
                Utilities.showToast(AppLabels.UPLOAD_FORMATS, 2000, Images.BANK_ICON)
            }
        }
        if (mfile) {
            reader.readAsDataURL(mfile)
        }
    }
    compressImage = async (mfile) => {
        compressImg(mfile, options).then((compressedFile) => {
            this.setState({ bankDocFile: blobToFile(compressedFile ? compressedFile : mfile, mfile.name), isLoadingshow: false }, () => {
            })
        })
    }


    uploadBankDocImage() {
        globalThis.setState({ isLoading: true });
        var data = new FormData();
        data.append("bank_document", this.state.bankDocFile);
        var xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.addEventListener("readystatechange", function () {
            if (this.readyState == 4) {
                if (!this.responseText) {
                    Utilities.showToast(AppLabels.SOMETHING_ERROR, 5000, Images.PAN_ICON);
                    globalThis.setState({ isLoading: false });
                    return;
                }
                var response = JSON.parse(this.responseText);
                if (response != '' && response.response_code == WSC.successCode) {
                    globalThis.setState({ bankDocFile: '', bankDocImageURL: response.data.file_name })
                    globalThis.updateBankAccDetailsApi(response.data.file_name);
                }
                else {
                    globalThis.setState({ isLoading: false });
                    var keys = Object.keys(response.error);
                    if (keys.length > 0) {
                        let errorKey = keys[0];
                        Utilities.showToast(response.error[errorKey], 5000, Images.BANK_ICON);
                    }
                }

            }
        });
        xhr.addEventListener("load", function (e) {
            if (e.currentTarget.status > 400) {
                globalThis.setState({ isLoading: false });
            }
        }, false);
        xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD_BANK_DOCUMENT);
        xhr.setRequestHeader('Sessionkey', WSManager.getToken())
        xhr.send(data);
    }

    updateBankAccDetailsApi(panPath) {
        this.setState({ isLoading: true });
        let param = {
            "first_name": this.state.bankFormData.userFullname,
            "last_name": "",
            "bank_name": this.state.bankFormData.bankName,
            "ac_number": this.state.bankFormData.accountNumber,
            "ifsc_code": this.state.bankFormData.ifscCode ? this.state.bankFormData.ifscCode.toUpperCase() : this.state.bankFormData.ifscCode,
            "bank_document": panPath,
            "upi_id": this.state.bankFormData.upi_id
        }
        updateUserBankDetail(param).then((responseJson) => {
            this.setState({ isLoading: false });
            if (responseJson != null && responseJson != '' && responseJson.response_code == WSC.successCode) {
                setValue.setBankDeleted(false);
                Utilities.showToast(responseJson.message, 5000, Images.BANK_ICON);
                setTimeout(() => {
                    this.props.history.replace({ pathname: '/my-profile' })
                }, 1000)
            }
        })
    }

    removeBankDocImage() {
        this.setState({
            bankDocImageURL: '',
            bankDocFile:'',
            refreshPage: false
        },()=>{
            this.setState({
                refreshPage: true
            })
        })
    }

    isPDF(url) {
        let isPDF = url.endsWith('.pdf')
        return isPDF;
    }
    validateValidUpi=()=>{
        var isValid = true;
        if (this.state.bankFormData.upi_id != null && this.state.bankFormData.upi_id != '') {
            let upiIdValid = Validation.validate('upi_id', this.state.bankFormData.upi_id) === 'success'
            if (!upiIdValid) {
                Utilities.showToast(AppLabels.VALID_UPI_MESSAGE, 5000);
                isValid =false;
            }
            else{
                isValid =true;
             }
        }
        return isValid
    }

    render() {
        const {
            userProfile,
            bankFormData,
            bankFormErrors,
            showDeleteModal,
            refreshPage,
            isLoadingshow,
            showMessageModal
        } = this.state;

        const HeaderOption = {
            back: true,
            notification: false,
            hideShadow: true,
            title: AppLabels.BankVerification,
            fromProfile: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        let bankDocDescTxt = AppLabels.UPLOAD_BANK_DOC_DESC || ''
        let banCodeMSg = AppLabels.BANK + ' ' + AppLabels.CODE;
        let bankDocDesc = Utilities.getMasterData().int_version != 1 ? bankDocDescTxt : bankDocDescTxt.replace((', ' + AppLabels.IFSC_CODE), (', ' + banCodeMSg).toLowerCase())
        console.log('first this.state.autoKYCChanges',this.state.autoKYCChanges)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container transparent-header web-container-fixed verify-account">
                        {this.state.isLoading &&
                            <CustomLoader />
                        }
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.mywallet.title}</title>
                            <meta name="description" content={MetaData.mywallet.description} />
                            <meta name="keywords" content={MetaData.mywallet.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        {refreshPage &&
                            <div className="verify-wrapper" >
                                {
                                    !this.state.autoKYCChanges && window.ReactNativeWebView ?
                                        <div className="upload-section cursor-pointer" onClick={() => userProfile.bank_verified != '1' ? this.actionPancard() : ''} style={{ pointerEvents: (userProfile.is_bank_verified == '2' || userProfile.is_bank_verified == '1') ? 'none' : '' }}>
                                            <input id="myInput"
                                                type="file"
                                                accept="image/*"
                                                ref={(bankImgRef) => this.bankDocUpload = bankImgRef}
                                                style={{ display: 'none' }}
                                                onChange={this.onBankDocImgDrop.bind(this)}
                                            />
                                            {(this.isPDF(this.state.bankDocImageURL)) ?
                                                <span>{'' + this.state.bankDocImageURL}</span>
                                                :
                                                <Image className={this.state.bankDocImageURL ? 'upload-img-show' : ''} object-fit='cover'
                                                    src={!this.state.bankDocImageURL ? '' : (this.state.bankDocFile != '' ? this.state.bankDocImageURL : Utilities.getPanURL(this.state.bankDocImageURL))} />
                                            }

                                            {(userProfile.is_bank_verified != '1' && userProfile.is_bank_verified != '2')
                                                && (this.state.bankDocImageURL) &&
                                                <span className="delete-selected-img">
                                                    <i id="removeUploadedimg" onClick={(e) => { e.stopPropagation(); this.removeBankDocImage() }}
                                                        className="icon-delete"></i>
                                                </span>
                                            }
                                            {!this.state.bankDocImageURL &&
                                                <React.Fragment>
                                                    <div className="text-center">
                                                        <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                                                    </div>
                                                    {userProfile.bank_verified != '1' &&
                                                        <div className="upload-text" id="bankDocUpload" >{AppLabels.UPLOAD_BANK_DOC}</div>
                                                    }
                                                    {userProfile.bank_verified != '1' &&
                                                        <div className="upload-description" id="bankDocUpload" >( {bankDocDesc} )</div>
                                                    }
                                                    <p className="upload-details">{AppLabels.MAX_SIZE_UPLOAD}</p>
                                                    <p className="upload-details">{AppLabels.UPLOAD_FORMATS}</p>
                                                </React.Fragment>
                                            }
                                        {
                                            isLoadingshow &&
                                            <div className="upload-loader"><div className="loader" /></div>
                                        }
                                        </div>
                                        :
                                        !this.state.autoKYCChanges &&
                                        <div className="upload-section cursor-pointer a1" onClick={() => userProfile.bank_verified != '1' ? this.bankDocUpload.click() : ''} style={{ pointerEvents: (userProfile.is_bank_verified == '2' || userProfile.is_bank_verified == '1') ? 'none' : '' }}>
                                            <input id="myInput"
                                                type="file"
                                                accept="image/*"
                                                ref={(bankImgRef) => this.bankDocUpload = bankImgRef}
                                                style={{ display: 'none' }}
                                                onChange={this.onBankDocImgDrop.bind(this)}
                                            />
                                            {(this.isPDF(this.state.bankDocImageURL)) ?
                                                <span>{'' + this.state.bankDocImageURL}</span>
                                                :
                                                <Image className={this.state.bankDocImageURL ? 'upload-img-show' : ''} object-fit='cover'
                                                    src={!this.state.bankDocImageURL ? '' : (this.state.bankDocFile != '' ? this.state.bankDocImageURL : Utilities.getPanURL(this.state.bankDocImageURL))} />
                                            }

                                            {(userProfile.is_bank_verified != '1' && userProfile.is_bank_verified != '2')
                                                && (this.state.bankDocImageURL) &&
                                                <span className="delete-selected-img">
                                                    <i id="removeUploadedimg" onClick={(e) => { e.stopPropagation(); this.removeBankDocImage() }}
                                                        className="icon-delete"></i>
                                                </span>
                                            }
                                            {!this.state.bankDocImageURL &&
                                                <React.Fragment>
                                                    <div className="text-center">
                                                        <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                                                    </div>
                                                    {userProfile.bank_verified != '1' &&
                                                        <div className="upload-text" id="bankDocUpload" >{AppLabels.UPLOAD_BANK_DOC}</div>
                                                    }
                                                    {userProfile.bank_verified != '1' &&
                                                        <div className="upload-description" id="bankDocUpload" >( {bankDocDesc} )</div>
                                                    }
                                                    <p className="upload-details">{AppLabels.MAX_SIZE_UPLOAD}</p>
                                                    <p className="upload-details">{AppLabels.UPLOAD_FORMATS}</p>
                                                </React.Fragment>
                                            }
                                        {
                                            isLoadingshow &&
                                            <div className="upload-loader"><div className="loader" /></div>
                                        }
                                        </div>
                                }
                                {console.log('this.state.autoKYCChanges',this.state.autoKYCChanges)}
                            <div className={"uploaded-info-section" + (userProfile && userProfile.is_bank_verified == '1' ? ' noneditable-section' : '')} style={{ pointerEvents: (userProfile.is_bank_verified == '2' || userProfile.is_bank_verified == '1') ? 'none' : '' }}>
                                    {
                                    !this.state.autoKYCChanges && <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center a2 input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.userFullname)}`)}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='fName'
                                                    name='fName'
                                                    placeholder={AppLabels.FULL_NAME_AS_BANK}
                                                    type='text'
                                                    value={bankFormData.userFullname}
                                                    onChange={this.onFullNameAsPerBankChange}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                }
                                {
                                        this.state.autoKYCChanges &&  <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.firstName)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='firstName'
                                                        name='firstName'
                                                        placeholder={AppLabels.FIRST_NAME}
                                                        type='text'
                                                        value={bankFormData.firstName}
                                                        onChange={this.handleChangeFirstName}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    {
                                        this.state.autoKYCChanges &&  <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.lastName)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='lastName'
                                                        name='lastName'
                                                        placeholder={AppLabels.LAST_NAME}
                                                        type='text'
                                                        value={bankFormData.lastName}
                                                        onChange={this.handleChangeLastName}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.bankName)}`)}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='bankName'
                                                    name='bankName'
                                                    placeholder={AppLabels.BANK_NAME}
                                                    type='text'
                                                    value={bankFormData.bankName}
                                                    onChange={this.handleChangeBankName}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.accountNumber)}`)}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='accountNo'
                                                    name='accountNo'
                                                    placeholder={AppLabels.ACCOUNT_NUMBER}
                                                    type='text'
                                                    value={bankFormData.accountNumber}
                                                    onChange={this.handleChangeAccountNo}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    {
                                        // Utilities.getMasterData().int_version != 1 && 
                                        <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center ifsc-inp input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.ifscCode)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='ifscCode'
                                                        name='ifscCode'
                                                        placeholder={ Utilities.getMasterData().int_version != 1 ? AppLabels.IFSC_CODE : banCodeMSg}
                                                        type='text'
                                                        value={bankFormData.ifscCode}
                                                        onChange={this.handleChangeIfscCode}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    {
                                        Utilities.getMasterData().int_version != 1 && <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent font-14 gray-input-field ' + (`${this.errorClass(bankFormErrors.upi_id)}`)}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyleLeft}
                                                    id='upi_id'
                                                    name='upi_id'
                                                    placeholder={"UPI"}
                                                    type='email'
                                                    value={bankFormData.upi_id}
                                                    onChange={this.handleChangeUPI}
                                                />
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                    }
                                </div>
                                {/* <div className={"text-center m-t-30- btm-fixed-action btm-bank btm-btn-verify" + (Utilities.getMasterData().int_version != 1 && !this.state.autoKYCChanges ? ' btm-bank-non-fixed' : '')}> */}
                                <div className="text-center m-t-30- btm-fixed-action btm-bank btm-btn-verify btm-bank-non-fixed">

                                    <a
                                        href
                                        className={"button button-primary-rounded btn-verify" + (this.state.bankformValid ? '' : userProfile && (userProfile.is_bank_verified == '1' || userProfile.is_bank_verified == '2') ? '' : ' disabled')}
                                        id="bankDocSubmit"
                                        onClick={() => this.validateOnSubmit()}
                                    >
                                        {userProfile && userProfile.is_bank_verified == '0' ?
                                            AppLabels.VERIFY_BANK_DETAILS
                                            :
                                            AppLabels.DELETE
                                        }
                                    </a>
                                </div>
                            </div>
                        }
                        {showDeleteModal &&
                            <DeleteConfirmationModal IsShow={showDeleteModal} IsHide={this.HideDeletConfirmModal} onDelete={this.deleteBankDetail} />
                        }
                        {
                            showMessageModal &&
                            <SimpleMessageModal data={{
                                onButtonClick: this.OkButtonClick,
                                Icon: Images.QUES_ICON,
                                firstMsg: AppLabels.ADDITIONAL_INFO,
                                secondMsg: AppLabels.ADDITIONAL_BANK_DESC
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
