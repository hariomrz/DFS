(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[213],{958:function(e,t,a){"use strict";a.r(t);var n=a(59),l=a(11),s=a(12),r=a(15),o=a(14),c=a(0),i=a.n(c),d=a(539),u=a(543),m=a(538),E=a(155),p=a(156),h=a(499),g=a(542),R=a(154),f=a(516),w=a(5),_=a.n(w),P=a(10),b=a(18),S=a(157),y=a(514),A=a(8),v=a(511),N=a(565),C=a(507),T=a.n(C),M=a(158),O=function(e){Object(r.a)(a,e);var t=Object(o.a)(a);function a(e){var s;return Object(l.a)(this,a),(s=t.call(this,e)).getReward=function(){s.setState({ListPosting:!0});var e=s.state,t={items_perpage:e.PERPAGE,current_page:e.CURRENT_PAGE};Object(y.Hb)(t).then((function(e){if(e.response_code==A.qk){var t=e.data?e.data:[];s.setState({ListPosting:!1,RewardList:t.result?t.result:[],Total:t.total?t.total:0,prizeOptions:t.prize_type?t.prize_type:[]})}else b.notify.show(A.Ri,"error",3e3)})).catch((function(e){b.notify.show(A.Ri,"error",3e3)}))},s.deleteToggle=function(e){s.setState({RewardId:e,DeleteModalOpen:!s.state.DeleteModalOpen})},s.deleteReward=function(){var e=s.state,t=e.RewardId,a=e.RewardList,n=e.Total;s.setState({DeletePosting:!0});var l={scratch_card_id:t},r=a;Object(y.ab)(l).then((function(e){e.response_code===A.qk&&(_.a.remove(r,(function(e){return e.scratch_card_id==t})),s.setState({RewardList:r,DeleteModalOpen:!1,Total:0==r.length?0:n}),b.notify.show(e.message,"success",5e3)),s.setState({DeletePosting:!1})})).catch((function(e){b.notify.show(A.Ri,"error",5e3)}))},s.addEditRewadModalToggle=function(e,t){s.setState({SCRATCH_CARD_ID:t.scratch_card_id?t.scratch_card_id:"1",SelectPrizeType:t.prize_type?t.prize_type:"1",Amount:t.amount?t.amount:"",ResultText:t.result_text?t.result_text:"",RewardStatus:t.status?t.status:"1",addEditRewFlag:e,addEditPosting:!0,addEditModalOpen:!s.state.addEditModalOpen})},s.handlePrizeChange=function(e){Object(P.d)(s.state.Amount)||s.setState({addEditPosting:!1}),s.setState({SelectPrizeType:e.value},s.createResultMsg)},s.handleInputChange=function(e){var t=e.target.name,a=e.target.value;s.setState({AmountMsg:!1}),"Amount"===t&&P.i.isFloat(a)&&(a=s.state.Amount,s.setState({AmountMsg:!0})),s.setState(Object(n.a)({},t,a),(function(){s.createResultMsg(),"Amount"===t&&(a.length<=0||a.length>7)?s.setState({Amount:"",AmountMsg:!0,addEditPosting:!0,ResultText:""}):s.setState({addEditPosting:!1})}))},s.addEditReward=function(){s.setState({addEditPosting:!0});var e=s.state,t=e.addEditRewFlag,a=e.SelectPrizeType,n=e.Amount,l=e.ResultText,r=e.RewardStatus,o=e.SCRATCH_CARD_ID,c={prize_type:"0"===n?"":a,amount:n,result_text:l,status:r},i="";1==t?i=Object(y.N)(c):(c.scratch_card_id=o,i=Object(y.hc)(c)),i.then((function(e){e.response_code==A.qk?(s.getReward(),s.setState({SelectPrizeType:"1",Amount:"",ResultText:"",RewardStatus:"1",addEditModalOpen:!1}),b.notify.show(e.message,"success",5e3)):b.notify.show(A.Ri,"error",5e3)})).catch((function(e){b.notify.show(A.Ri,"error",5e3)}))},s.createResultMsg=function(){var e=s.state,t=e.SelectPrizeType,a=e.Amount,n="";if("0"==t)n="bonus cash";else if("1"==t)n="real cash";else if("2"==t){n="coin"+(a>1?"s":"")}var l="Better luck next time";a>0&&(l=a?"You won "+a+" "+n:""),s.setState({ResultText:l})},s.state={CURRENT_PAGE:1,PERPAGE:A.Vf,RewardList:[],ListPosting:!0,DeletePosting:!1,DeleteModalOpen:!1,addEditModalOpen:!1,SelectPrizeType:"1",Amount:"",ResultText:"",RewardStatus:"1",AmountMsg:!1,addEditPosting:!0,prizeOptions:[]},s}return Object(s.a)(a,[{key:"componentDidMount",value:function(){"1"!=P.i.allowScratchWin()&&(b.notify.show(v.M,"error",5e3),this.props.history.push("/dashboard")),this.getReward()}},{key:"handlePageChange",value:function(e){e!=this.state.CURRENT_PAGE&&this.setState({CURRENT_PAGE:e},this.getReward)}},{key:"addEditRewadModal",value:function(){var e=this,t=this.state,a=t.addEditRewFlag,n=t.addEditPosting,l=t.Amount,s=t.addEditModalOpen,r=t.prizeOptions,o=t.SelectPrizeType,c=t.ResultText,f=t.RewardStatus,w=t.AmountMsg,_={is_disabled:"0"===l,is_searchable:!0,is_clearable:!1,menu_is_open:!1,class_name:"form-control",sel_options:"0"===l?[]:r,place_holder:"Select Prize",selected_value:o,modalCallback:this.handlePrizeChange};return i.a.createElement(d.a,{isOpen:s,toggle:function(){return e.addEditRewadModalToggle("","")},className:"add-league-modal modal-xs reward-mod ".concat(1===a?"animate-modal-top":"")},i.a.createElement(u.a,null,1===a?"Add":"Edit"," Reward"),i.a.createElement(m.a,null,i.a.createElement(E.a,null,i.a.createElement(p.a,{md:12},i.a.createElement("label",null,"Select Prize"),i.a.createElement(M.a,{SelectProps:_}))),i.a.createElement(E.a,{className:"mt-3"},i.a.createElement(p.a,{md:12},i.a.createElement("label",null,"Amount"),i.a.createElement(h.a,{maxLength:7,type:"number",name:"Amount",placeholder:"Amount",value:l,onChange:function(t){return e.handleInputChange(t)}}),w&&i.a.createElement("span",{className:"color-red"},v.Db))),i.a.createElement(E.a,{className:"mt-3"},i.a.createElement(p.a,{md:12},i.a.createElement("label",null,"Result Text"),i.a.createElement(h.a,{disabled:!0,type:"text",name:"ResultText",placeholder:"ResultText",value:c,onChange:function(t){return e.handleInputChange(t)}}))),i.a.createElement(E.a,{className:"mt-3"},i.a.createElement(p.a,{md:12},i.a.createElement("label",{htmlFor:"ProofDesc"},"Reward Status"),i.a.createElement("ul",{className:"radio-option-list"},i.a.createElement("li",{className:"radio-option-item"},i.a.createElement("div",{className:"custom-radio"},i.a.createElement("input",{type:"radio",className:"custom-control-input",name:"RewardStatus",value:"1",checked:"1"===f,onChange:this.handleInputChange}),i.a.createElement("label",{className:"custom-control-label"},i.a.createElement("span",{className:"input-text"},"Active")))),i.a.createElement("li",{className:"radio-option-item"},i.a.createElement("div",{className:"custom-radio"},i.a.createElement("input",{type:"radio",className:"custom-control-input",name:"RewardStatus",value:"0",checked:"0"===f,onChange:this.handleInputChange}),i.a.createElement("label",{className:"custom-control-label"},i.a.createElement("span",{className:"input-text"},"Inactive")))))))),i.a.createElement(g.a,null,i.a.createElement(R.a,{disabled:n,className:"btn-secondary-outline",onClick:this.addEditReward},2===a?"Update":"Save")))}},{key:"render",value:function(){var e=this,t=this.state,a=t.RewardList,n=t.ListPosting,l=t.Total,s=t.DeleteModalOpen,r=t.DeletePosting,o=t.CURRENT_PAGE,c=t.PERPAGE,d=t.addEditModalOpen,u={publishModalOpen:s,publishPosting:r,modalActionNo:this.deleteToggle,modalActionYes:this.deleteReward,MainMessage:v.Eb,SubMessage:v.Fb};return i.a.createElement("div",{className:"sw_reward"},s&&i.a.createElement(N.a,u),d&&this.addEditRewadModal(),i.a.createElement(E.a,null,i.a.createElement(p.a,{md:12},i.a.createElement("h2",{className:"h2-cls float-left animate-left"},"Manage Reward"),i.a.createElement(R.a,{onClick:function(){return e.addEditRewadModalToggle(1,"")},className:"btn-secondary-outline float-right animate-right"},"Add Reward"))),i.a.createElement(E.a,{className:"mt-30"},i.a.createElement(p.a,{md:12,className:"table-responsive common-table"},i.a.createElement(f.a,{className:"animate-top"},i.a.createElement("thead",null,i.a.createElement("tr",null,i.a.createElement("th",null,"Prize"),i.a.createElement("th",null,"Amount"),i.a.createElement("th",null,"Result text"),i.a.createElement("th",null,"Status"),i.a.createElement("th",null,"Action"))),l>0?_.a.map(a,(function(t,a){return i.a.createElement("tbody",{key:a},i.a.createElement("tr",null,i.a.createElement("td",null,""==t.prize_type&&"--","0"==t.prize_type&&"Bonus","1"==t.prize_type&&"Real Cash","2"==t.prize_type&&"Coin"),i.a.createElement("td",null,"0"==t.amount?"--":t.amount),i.a.createElement("td",null,t.result_text),i.a.createElement("td",null,i.a.createElement("span",{className:"".concat("1"==t.status?"text-green":"text-red")},"1"==t.status&&"Active","0"==t.status&&"Inactive")),i.a.createElement("td",null,i.a.createElement("i",{onClick:function(){return e.deleteToggle(t.scratch_card_id)},className:"icon-delete"}),i.a.createElement("i",{onClick:function(){return e.addEditRewadModalToggle(2,t)},className:"icon-edit ml-4"}))))})):i.a.createElement("tbody",null,i.a.createElement("tr",null,i.a.createElement("td",{colSpan:"22"},0!=l||n?i.a.createElement(S.a,null):i.a.createElement("div",{className:"no-records"},"No Record Found."))))))),i.a.createElement(E.a,null,i.a.createElement(p.a,{md:12},l>c&&i.a.createElement("div",{className:"custom-pagination float-right mt-5"},i.a.createElement(T.a,{activePage:o,itemsCountPerPage:c,totalItemsCount:l,pageRangeDisplayed:5,onChange:function(t){return e.handlePageChange(t)}})))))}}]),a}(c.Component);t.default=O}}]);
//# sourceMappingURL=213.dfecb0c9.chunk.js.map