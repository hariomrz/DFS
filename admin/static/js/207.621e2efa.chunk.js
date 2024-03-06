(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[207],{952:function(e,a,t){"use strict";t.r(a);var n=t(59),i=t(11),s=t(12),r=t(15),m=t(14),c=t(0),l=t.n(c),d=t(155),o=t(156),u=t(499),h=t(154),g=t(5),f=t.n(g),E=t(8),N=t(9),p=t(18),M=function(e){Object(r.a)(t,e);var a=Object(m.a)(t);function t(e){var s;return Object(i.a)(this,t),(s=a.call(this,e)).handleInputChange=function(e){var a=e.target.name,t=e.target.value;s.setState(Object(n.a)({},a,t),(function(){return s.validateForm(a,t)}))},s.validateForm=function(e,a){var t=s.state.MName,n=s.state.MValue,i=s.state.fileName;switch(e){case"MName":t=a.length>0&&a.length<=50,s.setState({MNameMSg:t});break;case"MValue":n=!(a.length>10&&!a.match(/^[0-9]*$/)),s.setState({MValueMSg:n})}s.setState({formValid:t&&n&&!f.a.isUndefined(i)&&!f.a.isNull(i)})},s.onChangeImage=function(e,a,t){s.setState({fileName:URL.createObjectURL(e.target.files[0])},(function(){this.validateForm()}));var n=e.target.files[0];if(n){var i=new FormData;s.state.editCase&&i.append("merchandise_id",t),i.append("file",n),i.append("source",a),i.append("previous_img",s.state.Previous_Img),N.a.multipartPost(E.nk+E.ci,i).then((function(e){e.response_code==E.qk?s.setState({IMAGE_NAME:e.data.image_name}):s.setState({fileName:null},s.validateForm)})).catch((function(e){console.log("486"),p.notify.show(E.Ri,"error",3e3)}))}},s.resetFile=function(){s.setState({fileName:null},(function(){this.validateForm()}))},s.addMerchandise=function(){s.setState({formValid:!1});var e=s.state,a={name:e.MName,price:e.MValue,image_name:e.IMAGE_NAME};N.a.Rest(E.nk+E.Dh,a).then((function(e){s.getMerchandiseList(),e.response_code==E.qk&&(p.notify.show(e.message,"success",5e3),s.setState({MName:"",MValue:"0",fileName:"",IMAGE_NAME:""})),s.setState({formValid:!0})})).catch((function(e){p.notify.show(E.Ri,"error",5e3)}))},s.updateMerchandise=function(){var e=s.state,a={name:e.MName,price:e.MValue,image_name:e.IMAGE_NAME,merchandise_id:e.EditItemData.merchandise_id};N.a.Rest(E.nk+E.Yh,a).then((function(e){s.getMerchandiseList(),e.response_code==E.qk&&(p.notify.show(e.message,"success",5e3),s.setState({MName:"",MValue:"",IMAGE_NAME:"",fileName:"",showCancelBtn:!1}))})).catch((function(e){p.notify.show(E.Ri,"error",5e3)})),s.setState({editCase:!1})},s.getMerchandiseList=function(){var e=s.state,a={sort_field:"added_date",sort_order:"DESC",items_perpage:e.PERPAGE,current_page:e.CURRENT_PAGE};N.a.Rest(E.nk+E.Nh,a).then((function(e){e.response_code==E.qk&&s.setState({MerchandiseList:e.data.merchandise_list,NextOffset:e.data.next_offset})})).catch((function(e){p.notify.show(E.Ri,"error",5e3)}))},s.editMerchandise=function(e){var a=e.merchandise_id;s.setState({showCancelBtn:!0,EditItemData:e,editCase:!0});var t={merchandise_id:a};N.a.Rest(E.nk+E.Mh,t).then((function(e){e.response_code==E.qk&&s.setState({MName:e.data.name,MValue:e.data.price,IMAGE_NAME:e.data.image_name,fileName:E.wi+E.ng+e.data.image_name,Previous_Img:e.data.image_name,reload:!1},(function(){window.scrollTo({top:0,behavior:"smooth"}),s.setState({reload:!0})}))})).catch((function(e){p.notify.show(E.Ri,"error",5e3)}))},s.resetValue=function(){s.setState({reload:!1,MName:"",MValue:"0",fileName:"",IMAGE_NAME:""},(function(){s.setState({reload:!0})}))},s.state={reload:!0,showCancelBtn:!1,MName:"",MValue:"0",fileName:"",formValid:!1,MNameMSg:!0,MValueMSg:!0,MerchandiseList:[],PERPAGE:E.Vf,CURRENT_PAGE:1,Previous_Img:"",EditItemData:[],editCase:!1},s}return Object(s.a)(t,[{key:"componentDidMount",value:function(){this.getMerchandiseList()}},{key:"render",value:function(){var e=this,a=this.state,t=a.fileName,n=a.MNameMSg,i=(a.MValueMSg,a.MName),s=a.MValue,r=a.formValid,m=a.MerchandiseList,g=a.reload,N=a.showCancelBtn,p=a.EditItemData;return l.a.createElement("div",{className:"animated fadeIn add-merchandise-container"},l.a.createElement("div",{className:"form-container"},l.a.createElement("div",{className:"header-primary"},"Add Merchandise"),g&&l.a.createElement("div",{className:"form-body add-rewards"},l.a.createElement(d.a,{className:"pb-3"},l.a.createElement(o.a,{xs:8,className:"border-right"},l.a.createElement("figure",{className:"upload-img"},f.a.isEmpty(t)?l.a.createElement(c.Fragment,null,this.state.editCase?l.a.createElement(c.Fragment,null,l.a.createElement(u.a,{accept:"image/x-png,image/gif,image/jpeg,image/bmp,image/jpg",type:"file",name:"merchandise_image",id:"merchandise_image",className:"gift_image",onChange:function(a){return e.onChangeImage(a,"edit",p.merchandise_id)}}),l.a.createElement("i",{onChange:function(a){return e.onChangeImage(a,"edit",p.merchandise_id)},className:"icon-camera"})):l.a.createElement(c.Fragment,null,l.a.createElement(u.a,{accept:"image/x-png,image/gif,image/jpeg,image/bmp,image/jpg",type:"file",name:"merchandise_image",id:"merchandise_image",className:"gift_image",onChange:function(a){return e.onChangeImage(a,"add","")}}),l.a.createElement("i",{onChange:function(a){return e.onChangeImage(a,"add","")},className:"icon-camera"}))):l.a.createElement(c.Fragment,null,l.a.createElement("a",{onClick:function(){return e.resetFile()}},l.a.createElement("i",{className:N?"icon-delete":"icon-close"})),l.a.createElement("img",{className:"img-cover",src:t}))),l.a.createElement("div",{className:"figure-help-text"},"Please upload image with maximum size of 150 by 150."),l.a.createElement("div",{className:"input-box"},l.a.createElement("div",{className:"mb-3"},l.a.createElement("label",{htmlFor:"MName"},"Name"),l.a.createElement(u.a,{maxLength:50,name:"MName",value:i,onChange:this.handleInputChange}),!n&&l.a.createElement("span",{className:"color-red"},"Please enter valid name.")),l.a.createElement("div",{className:"mb-3"},l.a.createElement("label",{htmlFor:"MValue"},"Value"),l.a.createElement(u.a,{maxLength:10,name:"MValue",value:s,onChange:this.handleInputChange}),l.a.createElement("div",{className:"field-info-text"},"Value contains only number.")),N?l.a.createElement(h.a,{disabled:!r,className:"btn-secondary-outline publish-btn float-right",onClick:function(){return e.updateMerchandise()}},"Update"):l.a.createElement(h.a,{disabled:!r,className:"btn-secondary-outline publish-btn float-right",onClick:this.addMerchandise},"Save"))),l.a.createElement(o.a,{xs:4,className:"text-center"},l.a.createElement("div",{className:"uploaded-logo-view"},t&&l.a.createElement("img",{className:"img-cover",src:t})),l.a.createElement("div",{className:"uploaded-label"},i)))),l.a.createElement(d.a,{className:"added-merchandise-list-wrap"},l.a.createElement(o.a,{xs:12},l.a.createElement("div",{className:"added-merchandise-list"},f.a.map(m,(function(a,t){return l.a.createElement("div",{key:t,className:"merchandise-info-wrap",id:"name"+t},l.a.createElement("div",{className:"merchandise-img-wrap"},l.a.createElement("a",{onClick:function(){return e.editMerchandise(a)}},l.a.createElement("i",{className:"icon-edit"})),l.a.createElement("img",{src:E.wi+E.wh+a.image_name,alt:""})),l.a.createElement("div",{className:"merchandise-related-data"},l.a.createElement("div",{className:"merchandise-label"},a.name),l.a.createElement("div",{className:"amt"},a.price)))})))))))}}]),t}(c.Component);a.default=M}}]);
//# sourceMappingURL=207.621e2efa.chunk.js.map