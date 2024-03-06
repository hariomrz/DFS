import Utils from 'Local/Helper/Utils/Utils';
import React from 'react';
import Loadable from "react-loadable";

function Loading(props) {
  if (props.error) {
    return <div>Error! <button onClick={props.retry}>Retry</button></div>;
  } else if (props.timedOut) {
    return <div>Taking a long time... <button onClick={props.retry}>Retry</button></div>;
  } else if (props.pastDelay) {
    return <div>Loading...</div>;
  } else {
    return null;
  }
}
const ViewCompletedEntries = Loadable({
  loader: () => import("../View/ViewCompletedEntries"),
  loading: Loading
});


const RouteMap = {
  header: {
    nav: [
      {
        name: 'GAMESHUB',
        path: '/sports-hub',
        key: 'gameshub',
        pageType: 1,
        exact: true
      },
      
    ],
    option: {
      isShow: true,
      isFooterShow: true,
      isSportsList: false
    }
  },

  route: [
    
    {
      path: "/completed-entries/:sports_id",
      name: "ViewCompletedEntries",
      Component: ViewCompletedEntries,
      exact: true,
      pageType: 1, // 0 = Public, 1 = Common, 2 = Private
      meta: {
        title: "Completed My Entries",
        description: "",
      },
      className: '',
      theme: {},
      option: {

      }
    },
    
    
  ]
}
export default RouteMap;
