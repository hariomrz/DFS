import React from 'react';
import _ from 'lodash';
import AppConfig from "InitialSetup/AppConfig";
import moment from 'moment';
import Moment from "react-moment";
import { Utilities, _filter, _isEmpty } from 'Utilities/Utilities';
import ls from 'local-storage';
import { GameType, SELECTED_GAMET, OTSportList } from 'helper/Constants';
import store from 'ReduxLib/store';
import {Actions} from 'ReduxLib/reducers';
import { SportsIDs } from "JsonFiles";
import { getUserAadharDetail } from 'WSHelper/WSCallings';
import * as AppLabels from "helper/AppLabels";
import i18n from 'i18n';
import WSManager from 'WSHelper/WSManager';

const Utils = {
  getUtcToLocal: (date, format = 'DD MMM - hh:mm A') => {
    return moment(date).utc(true).local().format(format);
  },
//  getImageBaseUrl:(image) =>{
//     let IMAGE_BASE_URL = Utilities.getS3URL(image);
//     return process.env.NODE_ENV === 'production' ? IMAGE_BASE_URL : require('./../assets/img/' + image);
//     },
  isDateTimePast: (dt) => {
    let date = moment(dt).utc(true).local().valueOf()
    let now = moment().utc().local().valueOf();
    return now > date;
  },
  convertToTimestamp: (date) => {
    return new Date(Utils.getUtcToLocal(date)).getTime()
  },
  MomentDateComponent:(date, format)=> {
    return (date ? <Moment date={Utils.getUtcToLocal(date)} format={format} /> : '')
  },
  getS3path:(name, type) => {
    let middlePath = ''
    switch (type) {
        case "banner":
            middlePath = 'upload/banner/'
            break;
        case "jersey":
            middlePath = 'upload/jersey/'
            break;
        case "profile":
            middlePath = 'upload/profile/thumb/'
            break;
        default:
            middlePath = 'upload/flag/'
            break;
    }
    return `${AppConfig.s3.BUCKET}${middlePath}${name}`
  },
  getPrizeInWordFormat:(number)=> {
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
  },
  getTodayTimestamp: (_utc = '') => {
    let date = new Date(_utc)
    let today = moment(date).utc(true).local().valueOf();
    return today;
  },
  numberWithCommas: (x, symbol = '') => {
      if (x == undefined) return x;
      x = x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      return symbol + x || symbol + '0';
  },
  dateIsBetween: (start_date, end_date) => {
      const _now = new Date()
      const compareDate = moment(Utils.getUtcToLocal(_now)).format("X")
      const startDate = moment(Utils.getUtcToLocal(start_date)).format("X")
      const endDate = moment(Utils.getUtcToLocal(end_date)).format("X")
      return (startDate <= compareDate && compareDate <= endDate)
  },
  digit: (value) => {
      const leftDigit = value >= 10 ? value.toString()[0] : '0';
      const rightDigit = value >= 10 ? value.toString()[1] : value.toString();
      return (
          leftDigit + rightDigit
      )
  },
  kFormatter: (num) => {
      if (typeof num !== "number") {
          num = Number(num)
      }
      if (Math.abs(num) >= 10000000) num = Math.sign(num) * ((Math.abs(num) / 10000000).toFixed(1)) + ' cr';
      else if (Math.abs(num) >= 100000) num = Math.sign(num) * ((Math.abs(num) / 100000).toFixed(1)) + ' L';
      else if (Math.abs(num) >= 1000) num = Math.sign(num) * ((Math.abs(num) / 1000).toFixed(1)) + 'k';
      return num;
  },
  addOrdinalSuffix:(number) =>{
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
  },

    getMasterData: () => {
        // console.log('ms', ls.get('_ms'))
        // return ls.get('_ms')

        const masterData = ls.get('_ms');
        return masterData || {};

    },
    setPickedGameType: (value) => {
        let strObj = JSON.stringify(value);
        store.dispatch(Actions.gameTypeHandler(value));
        localStorage.setItem('SHGT', btoa(strObj));
    },
    getPickedGameType: () => {
        let obj = localStorage.getItem('SHGT');
        return obj ? JSON.parse(atob(obj)) : null;
    },
    getSports: () => {
        const masterData = Utils.getMasterData() || {};
        const { sports_hub = [], fantasy_list = [] } = masterData;
    
        const game_key = Utils.getPickedGameType() || 'allow_dfs';
        if (!localStorage.getItem('SHGT')) {
            Utils.setPickedGameType('allow_dfs');
        }        
        const _sports = _.filter(sports_hub, (obj) => obj.game_key == game_key)[0];
        const sports = _.filter(fantasy_list, (obj) => _.includes(_sports.allowed_sports, obj.sports_id));
    

        if(game_key == GameType.OpinionTradeFantasy) {
            console.log(1);
        }
        // return game_key == GameType.OpinionTradeFantasy ? OTSportList : sports;
        return sports;
    },

    setSelectedSports: (sports_id) => {
        store.dispatch(Actions.setAppSelectedSport(sports_id));
        ls.set('selectedSports', sports_id)
    },
    getSelectedSports: (bool = false) => {
        const { default_sport } = Utils.getMasterData()
        return bool ? default_sport : (ls.get('selectedSports') || default_sport)
    },

    getGameTypeHash: (game_key) => {
        console.log(SELECTED_GAMET);
        console.log(game_key);
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
        if (SELECTED_GAMET == GameType.OpinionTradeFantasy) {
            lobbyHash = '#opinion-trade'
        }
        return lobbyHash
    },
    setBalance: (userBalance) =>  {
        // Saves UserBalance data to localStorage
        localStorage.setItem('userBalance', JSON.stringify(userBalance))
    },
    getBalance: () => {
        // Retrieves the UserBalance data from localStorage
        const userBalance = localStorage.getItem('userBalance')
        return userBalance ? JSON.parse(localStorage.userBalance) : {}
    },
    getSportsName: (id) => {
        if(!_.isNumber(Number(id.replace(/\D/g, '')))) return id;
        // let _value;
        // if(_.isNumber(id)) {
        //     const masterData = Utils.getMasterData() || {};
        //     const { fantasy_list = [] } = masterData;
        //     const sports = _.filter(fantasy_list, (obj) => obj.sports_id == id)[0] || "cricket";
        //     _value =  sports.en.toLowerCase()
        // } else {
        //     _value = SportsIDs[id]
        // }
        // console.log(_value);
        // return _value;
        const masterData = Utils.getMasterData() || {};
        const { fantasy_list = [] } = masterData;
        const sports = _.filter(fantasy_list, (obj) => obj.sports_id == id)[0] || "cricket";

        console.log(sports);

        return sports.en ? sports.en.toLowerCase() : sports
    },
    getSecIn: (currItem) => {
        return currItem["2nd_total"] > 0 &&
              Utils.isDateTimePast(currItem.season_scheduled_date) &&
              !Utils.isDateTimePast(currItem["2nd_inning_date"]);
      },
    getGameTypeName: () => {
        const i18nLang = i18n.language == 'en-US' ? 'en' : i18n.language
        const masterData = Utils.getMasterData() || {};
        const { sports_hub = [], fantasy_list = [] } = masterData;
        const _store = store.getState()
        const _GameType = sports_hub.find((obj) => obj.game_key == _store.GameType);
        const lang = i18nLang + '_t'
        return (!_isEmpty(_GameType) && _GameType[lang] + ' ') || ''
      },

    getAadharStatus: ({contest, ...props}) => {
        return new Promise((resolve, reject) => {
            const { a_aadhar } = Utilities.getMasterData()
            const loggedIn = WSManager.loggedIn()
            if (a_aadhar != 1 || !loggedIn || contest.entry_fee == 0) return resolve(true);
            const doAadhar = ({ aadhar_detail, aadhar_status }) => {
                if (aadhar_status == '0' && aadhar_detail.aadhar_id == "0") {
                    console.log('Not Submmited yet!');
                    Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
                    props.history.push("/aadhar-verification");
                    resolve(false)
                } else if (aadhar_status == '0' && aadhar_detail.aadhar_id != "0") {
                    console.log('Aadhar Status Pending!');
                    Utilities.showToast(AppLabels.VERIFICATION_PENDING_MSG, 3000);
                    props.history.push("/aadhar-verification");
                    resolve(false)
                } else if (aadhar_status == '1' && aadhar_detail.aadhar_id != "0") {
                    console.log('Aadhar Status Approved!');
                    resolve(true)
                } else if (aadhar_status == '2' && aadhar_detail.aadhar_id != "0") {
                    console.log('Aadhar Status Rejected!');
                    Utilities.showToast(AppLabels.AADHAAR_NOT_UPDATED, 3000);
                    props.history.push("/aadhar-verification");
                    resolve(false)
                }
            }
            if (_isEmpty(WSManager.getProfile().aadhar_detail) || (WSManager.getProfile().aadhar_status != "1")) {
                getUserAadharDetail().then(({ response_code, data }) => {
                    if (response_code == '200') {
                        const _profile = WSManager.updateProfile(data)
                        doAadhar(_profile)
                    }
                })
            } else {
                doAadhar(WSManager.getProfile())
            }
        })
    }
}

export default Utils;