import React, { Suspense, lazy } from 'react';
import { Row, Col, Button, FormGroup, OverlayTrigger, Tooltip, Checkbox } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import { userLogin, socialLogin, getBannedStats } from "../../../WSHelper/WSCallings";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Utilities, checkFlow, _isUndefined, sendMessageToApp, _isObject } from '../../../Utilities/Utilities';
import { inputStyle } from '../../../helper/input-style';
import { createBrowserHistory } from 'history';
import FloatingLabel from 'floating-label-react';
import ls from 'local-storage';
import Validation from '../../../helper/Validation';
import WSManager from "../../../WSHelper/WSManager";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import Images from '../../../components/images';
import * as AppLabels from "../../../helper/AppLabels";
import * as WSC from "../../../WSHelper/WSConstants";
import * as Constants from "../../../helper/Constants";
const SelectLanguage = lazy(() => import('../../CustomComponent/SelectLanguage'));
const GoogleLogin = lazy(() => import('../../CustomComponent/GoogleLogin'));
const FacebookLogin = lazy(() => import('../../CustomComponent/FacebookLogin'));
const ReactCaptcha = lazy(() => import('../../CustomComponent/ReactCaptcha'));

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

export default class EmailLogin extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            email: '',
            formValid: false,
            posting: false,
            allowLanguage: Constants.ALLOW_LANG,
            captchaToken: '',
            isLoading: false,
            isChecked: false

        };
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('emaillogin')

        let data = {
            action: 'back',
            targetFunc: 'back',
            type: true,
        }
        sendMessageToApp(data)
        setTimeout(() => {
            let token_data = {
                action: 'push',
                targetFunc: 'push',
                type: 'deviceid',
            }
            sendMessageToApp(token_data)
        }, 300);
        if (!_isUndefined(parsed) && parsed.referral !== "" && parsed.referral !== null && !_isUndefined(parsed.referral)) {
            WSManager.setReferralCode(parsed.referral)
        }
        if (!_isUndefined(parsed) && parsed.affcd) {
            WSManager.setAffiliatCode(parsed.affcd)
        }
    }

    componentDidMount() {
        if (!window.ReactNativeWebView && Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == '1') {
            this.getUserLatLongWeb()
        } else if (window.ReactNativeWebView && Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == '1') {
            Utilities.setLocationStatusToApp()
        }
        if (WSManager.getIsIOSApp()) {
            let def_lang = WSManager.getAppLang()
            if (window.ReactNativeWebView) {
                let data = {
                    action: 'back',
                    locale: def_lang,
                    targetFunc: 'handleLogoutReceived'
                }
                window.ReactNativeWebView.postMessage(JSON.stringify(data));
            }
        }
        if (window.ReactNativeWebView) {
            this.handelNativeGoogleLogin()
        }
        this.getBannedStateList();
    }

    componentWillUnmount() {
        let data = {
            action: 'back',
            targetFunc: 'back',
            type: false,
        }
        sendMessageToApp(data)
    }

    handelNativeGoogleLogin() {
        window.addEventListener('message', (e) => {
            if (e.data.action === 'login' && e.data.type === 'google') {
                let profileObj = {
                    email: e.data.response.user && e.data.response.user.email ? e.data.response.user.email : e.data.response.email
                };
                let googleUser = {
                    'tokenId': e.data.response.auth ? e.data.response.auth.idToken : e.data.response.idToken,
                    'googleId': e.data.response.user ? e.data.response.user.uid : e.data.response.uid,
                    'profileObj': profileObj
                };
                this.responseGoogle(googleUser, true)
            }
            else if (e.data.action === 'login' && e.data.type === 'facebook') {
                let fbUser = JSON.parse(e.data.response._bodyText);

                let user = {
                    'email': fbUser.email ? fbUser.email : '',
                    'accessToken': e.data.response.token,
                    'id': fbUser.id
                };
                this.onFacebookSuccess({ _profile: user, _token: user.accessToken })
            }
            else if (e.data.action === 'push' && e.data.type === 'deviceid') {
                WSC.DeviceToken.setDeviceId(e.data.token);
            }
            else if (e.data.action === 'latLong' && e.data.type === 'deviceLatLong') {
                this.setUserLatLongTrigerDuration(e.data)
            }

        });
    }

    getUserLatLongWeb = () => {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                let data = {
                    'lat': position.coords.latitude,
                    'longi': position.coords.longitude,
                };
                this.setUserLatLongTrigerDuration(data)
            }, (error) => {
                ls.set('encodedLatLong', 0)

            }, {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 1000
            });
        } else {
            console.log("Not Available");
        }
    }

    setUserLatLongTrigerDuration = (data) => {
        var currentTime = Math.round((new Date()).getTime() / 1000);
        let latlongtimeMain = ls.get('latlongtimeMain');
        console.log('latlongtimeMain', latlongtimeMain);
        console.log('currentTimeTriggir', currentTime);
        if (latlongtimeMain == null) {
            let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
            var mininmilsecond = parseInt(nextTrigerTime) * 60;
            var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
            console.log('expiredTimeElse', expiredTime);
            ls.set('latlongtimeMain', expiredTime)
            let latlong = data.lat + ',' + data.longi
            var encodedData = btoa(latlong)
            WSC.UserLatLong.setLatLONG(encodedData);
            ls.set('encodedLatLong', encodedData)

        }
        else if (parseFloat(currentTime) > parseFloat(latlongtimeMain)) {
            let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
            var mininmilsecond = parseInt(nextTrigerTime) * 60;
            var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
            console.log('expiredTimeElse', parseInt(currentTime) + parseInt(mininmilsecond));
            ls.set('latlongtimeMain', expiredTime)
            let latlong = data.lat + ',' + data.longi
            var encodedData = btoa(latlong)
            WSC.UserLatLong.setLatLONG(encodedData);
            ls.set('encodedLatLong', encodedData)


        }
    }

    /**
     * @description check validation of user entered email with local regex
     * @param type email for this screen
     * @param value user entered value
     */
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }

    /**
     * @description handle email change and update state variable
     * @param e click event
     */
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value }, this.validateForm);
    }

    /**
     * @description manage form validations
     */
    validateForm() {
        this.setState({ formValid: Validation.validate('email', this.state.email) == 'success' });
    }

    /**
     * @description this method update user email to server
     * @param e- click event
     * after success navigate to next step
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        let device_id = window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID

        let param = {
            "email": this.state.email,
            "device_type": Utilities.getDeviceType(),
            "device_id": device_id,
            ...Utilities.getCpSession()
        }
        if (process.env.REACT_APP_CAPTCHA_ENABLE == 1) {
            param['token'] = this.state.captchaToken;
        }
        userLogin(param).then((responseJson) => {
            this.setState({ posting: false });
            if (responseJson.response_code == WSC.successCode) {
                if (responseJson.data && responseJson.data.Sessionkey) {
                    WSManager.setTempToken(responseJson.data.Sessionkey);
                }
                let flow_type = ''
                if (responseJson.data && responseJson.data.is_user_exist == 0) {
                    flow_type = 'signup'
                } else {
                    flow_type = 'login'
                }
                this.gotoDetails(responseJson.data, flow_type)
            } else {
                Utilities.showToast(responseJson.global_error, 5000);
            }
        })
        Utilities.setDefaultSport();
    }

    /**
     * @description This function responsible for Navigate to next step after update email
     */
    gotoDetails = (data, flow_type) => {
        data['flow_type'] = flow_type;
        let nextStepData = { data: data || '' }
        if (this.props.location.state && this.props.location.state.joinContest) {
            let { facebook_data, google_data, lineupPath, FixturedContest, LobyyData, isReverseF, isSecIn, isPlayingAnnounced } = this.props.location.state;
            let { email } = this.state;
            nextStepData = { joinContest: this.props.location.state.joinContest || '', data: data, FixturedContest: FixturedContest, LobyyData: LobyyData, lineupPath: lineupPath, facebook_data: facebook_data, google_data: google_data, email: email, isReverseF: isReverseF, isSecIn: isSecIn, isPlayingAnnounced: isPlayingAnnounced };


            if (data.next_step === 'otp') {
                data['next_step'] = 'verify';
            }
            else {
                data['next_step'] = 'password';
            }
        }
        else {
            let { email } = this.state;
            if (data.next_step === 'otp') {
                data['next_step'] = 'verify';
            }
            else {
                data['next_step'] = 'password';
            }
            nextStepData = { data: data, email: email };
        }
        this.props.history.push(checkFlow(nextStepData))
    }

    onFacebookSuccess = ({ _profile, _token }) => {
        let user = { ..._profile, ..._token }


        if (user) {
            localStorage.setItem('facebook_id', user.id)
            localStorage.setItem('facebook_access_token', user.accessToken)
            this.setState({ posting: true });
            let param = {
                "email": user.email,
                "facebook_id": user.id,
                "facebook_access_token": user.accessToken,
                "password": '',
                "device_type": Utilities.getDeviceType(),
                "device_id": WSC.DeviceToken.getDeviceId(),
                ...Utilities.getCpSession()
            }
            socialLogin(param).then((responseJson) => {
                if (responseJson && responseJson.response_code === WSC.successCode) {
                    WSManager.setTempToken(responseJson.data.Sessionkey);
                    if (responseJson.data.next_step === 'set_password') {
                        responseJson.data.next_step = 'set-password'
                    }
                    let nextStepData = { data: responseJson.data, facebook_data: user, google_data: null, next_step: responseJson.data.next_step, isReverseF: this.props.location.state.isReverseF };
                    this.props.history.push(checkFlow(nextStepData))
                }
                this.setState({ posting: false });
            })
        }
    }

    /**
     * @description FB failure callback
     * @param err error received from FB api
    */
    onLoginFailure(err) {
    }

    responseGoogle = (googleUser, isSuccess) => {
        if (googleUser && isSuccess) {
            this.setState({ posting: true });
            var id_token = googleUser.tokenId;
            var googleId = googleUser.googleId;
            localStorage.setItem('google_id', googleId)
            localStorage.setItem('google_access_token', id_token)
            let param = {
                "email": googleUser.profileObj.email ? googleUser.profileObj.email : '',
                "google_id": googleId,
                "google_access_token": id_token,
                "password": '',
                "device_type": Utilities.getDeviceType(),
                "device_id": window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID,
                ...Utilities.getCpSession()
            }

            socialLogin(param).then((responseJson) => {
                if (responseJson.response_code === WSC.successCode) {
                    WSManager.setTempToken(responseJson.data.Sessionkey);
                    if (responseJson.data.next_step === 'set_password') {
                        responseJson.data.next_step = 'set-password'
                    }
                    let nextStepData = { data: responseJson.data, facebook_data: null, google_data: googleUser, next_step: responseJson.data.next_step, isReverseF: this.props.location.state.isReverseF };
                    this.props.history.push(checkFlow(nextStepData))
                }
                this.setState({ posting: false });
            })
        }
    }

    isAndroidApp() {
        if (navigator.userAgent.toLowerCase().match(/(android-app)/)) {
            return true;
        }
        return false;
    }

    appNativeLogin(type) {
        let data = {
            action: 'login',
            type: type,
        }
        sendMessageToApp(data)
    }

    onCaptchaChange = (value) => {
        this.setState({
            captchaToken: value
        })
    }

    getBannedStateList() {
        this.setState({
            isLoading: true
        })
        let bsList = ls.get('bslist');
        let bslistTime = ls.get('bslistTime');
        let minuts = bslistTime ? Utilities.minuteDiffValue(bslistTime) : 0;
        let hours = Math.floor(minuts / 60);
        if (bsList && hours < 2) {
            let Data = Utilities.getMasterData() || {};
            Data['banned_state'] = bsList;
            let banStates = Object.keys(Data.banned_state || {});
            Constants.setValue.setBanStateEnabled(banStates.length > 0);
            Utilities.setMasterData(Data);
            this.setState({
                isLoading: false
            })
        } else {
            let param = {
            }
            getBannedStats(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let Data = Utilities.getMasterData();
                    Data['banned_state'] = responseJson.data;
                    let banStates = Object.keys(responseJson.data || {});
                    Constants.setValue.setBanStateEnabled(banStates.length > 0);
                    Utilities.setMasterData(Data);
                    ls.set('bslist', responseJson.data);
                    ls.set('bslistTime', { date: Date.now() });
                    this.setState({
                        isLoading: false
                    })
                }
            })
        }
    }

    handleValidation = () => {
        this.setState({
            isChecked: !this.state.isChecked
        })
    }

    /**
     * @description Render UI component
     */
    render() {
        const {
            posting,
            email,
            isChecked
        } = this.state;
        let banStates = Object.values(Utilities.getMasterData().banned_state || {});
        let bsL = banStates.length;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white p-0">
                        {
                            process.env.REACT_APP_CAPTCHA_ENABLE == 1 && !this.state.posting && <Suspense fallback={<div />} ><ReactCaptcha
                                verifyCallback={this.onCaptchaChange}
                            /></Suspense>
                        }
                        {this.state.posting && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.emaillogin.title}</title>
                            <meta name="description" content={MetaData.emaillogin.description} />
                            <meta name="keywords" content={MetaData.emaillogin.keywords}></meta>
                        </Helmet>
                        <form onSubmit={this.onSubmit} className="signup-form email-flow" id='emailLoginForm'>

                            <div className="verification-block">
                                <div className="media-checks">
                                    <div className="socail-region">
                                        <img alt="" src={Images.BRAND_LOGO_FULL} className="logo-lg" />
                                        <Row>
                                            <Col xs={12} className='phone-number-style'>
                                                <FormGroup
                                                    className='input-label-center'
                                                    controlId="formBasicText"
                                                    validationState={this.getValidationState('email', email)}>
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={inputStyle}
                                                        id='email'
                                                        name='email'
                                                        value={email}
                                                        placeholder={AppLabels.ENTER_YOUR_EMAIL}
                                                        type='email'
                                                        onChange={this.handleChange}
                                                    />
                                                </FormGroup>
                                            </Col>
                                        </Row>
                                        {Utilities.getMasterData().int_version == "0" &&
                                            <Row className='align-items-baseline'>
                                            <Col xs={1}>
                                                <FormGroup>
                                                    <Checkbox
                                                        name='validate'
                                                        className="custom-validate-check text-right"
                                                        onChange={this.handleValidation}
                                                        checked={isChecked}
                                                    >
                                                    </Checkbox>
                                                </FormGroup>
                                            </Col>
                                            <Col xs={11}>

                                                <p className="auth-txt" onClick={this.handleValidation} style={{ marginTop: 0, padding: 0 }}>
                                                    {
                                                        Utilities.getMasterData().a_age_limit == 1 ?
                                                            AppLabels.I_hereby_confirm
                                                            :
                                                            AppLabels.I_agree_to
                                                    }
                                                    <a className='primary' target='_blank' href="/terms-condition" onClick={(event) => event.stopPropagation()}> {AppLabels.TANDC_TITLE} </a>
                                                    {bsL > 0 && <>
                                                        {
                                                            AppLabels.and_I_am_not_a2
                                                        }
                                                        {banStates.slice(0, bsL > 5 ? 5 : bsL).join(', ')}
                                                        {bsL > 5 && <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                            <Tooltip id="tooltip" className="tooltip-featured">
                                                                <strong>{banStates.join(', ')}</strong>
                                                            </Tooltip>
                                                        }><i style={{ padding: 3, fontSize: 12 }} className="icon-info" onClick={(event) => event.stopPropagation()} /></OverlayTrigger>}
                                                        {
                                                            AppLabels.and_I_am_not_txt
                                                        }
                                                    </>
                                                    }
                                                </p>


                                            </Col>
                                        </Row>}
                                        {(Utilities.getMasterData().allow_fb == '1' || Utilities.getMasterData().allow_google == '1') && <div className="title">{AppLabels.CONNECT_INSTANTLY_WITH}</div>}
                                        <div {...{
                                            className: `content ${(!isChecked && Utilities.getMasterData().int_version == "0") ? 'no-content' : ''}`
                                        }}>
                                            {
                                                Utilities.getMasterData().allow_fb == 1 && <React.Fragment>
                                                    {this.isAndroidApp() ?
                                                        <div>
                                                            {!isChecked && <div className='overlap-gplus'></div>}
                                                            <div onClick={() => this.appNativeLogin('facebook')} className="social-item facebook native">
                                                                <i className="icon-facebook facebook"></i>
                                                            </div>
                                                        </div>
                                                        :
                                                        <div className="social-item facebook">
                                                            {!isChecked && <div className='overlap-gplus'></div>}
                                                            <Suspense fallback={<div />} >
                                                                <FacebookLogin
                                                                    appId={WSC.FB_APP_ID}
                                                                    autoLoad={false}
                                                                    cookie={false}
                                                                    callback={this.onFacebookSuccess}
                                                                    onFailure={this.onLoginFailure}
                                                                    cssClass="bg mR-20"
                                                                    redirectUri={WSC.baseURL + '/signup'}
                                                                    fields="name,email,picture"
                                                                    scope={['email']}
                                                                    className="cursor-pointer"
                                                                    icon={<i className="icon-facebook facebook"></i>}
                                                                    textButton={<div className="label facebook"></div>}
                                                                /></Suspense>
                                                        </div>
                                                    }
                                                </React.Fragment>
                                            }
                                            {
                                                Utilities.getMasterData().allow_google == 1 && <React.Fragment>
                                                    {
                                                        this.isAndroidApp() ?
                                                            <div>
                                                                {!isChecked && <div className='overlap-gplus'></div>}
                                                                <div onClick={() => this.appNativeLogin('google')} className="social-item gplus native">
                                                                    <img src={Images.GPLUS_LOGO} alt="" width="30px" />
                                                                </div>
                                                            </div>
                                                            :
                                                            <div className="social-item gplus">

                                                                <Suspense fallback={<div />} >
                                                                    <GoogleLogin
                                                                        clientId={WSC.GPLUS_ID}
                                                                        buttonText={AppLabels.GOOGLE}
                                                                        scope="profile email"
                                                                        autoLoad={false}
                                                                        icon={false}
                                                                        fetchBasicProfile={false}
                                                                        redirectUri={WSC.baseURL + '/signup'}
                                                                        className="google-login-btn"
                                                                        onSuccess={this.responseGoogle}
                                                                        onFailure={this.responseGoogle}
                                                                    >
                                                                        <img src={Images.GPLUS_LOGO} alt="" width="30px" />
                                                                    </GoogleLogin></Suspense>
                                                            </div>
                                                    }
                                                </React.Fragment> 
                                            }

                                        </div>
                                        <Button className="btn-block btm-action-btn" 
                                        disabled={!(email && Validation.validate('email', this.state.email)) || posting} bsStyle="primary" type='submit'>{AppLabels.SIGN_UP_OR_LOGIN}</Button>
                                    </div>
                                </div>

                            </div>
                        </form>
                        {this.state.allowLanguage && this.state.allowLanguage.length > 1 &&
                            <Suspense fallback={<div />} >
                                <SelectLanguage isBottomFixed={true} />
                            </Suspense>
                        }
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
