<div id="UserListCtrl" ng-controller="UserListCtrl">
    <div ng-controller="QuestionController"> 
        <div>
            <?php $this->load->view('admin/all_question/question_filters'); ?>
            <section class="main-container">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-9">
                            <div class="filter-region">
			                    <div class="filter-tag selected-approve" ng-click="get_unansered_questions();" ng-class="TabUnansewered">
			                        <label>Unanswered Question</label>
			                    </div>
			                    <div class="filter-tag selected-reject" ng-click="get_ansered_questions();" ng-class="TabAnsewered">
			                        <label>Answered Question</label>
			                    </div>
								<div class="filter-tag selected-reject" ng-click="get_not_require_answer_questions();" ng-class="TabNotAnswered">
			                        <label>Does not require Answer</label>
			                    </div>
			                </div>
                        </div>
                        <div class="col-xs-3" style="text-align: center;">
                        	<div class="filter-tag selected-approve">
		                        <label>User Details</label>
		                    </div>
                        </div>
                    </div>
                    <div class="row p-t-sm">
                        <div class="col-xs-12" ng-init="get_unansered_questions();">
                            <?php $this->load->view('admin/all_question/questionsList'); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="send_question_notification_popup" ng-cloak>
        	<div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-close" ng-click="close_notification_popup();"></i></span></button>
                        <h4>Notifications</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup-content no-padding">
                            <div class="row-flued">
                            	<div class="col-xs-8">
			                    	<div class="post-type-title">
				                        <span class="a-link">{{popupContent.PostTitle | limitTo: 100}}</span>
				                    </div>
			                    	<span class="a-link" ng-bind-html="textToLink(popupContent.PostContent) | limitTo: 100"></span>
				                    <div class="row form-group m-t-sm">
				                    	<div class="col-sm-4">
				                    		<span class="bold-14">Send Notification to</span>						                  							                  	
						                </div>
										<div class="col-sm-8">
				                    		<input  type="checkbox" name="IsFollower" ng-model="userListReqData_default.IsFollower" ng-change="getNotiUsersList();">
				                    		<label for="">Post Owner followers</label>
						                  	
						                </div>
				                    </div>
									<div class="row form-group">
										<div class="col-sm-4">
			                    			<span class="bold-14">Send Notification to</span>
										</div>
										<div class="col-sm-8">
											<input class="m-h" type="radio" name="all" ng-value="0" ng-model="userListReqData_default.Gender" ng-change="getNotiUsersList();">
											<label for="">All</label>
											<input class="m-h" type="radio" name="male" ng-value="1" ng-model="userListReqData_default.Gender" ng-change="getNotiUsersList();">
											<label for="">Male</label>
											<input class="m-h" type="radio" name="female" ng-value="2" ng-model="userListReqData_default.Gender" ng-change="getNotiUsersList();">
											<label for="">Female</label>
										</div>
				                    </div>
				                    <div class="row form-group flx-rw" ng-init="getWardListNoti();">
										<div class="col-sm-4">
				                    		<span class="bold-14 m-r-lg">Location</span>
				                    	</div>
				                    	<div class="col-sm-8">
				                            <select id="select_ward" ng-change="wardSelectedNoti()" chosen class="form-control" ng-options="wards.WID as wards.WName+(wards.WNumber>0?' (Ward - '+wards.WNumber+')':' Ward') for wards in ward_list_noti" data-ng-model="userListReqData_default.WID">
				                                <option></option>
				                            </select>
				                    	</div>
			                        </div>
				                    <div class="row form-group flx-rw">
										<div class="col-sm-4">
			                    			<span class="bold-14">Age between</span>
				                    	</div>
			                            <div class="col-sm-8">
			                                <div class="row gutter-5">
			                                    <div class="col-sm-4">
			                                        <div class="form-group">
			                                            <input type="text" class="form-control" name="AgeStart" ng-model="userListReqData_default.AgeStart" ng-change="getNotiUsersList();" age-validate />
			                                        </div>
			                                    </div>
			                                    <div class="col-sm-4 text-center lh-30">&mdash;</div>
			                                    <div class="col-sm-4">
			                                        <div class="form-group">
			                                            <input type="text" class="form-control" name="AgeEnd" ng-model="userListReqData_default.AgeEnd" ng-class="{'red-border': (userListReqData_default.AgeStart >= userListReqData_default.AgeEnd)}" ng-change="getNotiUsersList();" age-validate />
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
				                    </div>
				                    <div class="row form-group">
										<div class="col-sm-4">
				                    		<span class="bold-14">Income level</span>
										</div>
			                            <div class="col-sm-8">
						                  	<input  type="checkbox" name="low" ng-model="userListReqData_default.Income.low" ng-change="getNotiUsersList();">
				                    		<label class="m-r-sm" for="">Low</label>
						                  	<input type="checkbox" name="medium" ng-model="userListReqData_default.Income.med" ng-change="getNotiUsersList();">
				                    		<label class="m-r-sm" for="">Medium</label>
						                  	<input type="checkbox" name="high" ng-model="userListReqData_default.Income.high" ng-change="getNotiUsersList();">
				                    		<label for="">High</label>
						                </div>
				                    </div>
									<div class="row">
                                		<div class="col-sm-3">
				                    
							            	<label class="control-label bolder">USER TAGS</label>
										</div>
										<div class="col-sm-9">	
											<div class="form-group input-icon tag-suggestions tag-dis-cls">
												<i class="ficon-price-tag"></i>
												<tags-input
													ng-model="QUE_reqData_default.TagUserType"
													display-property="Name"
													on-tag-added="addMemberTags('USER', $tag, popupContent.ModuleEntityGUID, 3)"
													on-tag-removed="removeMemberTags('USER', $tag, popupContent.ModuleEntityGUID, 3)"
													placeholder="Add user type" 
													add-from-autocomplete-only="true"
													template="tag7">
													<auto-complete source="loadMemberTags($query, popupContent.ModuleEntityID, 3, 'USER', 1)" load-on-focus="true" min-length="0"></auto-complete>
												</tags-input>
												<script type="text/ng-template" id="tag7">
													<div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
													<span ng-if="data.Name != ''" ng-bind="data.Name"></span>
													<a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
													</div>
												</script>
											</div>
										
											<div class="form-group m-l-md"> 
												<div class="radio-list">
													<label class="radio radio-inline">
														<input type="radio" ng-value="1"  ng-model="userListReqData_default.TagUserSearchType" name="MatchTagsuser" class="TagUserSearchType" ng-change="getNotiUsersList();">
														<span class="label">Any one tag</span>
													</label>
													<label class="radio radio-inline">
														<input type="radio" ng-value="0"  ng-model="userListReqData_default.TagUserSearchType" name="MatchTagsuser" class="TagUserSearchType" ng-change="getNotiUsersList();">
														<span class="label">Common tag</span>
													</label>
												</div>
											</div>
										</div>
									</div>
							        <!-- <div class="form-group flx-rw">
							            <label class="control-label bolder">PROFESSION TAGS</label>
							            <div class="input-icon col-sm-6">
							                <i class="ficon-profession"></i>
							                <tags-input
							                    ng-model="QUE_reqData_default.TagTagType"
							                    display-property="Name"
							                    on-tag-added="addMemberTags('PROFESSION', $tag, popupContent.ModuleEntityGUID, 3)"
							                    on-tag-removed="removeMemberTags('PROFESSION', $tag, popupContent.ModuleEntityGUID, 3)"
							                    placeholder="Add more profession" 
												add-from-autocomplete-only="true"
							                    template="tag5">
							                    <auto-complete source="loadMemberTags($query, popupContent.ModuleEntityID, 3, 'PROFESSION', 1)" load-on-focus="true" min-length="0"></auto-complete>
							                </tags-input>
							                <script type="text/ng-template" id="tag5">
							                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
							                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
							                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
							                    </div>
							                </script>
							            </div>
							        </div> -->
			                	</div>
			                	<div class="col-xs-4">
			                		<div class="rectangle-box-bg">
			                			<div>
							                <span class="bold-18">{{popupContent.NC}}</span>
							                <p>Notifications</p>
			                			</div>
			                			<div>
							                <span class="bold-18">{{popupContent.SC}}</span>
							                <p>SMS</p>
			                			</div>
						            </div>
						            <div class="p-sm">
						            	<input type="checkbox" name="send_notifiaction" ng-model="send_notifiaction">
			                    		<label for="">Send notification</label>
			                			<div class="rectangle-box-noti">
			                				<div class="form-group">
							            		<input type="text" class="form-control" name="noti_header" ng-model="popupContent.notificationTitle" placeholder="Header" autocomplete="off" />
			                				</div>
							                <!-- <input type="text" name="noti_header"> -->
							                <div class="text-field">
							                    <textarea style="min-height: 85px;" id="noti_text" name="noti_text" rows="2" type="text" placeholder="body" ng-model="popupContent.notificationText"></textarea>
							                </div>
							            </div>
						            </div>
						            <div class="p-sm">
						            	<input type="checkbox" name="send_sms" ng-model="send_sms">
			                    		<label for="">Send sms</label>
			                			<div class="rectangle-box-noti">
							                <div class="text-field">
							                    <textarea style="min-height: 85px;" id="sms_text" name="sms_text" rows="2" type="text" ng-model="popupContent.smsText"></textarea>
							                </div>
							            </div>
						            </div>

			            			<div class="rectangle-box-send">
			            				<span class="f-16 text-muted">Matches: </span><span class="bold-18">{{NotiUsersCount}}</span>
			            				<button ng-disabled="NotiUsersCount == 0" type="button" ng-click="send_notifiactions();" class="btn btn-primary">Send</button>
			            			</div>
			                	</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="responses_popup" ng-cloak ng-if="res_popup_act_data">
        	<div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="icon-close" ng-click="close_responses_popup();"></i></span></button>
                    	<h4>{{res_popup_act_data.popupHeading}} ({{res_popup_act_data.totlaCount}})</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup-content no-padding">
                            <div class="row-flued">
                            	<div class="post-type-title" ng-if="res_popup_act_data.PostTitle">
			                        <span class="a-link">{{res_popup_act_data.PostTitle}}</span>
			                    </div>
			                    <div class="post-type-title" ng-if="res_popup_act_data.PostTitle == ''">
			                        <span class="a-link" ng-bind-html="textToLink(res_popup_act_data.PostContent) | limitTo: 100"></span>
			                    </div>
			                    <div class="default-scroll scrollbar" when-scrolled="getAllResponses();">
				                    <div ng-repeat="(res_key, res_value) in allResponses" class="scrollbar">
				                    	<div class="row grey-bg-box-border m-b-sm p-sm">
						                	<div class="col-xs-9 m-b">
					                    		<p>
					                    			<span class="bold-14">({{res_key+1}}) </span>
				            						<span class="bold-14"> {{res_value.Name}}</span>
				            						<span class="f-14">(<span class="f-14" ng-bind="createDateObject(utc_to_time_zone(res_value.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></span>)</span>
				            						<span ng-if="res_value.Occupation" class="text-base block">{{res_value.Occupation}} </span>
												</p>
					                    		<span class="list-group-item-text f-14" ng-bind-html="textToLink(res_value.PostComment)"></span>
					                    		<div ng-if="(res_value.Media.length > 0)" ng-class="layoutClass(res_value.Media)" class="feed-content mediaPost">
											        <figure class="media-thumbwrap">
											            <a class="mediaThumb">
											            	<img ng-if="res_value.Media[0].MediaType == 'Image'" ng-src="{{imageServerPath + 'upload/comments/533x300/' + res_value.Media[0].ImageName}}" alt="">
											            </a>
											        </figure>
											    </div>
						                		<div ng-if="res_value.NoOfReplies > 0" style="margin-left: 10%;">
						                			<div ng-repeat="(rep_res_key, rep_res_value) in res_value.Replies" class="m-t-sm">
						                				<div class="row grey-bg-box-border m-b-sm p-sm">
							                				<div class="col-xs-9">
							                					<p>
							                						<span class="bold-14">({{res_key+1}}:{{rep_res_key+1}})</span>
							                						<span class="bold-14"> {{rep_res_value.Name}}</span>
							                						<span class="f-14">(<span class="f-14" ng-bind="createDateObject(utc_to_time_zone(rep_res_value.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></span>)</span>
							                					</p>
					                    						<span class="list-group-item-text f-14" ng-bind-html="textToLink(rep_res_value.PostComment)"></span>
							                				</div>
							                				<div class="col-xs-3 m-t-sm">
							                					<div>
												                    <label class="checkbox">
												                        <input ng-checked="rep_res_value.Solution == 0" ng-click="update_checkbox_set(rep_res_value.CommentGUID, 0)" id="comment_0_{{rep_res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
												                        <span class="label bold">None</span>
												                    </label>
												                </div>
									                    		<div>
												                    <label class="checkbox">
												                        <input ng-checked="rep_res_value.Solution == 1" ng-click="update_checkbox_set(rep_res_value.CommentGUID, 1)" id="comment_1_{{rep_res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
												                        <span class="label bold">Possible Solution</span>
												                    </label>
												                </div>
												                <div class="m-b">
												                    <label class="checkbox">
												                        <input ng-checked="rep_res_value.Solution == 2" ng-click="update_checkbox_set(rep_res_value.CommentGUID, 2)" id="comment_2_{{rep_res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
												                        <span class="label bold">Solution</span>
												                    </label>
												                </div>
							                				</div>
						                				</div>
						                			</div>
						                		</div>
						                    </div>
						                    <div class="col-xs-3">
					                    		<div>
								                    <label class="checkbox">
								                        <input ng-checked="res_value.Solution == 0" ng-click="update_checkbox_set(res_value.CommentGUID, 0)" id="comment_0_{{res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
								                        <span class="label bold">None</span>
								                    </label>
								                </div>
					                    		<div>
								                    <label class="checkbox">
								                        <input ng-checked="res_value.Solution == 1" ng-click="update_checkbox_set(res_value.CommentGUID, 1)" id="comment_1_{{res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
								                        <span class="label bold">Possible Solution</span>
								                    </label>
								                </div>
								                <div>
								                    <label class="checkbox">
								                        <input ng-checked="res_value.Solution == 2" ng-click="update_checkbox_set(res_value.CommentGUID, 2)" id="comment_2_{{res_value.CommentGUID}}" type="checkbox" class="check-content-filter">
								                        <span class="label bold">Solution</span>
								                    </label>
								                </div>
						                    </div>
				                    	</div>
				                    </div>
			                	</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-header" style="text-align: center;">
		            	<button type="button" class="btn btn-primary" ng-click="submit_solutions();">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="activity_details_popup" ng-cloak ng-if="popupActivityData">
        	<div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-close" ng-click="close_activity_details_popup();"></i></span></button>
                        <h4>Activity Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup-content no-padding">
                            <div class="row-flued">
                            	<div class="col-xs-7">
								    <div class="panel panel-primary">
								    	<div class="panel-body">
								            <ul class="list-group list-group-thumb sm">
								                <li class="list-group-item">
								                    <div class="list-group-body">
								                        <figure class="list-figure">
								                            <a class="thumb-48 " entitytype="page" entityguid="{{popupActivityData.UserGUID}}" ng-if="popupActivityData.PostAsModuleID == '18' && popupActivityData.ActivityTypeID !== 23 && popupActivityData.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + popupActivityData.UserProfileURL}}">
								                                <img ng-if="popupActivityData.EntityProfilePicture !== 'user_default.jpg'" err-name="{{popupActivityData.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityData.EntityProfilePicture}}">
								                            </a>
								                            <a class="thumb-48 " entitytype="user" entityguid="{{popupActivityData.UserGUID}}" ng-if="popupActivityData.PostAsModuleID == '3' && popupActivityData.ActivityTypeID !== '23' && popupActivityData.ActivityTypeID !== '24'" ng-href="{{baseUrl + popupActivityData.UserProfileURL}}">
								                                <img ng-if="popupActivityData.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{popupActivityData.UserName}}" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityData.ProfilePicture}}">
								                            </a>
								                            <a class="thumb-48 " entitytype="user" entityguid="{{popupActivityData.UserGUID}}" ng-if="(popupActivityData.ActivityTypeID == '23' || popupActivityData.ActivityTypeID == '24') && popupActivityData.ModuleID !== '18'" ng-href="{{baseUrl + popupActivityData.UserProfileURL}}">
								                                <img err-name="{{popupActivityData.UserName}}" ng-if="popupActivityData.ProfilePicture !== '' && popupActivityData.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityData.ProfilePicture}}">
								                            </a>
								                            <a class="thumb-48 " entitytype="page" entityguid="{{popupActivityData.EntityGUID}}" ng-if="(popupActivityData.ActivityTypeID == 23 || popupActivityData.ActivityTypeID == 24) && popupActivityData.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + popupActivityData.EntityProfileURL}}">
								                                <img ng-if="popupActivityData.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + popupActivityData.EntityProfilePicture}}">
								                            </a>
								                        </figure>
								                        <div class="list-group-content" ng-init="activityTitleMessage='';">
								                            <h6 class="list-group-item-heading" ng-bind-html="getTitleMessage(popupActivityData)"></h6>
								                            <ul class="list-activites">
								                                <li ng-if="activityData.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(utc_to_time_zone(activityData.comment_details.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
								                                <li ng-if="activityData.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(utc_to_time_zone(popupActivityData.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
								                            </ul>
								                        </div>
								                    </div>
								                	<ng-include ng-if="((popupActivityData.ActivityTypeID != 23) && (popupActivityData.ActivityTypeID != 24) && (popupActivityData.ActivityTypeID != 25) && (popupActivityData.ActivityTypeID != 16))" src="partialPageUrl + 'popupActivityContent.html'"></ng-include>
								                </li>
								            </ul>
								        </div>
								    </div>
			                	</div>
			                	<div class="col-xs-5">
			                		<div class="panel panel-primary">
			    						<div class="panel-body user-sm-info">
			    							<ul class="list-group list-group-thumb md">
									            <li class="list-group-item">
									                <div class="list-group-body">
									                	<figure class="list-figure">
									                        <a class="" entitytype="user">
									                            <img ng-if="((userPostDetail.UserDetails.ProfilePicture != '') && (userPostDetail.UserDetails.ProfilePicture !='user_default.jpg'))" class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + userPostDetail.UserDetails.ProfilePicture}}">
									                            <span ng-if="((userPostDetail.UserDetails.ProfilePicture == '') || (userPostDetail.UserDetails.ProfilePicture =='user_default.jpg'))" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(userPostDetail.UserDetails.Name)"></span></span>
									                        </a>
									                    </figure>
									                    <div class="list-group-content">
									                        <h4 class="list-group-item-heading lg">
																<a ng-bind="userPostDetail.UserDetails.Name"></a>
																<a uib-tooltip="VIP User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsVIP == 1 )" class="icn circle-icn circle-primary">
																	<i class="ficon-check"></i>
																</a>
																<a uib-tooltip="Association User" tooltip-append-to-body="true" ng-if="( userPostDetail.UserDetails.IsAssociation == 1 )" class="icn circle-icn circle-primary">
																	<i class="ficon-check"></i>
																</a>
									                        </h4>
									                        <span class="text-base block" ng-if="userPostDetail.UserDetails.Occupation">
									                            <span>{{userPostDetail.UserDetails.Occupation | limitTo: 35}}</span>
									                        </span>
									                        <span class="text-base block" >
									                            <span ng-if="userPostDetail.UserDetails.Locality.Name">{{userPostDetail.UserDetails.Locality.Name}}, {{userPostDetail.UserDetails.Locality.WName}} (Ward {{userPostDetail.UserDetails.Locality.WNumber}})</span>
									                        </span>
									                        <ul class="user-info-list collapse" id="userInfoToggle">
									                            <li class="row">
									                                <div class="col-xs-6">
									                                    <label class="label-text">Posts <span class="text" ng-bind="': ' + userPostDetail.UserDetails.PostCount"></span></label>
									                                </div>
									                                <div class="col-xs-6">
									                                    <label class="label-text">Comments <span class="text" ng-bind="': ' + userPostDetail.UserDetails.CommnetCount"></span></label>
									                                </div>
									                            </li>
									                            <li class="row text-right" ng-if="userPostDetail.UserDetails.MemberSince">
									                                <div class="col-xs-12"><small ng-bind="createDateObj(userPostDetail.UserDetails.MemberSince) | date : ' \'Member Since : \' dd \'-\' MMM yyyy'">Member Since : 02-Aug 2006</small></div>
									                            </li>
									                        </ul>
									                    </div>
									                </div>
									            </li>
									        </ul>
									        <a class="ficon-arrow-down feed-expand collapsed" data-toggle="collapse" href="#userInfoToggle"></a>
			    						</div>
			    						<div ng-if="(userPostDetail.UserDetails)" class="panel-body custom-scroll scroll-sms" style="height: 330px;">
			    							<div ng-if="userPostDetail.ActivityVisibility" class="form-group no-bordered">
									            <label class="control-label bolder">POST VISIBILITY</label>
									            <ul class="tags-list clearfix">
									                <li ng-repeat="visibility in userPostDetail.ActivityVisibility">
									                    <span>{{visibility.WID == 1 ? 'All Ward' : 'Ward '+visibility.WNumber}}</span>
									                </li>
									            </ul>
									        </div>
			    							<div ng-if="userPostDetail.ActivityTags.Question" class="form-group no-bordered">
									            <label class="checkbox">
									                <input ng-checked="userPostDetail.ActivityTags.Question.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Question, popupActivityData.ActivityID, 0, 'que', popupActivityData.ActivityGUID)" id="que_{{popupActivityData.ActivityID}}" value="{{userPostDetail.ActivityTags.Question.TagID}}" type="checkbox" class="check-content-filter">
									                <span class="label bold">Question</span>
									            </label>
									            <div class="input-icon" ng-if="userPostDetail.ActivityTags.Question.IsExist == 2">
									                <i class="ficon-price-tag"></i>
								                    <tags-input
								                        ng-model="userPostDetail.ActivityTags.Question.CCategory"
								                        display-property="Name"
								                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Question, popupActivityData.ActivityID)"
								                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Question, popupActivityData.ActivityID)"
								                        placeholder="Add question category"
								                        readonly="readonly"
								                        replace-spaces-with-dashes="false"
								                        add-from-autocomplete-only="true"
								                        template="qtag">
								                        <auto-complete source="loadTagCategories($query, popupActivityData.ActivityID, 'que')" load-on-focus="true" min-length="0"></auto-complete>
								                    </tags-input>
								                    <script type="text/ng-template" id="qtag">
								                        <div class="tag-template added-by-admin">
								                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
								                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
								                        </div>
								                    </script>
									            </div>
									        </div>
									        <div ng-if="userPostDetail.ActivityTags.Custom" class="form-group no-bordered">
									            <label class="checkbox">
									                <input ng-checked="userPostDetail.ActivityTags.Custom.IsExist == 1" ng-click="toggleCustomTags(userPostDetail.ActivityTags.Custom, popupActivityData.ActivityID, 0, 'cla', popupActivityData.ActivityGUID)" id="cla_{{popupActivityData.ActivityID}}" value="{{userPostDetail.ActivityTags.Custom.TagID}}" type="checkbox" class="check-content-filter">
									                <span class="label bold">Classified</span>
									            </label>
									            <div class="input-icon" ng-if="userPostDetail.ActivityTags.Custom.IsExist == 1">
									                <i class="ficon-price-tag"></i>
								                    <tags-input
								                        ng-model="userPostDetail.ActivityTags.Custom.CCategory"
								                        display-property="Name"
								                        on-tag-added="addTagCategories($tag, userPostDetail.ActivityTags.Custom, popupActivityData.ActivityID)"
								                        on-tag-removed="removeTagCategories($tag, userPostDetail.ActivityTags.Custom, popupActivityData.ActivityID)"
								                        placeholder="Add classified category"
								                        readonly="readonly"
								                        replace-spaces-with-dashes="false" 
								                        add-from-autocomplete-only="true"
								                        template="ctag">
								                        <auto-complete source="loadTagCategories($query, popupActivityData.ActivityID, 'cla')" load-on-focus="true" min-length="0"></auto-complete>
								                    </tags-input>
								                    <script type="text/ng-template" id="ctag">
								                        <div class="tag-template added-by-admin">
								                        <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
								                            <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
								                        </div>
								                    </script>
									            </div>
									        </div>
											<div>
											<div class="form-group no-bordered">
												<span ng-if="userPostDetail.IsAnswerRequired == 0">
													<a class="btn btn-xs btn-icn btn-default" ng-click="not_require_answer()" >
														<span class="icn " ng-cloak>
															<i class="ficon-comment"></i>
														</span>
													</a>
													<span class="label bold cursor-pointer" ng-click="not_require_answer()"> Does not require answer  </span>
												</span>
												<span ng-if="userPostDetail.IsAnswerRequired == 1">
													<a class="btn btn-xs btn-icn btn-default" ng-click="not_require_answer()" >
														<span class="icn gfill" ng-cloak>
															<i class="ficon-comment"></i>
														</span>
													</a> 
													<span class="label bold cursor-pointer" ng-click="not_require_answer()"> Require answer  </span>
												</span>												
											</div>
									        <div ng-if="userPostDetail.ActivityTags" class="form-group no-bordered">
									            <label class="control-label bolder">POST TAGS</label>
									            <div class="input-icon tag-suggestions">
									                <i class="ficon-price-tag"></i>
									                <tags-input
									                    ng-model="userPostDetail.ActivityTags.Normal"
									                    display-property="Name"
									                    on-tag-added="addMemberTagsPopup('ACTIVITY', $tag, popupActivityData.ActivityGUID, 0)"
									                    on-tag-removed="removeMemberTagsPopup('ACTIVITY', $tag, popupActivityData.ActivityGUID, 0)"
									                    placeholder="Add more tags"
									                    replace-spaces-with-dashes="false" 
									                    add-from-autocomplete-only="true"
									                    template="tag1">
									                    <auto-complete source="loadMemberTags($query, popupActivityData.ActivityID, 0, 'ACTIVITY', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
									                </tags-input>
									                <script type="text/ng-template" id="tag1">
									                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
									                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
									                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
									                    </div>
									                </script>
									            </div>
									        </div>
									        <div class="form-group no-bordered">
									            <label class="control-label bolder">USER TAGS</label>
									            <div class="input-icon tag-suggestions">
									                <i class="ficon-price-tag"></i>
									                <tags-input
									                    ng-model="userPostDetail.UserTags.User_ReaderTag"
									                    display-property="Name"
									                    on-tag-added="addMemberTagsPopup('USER', $tag, popupActivityData.ModuleEntityGUID, 3)"
									                    on-tag-removed="removeMemberTagsPopup('USER', $tag, popupActivityData.ModuleEntityGUID, 3)"
									                    placeholder="Add user type"
									                    replace-spaces-with-dashes="false" 
									                    add-from-autocomplete-only="true"
									                    template="tag7">
									                    <auto-complete source="loadMemberTags($query, popupActivityData.ModuleEntityID, 3, 'USER', 1)" load-on-focus="true" min-length="0" max-results-to-show="25"></auto-complete>
									                </tags-input>
									                <script type="text/ng-template" id="tag7">
									                    <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
									                    <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
									                    <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
									                    </div>
									                </script>
									            </div>

												<!-- <div class="input-icon">
													<i class="ficon-profession"></i>
													<tags-input 
														ng-model="userPostDetail.UserTags.UserProfession"
														display-property="Name"
														on-tag-added="addMemberTagsPopup('PROFESSION', $tag, popupActivityData.ModuleEntityGUID, 3)"
														on-tag-removed="removeMemberTagsPopup('PROFESSION', $tag, popupActivityData.ModuleEntityGUID, 3)"
														placeholder="Add more profession"                    
														replace-spaces-with-dashes="false" 
														add-from-autocomplete-only="true"
														template="tag5">
														<auto-complete source="loadMemberTags($query, popupActivityData.ModuleEntityID, 3, 'PROFESSION', 1)" load-on-focus="true" min-length="0"></auto-complete>
													</tags-input>
													<script type="text/ng-template" id="tag5">
														<div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
														<span ng-if="data.Name != ''" ng-bind="data.Name"></span>
														<a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
														</div>
													</script>
												</div>
												-->

									        </div>
											<div class="form-group no-bordered">
									            <label class="control-label bolder">Assign To</label>
									            <div class="form-group"> 
                                                    <select id="select_user" chosen title="Select Team Member" data-placeholder="Select Team Member" class="form-control" ng-options="users.UserID as users.Name for users in team_member_list" data-ng-model="TeamMember.ID" ng-change="assignTeamMember(popupActivityData.ActivityGUID)">
                                                       <option></option>
                                                    </select>
                                                </div>
									        </div>
			    						</div>
			    					</div>
			                	</div>
			                </div>
			            </div>
			        </div>
                </div>
            </div>
    	</div>

    	<div class="modal fade" id="total_views_popup" ng-cloak>
        	<div class="modal-dialog">
                <div class="modal-content">
                	<div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"><i class="icon-close" ng-click="close_total_views_popup();"></i></span></button>
                        <h4>Views (<span ng-bind="totalView"></span>)</h4>
                    </div>
                    <div class="modal-body">
                        <div class="popup-content no-padding">
                            <div class="row-flued">
                            	<div class="default-scroll scrollbar" when-scrolled="get_viewers_list();">
	                            	<ul class="listing-group suggest-list">
				                        <li ng-repeat="ld in viewersList" class="list-group-item">
				                            <div class="list-items-sm">
				                                <div class="list-inner">
				                                    <figure>
				                                        <a target="_self">
				                                            <img ng-if="ld.ProfilePicture !== ''" class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + ld.ProfilePicture}}" />
				                                            <img  ng-if="ld.ProfilePicture == ''"  class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + 'user_default.jpg'}}" />
				                                        </a>
				                                    </figure>
				                                    <div class="list-item-body">
				                                        <h4 class="list-heading-xs">
				                                            <a target="_self" class="name" ng-bind="ld.FirstName + ' ' + ld.LastName"></a>
				                                        </h4>
				                                        <div>
				                                            <small class="location" ng-if="ld.Locality.Name != ''" ng-bind="ld.Locality.Name"></small>
				                                        </div>
				                                    </div>
				                                </div>
				                            </div>
				                        </li>
				                    </ul>
                            	</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="ViewPageNo" value="1" />
        </div>

	</div>
</div>
<input type="hidden" id="LoggedInUserGUID" value="<?php echo $this->session->userdata('AdminLoginSessionKey') ?>" />
