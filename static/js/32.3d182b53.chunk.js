(window.webpackJsonp=window.webpackJsonp||[]).push([[32],{1123:function(e,t,a){"use strict";a.r(t);var n=a(18),i=a(33),s=a(135),c=a(134),l=a(136),r=a(4),m=a.n(r),o=a(944),E=a(942),u=a(6),d=a(137),h=a(940),p=a(945),b=a.n(p),f=a(941),g=a(943),S=a(938),I=a(0),N=a(97),w=a(9),A=function(e){function t(e){var a;return Object(n.a)(this,t),(a=Object(s.a)(this,Object(c.a)(t).call(this,e))).callApiFBQAList=function(){a.setState({ISLOAD:!0}),Object(d.wb)({}).then(function(e){if(a.setState({ISLOAD:!1}),e.response_code===I.bf){var t=e.data.questions||[];a.setState({FDBLIST:t})}})},a.btnAction=function(e,t){if(e.answer&&e.answer.length>1&&!a.state.isApiCalling){var n={feedback_question_id:e.feedback_question_id.$oid,answer:e.answer};a.setState({isApiCalling:!0,sentIndex:t}),Object(d.Td)(n).then(function(e){if(e.response_code===I.bf){var n=a.state.FDBLIST;n[t].submitted=!0,a.setState({FDBLIST:n})}a.setState({isApiCalling:!1})})}},a.onChangeText=function(e){var t=e.target.value,n=e.target.id,i=a.state.FDBLIST;i[n].answer=t,a.setState({FDBLIST:i})},a.renderListItem=function(e,t){var n=e.submitted;return m.a.createElement("li",{key:t},!n&&m.a.createElement(m.a.Fragment,null,e.coins>0&&m.a.createElement("div",{className:"top-price"},m.a.createElement("span",null,N.Qf,m.a.createElement("img",{src:S.a.IC_COIN,alt:""}),e.coins),m.a.createElement("img",{className:"img-shape",src:S.a.COINS_BACK_STRIPE,alt:""})),m.a.createElement("p",{className:"feedback-text mb30"},N.if),m.a.createElement("div",{className:"q-view"},m.a.createElement("p",{className:"question"},e.question),m.a.createElement("textarea",{onChange:a.onChangeText,placeholder:"Enter your suggestion",rows:"4",name:"answer",id:t,className:"ans-input"}),m.a.createElement("a",{href:!0,className:"send-btn",id:"send-btn"+t,onClick:function(){return a.btnAction(e,t)}},m.a.createElement("i",{className:"icon-send"+(a.state.sentIndex===t?" animate":"")})))),n&&m.a.createElement("div",{className:"submited-v"},m.a.createElement("p",{className:"feedback-text m-0 text-left"},N.if),m.a.createElement("img",{src:S.a.FB_THUMB,alt:"",className:"thumb-img"}),m.a.createElement("p",{className:"coin-text"},e.coins>0&&m.a.createElement(m.a.Fragment,null,m.a.createElement("img",{src:S.a.IC_COIN,alt:""})," +",e.coins," ",N.Xb))),m.a.createElement("p",{className:"hint-text"+(n?" m-0":"")},e.coins>0?N.hf:""))},a.Shimmer=function(e){return m.a.createElement(p.SkeletonTheme,{color:w.n?"#161920":null,highlightColor:w.n?"#0E2739":null},m.a.createElement("div",{key:e,className:"contest-list border"},m.a.createElement("div",{className:"shimmer-container"},m.a.createElement(b.a,{height:9,width:"30%"}),m.a.createElement("div",{className:"shimmer-line m-t-20 m-b-sm"},m.a.createElement(b.a,{height:6,width:"95%"}),m.a.createElement(b.a,{height:6,width:"70%"})),m.a.createElement("div",{className:"shimmer-image m-b"},m.a.createElement(b.a,{width:"100%",height:80})),m.a.createElement(b.a,{height:6,width:"80%"}))))},a.state={FDBLIST:[],ISLOAD:!1,isApiCalling:!1,sentIndex:-1},a}return Object(l.a)(t,e),Object(i.a)(t,[{key:"componentDidMount",value:function(){this.callApiFBQAList()}},{key:"render",value:function(){var e=this,t=this.state,a=t.FDBLIST,n=t.ISLOAD,i={back:!0,title:N.if,isPrimary:!w.n};return m.a.createElement(o.a.Consumer,null,function(t){return m.a.createElement("div",{className:"web-container feedback-c"},m.a.createElement(E.Helmet,{titleTemplate:"".concat(g.a.template," | %s")},m.a.createElement("title",null,g.a.ECFEEDBAK.title),m.a.createElement("meta",{name:"description",content:g.a.ECFEEDBAK.description}),m.a.createElement("meta",{name:"keywords",content:g.a.ECFEEDBAK.keywords})),m.a.createElement(f.a,Object.assign({},e.props,{HeaderOption:i})),m.a.createElement("ul",{className:"list-type"},Object(u.c)(a,function(t,a){return e.renderListItem(t,a)}),0===a.length&&!n&&m.a.createElement(h.h,{BG_IMAGE:S.a.no_data_bg_image,CENTER_IMAGE:S.a.BRAND_LOGO_FULL,MESSAGE_1:N.fk,CLASS:"pt40-per"}),0===a.length&&n&&[1,1,1,1,1,1].map(function(t,a){return e.Shimmer(a)})))})}}]),t}(r.Component);t.default=A}}]);