(window.webpackJsonp=window.webpackJsonp||[]).push([[42],{1130:function(e,a,t){"use strict";t.r(a);var r=t(948),n=t(18),l=t(33),s=t(135),c=t(134),i=t(136),m=t(4),o=t.n(m),p=t(944),d=t(1593),E=t(1557),u=t(940),_=t(938),N=t(6),h=t(137),g=t(97),v=t(0),y=t(945),b=t.n(y),w=t(951),f=t(1),z=t(14),O=t.n(z),S=t(989),F=t.n(S),T=t(9),D=t(969),A=function(e){function a(e,t){var r;return Object(n.a)(this,a),(r=Object(s.a)(this,Object(c.a)(a).call(this,e,t))).hideFilter=function(){r.setState({showLFitlers:!1})},r.getCategory=function(){Object(h.qb)().then(function(e){r.setState({ISLOAD:!1}),e.response_code===v.bf&&r.setState({categoryList:e.data})})},r.renderShimmer=function(e){return o.a.createElement("div",{key:e,className:"list-item"},o.a.createElement("span",{className:"shimmer"},o.a.createElement(b.a,{height:6,width:"90%"}),o.a.createElement(b.a,{height:4,width:"50%"})),o.a.createElement("span",{className:"amount"},o.a.createElement(b.a,{height:6,width:"30%"})),o.a.createElement("span",{className:"amount"},o.a.createElement(b.a,{height:6,width:"40%"})))},r.renderItem=function(e,a,t){var n=r.state.filterDataBy;return o.a.createElement("div",{key:e.user_id+t,id:e.user_id+t,className:"list-item"+(a?" own-v":"")},o.a.createElement("span",{className:"u-rank"},e.rank_value),o.a.createElement("span",{className:"usernm"},a?o.a.createElement(o.a.Fragment,null,o.a.createElement("div",{className:"usrnm-text"},r.state.OwnUserName.user_name),o.a.createElement("div",{className:"you-text"},"[",g.mt,"]")):e.user_name),o.a.createElement("span",{className:"amount"},o.a.createElement("div",{className:"val val-section"},e.prize_data&&e.prize_data.length>0?o.a.createElement(o.a.Fragment,null,3!=e.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement("span",null,0==e.prize_data[0].prize_type?o.a.createElement("i",{className:"icon-bonus"}):1==e.prize_data[0].prize_type?N.b.getMasterData().currency_code:o.a.createElement("img",{src:_.a.IC_COIN,alt:""})),o.a.createElement(o.a.Fragment,null,N.b.kFormatter(e.prize_data[0].amount))),3==e.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"bottom",overlay:o.a.createElement(E.a,{id:"tooltip"},o.a.createElement("strong",null,e.prize_data[0].name))},o.a.createElement("div",{className:"win"},e.prize_data[0].name)))):o.a.createElement(o.a.Fragment,null,"last_week"==n||"last_month"==n||"yesterday"==n?o.a.createElement("div",{className:"win"},"--"):r.showPrize(e.rank_value)))),o.a.createElement("span",{className:"corrected"},e.correct_answer,"/",e.attempts))},r.showSponser=function(){var e=r.state,a=e.SPONSORDATA,t=e.filterById,n=Object(N.f)(a,function(e){return e.prize_category==t});r.setState({showSponsorData:n})},r.showPrize=function(e){var a=r.state,t=a.showSponsorData,n=a.CFilter,l=parseInt(e),s=t&&t.length>0?t[0]:[],c=!0,i=[];Object(N.c)(s.prize_distribution_detail,function(e,a){var t=parseInt(e.max),r=parseInt(e.min);c&&(t>l&&r<l||t==l||r==l)&&(i.push(e),c=!1)});var m=i&&i.length>0?i[0]:"";return o.a.createElement(o.a.Fragment,null,""==n&&m&&m.amount?o.a.createElement("div",{className:"win"+(2==m.prize_type?" win-pL3":"")},3!=m.prize_type&&o.a.createElement(o.a.Fragment,null,0==m.prize_type?o.a.createElement("span",{className:"bns-span"},o.a.createElement("i",{className:"icon-bonus"})):1==m.prize_type?o.a.createElement("span",{className:"rupee-span"},N.b.getMasterData().currency_code):o.a.createElement("span",{className:"coin-span"},o.a.createElement("img",{src:_.a.IC_COIN,alt:""})),o.a.createElement(o.a.Fragment,null,N.b.kFormatter(m.amount))),3==m.prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"bottom",overlay:o.a.createElement(E.a,{id:"tooltip"},o.a.createElement("strong",null,m.amount))},o.a.createElement("div",{className:"win"},m.amount)))):o.a.createElement("div",{className:"win"},"--"))},r.renderTopUser=function(e){var a=r.state,t=a.filterDataBy,n=a.CFilter,l=e?e.length:0,s=l>0?e[0]:"",c=l>1?e[1]:"",i=l>2?e[2]:"";return o.a.createElement(o.a.Fragment,null,o.a.createElement("div",{className:"rank-section second-rank"+(l>1?"":" disabled")},o.a.createElement("div",{className:"section-data"},o.a.createElement("div",{className:"circle-wrap"},o.a.createElement("span",{className:"rank-pos second"},o.a.createElement("span",{className:"img-section"}),o.a.createElement("span",{className:"pos-text"},"2")),o.a.createElement("div",null,e.rank_value),""==n&&c&&c.prize_data&&c.prize_data.length>0?o.a.createElement("div",{className:"win"+(2==c.prize_type?" win-pL3":"")},3!=c.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,0==c.prize_data[0].prize_type?o.a.createElement("span",{className:"bns-span"},o.a.createElement("i",{className:"icon-bonus"})):1==c.prize_data[0].prize_type?o.a.createElement("span",{className:"rupee-span"},N.b.getMasterData().currency_code):o.a.createElement("span",{className:"coin-span"},o.a.createElement("img",{src:_.a.IC_COIN,alt:""})),o.a.createElement(o.a.Fragment,null,N.b.kFormatter(c.prize_data[0].amount))),3==c.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"bottom",overlay:o.a.createElement(E.a,{id:"tooltip"},o.a.createElement("strong",null,c.prize_data[0].name))},o.a.createElement("div",{className:"win"},c.prize_data[0].name)))):o.a.createElement(o.a.Fragment,null,"last_week"==t||"last_month"==t||"yesterday"==t?o.a.createElement("div",{className:"win"},"--"):o.a.createElement(o.a.Fragment,null,r.showPrize(2))),o.a.createElement("div",{className:"corrected"},c.correct_answer||0,"/",c.attempts||0)),o.a.createElement("div",{className:"winner-name"},c.user_name||"User Name"))),o.a.createElement("div",{className:"rank-section first-rank"+(l>0?"":" disabled")},o.a.createElement("div",{className:"section-data"},o.a.createElement("div",{className:"circle-wrap"},o.a.createElement("span",{className:"rank-pos first"},o.a.createElement("span",{className:"img-section"}),o.a.createElement("span",{className:"pos-text"},"1")),""==n&&s&&s.prize_data&&s.prize_data.length>0?o.a.createElement("div",{className:"win"+(2==s.prize_type?" win-pL3":"")},3!=s.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,0==s.prize_data[0].prize_type?o.a.createElement("span",{className:"bns-span"},o.a.createElement("i",{className:"icon-bonus"})):1==s.prize_data[0].prize_type?o.a.createElement("span",{className:"rupee-span"},N.b.getMasterData().currency_code):o.a.createElement("span",{className:"coin-span"},o.a.createElement("img",{src:_.a.IC_COIN,alt:""})),o.a.createElement(o.a.Fragment,null,N.b.kFormatter(s.prize_data[0].amount))),3==s.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"bottom",overlay:o.a.createElement(E.a,{id:"tooltip"},o.a.createElement("strong",null,s.prize_data[0].name))},o.a.createElement("div",{className:"win"},s.prize_data[0].name)))):o.a.createElement(o.a.Fragment,null,"last_week"==t||"last_month"==t||"yesterday"==t?o.a.createElement("div",{className:"win"},"--"):o.a.createElement(o.a.Fragment,null,r.showPrize(1))),o.a.createElement("div",{className:"corrected"},s.correct_answer||0,"/",s.attempts||0)),o.a.createElement("div",{className:"winner-name"},s.user_name||"User Name"))),o.a.createElement("div",{className:"rank-section third-rank"+(l>2?"":" disabled")},o.a.createElement("div",{className:"section-data"},o.a.createElement("div",{className:"circle-wrap"},o.a.createElement("span",{className:"rank-pos third"},o.a.createElement("span",{className:"img-section"}),o.a.createElement("span",{className:"pos-text"},"3")),""==n&&i&&i.prize_data&&i.prize_data.length>0?o.a.createElement("div",{className:"win"+(2==i.prize_type?" win-pL3":"")},3!=i.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,0==i.prize_data[0].prize_type?o.a.createElement("span",{className:"bns-span"},o.a.createElement("i",{className:"icon-bonus"})):1==i.prize_data[0].prize_type?o.a.createElement("span",{className:"rupee-span"},N.b.getMasterData().currency_code):o.a.createElement("span",{className:"coin-span"},o.a.createElement("img",{src:_.a.IC_COIN,alt:""})),o.a.createElement(o.a.Fragment,null,N.b.kFormatter(i.prize_data[0].amount))),3==i.prize_data[0].prize_type&&o.a.createElement(o.a.Fragment,null,o.a.createElement(d.a,{rootClose:!0,trigger:["click"],placement:"bottom",overlay:o.a.createElement(E.a,{id:"tooltip"},o.a.createElement("strong",null,i.prize_data[0].name))},o.a.createElement("div",{className:"win"},i.prize_data[0].name)))):o.a.createElement(o.a.Fragment,null,"last_week"==t||"last_month"==t||"yesterday"==t?o.a.createElement("div",{className:"win"},"--"):o.a.createElement(o.a.Fragment,null,r.showPrize(3))),o.a.createElement("div",{className:"corrected"},i.correct_answer||0,"/",i.attempts||0)),o.a.createElement("div",{className:"winner-name"},i.user_name||"User Name"))))},r.showSponsor=function(e,a){var t=r.state.filterById==e.prize_category?e:"";return o.a.createElement(o.a.Fragment,null,""!=t&&t.sponsor_name&&o.a.createElement("div",{className:"sponsored-section"},o.a.createElement("span",{className:"sponsored-text"},g.Wp),o.a.createElement("img",{src:N.b.getOpenPredFPPURL(e.sponsor_logo),alt:""})))},r.filterLeaderboard=function(e){r.setState({showLFitlers:!1,CFilter:e,PLIST:[],PNO:1,PSIZE:20,OWNDATA:""},function(){r.getLeaderboardData()})},r.handleTimeFilter=function(e,a){r.setState({filterDataBy:e,filterById:a,PLIST:[],PNO:1,PSIZE:20,OWNDATA:""},function(){r.getLeaderboardData()})},r.state={PLIST:[],OWNDATA:"",TOPTHREE:[],SPONSORDATA:[],PNO:1,PSIZE:20,categoryList:[],HMORE:!1,ISLOAD:!1,refreshList:!0,showLFitlers:!1,filterDataBy:"today",CFilter:"",filterById:"1",OwnUserName:O.a.get("profile"),showSponsorData:"",STARTDATE:"",ENDDATE:"",leadStatus:"",filerByTime:[{value:"today",label:g.Oq,prize_cat_id:"1"},{value:"this_week",label:g.Lq,prize_cat_id:"2"},{value:"this_month",label:g.Jq,prize_cat_id:"3"}],filerByPreTime:[{value:"yesterday",label:g.lt,prize_cat_id:"1"},{value:"last_week",label:g.Lh,prize_cat_id:"2"},{value:"last_month",label:g.Jh,prize_cat_id:"3"}]},r}return Object(i.a)(a,e),Object(l.a)(a,[{key:"UNSAFE_componentWillMount",value:function(){f.a.setPickedGameType(T.r.OpenPredLead);var e=window.location.href;e.includes("#open-predictor-leaderboard")||(e+="#open-predictor-leaderboard"),window.history.replaceState("","",e)}},{key:"componentDidMount",value:function(){this.getCategory(),this.getLeaderboardData()}},{key:"UNSAFE_componentWillReceiveProps",value:function(e){this.state.showLFitlers!=e.showLobbyFitlers&&this.setState({showLFitlers:e.showLobbyFitlers})}},{key:"getLeaderboardData",value:function(){var e=this,a=this.state,t=a.PNO,n=a.PSIZE,l=a.PLIST,s=a.CFilter,c=a.OWNDATA,i=a.filterDataBy,m=a.TOPTHREE,o=a.SPONSORDATA,p={category_id:s.category_id,page_no:t,page_size:n,filter:i};this.setState({ISLOAD:!0}),Object(h.rb)(p).then(function(a){if(e.setState({ISLOAD:!1}),a.response_code===v.bf){var s=a.data.own||"",i=a.data.other_list||[],p=a.data.top_three||[],d=a.data.sponsors||[],E=a.data.start_date||"",u=a.data.end_date||"",_=a.data.status;e.setState({PLIST:[].concat(Object(r.a)(l),Object(r.a)(i)),OWNDATA:1===t?s:c,TOPTHREE:1===t?p:m,SPONSORDATA:1===t?d:o,HMORE:i.length>=n-(s||c?1:0),PNO:t+1,STARTDATE:E,ENDDATE:u,leadStatus:_},function(){e.showSponser()})}})}},{key:"getMoreLData",value:function(){var e=this,a=this.state,t=a.PNO,n=a.PSIZE,l=a.PLIST,s=a.CFilter,c=a.OWNDATA,i=a.filterDataBy,m=a.TOPTHREE,o=a.SPONSORDATA,p={category_id:s.category_id,page_no:t,page_size:n,filter:i};this.setState({ISLOAD:!0}),Object(h.rb)(p).then(function(a){if(e.setState({ISLOAD:!1}),a.response_code===v.bf){var s=a.data.own||"",i=a.data.other_list||[],p=a.data.top_three||[],d=a.data.sponsors||[];e.setState({PLIST:[].concat(Object(r.a)(l),Object(r.a)(i)),OWNDATA:1===t?s:c,TOPTHREE:1===t?p:m,SPONSORDATA:1===t?d:o,HMORE:i.length>=n-(s||c?1:0),PNO:t+1},function(){e.showSponser()})}})}},{key:"render",value:function(){var e=this,a=this.state,t=a.categoryList,r=a.PLIST,n=a.OWNDATA,l=a.ISLOAD,s=a.HMORE,c=a.refreshList,i=a.CFilter,m=a.showLFitlers,d=a.filerByTime,E=a.filterDataBy,h=a.TOPTHREE,v=a.SPONSORDATA,y=a.filerByPreTime,b=a.filterById,f=a.STARTDATE,z=a.ENDDATE,O=a.leadStatus,S={showLFitler:m};return o.a.createElement(p.a.Consumer,null,function(a){return o.a.createElement("div",{className:"prediction-wrap-v prediction-part-v open-predict-leaderboard is-leaderboard fpp-leaderboard"},o.a.createElement(D.default,Object.assign({},e.props,{FitlerOptions:S,hideFilter:e.hideFilter,filerObj:t,filterLeaderboard:e.filterLeaderboard,filterDataBy:i})),o.a.createElement("div",{className:"fixed-ch-view"},o.a.createElement("div",{className:"filter-time-section"},o.a.createElement("ul",{className:"filter-time-wrap"},Object(N.c)(d,function(a,t){return o.a.createElement("li",{href:!0,className:"filter-time-btn"+(a.value==E?" active":"")+(2==a.prize_cat_id&&2==b&&f?" with-date":""),onClick:function(){return e.handleTimeFilter(a.value,a.prize_cat_id)}},a.label,2==a.prize_cat_id&&2==b&&f&&o.a.createElement("span",null,o.a.createElement(F.a,{date:f,format:"D MMM "}),"-",o.a.createElement(F.a,{date:z,format:" D MMM "})))}))),o.a.createElement("div",{className:"previous-data"},Object(N.c)(y,function(a,t){return o.a.createElement(o.a.Fragment,null,b===a.prize_cat_id&&o.a.createElement("a",{href:!0,className:"previous-time-btn"+("last_week"===E||"last_month"===E||"yesterday"===E?" active":""),onClick:function(){return e.handleTimeFilter(a.value,a.prize_cat_id)}},o.a.createElement("i",{className:"icon-arrow-up"}),o.a.createElement("i",{className:"icon-arrow-up"}),a.label))}),0==O&&o.a.createElement("div",{className:"leader-status"},o.a.createElement("span",null),g.Yh),3==O&&o.a.createElement("div",{className:"leader-status comp"},g.kc))),o.a.createElement("div",{className:"table-view"},o.a.createElement("div",{className:"top-three-users"},h&&h.length>0&&e.renderTopUser(h),o.a.createElement("div",{className:"white-section"})),h&&h.length>0&&v&&v.length>0&&v.map(function(a,t){return e.showSponsor(a)}),(r&&r.length>0||n&&n.length>0||h&&h.length>0)&&o.a.createElement("div",{className:"header-v"},o.a.createElement("span",{className:"u-rank"},g.Fn),o.a.createElement("span",{className:"usernm"},g.Gr),o.a.createElement("span",{className:"amount"},g.Um),o.a.createElement("span",{className:"corrected text-capitalize ellipsis-text"},g.Ic)),c&&n&&e.renderItem(n,!0,-1),r.length>0&&o.a.createElement(w.a,{dataLength:r.length,hasMore:!l&&s,next:function(){return e.getMoreLData()}},o.a.createElement("div",{className:"list-view"},r.map(function(a,t){return e.renderItem(a,!1,t)}))),0===r.length&&0===n.length&&0===h.length&&!l&&o.a.createElement("div",{className:"no-data-leaderboard"},o.a.createElement(u.h,{BG_IMAGE:_.a.no_data_bg_image,CENTER_IMAGE:_.a.BRAND_LOGO_FULL,MESSAGE_1:g.Wj})),0===r.length&&!n&&!l&&0!=h.length&&o.a.createElement(u.h,{BG_IMAGE:_.a.no_data_bg_image,CENTER_IMAGE:_.a.BRAND_LOGO_FULL,MESSAGE_1:g.hk,MESSAGE_2:g.gk}),0===r.length&&l&&Object(N.n)(16,function(a){return e.renderShimmer(a)})))})}}]),a}(o.a.Component);a.default=A}}]);