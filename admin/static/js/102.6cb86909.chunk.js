(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[102,218],{507:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==c(e)&&"function"!==typeof e)return{default:e};var t=u();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var s=n?Object.getOwnPropertyDescriptor(e,r):null;s&&(s.get||s.set)?Object.defineProperty(a,r,s):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=o(a(1)),s=o(a(509)),l=o(a(510)),i=o(a(3));function o(e){return e&&e.__esModule?e:{default:e}}function u(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return u=function(){return e},e}function c(e){return(c="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function f(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function d(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function p(e,t){return!t||"object"!==c(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function m(e){return(m=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function g(e,t){return(g=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function b(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var h=function(e){function t(){return f(this,t),p(this,m(t).apply(this,arguments))}var a,r,o;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&g(e,t)}(t,e),a=t,(r=[{key:"isFirstPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"isPrevPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isNextPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isLastPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"buildPages",value:function(){for(var e=[],t=this.props,a=t.itemsCountPerPage,r=t.pageRangeDisplayed,o=t.activePage,u=t.prevPageText,c=t.nextPageText,f=t.firstPageText,d=t.lastPageText,p=t.totalItemsCount,m=t.onChange,g=t.activeClass,b=t.itemClass,h=t.itemClassFirst,v=t.itemClassPrev,y=t.itemClassNext,C=t.itemClassLast,P=t.activeLinkClass,_=t.disabledClass,E=(t.hideDisabled,t.hideNavigation,t.linkClass),k=t.linkClassFirst,O=t.linkClassPrev,x=t.linkClassNext,T=t.linkClassLast,N=(t.hideFirstLastPages,t.getPageUrl),j=new s.default(a,r).build(p,o),D=j.first_page;D<=j.last_page;D++)e.push(n.default.createElement(l.default,{isActive:D===o,key:D,href:N(D),pageNumber:D,pageText:D+"",onClick:m,itemClass:b,linkClass:E,activeClass:g,activeLinkClass:P,ariaLabel:"Go to page number ".concat(D)}));return this.isPrevPageVisible(j.has_previous_page)&&e.unshift(n.default.createElement(l.default,{key:"prev"+j.previous_page,href:N(j.previous_page),pageNumber:j.previous_page,onClick:m,pageText:u,isDisabled:!j.has_previous_page,itemClass:(0,i.default)(b,v),linkClass:(0,i.default)(E,O),disabledClass:_,ariaLabel:"Go to previous page"})),this.isFirstPageVisible(j.has_previous_page)&&e.unshift(n.default.createElement(l.default,{key:"first",href:N(1),pageNumber:1,onClick:m,pageText:f,isDisabled:!j.has_previous_page,itemClass:(0,i.default)(b,h),linkClass:(0,i.default)(E,k),disabledClass:_,ariaLabel:"Go to first page"})),this.isNextPageVisible(j.has_next_page)&&e.push(n.default.createElement(l.default,{key:"next"+j.next_page,href:N(j.next_page),pageNumber:j.next_page,onClick:m,pageText:c,isDisabled:!j.has_next_page,itemClass:(0,i.default)(b,y),linkClass:(0,i.default)(E,x),disabledClass:_,ariaLabel:"Go to next page"})),this.isLastPageVisible(j.has_next_page)&&e.push(n.default.createElement(l.default,{key:"last",href:N(j.total_pages),pageNumber:j.total_pages,onClick:m,pageText:d,isDisabled:j.current_page===j.total_pages,itemClass:(0,i.default)(b,C),linkClass:(0,i.default)(E,T),disabledClass:_,ariaLabel:"Go to last page"})),e}},{key:"render",value:function(){var e=this.buildPages();return n.default.createElement("ul",{className:this.props.innerClass},e)}}])&&d(a.prototype,r),o&&d(a,o),t}(n.default.Component);t.default=h,b(h,"propTypes",{totalItemsCount:r.default.number.isRequired,onChange:r.default.func.isRequired,activePage:r.default.number,itemsCountPerPage:r.default.number,pageRangeDisplayed:r.default.number,prevPageText:r.default.oneOfType([r.default.string,r.default.element]),nextPageText:r.default.oneOfType([r.default.string,r.default.element]),lastPageText:r.default.oneOfType([r.default.string,r.default.element]),firstPageText:r.default.oneOfType([r.default.string,r.default.element]),disabledClass:r.default.string,hideDisabled:r.default.bool,hideNavigation:r.default.bool,innerClass:r.default.string,itemClass:r.default.string,itemClassFirst:r.default.string,itemClassPrev:r.default.string,itemClassNext:r.default.string,itemClassLast:r.default.string,linkClass:r.default.string,activeClass:r.default.string,activeLinkClass:r.default.string,linkClassFirst:r.default.string,linkClassPrev:r.default.string,linkClassNext:r.default.string,linkClassLast:r.default.string,hideFirstLastPages:r.default.bool,getPageUrl:r.default.func}),b(h,"defaultProps",{itemsCountPerPage:10,pageRangeDisplayed:5,activePage:1,prevPageText:"\u27e8",firstPageText:"\xab",nextPageText:"\u27e9",lastPageText:"\xbb",innerClass:"pagination",itemClass:void 0,linkClass:void 0,activeLinkClass:void 0,hideFirstLastPages:!1,getPageUrl:function(e){return"#"}})},509:function(e,t){function a(e,t){if(!(this instanceof a))return new a(e,t);this.per_page=e||25,this.length=t||10}e.exports=a,a.prototype.build=function(e,t){var a=Math.ceil(e/this.per_page);e=parseInt(e,10),(t=parseInt(t,10)||1)<1&&(t=1),t>a&&(t=a);var n=Math.max(1,t-Math.floor(this.length/2)),r=Math.min(a,t+Math.floor(this.length/2));r-n+1<this.length&&(t<a/2?r=Math.min(a,r+(this.length-(r-n))):n=Math.max(1,n-(this.length-(r-n)))),r-n+1>this.length&&(t>a/2?n++:r--);var s=this.per_page*(t-1);s<0&&(s=0);var l=this.per_page*t-1;return l<0&&(l=0),l>Math.max(e-1,0)&&(l=Math.max(e-1,0)),{total_pages:a,pages:Math.min(r-n+1,a),current_page:t,first_page:n,last_page:r,previous_page:t-1,next_page:t+1,has_previous_page:t>1,has_next_page:t<a,total_results:e,results:Math.min(l-s+1,e),first_result:s,last_result:l}}},510:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!==typeof e)return{default:e};var t=i();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var s=n?Object.getOwnPropertyDescriptor(e,r):null;s&&(s.get||s.set)?Object.defineProperty(a,r,s):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=l(a(1)),s=l(a(3));function l(e){return e&&e.__esModule?e:{default:e}}function i(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return i=function(){return e},e}function o(e){return(o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function c(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function f(e,t){return!t||"object"!==o(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function d(e){return(d=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function p(e,t){return(p=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function m(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var g=function(e){function t(){return u(this,t),f(this,d(t).apply(this,arguments))}var a,r,l;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&p(e,t)}(t,e),a=t,(r=[{key:"handleClick",value:function(e){var t=this.props,a=t.isDisabled,n=t.pageNumber;e.preventDefault(),a||this.props.onClick(n)}},{key:"render",value:function(){var e,t=this.props,a=t.pageText,r=(t.pageNumber,t.activeClass),l=t.itemClass,i=t.linkClass,o=t.activeLinkClass,u=t.disabledClass,c=t.isActive,f=t.isDisabled,d=t.href,p=t.ariaLabel,g=(0,s.default)(l,(m(e={},r,c),m(e,u,f),e)),b=(0,s.default)(i,m({},o,c));return n.default.createElement("li",{className:g,onClick:this.handleClick.bind(this)},n.default.createElement("a",{className:b,href:d,"aria-label":p},a))}}])&&c(a.prototype,r),l&&c(a,l),t}(n.Component);t.default=g,m(g,"propTypes",{pageText:r.default.oneOfType([r.default.string,r.default.element]),pageNumber:r.default.number.isRequired,onClick:r.default.func.isRequired,isActive:r.default.bool.isRequired,isDisabled:r.default.bool,activeClass:r.default.string,activeLinkClass:r.default.string,itemClass:r.default.string,linkClass:r.default.string,disabledClass:r.default.string,href:r.default.string}),m(g,"defaultProps",{activeClass:"active",disabledClass:"disabled",itemClass:void 0,linkClass:void 0,activeLinkCLass:void 0,isActive:!1,isDisabled:!1,href:"#"})},513:function(e,t,a){"use strict";a.d(t,"a",(function(){return o}));var n=a(0),r=a.n(n),s=a(523),l=a.n(s),i=a(9);function o(e){var t=e.data,a=t.date,n=t.format;return a?r.a.createElement(l.a,{date:i.a.getUtcToLocal(a),format:n}):""}},516:function(e,t,a){"use strict";var n=a(6),r=a(7),s=a(0),l=a.n(s),i=a(1),o=a.n(i),u=a(3),c=a.n(u),f=a(4),d={className:o.a.string,cssModule:o.a.object,size:o.a.string,bordered:o.a.bool,borderless:o.a.bool,striped:o.a.bool,dark:o.a.bool,hover:o.a.bool,responsive:o.a.oneOfType([o.a.bool,o.a.string]),tag:f.q,responsiveTag:f.q,innerRef:o.a.oneOfType([o.a.func,o.a.string,o.a.object])},p=function(e){var t=e.className,a=e.cssModule,s=e.size,i=e.bordered,o=e.borderless,u=e.striped,d=e.dark,p=e.hover,m=e.responsive,g=e.tag,b=e.responsiveTag,h=e.innerRef,v=Object(r.a)(e,["className","cssModule","size","bordered","borderless","striped","dark","hover","responsive","tag","responsiveTag","innerRef"]),y=Object(f.m)(c()(t,"table",!!s&&"table-"+s,!!i&&"table-bordered",!!o&&"table-borderless",!!u&&"table-striped",!!d&&"table-dark",!!p&&"table-hover"),a),C=l.a.createElement(g,Object(n.a)({},v,{ref:h,className:y}));if(m){var P=Object(f.m)(!0===m?"table-responsive":"table-responsive-"+m,a);return l.a.createElement(b,{className:P},C)}return C};p.propTypes=d,p.defaultProps={tag:"table",responsiveTag:"div"},t.a=p},871:function(e,t,a){"use strict";a.r(t),a.d(t,"default",(function(){return C}));var n=a(11),r=a(12),s=a(15),l=a(14),i=a(0),o=a.n(i),u=a(155),c=a(156),f=a(516),d=a(5),p=a.n(d),m=a(8),g=a(9),b=a(18),h=a(507),v=a.n(h),y=a(513),C=function(e){Object(s.a)(a,e);var t=Object(l.a)(a);function a(e){var r;return Object(n.a)(this,a),(r=t.call(this,e)).state={TotalPromo:0,PERPAGE:m.Vf,CURRENT_PAGE:1,promo_type:r.props.match.params.promo_type},r}return Object(r.a)(a,[{key:"componentDidMount",value:function(){this.getPromoCodeDetail()}},{key:"getPromoCodeDetail",value:function(){var e=this,t=this.state,a=t.CURRENT_PAGE,n={items_perpage:t.PERPAGE,total_items:0,current_page:a,sort_order:"DESC",sort_field:"PCE.added_date",promo_code:this.props.match.params.promo_code};g.a.Rest(m.nk+m.We,n).then((function(t){t.response_code==m.qk?e.setState({PromoCodeList:t.data.result,TotalPromo:t.data.total}):b.notify.show(m.Ri,"error",3e3)})).catch((function(e){b.notify.show(m.Ri,"error",3e3)}))}},{key:"handlePageChange",value:function(e){var t=this;e!==this.state.CURRENT_PAGE&&this.setState({CURRENT_PAGE:e},(function(){t.getPromoCodeDetail()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.promo_type,n=t.PromoCodeList,r=t.CURRENT_PAGE,s=t.PERPAGE,l=t.TotalPromo;return o.a.createElement("div",{className:"animated fadeIn promocode-view mt-4"},o.a.createElement(u.a,{className:"mb-5"},o.a.createElement(c.a,{md:12},o.a.createElement("h1",{className:"h1-cls"},"Promo Code Detail List"))),o.a.createElement(u.a,null,o.a.createElement(c.a,{md:12,className:"table-responsive common-table"},o.a.createElement(f.a,null,o.a.createElement("thead",null,o.a.createElement("tr",null,o.a.createElement("th",{className:"left-th text-center"},"Type"),o.a.createElement("th",null,"Promo code"),o.a.createElement("th",null,"Name"),o.a.createElement("th",null,"Amount Received"),3==a?o.a.createElement(i.Fragment,null,o.a.createElement("th",null,"Game Name"),o.a.createElement("th",null,"Entry Fee"),o.a.createElement("th",null,"Contest Scheduled Date")):o.a.createElement("th",null,"Deposit amount"),o.a.createElement("th",null,"Promocode Used Date"),o.a.createElement("th",{className:"right-th"},"Status"))),l>0?p.a.map(n,(function(t,n){return o.a.createElement("tbody",{key:n},o.a.createElement("tr",null,o.a.createElement("td",null,0==t.type&&o.a.createElement("td",null,"First Deposit"),1==t.type&&o.a.createElement("td",null,"Deposit Range"),2==t.type&&o.a.createElement("td",null,"Promo Code"),3==t.type&&o.a.createElement("td",null,"Contest Join")),o.a.createElement("td",null,t.promo_code),o.a.createElement("td",{onClick:function(){return e.props.history.push("/profile/"+t.user_unique_id)},className:"user-name text-ellipsis"},t.user_full_name),3==a?o.a.createElement(i.Fragment,null,o.a.createElement("td",null,t.amount_received),o.a.createElement("td",null,t.contest_name),o.a.createElement("td",null,t.entry_fee)):o.a.createElement("td",null,t.amount_received),o.a.createElement("td",null,t.deposit_amount),o.a.createElement("td",null,o.a.createElement(y.a,{data:{date:t.added_date,format:"D-MMM-YYYY hh:mm A"}})),o.a.createElement("td",null,"0"==t.status?"Pending":"1"==t.status?"Success":"2"==t.status?"Failed":"--")))})):o.a.createElement("tbody",null,o.a.createElement("tr",null,o.a.createElement("td",{colSpan:"12"},o.a.createElement("div",{className:"no-records"},"No Records Found."))))))),l>s&&o.a.createElement("div",{className:"custom-pagination lobby-paging"},o.a.createElement(v.a,{activePage:r,itemsCountPerPage:s,totalItemsCount:l,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})))}}]),a}(i.Component)}}]);
//# sourceMappingURL=102.6cb86909.chunk.js.map