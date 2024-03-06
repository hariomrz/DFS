import React, { lazy, Suspense } from 'react';
import { ProgressBar, OverlayTrigger, Tooltip } from 'react-bootstrap';
import Images from '../../components/images';
import WSManager from "../../WSHelper/WSManager";
import * as WSC from "../../WSHelper/WSConstants";
import * as AppLabels from "../../helper/AppLabels";
import CountdownTimer from '../../views/CountDownTimer';
import { MyContext } from '../../InitialSetup/MyProvider';
import ContestDetailModal from '../../Modals/ContestDetail';
import { createBrowserHistory } from 'history';
import { Utilities, checkBanState, _filter, convertToTimestamp, _Map } from '../../Utilities/Utilities';
import CustomHeader from '../../components/CustomHeader';
import { Sports } from "../../JsonFiles";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { getPublicContestDetail, getPublicContestDetailMultiGame, getUserTeams, getMultigameUserTeams, joinContest, getPublicContestNetworkfantasy, getPublicContestDetailLF } from '../../WSHelper/WSCallings';
import { MomentDateComponent } from '../../Component/CustomComponent';
import * as Constants from "../../helper/Constants";
import { AppSelectedSport, SELECTED_GAMET, GameType } from '../../helper/Constants';
import ls from 'local-storage';
import ConfirmationPopup from '../../Modals/ConfirmationPopup';
import Thankyou from '../../Modals/Thankyou';
const ReactSlickSlider = lazy(() => import('../../Component/CustomComponent/ReactSlickSlider'));

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
var globalThis = null;

export default class DMContest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            contestData: '',
            prizeList: [],
            showContestDetail: false,
            FixtureData: '',
            referredCodeForSignup: '',
            userTeamListSend: [],
            showConfirmationPopUp: false,
            is_network_contest: 0,
            allowCollection: Utilities.getMasterData().a_collection,
            allowRevFantasy: Utilities.getMasterData().a_reverse == '1',
            showRF: false
        }
    }
    UNSAFE_componentWillMount() {
        if (Utilities.getMasterData().a_dfst == 1) {
            ls.set('isDfsTourEnable', false)
        }
        Utilities.setScreenName('sharedcontest')

        WSManager.setShareContestJoin(true);
        // if(Constants.SELECTED_GAMET != Constants.GameType.MultiGame && Constants.SELECTED_GAMET != Constants.GameType.Free2Play){
        WSManager.setPickedGameType(Constants.GameType.DFS);
        //   }
        if (parsed.nf != "" && parsed.nf != null && parsed.nf == 1) {
            this.setState({ is_network_contest: 1 })
        }
        this.checkOldUrlPattern();
        this.checkForUserRefferal();
    }

    /**
     * @description this method is used to replace old url pattern to new eg. from "/7/contest-listing" to "/cricket/contest-listing"
     */
    checkOldUrlPattern = () => {

        let sportsId = this.props.match.params.sportsId;
        if (!(sportsId in Sports)) {
            if (sportsId in Sports.url) {
                let sportsId = this.props.match.params.sportsId;
                let contest_unique_id = this.props.match.params.contest_unique_id;
                this.props.history.replace("/" + Sports.url[sportsId] + "/contest/" + contest_unique_id);
                return;
            }
        }
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
        let methodApi =
        getPublicContestDetail
        //getPublicContestDetail ;
        methodApi(param).then((apiResponseData) => {
            if (apiResponseData.response_code == WSC.successCode) {
                let responseJson = apiResponseData.data
                if(responseJson.match) {
                    const { match, current_prize, prize_distibution_detail, ..._apiResponseData } = responseJson
                    let match_list = _Map(match, obj => {
                        return {...obj, game_starts_in: convertToTimestamp(obj.season_scheduled_date)}
                    })

                    responseJson = {..._apiResponseData, ...match_list[0], match_list: match_list, current_prize: JSON.parse(current_prize), prize_distibution_detail: JSON.parse(prize_distibution_detail), 
                        game_starts_in: convertToTimestamp(responseJson.season_scheduled_date)}
                }

                this.setState({
                    contestData: responseJson,
                    prizeList: responseJson.prize_distibution_detail,
                    showRF: (responseJson.is_reverse == "1" ? true : false) || false,
                    isSecIn: responseJson.is_2nd_inning == "1"
                })
                if (responseJson.total_user_joined == responseJson.size) {
                    Utilities.showToast(AppLabels.Entry_for_the_contest, 3000);
                }
            }
        })
    }


    componentDidMount() {
        globalThis = this;
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

    // onSubmitBtnClick = (data) => {
    //     WSManager.clearLineup();
    //     let urlData = data;
    //     let dateformaturl = parseURLDate(urlData.season_scheduled_date);
    //     let lineupPath = ''
    //     if(urlData.home){
    //         lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
    //     }
    //     else{
    //         let pathurl = Utilities.replaceAll(urlData.collection_name,' ','_');
    //         lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
    //     }

    //     if (WSManager.loggedIn()) {
    //         this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { FixturedContest: this.state.FixtureData, LobyyData: data,from:'share_contest' ,isFrom:'shareContest',resetIndex: 1, current_sport: Constants.AppSelectedSport } })
    //     }
    //     else {
    //         this.props.history.push({
    //             pathname: '/signup', state: {
    //                 joinContest: true,
    //                 lineupPath: lineupPath.toLowerCase(),
    //                 FixturedContest: this.state.FixtureData,
    //                 LobyyData: data
    //             }
    //         })
    //     }

    // }

    getUserLineUpListApi = async () => {
        let param = {
            "sports_id": AppSelectedSport,
            "collection_master_id": this.state.contestData.collection_master_id,
        }
        this.setState({ isLoaderShow: true })
        let user_data = ls.get('profile');
        var user_unique_id = 0;
        if (user_data && user_data.user_unique_id) {
            user_unique_id = user_data.user_unique_id;
        }
        var api_response_data = SELECTED_GAMET === GameType.DFS ? await getUserTeams(param, user_unique_id) : await getMultigameUserTeams(param, user_unique_id);;
        if (api_response_data) {
            let tList = this.state.isSecIn ? _filter(api_response_data, (obj, idx) => {
                return obj.is_2nd_inning == "1";
            }) : this.state.showRF ? _filter(api_response_data, (obj, idx) => {
                return obj.is_reverse == "1";
            }) : _filter(api_response_data, (obj, idx) => {
                return (obj.is_reverse != "1" && obj.is_2nd_inning != "1")
            })
            this.setState({
                userTeamListSend: tList
            })
            if (this.state.userTeamListSend) {
                let tempList = [];
                this.state.userTeamListSend.map((data, key) => {
                    tempList.push({ value: data, label: data.team_name })
                    return '';
                })
                this.setState({ userTeamListSend: tempList });
            }
        }
    }

    onSubmitBtnClick = (data) => {
        this.setState({ LobyyData: data })
        // if (!WSManager.loggedIn()) {
        //     setTimeout(() => {
        //         this.props.history.push({ pathname: '/signup' })
        //         Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
        //     }, 10);
        // } 
        // else {
        if (this.state.userTeamListSend.length > 0) {
            let urlData = data;

            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

            let lineupPath = '';
            if (urlData.home) {
                lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            }
            else {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            }

            if (!WSManager.loggedIn()) {
                setTimeout(() => {
                    this.props.history.push({ pathname: '/signup', state: { lineupPath: lineupPath.toLowerCase(), joinContest: true, LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecIn } })
                    Utilities.showToast(AppLabels.Please_Login_Signup_First, 3000);
                }, 10);
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
            let urlData = data;

            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
            let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
            dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();

            let lineupPath = '';
            if (urlData.home) {
                lineupPath = '/lineup/' + urlData.home + "-vs-" + urlData.away + "-" + dateformaturl
            }
            else {
                let pathurl = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                lineupPath = '/lineup/' + pathurl + "-" + dateformaturl
            }

            if (WSManager.loggedIn()) {
                this.props.history.push({ pathname: lineupPath.toLowerCase(), state: { LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecIn } })
            }
            else {
                this.props.history.push({ pathname: '/signup', state: { lineupPath: lineupPath.toLowerCase(), joinContest: true, LobyyData: data, FixturedContest: this.state.contestData, resetIndex: 1, collection_master_id: urlData.collection_master_id, current_sport: AppSelectedSport, isReverseF: this.state.showRF, isSecIn: this.state.isSecIn } })
            }
        }
        // }
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {
        if (dataFromConfirmPopUp.selectedTeam.lineup_master_id != null && dataFromConfirmPopUp.selectedTeam.lineup_master_id == "" || dataFromConfirmPopUp.selectedTeam == "") {
            Utilities.showToast(AppLabels.SELECT_NAME_FIRST, 1000);
        } else {
            if (checkBanState(dataFromConfirmPopUp.FixturedContestItem, CustomHeader)) {
                var currentEntryFee = 0;
                currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;

                if (currentEntryFee <= dataFromConfirmPopUp.balanceAccToMaxPercent) {
                    this.CallJoinGameApi(dataFromConfirmPopUp);
                } else {
                    //   setValue.setisFromConfirmModal(true);
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("SelectCaptainList")
                    this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, isSecIn: this.state.isSecIn });
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
            let urlData = this.state.LobyyData;
            let dateformaturl = Utilities.getUtcToLocal(urlData.season_scheduled_date);
            dateformaturl = new Date(dateformaturl);
            dateformaturl = dateformaturl.getDate() + '-' + (dateformaturl.getMonth() + 1) + '-' + dateformaturl.getFullYear();

            if (urlData.home) {
                this.props.history.push({ pathname: '/lineup/' + urlData.home.toLowerCase() + "-vs-" + urlData.away.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: {...this.state.LobyyData, game_starts_in: convertToTimestamp(this.state.LobyyData.season_scheduled_date)}, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecIn, isReverseF: this.state.showRF } })
            }
            else {
                let collectionName = Utilities.replaceAll(urlData.collection_name, ' ', '_');
                this.props.history.push({ pathname: '/lineup/' + collectionName.toLowerCase() + "-" + dateformaturl, state: { FixturedContest: dataFromConfirmFixture, LobyyData: {...this.state.LobyyData, game_starts_in: convertToTimestamp(this.state.LobyyData.season_scheduled_date)}, resetIndex: 1, current_sport: Constants.AppSelectedSport, isSecIn: this.state.isSecIn, isReverseF: this.state.showRF } })
            }
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

        joinContest(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
                    let singular_data = {};
                    singular_data.user_unique_id = WSManager.getProfile().user_unique_id;
                    singular_data.contest_id = dataFromConfirmPopUp.FixturedContestItem.contest_id;
                    singular_data.contest_date = dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date;
                    singular_data.fixture_name = dataFromConfirmPopUp.lobbyDataItem.collection_name;
                    singular_data.entry_fee = dataFromConfirmPopUp.FixturedContestItem.entryFeeOfContest;

                    if (window.ReactNativeWebView) {
                        let event_data = {
                            action: 'singular_event',
                            targetFunc: 'onSingularEventTrack',
                            type: 'Contest_joined',
                            args: singular_data,
                        }
                        window.ReactNativeWebView.postMessage(JSON.stringify(event_data));
                    }
                    else {
                        window.SingularEvent("Contest_joined", singular_data);
                    }
                }
                Utilities.gtmEventFire('join_contest', {
                    fixture_name: dataFromConfirmPopUp.lobbyDataItem.collection_name,
                    contest_name: dataFromConfirmPopUp.FixturedContestItem.contest_title,
                    league_name: dataFromConfirmPopUp.lobbyDataItem.league_name,
                    entry_fee: dataFromConfirmPopUp.FixturedContestItem.entry_fee,
                    fixture_scheduled_date: Utilities.getFormatedDateTime(dataFromConfirmPopUp.lobbyDataItem.season_scheduled_date, 'YYYY-MM-DD HH:mm:ss'),
                    contest_joining_date: Utilities.getFormatedDateTime(new Date(), 'YYYY-MM-DD HH:mm:ss'),
                })

                this.ConfirmatioPopUpHide();
                // if(contestAccessType=='1' || isPrivate=='1'){
                //     WSManager.updateFirebaseUsers(contestUid);
                // }
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

    getContestWinnerCount(prizeDistributionDetail) {
        if (prizeDistributionDetail && prizeDistributionDetail.length > 0) {
            if ((prizeDistributionDetail[prizeDistributionDetail.length - 1].max) > 1) {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNERS
            } else {
                return prizeDistributionDetail[prizeDistributionDetail.length - 1].max + " " + AppLabels.WINNER
            }
        }
    }

    FixtureListFunction = (item) => {
        return (
            <div className="collection-list">
                <div className="display-table">
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.home_flag)} alt="" className="team-img" />
                    </div>
                    <div className="display-table-cell text-center v-mid w-lobby-40">
                        <div className="team-block">
                            <span className="team-name text-uppercase">{item.home}</span>
                            <span className="verses">{AppLabels.VS}</span>
                            <span className="team-name text-uppercase">{item.away}</span>
                        </div>
                        <div className="match-timing">
                            {
                                Utilities.showCountDown(item) ?
                                    <div className="countdown time-line">
                                        {item.game_starts_in && <CountdownTimer deadlineTimeStamp={item.game_starts_in} currentDateTimeStamp={item.today} />}
                                    </div> :
                                    <span> <MomentDateComponent data={{ date: item.season_scheduled_date, format: "D MMM - hh:mm A " }} /></span>
                            }
                        </div>
                    </div>
                    <div className="display-table-cell text-center v-mid w20">
                        <img src={Utilities.teamFlagURL(item.away_flag)} alt="" className="team-img" />
                    </div>
                </div>
            </div>
        );
    }


    getPrizeAmount = (prize_data) => {
        let prize_text = "Prizes";
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
                            : is_tie_breaker == 0 && prizeAmount.point > 0 ? <span style={{ marginLeft: '14px', display: 'inlineBlock' }}> <img className="img-coin-contest" src={Images.IC_COIN} />{parseFloat(prizeAmount.point).toFixed(0)}</span>
                                : AppLabels.PRIZES
                }
            </React.Fragment>
        )


    }



    render() {
        globalThis = this;
        const {
            contestData,
            showContestDetail,
            FixtureData,
            showConfirmationPopUp,
            userTeamListSend,
            showThankYouModal,
            allowCollection,
            allowRevFantasy,
            showRF
        } = this.state;

        const HeaderOption = {
            back: false,
            filter: false,
            isPrimary: Constants.DARK_THEME_ENABLE ? false : true,
            title: AppLabels.Contest
        }

        var settings = {
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            variableWidth: false,
            // centerPadding: '100px 0 5px',
            innerWidth: 400,
            initialSlide: 0,
            className: "center",
            centerMode: true,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1,
                    }
                },
                {
                    breakpoint: 414,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '60px 0 10px',
                    }
                },
                {
                    breakpoint: 320,
                    settings: {
                        slidesToShow: 1,
                        centerPadding: '20px 0 10px',
                        afterChange: '',
                    }
                }
            ]
        };
        let rookie_setting = Utilities.getMasterData().rookie_setting || '';
        let isRookie = contestData.group_id == rookie_setting.group_id;
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container header-margin web-container-fixed share-contest-wrapper" + (Constants.SELECTED_GAMET == Constants.GameType.MultiGame && contestData && contestData.match_list && contestData.match_list.length > 1 ? ' share-collection-wrapper' : ' ')}>
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <meta name="keywords" content={MetaData.sharedcontest.keywords} />

                            <title>{MetaData.sharedcontest.title}</title>
                            <meta name="description" content={contestData ? contestData.collection_name + " | " + contestData.contest_name : MetaData.sharedcontest.description} />
                            {/* <meta property="og:description" content={contestData ? contestData.collection_name+" | "+contestData.contest_name : MetaData.sharedcontest.description} /> */}
                            <meta property="og:title" content={contestData ? contestData.contest_name : MetaData.sharedcontest.title}></meta>
                            <link rel="canonical" href={window.location.href} />
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="contest-card contest-card-wrapper">
                            <div className="contest-card-header public-contest">
                                <ul className={"fixture-list-content"}>
                                    {/* { Constants.SELECTED_GAMET != Constants.GameType.MultiGame  &&
                                            <React.Fragment>
                                                <li className="team-left-side">
                                                    <div className="team-content-img">
                                                        <img src={contestData.home_flag ? Utilities.teamFlagURL(contestData.home_flag) : ""} alt="" />
                                                    </div>
                                                    <span className="team-name">{contestData.home}</span> 
                                                </li>
                                                <li className="progress-middle">
                                                    <div className="team-content pb10 public-contest">
                                                        <p>{contestData.league_name}</p>
                                                        {
                                                            Utilities.showCountDown(contestData) ?

                                                                <div className="share-contest-countdown">
                                                                    {contestData.game_starts_in && <CountdownTimer deadlineTimeStamp={contestData.game_starts_in} />}
                                                                </div> :
                    
                                                                <span className="share-contest-time-date"> 
                                                                    <MomentDateComponent data={{date:contestData.season_scheduled_date,format:"D MMM - hh:mm A "}} /> 
                                                                </span>

                                                        }
                                                    </div>
                                                </li>
                                                <li className="team-right-side">
                                                    <span className="team-name">{contestData.away}</span>
                                                    <div className="team-content-img">
                                                        <img src={contestData.away_flag ? Utilities.teamFlagURL(contestData.away_flag) : ""} alt="" />
                                                    </div>
                                                </li>
                                            </React.Fragment>
                                        } */}
                                    {
                                        // Constants.SELECTED_GAMET == Constants.GameType.MultiGame  &&
                                        contestData && contestData.match_list && contestData.match_list.length == 1 &&
                                        <React.Fragment>
                                            <li className="team-left-side">
                                                <div className="team-content-img">
                                                    <img src={contestData.match_list ? Utilities.teamFlagURL(contestData.match_list[0].home_flag) : ""} alt="" />
                                                </div>
                                                <span className="team-name">{contestData.match_list[0].home}</span>
                                            </li>
                                            <li className="progress-middle">
                                                <div className="team-content pb10 public-contest">
                                                    <p>{contestData.match_list[0].league_name}</p>
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
                                            <li className="team-right-side">
                                                <span className="team-name">{contestData.match_list[0].away}</span>
                                                <div className="team-content-img">
                                                    <img src={contestData.match_list ? Utilities.teamFlagURL(contestData.match_list[0].away_flag) : ""} alt="" />
                                                </div>
                                            </li>
                                        </React.Fragment>
                                    }
                                    {
                                        // Constants.SELECTED_GAMET == Constants.GameType.MultiGame  && 
                                        contestData && contestData.match_list && contestData.match_list.length > 1 &&
                                        <li className="progress-middle progress-middle-fullwidth w-100 ">
                                            <div className="team-content pb10">
                                                <p>{contestData.collection_name}</p>
                                                <div className="collection-match-info">
                                                    {contestData.match_list.length} {AppLabels.MATCHES}
                                                    <span className="circle-divider"></span>
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
                                            </div>
                                            <div className="collection-body">
                                                <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}
                                                    // slideIndex={this.state.slideIndex}
                                                >
                                                    {contestData.match_list && contestData.match_list.map((item, index) => {
                                                        return (
                                                            <React.Fragment>
                                                                <div className="collection-list-slider">
                                                                    {this.FixtureListFunction(item)}
                                                                </div>
                                                            </React.Fragment>
                                                        );
                                                    })
                                                    }
                                                </ReactSlickSlider></Suspense>
                                            </div>
                                        </li>
                                    }
                                </ul>
                            </div>


                            <div className="contest-list contest-card-body" >
                                <div className="contest-list-header">
                                    <div className="contest-heading">
                                        <div className="featured-icon-wrap">
                                            {/* <div className="contest-name">{contestData.contest_name} </div> */}
                                            {contestData.multiple_lineup > 1 &&
                                                // <i className={"icon-m  contest-type " + ((contestData.guaranteed_prize == 2 && parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size)) ? '' : 'right-0')}></i>

                                                <span className="featured-icon new-featured-icon multi-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.MULTI}</span>
                                            }
                                            {
                                                contestData.guaranteed_prize == 2 &&
                                                parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                // <i className="icon-g contest-type"></i>
                                                <span className="featured-icon new-featured-icon gau-feat" onClick={(e) => e.stopPropagation()}>{AppLabels.GUARANTEED}</span>

                                            }
                                            {
                                                contestData.is_confirmed == 1 &&
                                                parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) &&
                                                <span className="featured-icon new-featured-icon conf-feat" onClick={(e) => e.stopPropagation()}>
                                                    {AppLabels.CONFIRMED}
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
                                                        {
                                                            Constants.SELECTED_GAMET == Constants.GameType.DFS && allowRevFantasy && showRF &&
                                                            <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                    <strong>{AppLabels.RF_TOOLTIP_TEXT}</strong>
                                                                </Tooltip>
                                                            }>
                                                                <img src={Images.REVERSE_FANTASY_ICON} alt="" className="rev-fan-img" />
                                                            </OverlayTrigger>
                                                        }
                                                        {this.state.isSecIn &&
                                                            <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                <Tooltip id="tooltip" >
                                                                    <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                </Tooltip>
                                                            }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                                    </span>
                                                    :
                                                    <span >
                                                        <span className="text-uppercase " >
                                                            {AppLabels.WIN + " "}
                                                        </span>
                                                        {this.getPrizeAmount(this.state.prizeList)}
                                                        {
                                                            Constants.SELECTED_GAMET == Constants.GameType.DFS && allowRevFantasy && showRF &&
                                                            <OverlayTrigger trigger={['hover', 'focus']} placement="right" overlay={
                                                                <Tooltip id="tooltip" className="tooltip-featured">
                                                                    <strong>{AppLabels.RF_TOOLTIP_TEXT}</strong>
                                                                </Tooltip>
                                                            }>
                                                                <img src={Images.REVERSE_FANTASY_ICON} alt="" className="rev-fan-img" />
                                                            </OverlayTrigger>
                                                        }
                                                        {this.state.isSecIn &&
                                                            <OverlayTrigger trigger={['hover']} placement="right" overlay={
                                                                <Tooltip id="tooltip" >
                                                                    <strong>{AppLabels.SEC_INNING_CHANCES}</strong>
                                                                </Tooltip>
                                                            }><span onClick={(e) => e.stopPropagation()} className='sec-in-tool'>{AppLabels.SEC_INNING}</span></OverlayTrigger>}
                                                    </span>
                                            }
                                        </h3>
                                        {
                                            contestData.max_bonus_allowed != '0' &&
                                            <div className="text-small-italic">
                                                {contestData.max_bonus_allowed}{'% '}{AppLabels.BONUS}
                                            </div>
                                        }
                                    </div>
                                    <div className="display-table">
                                        <div className="progress-bar-default display-table-cell v-mid" >
                                            <ProgressBar now={globalThis.ShowProgressBar(contestData.total_user_joined, contestData.minimum_size)} className={parseInt(contestData.total_user_joined) >= parseInt(contestData.minimum_size) ? ' ' : 'danger-area'} />
                                            <div className="progress-bar-value" >
                                                {
                                                    <>
                                                        <span className="total-entries">{Utilities.numberWithCommas(parseFloat(contestData.size) - parseFloat(contestData.total_user_joined))}  {AppLabels.SPOTS_LEFT}</span>
                                                        <span className="min-entries">{Utilities.numberWithCommas(contestData.size)}{" " + AppLabels.SPOTS} </span>
                                                    </>
                                                }
                                            </div>
                                        </div>
                                        <div className="display-table-cell v-mid position-relative entry-criteria pl15" >
                                            {parseInt(contestData.total_user_joined) < parseInt(contestData.size) && <button onClick={() => this.ContestDetailShow(contestData)}
                                                className={"white-base btnStyle btn-rounded btn btn-primary " + (isRookie ? ' btn-rookie' : '')}>
                                                {/* {AppLabels.JOIN}  */}
                                                {
                                                    contestData.currency_type == 2 ?
                                                        <React.Fragment>
                                                            <img src={Images.IC_COIN} alt="" className="img-coin" />
                                                            {contestData.entry_fee}
                                                        </React.Fragment>
                                                        :
                                                        <>
                                                            {(contestData.currency_type == 1) &&
                                                                contestData.entry_fee > 0 ?
                                                                <React.Fragment>
                                                                    <span className="currency-span">{Utilities.getMasterData().currency_code}</span>
                                                                    {contestData.entry_fee}
                                                                </React.Fragment>
                                                                :
                                                                <React.Fragment>
                                                                    {AppLabels.FREE}
                                                                </React.Fragment>
                                                            }


                                                            {contestData.currency_type == 2 &&
                                                                <React.Fragment>
                                                                    <img src={Images.COINS} alt="" className="beans-img" />
                                                                    {contestData.entry_fee}
                                                                </React.Fragment>
                                                            }
                                                        </>
                                                }
                                                {isRookie && <img style={{ top: '-23px' }} src={Images.ROOKIE_LOGO} alt='' className='rookie-img' />}
                                            </button>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button className="btn-block btn-primary bottom btn btn-default" onClick={() => this.props.history.push('/lobby')}>{AppLabels.GO_TO_LOBBY}</button>
                        {showContestDetail &&
                            <ContestDetailModal
                                showPCError={true}
                                LobyyData={contestData}
                                IsContestDetailShow={showContestDetail}
                                onJoinBtnClick={this.onSubmitBtnClick}
                                IsContestDetailHide={this.ContestDetailHide}
                                isSecIn={this.state.isSecIn}
                                OpenContestDetailFor={FixtureData}
                                profileShow={WSManager.getProfile()}
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
                                createdLineUp={''} />
                        }
                        {showThankYouModal &&
                            <Thankyou
                                ThankyouModalShow={this.ThankYouModalShow}
                                ThankYouModalHide={this.ThankYouModalHide}
                                goToLobbyClickEvent={this.goToLobby}
                                seeMyContestEvent={this.seeMyContest} />
                        }

                    </div>
                )}
            </MyContext.Consumer>
        )
    }
}