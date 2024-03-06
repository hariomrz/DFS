<div ng-cloak class="panel panel-default visible-lg visible-md" ng-if="similar_groups.length>0">
    <div class="panel-heading p-heading">
      <h3 ng-bind="lang.w_similar_groups"></h3>
    </div>
    <div class="panel-body">
        <ul class="list-group thumb-30">
            <li ng-repeat="group in similar_groups">
                <figure>
                    <a target="_self" entitytype="group" entityguid="{{group.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{group.ProfileURL}}"> <img ng-if="group.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{group.ProfilePicture}}" class="img-circle"  > </a>
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
                <div class="description">
                    <a target="_self" ng-href="<?php echo base_url();?>{{group.ProfileURL}}" class="a-link" ng-bind="group.GroupName"></a>
                    <ul class="activity-nav cat-sub-nav">
                        <li>
                            <span class="cat-name" ng-bind="group.Category.Name"></span>
                        </li>
                        <li><span class="cat-name" ng-if="group.Category.SubCategory.Name" ng-bind="group.Category.SubCategory.Name"></span></li>
                    </ul>
                    <div ng-bind="group.ShortGroupDescription"></div>
                    <span class="location" ng-if="group.MemberCount==1" ng-bind="'1 Member'"></span>
                    <span class="location" ng-if="group.MemberCount>1" ng-bind="group.MemberCount+' Members'"></span>
                    <div class="button-wrap-sm">
                       <button  ng-if="group.Permission.IsActiveMember == 1 && group.Permission.DirectGroupMember == 1 " class="btn btn-default btn-xs"  ng-click="groupDropOutAction(group.GroupGUID,'category');" ng-bind="lang.leave_group"></button>
                       <button ng-if="group.Permission.IsInvited != 1 && group.Permission.IsActiveMember != 1 && group.IsPublic == 1 " class="btn btn-default btn-xs"  ng-click="joinPublicGroup(group.GroupGUID,'category');" ng-bind="lang.join_group"></button>
                       <button ng-if="group.Permission.IsInvited == false && group.Permission.IsActiveMember == false && group.IsPublic ==0 && group.Permission.IsInviteSent" class="btn btn-default btn-xs"  ng-click="cancelGroupInvite(group.GroupGUID,'category');" ng-bind="lang.w_cancel_request"></button>
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
            </li>
        </ul>
    </div>
</div>