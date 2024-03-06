// Placeholder Function
function placeHolder(){
	var browserName=navigator.appName;
	if (browserName=="Microsoft Internet Explorer") {
		$('[placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur().parents('form').submit(function() {
		  $(this).find('[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  });
		});
	} else {
		// Focus	
		$('[type="text"], [type="password"],[type="email"], textarea, select.form-select').focus(function() {
		  var input = $(this);
			input.parents('[data-type="focus"]').addClass('focus');
		}).blur(function() {
		  var input = $(this);
			input.parents('[data-type="focus"]').removeClass('focus');
		});
		}
}

// Option Drop down
function userActiondropdown(){
   
    $('.user-action').bind('click',function(event){
            $('.inActive').hide();
            event.stopImmediatePropagation();
            event.preventDefault();
            $(this).addClass('selected');
            var dropdwonWidth = $('.userActiondropdown').width();
            if(!$('.userActiondropdown').is(':visible') && $('ul.userActiondropdown li').length > 0){
                    $('.userActiondropdown').css({left:$(this).offset().left-dropdwonWidth+26, top:$(this).offset().top+25 }).show();
            } else {
                    $('.user-action').removeClass('selected');
                    $('.userActiondropdown').hide();

            }
            if($('ul.userActiondropdown li').not(".ng-hide").length <= 0){
                ShowErrorMsg(PermissionDeniedAction);
                $('ul.userActiondropdown').css("display","none");
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
    });
	
	
 $('body').mouseup(function(){
	   $('.user-action, .inactive').removeClass('selected');
		$('.userActiondropdown').hide();
		$('.inActive').hide();
	 });
}

// Option Drop down
function emailActiondropdown(){   
    
    $('.email-action').bind('click',function(event){
        $('.inActive').hide();
        event.stopImmediatePropagation();
        $(this).addClass('selected');
        var dropdwonWidth = $('.emailActiondropdown').width();
        if(!$('.emailActiondropdown').is(':visible') && $('ul.emailActiondropdown li').length > 0){
            $('.emailActiondropdown').css({left:$(this).offset().left-dropdwonWidth+26, top:$(this).offset().top+25 }).show();
        } else {
            $('.email-action').removeClass('selected');
            $('.emailActiondropdown').hide();
        }
        
        if($('ul.emailActiondropdown li').not(".ng-hide").length <= 0){
            ShowErrorMsg(PermissionDeniedAction);
            $('ul.emailActiondropdown').css("display","none");
            $("html, body").animate({ scrollTop: 0 }, "slow");
        }
    });
	
	
    $('body').mouseup(function(){
        $('.email-action, .inactive').removeClass('selected');
        $('.emailActiondropdown').hide();
        $('.inActive').hide();
    });
}

// Option Drop down
function smtpActionDropdown(){   
    
    $('.smtp_action').bind('click',function(event){
        $('.inActive').hide();
        event.stopImmediatePropagation();
        $(this).addClass('selected');
        var dropdwonWidth = $('.smtpActiondropdown').width();
        if(!$('.smtpActiondropdown').is(':visible') && $('ul.smtpActiondropdown li').length > 0){
            $('.smtpActiondropdown').css({left:$(this).offset().left-dropdwonWidth+26, top:$(this).offset().top+25 }).show();
        } else {
            $('.smtp_action').removeClass('selected');
            $('.smtpActiondropdown').hide();
        }
        
        if($('ul.smtpActiondropdown li').not(".ng-hide").length <= 0){
            ShowErrorMsg(PermissionDeniedAction);
            $('ul.smtpActiondropdown').css("display","none");
            $("html, body").animate({ scrollTop: 0 }, "slow");
        }
    });
	
	
    $('body').mouseup(function(){
        $('.smtp_action, .inactive').removeClass('selected');
        $('.smtpActiondropdown').hide();
        $('.inActive').hide();
    });
}

function inActivemenu(){
	$('.inactive').on('click',function(event){
		$('.userActiondropdown').hide();
		event.stopImmediatePropagation();
 		$(this).addClass('selected');
		var dropdwonWidth = $('.inActive').width();
		if(!$('.inActive').is(':visible')){
			$('.inActive').css({left:$(this).offset().left-dropdwonWidth+26, top:$(this).offset().top+25 }).show();
		} else {
			$('.user-action').removeClass('selected');
			$('.inActive').hide();
			
		}
	});
}


//User Setting dropdown

function userSettingdropdown(){
	$('.user-setting').on('click',function(){
   		$('.monthView').slideUp('fast');
		$('.right-control li').removeClass('selected');
		if($(this).next('.userSettingdropdown').is(':visible')){
 			$(this).next('.userSettingdropdown').slideUp('fast');	
			$(this).parent().removeClass('selected');
		}
		else{
			$(this).parent().addClass('selected');
			$(this).next('.action-dropdown').slideDown('fast');	
			}
		});
		
		$('.userSettingdropdown li a').on('click',function(){
			$('.monthView').slideUp('fast');
			$('.userSettingdropdown').slideUp('fast');
			$('.right-control li').removeClass('selected');
		});
		
	}
//User Setting dropdown

function userMonthview(){
	$('.month-view').on('click',function(){
		$('.userSettingdropdown').slideUp('fast');	
		$('.right-control li').removeClass('selected');
		if($(this).next('.monthView').is(':visible')){
 			$(this).next('.monthView').slideUp('fast');
			$(this).parent().removeClass('selected');
		}
		else{
			$(this).parent().addClass('selected');
			$(this).next('.monthView').slideDown('fast');	
			}
 		if($('.customView').is(':visible')){
			$('.customView').slideUp()
			$('.viewList').slideDown();
                        if($('ul.custom-select li').length > 0){
                            $('ul.custom-select').slideDown();
                        }
                        
			}
		 });
 	 $('.customSelect').on('click',function(){
		$('.viewList').slideUp();
                if($('ul.custom-select li').length > 0){
                     $('ul.custom-select').slideUp();
                }
		$('.customView').slideDown();
 	  });		
	  
	  $('.viewList a').on('click',function(e){
			$('.right-control li').removeClass('selected');
			$('.monthView').slideUp('fast'); 
			$('#selectedTime').html($(this).text()); 
	  });
	
	/*$('.monthView li a').on('click',function(){
			$('.monthView').slideUp('fast');
			$('.userSettingdropdown').slideUp('fast');
			$('.right-control li').removeClass('selected');
		});*/
		
		$('#submitBtn').on('click',function(){
			 $('.monthView ').hide(); 
			 $('.right-control > li ').removeClass('selected');
			});
 	}
	 
  
$(document).mouseup(function (e) {
     $('.userSettingdropdown').slideUp('fast');
	 $('.right-control > li ').removeClass('selected');
	 $('#ui-datepicker-div').hide();
	 $('.monthView ').hide(); 
   });
   
 $('.monthView, .month-view, #ui-datepicker-div').mouseup(function (e) {
		  return false;
   });
  




 
//Select Multiple Row
	
function selectRow(event){
	var OneClick = 0;
	$(document).on('click','.selectMultiplerow tr',function(){
 		
		$(this).toggleClass('selected');
		$('.selectMultiplerow tr:first-child').removeClass('selected');
		if ($('.selectMultiplerow tr').hasClass('selected')){
			$('#ItemCounter').fadeIn();
		} else {
			$('#ItemCounter').fadeOut();
		}
		var ItemCount = $('.selectMultiplerow tr.selected').size();
		$('#ItemCounter .counter').html(ItemCount);
 		
	});
	
	$(document).on('click','#deleteSelectedrow',function(){
		$('.selectMultiplerow tr.selected').remove();
		var ItemCount = $('.selectMultiplerow tr.selected').size();
		$('#ItemCounter .counter').html(ItemCount);
	 });
 }
 
	
function AlternateRow(){
	$('.OddRow-BG tr:odd').addClass('odd-row');
	$('.EvenRow-BG tr:even').addClass('even-row');
}

//Function For Accrodion
function AccordionTable(){
	$('.accordion-wrap .icon-plus').on('click', function(){
		$(this).hide();
		$(this).next().show();
		var dataRole = $(this).attr('data-role');
		$(this).closest('li').find('.expandable-wrap').slideDown().promise().done(function(){
			//RowCounter(dataRole);
		});
	});
	$('.accordion-wrap .icon-minus').on('click', function(){
		$(this).hide();
		$(this).prev().show();
		var dataRole = $(this).attr('data-role');
		$(this).closest('li').find('.expandable-wrap').slideUp().promise().done(function(){
			//RowCounter(dataRole);
		});
	});
}


function RowCounter(id){
 	var RowCount = 	$('#'+id+' tr').filter(function() {return $(this).is(':visible')});
	if(RowCount.length > 10){
		$('#'+id).animate({height: '366px'});
  	} else {
		$('#'+id).css({height: '38px'});
	}
}

 /*Custom Scroll Bar*/
//for dynamic scroll 

 
	var listScrollApi;
	function addListScroll(ScrollSectionID) {
		var pane = $('#' + ScrollSectionID);
		pane.jScrollPane(
			{
				showArrows: false,
				maintainPosition: true
			});
		listScrollApi = pane.data('jsp');
	}

//Category Name Active
function activeCategory(){
	 $('.globalCheckbox').on('change',function(){
		$(this).parent().parent().toggleClass('focus');
		$(this).parent().toggleClass('icon-checked');
	 });
	 
	 if($(this).attr('checked',true)){
		 $(this).parent().parent().addClass('focus');
		 $(this).parent().addClass('icon-checked');
		}
  }
  
function filerToggle(){
$('#showHidefilter').on('click',function(){
	if($('.filter-view').is(':visible')){
		 $('.filter-view').slideUp();
		 $(this).text(Media_ShowAdvanceFilters);		
		}
	else{
		$('.filter-view').slideDown();
		 $(this).text(Media_HideAdvanceFilters);		
		}	
	});	
	
} 
  //Function For Messages Tab
function  messagesTab(){
 	$('[data-type="tab"] li a').click(function(){
 	 if($(this).hasClass('selected')){return false;}
		$('[data-type="tab"] li a').removeClass('selected');
		$(this).addClass('selected');
		$('#plainText, #messagesHtml').hide();
		$('#'+$(this).attr('data-rel')).fadeIn('slow');
   });
}
 
 
//Function Accordion

function tablesAccordion(){
	$('.accordion-tab').on('click',function(){
		$('.accordion-content').removeClass('selected');
			if($(this).next('.accordion-content').is(':visible')){
				$(this).next('.accordion-content').slideUp();
				$(this).removeClass('selected');
				}
			else{
				$(this).next('.accordion-content').slideDown();
				$(this).addClass('selected');
				}	
		});
	
}
 
//communicateMorelist Tooltip	
function communicateMorelist(){
	var addTimer;
	$('[data-tip=tooltip]').on("mouseenter",function(){
  		 if(addTimer)
			clearTimeout(addTimer);
  			var tipwd = $('.communicate-morelist').width();
 			$('.communicate-morelist').css({left:$(this).offset().left-tipwd + 50,top:$(this).offset().top+35}).show();
  			});	
  			$('[data-tip=tooltip]').on("mouseleave",function(){
 					addTimer=setTimeout(function(){$('.communicate-morelist').hide()},100);
				});
			$('.communicate-morelist').on("mouseenter",function(){
				if(addTimer)
				clearTimeout(addTimer);
			});
			$('.communicate-morelist').on("mouseleave",function(){
 		 		 $(this).fadeOut('slow');
		});
}



 
function hideShowgraph(){
$('#hideShowgraph').on('click',function(){
	if($('.media-graph').is(':visible')){
		 $('.media-graph').slideUp();
 		 $(this).removeClass('selected');		
		}
	else{
		$('.media-graph').slideDown();
		$(this).addClass('selected');
 		}	
	});	
	
} 


/*function commonSearch(){
	$('#searchButton,#mediaAnalyticSearch,#betaSearch,#supportErrorSearch').on('click',function(){
		$('.search-block').fadeIn();
  	  });
	  
	  $('#clearText').on('click',function(){
		  	$('.search-block').hide();
			$('#searchField').val('');
		  });
	  
	}*/

function commonSearch(){
	$('#searchButton,#mediaAnalyticSearch,#betaSearch,#supportErrorSearch,[data-type="search"] .icons').on('click',function(){
		$('.search-block').fadeIn();
		$(this).parent('.search-group').addClass('open');
	});
  
   $('#clearText, [data-search="close"]').on('click',function(){
	  	$('.search-block').hide();
		$('#searchField').val('');
		$(this).parent('.search-group').removeClass('open');
	});
  
}

// Browse Button
function browseButton(){ 
	$('.browse input[type=file]').change(function(){
		var $thisTxt = $(this).parents('.browse').find('.textfield');
		$thisTxt.val($(this).val().substr(12));
		var flnmarr = $(this).val().split('\\'); 
		var arrLen = flnmarr.length;
		$thisTxt.val(flnmarr[arrLen-1]);
	});
}
	
/*All Data Rel Tabs*/
function TabIndexFunc() {
	$('.support-tab-nav > span').click(function () {
		var contentRel = $(this).attr('data-rel');
		var linktext = $(this).text();
        $('#PhoneTitle').text(linktext);
		if(!$('#' + contentRel).is(':visible')){
			$('.tabs-content').hide();
			$('.support-tab-nav > span').removeClass('active');
			$(this).addClass('active');
			$('#' + contentRel).fadeIn();
		}
	});
};

//All Functions
$(function(){	
	inActivemenu();
	commonSearch();
	hideShowgraph();
  	tablesAccordion();
	$('.icon-email, .name, .thumbnail40, table tr th, .desc-footer, .category-desc a, .actincomunicate, .view-listing li.active .category-desc').on('click',function(event){
            if($(event.target).hasClass('userCheckBox')) {
                return;
            }
            event.stopImmediatePropagation();
        });

 	$('[rel=tipsynw]').tipsy({fade: true, gravity: 'nw'});
	$('[rel=tipsyse]').tipsy({fade: true, gravity: 'se'});
 	placeHolder();				//Placeholder Function
	userActiondropdown();       // Option Drop down
	userSettingdropdown();		//User Setting dropdown
	userMonthview();			//User Setting dropdown
	selectRow();				//Select Multiple Row
	AlternateRow();             //Change lternate row BG
	AccordionTable();			//Function For Accrodion
 	activeCategory();			//Category Name Active
 	//selectCategory();			//Select Category
	//addCategory();				//Add Category
	//ScrollBar();				//Custom Scroll Bar
	filerToggle();
 	 messagesTab();				//Function For Messages Tab
	 communicateMorelist();
	 browseButton();
	 TabIndexFunc();				//Function for tab
	 
	 $('.sub-nav a').on('click',function(){
		 $('.sub-nav a').removeClass('selected');
			 $(this).addClass('selected');
 		 }); 
 		 
	 $('.ui-datepicker').mousedown(function(){
		return false;
	}); 
	 
});

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
        $(".defaulttext").removeClass("hide");
        $('#error_message.notifications').removeClass('active');        
    }, 5000);
}

function setMenuTabActive(tabAttr) {
    $(".main-nav ul li").removeClass('selected');
    $('[data-active="'+tabAttr+'"]').addClass('selected');
}


$(document).keydown(function (e) {  
    if (e.keyCode == 27) {  
        $('#jquery-lightbox, #jquery-overlay').remove();
    }  
});


// Dropdown get value
/*$(document).on('click','[data-getvalue="dropdown"] > li > a', function(event) {
    var ico = $(this).find('.icn').html();
    $(this).closest('.dropup, .dropdown').find('.btn > .icn, .dropdown-toggle > .icn').html(ico);
    $(this).closest('.dropup, .dropdown').find('.btn > .text').text($(this).text());    
});

$(document).on('click','[data-gethtml="dropdown"] > li > a', function(event) {
    var ico = $(this).html();
    $(this).closest('.dropup, .dropdown').find('.btn .btn-text').html(ico);
});*/

// $(window).load(function(){
// 	$('[data-scrollfix="scrollFixRight"]').theiaStickySidebar({
//         additionalMarginTop: 80
//     });
//     alert('a');	 
// })
// 

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
 
function addedtagBydmin(){
	setTimeout(function() {
		$('.tag-list > li').each(function() {
			if($(this).find('.tag-template').hasClass('added-by-admin')){
				$(this).addClass('added-by-admins'); 
			} 
		});
	}, 200);
}

addedtagBydmin();