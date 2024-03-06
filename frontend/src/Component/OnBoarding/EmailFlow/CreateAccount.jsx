import React, { Suspense, lazy } from 'react';
import { Row, Col, FormGroup, Button, Checkbox, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { updateSignupData, getReferralData } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import * as AppLabels from "../../../helper/AppLabels";
import { MyContext } from '../../../InitialSetup/MyProvider';
import { Helmet } from "react-helmet";
import MetaData from "../../../helper/MetaData";
import CustomLoader from '../../../helper/CustomLoader';
import md5 from 'md5';
import { inputStyleLeft, darkInputStyleLeft, inputStyle } from '../../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import CustomHeader from '../../../components/CustomHeader';
import * as Constants from "../../../helper/Constants";
import { Utilities, _isObject } from '../../../Utilities/Utilities';
import { DARK_THEME_ENABLE } from '../../../helper/Constants';
import WSManager from '../../../WSHelper/WSManager';
import { BonusCaseModal } from '../../../Modals';
import Validation from '../../../helper/Validation';

const CustomPhoneInput = lazy(() => import('../../CustomComponent/CustomPhoneInput'));


let error = undefined;
export default class CreateAccount extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            phone: '',
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            formValid: false,
            error: AppLabels.PLEASE_ENTER_NEW_PASSWORD,
            isLoading: false,
            showPassword: false,
            showConfirmPassword: false,
            enterUserName: '',
            passwordMatch: true,
            passwordValidate: true,
            isChecked: false,
            code: '',
            createData: '',
            isShowPopup: false,
            usernameError: true,
            newPasswordError: true,
            email: '',
            referralError: true,
            referral_code: '',
            bsL: ''

        };
    }

    componentDidMount() {
        if (Utilities.getMasterData().bs_a == 1) {
            var banStates = localStorage.getItem('bslist');
            var bsL = banStates.length;
            this.setState({
                c: bsL
            })
        }
        this.getSignupReferralData()
    }


    /**
     * @description handle email change and update state variable
     * @param e click event
    */
    handleChange = (e, val) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value }, this.validateForm);

    }

    validateForm() {
        this.setState({ formValid: this.isValid(false), error: error });
    }


    /**
     * @description This function will check all fields are valid or not
     * @returns Boolean: either valid or not 
    */
    isValid = (notifyAllowed) => {

        let { confirmPassword, newPassword, enterUserName, isChecked, phone_no, referral_code, phone } = this.state;

        if (newPassword != '') {
            if (newPassword.length < 8 || newPassword.length > 50) {
                this.setState({
                    newPasswordError: false
                })
                return false
            }
            else {
                this.setState({
                    newPasswordError: true
                })
            }
        }

        if (enterUserName != '') {
            if (enterUserName.length < 3 || enterUserName.length > 25) {
                this.setState({
                    usernameError: false
                })
                // return false
            }
            else {
                this.setState({
                    usernameError: true
                })
            }
        }

        if (confirmPassword != '') {
            if (newPassword === confirmPassword) {
                this.setState({
                    passwordMatch: true
                })
            }
            else {
                this.setState({
                    passwordMatch: false
                })
                return false;
            }
        }

        // if (phone != '') {
        //     Utilities.isValidPhoneNumber(phone)
        // }


        if (referral_code != '') {
            if (referral_code.length < 6 || referral_code.length > 10) {
                this.setState({
                    referralError: false
                })
                return false
            }
            else {
                this.setState({
                    referralError: true
                })
            }
        }





        if (enterUserName == '' || newPassword == '' || confirmPassword == '') {
            return false;
        }


        error = '';
        return true;
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
    /**
     * @description  this method update user email to server
     * @param e- click event
     * after success navigate to next step
     * **/
    onSubmit = (e) => {
        e.preventDefault();
        let { enterUserName, referral_code, confirmPassword, code, phone } = this.state

        let cp = localStorage.getItem('cp') ? JSON.parse(localStorage.getItem('cp')) : ''


        let phone_code_str = "+" + code;
        let phone_no_str = this.state.phone;
        let phone_no = phone_no_str.replace(phone_code_str, "");

        let refCode = referral_code || localStorage.getItem('referralCode')

        this.setState({ isLoading: true });
        let param = {
            "user_name": enterUserName,
            "password": md5(confirmPassword),
            "phone_no": phone_no,
            "phone_code": code,
            "referral_code": refCode,
            "affcd": localStorage.getItem('affcd') ? localStorage.getItem('affcd') : '',
            "campaign_code": cp.campaign_code ? cp.campaign_code : '',
            "visit_code": cp.visit_code ? cp.visit_code : ''
        }

        updateSignupData(param).then((responseJson) => {
            this.setState({ isLoading: false });

            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 3000);
                this.setState({
                    createData: responseJson.data
                })
                WSManager.setToken(WSManager.getTempToken('id_temp_token'))
                this.showBonusPopUp(refCode == '' ? 0 : 1)
            }
        })

    }


    // showBonusPopUp = (e) => {
    //     const { withoutReferalData, referalData } = this.state
    //     if (!referalData) {
    //         this.SkipStep()
    //     } else {

    //         let passingData = {
    //             refData: referalData,
    //             withoutRefData: withoutReferalData,
    //             isSkip: 1,
    //         };
    //         try {
    //             this.setState({
    //                 passingData: passingData,
    //             }, () => {
    //                 const _max = Math.max.apply(null, Object.values(referalData));
    //                 this.setState({
    //                     isShowPopup: _max > 0 ? true : false,
    //                 }, () => {
    //                     if (_max == 0) {
    //                         this.SkipStep()
    //                     }
    //                 })
    //             })
    //         } catch (error) {
    //             this.SkipStep()
    //         }
    //     }
    // }

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
                            if (max == 0) {
                                this.SkipStep()
                            }
                        })
                    } else {
                        const _max = Math.max.apply(null, Object.values(referalData));
                        this.setState({
                            isShowPopup: _max > 0 ? true : false,
                        }, () => {
                            if (_max == 0) {
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
    SkipStep = () => {
        let { sports_hub } = Utilities.getMasterData()
        if (this.props.location.state.nextStepData && this.props.location.state.nextStepData.FixturedContest) {
            this.props.history.replace({ pathname: this.props.location.state.nextStepData.lineupPath, state: { FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath, isReverseF: this.props.location.state.nextStepData.isReverseF } })
        }
        else if (sports_hub.length > 2) {
            this.props.history.push('/sports-hub')
        }
        else {
            this.props.history.push('/lobby')
        }
    }

    getValidationState(type, value) {
        return Validation.validate(type, value)
    }

    getSignupReferralData() {
        this.setState({ posting: true });
        getReferralData().then((responseJson) => {
            this.setState({ posting: false });
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    referalData: responseJson.data.referral_data || "",
                    withoutReferalData: responseJson.data.without_referral_data || ""
                }
                )
            }
        })
        Utilities.setDefaultSport();
    }
    /**
     * @description Render UI component
    */
    handleChecked = (e) => {
        this.setState({
            isChecked: !this.state.isChecked
        }, () => {
            this.validateForm()
        })
    }

    checkBtnDisable = () => {
        // let valid = false
        if (this.state.isChecked && (this.state.formValid || !this.state.isLoading)) {
            if (this.state.phone == '') {
                return false
            }
            if (this.state.phone != '' && !Utilities.isValidPhoneNumber(this.state.phone)) {
                return true
            }
            return false
        }
        else {
            return true
        }
        // (isChecked && (!formValid || isLoading) && (phone == '' || (phone != '' && Utilities.isValidPhoneNumber(phone))))
    }

    render() {

        const HeaderOption = {
            back: true,
            filter: false,
            title: AppLabels.CREATE_ACCOUNT,
            hideShadow: true,
            isOnb: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        const {
            enterUserName,
            newPassword,
            confirmPassword,
            formValid,
            isLoading,
            showPassword,
            showConfirmPassword,
            referral_code, phone,
            passwordMatch,
            isChecked,
            usernameError,
            newPasswordError,
            email,
            referalData,
            referralError,
            // bsL
        } = this.state;


        let refCode = localStorage.getItem('referralCode')
        let propsData = this.props.location && this.props.location.state && this.props.location.state.nextStepData && this.props.location.state.nextStepData.data && this.props.location.state.nextStepData.data.user_profile
        let banStates = Utilities.getMasterData().bs_a == 1 ? localStorage.getItem('bslist') ? JSON.parse(localStorage.getItem('bslist')) : {} : ''
        banStates = Object.values(banStates)
        let bsL = Utilities.getMasterData().bs_a == 1 ? banStates && banStates.length : '';

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container bg-white single-signon-create-account">
                        {isLoading && <CustomLoader />}
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.createaccount.title}</title>
                            <meta name="description" content={MetaData.createaccount.description} />
                            <meta name="keywords" content={MetaData.createaccount.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <form onSubmit={this.onSubmit} className="mt-4 pt-5" id='changePwdForm'>
                            <div className="verification-block">
                                <Row>
                                    {/* {(propsData.google_data != null || propsData.facebook_data != null || propsData.facebook_data.email != undefined) && <Col xs={12} className='form-col'>
                                        <FormGroup
                                            className='input-label-center-align'
                                            controlId="formBasicText"
                                            validationState={this.getValidationState('email', email)}>
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={inputStyle}
                                                id='email'
                                                name='email'
                                                // value={}
                                                placeholder={AppLabels.EMAIL}
                                                type='email'
                                                onChange={this.handleChange}
                                            />
                                        </FormGroup>
                                    </Col>} */}
                                    <Col xs={12} className="form-col">
                                        <FormGroup
                                            className='input-label-center-align'
                                            controlId="formBasicText"
                                        >
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='enterUserName'
                                                name='enterUserName'
                                                value={enterUserName}
                                                placeholder={AppLabels.USER_NAME}
                                                type='text'
                                                onChange={this.handleChange}
                                            />

                                            {!usernameError && <p className='error-red'>{AppLabels.USERNAME_LENGTH}</p>}
                                        </FormGroup>
                                    </Col>
                                    <Col xs={12} className="form-col">
                                        <FormGroup
                                            className='input-label-center-align spc-input'
                                            controlId="formBasicText"
                                        >
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='newPassword'
                                                name='newPassword'
                                                value={newPassword}
                                                placeholder={AppLabels.PASSWORD}
                                                type={showPassword ? 'text' : 'password'}
                                                onChange={this.handleChange}
                                                className="password"
                                            />
                                            {!newPasswordError && <p className={'error-red mb-0'}>{AppLabels.NEW_PASSWORD_MIN_MAX_LENGTH}</p>}
                                        </FormGroup>
                                        <a href onClick={() => this.setState({ showPassword: !showPassword })} className="pwd-show-hide">
                                            <i className={showPassword ? "icon-eye" : "icon-eye-cancel"}></i>
                                        </a>

                                    </Col>
                                    <Col xs={12} className="form-col">
                                        <FormGroup
                                            className='input-label-center-align spc-input'
                                            controlId="formBasicText"
                                        >
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='confirmPassword'
                                                name='confirmPassword'
                                                value={confirmPassword}
                                                placeholder={AppLabels.CONFIRM_PASSWORD}
                                                type={showConfirmPassword ? 'text' : 'password'}
                                                onChange={(e) => this.handleChange(e, 'cp')}
                                                className="password"
                                            />
                                            {!passwordMatch && <p className='error-red'>{AppLabels.PASSWORD_NOT_MATCH}</p>}
                                        </FormGroup>
                                        <a href onClick={() => this.setState({ showConfirmPassword: !showConfirmPassword })} className="pwd-show-hide">
                                            <i className={showConfirmPassword ? "icon-eye" : "icon-eye-cancel"}></i>
                                        </a>
                                    </Col>

                                    {Utilities.getMasterData().a_mbl == "1" && <Col xs={12} className='phone-number-style form-col mt-2'>
                                        <Suspense fallback={<div />} >
                                            <CustomPhoneInput {...this.props} phone={phone} phonePreValue={propsData.phone_no} handleOnChange={this.handleOnChange} isFrom={'create-acc'} />
                                        </Suspense>
                                    </Col>}


                                    {
                                        !WSManager.getAffiliatCode() &&
                                        <Col xs={12} className="form-col">
                                            <h4 className='referral-code'>{AppLabels.HAVE_A_REFERRAL_CODE}</h4>
                                            <FormGroup
                                                className='input-label-center-align'
                                                controlId="formBasicText"
                                                validationState={this.getValidationState('referral_code', referral_code)}
                                            >
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='referral_code'
                                                    name='referral_code'
                                                    value={refCode ? refCode : referral_code}
                                                    placeholder={AppLabels.REFERRAL_CODE}
                                                    type='text'
                                                    onChange={this.handleChange}
                                                />
                                                {!referralError && <p className='error-red'>{AppLabels.PLEASE_ENTER_A_VALID_REFERRAL_CODE}</p>}
                                            </FormGroup>
                                        </Col>
                                    }


                                    <Row className='form-col mt-4'>
                                        <Col>
                                            <FormGroup>
                                                <Checkbox
                                                    name='validate'
                                                    className="custom-validate-check text-right"
                                                    onChange={this.handleChecked}
                                                    checked={isChecked}
                                                >
                                                </Checkbox>
                                            </FormGroup>
                                        </Col>

                                        <Col xs={11}>
                                            {Utilities.getMasterData().bs_a == 1 ?
                                                <p className="auth-txt" onClick={this.handleChecked} style={{ marginTop: 0, padding: 0 }}>
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
                                                        }>
                                                        </OverlayTrigger>}
                                                        {
                                                            AppLabels.and_I_am_not_txt
                                                        }
                                                    </>
                                                    }
                                                </p>
                                                :
                                                <p className="auth-txt" onClick={this.handleChecked} style={{ marginTop: 0, padding: 0 }}>
                                                    {
                                                        Utilities.getMasterData().a_age_limit == 1 ?
                                                            AppLabels.I_hereby_confirm
                                                            :
                                                            AppLabels.I_agree_to
                                                    }
                                                    <a className='primary' target='_blank' href="/terms-condition" onClick={(event) => event.stopPropagation()}> {AppLabels.TANDC_TITLE} </a>
                                                </p>
                                            }
                                        </Col>
                                    </Row>

                                    <Col xs={12}>
                                        <Button className="btn-block"
                                            disabled={this.checkBtnDisable()}
                                            // disabled={(isChecked && (!formValid || isLoading))} 
                                            bsStyle="primary" type='submit'>{AppLabels.SUBMIT}
                                        </Button>
                                    </Col>
                                </Row>
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