(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[96,218],{508:function(e,t,a){"use strict";var n=a(6),s=a(7),o=a(81),r=a(0),i=a.n(r),l=a(1),c=a.n(l),d=a(3),m=a.n(d),u=a(519),p=a(4),h=Object(o.a)({},u.Transition.propTypes,{children:c.a.oneOfType([c.a.arrayOf(c.a.node),c.a.node]),tag:p.q,baseClass:c.a.string,baseClassActive:c.a.string,className:c.a.string,cssModule:c.a.object,innerRef:c.a.oneOfType([c.a.object,c.a.string,c.a.func])}),b=Object(o.a)({},u.Transition.defaultProps,{tag:"div",baseClass:"fade",baseClassActive:"show",timeout:p.e.Fade,appear:!0,enter:!0,exit:!0,in:!0});function g(e){var t=e.tag,a=e.baseClass,o=e.baseClassActive,r=e.className,l=e.cssModule,c=e.children,d=e.innerRef,h=Object(s.a)(e,["tag","baseClass","baseClassActive","className","cssModule","children","innerRef"]),b=Object(p.o)(h,p.c),g=Object(p.n)(h,p.c);return i.a.createElement(u.Transition,b,(function(e){var s="entered"===e,u=Object(p.m)(m()(r,a,s&&o),l);return i.a.createElement(t,Object(n.a)({className:u},g,{ref:d}),c)}))}g.propTypes=h,g.defaultProps=b,t.a=g},516:function(e,t,a){"use strict";var n=a(6),s=a(7),o=a(0),r=a.n(o),i=a(1),l=a.n(i),c=a(3),d=a.n(c),m=a(4),u={className:l.a.string,cssModule:l.a.object,size:l.a.string,bordered:l.a.bool,borderless:l.a.bool,striped:l.a.bool,dark:l.a.bool,hover:l.a.bool,responsive:l.a.oneOfType([l.a.bool,l.a.string]),tag:m.q,responsiveTag:m.q,innerRef:l.a.oneOfType([l.a.func,l.a.string,l.a.object])},p=function(e){var t=e.className,a=e.cssModule,o=e.size,i=e.bordered,l=e.borderless,c=e.striped,u=e.dark,p=e.hover,h=e.responsive,b=e.tag,g=e.responsiveTag,f=e.innerRef,E=Object(s.a)(e,["className","cssModule","size","bordered","borderless","striped","dark","hover","responsive","tag","responsiveTag","innerRef"]),v=Object(m.m)(d()(t,"table",!!o&&"table-"+o,!!i&&"table-bordered",!!l&&"table-borderless",!!c&&"table-striped",!!u&&"table-dark",!!p&&"table-hover"),a),y=r.a.createElement(b,Object(n.a)({},E,{ref:f,className:v}));if(h){var N=Object(m.m)(!0===h?"table-responsive":"table-responsive-"+h,a);return r.a.createElement(g,{className:N},y)}return y};p.propTypes=u,p.defaultProps={tag:"table",responsiveTag:"div"},t.a=p},538:function(e,t,a){"use strict";var n=a(6),s=a(7),o=a(0),r=a.n(o),i=a(1),l=a.n(i),c=a(3),d=a.n(c),m=a(4),u={tag:m.q,className:l.a.string,cssModule:l.a.object},p=function(e){var t=e.className,a=e.cssModule,o=e.tag,i=Object(s.a)(e,["className","cssModule","tag"]),l=Object(m.m)(d()(t,"modal-body"),a);return r.a.createElement(o,Object(n.a)({},i,{className:l}))};p.propTypes=u,p.defaultProps={tag:"div"},t.a=p},539:function(e,t,a){"use strict";var n=a(81),s=a(6),o=a(16),r=a(22),i=a(0),l=a.n(i),c=a(1),d=a.n(c),m=a(3),u=a.n(m),p=a(32),h=a.n(p),b=a(4),g={children:d.a.node.isRequired,node:d.a.any},f=function(e){function t(){return e.apply(this,arguments)||this}Object(r.a)(t,e);var a=t.prototype;return a.componentWillUnmount=function(){this.defaultNode&&document.body.removeChild(this.defaultNode),this.defaultNode=null},a.render=function(){return b.f?(this.props.node||this.defaultNode||(this.defaultNode=document.createElement("div"),document.body.appendChild(this.defaultNode)),h.a.createPortal(this.props.children,this.props.node||this.defaultNode)):null},t}(l.a.Component);f.propTypes=g;var E=f,v=a(508);function y(){}var N=d.a.shape(v.a.propTypes),_={isOpen:d.a.bool,autoFocus:d.a.bool,centered:d.a.bool,scrollable:d.a.bool,size:d.a.string,toggle:d.a.func,keyboard:d.a.bool,role:d.a.string,labelledBy:d.a.string,backdrop:d.a.oneOfType([d.a.bool,d.a.oneOf(["static"])]),onEnter:d.a.func,onExit:d.a.func,onOpened:d.a.func,onClosed:d.a.func,children:d.a.node,className:d.a.string,wrapClassName:d.a.string,modalClassName:d.a.string,backdropClassName:d.a.string,contentClassName:d.a.string,external:d.a.node,fade:d.a.bool,cssModule:d.a.object,zIndex:d.a.oneOfType([d.a.number,d.a.string]),backdropTransition:N,modalTransition:N,innerRef:d.a.oneOfType([d.a.object,d.a.string,d.a.func]),unmountOnClose:d.a.bool,returnFocusAfterClose:d.a.bool,container:b.r},O=Object.keys(_),C={isOpen:!1,autoFocus:!0,centered:!1,scrollable:!1,role:"dialog",backdrop:!0,keyboard:!0,zIndex:1050,fade:!0,onOpened:y,onClosed:y,modalTransition:{timeout:b.e.Modal},backdropTransition:{mountOnEnter:!0,timeout:b.e.Fade},unmountOnClose:!0,returnFocusAfterClose:!0,container:"body"},T=function(e){function t(t){var a;return(a=e.call(this,t)||this)._element=null,a._originalBodyPadding=null,a.getFocusableChildren=a.getFocusableChildren.bind(Object(o.a)(a)),a.handleBackdropClick=a.handleBackdropClick.bind(Object(o.a)(a)),a.handleBackdropMouseDown=a.handleBackdropMouseDown.bind(Object(o.a)(a)),a.handleEscape=a.handleEscape.bind(Object(o.a)(a)),a.handleStaticBackdropAnimation=a.handleStaticBackdropAnimation.bind(Object(o.a)(a)),a.handleTab=a.handleTab.bind(Object(o.a)(a)),a.onOpened=a.onOpened.bind(Object(o.a)(a)),a.onClosed=a.onClosed.bind(Object(o.a)(a)),a.manageFocusAfterClose=a.manageFocusAfterClose.bind(Object(o.a)(a)),a.clearBackdropAnimationTimeout=a.clearBackdropAnimationTimeout.bind(Object(o.a)(a)),a.state={isOpen:!1,showStaticBackdropAnimation:!1},a}Object(r.a)(t,e);var a=t.prototype;return a.componentDidMount=function(){var e=this.props,t=e.isOpen,a=e.autoFocus,n=e.onEnter;t&&(this.init(),this.setState({isOpen:!0}),a&&this.setFocus()),n&&n(),this._isMounted=!0},a.componentDidUpdate=function(e,t){if(this.props.isOpen&&!e.isOpen)return this.init(),void this.setState({isOpen:!0});this.props.autoFocus&&this.state.isOpen&&!t.isOpen&&this.setFocus(),this._element&&e.zIndex!==this.props.zIndex&&(this._element.style.zIndex=this.props.zIndex)},a.componentWillUnmount=function(){this.clearBackdropAnimationTimeout(),this.props.onExit&&this.props.onExit(),this._element&&(this.destroy(),(this.props.isOpen||this.state.isOpen)&&this.close()),this._isMounted=!1},a.onOpened=function(e,t){this.props.onOpened(),(this.props.modalTransition.onEntered||y)(e,t)},a.onClosed=function(e){var t=this.props.unmountOnClose;this.props.onClosed(),(this.props.modalTransition.onExited||y)(e),t&&this.destroy(),this.close(),this._isMounted&&this.setState({isOpen:!1})},a.setFocus=function(){this._dialog&&this._dialog.parentNode&&"function"===typeof this._dialog.parentNode.focus&&this._dialog.parentNode.focus()},a.getFocusableChildren=function(){return this._element.querySelectorAll(b.h.join(", "))},a.getFocusedChild=function(){var e,t=this.getFocusableChildren();try{e=document.activeElement}catch(a){e=t[0]}return e},a.handleBackdropClick=function(e){if(e.target===this._mouseDownElement){e.stopPropagation();var t=this._dialog?this._dialog.parentNode:null;if(t&&e.target===t&&"static"===this.props.backdrop&&this.handleStaticBackdropAnimation(),!this.props.isOpen||!0!==this.props.backdrop)return;t&&e.target===t&&this.props.toggle&&this.props.toggle(e)}},a.handleTab=function(e){if(9===e.which){var t=this.getFocusableChildren(),a=t.length;if(0!==a){for(var n=this.getFocusedChild(),s=0,o=0;o<a;o+=1)if(t[o]===n){s=o;break}e.shiftKey&&0===s?(e.preventDefault(),t[a-1].focus()):e.shiftKey||s!==a-1||(e.preventDefault(),t[0].focus())}}},a.handleBackdropMouseDown=function(e){this._mouseDownElement=e.target},a.handleEscape=function(e){this.props.isOpen&&e.keyCode===b.l.esc&&this.props.toggle&&(this.props.keyboard?(e.preventDefault(),e.stopPropagation(),this.props.toggle(e)):"static"===this.props.backdrop&&(e.preventDefault(),e.stopPropagation(),this.handleStaticBackdropAnimation()))},a.handleStaticBackdropAnimation=function(){var e=this;this.clearBackdropAnimationTimeout(),this.setState({showStaticBackdropAnimation:!0}),this._backdropAnimationTimeout=setTimeout((function(){e.setState({showStaticBackdropAnimation:!1})}),100)},a.init=function(){try{this._triggeringElement=document.activeElement}catch(e){this._triggeringElement=null}this._element||(this._element=document.createElement("div"),this._element.setAttribute("tabindex","-1"),this._element.style.position="relative",this._element.style.zIndex=this.props.zIndex,this._mountContainer=Object(b.j)(this.props.container),this._mountContainer.appendChild(this._element)),this._originalBodyPadding=Object(b.i)(),Object(b.g)(),0===t.openCount&&(document.body.className=u()(document.body.className,Object(b.m)("modal-open",this.props.cssModule))),t.openCount+=1},a.destroy=function(){this._element&&(this._mountContainer.removeChild(this._element),this._element=null),this.manageFocusAfterClose()},a.manageFocusAfterClose=function(){if(this._triggeringElement){var e=this.props.returnFocusAfterClose;this._triggeringElement.focus&&e&&this._triggeringElement.focus(),this._triggeringElement=null}},a.close=function(){if(t.openCount<=1){var e=Object(b.m)("modal-open",this.props.cssModule),a=new RegExp("(^| )"+e+"( |$)");document.body.className=document.body.className.replace(a," ").trim()}this.manageFocusAfterClose(),t.openCount=Math.max(0,t.openCount-1),Object(b.p)(this._originalBodyPadding)},a.renderModalDialog=function(){var e,t=this,a=Object(b.n)(this.props,O);return l.a.createElement("div",Object(s.a)({},a,{className:Object(b.m)(u()("modal-dialog",this.props.className,(e={},e["modal-"+this.props.size]=this.props.size,e["modal-dialog-centered"]=this.props.centered,e["modal-dialog-scrollable"]=this.props.scrollable,e)),this.props.cssModule),role:"document",ref:function(e){t._dialog=e}}),l.a.createElement("div",{className:Object(b.m)(u()("modal-content",this.props.contentClassName),this.props.cssModule)},this.props.children))},a.render=function(){var e=this.props.unmountOnClose;if(this._element&&(this.state.isOpen||!e)){var t=!!this._element&&!this.state.isOpen&&!e;this._element.style.display=t?"none":"block";var a=this.props,o=a.wrapClassName,r=a.modalClassName,i=a.backdropClassName,c=a.cssModule,d=a.isOpen,m=a.backdrop,p=a.role,h=a.labelledBy,g=a.external,f=a.innerRef,y={onClick:this.handleBackdropClick,onMouseDown:this.handleBackdropMouseDown,onKeyUp:this.handleEscape,onKeyDown:this.handleTab,style:{display:"block"},"aria-labelledby":h,role:p,tabIndex:"-1"},N=this.props.fade,_=Object(n.a)({},v.a.defaultProps,{},this.props.modalTransition,{baseClass:N?this.props.modalTransition.baseClass:"",timeout:N?this.props.modalTransition.timeout:0}),O=Object(n.a)({},v.a.defaultProps,{},this.props.backdropTransition,{baseClass:N?this.props.backdropTransition.baseClass:"",timeout:N?this.props.backdropTransition.timeout:0}),C=m&&(N?l.a.createElement(v.a,Object(s.a)({},O,{in:d&&!!m,cssModule:c,className:Object(b.m)(u()("modal-backdrop",i),c)})):l.a.createElement("div",{className:Object(b.m)(u()("modal-backdrop","show",i),c)}));return l.a.createElement(E,{node:this._element},l.a.createElement("div",{className:Object(b.m)(o)},l.a.createElement(v.a,Object(s.a)({},y,_,{in:d,onEntered:this.onOpened,onExited:this.onClosed,cssModule:c,className:Object(b.m)(u()("modal",r,this.state.showStaticBackdropAnimation&&"modal-static"),c),innerRef:f}),g,this.renderModalDialog()),C))}return null},a.clearBackdropAnimationTimeout=function(){this._backdropAnimationTimeout&&(clearTimeout(this._backdropAnimationTimeout),this._backdropAnimationTimeout=void 0)},t}(l.a.Component);T.propTypes=_,T.defaultProps=C,T.openCount=0;t.a=T},542:function(e,t,a){"use strict";var n=a(6),s=a(7),o=a(0),r=a.n(o),i=a(1),l=a.n(i),c=a(3),d=a.n(c),m=a(4),u={tag:m.q,className:l.a.string,cssModule:l.a.object},p=function(e){var t=e.className,a=e.cssModule,o=e.tag,i=Object(s.a)(e,["className","cssModule","tag"]),l=Object(m.m)(d()(t,"modal-footer"),a);return r.a.createElement(o,Object(n.a)({},i,{className:l}))};p.propTypes=u,p.defaultProps={tag:"div"},t.a=p},543:function(e,t,a){"use strict";var n=a(6),s=a(7),o=a(0),r=a.n(o),i=a(1),l=a.n(i),c=a(3),d=a.n(c),m=a(4),u={tag:m.q,wrapTag:m.q,toggle:l.a.func,className:l.a.string,cssModule:l.a.object,children:l.a.node,closeAriaLabel:l.a.string,charCode:l.a.oneOfType([l.a.string,l.a.number]),close:l.a.object},p=function(e){var t,a=e.className,o=e.cssModule,i=e.children,l=e.toggle,c=e.tag,u=e.wrapTag,p=e.closeAriaLabel,h=e.charCode,b=e.close,g=Object(s.a)(e,["className","cssModule","children","toggle","tag","wrapTag","closeAriaLabel","charCode","close"]),f=Object(m.m)(d()(a,"modal-header"),o);if(!b&&l){var E="number"===typeof h?String.fromCharCode(h):h;t=r.a.createElement("button",{type:"button",onClick:l,className:Object(m.m)("close",o),"aria-label":p},r.a.createElement("span",{"aria-hidden":"true"},E))}return r.a.createElement(u,Object(n.a)({},g,{className:f}),r.a.createElement(c,{className:Object(m.m)("modal-title",o)},i),b||t)};p.propTypes=u,p.defaultProps={tag:"h5",wrapTag:"div",closeAriaLabel:"Close",charCode:215},t.a=p},865:function(e,t,a){"use strict";a.r(t),a.d(t,"default",(function(){return w}));var n=a(59),s=a(11),o=a(12),r=a(82),i=a(15),l=a(14),c=a(0),d=a.n(c),m=a(155),u=a(156),p=a(154),h=a(516),b=a(492),g=a(500),f=a(493),E=a(495),v=a(539),y=a(543),N=a(538),_=a(542),O=a(499),C=a(8),T=a(9),k=a(18),B=a(5),j=a.n(B),S=a(107),A=a(46),w=function(e){Object(i.a)(a,e);var t=Object(l.a)(a);function a(e){var o;return Object(s.a)(this,a),(o=t.call(this,e)).handleNameChange=function(e){var t=e.target.name,a=e.target.value;o.setState(Object(n.a)({},t,a)),"target_url"!=t||a.match(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/)?o.setState({validURL:!1},(function(){this.validateForm()})):o.setState({validURL:!0})},o.onChangeImage=function(e){o.setState({TempFileName:URL.createObjectURL(e.target.files[0])},(function(){this.validateForm()}));var t=e.target.files[0];if(t){var a=new FormData;a.append("userfile",t),T.a.multipartPost(C.nk+C.jg,a).then((function(e){o.setState({fileName:e.data.image_url,fileUplode:e.data.image_url,ImageName:e.data.image})})).catch((function(e){k.notify.show(C.Ri,"error",3e3)}))}},o.deleteToggle=function(e,t,a){e&&o.setState({deleteIndex:t,deleteId:a}),o.setState((function(e){return{DeleteModalOpen:!e.DeleteModalOpen}}))},o.deleteAppBanner=function(){var e=o.state,t=e.MasterScoringRules,a=e.deleteId,n=e.deleteIndex;o.setState({DeleteActionPosting:!0});var s={banner_id:a},r=t;T.a.Rest(C.nk+C.mb,s).then((function(e){e.response_code===C.qk&&(j.a.remove(r,(function(e,t){return t==n})),o.deleteToggle(!1,{},{}),k.notify.show("Banner deleted","success",5e3),o.setState({MasterScoringRules:r,DeleteActionPosting:!1}))})).catch((function(e){k.notify.show(C.Ri,"error",5e3)}))},o.updateBannerStatus=function(e,t,a,n){o.setState({ActionPosting:!0});var s=o.state.MasterScoringRules,r={status:a,banner_id:t,banner_type_id:n};T.a.Rest(C.nk+C.kg,r).then((function(t){t.response_code===C.qk&&(k.notify.show(t.message,"success",3e3),s[e].status=a,o.setState({MasterScoringRules:s,ActionPosting:!1}))})).catch((function(e){k.notify.show(C.Ri,"error",5e3)}))},o.state={TotalBanner:0,PERPAGE:C.Vf,CURRENT_PAGE:1,BannerType:[],NewBannerToggle:!1,AddBannerPosting:!1,target_url:"",banner_name:"",fileUplode:"",fileName:"",TempFileName:"",validURL:!1,MasterScoringRules:[],ActionPosting:!1,BannerOption:[],FixtureOption:[]},o.onChangeImage=o.onChangeImage.bind(Object(r.a)(o)),o.resetFile=o.resetFile.bind(Object(r.a)(o)),o}return Object(o.a)(a,[{key:"componentDidMount",value:function(){this.getLobbyBanner(),this.getBannerType()}},{key:"getLobbyBanner",value:function(){var e=this,t=this.state,a={items_perpage:t.PERPAGE,total_items:0,current_page:t.CURRENT_PAGE,sort_order:"DESC",sort_field:"banner_id"};T.a.Rest(C.nk+C.wd,a).then((function(t){t.response_code===C.qk&&e.setState({MasterScoringRules:t.data,TotalBanner:t.data.total})})).catch((function(e){k.notify.show(C.Ri,"error",5e3)}))}},{key:"getBannerType",value:function(){var e=this;T.a.Rest(C.nk+C.yd,{}).then((function(t){if(t.response_code===C.qk){var a=[];j.a.map(t.data,(function(e){a.push({value:e.banner_type_id,label:e.banner_type})})),e.setState({BannerType:t.data,BannerOption:a})}})).catch((function(e){k.notify.show(C.Ri,"error",5e3)}))}},{key:"getFixtureType",value:function(){var e=this;T.a.Rest(C.nk+C.pd,{name:"",target_url:"",banner_type_id:"1",collection_master_id:"",image:"",is_preview:0,is_remove:0,uploadbtn:1,image_name:"",size_tip:""}).then((function(t){if(t.response_code===C.qk){var a=[];j.a.map(t.data,(function(e){a.push({value:e.collection_master_id,label:e.collection_name+" "+e.season_schedule_date})})),e.setState({BannerType:t.data,FixtureOption:a})}})).catch((function(e){k.notify.show(C.Ri,"error",5e3)}))}},{key:"NewBannerTogle",value:function(e){this.setState({NewBannerToggle:e})}},{key:"handleBannerType",value:function(e,t){var a=this;this.setState(Object(n.a)({},e,t),(function(){a.validateForm(),1==t&&a.getFixtureType()}))}},{key:"validateForm",value:function(){var e=this.state,t=e.SelectBannerType,a=e.banner_name,n=e.SelectFixtureType,s=e.target_url,o=e.TempFileName;this.setState({AddBannerPosting:!1}),(1!=t||j.a.isEmpty(o)||j.a.isEmpty(t)||j.a.isEmpty(a)||j.a.isEmpty(n))&&(4!=t||j.a.isEmpty(o)||j.a.isEmpty(t)||j.a.isEmpty(a)||j.a.isEmpty(s))&&(j.a.isEmpty(t)||j.a.isEmpty(o)||j.a.isEmpty(a)||1==t||4==t)||this.setState({AddBannerPosting:!0})}},{key:"resetFile",value:function(e){e.preventDefault(),document.getElementById("banner_image").value="",this.setState({fileName:null,fileUplode:null},(function(){this.validateForm()}))}},{key:"createBanner",value:function(){var e=this;this.setState({AddBannerPosting:!1});var t=this.state,a=t.banner_name,s=t.target_url,o=t.SelectBannerType,r=t.fileUplode,i=t.ImageName,l=t.SelectFixtureType,c=Object(n.a)({name:a,target_url:s,banner_type_id:o,collection_master_id:"",image:r,image_name:i,is_preview:1,is_remove:1,uploadbtn:0,size_tip:""},"collection_master_id",l);T.a.Rest(C.nk+C.T,c).then((function(t){t.response_code===C.qk&&(k.notify.show(t.message,"success",3e3),e.setState({fileName:null,fileUplode:null,target_url:"",banner_name:"",SelectFixtureType:"",SelectBannerType:""},(function(){this.getLobbyBanner(),this.NewBannerTogle(!1)}))),e.setState({AddBannerPosting:!0})})).catch((function(e){k.notify.show(C.Ri,"error",3e3)}))}},{key:"handlePageChange",value:function(e){var t=this;this.setState({CURRENT_PAGE:e},(function(){t.getLobbyBanner()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.MasterScoringRules,n=t.NewBannerToggle,s=t.AddBannerPosting,o=t.SelectBannerType,r=t.banner_name,i=t.SelectFixtureType,l=t.target_url,C=t.validURL,T=t.ActionPosting,k=t.DeleteActionPosting,B=t.BannerOption,w=t.FixtureOption;return d.a.createElement(c.Fragment,null,!n&&d.a.createElement("div",{className:"mt-4"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:12},d.a.createElement("h1",{className:"h1-cls"},"Manage Banner"))),d.a.createElement(m.a,{className:"filters-box"},d.a.createElement(u.a,{md:12},d.a.createElement("div",{className:"filters-area"},d.a.createElement(p.a,{className:"btn-secondary",onClick:function(){return e.NewBannerTogle(!0)}},"New Banner")))),d.a.createElement(m.a,{className:"animated fadeIn new-banner"},d.a.createElement(u.a,{md:12,className:"table-responsive common-table"},d.a.createElement(h.a,null,d.a.createElement("thead",null,d.a.createElement("tr",null,d.a.createElement("th",{className:"left-th pl-4"},"Banner Type"),d.a.createElement("th",null,"Name"),d.a.createElement("th",null,"Target Url"),d.a.createElement("th",null,"Image"),d.a.createElement("th",null,"Status"),d.a.createElement("th",null,"Action"))),j.a.map(a,(function(t,a){return d.a.createElement("tbody",{key:a},d.a.createElement("tr",null,d.a.createElement("td",{className:"pl-4"},t.banner_type),d.a.createElement("td",null,t.name),d.a.createElement("td",null,t.target_url?t.target_url:"--"),d.a.createElement("td",null,d.a.createElement("figure",{className:"lobby-banner-img"},d.a.createElement("img",{src:t.image?t.image:A.a.no_image,className:"img-cover",alt:""}),"   ")),d.a.createElement("td",null,1==t.status?d.a.createElement("i",{className:"icon-verified active"}):d.a.createElement("i",{className:"icon-inactive"})),d.a.createElement("td",null,d.a.createElement(b.a,null,d.a.createElement(g.a,{disabled:T,className:"icon-action"}),d.a.createElement(f.a,null,1==t.status?d.a.createElement(E.a,{onClick:function(){return e.updateBannerStatus(a,t.banner_id,0,t.banner_type_id)}},"Deactivate"):d.a.createElement(E.a,{onClick:function(){return e.updateBannerStatus(a,t.banner_id,1,t.banner_type_id)}},"Active"),d.a.createElement(E.a,{onClick:function(){e.deleteToggle(!0,a,t.banner_id)}},"Delete"))))))}))))),d.a.createElement("div",null,d.a.createElement(v.a,{isOpen:this.state.DeleteModalOpen,toggle:this.deleteToggle},d.a.createElement(y.a,null,"Delete App Banner"),d.a.createElement(N.a,null,"Are you sure to delete this App banner data?"),d.a.createElement(_.a,null,d.a.createElement(p.a,{disabled:k,color:"secondary",onClick:function(){return e.deleteAppBanner()}},"Yes")," ",d.a.createElement(p.a,{color:"primary",onClick:this.deleteToggle},"No"))))),n&&d.a.createElement("div",{className:"mt-4"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:12},d.a.createElement("h1",{className:"h1-cls"},"New Banner"))),d.a.createElement("div",{className:"animated fadeIn new-banner"},d.a.createElement(u.a,{md:12,className:"input-row"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:3,className:"b-input-label"},"Banner Type ",d.a.createElement("span",{className:"asterrisk"},"*")),d.a.createElement(u.a,{md:9},d.a.createElement("i",{className:"icon-Shape"}),d.a.createElement(S.a,{name:"BannerType",searchable:!0,clearable:!1,options:B,placeholder:"Select Banner Type",menuIsOpen:!0,value:o,onChange:function(t){return e.handleBannerType("SelectBannerType",t.value)}})))),1==o&&d.a.createElement(u.a,{md:12,className:"input-row"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:3,className:"b-input-label"},"Fixtures",d.a.createElement("span",{className:"asterrisk"},"*")),d.a.createElement(u.a,{md:9},d.a.createElement("i",{className:"icon-Shape"}),d.a.createElement(S.a,{searchable:!0,clearable:!1,options:w,placeholder:"Select Fixtures",menuIsOpen:!0,value:i,onChange:function(t){return e.handleBannerType("SelectFixtureType",t.value)}})))),4==o&&d.a.createElement(u.a,{md:12,className:"input-row"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:3,className:"b-input-label"},"Target Url",d.a.createElement("span",{className:"asterrisk"},"*")),d.a.createElement(u.a,{md:9},d.a.createElement(O.a,{type:"text",name:"target_url",placeholder:"Target Url",onChange:this.handleNameChange,value:l}),C&&d.a.createElement("span",{className:"error-text"},"Please upload valid target URL")))),d.a.createElement(u.a,{md:12,className:"input-row"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:3,className:"b-input-label"},"Name ",d.a.createElement("span",{className:"asterrisk"},"*")),d.a.createElement(u.a,{md:9},d.a.createElement(O.a,{type:"text",name:"banner_name",placeholder:"Banner Name",onChange:this.handleNameChange,value:r})))),d.a.createElement(u.a,{md:12,className:"input-row"},d.a.createElement(m.a,null,d.a.createElement(u.a,{md:3,className:"b-input-label"},"Upload Image (1300 X 240) ",d.a.createElement("span",{className:"asterrisk"},"*")),d.a.createElement(u.a,{md:9},d.a.createElement(O.a,{type:"file",name:"banner_image",id:"banner_image",onChange:this.onChangeImage}),this.state.fileName&&d.a.createElement("div",{className:"banner-img"},d.a.createElement(p.a,{className:"btn-secondary mt-4 mb-3",onClick:this.resetFile},"Remove"),d.a.createElement("img",{className:"img-cover",src:this.state.fileName}))))),d.a.createElement(u.a,{md:12,className:"banner-action"},d.a.createElement(p.a,{disabled:!s,className:"btn-secondary mr-3",onClick:function(){return e.createBanner()}},"Save"),d.a.createElement(p.a,{className:"btn-secondary-outline",onClick:function(){return e.NewBannerTogle(!1)}},"Cancel")))))}}]),a}(c.Component)}}]);
//# sourceMappingURL=96.f816217c.chunk.js.map