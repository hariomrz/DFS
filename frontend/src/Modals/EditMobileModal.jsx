import React, { Suspense, lazy } from 'react';
import { Modal, FormGroup, Row, Col } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import FloatingLabel from 'floating-label-react';
import ls from 'local-storage';
import { inputStyle } from '../helper/input-style';
import OtpInput from 'react-otp-input';
import { editMobile, verifyEditedMobile } from '../WSHelper/WSCallings';
import * as WSC from "../WSHelper/WSConstants";
import { Utilities } from '../Utilities/Utilities';
import Images from '../components/images';
import { ONLY_SINGLE_COUNTRY, DEFAULT_COUNTRY_CODE } from '../helper/Constants';
const CustomPhoneInput = lazy(()=>import('../Component/CustomComponent/CustomPhoneInput'));


export default class EditMobileModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            code: '',
            phone: '',
            otp: '',
            mUserProfile: ls.get('profile'),
            isOtpShown: true,
            isValidMobileNo: false,
            enableButton: true,
            showSendMsg: false,
            isOtpCorrect: true
        };
    }

    /**
     *@description Check browser and manage input field accordingly 
     */
    checkBrowserISOpera() {
        navigator.userAgent.match(/Opera Mini/i)
        const isOpera = (navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) !== -1
        return isOpera;
    }

    handleOnChange = (value, data) => {
        if (ONLY_SINGLE_COUNTRY == 1 && value.startsWith('+' + DEFAULT_COUNTRY_CODE)) {
            this.setState({ code: data.dialCode, phone: value })
        } else if (ONLY_SINGLE_COUNTRY == 0) {
            this.setState({ code: data.dialCode, phone: value })
        } else {
            this.setState({ code: DEFAULT_COUNTRY_CODE, phone: '' })
        }
        this.setState({enableButton:true})
    }

    /**
     * @description handle OTP change and update state variable
     * @param OTP OTP entered by user
    */
    handleOtpChange = otp => {
        this.setState({ otp,
            isOtpCorrect: true });
    };

    /**
      * @description handle OTP change and update state variable 
      * same as above but called only in case of UC and Opera browser
      * @param OTP OTP entered by user
     */
    otpEnter = (evt) => {
        let mOtp = evt.target.value;
        this.setState({ otp: mOtp })
    }

    /**
    * @description This function send OTP to users mobile number
    */
    onSubmit = () => {
        if (!this.state.posting) {
            this.setState({ posting: true });
            let phone_code_str = "+" + this.state.code;
            let phone_no_str = this.state.phone;
            let phone_no = phone_no_str.replace(phone_code_str, "");
            if (!Utilities.isValidPhoneNumber(phone_no_str)) {
                Utilities.showToast(AppLabels.INVALID_PHONE_NUMBER, 2000, Images.MOBILE_ICON)
            }
            else {
                let param = {
                    "phone_no": phone_no,
                    "phone_code": this.state.code
                }
                editMobile(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson && responseJson.response_code === WSC.successCode) {
                        if (responseJson.data) {
                            this.setState({ isValidMobileNo: true, enableButton: false, showSendMsg: true }, () => {
                                setTimeout(() => {
                                    this.setState({ enableButton: true })
                                }, 1000 * 30);
                            })
                        }
                    }
                })
            }
        }
    }

    /**
  * @description This function verify OTP to users mobile number
  */
    varifyOTP = () => {
        let mOTP = this.state.otp;

        if (mOTP) {
            if (!this.state.posting) {
                this.setState({ posting: true });
                let phone_code_str = "+" + this.state.code;
                let phone_no_str = this.state.phone;
                let phone_no = phone_no_str.replace(phone_code_str, "");
                let param = {
                    "phone_no": phone_no,
                    "phone_code": this.state.code,
                    "otp": this.state.otp,
                }
                verifyEditedMobile(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson && responseJson.response_code === WSC.successCode) {
                        this.setState({
                            isOtpCorrect: true
                        })
                        if (responseJson.data) {
                            Utilities.showToast(AppLabels.YOUR_MOBILE_NUMBER_HAS_BEEN_UPDATED_SUCCUSSFULLY, 1000, Images.MOBILE_ICON)
                            this.props.IsEditMobileHide()
                        }
                    }
                    else {
                        this.setState({
                            isOtpCorrect: false
                        })
                    }
                })
            }
        }

    }

    render() {
        const { isOtpShown } = this.state;
        const { IsEditMobileShow, IsEditMobileHide, onHide } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={IsEditMobileShow}
                        onHide={onHide || IsEditMobileHide}
                        dialogClassName={"custom-modal edit-input-modal edit-input-modal-lg edit-mobile-no-modal" + (window.ReactNativeWebView ? " pb35" : '')}
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                {/* <i className="icon-mobile"></i> */}
                                <img src={Images.MOBILE_ICON_WHITE} alt="" width="20px"/>
                            </div>
                            <h2>{AppLabels.EDIT_MOBILE_NUMBER}</h2>
                            {this.state.mUserProfile.phone_no && <p>  {'+'}{this.state.mUserProfile.phone_code}{' '}{this.state.mUserProfile.phone_no}</p>}
                        </Modal.Header>
                        <Modal.Body>
                            <div className="edit-input-form edit-Mobile-form">
                                <Row>
                                    <Col xs={12} className="input-label-spacing z-idx-unset">
                                        <FormGroup
                                            className={'input-label-center input-transparent '}
                                            controlId="formBasicText">
                                            <Suspense fallback={<div />} >
                                                <CustomPhoneInput {...this.props} phone={this.state.phone} handleOnChange={this.handleOnChange} isFormLeft={true} isLabelHide={true} />
                                            </Suspense>
                                        </FormGroup>
                                        <div onClick={() => (this.state.enableButton && Utilities.isValidPhoneNumber(this.state.phone)) ? this.onSubmit() : ''} className={"button button-primary-rounded-sm input-action-btn "+((this.state.enableButton && Utilities.isValidPhoneNumber(this.state.phone))?"":" button-disabled")}>{AppLabels.SEND_OTP}</div>
                                        {this.state.showSendMsg &&
                                            <div className="email-sent-msg">
                                                {AppLabels.OTP_SENT_TO} {this.state.phone}
                                            </div>
                                        }
                                    </Col>
                                </Row>
                                <Row>
                                    <Col xs={12} className={'phone-number-style ' + (this.state.isValidMobileNo ? ' ' : ' disabled') + (!this.state.isOtpCorrect ? ' show-error-msg' : '')}>
                                        <div className="input-label">{AppLabels.ENTER_OTP}</div>
                                        {!this.checkBrowserISOpera() ?
                                            <div className="opt-block">
                                                {
                                                    isOtpShown &&
                                                    <OtpInput
                                                        autoComplete='off'
                                                        shouldautofocus={true}
                                                        containerStyle="otp-inputs otp-inputs-sm"
                                                        onChange={this.handleOtpChange}
                                                        value={this.state.otp}
                                                        numInputs={4} 
                                                        isDisabled={!this.state.isValidMobileNo}
                                                        isInputNum={true}

                                                    />
                                                }
                                            </div>
                                            :
                                            <FormGroup className='input-label-center' controlId="formBasicText" >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyle}
                                                    id='otp'
                                                    maxLength={4}
                                                    name='otp'
                                                    placeholder={AppLabels.ENTER_OTP}
                                                    type='text'
                                                    onChange={this.otpEnter}
                                                />
                                            </FormGroup>
                                        }
                                        <div className="error-text">{AppLabels.WRONG_OTP}</div>
                                    </Col>
                                </Row>
                                <div onClick={() => this.varifyOTP()} className={"button button-primary button-block btm-fixed" + ((this.state.otp && Utilities.isValidPhoneNumber(this.state.phone)) ? ' ' : ' disabled')}>{AppLabels.VERIFY_AND_UPDATE}</div>
                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}