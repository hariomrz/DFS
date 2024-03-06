<div infinite-scroll="getActivityList()" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">
    <div ng-if="((!activityDataListLoader && (requestObj.PageNo === 1)) || (activityDataList.length > 0))" id="adminActityFeed-{{activityData.activity_log_details.ID}}" class="panel panel-primary" ng-class="{ 'selected-feed' : ( currentActivityIndex === activityIndex ) }" ng-repeat="( activityIndex, activityData ) in activityDataList">
    <span data-ng-if="activityData.activity.IsPined == 1" class="sticky">                                        
                <i class="ficon-pin rotate-45"></i>
            </span>    
    <div class="panel-body">
            <ul class="list-group list-group-thumb sm">
                <li class="list-group-item">
                    <div class="list-group-body">
                        <div class="btn-toolbar btn-toolbar-right">
                           <!-- <a class="btn btn-xs btn-icn btn-default btn-mr" 
                               ng-class="(activityData.activity.IsPromoted == '0') ? 'promoted' : 'promote'"
                               ng-click="setPromotionStatus(activityData.activity)"
                               uib-tooltip="{{(activityData.activity.IsPromoted == '0') ? 'Promoted' : 'Promote'}}"
                               tooltip-append-to-body="true"
                            >
                                <span class="icn">
                                    <i class="ficon-promoted"></i>
                                </span>
                            </a>
                            
                            <a data-ng-if="activityData.activity.IsPined == 0" uib-tooltip="Pin to top"
                               tooltip-append-to-body="true" ng-click="pin_to_top(activityData.activity)" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span class="icn">
                                    <i class="ficon-out"></i>
                                </span>
                            </a>

                            <a data-ng-if="activityData.activity.IsPined == 1" uib-tooltip="Remove from Pin to top"
                               tooltip-append-to-body="true" ng-click="remove_pin_to_top(activityData.activity)" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span class="icn">
                                    <i class="ficon-out"></i>
                                </span>
                            </a>
-->                            

                            <a uib-tooltip="Delete Post"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID!=20" ng-click="delete_activity(activityData.activity.ActivityID, activityData.subject_user.UserID, 19)" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span class="icn">
                                    <i class="ficon-bin"></i>
                                </span>
                            </a>     

                <!--            <a uib-tooltip="Bump Up This Post"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID!=20" ng-click="bump_up(activityData.activity.ActivityGUID)" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span class="icn">
                                    <i class="ficon-arrow-long-up"></i>
                                </span>
                            </a>

-->

                            <a uib-tooltip="Delete Comment"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID==20" ng-click="delete_activity(activityData.comment_details.PostCommentID, activityData.subject_user.UserID, 20)" class="btn btn-xs btn-icn btn-default btn-mr">                                
                               <span class="icn">
                                    <i class="ficon-bin"></i>
                                </span>
                            </a>
<!--
                            <a uib-tooltip="Send Push Notification" tooltip-append-to-body="true" ng-click="open_activity_notification_popup(activityData.activity)" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span ng-class="activityData.activity.IsNotificationSent=='1'?'icn gfill':'icn'" >
                                    <i  class="ficon-email"></i>
                                </span>
                            </a>
-->
                            <!-- <a ng-if="activityData.activity.IsNotificationSent==1" uib-tooltip="Push Notification Sent"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default btn-mr">
                                <span class="icn">
                                    <i  class="ficon-send-mail"></i>
                                </span>
                            </a> -->
                            
                            <a uib-tooltip="Unverify Post"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID!=20 && activityData.activity.Verified != 0" class="btn btn-xs btn-icn btn-default btn-mr " ng-click="verify_activity(activityData.activity.ActivityID, activityData.subject_user.UserID, activityData.activity, 19)">
                                <span class="icn gfill"  ng-cloak>
                                    <i class="ficon-doubletick "></i>
                                </span>
                            </a>   
                            
                            <a uib-tooltip="Verify Post"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID!=20 && activityData.activity.Verified == 0" class="btn btn-xs btn-icn btn-default btn-mr " ng-click="verify_activity(activityData.activity.ActivityID, activityData.subject_user.UserID, activityData.activity, 19)">                                
                                <span class="icn " ng-cloak>
                                    <i class="ficon-doubletick"></i>
                                </span>
                            </a>


                            <a uib-tooltip="Unverify Comment"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID==20 && activityData.comment_details.Verified != 0" class="btn btn-xs btn-icn btn-default btn-mr " ng-click="verify_activity(activityData.comment_details.PostCommentID, activityData.subject_user.UserID, activityData.comment_details, 20)">
                                <span class="icn gfill"  ng-cloak>
                                    <i class="ficon-doubletick "></i>
                                </span>
                            </a>   
                            
                            <a uib-tooltip="Verify Comment"
                               tooltip-append-to-body="true" ng-if="activityData.activity_log_details.ActivityTypeID==20 && activityData.comment_details.Verified == 0" class="btn btn-xs btn-icn btn-default btn-mr " ng-click="verify_activity(activityData.comment_details.PostCommentID, activityData.subject_user.UserID, activityData.comment_details, 20)">                                
                                <span class="icn " ng-cloak>
                                    <i class="ficon-doubletick"></i>
                                </span>
                            </a>

                        <!--    <a uib-tooltip="Copy Post ID"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default btn-mr" ng-click="copy_activity_guid(activityData.activity.ActivityGUID)" >
                               <span class="icn " ng-cloak>
                                    <i class="ficon-copy"></i>
                                </span>
                            </a>                             

                            <a uib-tooltip="Show on Newsfeed"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default btn-mr" ng-if="activityData.activity.IsShowOnNewsFeed == 1"  ng-click="hide_activity(activityData.activity)" >
                               <span class="icn " ng-cloak>
                                    <i class="ficon-view-hide"></i>
                                </span>
                            </a>
                            <a uib-tooltip="Hide from Newsfeed"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default btn-mr" ng-if="activityData.activity.IsShowOnNewsFeed == 0" 
                                ng-click="hide_activity(activityData.activity)" >
                                <span class="icn " ng-cloak>
                                    <i class="ficon-eye"></i>
                                </span>
                            </a>
-->

                           
                            
                            <a uib-tooltip="View Details"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default btn-mr disableColor" ng-disabled="(currentActivityDataID == activityData.activity_log_details.ID)" ng-click="gotoActiveFeed(activityData.activity_log_details.ID, activityIndex);">
                                <span class="icn">
                                 <i class="ficon-getdetails"></i>
                                </span> 
                            </a>
                        </div>
                        <figure class="list-figure">

                            <a class="thumb-48 " entitytype="page" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '18' && activityData.activity.ActivityTypeID !== 23 && activityData.activity.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + activityData.subject_user.UserProfileURL}}">
                                <img ng-if="activityData.activity.EntityProfilePicture !== 'user_default.jpg'" err-name="{{activityData.activity.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + activityData.activity.EntityProfilePicture}}">
                            </a>
                            <a class="thumb-48 " entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '3' && activityData.activity.ActivityTypeID !== '23' && activityData.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                <img ng-if="activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{activityData.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/' + activityData.subject_user.ProfilePicture}}">
                            </a>
                            <a class="thumb-48 " entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="(activityData.activity.ActivityTypeID == '23' || activityData.activity.ActivityTypeID == '24') && activityData.activity.ModuleID !== '18'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                <img err-name="{{activityData.subject_user.UserName}}" ng-if="activityData.subject_user.ProfilePicture !== '' && activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + activityData.subject_user.ProfilePicture}}">
                            </a>

                            <a class="thumb-48 " entitytype="page" entityguid="{{activityData.activity.EntityGUID}}" ng-if="(activityData.activity.ActivityTypeID == 23 || activityData.activity.ActivityTypeID == 24) && activityData.activity.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + activityData.activity.EntityProfileURL}}">
                                <img ng-if="activityData.activity.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + activityData.activity.EntityProfilePicture}}">
                            </a>

                        </figure>
                        <div class="list-group-content" ng-init="activityTitleMessage='';">
                            <h6 class="list-group-item-heading" create-title-message parent-comment-id="activityData.parent_comment_details.PostCommentID" group-profile="activityData.group_profile" page-profile="activityData.page_profile" event-profile="activityData.event_profile" user-profile="activityData.user_profile" poll-data="activityData.PollData" activity-log-details="activityData.activity_log_details" subject-user="activityData.subject_user" activity-user="activityData.activity_user" parent-comment-user="activityData.parent_comment_user" activity="activityData.activity" parent-activity="activityData.parent_activity" parent-activity-user="activityData.parent_activity_user" activity-title-message="activityTitleMessage" activity-post-type="activityPostType">
                                    </h6>
                            <ul class="list-activites">
                                <li ng-if="activityData.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(utc_to_time_zone(activityData.comment_details.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                <li ng-if="activityData.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(utc_to_time_zone(activityData.activity.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                            </ul>
                        </div>
                    </div>
                <ng-include ng-if="((activityData.activity_log_details.ActivityTypeID != 23) && (activityData.activity_log_details.ActivityTypeID != 24) && (activityData.activity_log_details.ActivityTypeID != 25) && (activityData.activity_log_details.ActivityTypeID != 16))" src="partialPageUrl + '/dashboard/activityFeed/activityContent.html'"></ng-include>
                <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 16)" src="partialPageUrl + '/dashboard/activityFeed/ratingReview.html'"></ng-include>
                <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 25)" src="partialPageUrl + '/dashboard/activityFeed/pollCreated.html'"></ng-include>
                <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 23 || activityData.activity_log_details.ActivityTypeID == 24)" src="partialPageUrl + '/dashboard/activityFeed/activityContentPersona.html'"></ng-include>
                <ng-include ng-if="( sharedActivityPostType[activityData.activity_log_details.ActivityTypeID] && ( activityData.activity_log_details.ActivityTypeID != 14 ) && ( activityData.activity_log_details.ActivityTypeID != 15 ) )" src="partialPageUrl + '/dashboard/activityFeed/blockquoteContent.html'"></ng-include>
                </li>
            </ul>
        </div>
    </div>
    <div ng-if="activityDataListLoader" class="panel panel-primary">
        <div class="panel-body extra-block">
            <span class="loader text-lg" style="display:block;">&nbsp;</span>
        </div>
    </div>
    <div ng-if="activityDataList.length == 0" class="panel panel-primary">
        <div class="panel-body nodata-panel">
            <div class="nodata-text p-v-lg">
                <span class="nodata-media">                    
                    <p class="text-off ng-binding">No content shared yet!</p>
                </div>
            </div>
    </div> 
</div>

 <div class="popup animated" id="edit_title_popup" style="width: 100%;">
    <div class="popup-title">
        <span>Edit Title</span>
        <i class="icon-close" ng-click="close_edit_title_popup();">&nbsp;</i>
    </div>
    <div class="popup-content">
        <div class="modal-body has-padding">
            <!-- <div class="post-type-title">
                <a ng-click="open_activity_details_popup(questionData);" class="a-link">(Revert to original)</a>
            </div> -->
            <!-- <div class="pull-right">
                <div class="post-type-title">
                    <a ng-click="open_activity_details_popup(questionData);" class="a-link">(Revert to original)</a>
                </div>
            </div> -->
            <div class="row">
                <div class="col-xs-6">
                    <span>Original Text</span>
                    <div class="text-field" style="min-width: 250px;">
                        <textarea style="min-height: 150px;" id="current_post_title" name="current_post_title" rows="4" type="text" ng-model="currentActivityTitle" disabled></textarea>
                    </div>
                </div>
                <div class="col-xs-6">
                    <span>Edited Text</span>
                    <a ng-click="revert_activity_title();" class="a-link">(Revert to original)</a>
                    <div class="text-field" style="min-width: 250px;">
                        <textarea style="min-height: 150px;" maxlength="40" id="updated_post_title" name="updated_post_title" rows="4" type="text" ng-model="updatedActivityTitle"></textarea>
                    </div>
                </div>
                
            </div>
        </div>
        <div style="text-align: center;">
            <button type="button" class="btn btn-primary" ng-click="update_activity_title();">Submit</button>
        </div>
    </div>
</div>
<form id="wallpostform" ng-cloak method="post" ng-cloak>
<div class="modal fade" id="edit_post_content_admin" data-backdrop="static" data-keyboard="false" ng-cloak>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close" ng-click="close_edit_post_content();"></i></span></button>
                <h4>Edit Text</h4>
            </div>
            <div class="modal-body">
                <div class="popup-content no-padding">
                    <div class="row-flued">
                        <div class="col-xs-4">
                            <label for="edit_post_text"><h5>Original Text</h5></label>
                            <div class="rectangle-box-noti">
                                <!-- <input type="text" class="form-control" name="noti_header" ng-bind-html="textToLink(editActivityData.PostContent)" placeholder="Header" disabled /> -->
                                <p ng-if="editActivityData_modal.OriginalContent" class="list-group-item-text" ng-bind-html="textToLink(editActivityData_modal.OriginalContent)"></p>
                            </div>
                            <!-- <textarea style="min-height: 85px;" id="edit_post_text" name="edit_post_text" rows="2" type="text" placeholder="body" disabled ng-bind-html="textToLink(editActivityData_modal.OriginalContent)"></textarea> -->
                        </div>
                        <div class="col-xs-8">
                            <label for="edit_post_text"><h5>Edited Text</h5></label>
                            <a style="float: right" href="javascript:void(0)" ng-click="keep_original_content();">(Revert to original)</a>

                            <div id="postEditor" class="textarea">
                                <summernote ng-cloak ng-model="editActivityData_modal.EditPostContent" on-init="summernoteDropdown();" data-posttype="Post" on-paste="parseLinkDataWithDelay(evt,1)" on-focus="parseLinkData(evt,0)" on-keyup="parseLinkData(evt,0); parseTaggedInfo(contents);onSummerNoteChange(evt);" id="PostContent" config="options" on-image-upload="imageUpload(files)"></summernote>
                                
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button ng-disabled="updateActivityDataListLoader" type="button" class="btn btn-primary" ng-click="update_post_content();">Submit</button>
            </div>
        </div>
    </div>
</div>
</form>

<div class="modal fade" id="send_activity_notification_popup" ng-cloak>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon-close" ng-click="close_activity_notification_popup();"></i></span></button>
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
                                                <input type="text" class="form-control" name="AgeStart" ng-model="userListReqData_default.AgeStart" ng-change="getNotiUsersList();"  age-validate />
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
                                    <input type="checkbox" name="low" ng-model="userListReqData_default.Income.low" ng-change="getNotiUsersList();">
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
                                            on-tag-added="addMemberTagsNotiPopup('USER', $tag, popupContent.ModuleEntityGUID, 3)"
                                            on-tag-removed="removeMemberTagsNotiPopup('USER', $tag, popupContent.ModuleEntityGUID, 3)"
                                            placeholder="Add user type" 
                                            add-from-autocomplete-only="true"
                                            template="tag7">
                                            <auto-complete source="loadMemberTagsNotiPopup($query, popupContent.ModuleEntityID, 3, 'USER', 1)" load-on-focus="true" min-length="0"></auto-complete>
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
                            <!--<div class="form-group flx-rw">
                                <label class="control-label bolder">PROFESSION TAGS</label>
                                <div class="input-icon col-sm-6">
                                    <i class="ficon-profession"></i>
                                    <tags-input
                                        ng-model="QUE_reqData_default.TagTagType"
                                        display-property="Name"
                                        on-tag-added="addMemberTagsNotiPopup('PROFESSION', $tag, popupContent.ModuleEntityGUID, 3)"
                                        on-tag-removed="removeMemberTagsNotiPopup('PROFESSION', $tag, popupContent.ModuleEntityGUID, 3)"
                                        placeholder="Add more profession" 
                                        add-from-autocomplete-only="true"
                                        template="tag5">
                                        <auto-complete source="loadMemberTagsNotiPopup($query, popupContent.ModuleEntityID, 3, 'PROFESSION', 1)" load-on-focus="true" min-length="0"></auto-complete>
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

<div class="modal fade" id="view_similar_posts_popup" data-backdrop="static" data-keyboard="false" ng-cloak>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" ng-click="close_similar_posts_popup();">
                    <span aria-hidden="true"><i class="icon-close"></i></span></button>
                <h4>Similar posts</h4>
            </div>
            <div class="modal-body">
              <div class="popup-content no-padding">
                <div class="row-flued">
                  <div class="row" style="border-bottom: 2px solid #DFDFDF; margin-bottom: 10px;">
                    <div class="col-xs-6">
                      <div class="form-group no-bordered">
                        <label class="control-label bolder">POST TAGS</label>    
                        <div class="input-icon">
                          <i class="ficon-price-tag"></i>
                          <tags-input
                              ng-model="similar_post_tags"
                              display-property="Name"
                              on-tag-added="addMemberTagsSP('ACTIVITY', $tag)"
                              on-tag-removed="removeMemberTagsSP('ACTIVITY', $tag)"
                              placeholder="Add more tags"
                              replace-spaces-with-dashes="false" 
                              add-from-autocomplete-only="false"
                              template="tag_simialar1">
                              <auto-complete source="loadMemberTagsSP($query, '', 0, 'ACTIVITY', 1)" load-on-focus="false" min-length="0"></auto-complete>
                          </tags-input>
                          <script type="text/ng-template" id="tag_simialar1">
                              <div class="tag-template" ng-class="data.AddedBy=='1'?'added-by-admin':''">
                              <span ng-if="data.Name != ''" ng-bind="data.Name"></span>
                              <a class="remove-button ng-binding ng-scope" ng-click="$removeTag()" ng-bind="::$$removeTagSymbol">×</a>
                              </div>
                          </script>
                        </div>
                      </div>
                    </div>
                    <div class="col-xs-2">
                      <div class="form-group">
                        <label class="control-label">From Date</label>
                        <div data-error="hasError" class="date-field">
                            <input type="text"
                                   ng-model="requestObjSimilarPosts.StartDate"
                                   placeholder="__ /__ /__"
                                   readonly
                                   ng-change="updateSimilarPostDate()"
                                   id="similarPostFilterDatepicker"
                                   init-filter-datepicker
                                    pickerType="from"
                                    fromid="similarPostFilterDatepicker"
                                    toid="similarPostFilterDatepicker2"
                                   class="form-control" />
                            <label id="errorFromDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="similarPostFilterDatepicker">
                                <i class="ficon-calendar"></i>
                            </label>
                        </div>
                      </div>
                    </div>
                    <div class="col-xs-2">
                      <div class="form-group">
                        <label class="control-label">To Date</label>
                        <div data-error="hasError" class="date-field">
                            <input type="text"
                                   ng-model="requestObjSimilarPosts.EndDate"
                                   placeholder="__ /__ /__"
                                   readonly
                                   ng-change="updateSimilarPostDate()"
                                   init-filter-datepicker
                                    pickerType="to" 
                                    id="similarPostFilterDatepicker2" 
                                    fromid="similarPostFilterDatepicker" 
                                    toid="similarPostFilterDatepicker2"
                                   class="form-control" />
                            <label id="errorToDate" class="error-block-overlay"></label>
                            <label class="iconDate" for="similarPostFilterDatepicker2">
                                <i class="ficon-calendar"></i>
                            </label>
                        </div>
                      </div>
                    </div>
                    <div class="col-xs-2">
                      <button style="margin-top: 22px;" type="button" ng-click="reset_similar_filters();" class="btn btn-default btn-sm" ng-disabled="!resetSimilarFilter">RESET</button>
                    </div>
                  </div>
                  <div class="default-scroll scrollbar panel-body user-sm-info" when-scrolled="get_similar_posts(similar_post_tags);">
                    <ul class="list-group list-group-thumb md">
                      <li ng-repeat="similarActivityData in similar_posts" class="list-group-item" style="padding: 5px;">
                        <div class="row">
                          <div class="col-xs-9">
                            <div class="panel-body">
                              <ul class="list-group list-group-thumb sm">
                                <li class="list-group-item">
                                  <div class="list-group-body">
                                    <figure class="list-figure">
                                      <a class="thumb-48 " entitytype="user" entityguid="{{similarActivityData.subject_user.UserGUID}}" ng-if="similarActivityData.activity.PostAsModuleID == '3' && similarActivityData.activity.ActivityTypeID !== '23' && similarActivityData.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + similarActivityData.subject_user.UserProfileURL}}">
                                          <img ng-if="similarActivityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{similarActivityData.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/' + similarActivityData.subject_user.ProfilePicture}}">
                                      </a>
                                      <a class="thumb-48 " entitytype="user" entityguid="{{similarActivityData.subject_user.UserGUID}}" ng-if="(similarActivityData.activity.ActivityTypeID == '23' || similarActivityData.activity.ActivityTypeID == '24') && similarActivityData.activity.ModuleID !== '18'" ng-href="{{baseUrl + similarActivityData.subject_user.UserProfileURL}}">
                                          <img err-name="{{similarActivityData.subject_user.UserName}}" ng-if="similarActivityData.subject_user.ProfilePicture !== '' && similarActivityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + similarActivityData.subject_user.ProfilePicture}}">
                                      </a>
                                    </figure>
                                    <div class="list-group-content" ng-init="activityTitleMessage='';">
                                      <h6 class="list-group-item-heading" create-title-message parent-comment-id="similarActivityData.parent_comment_details.PostCommentID" group-profile="similarActivityData.group_profile" page-profile="similarActivityData.page_profile" event-profile="similarActivityData.event_profile" user-profile="similarActivityData.user_profile" poll-data="similarActivityData.PollData" activity-log-details="similarActivityData.activity_log_details" subject-user="similarActivityData.subject_user" activity-user="similarActivityData.activity_user" parent-comment-user="similarActivityData.parent_comment_user" activity="similarActivityData.activity" parent-activity="similarActivityData.parent_activity" parent-activity-user="similarActivityData.parent_activity_user" activity-title-message="activityTitleMessage" activity-post-type="activityPostType">
                                      </h6>
                                      <ul class="list-activites">
                                        <li ng-if="similarActivityData.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(utc_to_time_zone(similarActivityData.comment_details.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                        <li ng-if="similarActivityData.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(utc_to_time_zone(similarActivityData.activity.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                      </ul>
                                    </div>
                                  </div>
                                  <ng-include ng-if="((similarActivityData.activity_log_details.ActivityTypeID != 23) && (similarActivityData.activity_log_details.ActivityTypeID != 24) && (similarActivityData.activity_log_details.ActivityTypeID != 25) && (similarActivityData.activity_log_details.ActivityTypeID != 16))" src="partialPageUrl + '/dashboard/simalarFeed/activityContent.html'"></ng-include>
                                  <ng-include ng-if="(similarActivityData.activity_log_details.ActivityTypeID == 16)" src="partialPageUrl + '/dashboard/simalarFeed/ratingReview.html'"></ng-include>
                                  <ng-include ng-if="(similarActivityData.activity_log_details.ActivityTypeID == 25)" src="partialPageUrl + '/dashboard/simalarFeed/pollCreated.html'"></ng-include>
                                  <ng-include ng-if="(similarActivityData.activity_log_details.ActivityTypeID == 23 || similarActivityData.activity_log_details.ActivityTypeID == 24)" src="partialPageUrl + '/dashboard/simalarFeed/activityContentPersona.html'"></ng-include>
                                  <ng-include ng-if="( sharedActivityPostType[similarActivityData.activity_log_details.ActivityTypeID] && ( similarActivityData.activity_log_details.ActivityTypeID != 14 ) && ( similarActivityData.activity_log_details.ActivityTypeID != 15 ) )" src="partialPageUrl + '/dashboard/simalarFeed/blockquoteContent.html'"></ng-include>
                                </li>
                              </ul>
                            </div>
                          </div>
                          <div class="col-xs-3">
                            <!-- <div>
                              <label class="checkbox">
                                <input ng-checked="ld.is_similar == 1" ng-click="mark_similar_post(ld.activity.ActivityGUID)" id="comment_0_{{ld.activity.ActivityGUID}}" type="checkbox" class="check-content-filter">
                                <span class="label bold">None</span>
                              </label>
                            </div> -->
                            <button type="button" ng-click="mark_similar_post(similarActivityData.activity.ActivityGUID);similarActivityData.activity.is_similar=1;" class="btn btn-sm" ng-class="(similarActivityData.activity.is_similar == '0') ? 'btn-default' : 'btn-primary'" ng-disabled="similarActivityData.activity.is_similar">Mark as similar</button>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div ng-if="getSimilarPostsProcessing" class="panel panel-primary">
                    <div class="panel-body extra-block">
                      <span class="loader text-lg" style="display:block;">&nbsp;</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>

