import React from 'react';
import Cookies from 'universal-cookie';
import moment from 'moment';
import { getMasterData, loadLanguageResource, setAdsgraphyTrackingID, activateAccount, getBannedStats, GetPickFantasySports } from "../WSHelper/WSCallings";
import { withTranslation } from "react-i18next";
import { changeLanguageString, TODAY } from "../helper/AppLabels";
import { _Map, _isNull, Utilities, sendMessageToApp, _isUndefined, _filter } from '../Utilities/Utilities';
import { LANGUAGE_OBJ, setValue, GameType, ALLOW_LANG, SELECTED_GAMET } from '../helper/Constants';
import { createBrowserHistory } from 'history';
import CustomToast from "../Component/CustomComponent/Toast";
import CustomLoader from '../helper/CustomLoader';
import Routing from './Routing';
import ls from 'local-storage';
import WSManager from '../WSHelper/WSManager';
import * as WSC from "../WSHelper/WSConstants";
import { SetFantasyList } from '../JsonFiles/Sports';
import GeoLocationModal from '../../src/views/GeoLocationTagging/GeoLocationModal';
import withGeoFencing from '../WSHelper/GeoFencing';
import { withRedux } from 'ReduxLib';
export const MyContext = React.createContext()

const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
const cookies = new Cookies();
var globalThis = null;
class MyProvider extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            AppDataPosting: false,
            geoLoca: false
        }
    }

    getLanguageCode() {
        let lsLang = WSManager.getAppLang();
        let lang = lsLang ? lsLang.split('-') : (this.props.i18n && this.props.i18n.language) ? this.props.i18n.language.split('-') : ['en'];
        return lang[0];
    }

    loadResources = async (masterData) => {
        var l_code = this.getLanguageCode();
        let isCodeExist = false;
        _Map(masterData.l_list, (item) => {
            if (item == l_code && !isCodeExist) {
                isCodeExist = true;
            }
        })

        var param = { lang_code: isCodeExist ? l_code : masterData.default_lang };
        this.props.i18n.changeLanguage(param.lang_code);
        var api_response_data = await loadLanguageResource(param)
        if (api_response_data) {
            _Map(Object.keys(api_response_data), (key) => {
                LANGUAGE_OBJ[key] = api_response_data[key];
            })
        }
        else {
            import("../assets/i18n/translations/" + param.lang_code + ".json").then(data => {
                _Map(Object.keys(data), (key) => {
                    LANGUAGE_OBJ[key] = data[key];
                })
                changeLanguageString();
            });
        }
    }

    checkAppISLoggedIn(flag) {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                type: flag,
                targetFunc: 'handleLoginReceived'
            }
            sendMessageToApp(data);
            // this.handelNativeData();
        }
    }

    getHash = () => {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'otpHash',
                targetFunc: 'getHash'
            }
            sendMessageToApp(data);
            // this.handelNativeData();
        }
    }

    loadLangLocally() {
        var l_code = this.getLanguageCode();
        import("../assets/i18n/translations/" + l_code + ".json").then(data => {
            _Map(Object.keys(data), (key) => {
                LANGUAGE_OBJ[key] = data[key];
            })
            changeLanguageString();
        });
    }

    checkForAdsgraphyTrackingID = () => {

        var trackData = cookies.get('_adsgtd');
        if (!trackData && !_isUndefined(parsed)) {
            var trackingValue = parsed.t1 || parsed.t2;
            trackingValue = trackingValue ? trackingValue : (parsed.t3 || parsed.t4);
            trackingValue = trackingValue ? trackingValue : parsed.t5;
            trackingValue = (trackingValue instanceof Array) ? trackingValue[0] : trackingValue;
            if (trackingValue) {

                let param = {
                    affiliate_reference_id: trackingValue
                }

                setAdsgraphyTrackingID(param).then((responseJson) => {
                    if (responseJson.response_code == WSC.successCode) {
                        let expiryDate = new Date(moment().add(7, 'days'));
                        trackData = {
                            affiliate_reference_id: trackingValue,
                            user_track_id: responseJson.data.user_track_id
                        }
                        cookies.set('_adsgtd', JSON.stringify(trackData), { expires: expiryDate });
                    }
                })

            }
        }
    }

    getSportNLangLocalList = (apiDATA) => {
        let FList = [{
            ben: "বৈশিষ্ট্যযুক্ত",
            en: "FEATURED",
            es: "Presentada",
            fr: "Mis en exergue",
            guj: "ફીચર્ડ",
            hi: "फीचर्ड",
            id: "Unggulan",
            kn: "ವೈಶಿಷ್ಟ್ಯಗೊಳಿಸಲಾಗಿದೆ",
            pun: "ਫੀਚਰਡ",
            ru: "Избранное",
            sports_id: "0",
            sports_name: "FEATURED",
            tam: "இடம்பெற்றது",
            team_player_count: "",
            th: "แนะนำ",
            tl: "Itinatampok",
            zh: "精选"
        }]
        FList = [...FList, ...apiDATA.fantasy_list]
        if (FList) {
            let data = {
                default_sport: parseInt(apiDATA.default_sport || '7'),
                url: {}
            };
            _Map(FList, (item) => {
                let spID = parseInt(item.sports_id || '0');
                let spName = ((item.en || item.sports_name) || '').toLowerCase();
                data[spName] = spID;
                data.url[spID] = spName;
                _Map(apiDATA.l_list, (lObj) => {
                    data.url[spID + (lObj || '')] = item[lObj];
                })
            })
            SetFantasyList.FantasyList(data)
        }
        if (apiDATA.l_list) {
            let tmpAL = []
            _Map(ALLOW_LANG, (lObj) => {
                if (apiDATA.l_list.includes(lObj.value)) {
                    tmpAL.push(lObj);
                }
            })
            setValue.setLanguage(tmpAL);
        }
    }


    componentDidMount() {
        this.userAccountActivitaion();
        // this.getHash();
    }

    userAccountActivitaion() {
        if (parsed && parsed.activation_key) {
            this.callUserAccountActivitaion(parsed.activation_key)
        }
    }
    getUserLatLongWeb = (data) => {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                let data = {
                    'lat': position.coords.latitude,
                    'longi': position.coords.longitude,
                };
                this.setUserLatLongTrigerDuration(data)
            }, (error) => {
                ls.set('encodedLatLong', 0)

            }, {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 1000
            });
        } else {
        }
    }

    callUserAccountActivitaion(activeKey) {
        let param = {
            "key": activeKey,
        }
        activateAccount(param).then((responseJson) => {
            if (responseJson.response_code == WSC.successCode) {
                Utilities.showToast(responseJson.message, 3000);
            } else {
                Utilities.showToast(responseJson.global_error, 3000);
            }
        })
    }

    getBannedStateList() {
        let bsList = ls.get('bslist');
        let bslistTime = ls.get('bslistTime');
        let minuts = bslistTime ? Utilities.minuteDiffValue(bslistTime) : 0;
        let hours = Math.floor(minuts / 60);
        if (bsList && hours < 2) {
            let Data = Utilities.getMasterData() || {};
            Data['banned_state'] = bsList;
            let banStates = Object.keys(Data.banned_state || {});
            setValue.setBanStateEnabled(banStates.length > 0);
            Utilities.setMasterData(Data);
        } else {

            getBannedStats().then((responseJson) => {
                if (responseJson.response_code == WSC.successCode) {
                    let Data = Utilities.getMasterData();
                    Data['banned_state'] = responseJson.data;
                    let banStates = Object.keys(responseJson.data || {});
                    setValue.setBanStateEnabled(banStates.length > 0);
                    Utilities.setMasterData(Data);
                    ls.set('bslist', responseJson.data);
                    ls.set('bslistTime', { date: Date.now() });
                }
            })
        }
    }

    UNSAFE_componentWillMount() {
        this.checkForAdsgraphyTrackingID()
        this.checkAppISLoggedIn(false)
        this.loadLangLocally()
        let gameType = WSManager.getPickedGameType();
        setValue.setSelectedGameType(gameType || '');
        let whref = window.location.href;
        if (whref.includes('#prediction')) {
            WSManager.setPickedGameType(GameType.Pred);
            setValue.setGameTypesEnable(GameType.Pred);
            gameType = GameType.Pred;
        }
        if (whref.includes('#multigame')) {
            WSManager.setPickedGameType(GameType.MultiGame);
            setValue.setGameTypesEnable(GameType.MultiGame);
            gameType = GameType.MultiGame;
        }
        // if (whref.includes('#pickem')) {
        //     WSManager.setPickedGameType(GameType.Pickem);
        //     setValue.setGameTypesEnable(GameType.Pickem);
        //     gameType = GameType.Pickem;
        // }
        if (whref.includes('#pickem-tournament')) {
            WSManager.setPickedGameType(GameType.PickemTournament);
            setValue.setGameTypesEnable(GameType.PickemTournament);
            gameType = GameType.PickemTournament;
        }
        if (whref.includes('#tournament')) {
            WSManager.setPickedGameType(GameType.Tournament);
            setValue.setGameTypesEnable(GameType.Tournament);
            gameType = GameType.Tournament;
        }
        if (whref.includes('#freeToPlay')) {
            WSManager.setPickedGameType(GameType.Free2Play);
            setValue.setGameTypesEnable(GameType.Free2Play);
            gameType = GameType.Free2Play;
        }
        if (whref.includes('#open-predictor')) {
            WSManager.setPickedGameType(GameType.OpenPred);
            setValue.setGameTypesEnable(GameType.OpenPred);
            gameType = GameType.OpenPred;
        }
        if (whref.includes('#open-predictor-leaderboard')) {
            WSManager.setPickedGameType(GameType.OpenPredLead);
            setValue.setGameTypesEnable(GameType.OpenPredLead);
            gameType = GameType.OpenPredLead;
        }
        if (whref.includes('#stock-fantasy')) {
            WSManager.setPickedGameType(GameType.StockFantasy);
            setValue.setGameTypesEnable(GameType.StockFantasy);
            gameType = GameType.StockFantasy;
        }
        if (whref.includes('#stock-fantasy-equity')) {
            WSManager.setPickedGameType(GameType.StockFantasyEquity);
            setValue.setGameTypesEnable(GameType.StockFantasyEquity);
            gameType = GameType.StockFantasyEquity;
        }
        if (whref.includes('#live-stock-fantasy')) {
            WSManager.setPickedGameType(GameType.LiveStockFantasy);
            setValue.setGameTypesEnable(GameType.LiveStockFantasy);
            gameType = GameType.LiveStockFantasy;
        }
        if (whref.includes('#pick-fantasy')) {
            WSManager.setPickedGameType(GameType.PickFantasy);
            setValue.setGameTypesEnable(GameType.PickFantasy);
            gameType = GameType.PickFantasy;
        }
        if (whref.includes('#props')) {
            WSManager.setPickedGameType(GameType.PropsFantasy);
            setValue.setGameTypesEnable(GameType.PropsFantasy);
            gameType = GameType.PropsFantasy;
        }
        if (whref.includes('#opinion-trade')) {
            WSManager.setPickedGameType(GameType.OpinionTradeFantasy);
            setValue.setGameTypesEnable(GameType.OpinionTradeFantasy);
            gameType = GameType.OpinionTradeFantasy;
        }

        if (parsed.sgmty) {
            let urlGT = atob(parsed.sgmty)
            WSManager.setPickedGameType(urlGT);
            setValue.setGameTypesEnable(urlGT);
            gameType = urlGT;
        }



        getMasterData().then((api_response_data) => {
            if (api_response_data) {
                this.getSportNLangLocalList(api_response_data)
                if (api_response_data.leaderboard && api_response_data.leaderboard.length > 0) {
                    let dfsSelected = true//!gameType || gameType == GameType.DFS || gameType == GameType.StockFantasy || gameType == GameType.StockFantasyEquity || gameType == GameType.StockPredict
                    setValue.setDFSL(dfsSelected, api_response_data.allow_social);
                }
                if (process.env.REACT_APP_SERVE_LANG_LOCALLY != '1') {
                    this.loadResources(api_response_data).then(() => {
                        changeLanguageString();
                    });
                }
                const Data = {...api_response_data, dfs_multi: 1};
                let _SHD = Data.sports_hub
                if(process.env.REACT_APP_TEMP_DFS == '1') {
                     _SHD = _Map(Data.sports_hub, obj => {
                        if(obj.game_key == 'allow_dfs') {
                            obj.is_desktop = 1
                        }
                        return obj
                    })
                }
                const SHD = _SHD ? _SHD : []
                setValue.setCountryCodeData(Data.login_data);
                setValue.setOnlyCoinFlow(Data.coin_only);
                if (SHD.length > 1) {
                    setValue.setSportsHubAllow(true);
                } else {
                    setValue.setSportsHubAllow(false)
                }
                if (Data.otp_size) {
                    setValue.setOtpSize(Data.otp_size);
                }
                let banStates = Object.keys(Data.banned_state || {});
                setValue.setBanStateEnabled(banStates.length > 0);
                let appCacheVersion = WSManager.getLS('acv');
                if (appCacheVersion && appCacheVersion != Data.app_cache_version) {
                    WSManager.logout();
                } else {
                    ls.set('acv', Data.app_cache_version);
                    let apV = Data.app_version;
                    if (!apV || apV.length === 0) {
                        Data['app_version'] = '';
                        apV = ''
                    }
                    let downloadlinkData = apV && apV.android ? apV.android : {};
                    setValue.setAppDownloadLink(downloadlinkData.app_url)
                    let selectedSports = Utilities.getUrlSports();
                    Utilities.setMasterData(Data);
                    if (!window.ReactNativeWebView && Data.bs_a && Data.bs_a == '1' && WSManager.loggedIn()) {
                        this.getUserLatLongWeb()

                    }
                    if (SHD.length === 1 && !WSManager.getPickedGameType()) {
                        WSManager.setPickedGameType(SHD[0].game_key);
                        setValue.setGameTypesEnable(SHD[0].game_key);
                        WSManager.setPickedGameTypeID(SHD[0].sports_hub_id);
                    } else if (gameType) {
                        let isGameAllowed = false;
                        _Map(SHD, (item) => {
                            setValue.setGameTypesEnable(item.game_key);
                            if (item.game_key == gameType) {
                                isGameAllowed = true
                            }
                        })
                        if (!isGameAllowed) {
                            WSManager.removeLSItem('SHGT');
                        }
                    } else {
                        _Map(SHD, (item) => {
                            setValue.setGameTypesEnable(item.game_key);
                        })
                    }

                    if (selectedSports === "null") {
                        selectedSports = Data.default_sport;
                    }
                    this.setState({
                        AppDataPosting: true
                    }, () => {
                        changeLanguageString();
                    });
                    setTimeout(() => {
                        this.web_loaded();
                    }, 10);

                    if (_isNull(selectedSports) || selectedSports === Data.default_sport) {
                        ls.set('selectedSports', Data.default_sport);
                        setValue.setAppSelectedSport(Data.default_sport);
                    } else {
                        setValue.setAppSelectedSport(selectedSports);
                    }
                    let app_version = {
                        action: 'app_download',
                        targetFunc: 'app_download',
                        type: 'android',
                        data: apV.android || {}
                    }
                    
                    sendMessageToApp(app_version)
                    this.getHash();
                    this.handelNativeData();
                    if (WSManager.loggedIn()) {
                        this.getBannedStateList();
                    }

                    if ((WSManager.loggedIn() || WSManager.getTempToken('id_temp_token')) && Data.bs_a == 1) {
                        this.props.navigatorCheck();
                    }

                }


                // if(api_response_data.allow_picks == '1'){
                //     GetPickFantasySports().then((response_data)=>{
                //         if (response_data) {
                //             let data = response_data.data;
                //             ls.set('PFSportList',data)
                //             setValue.setPFSelectedSport(data && data[0]);
                //         }
                //     })
                // }
            }
        });
    }

    handelNativeData() {
        window.addEventListener('message', (e) => {
            if (e.data.action === 'app_version' && e.data.type === 'android') {
                Utilities.setAndroidAppVersion('' + e.data.version)
            }
            if (e.data.locale) {
                WSManager.setAppLang(e.data.locale);
            }
            if (e.data.UserProfile) {
                WSManager.setProfile(e.data.UserProfile);
            }
            if (e.data.LoginSessionKey) {
                WSManager.setToken(e.data.LoginSessionKey);
            }
            if (e.data.action === 'web_loaded' ) {
                if (e.data.isMobileApp) {
                    WSManager.setIsMobileApp(e.data.isMobileApp);
                }
                if (e.data.isIOSApp) {
                    WSManager.setIsIOSApp(e.data.isIOSApp);
                }
            }
            if (e.data.action === 'latLong' && e.data.type === 'deviceLatLong') {
                this.setUserLatLongTrigerDuration(e.data)
            }
            if (e.data.action === 'otpHash') {
                if (ls.get('otp_hash') == null) {
                    ls.set('otp_hash', e.data.otp_hash)
                }
            }
            if (e.data.action === 'geo_location') {
                if (e.data.res == '1') {

                }
            }
        });
    }

    setUserLatLongTrigerDuration = (data) => {
        var currentTime = Math.round((new Date()).getTime() / 1000);
        let latlongtimeMain = ls.get('latlongtimeMain');
        if (latlongtimeMain == null) {

            let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
            var mininmilsecond = parseInt(nextTrigerTime) * 60;
            var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
            ls.set('latlongtimeMain', expiredTime)

            let latlong = data.lat + ',' + data.longi
            var encodedData = btoa(latlong)
            ls.set('encodedLatLong', encodedData)

            WSC.UserLatLong.setLatLONG(encodedData);

        }
        else if (parseFloat(currentTime) > parseFloat(latlongtimeMain)) {
            let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm : 0
            var mininmilsecond = parseInt(nextTrigerTime) * 60;
            var expiredTime = parseInt(currentTime) + parseInt(mininmilsecond);
            ls.set('latlongtimeMain', expiredTime)

            let latlong = data.lat + ',' + data.longi
            var encodedData = btoa(latlong)
            WSC.UserLatLong.setLatLONG(encodedData);
            ls.set('encodedLatLong', encodedData)
        }

        //    let nextTrigerTime = Utilities.getMasterData().bs_tm ? Utilities.getMasterData().bs_tm :0
        //    var today = Math.round((new Date()).getTime() / 1000);
        //    var mininmilsecond = parseInt(nextTrigerTime) * 60;
        //    console.log('nextTrigerTime',nextTrigerTime);
        //    console.log('mininmilsecond',mininmilsecond);

        //    console.log('today',today);

        //    console.log('expiredTime',parseInt(today) + parseInt(mininmilsecond));

        //   ls.set('latlongtime', Utilities.getUtcToLocal);
    }

    web_loaded() {
        let webL = {
            action: 'web_loaded',
            targetFunc: 'web_loaded'
        }
        sendMessageToApp(webL)
    }

    appLoader = () => {
        if (WSManager.getIsIOSApp()) {
            return (
                <CustomLoader />
            )
        } else {
            return null
        }
    }


    render() {
        const {
            AppDataPosting,
        } = this.state;

        return (
            <MyContext.Provider>
                <CustomToast style={{ zIndex: 999999 }} />
                {
                    !AppDataPosting ? 
                    <></>
                    :
                    <>
                        {
                            (AppDataPosting)
                                ? <>{this.props.children}
                                    <Routing {...this.props}/>
                                </>
                                // ? ''
                                : window.ReactNativeWebView ? this.appLoader()
                                    : <div className={"web-container white-bg"}><CustomLoader /></div>
                        }
                    </>
                }
            </MyContext.Provider>
        )
    }
}
const MyProviderWrap = withTranslation()(withGeoFencing(MyProvider))
export default withRedux(MyProviderWrap)
