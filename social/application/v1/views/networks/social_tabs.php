<section class="build-tab">

	<div class="tab-head">
		<ul id="dynamicTab">
			<li data-rel="tab1" class="active"><a class="icon social-media" id="fbconect" onclick="initiliseSocialNetwork(1);">facebook</a></li>
			<li data-rel="tab2" ><a class="icon social-media " id="twconect" onclick="initiliseSocialNetwork(2)"></a>twitter</li>
			<li data-rel="tab3"><a class="icon social-media" id="gplusconect" onclick="initiliseSocialNetwork(3)">google </a></li>
			<li data-rel="tab4"><a class="icon social-media" id="linkdinconect" onclick="initiliseSocialNetwork(4)">linkedIn</a></li>

		</ul>
	</div>
	<section id="tab1" >
		<div id="fb-root"></div>
		<div id="facebook_connect">
			<section class="self-info box-sizing center">
				<input name="" type="button" class="btn btn-blue no-float" value="Connect with Facebook" onclick="fb_build_network.checkFbLoginStatus();">
				<div class="clear"></div>
			</section>
		</div>
		<div id="facebook_friends" style="display: none;">

			<section class="self-info">
				<a class="img50"><img id="my_fb_profile_pic" src="" width="50" height="50"  class="left"></a>
				<section class="text bold">
					<a id="my_fb_name"></a><br>
					<i class="icon icon-fb"></i>
				</section>
				<div class="clear"></div>
			</section>
			<section class="gray-box">
				<span class="p-left14 msg">Invite Facebook friends</span>
				<ul class="suggestions">

				</ul>
				<div class="w100p"><input name="" type="button" class="btn btn-green font16 m-top15 m-left14 facebook-addhero" value="Add Friends" onclick="fb_build_network.inviteRegisteredHeros();">
				</div>
			</section>
			<section class="invite"><a id="facebook-invite-btn" class="font14 bold btn btn-green" onclick="fb_build_network.FbMultiSelectFriend();">Invite Facebook friends to join</a><div class="clear"></div></section>

		</div>


	</section>

	<section id="tab2" style="display:none">
		<div id="twitter_connect">
			<section class="self-info box-sizing center">
				<span class="btn btn-blue no-float" onclick="twt_build_network.get_twitter_friend();">Connect with Twitter</span>
				<div class="clear"></div>
			</section>
		</div>
		<div id="twitter_friends" style="display: none;">
			<section class="self-info">
				<a class="img50" >
					<img src="<?=base_url();?>img/img50-3.jpg" id="twt_profile_pic" width="50" height="50"  class="left">
				</a>
				<section class="text bold">
					<a id="twt_user_name"></a><br>
					<i class="icon icon-twitter"></i>
				</section>
				<div class="clear"></div>
			</section>
			<section class="gray-box">
				<span class="p-left14 msg">Invite Twitter followers </span>
				<ul class="suggestions">

				</ul>
				<div class="w100p">
					<input name="" type="button" class="btn btn-green font16 m-top15 m-left14 twitter-addhero" value="Add Connections" onclick="twt_build_network.invite_registered_heros();">
				</div>
			</section>

			<div id="twitter_new_friends" style="display: none;">
				<section class="gray-box">
					<ul class="suggestions">

					</ul>
					<div class="w100p">
						<input id="invite_non_oyh_friend" name="" type="button" class="btn btn-green font16 m-top15 m-left14" value="Invite" onclick="twt_build_network.invite_non_OYH_friends();">

					</div>
				</section>
			</div>

			<section class="invite" id="twitter_invite_other" style="display: none;">
				<a id="twitter-invite-btn" class="font14 bold btn btn-green" onclick="twt_build_network.non_OYH_connection();">Invite Twitter Followers</a>
				<div class="clear"></div>
			</section>
		</div>

	</section>

	<section id="tab3" style="display:none">
		<div id="google_connect">
			<section class="self-info box-sizing center">
				<!-- <input name="" type="button" class="btn btn-blue no-float" value="Connect with Google+" onclick="bn_handleClientLoad();"> -->
				<div id="gplus_build_nw" class="customGPlusSignIn btn btn-blue no-float" style="display:inline-block;">
					<span class="icon"></span>    
					<span class="buttonText">Connect with Google+</span>            
				</div> 
				<div class="clear"></div>
			</section>
		</div>
		<div id="google_friends" style="display: none">
			<section class="self-info box-sizing">
				<a class="left"><img id="google_user_image" src="" width="50" height="50"  class="left"></a>
				<section class="text bold" style="text-transform: capitalize;">
					<a id="google_user_name"></a><br>
					<i class="icon icon-gplus"></i>
				</section>
				<div class="clear"></div>
			</section>
			<section class="gray-box">
				<span class="p-left14 msg">Invite Google+ connections</span>
				<ul class="suggestions">

				</ul>
				<div class="w100p">
					<input name="" type="button" class="btn btn-green font16 m-top15 m-left14 google-addhero" value="Add Connections" onclick="bn_inviteRegisteredHeros();">
				</div>
			</section>
			<section class="invite">
				<a id="google-invite-btn" onclick="shareOnGoogle();$(this).removeClass('btn-green').addClass('btn-gray');" class="btn btn-green">
					<div id="google_invite_others" class="font14 bold">
						Invite Google+ connections
					</div>
				</a>
				<div class="clear"></div>
			</section>

		</div>
		<div id="google_new_friends" style="display: none;">
			<section class="gray-box">
				<ul class="suggestions"></ul>
				<div class="w100p"><input name="" type="button" class="btn btn-green font16 m-top15 m-left14" value="Invite" onclick="">
				</div>
			</section>
		</div>
		<section class="invite" style="display: none;">
			<a onclick="google_network.shareOnGoogle();"> <div id="google_invite_others" class="font14 bold">Invite Google+ connections</div></a>
		</section>
	</section>

	<section id="tab4" style="display:none">
		<div id="linkedin_connect">
			<section class="self-info box-sizing center">
				<span class="btn btn-blue no-float" onclick="linkedin_network.dolinkedinLogin();">Connect with LinkedIn</span>
				<div class="clear"></div>
			</section>
		</div>
		<div id="linkedin_friends" style="display: none">
			<section class="self-info box-sizing">
				<a class="left"><img id="linkedin_user_image" src=""/></a>
				<section class="text bold">
					<a id="linkedin_user_name">Chris Maden</a><br>
					<i class="icon icon-linkdin"></i>
				</section>
			</section>

			<section class="gray-box">
				<span class="p-left14 msg">Invite LinkedIn connections</span>
				<ul class="suggestions">

				</ul>
				<div class="w100p">
					<input name="" type="button" class="btn btn-green font16 m-top15 m-left14 linkdin-addhero" value="Add Connections" onclick="linkedin_network.inviteRegisteredHeros();">
				</div>
			</section>
		</div>
		<div id="linkedin_new_friends" style="display: none;">
			<section class="gray-box">
				<ul class="suggestions">

				</ul>
				<div class="w100p">
					<input name="" type="button" class="btn btn-green font16 m-top15 m-left14" value="Invite" onclick="linkedin_network.getSelectedMemberToInvite()">
				</div>
			</section>
		</div>
		<section class="invite" id="linkedin_invite_others" style="display: none;">
			<a id="linkedin-invite-btn" class="font14 bold btn btn-green" onclick="linkedin_network.nonOYHConnection();">Invite Linkedin connections</a>
			<div class="clear"></div>
		</section>
	</section>
</section>


