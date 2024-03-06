/* Javascript for linkedIn */
var is_linkedin_loaded = false;
function checkLinkedInLoaded() {
	console.log('LinkedIn JS Loaded');
	is_linkedin_loaded = true;
}
var LinkedinSignin = function(){
	self_in = this;
}
$.extend(LinkedinSignin.prototype ,{
	self_in: {},
	callback:'',
	InLogin:function(){
		if(is_linkedin_loaded){
			IN.User.authorize(self_in.InUserInfo);

		} else {
			console.log('Linked js not loaded yet, Please try again');
		}
	},
	InUserInfo:function(){
		IN.API.Profile("me")
        .fields("email-address,id,first-name,last-name,picture-urls::(original),headline,date-of-birth,formatted-name,public-profile-url,skills,num-connections")
        .result(window[self_in.callback]);
	}
});