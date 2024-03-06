<aside class="col-sm-7 col-md-7 col-xs-12 pull-left" ng-init="getGroupSearchList(Keyword,10,1)">
    <section class="news-feed" ng-cloak>
        <div class="feed-title" ng-if="GroupTotalRecords > 0"><span ng-bind="GroupTotalRecords"></span> <span ng-bind="(GroupTotalRecords>1) ? 'results' : 'result' ;"></span> found</div>
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
                            <!-- <button ng-if="Group.IsMember=='1'" ng-click="leave_group_search(Group.GroupGUID);" class="btn btn-default btn-xs pull-right m-t-5">Leave</button>
                            <button ng-if="Group.IsMember=='0'" ng-click="join_group_search(Group.GroupGUID);" class="btn btn-default btn-xs pull-right m-t-5">Join</button> -->
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
                                        <li ng-if="Group.ActivityLevel!='' "><span class="gray-clr">Active :</span> <span ng-bind="Group.ActivityLevel"></span></li>
                                    </ul>
                                </li>
                                <li ng-if="Group.Category[0].Name !='' ">
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
                </ul>
                <div class="nodata-panel" ng-cloak ng-if="GroupTotalRecords==0">
                    <div class="nodata-text">
                        <span class="nodata-media">
                            <img src="{{AssetBaseUrl}}img/empty-img/empty-no-search-results-found.png" >
                        </span>
                        <h5>No Results Found!</h5>
                        <p class="text-off">
                        {{lang.no_groups_found}} 
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
</aside>