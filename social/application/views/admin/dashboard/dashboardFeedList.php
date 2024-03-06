<div infinite-scroll="getActivityList()" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">
    <div ng-if="((!activityDataListLoader && (requestObj.PageNo === 1)) || (activityDataList.length > 0))" id="adminActityFeed-{{activityData.activity_log_details.ID}}" class="panel panel-primary" ng-class="{ 'selected-feed' : ( currentActivityIndex === activityIndex ) }" ng-repeat="( activityIndex, activityData ) in activityDataList">
        <div class="panel-body">
            <ul class="list-group list-group-thumb sm">
                <li class="list-group-item">
                    <div class="list-group-body">
                        <div class="btn-toolbar btn-toolbar-right">
                            
                            <a uib-tooltip="Delete"
                               tooltip-append-to-body="true" ng-click="delete_activity(activityData.activity.ActivityID,activityData.subject_user.UserID)" class="btn btn-xs btn-icn btn-default">
                                <span class="icn">
                                    <i class="ficon-bin"></i>
                                </span>
                            </a>                             
                            <a class="btn btn-xs btn-default verify-btn" ng-click="verify_activity(activityData.activity.ActivityID,activityData.subject_user.UserID, activityData.activity)">
                                <span class="icn" ng-if="activityData.activity.Verified != 0" ng-cloak>
                                    <i class="ficon-check"></i>
                                </span>
                                <span class="text">Verify</span>
                            </a>
                            
                            
                            <a class="btn btn-xs btn-default verify-btn" ng-if="activityData.activity.IsShowOnNewsFeed == 1"  ng-click="hide_activity(activityData.activity)" >
                                <span class="text">Show on Newsfeed</span>
                            </a>
                            <a class="btn btn-xs btn-default verify-btn" ng-if="activityData.activity.IsShowOnNewsFeed == 0" 
                                ng-click="hide_activity(activityData.activity)" >
                                <span class="text">Hide from Newsfeed</span>
                            </a>
                            <a uib-tooltip="View Details"
                               tooltip-append-to-body="true" class="btn btn-xs btn-icn btn-default" ng-disabled="(currentActivityDataID == activityData.activity_log_details.ID)" ng-click="gotoActiveFeed(activityData.activity_log_details.ID, activityIndex);">
                                <span class="icn">
                                 <i class="ficon-getdetails"></i>
                                </span> 
                            </a>
                        </div>
                        <figure class="list-figure">

                            <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '18' && activityData.activity.ActivityTypeID !== 23 && activityData.activity.ActivityTypeID !== 24" ng-href="{{baseUrl + 'page/' + activityData.subject_user.UserProfileURL}}">
                                <img ng-if="activityData.activity.EntityProfilePicture !== 'user_default.jpg'" err-name="{{activityData.activity.EntityName}}"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.activity.EntityProfilePicture}}">
                            </a>
                            <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="activityData.activity.PostAsModuleID == '3' && activityData.activity.ActivityTypeID !== '23' && activityData.activity.ActivityTypeID !== '24'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                <img ng-if="activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" err-name="{{activityData.subject_user.UserName}}" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.subject_user.ProfilePicture}}">
                            </a>
                            <a class="thumb-48 loadbusinesscard" entitytype="user" entityguid="{{activityData.subject_user.UserGUID}}" ng-if="(activityData.activity.ActivityTypeID == '23' || activityData.activity.ActivityTypeID == '24') && activityData.activity.ModuleID !== '18'" ng-href="{{baseUrl + activityData.subject_user.UserProfileURL}}">
                                <img err-name="{{activityData.subject_user.UserName}}" ng-if="activityData.subject_user.ProfilePicture !== '' && activityData.subject_user.ProfilePicture !== 'user_default.jpg'"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.subject_user.ProfilePicture}}">
                            </a>

                            <a class="thumb-48 loadbusinesscard" entitytype="page" entityguid="{{activityData.activity.EntityGUID}}" ng-if="(activityData.activity.ActivityTypeID == 23 || activityData.activity.ActivityTypeID == 24) && activityData.activity.ModuleID == '18'" ng-href="{{baseUrl + 'page/' + activityData.activity.EntityProfileURL}}">
                                <img ng-if="activityData.activity.EntityProfilePicture !== ''"   class="img-circle" ng-src="{{imageServerPath + 'upload/profile/220x220/' + activityData.activity.EntityProfilePicture}}">
                            </a>

                        </figure>
                        <div class="list-group-content" ng-init="activityTitleMessage='';">
                            <h6 class="list-group-item-heading" create-title-message parent-comment-id="activityData.parent_comment_details.PostCommentID" group-profile="activityData.group_profile" page-profile="activityData.page_profile" event-profile="activityData.event_profile" user-profile="activityData.user_profile" poll-data="activityData.PollData" activity-log-details="activityData.activity_log_details" subject-user="activityData.subject_user" activity-user="activityData.activity_user" parent-comment-user="activityData.parent_comment_user" activity="activityData.activity" parent-activity="activityData.parent_activity" parent-activity-user="activityData.parent_activity_user" activity-title-message="activityTitleMessage" activity-post-type="activityPostType">
                                    </h6>
                            <ul class="list-activites">
                                <li ng-if="activityData.activity_log_details.ActivityTypeID=='20'" ng-bind="createDateObject(activityData.comment_details.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                <li ng-if="activityData.activity_log_details.ActivityTypeID!=='20'" ng-bind="createDateObject(activityData.activity.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
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
     
</div>
