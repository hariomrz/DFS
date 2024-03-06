<?php if(!$this->settings_model->isDisabled(1)) : // Check if group module is enabled  ?>
<div ng-cloak class="panel panel-default">
  <div ng-controller="GroupMemberCtrl" ng-cloak="">
    <div class="hidden-xs" id="GroupPageCtrlID" ng-init="get_top_group()" ng-controller="GroupPageCtrl" ng-cloak="" >
        <div class="panel-heading p-heading">
          <h3> 
              <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
              <span ng-bind="lang.w_my_groups"></span>
              <a target="_self" data-toggle="modal" data-target="#createGroup" class="pull-right gray-clr" ng-click="CreateEditGroup('createGroup');" ng-bind="lang.create"></a>
              <?php }else{ ?>
              <span ng-cloak="" class="capt" ng-bind="FirstName +'\'s Groups' "></span>
              <?php } ?>
              <?php  if ($this->session->userdata('UserID') != $UserID) { ?>
              <a target="_self" class="pull-right" href="{{'<?php echo site_url() ?>'+ProfileURL+'/groups'}}" ng-bind="lang.see_all"></a>
              <?php } ?>
          </h3>
          <div ng-if="TopGroup.length==0" class="blank-view">
            <img class="img-circle" src="<?php echo ASSET_BASE_URL ?>img/group-no-img.jpg" />
          </div>
        </div>
            <div class="panel-body" ng-if="TopGroup.length > 0">
          <div style="display:none;" class="people-suggestion-loader"><div class="spinner32"></div></div>
          <ul class="list-group removed-peopleslist middle-listings">
            <li class="list-group-item" id="grp{{list.GroupGUID}}" ng-repeat="list in listObj = TopGroup|limitTo:3">
                  <figure ng-if="list.Type=='FORMAL'">
                      <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                          <img ng-if="list.Type=='FORMAL'" ng-if="list.ProfilePicture!=''" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                      </a>
                  </figure>
                  <figure ng-if="list.Type=='INFORMAL'" ng-class="(list.MemberCount>2) ? 'group-thumb' : 'group-thumb-two' ;">
                      <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">
                          <img ng-if="list.Type=='INFORMAL' && list.ProfilePicture!='' && list.ProfilePicture!='group-no-img.jpg'" ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{list.ProfilePicture}}" class="img-circle"  >
                          <span ng-repeat="recipients in list.EntityMembers" class="ng-scope">
                          <img  ng-src="<?php echo IMAGE_SERVER_PATH.'upload/profile/220x220/' ?>{{recipients.ProfilePicture}}" entitytype="user" ng-if="$index<=2" class="ng-scope">
                        </span>
                      </a>
                  </figure>
              <div class="description"> 
                <a target="_self" entitytype="group" entityguid="{{list.GroupGUID}}" class="a-link name  ellipsis-lg loadbusinesscard" ng-href="<?php echo base_url();?>{{list.ProfileURL}}">{{list.GroupName}}</a>
                <ul class="invite-listings">
                  <li ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 && LoginSessionKey!==''">
                    <div class="button-wrap-sm">
                        <button class="btn btn-default btn-xs"  ng-click="joinPublicGroup(list.GroupGUID, 'fromUserWall');" ng-bind="lang.w_join"></button>
                     </div>   
                   </li>
                   <li ng-if="list.Permission.IsInvited != 1 && list.Permission.IsActiveMember != 1 && list.IsPublic == 1 && LoginSessionKey==''">
                        <div class="button-wrap-sm">
                        <button class="btn btn-default btn-xs"  ng-click="likeEmit('', 'ACTIVITY', '');" ng-bind="lang.w_join"></button>
                    </div>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
          <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
          <div class="footer-link">
            <a target="_self" class="pull-right" href="<?php echo site_url('group') ?>" ng-bind="lang.see_all"></a>
          </div>
          <?php } ?>
        </div>
    </div>
  </div>
</div>
<?php endif; ?>