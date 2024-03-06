import React, { useEffect, lazy, useState  } from 'react';
import { Redirect, Route, Switch, useLocation, withRouter} from "react-router-dom";
import { RenderRoute, RouteMap } from 'Local'
// import { Provider as DFS } from 'DFS';
import { Provider as OpinionTrade } from 'OpinionTrade';
import { Provider } from 'react-redux';
import { store, withRedux } from 'ReduxLib';
import ls from 'local-storage';
import Auth from 'Local/Helper/Auth';
import { GAME_ROUTE, IS_SPORTS_HUB, SELECTED_GAMET } from 'helper/Constants';
import * as WSC from "WSHelper/WSConstants";

import { BanStateModal, RGIModal } from 'Modals';
import { Utilities } from 'Utilities/Utilities';
import WSManager from 'WSHelper/WSManager';
import { getDailyCoins } from 'WSHelper/WSCallings';
const Banner = lazy(() => import('Modals/Banner'));
const DailyCheckinBonus = lazy(() => import('Component/CoinsModule/DailyCheckinBonus'));

const Layout = (props) => {

  const { actions, root } = props
  const { GameType } = root
  const { route, header, more} = RouteMap

  useEffect(() => {
    document.body.classList.add('desktop-wrap');
    actions.setAuth(Auth.getAuth())
    actions.setHeaderMore(more)
    return () => {
      document.body.classList.remove('desktop-wrap');
    }
  }, [])


  return (
      <Provider store={store}>
          <main className='default-container container desktop-container'>
            <Switch>
              {
                route.map((route, idx) => {
                  return route.redirect ? 
                  <Redirect exact from={route.from} to={route.to} />
                  :
                  route.Component ?
                    <Route {...{
                      ...route,
                      key: idx,
                      render: (props) => <RenderRoute {...props} {...route} header={header} />
                    }} />
                    :
                    null;
                })
              }
            <Redirect from="/lobby" to={`/${GAME_ROUTE[GameType] ? (GAME_ROUTE[GameType] + '/') : ''}lobby`} />
            </Switch>
            {/* <DFS /> */}
            <OpinionTrade />
            <RenderCurrentModal {...props}/>
          </main>
      </Provider>
  );
};

export default withRedux(Layout);



const RenderCurrentModal = withRouter(withRedux((props) => {
  const { actions, root } = props
  const { showBanStateModal } = root

  const Location = useLocation()
  const [currentPath, setCurrentPath] = useState(Location.pathname);
  const [isCheckInInit, setCheckInInit] = useState(true);

  const { a_coin, daily_streak_bonus, allow_self_exclusion, banner, a_aadhar } = Utilities.getMasterData()
  const [currentModal, setCurrentModal] = useState(0);
  const totalModals = 4;


  useEffect(() => {
    const { pathname } = Location;
    setCurrentPath(pathname);
    setCurrentModal(0)
  }, [Location.pathname]);


  useEffect(() => {
    if (Auth.getAuth()) {
      dailyCheckinInit()
    }
    return () => { }
  }, [Auth.getAuth()])

  /**
   * States for Daily Checkin
   */
  const [ApiCalled, setApiCalled] = useState(a_coin !== "0" ? false : true)
  const [dailyData, setDailyData] = useState('')
  const [showDCBM, setShowDCBM] = useState(false);

  /**
   * 
   * @description Method for Daily Checkin
   */
  const dailyCheckinInit = () => {
    let isSporthub = IS_SPORTS_HUB ? SELECTED_GAMET : true;
    if (isSporthub && a_coin !== "0" && !WSManager.getShareContestJoin()) {
      if (daily_streak_bonus !== "0") {
        let todayString = new Date().toDateString();
        console.log(WSManager.getDailyData(), 'dailyCheckinInit');
        if (WSManager.getDailyData().day_string !== todayString) {
          getDailyCoins({}).then(({ response_code, data }) => {
            if (response_code == WSC.successCode) {
              setDailyData(data)
              setShowDCBM(data.allow_claim === 1)
              WSManager.setDailyData(data)
            }
            setApiCalled(true)
          })
        } else {
          setDailyData(WSManager.getDailyData())
          setShowDCBM(WSManager.getDailyData().allow_claim === 1)
          setApiCalled(true)
        }
      } else {
        setApiCalled(true)
      }
    } else {
      setApiCalled(true)
    }
  }

  const dailyCheckinClose = (data) => {
    setDailyData(data)
    setShowDCBM(data.allow_claim === 1)
  }
  /**
    * 
    * @description Close Modal 
    */
  const closeModal = () => {
    setCurrentModal((prevModal) => {
      if (prevModal < totalModals - 1) {
        return prevModal + 1;
      } else {
        // All modals are closed, reset to the first modal
        return 0;
      }
    });
  };

  
  if (!ApiCalled || !Auth.getAuth()) return null;
  switch (currentModal) {
    case 0:
      if (a_coin !== "0") {
        if (showDCBM) {
          return (
            <DailyCheckinBonus {...props} preData={{
              dailyData: dailyData,
              mShow: true,
              mHide: () => [closeModal(), dailyCheckinClose(dailyData)]
            }} />
          );
        } else {
          closeModal()
        }
      } else {
        closeModal()
      }
      return null;

    case 1:
      if (allow_self_exclusion == 1 ? ((ls.get('RGMshow') || 0) == 1 ? false : true) : false) {
        return (
          <RGIModal
            showM={true}
            hideM={() => [closeModal(), ls.set('RGMshow', 1)]}
          />
        );
      } else {
        closeModal()
      }
      return null;

    case 2:
      let lsBannerData = WSManager.getBannerData();
      let mdBannerData = banner;
      if (mdBannerData && (!lsBannerData || lsBannerData.app_banner_id != mdBannerData.app_banner_id)) {
        return (
          <Banner
            {...props}
            isBannerShow={true}
            onBannerHide={() => [closeModal(), WSManager.setBannerData(banner)]}
          />
        );
      } else {
        closeModal()
      }

      return null;
   
    default:
      if (showBanStateModal && !WSManager.getProfile().master_state_id && a_aadhar != "1" && (currentPath.includes('/lobby') || currentPath.includes('/roster'))) {
        return (
          <BanStateModal
            {...props}
            mShow={showBanStateModal}
            mHide={() => [actions.modalToggle({ name: 'showBanStateModal', action: false }), closeModal()]}
            banStateData={{}}
            backDisabled={true}
          />
        )
      } else return null;
  }
}));