<div ng-if="SettingsData.m38=='1'" class="panel panel-default" ng-cloak="" ng-init="get_trending_widgets();" ng-show="recommended_article_list.length > 0">
    <div class="panel-heading">
        <h3 class="panel-title" ng-bind="lang.w_recommended_articles"></h3>
    </div>
    <div class="panel-body no-padding">
        <ul class="list-items-hovered list-items-borderd" ng-cloak>
            <li ng-repeat="article in recommended_article_list">
                <a target="_self" class="text-black" ng-href="{{BaseURL+article.ActivityURL}}" ng-bind="slice_string(article.PostTitle,50)"></a>
                <div ng-if="article.NoOfFollowers <= 1 " class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers+' Follower'"></div>
                <div ng-if="article.NoOfFollowers > 1 " class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers+' Followers'"></div>                        
                    
            </li>
        </ul>
        </div>
</div>
