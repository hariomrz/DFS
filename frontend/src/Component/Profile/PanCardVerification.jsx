import React,{lazy,Suspense} from 'react';
import { Row, Col, FormGroup, Image } from 'react-bootstrap';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import WSManager from "../../WSHelper/WSManager";
import MetaData from "../../helper/MetaData";
import CustomHeader from '../../components/CustomHeader';
import { inputStyleLeft ,darkInputStyleLeft} from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import { Utilities, blobToFile, _Map, compressImg } from '../../Utilities/Utilities';
import { updatePANCardDetail, verifyUserPan } from "../../WSHelper/WSCallings";
import Validation from "../../helper/Validation";
import CustomLoader from '../../helper/CustomLoader';
import Images from '../../components/images';
import { DARK_THEME_ENABLE } from '../../helper/Constants';
import SimpleMessageModal from '../../Modals/SimpleMessageModal';
const ReactDatePicker = lazy(()=>import('../CustomComponent/ReactDatePicker'));

var globalThis = null;
const today = Utilities.get18YearOldDate();
const options = {
    maxWidthOrHeight: 900       
}
export default class PanVerification extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            autoKYCChanges: this.checkAutoKYCChanges(),
            panCardData: {
                userName: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? WSManager.getProfile().first_name : '',
                panNo: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? WSManager.getProfile().pan_no : "",
                dob: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? new Date(WSManager.getProfile().dob) : "",
                firstName: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? WSManager.getProfile().first_name : '',
                lastName: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? WSManager.getProfile().last_name : '',
            },
            panCardError: {
                userName: '',
                panNo: '',
                dob: '',
                firstName: '',
                lastName: ''
            },
            panCardValidations: {
                userNameVaid: '',
                panNoValid: '',
                dobValid: '',
                firstNameValid: '',
                lastNameValid: ''
            },
            panCardvalid: false,
            userProfile: WSManager.getProfile(),
            panDocFile: '',
            panDocImageURL: (WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') ? Utilities.getPanURL(WSManager.getProfile().pan_image) : '',
            isLoading: false,
            file: '',
            refreshElement: true,
            isLoadingshow: false,
            refreshPage: true,
            showMessageModal: false,
            cameraPermisiionGranted: false,
            auto_PAN_attempted : ''

        }
        this.handleDOBChange = this.handleDOBChange.bind(this);
    }
  
  
    checkAutoKYCChanges = () => {
        var status = false;
        if (Utilities.getMasterData().auto_kyc_enable == 1) {
            if (WSManager.getProfile().pan_image) {
                // status = false
            } else {
                status = WSManager.getProfile().auto_pan_attempted <= (Utilities.getMasterData().auto_kyc_limit - 1 ) || WSManager.getProfile().pan_verified == 1
            }
        }
        return status;
    }

    componentDidMount = () => {
        Utilities.setScreenName('mywallet')
        
        this.handelCameraPermission()
        setTimeout(() => {
            const datePickers = document.getElementsByClassName("react-date-picker__inputGroup");
            if (datePickers && datePickers.length > 0) {
                _Map(datePickers[0].children, (el) => {
                    el.setAttribute("readOnly", true);
                })
            }
        }, 300);
    };

    actionPancard = () => {
        if (WSManager.getIsIOSApp()) {
            this.bankDocUpload.click()
        } else {
            if (window.ReactNativeWebView && !this.state.cameraPermisiionGranted) {
                let data = {
                    action: 'pancamera',
                    targetFunc: 'pancamera',
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
            else {
                this.bankDocUpload.click()
            }
        }

    }

 handelCameraPermission() {
    window.addEventListener('message', (e) => {

         if (e.data.action == 'pancamera' && e.data.type == 'granted') {
                 this.setState({cameraPermisiionGranted:true},()=>{
                     if(this.state.cameraPermisiionGranted){
                         this.bankDocUpload.click()

                     }
                 })
         }
         else if(e.data.action == 'pancamera' && e.data.type == 'denied'){
             this.setState({cameraPermisiionGranted:false})

         }

     });
 }
    onUsernameChange = (e) => {
        let { panCardData } = this.state;
        panCardData.userName = e.target.value;
        this.setState({ panCardData: panCardData });
        this.validateField(e.target.name, e.target.value);
    }

    onPanNoChange = (e) => {
        let { panCardData } = this.state;
        panCardData.panNo = e.target.value;
        this.setState({ panCardData: panCardData });
        this.validateField(e.target.name, e.target.value);
    }

    onDobChange = (e) => {
        let { panCardData } = this.state;
        panCardData.dob = e.target.value;
        this.setState({ panCardData: panCardData });
        this.validateField(e.target.name, e.target.value);
    }
    onFirstNameChange = (e) => {
        let { panCardData } = this.state;
        panCardData.firstName = e.target.value;
        this.setState({ panCardData: panCardData });
        this.validateField(e.target.name, e.target.value);
    }

    onLastNameChange = (e) => {
        let { panCardData } = this.state;
        panCardData.lastName = e.target.value;
        this.setState({ panCardData: panCardData });
        this.validateField(e.target.name, e.target.value);
    }
    handleDOBChange(date) {
        let { panCardData } = this.state;
        panCardData.dob = date;
        this.setState({
            panCardData: panCardData,
            showDatePicker: false,
            refreshElement: false
        }, () => {
            this.setState({ refreshElement: true })
            this.validateField('dob', panCardData.dob)
        });
    }

    validateField(fieldName, value) {
        let { panCardError, panCardValidations } = this.state;

        switch (fieldName) {
            case 'userName':
                panCardValidations.userNameVaid = (Validation.validate('pan_userName', value) === 'success');
                panCardError.userName = panCardValidations.userNameVaid ? '' : AppLabels.is_invalid;
                break;

            case 'firstName':
                panCardValidations.firstNameValid = (Validation.validate('fName', value) === 'success');
                panCardError.firstName = panCardValidations.firstNameValid ? '' : AppLabels.is_invalid;
                break;

            case 'lastName':
                panCardValidations.lastNameValid = (Validation.validate('lName', value) === 'success');
                panCardError.lastName = panCardValidations.lastNameValid ? '' : AppLabels.is_invalid;
                break;

            case 'pan_card':
                panCardValidations.panNoValid = Utilities.getMasterData().int_version != 1 ? (Validation.validate(fieldName, value) === 'success') : (value != '');
                panCardError.panNo = panCardValidations.panNoValid ? '' : AppLabels.is_invalid;
                break;

            case 'dob':
                panCardValidations.dobValid = value != '';
                panCardError.dob = panCardValidations.dobValid ? '' : AppLabels.is_invalid;
                break;

            default:
                break;
        }
        this.setState({
            panCardError: panCardError,
            panCardValidations: panCardValidations
        }, () => {
            this.validateForm(false)
        })
    }

    validateOnSubmit = () => {
        let { panCardError, panCardValidations, panCardData } = this.state;

        panCardValidations.userNameVaid = (Validation.validate('pan_userName', panCardData.userName) === 'success')
        panCardError.userName = panCardValidations.userNameVaid ? '' : AppLabels.is_invalid;

        panCardValidations.firstNameValid = (Validation.validate('fName', panCardData.firstName) === 'success')
        panCardError.firstName = panCardValidations.firstNameValid ? '' : AppLabels.is_invalid;

        panCardValidations.lastNameValid = (Validation.validate('lName', panCardData.lastName) === 'success')
        panCardError.lastName = panCardValidations.lastNameValid ? '' : AppLabels.is_invalid;

        panCardValidations.panNoValid = Utilities.getMasterData().int_version != 1 ? (panCardData.panNo != '' && (Validation.validate('pan_card', panCardData.panNo) === 'success')) : (panCardData.panNo != '');
        panCardError.panNo = panCardValidations.panNoValid ? '' : AppLabels.is_invalid;

        panCardValidations.dobValid = panCardData.dob != '';
        panCardError.dob = panCardValidations.dobValid ? '' : AppLabels.is_invalid;

        this.setState({
            panCardError: panCardError,
            panCardValidations: panCardValidations
        }, () => {
            this.validateForm(true)
        })
    }

    validateForm(submit) {
        let { panCardValidations } = this.state;
        if (this.state.autoKYCChanges) {
            this.setState({
                panCardvalid: panCardValidations.firstNameValid &&
                    panCardValidations.panNoValid &&
                    panCardValidations.lastNameValid &&
                    panCardValidations.dobValid
            }, () => {
                if (submit) {
                    this.verifyAutoKYCPAN()
                }
            })
        } else {
            this.setState({
                panCardvalid: panCardValidations.userNameVaid &&
                    panCardValidations.panNoValid &&
                    panCardValidations.dobValid
            }, () => {
                if (this.state.panCardvalid && submit) {
                    if (this.state.panDocFile == '') {
                        let msg = AppLabels.replace_PANTOID(AppLabels.Please_upload_ID_card);
                        Utilities.showToast(msg, 2000, Images.PAN_ICON)
                    }
                    else {
                        this.uploadPanDocImage()
                    }
                }
            })
        }
    }

    verifyAutoKYCPAN = () => {
        this.setState({ isLoading: true });
        let param = {
            "first_name": this.state.panCardData.firstName,
            "last_name": this.state.panCardData.lastName,
            "pan_no": this.state.panCardData.panNo,
            "dob": Utilities.getFormatedDate({ date: this.state.panCardData.dob, format: 'MMM DD, YYYY' }),
        }
        verifyUserPan(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000, Images.PAN_ICON);
                setTimeout(() => {
                    this.props.history.replace({ pathname: '/my-profile' })
                }, 1000)
            }
            else {
                Utilities.showToast(responseJson.global_error, 5000, Images.PAN_ICON);
                if (responseJson.data && responseJson.data.auto_pan_attempted > (Utilities.getMasterData().auto_kyc_limit - 1 )) {
                    this.setState({
                        // showMessageModal: true
                        auto_PAN_attempted : responseJson.data.auto_pan_attempted
                    })
                }
                if (responseJson.data && responseJson.data.auto_pan_attempted > (Utilities.getMasterData().auto_kyc_limit - 1 )) {
                    this.setState({
                        showMessageModal: true,
                        // auto_PAN_attempted : responseJson.data.auto_pan_attempted
                    })
                }

            }
            this.setState({ isLoading: false });

        })
    }

    // OkButtonClick = () => {
    //     this.setState({
    //         showMessageModal: false
    //     })
    //     let profile = this.state.userProfile;
    //     profile.auto_pan_attempted = '1'
    //     WSManager.setProfile(profile)
    //     this.setState({
    //         refreshPage: false,
    //         autoKYCChanges: false,
    //     }, () => {
    //         this.setState({
    //             refreshPage: true,
    //             panCardvalid: false
    //         })
    //     })
    // }
    OkButtonClick = () => {
        const {auto_PAN_attempted} = this.state;
        this.setState({
            showMessageModal: false
        })
        let profile = this.state.userProfile;
        // profile.auto_pan_attempted = '1'
        // WSManager.setProfile(profile)
           profile.auto_pan_attempted = auto_PAN_attempted
           let lsProfile = WSManager.getProfile();
           lsProfile['auto_pan_attempted'] = auto_PAN_attempted
            WSManager.setProfile(lsProfile);
        if(WSManager.getProfile().auto_pan_attempted < Utilities.getMasterData().auto_kyc_limit){
            this.setState({refreshPage: false, autoKYCChanges : true},()=>{
                this.setState({ refreshPage: true,
                            panCardvalid: false})
            })
        }else{
            this.setState({refreshPage: true, autoKYCChanges : false},()=>{
                this.setState({ refreshPage: true,
                            panCardvalid: false})
            }) 
        }
        // this.setState({
        //     refreshPage: false,
        //     autoKYCChanges: false,
        // }, () => {
        //     this.setState({
        //         refreshPage: true,
        //         panCardvalid: false
        //     })
        // })
    }


    errorClass(error) {
        if (error) {
            return (error.length == 0 ? '' : 'has-error')
        }
    }

    onPanDocImgDrop(e) {
        e.preventDefault();
        let reader = new FileReader();
        let mfile = e.target.files[0];
        reader.onloadend = () => {
            if (mfile.type && (mfile.type == 'image/png' || mfile.type == 'image/jpeg')) {
                this.setState({ panDocImageURL: reader.result, isLoadingshow: true })

                this.compressImage(mfile)

            }
            else {
                Utilities.showToast(AppLabels.UPLOAD_FORMATS, 2000, Images.PAN_ICON)
            }
        }
        if (mfile) {
            reader.readAsDataURL(mfile)
        }
    }
    compressImage = async (mfile) => {
        compressImg(mfile, options).then((compressedFile) => {
            this.setState({ panDocFile: blobToFile(compressedFile ? compressedFile : mfile, mfile.name), isLoadingshow: false }, () => {
            })
        })
    }


    uploadPanDocImage() {
        globalThis.setState({ isLoading: true });
        var data = new FormData();
        data.append("panfile", this.state.panDocFile);
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
                    globalThis.updatePanCardDetail(response.data.file_name);
                }
                else {
                    globalThis.setState({ isLoading: false });
                    var keys = Object.keys(response.error);
                    if (keys.length > 0) {
                        let errorKey = keys[0];
                        Utilities.showToast(response.error[errorKey], 5000, Images.PAN_ICON);
                    }
                }
            }
        });
        xhr.addEventListener("load", function (e) {
            if (e.currentTarget.status > 400) {
                globalThis.setState({ isLoading: false });
            }
        }, false);
        xhr.open("POST", WSC.userURL + WSC.DO_UPLOAD_PAN);
        xhr.setRequestHeader('Sessionkey', WSManager.getToken())
        xhr.send(data);
    }

    updatePanCardDetail(panPath) {
        this.setState({ isLoading: true });
        let param = {
            "first_name": this.state.panCardData.userName,
            "last_name": "",
            "dob": Utilities.getFormatedDate({ date: this.state.panCardData.dob, format: 'MMM DD, YYYY' }),
            "pan_no": this.state.panCardData.panNo,
            "pan_image": panPath
        }
        updatePANCardDetail(param).then((responseJson) => {

            if (responseJson != null && responseJson != '' && responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 5000, Images.PAN_ICON);
                setTimeout(() => {
                    this.props.history.replace({ pathname: '/my-profile' })
                }, 1000)
            }
            else {
                Utilities.showToast(responseJson.error, 5000, Images.PAN_ICON);
            }
            this.setState({ isLoading: false });

        })
    }

    removePanDocImage() {
        this.setState({
            panDocImageURL: '',
            panDocFile: '',
            refreshPage: false
        }, () => {
            this.setState({
                refreshPage: true
            })
        })
    }

    render() {
        globalThis = this;
        const {
            panCardData,
            panCardError,
            userProfile,
            showDatePicker,
            isLoadingshow,
            refreshPage,
            showMessageModal
        } = this.state

        const HeaderOption = {
            back: true,
            notification: false,
            hideShadow: true,
            title: AppLabels.replace_PANTOID(AppLabels.PANCARD_VERIFICATION),
            fromProfile: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

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
                        {
                            refreshPage &&
                            <div className="verify-wrapper">
                                {
                                    window.ReactNativeWebView ?
                                        !this.state.autoKYCChanges && <div className="upload-section cursor-pointer" onClick={() => userProfile.pan_verified != 1 ? this.actionPancard() : ''} >
                                            <input id="myInput"
                                                type="file"
                                                accept="image/*"
                                                ref={(bankImgRef) => this.bankDocUpload = bankImgRef}
                                                style={{ display: 'none' }}
                                                onChange={this.onPanDocImgDrop.bind(this)}
                                            />
                                            <Image className={this.state.panDocImageURL ? 'upload-img-show' : ''} object-fit='cover'
                                                src={!this.state.panDocImageURL ? '' : (this.state.panDocFile != '' ? this.state.panDocImageURL : Utilities.getPanURL(this.state.panDocImageURL))} />
                                            {(this.state.panDocFile != '' && !this.state.isLoading) &&
                                                <span className="delete-selected-img">
                                                    <i id="removeUploadedimg" onClick={(e) => { e.stopPropagation(); this.removePanDocImage() }}
                                                        className="icon-delete"></i>
                                                </span>
                                            }
                                            {!this.state.panDocImageURL &&
                                                <React.Fragment>
                                                    <div className="text-center">
                                                        {/* <i className="icon-pancard"></i> */}
                                                        <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                                                    </div>
                                                    {userProfile.pan_verified != 1 &&
                                                        <div className="upload-text" id="bankDocUpload" >{AppLabels.replace_PANTOID(AppLabels.UPLOAD_PAN_CARD)}</div>
                                                    }
                                                    <p className="upload-details">{AppLabels.MAX_SIZE_UPLOAD}</p>
                                                    <p className="upload-details">{AppLabels.UPLOAD_FORMATS}</p>
                                                </React.Fragment>
                                            }
                                        </div>
                                        :
                                    !this.state.autoKYCChanges && <div className="upload-section cursor-pointer" onClick={() => userProfile.pan_verified != 1 ? this.bankDocUpload.click() : ''} >
                                        <input id="myInput"
                                            type="file"
                                            accept="image/*"
                                            ref={(bankImgRef) => this.bankDocUpload = bankImgRef}
                                            style={{ display: 'none' }}
                                            onChange={this.onPanDocImgDrop.bind(this)}
                                        />
                                        <Image alt='' className={this.state.panDocImageURL ? 'upload-img-show' : ''} object-fit='cover'
                                            src={!this.state.panDocImageURL ? '' : (this.state.panDocFile != '' ? this.state.panDocImageURL : Utilities.getPanURL(this.state.panDocImageURL))} />
                                        {(this.state.panDocFile != '' && !this.state.isLoading) &&
                                            <span className="delete-selected-img">
                                                <i id="removeUploadedimg" onClick={(e) => { e.stopPropagation(); this.removePanDocImage() }}
                                                    className="icon-delete"></i>
                                            </span>
                                        }
                                        {!this.state.panDocImageURL &&
                                            <React.Fragment>
                                                <div className="text-center">
                                                    {/* <i className="icon-pancard"></i> */}
                                                    <img src={Images.PAN_ICON_PNG} alt="" className="pan-img" />
                                                </div>
                                                {userProfile.pan_verified != 1 &&
                                                    <div className="upload-text" id="bankDocUpload" >{AppLabels.replace_PANTOID(AppLabels.UPLOAD_PAN_CARD)}</div>
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
                                <form className={"uploaded-info-section" + (userProfile && userProfile.pan_verified == '1' ? ' noneditable-section' : '')}>
                                    {
                                        !this.state.autoKYCChanges && <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center input-transparent gray-input-field ' + (`${this.errorClass(panCardError.userName)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='userName'
                                                        name='userName'
                                                        disabled={(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2')}
                                                        placeholder={AppLabels.replace_PANTOID(AppLabels.NAME_ON_PANCARD)}
                                                        value={panCardData.userName}
                                                        type='text'
                                                        onChange={this.onUsernameChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    {
                                        this.state.autoKYCChanges && <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center input-transparent gray-input-field ' + (`${this.errorClass(panCardError.firstName)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='firstName'
                                                        name='firstName'
                                                        disabled={(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2')}
                                                        placeholder={AppLabels.FIRST_NAME}
                                                        value={panCardData.firstName}
                                                        type='text'
                                                        onChange={this.onFirstNameChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    {
                                        this.state.autoKYCChanges && <Row>
                                            <Col xs={12} className="input-label-spacing">
                                                <FormGroup
                                                    className={'input-label-center input-transparent gray-input-field ' + (`${this.errorClass(panCardError.lastName)}`)}
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                        id='lastName'
                                                        name='lastName'
                                                        disabled={(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2')}
                                                        placeholder={AppLabels.LAST_NAME}
                                                        value={panCardData.lastName}
                                                        type='text'
                                                        onChange={this.onLastNameChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                    }
                                    <Row>
                                        <Col xs={12} className="input-label-spacing">
                                            <FormGroup
                                                className={'input-label-center input-transparent gray-input-field ' + (`${this.errorClass(panCardError.panNo)}`)}
                                                controlId="formBasicText"
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='pan_card'
                                                    name='pan_card'
                                                    disabled={(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2')}
                                                    placeholder={AppLabels.replace_PANTOID(AppLabels.PANCARD_NUMBER)}
                                                    type='text'
                                                    value={panCardData.panNo ? panCardData.panNo : ''}
                                                    onChange={this.onPanNoChange}
                                                />
                                                <div className={"error-msg pan-error-msg" + (this.state.autoKYCChanges ? ' pb-3' : '')}>
                                                    {AppLabels.replace_PANTOID(AppLabels.PLEASE_ENTER_VALID_PAN_CARD_NUMBER)}
                                                </div>
                                            </FormGroup>
                                            {!this.state.autoKYCChanges && <div className={"pan-help-text" + (this.errorClass(panCardError.panNo) === 'has-error' ? ' mb20' : '')}>
                                                {userProfile && (userProfile.pan_verified == '0' || userProfile.pan_verified == '2') &&
                                                    AppLabels.replace_PANTOID(AppLabels.PANCARD_HELP_TEXT)
                                                }
                                            </div>}
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col sm={12} className="">
                                            <FormGroup className={'input-label-center input-transparent dob-date-picker zInx16 ' + (`${this.errorClass(panCardError.dob)}`)}
                                            >
                                                <div className='datepicker_display float-label'>
                                                    <div className="dobField" >
                                                        <label onClick={() => !(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2') && this.setState({ showDatePicker: !showDatePicker })} className="dob-text disb-dob">
                                                            {this.state.refreshElement &&
                                                                <FormGroup className='input-label-center input-transparent '>
                                                                    <FloatingLabel
                                                                        readOnly
                                                                        autoComplete='off'
                                                                        styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                                        id='dob'
                                                                        name='dob'
                                                                        placeholder={AppLabels.DOB}
                                                                        type='text'
                                                                        value={panCardData.dob ? Utilities.getFormatedDate({ date: panCardData.dob, format: "MMM DD, YYYY" }) : ''}
                                                                    />
                                                                </FormGroup>
                                                            }
                                                        </label>
                                                        <Suspense fallback={<div />} ><ReactDatePicker
                                                        id='dob'
                                                        className='date-picker-custom'
                                                        required={true}
                                                        disabled={(WSManager.getProfile().pan_no && WSManager.getProfile().pan_verified != '2')}
                                                        activeStartDate={today}
                                                        minDetail='decade'
                                                        locale='en-IN'
                                                        onChange={this.handleDOBChange}
                                                        maxDate={today}
                                                        value={panCardData.dob}
                                                        isOpen={showDatePicker}
                                                    /></Suspense>
                                                    </div>
                                                </div>
                                                {/* <div className='dob-border col-sm-12' /> */}
                                            </FormGroup>
                                        </Col>
                                    </Row>
                                </form>
                                {(WSManager.getProfile().pan_no == '' || WSManager.getProfile().pan_no == null || WSManager.getProfile().pan_verified == 2) &&
                                    <div className="text-center  btm-fixed-action btm-btn-verify">
                                        <a
                                            href
                                            id="verifyPanCard"
                                            className={"button button-primary-rounded btn-verify" + (this.state.panCardvalid && !this.state.isLoading ? '' : ' disabled')}
                                            onClick={() => this.validateOnSubmit()}
                                        >
                                            {AppLabels.replace_PANTOID(AppLabels.VERIFY_PANCARD)}
                                        </a>
                                    </div>
                                }
                            </div>
                        }
                        {
                            showMessageModal &&
                            <SimpleMessageModal data={{
                                onButtonClick: this.OkButtonClick,
                                Icon: Images.QUES_ICON,
                                firstMsg: AppLabels.ADDITIONAL_INFO,
                                secondMsg: AppLabels.ADDITIONAL_PAN_DESC
                            }} />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}
