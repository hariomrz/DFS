<div ng-cloak class="feed-title" ng-show="IsReminder==0 && popularData.length>0" ng-bind="lang.w_popular_stories"></div>
<div ng-show="IsReminder==0" ng-cloak class="popular-stories">
    <ul ng-if="activityData.length>0 && popularData.length>0" class="slider-nav">
        <li id="slidePrev" ng-click="getPopularLimit('Prev')">
            <span class="icon">
                 <i class="ficon-arrow-left-sml f-lg"></i>
            </span> 
        </li>
        <li id="slideNext" ng-click="getPopularLimit('Next')">
            <span class="icon">
                 <i class="ficon-arrow-left-sml f-lg"></i>
            </span> 
        </li>
        
    </ul>
    <div ng-cloak ng-if="activityData.length==0" ng-repeat="data in popularData" repeat-done="wallRepeatDone();" ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index;" viewport-watch class="news-feed-listing">
        <div class="inner-wall-post" ng-include="getTemplateUrl(data,1)" ></div>
    </div>
    <div ng-cloak ng-if="activityData.length>0" ng-repeat="data in popular_feeds_single" repeat-done="wallRepeatDone();" ng-init="SettingsFn(data.ActivityGUID); FeedIndex = $index;" viewport-watch class="news-feed-listing">
        <div class="inner-wall-post" ng-include="getTemplateUrl(data,1)" ></div>
    </div>
</div>