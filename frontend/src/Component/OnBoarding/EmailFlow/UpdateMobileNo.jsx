import React, { Suspense, lazy } from 'react';
import { Row, Col, FormGroup } from 'react-bootstrap';
import * as AppLabels from "../../../helper/AppLabels";
import WSManager from "../../../WSHelper/WSManager";
import { updateSignupData } from "../../../WSHelper/WSCallings";
import * as WSC from "../../../WSHelper/WSConstants";
import { MyContext } from '../../../InitialSetup/MyProvider';
import CustomHeader from '../../../components/CustomHeader';
import { DEFAULT_COUNTRY_CODE, ONLY_SINGLE_COUNTRY } from '../../../helper/Constants';
import { Utilities } from '../../../Utilities/Utilities';
const CustomPhoneInput = lazy(()=>import('../../CustomComponent/CustomPhoneInput'));

export default class UpdateMobileNo extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            phone: '',
            code: DEFAULT_COUNTRY_CODE,
            posting: false,
        };
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
            }
            if(WSManager.getAffiliatCode()){
                param['affcd'] = WSManager.getAffiliatCode();
            }
            updateSignupData(param).then((responseJson) => {
                this.setState({ posting: false });
                if (responseJson.response_code === WSC.successCode) {
                    this.gotoDetails(responseJson.data);
                }
            })
        }
    }

    gotoDetails = (data) => {


        if (this.props.location.state.data && this.props.location.state.data.next_step == 'phone') {
            this.props.history.push({ pathname: '/verify', state: { data: data, facebook_data: this.props.location.state.facebook_data, google_data: this.props.location.state.google_data } })
        }
        else {
            WSManager.setToken(WSManager.getTempToken('id_temp_token'));
            if (this.props.location.state.nextStepData && this.props.location.state.nextStepData.FixturedContest) {
                this.props.history.replace({ pathname: this.props.location.state.nextStepData.lineupPath, state: { FixturedContest: this.props.location.state.nextStepData.FixturedContest, LobyyData: this.props.location.state.nextStepData.LobyyData, lineupPath: this.props.location.state.nextStepData.lineupPath } })
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
            
            hideShadow: true,
            isOnb: true,
        }

        const {
            phone
        } = this.state;
        return (
            <MyContext.Consumer>
                {(context) => (


                    <div className="web-container bg-white set-pwd-wrap">
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />

                        <form onSubmit={this.onSubmit} className="onboarding-inner-pages inner-top-spacing onboarding-relative" id='updateMobileNoForm'>

                            {!(this.props.location.state.nextStepData.data && this.props.location.state.nextStepData.data.next_step == 'phone') &&
                                <a href className="skip-step mobile-skip" onClick={() => this.gotoDetails()}>{AppLabels.SKIP_STEP}</a>
                            }
                            <div className="verification-block">
                                <Row>
                                    <Col>
                                        <div className="onboarding-page-heading-lg">
                                            {AppLabels.YOUR_MOBILE_NUMBER}
                                        </div>
                                        <div className="onboarding-page-desc">
                                            {Utilities.getMasterData().int_version == 1 ? AppLabels.YOUR_MOBILE_NUMBER_TEXT_INT : AppLabels.YOUR_MOBILE_NUMBER_TEXT}
                                        </div>
                                    </Col>
                                </Row>
                                {/* NATIVE FORM */}
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
                                        <div className="optional-text">
                                            {AppLabels.OPTIONAL}
                                        </div>
                                    </Col>
                                </Row>
                                <Row className="text-center btm-fixed-submit">
                                    <Col xs={12}>
                                        <button className="submit-otp" disabled={!(phone && Utilities.isValidPhoneNumber(phone))} type='submit'><i className="icon-next-btn"></i></button>
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