(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[174],{545:function(e,t,a){"use strict";var n=a(11),i=a(12),l=a(15),s=a(14),o=a(0),r=a.n(o),c=a(539),m=a(543),u=a(538),p=a(542),d=a(154),f=a(46),E=function(e){Object(l.a)(a,e);var t=Object(s.a)(a);function a(e){return Object(n.a)(this,a),t.call(this,e)}return Object(i.a)(a,[{key:"render",value:function(){var e=this,t=this.props,a=t.Message,n=t.ActionPopupOpen,i=t.Screen,l=t.posting;return r.a.createElement(r.a.Fragment,null,r.a.createElement(c.a,{isOpen:n,className:"modal-sm action-request",toggle:function(){return e.props.modalCallback()}},r.a.createElement(m.a,null,r.a.createElement("img",{src:f.a.ERROR_ICON,alt:""})),r.a.createElement(u.a,null,r.a.createElement("span",{className:"info-text"},a)),r.a.createElement(p.a,{className:"request-footer"},r.a.createElement(d.a,{className:"btn-secondary-outline ripple no-btn",onClick:this.props.modalCallback},"No"),r.a.createElement(d.a,{disabled:l,className:"btn-secondary-outline ripple",onClick:function(){return"Report"==i?e.props.modalReportActionCallback():"Approve"==i?e.props.modalUpdatePendingCallback():e.props.modalActioCallback()}},"Yes"))))}}]),a}(o.Component);t.a=E},938:function(e,t,a){"use strict";a.r(t);var n=a(59),i=a(11),l=a(12),s=a(15),o=a(14),r=a(0),c=a.n(r),m=a(155),u=a(156),p=a(499),d=a(154),f=a(516),E=a(8),h=a(9),g=a(18),b=a(507),S=a.n(b),y=a(5),v=a.n(y),C=a(523),P=a.n(C),N=a(545),_=a(10),A=function(e){Object(s.a)(a,e);var t=Object(o.a)(a);function a(e){var l;return Object(i.a)(this,a),(l=t.call(this,e)).getUserList=function(){var e=l.state,t=e.PERPAGE,a=e.CURRENT_PAGE,n=e.isDescOrder,i=e.sortField,s={items_perpage:t,current_page:a,sort_order:n?"DESC":"ASC",sort_field:i};h.a.Rest(E.nk+E.Bi,s).then((function(e){e.response_code==E.qk?(1==a&&l.setState({SelfExclusion:e.data?e.data.self_exclusion:[]},(function(){if(!v.a.isEmpty(l.state.SelfExclusion)){var e=l.state.SelfExclusion;if(e[0].custom_data){var t=JSON.parse(e[0].custom_data);v.a.isUndefined(t.max_limit)||l.setState({MaximumLimit:t.max_limit}),v.a.isUndefined(t.default_limit)||l.setState({DefaultLimit:t.default_limit})}}})),l.setState({UserList:e.data?e.data.result:[],Total:e.data.total})):g.notify.show(E.Ri,"error",3e3)})).catch((function(e){g.notify.show(E.Ri,"error",3e3)}))},l.toggleActionPopup=function(e,t){l.setState({Message:E.vg,idxVal:t,UserID:e,ActionPopupOpen:!l.state.ActionPopupOpen})},l.setDefault=function(){l.setState({setDefPost:!0});var e=l.state,t=e.UserID,a=e.idxVal,n={user_id:t};h.a.Rest(E.nk+E.Hi,n).then((function(e){e.response_code==E.qk?(l.setState({setDefPost:!1}),l.toggleActionPopup(t,a),l.getUserList(),g.notify.show(e.message,"success",5e3)):g.notify.show(E.Ri,"error",5e3)})).catch((function(e){g.notify.show(E.Ri,"error",5e3)}))},l.handleInputChange=function(e){var t,a=e.target.name,i=e.target.value;l.setState((t={},Object(n.a)(t,a,i),Object(n.a)(t,"formValid",!1),t),(function(){if(v.a.isEmpty(l.state.DefaultLimit)){return g.notify.show("Default limit can not be empty.","error",3e3),l.setState({formValid:!0}),!1}if(v.a.isEmpty(l.state.MaximumLimit)){return g.notify.show("Maximum limit can not be empty.","error",3e3),l.setState({formValid:!0}),!1}if(parseInt(l.state.MaximumLimit)<parseInt(l.state.DefaultLimit)){return g.notify.show("Maximum limit should be greater than equal to default limit.","error",3e3),l.setState({formValid:!0}),!1}}))},l.SaveLimit=function(){l.setState({formValid:!0});var e=l.state,t={default_limit:e.DefaultLimit,max_limit:e.MaximumLimit};h.a.Rest(E.nk+E.wj,t).then((function(e){e.response_code==E.qk?(g.notify.show(e.message,"success",5e3),l.toggleSubActionPopup()):g.notify.show(E.Ri,"error",5e3)})).catch((function(e){g.notify.show(E.Ri,"error",5e3)}))},l.toggleSubActionPopup=function(){l.setState({SubMessage:E.wg,SubActionPopupOpen:!l.state.SubActionPopupOpen})},l.state={PERPAGE:E.Vf,CURRENT_PAGE:1,DefaultLimit:"",MaximumLimit:"",UserList:[],SelfExclusion:[],formValid:!0,sortField:"user_name",isDescOrder:"true",ActionPopupOpen:!1,SubActionPopupOpen:!1,setDefPost:!1},l}return Object(l.a)(a,[{key:"componentDidMount",value:function(){"1"!=_.i.allowSelfExclusion()&&(g.notify.show(E.og,"error",5e3),this.props.history.push("/dashboard")),this.getUserList()}},{key:"sortByColumn",value:function(e,t){var a=!t;this.setState({sortField:e,isDescOrder:a,CURRENT_PAGE:1},this.getUserList)}},{key:"handlePageChange",value:function(e){var t=this;e!==this.state.CURRENT_PAGE&&this.setState({CURRENT_PAGE:e},(function(){t.getUserList()}))}},{key:"render",value:function(){var e=this,t=this.state,a=t.UserList,n=t.DefaultLimit,i=t.MaximumLimit,l=t.CURRENT_PAGE,s=t.PERPAGE,o=t.Total,r=t.ActionPopupOpen,g=t.Message,b=t.formValid,y=t.isDescOrder,C=t.sortField,_=t.SubMessage,A=t.SubActionPopupOpen,k=t.setDefPost,x={Message:g,modalCallback:this.toggleActionPopup,ActionPopupOpen:r,modalActioCallback:this.setDefault,posting:k},R={Message:_,modalCallback:this.toggleSubActionPopup,ActionPopupOpen:A,modalActioCallback:this.SaveLimit,posting:b};return c.a.createElement("div",{className:"self-exclusion animated fadeIn"},c.a.createElement(N.a,x),c.a.createElement(N.a,R),c.a.createElement(m.a,null,c.a.createElement(u.a,{md:12},c.a.createElement("h1",{className:"h1-class"},"Self Exclusion"),c.a.createElement("div",{className:"se-sub-title"},"Selecting the loosing limit will be applicable to all the fantasy player. The new limit set will take immidiate effect. "))),c.a.createElement("div",{className:"se-limit-box"},c.a.createElement(m.a,null,c.a.createElement(u.a,{md:6},c.a.createElement("div",{className:"se-input-div"},c.a.createElement("label",{className:"se-label"},"Default Limit"),c.a.createElement("div",{className:"se-input-box"},c.a.createElement(p.a,{type:"number",placeholder:"500",name:"DefaultLimit",value:n,onChange:function(t){return e.handleInputChange(t)}}),c.a.createElement("span",null,"(This is the limit which is already set for the user, The Default limit is ",n,")")))),c.a.createElement(u.a,{md:6},c.a.createElement("label",{className:"se-label"},"Maximum Limit"),c.a.createElement("div",{className:"se-input-box"},c.a.createElement(p.a,{type:"number",placeholder:"1000",name:"MaximumLimit",value:i,onChange:function(t){return e.handleInputChange(t)}}),c.a.createElement("span",null,"(This is the max limit that user can set on their own without approval)")))),c.a.createElement(m.a,{className:"text-center mt-5"},c.a.createElement(u.a,{md:12},c.a.createElement(d.a,{disabled:b,className:"btn-secondary mr-3",onClick:function(){return e.toggleSubActionPopup()}},"Save")))),c.a.createElement(m.a,{className:"mt-5"},c.a.createElement(u.a,{md:12},c.a.createElement("h4",null,"User List"))),c.a.createElement(m.a,null,c.a.createElement(u.a,{md:12,className:"table-responsive common-table"},c.a.createElement(f.a,null,c.a.createElement("thead",null,c.a.createElement("tr",null,c.a.createElement("th",{className:"left-th text-center",onClick:function(){return e.sortByColumn("modified_date",y)}},"Updated Date",c.a.createElement("div",{className:"d-inline-block ".concat("modified_date"===C&&y?"":"rotate-icon")},c.a.createElement("i",{className:"icon-Shape ml-1"}))),c.a.createElement("th",{onClick:function(){return e.sortByColumn("user_name",y)}},"User Name",c.a.createElement("div",{className:"d-inline-block ".concat("user_name"===C&&y?"":"rotate-icon")},c.a.createElement("i",{className:"icon-Shape ml-1"}))),c.a.createElement("th",{onClick:function(){return e.sortByColumn("max_limit",y)}},"New limit",c.a.createElement("div",{className:"d-inline-block ".concat("max_limit"===C&&y?"":"rotate-icon")},c.a.createElement("i",{className:"icon-Shape ml-1"}))),c.a.createElement("th",null,"Changed By"),c.a.createElement("th",{className:"right-th"},"Default Limit"))),o>0?v.a.map(a,(function(t,a){return c.a.createElement("tbody",{key:a},c.a.createElement("tr",null,c.a.createElement("td",null,c.a.createElement(P.a,{date:h.a.getUtcToLocal(t.modified_date),format:"D-MMM-YYYY hh:mm A"})),c.a.createElement("td",null,t.user_name),c.a.createElement("td",null,t.max_limit),c.a.createElement("td",null,"1"==t.set_by&&"Set by user","2"==t.set_by&&"Set by admin"),c.a.createElement("td",null,c.a.createElement("a",{onClick:function(){return e.toggleActionPopup(t.user_id,1)},className:"se-set-default"},"Set to default"))))})):c.a.createElement("tbody",null,c.a.createElement("tr",null,c.a.createElement("td",{colSpan:"12"},c.a.createElement("div",{className:"no-records"},E.Cg))))))),o>s&&c.a.createElement("div",{className:"custom-pagination lobby-paging"},c.a.createElement(S.a,{activePage:l,itemsCountPerPage:s,totalItemsCount:o,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})))}}]),a}(r.Component);t.default=A}}]);
//# sourceMappingURL=174.7cf48e97.chunk.js.map