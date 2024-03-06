import React, { Component } from 'react'
import {
  Button,
  Card,
  CardBody,
  CardGroup,
  Col,
  Container,
  Form,
  Input,
  InputGroup,
  Row,
  Modal,
  ModalHeader,
  ModalBody,
  ModalFooter,
  Label,
  FormGroup,
} from 'reactstrap'
import * as NC from '../../../helper/NetworkingConstants'
import WSManager from '../../../helper/WSManager'
import { notify } from 'react-notify-toast'
import { Base64 } from 'js-base64'
import Notification from 'react-notify-toast'
import logo from '../../../assets/img/brand/logo.png'
import HF, { _isEmpty, _isUndefined } from '../../../helper/HelperFunction'
import Countdown from 'react-countdown-now'
import OtpInput from 'react-otp-input'
var md5 = require('md5')

class Login extends Component {
  constructor(props) {
    super(props)
    this.state = {
      email: '',
      password: '',
      formErrors: {
        email: '',
        password: '',
      },
      emailValid: false,
      passwordValid: false,
      formValid: false,
      otpModal: false,
      setOtp: '',
      hashOTP: '',
      error: false,
      isCompleted: '',
      dateNow: Date.now(),
    }
  }

  handleUserInput(e) {
    const name = e.target.name
    const value = e.target.value
    this.setState(
      {
        [name]: value,
      },
      () => {
        this.validateField(name, value)
      },
    )
  }

  validateField(fieldName, value) {
    let fieldValidationErrors = this.state.formErrors
    let emailValid = this.state.emailValid
    let passwordValid = this.state.passwordValid

    switch (fieldName) {
      case 'email':
        emailValid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i)
        fieldValidationErrors.email = emailValid ? '' : ' is invalid'
        break
      case 'password':
        passwordValid = value.length >= 6
        fieldValidationErrors.password = passwordValid ? '' : ' is too short'
        break
      default:
        break
    }
    this.setState(
      {
        formErrors: fieldValidationErrors,
        emailValid: emailValid,
        passwordValid: passwordValid,
      },
      this.validateForm,
    )
  }
  handleOtpChange = (e) => {
    this.setState({ setOtp: e })
    console.log(this.state.setOtp, 'console.log(object)console.log(object)')
  }
  validateForm() {
    this.setState({
      formValid: this.state.emailValid && this.state.passwordValid,
    })
  }
  resendOtp = () => {
    this.setState({
      isCompleted: '',
      setOtp: ''
    })
    let params = {
      email: this.state.email,
    }

    WSManager.Rest(NC.baseURL + NC.RESEND_OTP, params).then(
      (responseJson) => {
        if (responseJson.response_code === NC.successCode) {
          notify.show(responseJson.message, 'success', 3000)
          this.setState({ Email: '', formValid: true, hashOTP: responseJson.data.hash })
        }
        else {
          notify.show(NC.SYSTEM_ERROR, "error", 3000)
        }
      }).catch(error => {
        notify.show(NC.SYSTEM_ERROR, "error", 3000)
      })
  }


  // () => {
  //   this.renderer()
  // },

  submitOTP = () => {
    WSManager.Rest(NC.baseURL + NC.DO_LOGIN, {
      email: this.state.email,
      password: md5(this.state.password),
      otp: this.state.setOtp,
      hash: this.state.hashOTP,
      type: 'otp',
    }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let sessionKey = responseJson.data.Sessionkey
        WSManager.setToken(sessionKey)
        let role = responseJson.data.role
        WSManager.setRole(role)
        WSManager.setCreatedBy(responseJson.data.createdby)
        WSManager.setLoggedInID(responseJson.data.admin_id)

        WSManager.setKeyValueInLocal(
          'allow_self_exclusion',
          responseJson.data.module_setting.allow_self_exclusion,
        )

        WSManager.setKeyValueInLocal(
          'allow_private_contest',
          responseJson.data.module_setting.allow_private_contest,
        )
        WSManager.setKeyValueInLocal(
          'ALLOW_COIN_MODULE',
          responseJson.data.module_setting.allow_coin,
        )
        WSManager.setKeyValueInLocal(
          'ALLOW_PREDICTION_MODULE',
          responseJson.data.module_setting.allow_prediction,
        )

        WSManager.setKeyValueInLocal(
          'ALLOW_OPEN_PREDICTOR',
          responseJson.data.module_setting.allow_open_predictor,
        )

        //Allow auto kyc or not
        WSManager.setKeyValueInLocal(
          'AUTO_KYC_ALLOW',
          responseJson.data.module_setting.auto_kyc_enable,
        )
        WSManager.setKeyValueInLocal(
          'LF_PRIVATE_CONTEST',
          responseJson.data.module_setting.lf_private_contest,
        )

        WSManager.setKeyValueInLocal('LoadView', 'true')

        //start code international version
        WSManager.setKeyValueInLocal(
          'currency_code',
          responseJson.data.setting.currency_code,
        )
        WSManager.setKeyValueInLocal(
          'int_version',
          responseJson.data.setting.int_version,
        )
        //end code international version

        //Start code for admin role
        WSManager.setKeyValueInLocal(
          'module_access',
          responseJson.data.module_access,
        )
        let redirectpath =
          WSManager.getRole() > 1
            ? 'distributors/detail/' + Base64.encode(responseJson.data.admin_id)
            : ''
        //Start code for admin role
        if (WSManager.getRole() > 1) {
          this.props.history.push(redirectpath)
        } else if (
          WSManager.getKeyValueInLocal('module_access').includes('dashboard')
        ) {
          // this.props.history.push('/dashboard')
          this.props.history.push('/landing-screen')
        } else {
          this.props.history.push('/welcome-admin')
        }
      } else {
        this.setState({ error: true })
        notify.show(responseJson.error, 'error', 3000)
      }
    })
  }


  renderer = ({ minutes, seconds, completed }) => {
    if (completed) {
      this.setState({ isCompleted: completed })
      return false;
    }
    else {
      return (
        <span className="timer-resend">
          <small className='resend-text'>Resend in </small>
          {minutes}:{seconds}
        </span>
      );
    }
  };



  otpToggle = () => {
    this.setState(
      {
        otpModal: !this.state.otpModal,
        setOtp: '',
        error: false,
      }
    )
  }
  doLogin = () => {
    WSManager.Rest(NC.baseURL + NC.DO_LOGIN, {
      email: this.state.email,
      password: md5(this.state.password),
      type: 'login',
    }).then((responseJson) => {
      if (responseJson.response_code === NC.successCode) {
        let sessionKey = responseJson.data.Sessionkey
        let dataCheck = responseJson.data
        WSManager.setToken(sessionKey)
        if (!_isEmpty(dataCheck.two_fa) && dataCheck.two_fa.next == 'otp') {
          this.setState({
            otpModal: true,
            hashOTP: responseJson.data.two_fa.hash,
          })
        } else {
          let role = responseJson.data.role
          WSManager.setRole(role)
          WSManager.setCreatedBy(responseJson.data.createdby)
          WSManager.setLoggedInID(responseJson.data.admin_id)

          WSManager.setKeyValueInLocal(
            'allow_self_exclusion',
            responseJson.data.module_setting.allow_self_exclusion,
          )

          WSManager.setKeyValueInLocal(
            'allow_private_contest',
            responseJson.data.module_setting.allow_private_contest,
          )
          WSManager.setKeyValueInLocal(
            'ALLOW_COIN_MODULE',
            responseJson.data.module_setting.allow_coin,
          )
          WSManager.setKeyValueInLocal(
            'ALLOW_PREDICTION_MODULE',
            responseJson.data.module_setting.allow_prediction,
          )

          WSManager.setKeyValueInLocal(
            'ALLOW_OPEN_PREDICTOR',
            responseJson.data.module_setting.allow_open_predictor,
          )

          //Allow auto kyc or not
          WSManager.setKeyValueInLocal(
            'AUTO_KYC_ALLOW',
            responseJson.data.module_setting.auto_kyc_enable,
          )
          WSManager.setKeyValueInLocal(
            'LF_PRIVATE_CONTEST',
            responseJson.data.module_setting.lf_private_contest,
          )

          WSManager.setKeyValueInLocal('LoadView', 'true')

          //start code international version
          WSManager.setKeyValueInLocal(
            'currency_code',
            responseJson.data.setting.currency_code,
          )
          WSManager.setKeyValueInLocal(
            'int_version',
            responseJson.data.setting.int_version,
          )
          //end code international version

          //Start code for admin role
          WSManager.setKeyValueInLocal(
            'module_access',
            responseJson.data.module_access,
          )
          let redirectpath =
            WSManager.getRole() > 1
              ? 'distributors/detail/' +
              Base64.encode(responseJson.data.admin_id)
              : ''
          //Start code for admin role
          if (WSManager.getRole() > 1) {
            this.props.history.push(redirectpath)
          } else if (
            WSManager.getKeyValueInLocal('module_access').includes('dashboard')
          ) {
            // this.props.history.push('/dashboard')
            this.props.history.push('/landing-screen')
          } else {
            this.props.history.push('/welcome-admin')
          }
        }
        //distributor

        //End code for admin role
      } else if (responseJson.response_code != NC.successCode) {
        notify.show(responseJson.message, 'error', 3000)
        this.setState({ error: true })
      } else {
        notify.show('Api not configured', 'error', 3000)
      }
    })
  }
  forgotPassword = () => {
    window.location.href = '/admin/#/forgot-password'
  }

  render() {
    const { otpModal, setOtp, isCompleted, dateNow } = this.state
    return (
      <div className="nw-login">
        <div className="lg-head-str">
          <div className="login-head">
            <h4>
              {!_isEmpty(HF.getMasterData().site_title)
                ? HF.getMasterData().site_title
                : 'Fantasy'}{' '}
              Admin panel
            </h4>
            <p className="xtext-muted">Let in to get going</p>
          </div>
        </div>
        <div className="app flex-row xalign-items-center">
          <Container>
            <Row className="justify-content-center login-form">
              <Col md="5">
                <div className="text-center mb-20 animate-left">
                  <img src={logo} className="footer-logo" />
                </div>
                {/* <CardGroup> */}
                <Card className="xp-4 animate-right">
                  <CardBody>
                    <Form>
                      {/* <h3>Fantasy Panel</h3>
                      <p className="text-muted">Let in to get going</p> */}

                      <Notification options={{ zIndex: 1060 }} />
                      <div className="formErrors">
                        {Object.keys(this.state.formErrors).map(
                          (fieldName, i) => {
                            if (this.state.formErrors[fieldName].length > 0) {
                              return (
                                <div
                                  className="alert alert-danger fade show"
                                  role="alert"
                                  key={i}
                                >
                                  {fieldName} {this.state.formErrors[fieldName]}
                                  .
                                </div>
                              )
                            } else {
                              return ''
                            }
                          },
                        )}
                      </div>
                      <InputGroup className="mb-3">
                        <Input
                          type="text"
                          placeholder="Email"
                          autoComplete="email"
                          name="email"
                          value={this.state.username}
                          onChange={(event) => this.handleUserInput(event)}
                        />
                      </InputGroup>
                      <InputGroup className="mb-4">
                        <Input
                          type="password"
                          placeholder="Password"
                          autoComplete="current-password"
                          name="password"
                          value={this.state.password}
                          onChange={(event) => this.handleUserInput(event)}
                        />
                      </InputGroup>
                      <Row className="text-center">
                        <Col xs="12">
                          <Button
                            className="btn-secondary px-4"
                            disabled={!this.state.formValid}
                            onClick={this.doLogin}
                          >
                            Login
                          </Button>
                        </Col>
                      </Row>
                    </Form>
                    <Row className="forgot-password">
                      <Col xs="12">
                        <a onClick={() => this.forgotPassword()}>
                          {' '}
                          Forgot Password?{' '}
                        </a>
                      </Col>
                    </Row>
                  </CardBody>
                </Card>

                {/* </CardGroup> */}
              </Col>
            </Row>
          </Container>
        </div>
        {/* OTP Modal */}
        <Modal isOpen={otpModal == true} toggle={this.otpToggle}>
          <ModalHeader toggle={this.otpToggle} className="header-classMDl">
            <h3 className="otp-heading">
              OTP
              <br /> Verification
            </h3>
            <p className="enter-desc">
              Enter the 4 digit OTP code sent to your registered email to
              continue with Login.
            </p>
          </ModalHeader>
          <ModalBody className="modal-body-pd">
            {isCompleted ? (
              <div className="link-txt " onClick={() => this.resendOtp()}>
                <i className="icon-stop-watch"></i>
                <span className="resend-text">Resend OTP</span>
              </div>
            ) : (
              <div>
                <i className="icon-stop-watch"></i>
                <Countdown date={Date.now() + 30000} renderer={this.renderer} />
              </div>
            )}
            <div className="input-container">
              <OtpInput
                className="input-otpMain"
                value={setOtp}
                onChange={this.handleOtpChange}
                numInputs={4}
                isInputNum={1}
                // renderSeparator={<span>-</span>}
                renderInput={(props) => <input {...props} />}
              />
              {/* {this.state.error && (
                <p className="valid-otp">Please enter valid OTP.</p>
              )} */}
            </div>
            <div className="otp-send-to">
              <p className="sent-to">OTP is sent to</p>
              <p className="otp-email">{this.state.email}</p>
            </div>
          </ModalBody>
          <ModalFooter>
            {/* <Button color="primary" onClick={this.resendOtp}>
              Resend Otp
            </Button>{' '} */}
            {/* <Button className="cancel-btn" onClick={() => this.otpToggle()}>
              Cancel
            </Button> */}
            <Button
              className={setOtp.length <= 3 ? 'continue-btn' : 'cancel-btn'}
              onClick={this.submitOTP}
            >
              Continue
            </Button>
          </ModalFooter>
        </Modal>
      </div>
    )
  }
}

export default Login
