import React, { Component } from 'react';
import { HashRouter, Route, Switch, Redirect } from 'react-router-dom';

// Styles
// CoreUI Icons Set
import '@coreui/icons/css/coreui-icons.min.css';
// Import Flag Icons Set
import 'flag-icon-css/css/flag-icon.min.css';
// Import Font Awesome Icons Set
import 'font-awesome/css/font-awesome.min.css';
// Import Simple Line Icons Set
import 'simple-line-icons/css/simple-line-icons.css';
// Import Main styles for this application
import './scss/style.scss'
import WSManager from "./helper/WSManager";
// Containers
import { DefaultLayout } from './containers';
import { Login, Page401, Page404, Page500, ForgotPassword } from './views/Pages';
import * as NC from "./helper/NetworkingConstants";
import LS from 'local-storage';
import { notify } from 'react-notify-toast';
import HF from './helper/HelperFunction';
import _ from 'lodash';
import Loader from './components/Loader';
import moment from 'moment-timezone';
class App extends Component {
  constructor(props) {
    super(props)
    this.state = { isLoaded: false }
  }
  componentDidMount = () => {
    this.getMasterData()
  }

  getMasterData = () => {
    this.setState({ isLoaded: false })
    WSManager.Rest(NC.baseURL + NC.GET_APP_MASTER_LIST, {}).then((ResponseJson) => {

      if (ResponseJson.response_code === NC.successCode) {
        moment.tz.setDefault(ResponseJson.data.timezone);
        HF.setMasterData(ResponseJson.data);
        WSManager.setKeyValueInLocal('LF_PRIVATE_CONTEST', ResponseJson.data.lf_private_contest);

        /*Start to save sports_list in local storage */
        var sports_list = [];
        _.map(ResponseJson.data.sports_list, function (item) {
          sports_list.push({
            value: item.sports_id,
            label: item.sports_name
          });
        });
        //  LS.set('sports_list', sports_list);
        HF.setSportsData(sports_list);
        /*End to save sports_list in local storage */

        /*Start to save language_list in local storage */
        let language_list = []
        _.map(ResponseJson.data.language_list, (language, idx) => {
          language_list.push({
            label: language,
            value: idx,
          })
        })
        //  LS.set('language_list', language_list);
        HF.setLanguageData(language_list);
        /*End to save language_list in local storage */

        HF.setLeaderboardData(ResponseJson.data.leaderboard ? ResponseJson.data.leaderboard : []);

        this.setState({ isLoaded: true })
      }
    }).catch((error) => {
      notify.show(NC.SYSTEM_ERROR, "error", 5000);
    })
  }

  render() {

    const PrivateRoute = ({ component: Component, ...rest }) => (
      <Route {...rest} render={(props) => (
        WSManager.loggedIn() == true
          ? <Component {...props} />
          : <Redirect to={{
            pathname: '/login',
            state: { from: props.location }
          }} />

      )} />
    )
    return (
      <HashRouter>
        {
          this.state.isLoaded ?
            <Switch>
              <Route exact path="/login" name="Login Page" component={Login} />
              <Route exact path="/forgot-password" name="Forgot Password Page" component={ForgotPassword} />
              <Route exact path="/401" name="Page 401" component={Page401} />
              <Route exact path="/404" name="Page 404" component={Page404} />
              <Route exact path="/500" name="Page 500" component={Page500} />
              <PrivateRoute path="/" name="Home" component={DefaultLayout} />
            </Switch>
            :
            <Loader className="app-load" />
        }
      </HashRouter>
    );
  }
}

export default App;
