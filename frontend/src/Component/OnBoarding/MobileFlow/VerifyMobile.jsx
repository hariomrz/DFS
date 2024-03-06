import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import { Utilities, _handleWKeyDown, _isUndefined, headerProfileUpdate, isDesktop } from '../../../Utilities/Utilities';
import { inputStyle } from '../../../helper/input-style';
import ls from 'local-storage';
import FloatingLabel from 'floating-label-react';
import OtpInput from 'react-otp-input';
import WSManager from "../../../WSHelper/WSManager";
import Countdown from 'react-countdown-now';
import MetaData from "../../../helper/MetaData";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { setValue, OTPSIZE ,AppSelectedSport} from '../../../helper/Constants';
import { validatePhoneOTP, resendPhoneOTP } from '../../../WSHelper/WSCallings';
import Images from '../../../components/images';
import { autoReadSMS } from './autoReadSMS';
import { withRedux } from 'ReduxLib';

class VerifyMobile extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            otp: '',
            mData: '',
            posting: false,
            dateNow: Date.now(),
            isCompleted: false,
            isOtpShown: true,
        };
        this.desktopObject = isDesktop()
    }

    getCurrentDateTime = () => {
        var date = new Date().getDate(); //To get the Current Date
        var month = new Date().getMonth() + 1; //To get the Current Month
        var year = new Date().getFullYear(); //To get the Current Year
        var hours = new Date().getHours(); //To get the Current Hours
        var min = new Date().getMinutes(); //To get the Current Minutes
        var sec = new Date().getSeconds(); //To get the Current Seconds

        var cDate =  year + '/' + month + '/' + date
        + ' ' + hours + ':' + min + ':' + sec
        return  cDate
        
    
      }

    UNSAFE_componentWillMount() {
        const _this = this
        Utilities.setScreenName('verifymobile')
        document.addEventListener("keydown", _handleWKeyDown, false);
        window.addEventListener('message', (e) => {
          if (e.data.action === 'otpUser') {
            _this.setState({ otp: e.data.otp });
          } 
        });
        autoReadSMS()
    }

    componentWillUnmount() {
        document.removeEventListener("keydown", _handleWKeyDown);
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
        const { user_profile, flow_type } = this.props.location.state.nextStepData.data;
        const { facebook_data, google_data,  } = this.props.location.state.nextStepData;
        let device_id = WSC.DeviceToken.getDeviceId();
        let param = {
            "otp": this.state.otp,
            "phone_no": user_profile.phone_no,
            "facebook_access_token": facebook_data ? facebook_data.accessToken : '',
            "facebook_id": facebook_data ? facebook_data.id : '',
            "google_access_token": google_data ? google_data.tokenId : '',
            "google_id": google_data ? google_data.googleId : '',
            "device_type": Utilities.getDeviceType(),
            "device_id": device_id,
        }
        if(window.ReactNativeWebView){
            param['install_date'] = this.getCurrentDateTime()
        }
        if(WSManager.getAffiliatCode()){
            param['affcd'] = WSManager.getAffiliatCode();
        }
        validatePhoneOTP(param).then((responseJson) => {
            const { is_desktop = false } = this.desktopObject;
            this.setState({ posting: false });
            Utilities.gtmEventFire('onboarding_flow', {
                flow_type: flow_type,
                screen_no: '2'
            })
            if (responseJson.response_code === WSC.successCode) {

                if(process.env.REACT_APP_SINGULAR_ENABLE > 0)
                {
                    let singular_data = {};
                    singular_data.user_unique_id = responseJson.data.user_profile.user_unique_id;
                    singular_data.phone_no = responseJson.data.user_profile.phone_no;
                    singular_data.user_name = responseJson.data.user_profile.user_name;
                    singular_data.email = responseJson.data.user_profile.email;

                    if (window.ReactNativeWebView)
                    {
                        let data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Login_completed',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(data));
                    }
                    else
                    {
                        window.SingularLogin(responseJson.data.user_profile.user_unique_id);
                        window.SingularEvent("Login_completed", singular_data);
                    }
                }
                
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
                    setTimeout(() => {
                        headerProfileUpdate()
                    }, 10)
                    this.props.actions.setAuth(true)
                    if (window.ReactNativeWebView) {
                        let data = {
                            action: 'login',
                            attribute: responseJson.data.user_profile,
                            targetFunc: 'login'
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(data));
                    }
                    Utilities.gtmEventFire('login', {
                        'user_id': responseJson.data.user_profile.user_unique_id,
                        'user_name': responseJson.data.user_profile.user_name
                    }, true)
                    let { lineupPath, FixturedContest, LobyyData, joinContest , sportsId,isReverseF, isSecIn, isPlayingAnnounced} = this.props.location.state.nextStepData;
                     if (joinContest) {
                        this.props.history.push({
                            pathname: lineupPath,
                            state: is_desktop ? {
                                contestData: LobyyData,
                                fixtureData: FixturedContest
                            } : {
                                FixturedContest: FixturedContest, LobyyData: LobyyData, resetIndex: 2, isReverseF : isReverseF || false, isSecIn: isSecIn,isShare: true, isPlayingAnnounced
                            }
                        })
                        let sportId = FixturedContest.sports_id ? FixturedContest.sports_id : sportsId ;
                        if(!_isUndefined(sportId)) {
                            ls.set('selectedSports', sportId);
                        }
                        setValue.setAppSelectedSport(sportId);
                    } else {
                        if(responseJson.data.next_step == "login_success"){
                            this.gotoDetails("/lobby", responseJson.data);
                        }else{
                            this.gotoDetails("/", responseJson.data);
                        }
                    }
                } else {
                    this.gotoDetails("/" + responseJson.data.next_step, responseJson.data);
                }
                if(Utilities.getMasterData().bs_a == 1) {
                    const { is_desktop = false } = this.desktopObject;
                    if(!is_desktop) {
                        this.props.navigatorCheck(true);
                    }
                }
            }
            else{
                Utilities.showToast(responseJson.message, 3000);
            }

            if(Utilities.getMasterData().bs_a == 1){
                if(!is_desktop) {
                    this.props.navigatorCheck(true);
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
            this.setState({ posting: true });
            const { data } = this.props.location.state.nextStepData;
            let param = {
                "phone_no": data.user_profile.phone_no,
                "phone_code": data.user_profile.phone_code,
                "device_type": Utilities.getDeviceType(),
                "device_id": WSC.deviceID,
                "otp_hash": ls.get('otp_hash')
            }
            resendPhoneOTP(param).then((responseJson) => {
                this.setState({ posting: false });
                if (responseJson.response_code === WSC.successCode) {
                    this.setState({ dateNow: Date.now() })
                    this.setState({ isCompleted: false, otp: '', isOtpShown: false }, () => {
                        this.setState({ isOtpShown: true })
                    })
                    Utilities.gtmEventFire('otp_sent')
                    Utilities.showToast(responseJson.message, 5000, Images.MOBILE_ICON);
                }
            })
        }
    }

    /**
      * @description This function responsible for Navigate to next step after mobile verification
    */
    gotoDetails = (path, data) => {
        let { lineupPath, FixturedContest, LobyyData, facebook_data, google_data,isReverseF, isSecIn, isPlayingAnnounced } = this.props.location.state.nextStepData;
        let nextStepData = {
            data,
            facebook_data: facebook_data,
            google_data: google_data,
            FixturedContest: FixturedContest,
            LobyyData: LobyyData,
            lineupPath: lineupPath,
            isReverseF: isReverseF || false,
            isSecIn: isSecIn,
            isPlayingAnnounced
        };
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

    getCurrentDateTime = () => {
        var date = new Date().getDate(); //To get the Current Date
        var month = new Date().getMonth() + 1; //To get the Current Month
        var year = new Date().getFullYear(); //To get the Current Year
        var hours = new Date().getHours(); //To get the Current Hours
        var min = new Date().getMinutes(); //To get the Current Minutes
        var sec = new Date().getSeconds(); //To get the Current Seconds

        var cDate =  year + '/' + month + '/' + date
        + ' ' + hours + ':' + min + ':' + sec
        return  cDate
        
      
    
      }
    

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
        const { data } = this.props.location.state.nextStepData;
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
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages" id='verifyMobileForm'>
                            <div className="verification-block">
                                <div className="fixed-ht">
                                    <Row>
                                        <Col>
                                            <div className="onboarding-page-heading">
                                                {AppLabels.VERIFY_MOBILE}
                                            </div>
                                            <div className="onboarding-page-desc">
                                            {AppLabels.VERIFY_MOBILE_TEXT + OTPSIZE + AppLabels.VERIFY_MOBILE_TEXT1}
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row>
                                        <Col xs={12} className="text-left">
                                            {
                                                isCompleted ?
                                                    <div className="link-txt " onClick={() => this.ResendOtp()}>
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
                                            {
                                                !this.checkBrowserISOpera() ?
                                                    <div className="opt-block">
                                                        {
                                                            isOtpShown &&
                                                            <OtpInput
                                                                autoComplete='off'
                                                                shouldAutoFocus={true}
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
                                <Row className="btm-fixed-submit btm-fixed-submit2">
                                    <Col xs={12} className="text-center">
                                        <button className="submit-otp m-b-15" disabled={!otp || posting} type='submit'>
                                            <i className="icon-next-btn" />
                                        </button>
                                    </Col>
                                </Row>
                                <Row>
                                    <Col xs={12}>
                                        <div className="txt-verify-no">
                                            <p>{AppLabels.OTP_SENT_TO}</p>
                                            <span>+{data.user_profile.phone_code} {data.user_profile.phone_no} </span>
                                            <span className="link-icon" onClick={this.goBack}><i className="icon-edit-line" /></span>
                                        </div>
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

export default withRedux(VerifyMobile)