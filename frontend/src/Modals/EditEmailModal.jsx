import React from 'react';
import { Modal, FormGroup, Row, Col } from 'react-bootstrap';
import * as AppLabels from "../helper/AppLabels";
import { MyContext } from '../InitialSetup/MyProvider';
import FloatingLabel from 'floating-label-react';
import { inputStyleLeft,darkInputStyleLeft } from '../helper/input-style';
import { inputStyle } from '../helper/input-style';
import OtpInput from 'react-otp-input';
import Validation from '../helper/Validation';
import { editEmail, verifyEditedEmail } from '../WSHelper/WSCallings';
import { Utilities } from '../Utilities/Utilities';
import * as WSC from "../WSHelper/WSConstants";
import WSManager from '../WSHelper/WSManager';
import Images from '../components/images';
import {DARK_THEME_ENABLE} from "../helper/Constants";

export default class EditEmailModal extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            otp: '',
            isOtpShown: true,
            newEmail: this.props.isVerifyMode ? (this.props.email ? this.props.email : '') : '',
            enableButton: true,
            isValidEmail: false,
            isOtpCorrect: true,
            enteredEmailValid: false
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

    /**
     * @description handle email change and update state variable
     * @param e click event
     */
    onHandleChange = (e) => {
        const value = e.target.value;
        this.setState({
            newEmail: value,
            otp: '',
            enteredEmailValid: (Validation.validate('email', value) == 'success')
        });
    }

    /**
     * @description handle OTP change and update state variable
     * @param OTP OTP entered by user
    */
    handleOtpChange = otp => {
        this.setState({
            otp,
            isOtpCorrect: true
        });
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


    UNSAFE_componentWillMount() {
        if (this.props.email) {
            this.setState({
                enteredEmailValid: (Validation.validate('email', this.props.email) == 'success')
            })
        }
    }


    /**
   * @description This function send OTP to users email
   */
    onSubmit = () => {
        if (!this.state.posting) {
            this.setState({ posting: true });
            if (this.state.newEmail == '' && Validation.validate('email', this.state.newEmail) == 'error') {
                Utilities.showToast(AppLabels.INVALID_EMAIL_ID, 2000, Images.EMAIL_ICON)
            }
            else {
                let param = {
                    "email": this.state.newEmail,
                }
                editEmail(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson && responseJson.response_code == WSC.successCode) {
                        this.setState({ isValidEmail: true, enableButton: false }, () => {
                            setTimeout(() => {
                                this.setState({ enableButton: true })
                            }, 1000 * 30);
                        })
                    }
                })
            }
        }
    }

    /**
    * @description This function verify OTP to users email
    */
    varifyOTP = () => {
        let mOTP = this.state.otp;

        if (mOTP != '') {
            if (!this.state.posting) {
                this.setState({ posting: true });
                let param = {
                    "email": this.state.newEmail,
                    "otp": this.state.otp,
                }
                verifyEditedEmail(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson && responseJson.response_code === WSC.successCode) {
                        this.setState({
                            isOtpCorrect: true
                        })
                        if (responseJson.data) {
                            let mProfile = WSManager.getProfile();
                            mProfile.email = this.state.newEmail;
                            WSManager.setProfile(mProfile)
                            this.props.IsEditEmailHide()
                            if (this.props.isVerifyMode) {
                                this.props.history.goBack()
                            }
                        }
                        Utilities.showToast(responseJson.message, 1000, Images.EMAIL_ICON);
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
        const { isOtpShown, otp } = this.state;
        const { IsEditEmailShow, IsEditEmailHide } = this.props;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <Modal
                        show={IsEditEmailShow}
                        onHide={IsEditEmailHide}
                        dialogClassName={"custom-modal edit-input-modal edit-input-modal-lg edit-mobile-no-modal" + (window.ReactNativeWebView ? " pb35" : '')}
                        className="center-modal"
                    >
                        <Modal.Header>
                            <div className="icon-section">
                                <img src={DARK_THEME_ENABLE ? Images.EMAIL_ICON : Images.EMAIL_ICON_WHITE} alt="" width="34px" />
                            </div>
                            <h2>{this.props.isVerifyMode ? AppLabels.VERIFY_EMAIL_ADDRESS : AppLabels.EDIT_EMAIL_ADDRESS}</h2>
                            {!this.props.isVerifyMode && <p>{this.props.email}</p>}

                        </Modal.Header>
                        <Modal.Body>
                            <div className="edit-input-form edit-email-form">
                                <Row>
                                    <Col xs={12} className="input-label-spacing input-with-btn">
                                        <FormGroup
                                            className={'input-label-center input-transparent '}
                                            controlId="formBasicText">
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='emailId'
                                                name='emailId'
                                                placeholder={AppLabels.EMAIL_ADDRESS}
                                                type='text'
                                                value={this.state.newEmail}
                                                onChange={this.onHandleChange}
                                            />
                                        </FormGroup>
                                        <div onClick={() => (this.state.enableButton && this.state.enteredEmailValid && this.state.newEmail) ? this.onSubmit() : ''} className={"button button-primary-rounded-sm input-action-btn " + ((this.state.enableButton && this.state.enteredEmailValid && this.state.newEmail) ? "" : " button-disabled")}>{AppLabels.SEND_OTP}</div>
                                        <div className={"email-sent-msg" + (this.state.isValidEmail ? '' : ' hide')}>
                                            {AppLabels.OTP_SENT_TO} {this.state.newEmail}
                                        </div>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col xs={12} className={'phone-number-style ' + (this.state.isValidEmail ? ' ' : ' disabled') + (!this.state.isOtpCorrect ? ' show-error-msg' : '')}>
                                        <div className="input-label">{AppLabels.ENTER_OTP}</div>
                                        {!this.checkBrowserISOpera() ?
                                            <div className="opt-block">
                                                {
                                                    isOtpShown &&
                                                    <OtpInput
                                                        autoComplete='off'
                                                        shouldautofocus={true}
                                                        containerStyle="otp-inputs otp-inputs-sm"
                                                        value={otp}
                                                        onChange={this.handleOtpChange}
                                                        numInputs={4}
                                                        isDisabled={!this.state.isValidEmail}
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
                                                    value={otp}
                                                    onChange={this.otpEnter}
                                                />
                                            </FormGroup>
                                        }
                                        <div className="error-text">{AppLabels.WRONG_OTP}</div>
                                    </Col>
                                </Row>
                                <div onClick={() => this.varifyOTP()} className={"button button-primary button-block btm-fixed " +
                                    (this.state.otp == '' ? ' disabled' : '')}
                                // (this.state.otp.trim()==''?' disabled':'' )}
                                >{AppLabels.VERIFY_AND_UPDATE}</div>

                            </div>
                        </Modal.Body>
                    </Modal>

                )}
            </MyContext.Consumer>
        );
    }
}