<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getUserSearchList('',2,topUserOffset); getGroupSearchList('',2,topGroupOffset); getPageSearchList('',2,topPageOffset);">
    <section ng-if="TotalRecords>0" ng-cloak class="news-feed">
        <div class="feed-title">PEOPLE</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul ng-if="TotalRecords>0" class="list-group thumb-68">
                    <li ng-repeat="PS in PeopleSearch">
                        <figure>
                            <a target="_self" ng-href="{{BaseUrl+PS.ProfileLink}}">
                            <img  ng-if="PS.ProfilePicture!=='user_default.jpg'"  class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+PS.ProfilePicture}}">
                            <span ng-if="PS.ProfilePicture=='user_default.jpg'" class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(PS.FirstName+' '+PS.LastName)"></span></span>
                            </a>
                        </figure>
                        <div class="description">
                            <div ng-if="PS.ShowFriendsBtn==1 || ShowFollowBtn==1" class="btn-group btn-group-xs pull-right m-t-5">
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
                    <!-- <button ng-show="PeopleSearch.length<TotalRecords" class="btn  btn-link" type="button" ng-click="topUserOffset=topUserOffset+1; getUserSearchList('',2,topUserOffset);">Load More <span><i class="caret"></i></span></button> -->
                </ul>
            </div>
        </div>
    </section>
    <section ng-if="GroupTotalRecords>0" ng-cloak class="news-feed">
        <div class="feed-title">GROUPS</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul ng-if="GroupTotalRecords>0" class="list-group thumb-68">
                    <li ng-repeat="Group in GroupSearch">
                        <figure>
                            <span>
                                <a entitytype="group" entityguid="{{Group.GroupGUID}}" target="_self" ng-href="{{BaseUrl+Group.ProfileURL}}">
                                    <img   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+Group.ProfilePicture}}">
                                </a>
                            </span>
                        </figure>
                        <div class="description">
                            
                            <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="Group.Permission.IsActiveMember == 1 && Group.Permission.DirectGroupMember == 1 ">
                                <button ng-click='leave_group_search(Group.GroupGUID);'  aria-expanded="false" class="btn btn-sm btn-default" type="button"> <span class="text"><?php echo lang('leave_group'); ?></span></button>
                            </span>
                            <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="Group.Permission.IsInvited != 1 && Group.Permission.IsActiveMember != 1 && Group.IsPublic == 1 ">
                                <button aria-expanded="false" class="btn btn-sm btn-default" type="button" ng-click="join_group_search(Group.GroupGUID);"> <span class="text"><?php echo lang('join_group'); ?></span> </button>
                            </span> 
                            <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="Group.Permission.IsInvited == false && Group.Permission.IsActiveMember == false && Group.IsPublic ==0 && Group.Permission.IsInviteSent">
                                <button aria-expanded="false" class="btn btn-sm btn-default" type="button" ng-click="cancel_invite_search(Group.GroupGUID);"> <span class="text">Cancel Request</span> </button>
                            </span> 
                            <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="Group.Permission.IsInvited == false && Group.Permission.IsActiveMember == false && Group.IsPublic ==0 && !Group.Permission.IsInviteSent">
                                <button aria-expanded="false" class="btn btn-default" type="button" ng-click="request_invite_search(Group.GroupGUID);"> <span class="text">Request Invite</span> </button>
                            </span>

                            <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="Group.Permission.IsInvited == 1">
                                <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" type="button"> <span class="text"><?php echo lang('accept') ?></span> <i class="caret"></i> </button>
                                <ul role="menu" class="dropdown-menu">
                                    <li><a ng-click="accept_deny_request_search(Group.GroupGUID,'2');"><?php echo lang('accept') ?></a></li>
                                    <li><a ng-click="accept_deny_request_search(Group.GroupGUID,'2');"><?php echo lang('deny') ?></a></li>
                                </ul>
                            </span>
                            
                            <a entitytype="group" entityguid="{{Group.GroupGUID}}" target="_self" ng-href="{{BaseUrl+Group.ProfileURL}}" class="name" ng-bind="Group.GroupName">
                                <span class="group-secure"><i class="icon-lock"></i></span>
                            </a>
                            <ul class="sub-nav-listing">
                                <li>
                                    <ul class="activity-nav">
                                        <li>
                                            <i class="icon"><svg width="12px" height="12px" class="svg-icons">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#icnAccountGroup'}}"></use>
                                            </svg></i><span class="gray-clr">By</span> <a ng-href="{{Group.CreatedProfileUrl}}" target="_self" ng-bind="Group.CreatedBy"></a>
                                        </li>
                                        <li  ng-if="Group.ActivityLevel!='' "><span class="gray-clr">Active :</span> <span ng-bind="Group.ActivityLevel"></span></li>
                                    </ul>
                                </li>
                                <li ng-if="Group.Category.Name !='' ">
                                    <div class="location">
                                        <i class="icon">
                                            <svg width="14px" height="14px" class="svg-icons">
                                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#catgIcon'}}"></use>
                                            </svg>
                                        </i> <span ng-bind="Group.Category.Name"></span>
                                    </div>
                                </li>
                            </ul>
                            <p class="m-t-5" ng-bind="Group.GroupDescription"></p>
                        </div>
                    </li>
                    <!-- <button ng-show="GroupSearch.length<GroupTotalRecords" class="btn  btn-link" type="button" ng-click="topGroupOffset=topGroupOffset+1; getGroupSearchList('',2,topGroupOffset);">Load More <span><i class="caret"></i></span></button> -->
                </ul>
            </div>
        </div>
    </section>
    <section ng-if="PageTotalRecords>0" ng-cloak class="news-feed">
        <div class="feed-title">PAGES</div>
        <div class="news-feed-listing">
            <div class="feed-body">
                <ul class="list-group thumb-68">
                    <li ng-repeat="Page in PageSearch" ng-cloak>
                        <figure> 
                            <a entitytype="page" entityguid="{{Page.PageGUID}}" target="_self" ng-href="{{BaseUrl+'page/'+Page.PageURL}}" >
                                <img ng-src="{{ImageServerPath+'upload/profile/220x220/'+Page.ProfilePicture}}" class="img-circle"  >
                            </a> 
                        </figure>
                        <div class="description">
                            <button ng-if="Page.FollowStatus=='0'" class="btn btn-default btn-xs pull-right  m-t-5" ng-click="toggleFollowPage(Page.PageGUID,18,1,'search')">FOLLOW</button>
                            <button ng-if="Page.FollowStatus=='1'" class="btn btn-default btn-xs pull-right  m-t-5" ng-click="toggleFollowPage(Page.PageGUID,18,1,'search')">UNFOLLOW</button>
                            <a entitytype="page" entityguid="{{Page.PageGUID}}" target="_self" class="name ellipsis-pg" ng-href="{{BaseUrl+'page/'+Page.PageURL}}" ng-bind="Page.Title"></a>
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
                            <p class="m-t-5" ng-bind="smart_substr(Page.Description,400)"></p>
                        </div>
                    </li>
                    <!-- <button ng-show="PageSearch.length<PageTotalRecords" class="btn  btn-link" type="button" ng-click="topPageOffset=topPageOffset+1; getPageSearchList('',2,topPageOffset);">Load More <span><i class="caret"></i></span></button> -->
                </ul>
                
            </div>
        </div>
    </section>
    <div class="panel panel-info" ng-if="PageTotalRecords==0 && GroupTotalRecords==0 && TotalRecords==0" ng-cloak>    
        <div class="panel-body nodata-panel" >
            <div class="nodata-text">
                <span class="nodata-media">
                    <img src="assets/img/empty-img/empty-no-search-results-found.png" >
                </span>
                <p class="text-off">
                {{lang.no_general_result}}
                </p>
            </div>
        </div>
    </div>
    
</aside>