<div id="fb-root"></div>

<!--Container-->
<div class="container wrapper">
	<div class="custom-modal">
		<!--Offset Title-->
		<div class="row offset-title">
			<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6">
				<h4 class="label-title secondary-title">{{lang.invite}} 
					<span>
						{{lang.your}} {{lang.friends}} 
						<span class="hidden-phone">{{lang.to}} {{lang.web_name}}</span>
					</span>
				</h4>
			</div>
			<div class="col-lg-6 col-md-6 col-xs-12 col-sm-6 right">
				<h4 class="label-title secondary-title"> {{lang.find}} 
					<span>
						{{lang.friends}} 
						<span class="hidden-phone">{{lang.on}} {{lang.web_name}}</span>
					</span>
				</h4>
			</div>
		</div>
		<div class="invite-section">
			<div class="tab-dropdowns profile-tabs"> 
				<a href="javascript:void(0);"> 
					<i class="icon-smallcaret"></i> 
					<span>Facebook</span> 
				</a> 
			</div>
			<div class="verticle-tab"> 
				<!-- Nav tabs -->
				<ul id="tab-2" class="nav tabs-left social-tabs hidden-xs small-screen-tabs">
					<li class="active">
						<a href="#tabContent-1" id="fbconect" onclick="initiliseSocialNetwork(1);" data-toggle="tab">
							<span class="btn btn-social-default btn-fb rounded-corner"><i class="icon-facebook"></i></span>
							<span class="text"><?php echo lang('facebook') ?></span>
						</a>
					</li>
					<li>
						<a href="#tabContent-2" id="twconect" onclick="initiliseSocialNetwork(2)" data-toggle="tab">
							<span class="btn btn-social-default  btn-tw  rounded-corner"><i class="icon-twitter"></i></span>
							<span class="text"><?php echo lang('twitter') ?></span>
						</a>
					</li>
					<li>
						<a href="#tabContent-3" id="gplusconect" onclick="initiliseSocialNetwork(3)" data-toggle="tab">
							<span class="btn btn-social-default btn-gpl rounded-corner"><i class="icon-gplus"></i></span>
							<span class="text"><?php echo lang('gplus') ?></span>
						</a>
					</li>
					<li>
						<a href="#tabContent-4" id="linkdinconect" onclick="initiliseSocialNetwork(4)" data-toggle="tab">
							<span class="btn btn-social-default  btn-ld rounded-corner"><i class="icon-linkedin"></i></span>
							<span class="text"><?php echo lang('linkedin') ?></span>
						</a>
					</li>
					<li>
						<a href="#tabContent-5" data-toggle="tab">
							<span class="btn btn-social-default btn-email rounded-corner"><i class="icon-msg-white"></i></span>
							<span class="text"><?php echo lang('email') ?></span>
						</a>
					</li>
				</ul>
				<!-- Tab panes -->
				<div class="tab-content invite-space"> 

					<!--Facebook-->
					<div id="tabContent-1" class="tab-pane active">
						<div class="col-lg-5 col-sm-6 before-fb-login-wrap">
							<a class="btn rounded-corner btn-social-lg btn-icon-fb" href="javascript:void(0);">
								<i class="icon-facebooklg"></i>
							</a>
							<a class="social-lg-btn fb-btn btn-block" onclick="fb_build_network.FbMultiSelectFriend();" > 
								<mark><i class="icon-facebook"></i> </mark>
								<span class="btn-text"><?php echo lang('invite').' '.lang('your').' '.lang('facebook').' '.lang('friends') ?></span> 
							</a> 
						</div> 
					</div>

					<!--Twitter-->
					<div id="tabContent-2" class="tab-pane">
						<div class="col-lg-5 col-sm-6 before-fb-login-wrap">
							<a class="btn rounded-corner btn-social-lg btn-icon-tw" href="javascript:void(0);">
								<i class="icon-twitterlg"></i>
							</a>
							<div id="twitter_connect">
								<a class="social-lg-btn tw-btn btn-block" onclick="twt_build_network.get_twitter_friend();">
									<mark><i class="icon-twitter"></i> </mark>
									<span class="btn-text"><?php echo lang('connect_with_twitter') ?></span>
								</a>
							</div>
						</div> 
						<!--After connect-->
						<div class="after-connect">
							<div id="twitter_friends" style="display: none;">
								<ul class="list-group"> 
									<li class="list-group-item">
										<figure>
											<a><img src="<?=base_url();?>img/img50-3.jpg" id="twt_profile_pic" width="50" height="50"  class="left"></a>
										</figure>
										<div class="description"> 
											<a id="twt_user_name"></a><br>
											<i class="icon-twgray"></i> 
										</div>
									</li>
								</ul>
								<div class="add-connections"> 
									<span class="left-space"><?php echo lang('get_started_twitter_followers') ?> </span>
									<ul class="suggestions google-suggesstion list-group">
									</ul>
									<input name="" type="button" class="btn btn-default left-space" value="Add Connections" onclick="twt_build_network.invite_registered_heros();">
								</div>

								<div id="twitter_new_friends" style="display:none;">
									<ul class="suggestions google-suggesstion list-group">
									</ul>
									<input id="invite_non_oyh_friend" name="" type="button" class="btn btn-primary left-space" value="Invite Connections" onclick="twt_build_network.invite_non_OYH_friends();">
								</div>

								<div id="twitter_invite_other" style="display: none;"> 
									<a id="twitter-invite-btn" class="btn btn-primary left-space" onclick="twt_build_network.non_OYH_connection();">
										<?php echo lang('invite_twitter_followers') ?>
									</a>
								</div>

							</div>
						</div>
					</div>

					<!--Google Plus-->
					<div id="tabContent-3" class="tab-pane">
						<div class="col-lg-5 col-sm-6 before-fb-login-wrap">
							<a class="btn rounded-corner btn-social-lg btn-icon-gp" href="javascript:void(0);">
								<i class="icon-googlelg"></i>
							</a>
							<div id="google_connect">
								<!-- <input name="" type="button" class="btn btn-blue no-float" value="Connect with Google+" onclick="bn_handleClientLoad();"> -->
								<div id="gplus_build_nw" class="social-lg-btn gp-btn btn-block">
									<mark><i class="icon-gplus"></i> </mark>
									<span class="btn-text"><?php echo lang('connect_with_gplus') ?></span>
								</div>
							</div>
						</div>
						<!--After connect-->
						<div class="after-connect">
							<div id="google_friends" style="display: none">
								<ul class="list-group"> 
									<li class="list-group-item">
										<figure><a><img id="google_user_image" src="" width="50" height="50"  class="left"></a></figure>
										<div class="description"> 
											<a id="google_user_name"></a><br>
											<i class="icon-gpgray"></i> 
										</div>
									</li>
								</ul>
								<div class="add-connections">
									<span class="left-space"><?php echo lang('getting_started_google_followers') ?></span>
									<ul class="suggestions google-suggesstion list-group">
									</ul>
									<input name="" type="button" class="btn btn-default left-space" value="Add Connection" onclick="bn_inviteRegisteredHeros();">
								</div>
								<a id="google-invite-btn" onclick="shareOnGoogle();$(this).removeClass('btn-orange').addClass('btn-orange');" class="btn btn-primary left-space">
									<div id="google_invite_others"> <?php echo lang('invite_google_followers') ?> </div>
								</a>
							</div>
							<div id="google_new_friends" style="display: none;">
								<ul class="suggestions list-group">
								</ul>
								<input name="" type="button" class="btn btn-primary" value="Invite Connections" onclick="">
							</div>
							<div  style="display: none;">
								<a onclick="google_network.shareOnGoogle();">
									<div id="google_invite_others left-space"><?php echo lang('invite_google_connections') ?></div>
								</a> 
							</div>
						</div>
					</div>

					<!--//Linked In-->
					<div id="tabContent-4" class="tab-pane">
						<div class="col-lg-5 col-sm-6 before-fb-login-wrap">
							<a class="btn rounded-corner btn-social-lg btn-icon-ld" href="javascript:void(0);">
								<i class="icon-linkdinlg"></i>
							</a>
							<div id="linkedin_connect">
								<a class="social-lg-btn ld-btn btn-block" onclick="linkedin_network.dolinkedinLogin();">
									<mark><i class="icon-linkedin"></i> </mark>
									<span class="btn-text"><?php echo lang('connect_with_linkedin') ?></span>
								</a>
							</div>
						</div>

						<!--After connect-->
						<div class="after-connect">
							<div id="linkedin_friends" style="display: none">

								<ul class="list-group"> 
									<li class="list-group-item">
										<figure><a> <img id="linkedin_user_image" ></a></figure>
										<div class="description"> 
											<a id="linkedin_user_name">Chris Maden</a><br>
											<i class="icon-ldgray"></i> 
										</div>
									</li>
								</ul>

								<div class="add-connections"> 
									<span class="left-space"><?php echo lang('add_linkedin_connections') ?></span>
									<ul class="suggestions list-group">
									</ul>
									<input name="" type="button" class="btn btn-default left-space" value="Add Connections" onclick="linkedin_network.inviteRegisteredHeros();">
								</div>


							</div>
							<div id="linkedin_new_friends" style="display: none;">
								<ul class="suggestions google-suggesstion list-group">
								</ul>
								<input name="" type="button" class="btn btn-primary left-space" value="Invite Connections" onclick="linkedin_network.getSelectedMemberToInvite()">
							</div>

							<div  id="linkedin_invite_others" style="display: none;"> 
								<a id="linkedin-invite-btn" class="btn btn-primary left-space" onclick="linkedin_network.nonOYHConnection();"><?php echo lang('linkedin_followers') ?></a>
							</div>
						</div>
					</div>    

					<!--Email-->
					<div id="tabContent-5" class="tab-pane p-t-100">
						<div class="col-lg-8 co-md-8 col-sm-8" data-ng-controller="InviteFriendCtrl" id="InviteFriendCtrlContainer">
							<form id="email-newtwork-form" data-ng-submit="checkstatus()">
								<div class="form-group">
									<label><?php echo lang('email_of_friend') ?></label>
									<div class="text-field" data-error="hasError">
										<uix-input type="text" 
												name="form1email" 
												class="form-control"
												placeholder="xyz@vinfotech.com" 
												id="form1email" 
												value=""
												data-controltype="email" 
												data-mandatory="true" 
												data-msglocation="errorform1email" 
												data-requiredmessage="Please enter a your Email." 
												data-ng-model="friendsemail">
									</uix-input>
									<div id="errorform1email" class="error-block-overlay"></div>
								</div>
							</div>

							<div class="form-group">
								<label><?php echo lang('your_personal_message') ?></label>
								<div class="textarea-field" data-error="hasError">
									<uix-textarea 
									id="textareaID" 
									placeholder="Whats on your mind?" 
									data-ng-model="personalmessage" 
									maxcount="200"></uix-textarea>
									<div id="errorinvitaionmessage" class="error-block"></div>
								</div>
							</div>

							<div class="form-group">
								<button type="button" class="btn btn-primary" id="nativesendinvitaion"><?php echo lang('send_invites') ?></button>
							</div>

						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

<script type="text/javascript">
		$('document').ready(function(){
			initiliseSocialNetwork(1);
		});
</script>