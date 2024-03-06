<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getPageSearchList(Keyword,10,1)">
    <section class="news-feed" ng-cloak>
        <div class="feed-title" ng-if="PageTotalRecords > 0"><span ng-bind="PageTotalRecords"></span> <span ng-bind="(PageTotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul ng-if="PageTotalRecords>0" class="list-group thumb-68">
                    <li ng-repeat="Page in PageSearch" ng-cloak>
                        <figure> 
                            <a entitytype="page" entityguid="{{Page.PageGUID}}" target="_self" ng-href="{{BaseUrl+'page/'+Page.PageURL}}" >
                                <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+Page.ProfilePicture}}" class="img-circle"  >
                            </a> 
                        </figure>
                        <div class="description">
                            <button ng-if="Page.FollowStatus=='0'" class="btn btn-default btn-xs pull-right  m-t-5" ng-click="toggleFollowPage(Page.PageGUID,18,1,'search')">FOLLOW</button>
                            <button ng-if="Page.FollowStatus=='1'" class="btn btn-default btn-xs pull-right  m-t-5" ng-click="toggleFollowPage(Page.PageGUID,18,1,'search')">UNFOLLOW</button>
                            <a entitytype="page" entityguid="{{Page.PageGUID}}" target="_self" class="name" ng-href="{{BaseUrl+'page/'+Page.PageURL}}" ng-bind="Page.Title"></a>
                            <ul class="sub-nav-listing">
                                <li>
                                    <ul class="activity-nav">
                                        <li>
                                            <i class="icon"><svg width="11px" height="11px" class="svg-icons">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#multiUserIcon'}}"></use>
                                            </svg></i>
                                            <a ng-if="Page.Friends.length>0" ng-bind="page_friends_label(Page.Friends)"></a>
                                        </li>
                                        <li><span class="gray-clr">Active :</span> <span ng-bind="Page.Popularity"></span></li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="12px" height="12px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnAccountGroup'}}"></use>
                                            </svg>
                                        </i> 
                                        <span ng-if="Page.NoOfFollowers==1" ng-bind="Page.NoOfFollowers+' Follower'"></span>
                                        <span ng-if="Page.NoOfFollowers>1" ng-bind="Page.NoOfFollowers+' Followers'"></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="14px" height="14px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#catgIcon'}}"></use>
                                            </svg>
                                         </i> <span ng-bind="Page.Category"></span>
                                    </div>
                                </li>
                            </ul>
                            <p class="m-t-5" ng-bind="Page.Description"></p>
                        </div>
                    </li>
                </ul>
                <div class="nodata-panel" ng-cloak ng-if="PageTotalRecords==0">
                    <div class="nodata-text">
                        <span class="nodata-media">
                            <img src="{{AssetBaseUrl}}img/empty-img/empty-no-search-results-found.png" >
                        </span>
                        <h5>No Results Found!</h5>
                        <p class="text-off">
                        {{lang.no_pages_found}}
                        </p>
                    </div>
                </div>               
            </div>
        </div>
    </section>
</aside>