/* eslint-disable eqeqeq */
import React, { Suspense, lazy } from 'react';
import { withTranslation } from "react-i18next";
import "./assets/scss/style.scss";
import { DARK_THEME_ENABLE } from './helper/Constants';
import { Utilities, _isUndefined } from './Utilities/Utilities';
import { createBrowserHistory } from 'history';
import WSManager from './WSHelper/WSManager';
import { socketConnect } from 'socket.io-react';
import { io } from 'socket.io-client';
import * as WSC from "./WSHelper/WSConstants";
import { addVisit } from './WSHelper/WSCallings';
import firebase from "firebase";
import withClearCache from './CacheBuster';
const NoNetwork = lazy(()=>import('./views/NoNetwork/NoNetwork'));
const MyProvider = lazy(()=>import('./InitialSetup/MyProvider'));

if (process.env.REACT_APP_GTM_ID != '' && !window.ReactNativeWebView) {
  const TagManager = require('react-gtm-module');
  const tagManagerArgs = {
    gtmId: process.env.REACT_APP_GTM_ID
  }
  TagManager.initialize(tagManagerArgs)
}

if (!window.ReactNativeWebView) {
  let SessionLog = require("./helper/SessionLog").default;
  const sessionlog = new SessionLog()
  sessionlog.init()
}

const firebaseConfig = {
  apiKey: "AIzaSyA1Fq5irs0JoFcrhe905DA8pzRDwzJxDII",
  authDomain: "predev-fw.firebaseapp.com",
  databaseURL: "https://predev-fw-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "predev-fw",
  storageBucket: "predev-fw.appspot.com",
  messagingSenderId: "190426576558",
  appId: "1:190426576558:web:a1ee925cbfc369946adf3f",
};
if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
  firebase.analytics();
}


const history = createBrowserHistory();
const location = history.location;
const queryString = require('query-string');
const parsed = queryString.parse(location.search);
class App extends React.PureComponent {

  constructor(props) {
    super(props)
    this.state = {
      isOnline: true
    };
    Utilities.setCpSession()
  };

getSessionKey = () => {
  if (!_isUndefined(parsed) && parsed.Sessionkey) {
    localStorage.setItem('id_token', parsed.Sessionkey);
  }
}

  addVisitCode = () => {
    WSManager.getAflcCode().then(res => {
      if(!res) return;
      if(!localStorage.getItem('is_cpvisit')) {
        addVisit(res).then(responseJson=> {
          if (responseJson.response_code == WSC.successCode) {
            localStorage.setItem('is_cpvisit', 1);
          }
        })
      }
    })
  }
  
  async componentDidMount() {
    if(process.env.REACT_APP_SOCKET_CONNECTION == 1){
      this.getSessionKey()

    }
    if (DARK_THEME_ENABLE) {
      document.body.classList.add('body-dark-theme');
    }

    if (process.env.REACT_APP_SINGULAR_ENABLE > 0) {
      var apiKey = process.env.REACT_APP_SINGULAR_API;
      var secretKey = process.env.REACT_APP_SINGULAR_SECRET;
      var productId = process.env.REACT_APP_SINGULAR_PRODUCT;
      if (!window.ReactNativeWebView) {
       // window.initSingular(apiKey, secretKey, productId);
      }
    }

    this.setState({ isOnline: navigator.onLine })
    this.disableLogs();
    if (!_isUndefined(parsed) && parsed.affcd) {
      WSManager.setAffiliatCode(parsed.affcd)
    }
    if (!_isUndefined(parsed) && parsed.cp) {
      WSManager.setAflcCode(parsed.cp)
    }
    setTimeout(() => {
      this.addVisitCode()
    }, 500)
  }


  // getEventName(source){
  //   if(source=='fb'){
  //     return 'facebook_redirect';
  //   }
  //   else if(source=='insta'){
  //     return 'insta_redirect';
  //   }
  //   else if(source=='google_ads'){
  //     return 'googleads_redirect';
  //   }
  //   else if(source=='twitter'){
  //     return 'twitter_redirect';
  //   }
  //   else{
  //     return 'direct_redirect';

  //   }
  // }

  disableLogs = () => {
    try {
      if (process.env.NODE_ENV !== "development") {
        console.log = () => { };
        console.warn = () => { };
      }
    } catch (error) {
    }
  }

  checkISOnline = () => {
    return (
      <Suspense fallback={<div />} >{this.state.isOnline ? <MyProvider /> : <NoNetwork />}</Suspense>
    )
  }

  render() {
    return this.checkISOnline()
  }
}
export default withClearCache(withTranslation()(socketConnect(App)))
