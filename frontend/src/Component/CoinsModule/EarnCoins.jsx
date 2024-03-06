import React, { Component, Suspense } from 'react';
import { Col, Row, OverlayTrigger, Tooltip } from 'react-bootstrap';
import { Helmet } from "react-helmet";
import Skeleton, { SkeletonTheme } from 'react-loading-skeleton';
import { MyContext } from '../../views/Dashboard';
import { Utilities, _Map, isFooterTab, _isUndefined, _times } from '../../Utilities/Utilities';
import { getEarnCoinsList, getUserBalance, claimDownAppCoin } from '../../WSHelper/WSCallings';
import CustomHeader from '../../components/CustomHeader';
import WSManager from "../../WSHelper/WSManager";
import MD from "../../helper/MetaData";
import Images from '../../components/images';
import * as WSC from "../../WSHelper/WSConstants";
import * as AL from "../../helper/AppLabels";
import { QuizQuesModal, ComingSoonModal, QuizModal, ClamedToday, SpinWheel, QuizPlayedModal, ClaimSuccModal, QuizComingSoonModal, VideoComingSoonModal, DailyCheckInComingSoonModal, SpinWheelComingSoonModal, DownloadAppECModal } from "../../Component/UserEngagement";
import { GameType, DARK_THEME_ENABLE, AllowRedeem, DCBSucc, IS_SPORTS_HUB, SELECTED_GAMET, setValue } from '../../helper/Constants';
import { HowThisWorkModal } from '../../Modals';
import ls from 'local-storage';

var globalHELIST = [];
var showToolTip = true;
var gThis = null;

class EarnCoins extends Component {
    constructor(props) {
        super(props)
        this.state = {
            PRFLD: WSManager.getProfile(),
            AllSports: Utilities.getMasterData().sports_hub,
            HELIST: [],
            ISLOAD: false,
            user_balance: '',
            toolTip: showToolTip,
            blinkAnim: showToolTip,
            showBG: false,
            CSModal: false,
            showQuiz: false,
            showQ2: false,
            CTModal: false,
            SWModal: false,
            QPModal: false,
            QUIZ_LT: '',
            DSBonus: '',
            showClaimSucc: false,
            totalWonAmt: 0,
            QPCSModal: false,
            SWData: '',
            showHtwModal: false,
            DCBCSModal: false,
            SWCSModal: false,
            showBGCol: false,
            DAECModal: false,
            DwnAppCoin: 0,
            isDwnAppClaim: 0,
            appDownloaded: 0,
            FBCoins: 0,
            dailyQuizPopup:!_isUndefined(this.props.location.state) ? this.props.location.state.dailyQuizPopup : false,
            spnWhlS: false,
            coinExpiry: Utilities.getMasterData().coin_expiry_limit,
            isCoinExEnb: Utilities.getMasterData().coin_expiry_limit && parseInt(Utilities.getMasterData().coin_expiry_limit) > 0 ? true : false,
            spnWhlS: false,
            btnClick: false
        }
    }

    componentDidMount() {
        window.addEventListener('scroll', this.onScrollList);
        Utilities.handleAppBackManage('EarnCoins')
        setTimeout(() => {
            this.setState({ toolTip: false });
            setTimeout(() => {
                this.setState({ blinkAnim: false });
            }, 7000);
            showToolTip = false;
        }, 4000);
        if (globalHELIST.length > 0) {
            this.setState({
                HELIST: globalHELIST
            }, () => {
                this.callApiEarnCoinsList(true);
            })
            // this.getUserBal();
        } else {
            this.callApiEarnCoinsList();
        }
    }

    onScrollList = (event) => {
        let scrollOffset = window.pageYOffset;
        if (scrollOffset > 0 && scrollOffset < 221) {
            this.setState({
                showBG: true,
                showBGCol: false
            })
        }
        else if (scrollOffset > 220) {
            this.setState({
                showBG: false,
                showBGCol: true
            })
        }
        else {
            this.setState({
                showBG: false,
                showBGCol: false
            })
        }
    }

    componentWillUnmount() {
        gThis = null;
    }

    callApiEarnCoinsList = (isglobalHELIST) => {
        let param = {}
        this.setState({ ISLOAD: true })
        getEarnCoinsList(param).then((responseJson) => {
            // this.getUserBal();
            this.setState({ ISLOAD: false })
            if (responseJson.response_code === WSC.successCode) {
                this.setState({
                    HELIST: isglobalHELIST ? this.state.HELIST : responseJson.data.earn_coins || [],
                    QUIZ_LT: responseJson.data.quiz_dtl || '',
                    DSBonus: responseJson.data.daily_streak_bonus || '',
                    SWData: responseJson.data.spin_wheel || '',
                    DwnAppCoin: responseJson.data.download_app_coins || 0,
                    isDwnAppClaim: responseJson.data.download_app_claim_status || 0, // 1 means user clamined for coins
                    appDownloaded: responseJson.data.app_downloaded || 0,// 1 if downloded 
                    FBCoins: responseJson.data.feedback_coins || 0,// 1 if downloded 
                }, () => {
                    if (this.state.dailyQuizPopup) {
                        this.showQuizModal(this.state.QUIZ_LT)
                    }
                })
                globalHELIST = (responseJson.data.earn_coins || [])
            }
        })
    }

    getUserBal = () => {
        getUserBalance().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                WSManager.setAllowedBonusPercantage(responseJson.data.allowed_bonus_percantage)
                WSManager.setBalance(responseJson.data.user_balance);
                this.setState({ user_balance: responseJson.data.user_balance });
            }
        })
    }

    btnAction = (value) => {
        if (value.module_key === 'daily_streak_bonus' && CustomHeader.showDailyStreak) {
            CustomHeader.showDailyStreak();
        } else if (value.module_key === 'refer-a-friend') {
            //this.props.history.push({ pathname: "/refer-friend" });
            this.htwModalShow()
        } else if (value.module_key === 'feedback') {
            this.props.history.push({ pathname: "/feedback" });
        } else if (value.module_key === 'prediction') {
            WSManager.setPickedGameType(GameType.Pred);
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
        } else if (value.game_key === GameType.OpenPred) {
            WSManager.setPickedGameType(GameType.OpenPred);
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
        } else if (value.game_key === GameType.OpenPredLead) {
            WSManager.setPickedGameType(GameType.OpenPredLead);
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
        } else if (value.game_key === GameType.Pickem) {
            WSManager.setPickedGameType(GameType.Pickem);
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
        }
        else if (value.module_key === "download_app") {
            if (window.ReactNativeWebView && this.state.isDwnAppClaim == 0 && this.state.appDownloaded === 1) {
                this.showClaimSuccModal(this.state.DwnAppCoin)
                setTimeout(() => {
                    this.callClaimDownAppCoin()
                }, 1000);
            }
            else if (this.state.appDownloaded === 0 || (!window.ReactNativeWebView && this.state.isDwnAppClaim == 0 && this.state.appDownloaded === 1)) {
                this.showDAECSModal()
            }
        }
    }

    callClaimDownAppCoin = () => {
        let param = {}
        claimDownAppCoin(param).then((responseJson) => {
            if (responseJson.response_code === WSC.successCode) {
                Utilities.showToast(responseJson.message, 1000);
                this.setState({
                    isDwnAppClaim: 1
                })
            }
        })
    }

    gotoCoinTransaction = () => {
        this.props.history.push({ pathname: '/transactions', state: { tab: AL.COINS } })
    }
    gotoRewards = () => {
        this.props.history.push({ pathname: '/rewards', state: { tab: AL.COINS } })
    }

    static updateBalance = () => {
        if (gThis) {
            gThis.getUserBal();
        }
    }

    renderListItem = (item, isSport) => {
        let is_allSport = isSport;
        let data = (item[WSManager.getAppLang() || 'en']) || '';
        return (
            <React.Fragment>
                {
                    is_allSport ?
                        <React.Fragment>
                            {
                                // ( item.game_key == GameType.OpenPred || item.game_key == GameType.OpenPredLead || item.game_key == GameType.Pickem) &&
                                item.game_key == GameType.Pickem &&
                                <li key={item.game_key}>
                                    <div>
                                        <div className="img-sec">
                                            {
                                                (item.game_key == GameType.Pickem) ?
                                                    <img src={Images.PICK_EARNCOIN} alt="" />
                                                    :
                                                    <img src={Images.PREDICT_EARNCOIN} alt="" />
                                            }
                                        </div>
                                        <p className="list-t">{item[WSManager.getAppLang() + '_t']}</p>
                                        {/* <p className="list-d">{item[WSManager.getAppLang() + '_d']}</p> */}
                                    </div>
                                    <a href className="list-btn" onClick={() => this.btnAction(item)} >
                                        {/* {(item.game_key == GameType.Pickem || item.game_key == GameType.OpenPred || item.game_key == GameType.OpenPredLead) ? AL.PREDICT : (item.game_key == GameType.Pickem) ? AL.PICK : AL.PLAY_NOW } */}
                                        {item.game_key == GameType.Pickem ? AL.PREDICT : (item.game_key == GameType.Pickem) ? AL.PICK : AL.PLAY_NOW}
                                    </a>
                                </li>
                            }
                        </React.Fragment>
                        :
                        (item.module_key === "daily_streak_bonus" && Utilities.getMasterData().a_spin == 1) ? '' : <li key={item.module_key}>
                            <div>
                                <div className="img-sec">
                                    <img src={DARK_THEME_ENABLE ? Utilities.getS3URL('DT_' + item.image_url) : Utilities.getS3URL(item.image_url)} alt="" />
                                </div>
                                <p className="list-t" >{data.label.toLowerCase()}</p>
                                {/* <p className="list-d">{item.module_key == 'refer-a-friend' ? AL.GET_EXCITING_REWARDS_ON_EVERY_FRIEND_SIGNUP : data.description}</p> */}
                            </div>
                            {
                                item.module_key === "download_app" ?
                                    <a href className={"list-btn" + (this.state.isDwnAppClaim !== 0 ? " disabled" : "")} onClick={() => this.btnAction(item)} >
                                        {
                                            window.ReactNativeWebView &&
                                                this.state.isDwnAppClaim === 0 && this.state.appDownloaded === 1 ?
                                                AL.CLAIM
                                                :
                                                <>
                                                    <img src={Images.IC_COIN} alt="" className="sm-img" /> {this.state.DwnAppCoin}
                                                </>
                                        }
                                    </a>
                                    :
                                    item.module_key === "feedback" ?
                                        <a href className="list-btn" onClick={() => this.btnAction(item)} >
                                            <img src={Images.IC_COIN} alt="" className="sm-img" /> {this.state.FBCoins}
                                        </a>
                                        :
                                        <a href className="list-btn" onClick={() => this.btnAction(item)} >
                                            {data.button_text.toLowerCase()}
                                        </a>
                            }
                        </li>
                }
            </React.Fragment>
        )
    }

    Shimmer = (index) => {
        return (
            <SkeletonTheme color={DARK_THEME_ENABLE ? "#161920" : null} highlightColor={DARK_THEME_ENABLE ? "#0E2739" : null}>
                <div key={index} className="contest-list shimmer-border">
                    <div className="shimmer-container">
                        <div className="shimmer-top-view">
                            <div className="shimmer-line">
                                <Skeleton height={9} width={'80%'} />
                                <Skeleton height={6} width={'100%'} />
                                <Skeleton height={6} width={100} />
                            </div>
                            <div className="shimmer-bottom-view">
                                <div className="shimmer-buttin w-25">
                                    <Skeleton height={30} width={160} />
                                </div>
                            </div>
                        </div>
                        <div className="shimmer-image">
                            <Skeleton width={70} height={70} />
                        </div>
                    </div>
                </div>
            </SkeletonTheme>
        )
    }

    CSModalShow = () => {
        Utilities.gtmEventFire('button_click', {
            button_name: 'Watch Videos'
        })
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                CSModal: true
            })
        }
    }

    CSModalHide = () => {
        this.setState({
            CSModal: false
        })
    }

    showQuizModal = (data) => {
        Utilities.gtmEventFire('button_click', {
            button_name: 'Play Quiz'
        })
        // e.stopPropagation() QUIZ_LT.is_visited QUIZ_LT.quiz_uid
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            if (data.quiz_uid == null || Utilities.getMasterData().a_quiz === "0") {
                this.showQPCSModal()
            }
            else if (data.quiz_uid && data.is_visited == 1) {
                this.showQPModal()
            }
            else {
                this.setState({
                    showQuiz: true
                })
            }
        }
    }

    hideQuizModal = () => {
        this.setState({
            showQuiz: false
        })
    }

    goToSignUp = () => {
        this.props.history.push('/signup');
    }

    showCTModal = (isClaim) => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            if (Utilities.getMasterData().daily_streak_bonus === "0") {
                this.showDCBCSModal()
            }
            else if (isClaim == 1 && !DCBSucc) {
                CustomHeader.showDailyStreak()
            }
            else {
                this.setState({
                    CTModal: true
                })
            }
        }
    }

    CTModalHide = () => {
        this.setState({
            CTModal: false
        })
    }

    showSWModal = (allow_claim) => {
        // let isSpinSucc = spinWheelSucc && spinWheelSucc == true ? true : false
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            if (Utilities.getMasterData().a_spin === "0") {
                this.showSWCSModal()
            }
            else if (allow_claim == 0 && !this.state.spnWhlS) {
                this.setState({
                    btnClick: true
                })
                CustomHeader.showSpinWheel()
            }
            else {
                this.setState({
                    SWModal: true
                })
            }
        }
    }

    SWModalHide = () => {
        this.setState({
            SWModal: false,
            spnWhlS: true
        })
    }

    showQ2Modal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                showQuiz: false,
                showQ2: true
            })
        }
    }

    hideQ2Modal = () => {
        this.getUserBal();
        this.setState({
            showQ2: false,
            QUIZ_LT: {
                ...this.state.QUIZ_LT,
                'is_visited': 1
            }
        })
    }

    showQPModal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                QPModal: true
            })
        }
    }

    hideQPModal = () => {
        this.setState({
            QPModal: false
        })
    }

    showQPCSModal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                QPCSModal: true
            })
        }
    }

    hideQPCSModal = () => {
        this.setState({
            QPCSModal: false
        })
    }

    htwModalHide = () => {
        this.setState({
            showHtwModal: false
        });
    }

    htwModalShow = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                showHtwModal: true
            });
        }
    }

    showClaimSuccModal = (totalWonAmt) => {
        this.setState({
            showQ2: false,
            showClaimSucc: true,
            totalWonAmt: totalWonAmt || 0
        })
    }

    hideClaimSuccModal = () => {
        this.getUserBal();
        this.setState({
            showClaimSucc: false,
            QUIZ_LT: {
                ...this.state.QUIZ_LT,
                'is_visited': 1
            }
        })
    }

    showSWCSModal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                SWCSModal: true
            })
        }
    }

    hideSWCSModal = () => {
        this.setState({
            SWCSModal: false
        })
    }

    showDCBCSModal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                DCBCSModal: true
            })
        }
    }

    hideDCBCSModal = () => {
        this.setState({
            DCBCSModal: false
        })
    }

    showDAECSModal = () => {
        if (!WSManager.loggedIn()) {
            this.goToSignUp()
        }
        else {
            this.setState({
                DAECModal: true
            })
        }
    }

    hideDAECModal = () => {
        this.setState({
            DAECModal: false
        })
    }

    succSpinWheel = () => {
        this.setState({
            spnWhlS: true
        })
    }

    renderIconValue = (gk) => {
        let { AllSports } = this.state;
        let sb = AllSports
        return sb[0]
    }


    selectGameType = (item) => {
        Utilities.gtmEventFire('button_click', {
            button_name: item.en_t
        })
        ls.set('SHActive', false)
        Utilities.handleAppBackManage('game-type')
        let sport = ls.get('selectedSports');
        let allowedSport = item.allowed_sports || '';
        if (item.game_key == GameType.StockFantasy) {
            Utilities.scrollToTop()
            WSManager.setPickedGameType(item.game_key);
            this.props.history.push("/lobby" + Utilities.getGameTypeHash())
        }
        else if (item.game_key == GameType.StockPredict) {
            Utilities.scrollToTop()
            WSManager.setPickedGameType(item.game_key);
            this.props.history.push("/lobby" + Utilities.getGameTypeHash())
        }
        else if (item.game_key == GameType.PickFantasy) {
            let SelSport = ls.get('PFSSport');
            let SportsList = ls.get('PFSportList')
            // if(SelSport && SportsList.includes(SelSport.sports_id)){
            if (SelSport && (SportsList && SportsList.some(SL => SL.sports_id === SelSport.sports_id))) {
                Utilities.scrollToTop()
                if (!SELECTED_GAMET) {
                    setTimeout(() => {
                        CustomHeader.showSHSCM();
                    }, 100);
                }
                WSManager.setPickedGameType(item.game_key);
                WSManager.setPickedGameTypeID(item.sports_hub_id);
                this.props.history.push("/lobby#")
                // this.props.history.push("/lobby#" + Utilities.getPFSelectedSportsForUrl(SelSport.sports_id))
            }
            else {
                // ls.set('PFSSport', SportsList[0]);
                Utilities.scrollToTop()
                if (!SELECTED_GAMET) {
                    setTimeout(() => {
                        CustomHeader.showSHSCM();
                    }, 100);
                }
                WSManager.setPickedGameType(item.game_key);
                WSManager.setPickedGameTypeID(item.sports_hub_id);
                this.props.history.push("/lobby#")
            }
        }
        else if ((allowedSport == '') || (allowedSport.length > 0 && allowedSport.includes(sport))) {
            Utilities.scrollToTop()
            if (!SELECTED_GAMET) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
            }
            WSManager.setPickedGameType(item.game_key);
            WSManager.setPickedGameTypeID(item.sports_hub_id);
            if (item.game_key == GameType.PickemTournament) {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
            }
            else {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
            }
        }
        else {
            let FSport = allowedSport[0];
            ls.set('selectedSports', FSport);
            setValue.setAppSelectedSport(FSport);
            this.setState({ ACSPORTTAB: FSport });
            Utilities.scrollToTop()
            if (!SELECTED_GAMET) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
            }

            WSManager.setPickedGameType(item.game_key);
            WSManager.setPickedGameTypeID(item.sports_hub_id);
            if (item.game_key == GameType.PickemTournament) {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
            }
            else {
                this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
            }
        }
    }


    render() {
        const { PRFLD, HELIST, ISLOAD, user_balance, toolTip, blinswBG, CSModal, showQuiz, showQ2, CTModal, SWModal, QPModal,
            DSBonus, QUIZ_LT, showClaimSucc, totalWonAmt, showBG, blinkAnim, AllSports, QPCSModal, SWData, showHtwModal,
            DCBCSModal, SWCSModal, showBGCol, DAECModal, spnWhlS, coinExpiry, isCoinExEnb, btnClick } = this.state;
        let userCoinBalnc = (user_balance.point_balance || 0);
        gThis = this;

        let HeaderOption = {
            title: AL.HOW_TO_EARN,
            notification: true,
            hideShadow: true,
            filter: false,
            back: true,
            isPrimary: DARK_THEME_ENABLE ? false : true
        }

        let NumLength = Utilities.kFormatter(userCoinBalnc).length;
        let spImg = Utilities.getMasterData().hub_icon;
        let sports_hub = Utilities.getMasterData().sports_hub
        return (
            <MyContext.Consumer>
                {(context) => (
                    <div className={"web-container how-earn-coins how-earn-coins-new " + ((isFooterTab('earn-coins')) ? 'mh-100-per p-0' : '') + (showBG ? ' with-bg' : showBGCol ? ' with-pri-bg' : '')}>
                        {
                            (!isFooterTab('earn-coins')) &&
                            <CustomHeader
                                {...this.props}
                                HeaderOption={HeaderOption}
                                succSpinWheel={this.succSpinWheel}
                                hideSpinWheel={() => this.setState({ btnClick: false })} />
                        }
                        <Helmet titleTemplate={`${MD.template} | %s`}>
                            <title>{MD.ERNC.title}</title>
                            <meta name="description" content={MD.ERNC.description} />
                            <meta name="keywords" content={MD.ERNC.keywords}></meta>
                        </Helmet>
                        {
                            isCoinExEnb &&
                            <OverlayTrigger rootClose trigger={['click']} placement="bottom" overlay={
                                <Tooltip id="tooltip" className="tooltip-featured">
                                    <strong>{AL.COIN_EXPIRY_MSG1} {coinExpiry} {AL.COIN_EXPIRY_MSG2}</strong>
                                </Tooltip>
                            }>
                                <a href className="earn-coin-info-sec">
                                    <i className="icon-info"></i>
                                </a>
                            </OverlayTrigger>
                        }
                        <div className="top-profile">
                            <div className="top-profile-inn">
                                <div className="profile-v">
                                    {/* <img className="usr-img" src={PRFLD.image ? Utilities.getThumbURL(PRFLD.image) : Images.DEFAULT_AVATAR} alt="" />
                                    <div className="v-username" style={{paddingTop: 15}}>{PRFLD.user_name}</div> */}
                                </div>
                                {
                                    user_balance != '' && <div className="coins-v">
                                        <div onClick={this.gotoCoinTransaction} className={"c-balance " + (blinkAnim ? 'xshow-tooltip' : '') + (NumLength > 4 ? ' c-bal-sm' : '')}>
                                            <img className="coin-img" src={Images.IC_COIN} alt="" /><span>{Utilities.numberWithCommas(Utilities.kFormatter(userCoinBalnc))}</span>
                                        </div>
                                        {userCoinBalnc > 0 && AllowRedeem && <span onClick={this.gotoRewards} className="redeem">{AL.REDEEM}</span>}
                                    </div>
                                }
                            </div>
                        </div>
                        <div className="benf-sec">
                            <div className="daily-benfit-sec">
                                <div className="sec-heading">{AL.DAILY_BENEFITS}</div>
                                <Row>
                                    <Col xs={6} className="dbb-outer">
                                        <div className="daily-benf-box" onClick={() => this.showCTModal(DSBonus.allow_claim)}>
                                            <img src={Images.IC_COIN} alt="" />
                                            <div className="benf-title">{AL.DAILY_CHECK_INS}</div>
                                            <a href className={DSBonus.allow_claim == 1 && !DCBSucc && Utilities.getMasterData().daily_streak_bonus !== "0" ? "" : "disabled"}><img src={Images.IC_COIN} alt="" /> +{DSBonus.current_day_coins || 0}</a>
                                            {
                                                DSBonus.allow_claim == 1 &&
                                                <div className="xtra-info">{AL.CLAIM_TOMORROW}</div>
                                            }
                                        </div>
                                    </Col>
                                    <Col xs={6} className="dbb-outer">
                                        <div className="daily-benf-box" onClick={() => (!btnClick && this.showSWModal(SWData.claimed))}>
                                            <img src={Images.SPIN_EC} alt="" />
                                            <div className="benf-title">{AL.SPIN_EARN}</div>
                                            <a href className={SWData.claimed == 0 && !spnWhlS && Utilities.getMasterData().a_spin !== "0" ? "" : "disabled"}>
                                                <img src={Images.IC_COIN} alt="" /> +{SWData.max_coins || 0}
                                            </a>
                                            <div className="xtra-info"></div>
                                        </div>
                                    </Col>
                                    <Col xs={6} className="dbb-outer">
                                        <div className="daily-benf-box" onClick={() => this.CSModalShow()}>
                                            <img src={Images.VIDEO_EC} alt="" />
                                            <div className="benf-title">{AL.WATCH_VIDEOS}</div>
                                            <a href className="disabled"><img src={Images.IC_COIN} alt="" /> +10</a>
                                            <div className="xtra-info"></div>
                                        </div>
                                    </Col>
                                    <Col xs={6} className="dbb-outer">
                                        <div className="daily-benf-box" onClick={() => this.showQuizModal(QUIZ_LT)} >  {/*  onClick={()=>this.showClaimSuccModal()} >*/}
                                            <img src={Images.QUIZ_EC} alt="" />
                                            <div className="benf-title">{AL.PLAY_QUIZ}</div>
                                            <a href onClick={() => { QUIZ_LT.is_visited == 0 && this.showQuizModal(QUIZ_LT) }} className={QUIZ_LT.quiz_uid && QUIZ_LT.is_visited == 0 && Utilities.getMasterData().a_quiz !== "0" ? "" : "disabled"}><img src={Images.IC_COIN} alt="" /> +{QUIZ_LT.qq_coins || 0}</a>
                                            <div className="xtra-info"></div>
                                        </div>
                                    </Col>
                                </Row>
                            </div>
                            <div className="more-benfit-sec">
                                <div className="sec-heading">{AL.MORE_REWARDS}</div>
                                <ul className="list-type">
                                    {
                                        _Map(HELIST, (item, idx) => {
                                            return this.renderListItem(item)
                                        })
                                    }
                                    {
                                        HELIST.length === 0 && QUIZ_LT == '' && DSBonus == '' && ISLOAD &&
                                        [1, 1, 1, 1, 1, 1].map((item, index) => {
                                            return this.Shimmer(index)
                                        })
                                    }
                                    {
                                        _Map(AllSports, (item, idx) => {
                                            return this.renderListItem(item, true)
                                        })
                                    }
                                </ul>
                            </div>
                            {
                                (IS_SPORTS_HUB && WSManager.loggedIn() && AllSports.length != 2) &&
                                <div className={"sports-hub-footer-tabs EarnCoin-SHFtab"}>
                                    <div className="dot-list left">{
                                        _times(6, (itm) => {
                                            return (
                                                <span key={itm} />
                                            )
                                        })
                                    }
                                    </div>
                                    {
                                        (AllSports && AllSports.length == 2) ?
                                            <div onClick={() => this.selectGameType(this.renderIconValue())}
                                                className="isCoin coin-shine cursor-pointer single-gt-img">
                                                <div className="shadow-v" />
                                                <span className="fcoin">
                                                    <img src={Images[this.renderIconValue().game_key]} alt="" className='single-sport-icon single-sport-icon ani' />

                                                </span>
                                            </div>
                                            :
                                            <div onClick={() => this.props.history.push('/sports-hub')} className="isCoin coin-shine cursor-pointer">
                                                <div className="shadow-v" />
                                                <span className="fcoin">
                                                    <img src={spImg ? Utilities.getSettingURL(spImg) : Images.DT_SPORTS_HUB} alt="" />
                                                    <>
                                                        <div className="spark1">✦</div>
                                                        <div className="spark2">✦</div>
                                                        <div className="spark3">✦</div>
                                                    </>
                                                </span>
                                            </div>
                                    }
                                    <div className="dot-list right">{
                                        _times(6, (itm) => {
                                            return (
                                                <span key={itm} />
                                            )
                                        })
                                    }
                                    </div>
                                </div>
                            }
                        </div>
                        {
                            showQuiz &&
                            <QuizModal
                                isShow={showQuiz}
                                isHide={this.hideQuizModal}
                                Action={this.showQ2Modal}
                            />
                        }
                        {
                            showQ2 &&
                            <QuizQuesModal
                                isShow={showQ2}
                                isHide={this.hideQ2Modal}
                                QData={QUIZ_LT}
                                showClaimSuccModal={this.showClaimSuccModal}
                            />
                        }
                        {
                            CSModal &&
                            <VideoComingSoonModal
                                isShow={CSModal}
                                isHide={this.CSModalHide}
                            />
                        }
                        {
                            CTModal &&
                            <ClamedToday
                                isShow={CTModal}
                                isHide={this.CTModalHide}
                            />
                        }
                        {
                            SWModal &&
                            <SpinWheel
                                isShow={SWModal}
                                isHide={this.SWModalHide}
                            />
                        }
                        {
                            QPModal &&
                            <QuizPlayedModal
                                isShow={QPModal}
                                isHide={this.hideQPModal}
                            />
                        }
                        {
                            showClaimSucc &&
                            <ClaimSuccModal
                                isShow={showClaimSucc}
                                isHide={this.hideClaimSuccModal}
                                totalWonAmt={totalWonAmt}
                                userCoinBalnc={userCoinBalnc || 0}
                            />
                        }
                        {
                            QPCSModal &&
                            <QuizComingSoonModal
                                isShow={QPCSModal}
                                isHide={this.hideQPCSModal}
                            />
                        }
                        {
                            SWCSModal &&
                            <SpinWheelComingSoonModal
                                isShow={SWCSModal}
                                isHide={this.hideSWCSModal}
                            />
                        }
                        {
                            DCBCSModal &&
                            <DailyCheckInComingSoonModal
                                isShow={DCBCSModal}
                                isHide={this.hideDCBCSModal}
                            />
                        }
                        {
                            showHtwModal &&
                            <Suspense fallback={<div />} >
                                <HowThisWorkModal
                                    {...this.props}
                                    isFromRefer={false}
                                    mShow={this.htwModalShow}
                                    mHide={this.htwModalHide}
                                />
                            </Suspense>
                        }
                        {
                            DAECModal &&
                            <DownloadAppECModal
                                isShow={DAECModal}
                                isHide={this.hideDAECModal}
                            />
                        }
                    </div>
                )
                }
            </MyContext.Consumer>
        )
    }
}

export default EarnCoins;
