(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[198],{880:function(e,a,t){"use strict";t.r(a),t.d(a,"default",(function(){return E}));var n=t(11),r=t(12),l=t(15),s=t(14),o=t(0),i=t.n(o),u=t(155),c=t(156),d=t(154),g=t(499),p=(t(5),t(8)),m=t(9),h=t(107),f=(t(243),t(18)),v=t(10),b=null,E=function(e){Object(l.a)(t,e);var a=Object(s.a)(t);function t(e){var r;return Object(n.a)(this,t),(r=a.call(this,e)).selectedlanguage=function(e){if(!e)return!1;r.setState({current_lang_label:e.label,current_lang:e.value})},r.exportLang=function(e){if(""==e)return!1;var a=m.a.getToken(),t="common/export_language/"+e+"/?"+p.m+"="+a;window.open(p.nk+p.n+t)},r.state={language_list:v.i.getLanguageData()?v.i.getLanguageData():[],current_lang:"",current_lang_label:"",updatedCSV:"",updatedMaster:""},r}return Object(r.a)(t,[{key:"componentDidMount",value:function(){b=this}},{key:"uploadCSV",value:function(){this.setState({isUploadingFlag:!0});var e=new FormData;e.append("file",this.state.updatedCSV);var a=new XMLHttpRequest;a.withCredentials=!1,a.addEventListener("readystatechange",(function(){if(4===this.readyState){var e=JSON.parse(this.responseText);b.setState({isUploadingFlag:!1}),""!=e&&e.response_code===p.qk?f.notify.show("File uploaded","success",5e3):f.notify.show(e.message,"error",5e3)}}));var t=m.a.getToken();a.open("POST",p.nk+p.Ub+this.state.current_lang+"/"+p.m+"="+t),a.send(e)}},{key:"uploadMaster",value:function(){this.setState({isUploadingFlag:!0});var e=new FormData;e.append("file",this.state.updatedMaster);var a=new XMLHttpRequest;a.withCredentials=!1,a.addEventListener("readystatechange",(function(){if(4===this.readyState){var e=JSON.parse(this.responseText);b.setState({isUploadingFlag:!1}),""!=e&&e.response_code===p.qk?f.notify.show("File uploaded","success",5e3):f.notify.show(e.message,"error",5e3)}}));var t=m.a.getToken();a.open("POST",p.nk+p.Vb+this.state.current_lang+"/"+p.m+"="+t),a.send(e)}},{key:"onDropCSV",value:function(e){var a=this;e.preventDefault();var t=new FileReader,n=e.target.files[0];"importLanguage"==e.target.name&&(t.onloadend=function(){a.setState({updatedCSV:n},(function(){this.uploadCSV()}))}),t.readAsDataURL(n)}},{key:"onDropMaster",value:function(e){var a=this;e.preventDefault();var t=new FileReader,n=e.target.files[0];"importMasterLanguage"==e.target.name&&(t.onloadend=function(){a.setState({updatedMaster:n},(function(){this.uploadMaster()}))})}},{key:"render",value:function(){var e=this;return i.a.createElement("div",{className:"animated fadeIn"},i.a.createElement("div",null,i.a.createElement(u.a,null,i.a.createElement(c.a,{xl:"6",sm:"8"},i.a.createElement(h.a,{clearable:!1,className:"sports-seletor",name:"selected_language",placeholder:"Select Language",menuIsOpen:!0,value:this.state.current_lang,options:this.state.language_list,onChange:function(a){return e.selectedlanguage(a)}}))),i.a.createElement("hr",null),i.a.createElement(u.a,null,i.a.createElement(c.a,{xl:"6",sm:"8"},i.a.createElement(d.a,{className:"btn-secondary",onClick:function(){return e.exportLang(e.state.current_lang)}},"Export ",this.state.current_lang_label))),i.a.createElement("hr",null),i.a.createElement(u.a,null,i.a.createElement(c.a,{xl:"6",sm:"8"},i.a.createElement(g.a,{className:"d-none",id:"importLanguage",type:"file",name:"importLanguage",placeholder:"import language",accept:"csv/*",ref:function(a){return e.upload=a},onChange:this.onDropCSV.bind(this)}),i.a.createElement("label",{className:"btn-secondary",htmlFor:"importLanguage"},"Import  ",this.state.current_lang_label))),i.a.createElement("hr",null),i.a.createElement(u.a,null,i.a.createElement(c.a,{xl:"6",sm:"8"},i.a.createElement(g.a,{className:"d-none",id:"importMasterLanguage",type:"file",name:"importMasterLanguage",placeholder:"import master language",ref:function(a){return e.upload=a},onChange:this.onDropMaster.bind(this)}),i.a.createElement("label",{className:"btn-secondary",htmlFor:"importMasterLanguage"},"Upload   ",this.state.current_lang_label," Master FIle to Bucket")))))}}]),t}(o.Component)}}]);
//# sourceMappingURL=198.74e9e0b2.chunk.js.map