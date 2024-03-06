import React, { useEffect } from 'react';
import { Route, Switch, Redirect } from "react-router-dom";
import { Constant, RenderRoute } from 'Local';
import { RouteMap } from 'OpinionTrade';
import Utils from 'Local/Helper/Utils/Utils';
const Provider = () => {
  const { route, header } = RouteMap

  return (
    <>
      <Switch>
        {/* {
          Utils.getPickedGameType() == 'opinion_trade_fantasy' &&
          <Redirect from="/opinion-trade/lobby" exact to={'/opinion-trade' + Constant.DASHBOARD_PATH + '/' + Utils.getSelectedSports()} />
        } */}
        {
          route.map((route, idx) => {
            return route.Component ?
              <Route {...{
                ...route,
                key: idx,
                render: (props) => <RenderRoute {...props} {...route} header={header} />
              }} />
              :
              null;
          })
        }
      </Switch>
    </>
  );
};

export default Provider;
