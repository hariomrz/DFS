import React, { useCallback, useEffect, useState } from 'react';
import { Redirect } from "react-router-dom";
import { Constant } from 'Local'
import { Helper } from 'Local'
import { withRedux } from 'ReduxLib';
const { Auth } = Helper;
const RenderRoute = (props) => {
  const { className, Component, pageType, meta, option, exact, header, path, others, ...rest } = props
  const { actions, root } = rest;
  const [isAuth, setAuth] = useState(Auth.getAuth())

  useEffect(() => {
    actions.setHeaderProps({
      nav: [...header.nav, root.HeaderMore],
      option: { ...header.option, ...(option ? option : {}) },
      // others:{...header.others,...(others ? others : {})}
    });
    if (className) document.documentElement.setAttribute("class", className);
    return () => {
      document.documentElement.removeAttribute("class");
    }
  }, [header, option, root.HeaderMore])


  useEffect(() => {
    setAuth(Auth.getAuth())
  }, [root.isAuth])

  return (
    (pageType == 1 || (pageType == 0 && !isAuth) || (pageType == 2 && isAuth)) ?
        <Component {...rest} pageType={pageType} />
      :
      <Redirect from="*" to={isAuth ? Constant.DASHBOARD_PATH : Constant.DEFAULT_ROOT} />
  )
}
export default withRedux(RenderRoute);
