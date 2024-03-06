import './polyfill'
import React from 'react';
import ReactDOM from 'react-dom';
 import { Provider } from 'react-redux';
import './index.css';
import configureStore from './store';
import App from './App';
//import * as serviceWorker from './serviceWorker';
// disable ServiceWorker
// import registerServiceWorker from './registerServiceWorker';

ReactDOM.render(<Provider store={configureStore()}><App /></Provider>, document.getElementById('root'));
//ReactDOM.render(<App />, document.getElementById('root'));
// disable ServiceWorker
// registerServiceWorker();
//serviceWorker.unregister();
