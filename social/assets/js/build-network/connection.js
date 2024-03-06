var Connection = function(){
    self_con = this ;
};

$.extend(Connection.prototype,{
    self_con : {},

    pagination_data:function(){
        var output = {};
        output.total_user = $('#total_users').val();
        return output;
    },

    apply_relation_filter:function(){

        var all_relation_ids = [];
        var selected_relation_ids = [];
        var output = {};
        $("#showList input[type='checkbox']").each(function(){
															
			if($(this).val()!='')
			{
             all_relation_ids.push($(this).val());
			}
			
            if($(this).is(':checked')){
                selected_relation_ids.push($(this).val());
            }
         });
	
        if(selected_relation_ids.length >0){
            output.relation_ids = JSON.stringify(selected_relation_ids);
        } else {
            output.relation_ids = JSON.stringify(all_relation_ids);
        }
		
		if ($('#selectall').is(":checked"))
		{
			if($('#selectall').val()==''){
		 		output.relation_ids = JSON.stringify(all_relation_ids);
			}
		}
		

        return output;

    },
    get_filtered_heros:function(){
        $.ajax({
            url:  site_url + "connection/get_connection_by_filter",
            data: self_con.apply_relation_filter() ,
            type: "POST",
            success: function(response){
                $('#connection_user_list').html(response);
                //alert(response);
            }
        });


    },
    pagination_filter_data:function(){
        var result = self_con.apply_relation_filter();
        result.total_user = $('#total_filtered_user').val();

        return result;
    },

     disconnect_hero:function(user_unique_id){
		 
		openPopDiv('disconnectHeroPopUp');
		$("#user_unique_ids").val(user_unique_id);
		 
		 
		 }



});


var add_connection = function(){
    self_add_con = this;
};

$.extend(add_connection.prototype,{
    self_add_con : {},

    check_selected_relation:function(){ 
        var count = 0;
        $('.all_relation input').each(function(){
            if($(this).is(':checked')){
               // console.log($(this).val());
                count++;
            }
        });
        if(count ==0){ 

		$("#error_relation").html('Please select one or more relationships.').show();
            return false;
        } else
        {	
			  var formdata = $("#form_add_hero").serialize();
			 
			  var types = $("#types").val();
			
			  if($("#hero_id").val())
			  {
			   var heroid = $("#hero_id").val();
			  } 
		
			   $.ajax({
                url:  site_url + "connection/submit_add_hero",
                data: formdata,
                type: "POST",
                success: function(response)
				{	
				
		
					switch(types)
					{
					case '1':
					
						$("#addHerobutton-" + heroid).hide();
						$("#btnHero-" + heroid).show();

						setTimeout(function()
						{ 
							$("#btnHero-" + heroid).next().hide();
						},'300');
						$(".all_"+heroid).show();
						closePopDiv('addHero');
						
					break;
					case '2':
                         closePopDiv('addHero');
						 $("#addHerobutton-"+heroid).hide();
					  break;
					case '3':
						$("#userheroid").val(response);
						closePopDiv('addHero');
						$("#btnHero").show();
						$("#addHerobutton-"+heroid).hide();
					
					 break;
					 case '4':
					
						$("#userheroid").val(response);
						closePopDiv('addHero');
						$("#btnHero").show();
						$("#addHerobutton-"+heroid).hide();
						$("#disconnect-tooltip-"+heroid).show();
						$("#liid-"+heroid).hide();
						
					
					 break;
					 case '5':
                         closePopDiv('addHero');
						 $("#addHerobutton-"+heroid).hide();
					  break;
					 
					default:
					//alert('cc');
					}

						
				}
            }); 
           return false;
        }

    },
    count_char : function(val, counter_id ,msg_length) {
        var len = val.value.length;
        if (len >= msg_length) {
            $('#'+counter_id).text('0 characters remaining');
            val.value = val.value.substring(0, msg_length);
        } else {
            $('#'+counter_id).text((msg_length - len)+' characters remaining');
        }
    }
});








function disconnect_hero_popup()
{
	var user_unique_id = $("#user_unique_ids").val();
	
	      $.ajax({
                url:  site_url + "connection/disconnect_hero",
                data: 'hero_unique_id='+user_unique_id ,
                type: "POST",
                success: function(response){
                    $('#connection_'+user_unique_id).hide();
                    var total_hero = parseInt( $('#my_total_heros').html());
                    $('#my_total_heros').html(total_hero-1);
					
					$("#total_users").val(response);
					
					$("#total_user").val(response);
					closePopDiv('disconnectHeroPopUp');

                }
            });	
}

function userhero_cancel()
{
	var hero_id = $("#hero_id").val();
	window.location = site_url+'profile-view/'+hero_id;
}

function confirmsdelete(heroid,users_hero_id,userid)
{
    alertify.confirm("Are you sure, You want to disconnect this hero", function (e) {
    if (e) {
      delete_user_hero(heroid,users_hero_id,userid);	
    } else {
        return false;	
    }
});
	 
}

function delete_user_hero(heroid,users_hero_id,userid)
{
 	var types = $("#types").val();
	$("#heroid_popup").val(heroid);
	$("#users_hero_id_popup").val(users_hero_id);
	$("#userid_pop").val(userid);
	$("#types_popup").val(types);
	openPopDiv('deleteUserHeroPopUP');
	$('#disconnect-tooltip-'+heroid).hide();

}



function delete_user_hero_popup()
{
	

	var types = $("#types_search").val();
	var heroid = $("#heroid_popup").val();
	var users_hero_id = $("#users_hero_id_popup").val();
	var userid = $("#userid_pop").val();
		
	
		if(userid){ $("#addHero").hide();
		$.ajax({
			url:  site_url + "public_profile/delete_user_hero",
			data: 'hero_id='+userid+'&users_hero_id='+users_hero_id+'&types='+types,
			type: "POST",
			success: function(response){
			
				
				if(types==1)
				{
					$("#alreadyhero-" + heroid).hide();
					$("#addHerobutton-"+userid).show()	
				}
				
				if(types==6)
				{
					$("#alreadyhero-" + heroid).hide();
					$("#addHerobutton-"+userid).show()	
				}
				
				if(types==7)
				{
					$("#alreadyhero-" + heroid).hide();
					$("#addHerobutton-"+userid).show()	
				}
				
				if(types==5)
				{
					$("#alreadyhero-" + heroid).hide();
					$("#addHerobutton-"+userid).hide()	
				}
				
				/*$("#addHerobutton-" + heroid).show();
				$("#alreadyhero-" + heroid).hide();
				$*/
				
				closePopDiv('deleteUserHeroPopUP');
				
			}
		});	
	}

   
	}



function delete_user_hero_on_profile(userid,heroid)
{
  var types = $("#types").val();
  var userheroid = $("#userheroid").val();
  
       if(heroid){ $("#addHero").hide();
		$.ajax({
			url:  site_url + "public_profile/delete_user_hero",
			data: 'hero_id='+userid+'&userid='+userid+'&types=3'+'&userheroid='+heroid,
			type: "POST",
			success: function(response){
				
				closePopDiv('deleteUserHeroOnProfile');
				$('#disconnect-tooltip, #btnHero').hide(); 
				$('#addHerobutton-'+userid).show();
				
				
				}
		});	
	}
 
  
  
}



function send_single_message_connection(username,usersid)
{ 	
		openPopDiv('sendMessage');
		$("#to_names").html('');
		$("#to_names").val('');
		$("#Subject").val('');
		$("#message").val('');

		$("#userid_hero_names").html(username);
	
		var valid = '';
		var valname = '';
	    var htmlstr = '';
		
		valid = usersid;
		valname = username;
	
		htmlstr += '<input type="text" name="to_names[tags]['+valid+'-a]" value="'+valname+'" class="tag" />';
		
		$("#toname").html(htmlstr);
		$('input.tag').tagedit(); 
		

}  

function submitmessage_connection()
{
	var subject = $("#Subject").val();
	var message = $("#message").val();
	var to_names = $("#to_names").val();
	
	 if(to_names == ''){
        $('#to_names_error').html('Please enter name.').show();
        return false;
    }
	if(subject == ''){
        $('#to_subject_error').html('Please enter subject.').show();
        return false;
    }
	if(message == ''){
        $('#to_message_error').html('Please enter message.').show();
        return false;
    }
	
	 $.ajax({
		  url:  site_url + "build_network/send_message",
                data:$('#sendmessage').serialize(),
                type: "POST",
                success: function(data)
				{
					closePopDiv('sendMessage');
                }	
			
	})
	 
}

$(function(){
 
    // add multiple select / deselect functionality
$(document).on('click',"#selectall",function()
	{  
		$('.case').attr('checked', this.checked);
		
		if($('.case:checkbox:checked').length>0) 
		{
			document.getElementById("selectall").checked = true;
			$('.cc').attr('checked', true);
		} 
		else 
		{
			$("#selectall").removeAttr("checked");
			$(".cc").removeAttr("checked");
		}     
			var val = [];
			$('.case:checkbox:checked').each(function(i){
			val[i] = $(this).val();
		});
		
    });
});



function community_list_on_serach(user_id)
{	
	
	$.ajax({
				   url:site_url+'community/community_list_on_serach',
				   data:'user_id='+user_id,
				   type:'POST',
				   success:function(data){
					   
					  $("#community_list").html(data);
					  openPopDiv('communityPopup');
            
				   }
	 });
	
}
	
