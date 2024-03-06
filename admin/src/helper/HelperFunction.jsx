import React from "react";
import WSManager from './WSManager';
// import moment from 'moment';
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
import _remove from 'lodash/remove';
import _find from 'lodash/find';
import * as NC from './NetworkingConstants';
import Images from "../components/images";
import Moment from 'react-moment';
import moment from 'moment-timezone';
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
    _remove,
    _find,
};

export var APP_MASTER_DATA = '';
export var SPORTS_DATA = '';
export var LANGUAGE_DATA = '';
export var LEADERBOARD_DATA = '';
export default class HelperFunction {
    static getNumberWithCommas(x) {
        var res = x ? x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : null;
        return res
    }
    static getFormatedDateTime1 = (date, format) => {
        if (format) {
            return moment.utc(date).local().format(format);
        }
        return moment(date).utc().local().format();
    }

    static getFormatedDateTime = (date, format) => {
        const { timezone, int_version } = APP_MASTER_DATA
        let _format = format
        // if(format != 'YYYY-MM-DD') {
        //     _format = int_version == 0 ? 'D-MMM-YYYY hh:mm A' : 'D-MMM-YYYY HH:mm z';
        // }
         // if(format != 'YYYY-MM-DD' || (format != 'D MMM') || format != 'D MMM - ') {
        //     _format = int_version == 0 ? 'D-MMM-YYYY hh:mm A' : 'D-MMM-YYYY HH:mm z';
        // }

        return moment(date).utc(true).tz(timezone).format(_format)
    }

    

    

    static showCountDown(dateTime) {
        let scheduleDate = WSManager.getUtcToLocal(dateTime);
        let currentDate = HelperFunction.getFormatedDateTime(Date.now());
        var now = moment(currentDate); //todays date
        var end = moment(scheduleDate); // another date
        var duration = moment.duration(end.diff(now));
        var hours = duration.asHours();
        var minutes = duration.asMinutes();
        return ((minutes >= 0) && (hours <= 24));
    }


    static getTimeDiff = (dateTime) => {
        let scheduleDate = WSManager.getUtcToLocal(dateTime);
        let currentDate = this.getFormatedDateTime1(Date.now());
        var now = moment(currentDate); //todays date
        var end = moment(scheduleDate); // another date
        var duration = moment.duration(end.diff(now));
        var hours = duration.asHours();
        var minutes = duration.asMinutes();
        // return true;   
        return (minutes <= 0);
    }

    static getPercent = (value, total_value) => {
        let Percentage = "0"
        if (total_value > "0")
            Percentage = ((value / total_value) * 100).toFixed(0)
        return Percentage
    }

    static isFloat(value) {
        return !isNaN(value) && value.toString().indexOf('.') != -1
    }

    static scrollView(ID) {
        var elmnt = document.getElementById(ID);
        elmnt.scrollIntoView({ behavior: "smooth", block: "end" });
    }

    static capitalFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    static generatePassword(length) {
        let charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
            retVal = "";
        for (var i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        return retVal;
    }

    static copyContent = (item) => {
        const el = document.createElement('textarea');
        el.value = item;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }

    static getCurrentPage = (total_item, per_page, current_page) => {
        let page = Math.ceil((total_item - 1) / per_page)
        let fPage = page > current_page ? current_page : page
        return fPage
    }

    static decimalValidate = (value, digit) => {
        var t = value;
        return value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), digit)) : t;
    }
    static getIntVersion = () => {
        return this.getMasterData().int_version
    }

    static getCurrencyCode = () => {
        return this.getMasterData().currency_code
    }

    static getMasterData() {
        return APP_MASTER_DATA || '';
    }

    static setMasterData(data) {
        APP_MASTER_DATA = data;
    }

    static formatPhoneNumber = (phoneNumberString) => {
        var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
        var match = cleaned.match(/^(\d{4})(\d{3})(\d{3})$/)
        if (match) {
            return match[1] + '-' + match[2] + '-' + match[3]
        }
        return null
    }

    static getPrizeInWordFormat(number_val) {
        let number = parseFloat(number_val)
        let numberName = number;
        let intVersion = this.getIntVersion()
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

    static setSportsData(data) {
        SPORTS_DATA = data;
    }

    static getSportsData() {
        return SPORTS_DATA || '';
    }

    static setLanguageData(data) {
        LANGUAGE_DATA = data;
    }

    static getLanguageData() {
        return LANGUAGE_DATA || [];
    }

    static getLeaderboardData() {
        return LEADERBOARD_DATA || '';
    }

    static setLeaderboardData(data) {
        LEADERBOARD_DATA = data;
    }

    static containsString(target, pattern) {
        var value = 0;
        pattern.forEach(function (word) {
            value = value + target.includes(word);
        });
        return (value === 1)
    }
    static exportFunction = (query_string, export_url) => {
        var query_string = query_string;

        let sessionKey = WSManager.getToken();
        query_string += "&Sessionkey" + "=" + sessionKey;

        
        window.open(NC.baseURL + export_url + query_string, '_blank');
    }

    static getPrizeAmount = (prize_data) => {
        let prize_text = "Prizes";
        let is_tie_breaker = 0;
        let prizeAmount = { 'real': 0, 'bonus': 0, 'point': 0 };
        if (!_isUndefined(prize_data)) {
            prize_data.map(function (lObj) {
                var amount = 0;
                amount += !_isUndefined(lObj.amount) ? (parseInt(lObj.amount) * ((parseInt(lObj.max) - parseInt(lObj.min)) + 1)) : 0
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
            });
        }
        if (is_tie_breaker == 0 && prizeAmount.real > 0) {
            prize_text = this.getCurrencyCode() + this.getPrizeInWordFormat(prizeAmount.real);
        } else if (is_tie_breaker == 0 && prizeAmount.bonus > 0) {
            prize_text = '<i class="icon-bonus"></i>' + this.getPrizeInWordFormat(prizeAmount.bonus);
        } else if (is_tie_breaker == 0 && prizeAmount.point > 0) {
            prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + this.getPrizeInWordFormat(prizeAmount.point)
        }
        return { __html: prize_text };
    }

    static getMerchandiseName = (merchandise_list, id) => {
        let merName = ''
        if (!_isEmpty(merchandise_list) && !_isUndefined(id)) {
            let mObj = _find(merchandise_list, { merchandise_id: id });
            merName = mObj.name
        }
        return merName;
    }
    static get18YearOldDate = (date) => {
        return new Date(moment().subtract(18, 'years'));
    }

    static dateInUtc = (date) => {
        let returnD = ''
        if (!_isUndefined(date)) {
            returnD = moment.utc(date).format("YYYY-MM-DD HH:mm:ss")
        }

        return returnD
    }
    static removeWhiteSpace = (str) => {
        let retVal = ''
        if (!_isEmpty(str))
            retVal = str.replace(/\s+/g, '');

        return retVal
    }

    static getPrizeMoney = (prize_data) => {
        let prizeAmount = prize_data[0]
        let prize_text = ''
        if (prizeAmount.prize_type == '1') {
            prize_text = this.getCurrencyCode() + this.getPrizeInWordFormat(prizeAmount.amount);
        }
        else if (prizeAmount.prize_type == '0') {
            prize_text = '<i class="icon-bonus"></i>' + this.getPrizeInWordFormat(prizeAmount.amount);
        }
        else if (prizeAmount.prize_type == '2') {
            prize_text = '<img src="' + Images.COINIMG + '" alt="coin-img" />' + this.getPrizeInWordFormat(prizeAmount.amount)
        }
        else if (prizeAmount.prize_type == '3') {
            prize_text = prizeAmount.name
        }
        return { __html: prize_text };
    }

    static getTodayDate = () => {
        let d = new Date();
        return d.getDate()
    }

    static allowDFSTournament = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_dfs_tournament) ? this.getMasterData().allow_dfs_tournament : '0'
        return rVal
    }

    static allowTDSReport = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_tds) ? this.getMasterData().allow_tds : '0'
        return rVal
    }

    static allowIndianTDS = () => {
        let rVal = !_isUndefined(this.getMasterData().tds_india) ? this.getMasterData().tds_india : '0'
        return rVal
    }
    
    static allowPickemTournament = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_pickem_tournament) ? this.getMasterData().allow_pickem_tournament : '0'
        return rVal
    }

    static allowNetworkGame = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_network_fantasy) ? this.getMasterData().allow_network_fantasy : '0'
        return rVal
    }

    static allowGst = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_gst) ? this.getMasterData().allow_gst : '0'
        return rVal
    }
    static allowGstType = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_gst_type) ? this.getMasterData().allow_gst_type : '0'
        return rVal
    }

    
    static allowTds = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_tds) ? this.getMasterData().allow_tds : '0'
        return rVal
    }

    static allowScratchWin = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_scratchwin) ? this.getMasterData().allow_scratchwin : '0'
        return rVal
    }
    static getFirstDateOfMonth = () => {
        console.log(new Date(Date.now() - ((this.getTodayDate()) - 1) * 24 * 60 * 60 * 1000))
        return new Date(Date.now() - ((this.getTodayDate()) - 1) * 24 * 60 * 60 * 1000)
    }

    static allowSelfExclusion = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_self_exclusion) ? this.getMasterData().allow_self_exclusion : '0'
        return rVal
    }

    static allowPrivateContest = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_private_contest) ? this.getMasterData().allow_private_contest : '0'
        return rVal
    }

    static allowRefLeaderboard = () => {
        let rVal = !_isUndefined(this.getMasterData().a_ref_leaderboard) ? this.getMasterData().a_ref_leaderboard : '0'
        return rVal
    }

    static allowBuyCoin = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_buy_coin) ? this.getMasterData().allow_buy_coin : '0'
        return rVal
    }

    static allowRefLeaderboard = () => {
        let rVal = !_isUndefined(this.getMasterData().a_ref_leaderboard) ? this.getMasterData().a_ref_leaderboard : '0'
        return rVal
    }

    static allowCoin = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_coin) ? this.getMasterData().allow_coin : '0'
        return rVal
    }

    static allowBuyCoin = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_buy_coin) ? this.getMasterData().allow_buy_coin : '0'
        return rVal
    }
    static allowReverseContest = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_reverse_contest) ? this.getMasterData().allow_reverse_contest : '0'
        return rVal
    }
    static allowBooster = () => {
        let rVal = !_isUndefined(this.getMasterData().booster) ? this.getMasterData().booster : '0'
        return rVal
    }
    static checkAlphabets = (inputtxt) => {
        return !/^[a-zA-Z ]*$/g.test(inputtxt)
    }
    static allowSecondInni = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_2nd_inning) ? this.getMasterData().allow_2nd_inning : '0'
        return rVal
    }

    static promoteFixture = (val) => {
        let TempDate = moment(WSManager.getUtcToLocal(val.season_scheduled_date)).format("D-MMM-YYYY hh:mm A")
        var params = {};
        params.email_template_id = 4;

        params.season_game_uid = val.season_game_uid;
        params.league_id = val.league_id;
        params.all_user = 1;
        params.for_str = ' for ' + val.home + ' vs ' + val.away + ' (' + TempDate + ')';

        return params
    }
    static allowXpPoints = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_xp_point) ? this.getMasterData().allow_xp_point : '0'
        return rVal
    }
    static allowStockFantasy = () => {
        let rVal = !_isUndefined(this.getMasterData().asf) ? this.getMasterData().asf : '0'
        return rVal
    }
    static allowCoinOnly = () => {
        let rVal = !_isUndefined(this.getMasterData().coin_only) ? this.getMasterData().coin_only : '0'
        return rVal
    }
    static convertTodecimal = (val, dec_val) => {
        return parseFloat(val).toFixed(dec_val)
    }
    static dateGreaterThanToday = (inpdate) => {
        var mEndDate = new Date(WSManager.getUtcToLocal(inpdate));
        var curDate = new Date();

        let compDate = false;
        if (curDate >= mEndDate) {
            compDate = true;
        }
        return compDate
    }
    static removeLastComma = (str) => {
        return str.replace(/,\s*$/, "");
    }
    static allowRookieContest = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_rookie_contest) ? this.getMasterData().allow_rookie_contest : '0'
        return rVal
    }
    static replaceCharacter = (str, replace, replace_with) => {
        let new_str = parseFloat(str).toString()
        return new_str.replace(replace, replace_with);
    }

    static allowBenchPlyer = () => {
        let rVal = !_isUndefined(this.getMasterData().bench_player) ? this.getMasterData().bench_player : '0'
        return rVal
    }
    static allowOneSpace = (value) => {
        if (value)
            return value.replace(/  +/g, ' ')
        else
            return ''
    }
    static getDateFormat = (date, format) => {
        return date ? moment(date).format(format) : ''
    }
    static getMonthNumFromString(mon) {
        return new Date(Date.parse(mon + " 1, 2012")).getMonth() + 1
    }

    static countDecimals(str) {
        if (Math.floor(str.valueOf()) === str.valueOf()) return 0;
        return str.toString().split(".")[1].length || 0;
    }

    static allowSubscription = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_subscription) ? this.getMasterData().allow_subscription : '0'
        return rVal
    }
    static allowEquityFantasy = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_equity) ? this.getMasterData().allow_equity : '0'
        return rVal
    }
    static allowBoosterInSports = (sport_id) => {
        // let BoosterInSports = ['7', '2', '4', '5', '1'];
        let BoosterInSports = ['7'];
        return BoosterInSports.includes(sport_id)
    }
    static allowSportsPrediction = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_prediction) ? this.getMasterData().allow_prediction : '0'
        return rVal
    }
    static allowQuiz = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_quiz) ? this.getMasterData().allow_quiz : '0'
        return rVal
    }
    static getYesterdayDate = (inp_date) => {
        var d = inp_date;
        return d.setDate(d.getDate() - 1);
    }
    static dateIsToday = (inputDate, todaysDate) => {
        if (inputDate)
            return (inputDate.setHours(0, 0, 0, 0) == todaysDate.setHours(0, 0, 0, 0));
    }
    static isLastDayOfMonth(dt) {
        return new Date(dt.getTime() + 86400000).getDate() === 1;
    }
    static getLastMonthFirstDate(dt) { 
        if (dt)       
        return dt.setMonth(dt.getMonth() - 1);
    }
    static allowSocial = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_social) ? this.getMasterData().allow_social : '0'
        return rVal
    }
    static allowH2H = () => {
        let rVal = !_isUndefined(this.getMasterData().h2h_challenge) ? this.getMasterData().h2h_challenge : '0'
        return rVal
    }
    
    static get_h2h_group_id = () => {
        let rVal = !_isUndefined(this.getMasterData().h2h_group_id) ? this.getMasterData().h2h_group_id : '0'
        return rVal
    }
    static allowCryto = () => {
        let rVal = !_isUndefined(this.getMasterData().a_crypto) ? this.getMasterData().a_crypto : '0'
        return rVal
    }
    static allowLiveFantsy = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_lf) ? this.getMasterData().allow_lf : '0'
        return rVal
    }

    static getImageUrl = (img_path, img_name) => {
        return NC.S3 + img_path + img_name
    }

    static allowFastKhelo = () => {
        // let rVal = !_isUndefined(this.getMasterData().fast_khelo) ? this.getMasterData().fast_khelo : '0'
        let rVal = !_isUndefined(this.getMasterData().allow_lf) ? this.getMasterData().allow_lf : '1'
        return rVal

    }
    static allowPicksFantasy = () => {
        // let rVal = !_isUndefined(this.getMasterData().fast_khelo) ? this.getMasterData().fast_khelo : '0'
        let rVal = !_isUndefined(this.getMasterData().allow_picks) ? this.getMasterData().allow_picks : '1'
        return rVal

    }

    static allowPropsFantasy = () => {
        // let rVal = !_isUndefined(this.getMasterData().fast_khelo) ? this.getMasterData().fast_khelo : '0'
        let rVal = !_isUndefined(this.getMasterData().allow_props) ? this.getMasterData().allow_props : '1'
        return rVal

    }

      static allowOpinionTrade = () => {
        // let rVal = !_isUndefined(this.getMasterData().fast_khelo) ? this.getMasterData().fast_khelo : '0'
        let rVal = !_isUndefined(this.getMasterData().allow_opinion_trade) ? this.getMasterData().allow_opinion_trade : '1'
        return rVal

    }
    static allowStockPredict = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_stock_predict) ? this.getMasterData().allow_stock_predict : '0'
        return rVal
    }

    static allowBTC = () => {
        let rVal = !_isUndefined(this.getMasterData().a_btcpay) ? this.getMasterData().a_btcpay : '0'
        return rVal
    }
    static allowDFS = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_dfs) ? this.getMasterData().allow_dfs : '0'
        return rVal
    }

    static allowDfsAutopublish = () => {
        let rVal = !_isUndefined(this.getMasterData().dfs_auto_publish) ? this.getMasterData().dfs_auto_publish : '0'
        return rVal
    }
    static allowPAN = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_pan) ? this.getMasterData().allow_pan : '0'
        return rVal
    }
    static allowAADHAR = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_aadhar) ? this.getMasterData().allow_aadhar : '0'
        return rVal
    }
    static allowBANK = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_bank) ? this.getMasterData().allow_bank : '0'
        return rVal
    }
    static allowLFPrivateContest = () => {
        let rVal = !_isUndefined(this.getMasterData().lf_private_contest) ? this.getMasterData().lf_private_contest : '0'
        return rVal
    }
    static allowLiveStockFantasy = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_lsf) ? this.getMasterData().allow_lsf : '0'
        return rVal
    }

     static allowAffiliate = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_affiliate) ? this.getMasterData().allow_affiliate : '0'
        return rVal
    }

     static allowAffiliateCommssion = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_affiliate_commssion) ? this.getMasterData().allow_affiliate_commssion : '0'
        return rVal
    }
        static allowOpentrade= () => {
        let rVal = !_isUndefined(this.getMasterData().allow_opinion_trade) ? this.getMasterData().allow_opinion_trade : '0'
        return rVal
    }

}


