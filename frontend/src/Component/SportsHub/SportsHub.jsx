import React, { PureComponent, lazy, Suspense } from 'react';
import { MyContext } from '../../views/Dashboard';
import { Utilities, _Map, _times, _filter, _isEmpty, parseURLDate, IsGameTypeEnabled, getSelectedSports, setPickedGameType } from '../../Utilities/Utilities';
import { setValue, AppSelectedSport, SELECTED_GAMET, GameType, DARK_THEME_ENABLE, ReferralData } from '../../helper/Constants';
import Helmet from 'react-helmet';
import MetaData from '../../helper/MetaData';
import CustomHeader from '../../components/CustomHeader';
import WSManager from '../../WSHelper/WSManager';
import ls from 'local-storage';
import * as AL from "../../helper/AppLabels";
import { CommonLabels } from "../../helper/AppLabels";
import Images from '../../components/images';
import { Sports } from "../../JsonFiles";
import { getReferralData, updateDeviceToken, getUserXPCard } from '../../WSHelper/WSCallings';
import * as WSC from "../../WSHelper/WSConstants";
import * as Constants from "../../helper/Constants";
import { Modal, Button } from 'react-bootstrap';
import { isEmpty } from 'lodash';
const ReactSlickSlider = lazy(() => import('../CustomComponent/ReactSlickSlider'));
const Filter = lazy(() => import('../../components/filter'));

const appLang = WSManager.getAppLang();
class SportsHub extends PureComponent {
    constructor(props) {
        super(props)
        this.state = {
            SLIST: [],
            GLIST: [],
            HGLIST: [],
            FilterGLIST: [],
            FilterHGLIST: [],
            ACSPORTTAB: 0,
            filterArray: [],
            SelectedSport: '',
            showHubFitlers: false,
            referalData: '',
            canRedirect: true,
            HOS: {
                title: '',
                hideShadow: true,
                isPrimary: true,
                filter: true,
                loginOpt: true
            },
            REALGAMELIST: [],
            COINGAMELIST: [],
            FSPBanner: [],
            AdvSPBanner: [],
            sh_list: Utilities.getMasterData().sports_hub,
            featuredLeague: Utilities.getMasterData().featured_l_list,
            infoSportsDetail: false,
            nameConfirmation: '',
            dataForInfoDetail: '',
            userXPDetail: '',
            // footerTabs: Constants.DASHBOARD_FOOTER.tabs,
            footerTabs: [
                { tab_key: Utilities.getMasterData().allow_social == 1 ? 'feed' : 'lobby' },
                { tab_key: Utilities.getMasterData().a_coin == 1 ? 'redeem' : 'my-contests' },
                { tab_key: "earn-coins" },//Utilities.getMasterData().a_coin == '1'
                { tab_key: ((IsGameTypeEnabled(GameType.DFS) && Utilities.getMasterData().a_dfst == 1) || Utilities.getMasterData().a_pickem_tournament == 1) ? "leaderboard" : (Utilities.getMasterData().leaderboard && Utilities.getMasterData().leaderboard.length > 0 ? "leaderboard" : "my-profile") },
                { tab_key: "more" }
            ],
            sliderData: [
                {
                    status: 1
                },
                {
                    status: 2
                },
                {
                    status: 3
                },
                {
                    status: 4
                },
                //   {
                //     status: 5
                //   },
                //   {
                //     status: 6
                //   },

            ]
        }
        this.next = this.next.bind(this);
        this.previous = this.previous.bind(this);
    }

    next() {
        this.slider.slickNext();
    }
    previous() {
        this.slider.slickPrev();
    }
    BeforeChange=(oldIndex, newIndex)=>{
        // if(oldIndex == 18 && newIndex == 19){
        //     this.props.getMyLobbyFixturesList()
        // }
    }

    componentWillUnmount() {
        this.enableDisableBack(false);
    }

    enableDisableBack(flag) {
        let data = {
            action: 'back',
            type: flag,
            targetFunc: 'back'
        }
        this.sendMessageToApp(data);
        setTimeout(() => {
            let push_data = {
                action: 'push',
                targetFunc: 'push',
                type: 'receive',
            }
            this.sendMessageToApp(push_data)
        }, 300);
    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
        // this.handelNativeGoogleLogin = this.handelNativeGoogleLogin.bind(this); 
        //bind function once
    }

    componentDidMount() {
        Utilities.setScreenName('SHS');
        Utilities.handleAppBackManage('sports-hub')
        Utilities.handelNativeGoogleLogin(this)
        if (Utilities.getMasterData().a_xp_point == 1 && WSManager.loggedIn()) {
            this.callUserXPDetail();
        }
        this.setData()
        // if (!_isEmpty(ReferralData)) {
        //     this.setState({
        //         referalData: ReferralData.referral_data || "",
        //     })
        // } else {
        //     // if(Utilities.getMasterData().a_hub_banner == 1){
        //     //     this.getReferralData()
        //     // }
        // }
    }
    callUserXPDetail() {
        getUserXPCard().then((responseJson) => {
            if (responseJson && responseJson.response_code == WSC.successCode) {
                this.setState({
                    userXPDetail: responseJson.data.user_xp_card
                })
            }
        })
    }

    blockMultiRedirection() {
        ls.set('canRedirect', false)
        setTimeout(() => {

            ls.set('canRedirect', true)
        }, 1000 * 5);
    }
    updateDeviceToken = () => {
        let param = {
            "device_type": WSC.deviceTypeAndroid,
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if (WSManager.loggedIn()) {
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    canRedirect() {
        setTimeout(() => {
            this.setState({ canRedirect: true })
        }, 3000);
    }
    componentWillUnmount() {
        this.enableDisableBack(false);
    }

    enableDisableBack(flag) {
        let data = {
            action: 'back',
            type: flag,
            targetFunc: 'back'
        }
        this.sendMessageToApp(data);
        setTimeout(() => {
            let push_data = {
                action: 'push',
                targetFunc: 'push',
                type: 'receive',
            }
            this.sendMessageToApp(push_data)
        }, 300);
    }

    sendMessageToApp(action) {
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(action));
        }
    }

    blockMultiRedirection() {
        ls.set('canRedirect', false)
        setTimeout(() => {

            ls.set('canRedirect', true)
        }, 1000 * 5);
    }

    // getReferralData() {
    //     getReferralData().then((responseJson) => {
    //         if (responseJson.response_code == WSC.successCode) {
    //             if (responseJson.data) {
    //                 setValue.setReferralData(responseJson.data)
    //                 this.setState({
    //                     referalData: responseJson.data.referral_data || "",
    //                 })
    //             }
    //         }
    //     })
    // }

    updateDeviceToken = () => {
        let param = {
            "device_type": Utilities.getDeviceType(),
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if (WSManager.loggedIn()) {
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    setData = () => {
        const sports_id = Utilities.getUrlSports();
        const fantasy_list = Utilities.getMasterData().fantasy_list;
        const sh_list = Utilities.getMasterData().sports_hub;
        const sp_HubBanner = Utilities.getMasterData().sp_hub_banner;
        const Featured_bnr = _filter(sp_HubBanner, (obj) => {
            return obj.banner_type_id == 7; // top banner
        })
        const Adv_bnr = _filter(sp_HubBanner, (obj) => {
            return obj.banner_type_id == 8; // bottom banner
        })
        this.setState({
            SLIST: fantasy_list || [],
            GLIST: sh_list || [],
            filterArray: fantasy_list || [],
            FSPBanner: Featured_bnr || [],
            AdvSPBanner: Adv_bnr || []
        }, () => {
            const FHGLIST = _filter(sh_list, (obj) => {
                return obj.is_featured == 1;
            })
            const FGLIST = _filter(sh_list, (obj) => {
                return obj.is_featured == 0;
            })
            const RGLIST = _filter(sh_list, (obj) => {
                return obj.game_type == 1;
            })
            const CGLIST = _filter(sh_list, (obj) => {
                return obj.game_type == 2;
            })
            this.setState({
                HGLIST: FHGLIST,
                GLIST: FGLIST,
                FilterHGLIST: FHGLIST,
                FilterGLIST: FGLIST,
                REALGAMELIST: RGLIST,
                COINGAMELIST: CGLIST // game_type will be 1 for real money games, will be 2 for coin games
            })
            setValue.setAppSelectedSport(sports_id);
            this.setState({ ACSPORTTAB: sports_id })
            if (AppSelectedSport == null) {
                this.checkSportID();
            }
            this.checkOldUrl()
        })
    }

    checkSportID = () => {
        let interval = setInterval(() => {
            if (AppSelectedSport != null) {
                clearInterval(interval)
                this.setState({ ACSPORTTAB: AppSelectedSport })
            }
        }, 100)
    }

    checkOldUrl() {
        let url = window.location.href;
        if (!url.includes(Utilities.getSelectedSportsForUrl())) {
            window.history.replaceState("", "", window.location.pathname + "#" + Utilities.getSelectedSportsForUrl());
        }
    }

    onTabClick = (item) => {
        ls.set('selectedSports', item.sports_id);
        setValue.setAppSelectedSport(item.sports_id);
        this.setState({ ACSPORTTAB: item.sports_id });
        this.props.history.replace(window.location.pathname + "#" + Utilities.getSelectedSportsForUrl())
    }

    selectGameType = (item) => {
        Utilities.gtmEventFire('button_click', {
            button_name: item.en_t
        })
        ls.set('SHActive', false)
        Utilities.handleAppBackManage('game-type')
        let sport = ls.get('selectedSports');
        let allowedSport = item.allowed_sports || '';

        setValue.setSelectedGameType(item.game_key);
        
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
        else if(item.game_key == GameType.OpinionTradeFantasy) {
            Utilities.scrollToTop()
            setPickedGameType(item.game_key)
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl() + Utilities.getGameTypeHash())
        }
        else if(item.game_key == GameType.allow_dfs && item.is_desktop == '1') {
            setPickedGameType(item.game_key)
            this.props.history.push("/dfs/lobby/" + getSelectedSports())
        }
        // Desktop View: End
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
            if (item.game_key == GameType.PropsFantasy) {
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

    /** 
   @description show lobby filters 
   */
    showFilter = () => {
        this.setState({ showHubFitlers: true })
    }
    /** 
   @description hide lobby filters 
   */
    hideFilter = () => {
        this.setState({ showHubFitlers: false })
    }

    filterBySport = (filterBy) => {
        this.setState({
            showHubFitlers: false,
            SelectedSport: filterBy,
            refreshList: false
        }, () => {
            this.setState({
                refreshList: true
            })
            if (filterBy != '') {
                let spID = filterBy.sports_id
                let HGLIST = _filter(this.state.HGLIST, (obj) => {
                    return obj.allowed_sports && obj.allowed_sports.includes(spID);
                })
                let GLIST = _filter(this.state.GLIST, (itm) => {
                    return itm.allowed_sports && itm.allowed_sports.includes(spID);
                })
                let RGLIST = _filter(this.state.REALGAMELIST, (obj) => {
                    return obj.allowed_sports && obj.allowed_sports.includes(spID);
                })
                let CGLIST = _filter(this.state.COINGAMELIST, (obj) => {
                    return obj.allowed_sports && obj.allowed_sports.includes(spID);
                })
                this.setState({
                    FilterHGLIST: HGLIST,
                    FilterGLIST: GLIST,
                    REALGAMELIST: RGLIST,
                    COINGAMELIST: CGLIST
                })
                ls.set('selectedSports', spID);
                setValue.setAppSelectedSport(spID);
                this.setState({ ACSPORTTAB: spID });
                this.props.history.replace(window.location.pathname + "#" + Utilities.getSelectedSportsForUrl())
            }
            else {
                this.setData()
            }
        })
    }

    renderBigcard = (item, idx) => {
        let renderImg = item.image ? Utilities.getSettingURL(item.image) : Images.DFS_BIG;
        let avaSports = this.state.SelectedSport == '' ? item.allowed_sports : '';
        return (
            <li className={"card-v big-card" + (this.state.HGLIST && this.state.HGLIST.length == 1 ? ' big-card-single' : '')} key={idx + item.game_key} onClick={() => this.selectGameType(item)} >
                <img src={renderImg} alt="" />
                <div className="game-v">
                    <p className="game-v-title">{item[appLang + '_t']}</p>
                    <p className="game-v-detail">{item[appLang + '_d']}</p>
                    <span className="p-now">{AL.PLAY}</span>
                    <div className="ava-sport-sec">
                        {
                            avaSports && avaSports.length > 0 &&
                            <>
                                {
                                    _Map(avaSports, (obj, idx) => {
                                        var sportsId = '';
                                        if (obj in Sports.url) {
                                            sportsId = Sports.url[obj + (appLang || '')];
                                        }
                                        if (idx < 3) {
                                            return (
                                                sportsId != '' &&
                                                <span className="sport-text" onClick={(e) => this.selectModuleSport(e, item, obj)} key={idx + sportsId}>{sportsId}</span>
                                            )
                                        } else {
                                            return (
                                                <>
                                                    {
                                                        idx == 3 &&
                                                        <span key={idx + sportsId} className="sport-text">+{avaSports.length - 3}</span>
                                                    }
                                                </>
                                            )
                                        }
                                    })
                                }
                            </>
                        }
                    </div>
                </div>
            </li>
        )
    }

    selectModuleSport = (e, mode, sport) => {
        e.stopPropagation()
        if (sport != '') {
           
            ls.set('selectedSports', sport);
            setValue.setAppSelectedSport(sport);
            this.setState({ ACSPORTTAB: sport });
            this.props.history.replace(window.location.pathname + "#" + Utilities.getSelectedSportsForUrl())
            Utilities.scrollToTop()
            if (!SELECTED_GAMET) {
                setTimeout(() => {
                    CustomHeader.showSHSCM();
                }, 100);
            }
            WSManager.setPickedGameType(mode.game_key);
            this.props.history.push("/lobby#" + Utilities.getSelectedSportsForUrl())
        }
    }

    goToScreen = (pathname, mainurl, isInAppLink, name = null) => {
        
        if (name) {
            Utilities.gtmEventFire('button_click', {
                button_name: name
            })
        }
        if (WSManager.loggedIn()) {
            if (isInAppLink) {
                if (mainurl.includes('http')) {
                    // window.open(mainurl)
                    if (window.ReactNativeWebView) {
                        setTimeout(() => {
                            let data = {
                                action: "external_link",
                                type: 'external_link',
                                targetFunc: "external_link",
                                url: mainurl,
                            };
                            this.sendMessageToApp(data)
                        }, 100);
                    } else {
                        window.open(mainurl, "_blank")
                    }
                }
            }
            else {
                if (mainurl.includes('#prediction')) {
                    WSManager.setPickedGameType(Constants.GameType.Pred)
                }
                else if (mainurl.includes('#stock-fantasy')) {
                    WSManager.setPickedGameType(Constants.GameType.StockFantasy)
                }
                else if (mainurl.includes('#stock-fantasy-equity')) {
                    WSManager.setPickedGameType(Constants.GameType.StockFantasyEquity)
                }
                else if (mainurl.includes('#stock-prediction')) {
                    WSManager.setPickedGameType(Constants.GameType.StockPredict)
                }
                else if (mainurl.includes('#open-predictor')) {
                    WSManager.setPickedGameType(Constants.GameType.OpenPred)
                }
                else if (mainurl.includes('#freeToPlay')) {
                    WSManager.setPickedGameType(Constants.GameType.Free2Play)
                }
                else if (mainurl.includes('#open-predictor-leaderboard')) {
                    WSManager.setPickedGameType(Constants.GameType.OpenPredLead)
                }
                else if (mainurl.includes('#multigame')) {
                    WSManager.setPickedGameType(Constants.GameType.MultiGame)
                }
                else if (mainurl.includes('#pickem')) {
                    WSManager.setPickedGameType(Constants.GameType.Pickem)
                }
                else if (mainurl.includes('#tournament')) {
                    WSManager.setPickedGameType(Constants.GameType.Tournament)
                }
                else if (mainurl.includes('#pick-fantasy')) {
                    WSManager.setPickedGameType(Constants.GameType.PickFantasy)
                }
                else if (mainurl.includes('#pickem-tournamet')) {
                    WSManager.setPickedGameType(Constants.GameType.PickemTournament)
                }
                else if (mainurl.includes('#props')) {
                    WSManager.setPickedGameType(Constants.GameType.PropsFantasy)
                }
                else if (mainurl.includes('/dfs')) {
                    WSManager.setPickedGameType(Constants.GameType.DFS)
                }
                else if (mainurl.includes('#opinion-trade')) {
                    WSManager.setPickedGameType(Constants.GameType.OpinionTradeFantasy)
                }
                else {
                    WSManager.setPickedGameType(Constants.GameType.DFS)
                }
                this.props.history.push(pathname);
            }
        }
        else {
            this.props.history.push("/signup")
        }
    }

    // getReferralValue = () => {
    //     var isCoinAllowed = Utilities.getMasterData().a_coin == "1";
    //     var coinsAmt = parseInt(this.state.referalData.coins || '0');
    //     var realAmt = parseInt(this.state.referalData.real_amount || '0');
    //     var bonusAmt = parseInt(this.state.referalData.bonus_amount || '0');

    //     let text = realAmt > 0 ?
    //         (Utilities.getMasterData().currency_code + ' ' + realAmt + ' ' + AL.REAL_CASH) :
    //         bonusAmt > 0 ?
    //             Utilities.getMasterData().currency_code + ' ' + bonusAmt + ' ' + AL.BONUS_CASH :
    //             (isCoinAllowed && coinsAmt > 0) ?
    //                 coinsAmt + ' ' + AL.COINS : ''
    //     return text
    // }

    // renderBannerCard = () => {
    //     if (Utilities.getMasterData().a_hub_banner != 1) {
    //         return ''
    //     }
    //     let bannerImg = Utilities.getMasterData().hub_banner;
    //     let amountRef = this.getReferralValue()
    //     return (
    //         bannerImg ?
    //             <li onClick={() => this.goToScreen('/refer-friend')} className="pickem-prediction-outer-card is-card banner-card card-v">
    //                 <img className="img-dfs" src={Utilities.getSettingURL(bannerImg)} alt='' />
    //             </li>
    //             : amountRef ?
    //             (
    //             <li onClick={() => this.goToScreen('/refer-friend')} className="pickem-prediction-outer-card is-card banner-card card-v">
    //                 <div className="dfs-card dfs-card-new" >
    //                     {/* <img className="img-dfs-shape" src={Images.PICKEM_SHAPE_IMG} alt='' /> */}
    //                     <div className="dfs-c-new">
    //                         <div className="dfs-c-inner dfs-c-inner-left">
    //                             <p>
    //                                 {AL.REFER_A_FRIEND_AND_GET}
    //                                 {/* {Utilities.getMasterData().currency_code} */}
    //                                 {amountRef} {AL.REAL_CASE_ON_YOUR_FRIEND_SIGN_UP}
    //                                 {/* <a href className="button">{AL.REFER_NOW}!</a> */}
    //                             </p>                            
    //                         </div>
    //                         <div className="dfs-c-inner dfs-c-inner-right">
    //                             <img className="img-dfs" src={Images.REFER_BANNER_IMG} alt='' />
    //                         </div>
    //                     </div>
    //                 </div>
    //             </li>
    //         ) : ''
    //     )
    // }

    renderCard = (item, idx) => {
        var sportImg = '';
        if (DARK_THEME_ENABLE) {
            sportImg = item.game_key === GameType.MultiGame ? Images.DT_MULTI_GAME_IMG : item.game_key === GameType.Free2Play ? Images.DT_FTP_IMG : item.game_key === GameType.Pred ? Images.DT_PRED_IMG : item.game_key === GameType.Pickem ? Images.DT_PICKEM_TOUR_IMG : item.game_key === GameType.OpenPred ? Images.DT_OPEN_PRED_PP : item.game_key === GameType.OpenPredLead ? Images.DT_OPEN_PRED_LEAD : Images.DT_DFS_SIDE_IMG;
        }
        else {
            sportImg = item.game_key === GameType.MultiGame ? Images.MULTI_GAME_IMG : item.game_key === GameType.Free2Play ? Images.FTP_IMG : item.game_key === GameType.Pred ? Images.PRED_IMG : item.game_key === GameType.Pickem ? Images.PICKEM_TOUR_IMG : item.game_key === GameType.OpenPred ? Images.OPEN_PRED_PP : item.game_key === GameType.OpenPredLead ? Images.OPEN_PRED_LEAD : Images.DFS_SIDE_IMG;
        }
        sportImg = (item.game_key === GameType.StockFantasy || item.game_key === GameType.StockPredict) ? Images.stock_hub : sportImg
        let avaSports = this.state.SelectedSport == '' ? item.allowed_sports : '';
        sportImg = item.image ? Utilities.getSettingURL(item.image) : sportImg;

        return (
            <li className="card-v" key={item.game_key} onClick={() => this.selectGameType(item)} >
                <div className="game-v">
                    <img src={sportImg} alt="" className={"sport-img" + (item.game_key === GameType.StockFantasy ? ' bg-transparent' : '')} />
                    <p className="game-v-title">{item[appLang + '_t']}</p>
                    <p className="game-v-detail">{item[appLang + '_d']}</p>
                    <span className="p-now">{(item.game_key === GameType.Pred || item.game_key === GameType.OpenPred || item.game_key === GameType.OpenPredLead) ? AL.PREDICT : item.game_key === GameType.Pickem ? AL.PICK : (AL.PLAY + '!')}</span>
                </div>
                {
                    avaSports && avaSports.length > 0 &&
                    <div className="ava-sport">
                        {
                            _Map(avaSports, (obj, idx) => {
                                var sportsId = '';
                                if (obj in Sports.url) {
                                    sportsId = Sports.url[obj + (appLang || '')];
                                }
                                if (idx < 3) {
                                    return (
                                        sportsId != '' &&
                                        <span className="sport-text" onClick={(e) => this.selectModuleSport(e, item, obj)} key={idx + sportsId}>{sportsId}</span>
                                    )
                                } else {
                                    return (
                                        <>
                                            {
                                                idx == 3 &&
                                                <span key={idx + sportsId} className="sport-text">+{avaSports.length - 3}</span>
                                            }
                                        </>
                                    )
                                }
                                // return (
                                //     idx < 3 ?
                                //         sportsId != '' &&
                                //         <span className="sport-text" onClick={(e) => this.selectModuleSport(e, item, obj)} key={idx + sportsId}>{sportsId}</span>
                                //         :
                                //         <span key={idx + sportsId} className="sport-text" onClick={(e) => this.selectModuleSport(e, '', '')}>+{avaSports.length - 3}</span>
                                // )
                            })
                        }
                    </div>
                }
            </li>
        )
    }
    updateDeviceToken = () => {
        let param = {
            "device_type": Utilities.getDeviceType(),
            "device_id": WSC.DeviceToken.getDeviceId(),
        }
        if (WSManager.loggedIn()) {
            updateDeviceToken(param).then((responseJson) => {
            })
        }
    }

    renderGameTypeSlider = (item) => {
        var sportImg = '';
        let spBGImg = '';
        if (item.image && item.image != '') {
            spBGImg = item.image
        }
        else {
            spBGImg = item.game_key === GameType.MultiGame ? 'MGBG.png' :
                item.game_key === GameType.Free2Play ? 'F2PBG.png' :
                    item.game_key === GameType.Pred ? 'PrdWinBG.png' :
                        item.game_key === GameType.Pickem ? 'PicmBG.png' :
                            item.game_key === GameType.OpenPred ? 'OpenPreBG.png' :
                                item.game_key === GameType.OpenPredLead ? 'OpenPreLdBG.png' :
                                    item.game_key === GameType.StockFantasy ? 'StBG.png' :
                                        item.game_key === GameType.StockFantasyEquity ? 'StEqBG.png' :
                                            item.game_key === GameType.StockPredict ? 'StPrdBG.png' :
                                                item.game_key === GameType.LiveFantasy ? 'LiveFanBG.png' :
                                                    'DFSBG.png';
        }
        return <div className="new-game-card" key={item.game_key} onClick={() => this.selectGameType(item)} >
            <div className="ngame-card-inn"
                style={{ backgroundImage: item.image && item.image != '' ? `url(${Utilities.getSettingURL(spBGImg)})` : `url(${Utilities.getS3URL(spBGImg)})`, backgroundSize: 'cover', backgroundRepeat: 'no-repeat', backgroundPosition: 'center' }}>
                <div className="card-inn">
                    <div className="gt-img">
                        {/* <img src={sportImg} alt="" /> */}
                    </div>
                    {/* <div className="gt-lbl">{item[appLang + '_t']}</div> */}
                </div>
            </div>
        </div>
    }

    renderFeaturedBanner = (item) => {
        let url = item.target_url;
        let isInAppLink = false;
        if (url.includes(process.env.REACT_APP_BASE_URL)) {
            url = url.split(process.env.REACT_APP_BASE_URL)[1];
        }
        else {
            isInAppLink = true;
        }
        return <div className="gametp-banr-itm" onClick={() => this.goToScreen(url, item.target_url, isInAppLink, item.name)}>
            <img src={item.image} alt="" />
        </div>
    }

    goTo = (page) => {
        ls.set('SHActive', true)
        this.props.history.push(page)
    }

    renderTabById = (tabItem) => {
        if (tabItem.tab_key === 'lobby') {
            return <li className="active">
                <a href>
                    <div className="animated-tab-div">
                        <div><i className="icon-home" /></div>
                        <div className="tab-label">{AL.HOME}</div>
                    </div>
                </a>
            </li>
        }
        else if (tabItem.tab_key === 'feed') {
            return <li>
                <a href onClick={() => WSManager.loggedIn() ? this.goTo('/feed') : this.props.history.push("/signup")}>
                    <div className="animated-tab-div">
                        <div><i className="icon-feed" /></div>
                        <div className="tab-label">{AL.FEED}</div>
                    </div>
                </a>
            </li>
        }
        else if (tabItem.tab_key === 'redeem') {
            return <li>
                <a href onClick={() => WSManager.loggedIn() ? this.goTo('/rewards') : this.props.history.push("/signup")} >
                    <div className="animated-tab-div">
                        <div><i className="icon-redeem" /></div>
                        <div className="tab-label">{AL.REDEEM}</div>
                    </div>
                </a>
            </li>
        }
        else if (tabItem.tab_key === 'my-contests') {
            return <li>
                <a href className="disabled">
                    <div className="animated-tab-div">
                        <div><i className="icon-my-contests" /></div>
                        <div className="tab-label"> {AL.MY_CONTEST}</div>
                    </div>
                </a>
            </li>
        }
        else if (tabItem.tab_key === 'my-profile') {
            return <li>
                <a href onClick={() => this.goTo('/my-profile')}>
                    <div className="animated-tab-div">
                        <div><i className="icon-profile" /></div>
                        <div className="tab-label">{AL.PROFILE}</div>
                    </div>
                </a>
            </li>
        }
        else if (tabItem.tab_key === 'leaderboard') {
            if ((IsGameTypeEnabled(GameType.DFS) && Utilities.getMasterData().a_dfst == 1) || Utilities.getMasterData().a_pickem_tournament == 1) {
                return <li>
                    <a href onClick={() => WSManager.loggedIn() ? this.goTo('/tour-leaderboard') : this.props.history.push("/signup")} className={`${!WSManager.loggedIn() && 'disabled'}`}>
                        <div className="animated-tab-div">
                            <div><i className="icon-leaderboard" /></div>
                            <div className="tab-label">{AL.LEADERBOARD}</div>
                        </div>
                    </a>
                </li>
            }
            else {
                return <li>
                    <a href onClick={() => this.goTo('/leaderboard')}>
                        <div className="animated-tab-div">
                            <div><i className="icon-leaderboard" /></div>
                            <div className="tab-label">{AL.LEADERBOARD}</div>
                        </div>
                    </a>
                </li>
            }
        }
        else if (tabItem.tab_key === 'more') {
            return <li>
                <a href onClick={() => this.goTo('/more')}>
                    <div className="animated-tab-div">
                        <div><i className="icon-more-large" /></div>
                        <div className="tab-label">{AL.MORE}</div>
                    </div>
                </a>
            </li>
        }
        else if ((tabItem.tab_key === 'earn-coins' || tabItem.tab_key === 'sports-hub') && (Utilities.getMasterData().a_coin == "1")) {
            return <li>
                <a href onClick={() => WSManager.loggedIn() ? this.props.history.push('/earn-coins') : this.props.history.push("/signup")}>
                    <div className="isCoin coin-shine">
                        <div className="shadow-v" />
                        <span className="coins-tab-label">{AL.EARN_COINS}</span>
                        <span className='fcoin'>
                            <img src={Images.EARN_COINS} alt="" />
                            <React.Fragment>
                                <div className="spark1">✦</div>
                                <div className="spark2">✦</div>
                                <div className="spark3">✦</div>
                            </React.Fragment>
                        </span>
                    </div>
                </a>
            </li>
        }
    }
    infoSportsDetailShow = (e, item, name) => {
        e.stopPropagation();
        this.setState({
            infoSportsDetail: true,
            nameConfirmation: name,
            dataForInfoDetail: item
        })
    }
    infoSportsDetailsHide = () => {
        this.setState({ infoSportsDetail: false })
    }

    calcPer = (point, total) => {
        point = parseInt(point);
        total = parseInt(total);
        let per = ((point / total) * 100).toFixed(2) + '%';
        return per;
    }
    goToPage = (pathname) => {
        this.props.history.push({ pathname: pathname, state: { goBackProfile: true, userXPDetail: this.state.userXPDetail } });
    }

    goToLeague = (item) => {
        if (WSManager.loggedIn()) {
        this.props.history.push({
            pathname: '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/featured-tournament/' + item.dfs_id + '/' + item.pickem_id, state: {
                data: item,
            }
        })
    }else{
        this.props.history.push({ pathname: '/signup' })
    }
    }

    render() {
        const { userXPDetail, dataForInfoDetail, nameConfirmation, infoSportsDetail, sliderData, SLIST, HOS, showHubFitlers, filterArray, SelectedSport, FilterHGLIST, FilterGLIST, REALGAMELIST, COINGAMELIST, FSPBanner, AdvSPBanner, footerTabs, featuredLeague } = this.state;
        let isMaxPt = userXPDetail.max_level == userXPDetail.level_number;
        let total = isMaxPt ? parseInt(userXPDetail.max_end_point) - parseInt(userXPDetail.start_point) : parseInt(userXPDetail.next_level_start_point) - parseInt(userXPDetail.start_point);
        let point = parseInt(userXPDetail.point) - parseInt(userXPDetail.start_point);
        let maxExc = (userXPDetail.max_end_point && parseInt(userXPDetail.point) > parseInt(userXPDetail.max_end_point)) ? true : false;
        var settingsBnr = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            centerMode: FSPBanner.length == 1 ? false : true,
            className: "center",
            centerPadding: "20px 0 0 0",
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 1,
                        centerMode: FSPBanner.length == 1 ? false : true,
                        className: "center",
                        centerPadding: "20px 0 0 0"
                    }

                },
            ]
        };
        var settingsAdvBnr = {
            touchThreshold: 10,
            infinite: true,
            slidesToScroll: 1,
            slidesToShow: 1,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            centerMode: AdvSPBanner.length == 1 ? false : true,
            className: "center",
            centerPadding: "20px 0 0 0",
            autoplay: true,
            autoplaySpeed: 4500,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 1,
                        centerMode: AdvSPBanner.length == 1 ? false : true,
                        className: "center",
                        centerPadding: "20px 0 0 0"
                    }

                },
            ]
        };
        let FitlerOptions = {
            showHubFitlers: showHubFitlers,
            filtered_league_id: SelectedSport
        }
        var settingsLeague = {
            touchThreshold: 10,
            infinite: false,
            slidesToScroll: 1,
            slidesToShow: 2.5,
            variableWidth: false,
            initialSlide: 0,
            dots: false,
            // centerMode: AdvSPBanner.length == 1 ? false : true,
            // className: "center",
            // centerPadding: "20px 0 0 0",
            autoplay: false,
            autoplaySpeed: 4500,
            beforeChange: this.BeforeChange,
            responsive: [
                {
                    breakpoint: 530,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 1.7,
                        // centerMode: AdvSPBanner.length == 1 ? false : true,
                        // className: "right",
                        // centerPadding: "20px 0 0 0"
                    }
                },
                {
                    breakpoint: 425,
                    settings: {
                        slidesToScroll: 1,
                        slidesToShow: 1.5,
                        // centerMode: AdvSPBanner.length == 1 ? false : true,
                        // className: "right",
                        // centerPadding: "20px 0 0 0"
                    }

                },
            ]
        };

        // var settingsLeague = {
        //     touchThreshold: 10,
        //     infinite: false,
        //     slidesToScroll: 1,
        //     slidesToShow: 2.2,
        //     variableWidth: false,
        //     initialSlide: 0,
        //     dots: false,
        //     autoplay: false,
        //     autoplaySpeed: 5000,
        //     centerMode: false,
        //     centerPadding: "13px",
        //     responsive: [
        //         {
        //             breakpoint: 500,
        //             settings: {
        //                 // className: "center",
        //                 // centerPadding: "13px",
        //                 slidesToShow: 1.5,
        //             }
        //         }
        //         ]
        //     }
        return (
            <MyContext.Provider >
                <div className={"web-container sports-hub-c new-sport-hub " + (SLIST.length > 1 ? 'web-container-fixed' : 'web-container-no-fixed') + ' sport-hub-scroll'}>
                    <Helmet titleTemplate={`${MetaData.template} | %s`}>
                        <title>{MetaData.SHS.title}</title>
                        <meta name="description" content={MetaData.SHS.description} />
                        <meta name="keywords" content={MetaData.SHS.keywords}></meta>
                    </Helmet>
                    <CustomHeader {...this.props} HeaderOption={HOS} showLobbyFitlers={this.showFilter} />
                    {showHubFitlers && <Suspense fallback={<div />} ><Filter
                        {...this.props}
                        selectedFSport={SelectedSport}
                        sportsList={filterArray}
                        FitlerOptions={FitlerOptions}
                        hideFilter={this.hideFilter}
                        filterBySport={this.filterBySport}
                    /></Suspense>}
                    <div className='hub-ad-left'>
                        <img src={Images.AD_BANNER} alt='' className='img-fluid' />
                    </div>
                    <div className="dashboard-container">                        
                        <ul className="card-container"> 

                            <div className="new-game-wrap">                                {
                                    FSPBanner && FSPBanner.length == 1 &&
                                    <div className="gametp-banner-sec">
                                        {this.renderFeaturedBanner(FSPBanner[0])}
                                    </div>
                                }
                                
                                {
                                    FSPBanner && FSPBanner.length > 1 &&
                                    <div className="gametp-banr-slider">
                                        <Suspense fallback={<div />} ><ReactSlickSlider settings={settingsBnr}>
                                            {
                                                _Map(FSPBanner, (item, idx) => {
                                                    return this.renderFeaturedBanner(item)
                                                })
                                            }
                                        </ReactSlickSlider></Suspense>
                                    </div>
                                }

                                {
                                    featuredLeague.length > 0 &&
                                    <div className='spotlight-heading'><span>{CommonLabels.SPOTLIGHT_TXT}</span></div>
                                }

                                <div className={`gametp-banr-slider gametp-banr-slider-league ${featuredLeague.length == 1 ? "gametp-banr-slider-new" : ''}`}>
                                    <Suspense fallback={<div />} ><ReactSlickSlider settings={settingsLeague}>
                                        {
                                            _Map(featuredLeague, (item, idx) => {
                                                return (
                                                    <div className='featured-new-league' onClick={() => this.goToLeague(item)}>
                                                        <div className='f-league'>
                                                            <div className='league'>{item.name}</div>
                                                        </div>
                                                        {/* <img src={item.sports_id == "7" ? Images.CRICKET_1 : item.sports_id == "5" ? Images.SOCCER_1 : item.sports_id == "1" ? Images.BASEBALL_1 : item.sports_id == "4" ? Images.BASKETBALL_1 : item.sports_id == "15" ? Images.FL1 : item.sports_id == "13" ? Images.NFL_1 : item.sports_id == "11" ? Images.TENNIS_1 : item.sports_id == "3" ? Images.RUGBY_1 : item.sports_id == "8" ? Images.KABADDI_1 : item.sports_id == "9" ? Images.GOLF_1 : item.sports_id == "10" ? Images.BADMINTON_1 : item.sports_id == "6" ? Images.HOCKEY_1 : ''} /> */}
                                                        <img src={item.sports_id == "1" ? Images.BASEBALL_1 : item.sports_id == "2" ? Images.NFL_1  : item.sports_id == "3" ?  Images.RUGBY_1 : item.sports_id == "4" ? Images.BASKETBALL_1 : item.sports_id == "5" ? Images.SOCCER_1 : item.sports_id == "6" ? Images.HOCKEY_1 : item.sports_id == "7" ? Images.CRICKET_1 : item.sports_id == "8" ? Images.KABADDI_1 : item.sports_id == "9" ? Images.GOLF_1 : item.sports_id == "10" ? Images.BADMINTON_1 : item.sports_id == "11" ? Images.TENNIS_1 : item.sports_id == "13" ? Images.NCAA_1 : item.sports_id == "15" ? Images.FL1 : '' } />
                                                    </div>
                                                )
                                            })
                                        }
                                    </ReactSlickSlider></Suspense>
                                </div>

                                {/* {
                                    !isEmpty(featuredLeague) && featuredLeague.map((item) => {
                                        return <>item.name</>
                                    })
                                } */}

                                <div className='new-sports-hub-changes'>
                                    {
                                        this.state.sh_list.sort((a, b) => a.display_order - b.display_order).map((item, idx) => {
                                            return (
                                                <>
                                                    {idx == 2 && WSManager.loggedIn() &&
                                                        (Utilities.getMasterData().a_quiz == 1 || Utilities.getMasterData().a_xp_point == 1) &&
                                                        <>
                                                            <div className="second-ing-quiz-refer-container">

                                                                {Utilities.getMasterData().a_quiz == 1 && <div className="quiz-view" onClick={() => this.props.history.push("/earn-coins")} >
                                                                    <img src={Images.Sports_Hub_Quiz_Img} alt="" className='images-xp-quiz' />
                                                                    <div className="quix-text-view">
                                                                        {AL.PLAY_QUIZ_AND_WIN_TEXT}
                                                                    </div>
                                                                </div>
                                                                }
                                                                {Utilities.getMasterData().a_xp_point == 1 &&
                                                                    <div className="xp-changes-view"
                                                                        onClick={() => this.goToPage('/experience-points')}
                                                                    >
                                                                        <div className="text-for-current-level">{AL.YOUR_CURRENT_XP_LEVEL_TEXT1}</div>
                                                                        <div className="sports-card-slider">
                                                                            <div className="progress-bar progress-bar-new" style={{ width: (maxExc ? '100%' : this.calcPer(point, total)) }}></div>
                                                                            {!maxExc && <span>{userXPDetail.level_number}</span>}
                                                                            {
                                                                                maxExc ?
                                                                                    <span className="next-lvl">{userXPDetail.level_number}{maxExc && <>+</>}</span>
                                                                                    :
                                                                                    <span className="next-lvl">{userXPDetail.next_level}</span>
                                                                            }
                                                                        </div>
                                                                        <div className='earn-button'>
                                                                            <div className="button-view" onClick={() => this.goToPage('/experience-points')}><img src={Images.EARN_XPPOINTS} alt="" /> {AL.EARN_XP}</div>

                                                                        </div>
                                                                    </div>}
                                                                {((Utilities.getMasterData().a_xp_point == 0) || (Utilities.getMasterData().a_quiz == 0)) &&
                                                                    <div className="quiz-view" onClick={() => this.props.history.push("/refer-friend")}
                                                                    >
                                                                        <img src={Images.Sport_Redeem_Img} alt="" className='images-xp-quiz' />
                                                                    </div>
                                                                }
                                                            </div>
                                                        </>
                                                    }
                                                    <div className="sports-hub-img-view" key={idx}
                                                        onClick={() => this.selectGameType(item)}
                                                        style={{ backgroundImage: `url(${Utilities.getSettingURL(item.image)})` }}
                                                    >
                                                        <i className='icon-info' onClick={(e) => this.infoSportsDetailShow(e, item, item.game_key)} />

                                                    </div>
                                                </>
                                            )
                                        })
                                    }
                                </div>

                                {
                                    AdvSPBanner && AdvSPBanner.length >= 0 &&
                                    <>
                                        <div className={`gametp-banr-slider ${AdvSPBanner.length == 1 ? "gametp-banr-slider-new" : ''}`}>
                                            <Suspense fallback={<div />} ><ReactSlickSlider settings={settingsAdvBnr}>
                                                {
                                                    _Map(AdvSPBanner, (item, idx) => {
                                                        return this.renderFeaturedBanner(item)
                                                    })
                                                }
                                            </ReactSlickSlider></Suspense>
                                        </div>
                                    </>
                                }
                            </div>


                        </ul>



                    </div>
                    <div className="sports-hub-footer-tab">
                        <ul>
                            {footerTabs !== undefined &&
                                _Map(footerTabs, (item, idx) => {
                                    return this.renderTabById(item)
                                })
                            }
                        </ul>
                    </div>
                    {false &&
                        Utilities.getMasterData().a_coin == "1" && WSManager.loggedIn() && <div className="sports-hub-footer-tabs">
                            <div className="dot-list left">{
                                _times(6, (itm) => {
                                    return (
                                        <span key={itm} />
                                    )
                                })
                            }
                            </div>
                            <div onClick={() => this.props.history.push('/earn-coins')} className="isCoin coin-shine cursor-pointer">
                                <div className="shadow-v" />
                                <span className={"coins-tab-label" + (AL.EARN_COINS.length > 12 ? ' marque' : '')}>{AL.EARN_COINS}</span>
                                <span className="fcoin">
                                    <img src={Images.DT_EARN_COINS} alt="" />
                                    {/* <img src={DARK_THEME_ENABLE ? Images.DT_EARN_COINS : Images.EARN_COINS} alt="" /> */}
                                    <>
                                        <div className="spark1">✦</div>
                                        <div className="spark2">✦</div>
                                        <div className="spark3">✦</div>
                                    </>
                                </span>
                            </div>
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
                    {
                        infoSportsDetail &&
                        <Modal
                            show={infoSportsDetail}
                            onHide={() => this.infoSportsDetailsHide()}
                            dialogClassName="custom-bg-modal custom-modal header-circular-modal info-details-modal-view"
                            className="center-modal custom-bg-modal-dialog"
                        >
                            <a href className="close-header"
                                onClick={() => this.infoSportsDetailsHide()}
                            ><i className="icon-close"></i></a>
                            <Modal.Header className="">
                                <div className="modal-img-wrap">
                                    <div className="wrap ">
                                        <i className='icon-sports-info' />
                                    </div>
                                </div>
                                {nameConfirmation == 'allow_dfs' ? AL.DAILY_FANTASY : nameConfirmation == 'allow_stock_fantasy' ? AL.STOCK_FANTASY : nameConfirmation == 'allow_equity' ? AL.STOCK_EQUITY : nameConfirmation == 'allow_prediction' ?
                                    AL.SPORTS_PREDICTOR : nameConfirmation == 'allow_live_stock_fantasy' ? AL.LIVE_STOCK_FANTASY : nameConfirmation == "pickem_tournament" ? AL.PICKEM + ' ' + AL.TOURNAMENT : nameConfirmation == 'picks_fantasy' ? AL.PICKS_FANTASY : nameConfirmation == 'allow_stock_predict' ? AL.STOCK_PREDICT : nameConfirmation == 'live_fantasy' ? AL.LIVE_FANTASY : nameConfirmation == 'allow_fixed_open_predictor' ? AL.PREDICTOR_LEADERBOARD : nameConfirmation == 'allow_open_predictor' ? AL.OPEN_PREDICTOR : nameConfirmation == 'props_fantasy' ? AL.CommonLabels.OPEN_PROPS : nameConfirmation == 'opinion_trade_fantasy' ? CommonLabels.OPINION_TRADING : ""
                                }

                            </Modal.Header>
                            <Modal.Body className='innner-text-info'>

                                <div className="inner-text-info-one">
                                    {nameConfirmation == 'allow_dfs' ? AL.DFS_TEXT_1 : nameConfirmation == 'allow_stock_fantasy' ? AL.STOCK_FANTASY_TEXT_1 : nameConfirmation == 'allow_equity' ? AL.STOCK_EQUITY_TEXT_1 : nameConfirmation == 'allow_prediction' ?
                                        AL.SPORTS_PREDICTOR_TEXT_1 : nameConfirmation == 'allow_live_stock_fantasy' ? AL.LIVE_STOCK_FANTASY_TEXT_1 : nameConfirmation == "pickem_tournament" ? AL.PICKEM_TEXT_1 : nameConfirmation == 'picks_fantasy' ? AL.PICKS_FANTASY_TEXT_1 : nameConfirmation == 'allow_stock_predict' ? AL.STOCK_PREDICT_TEXT_1 : nameConfirmation == 'live_fantasy' ? AL.LIVE_FANTASY_TEXT_1 : nameConfirmation == 'allow_fixed_open_predictor' ? AL.PREDICTOR_LEADERBOARD_TEXT_1 : nameConfirmation == 'allow_open_predictor' ? AL.OPEN_PREDICTOR_TEXT_1 : nameConfirmation == 'props_fantasy' ? CommonLabels.WELCOME_TEXT_PROPS : nameConfirmation == 'opinion_trade_fantasy' ? CommonLabels.OPINION_TRADING_DETAILS : ""
                                    }
                                </div>
                                <div className="inner-text-info-two">
                                    {nameConfirmation == 'allow_dfs' ? AL.DFS_TEXT_2 : nameConfirmation == 'allow_stock_fantasy' ? AL.STOCK_FANTASY_TEXT_2 : nameConfirmation == 'allow_equity' ? AL.STOCK_EQUITY_TEXT_2 : nameConfirmation == 'allow_prediction' ?
                                        AL.SPORTS_PREDICTOR_TEXT_2 : nameConfirmation == 'allow_live_stock_fantasy' ? AL.LIVE_STOCK_FANTASY_TEXT_2 : nameConfirmation == "pickem_tournament" ? AL.PICKEM_TEXT_2 : nameConfirmation == 'picks_fantasy' ? AL.PICKS_FANTASY_TEXT_2 : nameConfirmation == 'allow_stock_predict' ? AL.STOCK_PREDICT_TEXT_2 : nameConfirmation == 'live_fantasy' ? AL.LIVE_FANTASY_TEXT_2 : nameConfirmation == 'allow_fixed_open_predictor' ? AL.PREDICTOR_LEADERBOARD_TEXT_2 : nameConfirmation == 'allow_open_predictor' ? AL.OPEN_PREDICTOR_TEXT_2 : ''
                                    }
                                </div>
                                <div className='start-playing-button-view'>
                                    <div className='start-playing-button' onClick={() => this.selectGameType(dataForInfoDetail)}> {AL.START_PLAYING}! </div></div>
                            </Modal.Body>

                        </Modal>
                    }
                </div>
            </MyContext.Provider>
        )
    }
}

export default SportsHub;