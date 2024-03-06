<div class="tab-pane fade" ng-if="showActivity" id="Activities">
    <div id="DashboardFeedController" ng-controller="DashboardFeedController" ng-init="getActivityList(userPersonaDetail.UserID, personaActivityObj);">
        <div>
            <div ng-if="((!activityDataListLoader && (requestObj.PageNo === 1)) || (activityDataList.length > 0))" id="adminActityFeed-{{activityData.activity_log_details.ID}}" class="activity-listing clearfix" ng-repeat="( activityIndex, activityData ) in activityDataList">
                 
                    <ul class="list-group list-group-thumb sm">
                        <li class="list-group-item">
                            <div class="list-group-body">
                                <figure class="list-figure">
                                    <!-- Default icons  -->
                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==5 || activityData.activity_log_details.ActivityTypeID==40 || activityData.activity_log_details.ActivityTypeID==6 || activityData.activity_log_details.ActivityTypeID==13 || activityData.activity_log_details.ActivityTypeID==23 || activityData.activity_log_details.ActivityTypeID==24" class="ficon-imageicn"></i>

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==25 || activityData.activity_log_details.ActivityTypeID==33" class="ficon-poll"></i>

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==16 || activityData.activity_log_details.ActivityTypeID==1 || activityData.activity_log_details.ActivityTypeID==7 || activityData.activity_log_details.ActivityTypeID==8 || activityData.activity_log_details.ActivityTypeID==11 || activityData.activity_log_details.ActivityTypeID==12 || activityData.activity_log_details.ActivityTypeID==20 || activityData.activity_log_details.ActivityTypeID==26" class="ficon-comment"></i>

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==9 || activityData.activity_log_details.ActivityTypeID==10 || activityData.activity_log_details.ActivityTypeID==14 || activityData.activity_log_details.ActivityTypeID==15" class="ficon-share"></i>

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==19" class="ficon-heart"></i>

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==27" class="ficon-search"></i>

                                    <!-- <i class="ficon-file-empty"></i> -->

                                    <i ng-if="activityData.activity_log_details.ActivityTypeID==21 || activityData.activity_log_details.ActivityTypeID==22" class="ficon-views"></i> 
                                </figure>
                                <i ng-hide="activityData.activity_log_details.ActivityTypeID==27 || activityData.activity_log_details.ActivityTypeID==21" class="ficon-arrow-down acc-arrow collapsed"  data-toggle="collapse" data-target="#acc{{$index}}"></i>
                                <div class="list-group-content" ng-init="activityTitleMessage">
                                    <h6 class="list-group-item-heading" create-title-message-persona parent-comment-id="activityData.parent_comment_details.PostCommentID" group-profile="activityData.group_profile" page-profile="activityData.page_profile" event-profile="activityData.event_profile" user-profile="activityData.user_profile" poll-data="activityData.PollData" activity-log-details="activityData.activity_log_details" subject-user="activityData.subject_user" activity-user="activityData.activity_user" parent-comment-user="activityData.parent_comment_user" activity="activityData.activity" parent-activity="activityData.parent_activity" parent-activity-user="activityData.parent_activity_user" activity-title-message="activityTitleMessage" activity-post-type="activityPostType">
                                    </h6>
                                    <ul class="list-activites">
                                        <li ng-bind="createDateObject(activityData.activity.CreatedDate) | date : 'dd MMM \'at\' hh:mm a'"></li>
                                    </ul>
                                </div> 
                            </div>
                        <div  class="collapse" id="acc{{$index}}">
                            <ng-include ng-if="((activityData.activity_log_details.ActivityTypeID != 19) && (activityData.activity_log_details.ActivityTypeID != 40) && (activityData.activity_log_details.ActivityTypeID != 25) && (activityData.activity_log_details.ActivityTypeID != 16) && (activityData.activity_log_details.ActivityTypeID!='21') && (activityData.activity_log_details.ActivityTypeID!='27') && (activityData.activity_log_details.ActivityTypeID!='33') && (activityData.activity_log_details.ActivityTypeID!='23') && (activityData.activity_log_details.ActivityTypeID!='24'))" src="partialPageUrl + '/dashboard/activityFeed/activityContent.html'"></ng-include>
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 16)" src="partialPageUrl + '/dashboard/activityFeed/ratingReview.html'"></ng-include>
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 25)" src="partialPageUrl + '/dashboard/activityFeed/pollCreated.html'"></ng-include>
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 33)" src="partialPageUrl + '/dashboard/activityFeed/pollVoted.html'"></ng-include>
                            
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 23 || activityData.activity_log_details.ActivityTypeID == 24)" src="partialPageUrl + '/dashboard/activityFeed/activityContentPersona.html'"></ng-include>

                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 19 && activityData.activity_log_details.ModuleID == 20)" src="partialPageUrl + '/dashboard/activityFeed/commentContentPersona.html'"></ng-include>
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 19 && activityData.activity_log_details.ModuleID == 19)" src="partialPageUrl + '/dashboard/activityFeed/activityContentPersona.html'"></ng-include>
                            <ng-include ng-if="(activityData.activity_log_details.ActivityTypeID == 40 && activityData.activity_log_details.ModuleID == 39)" src="partialPageUrl + '/dashboard/activityFeed/activityContentPersona.html'"></ng-include>
                            <ng-include ng-if="(sharedActivityPostType[activityData.activity_log_details.ActivityTypeID])" src="partialPageUrl + '/dashboard/activityFeed/blockquoteContentPersona.html'"></ng-include>
                        </div>   
                        </li>
                    </ul>
                 
            </div>
            <div ng-if="activityDataListLoader" class="bottom-loader">
                <div class="panel-body">
                    <span class="loader text-lg" style="display:block;">&nbsp;</span>
                </div>
            </div>
            <div ng-if="activityTotalRecord>activityDataList.length && show_load_more==1" class="bottom-loader">
                <div class="panel-body">
                    <button ng-click="getActivityList(userPersonaDetail.UserID)" class="btn btn-default btn-block">Load More</button>
                </div>
            </div>
        </div>
    </div>
</div>