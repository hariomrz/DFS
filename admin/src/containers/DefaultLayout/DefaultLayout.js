import React, { Component } from 'react';
import { Redirect, Route, Switch } from 'react-router-dom';
import { Container } from 'reactstrap';
import { AppHeader, AppSidebar, AppSidebarForm, AppSidebarHeader, AppSidebarNav } from '@coreui/react';
import navigation from '../../_nav';
import navigation2 from '../../_nav2';
import Notification from 'react-notify-toast';
import routes from '../../routes';
import DefaultHeader from './DefaultHeader';
import WSManager from "../../helper/WSManager";
import HF from '../../helper/HelperFunction';
import _ from 'lodash';
class DefaultLayout extends Component {
  constructor(props) {
    super(props)
    this.state = {
      navPosting: false,
    }
  }

  removeItem = (idx) => {
    delete navigation.items[idx]
  }

  removeNavigation = (modAccess, accessName, idx) => {
    if (modAccess.includes(accessName)) {
      let msData = HF.getMasterData()    
      if (accessName == 'coins' && (_.isUndefined(msData.allow_coin) || msData.allow_coin == '0')) {
        this.removeItem(idx)
      } if (accessName == 'dfs' && (HF.allowDFS() == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'accounting' && (HF.allowDFS() == '0' && HF.allowLiveFantsy() == '0')) {
        this.removeItem(idx)
      }

      if (accessName == 'sports_predictor' && (_.isUndefined(msData.allow_prediction) || msData.allow_prediction == '0')) {
        this.removeItem(idx)
      }

      if (accessName == 'props_fantasy' && (_.isUndefined(msData.allow_props) || msData.allow_props == '0')) {
        this.removeItem(idx)
      }
      
      if (accessName == 'open_predictor_with_pool' && (_.isUndefined(msData.allow_open_predictor) || msData.allow_open_predictor == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'live_fantasy' && (_.isUndefined(msData.allow_lf) || msData.allow_lf == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'picks_fantasy' && (_.isUndefined(msData.allow_picks) || msData.allow_picks == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'open_predictor_with_prize' && (_.isUndefined(msData.allow_fixed_open_predictor) || msData.allow_fixed_open_predictor == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'distributors' && (_.isUndefined(msData.allow_distributor) || msData.allow_distributor == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'multigame' && (_.isUndefined(msData.allow_multigame) || msData.allow_multigame == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'free_to_play' && (_.isUndefined(msData.allow_free_to_play) || msData.allow_free_to_play == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'network_game' && (_.isUndefined(msData.allow_network_fantasy) || msData.allow_network_fantasy == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'pickem_tournament' && (_.isUndefined(msData.allow_pickem_tournament) || msData.allow_pickem_tournament == '0')) {
        this.removeItem(idx)
      }

      if (accessName == 'affiliate' && (_.isUndefined(msData.allow_affiliate) || msData.allow_affiliate == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'accounting' && (msData.allow_gst == '0' && msData.allow_tds == '0')
      ) {
        this.removeItem(idx)
      }

      if (accessName == 'private_contest' && (_.isUndefined(msData.allow_private_contest) || msData.allow_private_contest == '0')) {
        this.removeItem(idx)
      }

      if (accessName == 'deals' && (_.isUndefined(msData.allow_deal) || msData.allow_deal == '0')) {
        this.removeItem(idx)
      }

      if ((accessName == 'xp_module' && HF.allowXpPoints() == '0') || accessName == 'coins' && (_.isUndefined(msData.allow_coin) || msData.allow_coin == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'stock_fantasy' && HF.allowStockFantasy() == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'equity_stock_fantasy' && HF.allowEquityFantasy() == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'live_fantasy' && HF.allowFastKhelo() == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'picks_fantasy' && HF.allowPicksFantasy() == '0')) {
        this.removeItem(idx)

      }
      if ((accessName == 'pick_fantasy' && HF.allowPickFantasy() == '0')) {
        this.removeItem(idx)
      }

      if ((accessName == 'opinion_trade' && HF.allowOpentrade() == '0')) {
        this.removeItem(idx)
      }
      
      if ((accessName == 'stock_predict' && HF.allowStockPredict() == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'live_stock_fantasy' && HF.allowLiveStockFantasy() == '0')) {
        this.removeItem(idx)
      }
      if (accessName == 'new_affiliate' && (_.isUndefined(msData.new_affiliate) || msData.new_affiliate == '0')) {
        this.removeItem(idx)
      }
      if ((accessName == 'finance_erp' && HF.allowDFS() == '0')) {
        this.removeItem(idx)
      }
    }
  }

  removeChildNav = (c_menu_name, idx, cidx) => {
    let d_flag = false
    
    let msData = HF.getMasterData()

    
    let Hubpage = msData.hub_list;
    let  countHubpage = Object.keys(Hubpage).length;   

    if (!_.isUndefined(navigation.items[idx])) {
      // let msData = HF.getMasterData()

      if (c_menu_name === 'Spin the wheel' && (_.isUndefined(msData.allow_spin) || msData.allow_spin == '0') && (!_.isUndefined(msData.allow_coin) && msData.allow_coin == '1')) {
        d_flag = true
      } else if (!_.isUndefined(HF.getMasterData().lf_private_contest) && HF.getMasterData().lf_private_contest == '0' && (c_menu_name === 'Private Contest Dashboard' || c_menu_name === 'Private Contest Setting')) {
        d_flag = true
      }

      else if (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '0' && (c_menu_name === 'System User Reports' || c_menu_name === 'System users')) {
        d_flag = true
      }
      /**Start to remove international version nav */
      else if (HF.allowCoinOnly() == '1' && (c_menu_name === 'User Money Paid' || c_menu_name === 'User Deposit Amount' || c_menu_name === 'Withdrawal List')) {
        d_flag = true
      }
      /**End to remove international version nav */
      else if (HF.allowScratchWin() == '0' && (c_menu_name === 'Manage Reward')) {
        d_flag = true
      }
      else if (HF.allowSelfExclusion() == '0' && (c_menu_name === 'Self Exclude')) {
        d_flag = true
      }
      else if (HF.allowRefLeaderboard() == '0' && (c_menu_name === 'Referral Setprize' || c_menu_name === 'Referral Leaderboard')) {
        d_flag = true
      }
      else if (c_menu_name === 'Buy Coins' && (_.isUndefined(msData.allow_buy_coin) || msData.allow_buy_coin == '0') && (!_.isUndefined(msData.allow_coin) && msData.allow_coin == '1')) {
        d_flag = true
      }
      else if (HF.allowBooster() == '0' && (c_menu_name === 'Booster Configuration')) {
        d_flag = true
      }
      else if ((HF.allowStockFantasy() == '0' && HF.allowEquityFantasy() == '0' && HF.allowLiveStockFantasy() == '0') && (c_menu_name === 'Stock Report')) {
        d_flag = true
      }
      else if (HF.allowRookieContest() == '0' && (c_menu_name === 'Manage Rookie')) {
        d_flag = true
      }
      else if ((msData.allow_coin == '0' || HF.allowSubscription() == '0') && (c_menu_name === 'Subscription')) {
        d_flag = true
      }
      else if (c_menu_name === 'Quiz' && (_.isUndefined(HF.allowQuiz()) || HF.allowQuiz() == '0') && (!_.isUndefined(msData.allow_coin) && msData.allow_coin == '1')) {
        d_flag = true
      }
      else if (HF.allowH2H() == '0' && (c_menu_name === 'Manage H2H Challenge')) {
        d_flag = true
      }
      else if (msData.allow_bs == "0" && (c_menu_name === 'Banned states configuration')) {
        d_flag = true
      }
       else if (HF.getMasterData().allow_app_qr == 0 && (c_menu_name === 'Mobile App')) {
         d_flag = true
       }

      else if (countHubpage  <=  2  && (c_menu_name === 'Hub Page')) {
        d_flag = true
      }

      
      if (d_flag) {
        delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Spin the wheel' && !_.isUndefined(msData.allow_spin) && msData.allow_spin == '0' && !_.isUndefined(msData.allow_coin) && msData.allow_coin == '1') {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Winnings Leaderboard' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Participant Report' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Player Management' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Teams Leaderboard' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Add User' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'System users' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Lobby' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }

      if (c_menu_name === 'Contest Report' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      // if (c_menu_name === 'Hub Page' && (HF.allowDFS() == '0')) {
      //   if (!_.isUndefined(navigation.items[idx]))
      //     delete navigation.items[idx]['children'][cidx]
      // }
      if (c_menu_name === 'Match Report' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'User Money Paid' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Teams' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if (c_menu_name === 'Manage Scoring' && (HF.allowDFS() == '0')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      // if (c_menu_name === 'Report' && (HF.allowDFS()== '0')) {
      //   if (!_.isUndefined(navigation.items[idx]))
      //     delete navigation.items[idx]['children'][cidx]
      // }


      if (!_.isUndefined(HF.getMasterData().pl_allow) && HF.getMasterData().pl_allow == '0' && (c_menu_name === 'System User Reports' || c_menu_name === 'System users')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      /**Start to remove international version nav */
      if (HF.allowCoinOnly() == '1' && (c_menu_name === 'User Money Paid' || c_menu_name === 'User Deposit Amount' || c_menu_name === 'Withdrawal List' || c_menu_name === 'Withdrawal Leaderboard' || c_menu_name === 'Depositors Leaderboard')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }


      if (HF.allowRefLeaderboard() == '0' && (c_menu_name === 'Referral Setprize' || c_menu_name === 'Referral Leaderboard')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }
      if ((!HF.getMasterData().leaderboard || HF.getMasterData().leaderboard.length == 0) && (c_menu_name === 'Marketing Leaderboard')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }

      if (c_menu_name === 'Buy Coins' && (_.isUndefined(msData.allow_buy_coin) || msData.allow_buy_coin == '0') && (!_.isUndefined(msData.allow_coin) && msData.allow_coin == '1')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }

      if ((!HF.getMasterData().leaderboard || HF.getMasterData().leaderboard.length == 0) && (c_menu_name === 'Marketing Leaderboard')) {
        if (!_.isUndefined(navigation.items[idx]))
          delete navigation.items[idx]['children'][cidx]
      }

    }
    if ((c_menu_name === 'GST Report') && HF.allowGst() == '0') {
      if (!_.isUndefined(navigation.items[idx]))
        delete navigation.items[idx]['children'][cidx]
    }

    if ((c_menu_name === 'TDS Report') && HF.allowTds() == '0') {
      if (!_.isUndefined(navigation.items[idx]))
        delete navigation.items[idx]['children'][cidx]
    }
  }

  componentDidMount = () => {
    /*Start code for admin role access*/
    let modAccess = WSManager.getKeyValueInLocal("module_access")
    _.map(navigation.items, (navItem, idx) => {
      let smallName = navItem ? navItem.name.toLowerCase() : ''
      let accessName = navItem ? smallName.replace(/ /g, '_') : ''
      if (!_.isNull(modAccess) && !_.isUndefined(modAccess)) {
        if (accessName.includes("'")) {
          accessName = accessName.replace("'", '')
        }
        if (accessName !== 'change_password') {
          if (accessName == "user_management" && modAccess.includes('user_wallet_manage')) {
            //show  User management
          }
          else if (!modAccess.includes(accessName)) {
            delete navigation.items[idx]
          }
          /**Start code to remove if not allowed from backend */
          this.removeNavigation(modAccess, accessName, idx)
          /**End code to remove if not allowed from backend */
        }

        if (!_.isEmpty(navItem) && !_.isUndefined(navItem.children)) {
          _.map(navItem.children, (navChild, cidx) => {
            if (!_.isUndefined(navChild) && !_.isUndefined(navChild.name)) {
              if (!_.isUndefined(navChild) && !_.isUndefined(navChild.name) && navChild.name === 'Create Tournament' && !_.isUndefined(HF.getMasterData().allow_dfs_tournament) && HF.getMasterData().allow_dfs_tournament == '0') {
                if (!_.isUndefined(navigation.items[idx]))
                  delete navigation.items[idx]['children'][cidx]
              }
              /**Start code to remove chield menu if not allowed from backend */
              this.removeChildNav(navChild.name, idx, cidx)
              /**End code to remove chield menu if not allowed from backend */
            }
          })
        }

      }
    })
    /*End code for admin role access*/
    this.setState({ navPosting: true })
  }


render() {
  return (
    <div className="app">
      <Notification options={{ zIndex: 1060 }} />
      <AppHeader fixed>
        <DefaultHeader />
      </AppHeader>
      <div className="app-body">
        {
          this.state.navPosting &&
          <AppSidebar fixed display="lg">
            <AppSidebarHeader />
            <AppSidebarForm />
            <AppSidebarNav className="animate-bottom" navConfig={(WSManager.getRole() == 1) ? navigation : navigation2} {...this.props} />
          </AppSidebar>
        }
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
              {/* <Redirect from="/" to="/dashboard" /> */}
              <Redirect from="/" to="/landing-screen" />
            </Switch>

          </Container>
        </main>
      </div>
    </div>
  );
}
}

export default DefaultLayout;
