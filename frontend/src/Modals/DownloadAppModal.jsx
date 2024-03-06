import React, { Suspense, lazy } from 'react';
import { Row, Col, Checkbox, Modal, FormGroup } from 'react-bootstrap';
import { NavLink } from "react-router-dom";
import { getAppStoreLink } from '../WSHelper/WSCallings';
import * as AppLabels from "../helper/AppLabels";
import * as WSC from "../WSHelper/WSConstants";
import { DEFAULT_COUNTRY_CODE, ONLY_SINGLE_COUNTRY } from '../helper/Constants';
import { Utilities } from '../Utilities/Utilities';
const ReactCaptcha = lazy(()=>import('../Component/CustomComponent/ReactCaptcha'));
const CustomPhoneInput = lazy(()=>import('../Component/CustomComponent/CustomPhoneInput'));

export default class DownloadAppModal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            code: DEFAULT_COUNTRY_CODE,
            smsChecked: false,
            posting: false,
            captchaToken: ''
        }
    }

    onCaptchaChange = (value) => {
        this.setState({
            captchaToken: value
        })
    }

    callDownloadAppApi = (e) => {
        e.preventDefault();
        setTimeout(() => {
            let phone_code_str = "+" + this.state.code;
            let phone_no = this.state.phone.replace(phone_code_str, "");
            let param = {
                "phone_no": phone_no,
                "phone_code": this.state.code,
                "source_str": Utilities.getCpSessionPath()
            }
            if (process.env.REACT_APP_CAPTCHA_ENABLE == 1) {
                param['token'] = this.state.captchaToken;
            }
            getAppStoreLink(param).then((responseJson) => {
                if (responseJson.response_code === WSC.successCode) {
                    Utilities.gtmEventFire('send_download_link')
                    this.closePopup()
                }
            })
        }, 200);
    }

    closePopup = () => {
        this.setState({ phone: '', smsChecked: false }, () => {
            this.props.handleClose();
        })
    }

    handleOnChange = (value, data) => {
        if (ONLY_SINGLE_COUNTRY == 1 && value.startsWith('+' + DEFAULT_COUNTRY_CODE)) {
            this.setState({ code: data.dialCode, phone: value })
        } else if (ONLY_SINGLE_COUNTRY == 0) {
            this.setState({ code: data.dialCode, phone: value })
        } else {
            this.setState({ code: DEFAULT_COUNTRY_CODE, phone: '' })
        }
    }

    render() {
        let { show } = this.props;
        return (
            <Modal show={show} onHide={this.closePopup} backdropClassName="modal-download-app" className="modal-download-app-dialog">
                {
                            process.env.REACT_APP_CAPTCHA_ENABLE == 1 && !this.state.posting && <Suspense fallback={<div />} ><ReactCaptcha
                                verifyCallback={this.onCaptchaChange}
                            /></Suspense>
                        }
                <Modal.Header closeButton>
                    <Modal.Title>
                        {AppLabels.DOWNLOAD_APP}
                    </Modal.Title>
                </Modal.Header>
                <form id="downloadAppDialog" onSubmit={this.callDownloadAppApi}>
                    <Modal.Body>
                        <div className="download-app-body text-center">
                            <Row>
                                <Col xs={12}>
                                    <FormGroup>
                                        <Suspense fallback={<div />} >
                                            <CustomPhoneInput
                                                {...this.props}
                                                phone={this.state.phone}
                                                handleOnChange={this.handleOnChange} />
                                        </Suspense>
                                    </FormGroup>
                                </Col>
                            </Row>

                            <div className="text-small m-t-20 sms-checkbox">
                                <FormGroup>
                                    <Checkbox className="custom-checkbox" value=""
                                        onClick={() => this.setState({
                                            smsChecked: !this.state.smsChecked
                                        })}
                                        name="all_leagues" id="all_leagues">
                                        <span className="auth-txt sm">
                                            <NavLink
                                                target='_blank'
                                                exact to="/terms-condition">{AppLabels.TERMS_CONDITION}</NavLink>
                                        </span>
                                    </Checkbox>
                                </FormGroup>
                            </div>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type='submit' 
                            disabled={!this.state.smsChecked || !this.state.phone}
                            className={"btn btn-primary" + (!this.state.smsChecked || !this.state.phone ? ' click-disabled' : '')}>{AppLabels.GET_LINK_NOW}</button>
                    </Modal.Footer>
                </form>
            </Modal>
        )
    }
}