import React, { Component } from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import { Container,Col, Row } from 'reactstrap';

import {

  AppHeader,
  AppSidebar,
  AppSidebarForm,
  AppSidebarHeader,
  AppSidebarMinimizer,
  AppSidebarNav,
} from '@coreui/react';
// sidebar nav config
import navigation from '../../_nav';
import Notification from 'react-notify-toast';
// routes config
import routes from '../../routes';

import DefaultAside from './DefaultAside';
import DefaultFooter from './DefaultFooter';
import DefaultHeader from './DefaultHeader';
import WSManager from '../../helper/WSManager';

class DefaultLayout extends Component {

  render() {
    return (
      <div className="app">
      <Notification options={{ zIndex: 1060 }} />
        <AppHeader fixed>
          <DefaultHeader />
        </AppHeader>
        <div className="app-body">
          <AppSidebar fixed display="lg">
            <AppSidebarHeader />
            <AppSidebarForm />
            <AppSidebarNav navConfig={navigation} {...this.props} />
          </AppSidebar>
          <main className="main">
            <Container fluid>
              <Switch>
                {routes.map((route, idx) => {
                    return route.component ? (<Route key={idx} path={route.path} exact={route.exact} name={route.name} render={props => (
                        <route.component {...props} />
                      )} />)
                      : (null);
                  },
                )}
                {
                  WSManager.getRole() == 1 ?<Redirect from="/" to="/affiliate-list" /> : <Redirect from="/" to="/user-affiliate-list" />
                }
                
              </Switch>
              
            </Container>
          </main>
         
        </div>
      </div>
    );
  }
}

export default DefaultLayout;
