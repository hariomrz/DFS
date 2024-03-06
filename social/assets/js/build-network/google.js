

var client_id   = google_client_id;
var scope	= google_scope;
var apiKey      = google_api_key;
var is_render   = false;
var inviteTime  = 0;
var Token       = '';
var myUrl       = '';


function handleClientLoad_1() {
        // Load the API client and auth library
        gapi.load('client:auth2', initAuth);
      }
      function initAuth() {
          
        gapi.client.setApiKey(apiKey);
        gapi.auth2.init({
            client_id: client_id,
            scope: scope
        }).then(function () {
          auth2 = gapi.auth2.getAuthInstance();
          // Listen for sign-in state changes.
          //auth2.isSignedIn.listen(updateSigninStatus);
          // Handle the initial sign-in state.
          //updateSigninStatus(auth2.isSignedIn.get());
          
        });
      }

handleClientLoad_1();
function handleClientLoad() {
    gapi.client.setApiKey(apiKey);
    window.setTimeout(checkAuth,1);
}

function checkAuth() {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: true}, handleAuthResult);
}

function handleAuthResult(authResult) {

    if (authResult && !authResult.error && is_render) {
        access_token = authResult.access_token;

        makeApiCall();
    }

    is_render = true;
    /* else {

        handleAuthClick();
    }*/
}

function handleAuthClick(event) {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: false}, handleAuthResult);
    return false;
}


function makeApiCall() {
    gapi.client.load('oauth2', 'v2', function() {
        var request = gapi.client.oauth2.userinfo.get();
        request.execute(function(resp) {
            checkGoogleUserExist(resp);
        });
    });
}

function checkGoogleUserExist(resp)
{
    $.ajax({
        url:  site_url + "signup/verify_login",
        data: 'login_type=google&social_id='+resp.id+'&my_info='+JSON.stringify(resp)+'&email='+resp.email,
        type: "POST",
        success: function(response){
            var output = JSON.parse(response);
            
            if(output.result == 'success'&& output.user_status =='active'){
                window.location = site_url+'dashboard' ;
            } else if(output.result == 'success' && output.user_status == 'unverified_email'){
                     openPopDiv('socialMessage');
					 $("#alertMessage").html('Please verify email.'); 
            }else if(output.user_status =='disable_account'){
                    window.location = site_url+'account-settings?p=1' ;
                }else{
                window.location.href = site_url+"signup-step-one";
            }
        }
    });
}



var is_render_signup =false;

function handleClientLoad_signup() {
    gapi.client.setApiKey(apiKey);
    window.setTimeout(checkAuth_signup,1);
}

function checkAuth_signup() {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: true}, handleAuthResult_signup);
}

function handleAuthResult_signup(authResult) {


    if (authResult && !authResult.error && is_render_signup) {
        access_token = authResult.access_token;

        makeApiCall_signup();
    } 
    is_render_signup = true;
    /*else {

        handleAuthClick_signup();
    }*/
}

function handleAuthClick_signup(event) {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: false}, handleAuthResult_signup);
    return false;
}


function makeApiCall_signup() {
    gapi.client.load('oauth2', 'v2', function() {
        var request = gapi.client.oauth2.userinfo.get();
        request.execute(function(resp) {
            getMyGoogleInfo(resp);
        });
    });
}

function getUserInfoByID(id) {
    console.log(id);
    gapi.client.plus.people.get({
        'userId': id
    }).execute(function(resp){
        console.log(resp);
    });
}

function getMyGoogleInfo(oauth_resp){

    gapi.client.load('plus', 'v1', function() {
        var request_me = gapi.client.plus.people.get({'userId': 'me'});

        request_me.execute(function(rep){
            Google_signup(rep, oauth_resp);
        });

    });

}

function getFriendListGoogle()
{
     auth2.signIn();
    /*gapi.client.load('plus','v1', function(){
        var request = gapi.client.plus.people.list({
            'userId': 'me',
            'collection': 'visible'
        });
        request.execute(function(resp) {
            angular.element(document.getElementById('InviteFriendCtrl')).scope().social_list('Google',resp.items);
        });
    });*/


}

function Google_signup(response,oauth_resp)
{
    var name = response.displayName;
    var email = oauth_resp.email;
    var google_id = response.id;
    var image = response.picture;
    $('#user_name').val(name);
    $('#email').val(email);
	$('#hid_email').val(email);
    $('.social_ids').val(0);
    $('#google_id').val(google_id);
    $('#social_image_url').val(image);

    /* This code will check if user already register then logged in them and redirect to landing page*/
    $.ajax({
        url:  site_url + "signup/verify_login",
        data: 'login_type=google&social_id='+response.id+'&my_info='+JSON.stringify(response),
        type: "POST",
        success: function(response){
            var output = JSON.parse(response);
            if(output.result == 'success'&& output.user_status =='active'){
                window.location.href = site_url;
            }
        }
    });
}


/********* ACCOUNT SETTING START ***********/


var google_acc = function(client_id,api_key, scope ){
    //alert('constructor Google account');
    self_gplus = this;
    this.credentials['Client_id'] = client_id;
    this.credentials['Api_key'] = api_key;
    this.credentials['Scope'] = scope;


};

$.extend(google_acc.prototype,{
    self_gplus :{},
    credentials:{},
    is_render : false,

    doGoogleLogin :function() {
        gapi.client.setApiKey(this.credentials['Api_key']);
        window.setTimeout(function(){},100);
        gapi.auth.authorize({client_id: self_gplus.credentials['Client_id'], scope: self_gplus.credentials['Scope'], immediate: true}, self_gplus.handleAuthResult);

    },
    handleAuthResult:function(authResult){
        if (authResult && !authResult.error && this.is_render) {
            access_token = authResult.access_token;

            self_gplus.getMyGoogleInfo();        // To get user's profile info

        } 
        this.is_render = true;
        /*else {

            self_gplus.handleAuthClick();
        }*/
    },
    handleAuthClick: function(){
        gapi.client.setApiKey(this.credentials['Api_key']);
        window.setTimeout(function(){},100);
        gapi.auth.authorize({client_id: self_gplus.credentials['Client_id'], scope: self_gplus.credentials['Scope'], immediate: false}, self_gplus.handleAuthResult);
    },

    getMyGoogleInfo:function(){
        gapi.client.load('oauth2', 'v2', function() {
            var request = gapi.client.oauth2.userinfo.get();
            request.execute(function(resp) {
                self_gplus.checkGoogleIdInDb(resp.id);

            });
        });

    },

    checkGoogleIdInDb:function(id){

        $.ajax({
            url:  site_url + "member/check_social_id_exist",
            data: 'social_type=3&social_id='+id,
            type: "POST",
            success: function(data){

                if(data == 1){
                    // id attach with this account

                    $('#gplus_acc_setting').parent().hide();

                    $('.detach-region.gplus-connect').show();
                    $('#google_public_url').attr('href','https://plus.google.com/'+id);

                } else {
                    // id already exist with other account
                    openPopDiv('socialAccount');
                }
            }
        });

    },
    detachGoogleAccount:function(){
        $.ajax({
            url:  site_url + "member/detach_social_network",
            data: 'social_type=3',
            type: "POST",
            success: function(data){
                $('#gplus_acc_setting').parent().show();
                $('.detach-region.gplus-connect').hide();
                closePopDiv('detachSocialAccount');
            }
        });
    }


});
/********* ACCOUNT SETTING END ***********/


var  all_friends = [] ,  oyh_friends = [] ;
var bn_is_render = false;

function bn_handleClientLoad() {
    gapi.client.setApiKey(apiKey);
    window.setTimeout(bn_checkAuth,1);
}

function bn_checkAuth() {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: true}, bn_handleAuthResult);
}

function bn_handleAuthResult(authResult) {

    if (authResult && !authResult.error && bn_is_render) {
        access_token = authResult.access_token;
        bn_makeApiCall();
    } 
    bn_is_render = true;
    /*else {

        bn_handleAuthClick();
    }*/
}

function bn_handleAuthClick(event) {
    gapi.auth.authorize({client_id: client_id, scope: scope, immediate: false}, bn_handleAuthResult);
    return false;
}

function bn_makeApiCall() {
    gapi.client.load('oauth2', 'v2', function() {
        var request = gapi.client.oauth2.userinfo.get();
        request.execute(function(resp) {
            bn_getMyGoogleInfo();        // To get user's profile info
            bn_getUsersGoogleFriends(); // To get user's friends
            shareOnGoogle();
        });
    });
}

 function bn_getMyGoogleInfo(){

    gapi.client.load('plus', 'v1', function() {
        var request_me = gapi.client.plus.people.get({'userId': 'me'});

        request_me.execute(function(rep){
            bn_user_profile(rep);
        });

    });

}

function bn_user_profile(profile){
    $('#google_user_image').attr('src',profile.image.url);
    $('#google_user_name').html(profile.displayName);
    $('#google_user_name').attr('href',profile.url);

}

function bn_getUsersGoogleFriends(){
    gapi.client.load('plus', 'v1', function() {
        var request = gapi.client.plus.people.list({'userId':'me','collection':'visible', orderBy:'alphabetical'});
        request.execute(function(resp) {
               if(resp.totalItems >0 ){
                   var list = resp.items;

                   bn_checkFriendsInDatabase(list);
               } else {

                   $('#google_connect').hide();
                   $('#tabContent-3 .before-fb-login-wrap').hide();
                   $('#google_friends').show();
                   $('#google_invite_others').show();
                   var html = '<li style="width:auto;text-transform:none;">Invite Google+ connections to build business within your existing network, and help your community prosper!</li>';
                   $('#google_friends .msg').hide();
                   $('#google_friends ul.suggestions').html(html);
				   $('#google_friends').find('.google-addhero').hide();
               }

        });
    });
}

function bn_checkFriendsInDatabase(friend_ids){

    var id_list = new Array();
    all_friends = [] ;
    $.each(friend_ids,function(index, value){
        id_list.push(value.id);
        all_friends[value.id] = value ;
    });

    $.ajax({
        url:  site_url + "api/build_network/check_friends_list",
        data: 'social_type=google&friend_ids='+JSON.stringify(id_list),
        type: "POST",
        success: function(data){
            bn_appendRegisteredHeros(data);
        }
    });

}

function bn_appendRegisteredHeros(list){
    var output = list.Data;
    $('#google_connect').hide();
    $('#tabContent-3 .before-fb-login-wrap').hide();
    $('#google_friends').show();
    $('#google_invite_others').show();
    if(output.length >0){

        oyh_friends = [] ;
        $('#google_friends ul.suggestions').html('');

        $.each(output,function(index, value){

            oyh_friends.push(value.google_id);


            var img_url     = all_friends[value.google_id].image.url;
            var name        = all_friends[value.google_id].displayName;
            //var user_link   = 'https://plus.google.com/'+value.google_id;
			var user_link = site_url+'profile-view/'+value.user_id;

            var html = '<li id="google_'+value.google_id+'">  <img src="'+ img_url+'" width="50" height="50" alt="" class="left">'+
                ' <div class="text"> <a class="bold" href="'+user_link+'">'+name+'</a><br>' ;
            if(value.is_hero == null)
                html += ' <input name="" type="checkbox" value="'+value.google_id+'" class="registered_friend">';

            html += '</div>  </li> ';

            $('#google_friends ul.suggestions').append(html);
			if (!$('#google_friends ul.suggestions li').find('.registered_friend').length) {
				$('#google_friends').find('.google-addhero').hide();			
			}
        });


    } else {
        var html = '<li style="width:auto;text-transform:none;">Invite Google+ connections to build business within your existing network, and help your community prosper!</li> ';
        $('#google_friends .msg').hide();
        $('#google_friends ul.suggestions').html(html);
		$('#google_friends').find('.google-addhero').hide();
		$('#google_new_friends').hide();
    }

}


function bn_inviteRegisteredHeros (){

    var selected_friend = [] ;
    $('#google_friends ul li .registered_friend').each(function(){

        if($(this).is(':checked')){

            selected_friend.push($(this).val());
        }
    });

    // console.log(selected_friend);
    if(selected_friend.length >0) {
        $.ajax({
            url:  site_url + "build_network/add_as_heros",
            data: 'social_type=3&social_ids='+JSON.stringify(selected_friend),
            type: "POST",
            success: function(data){

                for (var i=0;i<selected_friend.length;i++)
                {
                    $('#google_'+selected_friend[i]).hide();
                }
                $('input.registered_friend').attr('checked', false);
				if (!$('ul.suggestions li:visible').length) {
					$('#google_friends').find('.gray-box').hide();
					$('#google_friends').find('.invite').css('border','1px solid #D6D6D6');
				 }
            }
        });
    }


}

function generateToken () {
// Math.random should be unique because of its seeding algorithm.
// Convert it to base 36 (numbers + letters), and grab the first 9 characters
// after the decimal.
return Math.random().toString(36).substr(2, 9);
}; 

function shareOnGoogle(id){
    console.log('in');
    if(inviteTime==0){

        Token = generateToken();

        $.post(base_url+'api/build_network/googleInvitation',{Token:Token},function(r){
        console.log(r);
        });
        myUrl = site_url+'signup?Token='+Token;
        inviteTime++;
    }
    var options = {
        contenturl: myUrl,
        contentdeeplinkid: '/',
        clientid: client_id,
        cookiepolicy: 'single_host_origin',
        prefilltext: 'Welcome to CommonSocialNetwork',
        calltoactionlabel: 'JOIN',
        calltoactionurl: site_url,
        calltoactiondeeplinkid: '',
        recipients:id
    };
    
    setTimeout(function(){
        gapi.interactivepost.render('sharePostGoogle', options);
    },1000);
    //$('#sharePost').click();
    //$('#google-invite-btn').removeClass('btn-green').addClass('btn-gray');
}
