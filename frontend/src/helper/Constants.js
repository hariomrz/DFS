import { Sports } from "../JsonFiles";
import { MALE, FEMALE } from "./AppLabels";
import ls from "local-storage";
import { _Map, Utilities } from "../Utilities/Utilities";
import Images from "../components/images";

import store from 'ReduxLib/store';
import {Actions} from 'ReduxLib/reducers';

export var OTPSIZE = 4;

export var IS_BRAND_ENABLE = process.env.REACT_APP_DEV_BY_VTECH == 1;
export var IS_SPORTS_HUB = false;
export var IS_PREDICTION = false;
export var IS_PICKEM = false;
export var IS_DFS = false;
export var IS_STOCKFANTASY = false;
export var IS_LIVESTOCKFANTASY = false;
export var IS_TOURNAMENT = false;
export var IS_PICKEM_TOURNAMENT = false;
export var IS_DFSL = false;
export var IS_MULTIGAME = false;
export var IS_OPEN_PREDICTOR = false;
export var IS_FPP_OPEN_PREDICTOR = false; // open predictor with fixed prize pool
export var SELECTED_GAMET = '';
export var TOAST = {};
export const CONTEST_UPCOMING = 0;
export const CONTEST_LIVE = 1;
export const CONTEST_COMPLETED = 2;
export const CONTESTS_LIST = -1;
export const BANNER_TYPE_REFER_FRIEND = 2;
export const BANNER_TYPE_DEPOSITE = 3;
export var DEFAULT_COUNTRY = 'in';
export var DEFAULT_COUNTRY_CODE = '91';
export var ONLY_SINGLE_COUNTRY = 0;
export var BANNED_MASTER_COUNTRY_ID = '101';
export var AppSelectedSport = store.getState().SelectedSport || ls.get("selectedSports");
export var PFSelectedSport = ls.get("PFSSport");
export var OTSportList = ls.get("OTSportList");
// export var PFSelectedSport = ls.get("PFSSport") || {
//   is_default: "1",
//   name: "CRICKET",
//   sports_id: "1"
// };
export var APP_DOWNLOAD_LINK_ANDROID = "";
export var globalLineupData = {};
export var preTeamsList = {};
export var bannerData = {};
export var LOBBY_FILTER_ARRAY = [];
export var SignupTmpData = {};
export var ReferralData = {};
export var CountryList = [];
export var isBankDeleted = false;
export var trafficSource = '';
// export const DARK_THEME_ENABLE = process.env.REACT_APP_ENABLE_DARK_THEME == 1;
export const DARK_THEME_ENABLE = process.env.REACT_APP_ENABLE_DARK_THEME == 1 ? (process.env.REACT_APP_SHOW_TOGGLE_THEME_CHANGE == 1 ? ls.get('DarkTheme') : true ) : false;
// export const DARK_THEME_ENABLE = process.env.REACT_APP_ENABLE_DARK_THEME == 1 ? true : (process.env.REACT_APP_SHOW_TOGGLE_THEME_CHANGE == 1 && ls.get('DarkTheme') ? true : false);
export var BanStateEnabled = false;
export var StateTaggingValue = process.env.REACT_APP_STATE_TAGGING_ENABLE;

export var OnlyCoinsFlow = 0;
export var EnableBuyCoin = process.env.REACT_APP_BUY_COINS_ENABLE == 1 ? true : false;
export var PlayStoreLink = process.env.REACT_APP_DOWNLOAD_LINK_ANDROID || '';
export var IsSpinWheelSkip = false;
export var AllowRedeem = true;
export var RFContestId = '';
export var CMStatus = 2; // 0 to hide all coachmarks , 1 to show coachmark before login , 2 to show coachmark after login
export var IsDynamicStockRules = false; // true then dynamic rules otherwise static
export var STKHTPSlider = true; // true then how to play with slider
export var StockSetting = []
export var spinWheelSucc = false;
export var DCBSucc = false;
export var PFDefaultSport = {
  is_default: "1",
  name: "CRICKET",
  sports_id: "1"
}

export const getGendersList = () => [
  { value: "male", label: MALE },
  { value: "female", label: FEMALE }
];

export var ALLOW_LANG = []
export var DASHBOARD_FOOTER = {
  tabs: [
    { tab_key: "lobby" },
    { tab_key: "my-contests" },
    { tab_key: "earn-coins" },
    { tab_key: "my-profile" },
    { tab_key: "more" }
    // { tab_key: 'notification' },
    // { tab_key: 'my-wallet' },
    // { tab_key: 'refer-friend' }
  ],
  config: {
    tab_path: {
      "/lobby": "lobby",
      "/my-contests": "my-contests",
      "/earn-coins": "earn-coins",
      "/my-profile": "my-profile",
      "/leaderboard": "leaderboard",
      // "/feed": "feed",
      "/more": "more",
      "/my-contests/0": "0",
      "/my-contests/1": "1",
      "/my-contests/2": "2",
      "/notification": "notification",
      "/my-wallet": "my-wallet",
      "/refer-friend": "refer-friend"
    },
    my_contest_tab: {
      upcoming: "my-contests/0",
      live: "my-contests/1",
      completed: "my-contests/2"
    }
  }
};

export var LANGUAGE_OBJ = {};
export var NOTIFICATION_DATA = {};
export var MATCH_TYPE = {
  1: "ODI",
  2: "TEST",
  3: "T20",
  4: "T10"
};
export var PAYMENT_TYPE = {
  UPI: "upi",
  WALLET: "wallet",
  NET_BANKING: "net_banking",
  CREDIT_DEBIT_CARD: "credit_debit_card"
};
export var crypto_cur = {
    BNB: "BINANCE COIN",
    BNB_BSC: "SMART CHAIN",
    ETH: "ETHEREUM",
    TRX: "TRON"
}
export var GameType = {
  // left side key use throughout the files for check, right side value will same as game_key from master json api 
  OpenPredLead: "allow_fixed_open_predictor", //8
  Pickem: "allow_pickem",// 4
  OpenPred: "allow_open_predictor", // 6
  Pred: "allow_prediction",// 3
  MultiGame: "allow_multigame",// 5
  DFS: "allow_dfs",// 2
  Free2Play: "allow_free2play",// 7
  Tournament: "allow_tournament", // 1 
  StockFantasy: "allow_stock_fantasy", // 10
  StockFantasyEquity: "allow_equity", // 11 
  LiveFantasy: "live_fantasy", // 12

  StockPredict: "allow_stock_predict", // 27
  LiveStockFantasy: "allow_live_stock_fantasy",
  PickFantasy: "picks_fantasy",
  PickemTournament: "pickem_tournament",
  PropsFantasy: "props_fantasy",
  OpinionTradeFantasy: "opinion_trade_fantasy",

};

export var GAME_ROUTE = {
  "allow_dfs": "dfs",// 2
  // "opinion_trade_fantasy": "opinion-trade"
};

export class setValue {
  static setBanStateEnabled(value) {
    BanStateEnabled = value;
    if(value && Utilities.getMasterData().int_version == 1){
      StateTaggingValue = 0;
    }
  }
  static setCountryCodeData(value) {
    if(value){
      let tmpA = value.split('_');
      ONLY_SINGLE_COUNTRY = tmpA.length > 0 ? tmpA[0] :  0;
      DEFAULT_COUNTRY_CODE = tmpA.length > 1 ? tmpA[1] : '91';
      DEFAULT_COUNTRY = tmpA.length > 2 ? tmpA[2] : 'in';
      BANNED_MASTER_COUNTRY_ID = tmpA.length > 3 ? tmpA[3] : '101';
    }
  }
  static setOnlyCoinFlow(obj) {
    OnlyCoinsFlow = obj ? obj : 0;
  }
  static setAllowRedeem(obj) {
    AllowRedeem = OnlyCoinsFlow == 1 || OnlyCoinsFlow == 2 ? obj : true;
  }
  static setReferralData(obj) {
    ReferralData = obj;
  }
  static setToastObject(obj) {
    TOAST = obj;
  }
  static setSource(mSource){
    trafficSource = mSource;
  }
  static setOtpSize(value) {
    OTPSIZE = value;
  }
  static setNotificationCount(data) {
    NOTIFICATION_DATA = data;
  }
  static setFilter(filters) {
    LOBBY_FILTER_ARRAY = filters;
  }
  static setCountry(list) {
    CountryList = list;
  }

  static setLanguage(Languages) {
    ALLOW_LANG = Languages;
  }
  static setAppSelectedSport(sport) {
    AppSelectedSport = sport || Sports.default_sport;
    
    store.dispatch(Actions.setAppSelectedSport(AppSelectedSport));
    Utilities.setSelectedSports(AppSelectedSport)
  }
  static setPFSelectedSport(sport) {
    PFSelectedSport = sport || PFDefaultSport;
  }
  static setOTSportList(sport) {
    OTSportList = sport || OTSportList;
  }
  static setAppDownloadLink(link) {
    APP_DOWNLOAD_LINK_ANDROID = link;
  }
  static setBankDeleted(value) {
    isBankDeleted = value;
  }
  static setSportsHubAllow(value) {
    IS_SPORTS_HUB = value;
    if (value) {
      _Map(DASHBOARD_FOOTER.tabs, (item, idx) => {
        if (item.tab_key === "earn-coins") {
          DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "sports-hub";
        }
      });
    }
  }
  static setGameTypesEnable(value) {
    if (value == GameType.Tournament) {
      IS_TOURNAMENT = true;
    }
    if (value == GameType.DFS) {
      IS_DFS = true;
    }
    if (value == GameType.Pred) {
      IS_PREDICTION = true;
    }
    if (value == GameType.Pickem) {
      IS_PICKEM = true;
    }
    if (value == GameType.MultiGame) {
      IS_MULTIGAME = true;
    }
    if (value == GameType.OpenPred) {
      IS_OPEN_PREDICTOR = true;
    }
    if (value == GameType.OpenPredLead) {
      IS_FPP_OPEN_PREDICTOR = true;
    }
    if (value == GameType.StockFantasy) {
      IS_STOCKFANTASY = true;
    }
    if (value == GameType.LiveStockFantasy) {
      IS_LIVESTOCKFANTASY = true;
    }
    if (value == GameType.PickemTournament) {
      IS_PICKEM_TOURNAMENT = true;
    }
  }
  
  static setSelectedGameType(value, isOT = false) {
    // if(value == GameType.OpinionTradeFantasy && isOT) {
    //   _Map(DASHBOARD_FOOTER.tabs, (item, idx) => {
    //     if (item.tab_key === "my-contests") {
    //       DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "my-wallet";
    //     }
    //     if (item.tab_key === "leaderboard") {
    //       DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "my-profile";
    //     }
    //   });
    // } else {
    // }
    _Map(DASHBOARD_FOOTER.tabs, (item, idx) => {
      if (item.tab_key === "my-profile") {
        DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "leaderboard";
      }
    });
      
    SELECTED_GAMET = value;
  }

  static setDFSL(isDFS, allowSocila) {
    IS_DFSL = true
    if (isDFS) { 
      _Map(DASHBOARD_FOOTER.tabs, (item, idx) => {
        if (item.tab_key === "my-profile") {
          // if (allowSocila == 1) {
          //   DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "feed";

          // }
          //  else{
            DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "leaderboard";
  
          //  }
        }
        else if (item.tab_key === "leaderboard") {
          // if (allowSocila == 1) {
          //   DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "feed";

          // }
          //  else{
            DASHBOARD_FOOTER.tabs[idx]["tab_key"] = "leaderboard";
  
          //  }

        }
      });
    }
  }
  static skipSpinWheel() {
    IsSpinWheelSkip = true;
  }
  static succDCB() {
    DCBSucc = true;
  }
  static SetRFContestId(id) {
    RFContestId = id;
  }
  static setStockSettings(data) {
    StockSetting = data;
  }
}

export const ModuleRedirect = ['allow_dfs'];