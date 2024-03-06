import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities } from '../../../Utilities/Utilities';
import { inputStyle } from '../../../helper/input-style';
import ls from 'local-storage';
import FloatingLabel from 'floating-label-react';
import OtpInput from 'react-otp-input';
import WSManager from "../../../WSHelper/WSManager";
import Countdown from 'react-countdown-now';
import MetaData from "../../../helper/MetaData";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { setValue, OTPSIZE } from '../../../helper/Constants';
import { validateEmailOTP, resendEmailOTP } from '../../../WSHelper/WSCallings';
import Images from '../../../components/images';


export default class VerifyEmail extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            otp: '',
            posting: false,
            dateNow: Date.now(),
            isCompleted: false,
            isOtpShown: true,
        };
    }

    UNSAFE_componentWillMount() {
        document.addEventListener("keydown", this._handleKeyDown, false);
        Utilities.setScreenName('verifymobile')
    }

    componentWillUnmount() {
        document.removeEventListener("keydown", this._handleKeyDown);
    }

    _handleKeyDown = (event) => {

        const BACKSPACE = 8;
        const LEFT_ARROW = 37;
        const RIGHT_ARROW = 39;
        const DELETE = 46;
        const ENTER = 13;

        var isValidKey = event.keyCode === ENTER || event.keyCode === BACKSPACE || event.keyCode === LEFT_ARROW || event.keyCode === RIGHT_ARROW || event.keyCode === DELETE;
        if (this && event.target instanceof HTMLInputElement) {
            const regex = /^[0-9\b]+$/;
            if (event.key !== '' && !regex.test(event.key) && !isValidKey) {
                event.preventDefault();
            }
        }
    }


    /**
     * @description handle OTP change and update state variable
     * @param OTP OTP entered by user
    */
    handleOtpChange = otp => {
        const regex = /^[0-9\b]+$/;
        if (otp === '' || regex.test(otp)) {
            this.setState({ otp });
        }
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
      * @description This function verify OTP entered by user
      * @param e- click event
    */
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        const { facebook_data, google_data, email, data } = this.props.location.state.nextStepData;
        let device_id = WSC.DeviceToken.getDeviceId();
        let param = {
            "otp": this.state.otp,
            "email": email,
            "facebook_access_token": facebook_data ? facebook_data.accessToken : '',
            "facebook_id": facebook_data ? facebook_data.id : '',
            "google_access_token": google_data ? google_data.getAuthResponse().id_token : '',
            "google_id": google_data ? google_data.getId() : '',
            "device_type": Utilities.getDeviceType(),
            "device_id": device_id,
        }
        if (WSManager.getAffiliatCode()) {
            param['affcd'] = WSManager.getAffiliatCode();
        }
        validateEmailOTP(param).then((responseJson) => {
            this.setState({ posting: false });
            if (responseJson.response_code === WSC.successCode) {


                WSManager.googleTrack(WSC.GA_PROFILE_ID, 'login');

                if (device_id && device_id !== '') {
                    ls.set('isDeviceTokenUpdated', true);
                }

                WSManager.setTempToken(responseJson.data.Sessionkey);
                WSManager.setCookie('_id', responseJson.data.user_profile.user_unique_id)
                WSManager.setCookie('_nm', responseJson.data.user_profile.user_name)

                if (responseJson.data.is_profile_complete === 1) {
                    WSManager.setProfile(responseJson.data.user_profile);
                    WSManager.setToken(responseJson.data.Sessionkey);
                    let { lineupPath, FixturedContest, LobyyData, joinContest, isSecIn, isPlayingAnnounced } = this.props.location.state.nextStepData;
                    if (joinContest) {
                        this.props.history.push({
                            pathname: lineupPath,
                            state: {
                                FixturedContest: FixturedContest, LobyyData: LobyyData, resetIndex: 2, isSecIn: isSecIn, isPlayingAnnounced
                            }
                        })
                        ls.set('selectedSports', FixturedContest.sports_id);
                        setValue.setAppSelectedSport(FixturedContest.sports_id);
                    } else {
                        this.gotoDetails("/", responseJson.data);
                    }
                } else {
                    this.gotoDetails("/" + responseJson.data.next_step, responseJson.data);
                }
            }
        })
    }

    /**
      * @description This function responsible for go back to previous screen
    */
    goBack = () => {
        this.props.history.goBack();
    }

    /**
       * @description This function responsible for resend OTP in case use click on Resend button
     */
    ResendOtp = () => {
        if (!this.state.posting) {
            const { email } = this.props.location.state.nextStepData;
            let param = {
                "email": email,
                "device_type": Utilities.getDeviceType(),
                "device_id": WSC.deviceID,
            }
            this.setState({ posting: true });
            resendEmailOTP(param).then((responseJson) => {
                this.setState({ posting: false });
                if (responseJson.response_code === WSC.successCode) {
                    this.setState({ dateNow: Date.now() })
                    this.setState({ isCompleted: false, otp: '', isOtpShown: false }, () => {
                        this.setState({ isOtpShown: true })
                    })
                    Utilities.showToast(responseJson.message, 5000, Images.EMAIL_ICON);
                }
            })
        }
    }

    /**
      * @description This function responsible for Navigate to next step after mobile verification
    */
    gotoDetails = (path, data) => {
        let { lineupPath, FixturedContest, LobyyData, facebook_data, google_data, joinContest, isReverseF } = this.props.location.state.nextStepData;
       
        if (path === '/create-account') {
            path = '/create-account'
        }
        let nextStepData = {
            data:data,
            facebook_data: facebook_data,
            google_data: google_data,

            
            FixturedContest: FixturedContest,
            LobyyData: LobyyData,
            lineupPath: lineupPath,
            next_step: path,
            joinContest:joinContest,
            phone_no: data.user_profile.phone_no,
            isReverseF: isReverseF
        }
        this.props.history.push({
            pathname: path,
            state: {
                nextStepData: nextStepData
            }
        })
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
      * @description Used for Timer display
      * @param minutes remaining minutes
      * @param seconds remaining seconds
      * @param completed if completed timer then display resend button
    */
    renderer = ({ minutes, seconds, completed }) => {
        if (completed) {
            this.setState({ isCompleted: completed })
            return false;
        } else {
            return (
                <span className="timer-resend">
                    <small>{AppLabels.RESEND_IN} </small>
                    {minutes}:{seconds}
                </span>
            );
        }
    };

    /**
     * @description Render UI component
    */
    render() {
        const {
            otp,
            dateNow,
            isCompleted,
            posting,
            isOtpShown
        } = this.state;
        const { data, email } = this.props.location.state.nextStepData;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container bg-white p-0 verify-otp">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.verifymobile.title}</title>
                            <meta name="description" content={MetaData.verifymobile.description} />
                            <meta name="keywords" content={MetaData.verifymobile.keywords}></meta>
                        </Helmet>
                        <div className="registration-header header-wrap">
                            <Row>
                                <Col xs={12} className="text-right">
                                    <span className="header-action" onClick={this.goBack}>
                                        <i className="icon-close" />
                                    </span>
                                </Col>
                            </Row>
                        </div>
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages" id='verifyEmailForm'>
                            <div className="verification-block">
                                <div className="fixed-ht">
                                    <Row>
                                        <Col>
                                            <div className="onboarding-page-heading">
                                                {AppLabels.VERIFY_EMAIL}
                                            </div>
                                            <div className="onboarding-page-desc">
                                                {AppLabels.PASSWORD_TEXT + OTPSIZE + AppLabels.PASSWORD_TEXT1 + AppLabels.PASSWORD_TEXT2}
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col xs={12} className="text-left">
                                            {
                                                isCompleted ?
                                                    <div className="link-txt" onClick={() => this.ResendOtp()}>
                                                        <i className="icon-stop-watch"></i>
                                                        <span>{AppLabels.RESEND}</span>
                                                    </div>
                                                    :
                                                    <div>
                                                        <i className="icon-stop-watch"></i>
                                                        <Countdown date={dateNow + 30000} renderer={this.renderer} />
                                                    </div>
                                            }
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col xs={12} className='phone-number-style registered-otp-block'>
                                            {!this.checkBrowserISOpera() ?
                                                <div className="opt-block">
                                                    {
                                                        isOtpShown &&
                                                        <OtpInput
                                                            autoComplete='off'
                                                            shouldautofocus={true}
                                                            containerStyle={"otp-inputs" + (OTPSIZE > 4 ? ' otp-inputs-xsm' : '')}
                                                            value={otp}
                                                            onChange={this.handleOtpChange}
                                                            numInputs={OTPSIZE}
                                                        />
                                                    }
                                                </div>
                                                :
                                                <FormGroup className='input-label-center' controlId="formBasicText" >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={inputStyle}
                                                        id='otp'
                                                        maxLength={OTPSIZE}
                                                        name='otp'
                                                        placeholder={AppLabels.ENTER_OTP}
                                                        type='text'
                                                        value={otp}
                                                        onChange={this.otpEnter}
                                                    />
                                                </FormGroup>
                                            }
                                        </Col>
                                    </Row>
                                </div>
                                <Row>
                                    <Col xs={12} className="text-center btm-fixed-submit btm-fixed-submit2">
                                        <button className="submit-otp m-b-15" disabled={!otp || posting} type='submit'>
                                            <i className="icon-next-btn" />
                                        </button>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col xs={12}>
                                        <p className="txt-verify-no">
                                            <p>{AppLabels.OTP_SENT_TO}</p>
                                            <span> {email ? email : (data && data.user_profile) && data.user_profile.phone_no} </span>
                                            <span className="link-icon" onClick={this.goBack}><i className="icon-edit-line" /></span>
                                        </p>
                                    </Col>
                                </Row>
                            </div>
                        </form>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}