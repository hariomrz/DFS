<div class="page-content">
    <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->
    <div class="content container">
        <div class="row mlr-8">
            <div class="col-md-12">
                <div class="grid simple" ng-controller="UserProfileCtrl" ng-init="fetchDetails('load')" id="UserProfileCtrl">
                    <?php $this->load->view('include/inner-navigation'); ?>
                    <div class="grid-body no-border">
                        <form id="allcontrolform" class="tab-content m-b-0">
                            <div class="row profile-detail-wrap">
                                <div class="col-md-2">
                                    <!-- Profile Thumbnail -->
                                    <div class="user-profile-wrap">
                                        <div id="current-picture">
                                            <input type="hidden" name="profile_media" value="{{ProfilePicture}}"/>
                                            <img ng-src="{{imgsrc}}" > <i class="del-ico" onclick="removeThisMedia(this);"><?php echo lang('remove') ?></i>
                                        </div>
                                        <div class="custom-table" id="uploadprofilepic" style="display:none;">
                                            <aside class="custom-table-row">
                                                <aside class="custom-table-cell"> <i class="icn-picture" id="profile-picture"></i> </aside>
                                                <img id="loader" src="<?php echo ASSET_BASE_URL ?>img/circle-loader.GIF" style="display:none;width: 21px;margin: 18px 0 0 0;" /> </aside>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <!-- Profile Details -->
                                    <div class="overflow profile-detail">
                                        <div class="Editable" style="display:none">
                                            <div class="info border-radius3">
                                                <div class="textarea-box">
                                                    <uix-textarea rows="3" maxlength="200" maxcount="200" name="aboutme" class="form-control user-status-box post-input" tabindex="1" data-ng-model="editAbove" id="prifiledescription" placeholder="<?php echo lang('write_description') ?>"></uix-textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="Editable m-t-20 m-b-15" id="removediv" style="display:none">
                                            <h3 class="semi-bold"><?php echo lang('expertise') ?></h3>
                                            <input tabindex="2" type="hidden" name="tag[]" id="removeinput" ng-value="exp.Expertise" class="form-control input-lg tag" ng-repeat="exp in Expertise ">
                                        </div>
                                        <div class="NonEditable">
                                            <h3 ng-if="status!=''" class="semi-bold"><?php echo lang('about_me') ?></h3>
                                            <p ng-if="status!=''" ng-bind="status"></p>
                                        </div>
                                        <div class="NonEditable" ng-if="ttl>0">
                                            <h3 ng-if="status!=''" class="semi-bold"><?php echo lang('expertise') ?></h3>
                                            <a ng-if="status!=''" href="#" class="hashtags m-b-5 m-r-5" ng-repeat="exp in Expertise" ng-bind="exp.Expertise"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row post profile-post-block">
                                <div class="col-md-6">
                                    <ul class="action-bar">
                                        <li class="m-b-5"><a href="javascript:void(0);" class="color-lightgray"><i class="fa icn-connections m-r-5"></i> {{records}} <?php echo lang('connections') ?></a> </li>
                                        <li>
                                            <a href="javascript:void(0);" class="color-lightgray"> <i class="fa icn-complete  m-r-5"></i>{{Percent}}%
                                                <?php echo lang( 'complete') ?>
                                            </a>
                                        </li>

                                        <!-- alertify.message('Sample').dismissOthers();  -->

                                    </ul>
                                </div>
                                <div class="col-md-6">
                                <?php if($this->session->userdata('UserID')==$UserID){ ?>
                                    <button type="button" class="btn btn-orange btn-small pull-right" id="follow_button" style="display:none;"><span class="bold"><?php echo lang('follow') ?></span>
                                    </button>
                                    <button type="button" class="btn btn-orange btn-small pull-right m-r-10 " id="wallEdit" ng-click="fetchDetails('edit')"><span class="bold"><?php echo lang('edit') ?></span>
                                    </button>
                                    <button style='display: none;' type="button" class="btn btn-orange btn-small pull-right m-r-10 " id="wallSave" ng-click="fetchDetails('save')"><span class="bold"><?php echo lang('save') ?></span>
                                    </button>
                                <?php } else { ?>
                                    <span ng-controller="UserListCtrl" class="pull-right" ng-init="getProfileUser()">
                                        <button type="button" data-toggle="modal" data-target="#reportAbuse" class="btn btn-orange btn-small"><span class="bold"><?php echo lang('report_abuse') ?></span></button>

                                        <button type="button" ng-if="profileUser.ShowFollowBtn==1" class="btn btn-orange btn-small" id="followmem{{profileUser.UserID}}" ng-click="follow(profileUser.UserID)"><span class="bold">{{profileUser.follow}}</span></button>

                                        <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='1' && profileUser.ShowFriendsBtn=='1'" ng-click="removeFriend(profileUser.UserID)"><span class="bold"><?php echo lang('delete_request') ?></span></button>

                                        <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='2' && profileUser.ShowFriendsBtn=='1'" ng-click="rejectRequest(profileUser.UserID)"><span class="bold"><?php echo lang('cancel_request') ?></span></button>

                                        <button type="button" class="btn btn-orange btn-small btn-reject" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='3'"  ng-click="rejectRequest(profileUser.UserID)"><span class="bold"><?php echo lang('deny') ?></span></button>

                                        <button type="button" class="btn btn-orange btn-small" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='3'"  ng-click="acceptRequest(profileUser.UserID)"><span class="bold"><?php echo lang('accept') ?></span></button>
                                          &nbsp;
                                        <button type="button" class="btn btn-orange btn-small w140" lang="{{profileUser.UserID}}" ng-if="profileUser.FriendStatus=='4' && profileUser.ShowFriendsBtn=='1'" ng-click="sendRequest(profileUser.UserID)"><span class="bold"><?php echo lang('send_request') ?></span></button>
                                    </span>
                                <?php } ?>
                                </div>
                            </div>
                        </form>
                    </div>


                    <!-- Report Abuse Desc Starts -->
                    <div class="modal fade" id="reportAbuse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                      
                       <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" onclick="$('#commonErrorModal').html('')" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4><?php echo lang('report_abuse');?></h4>
                          </div>
                          <div class="modal-body without-footer" id="ReportAbuse">
                              <div class="form-group">
                                    <label>Group Description</label>
                                     <div class="textarea-field">
                                        <textarea id="reportAbuseDesc"></textarea> 
                                   </div>
                              </div>
                          </div>
                          <div class="modal-footer">
                          		<button ng-click="reportAbuse(<?php echo $UserID ?>)" class="btn btn-primary pull-right">Submit</button>
                          </div>
                         
                        </div>
                        </div>
                       
                    </div>
                    <!-- Report Abuse Desc Ends -->

                </div>
            </div>

            <!-- System Decision Alert Popup-->
            <div class="modal fade" id="systemDicisionAlert" lang="" tabindex="-1" role="dialog" data-aria-labelledby="myModalLabel" aria-hidden="true">
                
                  <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                            <h3><?php echo lang('confirm') ?>?</h3>
                        </div>
                        <div class="modal-body padding-20 without-footer overflow">
                            <p class="font16">
                                <?php echo lang( 'delete_post_msg') ?>
                            </p>
                            <div class="popup-double-button p-t-10">
                                <button type="button" class="btn btn-success" onclick="dodelete()">
                                    <?php echo lang( 'yes') ?>
                                </button>
                                <button type="button" class="btn" onclick="donothing()">
                                    <?php echo lang( 'no') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                   </div>
                
            </div>
            <!-- /System Decision Alert Popup-->
            
        </div>
        <div class="row">
        	<div class="col-md-8" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="GetWallPostInit()">
				<?php $this->load->view('wall/wall'); ?>
        	</div>
        	<div class="col-md-4">
                <section class="panel" ng-controller="UserListCtrl" ng-init="getFriends(8)">
                <article class="panel panel-default sap" ng-if="noOfObj>0">
                    <div class="panel-heading">
                        <a href="<?php echo site_url('users/friends'); ?>" ng-if="noOfObj>8" class="viewall">View All</a>
                        <h3 class="panel-title"><a><?php echo lang('friends') ?></a> - <span class="muted">{{noOfObj}}</span></h3>
                    </div>
                    <ul ng-if="noOfObj>0" class="list-group panel-user-list">
                        <li class="list-group-item" ng-repeat="friend in friends">
                            <a class="thumb"><img src="{{friend.profilepic}}" class="img-circle" /></a>
                            <div class="overflow">
                                <a href="{{friend.profileLink}}">{{friend.FirstName + ' ' + friend.LastName}}</a>
                            </div>
                        </li>
                    </ul>
                </article>
            </section>

            <section class="panel" ng-controller="UserProfileCtrl" ng-init="callfollowers()">
                <article class="panel panel-default sap" ng-if="fnoOfObj>0">
                    <div class="panel-heading">
                        <a href="javascript:void(0);" ng-if="fnoOfObj>8" class="viewall">View All</a>
                        <h3 class="panel-title"><a><?php echo lang('followers') ?></a> - <span class="muted">{{fnoOfObj}}</span></h3>
                    </div>
                    <ul ng-if="fnoOfObj>0" class="list-group panel-user-list">
                        <li class="list-group-item" ng-repeat="member in followers">
                            <a class="thumb"><img src="{{member.profilePicture}}" class="img-circle" /></a>
                            <div class="overflow vcenter">
                                <a href="{{member.profileLink}}">{{member.FirstName + ' ' + member.LastName}}</a>
                            </div>
                        </li>
                    </ul>
                </article>
            </section>

            <section class="panel" ng-controller="UserProfileCtrl" ng-init="callfollowing()">
                <article class="panel panel-default sap" ng-if="noOfObj>0">
                    <div ng-if="noOfObj>0" class="panel-heading">
                        <a href="javascript:void(0);" ng-if="noOfObj>8" class="viewall">View All</a>
                        <h3 class="panel-title"><a><?php echo lang('following') ?></a> - <span class="muted">{{noOfObj}}</span></h3>
                    </div>
                    <ul ng-if="noOfObj>0" class="list-group panel-user-list">
                        <li class="list-group-item" ng-repeat="member in connection">
                            <a class="thumb"><img src="{{member.profilePicture}}" class="img-circle" /></a>
                            <div class="overflow vcenter">
                                <a href="{{member.profileLink}}">{{member.FirstName + ' ' + member.LastName}}</a>
                            </div>
                        </li>
                    </ul>
                </article>
            </section>
                
        	</div>
        </div>
    </div>


    <input type="hidden" id="WallPageNo" value="1" />
    <input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />