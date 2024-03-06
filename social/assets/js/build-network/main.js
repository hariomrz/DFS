//Ready Function
$(function(){
    try {
        $(".designer").css({'opacity':'0'});
        $('select.designer').css({'opacity':'0'});
// radio button
        $(".designer").customInput({});
// Checkbox
        cBoxfn();
//Designer select box
        designerSelect();
//Function Signup input focus
        inputFocus();
// Safari issue of select dropdown
        SelectOptWebkit();

//Function For Tab Menu
        tabMenu();

        signupAccordion();
        accordionMultiOpen();
//Function for textarea auto grow
        $(".expandField").autoGrow();
//Function for Build Network page Tab
        $('#dynamicTab li').click(dynamicTab);
//Function for Email change
        changeEmail();

        detachAccount();
		
		$(document).mouseup(function(){
			if($('#disconnect-tooltip').is(':visible')){
				$('#disconnect-tooltip').hide();
			}
		});
		$('#btnHero').mouseup(function(){
			return false;
		});
		
	}catch (e){}
	
	$('[data-js="textarea"]').focus(function(){
		$(this).height(45);
	});
	
});



/*var listScrollApi;*/


//Function Signup input focus
function inputFocus(){
    $('select.designer, input[type="text"], input[type="password"], input[type="email"]').focus(function(){

        $(this).parent('.text-field').addClass('focus');
    });
    $('select.designer, input[type="text"], input[type="password"], input[type="email"]').blur(function(){
        $(this).parent('.text-field').removeClass('focus');
    });
}
$(function(){
    $('#sitesearch').focus(function(){

        $(this).parent('.site-search').addClass('focus');
    });
    $('#sitesearch').blur(function(){
        $(this).parent('.site-search').removeClass('focus');
    });
});
//Function for Build Network page Tab
function dynamicTab(){
    if($(this).hasClass('active')){
        return false;
    }
    $('#dynamicTab li').removeClass('active');
    $(this).addClass('active');
    $('#tab1, #tab2, #tab3, #tab4').hide()
    $('#'+$(this).attr('data-rel')).fadeIn();
}




$(window).load(function(){
    $('a ,input').each(function() {
        $(this).attr("hideFocus", "true").css("outline", "none");
    });
});
// Checkbox
function cBoxfn(){
//  $(".cbox, .cbox-selected").unbind('click');
    $(".cbox, .cbox-selected").bind("click", function () {
        if ($(this).hasClass("disabled")) $(this).attr("title", "Disabled");
        else if ($(this).attr("class") == "cbox") {
            $(this).children("input").attr("checked", true);
            $(this).removeClass().addClass("cbox-selected");
            $(this).children("input").trigger("change")
        } else if ($(this).attr("class") == "cbox-selected") {
            $(this).children("input").attr("checked", false);
            $(this).removeClass().addClass("cbox");
            $(this).children("input").trigger("change")
        }else{
            return false;
        }
    });
    var allCbox=$('.cbox input');
    allCbox.each(function(){
        if($(this).is(':checked')) {
            $(this).parent().removeClass().addClass('cbox-selected');
        }
    });

}
//End

//radio button
jQuery.fn.customInput = function(){
    $(this).each(function(i){
        if($(this).is('[type=checkbox],[type=radio]')){
            var input = $(this);
            // get the associated label using the input's id
            var label = $('label[for='+input.attr('id')+']');

            //get type, for classname suffix
            var inputType = (input.is('[type=checkbox]')) ? 'checkbox' : 'radio';
            // wrap the input + label in a div
            $('<div class="custom-'+ inputType +'"></div>').insertBefore(input).append(input, label);

            // find all inputs in this set using the shared name attribute
            var allInputs = $('input[name='+input.attr('name')+']');

            // necessary for browsers that don't support the :hover pseudo class on labels
            label.hover(
                function(){
                    $(this).addClass('hover');
                    if(inputType == 'checkbox' && input.is(':checked')){
                        $(this).addClass('checkedHover');
                    }
                },
                function(){ $(this).removeClass('hover checkedHover'); }
            );

            //bind custom event, trigger it, bind click,focus,blur events
            input.bind('updateState', function(){
                if (input.is(':checked')) {
                    if (input.is(':radio')) {
                        allInputs.each(function(){
                            $('label[for='+$(this).attr('id')+']').removeClass('checked');
                        });
                    };
                    label.addClass('checked');
                }
                else { label.removeClass('checked checkedHover checkedFocus'); }

            })
                .trigger('updateState')
                .click(function(){
                    $(this).trigger('updateState');
                })
                .focus(function(){
                    label.addClass('focus');
                    if(inputType == 'checkbox' && input.is(':checked')){
                        $(this).addClass('checkedFocus');
                    }
                })
                .blur(function(){ label.removeClass('focus checkedFocus'); });
        }
    });
};
//End

//Designer select box
function designerSelect(){
    $("select.designer").change(function () {
        var ds1 = "";
        var deId = this.id;
        $("#"+ deId +" option:selected").each(function () {
            ds1 = $(this).text();
        });
        $(this).prev().text(ds1);
    }).change();
}
//end
// Safari issue of select dropdown
function SelectOptWebkit(){
    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1){
        setTimeout(function(){
            $('select.designer').css( 'line-height' , '28px');
        });
    }
}

function addThumbnail(){
    $('.browse input[type=file]').css({'opacity':'0'});
    $('.browse input[type=file]').change(function(){
        $(this).prev().val($(this).val().substr(12));
        var flnmarr = $(this).val().split('\\');
        var arrLen = flnmarr.length;
        $(this).prev().prev().val(flnmarr[arrLen-1]);
    });
};
//function From Tab Menu
function tabMenu(){
    $('.menu-tab').bind('click',function(){
        if($('#mainNav').is(':visible')){
            $('#mainNav').slideUp();
        }
        else{
            $('#mainNav').slideDown();
        }
    });
}
//Function for right col accordion
function signupAccordion(){
    //$('.acc-content').hide();
    //$('.acc-head:third').addClass('active').next('.acc-content').show();
    $('#oneOpen .acc-head').click(function(){
        if( $(this).next('.acc-content').is(':visible') ) {
            $(this).removeClass('active').next('.acc-content').slideUp();
        }
        else{
            $('.acc-head').removeClass('active').next('.acc-content').slideUp();
            $(this).addClass('active').next('.acc-content').slideDown();
        }
    });
}


function accordionMultiOpen(){
    //$('.acc-content').hide();
    //$('.acc-head:third').addClass('active').next('.acc-content').show();
    $('#multiOpen .acc-head').click(function(){
        if( $(this).next('.acc-content').is(':visible') ) {
            $(this).removeClass('active').next('.acc-content').slideUp();
        }
        else{
            //$('.acc-head').removeClass('active').next('.acc-content').slideUp();
            $(this).addClass('active').next('.acc-content').slideDown();
        }
    });
}


//Function for Email change

function changeEmail(){
    $('.changeEmail').live('click',function(){
        $('.cancellink').css({'display':'inline-block'});
        $('#emailChangeview').hide();
        $('.change-email').show();
        $(this).text('Save');
        $(this).removeClass( );
        $(this).addClass('saveemail');
    });
    $('.saveemail').live('click',function(){
        $('.cancellink').css({'display':'none'});
        $('#emailChangeview').show();
        $('.change-email').hide();
        $(this).text('Change');
        $(this).removeClass('saveemail');
        $(this).addClass('changeEmail');
    });
    $('.cancellink').live('click',function(){
        //alert('a');
        $('#emailChangeview').show();
        $('.change-email').hide();
        $('.saveemail').text('Change');
        $('.saveemail').addClass('changeEmail');
        $('.changeEmail').removeClass('saveemail');
        $(this).hide();

    });
}

function detachAccount(){
    $('.account-link li a#fbconect').click(function(){
        /* $(this).parent().hide();
       $('.detach-region').hide();
        $('.detach-region.fb-connect').show();*/
    });
    $('.account-link li a#twconect').click(function(){
        /*  $(this).parent().hide();
        $('.detach-region').hide();
        $('.detach-region.tw-connect').show();*/
    });
    $('.account-link li a#gplusconect').click(function(){
        /*$(this).parent().hide();
        $('.detach-region').hide();
        $('.detach-region.gplus-connect').show();*/
    });
    $('.account-link li a#linkdinconect').click(function(){
        /* $(this).parent().hide();
        $('.detach-region').hide();
        $('.detach-region.linkedin-connect').show();*/
    });
}

//save search tooltip
function saveSearch(){
    if ($('#saveTip').is(':hidden')){
        $('#saveTip').fadeIn(300);
    }
    else {
        $('#saveTip').fadeOut(300);
    }
    $(document).mouseup(function(){
        $('#saveTip').fadeOut(300);
    });
    $('#saveTip').mouseup(function(){
        return false;
    });
}

//end

//global dropdown onclick function
function dropDown(e){
    e = '#'+e
    if ($(e).is(':hidden')){
        $(e).fadeIn(200);
    }
    else {
        $(e).fadeOut(200);
    }
    $(document).mouseup(function(){
        $(e).fadeOut(200);
    });
    $(e).mouseup(function(){
        return false;
    });
}
//end
function fixThumbnailMargins() {
    try{
    $('.row-fluid').each(function () {
        var $thumbnails = $(this).children(),
            previousOffsetLeft = $thumbnails.first().offset().left;
        $thumbnails.removeClass('first-in-row');
        $thumbnails.first().addClass('first-in-row');
        $thumbnails.each(function () {
            var $thumbnail = $(this),
                offsetLeft = $thumbnail.offset().left;
            if (offsetLeft < previousOffsetLeft) {
                $thumbnail.addClass('first-in-row');
            }
            previousOffsetLeft = offsetLeft;
        });
    });
    }catch (e){}
}

// Fix the margins when potentally the floating changed
$(window).resize(fixThumbnailMargins);
fixThumbnailMargins();

function count_psotmessage_char(val, counter_id ,msg_length,e) { 
        var len = val.value.length;
         var code = e.keyCode;
        if (len >= msg_length  && code!==46 && code!==8 &&code!==37 && code!==39 && code!==13) {
            $('#'+counter_id).css({'visibility':'visible','color':'red','float':'left'}).text('0 characters remaining');
            val.value = val.value.substring(0, msg_length);
            this.event.preventDefault();   
           
        } else {
            $('#'+counter_id).text((msg_length - len)+' characters remaining');
            $('#'+counter_id).css({'visibility':'visible','color':'','float':'left'})
            return true;
        }
    }