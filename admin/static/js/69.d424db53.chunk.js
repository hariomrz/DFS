(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[69,218],{507:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==c(e)&&"function"!==typeof e)return{default:e};var t=l();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var i=n?Object.getOwnPropertyDescriptor(e,r):null;i&&(i.get||i.set)?Object.defineProperty(a,r,i):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=u(a(1)),i=u(a(509)),o=u(a(510)),s=u(a(3));function u(e){return e&&e.__esModule?e:{default:e}}function l(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return l=function(){return e},e}function c(e){return(c="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function d(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function f(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function h(e,t){return!t||"object"!==c(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function p(e){return(p=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function b(e,t){return(b=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function m(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var g=function(e){function t(){return d(this,t),h(this,p(t).apply(this,arguments))}var a,r,u;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&b(e,t)}(t,e),a=t,(r=[{key:"isFirstPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"isPrevPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isNextPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return!(t.hideNavigation||a&&!e)}},{key:"isLastPageVisible",value:function(e){var t=this.props,a=t.hideDisabled;return t.hideNavigation,!(t.hideFirstLastPages||a&&!e)}},{key:"buildPages",value:function(){for(var e=[],t=this.props,a=t.itemsCountPerPage,r=t.pageRangeDisplayed,u=t.activePage,l=t.prevPageText,c=t.nextPageText,d=t.firstPageText,f=t.lastPageText,h=t.totalItemsCount,p=t.onChange,b=t.activeClass,m=t.itemClass,g=t.itemClassFirst,y=t.itemClassPrev,v=t.itemClassNext,w=t.itemClassLast,P=t.activeLinkClass,C=t.disabledClass,k=(t.hideDisabled,t.hideNavigation,t.linkClass),_=t.linkClassFirst,E=t.linkClassPrev,T=t.linkClassNext,O=t.linkClassLast,x=(t.hideFirstLastPages,t.getPageUrl),N=new i.default(a,r).build(h,u),M=N.first_page;M<=N.last_page;M++)e.push(n.default.createElement(o.default,{isActive:M===u,key:M,href:x(M),pageNumber:M,pageText:M+"",onClick:p,itemClass:m,linkClass:k,activeClass:b,activeLinkClass:P,ariaLabel:"Go to page number ".concat(M)}));return this.isPrevPageVisible(N.has_previous_page)&&e.unshift(n.default.createElement(o.default,{key:"prev"+N.previous_page,href:x(N.previous_page),pageNumber:N.previous_page,onClick:p,pageText:l,isDisabled:!N.has_previous_page,itemClass:(0,s.default)(m,y),linkClass:(0,s.default)(k,E),disabledClass:C,ariaLabel:"Go to previous page"})),this.isFirstPageVisible(N.has_previous_page)&&e.unshift(n.default.createElement(o.default,{key:"first",href:x(1),pageNumber:1,onClick:p,pageText:d,isDisabled:!N.has_previous_page,itemClass:(0,s.default)(m,g),linkClass:(0,s.default)(k,_),disabledClass:C,ariaLabel:"Go to first page"})),this.isNextPageVisible(N.has_next_page)&&e.push(n.default.createElement(o.default,{key:"next"+N.next_page,href:x(N.next_page),pageNumber:N.next_page,onClick:p,pageText:c,isDisabled:!N.has_next_page,itemClass:(0,s.default)(m,v),linkClass:(0,s.default)(k,T),disabledClass:C,ariaLabel:"Go to next page"})),this.isLastPageVisible(N.has_next_page)&&e.push(n.default.createElement(o.default,{key:"last",href:x(N.total_pages),pageNumber:N.total_pages,onClick:p,pageText:f,isDisabled:N.current_page===N.total_pages,itemClass:(0,s.default)(m,w),linkClass:(0,s.default)(k,O),disabledClass:C,ariaLabel:"Go to last page"})),e}},{key:"render",value:function(){var e=this.buildPages();return n.default.createElement("ul",{className:this.props.innerClass},e)}}])&&f(a.prototype,r),u&&f(a,u),t}(n.default.Component);t.default=g,m(g,"propTypes",{totalItemsCount:r.default.number.isRequired,onChange:r.default.func.isRequired,activePage:r.default.number,itemsCountPerPage:r.default.number,pageRangeDisplayed:r.default.number,prevPageText:r.default.oneOfType([r.default.string,r.default.element]),nextPageText:r.default.oneOfType([r.default.string,r.default.element]),lastPageText:r.default.oneOfType([r.default.string,r.default.element]),firstPageText:r.default.oneOfType([r.default.string,r.default.element]),disabledClass:r.default.string,hideDisabled:r.default.bool,hideNavigation:r.default.bool,innerClass:r.default.string,itemClass:r.default.string,itemClassFirst:r.default.string,itemClassPrev:r.default.string,itemClassNext:r.default.string,itemClassLast:r.default.string,linkClass:r.default.string,activeClass:r.default.string,activeLinkClass:r.default.string,linkClassFirst:r.default.string,linkClassPrev:r.default.string,linkClassNext:r.default.string,linkClassLast:r.default.string,hideFirstLastPages:r.default.bool,getPageUrl:r.default.func}),m(g,"defaultProps",{itemsCountPerPage:10,pageRangeDisplayed:5,activePage:1,prevPageText:"\u27e8",firstPageText:"\xab",nextPageText:"\u27e9",lastPageText:"\xbb",innerClass:"pagination",itemClass:void 0,linkClass:void 0,activeLinkClass:void 0,hideFirstLastPages:!1,getPageUrl:function(e){return"#"}})},509:function(e,t){function a(e,t){if(!(this instanceof a))return new a(e,t);this.per_page=e||25,this.length=t||10}e.exports=a,a.prototype.build=function(e,t){var a=Math.ceil(e/this.per_page);e=parseInt(e,10),(t=parseInt(t,10)||1)<1&&(t=1),t>a&&(t=a);var n=Math.max(1,t-Math.floor(this.length/2)),r=Math.min(a,t+Math.floor(this.length/2));r-n+1<this.length&&(t<a/2?r=Math.min(a,r+(this.length-(r-n))):n=Math.max(1,n-(this.length-(r-n)))),r-n+1>this.length&&(t>a/2?n++:r--);var i=this.per_page*(t-1);i<0&&(i=0);var o=this.per_page*t-1;return o<0&&(o=0),o>Math.max(e-1,0)&&(o=Math.max(e-1,0)),{total_pages:a,pages:Math.min(r-n+1,a),current_page:t,first_page:n,last_page:r,previous_page:t-1,next_page:t+1,has_previous_page:t>1,has_next_page:t<a,total_results:e,results:Math.min(o-i+1,e),first_result:i,last_result:o}}},510:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var n=function(e){if(e&&e.__esModule)return e;if(null===e||"object"!==u(e)&&"function"!==typeof e)return{default:e};var t=s();if(t&&t.has(e))return t.get(e);var a={},n=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var r in e)if(Object.prototype.hasOwnProperty.call(e,r)){var i=n?Object.getOwnPropertyDescriptor(e,r):null;i&&(i.get||i.set)?Object.defineProperty(a,r,i):a[r]=e[r]}a.default=e,t&&t.set(e,a);return a}(a(0)),r=o(a(1)),i=o(a(3));function o(e){return e&&e.__esModule?e:{default:e}}function s(){if("function"!==typeof WeakMap)return null;var e=new WeakMap;return s=function(){return e},e}function u(e){return(u="function"===typeof Symbol&&"symbol"===typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"===typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function l(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function c(e,t){for(var a=0;a<t.length;a++){var n=t[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function d(e,t){return!t||"object"!==u(t)&&"function"!==typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function f(e){return(f=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function h(e,t){return(h=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}function p(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var b=function(e){function t(){return l(this,t),d(this,f(t).apply(this,arguments))}var a,r,o;return function(e,t){if("function"!==typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&h(e,t)}(t,e),a=t,(r=[{key:"handleClick",value:function(e){var t=this.props,a=t.isDisabled,n=t.pageNumber;e.preventDefault(),a||this.props.onClick(n)}},{key:"render",value:function(){var e,t=this.props,a=t.pageText,r=(t.pageNumber,t.activeClass),o=t.itemClass,s=t.linkClass,u=t.activeLinkClass,l=t.disabledClass,c=t.isActive,d=t.isDisabled,f=t.href,h=t.ariaLabel,b=(0,i.default)(o,(p(e={},r,c),p(e,l,d),e)),m=(0,i.default)(s,p({},u,c));return n.default.createElement("li",{className:b,onClick:this.handleClick.bind(this)},n.default.createElement("a",{className:m,href:f,"aria-label":h},a))}}])&&c(a.prototype,r),o&&c(a,o),t}(n.Component);t.default=b,p(b,"propTypes",{pageText:r.default.oneOfType([r.default.string,r.default.element]),pageNumber:r.default.number.isRequired,onClick:r.default.func.isRequired,isActive:r.default.bool.isRequired,isDisabled:r.default.bool,activeClass:r.default.string,activeLinkClass:r.default.string,itemClass:r.default.string,linkClass:r.default.string,disabledClass:r.default.string,href:r.default.string}),p(b,"defaultProps",{activeClass:"active",disabledClass:"disabled",itemClass:void 0,linkClass:void 0,activeLinkCLass:void 0,isActive:!1,isDisabled:!1,href:"#"})},511:function(e,t,a){"use strict";a.d(t,"Ob",(function(){return n})),a.d(t,"cb",(function(){return r})),a.d(t,"bb",(function(){return i})),a.d(t,"ob",(function(){return o})),a.d(t,"C",(function(){return s})),a.d(t,"d",(function(){return u})),a.d(t,"fb",(function(){return l})),a.d(t,"E",(function(){return c})),a.d(t,"G",(function(){return d})),a.d(t,"F",(function(){return f})),a.d(t,"Q",(function(){return h})),a.d(t,"S",(function(){return p})),a.d(t,"Y",(function(){return b})),a.d(t,"Z",(function(){return m})),a.d(t,"nb",(function(){return g})),a.d(t,"N",(function(){return y})),a.d(t,"O",(function(){return v})),a.d(t,"Kb",(function(){return w})),a.d(t,"Vb",(function(){return P})),a.d(t,"Ub",(function(){return C})),a.d(t,"Tb",(function(){return k})),a.d(t,"Bb",(function(){return _})),a.d(t,"z",(function(){return E})),a.d(t,"P",(function(){return T})),a.d(t,"Pb",(function(){return O})),a.d(t,"W",(function(){return x})),a.d(t,"f",(function(){return N})),a.d(t,"e",(function(){return M})),a.d(t,"w",(function(){return j})),a.d(t,"x",(function(){return A})),a.d(t,"eb",(function(){return D})),a.d(t,"Nb",(function(){return L})),a.d(t,"U",(function(){return F})),a.d(t,"mb",(function(){return R})),a.d(t,"jb",(function(){return S})),a.d(t,"kb",(function(){return I})),a.d(t,"gb",(function(){return z})),a.d(t,"hb",(function(){return G})),a.d(t,"lb",(function(){return U})),a.d(t,"ib",(function(){return q})),a.d(t,"Lb",(function(){return V})),a.d(t,"L",(function(){return W})),a.d(t,"K",(function(){return B})),a.d(t,"T",(function(){return Y})),a.d(t,"H",(function(){return H})),a.d(t,"I",(function(){return J})),a.d(t,"D",(function(){return Z})),a.d(t,"db",(function(){return K})),a.d(t,"a",(function(){return Q})),a.d(t,"c",(function(){return X})),a.d(t,"b",(function(){return $})),a.d(t,"R",(function(){return ee})),a.d(t,"Qb",(function(){return te})),a.d(t,"q",(function(){return ae})),a.d(t,"h",(function(){return ne})),a.d(t,"i",(function(){return re})),a.d(t,"yb",(function(){return ie})),a.d(t,"ub",(function(){return oe})),a.d(t,"sb",(function(){return se})),a.d(t,"xb",(function(){return ue})),a.d(t,"tb",(function(){return le})),a.d(t,"wb",(function(){return ce})),a.d(t,"vb",(function(){return de})),a.d(t,"rb",(function(){return fe})),a.d(t,"qb",(function(){return he})),a.d(t,"Ab",(function(){return pe})),a.d(t,"zb",(function(){return be})),a.d(t,"J",(function(){return me})),a.d(t,"pb",(function(){return ge})),a.d(t,"V",(function(){return ye})),a.d(t,"X",(function(){return ve})),a.d(t,"r",(function(){return we})),a.d(t,"u",(function(){return Pe})),a.d(t,"v",(function(){return Ce})),a.d(t,"t",(function(){return ke})),a.d(t,"s",(function(){return _e})),a.d(t,"Cb",(function(){return Ee})),a.d(t,"Mb",(function(){return Te})),a.d(t,"Gb",(function(){return Oe})),a.d(t,"y",(function(){return xe})),a.d(t,"A",(function(){return Ne})),a.d(t,"B",(function(){return Me})),a.d(t,"ac",(function(){return je})),a.d(t,"bc",(function(){return Ae})),a.d(t,"dc",(function(){return De})),a.d(t,"Wb",(function(){return Le})),a.d(t,"ec",(function(){return Fe})),a.d(t,"Yb",(function(){return Re})),a.d(t,"Xb",(function(){return Se})),a.d(t,"Zb",(function(){return Ie})),a.d(t,"fc",(function(){return ze})),a.d(t,"gc",(function(){return Ge})),a.d(t,"cc",(function(){return Ue})),a.d(t,"Ib",(function(){return qe})),a.d(t,"Hb",(function(){return Ve})),a.d(t,"Jb",(function(){return We})),a.d(t,"M",(function(){return Be})),a.d(t,"Eb",(function(){return Ye})),a.d(t,"Fb",(function(){return He})),a.d(t,"Db",(function(){return Je})),a.d(t,"Rb",(function(){return Ze})),a.d(t,"Sb",(function(){return Ke})),a.d(t,"ab",(function(){return Qe})),a.d(t,"m",(function(){return Xe})),a.d(t,"p",(function(){return $e})),a.d(t,"k",(function(){return et})),a.d(t,"o",(function(){return tt})),a.d(t,"n",(function(){return at})),a.d(t,"l",(function(){return nt})),a.d(t,"j",(function(){return rt})),a.d(t,"g",(function(){return it}));var n="System generated an error please try again later.",r="No Records Found.",i="No participants till now",o="Please enter min 3 and max 100 alphanumeric character for promocode",s="Discount to be given to user on entry fee",u="Maximum amount value of the discount",l="This will define that how many times single user can use the same promo code",c="League name should be between 3 to 30 character.",d="Player name should be between 3 to 30 character.",f="Player abbr should be between 2 to 7 character.",h="Are you sure you want to delete this category?",p="Are you sure you want to delete this prediction?",b="Are you sure you want to submit this answer?",m="( you cannot undo this action )",g="Prediction past date time is not allowed",y="Are you sure you want to active this distributor?",v="Are you sure you want to block this distributor?",w="Please enter valid source",P="Please enter valid question.",C="Please enter alphanumeric option.",k="Please enter valid description.",_="Word limit 3 to 200",E="Word limit 3 to 140",T="Are you sure you want to cancel the match?",O="Publish Game",x="Are you sure you want to publish this game?",N="Manage Cancel Game/Fixture",M="Manage Cancel Contest",j="Manage Message",A="Manage Delay Schedule",D="Password should be between 6 to 30",L="System user name should be alphanumeric only (_, @, .) special character allowed, white space not allowed",F="Are you sure you want to delete this user?",R="Team(A) and Team (B) can't same.",S="Enter correct match link.",I="Are you sure you want to publish this fixture?",z="Are you sure you want to declare this result?",G="Are you sure you want to delete this pick?",U="(you cannot undo this action)",q="Value should be between 10 to 1000",V="Sport Preferences (Fixture participated)",W="Please enter min and max value in number ",B="Max value should be geater than min value ",Y="Are you sure you want to delete this userbase?",H="First name should be between 3 to 30 character",J="Last name should be between 3 to 30 character",Z="Please enter valid email address",K="Password should be between 6 to 30 character",Q="Please select atleast 1 role",X="Are you sure you want to block this affiliate?",$="Are you sure you want to active this affiliate?",ee="Are you sure you want to delete this group?",te="Are you sure you want to upload csv?",ae="System users already existed.",ne="Super! now you have the power to activate/ deactivate various coins earning widget for users which will be showcased in your app. User will earn coins through it.",re="Get all the user engaged with your fantasy platform by activating coins module",ie="Sport name should be between 3 to 15 character.",oe="Please select sport priority.",se="Are you sure you want to delete this sport?",ue="This Banner will be shown inside the tournament in carousel banner",le="This will be shown on frontend of the tournament card",ce="Allow to create prize distribution for tournament",de="On checking the pick'em will be Published",fe="Match start date & time should be greater than make picks date & time",he="Make picks date & time should be greater than current date & time",pe="Tournament end date & time should be greater than tournament start date & time",be="Tournament start date & time should be greater than current date & time",me="Match start date & time should be greater than current date & time",ge="Are you sure you want to cancel this tournament?",ye="Are you sure you want to delete this transaction?",ve="System Generated value updates in every 24 hours",we='Those users who have played any contest in the last 7 days. The Percentage chart tells about how much increase / Decrease in the no.of signups than the day entered in the master file.For Example, If in the master filter, "From" and "To" shows 1st May 2019 to 5th May 2019, percentage would show how much increase or decrease the no.is from 26th April 2019 to 30th April 2019.',Pe='Users who have played more than 10 contests in the last month, but have not played any contest this month.The Percentage chart tells about how much increase / Decrease in the no.of signups than the day entered in the master file.For Example, If in the master filter, "From" and "To" shows 1st May 2019 to 5th May 2019,percentage would show how much increase or decrease the no.is from 26th April 2019 to 30th April 2019.',Ce='This shows the no. of visitors who signed up on the website. The percentage chart tells about how much increase/decrease in the no. of signups than the days entered in the master filter. For example, if in master filter, "from" and "to" shows 1 May 2019 to 5 May 2019, then percentage would show how much increase or decrease the no. is from 26 April 2019 to 30 April 2019.',ke='Total deposit and depositors count as per the selected date range should be shown.The Percentage chart tells about how much increase / Decrease in the no.of signups than the day entered in the master file.For Example, If in the master filter, "From" and "To" shows 1st May 2019 to 5th May 2019,percentage would show how much increase or decrease the no.is from 26th April 2019 to 30th April 2019.',_e="The first chart shows the segregation between Mobile and Desktop users.The second chart shows the segregation between the web browser used.",Ee="Reverse fantasy is just opposite of traditional fantasy, team with lowest fantasy point will win. Lower the fantasy points higher the ranking in the leaderboard.",Te="Are you sure you want to highlight this match?",Oe="Are you sure you want to remove this match to highlight?",xe="Are you sure you want to delete this match?",Ne="Max amount capping allowed is 100B",Me="Are you sure you want to cancel tournament?",je="Are you sure you want to delete this?",Ae="You cannot undo this action",De="Please select level",Le="Please select badge",Fe="Please select coins, cashback or joining cashback to add reward",Re="Please enter coins points",Se="Please enter cashback details",Ie="Please enter contest joining discount details",ze="Please select activity",Ge="Please select count",Ue="Please enter earn points",qe="Check this to allow Scratch and win feature in this contest",Ve="Scratch and win will allow contest user to see scratch card",We="Scratch and win will allow contest user to see scratch card. Tap to active inactive for this contest",Be="Module is not enabled please contact to support team.",Ye="Are you sure you want to delete this reward?",He="(you cannot undo this action)",Je="Amount should be number between 0 to 7 in length",Ze="You cannot revert the origional scores after this process.",Ke="Are you sure, you want to update the scores?",Qe="Value should be between 1 to 100",Xe="Customise how you want to give prizes to contest winners.In the case of Percentage, the prize pool will be increased according to each new team entry.By selecting Fixed value you are defining exact prize for each rank and it will be treated as a guaranteed contest.Prizes will not increase according to entries.",$e="Select this if you want to give merchandise in pricing. Also inn case of a tie in user ranks, winner will be the one who created team first.",et="This will be shown on contest card (If you leave it blank, max contest prize will be visible)",tt="Here you can upload contest/league sponsor image and link. It will be visible on contest info screen and fixture card. Priority is given to league/HOF sponsor.",at="Once a contest gets fully filled, it will create a fresh new contest with 0 participants and the same parameters",nt="Pinning the contest will make it on the first in the listing",rt="% of entry fees of user which can be bonus money",it="Schedule date & time should be greater than current date & time"},516:function(e,t,a){"use strict";var n=a(6),r=a(7),i=a(0),o=a.n(i),s=a(1),u=a.n(s),l=a(3),c=a.n(l),d=a(4),f={className:u.a.string,cssModule:u.a.object,size:u.a.string,bordered:u.a.bool,borderless:u.a.bool,striped:u.a.bool,dark:u.a.bool,hover:u.a.bool,responsive:u.a.oneOfType([u.a.bool,u.a.string]),tag:d.q,responsiveTag:d.q,innerRef:u.a.oneOfType([u.a.func,u.a.string,u.a.object])},h=function(e){var t=e.className,a=e.cssModule,i=e.size,s=e.bordered,u=e.borderless,l=e.striped,f=e.dark,h=e.hover,p=e.responsive,b=e.tag,m=e.responsiveTag,g=e.innerRef,y=Object(r.a)(e,["className","cssModule","size","bordered","borderless","striped","dark","hover","responsive","tag","responsiveTag","innerRef"]),v=Object(d.m)(c()(t,"table",!!i&&"table-"+i,!!s&&"table-bordered",!!u&&"table-borderless",!!l&&"table-striped",!!f&&"table-dark",!!h&&"table-hover"),a),w=o.a.createElement(b,Object(n.a)({},y,{ref:g,className:v}));if(p){var P=Object(d.m)(!0===p?"table-responsive":"table-responsive-"+p,a);return o.a.createElement(m,{className:P},w)}return w};h.propTypes=f,h.defaultProps={tag:"table",responsiveTag:"div"},t.a=h},518:function(e,t,a){"use strict";a.d(t,"a",(function(){return r}));var n=a(0),r=a.n(n).a.createContext({})},553:function(e,t,a){"use strict";var n=a(6),r=a(22),i=a(0),o=a.n(i),s=a(1),u=a.n(s),l=a(3),c=a.n(l),d=a(518),f=a(4),h={tag:f.q,activeTab:u.a.any,className:u.a.string,cssModule:u.a.object},p=function(e){function t(t){var a;return(a=e.call(this,t)||this).state={activeTab:a.props.activeTab},a}return Object(r.a)(t,e),t.getDerivedStateFromProps=function(e,t){return t.activeTab!==e.activeTab?{activeTab:e.activeTab}:null},t.prototype.render=function(){var e=this.props,t=e.className,a=e.cssModule,r=e.tag,i=Object(f.n)(this.props,Object.keys(h)),s=Object(f.m)(c()("tab-content",t),a);return o.a.createElement(d.a.Provider,{value:{activeTabId:this.state.activeTab}},o.a.createElement(r,Object(n.a)({},i,{className:s})))},t}(i.Component);t.a=p,p.propTypes=h,p.defaultProps={tag:"div"}},554:function(e,t,a){"use strict";a.d(t,"a",(function(){return p}));var n=a(6),r=a(7),i=a(0),o=a.n(i),s=a(1),u=a.n(s),l=a(3),c=a.n(l),d=a(518),f=a(4),h={tag:f.q,className:u.a.string,cssModule:u.a.object,tabId:u.a.any};function p(e){var t=e.className,a=e.cssModule,i=e.tabId,s=e.tag,u=Object(r.a)(e,["className","cssModule","tabId","tag"]),l=function(e){return Object(f.m)(c()("tab-pane",t,{active:i===e}),a)};return o.a.createElement(d.a.Consumer,null,(function(e){var t=e.activeTabId;return o.a.createElement(s,Object(n.a)({},u,{className:l(t)}))}))}p.propTypes=h,p.defaultProps={tag:"div"}},942:function(e,t,a){"use strict";a.r(t);var n=a(11),r=a(12),i=a(15),o=a(14),s=a(0),u=a.n(s),l=a(155),c=a(156),d=a(516),f=a(503),h=a(501),p=a(502),b=a(553),m=a(554),g=a(507),y=a.n(g),v=a(8),w=a(5),P=a.n(w),C=a(107),k=a(9),_=a(18),E=a(46),T=a(10),O=a(511),x=function(e){Object(i.a)(a,e);var t=Object(o.a)(a);function a(e){var r;return Object(n.a)(this,a),(r=t.call(this,e)).handleFilter=function(e){e&&r.setState({Filter:e.value},r.getOpenPredictorLeaderboard)},r.getLeaderboardMasterData=function(){k.a.Rest(v.nk+v.Ze,{}).then((function(e){if(e.response_code==v.qk){var t=e.data;Object.keys(t).map((function(e){P.a.map(t[e],(function(e){e.value=e.from_date}))})),r.setState({FilterList:t})}else _.notify.show(v.Ri,"error",5e3)})).catch((function(e){_.notify.show(v.Ri,"error",5e3)}))},r.getOpenPredictorLeaderboard=function(){var e=r.state,t=e.activeTab,a=e.Filter,n=e.CURRENT_PAGE,i=e.PERPAGE,o="";"1"==t&&0==a?o="today":"2"==t&&0==a?o="this_week":"3"==t&&0==a?o="this_month":"1"!=t||P.a.isEmpty(a)?"2"!=t||P.a.isEmpty(a)?"3"!=t||P.a.isEmpty(a)||(o="month_date"):o="week_date":o="day_date";var s={filter:o,filter_date:a,current_page:n,items_perpage:i};k.a.Rest(v.nk+v.Ye,s).then((function(e){e.response_code==v.qk?r.setState({UsersList:e.data.result,Total:e.data.total}):_.notify.show(v.Ri,"error",5e3)})).catch((function(e){_.notify.show(v.Ri,"error",5e3)}))},r.renderUserData=function(){var e=r.state,t=e.UsersList,a=e.PERPAGE,n=e.CURRENT_PAGE,i=e.Total;return u.a.createElement(s.Fragment,null,u.a.createElement(l.a,null,u.a.createElement(c.a,{md:12},u.a.createElement("div",{className:"table-responsive common-table op-leaderboard"},u.a.createElement("div",{className:"tbl-min-hgt"},u.a.createElement(d.a,null,u.a.createElement("thead",null,u.a.createElement("tr",null,u.a.createElement("th",{className:"pl-4"},"Rank"),u.a.createElement("th",null,"Username"),u.a.createElement("th",null,"Prize"),u.a.createElement("th",null,"Referral"))),i>0?P.a.map(t,(function(e,t){return u.a.createElement("tbody",{key:t},u.a.createElement("tr",null,u.a.createElement("td",{className:"pl-4"},e.rank_value),u.a.createElement("td",null,e.user_name),u.a.createElement("td",null,null!=e.prize_data?0==e.prize_data[0].prize_type?u.a.createElement("i",{className:"icon-bonus1 mr-1"}):1==e.prize_data[0].prize_type?u.a.createElement("i",{className:"icon-rupess mr-1"}):2==e.prize_data[0].prize_type?u.a.createElement("img",{className:"mr-1",src:E.a.REWARD_ICON,alt:""}):"":"",null!=e.prize_data?3==e.prize_data[0].prize_type?e.prize_data[0].name:e.prize_data[0].amount:"--"),u.a.createElement("td",{className:"pl-4 font-weight-bold"},e.total_referral?e.total_referral:0)))})):u.a.createElement("tbody",null,u.a.createElement("tr",null,u.a.createElement("td",{colSpan:"8"},u.a.createElement("div",{className:"no-records"},v.Cg)))))),i>a&&u.a.createElement("div",{className:"custom-pagination float-right mb-5"},u.a.createElement(y.a,{activePage:n,itemsCountPerPage:a,totalItemsCount:i,pageRangeDisplayed:5,onChange:function(e){return r.handlePageChange(e)}}))))))},r.state={activeTab:"1",CURRENT_PAGE:1,PERPAGE:v.Vf,Filter:0,Total:0,FilterList:[]},r}return Object(r.a)(a,[{key:"componentDidMount",value:function(){"1"!=T.i.allowRefLeaderboard()&&(_.notify.show(O.M,"error",5e3),this.props.history.push("/dashboard")),this.getLeaderboardMasterData(),this.getOpenPredictorLeaderboard()}},{key:"toggle",value:function(e){this.setState({activeTab:e,Filter:0},this.getOpenPredictorLeaderboard)}},{key:"handlePageChange",value:function(e){var t=this;this.setState({CURRENT_PAGE:e},(function(){t.getOpenPredictorLeaderboard()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.Filter,n=t.activeTab,r=(t.FilterData,t.FilterList);return u.a.createElement(s.Fragment,null,u.a.createElement(l.a,null,u.a.createElement(c.a,{md:12},u.a.createElement("div",{className:"user-navigation"},u.a.createElement(l.a,null,u.a.createElement(c.a,{md:12},u.a.createElement(f.a,{tabs:!0},u.a.createElement(h.a,{className:"1"===n?"active":"",onClick:function(){e.toggle("1")}},u.a.createElement(p.a,null,"Today")),u.a.createElement(h.a,{className:"2"===n?"active":"",onClick:function(){e.toggle("2")}},u.a.createElement(p.a,null,"This Week")),u.a.createElement(h.a,{className:"3"===n?"active":"",onClick:function(){e.toggle("3")}},u.a.createElement(p.a,null,"This Month"))))),u.a.createElement(l.a,null,u.a.createElement(c.a,{md:3},u.a.createElement("div",{className:"select-week"},u.a.createElement("label",{htmlFor:"selectweek"},"Select ","1"==n?"Day":"2"==n?"Week":"Month"),u.a.createElement(C.a,{searchable:!1,clearable:!1,value:a,options:r["1"==n?"day_filter":"2"==n?"week_filter":"month_filter"],onChange:function(t){return e.handleFilter(t)}})))),u.a.createElement(b.a,{activeTab:n,className:"bg-white"},"1"==n&&u.a.createElement(m.a,{tabId:"1",className:"animated fadeIn"},this.renderUserData("Day")),"2"==n&&u.a.createElement(m.a,{tabId:"2",className:"animated fadeIn"},this.renderUserData("Week")),"3"==n&&u.a.createElement(m.a,{tabId:"3",className:"animated fadeIn"},this.renderUserData("Month")))))))}}]),a}(s.Component);t.default=x}}]);
//# sourceMappingURL=69.d424db53.chunk.js.map