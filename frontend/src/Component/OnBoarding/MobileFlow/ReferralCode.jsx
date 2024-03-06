import React from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import Validation from '../../../helper/Validation';
import WSManager from "../../../WSHelper/WSManager";
import { updateSignupData, getReferralData } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import FloatingLabel from 'floating-label-react';
import { inputStyle, darkInputStyle } from '../../../helper/input-style';
import { _isUndefined, _isNull, Utilities } from '../../../Utilities/Utilities';
import Images from '../../../components/images';
import { BonusCaseModal } from "../../../Modals";
import {DARK_THEME_ENABLE, OnlyCoinsFlow} from "../../../helper/Constants";

var referralCode = "";
export default class ReferralCode extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            referral: '',
            stepName: 'referral',
            userName: '',
            email: '',
            formValid: false,
            posting: false,
            referalData: '',
            showError: false,
            errorMsg: '',
            isShowPopup: false,
        };
    }
    componentDidMount() {
        this.getSignupReferralData()
    }

    /**
      * @description Lifecycle method used for initialization,
      *  get data from locale storage and history
     */
    UNSAFE_componentWillMount() {
        Utilities.setScreenName('referral')
        
        referralCode = WSManager.getReferralCode();
        this.getRefferalCodeofRefferedUser()
        if (!_isNull(referralCode) && !_isUndefined(referralCode) && referralCode != "undefined") {
            this.setState({ referral: referralCode })
        } else {
            this.setState({ referral: "" })
        }
    }

    /** 
      * @description Auto fill referral code and disable input when user comes by referral link
      * */
    getRefferalCodeofRefferedUser() {
        if (referralCode != null && referralCode != "" && !_isUndefined(referralCode)) {
            this.setState({
                referral: referralCode,
                formValid: true
            })
        }
    }

    /**
     * @description check validation of user entered referral code with local regex
     * @param type referral for this screen
     * @param value user entered value
    */
    getValidationState(type, value) {
        return Validation.validate(type, value)
    }
    /**
     * @description handle referral code change and update state variable
     * @param e click event
    */
    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value, showError: false }, this.validateForm);
    }

    /**
     * @description manage form validations
    */
    validateForm() {
        this.setState({ formValid: Validation.validate('referral', this.state.referral) == 'success' });
    }

    /**
     * @description if user enters referral code then submit using below method,
     * @param e- click event
     * after success navigate to next step
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        this.setState({ posting: true });
        let param = {
            "step": this.state.stepName,
            "referral_code": this.state.referral,
            "user_name": this.state.userName,
            "email": this.state.email
        }
        updateSignupData(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    showError: false, posting: false
                })
                this.showBonusPopUp(1);
                WSManager.setReferralCode(this.state.referral);
            } else {
                WSManager.setReferralCode("");
                this.setState({ posting: false, showError: true, errorMsg: responseJson.global_error });
            }
        })
    }
    SkipStep = (params) => {
        Utilities.gtmEventFire('onboarding_flow', {
            flow_type: 'signup',
            screen_no: '3'
        })

        if (Utilities.getMasterData().login_flow === '1') {
            let nextStepData = { data: this.props.location.state.nextStepData.data, FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath, facebook_data: this.props.location.state.nextStepData.facebook_data, google_data: this.props.location.state.nextStepData.google_data,joinContest:this.props.location.state.nextStepData.joinContest,isReverseF: this.props.location.state.nextStepData.isReverseF || false, "referral_code_used": this.state.referral, isSecIn: this.props.location.state.nextStepData.isSecIn,isShare: true,
            isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced};
            this.props.history.push({ pathname: "/pick-username", state: { nextStepData: nextStepData } })
        }
        else {
            let nextStepData = { FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath, facebook_data: this.props.location.state.nextStepData.facebook_data, google_data: this.props.location.state.nextStepData.google_data,isReverseF: this.props.location.state.nextStepData.isReverseF || false, "referral_code_used": this.state.referral, isSecIn: this.props.location.state.nextStepData.isSecIn,isShare: true,
            isPlayingAnnounced: this.props.location.state.nextStepData.isPlayingAnnounced};
            if(Utilities.getMasterData().a_eml == '0'){
                this.props.history.push({ pathname: '/pick-username', state: {nextStepData:nextStepData} })

            }
            else{
                this.props.history.push({ pathname: "/email", state: { nextStepData: nextStepData } })

            }
        }
    }
    
   /**
    * @description This function get referal banifit data
    */
    getSignupReferralData() {
        this.setState({ posting: true });
        getReferralData().then((responseJson) => {
            this.setState({ posting: false });
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    referalData: responseJson.data.referral_data || "",
                    withoutReferalData: responseJson.data.without_referral_data || ""
                }, () => {
                }
                )
            }
        })
        Utilities.setDefaultSport();
    }

    /**
     * @description Render UI component
    */
    renderRflBns = (referalData, referral, showError) => {
        var isCoinAllowed = Utilities.getMasterData().a_coin == "1";
        var coinsAmt = parseInt(referalData.coins || '0');
        var realAmt = parseInt(referalData.real_amount || '0');
        var bonusAmt = parseInt(referalData.bonus_amount || '0');
        var isRFLBA = (coinsAmt > 0 || realAmt > 0 || bonusAmt > 0)
        return (
            <Row className="vertical-center-section">
                <Col xs={12} className={"vertical-center-element" + (showError ? ' show-error-msg' : '')}>

                    {
                        isRFLBA && <div className="referral-banner">
                            <h2>
                                {AppLabels.GET}
                                {
                                    isCoinAllowed && (coinsAmt > bonusAmt) && (coinsAmt > realAmt)
                                        ?
                                        <React.Fragment>
                                            <span className="text-primary coin-img">
                                                <img src={Images.IC_COIN} alt="" />
                                                {coinsAmt}
                                            </span>
                                            {AppLabels.COINS}
                                        </React.Fragment>
                                        :
                                        (bonusAmt && (bonusAmt > realAmt) ?
                                            <React.Fragment>
                                                <span className="text-primary">
                                                    <i className="icon-bonus icon-top-shift-sm"></i>
                                                    {bonusAmt}
                                                </span>
                                                {AppLabels.BONUS_CASH}
                                            </React.Fragment>
                                            :
                                            <React.Fragment>
                                                <span className="text-primary">
                                                    <span>{Utilities.getMasterData().currency_code}</span>
                                                    {realAmt}
                                                </span>
                                                {AppLabels.REAL_CASH}
                                            </React.Fragment>
                                        )
                                }
                            </h2>
                            <div>{AppLabels.ON_ENTERING_YOUR_FRIENDS_REFERRAL_CODE} </div>
                        </div>
                    }

                    <FormGroup
                        className='input-label-center'
                        controlId="formBasicText"
                        validationState={this.getValidationState('referral', referral)}>
                        <FloatingLabel
                            autoComplete='off'
                            styles={ DARK_THEME_ENABLE ? darkInputStyle : inputStyle}
                            id='referral'
                            name='referral'
                            placeholder={AppLabels.ENTER_REFERRAL_CODE}
                            type='text'
                            value={!_isUndefined(referral) ? referral : ""}
                            onChange={this.handleChange}
                        />
                    </FormGroup>
                    {showError &&
                        <div className="error-text">{this.state.errorMsg}</div>
                    }
                </Col>
            </Row>

        )
    }

    renderWRflBns = (withoutReferalData) => {
        var isCoinAllowed = Utilities.getMasterData().a_coin == "1";
        var coinsAmt = parseInt(withoutReferalData.coins || '0');
        var realAmt = parseInt(withoutReferalData.real_amount || '0');
        var bonusAmt = parseInt(withoutReferalData.bonus_amount || '0');
        var isRFLBA = (coinsAmt > 0 || realAmt > 0 || bonusAmt > 0)
        return (
            <Row className="signup-info">
                {
                    isRFLBA && <Col xs={12}>
                        {AppLabels.SIGNUP_INFO}
                        {
                            isCoinAllowed && (coinsAmt > bonusAmt) && (coinsAmt > realAmt)
                                ?
                                <React.Fragment>
                                    <img src={Images.IC_COIN} alt="" />
                                    {coinsAmt}
                                    <span> {AppLabels.COINS}</span>
                                </React.Fragment>
                                :
                                (bonusAmt && (bonusAmt > realAmt) ?
                                    <React.Fragment>
                                        <i className="icon-bonus icon-top-shift-sm"></i>
                                        {bonusAmt}
                                        <span> {AppLabels.BONUS_CASH}</span>
                                    </React.Fragment>
                                    :
                                    <React.Fragment>
                                        <span>{Utilities.getMasterData().currency_code}</span>
                                        {realAmt}
                                        <span> {AppLabels.REAL_CASH}</span>
                                    </React.Fragment>
                                )
                        }
                        {AppLabels.SIGNUP_INFO1}
                    </Col>
                }
            </Row>
        )
    }

    showBonusPopUp = (e) => {
        const { withoutReferalData, referalData } = this.state
        if (!referalData) {
            this.SkipStep()
        } else {

            if (e == 0 && withoutReferalData == undefined || withoutReferalData == '') {
                return;
            }
            let passingData = {
                refData: referalData,
                withoutRefData: withoutReferalData,
                isSkip: e,
            };
            try {
                
                this.setState({
                    passingData: passingData,
                }, () => {
                    if (e == 0) {
                        const max = Math.max.apply(null, Object.values(withoutReferalData));
                        this.setState({
                            isShowPopup: max > 0 ? true : false,
                        }, () => {
                            if(max == 0) {
                                this.SkipStep()
                            }
                        })
                    } else {
                        const _max = Math.max.apply(null, Object.values(referalData));
                        this.setState({
                            isShowPopup: _max > 0 ? true : false,
                        }, () => {
                            if(_max == 0) {
                                this.SkipStep()
                            }
                        })
                    }
                })
            } catch (error) {
                this.SkipStep()
            }
        }
    }

    render() {
        const {
            referral,
            formValid,
            posting,
            referalData,
            withoutReferalData
        } = this.state;
        return (

            <MyContext.Consumer>
                {(context) => (

                    <div className="web-container bg-white pb-0 registration-web-container">

                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.referral.title}</title>
                            <meta name="description" content={MetaData.referral.description} />
                            <meta name="keywords" content={MetaData.referral.keywords}></meta>
                        </Helmet>
                        {/* <div className="registration-header header-wrap">
                            <Row>
                                <Col xs={12} className="text-right">
                                    <span className="header-action skip-step skip-step-view" onClick={() => this.showBonusPopUp(0)}>
                                        {AppLabels.SKIP_STEP}
                                    </span>
                                </Col>
                            </Row>
                        </div> */}
                        <div className="skip-step-container">
                            <div className='skip-step-view' onClick={() => this.showBonusPopUp(0)}>
                            {AppLabels.SKIP_STEP}
                            </div>
                        </div>
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages referral-page" id='referralCodeForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading-lg m-t-10">
                                            {AppLabels.HAVE_A_REFERRAL_CODE}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {OnlyCoinsFlow == 1 ? AppLabels.REFERRAL_CODE_TEXT_COIN : AppLabels.REFERRAL_CODE_TEXT}
                                        </div>
                                    </Col>
                                </Row>
                                {
                                    this.renderRflBns(referalData, referral, this.state.showError)
                                }
                                <Row className="btm-fixed-submit">
                                    <Col xs={12} className="text-center">
                                        <button className="submit-otp" disabled={!formValid || posting} type='submit'><i className="icon-next-btn"></i></button>
                                    </Col>
                                </Row>
                                {
                                    withoutReferalData &&
                                    this.renderWRflBns(withoutReferalData)
                                }
                            </div>

                        </form>
                        {
                            
                            this.state.isShowPopup ? <BonusCaseModal SkipStep={this.SkipStep} data={this.state.passingData} /> : ''
                        }

                    </div>


                )}
            </MyContext.Consumer>
        );
    }
}
