(window.webpackJsonp=window.webpackJsonp||[]).push([[27],{1060:function(e,t,a){"use strict";var n=a(18),o=a(33),i=a(135),c=a(134),r=a(136),s=a(4),l=a.n(s),d=a(944),p=a(1588),m=(a(954),a(137)),u=(a(940),a(6)),_=(a(955),a(956),a(1)),b=a(938),h=a(0),g=a(97),f=a(941),E=function(e){function t(e){var a;return Object(n.a)(this,t),(a=Object(i.a)(this,Object(c.a)(t).call(this,e))).submitPrediction=function(){var e=a.props.preData,t=e.mHide,n=e.cpData,o=e.successAction,i=parseInt(a.state.point_balance),c=0==n.entry_type?parseInt(a.state.bidAmount):parseInt(n.entry_fee),r=i-c,s={prediction_master_id:n.prediction_master_id,prediction_option_id:n.option_predicted.prediction_option_id,bet_coins:c};a.setState({isLoading:!0}),Object(m.Jd)(s).then(function(e){if(e.response_code===h.bf){f.a.updateCoinBalance(r);var i=_.a.getBalance();i.point_balance=r,_.a.setBalance(i),u.b.showToast(e.message,3e3,b.a.PREDICTION_IC),o(n),a.setState({isLoading:!1},function(){t()})}else a.setState({isLoading:!1})})},a.clickEarnCoins=function(){_.a.loggedIn()?a.props.history.push("/earn-coins"):a.goToSignup()},a.goToSignup=function(){a.props.history.push("/signup")},a.state={bidAmount:"",minCoin:parseInt(u.b.getMasterData().min_bet_coins||10),maxCoin:parseInt(u.b.getMasterData().max_bet_coins||9999),isLoading:!1,point_balance:_.a.getBalance().point_balance||0,refreshField:!0},a}return Object(r.a)(t,e),Object(o.a)(t,[{key:"UNSAFE_componentWillMount",value:function(){var e=this;document.addEventListener("keydown",u.g,!1),Object(m.kd)().then(function(t){t.response_code===h.bf&&(e.setState({point_balance:t.data.user_balance.point_balance||0}),_.a.setAllowedBonusPercantage(t.data.allowed_bonus_percantage),_.a.setBalance(t.data.user_balance))})}},{key:"componentWillUnmount",value:function(){document.removeEventListener("keydown",u.g)}},{key:"render",value:function(){var e=this,t=this.props.preData,a=t.mShow,n=t.mHide,o=t.cpData,i=this.state,c=i.bidAmount,r=i.minCoin,s=(i.isLoading,i.point_balance),m=(o.deadline_time,parseInt(s||0));1==o.entry_type&&o.entry_fee;return l.a.createElement(d.a.Consumer,null,function(t){return l.a.createElement(p.a,{show:a,onHide:n,dialogClassName:"modal-pred-confirm fpp-pred-confirm",className:"center-modal"},l.a.createElement(p.a.Body,null,l.a.createElement("div",{className:"container"},l.a.createElement("p",{className:"pred-que"},o.desc),o&&o.source_desc&&l.a.createElement("p",{className:"pred-desc"},g.Md," - ",o.source_desc),l.a.createElement("div",{className:"your-pre-text"},l.a.createElement("div",null,g.G,l.a.createElement("span",{className:"option"}," ",o.option_predicted.option),"?")),l.a.createElement("p",{className:"pred-desc"},g.Om),(m<r||m<c||1==o.entry_type&&m<o.entry_fee)&&l.a.createElement("span",{className:"no-coins-msg"},g.Zj,l.a.createElement("a",{href:!0,onClick:e.clickEarnCoins},g.Vd.toLowerCase())),l.a.createElement("button",{onClick:e.submitPrediction,className:"btn btn-m-p"},g.gq))))})}}]),t}(s.Component);t.a=E},1572:function(e,t,a){"use strict";a.r(t);var n=a(18),o=a(33),i=a(135),c=a(134),r=a(136),s=a(4),l=a.n(s),d=a(1593),p=a(1557),m=a(944),u=a(6),_=a(137),b=a(940),h=a(942),g=a.n(h),f=a(1060),E=a(943),y=a(1),v=a(941),N=a(956),k=a(938),w=a(0),C=a(97),S=a(9),D=function(e){function t(e){var a;return Object(n.a)(this,t),(a=Object(i.a)(this,Object(c.a)(t).call(this,e))).onSelectPredict=function(e,t){var n=a.state.detail;Object(u.c)(n.option,function(a,o){o===e?(a.user_selected_option=t.prediction_option_id,n.option_predicted=t):a.user_selected_option=null}),a.setState({detail:n},function(){setTimeout(function(){a.onMakePrediction()},50)})},a.onMakePrediction=function(){y.a.loggedIn()?a.setState({showCP:!0}):a.goToSignup()},a.hideCP=function(){a.setState({showCP:!1})},a.goToSignup=function(){var e=a.props.match.params,t="/"+u.b.getSelectedSportsForUrl().toLowerCase()+"/open-predictor-leaderboard-details/"+e.category_id+"/"+e.prediction_master_id;a.props.history.push({pathname:"/signup",state:{joinContest:!0,lineupPath:t,FixturedContest:a.state.LData,LobyyData:a.state.LData}})},a.timerCallback=function(){},a.gotoLobby=function(){a.props.history.push("/lobby#"+u.b.getSelectedSportsForUrl()+"#open-predictor-leaderboard")},a.successAction=function(){setTimeout(function(){a.props.history.length>2?a.props.history.goBack():a.gotoLobby()},1500)},a.renderFilledBar=function(e,t){var n=a.state.detail,o=0===n.total_predictions?0:(e.option_total_coins/n.total_pool*100).toFixed(2);o=o%1===0?Math.floor(o):o;var i=e.user_selected_option===e.prediction_option_id;return l.a.createElement("div",{key:t,onClick:function(){return a.onSelectPredict(t,e)},className:"prediction-bar"+(i?" selected":"")},l.a.createElement("div",{className:"filled-bar",style:{width:1==n.entry_type?i?"100%":"0":o+"%",animationDelay:.05*t+"s"}}),l.a.createElement("p",{className:"answer"},e.option),0==n.entry_type&&l.a.createElement("div",{className:"corrected-ans"},l.a.createElement("p",null,o>0?o+"%":"")))},a.state={HOS:{back:a.props.history.length>2,fixture:!1,title:"",hideShadow:!1,MLogo:!0,isPrimary:!S.n},LData:"",detail:"",showCP:!1,sourceUrlShow:!1},a}return Object(r.a)(t,e),Object(o.a)(t,[{key:"UNSAFE_componentWillMount",value:function(){if(y.a.setShareContestJoin(!0),y.a.setPickedGameType(S.r.OpenPred),this.props.match&&this.props.match.params){var e=this.props.match.params,t=atob(e.prediction_master_id);this.getDetail(e.category_id,t)}}},{key:"getDetail",value:function(e,t){var a=this,n={category_id:e,prediction_master_id:t};Object(_.ub)(n).then(function(e){e.response_code===w.bf&&(e.data.prediction?a.setState({detail:e.data.prediction[0]||"",LData:e.data.category_data}):(u.b.showToast(C.tn,1e3),setTimeout(function(){a.props.history.length>2?a.props.history.goBack():a.gotoLobby()},1e3)))})}},{key:"callNativeRedirection",value:function(e){var t={action:"predictionLink",targetFunc:"predictionLink",type:"link",url:e.source_url,detail:e};window.ReactNativeWebView.postMessage(JSON.stringify(t))}},{key:"render",value:function(){var e=this,t=this.state,a=t.HOS,n=t.LData,o=t.detail,i=t.showCP,c=o.deadline_time/1e3,r=0;return l.a.createElement(m.a.Consumer,null,function(t){return l.a.createElement("div",{className:"web-container prediction-detail-wrap"},l.a.createElement("img",{className:"bg-c-img",src:k.a.OPEN_CARD_IMG_DETAIL,alt:""}),l.a.createElement(g.a,{titleTemplate:"".concat(E.a.template," | %s")},l.a.createElement("title",null,E.a.PRDSHARE.title),l.a.createElement("meta",{name:"description",content:E.a.PRDSHARE.description}),l.a.createElement("meta",{name:"keywords",content:E.a.PRDSHARE.keywords})),l.a.createElement(v.a,Object.assign({},e.props,{LobyyData:n,HeaderOption:a,openPage:e.openPage})),""!==o&&l.a.createElement("div",{className:"pred-detail-v "},l.a.createElement("p",{className:"questions"},o.desc),(o.source_desc||o.source_url)&&l.a.createElement("div",{className:"que-desc"},o.source_url&&l.a.createElement(l.a.Fragment,null,window.ReactNativeWebView?l.a.createElement("a",{href:!0,onClick:function(){return e.callNativeRedirection(o)},className:"attached-url"},l.a.createElement("i",{className:"icon-link"})):l.a.createElement("a",{href:o.source_url,target:"_blank",className:"attached-url "},l.a.createElement("i",{className:"icon-link"}))),o.source_desc&&l.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"right",overlay:l.a.createElement(p.a,{id:"tooltip"},l.a.createElement("strong",null,o.source_desc))},l.a.createElement("i",{className:"icon-ic-info que-info"}))),Object(u.c)(o.option,function(t,a){return r=t.user_selected_option===t.prediction_option_id?t.bet_coins:r,e.renderFilledBar(t,a)}),l.a.createElement("div",{className:"footer-vc"},l.a.createElement("div",{className:"match-timing league-n"},l.a.createElement("div",{className:"leag-name"},n.name)),l.a.createElement("div",null,l.a.createElement("div",{className:"date-v"},l.a.createElement("div",{className:"match-timing"},u.b.showCountDown({game_starts_in:c})?l.a.createElement("span",{className:"d-flex"},l.a.createElement("div",{className:"countdown time-line"},c&&l.a.createElement(N.a,{timerCallback:e.timerCallback,deadlineTimeStamp:c})),C.do):l.a.createElement("span",null," ",l.a.createElement(b.g,{data:{date:o.deadline_date,format:"D MMM - hh:mm A "}}))))),l.a.createElement("div",{className:"lobby-go"},e.props.history.length<=2&&l.a.createElement("a",{href:!0,onClick:e.gotoLobby},C.cg)))),i&&l.a.createElement(f.a,Object.assign({},e.props,{preData:{mShow:i,mHide:e.hideCP,cpData:o,successAction:e.successAction}})))})}}]),t}(s.Component);t.default=D}}]);