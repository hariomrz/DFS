<div class="page-content"> 
	<!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->
	<div class="content container">
		<div class="row">
			<div class="col-md-12">
				<div class="grid simple" ng-controller="UserProfileCtrl" ng-init="fetchDetails('load')" id="UserProfileCtrl">
					<?php $this->load->view('include/inner-navigation'); ?>
					<div class="grid-body no-border p-b-0">
						<div class="row">
							<div class="tab-content m-b-0 useprofiletag-list">
								<?php if($this->dashboard=='dashboard' || $this->dashboard=='profile') { 
									$wallData['GroupId']='';
									$wallData['post_type_id']=2;
									?>
									<input type="hidden" id="post_type"  value = "2" />
									<?php } ?>
										<form id="allcontrolform" class="tab-content m-b-0">
											<div class="tab-pane active p-b-0" id="tab1hellowWorld">
												<div class="row column-seperation">
													<div class="col-md-12">
														<div class="user-profile-wrap">
															<div id="current-picture" > <img src="{{imgsrc}}" >
																<input type="hidden" name="profile_media" value="{{ProfilePicture}}"/>
																<i class="del-ico" onclick="removeThisMedia(this);"  style="display:none; right:0px!important;left:151px!important;"><?php echo lang('remove') ?></i> </div>
																<div class="custom-table" id="uploadprofilepic" style="display:none;">
																	<aside class="custom-table-row">
																		<aside class="custom-table-cell"> <i class="icn-picture" id="profile-picture"></i> </aside>
																		<img  id="loader" src="<?php echo ASSET_BASE_URL ?>img/loader.gif" style="display:none;width: 21px;margin: 18px 0 0 0;" /> </aside>
																	</div>
																</div>
																<div class="overflow m-t-25" >
																	<div class="Editable" style="display:none">
																		<div class="info bor b-t b-l b-r b-b b-grey border-radius3">
																			<div class="textarea-box">
																				<uix-textarea rows="3" maxlength="200" maxcount="200"  name="aboutme" class="form-control user-status-box post-input" tabindex="1" data-ng-model="editAbove" id="prifiledescription" placeholder="<?php echo lang('write_description') ?>"></uix-textarea>
																			</div>
																		</div>
																	</div>
																	<div class="Editable m-t-20 m-b-15" id="removediv" style="display:none" >
																		<h3 class="semi-bold"><?php echo lang('expertise') ?></h3>
																		<input tabindex="2" type="hidden" name="tag[]" id="removeinput"  ng-value="exp.Expertise" class="form-control input-lg tag" ng-repeat="exp in Expertise " >
																	</div>
																	<div class="NonEditable">
																		<h3 ng-if="status!=''" class="semi-bold"><?php echo lang('about_me') ?></h3>
																		<p ng-if="status!=''" ng-bind="status"></p>
																	</div>
																	<div class="NonEditable">
																		<h3 class="semi-bold" ng-if="ttl>0"><?php echo lang('expertise') ?></h3>
																		<a href="#" ng-if="ttl>0" class="hashtags m-b-5 m-r-5" ng-repeat="exp in Expertise" ng-bind="exp.Expertise"></a> </div>
																	</div>
																</div>
															</div>
															<div class="column-seperation">
																<div class="post p-t-10 p-b-10 p-l-5 p-r-15  b-t b-grey overflow" >
																	<ul class="action-bar no-margin pull-left m-t-15">
																		<li class="m-b-5"><a href="javascript:void(0);" onclick="alertify.success('Coming Soon')" class="color-lightgray"><i class="fa icn-connections m-r-5"></i> {{records}} <?php echo lang('connections') ?></a> </li>
																		<li><a href="javascript:void(0);" onclick="alertify.success('Coming Soon')" class="color-lightgray"> <i class="fa icn-complete  m-r-5"></i>{{Percent}}% <?php echo lang('complete') ?></a> </li>
																	</ul>
																	<button type="button" class="btn btn-orange btn-small pull-right" id="follow_button" style="display:none;"><span class="bold"><?php echo lang('follow') ?></span></button>
																	<button type="button" class="btn btn-orange btn-small pull-right m-r-10 " id="wallEdit" ng-click="fetchDetails('edit')"><span class="bold"><?php echo lang('edit') ?></span></button>
																	<button style='display: none;' type="button" class="btn btn-orange btn-small pull-right m-r-10 " id="wallSave" ng-click="fetchDetails('save')"><span class="bold"><?php echo lang('save') ?></span></button>
																	<div class="clearfix"></div>
																</div>
															</div>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-8" ng-controller="WallPostCtrl" id="WallPostCtrl" ng-init="GetWallPostInit()">
									<?php $this->load->view('wall/wall',$wallData); ?>
								</div>
								<div class="col-md-4" ng-controller="UserProfileCtrl" ng-init="FetchInterest('load')" id = "UserProfileCtrl"  >
									<div class="grid simple" ng-init="callfollowing()">
										<div class="grid-title b-b b-grey no-border border-top-radius3">
											<h4> <a href="#" class="text-error left-2" ><i class="fa color-orange icn-connections"></i> </a> <?php echo lang('following') ?> ({{noOfObj}})</h4>
										</div>
										<div class="grid-body no-border">
											<div class="superbox p-t-15">
												<div class="superbox-list" ng-repeat=" member in connection ">
													<input type="hidden" value="{{member.TypeEntityID}}" id="entityid" />
													<img style="width:65px;" title="{{member.FirstName + ' ' + member.LastName}}" src="{{member.profilePicture}}"  class="superbox-img" /> </div>
													<div class="superbox-float"></div>
												</div>
											</div>
											<div ng-if="noOfObj > 6" class="grid-title b-t b-grey no-border center-text border-bottom-radius3"> <span class="h5"> <a href="<?php echo base_url(); ?>group"><?php echo lang('see_all') ?> <i class="fa fa-caret-right"></i><?php echo lang('group_member');?></a> </span> </div>
										</div>	

										<div class="grid simple" ng-init="callfollowers()">
										<div class="grid-title b-b b-grey no-border border-top-radius3">
											<h4> <a href="#" class="text-error left-2" ><i class="fa color-orange icn-connections"></i> </a> <?php echo lang('followers') ?> ({{fnoOfObj}})</h4>
										</div>
										<div class="grid-body no-border">
											<div class="superbox p-t-15">
												<div class="superbox-list" ng-repeat=" member in followers ">
													<input type="hidden" value="{{member.TypeEntityID}}" id="entityid" />
													<img style="width:65px;" title="{{member.FirstName + ' ' + member.LastName}}" src="{{member.profilePicture}}"  class="superbox-img" /> </div>
													<div class="superbox-float"></div>
												</div>
											</div>
											<div ng-if="fnoOfObj > 6" class="grid-title b-t b-grey no-border center-text border-bottom-radius3"> <span class="h5"> <a href="<?php echo base_url(); ?>group"><?php echo lang('see_all') ?> <i class="fa fa-caret-right"></i><?php echo lang('group_member');?></a> </span> </div>
										</div>
									</div>

									<!-- Friends Starts -->
									<div class="col-md-4" ng-init="getFriends(8)" ng-controller="UserListCtrl" id = "UserListCtrl"  >
									<div ng-if="showFriendBox=='1'" class="grid simple">
										<div class="grid-title b-b b-grey no-border border-top-radius3">
											<h4> <a href="#" class="text-error left-2" ><i class="fa color-orange icn-connections"></i> </a> <?php echo lang('friends') ?> ({{noOfObj}})</h4>
										</div>
										<div class="grid-body no-border">
											<div class="superbox p-t-15">
												<div class="superbox-list" ng-repeat="friend in friends">
													<input type="hidden" value="{{friend.TypeEntityID}}" id="entityid" />
													<img style="width:65px;" title="{{friend.FirstName + ' ' + friend.LastName}}" src="{{friend.profilepic}}"  class="superbox-img" /> </div>
													<div class="superbox-float"></div>
												</div>
											</div>
											<div ng-if="noOfObj > 8" class="grid-title b-t b-grey no-border center-text border-bottom-radius3"> <span class="h5"> <a href="<?php echo site_url('users/friends'); ?>"><?php echo lang('see_all') ?> <i class="fa fa-caret-right"></i></a> </span> </div>
										</div>	
									</div>
									<!-- Friends Ends -->
								</div>
							</div>

							<input type="hidden" id="postGuid"  value = "" />

							<!-- System Decision Alert Popup-->
							<div class="modal fade" id="systemDicisionAlert" lang="" tabindex="-1" role="dialog" data-aria-labelledby="myModalLabel" aria-hidden="true">
								 
                              	 	<div class="modal-dialog">
										<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
											<h3><?php echo lang('confirm') ?>?</h3>
										</div>
										<div class="modal-body padding-20 without-footer overflow">
											<p class="font16"><?php echo lang('delete_post_msg') ?></p>
											<div class="popup-double-button p-t-10">
												<button type="button" class="btn btn-success" onclick="dodelete()"><?php echo lang('yes') ?></button>
												<button type="button" class="btn" onclick="donothing()"><?php echo lang('no') ?></button>
											</div>
										</div>
									</div>
                                    </div>
								 
							</div>
							<!-- /System Decision Alert Popup-->
							<footer> </footer>
						</div>
