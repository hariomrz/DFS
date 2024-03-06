import 'react-app-polyfill/ie9';
import React from 'react';
import ReactDOM from 'react-dom';
import { SocketProvider } from 'socket.io-react';
import { io } from 'socket.io-client';

import './index.css';
import { I18nextProvider } from "react-i18next";
import i18n from "./i18n";
import App from './App';
import * as serviceWorker from './serviceWorker';
import * as WSC from "./WSHelper/WSConstants";
import ErrorBoundary from './Component/ErrorBoundary';
import { Provider } from 'react-redux';
import { store } from 'ReduxLib';

const socket = io(
    WSC.nodeBaseURL,
    { 
        transports: ['websocket']

    }).connect();


// io(WSC.nodeBaseURL, { transports: ['websocket'] }).connect();
ReactDOM.render(
    <Provider store={store}><SocketProvider socket={socket}><I18nextProvider i18n={i18n}><ErrorBoundary><App /></ErrorBoundary></I18nextProvider></SocketProvider></Provider>
, document.getElementById('root'));

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: http://bit.ly/CRA-PWA
serviceWorker.unregister();
