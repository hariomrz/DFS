import React from 'react';
import { Row, Col, Button, FormGroup } from 'react-bootstrap';
import Validation from '../../../helper/Validation';
import WSManager from "../../../WSHelper/WSManager";
import { updateSignupData, validateLogin } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import FloatingLabel from 'floating-label-react';
import { inputStyle } from '../../../helper/input-style';
import CustomHeader from '../../../components/CustomHeader';
import { Utilities, _isUndefined } from '../../../Utilities/Utilities';

var md5 = require('md5');

export default class EnterPassword extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            password: '',
            formValid: false,
            posting: false,
            email: this.props.location.state && !_isUndefined(this.props.location.state.nextStepData) && !_isUndefined(this.props.location.state.nextStepData.email) ? this.props.location.state.nextStepData.email : '',
            showPassword: false
        };
    }

    /**
     * @description check validation of user entered email with local regex
     * @param type password for this screen
     * @param value user entered value
    */
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }

    /**
     * @description handle password change and update state variable
     * @param e click event
    */
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value.trim();
        this.setState({ [name]: value }, this.validateForm);
    }

    /**
     * @description manage form validations
    */
    validateForm() {
        this.setState({ formValid: Validation.validate('password', this.state.password) == 'success' });
    }

    /**
     * @description  this method update user password to server
     * @param e- click event
     * after success navigate to next step
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        const { data,next_step } = this.props.location.state.nextStepData;

        let param = {
            "step": "password",
            "password": md5(this.state.password),
            "email": data.email,
            "device_type":Utilities.getDeviceType(),
            "device_id": window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID,

        }
        if(WSManager.getAffiliatCode()){
            param['affcd'] = WSManager.getAffiliatCode();
        }

        if (next_step === '/set-password') {
            updateSignupData(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.parseSubmitResponse(responseJson.data);
                }
                this.setState({ posting: false });
            })
        } else {
            validateLogin(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    WSManager.setCookie('_id', responseJson.data.user_profile.user_unique_id)
                    WSManager.setCookie('_nm', responseJson.data.user_profile.user_name)
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
                    this.parseSubmitResponse(responseJson.data);
                }
                else{
                    Utilities.showToast(responseJson.message, 3000);
                }
                this.setState({ posting: false });
            })
        }
    }
    /**
           * @description This function responsible for parsing api response after submit call
         */
    parseSubmitResponse(apiData) {
        const { joinContest, lineupPath, FixturedContest, LobyyData, isReverseF } = this.props.location.state.nextStepData;
        if (apiData.Sessionkey) {
            WSManager.setTempToken(apiData.Sessionkey);
        }
        if (apiData.is_profile_complete == 1) {
            WSManager.setProfile(apiData.user_profile);
            WSManager.setToken(apiData.Sessionkey);
            if (joinContest) {
                let nextStepData = { FixturedContest: FixturedContest, LobyyData: LobyyData, isReverseF: isReverseF };
                this.props.history.push({ pathname: lineupPath, state:{nextStepData:nextStepData}  })
            } else {
                this.gotoDetails("/", apiData);
            }
        } else {
            this.gotoDetails("/" + apiData.next_step, apiData);
        }
    }

    /**
       * @description This function responsible for Navigate to next step after mobile verification
     */
    gotoDetails = (path, data) => {
        let { lineupPath, FixturedContest, LobyyData,facebook_data,google_data, isReverseF } = this.props.location.state.nextStepData;
        let mData = { data, facebook_data: facebook_data, google_data: google_data, FixturedContest: FixturedContest, LobyyData: LobyyData, lineupPath: lineupPath, isReverseF: isReverseF };
        this.props.history.push({ pathname: path, state: {nextStepData:mData} })
    }

    goToForgotPassword = () => {
        this.props.history.push({ pathname: '/enter-email', state: { email: this.state.email } })
    }

    UNSAFE_componentWillMount() {
        Utilities.setScreenName('passwordlogin')
    }

    /**
     * @description Render UI component
    */
    render() {

        const HeaderOption = {
            back: true,
            isPrimary:true,
            hideShadow: true,
            isOnb: true,
        }

        const {
            formValid,
            posting,
            password,
            showPassword
        } = this.state;
        console.log(this.props.location.state.nextStepData.isReverseF)
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white">
                        {this.state.posting && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.passwordlogin.title}</title>
                            <meta name="description" content={MetaData.passwordlogin.description} />
                            <meta name="keywords" content={MetaData.passwordlogin.keywords}></meta>
                        </Helmet>

                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />

                        <form onSubmit={this.onSubmit} className='onboarding-inner-pages' id='enterPaswordForm'>
                            <div className='view-center-align'>
                                <div className="verification-block ">
                                    <Row>
                                        <Col>
                                            <div className="onboarding-page-heading">
                                                {AppLabels.YOUR_PASSWORD}
                                            </div>
                                            <div className="onboarding-page-desc">
                                                {AppLabels.YOUR_PASSWORD_TEXT}
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row className="vertical-center-section-lg min-h-remove">
                                        <Col xs={12} className="vertical-center-element spc-input">
                                            <FormGroup
                                                className='input-label-center '
                                                controlId="formBasicText"
                                                validationState={this.getValidationState('password', password)}>

                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyle}
                                                    id='password'
                                                    name='password'
                                                    value={password}
                                                    placeholder={AppLabels.ENTER_PASSWORD}
                                                    type={showPassword? 'password' : 'text'}
                                                    onChange={this.handleChange}
                                                />
                                            </FormGroup>
                                            <a 
                                                href 
                                                onClick={
                                                    ()=>this.setState({
                                                        showPassword: !showPassword
                                                    })
                                                } 
                                                className="pwd-show-hide"
                                            >
                                                <i className={showPassword ? "icon-eye" : "icon-eye-cancel"}></i>
                                            </a>
                                            <div onClick={this.goToForgotPassword} className='forgot-password-text-container text-center m-t-20'>
                                                <span className='forgot-password-text'>
                                                    {AppLabels.FORGOT_PASSWORD_TEXT}
                                                </span>
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row className="text-center btm-fixed-submit">
                                        <Col xs={12}>
                                            <Button className="btn-block btm-action-btn mt30" disabled={!formValid || posting} bsStyle="primary" type='submit'>{AppLabels.SUBMIT}</Button>
                                        </Col>
                                    </Row>
                                </div>
                            </div>
                        </form>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}
