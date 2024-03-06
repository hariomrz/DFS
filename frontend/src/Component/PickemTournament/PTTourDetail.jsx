import React, { Component, lazy, Suspense } from 'react';
import { Tab, Row, Col, Nav, NavItem } from 'react-bootstrap';
import * as AL from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import { MyContext } from '../../InitialSetup/MyProvider';
import Images from '../../components/images';
import { getPTTourDetail, getLineupWithScore, callSubmitPickem, submitPTTieBreaker, getPTJoinTour } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import { Helmet } from "react-helmet";
import MetaData from "../../helper/MetaData";
import { _times, Utilities, _Map, parseURLDate, _isUndefined, _isEmpty, _filter } from '../../Utilities/Utilities';
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import CustomHeader from '../../components/CustomHeader';
import { AppSelectedSport, DARK_THEME_ENABLE, CONTEST_LIVE, CONTEST_UPCOMING, CONTEST_COMPLETED, SELECTED_GAMET } from '../../helper/Constants';
import { NoDataView } from '../CustomComponent';
import { MomentDateComponent } from '../CustomComponent';
import FixtureContest from "../../views/FixtureContest";
import FieldView from "../../views/FieldView";
import PTTourQueList from './PTTourQueList';
import PTContestDetailModal from './PTContestDetailModal';
import { ConfirmationPopup } from '../../Modals';
import CountdownTimer from '../../views/CountDownTimer';
import Slider from 'react-rangeslider';
import 'react-rangeslider/lib/index.css';
import WSManager from "../../WSHelper/WSManager";
import PTScoreSelModal from './PTScoreSelModal';
import WhatsTourModal from '../../Modals/WhatsTourModal';
import { PTFixtureDetailModal } from '.';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));

class PTTourDetail extends Component {
    constructor(props) {
        super(props);
        this.state = {
            detail: [],
            tournamenId: this.props.match.params.tourId,
            selectedTab: CONTEST_UPCOMING,
            fixtureList: [],
            isLoading: false,
            showContestDetail: false,
            detailData: '',
            activeTab: '0',
            showFixDetail: false,
            activeUserDetail: '',
            AllLineUPData: '',
            showFieldView: false,
            activeFix: '',
            perfectScore: '',
            tie_breaker_question: '',
            userTieValue: '',
            reloadList: false,
            realPSSum: 0,
            showConfirmationPopUp: false,
            isSportPredictor: false,
            winGoal: Utilities.getMasterData().pickem_win_goal,
            winGoalDiff: Utilities.getMasterData().pickem_win_goal_diff,
            winOnly: Utilities.getMasterData().pickem_win_only,
            maxPredictValue: Utilities.getMasterData().pickem_max_goal,
            showScoreModal: false,
            scorePredictItem: '',
            scorePredictFor: '',
            predictValueArray: [],
            selectedawayScore: '',
            selectedhomeScore: '',
            showTourNew: false
        }
    }


    componentDidMount = () => {
        this.callTournamentdeatilApi()
    }

    //for join flow

    joinTournament = (item) => {
        if (WSManager.loggedIn()) {
            if (Utilities.getMasterData().a_aadhar == "1") {
                if (this.props.location.state.aadharData && this.props.location.state.aadharData.aadhar_status == "1") {
                    this.setState({
                        LobyyData: this.props.location.state.itemContest,
                        FixtureData: this.props.location.state.itemContest,
                        showConfirmationPopUp: true,
                    })
                }
                else {
                    this.aadharConfirmation()
                }
            }
            else {
                this.setState({
                    LobyyData: this.props.location.state.itemContest,
                    FixtureData: this.props.location.state.itemContest,
                    showConfirmationPopUp: true,
                })
            }
        }
        else {
            this.props.history.push({ pathname: '/signup' })
        }
    }

    aadharConfirmation = () => {
        if (this.props.location.state.aadharData.aadhar_status == "0" && this.props.location.state.aadharData.aadhar_id) {
            Utilities.showToast(AL.VERIFICATION_PENDING_MSG, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AL.AADHAAR_NOT_UPDATED, 3000);
            this.props.history.push({ pathname: '/aadhar-verification' })
        }
    }

    /**
     * 
     * @description method to display confirmation popup model, when user join contest.
     */
    ConfirmatioPopUpShow = () => {
        this.setState({
            showConfirmationPopUp: true,
        });
    }
    /**
     * 
     * @description method to hide confirmation popup model
     */
    ConfirmatioPopUpHide = () => {
        this.setState({
            showConfirmationPopUp: false,
        });
    }

    ConfirmEvent = (dataFromConfirmPopUp) => {
        this.JoinGameApiCall(dataFromConfirmPopUp)
    }

    JoinGameApiCall = (dataFromConfirmPopUp) => {
        var currentEntryFee = 0;
        currentEntryFee = dataFromConfirmPopUp.entryFeeOfContest;
        if (
            (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2 && (parseInt(currentEntryFee) <= parseInt(dataFromConfirmPopUp.balanceAccToMaxPercent))) ||
            (dataFromConfirmPopUp.FixturedContestItem.currency_type != 2 && (parseFloat(currentEntryFee) <= parseFloat(dataFromConfirmPopUp.balanceAccToMaxPercent)))
        ) {
            this.CallJoinGameApi(dataFromConfirmPopUp);
        }
        else {
            if (dataFromConfirmPopUp.FixturedContestItem.currency_type == 2) {
                if (Utilities.getMasterData().allow_buy_coin == 1) {
                    WSManager.setFromConfirmPopupAddFunds(true);
                    WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                    WSManager.setPaymentCalledFrom("ContestListing")
                    this.props.history.push({ pathname: '/buy-coins', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { isFrom: 'contestList' } });

                }
                else {
                    this.props.history.push({ pathname: '/earn-coins', state: { isFrom: 'lineup-flow' } })
                }
            }

            else {
                WSManager.setFromConfirmPopupAddFunds(true);
                WSManager.setContestFromAddFundsAndJoin(dataFromConfirmPopUp)
                WSManager.setPaymentCalledFrom("ContestListing")
                this.props.history.push({ pathname: '/add-funds', contestDataForFunds: dataFromConfirmPopUp, fromConfirmPopupAddFunds: true, state: { amountToAdd: dataFromConfirmPopUp.AmountToAdd } });
            }
        }
    }

    CallJoinGameApi(dataFromConfirmPopUp) {
        let ApiAction = getPTJoinTour;
        let param = {
            "tournament_id": dataFromConfirmPopUp.FixturedContestItem.tournament_id,
            "device_type": window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType,
        }
        ApiAction(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                this.callTournamentdeatilApi()
                this.ConfirmatioPopUpHide();

                this.setState({
                    lineup_master_idArray: [],
                    lineup_master_id: ''
                })
                setTimeout(() => {
                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'pickem_contestjoin');

                    WSManager.googleTrackDaily(WSC.GA_PROFILE_ID, 'pickem_contestjoin');
                    //this.ThankYouModalShow()
                }, 300);
                WSManager.clearLineup();
            } else {
                if (Utilities.getMasterData().allow_self_exclusion == 1 && responseJson.data && responseJson.data.self_exclusion_limit == 1) {
                    this.ConfirmatioPopUpHide();
                    this.showUJC();
                }
                else {
                    Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                }
            }
        })
    }


    callTournamentdeatilApi = async () => {
        if (AppSelectedSport == null)
            return;
        this.setState({
            isLoading: true
        })
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_id": this.state.tournamenId
        }
        let apiResponse = await getPTTourDetail(param)
        if (apiResponse) {
            this.setState({
                selectedTab: apiResponse.data.status == "3" ? CONTEST_COMPLETED : CONTEST_UPCOMING,
                detail: apiResponse.data,
                fixtureList: apiResponse.data.match,
                isLoading: false,
                perfect_Score: this.handlePerfectScore(apiResponse.data.perfect_score || ''),
                tie_breaker_question: this.handlePerfectScore(apiResponse.data.tie_breaker_question || ''),
                isSportPredictor: apiResponse.data.is_score_predict && apiResponse.data.is_score_predict == 1 ? true : false
            }, () => {
                this.prizepoolSubmition(this.state.perfect_Score)
                if (this.state.isSportPredictor) {
                    let tmpArray = []
                    for (var i = 0; i <= this.state.maxPredictValue; i++) {
                        tmpArray.push(i)
                    }
                    this.setState({
                        predictValueArray: tmpArray
                    })
                }
                this.setState({
                    userTieValue: apiResponse.data.tie_breaker_user ? apiResponse.data.tie_breaker_user : this.state.tie_breaker_question.start
                })
            })
        }
    }

    handlePerfectScore = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    /**
     * @description Event of tab click (Live, Upcoming, Completed)
     * @param selectedTab value of selected tab
     */
    onTabClick = (selectedTab) => {
        this.setState({ selectedTab: selectedTab });
    }

    optionSelection = (data, optId, isPickReset) => {
        // this.setState({
        //     isLoading: true
        // })
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_id": this.state.tournamenId,
            "user_tournament_id": this.state.detail.user_tournament_id,
            "season_id": data.season_id,
            "team_id": optId,
            "user_team_id": optId,
            "score_predict": "0"
        }
        callSubmitPickem(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                if (isPickReset) {
                    Utilities.showToast(AL.YOU_HAVE_WITHDRAWN_YOUR_SELECTION, 2000);
                }
                else {
                    Utilities.showToast(responseJson.message, 2000);
                }
                this.callTournamentdeatilApi()
            }
            else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
    }

    timerCompletionCall = (item) => {
        this.setState({
            reloadList: true
        }, () => {
            this.setState({
                reloadList: false
            })
        })
    }

    selectScoreMdl = (isfor, item) => {
        if (this.state.scorePredictItem && this.state.scorePredictItem.season_id && item.season_id != this.state.scorePredictItem.season_id) {
            this.setState({
                selectedhomeScore: '',
                selectedawayScore: ''
            })
        }
        this.setState({
            showScoreModal: true,
            scorePredictItem: item,
            scorePredictFor: isfor
        })
    }

    hideScoreMdl = () => {
        this.setState({
            showScoreModal: false,
            scorePredictFor: ''
        })
    }

    userScoreSelection = (item) => {
        let selItemData = this.state.scorePredictItem
        if (this.state.scorePredictFor == 'home') {
            this.setState({
                selectedhomeScore: item == '' ? '-' : item
            }, () => {
                if ((this.state.selectedawayScore != '' || this.state.selectedawayScore == '0') || (!_isUndefined(selItemData.away_predict) && selItemData.away_predict != '')) {
                    this.optionScoreSelection()
                }
            })
        }
        if (this.state.scorePredictFor == 'away') {
            this.setState({
                selectedawayScore: item == '' ? '-' : item
            }, () => {
                if ((this.state.selectedhomeScore != '' || this.state.selectedhomeScore == '0') || (!_isUndefined(selItemData.home_predict) && selItemData.away_predict != '')) {
                    this.optionScoreSelection()
                }
            })
        }
        this.hideScoreMdl()
    }

    optionScoreSelection = () => {
        let data = this.state.scorePredictItem
        let homeScore = (this.state.selectedhomeScore != '' || this.state.selectedhomeScore == '0') ? this.state.selectedhomeScore : data.home_predict
        let awayScore = (this.state.selectedawayScore != '' || this.state.selectedawayScore == '0') ? this.state.selectedawayScore : data.away_predict
      
        let AwayScore = awayScore == "-" ? "0" : awayScore
        let HomeScore = homeScore == "-" ? "0" : homeScore
        let teamId = parseInt(AwayScore) > parseInt(HomeScore) ? data.away_id : (parseInt(AwayScore) == parseInt(HomeScore) ? 0 : data.home_id)
        let param = {
            "sports_id": AppSelectedSport,
            "tournament_id": this.state.tournamenId,
            "user_tournament_id": this.state.detail.user_tournament_id,
            "season_id": data.season_id,
            "team_id": teamId,
            "user_team_id": teamId,
            "score_predict": "1",
            "away_predict": awayScore == '-' ? 0 : awayScore,
            "home_predict": homeScore == '-' ? 0 : homeScore,
            "is_reset": awayScore == "-" || homeScore == "-" ? 1 : 0,
        }
        callSubmitPickem(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {

                Utilities.showToast(responseJson.message, 2000);
                this.setState({
                    showScoreModal: false,
                    scorePredictItem: '',
                    scorePredictFor: '',
                    selectedawayScore: '',
                    selectedhomeScore: ''
                })
                this.callTournamentdeatilApi()
            }
            else {
                Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
            }
        })
    }




    renderFixtureView = (fixtureList, isFor) => {
        const { isSportPredictor, winGoal, winGoalDiff, winOnly, detail, tie_breaker_question } = this.state

        let List = isFor == 'comp' ?
            fixtureList.filter(obj => (obj.status == 2 || obj.status == 3 || obj.status == 4))
            :
            (isFor == 'live' ?
                fixtureList.filter(
                    obj => (
                        obj.status != 2 && obj.status != 4 && Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') >= Utilities.getFormatedDateTime(Utilities.getUtcToLocal(obj.scheduled_date), 'YYYY-MM-DD HH:mm ')
                    )
                )
                :
                fixtureList.filter(
                    obj => (
                        obj.status != 2 && obj.status != 4 && Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(obj.scheduled_date), 'YYYY-MM-DD HH:mm ')
                    )
                )
            )
        List = (isFor == 'comp' || isFor == 'live') ? List.reverse() : List
        let { int_version } = Utilities.getMasterData()
        let fArray = _filter(List, (obj) => {
            return obj.user_team_id && obj.status != 4
        })
        let fArray1 = _filter(List, (obj) => {
            return !obj.user_team_id || obj.user_team_id && obj.status == 4
        })
        return (
            <>
                {
                    !this.state.reloadList && List.length > 0 &&
                    <>
                        {
                            isSportPredictor ?
                                <div className="pick-info-scr-prd">
                                    <div className="pick-info-body">
                                        <div className="txt-sc">
                                            <div>{AL.PT_PREDICT_TXT1}</div>
                                            <div className='score-txt'>+{winGoal}</div>
                                        </div>
                                        <div className="txt-sc">
                                            <div>{AL.PT_PREDICT_TXT2}</div>
                                            <div className='score-txt'>+{winGoalDiff}</div>
                                        </div>
                                        <div className="txt-sc">
                                            <div>{AL.PT_PREDICT_TXT3}</div>
                                            <div className='score-txt'>+{winOnly}</div>
                                        </div>
                                    </div>
                                    <div className="pick-info-footer">
                                        {AL.PT_DRAW_PREDICT_TXT}
                                    </div>
                                </div>
                                :
                                <div className="pick-info-sec pick-info-sec-view">

                                    <div className='positive-pts'>
                                        <div className="val">+{detail.points.correct} {AL.PTS}</div>
                                        <div className="lbl">{AL.CORRECT_TEXT}</div>
                                    </div>
                                    {this.state.selectedTab == 0 &&
                                        <div className='tap-join-text'>{(int_version == "1") ? AL.TAP_TO_JOIN : AL.TAP_TO_JOIN_GAME}</div>
                                    }
                                    <div className='negtive-pts'>
                                        <div className="val">-{detail.points.wrong} {AL.PTS}</div>
                                        <div className="lbl">{AL.INCORRECT_TEXT}</div>
                                    </div>

                                </div>
                        }
                    </>
                }
                <ul>
                    {
                        !this.state.reloadList && List.length > 0 ?
                            <>
                                {
                                    _Map(fArray, (item, idx) => {
                                        let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                                        let game_starts_in = Date.parse(sDate)
                                        item['game_starts_in'] = game_starts_in;
                                        return (
                                            <PTTourQueList
                                                {...this.props}
                                                item={item}
                                                isFor={isFor}
                                                joinTournament={() => this.joinTournament(item)}
                                                optionSelection={this.optionSelection}
                                                timerCallback={() => this.timerCompletionCall(item)}
                                                detail={this.state.detail}
                                                selectScoreMdl={this.selectScoreMdl}
                                                selectedawayScore={this.state.selectedawayScore}
                                                selectedhomeScore={this.state.selectedhomeScore}
                                                activeSeasonId={this.state.scorePredictItem.season_id}
                                                scorePredictFor={this.state.scorePredictFor}
                                            />
                                        )
                                    })
                                }
                                {
                                    _Map(fArray1, (item, idx) => {
                                        let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                                        let game_starts_in = Date.parse(sDate)
                                        item['game_starts_in'] = game_starts_in;
                                        return (
                                            <PTTourQueList
                                                {...this.props}
                                                item={item}
                                                isFor={isFor}
                                                joinTournament={() => this.joinTournament(item)}
                                                optionSelection={this.optionSelection}
                                                timerCallback={() => this.timerCompletionCall(item)}
                                                detail={this.state.detail}
                                                selectScoreMdl={this.selectScoreMdl}
                                                selectedawayScore={this.state.selectedawayScore}
                                                selectedhomeScore={this.state.selectedhomeScore}
                                                activeSeasonId={this.state.scorePredictItem.season_id}
                                                scorePredictFor={this.state.scorePredictFor}
                                            />
                                        )
                                    })
                                }
                            </>

                            // _Map(List, (item, idx) => {
                            //     let sDate = new Date(Utilities.getUtcToLocal(item.scheduled_date))
                            //     let game_starts_in = Date.parse(sDate)
                            //     item['game_starts_in'] = game_starts_in;
                            //     return (
                            //         <PTTourQueList
                            //             {...this.props}
                            //             item={item}
                            //             isFor={isFor}
                            //             joinTournament={()=>this.joinTournament(item)}
                            //             optionSelection={this.optionSelection}
                            //             timerCallback={() => this.timerCompletionCall(item)}
                            //             detail={this.state.detail}
                            //             selectScoreMdl={this.selectScoreMdl}
                            //             selectedawayScore={this.state.selectedawayScore}
                            //             selectedhomeScore={this.state.selectedhomeScore}
                            //             activeSeasonId={this.state.scorePredictItem.season_id}
                            //             scorePredictFor={this.state.scorePredictFor}
                            //         />
                            //     )
                            // })
                            :
                            <NoDataView
                                BG_IMAGE={Images.no_data_bg_image}
                                // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                MESSAGE_1={AL.NO_DATA_AVAILABLE}
                            />
                    }
                    {this.state.detail.status == '3' && detail.tie_breaker_user && parseInt(detail.tie_breaker_user) != 0 && detail.tie_breaker_user != '' && //tie breaker bor for completed matches only
                        <div className='tie-breaker' style={{ width: '100%' }}>
                            <div className='tb-tag'>
                                {AL.TIE_BREAKER}
                            </div>
                            <div className='title-tx'>{tie_breaker_question.question}</div>
                            <div className='flex-tb-div'>
                                <div className='point-counts'><span>{detail.tie_breaker_user || '--'}</span><br />{AL.YOUR_PRE}</div>
                                <div className='mid-line' />
                                <div className='point-counts'><span>{detail.tie_breaker_answer || '--'}</span><br />{AL.CORRECT_ANS}</div>
                            </div>
                        </div>}

                </ul>

            </>
        )
    }
    /**
     * @description method to display contest detail model
     * @param data - contest model data for which contest detail to be shown
     * @param activeTab -  tab to be open on detail, screen
     * @param event -  click event
     */
    ContestDetailShow = (data, activeTab, event) => {
        event.stopPropagation();
        event.preventDefault();
        this.setState({
            showContestDetail: true,
            detailData: data,
            activeTab: activeTab,
        });
    }
    /**
     * @description method to hide contest detail model
     */
    ContestDetailHide = () => {
        this.setState({
            showContestDetail: false,
        });
    }
    onSubmitBtnClick = () => {

    }

    bannerImg = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    renderBannerSection = (banner_images) => {
        let bannImg = this.bannerImg(banner_images)
        var settings = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            autoplay: true,
            autoplaySpeed: 5000,
            centerMode: bannImg.length == 1 ? false : true,
            responsive: [
                {
                    breakpoint: 500,
                    settings: {
                        className: "center",
                        centerPadding: "20px",
                    }

                },
                {
                    breakpoint: 360,
                    settings: {
                        className: "center",
                        centerPadding: "15px",
                    }

                }
            ]
        };
        if (bannImg.length > 0) {
            return <div className="banner-sec">
                <Suspense fallback={<div />} ><ReactSlickSlider settings={settings}>
                    {
                        bannImg.map((item, idx) => {
                            return (
                                <div className={`bann-item ${bannImg.length == 1 ? ' single-ban' : ''}`}>
                                    <div className="bann-inn">
                                        <img src={Utilities.getPickemTour(item)} alt="" />
                                    </div>
                                </div>
                            )
                        })
                    }
                </ReactSlickSlider>
                </Suspense>
            </div>
        }
        else {
            return <></>
        }
    }

    openFixDetail = (item) => {
        this.setState({
            showFixDetail: true,
            activeUserDetail: item
        })
    }

    hideFixDetail = () => {
        this.setState({
            showFixDetail: false,
            activeUserDetail: '',
            showFieldView: false
        })
    }

    showFieldView = (item) => {
        let teamname = item.name.split(" vs ")
        item['home'] = teamname[0]
        item['away'] = teamname[1]
        let param = {
            'lineup_master_contest_id': item.lmc_id,
            "sports_id": AppSelectedSport,
        }
        let apiCall = getLineupWithScore
        apiCall(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                // let lData;
                // lData = this.state.AllLineUPData;
                // lData[item.lmc_id] = responseJson.data;  
                this.setState({
                    AllLineUPData: responseJson.data
                }, () => {
                    this.setState({
                        showFieldView: true,
                        activeFix: item
                    });
                })
            }
        })
    }
    hideFieldView = () => {
        this.setState({
            showFieldView: false,
            activeFix: ''
        })
    }

    tieBreakerChange = (rangeValue) => {
        if(this.state.detail.user_tournament_id <= 0){
        this.setState({
            userTieValue: ''
        })
    }else{
        this.setState({
            userTieValue: rangeValue
        })
    }
    }

    handleChangeComplete = () => {
        let param = {
            "tournament_id": this.state.tournamenId,
            "user_tournament_id": this.state.detail.user_tournament_id,
            "answer": this.state.userTieValue,
        }
        submitPTTieBreaker(param).then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 2000);
                this.callTournamentdeatilApi()
            }
            else {
                // Utilities.showToast(responseJson.global_error != "" ? responseJson.global_error : responseJson.message, 2000);
                Utilities.showToast(CommonLabels.JOIN_THE_TOURNAMENT_TO_PREDICT_ANSWER, 2000);
            }
        })
    }

    prizeDetail = (data) => {
        try {
            return JSON.parse(data)
        }
        catch {
            return data
        }
    }

    showPrizePool = (detail) => {
        let newPrizeDistributionList = this.prizeDetail(detail.prize_detail)
        let prizeItem = newPrizeDistributionList[0]

        return (
            <>
                {
                    (prizeItem.prize_type == 0) ?
                        <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                        :
                        (prizeItem.prize_type == 1 || prizeItem.prize_type == 4) ?
                            <>{Utilities.getMasterData().currency_code}</>
                            :
                            (prizeItem.prize_type == 2) ?
                                <img style={{ position: 'relative', top: '-1px' }} src={Images.IC_COIN} width="10px" height="10px" />
                                :
                                <></>
                } {prizeItem.amount}
            </>
        )
    }


    prizepoolSubmition = (perfectScore) => {
        let realPrize = 0
        _Map(perfectScore, (score, indx) => {
            if ((score.correct == 'jackpot' || this.state.detail.match.length > parseInt(score.correct)) && score.prize_type == 1) {
                realPrize = parseInt(realPrize) + parseInt(score.amount)
            }
        })
        this.setState({
            realPSSum: realPrize
        })


    }
    showWhatsTour = () => {
        this.setState({
            showTourNew: true
        })
    }

    closeTourNew = () => {
        this.setState({
            showTourNew: false
        })
    }

    render() {
        const { detail, selectedTab, fixtureList, isLoading, showContestDetail, activeTab, tie_breaker_question, activeUserDetail, AllLineUPData, showFieldView, activeFix, perfect_Score, userTieValue, realPSSum, showConfirmationPopUp, winGoal, winGoalDiff, winOnly, isSportPredictor, showScoreModal, predictValueArray, selectedawayScore, selectedhomeScore, scorePredictFor, scorePredictItem, showTourNew,showFixDetail } = this.state
        let sDate = new Date(Utilities.getUtcToLocal(detail.start_date))
        let game_starts_in = Date.parse(sDate)
        detail['game_starts_in'] = game_starts_in;
        const HeaderOption = {
            back: true,
            // title: AL.TOURNAMENT,
            hideShadow: true,
            isPrimary: DARK_THEME_ENABLE ? false : true,
            notification: false,
            tourData: detail,
            isFrom : "PTDetail"
        }
        let userJackpotPrize = this.prizeDetail(detail.user_perfect_score_data)
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className="web-container web-container-fixed dfs-tour-container dfs-detail-wrap pickem-dfs-wrap">
                        <Helmet titleTemplate={`${MetaData.template} | %s`}>
                            <title>{MetaData.DFSTourList.title}</title>
                            <meta name="description" content={MetaData.DFSTourList.description} />
                            <meta name="keywords" content={MetaData.DFSTourList.keywords}></meta>
                        </Helmet>
                        <CustomHeader {...this.props} HeaderOption={HeaderOption} />
                        <div className="dfs-dtl-inn-sec">
                            {detail.banner_images && this.bannerImg(detail.banner_images) && this.bannerImg(detail.banner_images).length > 0 &&
                                this.renderBannerSection(detail.banner_images)
                            }
                            {/* <div className="league-info">
                                <span>{detail.league} </span>
                                <span className='schd'>
                                    <MomentDateComponent data={{ date: detail.start_date, format: "D MMM" }} /> -
                                    <MomentDateComponent data={{ date: detail.end_date, format: "D MMM" }} />
                                </span>
                            </div> */}
                            <div className="league-info">
                                <span>{AL.PICKEM} {' '} {AL.TOURNAMENT}</span>
                                <span className='schd what_tour' onClick={() => this.showWhatsTour()}>
                                    {AL.WHATS_TOURNAMT}
                                </span>
                            </div>
                            <div className={`mour-tour-wrap ${realPSSum != 0 ? ' with-perfect-score' : ''}`}>
                                <div className="more-tour-info">
                                    <div className="top-sec">
                                        <div className="img-sec">
                                            {
                                                perfect_Score && perfect_Score.length > 0 ?
                                                    <img src={Images.PERFECT_SCORE} alt="" />
                                                    :
                                                    <img src={Images.TOUR_TROPHY_IMG} alt="" />
                                            }
                                        </div>
                                        {
                                            // detail.status != '3' &&
                                            perfect_Score && perfect_Score.length > 0 ?
                                                <>
                                                    {
                                                        detail.status == 0 ?
                                                            <>
                                                                <div className="txt-sec">
                                                                    <div className="winn-txt">
                                                                        {AL.PERFECT_SCORE_WINS} {' '}
                                                                        {
                                                                            perfect_Score[0].prize_type == 0 ?
                                                                                <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                                                                                :
                                                                                perfect_Score[0].prize_type == 1 ?
                                                                                    Utilities.getMasterData().currency_code
                                                                                    :
                                                                                    perfect_Score[0].prize_type == 2 ?
                                                                                        <img alt='' style={{ marginRight: '2px', marginBottom: '1px' }} src={Images.IC_COIN} width="14px" height="14px" />
                                                                                        :
                                                                                        ''
                                                                        }
                                                                        {perfect_Score[0].amount}
                                                                    </div>
                                                                    <p>
                                                                        {AL.PERFECT_SCORE_TXT}
                                                                    </p>
                                                                </div>
                                                            </>
                                                            :
                                                            <>
                                                                {
                                                                    userJackpotPrize && userJackpotPrize.amount ?
                                                                        <div className="txt-sec font-f">
                                                                            {AL.PERFECT_SCR_TX}
                                                                            {' '}
                                                                            <span className='w-fix'>

                                                                                {
                                                                                    (userJackpotPrize.prize_type == 0) ?
                                                                                        <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                                                                                        :
                                                                                        (userJackpotPrize.prize_type == 1 || userJackpotPrize.prize_type == 4) ?
                                                                                            <>{Utilities.getMasterData().currency_code}</>
                                                                                            :
                                                                                            (userJackpotPrize.prize_type == 2) ?
                                                                                                <img style={{ position: 'relative', top: '-1px' }} src={Images.IC_COIN} width="10px" height="10px" />
                                                                                                :
                                                                                                <></>
                                                                                } {userJackpotPrize.amount}
                                                                            </span>
                                                                            {' '}
                                                                            {AL.PERFECT_SCR_TXS}
                                                                        </div>
                                                                        :
                                                                        <div className="txt-sec font-f">{AL.MISSED_JACKPOT_TEXT}</div>
                                                                }
                                                            </>
                                                    }
                                                </>
                                                :
                                                <div className="txt-sec">
                                                    <div className="winn-txt">{AL.WHAT_IS_PICK_TOUR}</div>
                                                    <p>
                                                        {AL.PICK_TOUR_DESC}
                                                    </p>
                                                </div>
                                        }
                                        {/* {detail.status != '3' &&
                                            perfect_Score && perfect_Score.length > 0 ?
                                                <div className="txt-sec">
                                                    <div className="winn-txt">
                                                        {AL.PERFECT_SCORE_WINS} {' '}
                                                        {
                                                            perfect_Score[0].prize_type == 0 ?
                                                                <i style={{ display: 'inlineBlock' }} className="icon-bonus"></i>
                                                                :
                                                                perfect_Score[0].prize_type == 1 ?
                                                                    Utilities.getMasterData().currency_code
                                                                    :
                                                                    perfect_Score[0].prize_type == 2 ?
                                                                        <img alt='' style={{ marginRight: '2px', marginBottom: '1px' }} src={Images.IC_COIN} width="14px" height="14px" />
                                                                        :
                                                                        ''
                                                        }
                                                        {perfect_Score[0].amount}
                                                    </div>
                                                    <p>
                                                        {AL.PERFECT_SCORE_TXT}
                                                    </p>
                                                </div>
                                                :
                                                detail.status != '3' &&
                                                <div className="txt-sec">
                                                    <div className="winn-txt">{AL.WHAT_IS_PICK_TOUR}</div>
                                                    <p>
                                                        {AL.PICK_TOUR_DESC}
                                                    </p>
                                                </div>
                                        }
                                        {detail.status == '3' &&
                                            detail.win_perfect_score == '1'?
                                            <div className="txt-sec font-f">
                                                {AL.PERFECT_SCR_TX} 
                                                {' '}
                                                <span className='w-fix'>
                                                    {Utilities.getMasterData().currency_code + ' ' + realPSSum}
                                                </span>
                                                {' '} 
                                                {AL.PERFECT_SCR_TXS}
                                            </div>
                                          :
                                          detail.status == '3' &&
                                          <div className="txt-sec font-f">{AL.MISSED_JACKPOT_TEXT}</div>
                                        } */}
                                    </div>
                                    <div className="btm-sec">
                                        <div className='info rank-sec' onClick={(e) => this.ContestDetailShow(detail, '2', e)}>
                                            <div className="graphic-sec">
                                                <i className="icon-standings"></i>
                                            </div>
                                            <div>
                                                <div className="val text-center">
                                                    {detail.game_rank ? (detail.game_rank == 0 ? '-' : detail.game_rank) : '-'}
                                                </div>
                                                <div className="lbl">{AL.RANK}</div>
                                            </div>
                                        </div>
                                        <div className='info' onClick={(e) => this.ContestDetailShow(detail, '0', e)}>
                                            <div className="graphic-sec">
                                                <img src={Images.PRIZE_BADGE_IMG} alt="" />
                                            </div>
                                            <div className="lbl">{AL.PRIZES}</div>
                                        </div>
                                        <div className='info' onClick={(e) => this.ContestDetailShow(detail, '1', e)}>
                                            <div className="graphic-sec">
                                                <img src={Images.RANK_BADGE_IMG} alt="" />
                                            </div>
                                            <div className="lbl">{AL.RULES}</div>
                                        </div>
                                    </div>
                                </div>
                                {
                                    realPSSum > 0 && detail.status != '3' &&
                                    <div className='prize-poll-view-text' onClick={(e) => this.ContestDetailShow(detail, '0', e)}>
                                        {AL.PRIZE_POLL_TEXT}
                                        <span className='mr-1'>{Utilities.getMasterData().currency_code + ' ' + realPSSum} </span> + <span className='ml-1'> {AL.JACKPORT_TEXT}</span>
                                    </div>
                                }
                            </div>
                            <Tab.Container id='my-contest-tabs' activeKey={selectedTab} defaultActiveKey={selectedTab}>
                                <Row className="clearfix">
                                    {detail.status != '3' && <Col className="link-tabs-view link-tab" xs={12}>
                                        <Nav>
                                            <NavItem onClick={() => this.onTabClick(CONTEST_UPCOMING)} eventKey={CONTEST_UPCOMING}>
                                                {AL.UPCOMING}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick(CONTEST_LIVE)} eventKey={CONTEST_LIVE}>
                                                {AL.LIVE}
                                            </NavItem>
                                            <NavItem onClick={() => this.onTabClick(CONTEST_COMPLETED)} eventKey={CONTEST_COMPLETED}>
                                                {AL.COMPLETED}
                                            </NavItem>
                                        </Nav>
                                    </Col>}
                                    <Col className="top-tab-margin" xs={12}>
                                        <Tab.Content animation>
                                            <Tab.Pane eventKey={CONTEST_UPCOMING}>
                                                <div className="fixture-list">
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length > 0 &&
                                                        <>
                                                            {/* {
                                                                isSportPredictor ?
                                                                <div className="pick-info-scr-prd">
                                                                    <div className="pick-info-body">
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT1}</div>
                                                                            <div className='score-txt'>+{winGoal}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT2}</div>
                                                                            <div className='score-txt'>+{winGoalDiff}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT3}</div>
                                                                            <div className='score-txt'>+{winOnly}</div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="pick-info-footer">
                                                                        {AL.PT_DRAW_PREDICT_TXT}
                                                                    </div>
                                                                </div>
                                                                :
                                                                <div className="pick-info-sec pick-info-sec-view">
                                                                    
                                                                        <div className='positive-pts'>
                                                                            <div className="val">+{detail.points.correct} {AL.PTS}</div>
                                                                            <div className="lbl">{AL.CORRECT_TEXT}</div>
                                                                        </div>
                                                                        <div className='tap-join-text'>{AL.TAP_TO_JOIN}</div>
                                                                        <div className='negtive-pts'>
                                                                            <div className="val">-{detail.points.wrong} {AL.PTS}</div>
                                                                            <div className="lbl">{AL.INCORRECT_TEXT}</div>
                                                                        </div>
                                                                
                                                                </div>
                                                            } */}
                                                            {/* <ul> */}
                                                            {this.renderFixtureView(fixtureList, 'upc')}
                                                            {/* </ul> */}
                                                          
                                                            {

                                                                tie_breaker_question && tie_breaker_question.start &&
                                                                !(Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') >= Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ')) &&

                                                                <div className="tie-breaker-sec">
                                                                    <div className={`tie-breaker-block ${detail.user_tournament_id <= 0 ? " tie-breaker-bg " :
                                                                            parseInt(userTieValue) != parseInt(tie_breaker_question.start) ? ' tie-breaker-sel' : (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ') ? ' disabled' : '')}`}>
                                                                        <div className='overlay'></div>
                                                                        <div className="tp-sec">
                                                                            <span className="tag">{AL.TIE_BREAKER}</span>
                                                                            <div className="timer-section cust-timer">
                                                                                {
                                                                                    Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') < Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ') &&
                                                                                    <>
                                                                                   
                                                                                        {
                                                                                            Utilities.showCountDown({ game_starts_in: detail.game_starts_in })
                                                                                                ?
                                                                                                <div className={"countdown-timer-section"}>
                                                                                                    {
                                                                                                        detail.game_starts_in && <CountdownTimer
                                                                                                            timerCallback={this.props.timerCompletionCall}
                                                                                                            deadlineTimeStamp={detail.game_starts_in} />
                                                                                                    }
                                                                                                </div>
                                                                                                :
                                                                                                <MomentDateComponent data={{ date: detail.start_date, format: "D MMM - hh:mm A " }} />
                                                                                        }
                                                                                    </>
                                                                                }
                                                                            </div>
                                                                        </div>
                                                                        <div className="que-txt pick-que">
                                                                            <span className='checkbox'>
                                                                                <i className="icon-tick-circular"></i>
                                                                            </span>
                                                                            <div>{tie_breaker_question.question}</div>

                                                                        </div>
                                                                        <div className={`slider `} >
                                                                        {/* <div className={`slider ${detail.user_tournament_id <= 0 ? " slider-pointer-events" : "" }`} > */}
                                                                            <Slider
                                                                                disabled={true}
                                                                                min={parseInt(tie_breaker_question.start)}
                                                                                max={parseInt(tie_breaker_question.end)}
                                                                                value={userTieValue}
                                                                                onChange={this.tieBreakerChange}
                                                                                handleLabel={userTieValue}
                                                                                tooltip={false}
                                                                                onChangeComplete={this.handleChangeComplete}
                                                                            />
                                                                            <div className="tie-breaker-value">
                                                                                <span>{tie_breaker_question.start}</span>
                                                                                <span>{tie_breaker_question.end}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div className="tie-breaker-info">
                                                                        {AL.TIE_BREAKER_INFO}
                                                                    </div>
                                                                </div>
                                                            }
                                                        </>
                                                    }
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length == 0 &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                        />
                                                    }
                                                </div>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={CONTEST_LIVE}>
                                                <div className="fixture-list">
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length > 0 &&
                                                        <>
                                                            {/* {
                                                                isSportPredictor ?
                                                                <div className="pick-info-scr-prd">
                                                                    <div className="pick-info-body">
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT1}</div>
                                                                            <div className='score-txt'>+{winGoal}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT2}</div>
                                                                            <div className='score-txt'>+{winGoalDiff}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT3}</div>
                                                                            <div className='score-txt'>+{winOnly}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                :
                                                                <div className="pick-info-sec pick-info-sec-view">
                                                                    
                                                                    <div className='positive-pts'>
                                                                        <div className="val">+{detail.points.correct} {AL.PTS}</div>
                                                                        <div className="lbl">{AL.CORRECT_TEXT}</div>
                                                                    </div>
                                                                    //<div className='tap-join-text'>{AL.TAP_TO_JOIN}</div>
                                                                    <div className='negtive-pts'>
                                                                        <div className="val">-{detail.points.wrong} {AL.PTS}</div>
                                                                        <div className="lbl">{AL.INCORRECT_TEXT}</div>
                                                                    </div>
                                                            
                                                                </div>
                                                            } */}
                                                            {/* <ul> */}
                                                            {this.renderFixtureView(fixtureList, 'live')}
                                                            {/* </ul> */}
                                                            {this.state.detail.status == '0' && (Utilities.getFormatedDateTime(Date.now(), 'YYYY-MM-DD HH:mm ') > Utilities.getFormatedDateTime(Utilities.getUtcToLocal(detail.start_date), 'YYYY-MM-DD HH:mm ')) && detail.tie_breaker_user && parseInt(detail.tie_breaker_user) != 0 && detail.tie_breaker_user != '' && //tie breaker bor for completed matches only
                                                                <div className='tie-breaker' style={{ width: '100%' }}>
                                                                    <div className='tb-tag'>
                                                                        {AL.TIE_BREAKER}
                                                                    </div>
                                                                    <div className='title-tx'>{tie_breaker_question.question}</div>
                                                                    <div className='flex-tb-div'>
                                                                        <div className='point-counts'><span>{detail.tie_breaker_user || '--'}</span><br />{AL.YOUR_PRE}</div>
                                                                        <div className='mid-line' />
                                                                        {/* tie_breaker_question && tie_breaker_question.start */}
                                                                        <div className='point-counts'><span>{tie_breaker_question && tie_breaker_question.start <= 0 ? (detail.tie_breaker_answer || '--') : "--" } </span><br />{AL.CORRECT_ANS}</div>
                                                                    </div>
                                                                </div>}

                                                        </>
                                                    }
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length == 0 &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                        />
                                                    }
                                                </div>
                                            </Tab.Pane>
                                            <Tab.Pane eventKey={CONTEST_COMPLETED}>
                                                <div className="fixture-list">
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length > 0 &&
                                                        <>
                                                            {/* {
                                                                isSportPredictor ?
                                                                <div className="pick-info-scr-prd">
                                                                    <div className="pick-info-body">
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT1}</div>
                                                                            <div className='score-txt'>+{winGoal}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT2}</div>
                                                                            <div className='score-txt'>+{winGoalDiff}</div>
                                                                        </div>
                                                                        <div className="txt-sc">
                                                                            <div>{AL.PT_PREDICT_TXT3}</div>
                                                                            <div className='score-txt'>+{winOnly}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                :
                                                                <div className="pick-info-sec pick-info-sec-view">
                                                                    
                                                                    <div className='positive-pts'>
                                                                        <div className="val">+{detail.points.correct} {AL.PTS}</div>
                                                                        <div className="lbl">{AL.CORRECT_TEXT}</div>
                                                                    </div>
                                                                    //<div className='tap-join-text'>{AL.TAP_TO_JOIN}</div>
                                                                    <div className='negtive-pts'>
                                                                        <div className="val">-{detail.points.wrong} {AL.PTS}</div>
                                                                        <div className="lbl">{AL.INCORRECT_TEXT}</div>
                                                                    </div>
                                                            
                                                                </div>
                                                            } */}
                                                            {/* <ul> */}
                                                            {this.renderFixtureView(fixtureList, 'comp')}
                                                            {/* </ul> */}
                                                        </>
                                                    }
                                                    {
                                                        !isLoading && fixtureList && fixtureList.length == 0 &&
                                                        <NoDataView
                                                            BG_IMAGE={Images.no_data_bg_image}
                                                            // CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.BRAND_LOGO_FULL}
                                                            CENTER_IMAGE={DARK_THEME_ENABLE ? Images.DT_BRAND_LOGO_FULL : Images.NO_DATA_VIEW}
                                                            MESSAGE_1={AL.NO_DATA_AVAILABLE}
                                                        />
                                                    }
                                                </div>
                                            </Tab.Pane>
                                        </Tab.Content>
                                    </Col>
                                </Row>
                            </Tab.Container>
                        </div>

                        {
                            showContestDetail &&
                            <PTContestDetailModal
                                {...this.props}
                                show={showContestDetail}
                                hide={this.ContestDetailHide}
                                detailData={detail}
                                activeTab={activeTab}
                                openFixDetail={this.openFixDetail}
                                perfect_Score={perfect_Score}
                                realPSSum={realPSSum}
                                isSportPredictor={isSportPredictor}
                            />
                        }
                        {
                            showFieldView &&
                            <FieldView
                                SelectedLineup={AllLineUPData ? AllLineUPData.lineup : ''}
                                MasterData={AllLineUPData || ''}
                                isFrom={'rank-view'}
                                showTeamCount={true}
                                LobyyData={activeFix}
                                // isFromLBPoints={true}
                                team_name={AllLineUPData ? (AllLineUPData.team_info.team_name || '') : ''}
                                showFieldV={showFieldView}
                                userName={activeUserDetail.user_name}
                                hideFieldV={this.hideFieldView.bind(this)}
                                current_sport={AppSelectedSport}
                                team_count={AllLineUPData ? AllLineUPData.team_count : []}
                            />
                        }
                        {
                            showConfirmationPopUp &&
                            <ConfirmationPopup
                                IsConfirmationPopupShow={showConfirmationPopUp}
                                IsConfirmationPopupHide={this.ConfirmatioPopUpHide}
                                TeamListData={''}//{userTeamListSend}
                                TotalTeam={0} //TotalTeam}
                                FixturedContest={this.state.LobyyData}
                                ConfirmationClickEvent={this.ConfirmEvent}
                                // CreateTeamClickEvent={this.createTeamAndJoin}
                                lobbyDataToPopup={this.state.LobyyData}
                                fromContestListingScreen={true}
                                createdLineUp={''}
                                selectedLineUps={[]}
                                hideExtraSec={true}
                                isPickemTournament={true}
                            // showDownloadApp={this.showDownloadApp}
                            // isStockF={true}
                            // isStockLF={true}
                            />
                        }
                        {
                            showScoreModal &&
                            <PTScoreSelModal
                                list={predictValueArray}
                                selScore={scorePredictFor == 'home' ? (selectedhomeScore || '') : (selectedawayScore || '')}
                                userScoreSelection={this.userScoreSelection}
                                hideScoreMdl={this.hideScoreMdl}
                                selectedawayScore={selectedawayScore}
                                selectedhomeScore={selectedhomeScore}
                                scorePredictFor={scorePredictFor}
                                scorePredictItem={scorePredictItem}
                            />
                        }
                        {
                            showTourNew &&
                            <WhatsTourModal showTourNew={showTourNew} closeTourNew={this.closeTourNew} rules={detail.rules} />
                        }

                        { showFixDetail &&
                        <PTFixtureDetailModal {...this.props}
                        show={showFixDetail}
                        hide={this.hideFixDetail}
                        activeUserDetail={activeUserDetail}
                        showFieldView={this.showFieldView}
                        details={detail}
                        />  
                         }
                    </div>

                )}
            </MyContext.Consumer>
        );
    }
}

export default PTTourDetail;