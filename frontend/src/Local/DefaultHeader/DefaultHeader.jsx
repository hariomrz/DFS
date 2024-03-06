import React, { useEffect, useState, lazy, Suspense } from 'react';
import { Link } from "react-router-dom";
import { Trans } from 'react-i18next';
import _ from 'lodash';
import * as WSC from "../../WSHelper/WSConstants";
import WSManager from 'WSHelper/WSManager';
import { NavLink, withRouter } from 'react-router-dom';
import Images from 'components/images';
import { withRedux } from 'ReduxLib';
import { getAppNotificationCount, getUserBalance, logoutUser } from '../../WSHelper/WSCallings'
import { Utilities, _includes, _isEmpty } from 'Utilities/Utilities';
import Utils from 'Local/Helper/Utils';
import Auth from 'Local/Helper/Auth/Auth';
import ls from 'local-storage';
import { OnlyCoinsFlow } from 'helper/Constants';


const DefaultHeader = ({ root, i18n, location, history, t, ...rest }) => {  
  const { cms_page, currency_code, a_coin, leaderboard, default_lang } = Utilities.getMasterData();
  const MasterData = Utilities.getMasterData()
  const { nav, option, route } = root.headerProps;
  const { isShow, isSportsList, sport_prefix} = option
  const AvaSports = Utils.getSports()
  const [ntfCount, setNtfCount] = useState([])
  const [userBal, setUserBal] = useState([])
  const [profileDetail, setProfileDetail] = useState(ls.get('profile') || {})
  const [balTimestamp, setBalTimestamp] = useState(root.headerBalTimestamp)
  const [profileTimestamp, setProfileTimestamp] = useState(root.headerProfileTimestamp)
  const [notifTimestamp, setNotifTimestamp] = useState(root.headerNotifTimestamp)

  
  //wallet total
  const realAmount = parseFloat(userBal.real_amount) || 0;
  const bonusAmount = parseFloat(userBal.bonus_amount) || 0;
  const winningAmount = parseFloat(userBal.winning_amount) || 0;
  
  const walletAmount = (realAmount + bonusAmount + winningAmount).toFixed(2)
  
  //api call for getAppNotificationCount
  const getNotificationCount = async () => {

    let response = await getAppNotificationCount();
    let data = response.data;
    setNtfCount(data);
  }

  //api call for getUserBalance
  const getUserBal = async () => {
    try {
      let response = await getUserBalance();
      let data = response.data.user_balance
      Utils.setBalance(response.data.user_balance);
      setUserBal(data);
    } catch (error) {
      console.error("An error occurred:", error);
    }
  };

  useEffect(() => {
    if (root.isAuth || balTimestamp != root.headerBalTimestamp) {
      setBalTimestamp(root.headerBalTimestamp)
      getNotificationCount();
      getUserBal()
    }
  }, [root.isAuth, root.headerBalTimestamp]);

  useEffect(() => {
    if (root.isAuth && profileTimestamp != root.headerProfileTimestamp) {
      setProfileTimestamp(root.headerProfileTimestamp);
      setProfileDetail(ls.get('profile') || {})
    }
  }, [root.isAuth, root.headerProfileTimestamp]);

  useEffect(() => {
    if (root.isAuth && notifTimestamp != root.headerNotifTimestamp) {
      setNotifTimestamp(root.headerNotifTimestamp);
      setNtfCount(0)
    }
  }, [root.isAuth, root.headerNotifTimestamp]);



  const showNav = (item) => {
    return (item.pageType == '0' && !root.isAuth) || (item.pageType == '1') || (item.pageType == '2' && root.isAuth)
  }

  const handleLogout = () => {
    let param = {
        Sessionkey: Auth.getAuth()
    }
    logoutUser(param).then((responseJson) => {
        if (responseJson.response_code == WSC.successCode) {
            WSManager.logout();
        }
        setTimeout(() => {
        }, 200);
    })
}
//const history = useHistory(); 

  return isShow ? (
    <header className='default-header'>
      <div className="container">
        <div className="default-header-logo">
          {/* <NavLink to={  '/' + ALConstants.GAME_ROUTE[root.GameType] + Constant.DASHBOARD_PATH + '/' + Utils.getSelectedSports(true)} className="logo-icon"> */}
          <NavLink to={'/lobby'} className="logo-icon">
            <img src={Images.WHITE_BRAND_LOGO} alt="" />
          </NavLink>

        </div>
        <ul className="header-navigation">
          {
            _.map(nav, (item, idx) => {
              return item.child ?
                (
                  <li className='submenu' key={idx}>
                    <a><Trans>{item.name}</Trans> <i className="icon-arrow-down" /></a>
                    <div className="submenu-dropdown more-dropdown">
                      {
                        _.map(item.child, (child, _i) => {
                          return showNav(child) && (
                            <ul key={_i}>
                              {
                                child.name != '' &&
                                <>

                                  <li> 
                                    <Link to={child.path} exac={child.exact} className={child.className || item.className}>
                                      <Trans>{child.name}</Trans>
                                    </Link>
                                  </li></>
                              }
                              {
                                _.map(child.menu, (nav, _j) => {
                                  return (
                                    (MasterData[nav.page_key] == '1' || (_includes(cms_page, nav.page_key) || nav.page_key == '_')) && 
                                    <>
                                      <li> <Link to={nav.path} exac={nav.exact} className={nav.className}>
                                        <Trans>{nav.name}</Trans>
                                      </Link></li>
                                    </>
                                  )
                                })
                              }
                            </ul>
                          )
                        })
                      }
                    </div>
                  </li>
                )
                :
                (
                  showNav(item) &&
                  (item.key == 'global-leaderboard' ? !_isEmpty(leaderboard) : true)
                  &&
                  <li>
                    {
                      <NavLink to={item.path} exac={item.exact} 
                        isActive={(match, location) => {
                          if (_includes(location.pathname, item.key)) {
                            return true;
                          }
                          if (item.child) {
                            return location.pathname.startsWith(item.key);
                          }
                          return false;
                        }}
                      >
                        {item.key == 'game_hub_lobby' ? Utils.getGameTypeName() : t(item.name)}
                      </NavLink>
                    }
                  </li>
                )
            })
          }
        </ul>
        <div className="header-utility">

        </div>

        <div className='nav-item-right'>
          {
            Auth.getAuth() ?
              <>

                <div className='notification'>
                  <NavLink to="/notification" exact>
                    <i className='icon-alarm-new' />
                    {ntfCount !== 0 && <><span className='ntf-dot'></span><span className='ntf-count'>{ntfCount}</span></>}
                  </NavLink>
                </div>

                <div className='wallet'>
                  <span className='wallet-item'>
                    <NavLink to="/my-wallet" exact>
                      <i className='icon-wallet-ic' />
                      {currency_code} {Utilities.numberWithCommas(walletAmount) || '0'}
                      {
                        a_coin == '1' &&
                        <>
                            {" / "}
                          <>
                            <img src={Images.IC_COIN} /> {Utilities.numberWithCommas(userBal.point_balance) || '0'}
                          </>
                        </>
                      }
                    </NavLink>
                  </span>
                </div>

                <div className='profile-img' >
                  <img src={profileDetail.image !== '' ? Utilities.getThumbURL(profileDetail.image) : Images.DEFAULT_AVATAR} alt="" />
                  <div className='my-profile-logout'>
                    <ul>
                      <li><NavLink to="/my-profile" exact><Trans>MY PROFILE</Trans></NavLink></li>
                      <li><NavLink to="/" exact  onClick={handleLogout}><Trans>LOGOUT</Trans></NavLink></li>
                    </ul>
                  </div>
                </div>
              </>
              :
              <div>
                <NavLink className='signuplogin-btn' to={`/signup`}><Trans>Sign Up</Trans>/<Trans>Login</Trans></NavLink>
              </div>
          } 
        </div>
      </div>
      {
        isSportsList &&
        <div className="sports-header">
          <div className="container">
            <div className="sports-tab-container">
              <ul>
                {
                  _.map(AvaSports, (item, idx) => {
                    return (
                      <li key={idx} {...{
                        onClick: () => Utils.setSelectedSports(item.sports_id)
                      }}>
                        <NavLink to={`${sport_prefix}/${item.sports_id}`}>{item[i18n.language == 'en-US' ? 'en' : i18n.language]}</NavLink>
                      </li>
                    )
                  })
                }
              </ul>
            </div>
          </div>
        </div>
      }
    </header>
  ) : null;
};
const DefaultHeaderWrap = withRouter(DefaultHeader, { withRef: true })
export default withRedux(DefaultHeaderWrap);