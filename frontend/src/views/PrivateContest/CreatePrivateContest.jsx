import React, { Suspense, lazy } from 'react';
import { MyContext } from "../../InitialSetup/MyProvider";
import * as AppLabels from "../../helper/AppLabels";
import { Row, Col, FormGroup } from 'react-bootstrap';
import FloatingLabel from 'floating-label-react';
import { AppSelectedSport, DARK_THEME_ENABLE, SELECTED_GAMET, GameType } from '../../helper/Constants';
import { inputStyleLeft, darkInputStyleLeft } from '../../helper/input-style';
import { _Map, IsGameTypeEnabled, Utilities } from '../../Utilities/Utilities';
import { createPrivateContest, createContestMasterData, getStockCreateContestMasterData, stockCreateContest, createContestMasterDataDFS, createPrivateContestDFS } from '../../WSHelper/WSCallings';
import PrizeBreak from './PrizeBreakup';
import * as WSC from "../../WSHelper/WSConstants";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import Images from '../../components/images';
const ReactSelectDD = lazy(() => import('../../Component/CustomComponent/ReactSelectDD'));

var ErrorMsgTime = 2500;
let mContext = null;

let minNoOfParticipants = 2;
export default class CreatePrivateContest extends React.Component {
    constructor(props) {
        super(props);
        console.log(props, 'props');
        this.state = {
            activeTab: 0,
            contestName: '',
            maxParticipants: '',
            entryFee: '',
            noOfWinner: '',
            isMultiEntry: true,
            contestDescription: '',
            prize_distribution_data: "",
            showPrizeBreakupModal: false,
            siteRake: '',
            hostRake: '',
            optionSelectedIndex: 0,
            isLoading: false,
            salaryCap: '',
            LobyyData: null,
            entryFeeOpt: [],
            selEntryFee: '',
            isCMounted: false,
            isDFSEnable: IsGameTypeEnabled("allow_dfs")
        };
    }

    handleTab(tab) {
        this.setState({ activeTab: tab })
    }

    componentDidMount() {
        if (this.props.LobyyData) {
            this.setState({ LobyyData: this.props.LobyyData })
        }
        this.callCreateContestMasterData();
        this.setState({
            isCMounted: true,
            entryFeeOpt: [
                {
                    value: 1,
                    label: Utilities.getMasterData().currency_code
                },
                {
                    value: 2,
                    label: <img src={Images.IC_COIN} alt="" />
                }
            ]
        }, () => {
            this.setState({
                selEntryFee: this.state.entryFeeOpt[0]
            })
        })
    }

    callCreateContestMasterData() {
        const { isDFSEnable } = this.state
        let param = {
            "sports_id": AppSelectedSport
        }
        let apiAction = this.props.isStockF ? getStockCreateContestMasterData : (isDFSEnable ? createContestMasterDataDFS : createContestMasterData)
        apiAction(this.props.isStockF || isDFSEnable ? {} : param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    prize_distribution_data: isDFSEnable ? responseJson.data.prize_distribution : responseJson.data.prize_distribution_data,
                    siteRake: parseFloat(responseJson.data.site_rake),
                    hostRake: parseFloat(responseJson.data.host_rake),
                    salaryCap: responseJson.data.salary_cap
                })
            }
        })
    }

    handleContestNameChange(e) {
        mContext.setState({ contestName: e.target.value.trim() })
    }

    handleMaxParticipantsChange(e) {
        mContext.setState({ maxParticipants: e.target.value.trim() })
    }

    handleEntryFeeChange(e) {
        let entry = e.target.value.trim();
        if (entry === '0') {
            mContext.setState({ noOfWinner: '' })
        }
        mContext.setState({ entryFee: e.target.value.trim() }, () => {
            console.log("entryFee", mContext.state.entryFee)
        })
    }

    handleNoOfWinnerChange(e) {
        // let preVal = mContext.state.noOfWinner;
        // let mVal = e.target.value.trim();
        // if( parseInt(mVal)<=10){
        //     mContext.setState({optionSelectedIndex:0,noOfWinner:mVal})
        // }
        // else{
        //   mContext.setState({optionSelectedIndex:0,noOfWinner:preVal})
        // }
        mContext.setState({ optionSelectedIndex: 0, noOfWinner: e.target.value.trim() })
    }

    handleDescriptionChange(e) {
        mContext.setState({ contestDescription: e.target.value })
    }

    canCreateContest(showError) {
        let errorMsg = ''
        let { entryFee, maxParticipants, contestName, contestDescription, noOfWinner } = this.state;
        if (contestName === '') {
            errorMsg = AppLabels.SELECT_CONTEST_NAME;
        }
        else if (contestName.length < 3) {
            errorMsg = AppLabels.SELECT_CONTEST_NAME_MIN_CONDITION;
        }
        else if (maxParticipants === '') {
            errorMsg = AppLabels.MAX_PARTICIPANTS;
        }
        else if (maxParticipants < 2) {
            errorMsg = AppLabels.MAX_PARTICIPANTS_MINIMUM_CONDITION;
        }
        else if (entryFee === '') {
            errorMsg = AppLabels.SELECT_ENTRY_FEE;
        }
        else if (entryFee && entryFee.includes(".")) {
            errorMsg = AppLabels.ENTRY_FEE_DECIMAL_VALIDATE;
        }
        else if (contestDescription.trim() === '') {
            errorMsg = AppLabels.CONTEST_DESCRIPTION_REQUIRE;
        }
        else if (contestDescription.trim().length < 3) {
            errorMsg = AppLabels.CONTEST_DESCRIPTION_MINIMUM_CONDITION;
        }
        else if (entryFee > 0 && (noOfWinner === '' || noOfWinner === 0)) {
            errorMsg = AppLabels.SELECT_WINNERS_COUNT;
        }



        if (showError && errorMsg !== '') {
            Utilities.showToast(errorMsg, ErrorMsgTime);
        }
        else if (showError) {
            if (entryFee > 0 && (parseInt(noOfWinner) > parseInt(maxParticipants))) {
                errorMsg = AppLabels.MAX_PARTICIPANTS_ERROR;
                Utilities.showToast(errorMsg, ErrorMsgTime);
            }
            else {
                this.callCreatePrivateContest()
            }
        }
        else if (errorMsg === '') {
            return true;
        }

        return false;
    }

    getPrizeDistributionData() {
        let { prize_distribution_data, noOfWinner } = this.state;
        if (noOfWinner !== '') {
            let selectedPrizeData = prize_distribution_data[noOfWinner];
            if (selectedPrizeData && Array.isArray(selectedPrizeData)) {
                for (let i = 0; i < selectedPrizeData.length; i++) {
                    let allItems = selectedPrizeData[i];
                    let totalWinning = 0; let per;
                    for (let j = 0; j < allItems.length; j++) {
                        let winValue = this.calculateWinning(allItems[j].per)
                        allItems[j].winning = winValue.toFixed(2);
                        totalWinning = totalWinning + winValue;
                    }
                    selectedPrizeData[i].totalWinning = totalWinning.toFixed(2);

                }
                return selectedPrizeData;
            }
            else if (selectedPrizeData) {
                let winValue = this.calculateWinning(selectedPrizeData.per)
                selectedPrizeData.winning = winValue.toFixed(2);
                selectedPrizeData.totalWinning = winValue.toFixed(2);
                console.log("pDATaElse", JSON.stringify(selectedPrizeData))

                return selectedPrizeData;
            }
            else {
                return [];
            }
        }
        else {
            return [];
        }
    }

    calculateWinning(percentValue) {
        let { entryFee, siteRake, hostRake } = this.state;
        let totalAmount = parseFloat(entryFee) * minNoOfParticipants;
        let totalRake = parseFloat(siteRake) + parseFloat(hostRake);
        let amountAfterSiteRake, winningAmt
        if (this.state.selEntryFee.value == 2) {
            amountAfterSiteRake = totalAmount;
            winningAmt = (amountAfterSiteRake * percentValue) / 100;
            return Math.floor(winningAmt);
            //return Math.round(percentValue);

        }
        else {
            amountAfterSiteRake = totalAmount - ((totalAmount * totalRake) / 100);
            winningAmt = (amountAfterSiteRake * percentValue) / 100;
            return winningAmt;

        }

    }

    onEditPrizeBreakUP(data) {
        mContext.setState({ showPrizeBreakupModal: true, prize_distribution_data: data })
    }

    onHideModal = (data) => {
        mContext.setState({ optionSelectedIndex: data, showPrizeBreakupModal: false })
    }

    callCreatePrivateContest() {
        let { LobyyData, salaryCap, maxParticipants, entryFee, noOfWinner, optionSelectedIndex, contestName, contestDescription, isMultiEntry, siteRake, hostRake, selEntryFee, isDFSEnable } = this.state;
        let distributionData = this.getPrizeDistributionData();
        let pData = distributionData[optionSelectedIndex];
        if (!pData) {
            pData = distributionData;
        }
        let mData = [];
        let totalAmount = parseFloat(entryFee) * minNoOfParticipants;
        let totalMaxAmount = parseFloat(entryFee) * maxParticipants;
        let prizePool = '';
        let prizePoolMax = '';

        if (selEntryFee.value == 2) {
            console.log("totalAmount", totalAmount + " " + "totalMaxAmount" + " " + totalMaxAmount)

            prizePool = totalAmount;
            prizePoolMax = totalMaxAmount;

            console.log("prizePool", prizePool + " " + "prizePoolMax" + " " + prizePoolMax)
        }
        else {
            if (siteRake && hostRake) {
                prizePool = (totalAmount - (totalAmount * (siteRake)) / 100).toFixed(2);
                prizePoolMax = (totalMaxAmount - (totalMaxAmount * (siteRake)) / 100).toFixed(2);
            }
            if (siteRake) {
                prizePool = (totalAmount - (totalAmount * (hostRake)) / 100).toFixed(2);
                prizePoolMax = (totalMaxAmount - (totalMaxAmount * (hostRake)) / 100).toFixed(2);
            }
            if (hostRake) {
                prizePool = (totalAmount - (totalAmount * (siteRake + hostRake)) / 100).toFixed(2);
                prizePoolMax = (totalMaxAmount - (totalMaxAmount * (siteRake + hostRake)) / 100).toFixed(2);
            }
            else {
                prizePool = (totalAmount - (totalAmount) / 100).toFixed(2);
                prizePoolMax = (totalMaxAmount - (totalMaxAmount) / 100).toFixed(2);
                if (siteRake && hostRake == 0) {
                    prizePool = (totalAmount - (totalAmount * (siteRake)) / 100).toFixed(2);
                    prizePoolMax = (totalMaxAmount - (totalMaxAmount * (siteRake)) / 100).toFixed(2);
                }
            }
        }

        if (pData && Array.isArray(pData)) {

            for (let i = 0; i < pData.length; i++) {
                let maxValue = parseFloat((parseFloat(prizePoolMax) * parseFloat(pData[i].per)) / 100);
                let temp = { 'min': pData[i].min, 'max': pData[i].max, 'per': pData[i].per, 'amount': pData[i].winning, 'min_value': pData[i].winning, 'max_value': maxValue.toFixed(2), 'prize_type': this.state.selEntryFee.value == 2 ? 2 : 1 };
                mData[i] = temp;
            }
        }
        else if (pData) {
            let maxValue = parseFloat((parseFloat(prizePoolMax) * parseFloat(pData.per)) / 100);
            let temp = { 'min': pData.min, 'max': pData.max, 'per': pData.per, 'amount': pData.winning, 'min_value': pData.winning, 'max_value': maxValue.toFixed(2), 'prize_type': this.state.selEntryFee.value == 2 ? 2 : 1 };
            mData[0] = temp;
        }
        if (entryFee == 0) {
            mData = [
                {
                    "prize_type": 4,
                    "isValid": true,
                    "min": 1,
                    "max": 1,
                    "per": "100.00",
                    "amount": "0.00"
                }
            ]
        }

        let totalAmountForHostRake = parseFloat(entryFee) * maxParticipants;
        let earning = parseFloat((totalAmountForHostRake * hostRake) / 100).toFixed(2);
        let hostEarn = earning > 0 ? this.state.selEntryFee.value == 2 ? Math.floor(earning) : Utilities.getMasterData().currency_code + ' ' + earning : '';
        let isCoins = Utilities.getMasterData().a_coin == "1"
        let isReal = Utilities.getMasterData().currency_code != ""
        let prizeType = (isReal && isCoins) ? (this.state.selEntryFee.value == 2 ? "2" : "1") : isReal ? "1" : isCoins ? "2" : entryFee == 0 ? "4" : "1";
        let currType = (isReal && isCoins) ? this.state.selEntryFee.value : isReal ? 1 : isCoins ? 2 : "";
        let SGUID = LobyyData && LobyyData.match_list && LobyyData.match_list[0] ? LobyyData.match_list[0].season_game_uid : LobyyData.season_game_uid

        if (!this.state.isLoading && LobyyData) {
            this.setState({ isLoading: true })
            let param = {
                "sports_id": AppSelectedSport,
                "league_id": LobyyData.league_id,
                "collection_master_id": LobyyData.collection_master_id,
                "prize_type": prizeType,
                "salary_cap": salaryCap,
                "prize_pool": prizePool,
                "number_of_winners": entryFee == 0 ? 1 : noOfWinner,
                "entry_fee": entryFee,
                "size": maxParticipants,
                "prize_distribution_detail": mData,
                "season_game_uid": [SGUID],
                "season_scheduled_date": LobyyData.season_scheduled_date,
                "game_name": contestName,
                "game_desc": contestDescription,
                "multiple_lineup": isMultiEntry ? maxParticipants > 6 ? 6 : maxParticipants : 0,
                "currency_type": currType

            }
            if (this.props.isSecIn) {
                param['is_2nd_inning'] = 1
            }
            let apiAction = createPrivateContest;
            if (this.props.isStockF) {
                param = {
                    "collection_id": LobyyData.collection_id || LobyyData.collection_master_id,
                    "prize_type": prizeType,
                    "prize_pool": prizePool,
                    "number_of_winners": entryFee == 0 ? 1 : noOfWinner,
                    "entry_fee": entryFee,
                    "size": maxParticipants,
                    "prize_distribution_detail": mData,
                    "season_scheduled_date": LobyyData.season_scheduled_date,
                    "game_name": contestName,
                    "game_desc": contestDescription,
                    "multiple_lineup": isMultiEntry ? maxParticipants > 6 ? 6 : maxParticipants : 0,
                    "currency_type": currType
                }
                apiAction = stockCreateContest;
            }
            if(isDFSEnable) {
                param = {
                    "collection_master_id": LobyyData.collection_master_id,
                    "number_of_winners": entryFee == 0 ? 1 : noOfWinner,
                    "entry_fee": entryFee,
                    "size": maxParticipants,
                    "prize_distribution_detail": mData,
                    "game_name": contestName,
                    "game_desc": contestDescription,
                    "multiple_lineup": isMultiEntry ? maxParticipants > 6 ? 6 : maxParticipants : 0,
                    "currency_type": currType
                }
                apiAction = createPrivateContestDFS;
            }
            apiAction(param).then((responseJson) => {
                this.setState({ isLoading: false })
                if (responseJson.response_code === WSC.successCode) {
                    responseJson.data.prize_distibution_detail = JSON.parse(responseJson.data.prize_distibution_detail)
                    this.props.history.replace({ pathname: '/share-private-contest', state: { LobyyData: this.state.LobyyData, FixturedContest: responseJson.data, hostEarn: hostEarn, isSecIn: this.props.isSecIn, isStockF: this.props.isStockF } })
                }
            })
        }
    }
    handleEntryFeeType = (selType) => {
        this.setState({
            selEntryFee: selType
        }, () => {
            setTimeout(() => {
                // this.getPrizePool();
                // this.createWinnersList();
            }, 100);
        }, 100)
    }
    render() {
        mContext = this;
        let { prize_distribution_data, entryFee, noOfWinner, showPrizeBreakupModal, siteRake, hostRake, optionSelectedIndex, maxParticipants, selEntryFee, entryFeeOpt } = this.state;
        let mData = this.getPrizeDistributionData();
        let prizeData = mData ? Array.isArray(mData) ? mData[optionSelectedIndex] : [mData] : [];
        //  let prizePool = (prizeData && prizeData.length===1)?prizeData[0].totalWinning: (prizeData && prizeData.length>0)?prizeData.totalWinning:'';
        //  let mPrizePool = prizePool!==''?(Utilities.getMasterData().currency_code+' '+prizePool):'';

        let totalAmount = parseFloat(entryFee) * maxParticipants;
        let earning = parseFloat((totalAmount * hostRake) / 100).toFixed(2);
        let hostEarn = earning > 0 ? this.state.selEntryFee.value == 2 ? Math.floor(earning) : Utilities.getMasterData().currency_code + ' ' + earning : '';
        let showEntryFeeWith = (Utilities.getMasterData().currency_code != "" && Utilities.getMasterData().a_coin == "1") ? 1 : Utilities.getMasterData().currency_code != "" ? 2 : Utilities.getMasterData().a_coin == "1" ? 3 : 2

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className='tab-parent-container'>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.createcontest.title}</title>
                            <meta name="description" content={MetaData.createcontest.description} />
                            <meta name="keywords" content={MetaData.createcontest.keywords}></meta>
                        </Helmet>
                        <form>
                            <div className='tab-container'>
                                <Row className='form-container'>
                                    <Col xs={12} >
                                        <FormGroup
                                            className='input-label-center-align input-transparent font-16 contest-name-input'
                                            controlId="formBasicText">
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='contest-name'
                                                name='contest-name'
                                                placeholder={AppLabels.CONTEST_NAME}
                                                maxLength={25}
                                                onChange={this.handleContestNameChange}
                                                value={this.state.contestName}
                                            />
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                    </Col>
                                    <div className='char-limit-container'>{(25 - (this.state.contestName.length))}/25 {AppLabels.CHARACTER_LEFT}</div>
                                </Row>
                                <Row className='form-container'>
                                    <Col xs={12} >
                                        <FormGroup
                                            className='input-label-center-align input-transparent font-16 contest-name-input'
                                            controlId="formBasicText">
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='max-participants'
                                                name='max-participants'
                                                placeholder={AppLabels.MAXIMUM + ' ' + AppLabels.PARTICIPANTS}
                                                maxLength={5}
                                                type='number'
                                                onChange={this.handleMaxParticipantsChange}
                                                value={this.state.maxParticipants}
                                            />
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                    </Col>
                                    <div className='char-limit-container'>{AppLabels.MINIMUM}{' ' + minNoOfParticipants}</div>
                                </Row>
                                <Row className='form-container'>
                                    <Col xs={12} >
                                        <FormGroup
                                            className='input-label-center-align input-transparent font-16 contest-name-input entry-fee-input'
                                            controlId="formBasicText">
                                            <FloatingLabel
                                                autoComplete='off'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='entry_fee'
                                                name='entry_fee'
                                                placeholder={AppLabels.Entry_fee + ' ( ' + (this.state.selEntryFee.value == 2 ? AppLabels.Coin : Utilities.getMasterData().currency_code) + ' )'}
                                                maxLength={5}
                                                type='number'
                                                onChange={this.handleEntryFeeChange}
                                                value={entryFee}
                                            />
                                            {
                                                showEntryFeeWith == 1 &&
                                                <>
                                                    <Suspense fallback={<div />} ><ReactSelectDD
                                                        onChange={this.handleEntryFeeType}
                                                        options={entryFeeOpt}
                                                        classNamePrefix="secondary"
                                                        className="select-secondary minusML10 sel-entry-type"
                                                        value={selEntryFee}
                                                        placeholder="--"
                                                        isSearchable={false}
                                                        isClearable={false}
                                                        theme={(theme) => ({
                                                            ...theme,
                                                            borderRadius: 0,
                                                            colors: {
                                                                ...theme.colors,
                                                                primary: process.env.REACT_APP_PRIMARY_COLOR,
                                                            },
                                                        })}
                                                    /></Suspense>
                                                    <span className="select-arr select-arr-entry-type"><i className="icon-arrow-down"></i></span>
                                                </>
                                            }
                                            {
                                                showEntryFeeWith == 3 &&
                                                <img src={Images.COINIMG} alt="" />
                                            }
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                    </Col>
                                    <div className='char-limit-container'>{AppLabels.FREE_CONTEST_HINT}</div>
                                </Row>
                                {(entryFee !== '' && entryFee !== '0') &&
                                    <Row className='form-container'>
                                        <Col xs={12} >
                                            <FormGroup
                                                className='input-label-center-align input-transparent font-16 contest-name-input'
                                                controlId="formBasicText">
                                                <FloatingLabel
                                                    autoComplete='off'
                                                    styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                    id='no_of_winners'
                                                    name='no_of_winners'
                                                    placeholder={AppLabels.Number_of_winners}
                                                    max={10}
                                                    type='number'
                                                    onChange={this.handleNoOfWinnerChange}
                                                    value={noOfWinner}
                                                />
                                            </FormGroup>
                                            <span className="bordered-span"></span>
                                        </Col>
                                    </Row>
                                }
                                {(entryFee !== '' && entryFee !== 0 && noOfWinner !== '' && noOfWinner !== 0) &&
                                    <div className='winning_distribution_container'>
                                        <div className='winning_distribution_label'>
                                            {AppLabels.WINNING_DISTRIBUTION}
                                            {prizeData && prizeData.length > 1 && <span onClick={() => this.onEditPrizeBreakUP(prize_distribution_data)}>{AppLabels.EDIT}</span>}
                                        </div>
                                        {prizeData && prizeData.length > 0 ?
                                            <div className='table-container'>
                                                <React.Fragment>
                                                    <div className="table-header">
                                                        <div className='header-item'>{AppLabels.RANK}</div>
                                                        <div className='header-item left-align'>{AppLabels.WINNING + '%'}</div>
                                                        <div className='header-item left-align'>{AppLabels.WINNING}</div>
                                                    </div>
                                                    {
                                                        _Map(prizeData, (item, index) => {
                                                            return (
                                                                <div className="table-body">
                                                                    <div className='table-item'>{(item.min === item.max) ? item.min : (item.min + '-' + item.max)}</div>
                                                                    <div className='table-item left-align'>{item.per}{'%'}</div>
                                                                    <div className='table-item bold left-align'>{
                                                                        this.state.selEntryFee.value == 2 ?
                                                                            <div>
                                                                                <img src={Images.IC_COIN} alt="" width='14px' />
                                                                                {
                                                                                    item.winning && item.winning
                                                                                }
                                                                            </div>
                                                                            : Utilities.getMasterData().currency_code + ' ' + item.winning
                                                                    }</div>
                                                                </div>
                                                            )
                                                        })
                                                    }
                                                </React.Fragment>
                                            </div>
                                            :
                                            <div>{AppLabels.MAX_WINNER}</div>
                                        }
                                    </div>
                                }
                                <div className='multi_entry_container'>
                                    <div className='multi_entry_label'>
                                        {AppLabels.MULTI_ENTRY}
                                    </div>
                                    <div className='multi_entry_desc'> {SELECTED_GAMET == GameType.StockFantasyEquity ? AppLabels.MULTI_ENTRY_JOIN_CONDITION_STKEQ : AppLabels.MULTI_ENTRY_JOIN_CONDITION}</div>
                                    <div className='btn-container'>
                                        <div onClick={() => this.setState({ isMultiEntry: true })} className={'multi_btn_style ' + (this.state.isMultiEntry ? ' selected' : '')}>{AppLabels.YES}</div>
                                        <div onClick={() => this.setState({ isMultiEntry: false })} className={'multi_btn_style ' + (!this.state.isMultiEntry ? ' selected' : '')}>{AppLabels.NO}</div>
                                    </div>
                                </div>
                                <Row className='form-container mT50'>
                                    <Col xs={12} >
                                        <div className='desc-label'> {AppLabels.DESCRIOTION}</div>
                                        <FormGroup
                                            className='input-label-center-align input-transparent font-16 contest-name-input'
                                            controlId="formBasicText">
                                            <textarea
                                                autoComplete='off'
                                                rows={3}
                                                className='text-area-style'
                                                styles={DARK_THEME_ENABLE ? darkInputStyleLeft : inputStyleLeft}
                                                id='description'
                                                name='description'
                                                //    placeholder={AppLabels.DESCRIOTION}
                                                maxLength={300}
                                                onChange={this.handleDescriptionChange}
                                                value={this.state.contestDescription}
                                            />
                                        </FormGroup>
                                        <span className="bordered-span"></span>
                                    </Col>
                                    <div className='char-limit-container'>{(200 - (this.state.contestDescription.length))}/200 {AppLabels.CHARACTER_LEFT}</div>
                                </Row>
                                <div className='create-contest-info'>{AppLabels.CREATE_PRIVATE_DESC_MSG}</div>
                            </div>
                        </form>
                        <div onClick={() => this.canCreateContest(true)} className={'create-contest-btn ' + (this.canCreateContest(false) ? '' : ' disabled')}>
                            <span>{entryFee === '0' ? AppLabels.CREATE_INVITE : this.state.selEntryFee.value == 2 ? <div>{AppLabels.CREATE_SHARE}</div> : (AppLabels.CREATE_EARN + ' ' + hostEarn)}</span>
                        </div>
                        {showPrizeBreakupModal &&
                            <PrizeBreak
                                isShow={showPrizeBreakupModal}
                                isHide={this.onHideModal}
                                prize_distribution_data={prize_distribution_data}
                                noOfWinner={noOfWinner}
                                siteRake={siteRake}
                                hostRake={hostRake}
                                entryFee={entryFee}
                                selectedType={this.state.selEntryFee.value}
                                optionSelectedIndex={optionSelectedIndex}
                            />
                        }
                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}