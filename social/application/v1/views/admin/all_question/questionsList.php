<div infinite-scroll="getQuestionsList();" infinite-scroll-distance="2" infinite-scroll-use-document-bottom="true" infinite-scroll-disabled="scroll_disable">
    <div ng-if="((!questionDataListLoader && (requestObj.PageNo === 1)) || (questionDataList.length > 0))" id="questionFeed-{{questionData.ActivityID}}" class="row panel panel-primary" ng-repeat="( questionIndex, questionData ) in questionDataList">
    <div class="col-xs-5">
        <div class="row">
            <div class="col-xs-1">
                <button style="transform: rotate(-90deg); position: absolute; left: 0px; top: 22px; contain: content;" type="button" class="btn btn-primary" ng-click="mark_question_ready(questionData.ActivityGUID);" ng-disabled="questionData.IsReady" ng-class="questionData.IsReady ? 'btn-bg-grey' : ''"><i class=""></i>Ready</button>
            </div>
            <div class="col-xs-11">
                <div class="feed-body">
                    <div class="feed-content">
                        <div class="post-type-title">
                            <a ng-bind="questionData.PostTitle" ng-click="open_activity_details_popup(questionData);" class="a-link"></a>
                        </div>
                    </div>
                </div>
                <div class="feed-header-block">
                    <span ng-bind="questionData.UserName"></span> on <span ng-cloak ng-bind="createDateObject(utc_to_time_zone(questionData.CreatedDate)) | date : 'dd MMM \'at\' hh:mm a'"></span>
                    <span ng-if="questionData.TeamMember.Name" > | Assigned: {{questionData.TeamMember.Name}}</span>
                </div>
            </div>
        </div>
    </div>
        <div class="col-xs-4 flx-rw">
            <div ng-if="questionData.VC != 0" class="square-box crs-ptr" ng-click="open_views_popup(questionData.ActivityGUID);">
                <span class="bold-18">{{questionData.VC}}</span>
                <p>View</p>
            </div>
            <div ng-if="questionData.VC == 0" class="square-box">
                <span class="bold-18">{{questionData.VC}}</span>
                <p>View</p>
            </div>
            <!-- <div class="square-box">
                <span class="bold-18">{{questionData.VC}}</span>
                <p>View</p>
            </div> -->
            <div ng-if="questionData.RC != 0" class="square-box crs-ptr" ng-click="open_responses_popup(questionData.ActivityGUID, questionData, 0);">
                <span class="bold-18">{{questionData.RC}}</span>
                <p>Response</p>
            </div>
            <div ng-if="questionData.SOC != 0" class="square-box crs-ptr" ng-click="open_responses_popup(questionData.ActivityGUID, questionData, 1);">
                <span class="bold-18">{{questionData.SOC}}</span>
                <p>Solution</p>
            </div>
            <div ng-if="questionData.RC == 0" class="square-box">
                <span class="bold-18">{{questionData.RC}}</span>
                <p>Response</p>
            </div>
            <div ng-if="questionData.SOC == 0" class="square-box">
                <span class="bold-18">{{questionData.SOC}}</span>
                <p>Solution</p>
            </div>
            <div class="square-box crs-ptr" ng-click="open_notification_popup(questionData);">
                <span class="bold-18">{{questionData.NC+questionData.SC}}</span>
                <p>Notification</p>
            </div>
        </div>
        <div class="col-xs-3 flx-rw">
            <div class="rectangle-box">
                <span class="bold-18">{{questionData.UQC}}</span>
                <p>Question asked</p>
            </div>
            <div class="rectangle-box">
                <span class="bold-18">{{questionData.URC}}</span>
                <p>Response posted</p>
            </div>
        </div>
    </div>


    <div ng-if="questionDataListLoader" class="panel panel-primary">
        <div class="panel-body extra-block">
            <span class="loader text-lg" style="display:block;">&nbsp;</span>
        </div>
    </div>
    <div ng-if="questionTotalRecord==0" class="panel panel-primary">
        <div class="panel-body nodata-panel">
            <div class="nodata-text p-v-lg">
                <span class="nodata-media">
                    <p class="text-off ng-binding">No content shared yet </p>
                </span>
            </div>
        </div>
    </div>
</div>
