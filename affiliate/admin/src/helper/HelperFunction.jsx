import WSManager from './WSManager';
import moment from 'moment';
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
import * as NC from './NetworkingConstants';
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
    _times
};
export var APP_MASTER_DATA = '';
export var SPORTS_DATA = '';
export var LANGUAGE_DATA = '';
export default class HelperFunction {
    static getNumberWithCommas(x) {
        var res = x ? x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : null;
        return res
    }
    static getFormatedDateTime = (date, format) => {
        if (format) {
            return moment.utc(date).local().format(format);
        }
        return moment(date).utc().local().format();
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
        let currentDate = this.getFormatedDateTime(Date.now());
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

    static getSportsData() {
        return SPORTS_DATA || '';
    }

    static setSportsData(data) {
        SPORTS_DATA = data;
    }

    static setLanguageData(data) {
        LANGUAGE_DATA = data;
    }

    static getLanguageData() {
        return LANGUAGE_DATA || [];
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

        console.log("Ex URL==", NC.baseURL + export_url + query_string);
        window.open(NC.baseURL + export_url + query_string, '_blank');
    }
    static formatPhoneNumber = (phoneNumberString) => {
        var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
        var match = cleaned.match(/^(\d{4})(\d{3})(\d{3})$/)
        if (match) {
            return match[1] + '-' + match[2] + '-' + match[3]
        }
        return null
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
    static getTodayDate = () => {
        let d = new Date();
        return d.getDate()
    }


    static allowNetworkGame = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_network_fantasy) ? this.getMasterData().allow_network_fantasy : '0'
        return rVal
    }
    
    static allowGst = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_gst) ? this.getMasterData().allow_gst : '0'

        return rVal
    }
    static allowScratchWin = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_scratchwin) ? this.getMasterData().allow_scratchwin : '0'
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
    static getFirstDateOfMonth = () => {
        return new Date(Date.now() - ((this.getTodayDate()) - 1) * 24 * 60 * 60 * 1000)
    }

    static allowCoinOnly = () => {
        let rVal = !_isUndefined(this.getMasterData().coin_only) ? this.getMasterData().coin_only : '0'
        return rVal
    }
    static allowSportsPrediction = () => {
        let rVal = !_isUndefined(this.getMasterData().allow_prediction) ? this.getMasterData().allow_prediction : '0'
        return rVal
    }
    static allowCryto = () => {
        let rVal = !_isUndefined(this.getMasterData().a_crypto) ? this.getMasterData().a_crypto : '0'
        return rVal
    }
    static allowBTC = () => {
        let rVal = !_isUndefined(this.getMasterData().a_btcpay) ? this.getMasterData().a_btcpay : '0'
        return rVal
    }

}