$(document).ready(function () {
	NProgress.configure({ showSpinner: false });
    NProgress.start();
    setTimeout(function() { NProgress.done(); $('.fade')}, 500);

topNavigation();
placeholder();
iconToggle();
postContentvisible();

$('.navbar-scroll-fixed').scrollFix({
	    fixTop: 50
	});

$(document).on('click','#feoforeLogintext', function (e) {
	$('#beforeLoginPopup').dropdown('toggle'); 
    e.stopPropagation();
    e.preventDefault();
});

//customComments();


//$(document).on('focus','.comment-text', function(){
//     $(this).parents('.wall-comments').addClass('textfocused');
//});
//$(document).on('blur','.comment-text', function(){
//     $(this).parents('.wall-comments').removeClass('textfocused');
//});



//alertFunction(); //Alert Funcion
$('.nav.quick-section').click(function(event){
     event.stopPropagation();
 });


/*Start Add active class on focus & blue*/
	$(".inside").children('input').blur(function () {
        $(this).parent().children('.add-on').removeClass('input-focus');
    });

    $(".inside").children('input').focus(function () {
        $(this).parent().children('.add-on').addClass('input-focus');
    });
/*End Add active class on focus & blue*/

 /**** Scroller ****/
  if ($.fn.niceScroll){
	var mainScroller = $("html").niceScroll({
					zindex:999999,
					boxzoom:true,
					cursoropacitymin :0.5,
					cursoropacitymax :0.8,
					cursorwidth :"10px",
					cursorborder :"0px solid",
					autohidemode:false
	});
  }
    
  /**** Mobile Side Menu ****/
  if ($.fn.waypoint){  
	var $head = $('#ha-header');
	$( '.ha-waypoint' ).each( function(i) {
					var $el = $( this ),
						animClassDown = $el.data( 'animateDown' ),
						animClassUp = $el.data( 'animateUp' );

					$el.waypoint( function( direction ) {
						if( direction === 'down' && animClassDown ) {
							$head.attr('class', 'ha-header ' + animClassDown);
						}
						else if( direction === 'up' && animClassUp ){
							$head.attr('class', 'ha-header ' + animClassUp);
						}
					}, { offset: '100%' } );
				
				$('.navbar-toggle').click(function(e) {
                   $('.navbar-collapse').toggle();
                });
			
 });
}
	
	/**** Parrallax ****/
	if ($.fn.parallax){
	$('#working-section').parallax("50%", 0.1,false);
	$('#section-1').parallax("50%", 0.1,false);
	$('#section-2').parallax("60%", 0.1,false);
	$('#section-3').parallax("40%", 0.1,false);
	$('#section-4').parallax("40%", 0.1,false);
	$('#section-5').parallax("40%", 0.1,false);
	$('#section-8').parallax("40%", 0.1,false);
	$('.parrallax-element, .parrallax-background').each(function () {
		$(this).css('background-image', 'url(' + $(this).attr("data-background-image") + ')');
		$(this).css('height',  $(this).attr("data-background-height"));
		$(this).css('width',  $(this).attr("data-background-width"));
		$(this).parallax("50%", 0.1);
	});
	}	
	
	hoverTiles();
	NotificationBar();
	 
	// scrollWin();
	// alllistAnimate();
	 
      if ($(window).width() <= 980) {
			$('.feature-region li').addClass('active');	
	  }
	  if ($(window).width() <=768) {
			MobHomeMenu();		
	  }
	  
	
	if ($(window).width() >=768) {
			$(window).scroll(function(){
			//page scroll header fix
			if($(window).scrollTop() >= 80){
				//$('header').addClass('fixedHeader');
				//$('header').addClass('fadeInDown');		
			}
		else {
			//$('header').removeClass('fixedHeader');
			//$('header').removeClass('fadeInDown');	
		}
	})	
   }
	if($(window).width() <= 1024) {
	  	$('.dropdown-toggle').click(function(e) {
	        //$('.dropdown-menu ').slideDown();
			//$('.top-nav .nav.quick-section').slideUp();
    	});

	$(document).click(function(e) {
		  var target = e.target;
		  if (!$(target).is('.dropdown-toggle') && !$(target).parents().is('.dropdown-toggle')) {
			//$('.dropdown-menu').slideUp();
		  }
	});
	}
	//Non closed drop down
	 $(document).on('click','.cmn-toggle-dropdown > a', function (event) {
			$(this).parent().toggleClass("open");
		   $('.dropdown-toggle').on('click', function (event) {
			$('.cmn-toggle-dropdown').removeClass('open');
		  });
	  });
	  $(document).on('touchstart click', function (e) {
		  if (!$('.cmn-toggle-dropdown').is(e.target) && $('.cmn-toggle-dropdown').has(e.target).length === 0 && $('.open').has(e.target).length === 0) {
			$('.cmn-toggle-dropdown').removeClass('open');
		  }
	  });
	
  editCoverToggle();
  accountSettingTab(); // Account Setting Tab 
  
  	if($(window).width() < 767){
		$(document).on('click','#ulID li, #newMsz', function(){
			$('.messsag-left-col').hide();
			$('.messsag-right-col').show();
		 });
		 $('#backList').on('click',function(){
			$('.messsag-left-col').show();
			$('.messsag-right-col').hide();
		  });
	}
	else{
		//$('.messsag-left-col, .messsag-right-col').removeAttr('style','');
	}
	
  $(".poll-post").on('click', function(){        
        var thisval = $('textarea#PostContent').val();
        if(thisval == ''){
          $('textarea#PostContent').css({height:'37px'}); 
        }        
  });

	$('textarea#PostContent').focus(function(){   	 	
			var thisval = $(this).val();
				if(thisval == ''){
					$(this).animate({height:'37px'});	
				}
				else{
					return false;
				}
   	 	
	});
	
	$('textarea#PostContent').blur(function(){
		var thisval = $(this).val();
		if(thisval == ''){
			$(this).animate({height:'37px'});		
		}
		else{
			return false;
		}
	 
	});
	
	//$('.composer-inner textarea').autosize();  
});




// Place any jQuery/helper plugins in here.
/*Input placeholder*/ 
function placeholder() {
    var input = document.createElement('input');
    if (('placeholder' in input) == false) {
        $('[placeholder]').focus(function () {
            var i = $(this);
            if (i.val() == i.attr('placeholder')) {
                i.val('').removeClass('placeholder');
                if (i.hasClass('password')) {
                    i.removeClass('password');
                    this.type = 'password';
                }
            }
        }).blur(function () {
            var i = $(this);
            if (i.val() == '' || i.val() == i.attr('placeholder')) {
                if (this.type == 'password') {
                    i.addClass('password');
                    this.type = 'text';
                }
                i.addClass('placeholder').val(i.attr('placeholder'));
            }
        }).blur().parents('form').submit(function () {
            $(this).find('[placeholder]').each(function () {
                var i = $(this);
                if (i.val() == i.attr('placeholder'))
                    i.val('');
            })
        });
    }
	
	$('[type="text"], [type="password"], textarea, select.form-select').focus(function() {
		var input = $(this);
		input.parents('[data-type="focus"]').addClass('focus');
	}).blur(function() {
		var input = $(this);
		input.parents('[data-type="focus"]').removeClass('focus');
	});
}
	

/*All Top Navigation in Ipad, Iphone*/	
function topNavigation(){
		if($(window).width() <= 1024) {
	$('.top-nav > a').bind('click', function (e) {		
		if(!$(this).parent('.top-nav').find('.nav.quick-section').is(':visible')){
			$(this).parent('.top-nav').find('.nav.quick-section').show();
			$('.chat-toggler-wrap .dropdown-menu').slideUp();
		}
		else{
			$(this).parent('.top-nav').find('.nav.quick-section').hide();			
			}			
			e.stopPropagation();
	});	
	$(document.body).bind('click', function (){
			$('.top-nav > a').parent('.top-nav').find('.nav.quick-section').hide();
		})
			
	} else{
		$('.top-nav > a').unbind('click');
		$(document.body).unbind('click');
	}
	}
	
/*Tab Navigation in Iphone*/
TabNavigationFn();
function TabNavigationFn() {
$('.nav-tabs-warp > .tab-text').on('resize click',function(){
if($(window).width() <= 767) {

			if(!$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').is(':visible')){
				$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').show();	
				$(this).find('i').removeClass('fa-sort-down');	
				$(this).find('i').addClass('fa-sort-up');		
			}
			else{
				$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').hide();
				$(this).find('i').removeClass('fa-sort-up');	
				$(this).find('i').addClass('fa-sort-down');	
				}		
		}

		});
}

if($(window).width() <= 767) {
		// $('.nav-tabs-warp > .tab-text').on('click',function(){		
		// 	if(!$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').is(':visible')){
		// 		$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').show();	
		// 		$(this).find('i').removeClass('fa-sort-down');	
		// 		$(this).find('i').addClass('fa-sort-up');		
		// 	}
		// 	else{
		// 		$(this).parent('.nav-tabs-warp').find('.nav.nav-tabs').hide();
		// 		$(this).find('i').removeClass('fa-sort-up');	
		// 		$(this).find('i').addClass('fa-sort-down');	
		// 		}		
		// });	

		// $('.nav.nav-tabs li a').on('click',function(){		
		// 	var  tittleText = $(this).text();
		// 		$('.tab-text > span').text();
		// 		$('.tab-text > span').text(tittleText);
		// 		$(this).closest('.nav.nav-tabs').hide();
		// 		$(this).parents('.nav-tabs-warp').find('.tab-text > i').removeClass('fa-sort-up');
		// 		$(this).parents('.nav-tabs-warp').find('.tab-text > i').addClass('fa-sort-down');
		// });
}

/*Function for Library Tab Active*/
function setTab(tabIndex) {
	var allLinks=$('#naviDropdown > a');
	allLinks.removeClass('active');
	$(allLinks[tabIndex-1]).addClass('active');
    
    var  tittleText = $(allLinks[tabIndex-1]).text();
    $('#current-tab').text(tittleText);
}


function setTabHead(tabAttr) {
	var allLinks=$('.header-navigation-content > a');
	allLinks.removeClass('active');
	$('[data-active="'+tabAttr+'"]').addClass('active');
}


function iosInit(){
var Switch = require('ios7-switch')
        , checkbox = document.querySelector('.ios')
        , mySwitch = new Switch(checkbox);
 mySwitch.toggle();
      mySwitch.el.addEventListener('click', function(e){
        e.preventDefault();
        mySwitch.toggle();
      }, false);
//creating multiple instances
/*var Switch2 = require('ios7-switch')
        , checkbox = document.querySelector('.iosblue')
        , mySwitch2 = new Switch2(checkbox);

      mySwitch2.el.addEventListener('click', function(e){
        e.preventDefault();
        mySwitch2.toggle();
      }, false);*/
}

 //start with `NewContent` being the HTML to add to the page
  function checkVerify(){
	    var NewContent="<i class='fa fa-check-circle color-gray font28  verify'></i>"
		$(".lander-choose").click(function(){
				$(".lander-choose").find('.verify').remove();
				if (NewContent != '')
					 $(this).append(NewContent);
				$(this).click(function(){
					$('.verify').toggle();
				 });
		});
}


// Mobile Menu
 if ($(window).width() <= 768) {
function MobHomeMenu() {
    $('.mobmenu').on('click' , function(e) {
		if(!$(this).next('.tab-nav').is(':visible')){
			$(this).next('.tab-nav').slideDown();
			$(this).addClass('active');
		} else {
			$(this).next('.tab-nav').slideUp();
			$(this).removeClass("active");
		}
    	e.stopPropagation();
	});  
	
	$('.mobmenu').on('click touchstart',function(e){

		e.stopPropagation();
	});
	
	$(document).on('click touchstart',function(){
			$('.mobmenu').next('.tab-nav').slideUp();
			$('.mobmenu').removeClass("active");
			
		});
}
$(window).resize(function (){
	$('.tab-nav').removeAttr('style');
	$('.mobmenu').removeClass('active');	 
});

}

$(document).ready(function(){
	$(window).resize(function(){
		$('.imgFill').imagefill();
		$('#coverViewimg').width($(window).width());
	});
});

function hoverTiles() {
    var
      tile = $('.feature-region li')
	  
      tile.hover(function() {
        tile.removeClass('active');
        $(this).addClass('active');       
      }, function(){
        //tile.removeClass('active');
        //tile.removeClass('inactive');
      })
      
  }
  
function NotificationBar() {
	$('.wrapper > .close').on('click', function(){		
		$(this).parents('.top-header').slideUp(800);
		$(this).parents('.bottom-bar').slideUp(800);		
		})
	}

//function scrollWin(){
//		$(window).scrollY(800){}
//}


/**
 * imagefill.js
 * Author & copyright (c) 2013: John Polacek
 * johnpolacek.com
 * https://twitter.com/johnpolacek
 *
 * Dual MIT & GPL license
 *
 * Project Page: http://johnpolacek.github.io/imagefill.js
 *
 * The jQuery plugin for making images fill their containers (and be centered)
 *
 * EXAMPLE
 * Given this html:
 * <div class="container"><img src="myawesomeimage" /></div>
 * $('.container').imagefill(); // image stretches to fill container
 *
 * REQUIRES:
 * imagesLoaded - https://github.com/desandro/imagesloaded
 *
 */
 ;(function($) {

  $.fn.imagefill = function(options) {

    var $container = this,
        $img = $container.find('img').addClass('loading').css({'position':'absolute'}),
        imageAspect = 1/1,
        containersH = 0,
        containersW = 0,
        defaults = {
          runOnce: false,
          throttle : 200  // 5fps
        },
        settings = $.extend({}, defaults, options);

    // make sure container isn't position:static
    var containerPos = $container.css('position');
    $container.css({'overflow':'hidden','position':(containerPos === 'static') ? 'relative' : containerPos});

    // set containerH, containerW
    $container.each(function() {
      containersH += $(this).height();
      containersW += $(this).width();
	  
    });

    // wait for image to load, then fit it inside the container

      imageAspect = $img.width() / $img.height();
      $img.removeClass('loading');
      fitImages();
      if (!settings.runOnce) {
        checkSizeChange();
      }


    function fitImages() {

      $container.each(function() {
        imageAspect = $(this).find('img').width() / $(this).find('img').height();
        var containerW = $(this).width();
        var containerH = $(this).height();
        var containerAspect = containerW/containerH;
        if (containerAspect < imageAspect) {
          // taller
          $(this).find('img').css({
              width: 'auto',
              height: containerH,
              top:0,
              left:-(containerH*imageAspect-containerW)/2
            });
        } else {
          // wider
          $(this).find('img').css({
              width: containerW,
              height: 'auto',
              top:-(containerW/imageAspect-containerH)/2,
              left:0
            });
        }
      });
    }

    function checkSizeChange() {
      var checkW = 0,
          checkH = 0;
      $container.each(function() {
        checkH += $(this).height();
        checkW += $(this).width();
      });
      if (containersH !== checkH || containersW !== checkW) {
        fitImages();
      }
      setTimeout(checkSizeChange, settings.throttle);
    }

    // return for chaining
    return this;
  };

}(jQuery));

//		http://www.inwebson.com/jquery/blocksit-js-dynamic-grid-layout-jquery-plugin/

(function(a){var b={numOfCol:5,offsetX:5,offsetY:5,blockElement:"div"};var c,d;var e=[];if(!Array.prototype.indexOf){Array.prototype.indexOf=function(a){var b=this.length>>>0;var c=Number(arguments[1])||0;c=c<0?Math.ceil(c):Math.floor(c);if(c<0)c+=b;for(;c<b;c++){if(c in this&&this[c]===a)return c}return-1}}var f=function(){e=[];for(var a=0;a<b.numOfCol;a++){g("empty-"+a,a,0,1,-b.offsetY)}};var g=function(a,c,d,f,g){for(var h=0;h<f;h++){var i=new Object;i.x=c+h;i.size=f;i.endY=d+g+b.offsetY*2;e.push(i)}};var h=function(a,b){for(var c=0;c<b;c++){var d=i(a+c,"x");e.splice(d,1)}};var i=function(a,b){for(var c=0;c<e.length;c++){var d=e[c];if(b=="x"&&d.x==a){return c}else if(b=="endY"&&d.endY==a){return c}}};var j=function(a,b){var c=[];for(var d=0;d<b;d++){c.push(e[i(a+d,"x")].endY)}var f=Math.min.apply(Math,c);var g=Math.max.apply(Math,c);return[f,g,c.indexOf(f)]};var k=function(a){if(a>1){var b=e.length-a;var c=false;var d,f;for(var g=0;g<e.length;g++){var h=e[g];var i=h.x;if(i>=0&&i<=b){var k=j(i,a);if(!c){c=true;d=k;f=i}else{if(k[1]<d[1]){d=k;f=i}}}}return[f,d[1]]}else{d=j(0,e.length);return[d[2],d[0]]}};var l=function(a,c){if(!a.data("size")||a.data("size")<0){a.data("size",1)}else if(a.data("size")>b.numOfCol){a.data("size",b.numOfCol)}var e=k(a.data("size"));var f=d*a.data("size")-(a.outerWidth()-a.width());a.css({width:f-b.offsetX*2,left:e[0]*d,top:e[1],position:"absolute"});var i=a.outerHeight();h(e[0],a.data("size"));g(a.attr("id"),e[0],e[1],a.data("size"),i)};a.fn.BlocksIt=function(g){if(g&&typeof g==="object"){a.extend(b,g)}c=a(this);d=c.width()/b.numOfCol;f();c.children(b.blockElement).each(function(b){l(a(this),b)});var h=j(0,e.length);c.height(h[1]+b.offsetY);return this}})(jQuery)


/* Profile Page JS*/
  $(function(){

        // Find the right method, call on correct element
        function launchFullScreen(element) {
          if(element.requestFullScreen) {
            element.requestFullScreen();
          } else if(element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
          } else if(element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen();
          }
        }
        // Whack fullscreen
        function cancelFullscreen() {
          if(document.cancelFullScreen) {
            document.cancelFullScreen();
          } else if(document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if(document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
          }
        }

        /*global Element */
        (function (window, document) {
          'use strict';
            var keyboardAllowed = typeof Element !== 'undefined' && 'ALLOW_KEYBOARD_INPUT' in Element, // IE6 throws without typeof check

            fn = (function () {
              var val, valLength;
              var fnMap = [
              [
              'requestFullscreen',
              'exitFullscreen',
              'fullscreenElement',
              'fullscreenEnabled',
              'fullscreenchange',
              'fullscreenerror'
              ],
                        // new WebKit
                        [
                        'webkitRequestFullscreen',
                        'webkitExitFullscreen',
                        'webkitFullscreenElement',
                        'webkitFullscreenEnabled',
                        'webkitfullscreenchange',
                        'webkitfullscreenerror'

                        ],
                        // old WebKit (Safari 5.1)
                        [
                        'webkitRequestFullScreen',
                        'webkitCancelFullScreen',
                        'webkitCurrentFullScreenElement',
                        'webkitCancelFullScreen',
                        'webkitfullscreenchange',
                        'webkitfullscreenerror'

                        ],
                        [
                        'mozRequestFullScreen',
                        'mozCancelFullScreen',
                        'mozFullScreenElement',
                        'mozFullScreenEnabled',
                        'mozfullscreenchange',
                        'mozfullscreenerror'
                        ],
                        [
                        'msRequestFullscreen',
                        'msExitFullscreen',
                        'msFullscreenElement',
                        'msFullscreenEnabled',
                        'MSFullscreenChange',
                        'MSFullscreenError'
                        ]
                        ];
                        var i = 0;
                        var l = fnMap.length;
                        var ret = {};

                        for (; i < l; i++) {
                          val = fnMap[i];
                          if (val && val[1] in document) {
                            for (i = 0, valLength = val.length; i < valLength; i++) {
                              ret[fnMap[0][i]] = val[i];
                            }
                            return ret;
                          }
                        }
                        return false;
                      })(),

                      screenfull = {
                        request: function (elem) {
                          var request = fn.requestFullscreen;

                          elem = elem || document.documentElement;

                        // Work around Safari 5.1 bug: reports support for
                        // keyboard in fullscreen even though it doesn't.
                        // Browser sniffing, since the alternative with
                        // setTimeout is even worse.
                        if (/5\.1[\.\d]* Safari/.test(navigator.userAgent)) {
                          elem[request]();
                        } else {
                          elem[request](keyboardAllowed && Element.ALLOW_KEYBOARD_INPUT);
                        }
                      },
                      exit: function () {
                        document[fn.exitFullscreen]();
                      },
                      toggle: function (elem) {
                        if (this.isFullscreen) {
                          this.exit();
                        } else {
                          this.request(elem);
                        }
                      },
                      onchange: function () {},
                      onerror: function () {},
                      raw: fn
                    };

                    if (!fn) {
                      window.screenfull = false;
                      return;
                    }

                    Object.defineProperties(screenfull, {
                      isFullscreen: {
                        get: function () {
                          return !!document[fn.fullscreenElement];
                        }
                      },
                      element: {
                        enumerable: true,
                        get: function () {
                          return document[fn.fullscreenElement];
                        }
                      },
                      enabled: {
                        enumerable: true,
                        get: function () {
                        // Coerce to boolean in case of old WebKit
                        return !!document[fn.fullscreenEnabled];
                      }
                    }
                  });

                    document.addEventListener(fn.fullscreenchange, function (e) {
                      screenfull.onchange.call(screenfull, e);
                // Toggle Function
                $('.theater-window').toggleClass('fullscreen');
              });
                    document.addEventListener(fn.fullscreenerror, function (e) {
                      screenfull.onerror.call(screenfull, e);
                    });
                    window.screenfull = screenfull;
                  })(window, document);

                  $('#launchFullscreen').on('click', function(){
                    launchFullScreen(document.documentElement);
                  });
                  $('#cancelFullscreen').on('click', function(){
                    cancelFullscreen();
                  });
        // $(document).keydown(function (e) {  
        //      if (e.keyCode == 27) {  
        //         //closeFullView(FullView);  
        //      }  
        //  });
});

var FullView;
function openFullView(FullViewId){
  $("#"+FullViewId).show();
  FullView = FullViewId;
  $('body').css({overflow:'hidden'});
}
function closeFullView(FullViewId){
  $("#"+FullViewId).hide();
  $('body').css({overflow:''});
}

$(function(){
  setTimeout(function(){
    $('#imgCoverAdj').imagefill();
    $('#naviTrigger').on('click', function(){
      if($('#naviDropdown').hasClass('hidden-xs')){
        $('#naviDropdown').removeClass('hidden-xs')
      } else {
        $('#naviDropdown').addClass('hidden-xs')
      }
    });
  },1000);
});


function editCoverToggle () {
  
  $('#editiconToggle').on('click', function () {
    if ($('#editiconContent').is(":visible")) {
      $('#editiconContent').removeClass('show');
      $(this).parents('.editicon').removeClass('active');
    } else {
      $('#editiconContent').addClass('show');
      $(this).parents('.editicon').addClass('active');
    }
  });


  $('#thumbEditToggle').on('click', function () {
      if ($('#thumbEditContent').is(":visible")) {
          $('#thumbEditContent').removeClass('show');
      } else {
          $('#thumbEditContent').addClass('show');
      }
  });

  $(document).click(function(e) {
      var target = e.target;
      
      if (!$(target).is('#editiconToggle, .editicon .fa') && !$(target).parents().is('#editiconContent')) {
        $('#editiconContent').removeClass('show');
        $('.editicon').removeClass('active');;
      }

      if (!$(target).is('#thumbEditToggle')) {
        $('#thumbEditContent').removeClass('show');
      }

  });
}

$('#manage-profile-menus').click(function(){
        if(!$(this).hasClass('active')){
            $('.manage-profile-content').removeClass('hidden-xs');
            $(this).addClass('active')
        } else {
            $('.manage-profile-content').addClass('hidden-xs');
            $(this).removeClass('active')
        }
    });

    // $('.manage-profile-content li').click(function(){
    //         $('.manage-profile-content').addClass('hidden-xs');
    //         $('#manage-profile-menus').removeClass('active');
    //     });

    $('.manage-profile-content').click(function(){
         if ($(this).find("li.active").length != 0) {
             var linkText = $('.manage-profile-content li.active').text();
             //alert(linkText);
            $('#manage-profile-menus').html( linkText +'<b class="caret"></b>');
         }
    });
    

$(document).click(function(e) {
      var target = e.target;
      if (!$(target).is('#manage-profile-menus')) {
            $('.manage-profile-content').addClass('hidden-xs');
            $('#manage-profile-menus').removeClass('active');
      }
});

// Account Setting Tab 
function accountSettingTab() {
  $('#bhoechietabTrigger').click(function(){
      if(!$(this).hasClass('active')){
          $('#bhoechietabContainer').removeClass('hidden-xs');
          $(this).addClass('active')
      } else {
          $('#bhoechietabContainer').addClass('hidden-xs');
          $(this).removeClass('active')
      }
  });

  $('#bhoechietabContainer').click(function(){
       if ($(this).find("a.active").length != 0) {
           var linkText = $('.list-group a.active').text();
           //alert(linkText);
          $('#bhoechietabCurrent').html( linkText );
       }
  });

  $(document).click(function(e) {
    var target = e.target;

    if (!$(target).is('#bhoechietabCurrent, #bhoechietabTrigger .fa')) {
        $('#bhoechietabContainer').addClass('hidden-xs');
        $('#bhoechietabTrigger').removeClass('active');;
      }    
  });
}


(function() { 
  tabDropdowns(); //Small Screen Tabs
 
 if($(window).width() > 767){
		 $(document).on('click','.wrapper .global-search .btn-search', function () {	
		//$(this).find('i').toggleClass('icon-removeclose');
	});
 }
	 
$(document).on('touchstart click','.search-toggle-btn', function(e) {
		$(this).prev().toggleClass('hidden-sm');
		$(this).parents('.global-form').toggleClass('active');
  });
  
$(document).on('touchstart click','.search-contentinput', function(e) {
      $(this).toggleClass('open-search');
      $(this).next().toggleClass('hidden-xs');
    }); 
  
  followToggle();
  LikeToggle();
  onOff();
  
 //Custom Filters
$(document).on('touchstart click','.custom-filters > li a', function(e) {
		$('.custom-filters > li a').parent('li').removeClass('active');
		$('.custom-filters > li a').find('i').remove();		
		var target = $(this).attr('data-rel');
		$(this).prepend ('<i class="icon-checkbase"></i>');
		$(this).parent('li').addClass('active');
		//$('.filterApply').removeClass('hide');
	    $("#"+target).removeClass('hide').siblings("div").addClass('hide');
});
  dradioButton();
}).call(this);

/*Thumb Carsual*/


/*Custom Functions and its Definations*/
/*Tab drop down for small screens*/
function tabDropdowns(){    
    $('.tab-dropdowns').click(function(e) {
      if($(this).hasClass('open-dropdown')){
          $(this).removeClass('open-dropdown');
                $('.small-screen-tabs').addClass('hidden-xs');
        } else {
          $(this).addClass('open-dropdown');
                $('.small-screen-tabs').removeClass('hidden-xs');
          }
      $('.small-screen-tabs li > a').click(function(e) {
                  $(this).parents('.small-screen-tabs').addClass('hidden-xs');
          $(".tab-dropdowns").removeClass('open-dropdown');
          
          var text = $(this).text();
          $('.tab-dropdowns a span').text(text);
            });
        });
  if ($(window).width() <= 767) {
    $('.dropdown-toggle').on('touchstart click', function(e) {
      $('.small-screen-tabs').addClass('hidden-xs');
      $(".tab-dropdowns").removeClass('open-dropdown');
    });
    $(document).on('touchstart click', function(e) {
        if ($(e.target).closest('.small-screen-tabs, .tab-dropdowns').length === 0) {
          $('.small-screen-tabs').addClass('hidden-xs');
          $(".tab-dropdowns").removeClass('open-dropdown');
        }
    });
  }
}

/*Followed toggle action*/
function followToggle(){
  $('.follow-action').click(function(e) {
    $(this).toggleClass('following');
    if ( $(this).hasClass("following") )  { 
        $(this).find('span').text('Following');
        $(this).find('i').addClass('icon-check');
    }
  else 
    {
        $(this).find('span').text('Follow');
        $(this).find('i').removeClass('icon-check');
    }
  });
}

/*Liked toggle action*/
function LikeToggle(){
  $('.like-action').click(function(e) {
    $(this).toggleClass('liked');
    if ( $(this).hasClass("liked") )  { 
        $(this).find('span').text('Liked');
        $(this).find('i').addClass('icon-check');
    }
  else 
    {
        $(this).find('span').text('Like');
        $(this).find('i').removeClass('icon-check');
    }
  });
}

/*On/Off toggle action*/
function onOff(){
  $('.btn-onoff').click(function(e) {
    $(this).toggleClass('on');
    if ( $(this).hasClass("on") )   { 
        $(this).find('span').text('On');
        $(this).find('i').addClass('icon-on');
        $(this).find('i').removeClass('icon-off');
    }
  else 
    {
      $(this).find('span').text('Off');
      $(this).find('i').addClass('icon-off');
      $(this).find('i').removeClass('icon-on');
    }
  });
}

function iconToggle(){
	 /*$(document).on('click', '.custom-icondrop .dropdown-withicons > li a' ,function(){
 	 var i = $(this).find('i').attr('class');
	 $(this).parents('.dropdown-withicons').siblings('.drop-icon').find('i').removeAttr('class');
	 $(this).parents('.dropdown-withicons').siblings('.drop-icon').find('i').addClass(i);
  })*/
}


/* highlight plugin */
jQuery.fn.highlight = function(pat) {
 function innerHighlight(node, pat) {
  var skip = 0;
  if (node.nodeType == 3) {
   var pos = node.data.toUpperCase().indexOf(pat);
   pos -= (node.data.substr(0, pos).toUpperCase().length - node.data.substr(0, pos).length);
   if (pos >= 0) {
    var spannode = document.createElement('span');
    spannode.className = 'highlight';
    var middlebit = node.splitText(pos);
    var endbit = middlebit.splitText(pat.length);
    var middleclone = middlebit.cloneNode(true);
    spannode.appendChild(middleclone);
    middlebit.parentNode.replaceChild(spannode, middlebit);
    skip = 1;
   }
  }
  else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
   for (var i = 0; i < node.childNodes.length; ++i) {
    i += innerHighlight(node.childNodes[i], pat);
   }
  }
  return skip;
 }
 return this.length && pat && pat.length ? this.each(function() {
  innerHighlight(this, pat.toUpperCase());
 }) : this;
};

jQuery.fn.removeHighlight = function() {
 return this.find("span.highlight").each(function() {
  this.parentNode.firstChild.nodeName;
  with (this.parentNode) {
   replaceChild(this.firstChild, this);
   normalize();
  }
 }).end();
};

$(document).ready(function(){
  setTimeout(function(){
    $('.posts').highlight('Sunil');
  },500);
});


function postContentvisible() {
    $(document).on('focusin', '#PostContent,.note-editable', function() {
        $('.post-content-block').fadeIn();
        $('.wall-content').addClass('post-content-open');
    });
    $(document).on('focusout', '#PostContent', function() {
        var value = $(this).val();
        if (value == '') {
            //  $('.post-content-block').slideUp();
            $('.wall-content').removeClass('post-content-open');
        } else {
            $('.post-content-block').fadeIn();
            $('.wall-content').addClass('post-content-open');
        }
    });
}

// Alert
function alertFunction() {
    $('[class*="alert-"], [class^="alert-"], .alert').animate({ top: '63px' }, 700);
    setTimeout(function () {
        $('[class*="alert-"], [class^="alert-"], .alert').animate({ top: '-250px' }, 700);
    }, 2500);
	$(document).on('click', '.close-alert' , function(){
		$(this).parents('.alert').fadeOut('fast');
	});
}

/*user to handle missing images in website*/
function missingImages(){ 
	 /*$('img').on('error', function (e) {
    	console.log('image error: ' + this.src);
    	 $(this).attr('ng-src', AssetBaseUrl+'img/profiles/user_default.jpg');
    	 $(this).attr('src', AssetBaseUrl+'img/profiles/user_default.jpg');
		});*/

	$(".img-circle img").on('load', function() {
	  // do stuff on success
	})
	.on('error', function() {
		//console.log('image error: ' + this.src);
	  	$(this).attr('ng-src', AssetBaseUrl+'img/profiles/user_default.jpg');
    	$(this).attr('src', AssetBaseUrl+'img/profiles/user_default.jpg');
    	$(this).load();
	})
	.each(function() {
	    if(this.complete) {	    	
	      $(this).load();
	    } else if(this.error) {
	      $(this).error();
	    }
	});

} 


function mszSectionHeight(){
	var mszContentBoxht = $('.message-box-content').height(),
		mszComposerht 	= $('.message-composer').innerHeight(),
		mszGrouptoht 	= $('.message-form-group').innerHeight(),
		mszListtotalht 	= mszContentBoxht - mszComposerht - mszGrouptoht;
		$('.msg-lisitings').height(mszListtotalht);

}

 
$(function(){  
    setTimeout(function(){
		missingImages(); 
		mszSectionHeight();
	},1000)
 
});

function ShowSuccessMsg(successMsg){
    $("#spn_noti").html("");
    sucessMsz();
    $("#spn_noti").html("  "+successMsg);
}

function ShowErrorMsg(errorMsg){
    //Show error message
    $("#error_message #spn_noti").html("");
    failureMsz();
    $("#error_message #spn_noti").html("  "+errorMsg);
}

//Function for Show success messege for every event
function sucessMsz() {
    $('#success_message.notifications').addClass('active');
    setTimeout(function () {
        $('#success_message.notifications').removeClass('active');
    }, 3000);
}

//Function for Show failure messege for every event
function failureMsz() {
    $('#error_message.notifications').addClass('active');
    setTimeout(function () {
        $('#error_message.notifications').removeClass('active');
    }, 5000);
}
$(window).resize(function(){
	mszSectionHeight();
});

function dradioButton(){
 /*$('.radio-btn input[type="radio"]').change(function(){
	if(!$(this).hasClass('selected')){
		$('.radio-btn input[type="radio"]').parent().removeClass('selected');
		$(this).parent().addClass('selected');
	}
 });*/
}

//Comment Expanded View 

//function customComments(){
//	
//	$(document).on('focusin','.wall-comments textarea', function(){
//			var   eachTextareaVal = $(this).val(),
//				  eachTextarea 	  = $('.wall-comments textarea');
//				  $(this).closest('.post-comments').addClass('textareaFocusin');
//				  $(this).closest('.post-comments').find('.user-thmb').show();
//				  $(this).closest('.wall-posts').find('.post-help-text').fadeIn();    	 
//				  $(this).closest('.post-comments').find('.post-help-text').fadeIn();    	 
//	});
//
//	$(document).on('focusout','.wall-comments textarea', function(){
//		var   eachTextareaVal = $(this).val(),
//			  eachTextarea 	  = $('.wall-comments textarea'),
//			  eachAttachedlist = $(this).closest('.post-comments').next('.attached-list').is(':visible');
// 
//			  	if( eachTextareaVal === '' && !eachAttachedlist ){			  		 
//					$(this).closest('.post-comments').removeClass('textareaFocusin');
//					$(this).closest('.post-comments').find('.user-thmb').hide();
//					$(this).closest('.wall-posts').find('.post-help-text').hide();
//					$(this).closest('.post-comments').find('.post-help-text').hide();
//			  	  }
//
//			  	  else {
//			  	  	
//			  	  	return false;
//
//			  	  } 
//	});
//}




function callSlider(){
	var slider;    
    var width = $(document).width();
    if(width>=1200){
	    slider=$('.bxslider').bxSlider({
	        minSlides: 1,
	        maxSlides: 2,
	        slideWidth:297,
	        pager: false
	    });
    }
    else if(width>=768&&width<=991){
      slider=$('.bxslider').bxSlider({
          minSlides: 1,
          maxSlides: 2,
          slideWidth:337,
          pager: false
     });
    }
    else if(width>=992&&width<=1199){
      slider=$('.bxslider').bxSlider({
          minSlides: 1,
          maxSlides: 2,
          slideWidth:447,
          pager: false
     });
    }
}

$(document).ready(function(){
	//callSlider();
});

// Function for Message section



function messageColresize (){

if($(window).width() >= 700 ){

	var winHt = $(window).height(),
		divideht = 200,
		totalHt = winHt - divideht;

		$('.message-left').height(totalHt);
		var mszLeftht = $('.message-left').height() - 112;
		$('.m-left-scroll').height(mszLeftht);
		var mszLeftht = $('.m-left-scroll').height() + 52,
			replyBlockht = $('.m-write-reply').innerHeight(),
			mszCoverHt = mszLeftht - replyBlockht; 

		$('.m-conversation-block').height(mszCoverHt);
		var lefColheight = $('.message-left').height();
		var newsectionHt  = lefColheight - replyBlockht;

		$('.message-right').height(newsectionHt);
		$('.message-right').css({'padding-bottom' : replyBlockht});
	}
}


$(function(){
	messageColresize ();
	$('#sendMszto').on('keyup',function(){
		$(this).next('.m-message-to-list').fadeIn();
	});

	$(document).on('mouseup',function(){
		 $('.m-message-to-list').hide();
	}); 
});

 
 
$(window).resize(function(){
	messageColresize ();
});


//Function for image center to according to parent element width

function imageCenter(){
	$('.banner-info > img').each(function(){

		var eachimgwd = $(this).width(),
			parentWd = $(this).parent().width(),
			totlawd = eachimgwd - parentWd,
			addedwd = totlawd/2;
			$(this).css({'margin-left':-addedwd});
	});
}
$('.banner-info > img').on('load',function(){

	imageCenter();
});
//Custom Tooltip

//Global Tooltip	
function globalTooltip(){
	var TooltipTimer;
	$('[data-rel="custom-tooltip"]').on("mouseenter",function(){
		$('.popover-content').html('');
 		var dataContent = $(this).attr('data-content');
 		 if(TooltipTimer)
			clearTimeout(TooltipTimer);
  			$('.popover-content').html(dataContent);
  			var tipheight = $('.customTooltip').height();
			var tipwith = $('.customTooltip').width();
			var dataPlacement = $(this).attr('data-placement');
			
    		if (dataPlacement == "right") 
				{
 					$('.customTooltip').removeClass('left  top  bottom fadeInDown fadeInUp fadeInRight');
					$('.customTooltip').css({left:$(this).offset().left + 30 ,top:$(this).offset().top-tipheight/2+5});
					$('.customTooltip').addClass('right fadeInLeft');
					$('.customTooltip').show();
				}
 			else if(dataPlacement == "left")
					{
 						$('.customTooltip').removeClass('bottom  right  top fadeInDown fadeInUp fadeInLeft');
						$('.customTooltip').css({left:$(this).offset().left-tipwith-5,top:$(this).offset().top-tipheight/2+5});
						$('.customTooltip').addClass('left fadeInRight');
						$('.customTooltip').show();
					}
 			else if(dataPlacement == "top")
					{
						$('.customTooltip').removeClass('left  right  bottom  fadeInUp fadeInLeft');
						$('.customTooltip').css({left:$(this).offset().left-tipwith/2+10,top:$(this).offset().top-tipheight});
						$('.customTooltip').addClass('top fadeInDown');
						$('.customTooltip').show();
					}
 			else if(dataPlacement == "bottom")
					{
						$('.customTooltip').removeClass('left  right  top fadeInDown fadeInUp fadeInLeft');
						$('.customTooltip').css({left:$(this).offset().left-tipwith/2+5,top:$(this).offset().top+20});
						$('.customTooltip').addClass('bottom fadeInUp');
						$('.customTooltip').show();
 					}		
 			else {
					$('.customTooltip').css({left:$(this).offset().left-tipwith/2+10, top:$(this).offset().top-tipheight});
					$('.customTooltip').show();
					$('.customTooltip').addClass('top fadeInDown');
					$('.customTooltip').removeClass('left right  bottom  fadeInUp fadeInLeft');
				}	
			});
			
 			$('[data-rel="custom-tooltip"]').on("mouseleave",function(){
					TooltipTimer=setTimeout(function(){$('.customTooltip').hide()},50);
				});
			$('.customTooltip').on("mouseenter",function(){
				if(TooltipTimer)
				clearTimeout(TooltipTimer);
			});
			$('.customTooltip').on("mouseleave",function(){
				$(this).fadeOut('slow');
		});
		
	$(document).mouseup(function(){
		$('.customTooltip').fadeOut('slow');
		});
	$('.customTooltip').mouseup(function(){
		return false;
	}); 
}


function groupNamelist(){

	var TooltipTimer;

	$('[data-rel="group-usertip"]').on("mouseenter",function(){
		var tipheight = $('.customTooltip').height();
		$('.customTooltip').css({left:$(this).offset().left  ,top:$(this).offset().top+ 35});
		$('.customTooltip').fadeIn();

}); 


$('[data-rel="group-usertip"]').on("mouseleave",function(){
			TooltipTimer=setTimeout(function(){$('.customTooltip').hide()},200);
		});
	$('.customTooltip').on("mouseenter",function(){
		if(TooltipTimer)
		clearTimeout(TooltipTimer);
	});
	$('.customTooltip').on("mouseleave",function(){
		$(this).fadeOut('slow');
	});
}
groupNamelist(); 

messageBlock();
attachedmediaWd();
//Message samll view

function messageBlock() { 
    if ($(window).width() <= 767) {
        $(document).on('click', '.m-user-listing > li', function () {
            $('.message-left').addClass('hidden-xs');
            $('.message-right').removeClass('hidden-xs');
            $('.m-conversation-content, .m-add-people-button').show();
            //$('.newcomposemail-wrap').hide(); 
        });

        $('#backTolist').on('click', function () {
            $('.message-left').removeClass('hidden-xs');
            $('.message-right').addClass('hidden-xs');
           // $('.message-info').hide();
            //$('.newcomposemail-wrap').show();
        });

        $(document).on('touchstart click', '#newMessage', function () {

			$('.m-new-message').show();
			//$('.m-conversation-content, .m-add-people-button').hide(); 

            $('.message-left').addClass('hidden-xs');
            $('.message-right').removeClass('hidden-xs');
        });

    } 
}

//Attached list 

function attachedmediaWd() {

	var totalLiof = $('.m-media-attached-list > ul > li').size(),
		totalulWd = totalLiof*108;
	$('.m-media-attached-list > ul').width(totalulWd);
}

$(function(){

	/*$('[data-type="uploadMediabutton"]').on('click',function(){ 
		$('[data-type="uploadedMediaview"], [data-type="imageIcn"]').removeClass('hide'); 
	});*/  
});
 

$(window).load(function(){
	if($(window).width() <= 767){ 		 
		if($('#filterBy').empty()){ 
				$('#filterBy').append($('[data-type="filterby"]').html());
				$('#avrageRating').append($('[data-type="avrageRating"]').html());
			}
		} 
});

 // these are (ruh-roh) globals. You could wrap in an
  // immediately-Invoked Function Expression (IIFE) if you wanted to...
  var currentTallest = 0,
      currentRowStart = 0,
      rowDivs = new Array();

  function setConformingHeight(el, newHeight) {
   // set the height to something new, but remember the original height in case things change
   el.data("originalHeight", (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight")));
   el.height(newHeight);
  }

  function getOriginalHeight(el) {
   // if the height has changed, send the originalHeight
   return (el.data("originalHeight") == undefined) ? (el.height()) : (el.data("originalHeight"));
  }

  function columnConform(el) {

   // find the tallest DIV in the row, and set the heights of all of the DIVs to match it.
   $(el).each(function() {

    // "caching"
    var $el = $(this);

    var topPosition = $el.position().top;

    if (currentRowStart != topPosition) {

     // we just came to a new row.  Set all the heights on the completed row
     for(currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) setConformingHeight(rowDivs[currentDiv], currentTallest);

     // set the variables for the new row
     rowDivs.length = 0; // empty the array
     currentRowStart = topPosition;
     currentTallest = getOriginalHeight($el);
     rowDivs.push($el);

    } else {

     // another div on the current row.  Add it to the list and check if it's taller
     rowDivs.push($el);
     currentTallest = (currentTallest < getOriginalHeight($el)) ? (getOriginalHeight($el)) : (currentTallest);

    }
    // do the last row
    for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) setConformingHeight(rowDivs[currentDiv], currentTallest);

   });

  }
  
  function editLocation(){
	$(document).on('click','[data-type="subDropdown"]', function(){
		var thisParent = $(this).parent('.dropdown-menu').prev();
        var location = $(this).attr('id');
        var locationArr  = location.split('-');
        var locationID = locationArr[1];
		$('#'+$(this).attr('data-rel')).css({left:thisParent.offset().left - 204 ,top:thisParent.offset().top + 36});
		$('#'+$(this).attr('data-rel')).children().find('input').attr('id',locationID);
		//$('#'+$(this).attr('data-rel')).addClass('locationEdit'+locationID);
		$('#'+$(this).attr('data-rel')).find('button:first').attr('id','addLoc'+locationID);
        $('#'+$(this).attr('data-rel')).show();
        $('#'+locationID).focus();
        var scope = angular.element('#AlbumCtrl').scope();
        scope.currentLocationInitialize(locationID,1);
        $('#addLoc'+locationID).bind('click',function(){
            $('#addLoc'+locationID).removeAttr('id');
            $(this).unbind("click");
            scope.currentLocationFillInPrepare(locationID);
            scope.currentLocationInitialize('AlbumLocation');
        });
        
	});
}





//For New Section
function privacyDropdown(){
	 $(document).on('click touchstart', '[data-dropdown="privacydropdown"] a' ,function(){
  	 	/* $('[data-dropdown="privacydropdown"] a').removeClass('active');
	 	 $(this).addClass('active');
 	 var iClass = $(this).find('i').attr('class'),
 	 	 thisParent = $(this).parents('[data-dropdown="privacydropdown"]').siblings('[data-dropdown="iconmenu"]').find('i'); 	 	 
	 	 thisParent.removeAttr('class');
	 	 thisParent.addClass(iClass);*/
  });
} 

//Globle Toggle Button

function toggleButton(){
	$(document).on('click', '[data-button="toggle"]' ,function(){
     	var findText = $(this).find('span').text(),
     		altText = $(this).attr('data-alt');
     	 
			$(this).toggleClass('active');
			$(this).find('i').toggleClass('active');
			$(this).find('span').text(altText);			   
	  		$(this).attr('data-alt',findText);
	});
}

function mentionTab() {
    $('.custom-filters > li a').on('click',function(){
        $('#mentionView').hide();
        $('.live-feed').removeClass('mentionOpen');
    });
    $('[data-mention="mentiontab"]').on('click', function() {
        $('#mentionView').addClass('visible').show();
        if ($('#mentionView').hasClass('visible')) {
            $('.live-feed').addClass('mentionOpen');
           // $('applyed-filter').
        };
    });
}

function liveFeedtoggle() {
    $(document).on('click', '[data-toggle="feedtoggle"]', function() {
        $('.live-feed').toggleClass('is-visible');
    });
}

//News Feed Slider
function feedSlider (slider) {
	/*var slider;    
    var width = $(document).width();
    if(width >= 1100){
	    slider = $('#suggestedList').bxSlider({
	        minSlides: 1,
	        maxSlides: 2,
	        slideWidth:315,
	        infiniteLoop:false,
	        pager: false
	    });
    }
    else if(width >= 768 && width <= 991){
      slider = $('#suggestedList').bxSlider({
          minSlides: 1,
          maxSlides: 1,
	      infiniteLoop:false,
          pager: false
     });
    }
    else if(width >= 992 && width <= 1199){
      slider = $('#suggestedList').bxSlider({
          minSlides: 1,
          maxSlides: 2,
          slideWidth:300,
          infiniteLoop:false,
          pager: false
     	});
    }
    else if(width >= 200 && width <= 767){
      slider = $('#suggestedList').bxSlider({
          minSlides: 1,
          maxSlides: 1,
          slideWidth:400,
          pager: false,
          infiniteLoop:false,
     	});
    }*/
}

function selectedDatetime(name){
	 $(document).on('change','input[name="'+ name +'"]',function(){

		if(!$(this).hasClass('selected')){
			$('input[name="'+ name +'"]').parent().removeClass('selected');
			$(this).parent().toggleClass('selected');
		}

	 });
 }
 
function selectFixedDate(){
	 $(document).on('click','.fixed-date a',function(){
         $('.fixed-date a').removeClass('active');
         $(this).addClass('active');
	 });
 }


function setReminder(){


	$(document).on('click','[data-set="reminderDate"]',function(){
		$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text('Select  Date and Time');
		$(this).closest ('.reminderDropdown').find('[data-arrow="backReminder"]').show();
		$(this).closest ('.reminderDropdown').find('[data-set="reminder"], [data-arrow="backeditReminder"]').hide();
		$(this).closest ('.reminderDropdown').find('[data-calendar="reminderCalendar"]').slideDown();
		 
	});

	$(document).on('click','[data-arrow="backReminder"]',function(){
		$(this).hide();
		ReminderDate = $(this).closest ('.reminderDropdown').find('[data-reminder="title"]').attr('data-reminder-date');
        if(ReminderDate!=undefined){
            ReminderDate = 'Edit';
            $(this).closest ('.reminderDropdown').find('[data-arrow="backeditReminder"]').show()
        }else{
            ReminderDate = 'Set Reminder';
        }
		$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text(ReminderDate);
		$(this).closest ('.reminderDropdown').find('[data-set="reminder"]').slideDown();
		$(this).closest ('.reminderDropdown').find('[data-calendar="reminderCalendar"]').hide();
	});
	$('[data-reminder="close"]').dropdown('toggle');

    editReminder();
} 

function editReminder(){
     $(document).on('click','[data-type="editReminder"]',function(){
		$(this).closest ('.reminderDropdown').find('[data-type="editreminderFooter"]').hide();
		$(this).closest ('.reminderDropdown').find('[data-type="reminderFooter"]').show();
		$(this).closest ('.reminderDropdown').find('[data-arrow="backeditReminder"]').show();
		$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text('Edit');
		$(this).closest ('.reminderDropdown').find('[data-set="reminder"]').slideDown();	
		$(this).closest ('.reminderDropdown').find('[data-reminder="close"]').show();	

	});

   $(document).on('click','[data-arrow="backeditReminder"]',function(){
		$(this).hide();
		var dataCalendarSet = $(this).closest ('.reminderDropdown').find('[data-calendar="reminderCalendar"]');
		if(dataCalendarSet.is(':visible')){
			$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text('Edit');			
		}else{
            var dataReminderDate = $(this).closest ('.reminderDropdown').find('[data-reminder="title"]').attr('data-reminder-date');
			$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text(dataReminderDate);			
		}
		//$(this).closest ('.reminderDropdown').find('[data-type="reminderFooter"], [data-reminder="close"]').hide();		
		$(this).closest ('.reminderDropdown').find('[data-type="editreminderFooter"]').show();
		$(this).closest ('.reminderDropdown').find('[data-set="reminder"]').hide();
                $(this).closest ('.reminderDropdown').find('[data-type="reminderFooter"]').hide();
	});
}


function resetCalendar(){
      $(document).on('click','[data-arrow="backeditReminder"]',function(){
		$(this).hide();
		var dataCalendarSet = $(this).closest ('.reminderDropdown').find('[data-calendar="reminderCalendar"]');
		if(dataCalendarSet.is(':visible')){
			$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text('Edit');			
		}else{
            var dataReminderDate = $(this).closest ('.reminderDropdown').find('[data-reminder="title"]').attr('data-reminder-date');
			$(this).closest ('.reminderDropdown').find('[data-reminder="title"]').text(dataReminderDate);			
		}
		$(this).closest ('.reminderDropdown').find('[data-type="reminderFooter"], [data-reminder="close"]').hide();		
		$(this).closest ('.reminderDropdown').find('[data-type="editreminderFooter"]').show();
		$(this).closest ('.reminderDropdown').find('[data-set="reminder"]').hide();
	});
}

function openReminder(){

	 $(document).on('click','.reminderNav .btn-circle',function(event) {
	 	$('.reminderNav').removeClass('open');
        $(this).parent().toggleClass("open");
    });

    $('body, document').on('touchstart click', function(e) {
        if (!$('.reminderNav').is(e.target) && $('.reminderNav').has(e.target).length === 0 && $('.open').has(e.target).length === 0) {
            $('.reminderNav').removeClass('open');
        }
    });
}

function closeReminder(e)
{
	$('[data-reminder="close"]').on('click',function(){         
        $(e).closest('.reminderNav').removeClass('open');
    });
}

function filterApply()
{
	$('.filterApply').removeClass('hide');
}

//News Feed Slider
function feedSlider(slider) {
    /*var slider;
    var width = $(document).width();
    if (width >= 1100) {
        slider = $('#suggestedList, #peopleYouKnow').bxSlider({
            minSlides: 1,
            maxSlides: 2,
            slideWidth: 315,
            infiniteLoop: false,
            pager: false
        });
    } else if (width >= 768 && width <= 991) {
        slider = $('#suggestedList, #peopleYouKnow').bxSlider({
            minSlides: 1,
            maxSlides: 1,
            infiniteLoop: false,
            pager: false
        });
    } else if (width >= 992 && width <= 1199) {
        slider = $('#suggestedList, #peopleYouKnow').bxSlider({
            minSlides: 1,
            maxSlides: 2,
            slideWidth: 300,
            infiniteLoop: false,
            pager: false
        });
    } else if (width >= 200 && width <= 767) {
        slider = $('#suggestedList, #peopleYouKnow').bxSlider({
            minSlides: 1,
            maxSlides: 1,
            slideWidth: 400,
            pager: false,
            infiniteLoop: false,
        });
    }*/
}

function liveFeedScroll(){
    var winht = $(window).height(),
        winwd = $(window).width(),
        headerNavht = 165,
        totlaHeight = winht-headerNavht;
        if(winwd >= 1000){
            $('#liveFeeds').height(totlaHeight);
        }
}

function bannerTheme(){
    $(document).on('click','[data-theme="banner"] > li', function(){
        $('[data-theme="banner"] > li').removeClass('selected');
        $(this).addClass('selected');
    });
}

/* Back to top page */
function backToTop() {
  $('[data-back="top"]').on('click', function(){
    $('html, body').animate({scrollTop: $('body').offset().top}, 800);
  });
}

/* Back to top Button */
function BackToTopSticky() {
    if($('body').find('.btn-gotop').length > 0){
        $(window).scroll(function(){
            var WindowTop = $(window).scrollTop();
            var BackToTop = parseInt($('.btn-gotop').offset().top) + 40;
            var WindowHeight = $('body').height() - $('footer').height();
            if (WindowTop > 600) {
                $('.btn-gotop').addClass('active');
            } else {
                $('.btn-gotop').removeClass('active');
            }
            if (WindowHeight < BackToTop) {
                $('.btn-gotop').css({bottom: $('footer').height()+10});
            } else {
                $('.btn-gotop').css({bottom: ''});
            }
            if ($(window).width() > 1024) {
                $('.btn-gotop').tooltip('hide'); 
            }
        }).scroll();
    }
    $('body').find('.btn-gotop').hover(function(){    
        if ($(window).width() > 1024) {
            $('.btn-gotop').tooltip('show');	
        }
    });
}

function navTabScroll() {
    if ($(window).width() < 767) {
      $('.nav-tabs-scroll').scrollingTabs({
        enableSwiping: true,
        disableScrollArrowsOnFullyScrolled: true,
        cssClassLeftArrow: 'icon-arrow-left',
        cssClassRightArrow: 'icon-arrow-right'

      });
    }
}

//stack modal overflow issue handeld here
$(document).ready(function(){
    $(document).on('show.bs.modal', '.modal-stack', function(e){
      $(e.currentTarget).data('HasClassModalOpen', $('body').hasClass('modal-open'));
    }).on('hidden.bs.modal', '.modal-stack', function(e){
        if($(e.currentTarget).data('HasClassModalOpen')){
          $('body').addClass('modal-open');
        };
    });
});
//stack modal overflow issue function ends here

//dropdownOnhover
function dropdownOnhover() {
    $(".dropdown-Onhover").hover(function(){
      var dropdownMenu = $(this).children(".dropdown-menu");
      if(dropdownMenu.is(":visible")){
        dropdownMenu.parent().toggleClass("open");
      }
    });    
}

//nav-tabs-modal-scroll
$(document).ready(function(){
  $(document).on('show.bs.modal', function (e) { 
    setTimeout(function() { 
      if ($(window).width() < 767) {
        $('.nav-tabs-modal-scroll').scrollingTabs({
          enableSwiping: true,
          disableScrollArrowsOnFullyScrolled: true,
          cssClassLeftArrow: 'icon-arrow-left',
          cssClassRightArrow: 'icon-arrow-right'

        });
      }
     },300);
  })
});





function cardTooltip() {
    var TooltipTimer;
    return;
    $('[data-type="businessCard"]').on('mouseenter', function() {
        var windowHeight = $(window).height() / 2,
            cardHeaight = $('[data-type="cardTip"]').height() + 10,
            cardWidth = $('[data-type="cardTip"]').width() + 10,
            thisOffsetleft = $(this).offset().left,
            wintopPosition = $(window).height() + $(document).scrollTop() - $(this).offset().top,
            winleftPosition = $(document).scrollLeft() + $(window).width() / 2;

        if (TooltipTimer)
            clearTimeout(TooltipTimer);
        
        if (windowHeight < wintopPosition && thisOffsetleft < winleftPosition) {
        
            $('[data-type="cardTip"]').css({
                left: $(this).offset().left,
                top: $(this).offset().top + 25
            });
            $('[data-type="cardTip"]')
                .removeClass('fadeInDown fadeInRight arrow-down arrow-down-right arrow-top-right')
                .addClass('fadeInUp')
                .show();
        } else if (thisOffsetleft > winleftPosition && windowHeight < wintopPosition) {
            
            $('[data-type="cardTip"]').css({
                left: $(this).offset().left - cardWidth,
                top: $(this).offset().top
            });
            $('[data-type="cardTip"]')
                .removeClass('fadeInDown arrow-down arrow-down-right')
                .addClass('fadeInRight arrow-top-right')
                .show();
        } else if ($(this).offset().left > winleftPosition) {
            $('[data-type="cardTip"]').css({
                left: $(this).offset().left - cardWidth,
                top: $(this).offset().top - cardHeaight + 50
            });
            $('[data-type="cardTip"]')
                .removeClass('fadeInUp arrow-top-right')
                .addClass('fadeInRight arrow-down-right')
                .show();
        } else {

            $('[data-type="cardTip"]').css({
                left: $(this).offset().left,
                top: $(this).offset().top - cardHeaight
            });
            $('[data-type="cardTip"]')
                .removeClass('fadeInUp fadeInRight arrow-down-right arrow-top-right')
                .addClass('fadeInDown arrow-down')
                .show();
        }

    });

    $('[type="businessCard"]').on("mouseleave", function() {
        TooltipTimer = setTimeout(function() {
            $('[data-type="cardTip"]').fadeOut();
        }, 200);
    });

    $('[data-type="cardTip"]').on("mouseleave", function() {
        $(this).fadeOut();
    });
    $('[data-type="cardTip"]').on("mouseenter", function() {
        if (TooltipTimer)
            clearTimeout(TooltipTimer);
    });
}



function fixedHeader() {
    var top,
        mainbar = $('[data-nav="fixed"]'),
        offset = 240,        
        disabled = false; 

    $(document).on('click', '[data-banner="hide"]', function() {
        visibleBaner = false;
         $('.banner-cover').slideUp('slow',function(){
            $('[data-banner="show"]').slideDown();
            $('body').addClass('naveFixed bannerHide');
        }); 
    });

    $(document).on('click', '[data-banner="show"]', function() {
        visibleBaner = true;
        offset = 280;
        setTimeout(function() {
            $('.banner-cover').slideDown();
            $('body').removeClass('naveFixed bannerHide');
        }, 150); 
    });

    $(window).on('scroll', function() {
        if (!disabled) {
            if($('[data-banner="show"]').length>0)
            {
	            top = $(this).scrollTop();  
	            if (top > offset && visibleBaner) {
	                $('body').addClass('naveFixed');
	                if ($('.banner-cover').is(':hidden') && top < offset) {
	                    $('[data-banner="show"]').show();
	                } else {
	                    $('[data-banner="show"]').hide();
	                }
	            } else if (top < offset && visibleBaner) {
	                $('body').removeClass('naveFixed');
	            }
            }
        }
        if ($('.cover-inner').is(':hidden')) {
            if (top > 150) {
                $('[data-banner="show"]').hide();
            } else {
                $('[data-banner="show"]').show();
            }
        }
    });
}

$(function(){
	fixedHeader();
});


$('.modal').on('hidden.bs.modal', function (e) { 
   if($('.media-popup').is(':visible')){
     $('body').addClass('modal-open');
   }
});

//Network media Slider
/*$(document).ready(function(){
    var slider;
    slider = $('.networkmedia').bxSlider({
        minSlides: 1,
        maxSlides: 1,
        slideWidth: 170,
        infiniteLoop: false,
        pager: false
    });
});*/

//poll section
$(function(){
    $(".poll-post ").on("click", function(){
        /*setTimeout(function(){
            $('textarea.createPoll').textntags();
        })   */
        $(this).addClass('active');
    });

    // $(".poll-que").on("click", function(){
    //     var pollHead = $(this).parents(".poll-vote");
    //     if(!(pollHead).hasClass('openPoll')){
    //         pollHead.addClass('openPoll')
    //         pollHead.children('.queVote').slideDown();
    //     }else{
    //         pollHead.removeClass('openPoll')
    //         pollHead.children('.queVote').slideUp();
    //     }
    // });
    $(".backSlide").on("click", function(){
        $(this).parents(".pollSlide").removeClass('onSlide');
    });




    //About To Close
    $(".poll-que .remove").on("click", function(e){
        e.stopPropagation();
    })

    $(".queVote").each(function() {
        var that = $(this);
        $(this).find("input[type=radio]").on("click", function(){
            that.find('li').removeClass('checked');
            if($('this:checked')){
               $(this).parents('.queVote li').addClass('checked');
               $(this).parents('.queVote').children(".vote-btn").removeClass('disabled')
            }
        });
    });

    $(".vote-btn").on("click", function(){
        $(this).hide();
        $(this).prev('.poll-que-description').children('li').addClass('voted');
    })

    //Poll Question listing 
    $(".pollQuestion").each(function() {
        var that = $(this);
        $(this).find("input[type=radio]").on("click", function(){
            that.find('li').removeClass('checked');
            if($('this:checked')){
               $(this).parents('.pollQuestion li').addClass('checked');
               $(this).parents('.pollQuestion').find(".feed-vote").removeClass('disabled')
            }
        });
    });

    $(".feed-vote").on("click", function(){
        $(this).hide();
        $(this).parents('.pollQuestion').find('li').addClass('vote-progress');
    });
});
 
$('.modal').on('hidden.bs.modal', function (e) { 
   if($('.media-popup, #endorsedList').is(':visible')){
   		$('body').addClass('modal-open');
   }
}); 

$(document).on('show.bs.modal', '.modal', function (event) {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex); 
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
$(document).on('hidden.bs.modal', '.modal', function (event) {
	 if($('.modal:visible').length){
	 	$('body').addClass('modal-open');
	 }
}); 

$(document).ready(function(){
	setTimeout(function(){
		$('#canShowPopup').val(1);
	},5000);
});

$(function() {
    function Parallax(element, options) {
      this.element = element;
      this.setting = $.extend({}, Parallax.Defaults, options);
      this.document = $(document);
      this.multiplier = 1 - this.setting.friction;
      this.$parentHeight = $(this.element).closest(this.setting.parent).outerHeight();
    };
    Parallax.Defaults = {
      'friction': 0.8,
      'height': true,
      'parent': '.parallax-wrapper'
    };

    Parallax.prototype.init = function() {
      if (this.setting.height) this.element.height(this.$parentHeight);
      this._Parallax();
    };

    Parallax.prototype._Parallax = function() {
      var self = this;
      $(window).scroll(function() {
        var fromTop = $(this.document).scrollTop();
        self.element.css({
          "transform": "translate3d(0," + (self.multiplier * fromTop) + "px, 0)",
          "background-position": "0px " + (Math.round((self.multiplier * fromTop))) + "px"
        })
      });
    };

    // plugin defination
    function Plugin(option) {
      return this.each(function() {
        var ths = $(this),
          data = ths.data('vParallax');
        if (!data) ths.data('vParallax', (data = new Parallax(ths, option)));
        data['init']();
      });
    };
    $.fn.vParallax = Plugin;
    $.fn.vParallax.constructor = Parallax;
});

/*(function(){
    function showPostType() {
        $(document).on('click','[data-type="post-type"]',function(){
            $('[data-type="post-overlay"]').addClass('active');
            $('#posttypeContent').show();
        });
    }
    function showPostEditor() {
        $(document).on('click','[data-type="post-type-list"] > li',function(){
            $('[data-type="post-type-list"] > li').removeClass('active');
            $(this).addClass('active');
            $('#posttypeContent').hide();
            $('#postEditor').show();
        });
    }
    $(document).on('click','[data-type="post-overlay"]',function(){
        $(this).removeClass('active');
        $('#posttypeContent').hide();
        $('#postEditor').hide();
    })
    showPostType();
    showPostEditor();
})()*/


// floating label js
$(document).ready(function() {

    $('.floated-label-control').each(function() {

        var that = $(this);
        $(this).on('focus', function() {
            $(this).closest('.form-group').addClass('active');
        });
        $(this).on('blur', function() {
            if ($(this).val().length == 0) {
              $(this).closest('.form-group').removeClass('active');
            }
        });

        setTimeout(function(){
            if (that.val() != '') {
                that.closest('.form-group').addClass('active');
            }
        }, 100);

    }); 
});
   

/* Header fixed */
function headerFixed() {
 if ($(window).width() >= 992) {
  $(window).scroll(function(){
    if ($(window).scrollTop() > 60) {
      $('.header-before').removeClass('header-transparent').addClass('fixed-top top-hide');

      if ($(window).scrollTop() > 100) {
          $('.header-before').addClass('top-zero').removeClass('top-hide');
      }
    } else {
        $('.header-before').addClass('header-transparent').removeClass('fixed-top top-zero');
    }
  }).scroll();
}
}



$(document).on('ready', function() {
    setTimeout(function() {
      headerFixed(); 
    },100); 
    
    // Chosen touch support.
    if ($('.chosen-container').length > 0) {
      $('.chosen-container').on('touchstart', function(e){
        e.stopPropagation(); e.preventDefault();
        // Trigger the mousedown event.
        $(this).trigger('mousedown');
      });
    }
});



$(function(){
	 $('[data-toggle="tooltip"]').tooltip({
            container: 'body'        
         });
	 privacyDropdown();
	 toggleButton();
	 feedSlider();
	 openReminder();
	 mentionTab();
	 liveFeedtoggle();
	 liveFeedScroll();

//Globle stopPropagation
	$(document).on('click','[data-type="stopPropagation"]', function (event) {
		event.stopImmediatePropagation()
	});
	editLocation();
	bannerTheme();
	backToTop();
    BackToTopSticky();
    navTabScroll();
    setTimeout(function(){dropdownOnhover(); }, 0);   

    $('.parallax-layer').vParallax();

    $(window).on('beforeunload', function() {
	    $(window).scrollTop(0);
	});

    /*if ((/Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))) {
        $('[class^="text-field"] select,  .select-box select, select').css({
            'opacity': '1',
            'font-size': '14px',
            'color': '#a3a3a3',
            'margin-top': '5px',
            'width': '100%'
        });
    }*/

});
