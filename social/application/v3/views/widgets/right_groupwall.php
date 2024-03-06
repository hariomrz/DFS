<span ng-init="get_widgets();"></span>
<div class="panel panel-widget" ng-cloak ng-hide="GroupDetails.Permission.IsAdmin && GroupDetails.MemberCount==1" ng-show="GroupDetails.MemberCount>0">
    <div class="panel-heading">
        <h3 class="panel-title">
          <a target="_self" ng-href="<?php echo base_url(); ?>{{GroupDetails.GroupMemberProfileURL}}" class="btn btn-default btn-xs pull-right" ng-if="LoginSessionKey" ng-cloak>
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
                    <a target="_self" entitytype="user" entityguid="{{member.ModuleEntityGUID}}" class="" href="{{'<?php echo site_url() ?>'+member.ProfileURL}}"> 
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
    <div ng-if="GroupDetails.MemberCount>4" class="panel-footer">
        <a target="_self" target="_self" class="view-link" ng-href="<?php echo base_url(); ?>{{GroupDetails.GroupMemberProfileURL}}" ng-bind="lang.see_all"></a>
    </div>
</div>

<?php if($post_type!=4) { ?>
<div ng-cloak class="panel panel-widget" ng-if="similar_groups.length>0">
    <div class="panel-heading">
        <h3 class="panel-title" ng-bind="lang.w_similar_groups"></h3>
    </div>
    <div class="panel-body">
        <ul class="list-items-group">
            <li ng-repeat="group in similar_groups">
              <div class="list-items-xmd">
                <div class="list-inner">
                <figure>
                    <a target="_self" entitytype="group" entityguid="{{group.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>{{group.ProfileURL}}"> <img ng-if="group.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{group.ProfilePicture}}" class="img-circle"  > </a>
                </figure>
                <div class="similar-group-info">                    
                    <span ng-if="group.IsPublic==1" class="icon group-type" tooltip data-placement="top" title="Public">
                       <i class="ficon-globe"></i>
                    </span>
                    <span ng-if="group.IsPublic==0" class="icon group-type" tooltip data-placement="top" title="Closed">
                       <i class="ficon-close f-lg"></i>
                    </span>
                    <span ng-if="group.IsPublic==2" class="icon group-type" tooltip data-placement="top" title="Secret">
                       <i class="ficon-secrets f-lg"></i>
                    </span>
                    <span class="icon group-activity-lavel" ng-class="group.ActivityLevel=='High'?'heigh':'moderate'"  tooltip data-placement="top" title="Activity Level : {{group.ActivityLevel}}">
                      <svg width="13px" height="9px" class="svg-icons no-hover">
                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="{{SiteURL+'assets/img/sprite.svg#iconGrouppactivity'}}"></use>
                      </svg>
                    </span>
                </div>
                <div class="list-item-body">
                    <h4 class="list-heading-xs ellipsis"><a target="_self" href="<?php echo base_url();?>{{group.ProfileURL}}" ng-bind="group.GroupName"></a></h4>
                    <ul class="activity-nav cat-sub-nav">
                        <li>
                            <span class="cat-name" ng-bind="group.Category.Name"></span>
                        </li>
                        <li><span class="cat-name" ng-if="group.Category.SubCategory.Name" ng-bind="group.Category.SubCategory.Name"></span></li>
                    </ul>
                    <div ng-bind="group.ShortGroupDescription"></div>
                    <div><small ng-if="group.MemberCount==1" ng-bind="'1 Member'"></small></div>
                    <div><small ng-if="group.MemberCount>1" ng-bind="group.MemberCount+' Members'"></small></div>
                    <div class="btn-toolbar btn-toolbar-xs">
                       <button  ng-if="group.Permission.IsActiveMember == 1 && group.Permission.DirectGroupMember == 1 " class="btn btn-default btn-xs"  ng-click="groupDropOutAction(group.GroupGUID,'category');" ng-bind="lang.leave_group"></button>
                       <button ng-if="group.Permission.IsInvited != 1 && group.Permission.IsActiveMember != 1 && group.IsPublic == 1 " class="btn btn-default btn-xs"  ng-click="joinPublicGroup(group.GroupGUID,'category');" ng-bind="lang.join_group"></button>
                       <button ng-if="group.Permission.IsInvited == false && group.Permission.IsActiveMember == false && group.IsPublic ==0 && group.Permission.IsInviteSent" class="btn btn-default btn-xs"  ng-click="cancelGroupInvite(group.GroupGUID,'category');" ng-bind="lang.cancel_request"></button>
                       <button ng-if="group.Permission.IsInvited == false && group.Permission.IsActiveMember == false && group.IsPublic ==0 && !group.Permission.IsInviteSent" class="btn btn-default btn-xs"  ng-click="requestGroupInvite(group.GroupGUID,'category');" ng-bind="lang.request_invite"></button>
                       <span class="btn-group btn-group-xs pull-right m-t-5" ng-if="group.Permission.IsInvited == 1">
                          <button  aria-expanded="false" data-toggle="dropdown" class="btn btn-sm btn-default dropdown-toggle" type="button"> <span class="text" ng-bind="lang.accept"></span> <i class="caret"></i> </button>
                          <ul role="menu" class="dropdown-menu">
                             <li><a target="_self" ng-click="groupAcceptDenyRequest(group.GroupGUID,'2','category');" ng-bind="lang.accept"></a></li>
                             <li><a target="_self" ng-click="groupAcceptDenyRequest(group.GroupGUID,'13','category');" ng-bind="lang.deny"></a></li>
                          </ul>
                       </span>
                    </div>
                </div>
              </div>
            </li>
        </ul>
    </div>
</div>
<?php } ?>