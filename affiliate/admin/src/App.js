import React, { Component } from 'react';
import { HashRouter, Route, Switch, Redirect } from 'react-router-dom';
import './App.css';
import '@coreui/icons/css/coreui-icons.min.css';
import 'flag-icon-css/css/flag-icon.min.css';
import 'font-awesome/css/font-awesome.min.css';
import 'simple-line-icons/css/simple-line-icons.css';
import './scss/style.scss'
import WSManager from "./helper/WSManager";
import { DefaultLayout } from './containers';
import { Login, Page401, Page404, Page500 } from './views/Pages';
import * as NC from "./helper/NetworkingConstants"

class App extends Component {

  componentDidMount = () => {
    const queryParams = new URLSearchParams(window.location.search);
    let _token = queryParams.get('token');
    let _role = queryParams.get('role');
    if (_token && _role) {
      WSManager.setToken(_token);
      WSManager.setRole(_role);
      // window.location.href = NC.baseURL+'/affiliate';
    }
  }

  render() {
      const PrivateRoute = ({ component: Component, ...rest }) => (
        <Route {...rest} render={(props) => (
          WSManager.getToken()
            ? <Component {...props} />
            : <Redirect to={{
              pathname: '/login',
              state: { from: props.location }
            }} />

        )} />
       
      )

    return (
      <HashRouter>
        <Switch>
          <Route exact path="/login" name="Login Page" component={Login} />
          <Route exact path="/401" name="Page 401" component={Page401} />
          <Route exact path="/404" name="Page 404" component={Page404} />
          <Route exact path="/500" name="Page 500" component={Page500} />
          <PrivateRoute path="/" name="Home" component={DefaultLayout} />
        </Switch>
      </HashRouter>
    );
  }
}

export default App;
