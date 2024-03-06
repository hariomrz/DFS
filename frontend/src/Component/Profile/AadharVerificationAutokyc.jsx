import React from 'react'
import { Row, Col, FormGroup, Checkbox, Button } from 'react-bootstrap'
import { MyContext } from '../../InitialSetup/MyProvider'
import * as WSC from '../../WSHelper/WSConstants'
import * as AppLabels from '../../helper/AppLabels'
import WSManager from '../../WSHelper/WSManager'
import Countdown from 'react-countdown-now';
import { inputStyleLeft } from '../../helper/input-style'
import FloatingLabel from 'floating-label-react'
import { Utilities } from '../../Utilities/Utilities'
import { VerfiyAadharOtp, GetAadharOtp } from '../../WSHelper/WSCallings'
import CustomLoader from '../../helper/CustomLoader'
import Images from '../../components/images'
import { VerificationAadharOtp } from '../../Modals'
import { MomentDateComponent } from '../../Component/CustomComponent'

export default class AadharVerificationAutokyc extends React.Component {
  constructor(props) {
    super(props)
    this.state = {
      aadharNumber: '',
      zoopVerify: false,
      is_bonus_popup: false,
      isOtp: false,
      aadharOtp: '',
      isCompleted: false,
      aadhaarData: "",
      dateNow: Date.now(),
      aadhar_number: '',
      ActiveAadharField: false
    }
  }

  /**
      * @description Used for Timer display
      * @param minutes remaining minutes
      * @param seconds remaining seconds
      * @param completed if completed timer then display resend button
    */
  renderer = ({ minutes, seconds, completed }) => {
    if (completed) {
      console.log(completed, "completedcompleted");
      this.setState({ isCompleted: completed })
      return false;
    } else {
      return (
        <span className="timer-resend">{minutes}:{seconds}</span>
      );
    }
  };

  getAadharDetail() {

    const { aadhar_number } = this.state
    this.setState({ isLoading: true })
    let param = {
      "aadhar_number": aadhar_number
    }

    GetAadharOtp(param).then((responseJson) => {
      this.setState({ isLoading: false })
      if (
        responseJson != null &&
        responseJson != '' &&
        responseJson.response_code == WSC.successCode
      ) {
        this.setState({
          isOtp: true,
          aadhaarData: responseJson.data,
          dateNow: Date.now(),
        })
      }
      else{
        Utilities.showToast(responseJson.message, 3000)
      }

    })
  }

  submitAadharDetail = () => {

    const { aadhar_number, aadhaarData, aadharOtp } = this.state
    this.setState({ isLoading: true })
    let param = {
      "aadhar_number": aadhar_number,
      "request_id": aadhaarData.request_id,
      "task_id": aadhaarData.task_id,
      "otp": aadharOtp,

      // "aadhar_number": "251739261596",
      // "request_id": "6839cfd3-1007-44c8-b582-6b2bac765b7f",
      // "task_id" : "Iipkz4Jw-lkX0-PSaF-WdT2-fQeXG6EJihqX",
      // "otp":253999
    }
    VerfiyAadharOtp(param).then((responseJson) => {
      this.setState({ isLoading: false })
      if (
        responseJson != null &&
        responseJson != '' &&
        responseJson.response_code == WSC.successCode
      ) {
        Utilities.showToast(responseJson.message, 5000, Images.BANK_ICON)
        setTimeout(() => {
          this.goback()
        }, 1000)
      }
      else {
        Utilities.showToast(responseJson.message, 3000)
        this.IsHide()
      }
    })
  }

  resendgetAadharDetail() {

    const { aadhar_number } = this.state
    this.setState({ isLoading: true })
    let param = {
      "aadhar_number": aadhar_number
    }
    GetAadharOtp(param).then((responseJson) => {
      this.setState({ isLoading: false })
      if (
        responseJson != null &&
        responseJson != '' &&
        responseJson.response_code == WSC.successCode
      ) {

        this.setState({
          dateNow: Date.now(),
          isCompleted: false,
          aadhaarData: responseJson.data
        })
      }
    })
  }
  handleChangeNumber = (e) => {
    const { value } = e.target
    if (value.length <= 12) {
      this.setState({
        aadhar_number: e.target.value,
      })
    }
  }
  handleKeyDown = (event) => {
    const { aadhar_number, ActiveAadharField } = this.state
    if(!ActiveAadharField) return;
    if (aadhar_number.length >= 12 && event.key !== 'Backspace') {
      event.preventDefault();
    }
  };

  componentDidMount() {
    window.addEventListener('keypress', this.handleKeyDown);
  }

  componentWillUnmount() {
    window.removeEventListener('keypress', this.handleKeyDown);
  }

  handleChangeOtp = (e) => {
    this.setState({
      aadharOtp: e.target.value,
    })
  }

  IsHide = () => {
    this.setState({
      aadharOtp: '',
    }, () => {
      this.setState({
        is_bonus_popup: !this.state.is_bonus_popup
      })
    })
  }

  goback = () => {
    const { history, location } = this.props
    if(location.state && location.state.returnpath === '/my-profile') {
      history.push(location.state.returnpath)
    } else {
      history.goBack()
    }
  }
  

  render() {
    const {
      aadhar_number,
      zoopVerify,
      is_bonus_popup,
      isOtp,
      aadharOtp,
      isCompleted,
      isLoading,
      dateNow
    } = this.state



    return (
      <MyContext.Consumer>
        {(context) => (
          <>
            {
              (WSManager.getProfile().aadhar_detail == '' ||
                WSManager.getProfile().aadhar_status == '2') ?
                <>
                  <div className="aadhar-otp-wraper">
                    {isLoading && <CustomLoader />}
                    <Row>
                      <Col xs={12}>
                        <div className='aadhar-otp-header'>
                          <h4>{AppLabels.VERIFY_TO_CONTINUE_PLAYING_FANTASY_CONTESTS}</h4>
                          <p>{AppLabels.WE_NEED_TO_ENSURE_YOU_RE_NOT_FROM_A_RESTRICTED_STATE}</p>
                        </div>

                      </Col>
                    </Row>
                    {<Row>
                      <Col xs={12} className="input-label-spacing">
                        <FormGroup
                          className={`input-label-center input-transparent`}
                          controlId="formBasicText"
                        >
                          <FloatingLabel
                            autoComplete="off"
                            styles={inputStyleLeft}
                            id="aadharNumber"
                            name="aadharNumber"
                            placeholder={AppLabels.AADHAR_NUMBER}
                            type="number"
                            value={aadhar_number}
                            onChange={(e) => this.handleChangeNumber(e)}
                            onFocus={() => this.setState({
                              ActiveAadharField: true
                            })}
                            onBlur={() => this.setState({
                              ActiveAadharField: false
                            })}
                          />
                        </FormGroup>
                      </Col>
                    </Row>}
                    {isOtp && <Row>

                      <Col xs={12}>
                        <div className='resend-aadhar-otp'>
                          {!isCompleted ? <span>
                            <Countdown date={dateNow + 30000}
                              renderer={this.renderer}
                            />
                          </span>
                            :
                            <div></div>
                          }
                          {isCompleted && <a className={!isCompleted ? 'disable-otp-send' : ''} onClick={() => this.resendgetAadharDetail()}>{AppLabels.SEND_AGAIN}</a>}
                        </div>
                      </Col>

                      {!is_bonus_popup && <Col xs={12} className="input-label-spacing">
                        <FormGroup
                          className={`input-label-center input-transparent`}
                          controlId="formBasicText"
                        >
                          <FloatingLabel
                            autoComplete="off"
                            styles={inputStyleLeft}
                            id="aadharOtp"
                            name="aadharOtp"
                            placeholder={AppLabels.ENTER_OTP}
                            value={aadharOtp}
                            type="number"
                            onChange={(e) => this.handleChangeOtp(e)}
                          />
                        </FormGroup>
                      </Col>}

                      <Col className='zoop-check'>
                        <FormGroup>
                          <Checkbox
                            className="custom-checkbox"
                            value=""
                            name="age"
                            id="age"
                            //defaultChecked={false}
                            onClick={() =>
                              this.setState({
                                zoopVerify: !zoopVerify,
                              })
                            }
                          >
                            <span className="consent-text">
                              {AppLabels.I_HEARBY_AGREE_TO_LET_ZOOP_ONE_VERIFY_MY_DATA_FOR_VERFICATION}
                            </span>
                          </Checkbox>
                        </FormGroup>
                      </Col>

                    </Row>}
                    {!isOtp && <Row>
                      <Col xs={12} className='text-center'>
                        <button
                          onClick={() => this.getAadharDetail()}
                          disabled={aadhar_number.length < 12}
                          className={`button button-primary-rounded-sm m-t ${aadhar_number.length < 12 ? 'disabled' : ''}`}
                        >{AppLabels.GET_OTP}</button>

                      </Col>
                    </Row>}
                    <VerificationAadharOtp
                      IsShow={is_bonus_popup}
                      IsHide={this.IsHide}
                    />
                  </div>
                  <div className="aadhar-otp-footer">
                    <div className='help-block-kyc'>
                      <div className='text-with-link'>
                        <span>{AppLabels.HAVING_TROUBLE}</span> <a onClick={() => this.props.toggleView()}>{AppLabels.TRY_MANUAL_VERIFICATION}</a>
                      </div>
                      <p>{AppLabels.AADHAAR_HELP_TXT}</p>
                    </div>
                    <div className="text-center mt-3 pt-3">
                      <a
                        id="verifyPanCard"
                        className={
                          zoopVerify &&
                            aadharOtp.length == '6'

                            ? 'button button-primary-rounded btn-verify'
                            : 'disabled button button-primary-rounded btn-verify'
                        }
                        onClick={() => this.submitAadharDetail()}
                      >
                        {AppLabels.VERIFY_AADHAAR_DETAILS}
                      </a>
                    </div>
                  </div>
                </>
                :
                <div className="verify-wrapper aadhar-block">

                  {WSManager.getProfile().aadhar_detail.verify_by == 2 &&
                    <div className="aadhar-bg">
                      <div>
                        <p className="aadhar-name-heading">{AppLabels.AADHAAR_CARD_NUMBER}</p>
                        <p className="aadhar-name">
                          {WSManager.getProfile().aadhar_detail.aadhar_number}
                        </p>
                      </div>
                      <div>
                        <p className="aadhar-name-heading">{AppLabels.DOB}</p>
                        <p className="aadhar-name">
                          <MomentDateComponent
                            data={{
                              date: WSManager.getProfile().dob,
                              format: 'MMM DD, YYYY',
                            }}
                          />
                        </p>
                      </div>
                      <div>
                        <p className="aadhar-name-heading">{AppLabels.ADDRESS}</p>
                        <p className="aadhar-name">
                          {WSManager.getProfile().address}
                        </p>
                      </div>
                    </div>}
                  {
                    WSManager.getProfile().aadhar_detail.verify_by == 1 &&
                    <div className="verify-wrapper aadhar-block">
                      <div className="upload-aadhar">
                        <div className="mb-3">
                          <img
                            src={(WSManager.getProfile().aadhar_detail && WSManager.getProfile().aadhar_detail.front_image) ? Utilities.aadharURL(
                              WSManager.getProfile().aadhar_detail.front_image,
                            ) : ''}
                            width="100"
                          />
                        </div>
                        <div className="mt-3">
                          <img
                            src={(WSManager.getProfile().aadhar_detail && WSManager.getProfile().aadhar_detail.back_image) ? Utilities.aadharURL(
                              WSManager.getProfile().aadhar_detail.back_image,
                            ) : ''}
                            width="100"
                          />
                        </div>
                      </div>
                      <div className="aadhar-bg">
                        <div>
                          <p className="aadhar-name-heading">
                            {AppLabels.NAME_ON_AADHAR}
                          </p>
                          <p className="aadhar-name">
                            {WSManager.getProfile().aadhar_detail.name}
                          </p>
                        </div>
                        <div>
                          <p className="aadhar-name-heading">
                            Aadhaar Card Number
                          </p>
                          <p className="aadhar-name">
                            {WSManager.getProfile().aadhar_detail.aadhar_number}
                          </p>
                        </div>
                      </div>
                    </div>
                  }
                </div>
            }
          </>
        )}
      </MyContext.Consumer>
    )
  }
}
