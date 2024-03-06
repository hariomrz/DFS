// JavaScript Document

setInterval(function(){
	fb_obj=new FacebookLogin('CallBack'); 
	$(document).on("click","#facebookbtn",function(){
		$("#IDSourceID").val("2");
		$("#IDSocialType").val("Facebook API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#facebookloginbtn",function(){
		$("#IDSourceIDLogin").val("2");
		$("#IDSocialTypeLogin").val("Facebook API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#gmailsignupbtn",function(){
		$("#IDSourceID").val("4");
		$("#IDSocialType").val("Google API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#gmailsignupbtn2",function(){
		$("#IDSourceIDLogin").val("4");
		$("#IDSocialTypeLogin").val("Google API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#linkedinbtn",function(){
		$("#IDSourceID").val("7");
		$("#IDSocialType").val("LinkedIN API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#linkedinloginbtn",function(){
		$("#IDSourceIDLogin").val("7");
		$("#IDSocialTypeLogin").val("LinkedIN API");
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#twitterloginbtn",function(){
		$("#IDSourceIDLogin").val("3");
		$("#IDSocialTypeLogin").val("Twitter API");
		twitterRegistration('login');
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	$(document).on("click","#twitterregistrationbtn",function(){
		$("#IDSourceID").val("3");
		$("#IDSocialType").val("Twitter API");
		twitterRegistration('registration');
		$('.loader-fad').show();
		setTimeout(function(){
			$('.loader-fad').hide();	
		},10000);
	});

	function twitterRegistration(action)
	{
		window.open(base_url+'api/twitter/twittersignup/'+action+'/',"popupwindow","width=500,height=500");
	}

	window.receiveDataFromPopup = function(data) 
	{
		if(data.user.taction=='login')
		{
			$('#LoginUserSocialID').val(data.user.id);
			$('#SignupUserSocialID').val(data.user.id);
			$('#first_name').val(data.user.firstname);
			$('#last_name').val(data.user.lastname);
			var profileUrl = 'https://twitter.com/intent/user?user_id='+data.user.id;
			qstring=getQueryString('3',data.user.id,'',data.user.firstname,data.user.lastname,data.user.picture,profileUrl);
			var picture = data.user.picture;
			submitLoginForm(qstring,3,data.user.id,'Twitter API','',data.user.firstname,data.user.lastname,picture,profileUrl);
		}
		else
		{
			$('#LoginUserSocialID').val(data.user.id);
			$('#SignupUserSocialID').val(data.user.id);
			$('#first_name').val(data.user.firstname);
			$('#last_name').val(data.user.lastname);
			var profileUrl = 'https://twitter.com/intent/user?user_id='+data.user.id;
			qstring=getQueryString('3',data.user.id,'',data.user.firstname,data.user.lastname,data.user.picture,profileUrl);
			var picture = data.user.picture;
			submitLoginForm(qstring,3,data.user.id,'Twitter API','',data.user.firstname,data.user.lastname,picture,profileUrl);
			//window.location=base_url+'signup?'+qstring;
		}
		console.log(data);
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
	

function CallBack(user_data) 
{
	console.log(user_data);
	if($("#IDSourceIDLogin").val()!='') 
	{
		$('#LoginUserSocialID').val(user_data.id);
		$('#first_name').val(user_data.first_name);
		$('#last_name').val(user_data.last_name);
		var profileUrl = 'https://facebook.com/'+user_data.id;
		qstring=getQueryString('2',user_data.id,user_data.email,user_data.first_name,user_data.last_name,'',profileUrl);
		submitLoginForm(qstring,2,user_data.id,'Facebook API',user_data.email,user_data.first_name,user_data.last_name,user_data.picture.normal,profileUrl);
	}
	else
	{
		$('#SignupUserSocialID').val(user_data.id);
		$('#first_name').val(user_data.first_name);
		$('#last_name').val(user_data.last_name);
		$('#email').val(user_data.email);
		$('#birthday').val(user_data.birthday);
		$('#fb_small').attr('src',user_data.picture.small);
		$('#fb_normal').attr('src',user_data.picture.normal);
		$('#fb_large').attr('src',user_data.picture.large);
		$('#fb_square').attr('src',user_data.picture.square);
		var profileUrl = 'https://facebook.com/'+user_data.id;
		qstring=getQueryString('2',user_data.id,user_data.email,user_data.first_name,user_data.last_name,'',profileUrl);
		window.location=base_url+'signup?'+qstring;
	}
}

$(document).ready(function(){
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

	g_obj_login=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj_login.SignInButtonRender('gmailloginbtn');
	g_obj_login.callback='gmailCallback';

	g_obj2=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj2.SignInButtonRender('gmailsignupbtn2');
	g_obj2.callback='gmailCallback';

	g_obj_login2=new GoogleLogin(google_client_id,google_scope,google_api_key);
	g_obj_login2.SignInButtonRender('gmailloginbtn2');
	g_obj_login2.callback='gmailCallback';
}

function gmailCallback(user_data)
{
	$('#SignupUserSocialID').val(user_data.id);
	$('#first_name').val(user_data.first_name);
	$('#last_name').val(user_data.last_name);
	$('#email').val(user_data.email);
	$('#public_url').val(user_data.public_url);      
	var img_size = 500;
	user_data.image = user_data.image.replace(/(sz=)[^\&]+/, '$1' + img_size);
	$('#fb_small').attr('src',user_data.image);
	var profileUrl = user_data.public_url;
	qstring=getQueryString('4',user_data.id,user_data.email,user_data.first_name,user_data.last_name,'',profileUrl);
	console.log(user_data);
	//window.location=base_url+'signup?'+qstring;
	var picture = user_data.image;
	submitLoginForm(qstring,4,user_data.id,'Google API',user_data.email,user_data.first_name,user_data.last_name,picture,profileUrl);
}

setInterval(function(){
	in_obj = new LinkedinSignin();
    in_obj.callback = 'linkedin_callback';
},300);

function linkedin_callback(user_data)
{
	$('#SignupUserSocialID').val(user_data.values[0].id);
	$('#first_name').val(user_data.values[0].firstName);
	$('#last_name').val(user_data.values[0].lastName);
	$('#email').val(user_data.values[0].emailAddress);
	var profileUrl = user_data.values[0].publicProfileUrl;
	qstring=getQueryString('7',user_data.values[0].id,user_data.values[0].emailAddress,user_data.values[0].firstName,user_data.values[0].lastName,'',profileUrl);
	console.log(user_data);
	//window.location=base_url+'signup?'+qstring;
	var picture = '';
	if(user_data.values[0].pictureUrls._total>0){
        picture = user_data.values[0].pictureUrls.values[0];
    }
	submitLoginForm(qstring,7,user_data.values[0].id,'LinkedIN API',user_data.values[0].emailAddress,user_data.values[0].firstName,user_data.values[0].lastName,picture,profileUrl);
} 

function submitLoginForm(queryString,type,id,socialtype,email,firstname,lastname,picture,profileUrl)
{
	$('.loader-fad').show();
	var SourceID=$("#IDSourceIDLogin").val();
	var SocialType=$("#IDSocialTypeLogin").val();
	var UserSocialID=$("#LoginUserSocialID").val();
	var requestData = {SocialType:socialtype,UserSocialID:id,Email:email,FirstName:firstname,LastName:lastname,Picture:picture,DeviceType:"Native",profileUrl:profileUrl};
        //console.log(requestData);return;
	$.ajax({
		type: "POST",
		url: base_url+"api/signup",
		data: JSON.stringify(requestData),
		dataType: "json",
		contentType: 'application/json; charset=UTF-8',
		headers: { 'Accept-Language': accept_language },
		success:function(data){
            if(data.ResponseCode == 504)
			{
                window.location=base_url+'signup?'+queryString;
                $('.loader-fad').hide();
            }else if(data.ResponseCode!=200 && data.ResponseCode!=512)
			{
				if(data.ResponseCode==503) {
					$("#errorUsername").text(data.Message);
				} else {
				alert(data.Message);
				}
				$('.loader-fad').hide();
			}
			else
			{
				var social_login_data = {DeviceType:"Native",SocialType:socialtype,UserSocialID:id};
				$.ajax({
					type: "POST",
					url: base_url+"signup/LogIn",
					data: JSON.stringify(social_login_data),
					dataType: "json",
					contentType: 'application/json; charset=UTF-8',
					headers: { 'Accept-Language': accept_language },
					success:function(data){
						window.location=base_url;		
					}
				});
			}
			$('.loader-fad').hide();
		}
	});
}

function getQueryString(type,id,email,fname,lname,picture,profileUrl)
{
	string='type='+type+'&id='+id+'&email='+email+'&fname='+fname+'&lname='+lname+'&picture='+picture+'&profileUrl='+profileUrl;
	return encodeURI(string);
}