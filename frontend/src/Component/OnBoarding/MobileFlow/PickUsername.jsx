import React from 'react';
import Cookies from 'universal-cookie';
import { Row, Col, FormGroup } from 'react-bootstrap';
import Validation from '../../../helper/Validation';
import WSManager from "../../../WSHelper/WSManager";
import {updateSignupData} from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import FloatingLabel from 'floating-label-react';
import { inputStyle,darkInputStyle } from '../../../helper/input-style';
import CustomHeader from '../../../components/CustomHeader';
import { SignupTmpData,DARK_THEME_ENABLE } from '../../../helper/Constants';
import { Utilities } from '../../../Utilities/Utilities';
import ls from 'local-storage';
const cookies = new Cookies();

export default class PickUsername extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            referral: '',
            stepName: 'username',
            userName: '',
            email: '',
            formValid: false,
            posting: false,
            isUserName: true,
            showError: false,
            errorMsg: ''
        };
    }


    /**
     * @description check validation of username with local regex
     * @param type email for this screen
     * @param value user entered value
    */
    componentDidMount() {
        console.log('isPlayingAnnounced:', this.props.location.state.nextStepData.isPlayingAnnounced);

        Utilities.setScreenName('pickusername')
        
     if(Utilities.getMasterData().a_eml != 0){
        if (this.props.location.state.nextStepData.data.user_profile && this.props.location.state.nextStepData.data.user_profile.user_name) {
            let uName = this.props.location.state.nextStepData.data.user_profile.user_name;
            this.setState({
                userName: uName, formValid: Validation.validate('userName', uName) == 'success', isUserName: false
            }, () => this.setState({ isUserName: true }))
        }
        else if (this.props.location.state.nextStepData.data.user_name) {
            let uName = this.props.location.state.nextStepData.data.user_name;
            this.setState({
                userName: uName, formValid: Validation.validate('userName', uName) == 'success', isUserName: false
            }, () => this.setState({ isUserName: true }))
        }
     }
       

    
       
    }
    
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }
    
    /**
     * @description handle username change and update state variable
     * @param e click event
    */
   handleChange = (e) => {
       const name = e.target.name;
       const value = e.target.value;
       this.setState({ [name]: value,showError: false }, this.validateForm);
    }
    
    /**
     * @description manage form validations
    */
   validateForm() {
       this.setState({ formValid: Validation.validate('userName', this.state.userName) == 'success' });
    }
    
    /**
     * @description  this method update username to server
     * @param e- click event
     * after success navigate lobby or lineup(in case user clicks join contest as a guest user) 
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        var trackData = cookies.get('_adsgtd');
        let param = {
            "step": this.state.stepName,
            "referral_code": WSManager.getReferralCode(),
            "user_name": this.state.userName,
            "email": this.state.email,
            ...WSManager.getAflcCode(true)
        }
        if (this.state.stepName == 'username') {
            if (this.props.location.state.nextStepData.FixturedContest && this.props.location.state.nextStepData.FixturedContest.is_private == 1) {
                param.is_signup_from_contest = 1;
                param.contest_unique_id = this.props.location.state.nextStepData.FixturedContest.contest_unique_id;
                param.contest_type = '1';

                if(ls.get('isFromLFSC')){
                    ls.set('isFromLFSC',false)
                    param.contest_type = '2';

   
                }

                
            }

        }
        if(trackData){
            param['user_track_id'] = trackData.user_track_id;
        }
        
        if(WSManager.getAffiliatCode()){
            param['affcd'] = WSManager.getAffiliatCode();
        }
        updateSignupData(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if(process.env.REACT_APP_SINGULAR_ENABLE > 0)
                {
                    if (window.ReactNativeWebView)
                    {
                        let data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Registration_completed',
                            args: responseJson.data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(data));
                    }
                    else
                    {
                        window.SingularLogin(responseJson.data.user_unique_id);
                        window.SingularEvent("Registration_completed", responseJson.data);
                    }
                }
                this.setState({showError: false, posting: false})
                if (window.ReactNativeWebView) {
                    let data = {
                        action: 'sign_up',
                        user_unique_id:  WSManager.getCookie('_id'),
                        referral_code: this.state.referral,
                        user_name: this.state.userName,
                        targetFunc: 'sign_up'
                    }
                    window.ReactNativeWebView.postMessage(JSON.stringify(data));
                }
                Utilities.gtmEventFire('onboarding_flow', {
                    flow_type: 'signup',
                    screen_no: '5'
                })
                Utilities.gtmEventFire('sign_up', {
                    'user_id': WSManager.getCookie('_id'),
                    'user_name': this.state.userName,
                    'referral_code': WSManager.getReferralCode() ? WSManager.getReferralCode() : '',
                }, true)
                WSManager.setToken(WSManager.getTempToken('id_temp_token'));
                WSManager.setCookie('_nm', this.state.userName)
                this.gotoDetails(responseJson.data)
                if (trackData) {
                    cookies.remove('_adsgtd');
                }
            } else {
                this.setState({ posting: false,showError: true,errorMsg: responseJson.error.user_name});
            }
        })
    }

    /**
      * @description This function responsible for Navigate to next step after update username
    */
   gotoDetails = (data) => {
    SignupTmpData['email'] = ''
    if (data.next_step && data.next_step == "mobile") {
        let nextStepData = { 
            FixturedContest: this.props.location.state.nextStepData.FixturedContest, 
            LobyyData: this.props.location.state.nextStepData.LobyyData, 
            lineupPath: this.props.location.state.nextStepData.lineupPath,
            joinContest:this.props.location.state.nextStepData.joinContest,
            isReverseF: this.props.location.state.nextStepData.isReverseF || false, 
            isSecIn: this.props.location.state.nextStepData.isSecIn || false ,isShare: true,
            isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced
        };
        if (Utilities.getMasterData().login_flow == 1 && Utilities.getMasterData().a_mbl == 0) {
            this.props.history.replace('/lobby')
        }
        else {
            this.props.history.push({ pathname: '/pick-mobile', state: { nextStepData: nextStepData } })
        }
    }
    else {
        WSManager.setToken(WSManager.getTempToken('id_temp_token'));
        if (this.props.location.state.nextStepData.FixturedContest) {
            let nextStepData = { 
                FixturedContest: this.props.location.state.nextStepData.FixturedContest, 
                LobyyData: this.props.location.state.nextStepData.LobyyData, 
                lineupPath: this.props.location.state.nextStepData.lineupPath,
                resetIndex: 2,
                isReverseF: this.props.location.state.nextStepData.isReverseF || false,
                isSecIn: this.props.location.state.nextStepData.isSecIn || false ,isShare: true,
                isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced
            };
            this.props.history.push({ pathname: this.props.location.state.nextStepData.lineupPath, state: {nextStepData:nextStepData} })

        }
        else {
            this.props.history.push('/lobby')
        }
    }
}

    /**
     * @description Render UI component
    */
    render() {
        const {
            formValid,
            posting,
            userName,
        } = this.state;
        const HeaderOption = {
            back: true,
            filter: false,
            
            hideShadow: true,
            isOnb: true,
        }
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container bg-white registration-web-container set-pwd-wrap">
                        {this.state.posting && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.pickusername.title}</title>
                            <meta name="description" content={MetaData.pickusername.description} />
                            <meta name="keywords" content={MetaData.pickusername.keywords}></meta>
                        </Helmet>
                        
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                      
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages inner-top-spacing" id='pickUsernameForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading-lg">
                                            {AppLabels.PICK_USER_NAME}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {AppLabels.USERNAME_UNIQUE}
                                        </div>
                                    </Col>
                                </Row>
                                <Row className="vertical-center-section">
                                    <Col xs={12} className={"vertical-center-element" + (this.state.showError ? ' show-error-msg' : '')}>
                                        <FormGroup
                                            className='input-label-center'
                                            controlId="formBasicText"
                                            validationState={this.getValidationState('userName', userName)}>
                                            {this.state.isUserName &&
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyle : inputStyle}
                                                    id='userName'
                                                    name='userName'
                                                    maxLength={25}
                                                    placeholder={AppLabels.USER_NAME}
                                                    type='text'
                                                    value={userName}
                                                    onChange={this.handleChange}
                                                />
                                            }
                                        </FormGroup>
                                        {this.state.showError &&
                                            <div className="error-text">{this.state.errorMsg}</div>
                                        }
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