import React, { Suspense, lazy } from 'react';
import { Row, Col, Button, FormGroup, OverlayTrigger, Tooltip, Checkbox } from 'react-bootstrap';
import { Helmet } from "react-helmet";
// import { socialLogin } from "../../../WSHelper/WSCallings";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Utilities, _isUndefined, checkFlow, sendMessageToApp } from '../../../Utilities/Utilities';
import { userLogin, getBannedStats, socialLogin } from '../../../WSHelper/WSCallings';
import { createBrowserHistory } from 'history';
import WSManager from "../../../WSHelper/WSManager";
import Images from '../../../components/images';
import CustomLoader from '../../../helper/CustomLoader';
import * as AppLabels from "../../../helper/AppLabels";
import * as WSC from "../../../WSHelper/WSConstants";
import * as Constants from "../../../helper/Constants";
// import firebase from '../../../views/firebase/firebase';
import MetaComponent from '../../MetaComponent';
import ls from 'local-storage';
const SelectLanguage = lazy(() => import('../../CustomComponent/SelectLanguage'));
const GoogleLogin = lazy(() => import('../../CustomComponent/GoogleLogin'));
const FacebookLogin = lazy(() => import('../../CustomComponent/FacebookLogin'));
const ReactCaptcha = lazy(() => import('../../CustomComponent/ReactCaptcha'));
const CustomPhoneInput = lazy(() => import('../../CustomComponent/CustomPhoneInput'));

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

class MobileLogin extends React.Component {
    constructor(props, context) {
        console.log(props);
        super(props, context);
        this.state = {
            phone: '',
            code: Constants.DEFAULT_COUNTRY_CODE,
            posting: false,
            allowLanguage: Constants.ALLOW_LANG,
            captchaToken: '',
            isLoading: false,
            isIOS: WSManager.getIsIOSApp() ? true : false,
            isChecked: false

        };
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('signup')

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
        console.log(Utilities.getCpSessionPath())
        if (!window.ReactNativeWebView && Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == '1') {
            this.getUserLatLongWeb()
        } else if (window.ReactNativeWebView && Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == '1') {
            // Utilities.setLocationStatusToApp()
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
            this.handelNativeData()
        }
        this.getBannedStateList();
    }

    componentWillUnmount() {
        let data = {
            action: 'back',
            type: false,
        }
        sendMessageToApp(data)
    }

    handelNativeData() {
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
    * @description This function send OTP to users mobile number
    * @param e- click event
    */
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        let phone_code_str = "+" + this.state.code;
        let phone_no_str = this.state.phone;
        let phone_no = phone_no_str.replace(phone_code_str, "");
        let device_id = window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID

        let param = {
            "phone_no": phone_no,
            "phone_code": this.state.code,
            "device_type": Utilities.getDeviceType(),
            "device_id": device_id,
            "otp_hash": ls.get('otp_hash'),
            //"is_systemuser": 1,
            ...Utilities.getCpSession(),
        }
        if (process.env.REACT_APP_CAPTCHA_ENABLE == 1) {
            param['token'] = this.state.captchaToken;
        }
        userLogin(param).then(async (responseJson) => {
            this.setState({ posting: false });
            if (responseJson && responseJson.response_code === WSC.successCode) {
                let flow_type = ''
                if (responseJson.data && responseJson.data.is_user_exist == 0) {
                    flow_type = 'signup'
                } else {
                    flow_type = 'login'
                }
                Utilities.gtmEventFire('onboarding_flow', {
                    flow_type: flow_type,
                    screen_no: '1'
                })
                Utilities.gtmEventFire('otp_sent')
                this.gotoDetails(responseJson.data, flow_type);
                // this.gotoDetails(responseJson.data, this.state.code);
            }else{
                Utilities.showToast(responseJson.global_error, 5000);
            }
        })
        Utilities.setDefaultSport();
    }

    /**
     * @description Method for get trigered event from url source
     * @param source recived from which platform user comes
     */
    getEventName(source) {
        if (source == 'fb') {
            return 'facebook_signup';
        }
        else if (source == 'insta') {
            return 'insta_signup';
        }
        else if (source == 'google_ads') {
            return 'googleads_signup';
        }
        else if (source == 'twitter') {
            return 'twitter_signup';
        }
        else {
            return 'direct_signup';

        }
    }

    /**
     * @description After User enter mobile navigate to OTP screen
     * @param data data received from login api response
     */
    gotoDetails = (data, flow_type) => {
        data['next_step'] = 'verify';
        data['flow_type'] = flow_type;
        let nextStepData = { data: data || '' }
        if (this.props.location.state) {
            let { lineupPath, FixturedContest, LobyyData, joinContest, sportsId, isReverseF, isSecIn, isPlayingAnnounced } = this.props.location.state;
            nextStepData = {
                data: data || '', facebook_data: null, google_data: null, joinContest: joinContest || '',
                lineupPath: lineupPath || '', FixturedContest: FixturedContest || '', LobyyData: LobyyData || '', sportsId: sportsId,
                isReverseF: isReverseF || false, isSecIn: isSecIn, isShare: true, isPlayingAnnounced: isPlayingAnnounced
            };
        }
        this.props.history.push(checkFlow(nextStepData))
    }

    /**
     * @description Used for FB success callback
     * @param user data received from FB api response
    */
    onFacebookSuccess = ({ _profile, _token }) => {
        let user = { ..._profile, ..._token }
        if (user) {
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
                    let nextStepData = { data: responseJson.data, facebook_data: user, google_data: null, nextStep: responseJson.data.next_step };
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
        console.error('FB onLoginFailure' + err)
    }

    /**
    * @description Used for Google success/failure callback
    * @param googleUser data received from Google api
    * @param isSuccess flag will true in case user data received else it will be false
   */
    responseGoogle = (googleUser, isSuccess) => {
        if (googleUser && isSuccess) {
            this.setState({ posting: true });
            var id_token = googleUser.tokenId;
            var googleId = googleUser.googleId;
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
                WSManager.setTempToken(responseJson.data.Sessionkey);
                if (responseJson.response_code === WSC.successCode) {
                    let nextStepData = { data: responseJson.data, facebook_data: null, google_data: googleUser, nextStep: responseJson.data.next_step };
                    this.props.history.push(checkFlow(nextStepData))
                }
                this.setState({ posting: false });
            })
        }
    }

    handleOnChange = (value, data) => {
        if (Constants.ONLY_SINGLE_COUNTRY == 1 && value.startsWith('+' + Constants.DEFAULT_COUNTRY_CODE)) {
            this.setState({ code: data.dialCode, phone: value })
        } else if (Constants.ONLY_SINGLE_COUNTRY == 0) {
            this.setState({ code: data.dialCode, phone: value })
        } else {
            this.setState({ code: Constants.DEFAULT_COUNTRY_CODE, phone: '' })
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
            phone,
            isChecked
        } = this.state;
        let banStates = Object.values(Utilities.getMasterData().banned_state || {});
        let bsL = banStates.length;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container bg-white">
                        {
                            process.env.REACT_APP_CAPTCHA_ENABLE == 1 && !this.state.posting && <Suspense fallback={<div />} ><ReactCaptcha
                                verifyCallback={this.onCaptchaChange}
                            /></Suspense>
                        }
                        {this.state.posting && <CustomLoader />}

                        {/* <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.signup.title}</title>
                            <meta name="description" content={MetaData.signup.description} />
                            <meta name="keywords" content={MetaData.signup.keywords}></meta>
                        </Helmet> */}
                        <MetaComponent page="signup" />

                        <form onSubmit={this.onSubmit} className="signup-form" id='mobileLoginForm'>
                            <div className="verification-block">
                                <div className="media-checks">
                                    <div className="socail-region">
                                        <img alt="" src={Constants.DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL} className="logo-lg" />

                                        <Row>
                                            <Col xs={12} className='phone-number-style'>
                                                <FormGroup>
                                                    <Suspense fallback={<div />} >
                                                        <CustomPhoneInput {...this.props} phone={phone} handleOnChange={this.handleOnChange} />
                                                    </Suspense>
                                                </FormGroup>
                                            </Col>
                                        </Row>
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
                                                    {(Utilities.getMasterData().bs_a == 1 && bsL > 0) && <>
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
                                        </Row>
                                        {(Utilities.getMasterData().allow_fb == 1 || Utilities.getMasterData().allow_google == 1) && <div className="title">{AppLabels.CONNECT_INSTANTLY_WITH}</div>}
                                        <div {...{
                                            className: `content ${!(isChecked) ? 'no-content' : ''}`
                                        }}>
                                            {
                                               Utilities.getMasterData().allow_fb == 1 && <React.Fragment>
                                                {
                                                       ( this.isAndroidApp() || this.state.isIOS) ?
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
                                                        (this.isAndroidApp() || this.state.isIOS) ?
                                                            <div>
                                                                {!isChecked && <div className='overlap-gplus'></div>}
                                                                <div onClick={() => this.appNativeLogin('google')} className="social-item gplus native">
                                                                    <img src={Images.GPLUS_LOGO} alt="" width="30px" />
                                                                </div>
                                                            </div>
                                                            :
                                                            <div className="social-item gplus">
                                                                {!isChecked && <div className='overlap-gplus'></div>}
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

                                        <Row>
                                            <Col xs={12}>
                                                {/* <p className="auth-txt">
                                                    {
                                                        Utilities.getMasterData().a_age_limit == 1 &&
                                                        AppLabels.I_hereby_confirm
                                                    }
                                                    {AppLabels.I_AGREE_TO_THE}
                                                    <a className='primary' target='_blank' href="/terms-condition"> {AppLabels.TERMS_CONDITION} </a>
                                                    {Utilities.getMasterData().int_version != 1 ? AppLabels.and_I_am_not_a : ''}
                                                </p> */}





                                            </Col>
                                        </Row>
                                        <Button className="btn-block btm-action-btn " disabled={!(phone && Utilities.isValidPhoneNumber(phone) && isChecked) || posting} bsStyle="primary" type='submit'>{AppLabels.SIGN_UP_OR_LOGIN}</Button>
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

export default MobileLogin