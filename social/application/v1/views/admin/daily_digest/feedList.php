<div  infinite-scroll="getDailyDigest()" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">

    <div ng-if="((!activityDataListLoader && (requestObj.PageNo === 1)) || (activityDataList.length > 0))" id="adminActityFeed-{{activityData.activity_log_details.ID}}" class="row panel panel-primary"  ng-repeat="( activityIndex, activityData ) in activityDataList">
        <div class="col-xs-7" ng-init="select_daily_digest();">
            <div class="panel-body">
                <ul class="list-group list-group-thumb sm">
                    <li class="list-group-item">
                        <div class="list-group-body">
                            <div class="btn-toolbar btn-toolbar-right" style="width:32%">
                                    <label class="checkbox checkbox-inline checkbox-block">
                                        <input ng-checked="activityData.activity.DailyDigestID != 0" type="checkbox" ng-click="select_daily_digest()" value="{{activityData.activity.ActivityID}}" ng-model="activityData.activity.selectedDD" id="daily_chk_{{activityData.activity.ActivityID}}" class="daily_digest_checkbox">
                                        <span class="label"></span>
                                    </label>
                                    <span>Daily Digest</span>
                                
                                    <label class="checkbox checkbox-inline checkbox-block">
                                        <input ng-checked="activityData.activity.ShowImage == 1" type="checkbox" ng-click="toggle_publish_button(activityData.activity.ActivityID)" value="{{activityData.activity.ActivityID}}" id="show_image_chk_{{activityData.activity.ActivityID}}" class="show_image_checkbox">
                                        <span class="label"></span>
                                    </label>
                                    <span>Show Image</span>
                            </div>
                            <figure class="list-figure">

                                <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '18' && activityData.activity.ActivityTypeID !== 23 && activityData.activity.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + activityData.subject_user.UserProfileURL}}">
                                    <img ng-if="activityData.activity.EntityProfilePicture !== 'user_default.jpg'" err-name="{{activityData.activity.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + activityData.activity.EntityProfilePicture}}">
                                </a>
                                <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '3' && activityData.activity.ActivityTypeID !== '23' && activityData.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                    <img ng-if="activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{activityData.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/' + activityData.subject_user.ProfilePicture}}">
                                </a>
                                <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="(activityData.activity.ActivityTypeID == '23' || activityData.activity.ActivityTypeID == '24') && activityData.activity.ModuleID !== '18'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                    <img err-name="{{activityData.subject_user.UserName}}" ng-if="activityData.subject_user.ProfilePicture !== '' && activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/' + activityData.subject_user.ProfilePicture}}">
                                </a>

                                <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.activity.EntityGUID}}" ng-if="(activityData.activity.ActivityTypeID == 23 || activityData.activity.ActivityTypeID == 24) && activityData.activity.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + activityData.activity.EntityProfileURL}}">
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
                    <ng-include ng-if="((activityData.activity_log_details.ActivityTypeID != 23) && (activityData.activity_log_details.ActivityTypeID != 24) && (activityData.activity_log_details.ActivityTypeID != 25) && (activityData.activity_log_details.ActivityTypeID != 16))" src="partialPageUrl + '/dashboard/activityFeed/activityDailyDigestContent.html'"></ng-include>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-xs-5">
            <div class="panel-body">
                <label for="" class="label">Description </label>
                <div class="text-field">
                    <textarea ng-change="toggle_publish_button(activityData.activity.ActivityID)" style="height:75px" maxlength="200" data-req-maxlen="200" id="daily-description-{{activityData.activity.ActivityID}}" ng-model="activityData.activity.Description" placeholder="Description">{{activityData.activity.Description}}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div ng-if="activityDataListLoader" class="panel panel-primary">
        <div class="panel-body extra-block">
            <span class="loader text-lg" style="display:block;">&nbsp;</span>
        </div>
    </div>
    <div ng-if="activityTotalRecord==0" class="panel panel-primary">
        <div class="panel-body nodata-panel">
            <div class="nodata-text p-v-lg">
                <span class="nodata-media">
                    <p class="text-off ng-binding">No content shared yet </p>
                </div>
            </div>
    </div> 
    
</div>
