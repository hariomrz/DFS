<div ng-if="SettingsData.m38=='1'" ng-cloak class="panel m-b" ng-init="get_suggested_articles();">
    <div class="panel-heading p-heading">
        <h3 ng-bind="lang.forum_suggested_articles"></h3>
    </div>
    <div class="panel-body no-padding">        
        <div class="bx-slider-fluid" ng-class="(article_list.length=='1') ? 'single-slide' : '' ;" ng-cloak>
            <ul ng-cloak class="wiki-listing" data-uix-bxslider="mode: 'horizontal', pager:false, controls: true,minSlides: 1,maxSlides:4,slideWidth:322,infiniteLoop: true,hideControlOnEnd: false">
                <li class="col-md-3" ng-repeat="article in article_list" ng-cloak data-notify-when-repeat-finished>
                    <div class="wiki-listing-content">
                        <div class="wiki-header" style="background-image: url({{ImageServerPath+'upload/wall/220x220/'+article.Album[0].Media[0].ImageName}});"> 
                            <div class="default-wiki-banner" ng-if="!article.Album[0].Media[0].ImageName" ng-cloak>
                                <span class="icon">
                                    <svg height="72px" width="72px" class="svg-icons no-hover">
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnKnowledge'}}"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="wiki-content">
                            <h2><a target="_self" ng-href="{{BaseURL+article.ActivityURL}}" ng-if="article.PostTitle" ng-bind="slice_string(article.PostTitle,50)"></a></h2>
                            <span class="time" ng-bind="date_format(article.CreatedDate)"></span>
                            <p ng-bind-html="slice_string(article.PostContent,150)"></p>
                        </div>
                        <div class="wiki-footer wiki-action">
                            <div class="feed-wiki-nav">
                                <ul class="feed-like-nav">
                                    <li class="iconlike active">
                                        <svg height="16px" width="16px" class="svg-icon">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconLike'}}"></use>
                                        </svg>
                                    </li>
                                    <li class="view-count" ng-bind="article.NoOfLikes"></li>
                                    <li>
                                        <svg height="18px" width="18px" class="svg-icon">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnComment'}}"></use>
                                        </svg>
                                    </li>
                                    <li class="view-count" ng-bind="article.NoOfComments"></li>
                                </ul>
                            
                                <button ng-click="subscribe_article(article.ActivityGUID);" class="btn btn-default btn-xs" ng-if="article.IsSubscribed!=='1'" ng-cloak ng-bind="lang.follow"></button>
                                <button ng-click="subscribe_article(article.ActivityGUID);" class="btn btn-primary btn-xs btn-text btn-text-lg following" ng-if="article.IsSubscribed=='1'" ng-cloak>
                                    <span class="icons no-hover">
                                        <svg height="10px" width="10px" class="svg-icons">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnCheck'}}"></use>
                                        </svg>
                                    </span><span class="text"><span ng-bind="lang.following"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>