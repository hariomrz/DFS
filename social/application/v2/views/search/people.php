<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getUserSearchList(Keyword,10,1, 1)">
    <section class="news-feed" ng-cloak>
        <div class="feed-title" ng-if="TotalRecords > 0"><span ng-bind="TotalRecords"></span> <span ng-bind="(TotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul ng-if="TotalRecords>0" class="list-group thumb-68">
                    <li ng-repeat="PS in PeopleSearch">
                        <figure>
                            <a target="_self" ng-href="{{BaseUrl+PS.ProfileLink}}">
                            <img ng-if="PS.ProfilePicture!=='user_default.jpg'"   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+PS.ProfilePicture}}">
                            <span ng-if="PS.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(PS.FirstName+' '+PS.LastName)"></span></span>
                            </a>
                        </figure>
                        <div class="description">
                            <div ng-cloak ng-if="PS.ShowFriendsBtn==0" class="btn-group btn-group-xs pull-right m-t-5">
                                <button type="button" class="btn btn-default">
                                    <span class="text" ng-click="follow(PS.UserGUID)" id="followmem{{PS.UserGUID}}" ng-bind="PS.FollowStatus"></span>
                                </button>
                            </div>
                            <div ng-cloak ng-if="PS.ShowFriendsBtn==1" class="btn-group btn-group-xs pull-right m-t-5">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span class="text" ng-if="PS.FriendStatus == '4'">Add As Friend</span> 
                                    <span class="text" ng-if="PS.FriendStatus == '2'">Cancel Request</span> 
                                    <span class="text" ng-if="PS.FriendStatus==1">Unfriend</span> 
                                    <span class="text" ng-if="PS.FriendStatus==3">Accept</span>
                                    <i class="caret"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li ng-if="PS.ShowFriendsBtn=='1'">
                                        <a ng-click="sendRequest(PS.UserGUID,'search')" ng-if="PS.FriendStatus == '3'">Accept</a>
                                        <a ng-click="removeFriend(PS.UserGUID,'search')" ng-if="PS.FriendStatus == '3'">Deny</a>
                                        <a ng-click="sendRequest(PS.UserGUID,'search')" ng-if="PS.FriendStatus == '4'">Add As Friend</a>
                                        <a ng-click="rejectRequest(PS.UserGUID,'search')" ng-if="PS.FriendStatus == '2'">Cancel Request</a>
                                        <a ng-click="removeFriend(PS.UserGUID,'search')" ng-if="PS.FriendStatus==1">Unfriend</a>
                                    </li>
                                    <li ng-if="PS.ShowFollowBtn=='1'">
                                        <a ng-click="follow(PS.UserGUID)" id="followmem{{PS.UserGUID}}" ng-bind="PS.FollowStatus">Follow</a>
                                    </li>
                                </ul>
                            </div>
                            <!-- <div ng-if="PS.ShowFriendsBtn && PS.FriendStatus==1" class="btn-group btn-group-xs pull-right m-t-5">
                                <button ng-click="removeFriend(PS.UserGUID,'search')" ng-if="PS.FriendStatus==1" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span class="text">Unfriend</span>
                                </button>
                            </div> -->
                            <a entitytype="user" entityguid="{{PS.UserGUID}}" target="_self" ng-href="{{BaseUrl+PS.ProfileLink}}" class="name" ng-bind="PS.FirstName+' '+PS.LastName"></a>
                            <ul class="sub-nav-listing">
                                <li ng-cloak ng-if="PS.MutualFriend>0">
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="14px" height="12px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#multiUserIcon'}}"></use>
                                            </svg>
                                        </i><span ng-cloak ng-if="PS.MutualFriend==1" ng-bind="PS.MutualFriend+' mutual friend'"></span><span ng-cloak ng-if="PS.MutualFriend>1" ng-bind="PS.MutualFriend+' mutual friends'"></span>
                                    </div>
                                </li>
                                <li ng-cloak ng-if="PS.Location!==''">
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="14px" height="14px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnMapMarke'}}"></use>
                                            </svg>
                                        </i> <span ng-bind="PS.Location"></span>
                                    </div>
                                </li>
                                <li ng-cloak ng-if="PS.InterestsCount>0">
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="8px" height="14px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#catgIcon'}}"></use>
                                            </svg>
                                        </i><span ng-bind="get_interest_label(PS.Interests,PS.InterestsCount)"></span>
                                    </div>
                                </li>
                            </ul>
                            <p class="m-t-5" ng-bind="PS.AboutMe"></p>
                        </div>
                    </li>
                </ul>
                <div class="nodata-panel" ng-cloak ng-if="TotalRecords==0">
                    <div class="nodata-text">
                        <span class="nodata-media">
                            <img src="assets/img/empty-img/empty-no-search-results-found.png" >
                        </span>
                        <h5>No Results Found!</h5>
                        <p class="text-off">
                        Seems like there are no users matching your search criteria! <br>Change your search terms, or tweak your filters. 
                        </p>
                        <a ng-href="<?php echo site_url('network/grow_your_network') ?>">Here's something for you to explore!</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>

<!-- Hidden Field Start -->
<!-- Hidden Field Ends -->
