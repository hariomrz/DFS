(window.webpackJsonp=window.webpackJsonp||[]).push([[43],{1126:function(e,t,a){"use strict";a.r(t);var n=a(948),s=a(18),r=a(33),i=a(135),c=a(134),l=a(136),m=a(4),o=a.n(m),d=a(944),p=a(940),h=a(137),u=a(6),E=a(951),O=a(945),N=a.n(O),_=a(942),I=a.n(_),v=a(943),S=a(941),L=a(938),g=a(0),P=a(97),b=a(9),f=function(e){function t(e){var a;return Object(s.a)(this,t),(a=Object(i.a)(this,Object(c.a)(t).call(this,e))).renderShimmer=function(e){return o.a.createElement("div",{key:e,className:"list-item"},o.a.createElement("span",{className:"shimmer"},o.a.createElement(N.a,{height:6,width:"90%"}),o.a.createElement(N.a,{height:4,width:"50%"})),o.a.createElement("span",{className:"amount"},o.a.createElement(N.a,{height:6,width:"30%"})),o.a.createElement("span",{className:"amount"},o.a.createElement(N.a,{height:6,width:"40%"})))},a.renderItem=function(e,t,n){return o.a.createElement("div",{key:e.user_id+n,id:e.user_id+n,className:"list-item"+(t?" own-v":"")},a.state.isLeader&&o.a.createElement("span",{className:"u-rank"},e.user_rank),o.a.createElement("span",null,e.user_name),o.a.createElement("span",{className:"amount"},o.a.createElement("img",{src:L.a.IC_COIN,alt:""}),o.a.createElement("div",{className:"val"},e.bet_coins)),o.a.createElement("span",{className:"amount"},o.a.createElement("img",{src:L.a.IC_COIN,alt:""}),o.a.createElement("div",{className:"val"},(a.state.isLeader?e.win_coins:e.estimated_winning)||0)))},a.state={PLIST:[],OWNDATA:"",PNO:1,PSIZE:20,HMORE:!1,ISLOAD:!1,PMID:"",isLeader:!1,HOS:{back:!0,title:"Participants",isPrimary:!b.n}},a}return Object(l.a)(t,e),Object(r.a)(t,[{key:"UNSAFE_componentWillMount",value:function(){var e=this;if(this.props.match&&this.props.match.params){var t=this.props.match.params,a=atob(t.prediction_master_id);this.setState({PMID:a,isLeader:!(!this.props.location||!this.props.location.state)&&this.props.location.state.isLeader},function(){e.getDetail()})}}},{key:"getDetail",value:function(){var e=this,t=this.state,a=t.PNO,s=t.PSIZE,r=t.PLIST,i=t.PMID,c=t.OWNDATA,l=t.isLeader,m={prediction_master_id:i,page_no:a,page_size:s,isLeader:l};this.setState({ISLOAD:!0}),Object(h.vb)(m).then(function(t){if(e.setState({ISLOAD:!1}),t.response_code===g.bf){var i=t.data.own||"",l=t.data.other_list||[];e.setState({PLIST:[].concat(Object(n.a)(r),Object(n.a)(l)),OWNDATA:1===a?i:c,HMORE:l.length>=s-(i||c?1:0),PNO:a+1})}})}},{key:"render",value:function(){var e=this,t=this.state,a=t.PLIST,n=t.HOS,s=t.ISLOAD,r=t.OWNDATA,i=t.HMORE,c=t.isLeader;return o.a.createElement(d.a.Consumer,null,function(t){return o.a.createElement("div",{className:"web-container prediction-part-v"+(c?" is-leaderboard":"")},o.a.createElement(I.a,{titleTemplate:"".concat(v.a.template," | %s")},o.a.createElement("title",null,v.a.PRDPLIST.title),o.a.createElement("meta",{name:"description",content:v.a.PRDPLIST.description}),o.a.createElement("meta",{name:"keywords",content:v.a.PRDPLIST.keywords})),o.a.createElement(S.a,Object.assign({},e.props,{HeaderOption:n})),o.a.createElement("div",{className:"header-v"},c&&o.a.createElement("span",{className:"u-rank"},P.Fn),o.a.createElement("span",null,P.Gr),o.a.createElement("span",{className:"amount"},P.Y),o.a.createElement("span",{className:"amount"},c?P.Bs:P.Pe)),r&&e.renderItem(r,!0,-1),a.length>0&&o.a.createElement(E.a,{dataLength:a.length,hasMore:!s&&i,next:function(){return e.getDetail()}},o.a.createElement("div",{className:"list-view"},a.map(function(t,a){return e.renderItem(t,!1,a)}))),0===a.length&&!r&&!s&&o.a.createElement(p.h,{BG_IMAGE:L.a.no_data_bg_image,CENTER_IMAGE:L.a.BRAND_LOGO_FULL,MESSAGE_1:P.hk}),0===a.length&&s&&Object(u.n)(16,function(t){return e.renderShimmer(t)}))})}}]),t}(m.Component);t.default=f}}]);