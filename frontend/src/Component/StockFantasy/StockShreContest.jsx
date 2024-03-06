import React, { lazy, Suspense } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import CountdownTimer from '../../views/CountDownTimer';
import { MyContext } from '../../InitialSetup/MyProvider';
import ContestDetailModal from '../../Modals/ContestDetail';
import { createBrowserHistory } from 'history';
import { Utilities, checkBanState, _filter } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { getStockPublicContest, stockJoinContest, getStockUserAllTeams } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../../Component/CustomComponent';
import * as Constants from "../../helper/Constants";
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);

export default class StockShreContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            contestData: '',
            prizeList: [],
            showContestDetail: false,
            FixtureData: '',
            referredCodeForSignup: '',
            userTeamListSend: [],
            showConfirmationPopUp: false
        }
    }
    UNSAFE_componentWillMount() {
        WSManager.setShareContestJoin(true);
        WSManager.setPickedGameType(Constants.GameType.StockFantasy);
        this.checkForUserRefferal();
    }

    checkForUserRefferal() {
        if (parsed.referral != "") {
            WSManager.setReferralCode(parsed.referral)
        }
    }

    getPublicContest(data) {
        let param = {
            "contest_unique_id": data.contest_unique_id
        }
        getStockPublicContest(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                let data = responseJson.data;
                data['season_scheduled_date'] = data.scheduled_date;
                data['collection_master_id'] = data.collection_id;
                this.setState({
                    contestData: data,
                    prizeList: data.prize_distibution_detail
                })
                if (data.total_user_joined == data.size) {
                    Utilities.showToast(AL.Entry_for_the_contest, 3000);
                }
            }
        })
    }


    componentDidMount() {
        const matchParam = this.props.match.params
        this.getPublicContest(matchParam)
    }

    ContestDetailShow = (data) => {
        this.setState({
            FixtureData: data,
            showContestDetail: true,
        });
        if (WSManager.loggedIn()) {
            this.getUserLineUpListApi();
        }
    }

    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }

    ConfirmatioPopUpShow = (data) => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }

    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    getUserLineUpListApi = async () => {
        let param = {
            "collection_id": this.state.contestData.collection_master_id,
        }
        this.setState({ isLoaderShow: true })
        var api_response_data = await getStockUserAllTeams(param);
        if (api_response_data.data) {
            this.setState({
                userTeamListSend: api_response_data.data
            }, () => {
                if (this.state.userTeamListSend) {
                    let tempList = [];
                    this.state.userTeamListSend.map((data, key) => {
                        tempList.push({ value: data, label: data.team_name })
                        return '';
                    })
                    this.setState({ userTeamListSend: tempList });
                }
            })
        }
    }

    goToLineup = (FixturedContestItem) => {
        FixturedContestItem['collection_master_id'] = FixturedContestItem.collection_id;
        let name = this.state.contestData.category_id.toString() === "1" ? 'Daily' : this.state.contestData.category_id.toString() === "2" ? 'Weekly' : 'Monthly';
        let lineupPath = Constants.SELECTED_GAMET == Constants.GameType.StockFantasy ?  '/stock-fantasy/lineup/' + name : '/stock-fantasy-equity/lineup/' + name ;
        if (!WSManager.loggedIn()) {
            this.props.history.push({ pathname: '/signup', state: { lineupPath: lineupPath.toLowerCase(), joinContest: true, LobyyData: this.state.contestData, FixturedContest: FixturedContestItem, resetIndex: 1, collection_master_id: this.state.contestData.collection_master_id } })
        } else {
            this.props.history.push({
                pathname: lineupPath.toLowerCase(), state: {
                    FixturedContest: FixturedContestItem,
                    LobyyData: this.state.contestData,
                    resetIndex: 1,
                    collection_master_id: this.state.contestData.collection_master_id
                }
            })
        }
    }

    onSubmitBtnClick = (data) => {
        this.setState({ LobyyData: data })
        if (this.state.userTeamListSend.length > 0) {
            if (!WSManager.loggedIn()) {
                this.goToLineup(data)
            }
            else {
                if (checkBanState(data, CustomHeader)) {
                    this.setState({ showConfirmationPopUp: true })
                }
                this.setState({ showContestDetail: false })
            }
        }
        else {
            WSManager.clearLineup();
            this.goToLineup(data)
        }
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {
        if (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AL.SELECT_NAME_FIRST, 1000);
        } else {
            if (checkBanState(dataFromConfirmPopUp.FixturedContestItem, CustomHeader)) {
                var currentEntryFee = 0;
                currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;

                if (currentEntryFee <= dataFromConfirmPopUp.balanceAccToMaxPercent) {
                    this.CallJoinGameApi(dataFromConfirmPopUp);
                } else {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isStockF: true });
                }
            }
            else {
                this.ConfirmatioPopUpHide();
            }
        }
    }
    createTeamAndJoin = (dataFromConfirmFixture, dataFromConfirmLobby) => {
        if (checkBanState(dataFromConfirmFixture, CustomHeader)) {
            WSManager.clearLineup();
            this.goToLineup(dataFromConfirmFixture)
        }
        else {
            this.ConfirmatioPopUpHide();
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let param = {
            "contest_id": dataFromConfirmPopUp.FixturedContestItem.contest_id,
            "lineup_master_id": dataFromConfirmPopUp.selectedTeam.value.lineup_master_id,
            "promo_code": dataFromConfirmPopUp.promoCode,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType
        }
        this.setState({ isLoaderShow: true })

        let contestUid = dataFromConfirmPopUp.FixturedContestItem.contest_unique_id
        let contestAccessType = dataFromConfirmPopUp.FixturedContestItem.contest_access_type;
        let isPrivate = dataFromConfirmPopUp.FixturedContestItem.is_private;

        stockJoinContest(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.ConfirmatioPopUpHide();
                if (contestAccessType == '1' || isPrivate == '1') {
                    let deviceIds = [];
                    deviceIds = responseJson.data.user_device_ids;
                    WSManager.updateFirebaseUsers(contestUid, deviceIds);
                }
                setTimeout(() => {
                    this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();

            } else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
    }

    ThankYouModalShow = (data) => {
        this.setState({
            showThankYouModal: true,
        });
    }

    ThankYouModalHide = () => {
        this.setState({
            showThankYouModal: false,
        });
    }


    goToLobby = () => {
        this.props.history.push({ pathname: '/' });
    }

    seeMyContest = () => {
        this.props.history.push({ pathname: '/my-contests', state: { from: 'SelectCaptain' } });
    }


    ShowProgressBar = (join, total) => {
        return join * 100 / total;
    }

    getPrizeAmount = (prize_data) => {
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        return (
            <React.Fragment>
                {
                    prize_data && prize_data.map(function (lObj, lKey) {
                        var amount = 0;
                        if (lObj.max_value) {
                            amount = parseFloat(lObj.max_value);
                        } else {
                            amount = parseFloat(lObj.amount);
                        }
                        if (lObj.prize_type == 3) {
                            is_tie_breaker = 1;
                        }
                        if (lObj.prize_type == 0) {
                            prizeAmount['bonus'] = parseFloat(prizeAmount['bonus']) + amount;
                        } else if (lObj.prize_type == 2) {
                            prizeAmount['point'] = parseFloat(prizeAmount['point']) + amount;
                        } else {
                            prizeAmount['real'] = parseFloat(prizeAmount['real']) + amount;
                        }
                    })
                }

                {
                    is_tie_breaker == 0 && prizeAmount.real > 0 ?
                        <span className="contest-prizes">{Utilities.getMasterData().currency_code}{parseFloat(prizeAmount.real).toFixed(0)}</span>
                        : is_tie_breaker == 0 && prizeAmount.bonus > 0 ? <span className="contest-prizes margin-contest"><i className="icon-bonus" />{parseFloat(prizeAmount.bonus).toFixed(0)}</span>
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ marginLeft: '14px', display: 'inlineBlock' }}> <img className="img-coin-contest" src={Images.IC_COIN} alt='' />{parseFloat(prizeAmount.point).toFixed(0)}</span>
                                : AL.PRIZES
                }
            </React.Fragment>
        )


    }

    render() {
        const {
            contestData,
            showContestDetail,
            FixtureData,
            showConfirmationPopUp,
            userTeamListSend,
            showThankYouModal
        } = this.state;

        const HeaderOption = {
            back: false,
            filter: false,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
            title: AL.Contest + ' ' + AL.DETAILS
        }

        let category_id = contestData.category_id || ''
        let name = category_id.toString() === "1" ? AL.DAILY : category_id.toString() === "2" ? AL.WEEKLY : category_id.toString() === "3" ? AL.MONTHLY : '';
        let icon = category_id.toString() === "1" ? Images.stock_24 : category_id.toString() === "2" ? Images.stock_mon : category_id.toString() === "3" ? Images.stock_cal : '';

        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container header-margin web-container-fixed share-contest-wrapper white-bg">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <meta name="keywords" content={MetaData.sharedcontest.keywords} />
                            <title>{MetaData.sharedcontest.title}</title>
                            <meta name="description" content={contestData ? contestData.collection_name + " | " + contestData.contest_name : MetaData.sharedcontest.description} />
                            <meta property="og:title" content={contestData ? contestData.contest_name : MetaData.sharedcontest.title}></meta>
                            <link rel="canonical" href={window.location.href} />
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="contest-card contest-card-wrapper">
                            <div className="contest-card-header ">
                                <ul>
                                    <React.Fragment>
                                        <li className="progress-middle">
                                            <div className="team-content pb10 public-contest stk">
                                                {icon && <img className='stk-img' src={icon} alt="" />}
                                                <p>{name} {AL.STOCK_FANTASY}</p>
                                                {
                                                    Utilities.showCountDown(contestData) ?
                                                        <div className="share-contest-countdown">
                                                            {contestData.game_starts_in && <CountdownTimer deadlineTimeStamp={contestData.game_starts_in} />}
                                                        </div> :
                                                        <span className="share-contest-time-date">
                                                            <MomentDateComponent data={{ date: contestData.season_scheduled_date, format: "D MMM - hh:mm A " }} />
                                                        </span>
                                                }
                                            </div>
                                        </li>
                                    </React.Fragment>
                                </ul>
                            </div>


                            <div className="contest-list contest-card-body" >
                                <div className="contest-list-header p-3">
                                    <div className="contest-heading">
                                        <div className="featured-icon-wrap">
                                            {contestData.multiple_lineup > 1 &&
                                                <span className="featured-icon new-featured-icon multi-feat" onClick={(e) => e.stopPropagation()}>{AL.MULTI}</span>
                                            }
                                            {
                                                contestData.guaranteed_prize == 2 &&
                                                parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                <span className="featured-icon new-featured-icon gau-feat" onClick={(e) => e.stopPropagation()}>{AL.GUARANTEED}</span>
                                            }
                                            {
                                                contestData.is_confirmed == 1 &&
                                                parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                <span className="featured-icon new-featured-icon conf-feat" onClick={(e) => e.stopPropagation()}>
                                                    {AL.CONFIRMED}
                                                </span>
                                            }
                                            {
                                                contestData.is_private == 1 &&
                                                <span style={{ position: "initial" }} className="featured-icon" onClick={(e) => e.stopPropagation()}>p</span>
                                            }
                                        </div>
                                        {/* -----assured code here----*/}
                                        <h3 className="win-type position-relative">
                                            {
                                                contestData.contest_title ?
                                                    <span>
                                                        {contestData.contest_title}
                                                    </span>
                                                    :
                                                    <span >
                                                        <span className="text-uppercase " >
                                                            {AL.WIN + " "}
                                                        </span>
                                                        {this.getPrizeAmount(this.state.prizeList)}
                                                    </span>
                                            }
                                        </h3>
                                        {
                                            contestData.max_bonus_allowed != '0' &&
                                            <div className="text-small-italic">
                                                {contestData.max_bonus_allowed}{'% '}{AL.BONUS}
                                            </div>
                                        }
                                    </div>
                                    <div className="display-table">
                                        <div className="progress-bar-default display-table-cell v-mid" >
                                            <ProgressBar now={this.ShowProgressBar(contestData.total_user_joined, contestData.minimum_size)} className={parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) ? ' ' : 'danger-area'} />
                                            <div className="progress-bar-value" >
                                                <span className="user-joined">{contestData.total_user_joined}</span><span className="total-entries"> / {contestData.size} {AL.ENTRIES}</span>
                                                <span className="min-entries">{AL.MIN} {contestData.minimum_size}</span>
                                            </div>
                                        </div>
                                        <div className="display-table-cell v-mid position-relative entry-criteria pl15" >
                                            {parseInt(contestData.total_user_joined) < parseInt(contestData.size) && <button onClick={() => this.ContestDetailShow(contestData)}
                                                className="white-base btnStyle btn-rounded btn btn-primary ">
                                                {
                                                    contestData.currency_type == 2 ?
                                                        <React.Fragment>
                                                            <img src={Images.IC_COIN} alt="" className="img-coin" />
                                                            {contestData.entry_fee}
                                                        </React.Fragment>
                                                        :
                                                        <>
                                                            {(contestData.prize_type == 0) &&
                                                                <React.Fragment>
                                                                    <span> <i className="icon-bonus"></i> </span>{contestData.entry_fee}
                                                                </React.Fragment>
                                                            }

                                                            {(contestData.prize_type == 1) &&
                                                                contestData.entry_fee > 0 ?
                                                                <React.Fragment>
                                                                    <span className="currency-span">{Utilities.getMasterData().currency_code}</span>
                                                                    {contestData.entry_fee}
                                                                </React.Fragment>
                                                                :
                                                                <React.Fragment>
                                                                    {AL.FREE}
                                                                </React.Fragment>
                                                            }


                                                            {contestData.prize_type == 2 &&
                                                                <React.Fragment>
                                                                    <img src={Images.COINS} alt="" className="beans-img" />
                                                                    {contestData.entry_fee}
                                                                </React.Fragment>
                                                            }
                                                        </>
                                                }
                                            </button>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button className="btn-primary bottom btn btn-primary-bottom-stk" onClick={() => this.props.history.push('/lobby')}>{AL.GO_TO_LOBBY}</button>
                        {showContestDetail &&
                            <ContestDetailModal
                                showPCError={true}
                                LobyyData={contestData}
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                OpenContestDetailFor={FixtureData}
                                isStockF={true}
                                {...this.props} />
                        }
                        {showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={this.ConfirmatioPopUpShow}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={userTeamListSend}
                                FixturedContest={FixtureData}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={FixtureData}
                                fromContestListingScreen={false}
                                TotalTeam={[]}
                                isStockF={true}
                                createdLineUp={''} />
                        }
                        {showThankYouModal &&
                            <Thankyou
                                ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.goToLobby}
                                seeMyContestEvent={this.seeMyContest}
                                isStock={true}
                                 />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}