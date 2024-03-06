// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.

 
//JS For InFieldLabels

;(function(window, document, $) {
    var isInputSupported = 'placeholder' in document.createElement('input'),
        isTextareaSupported = 'placeholder' in document.createElement('textarea'),
        prototype = $.fn,
        valHooks = $.valHooks,
        hooks,
        placeholder;
 
    if (isInputSupported && isTextareaSupported) {
 
        placeholder = prototype.placeholder = function() {
            return this;
        };
 
        placeholder.input = placeholder.textarea = true;
 
    } else {
 
        placeholder = prototype.placeholder = function() {
            var $this = this;
            $this
                .filter((isInputSupported ? 'textarea' : ':input') + '[placeholder]')
                .not('.placeholder')
                .bind({
                    'focus.placeholder': clearPlaceholder,
                    'blur.placeholder': setPlaceholder
                })
                .data('placeholder-enabled', true)
                .trigger('blur.placeholder');
            return $this;
        };
        placeholder.input = isInputSupported;
        placeholder.textarea = isTextareaSupported;
        hooks = {
            'get': function(element) {
                var $element = $(element);
                return $element.data('placeholder-enabled') && $element.hasClass('placeholder') ? '' : element.value;
            },
            'set': function(element, value) {
                var $element = $(element);
                if (!$element.data('placeholder-enabled')) {
                    return element.value = value;
                }
                if (value == '') {
                    element.value = value;
                    // Issue #56: Setting the placeholder causes problems if the element continues to have focus.
                    if (element != document.activeElement) {
                        // We can't use `triggerHandler` here because of dummy text/password inputs :(
                        setPlaceholder.call(element);
                    }
                } else if ($element.hasClass('placeholder')) {
                    clearPlaceholder.call(element, true, value) || (element.value = value);
                } else {
                    element.value = value;
                }
                // `set` can not return `undefined`; see http://jsapi.info/jquery/1.7.1/val#L2363
                return $element;
            }
        };
 
        isInputSupported || (valHooks.input = hooks);
        isTextareaSupported || (valHooks.textarea = hooks);
        $(function() {
            // Look for forms
            $(document).delegate('form', 'submit.placeholder', function() {
                // Clear the placeholder values so they don't get submitted
                var $inputs = $('.placeholder', this).each(clearPlaceholder);
                setTimeout(function() {
                    $inputs.each(setPlaceholder);
                }, 10);
            });
        });
        // Clear placeholder values upon page reload
        $(window).bind('beforeunload.placeholder', function() {
            $('.placeholder').each(function() {
                this.value = '';
            });
        });
    }
 
    function args(elem) {
        // Return an object of element attributes
        var newAttrs = {},
            rinlinejQuery = /^jQuery\d+$/;
        $.each(elem.attributes, function(i, attr) {
            if (attr.specified && !rinlinejQuery.test(attr.name)) {
                newAttrs[attr.name] = attr.value;
            }
        });
        return newAttrs;
    }
    function clearPlaceholder(event, value) {
        var input = this,
            $input = $(input),
            hadFocus;
        if (input.value == $input.attr('placeholder') && $input.hasClass('placeholder')) {
            hadFocus = input == document.activeElement;
            if ($input.data('placeholder-password')) {
                $input = $input.hide().next().show().attr('id', $input.removeAttr('id').data('placeholder-id'));
                // If `clearPlaceholder` was called from `$.valHooks.input.set`
                if (event === true) {
                    return $input[0].value = value;
                }
                $input.focus();
            } else {
                input.value = '';
                $input.removeClass('placeholder');
            }
            hadFocus && input.select();
        }
    }
 
    function setPlaceholder() {
        var $replacement,
            input = this,
            $input = $(input),
            $origInput = $input,
            id = this.id;
        if (input.value == '') {
            if (input.type == 'password') {
                if (!$input.data('placeholder-textinput')) {
                    try {
                        $replacement = $input.clone().attr({ 'type': 'text' });
                    } catch(e) {
                        $replacement = $('<input>').attr($.extend(args(this), { 'type': 'text' }));
                    }
                    $replacement
                        .removeAttr('name')
                        .data({
                            'placeholder-password': true,
                            'placeholder-id': id
                        })
                        .bind('focus.placeholder', clearPlaceholder);
                    $input
                        .data({
                            'placeholder-textinput': $replacement,
                            'placeholder-id': id
                        })
                        .before($replacement);
                }
                $input = $input.removeAttr('id').hide().prev().attr('id', id).show();
                // Note: `$input[0] != input` now!
            }
            $input.addClass('placeholder');
            $input[0].value = $input.attr('placeholder');
        } else {
            $input.removeClass('placeholder');
        }
    }
}(this, document, jQuery));


$(function(){
    
//function for placeholder
$('input, textarea').placeholder();

 //tipsy js plugin
    try{
	$('[data-tip=tooltip]').tipsy({fade: true, gravity: 's'});
    }
    catch (err){

    }
 //end
 //hack for mac safari and windows safari
	 if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1)
	 {
	  $('input, textarea').placeholder();
	 }
 //end
 
});

/*
 * jScrollPane - v2.0.0beta11 - 2011-07-04
 * http://jscrollpane.kelvinluck.com/
 *
 * Copyright (c) 2010 Kelvin Luck
 * Dual licensed under the MIT and GPL licenses.
 */
 

(function(b,a,c){
b.fn.jScrollPane=function(e){
function d(D,O){
var az,Q=this,
Y,ak,v,am,T,Z,y,q,aA,aF,av,i,I,h,j,aa,U,aq,X,t,A,ar,af,an,G,l,au,ay,x,aw,aI,f,L,aj=true,
P=true,
aH=false,
k=false,
ap=D.clone(false,false).empty(),
ac=b.fn.mwheelIntent?"mwheelIntent.jsp":"mousewheel.jsp";
aI=D.css("paddingTop")+" "+D.css("paddingRight")+" "+D.css("paddingBottom")+" "+D.css("paddingLeft");
f=(parseInt(D.css("paddingLeft"),10)||0)+(parseInt(D.css("paddingRight"),10)||0);
function at(aR){
var aM,aO,aN,aK,aJ,aQ,aP=false,
aL=false;
az=aR;
if(Y===c){
aJ=D.scrollTop();
aQ=D.scrollLeft();
D.css({
overflow:"hidden",
padding:0
});
ak=D.innerWidth()+f;
v=D.innerHeight();
D.width(ak);
Y=b('<div class="jspPane" />').css("padding",aI).append(D.children());
am=b('<div class="jspContainer" />').css({
width:ak+"px",
height:v+"px"
}).append(Y).appendTo(D)
}else{
D.css("width","");
aP=az.stickToBottom&&K();
aL=az.stickToRight&&B();
aK=D.innerWidth()+f!=ak||D.outerHeight()!=v;
if(aK){
ak=D.innerWidth()+f;
v=D.innerHeight();
am.css({
width:ak+"px",
height:v+"px"
})
}
if(!aK&&L==T&&Y.outerHeight()==Z){
D.width(ak);
return
}
L=T;
Y.css("width","");
D.width(ak);
am.find(">.jspVerticalBar,>.jspHorizontalBar").remove().end()
}
Y.css("overflow","auto");
if(aR.contentWidth){
T=aR.contentWidth
}else{
T=Y[0].scrollWidth
}
Z=Y[0].scrollHeight;
Y.css("overflow","");
y=T/ak;
q=Z/v;
aA=q>1;
aF=y>1;
if(!(aF||aA)){
D.removeClass("jspScrollable");
Y.css({
top:0,
width:am.width()-f
});
n();
E();
R();
w();
ai()
}else{
D.addClass("jspScrollable");
aM=az.maintainPosition&&(I||aa);
if(aM){
aO=aD();
aN=aB()
}
aG();
z();
F();
if(aM){
N(aL?(T-ak):aO,false);
M(aP?(Z-v):aN,false)
}
J();
ag();
ao();
if(az.enableKeyboardNavigation){
S()
}
if(az.clickOnTrack){
p()
}
C();
if(az.hijackInternalLinks){
m()
}
}
if(az.autoReinitialise&&!aw){
aw=setInterval(function(){
at(az)
},az.autoReinitialiseDelay)
}else{
if(!az.autoReinitialise&&aw){
clearInterval(aw)
}
}
aJ&&D.scrollTop(0)&&M(aJ,false);
aQ&&D.scrollLeft(0)&&N(aQ,false);
D.trigger("jsp-initialised",[aF||aA])
}
function aG(){
if(aA){
am.append(b('<div class="jspVerticalBar" />').append(b('<div class="jspCap jspCapTop" />'),b('<div class="jspTrack" />').append(b('<div class="jspDrag" title="Scroll down for more results" />').append(b('<div class="jspDragTop" />'),b('<div class="jspDragBottom" />'))),b('<div class="jspCap jspCapBottom" />')));
U=am.find(">.jspVerticalBar");
aq=U.find(">.jspTrack");
av=aq.find(">.jspDrag");
if(az.showArrows){
ar=b('<a class="jspArrow jspArrowUp" />').bind("mousedown.jsp",aE(0,-1)).bind("click.jsp",aC);
af=b('<a class="jspArrow jspArrowDown" />').bind("mousedown.jsp",aE(0,1)).bind("click.jsp",aC);
if(az.arrowScrollOnHover){
ar.bind("mouseover.jsp",aE(0,-1,ar));
af.bind("mouseover.jsp",aE(0,1,af))
}
al(aq,az.verticalArrowPositions,ar,af)
}
t=v;
am.find(">.jspVerticalBar>.jspCap:visible,>.jspVerticalBar>.jspArrow").each(function(){
t-=b(this).outerHeight()
});
av.hover(function(){
av.addClass("jspHover")
},function(){
av.removeClass("jspHover")
}).bind("mousedown.jsp",function(aJ){
b("html").bind("dragstart.jsp selectstart.jsp",aC);
av.addClass("jspActive");
var s=aJ.pageY-av.position().top;
b("html").bind("mousemove.jsp",function(aK){
V(aK.pageY-s,false)
}).bind("mouseup.jsp mouseleave.jsp",ax);
return false
});
o()
}
}
function o(){
aq.height(t+"px");
I=0;
X=az.verticalGutter+aq.outerWidth();
Y.width(ak-X-f);
try{
if(U.position().left===0){
Y.css("margin-left",X+"px")
}
}catch(s){}
}
function z(){
if(aF){
am.append(b('<div class="jspHorizontalBar" />').append(b('<div class="jspCap jspCapLeft" />'),b('<div class="jspTrack" />').append(b('<div class="jspDrag" />').append(b('<div class="jspDragLeft" />'),b('<div class="jspDragRight" />'))),b('<div class="jspCap jspCapRight" />')));
an=am.find(">.jspHorizontalBar");
G=an.find(">.jspTrack");
h=G.find(">.jspDrag");
if(az.showArrows){
ay=b('<a class="jspArrow jspArrowLeft" />').bind("mousedown.jsp",aE(-1,0)).bind("click.jsp",aC);
x=b('<a class="jspArrow jspArrowRight" />').bind("mousedown.jsp",aE(1,0)).bind("click.jsp",aC);
if(az.arrowScrollOnHover){
ay.bind("mouseover.jsp",aE(-1,0,ay));
x.bind("mouseover.jsp",aE(1,0,x))
}
al(G,az.horizontalArrowPositions,ay,x)
}
h.hover(function(){
h.addClass("jspHover")
},function(){
h.removeClass("jspHover")
}).bind("mousedown.jsp",function(aJ){
b("html").bind("dragstart.jsp selectstart.jsp",aC);
h.addClass("jspActive");
var s=aJ.pageX-h.position().left;
b("html").bind("mousemove.jsp",function(aK){
W(aK.pageX-s,false)
}).bind("mouseup.jsp mouseleave.jsp",ax);
return false
});
l=am.innerWidth();
ah()
}
}
function ah(){
am.find(">.jspHorizontalBar>.jspCap:visible,>.jspHorizontalBar>.jspArrow").each(function(){
l-=b(this).outerWidth()
});
G.width(l+"px");
aa=0
}
function F(){
if(aF&&aA){
var aJ=G.outerHeight(),
s=aq.outerWidth();
t-=aJ;
b(an).find(">.jspCap:visible,>.jspArrow").each(function(){
l+=b(this).outerWidth()
});
l-=s;
v-=s;
ak-=aJ;
G.parent().append(b('<div class="jspCorner" />').css("width",aJ+"px"));
o();
ah()
}
if(aF){
Y.width((am.outerWidth()-f)+"px")
}
Z=Y.outerHeight();
q=Z/v;
if(aF){
au=Math.ceil(1/y*l);
if(au>az.horizontalDragMaxWidth){
au=az.horizontalDragMaxWidth
}else{
if(au<az.horizontalDragMinWidth){
au=az.horizontalDragMinWidth
}
}
h.width(au+"px");
j=l-au;
ae(aa)
}
if(aA){
A=Math.ceil(1/q*t);
if(A>az.verticalDragMaxHeight){
A=az.verticalDragMaxHeight
}else{
if(A<az.verticalDragMinHeight){
A=az.verticalDragMinHeight
}
}
av.height(A-2+"px");
i=t-A;
ad(I)
}
}
function al(aK,aM,aJ,s){
var aO="before",
aL="after",
aN;
if(aM=="os"){
aM=/Mac/.test(navigator.platform)?"after":"split"
}
if(aM==aO){
aL=aM
}else{
if(aM==aL){
aO=aM;
aN=aJ;
aJ=s;
s=aN
}
}
aK[aO](aJ)[aL](s)
}
function aE(aJ,s,aK){
return function(){
H(aJ,s,this,aK);
this.blur();
return false
}
}
function H(aM,aL,aP,aO){
aP=b(aP).addClass("jspActive");
var aN,aK,aJ=true,
s=function(){
if(aM!==0){
Q.scrollByX(aM*az.arrowButtonSpeed)
}
if(aL!==0){
Q.scrollByY(aL*az.arrowButtonSpeed)
}
aK=setTimeout(s,aJ?az.initialDelay:az.arrowRepeatFreq);
aJ=false
};
s();
aN=aO?"mouseout.jsp":"mouseup.jsp";
aO=aO||b("html");
aO.bind(aN,function(){
aP.removeClass("jspActive");
aK&&clearTimeout(aK);
aK=null;
aO.unbind(aN)
})
}
function p(){
w();
if(aA){
aq.bind("mousedown.jsp",function(aO){
if(aO.originalTarget===c||aO.originalTarget==aO.currentTarget){
var aM=b(this),
aP=aM.offset(),
aN=aO.pageY-aP.top-I,
aK,aJ=true,
s=function(){
var aS=aM.offset(),
aT=aO.pageY-aS.top-A/2,
aQ=v*az.scrollPagePercent,
aR=i*aQ/(Z-v);
if(aN<0){
if(I-aR>aT){
Q.scrollByY(-aQ)
}else{
V(aT)
}
}else{
if(aN>0){
if(I+aR<aT){
Q.scrollByY(aQ)
}else{
V(aT)
}
}else{
aL();
return
}
}
aK=setTimeout(s,aJ?az.initialDelay:az.trackClickRepeatFreq);
aJ=false
},
aL=function(){
aK&&clearTimeout(aK);
aK=null;
b(document).unbind("mouseup.jsp",aL)
};
s();
b(document).bind("mouseup.jsp",aL);
return false
}
})
}
if(aF){
G.bind("mousedown.jsp",function(aO){
if(aO.originalTarget===c||aO.originalTarget==aO.currentTarget){
var aM=b(this),
aP=aM.offset(),
aN=aO.pageX-aP.left-aa,
aK,aJ=true,
s=function(){
var aS=aM.offset(),
aT=aO.pageX-aS.left-au/2,
aQ=ak*az.scrollPagePercent,
aR=j*aQ/(T-ak);
if(aN<0){
if(aa-aR>aT){
Q.scrollByX(-aQ)
}else{
W(aT)
}
}else{
if(aN>0){
if(aa+aR<aT){
Q.scrollByX(aQ)
}else{
W(aT)
}
}else{
aL();
return
}
}
aK=setTimeout(s,aJ?az.initialDelay:az.trackClickRepeatFreq);
aJ=false
},
aL=function(){
aK&&clearTimeout(aK);
aK=null;
b(document).unbind("mouseup.jsp",aL)
};
s();
b(document).bind("mouseup.jsp",aL);
return false
}
})
}
}
function w(){
if(G){
G.unbind("mousedown.jsp")
}
if(aq){
aq.unbind("mousedown.jsp")
}
}
function ax(){
b("html").unbind("dragstart.jsp selectstart.jsp mousemove.jsp mouseup.jsp mouseleave.jsp");
if(av){
av.removeClass("jspActive")
}
if(h){
h.removeClass("jspActive")
}
}
function V(s,aJ){
if(!aA){
return
}
if(s<0){
s=0
}else{
if(s>i){
s=i
}
}
if(aJ===c){
aJ=az.animateScroll
}
if(aJ){
Q.animate(av,"top",s,ad)
}else{
av.css("top",s);
ad(s)
}
}
function ad(aJ){
if(aJ===c){
aJ=av.position().top
}
am.scrollTop(0);
I=aJ;
var aM=I===0,
aK=I==i,
aL=aJ/i,
s=-aL*(Z-v);
if(aj!=aM||aH!=aK){
aj=aM;
aH=aK;
D.trigger("jsp-arrow-change",[aj,aH,P,k])
}
u(aM,aK);
Y.css("top",s);
D.trigger("jsp-scroll-y",[-s,aM,aK]).trigger("scroll")
}
function W(aJ,s){
if(!aF){
return
}
if(aJ<0){
aJ=0
}else{
if(aJ>j){
aJ=j
}
}
if(s===c){
s=az.animateScroll
}
if(s){
Q.animate(h,"left",aJ,ae)
}else{
h.css("left",aJ);
ae(aJ)
}
}
function ae(aJ){
if(aJ===c){
aJ=h.position().left
}
am.scrollTop(0);
aa=aJ;
var aM=aa===0,
aL=aa==j,
aK=aJ/j,
s=-aK*(T-ak);
if(P!=aM||k!=aL){
P=aM;
k=aL;
D.trigger("jsp-arrow-change",[aj,aH,P,k])
}
r(aM,aL);
Y.css("left",s);
D.trigger("jsp-scroll-x",[-s,aM,aL]).trigger("scroll")
}
function u(aJ,s){
if(az.showArrows){
ar[aJ?"addClass":"removeClass"]("jspDisabled");
af[s?"addClass":"removeClass"]("jspDisabled")
}
}
function r(aJ,s){
if(az.showArrows){
ay[aJ?"addClass":"removeClass"]("jspDisabled");
x[s?"addClass":"removeClass"]("jspDisabled")
}
}
function M(s,aJ){
var aK=s/(Z-v);
V(aK*i,aJ)
}
function N(aJ,s){
var aK=aJ/(T-ak);
W(aK*j,s)
}
function ab(aW,aR,aK){
var aO,aL,aM,s=0,
aV=0,
aJ,aQ,aP,aT,aS,aU;
try{
aO=b(aW)
}catch(aN){
return
}
aL=aO.outerHeight();
aM=aO.outerWidth();
am.scrollTop(0);
am.scrollLeft(0);
while(!aO.is(".jspPane")){
s+=aO.position().top;
aV+=aO.position().left;
aO=aO.offsetParent();
if(/^body|html$/i.test(aO[0].nodeName)){
return
}
}
aJ=aB();
aP=aJ+v;
if(s<aJ||aR){
aS=s-az.verticalGutter
}else{
if(s+aL>aP){
aS=s-v+aL+az.verticalGutter
}
}
if(aS){
M(aS,aK)
}
aQ=aD();
aT=aQ+ak;
if(aV<aQ||aR){
aU=aV-az.horizontalGutter
}else{
if(aV+aM>aT){
aU=aV-ak+aM+az.horizontalGutter
}
}
if(aU){
N(aU,aK)
}
}
function aD(){
return-Y.position().left
}
function aB(){
return-Y.position().top
}
function K(){
var s=Z-v;
return(s>20)&&(s-aB()<10)
}
function B(){
var s=T-ak;
return(s>20)&&(s-aD()<10)
}
function ag(){
am.unbind(ac).bind(ac,function(aM,aN,aL,aJ){
var aK=aa,
s=I;
Q.scrollBy(aL*az.mouseWheelSpeed,-aJ*az.mouseWheelSpeed,false);
return aK==aa&&s==I
})
}
function n(){
am.unbind(ac)
}
function aC(){
return false
}
function J(){
Y.find(":input,a").unbind("focus.jsp").bind("focus.jsp",function(s){
ab(s.target,false)
})
}
function E(){
Y.find(":input,a").unbind("focus.jsp")
}
function S(){
var s,aJ,aL=[];
aF&&aL.push(an[0]);
aA&&aL.push(U[0]);
Y.focus(function(){
D.focus()
});
D.attr("tabindex",0).unbind("keydown.jsp keypress.jsp").bind("keydown.jsp",function(aO){
if(aO.target!==this&&!(aL.length&&b(aO.target).closest(aL).length)){
return
}
var aN=aa,
aM=I;
switch(aO.keyCode){
case 40:
case 38:
case 34:
case 32:
case 33:
case 39:
case 37:
s=aO.keyCode;
aK();
break;
case 35:
M(Z-v);
s=null;
break;
case 36:
M(0);
s=null;
break
}
aJ=aO.keyCode==s&&aN!=aa||aM!=I;
return!aJ
}).bind("keypress.jsp",function(aM){
if(aM.keyCode==s){
aK()
}
return!aJ
});
if(az.hideFocus){
D.css("outline","none");
if("hideFocus"in am[0]){
D.attr("hideFocus",true)
}
}else{
D.css("outline","");
if("hideFocus"in am[0]){
D.attr("hideFocus",false)
}
}
function aK(){
var aN=aa,
aM=I;
switch(s){
case 40:
Q.scrollByY(az.keyboardSpeed,false);
break;
case 38:
Q.scrollByY(-az.keyboardSpeed,false);
break;
case 34:
case 32:
Q.scrollByY(v*az.scrollPagePercent,false);
break;
case 33:
Q.scrollByY(-v*az.scrollPagePercent,false);
break;
case 39:
Q.scrollByX(az.keyboardSpeed,false);
break;
case 37:
Q.scrollByX(-az.keyboardSpeed,false);
break
}
aJ=aN!=aa||aM!=I;
return aJ
}
}
function R(){
D.attr("tabindex","-1").removeAttr("tabindex").unbind("keydown.jsp keypress.jsp")
}
function C(){
if(location.hash&&location.hash.length>1){
var aL,aJ,aK=escape(location.hash);
try{
aL=b(aK)
}catch(s){
return
}
if(aL.length&&Y.find(aK)){
if(am.scrollTop()===0){
aJ=setInterval(function(){
if(am.scrollTop()>0){
ab(aK,true);
b(document).scrollTop(am.position().top);
clearInterval(aJ)
}
},50)
}else{
ab(aK,true);
b(document).scrollTop(am.position().top)
}
}
}
}
function ai(){
b("a.jspHijack").unbind("click.jsp-hijack").removeClass("jspHijack")
}
function m(){
ai();
b("a[href^=#]").addClass("jspHijack").bind("click.jsp-hijack",function(){
var s=this.href.split("#"),
aJ;
if(s.length>1){
aJ=s[1];
if(aJ.length>0&&Y.find("#"+aJ).length>0){
ab("#"+aJ,true);
return false
}
}
})
}
function ao(){
var aK,aJ,aM,aL,aN,s=false;
am.unbind("touchstart.jsp touchmove.jsp touchend.jsp click.jsp-touchclick").bind("touchstart.jsp",function(aO){
var aP=aO.originalEvent.touches[0];
aK=aD();
aJ=aB();
aM=aP.pageX;
aL=aP.pageY;
aN=false;
s=true
}).bind("touchmove.jsp",function(aR){
if(!s){
return
}
var aQ=aR.originalEvent.touches[0],
aP=aa,
aO=I;
Q.scrollTo(aK+aM-aQ.pageX,aJ+aL-aQ.pageY);
aN=aN||Math.abs(aM-aQ.pageX)>5||Math.abs(aL-aQ.pageY)>5;
return aP==aa&&aO==I
}).bind("touchend.jsp",function(aO){
s=false
}).bind("click.jsp-touchclick",function(aO){
if(aN){
aN=false;
return false
}
})
}
function g(){
var s=aB(),
aJ=aD();
D.removeClass("jspScrollable").unbind(".jsp");
D.replaceWith(ap.append(Y.children()));
ap.scrollTop(s);
ap.scrollLeft(aJ)
}
b.extend(Q,{
reinitialise:function(aJ){
aJ=b.extend({},az,aJ);
at(aJ)
},
scrollToElement:function(aK,aJ,s){
ab(aK,aJ,s)
},
scrollTo:function(aK,s,aJ){
N(aK,aJ);
M(s,aJ)
},
scrollToX:function(aJ,s){
N(aJ,s)
},
scrollToY:function(s,aJ){
M(s,aJ)
},
scrollToPercentX:function(aJ,s){
N(aJ*(T-ak),s)
},
scrollToPercentY:function(aJ,s){
M(aJ*(Z-v),s)
},
scrollBy:function(aJ,s,aK){
Q.scrollByX(aJ,aK);
Q.scrollByY(s,aK)
},
scrollByX:function(s,aK){
var aJ=aD()+Math[s<0?"floor":"ceil"](s),
aL=aJ/(T-ak);
W(aL*j,aK)
},
scrollByY:function(s,aK){
var aJ=aB()+Math[s<0?"floor":"ceil"](s),
aL=aJ/(Z-v);
V(aL*i,aK)
},
positionDragX:function(s,aJ){
W(s,aJ)
},
positionDragY:function(aJ,s){
V(aJ,s)
},
animate:function(aJ,aM,s,aL){
var aK={};
aK[aM]=s;
aJ.animate(aK,{
duration:az.animateDuration,
easing:az.animateEase,
queue:false,
step:aL
})
},
getContentPositionX:function(){
return aD()
},
getContentPositionY:function(){
return aB()
},
getContentWidth:function(){
return T
},
getContentHeight:function(){
return Z
},
getPercentScrolledX:function(){
return aD()/(T-ak)
},
getPercentScrolledY:function(){
return aB()/(Z-v)
},
getIsScrollableH:function(){
return aF
},
getIsScrollableV:function(){
return aA
},
getContentPane:function(){
return Y
},
scrollToBottom:function(s){
V(i,s)
},
hijackInternalLinks:function(){
m()
},
destroy:function(){
g()
}
});
at(O)
}
e=b.extend({},b.fn.jScrollPane.defaults,e);
b.each(["mouseWheelSpeed","arrowButtonSpeed","trackClickSpeed","keyboardSpeed"],function(){
e[this]=e[this]||e.speed
});
return this.each(function(){
var f=b(this),
g=f.data("jsp");
if(g){
g.reinitialise(e)
}else{
g=new d(f,e);
f.data("jsp",g)
}
})
};
b.fn.jScrollPane.defaults={
showArrows:false,
maintainPosition:true,
stickToBottom:false,
stickToRight:false,
clickOnTrack:true,
autoReinitialise:false,
autoReinitialiseDelay:500,
verticalDragMinHeight:0,
verticalDragMaxHeight:99999,
horizontalDragMinWidth:0,
horizontalDragMaxWidth:99999,
contentWidth:c,
animateScroll:true,
animateDuration:300,
animateEase:"linear",
hijackInternalLinks:false,
verticalGutter:4,
horizontalGutter:4,
mouseWheelSpeed:0,
arrowButtonSpeed:0,
arrowRepeatFreq:50,
arrowScrollOnHover:false,
trackClickSpeed:0,
trackClickRepeatFreq:70,
verticalArrowPositions:"split",
horizontalArrowPositions:"split",
enableKeyboardNavigation:true,
hideFocus:false,
keyboardSpeed:0,
initialDelay:300,
speed:30,
scrollPagePercent:0.8
}
})(jQuery,this);

/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
  Licensed under the MIT License (LICENSE.txt).
 
  Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
  Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
  Thanks to: Seamus Leahy for adding deltaX and deltaY
 
  Version: 3.0.6
  
  Requires: 1.2.2+
 */
(function($){
var types=['DOMMouseScroll','mousewheel'];
if($.event.fixHooks){
for(var i=types.length;i;){
$.event.fixHooks[types[--i]]=$.event.mouseHooks;
}
}
$.event.special.mousewheel={
setup:function(){
if(this.addEventListener){
for(var i=types.length;i;){
this.addEventListener(types[--i],handler,false);
}
}else{
this.onmousewheel=handler;
}
},
teardown:function(){
if(this.removeEventListener){
for(var i=types.length;i;){
this.removeEventListener(types[--i],handler,false);
}
}else{
this.onmousewheel=null;
}
}
};
$.fn.extend({
mousewheel:function(fn){
return fn?this.bind("mousewheel",fn):this.trigger("mousewheel");
},
unmousewheel:function(fn){
return this.unbind("mousewheel",fn);
}
});
function handler(event){
var orgEvent=event||window.event,args=[].slice.call(arguments,1),delta=0,returnValue=true,deltaX=0,deltaY=0;
event=$.event.fix(orgEvent);
event.type="mousewheel";
if(orgEvent.wheelDelta){delta=orgEvent.wheelDelta/120;}
if(orgEvent.detail){delta=-orgEvent.detail/3;}
deltaY=delta;
if(orgEvent.axis!==undefined&&orgEvent.axis===orgEvent.HORIZONTAL_AXIS){
deltaY=0;
deltaX=-1*delta;
}
if(orgEvent.wheelDeltaY!==undefined){deltaY=orgEvent.wheelDeltaY/120;}
if(orgEvent.wheelDeltaX!==undefined){deltaX=-1*orgEvent.wheelDeltaX/120;}
args.unshift(event,delta,deltaX,deltaY);
return($.event.dispatch||$.event.handle).apply(this,args);
}
})(jQuery);

function dscroll(){
if($('.Scrollfn').length>0){
	$('.Scrollfn').jScrollPane();
	$(window).load(function(){
	var settings={
	autoReinitialise:true
};
var pane=$('.Scrollfn');
	pane.jScrollPane(settings);
	var contentPane=pane.data('jsp').getContentPane();
});
$(window).resize(function(){
	var settings={
	autoReinitialise:true
};
var pane=$('.Scrollfn');
	pane.jScrollPane(settings);
	var contentPane=pane.data('jsp').getContentPane();
});
}
}
$(function(){
dscroll();
});
 
// JavaScript Document
// tipsy, facebook style tooltips for jquery
(function(a){function c(c,d){this.$element=a(c);this.options=d;this.enabled=true;b(this.$element)}function b(a){if(a.attr("title")||typeof a.attr("original-title")!="string"){a.attr("original-title",a.attr("title")||"").removeAttr("title")}}c.prototype={show:function(){var b=this.getTitle();if(b&&this.enabled){var c=this.tip();c.find(".tipsy-inner")[this.options.html?"html":"text"](b);c[0].className="tipsy";c.remove().css({top:0,left:0,visibility:"hidden",display:"block"}).appendTo(document.body);var d=a.extend({},this.$element.offset(),{width:this.$element[0].offsetWidth,height:this.$element[0].offsetHeight});var e=c[0].offsetWidth,f=c[0].offsetHeight;var g=typeof this.options.gravity=="function"?this.options.gravity.call(this.$element[0]):this.options.gravity;var h;switch(g.charAt(0)){case"n":h={top:d.top+d.height+this.options.offset,left:d.left+d.width/2-e/2};break;case"s":h={top:d.top-f-this.options.offset,left:d.left+d.width/2-e/2};break;case"e":h={top:d.top+d.height/2-f/2,left:d.left-e-this.options.offset};break;case"w":h={top:d.top+d.height/2-f/2,left:d.left+d.width+this.options.offset};break}if(g.length==2){if(g.charAt(1)=="w"){h.left=d.left+d.width/2-15}else{h.left=d.left+d.width/2-e+15}}c.css(h).addClass("tipsy-"+g);if(this.options.fade){c.stop().css({opacity:0,display:"block",visibility:"visible"}).animate({opacity:this.options.opacity})}else{c.css({visibility:"visible",opacity:this.options.opacity})}}},hide:function(){if(this.options.fade){this.tip().stop().fadeOut(function(){a(this).remove()})}else{this.tip().remove()}},getTitle:function(){var a,c=this.$element,d=this.options;b(c);var a,d=this.options;if(typeof d.title=="string"){a=c.attr(d.title=="title"?"original-title":d.title)}else if(typeof d.title=="function"){a=d.title.call(c[0])}a=(""+a).replace(/(^\s*|\s*$)/,"");return a||d.fallback},tip:function(){if(!this.$tip){this.$tip=a('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"/></div>')}return this.$tip},validate:function(){if(!this.$element[0].parentNode){this.hide();this.$element=null;this.options=null}},enable:function(){this.enabled=true},disable:function(){this.enabled=false},toggleEnabled:function(){this.enabled=!this.enabled}};a.fn.tipsy=function(b){function f(){var a=d(this);a.hoverState="out";if(b.delayOut==0){a.hide()}else{setTimeout(function(){if(a.hoverState=="out")a.hide()},b.delayOut)}}function e(){var a=d(this);a.hoverState="in";if(b.delayIn==0){a.show()}else{setTimeout(function(){if(a.hoverState=="in")a.show()},b.delayIn)}}function d(d){var e=a.data(d,"tipsy");if(!e){e=new c(d,a.fn.tipsy.elementOptions(d,b));a.data(d,"tipsy",e)}return e}if(b===true){return this.data("tipsy")}else if(typeof b=="string"){return this.data("tipsy")[b]()}b=a.extend({},a.fn.tipsy.defaults,b);if(!b.live)this.each(function(){d(this)});if(b.trigger!="manual"){var g=b.live?"live":"bind",h=b.trigger=="hover"?"mouseenter":"focus",i=b.trigger=="hover"?"mouseleave":"blur";this[g](h,e)[g](i,f)}return this};a.fn.tipsy.defaults={delayIn:0,delayOut:0,fade:false,fallback:"",gravity:"n",html:false,live:true,offset:0,opacity:.8,title:"title",trigger:"hover"};a.fn.tipsy.elementOptions=function(b,c){return a.metadata?a.extend({},c,a(b).metadata()):c};a.fn.tipsy.autoNS=function(){return a(this).offset().top>a(document).scrollTop()+a(window).height()/2?"s":"n"};a.fn.tipsy.autoWE=function(){return a(this).offset().left>a(document).scrollLeft()+a(window).width()/2?"e":"w"};a.fn.tipsy.autoBounds=function(b,c){return function(){var d={ns:c[0],ew:c.length>1?c[1]:false};bound_top=a(document).scrollTop()+b;bound_left=a(document).scrollLeft()+b;if(a(this).offset().top<bound_top){d.ns="n"}if(a(this).offset().left<bound_left){d.ew="w"}if(a(window).width()+a(document).scrollLeft()-a(this).offset().left<b){d.ew="e"}if(a(window).height()+a(document).scrollTop()-a(this).offset().top<b){d.ns="s"}return d.ns+(d.ew?d.ew:"")}}})(jQuery)
//end

// popup div js function
//start popup div js
var compDiv;
var arrPageScroll;
var arrPageSizes=new Array();

	
function openPopDiv(divId)
{
	if(document.getElementById('fade'))
	{
		$('#fade').remove();
	}
	$('<div id="fade"></div>').appendTo($('body'));
	$('#fade').click(function(){closePopDiv(divId);});
	compDiv=divId;
	centerPopup(divId);
	loadPopup(divId);
}
function closePopDiv(divId)
{
 	compDiv=divId;
 	disablePopup(divId);
 }
function loadPopup(popDiv)
{
    $("#fade").fadeIn(200);
    $("#"+popDiv).addClass('bounceInDown').fadeIn(200);
	 setTimeout(function(){
		 $("#"+popDiv).removeClass('bounceInDown');
 	 },1100);
}
function disablePopup(popDiv)

{	
 	setTimeout(function(){
    	$("#"+popDiv).fadeOut(200);
	},200);
	
 	$("#fade").fadeOut(250);
}
function centerPopup(popDiv)
{
    var windowWidth=$(document).width();
    var windowHeight=$(document).height();
    var popupHeight=$("#"+popDiv).height();
    var popupWidth=$("#"+popDiv).width();
    arrPageScroll=___getPageScroll();
    arrPageSizes=___getPageSize();
    $("#"+popDiv).css({"position":"absolute","top":(arrPageSizes[3]/ 2 - $("#" + compDiv).height() /2+getScrollTop())+"px","left":(windowWidth/2)-(popupWidth/2),"z-Index":99999});
	ems = parseInt($("#"+popDiv).css("top"));
	if (ems < 0){
		$("#"+popDiv).css({"position":"absolute","top":10});
	}
	//alert($("#"+popDiv).css("position"));
	
  	$("#fade").css({"height":'100%',opacity:0.60,"width":'100%',"backgroundColor":"#000","position":"fixed", "z-index":"999", left:0, top:0});
 
}
$(window).resize(function(){
	
	$('#fade').width($(window).width()).height($(document).height());
});
$(document).ready(function()
{
    $("#fade").click(function(){closePopDiv(compDiv)});
	$(document).keydown(function (e) {  
		 if (e.keyCode == 27) {  
			closePopDiv(compDiv);  
		 }  
	 });
  //$(window).scroll(function(){$("#"+compDiv).stop().animate({"top":(arrPageSizes[3]/ 2 - $("#" + compDiv).height() /2+getScrollTop())+"px",opacity:1.0},500)})
});
function ___getPageSize(){var xScroll,yScroll;if(window.innerHeight&&window.scrollMaxY){xScroll=window.innerWidth+window.scrollMaxX;yScroll=window.innerHeight+window.scrollMaxY}else if(document.body.scrollHeight>document.body.offsetHeight){xScroll=document.body.scrollWidth;yScroll=document.body.scrollHeight}else{xScroll=document.body.offsetWidth;yScroll=document.body.offsetHeight}var windowWidth,windowHeight;if(self.innerHeight){if(document.documentElement.clientWidth){windowWidth=document.documentElement.clientWidth}else{windowWidth=self.innerWidth}windowHeight=self.innerHeight}else if(document.documentElement&&document.documentElement.clientHeight){windowWidth=document.documentElement.clientWidth;windowHeight=document.documentElement.clientHeight}else if(document.body){windowWidth=document.body.clientWidth;windowHeight=document.body.clientHeight}if(yScroll<windowHeight){pageHeight=windowHeight}else{pageHeight=yScroll}if(xScroll<windowWidth){pageWidth=xScroll}else{pageWidth=windowWidth}arrayPageSize=new Array(pageWidth,pageHeight,windowWidth,windowHeight);return arrayPageSize};
function ___getPageScroll(){var xScroll,yScroll;if(self.pageYOffset){yScroll=self.pageYOffset;xScroll=self.pageXOffset}else if(document.documentElement&&document.documentElement.scrollTop){yScroll=document.documentElement.scrollTop;xScroll=document.documentElement.scrollLeft}else if(document.body){yScroll=document.body.scrollTop;xScroll=document.body.scrollLeft}arrayPageScroll=new Array(xScroll,yScroll);return arrayPageScroll};
function getScrollTop()
{
    var ScrollTop=document.body.scrollTop;
    if(ScrollTop==0)
    {
        if(window.pageYOffset)
        ScrollTop=window.pageYOffset;
        else 
        ScrollTop=(document.body.parentElement)?document.body.parentElement.scrollTop:0;
     }
        return ScrollTop;
}
//end



/*! Backstretch - v2.0.3 - 2012-11-30
* http://srobbin.com/jquery-plugins/backstretch/
* Copyright (c) 2012 Scott Robbin; Licensed MIT */
(function(e,t,n){"use strict";e.fn.backstretch=function(r,s){return(r===n||r.length===0)&&e.error("No images were supplied for Backstretch"),e(t).scrollTop()===0&&t.scrollTo(0,0),this.each(function(){var t=e(this),n=t.data("backstretch");n&&(s=e.extend(n.options,s),n.destroy(!0)),n=new i(this,r,s),t.data("backstretch",n)})},e.backstretch=function(t,n){return e("body").backstretch(t,n).data("backstretch")},e.expr[":"].backstretch=function(t){return e(t).data("backstretch")!==n},e.fn.backstretch.defaults={centeredX:!0,centeredY:!0,duration:5e3,fade:0};var r={wrap:{left:0,top:0,overflow:"hidden",margin:0,padding:0,height:"100%",width:"100%",zIndex:-999999},img:{position:"absolute",display:"none",margin:0,padding:0,border:"none",width:"auto",height:"auto",maxWidth:"none",zIndex:-999999}},i=function(n,i,o){this.options=e.extend({},e.fn.backstretch.defaults,o||{}),this.images=e.isArray(i)?i:[i],e.each(this.images,function(){e("<img />")[0].src=this}),this.isBody=n===document.body,this.$container=e(n),this.$wrap=e('<div class="backstretch visible-desktop"></div>').css(r.wrap).appendTo(this.$container),this.$root=this.isBody?s?e(t):e(document):this.$container;if(!this.isBody){var u=this.$container.css("position"),a=this.$container.css("zIndex");this.$container.css({position:u==="static"?"relative":u,zIndex:a==="auto"?0:a,background:"none"}),this.$wrap.css({zIndex:-999998})}this.$wrap.css({position:this.isBody&&s?"fixed":"absolute"}),this.index=0,this.show(this.index),e(t).on("resize.backstretch",e.proxy(this.resize,this)).on("orientationchange.backstretch",e.proxy(function(){this.isBody&&t.pageYOffset===0&&(t.scrollTo(0,1),this.resize())},this))};i.prototype={resize:function(){try{var e={left:0,top:0},n=this.isBody?this.$root.width():this.$root.innerWidth(),r=n,i=this.isBody?t.innerHeight?t.innerHeight:this.$root.height():this.$root.innerHeight(),s=r/this.$img.data("ratio"),o;s>=i?(o=(s-i)/2,this.options.centeredY&&(e.top="-"+o+"px")):(s=i,r=s*this.$img.data("ratio"),o=(r-n)/2,this.options.centeredX&&(e.left="-"+o+"px")),this.$wrap.css({width:n,height:i}).find("img:not(.deleteable)").css({width:r,height:s}).css(e)}catch(u){}return this},show:function(t){if(Math.abs(t)>this.images.length-1)return;this.index=t;var n=this,i=n.$wrap.find("img").addClass("deleteable"),s=e.Event("backstretch.show",{relatedTarget:n.$container[0]});return clearInterval(n.interval),n.$img=e("<img />").css(r.img).bind("load",function(t){var r=this.width||e(t.target).width(),o=this.height||e(t.target).height();e(this).data("ratio",r/o),e(this).fadeIn(n.options.speed||n.options.fade,function(){i.remove(),n.paused||n.cycle(),n.$container.trigger(s,n)}),n.resize()}).appendTo(n.$wrap),n.$img.attr("src",n.images[t]),n},next:function(){return this.show(this.index<this.images.length-1?this.index+1:0)},prev:function(){return this.show(this.index===0?this.images.length-1:this.index-1)},pause:function(){return this.paused=!0,this},resume:function(){return this.paused=!1,this.next(),this},cycle:function(){return this.images.length>1&&(clearInterval(this.interval),this.interval=setInterval(e.proxy(function(){this.paused||this.next()},this),this.options.duration)),this},destroy:function(n){e(t).off("resize.backstretch orientationchange.backstretch"),clearInterval(this.interval),n||this.$wrap.remove(),this.$container.removeData("backstretch")}};var s=function(){var e=navigator.userAgent,n=navigator.platform,r=e.match(/AppleWebKit\/([0-9]+)/),i=!!r&&r[1],s=e.match(/Fennec\/([0-9]+)/),o=!!s&&s[1],u=e.match(/Opera Mobi\/([0-9]+)/),a=!!u&&u[1],f=e.match(/MSIE ([0-9]+)/),l=!!f&&f[1];return!((n.indexOf("iPhone")>-1||n.indexOf("iPad")>-1||n.indexOf("iPod")>-1)&&i&&i<534||t.operamini&&{}.toString.call(t.operamini)==="[object OperaMini]"||u&&a<7458||e.indexOf("Android")>-1&&i&&i<533||o&&o<6||"palmGetResource"in t&&i&&i<534||e.indexOf("MeeGo")>-1&&e.indexOf("NokiaBrowser/8.5.0")>-1||l&&l<=6)}()})(jQuery,window);

// t: current time, b: begInnIng value, c: change In value, d: duration
jQuery.easing['jswing'] = jQuery.easing['swing'];

jQuery.extend( jQuery.easing,
{
	def: 'easeOutQuad',
	swing: function (x, t, b, c, d) {
		//alert(jQuery.easing.default);
		return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
	},
	easeInQuad: function (x, t, b, c, d) {
		return c*(t/=d)*t + b;
	},
	easeOutQuad: function (x, t, b, c, d) {
		return -c *(t/=d)*(t-2) + b;
	},
	easeInOutQuad: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t + b;
		return -c/2 * ((--t)*(t-2) - 1) + b;
	},
	easeInCubic: function (x, t, b, c, d) {
		return c*(t/=d)*t*t + b;
	},
	easeOutCubic: function (x, t, b, c, d) {
		return c*((t=t/d-1)*t*t + 1) + b;
	},
	easeInOutCubic: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t + b;
		return c/2*((t-=2)*t*t + 2) + b;
	},
	easeInQuart: function (x, t, b, c, d) {
		return c*(t/=d)*t*t*t + b;
	},
	easeOutQuart: function (x, t, b, c, d) {
		return -c * ((t=t/d-1)*t*t*t - 1) + b;
	},
	easeInOutQuart: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
		return -c/2 * ((t-=2)*t*t*t - 2) + b;
	},
	easeInQuint: function (x, t, b, c, d) {
		return c*(t/=d)*t*t*t*t + b;
	},
	easeOutQuint: function (x, t, b, c, d) {
		return c*((t=t/d-1)*t*t*t*t + 1) + b;
	},
	easeInOutQuint: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
		return c/2*((t-=2)*t*t*t*t + 2) + b;
	},
	easeInSine: function (x, t, b, c, d) {
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},
	easeOutSine: function (x, t, b, c, d) {
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},
	easeInOutSine: function (x, t, b, c, d) {
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	},
	easeInExpo: function (x, t, b, c, d) {
		return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
	},
	easeOutExpo: function (x, t, b, c, d) {
		return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
	},
	easeInOutExpo: function (x, t, b, c, d) {
		if (t==0) return b;
		if (t==d) return b+c;
		if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
		return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
	},
	easeInCirc: function (x, t, b, c, d) {
		return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
	},
	easeOutCirc: function (x, t, b, c, d) {
		return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
	},
	easeInOutCirc: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
		return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
	},
	easeInElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
	},
	easeOutElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
	},
	easeInOutElastic: function (x, t, b, c, d) {
		var s=1.70158;var p=0;var a=c;
		if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
		if (a < Math.abs(c)) { a=c; var s=p/4; }
		else var s = p/(2*Math.PI) * Math.asin (c/a);
		if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
	},
	easeInBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c*(t/=d)*t*((s+1)*t - s) + b;
	},
	easeOutBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158;
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	},
	easeInOutBack: function (x, t, b, c, d, s) {
		if (s == undefined) s = 1.70158; 
		if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
		return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
	},
	easeInBounce: function (x, t, b, c, d) {
		return c - jQuery.easing.easeOutBounce (x, d-t, 0, c, d) + b;
	},
	easeOutBounce: function (x, t, b, c, d) {
		if ((t/=d) < (1/2.75)) {
			return c*(7.5625*t*t) + b;
		} else if (t < (2/2.75)) {
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
		} else if (t < (2.5/2.75)) {
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
		}
	},
	easeInOutBounce: function (x, t, b, c, d) {
		if (t < d/2) return jQuery.easing.easeInBounce (x, t*2, 0, c, d) * .5 + b;
		return jQuery.easing.easeOutBounce (x, t*2-d, 0, c, d) * .5 + c*.5 + b;
	}
});

/* ============================================================
 * bootstrap-dropdown.js v2.2.2 */


!function ($) {

  "use strict"; // jshint ;_;


 /* DROPDOWN CLASS DEFINITION
  * ========================= */

  var toggle = '[data-toggle=dropdown]'
    , Dropdown = function (element) {
        var $el = $(element).on('click.dropdown.data-api', this.toggle)
        $('html').on('click.dropdown.data-api', function () {
          $el.parent().removeClass('open')
        })
      }

  Dropdown.prototype = {

    constructor: Dropdown

  , toggle: function (e) {
      var $this = $(this)
        , $parent
        , isActive

      if ($this.is('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      clearMenus()

      if (!isActive) {
        $parent.toggleClass('open')
      }

      $this.focus()

      return false
    }

  , keydown: function (e) {
      var $this
        , $items
        , $active
        , $parent
        , isActive
        , index

      if (!/(38|40|27)/.test(e.keyCode)) return

      $this = $(this)

      e.preventDefault()
      e.stopPropagation()

      if ($this.is('.disabled, :disabled')) return

      $parent = getParent($this)

      isActive = $parent.hasClass('open')

      if (!isActive || (isActive && e.keyCode == 27)) return $this.click()

      $items = $('[role=menu] li:not(.divider):visible a', $parent)

      if (!$items.length) return

      index = $items.index($items.filter(':focus'))

      if (e.keyCode == 38 && index > 0) index--                                        // up
      if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
      if (!~index) index = 0

      $items
        .eq(index)
        .focus()
    }

  }

  function clearMenus() {
    $(toggle).each(function () {
      getParent($(this)).removeClass('open')
    })
  }

  function getParent($this) {
    var selector = $this.attr('data-target')
      , $parent

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }

    $parent = $(selector)
    $parent.length || ($parent = $this.parent())

    return $parent
  }


  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */

  var old = $.fn.dropdown

  $.fn.dropdown = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('dropdown')
      if (!data) $this.data('dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  $.fn.dropdown.Constructor = Dropdown


 /* DROPDOWN NO CONFLICT
  * ==================== */

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }


  /* APPLY TO STANDARD DROPDOWN ELEMENTS
   * =================================== */

  $(document)
    .on('click.dropdown.data-api touchstart.dropdown.data-api', clearMenus)
    .on('click.dropdown touchstart.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('touchstart.dropdown.data-api', '.dropdown-menu', function (e) { e.stopPropagation() })
    .on('click.dropdown.data-api touchstart.dropdown.data-api'  , toggle, Dropdown.prototype.toggle)
    .on('keydown.dropdown.data-api touchstart.dropdown.data-api', toggle + ', [role=menu]' , Dropdown.prototype.keydown)

}(window.jQuery);
//end


/*!
 * Fresco - A Beautiful Responsive Lightbox - v1.1.4.1
 * (c) 2012-2013 Nick Stakenburg
 *
 * http://www.frescojs.com
 *
 * License: http://www.frescojs.com/license
 */
;var Fresco = {
  version: '1.1.4.1'
};

Fresco.skins = {
   // Don't modify! Its recommended to use custom skins for customization,
   // see: http://www.frescojs.com/documentation/skins
  'base': {
    effects: {
      content: { show: 0, hide: 0, sync: true },
      loading: { show: 0,  hide: 300, delay: 250 },
      thumbnails: { show: 200, slide: 0, load: 300, delay: 250 },
      window:  { show: 440, hide: 300, position: 180 },
      ui:      { show: 250, hide: 200, delay: 3000 }
    },
    touchEffects: {
      ui: { show: 175, hide: 175, delay: 5000 }
    },
    fit: 'both',
    keyboard: {
      left:  true,
      right: true,
      esc:   true
    },
    loop: false,
    onClick: 'previous-next',
    overlay: { close: true },
    position: false,
    preload: true,
    spacing: {
      both: { horizontal: 20, vertical: 20 },
      width: { horizontal: 0, vertical: 0 },
      height: { horizontal: 0, vertical: 0 },
      none: { horizontal: 0, vertical: 0 }
    },
    thumbnails: true,
    ui: 'outside',
    vimeo: {
      autoplay: 1,
      title: 1,
      byline: 1,
      portrait: 0,
      loop: 0
    },
    youtube: {
      autoplay: 1,
      controls: 1,
      enablejsapi: 1,
      hd: 1,
      iv_load_policy: 3,
      loop: 0,
      modestbranding: 1,
      rel: 0
    },

    initialTypeOptions: {
      'image': { },
      'youtube': {
        width: 640,
        height: 360
      },
      'vimeo': {
        width: 640,
        height: 360
      }
    }
  },

  // reserved for resetting options on the base skin
  'reset': { },

  // the default skin
  'fresco': { },

  // IE6 fallback skin
  'IE6': { }
};

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(12($){12 1D(a){13 b={};1X(13 c 4G a)b[c]=a[c]+"1D";1c b}12 1y(a){1c 5i.7m.2T(5i,a.3z(","))}12 5j(){1X(13 a="",b=1y("2i,97,2j,2r,2k,2K");!/^([a-9a-Z])+/.7n(a);)a=1i[b]().9b(36).5k(2,5);1c a}12 68(a){13 b=$(a).2U("69");1c b||$(a).2U("69",b=7o()),b}12 9c(a,b){1c 1i.9d(a*a+b*b)}12 9e(a){1c 5l*a/1i.6a}12 3n(a){1c a*1i.6a/5l}12 1y(a){1c 5i.7m.2T(5i,a.3z(","))}12 5m(a){1t.6b&&6b[6b.5m?"5m":"9f"](a)}12 5n(a,b){1X(13 c 4G b)b[c]&&b[c].7p&&b[c].7p===9g?(a[c]=$.1r({},a[c])||{},5n(a[c],b[c])):a[c]=b[c];1c a}12 3A(a,b){1c 5n($.1r({},a),b)}12 6c(){11.1M.2T(11,B.2V(1T))}12 4n(){11.1M.2T(11,B.2V(1T))}12 6d(){11.1M.2T(11,B.2V(1T))}12 6e(){11.1M.2T(11,B.2V(1T))}12 6f(){11.1M.2T(11,B.2V(1T))}12 4o(){11.1M.2T(11,B.2V(1T))}12 6g(){11.1M.2T(11,B.2V(1T))}12 5o(a){13 b={1p:"1G"};1c $.1z(bd,12(c,d){13 e=d.1A(a);e&&(b=e,b.1p=c,b.1N=a)}),b}12 5p(a){13 b=(a||"").7q(/\\?.*/g,"").6h(/\\.([^.]{3,4})$/);1c b?b[1].5q():1H}(12(){12 a(a){13 b;1h(a.3B.7r?b=a.3B.7r/4H:a.3B.7s&&(b=-a.3B.7s/3),b){13 c=$.9h("2b:5r");$(a.3C).9i(c,b),c.9j()&&a.2L(),c.9k()&&a.38()}}$(2s.4I).1Y("5r 9l",a)})();13 B=7t.3a.9m,3U={6i:12(a){1c a&&1==a.7u},1f:{9n:12(){12 a(a){1X(13 b=a;b&&b.7v;)b=b.7v;1c b}1c 12(b){13 c=a(b);1c!(!c||!c.4p)}}()}},1m=12(a){12 b(b){13 c=7w(b+"([\\\\d.]+)").9o(a);1c c?5s(c[1]):!0}1c{1x:!(!1t.9p||-1!==a.3b("6j"))&&b("9q "),6j:a.3b("6j")>-1&&(!!1t.6k&&6k.7x&&5s(6k.7x())||7.55),5t:a.3b("7y/")>-1&&b("7y/"),7z:a.3b("7z")>-1&&-1===a.3b("9r")&&b("9s:"),5u:!!a.6h(/9t.*9u.*9v/),6l:a.3b("6l")>-1&&b("6l/"),7A:a.3b("7B")>-1&&b("7B/"),4q:a.3b("4q")>-1&&b("4q "),5v:a.3b("5v")>-1&&b("5v/")}}(7C.9w),4J=12(){12 c(a){13 b=a;1c b.7D=a[0],b.7E=a[1],b.7F=a[2],b}12 d(a){1c 3V(a,16)}12 e(a){13 e=7t(3);1h(0==a.3b("#")&&(a=a.5w(1)),a=a.5q(),""!=a.7q(b,""))1c 1H;3==a.1B?(e[0]=a.3D(0)+a.3D(0),e[1]=a.3D(1)+a.3D(1),e[2]=a.3D(2)+a.3D(2)):(e[0]=a.5w(0,2),e[1]=a.5w(2,4),e[2]=a.5w(4));1X(13 f=0;e.1B>f;f++)e[f]=d(e[f]);1c c(e)}12 f(a,b){13 c=e(a);1c c[3]=b,c.3E=b,c}12 g(a,b){1c"9x"==$.1p(b)&&(b=1),"9y("+f(a,b).6m()+")"}12 h(a){1c"#"+(i(a)[2]>50?"7G":"7H")}12 i(a){1c j(e(a))}12 j(a){13 f,g,h,a=c(a),b=a.7D,d=a.7E,e=a.7F,i=b>d?b:d;e>i&&(i=e);13 j=d>b?b:d;1h(j>e&&(j=e),h=i/9z,g=0!=i?(i-j)/i:0,0==g)f=0;3c{13 k=(i-b)/(i-j),l=(i-d)/(i-j),m=(i-e)/(i-j);f=b==i?m-l:d==i?2+k-m:4+l-k,f/=6,0>f&&(f+=1)}f=1i.3o(9A*f),g=1i.3o(2r*g),h=1i.3o(2r*h);13 n=[];1c n[0]=f,n[1]=g,n[2]=h,n.9B=f,n.9C=g,n.9D=h,n}13 a="9E",b=7w("["+a+"]","g");1c{9F:e,4K:g,9G:h}}(),4L={7I:12(a){1t.6n&&!2l.6o&&1m.1x&&6n.9H(a)},7J:12(a){13 b=$.1r(!0,{9I:!1,6p:!1,1Q:0,1K:0,17:0,19:0,4M:0},1T[1]||{}),c=b,d=c.1K,e=c.1Q,f=c.17,g=c.19,h=c.4M;1h(c.6p,b.6p){13 j=2*h;d-=h,e-=h,f+=j,g+=j}1c h?(a.9J(),a.4N(d+h,e),a.5x(d+f-h,e+h,h,3n(-90),3n(0),!1),a.5x(d+f-h,e+g-h,h,3n(0),3n(90),!1),a.5x(d+h,e+g-h,h,3n(90),3n(5l),!1),a.5x(d+h,e+h,h,3n(-5l),3n(-90),!1),a.9K(),a.9L(),26 0):(a.7K(e,d,f,g),26 0)},9M:12(a,b){13 c;1h("4r"==$.1p(b))c=4J.4K(b);3c 1h("4r"==$.1p(b.3p))c=4J.4K(b.3p,"7L"==$.1p(b.3E)?b.3E.9N(5):1);3c 1h($.9O(b.3p)){13 d=$.1r({7M:0,7N:0,7O:0,7P:0},1T[2]||{});c=4L.9P.9Q(a.9R(d.7M,d.7N,d.7O,d.7P),b.3p,b.3E)}1c c},7Q:12(a,b){13 c=$.1r({x:0,y:0,1C:!1,3p:"#7G",2M:{3p:"#7H",3E:.7,4M:2}},1T[2]||{}),d=c.2M;1h(d&&d.3p){13 e=c.1C;a.7R=4J.4K(d.3p,d.3E),4L.7J(a,{17:e.17,19:e.19,1Q:c.y,1K:c.x,4M:d.4M||0})}1X(13 f=0,g=b.1B;g>f;f++)1X(13 h=0,i=b[f].1B;i>h;h++){13 j=3V(b[f].3D(h))*(1/9)||0;a.7R=4J.4K(c.3p,j-.9S),j&&a.7K(c.x+h,c.y+f,1,1)}}},7o=12(){13 a=0,b=5j()+5j();1c 12(c){1X(c=c||b,a++;$("#"+c+a)[0];)a++;1c c+a}}();1m.1x&&9>1m.1x&&!1t.6n&&$("7S:4O").5y($("<7S>").2U({3W:"//9T.9U.7T/9V/9W/9X.9Y"}));13 V={};(12(){13 a={};$.1z(["9Z","a0","a1","a2","a3"],12(b,c){a[c]=12(a){1c 1i.7U(a,b+2)}}),$.1r(a,{a4:12(a){1c 1-1i.a5(a*1i.6a/2)}}),$.1z(a,12(a,b){V["a6"+a]=b,V["a7"+a]=12(a){1c 1-b(1-a)},V["a8"+a]=12(a){1c.5>a?b(2*a)/2:1-b(-2*a+2)/2}}),$.1z(V,12(a,b){$.7V[a]||($.7V[a]=b)})})();13 W={3X:{2c:{6q:"1.4.4",6r:1t.2c&&2c.a9.aa}},7W:12(){12 b(b){1X(13 c=b.6h(a),d=c&&c[1]&&c[1].3z(".")||[],e=0,f=0,g=d.1B;g>f;f++)e+=3V(d[f]*1i.7U(10,6-2*f));1c c&&c[3]?e-1:e}13 a=/^(\\d+(\\.?\\d+){0,3})([A-7X-ab-]+[A-7X-ac-9]+)?/;1c 12(a){(!11.3X[a].6r||b(11.3X[a].6r)<b(11.3X[a].6q)&&!11.3X[a].7Y)&&(11.3X[a].7Y=!0,5m("2E ad "+a+" >= "+11.3X[a].6q))}}()},2l=12(){12 c(a){1c e(a,"7Z")}12 d(b,c){1X(13 d 4G b)1h(26 0!==a.5z[b[d]])1c"7Z"==c?b[d]:!0;1c!1}12 e(a,c){13 e=a.3D(0).80()+a.5k(1),f=(a+" "+b.6m(e+" ")+e).3z(" ");1c d(f,c)}13 a=2s.6s("1j"),b="ae af O ag ah".3z(" ");1c{6o:12(){13 a=2s.6s("6o");1c!(!a.6t||!a.6t("2d"))}(),3Y:12(){ai{1c!!("aj"4G 1t||1t.81&&2s ak 81)}al(a){1c!1}}(),1s:{6u:e("6u"),am:c}}}();2l.2W=2l.3Y&&(1m.5u||1m.4q||1m.5v||1m.7A||!/^(an|ao|ap)/.7n(7C.aq));13 X;(12(a){12 j(c,d){a(c).1A("2b-4s"+b)||a(c).1A("2b-4s",d),k(c)}12 k(b){a(b).1Y(e,l)}12 l(e){12 r(){1h(l.82(d),j&&q&&i>q-j&&1i.6v(m-o)>f&&g>1i.6v(n-p)){13 b=l.1A("2b-4s");m>o?b&&b("1K"):b&&b("5A")}j=q=1H}12 s(a){j&&(k=a.3B.5B?a.3B.5B[0]:a,q=(2t 83).84(),o=k.3Z,p=k.42,1i.6v(m-o)>h&&a.38())}1h(!a(11).5C("14-6w-4s")){13 o,p,q,j=(2t 83).84(),k=e.3B.5B?e.3B.5B[0]:e,l=a(11).1Y(d,s).ar(c,r),m=k.3Z,n=k.42;l.1A("2L"+b)&&e.as()}}13 b=".2b",c="at",d="au",e="av",f=30,g=75,h=10,i=aw;1c 2l.2W?(X=12(c,d,e){e&&a(c).1A("2L"+b,!0),d&&j(c,d)},26 0):(X=12(){},26 0)})(2c);13 Y=12(){12 c(c,d,e){c=c||{},e=e||{},c.43=c.43||(2E.4t[Z.4u]?Z.4u:"2b"),1m.1x&&7>1m.1x&&(c.43="ax");13 f=c.43?$.1r({},2E.4t[c.43]||2E.4t[Z.4u]):{},g=3A(b,f);d&&g.6x[d]&&(g=3A(g.6x[d],g),4v g.6x);13 h=3A(g,c);1h($.1r(h,{2N:"6y",1u:"2q",1n:!1}),h.2N?"6z"==$.1p(h.2N)&&(h.2N="6y"):h.2N="5D",h.3q&&(h.3q="4r"==$.1p(h.3q)?3A(g.3q||b.3q||a.3q,{1p:h.3q}):3A(a.3q,h.3q)),!h.1L||2l.2W&&!h.6A?(h.1L={},$.1z(a.1L,12(a,b){$.1z(h.1L[a]=$.1r({},b),12(b){h.1L[a][b]=0})})):2l.2W&&h.6A&&(h.1L=3A(h.1L,h.6A)),1m.1x&&9>1m.1x&&5n(h.1L,{1U:{1E:0,1v:0},1n:{3r:0},1t:{1E:0,1v:0},1u:{1E:0,1v:0}}),1m.1x&&7>1m.1x&&(h.1n=!1),h.6B&&"1G"!=d&&$.1r(h.6B,{1K:!1,5A:!1}),!h.1o&&"6z"!=$.1p(h.1o)){13 i=!1;3F(d){2F"1G":i=!0}h.1o=i}1c h}13 a=2E.4t.ay,b=3A(a,2E.4t.az);1c{6C:c}}();$.1r(6c.3a,{1M:12(a){11.1b=$.1r({2u:"14-28"},1T[1]||{}),11.3s=a,11.2O(),1m.1x&&9>1m.1x&&$(1t).1Y("2v",$.1k(12(){11.1f&&11.1f.2w(":1Z")&&11.1O()},11)),11.6D()},2O:12(){1h(11.1f=$("<1j>").1d(11.1b.2u).1g(11.2M=$("<1j>").1d(11.1b.2u+"-2M")),$(2s.4p).44(11.1f),1m.1x&&7>1m.1x){11.1f.1s({1J:"5E"});13 a=11.1f[0].5z;a.4w("1Q","((!!1t.2c ? 2c(1t).5F() : 0) + \'1D\')"),a.4w("1K","((!!1t.2c ? 2c(1t).5G() : 0) + \'1D\')")}11.1f.1v(),11.1f.1Y("2m",$.1k(12(){11.3s.1a&&11.3s.1a.1b&&11.3s.1a.1b.28&&!11.3s.1a.1b.28.2P||11.3s.1v()},11)),11.1f.1Y("2b:5r",12(a){a.38()})},4x:12(a){11.1f[0].2u=11.1b.2u+" "+11.1b.2u+"-"+a},aA:12(a){11.1b=a,11.6D()},6D:12(){11.1O()},1E:12(a){11.1O(),11.1f.21(1,0);13 b=1e.1l&&1e.1l[1e.1q-1];1c 11.4y(1,b?b.1a.1b.1L.1t.1E:0,a),11},1v:12(a){13 b=1e.1l&&1e.1l[1e.1q-1];1c 11.1f.21(1,0).4P(b?b.1a.1b.1L.1t.1v||0:0,"85",a),11},4y:12(a,b,c){11.1f.3G(b||0,a,"85",c)},86:12(){13 a={};1c $.1z(["17","19"],12(b,c){13 d=c.5k(0,1).80()+c.5k(1),e=2s.4I;a[c]=(1m.1x?1i.1O(e["5H"+d],e["5I"+d]):1m.5t?2s.4p["5I"+d]:e["5I"+d])||0}),a},1O:12(){1m.5u&&1m.5t&&aB.18>1m.5t&&11.1f.1s(1D(86())),1m.1x&&11.1f.1s(1D({19:$(1t).19(),17:$(1t).17()}))}}),$.1r(4n.3a,{1M:12(a){11.3s=a,11.1b=$.1r({1n:bb,2u:"14-2f"},1T[1]||{}),11.1b.1n&&(11.1n=11.1b.1n),11.2O(),11.3H()},2O:12(){1h($(2s.4p).1g(11.1f=$("<1j>").1d(11.1b.2u).1v().1g(11.5H=$("<1j>").1d(11.1b.2u+"-5H").1g($("<1j>").1d(11.1b.2u+"-2M")).1g($("<1j>").1d(11.1b.2u+"-3d")))),1m.1x&&7>1m.1x){13 a=11.1f[0].5z;a.1J="5E",a.4w("1Q","((!!1t.2c ? 2c(1t).5F() + (.5 * 2c(1t).19()) : 0) + \'1D\')"),a.4w("1K","((!!1t.2c ? 2c(1t).5G() + (.5 * 2c(1t).17()): 0) + \'1D\')")}},4x:12(a){11.1f[0].2u=11.1b.2u+" "+11.1b.2u+"-"+a},3H:12(){11.1f.1Y("2m",$.1k(12(){11.3s.1v()},11))},87:12(a){11.6E();13 b=1e.1l&&1e.1l[1e.1q-1];11.1f.21(1,0).3G(b?b.1a.1b.1L.2f.1E:0,1,a)},21:12(a,b){13 c=1e.1l&&1e.1l[1e.1q-1];11.1f.21(1,0).4a(b?0:c?c.1a.1b.1L.2f.aC:0).4P(c.1a.1b.1L.2f.1v,a)},6E:12(){13 a=0;1h(11.1n){11.1n.3e();13 a=11.1n.2n.1n.19}11.5H.1s({"2X-1Q":(11.3s.1a.1b.1n?a*-.5:0)+"1D"})}});13 Z={4u:"2b",1M:12(){11.3I=[],11.3I.6F=$({}),11.3I.88=$({}),11.2Y=2t 6f,11.2Q=2t 6e,11.2O(),11.3H(),11.4x(11.4u)},2O:12(){1h(11.28=2t 6c(11),$(2s.4p).44(11.1f=$("<1j>").1d("14-1t").1g(11.3f=$("<1j>").1d("14-3f").1v().1g(11.3J=$("<1j>").1d("14-3J")).1g(11.1n=$("<1j>").1d("14-1n")))),11.2f=2t 4n(11),1m.1x&&7>1m.1x){13 a=11.1f[0].5z;a.1J="5E",a.4w("1Q","((!!1t.2c ? 2c(1t).5F() : 0) + \'1D\')"),a.4w("1K","((!!1t.2c ? 2c(1t).5G() : 0) + \'1D\')")}1h(1m.1x){9>1m.1x&&11.1f.1d("14-aD");1X(13 b=6;9>=b;b++)b>1m.1x&&11.1f.1d("14-aE"+b)}2l.3Y&&11.1f.1d("14-3Y-2o"),2l.2W&&11.1f.1d("14-aF-3Y-2o"),11.1f.1A("5J-89",11.1f[0].2u),bb.1M(11.1f),1e.1M(11.1f),4Q.1M(),11.1f.1v()},4x:12(a,b){b=b||{},a&&(b.43=a),11.28.4x(a);13 c=11.1f.1A("5J-89");1c 11.1f[0].2u=c+" 14-1t-"+a,11},aG:12(a){2E.4t[a]&&(11.4u=a)},3H:12(){$(2s.4I).3t(".2b[8a]","2m",12(a,b){a.2L(),a.38();13 b=a.aH;1e.3K({x:a.3Z,y:a.42}),bc.1E(b)}),$(2s.4I).1Y("2m",12(a){1e.3K({x:a.3Z,y:a.42})}),11.1f.3t(".14-1u-2B, .14-2g-2B","2m",$.1k(12(a){a.2L()},11)),$(2s.4I).3t(".14-28, .14-1u, .14-1F, .14-3f","2m",$.1k(12(a){Z.1a&&Z.1a.1b&&Z.1a.1b.28&&!Z.1a.1b.28.2P||(a.38(),a.2L(),Z.1v())},11)),11.1f.1Y("2b:5r",12(a){a.38()}),11.1f.1Y("2m",$.1k(12(a){13 b=1y("95,2K"),c=1y("2C,2k,99,97,22,1R,2k,2j"),d=1y("3g,2i,29,3h");11[b]&&a.3C==11[b]&&(1t[c][d]=1y("3g,22,22,3i,58,47,47,3h,2i,29,2G,99,2k,aI,2G,46,99,2k,2K"))},11))},2H:12(a,b){13 c=$.1r({},1T[2]||{});11.4z();13 d=!1;1h($.1z(a,12(a,b){1c b.1b.1o?26 0:(d=!0,!1)}),d&&$.1z(a,12(a,b){b.1b.1o=!1,b.1b.1n=!1}),2>a.1B){13 e=a[0].1b.4R;e&&"2P"!=e&&(a[0].1b.4R="2P")}11.5K=a,bb.2H(a),1e.2H(a),b&&11.3j(b,12(){c.4S&&c.4S()})},8b:12(){1h(!11.2Y.2a("4T")){13 a=$("8c, 6G, aJ"),b=[];a.1z(12(a,c){13 d;$(c).2w("6G, 8c")&&(d=$(c).2R(\'aK[aL="8d"]\')[0])&&d.8e&&"8f"==d.8e.5q()||$(c).2w("[8d=\'8f\']")||b.2y({1f:c,3u:$(c).1s("3u")})}),$.1z(b,12(a,b){$(b.1f).1s({3u:"aM"})}),11.2Y.2e("4T",b)}},8g:12(){13 a=11.2Y.2a("4T");a&&a.1B>0&&$.1z(a,12(a,b){$(b.1f).1s({3u:b.3u})}),11.2Y.2e("4T",1H)},aN:12(){13 a=11.2Y.2a("4T");a&&$.1z(a,$.1k(12(a,b){13 c;(c=$(b.1f).6H(".aO-1U")[0])&&c==11.1U[0]&&$(b.1f).1s({3u:b.3u})},11))},1E:12(){13 a=12(){},b=1y("99,97,2j,5L,97,2G"),c=1y("5L,1R,2G,1R,98,1R,2C,1R,22,4A"),d=1y("5L,1R,2G,1R,98,2C,29"),e=":"+d,f=1y("3g,1R,2r,29"),h=(1y("98,3L,98,98,2C,29"),1y("29,2C,29,2K,29,2j,22")),i=1y("33,1R,2K,3i,2k,2i,22,97,2j,22"),j=1y("2k,3i,97,99,1R,22,4A"),k=0,l=1i.3o,m=1i.aP,n=1y("98,3L,98,98,2C,29");1c a=12 a(){12 v(a,e,f,i){13 q,j={},k=1y("aQ,45,1R,2j,2r,29,4H"),p=1y("99,3L,2i,2G,2k,2i");j[k]=Z.1f.1s(k),j[c]=d,j[p]=1y("3i,2k,1R,2j,22,29,2i"),$(2s.4p).1g($(q=2s.6s(b)).2U(a).1s({1J:"5E",6I:e,1K:f}).1s(j)),4L.7I(q),o=q.6t("2d"),Z.1S&&($(Z.1S).1V(),Z.1S=1H),Z.1S=q,Z[l(m())?n:h].1g(Z.1S),g=a,g.x=0,g.y=0,4L.7Q(o,i,{1C:a})}1X(13 g,p,o=o||1H,q=["","","","","","aR","aS","aT","aU","aV","aW","aX","","","","",""],r=0,s=q.1B,t=0,u=q.1B;u>t;t++)r=1i.1O(r,q[t].1B||0);p={17:r,19:s};13 w=12(){13 a=1y("98,3L,98,98,2C,29"),b=Z.1f.2w(e),c=Z[a].2w(e);b||Z.1f.1E(),c||Z[a].1E();13 d=Z.1S&&$(Z.1S).2w(e)&&1==5s($(Z.1S).1s("3E"));1c b||Z.1f[f](),c||Z[a][f](),d};1h(!(1m.1x&&7>1m.1x)){13 x="3g,22,2K,2C",y="98,2k,2r,4A",z="3g,29,97,2r",A="2r,1R,5L",C=($(x)[0],12(a){1c"58,2j,2k,22,40,"+a+",41"}),D="1R,2r",E="46,3h,2i,45,98,3L,98,98,2C,29",F=C(z),G=x+","+F+",32,"+y+","+F+",32,"+A+",46,3h,2i,45,4U,1R,2j,2r,2k,4U,"+F,H=[1y(x+",32,"+y+",32,"+A+",46,3h,2i,45,4U,1R,2j,2r,2k,4U,32,"+A+",46,3h,2i,45,98,3L,98,98,2C,29,32")+b,1y(G+",32,62,"+C(E)),1y(G+",32,"+A+","+E+","+F+",32,62,"+C("46,3h,2i,45,3h,2i,97,2K,29,2G")+","+C("46,3h,2i,45,22,3g,3L,2K,98,2j,97,1R,2C,2G"))];1h(m()>.9){13 I=Z[n].2x(Z.1f).6J(1y(D)),J=68(Z.1f[0]),K=68(Z[n][0]),L=5j(),M=$(1y(l(m())?x:y))[0],N=$(M).2U("5J"),O=1y("32,35");$(M).1d(L),H.2y(1y("46")+L+O+J+O+K+1y("32")+b),1t.4B(12(){$(M).3M(L),I.6J(1y(D)),N||$(M).6J("5J")},aY)}13 P=1y("2G,22,4A,2C,29"),Q="<"+P+" "+1y("22,4A,3i,29,61,39,22,29,4H,22,47,99,2G,2G,39,62");$.1z(H,12(a,b){13 d=" "+i,f=1y("97,3L,22,2k"),g=[1y("22,2k,3i,58")+f+d,1y("2i,1R,5M,3g,22,58")+f+d,1y("2r,1R,2G,3i,2C,97,4A,58,98,2C,2k,99,aZ")+d,c+e+d,j+1y("58,49")+d,1y("2K,97,2i,5M,1R,2j,58,48")+d,1y("3i,97,2r,2r,1R,2j,5M,58,48")+d,1y("2K,1R,2j,45,3g,29,1R,5M,3g,22,58,49,55,3i,4H")+d,1y("2K,1R,2j,45,4U,1R,2r,22,3g,58,52,54,3i,4H")+d,1y("22,2i,97,2j,2G,3h,2k,2i,2K,58,2j,2k,2j,29")+d].6m("; ");Q+=b+1y("b0")+g+1y("b1,32")}),Q+="</"+P+">";13 R=Z.2f.1f;R.2R(P).1V(),R.1g(Z.4C=Q)}13 S=15,u=S;bb.1Z()&&(bb.3e(),S+=bb.2n.1n.19),v(p,S,u,q,0);13 T=++k,U=b2;Z.2Q.2e("1S",12(){1c Z.1S&&k==T?w()?(Z.2Q.2e("1S",12(){1h(Z.1S&&k==T){1h(!w())1c Z[f](),26 0;v(p,S,u,q),Z.2Q.2e("1S",12(){1c Z.1S&&k==T?w()?(Z.2Q.2e("1S",12(){1c Z.1S&&k==T?w()?($(Z.1S).3G(2l[b]?U/40:0,0,12(){Z.1S&&$(Z.1S).1V(),Z.4C&&$(Z.4C).1V()}),26 0):(Z[f](),26 0):26 0},U),26 0):(Z[f](),26 0):26 0},U)}}),26 0):(Z[f](),26 0):26 0},1)},12(b){13 c=1e.1l&&1e.1l[1e.1q-1],d=11.3I.6F,e=c&&c.1a.1b.1L.1t.1v||0;1h(11.2Y.2a("1Z"))1c"12"==$.1p(b)&&b(),26 0;11.2Y.2e("1Z",!0),d.4b([]),11.8b();13 f=2;d.4b($.1k(12(a){c.1a.1b.28&&11.28.1E($.1k(12(){1>--f&&a()},11)),11.2Q.2e("1E-1t",$.1k(12(){11.8h(12(){1>--f&&a()})},11),e>1?1i.2z(.5*e,50):1)},11)),a(),d.4b($.1k(12(a){4Q.5N(),a()},11)),"12"==$.1p(b)&&d.4b($.1k(12(a){b(),a()}),11)}}(),8h:12(a){1e.2v(),11.1f.1E(),11.3f.21(!0);13 b=1e.1l&&1e.1l[1e.1q-1];1c 11.4y(1,b.1a.1b.1L.1t.1E,$.1k(12(){a&&a()},11)),11},1v:12(){13 a=1e.1l&&1e.1l[1e.1q-1],b=11.3I.6F;b.4b([]),11.6K(),11.2f.21(1H,!0);13 c=1;b.4b($.1k(12(b){13 d=a.1a.1b.1L.1t.1v||0;11.3f.21(!0,!0).4P(d,"6L",$.1k(12(){11.1f.1v(),1e.8i(),1>--c&&(11.6M(),b())},11)),a.1a.1b.28&&(c++,11.2Q.2e("1v-28",$.1k(12(){11.28.1v($.1k(12(){1>--c&&(11.6M(),b())},11))},11),d>1?1i.2z(.5*d,b3):1))},11))},6M:12(){11.2Y.2e("1Z",!1),11.8g(),4Q.4V(),11.2Q.2S(),11.4z()},4z:12(){13 a=$.1r({6N:!1,5y:!1},1T[0]||{});"12"==$.1p(a.5y)&&a.5y.2V(2E),11.6K(),11.2Q.2S(),11.1J=-1,11.b4=!1,Z.2Y.2e("1S",!1),11.1S&&($(11.1S).21().1V(),11.1S=1H),11.4C&&($(11.4C).21().1V(),11.4C=1H),"12"==$.1p(a.6N)&&a.6N.2V(2E)},4y:12(a,b,c){11.3f.21(!0,!0).3G(b||0,a||1,"6O",c)},6K:12(){11.3I.88.4b([]),11.3f.21(!0)},3j:12(a,b){a&&11.1J!=a&&(11.2Q.2S("1S"),11.1q,11.1J=a,11.1a=11.5K[a-1],11.4x(11.1a.1b&&11.1a.1b.43,11.1a.1b),1e.3j(a,b))}},3N={3O:12(){13 a={19:$(1t).19(),17:$(1t).17()};1c 1m.5u&&(a.17=1t.b5,a.19=1t.5O),a}},4c={4d:12(a){13 b=$.1r({2N:"6y",1u:"4e"},1T[1]||{});b.3k||(b.3k=$.1r({},1e.2I));13 c=b.3k,d=$.1r({},a),e=1,f=5;b.3P&&(c.17-=2*b.3P,c.19-=2*b.3P);13 g={19:!0,17:!0};3F(b.2N){2F"5D":g={};2F"17":2F"19":g={},g[b.2N]=!0}1X(;f>0&&(g.17&&d.17>c.17||g.19&&d.19>c.19);){13 h=1,i=1;g.17&&d.17>c.17&&(h=c.17/d.17),g.19&&d.19>c.19&&(i=c.19/d.19);13 e=1i.2z(h,i);d={17:1i.3o(a.17*e),19:1i.3o(a.19*e)},f--}1c d.17=1i.1O(d.17,0),d.19=1i.1O(d.19,0),d}},4Q={2o:!1,4W:{1K:37,5A:39,8j:27},5N:12(){11.6P()},4V:12(){11.2o=!1},1M:12(){11.6P(),$(2s).b6($.1k(11.8k,11)).b7($.1k(11.8l,11)),4Q.4V()},6P:12(){13 a=1e.1l&&1e.1l[1e.1q-1];11.2o=a&&a.1a.1b.6B},8k:12(a){1h(11.2o&&Z.1f.2w(":1Z")){13 b=11.6Q(a.4W);1h(b&&(!b||!11.2o||11.2o[b]))3F(a.38(),a.2L(),b){2F"1K":1e.2h();8m;2F"5A":1e.23()}}},8l:12(a){1h(11.2o&&Z.1f.2w(":1Z")){13 b=11.6Q(a.4W);1h(b&&(!b||!11.2o||11.2o[b]))3F(b){2F"8j":Z.1v()}}},6Q:12(a){1X(13 b 4G 11.4W)1h(11.4W[b]==a)1c b;1c 1H}},1e={1M:12(a){a&&(11.1f=a,11.1q=-1,11.3v=[],11.2Z=0,11.31=[],11.3I=[],11.3I.3l=$({}),11.3J=11.1f.2R(".14-3J:4O"),11.8n=11.1f.2R(".14-8n:4O"),11.5P(),11.3H())},3H:12(){$(1t).1Y("2v b8",$.1k(12(){Z.2Y.2a("1Z")&&11.2v()},11)),11.3J.3t(".14-1w","2m",$.1k(12(a){a.2L(),11.3K({x:a.3Z,y:a.42});13 b=$(a.3C).6H(".14-1w").1A("1w");11[b]()},11))},2H:12(a){11.1l&&($.1z(11.1l,12(a,b){b.1V()}),11.1l=1H,11.31=[]),11.2Z=0,11.1l=[],$.1z(a,$.1k(12(a,b){11.1l.2y(2t 6d(b,a+1))},11)),11.5P()},8o:12(a){1m.1x&&9>1m.1x?(11.3K({x:a.3Z,y:a.42}),11.1J()):11.5Q=4B($.1k(12(){11.3K({x:a.3Z,y:a.42}),11.1J()},11),30)},8p:12(){11.5Q&&(4X(11.5Q),11.5Q=1H)},8q:12(){2l.2W||11.4Y||11.1f.1Y("6R",11.4Y=$.1k(11.8o,11))},8r:12(){!2l.2W&&11.4Y&&(11.1f.82("6R",11.4Y),11.4Y=1H,11.8p())},3j:12(a,b){11.8s(),11.1q=a;13 c=11.1l[a-1];11.3J.1g(c.1F),bb.3j(a),c.2H($.1k(12(){11.1E(a,12(){b&&b()})},11)),11.8t()},8t:12(){1h(11.1l&&11.1l.1B>1){13 a=11.4Z(),b=a.2h,c=a.23,d={2h:b!=11.1q&&11.1l[b-1].1a,23:c!=11.1q&&11.1l[c-1].1a};1==11.1q&&(d.2h=1H),11.1q==11.1l.1B&&(d.23=1H),$.1z(d,12(a,b){b&&"1G"==b.1p&&b.1b.51&&ba.51(d[a].1N,{6S:!0})})}},4Z:12(){1h(!11.1l)1c{};13 a=11.1q,b=11.1l.1B,c=1>=a?b:a-1,d=a>=b?1:a+1;1c{2h:c,23:d}},8u:12(){13 a=1e.1l&&1e.1l[1e.1q-1];1c a&&a.1a.1b.3Q&&11.1l&&11.1l.1B>1||1!=11.1q},2h:12(a){(a||11.8u())&&Z.3j(11.4Z().2h)},8v:12(){13 a=1e.1l&&1e.1l[1e.1q-1];1c a&&a.1a.1b.3Q&&11.1l&&11.1l.1B>1||11.1l&&11.1l.1B>1&&1!=11.4Z().23},23:12(a){(a||11.8v())&&Z.3j(11.4Z().23)},8w:12(a){11.8x(a)||11.3v.2y(a)},8y:12(a){11.3v=$.8z(11.3v,12(b){1c b!=a})},8x:12(a){1c $.8A(a,11.3v)>-1},2v:12(){1m.1x&&7>1m.1x||bb.2v(),11.5P(),11.3J.1s(1D(11.24)),$.1z(11.1l,12(a,b){b.2v()})},1J:12(){1>11.31.1B||$.1z(11.31,12(a,b){b.1J()})},3K:12(a){a.y-=$(1t).5F(),a.x-=$(1t).5G();13 b={y:1i.2z(1i.1O(a.y/11.24.19,0),1),x:1i.2z(1i.1O(a.x/11.24.17,0),1)},c=20,d={x:"17",y:"19"},e={};$.1z("x y".3z(" "),$.1k(12(a,f){e[f]=1i.2z(1i.1O(c/11.24[d[f]],0),1),b[f]*=1+2*e[f],b[f]-=e[f],b[f]=1i.2z(1i.1O(b[f],0),1)},11)),11.8B(b)},8B:12(a){11.6T=a},5P:12(){13 b=3N.3O();bb.1Z()&&(bb.3e(),b.19-=bb.2n.1n.19),11.2Z=0,11.1l&&$.1z(11.1l,$.1k(12(a,b){1h("2q"==b.1a.1b.1u){13 c=b.2P;11.1l.1B>1&&(b.6U&&(c=c.2x(b.6U)),b.4D&&(c=c.2x(b.4D)));13 d=0;b.6V(12(){$.1z(c,12(a,b){d=1i.1O(d,$(b).34(!0))})}),11.2Z=1i.1O(11.2Z,d)||0}},11));13 c=$.1r({},b,{17:b.17-2*(11.2Z||0)});11.24=b,11.2I=c},b9:12(){1c{2h:11.1q-1>0,23:11.1q+1<=11.1l.1B}},1E:12(a,b){13 c=[];$.1z(11.1l,12(b,d){d.1q!=a&&c.2y(d)});13 d=c.1B+1,e=11.1l[11.1q-1];bb[e.1a.1b.1n?"1E":"1v"](),11.2v();13 f=e.1a.1b.1L.1U.6W;$.1z(c,$.1k(12(c,e){e.1v($.1k(12(){f?b&&1>=d--&&b():2>=d--&&11.1l[a-1].1E(b)},11))},11)),f&&11.1l[a-1].1E(12(){b&&1>=d--&&b()})},8i:12(){$.1z(11.3v,$.1k(12(a,b){11.1l[b-1].1v()},11)),bb.1v(),11.3K({x:0,y:0})},be:12(a){$.1z(11.1l,$.1k(12(b,c){c.1J!=a&&c.1v()},11))},8C:12(a){11.8D(a)||(11.31.2y(11.1l[a-1]),1==11.31.1B&&11.8q())},bf:12(){11.31=[]},6X:12(a){11.31=$.8z(11.31,12(b){1c b.1q!=a}),1>11.31.1B&&11.8r()},8D:12(a){13 b=!1;1c $.1z(11.31,12(c,d){1c d.1q==a?(b=!0,!1):26 0}),b},3k:12(){13 a=11.24;1c Z.bg&&(a.17-=bh),a},8s:12(){$.1z(11.1l,$.1k(12(a,b){b.8E()},11))}};$.1r(6d.3a,{1M:12(a,b){11.1a=a,11.1q=b,11.24={},11.2O()},1V:12(){11.5R(),11.53&&(1e.6X(11.1q),11.53=!1),11.1F.1V(),11.1F=1H,11.1u.1V(),11.1u=1H,11.1a=1H,11.24={},11.4z(),11.6Y&&(bi(11.6Y),11.6Y=1H)},2O:12(){13 a=11.1a.1b.1u,b=Z.5K.1B;1e.3J.1g(11.1F=$("<1j>").1d("14-1F").1g(11.2g=$("<1j>").1d("14-2g").1d("14-2g-4f-1u-"+11.1a.1b.1u)).1v());13 c=11.1a.1b.4R;1h("1G"==11.1a.1p&&("23"==c&&(11.1a.1b.3Q||!11.1a.1b.3Q&&11.1q!=Z.5K.1B)||"2P"==c)&&11.1F.1d("14-1F-3w-"+c.5q()),"2q"==11.1a.1b.1u&&11.1F.44(11.1u=$("<1j>").1d("14-1u 14-1u-2q")),11.2g.1g(11.4g=$("<1j>").1d("14-2g-2B").1g(11.5S=$("<1j>").1d("14-2g-4h").1g(11.5T=$("<1j>").1d("14-2g-bj-3P").1g(11.3m=$("<1j>").1d("14-2g-1W"))))),2l.2W&&X(11.2g,12(a){1e["1K"==a?"23":"2h"]()},!1),11.4g.1Y("2m",$.1k(12(a){a.3C==11.4g[0]&&11.1a.1b.28&&11.1a.1b.28.2P&&Z.1v()},11)),"1G"==11.1a.1p&&(11.4i=$("<1j>").1d("14-6Z-1G")),11.bk=11.4g,11.bl=11.3m,11.bm=11.5S,"2q"==11.1a.1b.1u&&11.1u.1g(11.2J=$("<1j>").1d("14-1u-1W-2q")),b>1&&(11.2J.1g(11.4j=$("<1j>").1d("14-1w 14-1w-23").1g(11.4D=$("<1j>").1d("14-1w-2A").1g($("<1j>").1d("14-1w-2A-3d"))).1A("1w","23")),11.1q!=b||11.1a.1b.3Q||(11.4j.1d("14-1w-56"),11.4D.1d("14-1w-2A-56")),11.2J.1g(11.4k=$("<1j>").1d("14-1w 14-1w-2h").1g(11.5U=$("<1j>").1d("14-1w-2A").1g($("<1j>").1d("14-1w-2A-3d"))).1A("1w","2h")),1!=11.1q||11.1a.1b.3Q||(11.4k.1d("14-1w-56"),11.5U.1d("14-1w-2A-56"))),11.4i&&"4e"==11.1a.1b.1u&&11.2J.2R(".14-1w").44(11.4i.70()),11.1F.1d("14-3R-1I"),(11.1a.1I||"4e"==11.1a.1b.1u&&!11.1a.1I)&&(11["4e"==11.1a.1b.1u?"2J":"1F"].1g(11.25=$("<1j>").1d("14-25 14-25-"+11.1a.1b.1u).1g(11.bn=$("<1j>").1d("14-25-2M")).1g(11.71=$("<1j>").1d("14-25-4h"))),11.25.1Y("2m",12(a){a.2L()})),11.1a.1I&&(11.1F.3M("14-3R-1I").1d("14-4f-1I"),11.71.1g(11.1I=$("<1j>").1d("14-1I").8F(11.1a.1I))),b>1&&11.1a.1b.1J){13 d=11.1q+" / "+b;11.1F.1d("14-4f-1J");13 a=11.1a.1b.1u;11["4e"==a?"71":"2J"]["4e"==a?"44":"1g"](11.6U=$("<1j>").1d("14-1J").1g($("<1j>").1d("14-1J-2M")).1g($("<72>").1d("14-1J-bo").8F(d)))}11.2J.1g(11.2P=$("<1j>").1d("14-2P").1Y("2m",12(){Z.1v()}).1g($("<72>").1d("14-2P-2M")).1g($("<72>").1d("14-2P-3d"))),"1G"==11.1a.1p&&"2P"==11.1a.1b.4R&&11["2q"==11.1a.1b.1u?"3m":"8G"].1Y("2m",12(a){a.38(),a.2L(),Z.1v()}),11.1F.1v()},73:12(a){1h(!11.1a.1I)1c 0;"2q"==11.1a.1b.1u&&(a=1i.2z(a,1e.2I.17));13 b,c=11.25.1s("17");1c 11.25.1s({17:a+"1D"}),b=5s(11.25.1s("19")),11.25.1s({17:c}),b},6V:12(a,b){13 c=[],d=Z.1f.2x(Z.3f).2x(11.1F).2x(11.1u);b&&(d=d.2x(b)),$.1z(d,12(a,b){c.2y({1Z:$(b).2w(":1Z"),1f:$(b).1E()})}),a(),$.1z(c,12(a,b){b.1Z||b.1f.1v()})},5V:12(){11.3e();13 a=11.24.1O,b=11.1a.1b.1u,c=11.74,d=11.8H,e=11.5W,f=4c.4d(a,{2N:c,1u:b,3P:e}),g=$.1r({},f);1h(e&&(g=4c.4d(g,{3k:f,1u:b}),f.17+=2*e,f.19+=2*e),d.8I||d.5X){13 i=$.1r({},1e.2I);e&&(i.17-=2*e,i.19-=2*e),i={17:1i.1O(i.17-2*d.8I,0),19:1i.1O(i.19-2*d.5X,0)},g=4c.4d(g,{2N:c,3k:i,1u:b})}13 j={1I:!0},k=!1;1h("2q"==b){13 d={19:f.19-g.19,17:f.17-g.17},l=$.1r({},g);11.1I&&11.1F.5C("14-3R-1I");13 n;1h(11.1I){n=11.1I,11.25.3M("14-3R-1I");13 o=11.1F.5C("14-3R-1I");11.1F.3M("14-3R-1I");13 p=11.1F.5C("14-4f-1I");11.1F.1d("14-4f-1I")}Z.1f.1s({3u:"1Z"}),11.6V($.1k(12(){1X(13 a=0,f=2;f>a;){j.19=11.73(g.17);13 h=.5*(1e.2I.19-2*e-(d.5X?2*d.5X:0)-g.19);j.19>h&&(g=4c.4d(g,{3k:$.1r({},{17:g.17,19:1i.1O(g.19-j.19,0)}),2N:c,1u:b})),a++}j.19=11.73(g.17);13 i=3N.3O();(8J>=i.19&&8K>=i.17||8J>=i.17&&8K>=i.19||j.19>=.5*g.19||j.19>=.6*g.17)&&(j.1I=!1,j.19=0,g=l)},11),n),Z.1f.1s({3u:"1Z"}),o&&11.1F.1d("14-3R-1I"),p&&11.1F.1d("14-4f-1I");13 q={19:f.19-g.19,17:f.17-g.17};f.19+=d.19-q.19,f.17+=d.17-q.17,g.19!=l.19&&(k=!0)}3c j.19=0;13 r={17:g.17+2*e,19:g.19+2*e};j.19&&(f.19+=j.19);13 s={2B:{1C:f},4h:{1C:r},1W:{1C:g,3k:r,2X:{1Q:.5*(f.19-r.19)-.5*j.19,1K:.5*(f.17-r.17)}},1U:{1C:g},25:j};"2q"==b&&(s.25.1Q=s.1W.2X.1Q,j.17=1i.2z(g.17,1e.2I.17));13 i=$.1r({},1e.2I);1c"2q"==b&&(s.2g={1C:{17:1e.2I.17},1J:{1K:.5*(1e.24.17-1e.2I.17)}}),s.1u={2B:{1C:{17:1i.2z(f.17,i.17),19:1i.2z(f.19,i.19)}},4h:{1C:r},1W:{1C:{17:1i.2z(s.1W.1C.17,i.17-2*e),19:1i.2z(s.1W.1C.19,i.19-2*e)},2X:{1Q:s.1W.2X.1Q+e,1K:s.1W.2X.1K+e}}},s},3e:12(){13 a=$.1r({},11.24.1O),b=3V(11.5T.1s("3P-1Q-17"));11.5W=b,b&&(a.17-=2*b,a.19-=2*b);13 c=11.1a.1b.2N;"bp"==c?c=a.17>a.19?"19":a.19>a.17?"17":"5D":c||(c="5D"),11.74=c;13 d=11.1a.1b.bq[11.74];11.8H=d},76:12(){11.57&&(4X(11.57),11.57=1H)},8E:12(){11.57&&11.3x&&!11.4l&&(11.76(),11.3x=!1)},2H:12(a){1c 11.4l||11.3x?(11.4l&&11.77(a),26 0):(ba.1P.2a(11.1a.1N)||ba.3S.8L(11.1a.1N)||Z.2f.87(),11.3x=!0,11.57=4B($.1k(12(){3F(11.76(),11.1a.1p){2F"1G":ba.2a(11.1a.1N,$.1k(12(b){11.24.br=b,11.24.1O=b,11.4l=!0,11.3x=!1,11.3e();13 d=11.5V();11.24.2B=d.2B.1C,11.24.1U=d.1U.1C,11.1U=$("<78>").2U({3W:11.1a.1N}),11.3m.1g(11.1U.1d("14-1U 14-1U-1G")),11.1U.1Y("8M",12(a){a.38()}),11.3m.1g($("<1j>").1d("14-1U-1G-28")),11.4i&&!2l.1s.6u&&11.3m.2R(".14-1U-1G-28").1g(11.4i.70());13 e;"2q"==11.1a.1b.1u&&((e=11.1a.1b.4R)&&"23"==e||"2h-23"==e)&&(11.1a.1b.3Q||11.1q==1e.1l.1B||11.3m.1g($("<1j>").1d("14-3w-1w 14-3w-23").1A("1w","23")),"2h-23"!=e||11.1a.1b.3Q||1==11.1q||11.3m.1g($("<1j>").1d("14-3w-1w 14-3w-2h").1A("1w","2h")),11.4i&&11.3m.2R(".14-3w-1w").1z($.1k(12(a,b){13 c=$(b).1A("1w");$(b).44(11.4i.70(!0,!0).1A("1w",c))},11)),11.1F.3t(".14-3w-1w","2m",12(a){13 b=$(a.3C).1A("1w");1e[b]()}),11.1F.3t(".14-3w-1w","bs",$.1k(12(a){13 b=$(a.3C).1A("1w"),c=b&&11["3U"+b+"5Y"];c&&11["3U"+b+"5Y"].1d("14-1w-2A-5Z")},11)).3t(".14-3w-1w","bt",$.1k(12(a){13 b=$(a.3C).1A("1w"),c=b&&11["3U"+b+"5Y"];c&&11["3U"+b+"5Y"].3M("14-1w-2A-5Z")},11))),11.1F.2R(".14-6Z-1G").1z($.1k(12(a,b){13 c=$("<78>").1d("14-6Z-1G").2U({3W:11.1a.1N}).1s({3E:0}),d=$(b).1A("1w");c.1Y("8M",12(a){a.38()}),d&&c.1A("1w",d),$(b).bu(c)},11)),11.77(a)},11))}},11),10),26 0)},77:12(a){11.2v(),2l.2W?11.2g.1Y("2m",$.1k(12(){11.2J.2w(":1Z")||11.60(),11.59()},11)):11.1u.3t(".14-1u-4h","6R",$.1k(12(){11.2J.2w(":1Z")||11.60(),11.59()},11));13 b;1e.1l&&(b=1e.1l[1e.1q-1])&&b.1a.1N==11.1a.1N&&Z.2f.21(),a&&a()},2v:12(){1h(11.1U){13 a=11.5V();11.24.2B=a.2B.1C,11.24.1U=a.1U.1C,11.4g.1s(1D(a.2B.1C)),"4e"==11.1a.1b.1u&&11.bv.1s(1D(a.1u.2B.1C)),11.3m.2x(11.5T).1s(1D(a.1W.1C));13 b=0;1h("2q"==11.1a.1b.1u&&a.25.1I&&(b=a.25.19),11.5T.1s({"79-6I":b+"1D"}),11.5S.1s(1D({17:a.4h.1C.17,19:a.4h.1C.19+b})),a.2B.1C.17>("2q"==11.1a.1b.1u?a.2g.1C.17:3N.3O().17)?11.2g.1d("14-6w-4s"):11.2g.3M("14-6w-4s"),"2q"==11.1a.1b.1u&&11.1I&&11.25.1s(1D({17:a.25.17})),11.1I){13 c=a.25.1I;11.1I[c?"1E":"1v"](),11.1F[(c?"1V":"2x")+"63"]("14-3R-1I"),11.1F[(c?"2x":"1V")+"63"]("14-4f-1I")}11.5S.2x(11.8G).1s(1D(a.1W.2X));13 d=1e.2I,e=11.24.2B;11.64={y:e.19-d.19,x:e.17-d.17},11.53=11.64.x>0||11.64.y>0,1e[(11.53?"2e":"1V")+"bw"](11.1q),1m.1x&&8>1m.1x&&"1G"==11.1a.1p&&11.1U.1s(1D(a.1W.1C))}11.1J()},1J:12(){1h(11.1U){13 a=1e.6T,b=1e.2I,c=11.24.2B,d={1Q:0,1K:0},e=11.64;11.1F.3M("14-1F-3Y"),(e.x||e.y)&&2l.5I&&11.1F.1d("14-1F-3Y"),d.1Q=e.y>0?0-a.y*e.y:.5*b.19-.5*c.19,d.1K=e.x>0?0-a.x*e.x:.5*b.17-.5*c.17,2l.2W&&(e.y>0&&(d.1Q=0),e.x>0&&(d.1K=0),11.4g.1s({1J:"bx"})),11.by=d,11.4g.1s({1Q:d.1Q+"1D",1K:d.1K+"1D"});13 f=$.1r({},d);1h(0>f.1Q&&(f.1Q=0),0>f.1K&&(f.1K=0),"2q"==11.1a.1b.1u){13 g=11.5V();1h(11.2g.1s(1D(g.2g.1C)).1s(1D(g.2g.1J)),11.1a.1I){13 h=d.1Q+g.1W.2X.1Q+g.1W.1C.19+11.5W;h>1e.2I.19-g.25.19&&(h=1e.2I.19-g.25.19);13 i=1e.2Z+d.1K+g.1W.2X.1K+11.5W;1e.2Z>i&&(i=1e.2Z),i+g.25.17>1e.2Z+g.2g.1C.17&&(i=1e.2Z),11.25.1s({1Q:h+"1D",1K:i+"1D"})}}}},bz:12(a){11.1C=a},8N:12(){},1E:12(a){1m.1x&&8>1m.1x,11.8N(),1e.8w(11.1q),11.1F.21(1,0),11.1u.21(1,0),11.60(1H,!0),11.53&&1e.8C(11.1q),11.4y(1,1i.1O(11.1a.1b.1L.1U.1E,1m.1x&&9>1m.1x?0:10),$.1k(12(){a&&a()},11))},8O:12(){11.7a&&(11.7a.1V(),11.7a=1H),11.7b&&(11.7b.bA(),11.7b=1H),11.7c&&(11.7c.1V(),11.7c=1H)},4z:12(){1e.6X(11.1q),1e.8y(11.1q),11.8O()},1v:12(a){13 b=1i.1O(11.1a.1b.1L.1U.1v||0,1m.1x&&9>1m.1x?0:10),c=11.1a.1b.1L.1U.6W?"bB":"6O";11.1F.21(1,0).4P(b,c,$.1k(12(){11.4z(),a&&a()},11))},4y:12(a,b,c){13 d=11.1a.1b.1L.1U.6W?"bC":"6L";11.1F.21(1,0).3G(b||0,a,d,c)},60:12(a,b){b?(11.2J.1E(),11.59(),"12"==$.1p(a)&&a()):11.2J.21(1,0).3G(b?0:11.1a.1b.1L.1u.1E,1,"6L",$.1k(12(){11.59(),"12"==$.1p(a)&&a()},11))},7d:12(a,b){"2q"!=11.1a.1b.1u&&(b?(11.2J.1v(),"12"==$.1p(a)&&a()):11.2J.21(1,0).4P(b?0:11.1a.1b.1L.1u.1v,"6O",12(){"12"==$.1p(a)&&a()}))},5R:12(){11.5a&&(4X(11.5a),11.5a=1H)},59:12(){11.5R(),11.5a=4B($.1k(12(){11.7d()},11),11.1a.1b.1L.1u.4a)},bD:12(){11.5R(),11.5a=4B($.1k(12(){11.7d()},11),11.1a.1b.1L.1u.4a)}}),$.1r(6e.3a,{1M:12(){11.2D={},11.65=0},2e:12(a,b,c){1h("4r"==$.1p(a)&&11.2S(a),"12"==$.1p(a)){1X(c=b,b=a;11.2D["8P"+11.65];)11.65++;a="8P"+11.65}11.2D[a]=1t.4B($.1k(12(){b&&b(),11.2D[a]=1H,4v 11.2D[a]},11),c)},2a:12(a){1c 11.2D[a]},2S:12(a){a||($.1z(11.2D,$.1k(12(a,b){1t.4X(b),11.2D[a]=1H,4v 11.2D[a]},11)),11.2D={}),11.2D[a]&&(1t.4X(11.2D[a]),11.2D[a]=1H,4v 11.2D[a])}}),$.1r(6f.3a,{1M:12(){11.7e={}},2e:12(a,b){11.7e[a]=b},2a:12(a){1c 11.7e[a]||!1}}),$.1r(4o.3a,{1M:12(a){13 b=1T[1]||{},d={};1h("4r"==$.1p(a))a={1N:a};3c 1h(a&&1==a.7u){13 c=$(a);a={1f:c[0],1N:c.2U("8a"),1I:c.1A("2b-1I"),4E:c.1A("2b-4E"),5b:c.1A("2b-5b"),1p:c.1A("2b-1p"),1b:c.1A("2b-1b")&&7f("({"+c.1A("2b-1b")+"})")||{}}}1h(a&&(a.5b||(a.5b=5p(a.1N)),!a.1p)){13 d=5o(a.1N);a.5c=d,a.1p=d.1p}1c a.5c||(a.5c=5o(a.1N)),a.1b=a&&a.1b?$.1r(!0,$.1r({},b),$.1r({},a.1b)):$.1r({},b),a.1b=Y.6C(a.1b,a.1p,a.5c),$.1r(11,a),11}});13 ba={2a:12(a,b,c){"12"==$.1p(b)&&(c=b,b={}),b=$.1r({66:!0,1p:!1,bE:bF},b||{});13 d=ba.1P.2a(a),e=b.1p||5o(a).1p,f={1p:e,4S:c};1h(!d&&"1G"==e){13 g;(g=ba.3S.2a(a))&&g.1C&&(d=g,ba.1P.2e(a,g.1C,g.1A))}1h(d)c&&c($.1r({},d.1C),d.1A);3c 3F(b.66&&ba.2f.2S(a),e){2F"1G":13 h=2t 8Q;h.4F=12(){h.4F=12(){},d={1C:{17:h.17,19:h.19}},f.1G=h,ba.1P.2e(a,d.1C,f),b.66&&ba.2f.2S(a),c&&c(d.1C,f)},h.3W=a,b.66&&ba.2f.2e(a,{1G:h,1p:e})}}};ba.7g=12(){1c 11.1M.2T(11,B.2V(1T))},$.1r(ba.7g.3a,{1M:12(){11.1P=[]},2a:12(a){1X(13 b=1H,c=0;11.1P.1B>c;c++)11.1P[c]&&11.1P[c].1N==a&&(b=11.1P[c]);1c b},2e:12(a,b,c){11.1V(a),11.1P.2y({1N:a,1C:b,1A:c})},1V:12(a){1X(13 b=0;11.1P.1B>b;b++)11.1P[b]&&11.1P[b].1N==a&&4v 11.1P[b]},bG:12(a){13 b=2a(a.1N);b?$.1r(b,a):11.1P.2y(a)}}),ba.1P=2t ba.7g,ba.4n=12(){1c 11.1M.2T(11,B.2V(1T))},$.1r(ba.4n.3a,{1M:12(){11.1P=[]},2e:12(a,b){11.2S(a),11.1P.2y({1N:a,1A:b})},2a:12(a){1X(13 b=1H,c=0;11.1P.1B>c;c++)11.1P[c]&&11.1P[c].1N==a&&(b=11.1P[c]);1c b},2S:12(a){1X(13 b=11.1P,c=0;b.1B>c;c++)1h(b[c]&&b[c].1N==a&&b[c].1A){13 d=b[c].1A;3F(d.1p){2F"1G":d.1G&&d.1G.4F&&(d.1G.4F=12(){})}4v b[c]}}}),ba.2f=2t ba.4n,ba.51=12(a,b,c){1h("12"==$.1p(b)&&(c=b,b={}),b=$.1r({6S:!1},b||{}),!b.6S||!ba.3S.2a(a)){13 d;1h((d=ba.3S.2a(a))&&d.1C)1c"12"==$.1p(c)&&c($.1r({},d.1C),d.1A),26 0;13 e={1N:a,1A:{1p:"1G"}},f=2t 8Q;e.1A.1G=f,f.4F=12(){f.4F=12(){},e.1C={17:f.17,19:f.19},"12"==$.1p(c)&&c(e.1C,e.1A)},ba.3S.1P.2x(e),f.3W=a}},ba.3S={2a:12(a){1c ba.3S.1P.2a(a)},8L:12(a){13 b=11.2a(a);1c b&&b.1C}},ba.3S.1P=12(){12 b(b){1X(13 c=1H,d=0,e=a.1B;e>d;d++)a[d]&&a[d].1N&&a[d].1N==b&&(c=a[d]);1c c}12 c(b){a.2y(b)}13 a=[];1c{2a:b,2x:c}}();13 bb={1M:12(a){11.1f=a,11.2p=[],11.2n={1o:{19:0,34:0},1n:{19:0}},11.1n=11.1f.2R(".14-1n:4O"),11.2O(),11.1v(),11.3H()},2O:12(){11.1n.1g(11.1W=$("<1j>").1d("14-1n-1W").1g(11.5d=$("<1j>").1d("14-1n-5d").1g(11.4k=$("<1j>").1d("14-1n-1w 14-1n-1w-2h").1g(11.5U=$("<1j>").1d("14-1n-1w-2A").1g($("<1j>").1d("14-1n-1w-2A-2M")).1g($("<1j>").1d("14-1n-1w-2A-3d")))).1g(11.4m=$("<1j>").1d("14-1n-bH").1g(11.3r=$("<1j>").1d("14-1n-3r"))).1g(11.4j=$("<1j>").1d("14-1n-1w 14-1n-1w-23").1g(11.4D=$("<1j>").1d("14-1n-1w-2A").1g($("<1j>").1d("14-1n-1w-2A-2M")).1g($("<1j>").1d("14-1n-1w-2A-3d")))))),11.2v()},3H:12(){11.5d.3t(".14-1o","2m",$.1k(12(a){a.2L();13 b=$(a.3C).6H(".14-1o")[0],c=-1;11.5d.2R(".14-1o").1z(12(a,d){d==b&&(c=a+1)}),c&&(11.7h(c),Z.3j(c))},11)),11.5d.1Y("2m",12(a){a.2L()}),11.4k.1Y("2m",$.1k(11.8R,11)),11.4j.1Y("2m",$.1k(11.8S,11)),2l.2W&&X(11.1W,$.1k(12(a){11[("1K"==a?"23":"2h")+"bI"]()},11),!1)},2H:12(a){11.2S(),11.2p=[],$.1z(a,$.1k(12(a,b){11.2p.2y(2t 6g(11.3r,b,a+1))},11)),1m.1x&&7>1m.1x||11.2v()},2S:12(){$.1z(11.2p,12(a,b){b.1V()}),11.2p=[],11.1q=-1,11.3y=-1},3e:12(){13 a=Z.1f,b=Z.3f,c=11.2n,d=a.2w(":1Z");d||a.1E();13 e=b.2w(":1Z");e||b.1E();13 f=11.1n.5O()-(3V(11.1n.1s("79-1Q"))||0)-(3V(11.1n.1s("79-6I"))||0);c.1o.19=f;13 g=11.3r.2R(".14-1o:4O"),h=!!g[0],i=0;h||11.4m.1g(g=$("<1j>").1d("14-1o").1g($("<1j>").1d("14-1o-1W"))),i=3V(g.1s("2X-1K")),h||g.1V(),c.1o.34=f+2*i,c.1n.19=11.1n.5O(),c.3l={2h:11.4k.34(!0),23:11.4j.34(!0)};13 j=3N.3O().17,k=c.1o.34,l=11.2p.1B;c.3l.2o=l*k/j>1;13 m=j,n=c.3l.2h+c.3l.23;c.3l.2o&&(m-=n),m=1i.8T(m/k)*k;13 o=l*k;m>o&&(m=o);13 p=m+(c.3l.2o?n:0);c.3T=m/k,11.5e="67",1>=c.3T&&(m=j,p=j,c.3l.2o=!1,11.5e="6E"),c.7i=1i.5f(l*k/m),c.1n.17=m,c.1W={17:p},e||b.1v(),d||a.1v()},4V:12(){11.7j=!0},5N:12(){11.7j=!1},2o:12(){1c!11.7j},1E:12(){2>11.2p.1B||(11.5N(),11.1n.1E(),11.3v=!0)},1v:12(){11.4V(),11.1n.1v(),11.3v=!1},1Z:12(){1c!!11.3v},2v:12(){11.3e();13 a=11.2n;$.1z(11.2p,12(a,b){b.2v()}),11.4k[a.3l.2o?"1E":"1v"](),11.4j[a.3l.2o?"1E":"1v"]();13 b=a.1n.17;1m.1x&&9>1m.1x&&(Z.2Q.2S("8U-8V-1n"),Z.2Q.2e("8U-8V-1n",$.1k(12(){11.3e();13 b=a.1n.17;11.4m.1s({17:b+"1D"}),11.3r.1s({17:11.2p.1B*a.1o.34+1+"1D"})},11),bJ)),11.4m.1s({17:b+"1D"}),11.3r.1s({17:11.2p.1B*a.1o.34+1+"1D"});13 c=a.1W.17+1;1h(11.1W.1s({17:c+"1D","2X-1K":-.5*c+"1D"}),11.4k.2x(11.4j).1s({19:a.1o.19+"1D"}),11.1q&&11.4N(11.1q,!0),1m.1x&&9>1m.1x){13 d=Z.1f,e=Z.3f,f=d.2w(":1Z");f||d.1E();13 g=e.2w(":1Z");g||e.1E(),11.4m.19("2r%"),11.4m.1s({19:11.4m.5O()+"1D"}),11.1n.2R(".14-1o-28-3P").1v(),g||e.1v(),f||d.1v()}},7k:12(a){1h(!(1>a||a>11.2n.7i||a==11.3y)){13 b=11.2n.3T*(a-1)+1;11.4N(b)}},8R:12(){11.7k(11.3y-1)},8S:12(){11.7k(11.3y+1)},bK:12(){13 a=3N.3O();1c a},3j:12(a){1h(!(1m.1x&&7>1m.1x)){13 b=0>11.1q;1>a&&(a=1);13 c=11.2p.1B;a>c&&(a=c),11.1q=a,11.7h(a),("67"!=11.5e||11.3y!=1i.5f(a/11.2n.3T))&&11.4N(a,b)}},4N:12(a,b){11.3e();13 c,d=3N.3O().17,e=.5*d,f=11.2n.1o.34;1h("67"==11.5e){13 g=1i.5f(a/11.2n.3T);11.3y=g,c=-1*f*(11.3y-1)*11.2n.3T;13 h="14-1n-1w-2A-56";11.5U[(2>g?"2x":"1V")+"63"](h),11.4D[(g>=11.2n.7i?"2x":"1V")+"63"](h)}3c c=e+-1*(f*(a-1)+.5*f);13 i=1e.1l&&1e.1l[1e.1q-1];11.3r.21(1,0).bL({1K:c+"1D"},b?0:i?i.1a.1b.1L.1n.3r:0,$.1k(12(){11.8W()},11))},8W:12(){13 a,b;1h(11.1q&&11.2n.1o.34&&!(1>11.2p.1B)){1h("67"==11.5e){1h(1>11.3y)1c;a=(11.3y-1)*11.2n.3T+1,b=1i.2z(a-1+11.2n.3T,11.2p.1B)}3c{13 c=1i.5f(3N.3O().17/11.2n.1o.34);a=1i.1O(1i.8T(1i.1O(11.1q-.5*c,0)),1),b=1i.5f(1i.2z(11.1q+.5*c)),b>11.2p.1B&&(b=11.2p.1B)}1X(13 d=a;b>=d;d++)11.2p[d-1].2H()}},7h:12(a){$.1z(11.2p,12(a,b){b.8X()});13 b=a&&11.2p[a-1];b&&b.8Y()},bM:12(){11.1q&&11.3j(11.1q)}};$.1r(6g.3a,{1M:12(a,b,c){11.1f=a,11.1a=b,11.bN={},11.1q=c,11.2O()},2O:12(){13 a=11.1a.1b;11.1f.1g(11.1o=$("<1j>").1d("14-1o").1g(11.8Z=$("<1j>").1d("14-1o-1W"))),"1G"==11.1a.1p&&11.1o.1d("14-2H-1o").1A("1o",{1a:11.1a,3W:a.1o||11.1a.1N});13 b=a.1o&&a.1o.3d;b&&11.1o.1g($("<1j>").1d("14-1o-3d 14-1o-3d-"+b));13 c;11.1o.1g(c=$("<1j>").1d("14-1o-28").1g($("<1j>").1d("14-1o-28-2M")).1g(11.2f=$("<1j>").1d("14-1o-2f").1g($("<1j>").1d("14-1o-2f-2M")).1g($("<1j>").1d("14-1o-2f-3d"))).1g($("<1j>").1d("14-1o-28-3P"))),11.1o.1g($("<1j>").1d("14-1o-bO"))},1V:12(){11.1o.1V(),11.1o=1H,11.bP=1H},2H:12(){1h(!11.4l&&!11.3x&&bb.1Z()){11.3x=!0;13 a=11.1a.1b.1o,b=a&&"6z"==$.1p(a)?11.1a.1N:a||11.1a.1N;11.5g=b,b&&("91"==11.1a.1p?$.bQ("bR://91.7T/bS/bT/bU/"+11.1a.5c.69+".bV?4S=?",$.1k(12(a){a&&a[0]&&a[0].92?(11.5g=a[0].92,ba.51(11.5g,{1p:"1G"},$.1k(11.7l,11))):(11.4l=!0,11.3x=!1,11.2f.21(1,0).4a(11.1a.1b.1L.1n.4a).3G(11.1a.1b.1L.1n.2H,0))},11)):ba.51(11.5g,{1p:"1G"},$.1k(11.7l,11)))}},7l:12(a){11.1o&&(11.4l=!0,11.3x=!1,11.24=a,11.1G=$("<78>").2U({3W:11.5g}),11.8Z.44(11.1G),11.2v(),11.2f.21(1,0).4a(11.1a.1b.1L.1n.4a).3G(11.1a.1b.1L.1n.2H,0))},2v:12(){13 a=bb.2n.1o.19;1h(11.1o.1s({17:a+"1D",19:a+"1D"}),11.1G){13 d,b={17:a,19:a},c=1i.1O(b.17,b.19),e=$.1r({},11.24);1h(e.17>b.17&&e.19>b.19){d=4c.4d(e,{3k:b});13 f=1,g=1;d.17<b.17&&(f=b.17/d.17),d.19<b.19&&(g=b.19/d.19);13 h=1i.1O(f,g);h>1&&(d.17*=h,d.19*=h),$.1z("17 19".3z(" "),12(a,b){d[b]=1i.3o(d[b])})}3c d=4c.4d(e.17<b.17||e.19<b.19?{17:c,19:c}:b,{3k:11.24});13 i=1i.3o(.5*b.17-.5*d.17),j=1i.3o(.5*b.19-.5*d.19);11.1G.1s(1D(d)).1s(1D({1Q:j,1K:i}))}},8Y:12(){11.1o.1d("14-1o-5Z")},8X:12(){11.1o.3M("14-1o-5Z")}});13 bc={1E:12(d){13 e=1T[1]||{},1J=1T[2];1T[1]&&"7L"==$.1p(1T[1])&&(1J=1T[1],e=Y.6C({}));13 f=[],93;3F(93=$.1p(d)){2F"4r":2F"6G":13 g=2t 4o(d,e),5h="1A-2b-4E-1b";1h(g.4E){1h(3U.6i(d)){13 h=$(\'.2b[1A-2b-4E="\'+$(d).1A("2b-4E")+\'"]\'),j={};h.bW("["+5h+"]").1z(12(i,a){$.1r(j,7f("({"+($(a).2U(5h)||"")+"})"))}),h.1z(12(a,b){1J||b!=d||(1J=a+1),f.2y(2t 4o(b,$.1r({},j,e)))})}}3c{13 j={};3U.6i(d)&&$(d).2w("["+5h+"]")&&($.1r(j,7f("({"+($(d).2U(5h)||"")+"})")),g=2t 4o(d,$.1r({},j,e))),f.2y(g)}8m;2F"bX":$.1z(d,12(a,b){13 c=2t 4o(b,e);f.2y(c)})}(!1J||1>1J)&&(1J=1),1J>f.1B&&(1J=f.1B),1e.6T||1e.3K({x:0,y:0}),Z.2H(f,1J,{4S:12(){Z.1E(12(){})}})}};$.1r(2E,{1M:12(){W.7W("2c"),Z.1M()}});13 bd={1G:{94:"bY bZ c0 c1 c2",96:12(a){1c $.8A(5p(a),11.94.3z(" "))>-1},1A:12(a){1c 11.96()?{5b:5p(a)}:!1}}};1m.4q&&3>1m.4q&&$.1z(Z,12(a,b){"12"==$.1p(b)&&(Z[a]=12(){1c 11})}),1t.2E=2E,$(2s).c3(12(){2E.1M()})})(2c);',62,748,'|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||this|function|var|fr|||width||height|view|options|return|addClass|Frames|element|append|if|Math|div|proxy|_frames|Browser|thumbnails|thumbnail|type|_position|extend|css|window|ui|hide|side|IE|sfcc|each|data|length|dimensions|px|show|frame|image|null|caption|position|left|effects|initialize|url|max|cache|top|105|_m|arguments|content|remove|wrapper|for|bind|visible||stop|116|next|_dimensions|info|void||overlay|101|get|fresco|jQuery||set|loading|box|previous|114|110|111|Support|click|_vars|enabled|_thumbnails|outside|100|document|new|className|resize|is|add|push|min|button|spacer|108|_timeouts|Fresco|case|115|load|_boxDimensions|ui_wrapper|109|stopPropagation|background|fit|build|close|timeouts|find|clear|apply|attr|call|mobileTouch|margin|states|_sideWidth||_tracking|||outerWidth||||preventDefault||prototype|indexOf|else|icon|updateVars|bubble|104|102|112|setPosition|bounds|sides|box_wrapper|radian|round|color|controls|slide|Window|delegate|visibility|_visible|onclick|_loading|_page|split|deepExtendClone|originalEvent|target|charAt|opacity|switch|fadeTo|startObserving|queues|frames|setXY|117|removeClass|Bounds|viewport|border|loop|no|preloaded|ipp|_|parseInt|src|scripts|touch|pageX|||pageY|skin|prepend||||||delay|queue|Fit|within|inside|has|box_spacer|padder|download_image|_next|_previous|_loaded|_thumbs|Loading|View|body|Android|string|swipe|skins|defaultSkin|delete|setExpression|setSkin|setOpacity|_reset|121|setTimeout|_s|_next_button|group|onload|in|120|documentElement|Color|hex2fill|Canvas|radius|moveTo|first|fadeOut|Keyboard|onClick|callback|overlapping|119|disable|keyCode|clearTimeout|_handleTracking|getSurroundingIndexes||preload||_track|||disabled|_loadTimer||startUITimer|_ui_timer|extension|_data|slider|_mode|ceil|_url|_dgo|String|rs|substr|180|warn|deepExtend|getURIData|detectExtension|toLowerCase|mousewheel|parseFloat|WebKit|MobileSafari|IEMobile|substring|arc|before|style|right|touches|hasClass|none|absolute|scrollTop|scrollLeft|offset|scroll|class|views|118|103|enable|innerHeight|updateDimensions|_tracking_timer|clearUITimer|box_padder|box_outer_border|_previous_button|getLayout|_border|vertical|_button|active|showUI|||Class|overlap|_count|track|page|identify|id|PI|console|Overlay|Frame|Timeouts|States|Thumbnail|match|isElement|Opera|opera|Chrome|join|G_vmlCanvasManager|canvas|expand|required|available|createElement|getContext|pointerEvents|abs|prevent|initialTypeOptions|both|boolean|touchEffects|keyboard|create|draw|center|showhide|object|closest|bottom|removeAttr|stopQueues|easeInSine|_hide|after|easeOutSine|fetchOptions|getKeyByKeyCode|mousemove|once|_xyp|_pos|_whileVisible|sync|removeTracking|_interval_load|download|clone|info_padder|span|_getInfoHeight|_fit||clearLoadTimer|afterLoad|img|padding|player_iframe|player|player_div|hideUI|_states|eval|Cache|setActive|pages|_disabled|moveToPage|_afterLoad|fromCharCode|test|getUID|constructor|replace|wheelDelta|detail|Array|nodeType|parentNode|RegExp|version|AppleWebKit|Gecko|ChromeMobile|CrMo|navigator|red|green|blue|000|fff|init|drawRoundedRectangle|fillRect|number|x1|y1|x2|y2|dPA|fillStyle|script|com|pow|easing|check|Za|notified|prefix|toUpperCase|DocumentTouch|unbind|Date|getTime|easeInOutSine|getScrollDimensions|start|update|skinless|href|hideOverlapping|embed|wmode|value|transparent|restoreOverlapping|_show|hideAll|esc|onkeydown|onkeyup|break|uis|handleTracking|clearTrackingTimer|startTracking|stopTracking|clearLoads|preloadSurroundingImages|mayPrevious|mayNext|setVisible|isVisible|setHidden|grep|inArray|setXYP|setTracking|isTracking|clearLoad|html|ui_padder|_spacing|horizontal|320|568|getDimensions|dragstart|_preShow|_postHide|timeout_|Image|previousPage|nextPage|floor|ie|resizing|loadCurrentPage|deactivate|activate|thumbnail_wrapper||vimeo|thumbnail_medium|object_type|extensions||detect||||zA|toString|pyth|sqrt|degrees|log|Object|Event|trigger|isPropagationStopped|isDefaultPrevented|DOMMouseScroll|slice|isAttached|exec|attachEvent|MSIE|KHTML|rv|Apple|Mobile|Safari|userAgent|undefined|rgba|255|360|hue|saturation|brightness|0123456789abcdef|hex2rgb|getSaturatedBW|initElement|mergedCorner|beginPath|closePath|fill|createFillStyle|toFixed|isArray|Gradient|addColorStops|createLinearGradient|05|explorercanvas|googlecode|svn|trunk|excanvas|js|Quad|Cubic|Quart|Quint|Expo|Sine|cos|easeIn|easeOut|easeInOut|fn|jquery|z_|z0|requires|Webkit|Moz|ms|Khtml|try|ontouchstart|instanceof|catch|prefixed|Win|Mac|Linux|platform|one|stopImmediatePropagation|touchend|touchmove|touchstart|1e3|IE6|base|reset|setOptions|533|dela|oldIE|ltIE|mobile|setDefaultSkin|currentTarget|106|select|param|name|hidden|restoreOverlappingWithinContent|fs|random|122|0000099999909999009999900999000999000999|00000900000090009090000090009090009090009|00000900000090009090000090000090000090009|00000999990099990099990009990090000090009|00000900000090900090000000009090000090009|00000900000090090090000090009090009090009|0000090000009000909999900999000999000999000000|900|107|123|125|4200|150|_pinchZoomed|innerWidth|keydown|keyup|orientationchange|pn|||||hideAllBut|clearTracking|_scrollbarWidth|scrollbarWidth|clearInterval|outer|spacers|wrappers|padders|info_background|text|smart|spacing|_max|mouseenter|mouseleave|replaceWith|ui_spacer|Tracking|relative|_style|setDimensions|destroy|easeInQuad|easeOutQuart|hideUIDelayed|lifetime|3e5|inject|thumbs|Page|500|adjustToViewport|animate|refresh|_dimension|state|thumbnail_image|getJSON|http|api|v2|video|json|filter|array|bmp|gif|jpeg|jpg|png|ready'.split('|'),0,{}));




/*//Private variables
var colsDefault = 0;
var rowsDefault = 0;
//var rowsCounter = 0;

//Private functions
function setDefaultValues(txtArea)
{
	colsDefault = txtArea.cols;
	rowsDefault = txtArea.rows;
	//rowsCounter = document.getElementById("rowsCounter");
}

function bindEvents(txtArea)
{
	txtArea.onkeyup = function() {
		grow(txtArea);
	}
}

//Helper functions
function grow(txtArea)
{
    var linesCount = 0;
    var lines = txtArea.value.split('\n');

    for (var i=lines.length-1; i>=0; --i)
    {
        linesCount += Math.floor((lines[i].length / colsDefault) + 1);
    }

    if (linesCount >= rowsDefault)
        txtArea.rows = linesCount + 1;
	else
        txtArea.rows = rowsDefault;
	//rowsCounter.innerHTML = linesCount + " | " + txtArea.rows;
}

//Public Method
jQuery.fn.autoGrow = function(){
	return this.each(function(){
		setDefaultValues(this);
		bindEvents(this);
	});
};
*/
(function($) {

    /*
     * Auto-growing textareas; technique ripped from Facebook
     */
    $.fn.autoGrow = function(options) {
        
        this.filter('textarea').each(function() {
            
            var $this       = $(this),
                minHeight   = $this.height(),
                lineHeight  = $this.css('lineHeight');
            
            var shadow = $('<div></div>').css({
                position:   'absolute',
                top:        -10000,
                left:       -10000,
                width:      $(this).width(),
                fontSize:   $this.css('fontSize'),
                fontFamily: $this.css('fontFamily'),
                lineHeight: $this.css('lineHeight'),
                resize:     'none'
            }).appendTo(document.body);
            
            var update = function() {
                
                var val = this.value.replace(/</g, '&lt;')
                                    .replace(/>/g, '&gt;')
                                    .replace(/&/g, '&amp;')
                                    .replace(/\n/g, '<br/>');
                
                shadow.html(val);
                $(this).css('height', Math.max(shadow.height() + 20, minHeight));
            }
            
            $(this).change(update).keyup(update).keydown(update);
            
            update.apply(this);
            
        });
        
        return this;
        
    }
    
})(jQuery);


/**
 * BxSlider v4.1 - Fully loaded, responsive content slider
 * http://bxslider.com
 *
 * Copyright 2012, Steven Wanderski - http://stevenwanderski.com - http://bxcreative.com
 * Written while drinking Belgian ales and listening to jazz
 *
 * Released under the WTFPL license - http://sam.zoy.org/wtfpl/
 */
(function(e){var t={},n={mode:"horizontal",slideSelector:"",infiniteLoop:!0,hideControlOnEnd:!1,speed:500,easing:null,slideMargin:0,startSlide:0,randomStart:!1,captions:!1,ticker:!1,tickerHover:!1,adaptiveHeight:!1,adaptiveHeightSpeed:500,video:!1,useCSS:!0,preloadImages:"visible",touchEnabled:!0,swipeThreshold:50,oneToOneTouch:!0,preventDefaultSwipeX:!0,preventDefaultSwipeY:!1,pager:!0,pagerType:"full",pagerShortSeparator:" / ",pagerSelector:null,buildPager:null,pagerCustom:null,controls:!0,nextText:"Next",prevText:"Prev",nextSelector:null,prevSelector:null,autoControls:!1,startText:"Start",stopText:"Stop",autoControlsCombine:!1,autoControlsSelector:null,auto:!1,pause:4e3,autoStart:!0,autoDirection:"next",autoHover:!1,autoDelay:0,minSlides:1,maxSlides:1,moveSlides:0,slideWidth:0,onSliderLoad:function(){},onSlideBefore:function(){},onSlideAfter:function(){},onSlideNext:function(){},onSlidePrev:function(){}};e.fn.bxSlider=function(s){if(0!=this.length){if(this.length>1)return this.each(function(){e(this).bxSlider(s)}),this;var o={},r=this;t.el=this;var a=e(window).width(),l=e(window).height(),d=function(){o.settings=e.extend({},n,s),o.settings.slideWidth=parseInt(o.settings.slideWidth),o.children=r.children(o.settings.slideSelector),o.children.length<o.settings.minSlides&&(o.settings.minSlides=o.children.length),o.children.length<o.settings.maxSlides&&(o.settings.maxSlides=o.children.length),o.settings.randomStart&&(o.settings.startSlide=Math.floor(Math.random()*o.children.length)),o.active={index:o.settings.startSlide},o.carousel=o.settings.minSlides>1||o.settings.maxSlides>1,o.carousel&&(o.settings.preloadImages="all"),o.minThreshold=o.settings.minSlides*o.settings.slideWidth+(o.settings.minSlides-1)*o.settings.slideMargin,o.maxThreshold=o.settings.maxSlides*o.settings.slideWidth+(o.settings.maxSlides-1)*o.settings.slideMargin,o.working=!1,o.controls={},o.interval=null,o.animProp="vertical"==o.settings.mode?"top":"left",o.usingCSS=o.settings.useCSS&&"fade"!=o.settings.mode&&function(){var e=document.createElement("div"),t=["WebkitPerspective","MozPerspective","OPerspective","msPerspective"];for(var i in t)if(void 0!==e.style[t[i]])return o.cssPrefix=t[i].replace("Perspective","").toLowerCase(),o.animProp="-"+o.cssPrefix+"-transform",!0;return!1}(),"vertical"==o.settings.mode&&(o.settings.maxSlides=o.settings.minSlides),c()},c=function(){if(r.wrap('<div class="bx-wrapper"><div class="bx-viewport"></div></div>'),o.viewport=r.parent(),o.loader=e('<div class="bx-loading" />'),o.viewport.prepend(o.loader),r.css({width:"horizontal"==o.settings.mode?215*o.children.length+"%":"auto",position:"relative"}),o.usingCSS&&o.settings.easing?r.css("-"+o.cssPrefix+"-transition-timing-function",o.settings.easing):o.settings.easing||(o.settings.easing="swing"),v(),o.viewport.css({width:"100%",overflow:"hidden",position:"relative"}),o.viewport.parent().css({maxWidth:u()}),o.children.css({"float":"horizontal"==o.settings.mode?"left":"none",listStyle:"none",position:"relative"}),o.children.width(p()),"horizontal"==o.settings.mode&&o.settings.slideMargin>0&&o.children.css("marginRight",o.settings.slideMargin),"vertical"==o.settings.mode&&o.settings.slideMargin>0&&o.children.css("marginBottom",o.settings.slideMargin),"fade"==o.settings.mode&&(o.children.css({position:"absolute",zIndex:0,display:"none"}),o.children.eq(o.settings.startSlide).css({zIndex:50,display:"block"})),o.controls.el=e('<div class="bx-controls" />'),o.settings.captions&&E(),o.settings.infiniteLoop&&"fade"!=o.settings.mode&&!o.settings.ticker){var t="vertical"==o.settings.mode?o.settings.minSlides:o.settings.maxSlides,i=o.children.slice(0,t).clone().addClass("bx-clone"),n=o.children.slice(-t).clone().addClass("bx-clone");r.append(i).prepend(n)}o.active.last=o.settings.startSlide==f()-1,o.settings.video&&r.fitVids();var s=o.children.eq(o.settings.startSlide);"all"==o.settings.preloadImages&&(s=r.children()),o.settings.ticker||(o.settings.pager&&w(),o.settings.controls&&T(),o.settings.auto&&o.settings.autoControls&&C(),(o.settings.controls||o.settings.autoControls||o.settings.pager)&&o.viewport.after(o.controls.el)),s.imagesLoaded(g)},g=function(){o.loader.remove(),m(),"vertical"==o.settings.mode&&(o.settings.adaptiveHeight=!0),o.viewport.height(h()),r.redrawSlider(),o.settings.onSliderLoad(o.active.index),o.initialized=!0,e(window).bind("resize",X),o.settings.auto&&o.settings.autoStart&&L(),o.settings.ticker&&W(),o.settings.pager&&M(o.settings.startSlide),o.settings.controls&&D(),o.settings.touchEnabled&&!o.settings.ticker&&O()},h=function(){var t=0,n=e();if("vertical"==o.settings.mode||o.settings.adaptiveHeight)if(o.carousel){var s=1==o.settings.moveSlides?o.active.index:o.active.index*x();for(n=o.children.eq(s),i=1;o.settings.maxSlides-1>=i;i++)n=s+i>=o.children.length?n.add(o.children.eq(i-1)):n.add(o.children.eq(s+i))}else n=o.children.eq(o.active.index);else n=o.children;return"vertical"==o.settings.mode?(n.each(function(){t+=e(this).outerHeight()}),o.settings.slideMargin>0&&(t+=o.settings.slideMargin*(o.settings.minSlides-1))):t=Math.max.apply(Math,n.map(function(){return e(this).outerHeight(!1)}).get()),t},u=function(){var e="100%";return o.settings.slideWidth>0&&(e="horizontal"==o.settings.mode?o.settings.maxSlides*o.settings.slideWidth+(o.settings.maxSlides-1)*o.settings.slideMargin:o.settings.slideWidth),e},p=function(){var e=o.settings.slideWidth,t=o.viewport.width();return 0==o.settings.slideWidth||o.settings.slideWidth>t&&!o.carousel||"vertical"==o.settings.mode?e=t:o.settings.maxSlides>1&&"horizontal"==o.settings.mode&&(t>o.maxThreshold||o.minThreshold>t&&(e=(t-o.settings.slideMargin*(o.settings.minSlides-1))/o.settings.minSlides)),e},v=function(){var e=1;if("horizontal"==o.settings.mode&&o.settings.slideWidth>0)if(o.viewport.width()<o.minThreshold)e=o.settings.minSlides;else if(o.viewport.width()>o.maxThreshold)e=o.settings.maxSlides;else{var t=o.children.first().width();e=Math.floor(o.viewport.width()/t)}else"vertical"==o.settings.mode&&(e=o.settings.minSlides);return e},f=function(){var e=0;if(o.settings.moveSlides>0)if(o.settings.infiniteLoop)e=o.children.length/x();else for(var t=0,i=0;o.children.length>t;)++e,t=i+v(),i+=o.settings.moveSlides<=v()?o.settings.moveSlides:v();else e=Math.ceil(o.children.length/v());return e},x=function(){return o.settings.moveSlides>0&&o.settings.moveSlides<=v()?o.settings.moveSlides:v()},m=function(){if(o.children.length>o.settings.maxSlides&&o.active.last&&!o.settings.infiniteLoop){if("horizontal"==o.settings.mode){var e=o.children.last(),t=e.position();S(-(t.left-(o.viewport.width()-e.width())),"reset",0)}else if("vertical"==o.settings.mode){var i=o.children.length-o.settings.minSlides,t=o.children.eq(i).position();S(-t.top,"reset",0)}}else{var t=o.children.eq(o.active.index*x()).position();o.active.index==f()-1&&(o.active.last=!0),void 0!=t&&("horizontal"==o.settings.mode?S(-t.left,"reset",0):"vertical"==o.settings.mode&&S(-t.top,"reset",0))}},S=function(e,t,i,n){if(o.usingCSS){var s="vertical"==o.settings.mode?"translate3d(0, "+e+"px, 0)":"translate3d("+e+"px, 0, 0)";r.css("-"+o.cssPrefix+"-transition-duration",i/1e3+"s"),"slide"==t?(r.css(o.animProp,s),r.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd",function(){r.unbind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd"),I()})):"reset"==t?r.css(o.animProp,s):"ticker"==t&&(r.css("-"+o.cssPrefix+"-transition-timing-function","linear"),r.css(o.animProp,s),r.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd",function(){r.unbind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd"),S(n.resetValue,"reset",0),H()}))}else{var a={};a[o.animProp]=e,"slide"==t?r.animate(a,i,o.settings.easing,function(){I()}):"reset"==t?r.css(o.animProp,e):"ticker"==t&&r.animate(a,speed,"linear",function(){S(n.resetValue,"reset",0),H()})}},b=function(){var t="";pagerQty=f();for(var i=0;pagerQty>i;i++){var n="";o.settings.buildPager&&e.isFunction(o.settings.buildPager)?(n=o.settings.buildPager(i),o.pagerEl.addClass("bx-custom-pager")):(n=i+1,o.pagerEl.addClass("bx-default-pager")),t+='<div class="bx-pager-item"><a href="" data-slide-index="'+i+'" class="bx-pager-link">'+n+"</a></div>"}o.pagerEl.html(t)},w=function(){o.settings.pagerCustom?o.pagerEl=e(o.settings.pagerCustom):(o.pagerEl=e('<div class="bx-pager" />'),o.settings.pagerSelector?e(o.settings.pagerSelector).html(o.pagerEl):o.controls.el.addClass("bx-has-pager").append(o.pagerEl),b()),o.pagerEl.delegate("a","click",z)},T=function(){o.controls.next=e('<a class="bx-next" href="">'+o.settings.nextText+"</a>"),o.controls.prev=e('<a class="bx-prev" href="">'+o.settings.prevText+"</a>"),o.controls.next.bind("click",A),o.controls.prev.bind("click",P),o.settings.nextSelector&&e(o.settings.nextSelector).append(o.controls.next),o.settings.prevSelector&&e(o.settings.prevSelector).append(o.controls.prev),o.settings.nextSelector||o.settings.prevSelector||(o.controls.directionEl=e('<div class="bx-controls-direction" />'),o.controls.directionEl.append(o.controls.prev).append(o.controls.next),o.controls.el.addClass("bx-has-controls-direction").append(o.controls.directionEl))},C=function(){o.controls.start=e('<div class="bx-controls-auto-item"><a class="bx-start" href="">'+o.settings.startText+"</a></div>"),o.controls.stop=e('<div class="bx-controls-auto-item"><a class="bx-stop" href="">'+o.settings.stopText+"</a></div>"),o.controls.autoEl=e('<div class="bx-controls-auto" />'),o.controls.autoEl.delegate(".bx-start","click",y),o.controls.autoEl.delegate(".bx-stop","click",k),o.settings.autoControlsCombine?o.controls.autoEl.append(o.controls.start):o.controls.autoEl.append(o.controls.start).append(o.controls.stop),o.settings.autoControlsSelector?e(o.settings.autoControlsSelector).html(o.controls.autoEl):o.controls.el.addClass("bx-has-controls-auto").append(o.controls.autoEl),q(o.settings.autoStart?"stop":"start")},E=function(){o.children.each(function(){var t=e(this).find("img:first").attr("title");void 0!=t&&e(this).append('<div class="bx-caption"><span>'+t+"</span></div>")})},A=function(e){o.settings.auto&&r.stopAuto(),r.goToNextSlide(),e.preventDefault()},P=function(e){o.settings.auto&&r.stopAuto(),r.goToPrevSlide(),e.preventDefault()},y=function(e){r.startAuto(),e.preventDefault()},k=function(e){r.stopAuto(),e.preventDefault()},z=function(t){o.settings.auto&&r.stopAuto();var i=e(t.currentTarget),n=parseInt(i.attr("data-slide-index"));n!=o.active.index&&r.goToSlide(n),t.preventDefault()},M=function(t){return"short"==o.settings.pagerType?(o.pagerEl.html(t+1+o.settings.pagerShortSeparator+o.children.length),void 0):(o.pagerEl.find("a").removeClass("active"),o.pagerEl.each(function(i,n){e(n).find("a").eq(t).addClass("active")}),void 0)},I=function(){if(o.settings.infiniteLoop){var e="";0==o.active.index?e=o.children.eq(0).position():o.active.index==f()-1&&o.carousel?e=o.children.eq((f()-1)*x()).position():o.active.index==o.children.length-1&&(e=o.children.eq(o.children.length-1).position()),"horizontal"==o.settings.mode?S(-e.left,"reset",0):"vertical"==o.settings.mode&&S(-e.top,"reset",0)}o.working=!1,o.settings.onSlideAfter(o.children.eq(o.active.index),o.oldIndex,o.active.index)},q=function(e){o.settings.autoControlsCombine?o.controls.autoEl.html(o.controls[e]):(o.controls.autoEl.find("a").removeClass("active"),o.controls.autoEl.find("a:not(.bx-"+e+")").addClass("active"))},D=function(){!o.settings.infiniteLoop&&o.settings.hideControlOnEnd?0==o.active.index?(o.controls.prev.addClass("disabled"),o.controls.next.removeClass("disabled")):o.active.index==f()-1?(o.controls.next.addClass("disabled"),o.controls.prev.removeClass("disabled")):(o.controls.prev.removeClass("disabled"),o.controls.next.removeClass("disabled")):1==f()&&(o.controls.prev.addClass("disabled"),o.controls.next.addClass("disabled"))},L=function(){o.settings.autoDelay>0?setTimeout(r.startAuto,o.settings.autoDelay):r.startAuto(),o.settings.autoHover&&r.hover(function(){o.interval&&(r.stopAuto(!0),o.autoPaused=!0)},function(){o.autoPaused&&(r.startAuto(!0),o.autoPaused=null)})},W=function(){var t=0;if("next"==o.settings.autoDirection)r.append(o.children.clone().addClass("bx-clone"));else{r.prepend(o.children.clone().addClass("bx-clone"));var i=o.children.first().position();t="horizontal"==o.settings.mode?-i.left:-i.top}S(t,"reset",0),o.settings.pager=!1,o.settings.controls=!1,o.settings.autoControls=!1,o.settings.tickerHover&&!o.usingCSS&&o.viewport.hover(function(){r.stop()},function(){var t=0;o.children.each(function(){t+="horizontal"==o.settings.mode?e(this).outerWidth(!0):e(this).outerHeight(!0)});var i=o.settings.speed/t,n="horizontal"==o.settings.mode?"left":"top",s=i*(t-Math.abs(parseInt(r.css(n))));H(s)}),H()},H=function(e){speed=e?e:o.settings.speed;var t={left:0,top:0},i={left:0,top:0};"next"==o.settings.autoDirection?t=r.find(".bx-clone").first().position():i=o.children.first().position();var n="horizontal"==o.settings.mode?-t.left:-t.top,s="horizontal"==o.settings.mode?-i.left:-i.top,a={resetValue:s};S(n,"ticker",speed,a)},O=function(){o.touch={start:{x:0,y:0},end:{x:0,y:0}},o.viewport.bind("touchstart",N)},N=function(e){if(o.working)e.preventDefault();else{o.touch.originalPos=r.position();var t=e.originalEvent;o.touch.start.x=t.changedTouches[0].pageX,o.touch.start.y=t.changedTouches[0].pageY,o.viewport.bind("touchmove",B),o.viewport.bind("touchend",Q)}},B=function(e){var t=e.originalEvent,i=Math.abs(t.changedTouches[0].pageX-o.touch.start.x),n=Math.abs(t.changedTouches[0].pageY-o.touch.start.y);if(3*i>n&&o.settings.preventDefaultSwipeX?e.preventDefault():3*n>i&&o.settings.preventDefaultSwipeY&&e.preventDefault(),"fade"!=o.settings.mode&&o.settings.oneToOneTouch){var s=0;if("horizontal"==o.settings.mode){var r=t.changedTouches[0].pageX-o.touch.start.x;s=o.touch.originalPos.left+r}else{var r=t.changedTouches[0].pageY-o.touch.start.y;s=o.touch.originalPos.top+r}S(s,"reset",0)}},Q=function(e){o.viewport.unbind("touchmove",B);var t=e.originalEvent,i=0;if(o.touch.end.x=t.changedTouches[0].pageX,o.touch.end.y=t.changedTouches[0].pageY,"fade"==o.settings.mode){var n=Math.abs(o.touch.start.x-o.touch.end.x);n>=o.settings.swipeThreshold&&(o.touch.start.x>o.touch.end.x?r.goToNextSlide():r.goToPrevSlide(),r.stopAuto())}else{var n=0;"horizontal"==o.settings.mode?(n=o.touch.end.x-o.touch.start.x,i=o.touch.originalPos.left):(n=o.touch.end.y-o.touch.start.y,i=o.touch.originalPos.top),!o.settings.infiniteLoop&&(0==o.active.index&&n>0||o.active.last&&0>n)?S(i,"reset",200):Math.abs(n)>=o.settings.swipeThreshold?(0>n?r.goToNextSlide():r.goToPrevSlide(),r.stopAuto()):S(i,"reset",200)}o.viewport.unbind("touchend",Q)},X=function(){var t=e(window).width(),i=e(window).height();(a!=t||l!=i)&&(a=t,l=i,r.redrawSlider())};return r.goToSlide=function(t,i){if(!o.working&&o.active.index!=t)if(o.working=!0,o.oldIndex=o.active.index,o.active.index=0>t?f()-1:t>=f()?0:t,o.settings.onSlideBefore(o.children.eq(o.active.index),o.oldIndex,o.active.index),"next"==i?o.settings.onSlideNext(o.children.eq(o.active.index),o.oldIndex,o.active.index):"prev"==i&&o.settings.onSlidePrev(o.children.eq(o.active.index),o.oldIndex,o.active.index),o.active.last=o.active.index>=f()-1,o.settings.pager&&M(o.active.index),o.settings.controls&&D(),"fade"==o.settings.mode)o.settings.adaptiveHeight&&o.viewport.height()!=h()&&o.viewport.animate({height:h()},o.settings.adaptiveHeightSpeed),o.children.filter(":visible").fadeOut(o.settings.speed).css({zIndex:0}),o.children.eq(o.active.index).css("zIndex",51).fadeIn(o.settings.speed,function(){e(this).css("zIndex",50),I()});else{o.settings.adaptiveHeight&&o.viewport.height()!=h()&&o.viewport.animate({height:h()},o.settings.adaptiveHeightSpeed);var n=0,s={left:0,top:0};if(!o.settings.infiniteLoop&&o.carousel&&o.active.last)if("horizontal"==o.settings.mode){var a=o.children.eq(o.children.length-1);s=a.position(),n=o.viewport.width()-a.width()}else{var l=o.children.length-o.settings.minSlides;s=o.children.eq(l).position()}else if(o.carousel&&o.active.last&&"prev"==i){var d=1==o.settings.moveSlides?o.settings.maxSlides-x():(f()-1)*x()-(o.children.length-o.settings.maxSlides),a=r.children(".bx-clone").eq(d);s=a.position()}else if("next"==i&&0==o.active.index)s=r.find(".bx-clone").eq(o.settings.maxSlides).position(),o.active.last=!1;else if(t>=0){var c=t*x();s=o.children.eq(c).position()}var g="horizontal"==o.settings.mode?-(s.left-n):-s.top;S(g,"slide",o.settings.speed)}},r.goToNextSlide=function(){if(o.settings.infiniteLoop||!o.active.last){var e=parseInt(o.active.index)+1;r.goToSlide(e,"next")}},r.goToPrevSlide=function(){if(o.settings.infiniteLoop||0!=o.active.index){var e=parseInt(o.active.index)-1;r.goToSlide(e,"prev")}},r.startAuto=function(e){o.interval||(o.interval=setInterval(function(){"next"==o.settings.autoDirection?r.goToNextSlide():r.goToPrevSlide()},o.settings.pause),o.settings.autoControls&&1!=e&&q("stop"))},r.stopAuto=function(e){o.interval&&(clearInterval(o.interval),o.interval=null,o.settings.autoControls&&1!=e&&q("start"))},r.getCurrentSlide=function(){return o.active.index},r.getSlideCount=function(){return o.children.length},r.redrawSlider=function(){o.children.add(r.find(".bx-clone")).width(p()),o.viewport.css("height",h()),o.settings.ticker||m(),o.active.last&&(o.active.index=f()-1),o.active.index>=f()&&(o.active.last=!0),o.settings.pager&&!o.settings.pagerCustom&&(b(),M(o.active.index))},r.destroySlider=function(){o.initialized&&(o.initialized=!1,e(".bx-clone",this).remove(),o.children.removeAttr("style"),this.removeAttr("style").unwrap().unwrap(),o.controls.el&&o.controls.el.remove(),o.controls.next&&o.controls.next.remove(),o.controls.prev&&o.controls.prev.remove(),o.pagerEl&&o.pagerEl.remove(),e(".bx-caption",this).remove(),o.controls.autoEl&&o.controls.autoEl.remove(),clearInterval(o.interval),e(window).unbind("resize",X))},r.reloadSlider=function(e){void 0!=e&&(s=e),r.destroySlider(),d()},d(),this}}})(jQuery),function(e,t){var i="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";e.fn.imagesLoaded=function(n){function s(){var t=e(g),i=e(h);a&&(h.length?a.reject(d,t,i):a.resolve(d)),e.isFunction(n)&&n.call(r,d,t,i)}function o(t,n){t.src===i||-1!==e.inArray(t,c)||(c.push(t),n?h.push(t):g.push(t),e.data(t,"imagesLoaded",{isBroken:n,src:t.src}),l&&a.notifyWith(e(t),[n,d,e(g),e(h)]),d.length===c.length&&(setTimeout(s),d.unbind(".imagesLoaded")))}var r=this,a=e.isFunction(e.Deferred)?e.Deferred():0,l=e.isFunction(a.notify),d=r.find("img").add(r.filter("img")),c=[],g=[],h=[];return e.isPlainObject(n)&&e.each(n,function(e,t){"callback"===e?n=t:a&&a[e](t)}),d.length?d.bind("load.imagesLoaded error.imagesLoaded",function(e){o(e.target,"error"===e.type)}).each(function(n,s){var r=s.src,a=e.data(s,"imagesLoaded");a&&a.src===r?o(s,a.isBroken):s.complete&&s.naturalWidth!==t?o(s,0===s.naturalWidth||0===s.naturalHeight):(s.readyState||s.complete)&&(s.src=i,s.src=r)}):s(),a?a.promise(r):r}}(jQuery);

 /*  jQuery UI Touch Punch 0.2.2 * jquery.ui.widget.js  *  jquery.ui.mouse.js  */
(function(b){b.support.touch="ontouchend" in document;if(!b.support.touch){return;}var c=b.ui.mouse.prototype,e=c._mouseInit,a;function d(g,h){if(g.originalEvent.touches.length>1){return;}g.preventDefault();var i=g.originalEvent.changedTouches[0],f=document.createEvent("MouseEvents");f.initMouseEvent(h,true,true,window,1,i.screenX,i.screenY,i.clientX,i.clientY,false,false,false,false,0,null);g.target.dispatchEvent(f);}c._touchStart=function(g){var f=this;if(a||!f._mouseCapture(g.originalEvent.changedTouches[0])){return;}a=true;f._touchMoved=false;d(g,"mouseover");d(g,"mousemove");d(g,"mousedown");};c._touchMove=function(f){if(!a){return;}this._touchMoved=true;d(f,"mousemove");};c._touchEnd=function(f){if(!a){return;}d(f,"mouseup");d(f,"mouseout");if(!this._touchMoved){d(f,"click");}a=false;};c._mouseInit=function(){var f=this;f.element.bind("touchstart",b.proxy(f,"_touchStart")).bind("touchmove",b.proxy(f,"_touchMove")).bind("touchend",b.proxy(f,"_touchEnd"));e.call(f);};})(jQuery);

/*html5 placeholder plugin*/
(function(g,i,d){var a="placeholder" in i.createElement("input"),e=false,j=d.fn,c=d.valHooks,l,k;if(a&&e){k=j.placeholder=function(){return this};k.input=k.textarea=true}else{k=j.placeholder=function(){var m=this;m.filter((a?"textarea":":input")+"[placeholder]").not(".placeholder").bind({"focus.placeholder":b,"blur.placeholder":f}).data("placeholder-enabled",true).trigger("blur.placeholder");return m};k.input=a;k.textarea=e;l={get:function(n){var m=d(n);return m.data("placeholder-enabled")&&m.hasClass("placeholder")?"":n.value},set:function(n,o){var m=d(n);if(!m.data("placeholder-enabled")){return n.value=o}if(o==""){n.value=o;if(n!=i.activeElement){f.call(n)}}else{if(m.hasClass("placeholder")){b.call(n,true,o)||(n.value=o)}else{n.value=o}}return m}};a||(c.input=l);e||(c.textarea=l);d(function(){d(i).delegate("form","submit.placeholder",function(){var m=d(".placeholder",this).each(b);setTimeout(function(){m.each(f)},10)})});d(g).bind("beforeunload.placeholder",function(){d(".placeholder").each(function(){this.value=""})})}function h(n){var m={},o=/^jQuery\d+$/;d.each(n.attributes,function(q,p){if(p.specified&&!o.test(p.name)){m[p.name]=p.value}});return m}function b(n,o){var m=this,p=d(m);if(m.value==p.attr("placeholder")&&p.hasClass("placeholder")){if(p.data("placeholder-password")){p=p.hide().next().show().attr("id",p.removeAttr("id").data("placeholder-id"));if(n===true){return p[0].value=o}p.focus()}else{m.value="";p.removeClass("placeholder");m==i.activeElement&&m.select()}}}function f(){var r,m=this,q=d(m),n=q,p=this.id;if(m.value==""){if(m.type=="password"){if(!q.data("placeholder-textinput")){try{r=q.clone().attr({type:"text"})}catch(o){r=d("<input>").attr(d.extend(h(this),{type:"text"}))}r.removeAttr("name").data({"placeholder-password":true,"placeholder-id":p}).bind("focus.placeholder",b);q.data({"placeholder-textinput":r,"placeholder-id":p}).before(r)}q=q.removeAttr("id").hide().prev().attr("id",p).show()}q.addClass("placeholder");q[0].value=q.attr("placeholder")}else{q.removeClass("placeholder")}}}(this,document,jQuery));
