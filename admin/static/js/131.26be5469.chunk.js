(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[131,218],{507:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==c(e)&&"function"!==typeof e)return{default:e};var t=u();if(t&&t.has(e))return t.get(e);var a={},r=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var n in e)if(Object.prototype.hasOwnProperty.call(e,n)){var s=r?Object.getOwnPropertyDescriptor(e,n):null;s&&(s.get||s.set)?Object.defineProperty(a,n,s):a[n]=e[n]}a.default=e,t&&t.set(e,a);return a}(a(0)),n=o(a(1)),s=o(a(509)),i=o(a(510)),l=o(a(3));function o(e){return e&&e.__esModule?e:{default:e}}function u(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return u=function(){return e},e}function c(e){return(c="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function p(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function d(e,t){for(var a=0;a<t.length;a++){var r=t[a];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function f(e,t){return!t||"object"!==c(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function b(e){return(b=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function g(e,t){return(g=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function m(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var h=function(e){function t(){return p(this,t),f(this,b(t).apply(this,arguments))}var a,n,o;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&g(e,t)}(t,e),a=t,(n=[{key:"isFirstPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"isPrevPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isNextPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isLastPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"buildPages",value:function(){for(var e=[],t=this.props,a=t.itemsCountPerPage,n=t.pageRangeDisplayed,o=t.activePage,u=t.prevPageText,c=t.nextPageText,p=t.firstPageText,d=t.lastPageText,f=t.totalItemsCount,b=t.onChange,g=t.activeClass,m=t.itemClass,h=t.itemClassFirst,v=t.itemClassPrev,y=t.itemClassNext,C=t.itemClassLast,P=t.activeLinkClass,_=t.disabledClass,k=(t.hideDisabled,t.hideNavigation,t.linkClass),E=t.linkClassFirst,O=t.linkClassPrev,N=t.linkClassNext,w=t.linkClassLast,T=(t.hideFirstLastPages,t.getPageUrl),x=new s.default(a,n).build(f,o),j=x.first_page;j<=x.last_page;j++)e.push(r.default.createElement(i.default,{isActive:j===o,key:j,href:T(j),pageNumber:j,pageText:j+"",onClick:b,itemClass:m,linkClass:k,activeClass:g,activeLinkClass:P,ariaLabel:"Go to page number ".concat(j)}));return this.isPrevPageVisible(x.has_previous_page)&&e.unshift(r.default.createElement(i.default,{key:"prev"+x.previous_page,href:T(x.previous_page),pageNumber:x.previous_page,onClick:b,pageText:u,isDisabled:!x.has_previous_page,itemClass:(0,l.default)(m,v),linkClass:(0,l.default)(k,O),disabledClass:_,ariaLabel:"Go to previous page"})),this.isFirstPageVisible(x.has_previous_page)&&e.unshift(r.default.createElement(i.default,{key:"first",href:T(1),pageNumber:1,onClick:b,pageText:p,isDisabled:!x.has_previous_page,itemClass:(0,l.default)(m,h),linkClass:(0,l.default)(k,E),disabledClass:_,ariaLabel:"Go to first page"})),this.isNextPageVisible(x.has_next_page)&&e.push(r.default.createElement(i.default,{key:"next"+x.next_page,href:T(x.next_page),pageNumber:x.next_page,onClick:b,pageText:c,isDisabled:!x.has_next_page,itemClass:(0,l.default)(m,y),linkClass:(0,l.default)(k,N),disabledClass:_,ariaLabel:"Go to next page"})),this.isLastPageVisible(x.has_next_page)&&e.push(r.default.createElement(i.default,{key:"last",href:T(x.total_pages),pageNumber:x.total_pages,onClick:b,pageText:d,isDisabled:x.current_page===x.total_pages,itemClass:(0,l.default)(m,C),linkClass:(0,l.default)(k,w),disabledClass:_,ariaLabel:"Go to last page"})),e}},{key:"render",value:function(){var e=this.buildPages();return r.default.createElement("ul",{className:this.props.innerClass},e)}}])&&d(a.prototype,n),o&&d(a,o),t}(r.default.Component);t.default=h,m(h,"propTypes",{totalItemsCount:n.default.number.isRequired,onChange:n.default.func.isRequired,activePage:n.default.number,itemsCountPerPage:n.default.number,pageRangeDisplayed:n.default.number,prevPageText:n.default.oneOfType([n.default.string,n.default.element]),nextPageText:n.default.oneOfType([n.default.string,n.default.element]),lastPageText:n.default.oneOfType([n.default.string,n.default.element]),firstPageText:n.default.oneOfType([n.default.string,n.default.element]),disabledClass:n.default.string,hideDisabled:n.default.bool,hideNavigation:n.default.bool,innerClass:n.default.string,itemClass:n.default.string,itemClassFirst:n.default.string,itemClassPrev:n.default.string,itemClassNext:n.default.string,itemClassLast:n.default.string,linkClass:n.default.string,activeClass:n.default.string,activeLinkClass:n.default.string,linkClassFirst:n.default.string,linkClassPrev:n.default.string,linkClassNext:n.default.string,linkClassLast:n.default.string,hideFirstLastPages:n.default.bool,getPageUrl:n.default.func}),m(h,"defaultProps",{itemsCountPerPage:10,pageRangeDisplayed:5,activePage:1,prevPageText:"\u27e8",firstPageText:"\xab",nextPageText:"\u27e9",lastPageText:"\xbb",innerClass:"pagination",itemClass:void 0,linkClass:void 0,activeLinkClass:void 0,hideFirstLastPages:!1,getPageUrl:function(e){return"#"}})},509:function(e,t){function a(e,t){if(!(this instanceof a))return new a(e,t);this.per_page=e||25,this.length=t||10}e.exports=a,a.prototype.build=function(e,t){var a=Math.ceil(e/this.per_page);e=parseInt(e,10),(t=parseInt(t,10)||1)<1&&(t=1),t>a&&(t=a);var r=Math.max(1,t-Math.floor(this.length/2)),n=Math.min(a,t+Math.floor(this.length/2));n-r+1<this.length&&(t<a/2?n=Math.min(a,n+(this.length-(n-r))):r=Math.max(1,r-(this.length-(n-r)))),n-r+1>this.length&&(t>a/2?r++:n--);var s=this.per_page*(t-1);s<0&&(s=0);var i=this.per_page*t-1;return i<0&&(i=0),i>Math.max(e-1,0)&&(i=Math.max(e-1,0)),{total_pages:a,pages:Math.min(n-r+1,a),current_page:t,first_page:r,last_page:n,previous_page:t-1,next_page:t+1,has_previous_page:t>1,has_next_page:t<a,total_results:e,results:Math.min(i-s+1,e),first_result:s,last_result:i}}},510:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var r=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!==typeof e)return{default:e};var t=l();if(t&&t.has(e))return t.get(e);var a={},r=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var n in e)if(Object.prototype.hasOwnProperty.call(e,n)){var s=r?Object.getOwnPropertyDescriptor(e,n):null;s&&(s.get||s.set)?Object.defineProperty(a,n,s):a[n]=e[n]}a.default=e,t&&t.set(e,a);return a}(a(0)),n=i(a(1)),s=i(a(3));function i(e){return e&&e.__esModule?e:{default:e}}function l(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return l=function(){return e},e}function o(e){return(o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function c(e,t){for(var a=0;a<t.length;a++){var r=t[a];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}function p(e,t){return!t||"object"!==o(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function d(e){return(d=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function f(e,t){return(f=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function b(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var g=function(e){function t(){return u(this,t),p(this,d(t).apply(this,arguments))}var a,n,i;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&f(e,t)}(t,e),a=t,(n=[{key:"handleClick",value:function(e){var t=this.props,a=t.isDisabled,r=t.pageNumber;e.preventDefault(),a||this.props.onClick(r)}},{key:"render",value:function(){var e,t=this.props,a=t.pageText,n=(t.pageNumber,t.activeClass),i=t.itemClass,l=t.linkClass,o=t.activeLinkClass,u=t.disabledClass,c=t.isActive,p=t.isDisabled,d=t.href,f=t.ariaLabel,g=(0,s.default)(i,(b(e={},n,c),b(e,u,p),e)),m=(0,s.default)(l,b({},o,c));return r.default.createElement("li",{className:g,onClick:this.handleClick.bind(this)},r.default.createElement("a",{className:m,href:d,"aria-label":f},a))}}])&&c(a.prototype,n),i&&c(a,i),t}(r.Component);t.default=g,b(g,"propTypes",{pageText:n.default.oneOfType([n.default.string,n.default.element]),pageNumber:n.default.number.isRequired,onClick:n.default.func.isRequired,isActive:n.default.bool.isRequired,isDisabled:n.default.bool,activeClass:n.default.string,activeLinkClass:n.default.string,itemClass:n.default.string,linkClass:n.default.string,disabledClass:n.default.string,href:n.default.string}),b(g,"defaultProps",{activeClass:"active",disabledClass:"disabled",itemClass:void 0,linkClass:void 0,activeLinkCLass:void 0,isActive:!1,isDisabled:!1,href:"#"})},516:function(e,t,a){"use strict";var r=a(6),n=a(7),s=a(0),i=a.n(s),l=a(1),o=a.n(l),u=a(3),c=a.n(u),p=a(4),d={className:o.a.string,cssModule:o.a.object,size:o.a.string,bordered:o.a.bool,borderless:o.a.bool,striped:o.a.bool,dark:o.a.bool,hover:o.a.bool,responsive:o.a.oneOfType([o.a.bool,o.a.string]),tag:p.q,responsiveTag:p.q,innerRef:o.a.oneOfType([o.a.func,o.a.string,o.a.object])},f=function(e){var t=e.className,a=e.cssModule,s=e.size,l=e.bordered,o=e.borderless,u=e.striped,d=e.dark,f=e.hover,b=e.responsive,g=e.tag,m=e.responsiveTag,h=e.innerRef,v=Object(n.a)(e,["className","cssModule","size","bordered","borderless","striped","dark","hover","responsive","tag","responsiveTag","innerRef"]),y=Object(p.m)(c()(t,"table",!!s&&"table-"+s,!!l&&"table-bordered",!!o&&"table-borderless",!!u&&"table-striped",!!d&&"table-dark",!!f&&"table-hover"),a),C=i.a.createElement(g,Object(r.a)({},v,{ref:h,className:y}));if(b){var P=Object(p.m)(!0===b?"table-responsive":"table-responsive-"+b,a);return i.a.createElement(m,{className:P},C)}return C};f.propTypes=d,f.defaultProps={tag:"table",responsiveTag:"div"},t.a=f},673:function(e,t,a){"use strict";a.r(t);var r=a(11),n=a(12),s=a(15),i=a(14),l=a(0),o=a.n(l),u=a(155),c=a(156),p=a(516),d=a(46),f=a(5),b=a.n(f),g=a(9),m=a(8),h=a(507),v=a.n(h),y=a(18),C=a(17),P=a(157),_=function(e){Object(s.a)(a,e);var t=Object(i.a)(a);function a(e){var n;return Object(r.a)(this,a),(n=t.call(this,e)).state={CURRENT_PAGE:1,PERPAGE:b.a.isUndefined(n.props.FromDashboard)?m.Vf:10,Pathname:"",MostWinBidData:[],ListPosting:!1},n}return Object(n.a)(a,[{key:"componentDidMount",value:function(){var e=this,t=this.props.history.location.pathname.split(/[/ ]+/).pop();this.setState({Pathname:t},(function(){e.getLeaderbordData()}))}},{key:"getLeaderbordData",value:function(){var e=this;this.setState({ListPosting:!0});var t=this.state,a=t.CURRENT_PAGE,r=t.PERPAGE,n=t.Pathname,s={items_perpage:r,current_page:a},i="";i="most-bid"==n||"mostbid"==this.props.viewType?m.Wg:m.Xg,g.a.Rest(m.nk+i,s).then((function(t){t.response_code==m.qk&&(1==a&&e.setState({Total:t.data.total}),e.setState({MostWinBidData:t.data.list,Total:t.data.total,ListPosting:!1}))})).catch((function(e){y.notify.show(m.Ri,"error",5e3)}))}},{key:"handlePageChange",value:function(e){var t=this;this.setState({CURRENT_PAGE:e},(function(){t.getLeaderbordData()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.ListPosting,r=t.CURRENT_PAGE,n=t.PERPAGE,s=t.Total,i=t.Pathname,l=t.MostWinBidData;return o.a.createElement(o.a.Fragment,null,o.a.createElement("div",{className:"top-earner-sc ".concat(b.a.isUndefined(this.props.FromDashboard)?"":"bg-white")},b.a.isUndefined(this.props.FromDashboard)&&o.a.createElement(o.a.Fragment,null,o.a.createElement(u.a,null,o.a.createElement(c.a,{md:6},o.a.createElement("div",{className:"float-left"},o.a.createElement("div",{className:"top-earner"},"most-win"==i&&"Most Win","most-bid"==i&&"Most Bid"),o.a.createElement("div",{className:"leader-board"},"Leaderboard"))),o.a.createElement(c.a,{md:6},o.a.createElement("div",{onClick:function(){return e.props.history.push("/open-predictor/dashboard")},className:"go-back"},"<"," Back")))),o.a.createElement(u.a,null,o.a.createElement(c.a,{md:12,className:"table-responsive common-table"},o.a.createElement(p.a,{className:"mb-0"},o.a.createElement("thead",null,"mostwin"==this.props.viewType?o.a.createElement("tr",{className:"dashboard-view"},o.a.createElement("th",{colSpan:"3"},"Leaderboard - Most Win")):"mostbid"==this.props.viewType?o.a.createElement("tr",{className:"dashboard-view"},o.a.createElement("th",{colSpan:"3"},"Leaderboard - Most Bid")):o.a.createElement("tr",null,o.a.createElement("th",{className:"left-th pl-3"},"Rank"),o.a.createElement("th",null,"Username"),"coins-distributed"==i&&o.a.createElement("th",null,"Event"),o.a.createElement("th",{className:"right-th"},"Coin Earned"))),s>0?b.a.map(l,(function(t,a){return o.a.createElement("tbody",{key:a},o.a.createElement("tr",null,o.a.createElement("td",null,o.a.createElement("b",null,t.user_rank)),o.a.createElement("td",null,t.user_name),o.a.createElement("td",null,o.a.createElement("img",{className:"mr-1",src:d.a.REWARD_ICON,alt:""}),"mostbid"==e.props.viewType||"most-bid"==i?t.coin_invested:t.coin_earned)))})):o.a.createElement("tbody",null,o.a.createElement("tr",null,o.a.createElement("td",{colSpan:"8"},0!=s||a?o.a.createElement(P.a,null):o.a.createElement("div",{className:"no-records"},m.Cg))))))),b.a.isUndefined(this.props.FromDashboard)?s>m.Vf&&o.a.createElement("div",{className:"custom-pagination"},o.a.createElement(v.a,{activePage:r,itemsCountPerPage:n,totalItemsCount:s,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})):"mostwin"==this.props.viewType?o.a.createElement("div",{className:"view-all-box"},o.a.createElement("a",{onClick:function(){return e.props.history.push("/open-predictor/most-win")},className:"view-all"},"View All")):o.a.createElement("div",{className:"view-all-box"},o.a.createElement("a",{onClick:function(){return e.props.history.push("/open-predictor/most-bid")},className:"view-all"},"View All"))))}}]),a}(l.Component);t.default=Object(C.g)(_)}}]);
//# sourceMappingURL=131.26be5469.chunk.js.map