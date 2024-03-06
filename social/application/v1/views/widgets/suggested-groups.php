<div ng-cloak ng-controller="GroupPageCtrl" id="GroupPageCtrl" ng-if="SettingsData.m1==1" ng-init="suggestedGroupList(3,'0',0)" ng-show="suggestedlist.length>0" class="panel panel-widget">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="text" ng-bind="lang.w_suggested_groups_small">Suggested Groups</span>
        </h3>        
    </div>
    <div class="panel-body no-padding">
        <ul class="list-items-suggested">
            <li id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = suggestedlist|limitTo:3" ng-cloak>
                <div class="list-items-md">
                    <div class="list-inner">
                            <figure>
                            <a entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}"> 
                                <img ng-if="list.ProfilePicture != ''" ng-src="<?php echo IMAGE_SERVER_PATH . 'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle" alt="" title=""> 
                            </a>
                            </figure>
                            <div class="list-item-body">
                            <h4 class="list-heading-sm">
                                <a class="ellipsis" entitytype="group" entityguid="{{list.GroupGUID}}" class="text-black loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}" ng-bind="list.GroupName"></a>
                            </h4>
                            <ul class="list-activites list-icons-disc text-off text-sm">
                                <li ng-cloak ng-if="list.MemberCount > 1" ng-bind="list.MemberCount + ' Members'"></li>
                                <li ng-cloak ng-if="list.MemberCount == 1" ng-bind="list.MemberCount + ' Member'"></li>
                                <li ng-cloak ng-if="list.DiscussionCount > 1" ng-bind="list.DiscussionCount + ' Discussions'"></li>
                                <li ng-cloak ng-if="list.DiscussionCount == 1" ng-bind="list.DiscussionCount + ' Discussion'"></li>
                            </ul>
                            <p class="ellipsis text-sm-muted m-b-xs" ng-bind="list.GroupDescription"></p>
                                </div>
                    </div>
                    <div class="list-items-bottom">      
                        <div class="action">
                            <a class="btn btn-default btn-xs" ng-click="joinPublicGroup(list.GroupGUID, 'discover')">Join</a>
                        </div>              
                        <div class="member-list-block">
                            <ul class="member-list">
                                <li class="member-item">
                                    <a ng-repeat="member in list.MembersList" ng-if="member.ProfilePicture !== ''" class="thumb-item" tooltip ng-attr-title="{{member.Name}}" data-container="body" data-placement="bottom">
                                        <img err-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" ng-src="{{ImageServerPath + 'upload/profile/220x220/' + member.ProfilePicture}}" alt="" title="" />
                                    </a>          
                                    </li>
                            </ul>
                            <ul class="list-activites text-off">
                                    <li>
                                    <span ng-bind-html="::get_members_talking(list.MembersList)"></span>                 
                                    </li>
                                </ul>
                            </div>
                        </div>
                </div>
            </li> 
        </ul>
    </div> 
    <div class="panel-footer">
        <a class="view-link" target="_self" ng-href="{{BaseUrl + 'group/discover'}}">View All</a>
    </div>
</div>
