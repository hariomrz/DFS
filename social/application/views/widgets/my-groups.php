<div ng-hide="postEditormode" ng-cloak class="panel-transparent" id="FormCtrl">
    <div class="custom-panel">
        <div class="panel-heading transparent">
          <h5 class="uppercase" ng-bind="lang.w_my_groups">MY GROUPS</h5>
        </div> 
        <ul class="listing-group">
            <li class="list-group-item" id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = TopGroup|limitTo:3">
                <div class="list-items-xs">
                  <div class="list-inner">
                    <figure ng-if="list.Type=='FORMAL'">
                        <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" href="<?php echo base_url();?>{{list.ProfileURL}}">
                            <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                        </a>
                    </figure>
                    <div class="list-item-body">
                        <h4 class="list-heading-xs">
                            <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="a-link name  ellipsis-lg loadbusinesscard" href="<?php echo base_url();?>{{list.ProfileURL}}">{{list.GroupName}}</a>
                        </h4>
                        <ul class="invite-listings">
                            <li ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 && LoginSessionKey!==''">
                                <div class="button-wrap-sm">
                                    <button class="btn btn-default btn-xs" ng-click="joinPublicGroup(list.GroupGUID, 'fromUserWall');" ng-bind="lang.w_join"></button>
                                </div>
                            </li>
                            <li ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 && LoginSessionKey==''">
                                <div class="button-wrap-sm">
                                    <button class="btn btn-default btn-xs" ng-click="likeEmit('', 'ACTIVITY', '');" ng-bind="lang.w_join"></button>
                                </div>
                            </li>
                        </ul>
                    </div>
                  </div>
                </div>
            </li>
        </ul>
    </div>
    <div class="panel-seprator">&nbsp;</div>
</div>