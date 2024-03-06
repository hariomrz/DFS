import React, { Component } from 'react'
import {
  Button,
  Col,
  Modal,
  ModalBody,
  ModalFooter,
  ModalHeader,
  Row,
} from 'react-bootstrap'
import WSManager from '../../WSHelper/WSManager'
import { withdrawAmount, sendOTP } from '../../WSHelper/WSCallings'
import * as WSC from '../../WSHelper/WSConstants'
import Countdown from 'react-countdown-now'
import * as AppLabels from '../../helper/AppLabels'

import OtpInput from 'react-otp-input'
import { MyContext } from '../../InitialSetup/MyProvider'
import { Utilities, _isEmpty } from '../../Utilities/Utilities'
export default class OTPValidation extends Component {
  constructor(props) {
    super(props)
    this.state = {
      back: '#FFFFFF',
      fore: '#000000',
      size: 80,
      copied: false,
      otp: '',
      error: false,
      isCompleted: '',
      hash: this.props && this.props.hashData,
      hash_main: '',
      dateNow: Date.now(),
    }
  }
  handleOtpChange = (otp) => {
    const regex = /^[0-9\b]+$/
    if (otp === '' || regex.test(otp)) {
      this.setState({ otp })
    }
  }

  componentDidMount() {}

  renderer = ({ minutes, seconds, completed }) => {
    if (completed) {
      this.setState({ isCompleted: completed })
      return false
    } else {
      return (
        <span className="timer-resend pl-1">
          <small>{AppLabels.RESEND_IN} </small>
          {minutes}:{seconds}
        </span>
      )
    }
  }

  callWithrawBalanceApi() {
    // alert(this..hash)

    this.setState({
      isLoading: true,
    })
    const { hash, hash_main } = this.state
    let param = {
      amount: this.props.mAmount,
      otp: this.state.otp,
      hash: hash_main == '' ? this.props.hashData : hash_main,
      withdraw_method:
        Utilities.getMasterData().allow_auto_withdrawal == 0
          ? 1
          : Utilities.getMasterData().srs_pout == 1
          ? 12
          : Utilities.getMasterData().a_crypto == 1
          ? 15
          : 17,

      // old isiW
      // isIW:
      //   this.state.isNormalTransfer == 1 &&
      //   this.state.mAmount > Utilities.getMasterData().auto_withdrawal_limit &&
      //   Utilities.getMasterData().allow_auto_withdrawal == 1
      //     ? 2
      //     : 1,
      // ...(Utilities.getMasterData().allow_auto_withdrawal == 1 && {
      //   apiversion: 'v2',

      // new isiW
      isIW:
        this.props.isNormalTransfer == 2
          ? 2
          : parseInt(Utilities.getMasterData().auto_withdrawal_limit) >
            parseInt(this.props.mAmount)
          ? 1
          : 2,
      ...(Utilities.getMasterData().allow_auto_withdrawal == 1 && {
        apiversion: 'v2',
      }),
    }
    withdrawAmount(param).then((responseJson) => {
      Utilities.gtmEventFire('withdraw_money', {
        amount: this.state.mAmount,
      })
      setTimeout(() => {
        this.setState({
          isLoading: false,
        })
      }, 50)
      if (responseJson.response_code == WSC.successCode) {
        Utilities.showToast(responseJson.message, 3000)
        this.props.history.push({ pathname: '/my-wallet' })
      } else {
        Utilities.showToast(responseJson.message, 3000)
        if (!_isEmpty(responseJson.data)) {
          this.setState({ error: true, otp: '' })
        } else {
          this.setState({}, () => this.closeOtpModal())
          this.props.history.push({ pathname: '/my-wallet' })
        }
      }
    })
  }

  ResendOtp = () => {
    sendOTP().then((responseJson) => {
      if (responseJson.response_code == WSC.successCode) {
        Utilities.showToast(responseJson.message, 3000)
        this.setState({
          isCompleted: false,
          hash_main: responseJson.data.hash,
          otp: '',
          error: false,
        })
      }
    })
  }

  closeOtpModal = () => {
    this.props.closeModal()
    this.setState({
      otp: '',
      isCompleted: false,
    })
  }

  render() {
    const { isModalOpen } = this.props
    const { isCompleted, otp } = this.state
    let otpLength = otp && otp.length
    return (
      <MyContext.Consumer>
        {(context) => (
          <Modal
            show={isModalOpen}
            dialogClassName="custom-modal otp-custom-valid"
          >
            <ModalHeader className="otp-mhead">
              <h3 className="otp-heading">
                {AppLabels.OTP}
                <br /> {AppLabels.VERIFICATION}
              </h3>
              <p className="enter-desc">
                {AppLabels.ENTER_FOUR_DIGITS_OTP}{' '}
                {Utilities.getMasterData().login_flow == '0'
                  ? 'mobile number'
                  : 'email'}{' '}
                {AppLabels.ENTER_FOUR_DIGITS_OTP_CONTINUE}
              </p>
            </ModalHeader>
            <ModalBody>
              {isCompleted && (
                <div className="link-txt " onClick={() => this.ResendOtp()}>
                  <i className="icon-stop-watch"></i>
                  <span>{AppLabels.RESEND}</span>
                </div>
              )}
              {!isCompleted && (
                <div>
                  <i className="icon-stop-watch"></i>
                  <Countdown
                    date={Date.now() + 30000}
                    renderer={this.renderer}
                  />
                </div>
              )}

              <Row>
                <Col
                  xs={12}
                  className="phone-number-style registered-otp-block"
                >
                  {
                    <>
                      <div className="opt-block mt-0 input-container">
                        {
                          <OtpInput
                            onChange={this.handleOtpChange}
                            numInputs={4}
                            value={this.state.otp}
                          />
                        }
                        {/* {this.state.error && (
                          <p className="valid-otp">
                            {AppLabels.PLEASE_ENTER_VALID_OTP}
                          </p>
                        )} */}
                      </div>
                      <div className="otp-send-to">
                        <p className="sent-to">{AppLabels.OTP_SENT_TO}</p>
                        <p className="otp-email">
                          {Utilities.getMasterData().login_flow == '0' &&
                          WSManager.getProfile().phone_no ? (
                            <span>
                              +{WSManager.getProfile().phone_code}{' '}
                              {WSManager.getProfile().phone_no}
                            </span>
                          ) : (
                            ''
                          )}
                          {Utilities.getMasterData().login_flow == '1' &&
                          WSManager.getProfile().email
                            ? WSManager.getProfile().email
                            : ''}
                        </p>
                      </div>
                    </>
                  }
                </Col>
              </Row>
            </ModalBody>
            <ModalFooter>
              <Row>
                <Col xs="6" onClick={() => this.closeOtpModal()}>
                  <Button className="cancel-btn">{AppLabels.CANCEL}</Button>
                </Col>
                <Col xs="6" onClick={() => this.callWithrawBalanceApi()}>
                  <Button
                    className={
                      'continue-btn' + (otpLength != 4 ? ' disabled' : '')
                    }
                    disabled={otpLength != 4}
                  >
                    {AppLabels.CONTINUE}
                  </Button>
                </Col>
              </Row>
            </ModalFooter>
          </Modal>
        )}
      </MyContext.Consumer>
    )
  }
}
