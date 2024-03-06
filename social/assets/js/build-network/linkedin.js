var is_linkedin_loaded = false;
function checkLinkedInLoaded() {
	is_linkedin_loaded = true;
}
/* SIGN IN VIA LINKEDIN FUNCTION STARTS*/
function onLinkedInAuth_signin()
{
    IN.User.authorize(getProfile_signin);
}
function getProfile_signin()
{
	IN.API.Profile("me")
	.fields("email-address","id","first-name","last-name","picture-url","headline","date-of-birth")
	.result(checkLinkedInUserExist);
}
function checkLinkedInUserExist(profiles)
{
    var info = profiles.values[0];
    $.ajax({
        url:  site_url + "signup/verify_login",
        data: 'login_type=linkedin&my_info='+JSON.stringify(info)+'&social_id='+info.id+'&email='+info.emailAddress,
        type: "POST",
        success: function(data){
            var output = JSON.parse(data);
            if(output.result == 'success'&& output.user_status =='active'){
                window.location=site_url+'dashboard';
            } else {
                window.location.href=site_url+"signup-step1";
            }
        }
    });
}

/* SIGN IN VIA LINKEDIN FUNCTION END*/
/* SIGNUP VIA LINKEDIN FUNCTION STARTS*/
function onLinkedInAuth_signup()
{
    IN.User.authorize(getProfile_signup);
}
function check_linked_login_status(){
    if(IN.User.isAuthorized()){
        getProfile_signup();
    }
}
function getProfile_signup()
{
	IN.API.Profile("me")
	.fields("email-address","id","first-name","last-name","picture-url","headline","date-of-birth,public-profile-url")
	.result(linkedin_signup);
}

function linkedin_signup(profile){
    var info = profile.values[0];
    var email = info.emailAddress;
    var name = info.firstName+" "+info.lastName;
    var linkedin_id = info.id;
    var public_url = info.publicProfileUrl;
    $('#user_name').val(name);
    $('#email').val(email);
	$('#hid_email').val(email);
    $('.social_ids').val(0);
    $('#linkedin_id').val(linkedin_id);
    $('#linkedin_public_url').val(public_url);
    try
    {
        if(info.pictureUrl!='')  {
            $('#social_image_url').val(info.pictureUrl);
        }else{
            $('#social_image_url').val('http://static02.linkedin.com/scds/common/u/img/icon/icon_no_photo_50x50.png');
        }
    }
    catch(err){
        console.log(err);
    }

    /* This code will check if user already register then logged in them and redirect to landing page*/
    $.ajax({
        url:  site_url + "signup/verify_login",
        data: 'login_type=linkedin&my_info='+JSON.stringify(info)+'&social_id='+info.id+'&email='+info.emailAddress,
        type: "POST",
        success: function(data){
            var output = JSON.parse(data);
            if(output.result == 'success' && output.user_status =='active'){
                window.location.href = site_url;
            }else if(output.result == 'success' && output.user_status == 'unverified_email'){
					 openPopDiv('socialMessage');
					 $("#alertMessage").html('Please verify email.');
            }else if(output.user_status =='disable_account'){
                    window.location = site_url+'account-settings?p=1' ;
                }else{
                var pathn = window.location.pathname.toString();
                if(pathn != "/signup-step-one")
                   window.location.href = site_url+"signup-step-one";
            }
        }
    });
}


/* SIGNUP VIA LINKEDIN FUNCTION ENDS*/
/*BUILD NETWORK FUNCTIONS START*/

var Network_linkedin = function(){
    //alert('constructor linkedIn');
    self_ln = this;
};

$.extend(Network_linkedin.prototype,{
    self_ln : {},
    myConnection:[],
    oyh_connection:[],
    dolinkedinLogin :function() {

        IN.User.authorize(self_ln.getUsersLinkedinConnection);
    },
    getUsersLinkedinConnection:function(){
       // get user's profile info
        IN.API.Profile("me")
            .fields("email-address","id","first-name","last-name","picture-url","headline","date-of-birth,public-profile-url")
            .result(self_ln.users_profile);

        // get user's connection
        IN.API.Connections("me")
            .fields("email-address","id","first-name","last-name","picture-url","headline","date-of-birth","distance,public-profile-url")
            .result(self_ln.checkFriendsInDatabase);
    },
    users_profile:function(profile){
        var info    = profile.values[0];
        var img_url = info.pictureUrl;
        if( typeof (img_url) == 'undefined')
        {
           img_url = 'http://static02.linkedin.com/scds/common/u/img/icon/icon_no_photo_50x50.png';
        }
        $('#linkedin_user_image').attr('src',img_url);
        $('#linkedin_user_name').html(info.firstName+' '+info.lastName);
    },

    checkFriendsInDatabase:function(friend_ids){

       if(friend_ids._total == 0){
        self_ln.appendRegisteredHeros(JSON.stringify(new Array()));
        return false;
       }
        var connections = friend_ids.values;
        var linkedin_ids = new Array();
        this.myConnection = [] ;

        $.each(connections,function(index, value){
            linkedin_ids.push(value.id.toString());
            self_ln.myConnection[value.id] = value;
        });

        $.ajax({
            url:  site_url + "api/build_network/check_friends_list",
            data: 'social_type=7&friend_ids='+JSON.stringify(linkedin_ids),
            type: "POST",
            success: function(data){
                //console.log(data);
                self_ln.appendRegisteredHeros(data);
                $.each(connections,function(index, v){
                    self_ln.myConnection[v.id] = v;
                    self_ln.myConnection[v.id].Status = data.Data[v.id].Status;
                });
            }
        });

    },
    appendRegisteredHeros:function(list){
        //console.log(list.Data);
        var output = list.Data;
        $('#linkedin_connect').hide();
        $('#tabContent-4 .before-fb-login-wrap').hide();
        $('.linkdin-addhero').remove();
        $('#linkedin_friends').show();
        $('#linkedin_invite_others').show();
        if(output.length >0){

            self_ln.oyh_connection = [] ;
            $('#linkedin_friends ul.suggestions').html('');

            $.each(output,function(index, value){

                self_ln.oyh_connection.push(value.linkedin_id);

                var img_url = self_ln.myConnection[value.linkedin_id].pictureUrl;

                var name = self_ln.myConnection[value.linkedin_id].firstName+' '+self_ln.myConnection[value.linkedin_id].lastName ;
                var html = '<li id="linkedin_'+value.linkedin_id+'">  <img src="'+ img_url+'" width="50" height="50" alt="" class="left">'+
                    ' <div class="text checkbox check-default"> <a class="bold color-grey" href="'+site_url+'profile-view/'+value.user_id+'">'+name+'</a><br>';

                if(value.is_hero)
                    html += ' <input name="" type="checkbox" value="'+value.linkedin_id+'" id="linkedin_friend_'+value.linkedin_id+'" class="registered_friend"><label for="linkedin_friend_'+value.linkedin_id+'"></label>';

                          

                html += '</div>  </li> ';
				//$('#linkedin_friends .msg').hide('Connections using OhYouHero. You Don\'t Have Any Friend In This Account.');
                $('#linkedin_friends ul.suggestions').append(html);
				if (!$('#linkedin_friends ul.suggestions li').find('.registered_friend').length) {
					$('#linkedin_friends').find('.linkdin-addhero').hide();
				}

            });


        } else {
            var html = '<li style="width:auto;text-transform:none;">Invite LinkedIn connections to build business within your existing network, and help your community prosper!</li> ';
            $('#linkedin_friends .msg').hide();
            $('#linkedin_friends ul.suggestions').html(html);
			$('#linkedin_friends').find('.linkdin-addhero').hide();
            $('#linkedin_new_friends').hide();
        }

    },

    nonOYHConnection:function(){

        //console.log(this.myConnection);
        $('#linkedin_new_friends ul.suggestions').html('');
        $('#linkedin_new_friends').show();
        for(var id in this.myConnection){
            if(jQuery.inArray(id, self_ln.oyh_connection) == -1){

                    
                var con = this.myConnection[id];
                var img_url = this.myConnection[id].pictureUrl;
                var id = id;
                if( typeof (img_url) == 'undefined')
                {
                   img_url = 'http://static02.linkedin.com/scds/common/u/img/icon/icon_no_photo_50x50.png';
                }

                var name = this.myConnection[id].firstName+' '+this.myConnection[id].lastName;
                var subhtml = '';
                if(this.myConnection[id].Status=='1'){
                    subhtml        = ' Already Registered';
                } else if(this.myConnection[id].Status=='3'){
                    subhtml        = ' Already Invited';
                } else {
                    subhtml        = ' <input class="new_connection" id="'+id+'" type="checkbox" value="'+id+'"><label for="'+id+'"></label>';
                }
                var html = '<li id="new_member_'+id+'">  <img src="'+ img_url+'" width="50" height="50" alt="" class="left">'+
                ' <div class="text checkbox check-default"> <a class="bold color-grey">'+name+'</a><br><span class="wrap-user-'+id+'">'+
                subhtml +'  </div>  </li> ';

                $('#linkedin_new_friends ul.suggestions').append(html);
            }
        }
		$('#linkedin-invite-btn').hide();
    },


    inviteRegisteredHeros:function(){
        var selected_friend = [] ;
        $('#linkedin_friends ul li .registered_friend').each(function(){

            if($(this).is(':checked')){

                selected_friend.push($(this).val());
            }
        });

       // console.log(selected_friend);
        if(selected_friend.length >0) {
            $.ajax({
                url:  site_url + "api/build_network/AddConnectionViaSocial",
                data: 'social_type=7&social_ids='+JSON.stringify(selected_friend),
                type: "POST",
                success: function(data){

                    for (var i=0;i<selected_friend.length;i++)
                    {
                        $('#linkedin_'+selected_friend[i]).hide();
                    }
                    $('input.registered_friend').attr('checked', false);
					if (!$('ul.suggestions li:visible').length) {
						$('#linkedin_friends').find('.gray-box').hide();
						$('#linkedin_friends').find('.invite').css('border','1px solid #D6D6D6');
				 	}
                }
            });
        }
    },

    getSelectedMemberToInvite:function(){
        $('#linkedin_new_friends ul li .new_connection').each(function(){

            if($(this).is(':checked')){

                self_ln.saveLinkedInInvitation($(this).val());
            }

        });
    },
    saveLinkedInInvitation:function(id){
		$.ajax({
            url:site_url+"api/build_network/save_social_invitation_request",
            data:'invite_type=7&social_id='+id,
            type:"POST",
            success:function(data){
                var output=data.Data;
                self_ln.sendMessage(id,output.link);
                $('.wrap-user-'+data.Data.uid).after('Already Invited');
                $('.wrap-user-'+data.Data.uid).remove();
            }
        });
    },

    sendMessage:function(id,link){

        var BODY = {
            "recipients": {
                "values": [{
                    "person": {
                        "_path": "/people/"+id
                    }
                }]
            },
            "subject": "CommonSocialNetwork Invitation",
            "body": ' Join CommonSocialNetwork '+link
        }

        IN.API.Raw("/people/~/mailbox")
            .method("POST")
            .body(JSON.stringify(BODY))
            .result(self_ln.sendMessageCallBack(id))
            .error(function error(e) { alert ("No dice") });
    } ,
    sendMessageCallBack:function(id){

        console.log(id+' sent message');
        $('#new_member_'+id).hide();

    }



});


/*BUILD NETWORK FUNCTIONS ENDS*/

/*********ACCOUNT SETTING START ********/


var linkedin_acc = function(){
  //  alert('constructor linkedIn Account');
    self_ln = this;
};

$.extend(linkedin_acc.prototype,{
    self_ln : {},

    dolinkedinLogin :function() {

        IN.User.authorize(self_ln.getUsersLinkedinProfile);
    },
    getUsersLinkedinProfile:function(){
        // get user's profile info
        IN.API.Profile("me")
            .fields("email-address","id","first-name","last-name","picture-url","headline","date-of-birth,public-profile-url")
            .result(self_ln.users_profile);

    },
    users_profile:function(profile){
        var info = profile.values[0];
        self_ln.checkLinkedinIdInDb(info.id, info.publicProfileUrl );

    },
    checkLinkedinIdInDb:function(id,public_url){

        $.ajax({
            url:  site_url + "member/check_social_id_exist",
            data: 'social_type=4&social_id='+id+'&public_url='+public_url,
            type: "POST",
            success: function(data){

                if(data == 1){
                    // id attach with this account

                    $('#linkdinconect').parent().hide();

                    $('.detach-region.linkedin-connect').show();
                    $('#linkedin_public_url').attr('href',public_url);

                } else {
                    // id already exist with other account
                    openPopDiv('socialAccount');
                }
            }
        });

    },
    detachLinkedinAccount:function(){
        $.ajax({
            url:  site_url + "member/detach_social_network",
            data: 'social_type=4',
            type: "POST",
            success: function(data){
                $('#linkdinconect').parent().show();
                $('.detach-region.linkedin-connect').hide();
                closePopDiv('detachSocialAccount');
            }
        });
    }
});