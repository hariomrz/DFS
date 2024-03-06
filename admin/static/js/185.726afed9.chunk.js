(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[185],{631:function(e,t,a){"use strict";var i=a(59),n=a(11),r=a(12),s=a(15),o=a(14),l=a(0),c=a.n(l),d=a(155),m=a(156),u=a(46),p=a(536),h=a.n(p),b=(a(521),a(17)),D=a(562),f=a.n(D),g=a(563),E=a.n(g),v=a(9),x=a(10),N=a(8),C=a(18),y=a(5),_=a.n(y),F=a(23),k=a.n(F),Y=function(e){Object(s.a)(a,e);var t=Object(o.a)(a);function a(e){var r;return Object(n.a)(this,a),(r=t.call(this,e)).coinDistributedGraph=function(){var e=r.state,t=e.FromDate,a=e.ToDate,n={from_date:t?k()(t).format("YYYY-MM-DD"):"",to_date:a?k()(a).format("YYYY-MM-DD"):""};_.a.isUndefined(r.props.user_unique_id)||(n.user_unique_id=r.props.user_unique_id),v.a.Rest(N.nk+N.P,n).then((function(e){e.response_code==N.qk&&r.setState({xAxisSeries:e.data.series,xAxisCategories:e.data.categories,totalCoinsDistributed:e.data.total_coins_distributed,closingBalance:e.data.closing_balance},(function(){r.setState({CoinDistributedGraph:{title:{text:""},plotOptions:{series:{marker:{symbol:"circle"}}},xAxis:Object(i.a)({categories:r.state.xAxisCategories,min:1,tickWidth:0,crosshair:!1,lineWidth:2,gridLineWidth:0,title:"",lineColor:"#D8D8D8"},"title",{text:""}),yAxis:[{title:{text:"Distribution"},min:1,tickWidth:0,crosshair:!1,lineWidth:1,gridLineWidth:1,lineColor:"#D8D8D8"},{title:{text:"Coins"},labels:{format:"50"},opposite:!0,min:1,tickWidth:0,crosshair:!1,lineWidth:1,gridLineWidth:1,lineColor:"#D8D8D8"}],allowPointSelect:!0,series:r.state.xAxisSeries,credits:{enabled:!1},legend:{enabled:!0,layout:"horizontal",align:"right",verticalAlign:"top",x:0,y:0,useHTML:!0,symbolPadding:10,symbolWidth:0,symbolHeight:0,symbolRadius:0,labelFormatter:function(){return'<span style="background-color:'+this.color+'" class="dis-indicator"></span>'+this.name}}}})}))})).catch((function(e){C.notify.show(N.Ri,"error",5e3)}))},r.coinRedeemedGraph=function(){var e=r.state,t=e.FromDate,a=e.ToDate,n={from_date:t?k()(t).format("YYYY-MM-DD"):"",to_date:a?k()(a).format("YYYY-MM-DD"):""},s=N.Q;_.a.isUndefined(r.props.user_unique_id)||(n.user_unique_id=r.props.user_unique_id,s=N.Qj),v.a.Rest(N.nk+s,n).then((function(e){e.response_code==N.qk&&r.setState({redeemedSeries:e.data.series_data,totalCoinRedeem:e.data.total_coin_redeem},(function(){var e;r.setState({CoinRedeemedGraph:{title:{text:""},chart:{type:"pie"},plotOptions:{pie:(e={borderWidth:7,dataLabels:!1,innerSize:"74%",allowPointSelect:!0,cursor:"pointer"},Object(i.a)(e,"dataLabels",{enabled:!0,color:"#9398A0",useHTML:!0,style:{fontSize:"14px",fontFamily:"MuliBold",textAlign:"right",lineHeight:"18px"},format:'<div><div class="clearfix slice-color"><span style="background-color: {point.color}" class="indicator"></span><span>{point.name}</span></div><div class="total-coins">{point.total_coins} Coins</div><div>{point.coins_user} Users</div><div class="graph-percent">{point.percentage:.1f} %</div></div>',connectorColor:"transparent",connectorPadding:10,y:-20,x:0}),Object(i.a)(e,"stacking","normal"),e)},series:[{data:r.state.redeemedSeries}],LineData:[],GraphHeaderTitle:[],credits:{enabled:!1},legend:{enabled:!1}}})}))})).catch((function(e){C.notify.show(N.Ri,"error",5e3)}))},r.handleDateFilter=function(e,t){r.setState(Object(i.a)({},t,e),(function(){(r.state.FromDate||r.state.ToDate)&&(r.coinDistributedGraph(),r.coinRedeemedGraph())}))},r.state={FromDate:x.i.getFirstDateOfMonth(),ToDate:new Date,redeemedSeries:[],xAxisCategories:[],xAxisSeries:[],totalCoinsDistributed:0,totalCoinRedeem:0,closingBalance:0},r}return Object(r.a)(a,[{key:"componentDidMount",value:function(){this.coinDistributedGraph(),this.coinRedeemedGraph()}},{key:"render",value:function(){var e=this,t=this.state,a=t.totalCoinRedeem,i=t.FromDate,n=t.ToDate,r=t.CoinDistributedGraph,s=t.CoinRedeemedGraph,o=t.totalCoinsDistributed,l=t.closingBalance;return c.a.createElement(c.a.Fragment,null,c.a.createElement("div",{className:"coins-dashboard mb-30"},c.a.createElement(d.a,null,c.a.createElement(m.a,{md:6},c.a.createElement("div",{className:"float-left"},c.a.createElement("label",{className:"closing-balance"},"Closing Balance"),c.a.createElement("div",{className:"balance-count"},c.a.createElement("div",{className:"img-wrap"},c.a.createElement("img",{className:"coin-img",src:u.a.REWARD_ICON,alt:""})),c.a.createElement("span",null,x.i.getNumberWithCommas(l))))),c.a.createElement(m.a,{md:6},!this.props.FromDashboard&&c.a.createElement("div",{className:"float-right"},c.a.createElement("div",{className:"member-box float-left"},c.a.createElement("label",{className:"filter-label"},"Date"),c.a.createElement(d.a,null,c.a.createElement(m.a,{md:6,className:"pr-0"},c.a.createElement(h.a,{maxDate:n,className:"filter-date",showYearDropdown:"true",selected:i,onChange:function(t){return e.handleDateFilter(t,"FromDate")},placeholderText:"From",dateFormat:"dd/MM/yyyy"})),c.a.createElement(m.a,{md:6,className:"pl-2"},c.a.createElement(h.a,{popperPlacement:"top-end",minDate:i,maxDate:new Date,className:"filter-date",showYearDropdown:"true",selected:n,onChange:function(t){return e.handleDateFilter(t,"ToDate")},placeholderText:"To",dateFormat:"dd/MM/yyyy"}))))))),c.a.createElement(d.a,null,c.a.createElement(m.a,{md:6},c.a.createElement("div",{className:"graph-box"},c.a.createElement("div",{className:"distributed-box"},c.a.createElement("div",{className:"title-box"},c.a.createElement(d.a,{className:"total-info-box"},c.a.createElement(m.a,{md:4},c.a.createElement("span",{onClick:function(){return e.props.history.push("/coins/coins-distributed")},className:"distributed-count"},x.i.getNumberWithCommas(o)),c.a.createElement("div",{className:"coins-distributed"},"Total Coins Distributed")),c.a.createElement(m.a,{md:8,className:"align-right"}))),c.a.createElement("div",{className:"graph-p-box"},c.a.createElement(E.a,{highcharts:f.a,options:r}))))),c.a.createElement(m.a,{md:6},c.a.createElement("div",{className:"graph-box"},c.a.createElement("div",{className:"distributed-box"},c.a.createElement("div",{className:"title-box"},c.a.createElement(d.a,{className:"total-info-box"},c.a.createElement(m.a,{md:4},c.a.createElement("span",{onClick:function(){return e.props.history.push("/coins/coin-redeem")},className:"distributed-count"},x.i.getNumberWithCommas(a)),c.a.createElement("div",{className:"coins-distributed"},"Total Coins Redeemed")),c.a.createElement(m.a,{md:8,className:"align-right"}))),c.a.createElement("div",{className:"graph-p-box pie-chart"},c.a.createElement(E.a,{highcharts:f.a,options:s}))))))))}}]),a}(l.Component);t.a=Object(b.g)(Y)},881:function(e,t,a){"use strict";a.r(t);var i=a(11),n=a(12),r=a(15),s=a(14),o=a(0),l=a.n(o),c=a(155),d=a(156),m=a(631),u=a(824),p=function(e){Object(r.a)(a,e);var t=Object(s.a)(a);function a(e){return Object(i.a)(this,a),t.call(this,e)}return Object(n.a)(a,[{key:"render",value:function(){var e=this,t={FromDashboard:!1,viewType:"topearner"},a={FromDashboard:!1,viewType:"topredeemer"};return l.a.createElement(l.a.Fragment,null,!this.props.FromDashboard&&l.a.createElement(c.a,null,l.a.createElement(d.a,{md:12,className:"mt-4"},l.a.createElement("h2",{className:"h2-cls float-left"},"Coins Dashboard"),l.a.createElement("div",{className:"coins-setting-box float-right"},l.a.createElement("i",{onClick:function(){return e.props.history.push("/coins/setting")},className:"icon-setting pointer"})))),l.a.createElement(m.a,{FromDashboard:this.props.FromDashboard}),!this.props.FromDashboard&&l.a.createElement(c.a,null,l.a.createElement(d.a,{md:6},l.a.createElement(u.default,t)),l.a.createElement(d.a,{md:6},l.a.createElement(u.default,a))))}}]),a}(o.Component);t.default=p}}]);
//# sourceMappingURL=185.726afed9.chunk.js.map