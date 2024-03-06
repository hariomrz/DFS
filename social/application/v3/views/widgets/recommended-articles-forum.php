<div ng-if="SettingsData.m38=='1'" class="panel panel-widget" ng-init="get_trending_widgets();" ng-show="recommended_article_list.length > 0" ng-cloak>
     <div class="panel-heading ">
            <h3 class="panel-title" ng-bind="lang.w_recommended_articles"></h3>
    </div>
    <div class="panel-body  no-padding">
        <ul class="list-items-hovered list-items-borderd" ng-cloak>
            <li ng-repeat="article in recommended_article_list" ng-if="article.PostTitle != ''">
                <a target="_self" ng-href="{{BaseURL + article.ActivityURL}}" ng-bind="slice_string(article.PostTitle,50)" class="text-black" ></a>
                <div ng-if="article.NoOfFollowers <= 1 " class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers+' Follower'"></div>
                <div ng-if="article.NoOfFollowers > 1 " class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers+' Followers'"></div>
            </li>
        </ul>
    </div>    
</div>
