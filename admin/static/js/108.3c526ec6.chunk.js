(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[108,218],{507:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==c(e)&&"function"!==typeof e)return{default:e};var t=u();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var s=n?Object.getOwnPropertyDescriptor(e,r):null;s&&(s.get||s.set)?Object.defineProperty(a,r,s):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=o(a(1)),s=o(a(509)),l=o(a(510)),i=o(a(3));function o(e){return e&&e.__esModule?e:{default:e}}function u(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return u=function(){return e},e}function c(e){return(c="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function p(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function m(e,t){return!t||"object"!==c(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function f(e){return(f=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function h(e,t){return(h=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function g(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var b=function(e){function t(){return d(this,t),m(this,f(t).apply(this,arguments))}var a,r,o;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&h(e,t)}(t,e),a=t,(r=[{key:"isFirstPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"isPrevPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isNextPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isLastPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"buildPages",value:function(){for(var e=[],t=this.props,a=t.itemsCountPerPage,r=t.pageRangeDisplayed,o=t.activePage,u=t.prevPageText,c=t.nextPageText,d=t.firstPageText,p=t.lastPageText,m=t.totalItemsCount,f=t.onChange,h=t.activeClass,g=t.itemClass,b=t.itemClassFirst,y=t.itemClassPrev,v=t.itemClassNext,_=t.itemClassLast,C=t.activeLinkClass,P=t.disabledClass,E=(t.hideDisabled,t.hideNavigation,t.linkClass),D=t.linkClassFirst,k=t.linkClassPrev,T=t.linkClassNext,w=t.linkClassLast,O=(t.hideFirstLastPages,t.getPageUrl),N=new s.default(a,r).build(m,o),x=N.first_page;x<=N.last_page;x++)e.push(n.default.createElement(l.default,{isActive:x===o,key:x,href:O(x),pageNumber:x,pageText:x+"",onClick:f,itemClass:g,linkClass:E,activeClass:h,activeLinkClass:C,ariaLabel:"Go to page number ".concat(x)}));return this.isPrevPageVisible(N.has_previous_page)&&e.unshift(n.default.createElement(l.default,{key:"prev"+N.previous_page,href:O(N.previous_page),pageNumber:N.previous_page,onClick:f,pageText:u,isDisabled:!N.has_previous_page,itemClass:(0,i.default)(g,y),linkClass:(0,i.default)(E,k),disabledClass:P,ariaLabel:"Go to previous page"})),this.isFirstPageVisible(N.has_previous_page)&&e.unshift(n.default.createElement(l.default,{key:"first",href:O(1),pageNumber:1,onClick:f,pageText:d,isDisabled:!N.has_previous_page,itemClass:(0,i.default)(g,b),linkClass:(0,i.default)(E,D),disabledClass:P,ariaLabel:"Go to first page"})),this.isNextPageVisible(N.has_next_page)&&e.push(n.default.createElement(l.default,{key:"next"+N.next_page,href:O(N.next_page),pageNumber:N.next_page,onClick:f,pageText:c,isDisabled:!N.has_next_page,itemClass:(0,i.default)(g,v),linkClass:(0,i.default)(E,T),disabledClass:P,ariaLabel:"Go to next page"})),this.isLastPageVisible(N.has_next_page)&&e.push(n.default.createElement(l.default,{key:"last",href:O(N.total_pages),pageNumber:N.total_pages,onClick:f,pageText:p,isDisabled:N.current_page===N.total_pages,itemClass:(0,i.default)(g,_),linkClass:(0,i.default)(E,w),disabledClass:P,ariaLabel:"Go to last page"})),e}},{key:"render",value:function(){var e=this.buildPages();return n.default.createElement("ul",{className:this.props.innerClass},e)}}])&&p(a.prototype,r),o&&p(a,o),t}(n.default.Component);t.default=b,g(b,"propTypes",{totalItemsCount:r.default.number.isRequired,onChange:r.default.func.isRequired,activePage:r.default.number,itemsCountPerPage:r.default.number,pageRangeDisplayed:r.default.number,prevPageText:r.default.oneOfType([r.default.string,r.default.element]),nextPageText:r.default.oneOfType([r.default.string,r.default.element]),lastPageText:r.default.oneOfType([r.default.string,r.default.element]),firstPageText:r.default.oneOfType([r.default.string,r.default.element]),disabledClass:r.default.string,hideDisabled:r.default.bool,hideNavigation:r.default.bool,innerClass:r.default.string,itemClass:r.default.string,itemClassFirst:r.default.string,itemClassPrev:r.default.string,itemClassNext:r.default.string,itemClassLast:r.default.string,linkClass:r.default.string,activeClass:r.default.string,activeLinkClass:r.default.string,linkClassFirst:r.default.string,linkClassPrev:r.default.string,linkClassNext:r.default.string,linkClassLast:r.default.string,hideFirstLastPages:r.default.bool,getPageUrl:r.default.func}),g(b,"defaultProps",{itemsCountPerPage:10,pageRangeDisplayed:5,activePage:1,prevPageText:"\u27e8",firstPageText:"\xab",nextPageText:"\u27e9",lastPageText:"\xbb",innerClass:"pagination",itemClass:void 0,linkClass:void 0,activeLinkClass:void 0,hideFirstLastPages:!1,getPageUrl:function(e){return"#"}})},509:function(e,t){function a(e,t){if(!(this instanceof a))return new a(e,t);this.per_page=e||25,this.length=t||10}e.exports=a,a.prototype.build=function(e,t){var a=Math.ceil(e/this.per_page);e=parseInt(e,10),(t=parseInt(t,10)||1)<1&&(t=1),t>a&&(t=a);var n=Math.max(1,t-Math.floor(this.length/2)),r=Math.min(a,t+Math.floor(this.length/2));r-n+1<this.length&&(t<a/2?r=Math.min(a,r+(this.length-(r-n))):n=Math.max(1,n-(this.length-(r-n)))),r-n+1>this.length&&(t>a/2?n++:r--);var s=this.per_page*(t-1);s<0&&(s=0);var l=this.per_page*t-1;return l<0&&(l=0),l>Math.max(e-1,0)&&(l=Math.max(e-1,0)),{total_pages:a,pages:Math.min(r-n+1,a),current_page:t,first_page:n,last_page:r,previous_page:t-1,next_page:t+1,has_previous_page:t>1,has_next_page:t<a,total_results:e,results:Math.min(l-s+1,e),first_result:s,last_result:l}}},510:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!==typeof e)return{default:e};var t=i();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var s=n?Object.getOwnPropertyDescriptor(e,r):null;s&&(s.get||s.set)?Object.defineProperty(a,r,s):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=l(a(1)),s=l(a(3));function l(e){return e&&e.__esModule?e:{default:e}}function i(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return i=function(){return e},e}function o(e){return(o="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function u(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function c(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function d(e,t){return!t||"object"!==o(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function p(e){return(p=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function m(e,t){return(m=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function f(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var h=function(e){function t(){return u(this,t),d(this,p(t).apply(this,arguments))}var a,r,l;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&m(e,t)}(t,e),a=t,(r=[{key:"handleClick",value:function(e){var t=this.props,a=t.isDisabled,n=t.pageNumber;e.preventDefault(),a||this.props.onClick(n)}},{key:"render",value:function(){var e,t=this.props,a=t.pageText,r=(t.pageNumber,t.activeClass),l=t.itemClass,i=t.linkClass,o=t.activeLinkClass,u=t.disabledClass,c=t.isActive,d=t.isDisabled,p=t.href,m=t.ariaLabel,h=(0,s.default)(l,(f(e={},r,c),f(e,u,d),e)),g=(0,s.default)(i,f({},o,c));return n.default.createElement("li",{className:h,onClick:this.handleClick.bind(this)},n.default.createElement("a",{className:g,href:p,"aria-label":m},a))}}])&&c(a.prototype,r),l&&c(a,l),t}(n.Component);t.default=h,f(h,"propTypes",{pageText:r.default.oneOfType([r.default.string,r.default.element]),pageNumber:r.default.number.isRequired,onClick:r.default.func.isRequired,isActive:r.default.bool.isRequired,isDisabled:r.default.bool,activeClass:r.default.string,activeLinkClass:r.default.string,itemClass:r.default.string,linkClass:r.default.string,disabledClass:r.default.string,href:r.default.string}),f(h,"defaultProps",{activeClass:"active",disabledClass:"disabled",itemClass:void 0,linkClass:void 0,activeLinkCLass:void 0,isActive:!1,isDisabled:!1,href:"#"})},516:function(e,t,a){"use strict";var n=a(6),r=a(7),s=a(0),l=a.n(s),i=a(1),o=a.n(i),u=a(3),c=a.n(u),d=a(4),p={className:o.a.string,cssModule:o.a.object,size:o.a.string,bordered:o.a.bool,borderless:o.a.bool,striped:o.a.bool,dark:o.a.bool,hover:o.a.bool,responsive:o.a.oneOfType([o.a.bool,o.a.string]),tag:d.q,responsiveTag:d.q,innerRef:o.a.oneOfType([o.a.func,o.a.string,o.a.object])},m=function(e){var t=e.className,a=e.cssModule,s=e.size,i=e.bordered,o=e.borderless,u=e.striped,p=e.dark,m=e.hover,f=e.responsive,h=e.tag,g=e.responsiveTag,b=e.innerRef,y=Object(r.a)(e,["className","cssModule","size","bordered","borderless","striped","dark","hover","responsive","tag","responsiveTag","innerRef"]),v=Object(d.m)(c()(t,"table",!!s&&"table-"+s,!!i&&"table-bordered",!!o&&"table-borderless",!!u&&"table-striped",!!p&&"table-dark",!!m&&"table-hover"),a),_=l.a.createElement(h,Object(n.a)({},y,{ref:b,className:v}));if(f){var C=Object(d.m)(!0===f?"table-responsive":"table-responsive-"+f,a);return l.a.createElement(g,{className:C},_)}return _};m.propTypes=p,m.defaultProps={tag:"table",responsiveTag:"div"},t.a=m},544:function(e,t,a){"use strict";var n=a(11),r=a(12),s=a(15),l=a(14),i=a(0),o=a.n(i),u=a(536),c=a.n(u),d=(a(521),a(10)),p=(a(8),function(e){Object(s.a)(a,e);var t=Object(l.a)(a);function a(){return Object(n.a)(this,a),t.apply(this,arguments)}return Object(r.a)(a,[{key:"render",value:function(){var e=this,t=this.props.DateProps,a=t.disabled_date,n=t.min_date,r=t.max_date,s=t.class_name,l=t.year_dropdown,i=t.month_dropdown,u=t.sel_date,p=t.date_key,m=t.place_holder,f=t.show_time_select,h=t.time_format,g=t.time_intervals,b=t.time_caption,y=t.date_format,v=t.popup_placement;return o.a.createElement(c.a,{disabled:a,minDate:Object(d.e)(n)?null:new Date(n),maxDate:Object(d.e)(r)?null:new Date(r),className:s,showYearDropdown:l,showMonthDropdown:i,selected:Object(d.e)(u)?null:new Date(u),onChange:function(t){return e.props.DateProps.handleCallbackFn(t,p)},placeholderText:m,showTimeSelect:f,timeFormat:h,timeIntervals:g,timeCaption:b,dateFormat:y,popperPlacement:v||"bottom-start"})}}]),a}(i.Component));t.a=p},875:function(e,t,a){"use strict";a.r(t),a.d(t,"default",(function(){return N}));var n=a(24),r=a(59),s=a(11),l=a(12),i=a(82),o=a(15),u=a(14),c=a(0),d=a.n(c),p=a(155),m=a(156),f=a(499),h=a(154),g=a(516),b=a(5),y=a.n(b),v=a(8),_=a(9),C=a(18),P=a(107),E=(a(521),a(507)),D=a.n(E),k=a(157),T=a(10),w=a(544),O=[{value:"",label:"All"},{value:"0",label:"Pending"},{value:"1",label:"Success"},{value:"2",label:"Failed"}],N=function(e){Object(o.a)(a,e);var t=Object(u.a)(a);function a(e){var n;return Object(s.a)(this,a),(n=t.call(this,e)).getPaymentFilter=function(){_.a.Rest(v.nk+v.Zd,{}).then((function(e){if(e.response_code==v.qk){var t=[];t.push({value:0,label:"All"}),y.a.map(e.data,(function(e,a){t.push({value:a,label:e})})),n.setState({PaymentType:t})}else C.notify.show(v.Ri,"error",3e3)})).catch((function(e){C.notify.show(v.Ri,"error",3e3)}))},n.getReportUser=function(){n.setState({posting:!0});var e=n.state,t=e.PERPAGE,a=e.CURRENT_PAGE,r=e.Keyword,s=e.FromDate,l=e.ToDate,i=e.sortField,o=e.isDescOrder,u=e.SelectedPaymentType,c={status:e.TrStatusChange.value,items_perpage:t,total_items:0,current_page:a,sort_order:o?"ASC":"DESC",sort_field:i,csv:!1,from_date:s?T.i.getFormatedDateTime(s,"YYYY-MM-DD"):"",to_date:l?T.i.getFormatedDateTime(l,"YYYY-MM-DD"):"",keyword:r,payment_method:u.value};_.a.Rest(v.nk+v.df,c).then((function(e){e.response_code==v.qk?n.setState({posting:!1,UserReportList:e.data.result,TotalUser:e.data.total,TotalDeposit:e.data.total_deposit}):C.notify.show(v.Ri,"error",3e3)})).catch((function(e){C.notify.show(v.Ri,"error",3e3)}))},n.exportReport_Post=function(){var e=n.state,t=e.Keyword,a=e.FromDate,r=e.ToDate,s=e.sortField,l=e.isDescOrder,i=e.SelectedPaymentType,o={status:e.TrStatusChange.value,sort_order:l?"ASC":"DESC",sort_field:s,from_date:a,to_date:r,keyword:t,report_type:"user_deposit",payment_method:i.value};_.a.Rest(v.nk+v.nc,o).then((function(e){e.response_code==v.qk?C.notify.show(e.message,"success",5e3):C.notify.show(v.Ri,"error",3e3)})).catch((function(e){C.notify.show(v.Ri,"error",3e3)}))},n.exportReport_Get=function(){var e=n.state,t=e.Keyword,a=e.FromDate,r=e.ToDate,s=e.isDescOrder,l=e.sortField,i=e.TrStatusChange,o=e.SelectedPaymentType,u="",c="",d=s?"ASC":"DESC";""!=a&&""!=r&&(u=a?T.i.getFormatedDateTime(a,"YYYY-MM-DD"):"",c=r?T.i.getFormatedDateTime(r,"YYYY-MM-DD"):"");var p="&report_type=user_deposit&csv=1&keyword="+t+"&from_date="+u+"&to_date="+c+"&sort_order="+d+"&sort_field="+l+"&status="+i.value+"&payment_method="+o.value;T.i.exportFunction(p,"adminapi/index.php/report/get_report_user_deposit_amount?")},n.handleTypeChange=function(e,t){null!=e&&n.setState(Object(r.a)({},t,e),n.getReportUser)},n.handleDate=function(e,t){n.setState(Object(r.a)({},t,e),(function(){(n.state.FromDate||n.state.ToDate)&&n.getReportUser()}))},n.searchByUser=function(e){n.setState({Keyword:e.target.value},n.SearchCodeReq)},n.clearFilter=function(){n.setState({SelectedPaymentType:0,FromDate:new Date(Date.now()-24*(T.i.getTodayDate()-1)*60*60*1e3),ToDate:new Date,Keyword:"",isDescOrder:!0,sortField:"first_name"},(function(){n.getReportUser()}))},n.state={TotalUser:0,PERPAGE:v.Vf,CURRENT_PAGE:1,startDate:"",endDate:"",FromDate:new Date(Date.now()-24*(T.i.getTodayDate()-1)*60*60*1e3),ToDate:new Date,UserReportList:[],Keyword:"",sortField:"first_name",isDescOrder:!0,SelectedPaymentType:{value:"2",label:"PayTM"},PaymentType:[],TotalDeposit:"",posting:!1,TrStatusChange:""},n.SearchCodeReq=y.a.debounce(n.SearchCodeReq.bind(Object(i.a)(n)),500),n}return Object(l.a)(a,[{key:"componentDidMount",value:function(){this.getReportUser(),this.getPaymentFilter()}},{key:"handlePageChange",value:function(e){var t=this;this.setState({CURRENT_PAGE:e},(function(){t.getReportUser()}))}},{key:"SearchCodeReq",value:function(){this.state.Keyword.length>2&&this.getReportUser()}},{key:"sortContest",value:function(e,t){var a=e==this.state.sortField?!t:t;this.setState({sortField:e,isDescOrder:a,CURRENT_PAGE:1},this.getReportUser)}},{key:"render",value:function(){var e=this,t=this.state,a=t.UserReportList,r=t.CURRENT_PAGE,s=t.PERPAGE,l=t.TotalUser,i=t.Keyword,o=t.isDescOrder,u=t.SelectedPaymentType,b=t.PaymentType,C=t.TotalDeposit,E=t.posting,T=t.FromDate,N=t.ToDate,x=t.TrStatusChange,R={disabled_date:!1,show_time_select:!1,time_format:!1,time_intervals:!1,time_caption:!1,date_format:"dd/MM/yyyy",handleCallbackFn:this.handleDate,class_name:"form-control mr-3",year_dropdown:!0,month_dropdown:!0},S=Object(n.a)(Object(n.a)({},R),{},{min_date:!1,max_date:new Date(N),sel_date:new Date(T),date_key:"FromDate",place_holder:"From Date"}),j=Object(n.a)(Object(n.a)({},R),{},{min_date:new Date(T),max_date:new Date,sel_date:new Date(N),date_key:"ToDate",place_holder:"To Date"});return d.a.createElement(c.Fragment,null,d.a.createElement("div",{className:"animated fadeIn mt-4"},d.a.createElement(p.a,null,d.a.createElement(m.a,{md:12},d.a.createElement("h1",{className:"h1-cls"},"User Deposit Amount"))),d.a.createElement("div",{className:"user-deposit-amount"},d.a.createElement(p.a,{className:"xfilter-userlist mt-5"},d.a.createElement(m.a,{md:2},d.a.createElement("div",null,d.a.createElement("label",{className:"filter-label"},"Payment Method"),d.a.createElement(P.a,{isSearchable:!0,class:"form-control",options:b,menuIsOpen:!0,value:u,onChange:function(t){return e.handleTypeChange(t,"SelectedPaymentType")}}))),d.a.createElement(m.a,{md:2},d.a.createElement("div",null,d.a.createElement("label",{className:"filter-label"},"Status"),d.a.createElement(P.a,{isSearchable:!0,class:"form-control",options:O,placeholder:"Transaction Status",menuIsOpen:!0,value:x,onChange:function(t){return e.handleTypeChange(t,"TrStatusChange")}}))),d.a.createElement(m.a,{md:2},d.a.createElement("div",{className:"search-box"},d.a.createElement("label",{className:"filter-label"},"Search User"),d.a.createElement(f.a,{placeholder:"Search User",name:"code",value:i,onChange:this.searchByUser}))),d.a.createElement(m.a,{md:2},d.a.createElement("div",null,d.a.createElement("label",{className:"filter-label"},"Select From Date"),d.a.createElement(w.a,{DateProps:S}))),d.a.createElement(m.a,{md:2},d.a.createElement("div",null,d.a.createElement("label",{className:"filter-label"},"Select To Date"),d.a.createElement(w.a,{DateProps:j}))),d.a.createElement(m.a,{md:2},d.a.createElement("label",{className:"filter-label"},"Total Deposit"),d.a.createElement("h4",null,C))),d.a.createElement(p.a,{className:"filters-box"},d.a.createElement(m.a,{md:11},d.a.createElement("div",{className:"filters-area"},d.a.createElement(h.a,{className:"btn-secondary",onClick:function(){return e.clearFilter()}},"Clear Filters"))),d.a.createElement(m.a,{md:1,className:""},d.a.createElement("i",{className:"export-list icon-export",onClick:function(t){return l>v.oc?e.exportReport_Post():e.exportReport_Get()}}))),d.a.createElement(p.a,{className:"filters-box"},d.a.createElement(m.a,{md:12},d.a.createElement("div",{className:"filters-area"},d.a.createElement("h4",null,"Total Record Count:",l)))),d.a.createElement(p.a,null,d.a.createElement(m.a,{md:12,className:"table-responsive common-table"},d.a.createElement(g.a,null,d.a.createElement("thead",null,d.a.createElement("tr",null,d.a.createElement("th",null,"Order Id"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("user_name",o)}},"UserName"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("first_name",o)}},"Name"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("phone",o)}},"Phone"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("email",o)}},"Email"),d.a.createElement("th",null,"Transaction Id"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("payment_request",o)}},"Request Amount"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("payment_gateway_id",o)}},"Payment Mode"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("O.date_added",o)}},"Transaction Date "),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("added_date",o)}},"Member Since"),d.a.createElement("th",{className:"pointer",onClick:function(){return e.sortContest("last_deposit_date",o)}},"Status"))),a.length>0?y.a.map(a,(function(t,a){return d.a.createElement("tbody",{key:a},d.a.createElement("tr",null,d.a.createElement("td",null,t.order_unique_id),d.a.createElement("td",null,d.a.createElement("a",{className:"pointer",style:{textDecoration:"underline"},onClick:function(){return e.props.history.push("/profile/"+t.user_unique_id)}},t.user_name)),d.a.createElement("td",null,t.name),d.a.createElement("td",null,t.phone),d.a.createElement("td",null,d.a.createElement("a",{className:"pointer",style:{textDecoration:"underline"},onClick:function(){return e.props.history.push("/profile/"+t.user_unique_id)}},t.email)),d.a.createElement("td",null,t.txn_id),d.a.createElement("td",null,t.payment_request),d.a.createElement("td",null,1==t.payment_gateway_id?"PayUMoney":2==t.payment_gateway_id?"PayTM":4==t.payment_gateway_id?"GoCash":5==t.payment_gateway_id?"M-Pesa":6==t.payment_gateway_id?"Paypal":8==t.payment_gateway_id?"RazorPay":""),d.a.createElement("td",null,_.a.getUtcToLocalFormat(t.order_date_added,"D-MMM-YYYY hh:mm A")),d.a.createElement("td",null,_.a.getUtcToLocalFormat(t.member_since,"D-MMM-YYYY")),d.a.createElement("td",null,0==t.status?d.a.createElement("i",{className:"icon-verified",title:"Not yet"}):1==t.status?d.a.createElement("i",{className:"icon-verified text-green",title:"Payment Processed Done"}):d.a.createElement("i",{className:"icon-inactive text-red",title:8==t.source?"Rejected":"Failed"}))))})):d.a.createElement("tbody",null,d.a.createElement("tr",null,d.a.createElement("td",{colSpan:"22"},0!=a.length||E?d.a.createElement(k.a,null):d.a.createElement("div",{className:"no-records"},"No Record Found."))))))),l>s&&d.a.createElement("div",{className:"custom-pagination lobby-paging"},d.a.createElement(D.a,{activePage:r,itemsCountPerPage:s,totalItemsCount:l,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})))))}}]),a}(c.Component)}}]);
//# sourceMappingURL=108.3c526ec6.chunk.js.map