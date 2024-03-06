import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import Validation from '../../../helper/Validation';
import { updateSignupData, userLogin } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import FloatingLabel from 'floating-label-react';
import { inputStyle, darkInputStyle } from '../../../helper/input-style';
import CustomHeader from '../../../components/CustomHeader';
import { SignupTmpData, DARK_THEME_ENABLE } from '../../../helper/Constants';
import WSManager from '../../../WSHelper/WSManager';
import { Utilities } from '../../../Utilities/Utilities';

export default class PickEmail extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            referral: '',
            stepName: 'email',
            userName: '',
            email: SignupTmpData.email || '',
            formValid: SignupTmpData.email ? true : false,
            posting: false,
            setEmail: true
        };
    }

    componentDidMount() {
        Utilities.setScreenName('pickemail')

        if (this.props.location.state.nextStepData.facebook_data && this.props.location.state.nextStepData.facebook_data.email) {
            this.setState({ setEmail: false, email: this.props.location.state.nextStepData.facebook_data.email }, () => {
                this.setState({ setEmail: true }, () => this.validateForm())
            })
        }
        else if (this.props.location.state.nextStepData.google_data && this.props.location.state.nextStepData.google_data.profileObj.email) {
            let userEmail = this.props.location.state.nextStepData.google_data.profileObj.email;

            this.setState({ setEmail: false, email: userEmail }, () => {
                this.setState({ setEmail: true }, () => this.validateForm())
            })
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
     * @description  this method update user email to server
     * @param e- click event
     * after success navigate to next step
     * **/
    // onSubmit = (e) => {
    //     e.preventDefault();
    //     this.setState({ posting: true });
    //     let param = {
    //         "step": this.state.stepName,
    //         "referral_code": this.state.referral,
    //         "user_name": this.state.userName,
    //         "email": this.state.email
    //     }
    //     if(WSManager.getAffiliatCode()){
    //         param['affcd'] = WSManager.getAffiliatCode();
    //     }
    //     updateSignupData(param).then((responseJson) => {
    //         if (responseJson && responseJson.response_code == WSC.successCode) {
    //             Utilities.gtmEventFire('onboarding_flow', {
    //                 flow_type: 'signup',
    //                 screen_no: '4'
    //             })
    //             this.gotoDetails(responseJson.data)
    //         }
    //         this.setState({ posting: false });
    //     })
    // }

    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        let device_id = window.ReactNativeWebView ? WSC.DeviceToken.getDeviceId() : WSC.deviceID
        let { login_flow } = Utilities.getMasterData()

        if (login_flow == "1") {
            let param = {
                "email": this.state.email,
                "device_type": Utilities.getDeviceType(),
                "device_id": device_id,
                "google_id": localStorage.getItem('google_id') ? localStorage.getItem('google_id') : '',
                "google_access_token": localStorage.getItem('google_access_token') ? localStorage.getItem('google_access_token') : '',
                "facebook_id": localStorage.getItem('facebook_id') ? localStorage.getItem('facebook_id') : '',
                "facebook_access_token": localStorage.getItem('facebook_access_token') ? localStorage.getItem('facebook_access_token') : '',
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
                    this.gotoDetails(responseJson.data)
                }
            })
            Utilities.setDefaultSport();
        }

        if (login_flow == "0") {

            let param = {
                "step": this.state.stepName,
                "referral_code": this.state.referral,
                "user_name": this.state.userName,
                "email": this.state.email
            }
            if (WSManager.getAffiliatCode()) {
                param['affcd'] = WSManager.getAffiliatCode();
            }
            updateSignupData(param).then((responseJson) => {
                if (responseJson && responseJson.response_code == WSC.successCode) {
                    Utilities.gtmEventFire('onboarding_flow', {
                        flow_type: 'signup',
                        screen_no: '4'
                    })
                    this.gotoDetails(responseJson.data)
                }
                this.setState({ posting: false });
            })
        }
    }




    /**
       * @description This function responsible for Navigate to next step after update email
     */
    gotoDetails = (data) => {
        let { login_flow } = Utilities.getMasterData()
        SignupTmpData['email'] = this.state.email;
        if (login_flow == "1") {
            let nextStepData = {
                data: data, FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath, facebook_data: this.props.location.state.nextStepData.facebook_data, google_data: this.props.location.state.nextStepData.google_data,
                isReverseF: this.props.location.state.nextStepData.isReverseF || false, isSecIn: this.props.location.state.nextStepData.isSecIn, isShare: true, email: data.email,
                isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced
            }
            // console.log('nextStepData', nextStepData)
            this.props.history.push({ pathname: '/verify', state: { nextStepData: nextStepData } })
        }
        if (login_flow == "0") {
            let nextStepData = {
                data: data, FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath, facebook_data: this.props.location.state.nextStepData.facebook_data, google_data: this.props.location.state.nextStepData.google_data,
                isReverseF: this.props.location.state.nextStepData.isReverseF || false, isSecIn: this.props.location.state.nextStepData.isSecIn, isShare: true,
                isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced
            }
            this.props.history.push({ pathname: '/pick-username', state: { nextStepData: nextStepData } })

        }
    }

    /**
     * @description Render UI component
    */
    render() {
        const {
            formValid,
            posting,
            email
        } = this.state;
        const HeaderOption = {
            back: true,
            hideShadow: true,
            isOnb: true
        }
        return (
            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white registration-web-container">
                        {this.state.posting && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.pickemail.title}</title>
                            <meta name="description" content={MetaData.pickemail.description} />
                            <meta name="keywords" content={MetaData.pickemail.keywords}></meta>
                        </Helmet>

                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />

                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages inner-top-spacing" id='pickEmailForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading-lg">
                                            {AppLabels.EMAIL_ADDRESS}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {AppLabels.EMAIL_USE}
                                        </div>
                                    </Col>
                                </Row>
                                <Row className="vertical-center-section">
                                    <Col xs={12} className="vertical-center-element">
                                        <FormGroup
                                            className='input-label-center'
                                            controlId="formBasicText"
                                            validationState={this.getValidationState('email', email)}>
                                            {this.state.setEmail &&
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyle : inputStyle}
                                                    id='email'
                                                    name='email'
                                                    placeholder={AppLabels.ENTER_YOUR_EMAIL}
                                                    type='email'
                                                    value={email}
                                                    onChange={this.handleChange}
                                                />
                                            }
                                        </FormGroup>
                                    </Col>
                                </Row>
                                <Row className="btm-fixed-submit">
                                    <Col xs={12} className="text-center">
                                        <button className="submit-otp" disabled={!formValid || posting} type='submit'><i className="icon-next-btn"></i></button>
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