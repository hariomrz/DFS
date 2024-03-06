import React, { Suspense, lazy } from 'react';
import { Modal, Tooltip, OverlayTrigger, Button, FormGroup, Row, Col } from 'react-bootstrap';
import ls from 'local-storage';
import WSManager from "../WSHelper/WSManager";
import * as WSC from "../WSHelper/WSConstants";
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from '../helper/AppLabels';
import { inputStyle } from '../helper/input-style';
import { Utilities, withRouter } from '../Utilities/Utilities';
import { getUserBalance, validateContestPromo, validateStockContestPromo, validateContestPromoLF } from '../WSHelper/WSCallings';
import * as Constants from "../helper/Constants";
import { inputStyleLeft, darkInputStyleLeft } from '../helper/input-style';
import FloatingLabel from 'floating-label-react';
import Images from "../components/images";
const ReactSelectDD = lazy(() => import('../Component/CustomComponent/ReactSelectDD'));
var UserBalance = null;
var discountAmount = 0;
var entryFeeAfterDiscount = 0;

class ConfirmationPopup extends React.Component {
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
            entryFeeOfContest: this.props.FixturedContest.entry_fee ? this.props.FixturedContest.entry_fee : '',
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
            isBenchEnable: this.props && this.props.isBenchEnable ? this.props.isBenchEnable : false,
            isStockF: this.props.isStockF || false,
            isStockPF: this.props.isStockPF || false,
            isStockLF: this.props.isStockLF || false,
            hideExtraSec: this.props.hideExtraSec || false,
            bn_state: localStorage.getItem('banned_on'),
            playFreeContest: localStorage.getItem('playFreeContest'),
            profileData: WSManager.getProfile(),
            isProps: this.props.isProps ? this.props.isProps : ls.get('isProps')
        };


    }

    fiterCreatedTeamFromAllTeams(TeamListData) {
        let DSBTeam = this.state.isBenchEnable && this.state.FixturedContestItem && this.state.FixturedContestItem.is_network_contest && this.state.FixturedContestItem.is_network_contest == 1 ? true : false;
        let isPickFantasy = Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? true : false
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
                else if (isPickFantasy && this.state.lineUpMasterIdOfCreatedTeam == tempObj.value.user_team_id) {
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

            if (DSBTeam) {
                let tmpArry = []
                for (var obj of teamList1) {
                    if (obj.bench_applied != 1) {
                        tmpArry.push(obj)
                    }
                }
                this.setState({
                    TeamsSortedArray: tmpArry.reverse(),
                    Teams: tmpArry
                })
            }
            else {
                this.setState({
                    TeamsSortedArray: teamList1,// teamList1.reverse(),
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
        if (this.props && this.props.isDFSTour) {
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
        if (this.state.FixturedContestItem.currency_type != 2 && maxBonusAllowed != null && maxBonusAllowed != "") {
            UserBalance = Utilities.getMaxBonusAllowedOfEntryFeeContestWise(EntryFee, maxBonusAllowed);
        }
        else if (this.state.FixturedContestItem.currency_type == 2) {
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
                contest_id: this.props.FixturedContest.contest_id,
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
                if (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
                    this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus)
                }
                else {
                    this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus_allowed)
                }

            } else {
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
            let apiUrl = this.props.isStockF ? validateStockContestPromo : Constants.SELECTED_GAMET == Constants.GameType.LiveFantasy ? validateContestPromoLF : validateContestPromo;
            apiUrl(param).then((responseJson) => {
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
        if (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
            this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus)
        }
        else {
            this.userBalance(this.state.FixturedContestItem.entry_fee, this.state.FixturedContestItem.max_bonus_allowed)
        }
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

        if (Constants.SELECTED_GAMET == Constants.GameType.PickemTournament) {
            this.userBalance(entryFeeAfterDiscount, this.state.FixturedContestItem.max_bonus)
        }
        else {
            this.userBalance(entryFeeAfterDiscount, this.state.FixturedContestItem.max_bonus_allowed)
        }
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

    aadharConfirmation = () => {
        const { profileData } = this.state;
        if (profileData && profileData.aadhar_status == "0" && profileData.aadhar_detail.aadhar_id) {
            Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
    }
    getBSlist = () => {
        let _obj = ls.get('bslist')
        if (_obj) {
            return Object.values(_obj)
        } else return []
    }

    render() {

        const { ConfirmationClickEvent, FixturedContest, propsData, isPickemTournament } = this.props;
        const { isProps } = this.state
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
            TourDetail,
            isStockLF,
            hideExtraSec,
            bn_state
        } = this.state;
        let EntryFee = FixturedContestItem.entry_fee || 0
        let CurrencyType = FixturedContestItem.currency_type
        let isNetworkFantasyContest = FixturedContestItem && FixturedContestItem.is_network_contest && FixturedContestItem.is_network_contest == 1 ? true : false


        const { profileData } = this.state;
        let maxCBCapAmount = Utilities.getMasterData().max_contest_bonus;
        let PickFantasy = Constants.SELECTED_GAMET == Constants.GameType.PickFantasy ? true : false
        let banStates = this.getBSlist()
        let bsL = banStates.length;
        const context = ''
        return (
            <Modal
                show={this.props.IsConfirmationPopupShow}
                onHide={this.props.IsConfirmationPopupHide}
                dialogClassName="custom-modal thank-you-modal confirmation-modal confirmation-modal-contestlist header-circular-modal "
                className="center-modal confirm-new"
            >
                <Modal.Header closeButton onHide={this.props.IsConfirmationPopupHide}>
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
                            {
                                !isStockLF && !hideExtraSec && !isProps &&
                                <Row className='mt20' style={{ padding: 0 }}>
                                    <Col xs={12} style={{ padding: 0 }}>
                                        <div className={"fee-container fee-container-full" + (isDFSTour ? ' disabled-select-event' : '') + (isDFSTour && TourDetail.user_info.is_joined != '1' ? ' mb-1' : '')}>
                                            {(lineUpMasterIdArray && lineUpMasterIdArray.length > 1 && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy && !PickFantasy) &&
                                                <div className="lable-text small-div multi-one">
                                                    {AppLabels.JOINED_WITH1} {lineUpMasterIdArray.length} {AppLabels.JOINED_WITH2}
                                                </div>}
                                            {(!lineUpMasterIdArray || lineUpMasterIdArray.length <= 1 && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy) &&
                                                <div className="lable-text small-div">{this.props.isStockF ? AppLabels.JOINING_CONTEST_WITH : PickFantasy ? 'Joining Pick with' : AppLabels.JOINING_TEAM_WITH}
                                                </div>}
                                            {(!lineUpMasterIdArray || lineUpMasterIdArray.length <= 1 && Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy) &&
                                                Constants.SELECTED_GAMET != Constants.GameType.LiveFantasy &&
                                                <div className="joining-team">
                                                    {
                                                        this.state.TotalTeam && this.state.TotalTeam.length < parseInt(Utilities.getMasterData().a_teams) ?
                                                            isCMounted && <Suspense fallback={<div />} ><ReactSelectDD
                                                                onChange={isDFSTour ? '' : this.handleTeamChange}
                                                                options={isDFSTour ? '' : lineUpMasterIdOfCreatedTeam != "" ? TeamsSortedArray : [...[{ label: this.props.isStockF ? AppLabels.CREATE_NEW_PORTFOLIO : PickFantasy ? 'Create New Pick' : AppLabels.CREATE_NEW_TEAM, value: AppLabels.CREATE_NEW_TEAM }], ...TeamsSortedArray]}
                                                                className={"basic-select-field" + (lineUpMasterIdOfCreatedTeam != "" ? '' : ' add-create-team')}
                                                                classNamePrefix="select"
                                                                value={selectedTeam}
                                                                placeholder={this.props.isStockF ? AppLabels.SELECT_PORTFOLIO : PickFantasy ? 'Select Pick' : AppLabels.SELECT_TEAM}
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
                                                                placeholder={this.props.isStockF ? AppLabels.SELECT_PORTFOLIO : PickFantasy ? 'Select Pick' : AppLabels.SELECT_TEAM}
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
                            }
                            {
                                isProps &&
                                <Row className="p-0">
                                    <Col xs={12} className="p-0">
                                        <div className="props-team-label">Joining Team(s) With</div>
                                        <div className="props-team-name">{propsData.team_name}</div>
                                    </Col>
                                </Row>
                            }
                            {
                                (!isDFSTour || (isDFSTour && TourDetail.user_info.is_joined != '1')) &&
                                <>
                                    <Row style={{ padding: 0 }} className={(isStockLF || hideExtraSec) ? 'mt20' : ''}>
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
                                                    {
                                                        Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" && (this.state.mUserBalance.cb_balance || 0) > 0 ? 
                                                        <strong>{CommonLabels.CONFIRMATION_TEXT}</strong> 
                                                        :
                                                        Constants.SELECTED_GAMET == Constants.GameType.PickemTournament ?
                                                            <>
                                                            {AppLabels.PAYABLE_TOOLTIP1
                                                                + (FixturedContest.max_bonus != null && FixturedContest.max_bonus != "" ? FixturedContest.max_bonus : WSManager.getAllowedBonusPercantage()) + AppLabels.PAYABLE_TOOLTIP2 + (maxCBCapAmount > 0 ? (' (' + AppLabels.MAX + " " + Utilities.getMasterData().currency_code + maxCBCapAmount + ')') : '')}
                                                                </>
                                                            :
                                                            <>
                                                            {AppLabels.PAYABLE_TOOLTIP1
                                                                + (FixturedContest.max_bonus_allowed != null && FixturedContest.max_bonus_allowed != "" ? FixturedContest.max_bonus_allowed : WSManager.getAllowedBonusPercantage()) + AppLabels.PAYABLE_TOOLTIP2 + (maxCBCapAmount > 0 ? (' (' + AppLabels.MAX + " " + Utilities.getMasterData().currency_code + maxCBCapAmount + ')') : '')}
                                                                </>
                                                    }
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
                                                        <>
                                                            {this.props.remaining_coin ? Utilities.numberWithCommas(this.props.remaining_coin) : entryFeeAfterPromoCode == "" ?
                                                                Utilities.numberWithCommas(EntryFee) :
                                                                entryFeeAfterPromoCode
                                                            }
                                                        </>
                                                    </span>
                                                </div>
                                            </div>
                                        </Col>
                                    </Row>
                                </>
                            }
                            {((isDFSTour && TourDetail.user_info.is_joined != '1') || !isDFSTour) && balanceAccToMaxPercent < EntryFee && CurrencyType != 2 &&
                                <Row className="p-0">
                                    <Col xs={12} className="p-0">
                                        {
                                            this.state.refreshField &&
                                            <div className={"amount-subtext amount-subtext-font10 input-label-spacing" + (this.state.AmountToAdd != 0 ? ' field-fill' : ' disablelabel')}>
                                                <span className="amount-formlabel">{AppLabels.AMOUNT_TO_ADD}</span>
                                                <FormGroup
                                                    className='input-label-center input-transparent '
                                                    controlId="formBasicText"
                                                >
                                                    <FloatingLabel
                                                        autoComplete='off'
                                                        styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
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
                                            <a href className="amount-option" onClick={() => this.addAmount(100)}>
                                                <span>+</span> {Utilities.getMasterData().currency_code} 100
                                            </a>
                                            <a href className="amount-option" onClick={() => this.addAmount(200)}>
                                                <span>+</span> {Utilities.getMasterData().currency_code} 200
                                            </a>
                                            <a href className="amount-option" onClick={() => this.addAmount(500)}>
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
                                            Utilities.getMasterData().int_version == 1 ? AppLabels.BY_JOINING_THIS_CONTEST_ACCEPTING + ' ' + WSC.AppName + ' ' :
                                                AppLabels.BY_JOINING_THIS_CONTEST + WSC.AppName + AppLabels.TC + ' '
                                        }
                                        {
                                            (Utilities.getMasterData().bs_a == 1 && bsL > 0) && <>
                                                {
                                                    AppLabels.and_I_am_not_a2
                                                }
                                                {banStates.slice(0, bsL > 5 ? 5 : bsL).join(', ')}
                                                {bsL > 5 && <OverlayTrigger rootClose trigger={['click']} placement="left" overlay={
                                                    <Tooltip id="tooltip" className="tooltip-featured">
                                                        <strong>{banStates.join(', ')}</strong>
                                                    </Tooltip>
                                                }><i style={{ padding: 3, fontSize: 12 }} className="icon-info" onClick={(event) => event.stopPropagation()} /></OverlayTrigger>}
                                                {
                                                    AppLabels.and_I_am_not_txt
                                                }
                                            </>
                                        }

                                        {
                                            AppLabels.AGREE_TO_CONTACTED_BY + WSC.AppName + AppLabels.AND_THEIR_PARTNERS
                                        }

                                        {
                                            Utilities.getMasterData().int_version != 1 &&
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
                    {Utilities.getMasterData().allow_gst == 1 && Utilities.getMasterData().gst_type == "new" && (this.state.mUserBalance.cb_balance || 0) <= 0 && CurrencyType != 2 && EntryFee > 0 && this.state.showPromoCode && !isNetworkFantasyContest && !isDFSTour && !PickFantasy && !hideExtraSec &&
                        <div >
                            <FormGroup
                                className='input-label-center input-with-cancel'
                                controlId="formBasicText"
                            >
                                {/* {promoCode != '' && <label className="input-label">{AppLabels.ENTER_PROMO_CODE}</label>} */}
                                <div className="promocode-input promocode-floating-label">
                                    {
                                        isChanged ?
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={Constants.DARK_THEME_ENABLE ? darkInputStyleLeft : { ...inputStyleLeft, label: { ...inputStyleLeft.label, fontSize: "14px !important" } }}
                                                id='promoCode'
                                                name='promoCode'
                                                placeholder={AppLabels.ENTER_PROMO_CODE}
                                                type='text'
                                                onChange={this.handleChange}
                                                value={promoCode}
                                                maxLength={100}
                                                disabled={isDisabled}
                                            />
                                            : <div className='input-label-center input-with-cancel'
                                                styles={inputStyle}
                                            />
                                    }
                                    {isDisabled &&
                                        <i onClick={() => this.removePromoCode()} className="icon-close"></i>
                                    }
                                </div>
                                {
                                    !isNetworkFantasyContest && !isDisabled &&

                                    <a onClick={() => this.callGetPromoCodeDetailApi(promoCode)} className={"promo-btn" + (promoCode.length < 4 ? " disabled" : "")}>
                                        {AppLabels.APPLY}
                                    </a>
                                }

                            </FormGroup>

                        </div>}
                    {
                        !isDFSTour && !hideExtraSec && !isProps &&
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
                        <a href className={"joinContestConfirm single-text" + (clickOnce && selectedTeam != '' ? ' click-disabled' : '')}
                            onClick={() => {
                                this.setState({ clickOnce: selectedTeam != '' },
                                    () => {
                                        if ((bn_state == 1 || bn_state == 2) && FixturedContestItem.entry_fee != '0') {
                                            Utilities.bannedStateToast(bn_state)
                                        }
                                        else {
                                            if (isDFSTour && TourDetail.user_info.is_joined == '1') {
                                                ConfirmationClickEvent(this.state, context);
                                            }
                                            else if (Constants.OnlyCoinsFlow == 1 || Constants.OnlyCoinsFlow == 2) {
                                                var currentEntryFee = 0;
                                                currentEntryFee = this.state.entryFeeOfContest;
                                                if (
                                                    (FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(balanceAccToMaxPercent))) ||
                                                    (FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(balanceAccToMaxPercent)))
                                                ) {
                                                    (Utilities.getMasterData().a_aadhar == "1") ?
                                                        ((profileData && profileData.aadhar_status == "1") || FixturedContestItem.entry_fee != '0') ?
                                                            ConfirmationClickEvent(this.state, context, FixturedContestItem)
                                                            :
                                                            this.aadharConfirmation()
                                                        :
                                                        ConfirmationClickEvent(this.state, context)

                                                }
                                                else {
                                                    if (FixturedContestItem.currency_type == 2 && Utilities.getMasterData().allow_buy_coin == 1) {
                                                        this.goToBuyCoins()
                                                    }
                                                    else {
                                                        if (FixturedContestItem.currency_type == 2) {
                                                            if (Constants.EnableBuyCoin) {
                                                                if (bn_state == 1 || bn_state == 2) {
                                                                    if (FixturedContestItem.entry_fee == '0') {
                                                                        this.goToBuyCoins()
                                                                    }
                                                                    else {
                                                                        Utilities.bannedStateToast()
                                                                    }
                                                                }

                                                                else if (bn_state == 0) {
                                                                    (Utilities.getMasterData().a_aadhar == "1") ?
                                                                        ((profileData && profileData.aadhar_status == "1") || FixturedContestItem.entry_fee != '0') ?
                                                                            this.goToBuyCoins()
                                                                            :
                                                                            this.aadharConfirmation()
                                                                        :
                                                                        this.goToBuyCoins()
                                                                }
                                                                else {
                                                                    this.goToBuyCoins()
                                                                }
                                                            }
                                                        }
                                                        else {
                                                            (Utilities.getMasterData().a_aadhar == "1") ?
                                                                ((profileData && profileData.aadhar_status == "1") || FixturedContestItem.entry_fee != '0')
                                                                    ?
                                                                    ConfirmationClickEvent(this.state, context, FixturedContestItem)
                                                                    :
                                                                    this.aadharConfirmation()
                                                                :
                                                                ConfirmationClickEvent(this.state, context)

                                                        }
                                                    }

                                                }
                                            }
                                            else {
                                                if (Utilities.getMasterData().a_aadhar == "1" && FixturedContestItem.entry_fee != '0') {
                                                    if (WSManager.getProfile().aadhar_status == 1) {
                                                        ConfirmationClickEvent(this.state, context);
                                                    }
                                                    else {
                                                        this.aadharConfirmation()
                                                    }
                                                }
                                                else {
                                                    ConfirmationClickEvent(this.state, context);
                                                }
                                            }
                                            setTimeout(() => {
                                                this.setState({ clickOnce: false })
                                            }, 3000);
                                        }


                                    });
                            }}>
                            {
                                isDFSTour && TourDetail.user_info.is_joined == '1' ?
                                    AppLabels.JOIN_FIXTURE
                                    :
                                    ((CurrencyType != 2 && (parseFloat(balanceAccToMaxPercent) >= parseFloat(entryFeeOfContest)))
                                        ||
                                        (CurrencyType == 2 && (parseInt(balanceAccToMaxPercent) >= parseInt(entryFeeOfContest)))) ?
                                        ((isDFSTour || isPickemTournament) ? AppLabels.JOIN_TOURNAMENT_TEXT : isProps ? AppLabels.CONFIRM : AppLabels.JOIN_CONTEST) :
                                        Constants.SELECTED_GAMET != Constants.GameType.Free2Play ?
                                            (CurrencyType == 2
                                                ?
                                                (isDFSTour ? AppLabels.ADD_COIN_AND_JOIN_TOURNAMENT : AppLabels.ADD_COIN_AND_JOIN_CONTEST)
                                                :
                                                (isDFSTour ? AppLabels.ADD_FUND_JOIN_TOURNAMENT : AppLabels.ADD_FUND_JOIN_CONTEST)
                                            )
                                            :
                                            (isDFSTour ? AppLabels.JOIN_TOURNAMENT_TEXT : isProps ? AppLabels.CONFIRM : AppLabels.JOIN_CONTEST)
                            }
                        </a>
                    }
                </Modal.Footer>
            </Modal>
        );
    }
}

ConfirmationPopup.defaultProps = {
    isProps: false,
    isStockF: false,
    isStockPF: false,
    propsData: {}
}
export default withRouter(ConfirmationPopup)