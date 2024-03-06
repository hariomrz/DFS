<div class="panel panel-widget" ng-cloak ng-hide="GroupDetails.Permission.IsAdmin && GroupDetails.MemberCount==1" ng-init="showMembersWidget()" ng-show="GroupDetails.MemberCount>0">
  <div class="panel-heading">
    <h3 class="panel-title">      
      <a target="_self" ng-if="LoginSessionKey" ng-cloak ng-href="{{BaseUrl+ GroupDetails.GroupMemberProfileURL }}" class="btn btn-default btn-xs pull-right">
        <span class="icon">
          <i class="ficon-settings"></i>
        </span>
      </a>
      <span class="text">
        <span ng-if="GroupDetails.MemberCount==1" ng-bind="'1 Member'"></span>
        <span ng-if="GroupDetails.MemberCount>1" ng-bind="GroupDetails.MemberCount+' Members'"></span> 
 
        <small ng-if="group_members_friends==1" ng-bind="'(1 Friend)'"></small>
        <small ng-if="group_members_friends>1" ng-bind="'('+group_members_friends+' Friends)'"></small>
      </span>  
    </h3>
  </div>
  <div class="panel-body">
    <ul class="list-items-vertical list-items-column list-group-inline row">
        <li class="col-xs-6" ng-repeat="member in group_members">
          <div class="list-items-xmd">
            <div class="list-inner">
            <figure> 
                <span tooltip data-placement="top" data-original-title="Expert" ng-if="member.IsExpert=='1'" class="icon group-expert">
                     <i class="ficon-expert f-white"></i>
                </span>
                <a target="_self" entitytype="user" entityguid="{{member.ModuleEntityGUID}}" class="" href="{{BaseUrl+member.ProfileURL}}"> 
                    <img   err-name="{{member.FirstName+' '+member.LastName}}" ng-if="member.ProfilePicture != '' && member.ProfilePicture != 'user_default.jpg'"  class="img-circle" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{member.ProfilePicture}}"> 
                    <span ng-if="member.ProfilePicture=='' || member.ProfilePicture=='user_default.jpg' " class="default-thumb"><span ng-bind="getDefaultImgPlaceholder(member.FirstName+' '+member.LastName)"></span></span>
                </a>
            </figure>
            <div class="list-item-body">
              <h4 class="list-heading-xs ellipsis"> <a target="_self" ng-bind="member.FirstName+' '+member.LastName"></a></h4>
              <div><small ng-if="member.Location!==''" ng-bind="member.Location"></small></div>
              <div class="btn-toolbar btn-toolbar-xs center" ng-if="member.ShowFriendsBtn==1" >
                  <button ng-click="sendFriendRequest(member.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="member.FriendStatus == '4'" ng-bind="lang.send_request"></button>
                  <button ng-click="RejectFriendRequest(member.ModuleEntityGUID)" class="btn btn-default btn-xs" ng-if="member.FriendStatus == '2'" ng-bind="lang.cancel_request"></button>
              </div>
            </div>
          </div>

          </div>
        </li>
      </ul>      
  </div>
  <div class="panel-footer">
    <a target="_self" class="view-link" ng-href="{{BaseUrl+ GroupDetails.GroupMemberProfileURL }}" ng-if="LoginSessionKey" ng-bind="lang.see_all"></a>
    
    <a target="_self" class="view-link" ng-if="+LoginSessionKey == 0" ng-click="loginRequired()" ng-bind="lang.see_all"></a>
    
  </div>
</div>