(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[15],{507:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==c(e)&&"function"!==typeof e)return{default:e};var t=u();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var i=n?Object.getOwnPropertyDescriptor(e,r):null;i&&(i.get||i.set)?Object.defineProperty(a,r,i):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=o(a(1)),i=o(a(509)),s=o(a(510)),l=o(a(3));function o(e){return e&&e.__esModule?e:{default:e}}function u(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return u=function(){return e},e}function c(e){return(c="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function p(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function f(e,t){return!t||"object"!==c(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function m(e){return(m=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function g(e,t){return(g=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function h(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var b=function(e){function t(){return d(this,t),f(this,m(t).apply(this,arguments))}var a,r,o;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&g(e,t)}(t,e),a=t,(r=[{key:"isFirstPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"isPrevPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isNextPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isLastPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"buildPages",value:function(){for(var e=[],t=this.props,a=t.itemsCountPerPage,r=t.pageRangeDisplayed,o=t.activePage,u=t.prevPageText,c=t.nextPageText,d=t.firstPageText,p=t.lastPageText,f=t.totalItemsCount,m=t.onChange,g=t.activeClass,h=t.itemClass,b=t.itemClassFirst,v=t.itemClassPrev,y=t.itemClassNext,C=t.itemClassLast,E=t.activeLinkClass,P=t.disabledClass,_=(t.hideDisabled,t.hideNavigation,t.linkClass),k=t.linkClassFirst,D=t.linkClassPrev,N=t.linkClassNext,T=t.linkClassLast,w=(t.hideFirstLastPages,t.getPageUrl),x=new i.default(a,r).build(f,o),O=x.first_page;O<=x.last_page;O++)e.push(n.default.createElement(s.default,{isActive:O===o,key:O,href:w(O),pageNumber:O,pageText:O+"",onClick:m,itemClass:h,linkClass:_,activeClass:g,activeLinkClass:E,ariaLabel:"Go to page number ".concat(O)}));return this.isPrevPageVisible(x.has_previous_page)&&e.unshift(n.default.createElement(s.default,{key:"prev"+x.previous_page,href:w(x.previous_page),pageNumber:x.previous_page,onClick:m,pageText:u,isDisabled:!x.has_previous_page,itemClass:(0,l.default)(h,v),linkClass:(0,l.default)(_,D),disabledClass:P,ariaLabel:"Go to previous page"})),this.isFirstPageVisible(x.has_previous_page)&&e.unshift(n.default.createElement(s.default,{key:"first",href:w(1),pageNumber:1,onClick:m,pageText:d,isDisabled:!x.has_previous_page,itemClass:(0,l.default)(h,b),linkClass:(0,l.default)(_,k),disabledClass:P,ariaLabel:"Go to first page"})),this.isNextPageVisible(x.has_next_page)&&e.push(n.default.createElement(s.default,{key:"next"+x.next_page,href:w(x.next_page),pageNumber:x.next_page,onClick:m,pageText:c,isDisabled:!x.has_next_page,itemClass:(0,l.default)(h,y),linkClass:(0,l.default)(_,N),disabledClass:P,ariaLabel:"Go to next page"})),this.isLastPageVisible(x.has_next_page)&&e.push(n.default.createElement(s.default,{key:"last",href:w(x.total_pages),pageNumber:x.total_pages,onClick:m,pageText:p,isDisabled:x.current_page===x.total_pages,itemClass:(0,l.default)(h,C),linkClass:(0,l.default)(_,T),disabledClass:P,ariaLabel:"Go to last page"})),e}},{key:"render",value:function(){var e=this.buildPages();return n.default.createElement("ul",{className:this.props.innerClass},e)}}])&&p(a.prototype,r),o&&p(a,o),t}(n.default.Component);t.default=b,h(b,"propTypes",{totalItemsCount:r.default.number.isRequired,onChange:r.default.func.isRequired,activePage:r.default.number,itemsCountPerPage:r.default.number,pageRangeDisplayed:r.default.number,prevPageText:r.default.oneOfType([r.default.string,r.default.element]),nextPageText:r.default.oneOfType([r.default.string,r.default.element]),lastPageText:r.default.oneOfType([r.default.string,r.default.element]),firstPageText:r.default.oneOfType([r.default.string,r.default.element]),disabledClass:r.default.string,hideDisabled:r.default.bool,hideNavigation:r.default.bool,innerClass:r.default.string,itemClass:r.default.string,itemClassFirst:r.default.string,itemClassPrev:r.default.string,itemClassNext:r.default.string,itemClassLast:r.default.string,linkClass:r.default.string,activeClass:r.default.string,activeLinkClass:r.default.string,linkClassFirst:r.default.string,linkClassPrev:r.default.string,linkClassNext:r.default.string,linkClassLast:r.default.string,hideFirstLastPages:r.default.bool,getPageUrl:r.default.func}),h(b,"defaultProps",{itemsCountPerPage:10,pageRangeDisplayed:5,activePage:1,prevPageText:"\u27e8",firstPageText:"\xab",nextPageText:"\u27e9",lastPageText:"\xbb",innerClass:"pagination",itemClass:void 0,linkClass:void 0,activeLinkClass:void 0,hideFirstLastPages:!1,getPageUrl:function(e){return"#"}})},509:function(e,t){function a(e,t){if(!(this instanceof a))return new a(e,t);this.per_page=e||25,this.length=t||10}e.exports=a,a.prototype.build=function(e,t){var a=Math.ceil(e/this.per_page);e=parseInt(e,10),(t=parseInt(t,10)||1)<1&&(t=1),t>a&&(t=a);var n=Math.max(1,t-Math.floor(this.length/2)),r=Math.min(a,t+Math.floor(this.length/2));r-n+1<this.length&&(t<a/2?r=Math.min(a,r+(this.length-(r-n))):n=Math.max(1,n-(this.length-(r-n)))),r-n+1>this.length&&(t>a/2?n++:r--);var i=this.per_page*(t-1);i<0&&(i=0);var s=this.per_page*t-1;return s<0&&(s=0),s>Math.max(e-1,0)&&(s=Math.max(e-1,0)),{total_pages:a,pages:Math.min(r-n+1,a),current_page:t,first_page:n,last_page:r,previous_page:t-1,next_page:t+1,has_previous_page:t>1,has_next_page:t<a,total_results:e,results:Math.min(s-i+1,e),first_result:i,last_result:s}}},510:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!==typeof e)return{default:e};var t=l();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var i=n?Object.getOwnPropertyDescriptor(e,r):null;i&&(i.get||i.set)?Object.defineProperty(a,r,i):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=s(a(1)),i=s(a(3));function s(e){return e&&e.__esModule?e:{default:e}}function l(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return l=function(){return e},e}function o(e){return(o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function c(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function d(e,t){return!t||"object"!==o(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function p(e){return(p=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function f(e,t){return(f=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function m(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var g=function(e){function t(){return u(this,t),d(this,p(t).apply(this,arguments))}var a,r,s;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&f(e,t)}(t,e),a=t,(r=[{key:"handleClick",value:function(e){var t=this.props,a=t.isDisabled,n=t.pageNumber;e.preventDefault(),a||this.props.onClick(n)}},{key:"render",value:function(){var e,t=this.props,a=t.pageText,r=(t.pageNumber,t.activeClass),s=t.itemClass,l=t.linkClass,o=t.activeLinkClass,u=t.disabledClass,c=t.isActive,d=t.isDisabled,p=t.href,f=t.ariaLabel,g=(0,i.default)(s,(m(e={},r,c),m(e,u,d),e)),h=(0,i.default)(l,m({},o,c));return n.default.createElement("li",{className:g,onClick:this.handleClick.bind(this)},n.default.createElement("a",{className:h,href:p,"aria-label":f},a))}}])&&c(a.prototype,r),s&&c(a,s),t}(n.Component);t.default=g,m(g,"propTypes",{pageText:r.default.oneOfType([r.default.string,r.default.element]),pageNumber:r.default.number.isRequired,onClick:r.default.func.isRequired,isActive:r.default.bool.isRequired,isDisabled:r.default.bool,activeClass:r.default.string,activeLinkClass:r.default.string,itemClass:r.default.string,linkClass:r.default.string,disabledClass:r.default.string,href:r.default.string}),m(g,"defaultProps",{activeClass:"active",disabledClass:"disabled",itemClass:void 0,linkClass:void 0,activeLinkCLass:void 0,isActive:!1,isDisabled:!1,href:"#"})},513:function(e,t,a){"use strict";a.d(t,"a",(function(){return o}));var n=a(0),r=a.n(n),i=a(523),s=a.n(i),l=a(9);function o(e){var t=e.data,a=t.date,n=t.format;return a?r.a.createElement(s.a,{date:l.a.getUtcToLocal(a),format:n}):""}},824:function(e,t,a){"use strict";a.r(t);var n=a(59),r=a(11),i=a(12),s=a(15),l=a(14),o=a(0),u=a.n(o),c=a(155),d=a(156),p=a(516),f=a(46),m=a(536),g=a.n(m),h=(a(521),a(5)),b=a.n(h),v=a(9),y=a(10),C=a(8),E=a(507),P=a.n(E),_=a(18),k=a(23),D=a.n(k),N=a(17),T=a(157),w=a(513),x=function(e){Object(s.a)(a,e);var t=Object(l.a)(a);function a(e){var i;return Object(r.a)(this,a),(i=t.call(this,e)).handleDateFilter=function(e,t){i.setState(Object(n.a)({},t,e),(function(){i.state.FromDate&&i.state.ToDate&&i.setState({CURRENT_PAGE:1},(function(){i.getCoinDistributedHistory()}))}))},i.exportRecords=function(){var e=i.state,t=e.Pathname,a="?from_date="+e.FromDate+"to_date="+e.ToDate;a+="&Sessionkey="+v.a.getToken(),"top-redeemer"==t||"topredeemer"==i.props.viewType?window.open(C.nk+C.rc+a,"_blank"):"top-earner"==t||"topearner"==i.props.viewType?window.open(C.nk+C.qc+a,"_blank"):window.open(C.nk+C.lc+a,"_blank")},i.state={CURRENT_PAGE:1,PERPAGE:b.a.isUndefined(i.props.FromDashboard)?C.Vf:10,FromDate:"",ToDate:"",totalCoinsDistributed:0,DistributedCoins:[],Pathname:"",DistributedPosting:!1},i}return Object(i.a)(a,[{key:"componentDidMount",value:function(){var e=this,t=this.props.history.location.pathname.split(/[/ ]+/).pop();this.setState({Pathname:t},(function(){e.getCoinDistributedHistory()}))}},{key:"getCoinDistributedHistory",value:function(){var e=this;this.setState({DistributedPosting:!0});var t=this.state,a=t.CURRENT_PAGE,n=t.PERPAGE,r=t.FromDate,i=t.ToDate,s=t.Pathname,l={from_date:r?D()(r).format("YYYY-MM-DD"):"",to_date:i?D()(i).format("YYYY-MM-DD"):"",items_perpage:n,current_page:a},o="";o="top-redeemer"==s||"topredeemer"==this.props.viewType?C.Bf:"top-earner"==s||"topearner"==this.props.viewType?C.Af:C.Dd,v.a.Rest(C.nk+o,l).then((function(t){t.response_code==C.qk&&(1==a&&e.setState({Total:t.data.total}),e.setState({DistributedCoins:t.data.list,totalCoinsDistributed:t.data.total_coins_distributed,DistributedPosting:!1}))})).catch((function(e){_.notify.show(C.Ri,"error",5e3)}))}},{key:"handlePageChange",value:function(e){var t=this;this.setState({CURRENT_PAGE:e},(function(){t.getCoinDistributedHistory()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.DistributedPosting,n=t.CURRENT_PAGE,r=t.PERPAGE,i=t.Total,s=t.FromDate,l=t.ToDate,o=t.Pathname,m=t.DistributedCoins,h=t.totalCoinsDistributed;return u.a.createElement(u.a.Fragment,null,u.a.createElement("div",{className:"top-earner-sc ".concat(b.a.isUndefined(this.props.FromDashboard)?"":"bg-white")},b.a.isUndefined(this.props.FromDashboard)&&u.a.createElement(u.a.Fragment,null,u.a.createElement(c.a,null,u.a.createElement(d.a,{md:6},u.a.createElement("div",{className:"float-left"},u.a.createElement("div",{className:"top-earner"},"top-earner"==o&&"Top Earner","top-redeemer"==o&&"Top Redeemer","coins-distributed"==o&&"Coin Distributed"),u.a.createElement("div",{className:"leader-board"},"Leaderboard"))),u.a.createElement(d.a,{md:6},u.a.createElement("div",{onClick:function(){return e.props.history.push("/coins/dashboard")},className:"go-back"},"<"," Back"))),"coins-distributed"==o&&u.a.createElement(c.a,{className:"mt-3"},u.a.createElement(d.a,{md:6},u.a.createElement("div",{className:"float-left"},u.a.createElement("div",{className:"total-title"},"Total Coin Distributed"),u.a.createElement("div",{className:"total-num"},u.a.createElement("img",{className:"coin-img",src:f.a.REWARD_ICON,alt:""}),u.a.createElement("span",{className:"num"},y.i.getNumberWithCommas(h))))),u.a.createElement(d.a,{md:6},u.a.createElement("div",{className:"float-right"},u.a.createElement("div",{className:"member-box float-left"},u.a.createElement("label",{className:"filter-label"},"Date"),u.a.createElement(g.a,{maxDate:new Date,className:"filter-date",showYearDropdown:"true",selected:s,onChange:function(t){return e.handleDateFilter(t,"FromDate")},placeholderText:"From"}),u.a.createElement(g.a,{maxDate:new Date,className:"filter-date",showYearDropdown:"true",selected:l,onChange:function(t){return e.handleDateFilter(t,"ToDate")},placeholderText:"To"}),u.a.createElement("div",{className:"export-topearner"},u.a.createElement("i",{className:"export-list icon-export",onClick:function(t){return e.exportRecords()}}))))))),u.a.createElement(c.a,null,u.a.createElement(d.a,{md:12,className:"table-responsive common-table"},u.a.createElement(p.a,{className:"mb-0"},u.a.createElement("thead",null,"topearner"==this.props.viewType?u.a.createElement("tr",{className:"dashboard-view"},u.a.createElement("th",{colSpan:"3"},"Top Earner")):"topredeemer"==this.props.viewType?u.a.createElement("tr",{className:"dashboard-view"},u.a.createElement("th",{colSpan:"3"},"Top Redeemer")):u.a.createElement("tr",null,"coins-distributed"==o?u.a.createElement("th",{className:"left-th pl-3"},"Date"):u.a.createElement("th",{className:"left-th pl-3"},"Rank"),u.a.createElement("th",null,"Username"),"coins-distributed"==o&&u.a.createElement("th",null,"Event"),u.a.createElement("th",{className:"right-th"},"Coin Earned"))),i>0?b.a.map(m,(function(e,t){return u.a.createElement("tbody",{key:t},u.a.createElement("tr",null,"coins-distributed"==o?u.a.createElement("td",null,u.a.createElement(w.a,{data:{date:e.date_added,format:"D MMM YY"}})):u.a.createElement("td",null,"#",e.user_rank),u.a.createElement("td",null,e.user_name),"coins-distributed"==o&&u.a.createElement("td",{className:"xtext-ellipsis"},e.message?e.message:"--"),u.a.createElement("td",null,u.a.createElement("img",{src:f.a.REWARD_ICON,alt:""}),"coins-distributed"==o?e.points:e.coin_earned)))})):u.a.createElement("tbody",null,u.a.createElement("tr",null,u.a.createElement("td",{colSpan:"8"},0!=i||a?u.a.createElement(T.a,null):u.a.createElement("div",{className:"no-records"},C.Cg))))))),b.a.isUndefined(this.props.FromDashboard)?i>C.Vf&&u.a.createElement("div",{className:"custom-pagination"},u.a.createElement(P.a,{activePage:n,itemsCountPerPage:r,totalItemsCount:i,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})):"topearner"==this.props.viewType?u.a.createElement("div",{className:"view-all-box"},u.a.createElement("a",{onClick:function(){return e.props.history.push("/coins/top-earner")},className:"view-all"},"View All")):u.a.createElement("div",{className:"view-all-box"},u.a.createElement("a",{onClick:function(){return e.props.history.push("/coins/top-redeemer")},className:"view-all"},"View All"))))}}]),a}(o.Component);t.default=Object(N.g)(x)}}]);
//# sourceMappingURL=15.f87e2230.chunk.js.map