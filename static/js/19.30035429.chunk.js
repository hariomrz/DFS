(window.webpackJsonp=window.webpackJsonp||[]).push([[19],{1228:function(e,a,t){"use strict";t.r(a);var l=t(25),n=t.n(l),s=t(42),r=t(18),c=t(33),m=t(135),i=t(134),o=t(136),d=t(4),u=t.n(d),p=t(1588),E=t(6),b=t(137),v=t(940),y=t(97),N=t(9),w=t(40),h=function(e){function a(e,t){var l;return Object(r.a)(this,a),(l=Object(m.a)(this,Object(i.a)(a).call(this,e,t))).componentDidMount=function(){l.getPlayerDetails()},l.getPlayerDetails=Object(s.a)(n.a.mark(function e(){var a,t;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return a={league_id:l.state.playerDetails.league_id,player_team_id:l.state.selectedGame.player_team_id},l.setState({isLoading:!0}),e.next=4,Object(b.Mc)(a);case 4:t=e.sent,l.setState({isLoading:!1}),t&&l.setState({playerDetails:t});case 7:case"end":return e.stop()}},e)})),l.state={playerDetails:e.playerDetails||{},team_abbr:e.team_abbr||{},selectedGame:e.selectedGame||{},isLoading:!1},l}return Object(o.a)(a,e),Object(c.a)(a,[{key:"componentWillReceiveProps",value:function(e){var a=this;e&&e.selectedGame!=this.props.selectedGame&&this.setState({playerDetails:e.playerDetails||{},team_abbr:e.team_abbr||{},selectedGame:e.selectedGame||{},isLoading:!1},function(){a.getPlayerDetails()})}},{key:"render",value:function(){var e=this.props,a=e.IsPlayerCardShow,t=e.IsPlayerCardHide,l=this.state,n=l.playerDetails,s=l.isLoading,r=l.team_abbr;return u.a.createElement(p.a,{show:a,bsSize:"large",dialogClassName:"modal-full-screen",className:"modal-pre-lm new-player-card player-break-down"},u.a.createElement(p.a.Body,null,u.a.createElement("div",{className:"close-header"},u.a.createElement("a",{href:!0,onClick:t},u.a.createElement("i",{className:"icon-close"}))),u.a.createElement("div",{className:"playercard-header"},u.a.createElement("div",{className:"player-self-detail"},u.a.createElement("span",{className:"l-name"},n.full_name),n.home&&u.a.createElement("span",{className:"fixture-name"},u.a.createElement("span",{className:r.toLowerCase()==(n.home||"").toLowerCase()?"active":""},n.home)," ",y.as," ",u.a.createElement("span",{className:r.toLowerCase()==(n.away||"").toLowerCase()?"active":""},n.away)," ","7"==N.d&&n.format?"("+N.C[n.format]+")":""),u.a.createElement("span",{className:"league-name"},n.league_name?n.league_name+", ":"",u.a.createElement("span",null,u.a.createElement(v.g,{data:{date:n.scheduled_date,format:"DD MMM - YYYY"}})))),u.a.createElement("ul",{className:"list-player-detail"},u.a.createElement("li",null,u.a.createElement("h4",null,n.position),u.a.createElement("span",null,y.uo)),u.a.createElement("li",null,u.a.createElement("h4",null,n.salary||0),u.a.createElement("span",null,y.ad)),u.a.createElement("li",null,u.a.createElement("h4",null,n.player_value||0),u.a.createElement("span",null,y.cm," ",y.Jr)))),u.a.createElement("div",{className:"break-down-l"},u.a.createElement("div",{className:"break-down-h"},u.a.createElement("div",{className:"item-n max"},y.Re),u.a.createElement("div",{className:"item-n"},y.d),u.a.createElement("div",{className:"item-n"},y.rm))),!s&&u.a.createElement(u.a.Fragment,null,Object(E.c)(Object.keys(n.break_down||{}),function(e){var a=e.replace("_"," ");return u.a.createElement("div",{key:e,className:"break-down-l"},u.a.createElement("div",{className:"strip-v"},a),Object(E.c)(n.break_down[e],function(e){return u.a.createElement("div",{key:e.name,className:"break-down-h sub"},u.a.createElement("div",{className:"item-n max"},e.name),u.a.createElement("div",{className:"item-n"},e.points),u.a.createElement("div",{className:"item-n"},e.score))}))}),u.a.createElement("div",{className:"total-footer"},u.a.createElement("span",null,y.Pq),u.a.createElement("span",{className:"max"},n.score)),w.isMobileSafari&&u.a.createElement("div",{className:"mob-browser-support"}))))}}]),a}(u.a.Component);a.default=h}}]);