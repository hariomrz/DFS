/*BUILD NETWORK FUNCTIONS START*/

var Invite_Email = function(){
   // alert('constructor Email');
    self_email = this;
};

$.extend(Invite_Email.prototype,{
    self_email : {},


    showMore :function() {
        $('.more-email').children('input').val('').removeClass('input-error');
        $('.more-email').show();
        $('#show_more').hide();
        $('#show_less').show();
    },
    showLess:function(){
        $('.more-email').hide();
        $('#show_more').show();
        $('#show_less').hide();
    },
    submitEmails:function(){
		
        var err_count = 0;
        var has_value = false;
        var all_emails = [] ;
        $('.invite_email').removeClass('input-error');
        $('.msg-green').hide();
        $('#common_err_msg').hide();
		
		if($('#email_message').val()=='')
		{
			var message = $("#email_message").attr('placeholder'); 
		}
		else
		{
			var message = $('#email_message').val();
		}
	
        $('.invite_email').each(function(){

            var email = $(this).val();
            if(email != ''){
                has_value = true;
                // collect all email in array
                all_emails.push($.trim(email));
            }

            if(!self_email.validateEmail(email) && email != ''){
                $(this).addClass('input-error');
                err_count++;
            }
        });

        if(!has_value){
            $('#common_err_msg').html('Please enter at least one email address.');
            $('#common_err_msg').show();
            return false;
        } else if(message == '') {
            $('#common_err_msg').html('Please enter message.');
            $('#common_err_msg').show();
            return false;
        }

        if(err_count >0 )
		{
            return false;
        }
        else
		{
			
          //  alert('success');
		  		  
            $.ajax({
                url:  site_url + "build_network/send_native_invitations",
                data: 'message='+message+'&emails='+JSON.stringify(all_emails),
                type: "POST",
                success: function(data){
                    var output = JSON.parse(data);

                    $('#email_message').val('');
                    var invited=output.invited;
                    var registered = new Array();
                    $.each(output.registered, function(key, value){
                        registered.push(value);
                    });


                    $('.invite_email').each(function(){

                        var email = $(this).val();
                        if(jQuery.inArray(email,invited ) != -1){
                            $(this).next().children().show();
                            $(this).next().children().html('Already invited');
                        }
						
                        if(jQuery.inArray(email,registered) != -1){
                            $(this).next().children().show();
                            $(this).next().children().html('Already Registered');
                        }
						
						
                    });
					
					$("#success_invited_message").html('Successfully Invited');		
					$('.invite_email').val('');
					$('.invite_email').html('');
					$('#email_message').val('');
					$('#message_counter').html('300 characters remaining');
				

                }
            });
        }



    },
    validateEmail:function(sEmail){
        var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (filter.test(sEmail)) {
            return true;
        } else {
            return false;
        }
    },
    countChar: function(val, counter_id ,msg_length) {
    var len = val.value.length;
    if (len >= msg_length) {
        $('#'+counter_id).text('0 characters remaining');
        val.value = val.value.substring(0, msg_length);
    } else {
        $('#'+counter_id).text((msg_length - len)+' characters remaining');
    }
}



});

$(document).on("click",".add-as-hero",function(){
    var hero_id = $(this).attr('rel');
    $.ajax({
				url:  site_url + "build_network/add_search_user_as_hero",
				type: "POST",			
				data :'hero_id='+hero_id,
				success: function(response)
					{
                                           // $('#search_result_area').html(response);
                                           // openPopDiv('memberPopup');
											
								
					}
				});
});

function searchContent(keyword)
{
    $('#err_search').hide();
	var count = 0 ;
	if(keyword.indexOf('@')>0)
	{
		if(!validateEmail(keyword))
		{
			$('#err_search').fadeIn("fast", function() {
				$(this).html('Please enter a valid email address');
				//$(this).fadeOut(3000, function(){$(this).html(' ');});
			});	
			count++;
		}
	}
	else
	{
		var namePattern = /^[A-Za-z. \-\']+$/;
		if(!namePattern.test(keyword) || keyword ==' ')
		{
			$('#err_search').fadeIn("fast", function() {
					$(this).html('Please enter a valid name or email address.');
	            //$(this).fadeOut(3000, function(){$(this).html(' ');});
		    });		
			count++;
		}
	}
	if(count ==0)
	{
			$.ajax({
				url:  site_url + "build_network/search",
				type: "POST",			
				data :$('#form_search_page').serialize(),
				success: function(response)
					{
                                            $('#search_result_area').html(response);
                                            $('#memberPopup').show();
											
								
					}
				});
	}
		
		return false;
}



// Validate email id and return true if valid else false return
function validateEmail(sEmail) {
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    if (filter.test(sEmail)) {
        return true;
    } else {
        return false;
    }
}
