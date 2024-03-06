/* Facebook classes */
var appId        = FacebookAppId;
var is_fb_loaded = false;

window.fbAsyncInit = function() {
				    FB.init({
					      appId      : appId,			// App ID
					      status     : true, 			// check login status
					      cookie     : true, 			// enable cookies to allow the server to access the session
					      xfbml      : true,  			// parse XFBML
					      frictionlessRequests : true  // Only for friend request dialog box
				    });
			
				    is_fb_loaded = true;
		    
		  };

  // Load the SDK Asynchronously
  (function(d){
     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = "//connect.facebook.net/en_US/all.js";
     ref.parentNode.insertBefore(js, ref);
   }(document));

/*  ***********************ABOVE CODE USED ONCE IN JS FILE********* *********************/

var FacebookLogin = function(callback){	

	self_fb = this;
	self_fb.callback = callback;
	
};


$.extend(FacebookLogin.prototype,{
	self_fb 	: {},
	scope		: 'email,user_friends',
	field_req	: 'id,name,first_name,last_name,email',
	callback	: '',


	FbLoginStatusCheck:function(){
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
					self_fb.FbUserInformation();
			} else if (response.status === 'not_authorized') {
				self_fb.FbLogin();
			} else {
			   // the user isn't logged in to Facebook.
			   console.log('THE USER ISN\'T LOGGED IN TO FACEBOOK.');
			   self_fb.FbLogin();
			}
		});
	},

	FbLogin:function(){
		FB.login(function(response) {
				//console.log(response);

				if (response.authResponse) {
     				self_fb.FbUserInformation();

		   		} else {
			    	$('.loader-signup').hide();

		   		}
	 	}, {scope: self_fb.scope});

	},

	FbUserInformation:function(){
		 FB.api('/me?fields='+self_fb.field_req, function(response) {
			//console.log(response);
			var output = response;
			delete output.picture ;
			var small  = 'https://graph.facebook.com/'+ response.id + '/picture?type=small';
			var normal = 'https://graph.facebook.com/'+ response.id + '/picture?type=normal';
			var large  = 'https://graph.facebook.com/'+ response.id + '/picture?type=large';
			var square = 'https://graph.facebook.com/'+ response.id + '/picture?type=square';
			
			output.picture        = {};
			output.picture.small  = small;
			output.picture.normal = normal;
			output.picture.large  = large;
			output.picture.square = square;

			window[self_fb.callback](output);
			

		});
	}

});


var FacebookFriend = function(){
	self_fb = this;
	
};

$.extend(FacebookFriend.prototype,{
	self_fb 	: {},
	scope		: 'email,friends_birthday,user_friends',
	field_req	: 'id,name,birthday,first_name,last_name,username',
	friend_list_callback : '',

	FbLoginStatusCheck:function(){
		FB.getLoginStatus(function(response) {

			if (response.status === 'connected') {
					self_fb.FbFriendList();

			} else if (response.status === 'not_authorized') {
				self_fb.FbLogin();
			} else {
			   // the user isn't logged in to Facebook.
			   console.log('THE USER ISN\'T LOGGED IN TO FACEBOOK.');
			   self_fb.FbLogin();
			}
		});

	},

	FbLogin:function(){
		FB.login(function(response) {
				//console.log(response);

				if (response.authResponse) {
     				self_fb.FbFriendList();

		   		} else {
			    	$('.loader-signup').hide();

		   		}
	 	}, {scope: self_fb.scope});

	},

	

	FbFriendList:function(){
		
		FB.api('/me/friends?fields='+self_fb.field_req, function(response) { 
						
			window[self_fb.friend_list_callback](response);
			
		});
	},

	AppendFriendList:function(list){
		$.ajax({
			type: "POST", 
			url: base_url + "facebook/put_friend_list",
			data: {friend_list :JSON.stringify(list.data)},
			success: function (data){ 
				$('#facebook_friend_list').html(data);
			}
		});
	},

	SelectedFriendInvite:function(requestCallback, selectedFriends,data){
		FB.ui({	method: 'apprequests',
  				message: data.message,
  				to: selectedFriends,
  				title: data.title
			  },window[requestCallback] );
	},

	AllFriendInvite:function(requestCallback){
		FB.ui({	method: 'apprequests',
  				message: 'My Great Request'
			},window[requestCallback] );
	}



});


var FacebookOpenGraphLike = function(callback){	

	self_fb = this;
	self_fb.callback = callback;
	
};


$.extend(FacebookOpenGraphLike.prototype,{
	self_fb 	: {},
	scope		: 'email,user_birthday,publish_actions,user_friends',
	field_req	: 'id,name,birthday,first_name,last_name,email,username',
	callback	: '',


	FbLoginStatusCheck:function(){
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
					self_fb.FbUserInformation();
			} else if (response.status === 'not_authorized') {
				self_fb.FbLogin();
			} else {
			   // the user isn't logged in to Facebook.
			   console.log('THE USER ISN\'T LOGGED IN TO FACEBOOK.');
			   self_fb.FbLogin();
			}
		});

	},

	FbLogin:function(){
		FB.login(function(response) {
				//console.log(response);

				if (response.authResponse) {
     				self_fb.FbUserInformation();

		   		} else {
			    	$('.loader-signup').hide();

		   		}
	 	}, {scope: self_fb.scope});

	},

	FbUserInformation:function(){
		 FB.api('/me?fields='+self_fb.field_req, function(response) {
			//console.log(response);
			var output = response;
			delete output.picture ;
			var small  = 'https://graph.facebook.com/'+ response.id + '/picture?type=small';
			var normal = 'https://graph.facebook.com/'+ response.id + '/picture?type=normal';
			var large  = 'https://graph.facebook.com/'+ response.id + '/picture?type=large';
			var square = 'https://graph.facebook.com/'+ response.id + '/picture?type=square';
			
			output.picture        = {};
			output.picture.small  = small;
			output.picture.normal = normal;
			output.picture.large  = large;
			output.picture.square = square;

			window[self_fb.callback](output);
			

		});
	}

});


var FacebookPostShare = function(callback){	

	self_fb = this;
	self_fb.callback = callback;
	
};


$.extend(FacebookPostShare.prototype,{
	self_fb 	: {},
	scope		: 'email,user_birthday,publish_stream,publish_actions,user_friends',
	field_req	: 'id,name,birthday,first_name,last_name,email,username',
	callback	: '',


	FbLoginStatusCheck:function(){
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
					self_fb.FbUserInformation();
			} else if (response.status === 'not_authorized') {
				self_fb.FbLogin();
			} else {
			   // the user isn't logged in to Facebook.
			   $('.loader-signup').hide();
			   self_fb.FbLogin();
			}
				});

	},

	FbLogin:function(){
		FB.login(function(response) {
				//console.log(response);

				if (response.authResponse) {
     				self_fb.FbUserInformation();

		   		} else {
			    	$('.loader-signup').hide();

		   		}
	 	}, {scope: self_fb.scope});

	},

	FbUserInformation:function(){
		 FB.api('/me?fields='+self_fb.field_req, function(response) {
			//console.log(response);
			var output = response;
			delete output.picture ;
			var small  = 'https://graph.facebook.com/'+ response.id + '/picture?type=small';
			var normal = 'https://graph.facebook.com/'+ response.id + '/picture?type=normal';
			var large  = 'https://graph.facebook.com/'+ response.id + '/picture?type=large';
			var square = 'https://graph.facebook.com/'+ response.id + '/picture?type=square';
			
			output.picture        = {};
			output.picture.small  = small;
			output.picture.normal = normal;
			output.picture.large  = large;
			output.picture.square = square;

			window[self_fb.callback](output);
			

		});
	},

	PostMessage:function(){
		FB.api('/me/feed', 'post', { message: msg }, function(response) {
								if (!response || response.error) {
									 alert('Error occured');
								} else {
									 $('#postmsgresponse').html(response.id);
									 
									// alert('Posted on Facebook Post ID: ' + response.id);
								}
						   });
	},

	postToFeed: function(obj) {
		
		var obj = {
					method      : 'feed',
					link        : obj.link,
					picture     : obj.image,
					//picture   :'http://suvudu.com/files/mt-files/Edward%20Cullen.jpg',
					name        : obj.name,
					caption     : obj.caption,
					description : obj.desc
					};
		//console.log(obj);
		function callback(response) {
				console.log(response['post_id']);
		}

		FB.ui(obj, callback);
	}

});
