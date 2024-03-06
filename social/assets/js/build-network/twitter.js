
var twitter_signin = function (){
    tw_sign = this ;

};

$.extend(twitter_signin.prototype,{
    tw_sign:{},
    is_twt_login:false,

    twitter_signin:function(){
        if(this.is_twt_login && false){
            $.ajax({
                url:  site_url + "api/twitter/do_login_twt",
                type: "POST",
                success: function(data){
                    var output = JSON.parse(data);
                    tw_sign.check_twitter_user_exist(output);

                }
            });
        } else {
            // open pop up to login window
            window.open(site_url + "api/twitter/do_login_twt",'Twitter','width=500,height=500,scrollbars=yes');
        }
    },
    response_user_data:function(user_data){
        this.is_twt_login = true;

        tw_sign.check_twitter_user_exist(user_data);
    },

    check_twitter_user_exist:function(response){
        //alert(response);
        $.ajax({
            url:  site_url + "signup/verify_login",
            data: 'login_type=twitter&social_id='+response.twitter_id,
            type: "POST",
            success: function(data){

                var output = JSON.parse(data);

                if(output.result == 'success'&& output.user_status =='active'){

                    window.location = site_url+'dashboard' ;
                } else  if(output.result == 'success' && output.user_status == 'unverified_email'){
					
                     openPopDiv('socialMessage');
					 $("#alertMessage").html('Please verify email.');
					 
                }else
                    if(output.user_status =='disable_account'){
                    window.location = site_url+'account-settings?p=1' ;
                }else{
                    window.location.href = site_url+"signup-step-one";
                }
            }
        });
    }


});



function getTwitterUserDetailSignup(){
    $.ajax({
        url:  site_url + "api/twitter/get_twitter_user_data",
        type: "POST",
        success: function(data){
            var output = JSON.parse(data);
            $('#user_name').val(output.name);
            $('.social_ids').val(0);
            $('#twitter_id').val(output.twitter_id);
            $('#social_image_url').val(output.profile_image_url);

        }
    });
}


var userexists = '';

var Network_twitter = function (){
    self_tw = this ;
     // alert('twitter build network');
};

$.extend(Network_twitter.prototype,{
    self_tw      :{},
    is_twt_login :false,
    all_friends  :[],
    oyh_friends  :[],

    user_profile:function(profile){
        $('#twt_profile_pic').attr('src',profile.profile_image_url);
        $('#twt_user_name').html(profile.name);
        $('#twt_user_name').attr('href','https://twitter.com/'+profile.screen_name);
		
    },

    get_twitter_friend:function(){
     
        window.open(site_url + "api/twitter/get_twitter_friends",'Twitter','width=500,height=500,scrollbars=yes');
       
    },
    response_twitter_friend: function(friend_list, user_data){
			
            this.user_profile(user_data);
        // This function will not call ideally because get_twitter_friend call automatically just after login.
		this.check_friends_in_database(friend_list);

    },

    check_friends_in_database:function(friend_ids){

        var id_list         = new Array();
        self_tw.all_friends = [] ;

        $.each(friend_ids,function(index, value){
            id_list.push(value.id);
            self_tw.all_friends[value.id] = value ;
        });
        angular.element(document.getElementById('InviteFriendCtrl')).scope().social_list('Twitter',friend_ids);
        /*var post_data = { 
                            'social_type'     : '3' , 
                            'friend_ids'      : JSON.stringify(id_list)
                        };


        $.ajax({
            url:  site_url + "api/build_network/check_friends_list",
            data: post_data,
            type: "POST",
            success: function(data){
                self_tw.append_registered_heros(data);
                $.each(friend_ids,function(index, v){
                    //$('body').append('<input type="hidden" id="t'+v.id+'" value="'+v+'" />')
                    self_tw.all_friends[v.id] = v;
                    self_tw.all_friends[v.id].Status = data.Data[v.id].Status;
                });
            }
        });*/

    },
    append_registered_heros:function(list){
        var output = list.Data;

        $('#twitter_connect').hide();
        $('#tabContent-2 .before-fb-login-wrap').hide();
        $('#twitter_friends').show();
        $('#twitter_invite_other').show();
        if(output.length >0){
           // alert('append_registered_heros if part');

            self_tw.oyh_friends = [] ;
            $('#twitter_friends ul.suggestions').html('');

            $.each(output,function(index, value){
                self_tw.oyh_friends.push(value.social_id);

                //console.log( self_tw.all_friends[value.social_id] );

                var img_url     = self_tw.all_friends[value.social_id].profile_image_url;
                var name        = self_tw.all_friends[value.social_id].name;
               // var user_link   = 'https://twitter.com/'+self_tw.all_friends[value.social_id].screen_name;
			    var user_link   = site_url+'profile-view/'+value.user_id;

                var html = '<li id="twitter_'+value.social_id+'">  <img src="'+ img_url+'" width="50" height="50" alt="" class="left">'+
                    ' <div class="text"> <a class="bold" href="'+user_link+'">'+name+'</a><br>' ;
                if(value.is_hero == null)
                    html += ' <input name="" type="checkbox" value="'+value.social_id+'" class="registered_friend">';

                html += '</div>  </li> ';

                $('#twitter_friends ul.suggestions').append(html);
				if (!$('#twitter_friends ul.suggestions li').find('.registered_friend').length) {
					$('#twitter_friends').find('.twitter-addhero').hide();			
				}

            });


        } else {
           // alert('append_registered_heros else part'); 
            var html = '<li style="width:auto;text-transform:none;">Invite Twitter followers to build  your network.</li> ';
            $('#twitter_friends .msg').hide();
            $('#twitter_friends ul.suggestions').html(html);
			$('#twitter_friends').find('.twitter-addhero').hide();
			$('#twitter_new_friends').hide();
        }

    },

    invite_registered_heros:function(){

        var selected_friend = [] ;
        $('#twitter_friends ul li .registered_friend').each(function(){

            if( $(this).is(':checked') ){

                selected_friend.push($(this).val());
            }
        });
        if(selected_friend.length >0) {
            var post_data = { 
                                'social_type'     :3 , 
                                'social_ids'      :JSON.stringify(selected_friend) 
                            };
            $.ajax({
                url:  site_url + "api/build_network/AddConnectionViaSocial",
                data: post_data ,
                type: "POST",
                success: function(data){

                    for (var i=0;i<selected_friend.length;i++)
                    {
                        $('#twitter_'+selected_friend[i]).hide();
                    }
                    
                    $('input.registered_friend').attr('checked', false);
					
                    if (!$('ul.suggestions li:visible').length) {
						$('#twitter_friends').find('.gray-box').hide();
						$('#twitter_friends').find('.invite').css('border','1px solid #D6D6D6');
				 	}
                }
            });
        }


    },
    non_OYH_connection:function(){
        //console.log(self_tw.all_friends);
        $('#twitter_new_friends ul.suggestions').html('');
        if(this.all_friends.length >0 ){
            $('#twitter_new_friends').show();
            $('#twitter_invite_other').hide();
			for(var id in this.all_friends)
			{
				if(jQuery.inArray(id, self_tw.oyh_friends) == -1)
				{
					
                        var img_url     = this.all_friends[id].profile_image_url;
                        var name        = this.all_friends[id].name;
                        var user_link   = 'https://twitter.com/'+self_tw.all_friends[id].screen_name;
                        var SocialID    = id;
                        var subhtml = '';
                        if(this.all_friends[id].Status=='1'){
                            subhtml        = ' Already Registered';
                        } else if(this.all_friends[id].Status=='3'){
                            subhtml        = ' Already Invited';
                        } else {
                            subhtml        = ' <input class="new_connection" id="'+SocialID+'" type="checkbox" value="'+SocialID+'">\
                                                        <label for="'+SocialID+'" ></label>';
                        }
                        var html = '<li id="new_member_'+SocialID+'">\
                                                    <img src="'+ img_url+'" width="50" height="50" alt="" class="left">'+
                                                  ' <div class="text checkbox check-default">\
                                                        <a class="bold color-grey" href="'+user_link+'" target="_blank" >'+name+'</a><br><span id="wrap_user_'+SocialID+'">'+
                                                      subhtml +'</span> </div>\
                                                </li> ';

                    $('#twitter_new_friends ul.suggestions').append(html);
                   
				}
			}
		} else {
			 var html = '<li>  No friend joined yet </li> ';
			$('#invite_non_oyh_friend').hide();
			$('#twitter_new_friends ul.suggestions').append(html);
		}
		$('#twitter-invite-btn').removeClass('btn-green').addClass('btn-gray');
    },


    invite_non_OYH_friends:function(friend_str){
			LoginSessionKey=LoginSessionKey;
            window.open(site_url+"api/twitter/post_direct_message?list="+friend_str+'&loginsessionkey='+LoginSessionKey,'Twitter','width=500,height=500,scrollbars=yes');
    },

    hide_invited_non_OYH_friend:function(){

        $('#twitter_new_friends ul li .new_connection').each(function(){

            if($(this).is(':checked')){
                $('#new_member_'+$(this).val()).hide();
            }
        });
        $('input.new_connection').attr('checked', false);



    }




});



/***************ACCOUNT SETTING STARTS ************/


var twitter_acc = function(){
    //alert('constructor Facebook account ');
    self_twt = this;

};

$.extend(twitter_acc.prototype,{
    self_twt :{},
    is_twt_login:false,

    get_user_data:function(){
        // open pop up to login window
        window.open(site_url + "api/twitter/twt_login_account_setting",'Twitter','width=500,height=500,scrollbars=yes');
    },

    response_user_data:function(user){
        this.is_twt_login = true;
        this.check_twitter_id_in_db(user);
    },
    check_twitter_id_in_db:function(user){


        var id = user.twitter_id;
        $.ajax({
            url:  site_url + "member/check_social_id_exist",
            data: 'social_type=2&social_id='+id,
            type: "POST",
            success: function(data){

                if(data == 1){
                    // id attach with this account
                    //   alert('now id attach');
                    $('#twconect').parent().hide();
                    //$('.detach-region').hide();
                    $('.detach-region.tw-connect').show();
                    $('#twitter_public_url').attr('href','https://www.twitter.com/'+user.screen_name);

                } else {
                    // id already exist with other account
                    openPopDiv('socialAccount');
                }
            }
        });

    },
    detach_twitter_account:function(){
        $('#twconect').parent().show();
        $('.detach-region.tw-connect').hide();
        closePopDiv('detachSocialAccount');

        $.ajax({
            url:  site_url + "member/detach_social_network",
            data: 'social_type=2',
            type: "POST",
            success: function(data){
                /*  $('#fbconect').parent().show();
                 $('.detach-region.fb-connect').hide();
                 closePopDiv('detachSocialAccount');*/
            }
        });
    },

    get_user_public_url:function(id){
        $.ajax({
            type:'post',
            url: site_url+'api/twitter/twitter_lookup',
            data: 'twitter_ids='+id,
            success: function(data){
                $('#twitter_public_url').attr('href','https://www.twitter.com/'+data.screen_name);
            }
        });
    }


});

function update_invite_user(id){
    angular.element(document.getElementById("InviteFriendCtrl")).scope().invited_user("twitter",id);
}