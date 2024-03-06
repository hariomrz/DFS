import React, { Suspense, lazy } from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import * as AppLabels from "../../../helper/AppLabels";
import WSManager from "../../../WSHelper/WSManager";
import { userLogin, updateSignupData } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import { MyContext } from '../../../InitialSetup/MyProvider';
import CustomHeader from '../../../components/CustomHeader';
import { DEFAULT_COUNTRY_CODE, ONLY_SINGLE_COUNTRY } from '../../../helper/Constants';
import { Utilities } from '../../../Utilities/Utilities';
const ReactCaptcha = lazy(()=>import('../../CustomComponent/ReactCaptcha'));
const CustomPhoneInput = lazy(()=>import('../../CustomComponent/CustomPhoneInput'));

export default class PickMobileNo extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            phone: '',
            code: DEFAULT_COUNTRY_CODE,
            formValid: false,
            posting: false,
            captchaToken: '',

        };
    }

    onCaptchaChange = (value) => {
        this.setState({
            captchaToken: value
        })
    }

    handleChange = (value, data) => {
        if (ONLY_SINGLE_COUNTRY == 1 && value.startsWith('+' + DEFAULT_COUNTRY_CODE)) {
            this.setState({ code: data.dialCode, phone: value })
        } else if (ONLY_SINGLE_COUNTRY == 0) {
            this.setState({ code: data.dialCode, phone: value })
        } else {
            this.setState({ code: DEFAULT_COUNTRY_CODE, phone: '' })
        }
    }

    onSubmit = (e) => {
        e.preventDefault();
        if (!this.state.posting) {
            this.setState({ posting: true });
            let phone_code_str = "+" + this.state.code;
            let phone_no_str = this.state.phone;
            let phone_no = phone_no_str.replace(phone_code_str, "");
            let param = {
                "step": 'mobile',
                "phone_no": phone_no,
                "phone_code": this.state.code,
                "device_type": Utilities.getDeviceType(),
                "device_id": WSC.deviceID,
                ...Utilities.getCpSession()
            }
            if (process.env.REACT_APP_CAPTCHA_ENABLE == 1) {
                param['token'] = this.state.captchaToken;
            }
            if(WSManager.getAffiliatCode()){
                param['affcd'] = WSManager.getAffiliatCode();
            }
            const { nextStep } = this.props.location.state.nextStepData;

            if (nextStep && nextStep === 'phone') {
                userLogin(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson.response_code === WSC.successCode) {
                        this.gotoDetails(responseJson.data);
                    }
                })
            } else {
                updateSignupData(param).then((responseJson) => {
                    this.setState({ posting: false });
                    if (responseJson.response_code === WSC.successCode) {
                        this.gotoDetails(responseJson.data);
                    }
                })
            }
        }
    }

    gotoDetails = (data) => {

        if (this.props.location.state.nextStepData.data && this.props.location.state.nextStepData.data.next_step == 'phone') {
            let nextStepData = { data: data, facebook_data: this.props.location.state.nextStepData.facebook_data, google_data: this.props.location.state.nextStepData.google_data };
            this.props.history.push({ pathname: '/verify', state: { nextStepData: nextStepData } })
        }
        else {
            WSManager.setToken(WSManager.getTempToken('id_temp_token'));
            if (this.props.location.state.nextStepData.FixturedContest) {
                let nextStepData = { FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath };
                this.props.history.replace({ pathname: this.props.location.state.nextStepData.lineupPath, state: { nextStepData: nextStepData } })
            }
            else {
                this.props.history.replace('/lobby')
            }
        }
    }

    render() {
        const HeaderOption = {
            back: true,
            filter: false,
            title: "",
            hideShadow: true,
            isOnb: true,
        }

        const {
            phone
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (


                    <div className="web-container bg-white">
                        {
                            process.env.REACT_APP_CAPTCHA_ENABLE == 1 && !this.state.posting && <Suspense fallback={<div />} ><ReactCaptcha
                                verifyCallback={this.onCaptchaChange}
                            /></Suspense>
                        }
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages inner-top-spacing onboarding-relative" id='pickMobileNoForm'>
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading-lg">
                                            {AppLabels.YOUR_MOBILE_NUMBER}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {AppLabels.YOUR_MOBILE_NUMBER_TEXT}
                                        </div>
                                    </Col>
                                </Row>
                                <Row className="vertical-center-section">
                                    <Col xs={12} className="vertical-center-element">
                                        <FormGroup className="m-b-15">
                                            <Suspense fallback={<div />} >
                                                <CustomPhoneInput
                                                    {...this.props}
                                                    phone={phone}
                                                    handleOnChange={this.handleChange} />
                                            </Suspense>
                                        </FormGroup>
                                    </Col>
                                </Row>

                                <Col xs={12}>
                                    <button className="submit-otp" disabled={!(phone && Utilities.isValidPhoneNumber(phone))} type='submit'><i className="icon-next-btn"></i></button>
                                </Col>

                            </div>
                        </form>
                    </div>
                )}
            </MyContext.Consumer>
        );
    }
}