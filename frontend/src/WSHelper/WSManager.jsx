import ReactGA from 'react-ga';
import cookie from 'react-cookies';
import * as WSC from "./WSConstants";
import ls from 'local-storage';
import app_config from "../InitialSetup/AppConfig";
import { Utilities, _isUndefined, getImageBaseUrl, _mergeWith, _isNull } from "../Utilities/Utilities";
import { setValue } from '../helper/Constants';
import firebase from "firebase";
import { getAllStates } from './WSCallings';
import store from '../ReduxLib/store';
import {Actions} from '../ReduxLib/reducers';

require('es6-promise').polyfill();
require('isomorphic-fetch');
var md5 = require('md5');

const setApiCache = (url, param, responseJson) => {
    if (process.env.NODE_ENV === 'development' && ls.get('iscapi')) {
        sessionStorage.setItem((url + '?' + btoa(JSON.stringify(param))), JSON.stringify(responseJson));
    }
}

const getApiCache = (url, param) => {
    const cachedResponse = sessionStorage.getItem((url + '?' + btoa(JSON.stringify(param))));
    if (cachedResponse && process.env.NODE_ENV === 'development' && ls.get('iscapi')) {
        return new Promise((resolve) => {
            resolve(JSON.parse(cachedResponse))
        })
    }
    return false
}

export default class WSManager {
    constructor() {
        this.getToken = this.getToken.bind(this)
    }

    static manageSecurityData = (param) => {
        if (process.env.REACT_APP_KEY_F1 == undefined || process.env.REACT_APP_KEY_F1 == '' || process.env.REACT_APP_KEY_F2 == undefined || process.env.REACT_APP_KEY_F2 == '' || process.env.REACT_APP_PUBLIC_KEY == undefined || process.env.REACT_APP_PUBLIC_KEY == '') {
            return true
        }
        let keyFormat = process.env.REACT_APP_KEY_F1 + process.env.REACT_APP_KEY_F2;
        let strParamsL = JSON.stringify(param || {}).length
        var hostName = window.location.host;
        let UtcDate = new Date().toUTCString()
        let timeStamp = new Date(UtcDate).getTime() / 1000
        let dataObj = {
            KEY: process.env.REACT_APP_PUBLIC_KEY,
            TIMESTAMP: timeStamp,
            REFID: Utilities.getRandomRefrenceId(),
            CONTENT: strParamsL,
            LANGUAGE: this.getAppLang(),
            ORIGIN: window.location.protocol + '//' + hostName,
            UTCDATE: UtcDate,

        }

        let token = '';
        let spliKeys = keyFormat.split("_");
        spliKeys.map((item, idx) => { // item is key name
            //token = token + dataObj[item] + (spliKeys.length >= idx ? '' : '_' )
            token = token + (idx == 0 ? '' : '_') + dataObj[item]
        });
        //  console.log("token===>", token)

        //  console.log("token===>", md5(token))
        let md5Token = md5(token)
        let subC = strParamsL % 2 == 1 ? 13 : 14
        var firstPart = md5Token.substring(0, subC); // seperates string from 16 characters 
        var secondPart = md5Token.substring(subC, md5Token.length);
        //  console.log("firstPart===>", firstPart)
        //  console.log("secondPart===>", secondPart)


        var UserToken = ''; var cookies = '';
        if (strParamsL % 2 == 1) {
            UserToken = firstPart
            cookies = secondPart
        }
        else {
            UserToken = secondPart
            cookies = firstPart
        }
        //cookie.save('_ga_token', cookies)
        //cookie.save('UserRefID',dataObj.REFID)
        let headerObject = {
            UserToken: UserToken,
            Date: dataObj.UTCDATE,
            GA_TOKEN: cookies,
            UserRefID: dataObj.REFID
        }
        return headerObject;
    }

    // API 
    static Rest(url, param, cacheResponse) {
        let LSTStamp = ls.get('GLTStamp') || ''
        let cacheKey = "";
        if (param) {
            cacheKey = (url + JSON.stringify(param));
        }
        else {
            cacheKey = url + "";
        }

        if (cacheResponse) {
            let response = ls.get(cacheKey.toString())
            if (response)
                cacheResponse(response);
        }

        let apiHeader = {
            'Accept-Language': this.getAppLang(),
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/json;charset=UTF-8',
            'Sessionkey': this.getToken() || this.getTempToken() || "",
            'Device': window.ReactNativeWebView ? WSManager.getIsIOSApp() ? 'ios' : 'android' : 'web',
            // 'loc_check': 1
        };

        if (param && param.apiversion) {
            apiHeader['Apiversion'] = param.apiversion;
        }
        if (Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == 1) {

            apiHeader['Ult'] = WSC.UserLatLong.getLatLong() ? WSC.UserLatLong.getLatLong() : ls.get('encodedLatLong') ? ls.get('encodedLatLong') : ''

        }

        if (LSTStamp) {
            let dateTm = Date.now()
            if (dateTm > LSTStamp) {
                ls.remove('GLTStamp')
                let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
                var now = new Date(Date.now() + (bs_tm * 60 * 1000));
                ls.set('GLTStamp', Date.parse(now))
                apiHeader['loc_check'] = 1
            }
        }
        else {
            let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
            var now = new Date(Date.now() + (bs_tm * 60 * 1000));
            ls.set('GLTStamp', Date.parse(now))
        }



        let HeaderObject = this.manageSecurityData(param)
        if (HeaderObject) {
            apiHeader['User-Token'] = HeaderObject.UserToken;
            apiHeader['RequestTime'] = HeaderObject.Date;
            apiHeader['_ga_token'] = HeaderObject.GA_TOKEN;
            apiHeader['X-RefID'] = HeaderObject.UserRefID


        }


        if (url && url.includes('join_game')) {
            window.posting = true;
        }
        if (getApiCache(url, param) != false) return getApiCache(url, param)
        return fetch(url, {
            method: 'POST',
            headers: apiHeader,
            body: JSON.stringify(param)
        })
            .then((response) => {
                let b_s = (response.headers && response.headers.get('banned_cs')) ? response.headers.get('banned_cs').split('_') : '';
                Utilities.geoLocationChanges(b_s)

                return response.json()
            })
            .then(responseJson => {
                window.posting = false;
                setApiCache(url, param, responseJson)
                if (responseJson.response_code != WSC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error
                    var toastIcon = 'icon-ic-info';
                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                            if (key === "phone_no") {
                                toastIcon = getImageBaseUrl('mobile-icon.svg');
                            } else if (key === "user_name" || key === "first_name" || key === "gender" || key === "dob") {
                                toastIcon = 'icon-user';
                            } else if (key === "email") {
                                toastIcon = getImageBaseUrl('email-icon.svg');
                            } else if (key === "ifsc_code") {
                                toastIcon = getImageBaseUrl('bank-icon.svg');
                            }
                        }
                    }
                    // if (responseJson.response_code == WSC.inQueueCode) {
                    //     let message = btoa(errorMsg || "Sorry!! You are in queue, Please try again...")
                    //     let time = btoa(responseJson.data || 5)
                    //     window.location.assign('/you-are-in-queue/?yqmsg=' + message + '&yqtm=' + time);
                    // }
                    // if (responseJson.response_code == WSC.BannedStateCode) {
                    //     let message = btoa(errorMsg || "Sorry!! You are in queue, Please try again...")
                    //     let time = btoa(responseJson.data || 5)

                    //     window.location.assign('/you-are-in-queue/?yqmsg=' + message + '&yqtm=' + time);
                    // }

                    else if (responseJson.response_code == WSC.sessionExpireCode) {
                        Utilities.showToast(responseJson.global_error, 5000);
                        this.logout();
                    } else {
                        if (errorMsg) {
                            // Utilities.showToast(errorMsg, 5000, toastIcon);
                        }
                    }
                }

                if (cacheResponse) {
                    ls.set(cacheKey, JSON.stringify(responseJson));
                }


                return responseJson;
            })
            .catch((error) => {
                window.posting = false;
                console.error(error);
                return {};
            });

    }


    static RestGet(url, param) {

        return fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json;charset=UTF-8',
                Sessionkey: this.getToken() || ""
            }
            // param: JSON.stringify(param)
        })
            .then((response) => {
                let b_s = (response.headers && response.headers.get('banned_cs')) ? response.headers.get('banned_cs').split('_') : '';
                Utilities.geoLocationChanges(b_s)

                response.json()
            })
            .then(responseJson => {
                // console.log('URL- ' + url + '\n\nParameters: - ' + param, '\n\nResponse: - ', responseJson);
                if (responseJson.response_code != WSC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == WSC.sessionExpireCode) {

                    } else {
                        Utilities.showToast(errorMsg, 5000);
                        // notify.show(errorMsg, "error", 5000);
                    }
                }
                return responseJson;
            })
            .catch((error) => {
                console.error(error);
            });

    }

    static RestS3ApiCall = async (s3_api_data_url, api_url, param) => {
        let LSTStamp = ls.get('GLTStamp') || ''

        try {
            var response = {};
            response.ok = false;
            if (app_config.s3.BUCKET_STATIC_DATA_ALLOWED && s3_api_data_url) {

                let tmpArray = s3_api_data_url.split('/');
                let splitURL = tmpArray.length > 0 ? tmpArray[tmpArray.length - 1] : api_url;

                let S3ApiLastTime = WSManager.getS3LastTime();
                let oldDateData = S3ApiLastTime[splitURL];
                let timeStamp = ''
                let LSTStamp = ls.get('GLTStamp') || ''

                if (!oldDateData || Utilities.minuteDiffValue(oldDateData) > 1 || api_url === WSC.GET_CONTEST_LEADERBOARD
                    || api_url === WSC.GET_CONTEST_DETAIL || api_url === WSC.GET_FIXTURE_DETAIL
                    || api_url === WSC.GET_USER_LINEUP_LIST || api_url === WSC.GET_FIXTURE_DETAIL || api_url === WSC.GET_ALL_ROSTER) {
                    timeStamp = "?" + Date.now();
                    S3ApiLastTime[splitURL] = { date: Date.now() };
                    WSManager.setS3LastTime(S3ApiLastTime);
                }


                let apiHeader = {
                    'Accept-Language': localStorage.getItem('i18nextLng'),
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'text/plain',
                    'Access-Control-Allow-Methods': 'PUT, POST, DELETE, GET',
                    'Access-Control-Allow-Origin': '*',
                    'Device': window.ReactNativeWebView ? WSManager.getIsIOSApp() ? 'ios' : 'android' : 'web',
                    // 'loc_check': 1

                };
                let HeaderObject = this.manageSecurityData(param)
                if (HeaderObject) {
                    apiHeader['User-Token'] = HeaderObject.UserToken;
                    apiHeader['RequestTime'] = HeaderObject.Date;
                    apiHeader['_ga_token'] = HeaderObject.GA_TOKEN;
                    apiHeader['X-RefID'] = HeaderObject.UserRefID
                }
                if (Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == 1) {

                    apiHeader['Ult'] = WSC.UserLatLong.getLatLong() ? WSC.UserLatLong.getLatLong() : ls.get('encodedLatLong') ? ls.get('encodedLatLong') : ''

                }
                if (LSTStamp) {
                    let dateTm = Date.now()
                    if (dateTm > LSTStamp) {
                        ls.remove('GLTStamp')
                        let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
                        var now = new Date(Date.now() + (bs_tm * 60 * 1000));
                        ls.set('GLTStamp', Date.parse(now))
                        apiHeader['loc_check'] = 1
                    }
                }
                else {
                    let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
                    var now = new Date(Date.now() + (bs_tm * 60 * 1000));
                    ls.set('GLTStamp', Date.parse(now))
                }

                response = await fetch(s3_api_data_url + timeStamp, {
                    mode: 'cors',
                    method: 'GET',
                    headers: apiHeader
                })

            }


            if (response.ok == true) {
                let b_s = (response.headers && response.headers.get('banned_cs')) ? response.headers.get('banned_cs').split('_') : '';
                Utilities.geoLocationChanges(b_s)
                const responseJson = await response.json();
                return responseJson;
            }
            else {
                var actualBaseURL = WSC.baseURL
                // let bs_tm = Utilities.getMasterData() ? parseInt(Utilities.getMasterData().bs_tm) : ''
                // let dateTm = parseInt(Date.now())
                // let sumTm = bs_tm + dateTm
                let LSTStamp = ls.get('GLTStamp') || ''
                if (api_url.startsWith("user/")) {
                    actualBaseURL = WSC.userURL;
                }
                if (api_url.startsWith("fantasy/")) {
                    actualBaseURL = WSC.fantasyURL;
                }
                let apiHeader = {
                    'Accept-Language': localStorage.getItem('i18nextLng'),
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json;charset=UTF-8',
                    'Sessionkey': WSManager.getToken() || WSManager.getTempToken() || "",
                    'Device': window.ReactNativeWebView ? WSManager.getIsIOSApp() ? 'ios' : 'android' : 'web',
                    // 'loc_check': 1
                };
                let HeaderObject = this.manageSecurityData(param)
                if (HeaderObject) {
                    apiHeader['User-Token'] = HeaderObject.UserToken;
                    apiHeader['RequestTime'] = HeaderObject.Date;
                    apiHeader['_ga_token'] = HeaderObject.GA_TOKEN;
                    apiHeader['X-RefID'] = HeaderObject.UserRefID
                }
                if (Utilities.getMasterData().bs_a && Utilities.getMasterData().bs_a == 1) {
                    apiHeader['Ult'] = WSC.UserLatLong.getLatLong() ? WSC.UserLatLong.getLatLong() : ls.get('encodedLatLong') ? ls.get('encodedLatLong') : ''

                }
                if (LSTStamp) {
                    let dateTm = Date.now()
                    if (dateTm > LSTStamp) {
                        ls.remove('GLTStamp')
                        let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
                        var now = new Date(Date.now() + (bs_tm * 60 * 1000));
                        ls.set('GLTStamp', Date.parse(now))
                        apiHeader['loc_check'] = 1
                    }
                }
                else {
                    let bs_tm = Utilities.getMasterData() ? Number(Utilities.getMasterData().bs_tm) : ''
                    var now = new Date(Date.now() + (bs_tm * 60 * 1000));
                    ls.set('GLTStamp', Date.parse(now))
                }
                if (getApiCache(api_url, param) != false) return getApiCache(api_url, param)
                const response = await fetch(actualBaseURL + api_url, {
                    method: 'POST',
                    headers: apiHeader,
                    body: JSON.stringify(param)
                });
                if (response.status != WSC.successCode) {
                    var errorMsg = response.message != '' ? response.message : response.global_error

                    if (errorMsg == '') {
                        for (var key in response.error) {
                            errorMsg = response.error[key];
                        }
                    }
                    // if (response.status == WSC.inQueueCode) {
                    //     const responseJson = await response.json();
                    //     let message = btoa(errorMsg || "Sorry!! You are in queue, Please try again...")
                    //     let time = btoa(responseJson.data || 5)
                    //     window.location.assign('/you-are-in-queue/?yqmsg=' + message + '&yqtm=' + time);
                    // }
                    // if (response.status == WSC.BannedStateCode) {
                    //     const responseJson = await response.json();
                    //     let message = btoa(errorMsg || "Sorry!! You are in queue, Please try again...")
                    //     let time = btoa(responseJson.data || 5)
                    //     window.location.assign('/you-are-in-queue/?yqmsg=' + message + '&yqtm=' + time);
                    // }


                    else if (response.status == WSC.sessionExpireCode) {
                        Utilities.showToast(response.global_error, 5000);
                        this.logout();
                    } else {
                        if (errorMsg) {
                            Utilities.showToast(errorMsg, 5000);
                        }
                    }
                } else {

                    const responseJson = await response.json();
                    setApiCache(api_url, param, responseJson.data)
                    return responseJson.data;
                }
            }
        }
        catch (error) {
            console.error(error);
        }
    }

    // Check Auth

    static loggedIn() {
        return localStorage.getItem('id_token') != null;
        // return false;
    }

    static clearLineup() {
        ls.remove('home_player_count');
        ls.remove('away_player_count');
        ls.remove('Lineup_data')
    }

    static logout() {
        let singularData = {};
        if (process.env.REACT_APP_SINGULAR_ENABLE == 1) {
            for (var key in localStorage) {
                if (key.includes("singular") || key.includes(process.env.REACT_APP_SINGULAR_API)) {
                    singularData[key] = localStorage[key];
                }
            }
        }

        let def_lang = this.getAppLang()
        if (window.ReactNativeWebView) {
            let data = {
                action: 'back',
                locale: def_lang,
                targetFunc: 'handleLogoutReceived'
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
        // Utilities.setLocationStatusToApp()
        let admin_id_token = localStorage.getItem('admin_id_token');
        let ALLOW_COIN_MODULE = localStorage.getItem('ALLOW_COIN_MODULE');
        let LF_PRIVATE_CONTEST = localStorage.getItem('LF_PRIVATE_CONTEST');
        let module_access = localStorage.getItem('module_access');
        let role = localStorage.getItem('role');
        let defSports = ls.get('selectedSports');
        let defPFSports = ls.get('PFSSport');
        let gameType = WSManager.getPickedGameType();
        let xpModal = ls.get('xpModal');
        let guruFiledViewCheck = ls.get('guruFiledViewCheck');
        let guruRosterCheck = ls.get('guruRosterCheck');
        let profileData = ls.get('profile')
        let SHGT = localStorage.getItem('SHGT')
        sessionStorage.clear();
        localStorage.clear();
        this.removeCookie('_id');
        this.removeCookie('_nm');
        ls.clear();
        this.setAppLang(def_lang);
        ls.set('selectedSports', defSports);
        ls.set('PFSSport', defPFSports);
        ls.set('xpModal', xpModal);
        ls.set('guruFiledViewCheck', guruFiledViewCheck);
        ls.set('guruRosterCheck', guruRosterCheck);
        ls.set('user_id', profileData && profileData.user_unique_id);
        ls.set('SHGT', SHGT);
        if (admin_id_token) {
            localStorage.setItem('admin_id_token', admin_id_token);
        }
        if (ALLOW_COIN_MODULE) {
            localStorage.setItem('ALLOW_COIN_MODULE', ALLOW_COIN_MODULE);
        }
        if (LF_PRIVATE_CONTEST) {
            localStorage.setItem('LF_PRIVATE_CONTEST', LF_PRIVATE_CONTEST);
        }
        if (module_access) {
            localStorage.setItem('module_access', module_access);
        }
        if (role) {
            localStorage.setItem('role', role);
        }
        setValue.setAppSelectedSport(defSports);
        if (gameType) {
            WSManager.setPickedGameType(gameType);
        }
        if (process.env.REACT_APP_SINGULAR_ENABLE == 1) {
            for (var key in singularData) {
                if (key.includes("singular") || key.includes(process.env.REACT_APP_SINGULAR_API)) {
                    localStorage.setItem(key, singularData[key]);
                }
            }
        }

        setTimeout(() => {
            if (navigator.userAgent.match(/(Mobile)/) || navigator.userAgent.match(/(mobile)/) || window.ReactNativeWebView) {
                window.location.assign('/signup');
            }
            else {
                window.location.assign('/');
            }
        }, 300);
    }

    static setIsMobileApp(isMobileApp) {
        // Saves isMobileApp data to localStorage
        localStorage.setItem('isMobileApp', isMobileApp)
    }
    static getIsMobileApp() {
        // Get isMobileApp data to localStorage
        const isMobileApp = localStorage.getItem('isMobileApp')
        return isMobileApp ? JSON.parse(localStorage.isMobileApp) : false
    }
    static setIsIOSApp(isIOSApp) {
        // Saves isIOSApp data to localStorage
        setValue.setAllowRedeem(isIOSApp ? false : true)
        localStorage.setItem('isIOSApp', isIOSApp)
    }
    static getIsIOSApp() {
        // Get isMobileApp data to localStorage
        const isIOSApp = localStorage.getItem('isIOSApp')
        return isIOSApp ? JSON.parse(localStorage.isIOSApp) : false
    }

    static setProfile(profile) {
        // Saves profile data to localStorage
        try {
            if (window.ReactNativeWebView) {
                const gprofile = this.getProfile();
                const tmpProfile = _mergeWith({}, gprofile, profile, (o, s) => _isNull(s) ? o : s)
                localStorage.setItem('referral_code', tmpProfile.referral_code)
                localStorage.setItem('profile', JSON.stringify(tmpProfile))

            } else {
                localStorage.setItem('referral_code', profile.referral_code)
                localStorage.setItem('profile', JSON.stringify(profile))
            }
        }
        catch (error) {
            localStorage.setItem('referral_code', profile.referral_code)
        }
    }


    static getProfile() {
        // Retrieves the profile data from localStorage
        const profile = localStorage.getItem('profile')
        return profile ? JSON.parse(localStorage.profile) : {}
    }

    static updateProfile(aadharData) {
        // Retrieves the profile data from localStorage
        let updatedProfile = localStorage.getItem('profile')
        updatedProfile = JSON.parse(updatedProfile)
        updatedProfile['aadhar_status'] = aadharData.aadhar_status
        updatedProfile['aadhar_detail']['aadhar_id'] = aadharData.aadhar_id
        return localStorage.setItem('profile', JSON.stringify(updatedProfile))
    }

    static getUserReferralCode() {
        return localStorage.getItem('referral_code')
    }

    static setToken(idToken) {
        // Saves user token to localStorage
        localStorage.setItem('id_token', idToken);
    }

    static getToken = () => {
        // Retrieves the user token from localStorage
        // return '7864ef5e61cbb021e15c9b6aed396250';
        return localStorage.getItem('id_token')
    }

    static setTempToken(idToken) {
        // Saves user token to localStorage
        localStorage.setItem('id_temp_token', idToken);
    }

    static getTempToken() {
        // Retrieves the user token from localStorage
        // return '7864ef5e61cbb021e15c9b6aed396250';
        return localStorage.getItem('id_temp_token')
    }

    static setBalance(userBalance) {
        // Saves UserBalance data to localStorage
        localStorage.setItem('userBalance', JSON.stringify(userBalance))
    }

    static getBalance() {
        // Retrieves the UserBalance data from localStorage
        const userBalance = localStorage.getItem('userBalance')
        return userBalance ? JSON.parse(localStorage.userBalance) : {}
    }

    static setAllowedBonusPercantage(allowed_bonus_percantage) {
        // Saves allowed_bonus_percantage data to localStorage
        localStorage.setItem('allowed_bonus_percantage', allowed_bonus_percantage)
    }

    static getAllowedBonusPercantage() {
        // Retrieves the allowed_bonus_percantage data from localStorage
        return localStorage.getItem('allowed_bonus_percantage')
    }


    static setContestFromAddFundsAndJoin(contestData) {
        // Saves Data of Contest from ConfirmationModal to localStorage
        localStorage.setItem('contestData', JSON.stringify(contestData))
    }

    static getContestFromAddFundsAndJoin() {
        // Retrieves Data of Contest from ConfirmationModal to localStorage
        const contestData = localStorage.getItem('contestData')
        return contestData ? JSON.parse(localStorage.contestData) : {}
    }

    static setContestFromAddCoinAndJoin(contestCoinData) {
        // Saves Data of Contest from ConfirmationModal to localStorage
        localStorage.setItem('contestCoinData', JSON.stringify(contestCoinData))
    }

    static getContestFromAddCoinAndJoin() {
        // Retrieves Data of Contest from ConfirmationModal to localStorage
        const contestCoinData = localStorage.getItem('contestCoinData')
        return contestCoinData ? JSON.parse(localStorage.contestCoinData) : {}
    }


    static setFromFundsOnly(isAddFundsClicked) {
        // Saves boolean value that user comes from Add Funds
        localStorage.setItem('isAddFundsClicked', isAddFundsClicked)
    }

    static getFromFundsOnly() {
        // Saves boolean value that user comes from Add Funds
        return localStorage.getItem('isAddFundsClicked') != null && !_isUndefined(localStorage.getItem('isAddFundsClicked')) ? localStorage.getItem('isAddFundsClicked') : false
    }

    static setPaymentCalledFrom(PaymentCalledFrom) {
        // Saves name of class from which Funds Are Added 
        localStorage.setItem('PaymentCalledFrom', PaymentCalledFrom);
    }
    static getPaymentCalledFrom() {
        // Retrieves name of class from which Funds Are Added 
        return localStorage.getItem('PaymentCalledFrom')
    }
    static setIsFromPayment(status) {
        // Saves name of class from which Funds Are Added 
        localStorage.setItem('status', status);
    }
    static getIsFromPayment() {
        // Retrieves name of class from which Funds Are Added 
        return localStorage.getItem('status')
    }

    static setFromConfirmPopupAddFunds(flag) {
        localStorage.setItem('from_confirm_popup_add_funds', flag);
    }

    static getFromConfirmPopupAddFunds() {
        return localStorage.getItem('from_confirm_popup_add_funds')
    }

    static setReferralCode(referralCode) {
        // Saves the refferal code
        localStorage.setItem('referralCode', referralCode);
    }
    static getReferralCode() {
        // Retrieves the refferal code 
        return localStorage.getItem('referralCode')
    }
    static setAffiliatCode(affCode) {
        localStorage.setItem('affcd', affCode);
    }
    static getAffiliatCode() {
        return localStorage.getItem('affcd')
    }
    static setStockSetting(stockset) {
        localStorage.setItem('stockset', JSON.stringify(stockset));
    }
    static getStockSetting() {
        let stockset = localStorage.getItem('stockset')
        return JSON.parse(stockset)
    }

    static setAflcCode(cp) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for (var i = 0; i < 8; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }
        this.getAflcCode().then(res => {
            if (res == null || (res && res.campaign_code != cp)) {
                localStorage.removeItem('is_cpvisit');
                localStorage.setItem('cp', JSON.stringify({
                    campaign_code: cp,
                    visit_code: text
                }));
            }
        }).catch(err => {
            console.log(err);
        })
    }

    static getAflcCode(is_direct = false) {
        const cp = localStorage.getItem('cp')
        if (is_direct) {
            if (!cp) {
                return (null)
            }
            const _cp = JSON.parse(cp)
            return _cp
        } else {
            return new Promise((resolve) => {
                if (!cp) {
                    resolve(null)
                }
                const _cp = JSON.parse(cp)
                resolve(_cp)
            })
        }
    }

    static googleTrack(profileId, action) {
        let rcuid = localStorage.getItem('rcuid');
        ReactGA.initialize(WSC.GA_PROFILE_ID);
        ReactGA.event({
            category: rcuid ? rcuid : 'nocampaign',
            action: action
        });
    }

    static googleTrackDaily(profileId, action) {
        var userAnalytic = cookie.load('userAnalytic');
        if (!userAnalytic) {
            let rcuid = localStorage.getItem('rcuid');
            ReactGA.initialize(WSC.GA_PROFILE_ID);
            ReactGA.event({
                category: rcuid ? rcuid : 'nocampaign',
                action: action
            });
            var today = new Date();
            var userAnalytic = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
            var date = new Date();
            var midnight = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 29, 29, 59);
            cookie.save('userAnalytic', userAnalytic, { path: '/', expires: midnight })
        }
    }


    static setAppLang(lang) {
        // Saves the app language
        localStorage.setItem('i18nextLng', lang);
        this.setCookie('i18nextLng', lang)
    }
    static getAppLang() {
        // Retrieves the app language  
        let lsLang = localStorage.getItem('i18nextLng');
        let lang = lsLang ? lsLang.split('-') : ['en'];
        if (lang.length > 1) {
            this.setAppLang(lang[0])
        }
        return lang[0];
    }

    static setS3LastTime(data) {
        let strObj = JSON.stringify(data);
        localStorage.setItem('S3LTAC', btoa(strObj));
    }
    static getS3LastTime() {
        let obj = localStorage.getItem('S3LTAC');
        return obj ? JSON.parse(atob(obj)) : {};
    }
    static setBannerData(data) {
        let strObj = JSON.stringify(data);
        localStorage.setItem('abdata', btoa(strObj));
    }
    static getBannerData() {
        let obj = localStorage.getItem('abdata');
        return obj && obj != '[object Object]' ? JSON.parse(atob(obj)) : {};
    }
    static setShareContestJoin(data) {
        let strObj = JSON.stringify(data);
        localStorage.setItem('SCJF', btoa(strObj));
    }
    static getShareContestJoin() {
        let obj = localStorage.getItem('SCJF');
        return obj ? JSON.parse(atob(obj)) : null;
    }

    static setDailyData(data) {
        let todayString = new Date().toDateString();
        data['day_string'] = todayString;
        let strObj = JSON.stringify(data);
        localStorage.setItem('DSBD', btoa(strObj));
    }
    static getDailyData() {
        let obj = localStorage.getItem('DSBD');
        return obj ? JSON.parse(atob(obj)) : {};
    }
    static setWheelData(data) {
        let strObj = JSON.stringify(data);
        localStorage.setItem('S2WW', btoa(strObj));
    }
    static getWheelData() {
        let obj = localStorage.getItem('S2WW');
        return obj ? JSON.parse(atob(obj)) : {};
    }
    static setPickedGameType(value) {
        ls.remove('SHActive')

        setValue.setSelectedGameType(value);
        store.dispatch(Actions.gameTypeHandler(value));
        let strObj = JSON.stringify(value);
        localStorage.setItem('SHGT', btoa(strObj));
    }
    static getPickedGameType() {
        let obj = localStorage.getItem('SHGT');
        store.dispatch(Actions.gameTypeHandler(obj ? JSON.parse(atob(obj)) : ""));
        return obj ? JSON.parse(atob(obj)) : null;
    }
    static setPickedGameTypeID(value) {
        localStorage.setItem('SHGTID', value);
    }
    static getPickedGameTypeID() {
        let obj = localStorage.getItem('SHGTID');
        return obj || null;
    }

    static removeLSItem(key) {
        localStorage.removeItem(key)
    }

    static setPredictionId(seasonId) {
        localStorage.setItem('seasonId', seasonId);
    }
    static getPredictionId() {
        return localStorage.getItem('seasonId')
    }
    static setDFSTourEnabel(flag) {
        localStorage.setItem('isDFSTourEnable', flag);
    }
    static getDFSTourEnabel() {
        return localStorage.getItem('isDFSTourEnable')
    }
    static updateFirebaseUsers(contest_unique_id, mDeviceIdArr) {
        // let userDeviceTokenList = [];
        // if(mDeviceIdArr && mDeviceIdArr.length>0){
        //     userDeviceTokenList = mDeviceIdArr;
        // }
        // else{
        //     userDeviceTokenList = [WSC.DeviceToken.getDeviceId()];
        // }
        if (!this.groupMembersRef) {
            this.groupMembersRef = firebase
                .database()
                .ref()
                .child("group_members")
                .child(contest_unique_id)
                .child(WSManager.getProfile().user_id);
            var newItem = {
                userName: WSManager.getProfile().user_name,
                userId: WSManager.getProfile().user_id,
                userImage: WSManager.getProfile().image !== '' ? Utilities.getThumbURL(WSManager.getProfile().image) : '',
                date: new Date().toDateString(),
                deviceList: mDeviceIdArr != null ? mDeviceIdArr : [],
                deviceType: window.ReactNativeWebView ? WSC.deviceTypeAndroid : WSC.deviceType,
                muteNotification: "0",
            };
            // this.groupMembersRef.push(newItem);
            this.groupMembersRef.set(newItem);
        }


        //   let mDeviceId = WSC.DeviceToken.getDeviceId();

    }
    // API 
    static setCookie = (cname, cvalue, exdays) => {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    static getCookie = (cname) => {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    static removeCookie = (cname) => {
        document.cookie = cname + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    static setActiveScratch(value) {
        let strObj = JSON.stringify(value);
        localStorage.setItem('actsc', btoa(strObj));
    }
    static getActiveScratch() {
        let obj = localStorage.getItem('actsc');
        return obj ? JSON.parse(atob(obj)) : {};
    }
    static setH2hMessage(value) {
        let strObj = JSON.stringify(value);
        localStorage.setItem('h2hM', btoa(strObj));
    }
    static getH2hMessage() {
        let obj = localStorage.getItem('h2hM');
        return obj ? JSON.parse(atob(obj)) : {};
    }
    static multipartPost(url, body = {}) {
        const auth = this.getToken();
        const token = (!!auth && auth) || null;
        const settings =
            token !== null
                ? {
                    method: "POST",
                    body: body,
                    headers: {
                        Sessionkey: token,
                        'Accept': 'application/json, text/plain, */*',
                        'role': 2
                        // 'ContentType': 'multipart/form-data',
                        // "Access-Control-Allow-Origin": "*",
                        // "Access-Control-Allow-Headers": "*",
                    }
                }
                : {
                    method: "POST",
                    body: body,
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        // 'ContentType': 'multipart/form-data',
                        // "Access-Control-Allow-Origin": "*",
                        // "Access-Control-Allow-Headers": "*",
                    }
                };

        return fetch(url, settings)
            .then((response) => {
                return response.json()
            })
            .then(responseJson => {
                if (responseJson.response_code != WSC.successCode) {
                    var errorMsg = responseJson.message != '' ? responseJson.message : responseJson.global_error

                    if (errorMsg == '') {
                        for (var key in responseJson.error) {
                            errorMsg = responseJson.error[key];
                        }
                    }
                    if (responseJson.response_code == WSC.sessionExpireCode) {
                    } else {
                        Utilities.showToast(errorMsg, "error", 5000);
                    }
                }
                return responseJson;
            })
    }

    static getLS = (key) => {
        const _key = localStorage.getItem(key);
        if (!_key) {
          return null;
        }
        return JSON.parse(_key);
    }
    
}
