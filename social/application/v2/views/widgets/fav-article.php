<div ng-if="SettingsData.m38=='1'" ng-cloak="" ng-init="get_fav_wiki();" ng-show="fav_article_list.length>0" >
    <div class="panel-heading">
        <h3 class="panel-title" ng-bind="lang.w_my_fav_articles"></h3>
    </div>
    <div class="panel-body  no-padding">
        <ul class="list-items-hovered list-items-borderd" ng-cloak>
            <li ng-repeat="article in fav_article_list">
                <a target="_self" class="text-black" ng-href="{{BaseURL+article.ActivityURL}}" ng-bind="slice_string(article.PostTitle,50)"></a></h4>
                <div ng-if="article.NoOfFollowers <= 1 " class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers +' Follower' "></div>
                <div ng-if="article.NoOfFollowers > 1 "class="text-sm-off semi-bold" ng-bind="article.NoOfFollowers +' Followers' "></div>                        
            </li>
        </ul>
    </div>
</div>
