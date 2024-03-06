// JavaScript Document
setInterval(function(){
	fb_obj=new FacebookLogin('CallBack'); 
	$(document).on("click","#facebookbtn",function(){
		$("#IDSourceID").val("2");
		$("#IDSocialType").val("Facebook API");
	});

	$(document).on("click","#facebookloginbtn",function(){
		$("#IDSourceIDLogin").val("2");
		$("#IDSocialTypeLogin").val("Facebook API");
	});

	$(document).on("click","#gmailsignupbtn",function(){
		$("#IDSourceID").val("4");
		$("#IDSocialType").val("Google API");
		$("#IDSourceIDLogin").val("4");
	});

	$(document).on("click","#linkedinbtn",function(){
		$("#IDSourceID").val("7");
		$("#IDSocialType").val("LinkedIN API");
	});

	$(document).on("click","#linkedinloginbtn",function(){
		$("#IDSourceIDLogin").val("7");
		$("#IDSocialTypeLogin").val("LinkedIN API");
	});

	$(document).on("click","#twitterloginbtn",function(){
		$("#IDSourceIDLogin").val("3");
		$("#IDSocialTypeLogin").val("Twitter API");
		twitterRegistration('login');
	});

	$(document).on("click","#twitterregistrationbtn",function(){
		$("#IDSourceID").val("3");
		$("#IDSocialType").val("Twitter API");
		twitterRegistration('registration');
	});

	function twitterRegistration(action) {
		window.open(base_url+'api/twitter/twittersignup/'+action+'/',"popupwindow","width=500,height=500");
	}

	window.receiveDataFromPopup = function(data) {
		var appElement = document.querySelector('[ng-controller="aboutCtrl"]'); 
		var $scope = angular.element(appElement).scope();
		var requestData = {
						    SocialType:3,
						    SocialID:data.user.id,
						    profileUrl:'https://twitter.com/'+data.user.screen_name,
						    ProfilePicture:data.user.picture
						};
		$scope.UserSocialAccountData(requestData);
		//profileUrl:'https://twitter.com/intent/user?user_id='+data.user.id,
	};				
	
	$(document).on("click","#idsignup",function(){
		pdata=$("#registrationform").serialize();
		$.ajax({
			type: "POST",
			url: base_url+"api_signup/signup.json",
			data: pdata,
			success:function(data){
				if(data.SignUp.ResponseCode!=200)
				{
					alert(data.SignUp.Message);
				}
				else
				{
					alert('Registered successfully, login access key is : .'+data.SignUp.Data.LoginSessionKey);
				}
			}
		});
	});
},300);
	

function CallBack(user_data) {		
	var appElement = document.querySelector('[ng-controller="aboutCtrl"]'); 
	var $scope = angular.element(appElement).scope();
	var requestData = {
					    SocialType:2,
					    SocialID:user_data.id,
					    profileUrl:'https://facebook.com/'+user_data.id,
					    ProfilePicture:user_data.picture.normal
					};
	$scope.UserSocialAccountData(requestData);
}

$(document).ready(function(){
	console.log('google init');
	var po = document.createElement('script');
	po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/client:plusone.js?onload=google_init';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(po,s);
});

function google_init(){
	g_obj1=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj1.SignInButtonRender('gmailsignupbtn');
	g_obj1.callback='gmailCallback';

	g_obj2=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj2.SignInButtonRender('gplusimage');
	g_obj2.callback='gmailCallback';

	g_obj_login=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj_login.SignInButtonRender('gmailloginbtn');
	g_obj_login.callback='gmailCallback';
}

function gmailCallback(user_data) {
	var img_size = 500;
	user_data.image = user_data.image.replace(/(sz=)[^\&]+/, '$1' + img_size);
	var picture = user_data.image;

	var appElement = document.querySelector('[ng-controller="aboutCtrl"]'); 
	var $scope = angular.element(appElement).scope();
	var requestData = {
					    SocialType:4,
					    SocialID:user_data.id,
					    profileUrl:user_data.public_url,
					    ProfilePicture:picture
					};
	$scope.UserSocialAccountData(requestData);
}

setInterval(function(){
	in_obj = new LinkedinSignin();
    in_obj.callback = 'linkedin_callback';
},300);

function linkedin_callback(user_data) {	
	var picture = '';
	if(user_data.values[0].pictureUrls._total>0){
        picture = user_data.values[0].pictureUrls.values[0];
    }

    var appElement = document.querySelector('[ng-controller="aboutCtrl"]'); 
	var $scope = angular.element(appElement).scope();
	var requestData = {
					    SocialType:7,
					    SocialID:user_data.values[0].id,
					    profileUrl:user_data.values[0].publicProfileUrl,
					    ProfilePicture:picture
					};
	$scope.UserSocialAccountData(requestData);
} 

function submitLoginForm(type,id,profileUrl,profilepicture)
{
	var requestData = {SocialType:type,SocialID:id,profileUrl:profileUrl,ProfilePicture:profilepicture};
        //console.log(requestData);return;
	$.ajax({
		type: "POST",
		url: base_url+"api/users/attach_social_account",
		data: JSON.stringify(requestData),
		dataType: "json",
		contentType: 'application/json; charset=UTF-8',
		success:function(data){
            //window.location.reload();
            if(data.ResponseCode==200){
				angular.element(document.getElementById('MyAccountCtrl')).scope().changeSocialValue(data.Data.SocialType, data.Data.SocialID,data.Data.profileUrl, data.Data.ProfilePicture);
			} else if(data.ResponseCode == 201){

			} else {
				var socialnetwork = '';
				if(type == '7'){
					socialnetwork = 'linkedin';
				} else if(type == '3'){
					socialnetwork = 'twitter';
				} else if(type == '2'){
					socialnetwork = 'facebook';
				} else if(type == '4'){
					socialnetwork = 'google';
				}
				showAlertBox('Account in Use','This '+socialnetwork+' account is already associated with another '+site_name+' member.',function(e){
					if(e){
						$('AlertModal').remove();
					}
				});
			}
		}
	});
}

function getQueryString(type,id,email,fname,lname,picture) {
	string='type='+type+'&id='+id+'&email='+email+'&fname='+fname+'&lname='+lname+'&picture='+picture;
	return encodeURI(string);
}