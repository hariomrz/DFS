<script type="text/javascript">
	var google_plus1_loaded = false ;
	(function() {
            var po = document.createElement('script');
            po.type = 'text/javascript';
            po.async = true;
            po.src = 'https://apis.google.com/js/client:plusone.js?onload=render';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	})();
	function render() {
            gapi.signin.render('gplus_build_nw', {
                    'callback': 'bn_handleAuthResult',
                    'clientid': google_client_id,
                    'cookiepolicy': 'single_host_origin',
                    'requestvisibleactions': 'http://schemas.google.com/AddActivity',
                    'scope': google_scope
            });
	}

	//var type = '< ?php echo ($this->session->userdata('login_type'))?$this->session->userdata('login_type'):'native'; ?>';
	$(document).ready(function(){
            setTimeout(function(){
                autoTriggerSocialLogin('facebook');
                //autoTriggerSocialLogin('twitter');
                /*autoTriggerSocialLogin('linkedin');*/
                autoTriggerSocialLogin('google');
            },1000);
	});

	function autoTriggerSocialLogin(type){
		switch(type){
                    case 'facebook':
			var fb_time = setInterval(function(){callFbAuth();},500);
			function callFbAuth(){
                            if(is_fb_loaded == true){
                                fb_time = window.clearInterval(fb_time);
                                fb_build_network = new Build_network();
                            }
			}
			break;
                    case 'twitter':
                        if(typeof  twt_build_network == 'undefined'){
                            twt_build_network = new Network_twitter();
                            <?php if(isset($_SESSION['twitter'])){ ?>
                                    twt_build_network.is_twt_login = true;
                                    <?php }?>
                        }
			break;
                    case 'linkedin':
                        var linkin_time = setInterval(function(){callLinkedInAuth();},500);
                        function callLinkedInAuth() {
                            if(is_linkedin_loaded == true) {
                                linkin_time = window.clearInterval(linkin_time);
                                $('#linkdinconect').trigger('click');
                                if(typeof (linkedin_network) == 'undefined'){
                                        linkedin_network = new Network_linkedin();
                                }
                                linkedin_network.dolinkedinLogin();
                            }
                        }
			break;
                    case 'google':
                        var google_time = setInterval(function(){callGoogleAuth();},1000);
                        function callGoogleAuth() {
                            if(google_plus1_loaded == false) {
                                google_time = window.clearInterval(google_time);
                                $('#gplusconect').trigger('click');
                                // google_network.doGoogleLogin();
                                bn_handleClientLoad();
                            }
                        }
			break;
			default :
				initiliseSocialNetwork(1);
			break;
		}
	}

	/*$('document').ready(function(){
		setTimeout(function(){
			  fb_build_network = new Build_network();
		},500);
	});*/
	function initiliseSocialNetwork(type){
		switch (type){
			case 1:
			if(typeof (fb_build_network) == 'undefined'){
				fb_build_network = new Build_network();
			}
			break;
			case 2:
				if(typeof  twt_build_network == 'undefined'){
					twt_build_network = new Network_twitter();
					<?php if(isset($_SESSION['twitter'])){ ?>
						twt_build_network.is_twt_login = true;
						<?php }?>
				}
			break;
			case 3:
				console.log('Called shareOnGoogle');
				shareOnGoogle();
			break;
			case 4:
				if(typeof (linkedin_network) == 'undefined'){
					linkedin_network = new Network_linkedin();
				}
			break;
		}
	}
</script>
