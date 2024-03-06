import React, { Suspense, lazy } from 'react';
import { Modal, Tooltip, OverlayTrigger, Button, FormGroup, Row, Col } from 'react-bootstrap';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import { inputStyle } from '../../helper/input-style';
import { MyContext } from '../../InitialSetup/MyProvider';
import { Utilities } from '../../Utilities/Utilities';
import { getUserBalance,validateContestPromoLF } from '../../WSHelper/WSCallings';
import * as Constants from "../../helper/Constants";
import { inputStyleLeft,darkInputStyleLeft } from '../../helper/input-style';
import FloatingLabel from 'floating-label-react';
import Images from "../../components/images";
const ReactSelectDD = lazy(()=>import('../../Component/CustomComponent/ReactSelectDD'));
var UserBalance = null;
var discountAmount = 0;
var entryFeeAfterDiscount = 0;

export default class LiveFantasyConfirmationPopup extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.state = {
            selectedTeam: '',
            Teams: this.props.TeamListData,
            TotalTeam: this.props.TotalTeam,
            FixturedContestItem: this.props.FixturedContest,
            lobbyDataItem: this.props.lobbyDataToPopup,
            mUserBalance: '',
            balanceAccToMaxPercent: "",
            promoCode: '',
            benefitCap: '',
            entryFeeAfterPromoCode: "",
            isDisabled: false,
            lineUpMasterIdOfCreatedTeam: this.props.createdLineUp != "" ? this.props.createdLineUp : "",
            lineUpMasterIdArray: this.props.selectedLineUps ? this.props.selectedLineUps : [],
            TeamsSortedArray: [],
            isChanged: true,
            refreshAddFundsBtn: true,
            entryFeeOfContest: this.props.FixturedContest.entry_fee,
            showPromoCode: false,
            clickOnce: false,
            promoCodeErrorMsg: '',
            contestMaxBonusAllowed: '',
            useWinningAmt: false,
            AmountToAdd: parseFloat(this.props.FixturedContest.entry_fee),
            refreshField: true,
            isLoading: false,
            isCMounted: false,
            isDFSTour: this.props.isDFSTour || false,
            TourDetail: this.props && this.props.TourDetail ? this.props.TourDetail : '',
            isBenchEnable: this.props && this.props.isBenchEnable ? this.props.isBenchEnable : false
        };


    }

    fiterCreatedTeamFromAllTeams(TeamListData) {
        let DSBTeam = this.state.isBenchEnable && this.state.FixturedContestItem && this.state.FixturedContestItem.is_network_contest && this.state.FixturedContestItem.is_network_contest == 1 ? true : false;
        if (this.state.lineUpMasterIdOfCreatedTeam != "") {
            let teamList1 = [];
            for (var obj of TeamListData) {
                let tempObj = {};
                tempObj['label'] = obj.label ? obj.label : obj.team_name;
                tempObj['value'] = obj.value ? obj.value : obj;

                if (this.state.isDFSTour && this.state.lineUpMasterIdOfCreatedTeam == tempObj.value.tournament_team_id) {
                    teamList1.push(tempObj)
                    this.setState({ selectedTeam: tempObj })

                }
                else if (this.state.lineUpMasterIdOfCreatedTeam == tempObj.value.lineup_master_id) {
                    teamList1.push(tempObj)
                    this.setState({ selectedTeam: tempObj })

                } 
                else {
                    teamList1.push(tempObj)

                }
            }

            if(DSBTeam){
                let tmpArry = []
                for (var obj of teamList1) {
                    if(obj.bench_applied != 1){
                        tmpArry.push(obj)
                    }
                }
                this.setState({
                    TeamsSortedArray: tmpArry.reverse(),
                    Teams: tmpArry
                })
            }
            else{
                this.setState({
                    TeamsSortedArray: teamList1.reverse(),
                    Teams: teamList1
                })
            }


        } else {
            if (this.props.TeamListData.length > 1) {
                if (this.props.TeamListData[0].lineup_master_id > this.props.TeamListData[1].lineup_master_id) {
                    this.setState({ TeamsSortedArray: this.props.TeamListData.reverse() })
                } else {
                    this.setState({ TeamsSortedArray: this.props.TeamListData })
                }
            } else {
                this.setState({ TeamsSortedArray: this.props.TeamListData })
            }

        }
    }

    
    componentWillMount() {
        if(this.props && this.props.isDFSTour){
            let team = this.props && this.props.TeamListData ? this.props.TeamListData[0] : ''
            this.setState({
                selectedTeam: team
            })
        }
    }
    

    handleTeamChange = (selectedOption) => {
        if (selectedOption.value == AppLabels.CREATE_NEW_TEAM) {
            this.props.CreateTeamClickEvent(this.props.FixturedContest, this.state.lobbyDataItem)
        }
        this.setState({ selectedTeam: selectedOption })
    }

    ShowPromoCode = () => {
        this.setState({
            showPromoCode: true
        })
    }

    userBalance(EntryFee, maxBonusAllowed) {
        if (this.state.FixturedContestItem.currency_type != 2 && maxBonusAllowed != null && maxBonusAllowed != ""){
            UserBalance = Utilities.getMaxBonusAllowedOfEntryFeeContestWise(EntryFee, maxBonusAllowed);
        } 
        else if(this.state.FixturedContestItem.currency_type == 2){
            UserBalance = this.state.mUserBalance.point_amount
        }
        else {
            UserBalance = Utilities.getBalanceAccToMaxPercentOfEntryFee(EntryFee);
        }

        this.setState({
            refreshAddFundsBtn: false,
            entryFeeOfContest: EntryFee
        })

        setTimeout(() => {
            this.setState({
                balanceAccToMaxPercent: UserBalance,
                refreshAddFundsBtn: true
            })
        }, 100);
    }

    componentDidMount() {
        this.handelBycoinAppEvent()
        var allowedBonusPercantage = WSManager.getAllowedBonusPercantage()
        var BonusAllowed = parseFloat(allowedBonusPercantage) * parseFloat(this.state.FixturedContestItem.entry_fee) / 100
        this.setState({
            contestMaxBonusAllowed: BonusAllowed
        })

        this.callUserBalanceApi();
        this.fiterCreatedTeamFromAllTeams(this.props.TeamListData);
        this.setState({
            isCMounted: true
        });
        if (this.props.FixturedContest && this.props.FixturedContest.is_scratchwin == 1 && Utilities.getMasterData().a_scratchwin == 1) {
            WSManager.setActiveScratch({
                contest_id: this.props.FixturedContest.contest_id ,
                is_scratchwin: true
            })
        }

    }


    callUserBalanceApi() {

        getUserBalance().then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.setState({
                    mUserBalance: responseJson.data.user_balance
                })
                WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
                WSManager.setBalance(responseJson.data.user_balance);
                this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus_allowed)

            }else{
                this.props.IsConfirmationPopupHide()
            }
        })
    }

    callGetPromoCodeDetailApi(inputPromoCode) {

        if (!this.state.isLoading) {
            this.setState({
                isLoading: true
            })
            let param = {
                "promo_code": inputPromoCode,
                "contest_id": this.state.FixturedContestItem.contest_id
            }
            validateContestPromoLF(param).then((responseJson) => {
                this.setState({
                    isLoading: false
                })
                if (responseJson.response_code == WSC.successCode) {
                    Utilities.showToast(AppLabels.Promocode_has_been_applied, 2500);
                    this.setState({
                        benefitCap: responseJson.data.benefit_cap,
                        isDisabled: true
                    }, () => {
                        this.hidePromoCode()
                    })
                    this.entryFeeAccToBenefitCapOrDiscount(responseJson.data.amount, this.state.benefitCap);
                }
                else {
                    this.setState({
                        promoCodeErrorMsg: responseJson.message
                    })
                }
            })
        }
    }

    removePromoCode() {
        this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus_allowed)
        this.setState({
            entryFeeAfterPromoCode: "",
            promoCode: "",
            isChanged: false,
            isDisabled: false,
            showPromoCode: false,
        })

        setTimeout(() => {
            this.setState({
                isChanged: true
            })
        }, 100);
    }

    handleChange = (e) => {
        const name = e.target.name;
        const value = e.target.value;
        this.setState({ [name]: value });
    }

    entryFeeAccToBenefitCapOrDiscount(mDiscountAmount, mBenefitCap) {

        discountAmount = mDiscountAmount;
        entryFeeAfterDiscount = parseFloat(this.state.FixturedContestItem.entry_fee) - parseFloat(discountAmount);
        if (entryFeeAfterDiscount < 0)
            entryFeeAfterDiscount = 0;

        this.setState({
            entryFeeAfterPromoCode: entryFeeAfterDiscount == 0 ? "0" : entryFeeAfterDiscount
        })

        this.userBalance(entryFeeAfterDiscount, this.state.FixturedContestItem.max_bonus_allowed)
    }

    UseWinning = () => {
        this.setState({
            useWinningAmt: !this.state.useWinningAmt
        })
    }

    hidePromoCode = () => {
        this.setState({
            showPromoCode: false
        })
    }
    PromoCodeFn = (event) => {
        event.stopPropagation()
    }

    addAmount = (amt) => {
        let totalAmt = this.state.AmountToAdd || 0;
        totalAmt = parseFloat(totalAmt) + parseFloat(amt);
        this.setState({
            AmountToAdd: totalAmt,
            refreshField: false
        }, () => {
            this.setState({
                refreshField: true
            })
        })
    }

    onChange = (e) => {
        let amt = e.target.value;
        this.setState({
            AmountToAdd: amt
        })
    }

    goToBuyCoins = () => {

        if (window.ReactNativeWebView) {
            let data = {
                action: 'openBuyScreen',
                targetFunc: 'openBuyScreen',
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        } else {
            this.props.showDownloadApp();
        }

    }
    handelBycoinAppEvent() {
        window.addEventListener('message', (e) => {
           if (e.data.action == 'buyCoin' && e.data.type == 'succuss') {
                this.callUserBalanceApi();
              }            
        });
    }

    render() {
        const {
            selectedTeam,
            FixturedContestItem,
            mUserBalance,
            balanceAccToMaxPercent,
            promoCode,
            entryFeeAfterPromoCode,
            isDisabled,
            lineUpMasterIdOfCreatedTeam,
            TeamsSortedArray,
            isChanged,
            refreshAddFundsBtn,
            entryFeeOfContest,
            clickOnce,
            lineUpMasterIdArray,
            useWinningAmt,
            BalanceDetail,
            isCMounted,
            isDFSTour,
            TourDetail
        } = this.state;
        let EntryFee = FixturedContestItem.entry_fee || 0
        let CurrencyType = FixturedContestItem.currency_type
        let isNetworkFantasyContest =  FixturedContestItem && FixturedContestItem.is_network_contest && FixturedContestItem.is_network_contest == 1 ? true :false

        const { ConfirmationClickEvent, FixturedContest } = this.props;
        let maxCBCapAmount = Utilities.getMasterData().max_contest_bonus;
        return (
            <MyContext.Consumer>
                {(context) => (

                        <Modal
                        show={this.props.IsConfirmationPopupShow}
                        onHide={this.props.IsConfirmationPopupHide}
                        dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist header-circular-modal "
                        className="center-modal confirm-new"
                        >
                        <Modal.Header closeButton>
                            <div className="modal-img-wrap">
                                <div className="wrap">
                                    <i className="icon-card-ic"></i>   
                                </div>
                            </div>
                            <div className='Confirm-header '>
                                {balanceAccToMaxPercent < EntryFee ?
                                    <React.Fragment>
                                        {
                                            isDFSTour && TourDetail.user_info.is_joined != '1' ?
                                            AppLabels.LOW_BALANCE : AppLabels.CONFIRMATION
                                        }
                                    </React.Fragment>
                                    :
                                    <React.Fragment>
                                        {AppLabels.CONFIRMATION}
                                    </React.Fragment>
                                }
                            </div>
                        </Modal.Header>

                        <Modal.Body>
                            {
                                mUserBalance &&
                                <React.Fragment>
                                    <Row className='mt20' style={{ padding: 0 }}>
                                        <Col xs={12} style={{ padding: 0 }}>
                                            <div className={"fee-container fee-container-full" + (isDFSTour ? ' disabled-select-event' : '') + (isDFSTour && TourDetail.user_info.is_joined != '1' ? ' mb-1' : '')}>
                                                {(lineUpMasterIdArray && lineUpMasterIdArray.length > 1 ) && <div className="lable-text small-div multi-one">{AppLabels.JOINED_WITH1} {lineUpMasterIdArray.length} {AppLabels.JOINED_WITH2}</div>}
                                                {(!lineUpMasterIdArray || lineUpMasterIdArray.length <= 1 ) && <div className="lable-text small-div">{this.props.isStockF ? AppLabels.JOINING_CONTEST_WITH : AppLabels.JOINING_TEAM_WITH}</div>}
                                                {(!lineUpMasterIdArray || lineUpMasterIdArray.length <= 1 ) && <div className="joining-team">
                                                    
                                                    {
                                                        this.state.TotalTeam && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams) ?
                                                        isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                onChange={isDFSTour ? '' : this.handleTeamChange}
                                                                options={isDFSTour ? '' : lineUpMasterIdOfCreatedTeam != "" ? TeamsSortedArray : [...[{ label: this.props.isStockF ? AppLabels.CREATE_NEW_PORTFOLIO : AppLabels.CREATE_NEW_TEAM, value: AppLabels.CREATE_NEW_TEAM }], ...TeamsSortedArray]}
                                                                className={"basic-select-field" + (lineUpMasterIdOfCreatedTeam != "" ? '' : ' add-create-team')}
                                                                classNamePrefix="select"
                                                                value={selectedTeam}
                                                                placeholder={this.props.isStockF ? AppLabels.SELECT_PORTFOLIO : AppLabels.SELECT_TEAM}
                                                                isSearchable={false}
                                                                isClearable={false}
                                                                theme={(theme) => ({
                                                                    ...theme,
                                                                    borderRadius: 0,
                                                                    colors: {
                                                                        ...theme.colors,
                                                                        primary25: '#fff',
                                                                        primary: '#555555',
                                                                    },
                                                                })}
                                                            /></Suspense>
                                                            :
                                                            isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                onChange={isDFSTour ? '' : this.handleTeamChange}
                                                                options={isDFSTour ? '' : TeamsSortedArray}
                                                                className={"basic-select-field"}
                                                                classNamePrefix="select"
                                                                value={selectedTeam}
                                                                placeholder={this.props.isStockF ? AppLabels.SELECT_PORTFOLIO : AppLabels.SELECT_TEAM}
                                                                isSearchable={false} isClearable={false}
                                                                theme={(theme) => ({
                                                                    ...theme,
                                                                    borderRadius: 0,
                                                                    colors: {
                                                                        ...theme.colors,
                                                                        primary25: '#fff',
                                                                        primary: '#555555',
                                                                    },
                                                                })}
                                                            /></Suspense>
                                                    }
                                                </div>}
                                            </div>
                                        </Col>
                                    </Row>
                                    {
                                        (!isDFSTour || (isDFSTour && TourDetail.user_info.is_joined != '1') )&& 
                                        <>
                                            <Row style={{ padding: 0 }}>
                                                <Col xs={12} style={{ padding: 0, marginBottom: CurrencyType == 2 ? 10 : 0 }}>
                                                    <div className={"fee-container" + (balanceAccToMaxPercent < EntryFee ? ' fee-container-danger' : '')}>
                                                        <div className="lable-text">{AppLabels.CURRENT_BALANCE}
                                                        </div>
                                                        <div className="payable-amount-value">
                                                            <span style={{ fontSize: ((JSON.stringify(balanceAccToMaxPercent).length) > 5 ? 14 : 14) }} className={balanceAccToMaxPercent < EntryFee ? ' ' : ''}> 
                                                                {
                                                                    CurrencyType == 2 ? 
                                                                    <img src={Images.IC_COIN} alt="" className="img-coin" />
                                                                    :
                                                                    Utilities.getMasterData().currency_code
                                                                }
                                                                {Utilities.numberWithCommas(parseInt(balanceAccToMaxPercent).toFixed(2))}</span>
                                                        </div>
                                                    </div>
                                                </Col>
                                            </Row>
                                            {
                                                CurrencyType != 2 &&
                                                <Row className="p-0">
                                                    <Col xs={12} className="p-0">
                                                        <div className="amount-subtext">
                                                            {AppLabels.PAYABLE_TOOLTIP1
                                                                + (FixturedContest.max_bonus_allowed != null && FixturedContest.max_bonus_allowed != "" ? FixturedContest.max_bonus_allowed : WSManager.getAllowedBonusPercantage()) + AppLabels.PAYABLE_TOOLTIP2 + (maxCBCapAmount > 0 ? (' (' + AppLabels.MAX + " " + Utilities.getMasterData().currency_code + maxCBCapAmount + ')') : '')}
                                                            <div className="amount-subtext-font9">*{AppLabels.NOT_VALID_FOR_PRIVATE_CONTEST}</div>
                                                        </div>
                                                    </Col>
                                                </Row>
                                            }
                                            <Row className="p-0">
                                                <Col xs={12} style={{ padding: 0 }}>
                                                    <div className="fee-container">
                                                        <div className='lable-text'>{AppLabels.JOINING_AMOUNT}</div>
                                                        <div className="payable-amount-value">
                                                            <span className='confirmation'>
                                                                {
                                                                    CurrencyType == 2 ? 
                                                                        <img src={Images.IC_COIN} alt="" className="img-coin" />
                                                                    :
                                                                    Utilities.getMasterData().currency_code
                                                                }
                                                                {
                                                                    Constants.SELECTED_GAMET == Constants.GameType.Free2Play ?
                                                                        <>0</>
                                                                        :
                                                                        <>
                                                                            {entryFeeAfterPromoCode == "" ? 
                                                                                Utilities.numberWithCommas(EntryFee) : 
                                                                                entryFeeAfterPromoCode
                                                                            }
                                                                        </>
                                                                } 
                                                            </span>
                                                        </div>
                                                    </div>
                                                </Col>
                                            </Row>
                                        </>
                                    }
                                    {((isDFSTour && TourDetail.user_info.is_joined != '1') || !isDFSTour) && balanceAccToMaxPercent < EntryFee && CurrencyType !=2 &&
                                        <Row className="p-0">
                                            <Col xs={12} className="p-0">
                                                {
                                                    this.state.refreshField &&
                                                        <div className={"amount-subtext amount-subtext-font10 input-label-spacing" + (this.state.AmountToAdd != 0 ? ' field-fill' : ' disablelabel' )}>
                                                            <span className="amount-formlabel">{AppLabels.AMOUNT_TO_ADD}</span>
                                                            <FormGroup
                                                                className='input-label-center input-transparent '
                                                                controlId="formBasicText"
                                                            >
                                                            <FloatingLabel
                                                                autoComplete='off'
                                                                styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft }
                                                                id='addAmt'
                                                                name='addAmt'
                                                                value={this.state.AmountToAdd}
                                                                placeholder={''}
                                                                type='text'
                                                                onChange={this.onChange}
                                                            />
                                                            </FormGroup>
                                                            <span className="currency-symbol">
                                                                {/* <i className="icon-ruppee"></i> */}
                                                                {Utilities.getMasterData().currency_code}
                                                                { 
                                                                    this.state.AmountToAdd == 0 && 
                                                                    <span className="text-0">0</span>
                                                                }
                                                            </span>
                                                        </div>
                                                }
                                                <div className="add-amount-options">
                                                    <a href className="amount-option" onClick={()=>this.addAmount(100)}>
                                                        <span>+</span> {Utilities.getMasterData().currency_code} 100
                                                    </a>
                                                    <a href className="amount-option" onClick={()=>this.addAmount(200)}>
                                                        <span>+</span> {Utilities.getMasterData().currency_code} 200
                                                    </a>
                                                    <a href className="amount-option" onClick={()=>this.addAmount(500)}>
                                                        <span>+</span> {Utilities.getMasterData().currency_code} 500
                                                    </a>
                                                </div>
                                            </Col>
                                        </Row>
                                    }
                                    
                                    <Row className="p-0">
                                        <Col xs={12} className="p-0">
                                            <div className="amount-subtext amount-subtext-font10">
                                                {
                                                    Utilities.getMasterData().int_version == 1  ? AppLabels.BY_JOINING_THIS_CONTEST_ACCEPTING + ' ' + WSC.AppName :
                                                    AppLabels.BY_JOINING_THIS_CONTEST + WSC.AppName + AppLabels.TC + AppLabels.and_I_am_not_a + AppLabels.AGREE_TO_CONTACTED_BY + WSC.AppName + AppLabels.AND_THEIR_PARTNERS
                                                }
                                                {
                                                    Utilities.getMasterData().int_version != 1  &&
                                                    <a className='primary' target='_blank' href="/terms-condition"> {AppLabels.GO_TO} {AppLabels.TC} </a>
                                                }
                                                

                                                {/* {AppLabels.BY_JOINING_THIS_CONTEST} {WSC.AppName} {AppLabels.TC} {AppLabels.and_I_am_not_a} {AppLabels.AGREE_TO_CONTACTED_BY} {WSC.AppName} {AppLabels.AND_THEIR_PARTNERS} <a className='primary' target='_blank' href="/terms-condition"> {AppLabels.GO_TO} {AppLabels.TC} </a> */}
                                            </div>
                                        </Col>
                                    </Row>
                                    {((isDFSTour && TourDetail.user_info.is_joined != '1') || !isDFSTour) && (CurrencyType != 2 && EntryFee > 0 && !this.state.showPromoCode && lineUpMasterIdArray.length <= 1) &&
                                        this.ShowPromoCode()
                                    }
                                </React.Fragment>



                            }
                            {CurrencyType != 2 && EntryFee > 0 && this.state.showPromoCode && !isNetworkFantasyContest &&  !isDFSTour && 
                                <div >
                                    <FormGroup
                                        className='input-label-center input-with-cancel'
                                        controlId="formBasicText"
                                    >
                                        {promoCode != '' && <label className="input-label">{AppLabels.ENTER_PROMO_CODE}</label>}
                                        <span className="promocode-input">
                                            {
                                                isChanged ? <input
                                                    id='promoCode'
                                                    name='promoCode'
                                                    placeholder={AppLabels.ENTER_PROMO_CODE}
                                                    type='text'
                                                    value={promoCode}
                                                    maxLength={100}
                                                    onChange={this.handleChange}
                                                    disabled={isDisabled}
                                                /> : <div className='input-label-center input-with-cancel'
                                                    styles={inputStyle}
                                                    />
                                            }
                                            {isDisabled &&
                                                <i onClick={() => this.removePromoCode()} className="icon-close"></i>
                                            }
                                        </span>
                                        {
                                            !isNetworkFantasyContest && !isDisabled &&
                                            
                                            <a onClick={() => this.callGetPromoCodeDetailApi(promoCode)} className={"promo-btn" + (promoCode.length < 4 ? " disabled" : "")}>
                                                {AppLabels.APPLY}
                                            </a>
                                        }

                                    </FormGroup>
                                    
                                </div>}
                                {
                                    !isDFSTour &&
                                    <div className="text-center promocode-congrats">
                                        {isDisabled &&
                                            <React.Fragment>
                                                {AppLabels.PROMO_TEXT1} 
                                                <span className="teal-color text-bold">
                                                    {Utilities.getMasterData().currency_code}
                                                    {(parseFloat(EntryFee) - parseFloat(entryFeeAfterPromoCode)).toFixed(2)}
                                                </span> 
                                                {AppLabels.PROMO_TEXT2} 
                                                <span className="teal-color text-bold">
                                                    {Utilities.getMasterData().currency_code}{entryFeeAfterPromoCode}
                                                </span>.
                                                </React.Fragment>
                                        }
                                        {!isDisabled &&
                                            <React.Fragment>
                                                <span className="text-danger">
                                                    {this.state.promoCodeErrorMsg}
                                                </span>
                                            </React.Fragment>
                                        }
                                    </div>
                                }

                        </Modal.Body>
                        <Modal.Footer className='custom-modal-footer dual-btn-footer'>
                            {refreshAddFundsBtn &&
                                <a href className={"joinContestConfirm single-text" + (clickOnce && selectedTeam != '' ? ' click-disabled' : '')} onClick={() => {
                                    this.setState({ clickOnce: selectedTeam != '' }, () => {
                                        if(isDFSTour && TourDetail.user_info.is_joined == '1'){
                                            ConfirmationClickEvent(this.state, context);
                                        }
                                        else if (Constants.OnlyCoinsFlow == 1 || Constants.OnlyCoinsFlow == 2) {
                                            var currentEntryFee = 0;
                                            currentEntryFee = this.state.entryFeeOfContest;
                                            if (
                                                (FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(balanceAccToMaxPercent))) ||
                                                (FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(balanceAccToMaxPercent)))
                                            ) {
                                                ConfirmationClickEvent(this.state, context);

                                            }
                                            else {
                                                if (FixturedContestItem.currency_type == 2 && Utilities.getMasterData().allow_buy_coin == 1) {
                                                    this.goToBuyCoins()
                                                }
                                                else {
                                                    ConfirmationClickEvent(this.state, context);

                                                }
                                            }

                                        }
                                        else {
                                            ConfirmationClickEvent(this.state, context);
                                        }
                                        setTimeout(() => {
                                            this.setState({ clickOnce: false })
                                        }, 3000);


                                    });
                                }}> 
                                      {
                                          isDFSTour && TourDetail.user_info.is_joined == '1' ? 
                                           AppLabels.JOIN_FIXTURE
                                          :
                                        ( (CurrencyType != 2 && (parseFloat(balanceAccToMaxPercent) >= parseFloat(entryFeeOfContest))) 
                                        || 
                                        (CurrencyType == 2 && (parseInt(balanceAccToMaxPercent) >= parseInt(entryFeeOfContest)))) ? 
                                        (isDFSTour ? AppLabels.JOIN_TOURNAMENT_TEXT : AppLabels.JOIN_CONTEST ) : 
                                            Constants.SELECTED_GAMET != Constants.GameType.Free2Play ? 
                                                (CurrencyType == 2 
                                                    ?
                                                    (isDFSTour ? AppLabels.ADD_COIN_AND_JOIN_TOURNAMENT : AppLabels.ADD_COIN_AND_JOIN_CONTEST ) 
                                                    : 
                                                    (isDFSTour ? AppLabels.ADD_FUND_JOIN_TOURNAMENT : AppLabels.ADD_FUND_JOIN_CONTEST ) 
                                                )
                                                : 
                                                (isDFSTour ? AppLabels.JOIN_TOURNAMENT_TEXT : AppLabels.JOIN_CONTEST )
                                    }
                                </a>
                            }
                        </Modal.Footer>
                        </Modal>                 

                )}
            </MyContext.Consumer>
        );
    }
}