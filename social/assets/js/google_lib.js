/* Google library js*/

	

var GoogleLogin = function(client_id, scope, api_key) {
	self_g         = this;
	this.client_id = client_id;
	this.scope     = scope;
	this.api_key   = api_key;
};

$.extend(GoogleLogin.prototype,{
	self_g      : '',
	is_render     : false,
	client_id     : '',
	scope       : '',
	api_key     : '',
	user_info   : {},
	callback    : '',

	SignInButtonRender:function(sign_in_btn_id) {

		gapi.signin.render(sign_in_btn_id, {
			'callback': GoogleLogin.prototype.SignInCallBack,
			'clientid': self_g.client_id,
			'cookiepolicy': 'single_host_origin',
			'requestvisibleactions': 'http://schemas.google.com/AddActivity',
			'scope': self_g.scope
		});


	},
	SignInCallBack:function(authResult){


		if (authResult && !authResult.error && self_g.is_render) {
				access_token = authResult.access_token;
			    self_g.GetUserEmail();
		} 
		self_g.is_render = true;
		
	},

	GetUserEmail:function(){
		gapi.client.load('oauth2', 'v2', function() {
				//var request = gapi.client.oauth2.userinfo.get();
            var request = gapi.client.oauth2.userinfo.v2.me.get();

                //request.B['apiVersion'] ='v2';
				request.execute(function(resp) {
					 //console.log(resp);
			        self_g.user_info.id    = resp.id;
			        self_g.user_info.email = resp.email;
                    self_g.GetUserProfile();
				});
		});
	},

	GetUserProfile:function(){

		gapi.client.load('plus', 'v1', function() {

            var request_me = gapi.client.plus.people.get({'userId': 'me'});
            //console.log(request_me);
            //request_me.B['apiVersion'] ='v1';
			request_me.execute(function(rep){
                self_g.user_info.displayName = rep.displayName;
                self_g.user_info.gender      = rep.gender;
                self_g.user_info.image       = rep.image.url;
                self_g.user_info.first_name  = rep.name.givenName;
                self_g.user_info.last_name   = rep.name.familyName;
                self_g.user_info.public_url  = rep.url;

                self_g.ReturnUserInfo();
			});
		});
	},

	ReturnUserInfo:function(){
		window[self_g.callback](self_g.user_info);
	},

	GoogleSignout:function(){
		gapi.auth.signOut();
	}


});





var GoogleAutoLogin = function(client_id, scope, api_key) {
	self_g         = this;
	this.client_id = client_id;
	this.scope     = scope;
	this.api_key   = api_key;
};

$.extend(GoogleAutoLogin.prototype,{
	self_g      : '',
	is_render   : false,
	client_id   : '',
	scope       : '',
	api_key     : '',
	user_info   : {},
	callback    : '',

	SignInButtonRender:function(sign_in_btn_id) {

		gapi.signin.render(sign_in_btn_id, {
			'callback': GoogleAutoLogin.prototype.SignInCallBack,
			'clientid': self_g.client_id,
			'cookiepolicy': 'single_host_origin',
			'requestvisibleactions': 'http://schemas.google.com/AddActivity',
			'scope': self_g.scope
		});

	},
	SignInCallBack:function(authResult){

		if (authResult && !authResult.error ) {
				access_token = authResult.access_token;
				
				self_g.GetUserEmail();
				
		} 
		self_g.is_render = true;
		
	},

	GetUserEmail:function(){
		gapi.client.load('oauth2', 'v2', function() {
			/*var request = gapi.client.oauth2.userinfo.get();
            request.B['apiVersion'] ='v2';*/
            var request = gapi.client.oauth2.userinfo.v2.me.get();
            request.execute(function(resp) {
					 //console.log(resp);
			self_g.user_info.id    = resp.id;
			self_g.user_info.email = resp.email;

					 self_g.GetUserProfile();
				});
		});
	},

	GetUserProfile:function(){

		gapi.client.load('plus', 'v1', function() {
			var request_me = gapi.client.plus.people.get({'userId': 'me'});
            request_me.B['apiVersion'] ='v1';
			request_me.execute(function(rep){
				    //console.log(rep);
                    self_g.user_info.displayName = rep.displayName;
                    self_g.user_info.gender      = rep.gender;
                    self_g.user_info.image       = rep.image.url;
                    self_g.user_info.first_name  = rep.name.givenName;
                    self_g.user_info.last_name   = rep.name.familyName;
                    self_g.user_info.public_url  = rep.url;

                    self_g.ReturnUserInfo();
			});
		});
	},

	ReturnUserInfo:function(){
		window[self_g.callback](self_g.user_info);
	}


});



var GoogleFriend = function(client_id, scope, api_key) {
	self_g         = this;
	this.client_id = client_id;
	this.scope     = scope;
	this.api_key   = api_key;
};

$.extend(GoogleFriend.prototype,{
	self_g      : '',
	is_render   : false,
	client_id   : '',
	scope       : '',
	api_key     : '',
	user_info   : {},
	friend_list : {},
	callback    : '',

	SignInButtonRender:function(sign_in_btn_id) {

		gapi.signin.render(sign_in_btn_id, {
			'callback': GoogleFriend.prototype.SignInCallBack,
			'clientid': self_g.client_id,
			'cookiepolicy': 'single_host_origin',
			'requestvisibleactions': 'http://schemas.google.com/AddActivity',
			'scope': self_g.scope
		});

	},
	SignInCallBack:function(authResult){

		if (authResult && !authResult.error && self_g.is_render) {
				access_token = authResult.access_token;
				
				self_g.GetUserEmail();
				
		} 
		self_g.is_render = true;
		
	},

	GetUserEmail:function(){
		gapi.client.load('oauth2', 'v2', function() {
            /*var request = gapi.client.oauth2.userinfo.get();
            request.B['apiVersion'] ='v2';*/
            var request = gapi.client.oauth2.userinfo.v2.me.get();
            request.execute(function(resp) {
					 //console.log(resp);
                self_g.user_info.id    = resp.id;
                self_g.user_info.email = resp.email;
			    self_g.GetUserProfile();
			});
		});
	},

	GetUserProfile:function(){

		gapi.client.load('plus', 'v1', function() {
			var request_me = gapi.client.plus.people.get({'userId': 'me'});
            request_me.B['apiVersion'] ='v1';
			request_me.execute(function(rep){
				//console.log(rep);
				self_g.user_info.displayName = rep.displayName;
				self_g.user_info.gender      = rep.gender;
				self_g.user_info.image       = rep.image.url;
				self_g.user_info.first_name  = rep.name.givenName;
				self_g.user_info.last_name   = rep.name.familyName;
				self_g.user_info.public_url  = rep.url;
				
				 self_g.ReturnFriendList(self_g.user_info);
				
			});
		});
	},

	ReturnFriendList:function(list){
		window[self_g.callback](list);
	},

	GetFriendList:function(){
		gapi.client.load('plus', 'v1', function() {
	        var request = gapi.client.plus.people.list({'userId':'me','collection':'visible', orderBy:'alphabetical'});

            request.B['apiVersion'] ='v1';
            request.execute(function(resp) {
	               console.log(resp);
	               if(resp.totalItems >0 ){
	                   var list = resp.items;
	                   self_g.ReturnFriendList(list);
	               } else {
	               		console.log('No friend found');
	               }

	        });
    	});
	},

	/*AppendFriendList:function(list){
		$.ajax({
			type: "POST", 
			url: base_url + "google/put_friend_list",
			data: {friend_list :JSON.stringify(list)},
			success: function (data){ 
				$('#google_friend_list').html(data);
			}
		});
	},*/

	shareOnGoogle :function(id, message){
	    var options = {
			contenturl             : site_url+'/google/google_friend_list',
			contentdeeplinkid      : '/',
			clientid               : self_g.client_id,
			cookiepolicy           : 'single_host_origin',
			prefilltext            : message,
			calltoactionlabel      : 'JOIN',
			calltoactionurl        : site_url+'/google/google_friend_list',
			calltoactiondeeplinkid : '/google/google_friend_list'
	    };

	    gapi.interactivepost.render(id, options);

	}


});