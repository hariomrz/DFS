import React, { Component, Fragment } from "react";
import { Row, Col, Button, Input } from 'reactstrap';
import _ from 'lodash';
import * as NC from '../../helper/NetworkingConstants';
import WSManager from "../../helper/WSManager";
import { PASS_LENGTH_MSG } from '../../helper/Message'
var md5 = require('md5');
class ChangePassword extends Component {
    constructor(props) {
        super(props)
        this.state = {
            OldPassword: '',
            NewPassword: '',
            ConfirmPassword: '',
            OldPasswordMsg: true,
            NewPasswordMsg: true,
            ConfirmPasswordMsg: true,
            passNotMatch: false,
            formValid: false,
            PsssMsgType: '',
            PasswordMsg: '',
            SubmitPosting: false,
        }
    }
    handleInputChange = e => {
        let name = e.target.name
        let value = e.target.value
        this.setState({ [name]: value }, () => this.validateForm(name, value))
    }

    validateForm = (name, value) => {
        let { OldPassword, NewPassword, ConfirmPassword } = this.state
        let validOldPassword = OldPassword
        let validNewPassword = NewPassword
        let validConfirmPassword = ConfirmPassword

        switch (name) {
            case 'OldPassword':
                validOldPassword = (value.trim().length > 5) ? true : false
                this.setState({ OldPasswordMsg: validOldPassword })
                break;
            case 'NewPassword':
                validNewPassword = (value.trim().length > 5 && validNewPassword === validConfirmPassword) ? true : false
                this.setState({ NewPasswordMsg: validNewPassword })
                break;
            case 'ConfirmPassword':
                validConfirmPassword = (value.trim().length > 5 && validNewPassword === validConfirmPassword) ? true : false
                this.setState({ ConfirmPasswordMsg: validConfirmPassword })
                break;

            default:
                break;
        }
        this.setState({ formValid: validOldPassword && validNewPassword && validConfirmPassword })
    }

    handleSubmit = () => {
        let { OldPassword, NewPassword, ConfirmPassword } = this.state
        this.setState({ SubmitPosting : true })
        let param = {
            old_password: md5(OldPassword),
            new_password: md5(NewPassword),
            confirm_password: md5(ConfirmPassword),
        }
        let URL = NC.baseURL + NC.CHANGE_PASSWORD
        fetch(URL, {
            method: 'POST',
            body: JSON.stringify(param),
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json;charset=UTF-8',
                Sessionkey: WSManager.getToken()
            },
        }).then((response) => {
            return response.json()
        }).then((ResponseJson) => {
            if (ResponseJson.response_code == NC.successCode) {
                this.setState({
                    OldPassword: '',
                    NewPassword: '',
                    ConfirmPassword: '',
                    formValid: false,
                    PsssMsgType: 'success',
                    PasswordMsg: ResponseJson.message,
                    SubmitPosting: false
                })                
            } else {
                this.setState({
                    PsssMsgType: 'error',
                    PasswordMsg: ResponseJson.message,
                    SubmitPosting: false
                })
            }
            setTimeout(() => {
                this.setState({
                    PasswordMsg: '',
                    PsssMsgType: '',
                })
            }, 5000);
        }).catch((error) => {
        })
    }

    render() {
        let { SubmitPosting, PsssMsgType, PasswordMsg, OldPasswordMsg, formValid, OldPassword, NewPassword, ConfirmPassword } = this.state
        return (
            <Fragment>
                <Row>
                    <Col md={12}>
                        {
                            (WSManager.getRole() > 1) &&
                            <label className="backtofixtures float-right mt-30" onClick={() => this.props.history.goBack()}> {'<'} Back to Distributors</label>
                        }
                    </Col>
                </Row>

                <div className="change-password">
                    <Row>
                        <Col md={12}>
                            <h2 className="h2-cls">Change Password</h2>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <div className={`password-msg ${PsssMsgType === 'success' ? 'c-success' : PsssMsgType === 'error' ? 'c-error' : ''}`}>{PasswordMsg}</div>
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <label>Old Password<span className="asterrisk"> *</span></label>
                            <Input
                                maxLength="30"
                                type="password"
                                name="OldPassword"
                                value={OldPassword}
                                onChange={this.handleInputChange}
                            />
                            {!OldPasswordMsg &&
                                <span className="color-red">
                                    Please enter valid password.
                                </span>
                            }
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <label>New Password<span className="asterrisk"> *</span><sub> ({PASS_LENGTH_MSG})</sub></label>
                            <Input
                                maxLength="30"
                                type="password"
                                name="NewPassword"
                                value={NewPassword}
                                onChange={this.handleInputChange}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col md={12}>
                            <label>Confirm Password<span className="asterrisk"> *</span></label>
                            <Input
                                maxLength="30"
                                type="password"
                                name="ConfirmPassword"
                                value={ConfirmPassword}
                                onChange={this.handleInputChange}
                            />
                        </Col>
                    </Row>
                    <Row className="text-center">
                        <Col md={12}>
                            <Button
                                disabled={!formValid || SubmitPosting}
                                onClick={this.handleSubmit}
                            >
                                Submit
                            </Button>
                        </Col>
                    </Row>
                </div>
            </Fragment>
        )
    }
}
export default ChangePassword

