(this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]=this["webpackJsonp@coreui/coreui-pro-react-admin-template-starter"]||[]).push([[203],{903:function(e,t,a){"use strict";a.r(t);var r=a(11),i=a(12),n=a(15),c=a(14),o=a(0),l=a.n(o),s=a(46),m=a(155),u=a(156),d=a(154),p=a(9),E=a(8),f=a(18),N=function(e){Object(n.a)(a,e);var t=Object(c.a)(a);function a(e){var i;return Object(r.a)(this,a),(i=t.call(this,e)).getPredictionModule=function(){p.a.Rest(E.nk+E.Gc,{}).then((function(e){e.response_code==E.qk?i.setState({ModuleSetting:e.data.allow_open_predictor}):f.notify.show(E.Ri,"error",5e3)})).catch((function(e){f.notify.show(E.Ri,"error",5e3)}))},i.updatePredictionModule=function(){var e=i.state.ModuleSetting,t={status:1==e?0:1};p.a.Rest(E.nk+E.Oc,t).then((function(t){t.response_code==E.qk?(0==e&&i.props.history.push("/prize-open-predictor/category"),f.notify.show(t.global_error,"success",5e3),i.setState({ModuleSetting:1==e?0:1}),p.a.setKeyValueInLocal("ALLOW_OPEN_PREDICTOR",1),setTimeout((function(){window.location.reload()}),1e3)):f.notify.show(E.Ri,"error",5e3)})).catch((function(e){f.notify.show(E.Ri,"error",5e3)}))},i.state={ModuleSetting:""},i}return Object(i.a)(a,[{key:"componentDidMount",value:function(){this.getPredictionModule()}},{key:"render",value:function(){var e=this.state.ModuleSetting;return l.a.createElement(o.Fragment,null,l.a.createElement("div",{className:"prediction-module"},l.a.createElement(m.a,null,l.a.createElement(u.a,{md:12},l.a.createElement("div",{className:"pre-heading text-center"},"Prediction Module Benefits"))),l.a.createElement(m.a,null,l.a.createElement(u.a,{md:12},l.a.createElement("div",{className:"container"},l.a.createElement("ul",{className:"prediction-list"},l.a.createElement("li",{className:"prediction-item float-left"},l.a.createElement("figure",{className:"pre-img-container pr-20"},l.a.createElement("img",{src:s.a.OP_PREDICTION_1,alt:""})),l.a.createElement("div",{className:"pre-info-box text-left"},l.a.createElement("div",{className:"pre-title"},"Generic Questions"),l.a.createElement("div",{className:"pre-sub-title"},"Post general question related to important affairs in the world"))),l.a.createElement("li",{className:"prediction-item float-right"},l.a.createElement("div",{className:"pre-info-box text-right"},l.a.createElement("div",{className:"pre-title"},"Customizable Categories"),l.a.createElement("div",{className:"pre-sub-title"},"You can add custom categories as per your requirement. No limitation")),l.a.createElement("figure",{className:"pre-img-container pl-20"},l.a.createElement("img",{src:s.a.OP_PREDICTION_2,alt:"",className:"img-cover"}))),l.a.createElement("li",{className:"prediction-item float-left"},l.a.createElement("figure",{className:"pre-img-container pr-20"},l.a.createElement("img",{src:s.a.OP_PREDICTION_3,alt:""})),l.a.createElement("div",{className:"pre-info-box text-left"},l.a.createElement("div",{className:"pre-title"},"Store For Users"),l.a.createElement("div",{className:"pre-sub-title"},"Coins can be redeemed in real cash, merchandise, vouchers etc."))))))),l.a.createElement(m.a,null,l.a.createElement(u.a,{md:12},l.a.createElement("div",{className:"pre-btn"},l.a.createElement(d.a,{onClick:this.updatePredictionModule,className:"btn-secondary-outline"},1==e?"Deactivate Prediction":"Activate Prediction Now"))))))}}]),a}(o.Component);t.default=N}}]);
//# sourceMappingURL=203.85d8d8ab.chunk.js.map