import React from 'react'
import { sendOTP, withdrawAmount } from '../../WSHelper/WSCallings'
import * as WSC from '../../WSHelper/WSConstants'
import * as AppLabels from '../../helper/AppLabels'
import { MyContext } from '../../InitialSetup/MyProvider'
import { Helmet } from 'react-helmet'
import MetaData from '../../helper/MetaData'
import CustomHeader from '../../components/CustomHeader'
import ls from 'local-storage'
import { MomentDateComponent } from '../CustomComponent'
import EditStateAndCityModal from '../../Modals/EditStateAndCityModal'
import { DARK_THEME_ENABLE, StateTaggingValue } from '../../helper/Constants'
import Images from '../../components/images'
import OTPValidation from './OTPValidation'
import { FormGroup, ControlLabel, FormControl, OverlayTrigger, Tooltip } from 'react-bootstrap';
import WSManager from "../../WSHelper/WSManager";
import { Utilities, _isEmpty } from '../../Utilities/Utilities';


export default class Withdraw extends React.Component {
  constructor(props) {
    super(props)
    this.state = {
      mAmount: '',
      mAmountError: '',
      mAmountValid: '',
      winningAmt: ls.get('userBalance'),
      profileDetail: WSManager.getProfile(),
      minWithdrawAmount: Utilities.getMasterData()
        ? parseFloat(Utilities.getMasterData().min_withdrawal || 0)
        : '',
      maxWithdrawAmount: Utilities.getMasterData()
        ? parseFloat(Utilities.getMasterData().max_withdrawal || 0)
        : '',
      withdrawPendingData: '',
      allow_withdraw: '',
      showStateTagModal: false,
      isLoading: false,
      isNormalTransfer: 2,
      profileData: '',
      OTPmodal: false,
      setOtp: '',
      hash: '',
      closeModal: false,
    }
  }
  UNSAFE_componentWillMount() {
    if (!this.props.location.state || !this.props.location.state.isFromWallet) {
      this.props.history.replace({ pathname: '/my-wallet' })
    } else {
      this.setState({
        withdrawPendingData: this.props.location.state.withdrawStatus,
        allow_withdraw: this.props.location.state.allow_withdraw,
      })
    }
  }
  componentDidMount = () => {
    if (
      StateTaggingValue > 0 &&
      (!WSManager.getProfile().master_state_id || !WSManager.getProfile().city)
    ) {
      Utilities.setScreenName('addfunds')
      this.setState({
        showStateTagModal: true,
      })
    }
  }

  onAmountChange = (e) => {
    this.setState({ mAmount: e.target.value })
    this.validateField(e.target.id, e.target.value);
}

  validateField = (fieldName, value) => {
    let { minWithdrawAmount, winningAmt, maxWithdrawAmount } = this.state
    let winAmount = parseFloat(winningAmt.winning_amount || 0)
    var mAmountValid = value >= minWithdrawAmount && value <= winAmount
    if (maxWithdrawAmount > 0) {
      mAmountValid = mAmountValid && value <= maxWithdrawAmount
    }
    let mAmountError = mAmountValid ? '' : ' ' + AppLabels.is_invalid

    this.setState(
      {
        mAmountError: mAmountError,
        mAmountValid: mAmountValid,
      },
      this.validateForm(false),
    )
  }

  

  validateForm = (submit) => {
    const { mAmountValid } = this.state

    this.setState(
      {
        formValid: mAmountValid,
      },
      () => {
        if (submit) {
          this.callWithrawBalanceApi()
        }
      },
    )
  }
  otpToggle = () => {
    // alert('ddddd')
    this.setState(
      { OTPmodal: !this.state.OTPmodal, setOtp: '', error: false },
      () => {
        console.log(this.state.OTPmodal, '112233')
      },
    )
  }
  withdrawOnclick = () => {
    let curr = Utilities.getMasterData() ? Utilities.getMasterData().currency_code : ""
    let withdrawTypeAmt = this.state.mAmount ? parseFloat(this.state.mAmount) : 0
    let winning_amt = this.state.winningAmt ? parseFloat(this.state.winningAmt.winning_amount) : 0

    if (this.state.mAmount >= this.state.minWithdrawAmount &&
      this.state.mAmount <= this.state.maxWithdrawAmount &&
      winning_amt > withdrawTypeAmt) {

      if (Utilities.getMasterData().wdl_2fa == '1') {
        this.setState({ OTPmodal: true })
        this.otpsend()
      } else if (Utilities.getMasterData().wdl_2fa == '0') {
        this.validateOnSubmit()
      }
    }
    else if (withdrawTypeAmt > winning_amt) {
      Utilities.showToast(AppLabels.YOU_DO_NOT_HAVE_SUFFICIENT_WINNING_AMOUNT, 2000)
    }
    else if (this.state.mAmount != '' && (withdrawTypeAmt < winning_amt)) {
      Utilities.showToast(AppLabels.MIN_WITHDRAW_LIMIT + this.state.minWithdrawAmount + AppLabels.MAX_WITHDRAW_LIMIT + curr + this.state.maxWithdrawAmount)
    }
    else { Utilities.showToast(AppLabels.PLEASE_ENTER_AMOUNT, 2000) }

  }
  otpsend = () => {
    if (
      this.state.mAmount >= this.state.minWithdrawAmount &&
      this.state.mAmount <= this.state.maxWithdrawAmount &&
      this.state.winningAmt > this.state.mAmount
    ) {
      sendOTP().then((responseJson) => {
        if (responseJson.response_code == WSC.successCode) {
          Utilities.showToast(responseJson.message, 3000)
          this.setState({ hash: responseJson.data.hash })
        }

      })
    }
    else {
      Utilities.showToast(AppLabels.MIN_WITHDRAW_LIMIT + this.state.minWithdrawAmount + AppLabels.MAX_WITHDRAW_LIMIT + this.state.maxWithdrawAmount)
    }
  }
      validateOnSubmit = () => {

        let { mAmount, minWithdrawAmount, winningAmt, maxWithdrawAmount } = this.state;
        if (mAmount == '' || mAmount == 0) {
            Utilities.showToast(AppLabels.PLEASE_ENTER_AMOUNT, 2000);
        } else {
            let winAmount = parseFloat(winningAmt.winning_amount || 0)
            var mAmountValid = mAmount >= minWithdrawAmount && mAmount <= winAmount;
            if (maxWithdrawAmount > 0) {
                mAmountValid = mAmountValid && mAmount <= maxWithdrawAmount
            }
            let mAmountError = mAmountValid ? '' : ' ' + AppLabels.is_invalid;
            if (winAmount == 0 || winAmount < mAmount) {
                Utilities.showToast(AppLabels.SUFFICIENT_WINNING, 3000);
            } else if (mAmount !== '' && !mAmountValid) {
                let msg = AppLabels.MIN_WITHDRAW_LIMIT + this.state.minWithdrawAmount + (this.state.maxWithdrawAmount ? (AppLabels.MAX_WITHDRAW_LIMIT + Utilities.getMasterData().currency_code + this.state.maxWithdrawAmount) : "")
                Utilities.showToast(msg, 3000);
            }

            this.setState({
                mAmountError: mAmountError,
                mAmountValid: mAmountValid,
            }, this.validateForm(mAmountValid));
        }
    }

  errorClass(error) {
    if (error) {
      return error.length == 0 ? '' : 'has-error'
    }
  }

  callWithrawBalanceApi() {
    this.setState({
      isLoading: true,
    })
    let param = {
      "amount": this.state.mAmount,
      "withdraw_method": Utilities.getMasterData().allow_auto_withdrawal == 0 ? 1 : Utilities.getMasterData().srs_pout == 1 ? 12 : Utilities.getMasterData().a_crypto == 1 ? 15 : 17,
      "isIW": this.state.isNormalTransfer == 2 ? 2 : parseInt(Utilities.getMasterData().auto_withdrawal_limit) > parseInt(this.state.mAmount) ? 1 : 2,
      ...(Utilities.getMasterData().allow_auto_withdrawal == 1 && { 'apiversion': 'v2' }),

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
        this.props.history.replace({ pathname: '/my-wallet' })
      }
    })
  }
  closeModal = () => {
    this.setState({
      OTPmodal: false,
    })
  }
  getWithdrawal = (is_diff = false) => {
    const { winningAmt, mAmount } = this.state;
    const tds_per = Utilities.getMasterData().allow_tds.percent
    const net_winning = Number(winningAmt.net_winning)

    if (tds_per) {
        let texableValue = net_winning <= mAmount ? net_winning : mAmount;
        let per = 0
        if (net_winning > 0) {
            per = (texableValue / 100) * tds_per;
        }

        return is_diff ? Utilities.numberWithCommas((mAmount - per).toFixed(2)) : Utilities.numberWithCommas(Number(per).toFixed(2))
    }
}

  handleOpenModal = () => {
    this.setState({ OTPmodal: true })
  }
  hideStateTagModal = () => {
    this.setState({
      showStateTagModal: false,
    })
  }
  handleIWstatus = (status) => {
    this.setState({
      isNormalTransfer: status,
    })
  }

  render() {
    const { profileDetail, winningAmt, withdrawPendingData, allow_withdraw, showStateTagModal, isLoading, isNormalTransfer, profileData, OTPmodal,
    } = this.state

    const HeaderOption = {
      back: true,
      notification: false,
      title: AppLabels.WITHDRAW,
      fromAddFund: true,
      hideShadow: true,
      isPrimary: DARK_THEME_ENABLE ? false : true,
      infoIcon: true
    }
    let banCodeMSg = AppLabels.BANK + ' ' + AppLabels.CODE
    return (
      <>
        <MyContext.Consumer>
          {(context) => (
            <div className="web-container web-container-fixed withdraw-page-wapper white-bg">
              <Helmet titleTemplate={`${MetaData.template} | %s`}>
                  <title>{MetaData.addfunds.title}</title>
                  <meta name="description" content={MetaData.addfunds.description} />
                  <meta name="keywords" content={MetaData.addfunds.keywords}></meta>
              </Helmet>
              <CustomHeader {...this.props} HeaderOption={HeaderOption} />
              <div className={"withdraw-header header-with-circle" + (allow_withdraw !== 0 ? '' : ' pending-section')}>
                {allow_withdraw !== 0 ? (
                  <React.Fragment>
                      <div className="total-winning-section">
                          <div className="display-table-cell winning-text-section">
                              <i className="icon-badge"></i>
                              <h2>{AppLabels.TOTAL_WINNINGS}</h2>
                              <p>{AppLabels.YOU_CAN_WITHDRAW_ONLY_FROM_WINNING}</p>
                          </div>
                          <div className="display-table-cell winning-amount text-right">
                              {Utilities.getMasterData().currency_code} {winningAmt.winning_amount}
                          </div>
                      </div>
                  </React.Fragment>
                ) : (
                  <React.Fragment>
                    <div className="total-winning-section pending-section">
                        <div className="display-table-cell winning-text-section pl-0">
                            <h2 className="text-capitalize">{AppLabels.PENDING_AMOUNT}</h2>
                            <p><MomentDateComponent data={{ date: withdrawPendingData.date_added, format: "MMM DD - hh:mm a " }} /></p>
                        </div>
                        <div className="withdraw-body">
                            {allow_withdraw !== 0 &&
                                <div className="min-withdraw-text">{AppLabels.MIN_WITHDRAW_LIMIT}{this.state.minWithdrawAmount}{this.state.maxWithdrawAmount ? (AppLabels.MAX_WITHDRAW_LIMIT + Utilities.getMasterData().currency_code + this.state.maxWithdrawAmount) : ""}</div>
                            }

                            {
                                (!_isEmpty(Utilities.getMasterData().allow_tds) && Utilities.getMasterData().allow_tds.ind == 1 && allow_withdraw !== 0 && (this.state.mAmount && !this.state.mAmountError)) &&
                                <div className="tds-calculation-container">
                                    <div className="heading">{AppLabels.TDS_CALCULATION}</div>
                                    <div className='withdraw-detail-container'>
                                        <div className="withdraw-detail-view">
                                            <div className="detail-for">{AppLabels.AMOUNT_REQUESTED}</div>
                                            <div className="detail-value">{this.state.mAmount ? <>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(this.state.mAmount)}</> : '-'}</div>
                                        </div>
                                        <div className="withdraw-detail-view">
                                            <div className="detail-for">{AppLabels.NET_WINNING_TEXT}</div>
                                            <div className="detail-value">{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(winningAmt.net_winning)}</div>
                                        </div>
                                        <div className="withdraw-detail-view ">
                                            <div className="detail-for taxes-background-view">
                                                {AppLabels.TAXES_ON_NET_WIN} ({!_isEmpty(Utilities.getMasterData().allow_tds) && Utilities.getMasterData().allow_tds.percent + '%'})
                                                <OverlayTrigger trigger={['click']} placement="right" overlay={
                                                    <Tooltip id="tooltip" className='tax-net-info'>
                                                        {AppLabels.TAX_NET_WINNINGS_INFO}
                                                    </Tooltip>
                                                }>
                                                    <span className="icon-info custm-spac" onClick={(e) => e.stopPropagation()} />
                                                </OverlayTrigger>
                                            </div>

                                            <div className="detail-value">
                                                <>{Utilities.getMasterData().currency_code} {this.getWithdrawal()}</>
                                            </div>
                                        </div>
                                        <div className="withdraw-detail-view after-tds-view">
                                            <div className="detail-for detail-for-new">
                                                {AppLabels.WITHDRAW_AFTER_TDS} </div>
                                            <div className="detail-value detail-value-new">
                                                {(this.state.mAmount) ?
                                                    <>
                                                        {Utilities.getMasterData().currency_code}{" "}
                                                        {this.getWithdrawal(true)}
                                                    </> : "-"
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            }



                            {profileDetail.user_bank_detail.ac_number ? <div className="withdraw-acc-detail">
                                <div className="heading">{AppLabels.YOUR_WITHDRAWAL_AMOUNT_WILL_BE_CREDITED_TO_THIS_ACCOUNT}</div>
                                <div className='withdraw-detail-container'>
                                    <div className="withdraw-detail-view">
                                        <div className="detail-for">{AppLabels.FULL_NAME_AS_BANK}</div>
                                        <div className="detail-value">{(profileDetail.user_bank_detail.first_name || '') + ' ' + (profileDetail.user_bank_detail.last_name || '')} </div>
                                    </div>
                                    <div className="withdraw-detail-view">
                                        <div className="detail-for">{AppLabels.BANK_NAME}</div>
                                        <div className="detail-value">{profileDetail.user_bank_detail.bank_name}</div>
                                    </div>
                                    <div className="withdraw-detail-view">
                                        <div className="detail-for">{AppLabels.ACCOUNT_NUMBER}</div>
                                        <div className="detail-value">{profileDetail.user_bank_detail.ac_number}</div>
                                    </div>
                                    <div className="withdraw-detail-view">
                                        <div className="detail-for">{Utilities.getMasterData().int_version != 1 ? AppLabels.IFSC_CODE : banCodeMSg}</div>
                                        <div className="detail-value">{profileDetail.user_bank_detail.ifsc_code}</div>
                                    </div>
                                </div>
                                {/* {
                                    Utilities.getMasterData().int_version != 1 && <> */}
                                {/* <div className="detail-for">{Utilities.getMasterData().int_version != 1 ? AppLabels.IFSC_CODE : banCodeMSg}</div>
                                <div className="detail-value">{profileDetail.user_bank_detail.ifsc_code}</div> */}
                                {/* </>
                                } */}
                            </div> : <div className='pb-5' />}
                            {allow_withdraw !== 0 ?
                                <div className="text-center">
                                    <a href className={"button button-primary-rounded button-block" + (isLoading || !(this.state.mAmount && !this.state.mAmountError) ? ' disabled' : '')} onClick={() => this.validateOnSubmit()}>{" "}{AppLabels.WITHDRAW}{" "}
                                        {!_isEmpty(Utilities.getMasterData().allow_tds) && Utilities.getMasterData().allow_tds.ind == 1 &&
                                            ((this.state.mAmount && !this.state.mAmountError)) && <>{' '}{Utilities.getMasterData().currency_code} {this.getWithdrawal(true)}</>}
                                    </a>
                                </div>
                                :
                                <div className="text-center withdraw-help-text">{AppLabels.RAISE_ANOTHER_WITHDRAWAL_REQUEST}</div>
                            }

                            {/* onClick={() => this.validateOnSubmit()} */}
                        </div>
                    </div>
                </React.Fragment>
                )}
                {Utilities.getMasterData().a_crypto != 1 &&
                  allow_withdraw !== 0 &&
                  Utilities.getMasterData().allow_auto_withdrawal != 0 &&
                  Utilities.getMasterData().pg_fee != 0 && (
                    <React.Fragment>
                      <div className="normal-instant-tansfer">
                          <div onClick={() => this.handleIWstatus(2)}
                              className={"normal-transfer" + (isNormalTransfer == 2 ? ' active' : '')}>
                              <div className='inner-container'>
                                  <div className={'radio-circle-outer' + (isNormalTransfer == 2 ? ' active' : '')}>
                                      <div className={'inner' + (isNormalTransfer == 2 ? ' active' : '')}></div>

                                  </div>
                                  <div className={'right-side-conatiner' + (isNormalTransfer == 2 ? ' active' : '')}>
                                      <div className='normal-transfer-text'>{AppLabels.NORMAL_TRANSFER}</div>
                                      <div className='working-days'>{AppLabels.GET_THREE_FIVE_DAYS}</div>
                                      <div className={'its-free' + (isNormalTransfer == 2 ? ' active' : '')}>{AppLabels.ITS_FREE}</div>

                                  </div>

                              </div>
                          </div>
                          <div onClick={() => this.handleIWstatus(1)}
                              className={"instant-transfer" + (isNormalTransfer == 1 ? ' active' : '')}>
                              <div className='inner-container'>
                                  <div className={'radio-circle-outer' + (isNormalTransfer == 1 ? ' active' : '')}>
                                      <div className={'inner' + (isNormalTransfer == 1 ? ' active' : '')}></div>

                                  </div>
                                  <div className={'right-side-conatiner' + (isNormalTransfer == 1 ? ' active' : '')}>
                                      <div className='normal-transfer-text'>{AppLabels.INSTANT_TRANSFER}</div>
                                      <div className='working-days'>{AppLabels.GET_ONE_TWO_HOURS}</div>
                                      <div className={'its-free' + (isNormalTransfer == 1 ? ' active' : '')}>{Utilities.getMasterData().pg_fee.includes("%") ? ' ' : Utilities.getMasterData().currency_code}{Utilities.getMasterData().pg_fee || 10}{' '}{AppLabels.CHARGES}</div>

                                  </div>

                              </div>
                          </div>
                      </div>
                    </React.Fragment>
                  )}
                {
                    (allow_withdraw !== 0 && isNormalTransfer == 1) || (Utilities.getMasterData().a_crypto == 1) &&
                    <React.Fragment>
                        <div className='flash-container'>
                            <img className='img-flash' src={Images.FLASH} alt=''></img>
                            <div className='instant-transfer-label-container'>
                                <div className='it-label'>{Utilities.getMasterData().a_crypto == 1 ? AppLabels.WITHDRAW_CHARGES : AppLabels.INSTANT_TRANSFER}</div>
                                <div className='it-message'>{Utilities.getMasterData().currency_code}{Utilities.getMasterData().pg_fee || 10}{' '}{AppLabels.IW_MESSAGE}</div>

                            </div>

                        </div>
                    </React.Fragment>
                }
                {allow_withdraw !== 0 ?
                    <div>
                        <FormGroup
                            className={'input-label-center input-transparent overlay-fixed-view font-14 ' + (`${this.errorClass(this.state.mAmountError)}`)}
                        >
                            <ControlLabel>{AppLabels.ENTER_WITHDRAWAL_AMOUNT}</ControlLabel>
                            <FormControl
                                id='amount'
                                name='amount'
                                placeholder={AppLabels.AMOUNT.replace('##', Utilities.getMasterData().currency_code)}
                                type='number'
                                onChange={this.onAmountChange}
                            />
                        </FormGroup>
                    </div>
                    :
                    <div className="withdrawal-status">
                        {AppLabels.YOUR_WITHDRAWAL_REQUEST_IS_PENDING}
                    </div>
                }
              </div>
              <div className="withdraw-body">
                {allow_withdraw !== 0 &&
                    <div className="min-withdraw-text">{AppLabels.MIN_WITHDRAW_LIMIT}{this.state.minWithdrawAmount}{this.state.maxWithdrawAmount ? (AppLabels.MAX_WITHDRAW_LIMIT + Utilities.getMasterData().currency_code + this.state.maxWithdrawAmount) : ""}</div>
                }
                
{
(!_isEmpty(Utilities.getMasterData().allow_tds) && Utilities.getMasterData().allow_tds.ind == 1 && allow_withdraw !== 0 && (this.state.mAmount && !this.state.mAmountError)) &&
<div className="tds-calculation-container">
    <div className="heading">{AppLabels.TDS_CALCULATION}</div>
    <div className='withdraw-detail-container'>
        <div className="withdraw-detail-view">
            <div className="detail-for">{AppLabels.AMOUNT_REQUESTED}</div>
            <div className="detail-value">{this.state.mAmount ? <>{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(this.state.mAmount)}</> : '-'}</div>
        </div>
        <div className="withdraw-detail-view">
            <div className="detail-for">{AppLabels.NET_WINNING_TEXT}</div>
            <div className="detail-value">{Utilities.getMasterData().currency_code} {Utilities.numberWithCommas(winningAmt.net_winning)}</div>
        </div>
        <div className="withdraw-detail-view ">
            <div className="detail-for taxes-background-view">
                {AppLabels.TAXES_ON_NET_WIN} ({!_isEmpty(Utilities.getMasterData().allow_tds) && Utilities.getMasterData().allow_tds.percent + '%'})
                <OverlayTrigger trigger={['click']} placement="right" overlay={
                    <Tooltip id="tooltip" className='tax-net-info'>
                        {AppLabels.TAX_NET_WINNINGS_INFO}
                    </Tooltip>
                }>
                    <span className="icon-info custm-spac" onClick={(e) => e.stopPropagation()} />
                </OverlayTrigger>
            </div>

            <div className="detail-value">
                <>{Utilities.getMasterData().currency_code} {this.getWithdrawal()}</>
            </div>
        </div>
        <div className="withdraw-detail-view after-tds-view">
            <div className="detail-for detail-for-new">
                {AppLabels.WITHDRAW_AFTER_TDS} </div>
            <div className="detail-value detail-value-new">
                {(this.state.mAmount) ?
                    <>
                        {Utilities.getMasterData().currency_code}{" "}
                        {this.getWithdrawal(true)}
                    </> : "-"
                }
            </div>
        </div>
    </div>
</div>
}

                {profileDetail.user_bank_detail.ac_number ? <div className="withdraw-acc-detail">
                    <div className="heading">{AppLabels.YOUR_WITHDRAWAL_AMOUNT_WILL_BE_CREDITED_TO_THIS_ACCOUNT}</div>
                    <div className="p">
                      <div className="detail-for">{AppLabels.FULL_NAME_AS_BANK}</div>
                      <div className="detail-value">{(profileDetail.user_bank_detail.first_name || '') + ' ' + (profileDetail.user_bank_detail.last_name || '')} </div>
                      <div className="detail-for">{AppLabels.BANK_NAME}</div>
                      <div className="detail-value">{profileDetail.user_bank_detail.bank_name}</div>
                      <div className="detail-for">{AppLabels.ACCOUNT_NUMBER}</div>
                      <div className="detail-value">{profileDetail.user_bank_detail.ac_number}</div>
                      {/* {
                          Utilities.getMasterData().int_version != 1 && <> */}
                      <div className="detail-for">{Utilities.getMasterData().int_version != 1 ? AppLabels.IFSC_CODE : banCodeMSg}</div>
                      <div className="detail-value">{profileDetail.user_bank_detail.ifsc_code}</div>
                      {/* </>
                      } */}
                    </div>
                </div> : <div className='pb-5' />}
                {/* {allow_withdraw !== 0 ?
<div className="text-center">
    <a href className={"button button-primary-rounded button-block" + (isLoading || !(this.state.mAmount && !this.state.mAmountError) ? ' disabled' : '')} onClick={() => this.validateOnSubmit()}>{" "}{AppLabels.WITHDRAW}{" "}
        {!_isEmpty(Utilities.getMasterData().allow_tds) &&
            ((this.state.mAmount && !this.state.mAmountError)) && <>{Utilities.getMasterData().currency_code} {this.getWithdrawal(true)}</>}
    </a>
</div>
:
<div className="text-center withdraw-help-text">{AppLabels.RAISE_ANOTHER_WITHDRAWAL_REQUEST}</div>
} */}
                {allow_withdraw !== 0 ?
                    <div className="text-center">
                        <a href className={"button button-primary-rounded button-block" + (isLoading ? ' disabled' : '')} onClick={() => this.withdrawOnclick()}>{AppLabels.WITHDRAW}
                        {!_isEmpty(Utilities.getMasterData().allow_tds) &&
            ((this.state.mAmount && !this.state.mAmountError)) && <> {''} {Utilities.getMasterData().currency_code} {this.getWithdrawal(true)}</>}
                        </a>
                    </div>
                    :
                    <div className="text-center withdraw-help-text">{AppLabels.RAISE_ANOTHER_WITHDRAWAL_REQUEST}</div>
                }
              </div>

              {showStateTagModal && (
                <EditStateAndCityModal
                  {...this.props}
                  mShow={showStateTagModal}
                  mHide={this.hideStateTagModal}
                />
              )}
            </div>
          )}
        </MyContext.Consumer>

        {this.state.mAmount >= this.state.minWithdrawAmount &&
          this.state.mAmount <= this.state.maxWithdrawAmount &&
          this.state.winningAmt > this.state.mAmount && (
            <OTPValidation
              {...this.props}
              isModalOpen={this.state.OTPmodal}
              validateFn={this.validateProps}
              mAmount={this.state.mAmount}
              minWithdrawAmount={this.state.minWithdrawAmount}
              winningAmt={this.state.winningAmt}
              maxWithdrawAmount={this.state.maxWithdrawAmount}
              isNormalTransfer={this.state.isNormalTransfer}
              hashData={this.state.hash}
              closeModal={this.closeModal}
            />
          )}
      </>
    )
  }
}
