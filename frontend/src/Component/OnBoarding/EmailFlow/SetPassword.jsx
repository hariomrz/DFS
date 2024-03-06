import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
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

export default class SetPassword extends React.Component {
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
        let device_id = window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID

        let param = {
            "step": "password",
            "password": md5(this.state.password),
            "email": data.user_profile.email,
            "device_type":Utilities.getDeviceType(),
            "device_id": device_id,

        }
        if(WSManager.getAffiliatCode()){
            param['affcd'] = WSManager.getAffiliatCode();
        }
        if (next_step === '/set-password' || next_step === 'set-password') {
            updateSignupData(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.parseSubmitResponse(responseJson.data);
                }
                this.setState({ posting: false });
            })
        } else {
            validateLogin(param).then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    this.parseSubmitResponse(responseJson.data);
                }
                this.setState({ posting: false });
            })
        }
    }
    /**
           * @description This function responsible for parsing api response after submit call
         */
    parseSubmitResponse(apiData) {
        const { joinContest, lineupPath, FixturedContest, LobyyData } = this.props.location.state.nextStepData;
        if (apiData.Sessionkey) {
            WSManager.setTempToken(apiData.Sessionkey);
        }
        if (apiData.is_profile_complete == 1) {
            WSManager.setProfile(apiData.user_profile);
            WSManager.setToken(apiData.Sessionkey);
            if (joinContest) {
                let nextStepData = { FixturedContest: FixturedContest, LobyyData: LobyyData };
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
        let { lineupPath, FixturedContest, LobyyData,facebook_data,google_data, joinContest } = this.props.location.state.nextStepData;
        let mData = { data, facebook_data: facebook_data, google_data: google_data, FixturedContest: FixturedContest, LobyyData: LobyyData, lineupPath: lineupPath, joinContest:joinContest };
        this.props.history.push({ pathname: path, state: {nextStepData:mData} })
    }
    

    UNSAFE_componentWillMount(){
        Utilities.setScreenName('passwordlogin')
    }
    /**
     * @description Render UI component
    */
    render() {
        alert('555555')

        const HeaderOption = {
            back: true,
            hideShadow: true,
            isOnb: true,
        }

        const {
            formValid,
            posting,
            password,
            showPassword
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white set-pwd-wrap">
                        {this.state.posting && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.passwordlogin.title}</title>
                            <meta name="description" content={MetaData.passwordlogin.description} />
                            <meta name="keywords" content={MetaData.passwordlogin.keywords}></meta>
                        </Helmet>

                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        
                        <form onSubmit={this.onSubmit} className='onboarding-inner-pages' id='setPwdForm'>
                            <div className='view-center-align'>
                                <div className="verification-block ">
                                    <Row>
                                        <Col>
                                            <div className="onboarding-page-heading-lg">
                                                {AppLabels.CREATE_YOUR_PASSWORD}
                                            </div>
                                            <div className="onboarding-page-desc">
                                                {AppLabels.CREATE_YOUR_PASSWORD_TEXT}
                                            </div>
                                        </Col>
                                    </Row>
                                    <Row className="vertical-center-section-lg">
                                        <Col xs={12}  className="vertical-center-element">
                                            <FormGroup
                                                className='input-label-center'
                                                controlId="formBasicText"
                                                validationState={this.getValidationState('password', password)}>

                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={inputStyle}
                                                    id='password'
                                                    name='password'
                                                    value={password}
                                                    placeholder={AppLabels.PASSWORD}
                                                    type={showPassword? 'text' : 'password'}
                                                    onChange={this.handleChange}
                                                />
                                            </FormGroup>
                                            <a 
                                                href 
                                                onClick={
                                                    ()=>this.setState({showPassword: !showPassword})
                                                } 
                                                className="pwd-show-hide"
                                            >
                                                <i className={showPassword ? "icon-eye" : "icon-eye-cancel"}></i>
                                            </a>
                                        </Col>
                                    </Row>
                                    <Row className="btm-fixed-submit">                                               
                                        <Col xs={12} className="text-center">
                                            <button className="submit-otp" disabled={!formValid || posting} type='submit'><i className="icon-next-btn"></i></button>
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
