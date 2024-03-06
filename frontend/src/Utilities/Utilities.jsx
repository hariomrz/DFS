import React from "react";
import WSManager from "../WSHelper/WSManager";
import moment from 'moment';
import { parsePhoneNumberFromString } from 'libphonenumber-js';
import ls from 'local-storage';
import { Sports } from "../JsonFiles";
import { AppSelectedSport, setValue, DASHBOARD_FOOTER, TOAST, SELECTED_GAMET, GameType, BanStateEnabled, PFSelectedSport, PFDefaultSport } from "../helper/Constants";
import AppConfig from "../InitialSetup/AppConfig";
import MetaData from "../helper/MetaData";
import _Map from 'lodash/map';
import _isNull from 'lodash/isNull';
import _isEmpty from 'lodash/isEmpty';
import _isUndefined from 'lodash/isUndefined';
import _cloneDeep from 'lodash/cloneDeep';
import _findIndex from 'lodash/findIndex';
import _filter from 'lodash/filter';
import _indexOf from 'lodash/indexOf';
import _sumBy from 'lodash/sumBy';
import _debounce from 'lodash/debounce';
import _times from 'lodash/times';
import _mergeWith from 'lodash/mergeWith';
import _isObject from 'lodash/isObject';
import _includes from 'lodash/includes';
import _uniqBy from 'lodash/uniqBy';
import _chain from 'lodash/chain';
import _sortBy from 'lodash/sortBy';
import _omit from 'lodash/omit';
import _invert from 'lodash/invert';
import _chunk from 'lodash/chunk';
import _reduce from 'lodash/reduce';
import * as WSC from "../WSHelper/WSConstants";
import * as Constants from "../helper/Constants";
import * as AppLabels from "../helper/AppLabels";
import { CommonLabels } from "../helper/AppLabels";
import { withRouter } from "react-router-dom";
import CountdownTimer from '../views/CountDownTimer';

import store from 'ReduxLib/store';
import {Actions} from 'ReduxLib/reducers';

import _orderBy from 'lodash/orderBy';
export {
    _Map,
    _isNull,
    _isEmpty,
    _isUndefined,
    _cloneDeep,
    _findIndex,
    _filter,
    _indexOf,
    _sumBy,
    _debounce,
    _times,
    _mergeWith,
    _isObject,
    withRouter,
    _includes,
    _uniqBy,
    _chain,
    _sortBy,
    _omit,
    _invert,
    _chunk,
    _reduce,
    _orderBy
};

export var APP_MASTER_DATA = '';
export var PROPS_IDS = {};
export var ANDROID_APP_INSTALLED_VERSION = '';// Used for android app version to be display on more screen
export var PROPS_PAYOUT_MDT = {};

class Utilities {

    static teamFlagURL(flag) {
        return (flag || '').includes('http') ? flag : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.FLAG + (flag || 'flag_default.jpg');
    }
    static playerJersyURL(jersy) {
        return (jersy || '').includes('http') ? jersy : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.JERSY + jersy;
    }
    static getThumbURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.THUMB + file;
    }
    static getPanURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PAN + file;
    }
    static aadharURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.AADHAR + file;
    }
    static getUploadURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.UPLOAD + file;
    }
    static getBankURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.BANK + file;
    }
    static getBannerURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.BANNER + file;
    }
    static getAppBannerURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.APPBANNER + file;
    }
    static getRewardsURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.REWARDS + file;
    }
    static getS3URL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.S3ASSETS + file;
    }
    static getBadgeURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.BADGES + file;
    }
    static getMerchandiseURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.MERCHANDISE + file;
    }
    static getSponserURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.SPONSER + file;
    }
    static getCategoryURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.CATEGORY + file;
    }
    static getOpenPredURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.OPENPRED + file;
    }
    static getOpenPredFPPURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.OPENPREDFPP + file;
    }
    static getCMSURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.CMS + file;
    }

    static getSettingURL(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.SETTING + file;
    }
    static getPickemTourLogo(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PICKEM_TOUR_LOGO + file;
    }
    static getPickemTourSponsor(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PICKEM_TOUR_SPONSOR + file;
    }
    static getPickemTeamFlag(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PICKEM_TEAM_FLAG + file;
    }
    static getDFSTourLogo(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.DFS_TOUR_LOGO + file;
    }
    static getDFSTourSponsor(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.DFS_TOUR_SPONSOR + file;
    }
    static getStockLogo(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.STOCK_LOGO + file;
    }
    static getBoosterLogo(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.BOOSTER_LOGO + file;
    }
    static getH2HLogo(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.H2H + file;
    }
    static getDFSTour(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.DFSTOUR + file;
    }
    static getPickemTour(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PICKEMTOUR + file;
    }
    static getWhatsNew(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.WHATSNEW + file;
    }
    static getQuizImg(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.GETQUIZIMG + file;
    }
    static getPickImg(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PICKS + file;
    }
    static getPaymentImg(file) {
        return (file || '').includes('http') ? file : AppConfig.s3.BUCKET + AppConfig.s3.S3_FOLDER.PAYMENTSIMG + file;
    }

    static getTotalUserBalance(mBonusAmount, mRealAmount, mWinningAmount) {
        var bonusAmount = mBonusAmount;
        var realAmount = mRealAmount;
        var winningAmount = mWinningAmount;
        var totalUserBalance = parseFloat(bonusAmount) + parseFloat(realAmount) + parseFloat(winningAmount);
        return totalUserBalance
    }

    static getExactValue = (pPer) => {
        let num = pPer && pPer.toString(); //If it's not already a String
        if (num && num.includes('.')) {
            num = num.slice(0, (num.indexOf(".")) + 2); //With 3 exposing the hundredths place

        }
        //Number(num); //If you need it back as a Number
        if (num != undefined) {

            return num

        }
    }
    static getExactValueSP = (pPer) => {
        let num = pPer && pPer.toString(); //If it's not already a String
        if (num && num.includes('.')) {
            num = num.slice(0, (num.indexOf(".")) + 3); //With 3 exposing the hundredths place

        }
        //Number(num); //If you need it back as a Number
        if (num != undefined) {

            return num

        }
    }

    static subNumber = (b, c) => {

        let b1 = b.toString().split(".")
        let b1_max = 0
        if (b1.length == 2) {
            b1_max = b1[1].length
        }

        let c1 = c.toString().split(".")
        let c1_max = 0
        if (c1.length == 2) {
            c1_max = c1[1].length
        }

        let max_len = b1_max > c1_max ? b1_max : c1_max

        return Number((b - c).toFixed(max_len))

    }
    static addNumber = (b, c) => {

        let b1 = b.toString().split(".")
        let b1_max = 0
        if (b1.length == 2) {
            b1_max = b1[1].length
        }

        let c1 = c.toString().split(".")
        let c1_max = 0
        if (c1.length == 2) {
            c1_max = c1[1].length
        }

        let max_len = b1_max > c1_max ? b1_max : c1_max

        return Number((b + c).toFixed(max_len))

    }

    static getBalanceAccToMaxPercentOfEntryFee(mEntryFee) {
        var UserBalance = WSManager.getBalance();
        var allowedBonusPercantage = WSManager.getAllowedBonusPercantage();
        var bonusAmount = parseFloat(UserBalance.bonus_amount);
        var realAmount = UserBalance.real_amount;
        var winningAmount = UserBalance.winning_amount;
        var depositAndWinning = parseFloat(realAmount) + parseFloat(winningAmount);
        var maxBonusAccToEntryFee = parseFloat(allowedBonusPercantage) * parseFloat(mEntryFee) / 100;
        let master_data = Utilities.getMasterData();
        if (master_data.max_contest_bonus > 0) {
            maxBonusAccToEntryFee = maxBonusAccToEntryFee > master_data.max_contest_bonus ? parseFloat(master_data.max_contest_bonus) : maxBonusAccToEntryFee
        }
        if (bonusAmount > maxBonusAccToEntryFee) {
            return (maxBonusAccToEntryFee + depositAndWinning);
        } else {
            return (bonusAmount + depositAndWinning);
        }
    }
    static callNativeRedirection(sponsorLink, event) {
        event.stopPropagation();

        let data = {
            action: 'sponserLink',
            targetFunc: 'sponserLink',
            type: 'link',
            url: sponsorLink,
            detail: ""
        }
        window.ReactNativeWebView.postMessage(JSON.stringify(data));
    }
    static getValidSponserURL(sponserUrl) {
        let sponserUrlConstant = sponserUrl ? sponserUrl : null
        return (sponserUrlConstant);
    }

    static setLocationStatusToApp() {
        if (window.ReactNativeWebView) {
            let dataLoc = { "bs_a": Utilities.getMasterData().bs_a, "bs_fs": Utilities.getMasterData().bs_fs, "bs_tm": Utilities.getMasterData().bs_tm }
            let data = {
                action: 'location',
                targetFunc: 'location',
                locationData: dataLoc
            }
            sendMessageToApp(data)
        }
    }

    static getBalanceInDetail(mEntryFee) {
        let BalanceDetail = {};
        var UserBalance = WSManager.getBalance();
        var allowedBonusPercantage = WSManager.getAllowedBonusPercantage();
        var bonusAmount = parseFloat(UserBalance.bonus_amount);
        var realAmount = UserBalance.real_amount;
        var winningAmount = UserBalance.winning_amount;
        var maxBonusAccToEntryFee = parseFloat(allowedBonusPercantage) * parseFloat(mEntryFee) / 100;
        let master_data = Utilities.getMasterData();
        if (master_data.max_contest_bonus > 0) {
            maxBonusAccToEntryFee = maxBonusAccToEntryFee > master_data.max_contest_bonus ? master_data.max_contest_bonus : maxBonusAccToEntryFee
        }
        if (bonusAmount > maxBonusAccToEntryFee) {
            BalanceDetail['Bonus'] = maxBonusAccToEntryFee;

        } else {
            BalanceDetail['Bonus'] = bonusAmount;
        }

        var EntryFeeLeft = parseFloat(mEntryFee) - parseFloat(BalanceDetail['Bonus']);
        if (realAmount >= EntryFeeLeft) {
            BalanceDetail['Deposit'] = EntryFeeLeft;
        }
        else {
            BalanceDetail['Deposit'] = realAmount;
            if (winningAmount >= (EntryFeeLeft - realAmount)) {
                BalanceDetail['Winning'] = (EntryFeeLeft - realAmount);
            }
            else {
                BalanceDetail['Winning'] = winningAmount;
            }
        }
        return BalanceDetail;
    }

    static getTotalBalance(data) {
        return (parseFloat(data.winning_amount) + parseFloat(data.cb_balance || 0) + parseFloat(data.bonus_amount) + parseFloat(data.real_amount)).toFixed(2)
    }

    static setDefaultSport() {
        let master_data = Utilities.getMasterData();
        ls.set('selectedSports', AppSelectedSport || master_data.default_sport);
        setValue.setAppSelectedSport(AppSelectedSport || master_data.default_sport);
    }

    // static getMaxBonusAllowedOfEntryFeeContestWise(mEntryFee, maxBonusAllowed) {
    //     var UserBalance = WSManager.getBalance();
    //     var allowedBonusPercantage = maxBonusAllowed;
    //     var bonusAmount = parseFloat(UserBalance.bonus_amount);
    //     var realAmount = UserBalance.real_amount;
    //     var winningAmount = UserBalance.winning_amount;
    //     var depositAndWinning = parseFloat(realAmount) + parseFloat(winningAmount);
    //     var maxBonusAccToEntryFee = parseFloat(allowedBonusPercantage) * parseFloat(mEntryFee) / 100;
    //     let master_data = Utilities.getMasterData();
    //     if (master_data.max_contest_bonus > 0) {
    //         maxBonusAccToEntryFee = maxBonusAccToEntryFee > master_data.max_contest_bonus ? parseFloat(master_data.max_contest_bonus) : maxBonusAccToEntryFee
    //     }
    //     if (bonusAmount > maxBonusAccToEntryFee) {
    //         return (maxBonusAccToEntryFee + depositAndWinning);
    //     } else {
    //         return (bonusAmount + depositAndWinning);
    //     }
    // }
    static getMaxBonusAllowedOfEntryFeeContestWise(mEntryFee, maxBonusAllowed) {
        var UserBalance = WSManager.getBalance();
        var allowedBonusPercantage = maxBonusAllowed;
        var bonusAmount = parseFloat(UserBalance.bonus_amount);
        var realAmount = UserBalance.real_amount;
        var winningAmount = UserBalance.winning_amount;
        var depositAndWinning = parseFloat(realAmount) + parseFloat(winningAmount);
        var maxBonusAccToEntryFee = parseFloat(allowedBonusPercantage) * parseFloat(mEntryFee) / 100;
        let master_data = Utilities.getMasterData();
        var cbBalance = parseFloat(UserBalance.cb_balance || 0)

        if (cbBalance > 0) {
        if (parseFloat(realAmount) >= parseFloat(mEntryFee)) {
            const GST = (mEntryFee * Utilities.getMasterData().gst_rate / 100);
           if(parseFloat(cbBalance) > parseFloat(GST)){
            var totalAmt =  parseFloat(GST) + parseFloat(realAmount) + parseFloat(winningAmount)
           }else{
            var totalAmt =  parseFloat(cbBalance) + parseFloat(realAmount) + parseFloat(winningAmount)
           }
            var cashBackBal = parseFloat(totalAmt)
        } else {
             if(parseFloat(realAmount) <= parseFloat(mEntryFee)) { 
            const GST = (realAmount * Utilities.getMasterData().gst_rate / 100);
            if(parseFloat(cbBalance) > parseFloat(GST)){
                var totalAmt =  parseFloat(GST) + parseFloat(realAmount) + parseFloat(winningAmount)
               }else{
                var totalAmt =  parseFloat(cbBalance) + parseFloat(realAmount) + parseFloat(winningAmount)
               }
            var cashBackBal = parseFloat(totalAmt)
        } }
    }
       
        
        if (cbBalance > 0) {
         return cashBackBal

        } else {

            if (master_data.max_contest_bonus > 0) {
                maxBonusAccToEntryFee = maxBonusAccToEntryFee > master_data.max_contest_bonus ? parseFloat(master_data.max_contest_bonus) : maxBonusAccToEntryFee
            }
            if (bonusAmount > maxBonusAccToEntryFee) {
                return (maxBonusAccToEntryFee + depositAndWinning);
            } else {
                return (bonusAmount + depositAndWinning);
            }
        }
    }
    /**
     * @description This function is responsible to get 18 year old date
     * @param date UTC date
     * @return 18 years old date
    */
    static get18YearOldDate = (date) => {
        return new Date(moment().subtract(18, 'years'));
    }
    /**
     * @description This function is responsible to get 18 year old date
     * @param date UTC date
     * @return 18 years old date
    */
    static getFormatedDate = (data) => {
        return moment(data.date).format(data.format);
    }

    /**
     * @description This function is responsible to convert UTC date to local date
     * @param date UTC date
     * @return Local date
    */
    static getUtcToLocal = (date) => {
        return moment(date).utc(true).local().format();
    }
    /**
     * @description This function is responsible to convert local date to UTC date
     * @param date Local date
     * @return UTC date
    */
    static getLocalToUtc = (date, formate) => {
        return moment.utc(date).format(formate);
    }

    static getFormatedDateTime = (date, format) => {
        if (format) {
            return moment.utc(date).local().format(format);
        }
        return moment(date).utc().local().format();
    }
    /**
     * @description This function is to get know that count down timer should be display or not
     * @param item Fixture item
     */
    static showCountDown(item, forOHr) {
        // forOHr by default false, stats for one hour timer 
        let FOH = forOHr || false
        let scheduleDate = Utilities.getFormatedDateTime(parseInt(item.game_starts_in));
        let currentDate = Utilities.getFormatedDateTime(Date.now());
        var now = moment(currentDate); //todays date
        var end = moment(scheduleDate); // another date
        var duration = moment.duration(end.diff(now));
        var hours = duration.asHours();
        var minutes = duration.asMinutes();
        return (FOH ? ((minutes >= 0) && (hours <= 1)) : ((minutes >= 0) && (hours <= 24)));
    }
    static getExactValueContest = (pPer) => {
        let num = pPer && pPer.toString(); //If it's not already a String
        if (num && num.includes('.')) {
            num = num.slice(0, (num.indexOf(".")) + 3); //With 3 exposing the hundredths place

        }
        //Number(num); //If you need it back as a Number
        if (num != undefined) {

            return num

        }
    }


    static minuteDiffValue(item) {
        let currentDate = Utilities.getFormatedDateTime(Date.now());
        let scheduleDate = Utilities.getFormatedDateTime(item.date);
        var now = moment(currentDate);
        var end = moment(scheduleDate);
        var duration = moment.duration(now.diff(end));
        var minutes = duration.asMinutes();
        return minutes;
    }
    static minuteDiffValueStock(item, timeToCompare) {
        let currentDate = Utilities.getFormatedDateTime(Date.now());
        let scheduleDate = Utilities.getFormatedDateTime(item.date);
        var now = moment(currentDate);
        var end = moment(scheduleDate);
        var duration = moment.duration(now.diff(end));
        var minutes = duration.asMinutes();
        return minutes <= timeToCompare;
    }
    static minuteDiffValueGeo() {
        let currDate = new Date();
        let vvvvsdv = currDate.getHours() + ":" + currDate.getMinutes() + ":" + currDate.getSeconds();
    }
    static getSec(date, configTime) {
        //  let date = '2022-03-09 13:42:00'
        var dt;

        var isSafari = window.safari !== undefined;
        if (isSafari) {
            dt = moment(date).toDate()
        }
        else if (navigator.userAgent.match(/(iPod|iPhone|iPad)/) && navigator.userAgent.match(/AppleWebKit/)) {
            dt = moment(date).toDate()
        }
        else {
            dt = new Date(date);

        }

        dt.setSeconds(dt.getSeconds() + configTime + 3);
        let dateObj = Utilities.getUtcToLocal(dt)
        let game_starts_in = new Date(dateObj).getTime();
        let currentTime = Date.now()

        var seconds = Math.floor((game_starts_in - (currentTime)) / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);
        seconds = seconds - (days * 24 * 60 * 60) - (hours * 60 * 60) - (minutes * 60);

        return minutes < 0 ? -1 : seconds;
    }

    static getSecOther(date) {
        //  let date = '2022-03-09 13:42:00'
        var dt;

        var isSafari = window.safari !== undefined;
        if (isSafari) {
            dt = moment(date).toDate()
        }
        else if (navigator.userAgent.match(/(iPod|iPhone|iPad)/) && navigator.userAgent.match(/AppleWebKit/)) {
            dt = moment(date).toDate()
        }
        else {
            dt = new Date(date);

        }
        dt.setSeconds(dt.getSeconds());
        let dateObj = Utilities.getUtcToLocal(dt)
        let game_starts_in = new Date(dateObj).getTime();
        let currentTime = Date.now()


        var seconds = Math.floor((game_starts_in - (currentTime)) / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);
        seconds = seconds - (days * 24 * 60 * 60) - (hours * 60 * 60) - (minutes * 60);
        return minutes < 0 ? -1 : seconds;
    }

    static getCurrentTime() {
        var currentdate = new Date();
        var datetime = currentdate.getDate() + "/"
            + (currentdate.getMonth() + 1) + "/"
            + currentdate.getFullYear() + " @ "
            + currentdate.getHours() + ":"
            + currentdate.getMinutes() + ":"
            + currentdate.getSeconds();

        return datetime;
    }

    static scrollToTop() {
        window.scrollTo(0, 0)
    }

    static kFormatter(num) {
        return Math.abs(num) > 9999 ? Math.sign(num) * ((Math.abs(num) / 1000).toFixed(1)) + 'k' : Math.sign(num) * Math.abs(num).toFixed(0)
    }

    static kLowerFormatter(num) {
        return Math.abs(num) > 9999 ? Math.sign(num) * (Math.floor(Math.abs(num) / 1000)) + 'k' : Math.sign(num) * Math.abs(num).toFixed(0)
    }

    static numberWithCommas(x) {
        if (x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        } return x
    }

    static showToast(message = "", duration = 2000, icon = 'icon-ic-info') {
        if (message != "" && TOAST) {
            TOAST.showToast({ message: message, duration: duration, icon: icon });
        }
    }
    static handleAppBackManage = (pageType) => {
        if (window.ReactNativeWebView) {
            let data = {
                action: 'SessionKey',
                targetFunc: 'SessionKey',
                page: pageType,
                SessionKey: WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '',
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
    }
    static sendProfileDataToApp = (profileData, action) => {
        if (window.ReactNativeWebView) {
            let data = {
                action: action,
                targetFunc: action,
                page: profileData,
                SessionKey: WSManager.getToken() ? WSManager.getToken() : WSManager.getTempToken() ? WSManager.getTempToken() : '',
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
    }

    static getSelectedSportsForUrl(sports_id) {
        let selectedSports = sports_id;
        let sports_url = '';
        if (typeof sports_id == "undefined") {
            selectedSports = AppSelectedSport;
        }
        if (selectedSports != null) {
            sports_url = Sports.url[selectedSports] ? Sports.url[selectedSports].toLowerCase() : '';
        }
        return sports_url
    }

    static getPFSelectedSportsForUrl(sports_id) {
        let selectedSports = sports_id;
        let sports_url = '';
        if (typeof sports_id == "undefined") {
            selectedSports = PFSelectedSport && PFSelectedSport.name;
        }
        if (selectedSports != null) {
            sports_url = selectedSports.toLowerCase();

        }
        return sports_url
    }

    static getPFSelectedSportsID(sport) {
        let selectedSports = sport;
        let SID = ''
        let SLIST = ls.get('PFSportList')
        if (typeof sport == "undefined") {
            selectedSports = PFSelectedSport.sports_id;
        }
        if (selectedSports != null && SLIST) {
            for (var obj of SLIST) {
                if (obj.name.toLowerCase() === selectedSports) {
                    SID = obj.sports_id;
                    ls.set('PFSSport', obj)
                    setValue.setPFSelectedSport(obj);
                }
            }
        }
        return SID ? SID : PFDefaultSport.sports_id
    }

    static getGameTypeHash() {
        var lobbyHash = ''
        if (SELECTED_GAMET == GameType.Pred) {
            lobbyHash = '#prediction'
        }
        if (SELECTED_GAMET == GameType.OpenPred) {
            lobbyHash = '#open-predictor'
        }
        if (SELECTED_GAMET == GameType.Free2Play) {
            lobbyHash = '#freeToPlay'
        }
        if (SELECTED_GAMET == GameType.OpenPredLead) {
            lobbyHash = '#open-predictor-leaderboard'
        }
        if (SELECTED_GAMET == GameType.MultiGame) {
            lobbyHash = '#multigame'
        }
        if (SELECTED_GAMET == GameType.Pickem) {
            lobbyHash = '#pickem'
        }
        if (SELECTED_GAMET == GameType.Tournament) {
            lobbyHash = '#tournament'
        }
        if (SELECTED_GAMET == GameType.StockFantasy) {
            lobbyHash = '#stock-fantasy'
        }
        if (SELECTED_GAMET == GameType.StockFantasyEquity) {
            lobbyHash = '#stock-fantasy-equity'
        }
        if (SELECTED_GAMET == GameType.StockPredict) {
            lobbyHash = '#stock-prediction'
        }
        if (SELECTED_GAMET == GameType.LiveStockFantasy) {
            lobbyHash = '#live-stock-fantasy'
        }
        if (SELECTED_GAMET == GameType.PickFantasy) {
            lobbyHash = '#pick-fantasy'
        }
        if (SELECTED_GAMET == GameType.PickemTournament) {
            lobbyHash = '#pickem-tournament'
        }
        if (SELECTED_GAMET == GameType.PropsFantasy) {
            lobbyHash = '#props'
        }
        if (SELECTED_GAMET == GameType.OpinionTradeFantasy) {
            lobbyHash = '#opinion-trade'
        }
        return lobbyHash
    }

    static getPFUrlSports() {
        var sportsId = PFSelectedSport;
        let url = window.location.href;
        if (url.includes("#")) {
            let urlArr = url.split("#");
            _Map(urlArr, (item) => {
                let selectedSports = item.toLowerCase();
                let SLIST = ls.get('PFSportList')
                for (var obj of SLIST) {
                    if (obj.name.toLowerCase() === selectedSports) {
                        sportsId = obj;
                        ls.set('PFSSport', sportsId)
                        setValue.setPFSelectedSport(sportsId);
                    }
                }
            })
        }
        sportsId = (sportsId === "null" || sportsId === null) ? PFDefaultSport : sportsId
        return sportsId
    }


    static getUrlSports() {
        var sportsId = AppSelectedSport;
        let url = window.location.href;
        if (url.includes("#")) {
            let urlArr = url.split("#");
            _Map(urlArr, (item) => {
                let selectedSports = item.toLowerCase();
                if (selectedSports in Sports) {
                    sportsId = Sports[selectedSports] + "";
                    ls.set('selectedSports', sportsId)
                    setValue.setAppSelectedSport(sportsId);
                }
            })
        }
        sportsId = (sportsId === "null" || sportsId === null) ? Sports.default_sport : sportsId
        return sportsId + "";
    }

    static setUrlParams = (LobyyData) => {
        let dateformaturl = Utilities.getFormatedDateTime(LobyyData.season_scheduled_date);
        dateformaturl = new Date(dateformaturl);
        let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
        let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
        dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
        let urlParams = LobyyData.home + "-vs-" + LobyyData.away + "-" + dateformaturl;
        return urlParams.toLowerCase();
    }
    static replaceAll = (stringLabel, stringToFind, stringToReplace) => {
        const regex = '/';

        // const regex = /[ \/,\s]/g;
        if (stringToFind === stringToReplace) return stringLabel;
        var temp = stringLabel;
        var index = temp.indexOf(stringToFind);
        while (index !== -1) {
            temp = temp.replace(stringToFind, stringToReplace);
            index = temp.indexOf(stringToFind);
        }
        return stringLabel.replaceAll(regex, stringToReplace);
        // return stringLabel
    }
    static replaceNotifAll = (stringLabel, stringToFind, stringToReplace) => {
        // const regex = /[ \/,\s]/g;
        if (stringToFind === stringToReplace) return stringLabel;
        var temp = stringLabel;
        var index = temp.indexOf(stringToFind);
        while (index !== -1) {
            temp = temp.replace(stringToFind, stringToReplace);
            index = temp.indexOf(stringToFind);
        }
        return temp;
        // return stringLabel.replaceAll(regex, '-');
    }

    static getMasterData() {
        return APP_MASTER_DATA || '';
    }

    static setMasterData(data) {
        APP_MASTER_DATA = data;
        ls.set('_ms', data)
    }

    static getAndroidAppVersion() {
        return ANDROID_APP_INSTALLED_VERSION;
    }

    static setAndroidAppVersion(version) {
        ANDROID_APP_INSTALLED_VERSION = version;
    }

    static getGameTypeSports() {
        var sportData = Utilities.getMasterData().sports_hub || [];

        let tempArray = []
        sportData.map((item, key) => {
            tempArray.push({
                sports_hub_id: item.sports_hub_id,
                game_key: item.game_key,
                allowed_sports: item.allowed_sports
            })
        })
        return tempArray;
    }
    static floorFigure(figure) {
        var d = Math.pow(10, 1);
        return (parseInt(figure * d) / d).toFixed(1);
    }

    static getPrizeInWordFormat(number) {
        number = parseFloat(number)
        let numberName = number;
        let intVersion = Utilities.getMasterData().int_version
        if (intVersion == "1") {
            if (number <= 999999) {
                number = number.toFixed(0)
                number = new Intl.NumberFormat('en-US').format(number)
                numberName = number
            }
            else if (number >= 1000000 && number <= 9999999) {
                number = (number / 1000000).toFixed(2)
                number = new Intl.NumberFormat('en-US').format(number)
                numberName = number + " Million";
            }
            else if (number >= 10000000) {
                number = (number / 1000000).toFixed(0)
                number = new Intl.NumberFormat('en-US').format(number)
                numberName = number + " Million";
            }
        }
        else {
            if (number < 100000) {
                number = number.toFixed(0)
                number = new Intl.NumberFormat('en-IN').format(number)
                numberName = number
            }
            else if (number >= 100000 && number <= 999999) {
                number = (number / 100000).toFixed(1)
                number = new Intl.NumberFormat('en-IN').format(number)
                numberName = number + " Lakh";
            }
            else if (number >= 1000000 && number <= 9999999) {
                number = (number / 100000).toFixed(1)
                number = new Intl.NumberFormat('en-IN').format(number)
                numberName = number + " Lakhs";
            }
            else if (number >= 10000000) {
                number = (number / 10000000).toFixed(1)
                number = new Intl.NumberFormat('en-IN').format(number)
                numberName = number + " Crore";
            }
        }


        return numberName;
    }
    static isValidPhoneNumber = (phone) => {
        if (!phone) {
            return false;
        }
        let formatedNum = parsePhoneNumberFromString(phone.toString());
        return formatedNum && formatedNum.isValid();
    }

    static isValidPhoneNotMandate = (phone) => {
        let formatedNum = parsePhoneNumberFromString(phone.toString());
        return formatedNum && formatedNum.isValid();
    }

    static setPathName = (pathName, from) => {
        if (window.ReactNativeWebView) {
            let data = {
                action: from,
                pathName: pathName,
                targetFunc: from
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
    }
    static handelNativeGoogleLogin(mcontex) {
        window.addEventListener('message', (e) => {

            if (e.data.locale != undefined) {
                WSManager.setAppLang(e.data.locale);

            }
            if (e.data.UserProfile != undefined) {
                WSManager.setProfile(e.data.UserProfile);
            }
            if (e.data.LoginSessionKey != '' && e.data.LoginSessionKey != undefined) {
                WSManager.setToken(e.data.LoginSessionKey);
            }
            if (e.data.isMobileApp != '' && e.data.isMobileApp != undefined) {
                WSManager.setIsMobileApp(e.data.isMobileApp);
            }

            if (e.data.action == 'push' && e.data.type == 'deviceid') {
                if (e.data.token && e.data.token != WSC.DeviceToken.getDeviceId()) {
                    WSC.DeviceToken.setDeviceId(e.data.token);
                    mcontex.updateDeviceToken();
                }
            }
            else if (e.data.action == 'push' && e.data.type == 'receive') {

                WSManager.setPickedGameType(Constants.GameType.DFS)

                let pushStockType = ['560', '561', '562', '563', '566', '567', '568', '624'];
                let pathName = '';
                let referFriendUE = ['53', '54', '55', '156', '159', '162', '37'];
                let transactionHiUE = ['6'];
                if (e.data.notif.group_id && e.data.notif.group_id != null && e.data.notif.group_id != '') {
                    if (mcontex.state.canRedirect) {
                        mcontex.setState({ canRedirect: false })
                        let pathName = 'group-chat/' + e.data.notif.group_id;
                        // alert('HUb pathName>>'+JSON.stringify(pathName));
                        if (pathName && pathName.trim() != '') {
                            mcontex.props.history.push({ pathname: pathName });
                            return;
                        }
                    }
                }
                if (referFriendUE.indexOf(e.data.notif.notification_type) > -1) {
                    mcontex.props.history.push({ pathname: '/refer-friend' });
                    return;
                }
                if (transactionHiUE.indexOf(e.data.notif.notification_type) > -1) {
                    mcontex.props.history.push({ pathname: '/transactions' });
                    return;
                }
                if (e.data.notif.notification_type == '422' || e.data.notif.notification_type == '420') {
                    mcontex.props.history.push({ pathname: '/more', state: { checkAffliate: true } });
                    return;
                }
                if (e.data.notif.notification_type == '582') {
                    mcontex.props.history.push({ pathname: '/earn-coins', state: { dailyQuizPopup: true } });
                    return;
                }
                if (e.data.notif.notification_type == '584' || e.data.notif.notification_type == '0') {
                    mcontex.props.history.push({ pathname: '/rewards' });
                    return;
                }
                if (e.data.notif.notification_type == '623' || e.data.notif.notification_type == '625' || e.data.notif.notification_type == '626' || e.data.notif.notification_type == '590') {
                    WSManager.setPickedGameType(Constants.GameType.StockPredict)
                    mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                    mcontex.props.history.push({ pathname: '/lobby', state: { stockStatistic: false } });
                    return;
                }
                if (e.data.notif.notification_type == '301') {
                    WSManager.setPickedGameType(Constants.GameType.DFS)
                    mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                    mcontex.props.history.push({ pathname: '/lobby', state: { stockStatistic: false } });
                    return;
                }
                if (e.data.notif.notification_type == '587' || e.data.notif.notification_type == '585') {
                    mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                    return;
                }

                if (e.data.notif.notification_type == '583' || e.data.notif.notification_type == '586') {
                    mcontex.props.history.push({ pathname: '/earn-coins' });
                    return;
                }
                if (e.data.notif.notification_type == '581') {
                    mcontex.props.history.push({ pathname: '/feedback' });
                    return;
                }
                if (pushStockType.indexOf(e.data.notif.notification_type) > -1) {
                    if (e.data.notif.stock_type && e.data.notif.stock_type == '1') {
                        WSManager.setPickedGameType(Constants.GameType.StockFantasy)
                    } else if (e.data.notif.stock_type && e.data.notif.stock_type == '2') {
                        WSManager.setPickedGameType(Constants.GameType.StockFantasyEquity)
                    } else if (e.data.notif.stock_type && e.data.notif.stock_type == '3') {
                        WSManager.setPickedGameType(Constants.GameType.StockPredict)
                    } else if (e.data.notif.stock_type && e.data.notif.stock_type == '4') {
                        WSManager.setPickedGameType(Constants.GameType.LiveFantasy)
                    }
                    if (e.data.notif.notification_type == '560' || e.data.notif.notification_type == '563' || e.data.notif.notification_type == '566' || e.data.notif.notification_type == '568') {
                        mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                        mcontex.props.history.push({ pathname: '/lobby', state: { stockStatistic: false } });
                    }
                    else if (e.data.notif.notification_type == '567' || e.data.notif.notification_type == '624') {
                        let pushListingObj = {}
                        pushListingObj['category_id'] = e.data.notif.category_id;
                        pushListingObj['collection_id'] = e.data.notif.collection_id;
                        mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                        mcontex.props.history.push({ pathname: '/lobby', state: { contestListing: true, pushListing: pushListingObj } });
                    }
                    else if (e.data.notif.notification_type == '561' || e.data.notif.notification_type == '562') {
                        mcontex.props.history.push("/sports-hub#" + Utilities.getSelectedSportsForUrl())
                        mcontex.props.history.push({ pathname: '/lobby', state: { stockStatistic: true } });
                    }
                }
                else {

                    if (e.data.notif.notification_type == '151' || e.data.notif.notification_type == '120' || e.data.notif.notification_type == '434') {//deposit promotion
                        pathName = 'add-funds';
                    }
                    else if (e.data.notif.notification_type == '435') {// promotion for contes
                        if (e.data.notif.redirect_to == 7) {
                            pathName = 'add-funds';
                        }
                        else {
                            pathName = 'lobby';
                        }
                    }
                    else if (e.data.notif.notification_type == '436') {
                        //pathName = '/my-contests?contest=completed';
                        mcontex.props.history.push({ pathname: '/my-contests', state: { from: 'notification' } });

                    }
                    else if (e.data.notif.notification_type == '121') {// promotion for contes
                        pathName = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest/' + e.data.notif.contest_unique_id
                    }
                    else if (e.data.notif.notification_type == '123') {// admin refer a friend
                        pathName = 'refer-friend';
                    }
                    else if (e.data.notif.notification_type == '441') {
                        mcontex.props.history.push({ pathname: '/my-contests' });
                        return;
                    }
                    else if (e.data.notif.notification_type == '124' ||
                        e.data.notif.notification_type == '131' ||
                        e.data.notif.notification_type == '132' ||
                        e.data.notif.notification_type == '300' ||
                        e.data.notif.notification_type == '442' ||
                        e.data.notif.notification_type == '443' ||
                        e.data.notif.notification_type == '440') {//124-promotion for fixture 131-match delay  132-lineup announced
                        WSManager.setPickedGameType(Constants.GameType.DFS)
                        ls.set('selectedSports', e.data.notif.sports_id);
                        Constants.setValue.setAppSelectedSport(e.data.notif.sports_id);
                        let dateformaturl = parseURLDate(e.data.notif.season_scheduled_date);
                        let data = e.data.notif;
                        let contestListingPath = '/' + Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + data.collection_master_id + '/' + data.home + "-vs-" + data.away + "-" + dateformaturl + "?sgmty=" + btoa(Constants.SELECTED_GAMET);
                        mcontex.props.history.push({ pathname: contestListingPath.toLowerCase() })
                        contestListingPath = '';
                        return;

                    }

                    else if (e.data.notif.notification_type == '135') {// custom notification
                        if (e.data.notif.custom_notification_type == 1) {
                            pathName = 'lobby';
                        }
                        else if (e.data.notif.custom_notification_type == 2) {
                            pathName = 'my-wallet';
                        }
                        else if (e.data.notif.custom_notification_type == 3) {
                            pathName = 'my-profile';
                        }
                        else if (e.data.notif.custom_notification_type == 4) {
                            pathName = 'my-contests?contest=upcoming';
                        }
                        else if (e.data.notif.custom_notification_type == 5) {
                            pathName = 'refer-friend';
                        }
                        else if (e.data.notif.custom_notification_type == 7) {
                            pathName = 'add-funds';
                        }
                        else {
                            pathName = 'lobby';
                        }
                    }

                    if (pathName && pathName.trim() != '') {
                        mcontex.props.history.push({ pathname: pathName });
                    }
                }
            }
            else if (e.data.action == 'app_dep_linking' && e.data.type == 'android') {
                let can = ls.get('canRedirect');
                if (can == null || can) {
                    mcontex.blockMultiRedirection()
                    let pathName = e.data.pathName;
                    if (pathName) {
                        mcontex.props.history.push(pathName);
                    }
                }
            }
            else if (e.data.action == 'app_dep_linking' && e.data.type == 'reset') {
                ls.set('canRedirect', true)
            }
        });
    }
    static setCpSession(src = null) {
        if (window.ReactNativeWebView) return null
        if (src) {
            window.sessionStorage.setItem("c367d", encodeURIComponent(src));
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            const utm_source = urlParams.get('utm_source');
            let getcp = this.getCpSessionPath()
            if (getcp.includes(utm_source)) return null
            if (utm_source) {
                window.sessionStorage.setItem("c367d", encodeURIComponent(window.location.search));
            }
        }
    }
    static getDeviceType() {
        let device_type = window.ReactNativeWebView ? (WSManager.getIsIOSApp() ? WSC.deviceTypeIOS : WSC.deviceTypeAndroid) : WSC.deviceType
        return device_type;
    }
    static getCpSession() {
        if (window.ReactNativeWebView) return null
        const urlParams = new URLSearchParams(decodeURIComponent(window.sessionStorage.getItem("c367d")));
        return {
            source: urlParams.get('utm_source') || '',
            medium: urlParams.get('utm_medium') || '',
            campaign: urlParams.get('utm_campaign') || '',
            term: urlParams.get('utm_term') || '',
            content: urlParams.get('utm_content') || '',
        }
    }
    static getRandomRefrenceId() {
        // var navigator_info = window.navigator;
        // var screen_info = window.screen;
        // var uid = navigator_info.mimeTypes.length;
        // uid += navigator_info.userAgent.replace(/\D+/g, '');
        // uid += navigator_info.plugins.length;
        // uid += screen_info.height || '';
        // uid += screen_info.width || '';
        // uid += screen_info.pixelDepth || '';
        let uid = Math.floor((Math.random() * 999999999 * 888888) + 1)
        return uid;
    }
    static getCpSessionPath() {
        if (window.ReactNativeWebView) return null
        return decodeURIComponent(window.sessionStorage.getItem("c367d"))
    }

    static bannedStateToast(bn_state) {
        if (bn_state == 1) {
            Utilities.showToast(AppLabels.FREE_CONTEST_INSTED, 3000)
        }
        if (bn_state == 2) {
            Utilities.showToast(AppLabels.USER_FROM_BANNED_STATE_ARE_NOT_ALLOWED, 3000)
        }
    }

    // COMMON GTM EVENT FIRE METHOD
    static setScreenName(key = null) {
        // MetaData.template +' | '+ MetaData.verifymobile.title
        let data = {
            targetFunc: 'setScreenName',
            screen_name: key ? `${MetaData.template} | ${MetaData[key].title}` : window.location.pathname
        }
        if (window.ReactNativeWebView) {
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        }
    }

    static gtmEventFire(event, object = {}, is_direct = false) {
        let eventPayload = {
            ...(!is_direct && { 'user_id': WSManager.getProfile().user_unique_id }),
            ...(!is_direct && { 'user_name': WSManager.getProfile().user_name }),
            ...object
        }
        if (window.ReactNativeWebView) {
            // For Mobile App
            let data = {
                targetFunc: 'gtmEventFire',
                event: event,
                Payload: eventPayload
            }
            window.ReactNativeWebView.postMessage(JSON.stringify(data));
        } else {
            if (window.dataLayer) {
                window.dataLayer.push({
                    'event': event,
                    ...eventPayload
                })
            }
        }
    }

    static getLineupPosCount() {
        return new Promise((resolve, reject) => {
            let lineupData = ls.get('Lineup_data')
            let positions = []

            function count_duplicate(a) {
                let counts = {}

                for (let i = 0; i < a.length; i++) {
                    if (counts[a[i]]) {
                        counts[a[i]] += 1
                    } else {
                        counts[a[i]] = 1
                    }
                }
                return counts
            }

            lineupData && lineupData.length > 0 && lineupData.forEach(function (obj) {
                positions.push(obj.position)
            })
            // return count_duplicate(positions)
            resolve(count_duplicate(positions))
        })
    }
    static setH2hData(dataFromConfirmPopUp, contest_id) {
        WSManager.setH2hMessage(true);
        ls.set('h2hTab', true);
        if (dataFromConfirmPopUp.FixturedContestItem && dataFromConfirmPopUp.FixturedContestItem.is_scratchwin == 1 && Utilities.getMasterData().a_scratchwin == 1) {
            WSManager.setActiveScratch({
                contest_id: contest_id,
                is_scratchwin: true
            })

        }
    }


    static geoLocationChanges(b_s) {
        let geoPlayFree = localStorage.getItem('geoPlayFree')
        let latlongv = localStorage.getItem('encodedLatLong') == 0 ? true : false

        let bslist = ls.get('bslist')
        let { master_state_id } = WSManager.getProfile()


        let { a_country, bs_sa, bs_a } = Utilities.getMasterData();
        let _banStates = Object.keys(bslist || {});
        const [country, bannedState] = b_s
        const banStates = bannedState ? _banStates.filter((obj) => obj == bannedState) : _banStates.filter((obj) => obj == master_state_id)
        if (WSManager.loggedIn()) {
            if (bs_a == 0) {
                localStorage.setItem('banned_on', 0)
            }
            else {
                if (bs_a == 1) {
                    if (!latlongv && banStates.length == 0) {
                        localStorage.setItem('banned_on', 0)
                    }
                    if (!latlongv && banStates.length != 0) {
                        localStorage.setItem('banned_on', 2)
                    }
                    if (latlongv && geoPlayFree == 'true') {
                        localStorage.setItem('banned_on', 1)
                    }

                    if (window.ReactNativeWebView) {
                        if (!latlongv && geoPlayFree == true) {
                            localStorage.setItem('banned_on', 1)
                        }
                    }
                }
            }
        }


        if (b_s) {
            if (a_country && a_country.includes(country) == true) {
                if (bs_sa == "0") {
                    localStorage.setItem('playFreeContest', 'false')
                    window.location.assign('/banned-state');
                }
                else {
                    localStorage.setItem('playFreeContest', 'true')
                }
                // }

                // else {
                //     localStorage.setItem('playFreeContest', 'false')
                // }
            }


            else if (a_country && a_country.includes(country) == false) {
                window.location.assign('/banned-state');
            }

            // }, 100)
        }

    }

    static aadharConfirmation(aadhar_data, props) {
        if (aadhar_data.aadhar_status == "0" && (aadhar_data.aadhar_detail.aadhar_id)) {
            Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
            props.history.push({ pathname: '/aadhar-verification' })
        }
        else {
            Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
            props.history.push({ pathname: '/aadhar-verification' })
        }
    }



    static approxeq(svr_time, epsilon = null) {
        let local_time = Math.round(Date.now() / 1000)
        return new Promise((resolve, reject) => {
            resolve(true)
            // if (epsilon == null) {
            //     epsilon = 4;
            // }
            // Math.abs(local_time - svr_time) < epsilon ? resolve(true) : reject(false)
        })
    }

    static setSocketEve(obj, is_direct = false) {
        const _obj = _cloneDeep(obj)
        let _skev = ls.get('_skev') || []
        return new Promise((resolve, reject) => {
            if (obj != undefined) {
                let _local_time = Math.round(Date.now() / 1000)
                Utilities.approxeq(_obj.time).then(res => {
                    let _data = {
                        ..._obj,
                        local_time: _local_time,
                    }
                    if (_obj.over_time) {
                        if (is_direct) {
                            _data = {
                                ..._data,
                                timer_date: _obj.market_date_time + Number(_obj.over_time) + 3,
                            }
                        } else {
                            _data = {
                                ..._data,
                                timer_date: _local_time + Number(_obj.over_time) + 2,
                            }
                        }
                    }

                    if (_filter(_skev, o => o.collection_id == _data.collection_id).length > 0) {
                        _skev = _Map(_skev, (obj) => {
                            if (obj.collection_id == _data.collection_id) {
                                obj = _data
                            }
                            return obj;
                        })
                        ls.set('_skev', _skev)
                    } else {
                        ls.set('_skev', [..._skev, _data])
                    }

                    resolve(_data)
                }).catch(err => {
                    let myConfirmWindow = window.confirm("Your device date is inaccurate, adjust your clock and try again")
                    if (myConfirmWindow == true || myConfirmWindow == false) {
                        window.location.reload()
                    }
                })
            }
        })
    }

    static removeSoketEve(collection_id) {
        let _skev = ls.get('_skev')
        let _arr = _filter(_skev, o => o.collection_id != collection_id)
        ls.set('_skev', _arr);
    }

    static exportFunction = (query_string, export_url) => {
        var query_string = query_string;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        let target_url = WSC.userURL + export_url + query_string
        if (window.ReactNativeWebView) {
            setTimeout(() => {
                let data = {
                    action: "external_link",
                    type: 'external_link',
                    targetFunc: "external_link",
                    url: target_url,
                };
                sendMessageToApp(data)
            }, 100);
        } else {
            window.open(target_url, '_blank');
        }
    }


    static downloadFile = (fileURL, meta = {}) => {
        var filename = fileURL.substring(fileURL.lastIndexOf('/') + 1);
        if (!window.ActiveXObject) {

            if (navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
                var save = document.createElement('a');
                save.href = fileURL;
                save.target = '_blank';
                save.download = filename;
                document.location = save.href;
            }
            else if (navigator.userAgent.toLowerCase().match(/(android)/)) {
                if (window.ReactNativeWebView) {
                    let data = {
                        action: 'download',
                        targetFunc: 'download',
                        type: 'team',
                        url: fileURL,
                        meta: { "file_name": filename, ...meta }
                    }
                    sendMessageToApp(data);
                }
                else {
                    let save = document.createElement('a');
                    save.href = fileURL;
                    save.target = '_blank';

                    save.download = filename;
                    var evt = new MouseEvent('click', {
                        'view': window,
                        'bubbles': true,
                        'cancelable': false
                    });
                    save.dispatchEvent(evt);
                    (window.URL || window.webkitURL).revokeObjectURL(save.href);
                }
            }
            else {
                fetch(fileURL)
                    .then(response => {
                        // Create a blob from the response data
                        return response.blob();
                    })
                    .then(blob => {
                        // Create a temporary URL object from the blob
                        const url = window.URL.createObjectURL(new Blob([blob]));

                        // Create a link element and simulate a click to download the file
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', filename);
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    })
                    .catch(error => {
                        // console.error(error);
                    });
            }
        }
        // for IE < 11
        else if (!!window.ActiveXObject && document.execCommand) {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, filename)
            _window.close();
        }
    }
    static getPropsIds(sports_id) {
        return PROPS_IDS[sports_id] || {};
    }

    static setPropsIds(data, sports_id) {
        PROPS_IDS[sports_id] = data;
    }
    static setSelectedSports = (sports_id) => {
        ls.set('selectedSports', sports_id)
    }

    static getPayoutMdta() {
        return PROPS_PAYOUT_MDT || {};
    }

    static setPayoutMdta(data) {
        PROPS_PAYOUT_MDT = data;
    }
}
// PROPS_PAYOUT_MDT
export function parseURLDate(date) {
    let dateformaturl = Utilities.getUtcToLocal(date);
    dateformaturl = new Date(dateformaturl);
    let dateformaturlDate = ("0" + dateformaturl.getDate()).slice(-2)
    let dateformaturlMonth = ("0" + (dateformaturl.getMonth() + 1)).slice(-2)
    dateformaturl = dateformaturlDate + '-' + dateformaturlMonth + '-' + dateformaturl.getFullYear();
    return dateformaturl;
}


export function BannerRedirectLink(result, props) {
    if (result.banner_type_id == 1) {
        let dateformaturl = parseURLDate(result.schedule_date);
        let contestListingPath = Utilities.getSelectedSportsForUrl().toLowerCase() + '/contest-listing/' + result.collection_master_id + '/' + result.home + "-vs-" + result.away + "-" + dateformaturl + "?sgmty=" + btoa(SELECTED_GAMET);
        props.history.push({ pathname: contestListingPath });
    }
    else if (result.banner_type_id == 2) {
        props.history.push({ pathname: '/refer-friend' });
    }
    else if (result.banner_type_id == 3) {
        props.history.push({ pathname: '/add-funds' });
    }
    else if (result.banner_type_id == 4) {
        if (result.target_url.includes('legality')) {
            props.history.push({ pathname: '/legality' });
        } else if (result.target_url.includes('/refer-friend')) {
            props.history.push({ pathname: '/refer-friend' });
        } else if (result.target_url.includes('/add-funds')) {
            props.history.push({ pathname: '/add-funds' });
        } else if (result.target_url.includes('feature/rookie')) {
            props.history.push({ pathname: '/feature/rookie' });
        } else {
            if (result.target_url.includes('http')) {
                if (checkSponserUrlDomain(result.target_url, process.env.REACT_APP_BASE_URL)) {
                    window.open(result.target_url, "_blank")
                }
                else {
                    if (window.ReactNativeWebView) {
                        setTimeout(() => {
                            let data = {
                                action: "external_link",
                                type: 'external_link',
                                targetFunc: "external_link",
                                url: result.target_url,
                            };
                            sendMessageToApp(data)
                        }, 100);

                    } else {
                        window.open(result.target_url, "_blank")
                    }
                }
            }
        }
    }
}

export function checkSponserUrlDomain(sponserUrl, baseUrl) {
    var isPathSame = false;
    const sponserUrlPath = new URL('', sponserUrl);
    const baseUrlPath = new URL('', baseUrl);
    if (sponserUrlPath.hostname.replace('www.', '') == baseUrlPath.hostname.replace('www.', '')) {
        isPathSame = true;
    }
    return isPathSame;

}

export function callNativeRedirection(url) {
    let data = {
        action: 'bannerLinkRedirection',
        targetFunc: 'bannerLinkRedirection',
        type: 'link',
        url: url,
    }
    window.ReactNativeWebView.postMessage(JSON.stringify(data));
}

export function getImageBaseUrl(image) {
    // let IMAGE_BASE_URL = require('./../assets/img/' + image)
    let IMAGE_BASE_URL = Utilities.getS3URL(image);
    return process.env.NODE_ENV === 'production' ? IMAGE_BASE_URL : require('./../assets/img/' + image);
    // return IMAGE_BASE_URL
}

export function _handleWKeyDown(event) {
    const BACKSPACE = 8;
    const LEFT_ARROW = 37;
    const RIGHT_ARROW = 39;
    const DELETE = 46;
    const ENTER = 13;

    var isValidKey = event.keyCode === ENTER || event.keyCode === BACKSPACE || event.keyCode === LEFT_ARROW || event.keyCode === RIGHT_ARROW || event.keyCode === DELETE;
    if (this && event.target instanceof HTMLInputElement) {
        const regex = /^[0-9\b]+$/;
        if (event.key !== '' && !regex.test(event.key) && !isValidKey) {
            event.preventDefault();
        }
    }
}

export function isValidJson(text) {
    if (typeof text !== "string") {
        return false;
    }
    else {
        return true;
    }
}

export function checkBanState(selectedFixture, CustomHeader, isFrom, isShare) {
    let isFromShare = isShare || false
    let isValid = true;

    if (BanStateEnabled) {
        let banStates = Object.keys(Utilities.getMasterData().banned_state || {});
        // let isRealMoney = selectedFixture ? checkIsRealMoney(selectedFixture) : false;
        let freeEntry = selectedFixture ? selectedFixture.entry_fee != '0' : false;
        if (!WSManager.getProfile().master_state_id && freeEntry && Utilities.getMasterData().a_aadhar != "1") {
            isValid = false;
            CustomHeader.showBanStateModal({ isFrom: isFrom ? isFrom : 'CL', isFromShare: isFromShare || false });
        } else if (banStates.includes(WSManager.getProfile().master_state_id) && freeEntry) {
            isValid = false;
            CustomHeader.showBanStateMSGModal({ isFrom: isFrom ? isFrom : 'CL', title: 'You are unable to enter contests', Msg1: 'Sorry, but players from ', Msg2: ' are not able to enter contests at this time', isFromShare: isFromShare || false });
        }
    }
    return isValid;
}
export function checkIsRealMoney(cItem) {
    if (cItem.prize_type == 1 && cItem.currency_type == 1 && cItem.entry_fee > 0) {
        return true;
    }
    var realAmount = 0;

    let prizeData = cItem.prize_distibution_detail ? cItem.prize_distibution_detail : cItem.prize_distribution_detail || [];
    if (SELECTED_GAMET == GameType.PickFantasy) {
        prizeData = JSON.parse(prizeData)
    }
    prizeData && prizeData.map(function (lObj, lKey) {
        let amount = 0;
        if (lObj.max_value) {
            amount = parseFloat(lObj.max_value);
        } else {
            amount = parseFloat(lObj.amount);
        }
        if (lObj.prize_type == 1) {
            realAmount = realAmount + amount;
        }
    })
    if (realAmount > 0) {
        return true;
    }
    return false;
}

export {
    Utilities
};
/**
    * @description After Social login check if user is existing or new user and navigate to appropriate step
    * @param data data received from social plateform
    * @param fbUser in case user login through FB then it will have user data else it will be null
    * @param googleUser in case user login through Google then it will have user data else it will be null
   */
export function checkFlow(nextStepData) {
    let pathName = '/' + nextStepData.data.next_step;

    if (nextStepData.data.next_step === 'login_success') {
        WSManager.setToken(nextStepData.data.Sessionkey);
        pathName = '/lobby';
    } else if (nextStepData.data.next_step === 'phone') {
        pathName = '/pick-mobile';
    }

    return { pathname: pathName, state: { nextStepData: nextStepData } }
}

export function isFooterTab(tab_key) {
    let allFooterTabs = DASHBOARD_FOOTER.tabs;
    for (let i = 0; i < allFooterTabs.length; i++) {
        if (allFooterTabs[i].tab_key === tab_key) {
            return true;
        }
    }
    return false;
}

export function sendMessageToApp(action) {
    if (window.ReactNativeWebView) {
        window.ReactNativeWebView.postMessage(JSON.stringify(action));
    }
}

export function blobToFile(theBlob, fileName) {
    //A Blob() is almost a File() - it's just missing the two properties below which we will add
    theBlob.lastModifiedDate = new Date();
    theBlob.name = fileName;
    return new File([theBlob], fileName, { name: fileName, lastModifiedDate: Date.now() });
}

export function compressImg(mfile, options) {
    const imageCompression = require('browser-image-compression').default;
    return imageCompression(mfile, options);
}

export function checkSame(date1, date2) {
    return moment(date1).isSame(date2);
}
export function isDateTimePast(season_scheduled_date) {
    let date = moment(season_scheduled_date).utc(true).local().valueOf()
    let now = moment().utc().local().valueOf();
    return now > date;
}
export function IsGameTypeEnabled(gameKey) {
    let testArray = Utilities.getMasterData().sports_hub.filter(obj => obj.game_key == gameKey)
    return testArray.length > 0 ? true : false
}


export function SportsSchedule({ item = {}, timerCallback = () => { } }) {
    // let _item = {
    //     ...item,
    //     ...(true ? {
    //             season_scheduled_date: "2023-05-30 17:00:00",
    //             end_scheduled_date: "2023-06-1 19:00:00",
    //             game_starts_in: "1683637080000"
    //         } : {}
    //     )
    // }
    const { season_scheduled_date, end_scheduled_date, game_starts_in } = item
    const toLocal = (date, format = '') => {
        return moment(date).utc(true).local().format(format);
    }
    const _startD = toLocal(season_scheduled_date, 'DD')
    const _endD = toLocal(end_scheduled_date, 'DD')

    const _startM = toLocal(season_scheduled_date, 'MM')
    const _endM = toLocal(end_scheduled_date, 'MM')

    const _startY = toLocal(season_scheduled_date, 'YYYY')
    const _endY = toLocal(end_scheduled_date, 'YYYY')
    return (
        <>
            {
                Utilities.showCountDown(item) ?
                    <span className="tour-timer-color">
                        {game_starts_in && <CountdownTimer timerCallback={() => timerCallback(item)} deadlineTimeStamp={game_starts_in} />}
                    </span>
                    :
                    <>
                        {
                            (_endD > _startD || Number(_endM) > Number(_startM) || Number(_endY) > Number(_startY)) ?
                                <>
                                    {
                                        `${toLocal(season_scheduled_date, 'DD MMM, hh:mm A')} - ${toLocal(end_scheduled_date, 'DD MMM, hh:mm A')}`
                                    }
                                </>
                                :
                                <>
                                    {
                                        `${toLocal(season_scheduled_date, 'DD MMM, hh:mm A')} - ${toLocal(end_scheduled_date, 'hh:mm A')}`
                                    }
                                </>
                        }
                    </>
            }
        </>
    )
}

export function addOrdinalSuffix(number) {
    if(!number) return 0;
    const suffixes = ['th', 'st', 'nd', 'rd'];
    const absNumber = Math.abs(number);
    const lastTwoDigits = absNumber % 100;

    let suffix;
    if (lastTwoDigits >= 11 && lastTwoDigits <= 13) {
        suffix = suffixes[0];
    } else {
        const lastDigit = absNumber % 10;
        suffix = suffixes[lastDigit] || suffixes[0];
    }
    return `${number}${number == 0 ? '' : suffix}`;
}
export function convertToTimestamp(date) {
    return new Date(Utilities.getUtcToLocal(date)).getTime()
}
export function prizeDataInclude(arr) {
    let _arr = _Map(arr, (obj) => {
        let prize_data = []
        switch (true) {
            case obj.bonus > 0:
                prize_data.push({ prize_type: '0', amount: obj.bonus })
                break;
            case obj.amount > 0:
                prize_data.push({ prize_type: '1', amount: obj.amount })
                break;
            case obj.coin > 0:
                prize_data.push({ prize_type: '2', amount: obj.coin })
                break;
            case obj.merchandise != '':
                prize_data.push({ prize_type: '3', name: obj.merchandise })
                break;
        
            default:
                break;
        }
        return {...obj, prize_data}
    })
    return _arr;
}

export function getPropsName(list, id) {
    const obj = _filter(list, o => o.prop_id == id)[0] || {}
    return obj.name
}

export function getSelectedSports (bool = false) {
    const { default_sport } = Utilities.getMasterData()
    return bool ? default_sport : (ls.get('selectedSports') || default_sport)
}

export function setPickedGameType (value) {
    let strObj = JSON.stringify(value);
    store.dispatch(Actions.gameTypeHandler(value));
    localStorage.setItem('SHGT', btoa(strObj));
}
export function isDesktop () {
    const breakpoint = 767;
    const windowWidth = window.innerWidth
    
    const { sports_hub, default_sport } = Utilities.getMasterData()
    const { GameType } = store.getState()
    let gameType = sports_hub.find(item => item.game_key == GameType) || sports_hub[0];
    const _finalBreakpoint = windowWidth > breakpoint && gameType && gameType.is_desktop == 1 && Constants.ModuleRedirect.includes(GameType)

   return { 
        is_desktop: _finalBreakpoint,
        game_key: GameType || sports_hub[0].game_key,
        game_route: Constants.GAME_ROUTE[GameType] || '',
        selected_game: AppSelectedSport || default_sport
    }
}

export function headerBalUpdate() {
    try {
        store.dispatch(Actions.headerBalUpdate())
    } catch (error) {
    }
}
export function headerProfileUpdate() {
    try {
        store.dispatch(Actions.headerProfileUpdate())
    } catch (error) {
    }
}
export function headerNotifUpdate() {
    try {
        store.dispatch(Actions.headerNotifUpdate())
    } catch (error) {
    }
}